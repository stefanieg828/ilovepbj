<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

$DOC_TYPES = [
    'handbook' => [
        'title_sweet' => 'Employee Handbook',
        'title_basic' => 'Employee Handbook',
        'parent' => 'team',
        'icon' => '📘',
        'blurb_sweet' => 'The house book for every teammate — edit sections or upload your own Word / PDF.',
        'blurb_basic' => 'House handbook for all staff. Edit sections or upload Word / PDF.',
    ],
    'training_manuals' => [
        'title_sweet' => 'Training Manuals',
        'title_basic' => 'Training Manuals',
        'parent' => 'team',
        'icon' => '📗',
        'blurb_sweet' => 'FOH + BOH training in one place — switch tabs, edit sections, or upload either packet.',
        'blurb_basic' => 'FOH and BOH training in one place. Switch tabs to edit or upload each manual.',
    ],
    // Legacy types kept for storage / uploads; requests redirect to training_manuals
    'foh_manual' => [
        'title_sweet' => 'FOH Training Manual',
        'title_basic' => 'FOH Training Manual',
        'parent' => 'team',
        'icon' => '✨',
        'blurb_sweet' => 'Servers, hosts & bar path — customize every section or upload your manual.',
        'blurb_basic' => 'Front-of-house training. Edit starters or upload your own manual.',
    ],
    'boh_manual' => [
        'title_sweet' => 'BOH Training Manual',
        'title_basic' => 'BOH Training Manual',
        'parent' => 'team',
        'icon' => '🔥',
        'blurb_sweet' => 'Kitchen, prep & dish standards — edit or drop in your BOH packet.',
        'blurb_basic' => 'Back-of-house training. Edit starters or upload your packet.',
    ],
    'sops' => [
        'title_sweet' => 'SOPs',
        'title_basic' => 'SOPs',
        'parent' => 'ops',
        'icon' => '📋',
        'blurb_sweet' => 'Standard operating procedures for how the house actually runs.',
        'blurb_basic' => 'Standard operating procedures for daily operations.',
    ],
    'health_safety' => [
        'title_sweet' => 'Health & Safety',
        'title_basic' => 'Health & Safety',
        'parent' => 'ops',
        'icon' => '🛟',
        'blurb_sweet' => 'Allergens, incidents, cleaning chemicals & emergency basics.',
        'blurb_basic' => 'Allergens, incidents, chemicals, and emergency basics.',
    ],
];

$type = preg_replace('/[^a-z_]/', '', strtolower((string) ($_GET['type'] ?? '')));
// Old FOH/BOH manual URLs → combined Training Manuals page
if ($type === 'foh_manual' || $type === 'boh_manual') {
    $part = $type === 'boh_manual' ? 'boh' : 'foh';
    header('Location: /admin/docs?type=training_manuals&part=' . $part);
    exit();
}
if (!isset($DOC_TYPES[$type])) {
    header('Location: /admin');
    exit();
}
$meta = $DOC_TYPES[$type];
$title = $is_sweet ? $meta['title_sweet'] : $meta['title_basic'];
$blurb = $is_sweet ? $meta['blurb_sweet'] : $meta['blurb_basic'];
$parent_href = $meta['parent'] === 'team' ? '/admin/team' : '/admin/ops';
$parent_label = $meta['parent'] === 'team'
    ? ($is_sweet ? 'Team & Roles' : 'Team')
    : ($is_sweet ? 'Restaurant Operations' : 'Operations');

$is_training = ($type === 'training_manuals');
$part = strtolower((string) ($_GET['part'] ?? 'foh'));
if ($part !== 'boh') {
    $part = 'foh';
}
// Active storage/upload type for the page (training page switches in JS)
$active_doc_type = $is_training
    ? ($part === 'boh' ? 'boh_manual' : 'foh_manual')
    : $type;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo htmlspecialchars($title); ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.2rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.92; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 12px; }
        .card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 8px; }
        .card-head h2 { margin: 0; }
        .mode-tabs { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
        .mode-tab { flex: 1; min-width: 140px; border: none; border-radius: 14px; padding: 12px 10px; font-size: 0.95rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); background: white; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .mode-tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .panel { display: none; } .panel.active { display: block; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 110px; resize: vertical; line-height: 1.45; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-add { width: 100%; margin-top: 4px; }
        .actions-bar { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .section { border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 0 14px 14px 0; padding: 14px 16px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .section-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .section-title { font-size: 1.12rem; margin: 0 0 6px; }
        .section-body { font-size: 0.98rem; opacity: 0.88; line-height: 1.45; white-space: pre-wrap; margin: 0 0 8px; }
        .section-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .empty { text-align: center; padding: 22px; opacity: 0.75; line-height: 1.4; }
        .upload-box { border: 2px dashed <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; border-radius: 16px; padding: 22px 16px; text-align: center; margin-bottom: 12px; }
        .upload-box strong { display: block; margin-bottom: 6px; }
        .file-chip { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 14px; border-radius: 14px; margin-bottom: 12px; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .file-chip .name { flex: 1; min-width: 140px; font-weight: 600; word-break: break-word; }
        .file-meta { font-size: 0.88rem; opacity: 0.75; width: 100%; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.8rem; background: <?php echo $is_sweet ? '#E8F8F1' : '#EAF1FA'; ?>; margin-right: 6px; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.5rem; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
        .print-only { display: none; }
        @media print {
            .header .back-link, .bottom-nav, .sync-pill, .mode-tabs, .toolbar, .section-actions, .btn-add, .actions-bar, .upload-box, .toast, .modal-backdrop { display: none !important; }
            body { background: white; padding: 0; }
            .header { background: white !important; color: black !important; }
            .print-only { display: block; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="<?php echo htmlspecialchars($parent_href); ?>" class="back-link">← <?php echo htmlspecialchars($parent_label); ?></a>
        <h1><?php echo htmlspecialchars($meta['icon'] . ' ' . $title); ?></h1>
        <p class="subtitle"><?php echo htmlspecialchars($blurb); ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div class="intro">
            <?php if ($is_training): ?>
                <?php echo $is_sweet
                    ? 'Both <strong>FOH</strong> and <strong>BOH</strong> training live here. Pick a tab, then use the editable guide or upload a Word / PDF for that side. Uploads are saved for the whole house 💕'
                    : 'FOH and BOH training live on this page. Choose a tab, then edit the starter guide or upload a Word / PDF for that manual.'; ?>
            <?php else: ?>
                <?php echo $is_sweet
                    ? 'Use the <strong>editable guide</strong> we started for you, or switch to <strong>upload</strong> if you already have a Word doc or PDF. Uploaded files live on your restaurant’s account so the whole team can open them 💕'
                    : 'Use the editable starter guide, or upload an existing Word / PDF. Uploaded files are stored for your restaurant so the team can open them.'; ?>
            <?php endif; ?>
        </div>

        <?php if ($is_training): ?>
        <div class="mode-tabs" id="part-tabs" style="margin-bottom:10px;">
            <button type="button" class="mode-tab <?php echo $part === 'foh' ? 'active' : ''; ?>" data-part="foh" id="part-foh"><?php echo $is_sweet ? '✨ FOH Training' : 'FOH Training'; ?></button>
            <button type="button" class="mode-tab <?php echo $part === 'boh' ? 'active' : ''; ?>" data-part="boh" id="part-boh"><?php echo $is_sweet ? '🔥 BOH Training' : 'BOH Training'; ?></button>
        </div>
        <?php endif; ?>

        <div class="mode-tabs">
            <button type="button" class="mode-tab active" data-mode="edit" id="tab-edit"><?php echo $is_sweet ? '✏️ Editable guide' : 'Editable guide'; ?></button>
            <button type="button" class="mode-tab" data-mode="upload" id="tab-upload"><?php echo $is_sweet ? '📎 Upload file' : 'Upload file'; ?></button>
        </div>

        <div class="panel active" id="panel-edit">
            <div class="card">
                <div class="card-head">
                    <h2><?php echo $is_sweet ? 'Sections' : 'Sections'; ?></h2>
                </div>
                <p class="hint"><?php echo $is_sweet
                    ? 'Add, edit, reorder, or delete sections. Reset restores the starter content (won’t delete an upload).'
                    : 'Add, edit, reorder, or delete sections. Reset restores starter content (keeps any upload).'; ?></p>
                <div class="toolbar">
                    <button type="button" class="btn btn-ghost btn-small" id="print-btn"><?php echo $is_sweet ? '🖨️ Print' : 'Print'; ?></button>
                    <button type="button" class="btn btn-ghost btn-small" id="reset-btn"><?php echo $is_sweet ? '♻️ Reset starters' : 'Reset starters'; ?></button>
                </div>
                <div id="sections-list"></div>
                <button type="button" class="btn btn-secondary btn-add" id="add-section-btn"><?php echo $is_sweet ? '+ Add section' : '+ Add section'; ?></button>
            </div>
        </div>

        <div class="panel" id="panel-upload">
            <div class="card">
                <h2><?php echo $is_sweet ? 'House file' : 'Uploaded file'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'PDF, Word (.doc / .docx) up to 10&nbsp;MB. This becomes the official packet when teammates open Upload mode.'
                    : 'PDF or Word (.doc / .docx), up to 10 MB.'; ?></p>
                <div id="upload-status"></div>
                <div class="upload-box" id="upload-box">
                    <strong><?php echo $is_sweet ? 'Drop your handbook / manual here' : 'Choose a file to upload'; ?></strong>
                    <span class="hint" style="display:block;margin:0;"><?php echo $is_sweet ? 'or tap to browse' : 'PDF or Word'; ?></span>
                    <input type="file" id="file-input" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="margin-top:12px;max-width:100%;">
                </div>
                <button type="button" class="btn btn-primary" id="upload-btn" style="width:100%;"><?php echo $is_sweet ? 'Upload for the house ✨' : 'Upload'; ?></button>
            </div>
        </div>

        <div class="actions-bar">
            <a href="<?php echo htmlspecialchars($parent_href); ?>" class="btn btn-secondary"><?php echo htmlspecialchars($parent_label); ?></a>
            <button type="button" class="btn btn-primary" id="save-note"><?php echo $is_sweet ? 'Saved automatically 💾' : 'Auto-saved'; ?></button>
        </div>
    </div>

    <div class="modal-backdrop" id="section-modal">
        <div class="modal">
            <h2 id="section-modal-title"><?php echo $is_sweet ? 'Add section' : 'Add section'; ?></h2>
            <form id="section-form">
                <input type="hidden" id="s-id">
                <div class="field"><label><?php echo $is_sweet ? 'Title' : 'Title'; ?></label><input id="s-title" required placeholder="<?php echo $is_sweet ? 'e.g. Dress code, Ticket flow…' : 'Section title'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Content' : 'Content'; ?></label><textarea id="s-body" rows="8" placeholder="<?php echo $is_sweet ? 'Write the house standard…' : 'Details…'; ?>"></textarea></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="s-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
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
        var IS_TRAINING = <?php echo $is_training ? 'true' : 'false'; ?>;
        var PAGE_TITLE = <?php echo json_encode($title); ?>;
        var DOC_TYPE = <?php echo json_encode($active_doc_type); ?>;
        var DOC_TITLE = PAGE_TITLE;
        var KEY = 'pbj_admin_doc_' + DOC_TYPE + '_v1';
        var SHARED_KEY = 'admin_doc_' + DOC_TYPE + '_v1';
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var DOC_PARENT = <?php echo json_encode($meta['parent'] ?? 'team'); ?>;
        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return true;
        }
        function docKey(suffix) {
            var base = (DOC_PARENT === 'ops') ? 'admin.ops.docs.' : 'admin.team.docs.';
            // ops uses sections as combined key for structure
            if (DOC_PARENT === 'ops') {
                if (suffix === 'edit_guide' || suffix === 'upload') return base + suffix;
                return 'admin.ops.docs.sections';
            }
            return base + suffix;
        }
        function applyDocPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            var map = {
                'add-section-btn': 'add_section',
                'print-btn': 'print',
                'reset-btn': 'reset',
                'upload-btn': 'upload',
                'tab-edit': 'edit_guide',
                'tab-upload': 'upload'
            };
            Object.keys(map).forEach(function(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.style.display = canP(docKey(map[id])) ? '' : 'none';
            });
            // section actions re-render
            try { renderSections(); } catch(e) {}
        }

        var shared = null;

        function partLabel(type) {
            if (type === 'boh_manual') return isSweet ? 'BOH Training' : 'BOH Training';
            if (type === 'foh_manual') return isSweet ? 'FOH Training' : 'FOH Training';
            return PAGE_TITLE;
        }

        function refreshKeys() {
            KEY = 'pbj_admin_doc_' + DOC_TYPE + '_v1';
            SHARED_KEY = 'admin_doc_' + DOC_TYPE + '_v1';
            DOC_TITLE = IS_TRAINING ? (PAGE_TITLE + ' · ' + partLabel(DOC_TYPE)) : PAGE_TITLE;
        }
        refreshKeys();

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function defaultSections() {
            var packs = {
                handbook: isSweet ? [
                    { id: 'hb-welcome', title: 'Welcome to the house', body: 'We’re glad you’re here! This handbook covers how we take care of guests, each other, and the restaurant. Ask a manager anytime something is unclear — no question is too small.' },
                    { id: 'hb-conduct', title: 'Team standards', body: '• Be on time and ready to work\n• Treat guests and teammates with respect\n• Phones away on the floor unless for work\n• Report issues early — we fix things together\n• Follow managers’ direction during service' },
                    { id: 'hb-dress', title: 'Dress code', body: 'Wear clean, house-approved uniform pieces. Closed-toe non-slip shoes. Hair secured as needed for your station. Name tag if provided. Ask your manager for the full look for FOH vs BOH.' },
                    { id: 'hb-attendance', title: 'Attendance & call-outs', body: 'If you cannot make a shift, call (don’t only text) as early as possible and follow the house call-out policy. Find coverage when asked. No-call / no-show may lead to progressive discipline.' },
                    { id: 'hb-pay', title: 'Pay, tips & breaks', body: 'Pay schedule and tip pooling follow house rules and local law. Take breaks as scheduled. Report payroll questions to management promptly.' },
                    { id: 'hb-safety', title: 'Safety & allergens', body: 'Know basic emergency exits, first-aid kit location, and how to report injuries. Never guess on allergens — check with a manager or the kitchen lead.' },
                    { id: 'hb-ack', title: 'Acknowledgement', body: 'I have read (or had explained) this handbook and understand I am responsible for following house standards. Updates may be posted; check with management for the latest version.' }
                ] : [
                    { id: 'hb-welcome', title: 'Welcome', body: 'This handbook covers expectations for all team members. Ask a manager if anything is unclear.' },
                    { id: 'hb-conduct', title: 'Conduct', body: 'Be on time, respectful, and ready to work. Follow direction during service. Report issues early.' },
                    { id: 'hb-dress', title: 'Dress code', body: 'Clean house-approved uniform, closed-toe non-slip shoes, hair secured as required for the station.' },
                    { id: 'hb-attendance', title: 'Attendance', body: 'Call out as early as possible per house policy. No-call / no-show may result in discipline.' },
                    { id: 'hb-pay', title: 'Pay & tips', body: 'Pay schedule and tip rules follow house policy and local law. Report payroll questions promptly.' },
                    { id: 'hb-safety', title: 'Safety', body: 'Know exits, first aid, and how to report injuries. Never guess on allergens.' },
                    { id: 'hb-ack', title: 'Acknowledgement', body: 'I understand I am responsible for following these standards and any posted updates.' }
                ],
                foh_manual: isSweet ? [
                    { id: 'foh-greet', title: 'Greet & seat', body: 'Acknowledge every guest within 60 seconds. Warm welcome, party size, seating preference. Flag allergies or celebrations to the server.' },
                    { id: 'foh-table', title: '8-step hospitality flow', body: '1. Greet fast\n2. Drinks first\n3. Know the menu\n4. Repeat the order\n5. Two-bite check-back\n6. Clear kindly\n7. Offer dessert / coffee\n8. Thank & invite back' },
                    { id: 'foh-pos', title: 'POS & payments', body: 'Ring accurately, apply comps only with approval, split checks cleanly, and never leave a signed card voucher unattended. Know void / comp house rules.' },
                    { id: 'foh-side', title: 'Sidework & stations', body: 'Complete opening, mid, and closing sidework checklists. Restock before you leave a station messy for the next person.' },
                    { id: 'foh-bar', title: 'Bar & alcohol (if applicable)', body: 'Check IDs when required. Never overserve. Know house pour standards and mocktail / zero-proof options.' },
                    { id: 'foh-last', title: 'Service recovery (L.A.S.T.)', body: 'Listen · Apologize · Solve · Thank. Escalate to a manager for serious issues. Never argue with a guest.' }
                ] : [
                    { id: 'foh-greet', title: 'Greet & seat', body: 'Acknowledge guests quickly. Confirm party size and needs. Flag allergies to the server.' },
                    { id: 'foh-table', title: 'Service flow', body: 'Greet, drinks, order accuracy, check-back, clear, dessert offer, thank you.' },
                    { id: 'foh-pos', title: 'POS & payments', body: 'Ring accurately. Comps/voids only with approval. Secure payment tools.' },
                    { id: 'foh-side', title: 'Sidework', body: 'Complete opening, mid, and closing sidework before leaving.' },
                    { id: 'foh-bar', title: 'Alcohol service', body: 'ID when required. Never overserve. Follow house pour standards.' },
                    { id: 'foh-last', title: 'Recovery', body: 'Listen, apologize, solve, thank. Escalate serious issues to a manager.' }
                ],
                boh_manual: isSweet ? [
                    { id: 'boh-safety', title: 'Kitchen safety first', body: 'Slip-resistant shoes, knife safety, hot pans announced, wet floor signs, never leave the line a trip hazard. Report injuries immediately.' },
                    { id: 'boh-temps', title: 'Temps & food safety', body: 'Know fridge/freezer logs, cook-to temps, and cooling procedures. When in doubt, throw it out or ask a lead. Label & date everything.' },
                    { id: 'boh-prep', title: 'Prep standards', body: 'Follow recipes and yield notes. Batch sizes match par. First-in, first-out. Tell the lead if you 86 something mid-prep.' },
                    { id: 'boh-line', title: 'Line ticket flow', body: 'Read the whole ticket. Fire in ticket order. Communicate mods clearly. Never send incomplete plates. Expo has the final word.' },
                    { id: 'boh-clean', title: 'Cleaning & closing', body: 'Clean as you go. Complete station close checklist. Chemicals stored correctly. Floors last after equipment is safe.' },
                    { id: 'boh-team', title: 'Teamwork', body: 'Help neighboring stations when you can. Call for help early. Respect dish — the kitchen runs on clean tools.' }
                ] : [
                    { id: 'boh-safety', title: 'Safety', body: 'Knife and heat safety, clear floors, report injuries immediately.' },
                    { id: 'boh-temps', title: 'Temps & food safety', body: 'Logs, cook temps, cooling, labeling, FIFO. Ask a lead when unsure.' },
                    { id: 'boh-prep', title: 'Prep', body: 'Follow recipes and pars. Communicate 86s early.' },
                    { id: 'boh-line', title: 'Line', body: 'Read full tickets, fire in order, call mods, no incomplete plates.' },
                    { id: 'boh-clean', title: 'Closing', body: 'Clean-as-you-go and full station close. Store chemicals correctly.' },
                    { id: 'boh-team', title: 'Teamwork', body: 'Support nearby stations and dish. Ask for help early.' }
                ],
                sops: isSweet ? [
                    { id: 'sop-open', title: 'Opening the restaurant', body: '1. Disarm / unlock per house process\n2. Lights, music, HVAC as needed\n3. Walk the floor & restrooms\n4. FOH sidework start\n5. BOH equipment on & temp check\n6. Confirm reservations / large parties\n7. Pre-shift notes in Jelly Jar if needed' },
                    { id: 'sop-close', title: 'Closing the restaurant', body: '1. Last seating cutoff communicated\n2. Sidework complete FOH & BOH\n3. Cash drawer / deposit process\n4. Trash, floors, restrooms\n5. Equipment off / safe\n6. Lights, locks, alarm\n7. Manager walk-through' },
                    { id: 'sop-cash', title: 'Cash handling', body: 'Only assigned people open the drawer. Count in / count out. Comps and voids need approval. Deposits go to the bank or safe per house rule — never leave cash unsecured.' },
                    { id: 'sop-86', title: '86 & menu changes', body: 'Kitchen lead updates 86 board / POS. FOH is told before guests order. Large 86s go to shift notes or a broadcast.' },
                    { id: 'sop-incident', title: 'Incidents & guest issues', body: 'Manager on duty handles serious complaints, injuries, and emergencies. Document what happened, who was involved, and follow-up.' }
                ] : [
                    { id: 'sop-open', title: 'Opening', body: 'Unlock, systems on, walk-through, sidework, temps, reservations, pre-shift notes.' },
                    { id: 'sop-close', title: 'Closing', body: 'Cutoff, sidework, cash, cleaning, equipment, lockup, manager walk.' },
                    { id: 'sop-cash', title: 'Cash handling', body: 'Assigned access only, count in/out, approved comps/voids, secure deposits.' },
                    { id: 'sop-86', title: '86 process', body: 'Update kitchen board/POS, inform FOH, use shift notes for large 86s.' },
                    { id: 'sop-incident', title: 'Incidents', body: 'MOD handles serious issues; document and follow up.' }
                ],
                health_safety: isSweet ? [
                    { id: 'hs-wash', title: 'Handwashing & illness', body: 'Wash hands after restroom, handling raw product, cleaning, or touching face/phone. Stay home when contagious per health code and house policy. Report symptoms to a manager.' },
                    { id: 'hs-allergen', title: 'Allergens', body: 'Major allergens must be taken seriously. Never guess. Check recipes / prep area. Communicate clearly to kitchen and guest. When unsure, get a manager.' },
                    { id: 'hs-injury', title: 'Injuries & first aid', body: 'Stop the hazard if safe. First-aid kit location: ________. Report every injury to the manager on duty. Serious injury → emergency services.' },
                    { id: 'hs-fire', title: 'Fire & evacuation', body: 'Know exits and extinguisher locations. Pull station if trained; evacuate if unsafe. Meeting point: ________. Never re-enter until cleared.' },
                    { id: 'hs-chem', title: 'Chemicals', body: 'Use only labeled bottles. Never mix chemicals. Read SDS if needed. Store away from food. Rinse tools after sanitizer per product directions.' },
                    { id: 'hs-security', title: 'Security basics', body: 'Do not prop exterior doors during closed hours. Escort cash with a second person when possible. Report suspicious activity to management.' }
                ] : [
                    { id: 'hs-wash', title: 'Handwashing & illness', body: 'Wash hands at required times. Stay home when contagious. Report symptoms to a manager.' },
                    { id: 'hs-allergen', title: 'Allergens', body: 'Never guess. Verify prep and recipes. Communicate clearly. Escalate when unsure.' },
                    { id: 'hs-injury', title: 'Injuries', body: 'First-aid kit location: ________. Report all injuries. Call emergency services when needed.' },
                    { id: 'hs-fire', title: 'Fire & evacuation', body: 'Know exits and extinguishers. Evacuate if unsafe. Meeting point: ________.' },
                    { id: 'hs-chem', title: 'Chemicals', body: 'Labeled bottles only. Never mix. Store away from food. Follow product directions.' },
                    { id: 'hs-security', title: 'Security', body: 'Secure doors and cash. Report suspicious activity.' }
                ]
            };
            return (packs[DOC_TYPE] || []).map(function (s) {
                return { id: s.id, title: s.title, body: s.body, updatedAt: 0 };
            });
        }

        function emptyState() {
            return {
                mode: 'edit', // preferred view: edit | upload
                sections: defaultSections(),
                upload: null, // mirrored from server when present
                structureAt: Date.now()
            };
        }

        function normalizeSection(s) {
            return {
                id: s.id || uid(),
                title: s.title || (isSweet ? 'Section' : 'Section'),
                body: s.body || '',
                updatedAt: s.updatedAt || 0
            };
        }

        function loadLocal() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) r = emptyState();
                if (!Array.isArray(r.sections) || !r.sections.length) r.sections = defaultSections();
                else r.sections = r.sections.map(normalizeSection);
                if (r.mode !== 'upload') r.mode = 'edit';
                if (!r.structureAt) r.structureAt = Date.now();
                return r;
            } catch (e) { return emptyState(); }
        }

        var state = loadLocal();
        var applyingRemote = false;
        var sectionModal = document.getElementById('section-modal');

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = typeof msg === 'string' ? msg : (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1400);
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
            if (shared && !applyingRemote) {
                shared.push(state);
            }
            if (showToast) toast();
        }

        function applyRemote(payload) {
            if (!payload || typeof payload !== 'object') return;
            applyingRemote = true;
            try {
                if (Array.isArray(payload.sections) && payload.sections.length) {
                    state.sections = payload.sections.map(normalizeSection);
                }
                if (payload.mode === 'edit' || payload.mode === 'upload') state.mode = payload.mode;
                if (payload.upload) state.upload = payload.upload;
                if (payload.structureAt) state.structureAt = payload.structureAt;
                localStorage.setItem(KEY, JSON.stringify(state));
                paintMode();
                renderSections();
                renderUpload();
            } finally {
                applyingRemote = false;
            }
        }

        function paintMode() {
            var mode = state.mode === 'upload' ? 'upload' : 'edit';
            // Only toggle edit/upload tabs — not FOH/BOH part tabs
            document.querySelectorAll('.mode-tab[data-mode]').forEach(function (t) {
                t.classList.toggle('active', t.dataset.mode === mode);
            });
            document.getElementById('panel-edit').classList.toggle('active', mode === 'edit');
            document.getElementById('panel-upload').classList.toggle('active', mode === 'upload');
        }

        function renderSections() {
            var root = document.getElementById('sections-list');
            var list = state.sections || [];
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No sections yet — add one or reset starters ✨'
                    : 'No sections yet. Add one or reset starters.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (s, i) {
                return '<div class="section" data-id="' + esc(s.id) + '">' +
                    '<div class="section-top"><div style="flex:1;min-width:0;">' +
                    '<div class="section-title">' + esc(s.title) + '</div>' +
                    '<div class="section-body">' + esc(s.body || (isSweet ? '(empty — tap Edit)' : '(empty)')) + '</div>' +
                    '</div></div>' +
                    '<div class="section-actions">' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="up" data-need-doc="reorder" data-id="' + esc(s.id) + '"' + (i === 0 ? ' disabled' : '') + '>↑</button>' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="down" data-need-doc="reorder" data-id="' + esc(s.id) + '"' + (i === list.length - 1 ? ' disabled' : '') + '>↓</button>' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="edit" data-need-doc="edit_section" data-id="' + esc(s.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-danger btn-small" data-act="del" data-need-doc="edit_section" data-id="' + esc(s.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function formatSize(n) {
            n = Number(n) || 0;
            if (n < 1024) return n + ' B';
            if (n < 1024 * 1024) return (n / 1024).toFixed(1) + ' KB';
            return (n / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function renderUpload() {
            var root = document.getElementById('upload-status');
            var u = state.upload;
            if (!u || !u.url) {
                root.innerHTML = '<p class="hint" style="margin-top:0;">' + (isSweet
                    ? 'No file uploaded yet for this document.'
                    : 'No file uploaded yet.') + '</p>';
                return;
            }
            root.innerHTML = '<div class="file-chip">' +
                '<span class="badge">' + esc((u.ext || '').toUpperCase() || 'FILE') + '</span>' +
                '<span class="name">' + esc(u.filename || u.stored || 'Document') + '</span>' +
                '<a class="btn btn-primary btn-small" href="' + esc(u.url) + '" target="_blank" rel="noopener">' + (isSweet ? 'Open' : 'Open') + '</a>' +
                '<button type="button" class="btn btn-danger btn-small" id="delete-upload-btn">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                '<div class="file-meta">' + esc(formatSize(u.size)) +
                (u.uploadedAt ? ' · ' + new Date(u.uploadedAt).toLocaleString() : '') +
                '</div></div>';
            var del = document.getElementById('delete-upload-btn');
            if (del) {
                del.addEventListener('click', function () {
                    if (!confirm(isSweet ? 'Remove the uploaded file for the whole house?' : 'Remove uploaded file?')) return;
                    var fd = new FormData();
                    fd.append('type', DOC_TYPE);
                    fd.append('action', 'delete');
                    fetch('doc-upload-api.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (!data || !data.ok) throw new Error('fail');
                            state.upload = null;
                            save(true);
                            renderUpload();
                            toast(isSweet ? 'File removed' : 'Removed');
                        })
                        .catch(function () { toast(isSweet ? 'Could not remove — try again' : 'Remove failed'); });
                });
            }
        }

        function openSectionModal(s) {
            document.getElementById('section-modal-title').textContent = s
                ? (isSweet ? 'Edit section' : 'Edit section')
                : (isSweet ? 'Add section' : 'Add section');
            document.getElementById('s-id').value = s ? s.id : '';
            document.getElementById('s-title').value = s ? (s.title || '') : '';
            document.getElementById('s-body').value = s ? (s.body || '') : '';
            sectionModal.classList.add('show');
            setTimeout(function () { document.getElementById('s-title').focus(); }, 40);
        }

        document.querySelectorAll('.mode-tab[data-mode]').forEach(function (tab) {
            tab.addEventListener('click', function () {
                state.mode = tab.dataset.mode === 'upload' ? 'upload' : 'edit';
                paintMode();
                save(false);
            });
        });

        document.getElementById('add-section-btn').addEventListener('click', function () { openSectionModal(null); });
        document.getElementById('s-cancel').addEventListener('click', function () { sectionModal.classList.remove('show'); });
        sectionModal.addEventListener('click', function (e) { if (e.target === sectionModal) sectionModal.classList.remove('show'); });

        document.getElementById('section-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('s-id').value;
            var data = {
                id: id || uid(),
                title: document.getElementById('s-title').value.trim(),
                body: document.getElementById('s-body').value,
                updatedAt: Date.now()
            };
            if (!data.title) return;
            if (id) {
                var idx = state.sections.findIndex(function (x) { return x.id === id; });
                if (idx !== -1) state.sections[idx] = normalizeSection(data);
                else state.sections.push(normalizeSection(data));
            } else {
                state.sections.push(normalizeSection(data));
            }
            sectionModal.classList.remove('show');
            save(true);
            renderSections();
        });

        document.getElementById('sections-list').addEventListener('click', function (e) {
            var need = e.target.closest('[data-need-doc]');
            if (need) {
                var suf = need.getAttribute('data-need-doc');
                if (suf && !canP(docKey(suf))) return;
            }

            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            var act = btn.dataset.act;
            var idx = state.sections.findIndex(function (x) { return x.id === id; });
            if (idx === -1) return;
            if (act === 'edit') {
                openSectionModal(state.sections[idx]);
                return;
            }
            if (act === 'del') {
                if (!confirm(isSweet ? 'Remove this section?' : 'Remove this section?')) return;
                state.sections.splice(idx, 1);
                save(true);
                renderSections();
                return;
            }
            if (act === 'up' && idx > 0) {
                var t = state.sections[idx - 1];
                state.sections[idx - 1] = state.sections[idx];
                state.sections[idx] = t;
                save(false);
                renderSections();
                return;
            }
            if (act === 'down' && idx < state.sections.length - 1) {
                var t2 = state.sections[idx + 1];
                state.sections[idx + 1] = state.sections[idx];
                state.sections[idx] = t2;
                save(false);
                renderSections();
            }
        });

        document.getElementById('reset-btn').addEventListener('click', function () {
            if (!confirm(isSweet
                ? 'Reset all sections to the starter guide? Your custom edits will be replaced (upload file stays).'
                : 'Reset sections to starter content? Custom edits will be replaced.')) return;
            state.sections = defaultSections();
            save(true);
            renderSections();
            toast(isSweet ? 'Starters restored ✨' : 'Starters restored');
        });

        document.getElementById('print-btn').addEventListener('click', function () {
            var w = window.open('', '_blank');
            if (!w) return;
            var html = '<html><head><title>' + esc(DOC_TITLE) + '</title><style>body{font-family:Georgia,serif;padding:24px;max-width:720px;margin:0 auto;color:#222;} h1{font-size:22pt;} h2{font-size:14pt;margin:22px 0 8px;border-bottom:1px solid #ccc;padding-bottom:4px;} p{white-space:pre-wrap;line-height:1.45;font-size:11pt;}</style></head><body>';
            html += '<h1>' + esc(DOC_TITLE) + '</h1>';
            (state.sections || []).forEach(function (s) {
                html += '<h2>' + esc(s.title) + '</h2><p>' + esc(s.body || '') + '</p>';
            });
            html += '</body></html>';
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(function () { w.print(); }, 250);
        });

        document.getElementById('upload-btn').addEventListener('click', function () {
            var input = document.getElementById('file-input');
            if (!input.files || !input.files[0]) {
                toast(isSweet ? 'Pick a file first 📎' : 'Choose a file first');
                return;
            }
            var fd = new FormData();
            fd.append('type', DOC_TYPE);
            fd.append('action', 'upload');
            fd.append('file', input.files[0]);
            var btn = document.getElementById('upload-btn');
            btn.disabled = true;
            btn.textContent = isSweet ? 'Uploading…' : 'Uploading…';
            fetch('doc-upload-api.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (pack) {
                    if (!pack.d || !pack.d.ok) {
                        var err = (pack.d && pack.d.error) || 'fail';
                        if (err === 'too_large') throw new Error(isSweet ? 'File too big (max 10 MB)' : 'File too large');
                        if (err === 'bad_ext') throw new Error(isSweet ? 'Use PDF or Word only' : 'PDF or Word only');
                        throw new Error(isSweet ? 'Upload failed' : 'Upload failed');
                    }
                    state.upload = pack.d.upload;
                    state.mode = 'upload';
                    save(true);
                    paintMode();
                    renderUpload();
                    input.value = '';
                    toast(isSweet ? 'Uploaded for the house ✨' : 'Uploaded');
                })
                .catch(function (err) {
                    toast(err && err.message ? err.message : (isSweet ? 'Upload failed' : 'Upload failed'));
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.textContent = isSweet ? 'Upload for the house ✨' : 'Upload';
                });
        });

        document.getElementById('save-note').addEventListener('click', function () {
            save(true);
        });

        function refreshUploadFromServer() {
            return fetch('doc-upload-api.php?type=' + encodeURIComponent(DOC_TYPE) + '&_=' + Date.now(), {
                credentials: 'same-origin', cache: 'no-store'
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data && data.ok) {
                    state.upload = data.upload || null;
                    localStorage.setItem(KEY, JSON.stringify(state));
                    renderUpload();
                }
            }).catch(function () { /* offline ok */ });
        }

        function initShared() {
            if (shared && typeof shared.stopPolling === 'function') {
                try { shared.stopPolling(); } catch (e) {}
            }
            shared = null;
            if (!window.PbjSharedState) {
                setSyncPill({ kind: 'offline', text: isSweet ? 'Local only' : 'Local only' });
                return;
            }
            shared = new PbjSharedState({
                key: SHARED_KEY,
                pollMs: 6000,
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
        }

        function switchTrainingPart(part) {
            if (!IS_TRAINING) return;
            var nextType = part === 'boh' ? 'boh_manual' : 'foh_manual';
            if (nextType === DOC_TYPE) return;
            // Persist current side before switching
            save(false);
            DOC_TYPE = nextType;
            refreshKeys();
            state = loadLocal();
            paintMode();
            renderSections();
            renderUpload();
            refreshUploadFromServer();
            initShared();
            document.querySelectorAll('#part-tabs .mode-tab').forEach(function (t) {
                t.classList.toggle('active', t.getAttribute('data-part') === part);
            });
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('type', 'training_manuals');
                url.searchParams.set('part', part);
                window.history.replaceState({}, '', url.pathname + '?' + url.searchParams.toString());
            } catch (e) {}
        }

        if (IS_TRAINING) {
            document.querySelectorAll('#part-tabs .mode-tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    switchTrainingPart(tab.getAttribute('data-part') === 'boh' ? 'boh' : 'foh');
                });
            });
        }

        paintMode();
        renderSections();
        renderUpload();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyDocPerms);
            document.addEventListener('pbj-perms-ready', applyDocPerms);
        refreshUploadFromServer();
        initShared();
    })();
    </script>
</body>
</html>
