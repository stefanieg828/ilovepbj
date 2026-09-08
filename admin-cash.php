<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Cash & Deposits' : 'Cash & Deposits'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.2rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.82rem; opacity: 0.7; margin-top: 4px; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 110px; }
        .crew-box { border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; border-radius: 14px; padding: 10px 12px; max-height: 220px; overflow-y: auto; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .crew-row { display: flex; align-items: center; gap: 10px; padding: 8px 4px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; cursor: pointer; }
        .crew-row:last-child { border-bottom: none; }
        .crew-row input { width: 18px; height: 18px; margin: 0; flex-shrink: 0; }
        .crew-row .crew-name { flex: 1; font-size: 1rem; }
        .crew-row .crew-role { font-size: 0.85rem; opacity: 0.65; }
        .crew-empty { font-size: 0.95rem; opacity: 0.7; padding: 8px 4px; line-height: 1.4; }
        .crew-empty a { color: inherit; font-weight: 600; }
        .crew-toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin: 8px 0 4px; }
        .btn-tiny { padding: 6px 12px; font-size: 0.88rem; border-radius: 10px; }
        .crew-pills { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; min-height: 0; }
        .pill { display: inline-block; border-radius: 999px; padding: 4px 10px; font-size: 0.82rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .entry { padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; }
        .entry-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .edit-banner { display: none; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.95rem; line-height: 1.4; <?php if ($is_sweet): ?>background: #E8F8F1; border: 1px solid #B8E6CF; color: #1F6B4A;<?php else: ?>background: #EAF1FA; border: 1px solid #C5D0DE; color: #1A2A44;<?php endif; ?> }
        .edit-banner.show { display: block; }
        .form-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .form-actions .btn { flex: 1; min-width: 120px; }
        .entry-meta { font-size: 0.92rem; opacity: 0.75; margin-top: 4px; line-height: 1.4; }
        .ok { color: #1F6B4A; }
        .warn { color: #C62828; }
        .empty { text-align: center; padding: 24px; opacity: 0.8; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .calc-row { display: flex; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: 12px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports & Sales' : 'Back to Reports & Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'Cash & Deposits' : 'Cash & Deposits'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Drawer count & bank drop' : 'Drawer count and bank deposit'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:0.88rem;margin-bottom:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);<?php if ($is_sweet): ?>background:#FFF5F6;color:#E55163;border:1px solid #F3C5CC;<?php else: ?>background:#EEF2F8;color:#1A2A44;border:1px solid #C5D0DE;<?php endif; ?>">
            <span class="dot" style="width:8px;height:8px;border-radius:50%;background:#2E9B63;"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>
        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-deposit">—</div><div class="lbl"><?php echo $is_sweet ? 'Deposits (7d)' : 'Deposits (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-short">—</div><div class="lbl"><?php echo $is_sweet ? 'Net over/short (7d)' : 'Net over/short (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-days">0</div><div class="lbl"><?php echo $is_sweet ? 'Days logged' : 'Days logged'; ?></div></div>
        </div>
        <div class="card" id="cash-habit-nudge" style="display:none;border:2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;">
            <h2 style="margin:0 0 6px;font-size:1.2rem;"><?php echo $is_sweet ? 'Missing cash closes' : 'Missing cash closes'; ?></h2>
            <p class="hint" id="cash-habit-text" style="margin:0 0 8px;"></p>
            <button type="button" class="btn btn-primary btn-small" id="btn-close-today"><?php echo $is_sweet ? 'Close today\'s drawer' : 'Close today'; ?></button>
        </div>
        <div class="card">
            <h2 id="form-title"><?php echo $is_sweet ? 'Close the drawer' : 'Close the drawer'; ?></h2>
            <p class="hint" id="form-hint"><?php echo $is_sweet
                ? 'Count the cash, compare to expected, log what goes to the bank. Pairs with Daily Sales tender mix 💵'
                : 'Count cash, compare to expected, log the bank deposit.'; ?></p>
            <div class="edit-banner" id="edit-banner"><?php echo $is_sweet ? 'Editing a saved close-out — save to update, or cancel ✏️' : 'Editing a saved close-out. Save to update, or cancel.'; ?></div>
            <form id="cash-form">
                <input type="hidden" id="f-id" value="">
                <div class="field"><label><?php echo $is_sweet ? 'Date' : 'Date'; ?></label><input type="date" id="f-date" required></div>

                <div class="field">
                    <label><?php echo $is_sweet ? 'On this shift (from Team)' : 'On this shift (from Team)'; ?></label>
                    <p class="hint" style="margin-top:0;"><?php echo $is_sweet
                        ? 'Check everyone who worked — list comes from Team & Roles. You can also suggest from the schedule 💕'
                        : 'Select everyone who worked. Roster comes from Team & Roles; optional suggest from schedule.'; ?></p>
                    <div class="field" style="margin-bottom:8px;">
                        <label><?php echo $is_sweet ? 'Quick add (pull-down)' : 'Quick add (dropdown)'; ?></label>
                        <select id="f-worker-select">
                            <option value=""><?php echo $is_sweet ? 'Select a teammate…' : 'Select a teammate…'; ?></option>
                        </select>
                    </div>
                    <div class="crew-toolbar">
                        <button type="button" class="btn btn-secondary btn-tiny" id="suggest-schedule"><?php echo $is_sweet ? '✨ From schedule' : 'From schedule'; ?></button>
                        <button type="button" class="btn btn-secondary btn-tiny" id="clear-crew"><?php echo $is_sweet ? 'Clear all' : 'Clear all'; ?></button>
                    </div>
                    <div class="crew-box" id="crew-box"></div>
                    <div class="crew-pills" id="crew-pills"></div>
                </div>

                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Starting bank ($)' : 'Starting bank ($)'; ?></label><input type="number" step="0.01" min="0" id="f-bank" placeholder="150.00"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Expected cash ($)' : 'Expected cash ($)'; ?></label><input type="number" step="0.01" min="0" id="f-expected"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Counted cash ($)' : 'Counted cash ($)'; ?></label><input type="number" step="0.01" min="0" id="f-counted"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Deposit amount ($)' : 'Deposit amount ($)'; ?></label><input type="number" step="0.01" min="0" id="f-deposit"></div>
                </div>
                <div class="calc-row">
                    <span><?php echo $is_sweet ? 'Over / short (auto)' : 'Over / short (auto)'; ?></span>
                    <strong id="calc-diff">—</strong>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Bag #, reason for variance…' : 'Bag #, variance…'; ?>"></div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-edit" style="display:none;"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary" id="save-btn"><?php echo $is_sweet ? 'Save cash day ✨' : 'Save cash day'; ?></button>
                </div>
            </form>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Recent close-outs' : 'Recent close-outs'; ?></h2>
            <div id="list"></div>
        </div>
        <div class="actions-bar">
            <a href="/admin/sales" class="btn btn-secondary"><?php echo $is_sweet ? '📈 Sales' : 'Sales'; ?></a>
            <a href="/admin/labor" class="btn btn-secondary"><?php echo $is_sweet ? '⏱️ Labor' : 'Labor'; ?></a>
            <a href="/admin/trends" class="btn btn-secondary"><?php echo $is_sweet ? '📊 Trends' : 'Trends'; ?></a>
            <a href="/admin/reports" class="btn btn-primary"><?php echo $is_sweet ? 'Reports hub' : 'Reports hub'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/shared-state.js?v=4"></script>
    <script src="/ops-nudges.js?v=2"></script>
    <script>
    (function () {
        const KEY = 'pbj_admin_cash_v1';
        const SHARED_KEY = 'admin_cash_v1';
        const SHARED_DATE = '2000-01-01';
        const TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
        const SCHED_KEY = 'pbj_admin_schedules_v1';
        const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var shared = null;
        var applyingRemote = false;
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
        function money(n) { if (n == null || isNaN(n)) return '—'; var sign = n < 0 ? '-' : ''; return sign + '$' + Math.abs(Math.round(n * 100) / 100).toFixed(2); }
        function num(v) { if (v === '' || v == null) return null; var n = parseFloat(v); return isNaN(n) ? null : n; }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }
        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (r && Array.isArray(r.days)) return r;
            } catch (e) {}
            return { days: [], structureAt: Date.now() };
        }
        function setSyncPill(info) {
            var pill = document.getElementById('sync-pill');
            var text = document.getElementById('sync-pill-text');
            if (!pill || !text) return;
            pill.classList.remove('offline', 'syncing');
            if (info.kind === 'offline') {
                pill.style.background = '#FFF8E8';
                pill.style.color = '#8A6D1F';
            }
            if (info.kind === 'syncing') pill.classList.add('syncing');
            text.textContent = info.text || '';
        }
        function save(t) {
            state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            if (t) {
                var el = document.getElementById('toast');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
            if (shared && !applyingRemote) shared.queuePush(state);
            paintCashHabit();
        }
        function normalizeCashDay(d) {
            d = d || {};
            return {
                id: d.id || uid(),
                date: d.date || '',
                bank: d.bank,
                expected: d.expected,
                counted: d.counted,
                deposit: d.deposit,
                workers: Array.isArray(d.workers) ? d.workers : [],
                notes: d.notes || '',
                updatedAt: d.updatedAt || 0
            };
        }
        function applyCashRemote(payload) {
            if (!payload || !Array.isArray(payload.days)) return;
            applyingRemote = true;
            var remote = payload.days.map(normalizeCashDay);
            var local = (state.days || []).map(normalizeCashDay);
            var merged = (window.PbjOpsNudges && window.PbjOpsNudges.mergeDaysByDate)
                ? window.PbjOpsNudges.mergeDaysByDate(local, remote).map(normalizeCashDay)
                : remote;
            state = { days: merged, structureAt: Math.max(payload.structureAt || 0, Date.now()) };
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
            paintCashHabit();
            applyingRemote = false;
        }
        function paintCashHabit() {
            var box = document.getElementById('cash-habit-nudge');
            var txt = document.getElementById('cash-habit-text');
            if (!box || !txt || !window.PbjOpsNudges) return;
            var st = window.PbjOpsNudges.cashCloseStatus(7);
            if (!st.missingCount && !st.todayNeedsClose) {
                box.style.display = 'none';
                return;
            }
            box.style.display = 'block';
            var bits = [];
            if (st.todayNeedsClose) {
                bits.push(isSweet ? 'Today has sales but no cash close yet' : 'Today: sales logged, no cash close');
            }
            if (st.missingCount) {
                bits.push(isSweet
                    ? (st.missingCount + ' day(s) with sales missing a close in the last week')
                    : (st.missingCount + ' sales day(s) missing cash close (7d)'));
            }
            txt.textContent = bits.join(' · ') + (isSweet ? ' 💵' : '');
        }

        function personRoles(p) {
            var roles = [];
            if (p && Array.isArray(p.roles) && p.roles.length) {
                p.roles.forEach(function (r) {
                    r = String(r || '').trim();
                    if (r && roles.indexOf(r) === -1) roles.push(r);
                });
            }
            if (p && p.role) {
                var single = String(p.role).trim();
                if (single && roles.indexOf(single) === -1) roles.unshift(single);
            }
            return roles;
        }

        function loadTeam() {
            for (var i = 0; i < TEAM_KEYS.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(TEAM_KEYS[i]) || 'null');
                    if (r && Array.isArray(r.people) && r.people.length) {
                        return r.people
                            .filter(function (p) { return p && p.status !== 'inactive' && (p.name || '').trim(); })
                            .map(function (p) {
                                var roles = personRoles(p);
                                return {
                                    id: String(p.id || ''),
                                    name: String(p.name || '').trim(),
                                    roles: roles,
                                    roleLabel: roles.join(', ')
                                };
                            })
                            .sort(function (a, b) { return a.name.localeCompare(b.name); });
                    }
                } catch (e) {}
            }
            return [];
        }

        /** Monday key YYYY-MM-DD for schedules week storage */
        function mondayKeyFromDateStr(dateStr) {
            var d = new Date(dateStr + 'T12:00:00');
            if (isNaN(d.getTime())) return null;
            var day = d.getDay();
            var diff = day === 0 ? -6 : 1 - day;
            d.setDate(d.getDate() + diff);
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }

        function dayNameFromDateStr(dateStr) {
            var d = new Date(dateStr + 'T12:00:00');
            if (isNaN(d.getTime())) return null;
            return DAY_NAMES[d.getDay()];
        }

        function scheduledWorkersForDate(dateStr) {
            try {
                var sched = JSON.parse(localStorage.getItem(SCHED_KEY) || 'null');
                if (!sched || !sched.weeks) return [];
                var wk = mondayKeyFromDateStr(dateStr);
                var dayName = dayNameFromDateStr(dateStr);
                if (!wk || !dayName) return [];
                // schedules store Mon–Sun (not Sun–Sat names for Sun = map Sun as...)
                // admin-schedules uses Mon,Tue,Wed,Thu,Fri,Sat,Sun
                var dayMap = { Sun: 'Sun', Mon: 'Mon', Tue: 'Tue', Wed: 'Wed', Thu: 'Thu', Fri: 'Fri', Sat: 'Sat' };
                var day = dayMap[dayName] || dayName;
                // Fix: DAY_NAMES gives Sun..Sat; schedules use Mon..Sun with 'Sun' for Sunday
                var shifts = (sched.weeks[wk] || []).filter(function (s) { return s.day === day; });
                var seen = {};
                var out = [];
                shifts.forEach(function (s) {
                    var key = s.personId || s.name;
                    if (!key || seen[key]) return;
                    seen[key] = true;
                    out.push({
                        id: s.personId || '',
                        name: s.name || '',
                        role: s.role || ''
                    });
                });
                return out;
            } catch (e) { return []; }
        }

        var state = load();
        var team = loadTeam();
        /** selected worker keys: person id or name */
        var selectedKeys = {};
        var editingId = null;

        document.getElementById('f-date').value = todayStr();

        function setEditMode(on, row) {
            editingId = on && row ? String(row.id) : null;
            document.getElementById('f-id').value = editingId || '';
            document.getElementById('edit-banner').classList.toggle('show', !!editingId);
            document.getElementById('cancel-edit').style.display = editingId ? '' : 'none';
            document.getElementById('form-title').textContent = editingId
                ? (isSweet ? 'Edit close-out' : 'Edit close-out')
                : (isSweet ? 'Close the drawer' : 'Close the drawer');
            document.getElementById('form-hint').textContent = editingId
                ? (isSweet ? 'Update anything below, then save — crew, cash counts, notes, all of it 💕' : 'Update fields below, then save.')
                : (isSweet ? 'Count the cash, compare to expected, log what goes to the bank. Pairs with Daily Sales tender mix 💵' : 'Count cash, compare to expected, log the bank deposit.');
            document.getElementById('save-btn').textContent = editingId
                ? (isSweet ? 'Update close-out ✨' : 'Update close-out')
                : (isSweet ? 'Save cash day ✨' : 'Save cash day');
        }

        function clearForm(keepDate) {
            var dateVal = keepDate ? document.getElementById('f-date').value : todayStr();
            document.getElementById('f-date').value = dateVal || todayStr();
            ['f-bank','f-expected','f-counted','f-deposit','f-notes'].forEach(function (id) {
                document.getElementById(id).value = '';
            });
            selectedKeys = {};
            paintCrew();
            updateDiff();
            setEditMode(false);
        }

        function loadIntoForm(row) {
            if (!row) return;
            setEditMode(true, row);
            document.getElementById('f-date').value = row.date || todayStr();
            document.getElementById('f-bank').value = row.bank != null ? row.bank : '';
            document.getElementById('f-expected').value = row.expected != null ? row.expected : '';
            document.getElementById('f-counted').value = row.counted != null ? row.counted : '';
            document.getElementById('f-deposit').value = row.deposit != null ? row.deposit : '';
            document.getElementById('f-notes').value = row.notes || '';
            setSelectedFromWorkers(row.workers || []);
            updateDiff();
            // Scroll to form so edit is obvious on mobile
            document.getElementById('form-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function workerKey(p) {
            return p.id ? String(p.id) : ('name:' + (p.name || '').toLowerCase());
        }

        function findTeamByKey(key) {
            return team.find(function (p) {
                return workerKey(p) === key || p.id === key || ('name:' + p.name.toLowerCase()) === key;
            }) || null;
        }

        function fillWorkerSelect() {
            var sel = document.getElementById('f-worker-select');
            sel.innerHTML = '<option value="">' + (isSweet ? 'Select a teammate…' : 'Select a teammate…') + '</option>';
            team.forEach(function (p) {
                var key = workerKey(p);
                if (selectedKeys[key]) return; // already on shift
                var label = p.name + (p.roleLabel ? ' · ' + p.roleLabel : '');
                sel.innerHTML += '<option value="' + esc(key) + '">' + esc(label) + '</option>';
            });
            if (!team.length) {
                sel.innerHTML = '<option value="">' + (isSweet ? 'No team yet — add in Team & Roles' : 'No team yet') + '</option>';
                sel.disabled = true;
            } else {
                sel.disabled = false;
            }
        }

        function paintCrew() {
            var box = document.getElementById('crew-box');
            var pills = document.getElementById('crew-pills');
            if (!team.length) {
                box.innerHTML = '<div class="crew-empty">' + (isSweet
                    ? 'No active teammates yet — add your crew in <a href="/admin/roster">Team & Roles</a> 💕'
                    : 'No active teammates. Add staff in <a href="/admin/roster">Team & Roles</a>.') + '</div>';
                pills.innerHTML = '';
                fillWorkerSelect();
                return;
            }
            box.innerHTML = team.map(function (p) {
                var key = workerKey(p);
                var checked = !!selectedKeys[key];
                return '<label class="crew-row">' +
                    '<input type="checkbox" data-key="' + esc(key) + '"' + (checked ? ' checked' : '') + '>' +
                    '<span class="crew-name">' + esc(p.name) + '</span>' +
                    (p.roleLabel ? '<span class="crew-role">' + esc(p.roleLabel) + '</span>' : '') +
                    '</label>';
            }).join('');
            var selected = team.filter(function (p) { return selectedKeys[workerKey(p)]; });
            pills.innerHTML = selected.map(function (p) {
                return '<span class="pill">' + esc(p.name) + (p.roleLabel ? ' · ' + esc(p.roles[0] || '') : '') + '</span>';
            }).join('');
            fillWorkerSelect();
        }

        function getSelectedWorkers() {
            return team.filter(function (p) { return selectedKeys[workerKey(p)]; }).map(function (p) {
                return {
                    id: p.id || '',
                    name: p.name,
                    role: (p.roles && p.roles[0]) || p.roleLabel || ''
                };
            });
        }

        function setSelectedFromWorkers(workers) {
            selectedKeys = {};
            (workers || []).forEach(function (w) {
                var key = w.id ? String(w.id) : ('name:' + String(w.name || '').toLowerCase());
                // Match team member if possible
                var match = team.find(function (p) {
                    return (w.id && p.id === String(w.id)) ||
                        (p.name && w.name && p.name.toLowerCase() === String(w.name).toLowerCase());
                });
                if (match) selectedKeys[workerKey(match)] = true;
                else if (w.name) selectedKeys[key] = true;
            });
            paintCrew();
        }

        document.getElementById('crew-box').addEventListener('change', function (e) {
            var cb = e.target.closest('input[type="checkbox"][data-key]');
            if (!cb) return;
            var key = cb.getAttribute('data-key');
            if (cb.checked) selectedKeys[key] = true;
            else delete selectedKeys[key];
            paintCrew();
        });

        document.getElementById('f-worker-select').addEventListener('change', function () {
            var key = this.value;
            if (!key) return;
            selectedKeys[key] = true;
            this.value = '';
            paintCrew();
        });

        document.getElementById('clear-crew').addEventListener('click', function () {
            selectedKeys = {};
            paintCrew();
        });

        document.getElementById('suggest-schedule').addEventListener('click', function () {
            var date = document.getElementById('f-date').value;
            var scheduled = scheduledWorkersForDate(date);
            if (!scheduled.length) {
                alert(isSweet
                    ? 'No shifts found on the schedule for that day — pick people from the list or add them under Schedules 💕'
                    : 'No shifts on the schedule for that day. Select people manually or add shifts under Schedules.');
                return;
            }
            // Merge onto current selection
            scheduled.forEach(function (w) {
                var match = team.find(function (p) {
                    return (w.id && p.id === String(w.id)) ||
                        (p.name && w.name && p.name.toLowerCase() === String(w.name).toLowerCase());
                });
                if (match) selectedKeys[workerKey(match)] = true;
            });
            paintCrew();
            var el = document.getElementById('toast');
            el.textContent = isSweet ? 'Added from schedule ✨' : 'Added from schedule';
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1100);
        });

        // When date changes (new entry only), soft-suggest from schedule if nothing selected yet
        document.getElementById('f-date').addEventListener('change', function () {
            if (editingId) return;
            if (Object.keys(selectedKeys).length) return;
            var scheduled = scheduledWorkersForDate(this.value);
            if (scheduled.length) setSelectedFromWorkers(scheduled);
        });

        document.getElementById('cancel-edit').addEventListener('click', function () {
            clearForm(false);
        });

        function updateDiff() {
            var exp = num(document.getElementById('f-expected').value);
            var cnt = num(document.getElementById('f-counted').value);
            var el = document.getElementById('calc-diff');
            if (exp == null || cnt == null) { el.textContent = '—'; el.className = ''; return; }
            var diff = cnt - exp;
            el.textContent = money(diff) + (diff === 0 ? (isSweet ? ' · even' : ' · even') : (diff > 0 ? (isSweet ? ' over' : ' over') : (isSweet ? ' short' : ' short')));
            el.className = diff === 0 ? 'ok' : 'warn';
        }
        function salesTenderCashForDate(dateStr) {
            try {
                var s = JSON.parse(localStorage.getItem('pbj_admin_sales_v2') || 'null')
                    || JSON.parse(localStorage.getItem('pbj_admin_sales_v1') || 'null');
                var days = (s && s.days) || [];
                for (var i = 0; i < days.length; i++) {
                    if (days[i] && days[i].date === dateStr) {
                        var tc = parseFloat(days[i].tenderCash);
                        if (!isNaN(tc)) return Math.round(tc * 100) / 100;
                    }
                }
            } catch (e) {}
            return null;
        }
        function suggestExpectedFromSales() {
            var date = document.getElementById('f-date').value;
            var expEl = document.getElementById('f-expected');
            if (!expEl || expEl.dataset.userEdited === '1') return;
            var cash = salesTenderCashForDate(date);
            if (cash == null) return;
            var bank = num(document.getElementById('f-bank').value) || 0;
            expEl.value = String(Math.round((bank + cash) * 100) / 100);
            expEl.title = isSweet
                ? ('Suggested: bank + Daily Sales cash tender (' + money(cash) + ')')
                : ('Suggested: bank + sales cash ' + cash);
            updateDiff();
        }
        document.getElementById('f-expected').addEventListener('input', function () {
            this.dataset.userEdited = '1';
            updateDiff();
        });
        document.getElementById('f-counted').addEventListener('input', updateDiff);
        document.getElementById('f-date').addEventListener('change', function () {
            document.getElementById('f-expected').dataset.userEdited = '';
            suggestExpectedFromSales();
        });
        document.getElementById('f-bank').addEventListener('input', function () {
            if (document.getElementById('f-expected').dataset.userEdited !== '1') suggestExpectedFromSales();
        });
        suggestExpectedFromSales();

        function render() {
            var days = state.days.slice().sort(function (a, b) { return b.date.localeCompare(a.date); });
            var weekCut = new Date(); weekCut.setDate(weekCut.getDate() - 6);
            var week = days.filter(function (d) { return new Date(d.date + 'T12:00:00') >= weekCut; });
            var dep = 0, short = 0, depN = 0;
            week.forEach(function (d) {
                if (num(d.deposit) != null) { dep += num(d.deposit); depN++; }
                if (num(d.expected) != null && num(d.counted) != null) short += num(d.counted) - num(d.expected);
            });
            document.getElementById('stat-deposit').textContent = depN ? money(dep) : '—';
            document.getElementById('stat-short').textContent = week.length ? money(short) : '—';
            document.getElementById('stat-days').textContent = String(days.length);

            var root = document.getElementById('list');
            if (!days.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No cash close-outs yet 💵' : 'No cash close-outs yet.') + '</div>';
                return;
            }
            root.innerHTML = days.map(function (d) {
                var diff = (num(d.expected) != null && num(d.counted) != null) ? (num(d.counted) - num(d.expected)) : null;
                var diffCls = diff == null ? '' : (diff === 0 ? 'ok' : 'warn');
                var when = new Date(d.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                var meta = [];
                if (Array.isArray(d.workers) && d.workers.length) {
                    meta.push('Shift: ' + d.workers.map(function (w) { return w.name; }).join(', '));
                }
                if (num(d.counted) != null) meta.push('Counted ' + money(num(d.counted)));
                if (num(d.expected) != null) meta.push('Expected ' + money(num(d.expected)));
                if (num(d.deposit) != null) meta.push('Deposit ' + money(num(d.deposit)));
                if (d.notes) meta.push(d.notes);
                var isEditingThis = editingId && String(d.id) === String(editingId);
                var editStyle = isEditingThis
                    ? ' style="outline:2px solid ' + (isSweet ? '#E55163' : '#1A2A44') + ';outline-offset:4px;border-radius:12px;padding:12px;"'
                    : '';
                return '<div class="entry"' + editStyle + '>' +
                    '<div class="entry-top"><span>' + esc(when) + (isEditingThis ? ' · ' + (isSweet ? 'editing' : 'editing') : '') + '</span>' +
                    '<span class="' + diffCls + '">' + (diff == null ? '—' : money(diff)) + '</span></div>' +
                    (meta.length ? '<div class="entry-meta">' + esc(meta.join(' · ')) + '</div>' : '') +
                    '<div class="entry-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-edit="' + esc(d.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-del="' + esc(d.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
            paintCashHabit();
        }

        document.getElementById('cash-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var date = document.getElementById('f-date').value;
            var workers = getSelectedWorkers();
            var id = editingId || document.getElementById('f-id').value || uid();
            var row = {
                id: id,
                date: date,
                workers: workers,
                bank: document.getElementById('f-bank').value,
                expected: document.getElementById('f-expected').value,
                counted: document.getElementById('f-counted').value,
                deposit: document.getElementById('f-deposit').value,
                notes: document.getElementById('f-notes').value.trim(),
                updatedAt: Date.now()
            };
            // Replace by edit id if editing; else replace same calendar date; else append
            if (editingId) {
                state.days = state.days.filter(function (d) { return String(d.id) !== String(editingId); });
                // Avoid two rows for same date after a date change
                state.days = state.days.filter(function (d) { return d.date !== date || String(d.id) === String(id); });
            } else {
                var existing = state.days.find(function (d) { return d.date === date; });
                if (existing) {
                    row.id = existing.id;
                    state.days = state.days.filter(function (d) { return d.date !== date; });
                }
            }
            state.days.push(row);
            save(true);
            clearForm(false);
            render();
        });
        document.getElementById('list').addEventListener('click', function (e) {
            var editBtn = e.target.closest('[data-edit]');
            if (editBtn) {
                var row = state.days.find(function (d) { return String(d.id) === String(editBtn.dataset.edit); });
                if (row) loadIntoForm(row);
                return;
            }
            var btn = e.target.closest('[data-del]'); if (!btn) return;
            if (!confirm(isSweet ? 'Remove this cash close-out?' : 'Remove this entry?')) return;
            var delId = String(btn.dataset.del);
            state.days = state.days.filter(function (d) { return String(d.id) !== delId; });
            if (editingId && editingId === delId) clearForm(false);
            save(true); render();
        });

        // Refresh team when returning to tab
        window.addEventListener('focus', function () {
            team = loadTeam();
            paintCrew();
        });

        // Initial: try schedule suggest for today
        var initialScheduled = scheduledWorkersForDate(todayStr());
        if (initialScheduled.length) setSelectedFromWorkers(initialScheduled);
        else paintCrew();

        var btnCloseToday = document.getElementById('btn-close-today');
        if (btnCloseToday) {
            btnCloseToday.addEventListener('click', function () {
                clearForm(false);
                document.getElementById('f-date').value = todayStr();
                document.getElementById('form-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.getElementById('f-counted').focus();
            });
        }

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRepPerms);
            document.addEventListener('pbj-perms-ready', applyRepPerms);
        updateDiff();
        paintCashHabit();

        if (window.PbjSharedState) {
            shared = new PbjSharedState({
                key: SHARED_KEY,
                date: SHARED_DATE,
                pollMs: 6000,
                onStatus: setSyncPill,
                onRemote: function (payload) {
                    if (applyingRemote) return;
                    applyCashRemote(payload);
                }
            });
            shared.bootstrap(
                function () { return state; },
                function (payload) { applyCashRemote(payload); }
            ).then(function () { shared.startPolling(); });
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only · multi-device off' : 'Local only' });
        }
    })();
    </script>
</body>
</html>
