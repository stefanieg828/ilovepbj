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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? '86 Board' : '86 Board'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.4rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 20px 14px 40px; max-width: 720px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 14px 16px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; text-align: center; font-size: 0.98rem; }
        .link-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .chip { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-size: 0.92rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { <?php if ($is_sweet): ?>background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A;<?php else: ?>background: #FFF8E8; color: #5C4B1A; border-color: #E0D2A0;<?php endif; ?> }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }
        .card { background: white; border-radius: 18px; padding: 16px 16px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 12px; }
        .hint { font-size: 0.92rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?>
        }
        .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        @media (max-width: 480px) { .row-2 { grid-template-columns: 1fr; } }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .btn-row .btn { flex: 1; min-width: 110px; }
        .item {
            border-radius: 14px; padding: 14px 14px; margin-bottom: 10px;
            border-left: 5px solid #C62828;
            <?php if ($is_sweet): ?>background: #FFF8F8;<?php else: ?>background: #FAF8F5;<?php endif; ?>
        }
        .item.is-low { border-left-color: #E6A817; <?php if ($is_sweet): ?>background: #FFFBF0;<?php else: ?>background: #FFF9EC;<?php endif; ?> }
        .item-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .item-name { font-size: 1.2rem; font-weight: 600; line-height: 1.25; margin: 0 0 4px; }
        .badge {
            display: inline-block; font-size: 0.75rem; letter-spacing: 0.04em; text-transform: uppercase;
            padding: 3px 8px; border-radius: 999px; font-weight: 700; white-space: nowrap;
        }
        .badge-86 { background: #FFCDD2; color: #B71C1C; }
        .badge-low { background: #FFE082; color: #6D4C00; }
        .item-meta { font-size: 0.88rem; opacity: 0.7; line-height: 1.35; }
        .item-note { margin-top: 6px; font-size: 0.95rem; opacity: 0.9; line-height: 1.35; }
        .item-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .empty { text-align: center; padding: 28px 16px; opacity: 0.8; line-height: 1.45; }
        .counts { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .count-pill { padding: 6px 12px; border-radius: 999px; font-size: 0.9rem; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .count-pill strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .denied { display: none; }
        .menu-suggest { max-height: 180px; overflow-y: auto; border-radius: 12px; margin-top: 6px; display: none; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; background: white; }
        .menu-suggest button {
            display: block; width: 100%; text-align: left; border: none; background: transparent;
            padding: 10px 12px; cursor: pointer; font-size: 0.95rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
            border-bottom: 1px solid <?php echo $is_sweet ? '#F8EEE8' : '#ECE6DF'; ?>;
        }
        .menu-suggest button:last-child { border-bottom: none; }
        .menu-suggest button:hover { <?php if ($is_sweet): ?>background: #FFF5F6;<?php else: ?>background: #EEF2F8;<?php endif; ?> }
        .picker-meta { font-size: 0.8rem; opacity: 0.6; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH/recipes" class="back-link">← <?php echo $is_sweet ? 'Menu & Recipes' : 'Menu & Recipes'; ?></a>
        <h1><?php echo $is_sweet ? '86 Board' : '86 Board'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'What’s out · what’s running low' : 'Out of stock and running low'; ?></p>
    </div>

    <div class="content">
        <div class="intro" id="intro">
            <?php echo $is_sweet
                ? 'Mark items 86 or low for the line & FOH. Syncs house-wide — pin the kitchen display on home for the pass 💕'
                : 'Mark items 86 or low for the line and FOH. Syncs across devices — pin the kitchen display from home shortcuts.'; ?>
        </div>

        <div class="link-row no-print">
            <a class="chip" href="/BOH/86/display"><?php echo $is_sweet ? '📺 Kitchen display' : '📺 Kitchen display'; ?></a>
            <a class="chip" href="/BOH/menu" data-perm-any="boh.recipes.menu_view,boh.recipes.menu_edit"><?php echo $is_sweet ? '📈 Menu' : '📈 Menu'; ?></a>
            <a class="chip" href="/BOH/opening-closing"><?php echo $is_sweet ? '🌅 Open / Close' : '🌅 Open / Close'; ?></a>
        </div>

        <div class="sync-pill syncing" id="sync-pill" title="<?php echo $is_sweet ? 'Shared with your restaurant group' : 'Shared with your restaurant group'; ?>">
            <span class="dot" aria-hidden="true"></span>
            <span id="sync-pill-text" class="sync-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div class="denied intro" id="denied"><?php echo $is_sweet ? 'No permission to view the 86 board.' : 'No permission to view the 86 board.'; ?></div>

        <div id="board-ui">
            <div class="counts" id="counts"></div>

            <div class="card" id="add-card" data-perm-any="boh.recipes.menu_edit,boh.tools.use">
                <h2><?php echo $is_sweet ? 'Add to board' : 'Add to board'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'Pick from the menu or type a free-text 86.' : 'Pick from the menu or type a free-text item.'; ?></p>
                <div class="field">
                    <label for="item-name"><?php echo $is_sweet ? 'Item' : 'Item'; ?></label>
                    <input type="text" id="item-name" autocomplete="off" placeholder="<?php echo $is_sweet ? 'Search menu or type…' : 'Search menu or type…'; ?>">
                    <div class="menu-suggest" id="menu-suggest" role="listbox"></div>
                </div>
                <div class="row-2">
                    <div class="field">
                        <label for="item-status"><?php echo $is_sweet ? 'Status' : 'Status'; ?></label>
                        <select id="item-status">
                            <option value="86">86</option>
                            <option value="low"><?php echo $is_sweet ? 'Low' : 'Low'; ?></option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="item-note"><?php echo $is_sweet ? 'Note (optional)' : 'Note (optional)'; ?></label>
                        <input type="text" id="item-note" maxlength="120" placeholder="<?php echo $is_sweet ? 'Until tomorrow, 2 left…' : 'Until tomorrow, 2 left…'; ?>">
                    </div>
                </div>
                <div class="btn-row">
                    <button type="button" class="btn btn-primary" id="add-btn"><?php echo $is_sweet ? 'Add to board' : 'Add to board'; ?></button>
                </div>
            </div>

            <div class="card">
                <h2><?php echo $is_sweet ? 'On the board' : 'On the board'; ?></h2>
                <div id="list"></div>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
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
        var selectedMenuId = null;
        var menuItems = B.loadMenu();

        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return true;
        }
        function canView() {
            return canP('boh.recipes.menu_view') || canP('boh.recipes.menu_edit') || canP('boh.tools.use');
        }
        function canEdit() {
            return canP('boh.recipes.menu_edit') || canP('boh.tools.use');
        }

        function toast(msg) {
            var el = document.getElementById('toast');
            if (!el) return;
            el.textContent = msg;
            el.classList.add('show');
            clearTimeout(toast._t);
            toast._t = setTimeout(function () { el.classList.remove('show'); }, 1800);
        }

        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function persist(push) {
            state = B.saveLocal(state);
            if (push !== false && sync) sync.push(state);
            render();
        }

        function applyPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            var view = canView();
            var edit = canEdit();
            document.getElementById('denied').style.display = view ? 'none' : 'block';
            document.getElementById('board-ui').style.display = view ? '' : 'none';
            var addCard = document.getElementById('add-card');
            if (addCard) addCard.style.display = edit ? '' : 'none';
            document.querySelectorAll('.item-actions').forEach(function (row) {
                row.style.display = edit ? '' : 'none';
            });
        }

        function renderCounts() {
            var n86 = 0, nLow = 0;
            (state.board || []).forEach(function (it) {
                if (it.status === 'low') nLow++; else n86++;
            });
            document.getElementById('counts').innerHTML =
                '<span class="count-pill"><strong>' + n86 + '</strong> 86</span>' +
                '<span class="count-pill"><strong>' + nLow + '</strong> ' + (isSweet ? 'low' : 'low') + '</span>' +
                '<span class="count-pill"><strong>' + (n86 + nLow) + '</strong> ' + (isSweet ? 'total' : 'total') + '</span>';
        }

        function render() {
            renderCounts();
            var list = document.getElementById('list');
            var items = (state.board || []).slice().sort(function (a, b) {
                if (a.status !== b.status) return a.status === '86' ? -1 : 1;
                return String(a.name).localeCompare(String(b.name), undefined, { sensitivity: 'base' });
            });
            if (!items.length) {
                list.innerHTML = '<div class="empty">' + (isSweet
                    ? 'Nothing 86’d — board is clear ✨'
                    : 'Nothing on the board — all clear.') + '</div>';
                applyPerms();
                return;
            }
            list.innerHTML = items.map(function (it) {
                var badge = it.status === 'low'
                    ? '<span class="badge badge-low">LOW</span>'
                    : '<span class="badge badge-86">86</span>';
                var when = B.formatWhen(it.at || it.updatedAt);
                var meta = [];
                if (it.by) meta.push(esc(it.by));
                if (when) meta.push(esc(when));
                var note = it.note ? '<div class="item-note">' + esc(it.note) + '</div>' : '';
                var actions = '';
                if (canEdit()) {
                    var toggleLabel = it.status === 'low' ? 'Mark 86' : 'Mark low';
                    actions =
                        '<div class="item-actions">' +
                          '<button type="button" class="btn btn-ghost btn-small" data-act="toggle" data-id="' + esc(it.id) + '">' + toggleLabel + '</button>' +
                          '<button type="button" class="btn btn-danger btn-small" data-act="clear" data-id="' + esc(it.id) + '">' + (isSweet ? 'Back / clear' : 'Back / clear') + '</button>' +
                        '</div>';
                }
                return '<div class="item' + (it.status === 'low' ? ' is-low' : '') + '" data-id="' + esc(it.id) + '">' +
                    '<div class="item-top"><div><p class="item-name">' + esc(it.name) + '</p>' +
                    (meta.length ? '<div class="item-meta">' + meta.join(' · ') + '</div>' : '') +
                    note + '</div>' + badge + '</div>' + actions + '</div>';
            }).join('');
            applyPerms();
        }

        function findItem(id) {
            for (var i = 0; i < state.board.length; i++) {
                if (state.board[i].id === id) return state.board[i];
            }
            return null;
        }

        function addItem() {
            if (!canEdit()) return;
            var nameEl = document.getElementById('item-name');
            var name = String(nameEl.value || '').trim();
            if (!name) {
                toast(isSweet ? 'Name the item first' : 'Enter an item name');
                nameEl.focus();
                return;
            }
            var status = document.getElementById('item-status').value === 'low' ? 'low' : '86';
            var note = String(document.getElementById('item-note').value || '').trim();
            // Upsert by name (case-insensitive) so we don't duplicate
            var existing = null;
            var key = name.toLowerCase();
            state.board.forEach(function (it) {
                if (String(it.name).toLowerCase() === key) existing = it;
            });
            if (existing) {
                existing.status = status;
                existing.note = note;
                existing.by = userName;
                existing.at = Date.now();
                existing.updatedAt = Date.now();
                if (selectedMenuId) existing.menuId = selectedMenuId;
            } else {
                state.board.push({
                    id: B.uid(),
                    name: name,
                    menuId: selectedMenuId,
                    status: status,
                    note: note,
                    by: userName,
                    at: Date.now(),
                    updatedAt: Date.now()
                });
            }
            nameEl.value = '';
            document.getElementById('item-note').value = '';
            selectedMenuId = null;
            hideSuggest();
            persist(true);
            toast(isSweet ? 'On the board 💾' : 'Saved');
        }

        function hideSuggest() {
            var box = document.getElementById('menu-suggest');
            box.style.display = 'none';
            box.innerHTML = '';
        }

        function showSuggest(q) {
            var box = document.getElementById('menu-suggest');
            q = String(q || '').trim().toLowerCase();
            if (!menuItems.length) {
                hideSuggest();
                return;
            }
            var matches = menuItems.filter(function (m) {
                if (!q) return true;
                return m.name.toLowerCase().indexOf(q) !== -1;
            }).slice(0, 12);
            if (!matches.length) {
                hideSuggest();
                return;
            }
            box.innerHTML = matches.map(function (m) {
                return '<button type="button" role="option" data-menu-id="' + esc(m.id) + '" data-menu-name="' + esc(m.name) + '">' +
                    esc(m.name) +
                    (m.category ? ' <span class="picker-meta">· ' + esc(m.category) + '</span>' : '') +
                    '</button>';
            }).join('');
            box.style.display = 'block';
        }

        document.getElementById('add-btn').addEventListener('click', addItem);
        document.getElementById('item-name').addEventListener('input', function () {
            selectedMenuId = null;
            showSuggest(this.value);
        });
        document.getElementById('item-name').addEventListener('focus', function () {
            showSuggest(this.value);
        });
        document.getElementById('item-name').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addItem();
            }
        });
        document.getElementById('menu-suggest').addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-menu-name]');
            if (!btn) return;
            document.getElementById('item-name').value = btn.getAttribute('data-menu-name') || '';
            selectedMenuId = btn.getAttribute('data-menu-id') || null;
            hideSuggest();
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#item-name') && !e.target.closest('#menu-suggest')) hideSuggest();
        });

        document.getElementById('list').addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-act]');
            if (!btn || !canEdit()) return;
            var id = btn.getAttribute('data-id');
            var act = btn.getAttribute('data-act');
            var it = findItem(id);
            if (!it) return;
            if (act === 'clear') {
                state.board = state.board.filter(function (x) { return x.id !== id; });
                persist(true);
                toast(isSweet ? 'Cleared — back on menu' : 'Cleared');
                return;
            }
            if (act === 'toggle') {
                it.status = it.status === 'low' ? '86' : 'low';
                it.by = userName;
                it.at = Date.now();
                it.updatedAt = Date.now();
                persist(true);
                toast(it.status === '86' ? 'Marked 86' : (isSweet ? 'Marked low' : 'Marked low'));
            }
        });

        sync = B.wire({
            statusEl: 'sync-pill',
            pollMs: 5000,
            getState: function () { return state; },
            setState: function (next) {
                state = B.normalize(next);
                B.saveLocal(state);
                render();
            }
        });

        render();
        if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyPerms);
        document.addEventListener('pbj-perms-ready', applyPerms);
    })();
    </script>
</body>
</html>
