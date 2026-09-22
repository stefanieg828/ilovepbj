<?php
// config.php — ilovepbj ops

$host = 'localhost';
$db   = 'u621791130_ilovepbj_ops';
$user = 'u621791130_ilovepbj';
$pass = 'Fets0987654321@';

// false = real login required (marketing/login/register for guests)
// true  = auto-login tester (local testing only)
define('AUTH_BYPASS', false);

// Public site domain (links, cookies, absolute URLs)
define('APP_DOMAIN', 'ilovepbj.shop');
define('APP_PUBLIC_URL', 'https://ilovepbj.shop');

// Platform admins always get in and can open approve-users.php (free testers, support).
// Houses owned by these emails get unlimited seats (demo / sales playground).
// Add your emails (lowercase) — comma-separated.
define('PLATFORM_ADMIN_EMAILS', 'stefaniegillum@gmail.com,stefanieg828@gmail.com,vintagestar28@gmail.com');

// Optional: emails that auto-approve on signup (free testers you trust). Comma-separated, lowercase.
// Leave empty so paid users unlock via Stripe and others wait for manual approval.
define('ACCESS_AUTO_APPROVE_EMAILS', '');

// Where owner join-alerts are also CC'd (optional extra inbox).
define('OWNER_NOTIFY_CC', 'nutsaboutpbj@ilovepbj.shop');

// No-card free trial for new house / individual starters (days). 0 disables.
define('PBJ_TRIAL_DAYS', 14);

// Days before trial end to send the “trial ending soon” email (1–7 typical).
define('PBJ_TRIAL_REMINDER_DAYS_BEFORE', 3);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('ilovepbj DB connection failed: ' . $e->getMessage());
    http_response_code(503);
    die('Database connection failed. Please try again in a moment.');
}

if (session_status() === PHP_SESSION_NONE) {
    $https = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// Sales commission / rep attribution (ref codes, ledger, rates)
if (is_readable(__DIR__ . '/sales-commission.inc.php')) {
    require_once __DIR__ . '/sales-commission.inc.php';
}
if (function_exists('pbj_ensure_sales_tables')) {
    pbj_ensure_sales_tables($pdo);
}
if (function_exists('pbj_capture_sales_ref_from_request')) {
    pbj_capture_sales_ref_from_request();
}

/** Ensure users.access_status exists (pending | approved | blocked). */
function pbj_ensure_access_column(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'access_status'")->fetch();
        if (!$cols) {
            $pdo->exec(
                "ALTER TABLE users ADD COLUMN access_status ENUM('pending','approved','blocked') NOT NULL DEFAULT 'pending' AFTER role"
            );
            // Existing accounts were already in use — keep them approved
            $pdo->exec("UPDATE users SET access_status = 'approved'");
        }
    } catch (Exception $e) {
        // Table may not exist in odd envs — ignore; inserts will surface errors
    }
}

pbj_ensure_access_column($pdo);

/** Ensure users.theme_chosen exists (0 = show first-login theme picker). */
function pbj_ensure_theme_chosen_column(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'theme_chosen'")->fetch();
        if (!$cols) {
            $pdo->exec(
                "ALTER TABLE users ADD COLUMN theme_chosen TINYINT(1) NOT NULL DEFAULT 0 AFTER theme"
            );
            // Existing accounts already picked a vibe (or defaulted) — don't force the picker
            $pdo->exec('UPDATE users SET theme_chosen = 1');
        }
    } catch (Exception $e) {
        // ignore in odd envs
    }
}

pbj_ensure_theme_chosen_column($pdo);

/** Ensure last_login_at + password-reset columns on users. */
function pbj_ensure_user_desk_columns(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_login_at'")->fetch();
        if (!$cols) {
            $pdo->exec('ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL DEFAULT NULL AFTER created_at');
        }
        $tok = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_reset_token'")->fetch();
        if (!$tok) {
            $pdo->exec('ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(64) NULL DEFAULT NULL AFTER last_login_at');
        }
        $exp = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_reset_expires'")->fetch();
        if (!$exp) {
            $pdo->exec('ALTER TABLE users ADD COLUMN password_reset_expires DATETIME NULL DEFAULT NULL AFTER password_reset_token');
        }
    } catch (Exception $e) {
        // ignore in odd envs
    }
}

pbj_ensure_user_desk_columns($pdo);

/** Ensure users.billing_json for Stripe customer/subscription when solo / pre-house. */
function pbj_ensure_user_billing_column(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'billing_json'")->fetch();
        if (!$cols) {
            $pdo->exec('ALTER TABLE users ADD COLUMN billing_json LONGTEXT NULL DEFAULT NULL AFTER access_status');
        }
    } catch (Exception $e) {
        // ignore
    }
}

pbj_ensure_user_billing_column($pdo);

/** Ensure users.prefs_json for per-user settings (home shortcuts, etc.) that sync across devices. */
function pbj_ensure_user_prefs_column(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'prefs_json'")->fetch();
        if (!$cols) {
            $pdo->exec('ALTER TABLE users ADD COLUMN prefs_json LONGTEXT NULL DEFAULT NULL AFTER billing_json');
        }
    } catch (Exception $e) {
        // ignore
    }
}

pbj_ensure_user_prefs_column($pdo);

/**
 * Load user prefs JSON (associative array). Empty array on miss / invalid.
 *
 * @return array<string,mixed>
 */
function pbj_load_user_prefs(PDO $pdo, int $userId): array {
    if ($userId <= 0) {
        return [];
    }
    pbj_ensure_user_prefs_column($pdo);
    try {
        $stmt = $pdo->prepare('SELECT prefs_json FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $raw = $stmt->fetchColumn();
        if ($raw === false || $raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Merge patch into user prefs and persist. Returns full prefs after save.
 *
 * @param array<string,mixed> $patch
 * @return array<string,mixed>
 */
function pbj_save_user_prefs(PDO $pdo, int $userId, array $patch): array {
    if ($userId <= 0) {
        return $patch;
    }
    pbj_ensure_user_prefs_column($pdo);
    $current = pbj_load_user_prefs($pdo, $userId);
    foreach ($patch as $k => $v) {
        if ($v === null) {
            unset($current[$k]);
        } else {
            $current[$k] = $v;
        }
    }
    try {
        $stmt = $pdo->prepare('UPDATE users SET prefs_json = ? WHERE id = ?');
        $stmt->execute([json_encode($current, JSON_UNESCAPED_UNICODE), $userId]);
    } catch (Exception $e) {
        error_log('pbj_save_user_prefs: ' . $e->getMessage());
    }
    return $current;
}

/** One-time: restaurant owners must have users.role = owner. */
function pbj_backfill_owner_roles(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $pdo->exec(
            "UPDATE users u
             INNER JOIN restaurants r ON r.owner_id = u.id
             SET u.role = 'owner'
             WHERE u.role IS NULL OR u.role NOT IN ('owner', 'admin')"
        );
    } catch (Exception $e) {
        // restaurants table may be missing in odd envs
    }
}

pbj_backfill_owner_roles($pdo);

// Auto-login stub only while AUTH_BYPASS is on
if (AUTH_BYPASS && empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 0;
    $_SESSION['username'] = 'tester';
    $_SESSION['user_name'] = 'Tester';
    $_SESSION['name'] = 'Tester';
    $_SESSION['role'] = 'admin';
    $_SESSION['access_status'] = 'approved';
    $_SESSION['email'] = '';
    $_SESSION['theme_chosen'] = 1;
    if (!isset($_SESSION['theme'])) {
        $_SESSION['theme'] = 'sweet';
    }
}

// Drop leftover tester sessions when real auth is on
if (!AUTH_BYPASS && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 0) {
    $_SESSION = [];
}

// Prefer Whiskings profile display name (cookie)
if (!empty($_COOKIE['pbj_display_name'])) {
    $display = trim((string) $_COOKIE['pbj_display_name']);
    if ($display !== '') {
        $_SESSION['user_name'] = $display;
        $_SESSION['name'] = $display;
    }
}

function pbj_email_list(string $constName): array {
    if (!defined($constName)) {
        return [];
    }
    $raw = (string) constant($constName);
    $parts = array_filter(array_map(static function ($e) {
        return strtolower(trim($e));
    }, explode(',', $raw)));
    return array_values($parts);
}

function pbj_is_platform_admin(?string $email): bool {
    $email = strtolower(trim((string) $email));
    if ($email === '') {
        return false;
    }
    return in_array($email, pbj_email_list('PLATFORM_ADMIN_EMAILS'), true);
}

function pbj_should_auto_approve(string $email): bool {
    $email = strtolower(trim($email));
    if ($email === '') {
        return false;
    }
    if (pbj_is_platform_admin($email)) {
        return true;
    }
    return in_array($email, pbj_email_list('ACCESS_AUTO_APPROVE_EMAILS'), true);
}

/** pending | approved | blocked — defaults pending for safety */
function pbj_access_status_for_new_user(string $email): string {
    return pbj_should_auto_approve($email) ? 'approved' : 'pending';
}

function pbj_user_is_approved(): bool {
    if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
        return true;
    }
    $status = $_SESSION['access_status'] ?? 'pending';
    if ($status === 'approved') {
        return true;
    }
    // Platform admins always in (session email or re-check)
    if (pbj_is_platform_admin($_SESSION['email'] ?? '')) {
        return true;
    }
    return false;
}

/**
 * Block logged-in but unapproved users from the app (until payment / manual OK).
 * Allow public + waiting + logout + approve page for admins.
 */
function pbj_enforce_access_gate(): void {
    if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
        return;
    }
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        return;
    }

    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $open = [
        'index.php',
        'login.php',
        'register.php',
        'join.php',
        'logout.php',
        'waiting.php',
        'choose-theme.php',
        'forgot-password.php',
        'reset-password.php',
        'privacy.php',
        'terms.php',
        'refunds.php',
        'approve-users.php', // page itself checks platform admin
        'platform-console.php', // platform admin only (page enforces)
        // Stripe billing — pending users must reach Checkout before approval
        'stripe-checkout.php',
        'billing-success.php',
        'billing-plans.php',
        'billing-upgrade.php',
        'stripe-webhook.php',
        'stripe-status.php',
        'sales-playground-api.php',
        'invite-sms-api.php',
    ];
    if (in_array($script, $open, true)) {
        return;
    }

    // Refresh status from DB occasionally so approve takes effect without re-login
    global $pdo;
    if (empty($_SESSION['access_checked_at']) || (time() - (int)$_SESSION['access_checked_at']) > 30) {
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare('SELECT access_status, email, role, theme, theme_chosen, full_name, username FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$uid]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $_SESSION['access_status'] = $row['access_status'] ?? 'pending';
                    $_SESSION['email'] = $row['email'] ?? ($_SESSION['email'] ?? '');
                    if (!empty($row['theme'])) {
                        $_SESSION['theme'] = $row['theme'];
                    }
                    if (array_key_exists('theme_chosen', $row)) {
                        $_SESSION['theme_chosen'] = (int) $row['theme_chosen'];
                    }
                } else {
                    $_SESSION = [];
                    header('Location: /login');
                    exit();
                }
            } catch (Exception $e) {
                // If column missing mid-request, don't lock everyone out hard
            }
            $_SESSION['access_checked_at'] = time();
        }
    }

    if (!pbj_user_is_approved()) {
        $status = $_SESSION['access_status'] ?? 'pending';
        if ($status === 'blocked') {
            header('Location: /waiting?blocked=1');
            exit();
        }
        header('Location: /waiting');
        exit();
    }

    // Paid or active free trial required once access is approved (trial expiry paywall)
    if ($pdo instanceof PDO && function_exists('pbj_user_billing_allows_access') && !pbj_user_billing_allows_access($pdo, $uid)) {
        $info = function_exists('pbj_trial_info') ? pbj_trial_info($pdo, $uid) : [];
        $qs = !empty($info['expired']) ? 'pay=trial_expired' : 'pay=needed';
        header('Location: /waiting?' . $qs);
        exit();
    }

    // First approved login: pick a starting theme before the hub
    if (!pbj_user_has_chosen_theme()) {
        header('Location: /choose-theme');
        exit();
    }
}

pbj_enforce_access_gate();

/**
 * Live pricing + seat caps (enforced when people join a house).
 * - Individual $5 · solo · no invite codes
 * - Small House 1–10 · $29
 * - Busy House 11–30 · $59
 * - Big House 31–75 · $99
 * - Large House 76–150 · $149
 * - Jumbo House 151–300 · $199
 * - Mega / Enterprise 300+ · Custom (starts at $249)
 * Multi-unit discounts apply per location tier × units (see marketing page).
 *
 * max_seats = max login accounts linked to the restaurant (owner counts as 1).
 * allows_invites = whether house invite codes / join-with-code are allowed.
 *
 * @return list<array{id:string,name:string,price:string,price_note?:string,tagline:string,features:list<string>,max_seats:?int,allows_invites:bool,recommended?:bool,coming?:bool,limited?:bool}>
 */
function pbj_plans(): array {
    return [
        [
            'id' => 'individual',
            'name' => 'Individual',
            'price' => '$5/mo',
            'price_note' => 'per person',
            'tagline' => 'Limited access — you build the house yourself',
            'limited' => true,
            'max_seats' => 1,
            'allows_invites' => false,
            'features' => [
                'Personal login & theme prefs',
                'Core checklists & tools you set up',
                'Solo only — no house invite codes',
                'Upgrade anytime when you add a crew',
            ],
        ],
        [
            'id' => 'crew_10',
            'name' => 'Small House',
            'price' => '$29/mo',
            'price_note' => '1–10 employees',
            'tagline' => 'Full ops for a tight crew',
            'recommended' => true,
            'max_seats' => 10,
            'allows_invites' => true,
            'features' => [
                'Showtime + The Heat + Jelly Jar',
                'Sandwich HQ: team, schedules, sales & labor',
                'Recipes → costing → inventory',
                'House invite codes & multi-device sync',
                'Up to 10 people on the house',
            ],
        ],
        [
            'id' => 'crew_30',
            'name' => 'Busy House',
            'price' => '$59/mo',
            'price_note' => '11–30 employees',
            'tagline' => 'Room to grow without losing the plot',
            'max_seats' => 30,
            'allows_invites' => true,
            'features' => [
                'Everything in Small House',
                'Larger roster & shift coverage',
                'FOH + BOH channels that stay tidy',
                'Training tracks for new hires',
                'Up to 30 people on the house',
            ],
        ],
        [
            'id' => 'crew_75',
            'name' => 'Big House',
            'price' => '$99/mo',
            'price_note' => '31–75 employees',
            'tagline' => 'One jar for a full-sized team',
            'max_seats' => 75,
            'allows_invites' => true,
            'features' => [
                'Everything in Busy House',
                'Headcount headroom for peak seasons',
                'Managers + multi-role staff',
                'Reports, cash & weekly trends',
                'Up to 75 people on the house',
            ],
        ],
        [
            'id' => 'crew_150',
            'name' => 'Large House',
            'price' => '$149/mo',
            'price_note' => '76–150 employees',
            'tagline' => 'Big crew energy, still one house',
            'max_seats' => 150,
            'allows_invites' => true,
            'features' => [
                'Everything in Big House',
                'Room for multi-shift full rosters',
                'Managers + multi-role staff at scale',
                'Reports, cash & weekly trends',
                'Up to 150 people on the house',
            ],
        ],
        [
            'id' => 'crew_300',
            'name' => 'Jumbo House',
            'price' => '$199/mo',
            'price_note' => '151–300 employees',
            'tagline' => 'Flagship single-location headcount',
            'max_seats' => 300,
            'allows_invites' => true,
            'features' => [
                'Everything in Large House',
                'Headcount for large single sites',
                'Managers + multi-role staff at scale',
                'Reports, cash & weekly trends',
                'Up to 300 people on the house',
            ],
        ],
        [
            'id' => 'custom',
            'name' => 'Mega / Enterprise',
            'price' => 'Custom',
            'price_note' => '300+ employees · starts at $249/mo',
            'tagline' => 'Enterprise headcount & multi-unit groups',
            'coming' => true,
            'max_seats' => null,
            'allows_invites' => true,
            'features' => [
                '300+ employees or multi-location groups',
                'Shared recipes, vendors & standards',
                'Custom onboarding & rollout',
                'Billing & roles at scale',
                'Starts at $249/mo — we quote your fit',
            ],
        ],
    ];
}

function pbj_plan_by_id(string $id): ?array {
    // Map legacy plan ids from earlier drafts
    $aliases = [
        'taster' => 'individual',
        'house' => 'crew_10',
        'empire' => 'custom',
        'full_house' => 'crew_10',
    ];
    if (isset($aliases[$id])) {
        $id = $aliases[$id];
    }
    foreach (pbj_plans() as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

/** Default plan when starting a restaurant (most common independent size). */
function pbj_default_plan_id(): string {
    return 'crew_10';
}

/** Max login seats for a plan (null = unlimited / custom). */
function pbj_plan_max_seats(?array $plan): ?int {
    if (!$plan) {
        return 10;
    }
    if (array_key_exists('max_seats', $plan)) {
        $m = $plan['max_seats'];
        return $m === null ? null : max(1, (int) $m);
    }
    // Fallbacks if older plan arrays omit the field
    if (!empty($plan['limited']) || ($plan['id'] ?? '') === 'individual') {
        return 1;
    }
    $id = (string) ($plan['id'] ?? '');
    return match ($id) {
        'crew_10' => 10,
        'crew_30' => 30,
        'crew_75' => 75,
        'crew_150' => 150,
        'crew_300' => 300,
        'custom' => null,
        default => 10,
    };
}

/** Whether this plan may share house invite codes / accept joiners. */
function pbj_plan_allows_invites(?array $plan): bool {
    if (!$plan) {
        return true;
    }
    if (array_key_exists('allows_invites', $plan)) {
        return (bool) $plan['allows_invites'];
    }
    return empty($plan['limited']) && ($plan['id'] ?? '') !== 'individual';
}

/** Plan id stored on a restaurant (from restaurant_settings). */
function pbj_restaurant_plan_id(PDO $pdo, int $restaurantId): string {
    if ($restaurantId <= 0) {
        return pbj_default_plan_id();
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $decoded = json_decode((string) $raw, true);
            if (is_array($decoded) && !empty($decoded['plan'])) {
                $plan = pbj_plan_by_id((string) $decoded['plan']);
                if ($plan) {
                    return $plan['id'];
                }
            }
        }
    } catch (Throwable $e) {
        // fall through
    }
    return pbj_default_plan_id();
}

/** How many login accounts are linked to this restaurant. */
function pbj_restaurant_member_count(PDO $pdo, int $restaurantId): int {
    if ($restaurantId <= 0) {
        return 0;
    }
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_restaurant WHERE restaurant_id = ?');
        $stmt->execute([$restaurantId]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * True when this house may grow without a paid seat cap
 * (platform-admin owned playground / sales demo, or settings flag).
 */
function pbj_restaurant_has_unlimited_seats(PDO $pdo, int $restaurantId): bool {
    if ($restaurantId <= 0) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $settings = json_decode((string) $raw, true);
            if (is_array($settings)) {
                if (!empty($settings['unlimited_seats']) || !empty($settings['demo_house'])) {
                    return true;
                }
                if (($settings['plan'] ?? '') === 'custom') {
                    // Custom can be unlimited when flagged; still check owner below
                }
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
    // Owner is a platform admin → demo playground
    try {
        $stmt = $pdo->prepare(
            'SELECT u.email FROM restaurants r
             LEFT JOIN users u ON u.id = r.owner_id
             WHERE r.id = ? LIMIT 1'
        );
        $stmt->execute([$restaurantId]);
        $email = strtolower(trim((string) $stmt->fetchColumn()));
        if ($email !== '' && pbj_is_platform_admin($email)) {
            return true;
        }
    } catch (Throwable $e) {
        // ignore
    }
    return false;
}

/** Demo / sales playground houses auto-approve joiners so trials start immediately. */
function pbj_restaurant_is_demo_house(PDO $pdo, int $restaurantId): bool {
    if ($restaurantId <= 0) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $settings = json_decode((string) $raw, true);
            if (is_array($settings) && !empty($settings['demo_house'])) {
                return true;
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
    return pbj_restaurant_has_unlimited_seats($pdo, $restaurantId);
}

/**
 * Can this house accept another login account?
 * @return array{ok:bool,reason:string,count:int,max:?int,plan_id:string,allows_invites:bool,remaining:?int,unlimited?:bool}
 */
function pbj_restaurant_seat_status(PDO $pdo, int $restaurantId): array {
    $planId = pbj_restaurant_plan_id($pdo, $restaurantId);
    $plan = pbj_plan_by_id($planId);
    $max = pbj_plan_max_seats($plan);
    $allows = pbj_plan_allows_invites($plan);
    $count = pbj_restaurant_member_count($pdo, $restaurantId);
    $unlimited = pbj_restaurant_has_unlimited_seats($pdo, $restaurantId);
    if ($unlimited) {
        $max = null;
        $allows = true;
    }
    $remaining = $max === null ? null : max(0, $max - $count);

    if (!$allows) {
        return [
            'ok' => false,
            'reason' => 'invites_disabled',
            'count' => $count,
            'max' => $max,
            'plan_id' => $planId,
            'allows_invites' => false,
            'remaining' => $remaining,
            'unlimited' => $unlimited,
        ];
    }
    if ($max !== null && $count >= $max) {
        return [
            'ok' => false,
            'reason' => 'at_capacity',
            'count' => $count,
            'max' => $max,
            'plan_id' => $planId,
            'allows_invites' => true,
            'remaining' => 0,
            'unlimited' => false,
        ];
    }
    return [
        'ok' => true,
        'reason' => '',
        'count' => $count,
        'max' => $max,
        'plan_id' => $planId,
        'allows_invites' => true,
        'remaining' => $remaining,
        'unlimited' => $unlimited,
    ];
}

/** Friendly error when a join is blocked by plan limits. */
function pbj_seat_limit_message(array $status): string {
    $reason = (string) ($status['reason'] ?? '');
    if ($reason === 'invites_disabled') {
        return 'This plan is solo only — it can’t accept teammates. Ask them to upgrade to a house plan.';
    }
    if ($reason === 'at_capacity') {
        $max = (int) ($status['max'] ?? 0);
        $name = pbj_plan_by_id((string) ($status['plan_id'] ?? ''))['name'] ?? 'This plan';
        return $name . ' is full (' . $max . ' people). The owner can upgrade at /billing/plans or free a seat before anyone else can join.';
    }
    return 'This house can’t accept another teammate right now.';
}

/** Numeric rank for comparing plans (higher = more seats / features). */
function pbj_plan_rank(string $planId): int {
    $plan = pbj_plan_by_id($planId);
    $id = $plan ? $plan['id'] : $planId;
    return match ($id) {
        'individual' => 1,
        'crew_10' => 10,
        'crew_30' => 30,
        'crew_75' => 75,
        'crew_150' => 150,
        'crew_300' => 300,
        'custom' => 400,
        default => 0,
    };
}

/** True if $toPlan is a paid upgrade from $fromPlan. */
function pbj_plan_is_upgrade(string $fromPlan, string $toPlan): bool {
    $from = pbj_plan_by_id($fromPlan);
    $to = pbj_plan_by_id($toPlan);
    if (!$to || !empty($to['coming'])) {
        return false;
    }
    $fromId = $from ? $from['id'] : $fromPlan;
    $toId = $to['id'];
    if ($fromId === $toId) {
        return false;
    }
    return pbj_plan_rank($toId) > pbj_plan_rank($fromId);
}

/**
 * Plans you can upgrade into from the current plan (excludes current + custom contact).
 * @return list<array>
 */
function pbj_upgrade_plan_options(string $currentPlanId): array {
    $out = [];
    foreach (pbj_plans() as $p) {
        if (!empty($p['coming'])) {
            continue;
        }
        if (pbj_plan_is_upgrade($currentPlanId, $p['id'])) {
            $out[] = $p;
        }
    }
    return $out;
}

/** @return array<string,mixed> */
function pbj_load_user_billing(PDO $pdo, int $userId): array {
    if ($userId <= 0) {
        return [];
    }
    try {
        $stmt = $pdo->prepare('SELECT billing_json FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $decoded = json_decode((string) $raw, true);
            return is_array($decoded) ? $decoded : [];
        }
    } catch (Throwable $e) {
        // column may be missing briefly
    }
    return [];
}

function pbj_save_user_billing(PDO $pdo, int $userId, array $patch): void {
    if ($userId <= 0) {
        return;
    }
    $cur = pbj_load_user_billing($pdo, $userId);
    $merged = array_merge($cur, $patch);
    try {
        $stmt = $pdo->prepare('UPDATE users SET billing_json = ? WHERE id = ?');
        $stmt->execute([json_encode($merged, JSON_UNESCAPED_UNICODE), $userId]);
    } catch (Throwable $e) {
        error_log('pbj_save_user_billing: ' . $e->getMessage());
    }
}

/** Free trial length in days (0 = disabled). */
function pbj_trial_days(): int {
    if (defined('PBJ_TRIAL_DAYS')) {
        return max(0, (int) PBJ_TRIAL_DAYS);
    }
    return 14;
}

/**
 * @return array{status:string,trial_ends_at:?string,plan_id:string,billing:array}
 */
function pbj_restaurant_billing_snapshot(PDO $pdo, int $restaurantId): array {
    $out = ['status' => '', 'trial_ends_at' => null, 'plan_id' => '', 'billing' => []];
    if ($restaurantId <= 0) {
        return $out;
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if (!$raw) {
            return $out;
        }
        $settings = json_decode((string) $raw, true);
        if (!is_array($settings)) {
            return $out;
        }
        $billing = is_array($settings['billing'] ?? null) ? $settings['billing'] : [];
        $status = strtolower(trim((string) ($billing['status'] ?? $settings['billing_status'] ?? '')));
        $ends = $billing['trial_ends_at'] ?? $settings['trial_ends_at'] ?? null;
        $out['status'] = $status;
        $out['trial_ends_at'] = is_string($ends) && $ends !== '' ? $ends : null;
        $out['plan_id'] = (string) ($settings['plan'] ?? $billing['plan_id'] ?? '');
        $out['billing'] = $billing;
    } catch (Throwable $e) {
        // ignore
    }
    return $out;
}

/**
 * Write billing status on a restaurant (trial / paid / expired).
 * Does not require Stripe helpers.
 */
function pbj_update_restaurant_billing_status(PDO $pdo, int $restaurantId, array $patch): void {
    if ($restaurantId <= 0) {
        return;
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
        if (!is_array($settings)) {
            $settings = [];
        }
        $billing = is_array($settings['billing'] ?? null) ? $settings['billing'] : [];
        $settings['billing'] = array_merge($billing, $patch);
        if (!empty($patch['status'])) {
            $settings['billing_status'] = (string) $patch['status'];
        }
        if (!empty($patch['plan_id'])) {
            $settings['plan'] = (string) $patch['plan_id'];
        }
        if (!empty($patch['trial_ends_at'])) {
            $settings['trial_ends_at'] = (string) $patch['trial_ends_at'];
        }
        $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
        if ($raw !== false && $raw !== null) {
            $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                ->execute([$json, $restaurantId]);
        } else {
            $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                ->execute([$restaurantId, $json]);
        }
    } catch (Throwable $e) {
        error_log('pbj_update_restaurant_billing_status: ' . $e->getMessage());
    }
}

/** True if this account already used (or is on) a free trial. */
function pbj_user_has_used_trial(PDO $pdo, int $userId): bool {
    $b = pbj_load_user_billing($pdo, $userId);
    if (!empty($b['trial_started_at']) || !empty($b['trial_ends_at']) || !empty($b['trial'])) {
        return true;
    }
    $status = strtolower(trim((string) ($b['status'] ?? '')));
    return in_array($status, ['trialing', 'trial_expired'], true);
}

/**
 * Start a no-card free trial for a new house / individual starter.
 * Approves the user and stores trial_ends_at on user (+ restaurant when present).
 *
 * @return array{ok:bool,error?:string,trial_ends_at?:string,days?:int}
 */
function pbj_start_free_trial(PDO $pdo, int $userId, string $planId, int $restaurantId = 0): array {
    $days = pbj_trial_days();
    if ($days <= 0) {
        return ['ok' => false, 'error' => 'trial_disabled'];
    }
    if ($userId <= 0) {
        return ['ok' => false, 'error' => 'bad_user'];
    }
    $plan = pbj_plan_by_id($planId);
    if (!$plan || !empty($plan['coming'])) {
        $planId = pbj_default_plan_id();
        $plan = pbj_plan_by_id($planId);
    } else {
        $planId = $plan['id'];
    }

    // Already paid — never overwrite with trial
    $cur = pbj_load_user_billing($pdo, $userId);
    if (strtolower((string) ($cur['status'] ?? '')) === 'paid') {
        return ['ok' => false, 'error' => 'already_paid'];
    }
    if (pbj_user_has_used_trial($pdo, $userId) && strtolower((string) ($cur['status'] ?? '')) === 'trialing') {
        // Already mid-trial — treat as success
        return [
            'ok' => true,
            'trial_ends_at' => (string) ($cur['trial_ends_at'] ?? ''),
            'days' => $days,
            'existing' => true,
        ];
    }
    if (pbj_user_has_used_trial($pdo, $userId) && strtolower((string) ($cur['status'] ?? '')) === 'trial_expired') {
        return ['ok' => false, 'error' => 'trial_already_used'];
    }

    $starts = date('c');
    $endsTs = time() + ($days * 86400);
    $ends = date('c', $endsTs);
    $patch = [
        'status' => 'trialing',
        'plan_id' => $planId,
        'trial' => true,
        'trial_started_at' => $starts,
        'trial_ends_at' => $ends,
        'trial_days' => $days,
        'updated_at' => $starts,
    ];
    if ($restaurantId > 0) {
        $patch['restaurant_id'] = $restaurantId;
    }
    pbj_save_user_billing($pdo, $userId, $patch);

    if ($restaurantId > 0) {
        pbj_update_restaurant_billing_status($pdo, $restaurantId, [
            'status' => 'trialing',
            'plan_id' => $planId,
            'trial' => true,
            'trial_started_at' => $starts,
            'trial_ends_at' => $ends,
            'trial_days' => $days,
        ]);
    }

    try {
        $pdo->prepare("UPDATE users SET access_status = 'approved' WHERE id = ?")->execute([$userId]);
    } catch (Throwable $e) {
        // ignore
    }
    if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === $userId) {
        $_SESSION['access_status'] = 'approved';
    }

    error_log('pbj_start_free_trial user#' . $userId . ' plan=' . $planId . ' rid=' . $restaurantId . ' ends=' . $ends);
    return ['ok' => true, 'trial_ends_at' => $ends, 'days' => $days];
}

/**
 * Trial / paid summary for banners and billing UI.
 *
 * @return array{
 *   active:bool,expired:bool,paid:bool,status:string,trial_ends_at:?string,
 *   days_left:int,ends_label:string,plan_id:string
 * }
 */
function pbj_trial_info(PDO $pdo, int $userId): array {
    $empty = [
        'active' => false,
        'expired' => false,
        'paid' => false,
        'status' => '',
        'trial_ends_at' => null,
        'days_left' => 0,
        'ends_label' => '',
        'plan_id' => '',
    ];
    if ($userId <= 0) {
        return $empty;
    }

    $userB = pbj_load_user_billing($pdo, $userId);
    $status = strtolower(trim((string) ($userB['status'] ?? '')));
    $ends = is_string($userB['trial_ends_at'] ?? null) ? (string) $userB['trial_ends_at'] : null;
    $planId = (string) ($userB['plan_id'] ?? '');

    // Prefer restaurant billing when on a real house
    try {
        $ctx = pbj_billing_context($pdo, $userId);
        $rid = (int) ($ctx['restaurant_id'] ?? 0);
        if ($rid > 0) {
            $snap = pbj_restaurant_billing_snapshot($pdo, $rid);
            if ($snap['status'] !== '') {
                $status = $snap['status'];
            }
            if (!empty($snap['trial_ends_at'])) {
                $ends = $snap['trial_ends_at'];
            }
            if ($snap['plan_id'] !== '') {
                $planId = $snap['plan_id'];
            }
        }
        if ($planId === '' && !empty($ctx['plan_id'])) {
            $planId = (string) $ctx['plan_id'];
        }
    } catch (Throwable $e) {
        // ignore
    }

    if ($status === 'paid' || $status === 'lifetime_free') {
        return array_merge($empty, ['paid' => true, 'status' => $status, 'plan_id' => $planId]);
    }

    $endsTs = $ends ? strtotime($ends) : false;
    $now = time();
    if ($status === 'trialing' && $endsTs && $endsTs > $now) {
        $daysLeft = (int) max(1, (int) ceil(($endsTs - $now) / 86400));
        return [
            'active' => true,
            'expired' => false,
            'paid' => false,
            'status' => 'trialing',
            'trial_ends_at' => $ends,
            'days_left' => $daysLeft,
            'ends_label' => date('M j, Y', $endsTs),
            'plan_id' => $planId,
        ];
    }
    if ($status === 'trialing' || $status === 'trial_expired' || ($endsTs && $endsTs <= $now && !empty($userB['trial']))) {
        return [
            'active' => false,
            'expired' => true,
            'paid' => false,
            'status' => 'trial_expired',
            'trial_ends_at' => $ends,
            'days_left' => 0,
            'ends_label' => $endsTs ? date('M j, Y', $endsTs) : '',
            'plan_id' => $planId,
        ];
    }
    return array_merge($empty, ['status' => $status, 'plan_id' => $planId, 'trial_ends_at' => $ends]);
}

/**
 * Mark trial expired on user (+ restaurant). Does not change access_status
 * (gate uses billing). Safe to call repeatedly.
 */
function pbj_expire_trial(PDO $pdo, int $userId, int $restaurantId = 0): void {
    if ($userId <= 0) {
        return;
    }
    $cur = pbj_load_user_billing($pdo, $userId);
    if (strtolower((string) ($cur['status'] ?? '')) === 'paid') {
        return;
    }
    pbj_save_user_billing($pdo, $userId, [
        'status' => 'trial_expired',
        'trial' => true,
        'trial_expired_at' => date('c'),
        'updated_at' => date('c'),
    ]);
    if ($restaurantId <= 0) {
        $restaurantId = (int) ($cur['restaurant_id'] ?? 0);
    }
    if ($restaurantId <= 0) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ? ORDER BY id DESC LIMIT 1');
            $stmt->execute([$userId]);
            $restaurantId = (int) ($stmt->fetchColumn() ?: 0);
        } catch (Throwable $e) {
            $restaurantId = 0;
        }
    }
    if ($restaurantId > 0) {
        $snap = pbj_restaurant_billing_snapshot($pdo, $restaurantId);
        if (($snap['status'] ?? '') !== 'paid') {
            pbj_update_restaurant_billing_status($pdo, $restaurantId, [
                'status' => 'trial_expired',
                'trial_expired_at' => date('c'),
            ]);
        }
    }
}

/**
 * Whether billing still allows app access.
 * Only free-trial expiry blocks (trialing past end / trial_expired).
 * Legacy manually approved or pre-trial houses stay open.
 */
function pbj_user_billing_allows_access(PDO $pdo, int $userId): bool {
    if ($userId <= 0) {
        return false;
    }
    $email = (string) ($_SESSION['email'] ?? '');
    if ($email === '') {
        try {
            $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $email = (string) ($stmt->fetchColumn() ?: '');
        } catch (Throwable $e) {
            $email = '';
        }
    }
    if (pbj_is_platform_admin($email)) {
        return true;
    }

    $ub = pbj_load_user_billing($pdo, $userId);
    $ust = strtolower(trim((string) ($ub['status'] ?? '')));
    if ($ust === 'paid' || $ust === 'lifetime_free') {
        return true;
    }
    if ($ust === 'trialing') {
        $ends = !empty($ub['trial_ends_at']) ? strtotime((string) $ub['trial_ends_at']) : false;
        if ($ends && $ends > time()) {
            return true;
        }
        if ($ends && $ends <= time()) {
            pbj_expire_trial($pdo, $userId, (int) ($ub['restaurant_id'] ?? 0));
            // Fall through — may still be open via another paid house
        }
    }
    if ($ust === 'trial_expired') {
        // Check if they later joined a paid house
    }

    $blockedByExpiredTrial = ($ust === 'trial_expired');
    $hasPaidOrActiveHouse = false;

    try {
        $stmt = $pdo->prepare(
            'SELECT r.id, r.owner_id FROM user_restaurant ur
             JOIN restaurants r ON r.id = ur.restaurant_id
             WHERE ur.user_id = ?'
        );
        $stmt->execute([$userId]);
        $houses = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$houses) {
            $own = $pdo->prepare('SELECT id, owner_id FROM restaurants WHERE owner_id = ?');
            $own->execute([$userId]);
            $houses = $own->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        foreach ($houses as $h) {
            $rid = (int) ($h['id'] ?? 0);
            if ($rid <= 0) {
                continue;
            }
            if (function_exists('pbj_restaurant_is_playground') && pbj_restaurant_is_playground($pdo, $rid)) {
                continue;
            }
            $snap = pbj_restaurant_billing_snapshot($pdo, $rid);
            $st = strtolower((string) ($snap['status'] ?? ''));
            if (in_array($st, ['paid', 'lifetime_free', 'demo'], true)) {
                $hasPaidOrActiveHouse = true;
                break;
            }
            if ($st === 'trialing') {
                $ends = $snap['trial_ends_at'] ? strtotime((string) $snap['trial_ends_at']) : false;
                if ($ends && $ends > time()) {
                    $hasPaidOrActiveHouse = true;
                    break;
                }
                // Expire and flag
                $ownerId = (int) ($h['owner_id'] ?? $userId);
                pbj_expire_trial($pdo, $ownerId, $rid);
                $blockedByExpiredTrial = true;
                continue;
            }
            if ($st === 'trial_expired') {
                $blockedByExpiredTrial = true;
            }
            // selected_unpaid / empty / other → legacy; not a trial lock
        }
    } catch (Throwable $e) {
        // fail open for infra blips
        return true;
    }

    if ($hasPaidOrActiveHouse) {
        return true;
    }
    if ($blockedByExpiredTrial) {
        return false;
    }
    // No trial record — legacy free, manual approve, or staff on non-trial house
    return true;
}

/**
 * True when owner should be nudged / sent to Stripe (active or expired trial).
 */
function pbj_user_needs_checkout(PDO $pdo, int $userId): bool {
    if ($userId <= 0) {
        return false;
    }
    if (pbj_is_platform_admin((string) ($_SESSION['email'] ?? ''))) {
        return false;
    }
    $info = pbj_trial_info($pdo, $userId);
    if (!empty($info['paid'])) {
        return false;
    }
    return !empty($info['active']) || !empty($info['expired']);
}

/** Days-before window for the “ending soon” trial email. */
function pbj_trial_reminder_days_before(): int {
    if (defined('PBJ_TRIAL_REMINDER_DAYS_BEFORE')) {
        return max(1, min(7, (int) PBJ_TRIAL_REMINDER_DAYS_BEFORE));
    }
    return 3;
}

/**
 * Build plain-text trial reminder emails.
 *
 * @param 'soon'|'end' $kind
 * @return array{subject:string,body:string}
 */
function pbj_trial_reminder_email_copy(string $kind, string $name, string $endsLabel, int $daysLeft, string $planName = ''): array {
    $base = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
    $plans = $base . '/billing/plans';
    $checkout = $base . '/billing/checkout?trial=1';
    $support = defined('OWNER_NOTIFY_CC') && OWNER_NOTIFY_CC !== ''
        ? (string) OWNER_NOTIFY_CC
        : 'nutsaboutpbj@ilovepbj.shop';
    $hi = $name !== '' ? "Hi {$name}," : 'Hi there,';
    $planBit = $planName !== '' ? " ({$planName})" : '';

    if ($kind === 'end') {
        return [
            'subject' => 'Your ilovepbj free trial ends today',
            'body' => $hi . "\n\n"
                . "Your free trial{$planBit} ends today"
                . ($endsLabel !== '' ? " ({$endsLabel})" : '')
                . ".\n\n"
                . "After today, your kitchen pauses until you subscribe — your house data, invite codes, and team setup stay safe so you can pick right back up.\n\n"
                . "Subscribe in one step:\n{$checkout}\n"
                . "Or review plans anytime:\n{$plans}\n\n"
                . "Questions about plans, seats, or getting set up? Reach out — we’re happy to help:\n"
                . "{$support}\n\n"
                . "Thanks for trying ilovepbj ops 💕\n"
                . "— the ilovepbj team\n",
        ];
    }

    // soon
    $dayWord = $daysLeft === 1 ? '1 day' : $daysLeft . ' days';
    return [
        'subject' => "Your ilovepbj free trial ends in {$dayWord}",
        'body' => $hi . "\n\n"
            . "Friendly heads-up: your free trial{$planBit} ends in {$dayWord}"
            . ($endsLabel !== '' ? " — on {$endsLabel}" : '')
            . ".\n\n"
            . "No card is on file yet. When you’re ready, subscribe to keep full access for your house and crew:\n"
            . "{$checkout}\n\n"
            . "See plans & seat tiers:\n{$plans}\n\n"
            . "Questions before you decide? Email us anytime — we’re here:\n"
            . "{$support}\n\n"
            . "— the ilovepbj team\n",
    ];
}

/**
 * Send due trial reminder emails (soon + end-day). Safe to run daily via cron.
 * Tracks sent flags on users.billing_json so each email goes at most once.
 *
 * @param array{dry_run?:bool,force_user_id?:int} $opts
 * @return array{ok:bool,scanned:int,soon:int,end:int,skipped:int,errors:int,details:list<array>}
 */
function pbj_process_trial_reminders(PDO $pdo, array $opts = []): array {
    $dry = !empty($opts['dry_run']);
    $forceUid = (int) ($opts['force_user_id'] ?? 0);
    $soonWindow = pbj_trial_reminder_days_before();
    $out = [
        'ok' => true,
        'scanned' => 0,
        'soon' => 0,
        'end' => 0,
        'skipped' => 0,
        'errors' => 0,
        'details' => [],
    ];

    // Prefer US restaurant timezone for “day” boundaries
    $tzName = 'America/New_York';
    try {
        $tz = new DateTimeZone($tzName);
    } catch (Throwable $e) {
        $tz = new DateTimeZone('UTC');
        $tzName = 'UTC';
    }
    $now = new DateTimeImmutable('now', $tz);
    $today = $now->format('Y-m-d');

    try {
        if ($forceUid > 0) {
            $stmt = $pdo->prepare(
                'SELECT id, email, full_name, username, billing_json FROM users WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$forceUid]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } else {
            // Narrow to anyone who ever had trial fields (cheap LIKE; JSON parsed in PHP)
            $stmt = $pdo->query(
                "SELECT id, email, full_name, username, billing_json FROM users
                 WHERE billing_json IS NOT NULL AND billing_json != ''
                   AND (
                     billing_json LIKE '%trial_ends_at%'
                     OR billing_json LIKE '%\"trialing\"%'
                     OR billing_json LIKE '%trial_expired%'
                   )"
            );
            $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        }
    } catch (Throwable $e) {
        error_log('pbj_process_trial_reminders query: ' . $e->getMessage());
        return array_merge($out, ['ok' => false, 'error' => $e->getMessage()]);
    }

    foreach ($rows as $row) {
        $out['scanned']++;
        $uid = (int) ($row['id'] ?? 0);
        $email = strtolower(trim((string) ($row['email'] ?? '')));
        $name = trim((string) ($row['full_name'] ?: $row['username'] ?: ''));
        $billing = [];
        if (!empty($row['billing_json'])) {
            $decoded = json_decode((string) $row['billing_json'], true);
            if (is_array($decoded)) {
                $billing = $decoded;
            }
        }

        $status = strtolower(trim((string) ($billing['status'] ?? '')));
        if ($status === 'paid' || $status === 'lifetime_free') {
            $out['skipped']++;
            continue;
        }

        $endsRaw = (string) ($billing['trial_ends_at'] ?? '');
        // Prefer restaurant snapshot when linked
        $rid = (int) ($billing['restaurant_id'] ?? 0);
        if ($rid <= 0) {
            try {
                $q = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ? ORDER BY id DESC LIMIT 1');
                $q->execute([$uid]);
                $rid = (int) ($q->fetchColumn() ?: 0);
            } catch (Throwable $e) {
                $rid = 0;
            }
        }
        if ($rid > 0) {
            $snap = pbj_restaurant_billing_snapshot($pdo, $rid);
            if (in_array($snap['status'], ['paid', 'lifetime_free'], true)) {
                $out['skipped']++;
                continue;
            }
            if (!empty($snap['trial_ends_at'])) {
                $endsRaw = (string) $snap['trial_ends_at'];
            }
            if ($snap['status'] !== '') {
                $status = $snap['status'];
            }
        }

        if ($endsRaw === '') {
            $out['skipped']++;
            continue;
        }
        $endsTs = strtotime($endsRaw);
        if ($endsTs === false) {
            $out['skipped']++;
            continue;
        }

        try {
            $endsDt = (new DateTimeImmutable('@' . $endsTs))->setTimezone($tz);
        } catch (Throwable $e) {
            $out['skipped']++;
            continue;
        }
        $endsDay = $endsDt->format('Y-m-d');
        $endsLabel = $endsDt->format('M j, Y');

        // Whole calendar days from today until end date (0 = ends today, negative = past)
        $todayStart = $now->setTime(0, 0, 0);
        $endsStart = $endsDt->setTime(0, 0, 0);
        $daysLeft = (int) $todayStart->diff($endsStart)->format('%r%a');

        $planId = (string) ($billing['plan_id'] ?? '');
        $plan = $planId !== '' ? pbj_plan_by_id($planId) : null;
        $planName = $plan ? (string) $plan['name'] : '';

        $soonSent = !empty($billing['trial_reminder_soon_sent_at']);
        $endSent = !empty($billing['trial_reminder_end_sent_at']);

        // —— End-day (or first run after midnight on end date, through +1 day catch-up) ——
        $isEndDay = ($endsDay === $today) || ($daysLeft === 0);
        $isEndCatchUp = ($daysLeft === -1 && !$endSent); // missed exact day — send once next morning
        if (($isEndDay || $isEndCatchUp) && !$endSent) {
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out['skipped']++;
                $out['details'][] = ['user_id' => $uid, 'kind' => 'end', 'ok' => false, 'error' => 'no_email'];
                continue;
            }
            $copy = pbj_trial_reminder_email_copy('end', $name, $endsLabel, 0, $planName);
            $sent = false;
            if ($dry) {
                $sent = true;
            } elseif (function_exists('pbj_send_mail')) {
                $sent = pbj_send_mail($email, $copy['subject'], $copy['body']);
            }
            if ($sent) {
                if (!$dry) {
                    pbj_save_user_billing($pdo, $uid, [
                        'trial_reminder_end_sent_at' => date('c'),
                        'updated_at' => date('c'),
                    ]);
                }
                $out['end']++;
                $out['details'][] = [
                    'user_id' => $uid,
                    'email' => $email,
                    'kind' => 'end',
                    'ok' => true,
                    'dry_run' => $dry,
                    'ends' => $endsDay,
                ];
                error_log('trial reminder end user#' . $uid . ' to ' . $email . ($dry ? ' (dry)' : ''));
            } else {
                $out['errors']++;
                $out['details'][] = ['user_id' => $uid, 'email' => $email, 'kind' => 'end', 'ok' => false, 'error' => 'send_failed'];
                error_log('trial reminder end FAILED user#' . $uid);
            }
            continue; // don't also send "soon" on end day
        }

        // —— Soon: within N days before end (inclusive of day N down to 1) ——
        if ($daysLeft >= 1 && $daysLeft <= $soonWindow && !$soonSent) {
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out['skipped']++;
                $out['details'][] = ['user_id' => $uid, 'kind' => 'soon', 'ok' => false, 'error' => 'no_email'];
                continue;
            }
            // Only while trial still running
            if ($status === 'trial_expired' || $daysLeft < 1) {
                $out['skipped']++;
                continue;
            }
            $copy = pbj_trial_reminder_email_copy('soon', $name, $endsLabel, $daysLeft, $planName);
            $sent = false;
            if ($dry) {
                $sent = true;
            } elseif (function_exists('pbj_send_mail')) {
                $sent = pbj_send_mail($email, $copy['subject'], $copy['body']);
            }
            if ($sent) {
                if (!$dry) {
                    pbj_save_user_billing($pdo, $uid, [
                        'trial_reminder_soon_sent_at' => date('c'),
                        'updated_at' => date('c'),
                    ]);
                }
                $out['soon']++;
                $out['details'][] = [
                    'user_id' => $uid,
                    'email' => $email,
                    'kind' => 'soon',
                    'ok' => true,
                    'dry_run' => $dry,
                    'days_left' => $daysLeft,
                    'ends' => $endsDay,
                ];
                error_log('trial reminder soon user#' . $uid . ' days_left=' . $daysLeft . ($dry ? ' (dry)' : ''));
            } else {
                $out['errors']++;
                $out['details'][] = ['user_id' => $uid, 'email' => $email, 'kind' => 'soon', 'ok' => false, 'error' => 'send_failed'];
                error_log('trial reminder soon FAILED user#' . $uid);
            }
            continue;
        }

        $out['skipped']++;
    }

    return $out;
}

/**
 * Billing + plan context for the logged-in user (owner path preferred).
 * @return array{
 *   user_id:int,can_manage:bool,plan_id:string,plan:?array,restaurant_id:int,restaurant_name:string,
 *   seats:array,billing:array,upgrade_options:list,is_owner:bool,role:string
 * }
 */
function pbj_billing_context(PDO $pdo, int $userId): array {
    $role = (string) ($_SESSION['role'] ?? '');
    $houses = pbj_user_restaurants($pdo, $userId);
    $restaurantId = 0;
    $restaurantName = '';
    $membershipRole = $role;
    $isOwner = false;
    $playgroundOnly = false;

    // Prefer a real (non-playground) restaurant this user owns
    try {
        $stmt = $pdo->prepare('SELECT id, name FROM restaurants WHERE owner_id = ? ORDER BY id DESC');
        $stmt->execute([$userId]);
        while ($owned = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $oid = (int) ($owned['id'] ?? 0);
            if ($oid > 0 && !pbj_restaurant_is_playground($pdo, $oid)) {
                $restaurantId = $oid;
                $restaurantName = (string) ($owned['name'] ?? '');
                $isOwner = true;
                $membershipRole = 'owner';
                break;
            }
        }
    } catch (Throwable $e) {
        // ignore
    }

    // Else prefer membership on a real house (staff on someone's paid kitchen)
    if ($restaurantId <= 0 && $houses) {
        foreach ($houses as $h) {
            $hid = (int) ($h['id'] ?? 0);
            if ($hid <= 0 || pbj_restaurant_is_playground($pdo, $hid)) {
                continue;
            }
            $restaurantId = $hid;
            $restaurantName = (string) ($h['name'] ?? '');
            $membershipRole = (string) ($h['membership_role'] ?? $role);
            $isOwner = ($membershipRole === 'owner')
                || ((int) ($h['owner_id'] ?? 0) === $userId);
            break;
        }
    }

    // Only playground seats (demo / sales) → treat as no billable house so they can convert
    if ($restaurantId <= 0) {
        $hasPlayground = false;
        foreach ($houses as $h) {
            $hid = (int) ($h['id'] ?? 0);
            if ($hid > 0 && pbj_restaurant_is_playground($pdo, $hid)) {
                $hasPlayground = true;
                break;
            }
        }
        if ($hasPlayground) {
            $playgroundOnly = true;
        }
    }

    $userBilling = pbj_load_user_billing($pdo, $userId);
    $restBilling = [];
    $planId = (string) ($userBilling['plan_id'] ?? $userBilling['plan'] ?? '');

    if ($restaurantId > 0) {
        $planId = pbj_restaurant_plan_id($pdo, $restaurantId) ?: $planId;
        try {
            $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
            $stmt->execute([$restaurantId]);
            $raw = $stmt->fetchColumn();
            if ($raw) {
                $settings = json_decode((string) $raw, true);
                if (is_array($settings)) {
                    if (!empty($settings['plan'])) {
                        $planId = (string) $settings['plan'];
                    }
                    if (is_array($settings['billing'] ?? null)) {
                        $restBilling = $settings['billing'];
                    }
                    if (!empty($settings['billing_status']) && empty($restBilling['status'])) {
                        $restBilling['status'] = $settings['billing_status'];
                    }
                }
            }
        } catch (Throwable $e) {
            // ignore
        }
    }

    if ($planId === '' || !pbj_plan_by_id($planId)) {
        // Solo / playground-only → individual; house without plan → default crew
        $planId = $restaurantId > 0 ? pbj_default_plan_id() : 'individual';
    }
    // Playground trial seats should not inherit the playground’s unlimited plan for billing UI
    if ($playgroundOnly && $restaurantId <= 0) {
        $planId = 'individual';
    }

    $billing = array_merge($userBilling, $restBilling);
    // Prefer restaurant stripe ids when present
    if (!empty($restBilling['stripe_subscription_id'])) {
        $billing['stripe_subscription_id'] = $restBilling['stripe_subscription_id'];
    }
    if (!empty($restBilling['stripe_customer_id'])) {
        $billing['stripe_customer_id'] = $restBilling['stripe_customer_id'];
    }

    $canManage = $isOwner
        || in_array($membershipRole, ['owner', 'admin', 'gm'], true)
        || in_array($role, ['owner', 'admin', 'gm'], true)
        || pbj_is_platform_admin((string) ($_SESSION['email'] ?? ''));

    // Solo individual, or playground-only guests converting to a paid house
    if ($restaurantId <= 0 && $userId > 0) {
        $canManage = true;
    }

    $seats = $restaurantId > 0
        ? pbj_restaurant_seat_status($pdo, $restaurantId)
        : [
            'ok' => false,
            'reason' => 'invites_disabled',
            'count' => 1,
            'max' => 1,
            'plan_id' => $planId,
            'allows_invites' => false,
            'remaining' => 0,
        ];

    return [
        'user_id' => $userId,
        'can_manage' => $canManage,
        'plan_id' => $planId,
        'plan' => pbj_plan_by_id($planId),
        'restaurant_id' => $restaurantId,
        'restaurant_name' => $restaurantName,
        'seats' => $seats,
        'billing' => $billing,
        'upgrade_options' => pbj_upgrade_plan_options($planId),
        'is_owner' => $isOwner || $restaurantId <= 0,
        'role' => $membershipRole ?: $role,
        'playground_only' => $playgroundOnly,
    ];
}

function pbj_generate_invite_code(PDO $pdo): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            if ($i === 4) {
                $code .= '-';
            }
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ? LIMIT 1');
        $stmt->execute([strtoupper($code)]);
        if (!$stmt->fetchColumn()) {
            return strtoupper($code);
        }
    }
    return strtoupper(bin2hex(random_bytes(4)));
}

function pbj_find_restaurant_by_code(PDO $pdo, string $code): ?array {
    $code = strtoupper(trim($code));
    $code = preg_replace('/\s+/', '', $code);
    if ($code === '') {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM restaurants WHERE UPPER(REPLACE(invite_code, " ", "")) = ? LIMIT 1');
    $stmt->execute([$code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function pbj_user_restaurants(PDO $pdo, int $userId): array {
    if ($userId <= 0) {
        return [];
    }
    $stmt = $pdo->prepare(
        'SELECT r.*, ur.role AS membership_role
         FROM user_restaurant ur
         JOIN restaurants r ON r.id = ur.restaurant_id
         WHERE ur.user_id = ?
         ORDER BY ur.joined_at ASC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function pbj_set_session_user(array $user): void {
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'] ?? '';
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['role'] = ($user['role'] ?? 'foh') === 'admin' ? 'gm' : ($user['role'] ?? 'foh');
    $_SESSION['theme'] = $user['theme'] ?? 'sweet';
    $_SESSION['theme_chosen'] = isset($user['theme_chosen']) ? (int) $user['theme_chosen'] : 0;
    $_SESSION['access_status'] = $user['access_status'] ?? 'pending';
    $_SESSION['access_checked_at'] = time();
    $name = $user['full_name'] ?? $user['username'] ?? 'friend';
    $_SESSION['user_name'] = $name;
    $_SESSION['name'] = $name;
    // Platform admins are always treated as approved in-session
    if (pbj_is_platform_admin($_SESSION['email'])) {
        $_SESSION['access_status'] = 'approved';
    }
}

/** Whether the user has completed the first-login theme picker. */
function pbj_user_has_chosen_theme(): bool {
    if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
        return true;
    }
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        return true;
    }
    if (!empty($_SESSION['theme_chosen'])) {
        return true;
    }
    return false;
}

/**
 * Available + coming-soon themes for pickers.
 * preview = optional hero/icon image shown on Look & Feel / first-run picker.
 *
 * @return list<array<string,mixed>>
 */
function pbj_theme_catalog(): array {
    return [
        [
            'id' => 'sweet',
            'name' => 'Sweet PBJ Vibes',
            'emoji' => '🍓',
            'tagline' => 'Pink headers, dreamy fonts, full PB&J charm',
            'available' => true,
            'dark' => false,
            'swatches' => ['#E55163', '#FCF8EE', '#BBE7DA', '#6B4A8C'],
            'preview' => 'pbj-home-sticker.jpg',
        ],
        [
            'id' => 'basic',
            'name' => 'Sleek Simple Style',
            'emoji' => '✨',
            'tagline' => 'Clean navy, calm type, easy reading',
            'available' => true,
            'dark' => false,
            'swatches' => ['#1A2A44', '#F1EBE4', '#C5D0DE', '#EEF2F8'],
            'preview' => 'basic-home-icon.png',
        ],
        [
            'id' => 'neon_diner',
            'name' => 'Neon 50s Diner',
            'emoji' => '🍔',
            'tagline' => 'Dark mode · hot pink & cyan neon that glows on black',
            'available' => true,
            'dark' => true,
            'swatches' => ['#0A0A0A', '#FF2ECB', '#00F0FF', '#FFFFFF'],
            'preview' => 'assets/themes/neon_diner.jpg',
        ],
        [
            'id' => 'farm',
            'name' => 'Farm-to-Table',
            'emoji' => '🌿',
            'tagline' => 'Tomato red, carrot, basil green on kraft cream',
            'available' => true,
            'dark' => false,
            'swatches' => ['#2F6B3A', '#C43B2C', '#E07A2F', '#F3E6D4'],
            'preview' => 'assets/themes/farm.jpg',
        ],
        [
            'id' => 'coffee',
            'name' => 'Coffee Shop Cozy',
            'emoji' => '☕',
            'tagline' => 'Espresso, latte foam, pastry gold, warm wood',
            'available' => true,
            'dark' => false,
            'swatches' => ['#4A2C1A', '#C4A484', '#F5EDE3', '#8B5A2B'],
            'preview' => 'assets/themes/coffee.jpg',
        ],
        [
            'id' => 'urban',
            'name' => 'Modern Urban Edge',
            'emoji' => '🏙️',
            'tagline' => 'Dark mode · late-night black, white, electric orange',
            'available' => true,
            'dark' => true,
            'swatches' => ['#000000', '#FFFFFF', '#FF2D00', '#1A1A1A'],
            'preview' => 'assets/themes/urban.jpg',
        ],
    ];
}

/** Theme ids that are live (selectable). */
function pbj_available_theme_ids(): array {
    $ids = [];
    foreach (pbj_theme_catalog() as $t) {
        if (!empty($t['available'])) {
            $ids[] = $t['id'];
        }
    }
    return $ids;
}

function pbj_normalize_theme_id(?string $theme): string {
    $theme = strtolower(trim((string) $theme));
    $allowed = pbj_available_theme_ids();
    // Also accept known coming-soon ids if already stored (future-proof)
    $known = array_column(pbj_theme_catalog(), 'id');
    if (in_array($theme, $allowed, true)) {
        return $theme;
    }
    if (in_array($theme, $known, true)) {
        // Stored but not available yet → fall back to sweet
        return 'sweet';
    }
    return 'sweet';
}

/** Current theme id from session. */
function pbj_theme_id(): string {
    return pbj_normalize_theme_id($_SESSION['theme'] ?? 'sweet');
}

/** Fun hub names (Showtime / The Heat / …) vs plain FOH/BOH. */
function pbj_use_fun_names(): bool {
    $id = pbj_theme_id();
    return in_array($id, ['sweet', 'neon_diner', 'farm', 'urban', 'coffee'], true);
}

/**
 * Theme-aware hub display names (nav, titles, back links).
 * Keys: home, foh, boh, admin, messages, settings
 *
 * @return array{home:string,foh:string,boh:string,admin:string,messages:string,settings:string}
 */
function pbj_hub_labels(?string $themeId = null): array {
    $id = pbj_normalize_theme_id($themeId ?? pbj_theme_id());

    if ($id === 'neon_diner') {
        return [
            'home' => 'The Hop',
            'foh' => 'The Diner',
            'boh' => 'The Galley',
            'admin' => 'The Mill',
            'messages' => 'The Yak',
            'settings' => 'The Dashboard',
        ];
    }

    if ($id === 'farm') {
        return [
            'home' => 'Homestead',
            'foh' => 'Porch',
            'boh' => 'Kitchen',
            'admin' => 'Barn',
            'messages' => 'Coop',
            'settings' => 'Toolshed',
        ];
    }

    if ($id === 'urban') {
        return [
            'home' => 'Base',
            'foh' => 'Floor',
            'boh' => 'Line',
            'admin' => 'HQ',
            'messages' => 'Wire',
            'settings' => 'System',
        ];
    }

    if ($id === 'coffee') {
        return [
            'home' => 'Brew',
            'foh' => 'Counter',
            'boh' => 'Roast',
            'admin' => 'Ledger',
            'messages' => 'Pinboard',
            'settings' => 'Steam',
        ];
    }

    if ($id === 'sweet') {
        return [
            'home' => 'PB&J Hub',
            'foh' => 'Showtime',
            'boh' => 'The Heat',
            'admin' => 'Sandwich HQ',
            'messages' => 'Jelly Jar',
            'settings' => 'Whiskings',
        ];
    }

    // basic (+ any future non-fun theme)
    return [
        'home' => 'Home',
        'foh' => 'FOH',
        'boh' => 'BOH',
        'admin' => 'Admin',
        'messages' => 'Messages',
        'settings' => 'Settings',
    ];
}

/** Single hub label by key (home|foh|boh|admin|messages|settings). */
function pbj_hub_label(string $key, ?string $themeId = null): string {
    $labels = pbj_hub_labels($themeId);
    return $labels[$key] ?? $key;
}

/** “Back to {hub}” helper. */
function pbj_back_to_hub(string $key, ?string $themeId = null): string {
    return 'Back to ' . pbj_hub_label($key, $themeId);
}

/**
 * Pretty public path for a hub (root-absolute).
 * home → /home · foh → /FOH · boh → /BOH · settings → /settings
 * admin/messages keep script names for now.
 *
 * @param string $key Hub key (home|foh|boh|admin|messages|settings) or legacy filename
 * @param string $query Optional query without leading ?
 */
function pbj_hub_href(string $key, string $query = ''): string {
    $raw = strtolower(trim(str_replace('\\', '/', $key)));
    $raw = basename($raw);
    $map = [
        'home' => '/home',
        'dashboard' => '/home',
        'dashboard.php' => '/home',
        'foh' => '/FOH',
        'showtime' => '/FOH',
        'showtime.php' => '/FOH',
        'boh' => '/BOH',
        'the-heat' => '/BOH',
        'the-heat.php' => '/BOH',
        'settings' => '/settings',
        'whiskings' => '/settings',
        'whiskings.php' => '/settings',
        'admin' => '/admin',
        'admin.php' => '/admin',
        'messages' => '/messages',
        'messages.php' => '/messages',
    ];
    $path = $map[$raw] ?? null;
    if ($path === null) {
        // Unknown key — return as root-absolute path if it looks like one
        if (str_starts_with($key, '/')) {
            $path = $key;
        } else {
            $path = '/' . ltrim($key, '/');
        }
    }
    $query = ltrim($query, "? \t");
    if ($query !== '') {
        return $path . '?' . $query;
    }
    return $path;
}

/**
 * Phase-1 pretty routes for FOH / BOH / Settings first-level cards.
 * script basename => public path
 *
 * @return array<string,string>
 */
function pbj_page_routes(): array {
    static $routes = null;
    if ($routes !== null) {
        return $routes;
    }
    $routes = [
        // Hubs (also in pbj_hub_href)
        'dashboard.php' => '/home',
        'showtime.php' => '/FOH',
        'the-heat.php' => '/BOH',
        'whiskings.php' => '/settings',
        // FOH cards
        'showtime-opening-closing.php' => '/FOH/opening-closing',
        'showtime-server-sidework.php' => '/FOH/sidework',
        'showtime-bar.php' => '/FOH/bar',
        'showtime-floor-plan.php' => '/FOH/floor-plan',
        'showtime-reservations.php' => '/FOH/reservations',
        'showtime-pos.php' => '/FOH/pos',
        // BOH cards
        'the-heat-prep.php' => '/BOH/prep',
        'the-heat-opening-closing.php' => '/BOH/opening-closing',
        'the-heat-recipes-hub.php' => '/BOH/recipes',
        'the-heat-cleaning.php' => '/BOH/cleaning',
        'the-heat-temps.php' => '/BOH/temps',
        'the-heat-tools.php' => '/BOH/tools',
        // BOH deeper (Menu & recipes cluster)
        'the-heat-menu.php' => '/BOH/menu',
        'the-heat-recipes.php' => '/BOH/recipe-cards',
        'the-heat-yields.php' => '/BOH/yields',
        'the-heat-86.php' => '/BOH/86',
        'the-heat-86-display.php' => '/BOH/86/display',
        'the-heat-allergens.php' => '/BOH/allergens',
        // Settings cards
        'whiskings-theme.php' => '/settings/theme',
        'whiskings-profile.php' => '/settings/profile',
        'whiskings-notifications.php' => '/settings/notifications',
        'whiskings-shift.php' => '/settings/shift',
        'whiskings-help.php' => '/settings/help',
        'whiskings-account.php' => '/settings/account',
        // Auth / onboarding / remaining hubs
        'admin.php' => '/admin',
        'messages.php' => '/messages',
        'login.php' => '/login',
        'register.php' => '/register',
        'join.php' => '/join',
        'logout.php' => '/logout',
        'waiting.php' => '/waiting',
        'choose-theme.php' => '/choose-theme',
        'forgot-password.php' => '/forgot-password',
        'reset-password.php' => '/reset-password',
        'privacy.php' => '/privacy',
        'terms.php' => '/terms',
        'refunds.php' => '/refunds',
        'approve-users.php' => '/approve-users',
        'index.php' => '/',
        // Messages (Jelly) children
        'jelly-announcements.php' => '/messages/announcements',
        'jelly-broadcasts.php' => '/messages/broadcasts',
        'jelly-shift-notes.php' => '/messages/shift-notes',
        'jelly-dms.php' => '/messages/dms',
        'jelly-foh.php' => '/messages/foh',
        'jelly-boh.php' => '/messages/boh',
        // Admin children
        'admin-team.php' => '/admin/team',
        'admin-ops.php' => '/admin/ops',
        'admin-catering.php' => '/admin/catering',
        'catering.php' => '/catering',
        'catering-inquire.php' => '/catering/inquire',
        'admin-schedules.php' => '/admin/schedules',
        'my-schedule.php' => '/schedule',
        'admin-reports.php' => '/admin/reports',
        'admin-inventory-vendors.php' => '/admin/inventory',
        'admin-compliance.php' => '/admin/compliance',
        'admin-team-roster.php' => '/admin/roster',
        'admin-employee-spotlight.php' => '/admin/spotlight',
        'admin-onboarding.php' => '/admin/onboarding',
        'admin-doc.php' => '/admin/docs',
        'admin-settings.php' => '/admin/settings',
        'admin-sales.php' => '/admin/sales',
        'admin-labor.php' => '/admin/labor',
        'admin-cash.php' => '/admin/cash',
        'admin-trends.php' => '/admin/trends',
        'admin-pnl.php' => '/admin/pnl',
        'admin-comps.php' => '/admin/comps',
        'admin-costing.php' => '/admin/costing',
        'admin-inventory.php' => '/admin/product-setup',
        'admin-inventory-count.php' => '/admin/count',
        'admin-auto-order.php' => '/admin/auto-order',
        'admin-checklist-overview.php' => '/admin/checklist-overview',
        'admin-pos-import.php' => '/admin/pos-import',
        'admin-pos-connect.php' => '/admin/pos-connect',
        'admin-house-setup.php' => '/admin/setup',
        'pos-oauth-start.php' => '/pos/oauth/start',
        'pos-oauth-callback.php' => '/pos/oauth/callback',
        'admin-product-list.php' => '/admin/product-list',
        'admin-vendors.php' => '/admin/vendors',
        'admin-order-guides.php' => '/admin/order-guides',
        'admin-invoices.php' => '/admin/invoices',
        'admin-waste.php' => '/admin/waste',
        'admin-pmix.php' => '/admin/pmix',
    ];
    return $routes;
}

/**
 * Pretty public path for any known app page (script name or alias).
 * Unknown scripts become root-absolute /filename.php.
 */
function pbj_page_href(string $id, string $query = ''): string {
    $raw = trim(str_replace('\\', '/', $id));
    $base = strtolower(basename($raw));
    if (!str_ends_with($base, '.php') && !str_contains($base, '/')) {
        // allow short aliases: "prep" alone is ambiguous — prefer full script or path
        $try = $base . '.php';
        $routes = pbj_page_routes();
        if (isset($routes[$try])) {
            $base = $try;
        }
    }
    $routes = pbj_page_routes();
    $path = $routes[$base] ?? null;
    if ($path === null) {
        // already pretty?
        foreach ($routes as $pretty) {
            if ($raw === $pretty || $raw === ltrim($pretty, '/')) {
                $path = $pretty;
                break;
            }
        }
    }
    if ($path === null) {
        if (str_starts_with($raw, '/')) {
            $path = $raw;
        } else {
            $path = '/' . ltrim($raw, '/');
        }
    }
    $query = ltrim($query, "? \t");
    if ($query !== '') {
        return $path . '?' . $query;
    }
    return $path;
}

/**
 * Normalize a post-login / post-theme redirect to a safe internal path.
 * Maps legacy hub / card PHP filenames to pretty URLs.
 */
function pbj_normalize_app_redirect(string $url, string $fallback = '/home'): string {
    $url = trim($url);
    if ($url === '' || strpos($url, '://') !== false || str_starts_with($url, '//')) {
        return $fallback;
    }
    // Allow root-absolute or relative
    $parts = parse_url($url);
    if ($parts === false) {
        return $fallback;
    }
    $path = $parts['path'] ?? '';
    $query = isset($parts['query']) ? (string) $parts['query'] : '';
    $base = strtolower(basename($path));
    $routes = pbj_page_routes();
    if (isset($routes[$base])) {
        $pretty = $routes[$base];
        return $query !== '' ? $pretty . '?' . $query : $pretty;
    }
    // Hubs by short name
    $hubShort = [
        'home' => '/home',
        'foh' => '/FOH',
        'boh' => '/BOH',
        'settings' => '/settings',
    ];
    if (isset($hubShort[$base])) {
        return $query !== '' ? $hubShort[$base] . '?' . $query : $hubShort[$base];
    }
    // Already pretty or other internal page
    if ($path === '' || $path === '/') {
        return $fallback;
    }
    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }
    // Block path traversal
    if (str_contains($path, '..')) {
        return $fallback;
    }
    return $query !== '' ? $path . '?' . $query : $path;
}

/**
 * Browser tab + PWA icons (PB&J hub image).
 * Safe to call from any page &lt;head&gt; — only prints once.
 */
function pbj_render_favicon_links(): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    // Cache-bust when icons are regenerated from pbj-home-sticker.jpg
    $v = '3';
    echo '<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=' . $v . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16.png?v=' . $v . '">' . "\n";
    echo '<link rel="icon" type="image/png" href="/favicon.png?v=' . $v . '">' . "\n";
    echo '<link rel="apple-touch-icon" href="/icon-180.png?v=' . $v . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png?v=' . $v . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="512x512" href="/icon-512.png?v=' . $v . '">' . "\n";
}

/** Emit <base href="/"> once so multi-segment pretty URLs resolve assets/links from site root. */
function pbj_render_base_href(): void {
    static $done = false;
    if (!$done) {
        $done = true;
        if (!empty($_SESSION['user_id']) || (defined('AUTH_BYPASS') && AUTH_BYPASS)) {
            echo '<base href="/">' . "\n";
        }
    }
    // Always attach hub icons when base helper is used (app pages)
    if (function_exists('pbj_render_favicon_links')) {
        pbj_render_favicon_links();
    }
}

/**
 * Icon path for a slot, theme-aware.
 * Slot examples: "nav/home", "showtime/pos", "jelly/announcements", "pos-hub/ringins"
 * Sweet keeps detailed PB&J stickers; basic keeps basic nav icons / null for hub cards;
 * neon/coffee/farm/urban use emoji-style SVG sets under assets/icons/{theme}/.
 * Neon uses detailed 50s-shine nav art for all nav slots (dashboard + bottom nav).
 */
/**
 * Append ?v=filemtime so sticker/icon refreshes bust browser cache.
 */
function pbj_asset_url(string $path): string {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if ($path === '') {
        return '';
    }
    $full = __DIR__ . '/' . $path;
    if (is_file($full)) {
        return $path . '?v=' . (string)filemtime($full);
    }
    return $path;
}

function pbj_icon(string $slot, ?string $themeId = null, bool $forDashboard = false): string {
    $slot = trim(str_replace('\\', '/', $slot), '/');
    $id = pbj_normalize_theme_id($themeId ?? pbj_theme_id());
    $path = '';

    // Detailed theme nav art (dashboard + bottom nav) when present
    if (str_starts_with($slot, 'nav/') && in_array($id, ['neon_diner', 'farm', 'urban', 'coffee'], true)) {
        $key = substr($slot, 4); // home, foh, …
        $dash = 'assets/icons/' . $id . '/dashboard/' . $key . '.jpg';
        if (is_file(__DIR__ . '/' . $dash)) {
            $path = $dash;
        }
    }

    // Sweet PBJ sticker pack (detailed art)
    if ($path === '' && $id === 'sweet') {
        static $sweetNav = [
            'nav/home' => 'pbj-home-sticker.jpg',
            'nav/foh' => 'pbj-foh-sticker.jpg',
            'nav/boh' => 'pbj-boh-sticker.jpg',
            'nav/admin' => 'pbj-admin-sticker.jpg',
            'nav/messages' => 'pbj-messages-sticker.jpg',
            'nav/settings' => 'pbj-settings-sticker.jpg',
        ];
        if (isset($sweetNav[$slot])) {
            $path = $sweetNav[$slot];
        } else {
            // Hub stickers already live under assets/{folder}/{name}.jpg
            $jpg = 'assets/' . $slot . '.jpg';
            if (is_file(__DIR__ . '/' . $jpg)) {
                $path = $jpg;
            }
        }
    }

    // Basic nav icons
    if ($path === '' && $id === 'basic') {
        static $basicNav = [
            'nav/home' => 'basic-home-icon.png',
            'nav/foh' => 'basic-foh-icon.jpg',
            'nav/boh' => 'basic-boh-icon.png',
            'nav/admin' => 'basic-admin-icon.jpg',
            'nav/messages' => 'basic-messages-icon.png',
            'nav/settings' => 'basic-settings-icon.png',
        ];
        if (isset($basicNav[$slot])) {
            $path = $basicNav[$slot];
        } else {
            // No dedicated hub pack — empty string lets pages use emoji fallback
            return '';
        }
    }

    // Neon / Coffee / Farm / Urban emoji-style SVGs
    if ($path === '') {
        $svg = 'assets/icons/' . $id . '/' . $slot . '.svg';
        if (is_file(__DIR__ . '/' . $svg)) {
            $path = $svg;
        }
    }

    // Coming-soon themes that fell back to sweet normalize still shouldn't use PBJ —
    // try neon set as a safe non-PBJ default for dark themes
    if ($path === '') {
        $fallback = 'assets/icons/neon_diner/' . $slot . '.svg';
        if (is_file(__DIR__ . '/' . $fallback)) {
            $path = $fallback;
        }
    }

    if ($path === '') {
        return '';
    }
    return pbj_asset_url($path);
}

/** Whether the active theme uses image icons for hub cards (vs plain emoji). */
function pbj_uses_hub_icons(?string $themeId = null): bool {
    $id = pbj_normalize_theme_id($themeId ?? pbj_theme_id());
    return in_array($id, ['sweet', 'neon_diner', 'coffee', 'farm', 'urban'], true);
}

/** CSS class for the icon wrapper. */
function pbj_icon_wrap_class(?string $themeId = null): string {
    $id = pbj_normalize_theme_id($themeId ?? pbj_theme_id());
    if ($id === 'sweet') {
        return 'card-icon sweet-sticker';
    }
    if ($id === 'neon_diner') {
        return 'card-icon theme-icon theme-icon-neon';
    }
    if (in_array($id, ['coffee', 'farm', 'urban'], true)) {
        return 'card-icon theme-icon theme-icon-' . $id;
    }
    return 'card-icon';
}

/**
 * Render a hub card icon (img when available, else emoji fallback).
 */
function pbj_render_card_icon(string $slot, string $alt = '', string $emojiFallback = '•'): void {
    $src = pbj_icon($slot);
    $alt = $alt !== '' ? $alt : $slot;
    if ($src !== '') {
        $cls = pbj_icon_wrap_class();
        echo '<div class="' . htmlspecialchars($cls) . '">';
        echo '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '">';
        echo '</div>';
        return;
    }
    echo '<div class="card-icon">' . $emojiFallback . '</div>';
}

/**
 * Uniform hub card grid/tiles for FOH, BOH, Admin, Messages, Settings.
 * Call on hub pages with body.hub-page.
 */
function pbj_render_hub_card_css(): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    echo <<<'CSS'
<style id="pbj-hub-cards">
/* —— Shared hub layout —— */
body.hub-page .content {
  max-width: 900px;
  margin: 0 auto;
  padding: 24px 14px 40px;
  box-sizing: border-box;
}
body.hub-page .hub-intro,
body.hub-page .intro.hub-intro,
body.hub-page .tip.hub-intro {
  background: white;
  border-radius: 18px;
  padding: 14px 18px;
  margin: 0 0 14px;
  line-height: 1.45;
  text-align: center;
  font-size: 0.95rem;
  opacity: 0.95;
  box-shadow: 0 5px 15px rgba(0,0,0,0.08);
  box-sizing: border-box;
}
/* Dark themes: theme paint may recolor; keep a readable fallback */
body.hub-page.pbj-theme-neon_diner .hub-intro,
body.hub-page.pbj-theme-urban .hub-intro,
html.pbj-theme-neon_diner body.hub-page .hub-intro,
html.pbj-theme-urban body.hub-page .hub-intro {
  background: #141414;
  color: #F5F5F7;
  border: 1px solid rgba(255,46,203,0.35);
  box-shadow: 0 0 18px rgba(0,0,0,0.35);
}
body.hub-page .hub-reorder-hint {
  text-align: center;
  font-size: 0.85rem;
  opacity: 0.65;
  margin: 0 0 12px;
}
body.hub-page .section-label {
  font-size: 0.95rem;
  opacity: 0.65;
  margin: 16px 4px 10px;
}
body.hub-page .grid,
body.hub-page .hub-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  align-items: stretch;
}
@media (max-width: 520px) {
  body.hub-page .grid,
  body.hub-page .hub-grid {
    grid-template-columns: 1fr;
  }
}
/* —— Equal card tiles (compact) —— */
body.hub-page .card {
  display: flex !important;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  height: 100%;
  min-height: 132px;
  padding: 12px 10px 10px !important;
  border-radius: 14px !important;
  box-sizing: border-box !important;
  text-align: center;
  text-decoration: none;
  color: inherit;
}
body.hub-page .card h3 {
  font-size: 1.02rem !important;
  margin: 6px 0 4px !important;
  line-height: 1.2;
  min-height: 2.1em;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}
body.hub-page .card p {
  margin: 0 !important;
  font-size: 0.78rem !important;
  opacity: 0.75;
  line-height: 1.3;
  min-height: 2.2em;
  flex: 1 1 auto;
  width: 100%;
}
/* One compact icon size on every hub */
body.hub-page .card .card-icon,
body.hub-page .card .card-icon.sweet-sticker,
body.hub-page .card .card-icon.theme-icon,
body.hub-page .card .card-icon.theme-icon-neon,
body.hub-page .card .card-icon.theme-icon-farm,
body.hub-page .card .card-icon.theme-icon-urban,
body.hub-page .card .card-icon.theme-icon-coffee {
  width: 48px !important;
  height: 48px !important;
  min-width: 48px !important;
  min-height: 48px !important;
  max-width: 48px !important;
  max-height: 48px !important;
  border-radius: 12px !important;
  margin: 0 auto 6px !important;
  padding: 3px !important;
  box-sizing: border-box !important;
  overflow: hidden !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex-shrink: 0;
}
body.hub-page .card .card-icon img {
  width: 100% !important;
  height: 100% !important;
  max-width: 100% !important;
  max-height: 100% !important;
  object-fit: contain !important;
  display: block !important;
  transform: none !important;
  border-radius: 10px;
  margin: 0 !important;
}
/* Messages count pills — reserve space so tiles stay even */
body.hub-page .card .count-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-top: 6px;
  min-height: 1.4em;
  font-size: 0.72rem;
  border-radius: 999px;
  padding: 3px 10px;
  flex-shrink: 0;
}
/* Stats row on Messages (optional chrome; doesn’t affect card size) */
body.hub-page .stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-bottom: 14px;
}
@media (max-width: 520px) {
  body.hub-page .stats-row { grid-template-columns: 1fr; }
}
</style>
CSS;
}

/** Shared CSS so theme emoji tiles size like sweet stickers. */
function pbj_render_icon_css(): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    echo <<<'CSS'
<style id="pbj-theme-icons">
.card-icon.theme-icon,
.card-icon.theme-icon-neon,
.card-icon.theme-icon-coffee,
.card-icon.theme-icon-farm,
.card-icon.theme-icon-urban,
.cat-icon.theme-icon,
.map-icon.theme-icon {
  width: 64px !important;
  height: 64px !important;
  border-radius: 16px !important;
  padding: 4px !important;
  overflow: hidden;
  box-sizing: border-box;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
}
.card-icon.theme-icon img,
.card-icon.theme-icon-neon img,
.card-icon.theme-icon-coffee img,
.card-icon.theme-icon-farm img,
.card-icon.theme-icon-urban img,
.cat-icon.theme-icon img,
.map-icon.theme-icon img {
  width: 100% !important;
  height: 100% !important;
  object-fit: contain !important;
  display: block;
  border-radius: 12px;
}
/* Nav tiles crop SVG tiles cleanly; keep labels centered, no wrap */
.nav-item img,
.nav-item-bottom img {
  object-fit: cover;
  background: transparent;
}
.nav-item,
.nav-item-bottom {
  text-align: center !important;
  align-items: center !important;
}
.nav-item span,
.nav-item-bottom span {
  display: block;
  width: 100%;
  text-align: center !important;
  line-height: 1.15;
  white-space: nowrap !important;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
CSS;
}

/** Paint tokens for the active (or given) theme. */
function pbj_theme_tokens(?string $themeId = null): array {
    $id = pbj_normalize_theme_id($themeId ?? pbj_theme_id());

    $base = [
        'id' => $id,
        'dark' => false,
        'fun_names' => false,
        'font_body' => "'Lora', Georgia, serif",
        'font_display' => "'Lora', Georgia, serif",
        'google_fonts' => 'https://fonts.googleapis.com/css2?family=Lora:wght@400;600&display=swap',
        'page_bg' => '#F1EBE4',
        'text' => '#1A2A44',
        'muted' => 'rgba(26,42,68,0.72)',
        'header_bg' => '#1A2A44',
        'header_text' => '#FFFFFF',
        'card_bg' => '#FFFFFF',
        'card_border' => '#C5D0DE',
        'primary' => '#1A2A44',
        'primary_text' => '#FFFFFF',
        'secondary_bg' => '#EEF2F8',
        'secondary_text' => '#1A2A44',
        'accent' => '#1A2A44',
        'accent2' => '#C5D0DE',
        'input_bg' => '#FAF8F5',
        'input_border' => '#C5D0DE',
        'nav_bg' => '#1A2A44',
        'nav_text' => '#BBE7DA',
        'nav_border' => '#BBE7DA',
        'danger_bg' => '#FDECEA',
        'danger_text' => '#B71C1C',
        'glow' => 'none',
    ];

    if ($id === 'sweet') {
        return array_merge($base, [
            'dark' => false,
            'fun_names' => true,
            'font_body' => "'DreamingOutLoudPro', Georgia, serif",
            'font_display' => "'ModernLoveCaps', Georgia, serif",
            'google_fonts' => null,
            'page_bg' => '#FCF8EE',
            'text' => '#3a2f1f',
            'muted' => 'rgba(58,47,31,0.72)',
            'header_bg' => '#E55163',
            'header_text' => '#FFFFFF',
            'card_bg' => '#FFFFFF',
            'card_border' => '#F3C5CC',
            'primary' => '#E55163',
            'primary_text' => '#FFFFFF',
            'secondary_bg' => '#FFF5F6',
            'secondary_text' => '#3a2f1f',
            'accent' => '#6B4A8C',
            'accent2' => '#BBE7DA',
            'input_bg' => '#FFFBF8',
            'input_border' => '#F3C5CC',
            'nav_bg' => '#6B4A8C',
            'nav_text' => '#BBE7DA',
            'nav_border' => '#BBE7DA',
        ]);
    }

    if ($id === 'neon_diner') {
        return array_merge($base, [
            'dark' => true,
            'fun_names' => true,
            // Local fonts from /Fonts (Warnes = headlines, Mouse Memoirs = body)
            'font_body' => "'Mouse Memoirs', 'MouseMemoirs', Georgia, sans-serif",
            'font_display' => "'Warnes', 'Warnes-Regular', system-ui, sans-serif",
            'google_fonts' => null,
            'page_bg' => '#0A0A0A',
            'text' => '#F5F5F7',
            'muted' => 'rgba(245,245,247,0.72)',
            'header_bg' => '#0D0D0D',
            'header_text' => '#FF2ECB',
            'card_bg' => '#141414',
            'card_border' => '#FF2ECB',
            'primary' => '#FF2ECB',
            'primary_text' => '#0A0A0A',
            'secondary_bg' => '#1A1A1A',
            'secondary_text' => '#00F0FF',
            'accent' => '#00F0FF',
            'accent2' => '#FF2ECB',
            // Pure black fields + bright type (page CSS often forces cream/white boxes)
            'input_bg' => '#000000',
            'input_border' => '#00F0FF',
            'nav_bg' => '#050505',
            'nav_text' => '#00F0FF',
            'nav_border' => '#FF2ECB',
            'danger_bg' => '#3A1018',
            'danger_text' => '#FF6B9D',
            'glow' => '0 0 18px rgba(255,46,203,0.45)',
        ]);
    }

    if ($id === 'farm') {
        return array_merge($base, [
            'dark' => false,
            'fun_names' => true,
            // Local fonts: Stylish Handwriting (headlines) + Notepen (body)
            'font_body' => "'Notepen', 'NotepenRegular', Georgia, serif",
            'font_display' => "'Stylish Handwriting Free', 'Stylish Handwriting', cursive",
            'google_fonts' => null,
            'page_bg' => '#F3E6D4',
            'text' => '#2C2416',
            'muted' => 'rgba(44,36,22,0.72)',
            'header_bg' => '#2F6B3A',
            'header_text' => '#FFF8EE',
            'card_bg' => '#FFFBF5',
            'card_border' => '#D9C7A8',
            'primary' => '#C43B2C',
            'primary_text' => '#FFF8EE',
            'secondary_bg' => '#EFE2CF',
            'secondary_text' => '#2F6B3A',
            'accent' => '#E07A2F',
            'accent2' => '#2F6B3A',
            'input_bg' => '#FFF9F1',
            'input_border' => '#D4C0A0',
            'nav_bg' => '#2F6B3A',
            'nav_text' => '#F3E6D4',
            'nav_border' => '#E07A2F',
            'danger_bg' => '#FDECEA',
            'danger_text' => '#9B1C1C',
            'glow' => '0 6px 18px rgba(47,107,58,0.18)',
        ]);
    }

    if ($id === 'urban') {
        return array_merge($base, [
            'dark' => true,
            'fun_names' => true,
            // Local: Rafika (stencil headlines) + Kelly Slab (body)
            'font_body' => "'Kelly Slab', 'KellySlab-Regular', Georgia, serif",
            'font_display' => "'Rafika', 'Rafika-Regular', Impact, sans-serif",
            'google_fonts' => null,
            'page_bg' => '#000000',
            'text' => '#F5F5F5',
            'muted' => 'rgba(245,245,245,0.68)',
            'header_bg' => '#0A0A0A',
            'header_text' => '#FFFFFF',
            'card_bg' => '#121212',
            'card_border' => '#2A2A2A',
            'primary' => '#FF2D00',
            'primary_text' => '#FFFFFF',
            'secondary_bg' => '#1A1A1A',
            'secondary_text' => '#FFFFFF',
            'accent' => '#FF2D00',
            'accent2' => '#FFFFFF',
            // Pure black fields + bright type for dark urban chrome
            'input_bg' => '#000000',
            'input_border' => '#FF2D00',
            'nav_bg' => '#050505',
            'nav_text' => '#FFFFFF',
            'nav_border' => '#FF2D00',
            'danger_bg' => '#3A1010',
            'danger_text' => '#FF6B4A',
            'glow' => '0 0 18px rgba(255,45,0,0.4)',
        ]);
    }

    if ($id === 'coffee') {
        return array_merge($base, [
            'dark' => false,
            'fun_names' => true,
            // Local: Coffee Town (headlines) + Ambery Garden (body)
            'font_body' => "'Ambery Garden', 'AmberyGarden-Regular', Georgia, serif",
            'font_display' => "'Coffee Town', 'CoffeeTown', Georgia, serif",
            'google_fonts' => null,
            'page_bg' => '#F5EDE3',
            'text' => '#3D2416',
            'muted' => 'rgba(61,36,22,0.72)',
            'header_bg' => '#4A2C1A',
            'header_text' => '#F5EDE3',
            'card_bg' => '#FFFAF5',
            'card_border' => '#E8D5C4',
            'primary' => '#8B5A2B',
            'primary_text' => '#FFFAF5',
            'secondary_bg' => '#EFE4D8',
            'secondary_text' => '#4A2C1A',
            'accent' => '#C4A484',
            'accent2' => '#D4A84B',
            'input_bg' => '#FFF9F3',
            'input_border' => '#DCC9B4',
            'nav_bg' => '#4A2C1A',
            'nav_text' => '#F5EDE3',
            'nav_border' => '#C4A484',
            'danger_bg' => '#FDECEA',
            'danger_text' => '#9B1C1C',
            'glow' => '0 6px 18px rgba(74,44,26,0.16)',
        ]);
    }

    // basic (default fallthrough)
    return array_merge($base, [
        'id' => 'basic',
        'fun_names' => false,
    ]);
}

/** Display name for the active theme id. */
function pbj_theme_display_name(?string $themeId = null): string {
    $themeId = pbj_normalize_theme_id($themeId ?? ($_SESSION['theme'] ?? 'sweet'));
    foreach (pbj_theme_catalog() as $t) {
        if ($t['id'] === $themeId) {
            return $t['name'];
        }
    }
    return 'Sweet PBJ Vibes';
}

/** Persist theme choice; marks first-login picker complete when $markChosen. */
function pbj_save_user_theme(PDO $pdo, int $userId, string $theme, bool $markChosen = true): string {
    $theme = pbj_normalize_theme_id($theme);
    // Only persist available themes
    if (!in_array($theme, pbj_available_theme_ids(), true)) {
        $theme = 'sweet';
    }
    if ($userId <= 0) {
        $_SESSION['theme'] = $theme;
        if ($markChosen) {
            $_SESSION['theme_chosen'] = 1;
        }
        return $theme;
    }
    try {
        if ($markChosen) {
            $stmt = $pdo->prepare('UPDATE users SET theme = ?, theme_chosen = 1 WHERE id = ?');
            $stmt->execute([$theme, $userId]);
            $_SESSION['theme_chosen'] = 1;
        } else {
            $stmt = $pdo->prepare('UPDATE users SET theme = ? WHERE id = ?');
            $stmt->execute([$theme, $userId]);
        }
    } catch (Exception $e) {
        try {
            $pdo->prepare('UPDATE users SET theme = ? WHERE id = ?')->execute([$theme, $userId]);
        } catch (Exception $e2) {
            // ignore
        }
        if ($markChosen) {
            $_SESSION['theme_chosen'] = 1;
        }
    }
    $_SESSION['theme'] = $theme;
    return $theme;
}

/**
 * CSS class string for <html>/<body> based on active theme.
 */
function pbj_theme_body_class(?string $themeId = null): string {
    $id = pbj_normalize_theme_id($themeId ?? pbj_theme_id());
    $classes = ['pbj-theme-' . preg_replace('/[^a-z0-9_-]/', '', $id)];
    $t = pbj_theme_tokens($id);
    if (!empty($t['dark'])) {
        $classes[] = 'pbj-theme-dark';
    }
    return implode(' ', $classes);
}

/**
 * Common theme flags for page shells.
 * - classic_sweet: pink cream Sweet look
 * - neon: dark neon diner
 * - farm: farm-to-table kraft / basil / tomato
 * - basic: sleek navy/cream
 * - fun: themed hub names + stickers
 *
 * @return array{id:string,classic_sweet:bool,neon:bool,farm:bool,urban:bool,coffee:bool,basic:bool,fun:bool}
 */
function pbj_theme_flags(): array {
    $id = pbj_theme_id();
    $classic = ($id === 'sweet');
    $neon = ($id === 'neon_diner');
    $farm = ($id === 'farm');
    $urban = ($id === 'urban');
    $coffee = ($id === 'coffee');
    return [
        'id' => $id,
        'classic_sweet' => $classic,
        'neon' => $neon,
        'farm' => $farm,
        'urban' => $urban,
        'coffee' => $coffee,
        'basic' => (!$classic && !$neon && !$farm && !$urban && !$coffee),
        'fun' => pbj_use_fun_names(),
    ];
}

/** Echo @font-face rules for Neon local fonts (safe to call on every neon page). */
function pbj_render_neon_font_faces(): void {
    if (pbj_theme_id() !== 'neon_diner') {
        return;
    }
    echo <<<'FACES'
<style id="pbj-neon-fonts">
@font-face {
  font-family: 'Warnes';
  src: url('/Fonts/Warnes-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Mouse Memoirs';
  src: url('/Fonts/MouseMemoirs-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'MouseMemoirs';
  src: url('/Fonts/MouseMemoirs-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
</style>
FACES;
}

/** Echo @font-face rules for Farm local fonts (Stylish Handwriting + Notepen). */
function pbj_render_farm_font_faces(): void {
    if (pbj_theme_id() !== 'farm') {
        return;
    }
    echo <<<'FACES'
<style id="pbj-farm-fonts">
@font-face {
  font-family: 'Stylish Handwriting Free';
  src: url('/Fonts/Stylish%20Handwriting%20Free.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Stylish Handwriting';
  src: url('/Fonts/Stylish%20Handwriting%20Free.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Notepen';
  src: url('/Fonts/Notepen.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'NotepenRegular';
  src: url('/Fonts/Notepen.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
</style>
FACES;
}

/** Echo @font-face rules for Urban local fonts (Rafika + Kelly Slab). */
function pbj_render_urban_font_faces(): void {
    if (pbj_theme_id() !== 'urban') {
        return;
    }
    echo <<<'FACES'
<style id="pbj-urban-fonts">
@font-face {
  font-family: 'Rafika';
  src: url('/Fonts/Rafika.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Rafika-Regular';
  src: url('/Fonts/Rafika.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Kelly Slab';
  src: url('/Fonts/KellySlab-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'KellySlab-Regular';
  src: url('/Fonts/KellySlab-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
</style>
FACES;
}

/** Echo @font-face rules for Coffee local fonts (Coffee Town + Ambery Garden). */
function pbj_render_coffee_font_faces(): void {
    if (pbj_theme_id() !== 'coffee') {
        return;
    }
    echo <<<'FACES'
<style id="pbj-coffee-fonts">
@font-face {
  font-family: 'Coffee Town';
  src: url('/Fonts/Coffee%20Town.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'CoffeeTown';
  src: url('/Fonts/Coffee%20Town.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Ambery Garden';
  src: url('/Fonts/Ambery%20Garden.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'AmberyGarden-Regular';
  src: url('/Fonts/Ambery%20Garden.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
</style>
FACES;
}

/**
 * Echo global theme paint CSS (variables + overrides).
 * Safe to call once per page (bottom-nav includes it).
 * Call in <head> ideally AFTER page styles so paint wins.
 */
function pbj_render_theme_paint(bool $force = false): void {
    static $done = false;
    // $force = re-emit after page styles (bottom-nav) so fonts/colors always win
    if ($done && !$force) {
        return;
    }
    $done = true;

    // Only paint authenticated app chrome; marketing/login keep their own look
    if (empty($_SESSION['user_id']) && !(defined('AUTH_BYPASS') && AUTH_BYPASS)) {
        return;
    }

    // Root base so /FOH/pos etc. load /assets and /hub-card-order.js correctly
    if (function_exists('pbj_render_base_href')) {
        pbj_render_base_href();
    }

    // Prefer session theme; normalize so neon_diner sticks
    if (!empty($_SESSION['theme'])) {
        $_SESSION['theme'] = pbj_normalize_theme_id($_SESSION['theme']);
    }

    $t = pbj_theme_tokens();
    $id = $t['id'];

    // Sweet / basic already painted by each page's inline PHP styles
    if ($id === 'sweet' || $id === 'basic') {
        return;
    }

    if (!empty($t['google_fonts'])) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars($t['google_fonts']) . '">' . "\n";
    }

    // Safe CSS values: do NOT entity-encode quotes (breaks font-family lists)
    $cssVal = static function (string $key) use ($t): string {
        $val = (string) ($t[$key] ?? '');
        $val = str_replace(['</', '<', "\n", "\r"], '', $val);
        return $val;
    };

    $fontBody = $cssVal('font_body');
    $fontDisplay = $cssVal('font_display');
    $themeAttr = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

    echo '<style id="pbj-theme-paint" data-theme="' . $themeAttr . '">' . "\n";

    if ($id === 'neon_diner') {
        // Multiple family aliases so both @font-face names and internal names resolve
        echo <<<'FACES'
@font-face {
  font-family: 'Warnes';
  src: url('/Fonts/Warnes-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Warnes-Regular';
  src: url('/Fonts/Warnes-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Mouse Memoirs';
  src: url('/Fonts/MouseMemoirs-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'MouseMemoirs';
  src: url('/Fonts/MouseMemoirs-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}

FACES;
    }

    if ($id === 'farm') {
        echo <<<'FACES'
@font-face {
  font-family: 'Stylish Handwriting Free';
  src: url('/Fonts/Stylish%20Handwriting%20Free.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Stylish Handwriting';
  src: url('/Fonts/Stylish%20Handwriting%20Free.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Notepen';
  src: url('/Fonts/Notepen.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'NotepenRegular';
  src: url('/Fonts/Notepen.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}

FACES;
    }

    if ($id === 'urban') {
        echo <<<'FACES'
@font-face {
  font-family: 'Rafika';
  src: url('/Fonts/Rafika.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Rafika-Regular';
  src: url('/Fonts/Rafika.otf') format('opentype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Kelly Slab';
  src: url('/Fonts/KellySlab-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'KellySlab-Regular';
  src: url('/Fonts/KellySlab-Regular.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}

FACES;
    }

    if ($id === 'coffee') {
        echo <<<'FACES'
@font-face {
  font-family: 'Coffee Town';
  src: url('/Fonts/Coffee%20Town.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'CoffeeTown';
  src: url('/Fonts/Coffee%20Town.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Ambery Garden';
  src: url('/Fonts/Ambery%20Garden.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'AmberyGarden-Regular';
  src: url('/Fonts/Ambery%20Garden.ttf') format('truetype');
  font-weight: 400 700;
  font-style: normal;
  font-display: swap;
}

FACES;
    }

    echo ":root, html.pbj-theme-{$themeAttr}, body.pbj-theme-{$themeAttr} {\n";
    foreach ([
        'page_bg', 'text', 'muted', 'header_bg', 'header_text', 'card_bg', 'card_border',
        'primary', 'primary_text', 'secondary_bg', 'secondary_text', 'accent', 'accent2',
        'input_bg', 'input_border', 'nav_bg', 'nav_text', 'nav_border',
        'danger_bg', 'danger_text', 'glow', 'font_body', 'font_display',
    ] as $key) {
        $cssKey = str_replace('_', '-', $key);
        echo '  --pbj-' . $cssKey . ': ' . $cssVal($key) . ";\n";
    }
    echo "}\n";

    // Direct font stacks (not only CSS vars) — more reliable across browsers
    $isDark = !empty($t['dark']);
    echo "/* ===== Theme paint: {$themeAttr} ===== */\n";
    echo "html, body,\n";
    echo "html.pbj-theme-{$themeAttr}, body.pbj-theme-{$themeAttr} {\n";
    echo "  background: " . $cssVal('page_bg') . " !important;\n";
    echo "  color: " . $cssVal('text') . " !important;\n";
    echo "  font-family: {$fontBody} !important;\n";
    echo "}\n";
    echo "h1, h2, h3,\n";
    echo ".header h1, .hub-main h1, body h1,\n";
    echo ".card h3, .card-head h2, .cat-title, .track-title, .section-label,\n";
    echo ".map-title, .modal h2, .closeout h2, .theme-card h2, .coming-card h3,\n";
    echo ".nav-item span, .nav-item, .nav-item-bottom span {\n";
    echo "  font-family: {$fontDisplay} !important;\n";
    echo "  font-weight: 700 !important;\n";
    echo "  letter-spacing: 0.02em;\n";
    echo "}\n";
    echo "p, label, .btn, button, a.btn, .subtitle, .tagline, .hint, .meta, .card p,\n";
    echo ".nav-item-bottom, .jelly-stat .lbl, .jelly-stat .sub, .jelly-stat .num,\n";
    echo ".chip, .tab, input, select, textarea, body, .content, .hub-main {\n";
    echo "  font-family: {$fontBody} !important;\n";
    echo "}\n";

    // Thin handwriting faces — weight tuning per theme
    // (this paint block only loads for the active theme, so selectors can be global)
    if ($id === 'farm') {
        echo "/* Notepen: thicken body copy */\n";
        echo <<<'BODY_BOLD'
html, body,
p, label, .btn, button, a.btn, .subtitle, .tagline, .hint, .meta, .card p,
.intro, .hello, .map-body, .map-bits, .jelly-stat .lbl, .jelly-stat .sub,
.chip, .tab, input, select, textarea, .content, .hub-main, .nav-item-bottom,
.field label, .tip p, .welcome-banner, li {
  font-weight: 700 !important;
  font-synthesis: weight !important;
  -webkit-text-stroke: 0.5px currentColor;
  paint-order: stroke fill;
  text-shadow:
    0.4px 0 0 currentColor,
   -0.4px 0 0 currentColor,
    0 0.4px 0 currentColor,
    0 -0.4px 0 currentColor,
    0.25px 0.25px 0 currentColor;
  letter-spacing: 0.015em;
}
body, .content, .hub-main, p, .card p, .intro, .map-body, .tip p {
  font-size: 1.1em;
  line-height: 1.55;
}
h1, h2, h3,
.header h1, .hub-main h1, .card h3, .map-title, .section-label,
.nav-item span, .nav-item, .cat-title, .track-title {
  -webkit-text-stroke: 0 !important;
  text-shadow: none !important;
  font-weight: 700 !important;
  font-size: revert;
  line-height: revert;
}
/* Farm bottom-nav labels: dark border around type */
.nav-item-bottom,
.nav-item-bottom span,
.bottom-nav .nav-item-bottom,
.bottom-nav .nav-item-bottom span {
  -webkit-text-stroke: 0.7px #2C2416 !important;
  paint-order: stroke fill;
  text-shadow:
    0.6px 0 0 #2C2416,
   -0.6px 0 0 #2C2416,
    0 0.6px 0 #2C2416,
    0 -0.6px 0 #2C2416,
    0.45px 0.45px 0 #2C2416,
   -0.45px 0.45px 0 #2C2416,
    0.45px -0.45px 0 #2C2416,
   -0.45px -0.45px 0 #2C2416 !important;
}

BODY_BOLD;
    }
    if ($id === 'coffee') {
        // Secondary font (Ambery Garden): lighter + more open letter-spacing
        echo <<<'COFFEE_BODY'
/* Coffee body (Ambery Garden): regular weight, open spacing */
html, body,
p, label, .btn, button, a.btn, .subtitle, .tagline, .hint, .meta, .card p,
.intro, .hello, .map-body, .map-bits, .jelly-stat .lbl, .jelly-stat .sub,
.chip, .tab, input, select, textarea, .content, .hub-main,
.field label, .tip p, .welcome-banner, li, .nav-item-bottom, .nav-item-bottom span {
  font-weight: 400 !important;
  font-synthesis: none !important;
  -webkit-text-stroke: 0 !important;
  text-shadow: none !important;
  letter-spacing: 0.06em;
}
body, .content, .hub-main, p, .card p, .intro, .map-body, .tip p {
  font-size: 1.08em;
  line-height: 1.6;
}
/* Coffee Town headlines stay bold */
h1, h2, h3,
.header h1, .hub-main h1, .card h3, .map-title, .section-label,
.nav-item span, .nav-item, .cat-title, .track-title {
  -webkit-text-stroke: 0 !important;
  text-shadow: none !important;
  font-weight: 700 !important;
  letter-spacing: 0.03em;
  font-size: revert;
  line-height: revert;
}
.nav-item-bottom,
.nav-item-bottom span,
.nav-item,
.nav-item span,
.bottom-nav .nav-item-bottom,
.bottom-nav .nav-item-bottom span {
  font-weight: 400 !important;
  font-synthesis: none !important;
  -webkit-text-stroke: 0 !important;
  text-shadow: none !important;
  letter-spacing: 0.05em;
}

COFFEE_BODY;
    }

    // Theme-aware elevation
    if ($id === 'neon_diner') {
        $cardShadow = '0 0 0 1px rgba(255,46,203,0.18), 0 8px 24px rgba(0,0,0,0.5)';
        $iconBg = '#0F0F0F';
        $iconGlow = '0 0 12px rgba(0,240,255,0.35)';
        $navShadow = '0 -4px 24px rgba(255,46,203,0.25)';
        $navImgGlow = '0 0 10px rgba(0,240,255,0.35)';
        $dashImgGlow = '0 0 10px rgba(255,46,203,0.35)';
        $itemBg = '#101010';
        $stepDoneBg = '#0A2A2A';
        $modalBackdrop = 'rgba(0,0,0,0.72)';
        $imgFilter = 'drop-shadow(0 0 6px rgba(0,240,255,0.35))';
    } elseif ($id === 'urban') {
        $cardShadow = '0 0 0 1px rgba(255,45,0,0.22), 0 10px 28px rgba(0,0,0,0.55)';
        $iconBg = '#0A0A0A';
        $iconGlow = '0 0 14px rgba(255,45,0,0.35)';
        $navShadow = '0 -4px 24px rgba(255,45,0,0.22)';
        $navImgGlow = '0 0 10px rgba(255,45,0,0.35)';
        $dashImgGlow = '0 0 12px rgba(255,45,0,0.4)';
        $itemBg = '#101010';
        $stepDoneBg = '#1A1008';
        $modalBackdrop = 'rgba(0,0,0,0.78)';
        $imgFilter = 'drop-shadow(0 0 6px rgba(255,45,0,0.3))';
    } elseif ($id === 'coffee') {
        $cardShadow = '0 8px 20px rgba(74,44,26,0.10), 0 1px 0 rgba(255,255,255,0.7) inset';
        $iconBg = '#FFFAF5';
        $iconGlow = '0 4px 12px rgba(139,90,43,0.14)';
        $navShadow = '0 -4px 18px rgba(74,44,26,0.18)';
        $navImgGlow = '0 3px 10px rgba(74,44,26,0.12)';
        $dashImgGlow = '0 4px 12px rgba(139,90,43,0.18)';
        $itemBg = '#FFFAF5';
        $stepDoneBg = '#EFE4D8';
        $modalBackdrop = 'rgba(61,36,22,0.4)';
        $imgFilter = 'none';
    } else {
        // farm (+ future light themes) — khaki sticker plate to match dashboard icons
        $cardShadow = '0 6px 18px rgba(44,36,22,0.10), 0 1px 0 rgba(255,255,255,0.6) inset';
        $iconBg = '#F3E6D4';
        $iconGlow = '0 4px 12px rgba(47,107,58,0.15)';
        $navShadow = '0 -4px 18px rgba(47,107,58,0.18)';
        $navImgGlow = '0 3px 10px rgba(44,36,22,0.12)';
        $dashImgGlow = '0 4px 12px rgba(196,59,44,0.18)';
        $itemBg = '#F3E6D4';
        $stepDoneBg = '#E5F0E7';
        $modalBackdrop = 'rgba(44,36,22,0.45)';
        $imgFilter = 'none';
    }

    $colorScheme = $isDark ? 'dark' : 'light';

    echo <<<CSS
/* Top nav (dashboard) + standard .header bars */
.header,
nav {
  background: var(--pbj-header-bg) !important;
  color: var(--pbj-header-text) !important;
  box-shadow: var(--pbj-glow) !important;
  border-bottom: 2px solid var(--pbj-accent) !important;
}
.header h1, nav .nav-item, .nav-item {
  color: var(--pbj-header-text) !important;
}
.subtitle, .header .subtitle, .hub-main .tagline, .tagline {
  color: var(--pbj-accent) !important;
  opacity: 0.95 !important;
}
.back-link { color: var(--pbj-accent) !important; }

.hub-main { color: var(--pbj-text) !important; }
.hub-main h1 { color: var(--pbj-primary) !important; }

.card, .intro, .hello, .stat, .closeout, .category, .modal, .track,
.person-card, .item, .person, .invite, .tip, .pin-note, .empty-slot,
.sync-pill, .code-box, .jelly-stat, .welcome-banner, .preview, .content .intro {
  background: var(--pbj-card-bg) !important;
  color: var(--pbj-text) !important;
  border-color: var(--pbj-card-border) !important;
  box-shadow: {$cardShadow} !important;
}
.welcome-banner {
  border: 2px dashed var(--pbj-accent) !important;
  color: var(--pbj-text) !important;
}
.welcome-banner a { color: var(--pbj-accent) !important; }
.welcome-banner .code {
  background: var(--pbj-secondary-bg) !important;
  color: var(--pbj-primary) !important;
  border: 2px dashed var(--pbj-card-border) !important;
}

.card h3, .card-head h2, .cat-title, .track-title, .section-label,
.map-title, .modal h2, .closeout h2, .jelly-stat .num {
  color: var(--pbj-primary) !important;
}
.card p, .cat-hint, .hint, .meta, .map-body, .intro, .hello,
.jelly-stat .lbl, .jelly-stat .sub { color: var(--pbj-muted) !important; }

.btn-primary, button.btn-primary, a.btn-primary, .btn.btn-primary {
  background: var(--pbj-primary) !important;
  color: var(--pbj-primary-text) !important;
  box-shadow: var(--pbj-glow) !important;
  border: none !important;
}
.btn-secondary, a.btn-secondary, button.btn-secondary {
  background: var(--pbj-secondary-bg) !important;
  color: var(--pbj-secondary-text) !important;
  box-shadow: 0 0 0 1px var(--pbj-accent) !important;
}
.btn-ghost, button.btn-ghost, a.btn-ghost {
  background: var(--pbj-secondary-bg) !important;
  color: var(--pbj-text) !important;
  border: 1px solid var(--pbj-accent) !important;
}
.btn-danger, button.btn-danger {
  background: var(--pbj-danger-bg) !important;
  color: var(--pbj-danger-text) !important;
}

.field input, .field select, .field textarea, input[type="text"], input[type="search"],
input[type="email"], input[type="password"], input[type="number"], input[type="date"],
input[type="tel"], input[type="time"], input[type="url"], input[type="datetime-local"],
textarea, select, .search, .notes-card input, .notes-card select, .notes-card textarea,
.modal input, .modal select, .modal textarea {
  background: var(--pbj-input-bg) !important;
  color: var(--pbj-text) !important;
  border-color: var(--pbj-input-border) !important;
  font-family: var(--pbj-font-body) !important;
  color-scheme: {$colorScheme};
}
.field input::placeholder, .field textarea::placeholder, input::placeholder, textarea::placeholder,
.search::placeholder {
  color: var(--pbj-muted) !important;
  opacity: 0.85;
}
select option, select optgroup {
  background: var(--pbj-input-bg) !important;
  color: var(--pbj-text) !important;
}
/* Beat browser autofill white boxes on dark themes */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
textarea:-webkit-autofill,
select:-webkit-autofill {
  -webkit-text-fill-color: var(--pbj-text) !important;
  caret-color: var(--pbj-text) !important;
  box-shadow: 0 0 0 1000px var(--pbj-input-bg) inset !important;
  transition: background-color 99999s ease-out 0s;
}

.tab, .chip, .mode-tab {
  background: var(--pbj-secondary-bg) !important;
  color: var(--pbj-text) !important;
  box-shadow: none !important;
}
.tab.active, .chip.active, .mode-tab.active {
  background: var(--pbj-primary) !important;
  color: var(--pbj-primary-text) !important;
  box-shadow: var(--pbj-glow) !important;
}
.badge, .count-pill, .pill, .soon-pill {
  background: var(--pbj-secondary-bg) !important;
  color: var(--pbj-accent) !important;
  border-color: var(--pbj-accent) !important;
}

.card-icon, .map-icon, .cat-icon, .card-icon.sweet-sticker, .cat-icon.sweet-sticker {
  background: {$iconBg} !important;
  border: 2px solid var(--pbj-accent) !important;
  box-shadow: {$iconGlow} !important;
  color: var(--pbj-primary) !important;
}

.bottom-nav {
  background: var(--pbj-nav-bg) !important;
  box-shadow: {$navShadow} !important;
  border-top: 2px solid var(--pbj-primary) !important;
}
.nav-item-bottom { color: var(--pbj-nav-text) !important; }
.nav-item-bottom img {
  border-color: var(--pbj-nav-border) !important;
  box-shadow: {$navImgGlow} !important;
}

.toast, .saved-toast, .toast.show {
  background: var(--pbj-primary) !important;
  color: var(--pbj-primary-text) !important;
  box-shadow: var(--pbj-glow) !important;
}
.modal-backdrop { background: {$modalBackdrop} !important; }
.modal {
  background: var(--pbj-card-bg) !important;
  color: var(--pbj-text) !important;
  border: 2px solid var(--pbj-primary) !important;
  box-shadow: var(--pbj-glow) !important;
}
.sync-pill {
  background: var(--pbj-secondary-bg) !important;
  color: var(--pbj-accent) !important;
  border: 1px solid var(--pbj-accent) !important;
}
.item, .person, .entry, .section {
  background: {$itemBg} !important;
  border-left-color: var(--pbj-primary) !important;
  color: var(--pbj-text) !important;
}
a { color: var(--pbj-accent); }
.step, .step.miss {
  background: var(--pbj-secondary-bg) !important;
  border-color: var(--pbj-card-border) !important;
  color: var(--pbj-text) !important;
}
.step.done {
  background: {$stepDoneBg} !important;
  border-color: var(--pbj-accent) !important;
}
.card-icon img, .cat-icon img, .map-icon img, .nav-item img {
  filter: {$imgFilter};
}
/* Dashboard top nav images */
.nav-item img {
  border: 2px solid var(--pbj-nav-border) !important;
  border-radius: 12px;
  box-shadow: {$dashImgGlow} !important;
}
CSS;

    // Neon + Urban: force black fields / bright type on every page (overrides sweet-template cream inputs)
    if ($isDark) {
        echo <<<'DARK_FIELDS'

/* ===== Dark theme form fields: black bg + bright text ===== */
html, body {
  color-scheme: dark !important;
}
input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="file"]):not([type="submit"]):not([type="button"]):not([type="image"]),
textarea,
select,
.field input,
.field textarea,
.field select,
.notes-card input,
.notes-card textarea,
.notes-card select,
.modal input,
.modal textarea,
.modal select,
.search,
input[type="search"] {
  background-color: #000000 !important;
  background-image: none !important;
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
  caret-color: #FFFFFF !important;
  border-color: var(--pbj-input-border) !important;
  color-scheme: dark !important;
}
input::placeholder,
textarea::placeholder,
.field input::placeholder,
.field textarea::placeholder,
.search::placeholder {
  color: rgba(255, 255, 255, 0.55) !important;
  -webkit-text-fill-color: rgba(255, 255, 255, 0.55) !important;
  opacity: 1 !important;
}
select option,
select optgroup {
  background-color: #000000 !important;
  color: #FFFFFF !important;
}
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
textarea:-webkit-autofill,
select:-webkit-autofill {
  -webkit-text-fill-color: #FFFFFF !important;
  caret-color: #FFFFFF !important;
  box-shadow: 0 0 0 1000px #000000 inset !important;
  border-color: var(--pbj-input-border) !important;
}

/* Checklists + Menu/Recipes cream panels: beat white/cream cards */
.checklist,
.progress-card,
.notes-card,
.screen-list .checklist,
.intro,
.loop,
.toolbar,
.tab:not(.active),
.btn-secondary,
.btn-ghost,
.btn.btn-secondary,
.btn.btn-ghost,
.handoff-item,
.handoff-empty,
.empty,
.complete-banner,
.sync-pill,
.modal,
.progress-bar,
.station,
.list-card,
.sync-card,
.chip:not(.active),
.category,
.recipe,
.item,
.stat,
.ing-builder,
.steps-builder,
.plate-builder,
.readonly-box,
.result,
.formula,
.filter-chip:not(.active),
.cat-tab:not(.active),
.yield-row,
.card {
  background-color: #0A0A0A !important;
  background: #0A0A0A !important;
  color: #FFFFFF !important;
  border-color: var(--pbj-card-border, #333333) !important;
  box-shadow: none !important;
}
/* Nested recipe builders: inputs that hardcode white/cream */
.ing-row input,
.ing-row select,
.ing-row textarea,
.step-row input,
.step-row select,
.step-row textarea,
.plate-builder input,
.plate-builder textarea,
.category-header:hover {
  background-color: #000000 !important;
  background: #000000 !important;
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
  border-color: var(--pbj-input-border, #444) !important;
}
.station-header,
.list-header,
button.station-header,
button.list-header {
  background: transparent !important;
  background-color: transparent !important;
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
}
.checklist .check-item,
.checklist .check-item:last-of-type {
  background: transparent !important;
  border-bottom-color: #2A2A2A !important;
  color: #FFFFFF !important;
}
.checklist .check-main,
button.check-main,
.checklist .check-text,
.checklist .check-body,
.checklist .empty,
.progress-card,
.progress-top,
#ecl-progress-label,
#ecl-progress-count,
.notes-card,
.notes-card h2,
.notes-card h3,
.notes-card .hint-sm,
.notes-card label,
.handoff-item,
.handoff-item .ht,
.handoff-item .hm,
.handoff-item .hb,
.handoff-feed-head h3,
.handoff-empty,
.intro,
.tab:not(.active),
.btn-secondary,
.btn-ghost {
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
}
.checklist .check-main,
button.check-main {
  background: transparent !important;
  background-color: transparent !important;
}
.checklist .check-item.done .check-text {
  color: rgba(255, 255, 255, 0.55) !important;
  -webkit-text-fill-color: rgba(255, 255, 255, 0.55) !important;
}
.checklist .checkbox {
  border-color: var(--pbj-accent, #FFFFFF) !important;
  background: #000000 !important;
  color: #FFFFFF !important;
}
.checklist .check-item.done .checkbox {
  background: var(--pbj-primary, #FF2ECB) !important;
  border-color: var(--pbj-primary, #FF2ECB) !important;
  color: #FFFFFF !important;
}
.progress-bar {
  background: #1A1A1A !important;
}
.tab.active {
  color: var(--pbj-primary-text, #FFFFFF) !important;
}
.btn-secondary,
a.btn-secondary,
button.btn-secondary {
  background: #111111 !important;
  color: #FFFFFF !important;
  box-shadow: 0 0 0 1px var(--pbj-accent, #444) !important;
}
.btn-ghost,
button.btn-ghost,
a.btn-ghost {
  background: #111111 !important;
  color: #FFFFFF !important;
  border-color: #333333 !important;
}
.btn-danger,
button.btn-danger {
  background: #3A1010 !important;
  color: #FFB4B4 !important;
}
.complete-banner,
.complete-banner.show {
  background: #0A2A1A !important;
  color: #B8F0D0 !important;
  border-color: #1F6B4A !important;
}
.sync-pill {
  background: #111111 !important;
  color: var(--pbj-accent, #FFFFFF) !important;
  border-color: var(--pbj-accent, #333) !important;
}
.handoff-item {
  background: #111111 !important;
  border-left-color: var(--pbj-accent, #FFFFFF) !important;
  color: #FFFFFF !important;
}
.notes-card input,
.notes-card textarea,
.notes-card select,
.modal input,
.modal textarea,
.modal select,
.field input,
.field textarea,
.field select {
  background-color: #000000 !important;
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
  border-color: var(--pbj-input-border, #444) !important;
}

/* Bright type everywhere under dark themes (beat #3a2f1f / #1A2A44 page CSS) — FOH + BOH */
body,
body .content,
body .content p,
body .content span,
body .content label,
body .content li,
body .content td,
body .content th,
body .content div,
body .content button:not(.btn-primary):not(.tab.active),
body .content .hint,
body .content .hint-sm,
body .content .meta,
body .content .subtitle,
body .content .empty,
body .content .intro,
body .content .check-text,
body .content .check-main,
body .content .check-body,
body .content .check-item,
body .content .progress-card,
body .content .progress-top,
body .content .notes-card,
body .content .notes-status,
body .content .handoff-item,
body .content .handoff-item *,
body .content .handoff-empty,
body .content .handoff-feed-head,
body .content .handoff-feed-head h3,
body .content .tab,
body .content button.tab,
body .content .btn-secondary,
body .content .btn-ghost,
body .content .btn-small,
body .content a.btn-secondary,
body .content a.btn-ghost,
body .content .chip,
body .content a.chip,
body .content .station,
body .content .station-header,
body .content .list-card,
body .content .list-header,
body .content .sync-card,
body .content .station-title,
body .content .list-title,
body .content .item-label,
body .content .loop,
body .content .loop strong,
body .content .category,
body .content .category-header,
body .content .cat-title,
body .content .cat-hint,
body .content .recipe,
body .content .recipe *,
body .content .item,
body .content .item *,
body .content .ing-builder,
body .content .ing-builder *,
body .content .steps-builder,
body .content .steps-builder *,
body .content .plate-builder,
body .content .plate-builder *,
body .content .readonly-box,
body .content .result,
body .content .result *,
body .content .formula,
body .content .filter-chip,
body .content .cat-tab,
body .content .stat,
body .content .stat .num,
body .content .stat .lbl,
body .content .price,
body .content .yield-pct,
body .content .section-label,
#ecl-progress-label,
#ecl-progress-count,
#ecl-notes-status,
#ecl-list-root,
#ecl-list-root *,
#list-root,
#list-root *,
#stations,
#stations *,
#lists,
#lists *,
#categories,
#categories * {
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
}
/* Bright accents (links + titles) — pink/cyan stay readable on black */
body .content a:not(.btn-primary):not(.btn-danger):not(.card),
body .content .back-link,
body .content .intro a,
body .content .loop a {
  color: var(--pbj-accent, #00F0FF) !important;
  -webkit-text-fill-color: var(--pbj-accent, #00F0FF) !important;
}
body .content h1,
body .content h2,
body .content h3,
body .content .notes-card h2,
body .content .modal h2,
body .content .section-label,
body .content .cat-title,
body .content .card h3,
body .content .loop strong,
body .content .price,
body .content .yield-pct,
body .content .stat .num,
body .content .readonly-box {
  color: var(--pbj-primary, #FF2ECB) !important;
  -webkit-text-fill-color: var(--pbj-primary, #FF2ECB) !important;
}
/* FC badges stay readable on dark cards */
body .content .badge-good { background: #1B5E20 !important; color: #C8E6C9 !important; -webkit-text-fill-color: #C8E6C9 !important; }
body .content .badge-mid { background: #F57F17 !important; color: #FFF9C4 !important; -webkit-text-fill-color: #FFF9C4 !important; }
body .content .badge-high { background: #B71C1C !important; color: #FFCDD2 !important; -webkit-text-fill-color: #FFCDD2 !important; }
body .content .badge-na { background: #333333 !important; color: #CFD8DC !important; -webkit-text-fill-color: #CFD8DC !important; }
body .content .btn-primary,
body .content a.btn-primary,
body .content button.btn-primary,
body .content .tab.active {
  color: var(--pbj-primary-text, #FFFFFF) !important;
  -webkit-text-fill-color: var(--pbj-primary-text, #FFFFFF) !important;
}
body .content .btn-danger,
body .content button.btn-danger {
  color: #FFB4B4 !important;
  -webkit-text-fill-color: #FFB4B4 !important;
}
body .content .check-item.done .check-text {
  color: rgba(255, 255, 255, 0.55) !important;
  -webkit-text-fill-color: rgba(255, 255, 255, 0.55) !important;
}
/* Header bar: keep title/sub bright */
.header,
.header h1,
.header .subtitle,
.header p,
.header a.back-link {
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
}
.header .subtitle {
  color: rgba(255, 255, 255, 0.92) !important;
  -webkit-text-fill-color: rgba(255, 255, 255, 0.92) !important;
  opacity: 1 !important;
}

/*
 * Buttons: kill white-on-white.
 * Page CSS often sets .btn-secondary { background: white } while dark paint
 * forces white type via body .content button / div. Always pair dark surfaces
 * with light type (and primary/danger keep their own fills).
 */
.btn,
a.btn,
button.btn,
body .content .btn,
body .content a.btn,
body .content button.btn,
body .content .btn-small,
body .content a.btn-small,
body .content button.btn-small,
body .hub-main .btn,
body .hub-main a.btn,
body .hub-main button.btn,
body .content button:not(.btn-primary):not(.tab.active):not(.check-main):not(.station-header):not(.list-header):not(.sc-modal-close),
body .hub-main button:not(.btn-primary):not(.sc-modal-close):not(.sc-remove),
.actions-bar .btn,
.actions-bar a.btn,
.toolbar .btn,
.toolbar a.btn,
.toolbar button,
.vendor-actions .btn,
.vendor-actions a.btn,
.modal-actions .btn,
.modal .btn,
.filter-chip,
.cat-tab,
.flow-step,
.mode-tab:not(.active),
.chip:not(.active),
button.chip,
a.chip {
  background: #111111 !important;
  background-color: #111111 !important;
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
  border-color: var(--pbj-accent, #333333) !important;
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
}
/* Primary: neon pink (or theme primary) + high-contrast label */
.btn-primary,
a.btn-primary,
button.btn-primary,
.btn.btn-primary,
body .content .btn-primary,
body .content a.btn-primary,
body .content button.btn-primary,
body .hub-main .btn-primary,
body .hub-main a.btn-primary,
body .hub-main button.btn-primary,
.tab.active,
.chip.active,
.mode-tab.active,
.filter-chip.active,
.cat-tab.active {
  background: var(--pbj-primary, #FF2ECB) !important;
  background-color: var(--pbj-primary, #FF2ECB) !important;
  color: var(--pbj-primary-text, #0A0A0A) !important;
  -webkit-text-fill-color: var(--pbj-primary-text, #0A0A0A) !important;
  border: none !important;
  box-shadow: var(--pbj-glow, 0 0 18px rgba(255, 46, 203, 0.45)) !important;
}
/* Ghost / secondary stay dark with accent edge */
.btn-secondary,
a.btn-secondary,
button.btn-secondary,
.btn-ghost,
a.btn-ghost,
button.btn-ghost,
body .content .btn-secondary,
body .content a.btn-secondary,
body .content .btn-ghost,
body .content a.btn-ghost {
  background: #111111 !important;
  background-color: #111111 !important;
  color: #FFFFFF !important;
  -webkit-text-fill-color: #FFFFFF !important;
  box-shadow: 0 0 0 1px var(--pbj-accent, #00F0FF) !important;
}
.btn-danger,
a.btn-danger,
button.btn-danger,
body .content .btn-danger,
body .content button.btn-danger {
  background: #3A1010 !important;
  background-color: #3A1010 !important;
  color: #FFB4B4 !important;
  -webkit-text-fill-color: #FFB4B4 !important;
  box-shadow: none !important;
}
/* Transparent text buttons (check rows, station headers) keep no fill */
button.check-main,
.checklist .check-main,
button.station-header,
button.list-header,
.sc-modal-close {
  background: transparent !important;
  background-color: transparent !important;
  box-shadow: none !important;
  border-color: transparent !important;
}

DARK_FIELDS;
    }

    echo "\n</style>\n";
}

function pbj_save_restaurant_plan(PDO $pdo, int $restaurantId, string $planId): void {
    $plan = pbj_plan_by_id($planId);
    $planId = $plan ? $plan['id'] : pbj_default_plan_id();
    $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
    $stmt->execute([$restaurantId]);
    $raw = $stmt->fetchColumn();
    $settings = [];
    if ($raw) {
        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            $settings = $decoded;
        }
    }
    $settings['plan'] = $planId;
    $settings['plan_selected_at'] = date('c');
    // Intent until Stripe marks paid (webhook / billing-success)
    if (($settings['billing_status'] ?? '') !== 'paid') {
        $settings['billing_status'] = ($planId === 'custom') ? 'contact' : 'selected_unpaid';
    }

    if ($raw !== false && $raw !== null) {
        $upd = $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?');
        $upd->execute([json_encode($settings, JSON_UNESCAPED_UNICODE), $restaurantId]);
    } else {
        $ins = $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())');
        $ins->execute([$restaurantId, json_encode($settings, JSON_UNESCAPED_UNICODE)]);
    }
}

function pbj_create_restaurant(PDO $pdo, int $ownerId, string $name, string $planId = 'crew_10'): array {
    $name = trim($name);
    if ($name === '') {
        $name = 'My Restaurant';
    }
    $code = pbj_generate_invite_code($pdo);
    $stmt = $pdo->prepare('INSERT INTO restaurants (name, owner_id, invite_code) VALUES (?, ?, ?)');
    $stmt->execute([$name, $ownerId, $code]);
    $rid = (int) $pdo->lastInsertId();

    $link = $pdo->prepare('INSERT INTO user_restaurant (user_id, restaurant_id, role) VALUES (?, ?, ?)');
    $link->execute([$ownerId, $rid, 'owner']);

    pbj_save_restaurant_plan($pdo, $rid, $planId);

    // Convert path: starting a real house moves them off demo/sales playground seats
    if (function_exists('pbj_detach_user_from_playgrounds')) {
        pbj_detach_user_from_playgrounds($pdo, $ownerId);
    }

    return [
        'id' => $rid,
        'name' => $name,
        'invite_code' => $code,
        'plan' => $planId,
    ];
}

/**
 * Approve an invite-code joiner (employees never pay — house owner does).
 * Does not un-block blocked accounts.
 */
function pbj_approve_invite_joiner(PDO $pdo, int $userId): void {
    if ($userId <= 0) {
        return;
    }
    try {
        $pdo->prepare("UPDATE users SET access_status = 'approved' WHERE id = ? AND access_status = 'pending'")
            ->execute([$userId]);
        if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === $userId) {
            $_SESSION['access_status'] = 'approved';
            $_SESSION['access_checked_at'] = time();
        }
    } catch (Throwable $e) {
        error_log('pbj_approve_invite_joiner: ' . $e->getMessage());
    }
}

/**
 * Link a user to a restaurant. Enforces plan seat caps + invite rules.
 * Any successful invite join auto-approves access (no pay wall for staff).
 * @throws RuntimeException when the house is full or invites are not allowed
 */
function pbj_join_restaurant(PDO $pdo, int $userId, int $restaurantId, string $role = 'foh'): bool {
    $allowed = ['owner', 'manager', 'boh', 'foh', 'admin', 'gm'];
    if (!in_array($role, $allowed, true)) {
        $role = 'foh';
    }
    // Already linked — still ensure invite joiners are approved (session fix / re-join)
    $check = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
    $check->execute([$userId, $restaurantId]);
    $existing = $check->fetchColumn();
    if ($existing) {
        pbj_approve_invite_joiner($pdo, $userId);
        return true;
    }
    $status = pbj_restaurant_seat_status($pdo, $restaurantId);
    if (empty($status['ok'])) {
        throw new RuntimeException(pbj_seat_limit_message($status));
    }
    $stmt = $pdo->prepare('INSERT INTO user_restaurant (user_id, restaurant_id, role) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $restaurantId, $role]);

    // Invite-code path = staff seat on someone's house → never send them to Stripe
    pbj_approve_invite_joiner($pdo, $userId);

    // Joining a real house converts them off playground seats
    if (!pbj_restaurant_is_playground($pdo, $restaurantId)) {
        pbj_detach_user_from_playgrounds($pdo, $userId);
    }

    // Notify owner + welcome note to joiner
    try {
        pbj_notify_house_join($pdo, $restaurantId, $userId, $role);
    } catch (Throwable $e) {
        error_log('join notify: ' . $e->getMessage());
    }

    return true;
}

/**
 * Load optional SMTP secrets (Hostinger mail, etc.).
 * Prefer private path: /var/www/private/ilovepbj/mail-secrets.local.php
 *
 * @return array{
 *   host:string,port:int,encryption:string,username:string,password:string,
 *   from_email:string,from_name:string
 * }
 */
function pbj_mail_load_secrets(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $domain = defined('APP_DOMAIN') ? APP_DOMAIN : 'ilovepbj.shop';
    $defaults = [
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'encryption' => 'ssl', // ssl | tls | none
        'username' => '',
        'password' => '',
        'from_email' => 'noreply@' . $domain,
        'from_name' => 'ilovepbj ops',
    ];
    $paths = [
        '/var/www/private/ilovepbj/mail-secrets.local.php',
        __DIR__ . '/mail-secrets.local.php',
    ];
    foreach ($paths as $path) {
        if (!is_readable($path)) {
            continue;
        }
        $loaded = include $path;
        if (!is_array($loaded)) {
            continue;
        }
        foreach (['host', 'encryption', 'username', 'password', 'from_email', 'from_name'] as $k) {
            if (!isset($loaded[$k]) || !is_string($loaded[$k])) {
                continue;
            }
            $val = trim($loaded[$k]);
            if ($val === '' || stripos($val, 'PASTE') !== false || stripos($val, 'YOUR_') !== false || stripos($val, 'REPLACE') !== false) {
                continue;
            }
            $defaults[$k] = $val;
        }
        if (isset($loaded['port']) && is_numeric($loaded['port'])) {
            $p = (int) $loaded['port'];
            if ($p > 0 && $p < 65536) {
                $defaults['port'] = $p;
            }
        }
    }
    // Env overrides (optional)
    foreach ([
        'SMTP_HOST' => 'host',
        'SMTP_USERNAME' => 'username',
        'SMTP_PASSWORD' => 'password',
        'SMTP_FROM_EMAIL' => 'from_email',
        'SMTP_FROM_NAME' => 'from_name',
        'SMTP_ENCRYPTION' => 'encryption',
    ] as $env => $key) {
        $v = getenv($env);
        if ($v !== false && trim((string) $v) !== '') {
            $defaults[$key] = trim((string) $v);
        }
    }
    $portEnv = getenv('SMTP_PORT');
    if ($portEnv !== false && is_numeric($portEnv)) {
        $defaults['port'] = (int) $portEnv;
    }
    $defaults['encryption'] = strtolower(trim((string) $defaults['encryption']));
    if (!in_array($defaults['encryption'], ['ssl', 'tls', 'none'], true)) {
        $defaults['encryption'] = 'ssl';
    }
    $defaults['from_email'] = strtolower(trim((string) $defaults['from_email']));
    $cache = $defaults;
    return $cache;
}

/** True when SMTP username+password are configured (can send via Hostinger/etc.). */
function pbj_mail_is_configured(): bool {
    $s = pbj_mail_load_secrets();
    $user = trim((string) ($s['username'] ?? ''));
    $pass = (string) ($s['password'] ?? '');
    if ($user === '' || !filter_var($user, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if ($pass === '' || strlen($pass) < 4) {
        return false;
    }
    $host = trim((string) ($s['host'] ?? ''));
    return $host !== '';
}

/**
 * Read one SMTP response (may be multi-line).
 * @param resource $fp
 */
function pbj_smtp_read($fp): string {
    $data = '';
    while (!feof($fp)) {
        $line = fgets($fp, 515);
        if ($line === false) {
            break;
        }
        $data .= $line;
        // Multi-line: "250-..." continues; "250 " ends
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $data;
}

/**
 * @param resource $fp
 */
function pbj_smtp_cmd($fp, string $cmd, string $expectPrefix): bool {
    if ($cmd !== '') {
        fwrite($fp, $cmd . "\r\n");
    }
    $resp = pbj_smtp_read($fp);
    if ($resp === '' || strpos($resp, $expectPrefix) !== 0) {
        error_log('pbj_smtp unexpected response (want ' . $expectPrefix . '): ' . trim($resp) . ' for cmd=' . $cmd);
        return false;
    }
    return true;
}

/**
 * Send plain-text mail via SMTP (AUTH LOGIN). Supports SSL (465) and STARTTLS (587).
 */
function pbj_send_mail_smtp(string $to, string $subject, string $body, string $replyTo = ''): bool {
    $s = pbj_mail_load_secrets();
    $host = trim((string) $s['host']);
    $port = (int) $s['port'];
    $enc = (string) $s['encryption'];
    $user = trim((string) $s['username']);
    $pass = (string) $s['password'];
    $fromEmail = trim((string) $s['from_email']);
    $fromName = trim((string) $s['from_name']);
    if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        $fromEmail = $user;
    }
    if ($fromName === '') {
        $fromName = 'ilovepbj ops';
    }

    $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        error_log("pbj_send_mail_smtp connect failed {$remote}: {$errstr} ({$errno})");
        return false;
    }
    stream_set_timeout($fp, 20);

    try {
        if (!pbj_smtp_cmd($fp, '', '220')) {
            fclose($fp);
            return false;
        }
        $ehloHost = defined('APP_DOMAIN') ? APP_DOMAIN : 'localhost';
        if (!pbj_smtp_cmd($fp, 'EHLO ' . $ehloHost, '250')) {
            fclose($fp);
            return false;
        }
        if ($enc === 'tls') {
            if (!pbj_smtp_cmd($fp, 'STARTTLS', '220')) {
                fclose($fp);
                return false;
            }
            $crypto = stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($crypto !== true) {
                error_log('pbj_send_mail_smtp STARTTLS failed');
                fclose($fp);
                return false;
            }
            if (!pbj_smtp_cmd($fp, 'EHLO ' . $ehloHost, '250')) {
                fclose($fp);
                return false;
            }
        }

        if (!pbj_smtp_cmd($fp, 'AUTH LOGIN', '334')) {
            fclose($fp);
            return false;
        }
        if (!pbj_smtp_cmd($fp, base64_encode($user), '334')) {
            fclose($fp);
            return false;
        }
        if (!pbj_smtp_cmd($fp, base64_encode($pass), '235')) {
            error_log('pbj_send_mail_smtp AUTH failed for user ' . $user);
            fclose($fp);
            return false;
        }

        if (!pbj_smtp_cmd($fp, 'MAIL FROM:<' . $fromEmail . '>', '250')) {
            fclose($fp);
            return false;
        }
        if (!pbj_smtp_cmd($fp, 'RCPT TO:<' . $to . '>', '250')) {
            fclose($fp);
            return false;
        }
        if (!pbj_smtp_cmd($fp, 'DATA', '354')) {
            fclose($fp);
            return false;
        }

        $domain = defined('APP_DOMAIN') ? APP_DOMAIN : 'ilovepbj.shop';
        $msgId = '<' . bin2hex(random_bytes(16)) . '@' . $domain . '>';
        $date = date('r');
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        // Dot-stuff body lines starting with .
        $safeBody = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], "\n", $body));
        $safeBody = str_replace("\n", "\r\n", $safeBody);

        $headers = [
            'Date: ' . $date,
            'From: ' . $encodedFromName . ' <' . $fromEmail . '>',
            'To: <' . $to . '>',
            'Subject: ' . $encodedSubject,
            'Message-ID: ' . $msgId,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: ilovepbj-ops',
        ];
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }

        $data = implode("\r\n", $headers) . "\r\n\r\n" . $safeBody . "\r\n.";
        if (!pbj_smtp_cmd($fp, $data, '250')) {
            fclose($fp);
            return false;
        }
        pbj_smtp_cmd($fp, 'QUIT', '221');
        fclose($fp);
        return true;
    } catch (Throwable $e) {
        error_log('pbj_send_mail_smtp: ' . $e->getMessage());
        if ($fp) {
            @fclose($fp);
        }
        return false;
    }
}

/**
 * Plain-text email helper.
 * Prefers authenticated SMTP (Hostinger) when mail-secrets are set;
 * falls back to PHP mail()/sendmail only if a local MTA exists.
 */
function pbj_send_mail(string $to, string $subject, string $body, string $replyTo = ''): bool {
    $to = trim($to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $replyTo = trim($replyTo);

    if (pbj_mail_is_configured()) {
        $ok = pbj_send_mail_smtp($to, $subject, $body, $replyTo);
        if (!$ok) {
            error_log('pbj_send_mail SMTP failed to ' . $to . ' subject=' . $subject);
        }
        return $ok;
    }

    // Local sendmail path (often missing on VPS — will fail quietly)
    $sendmail = (string) ini_get('sendmail_path');
    $hasSendmail = $sendmail !== '' && (
        str_contains($sendmail, 'sendmail')
            ? is_executable(explode(' ', trim($sendmail), 2)[0])
            : true
    );
    if (!$hasSendmail && !is_executable('/usr/sbin/sendmail') && !is_executable('/usr/bin/sendmail')) {
        error_log('pbj_send_mail: no SMTP secrets and no sendmail — cannot send to ' . $to);
        return false;
    }

    $domain = defined('APP_DOMAIN') ? APP_DOMAIN : 'ilovepbj.shop';
    $fromEmail = 'noreply@' . $domain;
    $s = pbj_mail_load_secrets();
    if (!empty($s['from_email']) && filter_var($s['from_email'], FILTER_VALIDATE_EMAIL)) {
        $fromEmail = $s['from_email'];
    }
    $fromName = !empty($s['from_name']) ? $s['from_name'] : 'ilovepbj ops';
    $headers = 'From: ' . $fromName . ' <' . $fromEmail . ">\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "MIME-Version: 1.0\r\n";
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    }
    $ok = @mail($to, $subject, $body, $headers);
    if (!$ok) {
        error_log('pbj_send_mail mail() failed to ' . $to . ' subject=' . $subject);
    }
    return (bool) $ok;
}

/** Owner email(s) for a restaurant (owner user + optional CC). */
function pbj_restaurant_owner_emails(PDO $pdo, int $restaurantId): array {
    $emails = [];
    try {
        $stmt = $pdo->prepare(
            'SELECT u.email, u.full_name, u.username, r.name AS restaurant_name, r.invite_code
             FROM restaurants r
             LEFT JOIN users u ON u.id = r.owner_id
             WHERE r.id = ? LIMIT 1'
        );
        $stmt->execute([$restaurantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $emails[] = strtolower(trim((string) $row['email']));
        }
    } catch (Throwable $e) {
        $row = null;
    }
    $cc = defined('OWNER_NOTIFY_CC') ? strtolower(trim((string) OWNER_NOTIFY_CC)) : '';
    if ($cc !== '' && filter_var($cc, FILTER_VALIDATE_EMAIL) && !in_array($cc, $emails, true)) {
        $emails[] = $cc;
    }
    return $emails;
}

/**
 * Email the house owner that someone joined, and a short welcome to the joiner.
 */
function pbj_notify_house_join(PDO $pdo, int $restaurantId, int $joinerUserId, string $role = 'foh'): void {
    $houseName = 'your house';
    $inviteCode = '';
    $ownerEmail = '';
    $ownerUserId = 0;
    try {
        $stmt = $pdo->prepare(
            'SELECT r.name, r.invite_code, r.owner_id, u.email AS owner_email
             FROM restaurants r
             LEFT JOIN users u ON u.id = r.owner_id
             WHERE r.id = ? LIMIT 1'
        );
        $stmt->execute([$restaurantId]);
        $h = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $houseName = (string) ($h['name'] ?? $houseName);
        $inviteCode = (string) ($h['invite_code'] ?? '');
        $ownerEmail = strtolower(trim((string) ($h['owner_email'] ?? '')));
        $ownerUserId = (int) ($h['owner_id'] ?? 0);
    } catch (Throwable $e) {
        // ignore
    }
    // Don't email yourself when you create / re-link your own house
    if ($ownerUserId > 0 && $joinerUserId === $ownerUserId) {
        return;
    }

    $joiner = ['full_name' => '', 'username' => '', 'email' => ''];
    try {
        $stmt = $pdo->prepare('SELECT full_name, username, email FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$joinerUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $joiner = $row;
        }
    } catch (Throwable $e) {
        // ignore
    }

    $display = trim((string) ($joiner['full_name'] ?: $joiner['username'] ?: 'Someone'));
    $jEmail = (string) ($joiner['email'] ?? '');
    $roleLabel = strtoupper($role);
    $base = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
    $isDemo = pbj_restaurant_is_demo_house($pdo, $restaurantId);

    // —— Owner note ——
    $ownerBody = "Hey!\n\n"
        . "{$display} just joined {$houseName} on ilovepbj ops.\n\n"
        . "Details:\n"
        . " · Name: {$display}\n"
        . " · Username: " . ($joiner['username'] ?? '—') . "\n"
        . " · Email: " . ($jEmail !== '' ? $jEmail : '—') . "\n"
        . " · Role: {$roleLabel}\n"
        . ($inviteCode !== '' ? " · House code they used: {$inviteCode}\n" : '')
        . ($isDemo ? "\n(This is your demo / playground house — unlimited seats. You can remove them anytime under Team → Invite & Onboarding.)\n" : '')
        . "\nManage house logins:\n{$base}/admin/onboarding\n"
        . "\n— ilovepbj ops\n";

    foreach (pbj_restaurant_owner_emails($pdo, $restaurantId) as $to) {
        pbj_send_mail(
            $to,
            "New teammate on {$houseName}: {$display}",
            $ownerBody,
            $jEmail
        );
    }

    // —— Joiner / new employee note ——
    if ($jEmail !== '' && filter_var($jEmail, FILTER_VALIDATE_EMAIL)) {
        $welcome = "Hi {$display},\n\n"
            . "You're in — welcome to {$houseName} on ilovepbj ops 💕\n\n"
            . "Log in anytime: {$base}/login\n"
            . "Use the username or email you signed up with.\n\n"
            . ($isDemo
                ? "This may be a trial / playground house. Explore freely — the host can remove access when you're done, or you can open your own house anytime from the home page.\n\n"
                : "Your house owner got a heads-up that you joined. If you need permissions or a different role, ask them.\n\n")
            . "Questions? Email nutsaboutpbj@ilovepbj.shop\n\n"
            . "— the ilovepbj ops team\n";
        pbj_send_mail(
            $jEmail,
            "Welcome to {$houseName} · ilovepbj ops",
            $welcome,
            $ownerEmail !== '' ? $ownerEmail : 'nutsaboutpbj@ilovepbj.shop'
        );
    }
}

/**
 * Login accounts linked to a restaurant (for owner member desk).
 * @return list<array{user_id:int,username:string,email:string,full_name:string,role:string,access_status:string,joined_at:?string}>
 */
function pbj_restaurant_members(PDO $pdo, int $restaurantId): array {
    if ($restaurantId <= 0) {
        return [];
    }
    try {
        $stmt = $pdo->prepare(
            'SELECT u.id AS user_id, u.username, u.email, u.full_name, u.access_status,
                    ur.role, ur.joined_at
             FROM user_restaurant ur
             JOIN users u ON u.id = ur.user_id
             WHERE ur.restaurant_id = ?
             ORDER BY
               CASE ur.role WHEN \'owner\' THEN 0 WHEN \'gm\' THEN 1 WHEN \'admin\' THEN 2 WHEN \'manager\' THEN 3 ELSE 4 END,
               ur.joined_at ASC'
        );
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Remove a login from a house (does not delete their account).
 * Cannot remove the restaurant owner_id.
 */
function pbj_remove_restaurant_member(PDO $pdo, int $restaurantId, int $userId, int $actorUserId): array {
    if ($restaurantId <= 0 || $userId <= 0) {
        return ['ok' => false, 'error' => 'bad_request'];
    }
    try {
        $own = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1');
        $own->execute([$restaurantId]);
        $ownerId = (int) $own->fetchColumn();
        if ($ownerId > 0 && $userId === $ownerId) {
            return ['ok' => false, 'error' => 'Cannot remove the house owner. Transfer ownership first.'];
        }
        // Actor must be owner / platform admin / owner role on house
        $actorOk = false;
        if ($actorUserId === $ownerId) {
            $actorOk = true;
        }
        if (!$actorOk) {
            $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$actorUserId]);
            if (pbj_is_platform_admin((string) $stmt->fetchColumn())) {
                $actorOk = true;
            }
        }
        if (!$actorOk) {
            $stmt = $pdo->prepare(
                "SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ? LIMIT 1"
            );
            $stmt->execute([$actorUserId, $restaurantId]);
            $r = (string) $stmt->fetchColumn();
            if (in_array($r, ['owner', 'gm', 'admin'], true)) {
                $actorOk = true;
            }
        }
        if (!$actorOk) {
            return ['ok' => false, 'error' => 'Only owners can remove house members.'];
        }

        $del = $pdo->prepare('DELETE FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
        $del->execute([$userId, $restaurantId]);
        if ($del->rowCount() < 1) {
            return ['ok' => false, 'error' => 'That person isn’t on this house.'];
        }
        return ['ok' => true];
    } catch (Throwable $e) {
        error_log('pbj_remove_restaurant_member: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not remove member.'];
    }
}

/**
 * True for demo / sales playground kitchens (DEMO-PBJ, SALES-PBJ, or flagged settings).
 * Real paid / free houses return false even when seats are unlimited.
 */
function pbj_restaurant_is_playground(PDO $pdo, int $restaurantId): bool {
    if ($restaurantId <= 0) {
        return false;
    }
    if (function_exists('pbj_restaurant_is_playground_resetable')
        && pbj_restaurant_is_playground_resetable($pdo, $restaurantId)) {
        return true;
    }
    try {
        $stmt = $pdo->prepare('SELECT invite_code FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $code = strtoupper(trim((string) $stmt->fetchColumn()));
        $codes = function_exists('pbj_playground_invite_codes')
            ? pbj_playground_invite_codes()
            : ['DEMO-PBJ', 'SALES-PBJ'];
        if ($code !== '' && in_array($code, $codes, true)) {
            return true;
        }
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $s = json_decode((string) $raw, true);
            if (is_array($s) && (
                !empty($s['playground_reset'])
                || !empty($s['sales_playground'])
                || !empty($s['demo_house'])
            )) {
                return true;
            }
        }
    } catch (Throwable $e) {
        return false;
    }
    return false;
}

/**
 * Remove a user from demo/sales playground memberships (guest seats only).
 * Used when they start or pay for their own house so they convert to the
 * restaurant / paid side instead of lingering on playground rosters.
 * Never removes membership if they own that playground restaurant.
 *
 * @return int number of memberships removed
 */
function pbj_detach_user_from_playgrounds(PDO $pdo, int $userId): int {
    if ($userId <= 0) {
        return 0;
    }
    $removed = 0;
    try {
        $playgroundIds = [];
        if (function_exists('pbj_sales_playground_restaurant_ids')) {
            $playgroundIds = pbj_sales_playground_restaurant_ids($pdo);
        }
        $codes = function_exists('pbj_playground_invite_codes')
            ? pbj_playground_invite_codes()
            : ['DEMO-PBJ', 'SALES-PBJ'];
        foreach ($codes as $code) {
            $stmt = $pdo->prepare('SELECT id FROM restaurants WHERE UPPER(invite_code) = ?');
            $stmt->execute([strtoupper((string) $code)]);
            while ($id = $stmt->fetchColumn()) {
                $playgroundIds[] = (int) $id;
            }
        }
        // Also catch memberships on houses flagged as playground in settings
        $stmt = $pdo->prepare(
            'SELECT ur.restaurant_id FROM user_restaurant ur WHERE ur.user_id = ?'
        );
        $stmt->execute([$userId]);
        while ($rid = $stmt->fetchColumn()) {
            $rid = (int) $rid;
            if ($rid > 0 && pbj_restaurant_is_playground($pdo, $rid)) {
                $playgroundIds[] = $rid;
            }
        }
        $playgroundIds = array_values(array_unique(array_filter(array_map('intval', $playgroundIds))));
        foreach ($playgroundIds as $rid) {
            if ($rid <= 0) {
                continue;
            }
            // Keep playground owners (platform admin) on their houses
            $own = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1');
            $own->execute([$rid]);
            if ((int) $own->fetchColumn() === $userId) {
                continue;
            }
            $del = $pdo->prepare('DELETE FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
            $del->execute([$userId, $rid]);
            $removed += (int) $del->rowCount();
        }
        if ($removed > 0) {
            error_log("pbj_detach_user_from_playgrounds: user {$userId} removed from {$removed} playground seat(s)");
        }
    } catch (Throwable $e) {
        error_log('pbj_detach_user_from_playgrounds: ' . $e->getMessage());
    }
    return $removed;
}

/**
 * True if user is linked to any non-playground restaurant (paying house or staff on one).
 * Platform admin may not set their password directly — only send a reset link.
 */
function pbj_user_is_on_paying_house(PDO $pdo, int $userId): bool {
    if ($userId <= 0) {
        return false;
    }
    try {
        $stmt = $pdo->prepare(
            'SELECT r.id, r.invite_code FROM user_restaurant ur
             JOIN restaurants r ON r.id = ur.restaurant_id
             WHERE ur.user_id = ?'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$rows) {
            // Also check ownership without membership row
            $own = $pdo->prepare('SELECT id, invite_code FROM restaurants WHERE owner_id = ?');
            $own->execute([$userId]);
            $rows = $own->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        if (!$rows) {
            return false; // solo / no house yet — treat as editable incl. password
        }
        foreach ($rows as $r) {
            $rid = (int) ($r['id'] ?? 0);
            $code = strtoupper(trim((string) ($r['invite_code'] ?? '')));
            $playgroundCodes = function_exists('pbj_playground_invite_codes')
                ? pbj_playground_invite_codes()
                : ['DEMO-PBJ', 'SALES-PBJ'];
            if (in_array($code, $playgroundCodes, true)) {
                continue;
            }
            if (function_exists('pbj_restaurant_is_playground_resetable') && pbj_restaurant_is_playground_resetable($pdo, $rid)) {
                continue;
            }
            if (function_exists('pbj_restaurant_is_demo_house') && pbj_restaurant_is_demo_house($pdo, $rid)) {
                // unlimited demo still "playground" unless it's a real paid plan house
                $plan = function_exists('pbj_restaurant_plan_id') ? pbj_restaurant_plan_id($pdo, $rid) : '';
                $billing = '';
                try {
                    $s = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ? LIMIT 1');
                    $s->execute([$rid]);
                    $raw = $s->fetchColumn();
                    $js = $raw ? json_decode((string) $raw, true) : [];
                    if (is_array($js)) {
                        $billing = (string) ($js['billing_status'] ?? '');
                        if (!empty($js['playground_reset']) || !empty($js['sales_playground']) || !empty($js['demo_house'])) {
                            continue;
                        }
                    }
                } catch (Throwable $e) {
                    // ignore
                }
            }
            // Real house
            return true;
        }
        return false;
    } catch (Throwable $e) {
        return false;
    }
}

/** Allow platform admin to type a new password (playground / free / solo only). */
function pbj_user_allows_direct_password_set(PDO $pdo, int $userId): bool {
    return !pbj_user_is_on_paying_house($pdo, $userId);
}

/**
 * Email a password-reset link (admin-triggered).
 * @return array{ok:bool,message?:string,error?:string,link?:string}
 */
function pbj_admin_send_password_reset(PDO $pdo, int $userId): array {
    if ($userId <= 0) {
        return ['ok' => false, 'error' => 'Invalid user.'];
    }
    try {
        $stmt = $pdo->prepare('SELECT id, email, username, full_name FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return ['ok' => false, 'error' => 'User not found.'];
        }
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $pdo->prepare(
            'UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?'
        )->execute([$token, $expires, $userId]);

        $base = defined('APP_PUBLIC_URL') ? rtrim((string) APP_PUBLIC_URL, '/') : 'https://ilovepbj.shop';
        $link = $base . '/reset-password?token=' . urlencode($token);
        $to = (string) ($user['email'] ?? '');
        $name = (string) ($user['full_name'] ?: $user['username']);
        $mailed = false;
        if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Reset your ilovepbj password';
            $body = "Hi {$name},\n\n"
                . "Your platform admin started a password reset for your ilovepbj ops account.\n\n"
                . "Open this link within 1 hour to choose a new password:\n{$link}\n\n"
                . "If you did not expect this, contact nutsaboutpbj@ilovepbj.shop.\n";
            if (function_exists('pbj_send_mail')) {
                $mailed = pbj_send_mail($to, $subject, $body);
            } else {
                $headers = "From: noreply@" . (defined('APP_DOMAIN') ? APP_DOMAIN : 'ilovepbj.shop') . "\r\n"
                    . "Content-Type: text/plain; charset=UTF-8\r\n";
                $mailed = @mail($to, $subject, $body, $headers);
            }
        }
        $smtpOk = function_exists('pbj_mail_is_configured') && pbj_mail_is_configured();
        error_log(
            'admin password reset for user #' . $userId
            . ' mailed=' . ($mailed ? '1' : '0')
            . ' smtp_configured=' . ($smtpOk ? '1' : '0')
        );
        if ($mailed) {
            $msg = 'Reset email sent to ' . $to . '.';
        } elseif ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Reset link created, but this user has no valid email — copy the link below.';
        } elseif (!$smtpOk) {
            $msg = 'Reset link created. Outbound mail is not configured yet (add Hostinger SMTP secrets) — copy the link below.';
        } else {
            $msg = 'Reset link created, but the email failed to send — copy the link below and check server logs.';
        }
        return [
            'ok' => true,
            'message' => $msg,
            'link' => $link,
        ];
    } catch (Throwable $e) {
        error_log('pbj_admin_send_password_reset: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not create reset link.'];
    }
}

/**
 * House owner/manager: update an employee on their house (name, username, email, role, access).
 * Password: reset email only (never set password directly for staff).
 * @return array{ok:bool,message?:string,error?:string}
 */
function pbj_owner_save_employee_profile(PDO $pdo, int $restaurantId, int $targetId, int $actorId, array $fields): array {
    if ($restaurantId <= 0 || $targetId <= 0 || $actorId <= 0) {
        return ['ok' => false, 'error' => 'Invalid request.'];
    }
    if (!pbj_can_manage_house_members($pdo, $restaurantId, $actorId)) {
        return ['ok' => false, 'error' => 'Only house owners/managers can edit team logins.'];
    }
    // Target must be on this house
    try {
        $chk = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ? LIMIT 1');
        $chk->execute([$targetId, $restaurantId]);
        if ($chk->fetchColumn() === false) {
            return ['ok' => false, 'error' => 'That person isn’t on this house.'];
        }
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Could not verify membership.'];
    }

    $own = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1');
    $own->execute([$restaurantId]);
    $ownerId = (int) $own->fetchColumn();

    $fullName = trim((string) ($fields['full_name'] ?? ''));
    $username = trim((string) ($fields['username'] ?? ''));
    $newEmail = trim((string) ($fields['email'] ?? ''));
    $role = strtolower(trim((string) ($fields['role'] ?? 'foh')));
    $access = strtolower(trim((string) ($fields['access_status'] ?? 'approved')));
    // DB enum: owner, manager, boh, foh, admin
    if ($role === 'gm') {
        $role = 'admin';
    }
    $allowedRoles = ['owner', 'manager', 'foh', 'boh', 'admin'];
    $allowedAccess = ['pending', 'approved', 'blocked'];

    if ($fullName === '' || $username === '' || $newEmail === '') {
        return ['ok' => false, 'error' => 'Name, username, and email are required.'];
    }
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Enter a valid email address.'];
    }
    if (!in_array($role, $allowedRoles, true)) {
        return ['ok' => false, 'error' => 'Invalid role.'];
    }
    if (!in_array($access, $allowedAccess, true)) {
        return ['ok' => false, 'error' => 'Invalid access status.'];
    }
    if ($ownerId > 0 && $targetId === $ownerId) {
        $role = 'owner';
        if ($access === 'blocked') {
            return ['ok' => false, 'error' => 'Cannot block the house owner.'];
        }
        $access = 'approved';
    }

    $dup = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1');
    $dup->execute([$username, $newEmail, $targetId]);
    if ($dup->fetchColumn()) {
        return ['ok' => false, 'error' => 'That username or email is already used by another account.'];
    }

    try {
        $pdo->prepare(
            'UPDATE users SET full_name = ?, username = ?, email = ?, role = ?, access_status = ? WHERE id = ?'
        )->execute([$fullName, $username, $newEmail, $role, $access, $targetId]);
        $pdo->prepare('UPDATE user_restaurant SET role = ? WHERE user_id = ? AND restaurant_id = ?')
            ->execute([$role, $targetId, $restaurantId]);
        return ['ok' => true, 'message' => 'Updated ' . $fullName . ' (‘@' . $username . ').'];
    } catch (Throwable $e) {
        error_log('pbj_owner_save_employee_profile: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Update failed. Try again.'];
    }
}

/**
 * Platform admin: update profile fields. Password set only when allowed.
 * @return array{ok:bool,message?:string,error?:string}
 */
function pbj_admin_save_user_profile(PDO $pdo, int $targetId, array $fields): array {
    if ($targetId <= 0) {
        return ['ok' => false, 'error' => 'Invalid user.'];
    }
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, role, access_status FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$targetId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        return ['ok' => false, 'error' => 'User not found.'];
    }

    $fullName = trim((string) ($fields['full_name'] ?? ''));
    $username = trim((string) ($fields['username'] ?? ''));
    $newEmail = trim((string) ($fields['email'] ?? ''));
    $role = strtolower(trim((string) ($fields['role'] ?? 'foh')));
    $access = strtolower(trim((string) ($fields['access_status'] ?? 'pending')));
    $pass1 = (string) ($fields['new_password'] ?? '');
    $pass2 = (string) ($fields['new_password_confirm'] ?? '');

    // DB enum: owner, manager, boh, foh, admin (gm is app alias → store as admin)
    if ($role === 'gm') {
        $role = 'admin';
    }
    $allowedRoles = ['owner', 'manager', 'foh', 'boh', 'admin'];
    $allowedAccess = ['pending', 'approved', 'blocked'];

    if ($fullName === '' || $username === '' || $newEmail === '') {
        return ['ok' => false, 'error' => 'Name, username, and email are required.'];
    }
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Enter a valid email address.'];
    }
    if (!in_array($role, $allowedRoles, true)) {
        return ['ok' => false, 'error' => 'Invalid role.'];
    }
    if (!in_array($access, $allowedAccess, true)) {
        return ['ok' => false, 'error' => 'Invalid access status.'];
    }

    $allowPass = pbj_user_allows_direct_password_set($pdo, $targetId);
    if ($pass1 !== '' || $pass2 !== '') {
        if (!$allowPass) {
            return ['ok' => false, 'error' => 'This account is on a paying house — use “Send password reset” instead of setting a password.'];
        }
        if ($pass1 !== $pass2) {
            return ['ok' => false, 'error' => 'New passwords do not match.'];
        }
        if (strlen($pass1) < 6) {
            return ['ok' => false, 'error' => 'Password must be at least 6 characters.'];
        }
    }

    $dup = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1');
    $dup->execute([$username, $newEmail, $targetId]);
    if ($dup->fetchColumn()) {
        return ['ok' => false, 'error' => 'That username or email is already used by another account.'];
    }

    try {
        if ($pass1 !== '' && $allowPass) {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $upd = $pdo->prepare(
                'UPDATE users SET full_name = ?, username = ?, email = ?, role = ?, access_status = ?,
                 password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?'
            );
            $upd->execute([$fullName, $username, $newEmail, $role, $access, $hash, $targetId]);
            return ['ok' => true, 'message' => 'Updated profile and set a new password.'];
        }
        $upd = $pdo->prepare(
            'UPDATE users SET full_name = ?, username = ?, email = ?, role = ?, access_status = ? WHERE id = ?'
        );
        $upd->execute([$fullName, $username, $newEmail, $role, $access, $targetId]);
        return ['ok' => true, 'message' => 'Updated profile for ' . $username . '.'];
    } catch (Throwable $e) {
        error_log('pbj_admin_save_user_profile: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Update failed: ' . $e->getMessage()];
    }
}

/** Whether actor can manage house logins (edit role / access / remove). */
function pbj_can_manage_house_members(PDO $pdo, int $restaurantId, int $actorUserId): bool {
    if ($restaurantId <= 0 || $actorUserId <= 0) {
        return false;
    }
    try {
        $own = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1');
        $own->execute([$restaurantId]);
        $ownerId = (int) $own->fetchColumn();
        if ($ownerId > 0 && $actorUserId === $ownerId) {
            return true;
        }
        $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$actorUserId]);
        if (pbj_is_platform_admin((string) $stmt->fetchColumn())) {
            return true;
        }
        $stmt = $pdo->prepare(
            "SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ? LIMIT 1"
        );
        $stmt->execute([$actorUserId, $restaurantId]);
        $r = (string) $stmt->fetchColumn();
        return in_array($r, ['owner', 'gm', 'admin', 'manager'], true);
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Edit a house member’s membership role and/or access_status.
 * @return array{ok:bool,error?:string}
 */
function pbj_update_restaurant_member(PDO $pdo, int $restaurantId, int $userId, int $actorUserId, array $patch): array {
    if ($restaurantId <= 0 || $userId <= 0) {
        return ['ok' => false, 'error' => 'bad_request'];
    }
    if (!pbj_can_manage_house_members($pdo, $restaurantId, $actorUserId)) {
        return ['ok' => false, 'error' => 'Only house managers can edit logins.'];
    }
    try {
        $own = $pdo->prepare('SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1');
        $own->execute([$restaurantId]);
        $ownerId = (int) $own->fetchColumn();

        $chk = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ? LIMIT 1');
        $chk->execute([$userId, $restaurantId]);
        $memRole = $chk->fetchColumn();
        if ($memRole === false) {
            return ['ok' => false, 'error' => 'That person isn’t on this house.'];
        }

        if (array_key_exists('role', $patch)) {
            $newRole = strtolower(trim((string) $patch['role']));
            $allowed = ['owner', 'manager', 'boh', 'foh', 'admin', 'gm'];
            if (!in_array($newRole, $allowed, true)) {
                return ['ok' => false, 'error' => 'Invalid role.'];
            }
            // Keep billable account owner as membership owner
            if ($ownerId > 0 && $userId === $ownerId) {
                $newRole = 'owner';
            }
            $pdo->prepare('UPDATE user_restaurant SET role = ? WHERE user_id = ? AND restaurant_id = ?')
                ->execute([$newRole, $userId, $restaurantId]);
            // Sync global role unless they're owner of another house
            if (!($ownerId > 0 && $userId === $ownerId)) {
                $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$newRole === 'admin' ? 'gm' : $newRole, $userId]);
            }
        }

        if (array_key_exists('access_status', $patch)) {
            $st = strtolower(trim((string) $patch['access_status']));
            if (!in_array($st, ['pending', 'approved', 'blocked'], true)) {
                return ['ok' => false, 'error' => 'Invalid access status.'];
            }
            // Don't block the account owner of the house
            if ($ownerId > 0 && $userId === $ownerId && $st === 'blocked') {
                return ['ok' => false, 'error' => 'Cannot block the house owner.'];
            }
            $pdo->prepare('UPDATE users SET access_status = ? WHERE id = ?')->execute([$st, $userId]);
        }

        return ['ok' => true];
    } catch (Throwable $e) {
        error_log('pbj_update_restaurant_member: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not update member.'];
    }
}

/** Flag a restaurant as the unlimited demo / sales playground. */
function pbj_mark_restaurant_demo(PDO $pdo, int $restaurantId, bool $demo = true): void {
    if ($restaurantId <= 0) {
        return;
    }
    try {
        $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
        $stmt->execute([$restaurantId]);
        $raw = $stmt->fetchColumn();
        $settings = $raw ? (json_decode((string) $raw, true) ?: []) : [];
        $settings['demo_house'] = $demo;
        $settings['unlimited_seats'] = $demo;
        if ($demo && empty($settings['plan'])) {
            $settings['plan'] = 'custom';
        }
        $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
        if ($raw !== false && $raw !== null) {
            $pdo->prepare('UPDATE restaurant_settings SET settings_json = ?, updated_at = NOW() WHERE restaurant_id = ?')
                ->execute([$json, $restaurantId]);
        } else {
            $pdo->prepare('INSERT INTO restaurant_settings (restaurant_id, settings_json, updated_at) VALUES (?, ?, NOW())')
                ->execute([$restaurantId, $json]);
        }
    } catch (Throwable $e) {
        error_log('pbj_mark_restaurant_demo: ' . $e->getMessage());
    }
}

function pbj_post_auth_redirect(PDO $pdo, string $afterTheme = '/home'): void {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0 && !(defined('AUTH_BYPASS') && AUTH_BYPASS)) {
        header('Location: /login');
        exit();
    }
    if ($uid > 0 && !pbj_user_is_approved()) {
        $st = $_SESSION['access_status'] ?? 'pending';
        header('Location: /waiting' . ($st === 'blocked' ? '?blocked=1' : ''));
        exit();
    }
    if ($uid > 0 && function_exists('pbj_user_billing_allows_access') && !pbj_user_billing_allows_access($pdo, $uid)) {
        $info = function_exists('pbj_trial_info') ? pbj_trial_info($pdo, $uid) : [];
        $qs = !empty($info['expired']) ? 'pay=trial_expired' : 'pay=needed';
        header('Location: /waiting?' . $qs);
        exit();
    }
    // Remember where to go after first theme pick (welcome banners, etc.)
    $afterTheme = pbj_normalize_app_redirect($afterTheme !== '' ? $afterTheme : '/home', '/home');
    if (!pbj_user_has_chosen_theme()) {
        $_SESSION['after_theme_url'] = $afterTheme;
        header('Location: /choose-theme');
        exit();
    }
    header('Location: ' . $afterTheme);
    exit();
}
