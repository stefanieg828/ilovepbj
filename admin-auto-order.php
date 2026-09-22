<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
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
    <title><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.5rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 900px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.4rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.82rem; opacity: 0.7; margin-top: 4px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .filter-chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .filter-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 6px; }
        .vendor-meta { font-size: 0.92rem; opacity: 0.75; margin-bottom: 12px; line-height: 1.4; }
        .line { display: grid; grid-template-columns: 1.4fr 0.7fr 0.7fr auto; gap: 8px; align-items: center; padding: 10px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        @media (max-width: 560px) {
            .line { grid-template-columns: 1fr 1fr; }
            .line .name { grid-column: 1 / -1; }
        }
        .line:last-child { border-bottom: none; }
        .line .name { font-size: 1.05rem; }
        .line .sub { font-size: 0.82rem; opacity: 0.65; margin-top: 2px; }
        .line input { width: 100%; box-sizing: border-box; border-radius: 10px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 8px 10px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .line label { display: block; font-size: 0.75rem; opacity: 0.6; margin-bottom: 2px; }
        .empty { text-align: center; padding: 36px 20px; background: white; border-radius: 18px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .actions-bar { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.75; margin: 0 0 12px; line-height: 1.4; }
        .vendor-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .saved-order { border-radius: 14px; padding: 14px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .saved-order h3 { margin: 0 0 6px; font-size: 1.1rem; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 140px; }
        .warn-box { background: #FFF8E6; border: 1px solid #F0D78C; border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; font-size: 0.95rem; line-height: 1.4; }
        .warn-box a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .flow-steps { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .flow-step { flex: 1; min-width: 100px; text-align: center; padding: 10px 8px; border-radius: 14px; font-size: 0.82rem; line-height: 1.25; text-decoration: none; color: inherit; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.06); opacity: 0.75; }
        .flow-step strong { display: block; font-size: 0.72rem; opacity: 0.65; margin-bottom: 2px; }
        .flow-step.active { opacity: 1; <?php if ($is_sweet): ?>background: #E55163; color: white; box-shadow: 0 4px 14px rgba(229,81,99,0.35);<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .flow-step.active strong { opacity: 0.9; color: inherit; }
        .flow-step:hover { opacity: 1; transform: translateY(-1px); }
        .cutoff-banner { border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; font-size: 0.95rem; line-height: 1.4; display: none; }
        .cutoff-banner.show { display: block; }
        .cutoff-banner.soon { background: #FFF3E0; border: 1px solid #FFB74D; color: #E65100; }
        .cutoff-banner.urgent { background: #FFEBEE; border: 1px solid #EF9A9A; color: #B71C1C; }
        .cutoff-banner a { font-weight: 700; }
        .print-only { display: none; }
        .vendor-sheet { width: 100%; border-collapse: collapse; font-size: 10pt; line-height: 1.25; margin: 0 0 18px; }
        .vendor-sheet th, .vendor-sheet td { border: 1px solid #999; padding: 4px 6px; text-align: left; vertical-align: top; }
        .vendor-sheet th { background: #e8e8e8 !important; font-weight: 700; font-size: 9pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .vendor-sheet .num { text-align: right; white-space: nowrap; }
        .vendor-sheet-title { font-size: 14pt; font-weight: 700; margin: 0 0 4px; }
        .vendor-sheet-meta { font-size: 9pt; margin: 0 0 8px; opacity: 0.85; }
        .vendor-sheet-block { break-inside: avoid; page-break-inside: avoid; margin-bottom: 16px; }
        @media print {
            body { background: white; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .back-link, .toolbar, .filters, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, #saved-section, #options-card, .vendor-actions, .line button, .warn-box, .flow-steps, .stats-row, .intro, #cutoff-banner, #warn-box, #order-root { display: none !important; }
            .card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
            .line input { border: none; padding: 0; background: transparent; }
            .print-only { display: block !important; }
            #vendor-print-root { display: block !important; padding: 0 8px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory & Vendors' : 'Back to Inventory & Vendors'; ?></a>
        <h1><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Par + usage + forecast → vendor order lists' : 'Par, usage & forecast → vendor orders'; ?></p>
    </div>

    <div class="content">
        <div class="flow-steps">
            <a class="flow-step" href="/admin/product-setup"><strong>Step 1</strong><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
            <a class="flow-step" href="/admin/count"><strong>Step 2</strong><?php echo $is_sweet ? 'Count Stock' : 'Count Stock'; ?></a>
            <a class="flow-step active" href="/admin/auto-order"><strong>Step 3</strong><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></a>
        </div>

        <div class="intro">
            <?php echo $is_sweet
                ? 'Step 3: suggest orders from <strong>par</strong>, <strong>count-history usage</strong>, and optional <strong>sales forecast</strong> — then group by vendor and email / text / export. More count sessions = smarter usage. Start with <a href="/admin/count">Count Stock</a> 💌'
                : 'Step 3: order suggestions from par, usage (count history), and sales forecast. Group by vendor; email, text, or export. Use <a href="/admin/count">Count Stock</a> first.'; ?>
        </div>

        <div class="stats-row">
            <div class="stat">
                <div class="num" id="stat-lines">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Lines to order' : 'Lines to order'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-vendors">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Vendors' : 'Vendors'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-est">—</div>
                <div class="lbl"><?php echo $is_sweet ? 'Est. cost' : 'Est. cost'; ?></div>
            </div>
        </div>

        <div class="card" id="options-card">
            <h2><?php echo $is_sweet ? 'Order options' : 'Order options'; ?></h2>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Suggest from' : 'Suggest from'; ?></label>
                    <select id="opt-mode">
                        <option value="max"><?php echo $is_sweet ? 'Max of par & usage (recommended)' : 'Max of par & usage'; ?></option>
                        <option value="par"><?php echo $is_sweet ? 'Par only (classic)' : 'Par only'; ?></option>
                        <option value="usage"><?php echo $is_sweet ? 'Usage + cover days only' : 'Usage + cover days only'; ?></option>
                    </select>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Days to cover' : 'Days to cover'; ?></label>
                    <select id="opt-cover">
                        <option value="auto"><?php echo $is_sweet ? 'Auto (until next delivery)' : 'Auto (next delivery)'; ?></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3" selected>3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="7">7</option>
                        <option value="10">10</option>
                        <option value="14">14</option>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Forecasted sales this period ($)' : 'Forecasted sales ($)'; ?></label>
                    <input type="number" id="opt-forecast" min="0" step="0.01" placeholder="<?php echo $is_sweet ? 'Optional — scales usage' : 'Optional'; ?>">
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Sales scale' : 'Sales scale'; ?></label>
                    <div id="sales-scale-hint" class="hint" style="margin:8px 0 0;min-height:1.3em;"><?php echo $is_sweet ? 'Uses avg recent sales when forecast is set' : 'Uses avg recent sales when forecast set'; ?></div>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Include categories' : 'Include categories'; ?></label>
                    <select id="opt-cats">
                        <option value="all"><?php echo $is_sweet ? 'All categories' : 'All categories'; ?></option>
                    </select>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Vendor filter' : 'Vendor filter'; ?></label>
                    <select id="opt-vendor">
                        <option value=""><?php echo $is_sweet ? 'All vendors' : 'All vendors'; ?></option>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Delivery day' : 'Delivery day'; ?></label>
                    <select id="opt-day">
                        <option value=""><?php echo $is_sweet ? 'All delivery days' : 'All delivery days'; ?></option>
                        <option value="today"><?php echo $is_sweet ? 'Delivers today' : 'Delivers today'; ?></option>
                        <option value="tomorrow"><?php echo $is_sweet ? 'Delivers tomorrow' : 'Delivers tomorrow'; ?></option>
                        <option value="Mon">Mon</option>
                        <option value="Tue">Tue</option>
                        <option value="Wed">Wed</option>
                        <option value="Thu">Thu</option>
                        <option value="Fri">Fri</option>
                        <option value="Sat">Sat</option>
                        <option value="Sun">Sun</option>
                    </select>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Unassigned vendor lines' : 'Unassigned vendor lines'; ?></label>
                    <select id="opt-unassigned">
                        <option value="include"><?php echo $is_sweet ? 'Include (show as Unassigned)' : 'Include unassigned'; ?></option>
                        <option value="hide"><?php echo $is_sweet ? 'Hide unassigned' : 'Hide unassigned'; ?></option>
                    </select>
                </div>
            </div>
            <p class="hint" id="gen-hint"></p>
            <p class="hint" id="usage-hint" style="margin-top:-4px;"></p>
            <div class="toolbar" style="margin-bottom:0;">
                <button type="button" class="btn btn-primary" id="regen-btn"><?php echo $is_sweet ? 'Refresh from inventory' : 'Refresh from inventory'; ?></button>
                <button type="button" class="btn btn-primary" id="export-vendor-btn"><?php echo $is_sweet ? 'Vendor CSV 📥' : 'Vendor CSV'; ?></button>
                <button type="button" class="btn btn-secondary" id="export-all-btn"><?php echo $is_sweet ? 'Full CSV (internal)' : 'Full CSV (internal)'; ?></button>
                <button type="button" class="btn btn-secondary" id="print-btn"><?php echo $is_sweet ? 'Print / PDF' : 'Print / PDF'; ?></button>
                <button type="button" class="btn btn-secondary" id="copy-all-btn"><?php echo $is_sweet ? 'Copy all text' : 'Copy all text'; ?></button>
                <button type="button" class="btn btn-secondary" id="email-all-btn"><?php echo $is_sweet ? 'Email all drafts' : 'Email all drafts'; ?></button>
                <button type="button" class="btn btn-ghost" id="save-order-btn"><?php echo $is_sweet ? 'Save this order' : 'Save this order'; ?></button>
            </div>
        </div>

        <div id="cutoff-banner" class="cutoff-banner"></div>
        <div id="warn-box" class="warn-box" style="display:none;"></div>
        <div id="order-root"></div>
        <div class="print-only" id="vendor-print-root" aria-hidden="true"></div>

        <div class="card" id="saved-section">
            <h2><?php echo $is_sweet ? 'Saved orders' : 'Saved orders'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Snapshots of past auto-orders (this browser).' : 'Snapshots of past auto-orders (this browser).'; ?></p>
            <div id="saved-list"></div>
        </div>

        <div class="actions-bar">
            <a href="/admin/count" class="btn btn-secondary"><?php echo $is_sweet ? '← Count Stock' : '← Count Stock'; ?></a>
            <a href="/admin/product-setup" class="btn btn-secondary"><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
            <a href="/admin/vendors" class="btn btn-secondary"><?php echo $is_sweet ? 'Vendors' : 'Vendors'; ?></a>
            <a href="/admin/inventory" class="btn btn-primary"><?php echo $is_sweet ? 'Inventory hub' : 'Inventory hub'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Done ✨' : 'Done'; ?></div>
    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const VENDOR_KEY = 'pbj_admin_vendors_v1';
        const ORDER_KEY = 'pbj_admin_auto_orders_v1';
        const CAT_ORDER_KEY = 'pbj_inv_category_order_v1';
        const COUNT_KEY = 'pbj_inv_count_sessions_v1';
        const OPTS_KEY = 'pbj_auto_order_opts_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyInvPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var view = canP('admin.inventory.view') || canP('admin.inventory.edit');
                var edit = canP('admin.inventory.edit');
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('inv-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="inv-denied">No permission to view inventory.</div>');
                    }
                }
                if (!edit) {
                    document.querySelectorAll('button.btn-primary, button.btn-danger, form button[type=submit]').forEach(function(el){
                        if (el.closest('a') || /print|export|back/i.test(el.textContent||'')) return;
                        el.style.display = 'none';
                    });
                    document.querySelectorAll('form input, form select, form textarea').forEach(function(el){ el.disabled = true; });
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }


        var DEFAULT_CAT_ORDER = [
            'dairy', 'meats', 'frozen', 'canned_dry', 'paper_disposable',
            'chemical_janitorial', 'supplies_equipment', 'produce', 'dispenser_beverage'
        ];
        var DEFAULT_CAT_LABELS = {
            dairy: 'Dairy',
            meats: 'Meats',
            frozen: 'Frozen',
            canned_dry: 'Canned & Dry',
            paper_disposable: 'Paper & Disposable',
            chemical_janitorial: 'Chemical & Janitorial',
            supplies_equipment: 'Supplies & Equipment',
            produce: 'Produce',
            dispenser_beverage: 'Dispenser Beverage'
        };
        var LEGACY_CAT_MAP = {
            food: 'canned_dry',
            paper: 'paper_disposable',
            janitorial: 'chemical_janitorial',
            other: 'supplies_equipment'
        };
        var UNASSIGNED = isSweet ? 'Unassigned vendor' : 'Unassigned vendor';
        var catOrder = [];
        var catLabels = {};
        var customCats = {};

        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
        }
        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg || (isSweet ? 'Done ✨' : 'Done');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1300);
        }
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function isBuiltinCat(slug) { return DEFAULT_CAT_ORDER.indexOf(slug) !== -1; }
        function catLabel(slug) {
            return catLabels[slug] || DEFAULT_CAT_LABELS[slug] || customCats[slug] || slug;
        }
        function rebuildCatLabels() {
            catLabels = {};
            DEFAULT_CAT_ORDER.forEach(function (c) { catLabels[c] = DEFAULT_CAT_LABELS[c]; });
            Object.keys(customCats).forEach(function (c) { catLabels[c] = customCats[c]; });
        }
        function loadCatState() {
            customCats = {};
            catOrder = DEFAULT_CAT_ORDER.slice();
            try {
                var r = JSON.parse(localStorage.getItem(CAT_ORDER_KEY) || 'null');
                if (Array.isArray(r) && r.length) {
                    catOrder = mergeOrder(r);
                } else if (r && typeof r === 'object') {
                    if (r.custom && typeof r.custom === 'object') {
                        Object.keys(r.custom).forEach(function (slug) {
                            var lab = String(r.custom[slug] || '').trim();
                            if (lab && !isBuiltinCat(slug)) customCats[slug] = lab;
                        });
                    }
                    if (r.labels && typeof r.labels === 'object') {
                        Object.keys(r.labels).forEach(function (slug) {
                            if (isBuiltinCat(slug)) return;
                            var lab = String(r.labels[slug] || '').trim();
                            if (lab) customCats[slug] = lab;
                        });
                    }
                    rebuildCatLabels();
                    catOrder = mergeOrder(Array.isArray(r.order) && r.order.length ? r.order : DEFAULT_CAT_ORDER);
                    return;
                }
            } catch (e) {}
            rebuildCatLabels();
            catOrder = mergeOrder(DEFAULT_CAT_ORDER);
        }
        function mergeOrder(preferred) {
            rebuildCatLabels();
            var seen = {};
            var order = [];
            (preferred || []).forEach(function (c) {
                if (!c || seen[c]) return;
                if (DEFAULT_CAT_LABELS[c] || customCats[c]) {
                    order.push(c);
                    seen[c] = true;
                }
            });
            DEFAULT_CAT_ORDER.forEach(function (c) {
                if (!seen[c]) { order.push(c); seen[c] = true; }
            });
            Object.keys(customCats).forEach(function (c) {
                if (!seen[c]) { order.push(c); seen[c] = true; }
            });
            return order;
        }
        function normalizeCategory(cat) {
            if (!cat) return 'canned_dry';
            if (LEGACY_CAT_MAP[cat]) return LEGACY_CAT_MAP[cat];
            if (catLabels[cat] || DEFAULT_CAT_LABELS[cat] || customCats[cat]) return cat;
            if (String(cat).indexOf('custom_') === 0 || String(cat).indexOf('cat_') === 0) {
                if (!customCats[cat]) {
                    customCats[cat] = String(cat).replace(/^custom_/, '').replace(/_/g, ' ');
                    rebuildCatLabels();
                    if (catOrder.indexOf(cat) === -1) catOrder.push(cat);
                }
                return cat;
            }
            return 'canned_dry';
        }
        function catRank(cat) {
            var i = catOrder.indexOf(normalizeCategory(cat));
            return i === -1 ? 999 : i;
        }
        loadCatState();

        function fillCatFilter() {
            var sel = document.getElementById('opt-cats');
            var current = sel.value || 'all';
            var html = '<option value="all">' + (isSweet ? 'All categories' : 'All categories') + '</option>';
            html += '<option value="group_food">' + (isSweet ? 'Food categories (no paper/chem/supply)' : 'Food categories only') + '</option>';
            html += '<option value="group_nonfood">' + (isSweet ? 'Paper + Chem + Supplies' : 'Paper + Chem + Supplies') + '</option>';
            catOrder.forEach(function (c) {
                html += '<option value="' + c + '">' + esc(catLabel(c)) + '</option>';
            });
            sel.innerHTML = html;
            if ([].some.call(sel.options, function (o) { return o.value === current; })) sel.value = current;
            else sel.value = 'all';
        }
        fillCatFilter();

        function loadInventory() {
            try {
                var r = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                return (r && typeof r.items === 'object') ? r.items : {};
            } catch (e) { return {}; }
        }
        function loadVendors() {
            try {
                var v = JSON.parse(localStorage.getItem(VENDOR_KEY) || 'null');
                return (v && Array.isArray(v.vendors)) ? v.vendors : [];
            } catch (e) { return []; }
        }
        function loadSaved() {
            try {
                var r = JSON.parse(localStorage.getItem(ORDER_KEY) || 'null');
                return r && Array.isArray(r.orders) ? r : { orders: [] };
            } catch (e) { return { orders: [] }; }
        }
        function saveSaved(state) {
            localStorage.setItem(ORDER_KEY, JSON.stringify(state));
        }

        function parByOf(item) {
            return item && item.parBy === 'case' ? 'case' : 'each';
        }
        function parByWord(item, forQty) {
            if (parByOf(item) === 'case') {
                var n = forQty != null ? parseFloat(forQty) : NaN;
                if (!isNaN(n) && n === 1) return isSweet ? 'case' : 'case';
                return isSweet ? 'cases' : 'cases';
            }
            return isSweet ? 'each' : 'each';
        }
        function loadCountSessions() {
            try {
                var r = JSON.parse(localStorage.getItem(COUNT_KEY) || 'null');
                return r && Array.isArray(r.sessions) ? r.sessions.slice() : [];
            } catch (e) { return []; }
        }
        function loadSalesDays() {
            try {
                var r = JSON.parse(localStorage.getItem('pbj_admin_sales_v2') || 'null');
                if (!r || !Array.isArray(r.days)) {
                    r = JSON.parse(localStorage.getItem('pbj_admin_sales_v1') || 'null');
                }
                return r && Array.isArray(r.days) ? r.days : [];
            } catch (e) { return []; }
        }
        function loadOrderOpts() {
            try {
                var r = JSON.parse(localStorage.getItem(OPTS_KEY) || 'null');
                return r && typeof r === 'object' ? r : {};
            } catch (e) { return {}; }
        }
        function saveOrderOpts() {
            var o = {
                mode: document.getElementById('opt-mode').value,
                cover: document.getElementById('opt-cover').value,
                forecast: document.getElementById('opt-forecast').value,
                cats: document.getElementById('opt-cats').value,
                vendor: document.getElementById('opt-vendor').value,
                day: document.getElementById('opt-day').value,
                unassigned: document.getElementById('opt-unassigned').value
            };
            localStorage.setItem(OPTS_KEY, JSON.stringify(o));
        }
        function applyOrderOpts() {
            var o = loadOrderOpts();
            if (o.mode) document.getElementById('opt-mode').value = o.mode;
            if (o.cover) document.getElementById('opt-cover').value = o.cover;
            if (o.forecast != null) document.getElementById('opt-forecast').value = o.forecast;
            if (o.day) document.getElementById('opt-day').value = o.day;
            if (o.unassigned) document.getElementById('opt-unassigned').value = o.unassigned;
            // cats/vendor filled later after options built
            applyOrderOpts._pending = o;
        }

        /**
         * Average daily usage from count session history.
         * For each consecutive pair of counts: if stock went down, usage = drop / days.
         */
        function buildDailyUsageMap() {
            var sessions = loadCountSessions().filter(function (s) {
                return s && s.counts && typeof s.counts === 'object' && s.at;
            }).sort(function (a, b) { return (a.at || 0) - (b.at || 0); });
            var sums = {}; // key -> { totalDaily, n }
            var i, j, key, a, b, ca, cb, days, drop, daily;
            for (i = 1; i < sessions.length; i++) {
                a = sessions[i - 1];
                b = sessions[i];
                days = (b.at - a.at) / (1000 * 60 * 60 * 24);
                if (!(days > 0.2)) continue; // ignore same-day double saves
                if (days > 45) continue; // too sparse
                Object.keys(b.counts).forEach(function (k) {
                    ca = parseFloat(a.counts[k]);
                    cb = parseFloat(b.counts[k]);
                    if (isNaN(ca) || isNaN(cb)) return;
                    drop = ca - cb;
                    if (drop <= 0) return; // received stock or no usage
                    daily = drop / days;
                    if (!sums[k]) sums[k] = { total: 0, n: 0 };
                    sums[k].total += daily;
                    sums[k].n += 1;
                });
            }
            var map = {};
            Object.keys(sums).forEach(function (k) {
                map[k] = Math.round((sums[k].total / sums[k].n) * 1000) / 1000;
            });
            return { map: map, sessionCount: sessions.length, pairCount: Object.keys(map).length };
        }

        function avgRecentNetSales(daysBack) {
            daysBack = daysBack || 28;
            var days = loadSalesDays();
            var cutoff = Date.now() - daysBack * 24 * 60 * 60 * 1000;
            var total = 0, n = 0;
            days.forEach(function (d) {
                if (!d || !d.date) return;
                var t = new Date(d.date + 'T12:00:00').getTime();
                if (isNaN(t) || t < cutoff) return;
                var net = parseFloat(d.net);
                if (isNaN(net)) net = parseFloat(d.gross);
                if (isNaN(net)) return;
                total += net;
                n++;
            });
            if (!n) return null;
            // average per day
            return { avgDay: total / n, days: n, total: total };
        }

        function getSalesScale() {
            var forecast = parseFloat(document.getElementById('opt-forecast').value);
            var hint = document.getElementById('sales-scale-hint');
            if (isNaN(forecast) || forecast <= 0) {
                if (hint) hint.textContent = isSweet
                    ? 'No forecast — usage unscaled. Enter $ to match a busier/slower week.'
                    : 'No forecast — usage unscaled.';
                return 1;
            }
            var sales = avgRecentNetSales(28);
            if (!sales || !sales.avgDay) {
                if (hint) hint.textContent = isSweet
                    ? 'Forecast set, but no Daily Sales yet — scale = 1×'
                    : 'Forecast set; no sales history — scale 1×';
                return 1;
            }
            // forecast is for "this period" — interpret as weekly if large, else daily
            // Compare forecast/7 to avg day if forecast looks weekly, else forecast as daily
            var avgWeek = sales.avgDay * 7;
            var scale;
            if (forecast > sales.avgDay * 2.5) {
                // treat as weekly forecast
                scale = forecast / avgWeek;
                if (hint) hint.textContent = isSweet
                    ? ('Scale ' + (Math.round(scale * 100) / 100) + '× · forecast week vs avg week ~' + money(avgWeek))
                    : ('Scale ' + (Math.round(scale * 100) / 100) + '× vs avg week ' + money(avgWeek));
            } else {
                scale = forecast / sales.avgDay;
                if (hint) hint.textContent = isSweet
                    ? ('Scale ' + (Math.round(scale * 100) / 100) + '× · forecast day vs avg day ~' + money(sales.avgDay))
                    : ('Scale ' + (Math.round(scale * 100) / 100) + '× vs avg day ' + money(sales.avgDay));
            }
            if (scale < 0.25) scale = 0.25;
            if (scale > 4) scale = 4;
            return Math.round(scale * 100) / 100;
        }

        var DAY_CODES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        function daysUntilNextDelivery(vendorName) {
            var meta = vendorMeta[vendorName];
            if (!meta || !meta.days || !meta.days.length) return null;
            var today = new Date().getDay(); // 0 Sun
            var codes = meta.days;
            var i, d, code;
            for (i = 1; i <= 7; i++) {
                d = (today + i) % 7;
                code = DAY_CODES[d];
                if (codes.indexOf(code) !== -1) return i;
            }
            // if delivers today and no other day, cover 7
            if (codes.indexOf(DAY_CODES[today]) !== -1) return 7;
            return null;
        }

        function coverDaysFor(item) {
            var raw = document.getElementById('opt-cover').value;
            if (raw !== 'auto') {
                var n = parseFloat(raw);
                return isNaN(n) || n < 1 ? 3 : n;
            }
            var vendor = String(item.vendor || '').trim();
            var d = daysUntilNextDelivery(vendor);
            return d != null ? d : 3;
        }

        function roundOrderQty(item, need) {
            if (need <= 0) return 0;
            if (parByOf(item) === 'case') {
                need = Math.ceil(need);
            } else {
                var pack = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
                if (!isNaN(pack) && pack > 0) {
                    need = Math.ceil(need / pack) * pack;
                }
            }
            return Math.round(need * 100) / 100;
        }

        /**
         * @returns {null|{ qty:number, reason:string, mode:string, dailyUsage:number|null, coverDays:number, parNeed:number|null, usageNeed:number|null }}
         */
        function calcNeed(item, key, usageMap, salesScale) {
            var onHand = parseFloat(item.onHand);
            var par = parseFloat(item.par);
            var mode = document.getElementById('opt-mode').value || 'max';
            var hasOnHand = !(item.onHand === '' || item.onHand == null || isNaN(onHand));
            var hasPar = !(item.par === '' || item.par == null || isNaN(par));
            if (!hasOnHand) return null;

            var parNeed = null;
            if (hasPar) parNeed = Math.max(0, par - onHand);

            var daily = usageMap && key && usageMap[key] != null ? usageMap[key] : null;
            var cover = coverDaysFor(item);
            var usageNeed = null;
            if (daily != null && daily > 0) {
                usageNeed = Math.max(0, daily * cover * (salesScale || 1) - onHand);
            }

            var raw = null;
            var reason = '';
            if (mode === 'par') {
                if (parNeed == null) return null;
                raw = parNeed;
                reason = isSweet ? 'to par' : 'to par';
            } else if (mode === 'usage') {
                if (usageNeed == null) {
                    // fall back to par if no usage history
                    if (parNeed == null) return null;
                    raw = parNeed;
                    reason = isSweet ? 'par (no usage hist.)' : 'par (no usage)';
                } else {
                    raw = usageNeed;
                    reason = isSweet
                        ? ('~' + (Math.round(daily * 100) / 100) + '/day × ' + cover + 'd')
                        : (daily + '/d × ' + cover + 'd');
                }
            } else {
                // max of both
                if (parNeed == null && usageNeed == null) return null;
                var p = parNeed != null ? parNeed : 0;
                var u = usageNeed != null ? usageNeed : 0;
                raw = Math.max(p, u);
                if (usageNeed != null && usageNeed >= (parNeed || 0) && usageNeed > 0) {
                    reason = isSweet
                        ? ('usage ~' + (Math.round(daily * 100) / 100) + '/d × ' + cover + 'd')
                        : ('usage ' + daily + '/d × ' + cover + 'd');
                } else {
                    reason = isSweet ? 'to par' : 'to par';
                }
            }

            var qty = roundOrderQty(item, raw);
            return {
                qty: qty,
                reason: reason,
                mode: mode,
                dailyUsage: daily,
                coverDays: cover,
                parNeed: parNeed != null ? roundOrderQty(item, parNeed) : null,
                usageNeed: usageNeed != null ? roundOrderQty(item, usageNeed) : null
            };
        }

        function dayCodeFromDate(d) { return DAY_CODES[d.getDay()]; }
        function resolveDeliveryDayFilter() {
            var v = document.getElementById('opt-day').value;
            if (!v) return '';
            if (v === 'today') return dayCodeFromDate(new Date());
            if (v === 'tomorrow') {
                var t = new Date();
                t.setDate(t.getDate() + 1);
                return dayCodeFromDate(t);
            }
            return v;
        }
        function vendorDeliversOn(vendorName, dayCode) {
            if (!dayCode) return true;
            if (vendorName === UNASSIGNED) return false; // handled separately
            var meta = vendorMeta[vendorName];
            if (!meta || !meta.days || !meta.days.length) return false; // no days set = not matching filter
            return meta.days.indexOf(dayCode) !== -1;
        }

        function catAllowed(cat, filter) {
            cat = normalizeCategory(cat);
            if (filter === 'all' || !filter) return true;
            if (filter === 'group_food') {
                return ['dairy', 'meats', 'frozen', 'canned_dry', 'produce', 'dispenser_beverage'].indexOf(cat) !== -1;
            }
            if (filter === 'group_nonfood' || filter === 'nonfood') {
                return ['paper_disposable', 'chemical_janitorial', 'supplies_equipment'].indexOf(cat) !== -1;
            }
            // legacy filters
            if (filter === 'food') return catAllowed(cat, 'group_food');
            if (filter === 'paper') return cat === 'paper_disposable';
            if (filter === 'janitorial') return cat === 'chemical_janitorial';
            return cat === filter;
        }

        // draft: { [vendorName]: [ { key, name, qty, unit, category, onHand, par, costPerUnit, packSize, sku } ] }
        var draft = {};
        var vendorMeta = {}; // name -> vendor record
        var savedState = loadSaved();

        function buildDraft() {
            var items = loadInventory();
            var vendors = loadVendors();
            vendorMeta = {};
            vendors.forEach(function (v) { vendorMeta[v.name] = v; });

            var catFilter = document.getElementById('opt-cats').value;
            var vendFilter = document.getElementById('opt-vendor').value;
            var dayCode = resolveDeliveryDayFilter();
            var hideUnassigned = document.getElementById('opt-unassigned').value === 'hide';
            var usagePack = buildDailyUsageMap();
            var salesScale = getSalesScale();
            draft = {};
            var missingVendor = 0;
            var missingPar = 0;
            var totalItems = 0;
            var skippedDay = 0;
            var skippedNoDays = 0;
            var usageLines = 0;

            Object.keys(items).forEach(function (key) {
                var item = items[key];
                totalItems++;
                if (!catAllowed(item.category, catFilter)) return;
                var needInfo = calcNeed(item, key, usagePack.map, salesScale);
                if (needInfo === null) {
                    if (item.par === '' || item.par == null || item.onHand === '' || item.onHand == null) missingPar++;
                    return;
                }
                if (needInfo.qty <= 0) return;
                if (needInfo.usageNeed != null && needInfo.parNeed != null && needInfo.usageNeed >= needInfo.parNeed && needInfo.usageNeed > 0) {
                    usageLines++;
                } else if (needInfo.usageNeed != null && needInfo.parNeed == null && needInfo.usageNeed > 0) {
                    usageLines++;
                } else if ((document.getElementById('opt-mode').value === 'usage') && needInfo.dailyUsage != null) {
                    usageLines++;
                }
                var need = needInfo.qty;
                var vendor = String(item.vendor || '').trim() || UNASSIGNED;
                if (vendor === UNASSIGNED) {
                    missingVendor++;
                    // unassigned has no delivery day — hide when filtering by day or when user opts out
                    if (hideUnassigned || dayCode) return;
                } else if (dayCode && !vendorDeliversOn(vendor, dayCode)) {
                    var meta = vendorMeta[vendor];
                    if (!meta || !meta.days || !meta.days.length) skippedNoDays++;
                    else skippedDay++;
                    return;
                }
                if (vendFilter && vendor !== vendFilter) return;
                if (!draft[vendor]) draft[vendor] = [];
                draft[vendor].push({
                    key: key,
                    name: item.name || key,
                    qty: need,
                    unit: item.unit || '',
                    orderBy: parByOf(item), // case | each — how order qty is measured
                    orderByLabel: parByWord(item, need),
                    category: normalizeCategory(item.category),
                    onHand: item.onHand,
                    par: item.par,
                    parBy: parByOf(item),
                    costPerUnit: item.costPerUnit,
                    casePrice: item.casePrice,
                    packSize: item.pack != null && item.pack !== '' ? item.pack : item.packSize,
                    pack: item.pack != null && item.pack !== '' ? item.pack : item.packSize,
                    size: item.size || '',
                    sku: item.sku || '',
                    reason: needInfo.reason || '',
                    dailyUsage: needInfo.dailyUsage,
                    coverDays: needInfo.coverDays,
                    parNeed: needInfo.parNeed,
                    usageNeed: needInfo.usageNeed
                });
            });

            // sort lines by distributor category order, then name
            Object.keys(draft).forEach(function (v) {
                draft[v].sort(function (a, b) {
                    var ca = catRank(a.category);
                    var cb = catRank(b.category);
                    if (ca !== cb) return ca - cb;
                    return a.name.localeCompare(b.name);
                });
            });

            var sel = document.getElementById('opt-vendor');
            var current = sel.value;
            var names = {};
            Object.keys(items).forEach(function (k) {
                var vn = String(items[k].vendor || '').trim();
                if (vn) names[vn] = true;
            });
            vendors.forEach(function (v) { if (v.name) names[v.name] = true; });
            var opts = '<option value="">' + (isSweet ? 'All vendors' : 'All vendors') + '</option>';
            Object.keys(names).sort().forEach(function (n) {
                var dayNote = '';
                if (vendorMeta[n] && vendorMeta[n].days && vendorMeta[n].days.length) {
                    dayNote = ' (' + vendorMeta[n].days.join(', ') + ')';
                }
                opts += '<option value="' + esc(n) + '"' + (current === n ? ' selected' : '') + '>' + esc(n + dayNote) + '</option>';
            });
            opts += '<option value="' + esc(UNASSIGNED) + '"' + (current === UNASSIGNED ? ' selected' : '') + '>' + esc(UNASSIGNED) + '</option>';
            sel.innerHTML = opts;

            var warn = document.getElementById('warn-box');
            var msgs = [];
            if (!totalItems) {
                msgs.push(isSweet
                    ? 'No products yet. Add them on <a href="/admin/product-setup">Product Setup</a> (or pull from recipes), then <a href="/admin/count">Count Stock</a>.'
                    : 'No products yet. Use <a href="/admin/product-setup">Product Setup</a>, then <a href="/admin/count">Count Stock</a>.');
            } else {
                if (missingVendor && !dayCode) msgs.push(isSweet
                    ? missingVendor + ' below-par item(s) have no vendor — assign distributors on <a href="/admin/product-setup">Product Setup</a> so orders split cleanly.'
                    : missingVendor + ' below-par item(s) have no vendor. Assign vendors on Product Setup.');
                if (missingPar) msgs.push(isSweet
                    ? missingPar + ' item(s) still need on-hand or par set (skipped). Finish <a href="/admin/count">Count Stock</a> or set par on Product Setup.'
                    : missingPar + ' item(s) missing on-hand or par (skipped). Use Count Stock / Product Setup.');
                if (dayCode) {
                    msgs.push(isSweet
                        ? 'Showing vendors that deliver on <strong>' + dayCode + '</strong>. Set delivery days under <a href="/admin/vendors">Vendors</a>.'
                        : 'Filtered to vendors delivering on <strong>' + dayCode + '</strong>. Set days on Vendors.');
                    if (skippedNoDays) msgs.push(isSweet
                        ? skippedNoDays + ' below-par line(s) skipped — vendor has no delivery days set.'
                        : skippedNoDays + ' line(s) skipped (vendor missing delivery days).');
                }
            }
            if (msgs.length) {
                warn.style.display = 'block';
                warn.innerHTML = msgs.join('<br>');
            } else {
                warn.style.display = 'none';
            }

            var dayHint = dayCode
                ? (isSweet ? ' Delivery filter: ' + dayCode + '.' : ' Delivery filter: ' + dayCode + '.')
                : '';
            var mode = document.getElementById('opt-mode').value;
            var modeLabel = mode === 'par'
                ? (isSweet ? 'par − on hand' : 'par − on hand')
                : (mode === 'usage'
                    ? (isSweet ? 'usage × cover days − on hand' : 'usage × cover − on hand')
                    : (isSweet ? 'max(par gap, usage × cover − on hand)' : 'max(par, usage cover)'));
            document.getElementById('gen-hint').textContent = (isSweet
                ? 'Suggest: ' + modeLabel + '. By-each rounds up to full packs when pack is set. Sales scale: ' + salesScale + '×.'
                : 'Suggest: ' + modeLabel + '. Pack rounding on. Sales scale: ' + salesScale + '×.') + dayHint;
            var uh = document.getElementById('usage-hint');
            if (uh) {
                if (usagePack.sessionCount < 2) {
                    uh.textContent = isSweet
                        ? 'Usage needs 2+ saved count sessions on Count Stock — right now only par is driving most lines.'
                        : 'Usage needs 2+ count sessions. Par drives most lines until then.';
                } else {
                    uh.textContent = isSweet
                        ? ('Usage from ' + usagePack.sessionCount + ' count sessions · ' + usagePack.pairCount + ' products with history · ' + usageLines + ' lines usage-led this pass')
                        : (usagePack.sessionCount + ' sessions · ' + usagePack.pairCount + ' products with usage · ' + usageLines + ' usage-led lines');
                }
            }
        }

        function allLines() {
            var lines = [];
            Object.keys(draft).sort(function (a, b) {
                if (a === UNASSIGNED) return 1;
                if (b === UNASSIGNED) return -1;
                return a.localeCompare(b);
            }).forEach(function (vendor) {
                draft[vendor].forEach(function (line) {
                    lines.push(Object.assign({ vendor: vendor }, line));
                });
            });
            return lines;
        }

        function renderStats() {
            var lines = allLines();
            var vendors = Object.keys(draft).filter(function (v) { return draft[v].length; });
            var est = 0, hasEst = false;
            lines.forEach(function (l) {
                var q = parseFloat(l.qty);
                if (isNaN(q)) return;
                var c;
                if (l.orderBy === 'case' && l.casePrice !== '' && l.casePrice != null) {
                    c = parseFloat(l.casePrice);
                } else {
                    c = parseFloat(l.costPerUnit);
                }
                if (!isNaN(c)) {
                    est += c * q;
                    hasEst = true;
                }
            });
            document.getElementById('stat-lines').textContent = lines.length;
            document.getElementById('stat-vendors').textContent = vendors.length;
            document.getElementById('stat-est').textContent = hasEst ? money(est) : '—';
        }

        function minutesUntilCutoff(cutoffTime) {
            if (!cutoffTime) return null;
            var parts = String(cutoffTime).split(':');
            var h = parseInt(parts[0], 10);
            var m = parseInt(parts[1] || '0', 10);
            if (isNaN(h)) return null;
            var now = new Date();
            var cut = new Date(now.getFullYear(), now.getMonth(), now.getDate(), h, m, 0, 0);
            return Math.round((cut - now) / 60000);
        }
        function formatCutoff(cutoffTime) {
            var parts = String(cutoffTime).split(':');
            var h = parseInt(parts[0], 10);
            var m = parts[1] || '00';
            if (isNaN(h)) return cutoffTime;
            var ap = h >= 12 ? 'PM' : 'AM';
            return (h % 12 || 12) + ':' + m + ' ' + ap;
        }
        function renderCutoffBanner() {
            var el = document.getElementById('cutoff-banner');
            var lines = [];
            Object.keys(draft || {}).forEach(function (vendor) {
                if (!draft[vendor] || !draft[vendor].length) return;
                var meta = vendorMeta[vendor];
                if (!meta || !meta.cutoffTime) return;
                var mins = minutesUntilCutoff(meta.cutoffTime);
                if (mins == null) return;
                var alertBefore = meta.alertBefore;
                var windowMin = 120; // default show within 2h if no alert set
                if (alertBefore === 'off') return;
                if (alertBefore === 'custom' && meta.alertCustomMinutes != null) {
                    windowMin = parseInt(meta.alertCustomMinutes, 10) || 120;
                } else if (alertBefore != null && alertBefore !== '' && !isNaN(parseInt(alertBefore, 10))) {
                    windowMin = parseInt(alertBefore, 10);
                }
                // also always surface if past cutoff or within 3 hours for vendors on today's order
                if (mins > Math.max(windowMin, 180)) return;
                var status;
                if (mins < 0) status = isSweet ? 'CUT-OFF PASSED' : 'CUT-OFF PASSED';
                else if (mins <= 30) status = isSweet ? mins + ' min left' : mins + ' min left';
                else status = isSweet ? Math.round(mins / 60 * 10) / 10 + 'h left' : Math.round(mins / 60 * 10) / 10 + 'h left';
                lines.push({
                    vendor: vendor,
                    text: vendor + ' · ' + formatCutoff(meta.cutoffTime) + ' · ' + status,
                    urgent: mins <= 30
                });
            });
            if (!lines.length) {
                el.className = 'cutoff-banner';
                el.innerHTML = '';
                return;
            }
            var urgent = lines.some(function (l) { return l.urgent; });
            el.className = 'cutoff-banner show ' + (urgent ? 'urgent' : 'soon');
            el.innerHTML = (isSweet ? '⏰ Order cut-off alert: ' : 'Cutoff alert: ') +
                lines.map(function (l) { return esc(l.text); }).join(' · ') +
                ' · <a href="/admin/vendors">' + (isSweet ? 'Vendors' : 'Vendors') + '</a>';
        }

        function renderOrder() {
            renderStats();
            renderCutoffBanner();
            var root = document.getElementById('order-root');
            var vendors = Object.keys(draft).sort(function (a, b) {
                if (a === UNASSIGNED) return 1;
                if (b === UNASSIGNED) return -1;
                return a.localeCompare(b);
            }).filter(function (v) { return draft[v].length; });

            if (!vendors.length) {
                var dayCode = resolveDeliveryDayFilter();
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? (dayCode
                        ? 'Nothing to order for <strong>' + esc(dayCode) + '</strong> delivery — try All days, or check vendor delivery days 💕'
                        : 'Nothing to order right now — everything is at par, or you still need a count 💕')
                    : (dayCode
                        ? 'Nothing to order for ' + esc(dayCode) + ' delivery. Try All days or set vendor days.'
                        : 'Nothing to order right now. All items at par, or stock still needs counting.')) +
                    '<div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:10px;justify-content:center;">' +
                    '<a class="btn btn-primary" href="/admin/count">' + (isSweet ? 'Count Stock' : 'Count Stock') + '</a>' +
                    '<a class="btn btn-secondary" href="/admin/product-setup">' + (isSweet ? 'Product Setup' : 'Product Setup') + '</a>' +
                    '</div></div>';
                return;
            }

            root.innerHTML = vendors.map(function (vendor) {
                var lines = draft[vendor];
                var meta = vendorMeta[vendor];
                var metaBits = [lines.length + (isSweet ? ' items' : ' items')];
                if (meta) {
                    if (meta.contact) metaBits.push(meta.contact);
                    if (meta.phone) metaBits.push(meta.phone);
                    if (meta.email) metaBits.push(meta.email);
                    if (meta.accountNumber) metaBits.push((isSweet ? 'Acct ' : 'Acct ') + meta.accountNumber);
                    if (meta.days && meta.days.length) metaBits.push((isSweet ? 'Delivers: ' : 'Delivers: ') + meta.days.join(', '));
                    if (meta.cutoffTime) {
                        var cp = String(meta.cutoffTime).split(':');
                        var ch = parseInt(cp[0], 10), cm = cp[1] || '00';
                        if (!isNaN(ch)) {
                            var ap = ch >= 12 ? 'PM' : 'AM';
                            var h12 = ch % 12 || 12;
                            metaBits.push((isSweet ? 'Cut-off ' : 'Cut-off ') + h12 + ':' + cm + ' ' + ap);
                        } else metaBits.push((isSweet ? 'Cut-off ' : 'Cut-off ') + meta.cutoffTime);
                    }
                    if (meta.caseMin !== '' && meta.caseMin != null && !isNaN(parseFloat(meta.caseMin))) {
                        metaBits.push((isSweet ? 'Min ' : 'Min ') + meta.caseMin + (isSweet ? ' cases' : ' cases'));
                    }
                }
                var rows = lines.map(function (line, idx) {
                    var byLabel = line.orderByLabel || (line.orderBy === 'case' ? 'cases' : 'each');
                    var parLabel = (line.par != null && line.par !== '' ? line.par : '—') + ' ' +
                        (line.parBy === 'case' ? (parseFloat(line.par) === 1 ? 'case' : 'cases') : 'each');
                    var ohLabel = (line.onHand != null && line.onHand !== '' ? line.onHand : '—') + ' ' +
                        (line.parBy === 'case' ? (parseFloat(line.onHand) === 1 ? 'case' : 'cases') : 'each');
                    var why = line.reason
                        ? (' · ' + (isSweet ? 'why: ' : 'why: ') + line.reason)
                        : '';
                    return '<div class="line" data-vendor="' + esc(vendor) + '" data-idx="' + idx + '">' +
                        '<div class="name"><strong>' + esc(line.name) + '</strong>' +
                            '<div class="sub">' + esc(catLabel(normalizeCategory(line.category))) +
                            (line.sku ? ' · #' + esc(line.sku) : '') +
                            ' · ' + (isSweet ? 'on hand ' : 'on hand ') + esc(ohLabel) +
                            ' / par ' + esc(parLabel) +
                            (line.pack || line.packSize ? ' · pack ' + esc(line.pack || line.packSize) + '/case' : '') +
                            esc(why) +
                            '</div></div>' +
                        '<div><label>' + (isSweet ? 'Order qty' : 'Order qty') + '</label>' +
                            '<input type="number" step="any" min="0" class="f-qty" value="' + esc(line.qty) + '"></div>' +
                        '<div><label>' + (isSweet ? 'Order as' : 'Order as') + '</label>' +
                            '<input type="text" class="f-order-by" value="' + esc(byLabel) + '" readonly style="opacity:0.9;"></div>' +
                        '<button type="button" class="btn btn-small btn-danger" data-rm>' + (isSweet ? '×' : '×') + '</button>' +
                        '</div>';
                }).join('');

                var emailBtn = (meta && meta.email)
                    ? '<button type="button" class="btn btn-small btn-primary" data-email-vendor="' + esc(vendor) + '">' + (isSweet ? 'Email draft' : 'Email') + '</button>'
                    : '<button type="button" class="btn btn-small btn-ghost" data-email-vendor="' + esc(vendor) + '">' + (isSweet ? 'Email (no address)' : 'Email') + '</button>';
                var hasPhone = meta && meta.phone && String(meta.phone).replace(/\D/g, '').length >= 7;
                var textBtn = hasPhone
                    ? '<button type="button" class="btn btn-small btn-primary" data-text-vendor="' + esc(vendor) + '">' + (isSweet ? 'Text sales rep 📱' : 'Text sales rep') + '</button>'
                    : '<button type="button" class="btn btn-small btn-ghost" data-text-vendor="' + esc(vendor) + '">' + (isSweet ? 'Text (no number)' : 'Text') + '</button>';

                var site = meta && meta.website ? String(meta.website).trim() : '';
                var orderPortal = meta && meta.orderUrl ? String(meta.orderUrl).trim() : '';
                if (site && !/^https?:\/\//i.test(site)) site = 'https://' + site;
                if (orderPortal && !/^https?:\/\//i.test(orderPortal)) orderPortal = 'https://' + orderPortal;
                // fallback known distributors if no website saved yet
                if (!site && vendor && vendor !== UNASSIGNED) {
                    var known = {
                        'sysco': 'https://www.sysco.com',
                        'us foods': 'https://www.usfoods.com',
                        'usfoods': 'https://www.usfoods.com',
                        'performance': 'https://www.pfgc.com',
                        'pfg': 'https://www.pfgc.com',
                        'gordon': 'https://www.gfs.com',
                        'gfs': 'https://www.gfs.com',
                        'restaurant depot': 'https://www.restaurantdepot.com',
                        'shamrock': 'https://www.shamrockfoods.com',
                        'ben e. keith': 'https://www.benekeith.com',
                        'ben e keith': 'https://www.benekeith.com',
                        'cheney': 'https://www.cheneybrothers.com',
                        'dot foods': 'https://www.dotfoods.com',
                        'kehe': 'https://www.kehe.com',
                        'unfi': 'https://www.unfi.com'
                    };
                    var vn = vendor.toLowerCase();
                    Object.keys(known).forEach(function (k) {
                        if (!site && vn.indexOf(k) !== -1) site = known[k];
                    });
                    if (vn.indexOf('sysco') !== -1 && !orderPortal) orderPortal = 'https://shop.sysco.com';
                    if ((vn.indexOf('gordon') !== -1 || vn.indexOf('gfs') !== -1) && !orderPortal) {
                        orderPortal = 'https://www.gfsmarketplace.com';
                    }
                }
                var siteBtn = site
                    ? '<a class="btn btn-small btn-ghost" href="' + esc(site) + '" target="_blank" rel="noopener noreferrer">' + (isSweet ? 'Open site 🌐' : 'Open site') + '</a>'
                    : '';
                var orderBtn = orderPortal
                    ? '<a class="btn btn-small btn-primary" href="' + esc(orderPortal) + '" target="_blank" rel="noopener noreferrer">' + (isSweet ? 'Order portal 🛒' : 'Order portal') + '</a>'
                    : '';

                return '<div class="card vendor-block" data-vendor-block="' + esc(vendor) + '">' +
                    '<h2>' + esc(vendor) + '</h2>' +
                    '<div class="vendor-meta">' + esc(metaBits.join(' · ')) +
                        (site ? ' · <a href="' + esc(site) + '" target="_blank" rel="noopener noreferrer" style="color:inherit;font-weight:600;">' + (isSweet ? 'website' : 'website') + '</a>' : '') +
                    '</div>' +
                    rows +
                    '<div class="vendor-actions">' +
                        siteBtn + orderBtn +
                        '<button type="button" class="btn btn-small btn-ghost" data-export-vendor="' + esc(vendor) + '">' + (isSweet ? 'Vendor CSV' : 'Vendor CSV') + '</button>' +
                        '<button type="button" class="btn btn-small btn-ghost" data-print-vendor="' + esc(vendor) + '">' + (isSweet ? 'Print / PDF' : 'Print / PDF') + '</button>' +
                        '<button type="button" class="btn btn-small btn-ghost" data-copy-vendor="' + esc(vendor) + '">' + (isSweet ? 'Copy' : 'Copy') + '</button>' +
                        emailBtn +
                        textBtn +
                    '</div></div>';
            }).join('');
        }

        function collectEdits() {
            document.querySelectorAll('.line').forEach(function (el) {
                var vendor = el.dataset.vendor;
                var idx = parseInt(el.dataset.idx, 10);
                if (!draft[vendor] || !draft[vendor][idx]) return;
                draft[vendor][idx].qty = el.querySelector('.f-qty').value;
                // order-as is locked to parBy (case/each); keep label in sync with qty
                var q = draft[vendor][idx].qty;
                draft[vendor][idx].orderByLabel = draft[vendor][idx].orderBy === 'case'
                    ? (parseFloat(q) === 1 ? 'case' : 'cases')
                    : 'each';
            });
        }

        function linesToCsv(lines) {
            var header = ['Vendor', 'Item', 'SKU', 'Category', 'OrderQty', 'OrderAs', 'OnHand', 'Par', 'ParBy', 'PackPerCase', 'Size', 'MeasureUnit', 'CasePrice', 'EstLine'];
            var rows = [header.join(',')];
            lines.forEach(function (l) {
                var qty = parseFloat(l.qty);
                var est = '';
                if (!isNaN(qty)) {
                    if (l.orderBy === 'case' && l.casePrice !== '' && l.casePrice != null && !isNaN(parseFloat(l.casePrice))) {
                        est = Math.round(parseFloat(l.casePrice) * qty * 100) / 100;
                    } else if (l.costPerUnit !== '' && l.costPerUnit != null && !isNaN(parseFloat(l.costPerUnit))) {
                        est = Math.round(parseFloat(l.costPerUnit) * qty * 100) / 100;
                    }
                }
                var cells = [
                    l.vendor, l.name, l.sku || '', catLabel(normalizeCategory(l.category)) || l.category || '',
                    l.qty, l.orderByLabel || l.orderBy || 'each',
                    l.onHand, l.par, l.parBy || 'each',
                    l.pack || l.packSize || '', l.size || '', l.unit || '',
                    l.casePrice || '', est
                ].map(function (c) {
                    var s = String(c == null ? '' : c);
                    if (/[",\n]/.test(s)) s = '"' + s.replace(/"/g, '""') + '"';
                    return s;
                });
                rows.push(cells.join(','));
            });
            return rows.join('\n');
        }

        function orderUnitCanon(line) {
            var raw = String((line && (line.orderBy || line.parBy || line.unit)) || 'each').toLowerCase();
            if (raw.indexOf('case') === 0) return 'case';
            return 'each';
        }

        /** Clean Sysco-style sheet: case|each units, grouped by vendor in CSV rows. */
        function linesToVendorCsv(lines) {
            var header = ['Vendor', 'AccountNumber', 'Item', 'SKU', 'OrderQty', 'OrderUnit', 'OnHand', 'Par'];
            var rows = [header.join(',')];
            var sorted = (lines || []).slice().sort(function (a, b) {
                var va = String(a.vendor || '').localeCompare(String(b.vendor || ''));
                if (va) return va;
                return String(a.name || '').localeCompare(String(b.name || ''));
            });
            sorted.forEach(function (l) {
                var meta = vendorMeta[l.vendor] || {};
                var cells = [
                    l.vendor || '',
                    meta.accountNumber || '',
                    l.name || '',
                    l.sku || '',
                    l.qty,
                    orderUnitCanon(l),
                    l.onHand != null && l.onHand !== '' ? l.onHand : '',
                    l.par != null && l.par !== '' ? l.par : ''
                ].map(function (c) {
                    var s = String(c == null ? '' : c);
                    if (/[",\n]/.test(s)) s = '"' + s.replace(/"/g, '""') + '"';
                    return s;
                });
                rows.push(cells.join(','));
            });
            return '\uFEFF' + rows.join('\r\n');
        }

        function buildVendorPrintHtml(lines, onlyVendor) {
            var date = new Date().toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
            var byVendor = {};
            (lines || []).forEach(function (l) {
                var v = l.vendor || UNASSIGNED;
                if (onlyVendor && v !== onlyVendor) return;
                if (!byVendor[v]) byVendor[v] = [];
                byVendor[v].push(l);
            });
            var keys = Object.keys(byVendor).sort();
            if (!keys.length) {
                return '<p>' + (isSweet ? 'Nothing to print' : 'Nothing to print') + '</p>';
            }
            var title = isSweet ? 'Vendor order guide' : 'Vendor order guide';
            var html = '<div class="vendor-sheet-meta">' + esc(title) + ' · ' + esc(date) + ' · ilovepbj ops</div>';
            keys.forEach(function (vendor) {
                var meta = vendorMeta[vendor] || {};
                var bits = [];
                if (meta.accountNumber) bits.push('Account #: ' + meta.accountNumber);
                if (meta.contact) bits.push(meta.contact);
                if (meta.phone) bits.push(meta.phone);
                if (meta.email) bits.push(meta.email);
                var rows = byVendor[vendor].map(function (l) {
                    return '<tr>' +
                        '<td>' + esc(l.name) + '</td>' +
                        '<td>' + esc(l.sku || '') + '</td>' +
                        '<td class="num">' + esc(l.qty) + '</td>' +
                        '<td>' + esc(orderUnitCanon(l)) + '</td>' +
                        '<td class="num">' + esc(l.onHand != null && l.onHand !== '' ? l.onHand : '') + '</td>' +
                        '<td class="num">' + esc(l.par != null && l.par !== '' ? l.par : '') + '</td>' +
                    '</tr>';
                }).join('');
                html += '<div class="vendor-sheet-block">' +
                    '<div class="vendor-sheet-title">' + esc(vendor) + '</div>' +
                    (bits.length ? '<div class="vendor-sheet-meta">' + esc(bits.join(' · ')) + '</div>' : '') +
                    '<table class="vendor-sheet"><thead><tr>' +
                    '<th>Item</th><th>SKU</th><th>Qty</th><th>Order unit</th><th>On hand</th><th>Par</th>' +
                    '</tr></thead><tbody>' + rows + '</tbody></table></div>';
            });
            return html;
        }

        function prepareVendorPrint(lines, onlyVendor) {
            var root = document.getElementById('vendor-print-root');
            if (!root) return;
            root.innerHTML = buildVendorPrintHtml(lines, onlyVendor || null);
        }

        function downloadCsv(filename, csv) {
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function linesToText(lines) {
            var date = new Date().toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
            var dayCode = resolveDeliveryDayFilter();
            var out = [];
            out.push(isSweet ? 'ilovepbj ops — Order list' : 'ilovepbj ops — Order list');
            out.push(date + (dayCode ? ' · Delivery: ' + dayCode : ''));
            out.push('');
            var byVendor = {};
            lines.forEach(function (l) {
                if (!byVendor[l.vendor]) byVendor[l.vendor] = [];
                byVendor[l.vendor].push(l);
            });
            Object.keys(byVendor).sort().forEach(function (v) {
                out.push('=== ' + v + ' ===');
                var meta = vendorMeta[v];
                if (meta) {
                    var bits = [meta.contact, meta.phone, meta.email].filter(Boolean);
                    if (meta.accountNumber) bits.push('Account #: ' + meta.accountNumber);
                    if (meta.days && meta.days.length) bits.push('Delivers: ' + meta.days.join(', '));
                    if (bits.length) out.push(bits.join(' · '));
                }
                byVendor[v].forEach(function (l) {
                    var sku = l.sku ? ' [' + l.sku + ']' : '';
                    var as = l.orderByLabel || (l.orderBy === 'case' ? 'cases' : 'each');
                    out.push('- ' + l.name + sku + ': ' + l.qty + ' ' + as +
                        (l.category ? ' (' + catLabel(normalizeCategory(l.category)) + ')' : ''));
                });
                out.push('');
            });
            return out.join('\n');
        }

        function emailSubject(vendor) {
            var date = new Date().toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            return (isSweet ? 'Order request' : 'Order request') + (vendor ? ' — ' + vendor : '') + ' — ' + date;
        }
        function openMailto(to, subject, body) {
            // mailto has length limits; if huge, copy + open empty compose
            var max = 1800;
            var useBody = body;
            if (body.length > max) {
                copyText(body).then(function () {
                    toast(isSweet ? 'Order copied — paste into email 📋' : 'Order copied — paste into email');
                }).catch(function () {});
                useBody = (isSweet
                    ? '(Order list was copied to your clipboard — paste below. Too long for automatic fill.)\n\n'
                    : '(Order list copied to clipboard — paste below.)\n\n') + body.slice(0, 400) + '\n…';
            }
            var url = 'mailto:' + encodeURIComponent(to || '') +
                '?subject=' + encodeURIComponent(subject) +
                '&body=' + encodeURIComponent(useBody);
            window.location.href = url;
        }
        function emailVendor(vendor) {
            collectEdits();
            var lines = (draft[vendor] || []).map(function (l) { return Object.assign({ vendor: vendor }, l); });
            if (!lines.length) return;
            var meta = vendorMeta[vendor];
            var to = (meta && meta.email) ? meta.email : '';
            // email field may be "email / account #" — pull first email-like token
            if (to && to.indexOf('@') === -1) to = '';
            else if (to) {
                var m = to.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
                to = m ? m[0] : (to.indexOf('@') !== -1 ? to.split(/[\s,;]+/)[0] : '');
            }
            var body = linesToText(lines);
            if (!to) {
                copyText(body).then(function () {
                    toast(isSweet ? 'No email on file — order copied 📋' : 'No email — order copied');
                });
                openMailto('', emailSubject(vendor), body);
                return;
            }
            openMailto(to, emailSubject(vendor), body);
            toast(isSweet ? 'Opening email draft 💌' : 'Opening email draft');
        }

        /** Normalize vendor phone for sms: links (keep leading + for country code). */
        function phoneForSms(raw) {
            var s = String(raw || '').trim();
            if (!s) return '';
            // Prefer first phone-like chunk if free text mixed in
            var m = s.match(/(\+?\d[\d\s().\-]{6,}\d)/);
            if (m) s = m[1];
            var hasPlus = s.charAt(0) === '+';
            var digits = s.replace(/\D/g, '');
            if (digits.length < 7) return '';
            return hasPlus ? ('+' + digits) : digits;
        }
        function openSms(phone, body) {
            // iOS: sms:number&body=   Android: sms:number?body=
            var max = 1200;
            var useBody = body;
            if (body.length > max) {
                copyText(body).then(function () {
                    toast(isSweet ? 'Order copied — paste into the text 📋' : 'Order copied — paste into text');
                }).catch(function () {});
                useBody = (isSweet
                    ? '(Full order was copied — paste below. Too long for auto-fill.)\n\n'
                    : '(Full order copied — paste below.)\n\n') + body.slice(0, 280) + '\n…';
            }
            var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            var url;
            if (phone) {
                url = isIOS
                    ? ('sms:' + phone + '&body=' + encodeURIComponent(useBody))
                    : ('sms:' + phone + '?body=' + encodeURIComponent(useBody));
            } else {
                // No number — open blank compose; body support varies without a recipient
                url = isIOS
                    ? ('sms:&body=' + encodeURIComponent(useBody))
                    : ('sms:?body=' + encodeURIComponent(useBody));
            }
            window.location.href = url;
        }
        function textVendor(vendor) {
            collectEdits();
            var lines = (draft[vendor] || []).map(function (l) { return Object.assign({ vendor: vendor }, l); });
            if (!lines.length) return;
            var meta = vendorMeta[vendor];
            var phone = phoneForSms(meta && meta.phone);
            var body = linesToText(lines);
            if (!phone) {
                copyText(body).then(function () {
                    toast(isSweet
                        ? 'No phone on file — order copied. Add sales rep # under Vendors 📱'
                        : 'No phone — order copied. Add number on Vendors.');
                }).catch(function () {});
                openSms('', body);
                return;
            }
            openSms(phone, body);
            toast(isSweet ? 'Opening text to sales rep 📱' : 'Opening text message');
        }

        function copyText(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text);
            }
            return new Promise(function (resolve, reject) {
                var ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    resolve();
                } catch (e) { reject(e); }
                document.body.removeChild(ta);
            });
        }

        function renderSaved() {
            var root = document.getElementById('saved-list');
            if (!savedState.orders.length) {
                root.innerHTML = '<p class="hint">' + (isSweet ? 'No saved orders yet.' : 'No saved orders yet.') + '</p>';
                return;
            }
            root.innerHTML = savedState.orders.slice().reverse().map(function (o) {
                var when = new Date(o.at).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
                return '<div class="saved-order">' +
                    '<h3>' + esc(o.label || (isSweet ? 'Order' : 'Order')) + '</h3>' +
                    '<div class="vendor-meta">' + esc(when) + ' · ' + (o.lines || []).length + (isSweet ? ' lines' : ' lines') +
                    ' · ' + (o.vendorCount || 0) + (isSweet ? ' vendors' : ' vendors') + '</div>' +
                    '<button type="button" class="btn btn-small btn-ghost" data-export-saved="' + esc(o.id) + '">' + (isSweet ? 'Vendor CSV' : 'Vendor CSV') + '</button> ' +
                    '<button type="button" class="btn btn-small btn-danger" data-del-saved="' + esc(o.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div>';
            }).join('');
        }

        var optsRestored = false;
        function refresh() {
            loadCatState();
            fillCatFilter();
            var pend = applyOrderOpts._pending;
            if (!optsRestored && pend) {
                if (pend.cats) {
                    var cs = document.getElementById('opt-cats');
                    if ([].some.call(cs.options, function (o) { return o.value === pend.cats; })) cs.value = pend.cats;
                }
            }
            buildDraft();
            if (!optsRestored && pend && pend.vendor) {
                var vs = document.getElementById('opt-vendor');
                if (vs && [].some.call(vs.options, function (o) { return o.value === pend.vendor; })) {
                    vs.value = pend.vendor;
                    buildDraft(); // re-filter with restored vendor
                }
                optsRestored = true;
            } else {
                optsRestored = true;
            }
            renderOrder();
            renderSaved();
            saveOrderOpts();
        }

        applyOrderOpts();

        document.getElementById('regen-btn').addEventListener('click', function () {
            refresh();
            toast(isSweet ? 'Refreshed from inventory ✨' : 'Refreshed');
        });
        document.getElementById('opt-cats').addEventListener('change', refresh);
        document.getElementById('opt-vendor').addEventListener('change', refresh);
        document.getElementById('opt-day').addEventListener('change', refresh);
        document.getElementById('opt-unassigned').addEventListener('change', refresh);
        document.getElementById('opt-mode').addEventListener('change', refresh);
        document.getElementById('opt-cover').addEventListener('change', refresh);
        document.getElementById('opt-forecast').addEventListener('change', refresh);
        document.getElementById('opt-forecast').addEventListener('input', function () {
            // live scale hint without full rebuild spam
            getSalesScale();
        });

        document.getElementById('email-all-btn').addEventListener('click', function () {
            collectEdits();
            var lines = allLines();
            if (!lines.length) {
                alert(isSweet ? 'Nothing to email yet' : 'Nothing to email yet');
                return;
            }
            // One combined draft (user can split per vendor with Email on each card)
            openMailto('', emailSubject(''), linesToText(lines));
            toast(isSweet ? 'Opening combined email draft 💌' : 'Opening email draft');
        });

        document.getElementById('export-vendor-btn').addEventListener('click', function () {
            collectEdits();
            var lines = allLines();
            if (!lines.length) { alert(isSweet ? 'Nothing to export' : 'Nothing to export'); return; }
            var stamp = new Date().toISOString().slice(0, 10);
            downloadCsv('pbj-vendor-order-' + stamp + '.csv', linesToVendorCsv(lines));
            toast(isSweet ? 'Vendor CSV ready — forward to Sysco-style vendors 📥' : 'Vendor CSV downloaded');
        });

        document.getElementById('export-all-btn').addEventListener('click', function () {
            collectEdits();
            var lines = allLines();
            if (!lines.length) { alert(isSweet ? 'Nothing to export' : 'Nothing to export'); return; }
            var stamp = new Date().toISOString().slice(0, 10);
            downloadCsv('pbj-order-full-' + stamp + '.csv', linesToCsv(lines));
            toast(isSweet ? 'Full CSV downloaded 📥' : 'CSV downloaded');
        });

        document.getElementById('print-btn').addEventListener('click', function () {
            collectEdits();
            var lines = allLines();
            if (!lines.length) { alert(isSweet ? 'Nothing to print' : 'Nothing to print'); return; }
            prepareVendorPrint(lines, null);
            window.print();
        });

        document.getElementById('copy-all-btn').addEventListener('click', function () {
            collectEdits();
            var lines = allLines();
            if (!lines.length) { alert(isSweet ? 'Nothing to copy' : 'Nothing to copy'); return; }
            copyText(linesToText(lines)).then(function () {
                toast(isSweet ? 'Copied to clipboard 📋' : 'Copied');
            }).catch(function () {
                alert(isSweet ? 'Could not copy — try Export CSV' : 'Could not copy');
            });
        });

        document.getElementById('save-order-btn').addEventListener('click', function () {
            collectEdits();
            var lines = allLines();
            if (!lines.length) {
                alert(isSweet ? 'Nothing to save yet' : 'Nothing to save yet');
                return;
            }
            var vendors = {};
            lines.forEach(function (l) { vendors[l.vendor] = true; });
            savedState.orders.push({
                id: uid(),
                label: (isSweet ? 'Order ' : 'Order ') + new Date().toLocaleDateString(),
                at: Date.now(),
                lines: lines,
                vendorCount: Object.keys(vendors).length
            });
            // keep last 30
            if (savedState.orders.length > 30) savedState.orders = savedState.orders.slice(-30);
            saveSaved(savedState);
            renderSaved();
            toast(isSweet ? 'Order saved 💾' : 'Order saved');
        });

        document.getElementById('order-root').addEventListener('click', function (e) {
            var rm = e.target.closest('[data-rm]');
            if (rm) {
                var row = rm.closest('.line');
                var vendor = row.dataset.vendor;
                var idx = parseInt(row.dataset.idx, 10);
                collectEdits();
                draft[vendor].splice(idx, 1);
                if (!draft[vendor].length) delete draft[vendor];
                renderOrder();
                return;
            }
            var exp = e.target.closest('[data-export-vendor]');
            if (exp) {
                collectEdits();
                var v = exp.dataset.exportVendor;
                var lines = (draft[v] || []).map(function (l) { return Object.assign({ vendor: v }, l); });
                if (!lines.length) return;
                var safe = v.replace(/[^\w\-]+/g, '-').slice(0, 40);
                downloadCsv('pbj-vendor-order-' + safe + '.csv', linesToVendorCsv(lines));
                toast(isSweet ? 'Vendor CSV ready 📥' : 'CSV ready');
                return;
            }
            var prv = e.target.closest('[data-print-vendor]');
            if (prv) {
                collectEdits();
                var pv = prv.dataset.printVendor;
                var plines = (draft[pv] || []).map(function (l) { return Object.assign({ vendor: pv }, l); });
                if (!plines.length) return;
                prepareVendorPrint(plines, pv);
                window.print();
                return;
            }
            var cp = e.target.closest('[data-copy-vendor]');
            if (cp) {
                collectEdits();
                var v2 = cp.dataset.copyVendor;
                var lines2 = (draft[v2] || []).map(function (l) { return Object.assign({ vendor: v2 }, l); });
                copyText(linesToText(lines2)).then(function () {
                    toast(isSweet ? 'Copied ' + v2 + ' 📋' : 'Copied');
                });
                return;
            }
            var em = e.target.closest('[data-email-vendor]');
            if (em) {
                emailVendor(em.dataset.emailVendor);
                return;
            }
            var tx = e.target.closest('[data-text-vendor]');
            if (tx) {
                textVendor(tx.dataset.textVendor);
            }
        });

        document.getElementById('order-root').addEventListener('change', function (e) {
            if (e.target.matches('.f-qty, .f-unit')) {
                collectEdits();
                renderStats();
            }
        });

        document.getElementById('saved-list').addEventListener('click', function (e) {
            var del = e.target.closest('[data-del-saved]');
            if (del) {
                savedState.orders = savedState.orders.filter(function (o) { return o.id !== del.dataset.delSaved; });
                saveSaved(savedState);
                renderSaved();
                toast(isSweet ? 'Removed' : 'Removed');
                return;
            }
            var exp = e.target.closest('[data-export-saved]');
            if (exp) {
                var order = savedState.orders.find(function (o) { return o.id === exp.dataset.exportSaved; });
                if (!order) return;
                downloadCsv('pbj-vendor-order-saved-' + order.id + '.csv', linesToVendorCsv(order.lines || []));
                toast(isSweet ? 'Vendor CSV downloaded 📥' : 'CSV downloaded');
            }
        });

        refresh();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyInvPerms);
            document.addEventListener('pbj-perms-ready', applyInvPerms);
    })();
    </script>
</body>
</html>
