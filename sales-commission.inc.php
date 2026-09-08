<?php
/**
 * Sales reps, attribution (ref codes / links), and commission rules.
 *
 * Rates (v1):
 *  - House subscription: 30% recurring monthly while the restaurant stays paid
 *  - Startup fee: 20% one-time (product not live yet — rate stored)
 *  - Butler service: 15% recurring monthly (product not live yet — rate stored)
 */

/** Commission product rates. */
function pbj_commission_rates(): array {
    return [
        'subscription' => [
            'rate_pct' => 30.0,
            'kind' => 'recurring',
            'label' => 'House / plan subscription',
            'active' => true,
            'note' => 'Recurring every month while the house stays subscribed',
        ],
        'startup' => [
            'rate_pct' => 20.0,
            'kind' => 'one_time',
            'label' => 'Startup fee',
            'active' => false,
            'note' => 'One-time when Startup product is live',
        ],
        'butler' => [
            'rate_pct' => 15.0,
            'kind' => 'recurring',
            'label' => 'Butler service',
            'active' => false,
            'note' => 'Recurring monthly when Butler is live',
        ],
    ];
}

function pbj_commission_rate_pct(string $product): float {
    $rates = pbj_commission_rates();
    return (float) ($rates[$product]['rate_pct'] ?? 0);
}

/** List monthly plan prices in cents (USD) for estimates + ledger. */
function pbj_plan_price_cents(string $planId): int {
    $plan = function_exists('pbj_plan_by_id') ? pbj_plan_by_id($planId) : null;
    $id = $plan ? $plan['id'] : $planId;
    return match ($id) {
        'individual' => 500,
        'crew_10' => 2900,
        'crew_30' => 5900,
        'crew_75' => 9900,
        'crew_150' => 14900,
        'crew_300' => 19900,
        default => 0,
    };
}

function pbj_commission_cents(int $baseCents, float $ratePct): int {
    if ($baseCents <= 0 || $ratePct <= 0) {
        return 0;
    }
    return (int) (int) round($baseCents * ($ratePct / 100.0));
}

function pbj_format_money_cents(int $cents): string {
    return '$' . number_format(max(0, $cents) / 100, 2);
}

/** Create sales tables if missing. */
function pbj_ensure_sales_tables(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS sales_reps (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(32) NOT NULL,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NULL DEFAULT NULL,
                phone VARCHAR(40) NULL DEFAULT NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                notes TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_sales_reps_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS sales_attributions (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                sales_rep_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                restaurant_id INT UNSIGNED NULL DEFAULT NULL,
                plan_id VARCHAR(32) NULL DEFAULT NULL,
                sales_code VARCHAR(32) NOT NULL,
                source VARCHAR(32) NULL DEFAULT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'trialing',
                attributed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                converted_at DATETIME NULL DEFAULT NULL,
                monthly_plan_cents INT NOT NULL DEFAULT 0,
                KEY idx_sa_rep (sales_rep_id),
                KEY idx_sa_user (user_id),
                KEY idx_sa_rest (restaurant_id),
                KEY idx_sa_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS sales_commission_events (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                sales_rep_id INT UNSIGNED NOT NULL,
                attribution_id INT UNSIGNED NULL DEFAULT NULL,
                restaurant_id INT UNSIGNED NULL DEFAULT NULL,
                user_id INT UNSIGNED NULL DEFAULT NULL,
                product VARCHAR(32) NOT NULL,
                event_type VARCHAR(32) NOT NULL DEFAULT 'recurring',
                rate_pct DECIMAL(6,2) NOT NULL,
                base_amount_cents INT NOT NULL DEFAULT 0,
                commission_cents INT NOT NULL DEFAULT 0,
                currency CHAR(3) NOT NULL DEFAULT 'usd',
                period_ym CHAR(7) NULL DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'earned',
                external_id VARCHAR(80) NULL DEFAULT NULL,
                note VARCHAR(255) NULL DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_sce_rep (sales_rep_id),
                KEY idx_sce_period (period_ym),
                KEY idx_sce_rest (restaurant_id),
                UNIQUE KEY uq_sce_dedupe (sales_rep_id, product, event_type, period_ym, restaurant_id, user_id, external_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } catch (Throwable $e) {
        error_log('pbj_ensure_sales_tables: ' . $e->getMessage());
    }
}

function pbj_normalize_sales_code(string $raw): string {
    $code = strtoupper(trim($raw));
    $code = preg_replace('/[^A-Z0-9_-]/', '', $code) ?? '';
    return substr($code, 0, 32);
}

/**
 * Capture ?ref= / ?sales= into a long-lived cookie (first-touch wins).
 * Safe on every request after session_start.
 */
function pbj_capture_sales_ref_from_request(): void {
    if (PHP_SAPI === 'cli') {
        return;
    }
    $raw = (string) ($_GET['ref'] ?? $_GET['sales'] ?? $_GET['sales_code'] ?? '');
    $code = pbj_normalize_sales_code($raw);
    if ($code === '' || strlen($code) < 2) {
        return;
    }
    // First-touch: don't overwrite an existing ref cookie
    $existing = pbj_normalize_sales_code((string) ($_COOKIE['pbj_sales_ref'] ?? ''));
    if ($existing !== '') {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['sales_ref'] = $existing;
        }
        return;
    }
    $https = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
    setcookie('pbj_sales_ref', $code, [
        'expires' => time() + 90 * 86400,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['pbj_sales_ref'] = $code;
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['sales_ref'] = $code;
    }
}

/** Resolved sales code from form → session → cookie (first non-empty). */
function pbj_resolve_sales_code_from_request(?string $formCode = null): string {
    $candidates = [
        $formCode,
        $_SESSION['sales_ref'] ?? null,
        $_COOKIE['pbj_sales_ref'] ?? null,
        $_GET['ref'] ?? null,
        $_GET['sales'] ?? null,
    ];
    foreach ($candidates as $c) {
        $n = pbj_normalize_sales_code((string) $c);
        if ($n !== '' && strlen($n) >= 2) {
            return $n;
        }
    }
    return '';
}

/** @return array<string,mixed>|null */
function pbj_sales_rep_by_code(PDO $pdo, string $code): ?array {
    pbj_ensure_sales_tables($pdo);
    $code = pbj_normalize_sales_code($code);
    if ($code === '') {
        return null;
    }
    try {
        $stmt = $pdo->prepare('SELECT * FROM sales_reps WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/** @return array<string,mixed>|null */
function pbj_sales_rep_by_id(PDO $pdo, int $id): ?array {
    pbj_ensure_sales_tables($pdo);
    if ($id <= 0) {
        return null;
    }
    try {
        $stmt = $pdo->prepare('SELECT * FROM sales_reps WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/** @return list<array<string,mixed>> */
function pbj_sales_reps_list(PDO $pdo, bool $activeOnly = false): array {
    pbj_ensure_sales_tables($pdo);
    try {
        $sql = 'SELECT * FROM sales_reps';
        if ($activeOnly) {
            $sql .= ' WHERE active = 1';
        }
        $sql .= ' ORDER BY active DESC, name ASC';
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * @return array{ok:bool,id?:int,error?:string,message?:string}
 */
function pbj_sales_rep_save(PDO $pdo, array $fields, int $id = 0): array {
    pbj_ensure_sales_tables($pdo);
    $name = trim((string) ($fields['name'] ?? ''));
    $code = pbj_normalize_sales_code((string) ($fields['code'] ?? ''));
    $email = strtolower(trim((string) ($fields['email'] ?? '')));
    $phone = trim((string) ($fields['phone'] ?? ''));
    $notes = trim((string) ($fields['notes'] ?? ''));
    $active = !empty($fields['active']) ? 1 : 0;

    if ($name === '' || $code === '' || strlen($code) < 2) {
        return ['ok' => false, 'error' => 'Name and a short sales code are required.'];
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Email looks invalid.'];
    }

    try {
        $dup = $pdo->prepare('SELECT id FROM sales_reps WHERE code = ? AND id != ? LIMIT 1');
        $dup->execute([$code, $id]);
        if ($dup->fetchColumn()) {
            return ['ok' => false, 'error' => 'That sales code is already in use.'];
        }
        if ($id > 0) {
            $pdo->prepare(
                'UPDATE sales_reps SET code = ?, name = ?, email = ?, phone = ?, active = ?, notes = ?, updated_at = NOW() WHERE id = ?'
            )->execute([
                $code,
                $name,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $active,
                $notes !== '' ? $notes : null,
                $id,
            ]);
            return ['ok' => true, 'id' => $id, 'message' => 'Updated sales rep ' . $code . '.'];
        }
        $pdo->prepare(
            'INSERT INTO sales_reps (code, name, email, phone, active, notes) VALUES (?,?,?,?,?,?)'
        )->execute([
            $code,
            $name,
            $email !== '' ? $email : null,
            $phone !== '' ? $phone : null,
            $active,
            $notes !== '' ? $notes : null,
        ]);
        $newId = (int) $pdo->lastInsertId();
        return ['ok' => true, 'id' => $newId, 'message' => 'Added sales rep ' . $code . '.'];
    } catch (Throwable $e) {
        error_log('pbj_sales_rep_save: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not save sales rep.'];
    }
}

/**
 * Attribute a new house / individual starter to a sales rep (first attribution wins).
 *
 * @return array{ok:bool,attribution_id?:int,sales_rep_id?:int,skipped?:bool,error?:string}
 */
function pbj_attribute_sale(
    PDO $pdo,
    int $userId,
    int $restaurantId,
    string $planId,
    string $salesCode,
    string $source = 'form'
): array {
    pbj_ensure_sales_tables($pdo);
    if ($userId <= 0) {
        return ['ok' => false, 'error' => 'bad_user'];
    }
    $code = pbj_normalize_sales_code($salesCode);
    if ($code === '') {
        return ['ok' => false, 'skipped' => true, 'error' => 'no_code'];
    }
    $rep = pbj_sales_rep_by_code($pdo, $code);
    if (!$rep || empty($rep['active'])) {
        return ['ok' => false, 'skipped' => true, 'error' => 'unknown_or_inactive_code'];
    }
    $repId = (int) $rep['id'];
    $plan = function_exists('pbj_plan_by_id') ? pbj_plan_by_id($planId) : null;
    $planId = $plan ? $plan['id'] : $planId;
    $cents = pbj_plan_price_cents($planId);

    try {
        // First-touch: if this house or user already attributed, keep it
        if ($restaurantId > 0) {
            $chk = $pdo->prepare('SELECT id, sales_rep_id FROM sales_attributions WHERE restaurant_id = ? LIMIT 1');
            $chk->execute([$restaurantId]);
            $ex = $chk->fetch(PDO::FETCH_ASSOC);
            if ($ex) {
                return [
                    'ok' => true,
                    'skipped' => true,
                    'attribution_id' => (int) $ex['id'],
                    'sales_rep_id' => (int) $ex['sales_rep_id'],
                ];
            }
        } else {
            $chk = $pdo->prepare(
                'SELECT id, sales_rep_id FROM sales_attributions WHERE user_id = ? AND restaurant_id IS NULL LIMIT 1'
            );
            $chk->execute([$userId]);
            $ex = $chk->fetch(PDO::FETCH_ASSOC);
            if ($ex) {
                return [
                    'ok' => true,
                    'skipped' => true,
                    'attribution_id' => (int) $ex['id'],
                    'sales_rep_id' => (int) $ex['sales_rep_id'],
                ];
            }
        }

        $pdo->prepare(
            'INSERT INTO sales_attributions
             (sales_rep_id, user_id, restaurant_id, plan_id, sales_code, source, status, monthly_plan_cents, attributed_at)
             VALUES (?,?,?,?,?,?,\'trialing\',?,NOW())'
        )->execute([
            $repId,
            $userId,
            $restaurantId > 0 ? $restaurantId : null,
            $planId,
            $code,
            substr($source, 0, 32),
            $cents,
        ]);
        $aid = (int) $pdo->lastInsertId();

        // Mirror onto restaurant settings / user billing for easy reads
        if ($restaurantId > 0 && function_exists('pbj_update_restaurant_billing_status')) {
            // Keep sales block in settings
            try {
                $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
                $stmt->execute([$restaurantId]);
                $raw = $stmt->fetchColumn();
                $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
                $settings['sales'] = [
                    'sales_rep_id' => $repId,
                    'sales_code' => $code,
                    'attribution_id' => $aid,
                    'attributed_at' => date('c'),
                    'source' => $source,
                ];
                $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
                if ($raw !== false && $raw !== null) {
                    $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                        ->execute([$json, $restaurantId]);
                } else {
                    $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?,?,NOW())')
                        ->execute([$restaurantId, $json]);
                }
            } catch (Throwable $e) {
                // ignore mirror failure
            }
        }
        if (function_exists('pbj_save_user_billing')) {
            pbj_save_user_billing($pdo, $userId, [
                'sales_rep_id' => $repId,
                'sales_code' => $code,
                'sales_attribution_id' => $aid,
            ]);
        }

        error_log("pbj_attribute_sale rep#{$repId} code={$code} user#{$userId} rid={$restaurantId} aid={$aid}");
        return ['ok' => true, 'attribution_id' => $aid, 'sales_rep_id' => $repId];
    } catch (Throwable $e) {
        error_log('pbj_attribute_sale: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'attribute_failed'];
    }
}

/**
 * Mark attribution paid + write subscription commission for this month (idempotent).
 */
function pbj_sales_mark_converted(
    PDO $pdo,
    int $userId,
    int $restaurantId = 0,
    string $planId = '',
    ?string $externalId = null
): void {
    pbj_ensure_sales_tables($pdo);
    if ($userId <= 0) {
        return;
    }
    try {
        $attr = null;
        if ($restaurantId > 0) {
            $stmt = $pdo->prepare('SELECT * FROM sales_attributions WHERE restaurant_id = ? ORDER BY id ASC LIMIT 1');
            $stmt->execute([$restaurantId]);
            $attr = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if (!$attr) {
            $stmt = $pdo->prepare(
                'SELECT * FROM sales_attributions WHERE user_id = ? ORDER BY id ASC LIMIT 1'
            );
            $stmt->execute([$userId]);
            $attr = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if (!$attr) {
            return;
        }
        $aid = (int) $attr['id'];
        $repId = (int) $attr['sales_rep_id'];
        $rid = (int) ($attr['restaurant_id'] ?? $restaurantId);
        if ($planId === '') {
            $planId = (string) ($attr['plan_id'] ?? '');
        }
        $cents = (int) ($attr['monthly_plan_cents'] ?? 0);
        if ($cents <= 0 && $planId !== '') {
            $cents = pbj_plan_price_cents($planId);
        }
        $pdo->prepare(
            "UPDATE sales_attributions
             SET status = 'paid',
                 converted_at = COALESCE(converted_at, NOW()),
                 plan_id = COALESCE(NULLIF(?, ''), plan_id),
                 monthly_plan_cents = CASE WHEN ? > 0 THEN ? ELSE monthly_plan_cents END
             WHERE id = ?"
        )->execute([$planId, $cents, $cents, $aid]);

        // First (and each) subscription period commission
        pbj_sales_record_subscription_commission($pdo, $repId, $aid, $rid, $userId, $planId, $cents, $externalId);
    } catch (Throwable $e) {
        error_log('pbj_sales_mark_converted: ' . $e->getMessage());
    }
}

/**
 * Record monthly subscription commission (30%). Idempotent per rep/house/month.
 */
function pbj_sales_record_subscription_commission(
    PDO $pdo,
    int $repId,
    int $attributionId,
    int $restaurantId,
    int $userId,
    string $planId,
    int $baseCents,
    ?string $externalId = null,
    ?string $periodYm = null
): bool {
    pbj_ensure_sales_tables($pdo);
    if ($repId <= 0) {
        return false;
    }
    $rate = pbj_commission_rate_pct('subscription');
    if ($baseCents <= 0 && $planId !== '') {
        $baseCents = pbj_plan_price_cents($planId);
    }
    if ($baseCents <= 0) {
        return false;
    }
    $comm = pbj_commission_cents($baseCents, $rate);
    $periodYm = $periodYm ?: date('Y-m');
    $ext = $externalId !== null && $externalId !== '' ? substr($externalId, 0, 80) : ('sub-' . $periodYm);

    try {
        // Manual de-dupe (unique key includes nullable restaurant_id carefully)
        $chk = $pdo->prepare(
            "SELECT id FROM sales_commission_events
             WHERE sales_rep_id = ? AND product = 'subscription' AND period_ym = ?
               AND COALESCE(restaurant_id,0) = ? AND COALESCE(user_id,0) = ?
             LIMIT 1"
        );
        $chk->execute([$repId, $periodYm, $restaurantId, $userId]);
        if ($chk->fetchColumn()) {
            return true; // already recorded this month
        }

        $pdo->prepare(
            "INSERT INTO sales_commission_events
             (sales_rep_id, attribution_id, restaurant_id, user_id, product, event_type, rate_pct,
              base_amount_cents, commission_cents, period_ym, status, external_id, note)
             VALUES (?,?,?,?, 'subscription', 'recurring', ?, ?, ?, ?, 'earned', ?, ?)"
        )->execute([
            $repId,
            $attributionId > 0 ? $attributionId : null,
            $restaurantId > 0 ? $restaurantId : null,
            $userId > 0 ? $userId : null,
            $rate,
            $baseCents,
            $comm,
            $periodYm,
            $ext,
            'Recurring 30% subscription commission',
        ]);
        return true;
    } catch (Throwable $e) {
        // unique collision = already logged
        if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'uq_sce')) {
            return true;
        }
        error_log('pbj_sales_record_subscription_commission: ' . $e->getMessage());
        return false;
    }
}

/**
 * Record one-time startup commission (20%) — ready when product ships.
 *
 * @return array{ok:bool,error?:string}
 */
function pbj_sales_record_startup_commission(
    PDO $pdo,
    int $repId,
    int $userId,
    int $restaurantId,
    int $baseCents,
    ?string $externalId = null
): array {
    pbj_ensure_sales_tables($pdo);
    $rate = pbj_commission_rate_pct('startup');
    if ($repId <= 0 || $baseCents <= 0) {
        return ['ok' => false, 'error' => 'bad_args'];
    }
    $comm = pbj_commission_cents($baseCents, $rate);
    $ext = $externalId ?: ('startup-' . $restaurantId . '-' . $userId);
    try {
        $pdo->prepare(
            "INSERT INTO sales_commission_events
             (sales_rep_id, restaurant_id, user_id, product, event_type, rate_pct,
              base_amount_cents, commission_cents, period_ym, status, external_id, note)
             VALUES (?,?,?, 'startup', 'one_time', ?, ?, ?, NULL, 'earned', ?, ?)"
        )->execute([
            $repId,
            $restaurantId > 0 ? $restaurantId : null,
            $userId > 0 ? $userId : null,
            $rate,
            $baseCents,
            $comm,
            substr($ext, 0, 80),
            'One-time 20% startup fee commission',
        ]);
        return ['ok' => true];
    } catch (Throwable $e) {
        if (str_contains($e->getMessage(), 'Duplicate')) {
            return ['ok' => true];
        }
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Record butler recurring commission (15%) — ready when product ships.
 */
function pbj_sales_record_butler_commission(
    PDO $pdo,
    int $repId,
    int $userId,
    int $restaurantId,
    int $baseCents,
    ?string $periodYm = null,
    ?string $externalId = null
): array {
    pbj_ensure_sales_tables($pdo);
    $rate = pbj_commission_rate_pct('butler');
    $periodYm = $periodYm ?: date('Y-m');
    if ($repId <= 0 || $baseCents <= 0) {
        return ['ok' => false, 'error' => 'bad_args'];
    }
    $comm = pbj_commission_cents($baseCents, $rate);
    try {
        $chk = $pdo->prepare(
            "SELECT id FROM sales_commission_events
             WHERE sales_rep_id = ? AND product = 'butler' AND period_ym = ?
               AND COALESCE(restaurant_id,0) = ? LIMIT 1"
        );
        $chk->execute([$repId, $periodYm, $restaurantId]);
        if ($chk->fetchColumn()) {
            return ['ok' => true];
        }
        $pdo->prepare(
            "INSERT INTO sales_commission_events
             (sales_rep_id, restaurant_id, user_id, product, event_type, rate_pct,
              base_amount_cents, commission_cents, period_ym, status, external_id, note)
             VALUES (?,?,?, 'butler', 'recurring', ?, ?, ?, ?, 'earned', ?, ?)"
        )->execute([
            $repId,
            $restaurantId > 0 ? $restaurantId : null,
            $userId > 0 ? $userId : null,
            $rate,
            $baseCents,
            $comm,
            $periodYm,
            $externalId ? substr($externalId, 0, 80) : ('butler-' . $periodYm),
            'Recurring 15% butler commission',
        ]);
        return ['ok' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Admin re-assign house/user to a sales rep.
 *
 * @return array{ok:bool,message?:string,error?:string}
 */
function pbj_sales_admin_reassign(PDO $pdo, int $repId, int $userId, int $restaurantId, string $planId = ''): array {
    pbj_ensure_sales_tables($pdo);
    $rep = pbj_sales_rep_by_id($pdo, $repId);
    if (!$rep) {
        return ['ok' => false, 'error' => 'Sales rep not found.'];
    }
    if ($restaurantId > 0 && $userId <= 0) {
        try {
            $st = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1');
            $st->execute([$restaurantId]);
            $userId = (int) ($st->fetchColumn() ?: 0);
        } catch (Throwable $e) {
            $userId = 0;
        }
        if ($planId === '' && function_exists('pbj_restaurant_plan_id')) {
            $planId = pbj_restaurant_plan_id($pdo, $restaurantId);
        }
    }
    if ($userId <= 0 && $restaurantId <= 0) {
        return ['ok' => false, 'error' => 'Need a user or restaurant.'];
    }
    if ($userId <= 0) {
        return ['ok' => false, 'error' => 'Could not resolve owner user for that house.'];
    }
    $code = (string) $rep['code'];
    // Clear existing attribution for this house/user so attribute can re-insert
    try {
        if ($restaurantId > 0) {
            $pdo->prepare('DELETE FROM sales_attributions WHERE restaurant_id = ?')->execute([$restaurantId]);
        } else {
            $pdo->prepare('DELETE FROM sales_attributions WHERE user_id = ? AND restaurant_id IS NULL')->execute([$userId]);
        }
    } catch (Throwable $e) {
        // continue
    }
    $res = pbj_attribute_sale($pdo, $userId, $restaurantId, $planId, $code, 'admin');
    if (empty($res['ok'])) {
        return ['ok' => false, 'error' => $res['error'] ?? 'reassign_failed'];
    }
    return ['ok' => true, 'message' => 'Attributed to ' . $code . '.'];
}

/**
 * Report rows for platform console.
 *
 * @return array{reps:list,attributions:list,events:list,rates:array,totals:array}
 */
function pbj_sales_commission_report(PDO $pdo): array {
    pbj_ensure_sales_tables($pdo);
    $reps = pbj_sales_reps_list($pdo, false);
    $attributions = [];
    $events = [];
    try {
        $attributions = $pdo->query(
            "SELECT a.*, r.name AS rep_name, r.code AS rep_code,
                    u.email AS owner_email, u.full_name AS owner_name, u.username AS owner_username,
                    rest.name AS restaurant_name, rest.invite_code
             FROM sales_attributions a
             LEFT JOIN sales_reps r ON r.id = a.sales_rep_id
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN restaurants rest ON rest.id = a.restaurant_id
             ORDER BY a.attributed_at DESC
             LIMIT 500"
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $attributions = [];
    }
    try {
        $events = $pdo->query(
            "SELECT e.*, r.name AS rep_name, r.code AS rep_code
             FROM sales_commission_events e
             LEFT JOIN sales_reps r ON r.id = e.sales_rep_id
             ORDER BY e.created_at DESC
             LIMIT 300"
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $events = [];
    }

    $totalsByRep = [];
    foreach ($reps as $rep) {
        $totalsByRep[(int) $rep['id']] = [
            'rep' => $rep,
            'attributions' => 0,
            'trialing' => 0,
            'paid' => 0,
            'est_mrr_cents' => 0,
            'est_monthly_comm_cents' => 0,
            'earned_comm_cents' => 0,
        ];
    }
    $rateSub = pbj_commission_rate_pct('subscription');
    foreach ($attributions as $a) {
        $rid = (int) $a['sales_rep_id'];
        if (!isset($totalsByRep[$rid])) {
            continue;
        }
        $totalsByRep[$rid]['attributions']++;
        $st = (string) ($a['status'] ?? '');
        if ($st === 'paid') {
            $totalsByRep[$rid]['paid']++;
            $base = (int) ($a['monthly_plan_cents'] ?? 0);
            if ($base <= 0) {
                $base = pbj_plan_price_cents((string) ($a['plan_id'] ?? ''));
            }
            $totalsByRep[$rid]['est_mrr_cents'] += $base;
            $totalsByRep[$rid]['est_monthly_comm_cents'] += pbj_commission_cents($base, $rateSub);
        } elseif ($st === 'trialing') {
            $totalsByRep[$rid]['trialing']++;
        }
    }
    foreach ($events as $e) {
        $rid = (int) $e['sales_rep_id'];
        if (!isset($totalsByRep[$rid])) {
            continue;
        }
        if (($e['status'] ?? '') === 'earned' || ($e['status'] ?? '') === 'paid') {
            $totalsByRep[$rid]['earned_comm_cents'] += (int) ($e['commission_cents'] ?? 0);
        }
    }

    return [
        'reps' => $reps,
        'attributions' => $attributions,
        'events' => $events,
        'rates' => pbj_commission_rates(),
        'totals' => array_values($totalsByRep),
    ];
}

/**
 * Public signup link for a rep code.
 */
function pbj_sales_signup_url(string $code, string $planId = 'crew_10'): string {
    $base = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
    $code = pbj_normalize_sales_code($code);
    $planId = $planId !== '' ? $planId : 'crew_10';
    return $base . '/register?plan=' . rawurlencode($planId) . '&ref=' . rawurlencode($code);
}

/**
 * Guests currently on DEMO-PBJ / SALES-PBJ (or flagged playgrounds).
 * Excludes playground owners and platform admins.
 *
 * @return list<array{
 *   user_id:int,username:string,email:string,full_name:string,access_status:string,
 *   playgrounds:list<string>,joined_at:?string,has_real_house:bool,real_house_id:int,
 *   real_house_name:string,already_attributed:bool,sales_code:?string
 * }>
 */
function pbj_sales_list_playground_guests(PDO $pdo): array {
    pbj_ensure_sales_tables($pdo);
    $out = [];
    try {
        $playgroundIds = [];
        if (function_exists('pbj_sales_playground_restaurant_ids')) {
            $playgroundIds = pbj_sales_playground_restaurant_ids($pdo);
        }
        $codes = function_exists('pbj_playground_invite_codes')
            ? pbj_playground_invite_codes()
            : ['DEMO-PBJ', 'SALES-PBJ'];
        foreach ($codes as $code) {
            $st = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ?');
            $st->execute([strtoupper((string) $code)]);
            while ($id = $st->fetchColumn()) {
                $playgroundIds[] = (int) $id;
            }
        }
        $playgroundIds = array_values(array_unique(array_filter(array_map('intval', $playgroundIds))));
        if (!$playgroundIds) {
            return [];
        }

        // Map playground id → label
        $labels = [];
        $in = implode(',', array_fill(0, count($playgroundIds), '?'));
        $st = $pdo->prepare("SELECT id, name, invite_code, owner_id FROM restaurants WHERE id IN ($in)");
        $st->execute($playgroundIds);
        $ownerIds = [];
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int) $r['id'];
            $labels[$rid] = strtoupper(trim((string) $r['invite_code'])) . ' · ' . (string) $r['name'];
            $ownerIds[$rid] = (int) $r['owner_id'];
        }

        $st = $pdo->prepare(
            "SELECT ur.user_id, ur.restaurant_id, ur.role AS mem_role, ur.joined_at,
                    u.username, u.email, u.full_name, u.access_status, u.role AS global_role
             FROM user_restaurant ur
             JOIN users u ON u.id = ur.user_id
             WHERE ur.restaurant_id IN ($in)
             ORDER BY ur.joined_at DESC"
        );
        $st->execute($playgroundIds);
        $byUser = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $uid = (int) $row['user_id'];
            $rid = (int) $row['restaurant_id'];
            // Skip playground owners
            if (!empty($ownerIds[$rid]) && $ownerIds[$rid] === $uid) {
                continue;
            }
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email !== '' && function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($email)) {
                continue;
            }
            if (!isset($byUser[$uid])) {
                $byUser[$uid] = [
                    'user_id' => $uid,
                    'username' => (string) ($row['username'] ?? ''),
                    'email' => (string) ($row['email'] ?? ''),
                    'full_name' => (string) ($row['full_name'] ?? ''),
                    'access_status' => (string) ($row['access_status'] ?? ''),
                    'playgrounds' => [],
                    'joined_at' => $row['joined_at'] ?? null,
                    'has_real_house' => false,
                    'real_house_id' => 0,
                    'real_house_name' => '',
                    'already_attributed' => false,
                    'sales_code' => null,
                ];
            }
            $label = $labels[$rid] ?? ('#' . $rid);
            if (!in_array($label, $byUser[$uid]['playgrounds'], true)) {
                $byUser[$uid]['playgrounds'][] = $label;
            }
            // Keep earliest join for sorting later
            if (!empty($row['joined_at']) && (
                empty($byUser[$uid]['joined_at']) || (string) $row['joined_at'] < (string) $byUser[$uid]['joined_at']
            )) {
                $byUser[$uid]['joined_at'] = $row['joined_at'];
            }
        }

        if (!$byUser) {
            return [];
        }

        // Real houses + attribution
        $uids = array_keys($byUser);
        $inU = implode(',', array_fill(0, count($uids), '?'));
        $st = $pdo->prepare(
            "SELECT r.id, r.name, r.owner_id, r.invite_code
             FROM restaurants r
             WHERE r.owner_id IN ($inU)"
        );
        $st->execute($uids);
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int) $r['id'];
            $uid = (int) $r['owner_id'];
            if (!isset($byUser[$uid])) {
                continue;
            }
            $isPg = function_exists('pbj_restaurant_is_playground') && pbj_restaurant_is_playground($pdo, $rid);
            if ($isPg) {
                continue;
            }
            $byUser[$uid]['has_real_house'] = true;
            $byUser[$uid]['real_house_id'] = $rid;
            $byUser[$uid]['real_house_name'] = (string) ($r['name'] ?? '');
        }

        $st = $pdo->prepare(
            "SELECT user_id, restaurant_id, sales_code FROM sales_attributions WHERE user_id IN ($inU)"
        );
        $st->execute($uids);
        while ($a = $st->fetch(PDO::FETCH_ASSOC)) {
            $uid = (int) $a['user_id'];
            if (!isset($byUser[$uid])) {
                continue;
            }
            $byUser[$uid]['already_attributed'] = true;
            $byUser[$uid]['sales_code'] = (string) ($a['sales_code'] ?? '');
        }

        $out = array_values($byUser);
        usort($out, static function ($a, $b) {
            return strcmp((string) ($b['joined_at'] ?? ''), (string) ($a['joined_at'] ?? ''));
        });
    } catch (Throwable $e) {
        error_log('pbj_sales_list_playground_guests: ' . $e->getMessage());
        return [];
    }
    return $out;
}

/**
 * Move a playground guest onto Sales & commissions:
 *  - create a real house if needed (or use existing owned house)
 *  - attribute to sales rep
 *  - optional free trial
 *  - detach DEMO/SALES playground seats
 *  - approve access
 *
 * @param array{
 *   plan_id?:string,restaurant_name?:string,start_trial?:bool,detach?:bool,mark_paid?:bool
 * } $opts
 * @return array{ok:bool,message?:string,error?:string,restaurant_id?:int,attribution_id?:int}
 */
function pbj_sales_convert_playground_user(PDO $pdo, int $userId, int $repId, array $opts = []): array {
    pbj_ensure_sales_tables($pdo);
    if ($userId <= 0 || $repId <= 0) {
        return ['ok' => false, 'error' => 'User and sales rep are required.'];
    }
    $rep = pbj_sales_rep_by_id($pdo, $repId);
    if (!$rep || empty($rep['active'])) {
        return ['ok' => false, 'error' => 'Pick an active sales rep.'];
    }

    try {
        $st = $pdo->prepare('SELECT id, username, email, full_name, access_status FROM users WHERE id = ? LIMIT 1');
        $st->execute([$userId]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return ['ok' => false, 'error' => 'User not found.'];
        }
        $email = strtolower(trim((string) ($user['email'] ?? '')));
        if ($email !== '' && function_exists('pbj_is_platform_admin') && pbj_is_platform_admin($email)) {
            return ['ok' => false, 'error' => 'Platform admins stay on playgrounds — don’t convert them.'];
        }
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Could not load user.'];
    }

    $planId = (string) ($opts['plan_id'] ?? 'crew_10');
    $plan = function_exists('pbj_plan_by_id') ? pbj_plan_by_id($planId) : null;
    if (!$plan || !empty($plan['coming'])) {
        $planId = function_exists('pbj_default_plan_id') ? pbj_default_plan_id() : 'crew_10';
        $plan = function_exists('pbj_plan_by_id') ? pbj_plan_by_id($planId) : null;
    } else {
        $planId = $plan['id'];
    }
    $isIndividual = ($planId === 'individual') || !empty($plan['limited']);
    $startTrial = !array_key_exists('start_trial', $opts) || !empty($opts['start_trial']);
    $detach = !array_key_exists('detach', $opts) || !empty($opts['detach']);
    $markPaid = !empty($opts['mark_paid']);
    $houseName = trim((string) ($opts['restaurant_name'] ?? ''));

    // Prefer existing non-playground house they own
    $restaurantId = 0;
    $createdHouse = false;
    try {
        $st = $pdo->prepare('SELECT id, name, invite_code FROM restaurants WHERE owner_id = ? ORDER BY id DESC');
        $st->execute([$userId]);
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int) $r['id'];
            if ($rid > 0 && function_exists('pbj_restaurant_is_playground') && pbj_restaurant_is_playground($pdo, $rid)) {
                continue;
            }
            $restaurantId = $rid;
            if ($houseName === '') {
                $houseName = (string) ($r['name'] ?? '');
            }
            break;
        }
    } catch (Throwable $e) {
        // ignore
    }

    if (!$isIndividual && $restaurantId <= 0) {
        if ($houseName === '') {
            $display = trim((string) ($user['full_name'] ?: $user['username'] ?: 'New'));
            $houseName = $display . "'s Kitchen";
        }
        if (!function_exists('pbj_create_restaurant')) {
            return ['ok' => false, 'error' => 'Cannot create restaurant (helper missing).'];
        }
        try {
            $created = pbj_create_restaurant($pdo, $userId, $houseName, $planId);
            $restaurantId = (int) ($created['id'] ?? 0);
            $createdHouse = true;
        } catch (Throwable $e) {
            error_log('pbj_sales_convert_playground_user create: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Could not create house: ' . $e->getMessage()];
        }
        if ($restaurantId <= 0) {
            return ['ok' => false, 'error' => 'House create returned no id.'];
        }
    }

    // Owner role + approve
    try {
        $pdo->prepare("UPDATE users SET role = 'owner', access_status = 'approved' WHERE id = ?")
            ->execute([$userId]);
    } catch (Throwable $e) {
        // ignore
    }

    // Plan on existing house
    if ($restaurantId > 0 && function_exists('pbj_save_restaurant_plan')) {
        try {
            pbj_save_restaurant_plan($pdo, $restaurantId, $planId);
        } catch (Throwable $e) {
            // non-fatal
        }
    }

    // Attribute to sales rep (replace any prior for this house/user)
    $code = (string) $rep['code'];
    try {
        if ($restaurantId > 0) {
            $pdo->prepare('DELETE FROM sales_attributions WHERE restaurant_id = ?')->execute([$restaurantId]);
        } else {
            $pdo->prepare('DELETE FROM sales_attributions WHERE user_id = ? AND restaurant_id IS NULL')->execute([$userId]);
        }
    } catch (Throwable $e) {
        // continue
    }
    $attr = pbj_attribute_sale($pdo, $userId, $restaurantId, $planId, $code, 'playground_convert');
    if (empty($attr['ok'])) {
        return ['ok' => false, 'error' => 'Attributed failed: ' . ($attr['error'] ?? 'unknown')];
    }

    // Trial or mark paid
    if ($markPaid) {
        if ($restaurantId > 0 && function_exists('pbj_update_restaurant_billing_status')) {
            pbj_update_restaurant_billing_status($pdo, $restaurantId, [
                'status' => 'paid',
                'plan_id' => $planId,
                'granted_by' => 'playground_convert',
                'paid_at' => date('c'),
            ]);
        }
        if (function_exists('pbj_save_user_billing')) {
            pbj_save_user_billing($pdo, $userId, [
                'status' => 'paid',
                'plan_id' => $planId,
                'paid_at' => date('c'),
            ]);
        }
        if (function_exists('pbj_sales_mark_converted')) {
            pbj_sales_mark_converted($pdo, $userId, $restaurantId, $planId, 'playground_convert');
        }
    } elseif ($startTrial && function_exists('pbj_start_free_trial')) {
        // Reset trial flags if they never paid — allow a fresh trial from convert
        if (function_exists('pbj_load_user_billing') && function_exists('pbj_save_user_billing')) {
            $ub = pbj_load_user_billing($pdo, $userId);
            $st = strtolower((string) ($ub['status'] ?? ''));
            if ($st !== 'paid' && $st !== 'lifetime_free') {
                // Clear prior trial_expired so start can run
                if (in_array($st, ['trial_expired', 'trialing', ''], true) || $st === '') {
                    pbj_save_user_billing($pdo, $userId, [
                        'status' => '',
                        'trial' => false,
                        'trial_started_at' => null,
                        'trial_ends_at' => null,
                        'trial_expired_at' => null,
                        // keep sales fields
                        'sales_rep_id' => (int) $rep['id'],
                        'sales_code' => $code,
                    ]);
                }
            }
        }
        $trial = pbj_start_free_trial($pdo, $userId, $planId, $restaurantId);
        // If already mid-trial or paid, still OK
        if (empty($trial['ok']) && ($trial['error'] ?? '') === 'already_paid') {
            // fine
        } elseif (empty($trial['ok']) && ($trial['error'] ?? '') === 'trial_already_used') {
            // Force a new trial window for convert path (admin intent)
            $days = function_exists('pbj_trial_days') ? pbj_trial_days() : 14;
            $ends = date('c', time() + max(1, $days) * 86400);
            if (function_exists('pbj_save_user_billing')) {
                pbj_save_user_billing($pdo, $userId, [
                    'status' => 'trialing',
                    'trial' => true,
                    'trial_started_at' => date('c'),
                    'trial_ends_at' => $ends,
                    'trial_days' => $days,
                    'plan_id' => $planId,
                    'sales_rep_id' => (int) $rep['id'],
                    'sales_code' => $code,
                ]);
            }
            if ($restaurantId > 0 && function_exists('pbj_update_restaurant_billing_status')) {
                pbj_update_restaurant_billing_status($pdo, $restaurantId, [
                    'status' => 'trialing',
                    'plan_id' => $planId,
                    'trial' => true,
                    'trial_started_at' => date('c'),
                    'trial_ends_at' => $ends,
                    'trial_days' => $days,
                ]);
            }
            try {
                $pdo->prepare("UPDATE users SET access_status = 'approved' WHERE id = ?")->execute([$userId]);
            } catch (Throwable $e) {
                // ignore
            }
        }
    }

    $detached = 0;
    if ($detach && function_exists('pbj_detach_user_from_playgrounds')) {
        // create_restaurant may have already detached; this catches any leftover seats
        $detached = pbj_detach_user_from_playgrounds($pdo, $userId);
    }

    $repLabel = (string) ($rep['name'] ?? '') . ' (' . $code . ')';
    $bits = ['Credited to ' . $repLabel];
    if ($createdHouse) {
        $bits[] = 'new house “' . $houseName . '”';
    } elseif ($restaurantId > 0) {
        $bits[] = 'house #' . $restaurantId;
    } elseif ($isIndividual) {
        $bits[] = 'Individual plan';
    }
    if ($markPaid) {
        $bits[] = 'marked paid';
    } elseif ($startTrial) {
        $bits[] = 'trial started';
    }
    if ($detach) {
        $bits[] = $detached > 0
            ? ('removed from ' . (int) $detached . ' playground seat(s)')
            : 'cleared playground seats';
    }

    error_log('pbj_sales_convert_playground_user user#' . $userId . ' rep#' . $repId . ' rid=' . $restaurantId);
    return [
        'ok' => true,
        'message' => implode(' · ', $bits) . '.',
        'restaurant_id' => $restaurantId,
        'attribution_id' => (int) ($attr['attribution_id'] ?? 0),
    ];
}
