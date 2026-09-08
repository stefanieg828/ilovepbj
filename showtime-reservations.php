<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Reservations, Waitlist & To-Gos' : 'Reservations, Waitlist & To-Gos'; ?> • <?php echo pbj_hub_label('foh'); ?> • ilovepbj ops</title>

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
            font-size: 2.3rem;
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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        @media (max-width: 520px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .tabs { flex-wrap: wrap; }
            .tab { min-width: 30%; flex: 1 1 30%; font-size: 0.9rem; padding: 12px 6px; }
        }
        .item.togo-new { border-left-color: #5B8DEF; }
        .item.togo-making { border-left-color: #E8A838; }
        .item.togo-ready { border-left-color: #2E9B63; }
        .item.togo-done { opacity: 0.72; }
        .badge.ready-now {
            <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;
            <?php else: ?>background: #E8F5E9; color: #1B5E20;<?php endif; ?>
        }
        .togo-chips {
            display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 6px; align-items: center;
        }
        .togo-chips .chip-lbl {
            font-size: 0.85rem; opacity: 0.7; width: 100%; margin: 0 0 2px;
        }
        .togo-chips button {
            border: none; border-radius: 999px; padding: 8px 12px; font-size: 0.88rem; cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFF5F6; border: 1px solid #F3C5CC; color: #3a2f1f;
            <?php else: ?>font-family: 'Lora', serif; background: #EEF2F8; border: 1px solid #C5D0DE; color: #1A2A44;<?php endif; ?>
        }
        .togo-chips button:hover { opacity: 0.9; transform: translateY(-1px); }
        .togo-chips button.is-active {
            <?php if ($is_sweet): ?>background: #E55163; color: white; border-color: #E55163;
            <?php else: ?>background: #1A2A44; color: white; border-color: #1A2A44;<?php endif; ?>
        }
        .togo-time-hint {
            font-size: 0.85rem; opacity: 0.75; margin: 0 0 12px; min-height: 1.2em;
        }
        .ready-flash {
            display: none; position: fixed; top: 12px; left: 50%; transform: translateX(-50%);
            z-index: 2200; max-width: 92vw; padding: 12px 18px; border-radius: 999px;
            font-size: 0.95rem; box-shadow: 0 8px 24px rgba(0,0,0,0.18); text-align: center;
            <?php if ($is_sweet): ?>background: #E55163; color: white; font-family: 'DreamingOutLoudPro', serif;
            <?php else: ?>background: #1A2A44; color: white; font-family: 'Lora', serif;<?php endif; ?>
        }
        .ready-flash.show { display: block; animation: readyPop 0.35s ease-out; }
        @keyframes readyPop {
            from { opacity: 0; transform: translateX(-50%) translateY(-8px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        .item.togo-ready.flash-ready {
            animation: togoReadyPulse 1.2s ease-in-out 2;
        }
        @keyframes togoReadyPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(46,155,99,0.35); }
            50% { box-shadow: 0 0 0 8px rgba(46,155,99,0); }
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

        .card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 14px;
        }

        .card h2 {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 1.5rem;
            margin: 0 0 14px;
        }

        .hint {
            font-size: 0.95rem;
            opacity: 0.7;
            margin: 0 0 14px;
            line-height: 1.4;
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

        .field.full {
            flex: 1 1 100%;
        }

        .field label {
            display: block;
            font-size: 0.85rem;
            opacity: 0.65;
            margin-bottom: 4px;
        }

        .field input, .field select, .field textarea {
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
            min-height: 70px;
            resize: vertical;
        }

        .field input:focus, .field select:focus, .field textarea:focus {
            outline: none;
            border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
        }

        .btn {
            border: none;
            border-radius: 14px;
            padding: 14px 16px;
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

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 6px;
        }

        .form-actions .btn {
            flex: 1;
        }

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
        .modal-backdrop.show { display: flex; }
        .modal {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 480px;
            padding: 22px 20px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal h2 {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 1.45rem;
            margin: 0 0 8px;
        }
        .modal .hint {
            font-size: 0.95rem;
            opacity: 0.75;
            margin: 0 0 14px;
            line-height: 1.4;
        }
        .modal .field { margin-bottom: 12px; }
        .modal .field label {
            display: block;
            font-size: 0.85rem;
            opacity: 0.65;
            margin-bottom: 4px;
        }
        .modal .field select,
        .modal .field input {
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
        .modal-actions { display: flex; gap: 10px; margin-top: 6px; }
        .modal-actions .btn { flex: 1; }

        .list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .item {
            background: white;
            border-radius: 18px;
            padding: 16px 18px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
        }

        .item.wait { border-left-color: #E8A838; }
        .item.seated { border-left-color: #7BC67E; opacity: 0.85; }
        .item.cancelled, .item.no-show { border-left-color: #B0B0B0; opacity: 0.65; }
        .item.notified { border-left-color: #5B9BD5; }

        .item-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 8px;
        }

        .item-name {
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

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.8rem;
            white-space: nowrap;
            background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>;
        }

        .item-meta {
            font-size: 0.98rem;
            line-height: 1.45;
            opacity: 0.85;
            margin-bottom: 6px;
        }

        .item-notes {
            font-size: 0.95rem;
            opacity: 0.75;
            font-style: italic;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .item-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .empty {
            text-align: center;
            padding: 36px 20px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            opacity: 0.85;
            line-height: 1.45;
        }

        .empty .icon {
            font-size: 2.2rem;
            margin-bottom: 8px;
        }

        .wait-quote {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 14px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .wait-quote label {
            font-size: 0.9rem;
            opacity: 0.7;
            white-space: nowrap;
        }

        .wait-quote input {
            width: 80px;
            border-radius: 10px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 8px 10px;
            font-size: 1.1rem;
            text-align: center;
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

        .wait-quote .unit {
            opacity: 0.7;
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

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .filter-chip {
            border: none;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 0.9rem;
            cursor: pointer;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
                color: #3a2f1f;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
        }

        .link-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .chip-link {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 0.9rem;
            <?php if ($is_sweet): ?>
                font-family: 'DreamingOutLoudPro', serif;
                background: #FFF5F6;
                color: #E55163;
                border: 1px solid #F3C5CC;
            <?php else: ?>
                font-family: 'Lora', serif;
                background: #EEF2F8;
                color: #1A2A44;
                border: 1px solid #C5D0DE;
            <?php endif; ?>
        }

        .filter-chip.active {
            <?php if ($is_sweet): ?>
                background: #E55163;
                color: white;
            <?php else: ?>
                background: #1A2A44;
                color: white;
            <?php endif; ?>
        }

        .time-overdue {
            color: #C62828;
            font-weight: 600;
        }

        .print-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }
        .print-bar .btn {
            flex: 1;
            min-width: 120px;
        }
        .print-date-field {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1 1 100%;
            background: white;
            border-radius: 14px;
            padding: 10px 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .print-date-field label {
            font-size: 0.9rem;
            opacity: 0.7;
            white-space: nowrap;
        }
        .print-date-field input {
            flex: 1;
            min-width: 0;
            border-radius: 10px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 8px 10px;
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
        @media print {
            .no-print, .back-link, .tabs, .print-bar, .filter-row, .form-actions,
            .actions-bar, .bottom-nav, #bottom-nav, nav, .sync-pill, .card form,
            .wait-quote, .item-actions, .modal-backdrop { display: none !important; }
            body { padding-bottom: 0; background: white; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .item { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header no-print">
        <a href="/FOH" class="back-link">← <?php echo pbj_back_to_hub('foh'); ?></a>
        <h1><?php echo $is_sweet ? 'Res · Wait · To-Gos' : 'Res · Wait · To-Gos'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Bookings, waitlist & grab-and-go board' : 'Bookings, waitlist, and to-go orders'; ?></p>
    </div>

    <div class="content">
        <div class="tabs no-print">
            <button type="button" class="tab active" data-tab="reservations"><?php echo $is_sweet ? '📅 Res' : 'Res'; ?></button>
            <button type="button" class="tab" data-tab="waitlist"><?php echo $is_sweet ? '⏳ Wait' : 'Wait'; ?></button>
            <button type="button" class="tab" data-tab="togos"><?php echo $is_sweet ? '🛍️ To-Gos' : 'To-Gos'; ?></button>
        </div>

        <div class="stats-row no-print">
            <div class="stat">
                <div class="num" id="stat-res">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Upcoming res' : 'Upcoming res'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-wait">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'On waitlist' : 'On waitlist'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-covers">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Waiting covers' : 'Waiting covers'; ?></div>
            </div>
            <div class="stat">
                <div class="num" id="stat-togo">0</div>
                <div class="lbl"><?php echo $is_sweet ? 'Active to-gos' : 'Active to-gos'; ?></div>
            </div>
        </div>

        <div class="print-bar no-print">
            <div class="print-date-field">
                <label for="print-date"><?php echo $is_sweet ? 'List date' : 'List date'; ?></label>
                <input type="date" id="print-date">
            </div>
            <button type="button" class="btn btn-secondary" id="print-daily-res-btn" data-perm="foh.res.print"><?php echo $is_sweet ? '🖨️ Print daily reservations' : 'Print daily reservations'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-waitlist-btn" data-perm="foh.res.print"><?php echo $is_sweet ? '🖨️ Print waitlist' : 'Print waitlist'; ?></button>
            <button type="button" class="btn btn-secondary" id="print-togo-btn" data-perm="foh.res.print"><?php echo $is_sweet ? '🖨️ Print to-gos' : 'Print to-gos'; ?></button>
            <button type="button" class="btn btn-ghost" id="export-daily-res-btn" data-perm="foh.res.print"><?php echo $is_sweet ? '⬇️ Save day (CSV)' : 'Save day (CSV)'; ?></button>
        </div>

        <!-- Reservations -->
        <div class="panel active" id="panel-reservations">
            <div class="card no-print">
                <h2><?php echo $is_sweet ? 'Add Reservation' : 'Add Reservation'; ?></h2>
                <form id="res-form">
                    <div class="field-row">
                        <div class="field">
                            <label for="res-name"><?php echo $is_sweet ? 'Guest name' : 'Guest name'; ?></label>
                            <input type="text" id="res-name" required placeholder="<?php echo $is_sweet ? 'Name on the book' : 'Guest name'; ?>">
                        </div>
                        <div class="field">
                            <label for="res-party"><?php echo $is_sweet ? 'Party size' : 'Party size'; ?></label>
                            <input type="number" id="res-party" min="1" max="30" value="2" required>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="res-date"><?php echo $is_sweet ? 'Date' : 'Date'; ?></label>
                            <input type="date" id="res-date" required>
                        </div>
                        <div class="field">
                            <label for="res-time"><?php echo $is_sweet ? 'Time' : 'Time'; ?></label>
                            <input type="time" id="res-time" required value="18:00">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="res-phone"><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label>
                            <input type="tel" id="res-phone" placeholder="<?php echo $is_sweet ? 'Optional' : 'Optional'; ?>">
                        </div>
                        <div class="field">
                            <label for="res-table"><?php echo $is_sweet ? 'Pref. table / section' : 'Pref. table / section'; ?></label>
                            <input type="text" id="res-table" placeholder="<?php echo $is_sweet ? 'e.g. Window, T7' : 'e.g. Window, T7'; ?>">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field full">
                            <label for="res-notes"><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label>
                            <textarea id="res-notes" placeholder="<?php echo $is_sweet ? 'Birthday, high chair, allergy, celebration vibes…' : 'Birthday, high chair, allergy…'; ?>"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save reservation ✨' : 'Save reservation'; ?></button>
                    </div>
                </form>
            </div>

            <div class="filter-row no-print" id="res-filters">
                <button type="button" class="filter-chip active" data-filter="upcoming"><?php echo $is_sweet ? 'Upcoming' : 'Upcoming'; ?></button>
                <button type="button" class="filter-chip" data-filter="today"><?php echo $is_sweet ? 'Today' : 'Today'; ?></button>
                <button type="button" class="filter-chip" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
                <button type="button" class="filter-chip" data-filter="done"><?php echo $is_sweet ? 'Seated / done' : 'Seated / done'; ?></button>
                <a class="chip-link" href="/FOH/floor-plan"><?php echo $is_sweet ? '🪑 Floor plan' : '🪑 Floor plan'; ?></a>
            </div>

            <div class="list" id="res-list"></div>
        </div>

        <!-- Waitlist -->
        <div class="panel" id="panel-waitlist">
            <div class="wait-quote no-print">
                <label for="quote-mins"><?php echo $is_sweet ? 'Current quote' : 'Current quote'; ?></label>
                <input type="number" id="quote-mins" min="0" max="180" value="20">
                <span class="unit"><?php echo $is_sweet ? 'min wait' : 'min wait'; ?></span>
            </div>

            <div class="card no-print">
                <h2><?php echo $is_sweet ? 'Add to Waitlist' : 'Add to Waitlist'; ?></h2>
                <form id="wait-form">
                    <div class="field-row">
                        <div class="field">
                            <label for="wait-name"><?php echo $is_sweet ? 'Guest name' : 'Guest name'; ?></label>
                            <input type="text" id="wait-name" required placeholder="<?php echo $is_sweet ? 'Name for the list' : 'Guest name'; ?>">
                        </div>
                        <div class="field">
                            <label for="wait-party"><?php echo $is_sweet ? 'Party size' : 'Party size'; ?></label>
                            <input type="number" id="wait-party" min="1" max="30" value="2" required>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="wait-phone"><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label>
                            <input type="tel" id="wait-phone" placeholder="<?php echo $is_sweet ? 'For a text when ready' : 'For notification'; ?>">
                        </div>
                        <div class="field">
                            <label for="wait-quoted"><?php echo $is_sweet ? 'Quoted wait (min)' : 'Quoted wait (min)'; ?></label>
                            <input type="number" id="wait-quoted" min="0" max="180" value="20">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field full">
                            <label for="wait-notes"><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label>
                            <textarea id="wait-notes" placeholder="<?php echo $is_sweet ? 'Stroller, high chair, outdoor pref…' : 'Stroller, high chair, outdoor pref…'; ?>"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Add to waitlist ⏳' : 'Add to waitlist'; ?></button>
                    </div>
                </form>
            </div>

            <div class="filter-row no-print" id="wait-filters">
                <button type="button" class="filter-chip active" data-filter="waiting"><?php echo $is_sweet ? 'Waiting' : 'Waiting'; ?></button>
                <button type="button" class="filter-chip" data-filter="notified"><?php echo $is_sweet ? 'Notified' : 'Notified'; ?></button>
                <button type="button" class="filter-chip" data-filter="all"><?php echo $is_sweet ? 'All today' : 'All today'; ?></button>
                <a class="chip-link" href="/FOH/floor-plan"><?php echo $is_sweet ? '🪑 Floor plan' : '🪑 Floor plan'; ?></a>
            </div>

            <div class="list" id="wait-list"></div>
        </div>

        <!-- To-Gos -->
        <div class="panel" id="panel-togos">
            <div class="card no-print">
                <h2><?php echo $is_sweet ? 'Add To-Go order' : 'Add To-Go order'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'Phone-ins, walk-ups, online, DoorDash — log it here so the house sees the same board 🛍️'
                    : 'Log phone, walk-up, online, or delivery-app pickups on one kitchen-synced board.'; ?></p>
                <form id="togo-form">
                    <div class="field-row">
                        <div class="field">
                            <label for="togo-name"><?php echo $is_sweet ? 'Guest / name' : 'Guest name'; ?></label>
                            <input type="text" id="togo-name" required placeholder="<?php echo $is_sweet ? 'Name on the bag' : 'Guest name'; ?>">
                        </div>
                        <div class="field">
                            <label for="togo-phone"><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label>
                            <input type="tel" id="togo-phone" placeholder="<?php echo $is_sweet ? 'For ready text when it’s up' : 'For ready notification'; ?>">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="togo-channel"><?php echo $is_sweet ? 'Channel' : 'Channel'; ?></label>
                            <select id="togo-channel">
                                <option value="phone"><?php echo $is_sweet ? 'Phone call' : 'Phone'; ?></option>
                                <option value="walkin"><?php echo $is_sweet ? 'Walk-up / counter' : 'Walk-up'; ?></option>
                                <option value="online"><?php echo $is_sweet ? 'Online order' : 'Online'; ?></option>
                                <option value="doordash">DoorDash</option>
                                <option value="ubereats">Uber Eats</option>
                                <option value="grubhub">Grubhub</option>
                                <option value="other"><?php echo $is_sweet ? 'Other' : 'Other'; ?></option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="togo-ticket"><?php echo $is_sweet ? 'Ticket / order #' : 'Ticket / order #'; ?></label>
                            <input type="text" id="togo-ticket" placeholder="<?php echo $is_sweet ? 'Optional' : 'Optional'; ?>">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="togo-date"><?php echo $is_sweet ? 'Date' : 'Date'; ?></label>
                            <input type="date" id="togo-date" required>
                        </div>
                        <div class="field">
                            <label for="togo-time"><?php echo $is_sweet ? 'Promised time' : 'Promised time'; ?></label>
                            <input type="time" id="togo-time" required value="12:00">
                        </div>
                    </div>
                    <div class="togo-chips no-print" id="togo-time-chips">
                        <span class="chip-lbl"><?php echo $is_sweet ? 'Promised in…' : 'Ready in…'; ?></span>
                        <button type="button" data-mins="10">+10 min</button>
                        <button type="button" data-mins="15">+15 min</button>
                        <button type="button" data-mins="20">+20 min</button>
                        <button type="button" data-mins="30">+30 min</button>
                        <button type="button" data-mins="45">+45 min</button>
                    </div>
                    <p class="togo-time-hint no-print" id="togo-time-hint"></p>
                    <div class="field-row">
                        <div class="field full">
                            <label for="togo-items"><?php echo $is_sweet ? 'Items / order notes' : 'Items / order notes'; ?></label>
                            <textarea id="togo-items" placeholder="<?php echo $is_sweet ? '2 burgers no onion, side fries, extra ranch…' : 'Items, mods, bag notes…'; ?>"></textarea>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field full">
                            <label for="togo-notes"><?php echo $is_sweet ? 'House notes' : 'House notes'; ?></label>
                            <input type="text" id="togo-notes" placeholder="<?php echo $is_sweet ? 'Paid, card on file, VIP…' : 'Paid, VIP, special…'; ?>">
                        </div>
                    </div>
                    <div class="form-actions" style="display:flex;flex-wrap:wrap;gap:8px;">
                        <button type="submit" class="btn btn-primary" style="flex:2;" data-perm="foh.togo.add"><?php echo $is_sweet ? 'Add to-go 🛍️' : 'Add to-go'; ?></button>
                        <button type="button" class="btn btn-secondary" style="flex:1;" id="togo-dup-last" data-perm="foh.togo.add"><?php echo $is_sweet ? 'Duplicate last' : 'Duplicate last'; ?></button>
                    </div>
                </form>
            </div>

            <div class="filter-row no-print" id="togo-filters">
                <button type="button" class="filter-chip active" data-filter="active"><?php echo $is_sweet ? 'Active' : 'Active'; ?></button>
                <button type="button" class="filter-chip" data-filter="ready"><?php echo $is_sweet ? 'Ready' : 'Ready'; ?></button>
                <button type="button" class="filter-chip" data-filter="today"><?php echo $is_sweet ? 'Today' : 'Today'; ?></button>
                <button type="button" class="filter-chip" data-filter="done"><?php echo $is_sweet ? 'Done' : 'Done'; ?></button>
                <button type="button" class="btn btn-small btn-ghost" id="togo-clear-picked" data-perm="foh.togo.remove" style="margin-left:auto;"><?php echo $is_sweet ? 'Clear picked-up today' : 'Clear picked-up today'; ?></button>
            </div>

            <div class="list" id="togo-list"></div>
        </div>

        <div class="actions-bar no-print">
            <button type="button" class="btn btn-secondary" id="clear-done-btn"><?php echo $is_sweet ? 'Clear finished' : 'Clear finished'; ?></button>
            <a href="/FOH" class="btn btn-primary"><?php echo pbj_back_to_hub('foh'); ?></a>
        </div>
    </div>

    <div class="modal-backdrop" id="seat-modal">
        <div class="modal">
            <h2 id="seat-modal-title"><?php echo $is_sweet ? 'Seat party' : 'Seat party'; ?></h2>
            <p class="hint" id="seat-modal-hint"><?php echo $is_sweet ? 'Which table are they sitting at?' : 'Which table are they sitting at?'; ?></p>
            <input type="hidden" id="seat-target-type">
            <input type="hidden" id="seat-target-id">
            <div class="field">
                <label for="seat-table-select"><?php echo $is_sweet ? 'Table from floor plan' : 'Table from floor plan'; ?></label>
                <select id="seat-table-select">
                    <option value=""><?php echo $is_sweet ? '— Choose a table —' : '— Choose a table —'; ?></option>
                </select>
            </div>
            <div class="field">
                <label for="seat-table-custom"><?php echo $is_sweet ? 'Or type table / section' : 'Or type table / section'; ?></label>
                <input type="text" id="seat-table-custom" maxlength="40" placeholder="<?php echo $is_sweet ? 'e.g. 7, Patio 2, Bar' : 'e.g. 7, Patio 2, Bar'; ?>">
            </div>
            <p class="hint" style="font-size:0.88rem;margin-top:-4px;"><?php echo $is_sweet ? 'Picking a floor-plan table also marks it seated on the map ✨' : 'Picking a floor-plan table also marks it seated on the map.'; ?></p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="seat-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                <button type="button" class="btn btn-primary" id="seat-confirm"><?php echo $is_sweet ? 'Seat party ✨' : 'Seat party'; ?></button>
            </div>
        </div>
    </div>

    <div class="ready-flash no-print" id="ready-flash" role="status" aria-live="polite"></div>
    <div class="sync-pill no-print syncing" id="sync-pill" style="position:fixed;bottom:100px;left:50%;transform:translateX(-50%);z-index:50;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:0.85rem;box-shadow:0 4px 14px rgba(0,0,0,0.12);background:#fff;opacity:0.95;">
        <span class="dot" style="width:8px;height:8px;border-radius:50%;background:#2E9B63;"></span>
        <span id="sync-pill-text">Connecting…</span>
    </div>
    <?php include 'bottom-nav.php'; ?>

    <script src="shared-state.js?v=3"></script>
    <script>
        (function () {
            const STORAGE_KEY = 'pbj_showtime_reservations_waitlist_v2';
            const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyResPermUi() {
                var resForm = document.getElementById('res-form');
                var waitForm = document.getElementById('wait-form');
                var togoForm = document.getElementById('togo-form');
                if (resForm) resForm.style.display = canP('foh.res.add') ? '' : 'none';
                if (waitForm) waitForm.style.display = canP('foh.wait.add') ? '' : 'none';
                if (togoForm) togoForm.style.display = canP('foh.togo.add') ? '' : 'none';
                if (window.PbjPerms) window.PbjPerms.applyDom();
            }


            const labels = {
                emptyRes: isSweet ? 'No reservations yet — the book is wide open ✨' : 'No reservations yet.',
                emptyWait: isSweet ? 'Waitlist is clear — walk-ins welcome! 💕' : 'Waitlist is empty.',
                emptyTogo: isSweet ? 'No to-gos yet — board is clear 🛍️' : 'No to-go orders yet.',
                seated: isSweet ? 'Seated' : 'Seated',
                cancelled: isSweet ? 'Cancelled' : 'Cancelled',
                noshow: isSweet ? 'No-show' : 'No-show',
                notified: isSweet ? 'Notified' : 'Notified',
                waiting: isSweet ? 'Waiting' : 'Waiting',
                booked: isSweet ? 'Booked' : 'Booked',
                togoNew: isSweet ? 'New' : 'New',
                togoMaking: isSweet ? 'Making' : 'Making',
                togoReady: isSweet ? 'Ready' : 'Ready',
                togoPicked: isSweet ? 'Picked up' : 'Picked up',
                party: isSweet ? 'party of' : 'party of',
                quoted: isSweet ? 'quoted' : 'quoted',
                waitingFor: isSweet ? 'waiting' : 'waiting',
                overdue: isSweet ? 'past quote' : 'past quote'
            };

            const TOGO_CHANNELS = {
                phone: isSweet ? 'Phone' : 'Phone',
                walkin: isSweet ? 'Walk-up' : 'Walk-up',
                online: isSweet ? 'Online' : 'Online',
                doordash: 'DoorDash',
                ubereats: 'Uber Eats',
                grubhub: 'Grubhub',
                other: isSweet ? 'Other' : 'Other'
            };

            let state = loadState();
            let resFilter = 'upcoming';
            let waitFilter = 'waiting';
            let togoFilter = 'active';
            const TOGO_PREFS_KEY = 'pbj_togo_prefs_v1';
            var knownReadyIds = {};
            var readyAlertPrimed = false;

            function loadTogoPrefs() {
                try {
                    return JSON.parse(localStorage.getItem(TOGO_PREFS_KEY) || '{}') || {};
                } catch (e) { return {}; }
            }
            function saveTogoPrefs(p) {
                try { localStorage.setItem(TOGO_PREFS_KEY, JSON.stringify(p || {})); } catch (e) {}
            }
            function pad2(n) {
                n = String(Math.floor(Number(n) || 0));
                return n.length < 2 ? '0' + n : n;
            }
            function timePlusMinutes(mins) {
                var d = new Date();
                d.setTime(d.getTime() + (parseInt(mins, 10) || 0) * 60000);
                return pad2(d.getHours()) + ':' + pad2(d.getMinutes());
            }
            function setTogoPromisedIn(mins) {
                mins = parseInt(mins, 10);
                if (isNaN(mins) || mins < 0) mins = 20;
                var tm = document.getElementById('togo-time');
                var dt = document.getElementById('togo-date');
                if (!tm) return false;
                // If adding minutes rolls past midnight, bump date
                var now = new Date();
                var then = new Date(now.getTime() + mins * 60000);
                if (dt) {
                    dt.value = then.getFullYear() + '-' + pad2(then.getMonth() + 1) + '-' + pad2(then.getDate());
                }
                var val = pad2(then.getHours()) + ':' + pad2(then.getMinutes());
                tm.value = val;
                // Some mobile browsers only paint after focus/blur or events
                try {
                    tm.dispatchEvent(new Event('input', { bubbles: true }));
                    tm.dispatchEvent(new Event('change', { bubbles: true }));
                } catch (e) {
                    try {
                        var ev = document.createEvent('HTMLEvents');
                        ev.initEvent('change', true, false);
                        tm.dispatchEvent(ev);
                    } catch (e2) {}
                }
                // Visual chip state
                var chips = document.getElementById('togo-time-chips');
                if (chips) {
                    chips.querySelectorAll('[data-mins]').forEach(function (b) {
                        b.classList.toggle('is-active', parseInt(b.getAttribute('data-mins'), 10) === mins);
                    });
                }
                var hint = document.getElementById('togo-time-hint');
                if (hint) {
                    hint.textContent = isSweet
                        ? ('Promised in ' + mins + ' min → ' + formatTime(val))
                        : ('Ready in ' + mins + ' min → ' + formatTime(val));
                }
                var prefs = loadTogoPrefs();
                prefs.defaultMins = mins;
                saveTogoPrefs(prefs);
                return true;
            }
            function playReadyChime() {
                try {
                    var Ctx = window.AudioContext || window.webkitAudioContext;
                    if (!Ctx) return;
                    var ctx = new Ctx();
                    var o = ctx.createOscillator();
                    var g = ctx.createGain();
                    o.type = 'sine';
                    o.frequency.value = 880;
                    g.gain.value = 0.0001;
                    o.connect(g);
                    g.connect(ctx.destination);
                    var t0 = ctx.currentTime;
                    g.gain.exponentialRampToValueAtTime(0.12, t0 + 0.02);
                    g.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.35);
                    o.start(t0);
                    o.stop(t0 + 0.4);
                    setTimeout(function () {
                        try { ctx.close(); } catch (e) {}
                    }, 500);
                } catch (e) {}
            }
            function flashReadyBanner(name) {
                var el = document.getElementById('ready-flash');
                if (!el) return;
                el.textContent = isSweet
                    ? ('🛍️ To-go READY' + (name ? ': ' + name : '') + '!')
                    : ('To-go READY' + (name ? ': ' + name : ''));
                el.classList.add('show');
                clearTimeout(flashReadyBanner._t);
                flashReadyBanner._t = setTimeout(function () {
                    el.classList.remove('show');
                }, 3200);
            }
            function notifyTogoReady(t, opts) {
                opts = opts || {};
                if (!t) return;
                var msg = isSweet
                    ? ('Hi' + (t.name ? ' ' + t.name : '') + '! Your order is ready for pickup 🛍️')
                    : ('Hi' + (t.name ? ' ' + t.name : '') + ', your order is ready for pickup.');
                if (t.ticket) msg += isSweet ? (' (#' + t.ticket + ')') : (' (#' + t.ticket + ')');
                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(msg).catch(function () {});
                    }
                } catch (e) {}
                var phone = String(t.phone || '').replace(/[^\d+]/g, '');
                if (phone && !opts.skipSms) {
                    // Prefer sms: deep link (device Messages)
                    var link = 'sms:' + encodeURIComponent(phone) + '?&body=' + encodeURIComponent(msg);
                    try { window.location.href = link; } catch (e2) {
                        try { window.open(link, '_blank'); } catch (e3) {}
                    }
                } else if (!phone && !opts.silent) {
                    alert(isSweet
                        ? 'No phone on this order — ready message copied if clipboard allows 💕'
                        : 'No phone on this order. Message copied if clipboard allows.');
                }
                t.notifiedReadyAt = Date.now();
                t.updatedAt = Date.now();
            }
            function checkNewReadyAlerts() {
                var ready = (state.togos || []).filter(function (t) { return t && t.status === 'ready'; });
                if (!readyAlertPrimed) {
                    ready.forEach(function (t) { knownReadyIds[String(t.id)] = 1; });
                    readyAlertPrimed = true;
                    return;
                }
                ready.forEach(function (t) {
                    var id = String(t.id);
                    if (!knownReadyIds[id]) {
                        knownReadyIds[id] = 1;
                        playReadyChime();
                        flashReadyBanner(t.name || '');
                    }
                });
                // drop ids no longer ready
                var keep = {};
                ready.forEach(function (t) { keep[String(t.id)] = 1; });
                knownReadyIds = keep;
            }

            function uid() {
                return Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
            }

            function emptyTombstones() {
                return { deletedReservationIds: {}, deletedWaitlistIds: {}, deletedTogoIds: {} };
            }

            function normalizeDeletedMap(map) {
                var out = {};
                if (!map || typeof map !== 'object') return out;
                Object.keys(map).forEach(function (k) {
                    var id = String(k);
                    if (!id) return;
                    var ts = parseInt(map[k], 10) || 0;
                    if (ts > 0) out[id] = ts;
                });
                return out;
            }

            /** Drop list rows that were hard-deleted (tombstone >= row timestamp). */
            function applyTombstonesToLists(s) {
                s = s || state;
                if (!s.deletedReservationIds) s.deletedReservationIds = {};
                if (!s.deletedWaitlistIds) s.deletedWaitlistIds = {};
                if (!s.deletedTogoIds) s.deletedTogoIds = {};
                s.deletedReservationIds = normalizeDeletedMap(s.deletedReservationIds);
                s.deletedWaitlistIds = normalizeDeletedMap(s.deletedWaitlistIds);
                s.deletedTogoIds = normalizeDeletedMap(s.deletedTogoIds);
                s.reservations = (s.reservations || []).filter(function (r) {
                    var id = String(r.id || '');
                    if (!id) return false;
                    var delAt = s.deletedReservationIds[id];
                    if (!delAt) return true;
                    var ts = parseInt(r.updatedAt || r.addedAt || r.createdAt || 0, 10) || 0;
                    return ts > delAt;
                });
                s.waitlist = (s.waitlist || []).filter(function (w) {
                    var id = String(w.id || '');
                    if (!id) return false;
                    var delAt = s.deletedWaitlistIds[id];
                    if (!delAt) return true;
                    var ts = parseInt(w.updatedAt || w.addedAt || w.createdAt || 0, 10) || 0;
                    return ts > delAt;
                });
                s.togos = (s.togos || []).filter(function (t) {
                    var id = String(t.id || '');
                    if (!id) return false;
                    var delAt = s.deletedTogoIds[id];
                    if (!delAt) return true;
                    var ts = parseInt(t.updatedAt || t.addedAt || t.createdAt || 0, 10) || 0;
                    return ts > delAt;
                });
                return s;
            }

            function tombstoneReservation(id) {
                id = String(id || '');
                if (!id) return;
                if (!state.deletedReservationIds) state.deletedReservationIds = {};
                state.deletedReservationIds[id] = Date.now();
                state.reservations = (state.reservations || []).filter(function (x) {
                    return String(x.id) !== id;
                });
            }

            function tombstoneWaitlist(id) {
                id = String(id || '');
                if (!id) return;
                if (!state.deletedWaitlistIds) state.deletedWaitlistIds = {};
                state.deletedWaitlistIds[id] = Date.now();
                state.waitlist = (state.waitlist || []).filter(function (x) {
                    return String(x.id) !== id;
                });
            }

            function tombstoneTogo(id) {
                id = String(id || '');
                if (!id) return;
                if (!state.deletedTogoIds) state.deletedTogoIds = {};
                state.deletedTogoIds[id] = Date.now();
                state.togos = (state.togos || []).filter(function (x) {
                    return String(x.id) !== id;
                });
            }

            function loadState() {
                try {
                    const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
                    var s = {
                        reservations: raw.reservations || [],
                        waitlist: raw.waitlist || [],
                        togos: raw.togos || [],
                        quoteMins: raw.quoteMins != null ? raw.quoteMins : 20,
                        deletedReservationIds: normalizeDeletedMap(raw.deletedReservationIds),
                        deletedWaitlistIds: normalizeDeletedMap(raw.deletedWaitlistIds),
                        deletedTogoIds: normalizeDeletedMap(raw.deletedTogoIds),
                        structureAt: raw.structureAt || 0,
                        quoteMinsAt: raw.quoteMinsAt || 0
                    };
                    return applyTombstonesToLists(s);
                } catch (e) {
                    return {
                        reservations: [],
                        waitlist: [],
                        togos: [],
                        quoteMins: 20,
                        deletedReservationIds: {},
                        deletedWaitlistIds: {},
                        deletedTogoIds: {}
                    };
                }
            }

            function saveState(opts) {
                opts = opts || {};
                var now = Date.now();
                state.structureAt = Math.max(state.structureAt || 0, now);
                if (!state.deletedReservationIds) state.deletedReservationIds = {};
                if (!state.deletedWaitlistIds) state.deletedWaitlistIds = {};
                if (!state.deletedTogoIds) state.deletedTogoIds = {};
                if (!state.togos) state.togos = [];
                // ensure rows have updatedAt for kitchen merge
                (state.reservations || []).forEach(function (r) { if (!r.updatedAt) r.updatedAt = r.addedAt || r.createdAt || now; });
                (state.waitlist || []).forEach(function (w) { if (!w.updatedAt) w.updatedAt = w.addedAt || now; });
                (state.togos || []).forEach(function (t) { if (!t.updatedAt) t.updatedAt = t.addedAt || t.createdAt || now; });
                applyTombstonesToLists(state);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                if (!opts.skipRemote && window._resShared) window._resShared.queuePush(state);
            }
            function stamp(row) {
                if (row && typeof row === 'object') row.updatedAt = Date.now();
                return row;
            }

            function escapeHtml(str) {
                return String(str || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function todayStr() {
                const d = new Date();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return d.getFullYear() + '-' + m + '-' + day;
            }

            function formatTime(t) {
                if (!t) return '';
                const parts = t.split(':');
                let h = parseInt(parts[0], 10);
                const m = parts[1] || '00';
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return h + ':' + m + ' ' + ampm;
            }

            function formatDate(d) {
                if (!d) return '';
                const dt = new Date(d + 'T12:00:00');
                return dt.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
            }

            function minutesWaiting(addedAt) {
                return Math.floor((Date.now() - addedAt) / 60000);
            }

            function isTogoActive(t) {
                return t && (t.status === 'new' || t.status === 'making' || t.status === 'ready');
            }

            function updateStats() {
                const today = todayStr();
                const upcomingRes = state.reservations.filter(function (r) {
                    return r.status === 'booked' && r.date >= today;
                });
                const waiting = state.waitlist.filter(function (w) {
                    return w.status === 'waiting' || w.status === 'notified';
                });
                const covers = waiting.reduce(function (sum, w) { return sum + (parseInt(w.party, 10) || 0); }, 0);
                const activeTogo = (state.togos || []).filter(isTogoActive);
                document.getElementById('stat-res').textContent = upcomingRes.length;
                document.getElementById('stat-wait').textContent = waiting.length;
                document.getElementById('stat-covers').textContent = covers;
                var st = document.getElementById('stat-togo');
                if (st) st.textContent = activeTogo.length;
            }

            function statusLabel(status) {
                const map = {
                    booked: labels.booked,
                    seated: labels.seated,
                    cancelled: labels.cancelled,
                    'no-show': labels.noshow,
                    waiting: labels.waiting,
                    notified: labels.notified,
                    new: labels.togoNew,
                    making: labels.togoMaking,
                    ready: labels.togoReady,
                    picked_up: labels.togoPicked
                };
                return map[status] || status;
            }

            function channelLabel(ch) {
                return TOGO_CHANNELS[ch] || ch || TOGO_CHANNELS.other;
            }

            function renderReservations() {
                const list = document.getElementById('res-list');
                const today = todayStr();
                let items = state.reservations.slice();

                items.sort(function (a, b) {
                    if (a.date !== b.date) return a.date < b.date ? -1 : 1;
                    return (a.time || '') < (b.time || '') ? -1 : 1;
                });

                if (resFilter === 'upcoming') {
                    items = items.filter(function (r) { return r.status === 'booked' && r.date >= today; });
                } else if (resFilter === 'today') {
                    items = items.filter(function (r) { return r.date === today && r.status !== 'cancelled'; });
                } else if (resFilter === 'done') {
                    items = items.filter(function (r) {
                        return r.status === 'seated' || r.status === 'cancelled' || r.status === 'no-show';
                    });
                }

                if (!items.length) {
                    list.innerHTML = '<div class="empty"><div class="icon">📋</div>' + escapeHtml(labels.emptyRes) + '</div>';
                    return;
                }

                list.innerHTML = items.map(function (r) {
                    const cls = r.status === 'booked' ? '' : r.status;
                    const meta = [
                        formatDate(r.date) + ' · ' + formatTime(r.time),
                        labels.party + ' ' + r.party,
                        r.phone || null,
                        r.tablePref ? (isSweet ? 'pref: ' : 'pref: ') + r.tablePref : null,
                        r.seatedTable ? (isSweet ? 'sat at: ' : 'sat at: ') + r.seatedTable : null
                    ].filter(Boolean).join(' · ');

                    let actions = '';
                    var resIdAttr = escapeHtml(String(r.id));
                    if (r.status === 'booked') {
                        actions =
                            '<button type="button" class="btn btn-small btn-primary" data-act="seat-res" data-id="' + resIdAttr + '">' + (isSweet ? 'Seat ✨' : 'Seat') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="noshow-res" data-id="' + resIdAttr + '">' + labels.noshow + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="cancel-res" data-id="' + resIdAttr + '">' + labels.cancelled + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="delete-res" data-id="' + resIdAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    } else {
                        actions =
                            '<button type="button" class="btn btn-small btn-ghost" data-act="reopen-res" data-id="' + resIdAttr + '">' + (isSweet ? 'Reopen' : 'Reopen') + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="delete-res" data-id="' + resIdAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    }

                    return (
                        '<div class="item ' + cls + '">' +
                            '<div class="item-top">' +
                                '<h3 class="item-name">' + escapeHtml(r.name) + '</h3>' +
                                '<span class="badge">' + escapeHtml(statusLabel(r.status)) + '</span>' +
                            '</div>' +
                            '<div class="item-meta">' + escapeHtml(meta) + '</div>' +
                            (r.notes ? '<div class="item-notes">' + escapeHtml(r.notes) + '</div>' : '') +
                            '<div class="item-actions">' + actions + '</div>' +
                        '</div>'
                    );
                }).join('');
            }

            function renderWaitlist() {
                const list = document.getElementById('wait-list');
                let items = state.waitlist.slice();

                items.sort(function (a, b) { return a.addedAt - b.addedAt; });

                if (waitFilter === 'waiting') {
                    items = items.filter(function (w) { return w.status === 'waiting'; });
                } else if (waitFilter === 'notified') {
                    items = items.filter(function (w) { return w.status === 'notified'; });
                } else {
                    items = items.filter(function (w) {
                        return w.status === 'waiting' || w.status === 'notified' || w.status === 'seated';
                    });
                }

                if (!items.length) {
                    list.innerHTML = '<div class="empty"><div class="icon">⏳</div>' + escapeHtml(labels.emptyWait) + '</div>';
                    return;
                }

                list.innerHTML = items.map(function (w, index) {
                    const waited = minutesWaiting(w.addedAt);
                    const quoted = parseInt(w.quoted, 10) || 0;
                    const past = waited > quoted && (w.status === 'waiting' || w.status === 'notified');
                    const waitText = waited + 'm ' + labels.waitingFor +
                        (quoted ? ' · ' + labels.quoted + ' ' + quoted + 'm' : '') +
                        (past ? ' · ' + labels.overdue : '');

                    const meta = [
                        labels.party + ' ' + w.party,
                        w.phone || null,
                        waitText,
                        w.seatedTable ? (isSweet ? 'sat at: ' : 'sat at: ') + w.seatedTable : null
                    ].filter(Boolean).join(' · ');

                    let actions = '';
                    var waitIdAttr = escapeHtml(String(w.id));
                    if (w.status === 'waiting') {
                        actions =
                            '<button type="button" class="btn btn-small btn-primary" data-act="notify-wait" data-id="' + waitIdAttr + '">' + (isSweet ? 'Notify 📲' : 'Notify') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="seat-wait" data-id="' + waitIdAttr + '">' + labels.seated + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="cancel-wait" data-id="' + waitIdAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    } else if (w.status === 'notified') {
                        actions =
                            '<button type="button" class="btn btn-small btn-primary" data-act="seat-wait" data-id="' + waitIdAttr + '">' + (isSweet ? 'Seat ✨' : 'Seat') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="rewait-wait" data-id="' + waitIdAttr + '">' + (isSweet ? 'Back to waiting' : 'Back to waiting') + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="cancel-wait" data-id="' + waitIdAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    } else {
                        actions =
                            '<button type="button" class="btn btn-small btn-ghost" data-act="rewait-wait" data-id="' + waitIdAttr + '">' + (isSweet ? 'Back to waiting' : 'Back to waiting') + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="delete-wait" data-id="' + waitIdAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    }

                    const pos = (w.status === 'waiting' || w.status === 'notified')
                        ? '<span class="badge">#' + (index + 1) + '</span>'
                        : '<span class="badge">' + escapeHtml(statusLabel(w.status)) + '</span>';

                    return (
                        '<div class="item ' + w.status + (past ? ' wait' : '') + '">' +
                            '<div class="item-top">' +
                                '<h3 class="item-name">' + escapeHtml(w.name) + '</h3>' +
                                pos +
                            '</div>' +
                            '<div class="item-meta' + (past ? ' time-overdue' : '') + '">' + escapeHtml(meta) + '</div>' +
                            (w.notes ? '<div class="item-notes">' + escapeHtml(w.notes) + '</div>' : '') +
                            '<div class="item-actions">' + actions + '</div>' +
                        '</div>'
                    );
                }).join('');
            }

            function togoRank(st) {
                // Ready first on active board, then making, then new
                if (st === 'ready') return 0;
                if (st === 'making') return 1;
                if (st === 'new') return 2;
                if (st === 'picked_up') return 3;
                if (st === 'no-show') return 4;
                if (st === 'cancelled') return 5;
                return 9;
            }

            function renderTogos() {
                const list = document.getElementById('togo-list');
                if (!list) return;
                const today = todayStr();
                let items = (state.togos || []).slice();

                items.sort(function (a, b) {
                    var ra = togoRank(a.status);
                    var rb = togoRank(b.status);
                    if (ra !== rb) return ra - rb;
                    if ((a.date || '') !== (b.date || '')) return (a.date || '') < (b.date || '') ? -1 : 1;
                    return (a.promisedTime || a.time || '') < (b.promisedTime || b.time || '') ? -1 : 1;
                });

                if (togoFilter === 'active') {
                    items = items.filter(isTogoActive);
                } else if (togoFilter === 'ready') {
                    items = items.filter(function (t) { return t.status === 'ready'; });
                } else if (togoFilter === 'today') {
                    items = items.filter(function (t) {
                        return t.date === today && t.status !== 'cancelled';
                    });
                } else if (togoFilter === 'done') {
                    items = items.filter(function (t) {
                        return t.status === 'picked_up' || t.status === 'no-show' || t.status === 'cancelled';
                    });
                }

                if (!items.length) {
                    list.innerHTML = '<div class="empty"><div class="icon">🛍️</div>' + escapeHtml(labels.emptyTogo) + '</div>';
                    checkNewReadyAlerts();
                    return;
                }

                var now = new Date();
                var nowMins = now.getHours() * 60 + now.getMinutes();

                list.innerHTML = items.map(function (t) {
                    var st = t.status || 'new';
                    var timeStr = formatTime(t.promisedTime || t.time);
                    var past = false;
                    if (isTogoActive(t) && t.date === today && (t.promisedTime || t.time)) {
                        var parts = String(t.promisedTime || t.time).split(':');
                        var pm = (parseInt(parts[0], 10) || 0) * 60 + (parseInt(parts[1], 10) || 0);
                        past = nowMins > pm + 2;
                    }
                    var cls = 'togo-' + (st === 'picked_up' || st === 'cancelled' || st === 'no-show' ? 'done' : st);
                    if (st === 'ready') cls += ' togo-ready';
                    if (st === 'making') cls += ' togo-making';
                    if (st === 'new') cls += ' togo-new';
                    if (st === 'ready' && knownReadyIds[String(t.id)] === undefined && readyAlertPrimed) {
                        cls += ' flash-ready';
                    }

                    var meta = [
                        formatDate(t.date) + (timeStr ? ' · ' + timeStr : ''),
                        channelLabel(t.channel),
                        t.ticket ? ('#' + t.ticket) : null,
                        t.phone || null,
                        t.notifiedReadyAt ? (isSweet ? 'notified' : 'notified') : null,
                        past ? (isSweet ? 'past promised time' : 'past promised time') : null
                    ].filter(Boolean).join(' · ');

                    var badgeCls = st === 'ready' ? ' badge ready-now' : '';
                    var idAttr = escapeHtml(String(t.id));
                    var actions = '';
                    if (st === 'new') {
                        actions =
                            '<button type="button" class="btn btn-small btn-primary" data-act="togo-making" data-id="' + idAttr + '">' + (isSweet ? 'Start making 🔥' : 'Start making') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-ready" data-id="' + idAttr + '">' + labels.togoReady + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-cancel" data-id="' + idAttr + '">' + labels.cancelled + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="togo-delete" data-id="' + idAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    } else if (st === 'making') {
                        actions =
                            '<button type="button" class="btn btn-small btn-primary" data-act="togo-ready" data-id="' + idAttr + '">' + (isSweet ? 'Mark ready ✨' : 'Mark ready') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-new" data-id="' + idAttr + '">' + (isSweet ? 'Back to new' : 'Back to new') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-cancel" data-id="' + idAttr + '">' + labels.cancelled + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="togo-delete" data-id="' + idAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    } else if (st === 'ready') {
                        actions =
                            '<button type="button" class="btn btn-small btn-primary" data-act="togo-picked" data-id="' + idAttr + '">' + (isSweet ? 'Picked up 🛍️' : 'Picked up') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-notify" data-id="' + idAttr + '">' + (isSweet ? 'Notify 📲' : 'Notify') + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-noshow" data-id="' + idAttr + '">' + labels.noshow + '</button>' +
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-making" data-id="' + idAttr + '">' + (isSweet ? 'Back to making' : 'Back to making') + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="togo-delete" data-id="' + idAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    } else {
                        actions =
                            '<button type="button" class="btn btn-small btn-ghost" data-act="togo-new" data-id="' + idAttr + '">' + (isSweet ? 'Reopen' : 'Reopen') + '</button>' +
                            '<button type="button" class="btn btn-small btn-danger" data-act="togo-delete" data-id="' + idAttr + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>';
                    }

                    return (
                        '<div class="item ' + cls + (past ? ' wait' : '') + '">' +
                            '<div class="item-top">' +
                                '<h3 class="item-name">' + escapeHtml(t.name) + '</h3>' +
                                '<span class="badge' + badgeCls + '">' + escapeHtml(statusLabel(st)) + '</span>' +
                            '</div>' +
                            '<div class="item-meta' + (past ? ' time-overdue' : '') + '">' + escapeHtml(meta) + '</div>' +
                            (t.items ? '<div class="item-notes">' + escapeHtml(t.items) + '</div>' : '') +
                            (t.notes ? '<div class="item-notes">' + escapeHtml(t.notes) + '</div>' : '') +
                            '<div class="item-actions">' + actions + '</div>' +
                        '</div>'
                    );
                }).join('');
                checkNewReadyAlerts();
            }

            function refresh() {
                updateStats();
                renderReservations();
                renderWaitlist();
                renderTogos();
            }

            // Tabs
            document.querySelectorAll('.tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const key = tab.dataset.tab;
                    document.querySelectorAll('.tab').forEach(function (t) {
                        t.classList.toggle('active', t === tab);
                    });
                    document.getElementById('panel-reservations').classList.toggle('active', key === 'reservations');
                    document.getElementById('panel-waitlist').classList.toggle('active', key === 'waitlist');
                    var togoPanel = document.getElementById('panel-togos');
                    if (togoPanel) togoPanel.classList.toggle('active', key === 'togos');
                });
            });

            // Filters
            document.getElementById('res-filters').addEventListener('click', function (e) {
                const chip = e.target.closest('.filter-chip');
                if (!chip) return;
                resFilter = chip.dataset.filter;
                document.querySelectorAll('#res-filters .filter-chip').forEach(function (c) {
                    c.classList.toggle('active', c === chip);
                });
                renderReservations();
            });

            document.getElementById('wait-filters').addEventListener('click', function (e) {
                const chip = e.target.closest('.filter-chip');
                if (!chip) return;
                waitFilter = chip.dataset.filter;
                document.querySelectorAll('#wait-filters .filter-chip').forEach(function (c) {
                    c.classList.toggle('active', c === chip);
                });
                renderWaitlist();
            });

            var togoFilters = document.getElementById('togo-filters');
            if (togoFilters) {
                togoFilters.addEventListener('click', function (e) {
                    const chip = e.target.closest('.filter-chip');
                    if (!chip) return;
                    togoFilter = chip.dataset.filter;
                    document.querySelectorAll('#togo-filters .filter-chip').forEach(function (c) {
                        c.classList.toggle('active', c === chip);
                    });
                    renderTogos();
                });
            }

            // Forms
            document.getElementById('res-date').value = todayStr();
            var togoDateEl = document.getElementById('togo-date');
            if (togoDateEl) togoDateEl.value = todayStr();

            document.getElementById('res-form').addEventListener('submit', function (e) {
                e.preventDefault();
                if (!canP('foh.res.add')) return;
                state.reservations.push({
                    id: uid(),
                    name: document.getElementById('res-name').value.trim(),
                    party: parseInt(document.getElementById('res-party').value, 10) || 2,
                    date: document.getElementById('res-date').value,
                    time: document.getElementById('res-time').value,
                    phone: document.getElementById('res-phone').value.trim(),
                    tablePref: document.getElementById('res-table').value.trim(),
                    notes: document.getElementById('res-notes').value.trim(),
                    status: 'booked',
                    createdAt: Date.now()
                });
                saveState();
                e.target.reset();
                document.getElementById('res-date').value = todayStr();
                document.getElementById('res-time').value = '18:00';
                document.getElementById('res-party').value = 2;
                refresh();
            });

            const quoteInput = document.getElementById('quote-mins');
            const waitQuoted = document.getElementById('wait-quoted');
            quoteInput.value = state.quoteMins;
            waitQuoted.value = state.quoteMins;
            quoteInput.addEventListener('change', function () {
                state.quoteMins = parseInt(this.value, 10) || 0;
                waitQuoted.value = state.quoteMins;
                saveState();
            });

            document.getElementById('wait-form').addEventListener('submit', function (e) {
                e.preventDefault();
                if (!canP('foh.wait.add')) return;
                const quoted = parseInt(document.getElementById('wait-quoted').value, 10);
                state.waitlist.push({
                    id: uid(),
                    name: document.getElementById('wait-name').value.trim(),
                    party: parseInt(document.getElementById('wait-party').value, 10) || 2,
                    phone: document.getElementById('wait-phone').value.trim(),
                    quoted: isNaN(quoted) ? state.quoteMins : quoted,
                    notes: document.getElementById('wait-notes').value.trim(),
                    status: 'waiting',
                    addedAt: Date.now()
                });
                saveState();
                e.target.reset();
                document.getElementById('wait-party').value = 2;
                document.getElementById('wait-quoted').value = state.quoteMins;
                refresh();
            });

            var togoForm = document.getElementById('togo-form');
            // Restore last channel + default promised time
            (function initTogoDefaults() {
                var prefs = loadTogoPrefs();
                var ch = document.getElementById('togo-channel');
                if (ch && prefs.lastChannel) ch.value = prefs.lastChannel;
                if (document.getElementById('togo-date')) document.getElementById('togo-date').value = todayStr();
                setTogoPromisedIn(prefs.defaultMins || 20);
            })();

            // Promised-in chips — bind each button (more reliable than delegation)
            (function wireTogoTimeChips() {
                var chips = document.getElementById('togo-time-chips');
                if (!chips) return;
                function onChip(ev) {
                    if (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                    }
                    var btn = ev && ev.currentTarget ? ev.currentTarget : null;
                    if (!btn || !btn.getAttribute) {
                        btn = (ev && ev.target && ev.target.closest) ? ev.target.closest('[data-mins]') : null;
                    }
                    if (!btn) return false;
                    var mins = parseInt(btn.getAttribute('data-mins'), 10);
                    if (isNaN(mins)) mins = 20;
                    setTogoPromisedIn(mins);
                    return false;
                }
                chips.querySelectorAll('button[data-mins]').forEach(function (btn) {
                    btn.addEventListener('click', onChip);
                    btn.addEventListener('touchend', function (ev) {
                        // avoid double-fire with click on some mobiles
                        ev.preventDefault();
                        onChip(ev);
                    }, { passive: false });
                });
            })();

            var dupBtn = document.getElementById('togo-dup-last');
            if (dupBtn) {
                dupBtn.addEventListener('click', function () {
                    if (!canP('foh.togo.add')) return;
                    var list = (state.togos || []).slice().sort(function (a, b) {
                        return (b.addedAt || 0) - (a.addedAt || 0);
                    });
                    var last = list[0];
                    if (!last) {
                        alert(isSweet ? 'No previous to-go to copy yet 💕' : 'No previous to-go order.');
                        return;
                    }
                    document.getElementById('togo-name').value = last.name || '';
                    document.getElementById('togo-phone').value = last.phone || '';
                    document.getElementById('togo-channel').value = last.channel || 'phone';
                    document.getElementById('togo-ticket').value = '';
                    document.getElementById('togo-date').value = todayStr();
                    document.getElementById('togo-time').value = timePlusMinutes(loadTogoPrefs().defaultMins || 20);
                    document.getElementById('togo-items').value = last.items || '';
                    document.getElementById('togo-notes').value = last.notes || '';
                    document.getElementById('togo-name').focus();
                });
            }

            if (togoForm) {
                togoForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!canP('foh.togo.add')) return;
                    if (!state.togos) state.togos = [];
                    var now = Date.now();
                    var channel = document.getElementById('togo-channel').value || 'phone';
                    var order = {
                        id: uid(),
                        name: document.getElementById('togo-name').value.trim(),
                        phone: document.getElementById('togo-phone').value.trim(),
                        channel: channel,
                        ticket: document.getElementById('togo-ticket').value.trim(),
                        date: document.getElementById('togo-date').value || todayStr(),
                        promisedTime: document.getElementById('togo-time').value || timePlusMinutes(20),
                        items: document.getElementById('togo-items').value.trim(),
                        notes: document.getElementById('togo-notes').value.trim(),
                        status: 'new',
                        addedAt: now,
                        updatedAt: now,
                        createdAt: now
                    };
                    state.togos.push(order);
                    var prefs = loadTogoPrefs();
                    prefs.lastChannel = channel;
                    prefs.lastOrderId = order.id;
                    saveTogoPrefs(prefs);
                    saveState();
                    e.target.reset();
                    document.getElementById('togo-date').value = todayStr();
                    document.getElementById('togo-time').value = timePlusMinutes(prefs.defaultMins || 20);
                    document.getElementById('togo-channel').value = prefs.lastChannel || 'phone';
                    refresh();
                    var togoTab = document.querySelector('.tab[data-tab="togos"]');
                    if (togoTab) togoTab.click();
                });
            }

            var clearPicked = document.getElementById('togo-clear-picked');
            if (clearPicked) {
                clearPicked.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!canP('foh.togo.remove')) return;
                    var today = todayStr();
                    var toClear = (state.togos || []).filter(function (t) {
                        return t.status === 'picked_up' && t.date === today;
                    });
                    if (!toClear.length) {
                        alert(isSweet ? 'No picked-up to-gos for today 💕' : 'No picked-up to-gos for today.');
                        return;
                    }
                    if (!confirm(isSweet
                        ? ('Clear ' + toClear.length + ' picked-up to-go' + (toClear.length === 1 ? '' : 's') + ' from today?')
                        : ('Clear ' + toClear.length + ' picked-up order(s) from today?'))) {
                        return;
                    }
                    if (!state.deletedTogoIds) state.deletedTogoIds = {};
                    var now = Date.now();
                    toClear.forEach(function (t) {
                        state.deletedTogoIds[String(t.id)] = now;
                    });
                    state.togos = (state.togos || []).filter(function (t) {
                        return !(t.status === 'picked_up' && t.date === today);
                    });
                    saveState();
                    refresh();
                });
            }

            // --- Seat at table modal ---
            var FLOOR_KEY = 'pbj_showtime_floor_plan_v3';
            var seatModal = document.getElementById('seat-modal');

            function loadFloorSnapshot() {
                try {
                    var raw = JSON.parse(localStorage.getItem(FLOOR_KEY) || 'null');
                    if (!raw) return { layout: [], tables: {} };
                    return {
                        layout: Array.isArray(raw.layout) ? raw.layout : [],
                        tables: raw.tables || {},
                        assignments: raw.assignments || {},
                        structureAt: raw.structureAt || 0,
                        layoutAt: raw.layoutAt || 0,
                        sections: raw.sections || [],
                        sectionsAt: raw.sectionsAt || 0,
                        turnGoal: raw.turnGoal || 45,
                        assignmentsAt: raw.assignmentsAt || 0,
                        turnGoalAt: raw.turnGoalAt || 0
                    };
                } catch (e) {
                    return { layout: [], tables: {} };
                }
            }

            function saveFloorSnapshot(floor) {
                try {
                    floor.structureAt = Date.now();
                    localStorage.setItem(FLOOR_KEY, JSON.stringify(floor));
                } catch (e) {}
                // Push so Floor Plan / other devices keep party notes after seat
                try {
                    if (window.PbjSharedState) {
                        if (!window._floorPushFromRes) {
                            window._floorPushFromRes = new PbjSharedState({
                                key: 'showtime_floor_v1',
                                pollMs: 60000,
                                onStatus: function () {},
                                onRemote: function () {}
                            });
                        }
                        // bootstrap version then push (fire-and-forget)
                        var pusher = window._floorPushFromRes;
                        pusher.fetchRemote().then(function (data) {
                            if (data && data.version != null) pusher.version = data.version || 0;
                            return pusher.push(floor, { force: false });
                        }).catch(function () {
                            pusher.push(floor, { force: true });
                        });
                    }
                } catch (e2) {}
            }

            function buildTableNotesFromGuest(existingNotes, guest) {
                var parts = [];
                var existing = (existingNotes || '').trim();
                if (existing) parts.push(existing);

                var guestBits = [];
                if (guest && guest.name) guestBits.push(guest.name);
                if (guest && guest.party) guestBits.push('party of ' + guest.party);
                if (guest && guest.phone) guestBits.push(guest.phone);
                if (guest && guest.notes) guestBits.push(guest.notes);
                // Also carry preferred table note if different from sat table
                if (guest && guest.tablePref && guest.seatedTable &&
                    String(guest.tablePref).toLowerCase() !== String(guest.seatedTable).toLowerCase()) {
                    guestBits.push((isSweet ? 'pref was: ' : 'pref was: ') + guest.tablePref);
                }

                if (guestBits.length) {
                    var chunk = guestBits.join(' · ');
                    // Avoid duplicate append if same chunk already in notes
                    if (!existing || existing.indexOf(chunk) === -1) {
                        parts.push(chunk);
                    }
                }
                return parts.join(' · ');
            }

            function fillSeatTableSelect(pref) {
                var sel = document.getElementById('seat-table-select');
                var floor = loadFloorSnapshot();
                var layout = floor.layout || [];
                var openFirst = [];
                var busy = [];
                layout.forEach(function (t) {
                    var id = String(t.id);
                    var st = (floor.tables && floor.tables[id]) || {};
                    var status = st.status || 'open';
                    var seats = t.seats || '';
                    var sec = t.section || '';
                    var label = 'Table ' + id +
                        (sec ? ' · Sec ' + sec : '') +
                        (seats ? ' · ' + seats + ' top' : '') +
                        (status && status !== 'open' ? ' · ' + status : '');
                    var opt = { id: id, label: label, status: status };
                    if (status === 'open' || status === 'reserved' || status === 'dirty') openFirst.push(opt);
                    else busy.push(opt);
                });
                var ordered = openFirst.concat(busy);
                sel.innerHTML = '<option value="">' + (isSweet ? '— Choose a table —' : '— Choose a table —') + '</option>' +
                    ordered.map(function (o) {
                        var vid = String(o.id).replace(/"/g, '');
                        return '<option value="' + vid + '">' + escapeHtml(o.label) + '</option>';
                    }).join('') +
                    '<option value="__custom__">' + (isSweet ? 'Other / type below…' : 'Other / type below…') + '</option>';
                // Prefill from preferred table if it matches a floor id
                var prefId = (pref || '').trim();
                if (prefId) {
                    var match = ordered.find(function (o) {
                        return o.id === prefId || ('Table ' + o.id) === prefId || o.id.toLowerCase() === prefId.toLowerCase();
                    });
                    if (match) {
                        sel.value = match.id;
                        document.getElementById('seat-table-custom').value = '';
                    } else {
                        sel.value = '__custom__';
                        document.getElementById('seat-table-custom').value = prefId;
                    }
                } else {
                    document.getElementById('seat-table-custom').value = '';
                }
            }

            function openSeatModal(type, id, guest) {
                document.getElementById('seat-target-type').value = type;
                document.getElementById('seat-target-id').value = id;
                var name = guest && guest.name ? guest.name : '';
                var party = guest && guest.party ? guest.party : '';
                document.getElementById('seat-modal-title').textContent = isSweet
                    ? ('Seat ' + (name || 'party') + (party ? ' · ' + party : ''))
                    : ('Seat ' + (name || 'party') + (party ? ' · party of ' + party : ''));
                document.getElementById('seat-modal-hint').textContent = isSweet
                    ? 'Which table are they sitting at?'
                    : 'Which table are they sitting at?';
                fillSeatTableSelect((guest && (guest.tablePref || guest.seatedTable)) || '');
                seatModal.classList.add('show');
            }

            function closeSeatModal() {
                seatModal.classList.remove('show');
            }

            function applySeatToFloor(tableId, guest) {
                if (!tableId) return false;
                var floor = loadFloorSnapshot();
                if (!floor.layout || !floor.layout.length) return false;
                var exists = floor.layout.some(function (t) { return String(t.id) === String(tableId); });
                if (!exists) return false;
                if (!floor.tables) floor.tables = {};
                var st = floor.tables[tableId] || {
                    status: 'open', party: 2, server: '', notes: '', seatedAt: null, updatedAt: 0
                };
                // Guest object should already include seatedTable when notes are built
                st.status = 'seated';
                st.party = (guest && guest.party) ? guest.party : (st.party || 2);
                st.seatedAt = Date.now();
                st.updatedAt = Date.now();
                // Always carry reservation / waitlist notes onto the table notes field
                st.notes = buildTableNotesFromGuest(st.notes, guest || {});
                if (!st.server && floor.assignments) {
                    var layoutItem = floor.layout.find(function (t) { return String(t.id) === String(tableId); });
                    var sec = layoutItem && layoutItem.section;
                    if (sec && floor.assignments[sec]) st.server = floor.assignments[sec];
                }
                floor.tables[tableId] = st;
                saveFloorSnapshot(floor);
                return true;
            }

            function confirmSeat() {
                var type = document.getElementById('seat-target-type').value;
                var id = document.getElementById('seat-target-id').value;
                var sel = document.getElementById('seat-table-select').value;
                var custom = document.getElementById('seat-table-custom').value.trim();
                var table = '';
                if (sel && sel !== '__custom__') table = sel;
                else if (custom) table = custom;
                else if (sel === '__custom__' && !custom) {
                    alert(isSweet ? 'Pick a table or type one 💕' : 'Pick or type a table.');
                    return;
                } else {
                    alert(isSweet ? 'Which table are they at?' : 'Please choose a table.');
                    return;
                }

                var guest = null;
                if (type === 'res') {
                    var r = state.reservations.find(function (x) { return String(x.id) === String(id); });
                    if (!r) return;
                    r.status = 'seated';
                    r.seatedTable = table;
                    r.updatedAt = Date.now();
                    guest = r;
                } else if (type === 'wait') {
                    var w = state.waitlist.find(function (x) { return String(x.id) === String(id); });
                    if (!w) return;
                    w.status = 'seated';
                    w.seatedTable = table;
                    w.updatedAt = Date.now();
                    guest = w;
                }
                // Mark map seated + copy guest/reservation notes onto table notes
                if (sel && sel !== '__custom__') {
                    applySeatToFloor(sel, guest || {});
                } else {
                    applySeatToFloor(table, guest || {});
                }
                saveState();
                closeSeatModal();
                refresh();
            }

            document.getElementById('seat-cancel').addEventListener('click', closeSeatModal);
            document.getElementById('seat-confirm').addEventListener('click', confirmSeat);
            seatModal.addEventListener('click', function (e) {
                if (e.target === seatModal) closeSeatModal();
            });
            document.getElementById('seat-table-select').addEventListener('change', function () {
                if (this.value && this.value !== '__custom__') {
                    document.getElementById('seat-table-custom').value = this.value;
                } else if (this.value === '__custom__') {
                    document.getElementById('seat-table-custom').focus();
                }
            });

            // List actions
            document.getElementById('res-list').addEventListener('click', function (e) {
                const btn = e.target.closest('[data-act]');
                if (!btn) return;
                const id = String(btn.dataset.id || '');
                const act = btn.dataset.act;
                const r = state.reservations.find(function (x) { return String(x.id) === id; });
                if (!r) return;

                if (act === 'seat-res') {
                    if (!canP('foh.res.seat_status')) return;
                    openSeatModal('res', id, r);
                    return;
                }
                if (act === 'cancel-res') { if (!canP('foh.res.seat_status')) return; r.status = 'cancelled'; r.updatedAt = Date.now(); }
                if (act === 'noshow-res') { if (!canP('foh.res.seat_status')) return; r.status = 'no-show'; r.updatedAt = Date.now(); }
                if (act === 'reopen-res') {
                    r.status = 'booked';
                    r.updatedAt = Date.now();
                    delete r.seatedTable;
                }
                if (act === 'delete-res') {
                    if (!canP('foh.res.remove')) return;
                    var resName = r.name || (isSweet ? 'this reservation' : 'this reservation');
                    if (!confirm(isSweet
                        ? ('Remove reservation for ' + resName + '? This cannot be undone.')
                        : ('Remove reservation for ' + resName + '? This cannot be undone.'))) {
                        return;
                    }
                    tombstoneReservation(id);
                    saveState();
                    refresh();
                    return;
                }
                saveState();
                refresh();
            });

            document.getElementById('wait-list').addEventListener('click', function (e) {
                const btn = e.target.closest('[data-act]');
                if (!btn) return;
                const id = String(btn.dataset.id || '');
                const act = btn.dataset.act;
                const w = state.waitlist.find(function (x) { return String(x.id) === id; });
                if (!w) return;

                if (act === 'notify-wait') { if (!canP('foh.wait.notify_seat')) return; w.status = 'notified'; w.updatedAt = Date.now(); }
                if (act === 'seat-wait') {
                    openSeatModal('wait', id, w);
                    return;
                }
                if (act === 'rewait-wait') {
                    w.status = 'waiting';
                    w.updatedAt = Date.now();
                    w.addedAt = Date.now();
                    delete w.seatedTable;
                }
                if (act === 'cancel-wait' || act === 'delete-wait') {
                    if (!canP('foh.wait.remove')) return;
                    var waitName = w.name || (isSweet ? 'this party' : 'this party');
                    if (!confirm(isSweet
                        ? ('Remove ' + waitName + ' from the waitlist? This cannot be undone.')
                        : ('Remove ' + waitName + ' from the waitlist? This cannot be undone.'))) {
                        return;
                    }
                    tombstoneWaitlist(id);
                    saveState();
                    refresh();
                    return;
                }
                saveState();
                refresh();
            });

            var togoList = document.getElementById('togo-list');
            if (togoList) {
                togoList.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-act]');
                    if (!btn) return;
                    const id = String(btn.dataset.id || '');
                    const act = btn.dataset.act;
                    if (!state.togos) state.togos = [];
                    const t = state.togos.find(function (x) { return String(x.id) === id; });
                    if (!t) return;

                    if (act === 'togo-delete') {
                        if (!canP('foh.togo.remove')) return;
                        var nm = t.name || (isSweet ? 'this order' : 'this order');
                        if (!confirm(isSweet
                            ? ('Remove to-go for ' + nm + '? This cannot be undone.')
                            : ('Remove to-go for ' + nm + '?'))) {
                            return;
                        }
                        tombstoneTogo(id);
                        saveState();
                        refresh();
                        return;
                    }

                    if (!canP('foh.togo.status') && act !== 'togo-delete') return;

                    if (act === 'togo-notify') {
                        if (!canP('foh.togo.status')) return;
                        notifyTogoReady(t);
                        saveState();
                        refresh();
                        return;
                    }
                    if (act === 'togo-new') { t.status = 'new'; t.updatedAt = Date.now(); }
                    if (act === 'togo-making') { t.status = 'making'; t.updatedAt = Date.now(); }
                    if (act === 'togo-ready') {
                        var wasReady = t.status === 'ready';
                        t.status = 'ready';
                        t.updatedAt = Date.now();
                        t.readyAt = Date.now();
                        // Auto-offer notify when first marked ready and phone exists
                        if (!wasReady && t.phone) {
                            saveState();
                            refresh();
                            if (confirm(isSweet
                                ? ('Order ready for ' + (t.name || 'guest') + ' — send ready text now?')
                                : ('Mark ready and text ' + (t.name || 'guest') + '?'))) {
                                notifyTogoReady(t);
                                saveState();
                                refresh();
                            }
                            return;
                        }
                    }
                    if (act === 'togo-picked') { t.status = 'picked_up'; t.updatedAt = Date.now(); t.pickedAt = Date.now(); }
                    if (act === 'togo-noshow') { t.status = 'no-show'; t.updatedAt = Date.now(); }
                    if (act === 'togo-cancel') { t.status = 'cancelled'; t.updatedAt = Date.now(); }
                    saveState();
                    refresh();
                });
            }

            document.getElementById('clear-done-btn').addEventListener('click', function () {
                if (!confirm(isSweet ? 'Clear seated, cancelled, no-show, and finished to-gos?' : 'Clear finished entries?')) return;
                var now = Date.now();
                if (!state.deletedReservationIds) state.deletedReservationIds = {};
                if (!state.deletedWaitlistIds) state.deletedWaitlistIds = {};
                if (!state.deletedTogoIds) state.deletedTogoIds = {};
                (state.reservations || []).forEach(function (r) {
                    if (r.status !== 'booked') {
                        state.deletedReservationIds[String(r.id)] = now;
                    }
                });
                (state.waitlist || []).forEach(function (w) {
                    if (w.status !== 'waiting' && w.status !== 'notified') {
                        state.deletedWaitlistIds[String(w.id)] = now;
                    }
                });
                (state.togos || []).forEach(function (t) {
                    if (!isTogoActive(t)) {
                        state.deletedTogoIds[String(t.id)] = now;
                    }
                });
                state.reservations = state.reservations.filter(function (r) {
                    return r.status === 'booked';
                });
                state.waitlist = state.waitlist.filter(function (w) {
                    return w.status === 'waiting' || w.status === 'notified';
                });
                state.togos = (state.togos || []).filter(isTogoActive);
                saveState();
                refresh();
            });

            // Print / save daily lists
            (function initPrintDate() {
                var el = document.getElementById('print-date');
                if (el) el.value = todayStr();
            })();

            function openPrintWindow(title, bodyHtml) {
                var w = window.open('', '_blank');
                if (!w) {
                    alert(isSweet ? 'Allow pop-ups to print 💕' : 'Allow pop-ups to print.');
                    return;
                }
                var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + escapeHtml(title) + '</title>' +
                    '<style>body{font-family:Georgia,serif;padding:20px;max-width:820px;margin:0 auto;color:#222;}' +
                    'h1{font-size:18pt;margin:0 0 4px;} .sub{opacity:.7;margin:0 0 14px;font-size:11pt;}' +
                    'table{width:100%;border-collapse:collapse;font-size:10.5pt;}' +
                    'th,td{border-bottom:1px solid #ddd;padding:6px 8px;text-align:left;vertical-align:top;}' +
                    'th{font-size:9.5pt;opacity:.75;} .notes{font-style:italic;opacity:.8;}' +
                    '.meta{font-size:9.5pt;opacity:.65;margin-top:18px;} .sum{margin:10px 0 0;font-size:11pt;}' +
                    '@media print{body{padding:8px;}}</style></head><body>' + bodyHtml +
                    '<p class="meta">ilovepbj ops · ' + escapeHtml(new Date().toLocaleString()) + '</p></body></html>';
                w.document.write(html);
                w.document.close();
                w.focus();
                setTimeout(function () { w.print(); }, 250);
            }

            function printDateValue() {
                var el = document.getElementById('print-date');
                return (el && el.value) ? el.value : todayStr();
            }

            function printDailyReservations() {
                if (!canP('foh.res.print')) return;
                var date = printDateValue();
                var items = state.reservations.filter(function (r) {
                    return r.date === date && r.status !== 'cancelled';
                }).slice().sort(function (a, b) {
                    return (a.time || '').localeCompare(b.time || '');
                });
                var covers = items.reduce(function (sum, r) {
                    return sum + (parseInt(r.party, 10) || 0);
                }, 0);
                var booked = items.filter(function (r) { return r.status === 'booked'; }).length;
                var title = isSweet ? 'Daily reservation list' : 'Daily reservation list';
                var html = '<h1>' + escapeHtml(title) + '</h1>' +
                    '<p class="sub">' + escapeHtml(formatDate(date)) + ' · ' + items.length + ' parties · ' + covers + ' covers' +
                    (booked ? ' · ' + booked + ' still booked' : '') + '</p>';
                if (!items.length) {
                    html += '<p>' + escapeHtml(isSweet ? 'No reservations for this date ✨' : 'No reservations for this date.') + '</p>';
                } else {
                    html += '<table><tr><th>Time</th><th>Name</th><th>Party</th><th>Phone</th><th>Table / pref</th><th>Status</th><th>Notes</th></tr>';
                    items.forEach(function (r) {
                        var table = r.seatedTable || r.tablePref || '';
                        html += '<tr><td>' + escapeHtml(formatTime(r.time)) + '</td><td>' + escapeHtml(r.name) +
                            '</td><td>' + escapeHtml(String(r.party || '')) + '</td><td>' + escapeHtml(r.phone || '') +
                            '</td><td>' + escapeHtml(table) + '</td><td>' + escapeHtml(statusLabel(r.status)) +
                            '</td><td class="notes">' + escapeHtml(r.notes || '') + '</td></tr>';
                    });
                    html += '</table>';
                    html += '<p class="sum">' + escapeHtml(isSweet
                        ? ('Total covers: ' + covers + ' 💕')
                        : ('Total covers: ' + covers)) + '</p>';
                }
                openPrintWindow(title + ' · ' + formatDate(date), html);
            }

            function printWaitlist() {
                if (!canP('foh.res.print')) return;
                var items = state.waitlist.filter(function (w) {
                    return w.status === 'waiting' || w.status === 'notified';
                }).slice().sort(function (a, b) { return a.addedAt - b.addedAt; });
                var covers = items.reduce(function (sum, w) {
                    return sum + (parseInt(w.party, 10) || 0);
                }, 0);
                var title = isSweet ? 'Waitlist snapshot' : 'Waitlist snapshot';
                var html = '<h1>' + escapeHtml(title) + '</h1>' +
                    '<p class="sub">' + escapeHtml(formatDate(todayStr())) + ' · ' + items.length + ' parties · ' +
                    covers + ' covers · quote ' + (state.quoteMins || 0) + ' min</p>';
                if (!items.length) {
                    html += '<p>' + escapeHtml(isSweet ? 'Waitlist is clear 💕' : 'Waitlist is empty.') + '</p>';
                } else {
                    html += '<table><tr><th>#</th><th>Name</th><th>Party</th><th>Phone</th><th>Waited</th><th>Quoted</th><th>Status</th><th>Notes</th></tr>';
                    items.forEach(function (w, i) {
                        var waited = minutesWaiting(w.addedAt);
                        html += '<tr><td>' + (i + 1) + '</td><td>' + escapeHtml(w.name) +
                            '</td><td>' + escapeHtml(String(w.party || '')) + '</td><td>' + escapeHtml(w.phone || '') +
                            '</td><td>' + waited + 'm</td><td>' + escapeHtml(String(w.quoted != null ? w.quoted + 'm' : '')) +
                            '</td><td>' + escapeHtml(statusLabel(w.status)) +
                            '</td><td class="notes">' + escapeHtml(w.notes || '') + '</td></tr>';
                    });
                    html += '</table>';
                }
                openPrintWindow(title, html);
            }

            function exportDailyCsv() {
                if (!canP('foh.res.print')) return;
                var date = printDateValue();
                var items = state.reservations.filter(function (r) { return r.date === date; })
                    .slice().sort(function (a, b) { return (a.time || '').localeCompare(b.time || ''); });
                var rows = [['Date', 'Time', 'Name', 'Party', 'Phone', 'Table / pref', 'Status', 'Notes']];
                items.forEach(function (r) {
                    rows.push([
                        r.date || '',
                        formatTime(r.time),
                        r.name || '',
                        r.party || '',
                        r.phone || '',
                        r.seatedTable || r.tablePref || '',
                        r.status || '',
                        r.notes || ''
                    ]);
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
                a.download = 'reservations-' + date + '.csv';
                a.click();
                URL.revokeObjectURL(a.href);
            }

            function printTogos() {
                if (!canP('foh.res.print')) return;
                var date = printDateValue();
                var items = (state.togos || []).filter(function (t) {
                    return t.date === date && t.status !== 'cancelled';
                }).slice().sort(function (a, b) {
                    return (a.promisedTime || '').localeCompare(b.promisedTime || '');
                });
                var active = items.filter(isTogoActive).length;
                var title = isSweet ? 'To-go board' : 'To-go board';
                var html = '<h1>' + escapeHtml(title) + '</h1>' +
                    '<p class="sub">' + escapeHtml(formatDate(date)) + ' · ' + items.length + ' orders · ' +
                    active + ' active</p>';
                if (!items.length) {
                    html += '<p>' + escapeHtml(isSweet ? 'No to-gos for this date 🛍️' : 'No to-go orders for this date.') + '</p>';
                } else {
                    html += '<table><tr><th>Time</th><th>Name</th><th>Channel</th><th>Ticket</th><th>Phone</th><th>Status</th><th>Items</th><th>Notes</th></tr>';
                    items.forEach(function (t) {
                        html += '<tr><td>' + escapeHtml(formatTime(t.promisedTime || t.time)) +
                            '</td><td>' + escapeHtml(t.name) +
                            '</td><td>' + escapeHtml(channelLabel(t.channel)) +
                            '</td><td>' + escapeHtml(t.ticket || '') +
                            '</td><td>' + escapeHtml(t.phone || '') +
                            '</td><td>' + escapeHtml(statusLabel(t.status)) +
                            '</td><td class="notes">' + escapeHtml(t.items || '') +
                            '</td><td class="notes">' + escapeHtml(t.notes || '') + '</td></tr>';
                    });
                    html += '</table>';
                }
                openPrintWindow(title + ' · ' + formatDate(date), html);
            }

            document.getElementById('print-daily-res-btn').addEventListener('click', printDailyReservations);
            document.getElementById('print-waitlist-btn').addEventListener('click', printWaitlist);
            var printTogoBtn = document.getElementById('print-togo-btn');
            if (printTogoBtn) printTogoBtn.addEventListener('click', printTogos);
            document.getElementById('export-daily-res-btn').addEventListener('click', exportDailyCsv);

            // Refresh wait / to-go times every 30s
            setInterval(function () {
                renderWaitlist();
                renderTogos();
                updateStats();
            }, 30000);

            refresh();
            applyResPermUi();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyResPermUi);
            document.addEventListener('pbj-perms-ready', applyResPermUi);

            function setSyncPill(info) {
                var pill = document.getElementById('sync-pill');
                var textEl = document.getElementById('sync-pill-text');
                if (!pill || !textEl) return;
                pill.classList.remove('offline', 'syncing');
                if (info.kind === 'offline') pill.classList.add('offline');
                if (info.kind === 'syncing') pill.classList.add('syncing');
                textEl.textContent = info.text || '';
            }
            function applyRemoteRes(payload) {
                if (!payload) return;
                // Merge tombstones (max ts per id) so local deletes aren't wiped by an older remote pull
                var delRes = normalizeDeletedMap(state.deletedReservationIds);
                var delWait = normalizeDeletedMap(state.deletedWaitlistIds);
                var delTogo = normalizeDeletedMap(state.deletedTogoIds);
                var remoteDelRes = normalizeDeletedMap(payload.deletedReservationIds);
                var remoteDelWait = normalizeDeletedMap(payload.deletedWaitlistIds);
                var remoteDelTogo = normalizeDeletedMap(payload.deletedTogoIds);
                Object.keys(remoteDelRes).forEach(function (k) {
                    if (!delRes[k] || remoteDelRes[k] >= delRes[k]) delRes[k] = remoteDelRes[k];
                });
                Object.keys(remoteDelWait).forEach(function (k) {
                    if (!delWait[k] || remoteDelWait[k] >= delWait[k]) delWait[k] = remoteDelWait[k];
                });
                Object.keys(remoteDelTogo).forEach(function (k) {
                    if (!delTogo[k] || remoteDelTogo[k] >= delTogo[k]) delTogo[k] = remoteDelTogo[k];
                });
                state = {
                    reservations: payload.reservations || [],
                    waitlist: payload.waitlist || [],
                    togos: Array.isArray(payload.togos) ? payload.togos : (state.togos || []),
                    quoteMins: payload.quoteMins != null ? payload.quoteMins : 20,
                    structureAt: payload.structureAt || Date.now(),
                    quoteMinsAt: payload.quoteMinsAt || 0,
                    deletedReservationIds: delRes,
                    deletedWaitlistIds: delWait,
                    deletedTogoIds: delTogo
                };
                applyTombstonesToLists(state);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                try {
                    var q = document.getElementById('quote-mins');
                    if (q) q.value = state.quoteMins;
                } catch (e) {}
                updateStats();
                if (typeof renderReservations === 'function') renderReservations();
                if (typeof renderWaitlist === 'function') renderWaitlist();
                if (typeof renderTogos === 'function') renderTogos();
            }
            // stamp updatedAt on mutations via event delegation-ish: wrap saveState calls by proxy
            var _save = saveState;
            saveState = function (opts) {
                opts = opts || {};
                // last-touched row stamping is handled when objects created with Date.now fields
                _save(opts);
            };

            if (window.PbjSharedState) {
                window._resShared = new PbjSharedState({
                    key: 'showtime_res_wait_v1',
                    pollMs: 3000,
                    onStatus: setSyncPill,
                    onRemote: function (payload) { applyRemoteRes(payload); }
                });
                window._resShared.bootstrap(
                    function () { return state; },
                    function (payload) { applyRemoteRes(payload); }
                ).then(function () { window._resShared.startPolling(); });
            } else {
                setSyncPill({ kind: 'offline', text: 'Local only' });
            }

        })();
    </script>
</body>
</html>
