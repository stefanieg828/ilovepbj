<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

// Theme Switcher
if (isset($_GET['theme'])) {
    pbj_save_user_theme($pdo, (int)($_SESSION['user_id'] ?? 0), (string)$_GET['theme'], true);
    header('Location: /home');
    exit();
}

$theme_id = pbj_theme_id();
$tokens = pbj_theme_tokens();
$hubs = pbj_hub_labels();
$fun_names = pbj_use_fun_names(); // fun hub names for Sweet + Neon
$is_sweet = $fun_names; // legacy: fun copy/labels
$theme_catalog = function_exists('pbj_theme_catalog')
    ? array_values(array_filter(pbj_theme_catalog(), static fn($t) => !empty($t['available'])))
    : [];
$is_classic_sweet = ($theme_id === 'sweet');
$is_neon = ($theme_id === 'neon_diner');
$is_farm = ($theme_id === 'farm');
$is_urban = ($theme_id === 'urban');
$is_coffee = ($theme_id === 'coffee');
$is_basic = ($theme_id === 'basic' || (!$is_classic_sweet && !$is_neon && !$is_farm && !$is_urban && !$is_coffee));
$body_class = function_exists('pbj_theme_body_class') ? pbj_theme_body_class() : ('pbj-theme-' . $theme_id);
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo htmlspecialchars($body_class); ?>" data-theme="<?php echo htmlspecialchars($theme_id); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title>ilovepbj ops · Dashboard</title>
    <link rel="manifest" href="/manifest.webmanifest?v=3">
    <meta name="theme-color" content="#E55163">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ilovepbj">
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>

    <?php if ($is_basic): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php elseif ($is_farm): ?>
    <?php if (function_exists('pbj_render_farm_font_faces')) { pbj_render_farm_font_faces(); } ?>
    <?php elseif ($is_urban): ?>
    <?php if (function_exists('pbj_render_urban_font_faces')) { pbj_render_urban_font_faces(); } ?>
    <?php elseif ($is_coffee): ?>
    <?php if (function_exists('pbj_render_coffee_font_faces')) { pbj_render_coffee_font_faces(); } ?>
    <?php endif; ?>

    <?php if ($is_neon): ?>
    <style id="pbj-neon-fonts">
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
    <?php endif; ?>

    <style>
        @font-face {
            font-family: 'DreamingOutLoudPro';
            src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype');
        }
        @font-face {
            font-family: 'ModernLoveCaps';
            src: url('/Fonts/modern-love-caps.ttf') format('truetype');
        }

        body {
            margin: 0;
            padding-bottom: 90px;
            <?php if ($is_classic_sweet): ?>
                font-family: 'DreamingOutLoudPro', Georgia, serif;
                background: #FCF8EE;
                color: #3a2f1f;
            <?php elseif ($is_neon): ?>
                font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif;
                background: #0A0A0A;
                color: #F5F5F7;
            <?php elseif ($is_farm): ?>
                font-family: 'Notepen', 'NotepenRegular', Georgia, serif;
                font-weight: 700;
                font-synthesis: weight;
                -webkit-text-stroke: 0.45px currentColor;
                text-shadow: 0.35px 0 0 currentColor, -0.35px 0 0 currentColor, 0 0.35px 0 currentColor, 0 -0.35px 0 currentColor;
                background: #F3E6D4;
                color: #2C2416;
                font-size: 1.05rem;
                line-height: 1.55;
            <?php elseif ($is_urban): ?>
                font-family: 'Kelly Slab', 'KellySlab-Regular', Georgia, serif;
                background: #000000;
                color: #F5F5F5;
            <?php elseif ($is_coffee): ?>
                font-family: 'Ambery Garden', 'AmberyGarden-Regular', Georgia, serif;
                font-weight: 400;
                font-synthesis: none;
                letter-spacing: 0.06em;
                background: #F5EDE3;
                color: #3D2416;
                font-size: 1.05rem;
                line-height: 1.6;
            <?php else: ?>
                font-family: 'Lora', Georgia, serif;
                background: #F1EBE4;
                color: #1A2A44;
            <?php endif; ?>
        }

        nav {
            <?php if ($is_classic_sweet): ?>
                background: #E55163;
            <?php elseif ($is_neon): ?>
                background: #0D0D0D;
                border-bottom: 2px solid #00F0FF;
                box-shadow: 0 0 18px rgba(255, 46, 203, 0.35);
            <?php elseif ($is_farm): ?>
                background: #2F6B3A;
                border-bottom: 3px solid #E07A2F;
                box-shadow: 0 6px 18px rgba(47,107,58,0.22);
            <?php elseif ($is_urban): ?>
                background: #0A0A0A;
                border-bottom: 2px solid #FF2D00;
                box-shadow: 0 0 18px rgba(255,45,0,0.28);
            <?php elseif ($is_coffee): ?>
                background: #4A2C1A;
                border-bottom: 3px solid #C4A484;
                box-shadow: 0 6px 18px rgba(74,44,26,0.2);
            <?php else: ?>
                background: #1A2A44;
            <?php endif; ?>
            padding: 16px 30px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-menu {
            display: flex;
            gap: 28px 48px;
            align-items: flex-start;
            justify-content: center;
            flex: 1;
            flex-wrap: nowrap;
        }

        .nav-item {
            <?php if ($is_neon): ?>
                color: #FF2ECB;
                font-family: 'Warnes', system-ui, sans-serif;
                font-weight: 700;
                letter-spacing: 0.02em;
            <?php elseif ($is_farm): ?>
                color: #FFF8EE;
                font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive;
                font-weight: 700;
                letter-spacing: 0.01em;
            <?php elseif ($is_urban): ?>
                color: #FFFFFF;
                font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif;
                font-weight: 400;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            <?php elseif ($is_coffee): ?>
                color: #F5EDE3;
                font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif;
                font-weight: 700;
                letter-spacing: 0.02em;
            <?php elseif ($is_classic_sweet): ?>
                color: white;
                font-family: 'ModernLoveCaps', serif;
            <?php else: ?>
                color: white;
                font-family: 'Lora', serif;
            <?php endif; ?>
            font-size: 1rem;
            line-height: 1.15;
            text-decoration: none;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            width: auto;
            min-width: 96px;
            padding: 10px 12px 12px;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        .nav-item span {
            display: block;
            width: 100%;
            text-align: center;
            margin: 0;
            padding: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-item:hover {
            <?php if ($is_neon): ?>
                background: rgba(0, 240, 255, 0.12);
                box-shadow: 0 0 16px rgba(255, 46, 203, 0.35);
            <?php elseif ($is_farm): ?>
                background: rgba(255, 248, 238, 0.14);
                box-shadow: 0 4px 14px rgba(44,36,22,0.12);
            <?php elseif ($is_urban): ?>
                background: rgba(255, 45, 0, 0.12);
                box-shadow: 0 0 16px rgba(255, 45, 0, 0.3);
            <?php elseif ($is_coffee): ?>
                background: rgba(245, 237, 227, 0.14);
                box-shadow: 0 4px 14px rgba(74,44,26,0.12);
            <?php else: ?>
                background: rgba(255,255,255,0.25);
            <?php endif; ?>
            transform: scale(1.05);
            border-radius: 12px;
        }

        .nav-item img {
            width: 68px;
            height: 68px;
            border-radius: 14px;
            flex-shrink: 0;
            display: block;
            margin: 0 auto 8px;
            object-fit: cover;
            <?php if ($is_neon): ?>
                border: 2px solid rgba(255, 46, 203, 0.9);
                box-shadow: 0 0 16px rgba(255, 46, 203, 0.5), 0 0 22px rgba(0, 240, 255, 0.28);
                filter: drop-shadow(0 0 5px rgba(0, 240, 255, 0.35));
                background: #0A0A0A;
            <?php elseif ($is_farm): ?>
                border: 2px solid #E07A2F;
                box-shadow: 0 4px 14px rgba(44,36,22,0.16);
                background: #F3E6D4;
            <?php elseif ($is_urban): ?>
                border: 2px solid #FF2D00;
                box-shadow: 0 0 14px rgba(255, 45, 0, 0.4);
                background: #0A0A0A;
            <?php elseif ($is_coffee): ?>
                border: 2px solid #C4A484;
                box-shadow: 0 4px 14px rgba(74,44,26,0.14);
                background: #FFFAF5;
            <?php else: ?>
                border: 3px solid white;
                box-shadow: 0 4px 8px rgba(0,0,0,0.25);
            <?php endif; ?>
        }

        /* Heat + Whiskings: same outer box as siblings, zoom art inside */
        .nav-item .nav-ico-zoom {
            /* Match .nav-item img content-box sizing so border sits outside (not inside) */
            width: 68px;
            height: 68px;
            border-radius: 14px;
            margin: 0 auto 8px;
            display: block;
            overflow: hidden;
            flex-shrink: 0;
            box-sizing: content-box;
            <?php if ($is_neon): ?>
                border: 2px solid rgba(255, 46, 203, 0.9);
                box-shadow: 0 0 16px rgba(255, 46, 203, 0.5), 0 0 22px rgba(0, 240, 255, 0.28);
                background: #0A0A0A;
            <?php elseif ($is_farm): ?>
                border: 2px solid #E07A2F;
                box-shadow: 0 4px 14px rgba(44,36,22,0.16);
                background: #F3E6D4;
            <?php elseif ($is_urban): ?>
                border: 2px solid #FF2D00;
                box-shadow: 0 0 14px rgba(255, 45, 0, 0.4);
                background: #0A0A0A;
            <?php elseif ($is_coffee): ?>
                border: 2px solid #C4A484;
                box-shadow: 0 4px 14px rgba(74,44,26,0.14);
                background: #FFFAF5;
            <?php else: ?>
                border: 3px solid white;
                box-shadow: 0 4px 8px rgba(0,0,0,0.25);
            <?php endif; ?>
        }
        .nav-item .nav-ico-zoom img {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            filter: none !important;
            object-fit: cover;
            transform: scale(1.28);
            transform-origin: center center;
        }

        /* Mobile / tablet: always two rows of three hub icons */
        @media (max-width: 900px) {
            nav { padding: 12px 12px 14px; }
            .nav-menu {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 14px 18px;
                width: 100%;
                max-width: 440px;
                margin: 0 auto;
                flex: none;
            }
            .nav-item {
                width: 100%;
                min-width: 0;
                padding: 8px 4px 10px;
                font-size: 0.88rem;
            }
            .nav-item img {
                width: 56px;
                height: 56px;
                margin-bottom: 6px;
            }
            .nav-item .nav-ico-zoom {
                width: 56px;
                height: 56px;
                margin-bottom: 6px;
            }
            .nav-item span {
                white-space: nowrap;
                font-size: 0.82rem;
            }
        }

        .hub-main {
            padding: 48px 20px 100px;
            max-width: 920px;
            margin: 0 auto;
            text-align: center;
        }
        .hub-main h1 {
            <?php if ($is_classic_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php elseif ($is_neon): ?>
                font-family: 'Warnes', system-ui, sans-serif;
                font-weight: 700;
                color: #FF2ECB;
                text-shadow: 0 0 18px rgba(255, 46, 203, 0.55), 0 0 4px rgba(0, 240, 255, 0.35);
                letter-spacing: 0.04em;
            <?php elseif ($is_farm): ?>
                font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive;
                font-weight: 700;
                color: #C43B2C;
                letter-spacing: 0.01em;
            <?php elseif ($is_urban): ?>
                font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif;
                font-weight: 400;
                color: #FF2D00;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                text-shadow: 0 0 14px rgba(255,45,0,0.45);
            <?php elseif ($is_coffee): ?>
                font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif;
                font-weight: 700;
                color: #8B5A2B;
                letter-spacing: 0.01em;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 3.6rem;
            margin: 0;
            line-height: 1.15;
        }
        .hub-main .tagline {
            font-size: 1.4rem;
            margin: 16px 0 28px;
            opacity: 0.9;
            <?php if ($is_neon): ?>
                color: #00F0FF;
                font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif;
                text-shadow: 0 0 10px rgba(0, 240, 255, 0.35);
            <?php elseif ($is_farm): ?>
                color: #2F6B3A;
                font-family: 'Notepen', 'NotepenRegular', Georgia, serif;
            <?php elseif ($is_urban): ?>
                color: #FFFFFF;
                font-family: 'Kelly Slab', 'KellySlab-Regular', Georgia, serif;
                opacity: 0.85;
            <?php elseif ($is_coffee): ?>
                color: #8B5A2B;
                font-family: 'Ambery Garden', 'AmberyGarden-Regular', Georgia, serif;
                font-weight: 400;
                letter-spacing: 0.06em;
            <?php endif; ?>
        }
        .list-complete-card {
            background: white;
            border-radius: 18px;
            padding: 16px 18px;
            margin: 0 0 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: none;
        }
        .list-complete-card.show { display: block; }
        .list-complete-card h2 {
            <?php if ($fun_names): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
            font-size: 1.25rem; margin: 0 0 10px;
        }
        .list-complete-card .lc-item {
            border-left: 4px solid <?php echo $fun_names ? '#E55163' : '#1A2A44'; ?>;
            padding: 10px 12px;
            margin-bottom: 8px;
            border-radius: 0 12px 12px 0;
            background: <?php echo $fun_names ? '#FFF8F9' : '#F4F6FA'; ?>;
        }
        .list-complete-card .lc-item a { color: inherit; text-decoration: none; font-weight: 600; }
        .list-complete-card .lc-meta { font-size: 0.88rem; opacity: 0.7; margin-top: 4px; }
        .jelly-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 28px;
            text-align: center;
        }
        @media (max-width: 560px) {
            .jelly-stats { grid-template-columns: 1fr; }
            .hub-main h1 { font-size: 2.6rem; }
        }
        .jelly-stat {
            <?php if ($is_neon): ?>
                background: #141414;
                border: 1px solid #FF2ECB;
                box-shadow: 0 0 0 1px rgba(255,46,203,0.18), 0 8px 24px rgba(0,0,0,0.5), 0 0 18px rgba(255,46,203,0.15);
                color: #F5F5F7;
            <?php elseif ($is_farm): ?>
                background: #FFFBF5;
                border: 1px solid #D9C7A8;
                box-shadow: 0 6px 18px rgba(44,36,22,0.10);
                color: #2C2416;
            <?php elseif ($is_urban): ?>
                background: #121212;
                border: 1px solid #FF2D00;
                box-shadow: 0 0 0 1px rgba(255,45,0,0.18), 0 10px 28px rgba(0,0,0,0.5);
                color: #F5F5F5;
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5;
                border: 1px solid #E8D5C4;
                box-shadow: 0 8px 20px rgba(74,44,26,0.10);
                color: #3D2416;
            <?php else: ?>
                background: white;
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            <?php endif; ?>
            border-radius: 18px;
            padding: 18px 14px;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .jelly-stat:hover {
            transform: translateY(-4px);
            <?php if ($is_neon): ?>
                box-shadow: 0 0 24px rgba(0, 240, 255, 0.35), 0 12px 28px rgba(0,0,0,0.55);
                border-color: #00F0FF;
            <?php elseif ($is_farm): ?>
                box-shadow: 0 10px 24px rgba(47,107,58,0.16);
                border-color: #E07A2F;
            <?php elseif ($is_urban): ?>
                box-shadow: 0 0 22px rgba(255,45,0,0.35);
                border-color: #FFFFFF;
            <?php elseif ($is_coffee): ?>
                box-shadow: 0 10px 24px rgba(139,90,43,0.16);
                border-color: #C4A484;
            <?php else: ?>
                box-shadow: 0 12px 24px rgba(0,0,0,0.12);
            <?php endif; ?>
        }
        .jelly-stat .num {
            font-size: 1.75rem;
            line-height: 1.1;
            <?php if ($is_classic_sweet): ?>
                color: #E55163;
            <?php elseif ($is_neon): ?>
                color: #FF2ECB;
                font-family: 'Warnes', system-ui, sans-serif;
                font-weight: 700;
                text-shadow: 0 0 12px rgba(255, 46, 203, 0.5);
            <?php elseif ($is_farm): ?>
                color: #C43B2C;
                font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive;
                font-weight: 700;
            <?php elseif ($is_urban): ?>
                color: #FF2D00;
                font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif;
                letter-spacing: 0.04em;
            <?php elseif ($is_coffee): ?>
                color: #8B5A2B;
                font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif;
                font-weight: 700;
            <?php else: ?>
                color: #1A2A44;
            <?php endif; ?>
        }
        .jelly-stat .lbl {
            font-size: 0.95rem;
            opacity: 0.7;
            margin-top: 6px;
            <?php if ($is_neon): ?>color: rgba(245,245,247,0.85); opacity: 1;<?php elseif ($is_farm): ?>color: rgba(44,36,22,0.75); opacity: 1;<?php elseif ($is_urban): ?>color: rgba(245,245,245,0.8); opacity: 1;<?php elseif ($is_coffee): ?>color: rgba(61,36,22,0.75); opacity: 1;<?php endif; ?>
        }
        .jelly-stat .sub {
            font-size: 0.8rem;
            opacity: 0.55;
            margin-top: 4px;
            <?php if ($is_neon): ?>color: #00F0FF; opacity: 0.75;<?php elseif ($is_farm): ?>color: #2F6B3A; opacity: 0.85;<?php elseif ($is_urban): ?>color: #FF2D00; opacity: 0.8;<?php elseif ($is_coffee): ?>color: #8B5A2B; opacity: 0.85;<?php endif; ?>
        }
        .a2hs-wrap {
            text-align: center;
            margin: 0 auto 18px;
            max-width: 420px;
        }
        .a2hs-btn {
            border: none;
            border-radius: 999px;
            padding: 12px 20px;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
            <?php if ($is_neon): ?>
                font-family: 'Mouse Memoirs', Georgia, sans-serif;
                background: #FF2ECB;
                color: #0A0A0A;
            <?php elseif ($is_farm): ?>
                font-family: 'Notepen', Georgia, serif;
                background: #2F6B3A;
                color: #FFF8EE;
            <?php elseif ($is_urban): ?>
                font-family: 'Kelly Slab', Georgia, serif;
                background: #FF2D00;
                color: #fff;
            <?php elseif ($is_coffee): ?>
                font-family: 'Ambery Garden', Georgia, serif;
                background: #4A2C1A;
                color: #F5EDE3;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                font-family: 'DreamingOutLoudPro', Georgia, serif;
                background: #E55163;
                color: white;
            <?php else: ?>
                font-family: 'Lora', Georgia, serif;
                background: #1A2A44;
                color: white;
            <?php endif; ?>
        }
        .a2hs-btn:active { transform: scale(0.98); }
        .a2hs-hint {
            margin: 10px 0 0;
            font-size: 0.9rem;
            line-height: 1.4;
            opacity: 0.85;
            text-align: left;
            <?php if ($is_neon): ?>
                background: #141414; border: 1px solid #00F0FF; color: #F5F5F7;
            <?php else: ?>
                background: white; box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            <?php endif; ?>
            border-radius: 14px;
            padding: 12px 14px;
        }
        .a2hs-hint[hidden], .a2hs-wrap[hidden] { display: none !important; }

        /* —— Star of the House banner (compact, top of home) —— */
        .star-banner {
            display: flex;
            max-width: 640px;
            margin: 0 auto 16px;
            border-radius: 14px;
            padding: 10px 14px;
            text-align: left;
            text-decoration: none;
            color: inherit;
            align-items: center;
            gap: 12px;
            box-sizing: border-box;
            <?php if ($is_neon): ?>
                background: linear-gradient(90deg, rgba(255,46,203,0.18), rgba(0,240,255,0.1));
                border: 1px solid #FF2ECB;
                box-shadow: 0 0 14px rgba(255,46,203,0.22);
            <?php elseif ($is_farm): ?>
                background: linear-gradient(90deg, #FFF1DE, #FFF8EE);
                border: 2px solid #E07A2F;
            <?php elseif ($is_urban): ?>
                background: linear-gradient(90deg, #1A0A08, #121212);
                border: 1px solid #FF2D00;
            <?php elseif ($is_coffee): ?>
                background: linear-gradient(90deg, #F3E6D4, #FFFAF5);
                border: 2px solid #C4A484;
            <?php elseif ($is_classic_sweet): ?>
                background: linear-gradient(90deg, #FFF0F2, #FFFBF5);
                border: 2px solid #F3C5CC;
                box-shadow: 0 4px 12px rgba(229,81,99,0.12);
            <?php else: ?>
                background: linear-gradient(90deg, #EEF2F8, #FFFFFF);
                border: 1px solid #C5D0DE;
                box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            <?php endif; ?>
        }
        .star-banner[hidden] { display: none !important; }
        .star-banner .sb-emoji {
            font-size: 1.45rem; line-height: 1; flex-shrink: 0;
        }
        .star-banner .sb-body { flex: 1; min-width: 0; }
        .star-banner .sb-kicker {
            font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase;
            opacity: 0.7; margin: 0 0 2px; font-weight: 600;
        }
        .star-banner .sb-name {
            margin: 0; font-size: 1.12rem; line-height: 1.2; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            <?php if ($is_classic_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163; font-size: 1.2rem; font-weight: 400;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB; font-weight: 700;
            <?php elseif ($is_farm): ?>color: #2F6B3A;
            <?php elseif ($is_urban): ?>color: #FF2D00;
            <?php elseif ($is_coffee): ?>color: #4A2C1A;
            <?php endif; ?>
        }
        .star-banner .sb-meta {
            margin: 2px 0 0; font-size: 0.82rem; opacity: 0.75;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .star-banner .sb-chevron {
            flex-shrink: 0; font-size: 1.1rem; opacity: 0.55;
        }

        /* —— Home extras: creator news, work music —— */
        .home-extra {
            max-width: 640px;
            margin: 0 auto 18px;
            text-align: left;
        }
        .creator-news {
            border-radius: 12px;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 0.82rem;
            line-height: 1.35;
            <?php if ($is_neon): ?>
                background: rgba(0,240,255,0.08); border: 1px solid rgba(0,240,255,0.35); color: #D8F7FF;
            <?php elseif ($is_farm): ?>
                background: rgba(47,107,58,0.08); border: 1px solid rgba(47,107,58,0.25); color: #2C2416;
            <?php elseif ($is_urban): ?>
                background: rgba(255,45,0,0.08); border: 1px solid rgba(255,45,0,0.35); color: #F5F5F5;
            <?php elseif ($is_coffee): ?>
                background: rgba(74,44,26,0.06); border: 1px solid rgba(196,164,132,0.5); color: #3D2416;
            <?php elseif ($is_classic_sweet): ?>
                background: #FFFBF8; border: 1px solid #F3E8DD; color: #5a4a3a;
            <?php else: ?>
                background: #FAF8F5; border: 1px solid #E6DFD7; color: #1A2A44;
            <?php endif; ?>
        }
        .creator-news[hidden] { display: none !important; }
        .creator-news-head {
            display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px;
        }
        .creator-news-title {
            font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase; opacity: 0.65; margin: 0; font-weight: 600;
        }
        .creator-news-actions { display: flex; gap: 6px; flex-shrink: 0; flex-wrap: wrap; align-items: center; }
        .creator-news-actions button {
            border: none; cursor: pointer; font-size: 0.75rem;
            padding: 4px 10px; border-radius: 999px; color: inherit;
            <?php if ($is_classic_sweet): ?>font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>font-family: inherit;<?php endif; ?>
        }
        .creator-news-actions button.ghost {
            background: transparent; opacity: 0.75;
            border: 1px solid rgba(0,0,0,0.12);
            <?php if ($is_neon || $is_urban): ?>border-color: rgba(255,255,255,0.25);<?php endif; ?>
        }
        .creator-news-actions button.ghost:hover { opacity: 1; }
        .creator-news-actions button.primary {
            <?php if ($is_classic_sweet): ?>background: #E55163; color: white;
            <?php elseif ($is_neon): ?>background: #00F0FF; color: #0A0A0A;
            <?php elseif ($is_farm): ?>background: #2F6B3A; color: #FFF8EE;
            <?php elseif ($is_urban): ?>background: #FF2D00; color: white;
            <?php elseif ($is_coffee): ?>background: #4A2C1A; color: #F5EDE3;
            <?php else: ?>background: #1A2A44; color: white;<?php endif; ?>
        }
        .creator-news-actions button:disabled { opacity: 0.45; cursor: default; }
        .creator-news-body {
            margin: 0; white-space: pre-wrap; word-break: break-word; max-height: 5.4em; overflow: auto;
        }
        .creator-news-body:empty::before {
            content: attr(data-empty);
            opacity: 0.5; font-style: italic;
        }
        .creator-news-edit {
            width: 100%; box-sizing: border-box; min-height: 72px; resize: vertical;
            border-radius: 8px; border: 1px solid rgba(0,0,0,0.12); padding: 8px 10px;
            font-size: 0.85rem; line-height: 1.4; margin-top: 6px; color: inherit;
            <?php if ($is_neon || $is_urban): ?>background: #0A0A0A; border-color: rgba(255,255,255,0.2);
            <?php else: ?>background: rgba(255,255,255,0.85);<?php endif; ?>
            <?php if ($is_classic_sweet): ?>font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>font-family: inherit;<?php endif; ?>
        }
        .creator-news-edit[hidden] { display: none !important; }
        .creator-news-status {
            margin: 6px 0 0; font-size: 0.75rem; opacity: 0.7; min-height: 1em;
        }
        .creator-news-status.ok { <?php if ($is_classic_sweet): ?>color: #1F6B4A;<?php else: ?>color: #2E9B63;<?php endif; ?> opacity: 1; }
        .creator-news-status.err { color: #B71C1C; opacity: 1; }
        .creator-news-hint {
            margin: 4px 0 0; font-size: 0.72rem; opacity: 0.6; line-height: 1.3;
        }
        .creator-news-hint[hidden] { display: none !important; }
        .work-music {
            border-radius: 14px;
            padding: 10px 12px;
            margin-bottom: 12px;
            <?php if ($is_neon): ?>
                background: #141414; border: 1px solid #00F0FF; color: #F5F5F7;
            <?php elseif ($is_farm): ?>
                background: #FFF8EE; border: 1px solid #C9B89A; color: #2C2416;
            <?php elseif ($is_urban): ?>
                background: #121212; border: 1px solid #444; color: #F5F5F5;
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5; border: 1px solid #C4A484; color: #3D2416;
            <?php elseif ($is_classic_sweet): ?>
                background: white; border: 1px solid #F3E8DD; color: #3a2f1f;
                box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            <?php else: ?>
                background: white; border: 1px solid #E6DFD7; color: #1A2A44;
                box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            <?php endif; ?>
        }
        .work-music.is-collapsed { padding: 8px 12px; }
        .work-music .wm-head {
            display: flex; align-items: center; gap: 8px; width: 100%;
            margin: 0; padding: 0; border: none; background: transparent;
            color: inherit; cursor: pointer; text-align: left;
            font: inherit; min-height: 1.6em;
        }
        .work-music .wm-head:focus-visible {
            outline: 2px solid currentColor; outline-offset: 2px; border-radius: 8px;
        }
        .work-music .wm-head-text {
            flex: 1; min-width: 0;
            display: flex; align-items: baseline; gap: 6px;
            white-space: nowrap; overflow: hidden;
        }
        .work-music .wm-head-label {
            flex-shrink: 0; font-size: 0.95rem; opacity: 0.9; font-weight: 600;
            <?php if ($is_classic_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163; font-size: 1.02rem;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #00F0FF;
            <?php endif; ?>
        }
        .work-music .wm-head-now {
            flex: 1; min-width: 0; font-size: 0.82rem; opacity: 0.7; font-weight: 400;
            overflow: hidden; text-overflow: ellipsis;
        }
        .work-music .wm-head-now:empty { display: none; }
        .work-music .wm-chevron {
            flex-shrink: 0; width: 1.4em; text-align: center;
            font-size: 0.75rem; opacity: 0.7; transition: transform 0.18s ease;
            line-height: 1;
        }
        .work-music:not(.is-collapsed) .wm-chevron { transform: rotate(180deg); }
        .work-music .wm-body { margin-top: 10px; }
        .work-music.is-collapsed .wm-body { display: none; }
        .work-music h2 {
            margin: 0 0 4px; font-size: 0.95rem; opacity: 0.85;
            <?php if ($is_classic_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163; font-size: 1.05rem;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #00F0FF;
            <?php else: ?>font-weight: 600;<?php endif; ?>
        }
        .work-music .wm-hint { margin: 0 0 10px; font-size: 0.78rem; opacity: 0.7; line-height: 1.35; }
        .work-music .wm-services {
            display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px;
        }
        .work-music .wm-svc {
            border: none; border-radius: 999px; padding: 6px 10px; font-size: 0.78rem; cursor: pointer;
            text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 4px;
            <?php if ($is_neon || $is_urban): ?>background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.18);
            <?php elseif ($is_classic_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC; font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>background: rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.1);<?php endif; ?>
        }
        .work-music .wm-svc:hover { opacity: 0.92; transform: translateY(-1px); }
        .work-music .wm-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
        .work-music input[type="url"],
        .work-music input[type="text"] {
            flex: 1; min-width: 140px; box-sizing: border-box;
            border-radius: 10px; border: 1px solid rgba(0,0,0,0.12);
            padding: 8px 10px; font-size: 0.88rem; color: inherit;
            <?php if ($is_neon || $is_urban): ?>background: #0A0A0A; border-color: rgba(255,255,255,0.2);
            <?php else: ?>background: rgba(255,255,255,0.85);<?php endif; ?>
            <?php if ($is_classic_sweet): ?>font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>font-family: inherit;<?php endif; ?>
        }
        .work-music .wm-btn {
            border: none; border-radius: 10px; padding: 8px 12px; font-size: 0.86rem; cursor: pointer;
            <?php if ($is_classic_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #E55163; color: white;
            <?php elseif ($is_neon): ?>font-family: 'Mouse Memoirs', Georgia, sans-serif; background: #00F0FF; color: #0A0A0A;
            <?php elseif ($is_farm): ?>background: #2F6B3A; color: #FFF8EE;
            <?php elseif ($is_urban): ?>background: #FF2D00; color: white;
            <?php elseif ($is_coffee): ?>background: #4A2C1A; color: #F5EDE3;
            <?php else: ?>background: #1A2A44; color: white;<?php endif; ?>
        }
        .work-music .wm-btn.ghost {
            <?php if ($is_neon || $is_urban): ?>background: transparent; border: 1px solid rgba(255,255,255,0.25); color: inherit;
            <?php else: ?>background: transparent; border: 1px solid rgba(0,0,0,0.12); color: inherit;<?php endif; ?>
        }
        .work-music .wm-btn:disabled { opacity: 0.4; cursor: default; }
        .work-music .wm-now {
            display: flex; align-items: center; gap: 10px; margin: 8px 0 6px;
            min-height: 2.4em;
        }
        .work-music .wm-now-ico {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
            <?php if ($is_classic_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;
            <?php elseif ($is_neon): ?>background: #0A0A0A; border: 1px solid #00F0FF;
            <?php else: ?>background: rgba(0,0,0,0.05);<?php endif; ?>
        }
        .work-music .wm-now-meta { flex: 1; min-width: 0; }
        .work-music .wm-now-title {
            margin: 0; font-size: 0.95rem; font-weight: 600; line-height: 1.25;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .work-music .wm-now-sub {
            margin: 2px 0 0; font-size: 0.75rem; opacity: 0.7;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .work-music .wm-controls {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin: 8px 0 10px;
        }
        .work-music .wm-ctrl {
            width: 44px; height: 44px; border-radius: 50%; border: none; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.15rem; line-height: 1; color: inherit;
            <?php if ($is_neon || $is_urban): ?>background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            <?php elseif ($is_classic_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;
            <?php else: ?>background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.08);<?php endif; ?>
        }
        .work-music .wm-ctrl.primary {
            width: 54px; height: 54px; font-size: 1.35rem;
            <?php if ($is_classic_sweet): ?>background: #E55163; color: white; border-color: #E55163;
            <?php elseif ($is_neon): ?>background: #00F0FF; color: #0A0A0A; border-color: #00F0FF;
            <?php elseif ($is_farm): ?>background: #2F6B3A; color: #FFF8EE; border-color: #2F6B3A;
            <?php elseif ($is_urban): ?>background: #FF2D00; color: white; border-color: #FF2D00;
            <?php elseif ($is_coffee): ?>background: #4A2C1A; color: #F5EDE3; border-color: #4A2C1A;
            <?php else: ?>background: #1A2A44; color: white; border-color: #1A2A44;<?php endif; ?>
        }
        .work-music .wm-ctrl:disabled { opacity: 0.35; cursor: default; }
        .work-music .wm-player { margin-top: 4px; }
        .work-music .wm-player iframe,
        .work-music .wm-player #wm-yt-host {
            width: 100%; border: 0; border-radius: 10px; display: block; background: #000;
        }
        .work-music .wm-player-actions {
            display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; align-items: center;
        }
        .work-music .wm-open {
            display: inline-block; font-size: 0.8rem; color: inherit; opacity: 0.9;
        }
        .work-music .wm-note {
            margin: 6px 0 0; font-size: 0.72rem; opacity: 0.65; line-height: 1.3;
        }
        .work-music .wm-queue {
            margin-top: 10px; border-top: 1px solid rgba(0,0,0,0.08); padding-top: 8px;
            <?php if ($is_neon || $is_urban): ?>border-top-color: rgba(255,255,255,0.12);<?php endif; ?>
        }
        .work-music .wm-queue-head {
            display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 6px;
        }
        .work-music .wm-queue-title { margin: 0; font-size: 0.78rem; font-weight: 600; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.03em; }
        .work-music .wm-qitem {
            display: flex; align-items: center; gap: 8px; padding: 7px 6px; border-radius: 10px;
            cursor: pointer; font-size: 0.85rem;
        }
        .work-music .wm-qitem:hover {
            <?php if ($is_neon || $is_urban): ?>background: rgba(255,255,255,0.06);
            <?php else: ?>background: rgba(0,0,0,0.04);<?php endif; ?>
        }
        .work-music .wm-qitem.active {
            <?php if ($is_classic_sweet): ?>background: #FFF5F6;
            <?php elseif ($is_neon): ?>background: rgba(0,240,255,0.1);
            <?php else: ?>background: rgba(0,0,0,0.06);<?php endif; ?>
        }
        .work-music .wm-qitem .wm-q-label {
            flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .work-music .wm-qitem .wm-q-badge {
            font-size: 0.68rem; opacity: 0.7; flex-shrink: 0; text-transform: capitalize;
        }
        .work-music .wm-qitem .wm-q-del {
            border: none; background: transparent; cursor: pointer; opacity: 0.55; font-size: 0.95rem; color: inherit; padding: 2px 6px;
        }
        .work-music .wm-qitem .wm-q-del:hover { opacity: 1; }
        .work-music .wm-empty { font-size: 0.82rem; opacity: 0.65; padding: 6px 2px; }
        .platform-desk {
            max-width: 640px;
            margin: 0 auto 18px;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 0.92rem;
            line-height: 1.4;
            <?php if ($is_neon): ?>
                background: #141414; border: 1px solid #00F0FF; color: #F5F5F7;
            <?php elseif ($is_classic_sweet): ?>
                background: #FFF5F6; border: 1px solid #F3C5CC; color: #3a2f1f;
            <?php else: ?>
                background: white; box-shadow: 0 3px 12px rgba(0,0,0,0.06);
            <?php endif; ?>
        }
        .platform-desk a { color: inherit; font-weight: 600; }

        /* —— Optional home shortcuts (up to 4) —— */
        .home-shortcuts {
            margin: 0 auto 28px;
            max-width: 640px;
            text-align: center;
        }
        .home-shortcuts-head {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .home-shortcuts-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
            opacity: 0.85;
            letter-spacing: 0.02em;
        }
        .home-shortcuts-edit {
            border: none;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.88rem;
            cursor: pointer;
            opacity: 0.9;
            <?php if ($is_neon): ?>
                font-family: 'Mouse Memoirs', Georgia, sans-serif;
                background: #141414;
                color: #00F0FF;
                border: 1px solid #00F0FF;
            <?php elseif ($is_farm): ?>
                font-family: 'Notepen', Georgia, serif;
                background: #FFF8EE;
                color: #2F6B3A;
                border: 1px solid #E07A2F;
            <?php elseif ($is_urban): ?>
                font-family: 'Kelly Slab', Georgia, serif;
                background: #121212;
                color: #FF2D00;
                border: 1px solid #FF2D00;
            <?php elseif ($is_coffee): ?>
                font-family: 'Ambery Garden', Georgia, serif;
                background: #FFFAF5;
                color: #8B5A2B;
                border: 1px solid #C4A484;
            <?php elseif ($is_classic_sweet): ?>
                font-family: 'DreamingOutLoudPro', Georgia, serif;
                background: #FFF5F6;
                color: #E55163;
                border: 1px solid #F3C5CC;
            <?php else: ?>
                font-family: 'Lora', Georgia, serif;
                background: #EEF2F8;
                color: #1A2A44;
                border: 1px solid #C5D0DE;
            <?php endif; ?>
        }
        .home-shortcuts-edit:hover { opacity: 1; transform: translateY(-1px); }
        .home-shortcuts-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }
        @media (max-width: 560px) {
            .home-shortcuts-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .home-shortcut {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 108px;
            padding: 14px 10px 12px;
            border-radius: 18px;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
            <?php if ($is_neon): ?>
                background: #141414;
                border: 1px solid #FF2ECB;
                box-shadow: 0 0 0 1px rgba(255,46,203,0.18), 0 8px 24px rgba(0,0,0,0.5);
            <?php elseif ($is_farm): ?>
                background: #FFFBF5;
                border: 1px solid #D9C7A8;
                box-shadow: 0 6px 18px rgba(44,36,22,0.10);
            <?php elseif ($is_urban): ?>
                background: #121212;
                border: 1px solid #FF2D00;
                box-shadow: 0 0 0 1px rgba(255,45,0,0.18), 0 10px 28px rgba(0,0,0,0.5);
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5;
                border: 1px solid #E8D5C4;
                box-shadow: 0 8px 20px rgba(74,44,26,0.10);
            <?php else: ?>
                background: white;
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            <?php endif; ?>
        }
        a.home-shortcut:hover {
            transform: translateY(-3px);
            <?php if ($is_neon): ?>
                box-shadow: 0 0 24px rgba(0, 240, 255, 0.35), 0 12px 28px rgba(0,0,0,0.55);
                border-color: #00F0FF;
            <?php elseif ($is_farm): ?>
                box-shadow: 0 10px 24px rgba(47,107,58,0.16);
                border-color: #E07A2F;
            <?php elseif ($is_urban): ?>
                box-shadow: 0 0 22px rgba(255,45,0,0.35);
            <?php elseif ($is_coffee): ?>
                box-shadow: 0 10px 24px rgba(139,90,43,0.16);
                border-color: #C4A484;
            <?php else: ?>
                box-shadow: 0 12px 24px rgba(0,0,0,0.12);
            <?php endif; ?>
        }
        .home-shortcut.is-empty {
            border-style: dashed;
            opacity: 0.72;
            cursor: pointer;
            <?php if ($is_neon): ?>
                border-color: rgba(0,240,255,0.45);
                background: rgba(20,20,20,0.6);
            <?php elseif ($is_farm): ?>
                border-color: #E07A2F;
            <?php elseif ($is_urban): ?>
                border-color: rgba(255,45,0,0.5);
            <?php elseif ($is_coffee): ?>
                border-color: #C4A484;
            <?php else: ?>
                border: 2px dashed #C5D0DE;
                background: rgba(255,255,255,0.55);
            <?php endif; ?>
        }
        .home-shortcut .sc-ico {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 1.55rem;
            line-height: 1;
            flex-shrink: 0;
        }
        .home-shortcut .sc-ico img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .home-shortcut .sc-label {
            font-size: 0.82rem;
            line-height: 1.2;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .home-shortcut .sc-remove {
            position: absolute;
            top: 4px;
            right: 6px;
            width: 24px;
            height: 24px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-size: 0.95rem;
            line-height: 1;
            padding: 0;
            display: none;
            align-items: center;
            justify-content: center;
            <?php if ($is_neon): ?>
                background: #FF2ECB; color: #0A0A0A;
            <?php elseif ($is_farm): ?>
                background: #C43B2C; color: #FFF8EE;
            <?php elseif ($is_urban): ?>
                background: #FF2D00; color: #fff;
            <?php elseif ($is_coffee): ?>
                background: #8B5A2B; color: #F5EDE3;
            <?php else: ?>
                background: #E55163; color: #fff;
            <?php endif; ?>
        }
        .home-shortcuts.is-editing .home-shortcut .sc-remove { display: flex; }
        .home-shortcuts-empty {
            margin: 0 0 10px;
            font-size: 0.9rem;
            opacity: 0.7;
            line-height: 1.4;
        }
        .home-shortcuts-empty[hidden] { display: none !important; }
        .home-shortcuts-sync {
            margin: 0 0 10px;
            font-size: 0.8rem;
            opacity: 0.65;
            line-height: 1.3;
            min-height: 1.1em;
        }
        .home-shortcuts-sync[hidden] { display: none !important; }
        .home-shortcuts-sync[data-kind="offline"] { opacity: 0.85; }
        .home-shortcut.sc-draggable {
            cursor: grab;
            touch-action: none;
            user-select: none;
            -webkit-user-select: none;
        }
        .home-shortcut.sc-draggable:active { cursor: grabbing; }
        .home-shortcut.dragging {
            opacity: 0.55;
            transform: scale(0.98);
            z-index: 5;
        }
        .home-shortcut .sc-grip {
            position: absolute;
            top: 6px;
            left: 8px;
            font-size: 0.7rem;
            opacity: 0.35;
            letter-spacing: -1px;
            pointer-events: none;
            line-height: 1;
        }
        .home-shortcuts-reorder-hint {
            margin: 8px 0 0;
            font-size: 0.8rem;
            opacity: 0.55;
        }
        .home-shortcuts-reorder-hint[hidden] { display: none !important; }

        /* Picker modal */
        .sc-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 200;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }
        @media (min-width: 640px) {
            .sc-modal-backdrop { align-items: center; }
        }
        .sc-modal-backdrop[hidden] { display: none !important; }
        .sc-modal {
            width: 100%;
            max-width: 480px;
            max-height: min(78vh, 640px);
            overflow: auto;
            border-radius: 20px;
            padding: 18px 16px 20px;
            text-align: left;
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
            <?php if ($is_neon): ?>
                background: #101010;
                border: 1px solid #FF2ECB;
                color: #F5F5F7;
            <?php elseif ($is_farm): ?>
                background: #FFFBF5;
                border: 1px solid #D9C7A8;
                color: #2C2416;
            <?php elseif ($is_urban): ?>
                background: #121212;
                border: 1px solid #FF2D00;
                color: #F5F5F5;
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5;
                border: 1px solid #E8D5C4;
                color: #3D2416;
            <?php else: ?>
                background: #fff;
                color: #1A2A44;
            <?php endif; ?>
        }
        .sc-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }
        .sc-modal-head h2 {
            margin: 0;
            font-size: 1.25rem;
        }
        .sc-modal-close {
            border: none;
            background: transparent;
            font-size: 1.4rem;
            cursor: pointer;
            line-height: 1;
            opacity: 0.7;
            color: inherit;
            padding: 4px 8px;
        }
        .sc-modal-close:hover { opacity: 1; }
        .sc-modal-hint {
            margin: 0 0 14px;
            font-size: 0.88rem;
            opacity: 0.7;
            line-height: 1.4;
        }
        .sc-group-label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.55;
            margin: 14px 0 8px;
        }
        .sc-picker-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .sc-picker-item {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            text-align: left;
            border: none;
            border-radius: 14px;
            padding: 10px 12px;
            cursor: pointer;
            font: inherit;
            color: inherit;
            <?php if ($is_neon): ?>
                background: #1A1A1A;
            <?php elseif ($is_farm): ?>
                background: #F3E6D4;
            <?php elseif ($is_urban): ?>
                background: #1A1A1A;
            <?php elseif ($is_coffee): ?>
                background: #F5EDE3;
            <?php else: ?>
                background: #F6F4F1;
            <?php endif; ?>
        }
        .sc-picker-item:hover { filter: brightness(1.05); }
        .sc-picker-item.is-selected {
            opacity: 0.45;
            cursor: default;
        }
        .sc-picker-item .sc-ico {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .sc-picker-item .sc-ico img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sc-picker-item .sc-name { font-weight: 600; font-size: 0.95rem; flex: 1; }
        .sc-picker-item .sc-check { font-size: 0.9rem; opacity: 0.7; }

        .dash-logo-wrap {
            text-align: center;
            margin: 28px auto 6px;
            padding: 0 16px;
            background: transparent;
        }
        .dash-logo-wrap img {
            display: block;
            /* ~30% smaller than previous 300px / 78vw */
            width: min(210px, 55vw);
            height: auto;
            margin: 0 auto;
            object-fit: contain;
            background: transparent;
            border-radius: 0;
            /* sticker float — no box/card behind the art */
            filter: drop-shadow(0 5px 12px rgba(0, 0, 0, 0.14));
            -webkit-user-drag: none;
        }
        /* dark themes: slightly stronger lift so mint border still reads */
        .pbj-theme-neon_diner .dash-logo-wrap img,
        .pbj-theme-urban .dash-logo-wrap img {
            filter: drop-shadow(0 0 12px rgba(255, 46, 203, 0.25))
                    drop-shadow(0 8px 18px rgba(0, 0, 0, 0.55));
        }
        .ideas-blurb {
            text-align: center;
            max-width: 420px;
            margin: 14px auto 8px;
            padding: 0 12px;
            font-size: 0.92rem;
            line-height: 1.45;
            opacity: 0.82;
            <?php if ($is_neon): ?>
                color: rgba(245,245,247,0.88);
            <?php elseif ($is_farm): ?>
                color: rgba(44,36,22,0.8);
            <?php elseif ($is_urban): ?>
                color: rgba(245,245,245,0.85);
            <?php elseif ($is_coffee): ?>
                color: rgba(61,36,22,0.8);
            <?php endif; ?>
        }
        .ideas-blurb a {
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid currentColor;
            <?php if ($is_neon): ?>
                color: #00F0FF;
            <?php elseif ($is_farm): ?>
                color: #2F6B3A;
            <?php elseif ($is_urban): ?>
                color: #FF2D00;
            <?php elseif ($is_coffee): ?>
                color: #8B5A2B;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                color: #E55163;
            <?php else: ?>
                color: #1A2A44;
            <?php endif; ?>
        }
        .ideas-blurb a:hover { opacity: 0.9; }
        .legal-blurb {
            text-align: center;
            max-width: 420px;
            margin: 10px auto 12px;
            padding: 0 12px;
            font-size: 0.88rem;
            line-height: 1.5;
            opacity: 0.75;
        }
        .legal-blurb a {
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid currentColor;
            <?php if ($is_neon): ?>
                color: #00F0FF;
            <?php elseif ($is_farm): ?>
                color: #2F6B3A;
            <?php elseif ($is_urban): ?>
                color: #FF2D00;
            <?php elseif ($is_coffee): ?>
                color: #8B5A2B;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                color: #E55163;
            <?php else: ?>
                color: #1A2A44;
            <?php endif; ?>
        }
        .legal-blurb a:hover { opacity: 0.9; }
        .theme-quick {
            max-width: 920px;
            margin: 22px auto 8px;
            padding: 18px 16px 16px;
            border-radius: 20px;
            text-align: center;
            <?php if ($is_neon): ?>
                background: #141414;
                border: 2px solid rgba(255, 46, 203, 0.45);
                box-shadow: 0 0 18px rgba(0, 240, 255, 0.12);
            <?php elseif ($is_farm): ?>
                background: #FFF8EE;
                border: 2px solid #D9C7A8;
                box-shadow: 0 6px 16px rgba(47, 107, 58, 0.1);
            <?php elseif ($is_urban): ?>
                background: #0A0A0A;
                border: 2px solid rgba(255, 45, 0, 0.5);
                box-shadow: 0 0 16px rgba(255, 45, 0, 0.15);
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5;
                border: 2px solid #E8D5C4;
                box-shadow: 0 6px 16px rgba(74, 44, 26, 0.1);
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                background: #fff;
                border: 2px solid #F3C5CC;
                box-shadow: 0 6px 16px rgba(0,0,0,0.06);
            <?php else: ?>
                background: #fff;
                border: 2px solid #C5D0DE;
                box-shadow: 0 6px 16px rgba(0,0,0,0.06);
            <?php endif; ?>
        }
        .theme-quick h2 {
            margin: 0 0 6px;
            font-size: 1.35rem;
            <?php if ($is_classic_sweet || $fun_names): ?>
                font-family: 'ModernLoveCaps', serif; color: #E55163;
            <?php elseif ($is_neon): ?>
                font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB; font-weight: 700;
            <?php elseif ($is_farm): ?>
                font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive; color: #C43B2C; font-weight: 700;
            <?php elseif ($is_urban): ?>
                font-family: 'Rafika', Impact, sans-serif; color: #FF2D00; letter-spacing: 0.05em; text-transform: uppercase;
            <?php elseif ($is_coffee): ?>
                font-family: 'Coffee Town', Georgia, serif; color: #8B5A2B; font-weight: 700;
            <?php else: ?>
                font-family: 'Lora', serif; color: #1A2A44;
            <?php endif; ?>
        }
        .theme-quick .tq-lead {
            margin: 0 0 14px;
            font-size: 0.95rem;
            opacity: 0.8;
            line-height: 1.4;
        }
        .theme-quick-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        @media (max-width: 720px) {
            .theme-quick-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 420px) {
            .theme-quick-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
        }
        .theme-quick-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: inherit;
            border-radius: 14px;
            padding: 12px 8px 10px;
            border: 2px solid transparent;
            transition: transform 0.15s, border-color 0.15s, box-shadow 0.15s;
            <?php if ($is_neon): ?>
                background: #1A1A1A;
            <?php elseif ($is_farm): ?>
                background: #F3E6D4;
            <?php elseif ($is_urban): ?>
                background: #121212;
            <?php elseif ($is_coffee): ?>
                background: #F5EDE3;
            <?php else: ?>
                background: #FCF8EE;
            <?php endif; ?>
        }
        .theme-quick-card:hover {
            transform: translateY(-2px);
            <?php if ($is_neon): ?>
                border-color: #00F0FF;
                box-shadow: 0 0 12px rgba(0, 240, 255, 0.25);
            <?php elseif ($is_urban): ?>
                border-color: #FF2D00;
            <?php elseif ($is_farm): ?>
                border-color: #E07A2F;
            <?php elseif ($is_coffee): ?>
                border-color: #C4A484;
            <?php else: ?>
                border-color: #E55163;
            <?php endif; ?>
        }
        .theme-quick-card.is-active {
            <?php if ($is_neon): ?>
                border-color: #FF2ECB;
                box-shadow: 0 0 14px rgba(255, 46, 203, 0.3);
            <?php elseif ($is_urban): ?>
                border-color: #FF2D00;
            <?php elseif ($is_farm): ?>
                border-color: #2F6B3A;
            <?php elseif ($is_coffee): ?>
                border-color: #8B5A2B;
            <?php else: ?>
                border-color: #E55163;
            <?php endif; ?>
        }
        .theme-quick-card .tq-preview {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            object-fit: cover;
            display: block;
            border: 2px solid rgba(0,0,0,0.08);
            background: #fff;
        }
        .theme-quick-card.is-dark .tq-preview {
            border-color: rgba(255,255,255,0.35);
            background: #000;
        }
        .theme-quick-card .tq-name {
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.25;
        }
        .theme-quick-card .tq-swatches {
            display: flex;
            gap: 4px;
            justify-content: center;
        }
        .theme-quick-card .tq-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.15);
            box-sizing: border-box;
        }
        .theme-quick-card.is-dark .tq-dot {
            border-color: rgba(255,255,255,0.7);
        }
        .theme-quick-card .tq-badge {
            font-size: 0.72rem;
            border-radius: 999px;
            padding: 2px 8px;
            <?php if ($is_neon): ?>
                background: #0D0D0D; color: #00F0FF; border: 1px solid #00F0FF;
            <?php elseif ($is_urban): ?>
                background: #000; color: #FF2D00; border: 1px solid #FF2D00;
            <?php elseif ($is_farm): ?>
                background: #E5F0E7; color: #2F6B3A;
            <?php elseif ($is_coffee): ?>
                background: #EFE4D8; color: #4A2C1A;
            <?php else: ?>
                background: #FFF5F6; color: #E55163;
            <?php endif; ?>
        }
        .theme-quick-more {
            display: inline-block;
            margin-top: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid currentColor;
            <?php if ($is_neon): ?>
                color: #00F0FF;
            <?php elseif ($is_farm): ?>
                color: #2F6B3A;
            <?php elseif ($is_urban): ?>
                color: #FF2D00;
            <?php elseif ($is_coffee): ?>
                color: #8B5A2B;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                color: #E55163;
            <?php else: ?>
                color: #1A2A44;
            <?php endif; ?>
        }
        .theme-quick-more:hover { opacity: 0.88; }
        .welcome-banner {
            <?php if ($is_neon): ?>
                background: #141414;
                border: 2px dashed #00F0FF;
                box-shadow: 0 0 18px rgba(0, 240, 255, 0.15);
                color: #F5F5F7;
            <?php elseif ($is_farm): ?>
                background: #FFFBF5;
                border: 2px dashed #E07A2F;
                box-shadow: 0 6px 18px rgba(44,36,22,0.10);
                color: #2C2416;
            <?php elseif ($is_urban): ?>
                background: #121212;
                border: 2px dashed #FF2D00;
                box-shadow: 0 0 18px rgba(255,45,0,0.15);
                color: #F5F5F5;
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5;
                border: 2px dashed #C4A484;
                box-shadow: 0 8px 20px rgba(74,44,26,0.10);
                color: #3D2416;
            <?php else: ?>
                background: white;
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            <?php endif; ?>
            border-radius: 18px;
            padding: 16px 18px;
            margin: 0 auto 20px;
            max-width: 640px;
            text-align: center;
            line-height: 1.45;
        }
        .welcome-banner .code {
            display: inline-block;
            margin-top: 8px;
            font-size: 1.35rem;
            letter-spacing: 0.1em;
            padding: 8px 16px;
            border-radius: 12px;
            <?php if ($is_classic_sweet): ?>
                background: #FFF5F6; color: #E55163; border: 2px dashed #F3C5CC;
            <?php elseif ($is_neon): ?>
                background: #1A1A1A; color: #FF2ECB; border: 2px dashed #FF2ECB;
                box-shadow: 0 0 12px rgba(255, 46, 203, 0.25);
            <?php elseif ($is_farm): ?>
                background: #EFE2CF; color: #C43B2C; border: 2px dashed #E07A2F;
            <?php elseif ($is_urban): ?>
                background: #1A1A1A; color: #FF2D00; border: 2px dashed #FF2D00;
            <?php elseif ($is_coffee): ?>
                background: #EFE4D8; color: #8B5A2B; border: 2px dashed #C4A484;
            <?php else: ?>
                background: #EEF2F8; color: #1A2A44; border: 2px dashed #C5D0DE;
            <?php endif; ?>
        }
        .welcome-banner a {
            <?php if ($is_classic_sweet): ?>
                color: #E55163;
            <?php elseif ($is_neon): ?>
                color: #00F0FF;
            <?php elseif ($is_farm): ?>
                color: #2F6B3A;
            <?php elseif ($is_urban): ?>
                color: #FF2D00;
            <?php elseif ($is_coffee): ?>
                color: #8B5A2B;
            <?php else: ?>
                color: #1A2A44;
            <?php endif; ?>
            font-weight: 600;
        }

        <?php if ($is_neon): ?>
        .neon-badge {
            display: inline-block;
            margin-bottom: 14px;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #FF2ECB;
            color: #00F0FF;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-family: 'Warnes', system-ui, sans-serif;
            box-shadow: 0 0 12px rgba(255, 46, 203, 0.3);
            background: #101010;
        }
        <?php elseif ($is_farm): ?>
        .farm-badge {
            display: inline-block;
            margin-bottom: 14px;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #E07A2F;
            color: #2F6B3A;
            font-size: 0.9rem;
            letter-spacing: 0.04em;
            font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive;
            background: #FFF8EE;
            box-shadow: 0 4px 12px rgba(47,107,58,0.12);
        }
        <?php elseif ($is_urban): ?>
        .urban-badge {
            display: inline-block;
            margin-bottom: 14px;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #FF2D00;
            color: #FF2D00;
            font-size: 0.95rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif;
            background: #0A0A0A;
            box-shadow: 0 0 14px rgba(255,45,0,0.3);
        }
        <?php elseif ($is_coffee): ?>
        .coffee-badge {
            display: inline-block;
            margin-bottom: 14px;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #C4A484;
            color: #8B5A2B;
            font-size: 0.95rem;
            letter-spacing: 0.03em;
            font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif;
            background: #FFFAF5;
            box-shadow: 0 4px 12px rgba(74,44,26,0.12);
        }
        <?php endif; ?>

        /* —— Movable home tiles (content-sized, not equal grid) —— */
        .dash-tiles-bar {
            max-width: 720px;
            margin: 0 auto 14px;
            text-align: center;
        }
        .dash-tiles-edit-btn {
            border: none;
            border-radius: 999px;
            padding: 8px 16px;
            font-size: 0.92rem;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            <?php if ($is_neon): ?>
                font-family: 'Mouse Memoirs', Georgia, sans-serif;
                background: #141414; color: #00F0FF; border: 1px solid #00F0FF;
            <?php elseif ($is_farm): ?>
                font-family: 'Notepen', Georgia, serif;
                background: #FFF8EE; color: #2F6B3A; border: 1px solid #E07A2F;
            <?php elseif ($is_urban): ?>
                font-family: 'Kelly Slab', Georgia, serif;
                background: #121212; color: #FF2D00; border: 1px solid #FF2D00;
            <?php elseif ($is_coffee): ?>
                font-family: 'Ambery Garden', Georgia, serif;
                background: #FFFAF5; color: #4A2C1A; border: 1px solid #C4A484;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                font-family: 'DreamingOutLoudPro', Georgia, serif;
                background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;
            <?php else: ?>
                font-family: 'Lora', Georgia, serif;
                background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;
            <?php endif; ?>
        }
        .dash-tiles-edit-btn[aria-pressed="true"] {
            <?php if ($is_neon): ?>background: #00F0FF; color: #0A0A0A;
            <?php elseif ($is_farm): ?>background: #2F6B3A; color: #FFF8EE;
            <?php elseif ($is_urban): ?>background: #FF2D00; color: #fff;
            <?php elseif ($is_coffee): ?>background: #4A2C1A; color: #F5EDE3;
            <?php elseif ($is_classic_sweet || $fun_names): ?>background: #E55163; color: #fff;
            <?php else: ?>background: #1A2A44; color: #fff;<?php endif; ?>
        }
        .dash-tiles-hint {
            margin: 8px 0 0;
            font-size: 0.85rem;
            opacity: 0.72;
            line-height: 1.35;
        }
        .dash-tiles-hint[hidden] { display: none !important; }
        .dash-tiles-hidden-panel {
            margin: 10px auto 0;
            max-width: 520px;
            text-align: left;
            padding: 10px 12px;
            border-radius: 14px;
            <?php if ($is_neon): ?>
                background: #141414; border: 1px dashed rgba(0,240,255,0.4);
            <?php elseif ($is_farm): ?>
                background: #FFF8EE; border: 1px dashed #E07A2F;
            <?php elseif ($is_urban): ?>
                background: #121212; border: 1px dashed rgba(255,45,0,0.45);
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5; border: 1px dashed #C4A484;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                background: #FFFBF8; border: 1px dashed #F3C5CC;
            <?php else: ?>
                background: #FAF8F5; border: 1px dashed #C5D0DE;
            <?php endif; ?>
        }
        .dash-tiles-hidden-panel[hidden] { display: none !important; }
        .dash-tiles-hidden-title {
            margin: 0 0 8px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            opacity: 0.7;
        }
        .dash-tiles-hidden-empty {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.7;
            line-height: 1.35;
        }
        .dash-tiles-hidden-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .dash-tiles-restore {
            border: none;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.82rem;
            cursor: pointer;
            font: inherit;
            color: inherit;
            <?php if ($is_neon): ?>
                background: rgba(0,240,255,0.12); border: 1px solid rgba(0,240,255,0.4);
            <?php elseif ($is_farm): ?>
                background: #EFE2CF; border: 1px solid #D9C7A8;
            <?php elseif ($is_urban): ?>
                background: #1A0A08; border: 1px solid rgba(255,45,0,0.4);
            <?php elseif ($is_coffee): ?>
                background: #F3E6D4; border: 1px solid #C4A484;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                background: #FFF0F2; border: 1px solid #F3C5CC;
            <?php else: ?>
                background: #EEF2F8; border: 1px solid #C5D0DE;
            <?php endif; ?>
        }
        .dash-tiles-restore:hover { opacity: 0.92; transform: translateY(-1px); }

        .dash-tiles {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-start;
            justify-content: flex-start;
            max-width: 720px;
            margin: 0 auto 20px;
            width: 100%;
            box-sizing: border-box;
        }
        .dash-tile {
            position: relative;
            box-sizing: border-box;
            margin: 0 !important;
            max-width: 100%;
        }
        .dash-tile[data-tile-size="full"] {
            flex: 1 1 100%;
            width: 100%;
        }
        .dash-tile[data-tile-size="half"] {
            flex: 1 1 calc(50% - 6px);
            min-width: min(220px, 100%);
        }
        .dash-tile[data-tile-size="third"] {
            flex: 1 1 calc(33.333% - 8px);
            min-width: min(140px, 100%);
        }
        .dash-tile[data-tile-size="auto"] {
            flex: 0 1 auto;
            width: max-content;
            max-width: 100%;
        }
        @media (max-width: 560px) {
            .dash-tile[data-tile-size="third"],
            .dash-tile[data-tile-size="half"] {
                flex: 1 1 100%;
                min-width: 0;
                width: 100%;
            }
        }
        .dash-tile.is-user-hidden { display: none !important; }
        .dash-tile .dash-tile-chrome {
            display: none;
            position: absolute;
            top: 6px;
            right: 6px;
            z-index: 6;
            gap: 4px;
            align-items: center;
        }
        .dash-tiles.is-editing .dash-tile:not(.is-user-hidden) .dash-tile-chrome {
            display: flex;
        }
        .dash-tiles.is-editing .dash-tile:not(.is-user-hidden) {
            cursor: grab;
            outline: 1px dashed rgba(0,0,0,0.18);
            outline-offset: 2px;
            border-radius: 14px;
            <?php if ($is_neon || $is_urban): ?>
                outline-color: rgba(255,255,255,0.28);
            <?php endif; ?>
        }
        .dash-tiles.is-editing .dash-tile.is-dragging {
            opacity: 0.55;
            transform: scale(0.98);
            z-index: 8;
            cursor: grabbing;
        }
        .dash-tile-grip {
            font-size: 0.75rem;
            opacity: 0.45;
            letter-spacing: -1px;
            padding: 2px 4px;
            pointer-events: none;
            user-select: none;
        }
        .dash-tile-hide {
            border: none;
            width: 26px;
            height: 26px;
            border-radius: 999px;
            cursor: pointer;
            font-size: 1.05rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            color: inherit;
            <?php if ($is_neon): ?>
                background: #1A1A1A; border: 1px solid #FF2ECB; color: #FF2ECB;
            <?php elseif ($is_farm): ?>
                background: #FFF8EE; border: 1px solid #E07A2F; color: #C43B2C;
            <?php elseif ($is_urban): ?>
                background: #1A1A1A; border: 1px solid #FF2D00; color: #FF2D00;
            <?php elseif ($is_coffee): ?>
                background: #FFFAF5; border: 1px solid #C4A484; color: #8B5A2B;
            <?php elseif ($is_classic_sweet || $fun_names): ?>
                background: #FFF5F6; border: 1px solid #F3C5CC; color: #E55163;
            <?php else: ?>
                background: #EEF2F8; border: 1px solid #C5D0DE; color: #1A2A44;
            <?php endif; ?>
        }
        .dash-tile-hide:hover { opacity: 0.9; transform: scale(1.05); }

        /* Flatten stacked margins inside the tile flex layout */
        .dash-tiles .home-shortcuts,
        .dash-tiles .list-complete-card,
        .dash-tiles .work-music,
        .dash-tiles .creator-news,
        .dash-tiles .theme-quick,
        .dash-tiles .star-banner,
        .dash-tiles .a2hs-wrap,
        .dash-tiles .platform-desk,
        .dash-tiles .daily-pulse,
        .dash-tiles .jelly-stat {
            margin-left: 0;
            margin-right: 0;
            max-width: none;
        }
        .dash-tiles .daily-pulse {
            width: 100%;
        }
        /* Daily Pulse */
        .daily-pulse {
            background: white;
            border-radius: 18px;
            padding: 16px 18px;
            margin: 0 0 14px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-decoration: none;
            color: inherit;
            display: block;
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
            box-sizing: border-box;
        }
        .daily-pulse-head {
            display: flex; justify-content: space-between; align-items: baseline; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;
        }
        .daily-pulse-title {
            margin: 0; font-size: 1.15rem;
            <?php if ($is_classic_sweet || $fun_names): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;
            <?php elseif ($is_neon): ?>font-family: 'Mouse Memoirs', Georgia, sans-serif; color: #00F0FF;
            <?php else: ?>font-family: inherit;<?php endif; ?>
        }
        .daily-pulse-sub { margin: 0; font-size: 0.82rem; opacity: 0.7; }
        .pulse-grid {
            display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px;
        }
        @media (max-width: 640px) {
            .pulse-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .pulse-cell {
            border-radius: 14px; padding: 10px 8px; text-align: center;
            <?php if ($is_classic_sweet || $fun_names): ?>background: #FFF5F6; border: 1px solid #F3C5CC;
            <?php elseif ($is_neon): ?>background: #141414; border: 1px solid #333;
            <?php else: ?>background: #F7F5F2; border: 1px solid #E6DFD7;<?php endif; ?>
        }
        .pulse-num { font-size: 1.15rem; line-height: 1.2; font-variant-numeric: tabular-nums; }
        .pulse-num.ok { color: #1F6B4A; }
        .pulse-num.warn { color: #C62828; }
        .pulse-lbl { font-size: 0.72rem; opacity: 0.7; margin-top: 4px; line-height: 1.25; }
        .pulse-pos-bar {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px;
            margin-top: 12px; padding: 10px 12px; border-radius: 14px;
            background: <?php echo $fun_names ? '#FFF5F6' : '#EEF2F8'; ?>;
            border: 1px solid <?php echo $fun_names ? '#F3C5CC' : '#C5D0DE'; ?>;
            font-size: 0.82rem; line-height: 1.35;
        }
        .pulse-pos-bar[hidden] { display: none !important; }
        .pulse-pos-status { flex: 1; min-width: 140px; opacity: 0.9; }
        .pulse-pos-status a { font-weight: 700; <?php if ($fun_names): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .pulse-pos-sync {
            border: none; border-radius: 999px; padding: 8px 14px; cursor: pointer; font-size: 0.82rem;
            <?php if ($fun_names): ?>background: #E55163; color: #fff; font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>background: #1A2A44; color: #fff; font-family: 'Lora', serif;<?php endif; ?>
        }
        .pulse-pos-sync:disabled { opacity: 0.45; cursor: default; }
        .daily-pulse-links { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .daily-pulse-links a {
            font-size: 0.85rem; text-decoration: none; border-radius: 999px; padding: 6px 12px;
            <?php if ($is_classic_sweet || $fun_names): ?>background: #E55163; color: white;
            <?php elseif ($is_neon): ?>background: #00F0FF; color: #0A0A0A;
            <?php else: ?>background: #1A2A44; color: white;<?php endif; ?>
        }
            margin-bottom: 0;
            max-width: 100%;
        }
        .dash-tiles .list-complete-card {
            width: 100%;
            box-sizing: border-box;
        }
        .dash-tiles .list-complete-card.show:not(.is-user-hidden) {
            display: block;
        }
        .dash-tiles .jelly-stat {
            text-align: center;
            height: 100%;
        }
        .dash-tiles .home-extra {
            display: contents;
        }
        .dash-tiles .theme-quick {
            max-width: 100%;
            margin-top: 0;
        }
        .dash-tiles .star-banner {
            max-width: 100%;
        }
        body.dash-tiles-editing {
            user-select: none;
        }
    </style>

    <?php
    // Paint last so global neon overrides always win on shared components
    if (function_exists('pbj_render_theme_paint')) {
        pbj_render_theme_paint();
    }
    if (function_exists('pbj_render_icon_css')) {
        pbj_render_icon_css();
    }
    ?>
</head>
<body class="<?php echo htmlspecialchars($body_class); ?>" data-theme="<?php echo htmlspecialchars($theme_id); ?>">
    <nav>
        <div class="nav-menu">
            <?php
            $navSlots = [
                ['href' => '/home', 'slot' => 'nav/home', 'key' => 'home'],
                ['href' => '/FOH', 'slot' => 'nav/foh', 'key' => 'foh'],
                ['href' => '/BOH', 'slot' => 'nav/boh', 'key' => 'boh'],
                ['href' => '/admin', 'slot' => 'nav/admin', 'key' => 'admin'],
                ['href' => '/messages', 'slot' => 'nav/messages', 'key' => 'messages'],
                ['href' => '/settings', 'slot' => 'nav/settings', 'key' => 'settings'],
            ];
            foreach ($navSlots as $item):
                $label = $hubs[$item['key']];
                $src = pbj_icon($item['slot']);
                $zoomIco = in_array($item['key'], ['boh', 'settings'], true);
            ?>
                <a href="<?php echo htmlspecialchars($item['href']); ?>" class="nav-item">
                    <?php if ($zoomIco): ?><span class="nav-ico-zoom"><?php endif; ?>
                    <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($label); ?>">
                    <?php if ($zoomIco): ?></span><?php endif; ?>
                    <span><?php echo htmlspecialchars($label); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <div class="hub-main">
        <?php
        $displayName = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'friend';
        $welcomeCode = trim((string)($_GET['code'] ?? ''));
        $isWelcome = !empty($_GET['welcome']);
        $isJoined = !empty($_GET['joined']);
        $isIndividual = !empty($_GET['individual']);
        $isTrialWelcome = !empty($_GET['trial']);
        $dashTrial = null;
        if (function_exists('pbj_trial_info') && !empty($_SESSION['user_id'])) {
            $dashTrial = pbj_trial_info($pdo, (int) $_SESSION['user_id']);
        }
        $showApproveLink = function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($_SESSION['email'] ?? '');
        if ($showApproveLink && empty($_SESSION['email'])) {
            $showApproveLink = false;
            try {
                $st = $pdo->prepare('SELECT email FROM users WHERE id = ?');
                $st->execute([(int)($_SESSION['user_id'] ?? 0)]);
                $em = $st->fetchColumn();
                if ($em) {
                    $_SESSION['email'] = $em;
                    $showApproveLink = pbj_is_platform_admin($em);
                }
            } catch (Exception $e) {}
        }

        // Creator's news bootstrap (platform-wide; hide is per-user client-side)
        $creatorNewsText = '';
        $creatorNewsCanEdit = false;
        $creatorNewsUpdatedAt = 0;
        try {
            if (!function_exists('creator_news_is_editor')) {
                // Inline helpers if API file not loaded
                $pdo->exec("CREATE TABLE IF NOT EXISTS platform_kv (
                    k VARCHAR(64) NOT NULL PRIMARY KEY,
                    v LONGTEXT NOT NULL,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_by INT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            }
            $cnStmt = $pdo->prepare('SELECT v, updated_at FROM platform_kv WHERE k = ? LIMIT 1');
            $cnStmt->execute(['creator_news']);
            $cnRow = $cnStmt->fetch(PDO::FETCH_ASSOC);
            if ($cnRow) {
                $cnData = json_decode((string) $cnRow['v'], true);
                if (is_array($cnData)) {
                    $creatorNewsText = trim((string) ($cnData['text'] ?? ''));
                    $creatorNewsUpdatedAt = (int) ($cnData['updatedAt'] ?? 0);
                } else {
                    $creatorNewsText = trim((string) $cnRow['v']);
                }
            }
            $cnUid = (int) ($_SESSION['user_id'] ?? 0);
            $cnEmail = strtolower(trim((string) ($_SESSION['email'] ?? '')));
            if (function_exists('pbj_permissions_is_omnipotent') && pbj_permissions_is_omnipotent($pdo, $cnUid)) {
                $creatorNewsCanEdit = true;
            } elseif ($cnEmail !== '' && function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($cnEmail)) {
                $creatorNewsCanEdit = true;
            } elseif ($showApproveLink) {
                $creatorNewsCanEdit = true;
            }
        } catch (Throwable $e) {
            // leave empty
        }
        ?>
        <?php if ($is_neon): ?>
        <div class="neon-badge">Neon 50s Diner</div>
        <?php elseif ($is_farm): ?>
        <div class="farm-badge">🌿 Farm-to-Table</div>
        <?php elseif ($is_urban): ?>
        <div class="urban-badge">🏙️ Modern Urban Edge</div>
        <?php elseif ($is_coffee): ?>
        <div class="coffee-badge">☕ Coffee Shop Cozy</div>
        <?php endif; ?>
        <?php if ($isWelcome || $isJoined || $welcomeCode !== ''): ?>
        <div class="welcome-banner">
            <?php if ($isWelcome && $isIndividual): ?>
                <strong><?php echo $fun_names ? 'Welcome, solo spoon 🥄' : 'Welcome — Individual plan.'; ?></strong><br>
                <?php if ($isTrialWelcome || !empty($dashTrial['active'])): ?>
                    <?php echo $fun_names
                        ? 'Your free trial is on — explore everything for ' . (int) ($dashTrial['days_left'] ?? pbj_trial_days()) . ' days, no card needed. Subscribe anytime under Account → Plans.'
                        : 'Free trial active (' . (int) ($dashTrial['days_left'] ?? pbj_trial_days()) . ' days). No card required yet.'; ?>
                <?php else: ?>
                <?php echo $fun_names
                    ? 'You\'re on the solo Individual plan — no house invite codes. Explore the tools and upgrade to a house plan when you\'re ready for a full team hub.'
                    : 'Solo Individual plan — no house invite codes. Upgrade to a house plan for full team features.'; ?>
                <?php endif; ?>
                <div style="margin-top:10px;font-size:0.95rem;opacity:0.8;">
                    <a href="/billing/plans"><?php echo $fun_names ? 'Plans & subscribe' : 'Plans & subscribe'; ?></a>
                    ·
                    <a href="/join"><?php echo $fun_names ? 'Join a house with a code' : 'Join with a code'; ?></a>
                </div>
            <?php elseif ($isWelcome): ?>
                <strong><?php echo $fun_names ? 'Your house is open 💕' : 'Your restaurant is ready.'; ?></strong><br>
                <?php if ($isTrialWelcome || !empty($dashTrial['active'])): ?>
                    <?php echo $fun_names
                        ? 'Free trial for ' . (int) ($dashTrial['days_left'] ?? pbj_trial_days()) . ' days — no card. Share this invite code so teammates can join:'
                        : 'Free trial active. Share this invite code with your team:'; ?>
                <?php else: ?>
                <?php echo $fun_names
                    ? 'Share this invite code so teammates can join:'
                    : 'Share this invite code with your team:'; ?>
                <?php endif; ?>
                <?php if ($welcomeCode !== ''): ?>
                    <div class="code"><?php echo htmlspecialchars(strtoupper($welcomeCode)); ?></div>
                <?php endif; ?>
                <div style="margin-top:10px;font-size:0.95rem;opacity:0.8;">
                    <?php echo $fun_names
                        ? 'Also under ' . htmlspecialchars($hubs['admin']) . ' → Invite & Onboarding, and ' . htmlspecialchars($hubs['settings']) . ' → Account.'
                        : 'Also in Admin → Invite & Onboarding, and Account settings.'; ?>
                    <?php if ($isTrialWelcome || !empty($dashTrial['active'])): ?>
                        · <a href="/billing/plans">Trial &amp; subscribe</a>
                    <?php endif; ?>
                </div>
            <?php elseif ($isJoined): ?>
                <strong><?php echo $fun_names ? 'You’re in the jar 🫙' : 'You’re on the team.'; ?></strong>
                <?php echo $fun_names ? ' Welcome — pick a hub below and let’s go.' : ' Welcome aboard.'; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (!$isWelcome && !empty($dashTrial['active']) && empty($dashTrial['paid'])): ?>
        <div class="welcome-banner" style="margin-top:10px;">
            <strong><?php echo $fun_names ? 'Free trial ⏳' : 'Free trial'; ?></strong>
            <?php echo (int) $dashTrial['days_left']; ?> day<?php echo (int) $dashTrial['days_left'] === 1 ? '' : 's'; ?> left
            <?php if ($dashTrial['ends_label'] !== ''): ?>
                · ends <?php echo htmlspecialchars($dashTrial['ends_label']); ?>
            <?php endif; ?>
            <div style="margin-top:8px;font-size:0.95rem;opacity:0.85;">
                <a href="/billing/plans"><?php echo $fun_names ? 'Subscribe when you’re ready' : 'Subscribe'; ?></a>
                — no card until you choose a plan at checkout.
            </div>
        </div>
        <?php endif; ?>

        <h1>Welcome back, <?= htmlspecialchars($displayName) ?>!</h1>
        <p class="tagline"><?php
            if ($is_neon) {
                echo 'Pull up a stool — what are we ringing in tonight?';
            } elseif ($is_farm) {
                echo 'Fresh from the field — what are we plating today?';
            } elseif ($is_urban) {
                echo 'City never sleeps — what are we running tonight?';
            } elseif ($is_coffee) {
                echo 'Pour a cup — what are we brewing today?';
            } elseif ($fun_names) {
                echo 'What would you like to do today?';
            } else {
                echo 'What would you like to do today?';
            }
        ?></p>

        <div class="dash-tiles-bar" id="dash-tiles-bar">
            <button type="button" class="dash-tiles-edit-btn" id="dash-tiles-edit-btn" aria-pressed="false">
                <?php echo $fun_names ? 'Arrange home' : 'Arrange home'; ?>
            </button>
            <p class="dash-tiles-hint" id="dash-tiles-hint" hidden>
                <?php echo $fun_names
                    ? 'Drag tiles to rearrange · tap × to hide anything (music, stats, themes…) · restore below 💕'
                    : 'Drag tiles to rearrange. Tap × to hide widgets you don’t want. Restore hidden tiles below.'; ?>
            </p>
            <div class="dash-tiles-hidden-panel" id="dash-tiles-hidden-panel" hidden></div>
        </div>

        <div class="dash-tiles" id="dash-tiles" data-fun="<?php echo $fun_names ? '1' : '0'; ?>">

        <div class="dash-tile" data-tile-id="house-setup" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'House setup' : 'House setup'; ?>" id="house-setup-banner" hidden style="margin-bottom:12px;">
            <a href="/admin/setup" style="display:block;text-decoration:none;color:inherit;background:white;border-radius:18px;padding:14px 16px;box-shadow:0 5px 15px rgba(0,0,0,0.08);">
                <strong id="house-setup-title"><?php echo $fun_names ? '🏠 Finish house setup' : 'Finish house setup'; ?></strong>
                <span id="house-setup-meta" style="display:block;font-size:0.85rem;opacity:0.75;margin-top:4px;"></span>
            </a>
        </div>

        <div class="daily-pulse dash-tile" data-tile-id="daily-pulse" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Daily Pulse' : 'Daily Pulse'; ?>" id="daily-pulse" data-perm-any="admin.reports.view,admin.schedules.view_wages" hidden>
            <div class="daily-pulse-head">
                <p class="daily-pulse-title"><?php echo $fun_names ? '💓 Daily Pulse' : 'Daily Pulse'; ?></p>
                <p class="daily-pulse-sub" id="pulse-sub"><?php echo $fun_names ? 'Owner view — today at a glance' : 'Today at a glance'; ?></p>
            </div>
            <div class="pulse-grid">
                <a class="pulse-cell" href="/admin/sales" style="text-decoration:none;color:inherit;">
                    <div class="pulse-num" id="pulse-sales">—</div>
                    <div class="pulse-lbl"><?php echo $fun_names ? 'Net sales' : 'Net sales'; ?></div>
                </a>
                <a class="pulse-cell" href="/admin/labor" style="text-decoration:none;color:inherit;">
                    <div class="pulse-num" id="pulse-labor">—</div>
                    <div class="pulse-lbl"><?php echo $fun_names ? 'Labor %' : 'Labor %'; ?></div>
                </a>
                <a class="pulse-cell" href="/admin/cash" style="text-decoration:none;color:inherit;">
                    <div class="pulse-num" id="pulse-cash">—</div>
                    <div class="pulse-lbl"><?php echo $fun_names ? 'Cash O/S' : 'Cash O/S'; ?></div>
                </a>
                <a class="pulse-cell" href="/admin/count" style="text-decoration:none;color:inherit;">
                    <div class="pulse-num" id="pulse-par">—</div>
                    <div class="pulse-lbl"><?php echo $fun_names ? 'Below par' : 'Below par'; ?></div>
                </a>
                <a class="pulse-cell" href="/admin/checklist-overview" style="text-decoration:none;color:inherit;">
                    <div class="pulse-num" id="pulse-lists">—</div>
                    <div class="pulse-lbl"><?php echo $fun_names ? 'Lists done' : 'Lists done'; ?></div>
                </a>
            </div>
            <div class="pulse-pos-bar" id="pulse-pos-bar" hidden>
                <span class="pulse-pos-status" id="pulse-pos-status"><?php echo $fun_names ? 'Checking POS…' : 'Checking POS…'; ?></span>
                <button type="button" class="pulse-pos-sync" id="pulse-pos-sync" hidden><?php echo $fun_names ? 'Sync all' : 'Sync all'; ?></button>
            </div>
            <div class="pulse-pos-bar" id="pulse-ops-nudge" hidden>
                <span class="pulse-pos-status" id="pulse-ops-status"></span>
            </div>
            <div class="daily-pulse-links">
                <a href="/admin/pnl"><?php echo $fun_names ? 'P&amp;L' : 'P&amp;L'; ?></a>
                <a href="/admin/auto-order"><?php echo $fun_names ? 'Auto-Order' : 'Auto-Order'; ?></a>
                <a href="/admin/pos-connect"><?php echo $fun_names ? 'Connect POS' : 'Connect POS'; ?></a>
                <a href="/admin/labor"><?php echo $fun_names ? 'Labor' : 'Labor'; ?></a>
                <a href="/admin/cash"><?php echo $fun_names ? 'Cash close' : 'Cash close'; ?></a>
                <a href="/admin/invoices"><?php echo $fun_names ? 'Invoices' : 'Invoices'; ?></a>
                <a href="/admin/pos-import"><?php echo $fun_names ? 'POS import' : 'POS import'; ?></a>
                <a href="/admin/checklist-overview"><?php echo $fun_names ? 'Checklists' : 'Checklists'; ?></a>
            </div>
        </div>

        <a class="star-banner dash-tile" data-tile-id="star" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Star of the house' : 'Star of the house'; ?>" id="star-banner" href="/admin/spotlight" hidden aria-label="<?php echo $fun_names ? 'Star of the house' : 'Star of the house'; ?>">
            <span class="sb-emoji" aria-hidden="true">⭐</span>
            <span class="sb-body">
                <p class="sb-kicker" id="star-banner-kicker"><?php echo $fun_names ? 'Star of the house' : 'Star of the house'; ?></p>
                <p class="sb-name" id="star-banner-name"></p>
                <p class="sb-meta" id="star-banner-meta"></p>
            </span>
            <span class="sb-chevron" aria-hidden="true">›</span>
        </a>

        <div id="a2hs-wrap" class="a2hs-wrap dash-tile" data-tile-id="a2hs" data-tile-size="auto" data-tile-label="<?php echo $fun_names ? 'Add to homepage' : 'Add to homepage'; ?>" hidden>
            <button type="button" id="a2hs-btn" class="a2hs-btn">
                <?php echo $fun_names ? '📱 Add to homepage' : 'Add to homepage'; ?>
            </button>
            <p class="a2hs-hint" id="a2hs-hint" hidden></p>
        </div>

        <?php
        // Optional dashboard shortcuts — up to 4 pins, per-device localStorage
        $scGroups = [
            'boh' => $hubs['boh'] ?? 'BOH',
            'foh' => $hubs['foh'] ?? 'FOH',
            'admin' => $hubs['admin'] ?? 'Admin',
            'messages' => $hubs['messages'] ?? 'Messages',
        ];
        $scCatalogRaw = [
            ['id' => 'prep', 'group' => 'boh', 'href' => '/BOH/prep', 'label' => 'Prep Lists', 'slot' => 'the-heat/prep', 'emoji' => '🔪'],
            ['id' => 'boh-open', 'group' => 'boh', 'href' => '/BOH/opening-closing', 'label' => 'BOH Open / Close', 'slot' => 'the-heat/opening-closing', 'emoji' => '🌅'],
            ['id' => 'cleaning', 'group' => 'boh', 'href' => '/BOH/cleaning', 'label' => 'Cleaning Schedule', 'slot' => 'the-heat/cleaning', 'emoji' => '✨'],
            ['id' => 'temps', 'group' => 'boh', 'href' => '/BOH/temps', 'label' => 'Temps & Safety', 'slot' => 'the-heat/temps', 'emoji' => '🌡️'],
            ['id' => 'recipes', 'group' => 'boh', 'href' => '/BOH/recipes', 'label' => 'Menu & Recipes', 'slot' => 'the-heat/menu-recipes', 'emoji' => '📖'],
            ['id' => 'tools', 'group' => 'boh', 'href' => '/BOH/tools', 'label' => 'Quick Tools', 'slot' => 'the-heat/quick-tools', 'emoji' => '🛠️'],
            ['id' => '86-board', 'group' => 'boh', 'href' => '/BOH/86', 'label' => '86 Board', 'slot' => 'recipes-hub/86-board', 'emoji' => '🚫'],
            ['id' => '86-display', 'group' => 'boh', 'href' => '/BOH/86/display', 'label' => '86 Kitchen Display', 'slot' => 'recipes-hub/86-display', 'emoji' => '📺'],
            ['id' => 'allergens', 'group' => 'boh', 'href' => '/BOH/allergens', 'label' => 'Allergen Menu', 'slot' => 'recipes-hub/allergen-menu', 'emoji' => '⚠️'],
            ['id' => 'foh-open', 'group' => 'foh', 'href' => '/FOH/opening-closing', 'label' => 'FOH Open / Close', 'slot' => 'showtime/opening-closing', 'emoji' => '🔑'],
            ['id' => 'sidework', 'group' => 'foh', 'href' => '/FOH/sidework', 'label' => 'Server Sidework', 'slot' => 'showtime/server-sidework', 'emoji' => '🧹'],
            ['id' => 'floor', 'group' => 'foh', 'href' => '/FOH/floor-plan', 'label' => 'Floor Plan', 'slot' => 'showtime/floor-plan', 'emoji' => '🪑'],
            ['id' => 'reservations', 'group' => 'foh', 'href' => '/FOH/reservations', 'label' => 'Res / Wait / To-Gos', 'slot' => 'showtime/reservations', 'emoji' => '📅'],
            ['id' => 'pos', 'group' => 'foh', 'href' => '/FOH/pos', 'label' => 'POS Reference', 'slot' => 'showtime/pos', 'emoji' => '🖥️'],
            ['id' => 'bar', 'group' => 'foh', 'href' => '/FOH/bar', 'label' => 'Bar Ops', 'slot' => 'showtime/bar-guest-service', 'emoji' => '🍸'],
            ['id' => 'schedules', 'group' => 'admin', 'href' => '/admin/schedules', 'label' => 'Schedules', 'slot' => 'sandwich-hq/schedules', 'emoji' => '🗓️'],
            ['id' => 'team', 'group' => 'admin', 'href' => '/admin/team', 'label' => 'Team & Roles', 'slot' => 'sandwich-hq/team', 'emoji' => '👥'],
            ['id' => 'inventory', 'group' => 'admin', 'href' => '/admin/inventory', 'label' => 'Inventory', 'slot' => 'sandwich-hq/inventory', 'emoji' => '📦'],
            ['id' => 'reports', 'group' => 'admin', 'href' => '/admin/reports', 'label' => 'Reports', 'slot' => 'sandwich-hq/reports', 'emoji' => '📊'],
            ['id' => 'compliance', 'group' => 'admin', 'href' => '/admin/compliance', 'label' => 'Compliance', 'slot' => 'sandwich-hq/compliance', 'emoji' => '✅'],
            ['id' => 'ops', 'group' => 'admin', 'href' => '/admin/ops', 'label' => 'Restaurant Ops', 'slot' => 'sandwich-hq/operations', 'emoji' => '🏢'],
            ['id' => 'announcements', 'group' => 'messages', 'href' => '/messages/announcements', 'label' => 'Announcements', 'slot' => 'jelly/announcements', 'emoji' => '📢'],
            ['id' => 'shift-notes', 'group' => 'messages', 'href' => '/messages/shift-notes', 'label' => 'Shift Notes', 'slot' => 'jelly/shift-notes', 'emoji' => '📝'],
            ['id' => 'dms', 'group' => 'messages', 'href' => '/messages/dms', 'label' => 'Direct Messages', 'slot' => 'jelly/dms', 'emoji' => '💬'],
            ['id' => 'broadcasts', 'group' => 'messages', 'href' => '/messages/broadcasts', 'label' => 'Broadcasts', 'slot' => 'jelly/broadcasts', 'emoji' => '📣'],
        ];
        $scCatalog = [];
        foreach ($scCatalogRaw as $item) {
            $icon = function_exists('pbj_icon') ? pbj_icon($item['slot']) : '';
            $scCatalog[] = [
                'id' => $item['id'],
                'group' => $item['group'],
                'href' => $item['href'],
                'label' => $item['label'],
                'emoji' => $item['emoji'],
                'icon' => $icon,
            ];
        }
        ?>
        <section class="home-shortcuts dash-tile" data-tile-id="shortcuts" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Quick pins' : 'Shortcuts'; ?>" id="home-shortcuts" aria-label="<?php echo $fun_names ? 'Your quick pins' : 'Dashboard shortcuts'; ?>">
            <div class="home-shortcuts-head">
                <p class="home-shortcuts-title"><?php echo $fun_names ? '⭐ Quick pins' : 'Shortcuts'; ?></p>
                <button type="button" class="home-shortcuts-edit" id="home-shortcuts-edit">
                    <?php echo $fun_names ? 'Customize' : 'Customize'; ?>
                </button>
            </div>
            <p class="home-shortcuts-empty" id="home-shortcuts-empty">
                <?php echo $fun_names
                    ? 'Optional — pin up to 4 favorites (prep lists, schedules, temps…). Pins sync across your devices 💕'
                    : 'Optional — pin up to 4 tools you use most. Your pins sync across devices on this account.'; ?>
            </p>
            <p class="home-shortcuts-sync" id="home-shortcuts-sync" hidden></p>
            <div class="home-shortcuts-grid" id="home-shortcuts-grid"></div>
            <p class="home-shortcuts-reorder-hint" id="home-shortcuts-reorder-hint" hidden>
                <?php echo $fun_names ? 'Drag pins to reorder · order syncs with your account' : 'Drag to reorder · order syncs with your account'; ?>
            </p>
        </section>

        <div class="list-complete-card dash-tile" data-tile-id="list-complete" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Lists finished today' : 'Checklists completed'; ?>" id="list-complete-card" data-perm="ops.receive_list_completion" hidden>
            <h2><?php echo $fun_names ? 'Lists finished today 🎉' : 'Checklists completed today'; ?></h2>
            <div id="list-complete-body"></div>
        </div>

        <a href="/messages" class="jelly-stat dash-tile" data-tile-id="stat-posts" data-tile-size="third" data-tile-label="<?php echo $fun_names ? 'Open posts' : 'Open posts'; ?>">
            <div class="num" id="stat-posts">0</div>
            <div class="lbl"><?php echo $fun_names ? 'Open posts' : 'Open posts'; ?></div>
            <div class="sub"><?php echo $fun_names ? 'Announcements · FOH · BOH · broadcasts' : 'Announcements, FOH, BOH, broadcasts'; ?></div>
        </a>
        <a href="/messages/shift-notes" class="jelly-stat dash-tile" data-tile-id="stat-handoffs" data-tile-size="third" data-tile-label="<?php echo $fun_names ? 'Handoffs today' : 'Handoffs today'; ?>">
            <div class="num" id="stat-handoffs">0</div>
            <div class="lbl"><?php echo $fun_names ? 'Handoffs today' : 'Handoffs today'; ?></div>
            <div class="sub"><?php echo $fun_names ? 'Shift notes for this crew' : 'Shift notes for today'; ?></div>
        </a>
        <a href="/messages/dms" class="jelly-stat dash-tile" data-tile-id="stat-chats" data-tile-size="third" data-tile-label="<?php echo $fun_names ? 'DM threads' : 'DM threads'; ?>">
            <div class="num" id="stat-chats">0</div>
            <div class="lbl"><?php echo $fun_names ? 'DM threads' : 'DM threads'; ?></div>
            <div class="sub"><?php echo $fun_names ? 'One-on-one chats' : 'One-on-one chats'; ?></div>
        </a>

            <div class="creator-news dash-tile" data-tile-id="creator-news" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Creator’s news' : "Creator's news"; ?>" id="creator-news"
                 <?php
                 // Visible immediately for editors, or when there is news (JS may still hide for users who opted out)
                 $cnShowServer = $creatorNewsCanEdit || $creatorNewsText !== '';
                 echo $cnShowServer ? '' : ' hidden';
                 ?>
                 data-can-edit="<?php echo $creatorNewsCanEdit ? '1' : '0'; ?>"
                 data-updated-at="<?php echo (int) $creatorNewsUpdatedAt; ?>">
                <div class="creator-news-head">
                    <p class="creator-news-title"><?php echo $fun_names ? 'Creator’s news' : "Creator's news"; ?></p>
                    <div class="creator-news-actions">
                        <button type="button" class="primary" id="creator-news-edit-btn" <?php echo $creatorNewsCanEdit ? '' : 'hidden'; ?>><?php echo $fun_names ? 'Edit' : 'Edit'; ?></button>
                        <button type="button" class="primary" id="creator-news-save-btn" hidden><?php echo $fun_names ? 'Save for everyone' : 'Save for everyone'; ?></button>
                        <button type="button" class="ghost" id="creator-news-cancel-btn" hidden><?php echo $fun_names ? 'Cancel' : 'Cancel'; ?></button>
                        <button type="button" class="ghost" id="creator-news-hide-btn"><?php echo $fun_names ? 'Hide' : 'Hide'; ?></button>
                    </div>
                </div>
                <p class="creator-news-body" id="creator-news-body"
                   data-empty="<?php echo $creatorNewsCanEdit
                       ? ($fun_names ? 'Tap Edit to post what’s new for every dashboard 💕' : 'Tap Edit to post an update for all users.')
                       : ($fun_names ? 'No updates yet — check back soon 💕' : 'No updates yet.'); ?>"><?php echo htmlspecialchars($creatorNewsText); ?></p>
                <textarea class="creator-news-edit" id="creator-news-edit" hidden
                    placeholder="<?php echo $fun_names ? 'What’s new, fixed, or upgraded… (everyone will see this on home)' : 'Features added, fixed, upgraded… shown on every home dashboard'; ?>"
                    maxlength="2000"><?php echo htmlspecialchars($creatorNewsText); ?></textarea>
                <p class="creator-news-hint" id="creator-news-hint" <?php echo $creatorNewsCanEdit ? '' : 'hidden'; ?>>
                    <?php echo $fun_names
                        ? 'Saved notes sync to all users’ home screens. They can hide it in Settings anytime.'
                        : 'Saves to every user’s home dashboard. Users can hide it under Settings.'; ?>
                </p>
                <p class="creator-news-status" id="creator-news-status" aria-live="polite"></p>
            </div>

            <div class="work-music dash-tile is-collapsed" data-tile-id="work-music" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Work music' : 'Work music'; ?>" id="work-music" aria-label="<?php echo $fun_names ? 'Work music' : 'Work music'; ?>">
                <button type="button" class="wm-head" id="wm-toggle" aria-expanded="false" aria-controls="wm-body">
                    <span class="wm-head-text">
                        <span class="wm-head-label"><?php echo $fun_names ? '🎵 Work music' : 'Work music'; ?></span>
                        <span class="wm-head-now" id="wm-head-now"></span>
                    </span>
                    <span class="wm-chevron" id="wm-chevron" aria-hidden="true">▼</span>
                </button>
                <div class="wm-body" id="wm-body" hidden>
                    <p class="wm-hint"><?php echo $fun_names
                        ? 'Your personal jam station — Spotify, YouTube, Apple Music, SoundCloud, or any link. Queue songs, then use Back / Play / Next. (Full control works best on YouTube; other services use their embedded player.)'
                        : 'Personal player: add Spotify, YouTube, Apple Music, SoundCloud, or custom links. Queue tracks and use Back / Play / Next. YouTube supports full play/pause here; other services use their embed controls.'; ?></p>

                    <div class="wm-services" id="wm-services">
                        <a class="wm-svc" href="https://open.spotify.com" target="_blank" rel="noopener noreferrer" data-svc="spotify">🟢 Spotify</a>
                        <a class="wm-svc" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" data-svc="youtube">▶️ YouTube</a>
                        <a class="wm-svc" href="https://music.apple.com" target="_blank" rel="noopener noreferrer" data-svc="apple">🍎 Apple Music</a>
                        <a class="wm-svc" href="https://soundcloud.com" target="_blank" rel="noopener noreferrer" data-svc="soundcloud">🟠 SoundCloud</a>
                        <button type="button" class="wm-svc" id="wm-svc-custom" data-svc="custom">🔗 Custom link</button>
                    </div>

                    <div class="wm-row">
                        <input type="url" id="wm-url" inputmode="url" placeholder="Paste Spotify / YouTube / Apple Music / SoundCloud / any URL…">
                        <input type="text" id="wm-title" placeholder="<?php echo $fun_names ? 'Optional nickname' : 'Optional title'; ?>" style="flex:0.7;min-width:100px;">
                        <button type="button" class="wm-btn" id="wm-add"><?php echo $fun_names ? 'Add to queue' : 'Add'; ?></button>
                    </div>

                    <div class="wm-now" id="wm-now" hidden>
                        <div class="wm-now-ico" id="wm-now-ico" aria-hidden="true">🎵</div>
                        <div class="wm-now-meta">
                            <p class="wm-now-title" id="wm-now-title"><?php echo $fun_names ? 'Nothing queued yet' : 'Nothing queued'; ?></p>
                            <p class="wm-now-sub" id="wm-now-sub"></p>
                        </div>
                    </div>

                    <div class="wm-controls" id="wm-controls">
                        <button type="button" class="wm-ctrl" id="wm-prev" title="<?php echo $fun_names ? 'Previous' : 'Previous'; ?>" aria-label="Previous">⏮</button>
                        <button type="button" class="wm-ctrl primary" id="wm-play" title="<?php echo $fun_names ? 'Play / Pause' : 'Play / Pause'; ?>" aria-label="Play">▶️</button>
                        <button type="button" class="wm-ctrl" id="wm-next" title="<?php echo $fun_names ? 'Next' : 'Next'; ?>" aria-label="Next">⏭</button>
                    </div>

                    <div class="wm-player" id="wm-player" hidden></div>
                    <div class="wm-player-actions" id="wm-player-actions" hidden>
                        <a class="wm-open" id="wm-open" href="#" target="_blank" rel="noopener noreferrer"><?php echo $fun_names ? 'Open in app ↗' : 'Open ↗'; ?></a>
                        <button type="button" class="wm-btn ghost" id="wm-remove-current"><?php echo $fun_names ? 'Remove from queue' : 'Remove'; ?></button>
                    </div>
                    <p class="wm-note" id="wm-note"></p>

                    <div class="wm-queue">
                        <div class="wm-queue-head">
                            <p class="wm-queue-title"><?php echo $fun_names ? 'Your queue' : 'Queue'; ?></p>
                            <button type="button" class="wm-btn ghost" id="wm-clear-queue" style="padding:4px 10px;font-size:0.78rem;"><?php echo $fun_names ? 'Clear all' : 'Clear all'; ?></button>
                        </div>
                        <div id="wm-queue-list"></div>
                    </div>
                </div>
            </div>

            <?php if ($showApproveLink): ?>
            <div class="platform-desk dash-tile" data-tile-id="platform-desk" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Platform desk' : 'Platform desk'; ?>" id="platform-desk">
                <strong><?php echo $fun_names ? 'Platform desk' : 'Admin'; ?></strong>
                · <a href="/platform"><?php echo $fun_names ? 'All restaurants &amp; users' : 'Platform console'; ?></a>
                · <a href="/approve-users"><?php echo $fun_names ? 'Approve people waiting for access' : 'Approve pending users'; ?></a>
                <?php
                try {
                    $pendingN = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE access_status = 'pending'")->fetchColumn();
                    if ($pendingN > 0) {
                        echo ' <span style="opacity:0.8;">(' . $pendingN . ' pending)</span>';
                    }
                } catch (Exception $e) {}
                ?>
            </div>
            <?php endif; ?>

        <?php if ($theme_catalog): ?>
        <div class="theme-quick dash-tile" data-tile-id="theme" data-tile-size="full" data-tile-label="<?php echo $fun_names ? 'Theme switcher' : 'Theme switcher'; ?>" aria-label="<?php echo $fun_names ? 'Change look and feel' : 'Change theme'; ?>">
            <h2><?php echo $fun_names ? 'Change the vibe?' : 'Want a different look?'; ?></h2>
            <p class="tq-lead">
                <?php
                $activeThemeName = function_exists('pbj_theme_display_name') ? pbj_theme_display_name($theme_id) : $theme_id;
                echo $fun_names
                    ? 'You’re on <strong>' . htmlspecialchars($activeThemeName) . '</strong> — tap a look to switch instantly ✨'
                    : 'Current theme: <strong>' . htmlspecialchars($activeThemeName) . '</strong>. Tap one to switch right away.';
                ?>
            </p>
            <div class="theme-quick-grid">
                <?php foreach ($theme_catalog as $t):
                    $tid = (string) ($t['id'] ?? '');
                    if ($tid === '') {
                        continue;
                    }
                    $isActive = ($tid === $theme_id);
                    $isDarkTheme = !empty($t['dark']);
                    $preview = (string) ($t['preview'] ?? '');
                    if ($preview !== '' && $preview[0] !== '/' && !str_starts_with($preview, 'http')) {
                        $preview = '/' . ltrim($preview, '/');
                    }
                    $swatches = is_array($t['swatches'] ?? null) ? $t['swatches'] : [];
                    $emoji = (string) ($t['emoji'] ?? '🎨');
                    $tname = (string) ($t['name'] ?? $tid);
                ?>
                <a
                    class="theme-quick-card<?php echo $isActive ? ' is-active' : ''; ?><?php echo $isDarkTheme ? ' is-dark' : ''; ?>"
                    href="/home?theme=<?php echo htmlspecialchars(urlencode($tid)); ?>"
                    <?php if ($isActive): ?>aria-current="true"<?php endif; ?>
                >
                    <?php if ($preview !== ''): ?>
                        <img class="tq-preview" src="<?php echo htmlspecialchars($preview); ?>" alt="" width="52" height="52">
                    <?php else: ?>
                        <span class="tq-preview" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;"><?php echo htmlspecialchars($emoji); ?></span>
                    <?php endif; ?>
                    <span class="tq-name"><?php echo htmlspecialchars($tname); ?></span>
                    <?php if ($swatches): ?>
                    <span class="tq-swatches" aria-hidden="true">
                        <?php foreach (array_slice($swatches, 0, 4) as $sw): ?>
                            <span class="tq-dot" style="background:<?php echo htmlspecialchars((string) $sw); ?>;"></span>
                        <?php endforeach; ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($isActive): ?>
                        <span class="tq-badge"><?php echo $fun_names ? 'On now' : 'Active'; ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <a class="theme-quick-more" href="/settings/theme">
                <?php echo $fun_names ? 'More look &amp; feel options →' : 'Open full theme settings →'; ?>
            </a>
        </div>
        <?php endif; ?>

        </div><!-- /.dash-tiles -->

        <div id="sc-modal-backdrop" class="sc-modal-backdrop" hidden>
            <div class="sc-modal" role="dialog" aria-modal="true" aria-labelledby="sc-modal-title">
                <div class="sc-modal-head">
                    <h2 id="sc-modal-title"><?php echo $fun_names ? 'Pick a pin' : 'Add shortcut'; ?></h2>
                    <button type="button" class="sc-modal-close" id="sc-modal-close" aria-label="Close">&times;</button>
                </div>
                <p class="sc-modal-hint" id="sc-modal-hint">
                    <?php echo $fun_names
                        ? 'Choose something you open a lot — max 4 pins on your home.'
                        : 'Choose a destination. You can pin up to 4 shortcuts on home.'; ?>
                </p>
                <div id="sc-picker-body"></div>
            </div>
        </div>

        <div class="dash-logo-wrap">
            <img src="/my-logo.png?v=20260716c" alt="ilovepbj ops" width="210" height="194" decoding="async" loading="lazy">
        </div>
        <p class="ideas-blurb">
            <?php if ($fun_names): ?>
                Got a sprinkle of an idea? Email us anytime at
            <?php else: ?>
                Have a suggestion or idea? Email us anytime at
            <?php endif; ?>
            <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a><?php echo $fun_names ? ' 💕' : '.'; ?>
        </p>
        <p class="legal-blurb">
            <a href="/privacy">Privacy</a>
            · <a href="/terms">Terms</a>
            · <a href="/refunds">Cancel &amp; refunds</a>
        </p>
    </div>

    <script>
    window.PBJ_HOME_SHORTCUTS = {
        max: 4,
        funNames: <?php echo $fun_names ? 'true' : 'false'; ?>,
        apiUrl: '/user-prefs-api.php',
        groups: <?php echo json_encode($scGroups, JSON_UNESCAPED_UNICODE); ?>,
        catalog: <?php echo json_encode($scCatalog, JSON_UNESCAPED_UNICODE); ?>,
        labels: {
            edit: <?php echo json_encode($fun_names ? 'Customize' : 'Customize'); ?>,
            done: <?php echo json_encode($fun_names ? 'Done' : 'Done'); ?>,
            add: <?php echo json_encode($fun_names ? 'Add pin' : 'Add'); ?>,
            full: <?php echo json_encode($fun_names ? 'You already have 4 pins — remove one first 💕' : 'You already have 4 shortcuts. Remove one to add another.'); ?>,
            syncing: <?php echo json_encode($fun_names ? 'Syncing pins…' : 'Syncing…'); ?>,
            synced: <?php echo json_encode($fun_names ? 'Synced across your devices 💕' : 'Synced across your devices'); ?>,
            offline: <?php echo json_encode($fun_names ? 'Offline — saved on this device' : 'Offline — saved on this device'); ?>,
            localOnly: <?php echo json_encode($fun_names ? 'On this device' : 'This device only'); ?>
        }
    };
    </script>
    <script src="/home-shortcuts.js?v=2"></script>
    <script src="/home-dashboard-tiles.js?v=2"></script>
    <script src="/shared-state.js?v=4"></script>
    <script src="/pos-sync-client.js?v=3"></script>
    <script src="/ops-nudges.js?v=3"></script>
    <script src="/daily-pulse.js?v=5"></script>
    <script>
    (function () {
        function loadJson(k, f) {
            try { var r = JSON.parse(localStorage.getItem(k) || 'null'); return r != null ? r : f; } catch (e) { return f; }
        }
        function checkSetup() {
            var ban = document.getElementById('house-setup-banner');
            if (!ban) return;
            if (loadJson('pbj_house_setup_dismissed_v1', {}).hide) {
                ban.hidden = true;
                ban.classList.add('is-user-hidden');
                return;
            }
            // Managers only
            if (window.PbjPerms && window.PbjPerms.loaded) {
                var r = window.PbjPerms.role;
                if (r !== 'owner' && r !== 'gm' && r !== 'admin' && r !== 'manager' && !window.PbjPerms.can('admin.reports.view')) {
                    ban.hidden = true;
                    return;
                }
            }
            var checks = 0, total = 7;
            function hasSales() {
                var s = loadJson('pbj_admin_sales_v2', null) || loadJson('pbj_admin_sales_v1', { days: [] });
                return s && Array.isArray(s.days) && s.days.some(function (d) { return d && (parseFloat(d.net) > 0 || parseFloat(d.gross) > 0); });
            }
            function hasLabor() {
                var l = loadJson('pbj_admin_labor_v2', null) || loadJson('pbj_admin_labor_v1', { days: [] });
                return l && Array.isArray(l.days) && l.days.length > 0;
            }
            function hasOpex() {
                var p = loadJson('pbj_admin_pnl_v1', null);
                if (!p || !p.expenses) return false;
                return Object.keys(p.expenses).some(function (k) { return p.expenses[k] && parseFloat(p.expenses[k].monthly) > 0; });
            }
            function hasCash() {
                var c = loadJson('pbj_admin_cash_v1', { days: [] });
                return (c.days || []).some(function (d) { return d && !isNaN(parseFloat(d.expected)) && !isNaN(parseFloat(d.counted)); });
            }
            function hasTeam() {
                var t = loadJson('pbj_admin_team_v2', null) || loadJson('pbj_admin_team_v1', null);
                return t && Array.isArray(t.people) && t.people.length > 0;
            }
            function hasSched() {
                var s = loadJson('pbj_admin_schedules_v1', null);
                if (!s || !s.weeks) return false;
                return Object.keys(s.weeks).some(function (k) { return Array.isArray(s.weeks[k]) && s.weeks[k].length; });
            }
            function hasInv() {
                var a = loadJson('pbj_invoice_cost_applied_v1', {});
                return Object.keys(a).some(function (k) { return a[k] && a[k].at; });
            }
            if (hasTeam()) checks++;
            if (hasSched()) checks++;
            if (hasSales()) checks++;
            if (hasSales() && hasLabor()) checks++;
            if (hasOpex()) checks++;
            if (hasCash()) checks++;
            if (hasInv()) checks++;
            if (checks >= total) {
                ban.hidden = true;
                return;
            }
            ban.hidden = false;
            ban.classList.remove('is-user-hidden');
            var meta = document.getElementById('house-setup-meta');
            if (meta) meta.textContent = checks + ' of ' + total + ' done — tap to continue setup';
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', checkSetup);
        else checkSetup();
        document.addEventListener('pbj-perms-ready', checkSetup);
        document.addEventListener('visibilitychange', function () { if (!document.hidden) checkSetup(); });
    })();
    </script>
    <script>
    (function () {
        function load(key) {
            try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; }
        }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function countPosts(key, field) {
            var r = load(key);
            if (!r) return 0;
            var arr = r[field];
            return Array.isArray(arr) ? arr.length : 0;
        }

        var ann = countPosts('pbj_jelly_announcements_v1', 'posts');
        var br = load('pbj_jelly_broadcasts_v1');
        var brPosts = (br && Array.isArray(br.posts)) ? br.posts : [];
        var notes = load('pbj_jelly_shift_notes_v1');
        var noteList = (notes && Array.isArray(notes.notes)) ? notes.notes : [];
        var today = todayStr();
        var handoffsToday = noteList.filter(function (n) { return n.date === today; }).length;
        var dms = load('pbj_jelly_dms_v1');
        var threads = (dms && Array.isArray(dms.threads)) ? dms.threads.length : 0;
        var foh = countPosts('pbj_jelly_foh_v1', 'posts');
        var boh = countPosts('pbj_jelly_boh_v1', 'posts');
        var openPosts = ann + brPosts.length + foh + boh;

        document.getElementById('stat-posts').textContent = String(openPosts);
        document.getElementById('stat-handoffs').textContent = String(handoffsToday);
        document.getElementById('stat-chats').textContent = String(threads);
    })();
    </script>
    <script>
    (function () {
        var fun = <?php echo $fun_names ? 'true' : 'false'; ?>;
        var HIDE_KEY = 'pbj_creator_news_hidden_v1';
        var SPOT_KEY = 'pbj_admin_spotlight_v1';
        var SPOT_SHARED = 'admin_spotlight_v1';

        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function fmtDate(s) {
            if (!s) return '';
            var p = String(s).split('-');
            if (p.length !== 3) return s;
            var dt = new Date(+p[0], +p[1] - 1, +p[2]);
            if (isNaN(dt.getTime())) return s;
            return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        // —— Current star ——
        function pickCurrent(state) {
            if (!state || !Array.isArray(state.awards) || !state.awards.length) return null;
            if (state.currentId) {
                var hit = state.awards.find(function (a) { return a.id === state.currentId; });
                if (hit) return hit;
            }
            var today = todayStr();
            var inWin = state.awards.filter(function (a) {
                return a.startDate && a.endDate && a.startDate <= today && a.endDate >= today;
            });
            if (inWin.length) {
                inWin.sort(function (a, b) { return (b.at || 0) - (a.at || 0); });
                return inWin[0];
            }
            return state.awards.slice().sort(function (a, b) { return (b.at || 0) - (a.at || 0); })[0];
        }
        function paintStar(state) {
            var el = document.getElementById('star-banner');
            if (!el) return;
            var a = pickCurrent(state);
            if (!a || !a.personName) {
                el.hidden = true;
                return;
            }
            el.hidden = false;
            var kicker = a.label || (fun ? 'Star of the house' : 'Star of the house');
            var kEl = document.getElementById('star-banner-kicker');
            var nEl = document.getElementById('star-banner-name');
            var mEl = document.getElementById('star-banner-meta');
            if (kEl) kEl.textContent = kicker;
            if (nEl) nEl.textContent = a.personName;
            var meta = [];
            if (a.startDate || a.endDate) meta.push(fmtDate(a.startDate) + ' – ' + fmtDate(a.endDate));
            if (a.roles && a.roles.length) meta.push(Array.isArray(a.roles) ? a.roles.join(' · ') : String(a.roles));
            else if (a.shoutout) meta.push(String(a.shoutout).replace(/\s+/g, ' ').slice(0, 72));
            if (mEl) {
                mEl.textContent = meta.join(' · ');
                mEl.style.display = meta.length ? '' : 'none';
            }
            el.setAttribute('aria-label', kicker + ': ' + a.personName);
        }
        function loadStarLocal() {
            try {
                var r = JSON.parse(localStorage.getItem(SPOT_KEY) || 'null');
                paintStar(r);
            } catch (e) { paintStar(null); }
        }
        loadStarLocal();
        if (window.PbjSharedState) {
            var spotShared = new PbjSharedState({
                key: SPOT_SHARED,
                pollMs: 8000,
                onRemote: function (payload) {
                    if (payload && Array.isArray(payload.awards)) {
                        localStorage.setItem(SPOT_KEY, JSON.stringify(payload));
                        paintStar(payload);
                    }
                },
                onStatus: function () {}
            });
            spotShared.bootstrap(
                function () {
                    try { return JSON.parse(localStorage.getItem(SPOT_KEY) || 'null'); } catch (e) { return null; }
                },
                function (payload) {
                    if (payload && Array.isArray(payload.awards)) {
                        localStorage.setItem(SPOT_KEY, JSON.stringify(payload));
                        paintStar(payload);
                    }
                }
            );
        }

        // —— Creator's news (one post for all homes; hide is per-device preference) ——
        var newsBox = document.getElementById('creator-news');
        var newsBody = document.getElementById('creator-news-body');
        var newsEdit = document.getElementById('creator-news-edit');
        var editBtn = document.getElementById('creator-news-edit-btn');
        var saveBtn = document.getElementById('creator-news-save-btn');
        var cancelBtn = document.getElementById('creator-news-cancel-btn');
        var hideBtn = document.getElementById('creator-news-hide-btn');
        var statusEl = document.getElementById('creator-news-status');
        var hintEl = document.getElementById('creator-news-hint');
        var canEditNews = !!(newsBox && newsBox.getAttribute('data-can-edit') === '1');
        var newsText = newsBody ? String(newsBody.textContent || '').trim() : '';
        var newsUpdatedAt = newsBox ? parseInt(newsBox.getAttribute('data-updated-at') || '0', 10) || 0 : 0;
        var editingNews = false;

        function newsHidden() {
            try { return localStorage.getItem(HIDE_KEY) === '1'; } catch (e) { return false; }
        }
        function setNewsHidden(v) {
            try { localStorage.setItem(HIDE_KEY, v ? '1' : '0'); } catch (e) {}
        }
        function setStatus(msg, kind) {
            if (!statusEl) return;
            statusEl.textContent = msg || '';
            statusEl.classList.remove('ok', 'err');
            if (kind) statusEl.classList.add(kind);
        }
        function setViewMode() {
            editingNews = false;
            if (newsEdit) newsEdit.hidden = true;
            if (newsBody) newsBody.hidden = false;
            if (editBtn) editBtn.hidden = !canEditNews;
            if (saveBtn) {
                saveBtn.hidden = true;
                saveBtn.disabled = false;
            }
            if (cancelBtn) cancelBtn.hidden = true;
            if (hideBtn) hideBtn.hidden = false;
        }
        function setEditMode() {
            editingNews = true;
            setNewsHidden(false); // editing unhides on this device
            if (newsBox) newsBox.hidden = false;
            if (newsEdit) {
                newsEdit.value = newsText;
                newsEdit.hidden = false;
                try { newsEdit.focus(); } catch (e) {}
            }
            if (newsBody) newsBody.hidden = true;
            if (editBtn) editBtn.hidden = true;
            if (saveBtn) {
                saveBtn.hidden = false;
                saveBtn.disabled = false;
            }
            if (cancelBtn) cancelBtn.hidden = false;
            if (hideBtn) hideBtn.hidden = true;
            setStatus(fun ? 'Editing — Save posts this to every home 💕' : 'Editing — Save posts this to all dashboards.');
        }
        function showNewsUi() {
            if (!newsBox) return;
            // Crew: honor Hide preference; no box when empty
            if (!canEditNews) {
                if (newsHidden() || !newsText) {
                    newsBox.hidden = true;
                    return;
                }
            }
            // Creator/platform admin: always show so Edit/Save stay available
            newsBox.hidden = false;
            if (newsBody && !editingNews) {
                newsBody.textContent = newsText || '';
            }
            if (hintEl) hintEl.hidden = !canEditNews;
            if (!editingNews) setViewMode();
        }
        function applyNewsPayload(data) {
            if (!data || !data.ok) return;
            canEditNews = !!data.canEdit;
            if (newsBox) newsBox.setAttribute('data-can-edit', canEditNews ? '1' : '0');
            var nextText = String(data.text || '');
            var nextAt = parseInt(data.updatedAt || 0, 10) || 0;
            if (editingNews && nextAt <= newsUpdatedAt) return;
            if (!editingNews || nextAt > newsUpdatedAt) {
                newsText = nextText;
                newsUpdatedAt = nextAt;
                if (newsBox) newsBox.setAttribute('data-updated-at', String(newsUpdatedAt));
                if (newsBody && !editingNews) newsBody.textContent = newsText || '';
                if (newsEdit && !editingNews) newsEdit.value = newsText;
            }
            showNewsUi();
        }
        function loadNews() {
            fetch('/creator-news-api.php', { credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(applyNewsPayload)
                .catch(function () { showNewsUi(); });
        }
        if (hideBtn) {
            hideBtn.addEventListener('click', function () {
                if (canEditNews) {
                    // Creator still needs the box to post — hide is for crew only
                    setStatus(fun
                        ? 'Hide is for your crew — as creator you always see this box so you can edit 💕'
                        : 'As the creator you always see this box so you can edit. Crew can hide it.');
                    return;
                }
                setNewsHidden(true);
                if (newsBox) newsBox.hidden = true;
                setStatus(fun
                    ? 'Hidden on your home. Re-show anytime under Settings → Home dashboard 💕'
                    : 'Hidden on your home. Turn it back on under Settings.');
            });
        }
        if (editBtn) {
            editBtn.addEventListener('click', function () {
                if (!canEditNews) {
                    setStatus(fun ? 'Only the app creator can edit this 🔒' : 'Only the app creator can edit this.', 'err');
                    return;
                }
                setEditMode();
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                setViewMode();
                if (newsBody) newsBody.textContent = newsText || '';
                setStatus('');
                showNewsUi();
            });
        }
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                if (!canEditNews || !newsEdit) return;
                var text = String(newsEdit.value || '').trim();
                saveBtn.disabled = true;
                setStatus(fun ? 'Saving for everyone…' : 'Saving…');
                fetch('/creator-news-api.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text: text })
                })
                    .then(function (r) {
                        return r.json().then(function (data) {
                            return { okHttp: r.ok, data: data };
                        });
                    })
                    .then(function (pack) {
                        saveBtn.disabled = false;
                        var data = pack.data;
                        if (!pack.okHttp || !data || !data.ok) {
                            var msg = (data && (data.message || data.error)) || (fun ? 'Could not save' : 'Could not save');
                            if (data && data.error === 'forbidden') {
                                msg = fun
                                    ? 'Only the app creator can edit Creator’s news 🔒'
                                    : 'Only the app creator can edit this.';
                            }
                            setStatus(msg, 'err');
                            return;
                        }
                        newsText = String(data.text || '');
                        newsUpdatedAt = parseInt(data.updatedAt || 0, 10) || Date.now();
                        canEditNews = true;
                        if (newsBox) {
                            newsBox.setAttribute('data-updated-at', String(newsUpdatedAt));
                            newsBox.setAttribute('data-can-edit', '1');
                        }
                        setNewsHidden(false);
                        setViewMode();
                        if (newsBody) newsBody.textContent = newsText || '';
                        showNewsUi();
                        setStatus(
                            newsText
                                ? (fun ? 'Saved — live on every home dashboard ✨' : 'Saved — visible on all dashboards.')
                                : (fun ? 'Cleared — no news shown to the crew' : 'Cleared — nothing posted.'),
                            'ok'
                        );
                        setTimeout(function () { setStatus(''); }, 4500);
                    })
                    .catch(function () {
                        saveBtn.disabled = false;
                        setStatus(fun ? 'Network error — try again' : 'Network error — try again', 'err');
                    });
            });
        }

        showNewsUi();
        loadNews();
        setInterval(function () {
            if (!document.hidden && !editingNews) loadNews();
        }, 45000);

    })();
    </script>
    <script src="/work-music.js?v=2"></script>
    <script>
    (function () {
        if (window.PbjWorkMusic) {
            PbjWorkMusic.init({ funNames: <?php echo $fun_names ? 'true' : 'false'; ?> });
        }
    })();
    </script>
    <script>
    (function () {
        var isSweet = <?php echo $fun_names ? 'true' : 'false'; ?>;
        function paintListCompletions() {
            var card = document.getElementById('list-complete-card');
            var body = document.getElementById('list-complete-body');
            if (!card || !body) return;
            if (!window.PbjListComplete || !PbjListComplete.canReceive()) {
                card.classList.remove('show');
                card.hidden = true;
                return;
            }
            var events = PbjListComplete.todaysEvents().slice(0, 6);
            if (!events.length) {
                card.classList.remove('show');
                card.hidden = true;
                return;
            }
            card.hidden = false;
            card.classList.add('show');
            body.innerHTML = events.map(function (ev) {
                var when = '';
                try {
                    when = new Date(ev.at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                } catch (e) { when = ''; }
                var title = (ev.pageTitle || 'Checklist') +
                    (ev.listTitle && ev.listTitle !== ev.pageTitle ? ' · ' + ev.listTitle : '');
                var href = ev.href || '#';
                var who = ev.completedBy || 'Team';
                return '<div class="lc-item">' +
                    (href && href !== '#'
                        ? '<a href="' + href.replace(/"/g, '&quot;') + '">' + title.replace(/</g, '&lt;') + '</a>'
                        : '<strong>' + title.replace(/</g, '&lt;') + '</strong>') +
                    '<div class="lc-meta">' +
                    (isSweet ? 'Finished by ' : 'Completed by ') + String(who).replace(/</g, '&lt;') +
                    (when ? ' · ' + when : '') +
                    '</div></div>';
            }).join('');
        }
        function boot() {
            paintListCompletions();
            document.addEventListener('pbj-list-complete', paintListCompletions);
            setInterval(paintListCompletions, 15000);
        }
        if (window.PbjPerms && PbjPerms.ready) PbjPerms.ready.then(boot);
        else {
            document.addEventListener('pbj-perms-ready', boot);
            setTimeout(boot, 1500);
        }
    })();
    </script>
    <script>
    (function () {
        var isSweet = <?php echo $fun_names ? 'true' : 'false'; ?>;
        var wrap = document.getElementById('a2hs-wrap');
        var btn = document.getElementById('a2hs-btn');
        var hint = document.getElementById('a2hs-hint');
        if (!wrap || !btn) return;

        function isStandalone() {
            if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true;
            if (window.navigator.standalone === true) return true;
            return false;
        }
        if (isStandalone()) {
            wrap.hidden = true;
            return;
        }

        var deferredPrompt = null;
        var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent || '');
        var isSafari = isIos && /safari/i.test(navigator.userAgent || '') && !/crios|fxios|edgios/i.test(navigator.userAgent || '');

        // Always show on iOS Safari (instructions); Android shows after beforeinstallprompt or always with fallback tips
        wrap.hidden = false;

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;
            wrap.hidden = false;
        });

        window.addEventListener('appinstalled', function () {
            deferredPrompt = null;
            wrap.hidden = true;
        });

        btn.addEventListener('click', function () {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.finally(function () {
                    deferredPrompt = null;
                });
                if (hint) hint.hidden = true;
                return;
            }
            // Fallback instructions
            if (hint) {
                hint.hidden = false;
                if (isIos) {
                    hint.innerHTML = isSweet
                        ? '<strong>iPhone / iPad:</strong> tap the <strong>Share</strong> button (square with arrow), then <strong>Add to Home Screen</strong>. Open from your home screen next time 💕'
                        : '<strong>iPhone / iPad:</strong> tap Share, then <strong>Add to Home Screen</strong>. Open from your home screen icon next time.';
                } else {
                    hint.innerHTML = isSweet
                        ? '<strong>Android / Chrome:</strong> open the browser menu (⋮) and choose <strong>Install app</strong> or <strong>Add to Home screen</strong>. Then open ilovepbj from your home screen 💕'
                        : '<strong>Android / Chrome:</strong> open the browser menu and choose <strong>Install app</strong> or <strong>Add to Home screen</strong>.';
                }
            }
        });

        // Register minimal service worker (helps installability on Chrome)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js?v=5').catch(function () {});
        }
    })();
    </script>
</body>
</html>
