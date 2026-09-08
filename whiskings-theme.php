<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }

if (isset($_GET['theme'])) {
    $req = strtolower(trim((string)$_GET['theme']));
    pbj_save_user_theme($pdo, (int)$_SESSION['user_id'], $req, true);
    header("Location: /settings/theme");
    exit();
}

$current = pbj_theme_id();
$is_sweet = ($current === 'sweet');
$is_neon = ($current === 'neon_diner');
$is_farm = ($current === 'farm');
$is_urban = ($current === 'urban');
$is_coffee = ($current === 'coffee');
$fun_names = pbj_use_fun_names();
$catalog = pbj_theme_catalog();
$available = array_values(array_filter($catalog, static fn($t) => !empty($t['available'])));
$coming = array_values(array_filter($catalog, static fn($t) => empty($t['available'])));
$tokens = pbj_theme_tokens();
$body_class = function_exists('pbj_theme_body_class') ? pbj_theme_body_class() : ('pbj-theme-' . $current);
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo htmlspecialchars($body_class); ?>" data-theme="<?php echo htmlspecialchars($current); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $fun_names ? 'Look & Feel' : 'Theme'; ?> • <?php echo pbj_hub_label('settings'); ?> • ilovepbj ops</title>
    <?php if ($is_neon): ?>
    <style>
        @font-face {
            font-family: 'Warnes';
            src: url('/Fonts/Warnes-Regular.ttf') format('truetype');
            font-weight: 400 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Mouse Memoirs';
            src: url('/Fonts/MouseMemoirs-Regular.ttf') format('truetype');
            font-weight: 400 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'MouseMemoirs';
            src: url('/Fonts/MouseMemoirs-Regular.ttf') format('truetype');
            font-weight: 400 700;
            font-style: normal;
            font-display: swap;
        }
    </style>
    <?php elseif ($is_farm): ?>
    <?php if (function_exists('pbj_render_farm_font_faces')) { pbj_render_farm_font_faces(); } ?>
    <?php elseif ($is_urban): ?>
    <?php if (function_exists('pbj_render_urban_font_faces')) { pbj_render_urban_font_faces(); } ?>
    <?php elseif ($is_coffee): ?>
    <?php if (function_exists('pbj_render_coffee_font_faces')) { pbj_render_coffee_font_faces(); } ?>
    <?php elseif (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body {
            margin: 0; padding-bottom: 100px;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;
            <?php elseif ($is_neon): ?>
                font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif; background: #0A0A0A; color: #F5F5F7;
            <?php elseif ($is_farm): ?>
                font-family: 'Notepen', 'NotepenRegular', Georgia, serif; background: #F3E6D4; color: #2C2416;
            <?php elseif ($is_urban): ?>
                font-family: 'Kelly Slab', 'KellySlab-Regular', Georgia, serif; background: #000000; color: #F5F5F5;
            <?php elseif ($is_coffee): ?>
                font-family: 'Ambery Garden', 'AmberyGarden-Regular', Georgia, serif; background: #F5EDE3; color: #3D2416;
            <?php else: ?>
                font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;
            <?php endif; ?>
        }
        .header {
            <?php if ($is_sweet): ?>background: #E55163; color: white;
            <?php elseif ($is_neon): ?>background: #0D0D0D; color: #FF2ECB; border-bottom: 2px solid #00F0FF; box-shadow: 0 0 18px rgba(255,46,203,0.35);
            <?php elseif ($is_farm): ?>background: #2F6B3A; color: #FFF8EE; border-bottom: 3px solid #E07A2F; box-shadow: 0 6px 18px rgba(47,107,58,0.22);
            <?php elseif ($is_urban): ?>background: #0A0A0A; color: #FFFFFF; border-bottom: 2px solid #FF2D00; box-shadow: 0 0 18px rgba(255,45,0,0.28);
            <?php elseif ($is_coffee): ?>background: #4A2C1A; color: #F5EDE3; border-bottom: 3px solid #C4A484; box-shadow: 0 6px 18px rgba(74,44,26,0.2);
            <?php else: ?>background: #1A2A44; color: white;<?php endif; ?>
            padding: 20px 25px 25px; text-align: center;
        }
        .back-link { display: inline-block; <?php if ($is_neon): ?>color: #00F0FF;<?php elseif ($is_farm): ?>color: #F3E6D4;<?php elseif ($is_urban): ?>color: #FF2D00;<?php elseif ($is_coffee): ?>color: #F5EDE3;<?php else: ?>color: white;<?php endif; ?> text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; font-weight: 700; color: #FF2ECB; text-shadow: 0 0 14px rgba(255,46,203,0.5);
            <?php elseif ($is_farm): ?>font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive; font-weight: 700; color: #FFF8EE;
            <?php elseif ($is_urban): ?>font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif; color: #FF2D00; letter-spacing: 0.06em; text-transform: uppercase;
            <?php elseif ($is_coffee): ?>font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif; font-weight: 700; color: #F5EDE3;
            <?php else: ?>font-family: 'Lora', serif;<?php endif; ?>
            font-size: 2.6rem; margin: 0;
        }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; <?php if ($is_neon): ?>color: #00F0FF;<?php elseif ($is_farm): ?>color: #F3E6D4;<?php elseif ($is_urban): ?>color: #FFFFFF;<?php elseif ($is_coffee): ?>color: #F5EDE3;<?php endif; ?> }
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .intro {
            <?php if ($is_neon): ?>background: #141414; color: #F5F5F7; border: 1px solid #FF2ECB; box-shadow: 0 0 18px rgba(255,46,203,0.15);
            <?php else: ?>background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.08);<?php endif; ?>
            border-radius: 18px; padding: 16px 18px; margin-bottom: 18px; text-align: center; line-height: 1.45;
        }
        .section-label {
            font-size: 0.95rem; opacity: 0.7; margin: 4px 4px 12px;
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163; font-size: 1.2rem; opacity: 1;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB; font-size: 1.15rem; opacity: 1;
            <?php else: ?>font-weight: 600;<?php endif; ?>
        }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px; }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
        .theme-card { display: block; text-decoration: none; color: inherit; border-radius: 20px; padding: 28px 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: 3px solid transparent; transition: all 0.2s; }
        .theme-card.sweet { background: #FCF8EE; color: #3a2f1f; }
        .theme-card.basic { background: #F1EBE4; color: #1A2A44; }
        .theme-card.neon { background: #0A0A0A; color: #F5F5F7; border-color: #333; }
        .theme-card.neon .preview-img { background: #000000; border-color: #FF2ECB; }
        .theme-card.farm { background: #F3E6D4; color: #2C2416; border-color: #D9C7A8; }
        .theme-card.farm .preview-img { background: #FFF8EE; border-color: #E07A2F; }
        .theme-card.farm h2 { font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive; font-weight: 700; color: #C43B2C; font-size: 1.45rem; }
        .theme-card.urban { background: #000000; color: #F5F5F5; border-color: #FF2D00; }
        .theme-card.urban .preview-img { background: #000000; border-color: #FF2D00; }
        .theme-card.urban h2 { font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif; color: #FF2D00; letter-spacing: 0.06em; text-transform: uppercase; font-size: 1.5rem; }
        .theme-card.coffee { background: #F5EDE3; color: #3D2416; border-color: #E8D5C4; }
        .theme-card.coffee .preview-img { background: #FFFAF5; border-color: #C4A484; }
        .theme-card.coffee h2 { font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif; font-weight: 700; color: #8B5A2B; font-size: 1.45rem; }
        .theme-card.active { border-color: <?php echo htmlspecialchars($tokens['primary']); ?>; transform: scale(1.02); <?php if ($is_neon): ?>box-shadow: 0 0 18px rgba(255,46,203,0.35);<?php endif; ?> }
        .theme-card h2 { margin: 0 0 8px; font-size: 1.8rem; }
        .theme-card.sweet h2 { font-family: 'ModernLoveCaps', serif; color: #E55163; }
        .theme-card.basic h2 { font-family: 'Lora', serif; color: #1A2A44; }
        .theme-card.neon h2 { font-family: 'Warnes', system-ui, sans-serif; font-weight: 700; color: #FF2ECB; font-size: 1.35rem; }
        .theme-card p { margin: 0; opacity: 0.8; line-height: 1.4; }
        .swatch { display: flex; gap: 8px; justify-content: center; margin: 16px 0 10px; }
        .dot { width: 22px; height: 22px; border-radius: 50%; border: 2px solid rgba(0,0,0,0.12); box-sizing: border-box; }
        /* Dark swatches need a light edge so black is visible on dark cards */
        .theme-card.neon .dot,
        .theme-card.urban .dot,
        .theme-card.dark-theme .dot {
            border-color: rgba(255,255,255,0.85);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.25);
        }
        .dot.swatch-dark {
            border: 2px solid #FFFFFF !important;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.35);
        }
        .badge { display: inline-block; margin-top: 12px; border-radius: 999px; padding: 6px 12px; font-size: 0.9rem; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_neon): ?>background: #1A1A1A; color: #00F0FF; border: 1px solid #00F0FF;<?php endif; ?> }
        .coming-card {
            <?php if ($is_neon): ?>background: #141414; border: 1px solid #333; color: #F5F5F7;
            <?php else: ?>background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.06);<?php endif; ?>
            border-radius: 18px; padding: 14px;
        }
        .coming-card .preview-img {
            width: 100%; height: 128px; object-fit: contain; display: block;
            border-radius: 14px; margin: 0 auto 10px; background: #FAFAFA;
            border: 1px solid <?php echo $is_sweet ? '#F3E8DD' : ($is_neon ? '#333' : '#E6DFD7'); ?>;
        }
        .coming-card.dark-theme .preview-img {
            background: #000000;
            border-color: #2A2A2A;
        }
        .dark-pill {
            display: inline-block; margin-top: 6px; margin-right: 6px;
            border-radius: 999px; padding: 4px 10px; font-size: 0.72rem;
            background: #1A1A1A; color: #BBE7DA; border: 1px solid #333;
        }
        .coming-card h3 { margin: 0 0 4px; font-size: 1.15rem; <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #6B4A8C;<?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .coming-card p { margin: 0; font-size: 0.9rem; opacity: 0.75; line-height: 1.4; }
        .soon-pill { display: inline-block; margin-top: 10px; border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php elseif ($is_neon): ?>background: #1A1A1A; color: #00F0FF; border: 1px solid #00F0FF;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .theme-card .preview-img {
            width: 80px; height: 80px; object-fit: contain; display: block;
            margin: 0 auto 10px; border-radius: 16px; background: #FFFBFA;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : ($is_neon ? '#FF2ECB' : '#C5D0DE'); ?>;
        }
        .preview {
            <?php if ($is_neon): ?>background: #141414; border: 1px solid #FF2ECB; color: #F5F5F7; box-shadow: 0 0 18px rgba(255,46,203,0.12);
            <?php else: ?>background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.08);<?php endif; ?>
            border-radius: 18px; padding: 20px; margin-bottom: 16px;
        }
        .preview h3 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> margin: 0 0 10px; font-size: 1.4rem; }
        .preview-bar {
            height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; margin-bottom: 12px;
            <?php if ($is_sweet): ?>background: #E55163;
            <?php elseif ($is_neon): ?>background: #0D0D0D; color: #FF2ECB; border: 1px solid #00F0FF; box-shadow: 0 0 12px rgba(0,240,255,0.25); font-family: 'Warnes', system-ui, sans-serif;
            <?php else: ?>background: #1A2A44;<?php endif; ?>
        }
        .btn {
            display: block; text-align: center; border-radius: 14px; padding: 14px 16px; text-decoration: none;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #E55163; color: white;
            <?php elseif ($is_neon): ?>font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif; background: #FF2ECB; color: #0A0A0A; box-shadow: 0 0 18px rgba(255,46,203,0.4);
            <?php else: ?>font-family: 'Lora', serif; background: #1A2A44; color: white;<?php endif; ?>
        }
    </style>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
</head>
<body class="<?php echo htmlspecialchars($body_class); ?>" data-theme="<?php echo htmlspecialchars($current); ?>">
    <div class="header">
        <a href="/settings" class="back-link">← <?php echo pbj_back_to_hub('settings'); ?></a>
        <h1><?php echo $fun_names ? 'Look & Feel' : 'Theme'; ?></h1>
        <p class="subtitle"><?php echo $fun_names ? 'Pick the vibe for your whole ops hub' : 'Choose how the app looks'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php
            $activeName = pbj_theme_display_name($current);
            if ($current === 'neon_diner') {
                echo 'You\'re on <strong>Neon 50s Diner</strong> — dark mode with hot pink &amp; cyan neon on black. Late shift never looked this fun 🍔';
            } elseif ($current === 'farm') {
                echo 'You\'re on <strong>Farm-to-Table</strong> — basil green, tomato red, and carrot on kraft cream. Fresh from the field 🌿';
            } elseif ($current === 'urban') {
                echo 'You\'re on <strong>Modern Urban Edge</strong> — late-night black, white, and electric orange. The city never sleeps 🏙️';
            } elseif ($current === 'coffee') {
                echo 'You\'re on <strong>Coffee Shop Cozy</strong> — espresso browns, latte foam, and pastry gold. Pour one out ☕';
            } elseif ($is_sweet) {
                echo 'You\'re on <strong>Sweet PBJ Vibes</strong> right now — pink headers, dreamy fonts, full PB&amp;J energy 💕 Change anytime; more themes are baking.';
            } else {
                echo 'You\'re on <strong>' . htmlspecialchars($activeName) . '</strong> right now. Change anytime below.';
            }
            ?>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Available now' : 'Available now'; ?></div>
        <div class="grid">
            <?php foreach ($available as $t):
                $active = $current === $t['id'];
                $clsMap = ['basic' => 'basic', 'neon_diner' => 'neon', 'farm' => 'farm', 'urban' => 'urban', 'coffee' => 'coffee', 'sweet' => 'sweet'];
                $cls = $clsMap[$t['id']] ?? 'sweet';
            ?>
            <a href="/settings/theme?theme=<?php echo urlencode($t['id']); ?>" class="theme-card <?php echo $cls; ?><?php echo $active ? ' active' : ''; ?><?php echo !empty($t['dark']) ? ' dark-theme' : ''; ?>">
                <?php if (!empty($t['preview'])): ?>
                <img class="preview-img" src="<?php echo htmlspecialchars($t['preview']); ?>" alt="<?php echo htmlspecialchars($t['name']); ?>">
                <?php else: ?>
                <div style="font-size:2.2rem;"><?php echo htmlspecialchars($t['emoji']); ?></div>
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($t['name']); ?></h2>
                <div class="swatch">
                    <?php foreach ($t['swatches'] as $c):
                        $hex = strtoupper(ltrim(trim($c), '#'));
                        $isNearBlack = in_array($hex, ['000', '000000', '0A0A0A', '050505', '0D0D0D', '1A1A1A', '121212'], true)
                            || (strlen($hex) === 6 && hexdec(substr($hex,0,2)) < 40 && hexdec(substr($hex,2,2)) < 40 && hexdec(substr($hex,4,2)) < 40);
                    ?>
                    <span class="dot<?php echo $isNearBlack ? ' swatch-dark' : ''; ?>" style="background:<?php echo htmlspecialchars($c); ?>;"></span>
                    <?php endforeach; ?>
                </div>
                <p><?php echo htmlspecialchars($t['tagline']); ?></p>
                <?php if (!empty($t['dark'])): ?><span class="dark-pill">Dark mode</span><?php endif; ?>
                <?php if ($active): ?><span class="badge">✓ Active</span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="preview">
            <h3>Live preview</h3>
            <div class="preview-bar"><?php
                if ($is_neon) {
                    echo 'Header sample · Neon 50s Diner';
                } elseif ($is_sweet) {
                    echo 'Header sample · Sweet PBJ Vibes';
                } else {
                    echo 'Header sample · Sleek Simple Style';
                }
            ?></div>
            <p style="margin:0; opacity:0.8; line-height:1.4;">
                <?php if ($is_neon): ?>
                    Dark chrome, hot pink headlines (Warnes), and Mouse Memoirs body type — applied across the hub when this theme is active.
                <?php elseif ($is_sweet): ?>
                    Buttons, cards, and nav all flip with your theme across Showtime, The Heat, Jelly Jar, and Sandwich HQ.
                <?php else: ?>
                    Buttons, cards, and navigation follow this theme across the whole app.
                <?php endif; ?>
            </p>
        </div>
        <a href="/settings" class="btn"><?php echo pbj_back_to_hub('settings'); ?></a>
    </div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        function gate() {
            if (!window.PbjPerms || !PbjPerms.loaded) return;
            if (PbjPerms.can('settings.theme')) return;
            document.querySelectorAll('.theme-card').forEach(function (a) {
                a.style.pointerEvents = 'none';
                a.style.opacity = '0.45';
            });
            var c = document.querySelector('.content');
            if (c && !document.getElementById('wh-theme-denied')) {
                c.insertAdjacentHTML('afterbegin', '<div class="intro" id="wh-theme-denied" style="margin:12px 0;">No permission to change theme.</div>');
            }
        }
        if (window.PbjPerms && PbjPerms.ready) PbjPerms.ready.then(gate);
        document.addEventListener('pbj-perms-ready', gate);
    })();
    </script>
</body>
</html>
