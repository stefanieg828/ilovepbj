<?php
require_once 'config.php';
require_once __DIR__ . '/stripe-config.php';

if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
    pbj_post_auth_redirect($pdo);
}

$mode = ($_GET['mode'] ?? $_POST['mode'] ?? 'start') === 'join' ? 'join' : 'start';
$planId = (string)($_GET['plan'] ?? $_POST['plan'] ?? pbj_default_plan_id());
$resolved = pbj_plan_by_id($planId);
if (!$resolved || !empty($resolved['coming'])) {
    $planId = pbj_default_plan_id();
    $resolved = pbj_plan_by_id($planId);
} else {
    $planId = $resolved['id'];
}
$plan = $resolved;
$codePrefill = strtoupper(trim((string)($_GET['code'] ?? $_POST['restaurant_code'] ?? '')));
$salesCodePrefill = function_exists('pbj_resolve_sales_code_from_request')
    ? pbj_resolve_sales_code_from_request((string) ($_POST['sales_code'] ?? $_GET['ref'] ?? $_GET['sales'] ?? ''))
    : strtoupper(trim((string) ($_GET['ref'] ?? $_GET['sales'] ?? $_POST['sales_code'] ?? '')));

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = ($_POST['mode'] ?? 'start') === 'join' ? 'join' : 'start';
    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $full_name = trim((string)($_POST['full_name'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    $restaurant_name = trim((string)($_POST['restaurant_name'] ?? ''));
    $restaurant_code = strtoupper(trim((string)($_POST['restaurant_code'] ?? '')));
    $join_role = (string)($_POST['join_role'] ?? 'foh');
    $salesCodeIn = function_exists('pbj_resolve_sales_code_from_request')
        ? pbj_resolve_sales_code_from_request((string) ($_POST['sales_code'] ?? ''))
        : strtoupper(trim((string) ($_POST['sales_code'] ?? '')));
    $planId = (string)($_POST['plan'] ?? pbj_default_plan_id());
    $postPlan = pbj_plan_by_id($planId);
    if (!$postPlan || !empty($postPlan['coming'])) {
        $planId = pbj_default_plan_id();
    } else {
        $planId = $postPlan['id'];
    }

    $isIndividual = ($planId === 'individual' && $mode === 'start');

    if ($username === '' || $email === '' || $full_name === '' || $password === '') {
        $error = 'Please fill in all account fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($mode === 'start' && !$isIndividual && $restaurant_name === '') {
        $error = 'Give your restaurant a name.';
    } elseif ($mode === 'join' && $restaurant_code === '') {
        $error = 'Enter the restaurant invite code from your manager.';
    } else {
        try {
            $house = null;
            if ($mode === 'join') {
                $house = pbj_find_restaurant_by_code($pdo, $restaurant_code);
                if (!$house) {
                    throw new RuntimeException('That restaurant code wasn’t found. Check with your manager and try again.');
                }
                $seatStatus = pbj_restaurant_seat_status($pdo, (int) $house['id']);
                if (empty($seatStatus['ok'])) {
                    throw new RuntimeException(pbj_seat_limit_message($seatStatus));
                }
            }

            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn()) {
                $error = 'Username or email already exists. Try logging in instead.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                // House starters (any paid plan, including Individual) are Owners.
                // Joiners pick foh / boh / manager.
                $role = $mode === 'start'
                    ? 'owner'
                    : (in_array($join_role, ['foh', 'boh', 'manager'], true) ? $join_role : 'foh');
                $accessStatus = pbj_access_status_for_new_user($email);

                $pdo->beginTransaction();
                $ins = $pdo->prepare(
                    "INSERT INTO users (username, email, full_name, password, role, theme, access_status) VALUES (?, ?, ?, ?, ?, 'sweet', ?)"
                );
                $ins->execute([$username, $email, $full_name, $hashed, $role, $accessStatus]);
                $uid = (int) $pdo->lastInsertId();

                $sessionBase = [
                    'id' => $uid,
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $full_name,
                    'theme' => 'sweet',
                    'theme_chosen' => 0,
                    'access_status' => $accessStatus,
                ];

                if ($mode === 'start' && $isIndividual) {
                    $pdo->commit();
                    pbj_set_session_user($sessionBase + ['role' => 'owner']);
                    $_SESSION['pending_plan_id'] = $planId;
                    if ($salesCodeIn !== '' && function_exists('pbj_attribute_sale')) {
                        pbj_attribute_sale($pdo, $uid, 0, $planId, $salesCodeIn, 'register');
                    }
                    if ($accessStatus === 'approved') {
                        pbj_post_auth_redirect($pdo, '/home');
                    }
                    // 14-day no-card free trial (then subscribe)
                    if (function_exists('pbj_start_free_trial') && pbj_trial_days() > 0) {
                        $trial = pbj_start_free_trial($pdo, $uid, $planId, 0);
                        if (!empty($trial['ok'])) {
                            $_SESSION['access_status'] = 'approved';
                            pbj_post_auth_redirect($pdo, '/home?welcome=1&trial=1');
                        }
                    }
                    // Fallback: card pay when Stripe is set up
                    if (function_exists('stripe_is_configured') && stripe_is_configured() && stripe_price_id_for_plan($planId)) {
                        header('Location: /billing/checkout?plan=' . urlencode($planId));
                        exit();
                    }
                    header('Location: /waiting?created=1');
                    exit();
                }

                if ($mode === 'start') {
                    $created = pbj_create_restaurant($pdo, $uid, $restaurant_name, $planId);
                    $pdo->commit();
                    pbj_set_session_user($sessionBase + ['role' => 'owner']);
                    $_SESSION['pending_plan_id'] = $planId;
                    $_SESSION['pending_invite_code'] = $created['invite_code'];
                    $ridCreated = (int) ($created['id'] ?? 0);
                    if ($salesCodeIn !== '' && function_exists('pbj_attribute_sale')) {
                        pbj_attribute_sale($pdo, $uid, $ridCreated, $planId, $salesCodeIn, 'register');
                    }
                    if ($accessStatus === 'approved') {
                        pbj_post_auth_redirect($pdo, '/home?welcome=1&code=' . urlencode($created['invite_code']));
                    }
                    // 14-day no-card free trial — full house access, then Stripe
                    if (function_exists('pbj_start_free_trial') && pbj_trial_days() > 0) {
                        $rid = $ridCreated;
                        $trial = pbj_start_free_trial($pdo, $uid, $planId, $rid);
                        if (!empty($trial['ok'])) {
                            $_SESSION['access_status'] = 'approved';
                            $go = '/home?welcome=1&trial=1&code=' . urlencode($created['invite_code']);
                            pbj_post_auth_redirect($pdo, $go);
                        }
                    }
                    // Fallback: house starter pays → Stripe Checkout
                    if (function_exists('stripe_is_configured') && stripe_is_configured() && stripe_price_id_for_plan($planId)) {
                        header('Location: /billing/checkout?plan=' . urlencode($planId));
                        exit();
                    }
                    header('Location: /waiting?created=1');
                    exit();
                }

                $allowedJoin = ['foh', 'boh', 'manager'];
                $memRole = in_array($join_role, $allowedJoin, true) ? $join_role : 'foh';
                pbj_join_restaurant($pdo, $uid, (int)$house['id'], $memRole);
                $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$memRole, $uid]);
                // Invite joiners never pay — ensure approved after join (session + DB)
                pbj_approve_invite_joiner($pdo, $uid);
                $pdo->commit();

                $sessionBase['access_status'] = 'approved';
                pbj_set_session_user($sessionBase + ['role' => $memRole]);
                $_SESSION['access_status'] = 'approved';
                pbj_post_auth_redirect($pdo, '/home?joined=1');
                exit();
            }
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account · ilovepbj ops</title>
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DreamingOutLoudPro', Georgia, serif; background: #FCF8EE; color: #3a2f1f; }
        .top { background: #E55163; color: white; padding: 16px 20px; text-align: center; }
        .top a { color: white; text-decoration: none; opacity: 0.9; font-size: 0.95rem; }
        h1 { font-family: 'ModernLoveCaps', serif; margin: 8px 0 0; font-size: 2.2rem; }
        .wrap { max-width: 520px; margin: 0 auto; padding: 24px 16px 60px; }
        .tabs { display: flex; gap: 8px; margin-bottom: 14px; }
        .tab {
            flex: 1; text-align: center; padding: 12px 10px; border-radius: 14px; background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06); text-decoration: none; color: inherit; font-size: 0.95rem;
        }
        .tab.active { background: #E55163; color: white; }
        .card { background: white; border-radius: 20px; padding: 24px 22px; box-shadow: 0 8px 28px rgba(0,0,0,0.08); }
        .plan-pill {
            display: inline-block; background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;
            border-radius: 999px; padding: 6px 12px; font-size: 0.9rem; margin-bottom: 12px;
        }
        .hint { font-size: 0.95rem; opacity: 0.75; margin: 0 0 14px; line-height: 1.4; }
        label { display: block; font-size: 0.88rem; opacity: 0.7; margin-bottom: 4px; text-align: left; }
        input, select {
            width: 100%; padding: 12px 14px; margin-bottom: 12px; border: 2px solid #F3C5CC; border-radius: 12px;
            font-size: 1.05rem; font-family: inherit; background: #FFFBF8; color: #3a2f1f;
        }
        input:focus, select:focus { outline: none; border-color: #E55163; }
        .row { display: flex; gap: 10px; flex-wrap: wrap; }
        .row > div { flex: 1; min-width: 140px; }
        .btn {
            display: block; width: 100%; border: none; border-radius: 14px; padding: 14px; font-size: 1.1rem;
            cursor: pointer; font-family: inherit; background: #E55163; color: white; margin-top: 6px;
        }
        .error { background: #FDECEA; color: #B71C1C; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
        .links { text-align: center; margin-top: 16px; line-height: 1.6; }
        .links a { color: #E55163; font-weight: 600; }
        .section-label { font-size: 0.9rem; opacity: 0.65; margin: 8px 0 10px; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="top">
        <a href="/">← Back to home</a>
        <h1>Create account</h1>
    </div>
    <div class="wrap">
        <div class="tabs">
            <a class="tab<?php echo $mode === 'start' ? ' active' : ''; ?>" href="/register?mode=start&amp;plan=<?php echo urlencode($planId); ?>">Start a restaurant</a>
            <a class="tab<?php echo $mode === 'join' ? ' active' : ''; ?>" href="/register?mode=join<?php echo $codePrefill ? ('&amp;code=' . urlencode($codePrefill)) : ''; ?>">I have a code</a>
        </div>

        <div class="card">
            <?php if ($mode === 'start' && $plan): ?>
                <span class="plan-pill">
                    Plan: <?php echo htmlspecialchars($plan['name']); ?> · <?php echo htmlspecialchars($plan['price']); ?>
                    <?php if (!empty($plan['price_note'])): ?>
                        (<?php echo htmlspecialchars($plan['price_note']); ?>)
                    <?php endif; ?>
                </span>
                <?php if (!empty($plan['limited'])): ?>
                    <p class="hint">Individual is solo access — no house invite codes. Start with a <strong><?php echo (int) pbj_trial_days(); ?>-day free trial</strong> (no card). Then $5/mo if you love it.</p>
                <?php else: ?>
                    <p class="hint">You’ll be the owner. We’ll generate a house invite code so your team can join (up to your plan’s seat limit). <strong><?php echo (int) pbj_trial_days(); ?>-day free trial</strong> — no card required. Subscribe anytime before it ends.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="hint">Create your login, then join the house with the code your manager shared.</p>
            <?php endif; ?>

            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <form method="POST" action="/register">
                <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
                <input type="hidden" name="plan" value="<?php echo htmlspecialchars($planId); ?>">

                <div class="section-label">Your account</div>
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" required value="<?php echo htmlspecialchars((string)($_POST['full_name'] ?? '')); ?>">

                <div class="row">
                    <div>
                        <label for="username">Username</label>
                        <input id="username" name="username" required autocomplete="username" value="<?php echo htmlspecialchars((string)($_POST['username'] ?? '')); ?>">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars((string)($_POST['email'] ?? '')); ?>">
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required minlength="6" autocomplete="new-password">
                    </div>
                    <div>
                        <label for="confirm_password">Confirm password</label>
                        <input id="confirm_password" type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
                    </div>
                </div>

                <?php if ($mode === 'start'): ?>
                    <div class="section-label" id="start-section-label"><?php echo !empty($plan['limited']) ? 'Plan' : 'Your restaurant'; ?></div>
                    <div id="restaurant-name-wrap"<?php echo !empty($plan['limited']) ? ' style="display:none"' : ''; ?>>
                        <label for="restaurant_name">Restaurant name</label>
                        <input id="restaurant_name" name="restaurant_name" placeholder="e.g. Downtown PB&amp;J" value="<?php echo htmlspecialchars((string)($_POST['restaurant_name'] ?? '')); ?>"<?php echo empty($plan['limited']) ? ' required' : ''; ?>>
                    </div>
                    <label for="plan_select">Plan</label>
                    <select id="plan_select" name="plan">
                        <?php foreach (pbj_plans() as $p): if (!empty($p['coming'])) continue; ?>
                            <option value="<?php echo htmlspecialchars($p['id']); ?>"<?php echo $planId === $p['id'] ? ' selected' : ''; ?> data-limited="<?php echo !empty($p['limited']) ? '1' : '0'; ?>">
                                <?php
                                $label = $p['name'] . ' — ' . $p['price'];
                                if (!empty($p['price_note'])) {
                                    $label .= ' (' . $p['price_note'] . ')';
                                }
                                echo htmlspecialchars($label);
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="sales_code">Sales / partner code <span style="opacity:0.65;font-weight:400;">(optional)</span></label>
                    <input id="sales_code" name="sales_code" placeholder="If a teammate shared a code" autocomplete="off"
                        value="<?php echo htmlspecialchars($salesCodePrefill ?: (string) ($_POST['sales_code'] ?? '')); ?>"
                        style="text-transform:uppercase;letter-spacing:0.04em;">
                    <p class="hint">Start free for <?php echo (int) pbj_trial_days(); ?> days — no card. Subscribe later from Account → Plans. A sales code credits your partner if you were referred.</p>
                <?php else: ?>
                    <div class="section-label">Join a house</div>
                    <label for="restaurant_code">Restaurant invite code</label>
                    <input id="restaurant_code" name="restaurant_code" required placeholder="e.g. ABCD-1234" value="<?php echo htmlspecialchars($codePrefill ?: (string)($_POST['restaurant_code'] ?? '')); ?>" style="text-transform:uppercase;letter-spacing:0.06em;">
                    <label for="join_role">I mostly work…</label>
                    <select id="join_role" name="join_role">
                        <?php
                        $jr = (string)($_POST['join_role'] ?? 'foh');
                        foreach (['foh' => 'FOH / service', 'boh' => 'BOH / kitchen', 'manager' => 'Manager / shift lead'] as $val => $label):
                        ?>
                        <option value="<?php echo $val; ?>"<?php echo $jr === $val ? ' selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <button class="btn" type="submit" id="submit-btn"><?php
                    if ($mode === 'join') {
                        echo 'Create account & join';
                    } elseif (!empty($plan['limited'])) {
                        echo 'Create Individual account';
                    } else {
                        echo 'Create house & account';
                    }
                ?></button>
            </form>

            <div class="links">
                Already have an account? <a href="/login">Log in</a><br>
                <a href="/#plans">See plans</a>
            </div>
        </div>
    </div>
    <?php if ($mode === 'start'): ?>
    <script>
    (function () {
        var sel = document.getElementById('plan_select');
        var wrap = document.getElementById('restaurant-name-wrap');
        var input = document.getElementById('restaurant_name');
        var label = document.getElementById('start-section-label');
        var btn = document.getElementById('submit-btn');
        if (!sel || !wrap || !input) return;
        function sync() {
            var opt = sel.options[sel.selectedIndex];
            var limited = opt && opt.getAttribute('data-limited') === '1';
            wrap.style.display = limited ? 'none' : 'block';
            input.required = !limited;
            if (label) label.textContent = limited ? 'Plan' : 'Your restaurant';
            if (btn) btn.textContent = limited ? 'Create Individual account' : 'Create house & account';
        }
        sel.addEventListener('change', sync);
        sync();
    })();
    </script>
    <?php endif; ?>
</body>
</html>
