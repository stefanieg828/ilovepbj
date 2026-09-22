<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

$default_categories = [
    ['id' => 'appetizers', 'title' => 'Appetizers',          'icon' => '🥟', 'hint' => 'Starters, shares, small plates'],
    ['id' => 'mains',      'title' => 'Mains & Sandwiches',  'icon' => '🥪', 'hint' => 'Build specs and plate notes'],
    ['id' => 'sides',      'title' => 'Sides & Salads',      'icon' => '🥗', 'hint' => 'Portions, garnishes, backups'],
    ['id' => 'sweets',     'title' => 'Sweets & Extras',     'icon' => '🍮', 'hint' => 'Desserts and finishing touches'],
    ['id' => 'batch',      'title' => 'Batch / Prep Specs',  'icon' => '📦', 'hint' => 'Large-batch formulas and cooling'],
    ['id' => 'allergens',  'title' => 'Allergen Notes',      'icon' => '⚠️', 'hint' => 'Common allergens and swaps'],
];

$units = ['ea', 'oz', 'lb', 'g', 'kg', 'tsp', 'tbsp', 'cup', 'fl oz', 'ml', 'L', 'qt', 'gal', 'slice', 'bunch', 'can', 'case', 'serving', 'servings', 'portion', 'portions'];
$prep_methods = ['as is', 'whole', 'diced', 'sliced', 'minced', 'julienne', 'chopped', 'shredded', 'peeled', 'trimmed', 'portioned', 'cooked', 'raw', 'other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'Standardized Recipes' : 'Standardized Recipes'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
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
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 780px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.95; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .category { background: white; border-radius: 18px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); overflow: hidden; }
        .category-header { display: flex; align-items: center; gap: 12px; padding: 16px 18px; cursor: pointer; width: 100%; border: none; background: transparent; text-align: left; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .category-header:hover { background: <?php echo $is_sweet ? '#FFF8F9' : '#F8F5F1'; ?>; }
        .cat-icon { width: 44px; height: 44px; min-width: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
        .cat-titles { flex: 1; min-width: 0; }
        .cat-title { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0; }
        .cat-hint { margin: 4px 0 0; font-size: 0.9rem; opacity: 0.7; }
        .cat-count { font-size: 0.9rem; opacity: 0.65; white-space: nowrap; }
        .chevron { opacity: 0.5; transition: transform 0.2s; }
        .category.open .chevron { transform: rotate(90deg); }
        .category-body { display: none; padding: 0 16px 16px; border-top: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .category.open .category-body { display: block; }
        .recipe { border-radius: 14px; padding: 14px; margin-top: 12px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .recipe h3 { margin: 0 0 6px; font-size: 1.15rem; }
        .recipe .yield { font-size: 0.9rem; opacity: 0.75; margin-bottom: 8px; line-height: 1.4; }
        .recipe .body { font-size: 1rem; line-height: 1.45; white-space: pre-wrap; opacity: 0.9; margin-bottom: 10px; }
        .ing-list { margin: 0 0 10px; padding: 0; list-style: none; }
        .ing-list li { padding: 6px 0; border-bottom: 1px dashed <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; font-size: 0.95rem; }
        .ing-list li:last-child { border-bottom: none; }
        .ing-main { display: flex; justify-content: space-between; gap: 10px; }
        .ing-qty { opacity: 0.75; white-space: nowrap; }
        .ing-prep { font-size: 0.85rem; opacity: 0.65; margin-top: 2px; }
        .ing-heading, .step-heading { font-size: 0.85rem; opacity: 0.65; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 0.03em; }
        .steps { margin: 0 0 10px; padding-left: 1.2rem; }
        .steps li { margin-bottom: 6px; line-height: 1.4; }
        .plate-imgs { display: flex; flex-wrap: wrap; gap: 8px; margin: 8px 0 10px; }
        .plate-imgs img { width: 72px; height: 72px; object-fit: cover; border-radius: 10px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; }
        .recipe-actions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
        .empty-slot { text-align: center; padding: 20px 12px; margin: 12px 0; border-radius: 14px; border: 2px dashed <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; opacity: 0.8; line-height: 1.4; }
        .add-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .actions-bar { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 600px; max-height: 94vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 72px; resize: vertical; }
        .field input:focus, .field textarea:focus, .field select:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .field-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .field-row .field { flex: 1; min-width: 90px; margin-bottom: 0; }
        .readonly-box { border-radius: 12px; padding: 10px 12px; font-size: 1.05rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #F3C5CC; color: #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #C5D0DE; color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .ing-builder, .steps-builder, .plate-builder { margin: 8px 0 14px; padding: 12px; border-radius: 14px; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .ing-builder h3, .steps-builder h3, .plate-builder h3 { margin: 0 0 10px; font-size: 1.05rem; }
        .ing-row { display: grid; grid-template-columns: 1.3fr 0.55fr 0.65fr 0.9fr auto; gap: 6px; margin-bottom: 8px; align-items: center; }
        @media (max-width: 560px) {
            .ing-row { grid-template-columns: 1fr 1fr; }
            .ing-row .ing-name { grid-column: 1 / -1; }
            .ing-row .ing-prep { grid-column: 1 / -1; }
        }
        .ing-row input, .ing-row select, .step-row input, .step-row textarea { width: 100%; box-sizing: border-box; border-radius: 10px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 8px 10px; font-size: 0.95rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .ing-remove, .step-remove { border: none; border-radius: 10px; padding: 8px 10px; cursor: pointer; background: #FDECEA; color: #B71C1C; font-size: 0.9rem; }
        .step-row { display: grid; grid-template-columns: 36px 1fr auto; gap: 8px; margin-bottom: 8px; align-items: start; }
        .step-num { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .step-row textarea { min-height: 44px; resize: vertical; }
        .plate-thumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .plate-thumb { position: relative; width: 80px; height: 80px; }
        .plate-thumb img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; }
        .plate-thumb button { position: absolute; top: -6px; right: -6px; border: none; border-radius: 999px; width: 24px; height: 24px; background: #B71C1C; color: white; cursor: pointer; font-size: 0.8rem; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .section-label { font-size: 0.9rem; opacity: 0.7; margin: 4px 0 8px; line-height: 1.35; }
        .menu-cost-hint { font-size: 0.85rem; opacity: 0.7; margin-top: 4px; }
        #print-root { display: none; }
        @media print {
            @page { margin: 0.4in; size: letter; }
            html, body {
                background: white !important;
                color: #000 !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                overflow: visible !important;
            }
            /* Hide all live UI so it doesn't create blank pages */
            body > *:not(#print-root) { display: none !important; }
            #print-root {
                display: block !important;
                position: static !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
            }
            .print-book-title { font-size: 16pt; font-weight: 700; margin: 0 0 2px; line-height: 1.2; }
            .print-book-sub { font-size: 9pt; margin: 0 0 10px; opacity: 0.8; }
            .print-cat-title { font-size: 12pt; font-weight: 700; margin: 12px 0 6px; padding-bottom: 3px; border-bottom: 1.5px solid #333; page-break-after: avoid; }
            .print-card {
                border: 1px solid #444;
                border-radius: 4px;
                padding: 10px 12px;
                margin: 0 0 10px;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            /* Single recipe: keep it to one block without forced extra pages */
            .print-single .print-card { margin-bottom: 0; }
            .print-card h2 { font-size: 13pt; margin: 0 0 3px; line-height: 1.2; }
            .print-meta { font-size: 9pt; margin: 0 0 6px; opacity: 0.85; line-height: 1.3; }
            .print-section { font-size: 8.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; margin: 7px 0 3px; }
            .print-ings { margin: 0; padding: 0; list-style: none; font-size: 9.5pt; }
            .print-ings li { display: flex; justify-content: space-between; gap: 8px; padding: 1px 0; border-bottom: 1px dotted #ccc; line-height: 1.25; }
            .print-ings li:last-child { border-bottom: none; }
            .print-prep { font-size: 8pt; opacity: 0.75; display: block; }
            .print-steps { margin: 0 0 0 1rem; padding: 0; font-size: 9.5pt; }
            .print-steps li { margin-bottom: 3px; line-height: 1.3; }
            .print-notes { font-size: 9.5pt; line-height: 1.3; margin: 0; white-space: pre-wrap; }
            .print-imgs { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px; }
            .print-imgs img { width: 70px; height: 70px; object-fit: cover; border: 1px solid #999; border-radius: 3px; }
            .print-empty { font-size: 9pt; opacity: 0.7; font-style: italic; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH/recipes" class="back-link">← <?php echo $is_sweet ? 'Back to Menu & Standardized Recipes' : 'Back to Menu & Standardized Recipes'; ?></a>
        <h1><?php echo $is_sweet ? 'Standardized Recipes' : 'Standardized Recipes'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Specs, ingredients, method & plate' : 'Specs, ingredients, method, and plate'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Build house specs with yield, portions, ingredients (+ prep method → <a href="/BOH/prep">Prep Lists</a>), numbered method steps, and plate notes/pics. Link a <a href="/BOH/menu">Menu</a> item for sell price · feeds <a href="/admin/costing">Costing</a>. Stock products live under Sandwich HQ → Inventory 💕'
                : 'House specs with yield, portions, prep methods (sync to <a href="/BOH/prep">Prep Lists</a>), steps, and plate notes. Link <a href="/BOH/menu">Menu</a> prices. Feeds <a href="/admin/costing">Costing</a>. Product setup is under Admin → Inventory.'; ?>
        </div>
        <div class="toolbar">
            <button type="button" class="btn btn-primary" id="add-cat-btn" data-perm="boh.recipes.std_edit"><?php echo $is_sweet ? '+ Category' : '+ Category'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-book-btn" data-perm="boh.recipes.std_view"><?php echo $is_sweet ? '🖨️ Print book' : 'Print book'; ?></button>
            <a href="/BOH/menu" class="btn btn-secondary"><?php echo $is_sweet ? '📋 Menu' : 'Menu'; ?></a>
            <a href="/admin/costing" class="btn btn-secondary"><?php echo $is_sweet ? '💰 Costing' : 'Costing'; ?></a>
            <button type="button" class="btn btn-secondary" id="expand-btn" data-perm="boh.recipes.std_view"><?php echo $is_sweet ? 'Expand all' : 'Expand all'; ?></button>
            <button type="button" class="btn btn-secondary" id="collapse-btn" data-perm="boh.recipes.std_view"><?php echo $is_sweet ? 'Collapse all' : 'Collapse all'; ?></button>
        </div>
        <div id="categories"></div>
        <div class="actions-bar">
            <button type="button" class="btn btn-secondary" id="reset-btn" data-perm="boh.recipes.std_edit"><?php echo $is_sweet ? 'Reset shells' : 'Reset shells'; ?></button>
            <a href="/BOH/recipes" class="btn btn-primary"><?php echo $is_sweet ? 'Menu & Standardized Recipes' : 'Menu & Standardized Recipes'; ?></a>
        </div>
    </div>

    <div id="print-root" aria-hidden="true"></div>

    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2 id="modal-title">Add</h2>
            <form id="form">
                <input type="hidden" id="m-mode"><input type="hidden" id="m-cat"><input type="hidden" id="m-recipe">
                <div class="field" id="f-icon" style="display:none"><label for="m-icon"><?php echo $is_sweet ? 'Emoji' : 'Emoji'; ?></label><input id="m-icon" maxlength="4"></div>
                <div class="field" id="f-title"><label for="m-title"><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="m-title" required></div>

                <!-- Category description uses f-hint; recipes hide this and use yield qty/unit -->
                <div class="field" id="f-hint"><label for="m-hint"><?php echo $is_sweet ? 'Description' : 'Description'; ?></label><input id="m-hint"></div>

                <div id="f-recipe-fields" style="display:none">
                    <div class="field-row" style="margin-bottom:12px;">
                        <div class="field">
                            <label for="m-yield-qty"><?php echo $is_sweet ? 'Yield qty' : 'Yield qty'; ?></label>
                            <input id="m-yield-qty" type="number" min="0" step="any" placeholder="e.g. 1">
                        </div>
                        <div class="field">
                            <label for="m-yield-unit"><?php echo $is_sweet ? 'Yield unit' : 'Yield unit'; ?></label>
                            <input id="m-yield-unit" list="yield-units" placeholder="<?php echo $is_sweet ? 'qt, servings, lb…' : 'qt, servings, lb…'; ?>">
                        </div>
                        <div class="field">
                            <label for="m-portions"><?php echo $is_sweet ? 'Portions' : 'Portions'; ?></label>
                            <input id="m-portions" type="number" min="0" step="any" placeholder="e.g. 12">
                        </div>
                    </div>
                    <datalist id="yield-units">
                        <option value="servings"><option value="serving"><option value="portions"><option value="qt"><option value="gal">
                        <option value="lb"><option value="oz"><option value="cup"><option value="batch"><option value="ea">
                    </datalist>

                    <div class="field">
                        <label for="m-menu"><?php echo $is_sweet ? 'Linked menu item' : 'Linked menu item'; ?></label>
                        <select id="m-menu">
                            <option value=""><?php echo $is_sweet ? '— None (set on Menu page) —' : '— None —'; ?></option>
                        </select>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Menu cost (from menu)' : 'Menu cost (from menu)'; ?></label>
                        <div class="readonly-box" id="m-menu-cost">—</div>
                        <p class="menu-cost-hint"><?php echo $is_sweet ? 'Optional — pulls sell price from your Menu. Edit prices on the Menu page.' : 'Sell price from Menu (optional).'; ?></p>
                    </div>

                    <div id="f-ingredients">
                        <div class="ing-builder">
                            <h3><?php echo $is_sweet ? 'Ingredients' : 'Ingredients'; ?></h3>
                            <p class="section-label"><?php echo $is_sweet ? 'Qty + unit for costing/inventory. Prep method (diced, sliced…) for the daily prep list ✨' : 'Qty + unit for costing. Prep method for prep lists.'; ?></p>
                            <div id="ing-rows"></div>
                            <button type="button" class="btn btn-small btn-ghost" id="add-ing-btn" style="margin-top:4px;"><?php echo $is_sweet ? '+ Ingredient' : '+ Ingredient'; ?></button>
                        </div>
                    </div>

                    <div class="steps-builder">
                        <h3><?php echo $is_sweet ? 'Method (steps)' : 'Method (steps)'; ?></h3>
                        <p class="section-label"><?php echo $is_sweet ? 'Step-by-step how to make it' : 'Step-by-step instructions'; ?></p>
                        <div id="step-rows"></div>
                        <button type="button" class="btn btn-small btn-ghost" id="add-step-btn" style="margin-top:4px;"><?php echo $is_sweet ? '+ Step' : '+ Step'; ?></button>
                    </div>

                    <div class="plate-builder">
                        <h3><?php echo $is_sweet ? 'Plate notes & pics' : 'Plate notes & pics'; ?></h3>
                        <div class="field" style="margin-bottom:8px;">
                            <label for="m-plate-notes"><?php echo $is_sweet ? 'Plate notes' : 'Plate notes'; ?></label>
                            <textarea id="m-plate-notes" placeholder="<?php echo $is_sweet ? 'Build, garnish, side placement…' : 'Build, garnish, plating…'; ?>"></textarea>
                        </div>
                        <label class="section-label"><?php echo $is_sweet ? 'Plate photos (optional, up to 4)' : 'Plate photos (optional, up to 4)'; ?></label>
                        <input type="file" id="m-plate-file" accept="image/*" multiple style="margin-bottom:8px;">
                        <div class="plate-thumbs" id="plate-thumbs"></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="m-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const KEY = 'pbj_heat_recipes_v1';
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const MENU_KEY = 'pbj_menu_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyRecipesPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var edit = canP('boh.recipes.std_edit');
                var view = canP('boh.recipes.std_view') || edit;
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('rec-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="rec-denied">No permission to view standardized recipes.</div>');
                    }
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof renderEquip === 'function') renderEquip(); } catch (e) {}
                try { if (typeof renderList === 'function') renderList(); } catch (e) {}
            }

        const defaults = <?php echo json_encode($default_categories, JSON_UNESCAPED_UNICODE); ?>;
        const units = <?php echo json_encode($units); ?>;
        const prepMethods = <?php echo json_encode($prep_methods); ?>;

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function normName(n) { return String(n || '').trim().toLowerCase().replace(/\s+/g, ' '); }
        function money(n) {
            if (n == null || n === '' || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
        }

        function shells() {
            return { categories: defaults.map(function (c) {
                return { id: c.id, title: c.title, icon: c.icon, hint: c.hint, open: false, recipes: [] };
            })};
        }

        function migrateRecipe(rec) {
            if (!Array.isArray(rec.ingredients)) rec.ingredients = [];
            rec.ingredients = rec.ingredients.map(function (ing) {
                return {
                    id: ing.id || uid(),
                    name: ing.name || '',
                    qty: ing.qty != null ? ing.qty : '',
                    unit: ing.unit || 'oz',
                    prepMethod: ing.prepMethod || ''
                };
            });
            // yield qty/unit from legacy yield string
            if ((rec.yieldQty === undefined || rec.yieldQty === '') && rec.yield) {
                var m = String(rec.yield).match(/^([\d.]+)\s*(.*)$/);
                if (m) {
                    rec.yieldQty = m[1];
                    rec.yieldUnit = (m[2] || '').trim();
                } else {
                    rec.yieldUnit = rec.yield;
                }
            }
            if (rec.yieldQty === undefined) rec.yieldQty = '';
            if (rec.yieldUnit === undefined) rec.yieldUnit = '';
            // method steps from body
            if (!Array.isArray(rec.methodSteps)) {
                rec.methodSteps = [];
                if (rec.body) {
                    String(rec.body).split(/\n+/).map(function (line) {
                        return line.replace(/^\s*\d+[\.\)]\s*/, '').trim();
                    }).filter(Boolean).forEach(function (text) {
                        rec.methodSteps.push({ id: uid(), text: text });
                    });
                }
            }
            if (rec.plateNotes === undefined) rec.plateNotes = '';
            if (!Array.isArray(rec.plateImages)) rec.plateImages = [];
            if (rec.menuItemId === undefined) rec.menuItemId = '';
            return rec;
        }

        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || !r.categories || !r.categories.length) return shells();
                r.categories.forEach(function (c) {
                    // migrate first shell Sauces → Appetizers
                    if (c.id === 'sauces') {
                        c.id = 'appetizers';
                        if (!c.title || /sauces/i.test(c.title)) {
                            c.title = 'Appetizers';
                            c.icon = c.icon || '🥟';
                            c.hint = c.hint && !/batch recipes/i.test(c.hint) ? c.hint : 'Starters, shares, small plates';
                        }
                    }
                    (c.recipes || []).forEach(migrateRecipe);
                });
                return r;
            } catch (e) { return shells(); }
        }
        function save(t) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (t) {
                var el = document.getElementById('toast');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
        }
        function loadIngMaster() {
            try {
                var r = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                if (!r || typeof r.items !== 'object') return { items: {} };
                return r;
            } catch (e) { return { items: {} }; }
        }
        function saveIngMaster(master) { localStorage.setItem(ING_KEY, JSON.stringify(master)); }
        function syncIngredientsToMaster(ingredients) {
            var master = loadIngMaster();
            (ingredients || []).forEach(function (ing) {
                var key = normName(ing.name);
                if (!key) return;
                if (!master.items[key]) {
                    master.items[key] = {
                        name: String(ing.name).trim(),
                        unit: ing.unit || 'ea',
                        costPerUnit: '', onHand: '', par: '', vendor: '',
                        category: 'canned_dry', packSize: '', sku: '', source: 'recipe',
                        enteredAt: Date.now()
                    };
                } else {
                    if (!master.items[key].name) master.items[key].name = String(ing.name).trim();
                    if (!master.items[key].unit && ing.unit) master.items[key].unit = ing.unit;
                }
            });
            saveIngMaster(master);
        }
        function loadMenu() {
            try {
                var r = JSON.parse(localStorage.getItem(MENU_KEY) || 'null');
                return r && Array.isArray(r.items) ? r.items : [];
            } catch (e) { return []; }
        }
        function menuById(id) {
            if (!id) return null;
            return loadMenu().find(function (m) { return m.id === id; }) || null;
        }
        function menuPrice(id) {
            var m = menuById(id);
            if (!m) return null;
            var p = parseFloat(m.price);
            return isNaN(p) ? null : p;
        }

        function find(id) { return state.categories.find(function (c) { return c.id === id; }); }
        var state = load();
        var modal = document.getElementById('modal');
        var draftIngredients = [];
        var draftSteps = [];
        var draftImages = [];

        function unitOptions(selected) {
            return units.map(function (u) {
                return '<option value="' + esc(u) + '"' + (u === selected ? ' selected' : '') + '>' + esc(u) + '</option>';
            }).join('');
        }
        function prepOptions(selected) {
            var opts = '<option value="">' + (isSweet ? 'Prep method…' : 'Prep method…') + '</option>';
            return opts + prepMethods.map(function (p) {
                return '<option value="' + esc(p) + '"' + (p === selected ? ' selected' : '') + '>' + esc(p) + '</option>';
            }).join('');
        }

        function fillMenuSelect(selectedId) {
            var sel = document.getElementById('m-menu');
            var items = loadMenu().slice().sort(function (a, b) { return (a.name || '').localeCompare(b.name || ''); });
            var html = '<option value="">' + (isSweet ? '— None (set on Menu page) —' : '— None —') + '</option>';
            items.forEach(function (m) {
                html += '<option value="' + esc(m.id) + '"' + (m.id === selectedId ? ' selected' : '') + '>' +
                    esc(m.name) + ' · ' + money(parseFloat(m.price)) + '</option>';
            });
            sel.innerHTML = html;
            if (selectedId) sel.value = selectedId;
            updateMenuCostDisplay();
        }
        function updateMenuCostDisplay() {
            var id = document.getElementById('m-menu').value;
            var p = menuPrice(id);
            var box = document.getElementById('m-menu-cost');
            if (p == null) {
                box.textContent = '—';
            } else {
                var m = menuById(id);
                box.textContent = money(p) + (m ? ' · ' + m.name : '');
            }
        }

        function renderIngRows() {
            var root = document.getElementById('ing-rows');
            if (!draftIngredients.length) {
                root.innerHTML = '<p class="section-label" style="margin:0 0 8px;">' + (isSweet ? 'No ingredients yet — tap + Ingredient' : 'No ingredients yet') + '</p>';
                return;
            }
            root.innerHTML = draftIngredients.map(function (ing, idx) {
                return '<div class="ing-row" data-idx="' + idx + '">' +
                    '<input class="ing-name" type="text" placeholder="' + (isSweet ? 'Ingredient' : 'Ingredient') + '" value="' + esc(ing.name) + '">' +
                    '<input class="ing-qty" type="number" step="any" min="0" placeholder="Qty" value="' + esc(ing.qty !== '' && ing.qty != null ? ing.qty : '') + '">' +
                    '<select class="ing-unit">' + unitOptions(ing.unit || 'oz') + '</select>' +
                    '<select class="ing-prep">' + prepOptions(ing.prepMethod || '') + '</select>' +
                    '<button type="button" class="ing-remove" data-remove="' + idx + '">×</button>' +
                    '</div>';
            }).join('');
        }
        function readIngRows() {
            var rows = document.querySelectorAll('#ing-rows .ing-row');
            draftIngredients = Array.prototype.map.call(rows, function (row) {
                var prev = draftIngredients[parseInt(row.dataset.idx, 10)] || {};
                return {
                    id: prev.id || uid(),
                    name: row.querySelector('.ing-name').value.trim(),
                    qty: row.querySelector('.ing-qty').value,
                    unit: row.querySelector('.ing-unit').value,
                    prepMethod: row.querySelector('.ing-prep').value
                };
            }).filter(function (ing) { return ing.name; });
            return draftIngredients;
        }

        function renderStepRows() {
            var root = document.getElementById('step-rows');
            if (!draftSteps.length) {
                root.innerHTML = '<p class="section-label" style="margin:0 0 8px;">' + (isSweet ? 'No steps yet — tap + Step' : 'No steps yet') + '</p>';
                return;
            }
            root.innerHTML = draftSteps.map(function (st, idx) {
                return '<div class="step-row" data-idx="' + idx + '">' +
                    '<div class="step-num">' + (idx + 1) + '</div>' +
                    '<textarea class="step-text" placeholder="' + (isSweet ? 'What happens in this step…' : 'Step instructions…') + '">' + esc(st.text || '') + '</textarea>' +
                    '<button type="button" class="step-remove" data-remove-step="' + idx + '">×</button>' +
                    '</div>';
            }).join('');
        }
        function readStepRows() {
            var rows = document.querySelectorAll('#step-rows .step-row');
            draftSteps = Array.prototype.map.call(rows, function (row) {
                var prev = draftSteps[parseInt(row.dataset.idx, 10)] || {};
                return { id: prev.id || uid(), text: row.querySelector('.step-text').value.trim() };
            }).filter(function (st) { return st.text; });
            return draftSteps;
        }

        function renderPlateThumbs() {
            var root = document.getElementById('plate-thumbs');
            root.innerHTML = draftImages.map(function (img, idx) {
                return '<div class="plate-thumb">' +
                    '<img src="' + esc(img.dataUrl) + '" alt="Plate">' +
                    '<button type="button" data-rm-img="' + idx + '" title="Remove">×</button>' +
                    '</div>';
            }).join('');
        }

        function yieldLabel(r) {
            if (r.yieldQty !== '' && r.yieldQty != null) {
                return String(r.yieldQty) + (r.yieldUnit ? ' ' + r.yieldUnit : '');
            }
            return r.yield || '';
        }

        function render() {
            document.getElementById('categories').innerHTML = state.categories.map(function (c) {
                var recipes = c.recipes || [];
                var body;
                if (!recipes.length) {
                    body = '<div class="empty-slot">' + (isSweet ? 'No recipes yet — add your house specs ✨' : 'No recipes yet. Add your house specs.') + '</div>';
                } else {
                    body = recipes.map(function (r) {
                        migrateRecipe(r);
                        var ings = r.ingredients || [];
                        var ingHtml = '';
                        if (ings.length) {
                            ingHtml = '<p class="ing-heading">' + (isSweet ? 'Ingredients' : 'Ingredients') + '</p><ul class="ing-list">' +
                                ings.map(function (ing) {
                                    var qty = (ing.qty !== '' && ing.qty != null) ? ing.qty : '—';
                                    return '<li><div class="ing-main"><span>' + esc(ing.name) + '</span><span class="ing-qty">' + esc(qty) + ' ' + esc(ing.unit || '') + '</span></div>' +
                                        (ing.prepMethod ? '<div class="ing-prep">' + (isSweet ? 'Prep: ' : 'Prep: ') + esc(ing.prepMethod) + '</div>' : '') +
                                        '</li>';
                                }).join('') + '</ul>';
                        } else {
                            ingHtml = '<p class="section-label">' + (isSweet ? 'No ingredient list yet — edit to add' : 'No ingredient list yet') + '</p>';
                        }
                        var steps = r.methodSteps || [];
                        var stepHtml = '';
                        if (steps.length) {
                            stepHtml = '<p class="step-heading">' + (isSweet ? 'Method' : 'Method') + '</p><ol class="steps">' +
                                steps.map(function (st) { return '<li>' + esc(st.text) + '</li>'; }).join('') + '</ol>';
                        } else if (r.body) {
                            stepHtml = '<div class="body">' + esc(r.body) + '</div>';
                        }
                        var plateHtml = '';
                        if (r.plateNotes) {
                            plateHtml += '<p class="step-heading">' + (isSweet ? 'Plate notes' : 'Plate notes') + '</p><div class="body">' + esc(r.plateNotes) + '</div>';
                        }
                        if (r.plateImages && r.plateImages.length) {
                            plateHtml += '<div class="plate-imgs">' + r.plateImages.map(function (img) {
                                return '<img src="' + esc(img.dataUrl) + '" alt="Plate">';
                            }).join('') + '</div>';
                        }
                        var y = yieldLabel(r);
                        var menuP = menuPrice(r.menuItemId);
                        var meta = [];
                        if (y) meta.push((isSweet ? 'Yield ' : 'Yield ') + y);
                        if (r.portions !== '' && r.portions != null) meta.push(esc(r.portions) + ' portions');
                        if (menuP != null) meta.push((isSweet ? 'Menu ' : 'Menu ') + money(menuP));

                        return '<div class="recipe"><h3>' + esc(r.title) + '</h3>' +
                            (meta.length ? '<div class="yield">' + meta.join(' · ') + '</div>' : '') +
                            ingHtml + stepHtml + plateHtml +
                            '<div class="recipe-actions">' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="print-recipe" data-cat="' + esc(c.id) + '" data-recipe="' + esc(r.id) + '">' + (isSweet ? '🖨️ Print card' : 'Print card') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="edit-recipe" data-need-perm="boh.recipes.std_edit" data-cat="' + esc(c.id) + '" data-recipe="' + esc(r.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="del-recipe" data-need-perm="boh.recipes.std_edit" data-cat="' + esc(c.id) + '" data-recipe="' + esc(r.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                            '</div></div>';
                    }).join('');
                }
                return '<div class="category' + (c.open ? ' open' : '') + '">' +
                    '<button type="button" class="category-header" data-act="toggle" data-cat="' + esc(c.id) + '">' +
                    '<span class="cat-icon">' + esc(c.icon || '📖') + '</span>' +
                    '<span class="cat-titles"><h2 class="cat-title">' + esc(c.title) + '</h2>' +
                    (c.hint ? '<p class="cat-hint">' + esc(c.hint) + '</p>' : '') + '</span>' +
                    '<span class="cat-count">' + recipes.length + '</span><span class="chevron">›</span></button>' +
                    '<div class="category-body">' + body +
                    '<div class="add-row">' +
                    '<button type="button" class="btn btn-small btn-primary" data-act="add-recipe" data-need-perm="boh.recipes.std_edit" data-cat="' + esc(c.id) + '">' + (isSweet ? '+ Recipe' : '+ Recipe') + '</button>' +
                    (recipes.length
                        ? '<button type="button" class="btn btn-small btn-ghost" data-act="print-cat" data-cat="' + esc(c.id) + '">' + (isSweet ? '🖨️ Print category' : 'Print category') + '</button>'
                        : '') +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit-cat" data-need-perm="boh.recipes.std_edit" data-cat="' + esc(c.id) + '">' + (isSweet ? 'Edit category' : 'Edit category') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del-cat" data-need-perm="boh.recipes.std_edit" data-cat="' + esc(c.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div></div>';
            }).join('');
        }

        function recipeCardHtml(r, catTitle) {
            migrateRecipe(r);
            var y = yieldLabel(r);
            var menuP = menuPrice(r.menuItemId);
            var meta = [];
            if (catTitle) meta.push(catTitle);
            if (y) meta.push((isSweet ? 'Yield ' : 'Yield ') + y);
            if (r.portions !== '' && r.portions != null) meta.push(r.portions + ' portions');
            if (menuP != null) meta.push((isSweet ? 'Menu ' : 'Menu ') + money(menuP));

            var ings = r.ingredients || [];
            var ingHtml = '';
            if (ings.length) {
                ingHtml = '<div class="print-section">' + (isSweet ? 'Ingredients' : 'Ingredients') + '</div><ul class="print-ings">' +
                    ings.map(function (ing) {
                        var qty = (ing.qty !== '' && ing.qty != null) ? ing.qty : '—';
                        return '<li><span>' + esc(ing.name) +
                            (ing.prepMethod ? '<span class="print-prep">' + (isSweet ? 'Prep: ' : 'Prep: ') + esc(ing.prepMethod) + '</span>' : '') +
                            '</span><span>' + esc(qty) + ' ' + esc(ing.unit || '') + '</span></li>';
                    }).join('') + '</ul>';
            } else {
                ingHtml = '<p class="print-empty">' + (isSweet ? 'No ingredients listed' : 'No ingredients listed') + '</p>';
            }

            var steps = r.methodSteps || [];
            var stepHtml = '';
            if (steps.length) {
                stepHtml = '<div class="print-section">' + (isSweet ? 'Method' : 'Method') + '</div><ol class="print-steps">' +
                    steps.map(function (st) { return '<li>' + esc(st.text) + '</li>'; }).join('') + '</ol>';
            } else if (r.body) {
                stepHtml = '<div class="print-section">' + (isSweet ? 'Method' : 'Method') + '</div><p class="print-notes">' + esc(r.body) + '</p>';
            }

            var plateHtml = '';
            if (r.plateNotes) {
                plateHtml += '<div class="print-section">' + (isSweet ? 'Plate notes' : 'Plate notes') + '</div><p class="print-notes">' + esc(r.plateNotes) + '</p>';
            }
            if (r.plateImages && r.plateImages.length) {
                plateHtml += '<div class="print-imgs">' + r.plateImages.map(function (img) {
                    return '<img src="' + esc(img.dataUrl) + '" alt="Plate">';
                }).join('') + '</div>';
            }

            return '<div class="print-card">' +
                '<h2>' + esc(r.title) + '</h2>' +
                (meta.length ? '<p class="print-meta">' + meta.map(esc).join(' · ') + '</p>' : '') +
                ingHtml + stepHtml + plateHtml +
                '</div>';
        }

        function printRecipes(opts) {
            opts = opts || {};
            var root = document.getElementById('print-root');
            var html = '';
            var dateStr = new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            var title = isSweet ? 'Standardized Recipe Book' : 'Standardized Recipe Book';
            var sub = dateStr + ' · ilovepbj ops';
            var single = false;

            if (opts.mode === 'recipe') {
                var cat = find(opts.catId);
                var r = cat && (cat.recipes || []).find(function (x) { return x.id === opts.recipeId; });
                if (!r) {
                    alert(isSweet ? 'Recipe not found' : 'Recipe not found');
                    return;
                }
                title = r.title || title;
                sub = (cat ? cat.title + ' · ' : '') + dateStr;
                html = recipeCardHtml(r, cat ? cat.title : '');
                single = true;
            } else if (opts.mode === 'category') {
                var c = find(opts.catId);
                if (!c) {
                    alert(isSweet ? 'Category not found' : 'Category not found');
                    return;
                }
                var recs = c.recipes || [];
                if (!recs.length) {
                    alert(isSweet ? 'No recipes in this category yet 💕' : 'No recipes in this category yet.');
                    return;
                }
                title = c.title + (isSweet ? ' — Recipes' : ' — Recipes');
                html = '<div class="print-cat-title">' + esc(c.icon || '') + ' ' + esc(c.title) + '</div>' +
                    recs.map(function (rec) { return recipeCardHtml(rec, c.title); }).join('');
            } else {
                // whole book
                var any = false;
                html = state.categories.map(function (cat) {
                    var recs = cat.recipes || [];
                    if (!recs.length) return '';
                    any = true;
                    return '<div class="print-cat-title">' + esc(cat.icon || '') + ' ' + esc(cat.title) + '</div>' +
                        recs.map(function (rec) { return recipeCardHtml(rec, cat.title); }).join('');
                }).join('');
                if (!any) {
                    alert(isSweet ? 'No recipes to print yet — add some first 💕' : 'No recipes to print yet.');
                    return;
                }
            }

            root.className = single ? 'print-single' : '';
            root.innerHTML =
                '<div class="print-book-title">' + esc(title) + '</div>' +
                '<p class="print-book-sub">' + esc(sub) + '</p>' +
                html;

            // Wait a tick so images/layout settle, then print; clear after
            var cleanup = function () {
                root.innerHTML = '';
                root.className = '';
                window.removeEventListener('afterprint', cleanup);
            };
            window.addEventListener('afterprint', cleanup);
            setTimeout(function () {
                window.print();
                // fallback cleanup if afterprint never fires
                setTimeout(cleanup, 1500);
            }, 50);
        }

        function openModal(mode, catId, recipeId) {
            document.getElementById('m-mode').value = mode;
            document.getElementById('m-cat').value = catId || '';
            document.getElementById('m-recipe').value = recipeId || '';
            var isCat = mode === 'cat' || mode === 'edit-cat';
            var isRecipe = !isCat;
            document.getElementById('f-icon').style.display = isCat ? 'block' : 'none';
            document.getElementById('f-hint').style.display = isCat ? 'block' : 'none';
            document.getElementById('f-recipe-fields').style.display = isRecipe ? 'block' : 'none';
            document.getElementById('f-hint').querySelector('label').textContent = isSweet ? 'Description' : 'Description';

            draftIngredients = [];
            draftSteps = [];
            draftImages = [];

            if (mode === 'cat') {
                document.getElementById('modal-title').textContent = isSweet ? 'New category' : 'New category';
                document.getElementById('m-title').value = '';
                document.getElementById('m-hint').value = '';
                document.getElementById('m-icon').value = '📖';
            } else if (mode === 'edit-cat') {
                var c = find(catId);
                document.getElementById('modal-title').textContent = isSweet ? 'Edit category' : 'Edit category';
                document.getElementById('m-title').value = c ? c.title : '';
                document.getElementById('m-hint').value = c ? (c.hint || '') : '';
                document.getElementById('m-icon').value = c ? (c.icon || '📖') : '📖';
            } else if (mode === 'edit-recipe') {
                var cat = find(catId);
                var r = cat && cat.recipes.find(function (x) { return x.id === recipeId; });
                if (r) migrateRecipe(r);
                document.getElementById('modal-title').textContent = isSweet ? 'Edit recipe' : 'Edit recipe';
                document.getElementById('m-title').value = r ? r.title : '';
                document.getElementById('m-yield-qty').value = r && r.yieldQty != null ? r.yieldQty : '';
                document.getElementById('m-yield-unit').value = r && r.yieldUnit ? r.yieldUnit : '';
                document.getElementById('m-portions').value = r && r.portions != null ? r.portions : '';
                document.getElementById('m-plate-notes').value = r ? (r.plateNotes || '') : '';
                draftIngredients = (r && r.ingredients ? r.ingredients : []).map(function (ing) {
                    return { id: ing.id || uid(), name: ing.name || '', qty: ing.qty != null ? ing.qty : '', unit: ing.unit || 'oz', prepMethod: ing.prepMethod || '' };
                });
                if (!draftIngredients.length) draftIngredients.push({ id: uid(), name: '', qty: '', unit: 'oz', prepMethod: '' });
                draftSteps = (r && r.methodSteps ? r.methodSteps : []).map(function (st) {
                    return { id: st.id || uid(), text: st.text || '' };
                });
                if (!draftSteps.length) draftSteps.push({ id: uid(), text: '' });
                draftImages = (r && r.plateImages ? r.plateImages : []).slice(0, 4);
                fillMenuSelect(r ? (r.menuItemId || '') : '');
            } else {
                document.getElementById('modal-title').textContent = isSweet ? 'Add recipe' : 'Add recipe';
                document.getElementById('m-title').value = '';
                document.getElementById('m-yield-qty').value = '';
                document.getElementById('m-yield-unit').value = '';
                document.getElementById('m-portions').value = '';
                document.getElementById('m-plate-notes').value = '';
                draftIngredients = [{ id: uid(), name: '', qty: '', unit: 'oz', prepMethod: '' }];
                draftSteps = [{ id: uid(), text: '' }];
                draftImages = [];
                fillMenuSelect('');
            }
            if (isRecipe) {
                renderIngRows();
                renderStepRows();
                renderPlateThumbs();
            }
            document.getElementById('m-plate-file').value = '';
            modal.classList.add('show');
            setTimeout(function () { document.getElementById('m-title').focus(); }, 40);
        }

        document.getElementById('m-menu').addEventListener('change', updateMenuCostDisplay);

        document.getElementById('add-ing-btn').addEventListener('click', function () {
            readIngRows();
            draftIngredients.push({ id: uid(), name: '', qty: '', unit: 'oz', prepMethod: '' });
            renderIngRows();
        });
        document.getElementById('ing-rows').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove]');
            if (!btn) return;
            readIngRows();
            draftIngredients.splice(parseInt(btn.dataset.remove, 10), 1);
            renderIngRows();
        });

        document.getElementById('add-step-btn').addEventListener('click', function () {
            readStepRows();
            draftSteps.push({ id: uid(), text: '' });
            renderStepRows();
        });
        document.getElementById('step-rows').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove-step]');
            if (!btn) return;
            readStepRows();
            draftSteps.splice(parseInt(btn.dataset.removeStep, 10), 1);
            renderStepRows();
        });

        document.getElementById('plate-thumbs').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-rm-img]');
            if (!btn) return;
            draftImages.splice(parseInt(btn.dataset.rmImg, 10), 1);
            renderPlateThumbs();
        });

        document.getElementById('m-plate-file').addEventListener('change', function (e) {
            var files = Array.prototype.slice.call(e.target.files || []);
            if (!files.length) return;
            var remaining = 4 - draftImages.length;
            if (remaining <= 0) {
                alert(isSweet ? 'Max 4 plate photos per recipe 💕' : 'Max 4 plate photos.');
                e.target.value = '';
                return;
            }
            files.slice(0, remaining).forEach(function (file) {
                if (!file.type || file.type.indexOf('image/') !== 0) return;
                var reader = new FileReader();
                reader.onload = function () {
                    var dataUrl = reader.result;
                    // light size guard
                    if (dataUrl && dataUrl.length > 900000) {
                        // try canvas compress
                        var img = new Image();
                        img.onload = function () {
                            var canvas = document.createElement('canvas');
                            var max = 900;
                            var w = img.width, h = img.height;
                            if (w > max || h > max) {
                                if (w > h) { h = Math.round(h * max / w); w = max; }
                                else { w = Math.round(w * max / h); h = max; }
                            }
                            canvas.width = w; canvas.height = h;
                            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                            var compressed = canvas.toDataURL('image/jpeg', 0.72);
                            draftImages.push({ id: uid(), dataUrl: compressed, name: file.name || 'plate' });
                            renderPlateThumbs();
                        };
                        img.src = dataUrl;
                    } else {
                        draftImages.push({ id: uid(), dataUrl: dataUrl, name: file.name || 'plate' });
                        renderPlateThumbs();
                    }
                };
                reader.readAsDataURL(file);
            });
            e.target.value = '';
        });

        document.getElementById('form').addEventListener('submit', function (e) {
            e.preventDefault();
            var mode = document.getElementById('m-mode').value;
            var catId = document.getElementById('m-cat').value;
            var recipeId = document.getElementById('m-recipe').value;
            var title = document.getElementById('m-title').value.trim();
            if (!title) return;

            if (mode === 'cat') {
                state.categories.push({
                    id: uid(),
                    title: title,
                    hint: document.getElementById('m-hint').value.trim(),
                    icon: document.getElementById('m-icon').value.trim() || '📖',
                    open: true,
                    recipes: []
                });
            } else if (mode === 'edit-cat') {
                var c = find(catId); if (!c) return;
                c.title = title;
                c.hint = document.getElementById('m-hint').value.trim();
                c.icon = document.getElementById('m-icon').value.trim() || '📖';
            } else {
                var ingredients = readIngRows().map(function (ing) {
                    return {
                        id: ing.id || uid(),
                        name: ing.name,
                        qty: ing.qty === '' ? '' : parseFloat(ing.qty),
                        unit: ing.unit || 'ea',
                        prepMethod: ing.prepMethod || ''
                    };
                });
                var steps = readStepRows();
                var portionsRaw = document.getElementById('m-portions').value;
                var portions = portionsRaw === '' ? '' : parseFloat(portionsRaw);
                var yieldQty = document.getElementById('m-yield-qty').value;
                var yieldUnit = document.getElementById('m-yield-unit').value.trim();
                var yieldLegacy = (yieldQty !== '' ? yieldQty : '') + (yieldUnit ? ' ' + yieldUnit : '');
                var payload = {
                    title: title,
                    yieldQty: yieldQty === '' ? '' : yieldQty,
                    yieldUnit: yieldUnit,
                    yield: yieldLegacy.trim(),
                    portions: isNaN(portions) ? '' : portions,
                    menuItemId: document.getElementById('m-menu').value || '',
                    ingredients: ingredients,
                    methodSteps: steps,
                    plateNotes: document.getElementById('m-plate-notes').value.trim(),
                    plateImages: draftImages.slice(0, 4),
                    body: steps.map(function (st, i) { return (i + 1) + '. ' + st.text; }).join('\n')
                };
                syncIngredientsToMaster(ingredients);

                if (mode === 'edit-recipe') {
                    var cat = find(catId); if (!cat) return;
                    var r = cat.recipes.find(function (x) { return x.id === recipeId; }); if (!r) return;
                    Object.keys(payload).forEach(function (k) { r[k] = payload[k]; });
                } else {
                    var cat2 = find(catId); if (!cat2) return;
                    cat2.recipes.push(Object.assign({ id: uid() }, payload));
                    cat2.open = true;
                }
            }
            save(true); modal.classList.remove('show'); render();
        });
        document.getElementById('m-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });

        document.getElementById('categories').addEventListener('click', function (e) {
                var b = e.target.closest('[data-act]'); if (b && b.getAttribute('data-need-perm') && !canP(b.getAttribute('data-need-perm'))) return;

            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var act = btn.dataset.act, catId = btn.dataset.cat, recipeId = btn.dataset.recipe, c = find(catId);
            if (act === 'toggle') { if (c) { c.open = !c.open; save(false); render(); } return; }
            if (act === 'add-recipe') { openModal('recipe', catId); return; }
            if (act === 'edit-recipe') { openModal('edit-recipe', catId, recipeId); return; }
            if (act === 'print-recipe') { printRecipes({ mode: 'recipe', catId: catId, recipeId: recipeId }); return; }
            if (act === 'print-cat') { printRecipes({ mode: 'category', catId: catId }); return; }
            if (act === 'del-recipe') {
                if (!c || !confirm(isSweet ? 'Remove this recipe?' : 'Remove this recipe?')) return;
                c.recipes = c.recipes.filter(function (x) { return x.id !== recipeId; }); save(true); render(); return;
            }
            if (act === 'edit-cat') { openModal('edit-cat', catId); return; }
            if (act === 'del-cat') {
                if (!confirm(isSweet ? 'Remove this category?' : 'Remove this category?')) return;
                state.categories = state.categories.filter(function (x) { return x.id !== catId; }); save(true); render();
            }
        });

        document.getElementById('add-cat-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.std_edit')) return; openModal('cat'); });
        document.getElementById('print-book-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.std_view')) return;
            printRecipes({ mode: 'book' });
        });
        document.getElementById('expand-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.std_view')) return; state.categories.forEach(function (c) { c.open = true; }); save(false); render(); });
        document.getElementById('collapse-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.std_view')) return; state.categories.forEach(function (c) { c.open = false; }); save(false); render(); });
        document.getElementById('reset-btn').addEventListener('click', function () {
                if (!canP('boh.recipes.std_edit')) return;
            if (!confirm(isSweet ? 'Reset to empty category shells? Clears recipes on this device.' : 'Reset to empty category shells?')) return;
            state = shells(); save(true); render();
        });
        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRecipesPerms);
            document.addEventListener('pbj-perms-ready', applyRecipesPerms);
    })();
    </script>

<script src="/first-10-minutes.js?v=1"></script>
<script>
(function () {
    if (!window.PbjFirst10) return;
    var sweet = <?php echo !empty($is_sweet) ? 'true' : 'false'; ?>;
    window.PbjFirst10.mountPageHint({ sweet: sweet, defaultStep: 'recipe' });
})();
</script>
</body>
</html>
