#!/usr/bin/env php
<?php
/**
 * Nightly (or manual) reset of Sales Showcase playground to starter content.
 * Usage: php /var/www/html/cli/reset-sales-playground.php
 *        php /var/www/html/cli/reset-sales-playground.php --save-from=1
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/pbj-permissions.php';
require_once dirname(__DIR__) . '/sales-playground.inc.php';

$saveFrom = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--save-from=')) {
        $saveFrom = (int) substr($arg, strlen('--save-from='));
    }
    if ($arg === '--save-only' && $saveFrom) {
        // handled below
    }
}

if ($saveFrom) {
    $r = pbj_sales_save_starters_from_restaurant($pdo, $saveFrom);
    echo $r['ok'] ? "Saved {$r['keys']} starter keys from restaurant {$saveFrom}\n" : ("Save failed: " . ($r['error'] ?? '') . "\n");
    if (!$r['ok']) {
        exit(1);
    }
    if (in_array('--save-only', $argv, true)) {
        exit(0);
    }
}

// Ensure both playgrounds exist
$ids = pbj_ensure_all_playgrounds($pdo);
echo "Demo house id={$ids['demo']} code=" . PBJ_DEMO_INVITE_CODE . "\n";
echo "Sales house id={$ids['sales']} code=" . PBJ_SALES_INVITE_CODE . "\n";

// If no gold master yet, capture from DEMO playground
$sharedDir = pbj_sales_snapshot_dir() . '/shared';
$hasStarters = count(glob($sharedDir . '/*.json') ?: []) > 0;
if (!$hasStarters) {
    $source = (int) $ids['demo'];
    echo "No gold master — capturing from restaurant {$source}…\n";
    $r = pbj_sales_save_starters_from_restaurant($pdo, $source);
    echo $r['ok'] ? "Captured {$r['keys']} keys\n" : ("Capture failed: " . ($r['error'] ?? '') . "\n");
}

$results = pbj_sales_reset_all($pdo);
foreach ($results as $id => $res) {
    $label = $res['house'] ?? ('#' . $id);
    echo "Reset {$label}: " . (!empty($res['ok']) ? "ok keys={$res['keys']} epoch={$res['epoch']}" : ('FAIL ' . ($res['error'] ?? ''))) . "\n";
}
echo "Gold master modules: " . pbj_sales_gold_master_key_count() . "\n";

exit(0);
