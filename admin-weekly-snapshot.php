<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$mail_ready = function_exists('pbj_mail_is_configured') && pbj_mail_is_configured();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Weekly Owner Snapshot' : 'Weekly Owner Snapshot'; ?> · <?php echo pbj_hub_label('admin'); ?> · ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.2rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 14px; line-height: 1.45; }
        .hint a { color: inherit; font-weight: 600; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; align-items: center; }
        .chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .range-label { font-size: 0.92rem; opacity: 0.75; margin-left: auto; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 12px; }
        @media (max-width: 520px) { .stats-row { grid-template-columns: 1fr 1fr; } }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.2rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .num.ok { color: #1F6B4A; }
        .stat .num.warn { color: #C62828; }
        .stat .lbl { font-size: 0.8rem; opacity: 0.7; margin-top: 4px; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 10px; }
        .empty { text-align: center; padding: 18px 10px; opacity: 0.8; line-height: 1.45; }
        .empty a { color: inherit; font-weight: 600; }
        .row { display: flex; justify-content: space-between; gap: 10px; align-items: baseline; padding: 8px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; font-size: 0.95rem; }
        .row:last-child { border-bottom: none; }
        .row .name { flex: 1; min-width: 0; }
        .row .meta { opacity: 0.7; font-size: 0.85rem; white-space: nowrap; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.75rem; margin-left: 6px; }
        .badge.high { background: #FDECEA; color: #B71C1C; }
        .badge.watch { background: #FFF3E0; color: #8A5A12; }
        .badge.ok { background: #E8F8F1; color: #1F6B4A; }
        .badge.s86 { background: #FFCDD2; color: #B71C1C; }
        .badge.slow { background: #FFF3E0; color: #8A5A12; }
        .actions-bar { display: flex; gap: 10px; margin-top: 4px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn:disabled { opacity: 0.5; cursor: default; }
        .opt-row { display: flex; align-items: flex-start; gap: 10px; margin-top: 12px; font-size: 0.95rem; line-height: 1.4; }
        .opt-row input { margin-top: 3px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; max-width: 90vw; text-align: center; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .denied { background: white; border-radius: 18px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); margin-bottom: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports & Sales' : 'Back to Reports & Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'Weekly Owner Snapshot' : 'Weekly Owner Snapshot'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Monday pulse — what matters, one screen' : 'One-screen weekly pulse for owners &amp; GMs'; ?></p>
    </div>
    <div class="content">
        <div class="denied" id="denied" hidden><?php echo $is_sweet
            ? 'This snapshot is for owners, GMs, and anyone with Reports view. Ask your house lead if you need access 💕'
            : 'Restricted to owners/GMs with Reports view permission.'; ?></div>

        <div id="snap-body">
            <p class="hint"><?php echo $is_sweet
                ? 'Pulls from <a href="/admin/sales">Daily Sales</a>, <a href="/admin/labor">Labor</a>, <a href="/BOH/86">86 Board</a>, checklists, and <a href="/BOH/menu">Menu</a> plate costs when those are filled in. Empty sections just mean that data isn\'t in the house yet — we never invent numbers ✨'
                : 'Uses Daily Sales, Labor, 86 Board, checklist completions, and menu plate costs when present. Empty = no data yet (nothing invented).'; ?></p>

            <div class="toolbar">
                <button type="button" class="chip active" data-range="lastWeek" id="chip-week"><?php echo $is_sweet ? 'Last week' : 'Last week'; ?></button>
                <button type="button" class="chip" data-range="rolling7" id="chip-roll"><?php echo $is_sweet ? 'Last 7 days' : 'Last 7 days'; ?></button>
                <span class="range-label" id="range-label">—</span>
            </div>

            <div class="stats-row">
                <div class="stat"><div class="num" id="stat-sales">—</div><div class="lbl"><?php echo $is_sweet ? 'Net sales' : 'Net sales'; ?></div></div>
                <div class="stat"><div class="num" id="stat-labor">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor $' : 'Labor $'; ?></div></div>
                <div class="stat"><div class="num" id="stat-pct">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor %' : 'Labor %'; ?></div></div>
            </div>

            <div class="card">
                <h2><?php echo $is_sweet ? 'Food cost % · dishes' : 'Food cost % · dishes'; ?></h2>
                <div id="dishes-wrap"></div>
            </div>

            <div class="card">
                <h2><?php echo $is_sweet ? '86 board right now' : '86 board (current)'; ?></h2>
                <div id="eighty-wrap"></div>
            </div>

            <div class="card">
                <h2><?php echo $is_sweet ? 'Checklist completions' : 'Checklist completions'; ?></h2>
                <div id="lists-wrap"></div>
            </div>

            <div class="card">
                <h2><?php echo $is_sweet ? 'Email this pulse' : 'Email this pulse'; ?></h2>
                <p class="hint" style="margin-top:0;"><?php echo $mail_ready
                    ? ($is_sweet ? 'SMTP is live — email yourself this week\'s snapshot, or opt into Monday morning delivery 💌' : 'SMTP configured. Email yourself now, or opt into Monday delivery.')
                    : ($is_sweet ? 'Server mail isn\'t fully set up yet — you can still open this page anytime. Opt-in is ready for when SMTP is live.' : 'Outbound mail not fully configured yet. Page works; weekly cron will send once SMTP is set.'); ?></p>
                <div class="actions-bar">
                    <button type="button" class="btn btn-primary" id="btn-email-me" <?php echo $mail_ready ? '' : 'disabled'; ?>><?php echo $is_sweet ? '📧 Email me this' : 'Email me this'; ?></button>
                    <a class="btn btn-secondary" href="/admin/trends"><?php echo $is_sweet ? 'Weekly Trends' : 'Weekly Trends'; ?></a>
                </div>
                <label class="opt-row">
                    <input type="checkbox" id="opt-weekly-email">
                    <span><?php echo $is_sweet
                        ? 'Email me this snapshot every Monday morning (owners &amp; GMs on the house)'
                        : 'Email this snapshot every Monday morning to house owners/GMs'; ?></span>
                </label>
            </div>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/shared-state.js?v=1"></script>
    <script src="/food-cost-shared.js?v=4"></script>
    <script src="/86-board-shared.js?v=1"></script>
    <script src="/weekly-owner-snapshot.js?v=1"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var mailReady = <?php echo $mail_ready ? 'true' : 'false'; ?>;
        var rangeMode = 'lastWeek';
        var lastSnap = null;
        var S = window.PbjWeeklyOwnerSnapshot;

        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return true;
        }
        function canView() {
            var r = window.PbjPerms && window.PbjPerms.role;
            if (r === 'owner' || r === 'gm' || r === 'admin') return true;
            return canP('admin.reports.view') || canP('admin.reports.edit');
        }
        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 2800);
        }
        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function money(n) { return S ? S.money(n) : '—'; }
        function money2(n) { return S ? S.money2(n) : '—'; }
        function pct(n) { return S ? S.pct(n) : '—'; }

        function renderDishes(dishes) {
            var wrap = document.getElementById('dishes-wrap');
            if (!dishes || !dishes.worst || !dishes.worst.length) {
                wrap.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No costed dishes yet — add sell prices on <a href="/BOH/menu">Menu</a> and ingredients on <a href="/admin/costing">Costing</a> 💕'
                    : 'No costed dishes yet. Need menu prices + recipe plate costs. See <a href="/BOH/menu">Menu</a> / <a href="/admin/costing">Costing</a>.') + '</div>';
                return;
            }
            var html = '<p class="hint" style="margin-top:0;">' + (isSweet ? 'Highest food-cost % (watch these)' : 'Highest food-cost %') + '</p>';
            dishes.worst.forEach(function (r) {
                html += '<div class="row"><div class="name">' + esc(r.name) +
                    '<span class="badge ' + esc(r.band) + '">' + pct(r.fc) + '</span></div>' +
                    '<div class="meta">' + money2(r.plate) + ' / ' + money2(r.sell) + '</div></div>';
            });
            if (dishes.best && dishes.best.length) {
                html += '<p class="hint" style="margin-top:14px;">' + (isSweet ? 'Best FC% (keepers)' : 'Lowest food-cost %') + '</p>';
                dishes.best.forEach(function (r) {
                    html += '<div class="row"><div class="name">' + esc(r.name) +
                        '<span class="badge ok">' + pct(r.fc) + '</span></div>' +
                        '<div class="meta">' + money2(r.plate) + ' / ' + money2(r.sell) + '</div></div>';
                });
            }
            wrap.innerHTML = html;
        }

        function render86(board) {
            var wrap = document.getElementById('eighty-wrap');
            if (!board || !board.total) {
                wrap.innerHTML = '<div class="empty">' + (isSweet
                    ? 'Board is clear — nothing 86\'d right now ✨'
                    : 'Nothing currently on the 86 board.') +
                    ' <a href="/BOH/86">' + (isSweet ? 'Open 86 Board' : 'Open 86 Board') + '</a></div>';
                return;
            }
            var html = '<p class="hint" style="margin-top:0;">' + board.count86 + ' 86 · ' + board.countLow + ' low</p>';
            board.items.forEach(function (it) {
                var badge = it.status === 'low' ? 'slow' : 's86';
                var label = it.status === 'low' ? 'low' : '86';
                html += '<div class="row"><div class="name">' + esc(it.name) +
                    '<span class="badge ' + badge + '">' + label + '</span></div>' +
                    '<div class="meta">' + esc(it.note || '') + '</div></div>';
            });
            wrap.innerHTML = html;
        }

        function renderLists(lists) {
            var wrap = document.getElementById('lists-wrap');
            if (!lists || !lists.hasData) {
                wrap.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No checklist completions recorded in this range yet. When lists finish, they land here 💕'
                    : 'No checklist completions in this range.') +
                    ' <a href="/admin/checklist-overview">' + (isSweet ? 'Checklist Overview' : 'Overview') + '</a></div>';
                return;
            }
            var html = '<p class="hint" style="margin-top:0;">' + lists.completions + ' completion' +
                (lists.completions === 1 ? '' : 's') + ' across ' + lists.daysWithCompletions + ' day(s)</p>';
            (lists.topLists || []).forEach(function (t) {
                html += '<div class="row"><div class="name">' + esc(t.title) + '</div><div class="meta">×' + t.count + '</div></div>';
            });
            wrap.innerHTML = html;
        }

        function paint() {
            if (!S) return;
            lastSnap = S.buildSnapshot({ range: rangeMode === 'rolling7' ? 'rolling7' : 'lastWeek' });
            document.getElementById('range-label').textContent =
                lastSnap.range.start + ' → ' + lastSnap.range.end;

            var sl = lastSnap.salesLabor;
            var salesEl = document.getElementById('stat-sales');
            var laborEl = document.getElementById('stat-labor');
            var pctEl = document.getElementById('stat-pct');
            salesEl.textContent = sl.hasSales ? money(sl.totalSales) : '—';
            laborEl.textContent = sl.hasLabor ? money(sl.totalLabor) : '—';
            if (sl.laborPct != null) {
                pctEl.textContent = pct(sl.laborPct);
                pctEl.className = 'num ' + (sl.laborPct > 35 ? 'warn' : 'ok');
            } else {
                pctEl.textContent = '—';
                pctEl.className = 'num';
            }

            renderDishes(lastSnap.dishes);
            render86(lastSnap.eightySix);
            renderLists(lastSnap.checklists);
        }

        function applyPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            if (!canView()) {
                document.getElementById('denied').hidden = false;
                document.getElementById('snap-body').hidden = true;
                return;
            }
            document.getElementById('denied').hidden = true;
            document.getElementById('snap-body').hidden = false;
            paint();
        }

        document.querySelectorAll('.chip[data-range]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                rangeMode = btn.getAttribute('data-range');
                document.querySelectorAll('.chip[data-range]').forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                });
                paint();
            });
        });

        document.getElementById('btn-email-me').addEventListener('click', function () {
            if (!mailReady || !lastSnap || !S) return;
            var btn = document.getElementById('btn-email-me');
            btn.disabled = true;
            var body = S.formatEmailBody(lastSnap, '', isSweet);
            fetch('/weekly-snapshot-api.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'email_me', body: body, range: lastSnap.range })
            }).then(function (r) { return r.json(); }).then(function (data) {
                btn.disabled = !mailReady;
                if (data && data.ok) toast(isSweet ? 'Sent to your inbox 💌' : 'Email sent');
                else toast((data && data.hint) || (isSweet ? 'Couldn\'t send — try again' : 'Send failed'));
            }).catch(function () {
                btn.disabled = !mailReady;
                toast(isSweet ? 'Network hiccup' : 'Network error');
            });
        });

        function loadOptIn() {
            fetch('/weekly-snapshot-api.php?action=get', { credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data.ok) {
                        document.getElementById('opt-weekly-email').checked = !!data.weeklyEmail;
                    }
                }).catch(function () {});
        }

        document.getElementById('opt-weekly-email').addEventListener('change', function () {
            var on = !!document.getElementById('opt-weekly-email').checked;
            fetch('/weekly-snapshot-api.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'set_opt_in', weeklyEmail: on })
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data && data.ok) {
                    toast(on
                        ? (isSweet ? 'Monday email on ✨' : 'Monday email enabled')
                        : (isSweet ? 'Monday email off' : 'Monday email disabled'));
                } else {
                    document.getElementById('opt-weekly-email').checked = !on;
                    toast((data && data.hint) || (isSweet ? 'Couldn\'t save' : 'Save failed'));
                }
            }).catch(function () {
                document.getElementById('opt-weekly-email').checked = !on;
                toast(isSweet ? 'Network hiccup' : 'Network error');
            });
        });

        if (window.PbjPerms && window.PbjPerms.ready) {
            window.PbjPerms.ready.then(applyPerms);
        } else {
            document.addEventListener('pbj-perms-ready', applyPerms);
            setTimeout(applyPerms, 800);
        }
        loadOptIn();
    })();
    </script>
</body>
</html>
