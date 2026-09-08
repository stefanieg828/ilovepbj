<?php
/**
 * Optional waste log — track spoilage/trim/overproduction for food cost control.
 */
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$reasons = [
    'Spoilage / expired',
    'Prep overproduction',
    'Dropped / mishandled',
    'Trim / yield loss',
    'Quality reject',
    'Staff meal (extra)',
    'Other',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'Waste log' : 'Waste log'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; }
        .subtitle { margin: 10px 0 0; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.2rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.8rem; opacity: 0.7; margin-top: 4px; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 10px; }
        .hint { font-size: 0.92rem; opacity: 0.75; line-height: 1.45; margin: 0 0 12px; }
        .hint a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .opt { font-size: 0.85rem; opacity: 0.7; margin: -6px 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8;
            <?php else: ?>font-family: 'Lora', serif; background: #FAF8F5;<?php endif; ?>
        }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> color: inherit; }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.88rem; border-radius: 10px; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .entry { padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; }
        .entry-meta { font-size: 0.9rem; opacity: 0.75; margin-top: 4px; line-height: 1.4; }
        .empty { text-align: center; padding: 20px; opacity: 0.8; }
        .actions-bar { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.88rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory' : 'Back to Inventory'; ?></a>
        <h1><?php echo $is_sweet ? 'Waste log' : 'Waste log'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Optional · protect food cost' : 'Optional food cost control'; ?></p>
    </div>
    <div class="content">
        <p class="opt"><?php echo $is_sweet
            ? '✨ Fully optional — use when you want visibility on spoilage & over-prep. Pairs with inventory costs & PMIX.'
            : 'Optional tool. Log waste when you want food-cost visibility.'; ?></p>
        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-cost">—</div><div class="lbl"><?php echo $is_sweet ? 'Waste $ (7d)' : 'Waste $ (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-count">0</div><div class="lbl"><?php echo $is_sweet ? 'Entries (7d)' : 'Entries (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-top">—</div><div class="lbl"><?php echo $is_sweet ? 'Top reason' : 'Top reason'; ?></div></div>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Log waste' : 'Log waste'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Pick a product from inventory when you can — we estimate $ from case price. Or type a name + cost manually.'
                : 'Link inventory for auto cost, or enter cost manually.'; ?></p>
            <form id="waste-form">
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Date' : 'Date'; ?></label><input type="date" id="f-date" required></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Reason' : 'Reason'; ?></label>
                        <select id="f-reason">
                            <?php foreach ($reasons as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Product (inventory, optional)' : 'Inventory product (optional)'; ?></label>
                    <select id="f-ing"><option value=""><?php echo $is_sweet ? '— Type name below or pick —' : '— Optional —'; ?></option></select>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Item name' : 'Item name'; ?></label><input id="f-name" required placeholder="<?php echo $is_sweet ? 'e.g. Roma tomatoes' : 'Item name'; ?>"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Qty wasted' : 'Qty'; ?></label><input type="number" id="f-qty" min="0" step="0.01" required placeholder="1"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Unit' : 'Unit'; ?></label><input id="f-unit" placeholder="<?php echo $is_sweet ? 'lb, ea, case…' : 'lb, ea…'; ?>"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Cost $ (est.)' : 'Cost $'; ?></label><input type="number" id="f-cost" min="0" step="0.01" placeholder="0.00"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Station, shift, story…' : 'Optional notes'; ?>"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;"><?php echo $is_sweet ? 'Log waste ✨' : 'Log waste'; ?></button>
            </form>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Recent waste' : 'Recent waste'; ?></h2>
            <div class="filters" id="filters">
                <button type="button" class="chip active" data-d="7"><?php echo $is_sweet ? '7 days' : '7 days'; ?></button>
                <button type="button" class="chip" data-d="28"><?php echo $is_sweet ? '28 days' : '28 days'; ?></button>
                <button type="button" class="chip" data-d="0"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
            </div>
            <div id="list"></div>
        </div>
        <div class="actions-bar">
            <a href="/admin/pmix" class="btn btn-secondary"><?php echo $is_sweet ? 'PMIX / ideal FC' : 'PMIX'; ?></a>
            <a href="/BOH/menu" class="btn btn-secondary"><?php echo $is_sweet ? 'Menu engineering' : 'Menu engineering'; ?></a>
            <a href="/admin/pnl" class="btn btn-primary"><?php echo $is_sweet ? 'P&amp;L' : 'P&amp;L'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/food-cost-shared.js?v=2"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var daysFilter = 7;
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

        var FC = window.PbjFoodCost;
        var state = FC ? FC.loadWaste() : { entries: [] };
        function save() {
            if (FC) FC.saveWaste(state);
            else localStorage.setItem('pbj_admin_waste_v1', JSON.stringify(state));
        }

        function fillIngredients() {
            var sel = document.getElementById('f-ing');
            var master = FC ? FC.loadMaster() : { items: {} };
            var opts = '<option value="">' + (isSweet ? '— Type name below or pick —' : '— Optional —') + '</option>';
            Object.keys(master.items || {}).sort(function (a, b) {
                var na = (master.items[a].name || a).toLowerCase();
                var nb = (master.items[b].name || b).toLowerCase();
                return na.localeCompare(nb);
            }).forEach(function (k) {
                var it = master.items[k];
                opts += '<option value="' + esc(k) + '" data-name="' + esc(it.name || k) + '">' + esc(it.name || k) + '</option>';
            });
            sel.innerHTML = opts;
        }

        function estimateCost() {
            var key = document.getElementById('f-ing').value;
            var qty = num(document.getElementById('f-qty').value);
            if (!key || qty == null) return;
            var master = FC.loadMaster();
            var it = master.items[key];
            if (!it) return;
            var cpu = FC.costPerRecipeUnit(it);
            // prefer case price / pack as "unit" estimate when no recipe unit
            if (cpu == null) {
                var cp = parseFloat(it.casePrice);
                var pack = parseFloat(it.pack || it.packSize) || 1;
                if (!isNaN(cp)) cpu = cp / pack;
            }
            if (cpu == null || isNaN(cpu)) return;
            document.getElementById('f-cost').value = String(Math.round(cpu * qty * 100) / 100);
        }

        document.getElementById('f-ing').addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (opt && opt.getAttribute('data-name')) {
                document.getElementById('f-name').value = opt.getAttribute('data-name');
            }
            estimateCost();
        });
        document.getElementById('f-qty').addEventListener('input', estimateCost);

        function inWindow(dateStr) {
            if (!daysFilter) return true;
            var cut = new Date();
            cut.setHours(12, 0, 0, 0);
            cut.setDate(cut.getDate() - (daysFilter - 1));
            return new Date(dateStr + 'T12:00:00') >= cut;
        }

        function render() {
            var list = (state.entries || []).filter(function (e) { return e && e.date && inWindow(e.date); });
            list.sort(function (a, b) { return (b.date || '').localeCompare(a.date || ''); });
            var total = 0, reasons = {};
            list.forEach(function (e) {
                var c = num(e.cost);
                if (c != null) total += c;
                var r = e.reason || 'Other';
                reasons[r] = (reasons[r] || 0) + 1;
            });
            document.getElementById('stat-cost').textContent = list.length ? money(total) : '—';
            document.getElementById('stat-count').textContent = String(list.length);
            var top = '—', topN = 0;
            Object.keys(reasons).forEach(function (r) {
                if (reasons[r] > topN) { topN = reasons[r]; top = r; }
            });
            document.getElementById('stat-top').textContent = topN ? top.split(' ')[0] : '—';
            document.getElementById('stat-top').title = top;

            var root = document.getElementById('list');
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No waste logged in this window — that\'s a good day 🥗' : 'No waste entries in this window.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (e) {
                var when = new Date(e.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                return '<div class="entry"><div class="entry-top"><span><strong>' + esc(e.itemName || e.name || '—') + '</strong></span><span>' + money(e.cost) + '</span></div>' +
                    '<div class="entry-meta">' + esc(when) + ' · ' + esc(e.qty) + (e.unit ? ' ' + esc(e.unit) : '') +
                    ' · ' + esc(e.reason || '') + (e.notes ? ' · ' + esc(e.notes) : '') + '</div>' +
                    '<button type="button" class="btn btn-small btn-danger" data-del="' + esc(e.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button></div>';
            }).join('');
        }

        document.getElementById('waste-form').addEventListener('submit', function (e) {
            e.preventDefault();
            state.entries = state.entries || [];
            state.entries.push({
                id: uid(),
                date: document.getElementById('f-date').value,
                itemName: document.getElementById('f-name').value.trim(),
                ingredientKey: document.getElementById('f-ing').value || '',
                qty: num(document.getElementById('f-qty').value),
                unit: document.getElementById('f-unit').value.trim(),
                cost: num(document.getElementById('f-cost').value),
                reason: document.getElementById('f-reason').value,
                notes: document.getElementById('f-notes').value.trim(),
                updatedAt: Date.now()
            });
            save();
            toast(isSweet ? 'Waste logged ✨' : 'Logged');
            document.getElementById('f-name').value = '';
            document.getElementById('f-qty').value = '';
            document.getElementById('f-unit').value = '';
            document.getElementById('f-cost').value = '';
            document.getElementById('f-notes').value = '';
            document.getElementById('f-ing').value = '';
            render();
        });
        document.getElementById('list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-del]');
            if (!btn) return;
            if (!confirm(isSweet ? 'Remove this waste entry?' : 'Remove entry?')) return;
            state.entries = state.entries.filter(function (x) { return String(x.id) !== String(btn.dataset.del); });
            save();
            render();
        });
        document.getElementById('filters').addEventListener('click', function (e) {
            var c = e.target.closest('[data-d]');
            if (!c) return;
            daysFilter = parseInt(c.getAttribute('data-d'), 10) || 0;
            document.querySelectorAll('#filters .chip').forEach(function (x) { x.classList.toggle('active', x === c); });
            render();
        });

        document.getElementById('f-date').value = todayStr();
        fillIngredients();
        render();
    })();
    </script>
</body>
</html>
