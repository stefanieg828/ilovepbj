<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

// Station shells only — prep items left blank for each kitchen to fill
$default_stations = $is_sweet
    ? [
        ['id' => 'cold',  'title' => 'Cold Prep',     'icon' => '🥗', 'hint' => 'Sauces, salads, batch cold items'],
        ['id' => 'hot',   'title' => 'Hot Prep',      'icon' => '🔥', 'hint' => 'Soups, braises, batch hot items'],
        ['id' => 'line',  'title' => 'Line Check',    'icon' => '🔪', 'hint' => 'Station pars before service'],
        ['id' => 'grill', 'title' => 'Grill / Flat',  'icon' => '🥩', 'hint' => 'Grill set, tools, backup product'],
        ['id' => 'fry',   'title' => 'Fry Station',   'icon' => '🍟', 'hint' => 'Oil, baskets, backup frozen'],
        ['id' => 'expo',  'title' => 'Expo / Pass',   'icon' => '🍽️', 'hint' => 'Plates, garnishes, ticket flow'],
    ]
    : [
        ['id' => 'cold',  'title' => 'Cold Prep',     'icon' => '🥗', 'hint' => 'Sauces, salads, batch cold items'],
        ['id' => 'hot',   'title' => 'Hot Prep',      'icon' => '🔥', 'hint' => 'Soups, braises, batch hot items'],
        ['id' => 'line',  'title' => 'Line Check',    'icon' => '🔪', 'hint' => 'Station pars before service'],
        ['id' => 'grill', 'title' => 'Grill / Flat',  'icon' => '🥩', 'hint' => 'Grill set, tools, backup product'],
        ['id' => 'fry',   'title' => 'Fry Station',   'icon' => '🍟', 'hint' => 'Oil, baskets, backup frozen'],
        ['id' => 'expo',  'title' => 'Expo / Pass',   'icon' => '🍽️', 'hint' => 'Plates, garnishes, ticket flow'],
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'Prep Lists & Line Checks' : 'Prep Lists & Line Checks'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
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
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .progress-card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .progress-top { display: flex; justify-content: space-between; margin-bottom: 8px; gap: 10px; }
        .progress-bar { height: 12px; border-radius: 999px; background: <?php echo $is_sweet ? '#F7E0E4' : '#D9E0EA'; ?>; overflow: hidden; }
        .progress-fill { height: 100%; width: 0%; border-radius: 999px; transition: width 0.25s; <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .sync-card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .sync-card p { margin: 0 0 12px; line-height: 1.45; opacity: 0.9; font-size: 0.98rem; }
        .sync-card .sync-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .sync-meta { font-size: 0.88rem; opacity: 0.65; margin-top: 10px; }
        .source-tag { display: inline-block; font-size: 0.75rem; padding: 2px 8px; border-radius: 999px; margin-top: 4px; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .station { background: white; border-radius: 18px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); overflow: hidden; }
        .station-header { display: flex; align-items: center; gap: 12px; padding: 16px 18px; cursor: pointer; width: 100%; border: none; background: transparent; text-align: left; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .station-header:hover { background: <?php echo $is_sweet ? '#FFF8F9' : '#F8F5F1'; ?>; }
        .st-icon { width: 44px; height: 44px; min-width: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
        .st-titles { flex: 1; min-width: 0; }
        .st-title { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0; }
        .st-hint { margin: 4px 0 0; font-size: 0.9rem; opacity: 0.7; }
        .st-count { font-size: 0.9rem; opacity: 0.65; white-space: nowrap; }
        .chevron { opacity: 0.5; transition: transform 0.2s; }
        .station.open .chevron { transform: rotate(90deg); }
        .station-body { display: none; padding: 0 14px 16px; border-top: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .station.open .station-body { display: block; }
        .check-item { display: flex; flex-direction: column; align-items: stretch; gap: 0; padding: 10px 8px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; user-select: none; }
        .check-item:last-of-type { border-bottom: none; }
        .check-main { display: flex; align-items: flex-start; gap: 12px; width: 100%; border: none; background: transparent; padding: 4px 0; margin: 0; text-align: left; cursor: pointer; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .check-main:hover { opacity: 0.92; }
        .checkbox { width: 26px; height: 26px; min-width: 26px; border-radius: 8px; border: 2px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; display: flex; align-items: center; justify-content: center; color: white; margin-top: 1px; }
        .check-item.done .checkbox { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .check-item.done .checkbox::after { content: '✓'; }
        .check-body { flex: 1; min-width: 0; }
        .check-label { font-size: 1.08rem; line-height: 1.35; }
        .check-par { font-size: 0.9rem; opacity: 0.7; margin-top: 2px; }
        .check-item.done .check-label { opacity: 0.5; text-decoration: line-through; }
        .assign-badge {
            display: inline-block; margin-top: 4px; font-size: 0.78rem; border-radius: 999px; padding: 2px 8px;
            <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?>
        }
        .assign-badge.mine { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .assign-filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .filter-chip {
            border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer;
            background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
        }
        .filter-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .item-actions { display: flex; gap: 6px; margin-top: 6px; margin-left: 38px; }
        .task-photo-row { margin-left: 38px; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { <?php if ($is_sweet): ?>background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A;<?php else: ?>background: #FFF8E8; color: #5C4B1A; border-color: #E0D2A0;<?php endif; ?> }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .empty-slot { text-align: center; padding: 20px 12px; margin: 12px 0; border-radius: 14px; border: 2px dashed <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; opacity: 0.8; line-height: 1.4; }
        .add-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .actions-bar { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus, .field textarea:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .print-only { display: none; }
        /* When printing a single station, hide the rest */
        body.print-one-station .station { display: none !important; }
        body.print-one-station .station.print-target { display: block !important; }
        @media print {
            .no-print, .back-link, .toolbar, .assign-filters, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .btn, .modal-backdrop, .sync-card, .progress-card, .intro, .item-actions, .add-row, .source-tag, .chevron, .st-count { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 12px 16px; }
            h1 { font-size: 1.4rem; }
            .subtitle { display: none; }
            .content { padding: 8px 12px; max-width: none; }
            .station { box-shadow: none; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 10px; break-inside: avoid; page-break-inside: avoid; }
            .station.open .station-body, .station-body { display: block !important; }
            .station-header { cursor: default; padding: 8px 10px; }
            .st-title { font-size: 1.05rem; color: #000 !important; }
            .st-hint { font-size: 0.8rem; }
            .st-icon { width: 28px; height: 28px; min-width: 28px; font-size: 1rem; border-width: 1px; }
            .check-item { padding: 6px 4px; border-bottom: 1px solid #ddd; page-break-inside: avoid; cursor: default; }
            .check-main { cursor: default; color: #000 !important; }
            .check-item.done .check-label { opacity: 1; text-decoration: none; }
            .checkbox { width: 16px; height: 16px; min-width: 16px; border-radius: 3px; border: 1.5px solid #333; background: white !important; }
            .check-item.done .checkbox::after { content: none; }
            .check-label { font-size: 0.95rem; }
            .check-par { font-size: 0.8rem; }
            .item-actions { display: none !important; }
            .print-box { display: inline-block; width: 14px; height: 14px; border: 1.5px solid #333; margin-right: 2px; vertical-align: middle; }
            body.print-one-station .station { display: none !important; }
            body.print-one-station .station.print-target { display: block !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH" class="back-link">← <?php echo pbj_back_to_hub('boh'); ?></a>
        <h1><?php echo $is_sweet ? 'Prep Lists & Line Checks' : 'Prep Lists & Line Checks'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Pars, prep & station ready checks' : 'Daily prep, pars, and station readiness'; ?></p>
    </div>

    <div class="content">
        <div class="intro no-print">
            <?php echo $is_sweet
                ? 'Pull prep tasks from your <a href="/BOH/recipe-cards" data-perm="boh.prep.open_recipes" style="color:#E55163;font-weight:600;">Standardized Recipes</a>, assign who owns each item, check off live across the house, snap 📷 photos, and ping management when prep is done 🔪'
                : 'Sync prep from recipes, assign tasks, check off live, attach photos, and notify managers when prep is complete.'; ?>
        </div>

        <div class="sync-card no-print">
            <p><?php echo $is_sweet
                ? 'Recipe cards with prep methods (diced, sliced, cooked…) build your daily list. Manual items stay put. Re-sync keeps checkmarks on matching tasks ✨'
                : 'Ingredients with a prep method on recipe cards populate stations. Manual items are kept. Re-sync preserves checkmarks on matching tasks.'; ?></p>
            <div class="sync-actions">
                <button type="button" class="btn btn-primary" id="sync-recipes-btn" data-perm="boh.prep.sync_recipes"><?php echo $is_sweet ? '↻ Sync from recipes' : '↻ Sync from recipes'; ?></button>
                <a href="/BOH/recipe-cards" class="btn btn-secondary"><?php echo $is_sweet ? 'Open recipes' : 'Open recipes'; ?></a>
            </div>
            <div class="sync-meta" id="sync-meta"><?php echo $is_sweet ? 'Not synced yet' : 'Not synced yet'; ?></div>
        </div>

        <div class="sync-pill no-print syncing" id="sync-pill" title="<?php echo $is_sweet ? 'Shared with your restaurant group' : 'Shared with your restaurant group'; ?>">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div class="progress-card no-print">
            <div class="progress-top">
                <span><?php echo $is_sweet ? 'Today\'s prep progress' : 'Today\'s prep progress'; ?></span>
                <span id="progress-count">0 / 0</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
        </div>

        <div class="toolbar no-print">
            <button type="button" class="btn btn-primary" id="add-station-btn" data-perm="boh.prep.add_station"><?php echo $is_sweet ? '+ Station' : '+ Station'; ?></button>
            <button type="button" class="btn btn-secondary" id="reset-checks-btn" data-perm="boh.prep.uncheck"><?php echo $is_sweet ? 'Uncheck all' : 'Uncheck all'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-prep-btn" data-perm="boh.prep.print_all"><?php echo $is_sweet ? '🖨️ Print all stations' : 'Print all stations'; ?></button>
        </div>
        <div class="assign-filters no-print" id="assign-filters">
            <button type="button" class="filter-chip active" data-assign-filter="all"><?php echo $is_sweet ? 'All tasks' : 'All'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="mine"><?php echo $is_sweet ? 'Mine' : 'Mine'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="unassigned"><?php echo $is_sweet ? 'Unassigned' : 'Unassigned'; ?></button>
        </div>
        <div class="print-only" id="print-prep-header" style="margin-bottom:8px;font-weight:600;"></div>

        <div id="stations"></div>

        <div class="actions-bar no-print">
            <button type="button" class="btn btn-secondary" id="reset-shells-btn" data-perm="boh.prep.reset"><?php echo $is_sweet ? 'Reset shells' : 'Reset shells'; ?></button>
            <a href="/BOH" class="btn btn-primary"><?php echo pbj_back_to_hub('boh'); ?></a>
        </div>
    </div>

    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2 id="modal-title">Add</h2>
            <form id="modal-form">
                <input type="hidden" id="m-station">
                <input type="hidden" id="m-item">
                <input type="hidden" id="m-mode">
                <div class="field" id="f-title"><label for="m-title"><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="m-title" type="text"></div>
                <div class="field" id="f-hint"><label for="m-hint"><?php echo $is_sweet ? 'Hint / par note' : 'Hint / par note'; ?></label><input id="m-hint" type="text"></div>
                <div class="field" id="f-assign"><label for="m-assign"><?php echo $is_sweet ? 'Assign to (optional)' : 'Assign to (optional)'; ?></label>
                    <input id="m-assign" list="prep-assign-list" type="text" maxlength="80" placeholder="<?php echo $is_sweet ? 'Name or role…' : 'Name or role…'; ?>">
                    <datalist id="prep-assign-list"></datalist>
                </div>
                <div class="field" id="f-icon" style="display:none"><label for="m-icon"><?php echo $is_sweet ? 'Emoji' : 'Emoji'; ?></label><input id="m-icon" type="text" maxlength="4"></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="m-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>

    <?php include 'bottom-nav.php'; ?>

    <script src="shared-state.js?v=4"></script>
    <script src="checklist-photos.js?v=1"></script>
    <script src="checklist-complete.js?v=1"></script>
    <script src="checklist-assign.js?v=2"></script>
    <script>
    (function () {
        const KEY = 'pbj_heat_prep_v1';
        const RECIPE_KEY = 'pbj_heat_recipes_v1';
        const SHARED_KEY = 'heat_prep_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        const userName = <?php echo json_encode($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Team'); ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function canPhotos() { return canP('boh.prep.attach_photos'); }
            function prepIsComplete() {
                var total = 0, done = 0;
                (state.stations || []).forEach(function (s) {
                    (s.items || []).forEach(function (i) {
                        total++;
                        if (i.done) done++;
                    });
                });
                return total > 0 && done === total;
            }
            function notifyIfPrepComplete() {
                if (!prepIsComplete() || !window.PbjListComplete) return;
                PbjListComplete.maybeNotify({
                    sourceKey: SHARED_KEY,
                    listId: 'all',
                    listTitle: isSweet ? 'All stations' : 'All stations',
                    pageTitle: isSweet ? 'Prep Lists & Line Checks' : 'Prep Lists & Line Checks',
                    href: '/BOH/prep',
                    completedBy: userName,
                    isSweet: isSweet
                });
            }
            function clearPrepCompleteNotify() {
                if (window.PbjListComplete) PbjListComplete.clearDedupe(SHARED_KEY, 'all');
            }
            function applyPrepPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof renderStations === 'function') renderStations(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        const defaults = <?php echo json_encode($default_stations, JSON_UNESCAPED_UNICODE); ?>;

        // Prep methods that don't create a daily prep task
        const SKIP_PREP = { '': true, 'as is': true, 'raw': true, 'whole': true };

        // Map prep method → station id
        const PREP_STATION = {
            diced: 'cold', sliced: 'cold', minced: 'cold', julienne: 'cold',
            chopped: 'cold', shredded: 'cold', peeled: 'cold', trimmed: 'cold',
            cooked: 'hot', portioned: 'line', other: 'line'
        };

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function norm(s) { return String(s || '').trim().toLowerCase().replace(/\s+/g, ' '); }
        function titleCaseName(s) {
            return String(s || '').trim().replace(/\s+/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        }
        function shells() {
            return {
                stations: defaults.map(function (s) {
                    return { id: s.id, title: s.title, icon: s.icon, hint: s.hint, open: false, items: [] };
                }),
                lastSyncAt: null,
                lastSyncCount: 0
            };
        }
        function load() {
            try {
                const r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || !r.stations || !r.stations.length) return shells();
                // migrate legacy items
                r.stations.forEach(function (s) {
                    (s.items || []).forEach(function (i) {
                        if (!i.source) i.source = 'manual';
                        if (i.assignedTo == null) i.assignedTo = '';
                        if (i.doneUpdatedAt == null) i.doneUpdatedAt = 0;
                        if (i.photoAt == null) i.photoAt = 0;
                    });
                });
                if (r.lastSyncAt === undefined) r.lastSyncAt = null;
                if (r.lastSyncCount === undefined) r.lastSyncCount = 0;
                return r;
            } catch (e) { return shells(); }
        }
        function touchStructure() {
            state.structureAt = Date.now();
        }

        function save(toast, opts) {
            opts = opts || {};
            localStorage.setItem(KEY, JSON.stringify(state));
            if (toast) {
                var t = document.getElementById('toast');
                t.textContent = typeof toast === 'string' ? toast : (isSweet ? 'Saved 💾' : 'Saved');
                t.classList.add('show');
                setTimeout(function () { t.classList.remove('show'); }, 1400);
            }
            // Push to restaurant group (debounced) unless this save came from remote
            if (!opts.skipRemote && shared) {
                shared.queuePush(state);
            }
        }
        function find(id) { return state.stations.find(function (s) { return s.id === id; }); }

        var assignFilter = 'all'; // all | mine | unassigned
        function loadTeamNames() {
            var names = [];
            var keys = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
            for (var i = 0; i < keys.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(keys[i]) || 'null');
                    if (r && Array.isArray(r.people)) {
                        r.people.forEach(function (p) {
                            if (p && p.status !== 'inactive' && (p.name || '').trim()) {
                                names.push(String(p.name).trim());
                            }
                        });
                        if (names.length) break;
                    }
                } catch (e) {}
            }
            if (userName && names.indexOf(userName) === -1) names.unshift(userName);
            return names.sort(function (a, b) { return a.localeCompare(b); });
        }
        function fillAssignDatalist() {
            var dl = document.getElementById('prep-assign-list');
            if (!dl) return;
            dl.innerHTML = loadTeamNames().map(function (n) {
                return '<option value="' + esc(n) + '"></option>';
            }).join('');
        }
        function isMine(item) {
            if (!item || !item.assignedTo) return false;
            return norm(item.assignedTo) === norm(userName);
        }
        function itemMatchesAssignFilter(item) {
            if (assignFilter === 'mine') return isMine(item);
            if (assignFilter === 'unassigned') return !String(item.assignedTo || '').trim();
            return true;
        }
        function paintAssignFilters() {
            document.querySelectorAll('#assign-filters [data-assign-filter]').forEach(function (chip) {
                chip.classList.toggle('active', chip.getAttribute('data-assign-filter') === assignFilter);
            });
        }

        function loadRecipes() {
            try {
                var r = JSON.parse(localStorage.getItem(RECIPE_KEY) || 'null');
                if (!r || !r.categories) return [];
                var out = [];
                r.categories.forEach(function (c) {
                    (c.recipes || []).forEach(function (rec) {
                        out.push(rec);
                    });
                });
                return out;
            } catch (e) { return []; }
        }

        function stationForPrep(method) {
            var m = norm(method);
            return PREP_STATION[m] || 'cold';
        }

        function recipeKeyFor(name, method) {
            return norm(name) + '|' + norm(method);
        }

        /** Collect unique prep tasks from all recipe ingredients */
        function collectFromRecipes() {
            var recipes = loadRecipes();
            var map = {}; // recipeKey → task
            var recipeCount = 0;
            recipes.forEach(function (rec) {
                var ings = rec.ingredients || [];
                var used = false;
                ings.forEach(function (ing) {
                    var name = (ing.name || '').trim();
                    var method = (ing.prepMethod || '').trim();
                    if (!name || SKIP_PREP[norm(method)]) return;
                    used = true;
                    var key = recipeKeyFor(name, method);
                    if (!map[key]) {
                        map[key] = {
                            recipeKey: key,
                            name: titleCaseName(name),
                            method: method,
                            stationId: stationForPrep(method),
                            recipes: [],
                            qtyNotes: []
                        };
                    }
                    // Recipes store title (not name) — fall back for older data shapes
                    var rName = (rec.title || rec.name || '').trim() || (isSweet ? 'Untitled recipe' : 'Untitled recipe');
                    if (map[key].recipes.indexOf(rName) === -1) map[key].recipes.push(rName);
                    var qty = ing.qty !== '' && ing.qty != null ? String(ing.qty) : '';
                    var unit = (ing.unit || '').trim();
                    if (qty || unit) {
                        var note = (qty + ' ' + unit).trim() + (rName ? ' · ' + rName : '');
                        if (map[key].qtyNotes.indexOf(note) === -1) map[key].qtyNotes.push(note);
                    }
                });
                if (used) recipeCount++;
            });
            return { tasks: Object.keys(map).map(function (k) { return map[k]; }), recipeCount: recipeCount, totalRecipes: recipes.length };
        }

        function buildPar(task) {
            var parts = [];
            if (task.recipes.length) {
                var list = task.recipes.slice(0, 4).join(', ');
                if (task.recipes.length > 4) list += ' +' + (task.recipes.length - 4) + ' more';
                parts.push((isSweet ? 'Recipes: ' : 'Recipes: ') + list);
            }
            if (task.qtyNotes.length) {
                parts.push(task.qtyNotes.slice(0, 3).join('; '));
            }
            return parts.join(' · ');
        }

        function ensureStation(stationId) {
            var s = find(stationId);
            if (s) return s;
            var def = defaults.find(function (d) { return d.id === stationId; });
            s = {
                id: stationId,
                title: def ? def.title : titleCaseName(stationId),
                icon: def ? def.icon : '🔪',
                hint: def ? def.hint : '',
                open: true,
                items: []
            };
            state.stations.push(s);
            return s;
        }

        /**
         * Merge recipe tasks into prep stations.
         * - Keeps manual items untouched
         * - Updates recipe-sourced items by recipeKey; preserves done
         * - Removes stale recipe-sourced items no longer in recipes
         * - Moves item if station assignment changed
         */
        function syncFromRecipes() {
            var collected = collectFromRecipes();
            var tasks = collected.tasks;
            var taskByKey = {};
            tasks.forEach(function (t) { taskByKey[t.recipeKey] = t; });

            // Index existing recipe items: recipeKey → { item, stationId }
            var existing = {};
            state.stations.forEach(function (s) {
                (s.items || []).forEach(function (i) {
                    if (i.source === 'recipe' && i.recipeKey) {
                        existing[i.recipeKey] = { item: i, stationId: s.id };
                    }
                });
            });

            // Remove stale recipe items
            state.stations.forEach(function (s) {
                s.items = (s.items || []).filter(function (i) {
                    if (i.source !== 'recipe') return true;
                    return !!(i.recipeKey && taskByKey[i.recipeKey]);
                });
            });

            var added = 0, updated = 0;
            tasks.forEach(function (task) {
                var label = task.name + ' — ' + task.method;
                var par = buildPar(task);
                var prev = existing[task.recipeKey];
                var station = ensureStation(task.stationId);

                if (prev) {
                    // Update fields, keep done + id + assignee
                    var it = prev.item;
                    it.label = label;
                    it.par = par;
                    it.source = 'recipe';
                    it.recipeKey = task.recipeKey;
                    it.method = task.method;
                    if (it.assignedTo == null) it.assignedTo = '';
                    // If station changed, move
                    if (prev.stationId !== task.stationId) {
                        var oldSt = find(prev.stationId);
                        if (oldSt) oldSt.items = (oldSt.items || []).filter(function (x) { return x.id !== it.id; });
                        if (!(station.items || []).some(function (x) { return x.id === it.id; })) {
                            station.items = station.items || [];
                            station.items.push(it);
                        }
                    } else {
                        // ensure still present after filter re-run — already kept by filter
                        if (!(station.items || []).some(function (x) { return x.recipeKey === task.recipeKey; })) {
                            station.items = station.items || [];
                            station.items.push(it);
                        }
                    }
                    updated++;
                } else {
                    station.items = station.items || [];
                    station.items.push({
                        id: uid(),
                        label: label,
                        par: par,
                        done: false,
                        doneUpdatedAt: 0,
                        assignedTo: '',
                        source: 'recipe',
                        recipeKey: task.recipeKey,
                        method: task.method
                    });
                    station.open = true;
                    added++;
                }
            });

            // Sort recipe items within each station alphabetically; manual after
            state.stations.forEach(function (s) {
                s.items = (s.items || []).slice().sort(function (a, b) {
                    var as = a.source === 'recipe' ? 0 : 1;
                    var bs = b.source === 'recipe' ? 0 : 1;
                    if (as !== bs) return as - bs;
                    return (a.label || '').localeCompare(b.label || '');
                });
            });

            state.lastSyncAt = new Date().toISOString();
            state.lastSyncCount = tasks.length;
            touchStructure();
            save(isSweet
                ? ('Synced ' + tasks.length + ' prep tasks ✨')
                : ('Synced ' + tasks.length + ' prep tasks'));
            render();
            return { added: added, updated: updated, total: tasks.length, recipeCount: collected.recipeCount, totalRecipes: collected.totalRecipes };
        }

        function formatSyncMeta() {
            var el = document.getElementById('sync-meta');
            if (!state.lastSyncAt) {
                el.textContent = isSweet ? 'Not synced yet — tap Sync from recipes' : 'Not synced yet — tap Sync from recipes';
                return;
            }
            var d = new Date(state.lastSyncAt);
            var when = isNaN(d.getTime()) ? state.lastSyncAt : d.toLocaleString();
            el.textContent = (isSweet ? 'Last sync: ' : 'Last sync: ') + when +
                ' · ' + (state.lastSyncCount || 0) + (isSweet ? ' recipe prep tasks' : ' recipe prep tasks');
        }

        let state = load();
        if (!state.structureAt) state.structureAt = Date.now();
        var shared = null;
        var applyingRemote = false;

        function setSyncPill(info) {
            var pill = document.getElementById('sync-pill');
            var text = document.getElementById('sync-pill-text');
            if (!pill || !text) return;
            pill.classList.remove('offline', 'syncing');
            if (info.kind === 'offline') pill.classList.add('offline');
            if (info.kind === 'syncing') pill.classList.add('syncing');
            text.textContent = info.text || '';
        }

        function applyRemoteState(payload) {
            if (!payload || !payload.stations) return;
            applyingRemote = true;
            state = payload;
            if (!state.structureAt) state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
            applyingRemote = false;
        }

        /** Fix recipe titles/pars for already-synced items (e.g. old "Untitled" bug used rec.name) */
        function refreshRecipeLabels() {
            var collected = collectFromRecipes();
            if (!collected.tasks.length) return false;
            var byKey = {};
            collected.tasks.forEach(function (t) { byKey[t.recipeKey] = t; });
            var changed = false;
            state.stations.forEach(function (s) {
                (s.items || []).forEach(function (i) {
                    if (i.source !== 'recipe' || !i.recipeKey) return;
                    var task = byKey[i.recipeKey];
                    if (!task) return;
                    var label = task.name + ' — ' + task.method;
                    var par = buildPar(task);
                    if (i.label !== label || i.par !== par) {
                        i.label = label;
                        i.par = par;
                        changed = true;
                    }
                });
            });
            if (changed) save(false, { skipRemote: true });
            return changed;
        }

        function progress() {
            var total = 0, done = 0;
            state.stations.forEach(function (s) {
                (s.items || []).forEach(function (i) {
                    total++;
                    if (i.done) done++;
                });
            });
            document.getElementById('progress-count').textContent = done + ' / ' + total;
            document.getElementById('progress-fill').style.width = total ? ((done / total) * 100) + '%' : '0%';
        }

        function render() {
            progress();
            formatSyncMeta();
            paintAssignFilters();
            var root = document.getElementById('stations');
            root.innerHTML = state.stations.map(function (s) {
                var items = s.items || [];
                var done = items.filter(function (i) { return i.done; }).length;
                var visible = items.filter(itemMatchesAssignFilter);
                var body;
                if (!items.length) {
                    body = '<div class="empty-slot">' + (isSweet
                        ? 'No prep items yet — sync from recipes or add your own ✨'
                        : 'No prep items yet. Sync from recipes or add your own.') + '</div>';
                } else if (!visible.length) {
                    body = '<div class="empty-slot">' + (isSweet
                        ? 'No tasks match this filter on this station'
                        : 'No tasks match this filter on this station') + '</div>';
                } else {
                    var photosOk = canPhotos();
                    var Photos = window.PbjTaskPhotos;
                    body = visible.map(function (item) {
                        var fromRecipe = item.source === 'recipe';
                        var photoHtml = '';
                        if (Photos) {
                            photoHtml = Photos.renderPhotoUi(item, { canAttach: photosOk, isSweet: isSweet });
                            // Prep uses station + item ids (not generic data-id alone)
                            photoHtml = photoHtml
                                .replace(/data-act="photo"/g, 'data-act="photo" data-station="' + esc(s.id) + '" data-item="' + esc(item.id) + '"')
                                .replace(/data-act="photo-del"/g, 'data-act="photo-del" data-station="' + esc(s.id) + '" data-item="' + esc(item.id) + '"');
                        }
                        var assignHtml = item.assignedTo
                            ? ('<span class="assign-badge' + (isMine(item) ? ' mine' : '') + '">' +
                                (isSweet ? '👤 ' : '') + esc(item.assignedTo) +
                                (isMine(item) ? (isSweet ? ' · you' : ' · you') : '') +
                                '</span>')
                            : '';
                        return '<div class="check-item' + (item.done ? ' done' : '') + '">' +
                            '<button type="button" class="check-main" data-act="toggle-item" data-need-perm="boh.prep.check_off" data-station="' + esc(s.id) + '" data-item="' + esc(item.id) + '" aria-pressed="' + (item.done ? 'true' : 'false') + '">' +
                            '<span class="checkbox" aria-hidden="true"></span>' +
                            '<span class="check-body">' +
                                '<div class="check-label">' + esc(item.label) + '</div>' +
                                (item.par ? '<div class="check-par">' + esc(item.par) + '</div>' : '') +
                                assignHtml +
                                (fromRecipe ? '<span class="source-tag">' + (isSweet ? 'From recipes' : 'From recipes') + '</span>' : '') +
                            '</span></button>' +
                            photoHtml +
                            '<div class="item-actions">' +
                                '<button type="button" class="btn btn-small btn-ghost" data-act="edit-item" data-need-perm="boh.prep.add_item" data-station="' + esc(s.id) + '" data-item="' + esc(item.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                                '<button type="button" class="btn btn-small btn-danger" data-act="del-item" data-need-perm="boh.prep.add_item" data-station="' + esc(s.id) + '" data-item="' + esc(item.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                            '</div></div>';
                    }).join('');
                }
                return '<div class="station' + (s.open ? ' open' : '') + '" data-station-id="' + esc(s.id) + '">' +
                    '<button type="button" class="station-header" data-act="toggle" data-station="' + esc(s.id) + '">' +
                        '<span class="st-icon">' + esc(s.icon || '🔪') + '</span>' +
                        '<span class="st-titles"><h2 class="st-title">' + esc(s.title) + '</h2>' +
                        (s.hint ? '<p class="st-hint">' + esc(s.hint) + '</p>' : '') + '</span>' +
                        '<span class="st-count">' + done + '/' + items.length + '</span>' +
                        '<span class="chevron">›</span></button>' +
                    '<div class="station-body">' + body +
                        '<div class="add-row">' +
                            '<button type="button" class="btn btn-small btn-primary" data-act="add-item" data-need-perm="boh.prep.add_item" data-station="' + esc(s.id) + '">' + (isSweet ? '+ Prep item' : '+ Prep item') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="print-station" data-need-perm="boh.prep.print_one" data-station="' + esc(s.id) + '">' + (isSweet ? '🖨️ Print' : 'Print') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="edit-station" data-need-perm="boh.prep.edit_station" data-station="' + esc(s.id) + '">' + (isSweet ? 'Edit station' : 'Edit station') + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="del-station" data-need-perm="boh.prep.delete_station" data-station="' + esc(s.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                        '</div></div></div>';
            }).join('');
        }

        function stampPrepHeader(label) {
            var d = new Date();
            document.getElementById('print-prep-header').textContent =
                label + ' · ' + d.toLocaleDateString() + ' · ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function clearPrintStationMode() {
            document.body.classList.remove('print-one-station');
            document.querySelectorAll('.station.print-target').forEach(function (el) {
                el.classList.remove('print-target');
            });
        }

        function printOneStation(stationId) {
            var s = find(stationId);
            if (!s) return;
            // Expand so items show on paper
            s.open = true;
            render();
            clearPrintStationMode();
            var el = document.querySelector('.station[data-station-id="' + stationId + '"]');
            if (!el) return;
            el.classList.add('print-target');
            document.body.classList.add('print-one-station');
            stampPrepHeader((s.icon ? s.icon + ' ' : '') + s.title + (isSweet ? ' prep list' : ' prep list'));
            setTimeout(function () {
                window.print();
                clearPrintStationMode();
            }, 80);
        }

        function printAllStations() {
            clearPrintStationMode();
            state.stations.forEach(function (s) { s.open = true; });
            render();
            stampPrepHeader(isSweet ? 'Prep Lists & Line Checks (all stations)' : 'Prep Lists & Line Checks (all stations)');
            setTimeout(function () { window.print(); }, 80);
        }

        var modal = document.getElementById('modal');
        function openModal(mode, stationId, itemId) {
            document.getElementById('m-mode').value = mode;
            document.getElementById('m-station').value = stationId || '';
            document.getElementById('m-item').value = itemId || '';
            var isStation = mode === 'station' || mode === 'edit-station';
            document.getElementById('f-icon').style.display = isStation ? 'block' : 'none';
            var fAssign = document.getElementById('f-assign');
            if (fAssign) fAssign.style.display = isStation ? 'none' : 'block';
            document.getElementById('f-hint').querySelector('label').textContent = isStation
                ? (isSweet ? 'Description' : 'Description')
                : (isSweet ? 'Par / notes' : 'Par / notes');
            fillAssignDatalist();
            if (mode === 'station') {
                document.getElementById('modal-title').textContent = isSweet ? 'New station' : 'New station';
                document.getElementById('m-title').value = '';
                document.getElementById('m-hint').value = '';
                document.getElementById('m-icon').value = '🔪';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = '';
            } else if (mode === 'edit-station') {
                var s = find(stationId);
                document.getElementById('modal-title').textContent = isSweet ? 'Edit station' : 'Edit station';
                document.getElementById('m-title').value = s ? s.title : '';
                document.getElementById('m-hint').value = s ? (s.hint || '') : '';
                document.getElementById('m-icon').value = s ? (s.icon || '🔪') : '🔪';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = '';
            } else if (mode === 'edit-item') {
                var st = find(stationId);
                var it = st && st.items.find(function (x) { return x.id === itemId; });
                document.getElementById('modal-title').textContent = isSweet ? 'Edit prep item' : 'Edit prep item';
                document.getElementById('m-title').value = it ? it.label : '';
                document.getElementById('m-hint').value = it ? (it.par || '') : '';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = it ? (it.assignedTo || '') : '';
            } else {
                document.getElementById('modal-title').textContent = isSweet ? 'Add prep item' : 'Add prep item';
                document.getElementById('m-title').value = '';
                document.getElementById('m-hint').value = '';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = '';
            }
            modal.classList.add('show');
            setTimeout(function () { document.getElementById('m-title').focus(); }, 40);
        }
        function closeModal() { modal.classList.remove('show'); }

        document.getElementById('modal-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var mode = document.getElementById('m-mode').value;
            var sid = document.getElementById('m-station').value;
            var iid = document.getElementById('m-item').value;
            var title = document.getElementById('m-title').value.trim();
            if (!title) return;
            if (mode === 'station') {
                state.stations.push({ id: uid(), title: title, hint: document.getElementById('m-hint').value.trim(), icon: document.getElementById('m-icon').value.trim() || '🔪', open: true, items: [] });
                touchStructure();
            } else if (mode === 'edit-station') {
                var s = find(sid); if (!s) return;
                s.title = title; s.hint = document.getElementById('m-hint').value.trim(); s.icon = document.getElementById('m-icon').value.trim() || '🔪';
                touchStructure();
            } else if (mode === 'edit-item') {
                var st = find(sid); if (!st) return;
                var it = st.items.find(function (x) { return x.id === iid; }); if (!it) return;
                it.label = title;
                it.par = document.getElementById('m-hint').value.trim();
                it.assignedTo = (document.getElementById('m-assign') && document.getElementById('m-assign').value.trim()) || '';
                // Edited recipe item becomes manual so re-sync won't overwrite custom label
                if (it.source === 'recipe') {
                    it.source = 'manual';
                    delete it.recipeKey;
                }
                touchStructure();
            } else {
                var st2 = find(sid); if (!st2) return;
                st2.items.push({
                    id: uid(),
                    label: title,
                    par: document.getElementById('m-hint').value.trim(),
                    assignedTo: (document.getElementById('m-assign') && document.getElementById('m-assign').value.trim()) || '',
                    done: false,
                    doneUpdatedAt: 0,
                    source: 'manual'
                });
                st2.open = true;
                touchStructure();
            }
            save(true); closeModal(); render();
        });
        document.getElementById('m-cancel').addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

        var assignFiltersEl = document.getElementById('assign-filters');
        if (assignFiltersEl) {
            assignFiltersEl.addEventListener('click', function (e) {
                var chip = e.target.closest('[data-assign-filter]');
                if (!chip) return;
                assignFilter = chip.getAttribute('data-assign-filter') || 'all';
                render();
            });
        }

        document.getElementById('stations').addEventListener('click', function (e) {
            if (e.target.closest('a.task-photo-thumb')) return;
                var need = e.target.closest('[data-need-perm]');
                if (need) {
                    var pk = need.getAttribute('data-need-perm') || (need.closest('[data-need-perm]') && need.closest('[data-need-perm]').getAttribute('data-need-perm'));
                }
                var btnNeed = e.target.closest('[data-act]');
                if (btnNeed && btnNeed.getAttribute('data-need-perm') && !canP(btnNeed.getAttribute('data-need-perm'))) return;

            // Prefer real buttons first so Edit/Remove never get swallowed by the check row
            var btn = e.target.closest('button[data-act]');
            if (!btn || !document.getElementById('stations').contains(btn)) return;
            e.preventDefault();
            e.stopPropagation();
            var act = btn.dataset.act, sid = btn.dataset.station, iid = btn.dataset.item, s = find(sid);
            if (act === 'toggle') { if (s) { s.open = !s.open; save(false, { skipRemote: true }); render(); } return; }
            if (act === 'toggle-item') {
                if (!s) return;
                var it = s.items.find(function (x) { return x.id === iid; });
                if (it) {
                    var wasDone = !!it.done;
                    it.done = !it.done;
                    it.doneUpdatedAt = Date.now();
                    save(false);
                    render();
                    if (it.done && !wasDone) notifyIfPrepComplete();
                    else if (!it.done && wasDone) clearPrepCompleteNotify();
                }
                return;
            }
            if (act === 'photo') {
                if (!canPhotos() || !window.PbjTaskPhotos || !s) return;
                var photoItem = s.items.find(function (x) { return x.id === iid; });
                if (!photoItem) return;
                PbjTaskPhotos.openPicker(function (file) {
                    btn.classList.add('task-photo-busy');
                    PbjTaskPhotos.captureAndAttach(file).then(function (res) {
                        if (photoItem.photoUrl && photoItem.photoUrl !== res.photoUrl) {
                            PbjTaskPhotos.clearItemPhoto({ photoUrl: photoItem.photoUrl });
                        }
                        if (res.photoUrl) {
                            photoItem.photoUrl = res.photoUrl;
                            delete photoItem.photoDataUrl;
                            delete photoItem.photo;
                        } else if (res.photoDataUrl) {
                            photoItem.photoDataUrl = res.photoDataUrl;
                            delete photoItem.photoUrl;
                        }
                        photoItem.photoAt = res.photoAt || Date.now();
                        if (!photoItem.done) {
                            photoItem.done = true;
                            photoItem.doneUpdatedAt = Date.now();
                        }
                        save(isSweet ? 'Photo saved 📷' : 'Photo saved');
                        render();
                        notifyIfPrepComplete();
                    }).catch(function () {
                        save(isSweet ? 'Couldn’t save photo' : 'Could not save photo', { skipRemote: true });
                    }).then(function () { btn.classList.remove('task-photo-busy'); });
                });
                return;
            }
            if (act === 'photo-del') {
                if (!canPhotos() || !window.PbjTaskPhotos || !s) return;
                var delItem = s.items.find(function (x) { return x.id === iid; });
                if (!delItem) return;
                if (!confirm(isSweet ? 'Remove this photo?' : 'Remove this photo?')) return;
                PbjTaskPhotos.clearItemPhoto(delItem).then(function () {
                    save(true);
                    render();
                });
                return;
            }
            if (act === 'add-item') { openModal('item', sid); return; }
            if (act === 'edit-item') { openModal('edit-item', sid, iid); return; }
            if (act === 'print-station') { printOneStation(sid); return; }
            if (act === 'del-item') {
                if (!s || !confirm(isSweet ? 'Remove this prep item?' : 'Remove this item?')) return;
                var removing = s.items.find(function (x) { return x.id === iid; });
                if (removing && window.PbjTaskPhotos) PbjTaskPhotos.clearItemPhoto(removing);
                s.items = s.items.filter(function (x) { return x.id !== iid; });
                touchStructure();
                clearPrepCompleteNotify();
                save(true); render(); return;
            }
            if (act === 'edit-station') { openModal('edit-station', sid); return; }
            if (act === 'del-station') {
                if (!confirm(isSweet ? 'Remove this station?' : 'Remove this station?')) return;
                state.stations = state.stations.filter(function (x) { return x.id !== sid; });
                touchStructure();
                clearPrepCompleteNotify();
                save(true); render();
            }
        });

        document.getElementById('add-station-btn').addEventListener('click', function () {
                if (!canP('boh.prep.add_station')) return; openModal('station'); });
        document.getElementById('reset-checks-btn').addEventListener('click', function () {
                if (!canP('boh.prep.uncheck')) return;
            var now = Date.now();
            state.stations.forEach(function (s) {
                (s.items || []).forEach(function (i) {
                    i.done = false;
                    i.doneUpdatedAt = now;
                });
            });
            clearPrepCompleteNotify();
            save(true); render();
        });
        document.getElementById('reset-shells-btn').addEventListener('click', function () {
                if (!canP('boh.prep.reset')) return;
            if (!confirm(isSweet ? 'Reset to empty station shells? This clears prep items for the whole kitchen today.' : 'Reset to empty station shells for the kitchen today?')) return;
            state = shells();
            touchStructure();
            save(true); render();
        });

        document.getElementById('sync-recipes-btn').addEventListener('click', function () {
                if (!canP('boh.prep.sync_recipes')) return;
            var preview = collectFromRecipes();
            if (!preview.totalRecipes) {
                alert(isSweet
                    ? 'No recipes found yet. Add recipes with prep methods on Standardized Recipes, then sync here ✨'
                    : 'No recipes found. Add recipes with prep methods on Standardized Recipes, then sync.');
                return;
            }
            if (!preview.tasks.length) {
                alert(isSweet
                    ? 'Recipes are saved, but no ingredients have a prep method yet (diced, sliced, cooked…). Set prep methods on recipe cards, then sync ✨'
                    : 'Recipes found, but no ingredients have a prep method set. Add prep methods on recipe cards, then sync.');
                return;
            }
            var result = syncFromRecipes();
            if (result.total === 0) return;
            // open stations that received items
            state.stations.forEach(function (s) {
                if ((s.items || []).some(function (i) { return i.source === 'recipe'; })) s.open = true;
            });
            save(false); render();
        });

        document.getElementById('print-prep-btn').addEventListener('click', printAllStations);

        // After print dialog closes (some browsers), ensure filter is cleared
        window.addEventListener('afterprint', clearPrintStationMode);

        refreshRecipeLabels();
        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyPrepPerms);
            document.addEventListener('pbj-perms-ready', applyPrepPerms);

        // Restaurant-group live sync (check-offs + list structure for today)
        if (window.PbjSharedState) {
            shared = new PbjSharedState({
                key: SHARED_KEY,
                pollMs: 4000,
                onStatus: setSyncPill,
                onRemote: function (payload) {
                    if (applyingRemote) return;
                    applyRemoteState(payload);
                }
            });
            shared.bootstrap(
                function () { return state; },
                function (payload) { applyRemoteState(payload); }
            ).then(function () {
                shared.startPolling();
            });
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only' : 'Local only' });
        }
    })();
    </script>
</body>
</html>
