<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

// Seeded house checklists — fully editable in the browser
$default_lists = [
    [
        'id' => 'opening',
        'title' => $is_sweet ? 'Opening' : 'Opening',
        'icon' => '🌅',
        'hint' => $is_sweet ? 'Before the first ticket hits' : 'Before first tickets',
        'items' => $is_sweet
            ? [
                'Walk-in & reach-in temps logged',
                'Hand sinks stocked (soap, paper towels)',
                'Sanitizer buckets mixed & strip-tested',
                'Line pans filled to pars — labeled & dated',
                'Grill / flat top fired & cleaned',
                'Fryers on, oil checked, baskets ready',
                'Ovens / equipment preheated to service temps',
                'Boards, knives & station tools set',
                'Ticket printer / KDS online',
                '86 board reviewed with FOH',
                'Trash liners in, floor mats down',
                'Final line walk — ready for tickets 🔥',
            ]
            : [
                'Log walk-in & reach-in temps',
                'Stock hand sinks (soap, paper towels)',
                'Mix and strip-test sanitizer buckets',
                'Fill line pans to pars; label & date',
                'Fire and clean grill / flat top',
                'Turn on fryers; check oil and baskets',
                'Preheat ovens / equipment to service temps',
                'Set boards, knives, and station tools',
                'Confirm ticket printer / KDS online',
                'Review 86 board with FOH',
                'Set trash liners and floor mats',
                'Final line walk — ready for service',
            ],
    ],
    [
        'id' => 'mid',
        'title' => $is_sweet ? 'Mid-Shift' : 'Mid-Shift',
        'icon' => '🔄',
        'hint' => $is_sweet ? 'Changeover & rush reset' : 'Changeover and rush reset',
        'items' => $is_sweet
            ? [
                'Line pans topped / refilled to pars',
                'Backup product pulled & labeled',
                'Boards swapped / re-sanitized mid-service',
                'Sanitizer refreshed & re-tested',
                'Floors swept in dead zones / spills cleared',
                'Trash not overflowing',
                '86 board updated with FOH',
                'Temps spot-checked (hot hold & cold wells)',
                'Hand sinks still stocked',
                'Next-shift notes started if needed',
            ]
            : [
                'Top / refill line pans to pars',
                'Pull and label backup product',
                'Swap / re-sanitize boards mid-service',
                'Refresh and re-test sanitizer',
                'Sweep floors; clear spills',
                'Empty trash before full',
                'Update 86 board with FOH',
                'Spot-check hot hold and cold well temps',
                'Restock hand sinks',
                'Start next-shift notes if needed',
            ],
    ],
    [
        'id' => 'closing',
        'title' => $is_sweet ? 'Closing' : 'Closing',
        'icon' => '🌙',
        'hint' => $is_sweet ? 'Break down clean & lock it in' : 'Break down, clean, secure',
        'items' => $is_sweet
            ? [
                'All product labeled, dated & put away',
                'Line broken down; pans washed & stored',
                'Grill / flat top scrubbed & oiled',
                'Fryers filtered / shut down per house rules',
                'Ovens wiped & turned off',
                'Boards, knives & tools washed & stored',
                'Floors swept & mopped',
                'Mats cleaned / hung',
                'Trash & recycling out; liners replaced',
                'Walk-in organized; temps logged',
                'Sanitizer dumped; sinks cleaned',
                'Hoods wiped as assigned; lights out 🌙',
            ]
            : [
                'Label, date, and put away all product',
                'Break down line; wash and store pans',
                'Scrub and oil grill / flat top',
                'Filter / shut down fryers per house rules',
                'Wipe ovens and turn off',
                'Wash and store boards, knives, tools',
                'Sweep and mop floors',
                'Clean / hang mats',
                'Take out trash & recycling; replace liners',
                'Organize walk-in; log temps',
                'Dump sanitizer; clean sinks',
                'Wipe hoods as assigned; lights out',
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
    <title><?php echo $is_sweet ? 'Opening & Closing' : 'Opening & Closing'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
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
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; font-size: 1.02rem; }
        .link-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .chip { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC; font-family: 'DreamingOutLoudPro', serif;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE; font-family: 'Lora', serif;<?php endif; ?> }
        .tabs { display: flex; gap: 8px; margin-bottom: 16px; }
        .tab { flex: 1; border: none; border-radius: 14px; padding: 12px 8px; font-size: 0.95rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .progress-card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .progress-top { display: flex; justify-content: space-between; margin-bottom: 8px; gap: 10px; }
        .progress-bar { height: 12px; border-radius: 999px; background: <?php echo $is_sweet ? '#F7E0E4' : '#D9E0EA'; ?>; overflow: hidden; }
        .progress-fill { height: 100%; width: 0%; border-radius: 999px; transition: width 0.25s; <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .complete-banner { display: none; margin-top: 12px; padding: 14px; border-radius: 14px; text-align: center; <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A; border: 1px solid #B6E5CF;<?php else: ?>background: #EAF1FA; color: #1A2A44; border: 1px solid #C5D4E8;<?php endif; ?> }
        .complete-banner.show { display: block; }
        .checklist { background: white; border-radius: 18px; padding: 8px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.08); margin-bottom: 12px; }
        .check-item { display: flex; flex-direction: column; align-items: stretch; gap: 0; padding: 10px 16px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; user-select: none; }
        .check-item:last-of-type { border-bottom: none; }
        .check-item input { display: none; }
        .check-main { display: flex; align-items: flex-start; gap: 12px; width: 100%; border: none; background: transparent; padding: 4px 0; margin: 0; text-align: left; cursor: pointer; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .check-main:hover { opacity: 0.92; }
        .checkbox { width: 26px; height: 26px; min-width: 26px; border-radius: 8px; border: 2px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; display: flex; align-items: center; justify-content: center; color: white; margin-top: 1px; }
        .check-item.done .checkbox { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .check-item.done .checkbox::after { content: '✓'; }
        .check-body { flex: 1; min-width: 0; }
        .check-text { font-size: 1.08rem; line-height: 1.35; }
        .check-item.done .check-text { opacity: 0.55; text-decoration: line-through; }
        .item-actions { display: flex; gap: 6px; margin-top: 6px; margin-left: 38px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .btn { border: none; border-radius: 14px; padding: 12px 14px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 7px 11px; font-size: 0.88rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .actions { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions .btn { flex: 1; min-width: 120px; }
        .empty { text-align: center; padding: 24px 14px; opacity: 0.8; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint-sm { font-size: 0.88rem; opacity: 0.65; margin: 0 0 10px; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { <?php if ($is_sweet): ?>background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A;<?php else: ?>background: #FFF8E8; color: #5C4B1A; border-color: #E0D2A0;<?php endif; ?> }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .print-only { display: none; }
        .print-all-block { display: none; }
        body.print-all .screen-list { display: none !important; }
        body.print-all .print-all-block { display: block !important; }
        body.print-one .print-all-block { display: none !important; }
        @media print {
            .no-print, .back-link, .toolbar, .assign-filters, .actions, .bottom-nav, #bottom-nav, nav, .toast, .btn, .modal-backdrop, .link-row, .tabs, .progress-card, .intro, .item-actions, .hint-sm { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 12px 16px; }
            h1 { font-size: 1.4rem; }
            .subtitle { display: none; }
            .content { padding: 8px 12px; max-width: none; }
            .checklist { box-shadow: none; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 12px; page-break-inside: avoid; }
            .check-item { padding: 6px 10px; border-bottom: 1px solid #ddd; page-break-inside: avoid; cursor: default; }
            .check-main { cursor: default; color: #000 !important; }
            .check-item.done .check-text { opacity: 1; text-decoration: none; }
            .checkbox { width: 16px; height: 16px; min-width: 16px; border-radius: 3px; border: 1.5px solid #333; background: white !important; }
            .check-item.done .checkbox::after { content: none; }
            .check-text { font-size: 0.95rem; }
            .item-actions { display: none !important; }
            .print-section-title { font-size: 1.1rem; font-weight: 600; margin: 12px 0 6px; page-break-after: avoid; }
            body.print-all .screen-list { display: none !important; }
            body.print-all .print-all-block { display: block !important; }
            body.print-one .print-all-block { display: none !important; }
            body.print-one .screen-list { display: block !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH" class="back-link">← <?php echo pbj_back_to_hub('boh'); ?></a>
        <h1><?php echo $is_sweet ? 'Opening & Closing' : 'Opening & Closing'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Kitchen open, mid-shift & close checklists' : 'Kitchen open, mid-shift, and close checklists'; ?></p>
    </div>

    <div class="content">
        <div class="intro no-print">
            <?php echo $is_sweet
                ? 'Opening, Mid-Shift, and Closing lists are ready — assign who owns each task, check them off live with your restaurant group, snap a 📷 photo, and ping management when a list is fully done 🔥'
                : 'Opening, Mid-Shift, and Closing lists with assignees. Check-offs sync live; attach phone photos; managers get notified when a list completes.'; ?>
        </div>

        <div class="link-row no-print">
            <a class="chip" href="/BOH/temps"><?php echo $is_sweet ? '🌡️ Log temps' : '🌡️ Log temps'; ?></a>
            <a class="chip" href="/BOH/prep"><?php echo $is_sweet ? '🔪 Prep lists' : '🔪 Prep lists'; ?></a>
            <a class="chip" href="/BOH/cleaning"><?php echo $is_sweet ? '✨ Cleaning' : '✨ Cleaning'; ?></a>
            <a class="chip" href="/BOH/86"><?php echo $is_sweet ? '🚫 86 board' : '🚫 86 board'; ?></a>
        </div>

        <div class="sync-pill no-print syncing" id="sync-pill" title="<?php echo $is_sweet ? 'Shared with your restaurant group' : 'Shared with your restaurant group'; ?>">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div class="tabs no-print" id="tabs"></div>

        <div class="progress-card no-print">
            <div class="progress-top">
                <span id="progress-label"><?php echo $is_sweet ? 'Kitchen progress' : 'Progress'; ?></span>
                <span id="progress-count">0 / 0</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
            <div class="complete-banner" id="complete-banner"><?php echo $is_sweet ? 'All done — kitchen crushed it! 🔥' : 'Checklist complete.'; ?></div>
        </div>

        <div class="toolbar no-print">
            <button type="button" class="btn btn-primary" id="add-item-btn" data-perm="boh.opening.add_tasks"><?php echo $is_sweet ? '+ Task' : '+ Task'; ?></button>
            <button type="button" class="btn btn-secondary" id="reset-checks-btn" data-perm="boh.opening.uncheck"><?php echo $is_sweet ? 'Uncheck list' : 'Uncheck list'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-list-btn" data-perm="boh.opening.print"><?php echo $is_sweet ? '🖨️ Print this list' : 'Print this list'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-all-btn" data-perm="boh.opening.print"><?php echo $is_sweet ? '🖨️ Print all (open · mid · close)' : 'Print all lists'; ?></button>
        </div>
        <div class="assign-filters no-print" id="assign-filters">
            <button type="button" class="filter-chip active" data-assign-filter="all"><?php echo $is_sweet ? 'All tasks' : 'All'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="mine"><?php echo $is_sweet ? 'Mine' : 'Mine'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="unassigned"><?php echo $is_sweet ? 'Unassigned' : 'Unassigned'; ?></button>
        </div>
        <div class="print-only" id="print-oc-header" style="margin-bottom:8px;font-weight:600;"></div>

        <div class="screen-list" id="list-root"></div>
        <div class="print-all-block" id="print-all-root"></div>

        <p class="hint-sm no-print"><?php echo $is_sweet ? 'Tip: Edit or remove any starter task so it matches your kitchen.' : 'Tip: Edit or remove starter tasks to match your kitchen.'; ?></p>

        <div class="actions no-print">
            <button type="button" class="btn btn-secondary" id="reset-shells-btn"><?php echo $is_sweet ? 'Restore starter lists' : 'Restore starter lists'; ?></button>
            <a href="/BOH" class="btn btn-primary"><?php echo pbj_back_to_hub('boh'); ?></a>
        </div>
    </div>

    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2 id="modal-title"><?php echo $is_sweet ? 'Task' : 'Task'; ?></h2>
            <form id="form">
                <input type="hidden" id="m-id">
                <div class="field">
                    <label for="m-label"><?php echo $is_sweet ? 'Task' : 'Task'; ?></label>
                    <input id="m-label" required maxlength="160" placeholder="<?php echo $is_sweet ? 'e.g. Filter fryer #2' : 'e.g. Filter fryer #2'; ?>">
                </div>
                <div class="field" id="f-assign">
                    <label for="m-assign"><?php echo $is_sweet ? 'Assign to (optional)' : 'Assign to (optional)'; ?></label>
                    <input id="m-assign" list="oc-assign-list" type="text" maxlength="80" placeholder="<?php echo $is_sweet ? 'Name or role…' : 'Name or role…'; ?>">
                    <datalist id="oc-assign-list"></datalist>
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
    <script src="checklist-complete.js?v=1"></script>
    <script src="checklist-assign.js?v=2"></script>
    <script>
    (function () {
        // v3: stable starter task ids + kitchen sync key with full Open/Mid/Close lists
        const KEY = 'pbj_heat_open_close_v3';
        const SHARED_KEY = 'heat_open_close_v3';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        const userName = <?php echo json_encode($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Team'); ?>;
        var assignFilter = 'all';
        var Assign = window.PbjChecklistAssign;
        if (Assign) Assign.ensureStyles(isSweet);
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function canPhotos() { return canP('boh.opening.attach_photos'); }
            function listIsComplete(list) {
                var items = (list && list.items) || [];
                return items.length > 0 && items.every(function (i) { return i.done; });
            }
            function notifyIfListComplete(list) {
                if (!list || !listIsComplete(list) || !window.PbjListComplete) return;
                PbjListComplete.maybeNotify({
                    sourceKey: SHARED_KEY,
                    listId: list.id,
                    listTitle: ((list.icon ? list.icon + ' ' : '') + (list.title || 'List')).trim(),
                    pageTitle: isSweet ? 'BOH Opening & Closing' : 'BOH Opening & Closing',
                    href: '/BOH/opening-closing',
                    completedBy: userName,
                    isSweet: isSweet
                });
            }
            function clearCompleteNotify(list) {
                if (list && window.PbjListComplete) PbjListComplete.clearDedupe(SHARED_KEY, list.id);
            }
            function applyBohOcPerms() {
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
        /** True when Opening + Mid + Closing all exist with real tasks */
        function hasFullStarters(s) {
            if (!s || !s.lists || !s.lists.length) return false;
            var need = { opening: false, mid: false, closing: false };
            s.lists.forEach(function (l) {
                if (need.hasOwnProperty(l.id) && (l.items || []).length > 0) need[l.id] = true;
            });
            return need.opening && need.mid && need.closing;
        }
        /** Original Opening / Mid-Shift / Closing with stable ids for kitchen sync */
        function shells() {
            return {
                active: 'opening',
                structureAt: Date.now(),
                lists: defaults.map(function (l) {
                    return {
                        id: l.id,
                        title: l.title,
                        icon: l.icon,
                        hint: l.hint,
                        items: (l.items || []).map(function (label, idx) {
                            return {
                                id: l.id + '-' + idx,
                                label: label,
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
                if (!r || !r.lists || !r.lists.length || !hasFullStarters(r)) {
                    // migrate older local key if it still has full lists
                    try {
                        var old = JSON.parse(localStorage.getItem('pbj_heat_open_close_v2') || 'null');
                        if (old && hasFullStarters(old)) {
                            if (!old.structureAt) old.structureAt = Date.now();
                            (old.lists || []).forEach(function (l) {
                                (l.items || []).forEach(function (it) {
                                    if (!it.id) it.id = uid();
                                    if (it.doneUpdatedAt == null) it.doneUpdatedAt = 0;
                                });
                            });
                            return old;
                        }
                    } catch (e2) {}
                    return shells();
                }
                if (!r.active) r.active = r.lists[0].id;
                if (!r.structureAt) r.structureAt = Date.now();
                r.lists.forEach(function (l) {
                    (l.items || []).forEach(function (it) {
                        if (!it.id) it.id = uid();
                        if (typeof it.done !== 'boolean') it.done = false;
                        if (it.doneUpdatedAt == null) it.doneUpdatedAt = 0;
                        if (it.assignedTo == null) it.assignedTo = '';
                        if (Assign && Assign.ensureItemFields) Assign.ensureItemFields(it);
                    });
                });
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
                var el = document.getElementById('toast');
                el.textContent = typeof toast === 'string' ? toast : (isSweet ? 'Saved 💾' : 'Saved');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
            if (!opts.skipRemote && shared) {
                shared.queuePush(state);
            }
        }
        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function activeList() {
            return state.lists.find(function (l) { return l.id === state.active; }) || state.lists[0];
        }

        var state = load();
        var modal = document.getElementById('modal');
        var editId = null;
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
            // Incomplete / empty kitchen payload → restore full starter trio
            if (!hasFullStarters(payload)) {
                state = shells();
                localStorage.setItem(KEY, JSON.stringify(state));
                render();
                if (shared) shared.push(state, { force: true });
                return;
            }
            applyingRemote = true;
            var keepActive = state.active;
            state = payload;
            if (!state.active) state.active = keepActive || (state.lists[0] && state.lists[0].id);
            if (keepActive && state.lists.some(function (l) { return l.id === keepActive; })) {
                state.active = keepActive;
            }
            if (!state.structureAt) state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
            applyingRemote = false;
        }

        function renderTabs() {
            document.getElementById('tabs').innerHTML = state.lists.map(function (l) {
                return '<button type="button" class="tab' + (l.id === state.active ? ' active' : '') + '" data-tab="' + esc(l.id) + '">' +
                    esc(l.icon || '') + ' ' + esc(l.title) + '</button>';
            }).join('');
        }

        function render() {
            renderTabs();
            if (Assign) Assign.paintFilterChips('#assign-filters', assignFilter);
            var list = activeList();
            var items = list ? (list.items || []) : [];
            var visible = Assign ? Assign.filterItems(items, assignFilter, userName) : items;
            var done = items.filter(function (i) { return i.done; }).length;
            document.getElementById('progress-count').textContent = done + ' / ' + items.length;
            document.getElementById('progress-fill').style.width = items.length ? ((done / items.length) * 100) + '%' : '0%';
            document.getElementById('complete-banner').classList.toggle('show', items.length > 0 && done === items.length);
            document.getElementById('progress-label').textContent = list
                ? ((list.icon ? list.icon + ' ' : '') + list.title + (list.hint ? ' · ' + list.hint : ''))
                : (isSweet ? 'Kitchen progress' : 'Progress');

            var root = document.getElementById('list-root');
            if (!items.length) {
                root.innerHTML = '<div class="checklist"><div class="empty">' +
                    (isSweet ? 'No tasks yet — add one for this shift ✨' : 'No tasks yet. Add one for this shift.') +
                    '</div></div>';
                return;
            }
            if (!visible.length) {
                root.innerHTML = '<div class="checklist"><div class="empty">' +
                    (isSweet ? 'No tasks match this filter' : 'No tasks match this filter') +
                    '</div></div>';
                return;
            }
            var photosOk = canPhotos();
            var Photos = window.PbjTaskPhotos;
            root.innerHTML = '<div class="checklist">' + visible.map(function (item) {
                var photoHtml = Photos ? Photos.renderPhotoUi(item, { canAttach: photosOk, isSweet: isSweet }) : '';
                var assignHtml = Assign ? Assign.badgeHtml(item, userName, isSweet, esc) : '';
                return '<div class="check-item' + (item.done ? ' done' : '') + '">' +
                    '<button type="button" class="check-main" data-act="toggle" data-need-perm="boh.opening.check_off" data-id="' + esc(item.id) + '">' +
                    '<span class="checkbox" aria-hidden="true"></span>' +
                    '<span class="check-body"><div class="check-text">' + esc(item.label) + '</div>' +
                    assignHtml + '</span></button>' +
                    photoHtml +
                    '<div class="item-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-need-perm="boh.opening.edit_tasks" data-id="' + esc(item.id) + '">Edit</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-need-perm="boh.opening.edit_tasks" data-id="' + esc(item.id) + '">Remove</button>' +
                    '</div></div>';
            }).join('') + '</div>';
        }

        document.getElementById('tabs').addEventListener('click', function (e) {
            var tab = e.target.closest('[data-tab]');
            if (!tab) return;
            state.active = tab.dataset.tab;
            save(false, { skipRemote: true }); // tab choice is personal preference
            render();
        });

        document.getElementById('list-root').addEventListener('click', function (e) {
            if (e.target.closest('a.task-photo-thumb')) return;
                var b = e.target.closest('[data-act]'); if (b && b.getAttribute('data-need-perm') && !canP(b.getAttribute('data-need-perm'))) return;

            var btn = e.target.closest('button[data-act]');
            if (!btn || !document.getElementById('list-root').contains(btn)) return;
            e.preventDefault();
            e.stopPropagation();
            var list = activeList();
            if (!list) return;
            var id = btn.dataset.id;
            var act = btn.dataset.act;
            if (act === 'toggle') {
                var it = list.items.find(function (x) { return x.id === id; });
                if (it) {
                    var wasDone = !!it.done;
                    it.done = !it.done;
                    it.doneUpdatedAt = Date.now();
                    save(false);
                    render();
                    if (it.done && !wasDone) notifyIfListComplete(list);
                    else if (!it.done && wasDone) clearCompleteNotify(list);
                }
                return;
            }
            if (act === 'photo') {
                if (!canPhotos() || !window.PbjTaskPhotos) return;
                var photoItem = list.items.find(function (x) { return x.id === id; });
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
                        notifyIfListComplete(list);
                    }).catch(function () {
                        save(isSweet ? 'Couldn’t save photo' : 'Could not save photo', { skipRemote: true });
                    }).then(function () { btn.classList.remove('task-photo-busy'); });
                });
                return;
            }
            if (act === 'photo-del') {
                if (!canPhotos() || !window.PbjTaskPhotos) return;
                var delItem = list.items.find(function (x) { return x.id === id; });
                if (!delItem) return;
                if (!confirm(isSweet ? 'Remove this photo?' : 'Remove this photo?')) return;
                PbjTaskPhotos.clearItemPhoto(delItem).then(function () {
                    save(true);
                    render();
                });
                return;
            }
            if (act === 'edit') {
                var item = list.items.find(function (x) { return x.id === id; });
                if (!item) return;
                editId = id;
                document.getElementById('modal-title').textContent = isSweet ? 'Edit task' : 'Edit task';
                document.getElementById('m-label').value = item.label;
                if (document.getElementById('m-assign')) document.getElementById('m-assign').value = item.assignedTo || '';
                if (Assign) Assign.fillDatalist('oc-assign-list', userName, esc);
                modal.classList.add('show');
                setTimeout(function () { document.getElementById('m-label').focus(); }, 40);
                return;
            }
            if (act === 'del') {
                if (!confirm(isSweet ? 'Remove this task?' : 'Remove this task?')) return;
                var removing = list.items.find(function (x) { return x.id === id; });
                if (removing && window.PbjTaskPhotos) PbjTaskPhotos.clearItemPhoto(removing);
                list.items = list.items.filter(function (x) { return x.id !== id; });
                touchStructure();
                clearCompleteNotify(list);
                save(true); render();
            }
        });

        document.getElementById('add-item-btn').addEventListener('click', function () {
                if (!canP('boh.opening.add_tasks')) return;
            editId = null;
            document.getElementById('modal-title').textContent = isSweet ? 'Add task' : 'Add task';
            document.getElementById('m-label').value = '';
            if (document.getElementById('m-assign')) document.getElementById('m-assign').value = '';
            if (Assign) Assign.fillDatalist('oc-assign-list', userName, esc);
            modal.classList.add('show');
            setTimeout(function () { document.getElementById('m-label').focus(); }, 40);
        });

        document.getElementById('form').addEventListener('submit', function (e) {
            e.preventDefault();
            var label = document.getElementById('m-label').value.trim();
            if (!label) return;
            var assignTo = (document.getElementById('m-assign') && document.getElementById('m-assign').value.trim()) || '';
            var list = activeList();
            if (!list) return;
            if (editId) {
                var it = list.items.find(function (x) { return x.id === editId; });
                if (it) {
                    it.label = label;
                    it.assignedTo = assignTo;
                }
            } else {
                list.items.push({ id: uid(), label: label, done: false, doneUpdatedAt: 0, assignedTo: assignTo });
            }
            touchStructure();
            save(true);
            modal.classList.remove('show');
            render();
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

        document.getElementById('reset-checks-btn').addEventListener('click', function () {
                if (!canP('boh.opening.uncheck')) return;
            var list = activeList();
            if (!list) return;
            var now = Date.now();
            list.items.forEach(function (i) {
                i.done = false;
                i.doneUpdatedAt = now;
            });
            clearCompleteNotify(list);
            save(true); render();
        });

        document.getElementById('reset-shells-btn').addEventListener('click', function () {
            if (!confirm(isSweet
                ? 'Restore original Opening, Mid-Shift, and Closing lists for the whole kitchen today?'
                : 'Restore original Opening, Mid, and Closing lists for the kitchen today?')) return;
            state = shells();
            save(isSweet ? 'Starter lists restored ✨' : 'Starter lists restored');
            render();
            if (shared) shared.push(state, { force: true });
        });

        function buildPrintListHtml(list) {
            var items = list.items || [];
            var rows = items.length
                ? items.map(function (item) {
                    return '<label class="check-item">' +
                        '<span class="checkbox"></span>' +
                        '<span class="check-body"><div class="check-text">' + esc(item.label) +
                        (item.assignedTo ? ' <em>(' + esc(item.assignedTo) + ')</em>' : '') +
                        '</div></span></label>';
                }).join('')
                : '<div class="empty">' + (isSweet ? 'No tasks' : 'No tasks') + '</div>';
            return '<div class="print-section-title">' + esc((list.icon ? list.icon + ' ' : '') + list.title) +
                (list.hint ? ' · ' + esc(list.hint) : '') + '</div>' +
                '<div class="checklist">' + rows + '</div>';
        }

        function stampPrintHeader(label) {
            var d = new Date();
            document.getElementById('print-oc-header').textContent =
                label + ' · ' + d.toLocaleDateString() + ' · ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        document.getElementById('print-list-btn').addEventListener('click', function () {
                if (!canP('boh.opening.print')) return;
            var list = activeList();
            if (!list) return;
            document.body.classList.remove('print-all');
            document.body.classList.add('print-one');
            stampPrintHeader((list.icon ? list.icon + ' ' : '') + list.title + (isSweet ? ' checklist' : ' checklist'));
            setTimeout(function () {
                window.print();
                document.body.classList.remove('print-one');
            }, 60);
        });

        document.getElementById('print-all-btn').addEventListener('click', function () {
                if (!canP('boh.opening.print')) return;
            document.getElementById('print-all-root').innerHTML = state.lists.map(buildPrintListHtml).join('');
            document.body.classList.remove('print-one');
            document.body.classList.add('print-all');
            stampPrintHeader(isSweet ? 'Opening · Mid-Shift · Closing checklists' : 'Opening · Mid · Closing checklists');
            setTimeout(function () {
                window.print();
                document.body.classList.remove('print-all');
            }, 60);
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
                    if (!hasFullStarters(state)) state = shells();
                    return state;
                },
                function (payload) { applyRemoteState(payload); }
            ).then(function (result) {
                if (result && (result.source === 'empty' || result.source === 'seeded')) {
                    if (!hasFullStarters(state)) {
                        state = shells();
                        save(false, { skipRemote: true });
                        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyBohOcPerms);
            document.addEventListener('pbj-perms-ready', applyBohOcPerms);
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
