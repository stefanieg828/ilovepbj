<?php
require_once 'config.php';
require_once __DIR__ . '/stripe-config.php';

if (empty($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
    header('Location: /login');
    exit();
}

$uid = (int) ($_SESSION['user_id'] ?? 0);
$joined = !empty($_GET['joined']);
$created = !empty($_GET['created']);

// Invite joiners who landed here stuck as "pending": approve if they're on a house
// Do NOT auto-approve archived or blocked accounts
if ($uid > 0 && !pbj_user_is_approved()) {
    $gateStatus = $_SESSION['access_status'] ?? 'pending';
    if (!in_array($gateStatus, ['archived', 'blocked'], true)) {
        try {
            $onHouse = $pdo->prepare('SELECT restaurant_id FROM user_restaurant WHERE user_id = ? LIMIT 1');
            $onHouse->execute([$uid]);
            if ($onHouse->fetchColumn()) {
                pbj_approve_invite_joiner($pdo, $uid);
                $_SESSION['access_status'] = 'approved';
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
}

// If already approved, go in (theme picker first if needed)
if (pbj_user_is_approved()) {
    pbj_post_auth_redirect($pdo, $joined ? '/home?joined=1' : '/home');
}

$blocked = !empty($_GET['blocked']) || (($_SESSION['access_status'] ?? '') === 'blocked');
$archived = !empty($_GET['archived']) || (($_SESSION['access_status'] ?? '') === 'archived');
$pay = (string) ($_GET['pay'] ?? '');
$code = $_SESSION['pending_invite_code'] ?? '';
$name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'friend';
$email = $_SESSION['email'] ?? '';
$isAdmin = pbj_is_platform_admin($email);
$planId = (string) ($_SESSION['pending_plan_id'] ?? pbj_default_plan_id());
$trialInfo = function_exists('pbj_trial_info') ? pbj_trial_info($pdo, $uid) : [];
if (!empty($trialInfo['plan_id'])) {
    $planId = (string) $trialInfo['plan_id'];
}
$stripeReady = function_exists('stripe_is_configured') && stripe_is_configured() && stripe_price_id_for_plan($planId);
$trialExpired = ($pay === 'trial_expired') || !empty($trialInfo['expired']);
$trialActive = !empty($trialInfo['active']);
// House starters / trial converts pay — invite-code joiners never see Checkout
$canPay = $stripeReady && !$joined && !$blocked && !$archived && ($trialExpired || $trialActive || $pay === 'needed' || $created || $pay !== '');
if ($joined && !$trialExpired) {
    $canPay = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $archived ? 'Account archived' : ($blocked ? 'Access paused' : 'Almost there'); ?> · ilovepbj ops</title>
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; font-family: 'DreamingOutLoudPro', Georgia, serif;
            background:
                radial-gradient(ellipse 70% 40% at 10% 0%, rgba(187,231,218,0.5), transparent 55%),
                radial-gradient(ellipse 60% 40% at 100% 10%, rgba(107,74,140,0.12), transparent 50%),
                #FCF8EE;
            color: #3a2f1f;
        }
        .top {
            background: linear-gradient(105deg, #E55163 0%, #6B4A8C 100%);
            color: white; padding: 20px; text-align: center;
        }
        h1 { font-family: 'ModernLoveCaps', serif; margin: 0; font-size: 2.2rem; }
        .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 50px; }
        .card {
            background: white; border-radius: 20px; padding: 28px 24px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.08); border: 3px solid #BBE7DA;
        }
        .card h2 { font-family: 'ModernLoveCaps', serif; color: #E55163; margin: 0 0 12px; font-size: 1.6rem; text-align: center; }
        .card.blocked h2 { color: #6B4A8C; }
        p { line-height: 1.5; opacity: 0.9; margin: 0 0 12px; }
        .code {
            display: inline-block; margin: 8px 0; padding: 10px 16px; border-radius: 12px;
            background: #FFF5F6; color: #E55163; border: 2px dashed #F3C5CC;
            letter-spacing: 0.1em; font-size: 1.2rem;
        }
        .note {
            background: #F3EEF8; border: 1px solid rgba(107,74,140,0.25); border-radius: 14px;
            padding: 12px 14px; margin: 14px 0; font-size: 0.95rem; line-height: 1.45; color: #6B4A8C;
        }
        .btn {
            display: block; width: 100%; text-align: center; border-radius: 14px; padding: 14px;
            text-decoration: none; font-family: inherit; margin-top: 10px; border: none; cursor: pointer; font-size: 1.05rem;
        }
        .btn-primary { background: #E55163; color: white; }
        .btn-purple { background: #6B4A8C; color: #BBE7DA; }
        .btn-ghost { background: white; color: #3a2f1f; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .meta { text-align: center; font-size: 0.9rem; opacity: 0.7; margin-top: 16px; }
    </style>
    <meta http-equiv="refresh" content="45">
</head>
<body>
    <div class="top">
        <h1>ilovepbj ops</h1>
    </div>
    <div class="wrap">
        <div class="card<?php echo ($blocked || $archived) ? ' blocked' : ''; ?>">
            <?php if ($archived): ?>
                <h2>Account archived</h2>
                <p>Hey <?php echo htmlspecialchars($name); ?> — this account is archived and can’t use the ops hub right now.</p>
                <p><strong>Reset your password to reactivate.</strong> After you choose a new password, your account becomes approved again and you can log in normally.</p>
                <a class="btn btn-primary" href="/forgot-password">Forgot password / reactivate →</a>
                <p style="font-size:0.95rem;opacity:0.75;margin-top:12px;">If you didn’t expect this, contact your house owner or <strong>nutsaboutpbj@ilovepbj.shop</strong>.</p>
            <?php elseif ($blocked): ?>
                <h2>Access paused</h2>
                <p>Hey <?php echo htmlspecialchars($name); ?> — this account isn’t cleared for the ops hub right now.</p>
                <p>If you think that’s a mistake, reach out to the person who invited you (or the house that runs ilovepbj ops).</p>
            <?php elseif ($trialExpired): ?>
                <h2>Trial ended 💕</h2>
                <p>Hey <strong><?php echo htmlspecialchars($name); ?></strong> — your
                    <?php echo (int) (function_exists('pbj_trial_days') ? pbj_trial_days() : 14); ?>-day free trial is over.
                    Subscribe to keep your house, team data, and invite codes.</p>
                <?php if ($pay === 'cancel'): ?>
                    <div class="note">Checkout canceled — no charge. Your kitchen stays paused until you subscribe.</div>
                <?php elseif ($pay === 'error'): ?>
                    <div class="note">We couldn’t start Checkout<?php
                        $reason = trim((string) ($_GET['reason'] ?? ''));
                        echo $reason !== '' ? ' (' . htmlspecialchars($reason) . ')' : '';
                    ?>. Try again below.</div>
                <?php endif; ?>
                <?php if ($code !== ''): ?>
                    <p>Your house invite code (still yours after you subscribe):</p>
                    <div style="text-align:center;"><span class="code"><?php echo htmlspecialchars($code); ?></span></div>
                <?php endif; ?>
                <?php if ($canPay): ?>
                    <a class="btn btn-primary" href="/billing/checkout?plan=<?php echo htmlspecialchars(urlencode($planId)); ?>&amp;trial=1">Subscribe with card (Stripe) →</a>
                    <p style="font-size:0.9rem;opacity:0.75;text-align:center;margin-top:8px;">Secure checkout · monthly plan · cancel anytime.</p>
                <?php elseif ($joined): ?>
                    <div class="note">Ask your <strong>house owner</strong> to subscribe — staff don’t pay. Once they do, refresh and you’ll be back in.</div>
                <?php else: ?>
                    <div class="note">Stripe isn’t ready on this server yet — email <strong>nutsaboutpbj@ilovepbj.shop</strong> and we’ll help.</div>
                <?php endif; ?>
            <?php else: ?>
                <h2><?php echo $canPay ? 'Almost in 💕' : 'Account received 💕'; ?></h2>
                <p>Hey <strong><?php echo htmlspecialchars($name); ?></strong> —
                    <?php if ($canPay): ?>
                        your account is ready. Finish with a quick card payment (Stripe) to unlock the full ops hub.
                    <?php else: ?>
                        you’re signed up. A house owner or admin needs to green-light your access (or the house subscription needs to be active).
                    <?php endif; ?>
                </p>
                <?php if ($pay === 'cancel'): ?>
                    <div class="note">Payment canceled — no charge. You can try again anytime.</div>
                <?php elseif ($pay === 'error'): ?>
                    <div class="note">We couldn’t start Checkout<?php
                        $reason = trim((string) ($_GET['reason'] ?? ''));
                        echo $reason !== '' ? ' (' . htmlspecialchars($reason) . ')' : '';
                    ?>. Try the pay button again — if it keeps failing, tell us what this note says.</div>
                <?php elseif ($pay === 'not_configured'): ?>
                    <div class="note">Stripe keys aren’t on the server yet. Add <code>stripe-secrets.local.php</code> (see the example file).</div>
                <?php endif; ?>
                <?php if ($created && $code !== ''): ?>
                    <p>Your restaurant was created. Save this house invite code for your team:</p>
                    <div style="text-align:center;"><span class="code"><?php echo htmlspecialchars($code); ?></span></div>
                <?php elseif ($joined): ?>
                    <p>You’re linked to a house — once the house is active / you’re approved, you’ll walk straight into that kitchen.</p>
                <?php endif; ?>
                <?php if ($canPay): ?>
                    <a class="btn btn-primary" href="/billing/checkout?plan=<?php echo htmlspecialchars(urlencode($planId)); ?>&amp;trial=1">Pay with card (Stripe) →</a>
                    <p style="font-size:0.9rem;opacity:0.75;text-align:center;margin-top:8px;">Secure checkout on Stripe.</p>
                <?php else: ?>
                    <div class="note">
                        <strong>Free testers:</strong> if you were invited to try for free, hang tight — you’ll get in when you’re approved. No payment needed for approved testers.
                    </div>
                <?php endif; ?>
                <p style="font-size:0.95rem;opacity:0.75;">This page refreshes every so often. After you’re approved, refresh and you’ll land in the hub.</p>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <a class="btn btn-purple" href="/approve-users">Open approval desk</a>
            <?php endif; ?>
            <a class="btn <?php echo $canPay ? 'btn-ghost' : 'btn-primary'; ?>" href="/waiting">Check again</a>
            <a class="btn btn-ghost" href="/logout">Log out</a>
            <p class="meta"><?php echo htmlspecialchars($email); ?></p>
        </div>
    </div>
</body>
</html>
