<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

$greetings = $is_sweet
    ? [
        [
            'title' => 'Door / Host Welcome',
            'script' => '"Welcome to ilovepbj! How many in your party today?"',
            'tip' => 'Eye contact, big smile, and a warm "we\'re so glad you\'re here."',
        ],
        [
            'title' => 'Table Greeting (within 60 seconds)',
            'script' => '"Hi friends! I\'m ___ and I\'ll be taking care of you. Can I start you with waters, or something bubbly?"',
            'tip' => 'Introduce yourself, set the vibe, and offer a first decision quickly.',
        ],
        [
            'title' => 'First-Timers',
            'script' => '"First time with us? Perfect — we\'re all about cozy sandwiches & sweet little moments. Want a quick favorite or two?"',
            'tip' => 'Guide gently. Offer 1–2 signatures instead of listing the whole menu.',
        ],
        [
            'title' => 'Regulars / VIPs',
            'script' => '"So good to see you again! Want the usual, or feeling adventurous today?"',
            'tip' => 'Use their name if you know it. Remember allergies, seating prefs, and go-tos.',
        ],
        [
            'title' => 'Phone / Takeout',
            'script' => '"Thanks for calling ilovepbj! Are you placing an order for pickup, or can I help with something else?"',
            'tip' => 'Smile on the phone — guests can hear it. Confirm name, time, and special requests.',
        ],
    ]
    : [
        [
            'title' => 'Door / Host Welcome',
            'script' => '"Welcome to ilovepbj. How many in your party?"',
            'tip' => 'Make eye contact, smile, and acknowledge every guest promptly.',
        ],
        [
            'title' => 'Table Greeting (within 60 seconds)',
            'script' => '"Hi, I\'m ___. I\'ll be taking care of you. Can I start you with waters or a drink?"',
            'tip' => 'Introduce yourself and offer an early decision point.',
        ],
        [
            'title' => 'First-Timers',
            'script' => '"First time with us? Happy to share a couple of favorites if you\'d like."',
            'tip' => 'Recommend 1–2 signatures rather than walking the entire menu.',
        ],
        [
            'title' => 'Regulars / VIPs',
            'script' => '"Great to see you again. Would you like your usual, or something new?"',
            'tip' => 'Use names when known. Note allergies, seating, and preferences.',
        ],
        [
            'title' => 'Phone / Takeout',
            'script' => '"Thanks for calling ilovepbj. Are you placing a pickup order?"',
            'tip' => 'Confirm name, ready time, and any special requests before hanging up.',
        ],
    ];

$steps = $is_sweet
    ? [
        ['title' => '1 · Greet Fast', 'body' => 'Guest acknowledged within 60 seconds of seating — even if it\'s just a wave and "I\'ll be right with you!"'],
        ['title' => '2 · Drinks First', 'body' => 'Offer water immediately, then drinks. Keep the table never empty-handed for long.'],
        ['title' => '3 · Know the Menu', 'body' => 'Be ready with favorites, allergies, swaps, and what\'s 86\'d. Confidence is hospitality.'],
        ['title' => '4 · Repeat the Order', 'body' => 'Read it back with love. Catch allergies, mods, and "no onion" moments before they hit the kitchen.'],
        ['title' => '5 · Check Back', 'body' => 'Two-bite check: "How\'s everything tasting?" Fix little things before they become big things.'],
        ['title' => '6 · Clear Kindly', 'body' => 'Clear finished plates without rushing dessert dreams. Ask before stacking over guests.'],
        ['title' => '7 · Offer Sweet Finishes', 'body' => 'Dessert, coffee, or a little something to-go. Soft sell, never pushy.'],
        ['title' => '8 · Thank & Invite Back', 'body' => '"Thank you so much for coming in — we can\'t wait to see you again!" Escort if it feels right.'],
    ]
    : [
        ['title' => '1 · Greet Promptly', 'body' => 'Acknowledge guests within 60 seconds of seating, even if you cannot fully greet yet.'],
        ['title' => '2 · Drinks First', 'body' => 'Offer water immediately, then take beverage orders.'],
        ['title' => '3 · Know the Menu', 'body' => 'Be ready with favorites, allergies, substitutions, and 86 items.'],
        ['title' => '4 · Repeat the Order', 'body' => 'Confirm modifications and allergies before sending to the kitchen.'],
        ['title' => '5 · Check Back', 'body' => 'Return within two bites to confirm quality and fix issues early.'],
        ['title' => '6 · Clear Professionally', 'body' => 'Clear finished plates without rushing. Never stack plates over guests.'],
        ['title' => '7 · Offer Next Steps', 'body' => 'Suggest dessert, coffee, or to-go options when appropriate.'],
        ['title' => '8 · Thank & Invite Back', 'body' => 'Thank guests sincerely and invite them to return.'],
    ];

$recovery = $is_sweet
    ? [
        [
            'title' => 'L.A.S.T. Method',
            'body' => 'Listen · Apologize · Solve · Thank. Every recovery starts with real listening — no interrupting, no defending.',
        ],
        [
            'title' => 'Slow Ticket / Long Wait',
            'body' => '"I\'m so sorry for the wait — thank you for your patience. I\'m checking with the kitchen right now and I\'ll update you in two minutes." Then actually update them.',
        ],
        [
            'title' => 'Wrong Order / Missed Mod',
            'body' => '"You\'re absolutely right — that\'s on us. I\'m fixing this immediately." Remake or replace without making the guest feel difficult.',
        ],
        [
            'title' => 'Food Quality Issue',
            'body' => 'Never argue taste. Offer remake, swap, or remove from the check. Loop in a manager when needed.',
        ],
        [
            'title' => 'Spill or Mess',
            'body' => 'Apologize, clean quickly, offer napkins / refresh, and check clothes if needed. Stay calm and kind.',
        ],
        [
            'title' => 'When to Get a Manager',
            'body' => 'Allergy concerns, repeated complaints, refunds beyond your authority, unsafe situations, or anytime a guest asks for one.',
        ],
        [
            'title' => 'Magic Softeners',
            'body' => 'Comp a drink, dessert, or a little treat when it turns the story around. Recovery is cheaper than a lost regular.',
        ],
    ]
    : [
        [
            'title' => 'L.A.S.T. Method',
            'body' => 'Listen · Apologize · Solve · Thank. Start with full attention — no interrupting or defending.',
        ],
        [
            'title' => 'Slow Ticket / Long Wait',
            'body' => 'Apologize, thank them for patience, check with the kitchen, and return with a real update.',
        ],
        [
            'title' => 'Wrong Order / Missed Mod',
            'body' => 'Own the error, fix it immediately, and do not make the guest feel difficult.',
        ],
        [
            'title' => 'Food Quality Issue',
            'body' => 'Do not argue taste. Offer remake, swap, or remove from the check. Involve a manager when needed.',
        ],
        [
            'title' => 'Spill or Mess',
            'body' => 'Apologize, clean promptly, offer refresh support, and check on the guest.',
        ],
        [
            'title' => 'When to Get a Manager',
            'body' => 'Allergy concerns, repeated complaints, refunds beyond authority, safety issues, or guest request.',
        ],
        [
            'title' => 'Recovery Tools',
            'body' => 'Comp a drink, dessert, or small item when appropriate. Recover the experience before it becomes a review.',
        ],
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_sweet ? 'Guest Service Standards' : 'Guest Service Standards'; ?> • <?php echo pbj_hub_label('foh'); ?> • ilovepbj ops</title>

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
            padding: 24px 20px;
            max-width: 760px;
            margin: 0 auto;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }

        .tab {
            flex: 1;
            border: none;
            border-radius: 14px;
            padding: 14px 8px;
            font-size: 0.98rem;
            cursor: pointer;
            transition: all 0.2s;
            line-height: 1.25;
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

        .panel {
            display: none;
        }

        .panel.active {
            display: block;
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 20px 22px;
            margin-bottom: 14px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .card h3 {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 1.45rem;
            margin: 0 0 10px;
            line-height: 1.2;
        }

        .script {
            font-size: 1.12rem;
            line-height: 1.45;
            margin: 0 0 12px;
            padding: 14px 16px;
            border-radius: 12px;
            <?php if ($is_sweet): ?>
                background: #FFF5F6;
                border-left: 4px solid #E55163;
            <?php else: ?>
                background: #EEF2F8;
                border-left: 4px solid #1A2A44;
            <?php endif; ?>
        }

        .tip, .body-text {
            margin: 0;
            font-size: 1.05rem;
            line-height: 1.45;
            opacity: 0.85;
        }

        .tip strong {
            opacity: 1;
        }

        .step-list .card {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .step-num {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            margin-top: 2px;
            <?php if ($is_sweet): ?>
                background: #E55163;
            <?php else: ?>
                background: #1A2A44;
            <?php endif; ?>
        }

        .step-list .card h3 {
            margin-bottom: 6px;
        }

        .checklist {
            background: white;
            border-radius: 18px;
            padding: 8px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 14px;
        }

        .check-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>;
            cursor: pointer;
            transition: background 0.15s;
            user-select: none;
        }

        .check-item:last-child {
            border-bottom: none;
        }

        .check-item:hover {
            background: <?php echo $is_sweet ? '#FFF8F9' : '#F8F5F1'; ?>;
        }

        .check-item input {
            display: none;
        }

        .checkbox {
            width: 26px;
            height: 26px;
            min-width: 26px;
            border-radius: 8px;
            border: 2px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
            font-size: 0.95rem;
            color: white;
        }

        .check-item.done .checkbox {
            <?php if ($is_sweet): ?>
                background: #E55163;
            <?php else: ?>
                background: #1A2A44;
            <?php endif; ?>
        }

        .check-item.done .checkbox::after {
            content: '✓';
        }

        .check-body {
            flex: 1;
        }

        .check-body h3 {
            <?php if ($is_sweet): ?>
                font-family: 'ModernLoveCaps', serif;
                color: #E55163;
            <?php else: ?>
                font-family: 'Lora', serif;
                color: #1A2A44;
            <?php endif; ?>
            font-size: 1.25rem;
            margin: 0 0 6px;
        }

        .check-body p {
            margin: 0;
            font-size: 1.02rem;
            line-height: 1.4;
            opacity: 0.85;
        }

        .check-item.done .check-body h3,
        .check-item.done .check-body p {
            opacity: 0.5;
        }

        .check-item.done .check-body h3 {
            text-decoration: line-through;
        }

        .progress-card {
            background: white;
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .progress-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 12px;
        }

        .progress-count {
            opacity: 0.8;
            white-space: nowrap;
        }

        .progress-bar {
            height: 12px;
            border-radius: 999px;
            background: <?php echo $is_sweet ? '#F7E0E4' : '#D9E0EA'; ?>;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            border-radius: 999px;
            transition: width 0.25s ease;
            <?php if ($is_sweet): ?>
                background: #E55163;
            <?php else: ?>
                background: #1A2A44;
            <?php endif; ?>
        }

        .section-intro {
            margin: 0 0 16px;
            font-size: 1.05rem;
            line-height: 1.45;
            opacity: 0.8;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .btn {
            flex: 1;
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

        .hidden-on-greetings .btn-secondary {
            display: none;
        }

        .panel-greetings .btn-secondary,
        .panel-recovery .btn-secondary {
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="showtime.php" class="back-link">← <?php echo pbj_back_to_hub('foh'); ?></a>
        <h1><?php echo $is_sweet ? 'Guest Service Standards' : 'Guest Service Standards'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Scripts, hospitality steps & recovery magic' : 'Scripts, hospitality steps, and recovery tips'; ?></p>
    </div>

    <div class="content">
        <div class="tabs">
            <button type="button" class="tab active" data-tab="greetings"><?php echo $is_sweet ? '💬 Greetings' : 'Greetings'; ?></button>
            <button type="button" class="tab" data-tab="steps"><?php echo $is_sweet ? '✨ Steps' : 'Steps'; ?></button>
            <button type="button" class="tab" data-tab="recovery"><?php echo $is_sweet ? '🩹 Recovery' : 'Recovery'; ?></button>
        </div>

        <!-- Greetings -->
        <div class="panel active" id="panel-greetings">
            <p class="section-intro"><?php echo $is_sweet ? 'Use these as warm starting points — make them yours, just keep the heart.' : 'Use these as starting points and adapt to your natural voice.'; ?></p>
            <?php foreach ($greetings as $g): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($g['title']); ?></h3>
                <p class="script"><?php echo htmlspecialchars($g['script']); ?></p>
                <p class="tip"><strong><?php echo $is_sweet ? 'Sweet tip: ' : 'Tip: '; ?></strong><?php echo htmlspecialchars($g['tip']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Hospitality Steps (checklist) -->
        <div class="panel" id="panel-steps">
            <div class="progress-card" id="steps-progress-card">
                <div class="progress-top">
                    <span><?php echo $is_sweet ? 'Service flow checklist' : 'Service flow checklist'; ?></span>
                    <span class="progress-count" id="progress-count">0 / 0</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill"></div>
                </div>
            </div>
            <p class="section-intro"><?php echo $is_sweet ? 'Tap through the 8 steps of a beautiful table — practice makes muscle memory.' : 'Work through the 8 steps of a complete table experience.'; ?></p>
            <div class="checklist" id="steps-checklist">
                <?php foreach ($steps as $i => $step): ?>
                <label class="check-item" data-key="step-<?php echo $i; ?>">
                    <input type="checkbox">
                    <span class="checkbox"></span>
                    <span class="check-body">
                        <h3><?php echo htmlspecialchars($step['title']); ?></h3>
                        <p><?php echo htmlspecialchars($step['body']); ?></p>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recovery -->
        <div class="panel" id="panel-recovery">
            <p class="section-intro"><?php echo $is_sweet ? 'When things wobble, this is how we turn it into a love story.' : 'Use these frameworks when something goes wrong.'; ?></p>
            <?php foreach ($recovery as $r): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($r['title']); ?></h3>
                <p class="body-text"><?php echo htmlspecialchars($r['body']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="actions" id="actions-row">
            <button type="button" class="btn btn-secondary" id="reset-btn" style="display:none;"><?php echo $is_sweet ? 'Reset steps' : 'Reset steps'; ?></button>
            <a href="showtime.php" class="btn btn-primary"><?php echo pbj_back_to_hub('foh'); ?></a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>

    <script>
        (function () {
            const storageKey = 'pbj_showtime_guest_service_steps_v1';
            const tabs = document.querySelectorAll('.tab');
            const panels = {
                greetings: document.getElementById('panel-greetings'),
                steps: document.getElementById('panel-steps'),
                recovery: document.getElementById('panel-recovery')
            };
            const progressCount = document.getElementById('progress-count');
            const progressFill = document.getElementById('progress-fill');
            const resetBtn = document.getElementById('reset-btn');
            let activeTab = 'greetings';

            function loadState() {
                try {
                    return JSON.parse(localStorage.getItem(storageKey) || '{}');
                } catch (e) {
                    return {};
                }
            }

            function saveState(state) {
                localStorage.setItem(storageKey, JSON.stringify(state));
            }

            function updateProgress() {
                const items = document.querySelectorAll('#steps-checklist .check-item');
                const total = items.length;
                let done = 0;
                items.forEach(function (item) {
                    if (item.classList.contains('done')) done++;
                });
                progressCount.textContent = done + ' / ' + total;
                progressFill.style.width = total ? ((done / total) * 100) + '%' : '0%';
            }

            function applyState() {
                const state = loadState();
                document.querySelectorAll('#steps-checklist .check-item').forEach(function (item) {
                    const checked = !!state[item.dataset.key];
                    item.classList.toggle('done', checked);
                    item.querySelector('input').checked = checked;
                });
                updateProgress();
            }

            function syncResetVisibility() {
                resetBtn.style.display = activeTab === 'steps' ? 'block' : 'none';
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    activeTab = tab.dataset.tab;
                    tabs.forEach(function (t) { t.classList.toggle('active', t === tab); });
                    Object.keys(panels).forEach(function (key) {
                        panels[key].classList.toggle('active', key === activeTab);
                    });
                    syncResetVisibility();
                });
            });

            document.querySelectorAll('#steps-checklist .check-item').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const input = item.querySelector('input');
                    const next = !input.checked;
                    input.checked = next;
                    item.classList.toggle('done', next);

                    const state = loadState();
                    if (next) {
                        state[item.dataset.key] = true;
                    } else {
                        delete state[item.dataset.key];
                    }
                    saveState(state);
                    updateProgress();
                });
            });

            resetBtn.addEventListener('click', function () {
                const state = loadState();
                document.querySelectorAll('#steps-checklist .check-item').forEach(function (item) {
                    item.classList.remove('done');
                    item.querySelector('input').checked = false;
                    delete state[item.dataset.key];
                });
                saveState(state);
                updateProgress();
            });

            applyState();
            syncResetVisibility();
        })();
    </script>
</body>
</html>
