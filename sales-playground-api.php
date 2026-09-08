<?php
/**
 * Playground meta + platform-admin controls (DEMO-PBJ + SALES-PBJ).
 * GET  → { playground_reset, sales_reset_epoch, … } for current user's house
 * POST action=reset|save_starters|ensure (platform admin only)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/pbj-permissions.php';
require_once __DIR__ . '/sales-playground.inc.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

$uid = (int) $_SESSION['user_id'];
$email = (string) ($_SESSION['email'] ?? '');

// Prefer a playground house the user belongs to
$rid = 0;
try {
    $codes = pbj_playground_invite_codes();
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $params = array_merge([$uid], $codes);
    $stmt = $pdo->prepare(
        "SELECT r.id FROM user_restaurant ur
         JOIN restaurants r ON r.id = ur.restaurant_id
         WHERE ur.user_id = ? AND UPPER(r.invite_code) IN ($placeholders)
         ORDER BY CASE WHEN UPPER(r.invite_code) = 'SALES-PBJ' THEN 0 ELSE 1 END, ur.joined_at ASC
         LIMIT 1"
    );
    $stmt->execute($params);
    $rid = (int) ($stmt->fetchColumn() ?: 0);
    if ($rid <= 0) {
        $houses = pbj_user_restaurants($pdo, $uid);
        foreach ($houses as $h) {
            $hid = (int) ($h['id'] ?? 0);
            if ($hid > 0 && pbj_restaurant_is_playground_resetable($pdo, $hid)) {
                $rid = $hid;
                break;
            }
        }
    }
} catch (Throwable $e) {
    $rid = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $meta = $rid > 0
        ? pbj_sales_client_meta($pdo, $rid)
        : ['sales_playground' => false, 'playground_reset' => false];
    $meta['ok'] = true;
    $meta['restaurant_id'] = $rid;
    $meta['is_platform_admin'] = pbj_is_platform_admin($email);
    $meta['gold_master_keys'] = pbj_sales_gold_master_key_count();
    $meta['playground_ids'] = pbj_sales_playground_restaurant_ids($pdo);
    echo json_encode($meta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

if (!pbj_is_platform_admin($email)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode((string) $raw, true);
if (!is_array($body)) {
    $body = $_POST;
}
$action = (string) ($body['action'] ?? '');

if ($action === 'ensure') {
    $ids = pbj_ensure_all_playgrounds($pdo);
    echo json_encode([
        'ok' => true,
        'demo_id' => $ids['demo'],
        'sales_id' => $ids['sales'],
        'codes' => pbj_playground_invite_codes(),
    ]);
    exit;
}

if ($action === 'save_starters') {
    $source = (int) ($body['source_restaurant_id'] ?? 0);
    if ($source <= 0) {
        // Default: capture from DEMO playground
        $source = pbj_ensure_demo_playground($pdo);
    }
    $r = pbj_sales_save_starters_from_restaurant($pdo, $source);
    echo json_encode($r);
    exit;
}

if ($action === 'reset') {
    // Only one house? optional restaurant_id — else both
    $only = (int) ($body['restaurant_id'] ?? 0);
    pbj_ensure_all_playgrounds($pdo);
    if ($only > 0 && pbj_restaurant_is_playground_resetable($pdo, $only)) {
        $results = [$only => pbj_sales_reset_house($pdo, $only)];
    } else {
        $results = pbj_sales_reset_all($pdo);
    }
    $ok = true;
    foreach ($results as $res) {
        if (empty($res['ok'])) {
            $ok = false;
        }
    }
    echo json_encode([
        'ok' => $ok,
        'results' => $results,
        'gold_master_keys' => pbj_sales_gold_master_key_count(),
        'message' => $ok
            ? 'Both playgrounds restored from gold master. Live deletes are gone; starters are safe on disk.'
            : 'One or more houses failed to reset — check gold master / logs.',
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'unknown_action']);
