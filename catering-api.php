<?php
/**
 * Catering JSON API — events + recipe trays with food-cost / quote snapshots.
 * Auth required except action=public_inquire (house invite code).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catering.inc.php';
if (is_file(__DIR__ . '/pbj-permissions.php')) {
    require_once __DIR__ . '/pbj-permissions.php';
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function cat_json(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function cat_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $j = json_decode($raw, true);
        if (is_array($j)) {
            return $j;
        }
    }
    return array_merge($_GET, $_POST);
}

$input = cat_input();
$action = strtolower(trim((string) ($input['action'] ?? $_GET['action'] ?? 'list')));

global $pdo;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    cat_json(['ok' => false, 'error' => 'db'], 500);
}
pbj_catering_ensure_tables($pdo);

function cat_get_event(PDO $pdo, int $rid, int $id): ?array {
    $stmt = $pdo->prepare('SELECT * FROM catering_events WHERE id = ? AND restaurant_id = ? LIMIT 1');
    $stmt->execute([$id, $rid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function cat_house_meta(PDO $pdo, int $rid): array {
    $stmt = $pdo->prepare('SELECT id, name, invite_code FROM restaurants WHERE id = ? LIMIT 1');
    $stmt->execute([$rid]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $code = strtoupper(trim((string) ($r['invite_code'] ?? '')));
    $path = $code !== '' ? '/catering/inquire?house=' . rawurlencode($code) : '/catering/inquire';
    return [
        'restaurant_id' => $rid,
        'restaurant_name' => (string) ($r['name'] ?? ''),
        'invite_code' => $code,
        'inquire_path' => $path,
        'inquire_url' => $code !== '' ? 'https://ilovepbj.shop' . $path : '',
    ];
}

/* ----- Public inquire ----- */
if ($action === 'public_inquire') {
    $house = strtoupper(preg_replace('/\s+/', '', (string) ($input['house'] ?? $input['code'] ?? $_GET['house'] ?? '')));
    if ($house === '') {
        cat_json(['ok' => false, 'error' => 'house_required'], 400);
    }
    $rest = function_exists('pbj_find_restaurant_by_code')
        ? pbj_find_restaurant_by_code($pdo, $house)
        : null;
    if (!$rest) {
        cat_json(['ok' => false, 'error' => 'house_not_found'], 404);
    }
    $rid = (int) $rest['id'];
    $name = pbj_catering_clip((string) ($input['contact_name'] ?? $input['name'] ?? ''), 120);
    $email = pbj_catering_clip((string) ($input['contact_email'] ?? $input['email'] ?? ''), 190);
    $phone = pbj_catering_clip((string) ($input['contact_phone'] ?? $input['phone'] ?? ''), 40);
    $eventName = pbj_catering_clip((string) ($input['event_name'] ?? ''), 190);
    $notes = pbj_catering_clip((string) ($input['notes'] ?? $input['message'] ?? ''), 4000);
    $delivery = pbj_catering_clip((string) ($input['delivery_notes'] ?? ''), 2000);
    $eventDate = trim((string) ($input['event_date'] ?? ''));
    $eventTime = pbj_catering_clip((string) ($input['event_time'] ?? ''), 40);
    $headcount = isset($input['headcount']) && $input['headcount'] !== ''
        ? max(0, (int) $input['headcount']) : null;

    if ($name === '') {
        cat_json(['ok' => false, 'error' => 'name_required'], 400);
    }
    if ($email === '' && $phone === '') {
        cat_json(['ok' => false, 'error' => 'contact_required'], 400);
    }
    if ($eventDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
        cat_json(['ok' => false, 'error' => 'bad_date'], 400);
    }
    if ($eventDate === '') {
        $eventDate = null;
    }

    $now = time();
    $last = (int) ($_SESSION['catering_inquire_last'] ?? 0);
    if ($last > 0 && ($now - $last) < 20) {
        cat_json(['ok' => false, 'error' => 'slow_down'], 429);
    }
    $_SESSION['catering_inquire_last'] = $now;

    $stmt = $pdo->prepare(
        'INSERT INTO catering_events
         (restaurant_id, status, contact_name, contact_email, contact_phone,
          event_name, event_date, event_time, headcount, delivery_notes, notes,
          source, target_fc_pct, created_by)
         VALUES (?, \'inquiry\', ?, ?, ?, ?, ?, ?, ?, ?, ?, \'share_link\', 30.00, NULL)'
    );
    $stmt->execute([
        $rid,
        $name,
        $email !== '' ? $email : null,
        $phone !== '' ? $phone : null,
        $eventName !== '' ? $eventName : null,
        $eventDate,
        $eventTime !== '' ? $eventTime : null,
        $headcount,
        $delivery !== '' ? $delivery : null,
        $notes !== '' ? $notes : null,
    ]);
    cat_json([
        'ok' => true,
        'id' => (int) $pdo->lastInsertId(),
        'restaurant_name' => (string) ($rest['name'] ?? ''),
        'message' => 'Thanks — your catering inquiry is in. The kitchen will follow up soon.',
    ]);
}

/* ----- Auth gate ----- */
if (empty($_SESSION['user_id']) && !(defined('AUTH_BYPASS') && AUTH_BYPASS)) {
    cat_json(['ok' => false, 'error' => 'auth'], 401);
}

$rid = pbj_catering_resolve_restaurant_id($pdo);
if ($rid <= 0) {
    cat_json(['ok' => false, 'error' => 'no_restaurant'], 400);
}

$canView = function_exists('pbj_can') ? pbj_can('admin.catering.view') : true;
$canEdit = function_exists('pbj_can') ? pbj_can('admin.catering.edit') : true;
if (!$canView && !$canEdit) {
    cat_json(['ok' => false, 'error' => 'forbidden'], 403);
}
$uid = (int) ($_SESSION['user_id'] ?? 0);

if ($action === 'meta' || $action === 'bootstrap') {
    cat_json([
        'ok' => true,
        'can_edit' => (bool) $canEdit,
        'house' => cat_house_meta($pdo, $rid),
        'statuses' => pbj_catering_statuses(),
    ]);
}

if ($action === 'list') {
    $status = strtolower(trim((string) ($input['status'] ?? '')));
    $sql = 'SELECT * FROM catering_events WHERE restaurant_id = ?';
    $params = [$rid];
    if ($status !== '' && $status !== 'all') {
        $sql .= ' AND status = ?';
        $params[] = pbj_catering_normalize_status($status);
    }
    $sql .= ' ORDER BY COALESCE(event_date, DATE(created_at)) ASC, id DESC LIMIT 200';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $out = [];
    foreach ($rows as $row) {
        $out[] = pbj_catering_enrich_event($pdo, $row);
    }
    cat_json([
        'ok' => true,
        'events' => $out,
        'can_edit' => (bool) $canEdit,
        'house' => cat_house_meta($pdo, $rid),
    ]);
}

if ($action === 'get') {
    $id = (int) ($input['id'] ?? 0);
    $row = $id > 0 ? cat_get_event($pdo, $rid, $id) : null;
    if (!$row) {
        cat_json(['ok' => false, 'error' => 'not_found'], 404);
    }
    cat_json([
        'ok' => true,
        'event' => pbj_catering_enrich_event($pdo, $row),
        'can_edit' => (bool) $canEdit,
        'house' => cat_house_meta($pdo, $rid),
    ]);
}

if ($action === 'create' || $action === 'update') {
    if (!$canEdit) {
        cat_json(['ok' => false, 'error' => 'forbidden'], 403);
    }
    $id = (int) ($input['id'] ?? 0);
    $name = pbj_catering_clip((string) ($input['contact_name'] ?? ''), 120);
    $email = pbj_catering_clip((string) ($input['contact_email'] ?? ''), 190);
    $phone = pbj_catering_clip((string) ($input['contact_phone'] ?? ''), 40);
    $eventName = pbj_catering_clip((string) ($input['event_name'] ?? ''), 190);
    $notes = pbj_catering_clip((string) ($input['notes'] ?? ''), 4000);
    $delivery = pbj_catering_clip((string) ($input['delivery_notes'] ?? ''), 2000);
    $eventDate = trim((string) ($input['event_date'] ?? ''));
    $eventTime = pbj_catering_clip((string) ($input['event_time'] ?? ''), 40);
    $headcount = isset($input['headcount']) && $input['headcount'] !== ''
        ? max(0, (int) $input['headcount']) : null;
    $status = pbj_catering_normalize_status((string) ($input['status'] ?? 'inquiry'));
    $targetFc = isset($input['target_fc_pct']) ? (float) $input['target_fc_pct'] : 30.0;
    if ($targetFc < 5 || $targetFc > 80) {
        $targetFc = 30.0;
    }
    $source = pbj_catering_clip((string) ($input['source'] ?? 'admin'), 32);
    if (!in_array($source, ['admin', 'public', 'share_link'], true)) {
        $source = 'admin';
    }
    if ($name === '' && $eventName === '') {
        cat_json(['ok' => false, 'error' => 'name_required'], 400);
    }
    if ($name === '') {
        $name = $eventName !== '' ? $eventName : 'Catering event';
    }
    if ($eventDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
        cat_json(['ok' => false, 'error' => 'bad_date'], 400);
    }
    if ($eventDate === '') {
        $eventDate = null;
    }

    if ($action === 'create') {
        $stmt = $pdo->prepare(
            'INSERT INTO catering_events
             (restaurant_id, status, contact_name, contact_email, contact_phone,
              event_name, event_date, event_time, headcount, delivery_notes, notes,
              source, target_fc_pct, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $rid, $status, $name,
            $email !== '' ? $email : null, $phone !== '' ? $phone : null,
            $eventName !== '' ? $eventName : null, $eventDate,
            $eventTime !== '' ? $eventTime : null, $headcount,
            $delivery !== '' ? $delivery : null, $notes !== '' ? $notes : null,
            $source, $targetFc, $uid > 0 ? $uid : null,
        ]);
        $id = (int) $pdo->lastInsertId();
    } else {
        if ($id <= 0 || !cat_get_event($pdo, $rid, $id)) {
            cat_json(['ok' => false, 'error' => 'not_found'], 404);
        }
        $stmt = $pdo->prepare(
            'UPDATE catering_events SET
                status = ?, contact_name = ?, contact_email = ?, contact_phone = ?,
                event_name = ?, event_date = ?, event_time = ?, headcount = ?,
                delivery_notes = ?, notes = ?, target_fc_pct = ?
             WHERE id = ? AND restaurant_id = ?'
        );
        $stmt->execute([
            $status, $name,
            $email !== '' ? $email : null, $phone !== '' ? $phone : null,
            $eventName !== '' ? $eventName : null, $eventDate,
            $eventTime !== '' ? $eventTime : null, $headcount,
            $delivery !== '' ? $delivery : null, $notes !== '' ? $notes : null,
            $targetFc, $id, $rid,
        ]);
    }
    $row = cat_get_event($pdo, $rid, $id);
    cat_json(['ok' => true, 'event' => pbj_catering_enrich_event($pdo, $row)]);
}

if ($action === 'delete') {
    if (!$canEdit) {
        cat_json(['ok' => false, 'error' => 'forbidden'], 403);
    }
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0 || !cat_get_event($pdo, $rid, $id)) {
        cat_json(['ok' => false, 'error' => 'not_found'], 404);
    }
    $pdo->prepare('DELETE FROM catering_event_items WHERE event_id = ? AND restaurant_id = ?')->execute([$id, $rid]);
    $pdo->prepare('DELETE FROM catering_events WHERE id = ? AND restaurant_id = ?')->execute([$id, $rid]);
    cat_json(['ok' => true]);
}

if ($action === 'set_status') {
    if (!$canEdit) {
        cat_json(['ok' => false, 'error' => 'forbidden'], 403);
    }
    $id = (int) ($input['id'] ?? 0);
    $status = pbj_catering_normalize_status((string) ($input['status'] ?? ''));
    if ($id <= 0 || !cat_get_event($pdo, $rid, $id)) {
        cat_json(['ok' => false, 'error' => 'not_found'], 404);
    }
    $pdo->prepare('UPDATE catering_events SET status = ? WHERE id = ? AND restaurant_id = ?')
        ->execute([$status, $id, $rid]);
    $row = cat_get_event($pdo, $rid, $id);
    cat_json(['ok' => true, 'event' => pbj_catering_enrich_event($pdo, $row)]);
}

if ($action === 'item_add' || $action === 'item_update') {
    if (!$canEdit) {
        cat_json(['ok' => false, 'error' => 'forbidden'], 403);
    }
    $eventId = (int) ($input['event_id'] ?? 0);
    $ev = $eventId > 0 ? cat_get_event($pdo, $rid, $eventId) : null;
    if (!$ev) {
        cat_json(['ok' => false, 'error' => 'not_found'], 404);
    }
    $itemId = (int) ($input['id'] ?? 0);
    $recipeId = pbj_catering_clip((string) ($input['recipe_id'] ?? ''), 64);
    $title = pbj_catering_clip((string) ($input['recipe_title'] ?? $input['title'] ?? ''), 190);
    if ($title === '') {
        cat_json(['ok' => false, 'error' => 'title_required'], 400);
    }
    $qty = (float) ($input['qty'] ?? 1);
    if ($qty <= 0) {
        $qty = 1;
    }
    if ($qty > 9999) {
        $qty = 9999;
    }
    $unit = pbj_catering_clip((string) ($input['unit_label'] ?? 'tray'), 40);
    if ($unit === '') {
        $unit = 'tray';
    }
    $notes = pbj_catering_clip((string) ($input['notes'] ?? ''), 255);
    $unitCost = array_key_exists('unit_cost_snapshot', $input) && $input['unit_cost_snapshot'] !== '' && $input['unit_cost_snapshot'] !== null
        ? round((float) $input['unit_cost_snapshot'], 4) : null;
    $sell = array_key_exists('sell_price_snapshot', $input) && $input['sell_price_snapshot'] !== '' && $input['sell_price_snapshot'] !== null
        ? round((float) $input['sell_price_snapshot'], 4) : null;

    if ($sell === null && $unitCost !== null) {
        $sug = pbj_catering_suggested_sell($unitCost, (float) ($ev['target_fc_pct'] ?? 30));
        if ($sug) {
            $sell = $sug['at_target'];
        }
    }

    if ($action === 'item_add') {
        $ordStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM catering_event_items WHERE event_id = ?');
        $ordStmt->execute([$eventId]);
        $sort = (int) $ordStmt->fetchColumn();
        $stmt = $pdo->prepare(
            'INSERT INTO catering_event_items
             (event_id, restaurant_id, recipe_id, recipe_title, qty, unit_label,
              unit_cost_snapshot, sell_price_snapshot, notes, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $eventId, $rid,
            $recipeId !== '' ? $recipeId : null, $title, $qty, $unit,
            $unitCost, $sell, $notes !== '' ? $notes : null, $sort,
        ]);
        $itemId = (int) $pdo->lastInsertId();
    } else {
        if ($itemId <= 0) {
            cat_json(['ok' => false, 'error' => 'not_found'], 404);
        }
        $chk = $pdo->prepare('SELECT id FROM catering_event_items WHERE id = ? AND event_id = ? AND restaurant_id = ?');
        $chk->execute([$itemId, $eventId, $rid]);
        if (!$chk->fetchColumn()) {
            cat_json(['ok' => false, 'error' => 'not_found'], 404);
        }
        $stmt = $pdo->prepare(
            'UPDATE catering_event_items SET
                recipe_id = ?, recipe_title = ?, qty = ?, unit_label = ?,
                unit_cost_snapshot = ?, sell_price_snapshot = ?, notes = ?
             WHERE id = ? AND event_id = ? AND restaurant_id = ?'
        );
        $stmt->execute([
            $recipeId !== '' ? $recipeId : null, $title, $qty, $unit,
            $unitCost, $sell, $notes !== '' ? $notes : null,
            $itemId, $eventId, $rid,
        ]);
    }
    $row = cat_get_event($pdo, $rid, $eventId);
    cat_json(['ok' => true, 'event' => pbj_catering_enrich_event($pdo, $row), 'item_id' => $itemId]);
}

if ($action === 'item_delete') {
    if (!$canEdit) {
        cat_json(['ok' => false, 'error' => 'forbidden'], 403);
    }
    $eventId = (int) ($input['event_id'] ?? 0);
    $itemId = (int) ($input['id'] ?? 0);
    if ($eventId <= 0 || $itemId <= 0 || !cat_get_event($pdo, $rid, $eventId)) {
        cat_json(['ok' => false, 'error' => 'not_found'], 404);
    }
    $pdo->prepare('DELETE FROM catering_event_items WHERE id = ? AND event_id = ? AND restaurant_id = ?')
        ->execute([$itemId, $eventId, $rid]);
    $row = cat_get_event($pdo, $rid, $eventId);
    cat_json(['ok' => true, 'event' => pbj_catering_enrich_event($pdo, $row)]);
}

cat_json(['ok' => false, 'error' => 'unknown_action'], 400);
