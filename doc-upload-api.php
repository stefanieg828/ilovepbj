<?php
/**
 * Document upload / delete for house manuals.
 * POST multipart: type, action=upload|delete, file (for upload)
 * Stores under uploads/docs/{restaurant_id}/
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

$ALLOWED_TYPES = ['handbook', 'foh_manual', 'boh_manual', 'sops', 'health_safety'];
$ALLOWED_MIME = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-word' => 'doc',
    'application/octet-stream' => null, // allow if extension ok
];
$ALLOWED_EXT = ['pdf', 'doc', 'docx'];
$MAX_BYTES = 10 * 1024 * 1024; // 10MB

function ensure_demo_restaurant(PDO $pdo): int {
    $stmt = $pdo->query("SELECT id FROM restaurants WHERE invite_code = 'DEMO-PBJ' LIMIT 1");
    $id = $stmt->fetchColumn();
    if ($id) return (int) $id;
    try {
        $pdo->prepare("INSERT INTO restaurants (name, owner_id, invite_code) VALUES ('Demo Kitchen', 1, 'DEMO-PBJ')")->execute();
        return (int) $pdo->lastInsertId();
    } catch (Exception $e) {
        $stmt = $pdo->query("SELECT id FROM restaurants ORDER BY id ASC LIMIT 1");
        $id = $stmt->fetchColumn();
        if ($id) return (int) $id;
        throw $e;
    }
}

function resolve_restaurant_id(PDO $pdo): int {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        // Local testing only — never auto-join real accounts to DEMO playground
        if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
            return ensure_demo_restaurant($pdo);
        }
        return 0;
    }
    // Prefer a house they own (paid / free Small House, etc.)
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
    // No house → local only. Do not auto-join DEMO-PBJ / SALES-PBJ.
    return 0;
}

function docs_dir(int $rid): string {
    $base = __DIR__ . '/uploads/docs/' . $rid;
    if (!is_dir($base)) {
        mkdir($base, 0775, true);
    }
    return $base;
}

function meta_path(int $rid, string $type): string {
    return docs_dir($rid) . '/' . $type . '.json';
}

function file_path_for(int $rid, string $type, string $ext): string {
    return docs_dir($rid) . '/' . $type . '.' . $ext;
}

function load_meta(int $rid, string $type): ?array {
    $p = meta_path($rid, $type);
    if (!is_file($p)) return null;
    $j = json_decode((string) file_get_contents($p), true);
    return is_array($j) ? $j : null;
}

function save_meta(int $rid, string $type, array $meta): void {
    file_put_contents(meta_path($rid, $type), json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function delete_files(int $rid, string $type): void {
    $dir = docs_dir($rid);
    foreach (glob($dir . '/' . $type . '.*') ?: [] as $f) {
        if (is_file($f)) @unlink($f);
    }
}

function public_url(int $rid, string $type, string $ext): string {
    return 'uploads/docs/' . $rid . '/' . $type . '.' . $ext;
}

try {
    $rid = resolve_restaurant_id($pdo);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'restaurant']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$type = preg_replace('/[^a-z_]/', '', strtolower((string) ($_REQUEST['type'] ?? '')));

if (!in_array($type, $ALLOWED_TYPES, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_type']);
    exit;
}

// No house membership — never write into DEMO playground
if ($rid <= 0) {
    if ($method === 'GET') {
        echo json_encode(['ok' => true, 'restaurantId' => 0, 'upload' => null, 'localOnly' => true]);
        exit;
    }
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'no_house']);
    exit;
}

if ($method === 'GET') {
    $meta = load_meta($rid, $type);
    echo json_encode(['ok' => true, 'restaurantId' => $rid, 'upload' => $meta]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$action = strtolower((string) ($_POST['action'] ?? 'upload'));

if ($action === 'delete') {
    delete_files($rid, $type);
    echo json_encode(['ok' => true, 'restaurantId' => $rid, 'upload' => null]);
    exit;
}

if ($action !== 'upload') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_action']);
    exit;
}

if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'no_file']);
    exit;
}

$f = $_FILES['file'];
if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'upload_err', 'code' => $f['error'] ?? null]);
    exit;
}

$size = (int) ($f['size'] ?? 0);
if ($size <= 0 || $size > $MAX_BYTES) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'too_large', 'maxMb' => 10]);
    exit;
}

$origName = (string) ($f['name'] ?? 'document');
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($ext, $ALLOWED_EXT, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_ext', 'allowed' => $ALLOWED_EXT]);
    exit;
}

$mime = (string) ($f['type'] ?? '');
// light mime check — extension already restricted
if ($mime && !isset($ALLOWED_MIME[$mime]) && strpos($mime, 'word') === false && $mime !== 'application/pdf') {
    // still allow known extensions
}

// Clear previous files for this type
delete_files($rid, $type);

$dest = file_path_for($rid, $type, $ext);
if (!move_uploaded_file($f['tmp_name'], $dest)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'save_failed']);
    exit;
}
@chmod($dest, 0644);

$meta = [
    'filename' => $origName,
    'stored' => basename($dest),
    'ext' => $ext,
    'mime' => $mime ?: ($ext === 'pdf' ? 'application/pdf' : 'application/octet-stream'),
    'size' => $size,
    'url' => public_url($rid, $type, $ext),
    'uploadedAt' => time() * 1000,
    'uploadedBy' => (int) ($_SESSION['user_id'] ?? 0),
];
save_meta($rid, $type, $meta);

echo json_encode(['ok' => true, 'restaurantId' => $rid, 'upload' => $meta]);
