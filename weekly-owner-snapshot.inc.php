<?php
/**
 * Weekly owner snapshot — server helpers (opt-in + cron email from shared state).
 */

if (!function_exists('pbj_permissions_load_settings')) {
    require_once __DIR__ . '/pbj-permissions.php';
}

function pbj_weekly_snapshot_ensure_shared_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_shared_state (
        restaurant_id INT NOT NULL,
        state_key VARCHAR(64) NOT NULL,
        business_date DATE NOT NULL,
        payload LONGTEXT NOT NULL,
        version INT UNSIGNED NOT NULL DEFAULT 1,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT NULL,
        PRIMARY KEY (restaurant_id, state_key, business_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/** @return array<string,mixed>|null */
function pbj_weekly_snapshot_load_shared(PDO $pdo, int $rid, string $key, string $date = '2000-01-01'): ?array {
    if ($rid <= 0 || $key === '') {
        return null;
    }
    try {
        pbj_weekly_snapshot_ensure_shared_table($pdo);
        $stmt = $pdo->prepare(
            'SELECT payload FROM restaurant_shared_state
             WHERE restaurant_id = ? AND state_key = ? AND business_date = ? LIMIT 1'
        );
        $stmt->execute([$rid, $key, $date]);
        $raw = $stmt->fetchColumn();
        if (!$raw) {
            return null;
        }
        $j = json_decode((string) $raw, true);
        return is_array($j) ? $j : null;
    } catch (Throwable $e) {
        return null;
    }
}

/** @return array{start:string,end:string,label:string} */
function pbj_weekly_snapshot_last_week_range(?DateTimeInterface $ref = null): array {
    $ref = $ref ? DateTimeImmutable::createFromInterface($ref) : new DateTimeImmutable('today');
    $dow = (int) $ref->format('N');
    $mondayThis = $ref->modify('-' . ($dow - 1) . ' days');
    $start = $mondayThis->modify('-7 days');
    $end = $start->modify('+6 days');
    return [
        'start' => $start->format('Y-m-d'),
        'end' => $end->format('Y-m-d'),
        'label' => 'Last week (Mon–Sun)',
    ];
}

function pbj_weekly_snapshot_opt_in_get(array $settings): bool {
    return !empty($settings['weeklyOwnerSnapshotEmail']);
}

function pbj_weekly_snapshot_opt_in_set(PDO $pdo, int $rid, bool $on): void {
    $settings = pbj_permissions_load_settings($pdo, $rid);
    $settings['weeklyOwnerSnapshotEmail'] = $on;
    $settings['weeklyOwnerSnapshotEmailUpdatedAt'] = time();
    pbj_permissions_save_settings($pdo, $rid, $settings);
}

function pbj_weekly_snapshot_build_email_text(PDO $pdo, int $rid, string $houseName, array $range): string {
    $start = $range['start'];
    $end = $range['end'];
    $lines = [];
    $lines[] = 'Weekly owner snapshot';
    if ($houseName !== '') {
        $lines[] = $houseName;
    }
    $lines[] = ($range['label'] ?? 'Week') . ': ' . $start . ' → ' . $end;
    $lines[] = '';

    $sales = pbj_weekly_snapshot_load_shared($pdo, $rid, 'admin_sales_v1', '2000-01-01');
    $days = (is_array($sales) && !empty($sales['days']) && is_array($sales['days'])) ? $sales['days'] : [];
    $totalSales = 0.0;
    $salesN = 0;
    foreach ($days as $d) {
        if (!is_array($d) || empty($d['date']) || $d['date'] < $start || $d['date'] > $end) {
            continue;
        }
        $net = null;
        if (isset($d['net']) && is_numeric($d['net'])) {
            $net = (float) $d['net'];
        } elseif (isset($d['gross']) && is_numeric($d['gross'])) {
            $net = (float) $d['gross'];
        }
        if ($net !== null) {
            $totalSales += $net;
            $salesN++;
        }
    }

    $labor = pbj_weekly_snapshot_load_shared($pdo, $rid, 'admin_labor_v1', '2000-01-01');
    $ldays = (is_array($labor) && !empty($labor['days']) && is_array($labor['days'])) ? $labor['days'] : [];
    $totalLabor = 0.0;
    $laborN = 0;
    foreach ($ldays as $d) {
        if (!is_array($d) || empty($d['date']) || $d['date'] < $start || $d['date'] > $end) {
            continue;
        }
        $amt = null;
        foreach (['cost', 'totalLabor', 'labor$'] as $k) {
            if (isset($d[$k]) && $d[$k] !== '' && is_numeric($d[$k])) {
                $amt = (float) $d[$k];
                break;
            }
        }
        if ($amt === null && !empty($d['entries']) && is_array($d['entries'])) {
            $sum = 0.0;
            $any = false;
            foreach ($d['entries'] as $e) {
                if (!is_array($e)) {
                    continue;
                }
                if (isset($e['laborCost']) && is_numeric($e['laborCost'])) {
                    $sum += (float) $e['laborCost'];
                    $any = true;
                }
            }
            if ($any) {
                $amt = $sum;
            }
        }
        if ($amt !== null) {
            $totalLabor += $amt;
            $laborN++;
        }
    }

    $lines[] = '—— Sales & labor ——';
    if ($salesN || $laborN) {
        $lines[] = 'Net sales: ' . ($salesN ? ('$' . number_format($totalSales, 0)) : '—') .
            ($salesN ? (' (' . $salesN . ' days)') : '');
        $lines[] = 'Labor $: ' . ($laborN ? ('$' . number_format($totalLabor, 0)) : '—') .
            ($laborN ? (' (' . $laborN . ' days)') : '');
        if ($salesN && $totalSales > 0 && $laborN) {
            $lines[] = 'Labor %: ' . number_format(($totalLabor / $totalSales) * 100, 1) . '%';
        }
    } else {
        $lines[] = 'No sales/labor synced for this week yet.';
    }
    $lines[] = '';

    $lines[] = '—— Food cost % (menu) ——';
    $lines[] = 'Open the in-app snapshot for dish FC% (menu + recipe costs live on your devices).';
    $lines[] = '';

    $board = pbj_weekly_snapshot_load_shared($pdo, $rid, '86_board_v1', '2000-01-01');
    $items = [];
    if (is_array($board)) {
        if (!empty($board['board']) && is_array($board['board'])) {
            $items = $board['board'];
        } elseif (!empty($board['items']) && is_array($board['items'])) {
            $items = $board['items'];
        }
    }
    $active = [];
    $n86 = 0;
    $nLow = 0;
    foreach ($items as $it) {
        if (!is_array($it) || trim((string) ($it['name'] ?? '')) === '') {
            continue;
        }
        $active[] = $it;
        if (($it['status'] ?? '') === 'low') {
            $nLow++;
        } else {
            $n86++;
        }
    }
    $lines[] = '—— 86 board ——';
    if ($active) {
        $lines[] = $n86 . ' 86 · ' . $nLow . ' low';
        foreach (array_slice($active, 0, 12) as $it) {
            $st = (($it['status'] ?? '') === 'low') ? 'low' : '86';
            $note = trim((string) ($it['note'] ?? ''));
            $lines[] = '  · [' . $st . '] ' . trim((string) $it['name']) . ($note !== '' ? (' — ' . $note) : '');
        }
    } else {
        $lines[] = "Nothing currently 86'd (or board not synced yet).";
    }
    $lines[] = '';

    $completions = 0;
    $daysHit = [];
    $titles = [];
    $period = new DatePeriod(
        new DateTimeImmutable($start),
        new DateInterval('P1D'),
        (new DateTimeImmutable($end))->modify('+1 day')
    );
    foreach ($period as $dt) {
        $d = $dt->format('Y-m-d');
        $pack = pbj_weekly_snapshot_load_shared($pdo, $rid, 'ops_list_completions_v1', $d);
        if ($pack === null && $d === $end) {
            $pack = pbj_weekly_snapshot_load_shared($pdo, $rid, 'ops_list_completions_v1', '2000-01-01');
        }
        if (!is_array($pack) || empty($pack['events']) || !is_array($pack['events'])) {
            continue;
        }
        foreach ($pack['events'] as $ev) {
            if (!is_array($ev)) {
                continue;
            }
            $ed = (string) ($ev['date'] ?? '');
            if ($ed === '' && !empty($ev['at'])) {
                $ts = (int) $ev['at'];
                if ($ts > 1000000000000) {
                    $ts = (int) floor($ts / 1000);
                }
                $ed = date('Y-m-d', $ts);
            }
            if ($ed < $start || $ed > $end) {
                continue;
            }
            $completions++;
            $daysHit[$ed] = true;
            $t = (string) ($ev['listTitle'] ?? $ev['pageTitle'] ?? 'List');
            $titles[$t] = ($titles[$t] ?? 0) + 1;
        }
    }
    $lines[] = '—— Checklists ——';
    if ($completions > 0) {
        $lines[] = $completions . ' completion(s) across ' . count($daysHit) . ' day(s)';
        arsort($titles);
        $i = 0;
        foreach ($titles as $t => $c) {
            $lines[] = '  · ' . $t . ' ×' . $c;
            if (++$i >= 6) {
                break;
            }
        }
    } else {
        $lines[] = 'No checklist completions synced for this week yet.';
    }
    $lines[] = '';
    $base = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
    $lines[] = 'Open in app: ' . $base . '/admin/reports/weekly-snapshot';
    $lines[] = '— ilovepbj ops';
    return implode("\n", $lines);
}

/**
 * @return array{ok:bool,dry_run:bool,scanned:int,sent:int,skipped:int,errors:int,details:list}
 */
function pbj_process_weekly_owner_snapshots(PDO $pdo, array $opts = []): array {
    $dry = !empty($opts['dry_run']);
    $forceRid = (int) ($opts['force_restaurant_id'] ?? 0);
    $out = [
        'ok' => true,
        'dry_run' => $dry,
        'scanned' => 0,
        'sent' => 0,
        'skipped' => 0,
        'errors' => 0,
        'details' => [],
    ];

    if (!$dry && function_exists('pbj_mail_is_configured') && !pbj_mail_is_configured()) {
        return array_merge($out, ['ok' => false, 'error' => 'mail_not_configured']);
    }

    pbj_permissions_ensure_settings_table($pdo);
    $range = pbj_weekly_snapshot_last_week_range();

    try {
        if ($forceRid > 0) {
            $stmt = $pdo->prepare('SELECT restaurant_id, settings_json FROM restaurant_settings WHERE restaurant_id = ?');
            $stmt->execute([$forceRid]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (!$rows) {
                $rows = [['restaurant_id' => $forceRid, 'settings_json' => '{}']];
            }
        } else {
            $stmt = $pdo->query('SELECT restaurant_id, settings_json FROM restaurant_settings');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        }
    } catch (Throwable $e) {
        return array_merge($out, ['ok' => false, 'error' => $e->getMessage()]);
    }

    foreach ($rows as $row) {
        $rid = (int) ($row['restaurant_id'] ?? 0);
        if ($rid <= 0) {
            continue;
        }
        $settings = json_decode((string) ($row['settings_json'] ?? ''), true);
        if (!is_array($settings)) {
            $settings = [];
        }
        $out['scanned']++;
        if (!pbj_weekly_snapshot_opt_in_get($settings) && $forceRid <= 0) {
            $out['skipped']++;
            continue;
        }

        $houseName = '';
        try {
            $st = $pdo->prepare('SELECT name FROM restaurants WHERE id = ? LIMIT 1');
            $st->execute([$rid]);
            $houseName = trim((string) ($st->fetchColumn() ?: ''));
        } catch (Throwable $e) {
            $houseName = '';
        }

        $emails = function_exists('pbj_restaurant_owner_emails')
            ? pbj_restaurant_owner_emails($pdo, $rid)
            : [];
        if (!$emails) {
            $out['skipped']++;
            $out['details'][] = ['restaurant_id' => $rid, 'ok' => false, 'error' => 'no_owner_email'];
            continue;
        }

        $body = pbj_weekly_snapshot_build_email_text($pdo, $rid, $houseName, $range);
        $subject = 'Weekly owner snapshot' . ($houseName !== '' ? (' · ' . $houseName) : '') .
            ' · ' . $range['start'] . ' → ' . $range['end'];

        foreach ($emails as $to) {
            if ($dry) {
                $out['sent']++;
                $out['details'][] = [
                    'ok' => true,
                    'dry_run' => true,
                    'restaurant_id' => $rid,
                    'email' => $to,
                    'house' => $houseName,
                ];
                continue;
            }
            $ok = function_exists('pbj_send_mail') ? pbj_send_mail($to, $subject, $body) : false;
            if ($ok) {
                $out['sent']++;
                $out['details'][] = [
                    'ok' => true,
                    'restaurant_id' => $rid,
                    'email' => $to,
                    'house' => $houseName,
                ];
            } else {
                $out['errors']++;
                $out['details'][] = [
                    'ok' => false,
                    'restaurant_id' => $rid,
                    'email' => $to,
                    'error' => 'send_failed',
                ];
            }
        }
    }

    return $out;
}
