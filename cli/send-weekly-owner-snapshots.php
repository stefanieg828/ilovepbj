#!/usr/bin/env php
<?php
/**
 * Email weekly owner snapshots (opt-in houses).
 *
 * Usage:
 *   php /var/www/html/cli/send-weekly-owner-snapshots.php
 *   php /var/www/html/cli/send-weekly-owner-snapshots.php --dry-run
 *   php /var/www/html/cli/send-weekly-owner-snapshots.php --restaurant=12
 *
 * Cron: Monday morning (see /etc/cron.d/ilovepbj-weekly-snapshot)
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/weekly-owner-snapshot.inc.php';

$dryRun = in_array('--dry-run', $argv, true);
$forceRid = 0;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--restaurant=')) {
        $forceRid = (int) substr($arg, strlen('--restaurant='));
    }
}

$opts = ['dry_run' => $dryRun];
if ($forceRid > 0) {
    $opts['force_restaurant_id'] = $forceRid;
}

if (!$dryRun && function_exists('pbj_mail_is_configured') && !pbj_mail_is_configured()) {
    fwrite(STDERR, "Mail not configured (SMTP secrets) — cannot send. Use --dry-run to preview.\n");
    exit(1);
}

$r = pbj_process_weekly_owner_snapshots($pdo, $opts);
$mode = !empty($r['dry_run']) || $dryRun ? 'DRY-RUN' : 'LIVE';

echo "[{$mode}] weekly owner snapshots\n";
echo '  scanned=' . (int) ($r['scanned'] ?? 0)
    . ' sent=' . (int) ($r['sent'] ?? 0)
    . ' skipped=' . (int) ($r['skipped'] ?? 0)
    . ' errors=' . (int) ($r['errors'] ?? 0) . "\n";

if (!empty($r['details'])) {
    foreach ($r['details'] as $d) {
        $flag = !empty($d['ok']) ? (!empty($d['dry_run']) || $dryRun ? 'would-send' : 'sent') : 'fail';
        echo sprintf(
            "  [%s] house=#%d %s <%s>%s\n",
            $flag,
            (int) ($d['restaurant_id'] ?? 0),
            $d['house'] ?? '',
            $d['email'] ?? '',
            isset($d['error']) ? (' err=' . $d['error']) : ''
        );
    }
}

if (empty($r['ok'])) {
    fwrite(STDERR, 'Error: ' . ($r['error'] ?? 'unknown') . "\n");
    exit(1);
}

exit(!empty($r['errors']) ? 2 : 0);
