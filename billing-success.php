<?php
/**
 * After Stripe Checkout — confirm payment and open the app.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe-config.php';
require_once __DIR__ . '/pbj-permissions.php';

$sessionId = (string) ($_GET['session_id'] ?? '');
$paid = false;
$error = '';

if ($sessionId !== '' && stripe_is_configured()) {
    $res = stripe_retrieve_session($sessionId);
    if (!empty($res['ok']) && is_array($res['data'])) {
        $applied = stripe_apply_paid_session($pdo, $res['data']);
        $paid = !empty($applied['ok']);
        if (!$paid) {
            $error = $applied['error'] ?? 'not_paid';
        }
    } else {
        $error = $res['error'] ?? 'session';
    }
}

// Refresh session status from DB
if (!empty($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare('SELECT access_status FROM users WHERE id = ?');
        $stmt->execute([(int) $_SESSION['user_id']]);
        $st = $stmt->fetchColumn();
        if ($st) {
            $_SESSION['access_status'] = $st;
        }
    } catch (Throwable $e) {
        // ignore
    }
}

$isUpgrade = !empty($_GET['upgrade']);
if ($paid || pbj_user_is_approved()) {
    if ($isUpgrade) {
        header('Location: /billing/plans?ok=upgraded');
        exit();
    }
    // Short delay UX not needed — go home / theme
    pbj_post_auth_redirect($pdo, '/home?welcome=paid');
}

// Not confirmed yet — friendly page with retry
$name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'friend';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment · ilovepbj ops</title>
    <style>
        body { margin: 0; font-family: Georgia, serif; background: #FCF8EE; color: #3a2f1f; }
        .top { background: #E55163; color: white; padding: 20px; text-align: center; }
        .wrap { max-width: 480px; margin: 0 auto; padding: 28px 16px; }
        .card { background: white; border-radius: 20px; padding: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        h1 { margin: 0 0 12px; font-size: 1.6rem; color: #E55163; text-align: center; }
        p { line-height: 1.5; }
        .btn { display: block; text-align: center; background: #E55163; color: white; text-decoration: none; padding: 14px; border-radius: 14px; margin-top: 12px; }
        .btn-ghost { background: white; color: #3a2f1f; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
    </style>
    <?php if ($sessionId): ?>
    <meta http-equiv="refresh" content="4;url=/billing/success?session_id=<?php echo htmlspecialchars(urlencode($sessionId)); ?>">
    <?php endif; ?>
</head>
<body>
    <div class="top"><strong>ilovepbj ops</strong></div>
    <div class="wrap">
        <div class="card">
            <h1>Confirming your payment…</h1>
            <p>Hey <?php echo htmlspecialchars((string) $name); ?> — if you just paid, we’re unlocking your account. This page refreshes automatically.</p>
            <?php if ($error): ?>
                <p style="opacity:0.75;font-size:0.9rem;">Status: still waiting on Stripe (<?php echo htmlspecialchars($error); ?>).</p>
            <?php endif; ?>
            <a class="btn" href="/billing/checkout">Try payment again</a>
            <a class="btn btn-ghost" href="/waiting">Back to waiting room</a>
        </div>
    </div>
</body>
</html>
