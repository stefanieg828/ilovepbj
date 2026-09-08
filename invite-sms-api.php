<?php
/**
 * Send (or prepare) a playground invite text.
 * POST JSON: { phone, house_code, name? }
 * Platform admins only.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/sms-config.php';
if (is_readable(__DIR__ . '/sales-playground.inc.php')) {
    require_once __DIR__ . '/sales-playground.inc.php';
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

$email = (string) ($_SESSION['email'] ?? '');
if (!function_exists('pbj_is_platform_admin') || !pbj_is_platform_admin($email)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'ok' => true,
        'twilio_configured' => sms_is_configured(),
        'houses' => sms_playground_houses($pdo),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode((string) $raw, true);
if (!is_array($body)) {
    $body = $_POST;
}

$phoneRaw = trim((string) ($body['phone'] ?? ''));
$houseCode = strtoupper(trim((string) ($body['house_code'] ?? '')));
$personName = trim((string) ($body['name'] ?? ''));
$forceDevice = !empty($body['device_sms']); // prefer sms: link even if Twilio is on

$houses = sms_playground_houses($pdo);
$house = null;
foreach ($houses as $h) {
    if ($h['code'] === $houseCode) {
        $house = $h;
        break;
    }
}
if (!$house) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'unknown_house']);
    exit;
}

$to = sms_normalize_phone($phoneRaw);
if ($to === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_phone', 'hint' => 'Use a full mobile number, e.g. 5551234567 or +15551234567']);
    exit;
}

$bodyText = sms_playground_invite_body($house['name'], $house['code']);
if ($personName !== '') {
    $bodyText = "Hi {$personName}!\n\n" . $bodyText;
}

// Device SMS deep link (works on phones without Twilio)
$smsLink = 'sms:' . rawurlencode($to) . '?&body=' . rawurlencode($bodyText);
// iOS prefers body= ; Android often &body= — include both patterns via ?&

if (!$forceDevice && sms_is_configured()) {
    $sent = sms_send($to, $bodyText);
    if (!empty($sent['ok'])) {
        echo json_encode([
            'ok' => true,
            'mode' => 'twilio',
            'to' => $to,
            'house_code' => $house['code'],
            'sid' => $sent['sid'] ?? '',
            'message' => $bodyText,
        ]);
        exit;
    }
    // Fall through to device link if Twilio fails
    echo json_encode([
        'ok' => true,
        'mode' => 'device_sms',
        'fallback' => true,
        'twilio_error' => $sent['error'] ?? 'send_failed',
        'to' => $to,
        'house_code' => $house['code'],
        'sms_link' => $smsLink,
        'message' => $bodyText,
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'mode' => 'device_sms',
    'to' => $to,
    'house_code' => $house['code'],
    'sms_link' => $smsLink,
    'message' => $bodyText,
    'hint' => sms_is_configured()
        ? null
        : 'Twilio not configured yet — opening your phone Messages app instead. Add sms-secrets.local.php for one-tap server send.',
]);
