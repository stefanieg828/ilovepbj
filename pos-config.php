<?php
/**
 * POS OAuth + API helpers (Square seller OAuth, Toast partner client-credentials).
 */
require_once __DIR__ . '/config.php';

function pos_secret_search_dirs(): array {
    $dirs = ['/var/www/private/ilovepbj'];
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $parent = dirname((string) $_SERVER['DOCUMENT_ROOT']) . '/private/ilovepbj';
        if (!in_array($parent, $dirs, true)) {
            $dirs[] = $parent;
        }
    }
    $dirs[] = __DIR__;
    return $dirs;
}

/** @return array<string,mixed> */
function pos_load_secrets(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $defaults = [
        'token_cipher_key' => '',
        'square' => [
            'application_id' => '',
            'application_secret' => '',
            'environment' => 'sandbox',
            'redirect_uri' => '',
            'webhook_signature_key' => '',
        ],
        'toast' => [
            'client_id' => '',
            'client_secret' => '',
            'api_host' => 'ws-api.toasttab.com',
            'user_access_type' => 'TOAST_MACHINE_CLIENT',
        ],
        'clover' => [
            'app_id' => '',
            'app_secret' => '',
            'environment' => 'sandbox', // sandbox | production
            'redirect_uri' => '',
        ],
    ];
    foreach (pos_secret_search_dirs() as $dir) {
        $path = rtrim($dir, '/') . '/pos-secrets.local.php';
        if (!is_readable($path)) {
            continue;
        }
        $loaded = include $path;
        if (!is_array($loaded)) {
            continue;
        }
        if (!empty($loaded['token_cipher_key']) && is_string($loaded['token_cipher_key'])) {
            $v = trim($loaded['token_cipher_key']);
            if ($v !== '' && stripos($v, 'PASTE') === false && stripos($v, 'REPLACE') === false) {
                $defaults['token_cipher_key'] = $v;
            }
        }
        foreach (['square', 'toast', 'clover'] as $prov) {
            if (empty($loaded[$prov]) || !is_array($loaded[$prov])) {
                continue;
            }
            foreach ($loaded[$prov] as $k => $val) {
                if (!is_string($val) && !is_numeric($val)) {
                    continue;
                }
                $val = trim((string) $val);
                if ($val === '' || stripos($val, 'PASTE') !== false || stripos($val, 'REPLACE') !== false) {
                    continue;
                }
                $defaults[$prov][$k] = $val;
            }
        }
    }
    // Environment overrides
    if (getenv('SQUARE_APPLICATION_ID')) {
        $defaults['square']['application_id'] = getenv('SQUARE_APPLICATION_ID');
    }
    if (getenv('SQUARE_APPLICATION_SECRET')) {
        $defaults['square']['application_secret'] = getenv('SQUARE_APPLICATION_SECRET');
    }
    if (getenv('SQUARE_WEBHOOK_SIGNATURE_KEY')) {
        $defaults['square']['webhook_signature_key'] = getenv('SQUARE_WEBHOOK_SIGNATURE_KEY');
    }
    if (getenv('TOAST_CLIENT_ID')) {
        $defaults['toast']['client_id'] = getenv('TOAST_CLIENT_ID');
    }
    if (getenv('TOAST_CLIENT_SECRET')) {
        $defaults['toast']['client_secret'] = getenv('TOAST_CLIENT_SECRET');
    }
    if (getenv('CLOVER_APP_ID')) {
        $defaults['clover']['app_id'] = getenv('CLOVER_APP_ID');
    }
    if (getenv('CLOVER_APP_SECRET')) {
        $defaults['clover']['app_secret'] = getenv('CLOVER_APP_SECRET');
    }
    $cache = $defaults;
    return $cache;
}

function pos_public_base_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'ilovepbj.shop';
    return ($https ? 'https://' : 'http://') . $host;
}

function pos_square_configured(): bool {
    $s = pos_load_secrets()['square'];
    return $s['application_id'] !== '' && $s['application_secret'] !== '';
}

function pos_toast_configured(): bool {
    $t = pos_load_secrets()['toast'];
    return $t['client_id'] !== '' && $t['client_secret'] !== '';
}

function pos_clover_configured(): bool {
    $c = pos_load_secrets()['clover'];
    return $c['app_id'] !== '' && $c['app_secret'] !== '';
}

function pos_clover_is_sandbox(): bool {
    $env = pos_load_secrets()['clover']['environment'] ?? 'sandbox';
    return $env !== 'production';
}

/** OAuth authorize host (browser) */
function pos_clover_oauth_site(): string {
    return pos_clover_is_sandbox()
        ? 'https://sandbox.dev.clover.com'
        : 'https://www.clover.com';
}

/** REST API + token host */
function pos_clover_api_base(): string {
    return pos_clover_is_sandbox()
        ? 'https://apisandbox.dev.clover.com'
        : 'https://api.clover.com';
}

function pos_clover_redirect_uri(): string {
    $c = pos_load_secrets()['clover'];
    if (!empty($c['redirect_uri'])) {
        return $c['redirect_uri'];
    }
    // Same callback path as Square — provider is selected via session state
    return pos_public_base_url() . '/pos/oauth/callback';
}

function pos_square_oauth_base(): string {
    $env = pos_load_secrets()['square']['environment'] ?? 'sandbox';
    return $env === 'production'
        ? 'https://connect.squareup.com'
        : 'https://connect.squareupsandbox.com';
}

function pos_square_api_base(): string {
    return pos_square_oauth_base();
}

function pos_square_redirect_uri(): string {
    $s = pos_load_secrets()['square'];
    if (!empty($s['redirect_uri'])) {
        return $s['redirect_uri'];
    }
    return pos_public_base_url() . '/pos/oauth/callback';
}

/** Scopes for sales + labor bridge */
function pos_square_scopes(): array {
    return [
        'MERCHANT_PROFILE_READ',
        'PAYMENTS_READ',
        'ORDERS_READ',
        'EMPLOYEES_READ',
        'TIMECARDS_READ',
    ];
}

function pos_encrypt(string $plain): string {
    $key = pos_load_secrets()['token_cipher_key'] ?? '';
    if ($key === '' || strlen($key) < 16) {
        // Fallback: store base64 only (dev). Prefer setting token_cipher_key.
        return 'b64:' . base64_encode($plain);
    }
    $binKey = hash('sha256', $key, true);
    $iv = random_bytes(16);
    $cipher = openssl_encrypt($plain, 'AES-256-CBC', $binKey, OPENSSL_RAW_DATA, $iv);
    if ($cipher === false) {
        return 'b64:' . base64_encode($plain);
    }
    return 'enc:' . base64_encode($iv . $cipher);
}

function pos_decrypt(string $blob): string {
    if (str_starts_with($blob, 'b64:')) {
        $out = base64_decode(substr($blob, 4), true);
        return $out === false ? '' : $out;
    }
    if (!str_starts_with($blob, 'enc:')) {
        return $blob;
    }
    $raw = base64_decode(substr($blob, 4), true);
    if ($raw === false || strlen($raw) < 17) {
        return '';
    }
    $key = pos_load_secrets()['token_cipher_key'] ?? '';
    if ($key === '') {
        return '';
    }
    $binKey = hash('sha256', $key, true);
    $iv = substr($raw, 0, 16);
    $cipher = substr($raw, 16);
    $plain = openssl_decrypt($cipher, 'AES-256-CBC', $binKey, OPENSSL_RAW_DATA, $iv);
    return $plain === false ? '' : $plain;
}

function pos_ensure_tables(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_pos_connections (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT NOT NULL,
        provider VARCHAR(32) NOT NULL,
        merchant_id VARCHAR(128) NULL,
        location_id VARCHAR(128) NULL,
        location_name VARCHAR(255) NULL,
        access_token TEXT NULL,
        refresh_token TEXT NULL,
        token_expires_at DATETIME NULL,
        scopes TEXT NULL,
        meta_json LONGTEXT NULL,
        status VARCHAR(32) NOT NULL DEFAULT 'active',
        connected_at DATETIME NULL,
        last_sync_at DATETIME NULL,
        last_sync_status VARCHAR(64) NULL,
        last_error TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_rest_provider (restaurant_id, provider),
        KEY idx_provider (provider)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function pos_resolve_restaurant_id(PDO $pdo): int {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        return 0;
    }
    try {
        $own = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ? ORDER BY id DESC LIMIT 1');
        $own->execute([$uid]);
        $id = (int) ($own->fetchColumn() ?: 0);
        if ($id > 0) {
            return $id;
        }
    } catch (Exception $e) {
        // ignore
    }
    $stmt = $pdo->prepare('SELECT restaurant_id FROM user_restaurant WHERE user_id = ? ORDER BY joined_at ASC LIMIT 1');
    $stmt->execute([$uid]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function pos_user_can_manage(PDO $pdo): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    $uid = (int) $_SESSION['user_id'];
    $rid = pos_resolve_restaurant_id($pdo);
    if ($rid <= 0) {
        return false;
    }
    if (function_exists('pbj_permissions_is_omnipotent') && pbj_permissions_is_omnipotent($pdo, $uid)) {
        return true;
    }
    $role = 'foh';
    if (function_exists('pbj_permissions_user_role')) {
        $role = pbj_permissions_user_role($pdo, $uid, $rid);
    } else {
        $role = (string) ($_SESSION['role'] ?? 'foh');
    }
    $role = strtolower($role);
    if (in_array($role, ['owner', 'gm', 'admin', 'manager'], true)) {
        return true;
    }
    // also allow reports edit
    if (function_exists('pbj_permissions_load_matrix') && function_exists('pbj_permissions_grants_for_role')) {
        $matrix = pbj_permissions_load_matrix($pdo, $rid);
        $grants = pbj_permissions_grants_for_role($matrix, $role);
        if (!empty($grants['admin.reports.edit']) || !empty($grants['admin.reports.view'])) {
            return true;
        }
    }
    return false;
}

/** @return list<array<string,mixed>> */
function pos_list_connections(PDO $pdo, int $rid): array {
    pos_ensure_tables($pdo);
    $stmt = $pdo->prepare('SELECT * FROM restaurant_pos_connections WHERE restaurant_id = ? ORDER BY provider ASC');
    $stmt->execute([$rid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($rows as &$r) {
        unset($r['access_token'], $r['refresh_token']);
        $r['connected'] = ($r['status'] ?? '') === 'active' && !empty($r['connected_at']);
        $meta = [];
        if (!empty($r['meta_json'])) {
            $decoded = json_decode((string) $r['meta_json'], true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }
        $r['meta'] = $meta;
    }
    unset($r);
    return $rows;
}

/** @return array<string,mixed>|null */
function pos_get_connection(PDO $pdo, int $rid, string $provider): ?array {
    pos_ensure_tables($pdo);
    $stmt = $pdo->prepare('SELECT * FROM restaurant_pos_connections WHERE restaurant_id = ? AND provider = ? LIMIT 1');
    $stmt->execute([$rid, $provider]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function pos_save_connection(PDO $pdo, int $rid, string $provider, array $fields): void {
    pos_ensure_tables($pdo);
    $existing = pos_get_connection($pdo, $rid, $provider);
    $cols = [
        'merchant_id', 'location_id', 'location_name', 'access_token', 'refresh_token',
        'token_expires_at', 'scopes', 'meta_json', 'status', 'connected_at',
        'last_sync_at', 'last_sync_status', 'last_error',
    ];
    $data = [];
    foreach ($cols as $c) {
        if (array_key_exists($c, $fields)) {
            $data[$c] = $fields[$c];
        }
    }
    if (isset($data['access_token']) && $data['access_token'] !== null && $data['access_token'] !== '') {
        $data['access_token'] = pos_encrypt((string) $data['access_token']);
    }
    if (isset($data['refresh_token']) && $data['refresh_token'] !== null && $data['refresh_token'] !== '') {
        $data['refresh_token'] = pos_encrypt((string) $data['refresh_token']);
    }
    if (isset($data['meta_json']) && is_array($data['meta_json'])) {
        $data['meta_json'] = json_encode($data['meta_json']);
    }
    if ($existing) {
        $sets = [];
        $vals = [];
        foreach ($data as $k => $v) {
            $sets[] = "$k = ?";
            $vals[] = $v;
        }
        if (!$sets) {
            return;
        }
        $vals[] = $rid;
        $vals[] = $provider;
        $pdo->prepare('UPDATE restaurant_pos_connections SET ' . implode(', ', $sets) . ' WHERE restaurant_id = ? AND provider = ?')
            ->execute($vals);
    } else {
        $data['restaurant_id'] = $rid;
        $data['provider'] = $provider;
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }
        if (empty($data['connected_at'])) {
            $data['connected_at'] = date('Y-m-d H:i:s');
        }
        $keys = array_keys($data);
        $place = array_fill(0, count($keys), '?');
        $pdo->prepare('INSERT INTO restaurant_pos_connections (' . implode(',', $keys) . ') VALUES (' . implode(',', $place) . ')')
            ->execute(array_values($data));
    }
}

function pos_connection_access_token(array $row): string {
    $blob = (string) ($row['access_token'] ?? '');
    if ($blob === '') {
        return '';
    }
    return pos_decrypt($blob);
}

function pos_connection_refresh_token(array $row): string {
    $blob = (string) ($row['refresh_token'] ?? '');
    if ($blob === '') {
        return '';
    }
    return pos_decrypt($blob);
}

function pos_http_json(string $method, string $url, array $headers = [], $body = null, int $timeout = 30): array {
    $ch = curl_init($url);
    $hdrs = [];
    foreach ($headers as $k => $v) {
        $hdrs[] = $k . ': ' . $v;
    }
    if (!isset($headers['Content-Type']) && $body !== null) {
        $hdrs[] = 'Content-Type: application/json';
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_HTTPHEADER => $hdrs,
        CURLOPT_HEADER => true,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
    }
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    if ($raw === false) {
        return ['ok' => false, 'status' => 0, 'error' => $err ?: 'curl_failed', 'body' => null, 'headers' => []];
    }
    $headerText = substr($raw, 0, $headerSize);
    $bodyText = substr($raw, $headerSize);
    $json = json_decode($bodyText, true);
    return [
        'ok' => $code >= 200 && $code < 300,
        'status' => $code,
        'error' => $code >= 200 && $code < 300 ? null : ($json['message'] ?? $json['error'] ?? $bodyText),
        'body' => $json,
        'raw' => $bodyText,
        'headers' => $headerText,
    ];
}

// —— Square OAuth ——

function pos_square_authorize_url(string $state): string {
    $s = pos_load_secrets()['square'];
    // Square expects space-separated scopes; include redirect_uri so it matches the token exchange.
    $q = http_build_query([
        'client_id' => $s['application_id'],
        'scope' => implode(' ', pos_square_scopes()),
        'session' => 'false',
        'state' => $state,
        'redirect_uri' => pos_square_redirect_uri(),
    ], '', '&', PHP_QUERY_RFC3986);
    return pos_square_oauth_base() . '/oauth2/authorize?' . $q;
}

function pos_square_exchange_code(string $code): array {
    $s = pos_load_secrets()['square'];
    $res = pos_http_json('POST', pos_square_oauth_base() . '/oauth2/token', [
        'Square-Version' => '2024-12-18',
        'Content-Type' => 'application/json',
    ], [
        'client_id' => $s['application_id'],
        'client_secret' => $s['application_secret'],
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => pos_square_redirect_uri(),
    ]);
    if (!$res['ok'] || empty($res['body']['access_token'])) {
        return ['ok' => false, 'error' => is_string($res['error']) ? $res['error'] : 'token_exchange_failed', 'raw' => $res];
    }
    return ['ok' => true, 'token' => $res['body']];
}

function pos_square_refresh(PDO $pdo, int $rid): array {
    $conn = pos_get_connection($pdo, $rid, 'square');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $refresh = pos_connection_refresh_token($conn);
    if ($refresh === '') {
        return ['ok' => false, 'error' => 'no_refresh_token'];
    }
    $s = pos_load_secrets()['square'];
    $res = pos_http_json('POST', pos_square_oauth_base() . '/oauth2/token', [
        'Square-Version' => '2024-12-18',
        'Content-Type' => 'application/json',
    ], [
        'client_id' => $s['application_id'],
        'client_secret' => $s['application_secret'],
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh,
    ]);
    if (!$res['ok'] || empty($res['body']['access_token'])) {
        return ['ok' => false, 'error' => 'refresh_failed', 'raw' => $res];
    }
    $tok = $res['body'];
    $expires = null;
    if (!empty($tok['expires_at'])) {
        $expires = date('Y-m-d H:i:s', strtotime($tok['expires_at']));
    } elseif (!empty($tok['expires_in'])) {
        $expires = date('Y-m-d H:i:s', time() + (int) $tok['expires_in']);
    }
    pos_save_connection($pdo, $rid, 'square', [
        'access_token' => $tok['access_token'],
        'refresh_token' => $tok['refresh_token'] ?? $refresh,
        'token_expires_at' => $expires,
        'last_error' => null,
    ]);
    return ['ok' => true];
}

function pos_square_api(PDO $pdo, int $rid, string $method, string $path, $body = null): array {
    $conn = pos_get_connection($pdo, $rid, 'square');
    if (!$conn || ($conn['status'] ?? '') !== 'active') {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    // refresh if expiring within 2 days
    if (!empty($conn['token_expires_at'])) {
        $exp = strtotime($conn['token_expires_at']);
        if ($exp && $exp < time() + 2 * 86400) {
            pos_square_refresh($pdo, $rid);
            $conn = pos_get_connection($pdo, $rid, 'square');
        }
    }
    $token = pos_connection_access_token($conn ?: []);
    if ($token === '') {
        return ['ok' => false, 'error' => 'no_token'];
    }
    $url = pos_square_api_base() . $path;
    $res = pos_http_json($method, $url, [
        'Authorization' => 'Bearer ' . $token,
        'Square-Version' => '2024-12-18',
        'Content-Type' => 'application/json',
    ], $body);
    if ($res['status'] === 401) {
        $ref = pos_square_refresh($pdo, $rid);
        if ($ref['ok']) {
            $conn = pos_get_connection($pdo, $rid, 'square');
            $token = pos_connection_access_token($conn ?: []);
            $res = pos_http_json($method, $url, [
                'Authorization' => 'Bearer ' . $token,
                'Square-Version' => '2024-12-18',
                'Content-Type' => 'application/json',
            ], $body);
        }
    }
    return $res;
}

/**
 * Aggregate Square payments into daily sales rows for [startDate, endDate] inclusive (Y-m-d).
 * @return array{ok:bool,days?:list<array>,error?:string}
 */
function pos_square_sync_sales(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $conn = pos_get_connection($pdo, $rid, 'square');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $locationId = (string) ($conn['location_id'] ?? '');
    $begin = $startDate . 'T00:00:00Z';
    $end = date('Y-m-d', strtotime($endDate . ' +1 day')) . 'T00:00:00Z';

    $cursor = null;
    $byDay = [];
    do {
        $q = http_build_query(array_filter([
            'begin_time' => $begin,
            'end_time' => $end,
            'location_id' => $locationId ?: null,
            'cursor' => $cursor,
            'limit' => 100,
        ]));
        $res = pos_square_api($pdo, $rid, 'GET', '/v2/payments?' . $q);
        if (!$res['ok']) {
            return ['ok' => false, 'error' => is_string($res['error']) ? $res['error'] : 'payments_failed', 'raw' => $res];
        }
        $payments = $res['body']['payments'] ?? [];
        foreach ($payments as $p) {
            if (($p['status'] ?? '') !== 'COMPLETED') {
                continue;
            }
            $created = $p['created_at'] ?? '';
            if ($created === '') {
                continue;
            }
            $day = substr($created, 0, 10);
            if ($day < $startDate || $day > $endDate) {
                continue;
            }
            if (!isset($byDay[$day])) {
                $byDay[$day] = [
                    'date' => $day,
                    'gross_cents' => 0,
                    'net_cents' => 0,
                    'tax_cents' => 0,
                    'tip_cents' => 0,
                    'cash_cents' => 0,
                    'card_cents' => 0,
                    'other_cents' => 0,
                    'checks' => 0,
                ];
            }
            $total = (int) ($p['total_money']['amount'] ?? 0);
            $tip = (int) ($p['tip_money']['amount'] ?? 0);
            $appFee = (int) ($p['app_fee_money']['amount'] ?? 0);
            // total includes tip typically
            $byDay[$day]['gross_cents'] += $total;
            $byDay[$day]['tip_cents'] += $tip;
            $byDay[$day]['net_cents'] += max(0, $total - $tip);
            $byDay[$day]['checks'] += 1;
            $sourceType = strtoupper((string) ($p['source_type'] ?? ''));
            if ($sourceType === 'CASH') {
                $byDay[$day]['cash_cents'] += $total;
            } elseif (in_array($sourceType, ['CARD', 'CARD_PRESENT', 'CARD_ON_FILE'], true) || !empty($p['card_details'])) {
                $byDay[$day]['card_cents'] += $total;
            } else {
                $byDay[$day]['other_cents'] += $total;
            }
        }
        $cursor = $res['body']['cursor'] ?? null;
    } while ($cursor);

    $days = [];
    foreach ($byDay as $day => $agg) {
        $days[] = [
            'date' => $day,
            'source' => 'pos',
            'posProvider' => 'square',
            'gross' => round($agg['gross_cents'] / 100, 2),
            'net' => round($agg['net_cents'] / 100, 2),
            'tax' => round($agg['tax_cents'] / 100, 2),
            'tips' => round($agg['tip_cents'] / 100, 2),
            'covers' => '',
            'checks' => $agg['checks'],
            'tenderCash' => round($agg['cash_cents'] / 100, 2),
            'tenderCard' => round($agg['card_cents'] / 100, 2),
            'tenderOther' => round($agg['other_cents'] / 100, 2),
            'labor' => '',
            'notes' => 'Synced from Square',
        ];
    }
    usort($days, static function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    pos_save_connection($pdo, $rid, 'square', [
        'last_sync_at' => date('Y-m-d H:i:s'),
        'last_sync_status' => 'ok',
        'last_error' => null,
    ]);

    return ['ok' => true, 'days' => $days, 'count' => count($days)];
}

function pos_square_list_locations(PDO $pdo, int $rid): array {
    $res = pos_square_api($pdo, $rid, 'GET', '/v2/locations');
    if (!$res['ok']) {
        return ['ok' => false, 'error' => $res['error'] ?? 'locations_failed', 'locations' => []];
    }
    $locs = [];
    foreach ($res['body']['locations'] ?? [] as $loc) {
        if (($loc['status'] ?? '') === 'INACTIVE') {
            continue;
        }
        $locs[] = [
            'id' => $loc['id'] ?? '',
            'name' => $loc['name'] ?? ($loc['id'] ?? ''),
            'timezone' => $loc['timezone'] ?? '',
        ];
    }
    return ['ok' => true, 'locations' => $locs];
}

/**
 * Map Square team members id => display name (best effort).
 * @return array<string,string>
 */
function pos_square_team_name_map(PDO $pdo, int $rid): array {
    $map = [];
    $cursor = null;
    do {
        $body = [
            'query' => [
                'filter' => [
                    'status' => 'ACTIVE',
                ],
            ],
            'limit' => 100,
        ];
        if ($cursor) {
            $body['cursor'] = $cursor;
        }
        $res = pos_square_api($pdo, $rid, 'POST', '/v2/team-members/search', $body);
        if (!$res['ok']) {
            break;
        }
        foreach ($res['body']['team_members'] ?? [] as $tm) {
            $id = (string) ($tm['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $given = trim((string) ($tm['given_name'] ?? ''));
            $family = trim((string) ($tm['family_name'] ?? ''));
            $name = trim($given . ' ' . $family);
            if ($name === '') {
                $name = (string) ($tm['email_address'] ?? $id);
            }
            $map[$id] = $name;
        }
        $cursor = $res['body']['cursor'] ?? null;
    } while ($cursor);
    return $map;
}

/**
 * Aggregate Square timecards/shifts into labor day rows for [startDate, endDate].
 * Shape matches pbj_admin_labor_v2 (entries + cost + hours).
 * @return array{ok:bool,laborDays?:list<array>,error?:string,count?:int}
 */
function pos_square_sync_labor(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $conn = pos_get_connection($pdo, $rid, 'square');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $locationId = (string) ($conn['location_id'] ?? '');
    $begin = $startDate . 'T00:00:00Z';
    $end = date('Y-m-d', strtotime($endDate . ' +1 day')) . 'T00:00:00Z';

    $names = pos_square_team_name_map($pdo, $rid);
    $filter = [
        'start' => [
            'start_at' => $begin,
            'end_at' => $end,
        ],
    ];
    if ($locationId !== '') {
        $filter['location_ids'] = [$locationId];
    }

    $records = [];
    $cursor = null;
    $usedEndpoint = 'timecards';

    // Prefer modern Timecards API; fall back to legacy Shifts if needed.
    do {
        $body = [
            'query' => ['filter' => $filter],
            'limit' => 200,
        ];
        if ($cursor) {
            $body['cursor'] = $cursor;
        }
        $res = pos_square_api($pdo, $rid, 'POST', '/v2/labor/timecards/search', $body);
        if (!$res['ok'] && $cursor === null) {
            $usedEndpoint = 'shifts';
            break;
        }
        if (!$res['ok']) {
            return ['ok' => false, 'error' => is_string($res['error']) ? $res['error'] : 'timecards_failed', 'raw' => $res];
        }
        foreach ($res['body']['timecards'] ?? [] as $tc) {
            if (is_array($tc)) {
                $records[] = $tc;
            }
        }
        $cursor = $res['body']['cursor'] ?? null;
    } while ($cursor);

    if ($usedEndpoint === 'shifts') {
        $cursor = null;
        do {
            $body = [
                'query' => ['filter' => $filter],
                'limit' => 200,
            ];
            if ($cursor) {
                $body['cursor'] = $cursor;
            }
            $res = pos_square_api($pdo, $rid, 'POST', '/v2/labor/shifts/search', $body);
            if (!$res['ok']) {
                $msg = is_string($res['error']) ? $res['error'] : 'shifts_failed';
                // Soft-fail labor so sales sync still works
                pos_save_connection($pdo, $rid, 'square', [
                    'last_error' => substr('labor: ' . $msg, 0, 500),
                ]);
                return [
                    'ok' => false,
                    'error' => $msg,
                    'hint' => 'Square TIMECARDS_READ scope required. Reconnect Square if labor was not granted.',
                    'raw' => $res,
                ];
            }
            foreach ($res['body']['shifts'] ?? [] as $sh) {
                if (is_array($sh)) {
                    $records[] = $sh;
                }
            }
            $cursor = $res['body']['cursor'] ?? null;
        } while ($cursor);
    }

    /** @var array<string,array{date:string,entries:list<array>,hours:float,cost:float}> $byDay */
    $byDay = [];
    foreach ($records as $tc) {
        $startAt = (string) ($tc['start_at'] ?? '');
        if ($startAt === '') {
            continue;
        }
        $endAt = (string) ($tc['end_at'] ?? '');
        $status = strtoupper((string) ($tc['status'] ?? 'CLOSED'));
        // Open punches: count through now for partial day
        if ($endAt === '' && $status === 'OPEN') {
            $endAt = gmdate('c');
        }
        if ($endAt === '') {
            continue;
        }
        $day = substr($startAt, 0, 10);
        if ($day < $startDate || $day > $endDate) {
            continue;
        }

        $breakMins = 0;
        foreach ($tc['breaks'] ?? [] as $br) {
            if (!is_array($br)) {
                continue;
            }
            // unpaid breaks reduce paid hours
            $paid = !empty($br['is_paid']);
            if ($paid) {
                continue;
            }
            $bStart = $br['start_at'] ?? null;
            $bEnd = $br['end_at'] ?? null;
            if ($bStart && $bEnd) {
                $secs = max(0, strtotime((string) $bEnd) - strtotime((string) $bStart));
                $breakMins += (int) round($secs / 60);
            } elseif (!empty($br['expected_duration'])) {
                // ISO-8601 duration e.g. PT30M
                if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/i', (string) $br['expected_duration'], $m)) {
                    $breakMins += ((int) ($m[1] ?? 0)) * 60 + ((int) ($m[2] ?? 0));
                }
            }
        }

        $startTs = strtotime($startAt);
        $endTs = strtotime($endAt);
        if ($startTs === false || $endTs === false || $endTs <= $startTs) {
            continue;
        }
        $hours = max(0, ($endTs - $startTs) / 3600 - ($breakMins / 60));
        $hours = round($hours * 100) / 100;

        $wage = $tc['wage'] ?? [];
        $title = (string) ($wage['title'] ?? '');
        $rateCents = (int) ($wage['hourly_rate']['amount'] ?? 0);
        $wageRate = $rateCents > 0 ? round($rateCents / 100, 2) : null;
        $laborCost = $wageRate !== null ? round($wageRate * $hours, 2) : null;

        $tmId = (string) ($tc['team_member_id'] ?? $tc['employee_id'] ?? '');
        $name = $names[$tmId] ?? ($tmId !== '' ? 'Team ' . substr($tmId, -4) : 'Team member');

        // Local clock times HH:MM for labor UI
        $clockIn = date('H:i', $startTs);
        $clockOut = date('H:i', $endTs);

        if (!isset($byDay[$day])) {
            $byDay[$day] = [
                'date' => $day,
                'entries' => [],
                'hours' => 0.0,
                'cost' => 0.0,
            ];
        }
        $byDay[$day]['hours'] += $hours;
        if ($laborCost !== null) {
            $byDay[$day]['cost'] += $laborCost;
        }
        $byDay[$day]['entries'][] = [
            'name' => $name,
            'role' => $title,
            'actualStart' => $clockIn,
            'actualEnd' => $clockOut,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breakMins' => $breakMins,
            'timeSource' => 'pos',
            'wageRate' => $wageRate !== null ? $wageRate : '',
            'laborCost' => $laborCost !== null ? $laborCost : '',
            'externalId' => $tmId,
            'punchId' => (string) ($tc['id'] ?? ''),
            'notes' => $status === 'OPEN' ? 'Open punch (partial)' : '',
        ];
    }

    $laborDays = [];
    foreach ($byDay as $day => $agg) {
        $laborDays[] = [
            'date' => $day,
            'source' => 'pos',
            'posProvider' => 'square',
            'costSource' => 'pos',
            'hours' => round($agg['hours'], 2),
            'cost' => round($agg['cost'], 2),
            'foh' => '',
            'boh' => '',
            'notes' => 'Synced from Square timecards',
            'entries' => $agg['entries'],
        ];
    }
    usort($laborDays, static function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    $meta = [];
    if (!empty($conn['meta_json'])) {
        $decoded = json_decode((string) $conn['meta_json'], true);
        if (is_array($decoded)) {
            $meta = $decoded;
        }
    }
    $meta['labor_last_sync_at'] = date('Y-m-d H:i:s');
    $meta['labor_days'] = count($laborDays);

    pos_save_connection($pdo, $rid, 'square', [
        'meta_json' => $meta,
        'last_error' => null,
    ]);

    return ['ok' => true, 'laborDays' => $laborDays, 'count' => count($laborDays)];
}

/**
 * Merge labor $ onto sales day rows by date.
 * @param list<array> $salesDays
 * @param list<array> $laborDays
 * @return list<array>
 */
function pos_patch_sales_labor(array $salesDays, array $laborDays): array {
    $costByDate = [];
    foreach ($laborDays as $ld) {
        if (!empty($ld['date']) && isset($ld['cost']) && $ld['cost'] !== '' && $ld['cost'] !== null) {
            $costByDate[$ld['date']] = $ld['cost'];
        }
    }
    foreach ($salesDays as &$d) {
        if (isset($costByDate[$d['date'] ?? ''])) {
            $d['labor'] = $costByDate[$d['date']];
        }
    }
    unset($d);
    return $salesDays;
}

/**
 * Combine sales + labor sync results (sales failure is hard-fail).
 * @return array{ok:bool,days?:list,laborDays?:list,count?:int,laborCount?:int,error?:string,laborError?:string,laborHint?:?string}
 */
function pos_combine_sales_labor(array $sales, array $labor): array {
    if (empty($sales['ok'])) {
        return $sales;
    }
    $laborDays = [];
    $laborError = null;
    $laborHint = null;
    if (!empty($labor['ok'])) {
        $laborDays = $labor['laborDays'] ?? [];
        $sales['days'] = pos_patch_sales_labor($sales['days'] ?? [], $laborDays);
    } else {
        $laborError = is_string($labor['error'] ?? null) ? $labor['error'] : 'labor_sync_failed';
        $laborHint = $labor['hint'] ?? null;
    }
    return [
        'ok' => true,
        'days' => $sales['days'] ?? [],
        'count' => $sales['count'] ?? count($sales['days'] ?? []),
        'laborDays' => $laborDays,
        'laborCount' => count($laborDays),
        'laborError' => $laborError,
        'laborHint' => $laborHint,
    ];
}

/**
 * Full Square sync: payments → sales days, timecards → labor days.
 * Soft-attaches comps + item-level PMIX (does not fail the sync).
 */
function pos_square_sync_full(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $sales = pos_square_sync_sales($pdo, $rid, $startDate, $endDate);
    $labor = pos_square_sync_labor($pdo, $rid, $startDate, $endDate);
    $combined = pos_combine_sales_labor($sales, $labor);
    // Soft-attach comps/voids from refunds (does not fail the sync)
    try {
        $comps = pos_square_sync_comps($pdo, $rid, $startDate, $endDate);
        if (!empty($comps['ok'])) {
            $combined['compEntries'] = $comps['entries'] ?? [];
            $combined['compCount'] = $comps['count'] ?? 0;
        } else {
            $combined['compError'] = $comps['error'] ?? null;
        }
    } catch (Throwable $e) {
        $combined['compError'] = $e->getMessage();
    }
    // Soft-attach product mix for ideal food cost / menu matrix
    try {
        $pmix = pos_square_sync_pmix($pdo, $rid, $startDate, $endDate);
        if (!empty($pmix['ok'])) {
            $combined['pmixItems'] = $pmix['items'] ?? [];
            $combined['pmixByDay'] = $pmix['byDay'] ?? [];
            $combined['pmixCount'] = $pmix['count'] ?? 0;
            $combined['pmixProvider'] = 'square';
        } else {
            $combined['pmixError'] = $pmix['error'] ?? null;
        }
    } catch (Throwable $e) {
        $combined['pmixError'] = $e->getMessage();
    }
    return $combined;
}

/**
 * Pull Square refunds as comps/voids entries for the browser comps log.
 * @return array{ok:bool,entries?:list,count?:int,error?:string}
 */
function pos_square_sync_comps(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $conn = pos_get_connection($pdo, $rid, 'square');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $locationId = (string) ($conn['location_id'] ?? '');
    $begin = $startDate . 'T00:00:00Z';
    $end = date('Y-m-d', strtotime($endDate . ' +1 day')) . 'T00:00:00Z';
    $cursor = null;
    $entries = [];
    do {
        $q = http_build_query(array_filter([
            'begin_time' => $begin,
            'end_time' => $end,
            'location_id' => $locationId ?: null,
            'cursor' => $cursor,
            'limit' => 100,
        ]));
        $res = pos_square_api($pdo, $rid, 'GET', '/v2/refunds?' . $q);
        if (!$res['ok']) {
            // Older accounts may lack refunds scope — soft fail
            return ['ok' => false, 'error' => is_string($res['error']) ? $res['error'] : 'refunds_failed'];
        }
        foreach ($res['body']['refunds'] ?? [] as $rf) {
            if (!is_array($rf)) {
                continue;
            }
            $status = strtoupper((string) ($rf['status'] ?? ''));
            if ($status && $status !== 'COMPLETED' && $status !== 'PENDING') {
                continue;
            }
            $created = (string) ($rf['created_at'] ?? '');
            if ($created === '') {
                continue;
            }
            $day = substr($created, 0, 10);
            if ($day < $startDate || $day > $endDate) {
                continue;
            }
            $amountCents = (int) ($rf['amount_money']['amount'] ?? 0);
            $amount = round($amountCents / 100, 2);
            if ($amount <= 0) {
                continue;
            }
            $reason = (string) ($rf['reason'] ?? 'Square refund');
            $entries[] = [
                'id' => 'sq_ref_' . ($rf['id'] ?? uniqid()),
                'date' => $day,
                'type' => 'void',
                'amount' => $amount,
                'reason' => $reason !== '' ? $reason : 'Square refund',
                'notes' => 'Synced from Square refund ' . ($rf['id'] ?? ''),
                'source' => 'pos',
                'posProvider' => 'square',
                'externalId' => (string) ($rf['id'] ?? ''),
                'updatedAt' => time() * 1000,
            ];
        }
        $cursor = $res['body']['cursor'] ?? null;
    } while ($cursor);

    return ['ok' => true, 'entries' => $entries, 'count' => count($entries)];
}

/**
 * Normalize item name for PMIX aggregation.
 */
function pos_pmix_norm_key(string $name): string {
    $n = strtolower(trim($name));
    // Drop punctuation (& in PB&J, etc.) so variants collapse to the same key
    $n = preg_replace('/[^a-z0-9]+/', '', $n) ?? $n;
    return $n;
}

/**
 * Add a sold line into an aggregate map (by normalized name).
 * @param array<string,array> $agg
 */
function pos_pmix_add(array &$agg, string $name, float $qty, ?float $unitPrice, string $provider, string $day = ''): void {
    if ($qty <= 0) {
        return;
    }
    $name = trim($name);
    if ($name === '') {
        $name = 'Item';
    }
    $key = pos_pmix_norm_key($name);
    if ($key === '') {
        $key = 'item';
    }
    if (!isset($agg[$key])) {
        $agg[$key] = [
            'name' => $name,
            'qty' => 0.0,
            'sales' => 0.0,
            'priceSamples' => 0.0,
            'priceN' => 0,
            'provider' => $provider,
        ];
    }
    $agg[$key]['qty'] += $qty;
    if ($unitPrice !== null && !is_nan($unitPrice) && $unitPrice >= 0) {
        $agg[$key]['sales'] += $unitPrice * $qty;
        $agg[$key]['priceSamples'] += $unitPrice;
        $agg[$key]['priceN']++;
    }
    if ($day !== '') {
        // keep first provider label; multi-provider merges later
        $agg[$key]['provider'] = $provider;
    }
}

/**
 * @param array<string,array> $agg
 * @return list<array{name:string,qty:float,unitPrice:?float,sales$:?float,provider:string}>
 */
function pos_pmix_finalize(array $agg): array {
    $items = [];
    foreach ($agg as $row) {
        $qty = round((float) $row['qty'], 3);
        if ($qty <= 0) {
            continue;
        }
        $unit = null;
        if (!empty($row['priceN'])) {
            $unit = round(((float) $row['priceSamples']) / (int) $row['priceN'], 4);
        } elseif ($qty > 0 && (float) $row['sales'] > 0) {
            $unit = round(((float) $row['sales']) / $qty, 4);
        }
        $sales = (float) $row['sales'];
        $items[] = [
            'name' => (string) $row['name'],
            'qty' => $qty,
            'unitPrice' => $unit,
            'sales$' => $sales > 0 ? round($sales, 2) : null,
            'provider' => (string) ($row['provider'] ?? ''),
        ];
    }
    usort($items, static function ($a, $b) {
        return $b['qty'] <=> $a['qty'];
    });
    return $items;
}

/**
 * Merge multiple PMIX item lists by name.
 * @param list<list<array>> $lists
 * @return list<array>
 */
function pos_pmix_merge_item_lists(array $lists): array {
    $agg = [];
    foreach ($lists as $list) {
        if (!is_array($list)) {
            continue;
        }
        foreach ($list as $it) {
            if (!is_array($it)) {
                continue;
            }
            pos_pmix_add(
                $agg,
                (string) ($it['name'] ?? ''),
                (float) ($it['qty'] ?? 0),
                isset($it['unitPrice']) && $it['unitPrice'] !== '' && $it['unitPrice'] !== null
                    ? (float) $it['unitPrice'] : null,
                (string) ($it['provider'] ?? 'pos')
            );
        }
    }
    return pos_pmix_finalize($agg);
}

/**
 * Square Orders Search → product mix (qty sold by line item name).
 * Soft-fails if ORDERS_READ missing or no location.
 * @return array{ok:bool,items?:list,byDay?:array,count?:int,error?:string,hint?:string}
 */
function pos_square_sync_pmix(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $conn = pos_get_connection($pdo, $rid, 'square');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $locationId = (string) ($conn['location_id'] ?? '');
    if ($locationId === '') {
        $locs = pos_square_list_locations($pdo, $rid);
        if (!empty($locs['ok']) && !empty($locs['locations'][0]['id'])) {
            $locationId = (string) $locs['locations'][0]['id'];
        }
    }
    if ($locationId === '') {
        return [
            'ok' => false,
            'error' => 'no_location',
            'hint' => 'Pick a Square location on POS Connections before pulling item mix.',
        ];
    }

    $begin = $startDate . 'T00:00:00Z';
    $end = date('Y-m-d', strtotime($endDate . ' +1 day')) . 'T00:00:00Z';
    $cursor = null;
    $agg = [];
    $byDayAgg = [];
    $orderCount = 0;

    do {
        $body = [
            'location_ids' => [$locationId],
            'query' => [
                'filter' => [
                    'date_time_filter' => [
                        'closed_at' => [
                            'start_at' => $begin,
                            'end_at' => $end,
                        ],
                    ],
                    'state_filter' => [
                        'states' => ['COMPLETED'],
                    ],
                ],
            ],
            'limit' => 100,
        ];
        if ($cursor) {
            $body['cursor'] = $cursor;
        }
        $res = pos_square_api($pdo, $rid, 'POST', '/v2/orders/search', $body);
        if (!$res['ok']) {
            $msg = is_string($res['error']) ? $res['error'] : 'orders_search_failed';
            return [
                'ok' => false,
                'error' => $msg,
                'hint' => 'Square item mix needs ORDERS_READ. Reconnect Square if this keeps failing.',
                'status' => $res['status'] ?? 0,
            ];
        }
        foreach ($res['body']['orders'] ?? [] as $order) {
            if (!is_array($order)) {
                continue;
            }
            $closed = (string) ($order['closed_at'] ?? $order['created_at'] ?? '');
            if ($closed === '') {
                continue;
            }
            $day = substr($closed, 0, 10);
            if ($day < $startDate || $day > $endDate) {
                continue;
            }
            $orderCount++;
            if (!isset($byDayAgg[$day])) {
                $byDayAgg[$day] = [];
            }
            foreach ($order['line_items'] ?? [] as $li) {
                if (!is_array($li)) {
                    continue;
                }
                $name = trim((string) ($li['name'] ?? ''));
                if ($name === '') {
                    $name = (string) ($li['catalog_object_id'] ?? 'Item');
                }
                $qty = (float) ($li['quantity'] ?? 1);
                if ($qty <= 0) {
                    continue;
                }
                $unit = null;
                if (isset($li['base_price_money']['amount'])) {
                    $unit = ((int) $li['base_price_money']['amount']) / 100.0;
                } elseif (isset($li['gross_sales_money']['amount']) && $qty > 0) {
                    $unit = (((int) $li['gross_sales_money']['amount']) / 100.0) / $qty;
                } elseif (isset($li['total_money']['amount']) && $qty > 0) {
                    $unit = (((int) $li['total_money']['amount']) / 100.0) / $qty;
                }
                pos_pmix_add($agg, $name, $qty, $unit, 'square', $day);
                pos_pmix_add($byDayAgg[$day], $name, $qty, $unit, 'square', $day);
            }
        }
        $cursor = $res['body']['cursor'] ?? null;
    } while ($cursor);

    $items = pos_pmix_finalize($agg);
    $byDay = [];
    foreach ($byDayAgg as $day => $dayAgg) {
        $dayItems = pos_pmix_finalize($dayAgg);
        if ($dayItems) {
            $byDay[$day] = $dayItems;
        }
    }
    ksort($byDay);

    return [
        'ok' => true,
        'provider' => 'square',
        'items' => $items,
        'byDay' => $byDay,
        'count' => count($items),
        'orderCount' => $orderCount,
        'start' => $startDate,
        'end' => $endDate,
    ];
}

/**
 * Toast order selections → product mix.
 * @return array{ok:bool,items?:list,byDay?:array,count?:int,error?:string,hint?:string}
 */
function pos_toast_sync_pmix(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $auth = pos_toast_ensure_token($pdo, $rid);
    if (!$auth['ok']) {
        return $auth;
    }
    $t = pos_load_secrets()['toast'];
    $host = rtrim($t['api_host'] ?: 'ws-api.toasttab.com', '/');
    $guid = $auth['restaurant_guid'];
    if ($guid === '') {
        return ['ok' => false, 'error' => 'missing_restaurant_guid'];
    }

    $startIso = $startDate . 'T00:00:00.000Z';
    $endIso = date('Y-m-d', strtotime($endDate . ' +1 day')) . 'T00:00:00.000Z';
    $url = 'https://' . $host . '/orders/v2/ordersBulk?' . http_build_query([
        'startDate' => $startIso,
        'endDate' => $endIso,
        'pageSize' => 100,
    ]);
    $res = pos_http_json('GET', $url, [
        'Authorization' => 'Bearer ' . $auth['token'],
        'Toast-Restaurant-External-ID' => $guid,
        'Content-Type' => 'application/json',
    ]);
    if (!$res['ok']) {
        $msg = is_string($res['error']) ? $res['error'] : 'toast_orders_failed';
        return [
            'ok' => false,
            'error' => $msg,
            'hint' => 'Toast item mix needs Orders API access (same as sales sync).',
            'status' => $res['status'] ?? 0,
        ];
    }

    $orders = $res['body'];
    if (isset($orders['orders']) && is_array($orders['orders'])) {
        $orders = $orders['orders'];
    }
    if (!is_array($orders)) {
        $orders = [];
    }

    $agg = [];
    $byDayAgg = [];
    $orderCount = 0;

    foreach ($orders as $order) {
        if (!is_array($order)) {
            continue;
        }
        if (!empty($order['voided']) || (($order['voidDate'] ?? null) !== null && ($order['voidDate'] ?? '') !== '')) {
            continue;
        }
        $created = $order['closedDate'] ?? $order['modifiedDate'] ?? $order['openedDate'] ?? $order['createdDate'] ?? '';
        if ($created === '') {
            continue;
        }
        $day = substr((string) $created, 0, 10);
        if ($day < $startDate || $day > $endDate) {
            continue;
        }
        $orderCount++;
        if (!isset($byDayAgg[$day])) {
            $byDayAgg[$day] = [];
        }
        $checks = $order['checks'] ?? [];
        if (!is_array($checks)) {
            $checks = [];
        }
        foreach ($checks as $check) {
            if (!is_array($check) || !empty($check['voided'])) {
                continue;
            }
            foreach ($check['selections'] ?? [] as $sel) {
                if (!is_array($sel) || !empty($sel['voided'])) {
                    continue;
                }
                // Skip pure modifiers if they have no display as item (keep all with qty)
                $name = trim((string) ($sel['displayName'] ?? $sel['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $qty = (float) ($sel['quantity'] ?? 1);
                if ($qty <= 0) {
                    continue;
                }
                $unit = null;
                foreach (['price', 'receiptLinePrice', 'preDiscountPrice', 'unitPrice'] as $pk) {
                    if (isset($sel[$pk]) && $sel[$pk] !== '' && $sel[$pk] !== null) {
                        $unit = (float) $sel[$pk];
                        break;
                    }
                }
                pos_pmix_add($agg, $name, $qty, $unit, 'toast', $day);
                pos_pmix_add($byDayAgg[$day], $name, $qty, $unit, 'toast', $day);
            }
        }
    }

    $items = pos_pmix_finalize($agg);
    $byDay = [];
    foreach ($byDayAgg as $day => $dayAgg) {
        $dayItems = pos_pmix_finalize($dayAgg);
        if ($dayItems) {
            $byDay[$day] = $dayItems;
        }
    }
    ksort($byDay);

    return [
        'ok' => true,
        'provider' => 'toast',
        'items' => $items,
        'byDay' => $byDay,
        'count' => count($items),
        'orderCount' => $orderCount,
        'start' => $startDate,
        'end' => $endDate,
    ];
}

/**
 * Pull PMIX from all connected Square/Toast providers and merge.
 * @return array{ok:bool,items?:list,byDay?:array,results?:array,providers?:list,error?:string}
 */
function pos_sync_pmix_connected(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $conns = pos_list_connections($pdo, $rid);
    $providers = [];
    foreach ($conns as $c) {
        if (empty($c['connected'])) {
            continue;
        }
        $p = (string) ($c['provider'] ?? '');
        if (in_array($p, ['square', 'toast'], true)) {
            $providers[] = $p;
        }
    }
    if (!$providers) {
        return [
            'ok' => false,
            'error' => 'none_connected',
            'hint' => 'Connect Square or Toast on POS Connections to pull item mix.',
        ];
    }

    $results = [];
    $lists = [];
    $byDayLists = [];
    $anyOk = false;
    $errors = [];

    foreach ($providers as $prov) {
        if ($prov === 'square') {
            $one = pos_square_sync_pmix($pdo, $rid, $startDate, $endDate);
        } else {
            $one = pos_toast_sync_pmix($pdo, $rid, $startDate, $endDate);
        }
        $results[$prov] = $one;
        if (!empty($one['ok'])) {
            $anyOk = true;
            $lists[] = $one['items'] ?? [];
            foreach ($one['byDay'] ?? [] as $day => $dayItems) {
                if (!isset($byDayLists[$day])) {
                    $byDayLists[$day] = [];
                }
                $byDayLists[$day][] = $dayItems;
            }
        } else {
            $errors[$prov] = $one['error'] ?? 'failed';
        }
    }

    $merged = pos_pmix_merge_item_lists($lists);
    $byDay = [];
    foreach ($byDayLists as $day => $dayList) {
        $byDay[$day] = pos_pmix_merge_item_lists($dayList);
    }
    ksort($byDay);

    return [
        'ok' => $anyOk,
        'items' => $merged,
        'byDay' => $byDay,
        'count' => count($merged),
        'providers' => $providers,
        'results' => $results,
        'errors' => $errors,
        'start' => $startDate,
        'end' => $endDate,
    ];
}

// —— Toast partner client-credentials ——

function pos_toast_authenticate(): array {
    $t = pos_load_secrets()['toast'];
    if ($t['client_id'] === '' || $t['client_secret'] === '') {
        return ['ok' => false, 'error' => 'toast_not_configured'];
    }
    $host = rtrim($t['api_host'] ?: 'ws-api.toasttab.com', '/');
    $url = 'https://' . $host . '/authentication/v1/authentication/login';
    $res = pos_http_json('POST', $url, [
        'Content-Type' => 'application/json',
    ], [
        'clientId' => $t['client_id'],
        'clientSecret' => $t['client_secret'],
        'userAccessType' => $t['user_access_type'] ?: 'TOAST_MACHINE_CLIENT',
    ]);
    if (!$res['ok']) {
        return ['ok' => false, 'error' => is_string($res['error']) ? $res['error'] : 'toast_auth_failed', 'raw' => $res];
    }
    $token = $res['body']['token']['accessToken']
        ?? $res['body']['accessToken']
        ?? $res['body']['token']
        ?? null;
    if (!$token || !is_string($token)) {
        // partner response shape varies
        if (!empty($res['body']['token']) && is_array($res['body']['token'])) {
            $token = $res['body']['token']['accessToken'] ?? null;
        }
    }
    if (!$token) {
        return ['ok' => false, 'error' => 'toast_no_token', 'raw' => $res['body']];
    }
    $expiresIn = (int) ($res['body']['token']['expiresIn'] ?? $res['body']['expiresIn'] ?? 3600);
    return [
        'ok' => true,
        'access_token' => $token,
        'expires_in' => $expiresIn,
        'raw' => $res['body'],
    ];
}

/**
 * Connect Toast for a restaurant by restaurant GUID (external ID).
 * Validates credentials by authenticating and storing the restaurant GUID.
 */
function pos_toast_connect(PDO $pdo, int $rid, string $restaurantGuid, string $locationName = ''): array {
    $auth = pos_toast_authenticate();
    if (!$auth['ok']) {
        return $auth;
    }
    $expires = date('Y-m-d H:i:s', time() + (int) ($auth['expires_in'] ?? 3600) - 60);
    pos_save_connection($pdo, $rid, 'toast', [
        'merchant_id' => $restaurantGuid,
        'location_id' => $restaurantGuid,
        'location_name' => $locationName !== '' ? $locationName : 'Toast restaurant',
        'access_token' => $auth['access_token'],
        'refresh_token' => '', // client-credentials re-auth
        'token_expires_at' => $expires,
        'scopes' => 'partner',
        'status' => 'active',
        'connected_at' => date('Y-m-d H:i:s'),
        'meta_json' => ['restaurantGuid' => $restaurantGuid],
        'last_error' => null,
    ]);
    return ['ok' => true];
}

function pos_toast_ensure_token(PDO $pdo, int $rid): array {
    $conn = pos_get_connection($pdo, $rid, 'toast');
    if (!$conn || ($conn['status'] ?? '') !== 'active') {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $needRefresh = true;
    if (!empty($conn['token_expires_at'])) {
        $exp = strtotime($conn['token_expires_at']);
        if ($exp && $exp > time() + 120) {
            $needRefresh = false;
        }
    }
    if ($needRefresh) {
        $auth = pos_toast_authenticate();
        if (!$auth['ok']) {
            return $auth;
        }
        $expires = date('Y-m-d H:i:s', time() + (int) ($auth['expires_in'] ?? 3600) - 60);
        pos_save_connection($pdo, $rid, 'toast', [
            'access_token' => $auth['access_token'],
            'token_expires_at' => $expires,
            'last_error' => null,
        ]);
        $conn = pos_get_connection($pdo, $rid, 'toast');
    }
    $token = pos_connection_access_token($conn ?: []);
    if ($token === '') {
        return ['ok' => false, 'error' => 'no_token'];
    }
    return [
        'ok' => true,
        'token' => $token,
        'restaurant_guid' => (string) ($conn['location_id'] ?? $conn['merchant_id'] ?? ''),
    ];
}

/**
 * Pull orders for date range and aggregate to daily sales.
 * Uses Toast orders bulk API when available; returns clear error if partner scopes missing.
 */
function pos_toast_sync_sales(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $auth = pos_toast_ensure_token($pdo, $rid);
    if (!$auth['ok']) {
        return $auth;
    }
    $t = pos_load_secrets()['toast'];
    $host = rtrim($t['api_host'] ?: 'ws-api.toasttab.com', '/');
    $guid = $auth['restaurant_guid'];
    if ($guid === '') {
        return ['ok' => false, 'error' => 'missing_restaurant_guid'];
    }

    // Toast orders: GET /orders/v2/ordersBulk?startDate=&endDate= (ISO dates)
    // Some environments use businessDate. We try bulk first.
    $startIso = $startDate . 'T00:00:00.000Z';
    $endIso = date('Y-m-d', strtotime($endDate . ' +1 day')) . 'T00:00:00.000Z';
    $url = 'https://' . $host . '/orders/v2/ordersBulk?' . http_build_query([
        'startDate' => $startIso,
        'endDate' => $endIso,
        'pageSize' => 100,
    ]);
    $res = pos_http_json('GET', $url, [
        'Authorization' => 'Bearer ' . $auth['token'],
        'Toast-Restaurant-External-ID' => $guid,
        'Content-Type' => 'application/json',
    ]);

    if (!$res['ok']) {
        // Partner scope may block — surface helpful message
        $msg = is_string($res['error']) ? $res['error'] : 'toast_orders_failed';
        pos_save_connection($pdo, $rid, 'toast', [
            'last_sync_at' => date('Y-m-d H:i:s'),
            'last_sync_status' => 'error',
            'last_error' => substr($msg, 0, 500),
        ]);
        return [
            'ok' => false,
            'error' => $msg,
            'hint' => 'Toast Orders API requires partner approval and restaurant access. Confirm client credentials and restaurant GUID.',
            'status' => $res['status'] ?? 0,
        ];
    }

    $orders = $res['body'];
    if (isset($orders['orders']) && is_array($orders['orders'])) {
        $orders = $orders['orders'];
    }
    if (!is_array($orders)) {
        $orders = [];
    }

    $byDay = [];
    foreach ($orders as $order) {
        if (!is_array($order)) {
            continue;
        }
        // skip voided
        if (!empty($order['voided']) || (($order['voidDate'] ?? null) !== null && ($order['voidDate'] ?? '') !== '')) {
            continue;
        }
        $created = $order['closedDate'] ?? $order['modifiedDate'] ?? $order['openedDate'] ?? $order['createdDate'] ?? '';
        if ($created === '') {
            continue;
        }
        $day = substr((string) $created, 0, 10);
        if ($day < $startDate || $day > $endDate) {
            continue;
        }
        if (!isset($byDay[$day])) {
            $byDay[$day] = [
                'date' => $day,
                'net' => 0.0,
                'gross' => 0.0,
                'tax' => 0.0,
                'tips' => 0.0,
                'checks' => 0,
            ];
        }
        $checks = $order['checks'] ?? [];
        if (!is_array($checks) || !$checks) {
            // amount fields at order level sometimes
            $amt = (float) ($order['totalAmount'] ?? $order['amount'] ?? 0);
            $byDay[$day]['net'] += $amt;
            $byDay[$day]['gross'] += $amt;
            $byDay[$day]['checks'] += 1;
            continue;
        }
        foreach ($checks as $check) {
            if (!is_array($check) || !empty($check['voided'])) {
                continue;
            }
            $total = (float) ($check['totalAmount'] ?? 0);
            $tax = (float) ($check['taxAmount'] ?? 0);
            $tip = 0.0;
            foreach ($check['payments'] ?? [] as $pay) {
                if (is_array($pay)) {
                    $tip += (float) ($pay['tipAmount'] ?? 0);
                }
            }
            $byDay[$day]['gross'] += $total;
            $byDay[$day]['net'] += max(0, $total - $tax);
            $byDay[$day]['tax'] += $tax;
            $byDay[$day]['tips'] += $tip;
            $byDay[$day]['checks'] += 1;
        }
    }

    $days = [];
    foreach ($byDay as $day => $agg) {
        $days[] = [
            'date' => $day,
            'source' => 'pos',
            'posProvider' => 'toast',
            'gross' => round($agg['gross'], 2),
            'net' => round($agg['net'], 2),
            'tax' => round($agg['tax'], 2),
            'tips' => round($agg['tips'], 2),
            'covers' => '',
            'checks' => $agg['checks'],
            'tenderCash' => '',
            'tenderCard' => '',
            'tenderOther' => '',
            'labor' => '',
            'notes' => 'Synced from Toast',
        ];
    }
    usort($days, static function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    pos_save_connection($pdo, $rid, 'toast', [
        'last_sync_at' => date('Y-m-d H:i:s'),
        'last_sync_status' => 'ok',
        'last_error' => null,
    ]);

    return ['ok' => true, 'days' => $days, 'count' => count($days)];
}

/**
 * Toast labor: GET /labor/v1/timeEntries (+ employees/jobs for names).
 * @return array{ok:bool,laborDays?:list<array>,error?:string,count?:int,hint?:string}
 */
function pos_toast_sync_labor(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $auth = pos_toast_ensure_token($pdo, $rid);
    if (!$auth['ok']) {
        return $auth;
    }
    $t = pos_load_secrets()['toast'];
    $host = rtrim($t['api_host'] ?: 'ws-api.toasttab.com', '/');
    $guid = $auth['restaurant_guid'];
    if ($guid === '') {
        return ['ok' => false, 'error' => 'missing_restaurant_guid'];
    }

    $headers = [
        'Authorization' => 'Bearer ' . $auth['token'],
        'Toast-Restaurant-External-ID' => $guid,
        'Content-Type' => 'application/json',
    ];

    $empNames = [];
    $jobTitles = [];
    $empRes = pos_http_json('GET', 'https://' . $host . '/labor/v1/employees', $headers);
    if ($empRes['ok'] && is_array($empRes['body'])) {
        $list = $empRes['body'];
        if (isset($list['employees']) && is_array($list['employees'])) {
            $list = $list['employees'];
        }
        foreach ($list as $emp) {
            if (!is_array($emp)) {
                continue;
            }
            $eid = (string) ($emp['guid'] ?? '');
            if ($eid === '') {
                continue;
            }
            $name = trim((string) (($emp['firstName'] ?? '') . ' ' . ($emp['lastName'] ?? '')));
            if ($name === '') {
                $name = (string) ($emp['email'] ?? $eid);
            }
            $empNames[$eid] = $name;
        }
    }
    $jobRes = pos_http_json('GET', 'https://' . $host . '/labor/v1/jobs', $headers);
    if ($jobRes['ok'] && is_array($jobRes['body'])) {
        $list = $jobRes['body'];
        if (isset($list['jobs']) && is_array($list['jobs'])) {
            $list = $list['jobs'];
        }
        foreach ($list as $job) {
            if (!is_array($job)) {
                continue;
            }
            $jid = (string) ($job['guid'] ?? '');
            if ($jid !== '') {
                $jobTitles[$jid] = (string) ($job['title'] ?? $job['name'] ?? '');
            }
        }
    }

    $startIso = $startDate . 'T00:00:00.000-0000';
    $endIso = date('Y-m-d', strtotime($endDate . ' +1 day')) . 'T00:00:00.000-0000';
    $url = 'https://' . $host . '/labor/v1/timeEntries?' . http_build_query([
        'startDate' => $startIso,
        'endDate' => $endIso,
        'includeMissedBreaks' => 'false',
    ]);
    $res = pos_http_json('GET', $url, $headers);
    if (!$res['ok']) {
        $msg = is_string($res['error']) ? $res['error'] : 'toast_labor_failed';
        return [
            'ok' => false,
            'error' => $msg,
            'hint' => 'Toast Labor API requires partner labor scope and restaurant access.',
            'status' => $res['status'] ?? 0,
        ];
    }

    $entries = $res['body'];
    if (isset($entries['timeEntries']) && is_array($entries['timeEntries'])) {
        $entries = $entries['timeEntries'];
    }
    if (!is_array($entries)) {
        $entries = [];
    }

    /** @var array<string,array{date:string,entries:list,hours:float,cost:float}> $byDay */
    $byDay = [];
    foreach ($entries as $te) {
        if (!is_array($te) || !empty($te['deleted'])) {
            continue;
        }
        $inDate = (string) ($te['inDate'] ?? '');
        if ($inDate === '') {
            continue;
        }
        $outDate = $te['outDate'] ?? null;
        $outStr = ($outDate !== null && $outDate !== '') ? (string) $outDate : gmdate('c');
        $open = ($outDate === null || $outDate === '');

        $day = '';
        if (!empty($te['businessDate'])) {
            $bd = preg_replace('/\D/', '', (string) $te['businessDate']);
            if (is_string($bd) && strlen($bd) === 8) {
                $day = substr($bd, 0, 4) . '-' . substr($bd, 4, 2) . '-' . substr($bd, 6, 2);
            }
        }
        if ($day === '') {
            $day = substr($inDate, 0, 10);
        }
        if ($day < $startDate || $day > $endDate) {
            continue;
        }

        $breakMins = 0;
        foreach ($te['breaks'] ?? [] as $br) {
            if (!is_array($br) || !empty($br['missed']) || !empty($br['paid'])) {
                continue;
            }
            $bIn = $br['inDate'] ?? null;
            $bOut = $br['outDate'] ?? null;
            if ($bIn && $bOut) {
                $secs = max(0, strtotime((string) $bOut) - strtotime((string) $bIn));
                $breakMins += (int) round($secs / 60);
            }
        }

        $reg = (float) ($te['regularHours'] ?? 0);
        $ot = (float) ($te['overtimeHours'] ?? 0);
        $hours = $reg + $ot;
        if ($hours <= 0) {
            $startTs = strtotime($inDate);
            $endTs = strtotime($outStr);
            if ($startTs && $endTs && $endTs > $startTs) {
                $hours = max(0, ($endTs - $startTs) / 3600 - ($breakMins / 60));
            }
        }
        $hours = round($hours * 100) / 100;

        $wageRate = isset($te['hourlyWage']) ? (float) $te['hourlyWage'] : null;
        $laborCost = ($wageRate !== null) ? round($wageRate * $hours, 2) : null;

        $empId = (string) ($te['employeeReference']['guid'] ?? '');
        $jobId = (string) ($te['jobReference']['guid'] ?? '');
        $name = $empNames[$empId] ?? ($empId !== '' ? 'Team ' . substr($empId, 0, 6) : 'Team member');
        $role = $jobTitles[$jobId] ?? '';

        $startTs = strtotime($inDate) ?: time();
        $endTs = strtotime($outStr) ?: time();
        $clockIn = date('H:i', $startTs);
        $clockOut = date('H:i', $endTs);

        if (!isset($byDay[$day])) {
            $byDay[$day] = ['date' => $day, 'entries' => [], 'hours' => 0.0, 'cost' => 0.0];
        }
        $byDay[$day]['hours'] += $hours;
        if ($laborCost !== null) {
            $byDay[$day]['cost'] += $laborCost;
        }
        $byDay[$day]['entries'][] = [
            'name' => $name,
            'role' => $role,
            'actualStart' => $clockIn,
            'actualEnd' => $clockOut,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breakMins' => $breakMins,
            'timeSource' => 'pos',
            'wageRate' => $wageRate !== null ? $wageRate : '',
            'laborCost' => $laborCost !== null ? $laborCost : '',
            'externalId' => $empId,
            'punchId' => (string) ($te['guid'] ?? ''),
            'notes' => $open ? 'Open punch (partial)' : '',
        ];
    }

    $laborDays = [];
    foreach ($byDay as $day => $agg) {
        $laborDays[] = [
            'date' => $day,
            'source' => 'pos',
            'posProvider' => 'toast',
            'costSource' => 'pos',
            'hours' => round($agg['hours'], 2),
            'cost' => round($agg['cost'], 2),
            'foh' => '',
            'boh' => '',
            'notes' => 'Synced from Toast time entries',
            'entries' => $agg['entries'],
        ];
    }
    usort($laborDays, static function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    $conn = pos_get_connection($pdo, $rid, 'toast');
    $meta = [];
    if ($conn && !empty($conn['meta_json'])) {
        $decoded = json_decode((string) $conn['meta_json'], true);
        if (is_array($decoded)) {
            $meta = $decoded;
        }
    }
    $meta['labor_last_sync_at'] = date('Y-m-d H:i:s');
    $meta['labor_days'] = count($laborDays);
    pos_save_connection($pdo, $rid, 'toast', ['meta_json' => $meta, 'last_error' => null]);

    return ['ok' => true, 'laborDays' => $laborDays, 'count' => count($laborDays)];
}

function pos_toast_sync_full(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $sales = pos_toast_sync_sales($pdo, $rid, $startDate, $endDate);
    $labor = pos_toast_sync_labor($pdo, $rid, $startDate, $endDate);
    $combined = pos_combine_sales_labor($sales, $labor);
    try {
        $pmix = pos_toast_sync_pmix($pdo, $rid, $startDate, $endDate);
        if (!empty($pmix['ok'])) {
            $combined['pmixItems'] = $pmix['items'] ?? [];
            $combined['pmixByDay'] = $pmix['byDay'] ?? [];
            $combined['pmixCount'] = $pmix['count'] ?? 0;
            $combined['pmixProvider'] = 'toast';
        } else {
            $combined['pmixError'] = $pmix['error'] ?? null;
        }
    } catch (Throwable $e) {
        $combined['pmixError'] = $e->getMessage();
    }
    return $combined;
}

// —— Clover OAuth v2 ——

function pos_clover_authorize_url(string $state): string {
    $c = pos_load_secrets()['clover'];
    $q = http_build_query([
        'client_id' => $c['app_id'],
        'redirect_uri' => pos_clover_redirect_uri(),
        'state' => $state,
        'response_type' => 'code',
    ], '', '&', PHP_QUERY_RFC3986);
    return pos_clover_oauth_site() . '/oauth/v2/authorize?' . $q;
}

function pos_clover_exchange_code(string $code): array {
    $c = pos_load_secrets()['clover'];
    $res = pos_http_json('POST', pos_clover_api_base() . '/oauth/v2/token', [
        'Content-Type' => 'application/json',
    ], [
        'client_id' => $c['app_id'],
        'client_secret' => $c['app_secret'],
        'code' => $code,
    ]);
    if (!$res['ok'] || empty($res['body']['access_token'])) {
        return ['ok' => false, 'error' => is_string($res['error']) ? $res['error'] : 'token_exchange_failed', 'raw' => $res];
    }
    return ['ok' => true, 'token' => $res['body']];
}

function pos_clover_refresh(PDO $pdo, int $rid): array {
    $conn = pos_get_connection($pdo, $rid, 'clover');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $refresh = pos_connection_refresh_token($conn);
    if ($refresh === '') {
        return ['ok' => false, 'error' => 'no_refresh_token'];
    }
    $c = pos_load_secrets()['clover'];
    $res = pos_http_json('POST', pos_clover_api_base() . '/oauth/v2/refresh', [
        'Content-Type' => 'application/json',
    ], [
        'client_id' => $c['app_id'],
        'refresh_token' => $refresh,
    ]);
    // Some environments also want client_secret on refresh
    if (!$res['ok'] || empty($res['body']['access_token'])) {
        $res = pos_http_json('POST', pos_clover_api_base() . '/oauth/v2/refresh', [
            'Content-Type' => 'application/json',
        ], [
            'client_id' => $c['app_id'],
            'client_secret' => $c['app_secret'],
            'refresh_token' => $refresh,
        ]);
    }
    if (!$res['ok'] || empty($res['body']['access_token'])) {
        return ['ok' => false, 'error' => 'refresh_failed', 'raw' => $res];
    }
    $tok = $res['body'];
    $expires = null;
    if (!empty($tok['access_token_expiration'])) {
        $exp = $tok['access_token_expiration'];
        // Clover may return ms epoch or seconds
        if (is_numeric($exp)) {
            $ts = (int) $exp;
            if ($ts > 20000000000) {
                $ts = (int) floor($ts / 1000);
            }
            $expires = date('Y-m-d H:i:s', $ts);
        }
    } elseif (!empty($tok['expires_in'])) {
        $expires = date('Y-m-d H:i:s', time() + (int) $tok['expires_in']);
    }
    pos_save_connection($pdo, $rid, 'clover', [
        'access_token' => $tok['access_token'],
        'refresh_token' => $tok['refresh_token'] ?? $refresh,
        'token_expires_at' => $expires,
        'last_error' => null,
    ]);
    return ['ok' => true];
}

function pos_clover_api(PDO $pdo, int $rid, string $method, string $path, $body = null): array {
    $conn = pos_get_connection($pdo, $rid, 'clover');
    if (!$conn || ($conn['status'] ?? '') !== 'active') {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    if (!empty($conn['token_expires_at'])) {
        $exp = strtotime($conn['token_expires_at']);
        if ($exp && $exp < time() + 120) {
            pos_clover_refresh($pdo, $rid);
            $conn = pos_get_connection($pdo, $rid, 'clover');
        }
    }
    $token = pos_connection_access_token($conn ?: []);
    if ($token === '') {
        return ['ok' => false, 'error' => 'no_token'];
    }
    $url = pos_clover_api_base() . $path;
    $res = pos_http_json($method, $url, [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ], $body);
    if ($res['status'] === 401) {
        $ref = pos_clover_refresh($pdo, $rid);
        if (!empty($ref['ok'])) {
            $conn = pos_get_connection($pdo, $rid, 'clover');
            $token = pos_connection_access_token($conn ?: []);
            $res = pos_http_json($method, $url, [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ], $body);
        }
    }
    return $res;
}

/**
 * Aggregate Clover payments into daily sales for [startDate, endDate] inclusive.
 * @return array{ok:bool,days?:list<array>,error?:string,count?:int}
 */
function pos_clover_sync_sales(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $conn = pos_get_connection($pdo, $rid, 'clover');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $merchantId = (string) ($conn['merchant_id'] ?? $conn['location_id'] ?? '');
    if ($merchantId === '') {
        return ['ok' => false, 'error' => 'missing_merchant_id'];
    }

    $startMs = strtotime($startDate . ' 00:00:00') * 1000;
    $endMs = (strtotime($endDate . ' 00:00:00') + 86400) * 1000 - 1;
    $byDay = [];
    $offset = 0;
    $limit = 100;
    $maxPages = 50;

    for ($page = 0; $page < $maxPages; $page++) {
        // Clover filter syntax: createdTime>=ms
        $path = '/v3/merchants/' . rawurlencode($merchantId) . '/payments?'
            . 'filter=' . rawurlencode('createdTime>=' . $startMs)
            . '&filter=' . rawurlencode('createdTime<=' . $endMs)
            . '&limit=' . $limit
            . '&offset=' . $offset
            . '&expand=tender';
        $res = pos_clover_api($pdo, $rid, 'GET', $path);
        if (!$res['ok']) {
            $msg = is_string($res['error']) ? $res['error'] : 'payments_failed';
            pos_save_connection($pdo, $rid, 'clover', [
                'last_sync_at' => date('Y-m-d H:i:s'),
                'last_sync_status' => 'error',
                'last_error' => substr($msg, 0, 500),
            ]);
            return ['ok' => false, 'error' => $msg, 'raw' => $res];
        }
        $elements = $res['body']['elements'] ?? [];
        if (!is_array($elements) || !$elements) {
            break;
        }
        foreach ($elements as $p) {
            if (!is_array($p)) {
                continue;
            }
            // Skip refunds / failed
            $result = strtoupper((string) ($p['result'] ?? 'SUCCESS'));
            if ($result !== '' && $result !== 'SUCCESS') {
                continue;
            }
            $created = $p['createdTime'] ?? $p['clientCreatedTime'] ?? null;
            if ($created === null || $created === '') {
                continue;
            }
            $ts = is_numeric($created) ? (int) $created : strtotime((string) $created) * 1000;
            if ($ts > 20000000000) {
                // already ms
            } elseif ($ts > 1000000000) {
                $ts = $ts * 1000;
            }
            $day = date('Y-m-d', (int) floor($ts / 1000));
            if ($day < $startDate || $day > $endDate) {
                continue;
            }
            if (!isset($byDay[$day])) {
                $byDay[$day] = [
                    'date' => $day,
                    'gross_cents' => 0,
                    'net_cents' => 0,
                    'tax_cents' => 0,
                    'tip_cents' => 0,
                    'cash_cents' => 0,
                    'card_cents' => 0,
                    'other_cents' => 0,
                    'checks' => 0,
                ];
            }
            $amount = (int) ($p['amount'] ?? 0); // cents, may include tax
            $tip = (int) ($p['tipAmount'] ?? 0);
            $tax = (int) ($p['taxAmount'] ?? 0);
            $byDay[$day]['gross_cents'] += $amount + $tip;
            $byDay[$day]['tip_cents'] += $tip;
            $byDay[$day]['tax_cents'] += $tax;
            $byDay[$day]['net_cents'] += max(0, $amount - $tax);
            $byDay[$day]['checks'] += 1;

            $tenderLabel = '';
            if (!empty($p['tender']) && is_array($p['tender'])) {
                $tenderLabel = strtoupper((string) ($p['tender']['label'] ?? $p['tender']['labelKey'] ?? ''));
            }
            $totalWithTip = $amount + $tip;
            if (str_contains($tenderLabel, 'CASH')) {
                $byDay[$day]['cash_cents'] += $totalWithTip;
            } elseif (
                str_contains($tenderLabel, 'CREDIT')
                || str_contains($tenderLabel, 'DEBIT')
                || str_contains($tenderLabel, 'CARD')
                || str_contains($tenderLabel, 'VISA')
                || str_contains($tenderLabel, 'MC')
                || str_contains($tenderLabel, 'AMEX')
            ) {
                $byDay[$day]['card_cents'] += $totalWithTip;
            } else {
                $byDay[$day]['other_cents'] += $totalWithTip;
            }
        }
        if (count($elements) < $limit) {
            break;
        }
        $offset += $limit;
    }

    $days = [];
    foreach ($byDay as $day => $agg) {
        $days[] = [
            'date' => $day,
            'source' => 'pos',
            'posProvider' => 'clover',
            'gross' => round($agg['gross_cents'] / 100, 2),
            'net' => round($agg['net_cents'] / 100, 2),
            'tax' => round($agg['tax_cents'] / 100, 2),
            'tips' => round($agg['tip_cents'] / 100, 2),
            'covers' => '',
            'checks' => $agg['checks'],
            'tenderCash' => round($agg['cash_cents'] / 100, 2),
            'tenderCard' => round($agg['card_cents'] / 100, 2),
            'tenderOther' => round($agg['other_cents'] / 100, 2),
            'labor' => '',
            'notes' => 'Synced from Clover',
        ];
    }
    usort($days, static function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    pos_save_connection($pdo, $rid, 'clover', [
        'last_sync_at' => date('Y-m-d H:i:s'),
        'last_sync_status' => 'ok',
        'last_error' => null,
    ]);

    return ['ok' => true, 'days' => $days, 'count' => count($days)];
}

function pos_clover_merchant_info(PDO $pdo, int $rid): array {
    $conn = pos_get_connection($pdo, $rid, 'clover');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $mid = (string) ($conn['merchant_id'] ?? '');
    if ($mid === '') {
        return ['ok' => false, 'error' => 'missing_merchant_id'];
    }
    $res = pos_clover_api($pdo, $rid, 'GET', '/v3/merchants/' . rawurlencode($mid));
    if (!$res['ok']) {
        return ['ok' => false, 'error' => $res['error'] ?? 'merchant_failed'];
    }
    $name = (string) ($res['body']['name'] ?? $mid);
    return [
        'ok' => true,
        'locations' => [
            ['id' => $mid, 'name' => $name],
        ],
        'merchant' => $res['body'],
    ];
}

/**
 * Map Clover employee id => name + optional wage ($/hr).
 * @return array{names:array<string,string>,wages:array<string,float>,roles:array<string,string>}
 */
function pos_clover_employee_maps(PDO $pdo, int $rid, string $merchantId): array {
    $names = [];
    $wages = [];
    $roles = [];
    $offset = 0;
    $limit = 100;
    for ($page = 0; $page < 20; $page++) {
        $path = '/v3/merchants/' . rawurlencode($merchantId) . '/employees?'
            . 'expand=roles&limit=' . $limit . '&offset=' . $offset;
        $res = pos_clover_api($pdo, $rid, 'GET', $path);
        if (!$res['ok']) {
            break;
        }
        $elements = $res['body']['elements'] ?? [];
        if (!is_array($elements) || !$elements) {
            break;
        }
        foreach ($elements as $emp) {
            if (!is_array($emp)) {
                continue;
            }
            $id = (string) ($emp['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $name = trim((string) ($emp['name'] ?? ''));
            if ($name === '') {
                $name = trim((string) (($emp['nickname'] ?? '') ?: (($emp['firstName'] ?? '') . ' ' . ($emp['lastName'] ?? ''))));
            }
            if ($name === '') {
                $name = 'Employee ' . substr($id, -4);
            }
            $names[$id] = $name;
            // wage from first role if present (cents)
            $roleEls = $emp['roles']['elements'] ?? $emp['roles'] ?? [];
            if (is_array($roleEls)) {
                foreach ($roleEls as $role) {
                    if (!is_array($role)) {
                        continue;
                    }
                    $rname = (string) ($role['name'] ?? '');
                    if ($rname !== '' && empty($roles[$id])) {
                        $roles[$id] = $rname;
                    }
                    if (isset($role['wage']) && is_numeric($role['wage'])) {
                        $wages[$id] = round(((int) $role['wage']) / 100, 2);
                        break;
                    }
                    if (isset($role['hourlyWage']) && is_numeric($role['hourlyWage'])) {
                        $hw = (float) $role['hourlyWage'];
                        // if looks like cents
                        $wages[$id] = $hw > 200 ? round($hw / 100, 2) : round($hw, 2);
                        break;
                    }
                }
            }
        }
        if (count($elements) < $limit) {
            break;
        }
        $offset += $limit;
    }
    return ['names' => $names, 'wages' => $wages, 'roles' => $roles];
}

/**
 * Clover labor via merchant shifts (time clock).
 * Tries /shifts with inTime filters; falls back to employee shifts if needed.
 * @return array{ok:bool,laborDays?:list<array>,error?:string,count?:int,hint?:string}
 */
function pos_clover_sync_labor(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $conn = pos_get_connection($pdo, $rid, 'clover');
    if (!$conn) {
        return ['ok' => false, 'error' => 'not_connected'];
    }
    $merchantId = (string) ($conn['merchant_id'] ?? $conn['location_id'] ?? '');
    if ($merchantId === '') {
        return ['ok' => false, 'error' => 'missing_merchant_id'];
    }

    $startMs = strtotime($startDate . ' 00:00:00') * 1000;
    $endMs = (strtotime($endDate . ' 00:00:00') + 86400) * 1000 - 1;
    $maps = pos_clover_employee_maps($pdo, $rid, $merchantId);
    $names = $maps['names'];
    $wages = $maps['wages'];
    $roles = $maps['roles'];

    $records = [];
    $offset = 0;
    $limit = 100;
    $okPath = false;
    $lastErr = null;

    // Primary: merchant-level shifts filtered by inTime
    for ($page = 0; $page < 50; $page++) {
        $path = '/v3/merchants/' . rawurlencode($merchantId) . '/shifts?'
            . 'filter=' . rawurlencode('inTime>=' . $startMs)
            . '&filter=' . rawurlencode('inTime<=' . $endMs)
            . '&expand=employee,role'
            . '&limit=' . $limit
            . '&offset=' . $offset;
        $res = pos_clover_api($pdo, $rid, 'GET', $path);
        if (!$res['ok']) {
            $lastErr = is_string($res['error']) ? $res['error'] : 'shifts_failed';
            break;
        }
        $okPath = true;
        $elements = $res['body']['elements'] ?? [];
        if (!is_array($elements) || !$elements) {
            break;
        }
        foreach ($elements as $sh) {
            if (is_array($sh)) {
                $records[] = $sh;
            }
        }
        if (count($elements) < $limit) {
            break;
        }
        $offset += $limit;
    }

    // Fallback: per-employee shifts if merchant shifts unsupported
    if (!$okPath && $names) {
        $records = [];
        $empIds = array_keys($names);
        $fetchCount = 0;
        foreach ($empIds as $empId) {
            if ($fetchCount > 80) {
                break;
            }
            $fetchCount++;
            $path = '/v3/merchants/' . rawurlencode($merchantId) . '/employees/' . rawurlencode($empId)
                . '/shifts?filter=' . rawurlencode('inTime>=' . $startMs)
                . '&filter=' . rawurlencode('inTime<=' . $endMs)
                . '&limit=100';
            $res = pos_clover_api($pdo, $rid, 'GET', $path);
            if (!$res['ok']) {
                continue;
            }
            $okPath = true;
            foreach ($res['body']['elements'] ?? [] as $sh) {
                if (is_array($sh)) {
                    if (empty($sh['employee'])) {
                        $sh['employee'] = ['id' => $empId];
                    }
                    $records[] = $sh;
                }
            }
        }
    }

    if (!$okPath) {
        return [
            'ok' => false,
            'error' => $lastErr ?: 'clover_shifts_unavailable',
            'hint' => 'Clover time clock / Employees permission required for labor shifts.',
        ];
    }

    /** @var array<string,array{date:string,entries:list,hours:float,cost:float}> $byDay */
    $byDay = [];
    foreach ($records as $sh) {
        $inRaw = $sh['inTime'] ?? $sh['overrideInTime'] ?? null;
        if ($inRaw === null || $inRaw === '') {
            continue;
        }
        $inMs = is_numeric($inRaw) ? (int) $inRaw : (strtotime((string) $inRaw) * 1000);
        if ($inMs < 20000000000 && $inMs > 1000000000) {
            $inMs *= 1000;
        }
        $outRaw = $sh['outTime'] ?? $sh['overrideOutTime'] ?? null;
        $open = ($outRaw === null || $outRaw === '');
        $outMs = $open ? (int) (microtime(true) * 1000) : (is_numeric($outRaw) ? (int) $outRaw : (strtotime((string) $outRaw) * 1000));
        if (!$open && $outMs < 20000000000 && $outMs > 1000000000) {
            $outMs *= 1000;
        }

        $day = date('Y-m-d', (int) floor($inMs / 1000));
        if ($day < $startDate || $day > $endDate) {
            continue;
        }

        $hours = max(0, ($outMs - $inMs) / 3600000.0);
        // unpaid breaks if present
        $breakMins = 0;
        foreach ($sh['breaks'] ?? [] as $br) {
            if (!is_array($br)) {
                continue;
            }
            $bIn = $br['inTime'] ?? $br['startTime'] ?? null;
            $bOut = $br['outTime'] ?? $br['endTime'] ?? null;
            if ($bIn !== null && $bOut !== null) {
                $bInMs = is_numeric($bIn) ? (int) $bIn : strtotime((string) $bIn) * 1000;
                $bOutMs = is_numeric($bOut) ? (int) $bOut : strtotime((string) $bOut) * 1000;
                if ($bInMs < 20000000000) {
                    $bInMs *= 1000;
                }
                if ($bOutMs < 20000000000) {
                    $bOutMs *= 1000;
                }
                $breakMins += (int) round(max(0, $bOutMs - $bInMs) / 60000);
            }
        }
        $hours = max(0, $hours - ($breakMins / 60));
        $hours = round($hours * 100) / 100;

        $empId = '';
        if (!empty($sh['employee']['id'])) {
            $empId = (string) $sh['employee']['id'];
        } elseif (!empty($sh['employee']['href'])) {
            // last path segment
            $empId = (string) basename((string) $sh['employee']['href']);
        } elseif (!empty($sh['employee']['id'])) {
            $empId = (string) $sh['employee']['id'];
        }

        $name = $names[$empId] ?? (string) ($sh['employee']['name'] ?? ($empId !== '' ? 'Employee ' . substr($empId, -4) : 'Team member'));
        $role = '';
        if (!empty($sh['role']['name'])) {
            $role = (string) $sh['role']['name'];
        } elseif (!empty($roles[$empId])) {
            $role = $roles[$empId];
        }

        $wageRate = $wages[$empId] ?? null;
        if ($wageRate === null && isset($sh['role']['wage']) && is_numeric($sh['role']['wage'])) {
            $wageRate = round(((int) $sh['role']['wage']) / 100, 2);
        }
        $laborCost = ($wageRate !== null) ? round($wageRate * $hours, 2) : null;

        $clockIn = date('H:i', (int) floor($inMs / 1000));
        $clockOut = date('H:i', (int) floor($outMs / 1000));

        if (!isset($byDay[$day])) {
            $byDay[$day] = ['date' => $day, 'entries' => [], 'hours' => 0.0, 'cost' => 0.0];
        }
        $byDay[$day]['hours'] += $hours;
        if ($laborCost !== null) {
            $byDay[$day]['cost'] += $laborCost;
        }
        $byDay[$day]['entries'][] = [
            'name' => $name,
            'role' => $role,
            'actualStart' => $clockIn,
            'actualEnd' => $clockOut,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breakMins' => $breakMins,
            'timeSource' => 'pos',
            'wageRate' => $wageRate !== null ? $wageRate : '',
            'laborCost' => $laborCost !== null ? $laborCost : '',
            'externalId' => $empId,
            'punchId' => (string) ($sh['id'] ?? ''),
            'notes' => $open ? 'Open punch (partial)' : '',
        ];
    }

    $laborDays = [];
    foreach ($byDay as $day => $agg) {
        $laborDays[] = [
            'date' => $day,
            'source' => 'pos',
            'posProvider' => 'clover',
            'costSource' => 'pos',
            'hours' => round($agg['hours'], 2),
            'cost' => round($agg['cost'], 2),
            'foh' => '',
            'boh' => '',
            'notes' => 'Synced from Clover shifts',
            'entries' => $agg['entries'],
        ];
    }
    usort($laborDays, static function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    $meta = [];
    if (!empty($conn['meta_json'])) {
        $decoded = json_decode((string) $conn['meta_json'], true);
        if (is_array($decoded)) {
            $meta = $decoded;
        }
    }
    $meta['labor_last_sync_at'] = date('Y-m-d H:i:s');
    $meta['labor_days'] = count($laborDays);
    pos_save_connection($pdo, $rid, 'clover', [
        'meta_json' => $meta,
        'last_error' => null,
    ]);

    return ['ok' => true, 'laborDays' => $laborDays, 'count' => count($laborDays)];
}

function pos_clover_sync_full(PDO $pdo, int $rid, string $startDate, string $endDate): array {
    $sales = pos_clover_sync_sales($pdo, $rid, $startDate, $endDate);
    $labor = pos_clover_sync_labor($pdo, $rid, $startDate, $endDate);
    return pos_combine_sales_labor($sales, $labor);
}

function pos_disconnect(PDO $pdo, int $rid, string $provider): void {
    pos_ensure_tables($pdo);
    // Soft disconnect: clear tokens, keep row for history
    pos_save_connection($pdo, $rid, $provider, [
        'access_token' => '',
        'refresh_token' => '',
        'status' => 'disconnected',
        'last_error' => null,
    ]);
}
