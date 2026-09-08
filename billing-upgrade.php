<?php
/**
 * Apply a plan upgrade for the logged-in owner.
 * POST /billing/upgrade  plan=crew_30  restaurant_name?=...
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe-config.php';

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    header('Location: /login?next=' . urlencode('/billing/plans'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /billing/plans');
    exit();
}

$uid = (int) $_SESSION['user_id'];
$email = (string) ($_SESSION['email'] ?? '');
$targetPlanId = (string) ($_POST['plan'] ?? '');
$restaurantName = trim((string) ($_POST['restaurant_name'] ?? ''));

$target = pbj_plan_by_id($targetPlanId);
if (!$target || !empty($target['coming']) || !stripe_price_id_for_plan($target['id'])) {
    header('Location: /billing/plans?err=' . rawurlencode('That plan isn’t available for self-serve upgrade.'));
    exit();
}
$targetPlanId = $target['id'];

$ctx = pbj_billing_context($pdo, $uid);
if (empty($ctx['can_manage'])) {
    header('Location: /billing/plans?err=' . rawurlencode('Only the house owner can change the plan.'));
    exit();
}

$currentId = (string) $ctx['plan_id'];
if (!pbj_plan_is_upgrade($currentId, $targetPlanId)) {
    header('Location: /billing/plans?err=' . rawurlencode('Pick a higher plan than your current one.'));
    exit();
}

if (!stripe_is_configured()) {
    header('Location: /billing/plans?err=' . rawurlencode('Billing isn’t configured on the server yet.'));
    exit();
}

$rid = (int) $ctx['restaurant_id'];

// Individual → house plan: create the restaurant first
$needsHouse = ($rid <= 0) && empty($target['limited']);
if ($needsHouse) {
    if ($restaurantName === '') {
        header('Location: /billing/plans?err=' . rawurlencode('Please name your restaurant to start a house plan.') . '&name=');
        exit();
    }
    try {
        $created = pbj_create_restaurant($pdo, $uid, $restaurantName, $targetPlanId);
        $rid = (int) ($created['id'] ?? 0);
        $_SESSION['pending_invite_code'] = $created['invite_code'] ?? '';
        // Owner role in global profile
        try {
            $pdo->prepare("UPDATE users SET role = 'owner' WHERE id = ?")->execute([$uid]);
            $_SESSION['role'] = 'owner';
        } catch (Throwable $e) {
            // ignore
        }
    } catch (Throwable $e) {
        error_log('billing upgrade create restaurant: ' . $e->getMessage());
        header('Location: /billing/plans?err=' . rawurlencode('Could not create your house. Try again.'));
        exit();
    }
} elseif ($rid > 0) {
    // Mark intended plan on the house (billing status preserved if already paid)
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
        $stmt->execute([$rid]);
        $raw = $stmt->fetchColumn();
        $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
        $settings['plan'] = $targetPlanId;
        $settings['plan_selected_at'] = date('c');
        $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
        if ($raw) {
            $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                ->execute([$json, $rid]);
        } else {
            $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                ->execute([$rid, $json]);
        }
    } catch (Throwable $e) {
        // non-fatal
    }
}

$_SESSION['pending_plan_id'] = $targetPlanId;

$billing = is_array($ctx['billing'] ?? null) ? $ctx['billing'] : [];
$subId = trim((string) ($billing['stripe_subscription_id'] ?? ''));
$customerId = trim((string) ($billing['stripe_customer_id'] ?? ''));

// Prefer in-place subscription change when we already have a Stripe sub
if ($subId !== '' && strpos($subId, 'sub_') === 0) {
    $changed = stripe_change_subscription_plan($subId, $targetPlanId, [
        'user_id' => $uid,
        'restaurant_id' => $rid,
    ]);
    if (!empty($changed['ok'])) {
        $sub = is_array($changed['subscription'] ?? null) ? $changed['subscription'] : [];
        stripe_apply_plan_change($pdo, $uid, $rid, $targetPlanId, [
            'stripe_subscription_id' => $sub['id'] ?? $subId,
            'stripe_customer_id' => is_string($sub['customer'] ?? null) ? $sub['customer'] : ($customerId ?: null),
            'status' => 'paid',
        ]);
        stripe_mark_user_approved($pdo, $uid);
        header('Location: /billing/plans?ok=upgraded');
        exit();
    }
    // If update failed (canceled sub, etc.), fall through to Checkout
    error_log('stripe sub upgrade failed: ' . ($changed['error'] ?? 'unknown') . ' — falling back to Checkout');
}

// New Checkout session (first sub, or sub update failed)
$result = stripe_create_checkout_session([
    'plan_id' => $targetPlanId,
    'user_id' => $uid,
    'restaurant_id' => $rid,
    'email' => $email,
    'customer_id' => $customerId,
    'upgrade' => true,
    'cancel_url' => stripe_public_base_url() . '/billing/plans?pay=cancel',
]);

if (empty($result['ok']) || empty($result['url'])) {
    $err = (string) ($result['error'] ?? 'checkout_failed');
    error_log('Stripe upgrade checkout failed: ' . $err);
    header('Location: /billing/plans?err=' . rawurlencode('Could not start Checkout (' . $err . '). Try again.'));
    exit();
}

header('Location: ' . $result['url'], true, 303);
exit();
