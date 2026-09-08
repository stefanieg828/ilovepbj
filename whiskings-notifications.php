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
    <title><?php echo $is_sweet ? 'Ping Me' : 'Notifications'; ?> • <?php echo pbj_hub_label('settings'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.6rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 640px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .card { background: white; border-radius: 18px; padding: 8px 0; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 20px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .row:last-child { border-bottom: none; }
        .row-title { font-size: 1.1rem; margin-bottom: 4px; }
        .row-desc { font-size: 0.92rem; opacity: 0.7; line-height: 1.35; }
        .switch { position: relative; width: 52px; height: 30px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; border-radius: 999px; background: <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; transition: 0.2s; }
        .slider:before { content: ''; position: absolute; height: 24px; width: 24px; left: 3px; top: 3px; border-radius: 50%; background: white; transition: 0.2s; box-shadow: 0 1px 4px rgba(0,0,0,0.15); }
        .switch input:checked + .slider { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .switch input:checked + .slider:before { transform: translateX(22px); }
        .btn { display: block; text-align: center; border-radius: 14px; padding: 14px 16px; text-decoration: none; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #E55163; color: white;<?php else: ?>font-family: 'Lora', serif; background: #1A2A44; color: white;<?php endif; ?> }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>
    <div class="header">
        <a href="/settings" class="back-link">← <?php echo pbj_back_to_hub('settings'); ?></a>
        <h1><?php echo $is_sweet ? 'Ping Me' : 'Notifications'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Choose what you want to hear about' : 'Choose what you want alerts for'; ?></p>
    </div>
    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Push notifications aren\'t live yet — these prefs are saved so we\'re ready when they are. Flip what matters to you 🔔'
                : 'Push is not live yet. Preferences are saved for when notifications are connected.'; ?>
        </div>
        <div class="card" id="toggles"></div>
        <a href="/settings" class="btn"><?php echo pbj_back_to_hub('settings'); ?></a>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        const KEY = 'pbj_whiskings_notifications_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyWhiskPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                if (!canP('settings.notifications')) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('wh-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="wh-denied">No permission for this settings page.</div>');
                    }
                    document.querySelectorAll('button.btn-primary, form button[type=submit]').forEach(function(el){ el.style.display='none'; });
                    document.querySelectorAll('input,select,textarea').forEach(function(el){ el.disabled = true; });
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        const items = isSweet ? [
            { id: 'announcements', title: 'Team Announcements', desc: 'Restaurant-wide posts in the Jelly Jar' },
            { id: 'broadcasts', title: 'Manager Broadcasts', desc: 'Urgent alerts & schedule changes' },
            { id: 'dms', title: 'Direct Messages', desc: 'One-on-one chats from teammates' },
            { id: 'shiftNotes', title: 'Shift Notes & Handoffs', desc: 'What the last crew left for you' },
            { id: 'foh', title: 'FOH channel', desc: 'Front-of-house floor updates' },
            { id: 'boh', title: 'BOH channel', desc: 'Kitchen 86s, prep & line notes' },
            { id: 'schedule', title: 'Schedule reminders', desc: 'In-app / browser alerts before shifts (timing set under Schedules)' },
            { id: 'listComplete', title: 'Checklist & prep done', desc: 'When FOH/BOH lists or prep finish for the day (needs the ops permission)' }
        ] : [
            { id: 'announcements', title: 'Team Announcements', desc: 'Restaurant-wide posts' },
            { id: 'broadcasts', title: 'Manager Broadcasts', desc: 'Urgent alerts and schedule changes' },
            { id: 'dms', title: 'Direct Messages', desc: 'One-on-one chats' },
            { id: 'shiftNotes', title: 'Shift Notes & Handoffs', desc: 'Notes from the previous shift' },
            { id: 'foh', title: 'FOH channel', desc: 'Front-of-house updates' },
            { id: 'boh', title: 'BOH channel', desc: 'Kitchen updates' },
            { id: 'schedule', title: 'Schedule reminders', desc: 'In-app / browser alerts before shifts (timing set under Schedules)' },
            { id: 'listComplete', title: 'Checklist & prep complete', desc: 'When FOH/BOH checklists or prep lists finish for the day (requires permission)' }
        ];

        function defaults() {
            var o = {};
            items.forEach(function (i) { o[i.id] = true; });
            return o;
        }
        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                return r || defaults();
            } catch (e) { return defaults(); }
        }
        function save(state) {
            localStorage.setItem(KEY, JSON.stringify(state));
            var t = document.getElementById('toast');
            t.classList.add('show');
            setTimeout(function () { t.classList.remove('show'); }, 1000);
        }

        var state = load();
        var root = document.getElementById('toggles');
        root.innerHTML = items.map(function (item) {
            var on = state[item.id] !== false;
            return '<div class="row">' +
                '<div><div class="row-title">' + item.title + '</div>' +
                '<div class="row-desc">' + item.desc + '</div></div>' +
                '<label class="switch"><input type="checkbox" data-id="' + item.id + '"' + (on ? ' checked' : '') + '>' +
                '<span class="slider"></span></label></div>';
        }).join('');

        root.addEventListener('change', function (e) {
            var input = e.target.closest('input[data-id]');
            if (!input) return;
            state[input.dataset.id] = input.checked;
            save(state);
        });
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyWhiskPerms);
            document.addEventListener('pbj-perms-ready', applyWhiskPerms);
})();
    </script>
</body>
</html>
