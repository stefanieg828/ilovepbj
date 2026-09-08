<?php
/**
 * Vendor invoice library API — folders + scan/upload storage per restaurant.
 *
 * GET  → { ok, restaurantId, folders, invoices }
 * POST multipart or form:
 *   action=folder_create|folder_rename|folder_delete|folder_move
 *   action=upload|update|move|delete
 *
 * Files: uploads/invoices/{restaurant_id}/{id}.{ext}
 * Index: uploads/invoices/{restaurant_id}/index.json
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
    'application/pdf' => 'pdf',
];
$ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif', 'pdf'];
$MAX_BYTES = 10 * 1024 * 1024; // 10MB

/** Safe string length limit (no mbstring required). */
function inv_clip(string $s, int $max): string {
    if ($max <= 0) {
        return '';
    }
    if (function_exists('mb_substr')) {
        return (string) mb_substr($s, 0, $max, 'UTF-8');
    }
    if (function_exists('iconv_substr')) {
        $cut = @iconv_substr($s, 0, $max, 'UTF-8');
        if ($cut !== false && $cut !== null) {
            return (string) $cut;
        }
    }
    // Byte-safe fallback for ASCII-heavy titles / filenames
    if (strlen($s) <= $max) {
        return $s;
    }
    return substr($s, 0, $max);
}

function inv_ensure_demo_restaurant(PDO $pdo): int {
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

function inv_resolve_restaurant_id(PDO $pdo): int {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
            return inv_ensure_demo_restaurant($pdo);
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

function inv_uid(): string {
    return bin2hex(random_bytes(8));
}

function inv_base_dir(int $rid): string {
    $base = __DIR__ . '/uploads/invoices/' . $rid;
    if (!is_dir($base)) {
        mkdir($base, 0775, true);
    }
    return $base;
}

function inv_index_path(int $rid): string {
    return inv_base_dir($rid) . '/index.json';
}

function inv_default_folders(): array {
    $defs = [
        ['id' => 'f_food', 'name' => 'Food & Beverage'],
        ['id' => 'f_alcohol', 'name' => 'Alcohol'],
        ['id' => 'f_paper', 'name' => 'Paper & Disposables'],
        ['id' => 'f_cleaning', 'name' => 'Cleaning & Janitorial'],
    ];
    $out = [];
    foreach ($defs as $i => $d) {
        $out[] = [
            'id' => $d['id'],
            'name' => $d['name'],
            'sort' => $i,
            'createdAt' => 0,
        ];
    }
    return $out;
}

function inv_empty_state(): array {
    return [
        'folders' => inv_default_folders(),
        'invoices' => [],
        'updatedAt' => time() * 1000,
    ];
}

function inv_normalize_state(?array $raw): array {
    $state = inv_empty_state();
    if (!is_array($raw)) {
        return $state;
    }
    $folders = [];
    if (!empty($raw['folders']) && is_array($raw['folders'])) {
        foreach ($raw['folders'] as $i => $f) {
            if (!is_array($f)) {
                continue;
            }
            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($f['id'] ?? ''));
            $name = trim((string) ($f['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $folders[] = [
                'id' => $id,
                'name' => inv_clip($name, 80),
                'sort' => isset($f['sort']) ? (int) $f['sort'] : (int) $i,
                'createdAt' => (int) ($f['createdAt'] ?? 0),
            ];
        }
    }
    if (!$folders) {
        $folders = inv_default_folders();
    }
    usort($folders, function ($a, $b) {
        if ($a['sort'] === $b['sort']) {
            return strcmp($a['name'], $b['name']);
        }
        return $a['sort'] <=> $b['sort'];
    });
    foreach ($folders as $i => &$f) {
        $f['sort'] = $i;
    }
    unset($f);

    $invoices = [];
    if (!empty($raw['invoices']) && is_array($raw['invoices'])) {
        foreach ($raw['invoices'] as $inv) {
            if (!is_array($inv)) {
                continue;
            }
            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($inv['id'] ?? ''));
            $url = (string) ($inv['url'] ?? '');
            if ($id === '' || $url === '') {
                continue;
            }
            $invoices[] = [
                'id' => $id,
                'folderId' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($inv['folderId'] ?? '')),
                'filename' => (string) ($inv['filename'] ?? ''),
                'originalName' => inv_clip((string) ($inv['originalName'] ?? ''), 180),
                'title' => inv_clip(trim((string) ($inv['title'] ?? '')), 120),
                'note' => inv_clip(trim((string) ($inv['note'] ?? '')), 500),
                'mime' => (string) ($inv['mime'] ?? ''),
                'size' => (int) ($inv['size'] ?? 0),
                'url' => $url,
                'createdAt' => (int) ($inv['createdAt'] ?? 0),
                'updatedAt' => (int) ($inv['updatedAt'] ?? 0),
                'createdBy' => (int) ($inv['createdBy'] ?? 0),
            ];
        }
    }

    $state['folders'] = $folders;
    $state['invoices'] = $invoices;
    $state['updatedAt'] = (int) ($raw['updatedAt'] ?? (time() * 1000));
    return $state;
}

function inv_load(int $rid): array {
    $path = inv_index_path($rid);
    if (!is_file($path)) {
        $state = inv_empty_state();
        inv_save($rid, $state);
        return $state;
    }
    $j = json_decode((string) file_get_contents($path), true);
    return inv_normalize_state(is_array($j) ? $j : null);
}

function inv_save(int $rid, array $state): void {
    $state['updatedAt'] = (int) round(microtime(true) * 1000);
    $path = inv_index_path($rid);
    $tmp = $path . '.tmp';
    $json = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        throw new RuntimeException('encode');
    }
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        throw new RuntimeException('write');
    }
    rename($tmp, $path);
    @chmod($path, 0664);
}

function inv_find_folder(array &$state, string $id): ?int {
    foreach ($state['folders'] as $i => $f) {
        if ($f['id'] === $id) {
            return $i;
        }
    }
    return null;
}

function inv_find_invoice(array &$state, string $id): ?int {
    foreach ($state['invoices'] as $i => $inv) {
        if ($inv['id'] === $id) {
            return $i;
        }
    }
    return null;
}

function inv_public_url(int $rid, string $filename): string {
    return '/uploads/invoices/' . $rid . '/' . rawurlencode($filename);
}

function inv_delete_file(int $rid, string $filename): void {
    $filename = basename($filename);
    if ($filename === '' || $filename === 'index.json' || str_contains($filename, '..')) {
        return;
    }
    $path = inv_base_dir($rid) . '/' . $filename;
    $realBase = realpath(inv_base_dir($rid));
    $realPath = realpath($path);
    if ($realBase && $realPath && str_starts_with($realPath, $realBase) && is_file($realPath)) {
        @unlink($realPath);
    }
}

function inv_post_param(string $key, $default = '') {
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    // JSON body fallback
    static $json = null;
    if ($json === null) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode((string) $raw, true);
        $json = is_array($decoded) ? $decoded : [];
    }
    return $json[$key] ?? $default;
}

try {
    $rid = inv_resolve_restaurant_id($pdo);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uid = (int) ($_SESSION['user_id'] ?? 0);

    if ($rid <= 0) {
        if ($method === 'GET') {
            echo json_encode([
                'ok' => true,
                'restaurantId' => 0,
                'localOnly' => true,
                'folders' => inv_default_folders(),
                'invoices' => [],
            ]);
            exit;
        }
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'no_house']);
        exit;
    }

    if ($method === 'GET') {
        $state = inv_load($rid);
        echo json_encode([
            'ok' => true,
            'restaurantId' => $rid,
            'folders' => $state['folders'],
            'invoices' => $state['invoices'],
            'updatedAt' => $state['updatedAt'],
        ]);
        exit;
    }

    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'method']);
        exit;
    }

    $action = strtolower(trim((string) inv_post_param('action', '')));
    $state = inv_load($rid);

    // —— Folders ——
    if ($action === 'folder_create') {
        $name = trim((string) inv_post_param('name', ''));
        if ($name === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'name']);
            exit;
        }
        $maxSort = -1;
        foreach ($state['folders'] as $f) {
            $maxSort = max($maxSort, (int) $f['sort']);
        }
        $folder = [
            'id' => 'f_' . inv_uid(),
            'name' => inv_clip($name, 80),
            'sort' => $maxSort + 1,
            'createdAt' => (int) round(microtime(true) * 1000),
        ];
        $state['folders'][] = $folder;
        inv_save($rid, $state);
        echo json_encode(['ok' => true, 'folder' => $folder, 'folders' => $state['folders']]);
        exit;
    }

    if ($action === 'folder_rename') {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('id', ''));
        $name = trim((string) inv_post_param('name', ''));
        $idx = inv_find_folder($state, $id);
        if ($idx === null || $name === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_folder']);
            exit;
        }
        $state['folders'][$idx]['name'] = inv_clip($name, 80);
        inv_save($rid, $state);
        echo json_encode(['ok' => true, 'folder' => $state['folders'][$idx], 'folders' => $state['folders']]);
        exit;
    }

    if ($action === 'folder_delete') {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('id', ''));
        $idx = inv_find_folder($state, $id);
        if ($idx === null) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_folder']);
            exit;
        }
        if (count($state['folders']) <= 1) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'last_folder']);
            exit;
        }
        // Move invoices to first remaining folder
        $fallback = null;
        foreach ($state['folders'] as $f) {
            if ($f['id'] !== $id) {
                $fallback = $f['id'];
                break;
            }
        }
        foreach ($state['invoices'] as &$inv) {
            if ($inv['folderId'] === $id) {
                $inv['folderId'] = $fallback;
                $inv['updatedAt'] = (int) round(microtime(true) * 1000);
            }
        }
        unset($inv);
        array_splice($state['folders'], $idx, 1);
        foreach ($state['folders'] as $i => &$f) {
            $f['sort'] = $i;
        }
        unset($f);
        inv_save($rid, $state);
        echo json_encode([
            'ok' => true,
            'folders' => $state['folders'],
            'invoices' => $state['invoices'],
            'movedTo' => $fallback,
        ]);
        exit;
    }

    if ($action === 'folder_move') {
        // Reorder: direction up|down, or absolute sort via order (comma ids)
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('id', ''));
        $direction = strtolower(trim((string) inv_post_param('direction', '')));
        $orderRaw = trim((string) inv_post_param('order', ''));

        if ($orderRaw !== '') {
            $ids = array_values(array_filter(array_map(function ($x) {
                return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($x));
            }, explode(',', $orderRaw))));
            $byId = [];
            foreach ($state['folders'] as $f) {
                $byId[$f['id']] = $f;
            }
            $new = [];
            foreach ($ids as $fid) {
                if (isset($byId[$fid])) {
                    $new[] = $byId[$fid];
                    unset($byId[$fid]);
                }
            }
            foreach ($byId as $f) {
                $new[] = $f;
            }
            foreach ($new as $i => &$f) {
                $f['sort'] = $i;
            }
            unset($f);
            $state['folders'] = $new;
            inv_save($rid, $state);
            echo json_encode(['ok' => true, 'folders' => $state['folders']]);
            exit;
        }

        $idx = inv_find_folder($state, $id);
        if ($idx === null || ($direction !== 'up' && $direction !== 'down')) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_move']);
            exit;
        }
        $swap = $direction === 'up' ? $idx - 1 : $idx + 1;
        if ($swap < 0 || $swap >= count($state['folders'])) {
            echo json_encode(['ok' => true, 'folders' => $state['folders']]);
            exit;
        }
        $tmp = $state['folders'][$idx];
        $state['folders'][$idx] = $state['folders'][$swap];
        $state['folders'][$swap] = $tmp;
        foreach ($state['folders'] as $i => &$f) {
            $f['sort'] = $i;
        }
        unset($f);
        inv_save($rid, $state);
        echo json_encode(['ok' => true, 'folders' => $state['folders']]);
        exit;
    }

    // —— Invoices ——
    if ($action === 'upload') {
        $folderId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('folderId', ''));
        if (inv_find_folder($state, $folderId) === null) {
            // default to first folder
            $folderId = $state['folders'][0]['id'] ?? '';
        }
        if ($folderId === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'no_folder']);
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
                $mime = strtolower((string) finfo_file($fi, $tmp));
                finfo_close($fi);
            }
        }
        if ($mime === '' && !empty($file['type'])) {
            $mime = strtolower((string) $file['type']);
        }
        $origName = (string) ($file['name'] ?? 'invoice.jpg');
        // Android content providers often send blob / empty names
        if ($origName === '' || $origName === 'blob' || $origName === 'image') {
            $origName = 'invoice.jpg';
        }
        $extFromName = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $ext = $ALLOWED_MIME[$mime] ?? null;
        // Android gallery: MIME may be empty, application/octet-stream, or binary/octet-stream
        if ($ext === null && in_array($extFromName, $ALLOWED_EXT, true)) {
            $ext = $extFromName === 'jpeg' ? 'jpg' : $extFromName;
            if ($ext === 'pdf') {
                $mime = 'application/pdf';
            } elseif ($mime === '' || $mime === 'application/octet-stream' || $mime === 'binary/octet-stream') {
                $mime = $ext === 'pdf' ? 'application/pdf' : ('image/' . ($ext === 'jpg' ? 'jpeg' : $ext));
            }
        }
        // Last resort: sniff magic bytes when extension/MIME missing
        if ($ext === null && is_readable($tmp)) {
            $head = (string) @file_get_contents($tmp, false, null, 0, 16);
            if (str_starts_with($head, "\xFF\xD8\xFF")) {
                $ext = 'jpg';
                $mime = 'image/jpeg';
            } elseif (str_starts_with($head, "\x89PNG")) {
                $ext = 'png';
                $mime = 'image/png';
            } elseif (str_starts_with($head, '%PDF')) {
                $ext = 'pdf';
                $mime = 'application/pdf';
            } elseif (str_starts_with($head, 'RIFF') && str_contains($head, 'WEBP')) {
                $ext = 'webp';
                $mime = 'image/webp';
            } elseif (str_starts_with($head, 'GIF8')) {
                $ext = 'gif';
                $mime = 'image/gif';
            }
        }
        if ($ext === null) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'type', 'mime' => $mime, 'name' => $origName]);
            exit;
        }

        $id = inv_uid();
        $filename = $id . '.' . $ext;
        $dest = inv_base_dir($rid) . '/' . $filename;
        if (!move_uploaded_file($tmp, $dest)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'save']);
            exit;
        }
        @chmod($dest, 0664);

        $title = trim((string) inv_post_param('title', ''));
        $note = trim((string) inv_post_param('note', ''));
        $now = (int) round(microtime(true) * 1000);
        $invoice = [
            'id' => $id,
            'folderId' => $folderId,
            'filename' => $filename,
            'originalName' => inv_clip($origName, 180),
            'title' => inv_clip($title !== '' ? $title : pathinfo($origName, PATHINFO_FILENAME), 120),
            'note' => inv_clip($note, 500),
            'mime' => $mime,
            'size' => (int) ($file['size'] ?? 0),
            'url' => inv_public_url($rid, $filename),
            'createdAt' => $now,
            'updatedAt' => $now,
            'createdBy' => $uid,
        ];
        $state['invoices'][] = $invoice;
        inv_save($rid, $state);
        echo json_encode(['ok' => true, 'invoice' => $invoice, 'invoices' => $state['invoices']]);
        exit;
    }

    if ($action === 'update') {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('id', ''));
        $idx = inv_find_invoice($state, $id);
        if ($idx === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }
        // Always accept title/note when sent (empty string clears note / title)
        $titleIn = inv_post_param('title', null);
        $noteIn = inv_post_param('note', null);
        if ($titleIn !== null) {
            $state['invoices'][$idx]['title'] = inv_clip(trim((string) $titleIn), 120);
        }
        if ($noteIn !== null) {
            $state['invoices'][$idx]['note'] = inv_clip(trim((string) $noteIn), 500);
        }
        $newFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('folderId', ''));
        if ($newFolder !== '' && inv_find_folder($state, $newFolder) !== null) {
            $state['invoices'][$idx]['folderId'] = $newFolder;
        }
        $state['invoices'][$idx]['updatedAt'] = (int) round(microtime(true) * 1000);
        inv_save($rid, $state);
        echo json_encode(['ok' => true, 'invoice' => $state['invoices'][$idx], 'invoices' => $state['invoices']]);
        exit;
    }

    if ($action === 'move') {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('id', ''));
        $folderId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('folderId', ''));
        $idx = inv_find_invoice($state, $id);
        if ($idx === null || inv_find_folder($state, $folderId) === null) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_move']);
            exit;
        }
        $state['invoices'][$idx]['folderId'] = $folderId;
        $state['invoices'][$idx]['updatedAt'] = (int) round(microtime(true) * 1000);
        inv_save($rid, $state);
        echo json_encode(['ok' => true, 'invoice' => $state['invoices'][$idx], 'invoices' => $state['invoices']]);
        exit;
    }

    if ($action === 'delete') {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) inv_post_param('id', ''));
        $idx = inv_find_invoice($state, $id);
        if ($idx === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }
        $filename = (string) ($state['invoices'][$idx]['filename'] ?? '');
        if ($filename !== '') {
            inv_delete_file($rid, $filename);
        }
        array_splice($state['invoices'], $idx, 1);
        inv_save($rid, $state);
        echo json_encode(['ok' => true, 'invoices' => $state['invoices']]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_action']);
} catch (Throwable $e) {
    error_log('invoices-api: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server']);
}
