<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$user_label = trim((string) ($_SESSION['name'] ?? $_SESSION['username'] ?? 'Manager'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Schedules & Shifts' : 'Schedules & Shifts'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.4rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .week-nav { display: flex; gap: 10px; align-items: center; margin-bottom: 14px; }
        .week-nav .label { flex: 1; text-align: center; font-size: 1.1rem; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 100px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .day-block { margin-bottom: 16px; }
        .day-title { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-size: 1.15rem; margin: 0 0 8px; }
        .shift { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; padding: 12px 14px; border-radius: 12px; margin-bottom: 8px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .shift-main { flex: 1; min-width: 160px; }
        .shift-name { font-size: 1.08rem; }
        .shift-meta { font-size: 0.9rem; opacity: 0.75; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.78rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; margin-left: 6px; vertical-align: middle; }
        .empty { opacity: 0.7; font-size: 0.95rem; padding: 8px 0; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
        .stats-row.four { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 640px) { .stats-row.four { grid-template-columns: 1fr 1fr; } }
        .stat { background: white; border-radius: 16px; padding: 14px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.4rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .num.warn { color: #C62828; }
        .stat .num.ok { color: #1F6B4A; }
        .stat .lbl { font-size: 0.85rem; opacity: 0.7; }
        .budget-card .field-row { margin-bottom: 4px; }
        .ot-list { margin: 8px 0 0; padding: 0; list-style: none; }
        .ot-list li {
            padding: 8px 10px; margin-bottom: 6px; border-radius: 10px; font-size: 0.92rem;
            background: #FFEBEE; color: #B71C1C; border: 1px solid #EF9A9A;
        }
        .role-budget-row {
            display: grid; grid-template-columns: 1.2fr 0.7fr 0.7fr 0.7fr; gap: 6px; align-items: center;
            padding: 8px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; font-size: 0.9rem;
        }
        .role-budget-row:last-child { border-bottom: none; }
        .role-budget-row .rb-over { color: #C62828; font-weight: 600; }
        @media (max-width: 520px) {
            .role-budget-row { grid-template-columns: 1fr 1fr; }
            .role-budget-row .rb-name { grid-column: 1 / -1; }
        }
        .team-empty { <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; line-height: 1.4; font-size: 0.95rem; }
        .team-empty a { color: inherit; font-weight: 600; }
        .role-hint { font-size: 0.88rem; opacity: 0.65; margin-top: 4px; }
        select:disabled { opacity: 0.55; }
        .print-bar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .print-bar .btn { flex: 1; min-width: 120px; }
        .check-row { display: flex; flex-wrap: wrap; gap: 12px 18px; margin: 8px 0 4px; }
        .check-row label { display: flex; align-items: center; gap: 6px; font-size: 0.95rem; cursor: pointer; }
        .check-row input { width: auto; }
        .alert-status { font-size: 0.9rem; opacity: 0.75; margin: 8px 0 0; line-height: 1.4; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { <?php if ($is_sweet): ?>background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A;<?php else: ?>background: #FFF8E8; color: #5C4B1A; border-color: #E0D2A0;<?php endif; ?> }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulseDot 1s infinite; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        @keyframes pulseDot { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }
        .posted-banner { border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; line-height: 1.4; font-size: 0.95rem; }
        .posted-banner.is-posted { <?php if ($is_sweet): ?>background: #E8F8F1; border: 1px solid #8FD4B2; color: #1F6B4A;<?php else: ?>background: #E8F5EE; border: 1px solid #8FCBB0; color: #1F6B4A;<?php endif; ?> }
        .posted-banner.is-draft { <?php if ($is_sweet): ?>background: #FFF8E8; border: 1px solid #E8D59A;<?php else: ?>background: #FFF8E8; border: 1px solid #E0D2A0;<?php endif; ?> }
        .req-row { padding: 12px 14px; border-radius: 12px; margin-bottom: 8px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .req-row .req-meta { font-size: 0.88rem; opacity: 0.75; margin-top: 4px; }
        .req-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 0; }
        .toggle-row input { width: auto; transform: scale(1.2); }
        .shift.is-open { <?php if ($is_sweet): ?>border-color: #E6A817; background: #FFFBF0;<?php else: ?>border-color: #E6A817; background: #FFF9EC;<?php endif; ?> }
        .badge-open { background: #FFE082; color: #6D4C00; }
        .badge-pending { background: #FFF3CD; color: #8A6D1F; }
        @media print {
            .no-print, .back-link, .bottom-nav, #bottom-nav, nav, .toast, .week-nav, .print-bar, .sync-pill, .posted-banner, #trade-settings-card, #approval-queue-card,
            .actions-bar, #shift-form, .team-empty, .stats-row { display: none !important; }
            body { padding-bottom: 0; background: white; }
            .card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="header no-print">
        <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
        <h1><?php echo $is_sweet ? 'Schedules & Shifts' : 'Schedules & Shifts'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Weekly coverage, alerts & star of the house' : 'Weekly schedules, alerts, and employee spotlight'; ?></p>
    </div>
    <div class="content">
        <div class="card no-print" data-perm-any="admin.team.spotlight.view,admin.team.spotlight.manage">
            <h2><?php echo $is_sweet ? '⭐ Star of the house' : '⭐ Employee spotlight'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Pick employee of the week / month / quarter / custom — posts to Team Announcements and shows on the home banner 💕'
                : 'Select employee of the week, month, quarter, or custom. Posts to announcements and the home banner.'; ?></p>
            <a href="/admin/spotlight" class="btn btn-primary" style="width:100%;box-sizing:border-box;" data-perm-any="admin.team.spotlight.view,admin.team.spotlight.manage"><?php echo $is_sweet ? 'Open Star of the house' : 'Open employee spotlight'; ?></a>
        </div>
        <div class="week-nav no-print">
            <button type="button" class="btn btn-secondary" id="prev-week">←</button>
            <div class="label" id="week-label">This week</div>
            <button type="button" class="btn btn-secondary" id="next-week">→</button>
        </div>
        <div class="sync-pill no-print" id="sched-sync-pill"><span class="dot"></span><span class="sync-text">This device</span></div>
        <div class="posted-banner is-draft no-print" id="posted-banner">Draft week — staff can trade after you post.</div>
        <div class="actions-bar no-print" id="post-week-bar" style="margin-bottom:14px;">
            <button type="button" class="btn btn-primary" id="post-week-btn" data-perm="admin.schedules.add_shift"><?php echo $is_sweet ? '📣 Post this week' : 'Post this week'; ?></button>
            <a href="/schedule" class="btn btn-secondary" data-perm-any="admin.schedules.view,admin.schedules.trade"><?php echo $is_sweet ? '👀 My Schedule' : 'My Schedule'; ?></a>
        </div>
        <div class="stats-row four no-print" id="sched-stats-row">
            <div class="stat"><div class="num" id="stat-shifts">0</div><div class="lbl"><?php echo $is_sweet ? 'Shifts this week' : 'Shifts this week'; ?></div></div>
            <div class="stat"><div class="num" id="stat-hours">0</div><div class="lbl"><?php echo $is_sweet ? 'Scheduled hours' : 'Scheduled hours'; ?></div></div>
            <div class="stat wage-only" hidden data-perm="admin.schedules.view_wages"><div class="num" id="stat-labor-dol">—</div><div class="lbl"><?php echo $is_sweet ? 'Projected labor $' : 'Projected labor $'; ?></div></div>
            <div class="stat wage-only" hidden data-perm="admin.schedules.view_wages"><div class="num" id="stat-labor-pct">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor % vs forecast' : 'Labor % vs forecast'; ?></div></div>
        </div>
        <div class="card no-print" id="sched-vs-actual-card">
            <h2><?php echo $is_sweet ? 'Schedule vs actual (this week)' : 'Schedule vs actual'; ?></h2>
            <p class="hint" id="sched-vs-actual-hint" style="margin-top:-4px;"><?php echo $is_sweet
                ? 'Compares this week’s board to Labor Snapshot / POS punches. Open Labor for day-level detail 💕'
                : 'Scheduled hours/$ vs Labor Snapshot actuals. Open Labor for detail.'; ?></p>
            <div class="stats-row four" style="margin-bottom:8px;">
                <div class="stat"><div class="num" id="sva-sched-h">—</div><div class="lbl"><?php echo $is_sweet ? 'Scheduled h' : 'Scheduled h'; ?></div></div>
                <div class="stat"><div class="num" id="sva-act-h">—</div><div class="lbl"><?php echo $is_sweet ? 'Actual h' : 'Actual h'; ?></div></div>
                <div class="stat"><div class="num" id="sva-var-h">—</div><div class="lbl"><?php echo $is_sweet ? 'Hours var' : 'Hours var'; ?></div></div>
                <div class="stat wage-only" hidden data-perm="admin.schedules.view_wages"><div class="num" id="sva-var-dol">—</div><div class="lbl"><?php echo $is_sweet ? '$ var' : '$ var'; ?></div></div>
            </div>
            <a href="/admin/labor" class="btn btn-ghost btn-small"><?php echo $is_sweet ? 'Open Labor Snapshot' : 'Open Labor'; ?></a>
        </div>

        <div class="card no-print budget-card wage-only" id="labor-budget-card" hidden data-perm="admin.schedules.view_wages">
            <h2><?php echo $is_sweet ? 'Labor budget (while you schedule)' : 'Labor budget'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Wages pull from <a href="/admin/roster">Team roster</a> ($/hr per role). Set forecasted sales + optional hour caps by position. Overtime warns when anyone goes over 40 hrs this week ⏱️'
                : 'Uses roster $/hr. Forecast sales for labor %. Position hour budgets optional. OT warns over 40 hrs/person.'; ?></p>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Forecasted sales this week ($)' : 'Forecasted sales ($)'; ?></label>
                    <input type="number" id="budget-sales" min="0" step="0.01" placeholder="<?php echo $is_sweet ? 'e.g. 18000' : 'e.g. 18000'; ?>">
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Target labor %' : 'Target labor %'; ?></label>
                    <input type="number" id="budget-target-pct" min="0" max="100" step="0.1" value="30" placeholder="30">
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'OT threshold (hrs/person)' : 'OT threshold (hrs)'; ?></label>
                    <input type="number" id="budget-ot-hrs" min="1" max="80" step="0.5" value="40">
                </div>
            </div>
            <p class="hint" id="budget-summary" style="margin-top:4px;"></p>
            <div id="role-budgets"></div>
            <ul class="ot-list" id="ot-warnings" style="display:none;"></ul>
            <div class="actions-bar" style="margin-top:10px;">
                <button type="button" class="btn btn-ghost btn-small" id="budget-save"><?php echo $is_sweet ? 'Save budget prefs 💾' : 'Save budget prefs'; ?></button>
            </div>
        </div>
        <div class="print-bar no-print">
            <button type="button" class="btn btn-secondary" id="print-week-btn" data-perm="admin.schedules.print"><?php echo $is_sweet ? '🖨️ Print week' : 'Print week'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-today-btn" data-perm="admin.schedules.print"><?php echo $is_sweet ? '🖨️ Print today' : 'Print today'; ?></button>
            <button type="button" class="btn btn-secondary" id="download-week-btn" data-perm="admin.schedules.print"><?php echo $is_sweet ? '⬇️ Save week (CSV)' : 'Save week (CSV)'; ?></button>
            <button type="button" class="btn btn-ghost" id="download-html-btn" data-perm="admin.schedules.print"><?php echo $is_sweet ? '⬇️ Save as HTML' : 'Save as HTML'; ?></button>
        </div>
        <div class="card no-print">
            <h2><?php echo $is_sweet ? 'Shift text & app alerts' : 'Shift alerts'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Ping the crew before clock-in: in the app, browser notifications, and optional texts if their phone is on Team & Roles and SMS is set up 🔔'
                : 'Remind staff before shifts via in-app banner, browser notifications, and optional SMS when a phone is on the roster.'; ?></p>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Alert before shift' : 'Alert before shift'; ?></label>
                    <select id="alert-before">
                        <option value="off"><?php echo $is_sweet ? 'Off' : 'Off'; ?></option>
                        <option value="15"><?php echo $is_sweet ? '15 min before' : '15 min before'; ?></option>
                        <option value="30" selected><?php echo $is_sweet ? '30 min before' : '30 min before'; ?></option>
                        <option value="60"><?php echo $is_sweet ? '1 hour before' : '1 hour before'; ?></option>
                        <option value="120"><?php echo $is_sweet ? '2 hours before' : '2 hours before'; ?></option>
                        <option value="180"><?php echo $is_sweet ? '3 hours before' : '3 hours before'; ?></option>
                        <option value="custom"><?php echo $is_sweet ? 'Custom…' : 'Custom…'; ?></option>
                    </select>
                </div>
                <div class="field" id="alert-custom-wrap" style="display:none;">
                    <label><?php echo $is_sweet ? 'Custom minutes before' : 'Custom minutes before'; ?></label>
                    <input id="alert-custom" type="number" min="1" max="720" step="1" placeholder="45" value="45">
                </div>
            </div>
            <div class="check-row">
                <label><input type="checkbox" id="alert-inapp" checked> <?php echo $is_sweet ? 'In-app banner' : 'In-app banner'; ?></label>
                <label><input type="checkbox" id="alert-browser" checked> <?php echo $is_sweet ? 'Browser notification' : 'Browser notification'; ?></label>
                <label><input type="checkbox" id="alert-sms"> <?php echo $is_sweet ? 'Text teammate (SMS)' : 'Text teammate (SMS)'; ?></label>
            </div>
            <p class="alert-status" id="alert-status"></p>
            <button type="button" class="btn btn-primary" style="width:100%;margin-top:10px;" id="save-alerts-btn" data-perm="admin.schedules.add_shift"><?php echo $is_sweet ? 'Save alert settings ✨' : 'Save alert settings'; ?></button>
        </div>
        <div class="card no-print" id="trade-settings-card" data-perm="admin.schedules.manage_settings">
            <h2><?php echo $is_sweet ? 'Trade settings' : 'Trade settings'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'After a week is posted, the crew can give up, swap, or claim shifts. Keep manager approval on if you want to sign off first 💕'
                : 'After a week is posted, staff can give up, swap, or claim. Approval on = you sign off first.'; ?></p>
            <div class="toggle-row">
                <label for="require-approval"><?php echo $is_sweet ? 'Require management approval' : 'Require management approval'; ?></label>
                <input type="checkbox" id="require-approval" checked>
            </div>
        </div>
        <div class="card no-print" id="approval-queue-card" data-perm="admin.schedules.approve">
            <h2><?php echo $is_sweet ? 'Approval queue' : 'Approval queue'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Pending give-ups, swaps, and claims. Approving updates the week board so labor stays in sync.'
                : 'Approve or deny pending trades. Approvals update the week board (and labor).'; ?></p>
            <div id="approval-queue"><div class="empty"><?php echo $is_sweet ? 'Nothing waiting ✨' : 'No pending requests.'; ?></div></div>
        </div>
        <div class="card no-print">
            <h2><?php echo $is_sweet ? 'Add shift' : 'Add shift'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Names come from Team & Roles. If someone has multiple roles, pick which hat they wear this shift 💕'
                : 'Names come from Team & Roles. If someone has multiple roles, choose the role for this shift.'; ?></p>
            <div class="team-empty" id="team-empty" style="display:none;">
                <?php echo $is_sweet
                    ? 'No active teammates yet — add your crew in <a href="/admin/roster">Team & Roles</a> first 💕'
                    : 'No active teammates yet. Add staff in <a href="/admin/roster">Team & Roles</a> first.'; ?>
            </div>
            <form id="shift-form">
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Day' : 'Day'; ?></label>
                        <select id="f-day"><?php foreach ($days as $d): ?><option value="<?php echo $d; ?>"><?php echo $d; ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="field"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label>
                        <select id="f-person" required>
                            <option value=""><?php echo $is_sweet ? 'Select teammate…' : 'Select teammate…'; ?></option>
                        </select>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Role for this shift' : 'Role for this shift'; ?></label>
                        <select id="f-role" required>
                            <option value=""><?php echo $is_sweet ? 'Pick a person first…' : 'Select person first…'; ?></option>
                        </select>
                        <div class="role-hint" id="role-hint"></div>
                    </div>
                    <div class="field"><label><?php echo $is_sweet ? 'Start' : 'Start'; ?></label><input id="f-start" type="time" value="10:00"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'End' : 'End'; ?></label><input id="f-end" type="time" value="16:00"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Section A, closer, training…' : 'Section, closer, training…'; ?>"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;" id="add-shift-btn" data-perm="admin.schedules.add_shift"><?php echo $is_sweet ? 'Add shift ✨' : 'Add shift'; ?></button>
            </form>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'This week\'s board' : 'Week board'; ?></h2>
            <div id="board"></div>
        </div>
        <div class="actions-bar no-print">
            <button type="button" class="btn btn-secondary" id="clear-week" data-perm="admin.schedules.clear_week"><?php echo $is_sweet ? 'Clear this week' : 'Clear this week'; ?></button>
            <a href="/admin/roster" class="btn btn-secondary"><?php echo $is_sweet ? '👥 Team' : 'Team'; ?></a>
            <a href="/admin" class="btn btn-primary"><?php echo pbj_back_to_hub('admin'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/ops-nudges.js?v=3"></script>
    <script src="/schedule-shared.js?v=1"></script>
    <script>
    (function () {
        const KEY = 'pbj_admin_schedules_v1';
        const TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
        const days = <?php echo json_encode($days); ?>;
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        const userLabel = <?php echo json_encode($user_label); ?>;
        var schedSync = null;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            /** Wages / labor $ — fail CLOSED until perms load (never flash $ to hourly staff). */
            function canSeeWages() {
                if (!window.PbjPerms || !window.PbjPerms.loaded) return false;
                // Owner always; otherwise explicit grant (defaults: owner/gm/manager via admin.schedules.*)
                if (window.PbjPerms.role === 'owner' || window.PbjPerms.role === 'gm' || window.PbjPerms.role === 'admin') {
                    return true;
                }
                return window.PbjPerms.can('admin.schedules.view_wages');
            }
            function applyWageVisibility() {
                var ok = canSeeWages();
                document.querySelectorAll('.wage-only').forEach(function (el) {
                    if (ok) {
                        el.hidden = false;
                        el.style.display = '';
                        el.removeAttribute('hidden');
                    } else {
                        el.hidden = true;
                        el.setAttribute('hidden', 'hidden');
                        // keep out of layout even if data-perm later unhides wrongly
                        if (el.classList.contains('budget-card') || el.classList.contains('stat')) {
                            el.style.display = 'none';
                        }
                    }
                });
                var row = document.getElementById('sched-stats-row');
                if (row) {
                    row.classList.toggle('four', ok);
                    if (!ok) row.style.gridTemplateColumns = '1fr 1fr';
                    else row.style.gridTemplateColumns = '';
                }
            }
            function applySchedPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                applyWageVisibility();
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
                try { if (typeof paintTradeUi === 'function') paintTradeUi(); } catch (e) {}
            }


        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        function mondayOf(d) {
            var x = new Date(d); x.setHours(12,0,0,0);
            var day = x.getDay(); var diff = day === 0 ? -6 : 1 - day;
            x.setDate(x.getDate() + diff); return x;
        }
        function weekKey(d) {
            var m = mondayOf(d);
            return m.getFullYear() + '-' + String(m.getMonth()+1).padStart(2,'0') + '-' + String(m.getDate()).padStart(2,'0');
        }
        function defaultAlerts() {
            return {
                alertBefore: '30',
                alertCustomMinutes: 45,
                channels: { inApp: true, browser: true, sms: false }
            };
        }
        function load() {
            if (window.PbjSchedules) return window.PbjSchedules.loadLocal();
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || typeof r.weeks !== 'object') return { weeks: {}, alerts: defaultAlerts() };
                if (!r.alerts || typeof r.alerts !== 'object') r.alerts = defaultAlerts();
                if (!r.alerts.channels) r.alerts.channels = { inApp: true, browser: true, sms: false };
                if (!r.posted) r.posted = {};
                if (!r.settings) r.settings = { requireApproval: true };
                if (!Array.isArray(r.requests)) r.requests = [];
                return r;
            } catch (e) { return { weeks: {}, alerts: defaultAlerts(), posted: {}, settings: { requireApproval: true }, requests: [] }; }
        }
        function save(t) {
            if (window.PbjSchedules) {
                state = window.PbjSchedules.saveLocal(state);
                if (schedSync) schedSync.push(state);
            } else {
                localStorage.setItem(KEY, JSON.stringify(state));
            }
            if (t) { var el = document.getElementById('toast'); el.classList.add('show'); setTimeout(function () { el.classList.remove('show'); }, 1100); }
            try { paintTradeUi(); } catch (e) {}
        }
        function alertMinutesValue() {
            var a = state.alerts || defaultAlerts();
            if (!a.alertBefore || a.alertBefore === 'off') return null;
            if (a.alertBefore === 'custom') {
                var c = parseInt(a.alertCustomMinutes, 10);
                return isNaN(c) || c < 1 ? null : c;
            }
            var n = parseInt(a.alertBefore, 10);
            return isNaN(n) || n < 1 ? null : n;
        }
        function paintAlertForm() {
            var a = state.alerts || defaultAlerts();
            document.getElementById('alert-before').value = a.alertBefore || 'off';
            document.getElementById('alert-custom').value = a.alertCustomMinutes || 45;
            document.getElementById('alert-custom-wrap').style.display =
                (a.alertBefore === 'custom') ? '' : 'none';
            var ch = a.channels || {};
            document.getElementById('alert-inapp').checked = ch.inApp !== false;
            document.getElementById('alert-browser').checked = ch.browser !== false;
            document.getElementById('alert-sms').checked = !!ch.sms;
            var mins = alertMinutesValue();
            var status = document.getElementById('alert-status');
            if (mins == null) {
                status.textContent = isSweet ? 'Alerts are off for now.' : 'Alerts are off.';
            } else {
                var bits = [];
                if (ch.inApp !== false) bits.push(isSweet ? 'in-app' : 'in-app');
                if (ch.browser !== false) bits.push(isSweet ? 'browser' : 'browser');
                if (ch.sms) bits.push(isSweet ? 'SMS (needs phone on roster)' : 'SMS (phone on roster)');
                status.textContent = isSweet
                    ? ('Reminding ' + mins + ' min before start via ' + (bits.join(', ') || 'nothing selected') + '. Keep the app open (or installed) so alerts can fire 💕')
                    : ('Remind ' + mins + ' min before via ' + (bits.join(', ') || 'no channels') + '. App must be open/installed for alerts to fire.');
            }
        }
        function dayDateForWeek(mon, dayName) {
            var idx = days.indexOf(dayName);
            if (idx < 0) return null;
            var d = new Date(mon);
            d.setDate(d.getDate() + idx);
            return d;
        }
        function dateStr(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function openPrintWindow(title, bodyHtml) {
            var w = window.open('', '_blank');
            if (!w) {
                alert(isSweet ? 'Allow pop-ups to print 💕' : 'Allow pop-ups to print.');
                return;
            }
            var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + esc(title) + '</title>' +
                '<style>body{font-family:Georgia,serif;padding:20px;max-width:900px;margin:0 auto;color:#222;}' +
                'h1{font-size:18pt;margin:0 0 6px;} .sub{opacity:.7;margin:0 0 16px;font-size:11pt;}' +
                'h2{font-size:13pt;margin:18px 0 8px;border-bottom:1px solid #ccc;padding-bottom:4px;}' +
                'table{width:100%;border-collapse:collapse;font-size:10.5pt;margin-bottom:8px;}' +
                'th,td{border-bottom:1px solid #ddd;padding:6px 8px;text-align:left;vertical-align:top;}' +
                'th{font-size:9.5pt;opacity:.75;} .empty{opacity:.6;font-style:italic;padding:6px 0;}' +
                '.meta{font-size:9.5pt;opacity:.65;margin-top:20px;} @media print{body{padding:8px;}}</style></head><body>' +
                bodyHtml +
                '<p class="meta">ilovepbj ops · ' + esc(new Date().toLocaleString()) + '</p>' +
                '</body></html>';
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(function () { w.print(); }, 250);
        }
        function buildWeekPrintHtml(opts) {
            opts = opts || {};
            var mon = opts.monday || currentMonday();
            var key = weekKey(mon);
            if (!state.weeks[key]) state.weeks[key] = [];
            var shifts = state.weeks[key] || [];
            var end = new Date(mon); end.setDate(end.getDate() + 6);
            var onlyDay = opts.onlyDay || null;
            var title = onlyDay
                ? (isSweet ? 'Daily schedule' : 'Daily schedule')
                : (isSweet ? 'Weekly schedule' : 'Weekly schedule');
            var range = onlyDay
                ? (function () {
                    var d = dayDateForWeek(mon, onlyDay);
                    return d ? d.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }) : onlyDay;
                })()
                : (mon.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) + ' – ' +
                    end.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }));
            var html = '<h1>' + esc(title) + '</h1><p class="sub">' + esc(range) + '</p>';
            var dayList = onlyDay ? [onlyDay] : days;
            dayList.forEach(function (day) {
                var dayShifts = shifts.filter(function (s) { return s.day === day; })
                    .sort(function (a, b) { return (a.start || '').localeCompare(b.start || ''); });
                var d = dayDateForWeek(mon, day);
                var dayLabel = day + (d ? ' · ' + d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) : '');
                html += '<h2>' + esc(dayLabel) + '</h2>';
                if (!dayShifts.length) {
                    html += '<div class="empty">' + esc(isSweet ? 'No shifts' : 'No shifts') + '</div>';
                    return;
                }
                html += '<table><tr><th>Name</th><th>Role</th><th>Start</th><th>End</th><th>Notes</th></tr>';
                dayShifts.forEach(function (s) {
                    html += '<tr><td>' + esc(s.name) + '</td><td>' + esc(s.role || '') + '</td><td>' +
                        esc(fmtTime(s.start)) + '</td><td>' + esc(fmtTime(s.end)) + '</td><td>' + esc(s.notes || '') + '</td></tr>';
                });
                html += '</table>';
            });
            var totalH = 0;
            shifts.filter(function (s) { return !onlyDay || s.day === onlyDay; })
                .forEach(function (s) { totalH += hoursBetween(s.start, s.end); });
            html += '<p class="sub">' + esc((onlyDay ? dayList[0] + ' · ' : '') + shifts.filter(function (s) {
                return !onlyDay || s.day === onlyDay;
            }).length + ' shifts · ' + totalH + ' hrs') + '</p>';
            return { title: title + ' · ' + range, html: html };
        }
        function todayDayName() {
            return days[(new Date().getDay() + 6) % 7]; // Mon=0 … Sun=6
        }
        function downloadCsv() {
            var mon = currentMonday();
            var key = ensureWeek();
            var shifts = (state.weeks[key] || []).slice().sort(function (a, b) {
                var di = days.indexOf(a.day) - days.indexOf(b.day);
                if (di !== 0) return di;
                return (a.start || '').localeCompare(b.start || '');
            });
            var rows = [['Date', 'Day', 'Name', 'Role', 'Start', 'End', 'Hours', 'Notes']];
            shifts.forEach(function (s) {
                var d = dayDateForWeek(mon, s.day);
                rows.push([
                    d ? dateStr(d) : '',
                    s.day || '',
                    s.name || '',
                    s.role || '',
                    fmtTime(s.start),
                    fmtTime(s.end),
                    hoursBetween(s.start, s.end),
                    s.notes || ''
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
            a.download = 'schedule-' + key + '.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        }
        function downloadHtml() {
            var pack = buildWeekPrintHtml({});
            var blob = new Blob(['<!DOCTYPE html><html><head><meta charset="utf-8"><title>' +
                pack.title.replace(/</g, '') + '</title></head><body>' + pack.html + '</body></html>'],
                { type: 'text/html;charset=utf-8' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'schedule-' + ensureWeek() + '.html';
            a.click();
            URL.revokeObjectURL(a.href);
        }
        function hoursBetween(start, end) {
            if (!start || !end) return 0;
            var a = start.split(':').map(Number), b = end.split(':').map(Number);
            var mins = (b[0]*60+b[1]) - (a[0]*60+a[1]);
            if (mins < 0) mins += 24*60;
            return Math.round((mins / 60) * 10) / 10;
        }
        function fmtTime(t) {
            if (!t) return '';
            var p = t.split(':'); var h = parseInt(p[0],10); var m = p[1]; var ap = h>=12?'PM':'AM'; h = h%12||12;
            return h + ':' + m + ' ' + ap;
        }

        function personRoles(p) {
            var roles = [];
            if (p && Array.isArray(p.roles) && p.roles.length) {
                p.roles.forEach(function (r) {
                    if (r && typeof r === 'object' && r.role) {
                        r = String(r.role || '').trim();
                    } else {
                        r = String(r || '').trim();
                    }
                    if (r && roles.indexOf(r) === -1) roles.push(r);
                });
            }
            if (p && p.role) {
                var single = String(p.role).trim();
                if (single && roles.indexOf(single) === -1) roles.unshift(single);
            }
            if (!roles.length) roles = ['Other'];
            return roles;
        }
        function personRoleWages(p) {
            var wages = {};
            if (p && p.roleWages && typeof p.roleWages === 'object' && !Array.isArray(p.roleWages)) {
                Object.keys(p.roleWages).forEach(function (k) {
                    var n = parseFloat(p.roleWages[k]);
                    if (!isNaN(n) && n >= 0) wages[k] = n;
                });
            }
            if (p && Array.isArray(p.roles)) {
                p.roles.forEach(function (r) {
                    if (r && typeof r === 'object' && r.role) {
                        var n = parseFloat(r.wage);
                        if (!isNaN(n) && n >= 0 && wages[r.role] == null) wages[r.role] = n;
                    }
                });
            }
            if (p && p.wage != null && p.wage !== '' && !isNaN(parseFloat(p.wage))) {
                var roles = personRoles(p);
                if (roles[0] && wages[roles[0]] == null) wages[roles[0]] = parseFloat(p.wage);
            }
            return wages;
        }
        function wageForShift(person, role) {
            if (!person) return null;
            var wages = person.roleWages || {};
            if (role && wages[role] != null && !isNaN(parseFloat(wages[role]))) return parseFloat(wages[role]);
            // fallback: any wage on the person
            var keys = Object.keys(wages);
            for (var i = 0; i < keys.length; i++) {
                if (wages[keys[i]] != null && !isNaN(parseFloat(wages[keys[i]]))) return parseFloat(wages[keys[i]]);
            }
            return null;
        }

        function loadTeam() {
            for (var i = 0; i < TEAM_KEYS.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(TEAM_KEYS[i]) || 'null');
                    if (r && Array.isArray(r.people) && r.people.length) {
                        return r.people
                            .filter(function (p) { return p && p.status !== 'inactive' && (p.name || '').trim(); })
                            .map(function (p) {
                                return {
                                    id: p.id || '',
                                    name: String(p.name || '').trim(),
                                    phone: String(p.phone || '').trim(),
                                    roles: personRoles(p),
                                    roleWages: personRoleWages(p)
                                };
                            })
                            .sort(function (a, b) { return a.name.localeCompare(b.name); });
                    }
                } catch (e) {}
            }
            return [];
        }

        var BUDGET_KEY = 'pbj_sched_labor_budget_v1';
        function defaultBudget() {
            return { salesForecast: '', targetPct: 30, otHrs: 40, roleHours: {} };
        }
        function loadBudget() {
            try {
                var r = JSON.parse(localStorage.getItem(BUDGET_KEY) || 'null');
                if (!r || typeof r !== 'object') return defaultBudget();
                if (!r.roleHours || typeof r.roleHours !== 'object') r.roleHours = {};
                return r;
            } catch (e) { return defaultBudget(); }
        }
        function saveBudgetPrefs(showToast) {
            var b = {
                salesForecast: document.getElementById('budget-sales').value,
                targetPct: document.getElementById('budget-target-pct').value,
                otHrs: document.getElementById('budget-ot-hrs').value,
                roleHours: budget.roleHours || {}
            };
            // collect role budget inputs
            document.querySelectorAll('#role-budgets [data-role-budget]').forEach(function (inp) {
                var role = inp.getAttribute('data-role-budget');
                if (!role) return;
                b.roleHours[role] = inp.value;
            });
            budget = b;
            localStorage.setItem(BUDGET_KEY, JSON.stringify(budget));
            if (showToast) {
                var el = document.getElementById('toast');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
            paintBudget();
        }
        var budget = loadBudget();

        function paintBudgetForm() {
            var salesEl = document.getElementById('budget-sales');
            var pctEl = document.getElementById('budget-target-pct');
            var otEl = document.getElementById('budget-ot-hrs');
            if (salesEl) salesEl.value = budget.salesForecast != null ? budget.salesForecast : '';
            if (pctEl) pctEl.value = budget.targetPct != null && budget.targetPct !== '' ? budget.targetPct : 30;
            if (otEl) otEl.value = budget.otHrs != null && budget.otHrs !== '' ? budget.otHrs : 40;
        }
        function money(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }
        function paintSchedVsActual() {
            var card = document.getElementById('sched-vs-actual-card');
            if (!card || !window.PbjOpsNudges) return;
            var weekDates = [];
            try {
                var m = mondayOf(new Date());
                m.setDate(m.getDate() + (typeof weekOffset === 'number' ? weekOffset : 0) * 7);
                for (var i = 0; i < 7; i++) {
                    var d = new Date(m);
                    d.setDate(m.getDate() + i);
                    weekDates.push(dateStr(d));
                }
            } catch (e) {
                return;
            }
            var schedH = 0, actH = 0, sched$ = 0, act$ = 0, sAny = false, aAny = false;
            weekDates.forEach(function (ds) {
                var one = window.PbjOpsNudges.scheduleVsActual(ds);
                schedH += one.schedHours || 0;
                actH += one.actualHours || 0;
                if (one.schedCost != null) { sched$ += one.schedCost; sAny = true; }
                if (one.actualCost != null) { act$ += one.actualCost; aAny = true; }
            });
            schedH = Math.round(schedH * 100) / 100;
            actH = Math.round(actH * 100) / 100;
            var varH = Math.round((actH - schedH) * 100) / 100;
            var var$ = (sAny || aAny) ? Math.round((act$ - sched$) * 100) / 100 : null;
            var elS = document.getElementById('sva-sched-h');
            var elA = document.getElementById('sva-act-h');
            var elV = document.getElementById('sva-var-h');
            var elD = document.getElementById('sva-var-dol');
            var hint = document.getElementById('sched-vs-actual-hint');
            if (elS) elS.textContent = schedH ? schedH + 'h' : '—';
            if (elA) elA.textContent = actH ? actH + 'h' : '—';
            if (elV) {
                elV.textContent = (schedH || actH) ? ((varH > 0 ? '+' : '') + varH + 'h') : '—';
                elV.className = 'num' + (varH > 1 ? ' warn' : (varH < -1 ? ' ok' : ''));
            }
            if (elD) {
                if (!canSeeWages() || var$ == null) {
                    elD.textContent = '—';
                    elD.className = 'num';
                } else {
                    elD.textContent = (var$ > 0 ? '+' : '') + money(var$);
                    elD.className = 'num' + (var$ > 25 ? ' warn' : (var$ < -25 ? ' ok' : ''));
                }
            }
            if (hint) {
                if (!schedH && !actH) {
                    hint.textContent = isSweet
                        ? 'Add shifts below, then save labor (or sync POS) to compare 💕'
                        : 'Add shifts and labor to compare.';
                } else {
                    hint.textContent = isSweet
                        ? ('This week: scheduled ' + schedH + 'h · actual ' + actH + 'h' +
                            (canSeeWages() && sAny ? ' · sched ' + money(sched$) : '') +
                            (canSeeWages() && aAny ? ' · actual ' + money(act$) : '') +
                            ' · green = under schedule')
                        : ('Sched ' + schedH + 'h vs actual ' + actH + 'h');
                }
            }
        }

        function paintBudget() {
            applyWageVisibility();
            paintSchedVsActual();
            if (!canSeeWages()) {
                // Never leave labor $ in the DOM for hourly eyes
                var laborEl0 = document.getElementById('stat-labor-dol');
                var pctEl0 = document.getElementById('stat-labor-pct');
                if (laborEl0) laborEl0.textContent = '—';
                if (pctEl0) { pctEl0.textContent = '—'; pctEl0.className = 'num'; }
                return;
            }
            var key = ensureWeek();
            var shifts = state.weeks[key] || [];
            var sales = parseFloat(document.getElementById('budget-sales') ? document.getElementById('budget-sales').value : budget.salesForecast);
            var targetPct = parseFloat(document.getElementById('budget-target-pct') ? document.getElementById('budget-target-pct').value : budget.targetPct);
            var otLimit = parseFloat(document.getElementById('budget-ot-hrs') ? document.getElementById('budget-ot-hrs').value : budget.otHrs);
            if (isNaN(otLimit) || otLimit <= 0) otLimit = 40;
            if (isNaN(targetPct)) targetPct = 30;

            var totalLabor = 0, wageHours = 0, missingWageH = 0;
            var byRole = {}; // { role: { hours, labor } }
            var byPerson = {}; // { name: hours }

            shifts.forEach(function (s) {
                var h = hoursBetween(s.start, s.end);
                var person = findPerson(s.personId || s.name) || team.find(function (p) { return p.name === s.name; });
                var rate = wageForShift(person, s.role);
                var role = s.role || 'Other';
                if (!byRole[role]) byRole[role] = { hours: 0, labor: 0, missing: 0 };
                byRole[role].hours += h;
                if (rate != null) {
                    totalLabor += h * rate;
                    wageHours += h;
                    byRole[role].labor += h * rate;
                } else {
                    missingWageH += h;
                    byRole[role].missing += h;
                }
                var pname = s.name || '—';
                byPerson[pname] = (byPerson[pname] || 0) + h;
            });
            totalLabor = Math.round(totalLabor * 100) / 100;

            var laborEl = document.getElementById('stat-labor-dol');
            var pctEl = document.getElementById('stat-labor-pct');
            if (laborEl) {
                laborEl.textContent = shifts.length ? money(totalLabor) : '—';
                if (missingWageH > 0 && laborEl) laborEl.title = (isSweet ? 'Missing wage on ~' : 'Missing wage ~') + missingWageH + 'h';
            }
            var laborPct = (!isNaN(sales) && sales > 0) ? (totalLabor / sales * 100) : null;
            if (pctEl) {
                if (laborPct == null) {
                    pctEl.textContent = '—';
                    pctEl.className = 'num';
                } else {
                    pctEl.textContent = (Math.round(laborPct * 10) / 10) + '%';
                    pctEl.className = 'num' + (laborPct > targetPct ? ' warn' : ' ok');
                }
            }

            var summary = document.getElementById('budget-summary');
            if (summary) {
                var bits = [];
                bits.push(isSweet
                    ? ('Projected labor ' + money(totalLabor) + (missingWageH ? ' (missing wage on ' + missingWageH + 'h)' : ''))
                    : ('Projected labor ' + money(totalLabor) + (missingWageH ? ' (' + missingWageH + 'h without wage)' : '')));
                if (!isNaN(sales) && sales > 0) {
                    var budget$ = sales * (targetPct / 100);
                    var delta = totalLabor - budget$;
                    bits.push(isSweet
                        ? ('Target ' + targetPct + '% of ' + money(sales) + ' = ' + money(budget$) +
                            (delta > 0 ? ' · over by ' + money(delta) : (delta < 0 ? ' · under by ' + money(-delta) : ' · on target')))
                        : ('Target ' + money(budget$) + (delta > 0 ? ' · over ' + money(delta) : delta < 0 ? ' · under ' + money(-delta) : ' · on target')));
                } else {
                    bits.push(isSweet ? 'Enter forecasted sales to see labor %' : 'Enter sales forecast for labor %');
                }
                summary.textContent = bits.join(' · ');
            }

            // Role hour budgets
            var roleRoot = document.getElementById('role-budgets');
            if (roleRoot) {
                var roles = Object.keys(byRole).sort();
                // also show roles that have a budget set but no shifts
                Object.keys(budget.roleHours || {}).forEach(function (r) {
                    if (roles.indexOf(r) === -1 && budget.roleHours[r] !== '' && budget.roleHours[r] != null) roles.push(r);
                });
                if (!roles.length) {
                    roleRoot.innerHTML = '<p class="hint">' + (isSweet ? 'Add shifts to see hours by position.' : 'Add shifts to see hours by position.') + '</p>';
                } else {
                    var html = '<div class="role-budget-row" style="opacity:0.65;font-size:0.78rem;">' +
                        '<span class="rb-name">' + (isSweet ? 'Position' : 'Position') + '</span>' +
                        '<span>' + (isSweet ? 'Scheduled h' : 'Sched h') + '</span>' +
                        '<span>' + (isSweet ? 'Budget h' : 'Budget h') + '</span>' +
                        '<span>' + (isSweet ? 'Labor $' : 'Labor $') + '</span></div>';
                    roles.forEach(function (role) {
                        var row = byRole[role] || { hours: 0, labor: 0 };
                        var cap = budget.roleHours && budget.roleHours[role] != null ? budget.roleHours[role] : '';
                        var capN = parseFloat(cap);
                        var over = !isNaN(capN) && capN >= 0 && row.hours > capN;
                        html += '<div class="role-budget-row">' +
                            '<span class="rb-name' + (over ? ' rb-over' : '') + '">' + esc(role) +
                            (over ? (isSweet ? ' ⚠ over' : ' ⚠ over') : '') + '</span>' +
                            '<span>' + (Math.round(row.hours * 10) / 10) + 'h</span>' +
                            '<span><input type="number" min="0" step="0.5" data-role-budget="' + esc(role) + '" value="' +
                                esc(cap) + '" placeholder="—" style="width:100%;box-sizing:border-box;border-radius:10px;border:2px solid ' +
                                (isSweet ? '#F3C5CC' : '#C5D0DE') + ';padding:6px 8px;font-size:0.9rem;"></span>' +
                            '<span>' + money(row.labor) + '</span></div>';
                    });
                    roleRoot.innerHTML = html;
                }
            }

            // OT warnings
            var otRoot = document.getElementById('ot-warnings');
            if (otRoot) {
                var otPeople = Object.keys(byPerson).filter(function (n) { return byPerson[n] > otLimit; })
                    .sort(function (a, b) { return byPerson[b] - byPerson[a]; });
                if (!otPeople.length) {
                    otRoot.style.display = 'none';
                    otRoot.innerHTML = '';
                } else {
                    otRoot.style.display = 'block';
                    otRoot.innerHTML = otPeople.map(function (n) {
                        var h = Math.round(byPerson[n] * 10) / 10;
                        var ot = Math.round((byPerson[n] - otLimit) * 10) / 10;
                        return '<li>' + esc(n) + ' · ' + h + 'h scheduled · ' +
                            (isSweet ? (ot + 'h over ' + otLimit + 'h OT line') : (ot + 'h over ' + otLimit + 'h')) + '</li>';
                    }).join('');
                }
            }
        }

        var state = load();
        var weekOffset = 0;
        var team = loadTeam();

        function fillPersonSelect() {
            var sel = document.getElementById('f-person');
            var empty = document.getElementById('team-empty');
            var btn = document.getElementById('add-shift-btn');
            var current = sel.value;
            sel.innerHTML = '<option value="">' + (isSweet ? 'Select teammate…' : 'Select teammate…') + '</option>';
            team.forEach(function (p) {
                var label = p.name + (p.roles.length === 1 ? ' · ' + p.roles[0] : ' · ' + p.roles.length + ' roles');
                sel.innerHTML += '<option value="' + esc(p.id || p.name) + '" data-name="' + esc(p.name) + '">' + esc(label) + '</option>';
            });
            if (current) sel.value = current;
            if (!team.length) {
                empty.style.display = 'block';
                sel.disabled = true;
                document.getElementById('f-role').disabled = true;
                btn.disabled = true;
            } else {
                empty.style.display = 'none';
                sel.disabled = false;
                document.getElementById('f-role').disabled = false;
                btn.disabled = false;
            }
            fillRoleSelect();
        }

        function findPerson(value) {
            if (!value) return null;
            return team.find(function (p) { return p.id === value || p.name === value; }) || null;
        }

        function fillRoleSelect() {
            var person = findPerson(document.getElementById('f-person').value);
            var roleSel = document.getElementById('f-role');
            var hint = document.getElementById('role-hint');
            roleSel.innerHTML = '';
            if (!person) {
                roleSel.innerHTML = '<option value="">' + (isSweet ? 'Pick a person first…' : 'Select person first…') + '</option>';
                hint.textContent = '';
                return;
            }
            var roles = person.roles.slice();
            // Ensure known house roles are available if they only have one but manager wants flexibility
            roles.forEach(function (r) {
                roleSel.innerHTML += '<option value="' + esc(r) + '">' + esc(r) + '</option>';
            });
            if (roles.length === 1) {
                roleSel.value = roles[0];
                hint.textContent = isSweet ? 'Pulled from their Team profile' : 'From their team profile';
            } else {
                roleSel.selectedIndex = 0;
                hint.textContent = isSweet
                    ? 'They have ' + roles.length + ' roles — pick which one for this shift'
                    : 'Multiple roles on file — choose one for this shift';
            }
        }

        function currentMonday() {
            var d = mondayOf(new Date());
            d.setDate(d.getDate() + weekOffset * 7);
            return d;
        }
        function ensureWeek() {
            var key = weekKey(currentMonday());
            if (!state.weeks[key]) state.weeks[key] = [];
            return key;
        }

        function render() {
            var mon = currentMonday();
            var key = ensureWeek();
            var shifts = state.weeks[key] || [];
            var end = new Date(mon); end.setDate(end.getDate() + 6);
            document.getElementById('week-label').textContent =
                mon.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) +
                ' – ' +
                end.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });

            var totalH = 0;
            shifts.forEach(function (s) { totalH += hoursBetween(s.start, s.end); });
            document.getElementById('stat-shifts').textContent = shifts.length;
            document.getElementById('stat-hours').textContent = totalH;
            paintBudget();

            var board = document.getElementById('board');
            board.innerHTML = days.map(function (day) {
                var dayShifts = shifts.filter(function (s) { return s.day === day; })
                    .sort(function (a, b) { return (a.start || '').localeCompare(b.start || ''); });
                var body = dayShifts.length
                    ? dayShifts.map(function (s) {
                        var openBadge = s.open ? '<span class="badge badge-open">' + (isSweet ? 'Open' : 'Open') + '</span>' : '';
                        var was = (s.open && s.originalName) ? ' · was ' + s.originalName : '';
                        return '<div class="shift' + (s.open ? ' is-open' : '') + '">' +
                            '<div class="shift-main"><div class="shift-name">' + esc(s.name) +
                            (s.role ? '<span class="badge">' + esc(s.role) + '</span>' : '') +
                            openBadge +
                            '</div>' +
                            '<div class="shift-meta">' + esc(fmtTime(s.start)) + ' – ' + esc(fmtTime(s.end)) +
                            (s.notes ? ' · ' + esc(s.notes) : '') + esc(was) + '</div></div>' +
                            '<button type="button" class="btn btn-small btn-danger" data-need-perm="admin.schedules.add_shift" data-del="' + esc(s.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                            '</div>';
                    }).join('')
                    : '<div class="empty">' + (isSweet ? 'No shifts yet' : 'No shifts yet') + '</div>';
                return '<div class="day-block"><h3 class="day-title">' + day + '</h3>' + body + '</div>';
            }).join('');
        }

        function paintTradeUi() {
            var S = window.PbjSchedules;
            var key = (function () {
                try { return weekKey(currentMonday()); } catch (e) { return ''; }
            })();
            var posted = S ? S.isWeekPosted(state, key) : !!(state.posted && state.posted[key]);
            var banner = document.getElementById('posted-banner');
            var postBtn = document.getElementById('post-week-btn');
            if (banner) {
                banner.className = 'posted-banner no-print ' + (posted ? 'is-posted' : 'is-draft');
                if (posted) {
                    var meta = (state.posted && state.posted[key]) || {};
                    var when = meta.at ? new Date(meta.at).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) : '';
                    banner.textContent = isSweet
                        ? ('Posted' + (meta.by && meta.by !== 'migration' ? ' by ' + meta.by : '') + (when ? ' · ' + when : '') + ' — crew can give up, swap, or claim.')
                        : ('Posted' + (meta.by && meta.by !== 'migration' ? ' · ' + meta.by : '') + (when ? ' · ' + when : '') + '. Staff can trade.');
                } else {
                    banner.textContent = isSweet
                        ? 'Draft week — post it when the board is ready so the crew can trade 💕'
                        : 'Draft week. Post it when ready so staff can trade.';
                }
            }
            if (postBtn) {
                postBtn.textContent = posted
                    ? (isSweet ? '📣 Posted' : 'Posted')
                    : (isSweet ? '📣 Post this week' : 'Post this week');
                postBtn.disabled = !!posted;
            }
            var tog = document.getElementById('require-approval');
            if (tog) {
                var on = !(state.settings && state.settings.requireApproval === false);
                tog.checked = on;
            }
            var q = document.getElementById('approval-queue');
            if (q) {
                var pending = S ? S.pendingRequests(state) : ((state.requests || []).filter(function (r) { return r.status === 'pending_approval'; }));
                if (!pending.length) {
                    q.innerHTML = '<div class="empty">' + (isSweet ? 'Nothing waiting ✨' : 'No pending requests.') + '</div>';
                } else {
                    q.innerHTML = pending.map(function (r) {
                        var shift = S ? S.findShift(state, r.weekKey, r.shiftId) : null;
                        var when = shift ? (shift.day + ' ' + fmtTime(shift.start) + '–' + fmtTime(shift.end)) : r.weekKey;
                        var label = S ? S.requestLabel(r, isSweet) : (r.type + ' · ' + (r.fromName || ''));
                        return '<div class="req-row">' +
                            '<div>' + esc(label) + '</div>' +
                            '<div class="req-meta">' + esc(when) + (r.note ? ' · ' + esc(r.note) : '') + '</div>' +
                            '<div class="req-actions">' +
                            '<button type="button" class="btn btn-primary btn-small" data-approve="' + esc(r.id) + '">' + (isSweet ? 'Approve' : 'Approve') + '</button>' +
                            '<button type="button" class="btn btn-danger btn-small" data-deny="' + esc(r.id) + '">' + (isSweet ? 'Deny' : 'Deny') + '</button>' +
                            '</div></div>';
                    }).join('');
                }
            }
        }

        document.getElementById('prev-week').addEventListener('click', function () { weekOffset--; render(); paintTradeUi(); });
        document.getElementById('next-week').addEventListener('click', function () { weekOffset++; render(); paintTradeUi(); });

        document.getElementById('f-person').addEventListener('change', fillRoleSelect);

        ['budget-sales', 'budget-target-pct', 'budget-ot-hrs'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', function () { paintBudget(); });
            el.addEventListener('change', function () { saveBudgetPrefs(false); });
        });
        var budgetSaveBtn = document.getElementById('budget-save');
        if (budgetSaveBtn) budgetSaveBtn.addEventListener('click', function () { saveBudgetPrefs(true); });
        var roleBudgetsEl = document.getElementById('role-budgets');
        if (roleBudgetsEl) {
            roleBudgetsEl.addEventListener('change', function (e) {
                if (!e.target.matches('[data-role-budget]')) return;
                saveBudgetPrefs(false);
            });
        }
        paintBudgetForm();
        applyWageVisibility(); // hide $ immediately; perms unlock for owner/GM/manager only

        document.getElementById('shift-form').addEventListener('submit', function (e) {
                if (!canP('admin.schedules.add_shift')) { e.preventDefault(); return; }
            e.preventDefault();
            var person = findPerson(document.getElementById('f-person').value);
            var role = document.getElementById('f-role').value.trim();
            if (!person) {
                alert(isSweet ? 'Pick a teammate from the list 💕' : 'Select a teammate from the list.');
                return;
            }
            if (!role) {
                alert(isSweet ? 'Pick a role for this shift' : 'Select a role for this shift.');
                return;
            }
            var key = ensureWeek();
            state.weeks[key].push({
                id: uid(),
                day: document.getElementById('f-day').value,
                personId: person.id || '',
                name: person.name,
                role: role,
                start: document.getElementById('f-start').value,
                end: document.getElementById('f-end').value,
                notes: document.getElementById('f-notes').value.trim()
            });
            save(true);
            document.getElementById('f-person').value = '';
            document.getElementById('f-notes').value = '';
            fillRoleSelect();
            render();
        });

        document.getElementById('board').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-del]'); if (!btn) return;
            var key = ensureWeek();
            state.weeks[key] = (state.weeks[key] || []).filter(function (s) { return s.id !== btn.dataset.del; });
            save(true); render();
        });

        document.getElementById('clear-week').addEventListener('click', function () {
            if (!canP('admin.schedules.clear_week')) return;
            if (!confirm(isSweet ? 'Clear all shifts for this week?' : 'Clear all shifts for this week?')) return;
            var key = ensureWeek();
            state.weeks[key] = [];
            save(true); render();
        });

        document.getElementById('alert-before').addEventListener('change', function () {
            document.getElementById('alert-custom-wrap').style.display =
                this.value === 'custom' ? '' : 'none';
        });
        document.getElementById('save-alerts-btn').addEventListener('click', function () {
            if (!canP('admin.schedules.add_shift')) return;
            var before = document.getElementById('alert-before').value;
            var custom = parseInt(document.getElementById('alert-custom').value, 10);
            if (before === 'custom' && (isNaN(custom) || custom < 1)) {
                alert(isSweet ? 'Enter custom minutes (at least 1)' : 'Enter custom minutes (min 1).');
                return;
            }
            state.alerts = {
                alertBefore: before,
                alertCustomMinutes: isNaN(custom) ? 45 : custom,
                channels: {
                    inApp: document.getElementById('alert-inapp').checked,
                    browser: document.getElementById('alert-browser').checked,
                    sms: document.getElementById('alert-sms').checked
                }
            };
            if (state.alerts.channels.browser && typeof Notification !== 'undefined' &&
                Notification.permission === 'default') {
                try { Notification.requestPermission(); } catch (e) {}
            }
            save(true);
            paintAlertForm();
        });

        document.getElementById('print-week-btn').addEventListener('click', function () {
            if (!canP('admin.schedules.print')) return;
            var pack = buildWeekPrintHtml({});
            openPrintWindow(pack.title, pack.html);
        });
        document.getElementById('print-today-btn').addEventListener('click', function () {
            if (!canP('admin.schedules.print')) return;
            var today = new Date();
            var pack = buildWeekPrintHtml({ monday: mondayOf(today), onlyDay: todayDayName() });
            openPrintWindow(pack.title, pack.html);
        });
        document.getElementById('download-week-btn').addEventListener('click', function () {
            if (!canP('admin.schedules.print')) return;
            downloadCsv();
        });
        document.getElementById('download-html-btn').addEventListener('click', function () {
            if (!canP('admin.schedules.print')) return;
            downloadHtml();
        });

        // Refresh roster when returning to tab / storage updates from Team page
        function refreshTeam() {
            team = loadTeam();
            fillPersonSelect();
        }
        window.addEventListener('focus', refreshTeam);
        window.addEventListener('storage', function (e) {
            if (!e.key || TEAM_KEYS.indexOf(e.key) !== -1) refreshTeam();
        });

        // Soft check whether Twilio SMS is ready (optional)
        fetch('/shift-alert-sms-api.php', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) return;
                var a = state.alerts || defaultAlerts();
                if (a.channels && a.channels.sms && !data.twilio_configured) {
                    var st = document.getElementById('alert-status');
                    if (st) {
                        st.textContent = (st.textContent || '') + (isSweet
                            ? ' SMS texts need Twilio setup (or device SMS won’t auto-send) — phones still need to be on Team & Roles 💕'
                            : ' SMS needs Twilio configured to auto-send; roster phones still required.');
                    }
                }
            }).catch(function () {});

        var postBtn = document.getElementById('post-week-btn');
        if (postBtn) {
            postBtn.addEventListener('click', function () {
                if (!canP('admin.schedules.add_shift')) return;
                var key = ensureWeek();
                if (!(state.weeks[key] || []).length) {
                    alert(isSweet ? 'Add at least one shift before posting 💕' : 'Add at least one shift before posting.');
                    return;
                }
                if (window.PbjSchedules && window.PbjSchedules.isWeekPosted(state, key)) return;
                if (!confirm(isSweet ? 'Post this week? The crew can then give up, swap, or claim.' : 'Post this week so staff can trade?')) return;
                if (window.PbjSchedules) state = window.PbjSchedules.postWeek(state, key, userLabel);
                else {
                    state.posted = state.posted || {};
                    state.posted[key] = { at: Date.now(), by: userLabel };
                }
                save(true);
                render();
            });
        }
        var tog = document.getElementById('require-approval');
        if (tog) {
            tog.addEventListener('change', function () {
                if (!canP('admin.schedules.manage_settings')) {
                    this.checked = !this.checked;
                    return;
                }
                if (window.PbjSchedules) state = window.PbjSchedules.setRequireApproval(state, this.checked);
                else {
                    state.settings = state.settings || {};
                    state.settings.requireApproval = this.checked;
                }
                save(true);
            });
        }
        var qEl = document.getElementById('approval-queue');
        if (qEl) {
            qEl.addEventListener('click', function (e) {
                var okBtn = e.target.closest('[data-approve]');
                var noBtn = e.target.closest('[data-deny]');
                if (!okBtn && !noBtn) return;
                if (!canP('admin.schedules.approve')) return;
                if (!window.PbjSchedules) return;
                var id = (okBtn || noBtn).getAttribute(okBtn ? 'data-approve' : 'data-deny');
                var res = okBtn
                    ? window.PbjSchedules.approveRequest(state, id, userLabel, team)
                    : window.PbjSchedules.denyRequest(state, id, userLabel);
                if (!res.ok) {
                    alert(res.message || (isSweet ? 'Couldn’t update that request' : 'Could not update that request.'));
                    return;
                }
                state = res.state;
                save(true);
                render();
            });
        }

        fillPersonSelect();
        paintAlertForm();
        render();
        paintTradeUi();
        if (window.PbjSchedules) {
            schedSync = window.PbjSchedules.wire({
                getState: function () { return state; },
                setState: function (next) {
                    state = next;
                    paintAlertForm();
                    render();
                    paintTradeUi();
                },
                statusEl: 'sched-sync-pill'
            });
        }
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applySchedPerms);
            document.addEventListener('pbj-perms-ready', applySchedPerms);
    })();
    </script>
</body>
</html>
