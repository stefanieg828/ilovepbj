<?php
require_once 'config.php';

if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

if (empty($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
    header('Location: /login');
    exit();
}

// Must be approved before picking theme (pending users stay on waiting)
if (!pbj_user_is_approved()) {
    $st = $_SESSION['access_status'] ?? 'pending';
    header('Location: /waiting' . ($st === 'blocked' ? '?blocked=1' : ''));
    exit();
}

// Already chose — go to hub (or saved next URL)
if (pbj_user_has_chosen_theme() && empty($_GET['force'])) {
    $next = pbj_normalize_app_redirect((string)($_SESSION['after_theme_url'] ?? '/home'), '/home');
    unset($_SESSION['after_theme_url']);
    header('Location: ' . $next);
    exit();
}

$error = '';
$catalog = pbj_theme_catalog();
$available = array_values(array_filter($catalog, static fn($t) => !empty($t['available'])));
$coming = array_values(array_filter($catalog, static fn($t) => empty($t['available'])));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = strtolower(trim((string)($_POST['theme'] ?? '')));
    $ok = false;
    foreach ($available as $t) {
        if ($t['id'] === $theme) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        $error = 'Please pick an available theme to start with.';
    } else {
        pbj_save_user_theme($pdo, (int)$_SESSION['user_id'], $theme, true);
        $next = pbj_normalize_app_redirect((string)($_SESSION['after_theme_url'] ?? '/home'), '/home');
        unset($_SESSION['after_theme_url']);
        header('Location: ' . $next);
        exit();
    }
}

$display = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'friend';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose your look · ilovepbj ops</title>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            font-family: 'DreamingOutLoudPro', Georgia, serif;
            background: linear-gradient(165deg, #FCF8EE 0%, #FFF5F6 45%, #F1EBE4 100%);
            color: #3a2f1f;
        }
        .top {
            background: #E55163; color: white; padding: 28px 20px 32px; text-align: center;
            border-radius: 0 0 28px 28px;
            box-shadow: 0 8px 24px rgba(229, 81, 99, 0.25);
        }
        h1 {
            font-family: 'ModernLoveCaps', serif;
            margin: 0; font-size: 2.5rem; line-height: 1.15;
        }
        .top p { margin: 12px auto 0; max-width: 420px; opacity: 0.95; line-height: 1.45; font-size: 1.05rem; }
        .wrap { max-width: 720px; margin: 0 auto; padding: 24px 16px 48px; }
        .hint {
            background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 18px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06); line-height: 1.5; text-align: center;
        }
        .hint strong { color: #E55163; }
        .error {
            background: #FDECEA; color: #B71C1C; border-radius: 14px; padding: 12px 14px;
            margin-bottom: 14px; text-align: center;
        }
        .section-label {
            font-family: 'ModernLoveCaps', serif; color: #E55163;
            font-size: 1.35rem; margin: 8px 4px 12px;
        }
        .grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px;
        }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
        .theme-card {
            position: relative; display: block; width: 100%; text-align: left;
            border: 3px solid transparent; border-radius: 20px; padding: 20px 16px;
            background: white; cursor: pointer; box-shadow: 0 6px 18px rgba(0,0,0,0.07);
            transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
            font-family: inherit; color: inherit;
        }
        .theme-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,0.1); }
        .theme-card.selected { border-color: #E55163; box-shadow: 0 0 0 3px rgba(229, 81, 99, 0.15); }
        .theme-card .emoji { font-size: 2rem; line-height: 1; }
        .theme-card h2 {
            margin: 8px 0 6px; font-size: 1.45rem;
            font-family: 'ModernLoveCaps', serif; color: #E55163;
        }
        .theme-card.basic-look h2 { color: #1A2A44; font-family: 'Lora', Georgia, serif; }
        .theme-card p { margin: 0; opacity: 0.8; line-height: 1.4; font-size: 0.95rem; }
        .swatch { display: flex; gap: 7px; margin: 14px 0 0; }
        .dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid rgba(0,0,0,0.08); }
        .check {
            position: absolute; top: 12px; right: 12px;
            width: 26px; height: 26px; border-radius: 50%;
            border: 2px solid #F3C5CC; background: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; color: transparent;
        }
        .theme-card.selected .check {
            background: #E55163; border-color: #E55163; color: white;
        }
        .coming-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;
        }
        @media (max-width: 560px) { .coming-grid { grid-template-columns: 1fr; } }
        .coming-card {
            border-radius: 18px; padding: 14px; background: white;
            box-shadow: 0 4px 14px rgba(0,0,0,0.05);
            position: relative; overflow: hidden;
        }
        .coming-card .preview-img {
            width: 100%; height: 120px; object-fit: contain;
            border-radius: 14px; background: #FAFAFA;
            display: block; margin: 0 auto 10px;
            border: 1px solid #F0E8E0;
        }
        .coming-card.dark-theme .preview-img {
            background: #000000;
            border-color: #2A2A2A;
        }
        .theme-card.dark-theme {
            background: #0A0A0A; color: #F5F5F7; border-color: #333;
        }
        .theme-card.dark-theme .preview-img {
            background: #000000;
            border-color: #FF2ECB;
        }
        .theme-card.dark-theme[data-theme="urban"] .preview-img {
            border-color: #FF2D00;
        }
        .theme-card.dark-theme h2 { color: #FF2ECB; }
        .theme-card.dark-theme[data-theme="urban"] h2 { color: #FF2D00; }
        .coming-card .dark-pill {
            display: inline-block; margin-top: 6px; margin-right: 6px;
            border-radius: 999px; padding: 4px 10px; font-size: 0.72rem;
            background: #1A1A1A; color: #BBE7DA; border: 1px solid #333;
        }
        .coming-card h3 { margin: 0 0 4px; font-size: 1.15rem; font-family: 'ModernLoveCaps', serif; color: #6B4A8C; }
        .coming-card p { margin: 0; font-size: 0.9rem; opacity: 0.75; line-height: 1.4; }
        .soon-pill {
            display: inline-block; margin-top: 10px;
            border-radius: 999px; padding: 4px 10px; font-size: 0.78rem;
            background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;
        }
        .theme-card .preview-img {
            width: 72px; height: 72px; object-fit: contain;
            border-radius: 16px; display: block; margin: 0 auto 8px;
            background: #FFFBFA; border: 2px solid #F3C5CC;
        }
        .theme-card .emoji { font-size: 2rem; line-height: 1; }
        .btn {
            display: block; width: 100%; border: none; border-radius: 16px;
            padding: 16px; font-size: 1.15rem; cursor: pointer;
            font-family: inherit; background: #E55163; color: white;
            box-shadow: 0 8px 20px rgba(229, 81, 99, 0.3);
        }
        .btn:hover { filter: brightness(0.97); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }
        .foot {
            text-align: center; margin-top: 16px; font-size: 0.95rem;
            opacity: 0.8; line-height: 1.5;
        }
        .foot a { color: #E55163; font-weight: 600; }
    </style>
</head>
<body>
    <div class="top">
        <h1>Pick your vibe</h1>
        <p>Hi <?php echo htmlspecialchars($display); ?>! Choose how ilovepbj ops looks for you. You can change this anytime in settings.</p>
    </div>
    <div class="wrap">
        <div class="hint">
            Start with <strong>Sweet PBJ Vibes</strong> or <strong>Sleek Simple Style</strong> — more looks are on the way.
            Change anytime under <strong>Whiskings → Look &amp; Feel</strong> (Settings → Theme).
        </div>

        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <form method="POST" action="/choose-theme" id="theme-form">
            <div class="section-label">Available now</div>
            <div class="grid">
                <?php foreach ($available as $i => $t): ?>
                <button type="button" class="theme-card<?php echo $t['id'] === 'basic' ? ' basic-look' : ''; ?><?php echo !empty($t['dark']) ? ' dark-theme' : ''; ?><?php echo $i === 0 ? ' selected' : ''; ?>" data-theme="<?php echo htmlspecialchars($t['id']); ?>">
                    <span class="check">✓</span>
                    <?php if (!empty($t['preview'])): ?>
                    <img class="preview-img" src="<?php echo htmlspecialchars($t['preview']); ?>" alt="<?php echo htmlspecialchars($t['name']); ?>">
                    <?php else: ?>
                    <div class="emoji"><?php echo htmlspecialchars($t['emoji']); ?></div>
                    <?php endif; ?>
                    <h2><?php echo htmlspecialchars($t['name']); ?></h2>
                    <p><?php echo htmlspecialchars($t['tagline']); ?></p>
                    <div class="swatch">
                        <?php foreach ($t['swatches'] as $c): ?>
                        <span class="dot" style="background:<?php echo htmlspecialchars($c); ?>;"></span>
                        <?php endforeach; ?>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="theme" id="theme-input" value="<?php echo htmlspecialchars($available[0]['id'] ?? 'sweet'); ?>">

            <?php if (!empty($coming)): ?>
            <div class="section-label">Coming soon</div>
            <div class="coming-grid">
                <?php foreach ($coming as $t): ?>
                <div class="coming-card<?php echo !empty($t['dark']) ? ' dark-theme' : ''; ?>">
                    <?php if (!empty($t['preview'])): ?>
                    <img class="preview-img" src="<?php echo htmlspecialchars($t['preview']); ?>" alt="<?php echo htmlspecialchars($t['name']); ?>">
                    <?php else: ?>
                    <div style="font-size:1.6rem;"><?php echo htmlspecialchars($t['emoji']); ?></div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($t['name']); ?></h3>
                    <p><?php echo htmlspecialchars($t['tagline']); ?></p>
                    <div class="swatch">
                        <?php foreach ($t['swatches'] as $c): ?>
                        <span class="dot" style="background:<?php echo htmlspecialchars($c); ?>;"></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($t['dark'])): ?><span class="dark-pill">Dark mode</span><?php endif; ?>
                    <span class="soon-pill">Coming soon</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <button class="btn" type="submit" id="continue-btn">Continue with Sweet PBJ Vibes ✨</button>
        </form>
        <p class="foot">
            Not ready? You can still <a href="/logout">sign out</a> — your choice waits for next login.
        </p>
    </div>
    <script>
    (function () {
        var labels = {
            sweet: 'Continue with Sweet PBJ Vibes ✨',
            basic: 'Continue with Sleek Simple Style',
            neon_diner: 'Continue with Neon 50s Diner 🍔'
        };
        var cards = document.querySelectorAll('.theme-card');
        var input = document.getElementById('theme-input');
        var btn = document.getElementById('continue-btn');
        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                cards.forEach(function (c) { c.classList.remove('selected'); });
                card.classList.add('selected');
                var id = card.getAttribute('data-theme');
                input.value = id;
                btn.textContent = labels[id] || 'Continue';
            });
        });
    })();
    </script>
</body>
</html>
