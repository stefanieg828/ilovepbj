<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$contact_types = [
    'manager' => $is_sweet ? 'Manager' : 'Manager',
    'owner' => $is_sweet ? 'Owner / GM' : 'Owner / GM',
    'vendor' => $is_sweet ? 'Vendor' : 'Vendor',
    'emergency' => $is_sweet ? 'Emergency' : 'Emergency',
    'utility' => $is_sweet ? 'Utility / Building' : 'Utility / Building',
    'other' => $is_sweet ? 'Other' : 'Other',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Restaurant Settings' : 'Restaurant Settings'; ?> • <?php echo $is_sweet ? 'Restaurant Operations' : 'Operations'; ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
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
        .card { background: white; border-radius: 18px; padding: 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.45rem; margin: 0 0 14px; }
        .card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 8px; }
        .card-head h2 { margin: 0; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 90px; resize: vertical; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .hours-row { display: grid; grid-template-columns: 50px 1fr 1fr auto; gap: 8px; align-items: center; margin-bottom: 8px; }
        @media (max-width: 520px) { .hours-row { grid-template-columns: 40px 1fr; } .hours-row .to, .hours-row .closed-wrap { grid-column: 2; } }
        .day-label { font-size: 0.95rem; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-add { width: 100%; margin-top: 4px; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .closed-wrap { display: flex; align-items: center; gap: 6px; font-size: 0.9rem; white-space: nowrap; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .item { border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 0 14px 14px 0; padding: 14px 16px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .item-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .item-title { font-size: 1.1rem; margin: 0 0 4px; }
        .item-body { font-size: 0.98rem; opacity: 0.85; line-height: 1.4; white-space: pre-wrap; margin: 0 0 8px; }
        .item-meta { font-size: 0.95rem; opacity: 0.8; line-height: 1.35; margin-bottom: 8px; }
        .item-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.8rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; margin: 0 4px 4px 0; }
        .badge.emergency { <?php if ($is_sweet): ?>background: #FDECEA; color: #B71C1C;<?php else: ?>background: #FDECEA; color: #8B1A1A;<?php endif; ?> }
        .badge.manager, .badge.owner { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .badge.vendor { <?php if ($is_sweet): ?>background: #FFF3E0; color: #8A5A12;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .phone-link { color: inherit; font-weight: 600; text-decoration: none; border-bottom: 1px dashed <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .phone-link:hover { opacity: 0.85; }
        .empty { text-align: center; padding: 18px; opacity: 0.75; line-height: 1.4; }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .search { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; margin-bottom: 12px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white;<?php else: ?>font-family: 'Lora', serif; background: white;<?php endif; ?> }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/ops" class="back-link">← <?php echo $is_sweet ? 'Back to Restaurant Operations' : 'Back to Operations'; ?></a>
        <h1><?php echo $is_sweet ? 'Restaurant Settings' : 'Restaurant Settings'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Location, hours, policies & phone list' : 'Location, hours, policies, and contacts'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <form id="settings-form">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Location' : 'Location'; ?></h2>
                <div class="field"><label><?php echo $is_sweet ? 'Restaurant name' : 'Restaurant name'; ?></label><input id="s-name" placeholder="ilovepbj"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Address' : 'Address'; ?></label><input id="s-address"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'City' : 'City'; ?></label><input id="s-city"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'State' : 'State'; ?></label><input id="s-state"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'ZIP' : 'ZIP'; ?></label><input id="s-zip"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Main phone' : 'Main phone'; ?></label><input id="s-phone" type="tel"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label><input id="s-email" type="email"></div>
                </div>
            </div>

            <div class="card">
                <h2><?php echo $is_sweet ? 'Hours' : 'Hours'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'Set open / close times, or mark closed.' : 'Set open/close times, or mark closed.'; ?></p>
                <div id="hours"></div>
            </div>

            <div class="actions-bar" style="margin-bottom:14px;">
                <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save location & hours ✨' : 'Save location & hours'; ?></button>
            </div>
        </form>

        <!-- House policies (expandable) -->
        <div class="card">
            <div class="card-head">
                <h2><?php echo $is_sweet ? 'House policies' : 'House policies'; ?></h2>
            </div>
            <p class="hint"><?php echo $is_sweet
                ? 'Dress code, call-outs, comps — plus any house rules you need. Add as many as you like 💕'
                : 'Starter policies plus any house rules you need. Add as many as you like.'; ?></p>
            <div id="policies-list"></div>
            <button type="button" class="btn btn-secondary btn-add" id="add-policy-btn"><?php echo $is_sweet ? '+ Add policy' : '+ Add policy'; ?></button>
        </div>

        <!-- Phone list -->
        <div class="card">
            <div class="card-head">
                <h2><?php echo $is_sweet ? 'Phone list' : 'Phone list'; ?></h2>
            </div>
            <p class="hint"><?php echo $is_sweet
                ? 'Managers, vendors, building, and minor emergency numbers — the house contact sheet 📞'
                : 'Managers, vendors, building, and emergency numbers for the house.'; ?></p>
            <input type="search" class="search" id="contact-search" placeholder="<?php echo $is_sweet ? 'Search name, number, notes…' : 'Search contacts…'; ?>">
            <div class="filters" id="contact-filters">
                <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
                <button type="button" class="chip" data-filter="manager"><?php echo $is_sweet ? 'Managers' : 'Managers'; ?></button>
                <button type="button" class="chip" data-filter="vendor"><?php echo $is_sweet ? 'Vendors' : 'Vendors'; ?></button>
                <button type="button" class="chip" data-filter="emergency"><?php echo $is_sweet ? 'Emergency' : 'Emergency'; ?></button>
                <button type="button" class="chip" data-filter="other"><?php echo $is_sweet ? 'Other' : 'Other'; ?></button>
            </div>
            <div id="contacts-list"></div>
            <button type="button" class="btn btn-secondary btn-add" id="add-contact-btn"><?php echo $is_sweet ? '+ Add contact' : '+ Add contact'; ?></button>
        </div>

        <div class="actions-bar">
            <a href="/admin/ops" class="btn btn-secondary"><?php echo $is_sweet ? 'Restaurant Operations' : 'Operations'; ?></a>
            <button type="button" class="btn btn-primary" id="print-btn"><?php echo $is_sweet ? '🖨️ Print contacts' : 'Print contacts'; ?></button>
        </div>
    </div>

    <!-- Policy modal -->
    <div class="modal-backdrop" id="policy-modal">
        <div class="modal">
            <h2 id="policy-modal-title"><?php echo $is_sweet ? 'Add policy' : 'Add policy'; ?></h2>
            <form id="policy-form">
                <input type="hidden" id="p-id">
                <div class="field"><label><?php echo $is_sweet ? 'Title' : 'Title'; ?></label><input id="p-title" required placeholder="<?php echo $is_sweet ? 'e.g. Dress code, Late arrivals…' : 'e.g. Dress code'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Details' : 'Details'; ?></label><textarea id="p-body" rows="5" placeholder="<?php echo $is_sweet ? 'What the house expects…' : 'Policy details…'; ?>"></textarea></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="p-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contact modal -->
    <div class="modal-backdrop" id="contact-modal">
        <div class="modal">
            <h2 id="contact-modal-title"><?php echo $is_sweet ? 'Add contact' : 'Add contact'; ?></h2>
            <form id="contact-form">
                <input type="hidden" id="c-id">
                <div class="field"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="c-name" required placeholder="<?php echo $is_sweet ? 'Person, company, or service' : 'Name'; ?>"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Type' : 'Type'; ?></label>
                        <select id="c-type">
                            <?php foreach ($contact_types as $k => $label): ?>
                            <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label><input id="c-phone" type="tel" required placeholder="555-123-4567"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Role / company' : 'Role / company'; ?></label><input id="c-role" placeholder="<?php echo $is_sweet ? 'GM, Sysco rep, landlord…' : 'Title or company'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="c-notes" placeholder="<?php echo $is_sweet ? 'After hours? Account #? When to call…' : 'When to call, account #…'; ?>"></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="c-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="shared-state.js?v=3"></script>
    <script>
    (function () {
        const KEY = 'pbj_admin_settings_v2';
        const OLD_KEY = 'pbj_admin_settings_v1';
        const SHARED_KEY = 'admin_settings_v1';
        const days = <?php echo json_encode($days); ?>;
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applySettingsPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                // coarse: if no settings perms at all, soft-lock
                var any = canP('admin.ops.settings.location') || canP('admin.ops.settings.hours') || canP('admin.ops.settings.policies') || canP('admin.ops.settings.phone');
                if (!any) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('set-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="set-denied">No permission to edit restaurant settings.</div>');
                    }
                }
                document.querySelectorAll('[data-act="del-pol" data-need-perm="admin.ops.settings.policies"], [data-act="edit-pol"]').forEach(function(el){
                    el.style.display = canP('admin.ops.settings.policies') ? '' : 'none';
                });
                document.querySelectorAll('[data-act="del-con" data-need-perm="admin.ops.settings.phone"], [data-act="edit-con"]').forEach(function(el){
                    el.style.display = canP('admin.ops.settings.phone') ? '' : 'none';
                });
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        const TYPE_LABELS = <?php echo json_encode($contact_types, JSON_UNESCAPED_UNICODE); ?>;

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function defaultPolicies() {
            return [
                { id: 'pol-dress', title: isSweet ? 'Dress code' : 'Dress code', body: '', updatedAt: 0 },
                { id: 'pol-callout', title: isSweet ? 'Call-out policy' : 'Call-out policy', body: '', updatedAt: 0 },
                { id: 'pol-comp', title: isSweet ? 'Comp / discount rules' : 'Comp / discount rules', body: '', updatedAt: 0 }
            ];
        }

        function emptyHours() {
            var hours = {};
            days.forEach(function (d) { hours[d] = { open: '11:00', close: '21:00', closed: false }; });
            return hours;
        }

        function emptyState() {
            return {
                name: 'ilovepbj',
                address: '', city: '', state: '', zip: '',
                phone: '', email: '',
                hours: emptyHours(),
                policies: defaultPolicies(),
                contacts: [],
                structureAt: Date.now()
            };
        }

        function normalizePolicy(p) {
            return {
                id: p.id || uid(),
                title: p.title || (isSweet ? 'House policy' : 'House policy'),
                body: p.body || '',
                updatedAt: p.updatedAt || 0
            };
        }

        function normalizeContact(c) {
            var t = c.type || 'other';
            if (!TYPE_LABELS[t]) t = 'other';
            return {
                id: c.id || uid(),
                name: c.name || '',
                type: t,
                phone: c.phone || '',
                role: c.role || '',
                notes: c.notes || '',
                updatedAt: c.updatedAt || 0
            };
        }

        function migrateV1(old) {
            if (!old) return null;
            var s = emptyState();
            s.name = old.name || s.name;
            s.address = old.address || '';
            s.city = old.city || '';
            s.state = old.state || '';
            s.zip = old.zip || '';
            s.phone = old.phone || '';
            s.email = old.email || '';
            if (old.hours) s.hours = old.hours;
            var policies = defaultPolicies();
            if (old.dress) policies[0].body = old.dress;
            if (old.callout) policies[1].body = old.callout;
            if (old.comp) policies[2].body = old.comp;
            if (old.other) {
                policies.push({ id: 'pol-other', title: isSweet ? 'Other house notes' : 'Other house notes', body: old.other, updatedAt: 0 });
            }
            s.policies = policies;
            s.contacts = Array.isArray(old.contacts) ? old.contacts.map(normalizeContact) : [];
            return s;
        }

        function loadLocal() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) {
                    var old = JSON.parse(localStorage.getItem(OLD_KEY) || 'null');
                    r = migrateV1(old) || emptyState();
                    localStorage.setItem(KEY, JSON.stringify(r));
                }
                if (!r.hours) r.hours = emptyHours();
                if (!Array.isArray(r.policies)) r.policies = defaultPolicies();
                else r.policies = r.policies.map(normalizePolicy);
                if (!Array.isArray(r.contacts)) r.contacts = [];
                else r.contacts = r.contacts.map(normalizeContact);
                if (!r.structureAt) r.structureAt = Date.now();
                return r;
            } catch (e) { return emptyState(); }
        }

        var state = loadLocal();
        var shared = null;
        var applyingRemote = false;
        var contactFilter = 'all';
        var contactSearch = '';
        var policyModal = document.getElementById('policy-modal');
        var contactModal = document.getElementById('contact-modal');

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = typeof msg === 'string' ? msg : (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1200);
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

        function save(showToast, opts) {
            opts = opts || {};
            state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            // Keep v1 fields lightly in sync for any older readers
            try {
                var legacy = {
                    name: state.name, address: state.address, city: state.city, state: state.state, zip: state.zip,
                    phone: state.phone, email: state.email, hours: state.hours,
                    dress: '', callout: '', comp: '', other: ''
                };
                (state.policies || []).forEach(function (p) {
                    var t = (p.title || '').toLowerCase();
                    if (t.indexOf('dress') !== -1) legacy.dress = p.body || '';
                    else if (t.indexOf('call') !== -1) legacy.callout = p.body || '';
                    else if (t.indexOf('comp') !== -1 || t.indexOf('discount') !== -1) legacy.comp = p.body || '';
                    else if (p.body) legacy.other += (legacy.other ? '\n\n' : '') + (p.title ? p.title + ': ' : '') + p.body;
                });
                localStorage.setItem(OLD_KEY, JSON.stringify(legacy));
            } catch (e) {}
            if (showToast) toast(showToast === true ? undefined : showToast);
            if (!opts.skipRemote && shared) shared.queuePush(state);
        }

        function applyRemote(payload) {
            if (!payload) return;
            applyingRemote = true;
            state = {
                name: payload.name || '',
                address: payload.address || '',
                city: payload.city || '',
                state: payload.state || '',
                zip: payload.zip || '',
                phone: payload.phone || '',
                email: payload.email || '',
                hours: payload.hours || emptyHours(),
                policies: Array.isArray(payload.policies) ? payload.policies.map(normalizePolicy) : defaultPolicies(),
                contacts: Array.isArray(payload.contacts) ? payload.contacts.map(normalizeContact) : [],
                structureAt: payload.structureAt || Date.now()
            };
            localStorage.setItem(KEY, JSON.stringify(state));
            paintLocationHours();
            renderPolicies();
            renderContacts();
            applyingRemote = false;
        }

        function paintLocationHours() {
            document.getElementById('s-name').value = state.name || '';
            document.getElementById('s-address').value = state.address || '';
            document.getElementById('s-city').value = state.city || '';
            document.getElementById('s-state').value = state.state || '';
            document.getElementById('s-zip').value = state.zip || '';
            document.getElementById('s-phone').value = state.phone || '';
            document.getElementById('s-email').value = state.email || '';
            document.getElementById('hours').innerHTML = days.map(function (d) {
                var h = state.hours[d] || { open: '11:00', close: '21:00', closed: false };
                return '<div class="hours-row" data-day="' + d + '">' +
                    '<span class="day-label">' + d + '</span>' +
                    '<input type="time" class="h-open" value="' + (h.open || '11:00') + '"' + (h.closed ? ' disabled' : '') + '>' +
                    '<input type="time" class="h-close to" value="' + (h.close || '21:00') + '"' + (h.closed ? ' disabled' : '') + '>' +
                    '<label class="closed-wrap"><input type="checkbox" class="h-closed"' + (h.closed ? ' checked' : '') + '> ' + (isSweet ? 'Closed' : 'Closed') + '</label>' +
                    '</div>';
            }).join('');
        }

        function renderPolicies() {
            var root = document.getElementById('policies-list');
            var list = (state.policies || []).slice();
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No policies yet — add your first house rule 💕' : 'No policies yet. Add one above.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (p) {
                var body = p.body
                    ? '<div class="item-body">' + esc(p.body) + '</div>'
                    : '<div class="item-body" style="opacity:0.55;">' + (isSweet ? 'No details yet — tap Edit to fill in' : 'No details yet') + '</div>';
                return '<div class="item" data-id="' + esc(p.id) + '">' +
                    '<div class="item-top"><h3 class="item-title">' + esc(p.title) + '</h3></div>' +
                    body +
                    '<div class="item-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit-pol" data-id="' + esc(p.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del-pol" data-need-perm="admin.ops.settings.policies" data-id="' + esc(p.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function telHref(phone) {
            return 'tel:' + String(phone || '').replace(/[^\d+]/g, '');
        }

        function renderContacts() {
            var root = document.getElementById('contacts-list');
            var list = (state.contacts || []).slice().sort(function (a, b) {
                // Emergency first, then name
                if (a.type === 'emergency' && b.type !== 'emergency') return -1;
                if (b.type === 'emergency' && a.type !== 'emergency') return 1;
                return (a.name || '').localeCompare(b.name || '');
            });
            if (contactFilter === 'manager') {
                list = list.filter(function (c) { return c.type === 'manager' || c.type === 'owner'; });
            } else if (contactFilter === 'other') {
                list = list.filter(function (c) { return c.type === 'other' || c.type === 'utility'; });
            } else if (contactFilter !== 'all') {
                list = list.filter(function (c) { return c.type === contactFilter; });
            }
            if (contactSearch) {
                var q = contactSearch.toLowerCase();
                list = list.filter(function (c) {
                    return [c.name, c.phone, c.role, c.notes, TYPE_LABELS[c.type] || ''].join(' ').toLowerCase().indexOf(q) !== -1;
                });
            }
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No contacts yet — add managers, vendors, or emergency numbers 📞'
                    : 'No contacts match. Add managers, vendors, or emergency numbers.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (c) {
                var typeLabel = TYPE_LABELS[c.type] || c.type;
                var badgeClass = 'badge ' + esc(c.type);
                var meta = [c.role].filter(Boolean).join(' · ');
                return '<div class="item" data-id="' + esc(c.id) + '">' +
                    '<div class="item-top"><div>' +
                    '<h3 class="item-title">' + esc(c.name) + '</h3>' +
                    '<span class="' + badgeClass + '">' + esc(typeLabel) + '</span>' +
                    '</div></div>' +
                    '<div class="item-meta"><a class="phone-link" href="' + esc(telHref(c.phone)) + '">' + esc(c.phone) + '</a>' +
                    (meta ? ' · ' + esc(meta) : '') + '</div>' +
                    (c.notes ? '<div class="item-body" style="margin-bottom:8px;">' + esc(c.notes) + '</div>' : '') +
                    '<div class="item-actions">' +
                    '<a class="btn btn-small btn-ghost" href="' + esc(telHref(c.phone)) + '">' + (isSweet ? 'Call' : 'Call') + '</a>' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit-con" data-id="' + esc(c.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del-con" data-need-perm="admin.ops.settings.phone" data-id="' + esc(c.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        // Location / hours
        document.getElementById('hours').addEventListener('change', function (e) {
            var row = e.target.closest('.hours-row'); if (!row) return;
            if (e.target.classList.contains('h-closed')) {
                var closed = e.target.checked;
                row.querySelector('.h-open').disabled = closed;
                row.querySelector('.h-close').disabled = closed;
            }
        });

        document.getElementById('settings-form').addEventListener('submit', function (e) {
            e.preventDefault();
            state.name = document.getElementById('s-name').value.trim();
            state.address = document.getElementById('s-address').value.trim();
            state.city = document.getElementById('s-city').value.trim();
            state.state = document.getElementById('s-state').value.trim();
            state.zip = document.getElementById('s-zip').value.trim();
            state.phone = document.getElementById('s-phone').value.trim();
            state.email = document.getElementById('s-email').value.trim();
            if (!state.hours) state.hours = emptyHours();
            document.querySelectorAll('.hours-row').forEach(function (row) {
                var d = row.dataset.day;
                state.hours[d] = {
                    open: row.querySelector('.h-open').value,
                    close: row.querySelector('.h-close').value,
                    closed: row.querySelector('.h-closed').checked
                };
            });
            save(isSweet ? 'Location & hours saved ✨' : 'Location & hours saved');
        });

        // Policies
        function openPolicyModal(p) {
            document.getElementById('policy-modal-title').textContent = p
                ? (isSweet ? 'Edit policy' : 'Edit policy')
                : (isSweet ? 'Add policy' : 'Add policy');
            document.getElementById('p-id').value = p ? p.id : '';
            document.getElementById('p-title').value = p ? (p.title || '') : '';
            document.getElementById('p-body').value = p ? (p.body || '') : '';
            policyModal.classList.add('show');
            setTimeout(function () { document.getElementById('p-title').focus(); }, 40);
        }

        document.getElementById('add-policy-btn').addEventListener('click', function () { openPolicyModal(null); });
        document.getElementById('p-cancel').addEventListener('click', function () { policyModal.classList.remove('show'); });
        policyModal.addEventListener('click', function (e) { if (e.target === policyModal) policyModal.classList.remove('show'); });

        document.getElementById('policy-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('p-id').value;
            var title = document.getElementById('p-title').value.trim();
            var body = document.getElementById('p-body').value.trim();
            if (!title) return;
            if (id) {
                var existing = state.policies.find(function (x) { return x.id === id; });
                if (existing) {
                    existing.title = title;
                    existing.body = body;
                    existing.updatedAt = Date.now();
                }
            } else {
                state.policies.push(normalizePolicy({ id: uid(), title: title, body: body, updatedAt: Date.now() }));
            }
            policyModal.classList.remove('show');
            save(true);
            renderPolicies();
        });

        document.getElementById('policies-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            var p = state.policies.find(function (x) { return x.id === id; });
            if (btn.dataset.act === 'del-pol') {
                if (!confirm(isSweet ? 'Remove this policy?' : 'Remove this policy?')) return;
                state.policies = state.policies.filter(function (x) { return x.id !== id; });
                save(true);
                renderPolicies();
                return;
            }
            if (btn.dataset.act === 'edit-pol' && p) openPolicyModal(p);
        });

        // Contacts
        function openContactModal(c) {
            document.getElementById('contact-modal-title').textContent = c
                ? (isSweet ? 'Edit contact' : 'Edit contact')
                : (isSweet ? 'Add contact' : 'Add contact');
            document.getElementById('c-id').value = c ? c.id : '';
            document.getElementById('c-name').value = c ? (c.name || '') : '';
            document.getElementById('c-type').value = c ? (c.type || 'other') : 'manager';
            document.getElementById('c-phone').value = c ? (c.phone || '') : '';
            document.getElementById('c-role').value = c ? (c.role || '') : '';
            document.getElementById('c-notes').value = c ? (c.notes || '') : '';
            contactModal.classList.add('show');
            setTimeout(function () { document.getElementById('c-name').focus(); }, 40);
        }

        document.getElementById('add-contact-btn').addEventListener('click', function () { openContactModal(null); });
        document.getElementById('c-cancel').addEventListener('click', function () { contactModal.classList.remove('show'); });
        contactModal.addEventListener('click', function (e) { if (e.target === contactModal) contactModal.classList.remove('show'); });

        document.getElementById('contact-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('c-id').value;
            var data = {
                id: id || uid(),
                name: document.getElementById('c-name').value.trim(),
                type: document.getElementById('c-type').value,
                phone: document.getElementById('c-phone').value.trim(),
                role: document.getElementById('c-role').value.trim(),
                notes: document.getElementById('c-notes').value.trim(),
                updatedAt: Date.now()
            };
            if (!data.name || !data.phone) return;
            if (id) {
                var idx = state.contacts.findIndex(function (x) { return x.id === id; });
                if (idx !== -1) state.contacts[idx] = normalizeContact(data);
                else state.contacts.push(normalizeContact(data));
            } else {
                state.contacts.push(normalizeContact(data));
            }
            contactModal.classList.remove('show');
            save(true);
            renderContacts();
        });

        document.getElementById('contacts-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            var c = state.contacts.find(function (x) { return x.id === id; });
            if (btn.dataset.act === 'del-con') {
                if (!confirm(isSweet ? 'Remove this contact?' : 'Remove this contact?')) return;
                state.contacts = state.contacts.filter(function (x) { return x.id !== id; });
                save(true);
                renderContacts();
                return;
            }
            if (btn.dataset.act === 'edit-con' && c) openContactModal(c);
        });

        document.getElementById('contact-filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.chip'); if (!chip) return;
            contactFilter = chip.dataset.filter;
            document.querySelectorAll('#contact-filters .chip').forEach(function (c) {
                c.classList.toggle('active', c === chip);
            });
            renderContacts();
        });
        document.getElementById('contact-search').addEventListener('input', function () {
            contactSearch = this.value.trim();
            renderContacts();
        });

        document.getElementById('print-btn').addEventListener('click', function () {
            var list = (state.contacts || []).slice().sort(function (a, b) {
                if (a.type === 'emergency' && b.type !== 'emergency') return -1;
                if (b.type === 'emergency' && a.type !== 'emergency') return 1;
                return (a.name || '').localeCompare(b.name || '');
            });
            var w = window.open('', '_blank');
            if (!w) return;
            var html = '<html><head><title>Phone List</title><style>body{font-family:serif;padding:20px;} h1{font-size:18pt;} table{width:100%;border-collapse:collapse;} td,th{border-bottom:1px solid #ccc;padding:6px;text-align:left;font-size:11pt;} .em{color:#B71C1C;font-weight:bold;}</style></head><body>';
            html += '<h1>' + esc(state.name || 'Restaurant') + ' — ' + (isSweet ? 'Phone List' : 'Phone List') + '</h1>';
            if (state.phone) html += '<p>Main: ' + esc(state.phone) + '</p>';
            html += '<table><tr><th>Name</th><th>Type</th><th>Phone</th><th>Role</th><th>Notes</th></tr>';
            list.forEach(function (c) {
                var cls = c.type === 'emergency' ? ' class="em"' : '';
                html += '<tr' + cls + '><td>' + esc(c.name) + '</td><td>' + esc(TYPE_LABELS[c.type] || c.type) + '</td><td>' + esc(c.phone) + '</td><td>' + esc(c.role) + '</td><td>' + esc(c.notes) + '</td></tr>';
            });
            html += '</table></body></html>';
            w.document.write(html);
            w.document.close();
            w.focus();
            w.print();
        });

        paintLocationHours();
        renderPolicies();
        renderContacts();

        if (window.PbjSharedState) {
            shared = new PbjSharedState({
                key: SHARED_KEY,
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
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only' : 'Local only' });
        }
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applySettingsPerms);
            document.addEventListener('pbj-perms-ready', applySettingsPerms);
})();
    </script>
</body>
</html>
