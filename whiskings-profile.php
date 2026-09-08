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
    <title><?php echo $is_sweet ? 'My Jar Tag' : 'My Profile'; ?> • <?php echo pbj_hub_label('settings'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.5rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 640px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 12px; font-size: 1.05rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 140px; }
        .btn { border: none; border-radius: 14px; padding: 14px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; width: 100%; box-sizing: border-box; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); margin-top: 10px; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 14px; line-height: 1.4; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .avatar { width: 72px; height: 72px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 14px; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163; color: #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44; color: #1A2A44;<?php endif; ?> }
    </style>
</head>
<body>
    <div class="header">
        <a href="/settings" class="back-link">← <?php echo pbj_back_to_hub('settings'); ?></a>
        <h1><?php echo $is_sweet ? 'My Jar Tag' : 'My Profile'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'How you show up across the app' : 'How you appear across the app'; ?></p>
    </div>
    <div class="content">
        <div class="card">
            <div class="avatar" id="avatar">?</div>
            <p class="hint"><?php echo $is_sweet
                ? 'This name is used as “from” on Jelly Jar posts and DMs. Saved on this device (and session) for now.'
                : 'Used as your name on messages and DMs. Saved on this device for now.'; ?></p>
            <form id="profile-form">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Display name' : 'Display name'; ?></label>
                    <input id="p-name" required placeholder="<?php echo $is_sweet ? 'What should we call you?' : 'Your name'; ?>">
                </div>
                <div class="field-row">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Role / title' : 'Role / title'; ?></label>
                        <select id="p-role">
                            <option value="Team">Team</option>
                            <option value="Server">Server</option>
                            <option value="Host">Host</option>
                            <option value="BOH">BOH</option>
                            <option value="Manager">Manager</option>
                            <option value="GM">GM</option>
                            <option value="Owner">Owner</option>
                        </select>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Preferred area' : 'Preferred area'; ?></label>
                        <select id="p-area">
                            <option value="both"><?php echo $is_sweet ? 'Whole house' : 'Whole house'; ?></option>
                            <option value="foh">FOH</option>
                            <option value="boh">BOH</option>
                        </select>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Phone' : 'Phone'; ?></label><input id="p-phone" type="tel"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label><input id="p-email" type="email"></div>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Bio / fun fact' : 'Notes'; ?></label>
                    <textarea id="p-bio" rows="3" placeholder="<?php echo $is_sweet ? 'Optional — allergies, go-to station, coffee order…' : 'Optional notes'; ?>"></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save my jar tag ✨' : 'Save profile'; ?></button>
            </form>
            <a href="/settings" class="btn btn-secondary"><?php echo pbj_back_to_hub('settings'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Profile saved 💾' : 'Profile saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        const KEY = 'pbj_whiskings_profile_v1';
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyWhiskPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                if (!canP('settings.profile')) {
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

        const sessionName = <?php echo json_encode($_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Tester'); ?>;
        const sessionRole = <?php echo json_encode($_SESSION['role'] ?? 'Team'); ?>;

        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) {
                    return { name: sessionName, role: sessionRole === 'admin' ? 'Manager' : (sessionRole || 'Team'), area: 'both', phone: '', email: '', bio: '' };
                }
                return r;
            } catch (e) {
                return { name: sessionName, role: 'Team', area: 'both', phone: '', email: '', bio: '' };
            }
        }

        function applyToSession(p) {
            // Keep PHP session in sync via a tiny cookie-free approach: store for JS pages only.
            // Also set document title vibe.
            try {
                // sessionStorage so other same-tab tools can read preferred name
                sessionStorage.setItem('pbj_display_name', p.name || '');
                sessionStorage.setItem('pbj_display_role', p.role || '');
            } catch (e) {}
        }

        function paint(p) {
            document.getElementById('p-name').value = p.name || '';
            document.getElementById('p-role').value = p.role || 'Team';
            document.getElementById('p-area').value = p.area || 'both';
            document.getElementById('p-phone').value = p.phone || '';
            document.getElementById('p-email').value = p.email || '';
            document.getElementById('p-bio').value = p.bio || '';
            var initial = (p.name || '?').trim().charAt(0).toUpperCase() || '?';
            document.getElementById('avatar').textContent = initial;
        }

        var profile = load();
        paint(profile);
        applyToSession(profile);

        document.getElementById('profile-form').addEventListener('submit', function (e) {
            e.preventDefault();
            profile = {
                name: document.getElementById('p-name').value.trim(),
                role: document.getElementById('p-role').value,
                area: document.getElementById('p-area').value,
                phone: document.getElementById('p-phone').value.trim(),
                email: document.getElementById('p-email').value.trim(),
                bio: document.getElementById('p-bio').value.trim()
            };
            localStorage.setItem(KEY, JSON.stringify(profile));
            applyToSession(profile);
            paint(profile);
            // Best-effort: update session name via fetch to a tiny endpoint? Skip — session is PHP.
            // Write a cookie so next PHP page loads could read it if we want later.
            document.cookie = 'pbj_display_name=' + encodeURIComponent(profile.name) + '; path=/; max-age=31536000';
            var t = document.getElementById('toast');
            t.classList.add('show');
            setTimeout(function () { t.classList.remove('show'); }, 1200);
        });
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyWhiskPerms);
            document.addEventListener('pbj-perms-ready', applyWhiskPerms);
})();
    </script>
</body>
</html>
