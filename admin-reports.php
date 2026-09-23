<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
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

    <title><?php echo $is_sweet ? 'Reports & Sales' : 'Reports & Sales'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 90px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.8rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 14px 40px; max-width: 1200px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.95; }
        .section-label { font-size: 0.95rem; opacity: 0.65; margin: 18px 4px 10px; letter-spacing: 0.02em; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 520px) { .grid { grid-template-columns: 1fr; } }
        .card { background: white; border-radius: 16px; padding: 16px 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: block; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .card h3 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 1.15rem; margin: 8px 0 4px; }
        .card p { margin: 0; font-size: 0.82rem; opacity: 0.75; line-height: 1.35; }
        .card-icon { width: 52px; height: 52px; border-radius: 12px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
        .pill { display: inline-block; margin-top: 8px; font-size: 0.72rem; border-radius: 999px; padding: 3px 8px; <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .closeout { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .closeout h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 6px; }
        .closeout .hint { margin: 0 0 12px; font-size: 0.92rem; opacity: 0.7; line-height: 1.4; }
        .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        @media (max-width: 700px) { .steps { grid-template-columns: 1fr 1fr; } }
        .step { display: block; text-decoration: none; color: inherit; border-radius: 14px; padding: 12px 10px; text-align: center; border: 2px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; transition: all 0.2s; }
        .step:hover { transform: translateY(-2px); }
        .step.done { <?php if ($is_sweet): ?>border-color: #B8E6CF; background: #E8F8F1;<?php else: ?>border-color: #A8C5E2; background: #EAF1FA;<?php endif; ?> }
        .step.miss { <?php if ($is_sweet): ?>border-color: #F3C5CC; background: #FFF5F6;<?php else: ?>border-color: #C5D0DE; background: #F7F5F2;<?php endif; ?> }
        .step .ico { font-size: 1.35rem; margin-bottom: 4px; }
        .step .ttl { font-size: 0.95rem; margin-bottom: 2px; }
        .step .st { font-size: 0.78rem; opacity: 0.75; }
        .step.done .st { color: #1F6B4A; opacity: 1; }
        .flow { font-size: 0.9rem; opacity: 0.75; margin: 12px 0 0; line-height: 1.45; text-align: center; }
    
        .card-icon { overflow: hidden; }
        .card-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
        <?php if ($is_sweet): ?>
        .card-icon.sweet-sticker {
            width: 72px; height: 72px; border-radius: 16px; background: #FFFBFA;
            padding: 4px; box-sizing: border-box;
        }
        .card-icon.sweet-sticker img { object-fit: contain; border-radius: 12px; }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
        <h1><?php echo $is_sweet ? 'Reports & Sales' : 'Reports & Sales'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Daily numbers, cash, labor, trends & P&amp;L' : 'Daily numbers, cash, labor, trends, and P&amp;L'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Connect Square / Clover / Toast for live sales &amp; labor, or log by hand / CSV. P&amp;L auto-fills from those days + Auto-Orders + your opex chart 💕'
                : 'Connect POS for live sales & labor, or manual/CSV. P&amp;L auto-fills sales, labor, Auto-Orders, and opex.'; ?>
        </div>

        <div class="closeout">
            <h2><?php echo $is_sweet ? 'Today\'s close-out' : 'Today\'s close-out'; ?></h2>
            <p class="hint" id="closeout-date"><?php echo $is_sweet ? 'Suggested path for end of night' : 'Suggested end-of-night path'; ?></p>
            <div class="steps">
                <a href="/admin/sales" class="step miss" id="step-sales">
                    <div class="ico">📈</div>
                    <div class="ttl"><?php echo $is_sweet ? 'Sales' : 'Sales'; ?></div>
                    <div class="st" id="st-sales">—</div>
                </a>
                <a href="/admin/labor" class="step miss" id="step-labor">
                    <div class="ico">⏱️</div>
                    <div class="ttl"><?php echo $is_sweet ? 'Labor' : 'Labor'; ?></div>
                    <div class="st" id="st-labor">—</div>
                </a>
                <a href="/admin/cash" class="step miss" id="step-cash">
                    <div class="ico">💵</div>
                    <div class="ttl"><?php echo $is_sweet ? 'Cash' : 'Cash'; ?></div>
                    <div class="st" id="st-cash">—</div>
                </a>
                <a href="/admin/trends" class="step" id="step-trends">
                    <div class="ico">📊</div>
                    <div class="ttl"><?php echo $is_sweet ? 'Trends' : 'Trends'; ?></div>
                    <div class="st" id="st-trends"><?php echo $is_sweet ? '7-day view' : '7-day view'; ?></div>
                </a>
            </div>
            <p class="flow"><?php echo $is_sweet
                ? 'Connect POS → Sales → Labor → Cash drawer → Weekly Trends → P&amp;L. Comps anytime you need them.'
                : 'Connect POS → Sales → Labor → Cash → Weekly Trends → P&amp;L. Log comps anytime.'; ?></p>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Core numbers' : 'Core numbers'; ?></div>
        <div class="grid">
            <a href="/admin/setup" class="card">
                <?php pbj_render_card_icon('ops-hub/settings', 'House setup', '🏠'); ?>
                <h3><?php echo $is_sweet ? 'House setup' : 'House setup'; ?></h3>
                <p><?php echo $is_sweet ? 'POS · labor · opex · cash · invoices checklist' : 'Launch checklist for a new house'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Start here' : 'Start'; ?></span>
            </a>
            <a href="/admin/pos-connect" class="card">
                <?php pbj_render_card_icon('showtime/pos', 'POS Connections', '🔗'); ?>
                <h3><?php echo $is_sweet ? 'POS Connections' : 'POS Connections'; ?></h3>
                <p><?php echo $is_sweet ? 'Square · Clover OAuth · Toast partner · live sync' : 'Square, Clover OAuth · Toast partner · live sync'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Live' : 'Live'; ?></span>
            </a>
            <a href="/admin/pos-import" class="card">
                <?php pbj_render_card_icon('pos-hub/payments', 'POS CSV Import', '📥'); ?>
                <h3><?php echo $is_sweet ? 'POS CSV Import' : 'POS CSV Import'; ?></h3>
                <p><?php echo $is_sweet ? 'Toast / Square / Clover day exports → Daily Sales' : 'Import day-level POS CSV into sales log'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Bridge' : 'Bridge'; ?></span>
            </a>
            <a href="/admin/sales" class="card">
                <?php pbj_render_card_icon('reports-hub/sales', 'Daily Sales', '💰'); ?>
                <h3>Daily Sales</h3>
                <p><?php echo $is_sweet ? 'Gross, net, covers, tender mix — POS + manual log' : 'Gross, net, covers, tender mix — POS + manual'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Manual · CSV · Live POS' : 'Manual · CSV · Live POS'; ?></span>
            </a>
            <a href="/admin/labor" class="card">
                <?php pbj_render_card_icon('reports-hub/labor', 'Labor Snapshot', '👷'); ?>
                <h3>Labor Snapshot</h3>
                <p><?php echo $is_sweet ? 'Schedule clocks now · POS punches & labor $ later' : 'Schedule clocks now · POS punches & labor $ later'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Manual now · POS later' : 'Manual now · POS later'; ?></span>
            </a>
            <a href="/admin/reports/weekly-snapshot" class="card">
                <?php pbj_render_card_icon('jelly/announcements', 'Weekly Owner Snapshot', '📬'); ?>
                <h3><?php echo $is_sweet ? 'Weekly Owner Snapshot' : 'Weekly Owner Snapshot'; ?></h3>
                <p><?php echo $is_sweet ? 'Monday pulse — dishes, 86s, lists, labor' : 'Monday pulse: FC%, 86s, checklists, labor'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Owners · GMs' : 'Owners · GMs'; ?></span>
            </a>
            <a href="/admin/trends" class="card">
                <?php pbj_render_card_icon('reports-hub/trends', 'Weekly Trends', '📊'); ?>
                <h3>Weekly Trends</h3>
                <p><?php echo $is_sweet ? 'Sales, saved labor hours & covers at a glance' : 'Sales, saved labor hours, and covers'; ?></p>
            </a>
            <a href="/admin/pnl" class="card">
                <?php pbj_render_card_icon('reports-hub/pnl', 'P&L Statement', '📋'); ?>
                <h3><?php echo $is_sweet ? 'P&amp;L Statement' : 'P&amp;L Statement'; ?></h3>
                <p><?php echo $is_sweet ? 'Auto-fill from POS sales & labor · orders · opex' : 'Auto-fill from POS sales, labor, orders, opex'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Sales · labor · Auto-Order · opex' : 'Sales · labor · orders · opex'; ?></span>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Controls & close-out' : 'Controls & close-out'; ?></div>
        <div class="grid">
            <a href="/admin/cash" class="card">
                <?php pbj_render_card_icon('reports-hub/cash', 'Cash & Deposits', '🏦'); ?>
                <h3>Cash & Deposits</h3>
                <p><?php echo $is_sweet ? 'Drawer count, over/short, bank deposit' : 'Drawer count, over/short, bank deposit'; ?></p>
            </a>
            <a href="/admin/comps" class="card">
                <?php pbj_render_card_icon('reports-hub/comps', 'Comps & Voids', '🎟️'); ?>
                <h3>Comps & Voids</h3>
                <p><?php echo $is_sweet ? 'Comps, voids, discounts — leakage watch' : 'Comps, voids, discounts — control leakage'; ?></p>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Food cost tools (optional)' : 'Food cost tools (optional)'; ?></div>
        <div class="grid">
            <a href="/admin/pmix" class="card">
                <?php pbj_render_card_icon('reports-hub/costing', 'PMIX', '🧮'); ?>
                <h3><?php echo $is_sweet ? 'PMIX / ideal FC' : 'PMIX / ideal FC'; ?></h3>
                <p><?php echo $is_sweet ? 'Product mix → theoretical food cost $' : 'Product mix → ideal food cost'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Optional' : 'Optional'; ?></span>
            </a>
            <a href="/admin/waste" class="card">
                <?php pbj_render_card_icon('reports-hub/waste', 'Waste log', '🗑️'); ?>
                <h3><?php echo $is_sweet ? 'Waste log' : 'Waste log'; ?></h3>
                <p><?php echo $is_sweet ? 'Spoilage &amp; over-prep dollars' : 'Spoilage and over-prep dollars'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Optional' : 'Optional'; ?></span>
            </a>
            <a href="/BOH/menu" class="card">
                <?php pbj_render_card_icon('recipes-hub/menu-engineering', 'Menu engineering', '⭐'); ?>
                <h3><?php echo $is_sweet ? 'Menu engineering' : 'Menu engineering'; ?></h3>
                <p><?php echo $is_sweet ? 'Plate cost · Star / Plowhorse matrix' : 'Plate cost & Star matrix'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Optional' : 'Optional'; ?></span>
            </a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
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

        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function loadDays(keys, field) {
            field = field || 'days';
            for (var i = 0; i < keys.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(keys[i]) || 'null');
                    if (r && Array.isArray(r[field])) return r[field];
                } catch (e) {}
            }
            return [];
        }
        function money(n) {
            if (n == null || isNaN(n)) return null;
            return '$' + (Math.round(n * 100) / 100).toFixed(2);
        }
        var today = todayStr();
        document.getElementById('closeout-date').textContent = isSweet
            ? 'End-of-night path for ' + today
            : 'Close-out checklist for ' + today;

        var sales = loadDays(['pbj_admin_sales_v2', 'pbj_admin_sales_v1']);
        var labor = loadDays(['pbj_admin_labor_v2', 'pbj_admin_labor_v1']);
        var cash = loadDays(['pbj_admin_cash_v1']);

        var sDay = sales.find(function (d) { return d.date === today; });
        var lDay = labor.find(function (d) { return d.date === today; });
        var cDay = cash.find(function (d) { return d.date === today; });

        function setStep(id, done, label) {
            var el = document.getElementById(id);
            var st = document.getElementById(id.replace('step-', 'st-'));
            el.classList.toggle('done', !!done);
            el.classList.toggle('miss', !done);
            if (st) st.textContent = label;
        }

        if (sDay) {
            var net = sDay.net != null ? sDay.net : sDay.gross;
            setStep('step-sales', true, money(net) || (isSweet ? 'Logged ✓' : 'Logged'));
        } else {
            setStep('step-sales', false, isSweet ? 'Not logged' : 'Not logged');
        }

        if (lDay && ((lDay.entries && lDay.entries.length) || lDay.hours != null || lDay.cost != null)) {
            var h = lDay.hours;
            var lab = (h != null && h !== '') ? (Number(h).toFixed(1) + 'h') : (isSweet ? 'Saved ✓' : 'Saved');
            if (lDay.cost != null && !isNaN(lDay.cost)) lab += ' · ' + money(lDay.cost);
            setStep('step-labor', true, lab);
        } else {
            setStep('step-labor', false, isSweet ? 'Not saved' : 'Not saved');
        }

        if (cDay) {
            var dep = cDay.deposit != null ? money(cDay.deposit) : null;
            setStep('step-cash', true, dep ? (isSweet ? 'Deposit ' + dep : 'Deposit ' + dep) : (isSweet ? 'Closed ✓' : 'Closed'));
        } else {
            setStep('step-cash', false, isSweet ? 'Drawer open' : 'Not closed');
        }
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRepPerms);
            document.addEventListener('pbj-perms-ready', applyRepPerms);
})();
    </script>
</body>
</html>
