<?php
/**
 * POS connections JSON API.
 *
 * GET  ?action=status
 * POST action=toast_connect  { restaurantGuid, locationName? }
 * POST action=set_location   { provider, locationId, locationName? }
 * POST action=disconnect     { provider }
 * POST action=sync           { provider, days?: number }  — Square includes labor + soft PMIX
 * POST action=sync_all       { days?: number }           — all connected POS
 * POST action=sync_pmix      { provider?: square|toast, days?: number } — item mix only
 */
require_once __DIR__ . '/pos-config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    require_once __DIR__ . '/config.php';
}

if (!pos_user_can_manage($pdo)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

$rid = pos_resolve_restaurant_id($pdo);
if ($rid <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'no_house']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';
$input = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }
    if (!$input) {
        $input = $_POST;
    }
    if (!empty($input['action'])) {
        $action = (string) $input['action'];
    }
}

/**
 * @return array{start:string,end:string,daysBack:int}
 */
function pos_api_range(array $input): array {
    $daysBack = (int) ($input['days'] ?? 7);
    if ($daysBack < 1) {
        $daysBack = 1;
    }
    if ($daysBack > 90) {
        $daysBack = 90;
    }
    $end = date('Y-m-d');
    $start = date('Y-m-d', strtotime('-' . ($daysBack - 1) . ' days'));
    return ['start' => $start, 'end' => $end, 'daysBack' => $daysBack];
}

/**
 * Run one provider sync (sales + labor when available).
 * @return array<string,mixed>
 */
function pos_api_sync_provider(PDO $pdo, int $rid, string $provider, string $start, string $end): array {
    if ($provider === 'square') {
        $res = pos_square_sync_full($pdo, $rid, $start, $end);
    } elseif ($provider === 'toast') {
        $res = pos_toast_sync_full($pdo, $rid, $start, $end);
    } elseif ($provider === 'clover') {
        $res = pos_clover_sync_full($pdo, $rid, $start, $end);
    } else {
        return ['provider' => $provider, 'ok' => false, 'error' => 'bad_provider'];
    }
    if (empty($res['ok'])) {
        return array_merge(['provider' => $provider, 'ok' => false], $res);
    }
    if ($provider === 'square') {
        $conn = pos_get_connection($pdo, $rid, 'square');
        $meta = [];
        if ($conn && !empty($conn['meta_json'])) {
            $decoded = json_decode((string) $conn['meta_json'], true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }
        $meta['pending_sync'] = false;
        $meta['last_client_sync_at'] = date('Y-m-d H:i:s');
        pos_save_connection($pdo, $rid, 'square', ['meta_json' => $meta]);
    }
    $out = [
        'provider' => $provider,
        'ok' => true,
        'days' => $res['days'] ?? [],
        'count' => $res['count'] ?? 0,
        'laborDays' => $res['laborDays'] ?? [],
        'laborCount' => $res['laborCount'] ?? 0,
        'laborError' => $res['laborError'] ?? null,
        'laborHint' => $res['laborHint'] ?? null,
    ];
    if (!empty($res['compEntries'])) {
        $out['compEntries'] = $res['compEntries'];
        $out['compCount'] = $res['compCount'] ?? count($res['compEntries']);
    }
    if (!empty($res['pmixItems'])) {
        $out['pmixItems'] = $res['pmixItems'];
        $out['pmixByDay'] = $res['pmixByDay'] ?? [];
        $out['pmixCount'] = $res['pmixCount'] ?? count($res['pmixItems']);
        $out['pmixProvider'] = $res['pmixProvider'] ?? $provider;
    }
    if (!empty($res['pmixError'])) {
        $out['pmixError'] = $res['pmixError'];
    }
    return $out;
}

try {
    if ($action === 'status' || $action === '') {
        $conns = pos_list_connections($pdo, $rid);
        $active = [];
        $latestSync = null;
        $pendingSync = false;
        $lastWebhookAt = null;
        foreach ($conns as $c) {
            if (!empty($c['connected'])) {
                $active[] = $c['provider'];
            }
            if (!empty($c['last_sync_at'])) {
                if ($latestSync === null || strcmp((string) $c['last_sync_at'], $latestSync) > 0) {
                    $latestSync = (string) $c['last_sync_at'];
                }
            }
            $meta = is_array($c['meta'] ?? null) ? $c['meta'] : [];
            if (!empty($meta['pending_sync'])) {
                $pendingSync = true;
            }
            if (!empty($meta['last_webhook_at'])) {
                if ($lastWebhookAt === null || strcmp((string) $meta['last_webhook_at'], $lastWebhookAt) > 0) {
                    $lastWebhookAt = (string) $meta['last_webhook_at'];
                }
            }
        }
        echo json_encode([
            'ok' => true,
            'restaurantId' => $rid,
            'configured' => [
                'square' => pos_square_configured(),
                'toast' => pos_toast_configured(),
                'clover' => pos_clover_configured(),
            ],
            'squareEnv' => pos_load_secrets()['square']['environment'] ?? 'sandbox',
            'cloverEnv' => pos_load_secrets()['clover']['environment'] ?? 'sandbox',
            'redirectUri' => pos_square_redirect_uri(),
            'cloverRedirectUri' => pos_clover_redirect_uri(),
            'connections' => $conns,
            'activeProviders' => $active,
            'anyConnected' => count($active) > 0,
            'latestSyncAt' => $latestSync,
            'pendingSync' => $pendingSync,
            'lastWebhookAt' => $lastWebhookAt,
        ]);
        exit;
    }

    if ($action === 'toast_connect') {
        if (!pos_toast_configured()) {
            echo json_encode(['ok' => false, 'error' => 'toast_not_configured']);
            exit;
        }
        $guid = trim((string) ($input['restaurantGuid'] ?? $input['restaurant_guid'] ?? ''));
        $name = trim((string) ($input['locationName'] ?? $input['location_name'] ?? ''));
        if ($guid === '') {
            echo json_encode(['ok' => false, 'error' => 'restaurant_guid_required']);
            exit;
        }
        $res = pos_toast_connect($pdo, $rid, $guid, $name);
        echo json_encode($res);
        exit;
    }

    if ($action === 'set_location') {
        $provider = strtolower(trim((string) ($input['provider'] ?? '')));
        $locId = trim((string) ($input['locationId'] ?? $input['location_id'] ?? ''));
        $locName = trim((string) ($input['locationName'] ?? $input['location_name'] ?? ''));
        if (!in_array($provider, ['square', 'toast', 'clover'], true) || $locId === '') {
            echo json_encode(['ok' => false, 'error' => 'bad_request']);
            exit;
        }
        pos_save_connection($pdo, $rid, $provider, [
            'location_id' => $locId,
            'location_name' => $locName,
        ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'list_locations') {
        $provider = strtolower(trim((string) ($input['provider'] ?? $_GET['provider'] ?? 'square')));
        if ($provider === 'square') {
            echo json_encode(pos_square_list_locations($pdo, $rid));
            exit;
        }
        if ($provider === 'clover') {
            echo json_encode(pos_clover_merchant_info($pdo, $rid));
            exit;
        }
        echo json_encode(['ok' => false, 'error' => 'unsupported']);
        exit;
    }

    if ($action === 'disconnect') {
        $provider = strtolower(trim((string) ($input['provider'] ?? '')));
        if (!in_array($provider, ['square', 'toast', 'clover'], true)) {
            echo json_encode(['ok' => false, 'error' => 'bad_provider']);
            exit;
        }
        if ($provider === 'square' && pos_square_configured()) {
            $conn = pos_get_connection($pdo, $rid, 'square');
            if ($conn) {
                $token = pos_connection_access_token($conn);
                if ($token !== '') {
                    $s = pos_load_secrets()['square'];
                    pos_http_json('POST', pos_square_oauth_base() . '/oauth2/revoke', [
                        'Square-Version' => '2024-12-18',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Client ' . $s['application_secret'],
                    ], [
                        'client_id' => $s['application_id'],
                        'access_token' => $token,
                    ]);
                }
            }
        }
        pos_disconnect($pdo, $rid, $provider);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'sync') {
        $provider = strtolower(trim((string) ($input['provider'] ?? '')));
        $range = pos_api_range($input);
        $res = pos_api_sync_provider($pdo, $rid, $provider, $range['start'], $range['end']);
        if (empty($res['ok'])) {
            echo json_encode($res);
            exit;
        }
        echo json_encode(array_merge($res, [
            'start' => $range['start'],
            'end' => $range['end'],
        ]));
        exit;
    }

    if ($action === 'sync_all') {
        $range = pos_api_range($input);
        $conns = pos_list_connections($pdo, $rid);
        $providers = [];
        foreach ($conns as $c) {
            if (!empty($c['connected']) && in_array($c['provider'], ['square', 'toast', 'clover'], true)) {
                $providers[] = $c['provider'];
            }
        }
        if (!$providers) {
            echo json_encode([
                'ok' => false,
                'error' => 'none_connected',
                'hint' => 'Connect Square, Clover, or Toast first.',
            ]);
            exit;
        }

        $results = [];
        $allDays = [];
        $allLabor = [];
        $pmixLists = [];
        $pmixByDayLists = [];
        $salesCount = 0;
        $laborCount = 0;
        $anyOk = false;
        $errors = [];

        foreach ($providers as $prov) {
            $one = pos_api_sync_provider($pdo, $rid, $prov, $range['start'], $range['end']);
            $results[$prov] = $one;
            if (!empty($one['ok'])) {
                $anyOk = true;
                foreach ($one['days'] ?? [] as $d) {
                    $allDays[] = $d;
                }
                foreach ($one['laborDays'] ?? [] as $ld) {
                    $allLabor[] = $ld;
                }
                $salesCount += (int) ($one['count'] ?? 0);
                $laborCount += (int) ($one['laborCount'] ?? 0);
                if (!empty($one['pmixItems'])) {
                    $pmixLists[] = $one['pmixItems'];
                    foreach ($one['pmixByDay'] ?? [] as $day => $dayItems) {
                        if (!isset($pmixByDayLists[$day])) {
                            $pmixByDayLists[$day] = [];
                        }
                        $pmixByDayLists[$day][] = $dayItems;
                    }
                }
            } else {
                $errors[$prov] = $one['error'] ?? 'failed';
            }
        }

        // De-dupe sales by date keeping last provider's values (later providers win)
        $byDate = [];
        foreach ($allDays as $d) {
            if (!empty($d['date'])) {
                $byDate[$d['date']] = $d;
            }
        }
        $mergedDays = array_values($byDate);
        usort($mergedDays, static function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $labByDate = [];
        foreach ($allLabor as $ld) {
            if (!empty($ld['date'])) {
                $labByDate[$ld['date']] = $ld;
            }
        }
        $mergedLabor = array_values($labByDate);
        usort($mergedLabor, static function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $mergedPmix = pos_pmix_merge_item_lists($pmixLists);
        $mergedPmixByDay = [];
        foreach ($pmixByDayLists as $day => $lists) {
            $mergedPmixByDay[$day] = pos_pmix_merge_item_lists($lists);
        }
        ksort($mergedPmixByDay);

        echo json_encode([
            'ok' => $anyOk,
            'start' => $range['start'],
            'end' => $range['end'],
            'providers' => $providers,
            'results' => $results,
            'days' => $mergedDays,
            'count' => count($mergedDays),
            'laborDays' => $mergedLabor,
            'laborCount' => count($mergedLabor),
            'pmixItems' => $mergedPmix,
            'pmixByDay' => $mergedPmixByDay,
            'pmixCount' => count($mergedPmix),
            'errors' => $errors,
            'connections' => pos_list_connections($pdo, $rid),
        ]);
        exit;
    }

    if ($action === 'sync_pmix') {
        $range = pos_api_range($input);
        $provider = strtolower(trim((string) ($input['provider'] ?? '')));
        if ($provider === 'square') {
            $res = pos_square_sync_pmix($pdo, $rid, $range['start'], $range['end']);
        } elseif ($provider === 'toast') {
            $res = pos_toast_sync_pmix($pdo, $rid, $range['start'], $range['end']);
        } else {
            $res = pos_sync_pmix_connected($pdo, $rid, $range['start'], $range['end']);
        }
        echo json_encode(array_merge($res, [
            'start' => $range['start'],
            'end' => $range['end'],
            'daysBack' => $range['daysBack'],
        ]));
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'unknown_action']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server', 'message' => $e->getMessage()]);
}
