<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }

$theme_id = pbj_theme_id();
$is_sweet = ($theme_id === 'sweet');
$is_neon = ($theme_id === 'neon_diner');
$is_farm = ($theme_id === 'farm');
$is_urban = ($theme_id === 'urban');
$is_coffee = ($theme_id === 'coffee');
$fun = pbj_use_fun_names();
$hubs = pbj_hub_labels();

function pbj_help_map_icon(string $navKey, string $emojiFallback): void {
    $src = function_exists('pbj_icon') ? pbj_icon('nav/' . $navKey) : '';
    $label = function_exists('pbj_hub_label') ? pbj_hub_label($navKey === 'home' ? 'home' : $navKey) : $navKey;
    // map nav keys: home, foh, boh, admin, messages, settings
    $labelKey = $navKey;
    if (function_exists('pbj_hub_label')) {
        $label = pbj_hub_label($labelKey);
    }
    if ($src !== '') {
        $cls = 'map-icon';
        $tid = pbj_theme_id();
        if ($tid === 'sweet') {
            $cls .= ' sweet-sticker';
        } elseif (in_array($tid, ['neon_diner', 'farm', 'coffee', 'urban'], true)) {
            $cls .= ' theme-icon theme-icon-' . str_replace('_diner', '', $tid);
            if ($tid === 'neon_diner') {
                $cls .= ' theme-icon-neon';
            }
        }
        echo '<div class="' . htmlspecialchars($cls) . '">';
        echo '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($label) . '">';
        echo '</div>';
        return;
    }
    echo '<div class="map-icon">' . $emojiFallback . '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $fun ? 'Where\'s What?' : 'App Help'; ?> • <?php echo pbj_hub_label('settings'); ?> • ilovepbj ops</title>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <?php if (function_exists('pbj_render_neon_font_faces')) { pbj_render_neon_font_faces(); } ?>
    <?php if (function_exists('pbj_render_farm_font_faces')) { pbj_render_farm_font_faces(); } ?>
    <?php if ($is_urban): ?><?php if (function_exists('pbj_render_urban_font_faces')) { pbj_render_urban_font_faces(); } ?>
    <?php if ($is_coffee): ?><?php if (function_exists('pbj_render_coffee_font_faces')) { pbj_render_coffee_font_faces(); } ?><?php endif; ?><?php endif; ?>
    <?php if (function_exists('pbj_render_farm_font_faces')) { pbj_render_farm_font_faces(); } ?>
    <?php if (!$is_sweet && !$is_neon && !$is_farm): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;
            <?php elseif ($is_neon): ?>font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif; background: #0A0A0A; color: #F5F5F7;
            <?php elseif ($is_farm): ?>font-family: 'Notepen', 'NotepenRegular', Georgia, serif; background: #F3E6D4; color: #2C2416;
            <?php elseif ($is_urban): ?>font-family: 'Kelly Slab', 'KellySlab-Regular', Georgia, serif; background: #000000; color: #F5F5F5;
            <?php elseif ($is_coffee): ?>font-family: 'Ambery Garden', 'AmberyGarden-Regular', Georgia, serif; background: #F5EDE3; color: #3D2416;
            <?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?>
        }
        .header {
            <?php if ($is_sweet): ?>background: #E55163; color: white;
            <?php elseif ($is_neon): ?>background: #0D0D0D; color: #FF2ECB; border-bottom: 2px solid #00F0FF;
            <?php elseif ($is_farm): ?>background: #2F6B3A; color: #FFF8EE; border-bottom: 3px solid #E07A2F;
            <?php elseif ($is_urban): ?>background: #0A0A0A; color: #FFFFFF; border-bottom: 2px solid #FF2D00;
            <?php elseif ($is_coffee): ?>background: #4A2C1A; color: #F5EDE3; border-bottom: 3px solid #C4A484;
            <?php else: ?>background: #1A2A44; color: white;<?php endif; ?>
            padding: 20px 25px 25px; text-align: center;
        }
        .back-link { display: inline-block; <?php if ($is_neon): ?>color: #00F0FF;<?php elseif ($is_farm): ?>color: #F3E6D4;<?php else: ?>color: white;<?php endif; ?> text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; font-weight: 700; color: #FF2ECB;
            <?php elseif ($is_farm): ?>font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive; font-weight: 700; color: #FFF8EE;
            <?php elseif ($is_urban): ?>font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif; color: #FF2D00; letter-spacing: 0.06em; text-transform: uppercase;
            <?php elseif ($is_coffee): ?>font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif; font-weight: 700; color: #F5EDE3;
            <?php else: ?>font-family: 'Lora', serif;<?php endif; ?>
            font-size: 2.5rem; margin: 0;
        }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; <?php if ($is_neon): ?>color: #00F0FF;<?php elseif ($is_farm): ?>color: #F3E6D4;<?php endif; ?> }
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .intro {
            <?php if ($is_neon): ?>background: #141414; border: 1px solid #FF2ECB; color: #F5F5F7;
            <?php elseif ($is_farm): ?>background: #FFFBF5; border: 1px solid #D9C7A8; color: #2C2416;
            <?php else: ?>background: white;<?php endif; ?>
            border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45;
        }
        .map-card {
            display: block; text-decoration: none; color: inherit;
            <?php if ($is_neon): ?>background: #141414; border: 1px solid #FF2ECB; color: #F5F5F7;
            <?php elseif ($is_farm): ?>background: #FFFBF5; border: 1px solid #D9C7A8; color: #2C2416;
            <?php else: ?>background: white;<?php endif; ?>
            border-radius: 18px; padding: 18px 20px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: transform 0.2s;
        }
        .map-card:hover { transform: translateY(-3px); }
        .map-top { display: flex; gap: 14px; align-items: flex-start; }
        .map-icon {
            width: 64px; height: 64px; min-width: 64px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.6rem; overflow: hidden;
            <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;
            <?php elseif ($is_neon): ?>background: #0A0A0A; border: 2px solid #FF2ECB;
            <?php elseif ($is_farm): ?>background: #FFF8EE; border: 2px solid #E07A2F;
            <?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?>
        }
        .map-icon img { width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 12px; }
        .map-icon.sweet-sticker, .map-icon.theme-icon {
            width: 64px; height: 64px; min-width: 64px; border-radius: 16px; padding: 0; box-sizing: border-box;
        }
        .map-title {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB; font-weight: 700;
            <?php elseif ($is_farm): ?>font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive; color: #C43B2C; font-weight: 700;
            <?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
            font-size: 1.35rem; margin: 0 0 6px;
        }
        .map-body { margin: 0; opacity: 0.8; line-height: 1.4; font-size: 1rem; }
        .map-bits { margin: 10px 0 0; padding: 0; list-style: none; font-size: 0.92rem; opacity: 0.75; line-height: 1.5; }
        .btn {
            display: block; text-align: center; border-radius: 14px; padding: 14px 16px; text-decoration: none; margin-top: 8px;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #E55163; color: white;
            <?php elseif ($is_neon): ?>font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif; background: #FF2ECB; color: #0A0A0A;
            <?php elseif ($is_farm): ?>font-family: 'Notepen', 'NotepenRegular', Georgia, serif; background: #C43B2C; color: #FFF8EE;
            <?php else: ?>font-family: 'Lora', serif; background: #1A2A44; color: white;<?php endif; ?>
        }
        .tip {
            <?php if ($is_neon): ?>background: #141414; border: 1px solid #FF2ECB; color: #F5F5F7;
            <?php elseif ($is_farm): ?>background: #FFFBF5; border: 1px solid #D9C7A8; color: #2C2416;
            <?php else: ?>background: white;<?php endif; ?>
            border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45;
        }
        .tip h3 {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;
            <?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB;
            <?php elseif ($is_farm): ?>font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive; color: #C43B2C;
            <?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
            margin: 0 0 8px; font-size: 1.25rem;
        }
        .tip p { margin: 0; opacity: 0.85; line-height: 1.45; }
        .tip p + p { margin-top: 10px; }
    </style>
    <?php if (function_exists('pbj_render_icon_css')) { pbj_render_icon_css(); } ?>
</head>
<body>
    <div class="header">
        <a href="/settings" class="back-link">← <?php echo pbj_back_to_hub('settings'); ?></a>
        <h1><?php echo $fun ? 'Where\'s What?' : 'App Help'; ?></h1>
        <p class="subtitle"><?php echo $fun ? 'A cute map of the whole ops hub' : 'Quick map of where things live'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $fun
                ? 'Lost in the jar? Tap a section below to jump in. Bottom nav always brings you home 💕'
                : 'Tap a section to jump in. The bottom nav is always available.'; ?>
        </div>

        <a href="/home" class="map-card">
            <div class="map-top">
                <?php pbj_help_map_icon('home', '🏠'); ?>
                <div>
                    <h2 class="map-title"><?php echo htmlspecialchars($hubs['home']); ?></h2>
                    <p class="map-body"><?php echo $fun ? 'Your landing pad — quick stats at a glance.' : 'Main dashboard with three message stats.'; ?></p>
                </div>
            </div>
        </a>

        <a href="/FOH" class="map-card">
            <div class="map-top">
                <?php pbj_help_map_icon('foh', '🎭'); ?>
                <div>
                    <h2 class="map-title"><?php echo htmlspecialchars($hubs['foh']); ?></h2>
                    <p class="map-body"><?php echo $fun ? 'Front-of-house ops & guest experience.' : 'Front of house operations.'; ?></p>
                    <ul class="map-bits">
                        <li>• <?php echo $fun ? 'Opening & closing, server sidework, floor plan' : 'Opening & closing, sidework, floor plan'; ?></li>
                        <li>• <?php echo $fun ? 'Reservations & waitlist, bar operations, POS quick ref' : 'Reservations, bar operations, POS quick ref'; ?></li>
                    </ul>
                </div>
            </div>
        </a>

        <a href="/BOH" class="map-card">
            <div class="map-top">
                <?php pbj_help_map_icon('boh', '🔥'); ?>
                <div>
                    <h2 class="map-title"><?php echo htmlspecialchars($hubs['boh']); ?></h2>
                    <p class="map-body"><?php echo $fun ? 'Kitchen ops, prep & line excellence.' : 'Back of house operations.'; ?></p>
                    <ul class="map-bits">
                        <li>• <?php echo $fun ? 'Prep lists, open/close, recipes, menu cards' : 'Prep, open/close, recipes, menu'; ?></li>
                        <li>• <?php echo $fun ? 'Cleaning, temps, yields, costing links, quick tools' : 'Cleaning, temps, yields, tools'; ?></li>
                    </ul>
                </div>
            </div>
        </a>

        <a href="/admin" class="map-card">
            <div class="map-top">
                <?php pbj_help_map_icon('admin', '📋'); ?>
                <div>
                    <h2 class="map-title"><?php echo htmlspecialchars($hubs['admin']); ?></h2>
                    <p class="map-body"><?php echo $fun ? 'Management, money, team handbooks & restaurant ops.' : 'Management and operations.'; ?></p>
                    <ul class="map-bits">
                        <li>• <?php echo $fun ? 'Team hub: roster, invites, handbook & FOH/BOH manuals' : 'Team: roster, invites, handbook, manuals'; ?></li>
                        <li>• <?php echo $fun ? 'Restaurant Operations + Compliance (certs & sign-offs)' : 'Operations + compliance (certs & sign-offs)'; ?></li>
                        <li>• <?php echo $fun ? 'Schedules, sales, labor & inventory' : 'Schedules, sales, labor, and inventory'; ?></li>
                    </ul>
                </div>
            </div>
        </a>

        <a href="/messages" class="map-card">
            <div class="map-top">
                <?php pbj_help_map_icon('messages', '💬'); ?>
                <div>
                    <h2 class="map-title"><?php echo htmlspecialchars($hubs['messages']); ?></h2>
                    <p class="map-body"><?php echo $fun ? 'Team updates, notes & conversations — syncs across devices.' : 'Team communication hub with multi-device sync.'; ?></p>
                    <ul class="map-bits">
                        <li>• <?php echo $fun ? 'Announcements & broadcasts (feed first, 5 posts/page)' : 'Announcements & broadcasts (paginated feed)'; ?></li>
                        <li>• <?php echo $fun ? 'Shift handoffs, DMs from roster, FOH & BOH channels' : 'Handoffs, DMs, FOH & BOH channels'; ?></li>
                    </ul>
                </div>
            </div>
        </a>

        <a href="/settings" class="map-card">
            <div class="map-top">
                <?php pbj_help_map_icon('settings', '⚙️'); ?>
                <div>
                    <h2 class="map-title"><?php echo htmlspecialchars($hubs['settings']); ?></h2>
                    <p class="map-body"><?php echo $fun ? 'Your personal prefs (you are here!).' : 'Your personal preferences (you are here).'; ?></p>
                    <ul class="map-bits">
                        <li>• <?php echo $fun ? 'Theme, profile, pings, shift vibe, account' : 'Theme, profile, notifications, shift prefs, account'; ?></li>
                    </ul>
                </div>
            </div>
        </a>

        <div class="tip">
            <h3><?php echo $fun ? 'Sweet tips' : 'Tips'; ?></h3>
            <p>
                <?php echo $fun
                    ? 'Recipe ingredients on ' . htmlspecialchars($hubs['boh']) . ' feed ' . htmlspecialchars($hubs['admin']) . ' costing & inventory. Fill recipe cards once — reports stay tasty.'
                    : 'Recipe ingredients on BOH feed admin costing and inventory. Add ingredients once to power both.'; ?>
            </p>
            <p>
                <?php echo $fun
                    ? 'Team wages on ' . htmlspecialchars($hubs['admin']) . ' power labor $ estimates. Schedules pull names from the roster.'
                    : 'Team wages power labor estimates. Schedules use the roster. Sales and labor are shaped for a future POS import.'; ?>
            </p>
            <p>
                <?php echo $fun
                    ? 'Most ops data lives in this browser (and syncs via shared state when online). Clear local data only from Account if you want a fresh test kitchen.'
                    : 'Most data is stored in this browser and can sync across devices. Clear local data from Account only when you want a reset.'; ?>
            </p>
        </div>

        <a href="/settings" class="btn"><?php echo pbj_back_to_hub('settings'); ?></a>
    </div>
    <?php include 'bottom-nav.php'; ?>
</body>
</html>
