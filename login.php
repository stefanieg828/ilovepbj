<?php
require_once 'config.php';

if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
    pbj_post_auth_redirect($pdo);
}

$error = '';
$notice = '';
if (!empty($_GET['registered'])) {
    $notice = 'Account created — log in to open your jar.';
}
if (!empty($_GET['joined'])) {
    $notice = 'You’re in the house! Log in anytime with your username or email.';
}
if (!empty($_GET['logout'])) {
    $notice = 'Signed out. See you next shift.';
}
if (!empty($_GET['reset'])) {
    $notice = 'Password updated. Log in with your new password.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string)($_POST['login'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Enter your username/email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Ensure access_status key exists for older rows
            if (!isset($user['access_status'])) {
                $user['access_status'] = 'pending';
            }
            // Platform admins always approved.
            // Do NOT auto-approve archived (or other) accounts on login — archived users
            // reactivate only via password reset → approved.
            if (pbj_is_platform_admin($user['email'] ?? '')) {
                if (($user['access_status'] ?? '') !== 'approved') {
                    $pdo->prepare("UPDATE users SET access_status = 'approved' WHERE id = ?")->execute([(int)$user['id']]);
                    $user['access_status'] = 'approved';
                }
            }
            try {
                $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([(int)$user['id']]);
            } catch (Throwable $e) {
                // column may not exist in odd envs
            }
            pbj_set_session_user($user);
            // Join with house invite code — staff never hit the pay wall
            $code = strtoupper(trim((string)($_POST['join_code'] ?? $_GET['code'] ?? '')));
            if ($code !== '' && ($user['access_status'] ?? '') !== 'archived') {
                $house = pbj_find_restaurant_by_code($pdo, $code);
                if ($house) {
                    try {
                        pbj_join_restaurant($pdo, (int)$user['id'], (int)$house['id'], 'foh');
                        pbj_approve_invite_joiner($pdo, (int)$user['id']);
                        $_SESSION['access_status'] = 'approved';
                        header('Location: /home?joined=1');
                        exit();
                    } catch (RuntimeException $e) {
                        // Logged in, but house is full / plan blocks invites — show why on join page
                        header('Location: /join?code=' . urlencode($code) . '&seat=1');
                        exit();
                    }
                }
            }
            pbj_post_auth_redirect($pdo);
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}

$codePrefill = trim((string)($_GET['code'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in · ilovepbj ops</title>
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'DreamingOutLoudPro', Georgia, serif; background: #FCF8EE; color: #3a2f1f; }
        .top { background: #E55163; color: white; padding: 16px 20px; text-align: center; }
        .top a { color: white; text-decoration: none; opacity: 0.9; font-size: 0.95rem; }
        .top a:hover { text-decoration: underline; }
        h1 { font-family: 'ModernLoveCaps', serif; margin: 8px 0 0; font-size: 2.4rem; }
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
        .btn:hover { filter: brightness(0.96); }
        .error { background: #FDECEA; color: #B71C1C; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
        .notice { background: #E8F8F1; color: #1F6B4A; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
        .links { text-align: center; margin-top: 18px; font-size: 0.98rem; line-height: 1.6; }
        .links a { color: #E55163; font-weight: 600; }
        .alt { margin-top: 16px; padding-top: 16px; border-top: 1px solid #F3E8DD; text-align: center; font-size: 0.95rem; opacity: 0.85; }
    </style>
</head>
<body>
    <div class="top">
        <a href="/">← Back to home</a>
        <h1>Welcome back</h1>
    </div>
    <div class="wrap">
        <div class="card">
            <p class="lead">Log in to your ops hub</p>
            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
            <form method="POST" action="/login">
                <?php if ($codePrefill !== ''): ?>
                    <input type="hidden" name="join_code" value="<?php echo htmlspecialchars($codePrefill); ?>">
                    <div class="notice" style="margin-bottom:12px;">After login you’ll join code <strong><?php echo htmlspecialchars($codePrefill); ?></strong>.</div>
                <?php endif; ?>
                <label for="login">Username or email</label>
                <input id="login" type="text" name="login" required autocomplete="username" value="<?php echo htmlspecialchars((string)($_POST['login'] ?? '')); ?>">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
                <button class="btn" type="submit">Log in</button>
            </form>
            <div class="links">
                <a href="/forgot-password">Forgot password?</a><br>
                Don’t have an account? <a href="/register<?php echo $codePrefill !== '' ? ('?mode=join&code=' . urlencode($codePrefill)) : ''; ?>">Create one</a>
            </div>
            <div class="alt">
                Have a restaurant code?
                <a href="/join<?php echo $codePrefill !== '' ? ('?code=' . urlencode($codePrefill)) : ''; ?>">Join a house</a>
            </div>
        </div>
    </div>
</body>
</html>
