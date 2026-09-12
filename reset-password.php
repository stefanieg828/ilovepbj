<?php
require_once 'config.php';

if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

// Allow archived/unapproved users to finish reset without bouncing to waiting
if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0 && pbj_user_is_approved()) {
    pbj_post_auth_redirect($pdo);
}

$error = '';
$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$user = null;

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    $error = 'This reset link is missing or invalid. Request a new one from the login page.';
} else {
    $stmt = $pdo->prepare(
        'SELECT id, username, email, access_status, password_reset_expires FROM users
         WHERE password_reset_token = ? LIMIT 1'
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $error = 'This reset link is invalid or already used. Request a new one.';
        $user = null;
    } elseif (empty($user['password_reset_expires']) || strtotime((string) $user['password_reset_expires']) < time()) {
        $error = 'This reset link has expired. Request a new one.';
        $user = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user && $error === '') {
    $pass1 = (string) ($_POST['password'] ?? '');
    $pass2 = (string) ($_POST['confirm_password'] ?? '');
    if ($pass1 === '' || $pass2 === '') {
        $error = 'Enter and confirm your new password.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pass1) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $upd = $pdo->prepare(
            'UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?'
        );
        $upd->execute([$hash, (int) $user['id']]);
        // Archived users reactivate (approved) after choosing a new password
        if (function_exists('pbj_reactivate_user_after_password_reset')) {
            pbj_reactivate_user_after_password_reset($pdo, (int) $user['id']);
        } else {
            $prev = strtolower(trim((string) ($user['access_status'] ?? '')));
            if ($prev === 'archived') {
                $pdo->prepare("UPDATE users SET access_status = 'approved' WHERE id = ?")->execute([(int) $user['id']]);
            }
        }
        header('Location: /login?reset=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password · ilovepbj ops</title>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'DreamingOutLoudPro', Georgia, serif; background: #FCF8EE; color: #3a2f1f; }
        .top { background: #E55163; color: white; padding: 16px 20px; text-align: center; }
        .top a { color: white; text-decoration: none; opacity: 0.9; font-size: 0.95rem; }
        h1 { font-family: 'ModernLoveCaps', serif; margin: 8px 0 0; font-size: 2.2rem; }
        .wrap { max-width: 440px; margin: 0 auto; padding: 28px 16px 50px; }
        .card { background: white; border-radius: 20px; padding: 28px 24px; box-shadow: 0 8px 28px rgba(0,0,0,0.08); }
        .card p.lead { margin: 0 0 18px; opacity: 0.8; text-align: center; }
        label { display: block; font-size: 0.9rem; opacity: 0.7; margin-bottom: 4px; text-align: left; }
        input {
            width: 100%; padding: 12px 14px; margin-bottom: 12px; border: 2px solid #F3C5CC; border-radius: 12px;
            font-size: 1.05rem; font-family: inherit; background: #FFFBF8; color: #3a2f1f;
        }
        input:focus { outline: none; border-color: #E55163; }
        .btn {
            display: block; width: 100%; border: none; border-radius: 14px; padding: 14px; font-size: 1.1rem;
            cursor: pointer; font-family: inherit; background: #E55163; color: white; margin-top: 6px;
        }
        .error { background: #FDECEA; color: #B71C1C; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
        .links { text-align: center; margin-top: 18px; font-size: 0.98rem; }
        .links a { color: #E55163; font-weight: 600; }
    </style>
</head>
<body>
    <div class="top">
        <a href="/login">← Back to log in</a>
        <h1>New password</h1>
    </div>
    <div class="wrap">
        <div class="card">
            <?php if ($error && !$user): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <div class="links">
                    <a href="/forgot-password">Request a new link</a>
                    · <a href="/login">Log in</a>
                </div>
            <?php else: ?>
                <p class="lead">Choose a new password for <?php echo htmlspecialchars((string)($user['username'] ?? 'your account')); ?>.</p>
                <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="POST" action="/reset-password">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <label for="password">New password</label>
                    <input id="password" type="password" name="password" required minlength="6" autocomplete="new-password">
                    <label for="confirm_password">Confirm password</label>
                    <input id="confirm_password" type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
                    <button class="btn" type="submit">Save new password</button>
                </form>
                <div class="links"><a href="/login">Cancel</a></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
