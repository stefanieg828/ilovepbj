<?php
require_once 'config.php';

if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

// Archived users need this page to reactivate — don't bounce them to waiting
if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0 && pbj_user_is_approved()) {
    pbj_post_auth_redirect($pdo);
}

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string) ($_POST['login'] ?? ''));
    if ($login === '') {
        $error = 'Enter your username or email.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, email, username, full_name FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Always show the same message (don’t reveal whether the account exists)
            $mailReady = function_exists('pbj_mail_is_configured') && pbj_mail_is_configured();
            $notice = $mailReady
                ? 'If that account exists, we emailed a reset link. Check your inbox and spam folder (the link expires in 1 hour).'
                : 'If that account exists, a reset was prepared. Email delivery is not fully set up on this server yet — ask your platform admin to send you a reset from the User desk, or finish SMTP setup (mail secrets).';

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                $upd = $pdo->prepare(
                    'UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?'
                );
                $upd->execute([$token, $expires, (int) $user['id']]);

                $base = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
                $link = $base . '/reset-password?token=' . urlencode($token);
                $to = (string) ($user['email'] ?? '');
                $name = (string) ($user['full_name'] ?: $user['username']);
                $mailed = false;
                if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
                    $subject = 'Reset your ilovepbj password';
                    $body = "Hi {$name},\n\n"
                        . "Someone requested a password reset for your ilovepbj ops account.\n\n"
                        . "Open this link within 1 hour to choose a new password:\n{$link}\n\n"
                        . "If you did not request this, you can ignore this email.\n\n"
                        . "— ilovepbj ops\n";
                    if (function_exists('pbj_send_mail')) {
                        $mailed = pbj_send_mail($to, $subject, $body);
                    }
                }
                error_log(
                    'password reset prepared for user #' . (int) $user['id']
                    . ' expires ' . $expires
                    . ' mailed=' . ($mailed ? '1' : '0')
                    . ' smtp_configured=' . ($mailReady ? '1' : '0')
                );
            }
        } catch (Throwable $e) {
            error_log('forgot-password: ' . $e->getMessage());
            $error = 'Something went wrong. Try again in a moment.';
            $notice = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password · ilovepbj ops</title>
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
        .card p.lead { margin: 0 0 18px; opacity: 0.8; text-align: center; line-height: 1.45; }
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
        .notice { background: #E8F8F1; color: #1F6B4A; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; line-height: 1.4; }
        .links { text-align: center; margin-top: 18px; font-size: 0.98rem; }
        .links a { color: #E55163; font-weight: 600; }
    </style>
</head>
<body>
    <div class="top">
        <a href="/login">← Back to log in</a>
        <h1>Forgot password</h1>
    </div>
    <div class="wrap">
        <div class="card">
            <p class="lead">Enter the username or email on your account. We’ll email a reset link when mail is configured. Your platform admin can also send a reset from the User desk.</p>
            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
            <?php if (!$notice): ?>
            <form method="POST" action="/forgot-password">
                <label for="login">Username or email</label>
                <input id="login" type="text" name="login" required autocomplete="username"
                    value="<?php echo htmlspecialchars((string)($_POST['login'] ?? '')); ?>">
                <button class="btn" type="submit">Send reset link</button>
            </form>
            <?php endif; ?>
            <div class="links">
                <a href="/login">Back to log in</a>
            </div>
        </div>
    </div>
</body>
</html>
