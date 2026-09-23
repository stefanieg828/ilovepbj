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

    <title><?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>

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
    </style>
    <?php if (function_exists('pbj_render_hub_card_css')) { pbj_render_hub_card_css(); } ?>
</head>
<body class="hub-page">
    <div class="header">
        <h1><?php echo pbj_hub_label('admin'); ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Management, team & restaurant ops' : 'Management & Operations'; ?></p>
    </div>

    <div class="content">
        <div class="hub-intro"><?php echo $is_sweet
                ? 'Sandwich HQ — team, ops, schedules, reports, inventory & compliance in one place 💕'
                : 'Team, operations, schedules, reports, inventory, and compliance.'; ?></div>
        <p class="hub-reorder-hint">Drag cards to rearrange · order saves on this device</p>
        <div class="grid hub-grid" data-hub-key="admin">
            <a href="/admin/team" class="card" data-card-id="team" data-perm-any="admin.team.roster.add,admin.team.permissions_manage,admin.team.docs.edit_guide,admin.team.onboarding.create_invite">
                <?php pbj_render_card_icon('sandwich-hq/team', 'Team & Roles', '👥'); ?>
                <h3>Team & Roles</h3>
                <p><?php echo $is_sweet ? 'Roster, handbook & FOH/BOH training manuals' : 'Roster, handbook, and training manuals'; ?></p>
            </a>
            <a href="/admin/ops" class="card" data-card-id="ops" data-perm-any="admin.ops.settings.location,admin.ops.docs.edit_guide,admin.ops.docs.sections">
                <?php pbj_render_card_icon('sandwich-hq/operations', 'Restaurant Operations', '🏢'); ?>
                <h3>Restaurant Operations</h3>
                <p><?php echo $is_sweet ? 'Settings, SOPs & health & safety' : 'Settings, SOPs, and health & safety'; ?></p>
            </a>
            <a href="/admin/schedules" class="card" data-card-id="schedules" data-perm-any="admin.schedules.add_shift,admin.schedules.clear_week">
                <?php pbj_render_card_icon('sandwich-hq/schedules', 'Schedules & Shifts', '🗓️'); ?>
                <h3>Schedules & Shifts</h3>
                <p><?php echo $is_sweet ? 'Schedules, alerts & star of the house' : 'Schedules, alerts, and employee spotlight'; ?></p>
            </a>
            <a href="/admin/reports" class="card" data-card-id="reports" data-perm="admin.reports.view">
                <?php pbj_render_card_icon('sandwich-hq/reports', 'Reports & Sales', '📊'); ?>
                <h3>Reports & Sales</h3>
                <p>Daily numbers, labor costs, and trend snapshots</p>
            </a>
            <a href="/admin/inventory" class="card" data-card-id="inventory" data-perm="admin.inventory.view">
                <?php pbj_render_card_icon('sandwich-hq/inventory', 'Inventory & Vendors', '📦'); ?>
                <h3>Inventory & Vendors</h3>
                <p>Products, counts, auto-order, and vendor contacts</p>
            </a>
            <a href="/admin/compliance" class="card" data-card-id="compliance" data-perm="admin.compliance.view">
                <?php pbj_render_card_icon('sandwich-hq/compliance', 'Compliance & Certifications', '✅'); ?>
                <h3>Compliance & Certifications</h3>
                <p><?php echo $is_sweet ? 'Certs, expirations & handbook sign-offs' : 'Certs, expirations, and document sign-offs'; ?></p>
            </a>
            <a href="/admin/checklist-overview" class="card" data-card-id="checklist-overview" data-perm-any="ops.receive_list_completion,admin.ops.docs.sections">
                <?php pbj_render_card_icon('ops-hub/checklist', 'Checklist Overview', '📋'); ?>
                <h3><?php echo $is_sweet ? 'Checklist Overview' : 'Checklist Overview'; ?></h3>
                <p><?php echo $is_sweet ? 'Who finished what — FOH, BOH & prep' : 'FOH/BOH completion board for managers'; ?></p>
            </a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
    <script src="hub-card-order.js"></script>
</body>
</html>
