<?php
/**
 * New-house setup checklist — connect POS, first sync, opex, cash close, invoice costs.
 */
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'House setup' : 'House setup'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.2rem; margin: 0; }
        .subtitle { margin: 10px 0 0; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0 0 10px; }
        .hint { font-size: 0.92rem; opacity: 0.75; line-height: 1.45; margin: 0 0 12px; }
        .progress { height: 10px; border-radius: 999px; background: <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; overflow: hidden; margin-bottom: 8px; }
        .progress > span { display: block; height: 100%; border-radius: 999px; <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> width: 0; transition: width 0.3s; }
        .step { display: flex; gap: 12px; align-items: flex-start; padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .step:last-child { border-bottom: none; }
        .step .check {
            width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 700;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
        }
        .step.done .check { <?php if ($is_sweet): ?>background: #E8F8F1; border-color: #B6E5CF; color: #1F6B4A;<?php else: ?>background: #EAF1FA; border-color: #C5D0DE; color: #1A2A44;<?php endif; ?> }
        .step .body { flex: 1; min-width: 0; }
        .step h3 { margin: 0 0 4px; font-size: 1.05rem; }
        .step p { margin: 0 0 8px; font-size: 0.9rem; opacity: 0.75; line-height: 1.4; }
        .btn { border: none; border-radius: 14px; padding: 10px 14px; font-size: 0.95rem; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> color: inherit; }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .stat { text-align: center; font-size: 1.1rem; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin" class="back-link">← <?php echo $is_sweet ? 'Back to Admin' : 'Back to Admin'; ?></a>
        <h1><?php echo $is_sweet ? 'House setup' : 'House setup'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Get money, labor & costs flowing 💕' : 'Get sales, labor, and costs flowing'; ?></p>
    </div>
    <div class="content">
        <div class="card">
            <div class="stat" id="progress-label">—</div>
            <div class="progress"><span id="progress-bar"></span></div>
            <p class="hint" id="progress-hint" style="margin:0;"><?php echo $is_sweet ? 'Checks update live from your house data' : 'Checks update from house data'; ?></p>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Launch checklist' : 'Launch checklist'; ?></h2>
            <div id="steps"></div>
        </div>
        <div class="btn-row">
            <a href="/home" class="btn btn-secondary"><?php echo $is_sweet ? 'Home / Daily Pulse' : 'Home'; ?></a>
            <a href="/admin/reports" class="btn btn-primary"><?php echo $is_sweet ? 'Reports hub' : 'Reports'; ?></a>
            <button type="button" class="btn btn-ghost" id="btn-dismiss"><?php echo $is_sweet ? 'Hide from home' : 'Hide from home'; ?></button>
        </div>
    </div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/ops-nudges.js?v=3"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var DISMISS_KEY = 'pbj_house_setup_dismissed_v1';

        function loadJson(key, fallback) {
            try {
                var r = JSON.parse(localStorage.getItem(key) || 'null');
                return r != null ? r : fallback;
            } catch (e) { return fallback; }
        }

        function hasSales() {
            var s = loadJson('pbj_admin_sales_v2', null) || loadJson('pbj_admin_sales_v1', { days: [] });
            return s && Array.isArray(s.days) && s.days.some(function (d) {
                return d && (parseFloat(d.net) > 0 || parseFloat(d.gross) > 0 || d.source === 'pos');
            });
        }
        function hasLabor() {
            var l = loadJson('pbj_admin_labor_v2', null) || loadJson('pbj_admin_labor_v1', { days: [] });
            return l && Array.isArray(l.days) && l.days.length > 0;
        }
        function hasOpex() {
            var p = loadJson('pbj_admin_pnl_v1', null);
            if (!p || !p.expenses) return false;
            return Object.keys(p.expenses).some(function (k) {
                var e = p.expenses[k];
                return e && e.monthly != null && parseFloat(e.monthly) > 0;
            });
        }
        function hasCash() {
            var c = loadJson('pbj_admin_cash_v1', { days: [] });
            return (c.days || []).some(function (d) {
                return d && !isNaN(parseFloat(d.expected)) && !isNaN(parseFloat(d.counted));
            });
        }
        function hasInvoiceApplied() {
            var a = loadJson('pbj_invoice_cost_applied_v1', {});
            return Object.keys(a).some(function (k) { return a[k] && a[k].at; });
        }
        function hasTeam() {
            var t = loadJson('pbj_admin_team_v2', null) || loadJson('pbj_admin_team_v1', null);
            return t && Array.isArray(t.people) && t.people.length > 0;
        }
        function hasSchedule() {
            var s = loadJson('pbj_admin_schedules_v1', null);
            if (!s || !s.weeks) return false;
            return Object.keys(s.weeks).some(function (k) {
                return Array.isArray(s.weeks[k]) && s.weeks[k].length > 0;
            });
        }

        var steps = [
            {
                id: 'team',
                title: isSweet ? 'Add your team' : 'Add your team',
                body: isSweet ? 'Names, roles, and $/hr on Team roster — powers schedules & labor $.' : 'Roster with roles and wages.',
                href: '/admin/roster',
                cta: isSweet ? 'Open Team' : 'Team',
                done: hasTeam
            },
            {
                id: 'schedule',
                title: isSweet ? 'Build a week schedule' : 'Build a schedule',
                body: isSweet ? 'Drop shifts on the board so Labor can compare schedule vs actual.' : 'Add shifts for schedule vs actual.',
                href: '/admin/schedules',
                cta: isSweet ? 'Open Schedules' : 'Schedules',
                done: hasSchedule
            },
            {
                id: 'pos',
                title: isSweet ? 'Connect a POS (or import CSV)' : 'Connect POS or CSV',
                body: isSweet ? 'Square / Clover OAuth, Toast GUID, or CSV presets — sales land on Daily Sales.' : 'Live POS or CSV into Daily Sales.',
                href: '/admin/pos-connect',
                cta: isSweet ? 'POS Connections' : 'POS',
                done: function () {
                    return hasSales();
                }
            },
            {
                id: 'sync',
                title: isSweet ? 'Log or sync first sales + labor' : 'First sales + labor',
                body: isSweet ? 'Sync POS or save a sales day and a labor day so Pulse & P&L light up.' : 'At least one sales day and one labor day.',
                href: '/admin/sales',
                cta: isSweet ? 'Daily Sales' : 'Sales',
                done: function () { return hasSales() && hasLabor(); }
            },
            {
                id: 'opex',
                title: isSweet ? 'Set monthly opex on P&L' : 'Set monthly opex',
                body: isSweet ? 'Rent, utilities, marketing… prorates into each period automatically.' : 'Enter monthly operating expenses.',
                href: '/admin/pnl',
                cta: isSweet ? 'P&L Statement' : 'P&L',
                done: hasOpex
            },
            {
                id: 'cash',
                title: isSweet ? 'Close the drawer once' : 'First cash close',
                body: isSweet ? 'Expected + counted cash — becomes the nightly habit Pulse nags about.' : 'Log expected and counted cash.',
                href: '/admin/cash',
                cta: isSweet ? 'Cash & Deposits' : 'Cash',
                done: hasCash
            },
            {
                id: 'invoice',
                title: isSweet ? 'Apply costs from one invoice' : 'Apply one invoice',
                body: isSweet ? 'Upload a delivery ticket and Apply costs so recipes re-price.' : 'Upload invoice and apply costs.',
                href: '/admin/invoices',
                cta: isSweet ? 'Invoices' : 'Invoices',
                done: hasInvoiceApplied
            }
        ];

        // POS connect "done" softer: also check if they only did manual sales
        steps[2].done = function () {
            // Consider done if sales exist OR we can't know connect — sales is the outcome
            return hasSales();
        };

        function paint() {
            var doneN = 0;
            var root = document.getElementById('steps');
            root.innerHTML = steps.map(function (s) {
                var ok = false;
                try { ok = !!s.done(); } catch (e) { ok = false; }
                if (ok) doneN++;
                return '<div class="step' + (ok ? ' done' : '') + '" data-id="' + s.id + '">' +
                    '<div class="check">' + (ok ? '✓' : (doneN + 1)) + '</div>' +
                    '<div class="body">' +
                    '<h3>' + s.title + '</h3>' +
                    '<p>' + s.body + '</p>' +
                    (ok
                        ? '<span class="btn btn-ghost" style="opacity:0.7;pointer-events:none;">' + (isSweet ? 'Done' : 'Done') + '</span>'
                        : '<a class="btn btn-primary" href="' + s.href + '">' + s.cta + '</a>') +
                    '</div></div>';
            }).join('');
            // fix check numbers for incomplete
            var n = 0;
            root.querySelectorAll('.step').forEach(function (el) {
                if (el.classList.contains('done')) return;
                n++;
                var c = el.querySelector('.check');
                if (c) c.textContent = String(n);
            });
            var pct = steps.length ? Math.round(doneN / steps.length * 100) : 0;
            document.getElementById('progress-bar').style.width = pct + '%';
            document.getElementById('progress-label').textContent = isSweet
                ? (doneN + ' of ' + steps.length + ' complete · ' + pct + '%')
                : (doneN + ' / ' + steps.length + ' · ' + pct + '%');
            document.getElementById('progress-hint').textContent = doneN === steps.length
                ? (isSweet ? 'You’re live — Daily Pulse is your morning coffee ☕' : 'Setup complete.')
                : (isSweet ? 'Checks update live from your house data' : 'Checks update from house data');
        }

        document.getElementById('btn-dismiss').addEventListener('click', function () {
            localStorage.setItem(DISMISS_KEY, JSON.stringify({ at: Date.now(), hide: true }));
            this.textContent = isSweet ? 'Hidden from home ✓' : 'Hidden';
        });

        paint();
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) paint();
        });
        // Expose for home banner
        window.PbjHouseSetup = {
            progress: function () {
                var doneN = 0;
                steps.forEach(function (s) {
                    try { if (s.done()) doneN++; } catch (e) {}
                });
                return { done: doneN, total: steps.length, dismissed: !!(loadJson(DISMISS_KEY, {}).hide) };
            }
        };
    })();
    </script>
</body>
</html>
