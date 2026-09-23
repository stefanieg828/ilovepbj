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
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>

    <title><?php echo $is_sweet ? 'Inventory & Vendors' : 'Inventory & Vendors'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 90px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.6rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 14px 40px; max-width: 900px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px; }
        @media (max-width: 560px) { .stats-row { grid-template-columns: 1fr 1fr; } }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.35rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.8rem; opacity: 0.7; margin-top: 4px; }
        .section-label { font-size: 0.95rem; opacity: 0.65; margin: 18px 4px 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 520px) { .grid { grid-template-columns: 1fr; } }
        .card { background: white; border-radius: 16px; padding: 16px 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: block; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .card h3 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 1.15rem; margin: 8px 0 4px; }
        .card p { margin: 0; font-size: 0.82rem; opacity: 0.75; line-height: 1.35; }
        .card-icon { width: 52px; height: 52px; border-radius: 12px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
        .flow { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .flow-step { flex: 1; min-width: 90px; text-align: center; padding: 12px 8px; border-radius: 14px; font-size: 0.85rem; line-height: 1.3; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.06); }
        .flow-step strong { display: block; font-size: 0.75rem; opacity: 0.6; margin-bottom: 2px; }
        .pill { display: inline-block; margin-top: 8px; font-size: 0.72rem; border-radius: 999px; padding: 3px 8px; <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .pill.warn { <?php if ($is_sweet): ?>background: #FDECEA; color: #B71C1C;<?php else: ?>background: #FDECEA; color: #8B1A1A;<?php endif; ?> }
        .actions-bar { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
        .actions-bar a { flex: 1; min-width: 120px; text-align: center; border-radius: 14px; padding: 12px 16px; text-decoration: none; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .hint { font-size: 0.95rem; opacity: 0.75; margin: 0 0 10px; line-height: 1.4; }
    
        .card-icon { overflow: hidden; }
        .card-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
        <?php if ($is_sweet): ?>
        .card-icon.sweet-sticker {
            width: 72px; height: 72px; border-radius: 16px; background: #FFFBFA;
            padding: 4px; box-sizing: border-box;
        }
        .card-icon.sweet-sticker img { object-fit: contain; border-radius: 12px; }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
        <h1><?php echo $is_sweet ? 'Inventory & Vendors' : 'Inventory & Vendors'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Products → count → order by vendor' : 'Products, count, and order by vendor'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'House stock flow in three steps: set up products & pars, count on hand, then build vendor order lists. Phone numbers for reps also live in <a href="/admin/settings">Restaurant Settings → Phone list</a> 📦'
                : 'Three steps: set up products, count stock, build vendor orders. Vendor phones can also live under Restaurant Settings → Phone list.'; ?>
        </div>

        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-products">—</div><div class="lbl"><?php echo $is_sweet ? 'Products' : 'Products'; ?></div></div>
            <div class="stat"><div class="num" id="stat-low">—</div><div class="lbl"><?php echo $is_sweet ? 'Below par' : 'Below par'; ?></div></div>
            <div class="stat"><div class="num" id="stat-vendors">—</div><div class="lbl"><?php echo $is_sweet ? 'Vendors' : 'Vendors'; ?></div></div>
            <div class="stat"><div class="num" id="stat-counts">—</div><div class="lbl"><?php echo $is_sweet ? 'Count sessions' : 'Count sessions'; ?></div></div>
        </div>

        <div class="flow">
            <div class="flow-step"><strong>1</strong><?php echo $is_sweet ? 'Product setup' : 'Product setup'; ?></div>
            <div class="flow-step"><strong>2</strong><?php echo $is_sweet ? 'Count stock' : 'Count stock'; ?></div>
            <div class="flow-step"><strong>3</strong><?php echo $is_sweet ? 'Auto-order' : 'Auto-order'; ?></div>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Core flow' : 'Core flow'; ?></div>
        <div class="grid">
            <a href="/admin/product-setup" class="card">
                <?php pbj_render_card_icon('inventory-hub/product-setup', 'Product Setup', '🧩'); ?>
                <h3><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></h3>
                <p><?php echo $is_sweet ? 'Par, vendor, pack, case price & SKU' : 'Par, vendor, pack, case price, SKU'; ?></p>
                <span class="pill" id="pill-setup"><?php echo $is_sweet ? 'Start here' : 'Start here'; ?></span>
            </a>
            <a href="/admin/count" class="card">
                <?php pbj_render_card_icon('inventory-hub/count', 'Count Stock', '🔢'); ?>
                <h3><?php echo $is_sweet ? 'Count Stock' : 'Count Stock'; ?></h3>
                <p><?php echo $is_sweet ? 'Enter on-hand & save count sessions' : 'Enter on-hand and save count sessions'; ?></p>
            </a>
            <a href="/admin/auto-order" class="card">
                <?php pbj_render_card_icon('inventory-hub/auto-order', 'Auto-Order', '🤖'); ?>
                <h3><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></h3>
                <p><?php echo $is_sweet ? 'Vendor CSV · Print/PDF · email' : 'Vendor CSV, Print/PDF, email'; ?></p>
            </a>
            <a href="/admin/product-list" class="card">
                <?php pbj_render_card_icon('inventory-hub/product-list', 'Master Product List', '📃'); ?>
                <h3><?php echo $is_sweet ? 'Master Product List' : 'Product List'; ?></h3>
                <p><?php echo $is_sweet ? 'All products · pars · print & Excel' : 'All products, pars, print & Excel'; ?></p>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Vendors & templates' : 'Vendors & templates'; ?></div>
        <div class="grid">
            <a href="/admin/vendors" class="card">
                <?php pbj_render_card_icon('inventory-hub/vendors', 'Vendors', '🚚'); ?>
                <h3>Vendors</h3>
                <p><?php echo $is_sweet ? 'Account #, cut-off, min cases & links' : 'Account #, cut-off, min cases, links'; ?></p>
            </a>
            <a href="/admin/order-guides" class="card">
                <?php pbj_render_card_icon('inventory-hub/order-guides', 'Order Guides', '🛒'); ?>
                <h3>Order Guides</h3>
                <p><?php echo $is_sweet ? 'Vendor CSV & Print/PDF templates' : 'Vendor CSV & Print/PDF templates'; ?></p>
            </a>
            <a href="/admin/invoices" class="card" id="card-invoices">
                <?php pbj_render_card_icon('inventory-hub/invoices', 'Invoices', '🧾'); ?>
                <h3><?php echo $is_sweet ? 'Invoices' : 'Invoices'; ?></h3>
                <p><?php echo $is_sweet ? 'Scan / upload & apply costs to inventory' : 'Scan, upload, and apply costs'; ?></p>
                <span class="pill" id="pill-invoices" hidden></span>
            </a>
        </div>


        <div class="intro" id="invoice-cost-nudge" hidden style="margin-top:12px;"></div>
        <p class="hint" id="status-hint" style="margin-top:14px;"></p>

        <div class="actions-bar">
            <a href="/admin" class="btn-primary"><?php echo pbj_hub_label('admin'); ?></a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
    <script src="/ops-nudges.js?v=1"></script>
    <script>
    (function () {
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

        function load(key) {
            try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; }
        }
        // Keys used by Product Setup / Count / Vendors pages
        var products = [];
        var vendors = [];
        var counts = [];
        try {
            var ing = load('pbj_heat_ingredients_v1');
            if (ing && typeof ing === 'object') {
                if (Array.isArray(ing)) products = ing;
                else if (Array.isArray(ing.items)) products = ing.items;
                else if (Array.isArray(ing.products)) products = ing.products;
                else {
                    // map of id -> product
                    products = Object.keys(ing).map(function (k) { return ing[k]; }).filter(function (p) {
                        return p && typeof p === 'object' && (p.name || p.par != null || p.onHand != null);
                    });
                }
            }
        } catch (e) {}
        try {
            var ven = load('pbj_admin_vendors_v1');
            if (ven && Array.isArray(ven.vendors)) vendors = ven.vendors;
            else if (Array.isArray(ven)) vendors = ven;
        } catch (e) {}
        try {
            var cnt = load('pbj_inv_count_sessions_v1');
            if (cnt && Array.isArray(cnt.sessions)) counts = cnt.sessions;
            else if (cnt && Array.isArray(cnt)) counts = cnt;
            else if (cnt && Array.isArray(cnt.counts)) counts = cnt.counts;
        } catch (e) {}

        var low = 0;
        products.forEach(function (p) {
            if (!p || typeof p !== 'object') return;
            var on = parseFloat(p.onHand != null ? p.onHand : (p.count != null ? p.count : p.qty));
            var par = parseFloat(p.par);
            if (!isNaN(on) && !isNaN(par) && on < par) low++;
        });

        document.getElementById('stat-products').textContent = products.length ? String(products.length) : '0';
        document.getElementById('stat-low').textContent = products.length ? String(low) : '—';
        document.getElementById('stat-vendors').textContent = vendors.length ? String(vendors.length) : '0';
        document.getElementById('stat-counts').textContent = counts.length ? String(counts.length) : '0';

        var pill = document.getElementById('pill-setup');
        var hint = document.getElementById('status-hint');
        if (!products.length) {
            pill.textContent = isSweet ? 'Start here' : 'Start here';
            pill.className = 'pill warn';
            hint.textContent = isSweet
                ? 'No products yet — open Product Setup to add SKUs and pars, then count & order 💕'
                : 'No products yet. Start with Product Setup, then count and order.';
        } else if (low > 0) {
            pill.textContent = isSweet ? products.length + ' products' : products.length + ' products';
            pill.className = 'pill';
            hint.textContent = isSweet
                ? low + ' item' + (low === 1 ? '' : 's') + ' below par — Count Stock or Auto-Order when you’re ready 📦'
                : low + ' item(s) below par. Count or auto-order when ready.';
        } else {
            pill.textContent = isSweet ? products.length + ' products' : products.length + ' products';
            hint.textContent = isSweet
                ? 'Looking stocked — keep vendors & cut-offs updated under Vendors 🚚'
                : 'Looking stocked. Keep vendor cut-offs updated.';
        }

        function paintInvoiceNudge() {
            if (!window.PbjOpsNudges) return;
            window.PbjOpsNudges.fetchUnapplied().then(function (info) {
                var invPill = document.getElementById('pill-invoices');
                var nudge = document.getElementById('invoice-cost-nudge');
                if (!info || !info.count) {
                    if (invPill) invPill.hidden = true;
                    if (nudge) {
                        nudge.hidden = true;
                        nudge.innerHTML = '';
                    }
                    return;
                }
                if (invPill) {
                    invPill.hidden = false;
                    invPill.textContent = isSweet
                        ? (info.count + ' need costs')
                        : (info.count + ' unapplied');
                    invPill.className = 'pill warn';
                }
                if (nudge) {
                    nudge.hidden = false;
                    nudge.innerHTML = isSweet
                        ? ('🧾 <strong>' + info.count + '</strong> invoice' + (info.count === 1 ? '' : 's') +
                            ' not applied to inventory cost — <a href="/admin/invoices">Apply costs</a> so Product Setup &amp; recipes stay accurate 💕')
                        : (info.count + ' invoice(s) need cost apply. <a href="/admin/invoices">Open invoices</a>.');
                }
            });
        }
        paintInvoiceNudge();

                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyInvPerms);
            document.addEventListener('pbj-perms-ready', applyInvPerms);
})();
    </script>
</body>
</html>
