<?php
/**
 * Checklist / prep task photo upload & delete.
 * POST multipart: action=upload|delete, file (upload), photoId (delete optional), itemId
 * Stores under uploads/checklist-photos/{restaurant_id}/{YYYY-MM-DD}/
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

$ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/jpg' => 'jpg',
    'image/pjpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/heic' => 'heic',
    'image/heif' => 'heif',
    'image/gif' => 'gif',
];
$ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'];
$MAX_BYTES = 6 * 1024 * 1024; // 6MB raw upload (client should compress)

function ensure_demo_restaurant(PDO $pdo): int {
    $stmt = $pdo->query("SELECT id FROM restaurants WHERE invite_code = 'DEMO-PBJ' LIMIT 1");
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int) $id;
    }
    try {
        $pdo->prepare("INSERT INTO restaurants (name, owner_id, invite_code) VALUES ('Demo Kitchen', 1, 'DEMO-PBJ')")->execute();
        return (int) $pdo->lastInsertId();
    } catch (Exception $e) {
        $stmt = $pdo->query('SELECT id FROM restaurants ORDER BY id ASC LIMIT 1');
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }
        throw $e;
    }
}

function resolve_restaurant_id(PDO $pdo): int {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
            return ensure_demo_restaurant($pdo);
        }
        return 0;
    }
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
    return $rid ? (int) $rid : 0;
}

function photos_dir(int $rid, string $date): string {
    $base = __DIR__ . '/uploads/checklist-photos/' . $rid . '/' . $date;
    if (!is_dir($base)) {
        mkdir($base, 0775, true);
    }
    return $base;
}

function public_url(int $rid, string $date, string $filename): string {
    return '/uploads/checklist-photos/' . $rid . '/' . $date . '/' . rawurlencode($filename);
}

function safe_date(string $d): string {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
        return date('Y-m-d');
    }
    $p = explode('-', $d);
    if (!checkdate((int) $p[1], (int) $p[2], (int) $p[0])) {
        return date('Y-m-d');
    }
    return $d;
}

try {
    $rid = resolve_restaurant_id($pdo);
    if ($rid <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'no_house']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'method']);
        exit;
    }

    $action = strtolower(trim((string) ($_POST['action'] ?? 'upload')));
    $date = safe_date(trim((string) ($_POST['date'] ?? date('Y-m-d'))));

    if ($action === 'delete') {
        $url = trim((string) ($_POST['url'] ?? ''));
        $photoId = trim((string) ($_POST['photoId'] ?? ''));
        $deleted = false;
        $prefix = '/uploads/checklist-photos/' . $rid . '/';
        if ($url !== '' && strpos($url, $prefix) === 0) {
            $rel = substr($url, strlen('/uploads/'));
            $path = __DIR__ . '/uploads/' . $rel;
            // Prevent path traversal
            $realBase = realpath(__DIR__ . '/uploads/checklist-photos/' . $rid);
            $realPath = realpath($path);
            if ($realBase && $realPath && strpos($realPath, $realBase) === 0 && is_file($realPath)) {
                @unlink($realPath);
                $deleted = true;
            }
        } elseif ($photoId !== '' && preg_match('/^[a-zA-Z0-9._-]+$/', $photoId)) {
            $dir = photos_dir($rid, $date);
            foreach (glob($dir . '/' . $photoId . '.*') ?: [] as $f) {
                @unlink($f);
                $deleted = true;
            }
        }
        echo json_encode(['ok' => true, 'deleted' => $deleted]);
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

    $file = $_FILES['file'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'upload_error', 'code' => (int) ($file['error'] ?? 0)]);
        exit;
    }
    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $MAX_BYTES) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'size']);
        exit;
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'tmp']);
        exit;
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        if ($fi) {
            $mime = (string) finfo_file($fi, $tmp);
            finfo_close($fi);
        }
    }
    if ($mime === '' && !empty($file['type'])) {
        $mime = strtolower((string) $file['type']);
    }
    $mime = strtolower($mime);

    $origName = (string) ($file['name'] ?? 'photo.jpg');
    $extFromName = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $ext = $ALLOWED_MIME[$mime] ?? null;
    if ($ext === null && in_array($extFromName, $ALLOWED_EXT, true)) {
        $ext = $extFromName === 'jpeg' ? 'jpg' : $extFromName;
    }
    if ($ext === null) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'type', 'mime' => $mime]);
        exit;
    }

    $photoId = bin2hex(random_bytes(8));
    $filename = $photoId . '.' . $ext;
    $dir = photos_dir($rid, $date);
    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($tmp, $dest)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'save']);
        exit;
    }
    @chmod($dest, 0664);

    $url = public_url($rid, $date, $filename);
    echo json_encode([
        'ok' => true,
        'url' => $url,
        'photoId' => $photoId,
        'date' => $date,
        'mime' => $mime,
    ]);
} catch (Throwable $e) {
    error_log('checklist-photo-api: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server']);
}
