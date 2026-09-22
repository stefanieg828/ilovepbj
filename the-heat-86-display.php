<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_label = trim((string) ($_SESSION['name'] ?? $_SESSION['username'] ?? 'Team'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? '86 Kitchen Display' : '86 Kitchen Display'; ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        :root {
            --bg: <?php echo $is_sweet ? '#FCF8EE' : '#F1EBE4'; ?>;
            --ink: <?php echo $is_sweet ? '#3a2f1f' : '#1A2A44'; ?>;
            --accent: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
            --card: #ffffff;
            --86: #C62828;
            --low: #B8860B;
            --muted: rgba(0,0,0,0.55);
        }
        body.display-dark {
            --bg: #0E1116;
            --ink: #F5F5F7;
            --accent: <?php echo $is_sweet ? '#FF6B7A' : '#7EB6FF'; ?>;
            --card: #1A1F27;
            --86: #FF5C5C;
            --low: #FFD166;
            --muted: rgba(255,255,255,0.55);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', Georgia, serif;<?php else: ?>font-family: 'Lora', Georgia, serif;<?php endif; ?>
            padding-bottom: 24px;
        }
        body.slim-chrome { padding-bottom: 16px; }
        .topbar {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
            padding: 14px 18px;
            background: var(--accent);
            color: #fff;
        }
        body.display-dark .topbar { background: #12161C; border-bottom: 2px solid var(--accent); }
        .topbar h1 {
            margin: 0; font-size: clamp(1.6rem, 4vw, 2.4rem); line-height: 1.1;
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?>
        }
        .topbar-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .topbar a, .topbar button {
            border: 1px solid rgba(255,255,255,0.45);
            background: rgba(255,255,255,0.12);
            color: #fff; text-decoration: none; border-radius: 999px;
            padding: 8px 12px; font-size: 0.9rem; cursor: pointer;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?>
        }
        .topbar a:hover, .topbar button:hover { background: rgba(255,255,255,0.22); }
        .sync-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 999px; font-size: 0.85rem;
            background: rgba(255,255,255,0.15); color: #fff;
        }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #7CFFB2; }
        .sync-pill.offline .dot { background: #FFD166; }
        .sync-pill.syncing .dot { background: #7EB6FF; animation: pulse 1s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }
        .content { padding: 18px 16px 32px; max-width: 1100px; margin: 0 auto; }
        .summary {
            display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;
        }
        .summary span {
            background: var(--card); color: var(--ink); border-radius: 999px;
            padding: 8px 14px; font-size: 1rem; box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }
        .summary strong { color: var(--accent); }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
        }
        .tile {
            background: var(--card);
            border-radius: 18px;
            padding: 18px 18px 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-top: 6px solid var(--86);
            min-height: 120px;
            display: flex; flex-direction: column; gap: 8px;
            cursor: default;
        }
        .tile.is-low { border-top-color: var(--low); }
        .tile.can-tap { cursor: pointer; }
        .tile.can-tap:active { transform: scale(0.99); }
        .tile-badge {
            align-self: flex-start;
            font-size: 0.85rem; font-weight: 700; letter-spacing: 0.06em;
            text-transform: uppercase; padding: 4px 10px; border-radius: 999px;
        }
        .tile-badge.b86 { background: rgba(198,40,40,0.18); color: var(--86); }
        .tile-badge.blow { background: rgba(184,134,11,0.2); color: var(--low); }
        .tile-name {
            font-size: clamp(1.55rem, 3.2vw, 2.15rem);
            line-height: 1.15; margin: 0; font-weight: 700;
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php endif; ?>
        }
        .tile-note { font-size: 1.05rem; opacity: 0.85; line-height: 1.35; margin: 0; }
        .tile-meta { font-size: 0.9rem; color: var(--muted); margin-top: auto; }
        .empty {
            text-align: center; padding: 60px 20px;
            background: var(--card); border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .empty h2 {
            margin: 0 0 8px; font-size: clamp(1.8rem, 4vw, 2.6rem);
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: var(--accent);<?php else: ?>color: var(--accent);<?php endif; ?>
        }
        .empty p { margin: 0; font-size: 1.15rem; opacity: 0.8; }
        .clock { font-size: 0.95rem; opacity: 0.9; }
        /* Hide bottom nav on display — pin/tablet use */
        body.slim-chrome .bottom-nav,
        body.slim-chrome #bottom-nav,
        body.slim-chrome nav.bottom-nav { display: none !important; }
        .footer-hint {
            text-align: center; margin-top: 18px; font-size: 0.9rem; color: var(--muted);
        }
        .footer-hint a { color: var(--accent); }
    </style>
</head>
<body class="slim-chrome">
    <div class="topbar">
        <div>
            <h1><?php echo $is_sweet ? '86 Board' : '86 Board'; ?></h1>
            <div class="clock" id="clock"></div>
        </div>
        <div class="topbar-actions">
            <div class="sync-pill syncing" id="sync-pill">
                <span class="dot" aria-hidden="true"></span>
                <span id="sync-pill-text" class="sync-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
            </div>
            <button type="button" id="toggle-dark"><?php echo $is_sweet ? 'Dark / line' : 'Dark mode'; ?></button>
            <a href="/BOH/86"><?php echo $is_sweet ? 'Edit board' : 'Edit board'; ?></a>
            <a href="/home"><?php echo $is_sweet ? 'Home' : 'Home'; ?></a>
        </div>
    </div>

    <div class="content">
        <div class="summary" id="summary"></div>
        <div id="board"></div>
        <p class="footer-hint" id="tap-hint" hidden>
            <?php echo $is_sweet ? 'Tap a card to clear it when you’re back in stock · manage details on the staff board' : 'Tap a card to clear when back in stock. Full edit on the staff board.'; ?>
        </p>
    </div>

    <script src="/shared-state.js?v=3"></script>
    <script src="/86-board-shared.js?v=1"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var userName = <?php echo json_encode($user_label); ?>;
        var B = window.Pbj86Board;
        if (!B) return;

        var state = B.loadLocal();
        var sync = null;
        var DARK_KEY = 'pbj_86_display_dark_v1';

        function canEdit() {
            if (window.PbjPerms && window.PbjPerms.loaded) {
                return window.PbjPerms.can('boh.recipes.menu_edit') || window.PbjPerms.can('boh.tools.use');
            }
            return true;
        }

        function applyDark() {
            var on = false;
            try { on = localStorage.getItem(DARK_KEY) === '1'; } catch (e) {}
            document.body.classList.toggle('display-dark', on);
        }
        document.getElementById('toggle-dark').addEventListener('click', function () {
            var on = !document.body.classList.contains('display-dark');
            document.body.classList.toggle('display-dark', on);
            try { localStorage.setItem(DARK_KEY, on ? '1' : '0'); } catch (e) {}
        });
        applyDark();

        function tickClock() {
            var el = document.getElementById('clock');
            if (!el) return;
            var d = new Date();
            el.textContent = d.toLocaleString(undefined, {
                weekday: 'short', month: 'short', day: 'numeric',
                hour: 'numeric', minute: '2-digit'
            });
        }
        tickClock();
        setInterval(tickClock, 30000);

        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function persist(push) {
            state = B.saveLocal(state);
            if (push !== false && sync) sync.push(state);
            render();
        }

        function render() {
            var items = (state.board || []).slice().sort(function (a, b) {
                if (a.status !== b.status) return a.status === '86' ? -1 : 1;
                return String(a.name).localeCompare(String(b.name), undefined, { sensitivity: 'base' });
            });
            var n86 = 0, nLow = 0;
            items.forEach(function (it) { if (it.status === 'low') nLow++; else n86++; });
            document.getElementById('summary').innerHTML =
                '<span><strong>' + n86 + '</strong> 86</span>' +
                '<span><strong>' + nLow + '</strong> low</span>' +
                '<span><strong>' + items.length + '</strong> total</span>';

            var board = document.getElementById('board');
            var hint = document.getElementById('tap-hint');
            if (!items.length) {
                board.innerHTML = '<div class="empty"><h2>' + (isSweet ? 'All clear ✨' : 'All clear') + '</h2>' +
                    '<p>' + (isSweet ? 'Nothing 86’d or low right now.' : 'No items on the 86 board.') + '</p></div>';
                if (hint) hint.hidden = true;
                return;
            }
            var editable = canEdit();
            if (hint) hint.hidden = !editable;
            board.innerHTML = '<div class="grid">' + items.map(function (it) {
                var isLow = it.status === 'low';
                var badge = isLow
                    ? '<span class="tile-badge blow">LOW</span>'
                    : '<span class="tile-badge b86">86</span>';
                var note = it.note ? '<p class="tile-note">' + esc(it.note) + '</p>' : '';
                var when = B.formatWhen(it.at || it.updatedAt);
                var metaBits = [];
                if (it.by) metaBits.push(esc(it.by));
                if (when) metaBits.push(esc(when));
                var meta = metaBits.length ? '<div class="tile-meta">' + metaBits.join(' · ') + '</div>' : '';
                return '<article class="tile' + (isLow ? ' is-low' : '') + (editable ? ' can-tap' : '') + '" data-id="' + esc(it.id) + '"' +
                    (editable ? ' title="' + (isSweet ? 'Tap to clear' : 'Tap to clear') + '"' : '') + '>' +
                    badge +
                    '<h2 class="tile-name">' + esc(it.name) + '</h2>' +
                    note + meta +
                    '</article>';
            }).join('') + '</div>';
        }

        document.getElementById('board').addEventListener('click', function (e) {
            if (!canEdit()) return;
            var tile = e.target.closest('.tile[data-id]');
            if (!tile) return;
            var id = tile.getAttribute('data-id');
            var it = null;
            (state.board || []).forEach(function (x) { if (x.id === id) it = x; });
            if (!it) return;
            // Tap cycles: 86 -> low -> clear (or low -> 86 -> clear). Long-press not needed.
            if (it.status === '86') {
                it.status = 'low';
                it.by = userName;
                it.at = Date.now();
                it.updatedAt = Date.now();
            } else {
                state.board = state.board.filter(function (x) { return x.id !== id; });
            }
            persist(true);
        });

        sync = B.wire({
            statusEl: 'sync-pill',
            pollMs: 4000,
            getState: function () { return state; },
            setState: function (next) {
                state = B.normalize(next);
                B.saveLocal(state);
                render();
            }
        });

        render();
        if (window.PbjPerms && window.PbjPerms.ready) {
            window.PbjPerms.ready.then(function () { render(); });
        }
        document.addEventListener('pbj-perms-ready', function () { render(); });
    })();
    </script>
</body>
</html>
