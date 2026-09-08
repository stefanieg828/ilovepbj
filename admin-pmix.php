<?php
/**
 * Optional PMIX (product mix) + ideal food cost — qty sold × plate cost.
 */
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'PMIX & ideal food cost' : 'PMIX & ideal food cost'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.2rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 780px; margin: 0 auto; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        @media (max-width: 520px) { .stats-row { grid-template-columns: 1fr 1fr; } }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.15rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.78rem; opacity: 0.7; margin-top: 4px; line-height: 1.25; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0 0 10px; }
        .hint { font-size: 0.92rem; opacity: 0.75; line-height: 1.45; margin: 0 0 12px; }
        .hint a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .opt { font-size: 0.85rem; opacity: 0.7; margin: -4px 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8;
            <?php else: ?>font-family: 'Lora', serif; background: #FAF8F5;<?php endif; ?>
        }
        .field textarea { min-height: 110px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 0.88rem; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> color: inherit; }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.88rem; border-radius: 10px; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
        .line { display: grid; grid-template-columns: 1.4fr 0.55fr 0.7fr auto; gap: 8px; align-items: end; margin-bottom: 8px; }
        @media (max-width: 560px) { .line { grid-template-columns: 1fr 1fr; } }
        .line .field { margin-bottom: 0; }
        .entry { padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
        .entry-meta { font-size: 0.9rem; opacity: 0.75; margin-top: 4px; line-height: 1.4; }
        .empty { text-align: center; padding: 20px; opacity: 0.8; }
        .actions-bar { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-ok { background: #C8E6C9; color: #1B5E20; }
        .badge-miss { background: #FFECB3; color: #F57F17; }
        .detail-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        .detail-table th, .detail-table td { padding: 8px 4px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; text-align: left; vertical-align: top; }
        .detail-table th { opacity: 0.6; font-weight: normal; font-size: 0.78rem; }
        .detail-table td.num, .detail-table th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .session-chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.88rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin: 0 6px 6px 0; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .session-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .ideal-banner {
            border-radius: 14px; padding: 14px 16px; margin-bottom: 12px;
            background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>;
            border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            line-height: 1.45;
        }
        .ideal-banner strong { font-size: 1.05rem; }
        .file-label { display: block; padding: 12px; border-radius: 12px; border: 2px dashed <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; text-align: center; cursor: pointer; margin-bottom: 10px; }
        .file-label input { display: none; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports' : 'Back to Reports'; ?></a>
        <h1><?php echo $is_sweet ? 'PMIX & ideal FC' : 'PMIX & ideal FC'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Optional · product mix → theoretical food cost' : 'Optional product mix → ideal food cost'; ?></p>
    </div>
    <div class="content">
        <p class="opt"><?php echo $is_sweet
            ? '✨ Fully optional — paste a sales mix or type qty sold. We multiply by recipe plate cost for <strong>ideal food cost</strong> (what you should have used if every plate was perfect).'
            : 'Optional. Enter or import item qty sold; ideal food cost = qty × plate cost from linked recipes.'; ?></p>

        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-ideal">—</div><div class="lbl"><?php echo $is_sweet ? 'Ideal food $' : 'Ideal food $'; ?></div></div>
            <div class="stat"><div class="num" id="stat-sales">—</div><div class="lbl"><?php echo $is_sweet ? 'Menu sales $' : 'Menu sales $'; ?></div></div>
            <div class="stat"><div class="num" id="stat-pct">—</div><div class="lbl"><?php echo $is_sweet ? 'Ideal FC %' : 'Ideal FC %'; ?></div></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Sessions' : 'Sessions'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Each session is a day or period mix (lunch, dinner, week). Use the latest on <a href="/BOH/menu">Menu engineering</a> for Star / Plowhorse / Puzzle / Dog.'
                : 'Sessions feed menu engineering matrix on Menu Engineering.'; ?></p>
            <div id="session-list"></div>
            <div class="toolbar" style="margin-top:10px;">
                <button type="button" class="btn btn-primary btn-small" id="new-session"><?php echo $is_sweet ? '+ New session' : '+ New session'; ?></button>
                <button type="button" class="btn btn-danger btn-small" id="del-session"><?php echo $is_sweet ? 'Delete session' : 'Delete'; ?></button>
            </div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Session details' : 'Session details'; ?></h2>
            <div class="field-row">
                <div class="field"><label><?php echo $is_sweet ? 'Label' : 'Label'; ?></label><input id="s-label" placeholder="<?php echo $is_sweet ? 'e.g. Week of Jul 21 dinner' : 'Session label'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Date (start)' : 'Date'; ?></label><input type="date" id="s-date"></div>
            </div>
            <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="s-notes" placeholder="<?php echo $is_sweet ? 'Shift, POS source…' : 'Optional notes'; ?>"></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Pull from POS (optional)' : 'Pull from POS (optional)'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Square Orders + Toast selections → qty sold by item. Creates/updates a PMIX session for ideal FC &amp; the menu matrix. Needs POS connected on <a href="/admin/pos-connect">POS Connections</a>. Full sales sync also soft-pulls mix when available ✨'
                : 'Pull item qty from Square/Toast. Also soft-attached on full POS sync when available.'; ?></p>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Days back' : 'Days'; ?></label>
                    <select id="pos-days">
                        <option value="1"><?php echo $is_sweet ? 'Today' : '1 day'; ?></option>
                        <option value="7" selected><?php echo $is_sweet ? '7 days' : '7 days'; ?></option>
                        <option value="14"><?php echo $is_sweet ? '14 days' : '14 days'; ?></option>
                        <option value="28"><?php echo $is_sweet ? '28 days' : '28 days'; ?></option>
                    </select>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Provider' : 'Provider'; ?></label>
                    <select id="pos-provider">
                        <option value=""><?php echo $is_sweet ? 'All connected' : 'All connected'; ?></option>
                        <option value="square">Square</option>
                        <option value="toast">Toast</option>
                    </select>
                </div>
            </div>
            <label class="field" style="display:flex;align-items:flex-start;gap:10px;font-size:0.9rem;">
                <input type="checkbox" id="pos-split-days" style="margin-top:3px;">
                <span><?php echo $is_sweet
                    ? 'Split into one session per day (better for day-level matrix)'
                    : 'One session per business day'; ?></span>
            </label>
            <div class="toolbar" style="margin-top:10px;">
                <button type="button" class="btn btn-primary" id="pos-pull-btn"><?php echo $is_sweet ? '⬇ Pull item mix from POS' : 'Pull from POS'; ?></button>
            </div>
            <p class="hint" id="pos-pull-status" style="margin:8px 0 0;"></p>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Import mix (optional)' : 'Import mix (optional)'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'CSV or paste: <code>Item name, Qty, Unit price</code> (header optional). Unit price optional — we fall back to your menu price.'
                : 'CSV columns: name, qty, unit price (optional).'; ?></p>
            <label class="file-label"><?php echo $is_sweet ? '📎 Choose CSV file' : 'Choose CSV'; ?>
                <input type="file" id="csv-file" accept=".csv,text/csv,text/plain">
            </label>
            <div class="field"><label><?php echo $is_sweet ? 'Or paste rows' : 'Or paste rows'; ?></label>
                <textarea id="csv-paste" placeholder="Classic PB&amp;J, 42, 8.50&#10;Grilled cheese, 18, 9.00"></textarea>
            </div>
            <div class="toolbar">
                <button type="button" class="btn btn-primary" id="import-btn"><?php echo $is_sweet ? 'Import into session' : 'Import into session'; ?></button>
                <button type="button" class="btn btn-ghost" id="replace-import"><?php echo $is_sweet ? 'Replace all lines' : 'Replace all lines'; ?></button>
            </div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Manual lines' : 'Manual lines'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Pick a menu item when you can — plate cost & sell price auto-fill. Or type a free name + qty.'
                : 'Link menu items for plate cost; or free-type name + qty.'; ?></p>
            <div id="manual-lines"></div>
            <button type="button" class="btn btn-secondary" id="add-line" style="width:100%;margin-top:6px;"><?php echo $is_sweet ? '+ Add line' : '+ Add line'; ?></button>
            <button type="button" class="btn btn-primary" id="save-session" style="width:100%;margin-top:10px;"><?php echo $is_sweet ? 'Save session &amp; calc ✨' : 'Save &amp; calculate'; ?></button>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Ideal food cost' : 'Ideal food cost'; ?></h2>
            <div id="ideal-result">
                <div class="empty"><?php echo $is_sweet ? 'Save a session with qty sold to see ideal FC' : 'Save a session to see ideal food cost.'; ?></div>
            </div>
        </div>

        <div class="actions-bar">
            <a href="/BOH/menu" class="btn btn-secondary"><?php echo $is_sweet ? 'Menu engineering' : 'Menu engineering'; ?></a>
            <a href="/admin/waste" class="btn btn-secondary"><?php echo $is_sweet ? 'Waste log' : 'Waste log'; ?></a>
            <a href="/admin/pnl" class="btn btn-primary"><?php echo $is_sweet ? 'P&amp;L' : 'P&amp;L'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/food-cost-shared.js?v=2"></script>
    <script src="/pos-sync-client.js?v=3"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var FC = window.PbjFoodCost;
        var state = FC ? FC.loadPmix() : { sessions: [] };
        var activeId = null;
        var replaceOnImport = false;

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money(n) { if (n == null || isNaN(n)) return '—'; return '$' + (Math.round(n * 100) / 100).toFixed(2); }
        function num(v) { if (v === '' || v == null) return null; var n = parseFloat(v); return isNaN(n) ? null : n; }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1800);
        }
        function save() {
            if (FC) FC.savePmix(state);
            else localStorage.setItem('pbj_admin_pmix_v1', JSON.stringify(state));
        }
        function active() {
            if (!activeId) return null;
            for (var i = 0; i < (state.sessions || []).length; i++) {
                if (String(state.sessions[i].id) === String(activeId)) return state.sessions[i];
            }
            return null;
        }
        function menuOptionsHtml(selectedId) {
            var menu = FC ? FC.loadMenu() : { items: [] };
            var html = '<option value="">' + (isSweet ? '— Free type / pick —' : '— Optional menu —') + '</option>';
            (menu.items || []).slice().sort(function (a, b) {
                return String(a.name || '').localeCompare(String(b.name || ''));
            }).forEach(function (it) {
                var sel = selectedId && String(selectedId) === String(it.id) ? ' selected' : '';
                html += '<option value="' + esc(it.id) + '"' + sel + ' data-name="' + esc(it.name) + '" data-price="' + esc(it.price != null ? it.price : '') + '">' + esc(it.name) + '</option>';
            });
            return html;
        }

        function ensureSession() {
            if (!state.sessions) state.sessions = [];
            if (!state.sessions.length) {
                var s = {
                    id: uid(),
                    label: isSweet ? 'Today\'s mix' : 'Today',
                    date: todayStr(),
                    notes: '',
                    items: [],
                    updatedAt: Date.now()
                };
                state.sessions.push(s);
                activeId = s.id;
                save();
            }
            if (!activeId) {
                // newest first
                var sorted = state.sessions.slice().sort(function (a, b) {
                    return (b.updatedAt || 0) - (a.updatedAt || 0);
                });
                activeId = sorted[0].id;
            }
        }

        function collectFormIntoSession() {
            var s = active();
            if (!s) return;
            s.label = document.getElementById('s-label').value.trim() || (isSweet ? 'Untitled mix' : 'Untitled');
            s.date = document.getElementById('s-date').value || todayStr();
            s.notes = document.getElementById('s-notes').value.trim();
            var rows = [];
            document.querySelectorAll('#manual-lines .line').forEach(function (row) {
                var menuId = row.querySelector('.l-menu').value;
                var name = row.querySelector('.l-name').value.trim();
                var qty = num(row.querySelector('.l-qty').value);
                var price = num(row.querySelector('.l-price').value);
                if (!name && menuId) {
                    var opt = row.querySelector('.l-menu option:checked');
                    if (opt) name = opt.getAttribute('data-name') || opt.textContent;
                }
                if (!name || qty == null || qty <= 0) return;
                rows.push({
                    name: name,
                    qty: qty,
                    unitPrice: price,
                    menuItemId: menuId || ''
                });
            });
            s.items = rows;
            s.updatedAt = Date.now();
        }

        function renderSessions() {
            ensureSession();
            var root = document.getElementById('session-list');
            var list = (state.sessions || []).slice().sort(function (a, b) {
                return (b.updatedAt || 0) - (a.updatedAt || 0);
            });
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No sessions yet' : 'No sessions') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (s) {
                var n = (s.items || []).length;
                var label = s.label || s.date || 'Session';
                var meta = (s.date || '') + (n ? ' · ' + n + (isSweet ? ' items' : ' items') : '');
                return '<button type="button" class="session-chip' + (String(s.id) === String(activeId) ? ' active' : '') + '" data-id="' + esc(s.id) + '">' +
                    esc(label) + (meta ? ' <span style="opacity:0.75;font-size:0.8rem;">(' + esc(meta) + ')</span>' : '') + '</button>';
            }).join('');
        }

        function renderForm() {
            var s = active();
            if (!s) return;
            document.getElementById('s-label').value = s.label || '';
            document.getElementById('s-date').value = s.date || todayStr();
            document.getElementById('s-notes').value = s.notes || '';
            var root = document.getElementById('manual-lines');
            var items = s.items && s.items.length ? s.items : [{ name: '', qty: '', unitPrice: '', menuItemId: '' }];
            root.innerHTML = items.map(function (row, idx) {
                return lineHtml(row, idx);
            }).join('');
            bindLineEvents();
            calcIdeal();
        }

        function lineHtml(row, idx) {
            return '<div class="line" data-idx="' + idx + '">' +
                '<div class="field" style="grid-column:1/-1;"><label>' + (isSweet ? 'Menu item' : 'Menu item') + '</label>' +
                '<select class="l-menu">' + menuOptionsHtml(row.menuItemId) + '</select></div>' +
                '<div class="field"><label>' + (isSweet ? 'Name' : 'Name') + '</label>' +
                '<input class="l-name" value="' + esc(row.name || '') + '" placeholder="' + (isSweet ? 'Item name' : 'Name') + '"></div>' +
                '<div class="field"><label>' + (isSweet ? 'Qty sold' : 'Qty') + '</label>' +
                '<input class="l-qty" type="number" min="0" step="0.01" value="' + esc(row.qty != null ? row.qty : '') + '"></div>' +
                '<div class="field"><label>' + (isSweet ? 'Unit $' : 'Unit $') + '</label>' +
                '<input class="l-price" type="number" min="0" step="0.01" value="' + esc(row.unitPrice != null ? row.unitPrice : '') + '"></div>' +
                '<button type="button" class="btn btn-small btn-danger l-del" title="Remove">×</button>' +
                '</div>';
        }

        function bindLineEvents() {
            document.querySelectorAll('#manual-lines .l-menu').forEach(function (sel) {
                sel.addEventListener('change', function () {
                    var row = this.closest('.line');
                    var opt = this.options[this.selectedIndex];
                    if (!opt || !opt.value) return;
                    var name = opt.getAttribute('data-name');
                    var price = opt.getAttribute('data-price');
                    if (name) row.querySelector('.l-name').value = name;
                    if (price !== '' && price != null) row.querySelector('.l-price').value = price;
                });
            });
            document.querySelectorAll('#manual-lines .l-del').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var row = this.closest('.line');
                    if (document.querySelectorAll('#manual-lines .line').length <= 1) {
                        row.querySelector('.l-menu').value = '';
                        row.querySelector('.l-name').value = '';
                        row.querySelector('.l-qty').value = '';
                        row.querySelector('.l-price').value = '';
                        return;
                    }
                    row.remove();
                });
            });
        }

        function calcIdeal() {
            var s = active();
            if (!s || !FC) {
                document.getElementById('stat-ideal').textContent = '—';
                document.getElementById('stat-sales').textContent = '—';
                document.getElementById('stat-pct').textContent = '—';
                return;
            }
            // use in-memory form if present
            collectFormIntoSession();
            var result = FC.idealFoodCostFromSession(s);
            document.getElementById('stat-ideal').textContent = result.matched ? money(result.foodCost) : '—';
            document.getElementById('stat-sales').textContent = result.sales != null ? money(result.sales) : '—';
            document.getElementById('stat-pct').textContent = result.idealPct != null ? result.idealPct + '%' : '—';

            var root = document.getElementById('ideal-result');
            if (!s.items || !s.items.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'Add qty sold lines, then save ✨' : 'Add lines with qty sold.') + '</div>';
                return;
            }
            var banner = '<div class="ideal-banner">' +
                '<div><strong>' + (isSweet ? 'Ideal food cost: ' : 'Ideal food: ') + money(result.foodCost) + '</strong>' +
                (result.idealPct != null ? ' · <strong>' + result.idealPct + '%</strong> of menu sales' : '') + '</div>' +
                '<div style="font-size:0.9rem;opacity:0.85;margin-top:6px;">' +
                (isSweet
                    ? result.matched + ' matched to recipes · ' + result.unmatched + ' without plate cost · sales ' + money(result.sales)
                    : result.matched + ' matched · ' + result.unmatched + ' unmatched · sales ' + money(result.sales)) +
                '</div></div>';

            var table = '<table class="detail-table"><thead><tr>' +
                '<th>' + (isSweet ? 'Item' : 'Item') + '</th>' +
                '<th class="num">' + (isSweet ? 'Qty' : 'Qty') + '</th>' +
                '<th class="num">' + (isSweet ? 'Plate' : 'Plate') + '</th>' +
                '<th class="num">' + (isSweet ? 'Ideal $' : 'Ideal $') + '</th>' +
                '<th></th></tr></thead><tbody>';
            (result.detail || []).forEach(function (d) {
                table += '<tr><td>' + esc(d.name) +
                    (d.recipe ? '<div style="font-size:0.78rem;opacity:0.65;">' + esc(d.recipe) + '</div>' : '') +
                    '</td><td class="num">' + esc(d.qty) +
                    '</td><td class="num">' + (d.plateCost != null ? money(d.plateCost) : '—') +
                    '</td><td class="num">' + (d.ideal != null ? money(d.ideal) : '—') +
                    '</td><td>' + (d.matched
                        ? '<span class="badge badge-ok">' + (isSweet ? 'OK' : 'OK') + '</span>'
                        : '<span class="badge badge-miss">' + (isSweet ? 'No cost' : 'No cost') + '</span>') +
                    '</td></tr>';
            });
            table += '</tbody></table>';
            if (result.unmatched > 0) {
                table += '<p class="hint" style="margin-top:10px;">' + (isSweet
                    ? 'Link recipes on <a href="/BOH/recipe-cards">Standardized Recipes</a> and cost ingredients on <a href="/admin/costing">Costing</a> for full ideal FC.'
                    : 'Link recipes + cost ingredients for full ideal food cost.') + '</p>';
            }
            root.innerHTML = banner + table;
        }

        function parseCsvText(text) {
            var lines = String(text || '').replace(/^\uFEFF/, '').split(/\r?\n/).filter(function (l) { return l.trim(); });
            if (!lines.length) return [];
            var rows = [];
            lines.forEach(function (line, i) {
                var cols = splitCsvLine(line);
                if (!cols.length) return;
                // skip header
                if (i === 0 && /name|item|product|menu/i.test(cols[0]) && /qty|quantity|sold|count/i.test(cols[1] || '')) return;
                var name = (cols[0] || '').trim();
                var qty = num(cols[1]);
                var price = num(cols[2]);
                if (!name || qty == null) return;
                var menuIt = FC ? FC.findMenuItemByName(name) : null;
                rows.push({
                    name: name,
                    qty: qty,
                    unitPrice: price != null ? price : (menuIt && menuIt.price != null ? num(menuIt.price) : null),
                    menuItemId: menuIt ? menuIt.id : ''
                });
            });
            return rows;
        }

        function splitCsvLine(line) {
            var out = [], cur = '', inQ = false;
            for (var i = 0; i < line.length; i++) {
                var c = line[i];
                if (c === '"') {
                    if (inQ && line[i + 1] === '"') { cur += '"'; i++; }
                    else inQ = !inQ;
                } else if ((c === ',' || c === '\t' || c === ';') && !inQ) {
                    out.push(cur.trim()); cur = '';
                } else cur += c;
            }
            out.push(cur.trim());
            return out;
        }

        document.getElementById('session-list').addEventListener('click', function (e) {
            var chip = e.target.closest('[data-id]');
            if (!chip) return;
            collectFormIntoSession();
            save();
            activeId = chip.getAttribute('data-id');
            renderSessions();
            renderForm();
        });

        document.getElementById('new-session').addEventListener('click', function () {
            collectFormIntoSession();
            var s = {
                id: uid(),
                label: isSweet ? 'New mix' : 'New session',
                date: todayStr(),
                notes: '',
                items: [],
                updatedAt: Date.now()
            };
            state.sessions.push(s);
            activeId = s.id;
            save();
            renderSessions();
            renderForm();
            toast(isSweet ? 'New session ✨' : 'New session');
        });

        document.getElementById('del-session').addEventListener('click', function () {
            var s = active();
            if (!s) return;
            if (!confirm(isSweet ? 'Delete this PMIX session?' : 'Delete session?')) return;
            state.sessions = state.sessions.filter(function (x) { return String(x.id) !== String(s.id); });
            activeId = null;
            if (!state.sessions.length) {
                ensureSession();
            } else {
                activeId = state.sessions[0].id;
            }
            save();
            renderSessions();
            renderForm();
        });

        document.getElementById('add-line').addEventListener('click', function () {
            var root = document.getElementById('manual-lines');
            var wrap = document.createElement('div');
            wrap.innerHTML = lineHtml({ name: '', qty: '', unitPrice: '', menuItemId: '' }, root.children.length);
            root.appendChild(wrap.firstChild);
            bindLineEvents();
        });

        document.getElementById('save-session').addEventListener('click', function () {
            collectFormIntoSession();
            save();
            renderSessions();
            calcIdeal();
            toast(isSweet ? 'Session saved 💕' : 'Saved');
        });

        document.getElementById('replace-import').addEventListener('click', function () {
            replaceOnImport = !replaceOnImport;
            this.classList.toggle('btn-primary', replaceOnImport);
            this.classList.toggle('btn-ghost', !replaceOnImport);
            toast(replaceOnImport
                ? (isSweet ? 'Import will replace lines' : 'Replace mode on')
                : (isSweet ? 'Import will append' : 'Append mode'));
        });

        function doImport(text) {
            var rows = parseCsvText(text);
            if (!rows.length) {
                toast(isSweet ? 'No rows found' : 'No rows found');
                return;
            }
            var s = active();
            if (!s) return;
            if (replaceOnImport) s.items = rows;
            else s.items = (s.items || []).concat(rows);
            s.updatedAt = Date.now();
            save();
            renderSessions();
            renderForm();
            toast(isSweet ? 'Imported ' + rows.length + ' lines ✨' : 'Imported ' + rows.length);
        }

        document.getElementById('import-btn').addEventListener('click', function () {
            doImport(document.getElementById('csv-paste').value);
        });
        document.getElementById('csv-file').addEventListener('change', function () {
            var f = this.files && this.files[0];
            if (!f) return;
            var reader = new FileReader();
            reader.onload = function () { doImport(String(reader.result || '')); };
            reader.readAsText(f);
            this.value = '';
        });

        // live recalc on blur of meta fields
        ['s-label', 's-date', 's-notes'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', function () {
                collectFormIntoSession();
                save();
                renderSessions();
            });
        });

        document.getElementById('pos-pull-btn').addEventListener('click', function () {
            var btn = this;
            var status = document.getElementById('pos-pull-status');
            if (!window.PbjPosSync || !window.PbjPosSync.syncPmix) {
                toast(isSweet ? 'POS client missing — open POS Connections once' : 'POS client missing');
                return;
            }
            var days = parseInt(document.getElementById('pos-days').value, 10) || 7;
            var provider = document.getElementById('pos-provider').value || '';
            var splitDays = !!document.getElementById('pos-split-days').checked;
            btn.disabled = true;
            var prev = btn.textContent;
            btn.textContent = isSweet ? 'Pulling…' : 'Pulling…';
            if (status) status.textContent = isSweet ? 'Talking to POS…' : 'Syncing…';
            window.PbjPosSync.syncPmix({ days: days, provider: provider || undefined, splitDays: splitDays }).then(function (res) {
                btn.disabled = false;
                btn.textContent = prev;
                if (!res || !res.ok) {
                    var msg = (res && (res.hint || res.error)) || (isSweet ? 'Pull failed — connect Square or Toast' : 'Pull failed');
                    if (status) status.textContent = msg;
                    toast(msg);
                    return;
                }
                // Reload from storage (syncPmix already wrote)
                state = FC ? FC.loadPmix() : (window.PbjPosSync ? JSON.parse(localStorage.getItem('pbj_admin_pmix_v1') || '{"sessions":[]}') : { sessions: [] });
                var merged = res.merged || {};
                var count = res.count != null ? res.count : (merged.itemCount || 0);
                // Prefer the session we just wrote
                if (merged.session && merged.session.id) {
                    activeId = merged.session.id;
                } else if (state.sessions && state.sessions.length) {
                    var sorted = state.sessions.slice().sort(function (a, b) {
                        return (b.updatedAt || 0) - (a.updatedAt || 0);
                    });
                    activeId = sorted[0].id;
                }
                renderSessions();
                renderForm();
                var note = isSweet
                    ? ('Pulled ' + count + ' items' + (splitDays ? ' · per-day sessions' : '') + ' ✨')
                    : ('Pulled ' + count + ' items');
                if (status) status.textContent = note + (res.start && res.end ? ' · ' + res.start + ' → ' + res.end : '');
                toast(note);
            }).catch(function () {
                btn.disabled = false;
                btn.textContent = prev;
                if (status) status.textContent = isSweet ? 'Network error' : 'Network error';
                toast(isSweet ? 'Network error' : 'Network error');
            });
        });

        ensureSession();
        renderSessions();
        renderForm();
    })();
    </script>
</body>
</html>
