<?php
/**
 * Plan & upgrade desk — owners can move up seat tiers.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe-config.php';

if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    header('Location: /login?next=' . urlencode('/billing/plans'));
    exit();
}

$uid = (int) $_SESSION['user_id'];
$ctx = pbj_billing_context($pdo, $uid);
$is_sweet = (($_SESSION['theme'] ?? 'sweet') === 'sweet') || function_exists('pbj_theme_id') && pbj_theme_id() !== 'basic';
// Prefer fun styling for sweet + neon/farm etc.
if (function_exists('pbj_theme_flags')) {
    $tf = pbj_theme_flags();
    $is_sweet = !empty($tf['fun']) || !empty($tf['classic_sweet']);
}

$current = $ctx['plan'];
$currentId = $ctx['plan_id'];
$upgrades = $ctx['upgrade_options'];
$seats = $ctx['seats'];
$canManage = !empty($ctx['can_manage']);
$flash = (string) ($_GET['ok'] ?? '');
$err = (string) ($_GET['err'] ?? '');
$pay = (string) ($_GET['pay'] ?? '');
$needsHouseName = ($ctx['restaurant_id'] <= 0);

$name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'friend';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Plans & upgrades' : 'Plans & upgrades'; ?> · ilovepbj ops</title>
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
            color: #3a2f1f; padding-bottom: 40px;
        }
        .top {
            background: linear-gradient(105deg, #E55163 0%, #6B4A8C 100%);
            color: white; padding: 18px 16px 22px; text-align: center;
        }
        .top a { color: white; text-decoration: none; opacity: 0.9; font-size: 0.95rem; }
        h1 { font-family: 'ModernLoveCaps', serif; margin: 8px 0 0; font-size: 2.2rem; }
        .wrap { max-width: 720px; margin: 0 auto; padding: 22px 16px; }
        .card {
            background: white; border-radius: 20px; padding: 22px 20px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.08); border: 3px solid #BBE7DA; margin-bottom: 16px;
        }
        .card h2 {
            font-family: 'ModernLoveCaps', serif; color: #E55163; margin: 0 0 10px; font-size: 1.55rem;
        }
        .card.upgrade { border-color: #E55163; }
        .card.current { border-color: #6B4A8C; background: linear-gradient(180deg, #F3EEF8 0%, white 50%); }
        p { line-height: 1.5; margin: 0 0 10px; opacity: 0.9; }
        .meta { font-size: 0.95rem; opacity: 0.8; margin: 0 0 8px; }
        .price { font-size: 1.35rem; color: #6B4A8C; font-weight: 600; margin: 4px 0 10px; }
        .badge {
            display: inline-block; border-radius: 999px; padding: 4px 12px; font-size: 0.85rem;
            background: #6B4A8C; color: #BBE7DA; margin-bottom: 8px;
        }
        .badge.pink { background: #E55163; color: white; }
        .badge.warn { background: #FFF3D6; color: #8A5A00; }
        .badge.ok { background: #E8F8F1; color: #1F6B4A; }
        ul { margin: 0 0 14px; padding-left: 1.1rem; opacity: 0.88; }
        li { margin: 4px 0; }
        .btn {
            display: block; width: 100%; text-align: center; border: none; border-radius: 14px;
            padding: 14px; font-size: 1.05rem; font-family: inherit; cursor: pointer;
            text-decoration: none; box-sizing: border-box;
        }
        .btn-primary { background: #E55163; color: white; }
        .btn-purple { background: #6B4A8C; color: #BBE7DA; }
        .btn-ghost { background: white; color: #3a2f1f; box-shadow: 0 3px 10px rgba(0,0,0,0.08); margin-top: 10px; }
        .btn[disabled], .btn.is-disabled { opacity: 0.55; pointer-events: none; }
        label { display: block; font-size: 0.88rem; opacity: 0.7; margin: 12px 0 4px; }
        input[type=text] {
            width: 100%; padding: 12px 14px; border: 2px solid #F3C5CC; border-radius: 12px;
            font-size: 1.05rem; font-family: inherit; background: #FFFBF8;
        }
        .note {
            background: #F3EEF8; border: 1px solid rgba(107,74,140,0.25); border-radius: 14px;
            padding: 12px 14px; margin: 0 0 14px; font-size: 0.95rem; line-height: 1.45; color: #6B4A8C;
        }
        .note.err { background: #FDECEA; color: #B71C1C; border-color: #F5C2C0; }
        .note.ok { background: #E8F8F1; color: #1F6B4A; border-color: #B7E4C7; }
        .hint { font-size: 0.9rem; opacity: 0.7; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="top">
        <a href="/settings/account">← Account</a>
        <h1><?php echo $is_sweet ? 'Plans & upgrades' : 'Plans & upgrades'; ?></h1>
    </div>
    <div class="wrap">
        <?php if ($pay === 'cancel'): ?>
            <div class="note">Checkout canceled — no change to your plan. You can try again anytime.</div>
        <?php endif; ?>
        <?php if ($flash === 'upgraded'): ?>
            <div class="note ok">You’re upgraded 💕 Seat limits and features update right away.</div>
        <?php endif; ?>
        <?php if ($err !== ''): ?>
            <div class="note err"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>

        <?php
        $trialInfo = function_exists('pbj_trial_info') ? pbj_trial_info($pdo, $uid) : [];
        $billingStatus = strtolower((string) (($ctx['billing']['status'] ?? '') ?: ''));
        ?>
        <div class="card current">
            <span class="badge">Current plan</span>
            <h2><?php echo htmlspecialchars($current['name'] ?? 'Plan'); ?></h2>
            <div class="price"><?php echo htmlspecialchars($current['price'] ?? '—'); ?>
                <?php if (!empty($current['price_note'])): ?>
                    <span style="font-size:0.9rem;opacity:0.75;font-weight:400;"> · <?php echo htmlspecialchars($current['price_note']); ?></span>
                <?php endif; ?>
            </div>
            <p class="meta"><?php echo htmlspecialchars($current['tagline'] ?? ''); ?></p>
            <?php if (!empty($trialInfo['active'])): ?>
                <p class="meta">
                    <span class="badge ok">Free trial</span>
                    <strong><?php echo (int) $trialInfo['days_left']; ?> day<?php echo (int) $trialInfo['days_left'] === 1 ? '' : 's'; ?> left</strong>
                    · ends <?php echo htmlspecialchars($trialInfo['ends_label']); ?>
                </p>
                <?php if ($canManage): ?>
                    <a class="btn btn-primary" style="margin-top:10px;" href="/billing/checkout?plan=<?php echo htmlspecialchars(urlencode($currentId)); ?>&amp;trial=1">
                        Subscribe now (keep everything after trial)
                    </a>
                    <p class="hint">No charge until you complete Stripe checkout. Your trial clock keeps running either way.</p>
                <?php endif; ?>
            <?php elseif (!empty($trialInfo['expired'])): ?>
                <p class="meta"><span class="badge warn">Trial ended</span> Subscribe to reopen your kitchen.</p>
                <?php if ($canManage): ?>
                    <a class="btn btn-primary" style="margin-top:10px;" href="/billing/checkout?plan=<?php echo htmlspecialchars(urlencode($currentId)); ?>&amp;trial=1">
                        Subscribe with card →
                    </a>
                <?php endif; ?>
            <?php elseif ($billingStatus === 'paid' || !empty($trialInfo['paid'])): ?>
                <p class="meta"><span class="badge ok">Subscribed</span> Billing is active via Stripe.</p>
            <?php endif; ?>
            <?php if ($ctx['restaurant_name'] !== ''): ?>
                <p class="meta">House: <strong><?php echo htmlspecialchars($ctx['restaurant_name']); ?></strong></p>
            <?php elseif (!empty($ctx['playground_only'])): ?>
                <p class="meta">You’re on a demo / sales playground right now — pick a plan below to open your own house.</p>
            <?php else: ?>
                <p class="meta">Solo account — upgrade to a house plan to invite teammates.</p>
            <?php endif; ?>
            <?php if (!empty($seats['max']) && empty($ctx['playground_only'])): ?>
                <p class="meta">
                    Seats:
                    <strong><?php echo (int) $seats['count']; ?> / <?php echo (int) $seats['max']; ?></strong>
                    <?php if (empty($seats['ok']) && !empty($seats['allows_invites'])): ?>
                        <span class="badge warn" style="margin-left:6px;">Full</span>
                    <?php elseif (!empty($seats['allows_invites'])): ?>
                        <span class="badge ok" style="margin-left:6px;">Room to grow</span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if (!$canManage): ?>
                <div class="note">Only the house owner can change the subscription plan. Ask your owner if you need more seats.</div>
            <?php endif; ?>
        </div>

        <?php if ($canManage && $needsHouseName && $upgrades): ?>
            <div class="card">
                <h2><?php echo !empty($ctx['playground_only']) ? 'Convert to your own house' : 'Name your house'; ?></h2>
                <?php if (!empty($ctx['playground_only'])): ?>
                    <p>Ready to subscribe? Name your restaurant, pick a plan, and checkout. We’ll move you off the playground into your own paid kitchen — demo access is removed automatically.</p>
                <?php else: ?>
                    <p>Upgrading from Individual starts a restaurant so teammates can join. We’ll use this name on your house invite.</p>
                <?php endif; ?>
                <p class="hint">Enter the name on the upgrade form below (required for house plans).</p>
            </div>
        <?php endif; ?>

        <?php if ($canManage && !$upgrades): ?>
            <div class="card">
                <h2>You’re on top 🏆</h2>
                <p>You’re on the largest self-serve plan. Need 300+ seats, Mega / Enterprise, or multi-unit pricing? Email us for a custom quote (starts at $249/mo).</p>
                <a class="btn btn-purple" href="mailto:nutsaboutpbj@ilovepbj.shop?subject=Mega%20%2F%20Enterprise%20custom%20pricing">Talk Custom · nutsaboutpbj@</a>
            </div>
        <?php endif; ?>

        <?php foreach ($upgrades as $plan): ?>
            <div class="card upgrade">
                <span class="badge pink">Upgrade</span>
                <h2><?php echo htmlspecialchars($plan['name']); ?></h2>
                <div class="price"><?php echo htmlspecialchars($plan['price']); ?>
                    <?php if (!empty($plan['price_note'])): ?>
                        <span style="font-size:0.9rem;opacity:0.75;font-weight:400;"> · <?php echo htmlspecialchars($plan['price_note']); ?></span>
                    <?php endif; ?>
                </div>
                <p class="meta"><?php echo htmlspecialchars($plan['tagline']); ?></p>
                <ul>
                    <?php foreach ($plan['features'] as $f): ?>
                        <li><?php echo htmlspecialchars($f); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($canManage): ?>
                <form method="POST" action="/billing/upgrade">
                    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan['id']); ?>">
                    <?php if ($needsHouseName && empty($plan['limited'])): ?>
                        <label for="house_<?php echo htmlspecialchars($plan['id']); ?>">Restaurant name</label>
                        <input id="house_<?php echo htmlspecialchars($plan['id']); ?>" type="text" name="restaurant_name"
                               placeholder="e.g. Downtown PB&amp;J" required
                               value="<?php echo htmlspecialchars((string) ($_GET['name'] ?? $ctx['restaurant_name'])); ?>">
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit">
                        Upgrade to <?php echo htmlspecialchars($plan['name']); ?> →
                    </button>
                </form>
                <p class="hint">If you already subscribe, Stripe prorates the change. New houses go through secure Checkout.</p>
                <?php else: ?>
                    <a class="btn btn-primary is-disabled" href="#" aria-disabled="true">Owner only</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="card">
            <h2>Mega / Enterprise</h2>
            <p>300+ employees — custom pricing starting at <strong>$249/mo</strong>. We’ll size it to your headcount and rollout.</p>
            <a class="btn btn-purple" href="mailto:nutsaboutpbj@ilovepbj.shop?subject=Mega%20%2F%20Enterprise%20custom%20pricing">Email nutsaboutpbj@ilovepbj.shop</a>
        </div>
        <div class="card">
            <h2>Multi-unit pricing</h2>
            <p>Same per-location house tier × number of units, with volume discounts:</p>
            <ul>
                <li>3–10 units → 15% off</li>
                <li>11–25 units → 20% off</li>
                <li>26–50 units → 25% off</li>
                <li>51–100 units → 30% off</li>
                <li>100+ units → Custom pricing</li>
            </ul>
            <a class="btn btn-purple" href="mailto:nutsaboutpbj@ilovepbj.shop?subject=Multi-unit%20pricing%20quote">Get a multi-unit quote</a>
            <p class="hint" style="margin-top:14px;">Optional add-ons coming soon: Startup / Onboarding · Dedicated Butler</p>
            <a class="btn btn-ghost" href="/settings/account">Back to Account</a>
            <a class="btn btn-ghost" href="/home">Home</a>
        </div>
    </div>
</body>
</html>
