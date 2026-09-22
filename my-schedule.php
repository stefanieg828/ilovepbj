<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_label = trim((string) ($_SESSION['name'] ?? $_SESSION['username'] ?? 'Team'));
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'My Schedule' : 'My Schedule'; ?> • ilovepbj ops</title>
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
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .week-nav { display: flex; gap: 10px; align-items: center; margin-bottom: 14px; }
        .week-nav .label { flex: 1; text-align: center; font-size: 1.1rem; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .shift { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; padding: 12px 14px; border-radius: 12px; margin-bottom: 8px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .shift.is-open { <?php if ($is_sweet): ?>border-color: #E6A817; background: #FFFBF0;<?php else: ?>border-color: #E6A817; background: #FFF9EC;<?php endif; ?> }
        .shift-main { flex: 1; min-width: 160px; }
        .shift-name { font-size: 1.08rem; }
        .shift-meta { font-size: 0.9rem; opacity: 0.75; }
        .shift-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.78rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; margin-left: 6px; vertical-align: middle; }
        .badge-open { background: #FFE082; color: #6D4C00; }
        .empty { opacity: 0.7; font-size: 0.95rem; padding: 8px 0; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
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
        .who-bar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .who-bar select { flex: 1; min-width: 160px; }
        .modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; display: none; align-items: flex-end; justify-content: center; }
        .modal-bg.show { display: flex; }
        .modal { background: white; border-radius: 20px 20px 0 0; padding: 20px; width: 100%; max-width: 520px; max-height: 80vh; overflow: auto; }
        .denied { background: white; border-radius: 18px; padding: 18px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        [hidden] { display: none !important; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/home" class="back-link">← Home</a>
        <h1><?php echo $is_sweet ? 'My Schedule' : 'My Schedule'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Your shifts, open board & trades' : 'Your shifts, open board, and trades'; ?></p>
    </div>
    <div class="content">
        <div class="denied" id="no-perm" hidden>
            <?php echo $is_sweet ? 'You don’t have access to My Schedule. Ask an owner to grant View or Trade on Schedules.' : 'No permission to view My Schedule.'; ?>
        </div>
        <div id="app">
            <div class="sync-pill" id="sched-sync-pill"><span class="dot"></span><span class="sync-text">This device</span></div>
            <div class="week-nav">
                <button type="button" class="btn btn-secondary" id="prev-week">←</button>
                <div class="label" id="week-label">This week</div>
                <button type="button" class="btn btn-secondary" id="next-week">→</button>
            </div>
            <div class="posted-banner is-draft" id="posted-banner">This week isn’t posted yet.</div>

            <div class="card" id="who-card">
                <h2><?php echo $is_sweet ? 'I am…' : 'I am…'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'One-time pick so we know which roster name is you. Saves to this account 💕'
                    : 'One-time roster pick so we know which shifts are yours. Saves to your account.'; ?></p>
                <div class="who-bar">
                    <select id="me-select">
                        <option value=""><?php echo $is_sweet ? 'Select your name…' : 'Select your name…'; ?></option>
                    </select>
                    <button type="button" class="btn btn-primary btn-small" id="me-save"><?php echo $is_sweet ? 'That’s me' : 'That’s me'; ?></button>
                </div>
                <p class="hint" id="me-current" style="margin:10px 0 0;"></p>
            </div>

            <div class="card" id="mine-card">
                <h2><?php echo $is_sweet ? 'My shifts' : 'My shifts'; ?></h2>
                <p class="hint" id="mine-hint"><?php echo $is_sweet ? 'Give up a shift or propose a swap after the week is posted.' : 'Give up or propose a swap on posted weeks.'; ?></p>
                <div id="mine-list"><div class="empty"><?php echo $is_sweet ? 'No shifts this week' : 'No shifts this week.'; ?></div></div>
            </div>

            <div class="card" id="open-card">
                <h2><?php echo $is_sweet ? 'Open board' : 'Open board'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'Shifts someone gave up — claim one if you can work it.' : 'Given-up shifts. Claim one if you can work it.'; ?></p>
                <div id="open-list"><div class="empty"><?php echo $is_sweet ? 'Nothing open' : 'No open shifts.'; ?></div></div>
            </div>

            <div class="card" id="incoming-card">
                <h2><?php echo $is_sweet ? 'Swaps for you' : 'Swap offers'; ?></h2>
                <div id="incoming-list"><div class="empty"><?php echo $is_sweet ? 'No one has asked to swap yet' : 'No swap offers.'; ?></div></div>
            </div>

            <div class="card" id="mine-req-card">
                <h2><?php echo $is_sweet ? 'My requests' : 'My requests'; ?></h2>
                <div id="mine-req-list"><div class="empty"><?php echo $is_sweet ? 'None yet' : 'None yet.'; ?></div></div>
            </div>

            <a href="/admin/schedules" class="btn btn-secondary" style="width:100%;box-sizing:border-box;margin-top:4px;" data-perm-any="admin.schedules.add_shift,admin.schedules.approve"><?php echo $is_sweet ? '📅 Manager board' : 'Manager schedule board'; ?></a>
        </div>
    </div>
    <div class="modal-bg" id="swap-modal" hidden>
        <div class="modal">
            <h2 style="margin-top:0;"><?php echo $is_sweet ? 'Propose a swap' : 'Propose a swap'; ?></h2>
            <p class="hint" id="swap-hint"></p>
            <div class="field">
                <label><?php echo $is_sweet ? 'Swap with' : 'Swap with'; ?></label>
                <select id="swap-target"></select>
            </div>
            <div class="field">
                <label><?php echo $is_sweet ? 'Note (optional)' : 'Note (optional)'; ?></label>
                <input id="swap-note" placeholder="<?php echo $is_sweet ? 'Cover my close? I’ll take your lunch…' : 'Optional note'; ?>">
            </div>
            <div class="shift-actions">
                <button type="button" class="btn btn-primary" id="swap-go"><?php echo $is_sweet ? 'Send swap' : 'Send swap'; ?></button>
                <button type="button" class="btn btn-ghost" id="swap-cancel"><?php echo $is_sweet ? 'Never mind' : 'Cancel'; ?></button>
            </div>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/schedule-shared.js?v=1"></script>
    <script>
    (function () {
        var S = window.PbjSchedules;
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var userLabel = <?php echo json_encode($user_label); ?>;
        var days = <?php echo json_encode($days); ?>;
        if (!S) return;

        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return true;
        }
        function canTrade() { return canP('admin.schedules.trade'); }
        function canView() { return canP('admin.schedules.view') || canTrade() || canP('admin.schedules.add_shift'); }

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg || (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1400);
        }
        function esc(s) { return S.esc(s); }

        var state = S.loadLocal();
        var team = S.loadTeam();
        var weekOffset = 0;
        var meId = S.loadMeId();
        var swapFromId = null;
        var schedSync = null;

        function mePerson() { return S.findPerson(team, meId); }
        function currentMonday() {
            var d = S.mondayOf(new Date());
            d.setDate(d.getDate() + weekOffset * 7);
            return d;
        }
        function currentKey() { return S.weekKey(currentMonday()); }

        function persist(show) {
            state = S.saveLocal(state);
            if (schedSync) schedSync.push(state);
            if (show) toast();
            render();
        }

        function applyResult(res, okMsg, pendingMsg) {
            if (!res || !res.ok) {
                alert((res && res.message) || (isSweet ? 'Couldn’t do that' : 'Could not complete that.'));
                return;
            }
            state = res.state;
            persist(false);
            if (res.applied) toast(okMsg || (isSweet ? 'Done ✨' : 'Done'));
            else toast(pendingMsg || (isSweet ? 'Sent — waiting 💕' : 'Sent — waiting'));
        }

        function fillMeSelect() {
            var sel = document.getElementById('me-select');
            var cur = meId;
            sel.innerHTML = '<option value="">' + (isSweet ? 'Select your name…' : 'Select your name…') + '</option>';
            team.forEach(function (p) {
                sel.innerHTML += '<option value="' + esc(p.id || p.name) + '">' + esc(p.name) +
                    (p.roles && p.roles.length ? ' · ' + esc(p.roles[0]) : '') + '</option>';
            });
            if (cur) sel.value = cur;
            var label = document.getElementById('me-current');
            var person = mePerson();
            label.textContent = person
                ? (isSweet ? ('You’re ' + person.name + ' on this device / account.') : ('Signed in as ' + person.name + '.'))
                : (team.length ? '' : (isSweet ? 'No roster yet — ask a manager to add you in Team & Roles.' : 'No roster yet. Ask a manager to add you.'));
        }

        function setMe(id) {
            meId = id || '';
            S.pushMeToPrefs(meId);
            fillMeSelect();
            render();
        }

        function openSwapModal(shiftId) {
            swapFromId = shiftId;
            var key = currentKey();
            var mine = S.findShift(state, key, shiftId);
            var sel = document.getElementById('swap-target');
            var others = (state.weeks[key] || []).filter(function (s) {
                return s && s.id !== shiftId && !s.open;
            });
            sel.innerHTML = others.length
                ? others.map(function (s) {
                    return '<option value="' + esc(s.id) + '">' + esc(s.day + ' · ' + s.name + ' · ' + S.fmtTime(s.start) + '–' + S.fmtTime(s.end) + (s.role ? ' · ' + s.role : '')) + '</option>';
                }).join('')
                : '<option value="">' + (isSweet ? 'No other shifts this week' : 'No other shifts this week') + '</option>';
            document.getElementById('swap-hint').textContent = mine
                ? ((isSweet ? 'Your ' : 'Your ') + mine.day + ' ' + S.fmtTime(mine.start) + '–' + S.fmtTime(mine.end))
                : '';
            document.getElementById('swap-note').value = '';
            var bg = document.getElementById('swap-modal');
            bg.hidden = false;
            bg.classList.add('show');
        }
        function closeSwapModal() {
            var bg = document.getElementById('swap-modal');
            bg.classList.remove('show');
            bg.hidden = true;
            swapFromId = null;
        }

        function renderShiftRow(s, kind) {
            var actions = '';
            var posted = S.isWeekPosted(state, currentKey());
            var trade = canTrade() && posted && meId;
            if (kind === 'mine' && trade && !s.open) {
                actions =
                    '<div class="shift-actions">' +
                    '<button type="button" class="btn btn-ghost btn-small" data-giveup="' + esc(s.id) + '">' + (isSweet ? 'Give up' : 'Give up') + '</button>' +
                    '<button type="button" class="btn btn-ghost btn-small" data-swap="' + esc(s.id) + '">' + (isSweet ? 'Swap' : 'Swap') + '</button>' +
                    '</div>';
            }
            if (kind === 'open' && trade) {
                actions =
                    '<div class="shift-actions">' +
                    '<button type="button" class="btn btn-primary btn-small" data-claim="' + esc(s.id) + '">' + (isSweet ? 'Claim' : 'Claim') + '</button>' +
                    '</div>';
            }
            return '<div class="shift' + (s.open ? ' is-open' : '') + '">' +
                '<div class="shift-main"><div class="shift-name">' + esc(s.day) + ' · ' + esc(s.name) +
                (s.role ? '<span class="badge">' + esc(s.role) + '</span>' : '') +
                (s.open ? '<span class="badge badge-open">Open</span>' : '') +
                '</div>' +
                '<div class="shift-meta">' + esc(S.fmtTime(s.start)) + ' – ' + esc(S.fmtTime(s.end)) +
                (s.notes ? ' · ' + esc(s.notes) : '') +
                (s.open && s.originalName ? ' · was ' + esc(s.originalName) : '') +
                '</div></div>' + actions + '</div>';
        }

        function render() {
            var mon = currentMonday();
            var key = currentKey();
            if (!state.weeks[key]) state.weeks[key] = [];
            var end = new Date(mon); end.setDate(end.getDate() + 6);
            document.getElementById('week-label').textContent =
                mon.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) +
                ' – ' +
                end.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });

            var posted = S.isWeekPosted(state, key);
            var banner = document.getElementById('posted-banner');
            banner.className = 'posted-banner ' + (posted ? 'is-posted' : 'is-draft');
            banner.textContent = posted
                ? (isSweet ? 'This week is posted — you can give up, swap, or claim.' : 'This week is posted. You can trade.')
                : (isSweet ? 'This week isn’t posted yet — hang tight for the manager board.' : 'This week is not posted yet.');

            var person = mePerson();
            var shifts = state.weeks[key] || [];
            var mine = person
                ? shifts.filter(function (s) {
                    return !s.open && (s.personId === person.id || (!s.personId && s.name === person.name));
                }).sort(function (a, b) {
                    var di = days.indexOf(a.day) - days.indexOf(b.day);
                    return di !== 0 ? di : (a.start || '').localeCompare(b.start || '');
                })
                : [];
            var open = shifts.filter(function (s) { return s.open; }).sort(function (a, b) {
                var di = days.indexOf(a.day) - days.indexOf(b.day);
                return di !== 0 ? di : (a.start || '').localeCompare(b.start || '');
            });

            var mineEl = document.getElementById('mine-list');
            var hint = document.getElementById('mine-hint');
            if (!person) {
                mineEl.innerHTML = '<div class="empty">' + (isSweet ? 'Pick “I am…” first so we can find your shifts.' : 'Select who you are first.') + '</div>';
            } else if (!mine.length) {
                mineEl.innerHTML = '<div class="empty">' + (isSweet ? 'No shifts for you this week' : 'No shifts for you this week.') + '</div>';
            } else {
                mineEl.innerHTML = mine.map(function (s) { return renderShiftRow(s, 'mine'); }).join('');
            }
            if (hint) {
                hint.textContent = !posted
                    ? (isSweet ? 'Trades unlock after this week is posted.' : 'Trades unlock after this week is posted.')
                    : (isSweet ? 'Give up a shift or propose a swap. No wages on this page.' : 'Give up or propose a swap. Wages stay on the manager board.');
            }

            var openEl = document.getElementById('open-list');
            openEl.innerHTML = open.length
                ? open.map(function (s) { return renderShiftRow(s, 'open'); }).join('')
                : '<div class="empty">' + (isSweet ? 'Nothing open' : 'No open shifts.') + '</div>';

            var incoming = person ? S.openSwapsForPerson(state, person.id) : [];
            var incEl = document.getElementById('incoming-list');
            if (!incoming.length) {
                incEl.innerHTML = '<div class="empty">' + (isSweet ? 'No one has asked to swap yet' : 'No swap offers.') + '</div>';
            } else {
                incEl.innerHTML = incoming.map(function (r) {
                    var sh = S.findShift(state, r.weekKey, r.shiftId);
                    var tg = S.findShift(state, r.weekKey, r.targetShiftId);
                    return '<div class="req-row">' +
                        '<div>' + esc(r.fromName || 'Someone') + (isSweet ? ' wants to swap with you' : ' wants to swap') + '</div>' +
                        '<div class="req-meta">' +
                        esc(sh ? (sh.day + ' ' + S.fmtTime(sh.start) + '–' + S.fmtTime(sh.end) + ' · ' + (sh.name || '')) : '') +
                        ' ↔ ' +
                        esc(tg ? (tg.day + ' ' + S.fmtTime(tg.start) + '–' + S.fmtTime(tg.end) + ' · ' + (tg.name || '')) : '') +
                        (r.note ? ' · ' + esc(r.note) : '') +
                        '</div>' +
                        (canTrade()
                            ? '<div class="req-actions">' +
                              '<button type="button" class="btn btn-primary btn-small" data-accept="' + esc(r.id) + '">' + (isSweet ? 'Accept' : 'Accept') + '</button>' +
                              '<button type="button" class="btn btn-danger btn-small" data-decline="' + esc(r.id) + '">' + (isSweet ? 'No thanks' : 'Decline') + '</button>' +
                              '</div>'
                            : '') +
                        '</div>';
                }).join('');
            }

            var mineReq = person ? S.myRequests(state, person.id) : [];
            var reqEl = document.getElementById('mine-req-list');
            if (!mineReq.length) {
                reqEl.innerHTML = '<div class="empty">' + (isSweet ? 'None yet' : 'None yet.') + '</div>';
            } else {
                reqEl.innerHTML = mineReq.slice(0, 20).map(function (r) {
                    var canCancel = (r.status === 'open' || r.status === 'pending_approval') && r.fromPersonId === (person && person.id);
                    return '<div class="req-row">' +
                        '<div>' + esc(S.requestLabel(r, isSweet)) +
                        ' <span class="badge">' + esc(S.statusLabel(r.status, isSweet)) + '</span></div>' +
                        '<div class="req-meta">' + esc(r.weekKey) + (r.note ? ' · ' + esc(r.note) : '') + '</div>' +
                        (canCancel && canTrade()
                            ? '<div class="req-actions"><button type="button" class="btn btn-ghost btn-small" data-cancel="' + esc(r.id) + '">' + (isSweet ? 'Cancel' : 'Cancel') + '</button></div>'
                            : '') +
                        '</div>';
                }).join('');
            }
        }

        function applyPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            var ok = canView();
            document.getElementById('no-perm').hidden = ok;
            document.getElementById('app').hidden = !ok;
            if (ok) render();
        }

        document.getElementById('prev-week').addEventListener('click', function () { weekOffset--; render(); });
        document.getElementById('next-week').addEventListener('click', function () { weekOffset++; render(); });
        document.getElementById('me-save').addEventListener('click', function () {
            var id = document.getElementById('me-select').value;
            if (!id) {
                alert(isSweet ? 'Pick your name from the roster 💕' : 'Select your name from the roster.');
                return;
            }
            setMe(id);
            toast(isSweet ? 'Got it — that’s you 💕' : 'Saved');
        });

        document.getElementById('mine-list').addEventListener('click', function (e) {
            var g = e.target.closest('[data-giveup]');
            var sw = e.target.closest('[data-swap]');
            if (!g && !sw) return;
            if (!canTrade()) return;
            var person = mePerson();
            if (!person) return;
            if (g) {
                if (!confirm(isSweet ? 'Give up this shift? It’ll hit the open board (or the approval queue).' : 'Give up this shift?')) return;
                applyResult(
                    S.proposeGiveUp(state, {
                        weekKey: currentKey(),
                        shiftId: g.getAttribute('data-giveup'),
                        fromPersonId: person.id,
                        fromName: person.name,
                        actorName: person.name
                    }),
                    isSweet ? 'Shift is open ✨' : 'Shift is now open',
                    isSweet ? 'Sent for approval 💕' : 'Sent for approval'
                );
            }
            if (sw) openSwapModal(sw.getAttribute('data-swap'));
        });

        document.getElementById('open-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-claim]');
            if (!btn || !canTrade()) return;
            var person = mePerson();
            if (!person) {
                alert(isSweet ? 'Pick “I am…” first' : 'Select who you are first.');
                return;
            }
            if (!confirm(isSweet ? 'Claim this open shift?' : 'Claim this open shift?')) return;
            applyResult(
                S.proposeClaim(state, {
                    weekKey: currentKey(),
                    shiftId: btn.getAttribute('data-claim'),
                    fromPersonId: person.id,
                    fromName: person.name,
                    actorName: person.name
                }),
                isSweet ? 'It’s yours ✨' : 'Shift claimed',
                isSweet ? 'Claim sent for approval 💕' : 'Claim sent for approval'
            );
        });

        document.getElementById('incoming-list').addEventListener('click', function (e) {
            var acc = e.target.closest('[data-accept]');
            var dec = e.target.closest('[data-decline]');
            if (!acc && !dec) return;
            if (!canTrade()) return;
            var person = mePerson();
            var id = (acc || dec).getAttribute(acc ? 'data-accept' : 'data-decline');
            if (acc) {
                applyResult(
                    S.acceptSwap(state, id, person ? person.name : userLabel),
                    isSweet ? 'Swapped ✨' : 'Swap applied',
                    isSweet ? 'Accepted — waiting on manager 💕' : 'Accepted — pending approval'
                );
            } else {
                applyResult(
                    S.cancelRequest(state, id, person ? person.name : userLabel),
                    isSweet ? 'Declined' : 'Declined',
                    isSweet ? 'Declined' : 'Declined'
                );
            }
        });

        document.getElementById('mine-req-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-cancel]');
            if (!btn || !canTrade()) return;
            var person = mePerson();
            applyResult(
                S.cancelRequest(state, btn.getAttribute('data-cancel'), person ? person.name : userLabel),
                isSweet ? 'Cancelled' : 'Cancelled',
                isSweet ? 'Cancelled' : 'Cancelled'
            );
        });

        document.getElementById('swap-cancel').addEventListener('click', closeSwapModal);
        document.getElementById('swap-modal').addEventListener('click', function (e) {
            if (e.target === this) closeSwapModal();
        });
        document.getElementById('swap-go').addEventListener('click', function () {
            var person = mePerson();
            var target = document.getElementById('swap-target').value;
            if (!person || !swapFromId || !target) {
                alert(isSweet ? 'Pick a shift to swap with' : 'Select a shift to swap with.');
                return;
            }
            var res = S.proposeSwap(state, {
                weekKey: currentKey(),
                shiftId: swapFromId,
                targetShiftId: target,
                fromPersonId: person.id,
                fromName: person.name,
                note: document.getElementById('swap-note').value.trim(),
                actorName: person.name
            });
            closeSwapModal();
            applyResult(
                res,
                isSweet ? 'Swapped ✨' : 'Swap sent',
                isSweet ? 'Swap sent — waiting on them 💕' : 'Swap sent — waiting on teammate'
            );
        });

        fillMeSelect();
        S.pullMeFromPrefs(function (id) {
            if (id) meId = id;
            fillMeSelect();
            render();
        });
        render();
        schedSync = S.wire({
            getState: function () { return state; },
            setState: function (next) { state = next; render(); },
            statusEl: 'sched-sync-pill'
        });

        if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyPerms);
        document.addEventListener('pbj-perms-ready', applyPerms);
    })();
    </script>
</body>
</html>
