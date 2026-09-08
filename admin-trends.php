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
    <title><?php echo $is_sweet ? 'Weekly Trends' : 'Weekly Trends'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
        .stats-row.three { grid-template-columns: repeat(3, 1fr); }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.25rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.82rem; opacity: 0.7; margin-top: 4px; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .day-row { display: grid; grid-template-columns: 72px 1fr 70px; gap: 8px; align-items: center; margin-bottom: 10px; font-size: 0.92rem; }
        .bar-wrap { height: 14px; border-radius: 999px; overflow: hidden; <?php if ($is_sweet): ?>background: #FFF5F6;<?php else: ?>background: #EEF2F8;<?php endif; ?> }
        .bar { height: 100%; border-radius: 999px; min-width: 0; <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .bar.labor { <?php if ($is_sweet): ?>background: #F3A6B0;<?php else: ?>background: #7A8FA8;<?php endif; ?> }
        .legend { display: flex; flex-wrap: wrap; gap: 12px; font-size: 0.85rem; opacity: 0.75; margin-bottom: 12px; }
        .legend span::before { content: ''; display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: middle; }
        .legend .sales::before { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .legend .labor::before { <?php if ($is_sweet): ?>background: #F3A6B0;<?php else: ?>background: #7A8FA8;<?php endif; ?> }
        .table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        .table th, .table td { text-align: left; padding: 8px 5px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .table th { opacity: 0.65; font-weight: normal; }
        .empty { text-align: center; padding: 24px; opacity: 0.8; line-height: 1.4; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 100px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .ok { color: #1F6B4A; }
        .warn { color: #C62828; }
        .muted { opacity: 0.55; }
        .split { font-size: 0.82rem; opacity: 0.7; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports & Sales' : 'Back to Reports & Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'Weekly Trends' : 'Weekly Trends'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Sales + saved labor at a glance' : 'Sales and saved labor snapshot'; ?></p>
    </div>
    <div class="content">
        <p class="hint"><?php echo $is_sweet
            ? 'Last 7 days from <a href="/admin/sales" style="color:inherit;font-weight:600;">Daily Sales</a>, <a href="/admin/labor" style="color:inherit;font-weight:600;">Labor Snapshot</a> (saved days — schedule now, POS clocks/labor $ later), comps & more. Want a statement view? Open <a href="/admin/pnl" style="color:inherit;font-weight:600;">P&amp;L</a> 📊'
            : 'Last 7 days from Daily Sales, saved Labor Snapshot (manual now · POS punches later), comps, and more. See also <a href="/admin/pnl" style="color:inherit;font-weight:600;">P&amp;L Statement</a>.'; ?></p>

        <div class="stats-row three">
            <div class="stat"><div class="num" id="stat-sales">—</div><div class="lbl"><?php echo $is_sweet ? 'Net sales (7d)' : 'Net sales (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-labor">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor $ (7d)' : 'Labor $ (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-pct">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor %' : 'Labor %'; ?></div></div>
        </div>
        <div class="stats-row three">
            <div class="stat"><div class="num" id="stat-hours">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor hours (7d)' : 'Labor hours (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-covers">—</div><div class="lbl"><?php echo $is_sweet ? 'Covers (7d)' : 'Covers (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-avg">—</div><div class="lbl"><?php echo $is_sweet ? 'Avg check' : 'Avg check'; ?></div></div>
        </div>
        <div class="stats-row three">
            <div class="stat"><div class="num" id="stat-foh">—</div><div class="lbl"><?php echo $is_sweet ? 'FOH hours' : 'FOH hours'; ?></div></div>
            <div class="stat"><div class="num" id="stat-boh">—</div><div class="lbl"><?php echo $is_sweet ? 'BOH hours' : 'BOH hours'; ?></div></div>
            <div class="stat"><div class="num" id="stat-comps">—</div><div class="lbl"><?php echo $is_sweet ? 'Comps/voids (7d)' : 'Comps/voids (7d)'; ?></div></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Sales by day' : 'Sales by day'; ?></h2>
            <div class="legend"><span class="sales"><?php echo $is_sweet ? 'Net sales' : 'Net sales'; ?></span></div>
            <div id="sales-bars"></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Labor hours by day' : 'Labor hours by day'; ?></h2>
            <p class="hint" style="margin-top:-4px;"><?php echo $is_sweet
                ? 'From saved Labor Snapshot days (actual times). Empty days mean nothing saved yet ⏱️'
                : 'From saved Labor Snapshot days (actual hours).'; ?></p>
            <div class="legend"><span class="labor"><?php echo $is_sweet ? 'Actual hours' : 'Actual hours'; ?></span></div>
            <div id="labor-bars"></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Day-by-day' : 'Day-by-day'; ?></h2>
            <div id="table-wrap" style="overflow-x:auto;"></div>
        </div>

        <div class="actions-bar">
            <a href="/admin/pnl" class="btn btn-secondary"><?php echo $is_sweet ? '📋 P&amp;L' : 'P&amp;L'; ?></a>
            <a href="/admin/sales" class="btn btn-secondary"><?php echo $is_sweet ? '📈 Sales' : 'Sales'; ?></a>
            <a href="/admin/labor" class="btn btn-secondary"><?php echo $is_sweet ? '⏱️ Labor' : 'Labor'; ?></a>
            <a href="/admin/reports" class="btn btn-primary"><?php echo $is_sweet ? 'Reports' : 'Reports'; ?></a>
        </div>
    </div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
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

        function money(n) { if (n == null || isNaN(n)) return '—'; return '$' + (Math.round(n * 100) / 100).toFixed(2); }
        function num(v) { if (v === '' || v == null) return null; var n = parseFloat(v); return isNaN(n) ? null : n; }
        function pct(n) { if (n == null || isNaN(n)) return '—'; return (Math.round(n * 10) / 10).toFixed(1) + '%'; }
        function hrs(n) {
            if (n == null || isNaN(n)) return '—';
            return (Math.round(n * 10) / 10) + 'h';
        }
        function loadJson(key, fallback) {
            try { var r = JSON.parse(localStorage.getItem(key) || 'null'); return r || fallback; } catch (e) { return fallback; }
        }
        function dateStr(d) {
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }
        function last7Dates() {
            var out = [];
            for (var i = 6; i >= 0; i--) {
                var d = new Date(); d.setHours(12,0,0,0); d.setDate(d.getDate() - i);
                out.push(dateStr(d));
            }
            return out;
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

        // Sales: prefer v2
        var sales = loadJson('pbj_admin_sales_v2', null);
        if (!sales || !Array.isArray(sales.days)) sales = loadJson('pbj_admin_sales_v1', { days: [] });

        // Labor: prefer v2 (saved labor days with entries), fall back v1
        var labor = loadJson('pbj_admin_labor_v2', null);
        if (!labor || !Array.isArray(labor.days)) labor = loadJson('pbj_admin_labor_v1', { days: [] });
        if (!Array.isArray(labor.days)) labor.days = [];

        var comps = loadJson('pbj_admin_comps_v1', { entries: [] });
        if (!Array.isArray(comps.entries)) comps.entries = [];

        function netOf(d) {
            if (!d) return null;
            var n = num(d.net);
            if (n != null) return n;
            return num(d.gross);
        }

        /** Actual hours from a saved labor day (entries preferred) */
        function laborHoursOf(d) {
            if (!d) return null;
            if (Array.isArray(d.entries) && d.entries.length) {
                var h = 0;
                d.entries.forEach(function (e) {
                    var start = e.actualStart || e.schedStart || e.start || '';
                    var end = e.actualEnd || e.schedEnd || e.end || '';
                    h += hoursBetween(start, end, e.breakMins);
                });
                return Math.round(h * 100) / 100;
            }
            return num(d.hours);
        }

        function laborCostOf(d) {
            if (!d) return null;
            return num(d.cost) != null ? num(d.cost) : num(d.labor);
        }

        function laborFohOf(d) {
            if (!d) return null;
            if (num(d.foh) != null) return num(d.foh);
            return null;
        }
        function laborBohOf(d) {
            if (!d) return null;
            if (num(d.boh) != null) return num(d.boh);
            return null;
        }

        function laborPeopleOf(d) {
            if (!d || !Array.isArray(d.entries)) return null;
            return d.entries.length || null;
        }

        var salesByDate = {};
        (sales.days || []).forEach(function (d) { if (d && d.date) salesByDate[d.date] = d; });
        var laborByDate = {};
        (labor.days || []).forEach(function (d) { if (d && d.date) laborByDate[d.date] = d; });

        function laborCostForDate(date) {
            var l = laborByDate[date];
            var c = laborCostOf(l);
            if (c != null) return c;
            // Optional: labor $ typed on sales day
            var s = salesByDate[date];
            return s ? num(s.labor) : null;
        }

        var dates = last7Dates();
        var totalSales = 0, salesN = 0;
        var totalLabor$ = 0, laborCostN = 0;
        var totalHours = 0, hoursN = 0;
        var totalFoh = 0, fohN = 0, totalBoh = 0, bohN = 0;
        var covers = 0, checks = 0;
        var maxSales = 0, maxHours = 0;

        var dayData = dates.map(function (date) {
            var s = salesByDate[date];
            var l = laborByDate[date];
            var n = netOf(s);
            var lab$ = laborCostForDate(date);
            var labH = laborHoursOf(l);
            var foh = laborFohOf(l);
            var boh = laborBohOf(l);
            var people = laborPeopleOf(l);
            var cov = s ? num(s.covers) : null;
            var chk = s ? num(s.checks) : null;

            if (n != null) { totalSales += n; salesN++; if (n > maxSales) maxSales = n; }
            if (lab$ != null) { totalLabor$ += lab$; laborCostN++; }
            if (labH != null) { totalHours += labH; hoursN++; if (labH > maxHours) maxHours = labH; }
            if (foh != null) { totalFoh += foh; fohN++; }
            if (boh != null) { totalBoh += boh; bohN++; }
            if (cov != null) covers += cov;
            if (chk != null) checks += chk;

            return {
                date: date,
                net: n,
                labor$: lab$,
                hours: labH,
                foh: foh,
                boh: boh,
                people: people,
                covers: cov,
                checks: chk,
                hasLaborSave: !!l
            };
        });

        var weekCut = new Date(); weekCut.setDate(weekCut.getDate() - 6);
        var compTotal = 0;
        (comps.entries || []).forEach(function (e) {
            if (e && e.date && new Date(e.date + 'T12:00:00') >= weekCut) {
                compTotal += (num(e.amount) || 0);
            }
        });

        document.getElementById('stat-sales').textContent = salesN ? money(totalSales) : '—';
        document.getElementById('stat-labor').textContent = laborCostN ? money(totalLabor$) : '—';
        var laborPct = (salesN && totalSales > 0 && laborCostN) ? (totalLabor$ / totalSales * 100) : null;
        var pctEl = document.getElementById('stat-pct');
        pctEl.textContent = pct(laborPct);
        pctEl.className = 'num' + (laborPct != null && laborPct > 35 ? ' warn' : (laborPct != null ? ' ok' : ''));
        document.getElementById('stat-hours').textContent = hoursN ? hrs(totalHours) : '—';
        document.getElementById('stat-covers').textContent = covers ? String(covers) : '—';
        var avgDenom = checks || covers;
        document.getElementById('stat-avg').textContent = (salesN && avgDenom) ? money(totalSales / avgDenom) : '—';
        document.getElementById('stat-foh').textContent = fohN ? hrs(totalFoh) : '—';
        document.getElementById('stat-boh').textContent = bohN ? hrs(totalBoh) : '—';
        document.getElementById('stat-comps').textContent = (comps.entries && comps.entries.length) ? money(compTotal) : '—';

        // Sales bars
        var bars = document.getElementById('sales-bars');
        if (!salesN) {
            bars.innerHTML = '<div class="empty">' + (isSweet ? 'Log a few days under Daily Sales to see bars 📈' : 'Log Daily Sales to see trends.') + '</div>';
        } else {
            bars.innerHTML = dayData.map(function (d) {
                var label = new Date(d.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'numeric', day: 'numeric' });
                var w = (d.net != null && maxSales > 0) ? Math.round((d.net / maxSales) * 100) : 0;
                return '<div class="day-row"><span>' + label + '</span>' +
                    '<div class="bar-wrap"><div class="bar" style="width:' + w + '%"></div></div>' +
                    '<span style="text-align:right;">' + money(d.net) + '</span></div>';
            }).join('');
        }

        // Labor hour bars from saved labor days
        var laborBars = document.getElementById('labor-bars');
        if (!hoursN) {
            laborBars.innerHTML = '<div class="empty">' + (isSweet
                ? 'Save labor days under Labor Snapshot (pull schedule → edit actuals → save) to chart hours ⏱️'
                : 'Save labor days under Labor Snapshot to chart hours.') + '</div>';
        } else {
            laborBars.innerHTML = dayData.map(function (d) {
                var label = new Date(d.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'numeric', day: 'numeric' });
                var w = (d.hours != null && maxHours > 0) ? Math.round((d.hours / maxHours) * 100) : 0;
                var right = d.hours != null ? hrs(d.hours) : '—';
                return '<div class="day-row"><span class="' + (d.hasLaborSave ? '' : 'muted') + '">' + label + '</span>' +
                    '<div class="bar-wrap"><div class="bar labor" style="width:' + w + '%"></div></div>' +
                    '<span style="text-align:right;">' + right + '</span></div>';
            }).join('');
        }

        // Day-by-day table
        var wrap = document.getElementById('table-wrap');
        wrap.innerHTML = '<table class="table"><thead><tr>' +
            '<th>Day</th><th>Net</th><th>Labor $</th><th>Labor %</th><th>Hours</th><th>People</th><th>Covers</th>' +
            '</tr></thead><tbody>' +
            dayData.map(function (d) {
                var label = new Date(d.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                var lp = (d.net != null && d.net > 0 && d.labor$ != null) ? (d.labor$ / d.net * 100) : null;
                var lpCls = lp != null && lp > 35 ? 'warn' : (lp != null ? 'ok' : '');
                var hourTxt = d.hours != null ? hrs(d.hours) : '—';
                if (d.foh != null || d.boh != null) {
                    hourTxt += '<div class="split">F ' + (d.foh != null ? hrs(d.foh) : '—') +
                        ' · B ' + (d.boh != null ? hrs(d.boh) : '—') + '</div>';
                }
                return '<tr><td>' + label + '</td><td>' + money(d.net) + '</td><td>' + money(d.labor$) +
                    '</td><td class="' + lpCls + '">' + pct(lp) + '</td><td>' + hourTxt +
                    '</td><td>' + (d.people != null ? d.people : '—') +
                    '</td><td>' + (d.covers != null ? d.covers : '—') + '</td></tr>';
            }).join('') +
            '</tbody></table>';
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRepPerms);
            document.addEventListener('pbj-perms-ready', applyRepPerms);
})();
    </script>
</body>
</html>
