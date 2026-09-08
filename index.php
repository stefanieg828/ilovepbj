<?php
require_once 'config.php';

// Logged-in users (real accounts) go straight into the app
if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
    header('Location: /home');
    exit();
}
// Testing bypass still jumps in
if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
    header('Location: /home');
    exit();
}

$plans = pbj_plans();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ilovepbj ops — Restaurant operations that stick</title>
    <meta name="description" content="ilovepbj ops is a restaurant operations hub for FOH, BOH, team, messages, and day-to-day kitchen &amp; floor tools — sized for individuals and crews, with heart.">
    <link rel="canonical" href="https://ilovepbj.shop/">
    <meta name="robots" content="index, follow">
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ilovepbj ops">
    <meta property="og:title" content="ilovepbj ops — Restaurant operations that stick">
    <meta property="og:description" content="FOH, BOH, team, and messages in one ops hub for restaurants. Pick a plan, create your house, and run the shift with heart.">
    <meta property="og:url" content="https://ilovepbj.shop/">
    <meta property="og:image" content="https://ilovepbj.shop/icon-512.png?v=3">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="ilovepbj ops — Restaurant operations that stick">
    <meta name="twitter:description" content="Restaurant operations hub for FOH, BOH, team, and messages.">
    <meta name="twitter:image" content="https://ilovepbj.shop/icon-512.png?v=3">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "ilovepbj ops",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "url": "https://ilovepbj.shop/",
      "description": "Restaurant operations hub for FOH, BOH, team, messages, and day-to-day tools.",
      "offers": {
        "@type": "AggregateOffer",
        "priceCurrency": "USD",
        "lowPrice": "5",
        "highPrice": "249"
      }
    }
    </script>
    <style>
        /* Brand palette: pink · cream · deep purple · mint (bottom nav) */
        :root {
            --pink: #E55163;
            --cream: #FCF8EE;
            --ink: #3a2f1f;
            --purple: #6B4A8C;
            --mint: #BBE7DA;
            --mint-soft: #E8F7F2;
            --purple-soft: #F3EEF8;
            --pink-soft: #FFF5F6;
        }
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'DreamingOutLoudPro', Georgia, serif;
            background:
                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(187, 231, 218, 0.55), transparent 55%),
                radial-gradient(ellipse 70% 45% at 95% 5%, rgba(107, 74, 140, 0.12), transparent 50%),
                var(--cream);
            color: var(--ink);
            line-height: 1.5;
        }
        a { color: var(--pink); }
        .nav {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 16px 22px;
            background: linear-gradient(105deg, var(--pink) 0%, var(--pink) 55%, var(--purple) 100%);
            color: white;
            position: sticky; top: 0; z-index: 50;
            box-shadow: 0 4px 18px rgba(107, 74, 140, 0.22);
        }
        .nav-brand { font-family: 'ModernLoveCaps', serif; font-size: 1.5rem; text-decoration: none; color: white; }
        .nav-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; }
        .nav-link { color: white; text-decoration: none; opacity: 0.95; padding: 8px 12px; font-size: 1rem; }
        .nav-link:hover { text-decoration: underline; opacity: 1; color: var(--mint); }
        .btn {
            display: inline-block; border: none; border-radius: 14px; padding: 14px 22px; font-size: 1.05rem;
            text-decoration: none; cursor: pointer; font-family: 'DreamingOutLoudPro', Georgia, serif;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary {
            background: var(--pink); color: white;
            box-shadow: 0 6px 16px rgba(229, 81, 99, 0.3);
        }
        .btn-purple {
            background: var(--purple); color: var(--mint);
            box-shadow: 0 6px 16px rgba(107, 74, 140, 0.3);
        }
        .btn-mint {
            background: var(--mint); color: var(--purple);
            box-shadow: 0 4px 14px rgba(187, 231, 218, 0.5);
        }
        .btn-white { background: white; color: var(--pink); }
        .btn-outline {
            background: transparent; color: white;
            border: 2px solid rgba(187, 231, 218, 0.9);
        }
        .btn-outline:hover { background: rgba(187, 231, 218, 0.15); color: var(--mint); }
        .btn-ghost {
            background: white; color: var(--ink);
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            border: 2px solid transparent;
        }
        .btn-ghost:hover { border-color: var(--mint); color: var(--purple); }
        .btn-block { display: block; width: 100%; text-align: center; }
        .btn-sm { padding: 10px 16px; font-size: 0.95rem; border-radius: 12px; }
        .hero { text-align: center; padding: 56px 20px 40px; max-width: 860px; margin: 0 auto; }
        .hero h1 {
            font-family: 'ModernLoveCaps', serif; font-size: clamp(2.6rem, 8vw, 4.2rem);
            color: var(--pink); margin: 0 0 12px; line-height: 1.1;
        }
        .hero .tagline { font-size: clamp(1.15rem, 3vw, 1.45rem); opacity: 0.9; margin: 0 0 22px; }
        .hero-ctas { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-bottom: 14px; }
        .hero-sub { font-size: 1rem; opacity: 0.7; margin: 0; }
        .section { max-width: 1040px; margin: 0 auto; padding: 20px 18px 10px; }
        .section h2 {
            font-family: 'ModernLoveCaps', serif; color: var(--pink);
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            text-align: center; margin: 0 0 8px;
        }
        .section.plans-section h2 { color: var(--purple); }
        .section .lead { text-align: center; opacity: 0.8; margin: 0 0 22px; font-size: 1.1rem; }
        .band-mint {
            background: linear-gradient(180deg, var(--mint-soft) 0%, transparent 100%);
            border-top: 3px solid var(--mint);
            border-bottom: 3px solid transparent;
            margin: 12px 0 8px;
            padding-top: 28px;
            padding-bottom: 8px;
        }
        .band-purple {
            background: linear-gradient(180deg, var(--purple-soft) 0%, transparent 85%);
            border-top: 3px solid rgba(107, 74, 140, 0.35);
            margin: 8px 0 0;
            padding-top: 28px;
        }
        .features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 18px; }
        @media (max-width: 800px) { .features { grid-template-columns: 1fr; } }
        .feat {
            background: white; border-radius: 18px; padding: 20px 18px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.07); text-align: center;
            border: 2px solid transparent;
            transition: border-color 0.2s, transform 0.2s;
        }
        .feat:hover { transform: translateY(-3px); }
        .feat:nth-child(3n+1) { border-color: rgba(229, 81, 99, 0.25); }
        .feat:nth-child(3n+1) .ico-wrap { background: var(--pink-soft); border-color: var(--pink); }
        .feat:nth-child(3n+1) h3 { color: var(--pink); }
        .feat:nth-child(3n+2) { border-color: rgba(107, 74, 140, 0.28); }
        .feat:nth-child(3n+2) .ico-wrap { background: var(--purple-soft); border-color: var(--purple); }
        .feat:nth-child(3n+2) h3 { color: var(--purple); }
        .feat:nth-child(3n) { border-color: rgba(187, 231, 218, 0.9); }
        .feat:nth-child(3n) .ico-wrap { background: var(--mint-soft); border-color: #8fd4c0; }
        .feat:nth-child(3n) h3 { color: var(--purple); }
        .ico-wrap {
            width: 64px; height: 64px; border-radius: 14px; margin: 0 auto 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem; border: 2px solid var(--mint);
            background: var(--mint-soft);
            overflow: hidden;
            padding: 0;
        }
        .ico-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 12px;
        }
        .feat p { margin: 0; opacity: 0.8; font-size: 0.98rem; }
        .plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            align-items: stretch;
            margin-bottom: 12px;
        }
        @media (max-width: 520px) { .plans { grid-template-columns: 1fr; max-width: 420px; margin-left: auto; margin-right: auto; } }
        .plan {
            background: white; border-radius: 20px; padding: 22px 18px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.08);
            border: 3px solid var(--mint);
            display: flex; flex-direction: column; position: relative;
        }
        .plan.rec {
            border-color: var(--pink);
            box-shadow: 0 8px 24px rgba(229, 81, 99, 0.18);
            background: linear-gradient(180deg, var(--pink-soft) 0%, white 40%);
        }
        .plan.limited {
            border-color: var(--mint);
            background: linear-gradient(180deg, var(--mint-soft) 0%, white 45%);
        }
        .plan.custom-plan {
            border-color: var(--purple);
            background: linear-gradient(180deg, var(--purple-soft) 0%, white 45%);
        }
        .plan .badge {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            background: var(--pink); color: white; border-radius: 999px;
            padding: 4px 12px; font-size: 0.85rem; white-space: nowrap;
        }
        .plan .badge.mint-badge { background: var(--purple); color: var(--mint); }
        .plan .badge.purple-badge { background: var(--purple); color: white; }
        .plan h3 {
            font-family: 'ModernLoveCaps', serif; color: var(--pink);
            margin: 8px 0 4px; font-size: 1.45rem; text-align: center;
        }
        .plan.limited h3 { color: var(--purple); }
        .plan.custom-plan h3 { color: var(--purple); }
        .plan .price { text-align: center; font-size: 1.45rem; margin: 0; color: var(--ink); }
        .plan.rec .price { color: var(--pink); }
        .plan.limited .price { color: var(--purple); }
        .plan .price-note { text-align: center; font-size: 0.88rem; opacity: 0.7; margin: 2px 0 8px; }
        .plan .tag { text-align: center; opacity: 0.75; font-size: 0.92rem; margin: 0 0 14px; min-height: 2.8em; }
        .plan ul { list-style: none; padding: 0; margin: 0 0 18px; flex: 1; text-align: left; }
        .plan li { padding: 6px 0 6px 22px; position: relative; font-size: 0.95rem; opacity: 0.88; }
        .plan li::before { content: '✓'; position: absolute; left: 0; color: var(--pink); font-weight: bold; }
        .plan.limited li::before { color: #5a9e8a; }
        .plan.custom-plan li::before { color: var(--purple); }
        .plan .coming { text-align: center; font-size: 0.9rem; opacity: 0.7; margin-bottom: 10px; }
        .multi-unit-box, .optional-pricing-box {
            background: white; border-radius: 20px; padding: 24px 22px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.08);
            border: 3px solid var(--purple);
            margin: 22px auto 0; max-width: 920px;
        }
        .optional-pricing-box {
            border-color: var(--mint);
            background: linear-gradient(180deg, var(--mint-soft) 0%, white 55%);
            margin-top: 16px;
        }
        .multi-unit-box h3, .optional-pricing-box h3 {
            font-family: 'ModernLoveCaps', serif; color: var(--purple);
            margin: 0 0 8px; font-size: 1.55rem; text-align: center;
        }
        .optional-pricing-box h3 { color: var(--pink); }
        .multi-unit-box > p, .optional-pricing-box > p {
            text-align: center; opacity: 0.82; margin: 0 0 16px; line-height: 1.5;
        }
        .discount-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin: 0 0 14px;
        }
        .discount-chip {
            background: var(--purple-soft); border: 2px solid rgba(107,74,140,0.25);
            border-radius: 14px; padding: 12px 10px; text-align: center;
        }
        .discount-chip .units { display: block; font-size: 0.88rem; opacity: 0.75; margin-bottom: 4px; }
        .discount-chip .off { display: block; font-size: 1.15rem; color: var(--purple); font-weight: 700; }
        .discount-chip.custom-chip {
            background: linear-gradient(180deg, var(--pink-soft) 0%, white 70%);
            border-color: rgba(229,81,99,0.3);
        }
        .discount-chip.custom-chip .off { color: var(--pink); }
        .multi-unit-box .mu-cta, .optional-pricing-box .opt-list {
            text-align: center; margin: 8px 0 0;
        }
        .optional-pricing-box .opt-list {
            list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap;
            gap: 10px; justify-content: center;
        }
        .optional-pricing-box .opt-list li {
            background: white; border: 2px dashed rgba(229,81,99,0.35);
            border-radius: 999px; padding: 8px 16px; font-size: 0.95rem; color: var(--ink);
        }
        .optional-pricing-box .soon {
            display: inline-block; margin-left: 6px; font-size: 0.78rem;
            background: var(--pink); color: white; border-radius: 999px; padding: 2px 8px;
            vertical-align: middle;
        }
        .billing-note {
            text-align: center; font-size: 0.95rem; opacity: 0.75; max-width: 680px;
            margin: 8px auto 28px; line-height: 1.45;
            background: white; border-radius: 16px; padding: 14px 18px;
            border: 2px solid var(--mint);
            box-shadow: 0 4px 12px rgba(107, 74, 140, 0.06);
        }
        .billing-note strong { color: var(--purple); }
        .code-band {
            background: linear-gradient(135deg, var(--purple) 0%, #5a3d78 55%, var(--pink) 140%);
            border-radius: 20px; padding: 28px 22px; margin: 10px auto 30px; max-width: 720px;
            box-shadow: 0 8px 28px rgba(107, 74, 140, 0.28); text-align: center;
            color: white;
            border: 3px solid var(--mint);
        }
        .code-band h2 {
            margin: 0 0 8px; font-family: 'ModernLoveCaps', serif;
            color: var(--mint); font-size: 2rem;
        }
        .code-band p { opacity: 0.92; margin: 0 0 16px; color: white; }
        .code-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .code-band .btn-primary { background: var(--mint); color: var(--purple); box-shadow: none; }
        .code-band .btn-ghost {
            background: transparent; color: var(--mint);
            border: 2px solid var(--mint); box-shadow: none;
        }
        .code-band .btn-ghost:hover { background: rgba(187, 231, 218, 0.15); color: white; }
        .footer {
            text-align: center; padding: 28px 16px 40px; font-size: 0.95rem;
            background: var(--purple); color: var(--mint);
            margin-top: 20px;
            box-shadow: 0 -4px 16px rgba(107, 74, 140, 0.2);
        }
        .footer a { color: var(--mint); text-decoration: underline; text-underline-offset: 3px; }
        .footer p { margin: 6px 0; opacity: 0.95; }
        @media (max-width: 640px) {
            .nav { flex-direction: column; align-items: stretch; text-align: center; }
            .nav-actions { justify-content: center; }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a class="nav-brand" href="/">ilovepbj ops</a>
        <div class="nav-actions">
            <a class="nav-link" href="#how">How it works</a>
            <a class="nav-link" href="#plans">Plans</a>
            <a class="nav-link" href="/join">I have a code</a>
            <a class="btn btn-outline btn-sm" href="/login">Log in</a>
            <a class="btn btn-mint btn-sm" href="/register?plan=crew_10">Start free trial</a>
        </div>
    </nav>

    <header class="hero">
        <h1>Ops that stick like jelly</h1>
        <p class="tagline">
            One cute hub for FOH, BOH, team chat, schedules, sales &amp; recipes —
            built for real restaurants, not spreadsheets that melt at 5&nbsp;p.m.
        </p>
        <div class="hero-ctas">
            <a class="btn btn-primary" href="/register?plan=crew_10">Start 14-day free trial</a>
            <a class="btn btn-purple" href="/join">Join with a code</a>
            <a class="btn btn-ghost" href="/login">Log in</a>
        </div>
        <p class="hero-sub">14 days free · no card required · then house pricing by headcount</p>
    </header>

    <div class="band-mint">
    <section class="section" id="how">
        <h2>Everything in one jar</h2>
        <p class="lead">Choose one of six themes — Sweet PBJ Vibes, Sleek Simple Style, Neon 50s Diner, Farm-to-Table, Coffee Shop Cozy, or Modern Urban Edge — your whole house stays in sync.</p>
        <div class="features">
            <div class="feat">
                <div class="ico-wrap"><img src="<?php echo htmlspecialchars(function_exists('pbj_asset_url') ? pbj_asset_url('pbj-foh-sticker.jpg') : 'pbj-foh-sticker.jpg'); ?>" alt="Showtime"></div>
                <h3>Showtime</h3>
                <p>FOH open/close, sidework, floor, reservations &amp; POS quick refs.</p>
            </div>
            <div class="feat">
                <div class="ico-wrap"><img src="<?php echo htmlspecialchars(function_exists('pbj_asset_url') ? pbj_asset_url('pbj-boh-sticker.jpg') : 'pbj-boh-sticker.jpg'); ?>" alt="The Heat"></div>
                <h3>The Heat</h3>
                <p>Prep, recipes, temps, cleaning — ingredients that feed costing.</p>
            </div>
            <div class="feat">
                <div class="ico-wrap"><img src="<?php echo htmlspecialchars(function_exists('pbj_asset_url') ? pbj_asset_url('pbj-admin-sticker.jpg') : 'pbj-admin-sticker.jpg'); ?>" alt="Sandwich HQ"></div>
                <h3>Sandwich HQ</h3>
                <p>Team, schedules, sales, labor, cash, inventory &amp; training.</p>
            </div>
            <div class="feat">
                <div class="ico-wrap"><img src="<?php echo htmlspecialchars(function_exists('pbj_asset_url') ? pbj_asset_url('pbj-messages-sticker.jpg') : 'pbj-messages-sticker.jpg'); ?>" alt="Jelly Jar"></div>
                <h3>Jelly Jar</h3>
                <p>Announcements, handoffs, DMs, FOH &amp; BOH channels — multi-device.</p>
            </div>
            <div class="feat">
                <div class="ico-wrap"><img src="<?php echo htmlspecialchars(function_exists('pbj_asset_url') ? pbj_asset_url('pbj-settings-sticker.jpg') : 'pbj-settings-sticker.jpg'); ?>" alt="Whiskings"></div>
                <h3>Whiskings</h3>
                <p>Theme, profile, shift vibe &amp; prefs for every teammate.</p>
            </div>
            <div class="feat">
                <div class="ico-wrap"><img src="<?php echo htmlspecialchars(function_exists('pbj_asset_url') ? pbj_asset_url('pbj-home-sticker.jpg') : 'pbj-home-sticker.jpg'); ?>" alt="Invite codes"></div>
                <h3>Invite codes</h3>
                <p>Owners share a house code. Teammates register or log in and join.</p>
            </div>
        </div>
    </section>
    </div>

    <div class="band-purple">
    <section class="section plans-section" id="plans">
        <h2>Pick your spread</h2>
        <p class="lead">
            Every self-serve plan includes a <strong>14-day free trial</strong> — no card to start.
            Sized by team so it stays affordable. Individuals stay low ($5) for solo partial features.
        </p>
        <div class="plans">
            <?php foreach ($plans as $plan): ?>
            <div class="plan<?php
                echo !empty($plan['recommended']) ? ' rec' : '';
                echo !empty($plan['limited']) ? ' limited' : '';
                echo !empty($plan['coming']) ? ' custom-plan' : '';
            ?>">
                <?php if (!empty($plan['recommended'])): ?>
                    <span class="badge">Most popular</span>
                <?php elseif (!empty($plan['limited'])): ?>
                    <span class="badge mint-badge">Limited</span>
                <?php elseif (!empty($plan['coming'])): ?>
                    <span class="badge purple-badge">Custom</span>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                <div class="price"><?php echo htmlspecialchars($plan['price']); ?></div>
                <?php if (!empty($plan['price_note'])): ?>
                    <div class="price-note"><?php echo htmlspecialchars($plan['price_note']); ?></div>
                <?php endif; ?>
                <p class="tag"><?php echo htmlspecialchars($plan['tagline']); ?></p>
                <ul>
                    <?php foreach ($plan['features'] as $f): ?>
                        <li><?php echo htmlspecialchars($f); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if (!empty($plan['coming'])): ?>
                    <p class="coming">Tell us your headcount &amp; locations — we’ll quote Mega / Enterprise (starts at $249/mo)</p>
                    <a class="btn btn-purple btn-block" href="mailto:nutsaboutpbj@ilovepbj.shop?subject=Mega%20%2F%20Enterprise%20custom%20pricing">
                        Get custom pricing
                    </a>
                <?php elseif (!empty($plan['limited'])): ?>
                    <a class="btn btn-mint btn-block" href="/register?plan=<?php echo urlencode($plan['id']); ?>&amp;mode=start">
                        Try free 14 days
                    </a>
                <?php elseif (!empty($plan['recommended'])): ?>
                    <a class="btn btn-primary btn-block" href="/register?plan=<?php echo urlencode($plan['id']); ?>">
                        Try <?php echo htmlspecialchars($plan['name']); ?> free
                    </a>
                <?php else: ?>
                    <a class="btn btn-purple btn-block" href="/register?plan=<?php echo urlencode($plan['id']); ?>">
                        Try <?php echo htmlspecialchars($plan['name']); ?> free
                    </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="multi-unit-box" id="multi-unit">
            <h3>Multi-Unit Pricing</h3>
            <p>
                Same house pricing based on users at <strong>each location</strong>, times how many units you run —
                then a multi-unit discount stacks on top.
            </p>
            <div class="discount-grid">
                <div class="discount-chip">
                    <span class="units">3–10 units</span>
                    <span class="off">15% off</span>
                </div>
                <div class="discount-chip">
                    <span class="units">11–25 units</span>
                    <span class="off">20% off</span>
                </div>
                <div class="discount-chip">
                    <span class="units">26–50 units</span>
                    <span class="off">25% off</span>
                </div>
                <div class="discount-chip">
                    <span class="units">51–100 units</span>
                    <span class="off">30% off</span>
                </div>
                <div class="discount-chip custom-chip">
                    <span class="units">100+ units</span>
                    <span class="off">Custom</span>
                </div>
            </div>
            <p class="mu-cta">
                <a class="btn btn-purple" href="mailto:nutsaboutpbj@ilovepbj.shop?subject=Multi-unit%20pricing%20quote">
                    Get a multi-unit quote
                </a>
            </p>
        </div>

        <div class="optional-pricing-box" id="optional-pricing">
            <h3>Optional add-ons</h3>
            <p>Extra help when you want a softer landing — pricing coming soon.</p>
            <ul class="opt-list">
                <li>Startup / Onboarding <span class="soon">Coming soon</span></li>
                <li>Dedicated Butler <span class="soon">Coming soon</span></li>
            </ul>
        </div>

        <p class="billing-note">
            <strong>14-day free trial</strong> on every self-serve plan — no card required to start.
            Then: <strong>$5</strong> individual · <strong>$29</strong> (1–10) · <strong>$59</strong> (11–30) ·
            <strong>$99</strong> (31–75) · <strong>$149</strong> (76–150) · <strong>$199</strong> (151–300) ·
            <strong>custom</strong> for 300+ (starts at $249). Subscribe anytime during or after the trial via Stripe.
        </p>
    </section>
    </div>

    <section class="section">
        <div class="code-band" id="code">
            <h2>Got a restaurant code?</h2>
            <p>Your manager shared a house invite code. Create an account (or log in), then join their kitchen in one step.</p>
            <div class="code-actions">
                <a class="btn btn-primary" href="/join">Enter my code</a>
                <a class="btn btn-ghost" href="/register?mode=join">Register &amp; join</a>
                <a class="btn btn-ghost" href="/login">I already have an account</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p>ilovepbj ops · restaurant ops with heart</p>
        <p><a href="/login">Log in</a> · <a href="/register">Create account</a> · <a href="/join">Join with code</a></p>
        <p>
            <a href="/privacy">Privacy</a> ·
            <a href="/terms">Terms</a> ·
            <a href="/refunds">Cancel &amp; refunds</a>
            · <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
        </p>
    </footer>
</body>
</html>
