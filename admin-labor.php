<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Labor Snapshot' : 'Labor Snapshot'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.5rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 800px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.25rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.82rem; opacity: 0.7; margin-top: 4px; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .card-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; }
        .card-head h2 { margin: 0; }
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
        .week-nav { display: flex; gap: 10px; align-items: center; margin-bottom: 14px; }
        .week-nav .label { flex: 1; text-align: center; font-size: 1.05rem; }
        .day-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .day-chip { border: none; border-radius: 12px; padding: 10px 12px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .day-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .day-chip .sub { display: block; font-size: 0.78rem; opacity: 0.75; margin-top: 2px; }
        .day-chip.active .sub { opacity: 0.9; }
        .shift-card { border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 0 14px 14px 0; padding: 14px 14px 12px; margin-bottom: 12px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .shift-card.adjusted { border-left-color: #2E9B63; }
        .shift-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; margin-bottom: 8px; }
        .shift-name { font-size: 1.1rem; margin: 0 0 2px; }
        .shift-role { font-size: 0.9rem; opacity: 0.7; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.75rem; margin-left: 6px; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .badge.actual { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .badge.pos { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .badge.sched { <?php if ($is_sweet): ?>background: #FFF3E0; color: #8A5A12;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .pos-banner { border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; line-height: 1.4; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC; color: #3a2f1f;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE; color: #1A2A44;<?php endif; ?> }
        .shift-card.pos { border-left-color: #2E9B63; }
        .totals-bar.three { grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 520px) { .totals-bar.three { grid-template-columns: 1fr 1fr; } }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; }
        .sched-line { font-size: 0.9rem; opacity: 0.7; margin-bottom: 8px; }
        .hours-pill { font-size: 0.95rem; font-weight: 600; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .totals-bar { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 12px 0; }
        .total-box { border-radius: 12px; padding: 12px; text-align: center; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .total-box .n { font-size: 1.2rem; }
        .total-box .l { font-size: 0.82rem; opacity: 0.7; margin-top: 2px; }
        .empty { text-align: center; padding: 24px; opacity: 0.8; line-height: 1.4; }
        .entry { padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; }
        .entry-meta { font-size: 0.92rem; opacity: 0.75; margin-top: 4px; line-height: 1.4; }
        .entry-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .warn { color: #C62828; }
        .ok { color: #1F6B4A; }
        .edit-banner {
            border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.95rem; line-height: 1.4;
            <?php if ($is_sweet): ?>background: #E8F8F1; border: 1px solid #B8E6CF; color: #1F6B4A;
            <?php else: ?>background: #EAF1FA; border: 1px solid #C5D0DE; color: #1A2A44;<?php endif; ?>
        }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .form-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
        .form-actions .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .diff { font-size: 0.85rem; opacity: 0.75; }
        .diff.over { color: #C62828; }
        .diff.under { color: #1F6B4A; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports & Sales' : 'Back to Reports & Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'Labor Snapshot' : 'Labor Snapshot'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Schedule · Square · Toast · Clover labor' : 'Schedule + live POS labor'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:0.88rem;margin-bottom:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);<?php if ($is_sweet): ?>background:#FFF5F6;color:#E55163;border:1px solid #F3C5CC;<?php else: ?>background:#EEF2F8;color:#1A2A44;border:1px solid #C5D0DE;<?php endif; ?>">
            <span class="dot" style="width:8px;height:8px;border-radius:50%;background:#2E9B63;"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>
        <div class="edit-banner" id="conflict-banner" style="display:none;margin-bottom:12px;">
            <span id="conflict-banner-text"></span>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                <button type="button" class="btn btn-primary btn-small" id="conflict-keep"><?php echo $is_sweet ? 'Keep mine' : 'Keep mine'; ?></button>
                <button type="button" class="btn btn-ghost btn-small" id="conflict-use-kitchen"><?php echo $is_sweet ? 'Use kitchen' : 'Use kitchen'; ?></button>
            </div>
        </div>
        <div class="intro">
            <?php echo $is_sweet
                ? 'Shifts pull from <a href="/admin/schedules">Schedules</a>, or <a href="/admin/pos-connect">sync POS</a> (Square / Toast / Clover) for real clock punches &amp; labor $ ⏱️'
                : 'Shifts from <a href="/admin/schedules">Schedules</a> or POS labor sync (Square, Toast, Clover).'; ?>
        </div>
        <div class="pos-banner">
            <?php echo $is_sweet
                ? '✍️ <strong>Manual / schedule / live POS</strong> — wages from <a href="/admin/roster" style="color:inherit;font-weight:600;">Team</a>, or <a href="/admin/pos-connect" style="color:inherit;font-weight:600;">connect POS</a> and sync punches (Square timecards, Toast time entries, Clover shifts) with a “From POS” badge.'
                : '<strong>Manual / schedule / live POS.</strong> Sync Square, Toast, or Clover labor into this day log.'; ?>
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
                <button type="button" class="btn btn-primary" id="btn-sync-all-labor"><?php echo $is_sweet ? 'Sync all POS (sales + labor)' : 'Sync all POS'; ?></button>
                <a href="/admin/pos-connect" class="btn btn-secondary"><?php echo $is_sweet ? 'POS connections' : 'POS connections'; ?></a>
            </div>
            <p class="hint" id="labor-sync-status" style="margin:8px 0 0;opacity:0.75;"></p>
        </div>

        <div class="week-nav">
            <button type="button" class="btn btn-secondary" id="prev-week">←</button>
            <div class="label" id="week-label">This week</div>
            <button type="button" class="btn btn-secondary" id="next-week">→</button>
        </div>

        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-hours">—</div><div class="lbl"><?php echo $is_sweet ? 'Actual h (7d)' : 'Actual h (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-labor">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor $ (7d)' : 'Labor $ (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-pct">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor % (7d)' : 'Labor % (7d)'; ?></div></div>
        </div>
        <div class="stats-row" id="variance-stats" style="margin-top:0;">
            <div class="stat"><div class="num" id="stat-var-h">—</div><div class="lbl"><?php echo $is_sweet ? 'Sched vs actual h (7d)' : 'Sched vs actual h (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-var-dol">—</div><div class="lbl"><?php echo $is_sweet ? 'Sched vs actual $ (7d)' : 'Sched vs actual $ (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-sched-h">—</div><div class="lbl"><?php echo $is_sweet ? 'Scheduled h (7d)' : 'Scheduled h (7d)'; ?></div></div>
        </div>
        <p class="hint" id="variance-hint" style="margin:-4px 0 14px;text-align:center;"></p>

        <div class="day-chips" id="day-chips"></div>

        <div class="card">
            <div class="card-head">
                <h2 id="day-title"><?php echo $is_sweet ? 'Today\'s labor' : 'Day labor'; ?></h2>
            </div>
            <div class="field-row">
                <div class="field"><label><?php echo $is_sweet ? 'Data source' : 'Data source'; ?></label>
                    <select id="f-source">
                        <option value="manual"><?php echo $is_sweet ? 'Manual / schedule' : 'Manual / schedule'; ?></option>
                        <option value="pos"><?php echo $is_sweet ? 'From POS (synced)' : 'From POS (synced)'; ?></option>
                    </select>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'POS brand' : 'POS brand'; ?></label>
                    <select id="f-pos-provider">
                        <option value=""><?php echo $is_sweet ? 'None / skip for now' : 'None / skip for now'; ?></option>
                        <option value="toast">Toast</option>
                        <option value="square">Square</option>
                        <option value="clover">Clover</option>
                        <option value="aloha">Aloha</option>
                        <option value="spoton">SpotOn</option>
                        <option value="7shifts">7shifts</option>
                        <option value="homebase">Homebase</option>
                        <option value="other"><?php echo $is_sweet ? 'Other / local system…' : 'Other / local system…'; ?></option>
                    </select>
                    <input type="text" id="f-pos-custom" style="display:none;margin-top:8px;" placeholder="<?php echo $is_sweet ? 'e.g. Aloha, Micros, house POS…' : 'e.g. Aloha, Micros, house POS…'; ?>">
                    <p class="hint" style="margin:4px 0 0;"><?php echo $is_sweet ? 'Totally optional — pick a brand when you care, or type your own weird local one 💕' : 'Optional. Choose a brand or type a custom / local POS name.'; ?></p>
                </div>
            </div>
            <div class="toolbar">
                <button type="button" class="btn btn-secondary btn-small" id="pull-schedule"><?php echo $is_sweet ? '↻ Pull from schedule' : 'Pull from schedule'; ?></button>
                <button type="button" class="btn btn-secondary btn-small" id="add-manual"><?php echo $is_sweet ? '+ Add person' : '+ Add person'; ?></button>
                <button type="button" class="btn btn-secondary btn-small" id="pull-pos" title="<?php echo $is_sweet ? 'Sync all connected POS labor' : 'Sync POS labor'; ?>"><?php echo $is_sweet ? '⬇ Sync POS labor' : 'Sync POS labor'; ?></button>
            </div>
            <p class="hint" id="day-hint"><?php echo $is_sweet ? 'Clock in / out = actual punches (manual or from Square). Optional $/hr builds labor $ 💕' : 'Clock in/out are actual punches (manual or Square). Optional $/hr builds labor $.'; ?></p>
            <div id="entries"></div>

            <div class="totals-bar three">
                <div class="total-box"><div class="n" id="day-sched-h">—</div><div class="l"><?php echo $is_sweet ? 'Scheduled hours' : 'Scheduled hours'; ?></div></div>
                <div class="total-box"><div class="n" id="day-actual-h">—</div><div class="l"><?php echo $is_sweet ? 'Clocked hours' : 'Clocked hours'; ?></div></div>
                <div class="total-box"><div class="n" id="day-wages">—</div><div class="l"><?php echo $is_sweet ? 'Wages (from rates)' : 'Wages (from rates)'; ?></div></div>
            </div>
            <div class="totals-bar three" id="day-variance-bar" style="margin-top:8px;">
                <div class="total-box"><div class="n" id="day-var-h">—</div><div class="l"><?php echo $is_sweet ? 'Hours vs schedule' : 'Hours vs schedule'; ?></div></div>
                <div class="total-box"><div class="n" id="day-var-dol">—</div><div class="l"><?php echo $is_sweet ? '$ vs schedule' : '$ vs schedule'; ?></div></div>
                <div class="total-box"><div class="n" id="day-sched-dol">—</div><div class="l"><?php echo $is_sweet ? 'Scheduled labor $' : 'Scheduled labor $'; ?></div></div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Labor cost for day ($)' : 'Labor cost for day ($)'; ?></label>
                    <input type="number" step="0.01" min="0" id="f-cost" placeholder="0.00">
                    <p class="hint" style="margin:4px 0 0;"><?php echo $is_sweet ? 'Auto-fills from $/hr when you enter rates — or type POS labor $ later' : 'Auto from $/hr rates, or type day total / future POS labor $.'; ?></p>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Day notes' : 'Day notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'OT, call-outs, training…' : 'OT, call-outs, training…'; ?>"></div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="save-day"><?php echo $is_sweet ? 'Save this day ✨' : 'Save this day'; ?></button>
            </div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Saved labor days' : 'Saved labor days'; ?></h2>
            <p class="hint" id="target-hint"></p>
            <div id="list"></div>
        </div>

        <div class="actions-bar">
            <a href="/admin/sales" class="btn btn-secondary"><?php echo $is_sweet ? '📈 Sales' : 'Sales'; ?></a>
            <a href="/admin/cash" class="btn btn-secondary"><?php echo $is_sweet ? '💵 Cash next' : 'Cash next'; ?></a>
            <a href="/admin/schedules" class="btn btn-secondary"><?php echo $is_sweet ? '📅 Schedules' : 'Schedules'; ?></a>
            <a href="/admin/reports" class="btn btn-primary"><?php echo $is_sweet ? 'Reports hub' : 'Reports hub'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/shared-state.js?v=4"></script>
    <script src="/pos-sync-client.js?v=2"></script>
    <script src="/ops-nudges.js?v=2"></script>
    <script>
    (function () {
        const KEY = 'pbj_admin_labor_v2';
        const OLD_KEY = 'pbj_admin_labor_v1';
        const SCHED_KEY = 'pbj_admin_schedules_v1';
        const TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
        const SALES_KEYS = ['pbj_admin_sales_v2', 'pbj_admin_sales_v1'];
        const DAY_NAMES = <?php echo json_encode($days); ?>; // Mon..Sun
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyRepPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var view = canP('admin.reports.view') || canP('admin.reports.edit');
                var edit = canP('admin.reports.edit');
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('rep-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="rep-denied">No permission to view reports.</div>');
                    }
                }
                if (!edit) {
                    document.querySelectorAll('input, select, textarea, button.btn-primary, button.btn-danger').forEach(function(el) {
                        if (el.closest('.bottom-nav') || el.tagName === 'A') return;
                        if (el.id && /back|print|export|tab/i.test(el.id)) return;
                        if (el.classList && el.classList.contains('tab')) return;
                        if (el.type === 'button' && /print|export|tab/i.test(el.textContent||'')) return;
                        // don't disable pure navigation
                        if (el.tagName === 'BUTTON' && el.closest('.actions-bar')) return;
                        if (el.matches('input,select,textarea')) el.disabled = true;
                    });
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }


        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money(n) { if (n == null || isNaN(n)) return '—'; return '$' + (Math.round(n * 100) / 100).toFixed(2); }
        function num(v) {
            if (v === '' || v == null) return null;
            var n = parseFloat(v);
            return isNaN(n) ? null : n;
        }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }
        function mondayOf(d) {
            var x = new Date(d); x.setHours(12,0,0,0);
            var day = x.getDay(); var diff = day === 0 ? -6 : 1 - day;
            x.setDate(x.getDate() + diff); return x;
        }
        function weekKey(d) {
            var m = mondayOf(d);
            return m.getFullYear() + '-' + String(m.getMonth()+1).padStart(2,'0') + '-' + String(m.getDate()).padStart(2,'0');
        }
        function dateFromMondayOffset(mon, offset) {
            var d = new Date(mon); d.setDate(d.getDate() + offset);
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }
        function dayNameForDate(dateStr) {
            var d = new Date(dateStr + 'T12:00:00');
            // JS: 0=Sun..6=Sat → map to Mon..Sun labels used in schedules
            var js = d.getDay();
            var map = { 1: 'Mon', 2: 'Tue', 3: 'Wed', 4: 'Thu', 5: 'Fri', 6: 'Sat', 0: 'Sun' };
            return map[js];
        }
        function hoursBetween(start, end, breakMins) {
            if (!start || !end) return 0;
            var a = start.split(':').map(Number), b = end.split(':').map(Number);
            var mins = (b[0]*60+b[1]) - (a[0]*60+a[1]);
            if (mins < 0) mins += 24*60;
            mins -= (parseInt(breakMins, 10) || 0);
            if (mins < 0) mins = 0;
            return Math.round((mins / 60) * 100) / 100;
        }
        function fmtTime(t) {
            if (!t) return '—';
            var p = t.split(':'); var h = parseInt(p[0],10); var m = p[1]; var ap = h>=12?'PM':'AM'; h = h%12||12;
            return h + ':' + m + ' ' + ap;
        }
        function isFohRole(role) {
            var r = String(role || '').toLowerCase();
            return /server|host|bartender|bar|foh|shift lead|manager|gm|owner/.test(r);
        }
        function isBohRole(role) {
            var r = String(role || '').toLowerCase();
            return /cook|prep|dish|boh|kitchen|line/.test(r);
        }

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = typeof msg === 'string' ? msg : (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1100);
        }

        function loadState() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) {
                    var old = JSON.parse(localStorage.getItem(OLD_KEY) || 'null');
                    if (old && Array.isArray(old.days)) {
                        r = {
                            days: old.days.map(function (d) {
                                return normalizeDay(Object.assign({}, d, { entries: d.entries || [] }));
                            })
                        };
                        localStorage.setItem(KEY, JSON.stringify(r));
                    }
                }
                if (!r || !Array.isArray(r.days)) return { days: [] };
                r.days = r.days.map(normalizeDay);
                return r;
            } catch (e) { return { days: [] }; }
        }

        /**
         * POS-ready punch row.
         * actualStart/actualEnd = clock in/out (aliases clockIn/clockOut for future imports)
         * timeSource: schedule | manual | pos
         * wageRate ($/hr), laborCost ($) — POS labor $ can land on laborCost
         * externalId / punchId — POS employee / punch keys later
         */
        function normalizeEntry(e) {
            e = e || {};
            var clockIn = e.actualStart || e.clockIn || e.schedStart || e.start || '';
            var clockOut = e.actualEnd || e.clockOut || e.schedEnd || e.end || '';
            var timeSource = e.timeSource || 'manual';
            if (['schedule', 'manual', 'pos'].indexOf(timeSource) === -1) timeSource = 'manual';
            return {
                id: e.id || uid(),
                shiftId: e.shiftId || '',
                personId: e.personId || '',
                name: e.name || '',
                role: e.role || '',
                schedStart: e.schedStart || e.start || '',
                schedEnd: e.schedEnd || e.end || '',
                // Clock punches (UI + POS)
                actualStart: clockIn,
                actualEnd: clockOut,
                clockIn: clockIn,
                clockOut: clockOut,
                breakMins: e.breakMins != null && e.breakMins !== '' ? e.breakMins : 0,
                timeSource: timeSource,
                wageRate: e.wageRate != null ? e.wageRate : '',
                laborCost: e.laborCost != null ? e.laborCost : '',
                externalId: e.externalId || '',
                punchId: e.punchId || '',
                notes: e.notes || ''
            };
        }

        function normalizeDay(d) {
            var entries = Array.isArray(d.entries) ? d.entries.map(normalizeEntry) : [];
            var hours = d.hours;
            if ((hours === '' || hours == null) && entries.length) {
                hours = sumActualHours(entries);
            }
            var source = d.source || 'manual';
            if (['manual', 'schedule', 'pos', 'mixed'].indexOf(source) === -1) source = 'manual';
            return {
                id: d.id || uid(),
                date: d.date || '',
                hours: hours != null ? hours : '',
                cost: d.cost != null ? d.cost : '',
                costSource: d.costSource || (source === 'pos' ? 'pos' : 'manual'), // manual | wages | pos
                foh: d.foh != null ? d.foh : '',
                boh: d.boh != null ? d.boh : '',
                notes: d.notes || '',
                source: source,
                posProvider: d.posProvider || '',
                entries: entries,
                updatedAt: d.updatedAt || 0,
                structureAt: d.structureAt || 0
            };
        }

        function sumActualHours(entries) {
            var h = 0;
            (entries || []).forEach(function (e) {
                var inn = e.actualStart || e.clockIn;
                var out = e.actualEnd || e.clockOut;
                h += hoursBetween(inn, out, e.breakMins);
            });
            return Math.round(h * 100) / 100;
        }

        function entryHours(e) {
            return hoursBetween(e.actualStart || e.clockIn, e.actualEnd || e.clockOut, e.breakMins);
        }

        function entryWageCost(e) {
            if (num(e.laborCost) != null) return num(e.laborCost);
            var rate = num(e.wageRate);
            if (rate == null) return null;
            return Math.round(rate * entryHours(e) * 100) / 100;
        }

        function sumWageCosts(entries) {
            var total = 0, any = false;
            (entries || []).forEach(function (e) {
                var c = entryWageCost(e);
                if (c != null) { total += c; any = true; }
            });
            return any ? Math.round(total * 100) / 100 : null;
        }

        function sumSchedHours(entries) {
            var h = 0;
            (entries || []).forEach(function (e) {
                h += hoursBetween(e.schedStart, e.schedEnd, 0);
            });
            return Math.round(h * 100) / 100;
        }

        function fohBohFromEntries(entries) {
            var foh = 0, boh = 0;
            (entries || []).forEach(function (e) {
                var h = entryHours(e);
                if (isBohRole(e.role) && !isFohRole(e.role)) boh += h;
                else if (isFohRole(e.role)) foh += h;
                else {
                    foh += h;
                }
            });
            return { foh: Math.round(foh * 100) / 100, boh: Math.round(boh * 100) / 100 };
        }

        function loadTeamPeople() {
            for (var i = 0; i < TEAM_KEYS.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(TEAM_KEYS[i]) || 'null');
                    if (r && Array.isArray(r.people)) return r.people;
                } catch (e) {}
            }
            return [];
        }

        /** $/hr from Team for person + role (roleWages map) */
        function teamWageFor(personId, name, role) {
            var people = loadTeamPeople();
            var person = people.find(function (p) {
                if (!p || p.status === 'inactive') return false;
                if (personId && p.id && String(p.id) === String(personId)) return true;
                return name && p.name && String(p.name).trim().toLowerCase() === String(name).trim().toLowerCase();
            });
            if (!person) return '';
            var wages = person.roleWages || {};
            if (role && wages[role] != null && wages[role] !== '') return wages[role];
            // fall back to first role wage or primary role
            if (role && person.roles && person.roles.indexOf(role) !== -1 && wages[role] != null) return wages[role];
            if (person.role && wages[person.role] != null) return wages[person.role];
            var keys = Object.keys(wages);
            if (keys.length) return wages[keys[0]];
            if (person.wage != null && person.wage !== '') return person.wage;
            return '';
        }

        function loadScheduleShifts(dateStr) {
            try {
                var sched = JSON.parse(localStorage.getItem(SCHED_KEY) || 'null');
                if (!sched || !sched.weeks) return [];
                var wk = weekKey(new Date(dateStr + 'T12:00:00'));
                var dayName = dayNameForDate(dateStr);
                var shifts = (sched.weeks[wk] || []).filter(function (s) { return s.day === dayName; });
                return shifts.map(function (s) {
                    var wage = teamWageFor(s.personId, s.name, s.role);
                    return normalizeEntry({
                        id: uid(),
                        shiftId: s.id || '',
                        personId: s.personId || '',
                        name: s.name || '',
                        role: s.role || '',
                        schedStart: s.start || '',
                        schedEnd: s.end || '',
                        actualStart: s.start || '',
                        actualEnd: s.end || '',
                        clockIn: s.start || '',
                        clockOut: s.end || '',
                        breakMins: 0,
                        timeSource: 'schedule',
                        wageRate: wage,
                        notes: s.notes || ''
                    });
                }).sort(function (a, b) {
                    return (a.actualStart || '').localeCompare(b.actualStart || '') || (a.name || '').localeCompare(b.name || '');
                });
            } catch (e) { return []; }
        }

        function resolvePosProvider() {
            var sel = document.getElementById('f-pos-provider').value;
            if (sel === 'other') {
                var custom = (document.getElementById('f-pos-custom').value || '').trim();
                return custom || 'other';
            }
            return sel || '';
        }

        function syncPosCustomVisibility() {
            var sel = document.getElementById('f-pos-provider');
            var custom = document.getElementById('f-pos-custom');
            if (!sel || !custom) return;
            custom.style.display = sel.value === 'other' ? 'block' : 'none';
            if (sel.value !== 'other') custom.value = '';
        }

        /**
         * POS labor days arrive via PbjPosSync.mergeLaborDays (Square timecards).
         * Reload state after a sync event.
         */
        function importPosLaborDay(/* dateStr, posPayload */) {
            return null; // handled by pos-sync-client.js → localStorage
        }

        function loadSales() {
            for (var i = 0; i < SALES_KEYS.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(SALES_KEYS[i]) || 'null');
                    var map = {};
                    if (r && Array.isArray(r.days)) {
                        r.days.forEach(function (d) {
                            var net = num(d.net);
                            if (net == null) net = num(d.gross);
                            if (net != null) map[d.date] = net;
                        });
                        return map;
                    }
                } catch (e) {}
            }
            return {};
        }

        var shared = null;
        var applyingRemote = false;

        function setSyncPill(info) {
            var pill = document.getElementById('sync-pill');
            var text = document.getElementById('sync-pill-text');
            if (!pill || !text) return;
            pill.classList.remove('offline', 'syncing');
            if (info.kind === 'offline') {
                pill.classList.add('offline');
                pill.style.background = '#FFF8E8';
                pill.style.color = '#8A6D1F';
            }
            if (info.kind === 'syncing') pill.classList.add('syncing');
            text.textContent = info.text || '';
        }

        function saveState(showToast) {
            state.structureAt = Date.now();
            // Also mirror v1 aggregates for trends / older readers
            try {
                localStorage.setItem(OLD_KEY, JSON.stringify({
                    days: state.days.map(function (d) {
                        return {
                            id: d.id, date: d.date, hours: d.hours, cost: d.cost,
                            foh: d.foh, boh: d.boh, notes: d.notes
                        };
                    })
                }));
            } catch (e) {}
            localStorage.setItem(KEY, JSON.stringify(state));
            if (showToast) toast(showToast === true ? undefined : showToast);
            if (shared && !applyingRemote) shared.queuePush(state);
        }

        var pendingConflict = null;
        function showLaborConflict(remoteDay, localDay) {
            pendingConflict = { remote: remoteDay, local: localDay };
            var ban = document.getElementById('conflict-banner');
            var txt = document.getElementById('conflict-banner-text');
            if (!ban || !txt) return;
            ban.style.display = 'block';
            txt.textContent = isSweet
                ? ('Kitchen has a newer labor day for ' + (remoteDay.date || '') + ' while you’re editing. Keep yours or use kitchen?')
                : ('Conflict on ' + (remoteDay.date || '') + '. Keep yours or use kitchen?');
        }
        function hideLaborConflict() {
            pendingConflict = null;
            var ban = document.getElementById('conflict-banner');
            if (ban) ban.style.display = 'none';
        }

        function applyLaborRemote(payload) {
            if (!payload || !Array.isArray(payload.days)) return;
            applyingRemote = true;
            var remote = payload.days.map(normalizeDay);
            var local = state.days.map(normalizeDay);
            var keepDate = dirty ? selectedDate : null;
            var localKeep = keepDate ? local.find(function (d) { return d.date === keepDate; }) : null;
            var remoteSame = keepDate ? remote.find(function (d) { return d.date === keepDate; }) : null;
            if (dirty && localKeep && remoteSame && (remoteSame.updatedAt || 0) > (localKeep.updatedAt || 0)) {
                var without = local.filter(function (d) { return d.date !== keepDate; });
                var remoteWithout = remote.filter(function (d) { return d.date !== keepDate; });
                var mergedPart = (window.PbjOpsNudges && window.PbjOpsNudges.mergeDaysByDate)
                    ? window.PbjOpsNudges.mergeDaysByDate(without, remoteWithout).map(normalizeDay)
                    : remoteWithout;
                mergedPart.push(localKeep);
                state = { days: mergedPart, structureAt: Math.max(payload.structureAt || 0, Date.now()) };
                localStorage.setItem(KEY, JSON.stringify(state));
                showLaborConflict(remoteSame, localKeep);
                renderStatsAndList();
                renderWeekChips();
                applyingRemote = false;
                return;
            }
            var merged = (window.PbjOpsNudges && window.PbjOpsNudges.mergeDaysByDate)
                ? window.PbjOpsNudges.mergeDaysByDate(local, remote).map(normalizeDay)
                : remote;
            if (keepDate && localKeep && dirty) {
                merged = merged.filter(function (d) { return d.date !== keepDate; });
                merged.push(localKeep);
            }
            state = { days: merged, structureAt: Math.max(payload.structureAt || 0, Date.now()) };
            localStorage.setItem(KEY, JSON.stringify(state));
            hideLaborConflict();
            if (!dirty) loadDraftForDate(selectedDate);
            renderStatsAndList();
            renderWeekChips();
            applyingRemote = false;
        }

        var state = loadState();
        var weekOffset = 0;
        var selectedDate = todayStr();
        /** Working copy of entries for the selected day (not yet saved) */
        var draftEntries = [];
        var dirty = false;

        function currentMonday() {
            var d = mondayOf(new Date());
            d.setDate(d.getDate() + weekOffset * 7);
            return d;
        }

        function findSavedDay(dateStr) {
            return state.days.find(function (d) { return d.date === dateStr; }) || null;
        }

        function applyDayMeta(saved, opts) {
            opts = opts || {};
            if (saved) {
                if (!opts.keepCost) {
                    document.getElementById('f-cost').value = saved.cost != null ? saved.cost : '';
                    document.getElementById('f-notes').value = saved.notes || '';
                }
                var src = saved.source || 'manual';
                if (['manual', 'schedule', 'pos', 'mixed'].indexOf(src) === -1) src = 'manual';
                // select only has manual | pos
                document.getElementById('f-source').value = (src === 'pos') ? 'pos' : 'manual';
                // POS provider is optional preference (toast/aloha/custom name…)
                var prov = saved.posProvider || '';
                var known = ['toast','square','clover','aloha','spoton','7shifts','homebase','other',''];
                if (prov && known.indexOf(prov) === -1) {
                    document.getElementById('f-pos-provider').value = 'other';
                    document.getElementById('f-pos-custom').value = prov;
                } else {
                    document.getElementById('f-pos-provider').value = prov || '';
                    document.getElementById('f-pos-custom').value = '';
                }
                syncPosCustomVisibility();
            } else if (!opts.keepCost) {
                document.getElementById('f-cost').value = '';
                document.getElementById('f-notes').value = '';
                if (!opts.forceSchedule) {
                    document.getElementById('f-source').value = 'manual';
                }
            }
        }

        function loadDraftForDate(dateStr, opts) {
            opts = opts || {};
            selectedDate = dateStr;
            var saved = findSavedDay(dateStr);
            if (saved && saved.entries && saved.entries.length && !opts.forceSchedule) {
                draftEntries = saved.entries.map(normalizeEntry);
                applyDayMeta(saved, opts);
            } else if (opts.forceSchedule || !saved) {
                var fromSched = loadScheduleShifts(dateStr);
                if (fromSched.length) {
                    // If re-pulling, keep punches / rates when same shift matches
                    if (opts.forceSchedule && draftEntries.length) {
                        var byKey = {};
                        draftEntries.forEach(function (e) {
                            var k = e.shiftId || (e.personId + '|' + e.name + '|' + e.schedStart);
                            byKey[k] = e;
                        });
                        fromSched = fromSched.map(function (s) {
                            var k = s.shiftId || (s.personId + '|' + s.name + '|' + s.schedStart);
                            var prev = byKey[k];
                            if (prev) {
                                s.actualStart = prev.actualStart;
                                s.actualEnd = prev.actualEnd;
                                s.clockIn = prev.clockIn || prev.actualStart;
                                s.clockOut = prev.clockOut || prev.actualEnd;
                                s.breakMins = prev.breakMins;
                                s.wageRate = prev.wageRate !== '' && prev.wageRate != null ? prev.wageRate : s.wageRate;
                                s.laborCost = prev.laborCost;
                                s.timeSource = prev.timeSource === 'pos' ? 'pos' : (prev.timeSource || 'manual');
                                s.notes = prev.notes || s.notes;
                                s.id = prev.id;
                                s.externalId = prev.externalId;
                                s.punchId = prev.punchId;
                            }
                            return s;
                        });
                    }
                    draftEntries = fromSched;
                    applyDayMeta(saved, opts);
                } else if (saved) {
                    draftEntries = (saved.entries || []).map(normalizeEntry);
                    applyDayMeta(saved, opts);
                } else {
                    draftEntries = [];
                    applyDayMeta(null, opts);
                }
            } else {
                draftEntries = [];
                applyDayMeta(saved, opts);
            }
            dirty = false;
            renderDayEditor();
            renderWeekChips();
        }

        function renderWeekChips() {
            var mon = currentMonday();
            var end = new Date(mon); end.setDate(end.getDate() + 6);
            document.getElementById('week-label').textContent =
                mon.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) +
                ' – ' +
                end.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });

            var chips = document.getElementById('day-chips');
            chips.innerHTML = DAY_NAMES.map(function (name, i) {
                var dateStr = dateFromMondayOffset(mon, i);
                var d = new Date(dateStr + 'T12:00:00');
                var label = d.toLocaleDateString(undefined, { weekday: 'short', month: 'numeric', day: 'numeric' });
                var saved = findSavedDay(dateStr);
                var schedN = loadScheduleShifts(dateStr).length;
                var sub = saved
                    ? (isSweet ? 'saved' : 'saved')
                    : (schedN ? (schedN + ' sch') : '—');
                var active = dateStr === selectedDate ? ' active' : '';
                return '<button type="button" class="day-chip' + active + '" data-date="' + esc(dateStr) + '">' +
                    esc(label) + '<span class="sub">' + esc(sub) + '</span></button>';
            }).join('');
        }

        function renderDayEditor() {
            var d = new Date(selectedDate + 'T12:00:00');
            document.getElementById('day-title').textContent = d.toLocaleDateString(undefined, {
                weekday: 'long', month: 'short', day: 'numeric'
            });

            var root = document.getElementById('entries');
            if (!draftEntries.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No shifts for this day yet. Pull from schedule, add someone, or build the week under Schedules 📅'
                    : 'No shifts for this day. Pull from schedule or add someone.') + '</div>';
            } else {
                root.innerHTML = draftEntries.map(function (e, idx) {
                    var schedH = hoursBetween(e.schedStart, e.schedEnd, 0);
                    var actH = entryHours(e);
                    var adjusted = (e.actualStart !== e.schedStart) || (e.actualEnd !== e.schedEnd) || (parseInt(e.breakMins, 10) || 0) > 0;
                    var diff = Math.round((actH - schedH) * 100) / 100;
                    var diffCls = diff > 0 ? 'over' : (diff < 0 ? 'under' : '');
                    var diffTxt = diff === 0 ? '' : ((diff > 0 ? '+' : '') + diff + 'h vs sched');
                    var srcBadge = e.timeSource === 'pos'
                        ? '<span class="badge pos">' + (isSweet ? 'From POS' : 'From POS') + '</span>'
                        : (e.timeSource === 'schedule' && !adjusted
                            ? '<span class="badge sched">' + (isSweet ? 'Schedule' : 'Schedule') + '</span>'
                            : (adjusted
                                ? '<span class="badge actual">' + (isSweet ? 'Clocked' : 'Clocked') + '</span>'
                                : '<span class="badge">' + (isSweet ? 'Manual' : 'Manual') + '</span>'));
                    var wage = entryWageCost(e);
                    var cardCls = 'shift-card' + (e.timeSource === 'pos' ? ' pos' : (adjusted ? ' adjusted' : ''));
                    return '<div class="' + cardCls + '" data-idx="' + idx + '">' +
                        '<div class="shift-top">' +
                        '<div><h3 class="shift-name">' + esc(e.name || '—') + srcBadge +
                        '</h3><div class="shift-role">' + esc(e.role || '') + '</div></div>' +
                        '<div class="hours-pill">' + actH + 'h' + (wage != null ? '<div style="font-size:0.8rem;font-weight:normal;opacity:0.8;">' + money(wage) + '</div>' : '') + '</div></div>' +
                        '<div class="sched-line">' + (isSweet ? 'Scheduled ' : 'Scheduled ') +
                        esc(fmtTime(e.schedStart)) + ' – ' + esc(fmtTime(e.schedEnd)) +
                        ' · ' + schedH + 'h' +
                        (diffTxt ? ' <span class="diff ' + diffCls + '">(' + diffTxt + ')</span>' : '') +
                        '</div>' +
                        '<div class="field-row">' +
                        '<div class="field"><label>' + (isSweet ? 'Clock in' : 'Clock in') + '</label>' +
                        '<input type="time" data-f="actualStart" data-idx="' + idx + '" value="' + esc(e.actualStart || '') + '"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Clock out' : 'Clock out') + '</label>' +
                        '<input type="time" data-f="actualEnd" data-idx="' + idx + '" value="' + esc(e.actualEnd || '') + '"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Break (min)' : 'Break (min)') + '</label>' +
                        '<input type="number" min="0" step="5" data-f="breakMins" data-idx="' + idx + '" value="' + esc(e.breakMins != null ? e.breakMins : 0) + '"></div>' +
                        '</div>' +
                        '<div class="field-row">' +
                        '<div class="field"><label>' + (isSweet ? '$/hr (optional)' : '$/hr (optional)') + '</label>' +
                        '<input type="number" min="0" step="0.01" data-f="wageRate" data-idx="' + idx + '" value="' + esc(e.wageRate != null ? e.wageRate : '') + '" placeholder="15.00"></div>' +
                        '<div class="field"><label>' + (isSweet ? 'Labor $ (optional)' : 'Labor $ (optional)') + '</label>' +
                        '<input type="number" min="0" step="0.01" data-f="laborCost" data-idx="' + idx + '" value="' + esc(e.laborCost != null ? e.laborCost : '') + '" placeholder="' + (isSweet ? 'POS / override' : 'POS / override') + '"></div>' +
                        '</div>' +
                        '<div class="field"><label>' + (isSweet ? 'Note' : 'Note') + '</label>' +
                        '<input type="text" data-f="notes" data-idx="' + idx + '" value="' + esc(e.notes || '') + '" placeholder="' + (isSweet ? 'Stayed for rush, left early…' : 'OT, left early…') + '"></div>' +
                        '<button type="button" class="btn btn-small btn-danger" data-remove-idx="' + idx + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                        '</div>';
                }).join('');
            }

            var schedH = sumSchedHours(draftEntries);
            var actH = sumActualHours(draftEntries);
            var wages = sumWageCosts(draftEntries);
            // Prefer full schedule $ when draft has no sched times but schedule exists
            var dayVar = window.PbjOpsNudges ? window.PbjOpsNudges.scheduleVsActual(selectedDate) : null;
            if (dayVar && dayVar.schedHours > schedH && !draftEntries.some(function (e) { return e.schedStart; })) {
                schedH = dayVar.schedHours;
            }
            document.getElementById('day-sched-h').textContent = (draftEntries.length || (dayVar && dayVar.hasSchedule)) ? (schedH + 'h') : '—';
            document.getElementById('day-actual-h').textContent = draftEntries.length ? (actH + 'h') : '—';
            document.getElementById('day-wages').textContent = wages != null ? money(wages) : '—';

            var dayVarHEl = document.getElementById('day-var-h');
            var dayVarDolEl = document.getElementById('day-var-dol');
            var daySchedDolEl = document.getElementById('day-sched-dol');
            if (dayVarHEl && dayVarDolEl && daySchedDolEl) {
                var vH = Math.round((actH - schedH) * 100) / 100;
                var schedDol = dayVar && dayVar.schedCost != null ? dayVar.schedCost : null;
                // If draft has rates, recompute scheduled $ from draft sched hours
                if (draftEntries.length) {
                    var s$ = 0, sAny = false;
                    draftEntries.forEach(function (e) {
                        var h = hoursBetween(e.schedStart, e.schedEnd, 0);
                        var rate = num(e.wageRate);
                        if (rate == null) return;
                        s$ += rate * h;
                        sAny = true;
                    });
                    if (sAny) schedDol = Math.round(s$ * 100) / 100;
                }
                var actDol = wages;
                if (actDol == null && dayVar) actDol = dayVar.actualCost;
                var v$ = (schedDol != null && actDol != null) ? Math.round((actDol - schedDol) * 100) / 100 : null;
                dayVarHEl.textContent = draftEntries.length || (dayVar && dayVar.hasSchedule)
                    ? ((vH > 0 ? '+' : '') + vH + 'h')
                    : '—';
                dayVarHEl.className = 'n' + (vH > 0.25 ? ' warn' : (vH < -0.25 ? ' ok' : ''));
                dayVarDolEl.textContent = v$ != null
                    ? ((v$ > 0 ? '+' : '') + money(v$))
                    : '—';
                dayVarDolEl.className = 'n' + (v$ != null && v$ > 10 ? ' warn' : (v$ != null && v$ < -10 ? ' ok' : ''));
                daySchedDolEl.textContent = schedDol != null ? money(schedDol) : '—';
            }

            // Soft-fill day cost from wages if empty or was auto
            var costEl = document.getElementById('f-cost');
            if (wages != null && (costEl.value === '' || costEl.dataset.auto === '1')) {
                costEl.value = wages.toFixed(2);
                costEl.dataset.auto = '1';
            }

            var saved = findSavedDay(selectedDate);
            var hasPos = draftEntries.some(function (e) { return e.timeSource === 'pos'; });
            document.getElementById('day-hint').textContent = dirty
                ? (isSweet ? 'Unsaved changes — hit Save this day when ready 💕' : 'Unsaved changes — save when ready.')
                : (hasPos
                    ? (isSweet ? 'Some punches tagged From POS — edit anytime ✏️' : 'Includes POS punches — editable.')
                    : (saved
                        ? (isSweet ? 'Loaded saved labor for this day — edit anytime ✏️' : 'Loaded saved labor for this day.')
                        : (isSweet ? 'Clock in / out = actual punches. Optional $/hr builds labor $ 💕' : 'Edit clock in/out, optional $/hr, then save.')));
        }

        function renderStatsAndList() {
            var salesMap = loadSales();
            var days = state.days.slice().sort(function (a, b) { return b.date.localeCompare(a.date); });
            var weekCut = new Date(); weekCut.setDate(weekCut.getDate() - 6);
            var week = days.filter(function (d) { return new Date(d.date + 'T12:00:00') >= weekCut; });
            // Also include current week days that have schedule-only? Stick to saved for 7d stats
            var hours = 0, cost = 0, sales = 0;
            week.forEach(function (d) {
                var h = num(d.hours);
                if (h == null && d.entries && d.entries.length) h = sumActualHours(d.entries);
                if (h != null) hours += h;
                if (num(d.cost) != null) cost += num(d.cost);
                if (salesMap[d.date] != null) sales += salesMap[d.date];
            });
            document.getElementById('stat-hours').textContent = hours ? (Math.round(hours * 10) / 10) + 'h' : '—';
            document.getElementById('stat-labor').textContent = cost ? money(cost) : '—';
            document.getElementById('stat-pct').textContent = (cost && sales) ? (Math.round((cost / sales) * 1000) / 10) + '%' : '—';

            // 7-day schedule vs actual
            var range = window.PbjOpsNudges ? window.PbjOpsNudges.scheduleVsActualRange(7) : null;
            var vhEl = document.getElementById('stat-var-h');
            var vdEl = document.getElementById('stat-var-dol');
            var shEl = document.getElementById('stat-sched-h');
            var vHint = document.getElementById('variance-hint');
            if (range && vhEl && vdEl && shEl) {
                shEl.textContent = range.schedHours ? (range.schedHours + 'h') : '—';
                vhEl.textContent = (range.daysWithSched || range.daysWithActual)
                    ? ((range.varHours > 0 ? '+' : '') + range.varHours + 'h')
                    : '—';
                vhEl.className = 'num' + (range.varHours > 1 ? ' warn' : (range.varHours < -1 ? ' ok' : ''));
                if (range.varCost != null) {
                    vdEl.textContent = (range.varCost > 0 ? '+' : '') + money(range.varCost);
                    vdEl.className = 'num' + (range.varCost > 25 ? ' warn' : (range.varCost < -25 ? ' ok' : ''));
                } else {
                    vdEl.textContent = '—';
                    vdEl.className = 'num';
                }
                if (vHint) {
                    if (!range.daysWithSched && !range.daysWithActual) {
                        vHint.textContent = isSweet
                            ? 'Build a schedule + save labor days (or sync POS) to see variance 💕'
                            : 'Add schedule and labor to compare.';
                    } else {
                        vHint.textContent = isSweet
                            ? ('Scheduled ' + range.schedHours + 'h · actual ' + range.actualHours + 'h' +
                                (range.schedCost ? ' · sched ' + money(range.schedCost) : '') +
                                (range.actualCost ? ' · actual ' + money(range.actualCost) : '') +
                                ' · green = under schedule')
                            : ('Sched ' + range.schedHours + 'h vs actual ' + range.actualHours + 'h');
                    }
                }
            }

            var root = document.getElementById('list');
            if (!days.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No saved labor days yet — pull a schedule day and save ⏱️' : 'No saved labor days yet.') + '</div>';
                return;
            }
            root.innerHTML = days.map(function (d) {
                var when = new Date(d.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                var sale = salesMap[d.date];
                var h = num(d.hours);
                if (h == null && d.entries && d.entries.length) h = sumActualHours(d.entries);
                var c = num(d.cost);
                var pct = (c != null && sale) ? (c / sale * 100) : null;
                var pctClass = pct == null ? '' : (pct > 35 ? 'warn' : 'ok');
                var meta = [];
                if (h != null) meta.push(h + 'h clocked');
                if (d.entries && d.entries.length) meta.push(d.entries.length + ' people');
                if (c != null) meta.push(money(c));
                if (d.costSource === 'pos' || d.source === 'pos') meta.push('POS labor $');
                else if (d.costSource === 'wages') meta.push('from rates');
                if (num(d.foh) != null) meta.push('FOH ' + d.foh + 'h');
                if (num(d.boh) != null) meta.push('BOH ' + d.boh + 'h');
                if (pct != null) meta.push('Labor ' + (Math.round(pct * 10) / 10) + '%');
                if (d.notes) meta.push(d.notes);
                var srcBadge = (d.source === 'pos' || (d.entries || []).some(function (e) { return e.timeSource === 'pos'; }))
                    ? '<span class="badge pos">' + (isSweet ? 'POS' : 'POS') + '</span>'
                    : '';
                return '<div class="entry"><div class="entry-top"><span>' + esc(when) + srcBadge + '</span>' +
                    (pct != null
                        ? '<span class="' + pctClass + '">' + (Math.round(pct * 10) / 10) + '%</span>'
                        : '<span>' + (c != null ? money(c) : (h != null ? h + 'h' : '—')) + '</span>') +
                    '</div><div class="entry-meta">' + esc(meta.join(' · ')) + '</div>' +
                    '<div class="entry-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-open="' + esc(d.date) + '">' + (isSweet ? 'Edit day' : 'Edit day') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-del="' + esc(d.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function saveCurrentDay() {
            var actH = sumActualHours(draftEntries);
            var parts = fohBohFromEntries(draftEntries);
            var existing = findSavedDay(selectedDate);
            var wages = sumWageCosts(draftEntries);
            var costVal = document.getElementById('f-cost').value;
            var costSource = 'manual';
            if (document.getElementById('f-cost').dataset.auto === '1' && wages != null) costSource = 'wages';
            var hasPosPunches = draftEntries.some(function (e) { return e.timeSource === 'pos'; });
            var hasSched = draftEntries.some(function (e) { return e.timeSource === 'schedule'; });
            var hasManual = draftEntries.some(function (e) { return e.timeSource === 'manual' || (!e.timeSource); });
            var daySource = 'manual';
            if (hasPosPunches && (hasSched || hasManual)) daySource = 'mixed';
            else if (hasPosPunches) daySource = 'pos';
            else if (hasSched && !hasManual) daySource = 'schedule';
            // Preserve POS day source if previously imported and user didn't wipe punches
            if (existing && existing.source === 'pos' && hasPosPunches) daySource = 'pos';
            if (existing && existing.costSource === 'pos' && document.getElementById('f-cost').dataset.auto !== '1') {
                // user may have kept POS $ — if they didn't clear, leave costSource pos when previous was pos
                if (String(costVal) === String(existing.cost)) costSource = 'pos';
            }

            // Sync clock aliases before save
            var entries = draftEntries.map(function (e) {
                var n = normalizeEntry(e);
                n.clockIn = n.actualStart;
                n.clockOut = n.actualEnd;
                // if user edited times away from schedule, mark manual unless POS
                if (n.timeSource !== 'pos') {
                    if (n.actualStart !== n.schedStart || n.actualEnd !== n.schedEnd || (parseInt(n.breakMins, 10) || 0) > 0) {
                        if (n.timeSource === 'schedule') n.timeSource = 'manual';
                    }
                }
                return n;
            });

            var row = normalizeDay({
                id: existing ? existing.id : uid(),
                date: selectedDate,
                hours: actH,
                cost: costVal,
                costSource: costSource,
                foh: parts.foh,
                boh: parts.boh,
                notes: document.getElementById('f-notes').value.trim(),
                source: daySource,
                posProvider: resolvePosProvider(),
                entries: entries,
                updatedAt: Date.now()
            });
            state.days = state.days.filter(function (d) { return d.date !== selectedDate; });
            state.days.push(row);
            saveState(true);
            dirty = false;
            document.getElementById('f-cost').dataset.auto = costSource === 'wages' ? '1' : '0';
            renderDayEditor();
            renderWeekChips();
            renderStatsAndList();
        }

        // Events
        document.getElementById('prev-week').addEventListener('click', function () {
            weekOffset--;
            var mon = currentMonday();
            // Keep same weekday offset if possible
            var old = new Date(selectedDate + 'T12:00:00');
            var idx = (old.getDay() + 6) % 7; // Mon=0
            selectedDate = dateFromMondayOffset(mon, idx);
            loadDraftForDate(selectedDate);
            renderStatsAndList();
        });
        document.getElementById('next-week').addEventListener('click', function () {
            weekOffset++;
            var mon = currentMonday();
            var old = new Date(selectedDate + 'T12:00:00');
            var idx = (old.getDay() + 6) % 7;
            selectedDate = dateFromMondayOffset(mon, idx);
            loadDraftForDate(selectedDate);
            renderStatsAndList();
        });

        document.getElementById('day-chips').addEventListener('click', function (e) {
            var chip = e.target.closest('[data-date]'); if (!chip) return;
            if (dirty && !confirm(isSweet ? 'Leave without saving this day\'s changes?' : 'Discard unsaved changes?')) return;
            loadDraftForDate(chip.dataset.date);
        });

        document.getElementById('pull-schedule').addEventListener('click', function () {
            var shifts = loadScheduleShifts(selectedDate);
            if (!shifts.length) {
                alert(isSweet
                    ? 'No shifts on the schedule for this day — add them under Schedules first 💕'
                    : 'No shifts on the schedule for this day.');
                return;
            }
            if (draftEntries.length && dirty) {
                if (!confirm(isSweet ? 'Replace current rows with schedule (keeps actual times when shift matches)?' : 'Replace with schedule? Matching actuals are kept.')) return;
            }
            loadDraftForDate(selectedDate, { forceSchedule: true, keepCost: true });
            dirty = true;
            renderDayEditor();
            toast(isSweet ? 'Pulled from schedule ✨' : 'Pulled from schedule');
        });

        function reloadLaborFromStorage() {
            state = loadState();
            loadDraftForDate(selectedDate);
            renderStatsAndList();
            renderWeekChips();
            renderDayEditor();
        }

        function runPosLaborSync(btn) {
            if (!window.PbjPosSync) {
                alert(isSweet ? 'Sync tools still loading — refresh and try again.' : 'Sync client missing — refresh.');
                return;
            }
            var statusEl = document.getElementById('labor-sync-status');
            if (btn) btn.disabled = true;
            if (statusEl) statusEl.textContent = isSweet ? 'Syncing all connected POS (sales + labor)…' : 'Syncing…';
            window.PbjPosSync.syncAll(7).then(function (res) {
                if (btn) btn.disabled = false;
                if (!res || !res.ok) {
                    var msg = (res && (res.hint || res.error)) || (isSweet ? 'Sync failed — connect a POS under Connections' : 'Sync failed');
                    if (statusEl) statusEl.textContent = msg;
                    toast(msg);
                    return;
                }
                reloadLaborFromStorage();
                var line = isSweet
                    ? ((res.providers || []).join(', ') + ': ' + (res.count || 0) + ' sales · ' + (res.laborCount || 0) + ' labor ✨')
                    : ((res.count || 0) + ' sales · ' + (res.laborCount || 0) + ' labor');
                if (res.results) {
                    Object.keys(res.results).forEach(function (p) {
                        var r = res.results[p];
                        if (r && r.laborError) line += ' · ' + p + ' labor ⚠';
                    });
                }
                if (statusEl) statusEl.textContent = line;
                toast(line);
            }).catch(function () {
                if (btn) btn.disabled = false;
                if (statusEl) statusEl.textContent = isSweet ? 'Network error' : 'Network error';
            });
        }

        document.getElementById('pull-pos').addEventListener('click', function () {
            runPosLaborSync(this);
        });
        var btnSyncLabor = document.getElementById('btn-sync-all-labor');
        if (btnSyncLabor) {
            btnSyncLabor.addEventListener('click', function () {
                runPosLaborSync(this);
            });
        }
        document.addEventListener('pbj-pos-synced', function () {
            reloadLaborFromStorage();
        });

        document.getElementById('add-manual').addEventListener('click', function () {
            var name = prompt(isSweet ? 'Teammate name?' : 'Employee name?');
            if (!name || !name.trim()) return;
            draftEntries.push(normalizeEntry({
                id: uid(),
                name: name.trim(),
                role: '',
                schedStart: '10:00',
                schedEnd: '16:00',
                actualStart: '10:00',
                actualEnd: '16:00',
                clockIn: '10:00',
                clockOut: '16:00',
                breakMins: 0,
                timeSource: 'manual'
            }));
            dirty = true;
            renderDayEditor();
        });

        document.getElementById('entries').addEventListener('input', function (e) {
            var input = e.target.closest('[data-f][data-idx]');
            if (!input) return;
            var idx = parseInt(input.getAttribute('data-idx'), 10);
            var field = input.getAttribute('data-f');
            if (!draftEntries[idx]) return;
            draftEntries[idx][field] = input.value;
            // Keep POS-ready aliases in sync
            if (field === 'actualStart') {
                draftEntries[idx].clockIn = input.value;
                if (draftEntries[idx].timeSource !== 'pos') draftEntries[idx].timeSource = 'manual';
            }
            if (field === 'actualEnd') {
                draftEntries[idx].clockOut = input.value;
                if (draftEntries[idx].timeSource !== 'pos') draftEntries[idx].timeSource = 'manual';
            }
            dirty = true;
            if (field === 'actualStart' || field === 'actualEnd' || field === 'breakMins' || field === 'wageRate' || field === 'laborCost') {
                renderDayEditor();
                var el = document.querySelector('[data-f="' + field + '"][data-idx="' + idx + '"]');
                if (el) { el.focus(); try { el.selectionStart = el.selectionEnd = el.value.length; } catch (err) {} }
            }
        });

        document.getElementById('f-cost').addEventListener('input', function () {
            this.dataset.auto = '0'; // user typed — not auto from wages
            dirty = true;
        });

        document.getElementById('entries').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove-idx]'); if (!btn) return;
            var idx = parseInt(btn.getAttribute('data-remove-idx'), 10);
            draftEntries.splice(idx, 1);
            dirty = true;
            renderDayEditor();
        });

        document.getElementById('f-notes').addEventListener('input', function () { dirty = true; });
        document.getElementById('f-pos-provider').addEventListener('change', function () {
            syncPosCustomVisibility();
            dirty = true;
        });
        document.getElementById('f-pos-custom').addEventListener('input', function () { dirty = true; });
        document.getElementById('f-source').addEventListener('change', function () { dirty = true; });
        syncPosCustomVisibility();

        document.getElementById('save-day').addEventListener('click', saveCurrentDay);

        document.getElementById('list').addEventListener('click', function (e) {
            var open = e.target.closest('[data-open]');
            if (open) {
                if (dirty && !confirm(isSweet ? 'Leave without saving this day\'s changes?' : 'Discard unsaved changes?')) return;
                var dateStr = open.dataset.open;
                // Jump week offset to that date's week
                var mon = mondayOf(new Date(dateStr + 'T12:00:00'));
                var thisMon = mondayOf(new Date());
                weekOffset = Math.round((mon - thisMon) / (7 * 24 * 3600 * 1000));
                loadDraftForDate(dateStr);
                document.getElementById('day-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            var del = e.target.closest('[data-del]'); if (!del) return;
            if (!confirm(isSweet ? 'Remove this labor day?' : 'Remove this labor day?')) return;
            state.days = state.days.filter(function (d) { return String(d.id) !== String(del.dataset.del); });
            saveState(true);
            if (findSavedDay(selectedDate) == null && !dirty) {
                // stay on day, just clear if we deleted current
            }
            // If deleted current saved day, reload draft from schedule
            var still = findSavedDay(selectedDate);
            if (!still) loadDraftForDate(selectedDate);
            renderStatsAndList();
            renderWeekChips();
        });

        document.getElementById('target-hint').textContent = isSweet
            ? 'Tip: many houses aim for ~25–35% labor of sales — pair with Daily Sales for the %.'
            : 'Tip: many houses aim for ~25–35% labor of sales.';

        // Align weekOffset to today
        weekOffset = 0;
        loadDraftForDate(todayStr());
        renderStatsAndList();
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRepPerms);
            document.addEventListener('pbj-perms-ready', applyRepPerms);

        var ck = document.getElementById('conflict-keep');
        var cu = document.getElementById('conflict-use-kitchen');
        if (ck) {
            ck.addEventListener('click', function () {
                if (!pendingConflict || !pendingConflict.local) { hideLaborConflict(); return; }
                var d = pendingConflict.local;
                d.updatedAt = Date.now();
                state.days = state.days.filter(function (x) { return x.date !== d.date; });
                state.days.push(normalizeDay(d));
                dirty = true;
                hideLaborConflict();
                saveState(false);
                toast(isSweet ? 'Kept your day · syncing…' : 'Kept yours');
            });
        }
        if (cu) {
            cu.addEventListener('click', function () {
                if (!pendingConflict || !pendingConflict.remote) { hideLaborConflict(); return; }
                var d = normalizeDay(pendingConflict.remote);
                state.days = state.days.filter(function (x) { return x.date !== d.date; });
                state.days.push(d);
                dirty = false;
                hideLaborConflict();
                saveState(false);
                loadDraftForDate(d.date);
                renderStatsAndList();
                renderWeekChips();
                toast(isSweet ? 'Using kitchen day ✨' : 'Using kitchen');
            });
        }

        if (window.PbjSharedState) {
            shared = new PbjSharedState({
                key: 'admin_labor_v1',
                date: '2000-01-01',
                pollMs: 6000,
                onStatus: setSyncPill,
                onRemote: function (payload) {
                    if (applyingRemote) return;
                    applyLaborRemote(payload);
                }
            });
            shared.bootstrap(
                function () { return state; },
                function (payload) { applyLaborRemote(payload); }
            ).then(function () { shared.startPolling(); });
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only · multi-device off' : 'Local only' });
        }
})();
    </script>
</body>
</html>
