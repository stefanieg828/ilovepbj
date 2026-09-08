<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

$job_roles = [
    'Owner', 'GM', 'Manager', 'Shift Lead',
    'Server', 'Host', 'Bartender',
    'BOH Cook', 'Prep', 'Dish', 'Other',
];

// Maps house job titles → access keys used by floor / future features
$access_keys = [
    'owner' => $is_sweet ? 'Owner' : 'Owner',
    'admin' => $is_sweet ? 'Admin / GM' : 'Admin / GM',
    'manager' => $is_sweet ? 'Manager / Shift Lead' : 'Manager / Shift Lead',
    'foh' => $is_sweet ? 'FOH (Server, Host, Bar)' : 'FOH',
    'boh' => $is_sweet ? 'BOH (Cook, Prep, Dish)' : 'BOH',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Crew roster' : 'Team roster'; ?> • <?php echo $is_sweet ? 'Team & Roles' : 'Team'; ?> • ilovepbj ops</title>
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
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.6rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.5rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.85rem; opacity: 0.7; margin-top: 4px; }
        .tabs { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
        .tab { flex: 1; min-width: 100px; border: none; border-radius: 14px; padding: 12px 8px; font-size: 0.95rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); background: white; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .panel { display: none; } .panel.active { display: block; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.45rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .person { border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 0 14px 14px 0; padding: 14px 16px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .person.inactive { opacity: 0.55; border-left-color: #B0B0B0; }
        .person-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .person-name { font-size: 1.2rem; margin: 0 0 4px; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.8rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; margin: 0 4px 4px 0; }
        .badge.perm { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .meta { font-size: 0.95rem; opacity: 0.8; line-height: 1.4; margin-bottom: 8px; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .search { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; margin-bottom: 12px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white;<?php else: ?>font-family: 'Lora', serif; background: white;<?php endif; ?> }
        .empty { text-align: center; padding: 28px; opacity: 0.8; line-height: 1.4; }
        .actions-bar { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .perm-row { display: flex; align-items: flex-start; gap: 10px; padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .perm-row:last-child { border-bottom: none; }
        .perm-row label { flex: 1; cursor: pointer; line-height: 1.35; }
        .perm-row input { margin-top: 4px; width: 18px; height: 18px; }
        .perm-sub { font-size: 0.88rem; opacity: 0.7; margin-top: 2px; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .check-grid { display: grid; grid-template-columns: 1fr; gap: 8px; }
        .role-pick { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; border: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; border-radius: 12px; padding: 10px 12px; margin: 0; cursor: pointer; }
        .role-pick input[type="checkbox"] { width: 18px; height: 18px; margin: 0; flex-shrink: 0; }
        .role-pick .role-name { flex: 1; min-width: 90px; }
        .role-pick .wage-wrap { display: flex; align-items: center; gap: 6px; font-size: 0.88rem; opacity: 0.85; }
        .role-pick .wage-wrap input { width: 88px; box-sizing: border-box; border-radius: 10px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 6px 8px; font-size: 0.95rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .role-pick .wage-wrap input:disabled { opacity: 0.4; }
        .role-pick:has(input[type="checkbox"]:checked) { <?php if ($is_sweet): ?>border-color: #E55163; background: #FFFBF8;<?php else: ?>border-color: #1A2A44; background: #FAF8F5;<?php endif; ?> }
        .badge.wage { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/team" class="back-link">← <?php echo $is_sweet ? 'Back to Team & Roles' : 'Back to Team'; ?></a>
        <h1><?php echo $is_sweet ? 'Crew roster' : 'Team roster'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Names, roles, wages & permissions' : 'Staff roster, roles, and permissions'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Build your crew list — multiple roles with optional $/hr next to each hat. Labor pulls those rates. Special powers live under Permissions 💕'
                : 'Build your crew — multiple roles with optional $/hr per role. Labor uses those rates. Permissions stay role-based.'; ?>
        </div>
        <div class="sync-pill syncing" id="sync-pill">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>
        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-total">0</div><div class="lbl"><?php echo $is_sweet ? 'Team' : 'Team'; ?></div></div>
            <div class="stat"><div class="num" id="stat-active">0</div><div class="lbl"><?php echo $is_sweet ? 'Active' : 'Active'; ?></div></div>
            <div class="stat"><div class="num" id="stat-roles">0</div><div class="lbl"><?php echo $is_sweet ? 'Roles used' : 'Roles used'; ?></div></div>
        </div>

        <div class="tabs">
            <button type="button" class="tab active" data-tab="roster"><?php echo $is_sweet ? '👥 Roster' : 'Roster'; ?></button>
            <button type="button" class="tab" data-tab="perms"><?php echo $is_sweet ? '🔐 Permissions' : 'Permissions'; ?></button>
        </div>

        <!-- Roster -->
        <div class="panel active" id="panel-roster">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Add teammate' : 'Add teammate'; ?></h2>
                <form id="add-form">
                    <div class="field"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="f-name" required placeholder="<?php echo $is_sweet ? 'First & last' : 'Name'; ?>"></div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Roles & wage (pick all that apply)' : 'Roles & wage (select all that apply)'; ?></label>
                        <p class="hint" style="margin-top:0;"><?php echo $is_sweet ? 'Many hats welcome — set $/hr next to each role (optional, but Labor loves it) 💕' : 'Select roles and optional $/hr for each. Used by Labor Snapshot.'; ?></p>
                        <div class="check-grid" id="f-roles">
                            <?php foreach ($job_roles as $r): ?>
                            <label class="role-pick">
                                <input type="checkbox" name="f-role" value="<?php echo htmlspecialchars($r); ?>" data-role-check="f"<?php echo $r === 'Server' ? ' checked' : ''; ?>>
                                <span class="role-name"><?php echo htmlspecialchars($r); ?></span>
                                <span class="wage-wrap">
                                    <span>$/hr</span>
                                    <input type="number" min="0" step="0.01" class="role-wage" data-role="<?php echo htmlspecialchars($r); ?>" placeholder="0.00"<?php echo $r === 'Server' ? '' : ' disabled'; ?> inputmode="decimal">
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field"><label><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label><input id="f-phone" type="tel"></div>
                        <div class="field"><label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label><input id="f-email" type="email"></div>
                    </div>
                    <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="f-notes" placeholder="<?php echo $is_sweet ? 'Keys, training, section prefs…' : 'Keys, training, section prefs…'; ?>"></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;" data-perm="admin.team.roster.add"><?php echo $is_sweet ? 'Add to roster ✨' : 'Add to roster'; ?></button>

                </form>
            </div>

            <input type="search" class="search" id="search" placeholder="<?php echo $is_sweet ? 'Search name, role, phone…' : 'Search…'; ?>">
            <div class="filters" id="filters">
                <button type="button" class="chip active" data-filter="active"><?php echo $is_sweet ? 'Active' : 'Active'; ?></button>
                <button type="button" class="chip" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
                <button type="button" class="chip" data-filter="inactive"><?php echo $is_sweet ? 'Inactive' : 'Inactive'; ?></button>
            </div>
            <div class="toolbar">
                <button type="button" class="btn btn-secondary" id="print-btn" data-perm="admin.team.roster.print"><?php echo $is_sweet ? '🖨️ Print roster' : 'Print roster'; ?></button>
                <button type="button" class="btn btn-secondary" id="export-btn" data-perm="admin.team.roster.export"><?php echo $is_sweet ? 'Export CSV' : 'Export CSV'; ?></button>
            </div>
            <div id="list"></div>
        </div>

                <!-- Permissions -->
        <div class="panel" id="panel-perms">
            <div class="card" id="perms-gate">
                <h2><?php echo $is_sweet ? 'Permissions by role' : 'Permissions by role'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'Pick an <strong>access role</strong>, then toggle what that role can do across FOH, BOH, Admin, Messages, and Settings. <strong>Owner</strong> always has full access. General Managers can manage this matrix 🔐'
                    : 'Select an access role and toggle capabilities. Owner always has full access. GMs can edit this matrix.'; ?></p>
                <div class="filters" id="perm-role-tabs" style="margin-bottom:12px;"></div>
                <div id="perm-editor" style="max-height:62vh;overflow:auto;"></div>
                <div class="toolbar" style="margin-top:14px;">
                    <button type="button" class="btn btn-secondary" id="reset-role-defaults"><?php echo $is_sweet ? 'Reset this role to defaults' : 'Reset role defaults'; ?></button>
                    <button type="button" class="btn btn-primary" id="save-role-perms" style="flex:1;"><?php echo $is_sweet ? 'Save permissions ✨' : 'Save permissions'; ?></button>
                </div>
                <p class="hint" style="margin-top:12px;margin-bottom:0;" id="perm-status"></p>
            </div>
            <div class="card" id="perms-locked" style="display:none;">
                <h2><?php echo $is_sweet ? 'Permissions locked' : 'No access'; ?></h2>
                <p class="hint" style="margin:0;"><?php echo $is_sweet
                    ? 'Only Owners and General Managers can change house permissions. Ask them if you need a power unlocked 💕'
                    : 'Only Owners and General Managers can change permissions.'; ?></p>
            </div>
        </div>

<div class="actions-bar">
            <a href="/FOH/floor-plan" class="btn btn-secondary"><?php echo $is_sweet ? '🪑 Floor plan' : 'Floor plan'; ?></a>
            <a href="/admin/team" class="btn btn-primary"><?php echo $is_sweet ? 'Team & Roles' : 'Team hub'; ?></a>
        </div>
    </div>

    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2><?php echo $is_sweet ? 'Edit teammate' : 'Edit teammate'; ?></h2>
            <form id="edit-form">
                <input type="hidden" id="e-id">
                <div class="field"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="e-name" required></div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Roles & wage (pick all that apply)' : 'Roles & wage (select all that apply)'; ?></label>
                    <div class="check-grid" id="e-roles">
                        <?php foreach ($job_roles as $r): ?>
                        <label class="role-pick">
                            <input type="checkbox" name="e-role" value="<?php echo htmlspecialchars($r); ?>" data-role-check="e">
                            <span class="role-name"><?php echo htmlspecialchars($r); ?></span>
                            <span class="wage-wrap">
                                <span>$/hr</span>
                                <input type="number" min="0" step="0.01" class="role-wage" data-role="<?php echo htmlspecialchars($r); ?>" placeholder="0.00" disabled inputmode="decimal">
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label><input id="e-phone" type="tel"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label><input id="e-email" type="email"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="e-notes"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Status' : 'Status'; ?></label>
                    <select id="e-status">
                        <option value="active"><?php echo $is_sweet ? 'Active' : 'Active'; ?></option>
                        <option value="inactive"><?php echo $is_sweet ? 'Inactive' : 'Inactive'; ?></option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="e-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
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
        const KEY = 'pbj_admin_team_v2';
        const SHARED_KEY = 'admin_team_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        const ACCESS_KEYS = <?php echo json_encode($access_keys, JSON_UNESCAPED_UNICODE); ?>;
        const DEFAULT_FLOOR_ROLES = ['owner', 'admin', 'manager'];

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        function emptyState() {
            return {
                people: [],
                deletedIds: {},
                structureAt: Date.now(),
                permissions: {
                    floorLayout: { roles: DEFAULT_FLOOR_ROLES.slice() }
                }
            };
        }

        /** Normalize roles array; keep legacy `role` as primary for older readers */
        function normalizeRoles(p) {
            var roles = [];
            if (Array.isArray(p.roles) && p.roles.length) {
                p.roles.forEach(function (r) {
                    // support [{role, wage}] or string
                    if (r && typeof r === 'object') r = r.role || r.name || '';
                    r = String(r || '').trim();
                    if (r && roles.indexOf(r) === -1) roles.push(r);
                });
            }
            if (p.role) {
                var single = String(p.role).trim();
                if (single && roles.indexOf(single) === -1) roles.unshift(single);
            }
            if (!roles.length) roles = ['Other'];
            return roles;
        }

        function normalizeRoleWages(p, roles) {
            var wages = {};
            if (p && p.roleWages && typeof p.roleWages === 'object' && !Array.isArray(p.roleWages)) {
                Object.keys(p.roleWages).forEach(function (k) {
                    var n = parseFloat(p.roleWages[k]);
                    if (!isNaN(n) && n >= 0) wages[k] = Math.round(n * 100) / 100;
                });
            }
            // legacy: single wage field
            if (p && p.wage != null && p.wage !== '' && !isNaN(parseFloat(p.wage))) {
                var primary = (roles && roles[0]) || p.role || 'Other';
                if (wages[primary] == null) wages[primary] = Math.round(parseFloat(p.wage) * 100) / 100;
            }
            // array form roles: [{role, wage}]
            if (Array.isArray(p.roles)) {
                p.roles.forEach(function (r) {
                    if (r && typeof r === 'object' && (r.role || r.name)) {
                        var name = String(r.role || r.name).trim();
                        var n = parseFloat(r.wage);
                        if (name && !isNaN(n) && n >= 0 && wages[name] == null) {
                            wages[name] = Math.round(n * 100) / 100;
                        }
                    }
                });
            }
            return wages;
        }

        function normalizePerson(p) {
            var roles = normalizeRoles(p || {});
            var roleWages = normalizeRoleWages(p || {}, roles);
            return {
                id: p.id || uid(),
                name: p.name || '',
                roles: roles,
                role: roles[0] || 'Other',
                roleWages: roleWages,
                phone: p.phone || '',
                email: p.email || '',
                notes: p.notes || '',
                status: p.status === 'inactive' ? 'inactive' : 'active',
                updatedAt: p.updatedAt || 0
            };
        }

        function moneyWage(n) {
            if (n == null || n === '' || isNaN(n)) return '';
            return '$' + (Math.round(parseFloat(n) * 100) / 100).toFixed(2);
        }

        function readRolesAndWages(containerSel) {
            var roles = [];
            var roleWages = {};
            document.querySelectorAll(containerSel + ' .role-pick').forEach(function (row) {
                var cb = row.querySelector('input[type="checkbox"]');
                var wageIn = row.querySelector('.role-wage');
                if (!cb || !cb.checked) return;
                var v = cb.value;
                if (!v || roles.indexOf(v) !== -1) return;
                roles.push(v);
                if (wageIn && wageIn.value !== '') {
                    var n = parseFloat(wageIn.value);
                    if (!isNaN(n) && n >= 0) roleWages[v] = Math.round(n * 100) / 100;
                }
            });
            return { roles: roles, roleWages: roleWages };
        }

        function setRolesAndWages(containerSel, roles, roleWages) {
            var set = {};
            (roles || []).forEach(function (r) { set[r] = true; });
            roleWages = roleWages || {};
            document.querySelectorAll(containerSel + ' .role-pick').forEach(function (row) {
                var cb = row.querySelector('input[type="checkbox"]');
                var wageIn = row.querySelector('.role-wage');
                if (!cb) return;
                cb.checked = !!set[cb.value];
                if (wageIn) {
                    wageIn.disabled = !cb.checked;
                    if (cb.checked && roleWages[cb.value] != null && roleWages[cb.value] !== '') {
                        wageIn.value = roleWages[cb.value];
                    } else if (!cb.checked) {
                        wageIn.value = '';
                    }
                }
            });
        }

        function wireRoleWageToggles(containerSel) {
            var root = document.querySelector(containerSel);
            if (!root) return;
            root.addEventListener('change', function (e) {
                var cb = e.target.closest('input[type="checkbox"]');
                if (!cb) return;
                var row = cb.closest('.role-pick');
                if (!row) return;
                var wageIn = row.querySelector('.role-wage');
                if (!wageIn) return;
                wageIn.disabled = !cb.checked;
                if (!cb.checked) wageIn.value = '';
                else setTimeout(function () { wageIn.focus(); }, 10);
            });
            // stop label toggle when clicking wage input
            root.addEventListener('click', function (e) {
                if (e.target.classList.contains('role-wage') || e.target.closest('.wage-wrap')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.target.classList.contains('role-wage')) e.target.focus();
                }
            });
        }

        function rolesLabel(p) {
            var roles = (p && p.roles && p.roles.length) ? p.roles : (p && p.role ? [p.role] : []);
            var wages = (p && p.roleWages) || {};
            return roles.map(function (r) {
                var w = wages[r];
                return w != null && w !== '' ? (r + ' ' + moneyWage(w)) : r;
            }).join(', ');
        }

        function pruneDeletedPeople(r) {
            if (!r.deletedIds || typeof r.deletedIds !== 'object') r.deletedIds = {};
            r.people = (r.people || []).filter(function (p) {
                var id = String(p.id || '');
                if (!id) return true;
                var delAt = r.deletedIds[id];
                if (delAt == null) return true;
                // Re-added / updated after delete keeps them
                if ((p.updatedAt || 0) > delAt) {
                    delete r.deletedIds[id];
                    return true;
                }
                return false;
            });
            return r;
        }

        function loadLocal() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) {
                    // migrate v1
                    var old = JSON.parse(localStorage.getItem('pbj_admin_team_v1') || 'null');
                    if (old && Array.isArray(old.people)) {
                        r = { people: old.people, deletedIds: {}, structureAt: Date.now(), permissions: { floorLayout: { roles: DEFAULT_FLOOR_ROLES.slice() } } };
                    }
                }
                if (!r || !Array.isArray(r.people)) return emptyState();
                r.people = r.people.map(normalizePerson);
                if (!r.deletedIds || typeof r.deletedIds !== 'object') r.deletedIds = {};
                if (!r.permissions || !r.permissions.floorLayout) {
                    r.permissions = { floorLayout: { roles: DEFAULT_FLOOR_ROLES.slice() } };
                }
                if (!r.structureAt) r.structureAt = Date.now();
                return pruneDeletedPeople(r);
            } catch (e) { return emptyState(); }
        }

        var state = loadLocal();
        var shared = null;
        var filter = 'active';
        var searchQ = '';
        var modal = document.getElementById('modal');
        var applyingRemote = false;

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
            if (showToast) toast(showToast === true ? undefined : showToast);
            if (!opts.skipRemote && shared) shared.queuePush(state);
            // Keep floor API role list in sync when permissions change
            if (opts.pushFloorApi) pushFloorRolePerms();
        }

        function applyRemote(payload) {
            if (!payload || !Array.isArray(payload.people)) return;
            applyingRemote = true;
            state = pruneDeletedPeople({
                people: payload.people.map(normalizePerson),
                deletedIds: (payload.deletedIds && typeof payload.deletedIds === 'object')
                    ? payload.deletedIds
                    : (state.deletedIds || {}),
                structureAt: payload.structureAt || Date.now(),
                permissions: payload.permissions || state.permissions
            });
            if (!state.permissions || !state.permissions.floorLayout) {
                state.permissions = { floorLayout: { roles: DEFAULT_FLOOR_ROLES.slice() } };
            }
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
            renderPerms();
            applyingRemote = false;
        }

        function render() {
            var active = state.people.filter(function (p) { return p.status !== 'inactive'; });
            var rolesUsed = {};
            active.forEach(function (p) {
                (p.roles || [p.role || 'Other']).forEach(function (r) { rolesUsed[r || 'Other'] = true; });
            });
            document.getElementById('stat-total').textContent = state.people.length;
            document.getElementById('stat-active').textContent = active.length;
            document.getElementById('stat-roles').textContent = Object.keys(rolesUsed).length;

            var list = state.people.slice().sort(function (a, b) {
                return (a.name || '').localeCompare(b.name || '');
            });
            if (filter === 'active') list = list.filter(function (p) { return p.status !== 'inactive'; });
            if (filter === 'inactive') list = list.filter(function (p) { return p.status === 'inactive'; });
            if (searchQ) {
                var q = searchQ.toLowerCase();
                list = list.filter(function (p) {
                    return [p.name, rolesLabel(p), p.phone, p.email, p.notes].join(' ').toLowerCase().indexOf(q) !== -1;
                });
            }

            var root = document.getElementById('list');
            if (!list.length) {
                root.innerHTML = '<div class="card empty">' + (isSweet ? 'No teammates match — add your crew above 💕' : 'No teammates match. Add staff above.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (p) {
                var roleList = (p.roles && p.roles.length) ? p.roles : [p.role || 'Other'];
                var wages = p.roleWages || {};
                var meta = [p.phone, p.email].filter(Boolean).join(' · ');
                var badges = roleList.map(function (r) {
                    var w = wages[r];
                    var wageBit = (w != null && w !== '')
                        ? ' <span class="badge wage">' + esc(moneyWage(w)) + '/hr</span>'
                        : '';
                    return '<span class="badge">' + esc(r) + '</span>' + wageBit;
                }).join(' ');
                return '<div class="person' + (p.status === 'inactive' ? ' inactive' : '') + '">' +
                    '<div class="person-top"><div><h3 class="person-name">' + esc(p.name) + '</h3>' + badges + '</div></div>' +
                    (meta ? '<div class="meta">' + esc(meta) + '</div>' : '') +
                    (p.notes ? '<div class="meta">' + esc(p.notes) + '</div>' : '') +
                    '<div class="actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-id="' + esc(p.id) + '">Edit</button>' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="toggle" data-id="' + esc(p.id) + '">' +
                    (p.status === 'inactive' ? (isSweet ? 'Reactivate' : 'Reactivate') : (isSweet ? 'Deactivate' : 'Deactivate')) + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-id="' + esc(p.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        // —— Sitewide permissions matrix editor ——
        var permMatrix = null;
        var permDefaults = null;
        var permCatalog = [];
        var permRoles = {};
        var permActiveRole = 'manager';
        var permCanManage = false;
        var permDirty = false;

        function loadPermMatrix() {
            return fetch('permissions-api.php?matrix=1&catalog=1', { credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !data.ok) throw new Error('fail');
                    permCanManage = !!data.canManage;
                    permRoles = data.roles || {};
                    permCatalog = data.catalog || [];
                    permMatrix = data.matrix || {};
                    permDefaults = data.defaults || {};
                    var gate = document.getElementById('perms-gate');
                    var locked = document.getElementById('perms-locked');
                    if (gate) gate.style.display = permCanManage ? '' : 'none';
                    if (locked) locked.style.display = permCanManage ? 'none' : '';
                    if (permCanManage) renderPerms();
                })
                .catch(function () {
                    document.getElementById('perm-status').textContent = isSweet
                        ? 'Could not load permissions — try again'
                        : 'Could not load permissions';
                });
        }

        function renderPerms() {
            if (!permCanManage || !permMatrix) {
                loadPermMatrix();
                return;
            }
            var tabs = document.getElementById('perm-role-tabs');
            var editor = document.getElementById('perm-editor');
            if (!tabs || !editor) return;
            var roleKeys = Object.keys(permRoles);
            if (roleKeys.indexOf(permActiveRole) === -1) permActiveRole = 'manager';
            tabs.innerHTML = roleKeys.map(function (rk) {
                return '<button type="button" class="chip' + (rk === permActiveRole ? ' active' : '') + '" data-perm-role="' + esc(rk) + '">' +
                    esc(permRoles[rk] || rk) + '</button>';
            }).join('');
            var lockedOwner = permActiveRole === 'owner';
            var row = permMatrix[permActiveRole] || {};
            editor.innerHTML = permCatalog.map(function (group) {
                var items = (group.items || []).map(function (it) {
                    var checked = lockedOwner ? true : !!row[it.key];
                    var dis = lockedOwner || (permActiveRole === 'gm' && it.key === 'admin.team.permissions_manage');
                    return '<label class="perm-row" style="display:flex;gap:10px;align-items:flex-start;padding:8px 4px;border-bottom:1px solid ' + (isSweet ? '#F3E8DD' : '#E6DFD7') + ';">' +
                        '<input type="checkbox" data-perm-key="' + esc(it.key) + '"' + (checked ? ' checked' : '') + (dis ? ' disabled' : '') + '>' +
                        '<span style="flex:1;"><span style="font-size:0.95rem;">' + esc(it.label) + '</span>' +
                        (dis ? '<div class="hint" style="margin:2px 0 0;font-size:0.8rem;">' + (isSweet ? 'Always on' : 'Always on') + '</div>' : '') +
                        '</span></label>';
                }).join('');
                return '<div class="card" style="margin-bottom:10px;box-shadow:none;border:1px solid ' + (isSweet ? '#F3E8DD' : '#E6DFD7') + ';">' +
                    '<h3 style="margin:0 0 8px;font-size:1.05rem;">' + esc(group.label) + '</h3>' + items + '</div>';
            }).join('');
        }

        function readEditorIntoMatrix() {
            if (!permMatrix[permActiveRole]) permMatrix[permActiveRole] = {};
            if (permActiveRole === 'owner') return;
            document.querySelectorAll('#perm-editor [data-perm-key]').forEach(function (cb) {
                var k = cb.getAttribute('data-perm-key');
                if (cb.disabled && permActiveRole === 'gm' && k === 'admin.team.permissions_manage') {
                    permMatrix[permActiveRole][k] = true;
                    return;
                }
                if (!cb.disabled) permMatrix[permActiveRole][k] = !!cb.checked;
            });
            permDirty = true;
        }

        document.getElementById('perm-role-tabs').addEventListener('click', function (e) {
            var chip = e.target.closest('[data-perm-role]');
            if (!chip) return;
            readEditorIntoMatrix();
            permActiveRole = chip.getAttribute('data-perm-role');
            renderPerms();
        });

        document.getElementById('perm-editor').addEventListener('change', function (e) {
            if (e.target && e.target.getAttribute('data-perm-key')) {
                readEditorIntoMatrix();
            }
        });

        document.getElementById('reset-role-defaults').addEventListener('click', function () {
            if (!permDefaults || !permDefaults[permActiveRole]) return;
            if (!confirm(isSweet ? 'Reset ' + (permRoles[permActiveRole] || permActiveRole) + ' to house defaults?' : 'Reset this role to defaults?')) return;
            permMatrix[permActiveRole] = Object.assign({}, permDefaults[permActiveRole]);
            if (permActiveRole === 'owner') {
                Object.keys(permMatrix[permActiveRole]).forEach(function (k) { permMatrix[permActiveRole][k] = true; });
            }
            permDirty = true;
            renderPerms();
            document.getElementById('perm-status').textContent = isSweet ? 'Defaults loaded — tap Save to apply ✨' : 'Defaults loaded — save to apply';
        });

        function pushFloorRolePerms() { /* legacy no-op; matrix API handles floor sync */ }

        // Tabs
        document.querySelectorAll('.tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t === tab); });
                document.querySelectorAll('.panel').forEach(function (p) { p.classList.remove('active'); });
                document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
                if (tab.dataset.tab === 'perms') renderPerms();
            });
        });

        document.getElementById('add-form').addEventListener('submit', function (e) {
            e.preventDefault();
            if (window.PbjPerms && window.PbjPerms.loaded && !window.PbjPerms.can('admin.team.roster.add')) { alert('No permission to add teammates'); return; }
            var pack = readRolesAndWages('#f-roles');
            if (!pack.roles.length) {
                alert(isSweet ? 'Pick at least one role for this teammate 💕' : 'Select at least one role.');
                return;
            }
            var newId = uid();
            if (state.deletedIds && state.deletedIds[newId] != null) delete state.deletedIds[newId];
            state.people.push(normalizePerson({
                id: newId,
                name: document.getElementById('f-name').value.trim(),
                roles: pack.roles,
                role: pack.roles[0],
                roleWages: pack.roleWages,
                phone: document.getElementById('f-phone').value.trim(),
                email: document.getElementById('f-email').value.trim(),
                notes: document.getElementById('f-notes').value.trim(),
                status: 'active',
                updatedAt: Date.now()
            }));
            e.target.reset();
            setRolesAndWages('#f-roles', ['Server'], {});
            save(true);
            render();
            renderPerms();
        });

        document.getElementById('filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.chip'); if (!chip) return;
            filter = chip.dataset.filter;
            document.querySelectorAll('#filters .chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
            render();
        });
        document.getElementById('search').addEventListener('input', function () {
            searchQ = this.value.trim();
            render();
        });

        document.getElementById('list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]'); if (!btn) return;
            e.preventDefault();
            var id = String(btn.getAttribute('data-id') || btn.dataset.id || '');
            var p = state.people.find(function (x) { return String(x.id) === id; });
            if (!p) {
                // Fallback: still allow remove by id even if row is stale
                if (btn.dataset.act === 'del' && id) {
                    if (!confirm(isSweet ? 'Remove this person from the roster?' : 'Remove this person?')) return;
                    if (!state.deletedIds) state.deletedIds = {};
                    state.deletedIds[id] = Date.now();
                    state.people = state.people.filter(function (x) { return String(x.id) !== id; });
                    save(true); render(); renderPerms();
                }
                return;
            }
            if (btn.dataset.act === 'del') {
                if (!confirm(isSweet ? 'Remove this person from the roster?' : 'Remove this person?')) return;
                if (!state.deletedIds) state.deletedIds = {};
                state.deletedIds[id] = Date.now();
                state.people = state.people.filter(function (x) { return String(x.id) !== id; });
                save(true); render(); renderPerms(); return;
            }
            if (btn.dataset.act === 'toggle') {
                p.status = p.status === 'inactive' ? 'active' : 'inactive';
                p.updatedAt = Date.now();
                save(true); render(); renderPerms(); return;
            }
            if (btn.dataset.act === 'edit') {
                document.getElementById('e-id').value = p.id;
                document.getElementById('e-name').value = p.name || '';
                setRolesAndWages('#e-roles', p.roles && p.roles.length ? p.roles : [p.role || 'Other'], p.roleWages || {});
                document.getElementById('e-phone').value = p.phone || '';
                document.getElementById('e-email').value = p.email || '';
                document.getElementById('e-notes').value = p.notes || '';
                document.getElementById('e-status').value = p.status === 'inactive' ? 'inactive' : 'active';
                modal.classList.add('show');
            }
        });

        document.getElementById('edit-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = String(document.getElementById('e-id').value || '');
            var p = state.people.find(function (x) { return String(x.id) === id; }); if (!p) return;
            var pack = readRolesAndWages('#e-roles');
            if (!pack.roles.length) {
                alert(isSweet ? 'Pick at least one role for this teammate 💕' : 'Select at least one role.');
                return;
            }
            p.name = document.getElementById('e-name').value.trim();
            p.roles = pack.roles;
            p.role = pack.roles[0];
            p.roleWages = pack.roleWages;
            p.phone = document.getElementById('e-phone').value.trim();
            p.email = document.getElementById('e-email').value.trim();
            p.notes = document.getElementById('e-notes').value.trim();
            p.status = document.getElementById('e-status').value;
            p.updatedAt = Date.now();
            save(true);
            modal.classList.remove('show');
            render();
            renderPerms();
        });
        document.getElementById('e-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });

        document.getElementById('save-role-perms').addEventListener('click', function () {
            if (!permCanManage || !permMatrix) {
                document.getElementById('perm-status').textContent = isSweet ? 'You cannot edit permissions' : 'No permission';
                return;
            }
            readEditorIntoMatrix();
            var st = document.getElementById('perm-status');
            st.textContent = isSweet ? 'Saving…' : 'Saving…';
            fetch('permissions-api.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ matrix: permMatrix })
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data && data.ok && data.matrix) {
                    permMatrix = data.matrix;
                    permDirty = false;
                    st.textContent = isSweet ? 'Permissions saved for the whole house ✨' : 'Permissions saved';
                    if (window.PbjPerms && window.PbjPerms.load) window.PbjPerms.load();
                    renderPerms();
                } else {
                    st.textContent = isSweet ? 'Save failed — try again' : 'Save failed';
                }
            }).catch(function () {
                st.textContent = isSweet ? 'Save failed (offline?)' : 'Save failed';
            });
        });

        document.getElementById('print-btn').addEventListener('click', function () {
            var rows = state.people.filter(function (p) { return p.status !== 'inactive'; })
                .sort(function (a, b) { return (a.name || '').localeCompare(b.name || ''); });
            var w = window.open('', '_blank');
            if (!w) return;
            var html = '<html><head><title>Team Roster</title><style>body{font-family:serif;padding:20px;} h1{font-size:18pt;} table{width:100%;border-collapse:collapse;} td,th{border-bottom:1px solid #ccc;padding:6px;text-align:left;font-size:11pt;}</style></head><body>';
            html += '<h1>' + (isSweet ? 'Team Roster' : 'Team Roster') + '</h1>';
            html += '<table><tr><th>Name</th><th>Roles & $/hr</th><th>Phone</th><th>Email</th><th>Notes</th></tr>';
            rows.forEach(function (p) {
                html += '<tr><td>' + esc(p.name) + '</td><td>' + esc(rolesLabel(p)) + '</td><td>' + esc(p.phone) + '</td><td>' + esc(p.email) + '</td><td>' + esc(p.notes) + '</td></tr>';
            });
            html += '</table></body></html>';
            w.document.write(html);
            w.document.close();
            w.focus();
            w.print();
        });

        document.getElementById('export-btn').addEventListener('click', function () {
            var rows = [['Name', 'Roles & $/hr', 'Phone', 'Email', 'Status', 'Notes']];
            state.people.slice().sort(function (a, b) { return (a.name || '').localeCompare(b.name || ''); }).forEach(function (p) {
                rows.push([p.name, rolesLabel(p), p.phone, p.email, p.status, p.notes]);
            });
            var csv = rows.map(function (r) {
                return r.map(function (c) {
                    var s = String(c == null ? '' : c);
                    if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
                    return s;
                }).join(',');
            }).join('\n');
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'team-roster.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        });

        wireRoleWageToggles('#f-roles');
        wireRoleWageToggles('#e-roles');
        // Default: Server checked with wage enabled
        setRolesAndWages('#f-roles', ['Server'], {});

        render();
        loadPermMatrix();

        if (window.PbjSharedState) {
            shared = new PbjSharedState({
                key: SHARED_KEY,
                pollMs: 4000,
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
    })();
    </script>
</body>
</html>
