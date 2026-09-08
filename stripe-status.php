<?php
header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/stripe-config.php';
$s = stripe_load_secrets();
$key = trim((string)($s['secret_key'] ?? ''));
$ok = stripe_is_configured();
echo "Stripe setup status for ilovepbj.shop\n";
echo "====================================\n";
echo "This page is served from: " . __DIR__ . "\n";
echo "Key file present: " . (is_readable(__DIR__.'/stripe-key.txt') ? 'yes' : 'no') . "\n";
echo "Secrets php present: " . (is_readable(__DIR__.'/stripe-secrets.local.php') ? 'yes' : 'no') . "\n";
echo "Configured (real key loaded): " . ($ok ? 'YES — ready to test checkout' : 'NO — paste sk_test_ into stripe-key.txt') . "\n";
if ($key !== '') {
    echo "Key starts with: " . substr($key, 0, 12) . "... (length " . strlen($key) . ")\n";
} else {
    echo "Key starts with: (empty)\n";
}
echo "\nWinSCP should open Remote directory: /var/www/html\n";
echo "Look for files: AAA_OPEN_ME_FOR_STRIPE.txt and stripe-key.txt\n";
