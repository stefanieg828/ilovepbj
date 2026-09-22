<?php
/**
 * Catering events + line items — schema ensure and helpers.
 * Inquiries are events with status=inquiry; bookings use quoted/confirmed/etc.
 */

function pbj_catering_ensure_tables(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS catering_events (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'inquiry',
                contact_name VARCHAR(120) NOT NULL DEFAULT '',
                contact_email VARCHAR(190) NULL,
                contact_phone VARCHAR(40) NULL,
                event_name VARCHAR(190) NULL,
                event_date DATE NULL,
                event_time VARCHAR(40) NULL,
                headcount INT UNSIGNED NULL,
                delivery_notes TEXT NULL,
                notes TEXT NULL,
                source VARCHAR(32) NOT NULL DEFAULT 'admin',
                target_fc_pct DECIMAL(5,2) NOT NULL DEFAULT 30.00,
                created_by INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_cat_rest_status (restaurant_id, status),
                INDEX idx_cat_rest_date (restaurant_id, event_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS catering_event_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id INT UNSIGNED NOT NULL,
                restaurant_id INT NOT NULL,
                recipe_id VARCHAR(64) NULL,
                recipe_title VARCHAR(190) NOT NULL DEFAULT '',
                qty DECIMAL(10,2) NOT NULL DEFAULT 1.00,
                unit_label VARCHAR(40) NULL DEFAULT 'tray',
                unit_cost_snapshot DECIMAL(12,4) NULL,
                sell_price_snapshot DECIMAL(12,4) NULL,
                notes VARCHAR(255) NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cat_item_event (event_id),
                INDEX idx_cat_item_rest (restaurant_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {
        error_log('pbj_catering_ensure_tables: ' . $e->getMessage());
    }
}

/** @return list<string> */
function pbj_catering_statuses(): array {
    return ['inquiry', 'quoted', 'confirmed', 'completed', 'cancelled'];
}

function pbj_catering_normalize_status(string $status): string {
    $status = strtolower(trim($status));
    return in_array($status, pbj_catering_statuses(), true) ? $status : 'inquiry';
}

function pbj_catering_clip(string $s, int $max): string {
    $s = trim($s);
    if ($max <= 0) {
        return '';
    }
    if (function_exists('mb_substr')) {
        return (string) mb_substr($s, 0, $max, 'UTF-8');
    }
    return strlen($s) <= $max ? $s : substr($s, 0, $max);
}

function pbj_catering_resolve_restaurant_id(PDO $pdo): int {
    if (function_exists('pbj_permissions_resolve_restaurant_id')) {
        $rid = (int) pbj_permissions_resolve_restaurant_id($pdo);
        if ($rid > 0) {
            return $rid;
        }
    }
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        return 0;
    }
    try {
        $own = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ? ORDER BY id DESC LIMIT 1');
        $own->execute([$uid]);
        $ownedId = (int) ($own->fetchColumn() ?: 0);
        if ($ownedId > 0) {
            return $ownedId;
        }
    } catch (Throwable $e) {
        // ignore
    }
    try {
        $stmt = $pdo->prepare('SELECT restaurant_id FROM user_restaurant WHERE user_id = ? ORDER BY joined_at ASC LIMIT 1');
        $stmt->execute([$uid]);
        $rid = $stmt->fetchColumn();
        return $rid ? (int) $rid : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * @return array{food_cost:float,quote_total:float,item_count:int,implied_fc:?float}
 */
function pbj_catering_rollup_items(array $items): array {
    $food = 0.0;
    $quote = 0.0;
    $n = 0;
    foreach ($items as $it) {
        if (!is_array($it)) {
            continue;
        }
        $qty = (float) ($it['qty'] ?? 0);
        if ($qty <= 0) {
            continue;
        }
        $n++;
        $uc = $it['unit_cost_snapshot'] ?? null;
        $sp = $it['sell_price_snapshot'] ?? null;
        if ($uc !== null && $uc !== '') {
            $food += $qty * (float) $uc;
        }
        if ($sp !== null && $sp !== '') {
            $quote += $qty * (float) $sp;
        }
    }
    $food = round($food, 2);
    $quote = round($quote, 2);
    $implied = $quote > 0 ? round(($food / $quote) * 1000) / 10 : null;
    return [
        'food_cost' => $food,
        'quote_total' => $quote,
        'item_count' => $n,
        'implied_fc' => $implied,
    ];
}

/**
 * @return array{at25:float,at275:float,at30:float,at_target:float}|null
 */
function pbj_catering_suggested_sell(?float $unitCost, float $targetFcPct = 30.0): ?array {
    if ($unitCost === null || $unitCost < 0 || !is_finite($unitCost)) {
        return null;
    }
    $t = $targetFcPct > 0 && $targetFcPct < 100 ? $targetFcPct / 100.0 : 0.30;
    $round = static function (float $n): float {
        return round($n, 2);
    };
    return [
        'at25' => $round($unitCost / 0.25),
        'at275' => $round($unitCost / 0.275),
        'at30' => $round($unitCost / 0.30),
        'at_target' => $round($unitCost / $t),
    ];
}

/** @return list<array<string,mixed>> */
function pbj_catering_fetch_items(PDO $pdo, int $eventId, int $restaurantId): array {
    $stmt = $pdo->prepare(
        'SELECT * FROM catering_event_items
         WHERE event_id = ? AND restaurant_id = ?
         ORDER BY sort_order ASC, id ASC'
    );
    $stmt->execute([$eventId, $restaurantId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($rows as &$r) {
        $r['id'] = (int) $r['id'];
        $r['event_id'] = (int) $r['event_id'];
        $r['restaurant_id'] = (int) $r['restaurant_id'];
        $r['qty'] = (float) $r['qty'];
        $r['unit_cost_snapshot'] = $r['unit_cost_snapshot'] !== null ? (float) $r['unit_cost_snapshot'] : null;
        $r['sell_price_snapshot'] = $r['sell_price_snapshot'] !== null ? (float) $r['sell_price_snapshot'] : null;
        $r['sort_order'] = (int) $r['sort_order'];
    }
    unset($r);
    return $rows;
}

/** @param array<string,mixed> $event @return array<string,mixed> */
function pbj_catering_enrich_event(PDO $pdo, array $event): array {
    $eid = (int) ($event['id'] ?? 0);
    $rid = (int) ($event['restaurant_id'] ?? 0);
    $items = $eid > 0 && $rid > 0 ? pbj_catering_fetch_items($pdo, $eid, $rid) : [];
    $roll = pbj_catering_rollup_items($items);
    $event['id'] = $eid;
    $event['restaurant_id'] = $rid;
    $event['headcount'] = isset($event['headcount']) && $event['headcount'] !== null && $event['headcount'] !== ''
        ? (int) $event['headcount'] : null;
    $event['target_fc_pct'] = (float) ($event['target_fc_pct'] ?? 30);
    $event['created_by'] = isset($event['created_by']) && $event['created_by'] !== null
        ? (int) $event['created_by'] : null;
    $event['items'] = $items;
    $event['rollups'] = $roll;
    return $event;
}
