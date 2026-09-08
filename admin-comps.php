<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$reasons = [
    'Guest recovery', 'Manager meal', 'Staff meal', 'VIP / owner',
    'Wrong order', 'Quality issue', 'Promo / coupon', 'Other',
];
$types = [
    'comp' => $is_sweet ? 'Comp' : 'Comp',
    'void' => $is_sweet ? 'Void' : 'Void',
    'discount' => $is_sweet ? 'Discount' : 'Discount',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Comps & Voids' : 'Comps & Voids'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .entry { padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .entry-meta { font-size: 0.92rem; opacity: 0.75; margin-top: 4px; line-height: 1.4; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.75rem; margin-right: 4px; <?php if ($is_sweet): ?>background: #FFF5F6;<?php else: ?>background: #EEF2F8;<?php endif; ?> }
        .badge.void { background: #FDECEA; color: #B71C1C; }
        .badge.comp { <?php if ($is_sweet): ?>background: #FFF3E0; color: #8A5A12;<?php else: ?>background: #EEF2F8;<?php endif; ?> }
        .badge.discount { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA;<?php endif; ?> }
        .empty { text-align: center; padding: 24px; opacity: 0.8; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .pos-note { font-size: 0.9rem; opacity: 0.75; margin: 0 0 12px; line-height: 1.4; padding: 10px 12px; border-radius: 12px; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports & Sales' : 'Back to Reports & Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'Comps & Voids' : 'Comps & Voids'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Watch the giveaways' : 'Track comps, voids, and discounts'; ?></p>
    </div>
    <div class="content">
        <div class="pos-note">
            <?php echo $is_sweet
                ? 'Log manually anytime. <strong>Square sync</strong> also imports refunds as voids into this list (see POS Connections → Sync) 🎫'
                : 'Manual entry + Square refunds import on POS sync.'; ?>
            <div style="margin-top:8px;">
                <button type="button" class="btn btn-primary btn-small" id="btn-sync-comps"><?php echo $is_sweet ? 'Sync Square refunds' : 'Sync Square refunds'; ?></button>
            </div>
        </div>
        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-total">—</div><div class="lbl"><?php echo $is_sweet ? 'Total (7d)' : 'Total (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-count">0</div><div class="lbl"><?php echo $is_sweet ? 'Entries (7d)' : 'Entries (7d)'; ?></div></div>
            <div class="stat"><div class="num" id="stat-voids">—</div><div class="lbl"><?php echo $is_sweet ? 'Voids (7d)' : 'Voids (7d)'; ?></div></div>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Log an adjustment' : 'Log an adjustment'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Who approved it, why, and how much — protects the house 💕' : 'Who approved it, why, and how much.'; ?></p>
            <form id="comp-form">
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Date' : 'Date'; ?></label><input type="date" id="f-date" required></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Type' : 'Type'; ?></label>
                        <select id="f-type">
                            <?php foreach ($types as $k => $label): ?>
                            <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Amount ($)' : 'Amount ($)'; ?></label><input type="number" step="0.01" min="0" id="f-amount" required></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Reason' : 'Reason'; ?></label>
                        <select id="f-reason">
                            <?php foreach ($reasons as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Approved by' : 'Approved by'; ?></label><input id="f-by" placeholder="<?php echo $is_sweet ? 'Manager name' : 'Manager name'; ?>"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Server / station' : 'Server / station'; ?></label><input id="f-server" placeholder="<?php echo $is_sweet ? 'Optional' : 'Optional'; ?>"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Item, table, story…' : 'Item, table, details…'; ?>"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;"><?php echo $is_sweet ? 'Log it ✨' : 'Log entry'; ?></button>
            </form>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Log' : 'Log'; ?></h2>
            <div class="filters" id="filters">
                <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
                <button type="button" class="chip" data-filter="comp"><?php echo $is_sweet ? 'Comps' : 'Comps'; ?></button>
                <button type="button" class="chip" data-filter="void"><?php echo $is_sweet ? 'Voids' : 'Voids'; ?></button>
                <button type="button" class="chip" data-filter="discount"><?php echo $is_sweet ? 'Discounts' : 'Discounts'; ?></button>
            </div>
            <div id="list"></div>
        </div>
        <div class="actions-bar">
            <a href="/admin/sales" class="btn btn-secondary"><?php echo $is_sweet ? '📈 Daily sales' : 'Daily sales'; ?></a>
            <a href="/admin/reports" class="btn btn-primary"><?php echo $is_sweet ? 'Back to Reports' : 'Back to Reports'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/pos-sync-client.js?v=3"></script>
    <script>
    (function () {
        const KEY = 'pbj_admin_comps_v1';
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

        const TYPE_LABELS = <?php echo json_encode($types, JSON_UNESCAPED_UNICODE); ?>;
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function money(n) { if (n == null || isNaN(n)) return '—'; return '$' + (Math.round(n * 100) / 100).toFixed(2); }
        function num(v) { if (v === '' || v == null) return null; var n = parseFloat(v); return isNaN(n) ? null : n; }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }
        function load() { try { var r = JSON.parse(localStorage.getItem(KEY) || 'null'); return r && Array.isArray(r.entries) ? r : { entries: [] }; } catch (e) { return { entries: [] }; } }
        function save(t) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (t) { var el = document.getElementById('toast'); el.classList.add('show'); setTimeout(function () { el.classList.remove('show'); }, 1100); }
        }
        var state = load();
        var filter = 'all';
        document.getElementById('f-date').value = todayStr();

        function render() {
            var list = state.entries.slice().sort(function (a, b) {
                return (b.date + b.id).localeCompare(a.date + a.id);
            });
            var weekCut = new Date(); weekCut.setDate(weekCut.getDate() - 6);
            var week = list.filter(function (e) { return new Date(e.date + 'T12:00:00') >= weekCut; });
            var total = 0, voids = 0;
            week.forEach(function (e) {
                var a = num(e.amount) || 0;
                total += a;
                if (e.type === 'void') voids += a;
            });
            document.getElementById('stat-total').textContent = week.length ? money(total) : '—';
            document.getElementById('stat-count').textContent = String(week.length);
            document.getElementById('stat-voids').textContent = week.length ? money(voids) : '—';

            if (filter !== 'all') list = list.filter(function (e) { return e.type === filter; });
            var root = document.getElementById('list');
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'Nothing logged yet — keep the house honest 🎫' : 'No entries yet.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (e) {
                var when = new Date(e.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                var meta = [e.reason, e.by ? ('By ' + e.by) : '', e.server, e.notes].filter(Boolean).join(' · ');
                return '<div class="entry"><div class="entry-top"><div><span class="badge ' + esc(e.type) + '">' + esc(TYPE_LABELS[e.type] || e.type) + '</span> ' +
                    esc(when) + '</div><strong>' + money(num(e.amount)) + '</strong></div>' +
                    (meta ? '<div class="entry-meta">' + esc(meta) + '</div>' : '') +
                    '<button type="button" class="btn btn-small btn-danger" style="margin-top:8px;" data-del="' + esc(e.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button></div>';
            }).join('');
        }

        document.getElementById('filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.chip'); if (!chip) return;
            filter = chip.dataset.filter;
            document.querySelectorAll('#filters .chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
            render();
        });

        document.getElementById('comp-form').addEventListener('submit', function (e) {
            e.preventDefault();
            state.entries.push({
                id: uid(),
                date: document.getElementById('f-date').value,
                type: document.getElementById('f-type').value,
                amount: document.getElementById('f-amount').value,
                reason: document.getElementById('f-reason').value,
                by: document.getElementById('f-by').value.trim(),
                server: document.getElementById('f-server').value.trim(),
                notes: document.getElementById('f-notes').value.trim(),
                source: 'manual',
                updatedAt: Date.now()
            });
            save(true);
            document.getElementById('f-amount').value = '';
            document.getElementById('f-by').value = '';
            document.getElementById('f-server').value = '';
            document.getElementById('f-notes').value = '';
            render();
        });
        document.getElementById('list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-del]'); if (!btn) return;
            if (!confirm(isSweet ? 'Remove this entry?' : 'Remove this entry?')) return;
            state.entries = state.entries.filter(function (x) { return String(x.id) !== String(btn.dataset.del); });
            save(true); render();
        });
        var btnSync = document.getElementById('btn-sync-comps');
        if (btnSync) {
            btnSync.addEventListener('click', function () {
                if (!window.PbjPosSync) {
                    alert(isSweet ? 'Connect Square under POS Connections first 💕' : 'POS sync unavailable');
                    return;
                }
                btnSync.disabled = true;
                var prev = btnSync.textContent;
                btnSync.textContent = isSweet ? 'Syncing…' : 'Syncing…';
                window.PbjPosSync.syncProvider('square', 14).then(function (res) {
                    btnSync.disabled = false;
                    btnSync.textContent = prev;
                    state = load();
                    render();
                    if (!res || !res.ok) {
                        alert((res && res.error) || (isSweet ? 'Sync failed — connect Square first' : 'Sync failed'));
                        return;
                    }
                    var n = (res.compCount != null) ? res.compCount : ((res.compEntries || []).length);
                    var toastEl = document.getElementById('toast');
                    if (toastEl) {
                        toastEl.textContent = isSweet
                            ? ('Square: ' + (res.count || 0) + ' sales · ' + n + ' refunds ✨')
                            : ('Synced · ' + n + ' refunds');
                        toastEl.classList.add('show');
                        setTimeout(function () { toastEl.classList.remove('show'); }, 2000);
                    }
                }).catch(function () {
                    btnSync.disabled = false;
                    btnSync.textContent = prev;
                });
            });
        }
        document.addEventListener('pbj-pos-synced', function () {
            state = load();
            render();
        });

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRepPerms);
            document.addEventListener('pbj-perms-ready', applyRepPerms);
    })();
    </script>
</body>
</html>
