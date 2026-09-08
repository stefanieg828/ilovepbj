<?php
/**
 * Square (and future POS) webhooks.
 * Configure Square notification URL:
 *   https://ilovepbj.shop/pos/webhook
 *
 * Events used: payment.*, labor.timecard.*, labor.shift.*
 * Sets pending_sync on the matching restaurant connection so the app auto-pulls.
 */
require_once __DIR__ . '/pos-config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$raw = file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] ?? $_SERVER['HTTP_X_SQUARE_SIGNATURE'] ?? '';

$secrets = pos_load_secrets();
$whKey = '';
if (!empty($secrets['square']['webhook_signature_key'])) {
    $whKey = (string) $secrets['square']['webhook_signature_key'];
}

// Optional signature check when key is configured
if ($whKey !== '' && $sig !== '') {
    $notifUrl = pos_public_base_url() . '/pos/webhook';
    // Square signs notification_url + body
    $expected = base64_encode(hash_hmac('sha256', $notifUrl . $raw, $whKey, true));
    if (!hash_equals($expected, $sig)) {
        // Some configs sign body only
        $expected2 = base64_encode(hash_hmac('sha256', $raw, $whKey, true));
        if (!hash_equals($expected2, $sig)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'bad_signature']);
            exit;
        }
    }
}

$payload = json_decode($raw ?: 'null', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_json']);
    exit;
}

$type = (string) ($payload['type'] ?? $payload['event_type'] ?? '');
$data = $payload['data'] ?? [];
$obj = is_array($data) ? ($data['object'] ?? $data) : [];

// Extract merchant id
$merchantId = (string) (
    $payload['merchant_id']
    ?? $obj['merchant_id']
    ?? ($obj['payment']['merchant_id'] ?? '')
    ?? ($obj['timecard']['location_id'] ?? '') // weak fallback
    ?? ''
);

$interesting = (
    str_starts_with($type, 'payment.')
    || str_starts_with($type, 'labor.')
    || str_contains($type, 'timecard')
    || str_contains($type, 'shift')
    || $type === ''
);

if (!$interesting) {
    echo json_encode(['ok' => true, 'ignored' => true, 'type' => $type]);
    exit;
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        require_once __DIR__ . '/config.php';
    }
    pos_ensure_tables($pdo);

    $updated = 0;
    if ($merchantId !== '') {
        $stmt = $pdo->prepare(
            "SELECT restaurant_id, meta_json FROM restaurant_pos_connections
             WHERE provider = 'square' AND merchant_id = ? AND status = 'active'"
        );
        $stmt->execute([$merchantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } else {
        // Broadcast pending sync to all active Square connections (sandbox tests)
        $stmt = $pdo->query(
            "SELECT restaurant_id, meta_json FROM restaurant_pos_connections
             WHERE provider = 'square' AND status = 'active'"
        );
        $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    foreach ($rows as $row) {
        $rid = (int) $row['restaurant_id'];
        $meta = [];
        if (!empty($row['meta_json'])) {
            $decoded = json_decode((string) $row['meta_json'], true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }
        $meta['pending_sync'] = true;
        $meta['last_webhook_at'] = date('Y-m-d H:i:s');
        $meta['last_webhook_type'] = $type !== '' ? $type : 'unknown';
        $meta['last_webhook_merchant'] = $merchantId;
        pos_save_connection($pdo, $rid, 'square', [
            'meta_json' => $meta,
        ]);
        $updated++;
    }

    // Lightweight event log table
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_pos_webhook_events (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        provider VARCHAR(32) NOT NULL DEFAULT 'square',
        event_type VARCHAR(128) NULL,
        merchant_id VARCHAR(128) NULL,
        restaurant_id INT NULL,
        payload_json MEDIUMTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_rest (restaurant_id),
        KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $ins = $pdo->prepare(
        'INSERT INTO restaurant_pos_webhook_events (provider, event_type, merchant_id, restaurant_id, payload_json)
         VALUES (?,?,?,?,?)'
    );
    $ridLog = $updated === 1 && !empty($rows[0]['restaurant_id']) ? (int) $rows[0]['restaurant_id'] : null;
    $ins->execute([
        'square',
        $type !== '' ? $type : null,
        $merchantId !== '' ? $merchantId : null,
        $ridLog,
        substr($raw, 0, 20000),
    ]);

    echo json_encode(['ok' => true, 'type' => $type, 'connectionsUpdated' => $updated]);
} catch (Throwable $e) {
    error_log('pos-webhook: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server']);
}
