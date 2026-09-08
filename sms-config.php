<?php
/**
 * SMS (Twilio) helpers for playground invite texts.
 * Secrets: /var/www/private/ilovepbj/sms-secrets.local.php
 */
require_once __DIR__ . '/config.php';

/**
 * @return array{account_sid:string,auth_token:string,from_number:string}
 */
function sms_load_secrets(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $defaults = [
        'account_sid' => '',
        'auth_token' => '',
        'from_number' => '',
    ];
    $paths = [
        '/var/www/private/ilovepbj/sms-secrets.local.php',
        __DIR__ . '/sms-secrets.local.php',
    ];
    foreach ($paths as $path) {
        if (!is_readable($path)) {
            continue;
        }
        $loaded = include $path;
        if (!is_array($loaded)) {
            continue;
        }
        foreach (['account_sid', 'auth_token', 'from_number'] as $k) {
            if (!empty($loaded[$k]) && is_string($loaded[$k])) {
                $val = trim($loaded[$k]);
                if ($val === '' || stripos($val, 'PASTE') !== false || stripos($val, 'YOUR_') !== false) {
                    continue;
                }
                $defaults[$k] = $val;
            }
        }
    }
    if (getenv('TWILIO_ACCOUNT_SID')) {
        $defaults['account_sid'] = (string) getenv('TWILIO_ACCOUNT_SID');
    }
    if (getenv('TWILIO_AUTH_TOKEN')) {
        $defaults['auth_token'] = (string) getenv('TWILIO_AUTH_TOKEN');
    }
    if (getenv('TWILIO_FROM_NUMBER')) {
        $defaults['from_number'] = (string) getenv('TWILIO_FROM_NUMBER');
    }
    $cache = $defaults;
    return $cache;
}

function sms_is_configured(): bool {
    $s = sms_load_secrets();
    $sid = trim((string) ($s['account_sid'] ?? ''));
    $tok = trim((string) ($s['auth_token'] ?? ''));
    $from = trim((string) ($s['from_number'] ?? ''));
    if ($sid === '' || strpos($sid, 'AC') !== 0) {
        return false;
    }
    if ($tok === '' || strlen($tok) < 10) {
        return false;
    }
    // E.164-ish
    if ($from === '' || !preg_match('/^\+[1-9]\d{7,14}$/', $from)) {
        return false;
    }
    return true;
}

/**
 * Normalize phone to E.164. Default country US (+1) when 10 digits.
 * @return string|null
 */
function sms_normalize_phone(string $raw, string $defaultCountry = 'US'): ?string {
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    // Keep leading +
    $hasPlus = str_starts_with($raw, '+');
    $digits = preg_replace('/\D+/', '', $raw);
    if ($digits === null || $digits === '') {
        return null;
    }
    if ($hasPlus) {
        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }
        return '+' . $digits;
    }
    // US/CA 10-digit
    if ($defaultCountry === 'US' || $defaultCountry === 'CA') {
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }
    }
    if (strlen($digits) >= 8 && strlen($digits) <= 15) {
        return '+' . $digits;
    }
    return null;
}

/**
 * Build invite SMS body for a playground house.
 */
function sms_playground_invite_body(string $houseName, string $inviteCode, string $siteUrl = ''): string {
    if ($siteUrl === '') {
        $siteUrl = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
    }
    $join = $siteUrl . '/join';
    return "You're invited to try {$houseName} on ilovepbj ops!\n\n"
        . "1) Open {$join}\n"
        . "2) Enter code: {$inviteCode}\n"
        . "3) Create your login and explore 💕\n\n"
        . "Questions? nutsaboutpbj@ilovepbj.shop";
}

/**
 * Send SMS via Twilio REST API.
 * @return array{ok:bool,sid?:string,error?:string,mode?:string}
 */
function sms_send(string $toE164, string $body): array {
    if (!sms_is_configured()) {
        return ['ok' => false, 'error' => 'not_configured', 'mode' => 'none'];
    }
    $s = sms_load_secrets();
    $sid = $s['account_sid'];
    $token = $s['auth_token'];
    $from = $s['from_number'];
    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($sid) . '/Messages.json';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => $sid . ':' . $token,
        CURLOPT_POSTFIELDS => http_build_query([
            'To' => $toE164,
            'From' => $from,
            'Body' => $body,
        ]),
        CURLOPT_TIMEOUT => 25,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'error' => $cerr ?: 'curl_failed', 'mode' => 'twilio'];
    }
    $data = json_decode($raw, true);
    if ($status >= 200 && $status < 300 && is_array($data) && !empty($data['sid'])) {
        return ['ok' => true, 'sid' => (string) $data['sid'], 'mode' => 'twilio'];
    }
    $msg = is_array($data) ? (string) ($data['message'] ?? $data['error_message'] ?? 'http_' . $status) : ('http_' . $status);
    error_log('sms_send failed: ' . $msg);
    return ['ok' => false, 'error' => $msg, 'mode' => 'twilio'];
}

/**
 * Known playground houses (invite code + label).
 * @return list<array{code:string,name:string,restaurant_id:int}>
 */
function sms_playground_houses(PDO $pdo): array {
    $out = [];
    $codes = [
        'DEMO-PBJ' => 'ilovepbj Playground',
        'SALES-PBJ' => 'Sales Showcase',
    ];
    try {
        $stmt = $pdo->query('SELECT id, name, invite_code FROM restaurants ORDER BY id ASC');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $code = strtoupper(trim((string) ($row['invite_code'] ?? '')));
            if ($code === '' || !isset($codes[$code])) {
                // Also include unlimited/demo houses
                $rid = (int) $row['id'];
                $isPlay = false;
                if (function_exists('pbj_restaurant_is_sales_playground') && pbj_restaurant_is_sales_playground($pdo, $rid)) {
                    $isPlay = true;
                } elseif (function_exists('pbj_restaurant_is_demo_house') && pbj_restaurant_is_demo_house($pdo, $rid)) {
                    $isPlay = true;
                } elseif (function_exists('pbj_restaurant_has_unlimited_seats') && pbj_restaurant_has_unlimited_seats($pdo, $rid)) {
                    $isPlay = true;
                }
                if (!$isPlay) {
                    continue;
                }
            }
            $out[] = [
                'code' => $code,
                'name' => (string) ($row['name'] ?: ($codes[$code] ?? $code)),
                'restaurant_id' => (int) $row['id'],
            ];
        }
    } catch (Throwable $e) {
        // fallback static
        foreach ($codes as $code => $name) {
            $out[] = ['code' => $code, 'name' => $name, 'restaurant_id' => 0];
        }
    }
    // Dedupe by code
    $seen = [];
    $uniq = [];
    foreach ($out as $h) {
        if (isset($seen[$h['code']])) {
            continue;
        }
        $seen[$h['code']] = true;
        $uniq[] = $h;
    }
    return $uniq;
}
