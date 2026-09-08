<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$uid = (int) ($_SESSION['user_id'] ?? 0);
$memberFlash = '';
$memberErr = '';

// Houses this user can manage (owned first, then memberships)
$manageableHouses = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM restaurants WHERE owner_id = ? ORDER BY id ASC');
    $stmt->execute([$uid]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $h) {
        $manageableHouses[(int) $h['id']] = $h;
    }
} catch (Throwable $e) {
    // ignore
}
$houses = $uid > 0 ? pbj_user_restaurants($pdo, $uid) : [];
foreach ($houses as $h) {
    $hid = (int) ($h['id'] ?? 0);
    if ($hid > 0 && !isset($manageableHouses[$hid])) {
        $manageableHouses[$hid] = $h;
    }
}
// Which house are we viewing?
$requestedHouse = (int) ($_GET['house_id'] ?? $_POST['house_id'] ?? 0);
$primaryHouse = null;
if ($requestedHouse > 0 && isset($manageableHouses[$requestedHouse])) {
    $primaryHouse = $manageableHouses[$requestedHouse];
} elseif ($manageableHouses) {
    // Prefer DEMO playground, then first owned
    foreach ($manageableHouses as $h) {
        if (strtoupper((string) ($h['invite_code'] ?? '')) === 'DEMO-PBJ') {
            $primaryHouse = $h;
            break;
        }
    }
    if (!$primaryHouse) {
        $primaryHouse = reset($manageableHouses) ?: null;
    }
}
$houseId = (int) ($primaryHouse['id'] ?? 0);
$houseCode = (string) ($primaryHouse['invite_code'] ?? '');
$houseName = (string) ($primaryHouse['name'] ?? '');
$seatStatus = $houseId > 0 ? pbj_restaurant_seat_status($pdo, $houseId) : null;
$isDemoHouse = $houseId > 0 && pbj_restaurant_is_demo_house($pdo, $houseId);
$isUnlimited = !empty($seatStatus['unlimited']);
$canManageMembers = $houseId > 0 && function_exists('pbj_can_manage_house_members')
    ? pbj_can_manage_house_members($pdo, $houseId, $uid)
    : false;

// Kick / remove a login from the house
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member']) && $houseId > 0) {
    $target = (int) ($_POST['user_id'] ?? 0);
    $res = pbj_remove_restaurant_member($pdo, $houseId, $target, $uid);
    if (!empty($res['ok'])) {
        $memberFlash = $is_sweet ? 'Removed from the house — their login still exists, just not on this kitchen 💕' : 'Removed from this house.';
    } else {
        $memberErr = (string) ($res['error'] ?? 'Could not remove.');
    }
}

// Edit house login — owners: employee name/email/username/role/access + password reset
// Platform admin: full profile (incl. direct password on playground accounts)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member']) && $houseId > 0) {
    $target = (int) ($_POST['user_id'] ?? 0);
    $isPlat = function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($_SESSION['email'] ?? '');

    if (!empty($_POST['send_reset_only'])) {
        // Owner or platform admin may email a reset for someone on this house
        if (!pbj_can_manage_house_members($pdo, $houseId, $uid)) {
            $memberErr = 'Only house owners/managers can reset team passwords.';
        } else {
            $onHouse = $pdo->prepare('SELECT 1 FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
            $onHouse->execute([$target, $houseId]);
            if (!$onHouse->fetchColumn()) {
                $memberErr = 'That person isn’t on this house.';
            } else {
                $res = pbj_admin_send_password_reset($pdo, $target);
                if (!empty($res['ok'])) {
                    $memberFlash = $res['message'] ?? 'Reset sent.';
                    if ($isPlat && !empty($res['link'])) {
                        $memberFlash .= ' Link: ' . $res['link'];
                    }
                } else {
                    $memberErr = (string) ($res['error'] ?? 'Reset failed.');
                }
            }
        }
    } elseif ($isPlat && !empty($_POST['full_profile'])) {
        $res = pbj_admin_save_user_profile($pdo, $target, [
            'full_name' => $_POST['full_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'foh',
            'access_status' => $_POST['access_status'] ?? 'approved',
            'new_password' => $_POST['new_password'] ?? '',
            'new_password_confirm' => $_POST['new_password_confirm'] ?? '',
        ]);
        if (!empty($res['ok'])) {
            $roleSync = strtolower(trim((string) ($_POST['role'] ?? 'foh')));
            if ($roleSync === 'admin') {
                $roleSync = 'admin';
            }
            try {
                $pdo->prepare('UPDATE user_restaurant SET role = ? WHERE user_id = ? AND restaurant_id = ?')
                    ->execute([$roleSync, $target, $houseId]);
            } catch (Throwable $e) {
                // ignore
            }
            $memberFlash = $res['message'] ?? ($is_sweet ? 'Profile saved 💕' : 'Saved.');
        } else {
            $memberErr = (string) ($res['error'] ?? 'Could not update.');
        }
    } elseif (!empty($_POST['employee_profile'])) {
        $res = pbj_owner_save_employee_profile($pdo, $houseId, $target, $uid, [
            'full_name' => $_POST['full_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'foh',
            'access_status' => $_POST['access_status'] ?? 'approved',
        ]);
        if (!empty($res['ok'])) {
            $memberFlash = $res['message'] ?? ($is_sweet ? 'Teammate updated 💕' : 'Saved.');
        } else {
            $memberErr = (string) ($res['error'] ?? 'Could not update.');
        }
    } else {
        $res = pbj_update_restaurant_member($pdo, $houseId, $target, $uid, [
            'role' => (string) ($_POST['role'] ?? ''),
            'access_status' => (string) ($_POST['access_status'] ?? ''),
        ]);
        if (!empty($res['ok'])) {
            $memberFlash = $is_sweet ? 'House login updated 💕' : 'Member updated.';
        } else {
            $memberErr = (string) ($res['error'] ?? 'Could not update.');
        }
    }
}

$isPlatAdminPage = function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($_SESSION['email'] ?? '');

$houseMembers = $houseId > 0 ? pbj_restaurant_members($pdo, $houseId) : [];
$realOwnerId = (int) ($primaryHouse['owner_id'] ?? 0);

$tracks = $is_sweet
    ? [
        ['id' => 'server', 'title' => 'Server / Host track', 'icon' => '✨', 'hint' => 'FOH hospitality path'],
        ['id' => 'boh', 'title' => 'BOH / Kitchen track', 'icon' => '🔥', 'hint' => 'Line & prep path'],
        ['id' => 'manager', 'title' => 'Shift lead / Manager', 'icon' => '📋', 'hint' => 'Keys, cash, coaching'],
    ]
    : [
        ['id' => 'server', 'title' => 'Server / Host track', 'icon' => '✨', 'hint' => 'FOH hospitality path'],
        ['id' => 'boh', 'title' => 'BOH / Kitchen track', 'icon' => '🔥', 'hint' => 'Line & prep path'],
        ['id' => 'manager', 'title' => 'Shift lead / Manager', 'icon' => '📋', 'hint' => 'Keys, cash, coaching'],
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Invite & Onboarding' : 'Invite & Onboarding'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 760px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; }
        .intro a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .pin-note { background: #FFF8E8; border: 1px solid #E8D59A; color: #8A6D1F; border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; font-size: 0.92rem; line-height: 1.45; }
        .tabs { display: flex; gap: 10px; margin-bottom: 16px; }
        .tab { flex: 1; border: none; border-radius: 14px; padding: 14px 10px; font-size: 1rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .panel { display: none; } .panel.active { display: block; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-tiny { padding: 6px 10px; font-size: 0.85rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .code-box { font-size: 1.6rem; letter-spacing: 0.12em; text-align: center; padding: 16px; border-radius: 14px; margin: 10px 0; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 2px dashed #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 2px dashed #C5D0DE;<?php endif; ?> }
        .invite { border-radius: 14px; padding: 14px; margin-bottom: 10px; <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;<?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?> }
        .invite.used { opacity: 0.6; }
        .track { border-radius: 16px; margin-bottom: 12px; overflow: hidden; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .track-head { display: flex; align-items: center; gap: 12px; padding: 16px; cursor: pointer; width: 100%; border: none; background: transparent; text-align: left; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .track-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
        .track-title { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.25rem; margin: 0; }
        .track-body { display: none; padding: 0 16px 16px; border-top: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .track.open .track-body { display: block; }
        .step-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; padding: 10px 4px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .step-row:last-child { border-bottom: none; }
        .step-check { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 160px; cursor: pointer; user-select: none; }
        .step-check input { display: none; }
        .checkbox { width: 24px; height: 24px; min-width: 24px; border-radius: 8px; border: 2px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; display: flex; align-items: center; justify-content: center; color: white; }
        .step-row.done .checkbox { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .step-row.done .checkbox::after { content: '✓'; }
        .step-row.done .step-label { opacity: 0.55; text-decoration: line-through; }
        .step-label { flex: 1; line-height: 1.35; }
        .step-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .step-edit-input { flex: 1; min-width: 140px; box-sizing: border-box; border-radius: 10px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 8px 10px; font-size: 0.95rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5;<?php endif; ?> }
        .empty-slot { text-align: center; padding: 16px; border: 2px dashed <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; border-radius: 12px; margin: 10px 0; opacity: 0.8; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .meta { font-size: 0.9rem; opacity: 0.75; }
        .team-empty { border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; line-height: 1.4; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .team-empty a { color: inherit; font-weight: 600; }
        .track-toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.75rem; margin-left: 6px; <?php if ($is_sweet): ?>background: #FFF5F6;<?php else: ?>background: #EEF2F8;<?php endif; ?> }
        .badge.unlimited { background: #6B4A8C; color: #BBE7DA; margin-left: 0; margin-right: 6px; }
        .member-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .member-row:last-child { border-bottom: none; }
        .member-meta { flex: 1; min-width: 160px; }
        .member-meta strong { display: block; }
        .note-box { white-space: pre-wrap; font-size: 0.92rem; line-height: 1.4; border-radius: 12px; padding: 12px; margin: 8px 0; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .flash-ok { background: #E8F8F1; color: #1F6B4A; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
        .flash-err { background: #FDECEA; color: #B71C1C; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/team" class="back-link">← <?php echo $is_sweet ? 'Back to Team & Roles' : 'Back to Team'; ?></a>
        <h1><?php echo $is_sweet ? 'Invite & Onboarding' : 'Invite & Onboarding'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'House codes, logins & training paths' : 'Invite codes, logins, and training paths'; ?></p>
    </div>
    <div class="content">
        <?php if ($memberFlash): ?><div class="flash-ok"><?php echo htmlspecialchars($memberFlash); ?></div><?php endif; ?>
        <?php if ($memberErr): ?><div class="flash-err"><?php echo htmlspecialchars($memberErr); ?></div><?php endif; ?>
        <?php
        $isPlatAdmin = function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($_SESSION['email'] ?? '');
        if ($isPlatAdmin):
            require_once __DIR__ . '/sales-playground.inc.php';
            require_once __DIR__ . '/sms-config.php';
            $salesIds = pbj_sales_playground_restaurant_ids($pdo);
            $salesId = $salesIds[0] ?? 0;
            $salesEpoch = $salesId ? pbj_sales_reset_epoch($pdo, $salesId) : 0;
            $smsReady = sms_is_configured();
            $playgroundHouses = sms_playground_houses($pdo);
        ?>
        <div class="card" style="border:2px solid #6B4A8C;">
            <h2><?php echo $is_sweet ? 'Playground restore ♻️' : 'Playground restore'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Covers <strong>both</strong> houses: <strong>ilovepbj Playground</strong> (<code>DEMO-PBJ</code>) and <strong>Sales Showcase</strong> (<code>SALES-PBJ</code>). '
                    . 'Testers can add / edit / delete anything. <strong>Reset to starters</strong> wipes live mess and puts the gold master back — the master lives on disk so deletes never eat it. '
                    . 'Nightly auto-reset at 2:00 AM Eastern for both.'
                : 'Resets DEMO-PBJ and SALES-PBJ from gold-master starters. Nightly 2:00 AM Eastern.'; ?></p>
            <p class="meta">
                Gold master modules: <strong id="gold-keys"><?php echo (int) pbj_sales_gold_master_key_count(); ?></strong>
                · Last epoch: <strong id="sales-epoch"><?php echo (int) $salesEpoch; ?></strong>
                <?php if ($salesEpoch): ?> · <?php echo htmlspecialchars(date('M j, g:ia T', $salesEpoch)); ?><?php endif; ?>
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
                <button type="button" class="btn btn-small btn-primary" id="sales-reset-now"><?php echo $is_sweet ? 'Reset BOTH playgrounds to starters now' : 'Reset both playgrounds now'; ?></button>
                <button type="button" class="btn btn-small btn-ghost" id="sales-save-starters"><?php echo $is_sweet ? 'Lock gold starters (from Playground when perfect)' : 'Save gold starters from Playground'; ?></button>
            </div>
            <p class="meta" style="margin-top:8px;opacity:0.8;"><?php echo $is_sweet
                ? '⚠️ Only click “Lock gold starters” when DEMO-PBJ looks perfect. Reset never overwrites that pack — it only restores from it.'
                : 'Only save gold starters when content is perfect. Reset restores; it does not rewrite gold.'; ?></p>
            <p class="meta" id="sales-admin-msg" style="margin-top:10px;"></p>
        </div>

        <div class="card" style="border:2px solid #E55163;">
            <h2><?php echo $is_sweet ? 'Send an invite 💌' : 'Send an invite'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Share the site + house code by <strong>text</strong> or <strong>email</strong> — email is perfect when they don’t want to share a phone number 💕 Works for <strong>Sales Showcase</strong> and <strong>ilovepbj Playground</strong>.'
                    . ($smsReady
                        ? ' Twilio is connected for texts.'
                        : ' Twilio isn’t set up yet — text opens your Messages app; email uses the server or your mail app.')
                : 'Send site URL + invite code by SMS or email. Email works when they prefer not to share a phone number.'; ?></p>
            <div class="field">
                <label><?php echo $is_sweet ? 'How to send' : 'Channel'; ?></label>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <label class="btn btn-small btn-ghost" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        <input type="radio" name="inv-channel" value="sms" checked> <?php echo $is_sweet ? '📱 Text' : 'Text'; ?>
                    </label>
                    <label class="btn btn-small btn-ghost" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        <input type="radio" name="inv-channel" value="email"> <?php echo $is_sweet ? '✉️ Email' : 'Email'; ?>
                    </label>
                </div>
            </div>
            <div class="field">
                <label><?php echo $is_sweet ? 'Playground' : 'Playground'; ?></label>
                <select id="sms-house">
                    <?php foreach ($playgroundHouses as $ph): ?>
                    <option value="<?php echo htmlspecialchars($ph['code']); ?>">
                        <?php echo htmlspecialchars($ph['name'] . ' · ' . $ph['code']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Their name (optional)' : 'Name (optional)'; ?></label>
                    <input id="sms-name" type="text" placeholder="<?php echo $is_sweet ? 'Sam' : 'Name'; ?>">
                </div>
                <div class="field" id="inv-phone-wrap">
                    <label><?php echo $is_sweet ? 'Mobile number' : 'Mobile number'; ?></label>
                    <input id="sms-phone" type="tel" inputmode="tel" placeholder="5551234567">
                </div>
                <div class="field" id="inv-email-wrap" style="display:none;">
                    <label><?php echo $is_sweet ? 'Email address' : 'Email address'; ?></label>
                    <input id="sms-email" type="email" inputmode="email" placeholder="sam@example.com" autocomplete="email">
                </div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;" id="inv-sms-actions">
                <button type="button" class="btn btn-small btn-primary" id="sms-send-btn"><?php echo $is_sweet ? 'Send text invite ✨' : 'Send text invite'; ?></button>
                <button type="button" class="btn btn-small btn-ghost" id="sms-device-btn"><?php echo $is_sweet ? 'Open in my Messages app' : 'Open in Messages'; ?></button>
            </div>
            <div style="display:none;flex-wrap:wrap;gap:8px;margin-top:4px;" id="inv-email-actions">
                <button type="button" class="btn btn-small btn-primary" id="email-send-btn"><?php echo $is_sweet ? 'Send email invite ✨' : 'Send email invite'; ?></button>
                <button type="button" class="btn btn-small btn-ghost" id="email-device-btn"><?php echo $is_sweet ? 'Open in my mail app' : 'Open in mail app'; ?></button>
            </div>
            <p class="meta" id="sms-msg" style="margin-top:10px;"></p>
            <div class="note-box" id="sms-preview" style="display:none;margin-top:10px;"></div>
        </div>
        <script>
        (function () {
            var msg = document.getElementById('sales-admin-msg');
            function postSales(action) {
                msg.textContent = 'Working…';
                return fetch('/sales-playground-api.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(action)
                }).then(function (r) { return r.json(); });
            }
            document.getElementById('sales-reset-now').addEventListener('click', function () {
                if (!confirm(<?php echo $is_sweet
                    ? "'Reset BOTH playgrounds (DEMO-PBJ + SALES-PBJ) to gold starters now?\\n\\n• All tester edits/deletes go away\\n• Logins stay\\n• Gold master on disk is untouched'"
                    : "'Reset both playgrounds to gold starters now?'"; ?>)) return;
                postSales({ action: 'reset' }).then(function (d) {
                    if (!d.ok) {
                        msg.textContent = 'Failed: ' + (d.message || d.error || 'unknown');
                        return;
                    }
                    var parts = [];
                    if (d.results) {
                        Object.keys(d.results).forEach(function (id) {
                            var r = d.results[id];
                            parts.push((r.house || ('#' + id)) + ': ' + (r.ok ? (r.keys + ' modules') : (r.error || 'fail')));
                            if (r.epoch) document.getElementById('sales-epoch').textContent = r.epoch;
                        });
                    }
                    if (d.gold_master_keys != null) {
                        var gk = document.getElementById('gold-keys');
                        if (gk) gk.textContent = d.gold_master_keys;
                    }
                    msg.textContent = (<?php echo $is_sweet ? "'Restored 💕 '" : "'Restored. '"; ?>) + parts.join(' · ');
                }).catch(function () { msg.textContent = 'Network error'; });
            });
            document.getElementById('sales-save-starters').addEventListener('click', function () {
                if (!confirm(<?php echo $is_sweet
                    ? "'Lock gold starters from ilovepbj Playground (DEMO-PBJ)?\\n\\nOnly do this when that house looks perfect.\\nPrevious gold is backed up automatically.'"
                    : "'Save gold starters from DEMO-PBJ? Previous gold is backed up.'"; ?>)) return;
                postSales({ action: 'save_starters', source_restaurant_id: 1 }).then(function (d) {
                    if (d.ok) {
                        msg.textContent = <?php echo $is_sweet ? "'Gold master locked with '" : "'Gold master saved: '"; ?> + d.keys + <?php echo $is_sweet ? "' modules 💕'" : "' modules.'"; ?>;
                        var gk = document.getElementById('gold-keys');
                        if (gk) gk.textContent = d.keys;
                    } else {
                        msg.textContent = 'Failed: ' + (d.error || 'unknown');
                    }
                }).catch(function () { msg.textContent = 'Network error'; });
            });

            var smsMsg = document.getElementById('sms-msg');
            var smsPreview = document.getElementById('sms-preview');
            var phoneWrap = document.getElementById('inv-phone-wrap');
            var emailWrap = document.getElementById('inv-email-wrap');
            var smsActions = document.getElementById('inv-sms-actions');
            var emailActions = document.getElementById('inv-email-actions');

            function channel() {
                var r = document.querySelector('input[name="inv-channel"]:checked');
                return r ? r.value : 'sms';
            }
            function syncChannelUi() {
                var ch = channel();
                var isEmail = ch === 'email';
                if (phoneWrap) phoneWrap.style.display = isEmail ? 'none' : '';
                if (emailWrap) emailWrap.style.display = isEmail ? '' : 'none';
                if (smsActions) smsActions.style.display = isEmail ? 'none' : 'flex';
                if (emailActions) emailActions.style.display = isEmail ? 'flex' : 'none';
                if (smsMsg) smsMsg.textContent = '';
            }
            document.querySelectorAll('input[name="inv-channel"]').forEach(function (el) {
                el.addEventListener('change', syncChannelUi);
            });
            syncChannelUi();

            function sendTextInvite(deviceOnly) {
                var phone = (document.getElementById('sms-phone').value || '').trim();
                var house = document.getElementById('sms-house').value;
                var name = (document.getElementById('sms-name').value || '').trim();
                if (!phone) {
                    smsMsg.textContent = <?php echo $is_sweet ? "'Need a mobile number, bestie 💕'" : "'Enter a mobile number.'"; ?>;
                    return;
                }
                smsMsg.textContent = 'Sending…';
                smsPreview.style.display = 'none';
                fetch('/invite-sms-api.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: phone,
                        house_code: house,
                        name: name,
                        device_sms: deviceOnly ? 1 : 0
                    })
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (!d.ok) {
                        smsMsg.textContent = d.error === 'bad_phone'
                            ? (<?php echo $is_sweet ? "'That number looks off — try 5551234567 or +15551234567'" : "'Invalid phone number'"; ?>)
                            : ('Failed: ' + (d.error || 'unknown'));
                        return;
                    }
                    if (d.message) {
                        smsPreview.style.display = 'block';
                        smsPreview.textContent = d.message;
                    }
                    if (d.mode === 'twilio') {
                        smsMsg.textContent = <?php echo $is_sweet ? "'Text sent via Twilio 💕'" : "'SMS sent.'"; ?>;
                        return;
                    }
                    smsMsg.textContent = d.hint || (<?php echo $is_sweet ? "'Opening Messages with the invite ready — hit Send 💕'" : "'Opening your Messages app — tap Send.'"; ?>);
                    if (d.sms_link) {
                        window.location.href = d.sms_link;
                    }
                }).catch(function () {
                    smsMsg.textContent = 'Network error';
                });
            }

            function sendEmailInvite(deviceOnly) {
                var email = (document.getElementById('sms-email').value || '').trim();
                var house = document.getElementById('sms-house').value;
                var name = (document.getElementById('sms-name').value || '').trim();
                if (!email) {
                    smsMsg.textContent = <?php echo $is_sweet ? "'Need an email address 💕'" : "'Enter an email address.'"; ?>;
                    return;
                }
                smsMsg.textContent = 'Sending…';
                smsPreview.style.display = 'none';
                fetch('/invite-email-api.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: email,
                        house_code: house,
                        name: name,
                        device_mail: deviceOnly ? 1 : 0
                    })
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (!d.ok) {
                        smsMsg.textContent = d.error === 'bad_email'
                            ? (<?php echo $is_sweet ? "'That email looks off — double-check it 💕'" : "'Invalid email address'"; ?>)
                            : ('Failed: ' + (d.error || 'unknown'));
                        return;
                    }
                    if (d.message) {
                        smsPreview.style.display = 'block';
                        smsPreview.textContent = d.message;
                    }
                    if (d.mode === 'mail') {
                        smsMsg.textContent = <?php echo $is_sweet ? "'Email invite sent 💕'" : "'Email invite sent.'"; ?>;
                        return;
                    }
                    smsMsg.textContent = d.hint || (<?php echo $is_sweet ? "'Opening your mail app — hit Send 💕'" : "'Opening your mail app — tap Send.'"; ?>);
                    if (d.mailto) {
                        window.location.href = d.mailto;
                    }
                }).catch(function () {
                    smsMsg.textContent = 'Network error';
                });
            }

            document.getElementById('sms-send-btn').addEventListener('click', function () { sendTextInvite(false); });
            document.getElementById('sms-device-btn').addEventListener('click', function () { sendTextInvite(true); });
            document.getElementById('email-send-btn').addEventListener('click', function () { sendEmailInvite(false); });
            document.getElementById('email-device-btn').addEventListener('click', function () { sendEmailInvite(true); });
        })();
        </script>
        <?php endif; ?>
        <div class="intro">
            <?php echo $is_sweet
                ? 'Share your <strong>house code</strong> so people can create an account and join. When they join, <strong>you get an email</strong> and so do they. Training tracks below are editable checklists 💕'
                : 'Share your house code so people can register and join. You and the new teammate both get an email note. Training tracks are editable checklists.'; ?>
        </div>
        <div class="pin-note">
            <?php
            $seatNote = '';
            if ($seatStatus) {
                if ($isUnlimited || $isDemoHouse) {
                    $seatNote = $is_sweet
                        ? ' <span class="badge unlimited">Unlimited playground</span> Seats used: <strong>' . (int) $seatStatus['count'] . '</strong> (no cap — perfect for demos & sales trials).'
                        : ' Unlimited seats · ' . (int) $seatStatus['count'] . ' logins on this house.';
                } elseif (empty($seatStatus['allows_invites'])) {
                    $seatNote = $is_sweet
                        ? ' Your current plan is solo — <a href="/billing/plans">upgrade to a house plan</a> to invite teammates.'
                        : ' Your plan does not include house invites. <a href="/billing/plans">Upgrade</a> to add teammates.';
                    $houseCode = '';
                } elseif ($seatStatus['max'] !== null) {
                    $seatNote = $is_sweet
                        ? ' Seats: <strong>' . (int) $seatStatus['count'] . ' / ' . (int) $seatStatus['max'] . '</strong>'
                            . (empty($seatStatus['ok']) ? ' — house is full; <a href="/billing/plans">upgrade the plan</a> to add more.' : ' available for logins on this plan.')
                        : ' Seats used: ' . (int) $seatStatus['count'] . ' of ' . (int) $seatStatus['max'] . '.'
                            . (empty($seatStatus['ok']) ? ' Full — <a href="/billing/plans">upgrade</a> to add more.' : '');
                }
            }
            if ($houseCode !== ''):
            ?>
                <?php echo $is_sweet
                    ? '🔗 <strong>' . htmlspecialchars($houseName !== '' ? $houseName : 'House') . ' code:</strong> <code style="font-size:1.1rem;letter-spacing:0.08em;">' . htmlspecialchars($houseCode) . '</code> — they use <a href="/join">Join with code</a> or Register → I have a code.' . $seatNote
                    : '<strong>House invite code:</strong> <code>' . htmlspecialchars($houseCode) . '</code>.' . $seatNote; ?>
            <?php else: ?>
                <?php echo $is_sweet
                    ? '🔗' . ($seatNote !== '' ? $seatNote : ' Start a house plan to get an invite code.')
                    : ($seatNote !== '' ? $seatNote : 'Start a house plan to get an invite code.'); ?>
            <?php endif; ?>
        </div>
        <div class="tabs">
            <button type="button" class="tab active" data-tab="invites"><?php echo $is_sweet ? '🔗 Invites' : 'Invites'; ?></button>
            <button type="button" class="tab" data-tab="training"><?php echo $is_sweet ? '📚 Training' : 'Training'; ?></button>
        </div>

        <div class="panel active" id="panel-invites">
            <?php if ($houseId > 0): ?>
            <div class="card">
                <h2><?php echo $is_sweet ? 'House logins' : 'House logins'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'See every account on this house. Edit name, email, username, role &amp; access. Send a password reset if someone is locked out. Remove after a demo if you need to.'
                    : 'View and edit team logins: name, email, username, role, access, and password reset.'; ?></p>
                <?php if (count($manageableHouses) > 1): ?>
                <form method="GET" action="/admin/onboarding" style="margin-bottom:12px;">
                    <label class="meta"><?php echo $is_sweet ? 'Which house?' : 'House'; ?></label>
                    <select name="house_id" onchange="this.form.submit()" style="width:100%;margin-top:4px;padding:10px;border-radius:12px;border:2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;font:inherit;">
                        <?php foreach ($manageableHouses as $hid => $hh): ?>
                        <option value="<?php echo (int) $hid; ?>"<?php echo $hid === $houseId ? ' selected' : ''; ?>>
                            <?php echo htmlspecialchars(($hh['name'] ?? 'House') . ' · ' . ($hh['invite_code'] ?? '')); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php endif; ?>
                <?php if (!$canManageMembers): ?>
                    <p class="meta"><?php echo $is_sweet ? 'You can view this list, but only owners/managers can edit logins.' : 'View only — managers can edit.'; ?></p>
                <?php endif; ?>
                <?php if (!$houseMembers): ?>
                    <p class="meta"><?php echo $is_sweet ? 'No one else is on this house yet — share the code above 💕' : 'No members yet.'; ?></p>
                <?php else: ?>
                    <?php foreach ($houseMembers as $m):
                        $mid = (int) ($m['user_id'] ?? 0);
                        $isBillableOwner = ($realOwnerId > 0 && $mid === $realOwnerId);
                        $label = trim((string) ($m['full_name'] ?: $m['username'] ?: 'User'));
                        $curRole = (string) ($m['role'] ?? 'foh');
                        $curAccess = (string) ($m['access_status'] ?? 'pending');
                    ?>
                    <div class="member-row" style="align-items:flex-start;flex-direction:column;">
                        <div class="member-meta" style="width:100%;">
                            <strong><?php echo htmlspecialchars($label); ?><?php if ($isBillableOwner): ?> <span class="badge"><?php echo $is_sweet ? 'House owner' : 'Owner'; ?></span><?php endif; ?></strong>
                            <span class="meta">
                                @<?php echo htmlspecialchars((string) ($m['username'] ?? '')); ?>
                                · <?php echo htmlspecialchars((string) ($m['email'] ?? '')); ?>
                            </span>
                        </div>
                        <?php if ($canManageMembers && !empty($isPlatAdminPage)):
                            $allowPw = function_exists('pbj_user_allows_direct_password_set') && pbj_user_allows_direct_password_set($pdo, $mid);
                            $onPay = function_exists('pbj_user_is_on_paying_house') && pbj_user_is_on_paying_house($pdo, $mid);
                        ?>
                        <form method="POST" style="width:100%;margin-top:10px;">
                            <input type="hidden" name="update_member" value="1">
                            <input type="hidden" name="full_profile" value="1">
                            <input type="hidden" name="house_id" value="<?php echo (int) $houseId; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $mid; ?>">
                            <div class="field-row">
                                <div class="field" style="flex:1;min-width:140px;">
                                    <label><?php echo $is_sweet ? 'Full name' : 'Full name'; ?></label>
                                    <input type="text" name="full_name" required value="<?php echo htmlspecialchars((string) ($m['full_name'] ?? '')); ?>">
                                </div>
                                <div class="field" style="flex:1;min-width:120px;">
                                    <label><?php echo $is_sweet ? 'Username' : 'Username'; ?></label>
                                    <input type="text" name="username" required autocomplete="off" value="<?php echo htmlspecialchars((string) ($m['username'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="field">
                                <label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label>
                                <input type="email" name="email" required autocomplete="off" value="<?php echo htmlspecialchars((string) ($m['email'] ?? '')); ?>">
                            </div>
                            <div class="field-row">
                                <div class="field" style="flex:1;min-width:110px;">
                                    <label><?php echo $is_sweet ? 'Role' : 'Role'; ?></label>
                                    <select name="role" <?php echo $isBillableOwner ? 'disabled' : ''; ?>>
                                        <?php foreach (['foh' => 'FOH', 'boh' => 'BOH', 'manager' => 'Manager', 'owner' => 'Owner', 'admin' => 'Admin (GM)'] as $rv => $rl): ?>
                                        <option value="<?php echo $rv; ?>"<?php echo ($curRole === $rv || ($curRole === 'gm' && $rv === 'admin')) ? ' selected' : ''; ?>><?php echo $rl; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($isBillableOwner): ?><input type="hidden" name="role" value="owner"><?php endif; ?>
                                </div>
                                <div class="field" style="flex:1;min-width:110px;">
                                    <label><?php echo $is_sweet ? 'Access' : 'Access'; ?></label>
                                    <select name="access_status" <?php echo $isBillableOwner ? 'disabled' : ''; ?>>
                                        <?php foreach (['approved' => 'Approved', 'pending' => 'Pending', 'blocked' => 'Blocked'] as $av => $al): ?>
                                        <option value="<?php echo $av; ?>"<?php echo $curAccess === $av ? ' selected' : ''; ?>><?php echo $al; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($isBillableOwner): ?><input type="hidden" name="access_status" value="approved"><?php endif; ?>
                                </div>
                            </div>
                            <?php if ($allowPw): ?>
                            <div class="field-row">
                                <div class="field" style="flex:1;min-width:120px;">
                                    <label><?php echo $is_sweet ? 'New password (optional)' : 'New password'; ?></label>
                                    <input type="password" name="new_password" autocomplete="new-password" minlength="6" placeholder="Leave blank to keep">
                                </div>
                                <div class="field" style="flex:1;min-width:120px;">
                                    <label><?php echo $is_sweet ? 'Confirm password' : 'Confirm' ?></label>
                                    <input type="password" name="new_password_confirm" autocomplete="new-password" minlength="6">
                                </div>
                            </div>
                            <p class="meta"><?php echo $is_sweet ? 'Playground / free — you can set a password here.' : 'Direct password allowed.'; ?></p>
                            <?php else: ?>
                            <p class="meta"><?php echo $is_sweet
                                ? 'Paying account — use <strong>Send password reset</strong> (no direct password).'
                                : 'Paying account: reset only.'; ?></p>
                            <?php endif; ?>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                                <button type="submit" class="btn btn-small btn-primary"><?php echo $is_sweet ? 'Save all info' : 'Save'; ?></button>
                            </div>
                        </form>
                        <form method="POST" style="margin:8px 0 0;display:inline-block;" onsubmit="return confirm(<?php echo $is_sweet ? "'Email them a password reset link?'" : "'Send password reset email?'"; ?>);">
                            <input type="hidden" name="update_member" value="1">
                            <input type="hidden" name="send_reset_only" value="1">
                            <input type="hidden" name="house_id" value="<?php echo (int) $houseId; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $mid; ?>">
                            <button type="submit" class="btn btn-small btn-ghost"><?php echo $is_sweet ? 'Send password reset ✉️' : 'Send password reset'; ?></button>
                        </form>
                        <?php if (!$isBillableOwner): ?>
                        <form method="POST" style="margin:8px 0 0;" onsubmit="return confirm(<?php echo $is_sweet ? "'Remove them from this house? They can rejoin with the code later.'" : "'Remove this member?'"; ?>);">
                            <input type="hidden" name="remove_member" value="1">
                            <input type="hidden" name="house_id" value="<?php echo (int) $houseId; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $mid; ?>">
                            <button type="submit" class="btn btn-small btn-danger"><?php echo $is_sweet ? 'Remove from house' : 'Remove'; ?></button>
                        </form>
                        <?php endif; ?>
                        <?php elseif ($canManageMembers): ?>
                        <form method="POST" style="width:100%;margin-top:10px;">
                            <input type="hidden" name="update_member" value="1">
                            <input type="hidden" name="employee_profile" value="1">
                            <input type="hidden" name="house_id" value="<?php echo (int) $houseId; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $mid; ?>">
                            <div class="field-row">
                                <div class="field" style="flex:1;min-width:140px;">
                                    <label><?php echo $is_sweet ? 'Full name' : 'Full name'; ?></label>
                                    <input type="text" name="full_name" required value="<?php echo htmlspecialchars((string) ($m['full_name'] ?? '')); ?>">
                                </div>
                                <div class="field" style="flex:1;min-width:120px;">
                                    <label><?php echo $is_sweet ? 'Username' : 'Username'; ?></label>
                                    <input type="text" name="username" required autocomplete="off" value="<?php echo htmlspecialchars((string) ($m['username'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="field">
                                <label><?php echo $is_sweet ? 'Email' : 'Email'; ?></label>
                                <input type="email" name="email" required autocomplete="off" value="<?php echo htmlspecialchars((string) ($m['email'] ?? '')); ?>">
                            </div>
                            <div class="field-row">
                                <div class="field" style="flex:1;min-width:110px;">
                                    <label><?php echo $is_sweet ? 'Role' : 'Role'; ?></label>
                                    <select name="role" <?php echo $isBillableOwner ? 'disabled' : ''; ?>>
                                        <?php foreach (['foh' => 'FOH', 'boh' => 'BOH', 'manager' => 'Manager', 'owner' => 'Owner', 'admin' => 'Admin (GM)'] as $rv => $rl): ?>
                                        <option value="<?php echo $rv; ?>"<?php echo ($curRole === $rv || ($curRole === 'gm' && $rv === 'admin')) ? ' selected' : ''; ?>><?php echo $rl; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($isBillableOwner): ?><input type="hidden" name="role" value="owner"><?php endif; ?>
                                </div>
                                <div class="field" style="flex:1;min-width:110px;">
                                    <label><?php echo $is_sweet ? 'Access' : 'Access'; ?></label>
                                    <select name="access_status" <?php echo $isBillableOwner ? 'disabled' : ''; ?>>
                                        <?php foreach (['approved' => 'Approved', 'pending' => 'Pending', 'blocked' => 'Blocked'] as $av => $al): ?>
                                        <option value="<?php echo $av; ?>"<?php echo $curAccess === $av ? ' selected' : ''; ?>><?php echo $al; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($isBillableOwner): ?><input type="hidden" name="access_status" value="approved"><?php endif; ?>
                                </div>
                            </div>
                            <p class="meta" style="margin-top:6px;"><?php echo $is_sweet
                                ? 'Passwords: use <strong>Send password reset</strong> so they set a new one (you never see it).'
                                : 'Use password reset email for staff — passwords are not shown here.'; ?></p>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
                                <button type="submit" class="btn btn-small btn-primary"><?php echo $is_sweet ? 'Save teammate' : 'Save'; ?></button>
                            </div>
                        </form>
                        <form method="POST" style="margin:8px 0 0;display:inline-block;" onsubmit="return confirm(<?php echo $is_sweet ? "'Email them a password reset link?'" : "'Send password reset email?'"; ?>);">
                            <input type="hidden" name="update_member" value="1">
                            <input type="hidden" name="send_reset_only" value="1">
                            <input type="hidden" name="house_id" value="<?php echo (int) $houseId; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $mid; ?>">
                            <button type="submit" class="btn btn-small btn-ghost"><?php echo $is_sweet ? 'Send password reset ✉️' : 'Send password reset'; ?></button>
                        </form>
                        <?php if (!$isBillableOwner): ?>
                        <form method="POST" style="margin:8px 0 0;" onsubmit="return confirm(<?php echo $is_sweet ? "'Remove them from this house? They can rejoin with the code later.'" : "'Remove this member?'"; ?>);">
                            <input type="hidden" name="remove_member" value="1">
                            <input type="hidden" name="house_id" value="<?php echo (int) $houseId; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $mid; ?>">
                            <button type="submit" class="btn btn-small btn-danger"><?php echo $is_sweet ? 'Remove from house' : 'Remove'; ?></button>
                        </form>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="meta"><?php echo htmlspecialchars(strtoupper($curRole) . ' · ' . $curAccess); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($houseCode !== '' && ($canManageMembers || $isPlatAdminPage)): ?>
            <div class="card">
                <h2><?php echo $is_sweet ? 'Email a house invite ✉️' : 'Email a house invite'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'No phone number needed — send the house code by email. Perfect when someone would rather not share their mobile 💕'
                    : 'Send the house code by email when they prefer not to share a phone number.'; ?></p>
                <div class="field-row">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Their name (optional)' : 'Name (optional)'; ?></label>
                        <input id="house-email-name" type="text" placeholder="<?php echo $is_sweet ? 'Sam' : 'Name'; ?>">
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Email address' : 'Email address'; ?></label>
                        <input id="house-email-to" type="email" inputmode="email" placeholder="sam@example.com" autocomplete="email">
                    </div>
                </div>
                <p class="meta"><?php echo $is_sweet ? 'House code in the message:' : 'House code:'; ?> <strong><?php echo htmlspecialchars($houseCode); ?></strong></p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">
                    <button type="button" class="btn btn-small btn-primary" id="house-email-send"><?php echo $is_sweet ? 'Send email invite ✨' : 'Send email invite'; ?></button>
                    <button type="button" class="btn btn-small btn-ghost" id="house-email-mailto"><?php echo $is_sweet ? 'Open in my mail app' : 'Open in mail app'; ?></button>
                </div>
                <p class="meta" id="house-email-msg" style="margin-top:10px;"></p>
                <div class="note-box" id="house-email-preview" style="display:none;margin-top:10px;"></div>
            </div>
            <script>
            (function () {
                var houseCode = <?php echo json_encode($houseCode); ?>;
                var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
                var msg = document.getElementById('house-email-msg');
                var preview = document.getElementById('house-email-preview');
                function sendHouseEmail(deviceOnly) {
                    var email = (document.getElementById('house-email-to').value || '').trim();
                    var name = (document.getElementById('house-email-name').value || '').trim();
                    if (!email) {
                        msg.textContent = isSweet ? 'Need an email address 💕' : 'Enter an email address.';
                        return;
                    }
                    msg.textContent = 'Sending…';
                    preview.style.display = 'none';
                    fetch('/invite-email-api.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            email: email,
                            house_code: houseCode,
                            name: name,
                            device_mail: deviceOnly ? 1 : 0
                        })
                    }).then(function (r) { return r.json(); }).then(function (d) {
                        if (!d.ok) {
                            msg.textContent = d.error === 'bad_email'
                                ? (isSweet ? 'That email looks off — double-check it 💕' : 'Invalid email address')
                                : d.error === 'forbidden'
                                    ? (isSweet ? 'Only owners / managers can email invites 🔒' : 'No permission to email invites.')
                                    : ('Failed: ' + (d.error || 'unknown'));
                            return;
                        }
                        if (d.message) {
                            preview.style.display = 'block';
                            preview.textContent = d.message;
                        }
                        if (d.mode === 'mail') {
                            msg.textContent = isSweet ? 'Email invite sent 💕' : 'Email invite sent.';
                            return;
                        }
                        msg.textContent = d.hint || (isSweet ? 'Opening your mail app — hit Send 💕' : 'Opening your mail app — tap Send.');
                        if (d.mailto) window.location.href = d.mailto;
                    }).catch(function () {
                        msg.textContent = 'Network error';
                    });
                }
                var sendBtn = document.getElementById('house-email-send');
                var mailBtn = document.getElementById('house-email-mailto');
                if (sendBtn) sendBtn.addEventListener('click', function () { sendHouseEmail(false); });
                if (mailBtn) mailBtn.addEventListener('click', function () { sendHouseEmail(true); });
            })();
            </script>
            <?php endif; ?>

            <div class="card">
                <h2><?php echo $is_sweet ? 'Personal invite note + tracking code' : 'Personal invite note + tracking code'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'Optional: draft a note for the new person <em>and</em> a reminder for you. Also generates a short tracking code for your list. For real app access they still use the <strong>house code</strong> above (Join with code).'
                    : 'Optional notes plus a tracking code. Real logins use the house code above.'; ?></p>
                <div class="team-empty" id="team-empty" style="display:none;">
                    <?php echo $is_sweet
                        ? 'No active teammates yet — add them in <a href="/admin/roster">Team & Roles</a>, or invite “Someone new” below 💕'
                        : 'No active teammates yet. Add staff in <a href="/admin/roster">Team & Roles</a>, or invite “Someone new.”'; ?>
                </div>
                <form id="invite-form">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Teammate (from roster)' : 'Teammate (from roster)'; ?></label>
                        <select id="i-person" required>
                            <option value=""><?php echo $is_sweet ? 'Select teammate…' : 'Select teammate…'; ?></option>
                            <option value="__new__"><?php echo $is_sweet ? '＋ Someone new (not on roster)' : '+ Someone new (not on roster)'; ?></option>
                        </select>
                    </div>
                    <div class="field-row" id="new-person-fields" style="display:none;">
                        <div class="field"><label><?php echo $is_sweet ? 'Name' : 'Name'; ?></label><input id="i-name" placeholder="<?php echo $is_sweet ? 'First & last' : 'Name'; ?>"></div>
                        <div class="field"><label><?php echo $is_sweet ? 'Role track' : 'Role track'; ?></label>
                            <select id="i-role">
                                <option value="Server">Server</option>
                                <option value="Host">Host</option>
                                <option value="Bartender">Bartender</option>
                                <option value="BOH Cook">BOH Cook</option>
                                <option value="Prep">Prep</option>
                                <option value="Dish">Dish</option>
                                <option value="Manager">Manager</option>
                                <option value="Shift Lead">Shift Lead</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="field" id="roster-role-field" style="display:none;">
                        <label><?php echo $is_sweet ? 'Role for this invite' : 'Role for this invite'; ?></label>
                        <select id="i-role-roster"></select>
                        <p class="hint" style="margin:4px 0 0;"><?php echo $is_sweet ? 'Pulled from their Team profile — pick which hat if they have a few' : 'From their team profile if they have multiple roles.'; ?></p>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Note for them (employee)' : 'Note for the invitee'; ?></label>
                        <textarea id="i-note-employee" rows="3" placeholder="<?php echo $is_sweet ? 'Hey! Here’s our house code — jump in and poke around…' : 'Welcome note for the new person'; ?>"></textarea>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'Note for you (owner reminder)' : 'Note for you (owner)' ?></label>
                        <textarea id="i-note-owner" rows="2" placeholder="<?php echo $is_sweet ? 'Invited Sam for a 1-day demo — remove Friday if they don’t convert' : 'Your private reminder about this invite'; ?>"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;"><?php echo $is_sweet ? 'Generate notes + tracking code ✨' : 'Generate notes + tracking code'; ?></button>
                </form>
                <div id="new-code" style="display:none;">
                    <p class="hint" style="margin-top:14px;"><?php echo $is_sweet ? 'Tracking code (optional label for your list):' : 'Tracking code:'; ?></p>
                    <div class="code-box" id="code-display"></div>
                    <?php if ($houseCode !== ''): ?>
                    <p class="hint"><?php echo $is_sweet ? 'Real house code to share for login access:' : 'House code for login:'; ?></p>
                    <div class="code-box" style="font-size:1.2rem;"><?php echo htmlspecialchars($houseCode); ?></div>
                    <?php endif; ?>
                    <p class="hint" style="margin-top:12px;"><strong><?php echo $is_sweet ? 'Message for them' : 'Message for them'; ?></strong></p>
                    <div class="note-box" id="note-employee-out"></div>
                    <button type="button" class="btn btn-small btn-ghost" id="copy-employee-note" style="width:100%;margin-bottom:10px;"><?php echo $is_sweet ? 'Copy their note' : 'Copy their note'; ?></button>
                    <p class="hint"><strong><?php echo $is_sweet ? 'Reminder for you' : 'Reminder for you'; ?></strong></p>
                    <div class="note-box" id="note-owner-out"></div>
                    <button type="button" class="btn btn-small btn-ghost" id="copy-owner-note" style="width:100%;"><?php echo $is_sweet ? 'Copy your note' : 'Copy your note'; ?></button>
                </div>
            </div>
            <div class="card">
                <h2><?php echo $is_sweet ? 'Invite tracking list' : 'Invite tracking list'; ?></h2>
                <div id="invite-list"></div>
            </div>
        </div>

        <div class="panel" id="panel-training">
            <p class="hint"><?php echo $is_sweet
                ? 'Edit every step — add, rename, reorder, delete. Check off progress as trainees learn. Your house, your list 💕'
                : 'Edit every step: add, rename, reorder, delete. Check off progress as trainees learn.'; ?></p>
            <div id="tracks"></div>
            <div class="card">
                <h2><?php echo $is_sweet ? 'Add training track' : 'Add training track'; ?></h2>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Track title' : 'Track title'; ?></label><input id="new-track-title" placeholder="<?php echo $is_sweet ? 'e.g. Bar, Catering…' : 'e.g. Bar, Catering'; ?>"></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Short hint' : 'Short hint'; ?></label><input id="new-track-hint" placeholder="<?php echo $is_sweet ? 'What this path is for' : 'What this path is for'; ?>"></div>
                </div>
                <button type="button" class="btn btn-secondary" id="add-track-btn" data-perm="admin.team.onboarding.add_track" style="width:100%;"><?php echo $is_sweet ? '+ Add track' : '+ Add track'; ?></button>
            </div>
            <button type="button" class="btn btn-secondary" id="reset-training" style="width:100%;margin-bottom:14px;"><?php echo $is_sweet ? 'Uncheck all training' : 'Uncheck all training'; ?></button>
        </div>

        <div class="actions-bar">
            <a href="/admin/roster" class="btn btn-secondary"><?php echo $is_sweet ? 'Team roster' : 'Team roster'; ?></a>
            <a href="/admin/team" class="btn btn-primary"><?php echo $is_sweet ? 'Team & Roles' : 'Team hub'; ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        const KEY = 'pbj_admin_onboarding_v2';
        const OLD_KEY = 'pbj_admin_onboarding_v1';
        const TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyOnbPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        const defaultTrackMeta = <?php echo json_encode($tracks, JSON_UNESCAPED_UNICODE); ?>;
        const defaultSteps = {
            server: [
                'Tour of restaurant & exits',
                'POS login & basic ring-ins',
                'Menu tasting / allergens overview',
                'Shadow a full shift',
                'Guest greeting standards review',
                'Sidework & section map',
                'Closing duties walkthrough',
                'First solo section with lead support'
            ],
            boh: [
                'Kitchen tour & safety',
                'Handwashing / sanitizer stations',
                'Station setup & pars',
                'Knife / equipment basics',
                'Recipe card walkthrough',
                'Ticket flow & expo',
                'Closing breakdown',
                'Food safety temp log practice'
            ],
            manager: [
                'Keys / alarm / opening keys',
                'Cash handling & drops',
                'Schedule tools overview',
                'Comps / voids authority',
                'Incident & guest recovery',
                'Inventory walk & order day',
                'Labor & sales snapshot review',
                'Closing manager checklist'
            ]
        };

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function codeGen() {
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            var out = '';
            for (var i = 0; i < 6; i++) out += chars[Math.floor(Math.random() * chars.length)];
            return out;
        }

        function personRoles(p) {
            var roles = [];
            if (p && Array.isArray(p.roles)) {
                p.roles.forEach(function (r) {
                    if (r && typeof r === 'object') r = r.role || r.name || '';
                    r = String(r || '').trim();
                    if (r && roles.indexOf(r) === -1) roles.push(r);
                });
            }
            if (p && p.role) {
                var single = String(p.role).trim();
                if (single && roles.indexOf(single) === -1) roles.unshift(single);
            }
            if (!roles.length) roles = ['Other'];
            return roles;
        }

        function loadTeam() {
            for (var i = 0; i < TEAM_KEYS.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(TEAM_KEYS[i]) || 'null');
                    if (r && Array.isArray(r.people) && r.people.length) {
                        return r.people
                            .filter(function (p) { return p && p.status !== 'inactive' && (p.name || '').trim(); })
                            .map(function (p) {
                                return {
                                    id: String(p.id || ''),
                                    name: String(p.name || '').trim(),
                                    roles: personRoles(p)
                                };
                            })
                            .sort(function (a, b) { return a.name.localeCompare(b.name); });
                    }
                } catch (e) {}
            }
            return [];
        }

        function defaultTrainingPack() {
            var training = {};
            var trackOrder = [];
            defaultTrackMeta.forEach(function (t) {
                trackOrder.push(t.id);
                training[t.id] = {
                    id: t.id,
                    title: t.title,
                    icon: t.icon,
                    hint: t.hint,
                    open: t.id === 'server',
                    steps: (defaultSteps[t.id] || []).map(function (label, i) {
                        return { id: t.id + '-' + i, label: label, done: false };
                    })
                };
            });
            return { training: training, trackOrder: trackOrder };
        }

        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r) {
                    var old = JSON.parse(localStorage.getItem(OLD_KEY) || 'null');
                    if (old) {
                        r = migrateV1(old);
                        localStorage.setItem(KEY, JSON.stringify(r));
                    }
                }
                if (!r) {
                    var pack = defaultTrainingPack();
                    r = { invites: [], training: pack.training, trackOrder: pack.trackOrder };
                }
                if (!Array.isArray(r.invites)) r.invites = [];
                if (!r.training || typeof r.training !== 'object') {
                    var d = defaultTrainingPack();
                    r.training = d.training;
                    r.trackOrder = d.trackOrder;
                }
                if (!Array.isArray(r.trackOrder) || !r.trackOrder.length) {
                    r.trackOrder = Object.keys(r.training);
                }
                // Ensure each track has shape
                r.trackOrder.forEach(function (id) {
                    if (!r.training[id]) return;
                    var t = r.training[id];
                    if (!Array.isArray(t.steps)) t.steps = [];
                    t.steps = t.steps.map(function (s) {
                        return {
                            id: s.id || uid(),
                            label: s.label || s.text || '',
                            done: !!s.done
                        };
                    });
                    if (!t.title) t.title = id;
                    if (!t.icon) t.icon = '📌';
                    if (t.hint == null) t.hint = '';
                    t.id = id;
                });
                return r;
            } catch (e) {
                var pack = defaultTrainingPack();
                return { invites: [], training: pack.training, trackOrder: pack.trackOrder };
            }
        }

        function migrateV1(old) {
            var pack = defaultTrainingPack();
            var training = pack.training;
            if (old.training) {
                Object.keys(old.training).forEach(function (id) {
                    var src = old.training[id];
                    if (!training[id]) {
                        training[id] = {
                            id: id,
                            title: id,
                            icon: '📌',
                            hint: '',
                            open: !!src.open,
                            steps: []
                        };
                        pack.trackOrder.push(id);
                    }
                    training[id].open = !!src.open;
                    if (Array.isArray(src.steps)) {
                        training[id].steps = src.steps.map(function (s) {
                            return { id: s.id || uid(), label: s.label || '', done: !!s.done };
                        });
                    }
                });
            }
            return {
                invites: Array.isArray(old.invites) ? old.invites : [],
                training: training,
                trackOrder: pack.trackOrder
            };
        }

        function save(t) {
            localStorage.setItem(KEY, JSON.stringify(state));
            // light v1 mirror for any old readers
            try {
                localStorage.setItem(OLD_KEY, JSON.stringify({
                    invites: state.invites,
                    training: state.training
                }));
            } catch (e) {}
            if (t) {
                var el = document.getElementById('toast');
                el.classList.add('show');
                setTimeout(function () { el.classList.remove('show'); }, 1100);
            }
        }

        var state = load();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyOnbPerms);
            document.addEventListener('pbj-perms-ready', applyOnbPerms);
        var team = loadTeam();
        var editingStep = null; // { trackId, stepId }

        function fillPersonSelect() {
            var sel = document.getElementById('i-person');
            var empty = document.getElementById('team-empty');
            var current = sel.value;
            sel.innerHTML = '<option value="">' + (isSweet ? 'Select teammate…' : 'Select teammate…') + '</option>' +
                '<option value="__new__">' + (isSweet ? '＋ Someone new (not on roster)' : '+ Someone new (not on roster)') + '</option>';
            team.forEach(function (p) {
                var label = p.name + (p.roles.length ? ' · ' + p.roles.join(', ') : '');
                sel.innerHTML += '<option value="' + esc(p.id || p.name) + '">' + esc(label) + '</option>';
            });
            if (current) sel.value = current;
            empty.style.display = team.length ? 'none' : 'block';
            onPersonChange();
        }

        function findPerson(val) {
            if (!val || val === '__new__') return null;
            return team.find(function (p) { return p.id === val || p.name === val; }) || null;
        }

        function onPersonChange() {
            var val = document.getElementById('i-person').value;
            var newFields = document.getElementById('new-person-fields');
            var rosterRole = document.getElementById('roster-role-field');
            var roleSel = document.getElementById('i-role-roster');
            if (val === '__new__') {
                newFields.style.display = 'flex';
                rosterRole.style.display = 'none';
                document.getElementById('i-name').required = true;
            } else if (val) {
                newFields.style.display = 'none';
                document.getElementById('i-name').required = false;
                var p = findPerson(val);
                if (p) {
                    rosterRole.style.display = 'block';
                    roleSel.innerHTML = p.roles.map(function (r) {
                        return '<option value="' + esc(r) + '">' + esc(r) + '</option>';
                    }).join('');
                } else {
                    rosterRole.style.display = 'none';
                }
            } else {
                newFields.style.display = 'none';
                rosterRole.style.display = 'none';
                document.getElementById('i-name').required = false;
            }
        }

        document.getElementById('i-person').addEventListener('change', onPersonChange);

        // Tabs
        document.querySelectorAll('.tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var key = tab.dataset.tab;
                document.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t === tab); });
                document.getElementById('panel-invites').classList.toggle('active', key === 'invites');
                document.getElementById('panel-training').classList.toggle('active', key === 'training');
            });
        });

        var HOUSE_CODE = <?php echo json_encode($houseCode, JSON_UNESCAPED_UNICODE); ?>;
        var HOUSE_NAME = <?php echo json_encode($houseName !== '' ? $houseName : 'our house', JSON_UNESCAPED_UNICODE); ?>;
        var JOIN_URL = <?php echo json_encode((defined('APP_PUBLIC_URL') ? rtrim(APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop') . '/join', JSON_UNESCAPED_UNICODE); ?>;

        function renderInvites() {
            var root = document.getElementById('invite-list');
            if (!state.invites.length) {
                root.innerHTML = '<p class="hint">' + (isSweet ? 'No tracking invites yet — generate one above 💕' : 'No tracking invites yet.') + '</p>';
                return;
            }
            root.innerHTML = state.invites.slice().reverse().map(function (inv) {
                var when = new Date(inv.at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                var notes = '';
                if (inv.noteEmployee) {
                    notes += '<div class="meta" style="margin-top:8px;"><strong>' + (isSweet ? 'For them:' : 'For them:') + '</strong> ' + esc(inv.noteEmployee) + '</div>';
                }
                if (inv.noteOwner) {
                    notes += '<div class="meta" style="margin-top:4px;"><strong>' + (isSweet ? 'For you:' : 'For you:') + '</strong> ' + esc(inv.noteOwner) + '</div>';
                }
                return '<div class="invite' + (inv.used ? ' used' : '') + '">' +
                    '<div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">' +
                    '<strong style="letter-spacing:0.08em;font-size:1.2rem;">' + esc(inv.code) + '</strong>' +
                    '<span class="meta">' + (inv.used ? (isSweet ? 'Used' : 'Used') : (isSweet ? 'Open' : 'Open')) + '</span></div>' +
                    '<div class="meta" style="margin-top:6px;">' + esc(when) +
                    (inv.name ? ' · ' + esc(inv.name) : '') +
                    (inv.role ? ' · ' + esc(inv.role) : '') +
                    (inv.personId ? ' <span class="badge">' + (isSweet ? 'Roster' : 'Roster') + '</span>' : '') +
                    '</div>' + notes +
                    '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">' +
                    (!inv.used ? '<button type="button" class="btn btn-small btn-ghost" data-act="use" data-id="' + esc(inv.id) + '">' + (isSweet ? 'Mark used' : 'Mark used') + '</button>' : '') +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-need-perm="admin.team.onboarding.create_invite" data-id="' + esc(inv.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function renderTracks() {
            var root = document.getElementById('tracks');
            if (!state.trackOrder.length) {
                root.innerHTML = '<div class="empty-slot">' + (isSweet ? 'No tracks yet — add one below' : 'No tracks yet.') + '</div>';
                return;
            }
            root.innerHTML = state.trackOrder.map(function (tid) {
                var t = state.training[tid];
                if (!t) return '';
                var steps = t.steps || [];
                var done = steps.filter(function (s) { return s.done; }).length;
                var body;
                if (!steps.length) {
                    body = '<div class="empty-slot">' + (isSweet ? 'No steps yet — add one below' : 'No steps yet') + '</div>';
                } else {
                    body = steps.map(function (s, idx) {
                        var isEditing = editingStep && editingStep.trackId === tid && editingStep.stepId === s.id;
                        var main;
                        if (isEditing) {
                            main = '<input class="step-edit-input" data-edit-input="' + esc(tid) + '" data-step="' + esc(s.id) + '" value="' + esc(s.label) + '">' +
                                '<button type="button" class="btn btn-tiny btn-primary" data-save-step="' + esc(tid) + '" data-step="' + esc(s.id) + '">' + (isSweet ? 'Save' : 'Save') + '</button>' +
                                '<button type="button" class="btn btn-tiny btn-ghost" data-cancel-edit="' + esc(tid) + '">' + (isSweet ? 'Cancel' : 'Cancel') + '</button>';
                        } else {
                            main = '<label class="step-check" data-toggle-done="' + esc(tid) + '" data-step="' + esc(s.id) + '">' +
                                '<input type="checkbox"' + (s.done ? ' checked' : '') + '>' +
                                '<span class="checkbox"></span>' +
                                '<span class="step-label">' + esc(s.label) + '</span></label>' +
                                '<div class="step-actions">' +
                                '<button type="button" class="btn btn-tiny btn-ghost" data-move="' + esc(tid) + '" data-step="' + esc(s.id) + '" data-dir="-1"' + (idx === 0 ? ' disabled style="opacity:0.4;"' : '') + '>↑</button>' +
                                '<button type="button" class="btn btn-tiny btn-ghost" data-move="' + esc(tid) + '" data-step="' + esc(s.id) + '" data-dir="1"' + (idx === steps.length - 1 ? ' disabled style="opacity:0.4;"' : '') + '>↓</button>' +
                                '<button type="button" class="btn btn-tiny btn-ghost" data-edit-step="' + esc(tid) + '" data-step="' + esc(s.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                                '<button type="button" class="btn btn-tiny btn-danger" data-del-step="' + esc(tid) + '" data-step="' + esc(s.id) + '">' + (isSweet ? 'Del' : 'Del') + '</button>' +
                                '</div>';
                        }
                        return '<div class="step-row' + (s.done && !isEditing ? ' done' : '') + '">' + main + '</div>';
                    }).join('');
                }
                return '<div class="track' + (t.open ? ' open' : '') + '">' +
                    '<button type="button" class="track-head" data-toggle="' + esc(tid) + '">' +
                    '<span class="track-icon">' + esc(t.icon || '📌') + '</span>' +
                    '<span style="flex:1;"><h3 class="track-title">' + esc(t.title) + '</h3>' +
                    '<div class="meta">' + esc(t.hint || '') + (t.hint ? ' · ' : '') + done + '/' + steps.length + '</div></span></button>' +
                    '<div class="track-body">' + body +
                    '<div class="field-row" style="margin-top:10px;">' +
                    '<div class="field" style="margin:0;flex:2;"><input data-new-step="' + esc(tid) + '" placeholder="' + (isSweet ? 'Add a training step…' : 'Add a training step…') + '"></div>' +
                    '<button type="button" class="btn btn-small btn-primary" data-add-step="' + esc(tid) + '">+</button>' +
                    '</div>' +
                    '<div class="track-toolbar">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-edit-track="' + esc(tid) + '">' + (isSweet ? 'Edit track' : 'Edit track') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-need-perm="admin.team.onboarding.edit_tracks" data-del-track="' + esc(tid) + '">' + (isSweet ? 'Remove track' : 'Remove track') + '</button>' +
                    '</div></div></div>';
            }).join('');
        }

        document.getElementById('invite-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var personVal = document.getElementById('i-person').value;
            if (!personVal) {
                alert(isSweet ? 'Pick a teammate or “Someone new” 💕' : 'Select a teammate or Someone new.');
                return;
            }
            var name = '';
            var role = '';
            var personId = '';
            if (personVal === '__new__') {
                name = document.getElementById('i-name').value.trim();
                role = document.getElementById('i-role').value;
                if (!name) {
                    alert(isSweet ? 'Enter a name for the new hire' : 'Enter a name.');
                    return;
                }
            } else {
                var p = findPerson(personVal);
                if (!p) {
                    alert(isSweet ? 'Couldn’t find that teammate — refresh or pick again' : 'Teammate not found.');
                    return;
                }
                name = p.name;
                personId = p.id || '';
                role = document.getElementById('i-role-roster').value || (p.roles[0] || 'Other');
            }
            var code = codeGen();
            var noteEmp = (document.getElementById('i-note-employee').value || '').trim();
            var noteOwn = (document.getElementById('i-note-owner').value || '').trim();
            if (!noteEmp) {
                noteEmp = isSweet
                    ? ('Hey ' + name + '!\n\nYou’re invited to try ' + HOUSE_NAME + ' on ilovepbj ops.\n\n1) Go to ' + JOIN_URL + '\n2) Enter house code: ' + (HOUSE_CODE || '(ask me)') + '\n3) Create your login and jump in 💕\n\nPoke around, then tell me what you think!')
                    : ('Hi ' + name + ',\n\nJoin ' + HOUSE_NAME + ' at ' + JOIN_URL + ' with house code ' + (HOUSE_CODE || '(ask host)') + '.');
            }
            if (!noteOwn) {
                noteOwn = isSweet
                    ? ('Invited ' + name + ' (' + role + ') on ' + new Date().toLocaleDateString() + '. Tracking ' + code + '. Remove them under House logins when the trial is done.')
                    : ('Invited ' + name + ' · ' + role + ' · track ' + code);
            }
            state.invites.push({
                id: uid(),
                code: code,
                name: name,
                role: role,
                personId: personId,
                noteEmployee: noteEmp,
                noteOwner: noteOwn,
                used: false,
                at: Date.now()
            });
            save(true);
            document.getElementById('code-display').textContent = code;
            document.getElementById('note-employee-out').textContent = noteEmp;
            document.getElementById('note-owner-out').textContent = noteOwn;
            document.getElementById('new-code').style.display = 'block';
            document.getElementById('i-person').value = '';
            document.getElementById('i-name').value = '';
            document.getElementById('i-note-employee').value = '';
            document.getElementById('i-note-owner').value = '';
            onPersonChange();
            renderInvites();
        });

        function copyText(elId, btn) {
            var t = document.getElementById(elId);
            if (!t) return;
            var text = t.textContent || '';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    if (btn) { var o = btn.textContent; btn.textContent = isSweet ? 'Copied 💕' : 'Copied'; setTimeout(function () { btn.textContent = o; }, 1200); }
                });
            } else {
                alert(text);
            }
        }
        var copyEmp = document.getElementById('copy-employee-note');
        if (copyEmp) copyEmp.addEventListener('click', function () { copyText('note-employee-out', copyEmp); });
        var copyOwn = document.getElementById('copy-owner-note');
        if (copyOwn) copyOwn.addEventListener('click', function () { copyText('note-owner-out', copyOwn); });

        document.getElementById('invite-list').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]'); if (!btn) return;
            var id = btn.dataset.id;
            if (btn.dataset.act === 'use') {
                var inv = state.invites.find(function (x) { return x.id === id; });
                if (inv) inv.used = true;
                save(true); renderInvites();
            }
            if (btn.dataset.act === 'del') {
                if (!confirm(isSweet ? 'Remove this invite code?' : 'Remove this invite code?')) return;
                state.invites = state.invites.filter(function (x) { return x.id !== id; });
                save(true); renderInvites();
            }
        });

        document.getElementById('tracks').addEventListener('click', function (e) {
            var toggle = e.target.closest('[data-toggle]');
            if (toggle) {
                var id = toggle.dataset.toggle;
                if (state.training[id]) state.training[id].open = !state.training[id].open;
                save(false); renderTracks(); return;
            }

            var add = e.target.closest('[data-add-step]');
            if (add) {
                var tid = add.dataset.addStep;
                var input = document.querySelector('[data-new-step="' + tid + '"]');
                var label = input && input.value.trim();
                if (!label) return;
                state.training[tid].steps.push({ id: uid(), label: label, done: false });
                state.training[tid].open = true;
                save(true); editingStep = null; renderTracks(); return;
            }

            var done = e.target.closest('[data-toggle-done]');
            if (done) {
                e.preventDefault();
                var track = done.dataset.toggleDone, stepId = done.dataset.step;
                var step = state.training[track].steps.find(function (s) { return s.id === stepId; });
                if (step) { step.done = !step.done; save(false); renderTracks(); }
                return;
            }

            var editBtn = e.target.closest('[data-edit-step]');
            if (editBtn) {
                editingStep = { trackId: editBtn.dataset.editStep, stepId: editBtn.dataset.step };
                renderTracks();
                setTimeout(function () {
                    var inp = document.querySelector('[data-edit-input="' + editBtn.dataset.editStep + '"][data-step="' + editBtn.dataset.step + '"]');
                    if (inp) { inp.focus(); inp.select(); }
                }, 30);
                return;
            }

            var cancel = e.target.closest('[data-cancel-edit]');
            if (cancel) {
                editingStep = null;
                renderTracks();
                return;
            }

            var saveBtn = e.target.closest('[data-save-step]');
            if (saveBtn) {
                var tid2 = saveBtn.dataset.saveStep;
                var sid = saveBtn.dataset.step;
                var inp2 = document.querySelector('[data-edit-input="' + tid2 + '"][data-step="' + sid + '"]');
                var newLabel = inp2 && inp2.value.trim();
                if (!newLabel) {
                    alert(isSweet ? 'Step can’t be blank' : 'Step can’t be blank.');
                    return;
                }
                var st = state.training[tid2].steps.find(function (s) { return s.id === sid; });
                if (st) st.label = newLabel;
                editingStep = null;
                save(true);
                renderTracks();
                return;
            }

            var delStep = e.target.closest('[data-del-step]');
            if (delStep) {
                if (!confirm(isSweet ? 'Remove this training step?' : 'Remove this step?')) return;
                var tid3 = delStep.dataset.delStep;
                var sid2 = delStep.dataset.step;
                state.training[tid3].steps = state.training[tid3].steps.filter(function (s) { return s.id !== sid2; });
                if (editingStep && editingStep.stepId === sid2) editingStep = null;
                save(true);
                renderTracks();
                return;
            }

            var move = e.target.closest('[data-move]');
            if (move) {
                var tid4 = move.dataset.move;
                var sid3 = move.dataset.step;
                var dir = parseInt(move.dataset.dir, 10);
                var arr = state.training[tid4].steps;
                var idx = -1;
                for (var i = 0; i < arr.length; i++) if (arr[i].id === sid3) { idx = i; break; }
                var j = idx + dir;
                if (idx < 0 || j < 0 || j >= arr.length) return;
                var tmp = arr[idx]; arr[idx] = arr[j]; arr[j] = tmp;
                save(false);
                renderTracks();
                return;
            }

            var editTrack = e.target.closest('[data-edit-track]');
            if (editTrack) {
                var tid5 = editTrack.dataset.editTrack;
                var tr = state.training[tid5];
                if (!tr) return;
                var title = prompt(isSweet ? 'Track title' : 'Track title', tr.title || '');
                if (title == null) return;
                title = title.trim();
                if (!title) { alert(isSweet ? 'Title can’t be blank' : 'Title required.'); return; }
                var hint = prompt(isSweet ? 'Short hint (optional)' : 'Hint (optional)', tr.hint || '');
                if (hint == null) return;
                var icon = prompt(isSweet ? 'Emoji icon' : 'Icon', tr.icon || '📌');
                if (icon == null) return;
                tr.title = title;
                tr.hint = hint.trim();
                tr.icon = (icon.trim() || '📌').slice(0, 4);
                save(true);
                renderTracks();
                return;
            }

            var delTrack = e.target.closest('[data-del-track]');
            if (delTrack) {
                var tid6 = delTrack.dataset.delTrack;
                var tr2 = state.training[tid6];
                if (!tr2) return;
                if (!confirm(isSweet ? 'Remove the whole “' + tr2.title + '” track and its steps?' : 'Remove this track and its steps?')) return;
                delete state.training[tid6];
                state.trackOrder = state.trackOrder.filter(function (x) { return x !== tid6; });
                if (editingStep && editingStep.trackId === tid6) editingStep = null;
                save(true);
                renderTracks();
            }
        });

        // Enter to save step edit
        document.getElementById('tracks').addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            var inp = e.target.closest('[data-edit-input]');
            if (!inp) return;
            e.preventDefault();
            var btn = document.querySelector('[data-save-step="' + inp.getAttribute('data-edit-input') + '"][data-step="' + inp.getAttribute('data-step') + '"]');
            if (btn) btn.click();
        });

        document.getElementById('add-track-btn').addEventListener('click', function () {
                if (!canP('admin.team.onboarding.add_track')) return;
            var title = document.getElementById('new-track-title').value.trim();
            if (!title) {
                alert(isSweet ? 'Give the track a title 💕' : 'Enter a track title.');
                return;
            }
            var hint = document.getElementById('new-track-hint').value.trim();
            var id = 'track-' + uid();
            state.training[id] = {
                id: id,
                title: title,
                icon: '📌',
                hint: hint,
                open: true,
                steps: []
            };
            state.trackOrder.push(id);
            document.getElementById('new-track-title').value = '';
            document.getElementById('new-track-hint').value = '';
            save(true);
            renderTracks();
        });

        document.getElementById('reset-training').addEventListener('click', function () {
            if (!confirm(isSweet ? 'Uncheck all training steps?' : 'Uncheck all training steps?')) return;
            Object.keys(state.training).forEach(function (k) {
                (state.training[k].steps || []).forEach(function (s) { s.done = false; });
            });
            save(true); renderTracks();
        });

        window.addEventListener('focus', function () {
            team = loadTeam();
            fillPersonSelect();
        });

        fillPersonSelect();
        renderInvites();
        renderTracks();
    })();
    </script>
</body>
</html>
