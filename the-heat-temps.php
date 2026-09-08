<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

// Equipment shells with min/max for range checks (°F)
$default_equipment = [
    ['id' => 'walkin',  'name' => 'Walk-in Cooler', 'target' => '33–40°F', 'min' => 33, 'max' => 40, 'type' => 'cold'],
    ['id' => 'reachin', 'name' => 'Reach-in Cooler', 'target' => '33–40°F', 'min' => 33, 'max' => 40, 'type' => 'cold'],
    ['id' => 'prep',    'name' => 'Prep Table / Cold Well', 'target' => '33–41°F', 'min' => 33, 'max' => 41, 'type' => 'cold'],
    ['id' => 'freezer', 'name' => 'Freezer', 'target' => '0°F or below', 'min' => null, 'max' => 0, 'type' => 'cold'],
    ['id' => 'hotline', 'name' => 'Hot Hold / Steam Table', 'target' => '135°F+', 'min' => 135, 'max' => null, 'type' => 'hot'],
    ['id' => 'dish',    'name' => 'Dish Machine Final Rinse', 'target' => 'Per machine / chemical', 'min' => null, 'max' => null, 'type' => 'other'],
];

$default_refs = $is_sweet
    ? [
        ['id' => 'poultry', 'title' => 'Poultry (chicken, turkey)', 'body' => "Cook to 165°F internal.\nHold hot at 135°F+.\nCool: 135→70°F within 2 hrs, then 70→41°F within 4 hrs."],
        ['id' => 'ground',  'title' => 'Ground meats', 'body' => "Cook to 155°F internal (or per local code).\nHold hot at 135°F+."],
        ['id' => 'steak',   'title' => 'Whole muscle steaks / chops', 'body' => "Cook to 145°F internal + rest (or per house / code).\nHold hot at 135°F+."],
        ['id' => 'fish',    'title' => 'Fish & seafood', 'body' => "Cook to 145°F internal (or until opaque / flakes).\nHold hot at 135°F+."],
        ['id' => 'cold',    'title' => 'Cold hold', 'body' => "Keep TCS foods at 41°F or below.\nLabel, date, and FIFO."],
        ['id' => 'danger',  'title' => 'Danger zone', 'body' => "41–135°F is the danger zone.\nMinimize time in this range. When in doubt, throw it out."],
    ]
    : [
        ['id' => 'poultry', 'title' => 'Poultry (chicken, turkey)', 'body' => "Cook to 165°F internal.\nHold hot at 135°F+.\nCool: 135→70°F within 2 hrs, then 70→41°F within 4 hrs."],
        ['id' => 'ground',  'title' => 'Ground meats', 'body' => "Cook to 155°F internal (or per local code).\nHold hot at 135°F+."],
        ['id' => 'steak',   'title' => 'Whole muscle steaks / chops', 'body' => "Cook to 145°F internal + rest (or per house / code).\nHold hot at 135°F+."],
        ['id' => 'fish',    'title' => 'Fish & seafood', 'body' => "Cook to 145°F internal (or until opaque / flakes).\nHold hot at 135°F+."],
        ['id' => 'cold',    'title' => 'Cold hold', 'body' => "Keep TCS foods at 41°F or below.\nLabel, date, and FIFO."],
        ['id' => 'danger',  'title' => 'Danger zone', 'body' => "41–135°F is the danger zone.\nMinimize time in this range. When in doubt, throw it out."],
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'Temps & Food Safety' : 'Temps & Food Safety'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
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
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .link-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .chip { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC; font-family: 'DreamingOutLoudPro', serif;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE; font-family: 'Lora', serif;<?php endif; ?> }
        .tabs { display: flex; gap: 10px; margin-bottom: 16px; }
        .tab { flex: 1; border: none; border-radius: 14px; padding: 14px 10px; font-size: 1rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .panel { display: none; } .panel.active { display: block; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.45rem; margin: 0 0 12px; }
        .equip-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; padding: 14px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .equip-row:last-child { border-bottom: none; }
        .equip-row.out { background: #FDECEA; margin: 0 -12px; padding: 14px 12px; border-radius: 12px; border-bottom: none; margin-bottom: 8px; }
        .equip-row.ok { background: #E8F8F1; margin: 0 -12px; padding: 14px 12px; border-radius: 12px; border-bottom: none; margin-bottom: 8px; }
        .equip-info { flex: 1; min-width: 140px; }
        .equip-name { font-size: 1.1rem; margin-bottom: 2px; }
        .equip-target { font-size: 0.9rem; opacity: 0.7; }
        .status-pill { display: inline-block; margin-top: 6px; font-size: 0.8rem; padding: 2px 8px; border-radius: 999px; }
        .status-pill.ok { background: #C8E6C9; color: #1B5E20; }
        .status-pill.out { background: #FFCDD2; color: #B71C1C; }
        .status-pill.na { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; opacity: 0.85; }
        .equip-fields { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
        .field { min-width: 90px; }
        .field label { display: block; font-size: 0.8rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .field.temp { width: 100px; min-width: 100px; }
        .field.half { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .log-item { border-left: 4px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; padding: 12px 14px; margin-bottom: 10px; border-radius: 0 12px 12px 0; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .log-item.has-out { border-left-color: #C62828; background: #FFF5F5; }
        .log-item .meta { font-size: 0.9rem; opacity: 0.7; margin-bottom: 4px; }
        .log-item .main { font-size: 1.02rem; line-height: 1.4; }
        .log-item .out-flag { color: #C62828; font-size: 0.9rem; margin-top: 4px; }
        .empty { text-align: center; padding: 28px 16px; opacity: 0.8; line-height: 1.45; }
        .ref-card { margin-bottom: 12px; }
        .ref-card h3 { margin: 0 0 6px; font-size: 1.15rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .ref-card p { margin: 0; opacity: 0.85; line-height: 1.4; }
        .ref-empty { opacity: 0.75; font-style: italic; }
        .actions-bar { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; padding: 22px 20px; max-height: 90vh; overflow-y: auto; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .round-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .print-only { display: none; }
        @media print {
            .no-print, .back-link, .toolbar, .tabs, .actions-bar, .bottom-nav, #bottom-nav, nav, .toast, .btn, .modal-backdrop, .link-row { display: none !important; }
            .print-only { display: block !important; }
            body { background: white; padding-bottom: 0; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card, .log-item, .equip-row { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
            .panel { display: block !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH" class="back-link">← <?php echo pbj_back_to_hub('boh'); ?></a>
        <h1><?php echo $is_sweet ? 'Temps & Food Safety' : 'Temps & Food Safety'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Cooler logs, cook temps & hold notes' : 'Cooler logs, cook temps, and hold notes'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Log cooler & hold temps by shift, spot out-of-range readings instantly, and keep house cook-temp notes handy 🌡️'
                : 'Log cooler and hold temps by shift, flag out-of-range readings, and keep cook-temp references handy.'; ?>
        </div>
        <div class="link-row no-print">
            <a class="chip" href="/BOH/opening-closing"><?php echo $is_sweet ? '🌅 Open / Close' : '🌅 Open / Close'; ?></a>
            <a class="chip" href="/BOH/cleaning"><?php echo $is_sweet ? '✨ Cleaning' : '✨ Cleaning'; ?></a>
            <a class="chip" href="/BOH/tools"><?php echo $is_sweet ? '🛠️ °F ↔ °C' : '🛠️ °F ↔ °C'; ?></a>
        </div>

        <div class="toolbar no-print" style="margin-bottom:12px;">
            <button type="button" class="btn btn-secondary" id="print-temps-btn"><?php echo $is_sweet ? '🖨️ Print log / history' : 'Print log / history'; ?></button>
            <button type="button" class="btn btn-secondary" id="export-temps-btn"><?php echo $is_sweet ? 'Export history CSV' : 'Export history CSV'; ?></button>
        </div>
        <div class="print-only" id="print-temps-header" style="margin-bottom:8px;font-weight:600;"></div>

        <div class="tabs no-print">
            <button type="button" class="tab active" data-tab="log"><?php echo $is_sweet ? '📝 Log' : 'Log'; ?></button>
            <button type="button" class="tab" data-tab="history"><?php echo $is_sweet ? '📒 History' : 'History'; ?></button>
            <button type="button" class="tab" data-tab="ref"><?php echo $is_sweet ? '📚 Reference' : 'Reference'; ?></button>
        </div>

        <div class="panel active" id="panel-log">
            <div class="toolbar no-print">
                <button type="button" class="btn btn-primary" id="add-equip-btn" data-perm="boh.temps.add_equipment"><?php echo $is_sweet ? '+ Equipment' : '+ Equipment'; ?></button>
                <button type="button" class="btn btn-secondary" id="save-round-btn" data-perm="boh.temps.save_round"><?php echo $is_sweet ? 'Save this round' : 'Save this round'; ?></button>
            </div>
            <div class="card">
                <h2><?php echo $is_sweet ? 'Temp round' : 'Temp round'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'Pick the shift, enter readings, then save. Green = in range, red = check it.' : 'Pick the shift, enter readings, then save. Green = in range, red = check.'; ?></p>
                <div class="round-meta">
                    <div class="field half">
                        <label for="round-shift"><?php echo $is_sweet ? 'Shift' : 'Shift'; ?></label>
                        <select id="round-shift">
                            <option value="open"><?php echo $is_sweet ? 'Opening' : 'Opening'; ?></option>
                            <option value="mid"><?php echo $is_sweet ? 'Mid-shift' : 'Mid-shift'; ?></option>
                            <option value="close"><?php echo $is_sweet ? 'Closing' : 'Closing'; ?></option>
                            <option value="other"><?php echo $is_sweet ? 'Other / spot check' : 'Other / spot check'; ?></option>
                        </select>
                    </div>
                    <div class="field half">
                        <label for="round-initials"><?php echo $is_sweet ? 'Your initials' : 'Your initials'; ?></label>
                        <input type="text" id="round-initials" maxlength="6" placeholder="AB">
                    </div>
                </div>
                <div id="equip-list"></div>
            </div>
        </div>

        <div class="panel" id="panel-history">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Recent logs' : 'Recent logs'; ?></h2>
                <div id="history-list"></div>
            </div>
            <button type="button" class="btn btn-secondary" id="clear-history-btn" style="width:100%;margin-bottom:14px;"><?php echo $is_sweet ? 'Clear history' : 'Clear history'; ?></button>
        </div>

        <div class="panel" id="panel-ref">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Cook temps & hold times' : 'Cook temps & hold times'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'Starter food-safety notes — edit to match your local code & house standards.' : 'Starter food-safety notes. Edit to match local code and house standards.'; ?></p>
                <div id="ref-list"></div>
                <button type="button" class="btn btn-primary" id="add-ref-btn" style="width:100%;margin-top:8px;"><?php echo $is_sweet ? '+ Reference note' : '+ Reference note'; ?></button>
            </div>
        </div>

        <div class="actions-bar">
            <button type="button" class="btn btn-secondary" id="reset-equip-btn"><?php echo $is_sweet ? 'Reset equipment shells' : 'Reset equipment shells'; ?></button>
            <a href="/BOH" class="btn btn-primary"><?php echo pbj_back_to_hub('boh'); ?></a>
        </div>
    </div>

    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2 id="modal-title">Add</h2>
            <form id="form">
                <input type="hidden" id="m-mode"><input type="hidden" id="m-id">
                <div class="field" id="f-name"><label for="m-name"><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="m-name" required></div>
                <div class="field" id="f-target"><label for="m-target"><?php echo $is_sweet ? 'Target label (optional)' : 'Target label (optional)'; ?></label><input id="m-target" placeholder="e.g. 33–40°F"></div>
                <div class="field" id="f-min"><label for="m-min"><?php echo $is_sweet ? 'Min °F (optional)' : 'Min °F (optional)'; ?></label><input id="m-min" type="number" step="any" placeholder="e.g. 33"></div>
                <div class="field" id="f-max"><label for="m-max"><?php echo $is_sweet ? 'Max °F (optional)' : 'Max °F (optional)'; ?></label><input id="m-max" type="number" step="any" placeholder="e.g. 40"></div>
                <div class="field" id="f-body" style="display:none"><label for="m-body"><?php echo $is_sweet ? 'Details' : 'Details'; ?></label><textarea id="m-body" rows="4"></textarea></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="m-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const KEY = 'pbj_heat_temps_v2';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyTempsPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var allowTemp = canP('boh.temps.enter_temps');
                document.querySelectorAll('input[type="number"].temp-input, input[data-temp], .temp-field input, #equip-list input[type=number]').forEach(function(inp){
                    if (!inp.closest('.modal')) inp.disabled = !allowTemp;
                });
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof renderEquip === 'function') renderEquip(); } catch (e) {}
                try { if (typeof renderList === 'function') renderList(); } catch (e) {}
            }

        const defaults = <?php echo json_encode($default_equipment, JSON_UNESCAPED_UNICODE); ?>;
        const defaultRefs = <?php echo json_encode($default_refs, JSON_UNESCAPED_UNICODE); ?>;

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function shells() {
            return {
                equipment: defaults.map(function (e) {
                    return { id: e.id, name: e.name, target: e.target, min: e.min, max: e.max, type: e.type };
                }),
                logs: [],
                refs: defaultRefs.map(function (r) {
                    return { id: r.id, title: r.title, body: r.body };
                })
            };
        }
        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || !r.equipment) return shells();
                if (!Array.isArray(r.logs)) r.logs = [];
                if (!Array.isArray(r.refs)) r.refs = defaultRefs.map(function (x) { return { id: x.id, title: x.title, body: x.body }; });
                r.equipment.forEach(function (e) {
                    if (e.min === undefined) e.min = null;
                    if (e.max === undefined) e.max = null;
                });
                return r;
            } catch (e) { return shells(); }
        }
        function save(t) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (t) {
                var el = document.getElementById('toast');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
        }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function parseNum(v) {
            if (v === null || v === undefined || v === '') return null;
            var n = parseFloat(String(v).replace(/[^\d.\-]/g, ''));
            return isNaN(n) ? null : n;
        }
        function rangeStatus(equip, tempStr) {
            var n = parseNum(tempStr);
            if (n === null) return 'na';
            var min = equip.min != null && equip.min !== '' ? Number(equip.min) : null;
            var max = equip.max != null && equip.max !== '' ? Number(equip.max) : null;
            if (min === null && max === null) return 'na';
            if (min !== null && n < min) return 'out';
            if (max !== null && n > max) return 'out';
            return 'ok';
        }
        function shiftLabel(s) {
            var map = { open: isSweet ? 'Opening' : 'Opening', mid: isSweet ? 'Mid-shift' : 'Mid-shift', close: isSweet ? 'Closing' : 'Closing', other: isSweet ? 'Spot check' : 'Spot check' };
            return map[s] || s || '';
        }

        var state = load();
        var modal = document.getElementById('modal');
        var draftTemps = {};

        document.querySelectorAll('.tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var key = tab.dataset.tab;
                document.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t === tab); });
                document.getElementById('panel-log').classList.toggle('active', key === 'log');
                document.getElementById('panel-history').classList.toggle('active', key === 'history');
                document.getElementById('panel-ref').classList.toggle('active', key === 'ref');
                if (key === 'history') renderHistory();
                if (key === 'ref') renderRefs();
            });
        });

        function statusPill(st) {
            if (st === 'ok') return '<span class="status-pill ok">' + (isSweet ? 'In range ✓' : 'In range') + '</span>';
            if (st === 'out') return '<span class="status-pill out">' + (isSweet ? 'Out of range!' : 'Out of range') + '</span>';
            return '<span class="status-pill na">' + (isSweet ? 'No range set' : 'No range set') + '</span>';
        }

        function renderEquip() {
            var root = document.getElementById('equip-list');
            if (!state.equipment.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No equipment yet — add your coolers & hot holds ✨' : 'No equipment yet. Add coolers and hot holds.') + '</div>';
                return;
            }
            root.innerHTML = state.equipment.map(function (e) {
                var val = draftTemps[e.id] != null ? draftTemps[e.id] : '';
                var st = val !== '' ? rangeStatus(e, val) : 'na';
                var rowClass = st === 'out' ? ' out' : (st === 'ok' ? ' ok' : '');
                return '<div class="equip-row' + rowClass + '" data-id="' + esc(e.id) + '">' +
                    '<div class="equip-info">' +
                        '<div class="equip-name">' + esc(e.name) + '</div>' +
                        '<div class="equip-target">' + (e.target ? esc(e.target) : (isSweet ? 'No target set' : 'No target set')) + '</div>' +
                        (val !== '' ? statusPill(st) : '') +
                        '<div style="margin-top:8px;display:flex;gap:6px;">' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="edit-equip" data-need-perm="boh.temps.edit_equipment" data-id="' + esc(e.id) + '">Edit</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="del-equip" data-need-perm="boh.temps.edit_equipment" data-id="' + esc(e.id) + '">Remove</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="equip-fields">' +
                        '<div class="field temp"><label>' + (isSweet ? 'Temp °F' : 'Temp °F') + '</label>' +
                        '<input type="text" inputmode="decimal" data-temp="' + esc(e.id) + '" placeholder="°" value="' + esc(val) + '"></div>' +
                    '</div></div>';
            }).join('');
        }

        function renderHistory() {
            var root = document.getElementById('history-list');
            if (!state.logs.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No rounds logged yet' : 'No rounds logged yet') + '</div>';
                return;
            }
            root.innerHTML = state.logs.slice().reverse().map(function (log) {
                var outs = 0;
                var lines = (log.readings || []).map(function (r) {
                    var mark = r.status === 'out' ? ' ⚠️' : (r.status === 'ok' ? ' ✓' : '');
                    if (r.status === 'out') outs++;
                    return esc(r.name) + ': ' + esc(r.temp) + (r.temp !== '' && r.temp != null ? '°' : '') + mark;
                }).join(' · ');
                var when = new Date(log.at).toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
                return '<div class="log-item' + (outs ? ' has-out' : '') + '">' +
                    '<div class="meta">' + esc(when) +
                    (log.shift ? ' · ' + esc(shiftLabel(log.shift)) : '') +
                    (log.initials ? ' · ' + esc(log.initials) : '') + '</div>' +
                    '<div class="main">' + lines + '</div>' +
                    (outs ? '<div class="out-flag">' + outs + (isSweet ? ' reading(s) out of range' : ' reading(s) out of range') + '</div>' : '') +
                    '</div>';
            }).join('');
        }

        function renderRefs() {
            var root = document.getElementById('ref-list');
            if (!state.refs.length) {
                root.innerHTML = '<p class="ref-empty">' + (isSweet ? 'No reference notes yet — add cook temps, cooling steps, or hold rules.' : 'No reference notes yet.') + '</p>';
                return;
            }
            root.innerHTML = state.refs.map(function (r) {
                return '<div class="ref-card card" style="box-shadow:none;border:1px solid ' + (isSweet ? '#F3E8DD' : '#E6DFD7') + ';">' +
                    '<h3>' + esc(r.title) + '</h3>' +
                    (r.body ? '<p style="white-space:pre-wrap;">' + esc(r.body) + '</p>' : '') +
                    '<div style="margin-top:10px;display:flex;gap:8px;">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit-ref" data-id="' + esc(r.id) + '">Edit</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del-ref" data-need-perm="boh.temps.edit_equipment" data-id="' + esc(r.id) + '">Remove</button>' +
                    '</div></div>';
            }).join('');
        }

        document.getElementById('equip-list').addEventListener('input', function (e) {
            if (e.target.dataset.temp) {
                draftTemps[e.target.dataset.temp] = e.target.value;
                renderEquip();
                // restore focus
                var input = document.querySelector('[data-temp="' + e.target.dataset.temp + '"]');
                if (input) {
                    input.focus();
                    var len = input.value.length;
                    try { input.setSelectionRange(len, len); } catch (err) {}
                }
            }
        });

        document.getElementById('equip-list').addEventListener('click', function (e) {
                var b = e.target.closest('[data-act]'); if (b && b.getAttribute('data-need-perm') && !canP(b.getAttribute('data-need-perm'))) return;

            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            if (btn.dataset.act === 'edit-equip') {
                var eq = state.equipment.find(function (x) { return x.id === id; });
                openModal('edit-equip', eq);
            }
            if (btn.dataset.act === 'del-equip') {
                if (!confirm(isSweet ? 'Remove this equipment?' : 'Remove this equipment?')) return;
                state.equipment = state.equipment.filter(function (x) { return x.id !== id; });
                delete draftTemps[id];
                save(true); renderEquip();
            }
        });

        document.getElementById('ref-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            if (btn.dataset.act === 'edit-ref') {
                var r = state.refs.find(function (x) { return x.id === id; });
                openModal('edit-ref', r);
            }
            if (btn.dataset.act === 'del-ref') {
                if (!confirm(isSweet ? 'Remove this note?' : 'Remove this note?')) return;
                state.refs = state.refs.filter(function (x) { return x.id !== id; });
                save(true); renderRefs();
            }
        });

        function setEquipFields(show) {
            document.getElementById('f-target').style.display = show ? 'block' : 'none';
            document.getElementById('f-min').style.display = show ? 'block' : 'none';
            document.getElementById('f-max').style.display = show ? 'block' : 'none';
            document.getElementById('f-body').style.display = show ? 'none' : 'block';
        }

        function openModal(mode, item) {
            document.getElementById('m-mode').value = mode;
            document.getElementById('m-id').value = item ? item.id : '';
            var isRef = mode === 'ref' || mode === 'edit-ref';
            setEquipFields(!isRef);
            if (mode === 'equip') {
                document.getElementById('modal-title').textContent = isSweet ? 'Add equipment' : 'Add equipment';
                document.getElementById('m-name').value = '';
                document.getElementById('m-target').value = '';
                document.getElementById('m-min').value = '';
                document.getElementById('m-max').value = '';
            } else if (mode === 'edit-equip') {
                document.getElementById('modal-title').textContent = isSweet ? 'Edit equipment' : 'Edit equipment';
                document.getElementById('m-name').value = item ? item.name : '';
                document.getElementById('m-target').value = item ? (item.target || '') : '';
                document.getElementById('m-min').value = item && item.min != null ? item.min : '';
                document.getElementById('m-max').value = item && item.max != null ? item.max : '';
            } else if (mode === 'ref') {
                document.getElementById('modal-title').textContent = isSweet ? 'Add reference' : 'Add reference';
                document.getElementById('m-name').value = '';
                document.getElementById('m-body').value = '';
            } else {
                document.getElementById('modal-title').textContent = isSweet ? 'Edit reference' : 'Edit reference';
                document.getElementById('m-name').value = item ? item.title : '';
                document.getElementById('m-body').value = item ? (item.body || '') : '';
            }
            modal.classList.add('show');
            setTimeout(function () { document.getElementById('m-name').focus(); }, 40);
        }

        document.getElementById('form').addEventListener('submit', function (e) {
            e.preventDefault();
            var mode = document.getElementById('m-mode').value;
            var id = document.getElementById('m-id').value;
            var name = document.getElementById('m-name').value.trim();
            if (!name) return;
            if (mode === 'equip') {
                state.equipment.push({
                    id: uid(),
                    name: name,
                    target: document.getElementById('m-target').value.trim(),
                    min: parseNum(document.getElementById('m-min').value),
                    max: parseNum(document.getElementById('m-max').value),
                    type: 'other'
                });
            } else if (mode === 'edit-equip') {
                var eq = state.equipment.find(function (x) { return x.id === id; });
                if (eq) {
                    eq.name = name;
                    eq.target = document.getElementById('m-target').value.trim();
                    eq.min = parseNum(document.getElementById('m-min').value);
                    eq.max = parseNum(document.getElementById('m-max').value);
                }
            } else if (mode === 'ref') {
                state.refs.push({ id: uid(), title: name, body: document.getElementById('m-body').value.trim() });
            } else if (mode === 'edit-ref') {
                var r = state.refs.find(function (x) { return x.id === id; });
                if (r) { r.title = name; r.body = document.getElementById('m-body').value.trim(); }
            }
            save(true); modal.classList.remove('show');
            renderEquip(); renderRefs();
        });
        document.getElementById('m-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });

        document.getElementById('add-equip-btn').addEventListener('click', function () {
                if (!canP('boh.temps.add_equipment')) return; openModal('equip'); });
        document.getElementById('add-ref-btn').addEventListener('click', function () { openModal('ref'); });

        document.getElementById('save-round-btn').addEventListener('click', function () {
                if (!canP('boh.temps.save_round')) return;
            var initials = document.getElementById('round-initials').value.trim();
            var shift = document.getElementById('round-shift').value;
            var readings = state.equipment.map(function (e) {
                var temp = (draftTemps[e.id] != null ? String(draftTemps[e.id]).trim() : '');
                return { id: e.id, name: e.name, temp: temp, status: temp !== '' ? rangeStatus(e, temp) : 'na' };
            }).filter(function (r) { return r.temp !== ''; });
            if (!readings.length) {
                alert(isSweet ? 'Enter at least one temp first 💕' : 'Enter at least one temp first.');
                return;
            }
            var outs = readings.filter(function (r) { return r.status === 'out'; }).length;
            state.logs.push({ id: uid(), at: Date.now(), initials: initials, shift: shift, readings: readings });
            if (state.logs.length > 100) state.logs = state.logs.slice(-100);
            draftTemps = {};
            save(true);
            renderEquip();
            var msg = isSweet ? 'Round saved! 🌡️' : 'Round saved.';
            if (outs) msg += (isSweet ? ' (' + outs + ' out of range — double-check!)' : ' (' + outs + ' out of range)');
            alert(msg);
        });

        document.getElementById('clear-history-btn').addEventListener('click', function () {
            if (!confirm(isSweet ? 'Clear all saved temp rounds on this device?' : 'Clear all saved temp rounds?')) return;
            state.logs = []; save(true); renderHistory();
        });

        document.getElementById('reset-equip-btn').addEventListener('click', function () {
            if (!confirm(isSweet ? 'Reset equipment to starters? Logs & reference notes stay.' : 'Reset equipment to starters?')) return;
            var logs = state.logs; var refs = state.refs;
            state = shells();
            state.logs = logs; state.refs = refs;
            draftTemps = {};
            save(true); renderEquip();
        });

        document.getElementById('print-temps-btn').addEventListener('click', function () {
            document.getElementById('print-temps-header').textContent =
                (isSweet ? 'Temps & Food Safety · ' : 'Temps · ') + new Date().toLocaleString();
            // show history for print too
            document.getElementById('panel-history').classList.add('active');
            renderHistory();
            window.print();
        });
        document.getElementById('export-temps-btn').addEventListener('click', function () {
            if (!state.logs.length) {
                alert(isSweet ? 'No history to export yet' : 'No history to export yet');
                return;
            }
            var rows = [['When', 'Shift', 'Initials', 'Equipment', 'Temp', 'Status']];
            state.logs.forEach(function (log) {
                var when = new Date(log.at).toISOString();
                (log.readings || []).forEach(function (r) {
                    rows.push([when, log.shift || '', log.initials || '', r.name || '', r.temp || '', r.status || '']);
                });
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
            a.download = 'temp-logs.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        });

        renderEquip();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyTempsPerms);
            document.addEventListener('pbj-perms-ready', applyTempsPerms);
    })();
    </script>
</body>
</html>
