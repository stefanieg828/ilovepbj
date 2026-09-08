<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'My Shift Vibe' : 'Shift Prefs'; ?> • <?php echo pbj_hub_label('settings'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.4rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 640px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 12px; font-size: 1.05rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip { border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; background: white; border-radius: 999px; padding: 8px 14px; cursor: pointer; font-size: 0.95rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.on { <?php if ($is_sweet): ?>background: #E55163; border-color: #E55163; color: white;<?php else: ?>background: #1A2A44; border-color: #1A2A44; color: white;<?php endif; ?> }
        .flag { display: flex; gap: 10px; }
        .flag label { flex: 1; text-align: center; border-radius: 14px; padding: 14px 10px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; cursor: pointer; background: white; }
        .flag input { display: none; }
        .flag label.on { <?php if ($is_sweet): ?>border-color: #E55163; background: #FFF5F6;<?php else: ?>border-color: #1A2A44; background: #EEF2F8;<?php endif; ?> }
        .btn { border: none; border-radius: 14px; padding: 14px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: block; width: 100%; box-sizing: border-box; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #E55163; color: white;<?php else: ?>font-family: 'Lora', serif; background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); margin-top: 10px; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 14px; line-height: 1.4; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .days { display: flex; flex-wrap: wrap; gap: 8px; }
        .days label { display: flex; align-items: center; gap: 4px; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/settings" class="back-link">← <?php echo pbj_back_to_hub('settings'); ?></a>
        <h1><?php echo $is_sweet ? 'My Shift Vibe' : 'Shift Prefs'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Stations, availability & today\'s flag' : 'Stations, availability, and today\'s flag'; ?></p>
    </div>
    <div class="content">
        <div class="card">
            <h2><?php echo $is_sweet ? 'Today I\'m on…' : 'Today I\'m on…'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Quick flag for handoffs and who\'s where.' : 'Quick flag for handoffs.'; ?></p>
            <div class="flag" id="today-flag">
                <label data-val="foh"><input type="radio" name="today" value="foh"><div>🍽️<br>FOH</div></label>
                <label data-val="boh"><input type="radio" name="today" value="boh"><div>🔥<br>BOH</div></label>
                <label data-val="off"><input type="radio" name="today" value="off"><div>🏠<br><?php echo $is_sweet ? 'Off' : 'Off'; ?></div></label>
            </div>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Preferred stations' : 'Preferred stations'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Tap all that feel like home.' : 'Select all that apply.'; ?></p>
            <div class="chips" id="stations"></div>
        </div>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Availability notes' : 'Availability notes'; ?></h2>
            <div class="field">
                <label><?php echo $is_sweet ? 'Usual days' : 'Usual days'; ?></label>
                <div class="days" id="days">
                    <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
                    <label><input type="checkbox" value="<?php echo $d; ?>"> <?php echo $d; ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="field">
                <label><?php echo $is_sweet ? 'Notes for schedulers' : 'Notes for schedulers'; ?></label>
                <textarea id="notes" rows="3" placeholder="<?php echo $is_sweet ? 'e.g. No mornings Tue · love expo · max 4 closes…' : 'e.g. No mornings Tue, prefer closes…'; ?>"></textarea>
            </div>
            <button type="button" class="btn" id="save"><?php echo $is_sweet ? 'Save my vibe ✨' : 'Save preferences'; ?></button>
            <a href="/settings" class="btn btn-secondary"><?php echo pbj_back_to_hub('settings'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        const KEY = 'pbj_whiskings_shift_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        const stationList = isSweet
            ? ['Host', 'Server', 'Expo', 'Bar', 'Grill', 'Fry', 'Prep', 'Dish', 'Manager']
            : ['Host', 'Server', 'Expo', 'Bar', 'Grill', 'Fry', 'Prep', 'Dish', 'Manager'];

        function load() {
            try {
                return JSON.parse(localStorage.getItem(KEY) || 'null') || { today: 'off', stations: [], days: [], notes: '' };
            } catch (e) { return { today: 'off', stations: [], days: [], notes: '' }; }
        }
        function save(state, toast) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (toast) {
                var t = document.getElementById('toast');
                t.classList.add('show');
                setTimeout(function () { t.classList.remove('show'); }, 1100);
            }
        }

        var state = load();

        // stations
        var stRoot = document.getElementById('stations');
        stRoot.innerHTML = stationList.map(function (s) {
            var on = (state.stations || []).indexOf(s) !== -1;
            return '<button type="button" class="chip' + (on ? ' on' : '') + '" data-station="' + s + '">' + s + '</button>';
        }).join('');
        stRoot.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-station]'); if (!btn) return;
            var s = btn.dataset.station;
            var i = state.stations.indexOf(s);
            if (i === -1) state.stations.push(s); else state.stations.splice(i, 1);
            btn.classList.toggle('on');
        });

        // today flag
        function paintFlag() {
            document.querySelectorAll('#today-flag label').forEach(function (lab) {
                lab.classList.toggle('on', lab.dataset.val === state.today);
                lab.querySelector('input').checked = lab.dataset.val === state.today;
            });
        }
        document.getElementById('today-flag').addEventListener('click', function (e) {
            var lab = e.target.closest('label'); if (!lab) return;
            state.today = lab.dataset.val;
            paintFlag();
            save(state, true);
        });
        paintFlag();

        // days
        document.querySelectorAll('#days input').forEach(function (c) {
            c.checked = (state.days || []).indexOf(c.value) !== -1;
        });
        document.getElementById('notes').value = state.notes || '';

        document.getElementById('save').addEventListener('click', function () {
            state.days = Array.prototype.map.call(document.querySelectorAll('#days input:checked'), function (c) { return c.value; });
            state.notes = document.getElementById('notes').value.trim();
            save(state, true);
        });
    })();
    </script>
</body>
</html>
