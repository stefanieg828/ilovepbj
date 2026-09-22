<?php
/**
 * Per-user preferences API (syncs across devices for the same account).
 *
 * GET  → { ok, userId, prefs: { home_shortcuts?, home_tiles?, schedule_me_id?, onboarding_10min?, ... } }
 * POST JSON { home_shortcuts? | home_tiles? | schedule_me_id? | onboarding_10min? }
 *       → merges into users.prefs_json; last-write-wins by updatedAt when both sides send it
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

$uid = (int) ($_SESSION['user_id'] ?? 0);

// AUTH_BYPASS tester (user_id 0) — no DB row; local-only
if ($uid <= 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(['ok' => true, 'userId' => 0, 'prefs' => [], 'localOnly' => true]);
        exit;
    }
    http_response_code(200);
    echo json_encode(['ok' => true, 'userId' => 0, 'prefs' => [], 'localOnly' => true]);
    exit;
}

/** Allow-list of known catalog ids (defense in depth; client also filters). */
function pbj_prefs_allowed_shortcut_ids(): array {
    return [
        'prep', 'boh-open', 'cleaning', 'temps', 'recipes', 'tools',
        'foh-open', 'sidework', 'floor', 'reservations', 'pos', 'bar',
        'schedules', 'team', 'inventory', 'reports', 'compliance', 'ops',
        'announcements', 'shift-notes', 'dms', 'broadcasts',
        '86-board', '86-display', 'allergens', 'my-schedule',
    ];
}

/** Allowed home dashboard tile ids (order + hide). */
function pbj_prefs_allowed_tile_ids(): array {
    return [
        'star',
        'a2hs',
        'shortcuts',
        'list-complete',
        'stat-posts',
        'stat-handoffs',
        'stat-chats',
        'creator-news',
        'work-music',
        'platform-desk',
        'theme',
    ];
}

/**
 * Normalize home_tiles blob: { order: string[], hidden: string[], updatedAt: int }
 *
 * @param mixed $raw
 * @return array{order: string[], hidden: string[], updatedAt: int}|null
 */
function pbj_prefs_normalize_home_tiles($raw): ?array {
    if (!is_array($raw)) {
        return null;
    }
    $allowed = array_flip(pbj_prefs_allowed_tile_ids());
    $defaultOrder = pbj_prefs_allowed_tile_ids();

    $orderIn = isset($raw['order']) && is_array($raw['order']) ? $raw['order'] : [];
    $hiddenIn = isset($raw['hidden']) && is_array($raw['hidden']) ? $raw['hidden'] : [];
    $updatedAt = (int) ($raw['updatedAt'] ?? $raw['updated_at'] ?? 0);

    $order = [];
    foreach ($orderIn as $id) {
        if (!is_string($id) && !is_int($id)) {
            continue;
        }
        $id = (string) $id;
        if ($id === '' || !isset($allowed[$id]) || in_array($id, $order, true)) {
            continue;
        }
        $order[] = $id;
    }
    foreach ($defaultOrder as $id) {
        if (!in_array($id, $order, true)) {
            $order[] = $id;
        }
    }

    $hidden = [];
    foreach ($hiddenIn as $id) {
        if (!is_string($id) && !is_int($id)) {
            continue;
        }
        $id = (string) $id;
        if ($id === '' || !isset($allowed[$id]) || in_array($id, $hidden, true)) {
            continue;
        }
        $hidden[] = $id;
    }

    if ($updatedAt < 0) {
        $updatedAt = 0;
    }

    return ['order' => $order, 'hidden' => $hidden, 'updatedAt' => $updatedAt];
}

/**
 * Normalize schedule_me_id: { id: string, updatedAt: int }
 *
 * @param mixed $raw
 * @return array{id: string, updatedAt: int}|null
 */
function pbj_prefs_normalize_schedule_me($raw): ?array {
    $id = '';
    $updatedAt = 0;
    if (is_string($raw) || is_int($raw)) {
        $id = trim((string) $raw);
    } elseif (is_array($raw)) {
        $id = trim((string) ($raw['id'] ?? $raw['personId'] ?? ''));
        $updatedAt = (int) ($raw['updatedAt'] ?? $raw['updated_at'] ?? 0);
    } else {
        return null;
    }
    if (strlen($id) > 80) {
        $id = substr($id, 0, 80);
    }
    if ($updatedAt < 0) {
        $updatedAt = 0;
    }
    return ['id' => $id, 'updatedAt' => $updatedAt];
}

/**
 * Normalize home_shortcuts blob: { ids: string[], updatedAt: int }
 *
 * @param mixed $raw
 * @return array{ids: string[], updatedAt: int}|null
 */
function pbj_prefs_normalize_home_shortcuts($raw): ?array {
    $ids = [];
    $updatedAt = 0;

    if (is_array($raw) && array_keys($raw) === range(0, count($raw) - 1)) {
        // Legacy bare array
        $ids = $raw;
    } elseif (is_array($raw)) {
        if (isset($raw['ids']) && is_array($raw['ids'])) {
            $ids = $raw['ids'];
        }
        $updatedAt = (int) ($raw['updatedAt'] ?? $raw['updated_at'] ?? 0);
    } else {
        return null;
    }

    $allowed = array_flip(pbj_prefs_allowed_shortcut_ids());
    $clean = [];
    foreach ($ids as $id) {
        if (!is_string($id) && !is_int($id)) {
            continue;
        }
        $id = (string) $id;
        if ($id === '' || !isset($allowed[$id])) {
            continue;
        }
        if (in_array($id, $clean, true)) {
            continue;
        }
        $clean[] = $id;
        if (count($clean) >= 4) {
            break;
        }
    }

    if ($updatedAt < 0) {
        $updatedAt = 0;
    }

    return ['ids' => $clean, 'updatedAt' => $updatedAt];
}


/**
 * Normalize onboarding_10min blob for the first-10-minutes guided path.
 *
 * @param mixed $raw
 * @return array{started:bool,dismissed:bool,completed:bool,steps:array<string,bool>,updatedAt:int}|null
 */
function pbj_prefs_normalize_onboarding_10min($raw): ?array {
    if (!is_array($raw)) {
        return null;
    }
    $stepsIn = isset($raw['steps']) && is_array($raw['steps']) ? $raw['steps'] : [];
    $steps = [
        'recipe' => !empty($stepsIn['recipe']),
        'plate_cost' => !empty($stepsIn['plate_cost']),
        'menu_price' => !empty($stepsIn['menu_price']),
        'save_account' => !empty($stepsIn['save_account']),
    ];
    $updatedAt = (int) ($raw['updatedAt'] ?? $raw['updated_at'] ?? 0);
    if ($updatedAt < 0) {
        $updatedAt = 0;
    }
    $completed = !empty($raw['completed']);
    if ($steps['recipe'] && $steps['plate_cost'] && $steps['menu_price'] && $steps['save_account']) {
        $completed = true;
    }
    return [
        'started' => array_key_exists('started', $raw) ? !empty($raw['started']) : true,
        'dismissed' => !empty($raw['dismissed']),
        'completed' => $completed,
        'steps' => $steps,
        'updatedAt' => $updatedAt,
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $prefs = pbj_load_user_prefs($pdo, $uid);
        $out = ['ok' => true, 'userId' => $uid, 'prefs' => []];

        if (isset($prefs['home_shortcuts'])) {
            $hs = pbj_prefs_normalize_home_shortcuts($prefs['home_shortcuts']);
            if ($hs !== null) {
                $out['prefs']['home_shortcuts'] = $hs;
            }
        }
        if (isset($prefs['home_tiles'])) {
            $ht = pbj_prefs_normalize_home_tiles($prefs['home_tiles']);
            if ($ht !== null) {
                $out['prefs']['home_tiles'] = $ht;
            }
        }
        if (isset($prefs['schedule_me_id'])) {
            $me = pbj_prefs_normalize_schedule_me($prefs['schedule_me_id']);
            if ($me !== null) {
                $out['prefs']['schedule_me_id'] = $me;
            }
        }
        if (isset($prefs['onboarding_10min'])) {
            $ob = pbj_prefs_normalize_onboarding_10min($prefs['onboarding_10min']);
            if ($ob !== null) {
                $out['prefs']['onboarding_10min'] = $ob;
            }
        }

        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_json']);
            exit;
        }

        $patch = [];
        $current = pbj_load_user_prefs($pdo, $uid);
        $outPrefs = [];
        $conflictKept = null;

        if (array_key_exists('home_shortcuts', $body)) {
            $incoming = pbj_prefs_normalize_home_shortcuts($body['home_shortcuts']);
            if ($incoming === null) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'bad_home_shortcuts']);
                exit;
            }
            // Last-write-wins when client and server both have timestamps
            $existing = isset($current['home_shortcuts'])
                ? pbj_prefs_normalize_home_shortcuts($current['home_shortcuts'])
                : null;
            if (
                $existing
                && $existing['updatedAt'] > 0
                && $incoming['updatedAt'] > 0
                && $existing['updatedAt'] > $incoming['updatedAt']
            ) {
                $outPrefs['home_shortcuts'] = $existing;
                $conflictKept = 'server';
            } else {
                if ($incoming['updatedAt'] <= 0) {
                    $incoming['updatedAt'] = (int) round(microtime(true) * 1000);
                }
                $patch['home_shortcuts'] = $incoming;
            }
        }

        if (array_key_exists('home_tiles', $body)) {
            $incoming = pbj_prefs_normalize_home_tiles($body['home_tiles']);
            if ($incoming === null) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'bad_home_tiles']);
                exit;
            }
            $existing = isset($current['home_tiles'])
                ? pbj_prefs_normalize_home_tiles($current['home_tiles'])
                : null;
            if (
                $existing
                && $existing['updatedAt'] > 0
                && $incoming['updatedAt'] > 0
                && $existing['updatedAt'] > $incoming['updatedAt']
            ) {
                $outPrefs['home_tiles'] = $existing;
                $conflictKept = 'server';
            } else {
                if ($incoming['updatedAt'] <= 0) {
                    $incoming['updatedAt'] = (int) round(microtime(true) * 1000);
                }
                $patch['home_tiles'] = $incoming;
            }
        }

        if (array_key_exists('schedule_me_id', $body)) {
            $incoming = pbj_prefs_normalize_schedule_me($body['schedule_me_id']);
            if ($incoming === null) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'bad_schedule_me_id']);
                exit;
            }
            $existing = isset($current['schedule_me_id'])
                ? pbj_prefs_normalize_schedule_me($current['schedule_me_id'])
                : null;
            if (
                $existing
                && $existing['updatedAt'] > 0
                && $incoming['updatedAt'] > 0
                && $existing['updatedAt'] > $incoming['updatedAt']
            ) {
                $outPrefs['schedule_me_id'] = $existing;
                $conflictKept = 'server';
            } else {
                if ($incoming['updatedAt'] <= 0) {
                    $incoming['updatedAt'] = (int) round(microtime(true) * 1000);
                }
                $patch['schedule_me_id'] = $incoming;
            }
        }

        if (array_key_exists('onboarding_10min', $body)) {
            $incoming = pbj_prefs_normalize_onboarding_10min($body['onboarding_10min']);
            if ($incoming === null) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'bad_onboarding_10min']);
                exit;
            }
            $existing = isset($current['onboarding_10min'])
                ? pbj_prefs_normalize_onboarding_10min($current['onboarding_10min'])
                : null;
            if (
                $existing
                && $existing['updatedAt'] > 0
                && $incoming['updatedAt'] > 0
                && $existing['updatedAt'] > $incoming['updatedAt']
            ) {
                $outPrefs['onboarding_10min'] = $existing;
                $conflictKept = 'server';
            } else {
                if ($incoming['updatedAt'] <= 0) {
                    $incoming['updatedAt'] = (int) round(microtime(true) * 1000);
                }
                $patch['onboarding_10min'] = $incoming;
            }
        }

        if (!$patch && !$outPrefs) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'empty_patch']);
            exit;
        }

        $saved = $patch ? pbj_save_user_prefs($pdo, $uid, $patch) : $current;
        if (isset($saved['home_shortcuts']) && !isset($outPrefs['home_shortcuts'])) {
            $hs = pbj_prefs_normalize_home_shortcuts($saved['home_shortcuts']);
            if ($hs !== null) {
                $outPrefs['home_shortcuts'] = $hs;
            }
        }
        if (isset($saved['home_tiles']) && !isset($outPrefs['home_tiles'])) {
            $ht = pbj_prefs_normalize_home_tiles($saved['home_tiles']);
            if ($ht !== null) {
                $outPrefs['home_tiles'] = $ht;
            }
        }
        if (isset($saved['schedule_me_id']) && !isset($outPrefs['schedule_me_id'])) {
            $me = pbj_prefs_normalize_schedule_me($saved['schedule_me_id']);
            if ($me !== null) {
                $outPrefs['schedule_me_id'] = $me;
            }
        }
        if (isset($saved['onboarding_10min']) && !isset($outPrefs['onboarding_10min'])) {
            $ob = pbj_prefs_normalize_onboarding_10min($saved['onboarding_10min']);
            if ($ob !== null) {
                $outPrefs['onboarding_10min'] = $ob;
            }
        }

        echo json_encode([
            'ok' => true,
            'userId' => $uid,
            'prefs' => $outPrefs,
            'conflict' => $conflictKept !== null,
            'kept' => $conflictKept,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
} catch (Exception $e) {
    error_log('user-prefs-api: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server']);
}
