<?php
/**
 * PRIVATE platform console — restaurants + every user.
 * Platform-admin emails only. Not linked for the public.
 */
require_once __DIR__ . '/config.php';
if (is_readable(__DIR__ . '/pbj-permissions.php')) {
    require_once __DIR__ . '/pbj-permissions.php';
}
if (is_readable(__DIR__ . '/sales-playground.inc.php')) {
    require_once __DIR__ . '/sales-playground.inc.php';
}

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    header('Location: /login?next=' . urlencode('/platform'));
    exit();
}

$email = (string) ($_SESSION['email'] ?? '');
if ($email === '') {
    try {
        $stmt = $pdo->prepare('SELECT email, access_status FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $_SESSION['user_id']]);
        $me = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($me) {
            $email = (string) ($me['email'] ?? '');
            $_SESSION['email'] = $email;
            $_SESSION['access_status'] = $me['access_status'] ?? 'pending';
        }
    } catch (Throwable $e) {
        // ignore
    }
}

if (!function_exists('pbj_is_platform_admin') || !pbj_is_platform_admin($email)) {
    http_response_code(403);
    header('X-Robots-Tag: noindex, nofollow');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="robots" content="noindex,nofollow"><title>Private</title></head>';
    echo '<body style="font-family:Georgia;background:#FCF8EE;padding:40px;text-align:center;color:#3a2f1f;">';
    echo '<h1 style="color:#E55163;">Private</h1><p>This page is not available.</p>';
    echo '<p><a href="/home">Home</a></p></body></html>';
    exit();
}

if (!pbj_user_is_approved()) {
    $pdo->prepare("UPDATE users SET access_status = 'approved' WHERE id = ?")->execute([(int) $_SESSION['user_id']]);
    $_SESSION['access_status'] = 'approved';
}

$actorId = (int) $_SESSION['user_id'];
$message = '';
$error = '';
$tab = (string) ($_GET['tab'] ?? 'restaurants');
if (!in_array($tab, ['restaurants', 'users', 'individuals', 'sales'], true)) {
    $tab = 'restaurants';
}
if (is_readable(__DIR__ . '/sales-commission.inc.php')) {
    require_once __DIR__ . '/sales-commission.inc.php';
}
$editUserId = isset($_GET['edit_user']) ? (int) $_GET['edit_user'] : 0;
$editRestId = isset($_GET['edit_rest']) ? (int) $_GET['edit_rest'] : 0;

/** @return array{ok:bool,message?:string,error?:string} */
function pbj_platform_delete_restaurant(PDO $pdo, int $rid, int $actorId): array {
    if ($rid <= 0) {
        return ['ok' => false, 'error' => 'Invalid restaurant.'];
    }
    try {
        $stmt = $pdo->prepare('SELECT id, name, invite_code FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->execute([$rid]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            return ['ok' => false, 'error' => 'Restaurant not found.'];
        }
        $code = strtoupper(trim((string) ($r['invite_code'] ?? '')));
        // Soft-guard playgrounds — still allow if confirmed via form
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM restaurant_shared_state WHERE restaurant_id = ?')->execute([$rid]);
        } catch (Throwable $e) {
            // ignore
        }
        try {
            $pdo->prepare('DELETE FROM restaurant_settings WHERE restaurant_id = ?')->execute([$rid]);
        } catch (Throwable $e) {
            // ignore
        }
        $pdo->prepare('DELETE FROM user_restaurant WHERE restaurant_id = ?')->execute([$rid]);
        $pdo->prepare('DELETE FROM restaurants WHERE id = ?')->execute([$rid]);
        $pdo->commit();
        return ['ok' => true, 'message' => 'Deleted restaurant “' . $r['name'] . '” (' . $code . ').'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('platform delete restaurant: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not delete restaurant.'];
    }
}

/** Ensure delete-user helper exists (shared with user desk). */
if (!function_exists('pbj_admin_delete_user_account')) {
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
            return ['ok' => false, 'error' => 'User not found.'];
        }
        $targetEmail = strtolower(trim((string) ($target['email'] ?? '')));
        if ($targetEmail !== '' && pbj_is_platform_admin($targetEmail)) {
            return ['ok' => false, 'error' => 'Platform admin accounts can’t be deleted here.'];
        }
        try {
            $pdo->beginTransaction();
            $own = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ?');
            $own->execute([$targetId]);
            $owned = $own->fetchAll(PDO::FETCH_COLUMN) ?: [];
            foreach ($owned as $rid) {
                $rid = (int) $rid;
                $cntStmt = $pdo->prepare('SELECT user_id FROM user_restaurant WHERE restaurant_id = ? AND user_id != ?');
                $cntStmt->execute([$rid, $targetId]);
                $others = $cntStmt->fetchAll(PDO::FETCH_COLUMN);
                if (!$others) {
                    try { $pdo->prepare('DELETE FROM restaurant_settings WHERE restaurant_id = ?')->execute([$rid]); } catch (Throwable $e) {}
                    try { $pdo->prepare('DELETE FROM restaurant_shared_state WHERE restaurant_id = ?')->execute([$rid]); } catch (Throwable $e) {}
                    $pdo->prepare('DELETE FROM user_restaurant WHERE restaurant_id = ?')->execute([$rid]);
                    $pdo->prepare('DELETE FROM restaurants WHERE id = ?')->execute([$rid]);
                } else {
                    $newOwner = (int) $others[0];
                    $pdo->prepare('UPDATE restaurants SET owner_id = ? WHERE id = ?')->execute([$newOwner, $rid]);
                }
            }
            $pdo->prepare('DELETE FROM user_restaurant WHERE user_id = ?')->execute([$targetId]);
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$targetId]);
            $pdo->commit();
            return ['ok' => true, 'message' => 'Deleted user ' . ($target['email'] ?: $target['username']) . '.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'Delete failed.'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    // —— Restaurants ——
    if ($action === 'rest_create') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $ownerId = (int) ($_POST['owner_id'] ?? 0);
        $planId = (string) ($_POST['plan_id'] ?? 'crew_10');
        $billing = (string) ($_POST['billing_status'] ?? 'lifetime_free');
        if ($name === '' || $ownerId <= 0) {
            $error = 'Restaurant name and owner are required.';
        } else {
            try {
                $created = pbj_create_restaurant($pdo, $ownerId, $name, $planId);
                $rid = (int) $created['id'];
                // Override billing
                $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
                $stmt->execute([$rid]);
                $raw = $stmt->fetchColumn();
                $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
                $settings['plan'] = $planId;
                $settings['billing_status'] = $billing;
                $settings['billing'] = array_merge(
                    is_array($settings['billing'] ?? null) ? $settings['billing'] : [],
                    [
                        'status' => $billing,
                        'plan_id' => $planId,
                        'granted_by' => 'platform_console',
                        'granted_at' => date('c'),
                    ]
                );
                $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
                if ($raw !== false && $raw !== null) {
                    $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                        ->execute([$json, $rid]);
                } else {
                    $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                        ->execute([$rid, $json]);
                }
                $pdo->prepare("UPDATE users SET role = 'owner', access_status = 'approved' WHERE id = ?")->execute([$ownerId]);
                $message = 'Created restaurant “' . $name . '” · code ' . $created['invite_code'] . '.';
                $tab = 'restaurants';
            } catch (Throwable $e) {
                $error = 'Could not create restaurant: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'rest_update') {
        $rid = (int) ($_POST['restaurant_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $ownerId = (int) ($_POST['owner_id'] ?? 0);
        $invite = strtoupper(trim((string) ($_POST['invite_code'] ?? '')));
        $planId = (string) ($_POST['plan_id'] ?? 'crew_10');
        $billing = (string) ($_POST['billing_status'] ?? 'selected_unpaid');
        if ($rid <= 0 || $name === '' || $ownerId <= 0 || $invite === '') {
            $error = 'Name, owner, and invite code are required.';
            $editRestId = $rid;
        } else {
            try {
                // Unique invite
                $dup = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ? AND id != ? LIMIT 1');
                $dup->execute([$invite, $rid]);
                if ($dup->fetchColumn()) {
                    $error = 'That invite code is already used by another house.';
                    $editRestId = $rid;
                } else {
                    $pdo->prepare('UPDATE restaurants SET name = ?, owner_id = ?, invite_code = ? WHERE id = ?')
                        ->execute([$name, $ownerId, $invite, $rid]);
                    // Ensure owner membership
                    $chk = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
                    $chk->execute([$ownerId, $rid]);
                    if ($chk->fetchColumn() === false) {
                        $pdo->prepare("INSERT INTO user_restaurant (user_id, restaurant_id, role) VALUES (?, ?, 'owner')")
                            ->execute([$ownerId, $rid]);
                    } else {
                        $pdo->prepare("UPDATE user_restaurant SET role = 'owner' WHERE user_id = ? AND restaurant_id = ?")
                            ->execute([$ownerId, $rid]);
                    }
                    $pdo->prepare("UPDATE users SET role = 'owner', access_status = 'approved' WHERE id = ?")->execute([$ownerId]);

                    $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
                    $stmt->execute([$rid]);
                    $raw = $stmt->fetchColumn();
                    $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
                    $settings['plan'] = $planId;
                    $settings['billing_status'] = $billing;
                    $settings['billing'] = array_merge(
                        is_array($settings['billing'] ?? null) ? $settings['billing'] : [],
                        ['status' => $billing, 'plan_id' => $planId, 'updated_at' => date('c'), 'updated_by' => 'platform_console']
                    );
                    $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
                    if ($raw !== false && $raw !== null) {
                        $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                            ->execute([$json, $rid]);
                    } else {
                        $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                            ->execute([$rid, $json]);
                    }
                    $message = 'Updated restaurant #' . $rid . ' · ' . $name . '.';
                    $editRestId = $rid;
                    $tab = 'restaurants';
                }
            } catch (Throwable $e) {
                $error = 'Update failed: ' . $e->getMessage();
                $editRestId = $rid;
            }
        }
    } elseif ($action === 'rest_delete') {
        $rid = (int) ($_POST['restaurant_id'] ?? 0);
        $res = pbj_platform_delete_restaurant($pdo, $rid, $actorId);
        if (!empty($res['ok'])) {
            $message = $res['message'] ?? 'Deleted.';
            if ($editRestId === $rid) {
                $editRestId = 0;
            }
        } else {
            $error = $res['error'] ?? 'Delete failed.';
        }
        $tab = 'restaurants';
    } elseif ($action === 'rest_add_member') {
        $rid = (int) ($_POST['restaurant_id'] ?? 0);
        $uid = (int) ($_POST['user_id'] ?? 0);
        $role = (string) ($_POST['role'] ?? 'foh');
        if ($rid <= 0 || $uid <= 0) {
            $error = 'Pick a restaurant and user.';
        } else {
            try {
                pbj_join_restaurant($pdo, $uid, $rid, $role);
                $pdo->prepare('UPDATE user_restaurant SET role = ? WHERE user_id = ? AND restaurant_id = ?')
                    ->execute([$role, $uid, $rid]);
                $message = 'Added user #' . $uid . ' to restaurant #' . $rid . ' as ' . $role . '.';
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
        $tab = 'restaurants';
        $editRestId = $rid;
    } elseif ($action === 'rest_remove_member') {
        $rid = (int) ($_POST['restaurant_id'] ?? 0);
        $uid = (int) ($_POST['user_id'] ?? 0);
        $res = pbj_remove_restaurant_member($pdo, $rid, $uid, $actorId);
        // Platform admin override if remove helper blocks
        if (empty($res['ok']) && $uid > 0 && $rid > 0) {
            try {
                $own = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ?');
                $own->execute([$rid]);
                if ((int) $own->fetchColumn() === $uid) {
                    $error = 'Cannot remove the billable owner — reassign owner first.';
                } else {
                    $pdo->prepare('DELETE FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?')->execute([$uid, $rid]);
                    $message = 'Removed user #' . $uid . ' from restaurant #' . $rid . '.';
                    $res = ['ok' => true];
                }
            } catch (Throwable $e) {
                $error = 'Remove failed.';
            }
        } elseif (!empty($res['ok'])) {
            $message = 'Removed member from house.';
        } else {
            $error = $res['error'] ?? 'Remove failed.';
        }
        $tab = 'restaurants';
        $editRestId = $rid;
    }

    // —— Users ——
    elseif ($action === 'user_create') {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $newEmail = trim((string) ($_POST['email'] ?? ''));
        $role = strtolower(trim((string) ($_POST['role'] ?? 'foh')));
        $access = strtolower(trim((string) ($_POST['access_status'] ?? 'approved')));
        $pass = (string) ($_POST['password'] ?? '');
        if ($fullName === '' || $username === '' || $newEmail === '' || strlen($pass) < 6) {
            $error = 'Name, username, email, and password (6+ chars) are required.';
            $tab = 'users';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email.';
            $tab = 'users';
        } else {
            try {
                $dup = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
                $dup->execute([$username, $newEmail]);
                if ($dup->fetchColumn()) {
                    $error = 'Username or email already exists.';
                } else {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $pdo->prepare(
                        "INSERT INTO users (username, email, full_name, password, role, theme, access_status, theme_chosen)
                         VALUES (?, ?, ?, ?, ?, 'sweet', ?, 1)"
                    )->execute([$username, $newEmail, $fullName, $hash, $role, $access]);
                    $message = 'Created user @' . $username . ' (#' . (int) $pdo->lastInsertId() . ').';
                }
            } catch (Throwable $e) {
                $error = 'Create user failed: ' . $e->getMessage();
            }
            $tab = 'users';
        }
    } elseif ($action === 'user_edit') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        $roleIn = strtolower(trim((string) ($_POST['role'] ?? 'foh')));
        if ($roleIn === 'gm') {
            $roleIn = 'admin';
        }
        $res = pbj_admin_save_user_profile($pdo, $uid, [
            'full_name' => $_POST['full_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $roleIn,
            'access_status' => $_POST['access_status'] ?? 'approved',
            'new_password' => $_POST['new_password'] ?? '',
            'new_password_confirm' => $_POST['new_password_confirm'] ?? '',
        ]);
        if (!empty($res['ok'])) {
            $message = $res['message'] ?? 'Saved.';
            // Keep house membership role in sync with global role
            try {
                $memRole = in_array($roleIn, ['owner', 'manager', 'boh', 'foh', 'admin'], true) ? $roleIn : 'foh';
                $pdo->prepare('UPDATE user_restaurant SET role = ? WHERE user_id = ?')
                    ->execute([$memRole, $uid]);
            } catch (Throwable $e) {
                // best-effort
            }
        } else {
            $error = $res['error'] ?? 'Save failed.';
        }
        $editUserId = $uid;
        $tab = (string) ($_POST['return_tab'] ?? 'users');
        if (!in_array($tab, ['restaurants', 'users', 'individuals', 'sales'], true)) {
            $tab = 'users';
        }
    } elseif ($action === 'user_delete') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        $res = pbj_admin_delete_user_account($pdo, $uid, $actorId);
        if (!empty($res['ok'])) {
            $message = $res['message'] ?? 'Deleted.';
            if ($editUserId === $uid) {
                $editUserId = 0;
            }
        } else {
            $error = $res['error'] ?? 'Delete failed.';
        }
        $tab = (string) ($_POST['return_tab'] ?? 'users');
    } elseif ($action === 'user_reset') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        $res = pbj_admin_send_password_reset($pdo, $uid);
        if (!empty($res['ok'])) {
            $message = $res['message'] ?? 'Reset sent.';
            if (!empty($res['link'])) {
                $message .= ' Link: ' . $res['link'];
            }
        } else {
            $error = $res['error'] ?? 'Reset failed.';
        }
        $editUserId = $uid;
        $tab = (string) ($_POST['return_tab'] ?? 'users');
    } elseif ($action === 'sales_rep_save' && function_exists('pbj_sales_rep_save')) {
        $repId = (int) ($_POST['rep_id'] ?? 0);
        $res = pbj_sales_rep_save($pdo, [
            'name' => $_POST['name'] ?? '',
            'code' => $_POST['code'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'active' => !empty($_POST['active']),
        ], $repId);
        if (!empty($res['ok'])) {
            $message = $res['message'] ?? 'Sales rep saved.';
        } else {
            $error = $res['error'] ?? 'Could not save sales rep.';
        }
        $tab = 'sales';
    } elseif ($action === 'sales_reassign' && function_exists('pbj_sales_admin_reassign')) {
        $repId = (int) ($_POST['sales_rep_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $restaurantId = (int) ($_POST['restaurant_id'] ?? 0);
        $planId = (string) ($_POST['plan_id'] ?? 'crew_10');
        $res = pbj_sales_admin_reassign($pdo, $repId, $userId, $restaurantId, $planId);
        if (!empty($res['ok'])) {
            $message = $res['message'] ?? 'Attribution updated.';
        } else {
            $error = $res['error'] ?? 'Reassign failed.';
        }
        $tab = 'sales';
    } elseif ($action === 'sales_convert_playground' && function_exists('pbj_sales_convert_playground_user')) {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $repId = (int) ($_POST['sales_rep_id'] ?? 0);
        $planId = (string) ($_POST['plan_id'] ?? 'crew_10');
        $houseName = trim((string) ($_POST['restaurant_name'] ?? ''));
        $billingMode = (string) ($_POST['billing_mode'] ?? 'trial'); // trial | paid | none
        $res = pbj_sales_convert_playground_user($pdo, $userId, $repId, [
            'plan_id' => $planId,
            'restaurant_name' => $houseName,
            'start_trial' => $billingMode === 'trial',
            'mark_paid' => $billingMode === 'paid',
            'detach' => true,
        ]);
        if (!empty($res['ok'])) {
            $message = $res['message'] ?? 'Converted from playground.';
        } else {
            $error = $res['error'] ?? 'Convert failed.';
        }
        $tab = 'sales';
    }
}

// —— Load data ——
$allUsers = $pdo->query(
    'SELECT id, username, email, full_name, role, access_status, created_at, last_login_at
     FROM users ORDER BY id ASC'
)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$restaurants = $pdo->query(
    'SELECT r.id, r.name, r.owner_id, r.invite_code, r.created_at,
            u.username AS owner_username, u.full_name AS owner_name, u.email AS owner_email
     FROM restaurants r
     LEFT JOIN users u ON u.id = r.owner_id
     ORDER BY r.id ASC'
)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$settingsByRest = [];
try {
    $st = $pdo->query('SELECT restaurant_id, settings_json FROM restaurant_settings');
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $settingsByRest[(int) $row['restaurant_id']] = json_decode((string) $row['settings_json'], true) ?: [];
    }
} catch (Throwable $e) {
    // ignore
}

$membersByRest = [];
try {
    $st = $pdo->query(
        'SELECT ur.restaurant_id, ur.user_id, ur.role AS mem_role,
                u.username, u.email, u.full_name, u.access_status, u.role AS global_role
         FROM user_restaurant ur
         JOIN users u ON u.id = ur.user_id
         ORDER BY ur.restaurant_id, ur.joined_at ASC'
    );
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $rid = (int) $row['restaurant_id'];
        if (!isset($membersByRest[$rid])) {
            $membersByRest[$rid] = [];
        }
        $membersByRest[$rid][] = $row;
    }
} catch (Throwable $e) {
    // ignore
}

$userHouseMap = []; // user_id => list of restaurant names
foreach ($membersByRest as $rid => $mems) {
    $rname = '';
    foreach ($restaurants as $rr) {
        if ((int) $rr['id'] === (int) $rid) {
            $rname = (string) $rr['name'];
            break;
        }
    }
    foreach ($mems as $m) {
        $uid = (int) $m['user_id'];
        if (!isset($userHouseMap[$uid])) {
            $userHouseMap[$uid] = [];
        }
        $userHouseMap[$uid][] = $rname . ' (' . $m['mem_role'] . ')';
    }
}

$individuals = array_values(array_filter($allUsers, static function ($u) use ($userHouseMap) {
    return empty($userHouseMap[(int) $u['id']]);
}));

$plans = function_exists('pbj_plans') ? pbj_plans() : [];
$planOptions = [];
foreach ($plans as $p) {
    if (!empty($p['coming'])) {
        continue;
    }
    $planOptions[$p['id']] = $p['name'] . ' · ' . $p['price'];
}
$billingOptions = [
    'lifetime_free' => 'Lifetime free',
    'paid' => 'Paid',
    'selected_unpaid' => 'Selected unpaid',
    'contact' => 'Custom / contact',
    'demo' => 'Demo',
];
// Must match users.role ENUM in MySQL
$roleOptions = ['owner', 'manager', 'foh', 'boh', 'admin'];
$accessOptions = ['approved', 'pending', 'blocked'];

$salesReport = function_exists('pbj_sales_commission_report')
    ? pbj_sales_commission_report($pdo)
    : ['reps' => [], 'attributions' => [], 'events' => [], 'rates' => [], 'totals' => []];
$playgroundGuests = function_exists('pbj_sales_list_playground_guests')
    ? pbj_sales_list_playground_guests($pdo)
    : [];
$editRepId = isset($_GET['edit_rep']) ? (int) $_GET['edit_rep'] : 0;
$editRep = null;
if ($editRepId > 0 && function_exists('pbj_sales_rep_by_id')) {
    $editRep = pbj_sales_rep_by_id($pdo, $editRepId);
}

function h($s): string {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// Match active theme (fonts, colors) for platform admin comfort
$__tf = function_exists('pbj_theme_flags') ? pbj_theme_flags() : [];
$theme_id = function_exists('pbj_theme_id') ? pbj_theme_id() : 'sweet';
$body_class = function_exists('pbj_theme_body_class') ? pbj_theme_body_class() : ('pbj-theme-' . $theme_id);
$tokens = function_exists('pbj_theme_tokens') ? pbj_theme_tokens() : [];
$pageBg = $tokens['page_bg'] ?? '#FCF8EE';
$text = $tokens['text'] ?? '#3a2f1f';
$cardBg = $tokens['card_bg'] ?? '#ffffff';
$cardBorder = $tokens['card_border'] ?? '#F3E8DD';
$headerBg = $tokens['header_bg'] ?? '#E55163';
$headerText = $tokens['header_text'] ?? '#ffffff';
$primary = $tokens['primary'] ?? '#E55163';
$primaryText = $tokens['primary_text'] ?? '#ffffff';
$secondaryBg = $tokens['secondary_bg'] ?? '#E8F7F2';
$secondaryText = $tokens['secondary_text'] ?? '#3a2f1f';
$accent = $tokens['accent'] ?? '#BBE7DA';
$inputBg = $tokens['input_bg'] ?? '#FFFBF8';
$inputBorder = $tokens['input_border'] ?? '#F3C5CC';
$fontBody = $tokens['font_body'] ?? "Georgia, serif";
$fontDisplay = $tokens['font_display'] ?? "Georgia, serif";
$dangerBg = $tokens['danger_bg'] ?? '#FDECEA';
$dangerText = $tokens['danger_text'] ?? '#B71C1C';
$isDark = !empty($tokens['dark']);
$mutedCard = $isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.03)';
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo h($body_class); ?>" data-theme="<?php echo h($theme_id); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="googlebot" content="noindex, nofollow">
    <title>Platform console · private</title>
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php
    if (!empty($__tf['neon']) && function_exists('pbj_render_neon_font_faces')) { pbj_render_neon_font_faces(); }
    if (!empty($__tf['farm']) && function_exists('pbj_render_farm_font_faces')) { pbj_render_farm_font_faces(); }
    if (!empty($__tf['urban']) && function_exists('pbj_render_urban_font_faces')) { pbj_render_urban_font_faces(); }
    if (!empty($__tf['coffee']) && function_exists('pbj_render_coffee_font_faces')) { pbj_render_coffee_font_faces(); }
    if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(true); }
    ?>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding-bottom: 48px;
            font-family: <?php echo $fontBody; ?>;
            background: <?php echo h($pageBg); ?>;
            color: <?php echo h($text); ?>;
        }
        .top {
            background: <?php echo h($headerBg); ?>;
            color: <?php echo h($headerText); ?>;
            padding: 16px 18px 20px; text-align: center;
            border-bottom: 3px solid <?php echo h($accent); ?>;
        }
        .top a { color: <?php echo h($accent); ?>; text-decoration: none; font-size: 0.92rem; }
        h1 {
            margin: 8px 0 4px; font-size: 1.75rem; letter-spacing: 0.02em;
            font-family: <?php echo $fontDisplay; ?>;
            color: <?php echo h($headerText); ?>;
        }
        .sub { opacity: 0.9; font-size: 0.92rem; margin: 0; color: <?php echo h($headerText); ?>; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 18px 14px; }
        .lock {
            background: <?php echo h($cardBg); ?>;
            border: 2px solid <?php echo h($cardBorder); ?>;
            border-radius: 14px;
            padding: 12px 14px; margin-bottom: 14px; font-size: 0.92rem; line-height: 1.45;
        }
        .hint { font-size: 0.9rem; opacity: 0.78; line-height: 1.4; }
        .pill {
            display: inline-block; border-radius: 999px; padding: 2px 10px; font-size: 0.82rem;
            background: <?php echo h($secondaryBg); ?>; color: <?php echo h($secondaryText); ?>;
            margin-left: 4px;
        }
        .tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .tab {
            padding: 10px 14px; border-radius: 999px; text-decoration: none;
            color: <?php echo h($text); ?>;
            background: <?php echo h($cardBg); ?>;
            border: 2px solid <?php echo h($cardBorder); ?>;
            font-size: 0.95rem;
            font-family: inherit;
        }
        .tab.active {
            background: <?php echo h($primary); ?>;
            border-color: <?php echo h($primary); ?>;
            color: <?php echo h($primaryText); ?>;
        }
        .msg {
            background: <?php echo h($secondaryBg); ?>;
            color: <?php echo h($secondaryText); ?>;
            border-radius: 12px; padding: 10px 14px; margin-bottom: 12px;
            border: 1px solid <?php echo h($cardBorder); ?>;
        }
        .err {
            background: <?php echo h($dangerBg); ?>;
            color: <?php echo h($dangerText); ?>;
            border-radius: 12px; padding: 10px 14px; margin-bottom: 12px;
        }
        .card {
            background: <?php echo h($cardBg); ?>;
            border: 2px solid <?php echo h($cardBorder); ?>;
            border-radius: 16px;
            padding: 16px; margin-bottom: 12px;
            color: <?php echo h($text); ?>;
        }
        .card h2 {
            margin: 0 0 10px; font-size: 1.2rem;
            font-family: <?php echo $fontDisplay; ?>;
            color: <?php echo h($primary); ?>;
        }
        .card h3 {
            margin: 0 0 6px; font-size: 1.08rem;
            font-family: <?php echo $fontDisplay; ?>;
            color: <?php echo h($text); ?>;
        }
        .meta { font-size: 0.88rem; opacity: 0.85; line-height: 1.45; }
        .badge {
            display: inline-block; border-radius: 999px; padding: 2px 9px; font-size: 0.75rem;
            background: <?php echo h($secondaryBg); ?>;
            color: <?php echo h($secondaryText); ?>;
            margin-left: 4px;
        }
        .badge.paid { background: <?php echo h($secondaryBg); ?>; color: <?php echo h($primary); ?>; }
        .badge.free { background: <?php echo h($secondaryBg); ?>; }
        .badge.warn { background: #FFF4E0; color: #8A5A00; }
        label { display: block; font-size: 0.82rem; opacity: 0.75; margin: 8px 0 4px; }
        input, select {
            width: 100%; max-width: 420px; padding: 9px 11px; border-radius: 10px;
            border: 2px solid <?php echo h($inputBorder); ?>;
            background: <?php echo h($inputBg); ?>;
            color: <?php echo h($text); ?>;
            font-family: inherit; font-size: 0.98rem;
        }
        .row { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
        .row .field { flex: 1; min-width: 140px; }
        .row .field input, .row .field select { max-width: none; }
        .btn {
            border: none; border-radius: 11px; padding: 9px 14px; cursor: pointer;
            font-family: inherit; font-size: 0.92rem; margin-top: 8px; margin-right: 6px;
            text-decoration: none; display: inline-block;
        }
        .btn-primary { background: <?php echo h($primary); ?>; color: <?php echo h($primaryText); ?>; }
        .btn-mint { background: <?php echo h($secondaryBg); ?>; color: <?php echo h($secondaryText); ?>; border: 1px solid <?php echo h($cardBorder); ?>; }
        .btn-ghost { background: <?php echo $mutedCard; ?>; color: <?php echo h($text); ?>; border: 1px solid <?php echo h($cardBorder); ?>; }
        .btn-danger { background: <?php echo h($dangerBg); ?>; color: <?php echo h($dangerText); ?>; }
        .member {
            border-top: 1px solid <?php echo h($cardBorder); ?>;
            padding: 10px 0; display: flex; flex-wrap: wrap;
            gap: 8px; justify-content: space-between; align-items: center;
        }
        .member:first-of-type { border-top: none; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: <?php echo h($primary); ?>; margin: 0 10px; font-weight: 600; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-width: 640px; }
        @media (max-width: 640px) { .grid2 { grid-template-columns: 1fr; } }
        details.edit { margin-top: 10px; }
        details.edit summary { cursor: pointer; color: <?php echo h($primary); ?>; font-weight: 600; }
        .count { opacity: 0.7; font-size: 0.9rem; }
        code { font-size: 0.9em; }
    </style>
</head>
<body class="<?php echo h($body_class); ?>" data-theme="<?php echo h($theme_id); ?>">
    <div class="top">
        <a href="/home">← Hub</a>
        · <a href="/approve-users">User desk</a>
        <h1>Platform console</h1>
        <p class="sub">Private · platform admin only · theme: <?php echo h(function_exists('pbj_theme_display_name') ? pbj_theme_display_name() : $theme_id); ?></p>
    </div>
    <div class="wrap">
        <div class="lock">
            🔒 Only your platform-admin emails can open this page. Everyone else gets a blank “Private” screen.
            Search engines are told not to index it. Path: <code>/platform</code>
        </div>

        <?php if ($message): ?><div class="msg"><?php echo h($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="err"><?php echo h($error); ?></div><?php endif; ?>

        <div class="tabs">
            <a class="tab<?php echo $tab === 'restaurants' ? ' active' : ''; ?>" href="/platform?tab=restaurants">Restaurants <span class="count">(<?php echo count($restaurants); ?>)</span></a>
            <a class="tab<?php echo $tab === 'users' ? ' active' : ''; ?>" href="/platform?tab=users">All users <span class="count">(<?php echo count($allUsers); ?>)</span></a>
            <a class="tab<?php echo $tab === 'individuals' ? ' active' : ''; ?>" href="/platform?tab=individuals">Individuals <span class="count">(<?php echo count($individuals); ?>)</span></a>
            <a class="tab<?php echo $tab === 'sales' ? ' active' : ''; ?>" href="/platform?tab=sales">Sales &amp; commissions <span class="count">(<?php echo count($salesReport['reps'] ?? []); ?>)</span></a>
        </div>

        <?php if ($tab === 'sales'): ?>
            <?php
            $rates = $salesReport['rates'] ?? (function_exists('pbj_commission_rates') ? pbj_commission_rates() : []);
            $fmt = static function (int $c): string {
                return function_exists('pbj_format_money_cents') ? pbj_format_money_cents($c) : ('$' . number_format($c / 100, 2));
            };
            ?>
            <div class="card">
                <h2>Commission rules</h2>
                <p class="hint" style="margin-top:0;">Locked in for v1 — products marked “coming” store the rate but don’t auto-pay until those products go live.</p>
                <ul style="margin:8px 0 0;padding-left:1.2rem;line-height:1.55;">
                    <?php foreach ($rates as $key => $rate): ?>
                        <li>
                            <strong><?php echo h($rate['label'] ?? $key); ?></strong>
                            — <?php echo h((string) ($rate['rate_pct'] ?? 0)); ?>%
                            (<?php echo h((string) ($rate['kind'] ?? '')); ?>)
                            <?php if (empty($rate['active'])): ?>
                                <span class="pill" style="background:#FFF3D6;color:#8A5A00;">coming soon</span>
                            <?php else: ?>
                                <span class="pill" style="background:#E8F8F1;color:#1F6B4A;">live</span>
                            <?php endif; ?>
                            <div class="hint" style="margin:2px 0 8px;"><?php echo h((string) ($rate['note'] ?? '')); ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h2><?php echo $editRep ? 'Edit sales rep' : 'Add sales rep'; ?></h2>
                <form method="POST" action="/platform?tab=sales">
                    <input type="hidden" name="action" value="sales_rep_save">
                    <input type="hidden" name="rep_id" value="<?php echo (int) ($editRep['id'] ?? 0); ?>">
                    <div class="grid2">
                        <div class="field">
                            <label>Name</label>
                            <input name="name" required placeholder="Jordan Lee" value="<?php echo h($editRep['name'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label>Sales code</label>
                            <input name="code" required placeholder="JORDAN" style="text-transform:uppercase;letter-spacing:0.05em;"
                                value="<?php echo h($editRep['code'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo h($editRep['email'] ?? ''); ?>" placeholder="rep@…">
                        </div>
                        <div class="field">
                            <label>Phone</label>
                            <input name="phone" value="<?php echo h($editRep['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label>Notes</label>
                        <input name="notes" value="<?php echo h($editRep['notes'] ?? ''); ?>" placeholder="Territory, team, …">
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;margin:8px 0 12px;">
                        <input type="checkbox" name="active" value="1"<?php echo !$editRep || !empty($editRep['active']) ? ' checked' : ''; ?>>
                        Active (can receive new attributions)
                    </label>
                    <button class="btn" type="submit"><?php echo $editRep ? 'Save rep' : 'Add rep'; ?></button>
                    <?php if ($editRep): ?>
                        <a class="btn btn-ghost" href="/platform?tab=sales">Cancel edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h2>Reps &amp; signup links</h2>
                <?php if (empty($salesReport['reps'])): ?>
                    <p class="hint">No sales reps yet — add one above. Share their link: <code>/register?ref=THEIRCODE</code></p>
                <?php else: ?>
                    <?php foreach ($salesReport['totals'] as $t):
                        $rep = $t['rep'];
                        $code = (string) ($rep['code'] ?? '');
                        $link = function_exists('pbj_sales_signup_url') ? pbj_sales_signup_url($code) : ('/register?ref=' . rawurlencode($code));
                        ?>
                        <div style="border:1px solid <?php echo h($cardBorder); ?>;border-radius:14px;padding:12px 14px;margin:0 0 10px;background:<?php echo h($mutedCard); ?>;">
                            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between;">
                                <div>
                                    <strong><?php echo h($rep['name'] ?? ''); ?></strong>
                                    <span class="pill"><?php echo h($code); ?></span>
                                    <?php if (empty($rep['active'])): ?>
                                        <span class="pill" style="background:#FDECEA;color:#B71C1C;">inactive</span>
                                    <?php endif; ?>
                                    <?php if (!empty($rep['email'])): ?>
                                        <div class="hint" style="margin:4px 0 0;"><?php echo h($rep['email']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <a class="btn btn-ghost" href="/platform?tab=sales&amp;edit_rep=<?php echo (int) $rep['id']; ?>">Edit</a>
                            </div>
                            <p class="hint" style="margin:10px 0 4px;word-break:break-all;">
                                Link: <a href="<?php echo h($link); ?>" target="_blank" rel="noopener"><?php echo h($link); ?></a>
                            </p>
                            <p class="hint" style="margin:0;">
                                Attributions: <strong><?php echo (int) $t['attributions']; ?></strong>
                                · trial <strong><?php echo (int) $t['trialing']; ?></strong>
                                · paid <strong><?php echo (int) $t['paid']; ?></strong>
                                · est. monthly commission <strong><?php echo h($fmt((int) $t['est_monthly_comm_cents'])); ?></strong>
                                · ledger earned <strong><?php echo h($fmt((int) $t['earned_comm_cents'])); ?></strong>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Playground → Sales &amp; commissions</h2>
                <p class="hint" style="margin-top:0;">
                    Guests on <strong>DEMO-PBJ</strong> / <strong>SALES-PBJ</strong> (and other playgrounds).
                    Convert them to a real house (or Individual), credit a sales rep, start a free trial (or mark paid),
                    and remove their playground seats so they live on the commission side.
                </p>
                <?php if (empty($playgroundGuests)): ?>
                    <p class="hint">No playground guests right now (owners &amp; platform admins are hidden).</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                            <thead>
                                <tr style="text-align:left;border-bottom:2px solid <?php echo h($cardBorder); ?>;">
                                    <th style="padding:6px;">Person</th>
                                    <th style="padding:6px;">Playground(s)</th>
                                    <th style="padding:6px;">Real house?</th>
                                    <th style="padding:6px;">Convert</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($playgroundGuests as $g):
                                $uid = (int) $g['user_id'];
                                $display = trim((string) ($g['full_name'] ?: $g['username'] ?: ('#' . $uid)));
                                $defaultHouse = $g['real_house_name'] !== ''
                                    ? (string) $g['real_house_name']
                                    : ($display . "'s Kitchen");
                                ?>
                                <tr style="border-bottom:1px solid <?php echo h($cardBorder); ?>;vertical-align:top;">
                                    <td style="padding:8px 6px;">
                                        <strong><?php echo h($display); ?></strong>
                                        <div class="hint">#<?php echo $uid; ?> · <?php echo h($g['username']); ?></div>
                                        <div class="hint"><?php echo h($g['email']); ?></div>
                                        <?php if (!empty($g['already_attributed'])): ?>
                                            <span class="pill">already · <?php echo h((string) $g['sales_code']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:8px 6px;">
                                        <?php foreach ($g['playgrounds'] as $pg): ?>
                                            <div class="hint"><?php echo h($pg); ?></div>
                                        <?php endforeach; ?>
                                        <?php if (!empty($g['joined_at'])): ?>
                                            <div class="hint">joined <?php echo h(substr((string) $g['joined_at'], 0, 10)); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:8px 6px;">
                                        <?php if (!empty($g['has_real_house'])): ?>
                                            <span class="pill" style="background:#E8F8F1;color:#1F6B4A;">yes · #<?php echo (int) $g['real_house_id']; ?></span>
                                            <div class="hint"><?php echo h($g['real_house_name']); ?></div>
                                        <?php else: ?>
                                            <span class="pill" style="background:#FFF3D6;color:#8A5A00;">playground only</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:8px 6px;min-width:260px;">
                                        <form method="POST" action="/platform?tab=sales" style="margin:0;">
                                            <input type="hidden" name="action" value="sales_convert_playground">
                                            <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                                            <div class="field" style="margin-bottom:6px;">
                                                <label>Sales rep</label>
                                                <select name="sales_rep_id" required>
                                                    <option value="">— pick rep —</option>
                                                    <?php foreach ($salesReport['reps'] as $rep): if (empty($rep['active'])) continue; ?>
                                                        <option value="<?php echo (int) $rep['id']; ?>"><?php echo h($rep['name'] . ' · ' . $rep['code']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="field" style="margin-bottom:6px;">
                                                <label>Plan</label>
                                                <select name="plan_id">
                                                    <?php foreach ($planOptions as $pid => $plabel): ?>
                                                        <option value="<?php echo h($pid); ?>"<?php echo $pid === 'crew_10' ? ' selected' : ''; ?>><?php echo h($plabel); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php if (empty($g['has_real_house'])): ?>
                                            <div class="field" style="margin-bottom:6px;">
                                                <label>New house name</label>
                                                <input name="restaurant_name" value="<?php echo h($defaultHouse); ?>" placeholder="Restaurant name">
                                            </div>
                                            <?php else: ?>
                                                <input type="hidden" name="restaurant_name" value="<?php echo h($defaultHouse); ?>">
                                            <?php endif; ?>
                                            <div class="field" style="margin-bottom:6px;">
                                                <label>Billing</label>
                                                <select name="billing_mode">
                                                    <option value="trial" selected>Start free trial</option>
                                                    <option value="paid">Mark paid (no Stripe)</option>
                                                    <option value="none">Credit only (no trial change)</option>
                                                </select>
                                            </div>
                                            <button class="btn" type="submit" style="margin-top:4px;" onclick="return confirm('Convert this playground guest to Sales &amp; commissions? They leave the playground.');">
                                                Convert to sales
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Manual attribution</h2>
                <p class="hint" style="margin-top:0;">If someone forgot a code, assign the house (or solo user) to a rep here. First attribution wins for self-serve signups.</p>
                <form method="POST" action="/platform?tab=sales">
                    <input type="hidden" name="action" value="sales_reassign">
                    <div class="grid2">
                        <div class="field">
                            <label>Sales rep</label>
                            <select name="sales_rep_id" required>
                                <option value="">— pick —</option>
                                <?php foreach ($salesReport['reps'] as $rep): if (empty($rep['active'])) continue; ?>
                                    <option value="<?php echo (int) $rep['id']; ?>"><?php echo h($rep['name'] . ' · ' . $rep['code']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Restaurant (house)</label>
                            <select name="restaurant_id">
                                <option value="0">— none / individual —</option>
                                <?php foreach ($restaurants as $rr): ?>
                                    <option value="<?php echo (int) $rr['id']; ?>">
                                        #<?php echo (int) $rr['id']; ?> <?php echo h($rr['name']); ?>
                                        (owner #<?php echo (int) $rr['owner_id']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Owner user id (required for individual)</label>
                            <input name="user_id" type="number" min="0" placeholder="User id" value="">
                        </div>
                        <div class="field">
                            <label>Plan id</label>
                            <select name="plan_id">
                                <?php foreach ($planOptions as $pid => $plabel): ?>
                                    <option value="<?php echo h($pid); ?>"><?php echo h($plabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn" type="submit">Assign credit</button>
                </form>
            </div>

            <div class="card">
                <h2>Attributions</h2>
                <?php if (empty($salesReport['attributions'])): ?>
                    <p class="hint">None yet. When a restaurant registers with a sales code (or link), they show up here.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.92rem;">
                            <thead>
                                <tr style="text-align:left;border-bottom:2px solid <?php echo h($cardBorder); ?>;">
                                    <th style="padding:6px;">When</th>
                                    <th style="padding:6px;">Rep</th>
                                    <th style="padding:6px;">House / owner</th>
                                    <th style="padding:6px;">Plan</th>
                                    <th style="padding:6px;">Status</th>
                                    <th style="padding:6px;">Est. /mo commission</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($salesReport['attributions'] as $a):
                                $base = (int) ($a['monthly_plan_cents'] ?? 0);
                                if ($base <= 0) {
                                    $base = function_exists('pbj_plan_price_cents') ? pbj_plan_price_cents((string) ($a['plan_id'] ?? '')) : 0;
                                }
                                $comm = function_exists('pbj_commission_cents')
                                    ? pbj_commission_cents($base, function_exists('pbj_commission_rate_pct') ? pbj_commission_rate_pct('subscription') : 30)
                                    : (int) round($base * 0.30);
                                $house = trim((string) ($a['restaurant_name'] ?? ''));
                                if ($house === '') {
                                    $house = 'Individual · ' . ($a['owner_name'] ?: $a['owner_username'] ?: ('user #' . $a['user_id']));
                                } else {
                                    $house .= ' · ' . ($a['owner_email'] ?: $a['owner_username'] ?: '');
                                }
                                ?>
                                <tr style="border-bottom:1px solid <?php echo h($cardBorder); ?>;">
                                    <td style="padding:6px;white-space:nowrap;"><?php echo h(substr((string) ($a['attributed_at'] ?? ''), 0, 16)); ?></td>
                                    <td style="padding:6px;"><span class="pill"><?php echo h($a['rep_code'] ?? ''); ?></span></td>
                                    <td style="padding:6px;"><?php echo h($house); ?></td>
                                    <td style="padding:6px;"><?php echo h((string) ($a['plan_id'] ?? '')); ?></td>
                                    <td style="padding:6px;"><?php echo h((string) ($a['status'] ?? '')); ?></td>
                                    <td style="padding:6px;"><?php echo h($fmt($comm)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Commission ledger</h2>
                <p class="hint" style="margin-top:0;">Written when a referred account pays (Stripe) and again each month on <code>invoice.paid</code>. Startup / butler rows will appear when those products bill.</p>
                <?php if (empty($salesReport['events'])): ?>
                    <p class="hint">No commission events yet.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.92rem;">
                            <thead>
                                <tr style="text-align:left;border-bottom:2px solid <?php echo h($cardBorder); ?>;">
                                    <th style="padding:6px;">When</th>
                                    <th style="padding:6px;">Rep</th>
                                    <th style="padding:6px;">Product</th>
                                    <th style="padding:6px;">Period</th>
                                    <th style="padding:6px;">Base</th>
                                    <th style="padding:6px;">Rate</th>
                                    <th style="padding:6px;">Commission</th>
                                    <th style="padding:6px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($salesReport['events'] as $e): ?>
                                <tr style="border-bottom:1px solid <?php echo h($cardBorder); ?>;">
                                    <td style="padding:6px;white-space:nowrap;"><?php echo h(substr((string) ($e['created_at'] ?? ''), 0, 16)); ?></td>
                                    <td style="padding:6px;"><?php echo h($e['rep_code'] ?? ''); ?></td>
                                    <td style="padding:6px;"><?php echo h($e['product'] ?? ''); ?></td>
                                    <td style="padding:6px;"><?php echo h((string) ($e['period_ym'] ?? '—')); ?></td>
                                    <td style="padding:6px;"><?php echo h($fmt((int) ($e['base_amount_cents'] ?? 0))); ?></td>
                                    <td style="padding:6px;"><?php echo h((string) ($e['rate_pct'] ?? '')); ?>%</td>
                                    <td style="padding:6px;"><strong><?php echo h($fmt((int) ($e['commission_cents'] ?? 0))); ?></strong></td>
                                    <td style="padding:6px;"><?php echo h((string) ($e['status'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($tab === 'restaurants'): ?>
            <div class="card">
                <h2>Add restaurant</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="rest_create">
                    <div class="grid2">
                        <div class="field">
                            <label>House name</label>
                            <input name="name" required placeholder="e.g. Downtown Bistro">
                        </div>
                        <div class="field">
                            <label>Owner account</label>
                            <select name="owner_id" required>
                                <option value="">Select user…</option>
                                <?php foreach ($allUsers as $u): ?>
                                <option value="<?php echo (int) $u['id']; ?>">
                                    #<?php echo (int) $u['id']; ?> · <?php echo h($u['full_name'] ?: $u['username']); ?> (@<?php echo h($u['username']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Plan</label>
                            <select name="plan_id">
                                <?php foreach ($planOptions as $pid => $plabel): ?>
                                <option value="<?php echo h($pid); ?>"<?php echo $pid === 'crew_10' ? ' selected' : ''; ?>><?php echo h($plabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Billing</label>
                            <select name="billing_status">
                                <?php foreach ($billingOptions as $bk => $bl): ?>
                                <option value="<?php echo h($bk); ?>"<?php echo $bk === 'lifetime_free' ? ' selected' : ''; ?>><?php echo h($bl); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Create restaurant</button>
                </form>
            </div>

            <?php foreach ($restaurants as $r):
                $rid = (int) $r['id'];
                $set = $settingsByRest[$rid] ?? [];
                $plan = (string) ($set['plan'] ?? '—');
                $billing = (string) ($set['billing_status'] ?? ($set['billing']['status'] ?? '—'));
                $members = $membersByRest[$rid] ?? [];
                $isEdit = ($editRestId === $rid);
            ?>
            <div class="card" id="rest-<?php echo $rid; ?>">
                <h3>
                    <?php echo h($r['name']); ?>
                    <span class="badge">#<?php echo $rid; ?></span>
                    <span class="badge"><?php echo h($r['invite_code']); ?></span>
                    <?php if ($billing === 'lifetime_free' || $billing === 'demo'): ?>
                        <span class="badge free"><?php echo h($billing); ?></span>
                    <?php elseif ($billing === 'paid'): ?>
                        <span class="badge paid">paid</span>
                    <?php else: ?>
                        <span class="badge warn"><?php echo h($billing); ?></span>
                    <?php endif; ?>
                </h3>
                <div class="meta">
                    Plan: <strong><?php echo h($plan); ?></strong>
                    · Owner: #<?php echo (int) $r['owner_id']; ?>
                    <?php echo h($r['owner_name'] ?: $r['owner_username'] ?: '—'); ?>
                    (<?php echo h($r['owner_email'] ?? ''); ?>)
                    · Members: <?php echo count($members); ?>
                    · Created <?php echo h((string) $r['created_at']); ?>
                </div>

                <h2 style="margin-top:14px;font-size:1rem;">Users on this house</h2>
                <?php if (!$members): ?>
                    <p class="meta">No members linked.</p>
                <?php else: ?>
                    <?php foreach ($members as $m): ?>
                    <div class="member">
                        <div class="meta">
                            <strong><?php echo h($m['full_name'] ?: $m['username']); ?></strong>
                            · @<?php echo h($m['username']); ?>
                            · <?php echo h($m['email']); ?><br>
                            House role: <strong><?php echo h($m['mem_role']); ?></strong>
                            · Access: <?php echo h($m['access_status']); ?>
                            · Global: <?php echo h($m['global_role']); ?>
                        </div>
                        <div>
                            <a class="btn btn-ghost" href="/platform?tab=users&edit_user=<?php echo (int) $m['user_id']; ?>#user-<?php echo (int) $m['user_id']; ?>">Edit user</a>
                            <?php if ((int) $m['user_id'] !== (int) $r['owner_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this user from the house?');">
                                <input type="hidden" name="action" value="rest_remove_member">
                                <input type="hidden" name="restaurant_id" value="<?php echo $rid; ?>">
                                <input type="hidden" name="user_id" value="<?php echo (int) $m['user_id']; ?>">
                                <button class="btn btn-danger" type="submit">Remove</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="POST" style="margin-top:12px;">
                    <input type="hidden" name="action" value="rest_add_member">
                    <input type="hidden" name="restaurant_id" value="<?php echo $rid; ?>">
                    <div class="row">
                        <div class="field">
                            <label>Add existing user</label>
                            <select name="user_id" required>
                                <option value="">Select…</option>
                                <?php foreach ($allUsers as $u): ?>
                                <option value="<?php echo (int) $u['id']; ?>">#<?php echo (int) $u['id']; ?> @<?php echo h($u['username']); ?> — <?php echo h($u['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field" style="max-width:140px;">
                            <label>Role</label>
                            <select name="role">
                                <?php foreach (['foh','boh','manager','owner','admin'] as $rr): ?>
                                <option value="<?php echo $rr; ?>"><?php echo $rr === 'admin' ? 'admin (GM)' : $rr; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-mint" type="submit">Add to house</button>
                    </div>
                </form>

                <details class="edit"<?php echo $isEdit ? ' open' : ''; ?>>
                    <summary>Edit restaurant</summary>
                    <form method="POST" style="margin-top:10px;">
                        <input type="hidden" name="action" value="rest_update">
                        <input type="hidden" name="restaurant_id" value="<?php echo $rid; ?>">
                        <div class="grid2">
                            <div class="field">
                                <label>Name</label>
                                <input name="name" required value="<?php echo h($r['name']); ?>">
                            </div>
                            <div class="field">
                                <label>Invite code</label>
                                <input name="invite_code" required value="<?php echo h($r['invite_code']); ?>" style="text-transform:uppercase;">
                            </div>
                            <div class="field">
                                <label>Owner</label>
                                <select name="owner_id" required>
                                    <?php foreach ($allUsers as $u): ?>
                                    <option value="<?php echo (int) $u['id']; ?>"<?php echo (int) $u['id'] === (int) $r['owner_id'] ? ' selected' : ''; ?>>
                                        #<?php echo (int) $u['id']; ?> @<?php echo h($u['username']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label>Plan</label>
                                <select name="plan_id">
                                    <?php foreach ($planOptions as $pid => $plabel): ?>
                                    <option value="<?php echo h($pid); ?>"<?php echo $plan === $pid ? ' selected' : ''; ?>><?php echo h($plabel); ?></option>
                                    <?php endforeach; ?>
                                    <?php if ($plan && !isset($planOptions[$plan])): ?>
                                    <option value="<?php echo h($plan); ?>" selected><?php echo h($plan); ?> (current)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label>Billing</label>
                                <select name="billing_status">
                                    <?php foreach ($billingOptions as $bk => $bl): ?>
                                    <option value="<?php echo h($bk); ?>"<?php echo $billing === $bk ? ' selected' : ''; ?>><?php echo h($bl); ?></option>
                                    <?php endforeach; ?>
                                    <?php if ($billing && !isset($billingOptions[$billing])): ?>
                                    <option value="<?php echo h($billing); ?>" selected><?php echo h($billing); ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">Save restaurant</button>
                    </form>
                    <form method="POST" style="margin-top:8px;" onsubmit="return confirm('DELETE this restaurant and all memberships/shared data? This cannot be undone.');">
                        <input type="hidden" name="action" value="rest_delete">
                        <input type="hidden" name="restaurant_id" value="<?php echo $rid; ?>">
                        <button class="btn btn-danger" type="submit">Delete restaurant</button>
                    </form>
                </details>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php
        $userList = $tab === 'individuals' ? $individuals : $allUsers;
        if ($tab === 'users' || $tab === 'individuals'):
        ?>
            <div class="card">
                <h2>Add user</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="user_create">
                    <div class="grid2">
                        <div class="field">
                            <label>Full name</label>
                            <input name="full_name" required>
                        </div>
                        <div class="field">
                            <label>Username</label>
                            <input name="username" required autocomplete="off">
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" required autocomplete="off">
                        </div>
                        <div class="field">
                            <label>Password</label>
                            <input type="password" name="password" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="field">
                            <label>Role</label>
                            <select name="role">
                                <?php foreach ($roleOptions as $ro): ?>
                                <option value="<?php echo $ro; ?>"<?php echo $ro === 'foh' ? ' selected' : ''; ?>><?php echo $ro; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Access</label>
                            <select name="access_status">
                                <?php foreach ($accessOptions as $ao): ?>
                                <option value="<?php echo $ao; ?>"<?php echo $ao === 'approved' ? ' selected' : ''; ?>><?php echo $ao; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Create user</button>
                </form>
            </div>

            <?php if (!$userList): ?>
                <div class="card"><p class="meta">No accounts in this list.</p></div>
            <?php endif; ?>

            <?php foreach ($userList as $u):
                $uid = (int) $u['id'];
                $houses = $userHouseMap[$uid] ?? [];
                $isEdit = ($editUserId === $uid);
                $isSelf = ($uid === $actorId);
                $isPlat = pbj_is_platform_admin((string) $u['email']);
                $allowPw = function_exists('pbj_user_allows_direct_password_set') && pbj_user_allows_direct_password_set($pdo, $uid);
            ?>
            <div class="card" id="user-<?php echo $uid; ?>">
                <h3>
                    <?php echo h($u['full_name'] ?: $u['username']); ?>
                    <span class="badge">#<?php echo $uid; ?></span>
                    <span class="badge"><?php echo h($u['access_status']); ?></span>
                    <?php if ($isPlat): ?><span class="badge free">platform admin</span><?php endif; ?>
                </h3>
                <div class="meta">
                    @<?php echo h($u['username']); ?> · <?php echo h($u['email']); ?><br>
                    Role: <strong><?php echo h($u['role']); ?></strong>
                    · Joined <?php echo h((string) $u['created_at']); ?>
                    · Last login: <?php echo h($u['last_login_at'] ?: 'Never'); ?><br>
                    Houses:
                    <?php if ($houses): ?>
                        <?php echo h(implode(' · ', $houses)); ?>
                    <?php else: ?>
                        <em>none (individual)</em>
                    <?php endif; ?>
                </div>

                <div style="margin-top:10px;">
                    <a class="btn btn-ghost" href="/platform?tab=<?php echo h($tab); ?>&edit_user=<?php echo $uid; ?>#user-<?php echo $uid; ?>">Edit</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Email password reset?');">
                        <input type="hidden" name="action" value="user_reset">
                        <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                        <input type="hidden" name="return_tab" value="<?php echo h($tab); ?>">
                        <button class="btn btn-mint" type="submit">Password reset</button>
                    </form>
                    <?php if (!$isSelf && !$isPlat): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently DELETE this user?');">
                        <input type="hidden" name="action" value="user_delete">
                        <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                        <input type="hidden" name="return_tab" value="<?php echo h($tab); ?>">
                        <button class="btn btn-danger" type="submit">Delete user</button>
                    </form>
                    <?php endif; ?>
                </div>

                <?php if ($isEdit): ?>
                <form method="POST" action="/platform?tab=<?php echo h(urlencode($tab)); ?>" style="margin-top:14px;border-top:1px solid <?php echo h($cardBorder ?? '#3d3450'); ?>;padding-top:12px;">
                    <input type="hidden" name="action" value="user_edit">
                    <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                    <input type="hidden" name="return_tab" value="<?php echo h($tab); ?>">
                    <div class="grid2">
                        <div class="field">
                            <label>Full name</label>
                            <input name="full_name" required value="<?php echo h($u['full_name']); ?>">
                        </div>
                        <div class="field">
                            <label>Username</label>
                            <input name="username" required autocomplete="off" value="<?php echo h($u['username']); ?>">
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" required autocomplete="off" value="<?php echo h($u['email']); ?>">
                        </div>
                        <div class="field">
                            <label>Role</label>
                            <select name="role">
                                <?php
                                $curRole = (string) $u['role'];
                                if ($curRole === 'gm') {
                                    $curRole = 'admin';
                                }
                                foreach ($roleOptions as $ro):
                                ?>
                                <option value="<?php echo $ro; ?>"<?php echo $curRole === $ro ? ' selected' : ''; ?>><?php echo $ro === 'admin' ? 'admin (GM)' : $ro; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Access</label>
                            <select name="access_status">
                                <?php foreach ($accessOptions as $ao): ?>
                                <option value="<?php echo $ao; ?>"<?php echo $u['access_status'] === $ao ? ' selected' : ''; ?>><?php echo $ao; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if ($allowPw): ?>
                    <div class="grid2">
                        <div class="field">
                            <label>New password (optional)</label>
                            <input type="password" name="new_password" autocomplete="new-password" placeholder="Leave blank to keep">
                        </div>
                        <div class="field">
                            <label>Confirm password</label>
                            <input type="password" name="new_password_confirm" autocomplete="new-password" placeholder="Same as above">
                        </div>
                    </div>
                    <p class="meta">Playground / free / individual — direct password allowed (6+ characters if set).</p>
                    <?php else: ?>
                    <p class="meta">Paying-house account — use Password reset (no direct set).</p>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit">Save user</button>
                    <a class="btn btn-ghost" href="/platform?tab=<?php echo h($tab); ?>">Cancel</a>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="links">
            <a href="/home">Hub</a>
            <a href="/approve-users">User desk</a>
            <a href="/admin/onboarding">House logins</a>
            <a href="/logout">Log out</a>
        </div>
    </div>
</body>
</html>
