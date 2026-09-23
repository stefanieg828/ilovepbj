<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

// Seeded schedule buckets with real starter tasks (still fully editable)
$default_lists = [
    [
        'id' => 'daily',
        'title' => 'Daily Clean',
        'icon' => '✨',
        'hint' => $is_sweet ? 'Every shift / end of night' : 'Every shift / end of night',
        'items' => $is_sweet
            ? [
                ['label' => 'Boards, knives & tools washed & put away', 'note' => 'End of shift'],
                ['label' => 'Line surfaces wiped & sanitized', 'note' => ''],
                ['label' => 'Prep tables scrubbed & sanitized', 'note' => ''],
                ['label' => 'Sanitizer buckets dumped & sinks cleaned', 'note' => ''],
                ['label' => 'Floors swept & mopped (line + prep)', 'note' => ''],
                ['label' => 'Mats cleaned / hung to dry', 'note' => ''],
                ['label' => 'Trash & recycling out; liners replaced', 'note' => ''],
                ['label' => 'Hand sinks wiped & restocked', 'note' => 'Soap + towels'],
                ['label' => 'Dish pit wiped; machine emptied of racks', 'note' => ''],
                ['label' => 'Walk-in floor swept; spills cleaned', 'note' => ''],
            ]
            : [
                ['label' => 'Wash and store boards, knives, and tools', 'note' => 'End of shift'],
                ['label' => 'Wipe and sanitize line surfaces', 'note' => ''],
                ['label' => 'Scrub and sanitize prep tables', 'note' => ''],
                ['label' => 'Dump sanitizer; clean sinks', 'note' => ''],
                ['label' => 'Sweep and mop floors (line + prep)', 'note' => ''],
                ['label' => 'Clean / hang mats', 'note' => ''],
                ['label' => 'Take out trash and recycling; replace liners', 'note' => ''],
                ['label' => 'Wipe and restock hand sinks', 'note' => 'Soap + towels'],
                ['label' => 'Wipe dish pit; clear machine racks', 'note' => ''],
                ['label' => 'Sweep walk-in floor; clean spills', 'note' => ''],
            ],
    ],
    [
        'id' => 'weekly',
        'title' => 'Weekly Clean',
        'icon' => '📅',
        'hint' => $is_sweet ? 'Assign by day of week' : 'Assign by day of week',
        'items' => $is_sweet
            ? [
                ['label' => 'Detail clean fryers / filter housing', 'note' => 'Pick a fixed day'],
                ['label' => 'Deep clean flat top / grill crevices', 'note' => ''],
                ['label' => 'Pull & clean under equipment', 'note' => ''],
                ['label' => 'Degrease equipment exteriors', 'note' => ''],
                ['label' => 'Clean oven interiors as assigned', 'note' => ''],
                ['label' => 'Wipe shelving in dry storage', 'note' => ''],
                ['label' => 'Clean walk-in shelves & door gaskets', 'note' => ''],
                ['label' => 'Flush floor drains with cleaner', 'note' => ''],
                ['label' => 'Sanitize ice machine scoop holder', 'note' => ''],
                ['label' => 'Organize chemical storage', 'note' => 'Labels facing out'],
            ]
            : [
                ['label' => 'Detail clean fryers / filter housing', 'note' => 'Pick a fixed day'],
                ['label' => 'Deep clean flat top / grill crevices', 'note' => ''],
                ['label' => 'Pull and clean under equipment', 'note' => ''],
                ['label' => 'Degrease equipment exteriors', 'note' => ''],
                ['label' => 'Clean oven interiors as assigned', 'note' => ''],
                ['label' => 'Wipe dry storage shelving', 'note' => ''],
                ['label' => 'Clean walk-in shelves and door gaskets', 'note' => ''],
                ['label' => 'Flush floor drains with cleaner', 'note' => ''],
                ['label' => 'Sanitize ice machine scoop holder', 'note' => ''],
                ['label' => 'Organize chemical storage', 'note' => 'Labels facing out'],
            ],
    ],
    [
        'id' => 'monthly',
        'title' => 'Monthly / Deep',
        'icon' => '🧽',
        'hint' => $is_sweet ? 'Hoods, drains, detail work' : 'Hoods, drains, detail work',
        'items' => $is_sweet
            ? [
                ['label' => 'Schedule / confirm hood cleaning', 'note' => 'Vendor or in-house'],
                ['label' => 'Deep clean walk-in walls & floors', 'note' => ''],
                ['label' => 'Deep clean freezers (defrost if needed)', 'note' => ''],
                ['label' => 'Detail clean dish machine (delime if due)', 'note' => ''],
                ['label' => 'Clean light fixtures & high dust', 'note' => ''],
                ['label' => 'Pull coolers & clean coils if trained', 'note' => 'Or call tech'],
                ['label' => 'Inventory & restock cleaning chemicals', 'note' => ''],
                ['label' => 'Review pest control log / follow-ups', 'note' => ''],
            ]
            : [
                ['label' => 'Schedule / confirm hood cleaning', 'note' => 'Vendor or in-house'],
                ['label' => 'Deep clean walk-in walls and floors', 'note' => ''],
                ['label' => 'Deep clean freezers (defrost if needed)', 'note' => ''],
                ['label' => 'Detail clean dish machine (delime if due)', 'note' => ''],
                ['label' => 'Clean light fixtures and high dust', 'note' => ''],
                ['label' => 'Pull coolers and clean coils if trained', 'note' => 'Or call tech'],
                ['label' => 'Inventory and restock cleaning chemicals', 'note' => ''],
                ['label' => 'Review pest control log / follow-ups', 'note' => ''],
            ],
    ],
    [
        'id' => 'station',
        'title' => 'Station Deep Cleans',
        'icon' => '🔪',
        'hint' => $is_sweet ? 'By station ownership' : 'By station ownership',
        'items' => $is_sweet
            ? [
                ['label' => 'Grill / flat — tools, scrapers, grease trap edge', 'note' => 'Grill lead'],
                ['label' => 'Fry — baskets, dump station, oil area', 'note' => 'Fry lead'],
                ['label' => 'Sauté / range — burners, splash, utensils', 'note' => ''],
                ['label' => 'Cold / garde — wells, lids, under-counter', 'note' => ''],
                ['label' => 'Expo / pass — plates, heat lamps, garnishes', 'note' => ''],
                ['label' => 'Prep room — slicers, mixers, sinks', 'note' => ''],
            ]
            : [
                ['label' => 'Grill / flat — tools, scrapers, grease trap edge', 'note' => 'Grill lead'],
                ['label' => 'Fry — baskets, dump station, oil area', 'note' => 'Fry lead'],
                ['label' => 'Sauté / range — burners, splash, utensils', 'note' => ''],
                ['label' => 'Cold / garde — wells, lids, under-counter', 'note' => ''],
                ['label' => 'Expo / pass — plates, heat lamps, garnishes', 'note' => ''],
                ['label' => 'Prep room — slicers, mixers, sinks', 'note' => ''],
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
    <title><?php echo $is_sweet ? 'Cleaning Schedule' : 'Cleaning Schedule'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
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
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.4rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .link-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .assign-filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .filter-chip {
            border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer;
            background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
        }
        .filter-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .chip { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC; font-family: 'DreamingOutLoudPro', serif;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE; font-family: 'Lora', serif;<?php endif; ?> }
        .progress-card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .progress-top { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .progress-bar { height: 12px; border-radius: 999px; background: <?php echo $is_sweet ? '#F7E0E4' : '#D9E0EA'; ?>; overflow: hidden; }
        .progress-fill { height: 100%; width: 0%; border-radius: 999px; transition: width 0.25s; <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .list-card { background: white; border-radius: 18px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); overflow: hidden; }
        .list-header { display: flex; align-items: center; gap: 12px; padding: 16px 18px; cursor: pointer; width: 100%; border: none; background: transparent; text-align: left; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .list-header:hover { background: <?php echo $is_sweet ? '#FFF8F9' : '#F8F5F1'; ?>; }
        .list-icon { width: 44px; height: 44px; min-width: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
        .list-titles { flex: 1; min-width: 0; }
        .list-title { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0; }
        .list-hint { margin: 4px 0 0; font-size: 0.9rem; opacity: 0.7; }
        .list-count { opacity: 0.65; font-size: 0.9rem; white-space: nowrap; }
        .chevron { opacity: 0.5; transition: transform 0.2s; }
        .list-card.open .chevron { transform: rotate(90deg); }
        .list-body { display: none; padding: 0 14px 16px; border-top: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .list-card.open .list-body { display: block; }
        .check-item { display: flex; flex-direction: column; align-items: stretch; gap: 0; padding: 10px 8px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; user-select: none; cursor: default; }
        .check-item input { display: none; }
        .check-main { display: flex; align-items: flex-start; gap: 12px; width: 100%; border: none; background: transparent; padding: 4px 0; margin: 0; text-align: left; cursor: pointer; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .checkbox { width: 26px; height: 26px; min-width: 26px; border-radius: 8px; border: 2px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; display: flex; align-items: center; justify-content: center; color: white; margin-top: 1px; }
        .check-item.done .checkbox { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .check-item.done .checkbox::after { content: '✓'; }
        .check-label { font-size: 1.08rem; line-height: 1.35; }
        .check-note { font-size: 0.9rem; opacity: 0.7; margin-top: 2px; }
        .check-item.done .check-label { opacity: 0.5; text-decoration: line-through; }
        .item-actions { display: flex; gap: 6px; margin-top: 6px; margin-left: 38px; }
        .task-photo-row { margin-left: 38px; }
        .empty-slot { text-align: center; padding: 20px 12px; margin: 12px 0; border-radius: 14px; border: 2px dashed <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; opacity: 0.8; line-height: 1.4; }
        .add-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .actions-bar { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { <?php if ($is_sweet): ?>background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A;<?php else: ?>background: #FFF8E8; color: #5C4B1A; border-color: #E0D2A0;<?php endif; ?> }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; padding: 22px 20px; max-height: 90vh; overflow-y: auto; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .print-only { display: none; }
        @media print {
            .no-print, .back-link, .toolbar, .assign-filters, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .btn, .modal-backdrop, .link-row, .item-actions, .add-row { display: none !important; }
            .print-only { display: block !important; }
            body { background: white; padding-bottom: 0; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .list-card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
            .list-body { display: block !important; }
            .list-card { margin-bottom: 10px; }
            .check-item { page-break-inside: avoid; }
            .check-main { cursor: default; color: #000 !important; }
            .check-item.done .check-label { opacity: 1; text-decoration: none; }
            .checkbox { background: white !important; }
            .check-item.done .checkbox::after { content: none; }
            .item-actions { display: none !important; }
            .sync-pill { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH" class="back-link">← <?php echo pbj_back_to_hub('boh'); ?></a>
        <h1><?php echo $is_sweet ? 'Cleaning Schedule' : 'Cleaning Schedule'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Daily, weekly, deep & station cleans' : 'Daily, weekly, deep, and station cleans'; ?></p>
    </div>
    <div class="content">
        <div class="intro no-print">
            <?php echo $is_sweet
                ? 'House cleaning lists are ready — Daily, Weekly, Monthly/Deep, and Station Deep Cleans. Assign who owns each task, check off live, snap 📷 photos, or add house tasks ✨'
                : 'Starter cleaning lists with assignees. Check-offs sync live; attach phone photos on tasks.'; ?>
        </div>
        <div class="link-row no-print">
            <a class="chip" href="/BOH/opening-closing"><?php echo $is_sweet ? '🌅 Open / Close' : '🌅 Open / Close'; ?></a>
            <a class="chip" href="/BOH/temps"><?php echo $is_sweet ? '🌡️ Temps' : '🌡️ Temps'; ?></a>
            <a class="chip" href="/BOH/tools"><?php echo $is_sweet ? '🛠️ Tools' : '🛠️ Tools'; ?></a>
        </div>
        <div class="sync-pill no-print syncing" id="sync-pill" title="<?php echo $is_sweet ? 'Shared with your restaurant group' : 'Shared with your restaurant group'; ?>">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>
        <div class="progress-card no-print">
            <div class="progress-top">
                <span><?php echo $is_sweet ? 'Checked off' : 'Checked off'; ?></span>
                <span id="progress-count">0 / 0</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
        </div>
        <div class="toolbar no-print">
            <button type="button" class="btn btn-primary" id="add-list-btn" data-perm="boh.cleaning.add_list"><?php echo $is_sweet ? '+ List' : '+ List'; ?></button>
            <button type="button" class="btn btn-secondary" id="uncheck-btn"><?php echo $is_sweet ? 'Uncheck all' : 'Uncheck all'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-clean-btn"><?php echo $is_sweet ? '🖨️ Print schedule' : 'Print schedule'; ?></button>
            <button type="button" class="btn btn-secondary" id="export-clean-btn"><?php echo $is_sweet ? 'Export CSV' : 'Export CSV'; ?></button>
        </div>
        <div class="assign-filters no-print" id="assign-filters">
            <button type="button" class="filter-chip active" data-assign-filter="all"><?php echo $is_sweet ? 'All tasks' : 'All'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="mine"><?php echo $is_sweet ? 'Mine' : 'Mine'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="unassigned"><?php echo $is_sweet ? 'Unassigned' : 'Unassigned'; ?></button>
        </div>
        <div class="print-only" id="print-clean-header" style="margin-bottom:8px;font-weight:600;"></div>
        <div id="lists"></div>
        <div class="actions-bar no-print">
            <button type="button" class="btn btn-secondary" id="reset-btn" data-perm="boh.cleaning.restore"><?php echo $is_sweet ? 'Restore starter lists' : 'Restore starter lists'; ?></button>
            <a href="/BOH" class="btn btn-primary"><?php echo pbj_back_to_hub('boh'); ?></a>
        </div>
    </div>
    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2 id="modal-title">Add</h2>
            <form id="form">
                <input type="hidden" id="m-mode"><input type="hidden" id="m-list"><input type="hidden" id="m-item">
                <div class="field" id="f-icon" style="display:none"><label for="m-icon"><?php echo $is_sweet ? 'Emoji' : 'Emoji'; ?></label><input id="m-icon" maxlength="4"></div>
                <div class="field"><label for="m-title"><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="m-title" required></div>
                <div class="field"><label for="m-note" id="note-label"><?php echo $is_sweet ? 'Note' : 'Note'; ?></label><input id="m-note"></div>
                <div class="field" id="f-assign"><label for="m-assign"><?php echo $is_sweet ? 'Assign to (optional)' : 'Assign to (optional)'; ?></label>
                    <input id="m-assign" list="clean-assign-list" type="text" maxlength="80" placeholder="<?php echo $is_sweet ? 'Name or role…' : 'Name or role…'; ?>">
                    <datalist id="clean-assign-list"></datalist>
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
    <script src="shared-state.js?v=4"></script>
    <script src="checklist-photos.js?v=1"></script>
    <script src="checklist-assign.js?v=2"></script>
    <script>
    (function () {
        // v3: stable starter item ids + kitchen sync
        const KEY = 'pbj_heat_cleaning_v3';
        const SHARED_KEY = 'heat_cleaning_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        const userName = <?php echo json_encode($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Team'); ?>;
        var assignFilter = 'all';
        var Assign = window.PbjChecklistAssign;
        if (Assign) Assign.ensureStyles(isSweet);
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function canPhotos() { return canP('boh.cleaning.attach_photos'); }
            function applyCleanPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof renderStations === 'function') renderStations(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        const defaults = <?php echo json_encode($default_lists, JSON_UNESCAPED_UNICODE); ?>;
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function totalItems(s) {
            var n = 0;
            (s && s.lists || []).forEach(function (l) { n += (l.items || []).length; });
            return n;
        }
        /** Original starter lists with stable ids so kitchen devices share the same tasks */
        function shells() {
            return {
                structureAt: Date.now(),
                lists: defaults.map(function (l) {
                    return {
                        id: l.id,
                        title: l.title,
                        icon: l.icon,
                        hint: l.hint,
                        open: l.id === 'daily',
                        items: (l.items || []).map(function (it, idx) {
                            return {
                                id: l.id + '-' + idx,
                                label: it.label,
                                note: it.note || '',
                                done: false,
                                doneUpdatedAt: 0,
                                assignedTo: ''
                            };
                        })
                    };
                })
            };
        }
        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || !r.lists || !r.lists.length || totalItems(r) === 0) {
                    // migrate from older local key if it still has tasks
                    try {
                        var old = JSON.parse(localStorage.getItem('pbj_heat_cleaning_v2') || 'null');
                        if (old && old.lists && totalItems(old) > 0) {
                            if (!old.structureAt) old.structureAt = Date.now();
                            (old.lists || []).forEach(function (l) {
                                (l.items || []).forEach(function (it) {
                                    if (it.doneUpdatedAt == null) it.doneUpdatedAt = 0;
                                });
                            });
                            return old;
                        }
                    } catch (e2) {}
                    return shells();
                }
                if (!r.structureAt) r.structureAt = Date.now();
                (r.lists || []).forEach(function (l) {
                    (l.items || []).forEach(function (it) {
                        if (!it.id) it.id = uid();
                        if (it.doneUpdatedAt == null) it.doneUpdatedAt = 0;
                        if (it.assignedTo == null) it.assignedTo = '';
                        if (Assign && Assign.ensureItemFields) Assign.ensureItemFields(it);
                    });
                });
                return r;
            } catch (e) { return shells(); }
        }
        function touchStructure() { state.structureAt = Date.now(); }
        function save(t, opts) {
            opts = opts || {};
            localStorage.setItem(KEY, JSON.stringify(state));
            if (t) {
                var el = document.getElementById('toast');
                el.textContent = typeof t === 'string' ? t : (isSweet ? 'Saved 💾' : 'Saved');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
            if (!opts.skipRemote && shared) shared.queuePush(state);
        }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function find(id) { return state.lists.find(function (l) { return l.id === id; }); }

        var state = load();
        var modal = document.getElementById('modal');
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
            if (!payload || !payload.lists) return;
            // Empty kitchen payload → restore original starter lists instead of blank UI
            if (totalItems(payload) === 0) {
                state = shells();
                localStorage.setItem(KEY, JSON.stringify(state));
                render();
                if (shared) shared.push(state, { force: true });
                return;
            }
            applyingRemote = true;
            state = payload;
            if (!state.structureAt) state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
            applyingRemote = false;
        }

        function progress() {
            var total = 0, done = 0;
            state.lists.forEach(function (l) {
                (l.items || []).forEach(function (i) { total++; if (i.done) done++; });
            });
            document.getElementById('progress-count').textContent = done + ' / ' + total;
            document.getElementById('progress-fill').style.width = total ? ((done / total) * 100) + '%' : '0%';
        }

        function render() {
            progress();
            if (Assign) Assign.paintFilterChips('#assign-filters', assignFilter);
            document.getElementById('lists').innerHTML = state.lists.map(function (l) {
                var items = l.items || [];
                var done = items.filter(function (i) { return i.done; }).length;
                var visible = Assign ? Assign.filterItems(items, assignFilter, userName) : items;
                var body;
                if (!items.length) {
                    body = '<div class="empty-slot">' + (isSweet ? 'No tasks yet — add your house cleaning list ✨' : 'No tasks yet. Add your house cleaning list.') + '</div>';
                } else if (!visible.length) {
                    body = '<div class="empty-slot">' + (isSweet ? 'No tasks match this filter on this list' : 'No tasks match this filter') + '</div>';
                } else {
                    var photosOk = canPhotos();
                    var Photos = window.PbjTaskPhotos;
                    body = visible.map(function (item) {
                        var photoHtml = '';
                        if (Photos) {
                            photoHtml = Photos.renderPhotoUi(item, { canAttach: photosOk, isSweet: isSweet });
                            photoHtml = photoHtml
                                .replace(/data-act="photo"/g, 'data-act="photo" data-list="' + esc(l.id) + '" data-item="' + esc(item.id) + '"')
                                .replace(/data-act="photo-del"/g, 'data-act="photo-del" data-list="' + esc(l.id) + '" data-item="' + esc(item.id) + '"');
                        }
                        var assignHtml = Assign ? Assign.badgeHtml(item, userName, isSweet, esc) : '';
                        return '<div class="check-item' + (item.done ? ' done' : '') + '">' +
                            '<button type="button" class="check-main" data-act="toggle-item" data-need-perm="boh.cleaning.check_off" data-list="' + esc(l.id) + '" data-item="' + esc(item.id) + '">' +
                            '<span class="checkbox" aria-hidden="true"></span>' +
                            '<span class="check-body"><div class="check-label">' + esc(item.label) + '</div>' +
                            (item.note ? '<div class="check-note">' + esc(item.note) + '</div>' : '') +
                            assignHtml +
                            '</span></button>' +
                            photoHtml +
                            '<div class="item-actions">' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="edit-item" data-need-perm="boh.cleaning.edit_tasks" data-list="' + esc(l.id) + '" data-item="' + esc(item.id) + '">Edit</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="del-item" data-need-perm="boh.cleaning.edit_tasks" data-list="' + esc(l.id) + '" data-item="' + esc(item.id) + '">Remove</button>' +
                            '</div></div>';
                    }).join('');
                }
                return '<div class="list-card' + (l.open ? ' open' : '') + '">' +
                    '<button type="button" class="list-header" data-act="toggle" data-need-perm="boh.cleaning.check_off" data-list="' + esc(l.id) + '">' +
                    '<span class="list-icon">' + esc(l.icon || '✨') + '</span>' +
                    '<span class="list-titles"><h2 class="list-title">' + esc(l.title) + '</h2>' +
                    (l.hint ? '<p class="list-hint">' + esc(l.hint) + '</p>' : '') + '</span>' +
                    '<span class="list-count">' + done + '/' + items.length + '</span><span class="chevron">›</span></button>' +
                    '<div class="list-body">' + body +
                    '<div class="add-row">' +
                    '<button type="button" class="btn btn-small btn-primary" data-act="add-item" data-need-perm="boh.cleaning.add_task" data-list="' + esc(l.id) + '">' + (isSweet ? '+ Task' : '+ Task') + '</button>' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit-list" data-need-perm="boh.cleaning.edit_list" data-list="' + esc(l.id) + '">' + (isSweet ? 'Edit list' : 'Edit list') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del-list" data-need-perm="boh.cleaning.edit_list" data-list="' + esc(l.id) + '">Remove</button>' +
                    '</div></div></div>';
            }).join('');
        }

        function openModal(mode, listId, itemId) {
            document.getElementById('m-mode').value = mode;
            document.getElementById('m-list').value = listId || '';
            document.getElementById('m-item').value = itemId || '';
            var isList = mode === 'list' || mode === 'edit-list';
            document.getElementById('f-icon').style.display = isList ? 'block' : 'none';
            var fAssign = document.getElementById('f-assign');
            if (fAssign) fAssign.style.display = isList ? 'none' : 'block';
            document.getElementById('note-label').textContent = isList ? (isSweet ? 'Description' : 'Description') : (isSweet ? 'When / who / notes' : 'When / who / notes');
            if (Assign) Assign.fillDatalist('clean-assign-list', userName, esc);
            if (mode === 'list') {
                document.getElementById('modal-title').textContent = isSweet ? 'New list' : 'New list';
                document.getElementById('m-title').value = ''; document.getElementById('m-note').value = ''; document.getElementById('m-icon').value = '✨';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = '';
            } else if (mode === 'edit-list') {
                var l = find(listId);
                document.getElementById('modal-title').textContent = isSweet ? 'Edit list' : 'Edit list';
                document.getElementById('m-title').value = l ? l.title : '';
                document.getElementById('m-note').value = l ? (l.hint || '') : '';
                document.getElementById('m-icon').value = l ? (l.icon || '✨') : '✨';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = '';
            } else if (mode === 'edit-item') {
                var list = find(listId);
                var it = list && list.items.find(function (x) { return x.id === itemId; });
                document.getElementById('modal-title').textContent = isSweet ? 'Edit task' : 'Edit task';
                document.getElementById('m-title').value = it ? it.label : '';
                document.getElementById('m-note').value = it ? (it.note || '') : '';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = it ? (it.assignedTo || '') : '';
            } else {
                document.getElementById('modal-title').textContent = isSweet ? 'Add task' : 'Add task';
                document.getElementById('m-title').value = ''; document.getElementById('m-note').value = '';
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = '';
            }
            modal.classList.add('show');
            setTimeout(function () { document.getElementById('m-title').focus(); }, 40);
        }

        document.getElementById('form').addEventListener('submit', function (e) {
            e.preventDefault();
            var mode = document.getElementById('m-mode').value;
            var listId = document.getElementById('m-list').value;
            var itemId = document.getElementById('m-item').value;
            var title = document.getElementById('m-title').value.trim();
            var assignTo = (document.getElementById('m-assign') && document.getElementById('m-assign').value.trim()) || '';
            if (!title) return;
            if (mode === 'list') {
                state.lists.push({ id: uid(), title: title, hint: document.getElementById('m-note').value.trim(), icon: document.getElementById('m-icon').value.trim() || '✨', open: true, items: [] });
                touchStructure();
            } else if (mode === 'edit-list') {
                var l = find(listId); if (!l) return;
                l.title = title; l.hint = document.getElementById('m-note').value.trim(); l.icon = document.getElementById('m-icon').value.trim() || '✨';
                touchStructure();
            } else if (mode === 'edit-item') {
                var list = find(listId); if (!list) return;
                var it = list.items.find(function (x) { return x.id === itemId; }); if (!it) return;
                it.label = title; it.note = document.getElementById('m-note').value.trim();
                it.assignedTo = assignTo;
                touchStructure();
            } else {
                var list2 = find(listId); if (!list2) return;
                list2.items.push({ id: uid(), label: title, note: document.getElementById('m-note').value.trim(), done: false, doneUpdatedAt: 0, assignedTo: assignTo });
                list2.open = true;
                touchStructure();
            }
            save(true); modal.classList.remove('show'); render();
        });
        document.getElementById('m-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });

        if (Assign) {
            Assign.wireFilterClicks(
                function () { return assignFilter; },
                function (f) { assignFilter = f; },
                function () { render(); }
            );
        }

        document.getElementById('lists').addEventListener('click', function (e) {
            if (e.target.closest('a.task-photo-thumb')) return;
                var b = e.target.closest('[data-act]'); if (b && b.getAttribute('data-need-perm') && !canP(b.getAttribute('data-need-perm'))) return;

            var btn = e.target.closest('button[data-act]');
            if (!btn || !document.getElementById('lists').contains(btn)) return;
            e.preventDefault();
            e.stopPropagation();
            var act = btn.dataset.act, listId = btn.dataset.list, itemId = btn.dataset.item, l = find(listId);
            if (act === 'toggle') { if (l) { l.open = !l.open; save(false, { skipRemote: true }); render(); } return; }
            if (act === 'toggle-item') {
                if (!l) return;
                var it = l.items.find(function (x) { return x.id === itemId; });
                if (it) {
                    it.done = !it.done;
                    it.doneUpdatedAt = Date.now();
                    save(false);
                    render();
                }
                return;
            }
            if (act === 'photo') {
                if (!canPhotos() || !window.PbjTaskPhotos || !l) return;
                var photoItem = l.items.find(function (x) { return x.id === itemId; });
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
                    }).catch(function () {
                        save(isSweet ? 'Couldn’t save photo' : 'Could not save photo', { skipRemote: true });
                    }).then(function () { btn.classList.remove('task-photo-busy'); });
                });
                return;
            }
            if (act === 'photo-del') {
                if (!canPhotos() || !window.PbjTaskPhotos || !l) return;
                var delItem = l.items.find(function (x) { return x.id === itemId; });
                if (!delItem) return;
                if (!confirm(isSweet ? 'Remove this photo?' : 'Remove this photo?')) return;
                PbjTaskPhotos.clearItemPhoto(delItem).then(function () {
                    save(true);
                    render();
                });
                return;
            }
            if (act === 'add-item') { openModal('item', listId); return; }
            if (act === 'edit-item') { openModal('edit-item', listId, itemId); return; }
            if (act === 'del-item') {
                if (!l || !confirm(isSweet ? 'Remove this task?' : 'Remove this task?')) return;
                var removing = l.items.find(function (x) { return x.id === itemId; });
                if (removing && window.PbjTaskPhotos) PbjTaskPhotos.clearItemPhoto(removing);
                l.items = l.items.filter(function (x) { return x.id !== itemId; });
                touchStructure();
                save(true); render(); return;
            }
            if (act === 'edit-list') { openModal('edit-list', listId); return; }
            if (act === 'del-list') {
                if (!confirm(isSweet ? 'Remove this list?' : 'Remove this list?')) return;
                state.lists = state.lists.filter(function (x) { return x.id !== listId; });
                touchStructure();
                save(true); render();
            }
        });

        document.getElementById('add-list-btn').addEventListener('click', function () {
                if (!canP('boh.cleaning.add_list')) return; openModal('list'); });
        document.getElementById('uncheck-btn').addEventListener('click', function () {
            var now = Date.now();
            state.lists.forEach(function (l) {
                (l.items || []).forEach(function (i) {
                    i.done = false;
                    i.doneUpdatedAt = now;
                });
            });
            save(true); render();
        });
        document.getElementById('reset-btn').addEventListener('click', function () {
                if (!canP('boh.cleaning.restore')) return;
            if (!confirm(isSweet
                ? 'Restore the original starter lists (Daily, Weekly, Monthly/Deep, Station) for the whole kitchen today?'
                : 'Restore original starter cleaning lists for the kitchen today?')) return;
            state = shells();
            save(isSweet ? 'Starter lists restored ✨' : 'Starter lists restored');
            render();
            if (shared) shared.push(state, { force: true });
        });
        document.getElementById('print-clean-btn').addEventListener('click', function () {
            state.lists.forEach(function (l) { l.open = true; });
            render();
            document.getElementById('print-clean-header').textContent =
                (isSweet ? 'Cleaning Schedule · ' : 'Cleaning Schedule · ') + new Date().toLocaleString();
            setTimeout(function () { window.print(); }, 50);
        });
        document.getElementById('export-clean-btn').addEventListener('click', function () {
            var rows = [['List', 'Task', 'Note', 'Assigned', 'Done']];
            state.lists.forEach(function (l) {
                (l.items || []).forEach(function (it) {
                    rows.push([l.title || '', it.label || '', it.note || '', it.assignedTo || '', it.done ? 'yes' : 'no']);
                });
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
            a.download = 'cleaning-schedule.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        });

        render();

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
                function () {
                    if (totalItems(state) === 0) state = shells();
                    return state;
                },
                function (payload) { applyRemoteState(payload); }
            ).then(function (result) {
                // If nothing useful on server, seed original starters for the kitchen
                if (result && (result.source === 'empty' || result.source === 'seeded')) {
                    if (totalItems(state) === 0) {
                        state = shells();
                        save(false, { skipRemote: true });
                        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyCleanPerms);
            document.addEventListener('pbj-perms-ready', applyCleanPerms);
                        shared.push(state, { force: true });
                    }
                }
                shared.startPolling();
            });
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only' : 'Local only' });
        }
    })();
    </script>
</body>
</html>
