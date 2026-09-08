#!/usr/bin/env php
<?php
/**
 * Remove playground guest seats for users inactive 60+ days.
 *
 * Usage:
 *   php /var/www/html/cli/purge-inactive-playground-members.php
 *   php /var/www/html/cli/purge-inactive-playground-members.php --dry-run
 *   php /var/www/html/cli/purge-inactive-playground-members.php --days=90
 *
 * Does not delete accounts — only removes user_restaurant rows on playground
 * kitchens (DEMO-PBJ, SALES-PBJ, and flagged playground houses).
 * Never removes the house owner or platform admins.
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/sales-playground.inc.php';

$dryRun = in_array('--dry-run', $argv, true);
$days = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--days=')) {
        $days = (int) substr($arg, strlen('--days='));
    }
}

$opts = ['dry_run' => $dryRun];
if ($days !== null && $days > 0) {
    $opts['days'] = $days;
}

$r = pbj_purge_inactive_playground_members($pdo, $opts);

$mode = !empty($r['dry_run']) ? 'DRY-RUN' : 'LIVE';
echo "[{$mode}] playground inactive purge\n";
echo "  days={$r['days']} playgrounds={$r['playgrounds']} scanned={$r['scanned']}\n";
echo "  removed={$r['removed']} kept_owners/admins={$r['kept_owners']}\n";

if (!empty($r['details'])) {
    foreach ($r['details'] as $d) {
        $flag = !empty($r['dry_run']) ? 'would-remove' : (!empty($d['removed']) ? 'removed' : 'skip');
        echo sprintf(
            "  [%s] house=%s (%s) user=#%d %s <%s> last=%s\n",
            $flag,
            $d['house'] ?? '',
            $d['invite_code'] ?? '',
            (int) ($d['user_id'] ?? 0),
            $d['username'] ?? '',
            $d['email'] ?? '',
            $d['last_activity'] ?? ''
        );
    }
}

if (empty($r['ok'])) {
    fwrite(STDERR, 'Error: ' . ($r['error'] ?? 'unknown') . "\n");
    exit(1);
}

exit(0);
