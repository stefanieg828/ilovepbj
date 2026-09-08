<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>

    <title><?php echo $is_sweet ? 'Compliance & Certifications' : 'Compliance & Certifications'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.15rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 780px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.92; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 14px; }
        @media (max-width: 560px) { .stats-row { grid-template-columns: 1fr 1fr; } }
        .stat { background: white; border-radius: 16px; padding: 12px 8px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.45rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .num.warn { color: #C9A227; }
        .stat .num.bad { color: #B71C1C; }
        .stat .num.good { color: #1F6B4A; }
        .stat .lbl { font-size: 0.78rem; opacity: 0.7; margin-top: 4px; line-height: 1.25; }
        .tabs { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
        .tab { flex: 1; min-width: 70px; border: none; border-radius: 14px; padding: 12px 8px; font-size: 0.9rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); background: white; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .panel { display: none; } .panel.active { display: block; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 12px; }
        .card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 8px; flex-wrap: wrap; }
        .card-head h2 { margin: 0; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 70px; resize: vertical; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
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
        .item { border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-radius: 0 14px 14px 0; padding: 14px 16px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .item.expired { border-left-color: #B71C1C; background: #FDECEA; }
        .item.due { border-left-color: #C9A227; background: #FFF8E8; }
        .item.missing { border-left-color: #5B8DEF; <?php if ($is_sweet): ?>background: #F5F8FF;<?php else: ?>background: #EEF2F8;<?php endif; ?> }
        .item.ok { border-left-color: #2E9B63; }
        .item-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .item-title { font-size: 1.08rem; margin: 0 0 4px; }
        .item-meta { font-size: 0.92rem; opacity: 0.85; line-height: 1.4; margin-bottom: 8px; }
        .item-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.78rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; margin: 0 4px 4px 0; }
        .badge.bad { background: #FDECEA; color: #B71C1C; }
        .badge.warn { background: #FFF3E0; color: #8A5A12; }
        .badge.good { background: #E8F8F1; color: #1F6B4A; }
        .badge.miss { background: #EAF1FA; color: #1A2A44; }
        .empty { text-align: center; padding: 22px; opacity: 0.75; line-height: 1.4; }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .search { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; margin-bottom: 12px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white;<?php else: ?>font-family: 'Lora', serif; background: white;<?php endif; ?> }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 540px; max-height: 90vh; overflow-y: auto; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.45rem; margin: 0 0 14px; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .check-row { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .check-row:last-child { border-bottom: none; }
        .check-row input { width: 18px; height: 18px; margin-top: 2px; }
        .check-row label { flex: 1; cursor: pointer; line-height: 1.35; }
        .person-card { border-radius: 14px; padding: 14px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .person-card h3 { margin: 0 0 6px; font-size: 1.1rem; }
        .mini { font-size: 0.88rem; opacity: 0.8; line-height: 1.4; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .linkish { color: inherit; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
        <h1><?php echo $is_sweet ? 'Compliance & Certifications' : 'Compliance & Certifications'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Certs, expirations & who signed what' : 'Certs, expirations, and document sign-offs'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div class="intro">
            <?php echo $is_sweet
                ? 'Track food handler cards, alcohol certs, allergy training, and handbook / manual sign-offs. Names pull from <a href="/admin/roster">Crew roster</a>. Handbooks & Training Manuals live under <a href="/admin/team">Team & Roles</a>; SOPs & safety under <a href="/admin/ops">Restaurant Operations</a> 💕'
                : 'Track certifications and document acknowledgements. People come from the <a href="/admin/roster">team roster</a>. Handbooks and SOPs are under Team and Operations.'; ?>
        </div>

        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-active">—</div><div class="lbl"><?php echo $is_sweet ? 'Active crew' : 'Active crew'; ?></div></div>
            <div class="stat"><div class="num bad" id="stat-expired">—</div><div class="lbl"><?php echo $is_sweet ? 'Expired certs' : 'Expired'; ?></div></div>
            <div class="stat"><div class="num warn" id="stat-due">—</div><div class="lbl"><?php echo $is_sweet ? 'Due in 30 days' : 'Due in 30 days'; ?></div></div>
            <div class="stat"><div class="num" id="stat-missing">—</div><div class="lbl"><?php echo $is_sweet ? 'Missing required' : 'Missing required'; ?></div></div>
        </div>

        <div class="tabs">
            <button type="button" class="tab active" data-tab="board"><?php echo $is_sweet ? '📋 Board' : 'Board'; ?></button>
            <button type="button" class="tab" data-tab="certs"><?php echo $is_sweet ? '🪪 Certs' : 'Certs'; ?></button>
            <button type="button" class="tab" data-tab="signoffs"><?php echo $is_sweet ? '✍️ Sign-offs' : 'Sign-offs'; ?></button>
            <button type="button" class="tab" data-tab="people"><?php echo $is_sweet ? '👥 By person' : 'By person'; ?></button>
            <button type="button" class="tab" data-tab="setup"><?php echo $is_sweet ? '⚙️ Setup' : 'Setup'; ?></button>
        </div>

        <!-- BOARD -->
        <div class="panel active" id="panel-board">
            <div class="card">
                <div class="card-head">
                    <h2><?php echo $is_sweet ? 'Attention list' : 'Attention list'; ?></h2>
                </div>
                <p class="hint"><?php echo $is_sweet
                    ? 'Expired, expiring soon, and required items still open — your morning compliance glance.'
                    : 'Expired, due soon, and missing required items.'; ?></p>
                <div class="filters" id="board-filters">
                    <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
                    <button type="button" class="chip" data-filter="expired"><?php echo $is_sweet ? 'Expired' : 'Expired'; ?></button>
                    <button type="button" class="chip" data-filter="due"><?php echo $is_sweet ? 'Due soon' : 'Due soon'; ?></button>
                    <button type="button" class="chip" data-filter="missing"><?php echo $is_sweet ? 'Missing' : 'Missing'; ?></button>
                </div>
                <div id="board-list"></div>
            </div>
            <div class="toolbar">
                <button type="button" class="btn btn-primary btn-small" id="quick-cert-btn"><?php echo $is_sweet ? '+ Log certification' : '+ Log cert'; ?></button>
                <button type="button" class="btn btn-secondary btn-small" id="quick-sign-btn"><?php echo $is_sweet ? '+ Record sign-off' : '+ Sign-off'; ?></button>
                <button type="button" class="btn btn-ghost btn-small" id="print-btn"><?php echo $is_sweet ? '🖨️ Print snapshot' : 'Print'; ?></button>
            </div>
        </div>

        <!-- CERTS -->
        <div class="panel" id="panel-certs">
            <div class="card">
                <div class="card-head">
                    <h2><?php echo $is_sweet ? 'Certifications log' : 'Certifications'; ?></h2>
                </div>
                <p class="hint"><?php echo $is_sweet
                    ? 'Food handler, alcohol, ServSafe, allergy training — whatever your house needs.'
                    : 'Log issued and expiration dates for house certifications.'; ?></p>
                <input type="search" class="search" id="cert-search" placeholder="<?php echo $is_sweet ? 'Search person or cert…' : 'Search…'; ?>">
                <div class="filters" id="cert-filters">
                    <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
                    <button type="button" class="chip" data-filter="expired"><?php echo $is_sweet ? 'Expired' : 'Expired'; ?></button>
                    <button type="button" class="chip" data-filter="due"><?php echo $is_sweet ? 'Due soon' : 'Due soon'; ?></button>
                    <button type="button" class="chip" data-filter="ok"><?php echo $is_sweet ? 'Current' : 'Current'; ?></button>
                </div>
                <div id="certs-list"></div>
                <button type="button" class="btn btn-secondary btn-add" id="add-cert-btn" data-perm="admin.compliance.add_cert"><?php echo $is_sweet ? '+ Log certification' : '+ Add certification'; ?></button>
            </div>
        </div>

        <!-- SIGNOFFS -->
        <div class="panel" id="panel-signoffs">
            <div class="card">
                <div class="card-head">
                    <h2><?php echo $is_sweet ? 'Document sign-offs' : 'Document sign-offs'; ?></h2>
                </div>
                <p class="hint"><?php echo $is_sweet
                    ? 'Record who acknowledged the handbook, FOH/BOH manuals, SOPs, or health & safety. Open the docs from Team & Operations anytime.'
                    : 'Track acknowledgements for handbooks, manuals, SOPs, and health & safety.'; ?></p>
                <div class="toolbar" style="margin-bottom:12px;">
                    <a href="/admin/docs?type=handbook" class="btn btn-ghost btn-small"><?php echo $is_sweet ? '📘 Handbook' : 'Handbook'; ?></a>
                    <a href="/admin/docs?type=training_manuals" class="btn btn-ghost btn-small"><?php echo $is_sweet ? '📗 Training Manuals' : 'Training Manuals'; ?></a>
                    <a href="/admin/docs?type=sops" class="btn btn-ghost btn-small"><?php echo $is_sweet ? '📋 SOPs' : 'SOPs'; ?></a>
                    <a href="/admin/docs?type=health_safety" class="btn btn-ghost btn-small"><?php echo $is_sweet ? '🛟 Safety' : 'Safety'; ?></a>
                </div>
                <input type="search" class="search" id="sign-search" placeholder="<?php echo $is_sweet ? 'Search person or document…' : 'Search…'; ?>">
                <div class="filters" id="sign-filters">
                    <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All records' : 'All'; ?></button>
                    <button type="button" class="chip" data-filter="missing"><?php echo $is_sweet ? 'Still needed' : 'Missing'; ?></button>
                    <button type="button" class="chip" data-filter="done"><?php echo $is_sweet ? 'Signed' : 'Signed'; ?></button>
                </div>
                <div id="signoffs-list"></div>
                <button type="button" class="btn btn-secondary btn-add" id="add-sign-btn" data-perm="admin.compliance.signoff"><?php echo $is_sweet ? '+ Record sign-off' : '+ Record sign-off'; ?></button>
            </div>
        </div>

        <!-- BY PERSON -->
        <div class="panel" id="panel-people">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Compliance by teammate' : 'By teammate'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'One glance per person — required certs + required sign-offs.'
                    : 'Per-person status for required certs and sign-offs.'; ?></p>
                <input type="search" class="search" id="people-search" placeholder="<?php echo $is_sweet ? 'Search roster…' : 'Search…'; ?>">
                <div id="people-list"></div>
            </div>
        </div>

        <!-- SETUP -->
        <div class="panel" id="panel-setup">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Certification types' : 'Certification types'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'What the house tracks. Mark required for the attention board. Default months auto-fills expiration when you log a cert.'
                    : 'Types you track. Required items show on the attention board. Default months help set expiry.'; ?></p>
                <div id="types-list"></div>
                <button type="button" class="btn btn-secondary btn-add" id="add-type-btn"><?php echo $is_sweet ? '+ Add cert type' : '+ Add type'; ?></button>
            </div>
            <div class="card">
                <h2><?php echo $is_sweet ? 'Required sign-off documents' : 'Required sign-off documents'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'Which house docs every active teammate should acknowledge. Toggle required on/off.'
                    : 'Documents that active teammates should acknowledge.'; ?></p>
                <div id="docs-list"></div>
                <button type="button" class="btn btn-secondary btn-add" id="add-doc-btn"><?php echo $is_sweet ? '+ Add custom document' : '+ Add document'; ?></button>
            </div>
            <div class="card">
                <h2><?php echo $is_sweet ? 'Alert window' : 'Alert window'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'How many days ahead counts as “due soon.”' : 'Days ahead for “due soon.”'; ?></p>
                <div class="field-row">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Days before expiry' : 'Days before expiry'; ?></label>
                        <input type="number" id="warn-days" min="7" max="180" step="1">
                    </div>
                </div>
                <button type="button" class="btn btn-primary" id="save-warn-btn" style="width:100%;"><?php echo $is_sweet ? 'Save alert window ✨' : 'Save'; ?></button>
            </div>
        </div>

        <div class="actions-bar">
            <a href="/admin/roster" class="btn btn-secondary"><?php echo $is_sweet ? '👥 Roster' : 'Roster'; ?></a>
            <a href="/admin/team" class="btn btn-secondary"><?php echo $is_sweet ? 'Handbooks' : 'Team hub'; ?></a>
            <a href="/admin" class="btn btn-primary"><?php echo pbj_hub_label('admin'); ?></a>
        </div>
    </div>

    <!-- Cert modal -->
    <div class="modal-backdrop" id="cert-modal">
        <div class="modal">
            <h2 id="cert-modal-title"><?php echo $is_sweet ? 'Log certification' : 'Log certification'; ?></h2>
            <form id="cert-form">
                <input type="hidden" id="c-id">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Teammate' : 'Teammate'; ?></label>
                    <select id="c-person" required></select>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Certification type' : 'Type'; ?></label>
                    <select id="c-type" required></select>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Issued on' : 'Issued on'; ?></label><input type="date" id="c-issued"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Expires on' : 'Expires on'; ?></label><input type="date" id="c-expires"></div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes / ID #' : 'Notes / ID #'; ?></label><input id="c-notes" placeholder="<?php echo $is_sweet ? 'Card #, provider, reminder…' : 'Card number, provider…'; ?>"></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="c-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sign-off modal -->
    <div class="modal-backdrop" id="sign-modal">
        <div class="modal">
            <h2 id="sign-modal-title"><?php echo $is_sweet ? 'Record sign-off' : 'Record sign-off'; ?></h2>
            <form id="sign-form">
                <input type="hidden" id="so-id">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Teammate' : 'Teammate'; ?></label>
                    <select id="so-person" required></select>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Document' : 'Document'; ?></label>
                    <select id="so-doc" required></select>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Signed on' : 'Signed on'; ?></label><input type="date" id="so-date" required></div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><input id="so-notes" placeholder="<?php echo $is_sweet ? 'In person, emailed, paper copy…' : 'Method, witness…'; ?>"></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="so-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Type modal -->
    <div class="modal-backdrop" id="type-modal">
        <div class="modal">
            <h2 id="type-modal-title"><?php echo $is_sweet ? 'Cert type' : 'Cert type'; ?></h2>
            <form id="type-form">
                <input type="hidden" id="t-id">
                <div class="field"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="t-name" required placeholder="<?php echo $is_sweet ? 'e.g. Food Handler Card' : 'e.g. Food Handler'; ?>"></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Default valid (months)' : 'Default months'; ?></label><input type="number" id="t-months" min="0" max="120" step="1" placeholder="12"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Applies to' : 'Applies to'; ?></label>
                        <select id="t-applies">
                            <option value="all"><?php echo $is_sweet ? 'Everyone' : 'Everyone'; ?></option>
                            <option value="foh"><?php echo $is_sweet ? 'FOH mainly' : 'FOH'; ?></option>
                            <option value="boh"><?php echo $is_sweet ? 'BOH mainly' : 'BOH'; ?></option>
                            <option value="manager"><?php echo $is_sweet ? 'Managers / leads' : 'Managers'; ?></option>
                        </select>
                    </div>
                </div>
                <div class="check-row">
                    <input type="checkbox" id="t-required">
                    <label for="t-required"><?php echo $is_sweet ? 'Required for attention board (missing shows as gap)' : 'Required — missing shows on board'; ?></label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="t-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Doc modal -->
    <div class="modal-backdrop" id="doc-modal">
        <div class="modal">
            <h2 id="doc-modal-title"><?php echo $is_sweet ? 'Sign-off document' : 'Sign-off document'; ?></h2>
            <form id="doc-form">
                <input type="hidden" id="d-id">
                <div class="field"><label><?php echo $is_sweet ? 'Label' : 'Label'; ?></label><input id="d-label" required placeholder="<?php echo $is_sweet ? 'e.g. Employee Handbook' : 'Document name'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Link (optional)' : 'Link (optional)'; ?></label>
                    <select id="d-link">
                        <option value=""><?php echo $is_sweet ? 'No deep link' : 'None'; ?></option>
                        <option value="handbook"><?php echo $is_sweet ? 'Employee Handbook' : 'Handbook'; ?></option>
                        <option value="foh_manual"><?php echo $is_sweet ? 'FOH Training Manual' : 'FOH Manual'; ?></option>
                        <option value="boh_manual"><?php echo $is_sweet ? 'BOH Training Manual' : 'BOH Manual'; ?></option>
                        <option value="sops"><?php echo $is_sweet ? 'SOPs' : 'SOPs'; ?></option>
                        <option value="health_safety"><?php echo $is_sweet ? 'Health & Safety' : 'Health & Safety'; ?></option>
                    </select>
                </div>
                <div class="check-row">
                    <input type="checkbox" id="d-required" checked>
                    <label for="d-required"><?php echo $is_sweet ? 'Required for every active teammate' : 'Required for active teammates'; ?></label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="d-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
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
        var KEY = 'pbj_admin_compliance_v1';
        var SHARED_KEY = 'admin_compliance_v1';
        var TEAM_KEY = 'pbj_admin_team_v2';
        var TEAM_KEY_OLD = 'pbj_admin_team_v1';
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyCompPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var view = canP('admin.compliance.view') || canP('admin.compliance.edit');
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('comp-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="comp-denied">No permission to view compliance.</div>');
                    }
                }
                var map = {
                    'add-cert-btn': 'admin.compliance.add_cert',
                    'add-sign-btn': 'admin.compliance.signoff',
                    'save-alert-btn': 'admin.compliance.alerts'
                };
                Object.keys(map).forEach(function(id){
                    var el = document.getElementById(id);
                    if (el) el.style.display = canP(map[id]) ? '' : 'none';
                });
                document.querySelectorAll('[data-act^="del-"]').forEach(function(el){
                    el.style.display = canP('admin.compliance.setup') || canP('admin.compliance.edit') ? '' : 'none';
                });
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        var WARN_DEFAULT = 30;

        var FOH_ROLES = { Server: 1, Host: 1, Bartender: 1, 'Shift Lead': 1, Manager: 1, GM: 1, Owner: 1 };
        var BOH_ROLES = { 'BOH Cook': 1, Prep: 1, Dish: 1, 'Shift Lead': 1, Manager: 1, GM: 1, Owner: 1 };
        var MGR_ROLES = { Owner: 1, GM: 1, Manager: 1, 'Shift Lead': 1 };

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function parseDate(s) {
            if (!s) return null;
            var p = String(s).split('-');
            if (p.length !== 3) return null;
            var dt = new Date(+p[0], +p[1] - 1, +p[2]);
            return isNaN(dt.getTime()) ? null : dt;
        }
        function addMonths(dateStr, months) {
            var dt = parseDate(dateStr);
            if (!dt || !months) return '';
            dt.setMonth(dt.getMonth() + (+months || 0));
            return dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
        }
        function daysUntil(dateStr) {
            var dt = parseDate(dateStr);
            if (!dt) return null;
            var now = new Date();
            now.setHours(0, 0, 0, 0);
            dt.setHours(0, 0, 0, 0);
            return Math.round((dt - now) / 86400000);
        }
        function formatDate(s) {
            var dt = parseDate(s);
            if (!dt) return '—';
            return dt.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function defaultCertTypes() {
            return [
                { id: 'ct-food', name: isSweet ? 'Food Handler Card' : 'Food Handler Card', defaultMonths: 36, required: true, appliesTo: 'all' },
                { id: 'ct-alcohol', name: isSweet ? 'Alcohol / TIPS / RBS' : 'Alcohol service cert', defaultMonths: 36, required: false, appliesTo: 'foh' },
                { id: 'ct-allergy', name: isSweet ? 'Allergen awareness' : 'Allergen awareness', defaultMonths: 12, required: true, appliesTo: 'all' },
                { id: 'ct-servsafe', name: isSweet ? 'ServSafe / Manager food safety' : 'Manager food safety', defaultMonths: 60, required: false, appliesTo: 'manager' },
                { id: 'ct-cpr', name: isSweet ? 'CPR / First Aid' : 'CPR / First Aid', defaultMonths: 24, required: false, appliesTo: 'manager' }
            ];
        }

        function defaultSignDocs() {
            return [
                { id: 'sd-handbook', label: isSweet ? 'Employee Handbook' : 'Employee Handbook', link: 'handbook', required: true },
                { id: 'sd-foh', label: isSweet ? 'FOH Training Manual' : 'FOH Training Manual', link: 'foh_manual', required: false },
                { id: 'sd-boh', label: isSweet ? 'BOH Training Manual' : 'BOH Training Manual', link: 'boh_manual', required: false },
                { id: 'sd-sops', label: isSweet ? 'SOPs acknowledgement' : 'SOPs acknowledgement', link: 'sops', required: true },
                { id: 'sd-health', label: isSweet ? 'Health & Safety' : 'Health & Safety', link: 'health_safety', required: true }
            ];
        }

        function emptyState() {
            return {
                certTypes: defaultCertTypes(),
                certs: [],
                signDocs: defaultSignDocs(),
                signoffs: [],
                warnDays: WARN_DEFAULT,
                structureAt: Date.now()
            };
        }

        function loadTeam() {
            try {
                var r = JSON.parse(localStorage.getItem(TEAM_KEY) || 'null');
                if (!r) r = JSON.parse(localStorage.getItem(TEAM_KEY_OLD) || 'null');
                if (!r || !Array.isArray(r.people)) return [];
                return r.people.filter(function (p) {
                    return p && p.id && p.active !== false && !p.inactive;
                }).map(function (p) {
                    var roles = [];
                    if (Array.isArray(p.roles)) {
                        p.roles.forEach(function (x) {
                            if (x && typeof x === 'object') roles.push(String(x.role || x.name || '').trim());
                            else roles.push(String(x || '').trim());
                        });
                    }
                    if (p.role && roles.indexOf(p.role) === -1) roles.push(String(p.role));
                    return {
                        id: String(p.id),
                        name: p.name || p.displayName || 'Teammate',
                        roles: roles.filter(Boolean),
                        role: p.role || (roles[0] || 'Other')
                    };
                }).sort(function (a, b) { return a.name.localeCompare(b.name); });
            } catch (e) { return []; }
        }

        function personBucket(person) {
            var roles = (person && person.roles) || [];
            if (!roles.length && person && person.role) roles = [person.role];
            var foh = false, boh = false, mgr = false;
            roles.forEach(function (r) {
                if (FOH_ROLES[r]) foh = true;
                if (BOH_ROLES[r]) boh = true;
                if (MGR_ROLES[r]) mgr = true;
            });
            return { foh: foh, boh: boh, mgr: mgr };
        }

        function typeApplies(type, person) {
            if (!type || type.appliesTo === 'all' || !type.appliesTo) return true;
            var b = personBucket(person);
            if (type.appliesTo === 'foh') return b.foh || b.mgr;
            if (type.appliesTo === 'boh') return b.boh || b.mgr;
            if (type.appliesTo === 'manager') return b.mgr;
            return true;
        }

        function normalizeType(t) {
            return {
                id: t.id || uid(),
                name: t.name || (isSweet ? 'Certification' : 'Certification'),
                defaultMonths: t.defaultMonths != null && t.defaultMonths !== '' ? (+t.defaultMonths || 0) : 12,
                required: !!t.required,
                appliesTo: ['all', 'foh', 'boh', 'manager'].indexOf(t.appliesTo) >= 0 ? t.appliesTo : 'all'
            };
        }

        function normalizeCert(c) {
            return {
                id: c.id || uid(),
                personId: c.personId || '',
                personName: c.personName || '',
                typeId: c.typeId || '',
                issuedOn: c.issuedOn || '',
                expiresOn: c.expiresOn || '',
                notes: c.notes || '',
                updatedAt: c.updatedAt || 0
            };
        }

        function normalizeDoc(d) {
            return {
                id: d.id || uid(),
                label: d.label || (isSweet ? 'Document' : 'Document'),
                link: d.link || '',
                required: d.required !== false
            };
        }

        function normalizeSign(s) {
            return {
                id: s.id || uid(),
                personId: s.personId || '',
                personName: s.personName || '',
                docId: s.docId || '',
                signedOn: s.signedOn || '',
                notes: s.notes || '',
                updatedAt: s.updatedAt || 0
            };
        }

        function loadLocal() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) r = emptyState();
                if (!Array.isArray(r.certTypes) || !r.certTypes.length) r.certTypes = defaultCertTypes();
                else r.certTypes = r.certTypes.map(normalizeType);
                if (!Array.isArray(r.certs)) r.certs = [];
                else r.certs = r.certs.map(normalizeCert);
                if (!Array.isArray(r.signDocs) || !r.signDocs.length) r.signDocs = defaultSignDocs();
                else r.signDocs = r.signDocs.map(normalizeDoc);
                if (!Array.isArray(r.signoffs)) r.signoffs = [];
                else r.signoffs = r.signoffs.map(normalizeSign);
                r.warnDays = Math.min(180, Math.max(7, +r.warnDays || WARN_DEFAULT));
                if (!r.structureAt) r.structureAt = Date.now();
                return r;
            } catch (e) { return emptyState(); }
        }

        var state = loadLocal();
        var team = loadTeam();
        var shared = null;
        var applyingRemote = false;
        var boardFilter = 'all';
        var certFilter = 'all';
        var signFilter = 'all';
        var certSearch = '';
        var signSearch = '';
        var peopleSearch = '';

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = typeof msg === 'string' ? msg : (isSweet ? 'Saved 💾' : 'Saved');
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1300);
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
            if (shared && !applyingRemote) shared.push(state);
            if (showToast) toast();
            renderAll();
        }

        function applyRemote(payload) {
            if (!payload || typeof payload !== 'object') return;
            applyingRemote = true;
            try {
                if (Array.isArray(payload.certTypes) && payload.certTypes.length) state.certTypes = payload.certTypes.map(normalizeType);
                if (Array.isArray(payload.certs)) state.certs = payload.certs.map(normalizeCert);
                if (Array.isArray(payload.signDocs) && payload.signDocs.length) state.signDocs = payload.signDocs.map(normalizeDoc);
                if (Array.isArray(payload.signoffs)) state.signoffs = payload.signoffs.map(normalizeSign);
                if (payload.warnDays != null) state.warnDays = Math.min(180, Math.max(7, +payload.warnDays || WARN_DEFAULT));
                if (payload.structureAt) state.structureAt = payload.structureAt;
                localStorage.setItem(KEY, JSON.stringify(state));
                renderAll();
            } finally {
                applyingRemote = false;
            }
        }

        function typeById(id) {
            return state.certTypes.find(function (t) { return t.id === id; });
        }
        function docById(id) {
            return state.signDocs.find(function (d) { return d.id === id; });
        }
        function personById(id) {
            return team.find(function (p) { return p.id === id; });
        }

        function certStatus(cert) {
            var days = daysUntil(cert.expiresOn);
            if (days == null) return { key: 'ok', label: isSweet ? 'No expiry' : 'No expiry', cls: 'ok' };
            if (days < 0) return { key: 'expired', label: isSweet ? 'Expired' : 'Expired', cls: 'expired' };
            if (days <= (state.warnDays || WARN_DEFAULT)) return { key: 'due', label: isSweet ? ('Due in ' + days + 'd') : ('Due in ' + days + 'd'), cls: 'due' };
            return { key: 'ok', label: isSweet ? 'Current' : 'Current', cls: 'ok' };
        }

        function latestCert(personId, typeId) {
            var list = state.certs.filter(function (c) { return c.personId === personId && c.typeId === typeId; });
            if (!list.length) return null;
            list.sort(function (a, b) {
                return String(b.expiresOn || b.issuedOn || '').localeCompare(String(a.expiresOn || a.issuedOn || ''));
            });
            return list[0];
        }

        function latestSign(personId, docId) {
            var list = state.signoffs.filter(function (s) { return s.personId === personId && s.docId === docId; });
            if (!list.length) return null;
            list.sort(function (a, b) { return String(b.signedOn || '').localeCompare(String(a.signedOn || '')); });
            return list[0];
        }

        function buildAttention() {
            var items = [];
            var warn = state.warnDays || WARN_DEFAULT;

            // cert statuses
            state.certs.forEach(function (c) {
                var st = certStatus(c);
                if (st.key === 'expired' || st.key === 'due') {
                    var t = typeById(c.typeId);
                    items.push({
                        kind: st.key,
                        sort: st.key === 'expired' ? 0 : 1,
                        personId: c.personId,
                        title: (c.personName || 'Teammate') + ' · ' + ((t && t.name) || 'Cert'),
                        meta: (st.key === 'expired'
                            ? (isSweet ? 'Expired ' : 'Expired ')
                            : (isSweet ? 'Expires ' : 'Expires ')) + formatDate(c.expiresOn),
                        certId: c.id,
                        type: 'cert'
                    });
                }
            });

            // missing required certs
            team.forEach(function (p) {
                state.certTypes.forEach(function (t) {
                    if (!t.required || !typeApplies(t, p)) return;
                    var latest = latestCert(p.id, t.id);
                    if (!latest) {
                        items.push({
                            kind: 'missing',
                            sort: 2,
                            personId: p.id,
                            title: p.name + ' · ' + t.name,
                            meta: isSweet ? 'Required cert not logged yet' : 'Required cert missing',
                            typeId: t.id,
                            type: 'missing_cert'
                        });
                    } else {
                        var st = certStatus(latest);
                        if (st.key === 'expired') {
                            // already covered by cert loop, but if name differs ok
                        }
                    }
                });
            });

            // missing required sign-offs
            team.forEach(function (p) {
                state.signDocs.forEach(function (d) {
                    if (!d.required) return;
                    var s = latestSign(p.id, d.id);
                    if (!s) {
                        items.push({
                            kind: 'missing',
                            sort: 3,
                            personId: p.id,
                            title: p.name + ' · ' + d.label,
                            meta: isSweet ? 'Sign-off still needed' : 'Sign-off missing',
                            docId: d.id,
                            type: 'missing_sign'
                        });
                    }
                });
            });

            items.sort(function (a, b) {
                if (a.sort !== b.sort) return a.sort - b.sort;
                return String(a.title).localeCompare(String(b.title));
            });
            return items;
        }

        function computeStats() {
            var expired = 0, due = 0;
            state.certs.forEach(function (c) {
                var st = certStatus(c);
                if (st.key === 'expired') expired++;
                if (st.key === 'due') due++;
            });
            var missing = 0;
            team.forEach(function (p) {
                state.certTypes.forEach(function (t) {
                    if (t.required && typeApplies(t, p) && !latestCert(p.id, t.id)) missing++;
                });
                state.signDocs.forEach(function (d) {
                    if (d.required && !latestSign(p.id, d.id)) missing++;
                });
            });
            return { active: team.length, expired: expired, due: due, missing: missing };
        }

        function fillPersonSelect(sel, selected) {
            var opts = team.map(function (p) {
                return '<option value="' + esc(p.id) + '"' + (selected === p.id ? ' selected' : '') + '>' + esc(p.name) + (p.role ? ' · ' + esc(p.role) : '') + '</option>';
            }).join('');
            if (!team.length) {
                sel.innerHTML = '<option value="">' + (isSweet ? 'Add people in Crew roster first' : 'No active teammates') + '</option>';
            } else {
                sel.innerHTML = '<option value="">' + (isSweet ? 'Choose teammate…' : 'Choose…') + '</option>' + opts;
            }
        }

        function fillTypeSelect(sel, selected) {
            sel.innerHTML = state.certTypes.map(function (t) {
                return '<option value="' + esc(t.id) + '"' + (selected === t.id ? ' selected' : '') + '>' + esc(t.name) + (t.required ? ' ★' : '') + '</option>';
            }).join('');
        }

        function fillDocSelect(sel, selected) {
            sel.innerHTML = state.signDocs.map(function (d) {
                return '<option value="' + esc(d.id) + '"' + (selected === d.id ? ' selected' : '') + '>' + esc(d.label) + (d.required ? ' ★' : '') + '</option>';
            }).join('');
        }

        function renderStats() {
            var s = computeStats();
            document.getElementById('stat-active').textContent = s.active;
            document.getElementById('stat-expired').textContent = s.expired;
            document.getElementById('stat-due').textContent = s.due;
            var missEl = document.getElementById('stat-missing');
            missEl.textContent = s.missing;
            missEl.className = 'num' + (s.missing ? ' warn' : ' good');
        }

        function renderBoard() {
            var root = document.getElementById('board-list');
            var items = buildAttention().filter(function (it) {
                if (boardFilter === 'all') return true;
                return it.kind === boardFilter;
            });
            if (!team.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'Add active teammates in <a class="linkish" href="/admin/roster">Crew roster</a> to track compliance 💕'
                    : 'Add active teammates in the roster to track compliance.') + '</div>';
                return;
            }
            if (!items.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'All clear on this filter — your house papers look tidy ✨'
                    : 'Nothing on this filter. Looking good.') + '</div>';
                return;
            }
            root.innerHTML = items.map(function (it) {
                var badge = it.kind === 'expired' ? '<span class="badge bad">' + (isSweet ? 'Expired' : 'Expired') + '</span>'
                    : it.kind === 'due' ? '<span class="badge warn">' + (isSweet ? 'Due soon' : 'Due soon') + '</span>'
                    : '<span class="badge miss">' + (isSweet ? 'Missing' : 'Missing') + '</span>';
                var actions = '';
                if (it.type === 'cert' && it.certId) {
                    actions = '<button type="button" class="btn btn-ghost btn-small" data-act="edit-cert" data-id="' + esc(it.certId) + '">' + (isSweet ? 'Update' : 'Update') + '</button>';
                } else if (it.type === 'missing_cert') {
                    actions = '<button type="button" class="btn btn-primary btn-small" data-act="add-cert-pre" data-person="' + esc(it.personId) + '" data-type="' + esc(it.typeId) + '">' + (isSweet ? 'Log cert' : 'Log') + '</button>';
                } else if (it.type === 'missing_sign') {
                    actions = '<button type="button" class="btn btn-primary btn-small" data-act="add-sign-pre" data-person="' + esc(it.personId) + '" data-doc="' + esc(it.docId) + '">' + (isSweet ? 'Record sign-off' : 'Sign-off') + '</button>';
                }
                return '<div class="item ' + esc(it.kind) + '"><div class="item-top"><div style="flex:1;min-width:0;">' +
                    '<div class="item-title">' + badge + ' ' + esc(it.title) + '</div>' +
                    '<div class="item-meta">' + esc(it.meta) + '</div></div></div>' +
                    '<div class="item-actions">' + actions + '</div></div>';
            }).join('');
        }

        function renderCerts() {
            var root = document.getElementById('certs-list');
            var q = certSearch.toLowerCase();
            var list = state.certs.slice().sort(function (a, b) {
                return String(a.personName || '').localeCompare(String(b.personName || '')) || String(a.expiresOn || '').localeCompare(String(b.expiresOn || ''));
            }).filter(function (c) {
                var st = certStatus(c);
                if (certFilter !== 'all' && st.key !== certFilter) return false;
                if (!q) return true;
                var t = typeById(c.typeId);
                var hay = ((c.personName || '') + ' ' + ((t && t.name) || '') + ' ' + (c.notes || '')).toLowerCase();
                return hay.indexOf(q) !== -1;
            });
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'No certifications match — log one below 🪪' : 'No certifications match.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (c) {
                var t = typeById(c.typeId);
                var st = certStatus(c);
                var badge = st.key === 'expired' ? '<span class="badge bad">' + esc(st.label) + '</span>'
                    : st.key === 'due' ? '<span class="badge warn">' + esc(st.label) + '</span>'
                    : '<span class="badge good">' + esc(st.label) + '</span>';
                return '<div class="item ' + esc(st.cls) + '"><div class="item-top"><div style="flex:1;min-width:0;">' +
                    '<div class="item-title">' + esc(c.personName || 'Teammate') + '</div>' +
                    '<div class="item-meta">' + badge + ' <strong>' + esc((t && t.name) || 'Cert') + '</strong><br>' +
                    (isSweet ? 'Issued ' : 'Issued ') + formatDate(c.issuedOn) + ' · ' +
                    (isSweet ? 'Expires ' : 'Expires ') + formatDate(c.expiresOn) +
                    (c.notes ? '<br>' + esc(c.notes) : '') +
                    '</div></div></div>' +
                    '<div class="item-actions">' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="edit-cert" data-id="' + esc(c.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-danger btn-small" data-act="del-cert" data-id="' + esc(c.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function renderSignoffs() {
            var root = document.getElementById('signoffs-list');
            var q = signSearch.toLowerCase();
            var rows = [];

            if (signFilter === 'done' || signFilter === 'all') {
                state.signoffs.forEach(function (s) {
                    var d = docById(s.docId);
                    rows.push({
                        kind: 'done',
                        personName: s.personName,
                        label: (d && d.label) || 'Document',
                        meta: (isSweet ? 'Signed ' : 'Signed ') + formatDate(s.signedOn) + (s.notes ? ' · ' + s.notes : ''),
                        id: s.id,
                        sortName: s.personName || ''
                    });
                });
            }
            if (signFilter === 'missing' || signFilter === 'all') {
                team.forEach(function (p) {
                    state.signDocs.forEach(function (d) {
                        if (!d.required && signFilter === 'missing') {
                            // still show missing optional? only required for missing filter
                        }
                        if (signFilter === 'missing' && !d.required) return;
                        if (!latestSign(p.id, d.id)) {
                            // for "all", only show missing required to reduce noise
                            if (signFilter === 'all' && !d.required) return;
                            rows.push({
                                kind: 'missing',
                                personName: p.name,
                                personId: p.id,
                                docId: d.id,
                                label: d.label,
                                meta: d.required
                                    ? (isSweet ? 'Required — not signed yet' : 'Required — not signed')
                                    : (isSweet ? 'Optional — not signed yet' : 'Not signed'),
                                sortName: p.name
                            });
                        }
                    });
                });
            }

            rows = rows.filter(function (r) {
                if (!q) return true;
                return ((r.personName || '') + ' ' + (r.label || '') + ' ' + (r.meta || '')).toLowerCase().indexOf(q) !== -1;
            }).sort(function (a, b) {
                if (a.kind !== b.kind) return a.kind === 'missing' ? -1 : 1;
                return String(a.sortName).localeCompare(String(b.sortName));
            });

            if (!team.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'Need a roster first 💕' : 'Add teammates first.') + '</div>';
                return;
            }
            if (!rows.length) {
                root.innerHTML = '<div class="empty">' + (isSweet ? 'Nothing here for this filter ✨' : 'Nothing for this filter.') + '</div>';
                return;
            }

            root.innerHTML = rows.map(function (r) {
                if (r.kind === 'done') {
                    return '<div class="item ok"><div class="item-top"><div style="flex:1;min-width:0;">' +
                        '<div class="item-title">' + esc(r.personName) + '</div>' +
                        '<div class="item-meta"><span class="badge good">' + (isSweet ? 'Signed' : 'Signed') + '</span> ' + esc(r.label) + '<br>' + esc(r.meta) + '</div></div></div>' +
                        '<div class="item-actions">' +
                        '<button type="button" class="btn btn-ghost btn-small" data-act="edit-sign" data-id="' + esc(r.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                        '<button type="button" class="btn btn-danger btn-small" data-act="del-sign" data-id="' + esc(r.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                        '</div></div>';
                }
                return '<div class="item missing"><div class="item-top"><div style="flex:1;min-width:0;">' +
                    '<div class="item-title">' + esc(r.personName) + '</div>' +
                    '<div class="item-meta"><span class="badge miss">' + (isSweet ? 'Needed' : 'Needed') + '</span> ' + esc(r.label) + '<br>' + esc(r.meta) + '</div></div></div>' +
                    '<div class="item-actions">' +
                    '<button type="button" class="btn btn-primary btn-small" data-act="add-sign-pre" data-person="' + esc(r.personId) + '" data-doc="' + esc(r.docId) + '">' + (isSweet ? 'Record' : 'Record') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function renderPeople() {
            var root = document.getElementById('people-list');
            var q = peopleSearch.toLowerCase();
            var list = team.filter(function (p) {
                return !q || p.name.toLowerCase().indexOf(q) !== -1;
            });
            if (!list.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No teammates yet — <a class="linkish" href="/admin/roster">build your crew</a> first.'
                    : 'No teammates on the roster yet.') + '</div>';
                return;
            }
            root.innerHTML = list.map(function (p) {
                var certLines = state.certTypes.filter(function (t) { return typeApplies(t, p); }).map(function (t) {
                    var latest = latestCert(p.id, t.id);
                    if (!latest) {
                        return '<div class="mini">' + (t.required ? '★ ' : '') + esc(t.name) + ': <span class="badge miss">' + (isSweet ? 'Missing' : 'Missing') + '</span></div>';
                    }
                    var st = certStatus(latest);
                    var b = st.key === 'expired' ? 'bad' : st.key === 'due' ? 'warn' : 'good';
                    return '<div class="mini">' + (t.required ? '★ ' : '') + esc(t.name) + ': <span class="badge ' + b + '">' + esc(st.label) + '</span> ' + formatDate(latest.expiresOn) + '</div>';
                }).join('');
                var signLines = state.signDocs.map(function (d) {
                    var s = latestSign(p.id, d.id);
                    if (!s) {
                        return '<div class="mini">' + (d.required ? '★ ' : '') + esc(d.label) + ': <span class="badge miss">' + (isSweet ? 'Unsigned' : 'Unsigned') + '</span></div>';
                    }
                    return '<div class="mini">' + (d.required ? '★ ' : '') + esc(d.label) + ': <span class="badge good">' + formatDate(s.signedOn) + '</span></div>';
                }).join('');
                return '<div class="person-card"><h3>' + esc(p.name) + '</h3>' +
                    '<div class="mini" style="margin-bottom:8px;opacity:0.7;">' + esc((p.roles && p.roles.join(', ')) || p.role || '') + '</div>' +
                    '<strong class="mini">' + (isSweet ? 'Certs' : 'Certs') + '</strong>' + certLines +
                    '<strong class="mini" style="display:block;margin-top:8px;">' + (isSweet ? 'Sign-offs' : 'Sign-offs') + '</strong>' + signLines +
                    '<div class="item-actions" style="margin-top:10px;">' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="add-cert-pre" data-person="' + esc(p.id) + '">' + (isSweet ? '+ Cert' : '+ Cert') + '</button>' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="add-sign-pre" data-person="' + esc(p.id) + '">' + (isSweet ? '+ Sign-off' : '+ Sign-off') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function renderSetup() {
            var typesRoot = document.getElementById('types-list');
            typesRoot.innerHTML = state.certTypes.map(function (t) {
                var applies = t.appliesTo === 'foh' ? (isSweet ? 'FOH' : 'FOH')
                    : t.appliesTo === 'boh' ? (isSweet ? 'BOH' : 'BOH')
                    : t.appliesTo === 'manager' ? (isSweet ? 'Managers' : 'Managers')
                    : (isSweet ? 'Everyone' : 'Everyone');
                return '<div class="item"><div class="item-top"><div style="flex:1;min-width:0;">' +
                    '<div class="item-title">' + esc(t.name) + (t.required ? ' <span class="badge warn">★ ' + (isSweet ? 'Required' : 'Required') + '</span>' : '') + '</div>' +
                    '<div class="item-meta">' + esc(applies) + ' · ' + (t.defaultMonths ? (t.defaultMonths + (isSweet ? ' mo default' : ' mo default')) : (isSweet ? 'No default expiry' : 'No default expiry')) + '</div>' +
                    '</div></div><div class="item-actions">' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="edit-type" data-id="' + esc(t.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-danger btn-small" data-act="del-type" data-id="' + esc(t.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('') || '<div class="empty">' + (isSweet ? 'No types yet' : 'No types yet') + '</div>';

            var docsRoot = document.getElementById('docs-list');
            docsRoot.innerHTML = state.signDocs.map(function (d) {
                var link = d.link ? (' · <a class="linkish" href="/admin/docs?type=' + encodeURIComponent(d.link) + '">' + (isSweet ? 'Open doc' : 'Open') + '</a>') : '';
                return '<div class="item"><div class="item-top"><div style="flex:1;min-width:0;">' +
                    '<div class="item-title">' + esc(d.label) + (d.required ? ' <span class="badge warn">★ ' + (isSweet ? 'Required' : 'Required') + '</span>' : '') + '</div>' +
                    '<div class="item-meta">' + (isSweet ? 'Sign-off document' : 'Sign-off document') + link + '</div>' +
                    '</div></div><div class="item-actions">' +
                    '<button type="button" class="btn btn-ghost btn-small" data-act="edit-doc" data-id="' + esc(d.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-danger btn-small" data-act="del-doc" data-id="' + esc(d.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');

            document.getElementById('warn-days').value = state.warnDays || WARN_DEFAULT;
        }

        function renderAll() {
            team = loadTeam();
            renderStats();
            renderBoard();
            renderCerts();
            renderSignoffs();
            renderPeople();
            renderSetup();
        }

        // Tabs
        document.querySelectorAll('.tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
                document.querySelectorAll('.panel').forEach(function (p) { p.classList.remove('active'); });
                tab.classList.add('active');
                document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
            });
        });

        function wireFilters(id, setter) {
            document.getElementById(id).addEventListener('click', function (e) {
                var chip = e.target.closest('.chip'); if (!chip) return;
                document.querySelectorAll('#' + id + ' .chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
                setter(chip.dataset.filter);
            });
        }
        wireFilters('board-filters', function (f) { boardFilter = f; renderBoard(); });
        wireFilters('cert-filters', function (f) { certFilter = f; renderCerts(); });
        wireFilters('sign-filters', function (f) { signFilter = f; renderSignoffs(); });

        document.getElementById('cert-search').addEventListener('input', function () { certSearch = this.value.trim(); renderCerts(); });
        document.getElementById('sign-search').addEventListener('input', function () { signSearch = this.value.trim(); renderSignoffs(); });
        document.getElementById('people-search').addEventListener('input', function () { peopleSearch = this.value.trim(); renderPeople(); });

        // Modals: cert
        var certModal = document.getElementById('cert-modal');
        function openCertModal(cert, pre) {
            pre = pre || {};
            document.getElementById('cert-modal-title').textContent = cert
                ? (isSweet ? 'Edit certification' : 'Edit certification')
                : (isSweet ? 'Log certification' : 'Log certification');
            document.getElementById('c-id').value = cert ? cert.id : '';
            fillPersonSelect(document.getElementById('c-person'), cert ? cert.personId : (pre.personId || ''));
            fillTypeSelect(document.getElementById('c-type'), cert ? cert.typeId : (pre.typeId || (state.certTypes[0] && state.certTypes[0].id)));
            document.getElementById('c-issued').value = cert ? (cert.issuedOn || '') : todayStr();
            document.getElementById('c-expires').value = cert ? (cert.expiresOn || '') : '';
            document.getElementById('c-notes').value = cert ? (cert.notes || '') : '';
            if (!cert) maybeAutofillExpiry();
            certModal.classList.add('show');
        }
        function maybeAutofillExpiry() {
            var typeId = document.getElementById('c-type').value;
            var issued = document.getElementById('c-issued').value;
            var t = typeById(typeId);
            if (t && t.defaultMonths && issued && !document.getElementById('c-expires').value) {
                document.getElementById('c-expires').value = addMonths(issued, t.defaultMonths);
            }
        }
        document.getElementById('c-type').addEventListener('change', function () {
            document.getElementById('c-expires').value = '';
            maybeAutofillExpiry();
        });
        document.getElementById('c-issued').addEventListener('change', maybeAutofillExpiry);
        document.getElementById('c-cancel').addEventListener('click', function () { certModal.classList.remove('show'); });
        certModal.addEventListener('click', function (e) { if (e.target === certModal) certModal.classList.remove('show'); });
        document.getElementById('cert-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('c-id').value;
            var personId = document.getElementById('c-person').value;
            var person = personById(personId);
            if (!personId || !person) {
                toast(isSweet ? 'Pick a teammate from the roster' : 'Pick a teammate');
                return;
            }
            var data = normalizeCert({
                id: id || uid(),
                personId: personId,
                personName: person.name,
                typeId: document.getElementById('c-type').value,
                issuedOn: document.getElementById('c-issued').value,
                expiresOn: document.getElementById('c-expires').value,
                notes: document.getElementById('c-notes').value.trim(),
                updatedAt: Date.now()
            });
            if (id) {
                var idx = state.certs.findIndex(function (x) { return x.id === id; });
                if (idx !== -1) state.certs[idx] = data;
                else state.certs.push(data);
            } else {
                state.certs.push(data);
            }
            certModal.classList.remove('show');
            save(true);
        });

        // Sign modal
        var signModal = document.getElementById('sign-modal');
        function openSignModal(sign, pre) {
            pre = pre || {};
            document.getElementById('sign-modal-title').textContent = sign
                ? (isSweet ? 'Edit sign-off' : 'Edit sign-off')
                : (isSweet ? 'Record sign-off' : 'Record sign-off');
            document.getElementById('so-id').value = sign ? sign.id : '';
            fillPersonSelect(document.getElementById('so-person'), sign ? sign.personId : (pre.personId || ''));
            fillDocSelect(document.getElementById('so-doc'), sign ? sign.docId : (pre.docId || (state.signDocs[0] && state.signDocs[0].id)));
            document.getElementById('so-date').value = sign ? (sign.signedOn || '') : todayStr();
            document.getElementById('so-notes').value = sign ? (sign.notes || '') : '';
            signModal.classList.add('show');
        }
        document.getElementById('so-cancel').addEventListener('click', function () { signModal.classList.remove('show'); });
        signModal.addEventListener('click', function (e) { if (e.target === signModal) signModal.classList.remove('show'); });
        document.getElementById('sign-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('so-id').value;
            var personId = document.getElementById('so-person').value;
            var person = personById(personId);
            if (!personId || !person) {
                toast(isSweet ? 'Pick a teammate from the roster' : 'Pick a teammate');
                return;
            }
            var data = normalizeSign({
                id: id || uid(),
                personId: personId,
                personName: person.name,
                docId: document.getElementById('so-doc').value,
                signedOn: document.getElementById('so-date').value,
                notes: document.getElementById('so-notes').value.trim(),
                updatedAt: Date.now()
            });
            // replace existing same person+doc if new
            if (!id) {
                state.signoffs = state.signoffs.filter(function (s) {
                    return !(s.personId === data.personId && s.docId === data.docId);
                });
                state.signoffs.push(data);
            } else {
                var idx = state.signoffs.findIndex(function (x) { return x.id === id; });
                if (idx !== -1) state.signoffs[idx] = data;
                else state.signoffs.push(data);
            }
            signModal.classList.remove('show');
            save(true);
        });

        // Type modal
        var typeModal = document.getElementById('type-modal');
        function openTypeModal(t) {
            document.getElementById('type-modal-title').textContent = t
                ? (isSweet ? 'Edit cert type' : 'Edit type')
                : (isSweet ? 'Add cert type' : 'Add type');
            document.getElementById('t-id').value = t ? t.id : '';
            document.getElementById('t-name').value = t ? t.name : '';
            document.getElementById('t-months').value = t ? (t.defaultMonths || '') : '12';
            document.getElementById('t-applies').value = t ? (t.appliesTo || 'all') : 'all';
            document.getElementById('t-required').checked = t ? !!t.required : false;
            typeModal.classList.add('show');
        }
        document.getElementById('t-cancel').addEventListener('click', function () { typeModal.classList.remove('show'); });
        typeModal.addEventListener('click', function (e) { if (e.target === typeModal) typeModal.classList.remove('show'); });
        document.getElementById('type-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('t-id').value;
            var data = normalizeType({
                id: id || uid(),
                name: document.getElementById('t-name').value.trim(),
                defaultMonths: document.getElementById('t-months').value,
                appliesTo: document.getElementById('t-applies').value,
                required: document.getElementById('t-required').checked
            });
            if (!data.name) return;
            if (id) {
                var idx = state.certTypes.findIndex(function (x) { return x.id === id; });
                if (idx !== -1) state.certTypes[idx] = data;
                else state.certTypes.push(data);
            } else state.certTypes.push(data);
            typeModal.classList.remove('show');
            save(true);
        });

        // Doc modal
        var docModal = document.getElementById('doc-modal');
        function openDocModal(d) {
            document.getElementById('doc-modal-title').textContent = d
                ? (isSweet ? 'Edit document' : 'Edit document')
                : (isSweet ? 'Add document' : 'Add document');
            document.getElementById('d-id').value = d ? d.id : '';
            document.getElementById('d-label').value = d ? d.label : '';
            document.getElementById('d-link').value = d ? (d.link || '') : '';
            document.getElementById('d-required').checked = d ? d.required !== false : true;
            docModal.classList.add('show');
        }
        document.getElementById('d-cancel').addEventListener('click', function () { docModal.classList.remove('show'); });
        docModal.addEventListener('click', function (e) { if (e.target === docModal) docModal.classList.remove('show'); });
        document.getElementById('doc-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('d-id').value;
            var data = normalizeDoc({
                id: id || uid(),
                label: document.getElementById('d-label').value.trim(),
                link: document.getElementById('d-link').value,
                required: document.getElementById('d-required').checked
            });
            if (!data.label) return;
            if (id) {
                var idx = state.signDocs.findIndex(function (x) { return x.id === id; });
                if (idx !== -1) state.signDocs[idx] = data;
                else state.signDocs.push(data);
            } else state.signDocs.push(data);
            docModal.classList.remove('show');
            save(true);
        });

        // Buttons
        document.getElementById('add-cert-btn').addEventListener('click', function () { openCertModal(null); });
        document.getElementById('quick-cert-btn').addEventListener('click', function () { openCertModal(null); });
        document.getElementById('add-sign-btn').addEventListener('click', function () { openSignModal(null); });
        document.getElementById('quick-sign-btn').addEventListener('click', function () { openSignModal(null); });
        document.getElementById('add-type-btn').addEventListener('click', function () { openTypeModal(null); });
        document.getElementById('add-doc-btn').addEventListener('click', function () { openDocModal(null); });
        document.getElementById('save-warn-btn').addEventListener('click', function () {
            state.warnDays = Math.min(180, Math.max(7, +document.getElementById('warn-days').value || WARN_DEFAULT));
            save(true);
        });

        document.getElementById('print-btn').addEventListener('click', function () {
            var s = computeStats();
            var attention = buildAttention();
            var w = window.open('', '_blank');
            if (!w) return;
            var html = '<html><head><title>Compliance</title><style>body{font-family:Georgia,serif;padding:24px;max-width:760px;margin:0 auto;} h1{font-size:20pt;} h2{font-size:13pt;margin-top:22px;} table{width:100%;border-collapse:collapse;font-size:10pt;} td,th{border-bottom:1px solid #ccc;padding:6px;text-align:left;vertical-align:top;} .bad{color:#B71C1C;} .warn{color:#8A5A12;}</style></head><body>';
            html += '<h1>' + esc(isSweet ? 'Compliance snapshot' : 'Compliance snapshot') + '</h1>';
            html += '<p>' + esc(todayStr()) + ' · ' + s.active + ' active · ' + s.expired + ' expired · ' + s.due + ' due · ' + s.missing + ' missing</p>';
            html += '<h2>' + esc(isSweet ? 'Attention' : 'Attention') + '</h2><table><tr><th>Item</th><th>Detail</th></tr>';
            if (!attention.length) html += '<tr><td colspan="2">' + esc(isSweet ? 'All clear' : 'All clear') + '</td></tr>';
            attention.forEach(function (it) {
                html += '<tr><td>' + esc(it.title) + '</td><td class="' + (it.kind === 'expired' ? 'bad' : it.kind === 'due' ? 'warn' : '') + '">' + esc(it.meta) + '</td></tr>';
            });
            html += '</table><h2>' + esc(isSweet ? 'All certs' : 'All certs') + '</h2><table><tr><th>Person</th><th>Type</th><th>Expires</th><th>Status</th></tr>';
            state.certs.forEach(function (c) {
                var t = typeById(c.typeId);
                var st = certStatus(c);
                html += '<tr><td>' + esc(c.personName) + '</td><td>' + esc((t && t.name) || '') + '</td><td>' + esc(formatDate(c.expiresOn)) + '</td><td>' + esc(st.label) + '</td></tr>';
            });
            html += '</table><h2>' + esc(isSweet ? 'Sign-offs' : 'Sign-offs') + '</h2><table><tr><th>Person</th><th>Document</th><th>Signed</th></tr>';
            state.signoffs.forEach(function (s) {
                var d = docById(s.docId);
                html += '<tr><td>' + esc(s.personName) + '</td><td>' + esc((d && d.label) || '') + '</td><td>' + esc(formatDate(s.signedOn)) + '</td></tr>';
            });
            html += '</table></body></html>';
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(function () { w.print(); }, 250);
        });

        // Delegated clicks
        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var act = btn.dataset.act;
            var id = btn.dataset.id;

            if (act === 'edit-cert') {
                var c = state.certs.find(function (x) { return x.id === id; });
                if (c) openCertModal(c);
                return;
            }
            if (act === 'del-cert') {
                if (!confirm(isSweet ? 'Remove this certification record?' : 'Remove this certification?')) return;
                state.certs = state.certs.filter(function (x) { return x.id !== id; });
                save(true);
                return;
            }
            if (act === 'add-cert-pre') {
                openCertModal(null, { personId: btn.dataset.person || '', typeId: btn.dataset.type || '' });
                return;
            }
            if (act === 'edit-sign') {
                var s = state.signoffs.find(function (x) { return x.id === id; });
                if (s) openSignModal(s);
                return;
            }
            if (act === 'del-sign') {
                if (!confirm(isSweet ? 'Remove this sign-off record?' : 'Remove this sign-off?')) return;
                state.signoffs = state.signoffs.filter(function (x) { return x.id !== id; });
                save(true);
                return;
            }
            if (act === 'add-sign-pre') {
                openSignModal(null, { personId: btn.dataset.person || '', docId: btn.dataset.doc || '' });
                return;
            }
            if (act === 'edit-type') {
                var t = typeById(id);
                if (t) openTypeModal(t);
                return;
            }
            if (act === 'del-type') {
                if (!confirm(isSweet ? 'Remove this cert type? Existing logs stay but won’t match a type.' : 'Remove this type?')) return;
                state.certTypes = state.certTypes.filter(function (x) { return x.id !== id; });
                save(true);
                return;
            }
            if (act === 'edit-doc') {
                var d = docById(id);
                if (d) openDocModal(d);
                return;
            }
            if (act === 'del-doc') {
                if (!confirm(isSweet ? 'Remove this sign-off document from the list?' : 'Remove this document?')) return;
                state.signDocs = state.signDocs.filter(function (x) { return x.id !== id; });
                save(true);
            }
        });

        // Refresh team when returning to tab
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') renderAll();
        });

        renderAll();

        if (window.PbjSharedState) {
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
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only' : 'Local only' });
        }
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyCompPerms);
            document.addEventListener('pbj-perms-ready', applyCompPerms);
})();
    </script>
</body>
</html>
