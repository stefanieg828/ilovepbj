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
    <title><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.95; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        @media (max-width: 640px) { .stats-row { grid-template-columns: 1fr 1fr; } }
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
        .search:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .item { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .item.low { border-left-color: #E57373; }
        .item.ok { border-left-color: #7BC67E; }
        .item-top { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 6px; }
        .item-name { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0; }
        .badge { border-radius: 999px; padding: 4px 10px; font-size: 0.8rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; white-space: nowrap; }
        .badge.low { background: #FDECEA; color: #B71C1C; }
        .badge.ok { background: #E8F8F1; color: #1F6B4A; }
        .meta-row { font-size: 0.9rem; opacity: 0.75; margin-bottom: 10px; line-height: 1.4; }
        .grid-fields { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 10px; }
        @media (min-width: 720px) { .grid-fields { grid-template-columns: repeat(4, 1fr); } }
        .field label { display: block; font-size: 0.78rem; opacity: 0.7; margin-bottom: 4px; line-height: 1.25; }
        .field input, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus, .field select:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .need { font-size: 0.95rem; margin-top: 4px; }
        .need.order { color: #C62828; font-weight: 600; }
        .empty { text-align: center; padding: 36px 20px; background: white; border-radius: 18px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .actions-bar { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 12px; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .item-actions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
        .count-mode .item .grid-fields > .field:not(.count-field) { display: none; }
        .count-banner { display: none; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 2px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; font-size: 0.95rem; line-height: 1.4; }
        .count-banner.show { display: block; }
        .hint { font-size: 0.92rem; opacity: 0.7; margin: 0 0 10px; line-height: 1.4; }
        .cat-order-row { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .cat-order-row:last-child { border-bottom: none; }
        .cat-order-row .cat-name { flex: 1; font-size: 1rem; }
        .cat-order-row .cat-btns { display: flex; gap: 6px; }
        details.cat-details { background: white; border-radius: 18px; padding: 14px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        details.cat-details summary { cursor: pointer; font-size: 1.05rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> list-style: none; }
        details.cat-details summary::-webkit-details-marker { display: none; }
        details.cat-details summary::before { content: '▸ '; }
        details.cat-details[open] summary::before { content: '▾ '; }
        .session { border-radius: 14px; padding: 12px 14px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .session h3 { margin: 0 0 4px; font-size: 1.05rem; }
        .session .meta { font-size: 0.9rem; opacity: 0.75; margin-bottom: 8px; line-height: 1.35; }
        .session-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .last-count { font-size: 0.95rem; opacity: 0.85; margin-top: 8px; }
        .flow-steps { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .flow-step { flex: 1; min-width: 100px; text-align: center; padding: 10px 8px; border-radius: 14px; font-size: 0.82rem; line-height: 1.25; text-decoration: none; color: inherit; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.06); opacity: 0.75; }
        .flow-step strong { display: block; font-size: 0.72rem; opacity: 0.65; margin-bottom: 2px; }
        .flow-step.active { opacity: 1; <?php if ($is_sweet): ?>background: #E55163; color: white; box-shadow: 0 4px 14px rgba(229,81,99,0.35);<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .flow-step.active strong { opacity: 0.9; color: inherit; }
        .flow-step:hover { opacity: 1; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory & Vendors' : 'Back to Inventory & Vendors'; ?></a>
        <h1><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Product setup — saved until you edit' : 'Product setup — saved until you edit'; ?></p>
    </div>

    <div class="content">
        <div class="flow-steps">
            <a class="flow-step active" href="/admin/product-setup"><strong>Step 1</strong><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
            <a class="flow-step" href="/admin/count"><strong>Step 2</strong><?php echo $is_sweet ? 'Count Stock' : 'Count Stock'; ?></a>
            <a class="flow-step" href="/admin/auto-order"><strong>Step 3</strong><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></a>
        </div>

        <div class="intro">
            <?php echo $is_sweet
                ? 'Step 1 of the order loop: set <strong>vendor, par (case/each), category, pack, size, unit, case price, location & SKU</strong> once — they <strong>auto-save</strong> and sync across the house. Next: <a href="/admin/count">Count Stock</a>, then <a href="/admin/auto-order">Auto-Order</a> 📦'
                : 'Step 1: set vendor, par, category, pack, size, unit, case price, location, and SKU. Syncs across devices. Next: <a href="/admin/count">Count Stock</a>, then <a href="/admin/auto-order">Auto-Order</a>.'; ?>
        </div>
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
                <div class="num" id="stat-low">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Below par' : 'Below par'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-novendor">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'No vendor' : 'No vendor'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-value">—</div>
                <div class="lbl"><?php echo $is_sweet ? 'On-hand $' : 'On-hand $'; ?></div>
            </div>
        </div>

        <div class="toolbar">
            <a href="/admin/count" class="btn btn-primary"><?php echo $is_sweet ? 'Next: Count Stock 📋' : 'Next: Count Stock'; ?></a>
            <a href="/admin/auto-order" class="btn btn-secondary"><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></a>
            <button type="button" class="btn btn-secondary" id="save-btn"><?php echo $is_sweet ? 'Save all' : 'Save all'; ?></button>
        </div>

        <div class="card" id="setup-note" style="margin-bottom:14px;">
            <p class="hint" style="margin:0;"><?php echo $is_sweet
                ? '✨ Fill in each product box (par, vendor, pack, etc.), then tap <strong>Save</strong> on that card. Pulled-from-recipe items stay as “Needs setup” until you save. Count Stock handles on-hand.'
                : 'Fill each product box, then tap Save on that card. Recipe-pulled items show Needs setup until saved.'; ?></p>
        </div>

        <div class="card" id="add-card">
            <h2><?php echo $is_sweet ? 'Add item' : 'Add item'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Anything not on a recipe — paper, chemicals, produce extras, dispenser syrup…' : 'Non-recipe items: paper, chemicals, produce, dispenser syrup, etc.'; ?></p>
            <div class="field-row">
                <div class="field" style="flex:1.4;"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="a-name" placeholder="<?php echo $is_sweet ? 'e.g. Mayo, to-go bags…' : 'Item name'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Category' : 'Category'; ?></label>
                    <select id="a-cat"></select>
                </div>
            </div>
            <div class="field-row">
                <div class="field"><label><?php echo $is_sweet ? 'Par level' : 'Par level'; ?></label><input id="a-par" type="number" step="any" min="0" placeholder="<?php echo $is_sweet ? 'e.g. 5' : 'e.g. 5'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Par is by' : 'Par is by'; ?></label>
                    <select id="a-par-by">
                        <option value="each"><?php echo $is_sweet ? 'Each' : 'Each'; ?></option>
                        <option value="case"><?php echo $is_sweet ? 'Case' : 'Case'; ?></option>
                    </select>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Vendor' : 'Vendor'; ?></label><input id="a-vendor" list="vendor-list" placeholder="<?php echo $is_sweet ? 'Distributor' : 'Vendor'; ?>"></div>
            </div>
            <div class="field-row">
                <div class="field"><label><?php echo $is_sweet ? 'Pack (# per case)' : 'Pack (# per case)'; ?></label><input id="a-pack" type="number" step="any" min="0" placeholder="<?php echo $is_sweet ? 'e.g. 6 bags/case' : 'e.g. 6'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Size (each pack)' : 'Size (each pack)'; ?></label><input id="a-size" type="number" step="any" min="0" placeholder="<?php echo $is_sweet ? 'e.g. 5' : 'e.g. 5'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Unit' : 'Unit'; ?></label><input id="a-unit" list="unit-list" placeholder="<?php echo $is_sweet ? 'lb, oz, ea…' : 'lb, oz, ea…'; ?>"></div>
            </div>
            <div class="field-row">
                <div class="field"><label><?php echo $is_sweet ? 'Case price ($)' : 'Case price ($)'; ?></label><input id="a-case-price" type="number" step="any" min="0" placeholder="0.00"></div>
                <div class="field"><label><?php echo $is_sweet ? 'SKU / item #' : 'SKU / item #'; ?></label><input id="a-sku" placeholder="<?php echo $is_sweet ? 'Vendor item #' : 'Optional'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Count location' : 'Count location'; ?></label>
                    <input id="a-location" list="location-list" placeholder="<?php echo $is_sweet ? 'Walk-in, Dry, Line…' : 'Walk-in, Dry, Line…'; ?>">
                </div>
            </div>
            <datalist id="location-list">
                <option value="Walk-in"><option value="Dry storage"><option value="Line"><option value="Freezer">
                <option value="Bar"><option value="Prep"><option value="Dish"><option value="Office">
            </datalist>
            <button type="button" class="btn btn-primary" id="add-btn" style="width:100%;"><?php echo $is_sweet ? '+ Add product' : '+ Add product'; ?></button>
        </div>
        <datalist id="unit-list">
            <option value="lb"><option value="oz"><option value="ea"><option value="gal"><option value="qt">
            <option value="ct"><option value="bag"><option value="tray"><option value="case"><option value="bunch">
        </datalist>
        <datalist id="vendor-list"></datalist>

        <input type="search" class="search" id="search" placeholder="<?php echo $is_sweet ? 'Search items…' : 'Search items…'; ?>">

        <details class="cat-details" id="cat-order-details">
            <summary><?php echo $is_sweet ? 'Category order (optional)' : 'Category order (optional)'; ?></summary>
            <p class="hint" style="margin-top:10px;"><?php echo $is_sweet ? 'Default is the usual distributor walk: Dairy → Meats → Frozen → … Reorder anytime, add a category you need, or Reset the standard nine (your custom ones stay at the end).' : 'Default matches a typical distributor guide. Reorder, add custom categories, or reset the standard nine (customs stay).'; ?></p>
            <div id="cat-order-list"></div>
            <div class="field-row" style="margin-top:12px; align-items:flex-end;">
                <div class="field" style="flex:1.6; margin-bottom:0;">
                    <label><?php echo $is_sweet ? 'Add a category' : 'Add a category'; ?></label>
                    <input id="cat-new-name" type="text" placeholder="<?php echo $is_sweet ? 'e.g. Bakery, Seafood, Bread…' : 'e.g. Bakery, Seafood…'; ?>" maxlength="48">
                </div>
                <button type="button" class="btn btn-primary btn-small" id="cat-add-btn" style="margin-bottom:0; white-space:nowrap;"><?php echo $is_sweet ? '+ Add' : '+ Add'; ?></button>
            </div>
            <button type="button" class="btn btn-ghost btn-small" id="cat-order-reset" style="margin-top:10px;"><?php echo $is_sweet ? 'Reset standard order' : 'Reset standard order'; ?></button>
        </details>

        <div class="filters" id="filters">
            <button type="button" class="filter-chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
            <button type="button" class="filter-chip" data-filter="low"><?php echo $is_sweet ? 'Below par' : 'Below par'; ?></button>
            <button type="button" class="filter-chip" data-filter="novendor"><?php echo $is_sweet ? 'No vendor' : 'No vendor'; ?></button>
        </div>
        <div class="filters" id="cat-filters" style="margin-top:-6px;"></div>

        <div id="inv-list"></div>

        <div class="card no-print" id="usage-par-card">
            <h2><?php echo $is_sweet ? 'Suggest pars from usage' : 'Suggest pars from usage'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Uses saved count sessions (same engine as Auto-Order). Sets blank or out-of-date pars to <strong>daily usage × cover days</strong>. You can still edit any card 💕'
                : 'From count history: par ≈ daily usage × cover days. Only fills blanks unless you force overwrite.'; ?></p>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Cover days' : 'Cover days'; ?></label>
                    <input type="number" id="usage-par-days" min="1" max="21" step="1" value="3">
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Only blank pars' : 'Only blank pars'; ?></label>
                    <select id="usage-par-mode">
                        <option value="blank"><?php echo $is_sweet ? 'Yes — leave existing pars' : 'Only blank'; ?></option>
                        <option value="all"><?php echo $is_sweet ? 'Overwrite all with usage' : 'Overwrite all'; ?></option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-primary" id="usage-par-btn" style="width:100%;"><?php echo $is_sweet ? '✨ Suggest pars from counts' : 'Suggest pars from counts'; ?></button>
            <p class="hint" id="usage-par-status" style="margin:10px 0 0;"></p>
        </div>

        <div class="actions-bar">
            <button type="button" class="btn btn-secondary" id="sync-btn"><?php echo $is_sweet ? 'Pull new recipe items' : 'Pull new recipe items'; ?></button>
            <a href="/admin/inventory" class="btn btn-secondary"><?php echo $is_sweet ? 'Inventory hub' : 'Inventory hub'; ?></a>
            <a href="/admin/count" class="btn btn-primary"><?php echo $is_sweet ? 'Next: Count Stock 📋' : 'Next: Count Stock'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const RECIPE_KEY = 'pbj_heat_recipes_v1';
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const VENDOR_KEY = 'pbj_admin_vendors_v1';
        const CAT_ORDER_KEY = 'pbj_inv_category_order_v1';
        const COUNT_KEY = 'pbj_inv_count_sessions_v1';
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

        // Distributor-style categories (standard walk order)
        var DEFAULT_CAT_ORDER = [
            'dairy',
            'meats',
            'frozen',
            'canned_dry',
            'paper_disposable',
            'chemical_janitorial',
            'supplies_equipment',
            'produce',
            'dispenser_beverage'
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
        // Map legacy categories from the first MVP
        var LEGACY_CAT_MAP = {
            food: 'canned_dry',
            paper: 'paper_disposable',
            janitorial: 'chemical_janitorial',
            other: 'supplies_equipment'
        };

        // Runtime: order + labels (defaults + custom)
        var catOrder = [];
        var catLabels = {};
        var customCats = {}; // slug -> label (user-added only)

        function normName(n) { return String(n || '').trim().toLowerCase().replace(/\s+/g, ' '); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
        }
        function toast(msg) {
            var el = document.getElementById('toast');
            if (msg) el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () {
                el.classList.remove('show');
                el.textContent = isSweet ? 'Saved 💾' : 'Saved';
            }, 1200);
        }
        function persistSetup(showToast) {
            collectRows();
            saveMaster(master);
            if (showToast) toast(isSweet ? 'Product setup saved 💾' : 'Product setup saved');
            renderStats();
        }
        function isBuiltinCat(slug) {
            return DEFAULT_CAT_ORDER.indexOf(slug) !== -1;
        }
        function catLabel(slug) {
            return catLabels[slug] || DEFAULT_CAT_LABELS[slug] || customCats[slug] || slug;
        }
        function rebuildCatLabels() {
            catLabels = {};
            DEFAULT_CAT_ORDER.forEach(function (c) { catLabels[c] = DEFAULT_CAT_LABELS[c]; });
            Object.keys(customCats).forEach(function (c) { catLabels[c] = customCats[c]; });
        }
        function slugifyCategory(name) {
            var base = String(name || '').trim().toLowerCase()
                .replace(/&/g, ' and ')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .replace(/_+/g, '_');
            if (!base) base = 'custom';
            if (base.length > 40) base = base.slice(0, 40).replace(/_+$/g, '');
            // avoid colliding with reserved filter tokens
            if (base === 'all' || base === 'low' || base === 'novendor' || base.indexOf('group_') === 0) {
                base = 'cat_' + base;
            }
            var slug = base;
            var n = 2;
            while (catLabels[slug] || customCats[slug] || DEFAULT_CAT_LABELS[slug]) {
                // if exact same label already exists, handled by caller
                slug = base + '_' + n;
                n++;
                if (n > 50) { slug = base + '_' + Date.now().toString(36); break; }
            }
            return slug;
        }
        function findCatByLabel(name) {
            var want = normName(name);
            var found = null;
            Object.keys(catLabels).forEach(function (slug) {
                if (normName(catLabels[slug]) === want) found = slug;
            });
            return found;
        }
        function loadCatState() {
            customCats = {};
            catOrder = DEFAULT_CAT_ORDER.slice();
            try {
                var r = JSON.parse(localStorage.getItem(CAT_ORDER_KEY) || 'null');
                // v1: plain array of slugs
                if (Array.isArray(r) && r.length) {
                    catOrder = mergeOrder(r, {});
                } else if (r && typeof r === 'object') {
                    if (r.custom && typeof r.custom === 'object') {
                        Object.keys(r.custom).forEach(function (slug) {
                            var lab = String(r.custom[slug] || '').trim();
                            if (lab && !isBuiltinCat(slug)) customCats[slug] = lab;
                        });
                    }
                    // also accept labels map
                    if (r.labels && typeof r.labels === 'object') {
                        Object.keys(r.labels).forEach(function (slug) {
                            if (isBuiltinCat(slug)) return;
                            var lab = String(r.labels[slug] || '').trim();
                            if (lab) customCats[slug] = lab;
                        });
                    }
                    rebuildCatLabels();
                    if (Array.isArray(r.order) && r.order.length) {
                        catOrder = mergeOrder(r.order, customCats);
                    } else {
                        catOrder = mergeOrder(DEFAULT_CAT_ORDER, customCats);
                    }
                    rebuildCatLabels();
                    return;
                }
            } catch (e) {}
            rebuildCatLabels();
            catOrder = mergeOrder(DEFAULT_CAT_ORDER, customCats);
        }
        function mergeOrder(preferred, customs) {
            rebuildCatLabels();
            var seen = {};
            var order = [];
            (preferred || []).forEach(function (c) {
                if (!c || seen[c]) return;
                if (DEFAULT_CAT_LABELS[c] || customs[c] || customCats[c]) {
                    order.push(c);
                    seen[c] = true;
                }
            });
            DEFAULT_CAT_ORDER.forEach(function (c) {
                if (!seen[c]) { order.push(c); seen[c] = true; }
            });
            Object.keys(customs || customCats).forEach(function (c) {
                if (!seen[c]) { order.push(c); seen[c] = true; }
            });
            return order;
        }
        function saveCatState() {
            localStorage.setItem(CAT_ORDER_KEY, JSON.stringify({
                order: catOrder.slice(),
                custom: Object.assign({}, customCats)
            }));
        }
        function normalizeCategory(cat) {
            if (!cat) return 'canned_dry';
            if (LEGACY_CAT_MAP[cat]) return LEGACY_CAT_MAP[cat];
            if (catLabels[cat] || DEFAULT_CAT_LABELS[cat] || customCats[cat]) return cat;
            // unknown slug: keep if it looks custom (don't wipe user data), else default
            if (String(cat).indexOf('custom_') === 0 || String(cat).indexOf('cat_') === 0) {
                if (!customCats[cat]) {
                    customCats[cat] = String(cat).replace(/^custom_/, '').replace(/_/g, ' ');
                    rebuildCatLabels();
                    if (catOrder.indexOf(cat) === -1) catOrder.push(cat);
                    saveCatState();
                }
                return cat;
            }
            return 'canned_dry';
        }
        function catRank(cat) {
            var i = catOrder.indexOf(normalizeCategory(cat));
            return i === -1 ? 999 : i;
        }
        function ensureItem(raw, fallbackName) {
            var item = raw && typeof raw === 'object' ? raw : {};
            // pack = # of bags/trays/packs per case (migrate from packSize)
            var pack = item.pack;
            if (pack === undefined || pack === null || pack === '') {
                pack = item.packSize !== undefined && item.packSize !== null ? item.packSize : '';
            }
            // casePrice primary; migrate from costPerUnit if needed
            var casePrice = item.casePrice;
            if (casePrice === undefined || casePrice === null || casePrice === '') {
                casePrice = item.costPerUnit !== undefined && item.costPerUnit !== null ? item.costPerUnit : '';
            }
            var packNum = parseFloat(pack);
            var caseNum = parseFloat(casePrice);
            // keep costPerUnit for costing/auto-order estimates = price per pack/unit when possible
            var costPerUnit = item.costPerUnit;
            if ((costPerUnit === undefined || costPerUnit === null || costPerUnit === '') && !isNaN(caseNum) && casePrice !== '') {
                if (!isNaN(packNum) && packNum > 0) {
                    costPerUnit = Math.round((caseNum / packNum) * 10000) / 10000;
                } else {
                    costPerUnit = casePrice;
                }
            }
            var parBy = item.parBy === 'case' ? 'case' : 'each';
            return {
                name: item.name || fallbackName || '',
                unit: item.unit || 'ea',
                size: item.size !== undefined && item.size !== null ? item.size : '',
                pack: pack,
                packSize: pack, // alias for Auto-Order / order rounding
                casePrice: casePrice,
                costPerUnit: costPerUnit !== undefined && costPerUnit !== null ? costPerUnit : '',
                onHand: item.onHand !== undefined && item.onHand !== null ? item.onHand : '',
                par: item.par !== undefined && item.par !== null ? item.par : '',
                parBy: parBy, // 'case' | 'each' — how par & on-hand are measured
                vendor: item.vendor || '',
                category: normalizeCategory(item.category),
                sku: item.sku || '',
                location: item.location || '',
                source: item.source || 'manual',
                // Costing Sheet conversion fields — must survive Product Setup re-save
                recipeUnitsPerPack: item.recipeUnitsPerPack !== undefined && item.recipeUnitsPerPack !== null ? item.recipeUnitsPerPack : '',
                recipeUnitLabel: item.recipeUnitLabel || '',
                directRecipeUnitCost: item.directRecipeUnitCost !== undefined && item.directRecipeUnitCost !== null ? item.directRecipeUnitCost : '',
                usableYieldPct: item.usableYieldPct !== undefined && item.usableYieldPct !== null ? item.usableYieldPct : '',
                enteredAt: item.enteredAt || undefined
            };
        }
        function parByOf(item) {
            return item && item.parBy === 'case' ? 'case' : 'each';
        }
        function parByWord(item, forQty) {
            // forQty: label next to a quantity (5 cases / 5 each)
            if (parByOf(item) === 'case') {
                var n = forQty != null ? parseFloat(forQty) : NaN;
                if (!isNaN(n) && n === 1) return isSweet ? 'case' : 'case';
                return isSweet ? 'cases' : 'cases';
            }
            return isSweet ? 'each' : 'each';
        }
        function syncDerivedPricing(item) {
            // After edits: packSize mirrors pack; costPerUnit derived from case ÷ pack
            item.packSize = item.pack;
            var packNum = parseFloat(item.pack);
            var caseNum = parseFloat(item.casePrice);
            if (!isNaN(caseNum) && item.casePrice !== '') {
                if (!isNaN(packNum) && packNum > 0) {
                    item.costPerUnit = Math.round((caseNum / packNum) * 10000) / 10000;
                } else {
                    item.costPerUnit = item.casePrice;
                }
            }
            return item;
        }
        function loadCountSessions() {
            try {
                var r = JSON.parse(localStorage.getItem(COUNT_KEY) || 'null');
                return r && Array.isArray(r.sessions) ? r : { sessions: [] };
            } catch (e) { return { sessions: [] }; }
        }
        function saveCountSessions(state) {
            localStorage.setItem(COUNT_KEY, JSON.stringify(state));
        }
        var countSessions = loadCountSessions();
        function catOptionsHtml(selected) {
            selected = normalizeCategory(selected);
            return catOrder.map(function (c) {
                return '<option value="' + c + '"' + (selected === c ? ' selected' : '') + '>' + esc(catLabel(c)) + '</option>';
            }).join('');
        }
        function fillAddCatSelect() {
            var sel = document.getElementById('a-cat');
            var keep = sel.value && catLabels[sel.value] ? sel.value : 'canned_dry';
            sel.innerHTML = catOptionsHtml(keep);
        }
        function renderCatOrderUi() {
            var root = document.getElementById('cat-order-list');
            root.innerHTML = catOrder.map(function (c, idx) {
                var upDis = idx === 0 ? ' disabled' : '';
                var downDis = idx === catOrder.length - 1 ? ' disabled' : '';
                var customBadge = !isBuiltinCat(c)
                    ? ' <span style="font-size:0.75rem;opacity:0.65;">' + (isSweet ? '(custom)' : '(custom)') + '</span>'
                    : '';
                var removeBtn = !isBuiltinCat(c)
                    ? '<button type="button" class="btn btn-small btn-danger" data-cat-remove="' + esc(c) + '" title="' + (isSweet ? 'Remove category' : 'Remove') + '">×</button>'
                    : '';
                return '<div class="cat-order-row" data-cat="' + esc(c) + '">' +
                    '<span class="cat-name">' + (idx + 1) + '. ' + esc(catLabel(c)) + customBadge + '</span>' +
                    '<span class="cat-btns">' +
                        '<button type="button" class="btn btn-small btn-ghost" data-cat-up="' + esc(c) + '"' + upDis + '>↑</button>' +
                        '<button type="button" class="btn btn-small btn-ghost" data-cat-down="' + esc(c) + '"' + downDis + '>↓</button>' +
                        removeBtn +
                    '</span></div>';
            }).join('');
        }
        function renderCatFilters() {
            var root = document.getElementById('cat-filters');
            root.innerHTML = catOrder.map(function (c) {
                var active = filter === c ? ' active' : '';
                return '<button type="button" class="filter-chip' + active + '" data-filter="' + esc(c) + '">' + esc(catLabel(c)) + '</button>';
            }).join('');
        }
        function refreshCategoryUi() {
            rebuildCatLabels();
            fillAddCatSelect();
            renderCatOrderUi();
            renderCatFilters();
        }
        function addCustomCategory(name) {
            name = String(name || '').trim();
            if (!name) {
                alert(isSweet ? 'Type a category name first 💕' : 'Enter a category name.');
                return false;
            }
            if (name.length > 48) {
                alert(isSweet ? 'Keep it under 48 characters' : 'Max 48 characters.');
                return false;
            }
            var existing = findCatByLabel(name);
            if (existing) {
                alert(isSweet ? '“' + catLabel(existing) + '” is already in your list' : 'That category already exists.');
                return false;
            }
            // slug: prefer clean name; if collides with builtin slug but different label, suffix
            var base = String(name).trim().toLowerCase()
                .replace(/&/g, ' and ')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .replace(/_+/g, '_');
            if (!base) base = 'custom';
            if (base.length > 40) base = base.slice(0, 40).replace(/_+$/g, '');
            if (base === 'all' || base === 'low' || base === 'novendor' || base.indexOf('group_') === 0) base = 'cat_' + base;
            var slug = base;
            if (isBuiltinCat(slug) || customCats[slug] || catLabels[slug]) {
                slug = 'custom_' + base;
                var n = 2;
                while (isBuiltinCat(slug) || customCats[slug] || catLabels[slug]) {
                    slug = 'custom_' + base + '_' + n;
                    n++;
                }
            }
            customCats[slug] = name;
            catOrder.push(slug);
            rebuildCatLabels();
            saveCatState();
            refreshCategoryUi();
            return slug;
        }
        function removeCustomCategory(slug) {
            if (isBuiltinCat(slug)) return;
            var label = catLabel(slug);
            var count = 0;
            Object.keys(master.items).forEach(function (k) {
                if (normalizeCategory(master.items[k].category) === slug) count++;
            });
            var msg = count
                ? (isSweet
                    ? 'Remove “' + label + '”? ' + count + ' item(s) will move to Canned & Dry.'
                    : 'Remove “' + label + '”? ' + count + ' item(s) will move to Canned & Dry.')
                : (isSweet ? 'Remove custom category “' + label + '”?' : 'Remove “' + label + '”?');
            if (!confirm(msg)) return;
            Object.keys(master.items).forEach(function (k) {
                if (master.items[k].category === slug) master.items[k].category = 'canned_dry';
            });
            saveMaster(master);
            delete customCats[slug];
            catOrder = catOrder.filter(function (c) { return c !== slug; });
            rebuildCatLabels();
            saveCatState();
            if (filter === slug) filter = 'all';
            refreshCategoryUi();
            render();
            toast(isSweet ? 'Category removed' : 'Category removed');
        }

        function loadRecipes() {
            try {
                var r = JSON.parse(localStorage.getItem(RECIPE_KEY) || 'null');
                return (r && r.categories) ? r.categories : [];
            } catch (e) { return []; }
        }
        function loadMaster() {
            try {
                var r = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                if (!r || typeof r.items !== 'object') return { items: {} };
                // migrate / normalize
                Object.keys(r.items).forEach(function (key) {
                    r.items[key] = ensureItem(r.items[key], key);
                });
                return r;
            } catch (e) { return { items: {} }; }
        }
        var invSync = null;
        function saveMaster(m, opts) {
            opts = opts || {};
            if (m && !opts.remote) m.structureAt = Date.now();
            localStorage.setItem(ING_KEY, JSON.stringify(m));
            if (invSync && !opts.remote && !opts.skipPush) {
                invSync.pushMaster(m);
            }
        }
        function touchItem(key) {
            if (master.items[key]) master.items[key].updatedAt = Date.now();
        }

        function loadVendors() {
            try {
                var v = JSON.parse(localStorage.getItem(VENDOR_KEY) || 'null');
                return (v && Array.isArray(v.vendors)) ? v.vendors : [];
            } catch (e) { return []; }
        }
        function fillVendorList() {
            var vendors = loadVendors();
            document.getElementById('vendor-list').innerHTML = vendors.map(function (x) {
                return '<option value="' + esc(x.name) + '">';
            }).join('');
        }

        function recipeUsage() {
            var map = {};
            loadRecipes().forEach(function (cat) {
                (cat.recipes || []).forEach(function (rec) {
                    (rec.ingredients || []).forEach(function (ing) {
                        var key = normName(ing.name);
                        if (!key) return;
                        if (!map[key]) map[key] = { name: String(ing.name).trim(), units: {}, recipes: [] };
                        if (ing.unit) map[key].units[ing.unit] = true;
                        if (map[key].recipes.indexOf(rec.title) === -1) map[key].recipes.push(rec.title);
                    });
                });
            });
            return map;
        }

        function syncFromRecipes() {
            // ONLY add new keys or fill empty name/unit — never overwrite vendor, par, price, sku, pack, category, onHand
            var usage = recipeUsage();
            Object.keys(usage).forEach(function (key) {
                if (!master.items[key]) {
                    master.items[key] = ensureItem({
                        name: usage[key].name,
                        unit: Object.keys(usage[key].units)[0] || 'ea',
                        category: 'canned_dry',
                        source: 'recipe'
                    }, usage[key].name);
                } else {
                    var existing = master.items[key];
                    // preserve all setup fields; only fill blanks
                    if (!existing.name) existing.name = usage[key].name;
                    if (!existing.unit) existing.unit = Object.keys(usage[key].units)[0] || 'ea';
                    if (existing.source !== 'manual') existing.source = 'recipe';
                    existing.category = normalizeCategory(existing.category);
                    master.items[key] = ensureItem(existing, usage[key].name);
                }
            });
            saveMaster(master);
            return usage;
        }

        loadCatState();
        var master = loadMaster();
        // migrate any legacy category values once
        Object.keys(master.items).forEach(function (k) {
            master.items[k].category = normalizeCategory(master.items[k].category);
        });
        saveMaster(master);
        var usage = syncFromRecipes();
        var filter = 'all';
        var search = '';
        fillVendorList();
        refreshCategoryUi();

        function isLow(item) {
            var onHand = parseFloat(item.onHand);
            var par = parseFloat(item.par);
            if (isNaN(onHand) || isNaN(par)) return false;
            return onHand < par;
        }
        function orderQty(item) {
            // Need is always in the same unit as par (case or each)
            var onHand = parseFloat(item.onHand);
            var par = parseFloat(item.par);
            if (isNaN(onHand) || isNaN(par)) return null;
            var need = par - onHand;
            if (need <= 0) return 0;
            if (parByOf(item) === 'case') {
                // order whole cases
                need = Math.ceil(need);
            } else {
                // by each: if pack (# per case) set, round up to full cases worth of eaches
                var pack = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
                if (!isNaN(pack) && pack > 0) {
                    need = Math.ceil(need / pack) * pack;
                }
            }
            return Math.round(need * 100) / 100;
        }

        function renderStats() {
            var keys = Object.keys(master.items);
            var low = 0, noVendor = 0, value = 0, hasValue = false;
            keys.forEach(function (k) {
                var item = master.items[k];
                if (isLow(item)) low++;
                if (!String(item.vendor || '').trim()) noVendor++;
                var onHand = parseFloat(item.onHand);
                var unitCost = parseFloat(item.costPerUnit);
                var casePrice = parseFloat(item.casePrice);
                var pack = parseFloat(item.pack);
                if (isNaN(unitCost) && !isNaN(casePrice) && item.casePrice !== '') {
                    unitCost = (!isNaN(pack) && pack > 0) ? (casePrice / pack) : casePrice;
                }
                if (!isNaN(onHand) && !isNaN(unitCost) && unitCost !== '') {
                    value += onHand * unitCost;
                    hasValue = true;
                }
            });
            document.getElementById('stat-total').textContent = keys.length;
            document.getElementById('stat-low').textContent = low;
            document.getElementById('stat-novendor').textContent = noVendor;
            document.getElementById('stat-value').textContent = hasValue ? money(value) : '—';
        }

        function collectItemEl(el) {
            if (!el) return null;
            var key = el.dataset.key;
            if (!key || !master.items[key]) return null;
            var parEl = el.querySelector('.f-par');
            var vendEl = el.querySelector('.f-vendor');
            var catEl = el.querySelector('.f-cat');
            var packEl = el.querySelector('.f-pack');
            var sizeEl = el.querySelector('.f-size');
            var unitEl = el.querySelector('.f-unit');
            var caseEl = el.querySelector('.f-case-price');
            var skuEl = el.querySelector('.f-sku');
            var locEl = el.querySelector('.f-location');
            var parByEl = el.querySelector('.f-par-by');
            // Never touch onHand on this page — that lives on Count Stock
            if (parEl) master.items[key].par = parEl.value;
            if (parByEl) master.items[key].parBy = parByEl.value === 'case' ? 'case' : 'each';
            if (vendEl) master.items[key].vendor = vendEl.value.trim();
            if (catEl) master.items[key].category = normalizeCategory(catEl.value);
            if (packEl) master.items[key].pack = packEl.value;
            if (sizeEl) master.items[key].size = sizeEl.value;
            if (unitEl) master.items[key].unit = unitEl.value.trim();
            if (caseEl) master.items[key].casePrice = caseEl.value;
            if (skuEl) master.items[key].sku = skuEl.value.trim();
            if (locEl) master.items[key].location = locEl.value.trim();
            syncDerivedPricing(master.items[key]);
            touchItem(key);
            return key;
        }
        function collectRows() {
            document.querySelectorAll('#inv-list .item').forEach(function (el) {
                collectItemEl(el);
            });
        }
        function saveItemCard(el) {
            var key = collectItemEl(el);
            if (!key) return;
            master.items[key].setupSaved = true;
            saveMaster(master);
            var name = (master.items[key] && master.items[key].name) || key;
            toast(isSweet ? 'Saved “' + name + '” 💾' : 'Saved “' + name + '”');
            render();
        }

        function setupReady(item) {
            // Ready when core fields filled; badge flips to “Setup saved” after explicit Save
            var filled = String(item.vendor || '').trim() &&
                item.par !== '' && item.par != null &&
                !isNaN(parseFloat(item.par));
            return filled && !!item.setupSaved;
        }
        function setupFilled(item) {
            return String(item.vendor || '').trim() &&
                item.par !== '' && item.par != null &&
                !isNaN(parseFloat(item.par));
        }

        function render() {
            renderStats();

            var root = document.getElementById('inv-list');
            var keys = Object.keys(master.items).sort(function (a, b) {
                var ca = catRank(master.items[a].category);
                var cb = catRank(master.items[b].category);
                if (ca !== cb) return ca - cb;
                return (master.items[a].name || a).localeCompare(master.items[b].name || b);
            });

            if (!keys.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No items yet — add products above, or put ingredients on recipe cards ✨'
                    : 'No items yet. Add items above or sync from recipes.') +
                    '<div style="margin-top:14px;"><a class="btn btn-primary" href="/BOH/recipe-cards">' + (isSweet ? 'Go to recipes' : 'Go to recipes') + '</a></div></div>';
                return;
            }

            var q = search.trim().toLowerCase();
            var filtered = keys.filter(function (key) {
                var item = master.items[key];
                var cat = normalizeCategory(item.category);
                if (q && (item.name || key).toLowerCase().indexOf(q) === -1 &&
                    (item.vendor || '').toLowerCase().indexOf(q) === -1 &&
                    (catLabel(cat) || '').toLowerCase().indexOf(q) === -1) return false;
                if (filter === 'low') return isLow(item);
                if (filter === 'novendor') return !String(item.vendor || '').trim();
                if (catLabels[filter] || customCats[filter] || DEFAULT_CAT_LABELS[filter]) return cat === filter;
                return true;
            });

            if (!filtered.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No matches for this filter' : 'No matches for this filter') + '</div>';
                return;
            }

            root.innerHTML = filtered.map(function (key) {
                var item = master.items[key];
                var low = isLow(item);
                var need = orderQty(item);
                var used = (usage[key] && usage[key].recipes) ? usage[key].recipes : [];
                var cat = normalizeCategory(item.category);
                var ready = setupReady(item);
                var packVal = item.pack !== '' && item.pack != null ? item.pack : (item.packSize || '');
                var sizeVal = item.size !== '' && item.size != null ? item.size : '';
                var caseVal = item.casePrice !== '' && item.casePrice != null ? item.casePrice : '';
                var by = parByOf(item);
                var packDesc = '';
                if (packVal !== '' || sizeVal !== '' || item.unit) {
                    packDesc = (packVal !== '' ? packVal + '×' : '') +
                        (sizeVal !== '' ? sizeVal : '') +
                        (item.unit ? ' ' + item.unit : '') +
                        (packVal !== '' ? (isSweet ? ' per case' : ' per case') : '');
                }
                var metaBits = [catLabel(cat)];
                if (item.par !== '' && item.par != null) {
                    metaBits.push((isSweet ? 'Par ' : 'Par ') + item.par + ' ' + parByWord(item, item.par));
                }
                if (item.vendor) metaBits.push(item.vendor);
                if (packDesc) metaBits.push(packDesc.trim());
                if (caseVal !== '' && !isNaN(parseFloat(caseVal))) metaBits.push((isSweet ? 'Case price ' : 'Case price ') + money(parseFloat(caseVal)));
                if (item.sku) metaBits.push((isSweet ? 'SKU ' : 'SKU ') + item.sku);
                if (item.location) metaBits.push((isSweet ? '📍 ' : '📍 ') + item.location);
                if (item.onHand !== '' && item.onHand != null) {
                    metaBits.push((isSweet ? 'Last count: ' : 'Last count: ') + item.onHand + ' ' + parByWord(item, item.onHand));
                }
                if (used.length) metaBits.push((isSweet ? 'Used in: ' : 'Used in: ') + used.slice(0, 3).join(', ') + (used.length > 3 ? '…' : ''));
                else if (item.source === 'manual') metaBits.push(isSweet ? 'Manual item' : 'Manual item');

                var catOptions = catOptionsHtml(cat);
                var filled = setupFilled(item);
                var badge = ready
                    ? '<span class="badge ok">' + (isSweet ? 'Setup saved' : 'Setup saved') + '</span>'
                    : (filled
                        ? '<span class="badge">' + (isSweet ? 'Tap Save ↓' : 'Tap Save') + '</span>'
                        : '<span class="badge">' + (isSweet ? 'Needs setup' : 'Needs setup') + '</span>');
                if (low) badge = '<span class="badge low">' + (isSweet ? 'Below par' : 'Below par') + '</span>' + ' ' + badge;

                var parByOpts = '<option value="each"' + (by === 'each' ? ' selected' : '') + '>' + (isSweet ? 'Each' : 'Each') + '</option>' +
                    '<option value="case"' + (by === 'case' ? ' selected' : '') + '>' + (isSweet ? 'Case' : 'Case') + '</option>';

                return '<div class="item ' + (ready ? 'ok' : '') + (low ? ' low' : '') + '" data-key="' + esc(key) + '">' +
                    '<div class="item-top">' +
                        '<h3 class="item-name">' + esc(item.name || key) + '</h3>' +
                        badge +
                    '</div>' +
                    '<div class="meta-row">' + esc(metaBits.join(' · ')) + '</div>' +
                    '<div class="grid-fields">' +
                        '<div class="field"><label>' + (isSweet ? 'Par level' : 'Par level') + '</label>' +
                            '<input class="f-par" type="number" step="any" min="0" value="' + esc(item.par !== '' && item.par != null ? item.par : '') + '"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Par is by' : 'Par is by') + '</label>' +
                            '<select class="f-par-by">' + parByOpts + '</select></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Vendor' : 'Vendor') + '</label>' +
                            '<input class="f-vendor" list="vendor-list" value="' + esc(item.vendor || '') + '" placeholder="' + (isSweet ? 'Distributor' : 'Vendor') + '"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Category' : 'Category') + '</label>' +
                            '<select class="f-cat">' + catOptions + '</select></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Pack (# per case)' : 'Pack (# per case)') + '</label>' +
                            '<input class="f-pack" type="number" step="any" min="0" value="' + esc(packVal) + '" placeholder="' + (isSweet ? 'bags/trays in a case' : 'e.g. 6') + '"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Size (each pack)' : 'Size (each pack)') + '</label>' +
                            '<input class="f-size" type="number" step="any" min="0" value="' + esc(sizeVal) + '" placeholder="' + (isSweet ? 'e.g. 5' : 'e.g. 5') + '"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Unit' : 'Unit') + '</label>' +
                            '<input class="f-unit" list="unit-list" type="text" value="' + esc(item.unit || '') + '" placeholder="lb, oz, ea…"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Case price ($)' : 'Case price ($)') + '</label>' +
                            '<input class="f-case-price" type="number" step="any" min="0" value="' + esc(caseVal) + '" placeholder="0.00"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'SKU / item #' : 'SKU / item #') + '</label>' +
                            '<input class="f-sku" type="text" value="' + esc(item.sku || '') + '" placeholder="' + (isSweet ? 'Vendor item #' : 'Optional') + '"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Count location' : 'Count location') + '</label>' +
                            '<input class="f-location" list="location-list" type="text" value="' + esc(item.location || '') + '" placeholder="' + (isSweet ? 'Walk-in, Dry, Line…' : 'Walk-in, Dry, Line…') + '"></div>' +
                    '</div>' +
                    '<div class="need" style="opacity:0.75;">' +
                        (isSweet
                            ? 'Par “by case” = count whole cases. Par “by each” = count bags/trays/packs. On-hand uses the same.'
                            : 'Par by case or each — on-hand is counted in the same unit.') +
                        (need != null && need > 0
                            ? ' <span class="order">' + (isSweet ? 'Would order ~ ' : 'Would order ~ ') + need + ' ' + parByWord(item, need) + '</span>'
                            : '') +
                    '</div>' +
                    '<div class="item-actions">' +
                        '<button type="button" class="btn btn-small btn-primary" data-save-item="' + esc(key) + '" style="flex:1;min-width:120px;">' +
                            (isSweet ? 'Save' : 'Save') +
                        '</button>' +
                        '<button type="button" class="btn btn-small btn-danger" data-del="' + esc(key) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div>' +
                    '</div>';
            }).join('');
        }

        function setFilter(newFilter) {
            collectRows();
            filter = newFilter;
            document.querySelectorAll('#filters .filter-chip, #cat-filters .filter-chip').forEach(function (c) {
                c.classList.toggle('active', c.dataset.filter === filter);
            });
            render();
        }
        document.getElementById('filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.filter-chip');
            if (!chip) return;
            setFilter(chip.dataset.filter);
        });
        document.getElementById('cat-filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.filter-chip');
            if (!chip) return;
            setFilter(chip.dataset.filter);
        });

        document.getElementById('cat-order-list').addEventListener('click', function (e) {
            var rm = e.target.closest('[data-cat-remove]');
            if (rm) {
                removeCustomCategory(rm.dataset.catRemove);
                return;
            }
            var up = e.target.closest('[data-cat-up]');
            var down = e.target.closest('[data-cat-down]');
            var slug = up ? up.dataset.catUp : (down ? down.dataset.catDown : null);
            if (!slug) return;
            var idx = catOrder.indexOf(slug);
            if (idx === -1) return;
            if (up && idx > 0) {
                catOrder.splice(idx, 1);
                catOrder.splice(idx - 1, 0, slug);
            } else if (down && idx < catOrder.length - 1) {
                catOrder.splice(idx, 1);
                catOrder.splice(idx + 1, 0, slug);
            } else return;
            saveCatState();
            refreshCategoryUi();
            render();
            toast(isSweet ? 'Category order saved ✨' : 'Category order saved');
        });
        document.getElementById('cat-order-reset').addEventListener('click', function () {
            // Restore standard nine in default order; keep custom categories at the end
            var customs = catOrder.filter(function (c) { return !isBuiltinCat(c); });
            catOrder = DEFAULT_CAT_ORDER.slice().concat(customs);
            saveCatState();
            refreshCategoryUi();
            setFilter(filter);
            toast(isSweet ? 'Standard order restored (customs kept) 💕' : 'Standard order restored (customs kept)');
        });
        document.getElementById('cat-add-btn').addEventListener('click', function () {
            var input = document.getElementById('cat-new-name');
            var slug = addCustomCategory(input.value);
            if (slug) {
                input.value = '';
                toast(isSweet ? 'Category added ✨' : 'Category added');
                render();
            }
        });
        document.getElementById('cat-new-name').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('cat-add-btn').click();
            }
        });

        document.getElementById('search').addEventListener('input', function () {
            collectRows();
            search = this.value;
            render();
        });

        document.getElementById('save-btn').addEventListener('click', function () {
            // Save all visible cards and mark each as setupSaved
            document.querySelectorAll('#inv-list .item').forEach(function (el) {
                var key = collectItemEl(el);
                if (key && master.items[key]) master.items[key].setupSaved = true;
            });
            saveMaster(master);
            toast(isSweet ? 'All products saved 💾' : 'All products saved');
            render();
        });

        document.getElementById('sync-btn').addEventListener('click', function () {
            collectRows();
            saveMaster(master);
            var before = Object.keys(master.items).length;
            usage = syncFromRecipes();
            var after = Object.keys(master.items).length;
            render();
            toast(isSweet
                ? (after > before ? 'Added ' + (after - before) + ' new item(s) from recipes ✨' : 'No new recipe items — your setup is untouched')
                : (after > before ? 'Added ' + (after - before) + ' new items' : 'No new items; setup unchanged'));
        });

        document.getElementById('add-btn').addEventListener('click', function () {
            var name = document.getElementById('a-name').value.trim();
            if (!name) {
                alert(isSweet ? 'Give the item a name first 💕' : 'Enter an item name.');
                return;
            }
            var key = normName(name);
            if (master.items[key]) {
                if (!confirm(isSweet ? 'That item already exists — update it with these values?' : 'Item already exists. Update it?')) return;
            }
            master.items[key] = ensureItem({
                name: name,
                par: document.getElementById('a-par').value,
                parBy: document.getElementById('a-par-by').value === 'case' ? 'case' : 'each',
                vendor: document.getElementById('a-vendor').value.trim(),
                category: document.getElementById('a-cat').value,
                pack: document.getElementById('a-pack').value,
                size: document.getElementById('a-size').value,
                unit: document.getElementById('a-unit').value.trim() || 'ea',
                casePrice: document.getElementById('a-case-price').value,
                sku: document.getElementById('a-sku').value.trim(),
                location: (document.getElementById('a-location') && document.getElementById('a-location').value.trim()) || '',
                source: 'manual',
                enteredAt: (master.items[key] && master.items[key].enteredAt) ? master.items[key].enteredAt : Date.now()
            }, name);
            syncDerivedPricing(master.items[key]);
            saveMaster(master);
            document.getElementById('a-name').value = '';
            document.getElementById('a-par').value = '';
            document.getElementById('a-par-by').value = 'each';
            document.getElementById('a-vendor').value = '';
            document.getElementById('a-pack').value = '';
            document.getElementById('a-size').value = '';
            document.getElementById('a-unit').value = '';
            document.getElementById('a-case-price').value = '';
            document.getElementById('a-sku').value = '';
            if (document.getElementById('a-location')) document.getElementById('a-location').value = '';
            toast(isSweet ? 'Product added ✨' : 'Product added');
            render();
        });

        // Draft values persist on change; explicit Save marks the card done
        document.getElementById('inv-list').addEventListener('change', function (e) {
            if (e.target.closest('[data-save-item]')) return;
            var itemEl = e.target.closest('.item');
            if (!itemEl) return;
            var key = itemEl.dataset.key;
            collectItemEl(itemEl);
            if (master.items[key]) master.items[key].setupSaved = false;
            saveMaster(master);
        });

        document.getElementById('inv-list').addEventListener('click', function (e) {
            var saveBtn = e.target.closest('[data-save-item]');
            if (saveBtn) {
                var card = saveBtn.closest('.item');
                if (card) saveItemCard(card);
                return;
            }
            var btn = e.target.closest('[data-del]');
            if (!btn) return;
            var key = btn.dataset.del;
            var name = (master.items[key] && master.items[key].name) || key;
            if (!confirm(isSweet ? 'Remove “' + name + '” from product list?' : 'Remove “' + name + '”?')) return;
            collectRows();
            delete master.items[key];
            saveMaster(master);
            toast(isSweet ? 'Removed' : 'Removed');
            render();
        });

        // Save before leaving page
        window.addEventListener('beforeunload', function () {
            try { collectRows(); saveMaster(master); } catch (e) {}
        });

        function buildDailyUsageMap() {
            var sessions = [];
            try {
                var r = JSON.parse(localStorage.getItem(COUNT_KEY) || 'null');
                if (r && Array.isArray(r.sessions)) sessions = r.sessions.slice();
            } catch (e) {}
            sessions = sessions.filter(function (s) {
                return s && s.counts && typeof s.counts === 'object' && s.at;
            }).sort(function (a, b) { return (a.at || 0) - (b.at || 0); });
            var sums = {};
            for (var i = 1; i < sessions.length; i++) {
                var a = sessions[i - 1], b = sessions[i];
                var days = (b.at - a.at) / (1000 * 60 * 60 * 24);
                if (!(days > 0.2) || days > 45) continue;
                Object.keys(b.counts).forEach(function (k) {
                    var ca = parseFloat(a.counts[k]);
                    var cb = parseFloat(b.counts[k]);
                    if (isNaN(ca) || isNaN(cb)) return;
                    var drop = ca - cb;
                    if (drop <= 0) return;
                    if (!sums[k]) sums[k] = { total: 0, n: 0 };
                    sums[k].total += drop / days;
                    sums[k].n += 1;
                });
            }
            var map = {};
            Object.keys(sums).forEach(function (k) {
                map[k] = sums[k].total / sums[k].n;
            });
            return { map: map, sessions: sessions.length };
        }

        document.getElementById('usage-par-btn').addEventListener('click', function () {
            var pack = buildDailyUsageMap();
            var status = document.getElementById('usage-par-status');
            if (pack.sessions < 2) {
                if (status) status.textContent = isSweet
                    ? 'Need 2+ saved count sessions first (Count Stock → Save count session).'
                    : 'Need 2+ count sessions first.';
                return;
            }
            var cover = parseFloat(document.getElementById('usage-par-days').value);
            if (isNaN(cover) || cover < 1) cover = 3;
            var onlyBlank = document.getElementById('usage-par-mode').value !== 'all';
            var updated = 0;
            Object.keys(pack.map).forEach(function (key) {
                if (!master.items[key]) return;
                var daily = pack.map[key];
                if (!(daily > 0)) return;
                var suggested = Math.ceil(daily * cover * 100) / 100;
                if (parByOf(master.items[key]) === 'case') suggested = Math.ceil(suggested);
                var cur = master.items[key].par;
                var blank = cur === '' || cur == null || isNaN(parseFloat(cur));
                if (onlyBlank && !blank) return;
                master.items[key].par = suggested;
                master.items[key].updatedAt = Date.now();
                master.items[key].parFromUsage = true;
                updated++;
            });
            saveMaster(master);
            render();
            if (status) {
                status.textContent = isSweet
                    ? ('Updated ' + updated + ' par(s) from ' + pack.sessions + ' sessions · cover ' + cover + 'd ✨')
                    : (updated + ' pars updated from ' + pack.sessions + ' sessions');
            }
            toast(isSweet ? 'Pars suggested ✨' : 'Pars updated');
        });

        render();
        if (window.PbjInvSync) {
            invSync = window.PbjInvSync.wire({
                statusEl: 'inv-sync-pill',
                getMaster: function () { return master; },
                setMaster: function (next, meta) {
                    master = next && next.items ? next : { items: (next && next.items) || {} };
                    // normalize
                    Object.keys(master.items || {}).forEach(function (key) {
                        master.items[key] = ensureItem(master.items[key], key);
                    });
                    saveMaster(master, { remote: true, skipPush: true });
                    render();
                },
                onMasterRemote: function () {
                    toast(isSweet ? 'Updated from house sync ✨' : 'Updated from house sync');
                }
            });
        } else {
            var pill = document.getElementById('inv-sync-text');
            if (pill) pill.textContent = isSweet ? 'Local only' : 'Local only';
        }
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyInvPerms);
            document.addEventListener('pbj-perms-ready', applyInvPerms);
    })();
    </script>
</body>
</html>
