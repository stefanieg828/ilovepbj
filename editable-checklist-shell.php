<?php
/**
 * Shared chrome for editable Showtime checklists.
 * Expects: $is_sweet, $page_title, $page_sub, $hub_href, $hub_label, $intro_html, $notes_enabled (bool)
 */
if (!isset($notes_enabled)) $notes_enabled = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo htmlspecialchars($page_title); ?> • <?php echo pbj_hub_label('foh'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
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
        .tabs { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .tab { flex: 1; min-width: 90px; border: none; border-radius: 14px; padding: 12px 8px; font-size: 0.95rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
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
        .check-main { display: flex; align-items: flex-start; gap: 12px; width: 100%; border: none; background: transparent; padding: 4px 0; margin: 0; text-align: left; cursor: pointer; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
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
        .actions .btn { flex: 1; min-width: 110px; }
        .empty { text-align: center; padding: 24px 14px; opacity: 0.8; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 110px; resize: vertical; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint-sm { font-size: 0.88rem; opacity: 0.65; margin: 0 0 10px; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .notes-card { background: white; border-radius: 18px; padding: 16px 18px; margin: 14px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .notes-card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 8px; }
        .notes-card .field { margin-bottom: 10px; }
        .notes-card .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .notes-card .field input, .notes-card .field select, .notes-card .field textarea {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?>
        }
        .notes-card .field textarea { min-height: 88px; resize: vertical; }
        .notes-card .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .notes-card .field-row .field { flex: 1; min-width: 100px; }
        .notes-card .form-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
        .notes-card .form-actions .btn { flex: 1; min-width: 120px; }
        .handoff-item {
            border-left: 5px solid #1A2A44;
            border-radius: 0 12px 12px 0; padding: 12px 14px; margin-bottom: 10px;
            background: #F4F4F5;
            color: #111111;
        }
        .handoff-item .ht { font-size: 1.05rem; margin: 0 0 4px; color: #111111; font-weight: 600; }
        .handoff-item .hm { font-size: 0.85rem; color: #222222; opacity: 0.85; margin-bottom: 6px; }
        .handoff-item .hb { font-size: 0.98rem; line-height: 1.4; white-space: pre-wrap; margin: 0 0 8px; color: #111111; }
        .handoff-empty { text-align: center; padding: 16px 8px; color: #111111; font-size: 0.95rem; opacity: 0.9; }
        .handoff-feed-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin: 16px 0 10px; }
        .handoff-feed-head h3 { margin: 0; font-size: 1.05rem; color: #111111; opacity: 1; font-weight: 600; }
        .notes-status { font-size: 0.88rem; opacity: 0.65; margin-top: 6px; min-height: 1.2em; }
        .print-only { display: none; }
        .print-all-block { display: none; }
        .print-section-title { font-weight: 600; margin: 12px 0 6px; }
        body.print-all .screen-list { display: none !important; }
        body.print-all .print-all-block { display: block !important; }
        @media print {
            .no-print, .back-link, .toolbar, .actions, .bottom-nav, #bottom-nav, nav, .toast, .btn, .modal-backdrop, .tabs, .progress-card, .intro, .item-actions, .hint-sm, .sync-pill { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 12px 16px; }
            h1 { font-size: 1.4rem; }
            .subtitle { display: none; }
            body.print-all .screen-list { display: none !important; }
            body.print-all .print-all-block { display: block !important; }
            .checklist { box-shadow: none; border: 1px solid #ccc; }
            .checkbox { background: white !important; }
            .check-item.done .checkbox::after { content: none; }
            .check-item.done .check-text { opacity: 1; text-decoration: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="<?php echo htmlspecialchars($hub_href); ?>" class="back-link">← <?php echo htmlspecialchars($hub_label); ?></a>
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="subtitle"><?php echo htmlspecialchars($page_sub); ?></p>
    </div>
    <div class="content">
        <div class="intro no-print"><?php echo $intro_html; ?></div>
        <div class="sync-pill no-print syncing" id="ecl-sync-pill">
            <span class="dot"></span>
            <span id="ecl-sync-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>
        <div class="tabs no-print" id="ecl-tabs"></div>
        <div class="progress-card no-print">
            <div class="progress-top">
                <span id="ecl-progress-label"><?php echo $is_sweet ? 'Progress' : 'Progress'; ?></span>
                <span id="ecl-progress-count">0 / 0</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" id="ecl-progress-fill"></div></div>
            <div class="complete-banner" id="ecl-complete"><?php echo $is_sweet ? 'All done — you crushed it! 🎉' : 'Checklist complete.'; ?></div>
        </div>
        <div class="toolbar no-print">
            <button type="button" class="btn btn-primary" id="ecl-add-btn"><?php echo $is_sweet ? '+ Task' : '+ Task'; ?></button>
            <button type="button" class="btn btn-secondary" id="ecl-uncheck-btn"><?php echo $is_sweet ? 'Uncheck list' : 'Uncheck list'; ?></button>
            <button type="button" class="btn btn-secondary" id="ecl-print-btn"><?php echo $is_sweet ? '🖨️ Print' : 'Print'; ?></button>
        </div>
        <div class="filters no-print" id="ecl-assign-filters" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
            <button type="button" class="filter-chip active" data-assign-filter="all" style="border:none;border-radius:999px;padding:8px 14px;font-size:0.9rem;cursor:pointer;background:white;box-shadow:0 2px 8px rgba(0,0,0,0.06);"><?php echo $is_sweet ? 'All tasks' : 'All'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="mine" style="border:none;border-radius:999px;padding:8px 14px;font-size:0.9rem;cursor:pointer;background:white;box-shadow:0 2px 8px rgba(0,0,0,0.06);"><?php echo $is_sweet ? 'Mine' : 'Mine'; ?></button>
            <button type="button" class="filter-chip" data-assign-filter="unassigned" style="border:none;border-radius:999px;padding:8px 14px;font-size:0.9rem;cursor:pointer;background:white;box-shadow:0 2px 8px rgba(0,0,0,0.06);"><?php echo $is_sweet ? 'Unassigned' : 'Unassigned'; ?></button>
        </div>
        <style>
            #ecl-assign-filters .filter-chip.active {
                <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?>
            }
            .assign-badge {
                display: inline-block; margin-top: 4px; font-size: 0.78rem; border-radius: 999px; padding: 2px 8px;
                <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?>
            }
            .assign-badge.mine { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        </style>
        <div class="print-only" id="ecl-print-header" style="margin-bottom:8px;font-weight:600;"></div>
        <div class="screen-list" id="ecl-list-root"></div>
        <div class="print-all-block" id="ecl-print-all"></div>
        <?php if ($notes_enabled): ?>
        <div class="notes-card no-print" id="ecl-handoff-card">
            <h2><?php echo $is_sweet ? 'Shift Handoff Notes' : 'Shift Handoff Notes'; ?></h2>
            <p class="hint-sm"><?php echo $is_sweet
                ? 'Posts here land in Messages → Shift Notes for the next FOH crew 💌'
                : 'Posts save to Shift Notes (Messages) so the next FOH crew can read them.'; ?></p>
            <form id="ecl-handoff-form">
                <div class="field-row">
                    <div class="field">
                        <label for="ecl-h-shift"><?php echo $is_sweet ? 'Shift' : 'Shift'; ?></label>
                        <select id="ecl-h-shift">
                            <option value="AM">AM</option>
                            <option value="PM" selected>PM</option>
                            <option value="Close">Close</option>
                            <option value="All day"><?php echo $is_sweet ? 'All day' : 'All day'; ?></option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="ecl-h-author-sel"><?php echo $is_sweet ? 'From' : 'From'; ?></label>
                        <select id="ecl-h-author-sel"></select>
                        <input type="text" id="ecl-h-author" style="display:none;margin-top:8px;" placeholder="<?php echo $is_sweet ? 'Type a name…' : 'Type a name…'; ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="ecl-h-title"><?php echo $is_sweet ? 'Headline (optional)' : 'Headline (optional)'; ?></label>
                    <input type="text" id="ecl-h-title" maxlength="120" placeholder="<?php echo $is_sweet ? 'e.g. Busy patio · 86 jam' : 'Short headline'; ?>">
                </div>
                <div class="field">
                    <label for="ecl-h-body"><?php echo $is_sweet ? 'What should the next shift know?' : 'Handoff notes'; ?></label>
                    <textarea id="ecl-h-body" required placeholder="<?php echo $is_sweet ? 'VIPs, allergies, 86s, printer drama, section notes…' : 'VIPs, 86s, equipment, section notes…'; ?>"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="ecl-h-submit"><?php echo $is_sweet ? 'Post handoff 📝' : 'Post handoff'; ?></button>
                    <a href="/messages/shift-notes" class="btn btn-secondary"><?php echo $is_sweet ? 'Full Shift Notes' : 'Full Shift Notes'; ?></a>
                </div>
            </form>
            <div class="notes-status" id="ecl-notes-status"></div>
            <div class="handoff-feed-head">
                <h3><?php echo $is_sweet ? 'Recent FOH handoffs' : 'Recent FOH handoffs'; ?></h3>
            </div>
            <div id="ecl-handoff-feed"></div>
        </div>
        <?php endif; ?>
        <p class="hint-sm no-print"><?php echo $is_sweet
            ? 'Tip: Edit tasks to match your house. Tap 📷 on a task to snap a phone photo when it’s done — management can get a ping when a whole list finishes 💕'
            : 'Tip: Edit tasks to match your house. Use 📷 on a task to add a phone photo. Management can be notified when a list is fully complete.'; ?></p>
        <div class="actions no-print">
            <button type="button" class="btn btn-secondary" id="ecl-restore-btn"><?php echo $is_sweet ? 'Restore starters' : 'Restore starters'; ?></button>
            <a href="<?php echo htmlspecialchars($hub_href); ?>" class="btn btn-primary"><?php echo htmlspecialchars($hub_label); ?></a>
        </div>
    </div>
    <div class="modal-backdrop" id="ecl-modal">
        <div class="modal">
            <h2 id="ecl-modal-title"><?php echo $is_sweet ? 'Task' : 'Task'; ?></h2>
            <form id="ecl-form">
                <div class="field">
                    <label for="ecl-m-label"><?php echo $is_sweet ? 'Task' : 'Task'; ?></label>
                    <input id="ecl-m-label" required maxlength="160" placeholder="<?php echo $is_sweet ? 'e.g. Polish water goblets' : 'e.g. Polish water goblets'; ?>">
                </div>
                <div class="field">
                    <label for="ecl-m-assign"><?php echo $is_sweet ? 'Assign to (optional)' : 'Assign to (optional)'; ?></label>
                    <input id="ecl-m-assign" list="ecl-assign-list" maxlength="80" placeholder="<?php echo $is_sweet ? 'Name or role…' : 'Name or role…'; ?>">
                    <datalist id="ecl-assign-list"></datalist>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="ecl-m-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="toast" id="ecl-toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
