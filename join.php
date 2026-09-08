<?php
require_once 'config.php';

if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

$error = '';
$success = '';
$housePreview = null;
$codePrefill = strtoupper(trim((string)($_GET['code'] ?? $_POST['restaurant_code'] ?? '')));
$loggedIn = !empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
$needHouse = !empty($_GET['need']);

if ($codePrefill !== '') {
    $housePreview = pbj_find_restaurant_by_code($pdo, $codePrefill);
    if ($housePreview && !empty($_GET['seat'])) {
        $error = pbj_seat_limit_message(pbj_restaurant_seat_status($pdo, (int) $housePreview['id']));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim((string)($_POST['restaurant_code'] ?? '')));
    $join_role = (string)($_POST['join_role'] ?? 'foh');
    $house = pbj_find_restaurant_by_code($pdo, $code);

    if (!$house) {
        $error = 'That restaurant code wasn’t found. Double-check the code from your manager.';
        $codePrefill = $code;
    } elseif (!$loggedIn) {
        // Send them to register with code, or login with code
        $action = ($_POST['action'] ?? 'register') === 'login' ? 'login' : 'register';
        if ($action === 'login') {
            header('Location: /login?code=' . urlencode($code));
            exit();
        }
        header('Location: /register?mode=join&code=' . urlencode($code));
        exit();
    } else {
        $uid = (int)$_SESSION['user_id'];
        $allowed = ['foh', 'boh', 'manager'];
        $memRole = in_array($join_role, $allowed, true) ? $join_role : 'foh';
        try {
            $seatStatus = pbj_restaurant_seat_status($pdo, (int) $house['id']);
            if (empty($seatStatus['ok'])) {
                throw new RuntimeException(pbj_seat_limit_message($seatStatus));
            }
            pbj_join_restaurant($pdo, $uid, (int)$house['id'], $memRole);
            pbj_approve_invite_joiner($pdo, $uid);
            // Don't overwrite owner global role if they already own places
            $cur = $_SESSION['role'] ?? '';
            if ($cur !== 'owner' && $cur !== 'admin') {
                $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$memRole, $uid]);
                $_SESSION['role'] = $memRole;
            }
            $_SESSION['access_status'] = 'approved';
            header('Location: /home?joined=1');
            exit();
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
            $codePrefill = $code;
        } catch (Exception $e) {
            $error = 'Could not join that house. Please try again.';
            $codePrefill = $code;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join with code · ilovepbj ops</title>
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DreamingOutLoudPro', Georgia, serif; background: #FCF8EE; color: #3a2f1f; }
        .top { background: #E55163; color: white; padding: 16px 20px; text-align: center; }
        .top a { color: white; text-decoration: none; opacity: 0.9; font-size: 0.95rem; }
        h1 { font-family: 'ModernLoveCaps', serif; margin: 8px 0 0; font-size: 2.2rem; }
        .wrap { max-width: 480px; margin: 0 auto; padding: 28px 16px 60px; }
        .card { background: white; border-radius: 20px; padding: 26px 22px; box-shadow: 0 8px 28px rgba(0,0,0,0.08); }
        .hint { opacity: 0.8; margin: 0 0 16px; line-height: 1.45; text-align: center; }
        label { display: block; font-size: 0.88rem; opacity: 0.7; margin-bottom: 4px; text-align: left; }
        input, select {
            width: 100%; padding: 12px 14px; margin-bottom: 12px; border: 2px solid #F3C5CC; border-radius: 12px;
            font-size: 1.1rem; font-family: inherit; background: #FFFBF8; color: #3a2f1f;
        }
        input:focus, select:focus { outline: none; border-color: #E55163; }
        .btn {
            display: block; width: 100%; border: none; border-radius: 14px; padding: 14px; font-size: 1.05rem;
            cursor: pointer; font-family: inherit; background: #E55163; color: white; margin-top: 4px; text-decoration: none; text-align: center;
        }
        .btn-secondary { background: white; color: #E55163; border: 2px solid #F3C5CC; margin-top: 10px; }
        .error { background: #FDECEA; color: #B71C1C; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
        .notice { background: #FFF5F6; border: 1px solid #F3C5CC; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; line-height: 1.4; }
        .preview { background: #E8F8F1; color: #1F6B4A; border-radius: 12px; padding: 12px; margin-bottom: 12px; text-align: center; }
        .links { text-align: center; margin-top: 16px; line-height: 1.6; }
        .links a { color: #E55163; font-weight: 600; }
        .actions { display: flex; flex-direction: column; gap: 0; }
    </style>
</head>
<body>
    <div class="top">
        <a href="<?php echo $loggedIn ? '/home' : '/'; ?>">← Back</a>
        <h1>Join a house</h1>
    </div>
    <div class="wrap">
        <div class="card">
            <?php if ($needHouse && $loggedIn): ?>
                <div class="notice">Your account isn’t linked to a restaurant yet. Enter a code from your manager, or <a href="/register?mode=start" style="color:#E55163;font-weight:600;">start a new house</a> (owners).</div>
            <?php endif; ?>

            <p class="hint">Enter the invite code your manager shared. Codes look like <strong>ABCD-1234</strong>.</p>

            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <?php if ($housePreview): ?>
                <div class="preview">Found: <strong><?php echo htmlspecialchars($housePreview['name']); ?></strong></div>
            <?php endif; ?>

            <form method="POST" action="/join">
                <label for="restaurant_code">Restaurant invite code</label>
                <input id="restaurant_code" name="restaurant_code" required placeholder="ABCD-1234" value="<?php echo htmlspecialchars($codePrefill); ?>" style="text-transform:uppercase;letter-spacing:0.08em;text-align:center;font-size:1.25rem;">

                <?php if ($loggedIn): ?>
                    <label for="join_role">I mostly work…</label>
                    <select id="join_role" name="join_role">
                        <option value="foh">FOH / service</option>
                        <option value="boh">BOH / kitchen</option>
                        <option value="manager">Manager / shift lead</option>
                    </select>
                    <button class="btn" type="submit">Join this house</button>
                <?php else: ?>
                    <div class="actions">
                        <button class="btn" type="submit" name="action" value="register">Continue — create account</button>
                        <button class="btn btn-secondary" type="submit" name="action" value="login">I already have an account</button>
                    </div>
                <?php endif; ?>
            </form>

            <div class="links">
                <?php if ($loggedIn): ?>
                    <a href="/home">Skip for now</a>
                <?php else: ?>
                    Starting a restaurant? <a href="/register?mode=start&amp;plan=house">Create a house</a><br>
                    <a href="/">Back to home</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
