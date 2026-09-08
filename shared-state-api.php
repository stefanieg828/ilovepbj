<?php
/**
 * Restaurant-shared checklist / prep state API.
 * GET  ?key=heat_prep_v1&date=YYYY-MM-DD
 * POST JSON { key, date, payload, baseVersion }
 *       → saves if baseVersion matches (or row missing); else 409 + server state
 *       → auto-merges done flags by item id using doneUpdatedAt
 */
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

function ensure_shared_table(PDO $pdo): void {
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

function ensure_demo_restaurant(PDO $pdo): int {
    $stmt = $pdo->query("SELECT id FROM restaurants WHERE invite_code = 'DEMO-PBJ' LIMIT 1");
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int) $id;
    }
    $owner = 1;
    try {
        $pdo->prepare("INSERT INTO restaurants (name, owner_id, invite_code) VALUES ('Demo Kitchen', ?, 'DEMO-PBJ')")
            ->execute([$owner]);
        return (int) $pdo->lastInsertId();
    } catch (Exception $e) {
        $stmt = $pdo->query("SELECT id FROM restaurants ORDER BY id ASC LIMIT 1");
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }
        throw $e;
    }
}

function resolve_restaurant_id(PDO $pdo): int {
    $uid = (int) ($_SESSION['user_id'] ?? 0);

    if ($uid > 0) {
        // Prefer a restaurant they own (real paid / free house) over playground memberships
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

        $stmt = $pdo->prepare(
            "SELECT restaurant_id FROM user_restaurant WHERE user_id = ? ORDER BY joined_at ASC LIMIT 1"
        );
        $stmt->execute([$uid]);
        $rid = $stmt->fetchColumn();
        if ($rid) {
            return (int) $rid;
        }

        // Do NOT auto-join real accounts to the demo playground — that would mix kitchens.
        // Only local AUTH_BYPASS testing uses the shared demo house below.
        return 0;
    }

    // AUTH_BYPASS tester (user_id 0) — shared Demo Kitchen for local testing only
    if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
        return ensure_demo_restaurant($pdo);
    }
    return 0;
}

function valid_key(string $key): bool {
    return (bool) preg_match('/^[a-z0-9_]{3,64}$/i', $key);
}

function valid_date(string $d): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
        return false;
    }
    $p = explode('-', $d);
    return checkdate((int) $p[1], (int) $p[2], (int) $p[0]);
}

function norm_merge_key($s) {
    $s = strtolower(trim((string) $s));
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
}

function put_done_map(array &$doneMap, $key, $it) {
    if ($key === '') {
        return;
    }
    $ts = (int) ($it['doneUpdatedAt'] ?? 0);
    // If no timestamp, treat checked as slightly newer than unchecked default
    if ($ts <= 0 && !empty($it['done'])) {
        $ts = 1;
    }
    $photoAt = (int) ($it['photoAt'] ?? 0);
    $photoUrl = isset($it['photoUrl']) ? (string) $it['photoUrl'] : null;
    $photoDataUrl = isset($it['photoDataUrl']) ? (string) $it['photoDataUrl'] : (isset($it['photo']) ? (string) $it['photo'] : null);
    if ($photoAt <= 0 && ($photoUrl || $photoDataUrl)) {
        $photoAt = 1;
    }
    if (!isset($doneMap[$key]) || $ts >= $doneMap[$key]['ts']) {
        $prev = $doneMap[$key] ?? null;
        $doneMap[$key] = [
            'ts' => $ts,
            'done' => !empty($it['done']),
            'doneUpdatedAt' => (int) ($it['doneUpdatedAt'] ?? $ts),
            'photoAt' => $photoAt,
            'photoUrl' => $photoUrl,
            'photoDataUrl' => $photoDataUrl,
        ];
        // Keep newer photo even if done flag came from older side
        if ($prev && (int) ($prev['photoAt'] ?? 0) > $photoAt) {
            $doneMap[$key]['photoAt'] = (int) $prev['photoAt'];
            $doneMap[$key]['photoUrl'] = $prev['photoUrl'] ?? null;
            $doneMap[$key]['photoDataUrl'] = $prev['photoDataUrl'] ?? null;
        }
    } else {
        // Older done stamp, but maybe newer photo
        if ($photoAt > (int) ($doneMap[$key]['photoAt'] ?? 0)) {
            $doneMap[$key]['photoAt'] = $photoAt;
            $doneMap[$key]['photoUrl'] = $photoUrl;
            $doneMap[$key]['photoDataUrl'] = $photoDataUrl;
        }
    }
}

/**
 * Merge two payload arrays: prefer newer structureAt for skeleton,
 * merge per-item done by doneUpdatedAt.
 * Matches items by id, recipeKey, or label so phone/laptop random ids still sync checks.
 */
function merge_shared_payloads($server, $client) {
    if (!is_array($server) || !$server) {
        return is_array($client) ? $client : [];
    }
    if (!is_array($client) || !$client) {
        return $server;
    }

    $sAt = (int) ($server['structureAt'] ?? 0);
    $cAt = (int) ($client['structureAt'] ?? 0);
    $base = $cAt >= $sAt ? json_decode(json_encode($client), true) : json_decode(json_encode($server), true);
    $other = $cAt >= $sAt ? $server : $client;

    $doneMap = [];

    $collectStations = function ($payload) use (&$doneMap) {
        if (empty($payload['stations']) || !is_array($payload['stations'])) {
            return;
        }
        foreach ($payload['stations'] as $st) {
            $sid = (string) ($st['id'] ?? '');
            foreach ($st['items'] ?? [] as $it) {
                $iid = (string) ($it['id'] ?? '');
                $rk = norm_merge_key($it['recipeKey'] ?? '');
                $lb = norm_merge_key($it['label'] ?? '');
                if ($iid !== '') {
                    put_done_map($doneMap, 'st:id:' . $sid . ':' . $iid, $it);
                }
                if ($rk !== '') {
                    put_done_map($doneMap, 'st:rk:' . $sid . ':' . $rk, $it);
                }
                if ($lb !== '') {
                    put_done_map($doneMap, 'st:lb:' . $sid . ':' . $lb, $it);
                }
            }
        }
    };

    $collectLists = function ($payload) use (&$doneMap) {
        if (empty($payload['lists']) || !is_array($payload['lists'])) {
            return;
        }
        foreach ($payload['lists'] as $list) {
            $lid = (string) ($list['id'] ?? '');
            foreach ($list['items'] ?? [] as $it) {
                $iid = (string) ($it['id'] ?? '');
                $lb = norm_merge_key($it['label'] ?? '');
                if ($iid !== '') {
                    put_done_map($doneMap, 'li:id:' . $lid . ':' . $iid, $it);
                }
                if ($lb !== '') {
                    put_done_map($doneMap, 'li:lb:' . $lid . ':' . $lb, $it);
                }
            }
        }
    };

    $collectStations($server);
    $collectStations($client);
    $collectLists($server);
    $collectLists($client);

    $pickDone = function ($keys) use ($doneMap) {
        $best = null;
        foreach ($keys as $k) {
            if ($k !== '' && isset($doneMap[$k])) {
                if ($best === null || $doneMap[$k]['ts'] >= $best['ts']) {
                    $best = $doneMap[$k];
                }
            }
        }
        return $best;
    };

    // Apply done onto base stations
    if (!empty($base['stations']) && is_array($base['stations'])) {
        foreach ($base['stations'] as &$st) {
            $sid = (string) ($st['id'] ?? '');
            if (empty($st['items']) || !is_array($st['items'])) {
                continue;
            }
            foreach ($st['items'] as &$it) {
                $best = $pickDone([
                    'st:id:' . $sid . ':' . ($it['id'] ?? ''),
                    'st:rk:' . $sid . ':' . norm_merge_key($it['recipeKey'] ?? ''),
                    'st:lb:' . $sid . ':' . norm_merge_key($it['label'] ?? ''),
                ]);
                if ($best) {
                    $it['done'] = $best['done'];
                    $it['doneUpdatedAt'] = $best['doneUpdatedAt'];
                    if ((int) ($best['photoAt'] ?? 0) >= (int) ($it['photoAt'] ?? 0)) {
                        if (!empty($best['photoUrl'])) {
                            $it['photoUrl'] = $best['photoUrl'];
                            unset($it['photoDataUrl'], $it['photo']);
                        } elseif (!empty($best['photoDataUrl'])) {
                            $it['photoDataUrl'] = $best['photoDataUrl'];
                            unset($it['photoUrl'], $it['photo']);
                        } elseif (array_key_exists('photoUrl', $best) || array_key_exists('photoDataUrl', $best)) {
                            // Explicit clear from newer photoAt with empty urls
                            if ((int) ($best['photoAt'] ?? 0) > 0 && empty($best['photoUrl']) && empty($best['photoDataUrl'])) {
                                unset($it['photoUrl'], $it['photoDataUrl'], $it['photo']);
                            }
                        }
                        if ((int) ($best['photoAt'] ?? 0) > 0) {
                            $it['photoAt'] = (int) $best['photoAt'];
                        }
                    }
                }
            }
            unset($it);
        }
        unset($st);
    }

    // Apply done onto base lists (open/close/cleaning)
    if (!empty($base['lists']) && is_array($base['lists'])) {
        foreach ($base['lists'] as &$list) {
            $lid = (string) ($list['id'] ?? '');
            if (empty($list['items']) || !is_array($list['items'])) {
                continue;
            }
            foreach ($list['items'] as &$it) {
                $best = $pickDone([
                    'li:id:' . $lid . ':' . ($it['id'] ?? ''),
                    'li:lb:' . $lid . ':' . norm_merge_key($it['label'] ?? ''),
                ]);
                if ($best) {
                    $it['done'] = $best['done'];
                    $it['doneUpdatedAt'] = $best['doneUpdatedAt'];
                    if ((int) ($best['photoAt'] ?? 0) >= (int) ($it['photoAt'] ?? 0)) {
                        if (!empty($best['photoUrl'])) {
                            $it['photoUrl'] = $best['photoUrl'];
                            unset($it['photoDataUrl'], $it['photo']);
                        } elseif (!empty($best['photoDataUrl'])) {
                            $it['photoDataUrl'] = $best['photoDataUrl'];
                            unset($it['photoUrl'], $it['photo']);
                        } elseif ((int) ($best['photoAt'] ?? 0) > 0 && empty($best['photoUrl']) && empty($best['photoDataUrl'])) {
                            unset($it['photoUrl'], $it['photoDataUrl'], $it['photo']);
                        }
                        if ((int) ($best['photoAt'] ?? 0) > 0) {
                            $it['photoAt'] = (int) $best['photoAt'];
                        }
                    }
                }
            }
            unset($it);
        }
        unset($list);
    }

    // Simple checklist map: checks[key] = { done, doneUpdatedAt }
    // Used by Showtime FOH lists (open/close, sidework, bar)
    if (isset($server['checks']) || isset($client['checks'])) {
        $baseChecks = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['checks']) || !is_array($payload['checks'])) {
                continue;
            }
            foreach ($payload['checks'] as $ck => $val) {
                $ck = (string) $ck;
                if ($ck === '') {
                    continue;
                }
                // Support legacy true/false values
                if (!is_array($val)) {
                    $val = ['done' => !empty($val), 'doneUpdatedAt' => !empty($val) ? 1 : 0];
                }
                $ts = (int) ($val['doneUpdatedAt'] ?? 0);
                if ($ts <= 0 && !empty($val['done'])) {
                    $ts = 1;
                }
                if (!isset($baseChecks[$ck]) || $ts >= (int) ($baseChecks[$ck]['doneUpdatedAt'] ?? 0)) {
                    $baseChecks[$ck] = [
                        'done' => !empty($val['done']),
                        'doneUpdatedAt' => $ts,
                    ];
                }
            }
        }
        $base['checks'] = $baseChecks;
    }

    // Floor layout (positions): newer layoutAt wins entirely
    if (isset($server['layout']) || isset($client['layout'])) {
        $sL = (int) ($server['layoutAt'] ?? 0);
        $cL = (int) ($client['layoutAt'] ?? 0);
        if ($cL >= $sL && !empty($client['layout']) && is_array($client['layout'])) {
            $base['layout'] = $client['layout'];
            $base['layoutAt'] = $cL;
        } elseif (!empty($server['layout']) && is_array($server['layout'])) {
            $base['layout'] = $server['layout'];
            $base['layoutAt'] = $sL;
        } elseif (!empty($client['layout']) && is_array($client['layout'])) {
            $base['layout'] = $client['layout'];
            $base['layoutAt'] = $cL;
        }
    }
    // Team roster people: merge by id using updatedAt; honor deletion tombstones
    // so Remove on one device doesn't get re-added from the other side.
    if (isset($server['people']) || isset($client['people'])
        || isset($server['deletedIds']) || isset($client['deletedIds'])) {
        // Merge deletedIds: keep max delete timestamp per person id
        $deleted = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['deletedIds']) || !is_array($payload['deletedIds'])) {
                continue;
            }
            foreach ($payload['deletedIds'] as $did => $dts) {
                $did = (string) $did;
                if ($did === '') {
                    continue;
                }
                $dts = (int) $dts;
                if (!isset($deleted[$did]) || $dts >= $deleted[$did]) {
                    $deleted[$did] = $dts;
                }
            }
        }

        $map = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['people']) || !is_array($payload['people'])) {
                continue;
            }
            foreach ($payload['people'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $id = (string) ($row['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $ts = (int) ($row['updatedAt'] ?? 0);
                // Skip if deleted at or after this row's last update
                if (isset($deleted[$id]) && $deleted[$id] >= $ts) {
                    continue;
                }
                if (!isset($map[$id]) || $ts >= (int) ($map[$id]['updatedAt'] ?? 0)) {
                    $map[$id] = $row;
                }
            }
        }
        // Drop tombstones for ids that were re-added with a newer updatedAt
        foreach ($map as $id => $row) {
            $ts = (int) ($row['updatedAt'] ?? 0);
            if (isset($deleted[$id]) && $ts > $deleted[$id]) {
                unset($deleted[$id]);
            }
        }
        $base['people'] = array_values($map);
        $base['deletedIds'] = $deleted;

        // Prefer newer permissions blob
        $sP = (int) ($server['structureAt'] ?? 0);
        $cP = (int) ($client['structureAt'] ?? 0);
        if ($cP >= $sP && isset($client['permissions'])) {
            $base['permissions'] = $client['permissions'];
        } elseif (isset($server['permissions'])) {
            $base['permissions'] = $server['permissions'];
        } elseif (isset($client['permissions'])) {
            $base['permissions'] = $client['permissions'];
        }
    }

    // Employee spotlight awards: merge by id using updatedAt; honor deletion tombstones
    if (isset($server['awards']) || isset($client['awards'])
        || isset($server['currentId']) || isset($client['currentId'])) {
        $deletedAwards = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['deletedIds']) || !is_array($payload['deletedIds'])) {
                continue;
            }
            // Only treat as award tombstones when awards are present in either side
            foreach ($payload['deletedIds'] as $did => $dts) {
                $did = (string) $did;
                if ($did === '') {
                    continue;
                }
                $dts = (int) $dts;
                if (!isset($deletedAwards[$did]) || $dts >= $deletedAwards[$did]) {
                    $deletedAwards[$did] = $dts;
                }
            }
        }
        $awardMap = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['awards']) || !is_array($payload['awards'])) {
                continue;
            }
            foreach ($payload['awards'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $id = (string) ($row['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $ts = (int) ($row['updatedAt'] ?? $row['at'] ?? 0);
                if (isset($deletedAwards[$id]) && $deletedAwards[$id] >= $ts) {
                    continue;
                }
                if (!isset($awardMap[$id]) || $ts >= (int) ($awardMap[$id]['updatedAt'] ?? $awardMap[$id]['at'] ?? 0)) {
                    $awardMap[$id] = $row;
                }
            }
        }
        foreach ($awardMap as $id => $row) {
            $ts = (int) ($row['updatedAt'] ?? $row['at'] ?? 0);
            if (isset($deletedAwards[$id]) && $ts > $deletedAwards[$id]) {
                unset($deletedAwards[$id]);
            }
        }
        $base['awards'] = array_values($awardMap);
        if (!empty($deletedAwards)) {
            $base['deletedIds'] = $deletedAwards;
        }
        // Prefer newer currentId by structureAt (already chose base), else keep either
        $sCurAt = (int) ($server['structureAt'] ?? 0);
        $cCurAt = (int) ($client['structureAt'] ?? 0);
        if ($cCurAt >= $sCurAt && array_key_exists('currentId', $client)) {
            $base['currentId'] = $client['currentId'];
        } elseif (array_key_exists('currentId', $server)) {
            $base['currentId'] = $server['currentId'];
        } elseif (array_key_exists('currentId', $client)) {
            $base['currentId'] = $client['currentId'];
        }
        // Drop currentId if award was deleted
        if (!empty($base['currentId']) && isset($deletedAwards[(string) $base['currentId']])) {
            $base['currentId'] = null;
        }
    }

    // Floor section definitions (add/edit/delete): newer sectionsAt wins
    if (isset($server['sections']) || isset($client['sections'])) {
        $sS = (int) ($server['sectionsAt'] ?? 0);
        $cS = (int) ($client['sectionsAt'] ?? 0);
        if ($cS >= $sS && !empty($client['sections']) && is_array($client['sections'])) {
            $base['sections'] = $client['sections'];
            $base['sectionsAt'] = $cS;
        } elseif (!empty($server['sections']) && is_array($server['sections'])) {
            $base['sections'] = $server['sections'];
            $base['sectionsAt'] = $sS;
        } elseif (!empty($client['sections']) && is_array($client['sections'])) {
            $base['sections'] = $client['sections'];
            $base['sectionsAt'] = $cS;
        }
    }

    // Floor plan tables: merge by table id using updatedAt
    if (isset($server['tables']) || isset($client['tables'])) {
        $mergedTables = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['tables']) || !is_array($payload['tables'])) {
                continue;
            }
            foreach ($payload['tables'] as $tid => $trow) {
                $tid = (string) $tid;
                if (!is_array($trow)) {
                    continue;
                }
                $ts = (int) ($trow['updatedAt'] ?? $trow['seatedAt'] ?? 0);
                if (!isset($mergedTables[$tid]) || $ts >= (int) ($mergedTables[$tid]['updatedAt'] ?? $mergedTables[$tid]['seatedAt'] ?? 0)) {
                    $mergedTables[$tid] = $trow;
                    if (!isset($mergedTables[$tid]['updatedAt'])) {
                        $mergedTables[$tid]['updatedAt'] = $ts;
                    }
                }
            }
        }
        $base['tables'] = $mergedTables;
    }
    // Floor assignments / turn goal meta
    if (isset($server['assignments']) || isset($client['assignments'])) {
        $sA = (int) ($server['assignmentsAt'] ?? 0);
        $cA = (int) ($client['assignmentsAt'] ?? 0);
        $base['assignments'] = ($cA >= $sA)
            ? ($client['assignments'] ?? $server['assignments'] ?? [])
            : ($server['assignments'] ?? $client['assignments'] ?? []);
        $base['assignmentsAt'] = max($sA, $cA);
    }
    if (isset($server['turnGoal']) || isset($client['turnGoal'])) {
        $sT = (int) ($server['turnGoalAt'] ?? 0);
        $cT = (int) ($client['turnGoalAt'] ?? 0);
        if ($cT >= $sT) {
            $base['turnGoal'] = $client['turnGoal'] ?? $server['turnGoal'] ?? 45;
            $base['turnGoalAt'] = $cT;
        } else {
            $base['turnGoal'] = $server['turnGoal'] ?? $client['turnGoal'] ?? 45;
            $base['turnGoalAt'] = $sT;
        }
    }

    // Reservations / waitlist: merge by id using updatedAt; honor deletion tombstones
    // so Remove on one device doesn't get re-added from the other side.
    $resWaitTombstoneKeys = [
        'reservations' => 'deletedReservationIds',
        'waitlist' => 'deletedWaitlistIds',
        'togos' => 'deletedTogoIds',
    ];
    foreach ($resWaitTombstoneKeys as $listKey => $tombKey) {
        if (!isset($server[$listKey]) && !isset($client[$listKey])
            && !isset($server[$tombKey]) && !isset($client[$tombKey])) {
            continue;
        }
        $deleted = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload[$tombKey]) || !is_array($payload[$tombKey])) {
                continue;
            }
            foreach ($payload[$tombKey] as $did => $dts) {
                $did = (string) $did;
                if ($did === '') {
                    continue;
                }
                $dts = (int) $dts;
                if (!isset($deleted[$did]) || $dts >= $deleted[$did]) {
                    $deleted[$did] = $dts;
                }
            }
        }

        $map = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload[$listKey]) || !is_array($payload[$listKey])) {
                continue;
            }
            foreach ($payload[$listKey] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $id = (string) ($row['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $ts = (int) ($row['updatedAt'] ?? $row['addedAt'] ?? $row['createdAt'] ?? 0);
                // Skip if deleted at or after this row's last update
                if (isset($deleted[$id]) && $deleted[$id] >= $ts) {
                    continue;
                }
                if (!isset($map[$id]) || $ts >= (int) ($map[$id]['updatedAt'] ?? $map[$id]['addedAt'] ?? $map[$id]['createdAt'] ?? 0)) {
                    $map[$id] = $row;
                    if (!isset($map[$id]['updatedAt'])) {
                        $map[$id]['updatedAt'] = $ts;
                    }
                }
            }
        }
        // Drop tombstones for ids that were re-added with a newer updatedAt
        foreach ($map as $id => $row) {
            $ts = (int) ($row['updatedAt'] ?? $row['addedAt'] ?? $row['createdAt'] ?? 0);
            if (isset($deleted[$id]) && $ts > $deleted[$id]) {
                unset($deleted[$id]);
            }
        }
        $base[$listKey] = array_values($map);
        $base[$tombKey] = $deleted;
    }
    if (isset($server['quoteMins']) || isset($client['quoteMins'])) {
        $sQ = (int) ($server['quoteMinsAt'] ?? 0);
        $cQ = (int) ($client['quoteMinsAt'] ?? 0);
        if ($cQ >= $sQ) {
            $base['quoteMins'] = $client['quoteMins'] ?? $server['quoteMins'] ?? 20;
            $base['quoteMinsAt'] = $cQ;
        } else {
            $base['quoteMins'] = $server['quoteMins'] ?? $client['quoteMins'] ?? 20;
            $base['quoteMinsAt'] = $sQ;
        }
    }

    // Shared notes (sidework handoff): newer notesAt wins
    $sNotesAt = (int) ($server['notesAt'] ?? 0);
    $cNotesAt = (int) ($client['notesAt'] ?? 0);
    if ($cNotesAt > 0 || $sNotesAt > 0 || isset($server['notes']) || isset($client['notes'])) {
        if ($cNotesAt >= $sNotesAt) {
            if (array_key_exists('notes', $client)) {
                $base['notes'] = (string) $client['notes'];
                $base['notesAt'] = $cNotesAt;
            } elseif (array_key_exists('notes', $server)) {
                $base['notes'] = (string) $server['notes'];
                $base['notesAt'] = $sNotesAt;
            }
        } else {
            if (array_key_exists('notes', $server)) {
                $base['notes'] = (string) $server['notes'];
                $base['notesAt'] = $sNotesAt;
            } elseif (array_key_exists('notes', $client)) {
                $base['notes'] = (string) $client['notes'];
                $base['notesAt'] = $cNotesAt;
            }
        }
    }

    // Preserve meta
    if (!isset($base['lastSyncAt']) && isset($other['lastSyncAt'])) {
        $base['lastSyncAt'] = $other['lastSyncAt'];
    }
    if (!isset($base['lastSyncCount']) && isset($other['lastSyncCount'])) {
        $base['lastSyncCount'] = $other['lastSyncCount'];
    }

    // Ops list completion events: merge by event id (dedupeKey), keep newest first, cap 100
    if (isset($server['events']) || isset($client['events'])) {
        $map = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['events']) || !is_array($payload['events'])) {
                continue;
            }
            foreach ($payload['events'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $id = (string) ($row['id'] ?? '');
                $dk = (string) ($row['dedupeKey'] ?? '');
                $key = $id !== '' ? ('id:' . $id) : ($dk !== '' ? ('dk:' . $dk) : '');
                if ($key === '') {
                    continue;
                }
                $ts = (int) ($row['at'] ?? 0);
                if (!isset($map[$key]) || $ts >= (int) ($map[$key]['at'] ?? 0)) {
                    $map[$key] = $row;
                }
            }
        }
        // Also collapse duplicate dedupeKeys keeping newest
        $byDedupe = [];
        foreach ($map as $row) {
            $dk = (string) ($row['dedupeKey'] ?? $row['id'] ?? '');
            if ($dk === '') {
                continue;
            }
            $ts = (int) ($row['at'] ?? 0);
            if (!isset($byDedupe[$dk]) || $ts >= (int) ($byDedupe[$dk]['at'] ?? 0)) {
                $byDedupe[$dk] = $row;
            }
        }
        $events = array_values($byDedupe);
        usort($events, static function ($a, $b) {
            return ((int) ($b['at'] ?? 0)) <=> ((int) ($a['at'] ?? 0));
        });
        if (count($events) > 100) {
            $events = array_slice($events, 0, 100);
        }
        $base['events'] = $events;
    }

    // Report day logs (Daily Sales / Labor / Cash): merge by business date using updatedAt
    // so two managers editing different days (or same day) don't wipe each other.
    if (isset($server['days']) || isset($client['days'])) {
        $dayMap = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['days']) || !is_array($payload['days'])) {
                continue;
            }
            foreach ($payload['days'] as $day) {
                if (!is_array($day)) {
                    continue;
                }
                $date = (string) ($day['date'] ?? '');
                if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    continue;
                }
                $ts = (int) ($day['updatedAt'] ?? 0);
                if (!isset($dayMap[$date]) || $ts >= (int) ($dayMap[$date]['updatedAt'] ?? 0)) {
                    if (!isset($day['updatedAt'])) {
                        $day['updatedAt'] = $ts ?: 1;
                    }
                    $dayMap[$date] = $day;
                }
            }
        }
        $days = array_values($dayMap);
        usort($days, static function ($a, $b) {
            return strcmp((string) ($a['date'] ?? ''), (string) ($b['date'] ?? ''));
        });
        // Keep last ~18 months of days max
        if (count($days) > 550) {
            $days = array_slice($days, -550);
        }
        $base['days'] = $days;
    }

    // Inventory product master: merge items{} by key using updatedAt / priceUpdatedAt
    if (isset($server['items']) || isset($client['items'])) {
        $mergedItems = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['items']) || !is_array($payload['items'])) {
                continue;
            }
            foreach ($payload['items'] as $ik => $row) {
                $ik = (string) $ik;
                if ($ik === '' || !is_array($row)) {
                    continue;
                }
                $ts = (int) ($row['updatedAt'] ?? $row['priceUpdatedAt'] ?? $row['enteredAt'] ?? 0);
                $prev = $mergedItems[$ik] ?? null;
                $prevTs = $prev ? (int) ($prev['updatedAt'] ?? $prev['priceUpdatedAt'] ?? $prev['enteredAt'] ?? 0) : -1;
                if (!$prev || $ts >= $prevTs) {
                    // Prefer newer whole row, but keep non-empty setup fields if newer row blanks them
                    if ($prev && $ts === $prevTs) {
                        // same stamp — fill blanks from the other
                        foreach ($prev as $pk => $pv) {
                            if ((!array_key_exists($pk, $row) || $row[$pk] === '' || $row[$pk] === null) && $pv !== '' && $pv !== null) {
                                $row[$pk] = $pv;
                            }
                        }
                    }
                    if (!isset($row['updatedAt'])) {
                        $row['updatedAt'] = $ts ?: time() * 1000;
                    }
                    $mergedItems[$ik] = $row;
                } elseif ($prev) {
                    // older incoming — only fill blanks on existing
                    foreach ($row as $pk => $pv) {
                        if (($prev[$pk] === '' || $prev[$pk] === null || !array_key_exists($pk, $prev)) && $pv !== '' && $pv !== null) {
                            $mergedItems[$ik][$pk] = $pv;
                        }
                    }
                }
            }
        }
        $base['items'] = $mergedItems;
    }

    // Inventory count sessions: merge by session id (keep newest 50)
    if (isset($server['sessions']) || isset($client['sessions'])) {
        $sessMap = [];
        foreach ([$server, $client] as $payload) {
            if (empty($payload['sessions']) || !is_array($payload['sessions'])) {
                continue;
            }
            foreach ($payload['sessions'] as $sess) {
                if (!is_array($sess)) {
                    continue;
                }
                $id = (string) ($sess['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $ts = (int) ($sess['at'] ?? 0);
                if (!isset($sessMap[$id]) || $ts >= (int) ($sessMap[$id]['at'] ?? 0)) {
                    $sessMap[$id] = $sess;
                }
            }
        }
        $sessions = array_values($sessMap);
        usort($sessions, static function ($a, $b) {
            return ((int) ($a['at'] ?? 0)) <=> ((int) ($b['at'] ?? 0));
        });
        if (count($sessions) > 50) {
            $sessions = array_slice($sessions, -50);
        }
        $base['sessions'] = $sessions;
    }

    $base['structureAt'] = max($sAt, $cAt);
    return $base;
}

try {
    ensure_shared_table($pdo);
    $restaurantId = resolve_restaurant_id($pdo);
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        $userId = null;
    }

    // No house membership — local-only mode (never read/write another kitchen’s data)
    if ($restaurantId <= 0) {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'GET') {
            $key = trim((string) ($_GET['key'] ?? ''));
            echo json_encode([
                'ok' => true,
                'exists' => false,
                'restaurantId' => 0,
                'key' => $key,
                'localOnly' => true,
            ]);
            exit;
        }
        // POST: accept but do not persist server-side for isolated users without a house
        echo json_encode(['ok' => true, 'saved' => false, 'restaurantId' => 0, 'localOnly' => true]);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $key = trim((string) ($_GET['key'] ?? ''));
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        if (!valid_key($key) || !valid_date($date)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_request']);
            exit;
        }

        $stmt = $pdo->prepare(
            "SELECT payload, version, updated_at, updated_by
             FROM restaurant_shared_state
             WHERE restaurant_id = ? AND state_key = ? AND business_date = ?"
        );
        $stmt->execute([$restaurantId, $key, $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode([
                'ok' => true,
                'exists' => false,
                'restaurantId' => $restaurantId,
                'key' => $key,
                'date' => $date,
                'version' => 0,
                'payload' => null,
                'updatedAt' => null,
            ]);
            exit;
        }

        $payload = json_decode($row['payload'], true);
        echo json_encode([
            'ok' => true,
            'exists' => true,
            'restaurantId' => $restaurantId,
            'key' => $key,
            'date' => $date,
            'version' => (int) $row['version'],
            'payload' => $payload,
            'updatedAt' => $row['updated_at'],
            'updatedBy' => $row['updated_by'] !== null ? (int) $row['updated_by'] : null,
        ]);
        exit;
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_json']);
            exit;
        }

        $key = trim((string) ($body['key'] ?? ''));
        $date = trim((string) ($body['date'] ?? date('Y-m-d')));
        $clientPayload = $body['payload'] ?? null;
        $baseVersion = isset($body['baseVersion']) ? (int) $body['baseVersion'] : 0;
        $force = !empty($body['force']);

        if (!valid_key($key) || !valid_date($date) || !is_array($clientPayload)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_request']);
            exit;
        }

        // Read current row (no long row-lock: merge is quick; last-writer merges with latest)
        $stmt = $pdo->prepare(
            "SELECT payload, version FROM restaurant_shared_state
             WHERE restaurant_id = ? AND state_key = ? AND business_date = ?"
        );
        $stmt->execute([$restaurantId, $key, $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $json = json_encode($clientPayload, JSON_UNESCAPED_UNICODE);
            try {
                $ins = $pdo->prepare(
                    "INSERT INTO restaurant_shared_state
                     (restaurant_id, state_key, business_date, payload, version, updated_by)
                     VALUES (?, ?, ?, ?, 1, ?)"
                );
                $ins->execute([$restaurantId, $key, $date, $json, $userId]);
            } catch (Exception $e) {
                // Race: another device inserted first — fall through to update path
                $stmt->execute([$restaurantId, $key, $date]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    throw $e;
                }
            }
            if (!$row) {
                echo json_encode([
                    'ok' => true,
                    'saved' => true,
                    'merged' => false,
                    'restaurantId' => $restaurantId,
                    'key' => $key,
                    'date' => $date,
                    'version' => 1,
                    'payload' => $clientPayload,
                ]);
                exit;
            }
        }

        $serverVersion = (int) $row['version'];
        $serverPayload = json_decode($row['payload'], true);
        if (!is_array($serverPayload)) {
            $serverPayload = [];
        }

        // Always merge so concurrent check-offs on different devices stick
        $merged = $force
            ? merge_shared_payloads($serverPayload, $clientPayload)
            : merge_shared_payloads($serverPayload, $clientPayload);
        $newVersion = $serverVersion + 1;
        $json = json_encode($merged, JSON_UNESCAPED_UNICODE);
        $upd = $pdo->prepare(
            "UPDATE restaurant_shared_state
             SET payload = ?, version = ?, updated_by = ?
             WHERE restaurant_id = ? AND state_key = ? AND business_date = ?"
        );
        $upd->execute([$json, $newVersion, $userId, $restaurantId, $key, $date]);

        echo json_encode([
            'ok' => true,
            'saved' => true,
            'merged' => true, // always return merged payload so clients converge
            'restaurantId' => $restaurantId,
            'key' => $key,
            'date' => $date,
            'version' => $newVersion,
            'payload' => $merged,
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server', 'message' => $e->getMessage()]);
}
