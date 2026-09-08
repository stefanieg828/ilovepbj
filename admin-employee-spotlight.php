<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Star of the house' : 'Employee spotlight'; ?> • <?php echo $is_sweet ? 'Schedules & Shifts' : 'Schedules'; ?> • ilovepbj ops</title>
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
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.92; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 80px; resize: vertical; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .hint { font-size: 0.95rem; opacity: 0.72; margin: 0 0 12px; line-height: 1.4; }
        .hint a { color: inherit; font-weight: 600; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .spotlight-card { text-align: center; }
        .spotlight-card .star { font-size: 2.2rem; margin-bottom: 6px; }
        .spotlight-card .period-label {
            display: inline-block; border-radius: 999px; padding: 4px 12px; font-size: 0.85rem; margin-bottom: 10px;
            <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?>
        }
        .spotlight-card .name {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
            font-size: 2rem; margin: 0 0 6px; line-height: 1.15;
        }
        .spotlight-card .roles { font-size: 1rem; opacity: 0.75; margin-bottom: 10px; }
        .spotlight-card .dates { font-size: 0.95rem; opacity: 0.7; margin-bottom: 12px; }
        .spotlight-card .shoutout { font-size: 1.1rem; line-height: 1.45; margin: 0 auto 14px; max-width: 32em; white-space: pre-wrap; }
        .field input[type="date"] { min-height: 44px; }
        .award {
            border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
            border-radius: 0 14px 14px 0; padding: 14px 16px; margin-bottom: 10px;
            <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?>
        }
        .award.current { border-left-color: #E8A838; <?php if ($is_sweet): ?>background: #FFF9F0;<?php else: ?>background: #F7F3EA;<?php endif; ?> }
        .award-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; flex-wrap: wrap; }
        .award-name { font-size: 1.15rem; margin: 0 0 4px; }
        .award-meta { font-size: 0.9rem; opacity: 0.75; line-height: 1.4; margin-bottom: 8px; }
        .award-body { font-size: 1rem; line-height: 1.4; white-space: pre-wrap; margin-bottom: 10px; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.78rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; margin: 0 4px 4px 0; }
        .badge.gold { background: #FFF3D6; color: #8A5A00; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .empty { text-align: center; padding: 28px; opacity: 0.8; line-height: 1.4; }
        .team-empty { <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; line-height: 1.4; font-size: 0.95rem; }
        .team-empty a { color: inherit; font-weight: 600; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .mgr-only[hidden] { display: none !important; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/schedules" class="back-link">← <?php echo $is_sweet ? 'Back to Schedules & Shifts' : 'Back to Schedules'; ?></a>
        <h1><?php echo $is_sweet ? 'Star of the house' : 'Employee spotlight'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Employee of the week, month, quarter — or anytime' : 'Employee of the week / month / quarter / custom'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Pick a teammate from your roster, set the period, and write a shout-out ⭐ Saving posts it to <strong>Team Announcements</strong> and shows them as the current star on the home dashboard.'
                : 'Select a teammate from the roster, set the period, and add a shout-out. Saving posts to Team Announcements and features them on the home dashboard.'; ?>
        </div>
        <div class="sync-pill syncing" id="sync-pill">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div id="current-spotlight"></div>

        <div class="card mgr-only" id="nominate-card">
            <h2><?php echo $is_sweet ? 'Select employee of…' : 'Select employee'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Owners, GMs, and managers can select. Names come from <a href="/admin/roster">Crew roster</a>. A Team Announcement is posted when you save 💕'
                : 'Owners, GMs, and managers can select. Names come from the <a href="/admin/roster">team roster</a>. Saving posts a Team Announcement.'; ?></p>
            <div class="team-empty" id="team-empty" style="display:none;">
                <?php echo $is_sweet
                    ? 'No active teammates yet — add your crew in <a href="/admin/roster">Crew roster</a> first 💕'
                    : 'No active teammates yet. Add staff in <a href="/admin/roster">Team roster</a> first.'; ?>
            </div>
            <form id="nominate-form">
                <input type="hidden" id="f-edit-id" value="">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Teammate' : 'Employee'; ?></label>
                    <select id="f-person" required>
                        <option value=""><?php echo $is_sweet ? 'Select teammate…' : 'Select teammate…'; ?></option>
                    </select>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Period' : 'Period'; ?></label>
                        <select id="f-period">
                            <option value="week"><?php echo $is_sweet ? 'Of the week' : 'Week'; ?></option>
                            <option value="month" selected><?php echo $is_sweet ? 'Of the month' : 'Month'; ?></option>
                            <option value="quarter"><?php echo $is_sweet ? 'Of the quarter' : 'Quarter'; ?></option>
                            <option value="custom"><?php echo $is_sweet ? 'Custom dates' : 'Custom'; ?></option>
                        </select>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Title on the spotlight' : 'Display title'; ?></label>
                        <input id="f-label" placeholder="<?php echo $is_sweet ? 'Auto-fills from period…' : 'Auto from period…'; ?>">
                    </div>
                </div>
                <div class="field-row" id="period-dates">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Starts' : 'Start'; ?></label>
                        <input id="f-start" type="date" required>
                        <p class="hint" style="margin:4px 0 0;font-size:0.85rem;"><?php echo $is_sweet ? 'Always editable — tweak anytime' : 'Always editable'; ?></p>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Ends' : 'End'; ?></label>
                        <input id="f-end" type="date" required>
                    </div>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Shout-out / why' : 'Shout-out / reason'; ?></label>
                    <textarea id="f-shoutout" placeholder="<?php echo $is_sweet ? 'What did they crush this period?' : 'Why this teammate?'; ?>"></textarea>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Selected by' : 'Selected by'; ?></label>
                    <input id="f-by" value="<?php echo htmlspecialchars($user_name); ?>">
                </div>
                <div class="actions" style="margin-top:12px;">
                    <button type="button" class="btn btn-secondary" id="cancel-edit" style="display:none;flex:1;"><?php echo $is_sweet ? 'Cancel edit' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary" style="flex:2;" id="submit-btn" data-perm="admin.team.spotlight.manage"><?php echo $is_sweet ? 'Save & announce ⭐' : 'Save & announce'; ?></button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Hall of fame' : 'History'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Past and current stars — newest first' : 'Past and current awards, newest first.'; ?></p>
            <div id="history"></div>
        </div>

        <div class="actions-bar">
            <a href="/admin/roster" class="btn btn-secondary"><?php echo $is_sweet ? '👥 Roster' : 'Roster'; ?></a>
            <a href="/messages/announcements" class="btn btn-secondary"><?php echo $is_sweet ? '📢 Announcements' : 'Announcements'; ?></a>
            <a href="/admin/schedules" class="btn btn-primary"><?php echo $is_sweet ? 'Schedules & Shifts' : 'Schedules'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="shared-state.js?v=3"></script>
    <script>
    (function () {
        var KEY = 'pbj_admin_spotlight_v1';
        var SHARED_KEY = 'admin_spotlight_v1';
        var ANN_KEY = 'pbj_jelly_announcements_v1';
        var ANN_SHARED = 'jelly_announcements_v1';
        var TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var userName = <?php echo json_encode($user_name, JSON_UNESCAPED_UNICODE); ?>;

        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return true;
        }
        function applyPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            var canView = canP('admin.team.spotlight.view') || canP('admin.team.spotlight.manage');
            var canManage = canP('admin.team.spotlight.manage');
            if (!canView) {
                var c = document.querySelector('.content');
                if (c && !document.getElementById('spot-denied')) {
                    c.insertAdjacentHTML('afterbegin',
                        '<div class="intro" id="spot-denied">' +
                        (isSweet ? 'No permission to view Star of the house 🔒' : 'No permission to view employee spotlight.') +
                        '</div>');
                }
            }
            var formCard = document.getElementById('nominate-card');
            if (formCard) {
                if (canManage) formCard.removeAttribute('hidden');
                else formCard.setAttribute('hidden', 'hidden');
            }
            document.querySelectorAll('[data-act="feature"], [data-act="edit"], [data-act="del"]').forEach(function (el) {
                el.style.display = canManage ? '' : 'none';
            });
            try { if (typeof render === 'function') render(); } catch (e) {}
        }

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function parseDate(s) {
            if (!s) return null;
            var p = String(s).split('-');
            if (p.length !== 3) return null;
            var dt = new Date(+p[0], +p[1] - 1, +p[2]);
            return isNaN(dt.getTime()) ? null : dt;
        }
        function fmtDate(s) {
            var dt = parseDate(s);
            if (!dt) return '—';
            return dt.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        }
        function ymd(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function startOfWeek(d) {
            var x = new Date(d.getFullYear(), d.getMonth(), d.getDate());
            var day = x.getDay();
            var diff = day === 0 ? -6 : 1 - day;
            x.setDate(x.getDate() + diff);
            return x;
        }
        function endOfWeek(d) {
            var s = startOfWeek(d);
            s.setDate(s.getDate() + 6);
            return s;
        }
        function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }
        function endOfMonth(d) { return new Date(d.getFullYear(), d.getMonth() + 1, 0); }
        function startOfQuarter(d) {
            var q = Math.floor(d.getMonth() / 3) * 3;
            return new Date(d.getFullYear(), q, 1);
        }
        function endOfQuarter(d) {
            var q = Math.floor(d.getMonth() / 3) * 3;
            return new Date(d.getFullYear(), q + 3, 0);
        }
        function defaultLabel(period) {
            if (period === 'week') return 'Employee of the Week';
            if (period === 'month') return 'Employee of the Month';
            if (period === 'quarter') return 'Employee of the Quarter';
            return isSweet ? 'Star of the house' : 'Employee spotlight';
        }
        function fillPeriodDates(period, opts) {
            opts = opts || {};
            var now = new Date();
            var startEl = document.getElementById('f-start');
            var endEl = document.getElementById('f-end');
            var labelEl = document.getElementById('f-label');
            // Dates are always editable; period only suggests defaults
            startEl.readOnly = false;
            endEl.readOnly = false;
            startEl.disabled = false;
            endEl.disabled = false;
            if (!opts.keepDates) {
                if (period === 'week') {
                    startEl.value = ymd(startOfWeek(now));
                    endEl.value = ymd(endOfWeek(now));
                } else if (period === 'month') {
                    startEl.value = ymd(startOfMonth(now));
                    endEl.value = ymd(endOfMonth(now));
                } else if (period === 'quarter') {
                    startEl.value = ymd(startOfQuarter(now));
                    endEl.value = ymd(endOfQuarter(now));
                } else {
                    if (!startEl.value) startEl.value = todayStr();
                    if (!endEl.value) endEl.value = todayStr();
                }
            }
            var cur = (labelEl.value || '').trim();
            var defaults = [
                'Employee of the Week', 'Employee of the Month', 'Employee of the Quarter',
                'Star of the house', 'Employee spotlight'
            ];
            if (!opts.keepLabel && (!cur || defaults.indexOf(cur) !== -1)) {
                labelEl.value = defaultLabel(period);
            }
        }

        function personRoles(p) {
            var roles = [];
            if (p && Array.isArray(p.roles) && p.roles.length) {
                p.roles.forEach(function (r) {
                    if (r && typeof r === 'object') r = r.role || r.name || '';
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
                            .filter(function (p) { return p && p.status !== 'inactive' && String(p.name || '').trim(); })
                            .map(function (p) {
                                return {
                                    id: String(p.id || ''),
                                    name: String(p.name || '').trim(),
                                    roles: personRoles(p)
                                };
                            })
                            .sort(function (a, b) { return a.name.localeCompare(b.name); });
                    }
                } catch (e) {}
            }
            return [];
        }
        function fillPersonSelect(selectedId) {
            var team = loadTeam();
            var sel = document.getElementById('f-person');
            var empty = document.getElementById('team-empty');
            if (empty) empty.style.display = team.length ? 'none' : '';
            var html = '<option value="">' + (isSweet ? 'Select teammate…' : 'Select teammate…') + '</option>';
            team.forEach(function (p) {
                var roleBit = p.roles.length ? ' · ' + p.roles.join(', ') : '';
                html += '<option value="' + esc(p.id) + '"' + (selectedId && selectedId === p.id ? ' selected' : '') + '>' +
                    esc(p.name) + esc(roleBit) + '</option>';
            });
            sel.innerHTML = html;
            return team;
        }

        function emptyState() {
            return { awards: [], currentId: null, structureAt: Date.now(), deletedIds: {} };
        }
        function normalizeAward(a) {
            a = a || {};
            return {
                id: a.id || uid(),
                personId: String(a.personId || ''),
                personName: String(a.personName || ''),
                roles: Array.isArray(a.roles) ? a.roles.slice() : [],
                periodType: ['week', 'month', 'quarter', 'custom'].indexOf(a.periodType) >= 0 ? a.periodType : 'month',
                label: String(a.label || defaultLabel(a.periodType || 'month')),
                startDate: String(a.startDate || ''),
                endDate: String(a.endDate || ''),
                shoutout: String(a.shoutout || ''),
                nominatedBy: String(a.nominatedBy || ''),
                announcementId: a.announcementId ? String(a.announcementId) : null,
                at: a.at || Date.now(),
                updatedAt: a.updatedAt || a.at || Date.now()
            };
        }
        function loadLocal() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || !Array.isArray(r.awards)) return emptyState();
                r.awards = r.awards.map(normalizeAward);
                if (!r.deletedIds || typeof r.deletedIds !== 'object') r.deletedIds = {};
                if (!r.structureAt) r.structureAt = Date.now();
                return r;
            } catch (e) { return emptyState(); }
        }
        function pruneDeleted(r) {
            if (!r.deletedIds || typeof r.deletedIds !== 'object') r.deletedIds = {};
            r.awards = (r.awards || []).filter(function (a) {
                var id = String(a.id || '');
                if (!id) return true;
                var delAt = r.deletedIds[id];
                if (delAt == null) return true;
                if ((a.updatedAt || 0) > delAt) {
                    delete r.deletedIds[id];
                    return true;
                }
                return false;
            });
            if (r.currentId && r.deletedIds[r.currentId]) r.currentId = null;
            if (r.currentId && !r.awards.some(function (a) { return a.id === r.currentId; })) {
                r.currentId = r.awards.length ? r.awards[0].id : null;
            }
            return r;
        }

        var state = pruneDeleted(loadLocal());
        var shared = null;

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = typeof msg === 'string' ? msg : (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1300);
        }
        function setSyncPill(info) {
            var pill = document.getElementById('sync-pill');
            var text = document.getElementById('sync-pill-text');
            if (!pill || !text) return;
            pill.classList.remove('offline', 'syncing');
            if (info.kind === 'offline') pill.classList.add('offline');
            if (info.kind === 'syncing') pill.classList.add('syncing');
            text.textContent = info.text || '';
        }
        function persist(showToast, toastMsg) {
            state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            if (shared) shared.queuePush(state);
            if (showToast) toast(toastMsg || undefined);
        }
        function applyRemote(payload) {
            if (!payload || !Array.isArray(payload.awards)) return;
            state = pruneDeleted({
                awards: payload.awards.map(normalizeAward),
                currentId: payload.currentId || null,
                structureAt: payload.structureAt || Date.now(),
                deletedIds: (payload.deletedIds && typeof payload.deletedIds === 'object') ? payload.deletedIds : {}
            });
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
        }
        function currentAward() {
            if (state.currentId) {
                var hit = state.awards.find(function (a) { return a.id === state.currentId; });
                if (hit) return hit;
            }
            var today = todayStr();
            var inWindow = state.awards.filter(function (a) {
                return a.startDate && a.endDate && a.startDate <= today && a.endDate >= today;
            });
            if (inWindow.length) {
                inWindow.sort(function (a, b) { return (b.at || 0) - (a.at || 0); });
                return inWindow[0];
            }
            if (!state.awards.length) return null;
            return state.awards.slice().sort(function (a, b) { return (b.at || 0) - (a.at || 0); })[0];
        }
        function periodBadge(a) {
            var map = { week: 'Week', month: 'Month', quarter: 'Quarter', custom: 'Custom' };
            return map[a.periodType] || a.periodType;
        }

        /** Push / update a Team Announcements post for this award */
        function syncAnnouncement(award, isUpdate) {
            var title = (award.label || defaultLabel(award.periodType)) + ': ' + (award.personName || '');
            var bodyParts = [];
            if (award.shoutout) bodyParts.push(award.shoutout);
            bodyParts.push(
                (isSweet ? 'Period: ' : 'Period: ') +
                fmtDate(award.startDate) + ' – ' + fmtDate(award.endDate)
            );
            if (award.roles && award.roles.length) {
                bodyParts.push((isSweet ? 'Roles: ' : 'Roles: ') + award.roles.join(', '));
            }
            if (award.nominatedBy) {
                bodyParts.push((isSweet ? 'Selected by ' : 'Selected by ') + award.nominatedBy);
            }
            var body = bodyParts.join('\n\n');
            var author = award.nominatedBy || userName || 'Team';
            var now = Date.now();

            var ann = { posts: [] };
            try {
                var raw = JSON.parse(localStorage.getItem(ANN_KEY) || 'null');
                if (raw && Array.isArray(raw.posts)) ann = raw;
            } catch (e) {}

            var postId = award.announcementId || null;
            var existing = postId ? ann.posts.find(function (p) { return p.id === postId; }) : null;

            if (existing) {
                existing.title = title;
                existing.body = body;
                existing.author = author;
                existing.pinned = true;
                existing.at = now;
                existing.updatedAt = now;
            } else {
                postId = postId || ('star-' + (award.id || uid()));
                ann.posts.unshift({
                    id: postId,
                    title: title,
                    body: body,
                    author: author,
                    pinned: true,
                    at: now,
                    updatedAt: now,
                    source: 'employee_spotlight',
                    awardId: award.id
                });
                award.announcementId = postId;
            }
            ann.structureAt = now;
            localStorage.setItem(ANN_KEY, JSON.stringify(ann));

            // Push kitchen-wide so other devices see it
            if (window.PbjSharedState) {
                var annShared = new PbjSharedState({
                    key: ANN_SHARED,
                    pollMs: 0,
                    onRemote: function () {},
                    onStatus: function () {}
                });
                // Seed or merge: bootstrap then force-push our posts with structureAt
                annShared.bootstrap(
                    function () { return ann; },
                    function (remote) {
                        if (remote && Array.isArray(remote.posts)) {
                            // Prefer remote posts, then ensure our post is present/updated
                            var map = {};
                            remote.posts.forEach(function (p) {
                                if (p && p.id) map[p.id] = p;
                            });
                            ann.posts.forEach(function (p) {
                                if (!p || !p.id) return;
                                var prev = map[p.id];
                                if (!prev || (p.at || 0) >= (prev.at || 0)) map[p.id] = p;
                            });
                            ann.posts = Object.keys(map).map(function (k) { return map[k]; })
                                .sort(function (a, b) {
                                    var ap = a.pinned ? 1 : 0, bp = b.pinned ? 1 : 0;
                                    if (ap !== bp) return bp - ap;
                                    return (b.at || 0) - (a.at || 0);
                                });
                            ann.structureAt = Date.now();
                            localStorage.setItem(ANN_KEY, JSON.stringify(ann));
                        }
                    }
                ).then(function () {
                    return annShared.push(ann, { force: true });
                }).catch(function () {});
            }
            return postId;
        }

        function renderSpotlight() {
            var root = document.getElementById('current-spotlight');
            var a = currentAward();
            if (!a) {
                root.innerHTML = '<div class="card spotlight-card empty">' +
                    (isSweet
                        ? 'No star selected yet — managers can pick someone below ⭐'
                        : 'No employee spotlight yet. Managers can select someone below.') +
                    '</div>';
                return;
            }
            var roles = (a.roles && a.roles.length) ? a.roles.join(' · ') : '';
            var html = '<div class="card spotlight-card">';
            html += '<div class="star">⭐</div>';
            html += '<div class="period-label">' + esc(a.label || defaultLabel(a.periodType)) + '</div>';
            html += '<h2 class="name">' + esc(a.personName) + '</h2>';
            if (roles) html += '<div class="roles">' + esc(roles) + '</div>';
            html += '<div class="dates">' + esc(fmtDate(a.startDate)) + ' – ' + esc(fmtDate(a.endDate)) +
                ' · <span class="badge gold">' + esc(periodBadge(a)) + '</span></div>';
            if (a.shoutout) html += '<div class="shoutout">' + esc(a.shoutout) + '</div>';
            if (a.nominatedBy) {
                html += '<div class="dates">' + (isSweet ? 'Selected by ' : 'Selected by ') + esc(a.nominatedBy) + '</div>';
            }
            html += '</div>';
            root.innerHTML = html;
        }

        function renderHistory() {
            var root = document.getElementById('history');
            var list = state.awards.slice().sort(function (a, b) { return (b.at || 0) - (a.at || 0); });
            var cur = currentAward();
            var curId = cur ? cur.id : null;
            if (!list.length) {
                root.innerHTML = '<div class="empty">' +
                    (isSweet ? 'Your hall of fame is empty — select the first star 💕' : 'No awards yet.') +
                    '</div>';
                return;
            }
            var canManage = canP('admin.team.spotlight.manage');
            root.innerHTML = list.map(function (a) {
                var isCur = a.id === curId;
                var html = '<div class="award' + (isCur ? ' current' : '') + '">';
                html += '<div class="award-top"><div>';
                html += '<h3 class="award-name">' + esc(a.personName) +
                    (isCur ? ' <span class="badge gold">' + (isSweet ? 'Current' : 'Current') + '</span>' : '') +
                    '</h3>';
                html += '<div class="award-meta">' + esc(a.label || defaultLabel(a.periodType)) +
                    ' · ' + esc(fmtDate(a.startDate)) + ' – ' + esc(fmtDate(a.endDate));
                html += '</div></div></div>';
                if (a.shoutout) html += '<div class="award-body">' + esc(a.shoutout) + '</div>';
                if (canManage) {
                    html += '<div class="actions">';
                    if (!isCur) {
                        html += '<button type="button" class="btn btn-small btn-ghost" data-act="feature" data-id="' + esc(a.id) + '">' +
                            (isSweet ? 'Make current' : 'Feature') + '</button>';
                    }
                    html += '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-id="' + esc(a.id) + '">' +
                        (isSweet ? 'Edit' : 'Edit') + '</button>';
                    html += '<button type="button" class="btn btn-small btn-danger" data-act="del" data-id="' + esc(a.id) + '">' +
                        (isSweet ? 'Remove' : 'Remove') + '</button>';
                    html += '</div>';
                }
                html += '</div>';
                return html;
            }).join('');
        }

        function render() {
            fillPersonSelect(document.getElementById('f-person').value);
            renderSpotlight();
            renderHistory();
            applyPerms();
        }

        function clearForm() {
            document.getElementById('f-edit-id').value = '';
            document.getElementById('f-person').value = '';
            document.getElementById('f-period').value = 'month';
            document.getElementById('f-label').value = defaultLabel('month');
            fillPeriodDates('month');
            document.getElementById('f-shoutout').value = '';
            document.getElementById('f-by').value = userName || '';
            document.getElementById('cancel-edit').style.display = 'none';
            document.getElementById('submit-btn').textContent = isSweet ? 'Save & announce ⭐' : 'Save & announce';
        }
        function startEdit(a) {
            document.getElementById('f-edit-id').value = a.id;
            fillPersonSelect(a.personId);
            document.getElementById('f-person').value = a.personId;
            document.getElementById('f-period').value = a.periodType || 'month';
            document.getElementById('f-label').value = a.label || defaultLabel(a.periodType);
            document.getElementById('f-start').value = a.startDate || '';
            document.getElementById('f-end').value = a.endDate || '';
            document.getElementById('f-start').readOnly = false;
            document.getElementById('f-end').readOnly = false;
            document.getElementById('f-start').disabled = false;
            document.getElementById('f-end').disabled = false;
            document.getElementById('f-shoutout').value = a.shoutout || '';
            document.getElementById('f-by').value = a.nominatedBy || userName || '';
            document.getElementById('cancel-edit').style.display = '';
            document.getElementById('submit-btn').textContent = isSweet ? 'Save changes 💾' : 'Save changes';
            document.getElementById('nominate-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.getElementById('f-period').addEventListener('change', function () {
            // While editing an existing star, keep their chosen dates; otherwise suggest period defaults
            var editing = !!(document.getElementById('f-edit-id').value);
            fillPeriodDates(this.value, { keepDates: editing });
        });
        document.getElementById('cancel-edit').addEventListener('click', clearForm);

        document.getElementById('nominate-form').addEventListener('submit', function (e) {
            e.preventDefault();
            if (!canP('admin.team.spotlight.manage')) {
                toast(isSweet ? 'No permission 🔒' : 'No permission');
                return;
            }
            var team = loadTeam();
            var personId = document.getElementById('f-person').value;
            var person = team.find(function (p) { return p.id === personId; });
            if (!person) {
                toast(isSweet ? 'Pick a teammate from the roster' : 'Pick a teammate');
                return;
            }
            var period = document.getElementById('f-period').value;
            var start = document.getElementById('f-start').value;
            var end = document.getElementById('f-end').value;
            if (!start || !end) {
                toast(isSweet ? 'Need start & end dates' : 'Start and end required');
                return;
            }
            if (end < start) {
                toast(isSweet ? 'End date is before start' : 'End date before start');
                return;
            }
            var label = document.getElementById('f-label').value.trim() || defaultLabel(period);
            var editId = document.getElementById('f-edit-id').value;
            var now = Date.now();
            var award;
            if (editId) {
                award = state.awards.find(function (x) { return x.id === editId; });
                if (award) {
                    award.personId = person.id;
                    award.personName = person.name;
                    award.roles = person.roles.slice();
                    award.periodType = period;
                    award.label = label;
                    award.startDate = start;
                    award.endDate = end;
                    award.shoutout = document.getElementById('f-shoutout').value.trim();
                    award.nominatedBy = document.getElementById('f-by').value.trim() || userName;
                    award.updatedAt = now;
                    award.at = now;
                    state.currentId = award.id;
                }
            } else {
                award = normalizeAward({
                    id: uid(),
                    personId: person.id,
                    personName: person.name,
                    roles: person.roles.slice(),
                    periodType: period,
                    label: label,
                    startDate: start,
                    endDate: end,
                    shoutout: document.getElementById('f-shoutout').value.trim(),
                    nominatedBy: document.getElementById('f-by').value.trim() || userName,
                    at: now,
                    updatedAt: now
                });
                state.awards.unshift(award);
                state.currentId = award.id;
            }
            if (award) {
                syncAnnouncement(award, !!editId);
            }
            persist(true, isSweet ? 'Saved & announced ⭐' : 'Saved & announced');
            clearForm();
            render();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.getElementById('history').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]');
            if (!btn) return;
            if (!canP('admin.team.spotlight.manage')) return;
            var id = btn.getAttribute('data-id');
            var act = btn.getAttribute('data-act');
            var award = state.awards.find(function (a) { return a.id === id; });
            if (act === 'feature' && award) {
                state.currentId = award.id;
                award.updatedAt = Date.now();
                syncAnnouncement(award, true);
                persist(true, isSweet ? 'Now featuring ⭐' : 'Featured');
                render();
            } else if (act === 'edit' && award) {
                startEdit(award);
            } else if (act === 'del' && award) {
                var ok = confirm(isSweet
                    ? ('Remove ' + (award.personName || 'this star') + ' from the hall of fame?')
                    : ('Remove award for ' + (award.personName || 'this person') + '?'));
                if (!ok) return;
                state.deletedIds = state.deletedIds || {};
                state.deletedIds[id] = Date.now();
                state.awards = state.awards.filter(function (a) { return a.id !== id; });
                if (state.currentId === id) {
                    state.currentId = state.awards.length ? state.awards[0].id : null;
                }
                persist(true, isSweet ? 'Removed' : 'Removed');
                render();
            }
        });

        fillPeriodDates('month');
        document.getElementById('f-label').value = defaultLabel('month');
        fillPersonSelect();
        render();

        if (window.PbjSharedState) {
            shared = new PbjSharedState({
                key: SHARED_KEY,
                pollMs: 4000,
                onRemote: function (payload) { applyRemote(payload); },
                onStatus: function (info) { setSyncPill(info); }
            });
            shared.bootstrap(
                function () { return state; },
                function (payload) { applyRemote(payload); }
            );
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'On this device only' : 'Device only' });
        }

        if (window.PbjPerms && window.PbjPerms.ready) {
            window.PbjPerms.ready.then(applyPerms).catch(function () { applyPerms(); });
        } else {
            setTimeout(applyPerms, 400);
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') fillPersonSelect(document.getElementById('f-person').value);
        });
        window.addEventListener('storage', function (e) {
            if (e.key && TEAM_KEYS.indexOf(e.key) !== -1) fillPersonSelect(document.getElementById('f-person').value);
        });
    })();
    </script>
</body>
</html>
