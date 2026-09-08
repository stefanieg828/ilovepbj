<?php
/**
 * Stripe billing config for ilovepbj ops (SaaS subscriptions).
 * Secrets load from stripe-secrets.local.php (not in git) or environment.
 */
require_once __DIR__ . '/config.php';

/**
 * Paths where Stripe secrets may live (private first — never expose via the web root).
 * @return list<string>
 */
function stripe_secret_search_dirs(): array {
    $dirs = [];
    // Preferred: outside public HTML
    $dirs[] = '/var/www/private/ilovepbj';
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $parent = dirname((string) $_SERVER['DOCUMENT_ROOT']) . '/private/ilovepbj';
        if (!in_array($parent, $dirs, true)) {
            $dirs[] = $parent;
        }
    }
    // Legacy fallback (stub only after lockdown — do not put live keys here)
    $dirs[] = __DIR__;
    return $dirs;
}

/**
 * Read a one-line or annotated key file; return first sk_test/sk_live token or ''.
 */
function stripe_read_key_file(string $path): string {
    if (!is_readable($path)) {
        return '';
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return '';
    }
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '' || $line[0] === '#' || $line[0] === '/') {
            continue;
        }
        if (preg_match('/(sk_(test|live)_[A-Za-z0-9]+)/', $line, $m)) {
            return $m[1];
        }
    }
    return '';
}

/** @return array{secret_key:string,webhook_secret:string,publishable_key:string} */
function stripe_load_secrets(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $defaults = [
        'secret_key' => '',
        'webhook_secret' => '',
        'publishable_key' => '',
    ];

    foreach (stripe_secret_search_dirs() as $dir) {
        $local = rtrim($dir, '/') . '/stripe-secrets.local.php';
        if (is_readable($local)) {
            $loaded = include $local;
            if (is_array($loaded)) {
                foreach (['secret_key', 'webhook_secret', 'publishable_key'] as $k) {
                    if (!empty($loaded[$k]) && is_string($loaded[$k])) {
                        $val = trim($loaded[$k]);
                        // Skip empty / placeholder stubs so a private file can win after a public stub
                        if ($val === '' || stripos($val, 'PASTE') !== false || stripos($val, 'YOUR_') !== false) {
                            continue;
                        }
                        $defaults[$k] = $val;
                    }
                }
            }
        }
        $keyFile = rtrim($dir, '/') . '/stripe-key.txt';
        $fromFile = stripe_read_key_file($keyFile);
        if ($fromFile !== '' && $defaults['secret_key'] === '') {
            $defaults['secret_key'] = $fromFile;
        } elseif ($fromFile !== '' && strpos($defaults['secret_key'], 'sk_') !== 0) {
            $defaults['secret_key'] = $fromFile;
        }
    }

    // Environment overrides (optional, highest priority)
    if (getenv('STRIPE_SECRET_KEY')) {
        $defaults['secret_key'] = (string) getenv('STRIPE_SECRET_KEY');
    }
    if (getenv('STRIPE_WEBHOOK_SECRET')) {
        $defaults['webhook_secret'] = (string) getenv('STRIPE_WEBHOOK_SECRET');
    }
    if (getenv('STRIPE_PUBLISHABLE_KEY')) {
        $defaults['publishable_key'] = (string) getenv('STRIPE_PUBLISHABLE_KEY');
    }
    $cache = $defaults;
    return $cache;
}

function stripe_is_configured(): bool {
    $s = stripe_load_secrets();
    $key = trim((string) ($s['secret_key'] ?? ''));
    if ($key === '' || strpos($key, 'sk_') !== 0) {
        return false;
    }
    // Reject placeholders so Checkout is not attempted until a real key is saved
    $upper = strtoupper($key);
    if (strpos($upper, 'REPLACE') !== false
        || strpos($upper, 'PASTE') !== false
        || strpos($upper, 'YOUR_SECRET') !== false
        || strpos($upper, 'XXXX') !== false) {
        return false;
    }
    // Real Stripe keys are long
    return strlen($key) > 20;
}

/**
 * Map app plan ids → Stripe Price IDs (recurring monthly).
 * Confirm amounts in Dashboard if a pay page shows the wrong total.
 */
function stripe_price_map(): array {
    return [
        'individual' => 'price_1TsSH7PW71pV7pWcbJFbItJw', // $5
        'crew_10' => 'price_1TsSHZPW71pV7pWcD9ZyLNQ4',    // $29
        'crew_30' => 'price_1TsSJWPW71pV7pWceI3IBPx0',    // $59
        'crew_75' => 'price_1TsSJrPW71pV7pWcvExVkIDt',    // $99
        'crew_150' => 'price_1TuzQSPW71pV7pWcXviixwLO',   // $149 Large House
        'crew_300' => 'price_1TuzQTPW71pV7pWcBHFxVV9g',   // $199 Jumbo House
    ];
}

function stripe_price_id_for_plan(string $planId): ?string {
    $map = stripe_price_map();
    $plan = pbj_plan_by_id($planId);
    $id = $plan ? $plan['id'] : $planId;
    return $map[$id] ?? null;
}

function stripe_public_base_url(): string {
    if (defined('APP_PUBLIC_URL') && APP_PUBLIC_URL) {
        return rtrim((string) APP_PUBLIC_URL, '/');
    }
    return 'https://ilovepbj.shop';
}

/**
 * Low-level Stripe API call (form-encoded).
 * @return array{ok:bool,status:int,data:?array,error:?string,raw:string}
 */
function stripe_api(string $method, string $path, array $params = []): array {
    $secrets = stripe_load_secrets();
    $secret = trim((string) ($secrets['secret_key'] ?? ''));
    if ($secret === '') {
        return ['ok' => false, 'status' => 0, 'data' => null, 'error' => 'missing_secret', 'raw' => ''];
    }
    $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
    $ch = curl_init();
    $headers = ['Authorization: Bearer ' . $secret];
    if (strtoupper($method) === 'GET') {
        if ($params) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        return ['ok' => false, 'status' => $status, 'data' => null, 'error' => $cerr ?: 'curl_failed', 'raw' => ''];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'status' => $status, 'data' => null, 'error' => 'bad_json', 'raw' => $raw];
    }
    if ($status < 200 || $status >= 300) {
        $msg = $data['error']['message'] ?? ('http_' . $status);
        return ['ok' => false, 'status' => $status, 'data' => $data, 'error' => $msg, 'raw' => $raw];
    }
    return ['ok' => true, 'status' => $status, 'data' => $data, 'error' => null, 'raw' => $raw];
}

/**
 * Create Checkout Session for a single plan. Returns hosted url.
 * @return array{ok:bool,url?:string,id?:string,error?:string}
 */
function stripe_create_checkout_session(array $opts): array {
    $planId = (string) ($opts['plan_id'] ?? '');
    $priceId = stripe_price_id_for_plan($planId);
    if (!$priceId) {
        return ['ok' => false, 'error' => 'unknown_plan'];
    }
    if (!stripe_is_configured()) {
        return ['ok' => false, 'error' => 'not_configured'];
    }
    $base = stripe_public_base_url();
    $uid = (int) ($opts['user_id'] ?? 0);
    $rid = (int) ($opts['restaurant_id'] ?? 0);
    $email = (string) ($opts['email'] ?? '');
    $isUpgrade = !empty($opts['upgrade']);
    $customerId = trim((string) ($opts['customer_id'] ?? ''));
    $successQs = $isUpgrade ? '&upgrade=1' : '';
    $cancelUrl = (string) ($opts['cancel_url'] ?? ($isUpgrade ? ($base . '/billing/plans?pay=cancel') : ($base . '/waiting?pay=cancel')));

    // Stripe form encoding for nested arrays
    $flat = [
        'mode' => 'subscription',
        'success_url' => $base . '/billing/success?session_id={CHECKOUT_SESSION_ID}' . $successQs,
        'cancel_url' => $cancelUrl,
        'client_reference_id' => (string) $uid,
        'line_items[0][price]' => $priceId,
        'line_items[0][quantity]' => 1,
        'metadata[plan_id]' => $planId,
        'metadata[user_id]' => (string) $uid,
        'metadata[restaurant_id]' => (string) $rid,
        'metadata[upgrade]' => $isUpgrade ? '1' : '0',
        'subscription_data[metadata][plan_id]' => $planId,
        'subscription_data[metadata][user_id]' => (string) $uid,
        'subscription_data[metadata][restaurant_id]' => (string) $rid,
        'allow_promotion_codes' => 'true',
    ];
    if ($customerId !== '' && strpos($customerId, 'cus_') === 0) {
        $flat['customer'] = $customerId;
    } elseif ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flat['customer_email'] = $email;
    }

    $res = stripe_api('POST', 'checkout/sessions', $flat);
    if (!$res['ok'] || empty($res['data']['url'])) {
        return ['ok' => false, 'error' => $res['error'] ?? 'checkout_failed'];
    }
    return [
        'ok' => true,
        'url' => $res['data']['url'],
        'id' => $res['data']['id'] ?? '',
    ];
}

/**
 * Change an existing subscription to a new plan price (with proration).
 * @return array{ok:bool,error?:string,subscription?:array}
 */
function stripe_change_subscription_plan(string $subscriptionId, string $planId, array $meta = []): array {
    $subscriptionId = trim($subscriptionId);
    $priceId = stripe_price_id_for_plan($planId);
    if ($subscriptionId === '' || strpos($subscriptionId, 'sub_') !== 0) {
        return ['ok' => false, 'error' => 'bad_subscription'];
    }
    if (!$priceId) {
        return ['ok' => false, 'error' => 'unknown_plan'];
    }
    if (!stripe_is_configured()) {
        return ['ok' => false, 'error' => 'not_configured'];
    }

    $got = stripe_api('GET', 'subscriptions/' . rawurlencode($subscriptionId), []);
    if (empty($got['ok']) || !is_array($got['data'])) {
        return ['ok' => false, 'error' => $got['error'] ?? 'sub_fetch_failed'];
    }
    $sub = $got['data'];
    $itemId = (string) ($sub['items']['data'][0]['id'] ?? '');
    if ($itemId === '') {
        return ['ok' => false, 'error' => 'no_subscription_item'];
    }

    $params = [
        'items[0][id]' => $itemId,
        'items[0][price]' => $priceId,
        'proration_behavior' => 'create_prorations',
        'metadata[plan_id]' => $planId,
    ];
    if (!empty($meta['user_id'])) {
        $params['metadata[user_id]'] = (string) $meta['user_id'];
    }
    if (!empty($meta['restaurant_id'])) {
        $params['metadata[restaurant_id]'] = (string) $meta['restaurant_id'];
    }

    $res = stripe_api('POST', 'subscriptions/' . rawurlencode($subscriptionId), $params);
    if (empty($res['ok'])) {
        return ['ok' => false, 'error' => $res['error'] ?? 'sub_update_failed'];
    }
    return ['ok' => true, 'subscription' => $res['data']];
}

/**
 * Apply a successful plan change locally (restaurant + user billing).
 */
function stripe_apply_plan_change(PDO $pdo, int $userId, int $restaurantId, string $planId, array $extra = []): void {
    $plan = pbj_plan_by_id($planId);
    $planId = $plan ? $plan['id'] : $planId;
    $patch = array_merge([
        'status' => 'paid',
        'plan_id' => $planId,
        'upgraded_at' => date('c'),
    ], $extra);

    if ($restaurantId > 0) {
        // Keep plan field in sync
        if (function_exists('pbj_save_restaurant_plan')) {
            // Don't reset billing_status to unpaid — update billing after
            $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
            $stmt->execute([$restaurantId]);
            $raw = $stmt->fetchColumn();
            $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
            $settings['plan'] = $planId;
            $settings['plan_selected_at'] = date('c');
            $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
            if ($raw) {
                $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                    ->execute([$json, $restaurantId]);
            } else {
                $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                    ->execute([$restaurantId, $json]);
            }
        }
        stripe_update_restaurant_billing($pdo, $restaurantId, $patch);
    }
    if ($userId > 0 && function_exists('pbj_save_user_billing')) {
        pbj_save_user_billing($pdo, $userId, [
            'plan_id' => $planId,
            'status' => $patch['status'] ?? 'paid',
            'stripe_customer_id' => $extra['stripe_customer_id'] ?? null,
            'stripe_subscription_id' => $extra['stripe_subscription_id'] ?? null,
            'updated_at' => date('c'),
        ]);
    }
}

function stripe_retrieve_session(string $sessionId): array {
    $sessionId = trim($sessionId);
    if ($sessionId === '' || strpos($sessionId, 'cs_') !== 0) {
        return ['ok' => false, 'error' => 'bad_session'];
    }
    return stripe_api('GET', 'checkout/sessions/' . rawurlencode($sessionId), [
        'expand[]' => 'subscription',
    ]);
}

function stripe_mark_user_approved(PDO $pdo, int $userId): void {
    if ($userId <= 0) {
        return;
    }
    // Paid starters are Owners with full access.
    $stmt = $pdo->prepare("UPDATE users SET access_status = 'approved', role = 'owner' WHERE id = ?");
    $stmt->execute([$userId]);
    if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === $userId) {
        $_SESSION['access_status'] = 'approved';
        $_SESSION['role'] = 'owner';
    }
}

function stripe_update_restaurant_billing(PDO $pdo, int $restaurantId, array $patch): void {
    if ($restaurantId <= 0) {
        return;
    }
    // reuse restaurant_settings helpers via permissions table functions if present
    if (function_exists('pbj_permissions_load_settings') && function_exists('pbj_permissions_save_settings')) {
        $settings = pbj_permissions_load_settings($pdo, $restaurantId);
        $billing = is_array($settings['billing'] ?? null) ? $settings['billing'] : [];
        $settings['billing'] = array_merge($billing, $patch);
        $settings['billing_status'] = $patch['status'] ?? ($settings['billing_status'] ?? 'paid');
        if (!empty($patch['plan_id'])) {
            $settings['plan'] = $patch['plan_id'];
        }
        pbj_permissions_save_settings($pdo, $restaurantId, $settings);
        return;
    }
    // fallback inline
    $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
    $stmt->execute([$restaurantId]);
    $raw = $stmt->fetchColumn();
    $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
    $billing = is_array($settings['billing'] ?? null) ? $settings['billing'] : [];
    $settings['billing'] = array_merge($billing, $patch);
    $settings['billing_status'] = $patch['status'] ?? 'paid';
    if (!empty($patch['plan_id'])) {
        $settings['plan'] = $patch['plan_id'];
    }
    $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
    if ($raw) {
        $pdo->prepare('UPDATE restaurant_settings SET settings_json = ? WHERE restaurant_id = ?')->execute([$json, $restaurantId]);
    } else {
        $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json) VALUES (?, ?)')->execute([$restaurantId, $json]);
    }
}

/**
 * Apply a paid Checkout Session to local DB.
 * @return array{ok:bool,error?:string,user_id?:int}
 */
function stripe_apply_paid_session(PDO $pdo, array $session): array {
    $paymentStatus = (string) ($session['payment_status'] ?? '');
    $status = (string) ($session['status'] ?? '');
    if ($paymentStatus !== 'paid' && $status !== 'complete') {
        // subscription mode often paid immediately with card
        if ($paymentStatus !== 'no_payment_required') {
            return ['ok' => false, 'error' => 'not_paid'];
        }
    }
    $meta = is_array($session['metadata'] ?? null) ? $session['metadata'] : [];
    $uid = (int) ($meta['user_id'] ?? $session['client_reference_id'] ?? 0);
    $rid = (int) ($meta['restaurant_id'] ?? 0);
    $planId = (string) ($meta['plan_id'] ?? '');
    if ($uid <= 0) {
        return ['ok' => false, 'error' => 'no_user'];
    }
    stripe_mark_user_approved($pdo, $uid);
    $customerId = is_string($session['customer'] ?? null) ? $session['customer'] : null;
    $subId = is_string($session['subscription'] ?? null)
        ? $session['subscription']
        : (is_array($session['subscription'] ?? null) ? ($session['subscription']['id'] ?? null) : null);

    $billingPatch = [
        'status' => 'paid',
        'plan_id' => $planId ?: null,
        'stripe_customer_id' => $customerId,
        'stripe_subscription_id' => $subId,
        'stripe_session_id' => $session['id'] ?? null,
        'paid_at' => date('c'),
    ];

    if ($rid > 0) {
        stripe_update_restaurant_billing($pdo, $rid, $billingPatch);
        if ($planId !== '') {
            // Keep top-level plan key accurate
            try {
                $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
                $stmt->execute([$rid]);
                $raw = $stmt->fetchColumn();
                $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
                $settings['plan'] = $planId;
                $settings['billing_status'] = 'paid';
                $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
                if ($raw) {
                    $pdo->prepare('UPDATE restaurant_settings SET settings_json = ? WHERE restaurant_id = ?')->execute([$json, $rid]);
                }
            } catch (Throwable $e) {
                // ignore
            }
        }
        // Ensure membership is owner for the paying house account
        try {
            $chk = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ? LIMIT 1');
            $chk->execute([$uid, $rid]);
            $memRole = $chk->fetchColumn();
            if ($memRole === false) {
                $pdo->prepare('INSERT INTO user_restaurant (user_id, restaurant_id, role) VALUES (?, ?, ?)')
                    ->execute([$uid, $rid, 'owner']);
            } elseif ((string) $memRole !== 'owner') {
                $pdo->prepare("UPDATE user_restaurant SET role = 'owner' WHERE user_id = ? AND restaurant_id = ?")
                    ->execute([$uid, $rid]);
            }
            // House owner_id should be the payer if unset or matching membership
            $own = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1');
            $own->execute([$rid]);
            $ownerId = (int) $own->fetchColumn();
            if ($ownerId <= 0) {
                $pdo->prepare('UPDATE restaurants SET owner_id = ? WHERE id = ?')->execute([$uid, $rid]);
            }
        } catch (Throwable $e) {
            error_log('stripe owner membership: ' . $e->getMessage());
        }
    }

    // Always keep user-level billing (solo Individual + upgrade source of truth)
    if (function_exists('pbj_save_user_billing')) {
        pbj_save_user_billing($pdo, $uid, array_filter([
            'plan_id' => $planId ?: null,
            'status' => 'paid',
            'trial' => false,
            'stripe_customer_id' => $customerId,
            'stripe_subscription_id' => $subId,
            'stripe_session_id' => $session['id'] ?? null,
            'paid_at' => date('c'),
            'converted_from_trial_at' => date('c'),
            'restaurant_id' => $rid > 0 ? $rid : null,
        ], static fn($v) => $v !== null && $v !== ''));
    }

    // Paid convert: drop demo/sales playground guest seats so they live on their house only
    if (function_exists('pbj_detach_user_from_playgrounds')) {
        pbj_detach_user_from_playgrounds($pdo, $uid);
    }

    // Sales commission: mark attribution paid + record this month's 30% subscription commission
    if (function_exists('pbj_sales_mark_converted')) {
        $sessionId = is_string($session['id'] ?? null) ? (string) $session['id'] : null;
        pbj_sales_mark_converted($pdo, $uid, $rid, $planId, $sessionId);
    }

    return ['ok' => true, 'user_id' => $uid];
}
