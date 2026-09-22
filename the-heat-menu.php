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
    <title><?php echo $is_sweet ? 'Menu Engineering' : 'Menu Engineering'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 12px; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.3rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.8rem; opacity: 0.7; margin-top: 4px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .item { border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 0 14px 14px 0; padding: 12px 14px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .item.fc-good { border-left-color: #43A047; }
        .item.fc-mid { border-left-color: #F9A825; }
        .item.fc-high { border-left-color: #E53935; }
        .item h3 { margin: 0 0 4px; font-size: 1.15rem; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .meta { font-size: 0.92rem; opacity: 0.85; line-height: 1.45; margin-bottom: 6px; }
        .price { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 700; }
        .eng { display: flex; flex-wrap: wrap; gap: 8px 14px; font-size: 0.92rem; margin-bottom: 8px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        .badge-good { background: #C8E6C9; color: #1B5E20; }
        .badge-mid { background: #FFF9C4; color: #F57F17; }
        .badge-high { background: #FFCDD2; color: #B71C1C; }
        .badge-na { background: #ECEFF1; color: #546E7A; }
        .badge-star { background: #FFF3E0; color: #E65100; }
        .badge-plow { background: #E3F2FD; color: #1565C0; }
        .badge-puzzle { background: #F3E5F5; color: #6A1B9A; }
        .badge-dog { background: #ECEFF1; color: #546E7A; }
        .matrix-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        @media (max-width: 520px) { .matrix-grid { grid-template-columns: 1fr; } }
        .matrix-cell { border-radius: 14px; padding: 12px 12px 10px; min-height: 88px; border: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .matrix-cell h3 { margin: 0 0 6px; font-size: 0.95rem; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .matrix-cell ul { margin: 0; padding-left: 16px; font-size: 0.88rem; line-height: 1.4; }
        .matrix-cell li { margin-bottom: 2px; }
        .matrix-cell.star { background: #FFF8F0; border-color: #FFCC80; }
        .matrix-cell.plowhorse { background: #F5FAFF; border-color: #90CAF9; }
        .matrix-cell.puzzle { background: #FAF5FC; border-color: #CE93D8; }
        .matrix-cell.dog { background: #F7F8F9; border-color: #CFD8DC; }
        .matrix-legend { font-size: 0.85rem; opacity: 0.75; line-height: 1.45; margin: 0 0 10px; }
        .empty { text-align: center; padding: 24px; opacity: 0.8; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.92rem; opacity: 0.75; margin: 0 0 10px; line-height: 1.4; }
        .suggest-banner { margin-top: 8px; padding: 10px 12px; border-radius: 12px; font-size: 0.92rem; line-height: 1.4; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .suggest-banner strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .filter-chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .filter-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .allergen-section { margin: 12px 0 4px; padding-top: 12px; border-top: 1px dashed <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .allergen-section > .alg-label { font-size: 0.85rem; opacity: 0.65; margin-bottom: 8px; }
        .allergen-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
        @media (max-width: 400px) { .allergen-checks { grid-template-columns: 1fr; } }
        .allergen-check {
            display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 12px; cursor: pointer; font-size: 0.92rem;
            <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?>
        }
        .allergen-check:has(input:checked) {
            <?php if ($is_sweet): ?>background: #FFF5F6; border-color: #E55163;<?php else: ?>background: #EEF2F8; border-color: #1A2A44;<?php endif; ?>
        }
        .allergen-check input { width: 16px; height: 16px; accent-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .alg-chips { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
        .alg-chip { font-size: 0.72rem; padding: 2px 7px; border-radius: 999px; background: #FFECB3; color: #6D4C00; }
        .alg-note { font-size: 0.82rem; opacity: 0.75; font-style: italic; margin-top: 4px; }
        .print-only { display: none; }
        @media print {
            .no-print, .back-link, .toolbar, .filters, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .modal-backdrop, .btn { display: none !important; }
            .print-only { display: block !important; }
            body { background: white; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card, .item { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
            .badge { border: 1px solid #999; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH/recipes" class="back-link no-print">← <?php echo $is_sweet ? 'Back to Menu & Standardized Recipes' : 'Back to Menu & Standardized Recipes'; ?></a>
        <h1><?php echo $is_sweet ? 'Menu Engineering' : 'Menu Engineering'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Sell price · plate cost · optional Star/Plowhorse matrix' : 'Sell price, plate cost, optional engineering matrix'; ?></p>
    </div>

    <div class="content">
        <div class="intro no-print">
            <?php echo $is_sweet
                ? 'Guest menu prices live here. Linked <a href="/BOH/recipe-cards">recipes</a> + costed ingredients show <strong>plate cost</strong>, <strong>FC %</strong>, and <strong>contribution $</strong>. Optional: add a <a href="/admin/pmix">PMIX</a> mix for the Star / Plowhorse / Puzzle / Dog matrix. Waste? <a href="/admin/waste">Waste log</a> 💕'
                : 'Menu prices + plate cost from recipes. Optional <a href="/admin/pmix">PMIX</a> unlocks popularity × margin matrix. <a href="/admin/waste">Waste log</a> is optional too.'; ?>
        </div>

        <div class="stats-row" id="stats-row">
            <div class="stat"><div class="num" id="stat-items">0</div><div class="lbl"><?php echo $is_sweet ? 'Menu items' : 'Menu items'; ?></div></div>
            <div class="stat"><div class="num" id="stat-avg-fc">—</div><div class="lbl"><?php echo $is_sweet ? 'Avg FC %' : 'Avg FC %'; ?></div></div>
            <div class="stat"><div class="num" id="stat-high">0</div><div class="lbl"><?php echo $is_sweet ? 'High FC items' : 'High FC items'; ?></div></div>
        </div>

        <div class="card no-print">
            <h2><?php echo $is_sweet ? 'Add menu item' : 'Add menu item'; ?></h2>
            <form id="add-form">
                <div class="field"><label><?php echo $is_sweet ? 'Menu name' : 'Menu name'; ?></label><input id="f-name" required placeholder="<?php echo $is_sweet ? 'e.g. Classic PB&J' : 'Item name'; ?>"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Sell price ($)' : 'Sell price ($)'; ?></label><input id="f-price" type="number" min="0" step="0.01" required placeholder="0.00"><div id="f-suggest" class="suggest-banner" style="display:none;margin-top:8px;"></div></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Menu category' : 'Menu category'; ?></label>
                        <select id="f-cat">
                            <option value="appetizers"><?php echo $is_sweet ? 'Appetizers' : 'Appetizers'; ?></option>
                            <option value="mains"><?php echo $is_sweet ? 'Mains & Sandwiches' : 'Mains'; ?></option>
                            <option value="sides"><?php echo $is_sweet ? 'Sides & Salads' : 'Sides'; ?></option>
                            <option value="sweets"><?php echo $is_sweet ? 'Sweets & Extras' : 'Sweets'; ?></option>
                            <option value="drinks"><?php echo $is_sweet ? 'Drinks' : 'Drinks'; ?></option>
                            <option value="other"><?php echo $is_sweet ? 'Other' : 'Other'; ?></option>
                        </select>
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes (optional)' : 'Notes (optional)'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Guest description, mods…' : 'Optional notes'; ?>"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;"><?php echo $is_sweet ? 'Add to menu ✨' : 'Add to menu'; ?></button>
            </form>
        </div>

        <div class="toolbar no-print">
            <button type="button" class="btn btn-secondary" id="print-btn" data-perm="boh.recipes.menu_view"><?php echo $is_sweet ? '🖨️ Print engineering' : 'Print engineering'; ?></button>
            <button type="button" class="btn btn-secondary" id="export-btn" data-perm="boh.recipes.menu_view"><?php echo $is_sweet ? 'Export CSV' : 'Export CSV'; ?></button>
            <a href="/admin/costing" class="btn btn-secondary"><?php echo $is_sweet ? 'Costing' : 'Costing'; ?></a>
            <a href="/admin/pmix" class="btn btn-secondary"><?php echo $is_sweet ? 'PMIX' : 'PMIX'; ?></a>
            <a href="/BOH/recipe-cards" class="btn btn-secondary"><?php echo $is_sweet ? 'Recipes' : 'Recipes'; ?></a>
        </div>

        <div class="card no-print" id="matrix-card">
            <h2><?php echo $is_sweet ? 'Menu matrix (optional)' : 'Menu matrix (optional)'; ?></h2>
            <p class="matrix-legend" id="matrix-legend">
                <?php echo $is_sweet
                    ? 'Popularity × contribution margin (Kasavana). Needs a <a href="/admin/pmix">PMIX session</a> with qty sold + plate costs. Totally optional — skip until you want it.'
                    : 'Optional Star/Plowhorse/Puzzle/Dog from PMIX qty × contribution. Needs PMIX + costs.'; ?>
            </p>
            <div class="matrix-grid" id="matrix-grid">
                <div class="matrix-cell star"><h3>⭐ Star <span class="badge badge-star" id="cnt-star">0</span></h3><ul id="list-star"></ul></div>
                <div class="matrix-cell plowhorse"><h3>🐴 Plowhorse <span class="badge badge-plow" id="cnt-plow">0</span></h3><ul id="list-plow"></ul></div>
                <div class="matrix-cell puzzle"><h3>🧩 Puzzle <span class="badge badge-puzzle" id="cnt-puzzle">0</span></h3><ul id="list-puzzle"></ul></div>
                <div class="matrix-cell dog"><h3>🐕 Dog <span class="badge badge-dog" id="cnt-dog">0</span></h3><ul id="list-dog"></ul></div>
            </div>
            <p class="hint" id="matrix-hint" style="margin:0;"></p>
        </div>

        <div class="filters no-print" id="filters">
            <button type="button" class="filter-chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
            <button type="button" class="filter-chip" data-filter="appetizers"><?php echo $is_sweet ? 'Appetizers' : 'Appetizers'; ?></button>
            <button type="button" class="filter-chip" data-filter="mains"><?php echo $is_sweet ? 'Mains' : 'Mains'; ?></button>
            <button type="button" class="filter-chip" data-filter="sides"><?php echo $is_sweet ? 'Sides' : 'Sides'; ?></button>
            <button type="button" class="filter-chip" data-filter="sweets"><?php echo $is_sweet ? 'Sweets' : 'Sweets'; ?></button>
            <button type="button" class="filter-chip" data-filter="drinks"><?php echo $is_sweet ? 'Drinks' : 'Drinks'; ?></button>
            <button type="button" class="filter-chip" data-filter="high"><?php echo $is_sweet ? 'High FC only' : 'High FC only'; ?></button>
            <button type="button" class="filter-chip" data-filter="star"><?php echo $is_sweet ? 'Stars' : 'Stars'; ?></button>
            <button type="button" class="filter-chip" data-filter="plowhorse"><?php echo $is_sweet ? 'Plowhorses' : 'Plowhorses'; ?></button>
            <button type="button" class="filter-chip" data-filter="puzzle"><?php echo $is_sweet ? 'Puzzles' : 'Puzzles'; ?></button>
            <button type="button" class="filter-chip" data-filter="dog"><?php echo $is_sweet ? 'Dogs' : 'Dogs'; ?></button>
        </div>

        <div class="print-only" style="margin-bottom:10px;font-size:11pt;">
            <strong><?php echo $is_sweet ? 'Menu Engineering Report' : 'Menu Engineering Report'; ?></strong>
            <span id="print-date"></span>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Menu items' : 'Menu items'; ?></h2>
            <p class="hint no-print"><?php echo $is_sweet ? 'Link a recipe to a menu item on Standardized Recipes so plate cost appears here.' : 'Link recipes to menu items to show plate cost.'; ?></p>
            <div id="list"></div>
        </div>

        <div class="actions-bar no-print">
            <a href="/admin/pmix" class="btn btn-secondary"><?php echo $is_sweet ? 'PMIX / ideal FC' : 'PMIX'; ?></a>
            <a href="/admin/waste" class="btn btn-secondary"><?php echo $is_sweet ? 'Waste log' : 'Waste log'; ?></a>
            <a href="/admin/costing" class="btn btn-secondary"><?php echo $is_sweet ? 'Costing' : 'Costing'; ?></a>
            <a href="/BOH/recipes" class="btn btn-primary"><?php echo $is_sweet ? 'Menu & Standardized Recipes' : 'Menu & Standardized Recipes'; ?></a>
        </div>
    </div>

    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2><?php echo $is_sweet ? 'Edit menu item' : 'Edit menu item'; ?></h2>
            <form id="edit-form">
                <input type="hidden" id="e-id">
                <div class="field"><label><?php echo $is_sweet ? 'Menu name' : 'Menu name'; ?></label><input id="e-name" required></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Sell price ($)' : 'Sell price ($)'; ?></label><input id="e-price" type="number" min="0" step="0.01" required><div id="e-suggest" class="suggest-banner" style="display:none;margin-top:8px;"></div></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Category' : 'Category'; ?></label>
                        <select id="e-cat">
                            <option value="appetizers"><?php echo $is_sweet ? 'Appetizers' : 'Appetizers'; ?></option>
                            <option value="mains"><?php echo $is_sweet ? 'Mains & Sandwiches' : 'Mains'; ?></option>
                            <option value="sides"><?php echo $is_sweet ? 'Sides & Salads' : 'Sides'; ?></option>
                            <option value="sweets"><?php echo $is_sweet ? 'Sweets & Extras' : 'Sweets'; ?></option>
                            <option value="drinks"><?php echo $is_sweet ? 'Drinks' : 'Drinks'; ?></option>
                            <option value="other"><?php echo $is_sweet ? 'Other' : 'Other'; ?></option>
                        </select>
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="e-notes"></div>
                <div class="allergen-section no-print">
                    <div class="alg-label"><?php echo $is_sweet ? 'Allergens (optional · US Big 9)' : 'Allergens (optional · US Big 9)'; ?></div>
                    <p class="hint" style="margin-top:0;"><?php echo $is_sweet ? 'Staff matrix for guest questions — leave blank if unused. Syncs house-wide.' : 'Optional staff reference; empty is fine. House-synced.'; ?></p>
                    <div id="e-allergen-checks" class="allergen-checks"></div>
                    <div class="field" style="margin-bottom:0;"><label><?php echo $is_sweet ? 'Allergen note (optional)' : 'Allergen note (optional)'; ?></label>
                        <input id="e-allergen-note" maxlength="200" placeholder="<?php echo $is_sweet ? 'e.g. fried in shared oil, can omit nuts' : 'e.g. fried in shared oil, can omit nuts'; ?>">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="e-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/shared-state.js?v=3"></script>
    <script src="/allergen-menu-shared.js?v=1"></script>
    <script src="/food-cost-shared.js?v=4"></script>
    <script>
    (function () {
        const KEY = 'pbj_menu_v1';
        const RECIPE_KEY = 'pbj_heat_recipes_v1';
        const ING_KEY = 'pbj_heat_ingredients_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var FC = window.PbjFoodCost;
        var A = window.PbjAllergenMenu;
        var allergenSync = null;
        var bucketById = {};
        function pushAllergenOverlay() {
            if (!A) return;
            if (!allergenSync) {
                allergenSync = A.wire({
                    getState: function () { return A.loadOverlayLocal(); },
                    setState: function (next) { A.saveOverlayLocal(next); }
                });
            }
            allergenSync.push(A.loadOverlayLocal());
        }
        function fillAllergenEditor(it) {
            var box = document.getElementById('e-allergen-checks');
            if (!box || !A) return;
            var allergens = (it && it.allergens) ? it.allergens : [];
            var tmp = document.createElement('div');
            tmp.innerHTML = A.renderCheckboxGrid(allergens, 'menu-e-alg');
            box.innerHTML = tmp.firstChild ? tmp.firstChild.innerHTML : '';
        }
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyMenuPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var edit = canP('boh.recipes.menu_edit');
                var view = canP('boh.recipes.menu_view') || edit;
                document.querySelectorAll('#add-form, form#add-form, .card form').forEach(function(f){ if (f.closest('.modal')) return; f.style.display = edit ? '' : 'none'; });
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('menu-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="menu-denied">No permission to view menu engineering.</div>');
                    }
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof renderEquip === 'function') renderEquip(); } catch (e) {}
                try { if (typeof renderList === 'function') renderList(); } catch (e) {}
            }

        var CAT_LABELS = {
            appetizers: 'Appetizers', mains: isSweet ? 'Mains & Sandwiches' : 'Mains',
            sides: isSweet ? 'Sides & Salads' : 'Sides', sweets: isSweet ? 'Sweets & Extras' : 'Sweets',
            drinks: 'Drinks', other: 'Other'
        };
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
        }
        function normName(n) { return String(n || '').trim().toLowerCase().replace(/\s+/g, ' '); }
        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                return r && Array.isArray(r.items) ? r : { items: [] };
            } catch (e) { return { items: [] }; }
        }
        function save(t) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (t) {
                var el = document.getElementById('toast');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
        }
        function loadMaster() {
            try {
                var r = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                return r && typeof r.items === 'object' ? r : { items: {} };
            } catch (e) { return { items: {} }; }
        }
        function loadRecipes() {
            try {
                var r = JSON.parse(localStorage.getItem(RECIPE_KEY) || 'null');
                return (r && r.categories) ? r.categories : [];
            } catch (e) { return []; }
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
        function recipeUnitsPerCase(item) {
            if (!item) return null;
            var packs = parseFloat(packOf(item));
            var perPack = parseFloat(item.recipeUnitsPerPack);
            if (isNaN(perPack) || perPack <= 0) return null;
            if (isNaN(packs) || packs <= 0) packs = 1;
            return packs * perPack;
        }
        function apCostPerRecipeUnit(item) {
            if (!item) return null;
            var caseP = parseFloat(casePriceOf(item));
            var units = recipeUnitsPerCase(item);
            if (!isNaN(caseP) && caseP >= 0 && casePriceOf(item) !== '' && units) return caseP / units;
            var direct = item.directRecipeUnitCost;
            if (direct !== undefined && direct !== null && direct !== '') {
                var cpu = parseFloat(direct);
                if (!isNaN(cpu)) return cpu;
            }
            return null;
        }
        function costPerRecipeUnit(item) {
            var ap = apCostPerRecipeUnit(item);
            if (ap == null) return null;
            var y = item.usableYieldPct;
            if (y === undefined || y === null || y === '') return ap;
            y = parseFloat(y);
            if (isNaN(y) || y <= 0 || y >= 100) return ap;
            return ap / (y / 100);
        }
        function plateCostForMenuId(menuId, master) {
            if (!menuId) return null;
            var cats = loadRecipes();
            var best = null;
            cats.forEach(function (cat) {
                (cat.recipes || []).forEach(function (rec) {
                    if (rec.menuItemId !== menuId) return;
                    var ings = rec.ingredients || [];
                    if (!ings.length) return;
                    var total = 0, missing = 0;
                    ings.forEach(function (ing) {
                        var item = master.items[normName(ing.name)];
                        var qty = parseFloat(ing.qty);
                        var cpu = costPerRecipeUnit(item);
                        if (cpu == null || isNaN(qty)) missing++;
                        else total += qty * cpu;
                    });
                    if (missing) return;
                    var portions = parseFloat(rec.portions);
                    var per = (!isNaN(portions) && portions > 0) ? total / portions : total;
                    if (best == null || per < best.per) {
                        best = { per: per, batch: total, recipe: rec.title, portions: portions };
                    }
                });
            });
            return best;
        }
        function fcBadge(pct) {
            if (pct == null || isNaN(pct)) return '<span class="badge badge-na">' + (isSweet ? 'Link recipe + cost' : 'No cost') + '</span>';
            if (pct < 30) return '<span class="badge badge-good">' + pct + '% FC</span>';
            if (pct <= 35) return '<span class="badge badge-mid">' + pct + '% FC</span>';
            return '<span class="badge badge-high">' + pct + '% FC</span>';
        }
        function fcClass(pct) {
            if (pct == null || isNaN(pct)) return '';
            if (pct < 30) return 'fc-good';
            if (pct <= 35) return 'fc-mid';
            return 'fc-high';
        }

        var state = load();
        var master = loadMaster();
        var filter = 'all';
        var modal = document.getElementById('modal');


        function fillSuggestBanner(el, portionCost, targetInputId) {
            if (!el) return;
            if (!FC || !FC.suggestedSellPrices || portionCost == null || isNaN(portionCost)) {
                el.style.display = 'none';
                el.innerHTML = '';
                return;
            }
            var sug = FC.suggestedSellPrices(portionCost);
            if (!sug) {
                el.style.display = 'none';
                el.innerHTML = '';
                return;
            }
            el.style.display = 'flex';
            el.innerHTML = (isSweet ? 'Suggested menu price: ' : 'Suggested menu price: ') +
                '<strong>' + money(sug.at30) + '–' + money(sug.at25) + '</strong>' +
                ' <span class="muted">(25–30% FC)</span>' +
                ' · mid <strong>' + money(sug.at275) + '</strong>' +
                '<button type="button" class="btn btn-small btn-primary" data-act="apply-suggest" data-target="' + esc(targetInputId) + '" data-price="' + sug.at275 + '">' +
                (isSweet ? 'Apply mid' : 'Apply mid') + '</button>';
        }
        function shouldShowSuggestForPrice(priceVal) {
            var p = parseFloat(priceVal);
            return priceVal === '' || priceVal == null || isNaN(p) || p <= 0;
        }

        function enrich(it) {
            var sell = parseFloat(it.price);
            var plate = plateCostForMenuId(it.id, master);
            var per = plate ? plate.per : null;
            var fc = (per != null && !isNaN(sell) && sell > 0) ? Math.round((per / sell) * 1000) / 10 : null;
            var contrib = (per != null && !isNaN(sell)) ? sell - per : null;
            var bucket = bucketById[String(it.id)] || null;
            return { it: it, sell: sell, plate: plate, per: per, fc: fc, contrib: contrib, bucket: bucket };
        }

        function latestPmixSession() {
            if (!FC) return null;
            var p = FC.loadPmix();
            var sessions = (p && p.sessions) ? p.sessions.slice() : [];
            if (!sessions.length) return null;
            sessions.sort(function (a, b) { return (b.updatedAt || 0) - (a.updatedAt || 0); });
            return sessions[0];
        }

        function bucketBadge(bucket) {
            if (!bucket || bucket === 'no_sales' || bucket === 'unknown') return '';
            var map = {
                star: ['badge-star', isSweet ? 'Star' : 'Star'],
                plowhorse: ['badge-plow', isSweet ? 'Plowhorse' : 'Plowhorse'],
                puzzle: ['badge-puzzle', isSweet ? 'Puzzle' : 'Puzzle'],
                dog: ['badge-dog', isSweet ? 'Dog' : 'Dog']
            };
            var m = map[bucket];
            if (!m) return '';
            return ' <span class="badge ' + m[0] + '">' + m[1] + '</span>';
        }

        function renderMatrix() {
            bucketById = {};
            var session = latestPmixSession();
            var hint = document.getElementById('matrix-hint');
            var buckets = { star: [], plowhorse: [], puzzle: [], dog: [] };
            if (!FC) {
                if (hint) hint.textContent = '';
                ['star', 'plow', 'puzzle', 'dog'].forEach(function (k) {
                    var el = document.getElementById('list-' + k);
                    if (el) el.innerHTML = '';
                    var c = document.getElementById('cnt-' + k);
                    if (c) c.textContent = '0';
                });
                return;
            }
            var matrix = FC.menuEngineeringMatrix(session);
            (matrix.rows || []).forEach(function (r) {
                if (r && r.id) bucketById[String(r.id)] = r.bucket;
                if (r && buckets[r.bucket]) buckets[r.bucket].push(r);
            });
            function fill(key, arr) {
                var ul = document.getElementById('list-' + (key === 'plowhorse' ? 'plow' : key));
                var cnt = document.getElementById('cnt-' + (key === 'plowhorse' ? 'plow' : key));
                if (cnt) cnt.textContent = String(arr.length);
                if (!ul) return;
                if (!arr.length) {
                    ul.innerHTML = '<li style="list-style:none;opacity:0.55;margin-left:-16px;">' + (isSweet ? '—' : '—') + '</li>';
                    return;
                }
                arr.sort(function (a, b) { return (b.qty || 0) - (a.qty || 0); });
                ul.innerHTML = arr.slice(0, 8).map(function (r) {
                    return '<li>' + esc(r.name) +
                        ' <span style="opacity:0.7;">· ' + esc(r.qty) + (isSweet ? ' sold' : '') +
                        (r.contrib != null ? ' · $' + (Math.round(r.contrib * 100) / 100).toFixed(2) + ' cm' : '') +
                        '</span></li>';
                }).join('') + (arr.length > 8 ? '<li style="opacity:0.6;">+' + (arr.length - 8) + ' more</li>' : '');
            }
            fill('star', buckets.star);
            fill('plowhorse', buckets.plowhorse);
            fill('puzzle', buckets.puzzle);
            fill('dog', buckets.dog);

            if (hint) {
                if (!session || !session.items || !session.items.length) {
                    hint.innerHTML = isSweet
                        ? 'No PMIX yet — matrix stays empty until you log qty sold on <a href="/admin/pmix">PMIX</a>. Plate costs still show below.'
                        : 'Add a PMIX session with qty sold to classify items.';
                } else if (!matrix.classifiable) {
                    hint.innerHTML = isSweet
                        ? 'PMIX loaded, but need plate costs + sell prices to classify. Cost on <a href="/admin/costing">Costing</a> and link recipes.'
                        : 'PMIX present; need plate cost + price to classify.';
                } else {
                    var label = session.label || session.date || 'latest';
                    hint.textContent = (isSweet ? 'Using PMIX “' : 'PMIX “') + label + '” · avg qty ' +
                        matrix.avgQty + ' · avg contrib $' + matrix.avgContrib +
                        ' · ' + matrix.classifiable + (isSweet ? ' classified' : ' classified');
                }
            }
        }

        function render() {
            master = loadMaster();
            renderMatrix();
            var root = document.getElementById('list');
            var enriched = state.items.map(enrich);
            var withFc = enriched.filter(function (e) { return e.fc != null; });
            var high = withFc.filter(function (e) { return e.fc > 35; }).length;
            var avg = withFc.length ? Math.round((withFc.reduce(function (s, e) { return s + e.fc; }, 0) / withFc.length) * 10) / 10 : null;
            document.getElementById('stat-items').textContent = state.items.length;
            document.getElementById('stat-avg-fc').textContent = avg != null ? avg + '%' : '—';
            document.getElementById('stat-high').textContent = high;

            var list = enriched.filter(function (e) {
                if (filter === 'high') return e.fc != null && e.fc > 35;
                if (filter === 'star' || filter === 'plowhorse' || filter === 'puzzle' || filter === 'dog') {
                    return e.bucket === filter;
                }
                if (filter === 'all') return true;
                return (e.it.category || 'other') === filter;
            }).sort(function (a, b) {
                var ca = (a.it.category || '').localeCompare(b.it.category || '');
                if (ca) return ca;
                // higher FC first within category when useful
                if (a.fc != null && b.fc != null && a.fc !== b.fc) return b.fc - a.fc;
                return (a.it.name || '').localeCompare(b.it.name || '');
            });

            if (!state.items.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No menu items yet — add your guest menu above ✨' : 'No menu items yet.') + '</div>';
                return;
            }
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'Nothing in this filter' : 'Nothing in this filter') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (e) {
                var it = e.it;
                var sugHtml = '';
                if (e.per != null && shouldShowSuggestForPrice(it.price)) {
                    var sug = (FC && FC.suggestedSellPrices) ? FC.suggestedSellPrices(e.per) : null;
                    if (sug) {
                        sugHtml = '<div class="suggest-banner no-print">' +
                            (isSweet ? 'Suggested menu price: ' : 'Suggested menu price: ') +
                            '<strong>' + money(sug.at30) + '–' + money(sug.at25) + '</strong>' +
                            ' <span class="muted">(25–30% FC)</span>' +
                            ' · mid <strong>' + money(sug.at275) + '</strong>' +
                            '<button type="button" class="btn btn-small btn-primary" data-act="apply-list-price" data-need-perm="boh.recipes.menu_edit" data-id="' + esc(it.id) + '" data-price="' + sug.at275 + '">' +
                            (isSweet ? 'Apply mid' : 'Apply mid') + '</button></div>';
                    }
                }
                return '<div class="item ' + fcClass(e.fc) + '"><h3>' + esc(it.name) + ' ' + fcBadge(e.fc) + bucketBadge(e.bucket) + '</h3>' +
                    '<div class="meta"><span class="price">' + money(e.sell) + '</span> · ' +
                    esc(CAT_LABELS[it.category] || it.category || 'Other') +
                    (it.notes ? ' · ' + esc(it.notes) : '') + '</div>' +
                    '<div class="eng">' +
                    '<span>' + (isSweet ? 'Plate cost: ' : 'Plate: ') + '<strong>' + (e.per != null ? money(e.per) : '—') + '</strong></span>' +
                    '<span>' + (isSweet ? 'Contribution: ' : 'Contrib: ') + '<strong>' + (e.contrib != null ? money(e.contrib) : '—') + '</strong></span>' +
                    (e.plate && e.plate.recipe ? '<span class="muted">' + (isSweet ? 'via ' : 'via ') + esc(e.plate.recipe) + '</span>' : '') +
                    '</div>' + sugHtml +
                    (function () {
                        var algs = (it.allergens && it.allergens.length) ? it.allergens : [];
                        var note = it.allergenNote || '';
                        if (A && it.id) {
                            var ov = A.loadOverlayLocal();
                            if (ov.items && ov.items[it.id]) {
                                if (ov.items[it.id].allergens && ov.items[it.id].allergens.length) algs = ov.items[it.id].allergens;
                                if (ov.items[it.id].allergenNote) note = ov.items[it.id].allergenNote;
                            }
                        }
                        if (!algs.length && !note) return '';
                        var chips = algs.length
                            ? '<div class="alg-chips">' + algs.map(function (id) {
                                return '<span class="alg-chip">' + esc(A ? A.shortFor(id) : id) + '</span>';
                              }).join('') + '</div>'
                            : '';
                        var n = note ? '<div class="alg-note">' + esc(note) + '</div>' : '';
                        return chips + n;
                    })() +
                    '<div class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-need-perm="boh.recipes.menu_edit" data-id="' + esc(it.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-need-perm="boh.recipes.menu_edit" data-id="' + esc(it.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        document.getElementById('add-form').addEventListener('submit', function (e) {
                if (!canP('boh.recipes.menu_edit')) { e.preventDefault(); return; }
            e.preventDefault();
            state.items.push({
                id: uid(),
                name: document.getElementById('f-name').value.trim(),
                price: document.getElementById('f-price').value,
                category: document.getElementById('f-cat').value,
                notes: document.getElementById('f-notes').value.trim()
            });
            e.target.reset();
            document.getElementById('f-cat').value = 'appetizers';
            save(true); render();
        });

        document.getElementById('filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.filter-chip');
            if (!chip) return;
            filter = chip.dataset.filter;
            document.querySelectorAll('.filter-chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
            render();
        });

        document.getElementById('list').addEventListener('click', function (e) {
                var b = e.target.closest('[data-act]'); if (b && b.getAttribute('data-need-perm') && !canP(b.getAttribute('data-need-perm'))) return;

            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            var it = state.items.find(function (x) { return x.id === id; });
            if (!it) return;
            if (btn.dataset.act === 'apply-list-price') {
                var applyPrice = parseFloat(btn.dataset.price);
                if (isNaN(applyPrice)) return;
                it.price = String(applyPrice);
                save(true); render(); return;
            }
            if (btn.dataset.act === 'del') {
                if (!confirm(isSweet ? 'Remove this menu item?' : 'Remove this menu item?')) return;
                state.items = state.items.filter(function (x) { return x.id !== id; });
                save(true);
                if (A) {
                    A.clearItemAllergens(id);
                    pushAllergenOverlay();
                }
                render(); return;
            }
            document.getElementById('e-id').value = it.id;
            document.getElementById('e-name').value = it.name || '';
            document.getElementById('e-price').value = it.price != null ? it.price : '';
            document.getElementById('e-cat').value = it.category || 'other';
            document.getElementById('e-notes').value = it.notes || '';
            var algNote = it.allergenNote || '';
            var algList = it.allergens || [];
            if (A) {
                var ov2 = A.loadOverlayLocal();
                if (ov2.items && ov2.items[it.id]) {
                    if (ov2.items[it.id].allergens) algList = ov2.items[it.id].allergens;
                    if (ov2.items[it.id].allergenNote != null && ov2.items[it.id].allergenNote !== '') algNote = ov2.items[it.id].allergenNote;
                }
            }
            document.getElementById('e-allergen-note').value = algNote;
            fillAllergenEditor({ id: it.id, allergens: algList, allergenNote: algNote });
            master = loadMaster();
            var plate = plateCostForMenuId(it.id, master);
            if (plate && plate.per != null) fillSuggestBanner(document.getElementById('e-suggest'), plate.per, 'e-price');
            else fillSuggestBanner(document.getElementById('e-suggest'), null, 'e-price');
            modal.classList.add('show');
        });

        document.getElementById('edit-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('e-id').value;
            var it = state.items.find(function (x) { return x.id === id; });
            if (!it) return;
            it.name = document.getElementById('e-name').value.trim();
            it.price = document.getElementById('e-price').value;
            it.category = document.getElementById('e-cat').value;
            it.notes = document.getElementById('e-notes').value.trim();
            var algNoteSave = document.getElementById('e-allergen-note').value.trim();
            var algSave = A ? A.readCheckboxes(document.getElementById('e-allergen-checks')) : [];
            it.allergens = algSave;
            it.allergenNote = algNoteSave;
            save(true);
            if (A) {
                A.setItemAllergens(it.id, algSave, algNoteSave, it.name);
                pushAllergenOverlay();
            }
            modal.classList.remove('show'); render();
        });
        document.getElementById('e-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act="apply-suggest"]');
            if (!btn) return;
            e.preventDefault();
            var targetId = btn.dataset.target;
            var price = parseFloat(btn.dataset.price);
            var input = targetId ? document.getElementById(targetId) : null;
            if (!input || isNaN(price)) return;
            input.value = String(price);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });

        document.getElementById('print-btn').addEventListener('click', function () {
            document.getElementById('print-date').textContent = ' · ' + new Date().toLocaleString();
            window.print();
        });
        document.getElementById('export-btn').addEventListener('click', function () {
            master = loadMaster();
            renderMatrix();
            var rows = [['Name', 'Category', 'Sell', 'Plate cost', 'FC %', 'Contribution', 'Bucket', 'Recipe']];
            state.items.map(enrich).forEach(function (e) {
                rows.push([
                    e.it.name || '',
                    CAT_LABELS[e.it.category] || e.it.category || '',
                    e.sell != null && !isNaN(e.sell) ? e.sell : '',
                    e.per != null ? Math.round(e.per * 100) / 100 : '',
                    e.fc != null ? e.fc : '',
                    e.contrib != null ? Math.round(e.contrib * 100) / 100 : '',
                    e.bucket || '',
                    e.plate ? e.plate.recipe : ''
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
            a.download = 'menu-engineering.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        });

        if (A) {
            allergenSync = A.wire({
                getState: function () { return A.loadOverlayLocal(); },
                setState: function (next) {
                    A.saveOverlayLocal(next);
                    try { render(); } catch (e) {}
                }
            });
        }
        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyMenuPerms);
            document.addEventListener('pbj-perms-ready', applyMenuPerms);
    })();
    </script>

<script src="/first-10-minutes.js?v=1"></script>
<script>
(function () {
    if (!window.PbjFirst10) return;
    var sweet = <?php echo !empty($is_sweet) ? 'true' : 'false'; ?>;
    window.PbjFirst10.mountPageHint({ sweet: sweet, defaultStep: 'menu_price' });
})();
</script>
</body>
</html>
