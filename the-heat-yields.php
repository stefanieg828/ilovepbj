<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

// Industry-typical AP → edible portion (EP) yields. Guides only — house yields vary.
$default_yields = [
    'produce' => [
        'title' => 'Produce',
        'icon' => '🥬',
        'items' => [
            ['name' => 'Tomato, whole', 'yield' => 96, 'note' => 'Core & stem only'],
            ['name' => 'Tomato, for slicing', 'yield' => 90, 'note' => 'Core + end trim'],
            ['name' => 'Cherry / grape tomato', 'yield' => 98, 'note' => 'Minimal trim'],
            ['name' => 'Cantaloupe', 'yield' => 60, 'note' => 'Rind & seeds out'],
            ['name' => 'Honeydew', 'yield' => 46, 'note' => 'Rind & seeds out'],
            ['name' => 'Watermelon', 'yield' => 52, 'note' => 'Rind out'],
            ['name' => 'Pineapple', 'yield' => 52, 'note' => 'Peel, eyes, core'],
            ['name' => 'Avocado', 'yield' => 67, 'note' => 'Skin & pit'],
            ['name' => 'Banana', 'yield' => 65, 'note' => 'Peel only'],
            ['name' => 'Apple, peeled', 'yield' => 76, 'note' => 'Peel, core, seeds'],
            ['name' => 'Apple, unpeeled', 'yield' => 91, 'note' => 'Core only'],
            ['name' => 'Orange, sections', 'yield' => 52, 'note' => 'Peel & membrane'],
            ['name' => 'Orange, juice', 'yield' => 45, 'note' => 'Juice yield'],
            ['name' => 'Lemon / lime', 'yield' => 45, 'note' => 'Juice / zest house'],
            ['name' => 'Strawberry', 'yield' => 88, 'note' => 'Hulls'],
            ['name' => 'Grapes', 'yield' => 94, 'note' => 'Stems'],
            ['name' => 'Onion, yellow', 'yield' => 89, 'note' => 'Peel & root'],
            ['name' => 'Garlic', 'yield' => 87, 'note' => 'Skin / root'],
            ['name' => 'Ginger root', 'yield' => 91, 'note' => 'Peel'],
            ['name' => 'Carrot', 'yield' => 81, 'note' => 'Peel & trim'],
            ['name' => 'Celery', 'yield' => 69, 'note' => 'Leaves & base'],
            ['name' => 'Bell pepper', 'yield' => 82, 'note' => 'Stem, seeds, ribs'],
            ['name' => 'Jalapeño / chile', 'yield' => 87, 'note' => 'Stem'],
            ['name' => 'Cucumber', 'yield' => 95, 'note' => 'Ends / peel optional'],
            ['name' => 'Zucchini / squash', 'yield' => 95, 'note' => 'Ends'],
            ['name' => 'Potato, peeled', 'yield' => 81, 'note' => 'Peel & eyes'],
            ['name' => 'Sweet potato', 'yield' => 80, 'note' => 'Peel'],
            ['name' => 'Broccoli', 'yield' => 61, 'note' => 'Stalk trim'],
            ['name' => 'Cauliflower', 'yield' => 45, 'note' => 'Leaves & core'],
            ['name' => 'Asparagus', 'yield' => 53, 'note' => 'Woody ends'],
            ['name' => 'Green beans', 'yield' => 88, 'note' => 'Ends'],
            ['name' => 'Corn, cob', 'yield' => 54, 'note' => 'Kernels only'],
            ['name' => 'Mushrooms', 'yield' => 97, 'note' => 'Trim stems'],
            ['name' => 'Lettuce, head', 'yield' => 74, 'note' => 'Outer leaves / core'],
            ['name' => 'Romaine', 'yield' => 75, 'note' => 'Outer / core'],
            ['name' => 'Iceberg', 'yield' => 73, 'note' => 'Outer / core'],
            ['name' => 'Spinach, fresh', 'yield' => 88, 'note' => 'Stems / sand'],
            ['name' => 'Kale', 'yield' => 70, 'note' => 'Stems'],
            ['name' => 'Cabbage', 'yield' => 80, 'note' => 'Outer / core'],
            ['name' => 'Cilantro / parsley', 'yield' => 85, 'note' => 'Stems house-dependent'],
            ['name' => 'Basil', 'yield' => 80, 'note' => 'Stems'],
        ],
    ],
    'meat' => [
        'title' => 'Meat & Poultry',
        'icon' => '🥩',
        'items' => [
            ['name' => 'Ground beef / turkey', 'yield' => 100, 'note' => 'AP = EP raw; cook shrink separate'],
            ['name' => 'Beef, boneless roast', 'yield' => 95, 'note' => 'Trim fat as needed'],
            ['name' => 'Beef, bone-in steak', 'yield' => 75, 'note' => 'Bone & fat trim varies'],
            ['name' => 'Beef, short ribs bone-in', 'yield' => 55, 'note' => 'High bone'],
            ['name' => 'Pork loin, boneless', 'yield' => 95, 'note' => 'Light trim'],
            ['name' => 'Pork chop, bone-in', 'yield' => 80, 'note' => 'Bone'],
            ['name' => 'Bacon, raw', 'yield' => 100, 'note' => 'Cook shrink ~30–40%'],
            ['name' => 'Ham, bone-in', 'yield' => 65, 'note' => 'Bone & fat'],
            ['name' => 'Chicken, whole raw', 'yield' => 65, 'note' => 'Bone-in bird → meat'],
            ['name' => 'Chicken breast, bone-in skin-on', 'yield' => 70, 'note' => 'Bone & skin'],
            ['name' => 'Chicken breast, boneless skinless', 'yield' => 98, 'note' => 'Light trim'],
            ['name' => 'Chicken thigh, boneless skinless', 'yield' => 95, 'note' => 'Light trim'],
            ['name' => 'Chicken wing, whole', 'yield' => 75, 'note' => 'Bone-in'],
            ['name' => 'Turkey breast, boneless', 'yield' => 95, 'note' => 'Light trim'],
            ['name' => 'Sausage, bulk', 'yield' => 100, 'note' => 'Cook shrink separate'],
        ],
    ],
    'seafood' => [
        'title' => 'Seafood',
        'icon' => '🐟',
        'items' => [
            ['name' => 'Fish fillet, skinless', 'yield' => 100, 'note' => 'Already EP'],
            ['name' => 'Fish, whole dressed', 'yield' => 50, 'note' => 'To fillet approx.'],
            ['name' => 'Salmon, whole', 'yield' => 65, 'note' => 'To skin-on fillet'],
            ['name' => 'Shrimp, head-on', 'yield' => 45, 'note' => 'To peeled'],
            ['name' => 'Shrimp, shell-on tail-on', 'yield' => 55, 'note' => 'To peeled'],
            ['name' => 'Shrimp, peeled deveined', 'yield' => 100, 'note' => 'Already EP'],
            ['name' => 'Scallops, dry', 'yield' => 100, 'note' => 'Already EP'],
            ['name' => 'Mussels / clams, live', 'yield' => 25, 'note' => 'Meat only (approx.)'],
            ['name' => 'Crab, whole', 'yield' => 15, 'note' => 'Picked meat approx.'],
            ['name' => 'Lobster, whole', 'yield' => 25, 'note' => 'Meat approx.'],
            ['name' => 'Calamari, cleaned', 'yield' => 100, 'note' => 'Already EP'],
        ],
    ],
    'other' => [
        'title' => 'Dairy & Other',
        'icon' => '🧀',
        'items' => [
            ['name' => 'Block cheese', 'yield' => 100, 'note' => 'Rind house-dependent'],
            ['name' => 'Hard cheese w/ rind', 'yield' => 90, 'note' => 'Rind waste'],
            ['name' => 'Butter', 'yield' => 100, 'note' => ''],
            ['name' => 'Eggs, shell → liquid', 'yield' => 87, 'note' => 'Shell loss'],
            ['name' => 'Nuts, in shell', 'yield' => 45, 'note' => 'Varies by type'],
            ['name' => 'Nuts, shelled', 'yield' => 100, 'note' => ''],
            ['name' => 'Bread loaf → crumbs / trim', 'yield' => 95, 'note' => 'End trim'],
        ],
    ],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'Produce & Meat Yields' : 'Produce & Meat Yields'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
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
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.2rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; font-size: 1.02rem; }
        .intro strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .link-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .chip { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-size: 0.95rem; border: none; cursor: pointer; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC; font-family: 'DreamingOutLoudPro', serif;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE; font-family: 'Lora', serif;<?php endif; ?> }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 10px; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; align-items: flex-end; }
        .field { flex: 1; min-width: 110px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus, .field select:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .result { margin-top: 10px; padding: 14px; border-radius: 14px; font-size: 1.05rem; line-height: 1.45; <?php if ($is_sweet): ?>background: #FFF5F6; color: #3a2f1f; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .result strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .search { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; margin-bottom: 12px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .cat-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .cat-tab { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.95rem; cursor: pointer; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFF5F6; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .cat-tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .yield-row { display: flex; align-items: flex-start; gap: 10px; padding: 12px 4px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .yield-row:last-child { border-bottom: none; }
        .yield-info { flex: 1; min-width: 0; }
        .yield-name { font-size: 1.05rem; }
        .yield-note { font-size: 0.88rem; opacity: 0.7; margin-top: 2px; }
        .yield-pct { font-size: 1.25rem; font-weight: 600; white-space: nowrap; min-width: 58px; text-align: right; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .yield-actions { display: flex; flex-direction: column; gap: 4px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 6px 10px; font-size: 0.82rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; white-space: nowrap; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .empty { text-align: center; padding: 20px; opacity: 0.75; }
        .formula { font-size: 0.95rem; padding: 12px 14px; border-radius: 12px; margin-bottom: 12px; line-height: 1.4; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px dashed #F3C5CC;<?php else: ?>background: #FAF8F5; border: 1px dashed #C5D0DE;<?php endif; ?> }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .print-only { display: none; }
        .print-full-chart { display: none; }
        body.print-yields .print-full-chart { display: block; }
        body.print-yields .screen-chart { display: none !important; }
        body.print-yields .calc-card { display: none !important; }
        @media print {
            .no-print, .back-link, .toolbar, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .btn, .link-row, .intro, .search, .cat-tabs, .yield-actions { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 12px 16px; }
            h1 { font-size: 1.35rem; }
            .subtitle { display: none; }
            .content { padding: 8px 12px; max-width: none; }
            .card { box-shadow: none; border: 1px solid #ccc; padding: 10px 12px; margin-bottom: 10px; }
            .card h2 { font-size: 1.1rem; color: #000 !important; margin-bottom: 6px; }
            .yield-row { padding: 4px 0; border-bottom: 1px solid #ddd; page-break-inside: avoid; }
            .yield-name { font-size: 0.9rem; }
            .yield-note { font-size: 0.75rem; }
            .yield-pct { font-size: 0.95rem; color: #000 !important; }
            body.print-yields .print-full-chart { display: block !important; }
            body.print-yields .screen-chart { display: none !important; }
            body.print-yields .calc-card { display: none !important; }
            .print-cat-title { font-size: 1rem; font-weight: 600; margin: 10px 0 4px; page-break-after: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH/recipes" class="back-link">← <?php echo $is_sweet ? 'Back to Menu & Standardized Recipes' : 'Back to Menu & Standardized Recipes'; ?></a>
        <h1><?php echo $is_sweet ? 'Produce & Meat Yields' : 'Produce & Meat Yields'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'AP → edible portion % for real food cost' : 'As-purchased to edible portion yields'; ?></p>
    </div>

    <div class="content">
        <div class="intro no-print">
            <?php echo $is_sweet
                ? 'You buy a whole cantaloupe, but you only use ~60% of it. That waste has to show up in food cost. <strong>EP cost = AP cost ÷ yield%</strong> — set the same % on Costing so recipes get true usable cost 💕'
                : 'Edible portion (EP) cost = as-purchased (AP) cost ÷ yield %. Use the chart and calculator, then set usable yield on Costing.'; ?>
        </div>

        <div class="link-row no-print">
            <a class="chip" href="/admin/costing"><?php echo $is_sweet ? '💰 Costing sheet' : '💰 Costing sheet'; ?></a>
            <a class="chip" href="/BOH/recipes"><?php echo $is_sweet ? '🥪 Menu & Recipes' : '🥪 Menu & Recipes'; ?></a>
            <a class="chip" href="/BOH/recipe-cards"><?php echo $is_sweet ? '📖 Recipes' : '📖 Recipes'; ?></a>
        </div>

        <div class="toolbar no-print">
            <button type="button" class="btn btn-secondary" id="print-yields-btn" data-perm="boh.recipes.yields_use"><?php echo $is_sweet ? '🖨️ Print full chart' : 'Print full chart'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-cat-btn" data-perm="boh.recipes.yields_use"><?php echo $is_sweet ? '🖨️ Print this category' : 'Print this category'; ?></button>
        </div>
        <div class="print-only" id="print-yields-header" style="margin-bottom:8px;font-weight:600;"></div>

        <div class="card calc-card no-print">
            <h2><?php echo $is_sweet ? 'EP cost calculator' : 'EP cost calculator'; ?></h2>
            <div class="formula">
                <?php echo $is_sweet
                    ? 'EP $ per unit = AP $ per unit ÷ (yield ÷ 100)<br>AP weight needed = EP weight needed ÷ (yield ÷ 100)'
                    : 'EP cost = AP cost ÷ (yield ÷ 100) · AP weight needed = EP weight ÷ (yield ÷ 100)'; ?>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="calc-ap"><?php echo $is_sweet ? 'AP cost ($ / unit)' : 'AP cost ($ / unit)'; ?></label>
                    <input type="number" id="calc-ap" step="any" min="0" value="2.00" placeholder="0.00">
                </div>
                <div class="field">
                    <label for="calc-yield"><?php echo $is_sweet ? 'Yield %' : 'Yield %'; ?></label>
                    <input type="number" id="calc-yield" step="any" min="0.1" max="100" value="60" placeholder="60">
                </div>
                <div class="field">
                    <label for="calc-ep-need"><?php echo $is_sweet ? 'EP amount needed (optional)' : 'EP amount needed (optional)'; ?></label>
                    <input type="number" id="calc-ep-need" step="any" min="0" placeholder="<?php echo $is_sweet ? 'e.g. 5 lb diced' : 'e.g. 5'; ?>">
                </div>
            </div>
            <div class="result" id="calc-result">—</div>
            <p class="hint" style="margin-top:10px;margin-bottom:0;"><?php echo $is_sweet ? 'Tap Use on any row below to drop that yield % into the calculator.' : 'Tap Use on a row to fill the yield %.'; ?></p>
        </div>

        <div class="card screen-chart">
            <h2><?php echo $is_sweet ? 'Yield chart' : 'Yield chart'; ?></h2>
            <p class="hint no-print"><?php echo $is_sweet
                ? 'Starter guides from typical foodservice AP→EP yields — edit house % if your cut differs. Not a substitute for your own waste studies.'
                : 'Typical foodservice AP→EP yields. Adjust for your house cuts.'; ?></p>
            <input type="search" class="search no-print" id="yield-search" placeholder="<?php echo $is_sweet ? 'Search tomato, chicken, shrimp…' : 'Search…'; ?>">
            <div class="cat-tabs no-print" id="cat-tabs"></div>
            <div id="yield-list"></div>
        </div>
        <div class="print-full-chart" id="print-full-chart"></div>

        <div class="actions-bar no-print">
            <button type="button" class="btn btn-secondary" id="reset-btn" data-perm="boh.recipes.yields_use"><?php echo $is_sweet ? 'Reset chart defaults' : 'Reset chart defaults'; ?></button>
            <a href="/admin/costing" class="btn btn-primary"><?php echo $is_sweet ? 'Open Costing →' : 'Open Costing →'; ?></a>
        </div>
    </div>

    <div class="toast" id="toast"><?php echo $is_sweet ? 'Yield loaded' : 'Yield loaded'; ?></div>
    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const KEY = 'pbj_heat_yields_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyYieldsPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var ok = canP('boh.recipes.yields_use');
                var c = document.querySelector('.content');
                if (c && !ok && !document.getElementById('yield-denied')) {
                    c.insertAdjacentHTML('afterbegin', '<div class="intro" id="yield-denied">No permission to use yields tools.</div>');
                    c.querySelectorAll('input,button.btn').forEach(function(el){ if (el.id !== undefined) el.disabled = true; });
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof renderEquip === 'function') renderEquip(); } catch (e) {}
                try { if (typeof renderList === 'function') renderList(); } catch (e) {}
            }

        const defaults = <?php echo json_encode($default_yields, JSON_UNESCAPED_UNICODE); ?>;

        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money2(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
        }
        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || !r.cats) return { cats: JSON.parse(JSON.stringify(defaults)), active: 'produce' };
                return r;
            } catch (e) { return { cats: JSON.parse(JSON.stringify(defaults)), active: 'produce' }; }
        }
        function save() { localStorage.setItem(KEY, JSON.stringify(state)); }

        var state = load();
        if (!state.active) state.active = 'produce';
        var searchQ = '';

        function runCalc() {
            var ap = parseFloat(document.getElementById('calc-ap').value);
            var y = parseFloat(document.getElementById('calc-yield').value);
            var epNeed = parseFloat(document.getElementById('calc-ep-need').value);
            var out = document.getElementById('calc-result');
            if (isNaN(ap) || ap < 0 || isNaN(y) || y <= 0) {
                out.innerHTML = isSweet ? 'Enter AP cost and a yield % greater than 0.' : 'Enter AP cost and yield % > 0.';
                return;
            }
            if (y > 100) y = 100;
            var factor = y / 100;
            var epCost = ap / factor;
            var html = (isSweet ? 'True EP cost: ' : 'True EP cost: ') + '<strong>' + money2(epCost) + '</strong> ' +
                (isSweet ? 'per same unit as AP' : 'per same unit') +
                '<br><span style="opacity:0.8;font-size:0.95rem;">' +
                money2(ap) + ' ÷ ' + y + '% = ' + money2(epCost) +
                ' · ' + (isSweet ? 'you pay ~' : 'multiplier ~') +
                '<strong>' + (Math.round((100 / y) * 100) / 100) + '×</strong> AP' +
                '</span>';
            if (!isNaN(epNeed) && epNeed > 0) {
                var apNeed = epNeed / factor;
                var batchCost = apNeed * ap; // wait - if ap is $ per unit and apNeed is units
                // AP cost total for needed EP = EP needed * EP unit cost = epNeed * (ap/factor)
                // or = (epNeed/factor) * ap
                var total = epNeed * epCost;
                html += '<br><br>' + (isSweet ? 'To net ' : 'To net ') + '<strong>' + epNeed + '</strong> EP units, buy about <strong>' +
                    (Math.round(apNeed * 100) / 100) + '</strong> AP units · AP spend ≈ <strong>' + money2(total) + '</strong>';
            }
            out.innerHTML = html;
        }

        ['calc-ap', 'calc-yield', 'calc-ep-need'].forEach(function (id) {
            document.getElementById(id).addEventListener('input', runCalc);
        });

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1400);
        }

        function useYield(pct, name) {
            document.getElementById('calc-yield').value = pct;
            runCalc();
            toast((name ? name + ' · ' : '') + pct + '%' + (isSweet ? ' loaded ✨' : ' loaded'));
            try {
                // stash for costing page optional prefill
                sessionStorage.setItem('pbj_yield_pick_v1', JSON.stringify({ yield: pct, name: name || '', at: Date.now() }));
            } catch (e) {}
        }

        function renderTabs() {
            var cats = state.cats;
            document.getElementById('cat-tabs').innerHTML = Object.keys(cats).map(function (id) {
                var c = cats[id];
                return '<button type="button" class="cat-tab' + (state.active === id ? ' active' : '') + '" data-cat="' + esc(id) + '">' +
                    esc(c.icon || '') + ' ' + esc(c.title) + '</button>';
            }).join('');
        }

        function renderList() {
            var cat = state.cats[state.active];
            var root = document.getElementById('yield-list');
            if (!cat) { root.innerHTML = ''; return; }
            var items = (cat.items || []).slice();
            var q = searchQ.trim().toLowerCase();
            if (q) {
                // search across all categories when typing
                items = [];
                Object.keys(state.cats).forEach(function (id) {
                    (state.cats[id].items || []).forEach(function (it) {
                        var hay = (it.name + ' ' + (it.note || '')).toLowerCase();
                        if (hay.indexOf(q) !== -1) items.push(Object.assign({ _cat: state.cats[id].title }, it));
                    });
                });
            }
            if (!items.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No matches' : 'No matches') + '</div>';
                return;
            }
            root.innerHTML = items.map(function (it, idx) {
                return '<div class="yield-row">' +
                    '<div class="yield-info">' +
                        '<div class="yield-name">' + esc(it.name) +
                        (it._cat && q ? ' <span style="opacity:0.55;font-size:0.85rem;">· ' + esc(it._cat) + '</span>' : '') +
                        '</div>' +
                        (it.note ? '<div class="yield-note">' + esc(it.note) + '</div>' : '') +
                    '</div>' +
                    '<div class="yield-pct">' + esc(it.yield) + '%</div>' +
                    '<div class="yield-actions">' +
                        '<button type="button" class="btn btn-small btn-ghost" data-use="' + esc(it.yield) + '" data-name="' + esc(it.name) + '">' +
                        (isSweet ? 'Use' : 'Use') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function render() {
            renderTabs();
            renderList();
        }

        document.getElementById('cat-tabs').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-cat]');
            if (!btn) return;
            state.active = btn.dataset.cat;
            searchQ = '';
            document.getElementById('yield-search').value = '';
            save();
            render();
        });

        document.getElementById('yield-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-use]');
            if (!btn) return;
            useYield(parseFloat(btn.dataset.use), btn.dataset.name);
        });

        document.getElementById('yield-search').addEventListener('input', function () {
            searchQ = this.value;
            renderList();
        });

        document.getElementById('reset-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.yields_use')) return;
            if (!confirm(isSweet ? 'Reset yield chart to starter defaults?' : 'Reset yield chart to defaults?')) return;
            state = { cats: JSON.parse(JSON.stringify(defaults)), active: 'produce' };
            save();
            render();
            toast(isSweet ? 'Defaults restored' : 'Defaults restored');
        });

        function rowHtml(it) {
            return '<div class="yield-row">' +
                '<div class="yield-info">' +
                    '<div class="yield-name">' + esc(it.name) + '</div>' +
                    (it.note ? '<div class="yield-note">' + esc(it.note) + '</div>' : '') +
                '</div>' +
                '<div class="yield-pct">' + esc(it.yield) + '%</div>' +
                '</div>';
        }

        function stampYieldHeader(label) {
            var d = new Date();
            document.getElementById('print-yields-header').textContent =
                label + ' · ' + d.toLocaleDateString();
        }

        document.getElementById('print-cat-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.yields_use')) return;
            document.body.classList.remove('print-yields');
            var cat = state.cats[state.active];
            stampYieldHeader((cat ? ((cat.icon || '') + ' ' + cat.title) : 'Yields') + (isSweet ? ' yields' : ' yields'));
            // clear search so list shows full category
            searchQ = '';
            document.getElementById('yield-search').value = '';
            renderList();
            setTimeout(function () { window.print(); }, 60);
        });

        document.getElementById('print-yields-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.yields_use')) return;
            var html = '';
            Object.keys(state.cats).forEach(function (id) {
                var c = state.cats[id];
                html += '<div class="card"><div class="print-cat-title">' + esc((c.icon || '') + ' ' + c.title) + '</div>';
                html += (c.items || []).map(rowHtml).join('') + '</div>';
            });
            document.getElementById('print-full-chart').innerHTML = html;
            document.body.classList.add('print-yields');
            stampYieldHeader(isSweet ? 'Produce & meat yield chart (full)' : 'Yield chart (full)');
            setTimeout(function () {
                window.print();
                document.body.classList.remove('print-yields');
            }, 60);
        });

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyYieldsPerms);
            document.addEventListener('pbj-perms-ready', applyYieldsPerms);
        runCalc();
    })();
    </script>
</body>
</html>
