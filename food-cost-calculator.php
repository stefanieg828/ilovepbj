<?php
require_once __DIR__ . '/config.php';

// Public SEO landing — no login gate (unlike index.php redirect for logged-in users)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant food cost calculator — plate cost &amp; FC% | ilovepbj</title>
    <meta name="description" content="Cost recipes, see food cost %, and get a suggested menu price. FREE DEMO (resets nightly) or start a no-card 14-day trial.">
    <link rel="canonical" href="https://ilovepbj.shop/food-cost-calculator">
    <meta name="robots" content="index, follow">
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ilovepbj ops">
    <meta property="og:title" content="Restaurant food cost calculator — plate cost &amp; FC% | ilovepbj">
    <meta property="og:description" content="Cost recipes, see food cost %, and get a suggested menu price. FREE DEMO (resets nightly) or start a no-card 14-day trial.">
    <meta property="og:url" content="https://ilovepbj.shop/food-cost-calculator">
    <meta property="og:image" content="https://ilovepbj.shop/icon-512.png?v=3">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Restaurant food cost calculator — plate cost &amp; FC% | ilovepbj">
    <meta name="twitter:description" content="Cost recipes, see food cost %, and get a suggested menu price. FREE DEMO or no-card 14-day trial.">
    <meta name="twitter:image" content="https://ilovepbj.shop/icon-512.png?v=3">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebApplication",
      "name": "ilovepbj restaurant food cost calculator",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "url": "https://ilovepbj.shop/food-cost-calculator",
      "description": "Cost recipes, see food cost %, and get a suggested menu price. FREE DEMO or no-card 14-day trial.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD",
        "description": "FREE DEMO and 14-day free trial (no card)"
      },
      "provider": {
        "@type": "Organization",
        "name": "ilovepbj ops",
        "url": "https://ilovepbj.shop/"
      }
    }
    </script>
    <style>
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
        .btn-sm { padding: 10px 16px; font-size: 0.95rem; border-radius: 12px; }
        .hero { text-align: center; padding: 56px 20px 40px; max-width: 860px; margin: 0 auto; }
        .hero h1 {
            font-family: 'ModernLoveCaps', serif; font-size: clamp(2.4rem, 7vw, 3.8rem);
            color: var(--pink); margin: 0 0 12px; line-height: 1.1;
        }
        .hero .tagline { font-size: clamp(1.1rem, 2.8vw, 1.35rem); opacity: 0.9; margin: 0 0 22px; }
        .hero-ctas { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-bottom: 14px; }
        .hero-sub { font-size: 1rem; opacity: 0.7; margin: 0; }
        .section { max-width: 1040px; margin: 0 auto; padding: 20px 18px 10px; }
        .section h2 {
            font-family: 'ModernLoveCaps', serif; color: var(--pink);
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            text-align: center; margin: 0 0 8px;
        }
        .section .lead { text-align: center; opacity: 0.8; margin: 0 0 22px; font-size: 1.1rem; }
        .band-mint {
            background: linear-gradient(180deg, var(--mint-soft) 0%, transparent 100%);
            border-top: 3px solid var(--mint);
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
        .steps {
            list-style: none; padding: 0; margin: 0 0 18px;
            counter-reset: step;
            display: grid; gap: 12px;
            max-width: 720px; margin-left: auto; margin-right: auto;
        }
        .steps li {
            counter-increment: step;
            background: white; border-radius: 16px; padding: 16px 18px 16px 64px;
            position: relative;
            box-shadow: 0 5px 18px rgba(0,0,0,0.07);
            border: 2px solid rgba(229, 81, 99, 0.2);
            font-size: 1.05rem;
        }
        .steps li::before {
            content: counter(step);
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--pink); color: white;
            display: flex; align-items: center; justify-content: center;
            font-family: 'ModernLoveCaps', serif; font-size: 1.1rem;
        }
        .steps li:nth-child(2n) { border-color: rgba(107, 74, 140, 0.28); }
        .steps li:nth-child(2n)::before { background: var(--purple); color: var(--mint); }
        .example-card {
            background: white; border-radius: 20px; padding: 22px 20px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.08);
            border: 3px solid var(--mint);
            max-width: 560px; margin: 8px auto 24px; text-align: center;
        }
        .example-card h3 {
            font-family: 'ModernLoveCaps', serif; color: var(--purple);
            margin: 0 0 8px; font-size: 1.4rem;
        }
        .example-card p { margin: 0; opacity: 0.88; font-size: 1.05rem; }
        .example-card .math {
            margin-top: 12px; padding: 12px; border-radius: 12px;
            background: var(--pink-soft); color: var(--pink); font-size: 1.1rem;
        }
        .try-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;
            margin-bottom: 18px;
        }
        @media (max-width: 720px) { .try-grid { grid-template-columns: 1fr; } }
        .try-card {
            background: white; border-radius: 20px; padding: 22px 18px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.08);
            border: 3px solid var(--mint);
            display: flex; flex-direction: column;
        }
        .try-card.playground {
            border-color: var(--pink);
            background: linear-gradient(180deg, var(--pink-soft) 0%, white 40%);
        }
        .try-card.trial {
            border-color: var(--purple);
            background: linear-gradient(180deg, var(--purple-soft) 0%, white 45%);
        }
        .try-card h3 {
            font-family: 'ModernLoveCaps', serif; margin: 0 0 8px; font-size: 1.5rem; text-align: center;
        }
        .try-card.playground h3 { color: var(--pink); }
        .try-card.trial h3 { color: var(--purple); }
        .try-card p { margin: 0 0 16px; opacity: 0.88; flex: 1; text-align: center; }
        .try-card .btn { text-align: center; }
        .why-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;
            margin-bottom: 22px;
        }
        @media (max-width: 800px) { .why-grid { grid-template-columns: 1fr; } }
        .why {
            background: white; border-radius: 18px; padding: 20px 18px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.07); text-align: center;
            border: 2px solid transparent;
        }
        .why:nth-child(1) { border-color: rgba(229, 81, 99, 0.25); }
        .why:nth-child(1) h3 { color: var(--pink); }
        .why:nth-child(2) { border-color: rgba(107, 74, 140, 0.28); }
        .why:nth-child(2) h3 { color: var(--purple); }
        .why:nth-child(3) { border-color: rgba(187, 231, 218, 0.9); }
        .why:nth-child(3) h3 { color: var(--purple); }
        .why h3 { margin: 0 0 8px; font-family: 'ModernLoveCaps', serif; font-size: 1.25rem; }
        .why p { margin: 0; opacity: 0.8; font-size: 0.98rem; }
        .cta-band {
            background: linear-gradient(135deg, var(--purple) 0%, #5a3d78 55%, var(--pink) 140%);
            border-radius: 20px; padding: 28px 22px; margin: 10px auto 30px; max-width: 720px;
            box-shadow: 0 8px 28px rgba(107, 74, 140, 0.28); text-align: center;
            color: white;
            border: 3px solid var(--mint);
        }
        .cta-band h2 {
            margin: 0 0 8px; font-family: 'ModernLoveCaps', serif;
            color: var(--mint); font-size: 2rem;
        }
        .cta-band p { opacity: 0.92; margin: 0 0 16px; color: white; }
        .cta-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .cta-band .btn-primary { background: var(--mint); color: var(--purple); box-shadow: none; }
        .cta-band .btn-ghost {
            background: transparent; color: var(--mint);
            border: 2px solid var(--mint); box-shadow: none;
        }
        .cta-band .btn-ghost:hover { background: rgba(187, 231, 218, 0.15); color: white; }
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
            <a class="nav-link" href="/checklists">Checklists</a>
            <a class="nav-link" href="#try-free">Try free</a>
            <a class="btn btn-outline btn-sm" href="/login">Log in</a>
            <a class="btn btn-mint btn-sm" href="/demo">Peek free demo</a>
        </div>
    </nav>

    <header class="hero">
        <h1>See your plate cost in minutes</h1>
        <p class="tagline">
            Recipe → plate cost → food cost % → suggested menu price. Built for real kitchens, not spreadsheet spaghetti.
        </p>
        <div class="hero-ctas">
            <a class="btn btn-primary" href="/demo">Peek free demo — no signup</a>
            <a class="btn btn-ghost" href="/register?mode=playground">Create a demo login</a>
            <a class="btn btn-purple" href="/register">Start a 14-day free trial (no card)</a>
            <a class="btn btn-ghost" href="/login">Already have a house? Log in</a>
        </div>
        <p class="hero-sub">FREE DEMO resets nightly · trial keeps your recipes &amp; costs</p>
    </header>

    <div class="band-mint">
    <section class="section" id="how">
        <h2>How it works</h2>
        <p class="lead">Four steps from recipe to menu price — no spreadsheet spaghetti required.</p>
        <ol class="steps">
            <li>Add a recipe (or use the demo data).</li>
            <li>Attach ingredient costs from your inventory.</li>
            <li>See plate cost and food cost % against your sell price.</li>
            <li>Tap suggested menu price aimed at about 25–30% food cost — adjust if you want.</li>
        </ol>
        <div class="example-card">
            <h3>Tiny example</h3>
            <p>QA burger costs <strong>$0.75</strong> a plate. Suggested sell around <strong>$2.73</strong> lands near <strong>27.5%</strong> food cost.</p>
            <div class="math">$0.75 ÷ 0.275 ≈ $2.73 menu price</div>
        </div>
        <p class="lead" style="max-width:720px;margin-left:auto;margin-right:auto;text-align:left;opacity:0.88;">
            <strong>How independent restaurants use plate cost:</strong>
            Owner-operators often cost a few hero dishes first — the burger, the pasta, the weekend special —
            so menu prices stay intentional when produce or protein jumps. Plate cost turns “feels about right”
            into a number you can share with the chef or shift lead without rebuilding a spreadsheet every week.
        </p>
    </section>
    </div>

    <div class="band-purple">
    <section class="section" id="try-free">
        <h2>Two ways to try free</h2>
        <p class="lead">Pick FREE DEMO for a nightly-reset sandbox, or a real trial that keeps your data.</p>
        <div class="try-grid">
            <div class="try-card playground">
                <h3>FREE DEMO</h3>
                <p>
                    Username + email + password, no card. Explore costing, recipes, and inventory.
                    <strong>Resets every night</strong> so entered data is wiped by morning.
                </p>
                <a class="btn btn-primary" href="/demo">Peek free demo — no signup</a>
            </div>
            <div class="try-card trial">
                <h3>14-day free trial</h3>
                <p>
                    Create a real account free — <strong>no card to start</strong>. Recipes and costs stay;
                    convert only if you want after 14 days.
                </p>
                <a class="btn btn-purple" href="/register">Start a 14-day free trial (no card)</a>
            </div>
        </div>
    </section>
    </div>

    <section class="section" id="why">
        <h2>Why owners use this</h2>
        <p class="lead">Food costing is the door in — then the rest of the house opens up.</p>
        <div class="why-grid">
            <div class="why">
                <h3>Stars vs silent losses</h3>
                <p>Know which plates carry the night and which quietly eat the margin.</p>
            </div>
            <div class="why">
                <h3>Price with intention</h3>
                <p>Suggested menu price aims near 25–30% food cost — you still set the final number.</p>
            </div>
            <div class="why">
                <h3>Multi-device ready</h3>
                <p>Once the house is set up, costing and recipes stay in sync across devices.</p>
            </div>
        </div>

        <div class="cta-band">
            <h2>Ready to cost a plate?</h2>
            <p>Start in the FREE DEMO, or open a real house with a no-card trial.</p>
            <div class="cta-actions">
                <a class="btn btn-primary" href="/demo">Peek free demo — no signup</a>
                <a class="btn btn-ghost" href="/register">Start free trial</a>
                <a class="btn btn-ghost" href="/login">Log in</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p>ilovepbj ops — FOH, BOH, schedules, and recipes in one house. Food costing is the door in.</p>
        <p><a href="/">Home</a> · <a href="/checklists">Checklists</a> · <a href="/demo">Peek free demo</a> · <a href="/register">Free trial</a> · <a href="/login">Log in</a></p>
        <p>
            <a href="/privacy">Privacy</a> ·
            <a href="/terms">Terms</a> ·
            <a href="/refunds">Cancel &amp; refunds</a>
            · <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
        </p>
    </footer>
</body>
</html>
