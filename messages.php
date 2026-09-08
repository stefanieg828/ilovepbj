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

    <title><?php echo pbj_hub_label('messages'); ?> • ilovepbj ops</title>
    <?php if (!empty($__tf['basic'])): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php elseif (!empty($is_farm)): ?>
    <?php if (function_exists('pbj_render_farm_font_faces')) { pbj_render_farm_font_faces(); } ?>
    <?php if (function_exists('pbj_render_urban_font_faces')) { pbj_render_urban_font_faces(); } ?>
    <?php if (function_exists('pbj_render_coffee_font_faces')) { pbj_render_coffee_font_faces(); } ?>
    <?php endif; ?>
    <?php if (function_exists('pbj_render_neon_font_faces')) { pbj_render_neon_font_faces(); } ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 90px; <?php if ($is_classic_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php elseif ($is_neon): ?>font-family: 'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif; background: #0A0A0A; color: #F5F5F7;<?php elseif (!empty($is_farm)): ?>font-family: 'Notepen', 'NotepenRegular', Georgia, serif; background: #F3E6D4; color: #2C2416;<?php elseif (!empty($is_urban)): ?>font-family: 'Kelly Slab', 'KellySlab-Regular', Georgia, serif; background: #000000; color: #F5F5F5;<?php elseif (!empty($is_coffee)): ?>font-family: 'Ambery Garden', 'AmberyGarden-Regular', Georgia, serif; background: #F5EDE3; color: #3D2416;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_classic_sweet): ?>background: #E55163; color: white;<?php elseif ($is_neon): ?>background: #0D0D0D; color: #FF2ECB; border-bottom: 2px solid #00F0FF; box-shadow: 0 0 18px rgba(255,46,203,0.35);<?php elseif ($is_farm): ?>background: #2F6B3A; color: #FFF8EE; border-bottom: 3px solid #E07A2F; box-shadow: 0 6px 18px rgba(47,107,58,0.22);<?php elseif (!empty($is_urban)): ?>background: #0A0A0A; color: #FFFFFF; border-bottom: 2px solid #FF2D00; box-shadow: 0 0 18px rgba(255,45,0,0.28);<?php elseif (!empty($is_coffee)): ?>background: #4A2C1A; color: #F5EDE3; border-bottom: 3px solid #C4A484; box-shadow: 0 6px 18px rgba(74,44,26,0.2);<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> padding: 25px; text-align: center; }
        h1 { <?php if ($is_classic_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; font-weight: 700; color: #FF2ECB; text-shadow: 0 0 16px rgba(255,46,203,0.5);<?php elseif ($is_farm): ?>font-family: 'Stylish Handwriting Free', 'Stylish Handwriting', cursive; font-weight: 700; color: #FFF8EE;<?php elseif (!empty($is_urban)): ?>font-family: 'Rafika', 'Rafika-Regular', Impact, sans-serif; color: #FF2D00; letter-spacing: 0.06em; text-transform: uppercase;<?php elseif (!empty($is_coffee)): ?>font-family: 'Coffee Town', 'CoffeeTown', Georgia, serif; font-weight: 700; color: #F5EDE3;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 3.8rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.2rem; opacity: 0.9; <?php if ($is_neon): ?>color: #00F0FF;<?php elseif ($is_farm): ?>color: #F3E6D4;<?php endif; ?> }
        .content { padding: 24px 14px 40px; max-width: 900px; margin: 0 auto; }
        .intro { <?php if ($is_neon): ?>background: #141414; border: 1px solid #FF2ECB; color: #F5F5F7; box-shadow: 0 0 18px rgba(255,46,203,0.12);<?php elseif ($is_farm): ?>background: #FFFBF5; border: 1px solid #D9C7A8; color: #2C2416; box-shadow: 0 6px 18px rgba(44,36,22,0.10);<?php else: ?>background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.08);<?php endif; ?> border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; line-height: 1.45; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        @media (max-width: 520px) { .stats-row { grid-template-columns: 1fr; } }
        .stat { <?php if ($is_neon): ?>background: #141414; border: 1px solid #FF2ECB; color: #F5F5F7; box-shadow: 0 0 12px rgba(255,46,203,0.12);<?php else: ?>background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.08);<?php endif; ?> border-radius: 16px; padding: 14px 10px; text-align: center; }
        .stat .num { font-size: 1.4rem; <?php if ($is_classic_sweet): ?>color: #E55163;<?php elseif ($is_neon): ?>color: #FF2ECB; font-family: 'Warnes', system-ui, sans-serif;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.85rem; opacity: 0.7; margin-top: 4px; }
        .section-label { font-size: 0.95rem; opacity: 0.65; margin: 16px 4px 10px; <?php if ($is_neon): ?>color: #00F0FF; opacity: 0.9; font-family: 'Warnes', system-ui, sans-serif;<?php endif; ?> }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 520px) { .grid { grid-template-columns: 1fr; } }
        .card { <?php if ($is_neon): ?>background: #141414; border: 1px solid #FF2ECB; color: #F5F5F7; box-shadow: 0 0 0 1px rgba(255,46,203,0.15), 0 8px 24px rgba(0,0,0,0.5);<?php else: ?>background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.1);<?php endif; ?> border-radius: 16px; padding: 16px 12px; text-align: center; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: block; position: relative; }
        .card:hover { transform: translateY(-8px); <?php if ($is_neon): ?>box-shadow: 0 0 24px rgba(0,240,255,0.3); border-color: #00F0FF;<?php else: ?>box-shadow: 0 15px 30px rgba(0,0,0,0.15);<?php endif; ?> }
        .card h3 { <?php if ($is_classic_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php elseif ($is_neon): ?>font-family: 'Warnes', system-ui, sans-serif; color: #FF2ECB;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 1.15rem; margin: 8px 0 4px; }
        .card p { margin: 0; font-size: 0.82rem; opacity: 0.75; line-height: 1.35; }
        .card-icon { width: 52px; height: 52px; border-radius: 12px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; overflow: hidden; <?php if ($is_classic_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php elseif ($is_neon): ?>background: #0F0F0F; border: 2px solid #00F0FF; box-shadow: 0 0 12px rgba(0,240,255,0.35);<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
        .card-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
        <?php if ($is_sweet): ?>
        .card-icon.sweet-sticker {  border-radius: 16px; background: <?php echo $is_neon ? '#0F0F0F' : '#FFFBFA'; ?>; padding: 4px; box-sizing: border-box; }
        .card-icon.sweet-sticker img { object-fit: contain; border-radius: 12px; }
        <?php endif; ?>
        .count-pill { display: inline-block; margin-top: 8px; font-size: 0.75rem; border-radius: 999px; padding: 3px 10px; <?php if ($is_classic_sweet): ?>background: #FFF5F6; color: #E55163;<?php elseif ($is_neon): ?>background: #1A1A1A; color: #00F0FF; border: 1px solid #00F0FF;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .count-pill.hot { <?php if ($is_neon): ?>background: #3A1018; color: #FF6B9D;<?php else: ?>background: #FDECEA; color: #B71C1C;<?php endif; ?> }
    </style>
    <?php if (function_exists('pbj_render_hub_card_css')) { pbj_render_hub_card_css(); } ?>
</head>
<body class="hub-page">
    <div class="header">
        <h1><?php echo pbj_hub_label('messages'); ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Team updates, notes & conversations' : 'Team communication hub'; ?></p>
    </div>

    <div class="content">
        <div class="intro hub-intro">
            <?php echo $is_sweet
                ? 'The house chat room 🫙<br>Announcements for everyone, shift handoffs, manager alerts, FOH/BOH channels, and DMs with your roster. Posts sync across devices when you\'re online 💕'
                : 'House communication:<br>Announcements, shift handoffs, manager broadcasts, FOH/BOH channels, and DMs. Syncs across devices when online.'; ?>
        </div>

        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-posts">0</div><div class="lbl"><?php echo $is_sweet ? 'Open posts' : 'Open posts'; ?></div></div>
            <div class="stat"><div class="num" id="stat-handoffs">0</div><div class="lbl"><?php echo $is_sweet ? 'Handoffs today' : 'Handoffs today'; ?></div></div>
            <div class="stat"><div class="num" id="stat-chats">0</div><div class="lbl"><?php echo $is_sweet ? 'DM threads' : 'DM threads'; ?></div></div>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'House-wide' : 'House-wide'; ?></div>
        <p class="hub-reorder-hint">Drag cards to rearrange · order saves on this device</p>
        <div class="grid hub-grid" data-hub-key="messages">
            <a data-perm-any="messages.announcements.view,messages.announcements.post" href="/messages/announcements" class="card" data-card-id="announcements" data-perm-any="messages.announcements.view,messages.announcements.post">
                <?php pbj_render_card_icon('jelly/announcements', 'Team Announcements', '📢'); ?>
                <h3>Team Announcements</h3>
                <p>Restaurant-wide updates from management</p>
                <span class="count-pill" data-count="announcements">—</span>
            </a>
            <a data-perm-any="messages.broadcasts.view,messages.broadcasts.post" href="/messages/broadcasts" class="card" data-card-id="broadcasts" data-perm-any="messages.broadcasts.view,messages.broadcasts.post">
                <?php pbj_render_card_icon('jelly/broadcasts', 'Manager Broadcasts', '📣'); ?>
                <h3>Manager Broadcasts</h3>
                <p>Urgent alerts, schedule changes, and reminders</p>
                <span class="count-pill" data-count="broadcasts">—</span>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Shift-to-shift' : 'Shift-to-shift'; ?></div>
        <div class="grid">
            <a data-perm-any="messages.shift_notes.view,messages.shift_notes.post" href="/messages/shift-notes" class="card" data-card-id="shift-notes" data-perm-any="messages.shift_notes.view,messages.shift_notes.post">
                <?php pbj_render_card_icon('jelly/shift-notes', 'Shift Notes & Handoffs', '📝'); ?>
                <h3>Shift Notes & Handoffs</h3>
                <p>Pass along what the next shift needs to know</p>
                <span class="count-pill" data-count="handoffs">—</span>
            </a>
            <a data-perm-any="messages.dms.view,messages.dms.send" href="/messages/dms" class="card" data-card-id="dms" data-perm-any="messages.dms.view,messages.dms.send">
                <?php pbj_render_card_icon('jelly/dms', 'Direct Messages', '💬'); ?>
                <h3>Direct Messages</h3>
                <p>One-on-one chats with teammates</p>
                <span class="count-pill" data-count="dms">—</span>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Floor & kitchen' : 'Floor & kitchen'; ?></div>
        <div class="grid">
            <a data-perm-any="messages.foh_updates.view,messages.foh_updates.post" href="/messages/foh" class="card" data-card-id="foh" data-perm-any="messages.foh_updates.view,messages.foh_updates.post">
                <?php pbj_render_card_icon('jelly/foh', 'FOH Updates', '🛎️'); ?>
                <h3>FOH Updates</h3>
                <p>Front-of-house channel for service notes</p>
                <span class="count-pill" data-count="foh">—</span>
            </a>
            <a data-perm-any="messages.boh_updates.view,messages.boh_updates.post" href="/messages/boh" class="card" data-card-id="boh" data-perm-any="messages.boh_updates.view,messages.boh_updates.post">
                <?php pbj_render_card_icon('jelly/boh', 'BOH Updates', '👨‍🍳'); ?>
                <h3>BOH Updates</h3>
                <p>Back-of-house channel for kitchen notes</p>
                <span class="count-pill" data-count="boh">—</span>
            </a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
    <script src="hub-card-order.js"></script>
    <script>
    (function () {
        function load(key) {
            try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; }
        }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
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
        var urgent = brPosts.filter(function (p) { return p.type === 'urgent'; }).length;
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

        function setPill(key, n, hot) {
            var el = document.querySelector('[data-count="' + key + '"]');
            if (!el) return;
            el.textContent = n ? (n + (n === 1 ? ' item' : ' items')) : 'Empty';
            if (hot && n) el.classList.add('hot');
        }
        setPill('announcements', ann);
        setPill('broadcasts', brPosts.length, urgent > 0);
        if (urgent > 0) {
            var b = document.querySelector('[data-count="broadcasts"]');
            if (b) b.textContent = urgent + ' urgent · ' + brPosts.length + ' total';
        }
        setPill('handoffs', handoffsToday);
        setPill('dms', threads);
        setPill('foh', foh);
        setPill('boh', boh);
    })();
    </script>
</body>
</html>
