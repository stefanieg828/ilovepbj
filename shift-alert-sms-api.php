<?php
/**
 * Send a shift reminder SMS (Twilio when configured).
 * POST JSON: { phone, name?, role?, start?, end?, minutes_before? }
 * Authenticated house members only (session).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/sms-config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'ok' => true,
        'twilio_configured' => sms_is_configured(),
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
$name = trim((string) ($body['name'] ?? ''));
$role = trim((string) ($body['role'] ?? ''));
$start = trim((string) ($body['start'] ?? ''));
$end = trim((string) ($body['end'] ?? ''));
$minutesBefore = (int) ($body['minutes_before'] ?? 0);
$forceDevice = !empty($body['device_sms']);

$to = sms_normalize_phone($phoneRaw);
if ($to === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_phone', 'hint' => 'Use a full mobile number, e.g. 5551234567']);
    exit;
}

// Friendly 12h times if HH:MM
$fmt = static function (string $t): string {
    if ($t === '' || !preg_match('/^\d{1,2}:\d{2}/', $t)) {
        return $t;
    }
    $p = explode(':', $t);
    $h = (int) $p[0];
    $m = $p[1] ?? '00';
    $ap = $h >= 12 ? 'PM' : 'AM';
    $h = $h % 12;
    if ($h === 0) {
        $h = 12;
    }
    return $h . ':' . $m . ' ' . $ap;
};

$startLabel = $fmt($start);
$endLabel = $fmt($end);
$who = $name !== '' ? $name : 'there';
$when = $startLabel !== '' ? $startLabel : 'soon';
if ($endLabel !== '') {
    $when .= '–' . $endLabel;
}
$roleBit = $role !== '' ? " ({$role})" : '';
$lead = $minutesBefore > 0
    ? "Heads up — shift in about {$minutesBefore} min!"
    : 'Shift reminder:';

$bodyText = "{$lead}\n{$who}{$roleBit} · {$when}\n— ilovepbj ops";

$smsLink = 'sms:' . rawurlencode($to) . '?&body=' . rawurlencode($bodyText);

if (!$forceDevice && sms_is_configured()) {
    $sent = sms_send($to, $bodyText);
    if (!empty($sent['ok'])) {
        echo json_encode([
            'ok' => true,
            'mode' => 'twilio',
            'to' => $to,
            'sid' => $sent['sid'] ?? '',
            'message' => $bodyText,
        ]);
        exit;
    }
    echo json_encode([
        'ok' => true,
        'mode' => 'device_fallback',
        'to' => $to,
        'sms_link' => $smsLink,
        'message' => $bodyText,
        'error' => $sent['error'] ?? 'twilio_failed',
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'mode' => 'device',
    'to' => $to,
    'sms_link' => $smsLink,
    'message' => $bodyText,
    'twilio_configured' => sms_is_configured(),
]);
