<?php
/**
 * Stripe webhooks — marks users paid/approved.
 * Point Dashboard webhook to: https://ilovepbj.shop/billing/webhook
 * Events: checkout.session.completed, invoice.paid
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe-config.php';
require_once __DIR__ . '/pbj-permissions.php';

header('Content-Type: application/json; charset=utf-8');

$payload = file_get_contents('php://input');
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$secrets = stripe_load_secrets();
$whsec = trim((string) ($secrets['webhook_secret'] ?? ''));

// When a webhook signing secret is configured, require a valid Stripe signature.
// Until you paste whsec_... into the private secrets file, we accept events but log a warning
// (Checkout success page still marks users paid even without webhooks).
if ($whsec !== '') {
    if ($sig === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'missing_signature']);
        exit;
    }
    // Minimal verification: timestamp + v1 HMAC (Stripe scheme)
    $ok = false;
    $parts = [];
    foreach (explode(',', $sig) as $piece) {
        $kv = explode('=', trim($piece), 2);
        if (count($kv) === 2) {
            $parts[$kv[0]][] = $kv[1];
        }
    }
    $timestamp = $parts['t'][0] ?? '';
    $signed = $timestamp . '.' . $payload;
    $expected = hash_hmac('sha256', $signed, $whsec);
    foreach ($parts['v1'] ?? [] as $v1) {
        if (hash_equals($expected, $v1)) {
            $ok = true;
            break;
        }
    }
    // Reject very old timestamps (±5 min)
    if ($ok && abs(time() - (int) $timestamp) > 300) {
        $ok = false;
    }
    if (!$ok) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'bad_signature']);
        exit;
    }
} else {
    error_log('stripe webhook: webhook_secret empty — set whsec in /var/www/private/ilovepbj/stripe-secrets.local.php');
}

$event = json_decode((string) $payload, true);
if (!is_array($event) || empty($event['type'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_json']);
    exit;
}

$type = (string) $event['type'];
$obj = $event['data']['object'] ?? null;
if (!is_array($obj)) {
    echo json_encode(['ok' => true, 'ignored' => true]);
    exit;
}

try {
    if ($type === 'checkout.session.completed') {
        $applied = stripe_apply_paid_session($pdo, $obj);
        echo json_encode(['ok' => true, 'applied' => !empty($applied['ok'])]);
        exit;
    }
    if ($type === 'customer.subscription.updated' || $type === 'customer.subscription.created') {
        $meta = is_array($obj['metadata'] ?? null) ? $obj['metadata'] : [];
        $uid = (int) ($meta['user_id'] ?? 0);
        $rid = (int) ($meta['restaurant_id'] ?? 0);
        $planId = (string) ($meta['plan_id'] ?? '');
        if ($uid > 0) {
            stripe_mark_user_approved($pdo, $uid);
        }
        if ($planId !== '' && function_exists('stripe_apply_plan_change')) {
            stripe_apply_plan_change($pdo, $uid, $rid, $planId, [
                'stripe_subscription_id' => $obj['id'] ?? null,
                'stripe_customer_id' => is_string($obj['customer'] ?? null) ? $obj['customer'] : null,
                'status' => (($obj['status'] ?? '') === 'active' || ($obj['status'] ?? '') === 'trialing') ? 'paid' : ($obj['status'] ?? 'paid'),
            ]);
        }
        echo json_encode(['ok' => true, 'subscription' => true]);
        exit;
    }
    if ($type === 'invoice.paid') {
        // Subscription renewals — keep access approved + monthly sales commission
        $meta = is_array($obj['subscription_details']['metadata'] ?? null)
            ? $obj['subscription_details']['metadata']
            : (is_array($obj['lines']['data'][0]['metadata'] ?? null) ? $obj['lines']['data'][0]['metadata'] : []);
        $uid = (int) ($meta['user_id'] ?? 0);
        $rid = (int) ($meta['restaurant_id'] ?? 0);
        $planId = (string) ($meta['plan_id'] ?? '');
        if ($uid > 0) {
            stripe_mark_user_approved($pdo, $uid);
        }
        if (function_exists('pbj_sales_mark_converted') && $uid > 0) {
            $invoiceId = is_string($obj['id'] ?? null) ? (string) $obj['id'] : null;
            // Period from invoice if present
            $periodStart = (int) ($obj['lines']['data'][0]['period']['start'] ?? 0);
            if ($periodStart > 0) {
                $ym = date('Y-m', $periodStart);
                // Load attribution and write month specifically
                try {
                    $attr = null;
                    if ($rid > 0) {
                        $st = $pdo->prepare('SELECT * FROM sales_attributions WHERE restaurant_id = ? LIMIT 1');
                        $st->execute([$rid]);
                        $attr = $st->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                    if (!$attr && $uid > 0) {
                        $st = $pdo->prepare('SELECT * FROM sales_attributions WHERE user_id = ? ORDER BY id ASC LIMIT 1');
                        $st->execute([$uid]);
                        $attr = $st->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                    if ($attr && function_exists('pbj_sales_record_subscription_commission')) {
                        $base = (int) ($attr['monthly_plan_cents'] ?? 0);
                        $p = $planId !== '' ? $planId : (string) ($attr['plan_id'] ?? '');
                        if ($base <= 0) {
                            $base = function_exists('pbj_plan_price_cents') ? pbj_plan_price_cents($p) : 0;
                        }
                        // Prefer Stripe line amount when present
                        $stripeAmt = (int) ($obj['amount_paid'] ?? $obj['lines']['data'][0]['amount'] ?? 0);
                        if ($stripeAmt > 0) {
                            $base = $stripeAmt;
                        }
                        pbj_sales_record_subscription_commission(
                            $pdo,
                            (int) $attr['sales_rep_id'],
                            (int) $attr['id'],
                            (int) ($attr['restaurant_id'] ?? $rid),
                            $uid,
                            $p,
                            $base,
                            $invoiceId,
                            $ym
                        );
                        $pdo->prepare(
                            "UPDATE sales_attributions SET status = 'paid', converted_at = COALESCE(converted_at, NOW()) WHERE id = ?"
                        )->execute([(int) $attr['id']]);
                    } else {
                        pbj_sales_mark_converted($pdo, $uid, $rid, $planId, $invoiceId);
                    }
                } catch (Throwable $e) {
                    error_log('invoice.paid commission: ' . $e->getMessage());
                    pbj_sales_mark_converted($pdo, $uid, $rid, $planId, $invoiceId);
                }
            } else {
                pbj_sales_mark_converted($pdo, $uid, $rid, $planId, $invoiceId);
            }
        }
        echo json_encode(['ok' => true, 'renewal' => true]);
        exit;
    }
    echo json_encode(['ok' => true, 'ignored' => $type]);
} catch (Throwable $e) {
    error_log('stripe webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server']);
}
