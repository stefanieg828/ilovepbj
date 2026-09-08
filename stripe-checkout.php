<?php
/**
 * Start Stripe Checkout for the logged-in user's plan.
 * GET /billing/checkout?plan=crew_10
 * GET /billing/checkout?plan=crew_30&upgrade=1  (approved users upgrading)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe-config.php';
require_once __DIR__ . '/pbj-permissions.php';

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    header('Location: /login?next=' . urlencode('/billing/checkout'));
    exit;
}

$isUpgrade = !empty($_GET['upgrade']) || !empty($_GET['from_plans']);
$isTrialConvert = !empty($_GET['trial']) || !empty($_GET['convert']);

// Already paid — first-time pay not needed (unless upgrade / trial convert)
if (pbj_user_is_approved() && !$isUpgrade && !$isTrialConvert) {
    $uidGate = (int) ($_SESSION['user_id'] ?? 0);
    $alreadyPaid = false;
    if ($uidGate > 0 && function_exists('pbj_trial_info')) {
        $ti = pbj_trial_info($pdo, $uidGate);
        $alreadyPaid = !empty($ti['paid']);
    } else {
        // No trial helpers — keep old behaviour
        $alreadyPaid = true;
    }
    if ($alreadyPaid) {
        header('Location: /billing/plans');
        exit;
    }
    // Active or expired free trial → allow Checkout to convert
}

$planId = (string) ($_GET['plan'] ?? $_SESSION['pending_plan_id'] ?? pbj_default_plan_id());
$plan = pbj_plan_by_id($planId);
if (!$plan || !empty($plan['coming'])) {
    $planId = pbj_default_plan_id();
    $plan = pbj_plan_by_id($planId);
}

if (!stripe_is_configured()) {
    header('Location: ' . ($isUpgrade ? '/billing/plans?err=' . rawurlencode('Stripe not configured') : '/waiting?pay=not_configured'));
    exit;
}

$uid = (int) $_SESSION['user_id'];
$email = (string) ($_SESSION['email'] ?? '');
$rid = 0;
$customerId = '';

// Prefer restaurant they own + billing ids
try {
    $ctx = function_exists('pbj_billing_context') ? pbj_billing_context($pdo, $uid) : null;
    if ($ctx) {
        $rid = (int) ($ctx['restaurant_id'] ?? 0);
        $customerId = (string) (($ctx['billing']['stripe_customer_id'] ?? '') ?: '');
    }
    if ($rid <= 0) {
        $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$uid]);
        $rid = (int) ($stmt->fetchColumn() ?: 0);
    }
    if ($rid <= 0) {
        $stmt = $pdo->prepare('SELECT restaurant_id FROM user_restaurant WHERE user_id = ? ORDER BY joined_at ASC LIMIT 1');
        $stmt->execute([$uid]);
        $rid = (int) ($stmt->fetchColumn() ?: 0);
    }
} catch (Throwable $e) {
    $rid = 0;
}

$_SESSION['pending_plan_id'] = $planId;

$result = stripe_create_checkout_session([
    'plan_id' => $planId,
    'user_id' => $uid,
    'restaurant_id' => $rid,
    'email' => $email,
    'customer_id' => $customerId,
    'upgrade' => $isUpgrade,
]);

if (empty($result['ok']) || empty($result['url'])) {
    $err = (string) ($result['error'] ?? 'unknown');
    error_log('Stripe checkout failed: ' . $err);
    if ($isUpgrade) {
        header('Location: /billing/plans?err=' . rawurlencode($err));
    } else {
        header('Location: /waiting?pay=error&reason=' . rawurlencode($err));
    }
    exit;
}

// Send them to Stripe's hosted pay page
header('Location: ' . $result['url'], true, 303);
exit;
