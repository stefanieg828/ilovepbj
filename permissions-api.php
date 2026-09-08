<?php
/**
 * Restaurant role permissions API.
 * GET  → { ok, role, roleLabel, grants, catalog?, matrix?, roles? }
 * POST → owner/gm with permissions_manage: { matrix: { role: { key: bool } } }
 */
require_once 'config.php';
require_once 'pbj-permissions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

try {
    $rid = pbj_permissions_resolve_restaurant_id($pdo);
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    $role = pbj_permissions_user_role($pdo, $uid, $rid);
    $rolesMeta = pbj_permission_roles();
    $matrix = pbj_permissions_load_matrix($pdo, $rid);
    $cur = pbj_permissions_current($pdo, true);
    $grants = $cur['grants'] ?? pbj_permissions_grants_for_role($matrix, $role);
    $omnipotent = !empty($cur['omnipotent']);
    $canManage = $omnipotent
        || !empty($grants['admin.team.permissions_manage'])
        || $role === 'owner'
        || $role === 'gm';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $out = [
            'ok' => true,
            'restaurantId' => $rid,
            'userId' => $uid,
            'role' => $role,
            'roleLabel' => $rolesMeta[$role] ?? $role,
            'grants' => $grants,
            'canManage' => $canManage,
            'omnipotent' => $omnipotent,
            'playground' => !empty($cur['playground']),
            'roles' => $rolesMeta,
        ];
        if ($canManage || isset($_GET['catalog'])) {
            $out['catalog'] = pbj_permission_catalog();
        }
        if ($canManage || isset($_GET['matrix'])) {
            $out['matrix'] = $matrix;
            $out['defaults'] = pbj_permissions_default_matrix();
        }
        // Always include catalog summary labels for "view own"
        if (isset($_GET['own']) || isset($_GET['labels'])) {
            $labels = [];
            foreach (pbj_permission_catalog() as $g) {
                foreach ($g['items'] as $it) {
                    $labels[$it['key']] = $g['label'] . ' — ' . $it['label'];
                }
            }
            $out['labels'] = $labels;
        }
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$canManage) {
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

        if (!empty($body['resetDefaults'])) {
            $saved = pbj_permissions_save_matrix($pdo, $rid, pbj_permissions_default_matrix());
        } elseif (isset($body['matrix']) && is_array($body['matrix'])) {
            $saved = pbj_permissions_save_matrix($pdo, $rid, $body['matrix']);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'need_matrix']);
            exit;
        }

        // Return grants for current user (omnipotent still full after save)
        $after = pbj_permissions_current($pdo);
        echo json_encode([
            'ok' => true,
            'saved' => true,
            'matrix' => $saved,
            'grants' => $after['grants'] ?? pbj_permissions_grants_for_role($saved, $role),
            'role' => $role,
            'omnipotent' => !empty($after['omnipotent']),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server', 'message' => $e->getMessage()]);
}
