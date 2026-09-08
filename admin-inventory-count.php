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
    <title><?php echo $is_sweet ? 'Count Stock' : 'Count Stock'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .content { padding: 24px 16px; max-width: 860px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.35rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.8rem; opacity: 0.7; margin-top: 4px; }
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
        .search { width: 100%; box-sizing: border-box; border-radius: 14px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 12px 14px; font-size: 1rem; margin-bottom: 14px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .item { background: white; border-radius: 18px; padding: 14px 16px; margin-bottom: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; display: grid; grid-template-columns: 1fr 110px; gap: 12px; align-items: center; }
        @media (max-width: 520px) { .item { grid-template-columns: 1fr; } }
        .item.low { border-left-color: #E57373; }
        .item.ok { border-left-color: #7BC67E; }
        .item-name { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.2rem; margin: 0 0 4px; }
        .meta { font-size: 0.88rem; opacity: 0.75; line-height: 1.35; }
        .need { font-size: 0.9rem; margin-top: 4px; }
        .need.order { color: #C62828; font-weight: 600; }
        .field label { display: block; font-size: 0.8rem; opacity: 0.65; margin-bottom: 4px; }
        .field input { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 12px 10px; font-size: 1.15rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 12px; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .hint { font-size: 0.92rem; opacity: 0.7; margin: 0 0 10px; line-height: 1.4; }
        .session { border-radius: 14px; padding: 12px 14px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .session h3 { margin: 0 0 4px; font-size: 1.05rem; }
        .session .meta { margin-bottom: 8px; }
        .session-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .empty { text-align: center; padding: 36px 20px; background: white; border-radius: 18px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .actions-bar { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .cat-head { font-size: 1rem; opacity: 0.7; margin: 18px 0 8px; padding-left: 4px; }
        .flow-steps { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .flow-step { flex: 1; min-width: 100px; text-align: center; padding: 10px 8px; border-radius: 14px; font-size: 0.82rem; line-height: 1.25; text-decoration: none; color: inherit; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.06); opacity: 0.75; }
        .flow-step strong { display: block; font-size: 0.72rem; opacity: 0.65; margin-bottom: 2px; }
        .flow-step.active { opacity: 1; <?php if ($is_sweet): ?>background: #E55163; color: white; box-shadow: 0 4px 14px rgba(229,81,99,0.35);<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .flow-step.active strong { opacity: 0.9; color: inherit; }
        .flow-step:hover { opacity: 1; transform: translateY(-1px); }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .sticky-cta { position: sticky; bottom: 88px; z-index: 50; margin: 12px 0; padding: 12px 14px; border-radius: 16px; display: none; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; box-shadow: 0 8px 24px rgba(0,0,0,0.12); <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .sticky-cta.show { display: flex; }
        .sticky-cta a { color: inherit; font-weight: 700; text-decoration: underline; }
        .print-only { display: none; }
        .var-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .var-table th, .var-table td { padding: 8px 6px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; text-align: left; vertical-align: top; }
        .var-table th { opacity: 0.65; font-weight: normal; font-size: 0.78rem; }
        .var-table td.num, .var-table th.num { text-align: right; white-space: nowrap; }
        .var-pos { color: #1F6B4A; }
        .var-neg { color: #C62828; }
        .loc-chip-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .offline-banner {
            display: none; border-radius: 14px; padding: 10px 14px; margin-bottom: 12px; font-size: 0.92rem; line-height: 1.35;
            background: #FFF8E8; border: 1px solid #E8D59A; color: #8A6D1F;
        }
        .offline-banner.show { display: block; }
        @media print {
            .no-print, .back-link, .toolbar, .filters, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .flow-steps, .sticky-cta, .search { display: none !important; }
            .print-only { display: block !important; }
            body { background: white; padding-bottom: 0; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .item { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; grid-template-columns: 1fr 80px; }
            .field input { border: none; background: transparent; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory & Vendors' : 'Back to Inventory & Vendors'; ?></a>
        <h1><?php echo $is_sweet ? 'Count Stock' : 'Count Stock'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Enter what’s on hand — then build the order' : 'Enter on-hand, then build the order'; ?></p>
    </div>

    <div class="content">
        <div class="flow-steps">
            <a class="flow-step" href="/admin/product-setup"><strong>Step 1</strong><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
            <a class="flow-step active" href="/admin/count"><strong>Step 2</strong><?php echo $is_sweet ? 'Count Stock' : 'Count Stock'; ?></a>
            <a class="flow-step" href="/admin/auto-order"><strong>Step 3</strong><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></a>
        </div>

        <div class="intro">
            <?php echo $is_sweet
                ? 'Step 2 of the order loop: <strong>on-hand only</strong>. Filter by <strong>count location</strong> (walk-in, dry, line…) set on <a href="/admin/product-setup">Product Setup</a>. Counts auto-save on this phone — works offline once this page is open. Save a session, then <a href="/admin/auto-order">Auto-Order</a> 📋'
                : 'Step 2: on-hand by location. Set locations on <a href="/admin/product-setup">Product Setup</a>. Works offline after load. Then <a href="/admin/auto-order">Auto-Order</a>.'; ?>
        </div>
        <div class="offline-banner" id="offline-banner"><?php echo $is_sweet
            ? '📡 Offline mode — counts still save on this device and will stay when you reconnect.'
            : 'Offline — counts save on this device.'; ?></div>
        <div class="sync-pill" id="inv-sync-pill" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:0.88rem;margin-bottom:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);<?php if ($is_sweet): ?>background:#FFF5F6;color:#E55163;border:1px solid #F3C5CC;<?php else: ?>background:#EEF2F8;color:#1A2A44;border:1px solid #C5D0DE;<?php endif; ?>">
            <span class="dot" style="width:8px;height:8px;border-radius:50%;background:#2E9B63;"></span>
            <span id="inv-sync-text"><?php echo $is_sweet ? 'Syncing…' : 'Syncing…'; ?></span>
        </div>
        <style>
            #inv-sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
            #inv-sync-pill.offline .dot { background: #C9A227; }
            #inv-sync-pill.syncing .dot { background: #5B8DEF; animation: invpulse 1s infinite; }
            @keyframes invpulse { 50% { opacity: 0.35; } }
        </style>

        <div class="stats-row">
            <div class="stat">
                <div class="num" id="stat-total">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Items' : 'Items'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-counted">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Counted' : 'Counted'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-low">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Below par' : 'Below par'; ?></div>
            </div>
        </div>

        <div class="toolbar no-print">
            <button type="button" class="btn btn-secondary" id="save-count-btn"><?php echo $is_sweet ? 'Save count session ✨' : 'Save count session'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-count-btn"><?php echo $is_sweet ? '🖨️ Print sheet' : 'Print sheet'; ?></button>
            <button type="button" class="btn btn-secondary" id="export-count-btn"><?php echo $is_sweet ? 'Export CSV' : 'Export CSV'; ?></button>
            <a href="/admin/auto-order" class="btn btn-primary"><?php echo $is_sweet ? 'Next: Auto-Order 🚚' : 'Next: Auto-Order'; ?></a>
            <a href="/admin/product-setup" class="btn btn-ghost"><?php echo $is_sweet ? '← Product Setup' : '← Product Setup'; ?></a>
        </div>
        <div class="print-only" id="print-count-header" style="margin-bottom:8px;font-weight:600;"></div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'This count' : 'This count'; ?></h2>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Session label (optional)' : 'Session label (optional)'; ?></label>
                    <input id="count-label" type="text" placeholder="<?php echo $is_sweet ? 'e.g. Tuesday AM walk-in' : 'e.g. Tuesday AM count'; ?>">
                </div>
            </div>
            <p class="hint" id="autosave-hint"><?php echo $is_sweet ? 'On-hand auto-saves as you type. “Save count session” freezes a timestamped snapshot in history.' : 'On-hand auto-saves. Save count session creates a history snapshot.'; ?></p>
        </div>

        <input type="search" class="search" id="search" placeholder="<?php echo $is_sweet ? 'Search products…' : 'Search products…'; ?>">

        <div class="filters" id="filters">
            <button type="button" class="filter-chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
            <button type="button" class="filter-chip" data-filter="uncounted"><?php echo $is_sweet ? 'Not counted' : 'Not counted'; ?></button>
            <button type="button" class="filter-chip" data-filter="low"><?php echo $is_sweet ? 'Below par' : 'Below par'; ?></button>
            <button type="button" class="filter-chip" data-filter="ready"><?php echo $is_sweet ? 'Setup ready' : 'Setup ready'; ?></button>
        </div>
        <div class="loc-chip-row" id="loc-filters"></div>

        <div id="count-list"></div>

        <div class="sticky-cta" id="sticky-cta">
            <span id="sticky-msg"><?php echo $is_sweet ? 'Items below par' : 'Items below par'; ?></span>
            <a href="/admin/auto-order"><?php echo $is_sweet ? 'Build Auto-Order →' : 'Build Auto-Order →'; ?></a>
        </div>

        <div class="card no-print">
            <h2><?php echo $is_sweet ? 'Instant variance' : 'Variance report'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Compare this walk to a saved session (or theoretical par). Green = over, red = short 📊'
                : 'Compare current on-hand to a prior session or to par.'; ?></p>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Compare to' : 'Compare to'; ?></label>
                    <select id="var-compare">
                        <option value="par"><?php echo $is_sweet ? 'Par levels' : 'Par levels'; ?></option>
                    </select>
                </div>
                <div class="field" style="flex:0;min-width:120px;align-self:flex-end;">
                    <button type="button" class="btn btn-primary btn-small" id="var-run" style="width:100%;"><?php echo $is_sweet ? 'Run report' : 'Run report'; ?></button>
                </div>
            </div>
            <div id="var-report"></div>
        </div>

        <div class="card no-print">
            <h2><?php echo $is_sweet ? 'Count history' : 'Count history'; ?></h2>
            <div id="count-sessions"></div>
        </div>

        <div class="actions-bar">
            <a href="/admin/product-setup" class="btn btn-secondary"><?php echo $is_sweet ? '← Product Setup' : '← Product Setup'; ?></a>
            <a href="/admin/auto-order" class="btn btn-primary"><?php echo $is_sweet ? 'Next: Auto-Order 🚚' : 'Next: Auto-Order'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const COUNT_KEY = 'pbj_inv_count_sessions_v1';
        const CAT_ORDER_KEY = 'pbj_inv_category_order_v1';
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
            dairy: 'Dairy', meats: 'Meats', frozen: 'Frozen', canned_dry: 'Canned & Dry',
            paper_disposable: 'Paper & Disposable', chemical_janitorial: 'Chemical & Janitorial',
            supplies_equipment: 'Supplies & Equipment', produce: 'Produce', dispenser_beverage: 'Dispenser Beverage'
        };
        var LEGACY_CAT_MAP = { food: 'canned_dry', paper: 'paper_disposable', janitorial: 'chemical_janitorial', other: 'supplies_equipment' };

        var catOrder = DEFAULT_CAT_ORDER.slice();
        var catLabels = Object.assign({}, DEFAULT_CAT_LABELS);
        var customCats = {};

        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg || (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1200);
        }
        function loadCatState() {
            try {
                var r = JSON.parse(localStorage.getItem(CAT_ORDER_KEY) || 'null');
                if (r && typeof r === 'object' && !Array.isArray(r)) {
                    if (r.custom) Object.keys(r.custom).forEach(function (k) {
                        if (r.custom[k] && DEFAULT_CAT_ORDER.indexOf(k) === -1) {
                            customCats[k] = r.custom[k];
                            catLabels[k] = r.custom[k];
                        }
                    });
                    if (Array.isArray(r.order) && r.order.length) catOrder = r.order.slice();
                } else if (Array.isArray(r) && r.length) {
                    catOrder = r.slice();
                }
            } catch (e) {}
            DEFAULT_CAT_ORDER.forEach(function (c) {
                if (catOrder.indexOf(c) === -1) catOrder.push(c);
            });
            Object.keys(customCats).forEach(function (c) {
                if (catOrder.indexOf(c) === -1) catOrder.push(c);
            });
        }
        function normalizeCategory(cat) {
            if (!cat) return 'canned_dry';
            if (LEGACY_CAT_MAP[cat]) return LEGACY_CAT_MAP[cat];
            if (catLabels[cat] || DEFAULT_CAT_LABELS[cat] || customCats[cat]) return cat;
            return 'canned_dry';
        }
        function catLabel(slug) {
            return catLabels[slug] || DEFAULT_CAT_LABELS[slug] || customCats[slug] || slug;
        }
        function catRank(cat) {
            var i = catOrder.indexOf(normalizeCategory(cat));
            return i === -1 ? 999 : i;
        }

        function loadMaster() {
            try {
                var r = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                if (!r || typeof r.items !== 'object') return { items: {} };
                return r;
            } catch (e) { return { items: {} }; }
        }
        var invSync = null;
        function saveMaster(m, opts) {
            opts = opts || {};
            if (m && !opts.remote) m.structureAt = Date.now();
            localStorage.setItem(ING_KEY, JSON.stringify(m));
            if (invSync && !opts.remote && !opts.skipPush) invSync.pushMaster(m);
        }
        function loadSessions() {
            try {
                var r = JSON.parse(localStorage.getItem(COUNT_KEY) || 'null');
                return r && Array.isArray(r.sessions) ? r : { sessions: [] };
            } catch (e) { return { sessions: [] }; }
        }
        function saveSessions(s, opts) {
            opts = opts || {};
            if (s && !opts.remote) s.structureAt = Date.now();
            localStorage.setItem(COUNT_KEY, JSON.stringify(s));
            if (invSync && !opts.remote && !opts.skipPush) invSync.pushSessions(s);
        }

        loadCatState();
        var master = loadMaster();
        var sessions = loadSessions();
        var filter = 'all';
        var locFilter = 'all';
        var search = '';

        function locationOf(item) {
            return String((item && item.location) || '').trim();
        }
        function allLocations() {
            var map = {};
            Object.keys(master.items).forEach(function (k) {
                var loc = locationOf(master.items[k]);
                if (loc) map[loc] = (map[loc] || 0) + 1;
            });
            return Object.keys(map).sort(function (a, b) { return a.localeCompare(b); }).map(function (loc) {
                return { name: loc, count: map[loc] };
            });
        }
        function paintLocFilters() {
            var root = document.getElementById('loc-filters');
            if (!root) return;
            var locs = allLocations();
            var html = '<button type="button" class="filter-chip' + (locFilter === 'all' ? ' active' : '') + '" data-loc="all">' +
                (isSweet ? 'All locations' : 'All locations') + '</button>';
            if (locs.length) {
                locs.forEach(function (L) {
                    html += '<button type="button" class="filter-chip' + (locFilter === L.name ? ' active' : '') + '" data-loc="' + esc(L.name) + '">' +
                        esc(L.name) + ' <span style="opacity:0.7">(' + L.count + ')</span></button>';
                });
                html += '<button type="button" class="filter-chip' + (locFilter === '__none__' ? ' active' : '') + '" data-loc="__none__">' +
                    (isSweet ? 'No location set' : 'No location') + '</button>';
            } else {
                html += '<span class="hint" style="margin:0;align-self:center;">' +
                    (isSweet ? 'Set locations on Product Setup for shelf-to-sheet walks 📍' : 'Set locations on Product Setup') + '</span>';
            }
            root.innerHTML = html;
        }
        function fillVarCompareSelect() {
            var sel = document.getElementById('var-compare');
            if (!sel) return;
            var cur = sel.value || 'par';
            var opts = '<option value="par">' + (isSweet ? 'Par levels' : 'Par levels') + '</option>';
            sessions.sessions.slice().reverse().forEach(function (s) {
                var when = new Date(s.at).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
                opts += '<option value="' + esc(s.id) + '">' + esc(s.label + ' · ' + when) + '</option>';
            });
            sel.innerHTML = opts;
            if ([].some.call(sel.options, function (o) { return o.value === cur; })) sel.value = cur;
        }
        function runVariance() {
            collectOnHand();
            saveMaster(master);
            var sel = document.getElementById('var-compare');
            var mode = sel ? sel.value : 'par';
            var root = document.getElementById('var-report');
            if (!root) return;
            var baseline = null;
            var baselineLabel = isSweet ? 'Par' : 'Par';
            if (mode !== 'par') {
                var sess = sessions.sessions.find(function (s) { return s.id === mode; });
                if (!sess) {
                    root.innerHTML = '<p class="hint">' + (isSweet ? 'Session not found' : 'Session not found') + '</p>';
                    return;
                }
                baseline = sess.counts || {};
                baselineLabel = sess.label || (isSweet ? 'Prior count' : 'Prior');
            }
            var rows = [];
            var shortN = 0, overN = 0, evenN = 0;
            Object.keys(master.items).sort(function (a, b) {
                return (master.items[a].name || a).localeCompare(master.items[b].name || b);
            }).forEach(function (key) {
                var item = master.items[key];
                if (locFilter !== 'all') {
                    var loc = locationOf(item);
                    if (locFilter === '__none__' && loc) return;
                    if (locFilter !== '__none__' && loc !== locFilter) return;
                }
                var cur = parseFloat(item.onHand);
                var base;
                if (baseline) {
                    base = parseFloat(baseline[key]);
                } else {
                    base = parseFloat(item.par);
                }
                if (isNaN(cur) && isNaN(base)) return;
                if (isNaN(cur)) cur = null;
                if (isNaN(base)) base = null;
                var diff = (cur != null && base != null) ? Math.round((cur - base) * 100) / 100 : null;
                if (diff != null) {
                    if (diff < 0) shortN++;
                    else if (diff > 0) overN++;
                    else evenN++;
                }
                // only show variance lines when both sides exist, or always show if any count
                if (diff == null && cur == null) return;
                rows.push({
                    name: item.name || key,
                    loc: locationOf(item),
                    cur: cur,
                    base: base,
                    diff: diff,
                    unit: parByWord(item, cur != null ? cur : base)
                });
            });
            if (!rows.length) {
                root.innerHTML = '<p class="hint">' + (isSweet
                    ? 'Nothing to compare yet — count some items first.'
                    : 'No comparable counts yet.') + '</p>';
                return;
            }
            // sort by absolute variance
            rows.sort(function (a, b) {
                var da = a.diff == null ? -1 : Math.abs(a.diff);
                var db = b.diff == null ? -1 : Math.abs(b.diff);
                return db - da;
            });
            var html = '<p class="hint" style="margin-top:4px;">' +
                (isSweet
                    ? (shortN + ' short · ' + overN + ' over · ' + evenN + ' even vs ' + baselineLabel)
                    : (shortN + ' short · ' + overN + ' over · ' + evenN + ' even vs ' + baselineLabel)) +
                '</p>';
            html += '<div style="overflow-x:auto;"><table class="var-table"><thead><tr>' +
                '<th>' + (isSweet ? 'Product' : 'Product') + '</th>' +
                '<th class="num">' + esc(baselineLabel) + '</th>' +
                '<th class="num">' + (isSweet ? 'Now' : 'Now') + '</th>' +
                '<th class="num">' + (isSweet ? 'Var' : 'Var') + '</th>' +
                '</tr></thead><tbody>';
            rows.forEach(function (r) {
                var cls = r.diff == null ? '' : (r.diff < 0 ? 'var-neg' : (r.diff > 0 ? 'var-pos' : ''));
                var diffTxt = r.diff == null ? '—' : ((r.diff > 0 ? '+' : '') + r.diff);
                html += '<tr><td>' + esc(r.name) +
                    (r.loc ? '<div class="meta">' + esc(r.loc) + '</div>' : '') +
                    '</td><td class="num">' + (r.base != null ? r.base : '—') +
                    '</td><td class="num">' + (r.cur != null ? r.cur : '—') +
                    '</td><td class="num ' + cls + '">' + diffTxt + '</td></tr>';
            });
            html += '</tbody></table></div>';
            root.innerHTML = html;
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
        function isLow(item) {
            var onHand = parseFloat(item.onHand);
            var par = parseFloat(item.par);
            if (isNaN(onHand) || isNaN(par)) return false;
            return onHand < par;
        }
        function orderQty(item) {
            var onHand = parseFloat(item.onHand);
            var par = parseFloat(item.par);
            if (isNaN(onHand) || isNaN(par)) return null;
            var need = par - onHand;
            if (need <= 0) return 0;
            if (parByOf(item) === 'case') {
                need = Math.ceil(need);
            } else {
                var pack = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
                if (!isNaN(pack) && pack > 0) need = Math.ceil(need / pack) * pack;
            }
            return Math.round(need * 100) / 100;
        }
        function setupReady(item) {
            return String(item.vendor || '').trim() && item.par !== '' && item.par != null && !isNaN(parseFloat(item.par));
        }

        function collectOnHand() {
            document.querySelectorAll('#count-list .item').forEach(function (el) {
                var key = el.dataset.key;
                if (!master.items[key]) return;
                var inp = el.querySelector('.f-onhand');
                if (inp) master.items[key].onHand = inp.value;
            });
        }
        function persistCounts(showToast) {
            collectOnHand();
            // stamp on-hand edits so multi-device merge prefers fresher counts
            Object.keys(master.items || {}).forEach(function (k) {
                if (master.items[k]) master.items[k].updatedAt = Date.now();
            });
            saveMaster(master);
            if (showToast) toast(isSweet ? 'Counts saved 💾' : 'Counts saved');
            renderStats();
        }

        function renderStats() {
            var keys = Object.keys(master.items);
            var counted = 0, low = 0;
            keys.forEach(function (k) {
                var item = master.items[k];
                if (item.onHand !== '' && item.onHand != null) counted++;
                if (isLow(item)) low++;
            });
            document.getElementById('stat-total').textContent = keys.length;
            document.getElementById('stat-counted').textContent = counted;
            document.getElementById('stat-low').textContent = low;
            var sticky = document.getElementById('sticky-cta');
            var msg = document.getElementById('sticky-msg');
            if (low > 0) {
                sticky.classList.add('show');
                msg.textContent = isSweet
                    ? low + ' below par — ready for Auto-Order'
                    : low + ' below par — build Auto-Order';
            } else {
                sticky.classList.remove('show');
            }
        }

        function renderList() {
            renderStats();
            var root = document.getElementById('count-list');
            var keys = Object.keys(master.items).sort(function (a, b) {
                var ca = catRank(master.items[a].category);
                var cb = catRank(master.items[b].category);
                if (ca !== cb) return ca - cb;
                return (master.items[a].name || a).localeCompare(master.items[b].name || b);
            });

            if (!keys.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No products yet — add ingredients on recipes or set them up on Product Setup first 💕'
                    : 'No products yet. Finish Product Setup first.') +
                    '<div style="margin-top:14px;"><a class="btn btn-primary" href="/admin/product-setup">' + (isSweet ? 'Go to Product Setup' : 'Go to Product Setup') + '</a></div></div>';
                return;
            }

            paintLocFilters();
            var q = search.trim().toLowerCase();
            var filtered = keys.filter(function (key) {
                var item = master.items[key];
                if (q && (item.name || key).toLowerCase().indexOf(q) === -1 &&
                    (item.vendor || '').toLowerCase().indexOf(q) === -1 &&
                    locationOf(item).toLowerCase().indexOf(q) === -1) return false;
                if (locFilter !== 'all') {
                    var loc = locationOf(item);
                    if (locFilter === '__none__') {
                        if (loc) return false;
                    } else if (loc !== locFilter) {
                        return false;
                    }
                }
                if (filter === 'uncounted') return item.onHand === '' || item.onHand == null;
                if (filter === 'low') return isLow(item);
                if (filter === 'ready') return setupReady(item);
                return true;
            });

            if (!filtered.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No matches for this filter' : 'No matches') + '</div>';
                return;
            }

            var html = '';
            var lastCat = null;
            filtered.forEach(function (key) {
                var item = master.items[key];
                var cat = normalizeCategory(item.category);
                if (cat !== lastCat) {
                    lastCat = cat;
                    html += '<div class="cat-head">' + esc(catLabel(cat)) + '</div>';
                }
                var low = isLow(item);
                var need = orderQty(item);
                var byWord = parByWord(item, item.par);
                var meta = [];
                meta.push((isSweet ? 'Par ' : 'Par ') + (item.par !== '' && item.par != null ? item.par : '—') + ' ' + byWord);
                if (locationOf(item)) meta.push('📍 ' + locationOf(item));
                if (item.vendor) meta.push(item.vendor);
                if (item.sku) meta.push('#' + item.sku);
                if (!setupReady(item)) meta.push(isSweet ? '⚠ setup incomplete' : 'setup incomplete');

                html += '<div class="item ' + (low ? 'low' : (item.onHand !== '' && item.onHand != null ? 'ok' : '')) + '" data-key="' + esc(key) + '">' +
                    '<div>' +
                        '<h3 class="item-name">' + esc(item.name || key) + '</h3>' +
                        '<div class="meta">' + esc(meta.join(' · ')) + '</div>' +
                        (need != null ? '<div class="need' + (need > 0 ? ' order' : '') + '">' +
                            (need > 0
                                ? (isSweet ? 'Order ~ ' : 'Order ~ ') + need + ' ' + parByWord(item, need)
                                : (isSweet ? 'At or above par' : 'At or above par')) +
                            '</div>' : '') +
                    '</div>' +
                    '<div class="field">' +
                        '<label>' + (isSweet ? 'On hand (' + byWord + ')' : 'On hand (' + byWord + ')') + '</label>' +
                        '<input class="f-onhand" type="number" step="any" min="0" inputmode="decimal" value="' +
                            esc(item.onHand !== '' && item.onHand != null ? item.onHand : '') + '" placeholder="' +
                            (parByOf(item) === 'case' ? (isSweet ? 'cases' : 'cases') : (isSweet ? 'each' : 'each')) + '">' +
                    '</div>' +
                '</div>';
            });
            root.innerHTML = html;
        }

        function defaultLabel() {
            return new Date().toLocaleString(undefined, {
                weekday: 'short', month: 'short', day: 'numeric',
                hour: 'numeric', minute: '2-digit'
            });
        }
        function saveSession() {
            collectOnHand();
            saveMaster(master);
            var counts = {};
            var counted = 0, low = 0;
            Object.keys(master.items).forEach(function (key) {
                var item = master.items[key];
                counts[key] = item.onHand !== undefined && item.onHand !== null ? String(item.onHand) : '';
                if (counts[key] !== '') counted++;
                if (isLow(item)) low++;
            });
            if (!Object.keys(master.items).length) {
                alert(isSweet ? 'No products to count yet' : 'No products to count yet');
                return;
            }
            var label = document.getElementById('count-label').value.trim() || defaultLabel();
            sessions.sessions.push({
                id: uid(),
                label: label,
                at: Date.now(),
                counts: counts,
                itemCount: Object.keys(master.items).length,
                counted: counted,
                lowCount: low
            });
            if (sessions.sessions.length > 40) sessions.sessions = sessions.sessions.slice(-40);
            saveSessions(sessions);
            document.getElementById('count-label').value = '';
            renderSessions();
            fillVarCompareSelect();
            toast(isSweet ? 'Count saved — ready for Auto-Order 📋' : 'Count saved — ready for Auto-Order');
            // gentle nudge into the order step
            setTimeout(function () {
                if (confirm(isSweet
                    ? 'Count session saved! Open Auto-Order to build the list by vendor?'
                    : 'Count saved. Open Auto-Order now?')) {
                    window.location.href = '/admin/auto-order';
                }
            }, 350);
        }
        function restoreSession(id) {
            var sess = sessions.sessions.find(function (s) { return s.id === id; });
            if (!sess) return;
            if (!confirm(isSweet
                ? 'Restore on-hand from “' + sess.label + '”? Current counts will be overwritten.'
                : 'Restore this session’s on-hand values?')) return;
            Object.keys(sess.counts || {}).forEach(function (key) {
                if (!master.items[key]) return;
                master.items[key].onHand = sess.counts[key];
            });
            saveMaster(master);
            renderList();
            toast(isSweet ? 'Counts restored ✨' : 'Counts restored');
        }
        function renderSessions() {
            var root = document.getElementById('count-sessions');
            if (!sessions.sessions.length) {
                root.innerHTML = '<p class="hint">' + (isSweet ? 'No sessions yet — finish a walk and save.' : 'No sessions yet.') + '</p>';
                return;
            }
            root.innerHTML = sessions.sessions.slice().reverse().map(function (s) {
                var when = new Date(s.at).toLocaleString(undefined, { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
                return '<div class="session"><h3>' + esc(s.label) + '</h3>' +
                    '<div class="meta">' + esc(when) + ' · ' + (s.counted != null ? s.counted : '—') + '/' + (s.itemCount || 0) +
                    (isSweet ? ' counted · ' : ' counted · ') + (s.lowCount != null ? s.lowCount : '—') +
                    (isSweet ? ' below par' : ' below par') + '</div>' +
                    '<div class="session-actions">' +
                    '<button type="button" class="btn btn-small btn-primary" data-restore="' + esc(s.id) + '">' + (isSweet ? 'Restore' : 'Restore') + '</button>' +
                    '<a class="btn btn-small btn-ghost" href="/admin/auto-order">' + (isSweet ? 'Auto-Order' : 'Auto-Order') + '</a>' +
                    '<button type="button" class="btn btn-small btn-danger" data-del="' + esc(s.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        document.getElementById('filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.filter-chip');
            if (!chip) return;
            collectOnHand();
            saveMaster(master);
            filter = chip.dataset.filter;
            document.querySelectorAll('#filters .filter-chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
            renderList();
        });
        var locFiltersEl = document.getElementById('loc-filters');
        if (locFiltersEl) {
            locFiltersEl.addEventListener('click', function (e) {
                var chip = e.target.closest('[data-loc]');
                if (!chip) return;
                collectOnHand();
                saveMaster(master);
                locFilter = chip.getAttribute('data-loc') || 'all';
                renderList();
            });
        }
        var varRun = document.getElementById('var-run');
        if (varRun) varRun.addEventListener('click', runVariance);
        function syncOfflineBanner() {
            var b = document.getElementById('offline-banner');
            if (!b) return;
            b.classList.toggle('show', !navigator.onLine);
        }
        window.addEventListener('online', syncOfflineBanner);
        window.addEventListener('offline', syncOfflineBanner);
        syncOfflineBanner();
        document.getElementById('search').addEventListener('input', function () {
            collectOnHand();
            search = this.value;
            renderList();
        });
        document.getElementById('count-list').addEventListener('change', function () {
            persistCounts(true);
            // update need text without full re-render of all fields - full re-render OK if not focused
            var active = document.activeElement;
            var inList = active && active.closest && active.closest('#count-list');
            if (!inList) renderList();
            else {
                // update just this row's need
                var row = active.closest('.item');
                if (row && master.items[row.dataset.key]) {
                    var item = master.items[row.dataset.key];
                    var need = orderQty(item);
                    var needEl = row.querySelector('.need');
                    if (needEl && need != null) {
                        needEl.className = 'need' + (need > 0 ? ' order' : '');
                        needEl.textContent = need > 0
                            ? ((isSweet ? 'Order ~ ' : 'Order ~ ') + need + ' ' + parByWord(item, need))
                            : (isSweet ? 'At or above par' : 'At or above par');
                    }
                    row.classList.toggle('low', isLow(item));
                    row.classList.toggle('ok', item.onHand !== '' && item.onHand != null && !isLow(item));
                }
                renderStats();
            }
        });
        document.getElementById('count-list').addEventListener('focusout', function (e) {
            if (!e.target.matches('.f-onhand')) return;
            persistCounts(false);
        });
        document.getElementById('save-count-btn').addEventListener('click', saveSession);
        document.getElementById('print-count-btn').addEventListener('click', function () {
            document.getElementById('print-count-header').textContent =
                (isSweet ? 'Stock Count · ' : 'Stock Count · ') + new Date().toLocaleString();
            window.print();
        });
        document.getElementById('export-count-btn').addEventListener('click', function () {
            collectOnHand();
            var rows = [['Product', 'Location', 'Category', 'Vendor', 'SKU', 'Par', 'Par by', 'On hand', 'Need']];
            Object.keys(master.items).sort(function (a, b) {
                return (master.items[a].name || a).localeCompare(master.items[b].name || b);
            }).forEach(function (key) {
                var item = master.items[key];
                var need = orderQty(item);
                rows.push([
                    item.name || key,
                    locationOf(item),
                    catLabel(normalizeCategory(item.category)),
                    item.vendor || '',
                    item.sku || '',
                    item.par != null ? item.par : '',
                    parByOf(item),
                    item.onHand != null ? item.onHand : '',
                    need != null ? need : ''
                ]);
            });
            var csv = rows.map(function (r) {
                return r.map(function (c) {
                    var s = String(c == null ? '' : c);
                    if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
                    return s;
                }).join(',');
            }).join('\n');
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'stock-count.csv';
            a.click();
            URL.revokeObjectURL(a.href);
            toast(isSweet ? 'CSV exported ✨' : 'CSV exported');
        });
        document.getElementById('count-sessions').addEventListener('click', function (e) {
            var rest = e.target.closest('[data-restore]');
            if (rest) { restoreSession(rest.dataset.restore); return; }
            var del = e.target.closest('[data-del]');
            if (del) {
                if (!confirm(isSweet ? 'Remove this session?' : 'Remove this session?')) return;
                sessions.sessions = sessions.sessions.filter(function (s) { return s.id !== del.dataset.del; });
                saveSessions(sessions);
                renderSessions();
                toast(isSweet ? 'Removed' : 'Removed');
            }
        });
        window.addEventListener('beforeunload', function () {
            try { collectOnHand(); saveMaster(master); } catch (e) {}
        });

        renderList();
        renderSessions();
        fillVarCompareSelect();
        if (window.PbjInvSync) {
            invSync = window.PbjInvSync.wire({
                statusEl: 'inv-sync-pill',
                syncSessions: true,
                getMaster: function () { return master; },
                setMaster: function (next) {
                    master = next && typeof next.items === 'object' ? next : { items: {} };
                    saveMaster(master, { remote: true, skipPush: true });
                    renderList();
                },
                getSessions: function () { return sessions; },
                setSessions: function (next) {
                    sessions = next && Array.isArray(next.sessions) ? next : { sessions: [] };
                    saveSessions(sessions, { remote: true, skipPush: true });
                    renderSessions();
                    fillVarCompareSelect();
                },
                onMasterRemote: function () {
                    toast(isSweet ? 'Counts synced from house ✨' : 'Synced from house');
                }
            });
        } else {
            var st = document.getElementById('inv-sync-text');
            if (st) st.textContent = isSweet ? 'Local only' : 'Local only';
        }
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyInvPerms);
            document.addEventListener('pbj-perms-ready', applyInvPerms);
})();
    </script>
</body>
</html>
