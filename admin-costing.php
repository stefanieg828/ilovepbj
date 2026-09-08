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
    <title><?php echo $is_sweet ? 'Costing Sheet' : 'Costing Sheet'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .content { padding: 24px 16px; max-width: 820px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .tabs { display: flex; gap: 10px; margin-bottom: 16px; }
        .tab { flex: 1; border: none; border-radius: 14px; padding: 14px 10px; font-size: 1rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .panel { display: none; } .panel.active { display: block; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.45rem; margin: 0 0 12px; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.4rem; line-height: 1.15; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.82rem; opacity: 0.7; margin-top: 4px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus, .field select:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .recipe-cost { border-radius: 14px; padding: 14px; margin-bottom: 12px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .recipe-cost h3 { margin: 0 0 6px; font-size: 1.15rem; }
        .recipe-cost .totals { display: flex; flex-wrap: wrap; gap: 12px 20px; margin: 8px 0 10px; font-size: 1.05rem; }
        .recipe-cost .totals strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .line { display: flex; justify-content: space-between; gap: 10px; padding: 8px 0; border-bottom: 1px dashed <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; font-size: 0.95rem; align-items: flex-start; }
        .line:last-child { border-bottom: none; }
        .line .line-left { flex: 1; min-width: 0; }
        .line .line-math { font-size: 0.82rem; opacity: 0.7; margin-top: 2px; line-height: 1.3; }
        .warn { color: #C62828; font-size: 0.88rem; }
        .ok-note { color: #1F6B4A; font-size: 0.85rem; }
        .muted { opacity: 0.7; font-size: 0.92rem; }
        .empty { text-align: center; padding: 28px 16px; opacity: 0.85; line-height: 1.45; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .conv-card { border-radius: 14px; padding: 14px; margin-bottom: 12px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .conv-card h3 { margin: 0 0 4px; font-size: 1.15rem; }
        .conv-card .meta { font-size: 0.88rem; opacity: 0.7; margin-bottom: 10px; line-height: 1.35; }
        .conv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        @media (min-width: 640px) { .conv-grid { grid-template-columns: 1fr 1fr 1fr; } }
        .conv-result { margin-top: 10px; padding: 10px 12px; border-radius: 12px; font-size: 0.95rem; line-height: 1.4; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .conv-result strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .search { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; margin-bottom: 12px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .suggest-banner { margin-top: 8px; padding: 10px 12px; border-radius: 12px; font-size: 0.92rem; line-height: 1.4; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        .badge-good { background: #C8E6C9; color: #1B5E20; }
        .badge-mid { background: #FFF9C4; color: #F57F17; }
        .badge-high { background: #FFCDD2; color: #B71C1C; }
        .badge-na { background: #ECEFF1; color: #546E7A; }
        .eng-row { display: flex; flex-wrap: wrap; gap: 8px 14px; margin-top: 6px; font-size: 0.95rem; }
        @media print {
            .back-link, .toolbar, .tabs, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .btn, .search { display: none !important; }
            body { background: white; padding-bottom: 0; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card, .recipe-cost, .conv-card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH/recipes" class="back-link">← <?php echo $is_sweet ? 'Back to Menu & Standardized Recipes' : 'Back to Menu & Standardized Recipes'; ?></a>
        <h1><?php echo $is_sweet ? 'Costing Sheet' : 'Costing Sheet'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Food cost from recipe ingredients' : 'Food cost from recipe ingredients'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Recipes often use different units than the case you buy (e.g. 8 sticks vs 4/5 lb bags). On <strong>Prices & conversions</strong>, set case price + recipe units per pack. For produce & meat that isn\'t 100% usable, set <strong>usable yield %</strong> (e.g. tomato 96%, cantaloupe 60%) so EP cost = AP cost ÷ yield. Chart: <a href="/BOH/yields">Produce & Meat Yields</a>. Linked <a href="/BOH/menu">Menu</a> prices show food-cost %. Invoice snaps update case prices → plates re-cost live. <strong>Theo vs actual</strong> food $ lives on <a href="/admin/pnl">P&amp;L</a> once sales are logged 💰'
                : 'Case→recipe conversions + yields. Invoice costs feed case prices. Theoretical vs actual food cost on <a href="/admin/pnl">P&amp;L</a> when sales exist.'; ?>
        </div>

        <div class="stats-row">
            <div class="stat">
                <div class="num" id="stat-recipes">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Recipes' : 'Recipes'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-ings">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Ingredients' : 'Ingredients'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-priced">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'With prices' : 'With prices'; ?></div>
            </div>
        </div>

        <div class="tabs">
            <button type="button" class="tab active" data-tab="recipes"><?php echo $is_sweet ? '📖 By recipe' : 'By recipe'; ?></button>
            <button type="button" class="tab" data-tab="prices"><?php echo $is_sweet ? '💲 Prices & conversions' : 'Prices & conversions'; ?></button>
        </div>

        <div class="panel active" id="panel-recipes">
            <div class="toolbar">
                <a href="/BOH/recipe-cards" class="btn btn-secondary"><?php echo $is_sweet ? 'Edit recipes' : 'Edit recipes'; ?></a>
                <a href="/BOH/menu" class="btn btn-secondary"><?php echo $is_sweet ? 'Menu engineering' : 'Menu engineering'; ?></a>
                <a href="/admin/pmix" class="btn btn-secondary"><?php echo $is_sweet ? 'PMIX' : 'PMIX'; ?></a>
                <a href="/admin/waste" class="btn btn-secondary"><?php echo $is_sweet ? 'Waste' : 'Waste'; ?></a>
                <a href="/admin/product-setup" class="btn btn-secondary"><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
                <button type="button" class="btn btn-secondary" id="print-recipes-btn" data-perm="boh.recipes.costing_view"><?php echo $is_sweet ? 'Print costing' : 'Print costing'; ?></button>
            </div>
            <div class="card" id="eng-summary" style="display:none;">
                <h2><?php echo $is_sweet ? 'Menu engineering snapshot' : 'Menu engineering snapshot'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'From linked menu prices + recipe plate cost. Target food-cost band defaults to under 30% (green), 30–35% watch, over 35% high.' : 'Linked menu prices + plate cost. Green &lt;30%, watch 30–35%, high &gt;35%.'; ?></p>
                <div class="stats-row" style="margin-bottom:0;">
                    <div class="stat"><div class="num" id="eng-avg">—</div><div class="lbl"><?php echo $is_sweet ? 'Avg FC %' : 'Avg FC %'; ?></div></div>
                    <div class="stat"><div class="num" id="eng-high">0</div><div class="lbl"><?php echo $is_sweet ? 'High FC' : 'High FC'; ?></div></div>
                    <div class="stat"><div class="num" id="eng-contrib">—</div><div class="lbl"><?php echo $is_sweet ? 'Avg $ contrib' : 'Avg contrib'; ?></div></div>
                </div>
            </div>
            <div id="recipe-costs"></div>
        </div>

        <div class="panel" id="panel-prices">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Case → recipe unit converter' : 'Case → recipe unit converter'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'Example: case is <strong>4 × 5 lb bags</strong>, each bag ≈ <strong>55 sticks</strong>, case price <strong>$48</strong>. Recipe calls for <strong>8 ea</strong> → we cost 8/220 of the case. <strong>Usable yield %</strong> adjusts for trim waste (cantaloupe 60% → true cost is higher). Leave blank or 100 for full use. Chart: <a href="/BOH/yields">Yields</a>.'
                    : 'Set packs/case, recipe units per pack, case price, and optional usable yield % (AP→EP). See <a href="/BOH/yields">Yields</a>.'; ?></p>
                <div class="toolbar" style="margin-bottom:12px;">
                    <button type="button" class="btn btn-primary" id="suggest-yields-btn" data-perm="boh.recipes.costing_edit"><?php echo $is_sweet ? '✨ Suggest yields for blanks' : 'Suggest yields for blanks'; ?></button>
                    <a href="/BOH/yields" class="btn btn-secondary"><?php echo $is_sweet ? 'Yield chart' : 'Yield chart'; ?></a>
                </div>
                <input type="search" class="search" id="price-search" placeholder="<?php echo $is_sweet ? 'Search ingredients…' : 'Search ingredients…'; ?>">
                <datalist id="yield-presets">
                    <option value="100" label="100% full use"></option>
                    <option value="96" label="Tomato ~96%"></option>
                    <option value="95" label="Cucumber / zucchini ~95%"></option>
                    <option value="91" label="Ginger / unpeeled apple ~91%"></option>
                    <option value="89" label="Onion ~89%"></option>
                    <option value="82" label="Bell pepper ~82%"></option>
                    <option value="81" label="Carrot / potato ~81%"></option>
                    <option value="75" label="Romaine / bone-in steak ~75%"></option>
                    <option value="67" label="Avocado ~67%"></option>
                    <option value="65" label="Whole chicken / banana ~65%"></option>
                    <option value="60" label="Cantaloupe ~60%"></option>
                    <option value="55" label="Shrimp shell-on ~55%"></option>
                    <option value="52" label="Watermelon / pineapple ~52%"></option>
                    <option value="50" label="Whole fish ~50%"></option>
                </datalist>
                <div id="price-list"></div>
                <button type="button" class="btn btn-primary" id="save-prices-btn" data-perm="boh.recipes.costing_edit" style="width:100%;margin-top:12px;"><?php echo $is_sweet ? 'Save conversions 💾' : 'Save conversions'; ?></button>
            </div>
        </div>

        <div class="actions-bar">
            <a href="/BOH/recipes" class="btn btn-primary"><?php echo $is_sweet ? 'Back to Menu & Standardized Recipes' : 'Back to Menu & Standardized Recipes'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Prices saved 💾' : 'Prices saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/food-cost-shared.js?v=3"></script>

    <script>
    (function () {
        const RECIPE_KEY = 'pbj_heat_recipes_v1';
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const MENU_KEY = 'pbj_menu_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyCostPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var edit = canP('boh.recipes.costing_edit');
                var view = canP('boh.recipes.costing_view') || edit;
                document.querySelectorAll('#panel-prices input, #panel-prices select, #panel-prices textarea').forEach(function(el){ el.disabled = !edit; });
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('cost-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="cost-denied">No permission to view costing.</div>');
                    }
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof renderEquip === 'function') renderEquip(); } catch (e) {}
                try { if (typeof renderList === 'function') renderList(); } catch (e) {}
            }


        function normName(n) { return String(n || '').trim().toLowerCase().replace(/\s+/g, ' '); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
        }

        // Keyword → AP→EP yield suggestions (longest key wins)
        var YIELD_MAP = [
            { k: 'cherry tomato', y: 98, l: 'Cherry / grape tomato' },
            { k: 'grape tomato', y: 98, l: 'Cherry / grape tomato' },
            { k: 'cantaloupe', y: 60, l: 'Cantaloupe' },
            { k: 'honeydew', y: 46, l: 'Honeydew' },
            { k: 'watermelon', y: 52, l: 'Watermelon' },
            { k: 'pineapple', y: 52, l: 'Pineapple' },
            { k: 'avocado', y: 67, l: 'Avocado' },
            { k: 'banana', y: 65, l: 'Banana' },
            { k: 'strawberry', y: 88, l: 'Strawberry' },
            { k: 'tomato', y: 96, l: 'Tomato, whole' },
            { k: 'apple', y: 76, l: 'Apple, peeled' },
            { k: 'orange', y: 52, l: 'Orange, sections' },
            { k: 'lemon', y: 45, l: 'Lemon / lime' },
            { k: 'lime', y: 45, l: 'Lemon / lime' },
            { k: 'onion', y: 89, l: 'Onion' },
            { k: 'garlic', y: 87, l: 'Garlic' },
            { k: 'ginger', y: 91, l: 'Ginger root' },
            { k: 'carrot', y: 81, l: 'Carrot' },
            { k: 'celery', y: 69, l: 'Celery' },
            { k: 'bell pepper', y: 82, l: 'Bell pepper' },
            { k: 'pepper', y: 82, l: 'Bell pepper' },
            { k: 'jalape', y: 87, l: 'Jalapeño' },
            { k: 'cucumber', y: 95, l: 'Cucumber' },
            { k: 'zucchini', y: 95, l: 'Zucchini' },
            { k: 'potato', y: 81, l: 'Potato, peeled' },
            { k: 'broccoli', y: 61, l: 'Broccoli' },
            { k: 'cauliflower', y: 45, l: 'Cauliflower' },
            { k: 'asparagus', y: 53, l: 'Asparagus' },
            { k: 'mushroom', y: 97, l: 'Mushrooms' },
            { k: 'romaine', y: 75, l: 'Romaine' },
            { k: 'iceberg', y: 73, l: 'Iceberg' },
            { k: 'lettuce', y: 74, l: 'Lettuce, head' },
            { k: 'spinach', y: 88, l: 'Spinach' },
            { k: 'kale', y: 70, l: 'Kale' },
            { k: 'cabbage', y: 80, l: 'Cabbage' },
            { k: 'cilantro', y: 85, l: 'Cilantro' },
            { k: 'parsley', y: 85, l: 'Parsley' },
            { k: 'basil', y: 80, l: 'Basil' },
            { k: 'ground beef', y: 100, l: 'Ground beef' },
            { k: 'ground turkey', y: 100, l: 'Ground turkey' },
            { k: 'chicken breast boneless', y: 98, l: 'Chicken breast B/S' },
            { k: 'chicken breast', y: 70, l: 'Chicken breast bone-in' },
            { k: 'chicken thigh', y: 95, l: 'Chicken thigh B/S' },
            { k: 'whole chicken', y: 65, l: 'Chicken, whole' },
            { k: 'chicken', y: 65, l: 'Chicken, whole' },
            { k: 'turkey breast', y: 95, l: 'Turkey breast' },
            { k: 'bacon', y: 100, l: 'Bacon (raw)' },
            { k: 'shrimp shell', y: 55, l: 'Shrimp shell-on' },
            { k: 'shrimp peeled', y: 100, l: 'Shrimp peeled' },
            { k: 'shrimp', y: 55, l: 'Shrimp shell-on' },
            { k: 'salmon', y: 65, l: 'Salmon, whole' },
            { k: 'fish fillet', y: 100, l: 'Fish fillet' },
            { k: 'scallop', y: 100, l: 'Scallops' }
        ];
        function suggestYieldForName(name) {
            var n = normName(name);
            if (!n) return null;
            var best = null;
            YIELD_MAP.forEach(function (row) {
                if (n.indexOf(row.k) !== -1) {
                    if (!best || row.k.length > best.k.length) best = row;
                }
            });
            return best ? { yield: best.y, label: best.l, key: best.k } : null;
        }
        function fcBadge(pct, reason) {
            if (pct == null || isNaN(pct)) {
                var why = reason ? (' · ' + reason) : '';
                return '<span class="badge badge-na">' + (isSweet ? 'No FC yet' : 'No FC') + why + '</span>';
            }
            if (pct < 30) return '<span class="badge badge-good">' + pct + '% FC · good</span>';
            if (pct <= 35) return '<span class="badge badge-mid">' + pct + '% FC · watch</span>';
            return '<span class="badge badge-high">' + pct + '% FC · high</span>';
        }
        function suggestedPricesHtml(portionCost, recipeId, menuItemId) {
            var FC = window.PbjFoodCost;
            if (!FC || !FC.suggestedSellPrices) return '';
            var sug = FC.suggestedSellPrices(portionCost);
            if (!sug) return '';
            var tip = '';
            var menu = null;
            if (menuItemId) {
                var items = loadMenu();
                menu = items.find(function (x) { return String(x.id) === String(menuItemId); }) || null;
            }
            var hasSell = menu && !isNaN(parseFloat(menu.price)) && parseFloat(menu.price) > 0;
            if (!hasSell) {
                tip = '<span class="muted" style="font-size:0.88rem;">' +
                    (isSweet ? 'Tip: apply mid (~27.5% FC) to set the linked menu price' : 'Tip: apply mid (~27.5% FC) as sell price') +
                    '</span>';
            }
            return '<div class="suggest-banner" data-suggest-price>' +
                (isSweet ? 'Suggested menu price: ' : 'Suggested menu price: ') +
                '<strong>' + money(sug.at30) + '–' + money(sug.at25) + '</strong>' +
                ' <span class="muted">(25–30% FC)</span>' +
                ' · mid <strong>' + money(sug.at275) + '</strong>' +
                '<button type="button" class="btn btn-small btn-primary" data-act="apply-menu-price"' +
                ' data-recipe-id="' + esc(recipeId || '') + '"' +
                ' data-menu-id="' + esc(menuItemId || '') + '"' +
                ' data-price="' + sug.at275 + '">' +
                (isSweet ? 'Apply mid' : 'Apply mid') + '</button>' +
                tip +
                '</div>';
        }
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function saveMenuItems(items) {
            localStorage.setItem(MENU_KEY, JSON.stringify({ items: items }));
        }
        function setRecipeMenuItemId(recipeId, menuItemId) {
            if (!recipeId) return false;
            try {
                var raw = JSON.parse(localStorage.getItem(RECIPE_KEY) || 'null');
                if (!raw || !Array.isArray(raw.categories)) return false;
                var found = false;
                raw.categories.forEach(function (cat) {
                    (cat.recipes || []).forEach(function (rec) {
                        if (String(rec.id) === String(recipeId)) {
                            rec.menuItemId = menuItemId;
                            found = true;
                        }
                    });
                });
                if (found) localStorage.setItem(RECIPE_KEY, JSON.stringify(raw));
                return found;
            } catch (e) { return false; }
        }
        function loadMenu() {
            try {
                var r = JSON.parse(localStorage.getItem(MENU_KEY) || 'null');
                return r && Array.isArray(r.items) ? r.items : [];
            } catch (e) { return []; }
        }
        function menuPriceForRecipe(rec) {
            if (!rec || !rec.menuItemId) return null;
            var m = loadMenu().find(function (x) { return x.id === rec.menuItemId; });
            if (!m) return null;
            var p = parseFloat(m.price);
            return isNaN(p) ? null : { price: p, name: m.name };
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
                return r;
            } catch (e) { return { items: {} }; }
        }
        function saveMaster(m) { localStorage.setItem(ING_KEY, JSON.stringify(m)); }

        function allRecipes() {
            var out = [];
            loadRecipes().forEach(function (cat) {
                (cat.recipes || []).forEach(function (rec) {
                    out.push({ cat: cat.title, recipe: rec });
                });
            });
            return out;
        }

        function packOf(item) {
            if (!item) return '';
            if (item.pack !== undefined && item.pack !== null && item.pack !== '') return item.pack;
            return item.packSize != null ? item.packSize : '';
        }
        function casePriceOf(item) {
            if (!item) return '';
            if (item.casePrice !== undefined && item.casePrice !== null && item.casePrice !== '') return item.casePrice;
            return '';
        }
        function ensureMasterFromRecipes(master, recipes) {
            recipes.forEach(function (row) {
                (row.recipe.ingredients || []).forEach(function (ing) {
                    var key = normName(ing.name);
                    if (!key) return;
                    if (!master.items[key]) {
                        master.items[key] = {
                            name: String(ing.name).trim(),
                            unit: ing.unit || 'ea',
                            costPerUnit: '',
                            onHand: '',
                            par: '',
                            vendor: '',
                            pack: '',
                            casePrice: '',
                            recipeUnitsPerPack: '',
                            recipeUnitLabel: ing.unit || 'ea',
                            directRecipeUnitCost: '',
                            usableYieldPct: ''
                        };
                    } else {
                        // seed recipe unit label from recipes if empty — never overwrite saved conversion fields
                        if (!master.items[key].recipeUnitLabel && ing.unit) {
                            master.items[key].recipeUnitLabel = ing.unit;
                        }
                        if (master.items[key].recipeUnitsPerPack === undefined) {
                            master.items[key].recipeUnitsPerPack = '';
                        }
                        if (master.items[key].directRecipeUnitCost === undefined) {
                            master.items[key].directRecipeUnitCost = '';
                        }
                        if (master.items[key].usableYieldPct === undefined) {
                            master.items[key].usableYieldPct = '';
                        }
                    }
                });
            });
            return master;
        }

        /** How many recipe-count units (sticks, ea, slices…) are in one full case */
        function recipeUnitsPerCase(item) {
            if (!item) return null;
            var packs = parseFloat(packOf(item));
            var perPack = parseFloat(item.recipeUnitsPerPack);
            if (isNaN(perPack) || perPack <= 0) return null;
            if (isNaN(packs) || packs <= 0) packs = 1; // treat as 1 pack purchase if pack blank
            return packs * perPack;
        }

        /** Optional direct $ per recipe unit (user override — never auto-filled) */
        function directRecipeCostOf(item) {
            if (!item) return '';
            if (item.directRecipeUnitCost !== undefined && item.directRecipeUnitCost !== null && item.directRecipeUnitCost !== '') {
                return item.directRecipeUnitCost;
            }
            return '';
        }

        /** Usable yield % (AP → EP). Blank or 100 = no waste adjustment. */
        function usableYieldOf(item) {
            if (!item) return null;
            if (item.usableYieldPct === undefined || item.usableYieldPct === null || item.usableYieldPct === '') return null;
            var y = parseFloat(item.usableYieldPct);
            if (isNaN(y) || y <= 0) return null;
            if (y > 100) y = 100;
            return y;
        }

        /**
         * Apply edible-portion yield: EP unit cost = AP unit cost ÷ (yield/100).
         * Example: $2/lb AP tomato at 96% → $2.08/lb EP.
         */
        function applyYield(apCost, item) {
            if (apCost == null || isNaN(apCost)) return null;
            var y = usableYieldOf(item);
            if (y == null || y >= 100) return apCost;
            return apCost / (y / 100);
        }

        /** As-purchased cost of one recipe unit (before yield waste). */
        function apCostPerRecipeUnit(item) {
            if (!item) return null;
            var caseP = parseFloat(casePriceOf(item));
            var units = recipeUnitsPerCase(item);
            if (!isNaN(caseP) && caseP >= 0 && casePriceOf(item) !== '' && units) {
                return caseP / units;
            }
            // Optional override only — do not use inventory costPerUnit (that's pack/case, not recipe unit)
            var direct = directRecipeCostOf(item);
            var cpu = parseFloat(direct);
            if (direct !== '' && !isNaN(cpu)) return cpu;
            return null;
        }

        /** Cost of one edible recipe unit. Conversion first; yield adjusts; optional direct only if no conversion. */
        function costPerRecipeUnit(item) {
            return applyYield(apCostPerRecipeUnit(item), item);
        }

        function lineCost(ing, master) {
            var key = normName(ing.name);
            var item = master.items[key];
            var qty = parseFloat(ing.qty);
            if (isNaN(qty)) {
                return { cost: null, missing: true, item: item, math: '', mode: 'none' };
            }
            var apCpu = apCostPerRecipeUnit(item);
            var cpu = applyYield(apCpu, item);
            if (cpu == null) {
                return { cost: null, missing: true, item: item, math: '', mode: 'none' };
            }
            var cost = qty * cpu;
            var packs = parseFloat(packOf(item));
            var perPack = parseFloat(item.recipeUnitsPerPack);
            var caseP = parseFloat(casePriceOf(item));
            var units = recipeUnitsPerCase(item);
            var y = usableYieldOf(item);
            var math = '';
            var mode = 'simple';
            var ru = (item && item.recipeUnitLabel) || ing.unit || 'ea';
            if (!isNaN(caseP) && casePriceOf(item) !== '' && units) {
                mode = 'convert';
                var pLabel = (!isNaN(packs) && packs > 0) ? packs : 1;
                math = pLabel + ' pack(s) × ' + perPack + ' ' + ru + '/pack = ' +
                    (Math.round(units * 100) / 100) + ' ' + ru + '/case · ' +
                    money(caseP) + ' ÷ ' + (Math.round(units * 100) / 100) + ' = ' +
                    money(apCpu) + ' AP/' + ru;
            } else {
                math = money(apCpu) + ' AP/' + ru;
            }
            if (y != null && y < 100) {
                mode = mode === 'convert' ? 'convert+yield' : 'yield';
                math += ' ÷ ' + y + '% yield = ' + money(cpu) + ' EP/' + ru;
            } else {
                math += ' (= ' + money(cpu) + '/' + ru + ')';
            }
            math += ' · × ' + qty + ' = ' + money(cost);
            return { cost: cost, missing: false, item: item, math: math, mode: mode, costPerUnit: cpu, yieldPct: y };
        }

        function hasPricing(item) {
            return costPerRecipeUnit(item) != null;
        }

        var master = loadMaster();
        var recipes = allRecipes();
        master = ensureMasterFromRecipes(master, recipes);
        saveMaster(master);
        var priceSearch = '';

        document.querySelectorAll('.tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var key = tab.dataset.tab;
                document.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t === tab); });
                document.getElementById('panel-recipes').classList.toggle('active', key === 'recipes');
                document.getElementById('panel-prices').classList.toggle('active', key === 'prices');
            });
        });

        function renderStats() {
            var ings = Object.keys(master.items);
            var priced = ings.filter(function (k) { return hasPricing(master.items[k]); });
            document.getElementById('stat-recipes').textContent = recipes.length;
            document.getElementById('stat-ings').textContent = ings.length;
            document.getElementById('stat-priced').textContent = priced.length;
        }

        function renderRecipes() {
            var root = document.getElementById('recipe-costs');
            var engN = 0, engSum = 0, engHigh = 0, engContrib = 0;
            if (!recipes.length) {
                root.innerHTML = '<div class="card empty">' + (isSweet
                    ? 'No recipes yet — add some on the recipe cards first 📖'
                    : 'No recipes yet. Add recipes first.') +
                    '<div style="margin-top:14px;"><a class="btn btn-primary" href="/BOH/recipe-cards">' + (isSweet ? 'Go to recipes' : 'Go to recipes') + '</a></div></div>';
                document.getElementById('eng-summary').style.display = 'none';
                return;
            }

            var withIngs = recipes.filter(function (r) { return (r.recipe.ingredients || []).length; });
            if (!withIngs.length) {
                root.innerHTML = '<div class="card empty">' + (isSweet
                    ? 'Recipes exist, but none have ingredient lists yet. Edit a recipe and add ingredients 💕'
                    : 'Recipes exist, but none have ingredient lists yet.') +
                    '<div style="margin-top:14px;"><a class="btn btn-primary" href="/BOH/recipe-cards">' + (isSweet ? 'Add ingredients' : 'Add ingredients') + '</a></div></div>';
                document.getElementById('eng-summary').style.display = 'none';
                return;
            }

            root.innerHTML = recipes.map(function (row) {
                var r = row.recipe;
                var ings = r.ingredients || [];
                if (!ings.length) {
                    return '<div class="recipe-cost"><h3>' + esc(r.title) + '</h3>' +
                        '<p class="muted">' + esc(row.cat) + (r.yield ? ' · ' + esc(r.yield) : '') + '</p>' +
                        '<p class="warn">' + (isSweet ? 'No ingredients on this card yet' : 'No ingredients yet') + '</p></div>';
                }
                var total = 0;
                var missing = 0;
                var lines = ings.map(function (ing) {
                    var lc = lineCost(ing, master);
                    if (lc.missing) missing++;
                    else total += lc.cost;
                    var qty = (ing.qty !== '' && ing.qty != null) ? ing.qty : '—';
                    return '<div class="line"><div class="line-left"><span>' + esc(ing.name) +
                        ' <span class="muted">(' + esc(qty) + ' ' + esc(ing.unit || '') + ')</span>' +
                        (lc.missing ? ' <span class="warn">needs conversion/price</span>' : '') +
                        (lc.mode === 'convert' || lc.mode === 'convert+yield' ? ' <span class="ok-note">✓ converted</span>' : '') +
                        (lc.yieldPct != null && lc.yieldPct < 100 ? ' <span class="ok-note">✓ ' + esc(lc.yieldPct) + '% yield</span>' : '') +
                        '</span>' +
                        (lc.math ? '<div class="line-math">' + esc(lc.math) + '</div>' : '') +
                        '</div><span>' + (lc.missing ? '—' : money(lc.cost)) + '</span></div>';
                }).join('');

                var portions = parseFloat(r.portions);
                var hasPortions = !isNaN(portions) && portions > 0;
                var per = (hasPortions && missing === 0) ? total / portions : null;
                var yieldTxt = '';
                if (r.yieldQty !== '' && r.yieldQty != null) {
                    yieldTxt = String(r.yieldQty) + (r.yieldUnit ? ' ' + r.yieldUnit : '');
                } else if (r.yield) {
                    yieldTxt = r.yield;
                }
                var menu = menuPriceForRecipe(r);
                var foodCostPct = (menu && per != null && menu.price > 0)
                    ? Math.round((per / menu.price) * 1000) / 10
                    : null;
                var contrib = (menu && per != null) ? menu.price - per : null;
                var noFcReason = '';
                if (foodCostPct == null) {
                    if (missing) noFcReason = isSweet ? 'needs prices' : 'needs prices';
                    else if (!hasPortions) noFcReason = isSweet ? 'no portions' : 'no portions';
                    else if (!menu) noFcReason = isSweet ? 'no menu price' : 'no menu price';
                    else if (!(menu.price > 0)) noFcReason = isSweet ? 'no sell price' : 'no sell price';
                }
                if (foodCostPct != null) {
                    engN++;
                    engSum += foodCostPct;
                    engContrib += (contrib || 0);
                    if (foodCostPct > 35) engHigh++;
                }

                return '<div class="recipe-cost" data-recipe-id="' + esc(r.id || '') + '">' +
                    '<h3>' + esc(r.title) + ' ' + fcBadge(foodCostPct, noFcReason) + '</h3>' +
                    '<p class="muted">' + esc(row.cat) + (yieldTxt ? ' · Yield ' + esc(yieldTxt) : '') +
                    (r.portions ? ' · ' + esc(r.portions) + ' portions' : '') +
                    (menu ? ' · Menu ' + money(menu.price) : '') + '</p>' +
                    '<div class="totals">' +
                        '<span>' + (isSweet ? 'Batch food cost: ' : 'Batch cost: ') + '<strong>' + (missing ? money(total) + '*' : money(total)) + '</strong></span>' +
                        '<span>' + (isSweet ? 'Cost / portion: ' : 'Cost / portion: ') + '<strong>' + (per != null ? money(per) : '—') + '</strong></span>' +
                        (menu ? '<span>' + (isSweet ? 'Menu sell: ' : 'Menu sell: ') + '<strong>' + money(menu.price) + '</strong></span>' : '') +
                        (foodCostPct != null ? '<span>' + (isSweet ? 'Food cost %: ' : 'Food cost %: ') + '<strong>' + foodCostPct + '%</strong></span>' : '') +
                        (contrib != null ? '<span>' + (isSweet ? 'Contribution: ' : 'Contribution: ') + '<strong>' + money(contrib) + '</strong></span>' : '') +
                    '</div>' +
                    (per != null ? suggestedPricesHtml(per, r.id, r.menuItemId || '') : '') +
                    (missing ? '<p class="warn">' + (isSweet ? '* Some ingredients need case conversion or price — open Prices & conversions' : '* Some ingredients need conversion or price') + '</p>' : '') +
                    lines +
                    '</div>';
            }).join('');

            var sumEl = document.getElementById('eng-summary');
            if (engN > 0) {
                sumEl.style.display = 'block';
                document.getElementById('eng-avg').textContent = (Math.round((engSum / engN) * 10) / 10) + '%';
                document.getElementById('eng-high').textContent = engHigh;
                document.getElementById('eng-contrib').textContent = money(engContrib / engN);
            } else {
                sumEl.style.display = 'none';
            }
        }

        function convSummary(item) {
            var packs = parseFloat(packOf(item));
            var perPack = parseFloat(item.recipeUnitsPerPack);
            var caseP = parseFloat(casePriceOf(item));
            var ru = item.recipeUnitLabel || 'ea';
            var units = recipeUnitsPerCase(item);
            var apCpu = apCostPerRecipeUnit(item);
            var cpu = costPerRecipeUnit(item);
            var y = usableYieldOf(item);
            if (cpu == null) {
                return isSweet
                    ? 'Enter case price + recipe units per pack to convert — or optional direct cost/unit. Add yield % if not 100% usable.'
                    : 'Enter case price + units per pack, or optional direct cost/unit. Optional yield %.';
            }
            var parts = [];
            if (units && !isNaN(caseP) && casePriceOf(item) !== '') {
                var p = (!isNaN(packs) && packs > 0) ? packs : 1;
                parts.push((isSweet ? 'Case: ' : 'Case: ') +
                    p + ' pack(s) × ' + perPack + ' ' + ru + ' = <strong>' +
                    (Math.round(units * 100) / 100) + ' ' + ru + '</strong>/case · ' +
                    money(caseP) + ' ÷ ' + (Math.round(units * 100) / 100) + ' = <strong>' +
                    money(apCpu) + '</strong> AP/' + ru);
            } else {
                parts.push((isSweet ? 'Direct: ' : 'Direct: ') + '<strong>' + money(apCpu) + '</strong> AP/' + ru);
            }
            if (y != null && y < 100) {
                parts.push((isSweet ? 'Yield ' : 'Yield ') + y + '% → <strong>' + money(cpu) + '</strong> EP/' + ru +
                    ' (' + (Math.round((100 / y) * 100) / 100) + '× AP)');
            } else {
                parts.push((isSweet ? 'EP cost: ' : 'EP cost: ') + '<strong>' + money(cpu) + '</strong>/' + ru +
                    (isSweet ? ' (100% usable)' : ' (100% usable)'));
            }
            return parts.join('<br>');
        }

        function renderPrices() {
            var root = document.getElementById('price-list');
            var keys = Object.keys(master.items).sort(function (a, b) {
                return (master.items[a].name || a).localeCompare(master.items[b].name || b);
            });
            var q = priceSearch.trim().toLowerCase();
            if (q) {
                keys = keys.filter(function (k) {
                    var it = master.items[k];
                    return (it.name || k).toLowerCase().indexOf(q) !== -1 ||
                        (it.vendor || '').toLowerCase().indexOf(q) !== -1;
                });
            }
            if (!Object.keys(master.items).length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No ingredients pulled yet — add them on recipe cards first.' : 'No ingredients yet. Add them on recipe cards.') + '</div>';
                return;
            }
            if (!keys.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No matches' : 'No matches') + '</div>';
                return;
            }
            root.innerHTML = keys.map(function (key) {
                var item = master.items[key];
                var pack = packOf(item);
                var caseP = casePriceOf(item);
                var directCost = directRecipeCostOf(item);
                var uy = item.usableYieldPct !== undefined && item.usableYieldPct !== null ? item.usableYieldPct : '';
                // recipe unit labels seen on cards
                var seenUnits = {};
                recipes.forEach(function (row) {
                    (row.recipe.ingredients || []).forEach(function (ing) {
                        if (normName(ing.name) === key && ing.unit) seenUnits[ing.unit] = true;
                    });
                });
                var seenList = Object.keys(seenUnits);
                var meta = [];
                if (item.vendor) meta.push(item.vendor);
                if (seenList.length) meta.push((isSweet ? 'Recipe uses: ' : 'Recipe uses: ') + seenList.join(', '));
                if (item.size || item.unit) {
                    meta.push((isSweet ? 'Pack size note: ' : 'Pack: ') +
                        (item.size ? item.size + ' ' : '') + (item.unit || ''));
                }
                // Prefer saved label; only fall back to recipe unit if never set
                var runitVal = item.recipeUnitLabel || seenList[0] || 'ea';
                var sug = suggestYieldForName(item.name || key);
                var yieldBlank = uy === '' || uy == null;
                var suggestHtml = '';
                if (sug && yieldBlank) {
                    suggestHtml = '<div class="suggest-banner">' +
                        (isSweet ? 'Suggested from chart: ' : 'Suggested: ') +
                        '<strong>' + esc(sug.label) + ' · ' + sug.yield + '%</strong>' +
                        '<button type="button" class="btn btn-small btn-primary" data-act="apply-yield" data-key="' + esc(key) + '" data-yield="' + sug.yield + '">' +
                        (isSweet ? 'Apply ' + sug.yield + '%' : 'Apply ' + sug.yield + '%') + '</button>' +
                        '<span class="muted" style="font-size:0.85rem;">' + (isSweet ? 'or leave blank = 100%' : 'blank = 100%') + '</span></div>';
                } else if (sug && !yieldBlank && parseFloat(uy) === sug.yield) {
                    suggestHtml = '<div class="suggest-banner" style="opacity:0.85;">' +
                        (isSweet ? '✓ Matches chart: ' : '✓ Chart match: ') + esc(sug.label) + ' · ' + sug.yield + '%</div>';
                }

                return '<div class="conv-card" data-key="' + esc(key) + '">' +
                    '<h3>' + esc(item.name || key) + '</h3>' +
                    (meta.length ? '<div class="meta">' + esc(meta.join(' · ')) + '</div>' : '') +
                    '<div class="conv-grid">' +
                        '<div class="field" style="margin:0"><label>' + (isSweet ? 'Case price ($)' : 'Case price ($)') + '</label>' +
                            '<input type="number" step="any" min="0" class="p-case" value="' + esc(caseP !== '' && caseP != null ? caseP : '') + '" placeholder="0.00"></div>' +
                        '<div class="field" style="margin:0"><label>' + (isSweet ? 'Packs per case' : 'Packs per case') + '</label>' +
                            '<input type="number" step="any" min="0" class="p-pack" value="' + esc(pack !== '' && pack != null ? pack : '') + '" placeholder="' + (isSweet ? 'e.g. 4 bags' : 'e.g. 4') + '"></div>' +
                        '<div class="field" style="margin:0"><label>' + (isSweet ? 'Recipe units per pack' : 'Recipe units per pack') + '</label>' +
                            '<input type="number" step="any" min="0" class="p-units" value="' + esc(item.recipeUnitsPerPack !== '' && item.recipeUnitsPerPack != null ? item.recipeUnitsPerPack : '') + '" placeholder="' + (isSweet ? 'e.g. 55 sticks' : 'e.g. 55') + '"></div>' +
                        '<div class="field" style="margin:0"><label>' + (isSweet ? 'Recipe unit name' : 'Recipe unit name') + '</label>' +
                            '<input type="text" class="p-runit" value="' + esc(runitVal) + '" placeholder="ea, stick, slice, lb…"></div>' +
                        '<div class="field" style="margin:0"><label>' + (isSweet ? 'Usable yield % (AP→EP)' : 'Usable yield % (AP→EP)') + '</label>' +
                            '<input type="number" step="any" min="0" max="100" class="p-yield-pct" list="yield-presets" value="' + esc(uy !== '' && uy != null ? uy : '') + '" placeholder="' + (isSweet ? 'blank = 100% · e.g. 60' : 'blank or 100 = full use') + '"></div>' +
                        '<div class="field" style="margin:0"><label>' + (isSweet ? 'OR direct cost / recipe unit ($)' : 'OR cost / recipe unit ($)') + '</label>' +
                            '<input type="number" step="any" min="0" class="p-cost" value="' + esc(directCost !== '' && directCost != null ? directCost : '') + '" placeholder="' + (isSweet ? 'optional — leave blank' : 'optional — leave blank') + '"></div>' +
                    '</div>' +
                    suggestHtml +
                    '<div class="conv-result" data-summary>' + convSummary(item) + '</div>' +
                    '</div>';
            }).join('');
        }

        function collectPriceCards() {
            document.querySelectorAll('#price-list .conv-card').forEach(function (row) {
                var key = row.dataset.key;
                if (!master.items[key]) return;
                var caseP = row.querySelector('.p-case').value;
                var pack = row.querySelector('.p-pack').value;
                var unitsU = row.querySelector('.p-units').value;
                var runit = row.querySelector('.p-runit').value.trim();
                var cost = row.querySelector('.p-cost').value;
                var yieldPct = row.querySelector('.p-yield-pct').value;
                // Persist exactly what the user entered — never auto-fill optional direct cost
                master.items[key].casePrice = caseP === '' ? '' : caseP;
                master.items[key].pack = pack === '' ? '' : pack;
                master.items[key].packSize = master.items[key].pack;
                master.items[key].recipeUnitsPerPack = unitsU === '' ? '' : unitsU;
                master.items[key].recipeUnitLabel = runit || master.items[key].recipeUnitLabel || 'ea';
                master.items[key].directRecipeUnitCost = cost === '' ? '' : cost;
                master.items[key].usableYieldPct = yieldPct === '' ? '' : yieldPct;
                var sum = row.querySelector('[data-summary]');
                if (sum) sum.innerHTML = convSummary(master.items[key]);
            });
        }

        document.getElementById('save-prices-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.costing_edit')) return;
            collectPriceCards();
            saveMaster(master);
            var toast = document.getElementById('toast');
            toast.textContent = isSweet ? 'Conversions saved 💾' : 'Conversions saved';
            toast.classList.add('show');
            setTimeout(function () { toast.classList.remove('show'); }, 1100);
            renderStats();
            renderRecipes();
            // Re-render from saved master so fields show exactly what was stored
            renderPrices();
        });

        document.getElementById('price-list').addEventListener('input', function (e) {
            if (!e.target.closest('.conv-card')) return;
            // Live summary only — write form → master, do not re-render (keeps focus & blanks)
            collectPriceCards();
        });

        document.getElementById('price-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act="apply-yield"]');
            if (!btn) return;
            var key = btn.dataset.key;
            var y = btn.dataset.yield;
            if (!master.items[key]) return;
            collectPriceCards();
            master.items[key].usableYieldPct = y;
            saveMaster(master);
            renderPrices();
            renderRecipes();
            renderStats();
            var toast = document.getElementById('toast');
            toast.textContent = isSweet ? 'Yield ' + y + '% applied ✨' : 'Yield ' + y + '% applied';
            toast.classList.add('show');
            setTimeout(function () { toast.classList.remove('show'); }, 1100);
        });

        document.getElementById('suggest-yields-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.costing_edit')) return;
            collectPriceCards();
            var n = 0;
            Object.keys(master.items).forEach(function (key) {
                var item = master.items[key];
                var blank = item.usableYieldPct === undefined || item.usableYieldPct === null || item.usableYieldPct === '';
                if (!blank) return;
                var sug = suggestYieldForName(item.name || key);
                if (sug && sug.yield < 100) {
                    item.usableYieldPct = String(sug.yield);
                    n++;
                }
            });
            saveMaster(master);
            renderPrices();
            renderRecipes();
            renderStats();
            var toast = document.getElementById('toast');
            toast.textContent = n
                ? (isSweet ? 'Applied ' + n + ' chart yield(s) ✨' : 'Applied ' + n + ' yield(s)')
                : (isSweet ? 'No blank matches found (or already set)' : 'No blank matches found');
            toast.classList.add('show');
            setTimeout(function () { toast.classList.remove('show'); }, 1400);
        });

        document.getElementById('recipe-costs').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act="apply-menu-price"]');
            if (!btn) return;
            if (!canP('boh.recipes.costing_edit') && !canP('boh.recipes.menu_edit')) return;
            var price = parseFloat(btn.dataset.price);
            if (isNaN(price) || price < 0) return;
            var menuId = btn.dataset.menuId || '';
            var recipeId = btn.dataset.recipeId || '';
            var items = loadMenu();
            var item = menuId ? items.find(function (x) { return String(x.id) === String(menuId); }) : null;
            var created = false;
            if (!item) {
                var card = btn.closest('.recipe-cost');
                var titleEl = card ? card.querySelector('h3') : null;
                var name = titleEl ? titleEl.childNodes[0].textContent.trim() : 'Menu item';
                menuId = menuId || uid();
                item = {
                    id: menuId,
                    name: name,
                    price: String(price),
                    category: 'mains',
                    notes: ''
                };
                items.push(item);
                created = true;
                if (recipeId) setRecipeMenuItemId(recipeId, menuId);
            } else {
                item.price = String(price);
            }
            saveMenuItems(items);
            renderRecipes();
            var toast = document.getElementById('toast');
            toast.textContent = created
                ? (isSweet ? 'Menu item created @ ' + money(price) + ' ✨' : 'Menu item created @ ' + money(price))
                : (isSweet ? 'Menu price set to ' + money(price) + ' ✨' : 'Menu price set to ' + money(price));
            toast.classList.add('show');
            setTimeout(function () { toast.classList.remove('show'); }, 1200);
        });

        document.getElementById('print-recipes-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.costing_view')) return;
            window.print();
        });

        document.getElementById('price-search').addEventListener('input', function () {
            // Commit visible cards before re-filter so in-progress edits aren't lost
            collectPriceCards();
            saveMaster(master);
            priceSearch = this.value;
            renderPrices();
        });

        renderStats();
        renderRecipes();
        renderPrices();
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyCostPerms);
            document.addEventListener('pbj-perms-ready', applyCostPerms);
})();
    </script>
</body>
</html>
