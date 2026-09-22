<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_label = trim((string) ($_SESSION['name'] ?? $_SESSION['username'] ?? 'Team'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'Allergen Menu' : 'Allergen Menu'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
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
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.4rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 20px 14px 40px; max-width: 960px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 14px 16px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; text-align: center; font-size: 0.98rem; }
        .link-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .chip { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-size: 0.92rem; border: none; cursor: pointer; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>font-family: 'Lora', serif; background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white; border-color: #E55163;<?php else: ?>background: #1A2A44; color: white; border-color: #1A2A44;<?php endif; ?> }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { <?php if ($is_sweet): ?>background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A;<?php else: ?>background: #FFF8E8; color: #5C4B1A; border-color: #E0D2A0;<?php endif; ?> }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }
        .card { background: white; border-radius: 18px; padding: 16px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 12px; }
        .hint { font-size: 0.92rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .counts { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .count-pill { padding: 6px 12px; border-radius: 999px; font-size: 0.9rem; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .count-pill strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .empty { text-align: center; padding: 28px 16px; opacity: 0.85; line-height: 1.5; }
        .empty a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .denied { display: none; }
        .matrix-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -4px; }
        table.matrix { width: 100%; border-collapse: collapse; min-width: 640px; font-size: 0.88rem; }
        table.matrix th, table.matrix td { padding: 8px 6px; text-align: center; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; vertical-align: middle; }
        table.matrix th { font-size: 0.72rem; letter-spacing: 0.02em; text-transform: uppercase; opacity: 0.8; position: sticky; top: 0; background: white; z-index: 1; }
        table.matrix th.item-col, table.matrix td.item-col { text-align: left; min-width: 140px; max-width: 200px; position: sticky; left: 0; background: white; z-index: 2; }
        table.matrix th.item-col { z-index: 3; }
        table.matrix .item-name { font-weight: 600; font-size: 0.98rem; line-height: 1.25; }
        table.matrix .item-meta { font-size: 0.78rem; opacity: 0.6; margin-top: 2px; }
        table.matrix .item-note { font-size: 0.8rem; opacity: 0.85; margin-top: 4px; line-height: 1.3; font-style: italic; }
        .mark { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 8px; font-weight: 700; font-size: 0.95rem; }
        .mark-yes { <?php if ($is_sweet): ?>background: #FFE0E4; color: #C62828;<?php else: ?>background: #FFCDD2; color: #B71C1C;<?php endif; ?> }
        .mark-no { opacity: 0.2; }
        .src-badge { display: inline-block; font-size: 0.68rem; padding: 1px 6px; border-radius: 999px; margin-left: 4px; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .row-actions { white-space: nowrap; }
        .legend { display: flex; flex-wrap: wrap; gap: 10px 16px; font-size: 0.85rem; opacity: 0.8; margin-bottom: 10px; }
        .legend span { display: inline-flex; align-items: center; gap: 6px; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 6px; }
        .modal .edit-sub { font-size: 0.9rem; opacity: 0.7; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
        .modal-actions .btn { flex: 1; min-width: 100px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?>
        }
        .field input:focus, .field textarea:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .allergen-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        @media (max-width: 400px) { .allergen-checks { grid-template-columns: 1fr; } }
        .allergen-check {
            display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-radius: 12px; cursor: pointer;
            <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?>
        }
        .allergen-check:has(input:checked) {
            <?php if ($is_sweet): ?>background: #FFF5F6; border-color: #E55163;<?php else: ?>background: #EEF2F8; border-color: #1A2A44;<?php endif; ?>
        }
        .allergen-check input { width: 18px; height: 18px; accent-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .print-only { display: none; }
        @media print {
            .no-print, .back-link, .link-row, .sync-pill, .bottom-nav, #bottom-nav, nav, .toast, .modal-backdrop, .btn, .row-actions, .denied { display: none !important; }
            .print-only { display: block !important; }
            body { background: white; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 12px; }
            .card { box-shadow: none; border: 1px solid #ccc; }
            table.matrix { min-width: 0; font-size: 9pt; }
            table.matrix th, table.matrix td { padding: 4px 3px; }
            .mark-yes { background: #eee !important; border: 1px solid #333; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .content { max-width: none; padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH/recipes" class="back-link no-print">← <?php echo $is_sweet ? 'Menu & Recipes' : 'Menu & Recipes'; ?></a>
        <h1><?php echo $is_sweet ? 'Allergen Menu' : 'Allergen Menu'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Big 9 staff matrix · FOH & BOH' : 'US Big 9 staff reference matrix'; ?></p>
    </div>

    <div class="content">
        <div class="intro" id="intro">
            <?php echo $is_sweet
                ? 'Optional staff reference for guest questions — milk, eggs, fish, shellfish, tree nuts, peanuts, wheat, soy, sesame. Leave blank if you don’t need it; never blocks costing 💕'
                : 'Optional US Big 9 matrix for FOH/BOH guest questions. Empty is fine — does not block menu engineering or costing.'; ?>
        </div>

        <div class="link-row no-print">
            <a class="chip" href="/BOH/menu" data-perm-any="boh.recipes.menu_view,boh.recipes.menu_edit"><?php echo $is_sweet ? '📈 Menu Engineering' : '📈 Menu'; ?></a>
            <a class="chip" href="/BOH/recipe-cards" data-perm-any="boh.recipes.std_view,boh.recipes.std_edit"><?php echo $is_sweet ? '🧾 Recipes' : '🧾 Recipes'; ?></a>
            <a class="chip" href="/BOH/86"><?php echo $is_sweet ? '🚫 86 Board' : '🚫 86 Board'; ?></a>
            <button type="button" class="chip" id="print-btn"><?php echo $is_sweet ? '🖨️ Print matrix' : '🖨️ Print'; ?></button>
        </div>

        <div class="sync-pill syncing no-print" id="sync-pill" title="<?php echo $is_sweet ? 'Shared with your restaurant group' : 'Shared with your restaurant group'; ?>">
            <span class="dot" aria-hidden="true"></span>
            <span id="sync-pill-text" class="sync-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div class="denied intro" id="denied"><?php echo $is_sweet ? 'No permission to view the allergen menu.' : 'No permission to view the allergen menu.'; ?></div>

        <div id="board-ui">
            <div class="counts" id="counts"></div>

            <div class="link-row no-print" id="filters" role="group" aria-label="Filter">
                <button type="button" class="chip active" data-filter="tagged"><?php echo $is_sweet ? 'Tagged only' : 'Tagged only'; ?></button>
                <button type="button" class="chip" data-filter="all"><?php echo $is_sweet ? 'All menu items' : 'All menu items'; ?></button>
            </div>

            <div class="card">
                <h2><?php echo $is_sweet ? 'Allergen matrix' : 'Allergen matrix'; ?></h2>
                <p class="hint no-print"><?php echo $is_sweet
                    ? 'Tap a row to tag Big 9 allergens + an optional note (shared oil, can omit nuts…). Syncs house-wide.'
                    : 'Edit a row for Big 9 tags and optional notes. House-synced overlay for multi-device FOH.'; ?></p>
                <div class="legend no-print">
                    <span><span class="mark mark-yes">✓</span> Contains</span>
                    <span><span class="mark mark-no">·</span> Not tagged</span>
                </div>
                <div class="print-only" style="margin-bottom:8px;font-size:10pt;">
                    <strong><?php echo $is_sweet ? 'Allergen Menu · Staff reference' : 'Allergen Menu · Staff reference'; ?></strong>
                    <span id="print-date"></span>
                    <div style="font-size:9pt;opacity:0.8;margin-top:4px;"><?php echo $is_sweet ? 'US Big 9 — confirm with kitchen for guest allergies.' : 'US Big 9 — confirm with kitchen for guest allergies.'; ?></div>
                </div>
                <div class="matrix-wrap">
                    <div id="matrix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="modal">
        <div class="modal">
            <h2 id="edit-title"><?php echo $is_sweet ? 'Tag allergens' : 'Tag allergens'; ?></h2>
            <p class="edit-sub" id="edit-sub"></p>
            <input type="hidden" id="e-id">
            <div class="field">
                <label><?php echo $is_sweet ? 'US Big 9 (optional)' : 'US Big 9 (optional)'; ?></label>
                <div id="e-checks"></div>
            </div>
            <div class="field">
                <label for="e-note"><?php echo $is_sweet ? 'Note (optional)' : 'Note (optional)'; ?></label>
                <input type="text" id="e-note" maxlength="200" placeholder="<?php echo $is_sweet ? 'e.g. fried in shared oil, can omit nuts' : 'e.g. fried in shared oil, can omit nuts'; ?>">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-danger btn-small" id="e-clear"><?php echo $is_sweet ? 'Clear tags' : 'Clear tags'; ?></button>
                <button type="button" class="btn btn-secondary" id="e-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                <button type="button" class="btn btn-primary" id="e-save"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/shared-state.js?v=3"></script>
    <script src="/allergen-menu-shared.js?v=1"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var A = window.PbjAllergenMenu;
        if (!A) return;

        var overlay = A.loadOverlayLocal();
        var sync = null;
        var filter = 'tagged';
        var modal = document.getElementById('modal');
        var editingId = null;

        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return true;
        }
        function canView() {
            return canP('boh.recipes.menu_view') || canP('boh.recipes.menu_edit') || canP('boh.tools.use');
        }
        function canEdit() {
            return canP('boh.recipes.menu_edit') || canP('boh.tools.use');
        }

        function toast(msg) {
            var el = document.getElementById('toast');
            if (!el) return;
            el.textContent = msg;
            el.classList.add('show');
            clearTimeout(toast._t);
            toast._t = setTimeout(function () { el.classList.remove('show'); }, 1800);
        }

        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function applyPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            var view = canView();
            var edit = canEdit();
            document.getElementById('denied').style.display = view ? 'none' : 'block';
            document.getElementById('board-ui').style.display = view ? '' : 'none';
            document.querySelectorAll('.row-actions').forEach(function (row) {
                row.style.display = edit ? '' : 'none';
            });
        }

        function persistAndPush() {
            overlay = A.saveOverlayLocal(overlay);
            if (sync) sync.push(overlay);
            render();
        }

        function renderCounts(rows) {
            var tagged = rows.filter(function (r) { return r.tagged; }).length;
            var menuCount = A.loadMenuItems().length;
            document.getElementById('counts').innerHTML =
                '<span class="count-pill"><strong>' + tagged + '</strong> ' + (isSweet ? 'tagged' : 'tagged') + '</span>' +
                '<span class="count-pill"><strong>' + menuCount + '</strong> ' + (isSweet ? 'on menu' : 'on menu') + '</span>' +
                '<span class="count-pill"><strong>9</strong> Big 9</span>';
        }

        function openEdit(row) {
            if (!canEdit()) return;
            if (row.source === 'recipe') {
                toast(isSweet ? 'Tag this on Menu Engineering (or link the recipe)' : 'Edit via Menu Engineering');
                return;
            }
            editingId = row.id;
            document.getElementById('e-id').value = row.id;
            document.getElementById('edit-title').textContent = isSweet ? 'Tag allergens' : 'Tag allergens';
            document.getElementById('edit-sub').textContent = row.name || '';
            document.getElementById('e-note').value = row.allergenNote || '';
            document.getElementById('e-checks').innerHTML = A.renderCheckboxGrid(row.allergens || [], 'e-alg');
            modal.classList.add('show');
        }

        function saveEdit() {
            if (!canEdit() || !editingId) return;
            var allergens = A.readCheckboxes(document.getElementById('e-checks'));
            var note = String(document.getElementById('e-note').value || '').trim();
            var name = document.getElementById('edit-sub').textContent || '';
            var result = A.setItemAllergens(editingId, allergens, note, name);
            overlay = result.overlay;
            if (sync) sync.push(overlay);
            modal.classList.remove('show');
            editingId = null;
            render();
            toast(isSweet ? 'Allergens saved 💾' : 'Saved');
        }

        function clearEdit() {
            if (!canEdit() || !editingId) return;
            var result = A.clearItemAllergens(editingId);
            overlay = result.overlay;
            if (sync) sync.push(overlay);
            modal.classList.remove('show');
            editingId = null;
            render();
            toast(isSweet ? 'Tags cleared' : 'Cleared');
        }

        function render() {
            var allRows = A.mergeMatrixRows(overlay);
            renderCounts(allRows);
            var rows = allRows.filter(function (r) {
                if (filter === 'all') return r.source === 'menu' || r.source === 'overlay' || r.tagged;
                return r.tagged;
            });
            // When "all", show all menu items; when tagged, only tagged (incl recipe-only)
            if (filter === 'all') {
                rows = allRows.filter(function (r) { return r.source === 'menu' || r.source === 'overlay'; });
                // also append tagged recipe-only at end
                allRows.forEach(function (r) {
                    if (r.source === 'recipe' && r.tagged) rows.push(r);
                });
            }

            var root = document.getElementById('matrix');
            var menuEmpty = !A.loadMenuItems().length;
            var anyTagged = allRows.some(function (r) { return r.tagged; });

            if (menuEmpty && !anyTagged) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No menu items yet — add them in <a href="/BOH/menu">Menu Engineering</a>, then tag Big 9 allergens here (totally optional).'
                    : 'No menu items yet. Add items in Menu Engineering, then optionally tag allergens here.') + '</div>';
                applyPerms();
                return;
            }

            if (filter === 'tagged' && !rows.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'Nothing tagged yet — optional. Switch to <strong>All menu items</strong> to tag, or leave blank if your house doesn’t need an allergen matrix ✨'
                    : 'No allergen tags yet. Switch to “All menu items” to tag, or leave empty — this feature is optional.') + '</div>';
                applyPerms();
                return;
            }

            if (!rows.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'Nothing to show' : 'Nothing to show') + '</div>';
                applyPerms();
                return;
            }

            var head = '<thead><tr><th class="item-col">' + (isSweet ? 'Item' : 'Item') + '</th>' +
                A.BIG9.map(function (a) {
                    return '<th title="' + esc(a.label) + '">' + esc(a.short) + '</th>';
                }).join('') +
                (canEdit() ? '<th class="no-print row-actions"></th>' : '') +
                '</tr></thead>';

            var body = '<tbody>' + rows.map(function (r) {
                var cells = A.BIG9.map(function (a) {
                    var has = r.allergens.indexOf(a.id) !== -1;
                    return '<td>' + (has
                        ? '<span class="mark mark-yes" title="' + esc(a.label) + '">✓</span>'
                        : '<span class="mark mark-no">·</span>') + '</td>';
                }).join('');
                var badge = r.source === 'recipe'
                    ? '<span class="src-badge">' + (isSweet ? 'recipe' : 'recipe') + '</span>'
                    : (r.source === 'overlay' ? '<span class="src-badge">sync</span>' : '');
                var note = r.allergenNote
                    ? '<div class="item-note">' + esc(r.allergenNote) + '</div>'
                    : '';
                var actions = canEdit() && r.source !== 'recipe'
                    ? '<td class="no-print row-actions"><button type="button" class="btn btn-ghost btn-small" data-act="edit" data-id="' + esc(r.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button></td>'
                    : (canEdit() ? '<td class="no-print row-actions"></td>' : '');
                return '<tr data-id="' + esc(r.id) + '" data-source="' + esc(r.source) + '">' +
                    '<td class="item-col"><div class="item-name">' + esc(r.name) + badge + '</div>' +
                    (r.category ? '<div class="item-meta">' + esc(r.category) + '</div>' : '') +
                    note + '</td>' + cells + actions + '</tr>';
            }).join('') + '</tbody>';

            root.innerHTML = '<table class="matrix" aria-label="Allergen matrix">' + head + body + '</table>';
            applyPerms();
        }

        document.getElementById('filters').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-filter]');
            if (!btn) return;
            filter = btn.getAttribute('data-filter');
            document.querySelectorAll('#filters .chip').forEach(function (c) {
                c.classList.toggle('active', c === btn);
            });
            render();
        });

        document.getElementById('matrix').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act="edit"]');
            if (btn) {
                var id = btn.getAttribute('data-id');
                var row = A.mergeMatrixRows(overlay).find(function (r) { return r.id === id; });
                if (row) openEdit(row);
                return;
            }
            if (!canEdit()) return;
            var tr = e.target.closest('tr[data-id]');
            if (!tr || e.target.closest('button')) return;
            if (tr.getAttribute('data-source') === 'recipe') return;
            var id2 = tr.getAttribute('data-id');
            var row2 = A.mergeMatrixRows(overlay).find(function (r) { return r.id === id2; });
            if (row2) openEdit(row2);
        });

        document.getElementById('e-save').addEventListener('click', saveEdit);
        document.getElementById('e-clear').addEventListener('click', clearEdit);
        document.getElementById('e-cancel').addEventListener('click', function () {
            modal.classList.remove('show');
            editingId = null;
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                editingId = null;
            }
        });

        document.getElementById('print-btn').addEventListener('click', function () {
            var prev = filter;
            filter = 'tagged';
            document.querySelectorAll('#filters .chip').forEach(function (c) {
                c.classList.toggle('active', c.getAttribute('data-filter') === 'tagged');
            });
            render();
            document.getElementById('print-date').textContent = ' · ' + new Date().toLocaleString();
            setTimeout(function () {
                window.print();
                filter = prev;
                document.querySelectorAll('#filters .chip').forEach(function (c) {
                    c.classList.toggle('active', c.getAttribute('data-filter') === prev);
                });
                render();
            }, 50);
        });

        sync = A.wire({
            statusEl: 'sync-pill',
            pollMs: 5000,
            getState: function () { return overlay; },
            setState: function (next) {
                overlay = A.normalizeOverlay(next);
                A.saveOverlayLocal(overlay);
                render();
            }
        });

        render();
        if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyPerms);
        document.addEventListener('pbj-perms-ready', applyPerms);
    })();
    </script>
</body>
</html>
