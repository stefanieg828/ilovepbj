<?php
/**
 * Weekly owner snapshot API
 * GET  ?action=get          → { ok, weeklyEmail, mailConfigured }
 * POST { action: set_opt_in, weeklyEmail: bool }
 * POST { action: email_me, body: string, range?: {start,end,label} }
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/pbj-permissions.php';
require_once __DIR__ . '/weekly-owner-snapshot.inc.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

$uid = (int) $_SESSION['user_id'];
$rid = pbj_permissions_resolve_restaurant_id($pdo);
if ($rid <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'no_house', 'hint' => 'Join or create a house first.']);
    exit;
}

// Permission: owner/gm/admin or admin.reports.view
$role = '';
try {
    if (function_exists('pbj_permissions_user_role')) {
        $role = (string) pbj_permissions_user_role($pdo, $uid, $rid);
    }
} catch (Throwable $e) {
    $role = (string) ($_SESSION['role'] ?? '');
}
$role = strtolower($role);
if ($role === 'admin') {
    $role = 'gm';
}
$can = in_array($role, ['owner', 'gm'], true);
if (!$can && function_exists('pbj_can')) {
    $can = pbj_can('admin.reports.view') || pbj_can('admin.reports.edit');
}
if (!$can) {
    // Fall back to session role
    $sr = strtolower((string) ($_SESSION['role'] ?? ''));
    if ($sr === 'admin') {
        $sr = 'gm';
    }
    $can = in_array($sr, ['owner', 'gm', 'manager'], true);
}
if (!$can) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden', 'hint' => 'Owners/GMs with Reports view only.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$mailReady = function_exists('pbj_mail_is_configured') && pbj_mail_is_configured();

if ($method === 'GET') {
    $settings = pbj_permissions_load_settings($pdo, $rid);
    echo json_encode([
        'ok' => true,
        'weeklyEmail' => pbj_weekly_snapshot_opt_in_get($settings),
        'mailConfigured' => $mailReady,
        'restaurantId' => $rid,
    ]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode((string) $raw, true);
if (!is_array($body)) {
    $body = $_POST;
}
$action = (string) ($body['action'] ?? '');

if ($action === 'set_opt_in') {
    $on = !empty($body['weeklyEmail']);
    try {
        pbj_weekly_snapshot_opt_in_set($pdo, $rid, $on);
        echo json_encode(['ok' => true, 'weeklyEmail' => $on]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'save_failed', 'hint' => 'Could not save setting.']);
    }
    exit;
}

if ($action === 'email_me') {
    if (!$mailReady) {
        http_response_code(503);
        echo json_encode(['ok' => false, 'error' => 'mail_not_configured', 'hint' => 'SMTP is not configured on this server yet.']);
        exit;
    }
    $text = trim((string) ($body['body'] ?? ''));
    if ($text === '' || strlen($text) > 200000) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'bad_body', 'hint' => 'Snapshot body missing.']);
        exit;
    }
    $to = strtolower(trim((string) ($_SESSION['email'] ?? '')));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $owners = pbj_restaurant_owner_emails($pdo, $rid);
        $to = $owners[0] ?? '';
    }
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'no_email', 'hint' => 'No email on your account.']);
        exit;
    }
    $range = $body['range'] ?? null;
    $rangeLabel = '';
    if (is_array($range) && !empty($range['start']) && !empty($range['end'])) {
        $rangeLabel = ' · ' . $range['start'] . ' → ' . $range['end'];
    }
    $house = '';
    try {
        $st = $pdo->prepare('SELECT name FROM restaurants WHERE id = ? LIMIT 1');
        $st->execute([$rid]);
        $house = trim((string) ($st->fetchColumn() ?: ''));
    } catch (Throwable $e) {
        $house = '';
    }
    $subject = 'Weekly owner snapshot' . ($house !== '' ? (' · ' . $house) : '') . $rangeLabel;
    $ok = pbj_send_mail($to, $subject, $text);
    if (!$ok) {
        http_response_code(502);
        echo json_encode(['ok' => false, 'error' => 'send_failed', 'hint' => 'Mail send failed. Try again later.']);
        exit;
    }
    echo json_encode(['ok' => true, 'to' => $to]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'bad_action']);
