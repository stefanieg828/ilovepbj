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
    <title><?php echo $is_sweet ? 'Vendors' : 'Vendors'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-link { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .vendor { border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 0 14px 14px 0; padding: 14px 16px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .vendor h3 { margin: 0 0 6px; font-size: 1.2rem; }
        .meta { font-size: 0.95rem; opacity: 0.8; line-height: 1.4; margin-bottom: 8px; }
        .meta a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; word-break: break-all; }
        .days { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
        .day-pill { border-radius: 999px; padding: 4px 10px; font-size: 0.8rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; }
        .day-checks { display: flex; flex-wrap: wrap; gap: 8px 14px; margin-bottom: 10px; }
        .day-checks label { font-size: 0.95rem; display: flex; align-items: center; gap: 4px; }
        .empty { text-align: center; padding: 24px; opacity: 0.8; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .hint { font-size: 0.92rem; opacity: 0.75; line-height: 1.4; margin: 0 0 12px; }
        .preset-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .preset-chip { border: none; border-radius: 999px; padding: 8px 12px; font-size: 0.88rem; cursor: pointer; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; color: inherit; box-shadow: 0 2px 6px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .preset-chip:hover { transform: translateY(-1px); }
        .preset-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory & Vendors' : 'Back to Inventory & Vendors'; ?></a>
        <h1><?php echo $is_sweet ? 'Vendors' : 'Vendors'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Contacts, delivery days & distributor links' : 'Contacts, delivery days, and links'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Add cut-off time, case minimum, and when you want a <strong>cut-off alert</strong> (30 min, 1 hr, custom…). Alerts show in the app while you’re using it, and as browser notifications if you allow them 🔔'
                : 'Set cut-off, case min, and alert timing. Alerts fire in-app (and as browser notifications if allowed).'; ?>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Quick add — major distributors' : 'Quick add — major distributors'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Fills the form below. Edit contact/delivery days, then hit Add vendor.' : 'Fills the form below. Edit details, then add.'; ?></p>
            <div class="preset-grid" id="presets"></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Add vendor' : 'Add vendor'; ?></h2>
            <form id="add-form">
                <div class="field"><label><?php echo $is_sweet ? 'Vendor name' : 'Vendor name'; ?></label><input id="f-name" required placeholder="Sysco, US Foods, local farm…"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Contact person' : 'Contact person'; ?></label><input id="f-contact"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label><input id="f-phone" type="tel"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label><input id="f-email" type="email" placeholder="orders@… or rep email"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Account number' : 'Account number'; ?></label><input id="f-account" placeholder="<?php echo $is_sweet ? 'Customer account #' : 'Customer account #'; ?>"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Website (main site)' : 'Website (main site)'; ?></label><input id="f-website" type="url" placeholder="https://www.sysco.com"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Order portal (optional)' : 'Order portal (optional)'; ?></label><input id="f-order-url" type="url" placeholder="<?php echo $is_sweet ? 'Only if you have a public order login URL' : 'Optional public order URL'; ?>"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Order cut-off time' : 'Order cut-off time'; ?></label><input id="f-cutoff" type="time"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Case minimum / order' : 'Case minimum / order'; ?></label><input id="f-case-min" type="number" min="0" step="1" placeholder="<?php echo $is_sweet ? 'e.g. 5' : 'e.g. 5'; ?>"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Cut-off alert' : 'Cut-off alert'; ?></label>
                        <select id="f-alert-before">
                            <option value="off"><?php echo $is_sweet ? 'Off' : 'Off'; ?></option>
                            <option value="15"><?php echo $is_sweet ? '15 min before' : '15 min before'; ?></option>
                            <option value="30" selected><?php echo $is_sweet ? '30 min before' : '30 min before'; ?></option>
                            <option value="60"><?php echo $is_sweet ? '1 hour before' : '1 hour before'; ?></option>
                            <option value="120"><?php echo $is_sweet ? '2 hours before' : '2 hours before'; ?></option>
                            <option value="180"><?php echo $is_sweet ? '3 hours before' : '3 hours before'; ?></option>
                            <option value="custom"><?php echo $is_sweet ? 'Custom…' : 'Custom…'; ?></option>
                        </select>
                    </div>
                    <div class="field" id="f-alert-custom-wrap" style="display:none;">
                        <label><?php echo $is_sweet ? 'Custom minutes before' : 'Custom minutes before'; ?></label>
                        <input id="f-alert-custom" type="number" min="1" step="1" placeholder="45">
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Delivery days' : 'Delivery days'; ?></label>
                    <div class="day-checks" id="f-days">
                        <?php foreach ($days as $d): ?>
                        <label><input type="checkbox" value="<?php echo $d; ?>"> <?php echo $d; ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Specialties, account tips…' : 'Specialties, account tips…'; ?>"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;"><?php echo $is_sweet ? 'Add vendor 🚚' : 'Add vendor'; ?></button>
            </form>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Vendor list' : 'Vendor list'; ?></h2>
            <div id="list"></div>
        </div>
        <div class="actions-bar">
            <a href="/admin/product-setup" class="btn btn-secondary"><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
            <a href="/admin/auto-order" class="btn btn-secondary"><?php echo $is_sweet ? 'Auto-Order' : 'Auto-Order'; ?></a>
            <a href="/admin/inventory" class="btn btn-primary"><?php echo $is_sweet ? 'Inventory hub' : 'Inventory hub'; ?></a>
        </div>
    </div>
    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2><?php echo $is_sweet ? 'Edit vendor' : 'Edit vendor'; ?></h2>
            <form id="edit-form">
                <input type="hidden" id="e-id">
                <div class="field"><label><?php echo $is_sweet ? 'Vendor name' : 'Vendor name'; ?></label><input id="e-name" required></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Contact' : 'Contact'; ?></label><input id="e-contact"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label><input id="e-phone" type="tel"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label><input id="e-email" type="email"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Account number' : 'Account number'; ?></label><input id="e-account" placeholder="<?php echo $is_sweet ? 'Customer account #' : 'Customer account #'; ?>"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Website' : 'Website'; ?></label><input id="e-website" type="url"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Order portal (optional)' : 'Order portal (optional)'; ?></label><input id="e-order-url" type="url"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Order cut-off time' : 'Order cut-off time'; ?></label><input id="e-cutoff" type="time"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Case minimum / order' : 'Case minimum / order'; ?></label><input id="e-case-min" type="number" min="0" step="1"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Cut-off alert' : 'Cut-off alert'; ?></label>
                        <select id="e-alert-before">
                            <option value="off"><?php echo $is_sweet ? 'Off' : 'Off'; ?></option>
                            <option value="15"><?php echo $is_sweet ? '15 min before' : '15 min before'; ?></option>
                            <option value="30"><?php echo $is_sweet ? '30 min before' : '30 min before'; ?></option>
                            <option value="60"><?php echo $is_sweet ? '1 hour before' : '1 hour before'; ?></option>
                            <option value="120"><?php echo $is_sweet ? '2 hours before' : '2 hours before'; ?></option>
                            <option value="180"><?php echo $is_sweet ? '3 hours before' : '3 hours before'; ?></option>
                            <option value="custom"><?php echo $is_sweet ? 'Custom…' : 'Custom…'; ?></option>
                        </select>
                    </div>
                    <div class="field" id="e-alert-custom-wrap" style="display:none;">
                        <label><?php echo $is_sweet ? 'Custom minutes before' : 'Custom minutes before'; ?></label>
                        <input id="e-alert-custom" type="number" min="1" step="1">
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Delivery days' : 'Delivery days'; ?></label>
                    <div class="day-checks" id="e-days">
                        <?php foreach ($days as $d): ?>
                        <label><input type="checkbox" value="<?php echo $d; ?>"> <?php echo $d; ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="e-notes"></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="e-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        const KEY = 'pbj_admin_vendors_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyInvPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var view = canP('admin.inventory.view') || canP('admin.inventory.edit');
                var edit = canP('admin.inventory.edit');
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('inv-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="inv-denied">No permission to view inventory.</div>');
                    }
                }
                if (!edit) {
                    document.querySelectorAll('button.btn-primary, button.btn-danger, form button[type=submit]').forEach(function(el){
                        if (el.closest('a') || /print|export|back/i.test(el.textContent||'')) return;
                        el.style.display = 'none';
                    });
                    document.querySelectorAll('form input, form select, form textarea').forEach(function(el){ el.disabled = true; });
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }


        // Main public sites (stable). orderUrl only when a well-known public order entry exists.
        var PRESETS = [
            { name: 'Sysco', website: 'https://www.sysco.com', orderUrl: 'https://shop.sysco.com', note: 'Broadline foodservice' },
            { name: 'US Foods', website: 'https://www.usfoods.com', orderUrl: '', note: 'Broadline foodservice' },
            { name: 'Performance Foodservice', website: 'https://www.pfgc.com', orderUrl: '', note: 'PFG / Performance Food Group' },
            { name: 'Gordon Food Service', website: 'https://www.gfs.com', orderUrl: 'https://www.gfsmarketplace.com', note: 'GFS' },
            { name: 'Restaurant Depot', website: 'https://www.restaurantdepot.com', orderUrl: '', note: 'Cash & carry' },
            { name: 'Shamrock Foods', website: 'https://www.shamrockfoods.com', orderUrl: '', note: 'Regional broadline' },
            { name: 'Ben E. Keith', website: 'https://www.benekeith.com', orderUrl: '', note: 'Regional broadline' },
            { name: 'Cheney Brothers', website: 'https://www.cheneybrothers.com', orderUrl: '', note: 'Southeast broadline' },
            { name: 'Dot Foods', website: 'https://www.dotfoods.com', orderUrl: '', note: 'Redistributor' },
            { name: 'KeHE', website: 'https://www.kehe.com', orderUrl: '', note: 'Specialty / natural' },
            { name: 'UNFI', website: 'https://www.unfi.com', orderUrl: '', note: 'Natural / organic' }
        ];

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function load() { try { var r = JSON.parse(localStorage.getItem(KEY) || 'null'); return r && Array.isArray(r.vendors) ? r : { vendors: [] }; } catch (e) { return { vendors: [] }; } }
        function save(t) { localStorage.setItem(KEY, JSON.stringify(state)); if (t) { var el = document.getElementById('toast'); el.classList.add('show'); setTimeout(function () { el.classList.remove('show'); }, 1100); } }
        function checkedDays(root) {
            return Array.prototype.map.call(root.querySelectorAll('input:checked'), function (c) { return c.value; });
        }
        function setDays(root, days) {
            root.querySelectorAll('input').forEach(function (c) { c.checked = (days || []).indexOf(c.value) !== -1; });
        }
        function normalizeUrl(u) {
            u = String(u || '').trim();
            if (!u) return '';
            if (!/^https?:\/\//i.test(u)) u = 'https://' + u;
            return u;
        }
        function formatTime12(hhmm) {
            if (!hhmm) return '';
            var p = String(hhmm).split(':');
            var h = parseInt(p[0], 10), m = p[1] || '00';
            if (isNaN(h)) return hhmm;
            var ap = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return h + ':' + m + ' ' + ap;
        }
        function alertMinutesFromForm(selectId, customId) {
            var v = document.getElementById(selectId).value;
            if (v === 'off' || !v) return { alertBefore: 'off', alertCustomMinutes: '' };
            if (v === 'custom') {
                var c = parseInt(document.getElementById(customId).value, 10);
                if (isNaN(c) || c < 1) c = 30;
                return { alertBefore: 'custom', alertCustomMinutes: c };
            }
            return { alertBefore: v, alertCustomMinutes: '' };
        }
        function toggleCustom(selectId, wrapId) {
            document.getElementById(wrapId).style.display =
                document.getElementById(selectId).value === 'custom' ? '' : 'none';
        }
        function alertLabel(v) {
            if (!v || v.alertBefore === 'off' || !v.alertBefore) return '';
            if (v.alertBefore === 'custom') {
                return (v.alertCustomMinutes || '?') + (isSweet ? ' min before' : ' min before');
            }
            var n = parseInt(v.alertBefore, 10);
            if (n === 60) return isSweet ? '1 hr before' : '1 hr before';
            if (n === 120) return isSweet ? '2 hr before' : '2 hr before';
            if (n === 180) return isSweet ? '3 hr before' : '3 hr before';
            return n + (isSweet ? ' min before' : ' min before');
        }
        document.getElementById('f-alert-before').addEventListener('change', function () {
            toggleCustom('f-alert-before', 'f-alert-custom-wrap');
        });
        document.getElementById('e-alert-before').addEventListener('change', function () {
            toggleCustom('e-alert-before', 'e-alert-custom-wrap');
        });
        function hostLabel(u) {
            try {
                return new URL(normalizeUrl(u)).hostname.replace(/^www\./, '');
            } catch (e) { return u; }
        }
        function matchPreset(name) {
            var n = String(name || '').toLowerCase();
            if (!n) return null;
            return PRESETS.find(function (p) {
                var pn = p.name.toLowerCase();
                return n === pn || n.indexOf(pn) !== -1 || pn.indexOf(n) !== -1 ||
                    (n.indexOf('sysco') !== -1 && pn.indexOf('sysco') !== -1) ||
                    (n.indexOf('us food') !== -1 && pn.indexOf('us food') !== -1) ||
                    (n.indexOf('pfg') !== -1 && pn.indexOf('performance') !== -1) ||
                    (n.indexOf('gfs') !== -1 && pn.indexOf('gordon') !== -1) ||
                    (n.indexOf('gordon') !== -1 && pn.indexOf('gordon') !== -1);
            }) || null;
        }
        /** Backfill website from presets for vendors missing links */
        function enrichVendor(v) {
            if (!v.website) {
                var p = matchPreset(v.name);
                if (p) {
                    v.website = p.website;
                    if (!v.orderUrl && p.orderUrl) v.orderUrl = p.orderUrl;
                }
            }
            return v;
        }

        var state = load();
        state.vendors.forEach(enrichVendor);
        save(false);
        var modal = document.getElementById('modal');

        // presets UI
        document.getElementById('presets').innerHTML = PRESETS.map(function (p, i) {
            return '<button type="button" class="preset-chip" data-preset="' + i + '">' + esc(p.name) + '</button>';
        }).join('');
        document.getElementById('presets').addEventListener('click', function (e) {
            var chip = e.target.closest('[data-preset]');
            if (!chip) return;
            var p = PRESETS[parseInt(chip.dataset.preset, 10)];
            if (!p) return;
            document.getElementById('f-name').value = p.name;
            document.getElementById('f-website').value = p.website || '';
            document.getElementById('f-order-url').value = p.orderUrl || '';
            if (p.note && !document.getElementById('f-notes').value) {
                document.getElementById('f-notes').value = p.note;
            }
            document.querySelectorAll('.preset-chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
            document.getElementById('f-name').focus();
        });

        function render() {
            var root = document.getElementById('list');
            var list = state.vendors.slice().sort(function (a, b) { return (a.name || '').localeCompare(b.name || ''); });
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No vendors yet — tap a distributor chip or add above 🚚' : 'No vendors yet.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (v) {
                enrichVendor(v);
                var meta = [v.contact, v.phone, v.email].filter(Boolean).join(' · ');
                var pills = (v.days || []).map(function (d) { return '<span class="day-pill">' + esc(d) + '</span>'; }).join('');
                var site = normalizeUrl(v.website);
                var order = normalizeUrl(v.orderUrl);
                var links = '';
                if (v.accountNumber) {
                    links += '<div class="meta">#️⃣ ' + (isSweet ? 'Account ' : 'Account ') + esc(v.accountNumber) + '</div>';
                }
                if (site) {
                    links += '<div class="meta">🌐 <a href="' + esc(site) + '" target="_blank" rel="noopener noreferrer">' + esc(hostLabel(site)) + '</a></div>';
                }
                if (order) {
                    links += '<div class="meta">🛒 <a href="' + esc(order) + '" target="_blank" rel="noopener noreferrer">' + (isSweet ? 'Order portal' : 'Order portal') + ' · ' + esc(hostLabel(order)) + '</a></div>';
                }
                var orderRules = [];
                if (v.cutoffTime) orderRules.push((isSweet ? 'Cut-off ' : 'Cut-off ') + formatTime12(v.cutoffTime));
                if (v.caseMin !== '' && v.caseMin != null && !isNaN(parseFloat(v.caseMin))) {
                    orderRules.push((isSweet ? 'Min ' : 'Min ') + v.caseMin + (isSweet ? ' cases' : ' cases'));
                }
                var al = alertLabel(v);
                if (al) orderRules.push((isSweet ? '🔔 Alert ' : '🔔 Alert ') + al);
                if (orderRules.length) {
                    links += '<div class="meta">⏰ ' + esc(orderRules.join(' · ')) + '</div>';
                }
                var btns = '<div style="display:flex;gap:8px;flex-wrap:wrap;">';
                if (site) {
                    btns += '<a class="btn btn-small btn-link" href="' + esc(site) + '" target="_blank" rel="noopener noreferrer">' + (isSweet ? 'Open site' : 'Open site') + '</a>';
                }
                if (order) {
                    btns += '<a class="btn btn-small btn-primary" href="' + esc(order) + '" target="_blank" rel="noopener noreferrer">' + (isSweet ? 'Order login' : 'Order login') + '</a>';
                }
                if (!site) {
                    var p = matchPreset(v.name);
                    if (p) {
                        btns += '<button type="button" class="btn btn-small btn-ghost" data-act="fill-link" data-id="' + esc(v.id) + '">' + (isSweet ? 'Add main site link' : 'Add site link') + '</button>';
                    }
                }
                btns += '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-id="' + esc(v.id) + '">Edit</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-id="' + esc(v.id) + '">Remove</button></div>';

                return '<div class="vendor"><h3>' + esc(v.name) + '</h3>' +
                    (meta ? '<div class="meta">' + esc(meta) + '</div>' : '') +
                    links +
                    (pills ? '<div class="days">' + pills + '</div>' : '') +
                    (v.notes ? '<div class="meta">' + esc(v.notes) + '</div>' : '') +
                    btns +
                    '</div>';
            }).join('');
            save(false);
        }

        document.getElementById('add-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var alertCfg = alertMinutesFromForm('f-alert-before', 'f-alert-custom');
            if (alertCfg.alertBefore !== 'off' && document.getElementById('f-cutoff').value &&
                typeof Notification !== 'undefined' && Notification.permission === 'default') {
                Notification.requestPermission();
            }
            state.vendors.push({
                id: uid(),
                name: document.getElementById('f-name').value.trim(),
                contact: document.getElementById('f-contact').value.trim(),
                phone: document.getElementById('f-phone').value.trim(),
                email: document.getElementById('f-email').value.trim(),
                accountNumber: document.getElementById('f-account').value.trim(),
                website: normalizeUrl(document.getElementById('f-website').value),
                orderUrl: normalizeUrl(document.getElementById('f-order-url').value),
                cutoffTime: document.getElementById('f-cutoff').value || '',
                caseMin: document.getElementById('f-case-min').value,
                alertBefore: alertCfg.alertBefore,
                alertCustomMinutes: alertCfg.alertCustomMinutes,
                days: checkedDays(document.getElementById('f-days')),
                notes: document.getElementById('f-notes').value.trim()
            });
            e.target.reset();
            document.getElementById('f-alert-before').value = '30';
            toggleCustom('f-alert-before', 'f-alert-custom-wrap');
            document.querySelectorAll('.preset-chip').forEach(function (c) { c.classList.remove('active'); });
            save(true); render();
        });

        document.getElementById('list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            var v = state.vendors.find(function (x) { return x.id === id; }); if (!v) return;
            if (btn.dataset.act === 'del') {
                if (!confirm(isSweet ? 'Remove this vendor?' : 'Remove this vendor?')) return;
                state.vendors = state.vendors.filter(function (x) { return x.id !== id; });
                save(true); render(); return;
            }
            if (btn.dataset.act === 'fill-link') {
                var p = matchPreset(v.name);
                if (p) {
                    v.website = p.website;
                    if (p.orderUrl && !v.orderUrl) v.orderUrl = p.orderUrl;
                    save(true); render();
                }
                return;
            }
            document.getElementById('e-id').value = v.id;
            document.getElementById('e-name').value = v.name || '';
            document.getElementById('e-contact').value = v.contact || '';
            document.getElementById('e-phone').value = v.phone || '';
            document.getElementById('e-email').value = v.email || '';
            document.getElementById('e-account').value = v.accountNumber || '';
            document.getElementById('e-website').value = v.website || '';
            document.getElementById('e-order-url').value = v.orderUrl || '';
            document.getElementById('e-cutoff').value = v.cutoffTime || '';
            document.getElementById('e-case-min').value = v.caseMin != null ? v.caseMin : '';
            var ab = v.alertBefore || 'off';
            document.getElementById('e-alert-before').value = ab;
            document.getElementById('e-alert-custom').value = v.alertCustomMinutes != null ? v.alertCustomMinutes : '';
            toggleCustom('e-alert-before', 'e-alert-custom-wrap');
            document.getElementById('e-notes').value = v.notes || '';
            setDays(document.getElementById('e-days'), v.days || []);
            modal.classList.add('show');
        });

        document.getElementById('edit-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('e-id').value;
            var v = state.vendors.find(function (x) { return x.id === id; }); if (!v) return;
            var alertCfg = alertMinutesFromForm('e-alert-before', 'e-alert-custom');
            if (alertCfg.alertBefore !== 'off' && document.getElementById('e-cutoff').value &&
                typeof Notification !== 'undefined' && Notification.permission === 'default') {
                Notification.requestPermission();
            }
            v.name = document.getElementById('e-name').value.trim();
            v.contact = document.getElementById('e-contact').value.trim();
            v.phone = document.getElementById('e-phone').value.trim();
            v.email = document.getElementById('e-email').value.trim();
            v.accountNumber = document.getElementById('e-account').value.trim();
            v.website = normalizeUrl(document.getElementById('e-website').value);
            v.orderUrl = normalizeUrl(document.getElementById('e-order-url').value);
            v.cutoffTime = document.getElementById('e-cutoff').value || '';
            v.caseMin = document.getElementById('e-case-min').value;
            v.alertBefore = alertCfg.alertBefore;
            v.alertCustomMinutes = alertCfg.alertCustomMinutes;
            v.notes = document.getElementById('e-notes').value.trim();
            v.days = checkedDays(document.getElementById('e-days'));
            save(true); modal.classList.remove('show'); render();
        });
        document.getElementById('e-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });

        // Auto-fill website when typing a known name
        document.getElementById('f-name').addEventListener('blur', function () {
            if (document.getElementById('f-website').value.trim()) return;
            var p = matchPreset(this.value);
            if (p) {
                document.getElementById('f-website').value = p.website || '';
                if (!document.getElementById('f-order-url').value.trim() && p.orderUrl) {
                    document.getElementById('f-order-url').value = p.orderUrl;
                }
            }
        });

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyInvPerms);
            document.addEventListener('pbj-perms-ready', applyInvPerms);
    })();
    </script>
</body>
</html>
