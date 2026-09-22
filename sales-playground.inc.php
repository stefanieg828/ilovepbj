<?php
/**
 * Playground houses (Sales Showcase + ilovepbj Free Demo) — full access,
 * restore to gold-master starters on demand + nightly.
 *
 * Codes: SALES-PBJ · FREE-DEMO (legacy alias DEMO-PBJ)
 * Gold master lives outside the live DB so tester deletes never eat starters.
 */
if (!defined('PBJ_SALES_PLAYGROUND_LOADED')) {
    define('PBJ_SALES_PLAYGROUND_LOADED', true);
}

define('PBJ_SALES_INVITE_CODE', 'SALES-PBJ');
define('PBJ_SALES_HOUSE_NAME', 'Sales Showcase');
define('PBJ_DEMO_INVITE_CODE', 'FREE-DEMO');
/** Legacy public code — still accepted for lookups until DB is updated. */
define('PBJ_DEMO_INVITE_CODE_LEGACY', 'DEMO-PBJ');
define('PBJ_DEMO_HOUSE_NAME', 'ilovepbj Free Demo');
/** Timezone for “end of day” reset (2:00 AM local). */
define('PBJ_SALES_TIMEZONE', 'America/New_York');

/** Public + legacy invite codes for the free demo house. */
function pbj_demo_invite_codes(): array {
    return [PBJ_DEMO_INVITE_CODE, PBJ_DEMO_INVITE_CODE_LEGACY];
}

/** Invite codes that always support starter reset (sales + free demo aliases). */
function pbj_playground_invite_codes(): array {
    return array_values(array_unique(array_merge(
        [PBJ_SALES_INVITE_CODE],
        pbj_demo_invite_codes()
    )));
}

function pbj_sales_snapshot_dir(): string {
    $dir = '/var/www/private/ilovepbj/sales-starters';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
        @mkdir($dir . '/shared', 0750, true);
        @mkdir($dir . '/gold-backup', 0750, true);
    }
    return $dir;
}

function pbj_sales_log(string $msg): void {
    error_log('[playground-reset] ' . $msg);
    $log = pbj_sales_snapshot_dir() . '/reset.log';
    @file_put_contents($log, date('c') . ' ' . $msg . "\n", FILE_APPEND);
}

/**
 * All restaurant ids that restore from gold starters.
 * @return list<int>
 */
function pbj_sales_playground_restaurant_ids(PDO $pdo): array {
    $ids = [];
    try {
        $stmt = $pdo->query('SELECT restaurant_id, settings_json FROM restaurant_settings');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $s = json_decode((string) ($row['settings_json'] ?? ''), true);
            if (is_array($s) && (!empty($s['sales_playground']) || !empty($s['playground_reset']))) {
                $ids[] = (int) $row['restaurant_id'];
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
    try {
        foreach (pbj_playground_invite_codes() as $code) {
            $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ?');
            $stmt->execute([$code]);
            while ($id = $stmt->fetchColumn()) {
                $ids[] = (int) $id;
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
    return array_values(array_unique(array_filter($ids)));
}

/** True if this house is Sales Showcase (nightly demo for consultants). */
function pbj_restaurant_is_sales_playground(PDO $pdo, int $restaurantId): bool {
    if ($restaurantId <= 0) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $s = json_decode((string) $raw, true);
            if (is_array($s) && !empty($s['sales_playground'])) {
                return true;
            }
        }
        $stmt = $pdo->prepare('SELECT invite_code FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $code = strtoupper(trim((string) $stmt->fetchColumn()));
        return $code === PBJ_SALES_INVITE_CODE;
    } catch (Throwable $e) {
        return false;
    }
}

/** True if house can be wiped back to gold starters (both playgrounds). */
function pbj_restaurant_is_playground_resetable(PDO $pdo, int $restaurantId): bool {
    if ($restaurantId <= 0) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $s = json_decode((string) $raw, true);
            if (is_array($s) && (!empty($s['playground_reset']) || !empty($s['sales_playground']))) {
                return true;
            }
        }
        $stmt = $pdo->prepare('SELECT invite_code FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $code = strtoupper(trim((string) $stmt->fetchColumn()));
        return in_array($code, pbj_playground_invite_codes(), true);
    } catch (Throwable $e) {
        return false;
    }
}

function pbj_sales_reset_epoch(PDO $pdo, int $restaurantId): int {
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $s = json_decode((string) $raw, true);
            if (is_array($s) && !empty($s['sales_reset_epoch'])) {
                return (int) $s['sales_reset_epoch'];
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
    return 0;
}

function pbj_sales_bump_epoch(PDO $pdo, int $restaurantId): int {
    $epoch = time();
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        $s = $raw ? (json_decode((string) $raw, true) ?: []) : [];
        $code = '';
        try {
            $c = $pdo->prepare('SELECT invite_code FROM restaurants WHERE id = ? LIMIT 1');
            $c->execute([$restaurantId]);
            $code = strtoupper(trim((string) $c->fetchColumn()));
        } catch (Throwable $e) {
            // ignore
        }
        $s['playground_reset'] = true;
        $s['demo_house'] = true;
        $s['unlimited_seats'] = true;
        if ($code === PBJ_SALES_INVITE_CODE) {
            $s['sales_playground'] = true;
        }
        $s['sales_reset_epoch'] = $epoch;
        $s['sales_last_reset_at'] = date('c');
        $json = json_encode($s, JSON_UNESCAPED_UNICODE);
        if ($raw !== false && $raw !== null) {
            $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                ->execute([$json, $restaurantId]);
        } else {
            $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                ->execute([$restaurantId, $json]);
        }
    } catch (Throwable $e) {
        pbj_sales_log('bump epoch failed: ' . $e->getMessage());
    }
    return $epoch;
}

/**
 * Ensure FREE-DEMO (personal free demo; legacy DEMO-PBJ) is flagged for unlimited + starter reset.
 */
function pbj_ensure_demo_playground(PDO $pdo): int {
    $id = 0;
    $matchedCode = '';
    foreach (pbj_demo_invite_codes() as $demoCode) {
        $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ? LIMIT 1');
        $stmt->execute([$demoCode]);
        $id = (int) ($stmt->fetchColumn() ?: 0);
        if ($id > 0) {
            $matchedCode = $demoCode;
            break;
        }
    }
    if ($id <= 0) {
        $ownerId = 1;
        try {
            $admins = function_exists('pbj_email_list') ? pbj_email_list('PLATFORM_ADMIN_EMAILS') : [];
            foreach ($admins as $em) {
                $s = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1');
                $s->execute([$em]);
                $uid = (int) ($s->fetchColumn() ?: 0);
                if ($uid > 0) {
                    $ownerId = $uid;
                    break;
                }
            }
        } catch (Throwable $e) {
            // ignore
        }
        $pdo->prepare('INSERT INTO restaurants (name, owner_id, invite_code) VALUES (?, ?, ?)')
            ->execute([PBJ_DEMO_HOUSE_NAME, $ownerId, PBJ_DEMO_INVITE_CODE]);
        $id = (int) $pdo->lastInsertId();
        pbj_sales_log("created free demo house id={$id} code=" . PBJ_DEMO_INVITE_CODE);
    } else {
        $pdo->prepare('UPDATE restaurants SET name = ? WHERE id = ? AND (name = ? OR name = ? OR name = ? OR name = ?)')
            ->execute([PBJ_DEMO_HOUSE_NAME, $id, 'Demo Kitchen', 'DEMO Kitchen', 'ilovepbj Playground', '']);
        // Prefer public FREE-DEMO code; leave legacy DEMO-PBJ until ops updates DB if needed
        if ($matchedCode !== '' && $matchedCode !== PBJ_DEMO_INVITE_CODE) {
            // Keep legacy row findable; name still shows Free Demo
            $pdo->prepare('UPDATE restaurants SET name = ? WHERE id = ?')
                ->execute([PBJ_DEMO_HOUSE_NAME, $id]);
        } else {
            $pdo->prepare('UPDATE restaurants SET name = ? WHERE id = ? AND UPPER(invite_code) = ?')
                ->execute([PBJ_DEMO_HOUSE_NAME, $id, PBJ_DEMO_INVITE_CODE]);
        }
    }

    $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
    $stmt->execute([$id]);
    $raw = $stmt->fetchColumn();
    $s = $raw ? (json_decode((string) $raw, true) ?: []) : [];
    $s['playground_reset'] = true;
    $s['demo_house'] = true;
    $s['unlimited_seats'] = true;
    $s['plan'] = $s['plan'] ?? 'custom';
    $s['billing_status'] = $s['billing_status'] ?? 'demo';
    // Always keep every role fully open on the personal playground
    $s['permissions'] = [
        'version' => 1,
        'matrix' => pbj_sales_full_permissions_matrix(),
        'playground_full_access' => true,
    ];
    $s['floorAllowedRoles'] = ['owner', 'admin', 'gm', 'manager', 'foh', 'boh'];
    if (empty($s['sales_reset_epoch'])) {
        $s['sales_reset_epoch'] = time();
    }
    $json = json_encode($s, JSON_UNESCAPED_UNICODE);
    if ($raw !== false && $raw !== null) {
        $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
            ->execute([$json, $id]);
    } else {
        $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
            ->execute([$id, $json]);
    }
    return $id;
}

function pbj_ensure_all_playgrounds(PDO $pdo): array {
    return [
        'demo' => pbj_ensure_demo_playground($pdo),
        'sales' => pbj_ensure_sales_playground($pdo),
    ];
}

/** Full-open permissions matrix for every role (sales demos). */
function pbj_sales_full_permissions_matrix(): array {
    if (!function_exists('pbj_permission_all_keys')) {
        require_once __DIR__ . '/pbj-permissions.php';
    }
    $keys = pbj_permission_all_keys();
    $allTrue = [];
    foreach ($keys as $k) {
        $allTrue[$k] = true;
    }
    $matrix = [];
    foreach (array_keys(pbj_permission_roles()) as $role) {
        $matrix[$role] = $allTrue;
    }
    return $matrix;
}

/**
 * Ensure the Sales Showcase house exists; return restaurant id.
 */
function pbj_ensure_sales_playground(PDO $pdo): int {
    $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ? LIMIT 1');
    $stmt->execute([PBJ_SALES_INVITE_CODE]);
    $id = (int) ($stmt->fetchColumn() ?: 0);

    $ownerId = 1;
    try {
        $admins = function_exists('pbj_email_list') ? pbj_email_list('PLATFORM_ADMIN_EMAILS') : [];
        foreach ($admins as $em) {
            $s = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1');
            $s->execute([$em]);
            $uid = (int) ($s->fetchColumn() ?: 0);
            if ($uid > 0) {
                $ownerId = $uid;
                break;
            }
        }
    } catch (Throwable $e) {
        // ignore
    }

    if ($id <= 0) {
        $ins = $pdo->prepare('INSERT INTO restaurants (name, owner_id, invite_code) VALUES (?, ?, ?)');
        $ins->execute([PBJ_SALES_HOUSE_NAME, $ownerId, PBJ_SALES_INVITE_CODE]);
        $id = (int) $pdo->lastInsertId();
        pbj_sales_log("created sales house id={$id}");
    } else {
        $pdo->prepare('UPDATE restaurants SET name = ? WHERE id = ?')->execute([PBJ_SALES_HOUSE_NAME, $id]);
    }

    // Link owner
    try {
        $chk = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
        $chk->execute([$ownerId, $id]);
        if ($chk->fetchColumn() === false) {
            $pdo->prepare("INSERT INTO user_restaurant (user_id, restaurant_id, role) VALUES (?, ?, 'owner')")
                ->execute([$ownerId, $id]);
        }
        $pdo->prepare('UPDATE restaurants SET owner_id = ? WHERE id = ?')->execute([$ownerId, $id]);
    } catch (Throwable $e) {
        // ignore
    }

    // Settings flags + full permissions
    $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
    $stmt->execute([$id]);
    $raw = $stmt->fetchColumn();
    $s = $raw ? (json_decode((string) $raw, true) ?: []) : [];
    $s['sales_playground'] = true;
    $s['playground_reset'] = true;
    $s['demo_house'] = true;
    $s['unlimited_seats'] = true;
    $s['plan'] = 'custom';
    $s['billing_status'] = 'demo';
    $s['permissions'] = [
        'version' => 1,
        'matrix' => pbj_sales_full_permissions_matrix(),
        'playground_full_access' => true,
    ];
    $s['floorAllowedRoles'] = ['owner', 'admin', 'gm', 'manager', 'foh', 'boh'];
    if (empty($s['sales_reset_epoch'])) {
        $s['sales_reset_epoch'] = time();
    }
    $json = json_encode($s, JSON_UNESCAPED_UNICODE);
    if ($raw !== false && $raw !== null) {
        $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
            ->execute([$json, $id]);
    } else {
        $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
            ->execute([$id, $json]);
    }

    return $id;
}

/**
 * Strip “done / checked” runtime noise so starters feel fresh.
 * @param array<string,mixed> $payload
 * @return array<string,mixed>
 */
function pbj_sales_sanitize_starter_payload(array $payload): array {
    $walk = function (&$node) use (&$walk) {
        if (!is_array($node)) {
            return;
        }
        // Common check flags
        if (array_key_exists('done', $node)) {
            $node['done'] = false;
        }
        if (array_key_exists('doneUpdatedAt', $node)) {
            $node['doneUpdatedAt'] = 0;
        }
        if (array_key_exists('checked', $node)) {
            $node['checked'] = false;
        }
        // checks map: key => {done, ...}
        if (isset($node['checks']) && is_array($node['checks'])) {
            foreach ($node['checks'] as $k => $v) {
                if (is_array($v)) {
                    $v['done'] = false;
                    $v['doneUpdatedAt'] = 0;
                    $node['checks'][$k] = $v;
                }
            }
        }
        foreach ($node as &$child) {
            if (is_array($child)) {
                $walk($child);
            }
        }
        unset($child);
    };
    $walk($payload);
    $payload['structureAt'] = (int) (microtime(true) * 1000);
    return $payload;
}

/**
 * Backup current gold master before overwriting (so we can recover).
 */
function pbj_sales_backup_gold_master(): void {
    $dir = pbj_sales_snapshot_dir();
    $sharedDir = $dir . '/shared';
    $backupRoot = $dir . '/gold-backup/' . date('Ymd-His');
    if (!is_dir($sharedDir)) {
        return;
    }
    $files = glob($sharedDir . '/*.json') ?: [];
    if (!$files && !is_readable($dir . '/settings.json')) {
        return;
    }
    @mkdir($backupRoot . '/shared', 0750, true);
    if (is_readable($dir . '/settings.json')) {
        @copy($dir . '/settings.json', $backupRoot . '/settings.json');
    }
    if (is_readable($dir . '/meta.json')) {
        @copy($dir . '/meta.json', $backupRoot . '/meta.json');
    }
    foreach ($files as $f) {
        @copy($f, $backupRoot . '/shared/' . basename($f));
    }
    // Keep last 10 backups only
    $dirs = glob($dir . '/gold-backup/*', GLOB_ONLYDIR) ?: [];
    rsort($dirs);
    foreach (array_slice($dirs, 10) as $old) {
        foreach (glob($old . '/shared/*') ?: [] as $sf) {
            @unlink($sf);
        }
        @unlink($old . '/settings.json');
        @unlink($old . '/meta.json');
        @rmdir($old . '/shared');
        @rmdir($old);
    }
    pbj_sales_log('backed up gold master to ' . $backupRoot);
}

/**
 * Save gold-master starters from a source restaurant’s latest shared rows.
 * Only call when the source house looks perfect — this is the permanent restore pack.
 * @return array{ok:bool,keys:int,error?:string}
 */
function pbj_sales_save_starters_from_restaurant(PDO $pdo, int $sourceRestaurantId): array {
    $dir = pbj_sales_snapshot_dir();
    $sharedDir = $dir . '/shared';
    if (!is_dir($sharedDir)) {
        @mkdir($sharedDir, 0750, true);
    }

    // Never destroy prior gold without a backup
    pbj_sales_backup_gold_master();

    // Settings template applied on every reset (full open + playground flags)
    $settings = [
        'playground_reset' => true,
        'demo_house' => true,
        'unlimited_seats' => true,
        'plan' => 'custom',
        'billing_status' => 'demo',
        'permissions' => [
            'version' => 1,
            'matrix' => pbj_sales_full_permissions_matrix(),
        ],
        'starter_saved_at' => date('c'),
        'starter_source_restaurant_id' => $sourceRestaurantId,
        'gold_master' => true,
    ];
    file_put_contents($dir . '/settings.json', json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $keys = 0;
    try {
        // Latest payload per state_key from source (structure after tester mess? capture only when clean)
        $sql = "SELECT s.state_key, s.payload
                FROM restaurant_shared_state s
                INNER JOIN (
                    SELECT state_key, MAX(business_date) AS max_date
                    FROM restaurant_shared_state
                    WHERE restaurant_id = ?
                    GROUP BY state_key
                ) t ON t.state_key = s.state_key AND t.max_date = s.business_date
                WHERE s.restaurant_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sourceRestaurantId, $sourceRestaurantId]);
        // Clear old shared snapshots (backup already taken)
        foreach (glob($sharedDir . '/*.json') ?: [] as $f) {
            @unlink($f);
        }
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = (string) $row['state_key'];
            if (!preg_match('/^[a-z0-9_]{3,64}$/i', $key)) {
                continue;
            }
            $payload = json_decode((string) $row['payload'], true);
            if (!is_array($payload)) {
                continue;
            }
            $payload = pbj_sales_sanitize_starter_payload($payload);
            $path = $sharedDir . '/' . $key . '.json';
            file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE));
            $keys++;
        }
    } catch (Throwable $e) {
        return ['ok' => false, 'keys' => $keys, 'error' => $e->getMessage()];
    }

    if ($keys < 1) {
        return ['ok' => false, 'keys' => 0, 'error' => 'no_shared_state_to_capture'];
    }

    // Meta
    file_put_contents($dir . '/meta.json', json_encode([
        'saved_at' => date('c'),
        'source_restaurant_id' => $sourceRestaurantId,
        'keys' => $keys,
        'note' => 'Gold master for FREE-DEMO (+ legacy DEMO-PBJ) + SALES-PBJ. Reset never edits these files — only live house rows.',
    ], JSON_PRETTY_PRINT));

    pbj_sales_log("saved gold master: {$keys} starter keys from restaurant {$sourceRestaurantId}");
    return ['ok' => true, 'keys' => $keys];
}

function pbj_sales_business_date(?string $tz = null): string {
    try {
        $tzName = $tz ?: PBJ_SALES_TIMEZONE;
        $dt = new DateTime('now', new DateTimeZone($tzName));
        return $dt->format('Y-m-d');
    } catch (Throwable $e) {
        return date('Y-m-d');
    }
}

/**
 * Wipe live shared state for a playground house and re-seed from gold-master files.
 * Gold master on disk is never modified. User memberships are kept.
 * @return array{ok:bool,keys:int,epoch:int,error?:string,house?:string}
 */
function pbj_sales_reset_house(PDO $pdo, int $restaurantId): array {
    if ($restaurantId <= 0) {
        return ['ok' => false, 'keys' => 0, 'epoch' => 0, 'error' => 'bad_id'];
    }
    $dir = pbj_sales_snapshot_dir();
    $sharedDir = $dir . '/shared';
    $date = pbj_sales_business_date();

    $code = '';
    $houseName = '';
    try {
        $stmt = $pdo->prepare('SELECT invite_code, name FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $code = strtoupper(trim((string) ($row['invite_code'] ?? '')));
        $houseName = (string) ($row['name'] ?? '');
    } catch (Throwable $e) {
        // ignore
    }

    $goldFiles = glob($sharedDir . '/*.json') ?: [];
    if (!$goldFiles) {
        return ['ok' => false, 'keys' => 0, 'epoch' => 0, 'error' => 'no_gold_master', 'house' => $houseName];
    }

    try {
        // Restore settings (permissions full open) — gold template + house-specific flags
        $settingsPath = $dir . '/settings.json';
        $settings = is_readable($settingsPath)
            ? (json_decode((string) file_get_contents($settingsPath), true) ?: [])
            : [];
        if (!is_array($settings) || empty($settings)) {
            $settings = [];
        }
        $settings['playground_reset'] = true;
        $settings['demo_house'] = true;
        $settings['unlimited_seats'] = true;
        $settings['plan'] = 'custom';
        $settings['billing_status'] = 'demo';
        $settings['permissions'] = [
            'version' => 1,
            'matrix' => pbj_sales_full_permissions_matrix(),
        ];
        // Sales Showcase keeps sales_playground; personal DEMO does not need consultant labeling
        if ($code === PBJ_SALES_INVITE_CODE) {
            $settings['sales_playground'] = true;
        } else {
            // Keep playground_reset so clients still wipe localStorage
            unset($settings['sales_playground']);
            $settings['playground_reset'] = true;
        }
        $epoch = time();
        $settings['sales_reset_epoch'] = $epoch;
        $settings['sales_last_reset_at'] = date('c');
        $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
        $chk = $pdo->prepare('SELECT restaurant_id FROM restaurant_settings WHERE restaurant_id = ?');
        $chk->execute([$restaurantId]);
        if ($chk->fetchColumn()) {
            $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                ->execute([$json, $restaurantId]);
        } else {
            $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                ->execute([$restaurantId, $json]);
        }

        // Wipe ALL live shared state (every date / every key) so deletes cannot linger
        $pdo->prepare('DELETE FROM restaurant_shared_state WHERE restaurant_id = ?')->execute([$restaurantId]);

        $keys = 0;
        $ins = $pdo->prepare(
            'INSERT INTO restaurant_shared_state
             (restaurant_id, state_key, business_date, payload, version, updated_by)
             VALUES (?, ?, ?, ?, 1, NULL)'
        );
        foreach ($goldFiles as $file) {
            $key = basename($file, '.json');
            if (!preg_match('/^[a-z0-9_]{3,64}$/i', $key)) {
                continue;
            }
            $payload = json_decode((string) file_get_contents($file), true);
            if (!is_array($payload)) {
                continue;
            }
            // Fresh checkmarks every restore
            $payload = pbj_sales_sanitize_starter_payload($payload);
            $ins->execute([
                $restaurantId,
                $key,
                $date,
                json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
            $keys++;
        }

        pbj_sales_log("reset house {$restaurantId} ({$code}): {$keys} keys seeded for {$date}, epoch={$epoch}");
        return ['ok' => true, 'keys' => $keys, 'epoch' => $epoch, 'house' => $houseName ?: $code];
    } catch (Throwable $e) {
        pbj_sales_log('reset failed: ' . $e->getMessage());
        return ['ok' => false, 'keys' => 0, 'epoch' => 0, 'error' => $e->getMessage(), 'house' => $houseName];
    }
}

/** Reset every playground house (DEMO + SALES). */
function pbj_sales_reset_all(PDO $pdo): array {
    pbj_ensure_all_playgrounds($pdo);
    $results = [];
    foreach (pbj_sales_playground_restaurant_ids($pdo) as $rid) {
        $results[$rid] = pbj_sales_reset_house($pdo, $rid);
    }
    return $results;
}

/**
 * Meta for clients (epoch) — any playground member gets local wipe on reset.
 * @return array<string,mixed>
 */
function pbj_sales_client_meta(PDO $pdo, int $restaurantId): array {
    if (!pbj_restaurant_is_playground_resetable($pdo, $restaurantId)) {
        return ['sales_playground' => false, 'playground_reset' => false];
    }
    $code = '';
    $name = '';
    try {
        $stmt = $pdo->prepare('SELECT invite_code, name FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $code = strtoupper(trim((string) ($row['invite_code'] ?? '')));
        $name = (string) ($row['name'] ?? '');
    } catch (Throwable $e) {
        // ignore
    }
    return [
        // Client treats sales_playground as “wipe local + follow epoch”
        'sales_playground' => true,
        'playground_reset' => true,
        'sales_reset_epoch' => pbj_sales_reset_epoch($pdo, $restaurantId),
        'invite_code' => $code,
        'house_name' => $name,
        'timezone' => PBJ_SALES_TIMEZONE,
        'business_date' => pbj_sales_business_date(),
    ];
}

/** Count gold-master starter modules on disk. */
function pbj_sales_gold_master_key_count(): int {
    $dir = pbj_sales_snapshot_dir() . '/shared';
    return count(glob($dir . '/*.json') ?: []);
}

/** Days of inactivity before a guest is removed from playground houses. */
function pbj_playground_inactive_days(): int {
    if (defined('PBJ_PLAYGROUND_INACTIVE_DAYS')) {
        $n = (int) PBJ_PLAYGROUND_INACTIVE_DAYS;
        return $n > 0 ? $n : 60;
    }
    return 60;
}

/**
 * Remove playground guest seats for users inactive for N days.
 * Never removes the house owner (or owner_id). Does not delete accounts —
 * only user_restaurant rows on playground kitchens.
 *
 * Activity = COALESCE(users.last_login_at, user_restaurant.joined_at, users.created_at).
 * Users who never logged in are judged from when they joined the playground.
 *
 * @param array{dry_run?:bool,days?:int} $opts
 * @return array{ok:bool,days:int,dry_run:bool,removed:int,kept_owners:int,scanned:int,playgrounds:int,details:list<array<string,mixed>>}
 */
function pbj_purge_inactive_playground_members(PDO $pdo, array $opts = []): array {
    $days = isset($opts['days']) ? (int) $opts['days'] : pbj_playground_inactive_days();
    if ($days < 1) {
        $days = 60;
    }
    $dryRun = !empty($opts['dry_run']);
    $result = [
        'ok' => true,
        'days' => $days,
        'dry_run' => $dryRun,
        'removed' => 0,
        'kept_owners' => 0,
        'scanned' => 0,
        'playgrounds' => 0,
        'details' => [],
    ];

    try {
        if (function_exists('pbj_ensure_user_desk_columns')) {
            pbj_ensure_user_desk_columns($pdo);
        }

        $playgroundIds = function_exists('pbj_sales_playground_restaurant_ids')
            ? pbj_sales_playground_restaurant_ids($pdo)
            : [];
        // Always include invite-code houses even if settings lag
        foreach (pbj_playground_invite_codes() as $code) {
            $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ?');
            $stmt->execute([strtoupper((string) $code)]);
            while ($id = $stmt->fetchColumn()) {
                $playgroundIds[] = (int) $id;
            }
        }
        $playgroundIds = array_values(array_unique(array_filter(array_map('intval', $playgroundIds))));
        $result['playgrounds'] = count($playgroundIds);
        if (!$playgroundIds) {
            return $result;
        }

        $cutoff = (new DateTimeImmutable('now'))->modify('-' . $days . ' days');
        $cutoffSql = $cutoff->format('Y-m-d H:i:s');

        $ownerStmt = $pdo->prepare('SELECT owner_id, name, invite_code FROM restaurants WHERE id = ? LIMIT 1');
        $memberStmt = $pdo->prepare(
            'SELECT ur.user_id, ur.role, ur.joined_at,
                    u.username, u.email, u.full_name, u.last_login_at, u.created_at
             FROM user_restaurant ur
             JOIN users u ON u.id = ur.user_id
             WHERE ur.restaurant_id = ?'
        );
        $delStmt = $pdo->prepare('DELETE FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');

        foreach ($playgroundIds as $rid) {
            if ($rid <= 0) {
                continue;
            }
            $ownerStmt->execute([$rid]);
            $house = $ownerStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ownerId = (int) ($house['owner_id'] ?? 0);
            $houseName = (string) ($house['name'] ?? ('#' . $rid));
            $invite = strtoupper(trim((string) ($house['invite_code'] ?? '')));

            $memberStmt->execute([$rid]);
            $members = $memberStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($members as $m) {
                $result['scanned']++;
                $uid = (int) ($m['user_id'] ?? 0);
                if ($uid <= 0) {
                    continue;
                }
                // Never remove the restaurant owner
                if ($ownerId > 0 && $uid === $ownerId) {
                    $result['kept_owners']++;
                    continue;
                }
                // Never remove platform admins (hosts / support on playground)
                $email = (string) ($m['email'] ?? '');
                if (function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($email)) {
                    $result['kept_owners']++;
                    continue;
                }

                $activityRaw = $m['last_login_at'] ?? null;
                if ($activityRaw === null || $activityRaw === '' || $activityRaw === '0000-00-00 00:00:00') {
                    $activityRaw = $m['joined_at'] ?? null;
                }
                if ($activityRaw === null || $activityRaw === '' || $activityRaw === '0000-00-00 00:00:00') {
                    $activityRaw = $m['created_at'] ?? null;
                }
                if ($activityRaw === null || $activityRaw === '' || $activityRaw === '0000-00-00 00:00:00') {
                    // No timestamps at all — treat as stale so they don't linger forever
                    $activityRaw = '1970-01-01 00:00:00';
                }

                try {
                    $activityAt = new DateTimeImmutable((string) $activityRaw);
                } catch (Throwable $e) {
                    $activityAt = new DateTimeImmutable('1970-01-01 00:00:00');
                }
                if ($activityAt > $cutoff) {
                    continue; // still active within window
                }

                $detail = [
                    'restaurant_id' => $rid,
                    'house' => $houseName,
                    'invite_code' => $invite,
                    'user_id' => $uid,
                    'username' => (string) ($m['username'] ?? ''),
                    'email' => $email,
                    'role' => (string) ($m['role'] ?? ''),
                    'last_activity' => $activityAt->format('Y-m-d H:i:s'),
                    'cutoff' => $cutoffSql,
                    'removed' => false,
                ];

                if (!$dryRun) {
                    $delStmt->execute([$uid, $rid]);
                    if ($delStmt->rowCount() > 0) {
                        $detail['removed'] = true;
                        $result['removed']++;
                    }
                } else {
                    $detail['removed'] = false; // would remove
                    $result['removed']++; // count as would-remove in dry-run
                }
                $result['details'][] = $detail;
            }
        }

        if ($result['removed'] > 0) {
            $mode = $dryRun ? 'dry-run' : 'purged';
            pbj_sales_log(
                "inactive members {$mode}: removed={$result['removed']} days={$days} playgrounds={$result['playgrounds']} scanned={$result['scanned']}"
            );
        }
    } catch (Throwable $e) {
        $result['ok'] = false;
        $result['error'] = $e->getMessage();
        pbj_sales_log('purge inactive playground members failed: ' . $e->getMessage());
        error_log('pbj_purge_inactive_playground_members: ' . $e->getMessage());
    }

    return $result;
}
