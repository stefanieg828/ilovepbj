<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$bypass = defined('AUTH_BYPASS') && AUTH_BYPASS;
$username = $_SESSION['username'] ?? 'tester';
$role = $_SESSION['role'] ?? '—';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Account' : 'Account'; ?> • <?php echo pbj_hub_label('settings'); ?> • ilovepbj ops</title>
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
        .card { background: white; border-radius: 18px; padding: 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .meta-row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .meta-row:last-child { border-bottom: none; }
        .meta-label { opacity: 0.65; }
        .meta-value { font-weight: 600; text-align: right; }
        .badge { display: inline-block; border-radius: 999px; padding: 4px 10px; font-size: 0.85rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .badge.warn { background: #FFF3D6; color: #8A5A00; }
        .hint { font-size: 0.95rem; opacity: 0.75; line-height: 1.45; margin: 0 0 14px; }
        .btn { border: none; border-radius: 14px; padding: 14px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: block; width: 100%; box-sizing: border-box; margin-bottom: 10px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>
    <div class="header">
        <a href="/settings" class="back-link">← <?php echo pbj_back_to_hub('settings'); ?></a>
        <h1><?php echo $is_sweet ? 'Account' : 'Account'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Logout, data & testing notes' : 'Logout, data, and account info'; ?></p>
    </div>
    <div class="content">
        <div class="card">
            <h2><?php echo $is_sweet ? 'Signed in as' : 'Signed in as'; ?></h2>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Username' : 'Username'; ?></span>
                <span class="meta-value"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Role' : 'Role'; ?></span>
                <span class="meta-value"><?php echo htmlspecialchars($role); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Theme' : 'Theme'; ?></span>
                <span class="meta-value"><?php echo htmlspecialchars(pbj_theme_display_name()); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Auth' : 'Auth'; ?></span>
                <span class="meta-value">
                    <?php if ($bypass): ?>
                        <span class="badge warn"><?php echo $is_sweet ? 'Bypass ON (testing)' : 'Bypass ON (testing)'; ?></span>
                    <?php else: ?>
                        <span class="badge"><?php echo $is_sweet ? 'Real login on' : 'Login required'; ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <?php
            $acctHouses = [];
            $acctUid = (int)($_SESSION['user_id'] ?? 0);
            if ($acctUid > 0 && function_exists('pbj_user_restaurants')) {
                $acctHouses = pbj_user_restaurants($pdo, $acctUid);
            }
            if ($acctHouses):
                $h0 = $acctHouses[0];
                $acctRid = (int) ($h0['id'] ?? 0);
                $acctSeats = $acctRid > 0 && function_exists('pbj_restaurant_seat_status')
                    ? pbj_restaurant_seat_status($pdo, $acctRid)
                    : null;
                $showInvite = $acctSeats === null || !empty($acctSeats['allows_invites']);
            ?>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'House' : 'Restaurant'; ?></span>
                <span class="meta-value"><?php echo htmlspecialchars($h0['name'] ?? '—'); ?></span>
            </div>
            <?php if ($showInvite): ?>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Invite code' : 'Invite code'; ?></span>
                <span class="meta-value" style="letter-spacing:0.06em;"><?php echo htmlspecialchars($h0['invite_code'] ?? '—'); ?></span>
            </div>
            <?php if ($acctSeats): ?>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Seats' : 'Team seats'; ?></span>
                <span class="meta-value"><?php
                    if (!empty($acctSeats['unlimited']) || $acctSeats['max'] === null) {
                        echo (int) $acctSeats['count'] . ($is_sweet ? ' · unlimited playground' : ' · unlimited');
                    } else {
                        echo (int) $acctSeats['count'] . ' / ' . (int) $acctSeats['max'];
                        if (empty($acctSeats['ok'])) {
                            echo $is_sweet ? ' · full — upgrade to add more' : ' · full';
                        }
                    }
                ?></span>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Invites' : 'Invites'; ?></span>
                <span class="meta-value"><?php echo $is_sweet ? 'Solo plan — upgrade a house to invite teammates' : 'Not included on this plan'; ?></span>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Plan & billing' : 'Plan & billing'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Need more seats or house features? Upgrade any time — Stripe prorates when you already subscribe 💕'
                : 'Upgrade for more seats or house features. Existing subscriptions are prorated by Stripe.'; ?></p>
            <a class="btn btn-primary" href="/billing/plans"><?php echo $is_sweet ? 'View plans & upgrade →' : 'View plans & upgrade →'; ?></a>
        </div>

        <?php if ($bypass): ?>
        <div class="card">
            <h2><?php echo $is_sweet ? 'Testing note' : 'Testing note'; ?></h2>
            <p class="hint">
                <?php echo $is_sweet
                    ? 'Login is bypassed in config.php (<code>AUTH_BYPASS</code>). Flip it to <strong>false</strong> before go-live so real accounts are required again.'
                    : 'Login is bypassed via AUTH_BYPASS in config.php. Set it to false before production.'; ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="card" data-perm="settings.view_own_perms" id="own-perms-card">
            <h2><?php echo $is_sweet ? 'Your permissions' : 'Your permissions'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'What this house lets your access role do. Owners & GMs can change the matrix under Team → Permissions 🔐'
                : 'What your access role can do. Owners and GMs edit the matrix under Team → Permissions.'; ?></p>
            <div class="meta-row">
                <span class="meta-label"><?php echo $is_sweet ? 'Access role' : 'Access role'; ?></span>
                <span class="meta-value" id="own-perm-role">…</span>
            </div>
            <div id="own-perm-list" style="margin-top:10px;max-height:280px;overflow:auto;font-size:0.92rem;line-height:1.4;"></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Local app data' : 'Local app data'; ?></h2>
            <p class="hint">
                <?php echo $is_sweet
                    ? 'Checklists, messages, inventory edits, and prefs live in this browser. Clear them if you want a fresh test kitchen — this can\'t be undone.'
                    : 'Checklists, messages, inventory, and prefs are stored in this browser. Clearing them cannot be undone.'; ?>
            </p>
            <button type="button" class="btn btn-danger" id="clear-data"><?php echo $is_sweet ? 'Clear my local app data' : 'Clear local app data'; ?></button>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Session' : 'Session'; ?></h2>
            <p class="hint">
                <?php echo $is_sweet
                    ? 'Logout ends this session. With bypass on, the next page load will auto-sign you back in as tester.'
                    : 'Logout ends this session. With bypass on, the next load auto-signs the test user in again.'; ?>
            </p>
            <a href="/logout" class="btn btn-primary"><?php echo $is_sweet ? 'Logout' : 'Logout'; ?></a>
            <a href="/settings" class="btn btn-secondary"><?php echo pbj_back_to_hub('settings'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Local data cleared ✨' : 'Local data cleared'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;

        function renderOwnPerms() {
            var roleEl = document.getElementById('own-perm-role');
            var listEl = document.getElementById('own-perm-list');
            if (!roleEl || !listEl) return;
            fetch('permissions-api.php?labels=1', { credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !data.ok) {
                        listEl.textContent = isSweet ? 'Could not load permissions' : 'Could not load';
                        return;
                    }
                    roleEl.textContent = data.roleLabel || data.role || '—';
                    var grants = data.grants || {};
                    var labels = data.labels || {};
                    var keys = Object.keys(grants).filter(function (k) { return grants[k]; }).sort();
                    if (!keys.length) {
                        listEl.innerHTML = '<p class="hint" style="margin:0;">' + (isSweet ? 'No special powers listed for this role.' : 'No grants listed.') + '</p>';
                        return;
                    }
                    listEl.innerHTML = '<ul style="margin:0;padding-left:1.1em;">' + keys.map(function (k) {
                        var lab = labels[k] || k;
                        return '<li style="margin-bottom:4px;">' + lab.replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</li>';
                    }).join('') + '</ul>';
                })
                .catch(function () {
                    listEl.textContent = isSweet ? 'Could not load permissions' : 'Could not load';
                });
        }
        renderOwnPerms();

        document.getElementById('clear-data').addEventListener('click', function () {
            if (!confirm(isSweet
                ? 'Clear ALL local app data on this device? Checklists, messages, inventory, costing prices, schedules — gone from this browser.'
                : 'Clear all local app data on this device? This cannot be undone.')) return;

            var keys = [];
            for (var i = 0; i < localStorage.length; i++) {
                var k = localStorage.key(i);
                if (k && k.indexOf('pbj_') === 0) keys.push(k);
            }
            keys.forEach(function (k) { localStorage.removeItem(k); });
            try {
                sessionStorage.removeItem('pbj_display_name');
                sessionStorage.removeItem('pbj_display_role');
            } catch (e) {}
            document.cookie = 'pbj_display_name=; path=/; max-age=0';

            var t = document.getElementById('toast');
            t.classList.add('show');
            setTimeout(function () { t.classList.remove('show'); }, 1400);
        });
    })();
    </script>
</body>
</html>
