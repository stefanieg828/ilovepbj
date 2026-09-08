<?php
/**
 * Floor layout edit permissions for a restaurant.
 * GET  → { ok, canEdit, isOwner, role, allowedUserIds, allowedRoles, users[] }
 * POST → owner/admin only: { allowedUserIds: int[], allowedRoles?: string[] }
 */
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

function floor_ensure_settings_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_settings (
        restaurant_id INT NOT NULL PRIMARY KEY,
        settings_json LONGTEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function floor_resolve_restaurant_id(PDO $pdo): int {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid > 0) {
        try {
            $own = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ? ORDER BY id DESC LIMIT 1');
            $own->execute([$uid]);
            $ownedId = (int) ($own->fetchColumn() ?: 0);
            if ($ownedId > 0) {
                return $ownedId;
            }
        } catch (Exception $e) {
            // ignore
        }
        $stmt = $pdo->prepare('SELECT restaurant_id FROM user_restaurant WHERE user_id = ? ORDER BY joined_at ASC LIMIT 1');
        $stmt->execute([$uid]);
        $rid = $stmt->fetchColumn();
        if ($rid) {
            return (int) $rid;
        }
    }
    // Never fall back to DEMO playground for strangers
    if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
        $stmt = $pdo->query("SELECT id FROM restaurants WHERE invite_code = 'DEMO-PBJ' LIMIT 1");
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }
    }
    return 0;
}

function floor_load_settings(PDO $pdo, int $rid): array {
    $defaults = [
        'floorAllowedRoles' => ['owner', 'admin', 'manager'],
        'floorAllowedUserIds' => [],
    ];
    $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
    $stmt->execute([$rid]);
    $raw = $stmt->fetchColumn();
    if (!$raw) return $defaults;
    $j = json_decode($raw, true);
    if (!is_array($j)) return $defaults;
    return array_merge($defaults, $j);
}

function floor_save_settings(PDO $pdo, int $rid, array $settings): void {
    $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare(
        'INSERT INTO restaurant_settings (restaurant_id, settings_json) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE settings_json = VALUES(settings_json)'
    );
    $stmt->execute([$rid, $json]);
}

function floor_user_role(PDO $pdo, int $uid, int $rid): string {
    if ($uid <= 0) {
        return (string) ($_SESSION['role'] ?? 'admin');
    }
    $stmt = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
    $stmt->execute([$uid, $rid]);
    $r = $stmt->fetchColumn();
    if ($r) return (string) $r;
    return (string) ($_SESSION['role'] ?? 'boh');
}

function floor_can_edit(string $role, int $uid, array $settings): bool {
    // Playgrounds: every role may edit floor (demo / sales)
    if (!empty($settings['playground_reset'])
        || !empty($settings['sales_playground'])
        || !empty($settings['demo_house'])
        || !empty($settings['permissions']['playground_full_access'])) {
        return true;
    }
    $roles = $settings['floorAllowedRoles'] ?? ['owner', 'admin', 'manager'];
    if (!is_array($roles)) $roles = ['owner', 'admin', 'manager'];
    if (in_array($role, $roles, true)) return true;
    // gm aliases to admin in some membership rows
    if ($role === 'gm' && in_array('admin', $roles, true)) return true;
    if ($role === 'admin' && in_array('gm', $roles, true)) return true;
    // Always let owner/admin through even if list was wiped
    if (in_array($role, ['owner', 'admin', 'gm'], true)) return true;
    $ids = $settings['floorAllowedUserIds'] ?? [];
    if (!is_array($ids)) $ids = [];
    foreach ($ids as $id) {
        if ((int) $id === $uid) return true;
    }
    // Testing bypass
    if (defined('AUTH_BYPASS') && AUTH_BYPASS && $uid <= 0) return true;
    return false;
}

function floor_is_owner_like(string $role): bool {
    return in_array($role, ['owner', 'admin'], true);
}

try {
    floor_ensure_settings_table($pdo);
    $rid = floor_resolve_restaurant_id($pdo);
    $uid = (int) ($_SESSION['user_id'] ?? 0);

    if ($rid <= 0) {
        echo json_encode([
            'ok' => true,
            'restaurantId' => 0,
            'userId' => $uid,
            'role' => (string) ($_SESSION['role'] ?? 'foh'),
            'canEdit' => false,
            'isOwner' => false,
            'allowedRoles' => [],
            'allowedUserIds' => [],
            'users' => [],
            'localOnly' => true,
        ]);
        exit;
    }

    $role = floor_user_role($pdo, $uid, $rid);
    $settings = floor_load_settings($pdo, $rid);
    // Invite-code playgrounds always open floor edit even if settings lag
    $isPlayground = false;
    if (function_exists('pbj_restaurant_is_playground')) {
        $isPlayground = pbj_restaurant_is_playground($pdo, $rid);
    } else {
        try {
            $ic = $pdo->prepare('SELECT invite_code FROM restaurants WHERE id = ? LIMIT 1');
            $ic->execute([$rid]);
            $code = strtoupper(trim((string) $ic->fetchColumn()));
            $isPlayground = in_array($code, ['DEMO-PBJ', 'SALES-PBJ'], true);
        } catch (Throwable $e) {
            $isPlayground = false;
        }
    }
    $canEdit = $isPlayground || floor_can_edit($role, $uid, $settings);
    $isOwner = floor_is_owner_like($role) || $isPlayground;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $users = [];
        $stmt = $pdo->prepare(
            'SELECT u.id, u.username, u.full_name, ur.role
             FROM user_restaurant ur
             JOIN users u ON u.id = ur.user_id
             WHERE ur.restaurant_id = ?
             ORDER BY u.username'
        );
        $stmt->execute([$rid]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'ok' => true,
            'restaurantId' => $rid,
            'userId' => $uid,
            'role' => $role,
            'canEdit' => $canEdit,
            'isOwner' => $isOwner,
            'allowedRoles' => $settings['floorAllowedRoles'],
            'allowedUserIds' => array_map('intval', $settings['floorAllowedUserIds'] ?? []),
            'users' => $users,
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$isOwner) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_json']);
            exit;
        }
        if (isset($body['allowedUserIds']) && is_array($body['allowedUserIds'])) {
            $settings['floorAllowedUserIds'] = array_values(array_unique(array_map('intval', $body['allowedUserIds'])));
        }
        if (isset($body['allowedRoles']) && is_array($body['allowedRoles'])) {
            $clean = [];
            foreach ($body['allowedRoles'] as $r) {
                $r = strtolower(trim((string) $r));
                if (in_array($r, ['owner', 'admin', 'manager', 'foh', 'boh'], true)) {
                    $clean[] = $r;
                }
            }
            if (!in_array('owner', $clean, true)) $clean[] = 'owner';
            if (!in_array('admin', $clean, true)) $clean[] = 'admin';
            $settings['floorAllowedRoles'] = array_values(array_unique($clean));
        }
        floor_save_settings($pdo, $rid, $settings);
        echo json_encode([
            'ok' => true,
            'saved' => true,
            'allowedRoles' => $settings['floorAllowedRoles'],
            'allowedUserIds' => $settings['floorAllowedUserIds'],
            'canEdit' => floor_can_edit($role, $uid, $settings),
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server', 'message' => $e->getMessage()]);
}
