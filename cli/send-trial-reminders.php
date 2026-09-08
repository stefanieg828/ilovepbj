#!/usr/bin/env php
<?php
/**
 * Email free-trial reminders:
 *  - ~3 days before trial ends (“ending soon”)
 *  - on the day it ends (subscribe + reach out)
 *
 * Usage:
 *   php /var/www/html/cli/send-trial-reminders.php
 *   php /var/www/html/cli/send-trial-reminders.php --dry-run
 *   php /var/www/html/cli/send-trial-reminders.php --user=123
 *
 * Cron: daily morning (see /etc/cron.d/ilovepbj-trial-reminders)
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

require_once dirname(__DIR__) . '/config.php';

$dryRun = in_array('--dry-run', $argv, true);
$forceUid = 0;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--user=')) {
        $forceUid = (int) substr($arg, strlen('--user='));
    }
}

$opts = ['dry_run' => $dryRun];
if ($forceUid > 0) {
    $opts['force_user_id'] = $forceUid;
}

if (!function_exists('pbj_process_trial_reminders')) {
    fwrite(STDERR, "pbj_process_trial_reminders missing — update config.php\n");
    exit(1);
}

if (!$dryRun && function_exists('pbj_mail_is_configured') && !pbj_mail_is_configured()) {
    fwrite(STDERR, "Mail not configured (SMTP secrets) — cannot send. Use --dry-run to preview.\n");
    exit(1);
}

$r = pbj_process_trial_reminders($pdo, $opts);

$mode = !empty($r['dry_run']) || $dryRun ? 'DRY-RUN' : 'LIVE';
$soonWindow = function_exists('pbj_trial_reminder_days_before') ? pbj_trial_reminder_days_before() : 3;

echo "[{$mode}] trial reminders\n";
echo "  soon_window={$soonWindow}d scanned={$r['scanned']} soon={$r['soon']} end={$r['end']} skipped={$r['skipped']} errors={$r['errors']}\n";

if (!empty($r['details'])) {
    foreach ($r['details'] as $d) {
        $flag = !empty($d['ok']) ? (!empty($d['dry_run']) || $dryRun ? 'would-send' : 'sent') : 'fail';
        echo sprintf(
            "  [%s] kind=%s user=#%d <%s> ends=%s%s\n",
            $flag,
            $d['kind'] ?? '?',
            (int) ($d['user_id'] ?? 0),
            $d['email'] ?? '',
            $d['ends'] ?? '',
            isset($d['days_left']) ? (' days_left=' . $d['days_left']) : ''
        );
    }
}

if (empty($r['ok'])) {
    fwrite(STDERR, 'Error: ' . ($r['error'] ?? 'unknown') . "\n");
    exit(1);
}

exit(!empty($r['errors']) ? 2 : 0);
