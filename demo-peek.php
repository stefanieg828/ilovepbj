<?php
/**
 * One-tap guest peek into the FREE-DEMO house — no username/email/password form.
 *
 * Nginx (pretty URL):
 *   location = /demo { rewrite ^ /demo-peek.php last; }
 *
 * Security notes:
 * - Joins FREE-DEMO / DEMO-PBJ only (never SALES-PBJ).
 * - Ephemeral guest users; nightly playground reset cleans shared mess.
 * - Session reuses one guest; does not grant platform admin.
 * - Skips first-run theme picker (defaults to sweet).
 */
require_once __DIR__ . '/config.php';
if (is_readable(__DIR__ . '/sales-playground.inc.php')) {
    require_once __DIR__ . '/sales-playground.inc.php';
}

if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

// Already signed in (guest or real) — don't mint another account.
if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0) {
    if (empty($_SESSION['demo_guest'])) {
        header('Location: /home');
        exit();
    }
    header('Location: /home?peek=1');
    exit();
}

// Light rate limit: one guest per session (reuse when already logged in above).
try {
    $demoId = 0;
    if (function_exists('pbj_ensure_demo_playground')) {
        $demoId = (int) pbj_ensure_demo_playground($pdo);
    }
    if ($demoId <= 0) {
        $demoCode = defined('PBJ_DEMO_INVITE_CODE') ? PBJ_DEMO_INVITE_CODE : 'FREE-DEMO';
        $house = pbj_find_restaurant_by_code($pdo, $demoCode);
        $demoId = $house ? (int) $house['id'] : 0;
    }
    if ($demoId <= 0) {
        throw new RuntimeException('Free demo is not available right now. Please try again shortly.');
    }

    // Never accidentally land on sales playground.
    if (function_exists('pbj_restaurant_is_sales_playground')
        && pbj_restaurant_is_sales_playground($pdo, $demoId)) {
        throw new RuntimeException('Free demo is not available right now. Please try again shortly.');
    }

    $uid = 0;
    $username = '';
    $email = '';
    $fullName = 'Demo guest';
    $role = 'foh';
    $hashed = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

    $pdo->beginTransaction();
    for ($attempt = 0; $attempt < 6; $attempt++) {
        $token = bin2hex(random_bytes(8));
        $username = 'guest_' . substr($token, 0, 12);
        $email = 'guest+' . $token . '@ilovepbj.demo';

        $check = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $check->execute([$username, $email]);
        if ($check->fetchColumn()) {
            continue;
        }

        $ins = $pdo->prepare(
            "INSERT INTO users (username, email, full_name, password, role, theme, access_status)
             VALUES (?, ?, ?, ?, ?, 'sweet', 'approved')"
        );
        $ins->execute([$username, $email, $fullName, $hashed, $role]);
        $uid = (int) $pdo->lastInsertId();
        break;
    }

    if ($uid <= 0) {
        throw new RuntimeException('Could not create a guest peek right now. Please try again.');
    }

    // Skip theme picker friction for one-tap peek.
    try {
        $pdo->prepare('UPDATE users SET theme_chosen = 1 WHERE id = ?')->execute([$uid]);
    } catch (Throwable $e) {
        // Column ensured elsewhere; ignore if unavailable.
    }

    pbj_join_restaurant($pdo, $uid, $demoId, $role);
    $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
    pbj_approve_invite_joiner($pdo, $uid);

    if (function_exists('pbj_save_user_prefs')) {
        pbj_save_user_prefs($pdo, $uid, [
            'demo_guest' => 1,
            'onboarding_10min' => [
                'started' => true,
                'dismissed' => false,
                'completed' => false,
                'steps' => [
                    'recipe' => false,
                    'plate_cost' => false,
                    'menu_price' => false,
                    'save_account' => false,
                ],
                'updatedAt' => (int) round(microtime(true) * 1000),
            ],
        ]);
    }

    $pdo->commit();

    pbj_set_session_user([
        'id' => $uid,
        'username' => $username,
        'email' => $email,
        'full_name' => $fullName,
        'theme' => 'sweet',
        'theme_chosen' => 1,
        'access_status' => 'approved',
        'role' => $role,
    ]);
    $_SESSION['access_status'] = 'approved';
    $_SESSION['theme_chosen'] = 1;
    $_SESSION['demo_guest'] = 1;
    $_SESSION['demo_peek_minted'] = 1;

    header('Location: /home?peek=1');
    exit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('demo-peek: ' . $e->getMessage());
    header('Location: /register?mode=playground&peek_error=1');
    exit();
}
