<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Order Guides' : 'Order Guides'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.5rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 110px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .line { display: flex; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; align-items: center; }
        .line:last-child { border-bottom: none; }
        .guide { border-radius: 14px; padding: 14px; margin-bottom: 12px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .guide h3 { margin: 0 0 6px; font-size: 1.15rem; }
        .meta { font-size: 0.9rem; opacity: 0.75; margin-bottom: 8px; }
        .empty { text-align: center; padding: 24px; opacity: 0.8; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .print-only { display: none; }
        .guide-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
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
            .back-link, .toolbar, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .card, .intro { display: none !important; }
            .print-only { display: block !important; }
            #vendor-print-root { display: block !important; padding: 0 8px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory & Vendors' : 'Back to Inventory & Vendors'; ?></a>
        <h1><?php echo $is_sweet ? 'Order Guides' : 'Order Guides'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Build lists from inventory gaps' : 'Build order lists from inventory'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'For day-to-day ordering, use the main loop: <a href="/admin/product-setup">Product Setup</a> → <a href="/admin/count">Count Stock</a> → <a href="/admin/auto-order">Auto-Order</a>. This page is for saved templates & custom guides 📋'
                : 'Day-to-day: Product Setup → Count Stock → Auto-Order. This page is for reusable templates.'; ?>
        </div>
        <div class="toolbar">
            <button type="button" class="btn btn-primary" id="pull-low"><?php echo $is_sweet ? 'Pull below-par items' : 'Pull below-par items'; ?></button>
            <button type="button" class="btn btn-primary" id="export-draft-csv"><?php echo $is_sweet ? 'Vendor CSV 📥' : 'Vendor CSV'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-draft-btn"><?php echo $is_sweet ? 'Print / PDF' : 'Print / PDF'; ?></button>
            <a href="/admin/auto-order" class="btn btn-secondary"><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></a>
            <a href="/admin/count" class="btn btn-secondary"><?php echo $is_sweet ? 'Count Stock' : 'Count Stock'; ?></a>
            <a href="/admin/product-setup" class="btn btn-secondary"><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Current draft' : 'Current draft'; ?></h2>
            <div class="field-row">
                <div class="field"><label><?php echo $is_sweet ? 'Guide name' : 'Guide name'; ?></label><input id="g-name" placeholder="<?php echo $is_sweet ? 'e.g. Sysco Tuesday' : 'e.g. Sysco Tuesday'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Vendor' : 'Vendor'; ?></label><input id="g-vendor" list="vendor-list" placeholder="<?php echo $is_sweet ? 'Optional' : 'Optional'; ?>"></div>
            </div>
            <datalist id="vendor-list"></datalist>
            <div id="draft-lines"></div>
            <div class="field-row" style="margin-top:12px;">
                <div class="field"><label><?php echo $is_sweet ? 'Item' : 'Item'; ?></label><input id="l-name" placeholder="<?php echo $is_sweet ? 'Ingredient / product' : 'Item'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Qty' : 'Qty'; ?></label><input id="l-qty" type="number" step="any" min="0"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Unit' : 'Unit'; ?></label><input id="l-unit" placeholder="case or each"></div>
            </div>
            <button type="button" class="btn btn-secondary" id="add-line" style="width:100%;margin-bottom:10px;"><?php echo $is_sweet ? '+ Add line' : '+ Add line'; ?></button>
            <button type="button" class="btn btn-primary" id="save-guide" style="width:100%;"><?php echo $is_sweet ? 'Save order guide ✨' : 'Save order guide'; ?></button>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Saved guides' : 'Saved guides'; ?></h2>
            <div id="saved"></div>
        </div>
        <div class="actions-bar">
            <a href="/admin/inventory" class="btn btn-secondary"><?php echo $is_sweet ? 'Inventory hub' : 'Inventory hub'; ?></a>
            <a href="/admin/auto-order" class="btn btn-primary"><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></a>
        </div>
    </div>
    <div class="print-only" id="vendor-print-root" aria-hidden="true"></div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        const KEY = 'pbj_admin_order_guides_v1';
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const VENDOR_KEY = 'pbj_admin_vendors_v1';
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

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function load() { try { var r = JSON.parse(localStorage.getItem(KEY) || 'null'); return r && Array.isArray(r.guides) ? r : { guides: [] }; } catch (e) { return { guides: [] }; } }
        function save(t) { localStorage.setItem(KEY, JSON.stringify(state)); if (t) { var el = document.getElementById('toast'); el.classList.add('show'); setTimeout(function () { el.classList.remove('show'); }, 1100); } }

        var state = load();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyInvPerms);
            document.addEventListener('pbj-perms-ready', applyInvPerms);
        var draft = [];

        try {
            var v = JSON.parse(localStorage.getItem(VENDOR_KEY) || 'null');
            if (v && Array.isArray(v.vendors)) {
                document.getElementById('vendor-list').innerHTML = v.vendors.map(function (x) {
                    return '<option value="' + esc(x.name) + '">';
                }).join('');
            }
        } catch (e) {}

        function renderDraft() {
            var root = document.getElementById('draft-lines');
            if (!draft.length) {
                root.innerHTML = '<p class="hint">' + (isSweet ? 'No lines yet — pull from inventory or add below.' : 'No lines yet.') + '</p>';
                return;
            }
            root.innerHTML = draft.map(function (line, idx) {
                return '<div class="line"><span>' + esc(line.name) + ' <span style="opacity:0.7">· ' + esc(line.qty) + ' ' + esc(line.unit || '') + '</span></span>' +
                    '<button type="button" class="btn btn-small btn-danger" data-rm="' + idx + '">×</button></div>';
            }).join('');
        }

        function renderSaved() {
            var root = document.getElementById('saved');
            if (!state.guides.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No saved guides yet' : 'No saved guides yet') + '</div>';
                return;
            }
            root.innerHTML = state.guides.slice().reverse().map(function (g) {
                var when = new Date(g.at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                var lines = (g.lines || []).map(function (l) {
                    return '<div class="line"><span>' + esc(l.name) + '</span><span>' + esc(l.qty) + ' ' + esc(l.unit || '') + '</span></div>';
                }).join('');
                return '<div class="guide"><h3>' + esc(g.name) + '</h3>' +
                    '<div class="meta">' + esc(when) + (g.vendor ? ' · ' + esc(g.vendor) : '') + ' · ' + (g.lines || []).length + ' items</div>' +
                    lines +
                    '<div class="guide-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-export="' + esc(g.id) + '">' + (isSweet ? 'Vendor CSV' : 'Vendor CSV') + '</button>' +
                    '<button type="button" class="btn btn-small btn-ghost" data-print="' + esc(g.id) + '">' + (isSweet ? 'Print / PDF' : 'Print / PDF') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-del="' + esc(g.id) + '">' + (isSweet ? 'Remove guide' : 'Remove guide') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        document.getElementById('pull-low').addEventListener('click', function () {
            try {
                var inv = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                if (!inv || !inv.items) {
                    alert(isSweet ? 'No products found yet — set pars on Product Setup first 💕' : 'No products found yet. Finish Product Setup first.');
                    return;
                }
                var vendorFilter = document.getElementById('g-vendor').value.trim().toLowerCase();
                var added = 0;
                Object.keys(inv.items).forEach(function (key) {
                    var item = inv.items[key];
                    var onHand = parseFloat(item.onHand);
                    var par = parseFloat(item.par);
                    if (isNaN(onHand) || isNaN(par) || onHand >= par) return;
                    var itemVendor = String(item.vendor || '').trim();
                    if (vendorFilter && itemVendor.toLowerCase() !== vendorFilter) return;
                    var need = par - onHand;
                    var parBy = item.parBy === 'case' ? 'case' : 'each';
                    if (parBy === 'case') {
                        need = Math.ceil(need);
                    } else {
                        var pack = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
                        if (!isNaN(pack) && pack > 0) need = Math.ceil(need / pack) * pack;
                    }
                    need = Math.round(need * 100) / 100;
                    var exists = draft.some(function (l) { return l.name.toLowerCase() === (item.name || key).toLowerCase(); });
                    if (!exists) {
                        draft.push({
                            name: item.name || key,
                            qty: need,
                            unit: parBy, // case | each (canonical for vendor export)
                            vendor: itemVendor,
                            parBy: parBy,
                            onHand: isNaN(onHand) ? '' : onHand,
                            par: isNaN(par) ? '' : par,
                            sku: item.sku || ''
                        });
                        added++;
                    }
                });
                renderDraft();
                alert(added ? (isSweet ? 'Added ' + added + ' below-par items ✨' : 'Added ' + added + ' items.') : (isSweet ? 'Nothing below par right now' : 'Nothing below par.'));
            } catch (e) {
                alert(isSweet ? 'Could not read inventory' : 'Could not read inventory');
            }
        });

        document.getElementById('add-line').addEventListener('click', function () {
            var name = document.getElementById('l-name').value.trim();
            if (!name) return;
            var unitRaw = document.getElementById('l-unit').value.trim().toLowerCase();
            var unitCanon = unitRaw.indexOf('case') === 0 ? 'case' : (unitRaw ? unitRaw : 'each');
            if (unitCanon !== 'case' && unitCanon !== 'each') {
                // keep typed unit for display, but vendor export will map non-case → each
                unitCanon = unitRaw || 'each';
            }
            draft.push({
                name: name,
                qty: document.getElementById('l-qty').value || '',
                unit: unitCanon.indexOf('case') === 0 ? 'case' : (unitCanon === 'each' || !unitCanon ? 'each' : unitCanon),
                parBy: unitCanon.indexOf('case') === 0 ? 'case' : 'each',
                vendor: document.getElementById('g-vendor').value.trim()
            });
            document.getElementById('l-name').value = '';
            document.getElementById('l-qty').value = '';
            document.getElementById('l-unit').value = '';
            renderDraft();
        });

        document.getElementById('draft-lines').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-rm]'); if (!btn) return;
            draft.splice(parseInt(btn.dataset.rm, 10), 1);
            renderDraft();
        });

        document.getElementById('save-guide').addEventListener('click', function () {
            if (!draft.length) {
                alert(isSweet ? 'Add at least one line first' : 'Add at least one line first');
                return;
            }
            var name = document.getElementById('g-name').value.trim() || (isSweet ? 'Order guide' : 'Order guide');
            state.guides.push({
                id: uid(),
                name: name,
                vendor: document.getElementById('g-vendor').value.trim(),
                lines: draft.slice(),
                at: Date.now()
            });
            save(true);
            draft = [];
            document.getElementById('g-name').value = '';
            document.getElementById('g-vendor').value = '';
            renderDraft();
            renderSaved();
        });


        function orderUnitCanon(line) {
            var raw = String((line && (line.parBy || line.unit)) || 'each').toLowerCase();
            if (raw.indexOf('case') === 0) return 'case';
            return 'each';
        }
        function csvEscape(c) {
            var s = String(c == null ? '' : c);
            if (/[",\n]/.test(s)) s = '"' + s.replace(/"/g, '""') + '"';
            return s;
        }
        function guideLinesForExport(guideOrLines, fallbackVendor) {
            var lines = Array.isArray(guideOrLines) ? guideOrLines : (guideOrLines && guideOrLines.lines) || [];
            var guideVendor = (!Array.isArray(guideOrLines) && guideOrLines && guideOrLines.vendor) ? guideOrLines.vendor : (fallbackVendor || '');
            return lines.map(function (l) {
                return {
                    vendor: l.vendor || guideVendor || (isSweet ? 'Unassigned vendor' : 'Unassigned vendor'),
                    name: l.name || '',
                    sku: l.sku || '',
                    qty: l.qty,
                    unit: orderUnitCanon(l),
                    onHand: l.onHand != null && l.onHand !== '' ? l.onHand : '',
                    par: l.par != null && l.par !== '' ? l.par : ''
                };
            });
        }
        function linesToVendorCsv(lines) {
            var header = ['Vendor', 'Item', 'SKU', 'OrderQty', 'OrderUnit', 'OnHand', 'Par'];
            var rows = [header.join(',')];
            var sorted = (lines || []).slice().sort(function (a, b) {
                var va = String(a.vendor || '').localeCompare(String(b.vendor || ''));
                if (va) return va;
                return String(a.name || '').localeCompare(String(b.name || ''));
            });
            sorted.forEach(function (l) {
                rows.push([l.vendor, l.name, l.sku || '', l.qty, orderUnitCanon(l), l.onHand, l.par].map(csvEscape).join(','));
            });
            return '\uFEFF' + rows.join('\r\n');
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
        function buildVendorPrintHtml(lines, title) {
            var date = new Date().toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
            var byVendor = {};
            (lines || []).forEach(function (l) {
                var v = l.vendor || (isSweet ? 'Unassigned vendor' : 'Unassigned vendor');
                if (!byVendor[v]) byVendor[v] = [];
                byVendor[v].push(l);
            });
            var keys = Object.keys(byVendor).sort();
            if (!keys.length) return '<p>' + (isSweet ? 'Nothing to print' : 'Nothing to print') + '</p>';
            var html = '<div class="vendor-sheet-meta">' + esc(title || (isSweet ? 'Vendor order guide' : 'Vendor order guide')) + ' · ' + esc(date) + ' · ilovepbj ops</div>';
            keys.forEach(function (vendor) {
                var rows = byVendor[vendor].map(function (l) {
                    return '<tr>' +
                        '<td>' + esc(l.name) + '</td>' +
                        '<td>' + esc(l.sku || '') + '</td>' +
                        '<td class="num">' + esc(l.qty) + '</td>' +
                        '<td>' + esc(orderUnitCanon(l)) + '</td>' +
                        '<td class="num">' + esc(l.onHand) + '</td>' +
                        '<td class="num">' + esc(l.par) + '</td>' +
                    '</tr>';
                }).join('');
                html += '<div class="vendor-sheet-block">' +
                    '<div class="vendor-sheet-title">' + esc(vendor) + '</div>' +
                    '<table class="vendor-sheet"><thead><tr>' +
                    '<th>Item</th><th>SKU</th><th>Qty</th><th>Order unit</th><th>On hand</th><th>Par</th>' +
                    '</tr></thead><tbody>' + rows + '</tbody></table></div>';
            });
            return html;
        }
        function prepareVendorPrint(lines, title) {
            var root = document.getElementById('vendor-print-root');
            if (!root) return;
            root.innerHTML = buildVendorPrintHtml(lines, title);
        }

        document.getElementById('export-draft-csv').addEventListener('click', function () {
            if (!canP('admin.inventory.view') && !canP('admin.inventory.edit')) {
                alert(isSweet ? 'No permission to view inventory' : 'No permission');
                return;
            }
            if (!draft.length) {
                alert(isSweet ? 'Add or pull lines first' : 'Nothing to export');
                return;
            }
            var vendor = document.getElementById('g-vendor').value.trim();
            var lines = guideLinesForExport(draft, vendor);
            var stamp = new Date().toISOString().slice(0, 10);
            downloadCsv('pbj-order-guide-' + stamp + '.csv', linesToVendorCsv(lines));
            var el = document.getElementById('toast');
            el.textContent = isSweet ? 'Vendor CSV ready 📥' : 'CSV downloaded';
            el.classList.add('show'); setTimeout(function () { el.classList.remove('show'); }, 1200);
        });

        document.getElementById('print-draft-btn').addEventListener('click', function () {
            if (!draft.length) {
                alert(isSweet ? 'Add or pull lines first' : 'Nothing to print');
                return;
            }
            var vendor = document.getElementById('g-vendor').value.trim();
            var name = document.getElementById('g-name').value.trim() || (isSweet ? 'Order guide draft' : 'Order guide draft');
            prepareVendorPrint(guideLinesForExport(draft, vendor), name);
            window.print();
        });

        document.getElementById('saved').addEventListener('click', function (e) {
            var del = e.target.closest('[data-del]');
            if (del) {
                state.guides = state.guides.filter(function (g) { return g.id !== del.dataset.del; });
                save(true); renderSaved();
                return;
            }
            var exp = e.target.closest('[data-export]');
            if (exp) {
                var g = state.guides.find(function (x) { return x.id === exp.dataset.export; });
                if (!g) return;
                var lines = guideLinesForExport(g);
                if (!lines.length) { alert(isSweet ? 'Guide is empty' : 'Guide is empty'); return; }
                var safe = String(g.name || 'guide').replace(/[^\\w\\-]+/g, '-').slice(0, 40);
                downloadCsv('pbj-order-guide-' + safe + '.csv', linesToVendorCsv(lines));
                var el = document.getElementById('toast');
                el.textContent = isSweet ? 'Vendor CSV ready 📥' : 'CSV downloaded';
                el.classList.add('show'); setTimeout(function () { el.classList.remove('show'); }, 1200);
                return;
            }
            var prv = e.target.closest('[data-print]');
            if (prv) {
                var gp = state.guides.find(function (x) { return x.id === prv.dataset.print; });
                if (!gp) return;
                var plines = guideLinesForExport(gp);
                if (!plines.length) { alert(isSweet ? 'Guide is empty' : 'Guide is empty'); return; }
                prepareVendorPrint(plines, gp.name || 'Order guide');
                window.print();
            }
        });

        renderDraft();
        renderSaved();
    })();
    </script>
</body>
</html>
