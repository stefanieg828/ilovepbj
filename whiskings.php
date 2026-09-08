<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

// Theme switcher (stays on Whiskings)
if (isset($_GET['theme'])) {
    pbj_save_user_theme($pdo, (int)($_SESSION['user_id'] ?? 0), (string)$_GET['theme'], true);
    header("Location: /settings");
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
$display = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'friend';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>

    <title><?php echo pbj_hub_label('settings'); ?> • ilovepbj ops</title>

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
            font-size: 3.4rem;
            margin: 0;
        }

        .subtitle {
            margin: 10px 0 0;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .content {
            padding: 20px 14px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hello {
            background: white;
            border-radius: 18px;
            padding: 18px 22px;
            margin-bottom: 22px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            line-height: 1.45;
        }

        .hello strong {
            <?php if ($is_sweet): ?>
                color: #E55163;
            <?php else: ?>
                color: #1A2A44;
            <?php endif; ?>
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

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 12px;
        }

        .chip {
            display: inline-block;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 0.85rem;
            <?php if ($is_sweet): ?>
                background: #FFF5F6;
                color: #E55163;
                border: 1px solid #F3C5CC;
            <?php else: ?>
                background: #EEF2F8;
                color: #1A2A44;
                border: 1px solid #C5D0DE;
            <?php endif; ?>
        }
</style>
    <?php if (function_exists('pbj_render_hub_card_css')) { pbj_render_hub_card_css(); } ?>
</head>
<body class="hub-page">
    <div class="header">
        <h1><?php echo pbj_hub_label('settings'); ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Your prefs, your vibe, your jar' : 'Personal settings and preferences'; ?></p>
    </div>

    <div class="content">
        <div class="hub-intro"><?php echo $is_sweet
                ? 'Your prefs, theme, profile &amp; account. Personal jar settings 💕'
                : 'Personal preferences, theme, profile, and account.'; ?></div>
        <div class="hello">
            <span id="hello-text">
            <?php echo $is_sweet
                ? 'Hey <strong>' . htmlspecialchars($display) . '</strong> 💕 This is your personal control panel. Theme, profile, pings, and account.'
                : 'Hi <strong>' . htmlspecialchars($display) . '</strong>. Manage your personal preferences here.'; ?>
            </span>
            <div class="chips">
                <span class="chip"><?php
                    $tid = pbj_theme_id();
                    if ($tid === 'neon_diner') echo '🍔 Neon 50s Diner';
                    elseif ($tid === 'farm') echo '🌿 Farm-to-Table';
                    elseif ($tid === 'urban') echo '🏙️ Modern Urban Edge';
                    elseif ($tid === 'coffee') echo '☕ Coffee Shop Cozy';
                    elseif ($tid === 'sweet') echo '🍓 Sweet PBJ Vibes';
                    else echo '✨ Sleek Simple Style';
                ?></span>
                <span class="chip" id="shift-chip"><?php echo $is_sweet ? '🗓️ Shift flag: —' : 'Shift: —'; ?></span>
            </div>
        </div>

        <p class="hub-reorder-hint">Drag cards to rearrange · order saves on this device</p>
        <div class="grid hub-grid" data-hub-key="whiskings">
            <a href="/settings/theme" class="card" data-card-id="theme" data-perm="settings.theme">
                <?php pbj_render_card_icon('whiskings/theme', 'Look & Feel', '🎨'); ?>
                <h3><?php echo $is_sweet ? 'Look & Feel' : 'Theme'; ?></h3>
                <p><?php
                    $tid = pbj_theme_id();
                    if ($tid === 'farm') echo 'Sweet, Neon, Farm-to-Table &amp; more — switch anytime';
                    elseif ($tid === 'neon_diner' || $is_sweet) echo 'Sweet, Sleek, Neon, Farm — switch anytime';
                    else echo 'Switch themes anytime';
                ?></p>
            </a>
            <a href="/settings/profile" class="card" data-card-id="profile" data-perm="settings.profile">
                <?php pbj_render_card_icon('whiskings/profile', 'My Jar Tag', '🙂'); ?>
                <h3><?php echo $is_sweet ? 'My Jar Tag' : 'My Profile'; ?></h3>
                <p><?php echo $is_sweet ? 'Name & details used across the app' : 'Name and details used across the app'; ?></p>
            </a>
            <a href="/settings/notifications" class="card" data-card-id="notifications" data-perm="settings.notifications">
                <?php pbj_render_card_icon('whiskings/notifications', 'Ping Me', '🔔'); ?>
                <h3><?php echo $is_sweet ? 'Ping Me' : 'Notifications'; ?></h3>
                <p><?php echo $is_sweet ? 'What you want to hear about' : 'Choose what you want alerts for'; ?></p>
            </a>
            <a href="/settings/shift" class="card" data-card-id="shift">
                <?php pbj_render_card_icon('whiskings/shift', 'My Shift Vibe', '⏱️'); ?>
                <h3><?php echo $is_sweet ? 'My Shift Vibe' : 'Shift Prefs'; ?></h3>
                <p><?php echo $is_sweet ? 'Stations, availability & today\'s flag' : 'Stations, availability, and today\'s flag'; ?></p>
            </a>
            <a href="/settings/help" class="card" data-card-id="help">
                <?php pbj_render_card_icon('whiskings/help', 'Where\'s What?', '❓'); ?>
                <h3><?php echo $is_sweet ? 'Where\'s What?' : 'App Help'; ?></h3>
                <p><?php echo $is_sweet ? 'A cute map of the whole ops hub' : 'Quick map of where things live'; ?></p>
            </a>
            <a href="/settings/account" class="card" data-card-id="account" data-perm="settings.view_own_perms">
                <?php pbj_render_card_icon('whiskings/account', 'Account', '👤'); ?>
                <h3><?php echo $is_sweet ? 'Account' : 'Account'; ?></h3>
                <p><?php echo $is_sweet ? 'Logout, clear local data & testing notes' : 'Logout, clear local data, and account info'; ?></p>
            </a>
        </div>

        <div class="hello" style="margin-top:18px;text-align:left;" id="dash-prefs">
            <strong style="display:block;margin-bottom:8px;"><?php echo $is_sweet ? 'Home dashboard tiles' : 'Home dashboard tiles'; ?></strong>
            <p style="margin:0 0 10px;font-size:0.9rem;opacity:0.75;line-height:1.4;">
                <?php echo $is_sweet
                    ? 'On home, tap <strong>Arrange home</strong> to drag tiles around or hide ones you don’t want (like work music). You can also toggle visibility here 💕'
                    : 'On home, use <strong>Arrange home</strong> to drag and reorder tiles, or hide widgets. You can also toggle them here.'; ?>
            </p>
            <div id="dash-tile-prefs" style="display:flex;flex-direction:column;gap:10px;"></div>
            <p id="dash-tile-prefs-hint" style="margin:10px 0 0;font-size:0.85rem;opacity:0.7;line-height:1.35;"></p>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
    <script src="/hub-card-order.js"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var fallback = <?php echo json_encode($display); ?>;
        try {
            var profile = JSON.parse(localStorage.getItem('pbj_whiskings_profile_v1') || 'null');
            if (profile && profile.name) {
                var name = profile.name;
                document.getElementById('hello-text').innerHTML = isSweet
                    ? 'Hey <strong>' + name.replace(/</g, '&lt;') + '</strong> 💕 This is your personal control panel. Theme, profile, pings, and account.'
                    : 'Hi <strong>' + name.replace(/</g, '&lt;') + '</strong>. Manage your personal preferences here.';
            }
        } catch (e) {}
        try {
            var shift = JSON.parse(localStorage.getItem('pbj_whiskings_shift_v1') || 'null');
            var today = (shift && shift.today) || 'off';
            var labels = isSweet
                ? { foh: '🍽️ FOH today', boh: '🔥 BOH today', off: '🏠 Off today' }
                : { foh: 'FOH today', boh: 'BOH today', off: 'Off today' };
            document.getElementById('shift-chip').textContent = labels[today] || labels.off;
        } catch (e) {
            document.getElementById('shift-chip').textContent = isSweet ? '🗓️ Shift flag: —' : 'Shift: —';
        }

        // Home dashboard tile visibility (shared with home-dashboard-tiles.js storage)
        var TILES_KEY = 'pbj_home_tiles_v1';
        var CREATOR_NEWS_HIDE_KEY = 'pbj_creator_news_hidden_v1';
        var API_URL = '/user-prefs-api.php';
        var TILE_DEFS = [
            { id: 'shortcuts', label: isSweet ? 'Quick pins / shortcuts' : 'Shortcuts / quick pins' },
            { id: 'work-music', label: isSweet ? 'Work music player' : 'Work music' },
            { id: 'creator-news', label: isSweet ? 'Creator’s news' : "Creator's news" },
            { id: 'stat-posts', label: isSweet ? 'Open posts snapshot' : 'Open posts' },
            { id: 'stat-handoffs', label: isSweet ? 'Handoffs today' : 'Handoffs today' },
            { id: 'stat-chats', label: isSweet ? 'DM threads' : 'DM threads' },
            { id: 'theme', label: isSweet ? 'Theme switcher' : 'Theme switcher' },
            { id: 'star', label: isSweet ? 'Star of the house' : 'Star of the house' },
            { id: 'list-complete', label: isSweet ? 'Lists finished today' : 'Checklists completed' },
            { id: 'a2hs', label: isSweet ? 'Add to homepage button' : 'Add to homepage' }
        ];
        var DEFAULT_ORDER = [
            'star', 'a2hs', 'shortcuts', 'list-complete',
            'stat-posts', 'stat-handoffs', 'stat-chats',
            'creator-news', 'work-music', 'platform-desk', 'theme'
        ];

        function loadTiles() {
            var order = DEFAULT_ORDER.slice();
            var hidden = [];
            var updatedAt = 0;
            try {
                var parsed = JSON.parse(localStorage.getItem(TILES_KEY) || 'null');
                if (parsed && typeof parsed === 'object') {
                    if (Array.isArray(parsed.order)) order = parsed.order;
                    if (Array.isArray(parsed.hidden)) hidden = parsed.hidden.slice();
                    updatedAt = parseInt(parsed.updatedAt, 10) || 0;
                }
            } catch (e) {}
            try {
                if (localStorage.getItem(CREATOR_NEWS_HIDE_KEY) === '1' && hidden.indexOf('creator-news') === -1) {
                    hidden.push('creator-news');
                }
            } catch (e2) {}
            return { order: order, hidden: hidden, updatedAt: updatedAt };
        }

        function saveTiles(state) {
            state.updatedAt = Date.now ? Date.now() : new Date().getTime();
            try {
                localStorage.setItem(TILES_KEY, JSON.stringify(state));
                localStorage.setItem(
                    CREATOR_NEWS_HIDE_KEY,
                    state.hidden.indexOf('creator-news') !== -1 ? '1' : '0'
                );
            } catch (e) {}
            // Best-effort sync
            try {
                fetch(API_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ home_tiles: state })
                }).catch(function () {});
            } catch (e2) {}
            return state;
        }

        var box = document.getElementById('dash-tile-prefs');
        var hint = document.getElementById('dash-tile-prefs-hint');
        var state = loadTiles();

        function paintHint() {
            if (!hint) return;
            var n = state.hidden.length;
            if (!n) {
                hint.textContent = isSweet
                    ? 'All listed tiles are visible on home. Drag-reorder anytime with Arrange home.'
                    : 'All listed tiles are visible. Use Arrange home on the dashboard to reorder.';
            } else {
                hint.textContent = isSweet
                    ? n + ' tile' + (n === 1 ? '' : 's') + ' hidden on home — uncheck to bring back 💕'
                    : n + ' tile' + (n === 1 ? '' : 's') + ' hidden. Uncheck “show” is off — check to restore.';
            }
        }

        function isShown(id) {
            return state.hidden.indexOf(id) === -1;
        }

        function setShown(id, shown) {
            if (shown) {
                state.hidden = state.hidden.filter(function (h) { return h !== id; });
            } else if (state.hidden.indexOf(id) === -1) {
                state.hidden.push(id);
            }
            saveTiles(state);
            paintHint();
        }

        if (box) {
            box.innerHTML = '';
            TILE_DEFS.forEach(function (def) {
                var lab = document.createElement('label');
                lab.style.cssText = 'display:flex;align-items:flex-start;gap:10px;cursor:pointer;line-height:1.35;font-size:0.95rem;';
                var cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.style.cssText = 'width:18px;height:18px;margin-top:2px;flex-shrink:0;';
                cb.checked = isShown(def.id);
                cb.setAttribute('data-tile-id', def.id);
                cb.addEventListener('change', function () {
                    setShown(def.id, cb.checked);
                });
                var span = document.createElement('span');
                span.innerHTML = (isSweet ? 'Show ' : 'Show ') + '<strong>' + def.label.replace(/</g, '&lt;') + '</strong>';
                lab.appendChild(cb);
                lab.appendChild(span);
                box.appendChild(lab);
            });
            paintHint();
        }

        // Pull server prefs if newer
        fetch(API_URL, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok || !data.prefs || !data.prefs.home_tiles) return;
                var remote = data.prefs.home_tiles;
                var remoteAt = parseInt(remote.updatedAt, 10) || 0;
                if (remoteAt > (state.updatedAt || 0)) {
                    state.order = Array.isArray(remote.order) ? remote.order : state.order;
                    state.hidden = Array.isArray(remote.hidden) ? remote.hidden.slice() : [];
                    state.updatedAt = remoteAt;
                    try {
                        localStorage.setItem(TILES_KEY, JSON.stringify(state));
                        localStorage.setItem(
                            CREATOR_NEWS_HIDE_KEY,
                            state.hidden.indexOf('creator-news') !== -1 ? '1' : '0'
                        );
                    } catch (e) {}
                    if (box) {
                        Array.prototype.forEach.call(box.querySelectorAll('input[data-tile-id]'), function (cb) {
                            cb.checked = state.hidden.indexOf(cb.getAttribute('data-tile-id')) === -1;
                        });
                    }
                    paintHint();
                }
            })
            .catch(function () {});
    })();
    </script>
</body>
</html>
