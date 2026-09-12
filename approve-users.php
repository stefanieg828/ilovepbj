<?php
require_once 'config.php';

if (empty($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
    header('Location: /login');
    exit();
}

$email = $_SESSION['email'] ?? '';
// Load email from DB if missing in session
if ($email === '') {
    $stmt = $pdo->prepare('SELECT email, access_status FROM users WHERE id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $me = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($me) {
        $email = $me['email'] ?? '';
        $_SESSION['email'] = $email;
        $_SESSION['access_status'] = $me['access_status'] ?? 'pending';
    }
}

if (!pbj_is_platform_admin($email)) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Nope</title></head><body style="font-family:Georgia;background:#FCF8EE;padding:40px;text-align:center;">';
    echo '<h1 style="color:#E55163;">User desk</h1>';
    echo '<p>Only platform admins can open this page.</p>';
    echo '<p><a href="/home">Back</a> · <a href="/logout">Log out</a></p>';
    echo '</body></html>';
    exit();
}

// Admins must themselves be approved (they should be)
if (!pbj_user_is_approved()) {
    // Force-approve platform admin in DB if needed
    $pdo->prepare("UPDATE users SET access_status = 'approved' WHERE id = ?")->execute([(int)$_SESSION['user_id']]);
    $_SESSION['access_status'] = 'approved';
}

$message = '';
$error = '';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

/**
 * Permanently remove a login so email/username can register again.
 * @return array{ok:bool,message?:string,error?:string}
 */
function pbj_admin_delete_user_account(PDO $pdo, int $targetId, int $actorId): array {
    if ($targetId <= 0) {
        return ['ok' => false, 'error' => 'Invalid user.'];
    }
    if ($targetId === $actorId) {
        return ['ok' => false, 'error' => 'You can’t delete your own account while logged in.'];
    }

    $stmt = $pdo->prepare('SELECT id, username, email, full_name, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$targetId]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target) {
        return ['ok' => false, 'error' => 'User not found (already deleted?).'];
    }

    $targetEmail = strtolower(trim((string) ($target['email'] ?? '')));
    if ($targetEmail !== '' && function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($targetEmail)) {
        return ['ok' => false, 'error' => 'Platform admin accounts can’t be deleted here.'];
    }

    try {
        $pdo->beginTransaction();

        // Restaurants this user owns
        $own = $pdo->prepare('SELECT id, name FROM restaurants WHERE owner_id = ?');
        $own->execute([$targetId]);
        $owned = $own->fetchAll(PDO::FETCH_ASSOC);

        foreach ($owned as $rest) {
            $rid = (int) $rest['id'];
            $cntStmt = $pdo->prepare('SELECT user_id FROM user_restaurant WHERE restaurant_id = ? AND user_id != ?');
            $cntStmt->execute([$rid, $targetId]);
            $others = $cntStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!$others) {
                // Empty house — remove restaurant + settings
                try {
                    $pdo->prepare('DELETE FROM restaurant_settings WHERE restaurant_id = ?')->execute([$rid]);
                } catch (Throwable $e) {
                    // table may not exist on older DBs
                }
                $pdo->prepare('DELETE FROM user_restaurant WHERE restaurant_id = ?')->execute([$rid]);
                $pdo->prepare('DELETE FROM restaurants WHERE id = ?')->execute([$rid]);
            } else {
                // Keep house; hand ownership to earliest remaining member
                $newOwner = (int) $others[0];
                $pdo->prepare('UPDATE restaurants SET owner_id = ? WHERE id = ?')->execute([$newOwner, $rid]);
                // Ensure membership row exists (should already)
                $chk = $pdo->prepare('SELECT 1 FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
                $chk->execute([$newOwner, $rid]);
                if (!$chk->fetchColumn()) {
                    $pdo->prepare('INSERT INTO user_restaurant (user_id, restaurant_id, role) VALUES (?, ?, ?)')
                        ->execute([$newOwner, $rid, 'owner']);
                }
            }
        }

        // Memberships
        $pdo->prepare('DELETE FROM user_restaurant WHERE user_id = ?')->execute([$targetId]);
        // Login row (frees email + username for re-register)
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$targetId]);

        $pdo->commit();
        $label = $target['email'] ?: $target['username'];
        return [
            'ok' => true,
            'message' => 'Deleted account for ' . $label . '. That email/username can register again. If they had a Stripe subscription, cancel it in the Stripe Dashboard separately.',
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('delete user failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Delete failed. Try again or check server logs.'];
    }
}

/**
 * Update account fields (platform admin only). Optionally set a new password.
 * @return array{ok:bool,message?:string,error?:string}
 */
/** @deprecated use pbj_admin_save_user_profile */
function pbj_admin_edit_user_account(PDO $pdo, int $targetId, array $fields): array {
    return pbj_admin_save_user_profile($pdo, $targetId, $fields);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $targetId = (int) ($_POST['user_id'] ?? 0);
    $actorId = (int) ($_SESSION['user_id'] ?? 0);

    if ($targetId > 0 && $action === 'delete') {
        $result = pbj_admin_delete_user_account($pdo, $targetId, $actorId);
        if (!empty($result['ok'])) {
            $message = $result['message'] ?? 'Account deleted.';
            if ($editId === $targetId) {
                $editId = 0;
            }
        } else {
            $error = $result['error'] ?? 'Delete failed.';
        }
    } elseif ($targetId > 0 && $action === 'edit') {
        $result = pbj_admin_save_user_profile($pdo, $targetId, [
            'full_name' => $_POST['full_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'foh',
            'access_status' => $_POST['access_status'] ?? 'pending',
            'new_password' => $_POST['new_password'] ?? '',
            'new_password_confirm' => $_POST['new_password_confirm'] ?? '',
        ]);
        if (!empty($result['ok'])) {
            $message = $result['message'] ?? 'Saved.';
            $editId = $targetId;
        } else {
            $error = $result['error'] ?? 'Update failed.';
            $editId = $targetId;
        }
    } elseif ($targetId > 0 && $action === 'send_reset') {
        $result = pbj_admin_send_password_reset($pdo, $targetId);
        if (!empty($result['ok'])) {
            $message = $result['message'] ?? 'Reset sent.';
            if (!empty($result['link'])) {
                $message .= ' Link (1 hour): ' . $result['link'];
            }
            $editId = $targetId;
        } else {
            $error = $result['error'] ?? 'Reset failed.';
            $editId = $targetId;
        }
    } elseif ($targetId > 0 && $action === 'archive') {
        if (!function_exists('pbj_admin_archive_user')) {
            $error = 'Archive helper missing.';
        } else {
            $result = pbj_admin_archive_user($pdo, $targetId, true);
            if (!empty($result['ok'])) {
                $message = $result['message'] ?? ('Archived user #' . $targetId . '.');
            } else {
                $error = $result['error'] ?? 'Archive failed.';
            }
        }
    } elseif ($targetId > 0 && in_array($action, ['approve', 'pending', 'block'], true)) {
        $map = [
            'approve' => 'approved',
            'pending' => 'pending',
            'block' => 'blocked',
        ];
        $newStatus = $map[$action];
        $upd = $pdo->prepare('UPDATE users SET access_status = ? WHERE id = ?');
        $upd->execute([$newStatus, $targetId]);
        $message = 'Updated user #' . $targetId . ' → ' . $newStatus . '.';
    } else {
        $error = 'Invalid action.';
    }
}

$filter = (string)($_GET['filter'] ?? 'pending');
if (!in_array($filter, ['pending', 'approved', 'blocked', 'archived', 'all'], true)) {
    $filter = 'pending';
}

$selectCols = 'id, username, email, full_name, role, access_status, created_at, last_login_at, password_reset_token, password_reset_expires';

if ($filter === 'all') {
    $users = $pdo->query(
        "SELECT $selectCols FROM users ORDER BY created_at DESC, id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare(
        "SELECT $selectCols FROM users WHERE access_status = ? ORDER BY created_at DESC, id DESC"
    );
    $stmt->execute([$filter]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$counts = [
    'pending' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE access_status = 'pending'")->fetchColumn(),
    'approved' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE access_status = 'approved'")->fetchColumn(),
    'blocked' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE access_status = 'blocked'")->fetchColumn(),
    'archived' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE access_status = 'archived'")->fetchColumn(),
    'all' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];

$rolesList = ['owner', 'manager', 'foh', 'boh', 'admin'];
$accessList = ['pending', 'approved', 'blocked', 'archived'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User desk · ilovepbj ops</title>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DreamingOutLoudPro', Georgia, serif; background: #FCF8EE; color: #3a2f1f; padding-bottom: 40px; }
        .top {
            background: linear-gradient(105deg, #E55163, #6B4A8C); color: white;
            padding: 18px 20px 22px; text-align: center;
        }
        .top a { color: #BBE7DA; text-decoration: none; font-size: 0.95rem; }
        h1 { font-family: 'ModernLoveCaps', serif; margin: 8px 0 0; font-size: 2rem; }
        .wrap { max-width: 880px; margin: 0 auto; padding: 20px 14px; }
        .intro {
            background: white; border-radius: 16px; padding: 16px 18px; margin-bottom: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06); line-height: 1.45; border-left: 5px solid #BBE7DA;
        }
        .tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .tab {
            padding: 10px 14px; border-radius: 999px; text-decoration: none; color: #3a2f1f;
            background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); font-size: 0.95rem;
        }
        .tab.active { background: #6B4A8C; color: #BBE7DA; }
        .msg { background: #E8F8F1; color: #1F6B4A; border-radius: 12px; padding: 10px 14px; margin-bottom: 12px; }
        .err { background: #FDECEA; color: #B71C1C; border-radius: 12px; padding: 10px 14px; margin-bottom: 12px; }
        .card {
            background: white; border-radius: 16px; padding: 16px; margin-bottom: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 2px solid #F3E8DD;
        }
        .card.pending { border-color: #F3C5CC; }
        .card.approved { border-color: #B8E6CF; }
        .card.blocked { border-color: #C5D0DE; opacity: 0.9; }
        .card.archived { border-color: #D4C4A8; opacity: 0.92; }
        .row-top { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; align-items: flex-start; }
        .name { font-size: 1.15rem; margin: 0 0 4px; }
        .meta { font-size: 0.9rem; opacity: 0.75; line-height: 1.45; }
        .badge {
            display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.8rem;
            background: #FFF5F6; color: #E55163;
        }
        .badge.ok { background: #E8F8F1; color: #1F6B4A; }
        .badge.bad { background: #EEF2F8; color: #1A2A44; }
        .badge.arch { background: #F3E8DD; color: #6B4A2A; }
        .badge.warn { background: #FFF4E0; color: #8A5A00; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; align-items: center; }
        .actions button, .actions a.btn-link {
            border: none; border-radius: 12px; padding: 10px 14px; cursor: pointer;
            font-family: inherit; font-size: 0.95rem; text-decoration: none; display: inline-block;
        }
        .btn-ok { background: #E55163; color: white; }
        .btn-wait { background: #BBE7DA; color: #6B4A8C; }
        .btn-block { background: #6B4A8C; color: #BBE7DA; }
        .btn-archive { background: #D4C4A8; color: #3a2f1f; }
        .btn-del { background: #FDECEA; color: #B71C1C; border: 1px solid #F5C2C0; }
        .btn-edit { background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC; }
        .btn-save { background: #E55163; color: white; }
        .btn-cancel { background: #EEF2F8; color: #1A2A44; }
        .empty { text-align: center; padding: 28px; opacity: 0.75; background: white; border-radius: 16px; }
        .links { text-align: center; margin-top: 18px; }
        .links a { color: #E55163; font-weight: 600; margin: 0 8px; }
        .edit-box {
            margin-top: 14px; padding-top: 14px; border-top: 1px solid #F3E8DD;
        }
        .edit-box label {
            display: block; font-size: 0.85rem; opacity: 0.7; margin: 8px 0 4px;
        }
        .edit-box input, .edit-box select {
            width: 100%; max-width: 420px; padding: 10px 12px; border: 2px solid #F3C5CC;
            border-radius: 12px; font-size: 1rem; font-family: inherit; background: #FFFBF8; color: #3a2f1f;
        }
        .edit-box .grid2 {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-width: 420px;
        }
        @media (max-width: 520px) { .edit-box .grid2 { grid-template-columns: 1fr; } }
        .edit-hint { font-size: 0.85rem; opacity: 0.65; margin: 6px 0 0; }
        .uid { font-size: 0.8rem; opacity: 0.5; }
    </style>
</head>
<body>
    <div class="top">
        <a href="/home">← Back to hub</a>
        <h1>User desk</h1>
    </div>
    <div class="wrap">
        <div class="intro">
            <strong>Platform admin only</strong> — edit every login: name, username, email, role, access, and last login.
            <strong>Playground / free accounts:</strong> you may set a password directly.
            <strong>Paying houses &amp; their staff:</strong> use <em>Send password reset</em> (no direct password set).
            Paid house accounts should be <strong>Owner</strong>. Cancel Stripe separately if you delete a paid owner.<br><strong>Archive</strong> soft-locks inactive users (does not delete). They reactivate by resetting their password.
        </div>

        <?php if ($message): ?><div class="msg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="tabs">
            <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'blocked' => 'Blocked', 'archived' => 'Archived', 'all' => 'All'] as $key => $label): ?>
                <a class="tab<?php echo $filter === $key ? ' active' : ''; ?>" href="/approve-users?filter=<?php echo $key; ?>">
                    <?php echo $label; ?> (<?php echo (int)$counts[$key]; ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$users): ?>
            <div class="empty">No people in this list right now.</div>
        <?php else: ?>
            <?php foreach ($users as $u): ?>
                <?php
                $uid = (int) $u['id'];
                $isEditing = ($editId === $uid);
                $resetActive = !empty($u['password_reset_token'])
                    && !empty($u['password_reset_expires'])
                    && strtotime((string) $u['password_reset_expires']) > time();
                $lastLogin = !empty($u['last_login_at']) ? (string) $u['last_login_at'] : 'Never';
                $isSelf = ($uid === (int) ($_SESSION['user_id'] ?? 0));
                $isPlatAdmin = function_exists('pbj_is_platform_admin') && pbj_is_platform_admin((string) ($u['email'] ?? ''));
                $onPaying = function_exists('pbj_user_is_on_paying_house') && pbj_user_is_on_paying_house($pdo, $uid);
                $allowDirectPw = function_exists('pbj_user_allows_direct_password_set') && pbj_user_allows_direct_password_set($pdo, $uid);
                ?>
                <div class="card <?php echo htmlspecialchars($u['access_status']); ?>" id="user-<?php echo $uid; ?>">
                    <div class="row-top">
                        <div>
                            <p class="name"><?php echo htmlspecialchars($u['full_name'] ?: $u['username']); ?>
                                <span class="uid">#<?php echo $uid; ?></span></p>
                            <div class="meta">
                                @<?php echo htmlspecialchars($u['username']); ?>
                                · <?php echo htmlspecialchars($u['email']); ?><br>
                                Role: <strong><?php echo htmlspecialchars($u['role']); ?></strong>
                                · Joined <?php echo htmlspecialchars((string)$u['created_at']); ?><br>
                                Last login: <?php echo htmlspecialchars($lastLogin); ?>
                                <?php if ($onPaying): ?>
                                    <br><span class="badge ok">Paying house account</span>
                                <?php else: ?>
                                    <br><span class="badge">Playground / free</span>
                                <?php endif; ?>
                                <?php if ($resetActive): ?>
                                    <br><span class="badge warn">Password reset active</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="badge<?php
                            echo $u['access_status'] === 'approved' ? ' ok'
                                : ($u['access_status'] === 'blocked' ? ' bad'
                                : ($u['access_status'] === 'archived' ? ' arch' : ''));
                        ?>"><?php echo htmlspecialchars($u['access_status']); ?></span>
                    </div>
                    <form method="POST" class="actions">
                        <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                        <?php if ($u['access_status'] !== 'approved'): ?>
                            <button class="btn-ok" type="submit" name="action" value="approve">Approve</button>
                        <?php endif; ?>
                        <?php if ($u['access_status'] !== 'pending'): ?>
                            <button class="btn-wait" type="submit" name="action" value="pending">Set pending</button>
                        <?php endif; ?>
                        <?php if ($u['access_status'] !== 'blocked'): ?>
                            <button class="btn-block" type="submit" name="action" value="block">Block</button>
                        <?php endif; ?>
                        <?php if ($u['access_status'] !== 'archived' && !$isSelf && !$isPlatAdmin): ?>
                            <button class="btn-archive" type="submit" name="action" value="archive"
                                onclick="return confirm('Archive <?php echo htmlspecialchars(addslashes($u['email'] ?: $u['username']), ENT_QUOTES); ?>? They can’t log in with the old password — they must reset password to reactivate.');">
                                Archive
                            </button>
                        <?php endif; ?>
                        <button class="btn-edit" type="submit" name="action" value="send_reset"
                            onclick="return confirm('Email a password reset link to <?php echo htmlspecialchars(addslashes((string)$u['email']), ENT_QUOTES); ?>?');">
                            Send password reset
                        </button>
                        <?php if (!$isEditing): ?>
                            <a class="btn-edit btn-link" href="/approve-users?filter=<?php echo urlencode($filter); ?>&edit=<?php echo $uid; ?>#user-<?php echo $uid; ?>">Edit all info</a>
                        <?php endif; ?>
                        <?php if (!$isSelf && !$isPlatAdmin): ?>
                            <button class="btn-del" type="submit" name="action" value="delete"
                                onclick="return confirm('Permanently delete <?php echo htmlspecialchars(addslashes($u['email'] ?: $u['username']), ENT_QUOTES); ?>? They can register again with this email. This cannot be undone.');">
                                Delete permanently
                            </button>
                        <?php endif; ?>
                    </form>

                    <?php if ($isEditing): ?>
                    <form method="POST" class="edit-box">
                        <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                        <input type="hidden" name="action" value="edit">
                        <label for="fn-<?php echo $uid; ?>">Full name</label>
                        <input id="fn-<?php echo $uid; ?>" type="text" name="full_name" required
                            value="<?php echo htmlspecialchars((string) $u['full_name']); ?>">

                        <label for="un-<?php echo $uid; ?>">Username</label>
                        <input id="un-<?php echo $uid; ?>" type="text" name="username" required autocomplete="off"
                            value="<?php echo htmlspecialchars((string) $u['username']); ?>">

                        <label for="em-<?php echo $uid; ?>">Email</label>
                        <input id="em-<?php echo $uid; ?>" type="email" name="email" required autocomplete="off"
                            value="<?php echo htmlspecialchars((string) $u['email']); ?>">

                        <div class="grid2">
                            <div>
                                <label for="role-<?php echo $uid; ?>">Role</label>
                                <select id="role-<?php echo $uid; ?>" name="role">
                                    <?php foreach ($rolesList as $r): ?>
                                        <option value="<?php echo $r; ?>"<?php echo $u['role'] === $r ? ' selected' : ''; ?>><?php echo $r; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="acc-<?php echo $uid; ?>">Access</label>
                                <select id="acc-<?php echo $uid; ?>" name="access_status">
                                    <?php foreach ($accessList as $a): ?>
                                        <option value="<?php echo $a; ?>"<?php echo $u['access_status'] === $a ? ' selected' : ''; ?>><?php echo $a; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if ($allowDirectPw): ?>
                        <label for="pw-<?php echo $uid; ?>">New password (optional)</label>
                        <input id="pw-<?php echo $uid; ?>" type="password" name="new_password" autocomplete="new-password" minlength="6" placeholder="Leave blank to keep current">

                        <label for="pw2-<?php echo $uid; ?>">Confirm new password</label>
                        <input id="pw2-<?php echo $uid; ?>" type="password" name="new_password_confirm" autocomplete="new-password" minlength="6" placeholder="Same as above">
                        <p class="edit-hint">Playground / free account — you may set a password directly, or use Send password reset above.</p>
                        <?php else: ?>
                        <p class="edit-hint" style="margin-top:12px;">
                            <strong>Paying house account</strong> — password is not editable here.
                            Use <strong>Send password reset</strong> so they choose a new password securely.
                        </p>
                        <?php endif; ?>

                        <div class="actions">
                            <button class="btn-save" type="submit">Save changes</button>
                            <a class="btn-cancel btn-link" href="/approve-users?filter=<?php echo urlencode($filter); ?>">Cancel</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="links">
            <a href="/home">Hub</a>
            <a href="/waiting">Waiting page</a>
            <a href="/logout">Log out</a>
        </div>
    </div>
</body>
</html>
