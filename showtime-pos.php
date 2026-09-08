<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$theme_id = function_exists('pbj_theme_id') ? pbj_theme_id() : 'sweet';

// Starter category shells — theme icon pack when available, else emoji
$posEmoji = [
    'ringins' => '🧾', 'mods' => '✏️', 'comps' => '🎁',
    'payments' => '💳', 'shortcuts' => '⌨️', 'troubleshooting' => '🔧',
];
$default_categories = [];
foreach ([
    ['id' => 'ringins',   'title' => 'Common Ring-Ins',          'hint' => 'Frequent menu buttons, modifiers, and PLUs'],
    ['id' => 'mods',      'title' => 'Modifiers & 86s',          'hint' => 'Extra / no / sub shortcuts and 86 process'],
    ['id' => 'comps',     'title' => 'Comps & Voids',            'hint' => 'When and how to comp, void, or discount'],
    ['id' => 'payments',  'title' => 'Payments & Cash-Out',      'hint' => 'Tenders, split checks, tips, and cash-out steps'],
    ['id' => 'shortcuts', 'title' => 'Keyboard / POS Shortcuts', 'hint' => 'Hotkeys and speed codes for your system'],
    ['id' => 'troubleshooting', 'title' => 'POS Troubleshooting', 'hint' => 'Printer jams, card declines, and reboot tips'],
] as $row) {
    $src = function_exists('pbj_icon') ? pbj_icon('pos-hub/' . $row['id']) : '';
    $row['icon'] = ($src !== '') ? $src : ($posEmoji[$row['id']] ?? '•');
    $default_categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>

    <title><?php echo $is_sweet ? 'POS Quick Reference' : 'POS Quick Reference'; ?> • <?php echo pbj_hub_label('foh'); ?> • ilovepbj ops</title>

    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>

    <style>
        @font-face {
            font-family: 'DreamingOutLoudPro';
            src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype');
        }
        @font-face {
            font-family: 'ModernLoveCaps';
            src: url('/Fonts/modern-love-caps.ttf') format('truetype');
        }

        body {
            margin: 0;
            padding-bottom: 100px;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
                background: #FCF8EE;
                color: #3a2f1f;
            <?php else: ?>
                font-family: 'Lora', serif;
                background: #F1EBE4;
                color: #1A2A44;
            <?php endif; ?>
        }

        .header {
            <?php if ($is_sweet): ?>
                background: #E55163;
            <?php else: ?>
                background: #1A2A44;
            <?php endif; ?>
            color: white;
            padding: 20px 25px 25px;
            text-align: center;
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            opacity: 0.9;
            font-size: 1rem;
            margin-bottom: 12px;
        }

        .back-link:hover {
            opacity: 1;
            text-decoration: underline;
        }

        h1 {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
            <?php else: ?>
                font-family: 'Lora', serif;
            <?php endif; ?>
            font-size: 2.4rem;
            margin: 0;
            line-height: 1.15;
        }

        .subtitle {
            margin: 10px 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 24px 16px;
            max-width: 760px;
            margin: 0 auto;
        }

        .intro {
            background: white;
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 18px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            font-size: 1.05rem;
            line-height: 1.45;
            opacity: 0.9;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .btn {
            border: none;
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>
                font-family: 'Lora', serif;
            <?php endif; ?>
        }

        .btn-primary {
            <?php if ($is_sweet): ?>
                background: #E55163;
                color: white;
            <?php else: ?>
                background: #1A2A44;
                color: white;
            <?php endif; ?>
        }

        .btn-secondary {
            background: white;
            color: inherit;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .btn-small {
            padding: 8px 12px;
            font-size: 0.9rem;
            border-radius: 10px;
        }

        .btn-ghost {
            background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>;
            color: inherit;
            border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
        }

        .btn-danger {
            background: #FDECEA;
            color: #B71C1C;
        }

        .category {
            background: white;
            border-radius: 18px;
            margin-bottom: 14px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            cursor: pointer;
            user-select: none;
            width: 100%;
            border: none;
            background: transparent;
            text-align: left;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
                color: #3a2f1f;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
        }

        .category-header:hover {
            background: <?php echo $is_sweet ? '#FFF8F9' : '#F8F5F1'; ?>;
        }

        .cat-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            overflow: hidden;
            <?php if ($is_sweet): ?>
                background: #FFF5F6;
                border: 2px solid #E55163;
            <?php else: ?>
                background: #EEF2F8;
                border: 2px solid #1A2A44;
            <?php endif; ?>
        }
        .cat-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        <?php if ($is_sweet): ?>
        .cat-icon.sweet-sticker {
            width: 56px;
            height: 56px;
            min-width: 56px;
            border-radius: 14px;
            background: #FFFBFA;
            padding: 3px;
            box-sizing: border-box;
        }
        <?php endif; ?>

        .cat-titles {
            flex: 1;
            min-width: 0;
        }

        .cat-title {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 1.35rem;
            margin: 0;
            line-height: 1.2;
        }

        .cat-hint {
            margin: 4px 0 0;
            font-size: 0.92rem;
            opacity: 0.7;
            line-height: 1.3;
        }

        .cat-count {
            font-size: 0.9rem;
            opacity: 0.65;
            white-space: nowrap;
        }

        .chevron {
            font-size: 1.1rem;
            opacity: 0.5;
            transition: transform 0.2s;
        }

        .category.open .chevron {
            transform: rotate(90deg);
        }

        .category-body {
            display: none;
            padding: 0 18px 18px;
            border-top: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>;
        }

        .category.open .category-body {
            display: block;
        }

        .entry-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 14px 0;
        }

        .entry {
            border-radius: 14px;
            padding: 14px 14px 12px;
            <?php if ($is_sweet): ?>
                background: #FFFBF8;
                border: 1px solid #F3E8DD;
            <?php else: ?>
                background: #FAF8F5;
                border: 1px solid #E6DFD7;
            <?php endif; ?>
        }

        .entry-label {
            font-size: 1.1rem;
            margin: 0 0 4px;
            font-weight: 600;
        }

        .entry-detail {
            margin: 0;
            font-size: 0.98rem;
            line-height: 1.4;
            opacity: 0.8;
            white-space: pre-wrap;
        }

        .entry-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .empty-slot {
            text-align: center;
            padding: 22px 14px;
            border-radius: 14px;
            border: 2px dashed <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            opacity: 0.8;
            font-size: 1rem;
            line-height: 1.4;
            margin: 14px 0;
        }

        .add-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Modal / editor */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 2000;
            align-items: flex-end;
            justify-content: center;
            padding: 16px;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 20px 20px 16px 16px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 22px 20px 20px;
            box-shadow: 0 -8px 30px rgba(0,0,0,0.2);
        }

        .modal h2 {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 1.6rem;
            margin: 0 0 14px;
        }

        .field {
            margin-bottom: 12px;
        }

        .field label {
            display: block;
            font-size: 0.85rem;
            opacity: 0.65;
            margin-bottom: 4px;
        }

        .field input, .field textarea, .field select {
            width: 100%;
            box-sizing: border-box;
            border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px;
            font-size: 1rem;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
                background: #FFFBF8;
                color: #3a2f1f;
            <?php else: ?>
                font-family: 'Lora', serif;
                background: #FAF8F5;
                color: #1A2A44;
            <?php endif; ?>
        }

        .field textarea {
            min-height: 100px;
            resize: vertical;
        }

        .field input:focus, .field textarea:focus, .field select:focus {
            outline: none;
            border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }

        .modal-actions .btn {
            flex: 1;
        }

        .actions-bar {
            display: flex;
            gap: 10px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .actions-bar .btn {
            flex: 1;
            min-width: 120px;
        }

        .saved-toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
            color: white;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 0.95rem;
            opacity: 0;
            pointer-events: none;
            transition: all 0.25s;
            z-index: 2100;
        }

        .saved-toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/FOH" class="back-link">← <?php echo pbj_back_to_hub('foh'); ?></a>
        <h1><?php echo $is_sweet ? 'POS Quick Reference' : 'POS Quick Reference'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Your house cheat sheet — fill it your way' : 'House cheat sheet — customize for your POS'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Categories are ready — entries are blank on purpose so each restaurant can add their own ring-ins, comps, shortcuts, and tips. Tap a category to open it, then add whatever your team needs 💕'
                : 'Categories are set up and left blank so your team can add ring-ins, comps, shortcuts, and tips for your POS. Open a category and add entries as needed.'; ?>
        </div>

        <div class="toolbar">
            <button type="button" class="btn btn-primary" id="add-category-btn" data-perm="foh.pos.add_category"><?php echo $is_sweet ? '+ New category' : '+ New category'; ?></button>
            <button type="button" class="btn btn-secondary" id="expand-all-btn" data-perm="foh.pos.expand"><?php echo $is_sweet ? 'Expand all' : 'Expand all'; ?></button>
            <button type="button" class="btn btn-secondary" id="collapse-all-btn" data-perm="foh.pos.expand"><?php echo $is_sweet ? 'Collapse all' : 'Collapse all'; ?></button>
        </div>

        <div id="categories"></div>

        <div class="actions-bar">
            <button type="button" class="btn btn-secondary" id="reset-btn" data-perm="foh.pos.reset"><?php echo $is_sweet ? 'Reset to empty shells' : 'Reset to empty shells'; ?></button>
            <a href="/FOH" class="btn btn-primary"><?php echo pbj_back_to_hub('foh'); ?></a>
        </div>
    </div>

    <div class="modal-backdrop" id="modal-backdrop" role="dialog" aria-modal="true">
        <div class="modal">
            <h2 id="modal-title"><?php echo $is_sweet ? 'Add entry' : 'Add entry'; ?></h2>
            <form id="entry-form">
                <input type="hidden" id="edit-cat-id">
                <input type="hidden" id="edit-entry-id">
                <div class="field" id="field-cat-title" style="display:none;">
                    <label for="cat-title-input"><?php echo $is_sweet ? 'Category name' : 'Category name'; ?></label>
                    <input type="text" id="cat-title-input" placeholder="<?php echo $is_sweet ? 'e.g. Happy Hour buttons' : 'e.g. Happy Hour buttons'; ?>">
                </div>
                <div class="field" id="field-cat-hint" style="display:none;">
                    <label for="cat-hint-input"><?php echo $is_sweet ? 'Short description' : 'Short description'; ?></label>
                    <input type="text" id="cat-hint-input" placeholder="<?php echo $is_sweet ? 'What belongs in this category?' : 'What belongs in this category?'; ?>">
                </div>
                <div class="field" id="field-cat-icon" style="display:none;">
                    <label for="cat-icon-input"><?php echo $is_sweet ? 'Emoji icon' : 'Emoji icon'; ?></label>
                    <input type="text" id="cat-icon-input" maxlength="4" placeholder="💳">
                </div>
                <div class="field" id="field-entry-label">
                    <label for="entry-label"><?php echo $is_sweet ? 'Title / button name' : 'Title / button name'; ?></label>
                    <input type="text" id="entry-label" placeholder="<?php echo $is_sweet ? 'e.g. Split check, Open food, Void last…' : 'e.g. Split check, Open food, Void last…'; ?>">
                </div>
                <div class="field" id="field-entry-detail">
                    <label for="entry-detail"><?php echo $is_sweet ? 'How-to / notes' : 'How-to / notes'; ?></label>
                    <textarea id="entry-detail" placeholder="<?php echo $is_sweet ? 'Steps, codes, manager approval notes — whatever your team needs to remember.' : 'Steps, codes, manager approval notes, etc.'; ?>"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="modal-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary" id="modal-save"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="saved-toast" id="saved-toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>

    <?php include 'bottom-nav.php'; ?>

    <script>
        (function () {
            const STORAGE_KEY = 'pbj_showtime_pos_ref_v1';
            const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyPosPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                // re-render categories if function exists
                try { if (typeof render === 'function') render(); } catch (e) {}
            }

            const defaults = <?php echo json_encode($default_categories, JSON_UNESCAPED_UNICODE); ?>;

            const labels = {
                empty: isSweet
                    ? 'Nothing here yet — add the notes your restaurant uses ✨'
                    : 'No entries yet. Add notes specific to your restaurant.',
                entries: isSweet ? 'entries' : 'entries',
                entry: isSweet ? 'entry' : 'entry',
                addEntry: isSweet ? '+ Add entry' : '+ Add entry',
                edit: isSweet ? 'Edit' : 'Edit',
                remove: isSweet ? 'Remove' : 'Remove',
                editCat: isSweet ? 'Edit category' : 'Edit category',
                deleteCat: isSweet ? 'Remove category' : 'Remove category',
                addEntryTitle: isSweet ? 'Add entry' : 'Add entry',
                editEntryTitle: isSweet ? 'Edit entry' : 'Edit entry',
                addCatTitle: isSweet ? 'New category' : 'New category',
                editCatTitle: isSweet ? 'Edit category' : 'Edit category'
            };

            let state = loadState();
            let modalMode = 'entry'; // entry | category

            function uid() {
                return Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
            }

            function blankFromDefaults() {
                return {
                    categories: defaults.map(function (c) {
                        return {
                            id: c.id,
                            title: c.title,
                            icon: c.icon,
                            hint: c.hint,
                            open: false,
                            entries: []
                        };
                    })
                };
            }

            function loadState() {
                try {
                    const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
                    if (!raw || !Array.isArray(raw.categories) || !raw.categories.length) {
                        return blankFromDefaults();
                    }
                    // Refresh known starter category icons (emoji → stickers in Sweet)
                    var iconById = {};
                    defaults.forEach(function (c) { iconById[c.id] = c.icon; });
                    raw.categories.forEach(function (cat) {
                        if (cat && cat.id && iconById[cat.id]) {
                            cat.icon = iconById[cat.id];
                        }
                    });
                    return raw;
                } catch (e) {
                    return blankFromDefaults();
                }
            }

            function renderCatIcon(icon) {
                var v = String(icon || '💳');
                if (v.indexOf('assets/') === 0 || v.indexOf('/') !== -1 || /\.(jpe?g|png|webp|gif|svg)$/i.test(v)) {
                    var cls = 'cat-icon';
                    if (v.indexOf('pbj-') !== -1 || v.indexOf('/showtime/') !== -1 || v.indexOf('pos-hub/') !== -1 && v.indexOf('.jpg') !== -1) {
                        cls += ' sweet-sticker';
                    } else if (v.indexOf('assets/icons/') === 0) {
                        cls += ' theme-icon';
                    } else if (typeof isSweet !== 'undefined' && isSweet) {
                        cls += ' sweet-sticker';
                    }
                    return '<span class="' + cls + '"><img src="' + escapeHtml(v) + '" alt=""></span>';
                }
                return '<span class="cat-icon">' + escapeHtml(v) + '</span>';
            }

            function saveState(showToast) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                if (showToast) {
                    const toast = document.getElementById('saved-toast');
                    toast.classList.add('show');
                    setTimeout(function () { toast.classList.remove('show'); }, 1200);
                }
            }

            function escapeHtml(str) {
                return String(str || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function findCat(id) {
                return state.categories.find(function (c) { return c.id === id; });
            }

            function render() {
                const root = document.getElementById('categories');
                if (!state.categories.length) {
                    root.innerHTML = '<div class="empty-slot">' + escapeHtml(labels.empty) + '</div>';
                    return;
                }

                root.innerHTML = state.categories.map(function (cat) {
                    const count = (cat.entries || []).length;
                    const countLabel = count + ' ' + (count === 1 ? labels.entry : labels.entries);

                    let entriesHtml;
                    if (!count) {
                        entriesHtml = '<div class="empty-slot">' + escapeHtml(labels.empty) + '</div>';
                    } else {
                        entriesHtml = '<div class="entry-list">' + cat.entries.map(function (entry) {
                            return (
                                '<div class="entry" data-entry-id="' + escapeHtml(entry.id) + '">' +
                                    '<p class="entry-label">' + escapeHtml(entry.label) + '</p>' +
                                    (entry.detail ? '<p class="entry-detail">' + escapeHtml(entry.detail) + '</p>' : '') +
                                    '<div class="entry-actions">' +
                                        (canP('foh.pos.add_entry') ? ('<button type="button" class="btn btn-small btn-ghost" data-act="edit-entry" data-cat="' + escapeHtml(cat.id) + '" data-entry="' + escapeHtml(entry.id) + '">' + labels.edit + '</button>' +
                                        '<button type="button" class="btn btn-small btn-danger" data-act="delete-entry" data-cat="' + escapeHtml(cat.id) + '" data-entry="' + escapeHtml(entry.id) + '">' + labels.remove + '</button>') : '') +
                                    '</div>' +
                                '</div>'
                            );
                        }).join('') + '</div>';
                    }

                    return (
                        '<div class="category' + (cat.open ? ' open' : '') + '" data-cat-id="' + escapeHtml(cat.id) + '">' +
                            '<button type="button" class="category-header" data-act="toggle" data-cat="' + escapeHtml(cat.id) + '">' +
                                renderCatIcon(cat.icon) +
                                '<span class="cat-titles">' +
                                    '<h2 class="cat-title">' + escapeHtml(cat.title) + '</h2>' +
                                    (cat.hint ? '<p class="cat-hint">' + escapeHtml(cat.hint) + '</p>' : '') +
                                '</span>' +
                                '<span class="cat-count">' + escapeHtml(countLabel) + '</span>' +
                                '<span class="chevron">›</span>' +
                            '</button>' +
                            '<div class="category-body">' +
                                entriesHtml +
                                '<div class="add-row">' +
                                    (canP('foh.pos.add_entry') ? ('<button type="button" class="btn btn-small btn-primary" data-act="add-entry" data-cat="' + escapeHtml(cat.id) + '">' + labels.addEntry + '</button>') : '') +
                                    (canP('foh.pos.edit_category') ? ('<button type="button" class="btn btn-small btn-ghost" data-act="edit-cat" data-cat="' + escapeHtml(cat.id) + '">' + labels.editCat + '</button>' +
                                    '<button type="button" class="btn btn-small btn-danger" data-act="delete-cat" data-cat="' + escapeHtml(cat.id) + '">' + labels.deleteCat + '</button>') : '') +
                                '</div>' +
                            '</div>' +
                        '</div>'
                    );
                }).join('');
            }

            // Modal helpers
            const backdrop = document.getElementById('modal-backdrop');
            const form = document.getElementById('entry-form');

            function showModal(mode, catId, entryId) {
                modalMode = mode;
                document.getElementById('edit-cat-id').value = catId || '';
                document.getElementById('edit-entry-id').value = entryId || '';

                const fieldCatTitle = document.getElementById('field-cat-title');
                const fieldCatHint = document.getElementById('field-cat-hint');
                const fieldCatIcon = document.getElementById('field-cat-icon');
                const fieldLabel = document.getElementById('field-entry-label');
                const fieldDetail = document.getElementById('field-entry-detail');
                const title = document.getElementById('modal-title');

                const isCat = mode === 'category' || mode === 'edit-category';
                fieldCatTitle.style.display = isCat ? 'block' : 'none';
                fieldCatHint.style.display = isCat ? 'block' : 'none';
                fieldCatIcon.style.display = isCat ? 'block' : 'none';
                fieldLabel.style.display = isCat ? 'none' : 'block';
                fieldDetail.style.display = isCat ? 'none' : 'block';

                if (mode === 'category') {
                    title.textContent = labels.addCatTitle;
                    document.getElementById('cat-title-input').value = '';
                    document.getElementById('cat-hint-input').value = '';
                    document.getElementById('cat-icon-input').value = '💳';
                } else if (mode === 'edit-category') {
                    title.textContent = labels.editCatTitle;
                    const cat = findCat(catId);
                    document.getElementById('cat-title-input').value = cat ? cat.title : '';
                    document.getElementById('cat-hint-input').value = cat ? (cat.hint || '') : '';
                    document.getElementById('cat-icon-input').value = cat ? (cat.icon || '💳') : '💳';
                } else if (mode === 'edit-entry') {
                    title.textContent = labels.editEntryTitle;
                    const cat = findCat(catId);
                    const entry = cat && cat.entries.find(function (e) { return e.id === entryId; });
                    document.getElementById('entry-label').value = entry ? entry.label : '';
                    document.getElementById('entry-detail').value = entry ? (entry.detail || '') : '';
                } else {
                    title.textContent = labels.addEntryTitle;
                    document.getElementById('entry-label').value = '';
                    document.getElementById('entry-detail').value = '';
                }

                backdrop.classList.add('show');
                setTimeout(function () {
                    const focusEl = isCat
                        ? document.getElementById('cat-title-input')
                        : document.getElementById('entry-label');
                    focusEl.focus();
                }, 50);
            }

            function hideModal() {
                backdrop.classList.remove('show');
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const catId = document.getElementById('edit-cat-id').value;
                const entryId = document.getElementById('edit-entry-id').value;

                if (modalMode === 'category') {
                    const title = document.getElementById('cat-title-input').value.trim();
                    if (!title) return;
                    state.categories.push({
                        id: uid(),
                        title: title,
                        hint: document.getElementById('cat-hint-input').value.trim(),
                        icon: document.getElementById('cat-icon-input').value.trim() || '💳',
                        open: true,
                        entries: []
                    });
                } else if (modalMode === 'edit-category') {
                    const cat = findCat(catId);
                    if (!cat) return;
                    const title = document.getElementById('cat-title-input').value.trim();
                    if (!title) return;
                    cat.title = title;
                    cat.hint = document.getElementById('cat-hint-input').value.trim();
                    cat.icon = document.getElementById('cat-icon-input').value.trim() || '💳';
                } else if (modalMode === 'edit-entry') {
                    const cat = findCat(catId);
                    if (!cat) return;
                    const entry = cat.entries.find(function (x) { return x.id === entryId; });
                    if (!entry) return;
                    const label = document.getElementById('entry-label').value.trim();
                    if (!label) return;
                    entry.label = label;
                    entry.detail = document.getElementById('entry-detail').value.trim();
                } else {
                    const cat = findCat(catId);
                    if (!cat) return;
                    const label = document.getElementById('entry-label').value.trim();
                    if (!label) return;
                    cat.entries.push({
                        id: uid(),
                        label: label,
                        detail: document.getElementById('entry-detail').value.trim()
                    });
                    cat.open = true;
                }

                saveState(true);
                hideModal();
                render();
            });

            document.getElementById('modal-cancel').addEventListener('click', hideModal);
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) hideModal();
            });

            document.getElementById('categories').addEventListener('click', function (e) {
                const btn = e.target.closest('[data-act]');
                if (!btn) return;
                const act = btn.dataset.act;
                const catId = btn.dataset.cat;
                const entryId = btn.dataset.entry;
                const cat = findCat(catId);

                if (act === 'toggle') {
                    if (cat) {
                        cat.open = !cat.open;
                        saveState(false);
                        render();
                    }
                    return;
                }
                if (act === 'add-entry') {
                    showModal('entry', catId);
                    return;
                }
                if (act === 'edit-entry') {
                    showModal('edit-entry', catId, entryId);
                    return;
                }
                if (act === 'delete-entry') {
                    if (!cat) return;
                    if (!confirm(isSweet ? 'Remove this entry?' : 'Remove this entry?')) return;
                    cat.entries = cat.entries.filter(function (x) { return x.id !== entryId; });
                    saveState(true);
                    render();
                    return;
                }
                if (act === 'edit-cat') {
                    showModal('edit-category', catId);
                    return;
                }
                if (act === 'delete-cat') {
                    if (!confirm(isSweet ? 'Remove this whole category?' : 'Remove this category?')) return;
                    state.categories = state.categories.filter(function (c) { return c.id !== catId; });
                    saveState(true);
                    render();
                }
            });

            document.getElementById('add-category-btn').addEventListener('click', function () {
                if (!canP('foh.pos.add_category')) return;
                showModal('category');
            });

            document.getElementById('expand-all-btn').addEventListener('click', function () {
                state.categories.forEach(function (c) { c.open = true; });
                saveState(false);
                render();
            });

            document.getElementById('collapse-all-btn').addEventListener('click', function () {
                state.categories.forEach(function (c) { c.open = false; });
                saveState(false);
                render();
            });

            document.getElementById('reset-btn').addEventListener('click', function () {
                if (!canP('foh.pos.reset')) return;
                if (!confirm(isSweet
                    ? 'Reset to empty category shells? This clears all entries you added on this device.'
                    : 'Reset to empty category shells? This clears all entries on this device.')) return;
                state = blankFromDefaults();
                saveState(true);
                render();
            });

            render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyPosPerms);
            document.addEventListener('pbj-perms-ready', applyPosPerms);

        })();
    </script>
</body>
</html>
