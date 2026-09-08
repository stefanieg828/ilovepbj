<?php
/**
 * Send a house / playground invite by email.
 * POST JSON: { email, house_code, name? }
 *
 * Allowed for:
 *  - Platform admins (any known playground / house code)
 *  - House owners/managers who can manage members of that house
 *
 * Falls back to a mailto: payload if server mail fails.
 */
require_once __DIR__ . '/config.php';
if (is_readable(__DIR__ . '/sms-config.php')) {
    require_once __DIR__ . '/sms-config.php';
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
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

$toEmail = strtolower(trim((string) ($body['email'] ?? '')));
$houseCode = strtoupper(trim((string) ($body['house_code'] ?? '')));
$personName = trim((string) ($body['name'] ?? ''));
$forceMailto = !empty($body['device_mail']); // prefer mailto: even if server mail works

if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_email', 'hint' => 'Enter a valid email address.']);
    exit;
}

if ($houseCode === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'need_house']);
    exit;
}

$uid = (int) ($_SESSION['user_id'] ?? 0);
$sessionEmail = (string) ($_SESSION['email'] ?? '');
$isPlat = function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($sessionEmail);

// Resolve house by invite code
$house = null;
try {
    $stmt = $pdo->prepare('SELECT id, name, invite_code, owner_id FROM restaurants WHERE UPPER(invite_code) = ? LIMIT 1');
    $stmt->execute([$houseCode]);
    $house = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $house = null;
}

if (!$house) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'unknown_house']);
    exit;
}

$houseId = (int) ($house['id'] ?? 0);
$houseName = trim((string) ($house['name'] ?? 'our kitchen'));
if ($houseName === '') {
    $houseName = 'our kitchen';
}
$code = strtoupper(trim((string) ($house['invite_code'] ?? $houseCode)));

// Authorization
$allowed = false;
if ($isPlat) {
    $allowed = true;
} elseif ($houseId > 0 && $uid > 0) {
    if (function_exists('pbj_can_manage_house_members') && pbj_can_manage_house_members($pdo, $houseId, $uid)) {
        $allowed = true;
    } elseif ((int) ($house['owner_id'] ?? 0) === $uid) {
        $allowed = true;
    }
}

if (!$allowed) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

$base = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
$join = $base . '/join';
$register = $base . '/register';

$greeting = $personName !== '' ? "Hi {$personName}," : 'Hi there,';
$mailBody = $greeting . "\n\n"
    . "You're invited to join {$houseName} on ilovepbj ops!\n\n"
    . "1) Open {$join}\n"
    . "   (or register at {$register} and choose “I have a code”)\n"
    . "2) Enter house code: {$code}\n"
    . "3) Create your login and explore\n\n"
    . "No phone number needed — email is enough.\n\n"
    . "Questions? nutsaboutpbj@ilovepbj.shop\n";

$subject = "You're invited to {$houseName} on ilovepbj ops";
$mailto = 'mailto:' . rawurlencode($toEmail)
    . '?subject=' . rawurlencode($subject)
    . '&body=' . rawurlencode($mailBody);

$replyTo = '';
if ($sessionEmail !== '' && filter_var($sessionEmail, FILTER_VALIDATE_EMAIL)) {
    $replyTo = $sessionEmail;
}

if (!$forceMailto && function_exists('pbj_send_mail')) {
    $sent = pbj_send_mail($toEmail, $subject, $mailBody, $replyTo);
    if ($sent) {
        echo json_encode([
            'ok' => true,
            'mode' => 'mail',
            'to' => $toEmail,
            'house_code' => $code,
            'house_name' => $houseName,
            'subject' => $subject,
            'message' => $mailBody,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Fallback: open the user's mail app with a ready-to-send message
echo json_encode([
    'ok' => true,
    'mode' => 'mailto',
    'fallback' => true,
    'to' => $toEmail,
    'house_code' => $code,
    'house_name' => $houseName,
    'subject' => $subject,
    'message' => $mailBody,
    'mailto' => $mailto,
    'hint' => function_exists('pbj_send_mail')
        ? 'Server mail could not send — opening your email app with the invite ready.'
        : 'Opening your email app with the invite ready — hit Send.',
], JSON_UNESCAPED_UNICODE);
