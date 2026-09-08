<?php
/**
 * Start POS OAuth connect.
 * GET ?provider=square|clover
 * Toast uses form POST to pos-api.php?action=toast_connect (credentials + restaurant GUID).
 */
require_once __DIR__ . '/pos-config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$pdo = $pdo ?? null;
if (!$pdo && isset($GLOBALS['pdo'])) {
    $pdo = $GLOBALS['pdo'];
}
if (!isset($pdo) || !($pdo instanceof PDO)) {
    require_once __DIR__ . '/config.php';
}

if (!pos_user_can_manage($pdo)) {
    http_response_code(403);
    echo 'Managers only.';
    exit;
}

$provider = strtolower(trim((string) ($_GET['provider'] ?? 'square')));
if (!in_array($provider, ['square', 'clover'], true)) {
    header('Location: /admin/pos-connect?err=provider');
    exit;
}

if ($provider === 'square' && !pos_square_configured()) {
    header('Location: /admin/pos-connect?err=square_config');
    exit;
}
if ($provider === 'clover' && !pos_clover_configured()) {
    header('Location: /admin/pos-connect?err=clover_config');
    exit;
}

$rid = pos_resolve_restaurant_id($pdo);
if ($rid <= 0) {
    header('Location: /admin/pos-connect?err=no_house');
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['pos_oauth_state'] = $state;
$_SESSION['pos_oauth_provider'] = $provider;
$_SESSION['pos_oauth_rid'] = $rid;
$_SESSION['pos_oauth_at'] = time();

if ($provider === 'clover') {
    $url = pos_clover_authorize_url($state);
} else {
    $url = pos_square_authorize_url($state);
}
header('Location: ' . $url);
exit;
