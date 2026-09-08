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
    <title><?php echo $is_sweet ? 'Daily Sales' : 'Daily Sales'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.6rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.25rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.82rem; opacity: 0.7; margin-top: 4px; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 110px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .entry { padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .entry:last-child { border-bottom: none; }
        .entry-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .entry-date { font-size: 1.1rem; }
        .entry-net { font-size: 1.15rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .entry-meta { font-size: 0.92rem; opacity: 0.75; margin-top: 4px; line-height: 1.4; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.75rem; margin-left: 6px; vertical-align: middle; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .badge.pos { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .empty { text-align: center; padding: 24px; opacity: 0.8; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .pos-banner { border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; line-height: 1.4; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC; color: #3a2f1f;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE; color: #1A2A44;<?php endif; ?> }
        .subhead { font-size: 0.95rem; opacity: 0.7; margin: 14px 0 8px; }
        .calc-row { display: flex; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: 12px; margin-bottom: 10px; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .entry-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .edit-banner { display: none; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.95rem; line-height: 1.4; <?php if ($is_sweet): ?>background: #E8F8F1; border: 1px solid #B8E6CF; color: #1F6B4A;<?php else: ?>background: #EAF1FA; border: 1px solid #C5D0DE; color: #1A2A44;<?php endif; ?> }
        .edit-banner.show { display: block; }
        .form-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .form-actions .btn { flex: 1; min-width: 120px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports & Sales' : 'Back to Reports & Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'Daily Sales' : 'Daily Sales'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'POS-shaped day log' : 'POS-shaped daily sales log'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>
        <div class="edit-banner" id="conflict-banner" style="display:none;margin-bottom:12px;">
            <span id="conflict-banner-text"></span>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                <button type="button" class="btn btn-primary btn-small" id="conflict-keep"><?php echo $is_sweet ? 'Keep mine' : 'Keep mine'; ?></button>
                <button type="button" class="btn btn-ghost btn-small" id="conflict-use-kitchen"><?php echo $is_sweet ? 'Use kitchen' : 'Use kitchen'; ?></button>
            </div>
        </div>
        <div class="pos-banner">
            <?php echo $is_sweet
                ? '✍️ <strong>Manual, CSV, or live POS</strong> — fields mirror Toast / Square / Clover close-outs. <a href="/admin/pos-connect" style="color:inherit;font-weight:700;">Connect Square, Clover, or Toast</a> for live sync, or <a href="/admin/pos-import" style="color:inherit;font-weight:700;">CSV presets</a> (Aloha, Micros, …) — days land here with a “From POS” badge.'
                : '<strong>Manual, CSV, or live POS.</strong> <a href="/admin/pos-connect">Connect Square/Clover/Toast</a> or <a href="/admin/pos-import">CSV presets</a> (Aloha/Micros…) into this day log.'; ?>
        </div>
        <div class="toolbar" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
            <a href="/admin/pos-connect" class="btn btn-primary"><?php echo $is_sweet ? '🔗 Connect POS' : 'Connect POS'; ?></a>
            <a href="/admin/pos-import" class="btn btn-secondary"><?php echo $is_sweet ? '📥 POS CSV import' : 'POS CSV import'; ?></a>
            <a href="/admin/pnl" class="btn btn-secondary"><?php echo $is_sweet ? 'P&amp;L' : 'P&amp;L'; ?></a>
        </div>
        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-today">—</div><div class="lbl"><?php echo $is_sweet ? 'Today net' : 'Today net'; ?></div></div>
            <div class="stat"><div class="num" id="stat-week">—</div><div class="lbl"><?php echo $is_sweet ? 'Last 7 days' : 'Last 7 days'; ?></div></div>
            <div class="stat"><div class="num" id="stat-avg">—</div><div class="lbl"><?php echo $is_sweet ? 'Avg check (7d)' : 'Avg check (7d)'; ?></div></div>
        </div>
        <div class="card">
            <h2 id="form-title"><?php echo $is_sweet ? 'Log a day' : 'Log a day'; ?></h2>
            <p class="hint" id="form-hint"><?php echo $is_sweet ? 'Fill what you have from the close report — leave blanks if you don\'t track it yet.' : 'Enter close-report fields; leave blanks optional.'; ?></p>
            <div class="edit-banner" id="edit-banner"><?php echo $is_sweet ? 'Editing a saved day — update anything, then save ✏️' : 'Editing a saved day. Update fields, then save.'; ?></div>
            <form id="sales-form">
                <input type="hidden" id="f-id" value="">
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Business date' : 'Business date'; ?></label><input type="date" id="f-date" required></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Source' : 'Source'; ?></label>
                        <select id="f-source">
                            <option value="manual"><?php echo $is_sweet ? 'Manual (typed)' : 'Manual entry'; ?></option>
                            <option value="pos"><?php echo $is_sweet ? 'From POS / CSV' : 'From POS / CSV'; ?></option>
                        </select>
                    </div>
                </div>

                <div class="subhead"><?php echo $is_sweet ? 'Sales totals' : 'Sales totals'; ?></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Gross sales ($)' : 'Gross sales ($)'; ?></label><input type="number" step="0.01" min="0" id="f-gross"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Net sales ($)' : 'Net sales ($)'; ?></label><input type="number" step="0.01" min="0" id="f-net"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Tax collected ($)' : 'Tax collected ($)'; ?></label><input type="number" step="0.01" min="0" id="f-tax"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Tips ($)' : 'Tips ($)'; ?></label><input type="number" step="0.01" min="0" id="f-tips"></div>
                </div>

                <div class="subhead"><?php echo $is_sweet ? 'Guests' : 'Guests'; ?></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Covers / guests' : 'Covers / guests'; ?></label><input type="number" min="0" id="f-covers"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Checks / tickets' : 'Checks / tickets'; ?></label><input type="number" min="0" id="f-checks"></div>
                </div>
                <div class="calc-row">
                    <span><?php echo $is_sweet ? 'Avg check (auto)' : 'Avg check (auto)'; ?></span>
                    <strong id="calc-avg">—</strong>
                </div>

                <div class="subhead"><?php echo $is_sweet ? 'Tender mix (how they paid)' : 'Tender mix'; ?></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Cash ($)' : 'Cash ($)'; ?></label><input type="number" step="0.01" min="0" id="f-tender-cash"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Card ($)' : 'Card ($)'; ?></label><input type="number" step="0.01" min="0" id="f-tender-card"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Other ($)' : 'Other ($)'; ?></label><input type="number" step="0.01" min="0" id="f-tender-other"></div>
                </div>

                <div class="subhead"><?php echo $is_sweet ? 'Optional' : 'Optional'; ?></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Labor $ (if not logging separately)' : 'Labor $ (optional)'; ?></label><input type="number" step="0.01" min="0" id="f-labor"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'POS (optional — for later)' : 'POS (optional — for later)'; ?></label>
                        <select id="f-pos-provider">
                            <option value=""><?php echo $is_sweet ? 'None / skip for now' : 'None / skip for now'; ?></option>
                            <option value="toast">Toast</option>
                            <option value="square">Square</option>
                            <option value="clover">Clover</option>
                            <option value="aloha">Aloha</option>
                            <option value="spoton">SpotOn</option>
                            <option value="other"><?php echo $is_sweet ? 'Other / local system…' : 'Other / local system…'; ?></option>
                        </select>
                        <input type="text" id="f-pos-custom" style="display:none;margin-top:8px;" placeholder="<?php echo $is_sweet ? 'e.g. Micros, house POS…' : 'e.g. Micros, house POS…'; ?>">
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Weather, event, 86s, private party…' : 'Weather, event, 86s…'; ?>"></div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-edit" style="display:none;"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary" id="save-btn"><?php echo $is_sweet ? 'Save day ✨' : 'Save day'; ?></button>
                </div>
            </form>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Recent days' : 'Recent days'; ?></h2>
            <div id="list"></div>
        </div>
        <div class="actions-bar">
            <a href="/admin/labor" class="btn btn-secondary"><?php echo $is_sweet ? '⏱️ Labor next' : 'Labor next'; ?></a>
            <a href="/admin/cash" class="btn btn-secondary"><?php echo $is_sweet ? '💵 Cash' : 'Cash'; ?></a>
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
        const KEY = 'pbj_admin_sales_v2';
        const OLD_KEY = 'pbj_admin_sales_v1';
        const SHARED_KEY = 'admin_sales_v1';
        const SHARED_DATE = '2000-01-01';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var formDirty = false;
        var pendingConflict = null;
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

        function normalizeDay(d) {
            return {
                id: d.id || uid(),
                date: d.date || '',
                source: d.source === 'pos' ? 'pos' : 'manual',
                posProvider: d.posProvider || '',
                gross: d.gross,
                net: d.net,
                tax: d.tax,
                tips: d.tips,
                covers: d.covers,
                checks: d.checks,
                tenderCash: d.tenderCash,
                tenderCard: d.tenderCard,
                tenderOther: d.tenderOther,
                labor: d.labor,
                notes: d.notes || '',
                updatedAt: d.updatedAt || 0
            };
        }

        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) {
                    var old = JSON.parse(localStorage.getItem(OLD_KEY) || 'null');
                    if (old && Array.isArray(old.days)) {
                        r = { days: old.days.map(function (d) {
                            return normalizeDay(Object.assign({}, d, { source: 'manual' }));
                        }), structureAt: Date.now() };
                        localStorage.setItem(KEY, JSON.stringify(r));
                    }
                }
                if (!r || !Array.isArray(r.days)) return { days: [], structureAt: Date.now() };
                r.days = r.days.map(normalizeDay);
                return r;
            } catch (e) { return { days: [], structureAt: Date.now() }; }
        }

        var state = load();
        var shared = null;
        var applyingRemote = false;

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = typeof msg === 'string' ? msg : (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1100);
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

        function save(showToast) {
            state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            // keep v1 readable for labor page
            try {
                localStorage.setItem(OLD_KEY, JSON.stringify({
                    days: state.days.map(function (d) {
                        return { id: d.id, date: d.date, gross: d.gross, net: d.net, covers: d.covers, labor: d.labor, notes: d.notes };
                    })
                }));
            } catch (e) {}
            if (showToast) toast(showToast === true ? undefined : showToast);
            if (shared) shared.queuePush(state);
        }

        function netOf(d) {
            var n = num(d.net);
            if (n != null) return n;
            return num(d.gross);
        }

        function avgCheckOf(d) {
            var n = netOf(d);
            var c = num(d.checks) || num(d.covers);
            if (n == null || !c) return null;
            return n / c;
        }

        function updateCalc() {
            var net = num(document.getElementById('f-net').value);
            if (net == null) net = num(document.getElementById('f-gross').value);
            var checks = num(document.getElementById('f-checks').value) || num(document.getElementById('f-covers').value);
            document.getElementById('calc-avg').textContent = (net != null && checks) ? money(net / checks) : '—';
        }

        ['f-net','f-gross','f-checks','f-covers'].forEach(function (id) {
            document.getElementById(id).addEventListener('input', updateCalc);
        });

        document.getElementById('f-date').value = todayStr();
        var editingId = null;

        function setEditMode(on, row) {
            editingId = on && row ? String(row.id) : null;
            document.getElementById('f-id').value = editingId || '';
            document.getElementById('edit-banner').classList.toggle('show', !!editingId);
            document.getElementById('cancel-edit').style.display = editingId ? '' : 'none';
            document.getElementById('form-title').textContent = editingId
                ? (isSweet ? 'Edit sales day' : 'Edit sales day')
                : (isSweet ? 'Log a day' : 'Log a day');
            document.getElementById('form-hint').textContent = editingId
                ? (isSweet ? 'Tweak numbers, tender mix, notes — then update 💕' : 'Update any fields, then save.')
                : (isSweet ? 'Fill what you have from the close report — leave blanks if you don\'t track it yet.' : 'Enter close-report fields; leave blanks optional.');
            document.getElementById('save-btn').textContent = editingId
                ? (isSweet ? 'Update day ✨' : 'Update day')
                : (isSweet ? 'Save day ✨' : 'Save day');
        }

        function fieldVal(v) {
            return (v === '' || v == null) ? '' : v;
        }

        function syncPosCustomVisibility() {
            var sel = document.getElementById('f-pos-provider');
            var custom = document.getElementById('f-pos-custom');
            if (!sel || !custom) return;
            custom.style.display = sel.value === 'other' ? 'block' : 'none';
            if (sel.value !== 'other') custom.value = '';
        }
        function resolvePosProvider() {
            var sel = document.getElementById('f-pos-provider').value;
            if (sel === 'other') {
                var custom = (document.getElementById('f-pos-custom').value || '').trim();
                return custom || 'other';
            }
            return sel || '';
        }

        function clearForm() {
            document.getElementById('f-date').value = todayStr();
            document.getElementById('f-source').value = 'manual';
            document.getElementById('f-pos-provider').value = '';
            document.getElementById('f-pos-custom').value = '';
            syncPosCustomVisibility();
            ['f-gross','f-net','f-tax','f-tips','f-covers','f-checks','f-tender-cash','f-tender-card','f-tender-other','f-labor','f-notes'].forEach(function (id) {
                document.getElementById(id).value = '';
            });
            updateCalc();
            setEditMode(false);
            formDirty = false;
            hideConflict();
        }

        function loadIntoForm(row) {
            if (!row) return;
            setEditMode(true, row);
            document.getElementById('f-date').value = row.date || todayStr();
            document.getElementById('f-source').value = 'manual';
            var prov = row.posProvider || '';
            var known = ['toast','square','clover','aloha','spoton','other',''];
            if (prov && known.indexOf(prov) === -1) {
                document.getElementById('f-pos-provider').value = 'other';
                document.getElementById('f-pos-custom').value = prov;
            } else {
                document.getElementById('f-pos-provider').value = prov || '';
                document.getElementById('f-pos-custom').value = '';
            }
            syncPosCustomVisibility();
            document.getElementById('f-gross').value = fieldVal(row.gross);
            document.getElementById('f-net').value = fieldVal(row.net);
            document.getElementById('f-tax').value = fieldVal(row.tax);
            document.getElementById('f-tips').value = fieldVal(row.tips);
            document.getElementById('f-covers').value = fieldVal(row.covers);
            document.getElementById('f-checks').value = fieldVal(row.checks);
            document.getElementById('f-tender-cash').value = fieldVal(row.tenderCash);
            document.getElementById('f-tender-card').value = fieldVal(row.tenderCard);
            document.getElementById('f-tender-other').value = fieldVal(row.tenderOther);
            document.getElementById('f-labor').value = fieldVal(row.labor);
            document.getElementById('f-notes').value = row.notes || '';
            updateCalc();
            document.getElementById('form-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.getElementById('cancel-edit').addEventListener('click', function () {
            clearForm();
            render();
        });

        function mergeDays(localDays, remoteDays) {
            if (window.PbjOpsNudges && window.PbjOpsNudges.mergeDaysByDate) {
                return window.PbjOpsNudges.mergeDaysByDate(localDays, remoteDays).map(normalizeDay);
            }
            return (remoteDays || localDays || []).map(normalizeDay);
        }

        function showConflict(remoteDay, localDay) {
            pendingConflict = { remote: remoteDay, local: localDay };
            var ban = document.getElementById('conflict-banner');
            var txt = document.getElementById('conflict-banner-text');
            if (!ban || !txt) return;
            ban.style.display = 'block';
            ban.classList.add('show');
            var when = remoteDay.date || '';
            txt.textContent = isSweet
                ? ('Kitchen has a newer sales day for ' + when + ' while you’re editing. Keep your numbers, or use the kitchen version?')
                : ('Conflict on ' + when + ': kitchen has a newer day. Keep yours or use kitchen?');
        }
        function hideConflict() {
            pendingConflict = null;
            var ban = document.getElementById('conflict-banner');
            if (ban) {
                ban.style.display = 'none';
                ban.classList.remove('show');
            }
        }

        function applyRemote(payload) {
            if (!payload || !Array.isArray(payload.days)) return;
            applyingRemote = true;
            var remoteDays = payload.days.map(normalizeDay);
            // If editing a day and remote has strictly newer same date, offer conflict UI
            if (formDirty && editingId) {
                var localEditing = state.days.find(function (d) { return String(d.id) === String(editingId); });
                if (localEditing) {
                    var remoteSame = remoteDays.find(function (d) { return d.date === localEditing.date; });
                    if (remoteSame && (remoteSame.updatedAt || 0) > (localEditing.updatedAt || 0)) {
                        // still merge other days
                        var without = state.days.filter(function (d) { return d.date !== localEditing.date; });
                        var remoteWithout = remoteDays.filter(function (d) { return d.date !== localEditing.date; });
                        state = {
                            days: mergeDays(without.concat([localEditing]), remoteWithout),
                            structureAt: Math.max(payload.structureAt || 0, Date.now())
                        };
                        localStorage.setItem(KEY, JSON.stringify(state));
                        showConflict(remoteSame, localEditing);
                        render();
                        applyingRemote = false;
                        return;
                    }
                }
            }
            state = {
                days: mergeDays(state.days, remoteDays),
                structureAt: Math.max(payload.structureAt || 0, state.structureAt || 0, Date.now())
            };
            localStorage.setItem(KEY, JSON.stringify(state));
            hideConflict();
            render();
            applyingRemote = false;
        }

        function render() {
            var days = state.days.slice().sort(function (a, b) { return b.date.localeCompare(a.date); });
            var today = todayStr();
            var todayEntry = days.find(function (d) { return d.date === today; });
            document.getElementById('stat-today').textContent = todayEntry ? money(netOf(todayEntry)) : '—';

            var weekCut = new Date(); weekCut.setDate(weekCut.getDate() - 6);
            var weekDays = days.filter(function (d) { return new Date(d.date + 'T12:00:00') >= weekCut; });
            var weekSum = 0, weekCount = 0, avgSum = 0, avgN = 0;
            weekDays.forEach(function (d) {
                var n = netOf(d);
                if (n != null) { weekSum += n; weekCount++; }
                var a = avgCheckOf(d);
                if (a != null) { avgSum += a; avgN++; }
            });
            document.getElementById('stat-week').textContent = weekCount ? money(weekSum) : '—';
            document.getElementById('stat-avg').textContent = avgN ? money(avgSum / avgN) : '—';

            var root = document.getElementById('list');
            if (!days.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No sales logged yet — drop your first day above 📈' : 'No sales logged yet.') + '</div>';
                return;
            }
            root.innerHTML = days.map(function (d) {
                var meta = [];
                if (num(d.gross) != null) meta.push('Gross ' + money(num(d.gross)));
                if (num(d.covers) != null) meta.push(d.covers + ' covers');
                if (num(d.checks) != null) meta.push(d.checks + ' checks');
                var ac = avgCheckOf(d);
                if (ac != null) meta.push('Avg ' + money(ac));
                if (num(d.tips) != null) meta.push('Tips ' + money(num(d.tips)));
                var tender = [];
                if (num(d.tenderCash) != null) tender.push('Cash ' + money(num(d.tenderCash)));
                if (num(d.tenderCard) != null) tender.push('Card ' + money(num(d.tenderCard)));
                if (num(d.tenderOther) != null) tender.push('Other ' + money(num(d.tenderOther)));
                if (tender.length) meta.push(tender.join(' / '));
                if (d.notes) meta.push(d.notes);
                var when = new Date(d.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                var badge = d.source === 'pos'
                    ? '<span class="badge pos">' + (isSweet ? 'From POS' : 'From POS') + '</span>'
                    : '<span class="badge">' + (isSweet ? 'Manual' : 'Manual') + '</span>';
                var isEditingThis = editingId && String(d.id) === String(editingId);
                var editStyle = isEditingThis
                    ? ' style="outline:2px solid ' + (isSweet ? '#E55163' : '#1A2A44') + ';outline-offset:4px;border-radius:12px;padding:12px;"'
                    : '';
                return '<div class="entry"' + editStyle + '><div class="entry-top"><span class="entry-date">' + esc(when) + badge +
                    (isEditingThis ? ' · ' + (isSweet ? 'editing' : 'editing') : '') + '</span>' +
                    '<span class="entry-net">' + money(netOf(d)) + '</span></div>' +
                    (meta.length ? '<div class="entry-meta">' + esc(meta.join(' · ')) + '</div>' : '') +
                    '<div class="entry-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-edit="' + esc(d.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-del="' + esc(d.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        document.getElementById('sales-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var date = document.getElementById('f-date').value;
            var id = editingId || document.getElementById('f-id').value || uid();
            // Prefer existing id when editing; when new, reuse same-date id if any
            if (!editingId) {
                var sameDate = state.days.find(function (d) { return d.date === date; });
                if (sameDate) id = sameDate.id;
            }
            var payload = normalizeDay({
                id: id,
                date: date,
                source: document.getElementById('f-source').value === 'pos' ? 'pos' : 'manual',
                posProvider: resolvePosProvider(),
                gross: document.getElementById('f-gross').value,
                net: document.getElementById('f-net').value,
                tax: document.getElementById('f-tax').value,
                tips: document.getElementById('f-tips').value,
                covers: document.getElementById('f-covers').value,
                checks: document.getElementById('f-checks').value,
                tenderCash: document.getElementById('f-tender-cash').value,
                tenderCard: document.getElementById('f-tender-card').value,
                tenderOther: document.getElementById('f-tender-other').value,
                labor: document.getElementById('f-labor').value,
                notes: document.getElementById('f-notes').value.trim(),
                updatedAt: Date.now()
            });
            // If editing and source was pos, preserve it
            if (editingId) {
                var prev = state.days.find(function (d) { return String(d.id) === String(editingId); });
                if (prev && prev.source === 'pos') payload.source = 'pos';
            }
            if (editingId) {
                state.days = state.days.filter(function (d) { return String(d.id) !== String(editingId); });
                // Avoid duplicate calendar dates after a date change
                state.days = state.days.filter(function (d) { return d.date !== date || String(d.id) === String(id); });
            } else {
                state.days = state.days.filter(function (d) { return d.date !== date; });
            }
            state.days.push(payload);
            formDirty = false;
            save(true);
            clearForm();
            render();
        });

        document.getElementById('list').addEventListener('click', function (e) {
            var editBtn = e.target.closest('[data-edit]');
            if (editBtn) {
                var row = state.days.find(function (d) { return String(d.id) === String(editBtn.dataset.edit); });
                if (row) {
                    loadIntoForm(row);
                    render();
                }
                return;
            }
            var btn = e.target.closest('[data-del]'); if (!btn) return;
            if (!confirm(isSweet ? 'Remove this day\'s sales log?' : 'Remove this sales log?')) return;
            var delId = String(btn.dataset.del);
            state.days = state.days.filter(function (d) { return String(d.id) !== delId; });
            if (editingId && editingId === delId) clearForm();
            save(true); render();
        });

        document.getElementById('f-pos-provider').addEventListener('change', syncPosCustomVisibility);
        syncPosCustomVisibility();

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRepPerms);
            document.addEventListener('pbj-perms-ready', applyRepPerms);
        updateCalc();

        document.getElementById('sales-form').addEventListener('input', function () {
            formDirty = true;
        });
        document.getElementById('conflict-keep').addEventListener('click', function () {
            if (!pendingConflict || !pendingConflict.local) { hideConflict(); return; }
            // Bump local updatedAt so kitchen merge keeps ours
            var d = pendingConflict.local;
            d.updatedAt = Date.now();
            state.days = state.days.filter(function (x) { return x.date !== d.date; });
            state.days.push(normalizeDay(d));
            formDirty = true;
            hideConflict();
            save(false);
            render();
            toast(isSweet ? 'Kept your day · syncing…' : 'Kept yours');
        });
        document.getElementById('conflict-use-kitchen').addEventListener('click', function () {
            if (!pendingConflict || !pendingConflict.remote) { hideConflict(); return; }
            var d = normalizeDay(pendingConflict.remote);
            state.days = state.days.filter(function (x) { return x.date !== d.date; });
            state.days.push(d);
            if (editingId) {
                var still = state.days.find(function (x) { return String(x.id) === String(editingId); });
                if (still) loadIntoForm(still);
                else clearForm();
            }
            formDirty = false;
            hideConflict();
            save(false);
            render();
            toast(isSweet ? 'Using kitchen day ✨' : 'Using kitchen');
        });

        if (window.PbjSharedState) {
            shared = new PbjSharedState({
                key: SHARED_KEY,
                date: SHARED_DATE,
                pollMs: 5000,
                onStatus: setSyncPill,
                onRemote: function (payload) {
                    if (applyingRemote) return;
                    applyRemote(payload);
                }
            });
            shared.bootstrap(
                function () { return state; },
                function (payload) { applyRemote(payload); }
            ).then(function () { shared.startPolling(); });
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only · multi-device off' : 'Local only' });
        }
    })();
    </script>
</body>
</html>
