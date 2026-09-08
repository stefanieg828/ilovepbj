<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

// Default layout — tweak later to match the real room
$tables = [
    // Section A — window / front
    ['id' => '1',  'seats' => 2, 'section' => 'A', 'x' => 8,  'y' => 10, 'w' => 18, 'h' => 16],
    ['id' => '2',  'seats' => 2, 'section' => 'A', 'x' => 30, 'y' => 10, 'w' => 18, 'h' => 16],
    ['id' => '3',  'seats' => 4, 'section' => 'A', 'x' => 52, 'y' => 8,  'w' => 20, 'h' => 20],
    ['id' => '4',  'seats' => 4, 'section' => 'A', 'x' => 76, 'y' => 8,  'w' => 20, 'h' => 20],
    // Section B — center
    ['id' => '5',  'seats' => 4, 'section' => 'B', 'x' => 12, 'y' => 38, 'w' => 20, 'h' => 20],
    ['id' => '6',  'seats' => 4, 'section' => 'B', 'x' => 40, 'y' => 38, 'w' => 20, 'h' => 20],
    ['id' => '7',  'seats' => 6, 'section' => 'B', 'x' => 68, 'y' => 36, 'w' => 24, 'h' => 24],
    // Section C — back / patio side
    ['id' => '8',  'seats' => 2, 'section' => 'C', 'x' => 10, 'y' => 70, 'w' => 16, 'h' => 16],
    ['id' => '9',  'seats' => 2, 'section' => 'C', 'x' => 32, 'y' => 70, 'w' => 16, 'h' => 16],
    ['id' => '10', 'seats' => 4, 'section' => 'C', 'x' => 54, 'y' => 68, 'w' => 20, 'h' => 20],
    ['id' => '11', 'seats' => 8, 'section' => 'C', 'x' => 78, 'y' => 66, 'w' => 18, 'h' => 26],
];

$sections = [
    'A' => $is_sweet ? 'Section A · Windows' : 'Section A · Windows',
    'B' => $is_sweet ? 'Section B · Center' : 'Section B · Center',
    'C' => $is_sweet ? 'Section C · Back' : 'Section C · Back',
];

$statuses = $is_sweet
    ? [
        'open'     => 'Open',
        'seated'   => 'Seated',
        'ordered'  => 'Ordered',
        'check'    => 'Check Dropped',
        'dirty'    => 'Needs Bus',
        'reserved' => 'Reserved',
    ]
    : [
        'open'     => 'Open',
        'seated'   => 'Seated',
        'ordered'  => 'Ordered',
        'check'    => 'Check Dropped',
        'dirty'    => 'Needs Bus',
        'reserved' => 'Reserved',
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Floor Plan & Tables' : 'Floor Plan & Tables'; ?> • <?php echo pbj_hub_label('foh'); ?> • ilovepbj ops</title>

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
            font-size: 2.5rem;
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
            max-width: 900px;
            margin: 0 auto;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .tab {
            flex: 1;
            border: none;
            border-radius: 14px;
            padding: 14px 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
                background: white;
                color: #3a2f1f;
                box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            <?php else: ?>
                font-family: 'Lora', serif;
                background: white;
                color: #1A2A44;
                box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            <?php endif; ?>
        }

        .tab.active {
            <?php if ($is_sweet): ?>
                background: #E55163;
                color: white;
            <?php else: ?>
                background: #1A2A44;
                color: white;
            <?php endif; ?>
        }

        .panel { display: none; }
        .panel.active { display: block; }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            background: white;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-open     { background: #7BC67E; }
        .status-seated   { background: #5B9BD5; }
        .status-ordered  { background: #9B7EDE; }
        .status-check    { background: #E8A838; }
        .status-dirty    { background: #E57373; }
        .status-reserved { background: #90A4AE; }

        .floor-wrap {
            background: white;
            border-radius: 20px;
            padding: 14px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 16px;
            overflow-x: auto;
        }

        .floor {
            position: relative;
            width: 100%;
            min-width: 320px;
            aspect-ratio: 1.15 / 1;
            border-radius: 16px;
            <?php if ($is_sweet): ?>
                background:
                    linear-gradient(90deg, rgba(229,81,99,0.06) 1px, transparent 1px),
                    linear-gradient(rgba(229,81,99,0.06) 1px, transparent 1px),
                    #FFF9F4;
                background-size: 24px 24px, 24px 24px, auto;
                border: 2px dashed #F3C5CC;
            <?php else: ?>
                background:
                    linear-gradient(90deg, rgba(26,42,68,0.06) 1px, transparent 1px),
                    linear-gradient(rgba(26,42,68,0.06) 1px, transparent 1px),
                    #F7F4F0;
                background-size: 24px 24px, 24px 24px, auto;
                border: 2px dashed #C5D0DE;
            <?php endif; ?>
        }

        .room-label {
            position: absolute;
            font-size: 0.75rem;
            opacity: 0.45;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .room-label.entrance {
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
        }

        .room-label.kitchen {
            top: 6px;
            right: 10px;
        }

        .section-zone {
            position: absolute;
            border: 2px dashed rgba(0,0,0,0.28);
            border-radius: 16px;
            pointer-events: none;
            z-index: 0;
            box-sizing: border-box;
        }
        .section-zone.color-0 { border-color: <?php echo $is_sweet ? 'rgba(229,81,99,0.55)' : 'rgba(26,42,68,0.45)'; ?>; background: <?php echo $is_sweet ? 'rgba(229,81,99,0.06)' : 'rgba(26,42,68,0.05)'; ?>; }
        .section-zone.color-1 { border-color: rgba(91,155,213,0.65); background: rgba(91,155,213,0.08); }
        .section-zone.color-2 { border-color: rgba(155,126,222,0.65); background: rgba(155,126,222,0.08); }
        .section-zone.color-3 { border-color: rgba(46,155,99,0.65); background: rgba(46,155,99,0.08); }
        .section-zone.color-4 { border-color: rgba(232,168,56,0.7); background: rgba(232,168,56,0.1); }
        .section-zone.color-5 { border-color: rgba(229,115,115,0.65); background: rgba(229,115,115,0.08); }
        .sec-actions { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
        .sec-actions .btn-tiny { padding: 6px 10px; font-size: 0.82rem; border-radius: 10px; border: none; cursor: pointer; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; color: inherit; }
        .sec-actions .btn-tiny.danger { background: #FDECEA; color: #B71C1C; }
        .section-zone-label {
            position: absolute;
            top: 4px;
            left: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.7;
            pointer-events: none;
        }
        .table {
            position: absolute;
            border-radius: 12px;
            border: 2px solid rgba(0,0,0,0.12);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            color: #1a1a1a;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            user-select: none;
            z-index: 2;
        }

        .table:hover, .table.selected {
            transform: scale(1.06);
            box-shadow: 0 6px 16px rgba(0,0,0,0.18);
            z-index: 5;
        }

        .table.selected {
            outline: 3px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
            outline-offset: 2px;
        }

        .table-num {
            font-weight: 600;
            font-size: 1.05rem;
            line-height: 1;
        }

        .table-meta {
            font-size: 0.72rem;
            opacity: 0.85;
            margin-top: 3px;
        }

        .detail-card, .assign-card, .turns-card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 14px;
        }

        .detail-card h2, .assign-card h2, .turns-card h2, .section-title {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 1.5rem;
            margin: 0 0 12px;
        }

        .detail-empty {
            opacity: 0.7;
            margin: 0;
            font-size: 1.05rem;
            line-height: 1.4;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 16px;
            margin-bottom: 14px;
        }

        .detail-grid .label {
            font-size: 0.85rem;
            opacity: 0.65;
            display: block;
            margin-bottom: 2px;
        }

        .detail-grid .value {
            font-size: 1.1rem;
        }

        .status-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .status-btn {
            border: 2px solid transparent;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 0.9rem;
            cursor: pointer;
            background: #f3f3f3;
            color: #222;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>
                font-family: 'Lora', serif;
            <?php endif; ?>
        }

        .status-btn.active {
            border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
            font-weight: 600;
        }

        .field-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }

        .field {
            flex: 1;
            min-width: 120px;
        }

        .field label {
            display: block;
            font-size: 0.85rem;
            opacity: 0.65;
            margin-bottom: 4px;
        }

        .field input, .field select, .notes-input {
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

        .field input:focus, .field select:focus, .notes-input:focus {
            outline: none;
            border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
        }

        .notes-input {
            min-height: 70px;
            resize: vertical;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .stat {
            background: white;
            border-radius: 16px;
            padding: 14px 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .stat .num {
            font-size: 1.6rem;
            line-height: 1.1;
            <?php if ($is_sweet): ?>
                color: #E55163;
            <?php else: ?>
                color: #1A2A44;
            <?php endif; ?>
        }

        .stat .lbl {
            font-size: 0.85rem;
            opacity: 0.7;
            margin-top: 4px;
        }

        .assign-row, .turn-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>;
        }

        .assign-row:last-child, .turn-row:last-child {
            border-bottom: none;
        }

        .section-badge {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            <?php if ($is_sweet): ?>
                background: #E55163;
            <?php else: ?>
                background: #1A2A44;
            <?php endif; ?>
        }

        .assign-info, .turn-info {
            flex: 1;
            min-width: 0;
        }

        .assign-info .name, .turn-info .name {
            font-size: 1.1rem;
            margin-bottom: 2px;
        }

        .assign-info .sub, .turn-info .sub {
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .turn-timer {
            text-align: right;
            min-width: 88px;
        }

        .turn-timer .mins {
            font-size: 1.25rem;
        }

        .turn-timer .mins.over {
            color: #C62828;
            font-weight: 600;
        }

        .turn-timer .goal {
            font-size: 0.8rem;
            opacity: 0.65;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            min-width: 120px;
            border: none;
            border-radius: 14px;
            padding: 14px 12px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
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


        .floor.edit-mode .table { outline: 2px dashed rgba(0,0,0,0.25); cursor: move; }
        .floor.edit-mode .table.dragging { opacity: 0.85; z-index: 5; }
        .table .del-table { display: none; position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%; border: none; background: #B71C1C; color: #fff; font-size: 12px; line-height: 22px; padding: 0; cursor: pointer; }
        .floor.edit-mode .table .del-table { display: block; }
        .layout-toolbar .btn { border: none; border-radius: 12px; padding: 10px 12px; font-size: 0.95rem; cursor: pointer; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .perm-list { max-height: 220px; overflow: auto; margin: 10px 0; }
        .perm-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #eee; }
        .perm-row label { flex: 1; cursor: pointer; }
        .hint {
            font-size: 0.95rem;
            opacity: 0.7;
            margin: 0 0 14px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/FOH" class="back-link">← <?php echo pbj_back_to_hub('foh'); ?></a>
        <h1><?php echo $is_sweet ? 'Floor Plan & Tables' : 'Floor Plan & Tables'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Seating chart, sections & turn times' : 'Seating chart, sections, and turn times'; ?></p>
    </div>

    <div class="content">
        <div class="tabs">
            <button type="button" class="tab active" data-tab="floor"><?php echo $is_sweet ? '🪑 Floor' : 'Floor'; ?></button>
            <button type="button" class="tab" data-tab="sections"><?php echo $is_sweet ? '👥 Sections' : 'Sections'; ?></button>
            <button type="button" class="tab" data-tab="turns"><?php echo $is_sweet ? '⏱️ Turns' : 'Turns'; ?></button>
        </div>

        <div class="stats-row">
            <div class="stat">
                <div class="num" id="stat-open">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Open' : 'Open'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-live">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'In service' : 'In service'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-dirty">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Need bus' : 'Need bus'; ?></div>
            </div>
        </div>

        <!-- Floor tab -->
        <div class="panel active" id="panel-floor">
            <div class="legend">
                <?php foreach ($statuses as $key => $label): ?>
                <div class="legend-item">
                    <span class="dot status-<?php echo $key; ?>"></span>
                    <?php echo htmlspecialchars($label); ?>
                </div>
                <?php endforeach; ?>
            </div>

            <p class="hint" id="floor-hint"><?php echo $is_sweet ? 'Tap a table to update status, party size, or notes. Use <strong>Edit layout</strong> to drag, add, or remove tables 💕' : 'Tap a table to update status. Use Edit layout to drag, add, or remove tables.'; ?></p>

            <div class="layout-toolbar no-print" id="layout-toolbar" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                <button type="button" class="btn btn-secondary" id="toggle-edit-layout"><?php echo $is_sweet ? '✏️ Edit layout' : 'Edit layout'; ?></button>
                <button type="button" class="btn btn-secondary" id="add-table-btn" style="display:none;"><?php echo $is_sweet ? '+ Table' : '+ Table'; ?></button>
                <span id="edit-mode-hint" style="font-size:0.88rem;opacity:0.7;align-self:center;"></span>
            </div>

            <div class="floor-wrap">
                <div class="floor" id="floor">
                    <span class="room-label entrance"><?php echo $is_sweet ? 'Entrance ♡' : 'Entrance'; ?></span>
                    <span class="room-label kitchen"><?php echo $is_sweet ? 'Kitchen →' : 'Kitchen →'; ?></span>
                </div>
            </div>

            <div class="detail-card" id="detail-card">
                <h2 id="detail-title"><?php echo $is_sweet ? 'Pick a table' : 'Select a table'; ?></h2>
                <p class="detail-empty" id="detail-empty"><?php echo $is_sweet ? 'Choose any table on the floor to seat a party or change status.' : 'Choose a table on the floor to seat a party or change status.'; ?></p>
                <div id="detail-body" style="display:none;">
                    <div class="detail-grid">
                        <div>
                            <span class="label"><?php echo $is_sweet ? 'Section' : 'Section'; ?></span>
                            <span class="value" id="detail-section-ro">—</span>
                            <select id="detail-section" class="section-select" style="display:none;width:100%;box-sizing:border-box;border-radius:10px;border:2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;padding:8px 10px;font-size:1rem;margin-top:2px;<?php if ($is_sweet): ?>font-family:'DreamingOutLoudPro',serif;<?php else: ?>font-family:'Lora',serif;<?php endif; ?>">
                            </select>
                        </div>
                        <div>
                            <span class="label"><?php echo $is_sweet ? 'Capacity' : 'Capacity'; ?></span>
                            <span class="value" id="detail-capacity">—</span>
                        </div>
                        <div>
                            <span class="label"><?php echo $is_sweet ? 'Seated at' : 'Seated at'; ?></span>
                            <span class="value" id="detail-seated-at">—</span>
                        </div>
                        <div>
                            <span class="label"><?php echo $is_sweet ? 'Turn time' : 'Turn time'; ?></span>
                            <span class="value" id="detail-turn">—</span>
                        </div>
                    </div>

                    <div class="status-btns" id="status-btns">
                        <?php foreach ($statuses as $key => $label): ?>
                        <button type="button" class="status-btn" data-status="<?php echo $key; ?>">
                            <span class="dot status-<?php echo $key; ?>" style="display:inline-block;vertical-align:middle;margin-right:4px;"></span>
                            <?php echo htmlspecialchars($label); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="party-size"><?php echo $is_sweet ? 'Party size' : 'Party size'; ?></label>
                            <input type="number" id="party-size" min="1" max="20" value="2">
                        </div>
                        <div class="field">
                            <label for="server-name"><?php echo $is_sweet ? 'Server' : 'Server'; ?></label>
                            <input type="text" id="server-name" placeholder="<?php echo $is_sweet ? 'Who\'s got this one?' : 'Server name'; ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label for="table-notes"><?php echo $is_sweet ? 'Table notes' : 'Table notes'; ?></label>
                        <textarea class="notes-input" id="table-notes" placeholder="<?php echo $is_sweet ? 'Birthday, high chair, allergy, VIP vibes…' : 'Birthday, high chair, allergy, VIP…'; ?>"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections tab -->
        <div class="panel" id="panel-sections">
            <p class="hint"><?php echo $is_sweet ? 'Name your sections, assign servers for the shift, and manage who owns which tables. Dotted zones update on the floor map 💕' : 'Name sections, assign servers, and manage table groups. Dotted zones update on the floor map.'; ?></p>
            <div class="assign-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                    <h2 style="margin:0;"><?php echo $is_sweet ? 'Sections' : 'Sections'; ?></h2>
                    <button type="button" class="btn btn-secondary" id="add-section-btn"><?php echo $is_sweet ? '+ Section' : '+ Section'; ?></button>
                </div>
                <div id="sections-list"></div>
            </div>
        </div>

        <!-- Turns tab -->
        <div class="panel" id="panel-turns">
            <p class="hint"><?php echo $is_sweet ? 'Live turn clock for seated tables. Goal defaults to 45 minutes — tweak anytime.' : 'Live turn clock for seated tables. Default goal is 45 minutes.'; ?></p>
            <div class="turns-card">
                <div class="field-row" style="margin-bottom:16px;">
                    <div class="field">
                        <label for="turn-goal"><?php echo $is_sweet ? 'Turn goal (minutes)' : 'Turn goal (minutes)'; ?></label>
                        <input type="number" id="turn-goal" min="15" max="180" value="45">
                    </div>
                </div>
                <h2 class="section-title"><?php echo $is_sweet ? 'Tables in play' : 'Tables in play'; ?></h2>
                <div id="turns-list">
                    <p class="detail-empty"><?php echo $is_sweet ? 'No tables seated yet — go fill the room!' : 'No tables currently seated.'; ?></p>
                </div>
            </div>
        </div>

        <div class="actions">
            <button type="button" class="btn btn-secondary" id="reset-btn"><?php echo $is_sweet ? 'Reset floor' : 'Reset floor'; ?></button>
            <a href="/FOH" class="btn btn-primary"><?php echo pbj_back_to_hub('foh'); ?></a>
        </div>
    </div>


    <div class="modal-backdrop" id="table-layout-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:2000;align-items:flex-end;justify-content:center;padding:16px;">
        <div style="background:white;border-radius:20px;width:100%;max-width:480px;padding:22px 20px;">
            <h2 id="tl-title" style="margin:0 0 14px;"><?php echo $is_sweet ? 'Add table' : 'Add table'; ?></h2>
            <div class="field"><label><?php echo $is_sweet ? 'Table # / name' : 'Table # / name'; ?></label><input id="tl-id" maxlength="12" placeholder="12"></div>
            <div class="field-row" style="display:flex;gap:10px;">
                <div class="field" style="flex:1;"><label><?php echo $is_sweet ? 'Seats' : 'Seats'; ?></label><input type="number" id="tl-seats" min="1" max="24" value="4"></div>
                <div class="field" style="flex:1;"><label><?php echo $is_sweet ? 'Section' : 'Section'; ?></label>
                    <select id="tl-section"></select>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="button" class="btn btn-secondary" id="tl-cancel" style="flex:1;"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                <button type="button" class="btn btn-primary" id="tl-save" style="flex:1;"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="section-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:2000;align-items:flex-end;justify-content:center;padding:16px;">
        <div style="background:white;border-radius:20px;width:100%;max-width:480px;padding:22px 20px;">
            <h2 id="sec-modal-title" style="margin:0 0 14px;"><?php echo $is_sweet ? 'Section' : 'Section'; ?></h2>
            <input type="hidden" id="sec-edit-id">
            <div class="field"><label><?php echo $is_sweet ? 'Code (short)' : 'Code (short)'; ?></label><input id="sec-id" maxlength="8" placeholder="A"></div>
            <div class="field"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="sec-name" maxlength="60" placeholder="<?php echo $is_sweet ? 'e.g. Patio' : 'e.g. Patio'; ?>"></div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="button" class="btn btn-secondary" id="sec-cancel" style="flex:1;"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                <button type="button" class="btn btn-primary" id="sec-save" style="flex:1;"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
            </div>
        </div>
    </div>
    <div class="sync-pill no-print syncing" id="sync-pill" style="position:fixed;bottom:100px;left:50%;transform:translateX(-50%);z-index:50;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:0.85rem;box-shadow:0 4px 14px rgba(0,0,0,0.12);background:#fff;opacity:0.95;">
        <span class="dot" style="width:8px;height:8px;border-radius:50%;background:#2E9B63;"></span>
        <span id="sync-pill-text">Connecting…</span>
    </div>
    <?php include 'bottom-nav.php'; ?>

    <script src="shared-state.js?v=3"></script>
    <script>
        (function () {
            const STORAGE_KEY = 'pbj_showtime_floor_plan_v3';
            const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            const DEFAULT_LAYOUT = <?php echo json_encode($tables, JSON_UNESCAPED_UNICODE); ?>;
            const statusColors = {
                open: '#7BC67E',
                seated: '#5B9BD5',
                ordered: '#9B7EDE',
                check: '#E8A838',
                dirty: '#E57373',
                reserved: '#90A4AE'
            };
            const liveStatuses = ['seated', 'ordered', 'check'];
            var tables = []; // {el,id,seats,section} rebuilt by renderLayout
            // Layout / structure rights come from sitewide role permissions.
            var canEditFloor = false;
            var canEditSections = false;
            var canSelectFill = true;
            var canFillSections = true;
            var canResetFloor = false;
            var canTurnGoal = true;
            var editLayoutMode = false;
            var drag = null;

            const tabs = document.querySelectorAll('.tab');
            const panels = {
                floor: document.getElementById('panel-floor'),
                sections: document.getElementById('panel-sections'),
                turns: document.getElementById('panel-turns')
            };

            let state = loadState();
            let selectedId = null;
            function getLayout() {
                if (!Array.isArray(state.layout) || !state.layout.length) {
                    state.layout = JSON.parse(JSON.stringify(DEFAULT_LAYOUT));
                }
                return state.layout;
            }

            var DEFAULT_SECTIONS = [
                { id: 'A', name: isSweet ? 'Windows' : 'Windows', color: 0 },
                { id: 'B', name: isSweet ? 'Center' : 'Center', color: 1 },
                { id: 'C', name: isSweet ? 'Back' : 'Back', color: 2 }
            ];

            function getSections() {
                if (!Array.isArray(state.sections) || !state.sections.length) {
                    state.sections = JSON.parse(JSON.stringify(DEFAULT_SECTIONS));
                }
                // normalize
                state.sections = state.sections.map(function (s, i) {
                    return {
                        id: String(s.id || ('S' + (i + 1))).toUpperCase(),
                        name: s.name || s.id || ('Section ' + (i + 1)),
                        color: (s.color != null ? s.color : i) % 6
                    };
                });
                return state.sections;
            }

            function sectionById(id) {
                id = String(id || '').toUpperCase();
                return getSections().find(function (s) { return s.id === id; }) || null;
            }

            function sectionLabel(id) {
                var s = sectionById(id);
                if (!s) return id ? ('Section ' + id) : '—';
                return s.id + (s.name ? ' · ' + s.name : '');
            }

            function firstSectionId() {
                var secs = getSections();
                return secs.length ? secs[0].id : 'A';
            }

            function nextSectionCode() {
                var used = {};
                getSections().forEach(function (s) { used[s.id] = true; });
                var alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                for (var i = 0; i < alphabet.length; i++) {
                    if (!used[alphabet[i]]) return alphabet[i];
                }
                var n = 1;
                while (used['S' + n]) n++;
                return 'S' + n;
            }

            function fillSectionSelects(selected) {
                selected = selected || firstSectionId();
                ['detail-section', 'tl-section'].forEach(function (sid) {
                    var el = document.getElementById(sid);
                    if (!el) return;
                    var cur = el.value || selected;
                    el.innerHTML = getSections().map(function (s) {
                        return '<option value="' + escAttr(s.id) + '">' + escHtml(sectionLabel(s.id)) + '</option>';
                    }).join('');
                    if ([].some.call(el.options, function (o) { return o.value === cur; })) el.value = cur;
                    else if (el.options.length) el.selectedIndex = 0;
                });
            }

            function escAttr(s) { return String(s || '').replace(/"/g, '&quot;'); }
            function escHtml(s) {
                return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

            function drawSectionZones() {
                var floor = document.getElementById('floor');
                floor.querySelectorAll('.section-zone').forEach(function (n) { n.remove(); });
                var layout = getLayout();
                var bySec = {};
                getSections().forEach(function (s) { bySec[s.id] = []; });
                layout.forEach(function (t) {
                    var sec = String(t.section || firstSectionId()).toUpperCase();
                    if (!bySec[sec]) bySec[sec] = [];
                    bySec[sec].push(t);
                });
                getSections().forEach(function (s) {
                    var list = bySec[s.id] || [];
                    if (!list.length) return;
                    var pad = 2.5;
                    var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                    list.forEach(function (t) {
                        var x = parseFloat(t.x) || 0;
                        var y = parseFloat(t.y) || 0;
                        var w = parseFloat(t.w) || 16;
                        var h = parseFloat(t.h) || 16;
                        minX = Math.min(minX, x);
                        minY = Math.min(minY, y);
                        maxX = Math.max(maxX, x + w);
                        maxY = Math.max(maxY, y + h);
                    });
                    minX = Math.max(0, minX - pad);
                    minY = Math.max(0, minY - pad);
                    maxX = Math.min(100, maxX + pad);
                    maxY = Math.min(100, maxY + pad);
                    var zone = document.createElement('div');
                    zone.className = 'section-zone color-' + ((s.color != null ? s.color : 0) % 6);
                    zone.dataset.section = s.id;
                    zone.style.left = minX + '%';
                    zone.style.top = minY + '%';
                    zone.style.width = (maxX - minX) + '%';
                    zone.style.height = (maxY - minY) + '%';
                    zone.innerHTML = '<span class="section-zone-label">' + escHtml(s.id + (s.name ? ' · ' + s.name : '')) + '</span>';
                    floor.appendChild(zone);
                });
            }

            function setTableSection(tableId, section) {
                section = String(section || firstSectionId()).toUpperCase();
                if (!sectionById(section)) return;
                var layoutItem = getLayout().find(function (x) { return String(x.id) === String(tableId); });
                if (!layoutItem) return;
                if (String(layoutItem.section).toUpperCase() === section) return;
                layoutItem.section = section;
                state.layoutAt = Date.now();
                saveState({ skipTouch: true });
                renderLayout();
                paintFloor();
                paintDetail();
                paintTurns();
                renderSectionsPanel();
            }

            function renderSectionsPanel() {
                var root = document.getElementById('sections-list');
                if (!root) return;
                var secs = getSections();
                if (!secs.length) {
                    root.innerHTML = '<p class="detail-empty">' + (isSweet ? 'No sections yet — add one ✨' : 'No sections yet.') + '</p>';
                    return;
                }
                root.innerHTML = secs.map(function (s, idx) {
                    var ids = getLayout().filter(function (t) { return String(t.section).toUpperCase() === s.id; }).map(function (t) { return t.id; });
                    var tablesTxt = ids.length ? ('Tables ' + ids.join(', ')) : (isSweet ? 'No tables yet' : 'No tables');
                    var serverVal = (state.assignments && state.assignments[s.id]) || '';
                    var actions = canEditSections
                        ? '<div class="sec-actions">' +
                          '<button type="button" class="btn-tiny" data-sec-act="up" data-sec="' + escAttr(s.id) + '"' + (idx === 0 ? ' disabled style="opacity:0.4;"' : '') + ' title="' + (isSweet ? 'Move up' : 'Move up') + '">↑</button>' +
                          '<button type="button" class="btn-tiny" data-sec-act="down" data-sec="' + escAttr(s.id) + '"' + (idx === secs.length - 1 ? ' disabled style="opacity:0.4;"' : '') + ' title="' + (isSweet ? 'Move down' : 'Move down') + '">↓</button>' +
                          '<button type="button" class="btn-tiny" data-sec-act="edit" data-sec="' + escAttr(s.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                          '<button type="button" class="btn-tiny danger" data-sec-act="del" data-sec="' + escAttr(s.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                          '</div>'
                        : '';
                    return '<div class="assign-row" data-sec-row="' + escAttr(s.id) + '">' +
                        '<div class="section-badge">' + escHtml(s.id) + '</div>' +
                        '<div class="assign-info" style="flex:1;min-width:120px;">' +
                          '<div class="name">' + escHtml(s.name || s.id) + '</div>' +
                          '<div class="sub" data-section-tables="' + escAttr(s.id) + '">' + escHtml(tablesTxt) + '</div>' +
                          actions +
                        '</div>' +
                        '<div class="field" style="flex:1; min-width:140px; margin:0;">' +
                          '<label class="sr-only" style="position:absolute;left:-9999px;">Server</label>' +
                          '<input type="text" data-assign="' + escAttr(s.id) + '" value="' + escAttr(serverVal) + '" placeholder="' + (isSweet ? 'Server name' : 'Server name') + '">' +
                        '</div></div>';
                }).join('');

                root.querySelectorAll('[data-assign]').forEach(function (input) {
                    if (!canFillSections) {
                        input.disabled = true;
                        return;
                    }
                    input.addEventListener('input', function () {
                        if (!state.assignments) state.assignments = {};
                        state.assignments[input.getAttribute('data-assign')] = input.value;
                        saveState({ assignments: true, skipTouch: true });
                    });
                });
                root.querySelectorAll('[data-sec-act]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (btn.disabled) return;
                        var act = btn.getAttribute('data-sec-act');
                        var id = btn.getAttribute('data-sec');
                        if (act === 'edit') openSectionModal(id);
                        if (act === 'del') deleteSection(id);
                        if (act === 'up') moveSection(id, -1);
                        if (act === 'down') moveSection(id, 1);
                    });
                });
                fillSectionSelects();
            }

            function moveSection(id, dir) {
                if (!canEditSections) return;
                var secs = getSections().slice().map(function (s) {
                    return { id: s.id, name: s.name, color: s.color };
                });
                var idx = -1;
                for (var i = 0; i < secs.length; i++) {
                    if (secs[i].id === id) { idx = i; break; }
                }
                if (idx < 0) return;
                var j = idx + dir;
                if (j < 0 || j >= secs.length) return;
                var tmp = secs[idx];
                secs[idx] = secs[j];
                secs[j] = tmp;
                state.sections = secs;
                state.sectionsAt = Date.now();
                saveState({ skipTouch: true });
                fillSectionSelects();
                renderLayout();
                paintFloor();
                paintDetail();
                renderSectionsPanel();
            }

            function openSectionModal(editId) {
                if (!canEditSections) return;
                var title = document.getElementById('sec-modal-title');
                var idEl = document.getElementById('sec-id');
                var nameEl = document.getElementById('sec-name');
                var hid = document.getElementById('sec-edit-id');
                idEl.disabled = false; // code is always editable
                if (editId) {
                    var s = sectionById(editId);
                    if (!s) return;
                    title.textContent = isSweet ? 'Edit section' : 'Edit section';
                    hid.value = s.id;
                    idEl.value = s.id;
                    nameEl.value = s.name || '';
                } else {
                    title.textContent = isSweet ? 'Add section' : 'Add section';
                    hid.value = '';
                    idEl.value = nextSectionCode();
                    nameEl.value = '';
                }
                document.getElementById('section-modal').style.display = 'flex';
                setTimeout(function () { (editId ? nameEl : idEl).focus(); }, 40);
            }

            function deleteSection(id) {
                if (!canEditSections) return;
                var secs = getSections();
                if (secs.length <= 1) {
                    alert(isSweet ? 'Keep at least one section 💕' : 'Keep at least one section.');
                    return;
                }
                var s = sectionById(id);
                if (!s) return;
                var count = getLayout().filter(function (t) { return String(t.section).toUpperCase() === id; }).length;
                var msg = isSweet
                    ? ('Remove section ' + s.id + (s.name ? ' (' + s.name + ')' : '') + '?' + (count ? ' Its ' + count + ' table(s) move to another section.' : ''))
                    : ('Remove section ' + s.id + '?' + (count ? ' ' + count + ' table(s) will be reassigned.' : ''));
                if (!confirm(msg)) return;
                var fallback = secs.find(function (x) { return x.id !== id; });
                if (!fallback) return;
                getLayout().forEach(function (t) {
                    if (String(t.section).toUpperCase() === id) t.section = fallback.id;
                });
                state.sections = secs.filter(function (x) { return x.id !== id; });
                if (state.assignments) delete state.assignments[id];
                state.layoutAt = Date.now();
                state.sectionsAt = Date.now();
                saveState({ skipTouch: true, assignments: true });
                renderLayout();
                paintFloor();
                paintDetail();
                paintTurns();
                renderSectionsPanel();
            }

            function renderLayout() {
                var floor = document.getElementById('floor');
                // keep labels
                var labels = floor.querySelectorAll('.room-label');
                var keep = [];
                labels.forEach(function (n) { keep.push(n); });
                floor.innerHTML = '';
                keep.forEach(function (n) { floor.appendChild(n); });
                tables = [];
                // zones first (under tables)
                drawSectionZones();
                getLayout().forEach(function (t) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'table';
                    btn.dataset.id = t.id;
                    btn.dataset.seats = t.seats;
                    btn.dataset.section = t.section;
                    btn.style.left = t.x + '%';
                    btn.style.top = t.y + '%';
                    btn.style.width = t.w + '%';
                    btn.style.height = t.h + '%';
                    btn.setAttribute('aria-label', 'Table ' + t.id);
                    var sec = (t.section || 'A').toUpperCase();
                    btn.innerHTML = '<span class="table-num">' + String(t.id).replace(/</g,'') + '</span>' +
                        '<span class="table-meta">' + (parseInt(t.seats,10)||0) + ' top · ' + sec + '</span>' +
                        '<button type="button" class="del-table" data-del="' + String(t.id).replace(/"/g,'') + '" title="Remove">×</button>';
                    floor.appendChild(btn);
                    tables.push({ el: btn, id: String(t.id), seats: parseInt(t.seats,10)||2, section: sec });
                    btn.addEventListener('click', function (e) {
                        if (e.target.closest('.del-table')) return;
                        if (editLayoutMode) return;
                        if (!canSelectFill) return;
                        selectedId = String(t.id);
                        paintDetail();
                    });
                    // drag in edit mode
                    btn.addEventListener('pointerdown', function (e) {
                        if (!editLayoutMode || e.target.closest('.del-table')) return;
                        e.preventDefault();
                        var rect = floor.getBoundingClientRect();
                        drag = {
                            id: String(t.id),
                            startX: e.clientX,
                            startY: e.clientY,
                            origX: parseFloat(t.x) || 0,
                            origY: parseFloat(t.y) || 0,
                            rect: rect
                        };
                        btn.classList.add('dragging');
                        btn.setPointerCapture(e.pointerId);
                    });
                    btn.addEventListener('pointermove', function (e) {
                        if (!drag || drag.id !== String(t.id)) return;
                        var dx = ((e.clientX - drag.startX) / drag.rect.width) * 100;
                        var dy = ((e.clientY - drag.startY) / drag.rect.height) * 100;
                        var nx = Math.max(0, Math.min(90, drag.origX + dx));
                        var ny = Math.max(0, Math.min(90, drag.origY + dy));
                        btn.style.left = nx + '%';
                        btn.style.top = ny + '%';
                        t.x = Math.round(nx * 10) / 10;
                        t.y = Math.round(ny * 10) / 10;
                    });
                    btn.addEventListener('pointerup', function (e) {
                        if (!drag || drag.id !== String(t.id)) return;
                        btn.classList.remove('dragging');
                        drag = null;
                        state.layoutAt = Date.now();
                        saveState({ skipTouch: true });
                        drawSectionZones(); // refresh dotted bounds after move
                    });
                });
                // delete handlers
                floor.querySelectorAll('[data-del]').forEach(function (b) {
                    b.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!editLayoutMode || !canEditFloor) return;
                        var id = b.getAttribute('data-del');
                        if (!confirm(isSweet ? 'Remove table ' + id + ' from the floor plan?' : 'Remove table ' + id + '?')) return;
                        state.layout = getLayout().filter(function (x) { return String(x.id) !== String(id); });
                        delete state.tables[id];
                        if (selectedId === String(id)) selectedId = null;
                        state.layoutAt = Date.now();
                        saveState({ skipTouch: true });
                        renderLayout();
                        paintFloor();
                        paintDetail();
                        paintTurns();
                    });
                });
            }


            function defaultTableState(id, seats) {
                return {
                    status: 'open',
                    party: seats,
                    server: '',
                    notes: '',
                    seatedAt: null,
                    updatedAt: 0
                };
            }

            function loadState() {
                try {
                    const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
                    var defSec = [
                        { id: 'A', name: 'Windows', color: 0 },
                        { id: 'B', name: 'Center', color: 1 },
                        { id: 'C', name: 'Back', color: 2 }
                    ];
                    return {
                        layout: Array.isArray(raw.layout) && raw.layout.length ? raw.layout : JSON.parse(JSON.stringify(DEFAULT_LAYOUT)),
                        layoutAt: raw.layoutAt || 0,
                        sections: Array.isArray(raw.sections) && raw.sections.length ? raw.sections : defSec,
                        sectionsAt: raw.sectionsAt || 0,
                        tables: raw.tables || {},
                        assignments: raw.assignments || {},
                        turnGoal: raw.turnGoal || 45,
                        structureAt: raw.structureAt || Date.now(),
                        assignmentsAt: raw.assignmentsAt || 0,
                        turnGoalAt: raw.turnGoalAt || 0
                    };
                } catch (e) {
                    return {
                        layout: JSON.parse(JSON.stringify(DEFAULT_LAYOUT)),
                        layoutAt: 0,
                        sections: [
                            { id: 'A', name: 'Windows', color: 0 },
                            { id: 'B', name: 'Center', color: 1 },
                            { id: 'C', name: 'Back', color: 2 }
                        ],
                        sectionsAt: 0,
                        tables: {},
                        assignments: {},
                        turnGoal: 45
                    };
                }
            }

            function saveState(opts) {
                opts = opts || {};
                if (!opts.skipTouch && selectedId && state.tables[selectedId]) {
                    state.tables[selectedId].updatedAt = Date.now();
                }
                // also stamp assignment/goal changes via opts
                if (opts.assignments) state.assignmentsAt = Date.now();
                if (opts.turnGoal) state.turnGoalAt = Date.now();
                state.structureAt = Math.max(state.structureAt || 0, Date.now());
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                if (!opts.skipRemote && window._floorShared) window._floorShared.queuePush(state);
            }

            function ensureTable(id, seats) {
                if (!state.tables[id]) {
                    state.tables[id] = defaultTableState(id, seats);
                }
                return state.tables[id];
            }

            function formatTime(ts) {
                if (!ts) return '—';
                const d = new Date(ts);
                return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            }

            function minutesSince(ts) {
                if (!ts) return null;
                return Math.floor((Date.now() - ts) / 60000);
            }

            function paintFloor() {
                let open = 0, live = 0, dirty = 0;
                tables.forEach(function (t) {
                    const st = ensureTable(t.id, t.seats);
                    t.el.style.background = statusColors[st.status] || statusColors.open;
                    if (st.status === 'open') open++;
                    if (liveStatuses.indexOf(st.status) !== -1) live++;
                    if (st.status === 'dirty') dirty++;
                });
                document.getElementById('stat-open').textContent = open;
                document.getElementById('stat-live').textContent = live;
                document.getElementById('stat-dirty').textContent = dirty;

                // refresh section table labels
                if (typeof renderSectionsPanel === 'function') {
                    // only update table sublines without rebuilding inputs focus loss:
                    getSections().forEach(function (s) {
                        var ids = tables.filter(function (t) { return t.section === s.id; }).map(function (t) { return t.id; });
                        var el = document.querySelector('[data-section-tables="' + s.id + '"]');
                        if (el) el.textContent = ids.length ? ('Tables ' + ids.join(', ')) : (isSweet ? 'No tables yet' : 'No tables');
                    });
                }
            }

            function paintDetail() {
                const empty = document.getElementById('detail-empty');
                const body = document.getElementById('detail-body');
                const title = document.getElementById('detail-title');

                tables.forEach(function (t) {
                    t.el.classList.toggle('selected', t.id === selectedId);
                });

                if (!selectedId) {
                    empty.style.display = 'block';
                    body.style.display = 'none';
                    title.textContent = <?php echo json_encode($is_sweet ? 'Pick a table' : 'Select a table'); ?>;
                    return;
                }

                const meta = tables.find(function (t) { return t.id === selectedId; });
                const st = ensureTable(selectedId, meta.seats);
                empty.style.display = 'none';
                body.style.display = 'block';
                title.textContent = <?php echo json_encode($is_sweet ? 'Table ' : 'Table '); ?> + selectedId;
                var secRo = document.getElementById('detail-section-ro');
                var secSel = document.getElementById('detail-section');
                fillSectionSelects(meta.section);
                if (canEditFloor) {
                    secRo.style.display = 'none';
                    secSel.style.display = 'block';
                    secSel.value = String(meta.section || firstSectionId()).toUpperCase();
                } else {
                    secSel.style.display = 'none';
                    secRo.style.display = 'block';
                    secRo.textContent = sectionLabel(meta.section);
                }
                document.getElementById('detail-capacity').textContent = meta.seats + ' seats';
                document.getElementById('detail-seated-at').textContent = formatTime(st.seatedAt);
                const mins = minutesSince(st.seatedAt);
                document.getElementById('detail-turn').textContent = mins === null ? '—' : mins + ' min';
                document.getElementById('party-size').value = st.party || meta.seats;
                document.getElementById('server-name').value = st.server || state.assignments[meta.section] || '';
                document.getElementById('table-notes').value = st.notes || '';

                document.querySelectorAll('.status-btn').forEach(function (btn) {
                    btn.classList.toggle('active', btn.dataset.status === st.status);
                });
            }

            // Change which section a table belongs to (owners / managers)
            document.getElementById('detail-section').addEventListener('change', function () {
                if (!selectedId || !canEditFloor) return;
                setTableSection(selectedId, this.value);
            });

            function paintTurns() {
                const list = document.getElementById('turns-list');
                const goal = state.turnGoal || 45;
                const rows = tables
                    .map(function (t) {
                        const st = ensureTable(t.id, t.seats);
                        if (liveStatuses.indexOf(st.status) === -1 || !st.seatedAt) return null;
                        const mins = minutesSince(st.seatedAt);
                        return { t: t, st: st, mins: mins };
                    })
                    .filter(Boolean)
                    .sort(function (a, b) { return b.mins - a.mins; });

                if (!rows.length) {
                    list.innerHTML = '<p class="detail-empty">' + <?php echo json_encode($is_sweet ? 'No tables seated yet — go fill the room!' : 'No tables currently seated.'); ?> + '</p>';
                    return;
                }

                list.innerHTML = rows.map(function (row) {
                    const over = row.mins >= goal;
                    return (
                        '<div class="turn-row">' +
                            '<div class="section-badge">' + row.t.id + '</div>' +
                            '<div class="turn-info">' +
                                '<div class="name">Table ' + row.t.id + ' · Sec ' + row.t.section + '</div>' +
                                '<div class="sub">' +
                                    (row.st.party || row.t.seats) + ' guests' +
                                    (row.st.server ? ' · ' + escapeHtml(row.st.server) : '') +
                                    ' · ' + row.st.status +
                                '</div>' +
                            '</div>' +
                            '<div class="turn-timer">' +
                                '<div class="mins' + (over ? ' over' : '') + '">' + row.mins + 'm</div>' +
                                '<div class="goal">goal ' + goal + 'm</div>' +
                            '</div>' +
                        '</div>'
                    );
                }).join('');
            }

            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function setStatus(id, status) {
                const meta = tables.find(function (t) { return t.id === id; });
                const st = ensureTable(id, meta.seats);
                const wasLive = liveStatuses.indexOf(st.status) !== -1;
                const willLive = liveStatuses.indexOf(status) !== -1;
                st.status = status;
                if (willLive && !st.seatedAt) {
                    st.seatedAt = Date.now();
                }
                if (status === 'open' || status === 'dirty' || status === 'reserved') {
                    if (status === 'open') {
                        st.seatedAt = null;
                        st.notes = st.notes; // keep notes optional; clear party soft
                    }
                    if (status === 'open') {
                        st.party = meta.seats;
                    }
                }
                if (!wasLive && willLive && !st.server) {
                    st.server = state.assignments[meta.section] || '';
                }
                saveState();
                paintFloor();
                paintDetail();
                paintTurns();
            }

            // Tabs
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const key = tab.dataset.tab;
                    tabs.forEach(function (t) { t.classList.toggle('active', t === tab); });
                    Object.keys(panels).forEach(function (k) {
                        panels[k].classList.toggle('active', k === key);
                    });
                    if (key === 'turns') paintTurns();
                    if (key === 'sections') renderSectionsPanel();
                });
            });

            // Table click
            tables.forEach(function (t) {
                ensureTable(t.id, t.seats);
                t.el.addEventListener('click', function () {
                    selectedId = t.id;
                    paintDetail();
                });
            });

            // Status buttons
            document.getElementById('status-btns').addEventListener('click', function (e) {
                const btn = e.target.closest('.status-btn');
                if (!btn || !selectedId) return;
                setStatus(selectedId, btn.dataset.status);
            });

            // Detail fields
            document.getElementById('party-size').addEventListener('change', function () {
                if (!selectedId) return;
                const meta = tables.find(function (t) { return t.id === selectedId; });
                const st = ensureTable(selectedId, meta.seats);
                st.party = parseInt(this.value, 10) || meta.seats;
                saveState();
                paintTurns();
            });

            document.getElementById('server-name').addEventListener('input', function () {
                if (!selectedId) return;
                const meta = tables.find(function (t) { return t.id === selectedId; });
                const st = ensureTable(selectedId, meta.seats);
                st.server = this.value;
                saveState();
                paintTurns();
            });

            document.getElementById('table-notes').addEventListener('input', function () {
                if (!selectedId) return;
                const meta = tables.find(function (t) { return t.id === selectedId; });
                const st = ensureTable(selectedId, meta.seats);
                st.notes = this.value;
                saveState();
            });

            // Section assignments are bound in renderSectionsPanel()

            // Turn goal
            const goalInput = document.getElementById('turn-goal');
            goalInput.value = state.turnGoal || 45;
            goalInput.addEventListener('change', function () {
                if (!canTurnGoal) return;
                state.turnGoal = parseInt(this.value, 10) || 45;
                saveState({ turnGoal: true, skipTouch: true });
                paintTurns();
                paintDetail();
            });

            // Reset
            document.getElementById('reset-btn').addEventListener('click', function () {
                if (!canResetFloor) return;
                if (!confirm(<?php echo json_encode($is_sweet ? 'Reset all table statuses for a fresh floor?' : 'Reset all table statuses?'); ?>)) return;
                state.tables = {};
                tables.forEach(function (t) { ensureTable(t.id, t.seats); });
                selectedId = null;
                saveState();
                paintFloor();
                paintDetail();
                paintTurns();
            });

            // Tick turn times
            setInterval(function () {
                paintDetail();
                if (panels.turns.classList.contains('active')) paintTurns();
            }, 30000);


            function setEditUi() {
                var bar = document.getElementById('layout-toolbar');
                if (!bar) return;
                var showBar = canEditFloor || canEditSections;
                bar.style.display = showBar ? 'flex' : 'none';
                document.getElementById('add-table-btn').style.display = (editLayoutMode && canEditFloor) ? '' : 'none';
                var addSec = document.getElementById('add-section-btn');
                if (addSec) addSec.style.display = canEditSections ? '' : 'none';
                var toggle = document.getElementById('toggle-edit-layout');
                if (toggle) toggle.style.display = canEditFloor ? '' : 'none';
                document.getElementById('floor').classList.toggle('edit-mode', editLayoutMode && canEditFloor);
                if (toggle) {
                    toggle.textContent = editLayoutMode
                        ? (isSweet ? '✓ Done editing' : 'Done editing')
                        : (isSweet ? '✏️ Edit layout' : 'Edit layout');
                }
                document.getElementById('edit-mode-hint').textContent = editLayoutMode
                    ? (isSweet ? 'Drag tables to move · × to remove' : 'Drag tables to move · × to remove')
                    : '';
            }

            document.getElementById('toggle-edit-layout').addEventListener('click', function () {
                if (!canEditFloor) return;
                editLayoutMode = !editLayoutMode;
                if (editLayoutMode) selectedId = null;
                setEditUi();
                paintDetail();
            });

            document.getElementById('add-table-btn').addEventListener('click', function () {
                if (!canEditFloor || !editLayoutMode) return;
                document.getElementById('tl-id').value = '';
                document.getElementById('tl-seats').value = '4';
                fillSectionSelects(firstSectionId());
                document.getElementById('tl-section').value = firstSectionId();
                document.getElementById('table-layout-modal').style.display = 'flex';
            });
            document.getElementById('tl-cancel').addEventListener('click', function () {
                document.getElementById('table-layout-modal').style.display = 'none';
            });
            document.getElementById('tl-save').addEventListener('click', function () {
                var id = document.getElementById('tl-id').value.trim();
                var seats = parseInt(document.getElementById('tl-seats').value, 10) || 4;
                var section = document.getElementById('tl-section').value || 'A';
                if (!id) { alert(isSweet ? 'Give the table a number or name' : 'Enter a table id'); return; }
                if (getLayout().some(function (t) { return String(t.id) === id; })) {
                    alert(isSweet ? 'That table already exists' : 'Table already exists');
                    return;
                }
                if (!sectionById(section)) section = firstSectionId();
                getLayout().push({ id: id, seats: seats, section: section, x: 40, y: 40, w: 18, h: 18 });
                state.layoutAt = Date.now();
                ensureTable(id, seats);
                saveState({ skipTouch: true });
                document.getElementById('table-layout-modal').style.display = 'none';
                renderLayout();
                paintFloor();
                paintTurns();
            });

            // Layout edit rights (owner/admin/manager for now). Fine-grained grants move to Sandwich HQ later.
            
            document.getElementById('add-section-btn').addEventListener('click', function () {
                openSectionModal(null);
            });
            document.getElementById('sec-cancel').addEventListener('click', function () {
                document.getElementById('sec-id').disabled = false;
                document.getElementById('section-modal').style.display = 'none';
            });
            document.getElementById('sec-save').addEventListener('click', function () {
                if (!canEditSections) return;
                var editId = document.getElementById('sec-edit-id').value.trim().toUpperCase();
                var idEl = document.getElementById('sec-id');
                var id = idEl.value.trim().toUpperCase();
                var name = document.getElementById('sec-name').value.trim();
                if (!id) { alert(isSweet ? 'Give the section a short code (A, Patio…)' : 'Enter a section code'); return; }
                if (!/^[A-Z0-9]{1,8}$/.test(id)) {
                    alert(isSweet ? 'Code: letters/numbers only, up to 8 chars' : 'Code: letters/numbers only, max 8');
                    return;
                }
                if (!name) name = id;

                // Work on one array only — getSections() remaps; don't re-fetch mid-edit or we overwrite the change
                var secs = getSections().slice().map(function (s) {
                    return { id: s.id, name: s.name, color: s.color };
                });
                if (editId) {
                    var found = false;
                    // New code must be unique (unless unchanged)
                    if (id !== editId && secs.some(function (s) { return s.id === id; })) {
                        alert(isSweet ? 'That section code already exists' : 'Section code already exists');
                        return;
                    }
                    secs = secs.map(function (s) {
                        if (s.id === editId) {
                            found = true;
                            return { id: id, name: name, color: s.color };
                        }
                        return s;
                    });
                    if (!found) {
                        alert(isSweet ? 'Could not find that section to edit' : 'Section not found');
                        return;
                    }
                    // If code changed, re-point tables + server assignments
                    if (id !== editId) {
                        getLayout().forEach(function (t) {
                            if (String(t.section || '').toUpperCase() === editId) {
                                t.section = id;
                            }
                        });
                        if (!state.assignments) state.assignments = {};
                        if (Object.prototype.hasOwnProperty.call(state.assignments, editId)) {
                            state.assignments[id] = state.assignments[editId];
                            delete state.assignments[editId];
                        } else if (state.assignments[id] == null) {
                            state.assignments[id] = '';
                        }
                        state.layoutAt = Date.now();
                    }
                } else {
                    if (secs.some(function (s) { return s.id === id; })) {
                        alert(isSweet ? 'That section code already exists' : 'Section code already exists');
                        return;
                    }
                    secs.push({ id: id, name: name, color: secs.length % 6 });
                    if (!state.assignments) state.assignments = {};
                    state.assignments[id] = state.assignments[id] || '';
                }
                state.sections = secs;
                state.sectionsAt = Date.now();
                saveState({ skipTouch: true, assignments: true });
                document.getElementById('section-modal').style.display = 'none';
                idEl.disabled = false;
                fillSectionSelects();
                renderLayout();
                paintFloor();
                paintDetail();
                renderSectionsPanel();
            });

            function applyFloorPerms() {
                var P = window.PbjPerms;
                if (P && P.loaded) {
                    canEditFloor = P.can('foh.floor.edit_layout');
                    canEditSections = P.can('foh.floor.edit_sections');
                    canSelectFill = P.can('foh.floor.select_fill');
                    canFillSections = P.can('foh.floor.fill_sections');
                    canResetFloor = P.can('foh.floor.reset');
                    canTurnGoal = P.can('foh.floor.turn_goal');
                }
                // Layout toolbar shows if either layout or section structure allowed
                var showBar = canEditFloor || canEditSections;
                var bar = document.getElementById('layout-toolbar');
                if (bar) bar.style.display = showBar ? 'flex' : 'none';
                var addSec = document.getElementById('add-section-btn');
                if (addSec) addSec.style.display = canEditSections ? '' : 'none';
                var toggle = document.getElementById('toggle-edit-layout');
                if (toggle) toggle.style.display = canEditFloor ? '' : 'none';
                setEditUi();
                var resetBtn = document.getElementById('reset-btn');
                if (resetBtn) resetBtn.style.display = canResetFloor ? '' : 'none';
                var turnGoal = document.getElementById('turn-goal');
                if (turnGoal) {
                    turnGoal.disabled = !canTurnGoal;
                    turnGoal.readOnly = !canTurnGoal;
                }
                renderSectionsPanel();
                paintDetail();
            }

            function loadPermissions() {
                if (window.PbjPerms && window.PbjPerms.ready) {
                    return window.PbjPerms.ready.then(function () {
                        applyFloorPerms();
                    });
                }
                return fetch('permissions-api.php', { credentials: 'same-origin', cache: 'no-store' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.ok && data.grants) {
                            canEditFloor = !!data.grants['foh.floor.edit_layout'];
                            canEditSections = !!data.grants['foh.floor.edit_sections'];
                            canSelectFill = !!data.grants['foh.floor.select_fill'];
                            canFillSections = !!data.grants['foh.floor.fill_sections'];
                            canResetFloor = !!data.grants['foh.floor.reset'];
                            canTurnGoal = !!data.grants['foh.floor.turn_goal'];
                        }
                        applyFloorPerms();
                    })
                    .catch(function () {
                        setEditUi();
                        renderSectionsPanel();
                        paintDetail();
                    });
            }

            renderLayout();
            renderSectionsPanel();
            setEditUi();
            loadPermissions();
            document.addEventListener('pbj-perms-ready', applyFloorPerms);
            paintFloor();
            paintDetail();
            paintTurns();

            // Kitchen live sync
            function setSyncPill(info) {
                var pill = document.getElementById('sync-pill');
                var textEl = document.getElementById('sync-pill-text');
                if (!pill || !textEl) return;
                pill.classList.remove('offline', 'syncing');
                if (info.kind === 'offline') pill.classList.add('offline');
                if (info.kind === 'syncing') pill.classList.add('syncing');
                textEl.textContent = info.text || '';
            }
            function applyRemoteFloor(payload) {
                if (!payload) return;
                // Prefer newer layout
                var remoteLayout = Array.isArray(payload.layout) ? payload.layout : null;
                var remoteLayoutAt = payload.layoutAt || 0;
                var keepLayout = state.layout;
                var keepLayoutAt = state.layoutAt || 0;
                if (remoteLayout && remoteLayout.length && remoteLayoutAt >= keepLayoutAt) {
                    keepLayout = remoteLayout;
                    keepLayoutAt = remoteLayoutAt;
                }
                var remoteSecAt = payload.sectionsAt || 0;
                var keepSec = state.sections;
                var keepSecAt = state.sectionsAt || 0;
                if (Array.isArray(payload.sections) && payload.sections.length && remoteSecAt >= keepSecAt) {
                    keepSec = payload.sections;
                    keepSecAt = remoteSecAt;
                }
                state = {
                    layout: keepLayout && keepLayout.length ? keepLayout : JSON.parse(JSON.stringify(DEFAULT_LAYOUT)),
                    layoutAt: keepLayoutAt,
                    sections: keepSec && keepSec.length ? keepSec : JSON.parse(JSON.stringify(DEFAULT_SECTIONS)),
                    sectionsAt: keepSecAt,
                    tables: payload.tables || state.tables || {},
                    assignments: payload.assignments || state.assignments || {},
                    turnGoal: payload.turnGoal != null ? payload.turnGoal : (state.turnGoal || 45),
                    structureAt: payload.structureAt || Date.now(),
                    assignmentsAt: payload.assignmentsAt || 0,
                    turnGoalAt: payload.turnGoalAt || 0
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                try {
                    document.querySelectorAll('[data-assign]').forEach(function (inp) {
                        var sec = inp.getAttribute('data-assign');
                        inp.value = (state.assignments && state.assignments[sec]) || '';
                    });
                    var goal = document.getElementById('turn-goal');
                    if (goal) goal.value = state.turnGoal || 45;
                } catch (e) {}
                renderLayout();
                renderSectionsPanel();
                paintFloor();
                paintDetail();
                paintTurns();
            }
            if (window.PbjSharedState) {
                window._floorShared = new PbjSharedState({
                    key: 'showtime_floor_v1',
                    pollMs: 3000,
                    onStatus: setSyncPill,
                    onRemote: function (payload) { applyRemoteFloor(payload); }
                });
                window._floorShared.bootstrap(
                    function () { return state; },
                    function (payload) { applyRemoteFloor(payload); }
                ).then(function () { window._floorShared.startPolling(); });
            } else {
                setSyncPill({ kind: 'offline', text: 'Local only' });
            }

        })();
    </script>
</body>
</html>
