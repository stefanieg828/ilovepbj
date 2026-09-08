<?php
/**
 * Platform-wide "Creator's news" for the home dashboard.
 * GET  → any authenticated user: { ok, text, updatedAt, canEdit }
 * POST → platform creator/admin only: { text }
 *
 * Saved once, shown on every user's home unless they hide it locally.
 */
require_once 'config.php';
require_once 'pbj-permissions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

function creator_news_ensure_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS platform_kv (
        k VARCHAR(64) NOT NULL PRIMARY KEY,
        v LONGTEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * Who may write Creator's news: site omnipotent email + platform admins.
 */
function creator_news_is_editor(PDO $pdo): bool {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if (function_exists('pbj_permissions_is_omnipotent') && pbj_permissions_is_omnipotent($pdo, $uid)) {
        return true;
    }
    $email = strtolower(trim((string) ($_SESSION['email'] ?? '')));
    if ($email === '' && $uid > 0) {
        try {
            $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$uid]);
            $email = strtolower(trim((string) $stmt->fetchColumn()));
            if ($email !== '') {
                $_SESSION['email'] = $email;
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
    if ($email !== '' && function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($email)) {
        return true;
    }
    return false;
}

function creator_news_load(PDO $pdo): array {
    creator_news_ensure_table($pdo);
    $stmt = $pdo->prepare('SELECT v, updated_at, updated_by FROM platform_kv WHERE k = ? LIMIT 1');
    $stmt->execute(['creator_news']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return ['text' => '', 'updatedAt' => 0, 'updatedBy' => null];
    }
    $data = json_decode((string) $row['v'], true);
    if (!is_array($data)) {
        $data = ['text' => (string) $row['v']];
    }
    $text = (string) ($data['text'] ?? '');
    // Preserve intentional whitespace inside notes; only trim ends
    $text = trim($text);
    $updatedAt = (int) ($data['updatedAt'] ?? 0);
    if ($updatedAt <= 0 && !empty($row['updated_at'])) {
        $updatedAt = (int) (strtotime((string) $row['updated_at']) * 1000);
    }
    return [
        'text' => $text,
        'updatedAt' => $updatedAt,
        'updatedBy' => isset($row['updated_by']) ? (int) $row['updated_by'] : null,
    ];
}

function creator_news_save(PDO $pdo, string $text, int $uid): array {
    creator_news_ensure_table($pdo);
    $payload = [
        'text' => $text,
        'updatedAt' => (int) round(microtime(true) * 1000),
    ];
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare(
        'INSERT INTO platform_kv (k, v, updated_by) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE v = VALUES(v), updated_by = VALUES(updated_by), updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute(['creator_news', $json, $uid > 0 ? $uid : null]);
    return [
        'text' => $text,
        'updatedAt' => $payload['updatedAt'],
        'updatedBy' => $uid > 0 ? $uid : null,
    ];
}

try {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    $canEdit = creator_news_is_editor($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $news = creator_news_load($pdo);
        echo json_encode([
            'ok' => true,
            'text' => $news['text'],
            'updatedAt' => $news['updatedAt'],
            'canEdit' => $canEdit,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$canEdit) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'forbidden', 'message' => 'Only the app creator can edit Creator\'s news.']);
            exit;
        }
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_json']);
            exit;
        }
        $text = isset($body['text']) ? (string) $body['text'] : '';
        $text = trim($text);
        if (function_exists('mb_strlen') && mb_strlen($text) > 2000) {
            $text = mb_substr($text, 0, 2000);
        } elseif (strlen($text) > 2000) {
            $text = substr($text, 0, 2000);
        }
        $saved = creator_news_save($pdo, $text, $uid);
        echo json_encode([
            'ok' => true,
            'text' => $saved['text'],
            'updatedAt' => $saved['updatedAt'],
            'canEdit' => true,
            'saved' => true,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
} catch (Throwable $e) {
    error_log('creator-news-api: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server', 'message' => $e->getMessage()]);
}
