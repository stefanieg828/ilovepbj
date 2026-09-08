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
    <title><?php echo $is_sweet ? 'Master Product List' : 'Product List'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 980px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; align-items: flex-end; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 6px 10px; font-size: 0.85rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .field { margin-bottom: 0; }
        .field label { display: block; font-size: 0.8rem; opacity: 0.65; margin-bottom: 4px; }
        .field select, .field input { box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .search { flex: 1; min-width: 180px; width: 100%; }
        .stats { font-size: 0.95rem; opacity: 0.75; margin-bottom: 12px; }
        .cat-head { font-size: 1.15rem; margin: 20px 0 10px; padding: 8px 12px; border-radius: 12px; <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163; background: #FFF5F6;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44; background: #EEF2F8;<?php endif; ?> }
        .item { background: white; border-radius: 16px; padding: 14px 16px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .item-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; margin-bottom: 8px; }
        .item-name { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.25rem; margin: 0; }
        .details { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 14px; font-size: 0.95rem; line-height: 1.35; }
        @media (min-width: 720px) { .details { grid-template-columns: repeat(4, 1fr); } }
        .details dt { font-size: 0.75rem; opacity: 0.6; margin: 0; }
        .details dd { margin: 0 0 4px; font-weight: 600; }
        .custom-btns { display: flex; gap: 6px; flex-shrink: 0; }
        .empty { text-align: center; padding: 36px 20px; background: white; border-radius: 18px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .hint { font-size: 0.92rem; opacity: 0.7; margin: 0 0 10px; line-height: 1.4; }
        .actions-bar { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .print-only { display: none; }
        /* Compact print table (hundreds of products → fewer pages) */
        .print-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; line-height: 1.2; }
        .print-table th, .print-table td { border: 1px solid #999; padding: 2px 4px; vertical-align: top; text-align: left; }
        .print-table th { background: #e8e8e8 !important; font-weight: 700; font-size: 7.5pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-table .cat-row td { background: #f0f0f0 !important; font-weight: 700; font-size: 8pt; padding: 3px 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-table .num { text-align: right; white-space: nowrap; }
        .print-table td.name { font-weight: 600; max-width: 140px; }
        @media print {
            @page { margin: 0.4in 0.35in; size: letter; }
            body { background: white !important; color: #000; padding: 0 !important; margin: 0; font-size: 8.5pt; }
            .header { display: none !important; }
            .content { padding: 0 !important; max-width: none !important; margin: 0 !important; }
            .back-link, .toolbar, .actions-bar, .bottom-nav, .toast, .custom-btns, .no-print,
            #list, .stats, .intro { display: none !important; }
            .print-only { display: block !important; }
            #print-header { display: block !important; margin: 0 0 6px; font-size: 9pt; font-weight: 600; }
            #print-table-wrap { display: block !important; }
            .print-table { page-break-inside: auto; }
            .print-table tr { page-break-inside: avoid; page-break-after: auto; }
            .print-table thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory & Vendors' : 'Back to Inventory & Vendors'; ?></a>
        <h1><?php echo $is_sweet ? 'Master Product List' : 'Product List'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Everything you need to run the house' : 'All products and their details'; ?></p>
    </div>

    <div class="content">
        <div class="print-only" id="print-header"></div>
        <div class="print-only" id="print-table-wrap"></div>

        <div class="intro no-print">
            <?php echo $is_sweet
                ? 'A clean list of <strong>all products</strong> with par levels and details — not an order form. Sort how you like, print it, or download for Excel. Edit products on <a href="/admin/product-setup">Product Setup</a> 📋'
                : 'Full product reference with par levels. Sort, print, or download to Excel. Edit on <a href="/admin/product-setup">Product Setup</a>.'; ?>
        </div>

        <div class="toolbar no-print">
            <div class="field" style="min-width:160px;">
                <label><?php echo $is_sweet ? 'Organize by' : 'Organize by'; ?></label>
                <select id="sort-mode">
                    <option value="category"><?php echo $is_sweet ? 'Category' : 'Category'; ?></option>
                    <option value="name"><?php echo $is_sweet ? 'Name (A–Z)' : 'Name (A–Z)'; ?></option>
                    <option value="entered"><?php echo $is_sweet ? 'Order entered' : 'Order entered'; ?></option>
                    <option value="custom"><?php echo $is_sweet ? 'Custom' : 'Custom'; ?></option>
                </select>
            </div>
            <div class="field search">
                <label><?php echo $is_sweet ? 'Search' : 'Search'; ?></label>
                <input type="search" id="search" placeholder="<?php echo $is_sweet ? 'Name, vendor, SKU…' : 'Name, vendor, SKU…'; ?>">
            </div>
            <button type="button" class="btn btn-primary" id="btn-excel"><?php echo $is_sweet ? 'Download Excel 📥' : 'Download Excel'; ?></button>
            <button type="button" class="btn btn-secondary" id="btn-print"><?php echo $is_sweet ? 'Print list' : 'Print list'; ?></button>
        </div>

        <p class="hint no-print" id="custom-hint" style="display:none;"><?php echo $is_sweet ? 'Custom mode: use ↑ ↓ on each product to arrange your list. Order is saved on this device.' : 'Custom mode: use ↑ ↓ to rearrange. Saved on this device.'; ?></p>
        <div class="stats" id="stats"></div>
        <div id="list"></div>

        <div class="actions-bar no-print">
            <a href="/admin/product-setup" class="btn btn-secondary"><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
            <a href="/admin/inventory" class="btn btn-primary"><?php echo $is_sweet ? 'Inventory hub' : 'Inventory hub'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Downloaded 📥' : 'Downloaded'; ?></div>
    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const CAT_ORDER_KEY = 'pbj_inv_category_order_v1';
        const CUSTOM_ORDER_KEY = 'pbj_inv_product_list_order_v1';
        const SORT_PREF_KEY = 'pbj_inv_product_list_sort_v1';
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
        var customOrder = [];
        var sortMode = 'category';
        var search = '';

        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg || (isSweet ? 'Done ✨' : 'Done');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1400);
        }
        function money(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
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
        function parByOf(item) {
            return item && item.parBy === 'case' ? 'case' : 'each';
        }
        function parByWord(item, qty) {
            if (parByOf(item) === 'case') {
                var n = qty != null ? parseFloat(qty) : NaN;
                if (!isNaN(n) && n === 1) return 'case';
                return 'cases';
            }
            return 'each';
        }
        function packOf(item) {
            if (item.pack !== undefined && item.pack !== null && item.pack !== '') return item.pack;
            return item.packSize != null ? item.packSize : '';
        }
        function casePriceOf(item) {
            if (item.casePrice !== undefined && item.casePrice !== null && item.casePrice !== '') return item.casePrice;
            return item.costPerUnit != null ? item.costPerUnit : '';
        }

        function loadMaster() {
            try {
                var r = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                if (!r || typeof r.items !== 'object') return { items: {} };
                return r;
            } catch (e) { return { items: {} }; }
        }
        function saveMaster(m) {
            localStorage.setItem(ING_KEY, JSON.stringify(m));
        }
        function loadCustomOrder() {
            try {
                var r = JSON.parse(localStorage.getItem(CUSTOM_ORDER_KEY) || 'null');
                return Array.isArray(r) ? r : [];
            } catch (e) { return []; }
        }
        function saveCustomOrder() {
            localStorage.setItem(CUSTOM_ORDER_KEY, JSON.stringify(customOrder));
        }

        loadCatState();
        var master = loadMaster();
        // Ensure enteredAt for "order entered" sort
        var keys = Object.keys(master.items);
        var changed = false;
        keys.forEach(function (k, idx) {
            if (!master.items[k].enteredAt) {
                master.items[k].enteredAt = Date.now() - (keys.length - idx) * 1000;
                changed = true;
            }
        });
        if (changed) saveMaster(master);

        customOrder = loadCustomOrder();
        // Keep custom order in sync with known keys
        keys.forEach(function (k) {
            if (customOrder.indexOf(k) === -1) customOrder.push(k);
        });
        customOrder = customOrder.filter(function (k) { return !!master.items[k]; });
        saveCustomOrder();

        try {
            var pref = localStorage.getItem(SORT_PREF_KEY);
            if (pref && ['category', 'name', 'entered', 'custom'].indexOf(pref) !== -1) sortMode = pref;
        } catch (e) {}
        document.getElementById('sort-mode').value = sortMode;

        function filteredKeys() {
            var q = search.trim().toLowerCase();
            return Object.keys(master.items).filter(function (key) {
                if (!q) return true;
                var item = master.items[key];
                var blob = [
                    item.name, key, item.vendor, item.sku,
                    catLabel(normalizeCategory(item.category)),
                    item.unit, item.size, packOf(item)
                ].join(' ').toLowerCase();
                return blob.indexOf(q) !== -1;
            });
        }

        function sortedKeys() {
            var keys = filteredKeys();
            if (sortMode === 'name') {
                keys.sort(function (a, b) {
                    return (master.items[a].name || a).localeCompare(master.items[b].name || b, undefined, { sensitivity: 'base' });
                });
            } else if (sortMode === 'entered') {
                keys.sort(function (a, b) {
                    var ea = master.items[a].enteredAt || 0;
                    var eb = master.items[b].enteredAt || 0;
                    if (ea !== eb) return ea - eb;
                    return (master.items[a].name || a).localeCompare(master.items[b].name || b);
                });
            } else if (sortMode === 'custom') {
                keys.sort(function (a, b) {
                    var ia = customOrder.indexOf(a);
                    var ib = customOrder.indexOf(b);
                    if (ia === -1) ia = 99999;
                    if (ib === -1) ib = 99999;
                    if (ia !== ib) return ia - ib;
                    return (master.items[a].name || a).localeCompare(master.items[b].name || b);
                });
            } else {
                // category (default)
                keys.sort(function (a, b) {
                    var ca = catRank(master.items[a].category);
                    var cb = catRank(master.items[b].category);
                    if (ca !== cb) return ca - cb;
                    return (master.items[a].name || a).localeCompare(master.items[b].name || b);
                });
            }
            return keys;
        }

        function detailRow(item) {
            var parText = (item.par !== '' && item.par != null)
                ? (item.par + ' ' + parByWord(item, item.par))
                : '—';
            var pack = packOf(item);
            var size = item.size !== '' && item.size != null ? item.size : '';
            var packDesc = '—';
            if (pack !== '' || size !== '' || item.unit) {
                packDesc = (pack !== '' ? pack + '×' : '') +
                    (size !== '' ? size : '') +
                    (item.unit ? ' ' + item.unit : '') +
                    (pack !== '' ? ' / case' : '');
                packDesc = packDesc.trim() || '—';
            }
            var caseP = casePriceOf(item);
            var caseText = caseP !== '' && !isNaN(parseFloat(caseP)) ? money(parseFloat(caseP)) : (caseP || '—');

            return {
                category: catLabel(normalizeCategory(item.category)),
                par: parText,
                parBy: parByOf(item) === 'case' ? 'Case' : 'Each',
                vendor: item.vendor || '—',
                pack: pack !== '' ? pack : '—',
                size: size !== '' ? size : '—',
                unit: item.unit || '—',
                packDesc: packDesc,
                casePrice: caseText,
                sku: item.sku || '—',
                parRaw: item.par !== '' && item.par != null ? item.par : '',
                casePriceRaw: caseP
            };
        }

        function render() {
            document.getElementById('custom-hint').style.display = sortMode === 'custom' ? '' : 'none';
            var keys = sortedKeys();
            var total = Object.keys(master.items).length;
            document.getElementById('stats').textContent = isSweet
                ? 'Showing ' + keys.length + ' of ' + total + ' products'
                : 'Showing ' + keys.length + ' of ' + total + ' products';

            document.getElementById('print-header').textContent =
                (isSweet ? 'Master Product List' : 'Product List') + ' · ' +
                new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) +
                ' · ' + keys.length + ' items';

            var root = document.getElementById('list');
            var printWrap = document.getElementById('print-table-wrap');
            if (!total) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No products yet — add them on Product Setup or from recipe cards 💕'
                    : 'No products yet. Add them on Product Setup first.') +
                    '<div style="margin-top:14px;"><a class="btn btn-primary" href="/admin/product-setup">' +
                    (isSweet ? 'Product Setup' : 'Product Setup') + '</a></div></div>';
                printWrap.innerHTML = '';
                return;
            }
            if (!keys.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No matches for this search' : 'No matches') + '</div>';
                printWrap.innerHTML = '';
                return;
            }

            var html = '';
            var printRows = '';
            var lastCat = null;
            // Product · SKU · Vendor · Pack · Size · Unit · Case $ · Par  (category is section headers only)
            var colCount = 8;
            keys.forEach(function (key, idx) {
                var item = master.items[key];
                var d = detailRow(item);
                var cat = normalizeCategory(item.category);
                if (sortMode === 'category' && cat !== lastCat) {
                    lastCat = cat;
                    html += '<div class="cat-head">' + esc(catLabel(cat)) + '</div>';
                    printRows += '<tr class="cat-row"><td colspan="' + colCount + '">' + esc(catLabel(cat)) + '</td></tr>';
                }
                var customBtns = '';
                if (sortMode === 'custom') {
                    customBtns = '<div class="custom-btns no-print">' +
                        '<button type="button" class="btn btn-small btn-ghost" data-up="' + esc(key) + '"' + (idx === 0 ? ' disabled' : '') + '>↑</button>' +
                        '<button type="button" class="btn btn-small btn-ghost" data-down="' + esc(key) + '"' + (idx === keys.length - 1 ? ' disabled' : '') + '>↓</button>' +
                        '</div>';
                }
                html += '<div class="item" data-key="' + esc(key) + '">' +
                    '<div class="item-top">' +
                        '<h3 class="item-name">' + esc(item.name || key) + '</h3>' +
                        customBtns +
                    '</div>' +
                    '<dl class="details">' +
                        '<div><dt>' + (isSweet ? 'Category' : 'Category') + '</dt><dd>' + esc(d.category) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Par level' : 'Par level') + '</dt><dd>' + esc(d.par) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Par by' : 'Par by') + '</dt><dd>' + esc(d.parBy) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Vendor' : 'Vendor') + '</dt><dd>' + esc(d.vendor) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Pack (# / case)' : 'Pack (# / case)') + '</dt><dd>' + esc(d.pack) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Size' : 'Size') + '</dt><dd>' + esc(d.size) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Unit' : 'Unit') + '</dt><dd>' + esc(d.unit) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Case price' : 'Case price') + '</dt><dd>' + esc(d.casePrice) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'SKU / item #' : 'SKU / item #') + '</dt><dd>' + esc(d.sku) + '</dd></div>' +
                        '<div><dt>' + (isSweet ? 'Pack description' : 'Pack description') + '</dt><dd>' + esc(d.packDesc) + '</dd></div>' +
                    '</dl>' +
                    '</div>';

                // Compact print row: Product | SKU | Vendor | Pack | Size | Unit | Case $ | Par
                printRows += '<tr>' +
                    '<td class="name">' + esc(item.name || key) + '</td>' +
                    '<td>' + esc(d.sku === '—' ? '' : d.sku) + '</td>' +
                    '<td>' + esc(d.vendor === '—' ? '' : d.vendor) + '</td>' +
                    '<td class="num">' + esc(d.pack === '—' ? '' : d.pack) + '</td>' +
                    '<td class="num">' + esc(d.size === '—' ? '' : d.size) + '</td>' +
                    '<td>' + esc(d.unit === '—' ? '' : d.unit) + '</td>' +
                    '<td class="num">' + esc(d.casePrice === '—' ? '' : d.casePrice) + '</td>' +
                    '<td class="num">' + esc(d.par) + '</td>' +
                    '</tr>';
            });
            root.innerHTML = html;

            printWrap.innerHTML =
                '<table class="print-table">' +
                '<thead><tr>' +
                '<th>Product</th><th>SKU / Item #</th><th>Vendor</th>' +
                '<th>Pack</th><th>Size</th><th>Unit</th><th>Case $</th><th>Par</th>' +
                '</tr></thead><tbody>' + printRows + '</tbody></table>';
        }

        function downloadExcel() {
            var keys = sortedKeys();
            if (!keys.length) {
                alert(isSweet ? 'Nothing to download yet' : 'Nothing to download yet');
                return;
            }
            // CSV with BOM — opens cleanly in Excel
            var headers = [
                'Name', 'Category', 'Par Level', 'Par By', 'Vendor',
                'Pack (# per case)', 'Size', 'Unit', 'Case Price', 'SKU / Item #', 'Pack Description'
            ];
            var rows = [headers.join(',')];
            function cell(v) {
                var s = String(v == null ? '' : v);
                if (/[",\n\r]/.test(s)) s = '"' + s.replace(/"/g, '""') + '"';
                return s;
            }
            keys.forEach(function (key) {
                var item = master.items[key];
                var d = detailRow(item);
                rows.push([
                    item.name || key, d.category, d.parRaw, d.parBy, item.vendor || '',
                    packOf(item), item.size || '', item.unit || '', d.casePriceRaw,
                    item.sku || '', d.packDesc
                ].map(cell).join(','));
            });
            var csv = '\uFEFF' + rows.join('\r\n');
            var blob = new Blob([csv], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            var stamp = new Date().toISOString().slice(0, 10);
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'pbj-product-list-' + stamp + '.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
            toast(isSweet ? 'Excel file ready 📥' : 'Excel file ready');
        }

        document.getElementById('sort-mode').addEventListener('change', function () {
            sortMode = this.value;
            try { localStorage.setItem(SORT_PREF_KEY, sortMode); } catch (e) {}
            render();
        });
        document.getElementById('search').addEventListener('input', function () {
            search = this.value;
            render();
        });
        document.getElementById('btn-excel').addEventListener('click', downloadExcel);
        document.getElementById('btn-print').addEventListener('click', function () {
            window.print();
        });
        document.getElementById('list').addEventListener('click', function (e) {
            var up = e.target.closest('[data-up]');
            var down = e.target.closest('[data-down]');
            if (!up && !down) return;
            var key = up ? up.dataset.up : down.dataset.down;
            var idx = customOrder.indexOf(key);
            if (idx === -1) return;
            if (up && idx > 0) {
                customOrder.splice(idx, 1);
                customOrder.splice(idx - 1, 0, key);
            } else if (down && idx < customOrder.length - 1) {
                customOrder.splice(idx, 1);
                customOrder.splice(idx + 1, 0, key);
            } else return;
            saveCustomOrder();
            render();
            toast(isSweet ? 'Custom order saved ✨' : 'Custom order saved');
        });

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyInvPerms);
            document.addEventListener('pbj-perms-ready', applyInvPerms);
    })();
    </script>
</body>
</html>
