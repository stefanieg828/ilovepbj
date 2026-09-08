<?php
/**
 * Shared OAuth callback for Square + Clover.
 * Redirect URI: https://ilovepbj.shop/pos/oauth/callback
 * Provider is chosen from session (set by /pos/oauth/start).
 */
require_once __DIR__ . '/pos-config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    require_once __DIR__ . '/config.php';
}

function pos_cb_fail(string $code): void {
    header('Location: /admin/pos-connect?err=' . rawurlencode($code));
    exit;
}

if (!pos_user_can_manage($pdo)) {
    pos_cb_fail('forbidden');
}

// Seller denied
if (!empty($_GET['error'])) {
    pos_cb_fail('denied');
}

$code = trim((string) ($_GET['code'] ?? ''));
$state = trim((string) ($_GET['state'] ?? ''));
// Clover sometimes returns merchant_id on the callback query string
$merchantFromQuery = trim((string) ($_GET['merchant_id'] ?? $_GET['merchantId'] ?? ''));

if ($code === '' || $state === '') {
    pos_cb_fail('missing_code');
}

$sessState = (string) ($_SESSION['pos_oauth_state'] ?? '');
$sessProv = (string) ($_SESSION['pos_oauth_provider'] ?? '');
$sessAt = (int) ($_SESSION['pos_oauth_at'] ?? 0);
if ($sessState === '' || !hash_equals($sessState, $state) || !in_array($sessProv, ['square', 'clover'], true)) {
    pos_cb_fail('state');
}
if ($sessAt < time() - 600) {
    pos_cb_fail('expired');
}

$rid = (int) ($_SESSION['pos_oauth_rid'] ?? 0);
if ($rid <= 0) {
    $rid = pos_resolve_restaurant_id($pdo);
}
if ($rid <= 0) {
    pos_cb_fail('no_house');
}

$provider = $sessProv;

// clear one-time state before long network work is fine after validation
unset($_SESSION['pos_oauth_state'], $_SESSION['pos_oauth_provider'], $_SESSION['pos_oauth_at'], $_SESSION['pos_oauth_rid']);

if ($provider === 'square') {
    $ex = pos_square_exchange_code($code);
    if (empty($ex['ok'])) {
        pos_cb_fail('token');
    }
    $tok = $ex['token'];
    $access = (string) ($tok['access_token'] ?? '');
    $refresh = (string) ($tok['refresh_token'] ?? '');
    $merchantId = (string) ($tok['merchant_id'] ?? '');
    $expires = null;
    if (!empty($tok['expires_at'])) {
        $expires = date('Y-m-d H:i:s', strtotime($tok['expires_at']));
    } elseif (!empty($tok['expires_in'])) {
        $expires = date('Y-m-d H:i:s', time() + (int) $tok['expires_in']);
    }

    pos_save_connection($pdo, $rid, 'square', [
        'merchant_id' => $merchantId,
        'access_token' => $access,
        'refresh_token' => $refresh,
        'token_expires_at' => $expires,
        'scopes' => implode(' ', pos_square_scopes()),
        'status' => 'active',
        'connected_at' => date('Y-m-d H:i:s'),
        'last_error' => null,
        'meta_json' => ['merchant_id' => $merchantId],
    ]);

    $locs = pos_square_list_locations($pdo, $rid);
    if (!empty($locs['ok']) && !empty($locs['locations'][0]['id'])) {
        pos_save_connection($pdo, $rid, 'square', [
            'location_id' => $locs['locations'][0]['id'],
            'location_name' => $locs['locations'][0]['name'] ?? '',
        ]);
    }

    header('Location: /admin/pos-connect?ok=square');
    exit;
}

// —— Clover ——
$ex = pos_clover_exchange_code($code);
if (empty($ex['ok'])) {
    pos_cb_fail('token');
}
$tok = $ex['token'];
$access = (string) ($tok['access_token'] ?? '');
$refresh = (string) ($tok['refresh_token'] ?? '');
$merchantId = (string) ($tok['merchant_id'] ?? $merchantFromQuery);
$expires = null;
if (!empty($tok['access_token_expiration'])) {
    $exp = $tok['access_token_expiration'];
    if (is_numeric($exp)) {
        $ts = (int) $exp;
        if ($ts > 20000000000) {
            $ts = (int) floor($ts / 1000);
        }
        $expires = date('Y-m-d H:i:s', $ts);
    }
} elseif (!empty($tok['expires_in'])) {
    $expires = date('Y-m-d H:i:s', time() + (int) $tok['expires_in']);
}

pos_save_connection($pdo, $rid, 'clover', [
    'merchant_id' => $merchantId,
    'location_id' => $merchantId,
    'access_token' => $access,
    'refresh_token' => $refresh,
    'token_expires_at' => $expires,
    'scopes' => 'merchant',
    'status' => 'active',
    'connected_at' => date('Y-m-d H:i:s'),
    'last_error' => null,
    'meta_json' => ['merchant_id' => $merchantId],
]);

// Best-effort merchant display name
$info = pos_clover_merchant_info($pdo, $rid);
if (!empty($info['ok']) && !empty($info['locations'][0]['name'])) {
    pos_save_connection($pdo, $rid, 'clover', [
        'location_name' => $info['locations'][0]['name'],
    ]);
}

header('Location: /admin/pos-connect?ok=clover');
exit;
