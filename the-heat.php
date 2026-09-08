<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$__tf = pbj_theme_flags();
$theme_id = $__tf['id'];
$is_neon = $__tf['neon'];
$is_farm = !empty($__tf['farm']);
$is_urban = !empty($__tf['urban']);
$is_coffee = !empty($__tf['coffee']);
$is_classic_sweet = $__tf['classic_sweet'];
$is_sweet = $__tf['fun']; // fun labels + stickers (Sweet + Neon)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>

    <title><?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>

    <?php if (!empty($__tf['basic'])): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <?php if (function_exists('pbj_render_neon_font_faces')) { pbj_render_neon_font_faces(); } ?>
    <?php if (function_exists('pbj_render_farm_font_faces')) { pbj_render_farm_font_faces(); } ?>
    <?php if (function_exists('pbj_render_urban_font_faces')) { pbj_render_urban_font_faces(); } ?>
    <?php if (function_exists('pbj_render_coffee_font_faces')) { pbj_render_coffee_font_faces(); } ?>

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
                font-family: 'DreamingOutLoudPro', serif;
                background: #FCF8EE;
                color: #3a2f1f;
            <?php elseif ($is_neon): ?>
                font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif;
                background: #0A0A0A;
                color: #F5F5F7;
            <?php elseif (!empty($is_farm)): ?>
                font-family: 'Notepen', 'NotepenRegular', Georgia, serif;
                background: #F3E6D4;
                color: #2C2416;
            <?php elseif (!empty($is_urban)): ?>
                font-family: 'Kelly Slab', 'KellySlab-Regular', Georgia, serif;
                background: #000000;
                color: #F5F5F5;
            <?php elseif (!empty($is_coffee)): ?>
                font-family: 'Ambery Garden', 'AmberyGarden-Regular', Georgia, serif;
                background: #F5EDE3;
                color: #3D2416;
            <?php else: ?>
                font-family: 'Lora', serif;
                background: #F1EBE4;
                color: #1A2A44;
            <?php endif; ?>
        }

        .header {
            <?php if ($is_classic_sweet): ?>
                background: #E55163;
                color: white;
            <?php elseif ($is_neon): ?>
                background: #0D0D0D;
                color: #FF2ECB;
                border-bottom: 2px solid #00F0FF;
                box-shadow: 0 0 18px rgba(255,46,203,0.35);
            <?php elseif (!empty($is_farm)): ?>
                background: #2F6B3A;
                color: #FFF8EE;
                border-bottom: 3px solid #E07A2F;
                box-shadow: 0 6px 18px rgba(47,107,58,0.22);
            <?php elseif (!empty($is_urban)): ?>
                background: #0A0A0A;
                color: #FFFFFF;
                border-bottom: 2px solid #FF2D00;
                box-shadow: 0 0 18px rgba(255,45,0,0.28);
            <?php elseif (!empty($is_coffee)): ?>
                background: #4A2C1A;
                color: #F5EDE3;
                border-bottom: 3px solid #C4A484;
                box-shadow: 0 6px 18px rgba(74,44,26,0.2);
            <?php else: ?>
                background: #1A2A44;
                color: white;
            <?php endif; ?>
            padding: 25px;
            text-align: center;
        }

        h1 {
            <?php if ($is_classic_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: inherit;
            <?php elseif ($is_neon): ?>
                font-family: 'Warnes', system-ui, sans-serif;
                font-weight: 700;
                color: #FF2ECB;
                text-shadow: 0 0 16px rgba(255,46,203,0.5);
            <?php elseif (!empty($is_farm)): ?>
                font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive;
                font-weight: 700;
                color: #FFF8EE;
            <?php elseif (!empty($is_urban)): ?>
                font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif;
                font-weight: 400;
                color: #FF2D00;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            <?php elseif (!empty($is_coffee)): ?>
                font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif;
                font-weight: 700;
                color: #F5EDE3;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: inherit;
            <?php endif; ?>
            font-size: 3.8rem;
            margin: 0;
        }

        .subtitle {
            margin: 10px 0 0;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .content {
            padding: 24px 14px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .card {
            background: <?php echo $is_neon ? '#141414' : 'white'; ?>;
            <?php if ($is_neon): ?>border: 1px solid #FF2ECB; color: #F5F5F7; box-shadow: 0 0 0 1px rgba(255,46,203,0.15), 0 8px 24px rgba(0,0,0,0.5);<?php endif; ?>
            border-radius: 16px;
            padding: 16px 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card h3 {
            <?php if ($is_classic_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
            <?php elseif ($is_neon): ?>
                font-family: 'Warnes', system-ui, sans-serif;
                font-weight: 700;
                color: #FF2ECB;
            <?php else: ?>
                font-family: 'Lora', serif;
            <?php endif; ?>
            font-size: 1.15rem;
            margin: 8px 0 4px;
        }

        .card p {
            margin: 0;
            font-size: 0.82rem;
            opacity: 0.75;
            line-height: 1.35;
        }

        .card-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            overflow: hidden;
            <?php if ($is_classic_sweet): ?>
                background: #FFF5F6;
                border: 2px solid #E55163;
            <?php elseif ($is_neon): ?>
                background: #0F0F0F;
                border: 2px solid #00F0FF;
                box-shadow: 0 0 12px rgba(0,240,255,0.35);
            <?php else: ?>
                background: #EEF2F8;
                border: 2px solid #1A2A44;
            <?php endif; ?>
        }

        .card-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        <?php if ($is_sweet): ?>
        .card-icon.sweet-sticker {
            
            border-radius: 16px;
            background: <?php echo $is_neon ? '#0F0F0F' : '#FFFBFA'; ?>;
            padding: 4px;
            box-sizing: border-box;
        }
        .card-icon.sweet-sticker img {
            object-fit: contain;
            border-radius: 12px;
        }
        <?php endif; ?>

        /* Service pulse — live FOH wait + to-gos for the kitchen */
        .service-pulse {
            display: block;
            text-decoration: none;
            color: inherit;
            border-radius: 14px;
            padding: 10px 14px;
            margin: 0 0 14px;
            box-sizing: border-box;
            <?php if ($is_classic_sweet): ?>
                background: linear-gradient(90deg, #FFF5F6, #FFFBF5);
                border: 2px solid #F3C5CC;
                box-shadow: 0 4px 12px rgba(229,81,99,0.1);
            <?php elseif ($is_neon): ?>
                background: #141414;
                border: 1px solid #00F0FF;
                box-shadow: 0 0 14px rgba(0,240,255,0.2);
                color: #F5F5F7;
            <?php elseif (!empty($is_farm)): ?>
                background: #FFF8EE;
                border: 2px solid #E07A2F;
            <?php elseif (!empty($is_urban)): ?>
                background: #121212;
                border: 1px solid #FF2D00;
                color: #F5F5F5;
            <?php elseif (!empty($is_coffee)): ?>
                background: #FFFAF5;
                border: 2px solid #C4A484;
            <?php else: ?>
                background: #FFFFFF;
                border: 1px solid #C5D0DE;
                box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            <?php endif; ?>
        }
        .service-pulse[hidden] { display: none !important; }
        .service-pulse.is-alert {
            <?php if ($is_classic_sweet): ?>border-color: #E55163; background: linear-gradient(90deg, #FFE8EC, #FFF5F6);
            <?php elseif ($is_neon): ?>border-color: #FF2ECB; box-shadow: 0 0 16px rgba(255,46,203,0.35);
            <?php elseif (!empty($is_farm)): ?>border-color: #C45C26;
            <?php elseif (!empty($is_urban)): ?>border-color: #FF2D00;
            <?php else: ?>border-color: #C62828;<?php endif; ?>
        }
        .service-pulse.is-ready {
            <?php if ($is_classic_sweet): ?>border-color: #2E9B63; background: linear-gradient(90deg, #E8F8F1, #FFFBF8);
            <?php elseif ($is_neon): ?>border-color: #00F0FF;
            <?php else: ?>border-color: #2E9B63;<?php endif; ?>
            animation: pulseReady 1.6s ease-in-out infinite;
        }
        @keyframes pulseReady {
            0%, 100% { box-shadow: 0 0 0 0 rgba(46, 155, 99, 0.25); }
            50% { box-shadow: 0 0 0 6px rgba(46, 155, 99, 0); }
        }
        .service-pulse-top {
            display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px;
        }
        .service-pulse-title {
            margin: 0; font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase;
            font-weight: 600; opacity: 0.7;
        }
        .service-pulse-link {
            font-size: 0.78rem; opacity: 0.8; color: inherit; text-decoration: none; white-space: nowrap;
        }
        .service-pulse-body {
            font-size: 0.9rem; line-height: 1.4;
            display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 0;
        }
        .service-pulse-bit { margin-right: 2px; }
        .service-pulse-sep { opacity: 0.4; margin: 0 6px; }
        .service-pulse-body strong {
            <?php if ($is_classic_sweet): ?>color: #E55163;
            <?php elseif ($is_neon): ?>color: #FF2ECB;
            <?php elseif (!empty($is_farm)): ?>color: #2F6B3A;
            <?php elseif (!empty($is_urban)): ?>color: #FF2D00;
            <?php else: ?>color: inherit;<?php endif; ?>
        }
</style>
    <?php if (function_exists('pbj_render_hub_card_css')) { pbj_render_hub_card_css(); } ?>
</head>
<body class="hub-page">
    <div class="header">
        <h1><?php echo pbj_hub_label('boh'); ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Kitchen ops, prep & line excellence' : 'Back of House Operations'; ?></p>
    </div>

    <div class="content">
        <a class="service-pulse" id="service-pulse" href="/FOH/reservations" hidden>
            <div class="service-pulse-top">
                <p class="service-pulse-title"><?php echo $is_sweet ? 'Service pulse' : 'Service pulse'; ?></p>
                <span class="service-pulse-link"><?php echo $is_sweet ? 'FOH board →' : 'Open board →'; ?></span>
            </div>
            <div class="service-pulse-body"></div>
        </a>

        <div class="tip hub-intro">
            <?php echo $is_sweet
                ? '💡 <strong>Recipe loop:</strong> Menu & Recipes → ingredient cards → Sandwich HQ costing & inventory. Build once, cost everywhere 💕'
                : '<strong>Recipe loop:</strong> Menu & Recipes feed costing and inventory. Build ingredients once.'; ?>
        </div>
        <p class="hub-reorder-hint">Drag cards to rearrange · order saves on this device</p>
        <div class="grid hub-grid" data-hub-key="the-heat">
            <a href="/BOH/prep" class="card" data-card-id="prep" data-perm-any="boh.prep.check_off,boh.prep.print_all,boh.prep.add_station">
                <?php pbj_render_card_icon('the-heat/prep', 'Prep Lists & Line Checks', '🔪'); ?>
                <h3>Prep Lists & Line Checks</h3>
                <p><?php echo $is_sweet ? 'Daily prep from recipes + station readiness' : 'Daily prep from recipes and station readiness'; ?></p>
            </a>
            <a href="/BOH/opening-closing" class="card" data-card-id="opening-closing" data-perm-any="boh.opening.check_off,boh.opening.print">
                <?php pbj_render_card_icon('the-heat/opening-closing', 'Opening & Closing', '🌅'); ?>
                <h3>Opening & Closing</h3>
                <p><?php echo $is_sweet ? 'Open, mid-shift & close kitchen checklists' : 'Open, mid-shift, and close kitchen checklists'; ?></p>
            </a>
            <a href="/BOH/recipes" class="card" data-card-id="menu-recipes" data-perm-any="boh.recipes.menu_view,boh.recipes.std_view,boh.recipes.costing_view,boh.recipes.yields_use">
                <?php pbj_render_card_icon('the-heat/menu-recipes', 'Menu & Standardized Recipes', '📖'); ?>
                <h3>Menu & Standardized Recipes</h3>
                <p><?php echo $is_sweet ? 'Guest menu prices + house recipe specs' : 'Guest menu prices and house recipe specs'; ?></p>
            </a>
            <a href="/BOH/cleaning" class="card" data-card-id="cleaning" data-perm-any="boh.cleaning.check_off,boh.cleaning.print">
                <?php pbj_render_card_icon('the-heat/cleaning', 'Cleaning Schedule', '✨'); ?>
                <h3>Cleaning Schedule</h3>
                <p><?php echo $is_sweet ? 'Daily, weekly, deep & station cleans' : 'Daily, weekly, deep, and station cleans'; ?></p>
            </a>
            <a href="/BOH/temps" class="card" data-card-id="temps" data-perm-any="boh.temps.enter_temps,boh.temps.print">
                <?php pbj_render_card_icon('the-heat/temps', 'Temps & Food Safety', '🌡️'); ?>
                <h3>Temps & Food Safety</h3>
                <p><?php echo $is_sweet ? 'Shift temp logs with range flags' : 'Shift temp logs with range flags'; ?></p>
            </a>
            <a href="/BOH/tools" class="card" data-card-id="quick-tools" data-perm-any="boh.tools.use">
                <?php pbj_render_card_icon('the-heat/quick-tools', 'Quick Tools', '🛠️'); ?>
                <h3>Quick Tools</h3>
                <p><?php echo $is_sweet ? 'Converters, timers, yields & kitchen cheats' : 'Converters, timers, yields, and kitchen cheats'; ?></p>
            </a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
<script src="/shared-state.js?v=3"></script>
<script src="/service-pulse.js?v=1"></script>
<script src="/hub-card-order.js"></script>
<script>
(function () {
    if (window.PbjServicePulse) {
        PbjServicePulse.init({ funNames: <?php echo $is_sweet ? 'true' : 'false'; ?> });
    }
})();
</script>
</body>
</html>
