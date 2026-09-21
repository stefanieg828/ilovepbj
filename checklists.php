<?php
require_once __DIR__ . '/config.php';

// Public SEO article — opening/closing/prep checklists for independent restaurants
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant opening &amp; closing checklists for small crews | ilovepbj</title>
    <meta name="description" content="Why paper opening, closing, and prep checklists fail in independent restaurants — and how a shared digital checklist helps a small FOH + BOH crew stay consistent. FREE DEMO or no-card trial.">
    <link rel="canonical" href="https://ilovepbj.shop/checklists">
    <meta name="robots" content="index, follow">
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="ilovepbj ops">
    <meta property="og:title" content="Restaurant opening &amp; closing checklists for small crews | ilovepbj">
    <meta property="og:description" content="Why paper checklists fail — and how a shared digital opening, closing, and prep checklist helps independent restaurant crews.">
    <meta property="og:url" content="https://ilovepbj.shop/checklists">
    <meta property="og:image" content="https://ilovepbj.shop/icon-512.png?v=3">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Restaurant opening &amp; closing checklists for small crews | ilovepbj">
    <meta name="twitter:description" content="Opening, closing, and prep checklists for independent restaurants — paper vs shared digital.">
    <meta name="twitter:image" content="https://ilovepbj.shop/icon-512.png?v=3">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": "Restaurant opening and closing checklists for independent restaurants",
      "description": "Why paper opening, closing, and prep checklists fail — and how a shared digital checklist helps a small FOH + BOH crew.",
      "url": "https://ilovepbj.shop/checklists",
      "author": {
        "@type": "Organization",
        "name": "ilovepbj ops",
        "url": "https://ilovepbj.shop/"
      },
      "publisher": {
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
            line-height: 1.55;
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
        .hero { text-align: center; padding: 56px 20px 36px; max-width: 860px; margin: 0 auto; }
        .hero h1 {
            font-family: 'ModernLoveCaps', serif; font-size: clamp(2.2rem, 6.5vw, 3.5rem);
            color: var(--pink); margin: 0 0 12px; line-height: 1.12;
        }
        .hero .seo-lead {
            font-size: clamp(1.05rem, 2.6vw, 1.25rem);
            color: var(--purple);
            margin: 0 0 10px;
            font-weight: 600;
        }
        .hero .tagline { font-size: clamp(1.05rem, 2.6vw, 1.28rem); opacity: 0.9; margin: 0 0 22px; }
        .hero-ctas { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-bottom: 14px; }
        .hero-sub { font-size: 1rem; opacity: 0.7; margin: 0; }
        .section { max-width: 760px; margin: 0 auto; padding: 20px 18px 10px; }
        .section.wide { max-width: 1040px; }
        .section h2 {
            font-family: 'ModernLoveCaps', serif; color: var(--pink);
            font-size: clamp(1.7rem, 4vw, 2.2rem);
            text-align: center; margin: 0 0 12px;
        }
        .section .lead { text-align: center; opacity: 0.85; margin: 0 0 20px; font-size: 1.08rem; }
        .article p {
            margin: 0 0 1em;
            font-size: 1.08rem;
            opacity: 0.92;
        }
        .article h3 {
            font-family: 'ModernLoveCaps', serif;
            color: var(--purple);
            font-size: 1.35rem;
            margin: 1.4em 0 0.5em;
        }
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
        .card-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;
            margin: 8px 0 22px;
        }
        @media (max-width: 800px) { .card-grid { grid-template-columns: 1fr; } }
        .info-card {
            background: white; border-radius: 18px; padding: 20px 18px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.07); text-align: left;
            border: 2px solid transparent;
        }
        .info-card:nth-child(1) { border-color: rgba(229, 81, 99, 0.25); }
        .info-card:nth-child(1) h3 { color: var(--pink); }
        .info-card:nth-child(2) { border-color: rgba(107, 74, 140, 0.28); }
        .info-card:nth-child(2) h3 { color: var(--purple); }
        .info-card:nth-child(3) { border-color: rgba(187, 231, 218, 0.9); }
        .info-card:nth-child(3) h3 { color: var(--purple); }
        .info-card h3 {
            margin: 0 0 8px; font-family: 'ModernLoveCaps', serif; font-size: 1.25rem; text-align: center;
        }
        .info-card p { margin: 0; opacity: 0.85; font-size: 0.98rem; }
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
            <a class="nav-link" href="#article">Article</a>
            <a class="nav-link" href="/food-cost-calculator">Food cost</a>
            <a class="nav-link" href="#try-free">Try free</a>
            <a class="btn btn-outline btn-sm" href="/login">Log in</a>
            <a class="btn btn-mint btn-sm" href="/register?mode=playground">FREE DEMO</a>
        </div>
    </nav>

    <header class="hero">
        <h1>Checklists that survive the rush</h1>
        <p class="seo-lead">Restaurant opening, closing, and prep checklists for independent restaurants.</p>
        <p class="tagline">
            Paper pads disappear. Group chats get buried. A shared digital checklist keeps FOH and BOH
            on the same page — even when the crew is small and the night is loud.
        </p>
        <div class="hero-ctas">
            <a class="btn btn-primary" href="/register?mode=playground">Enter FREE DEMO</a>
            <a class="btn btn-purple" href="/register">Start a 14-day free trial (no card)</a>
            <a class="btn btn-ghost" href="/food-cost-calculator">Food cost calculator</a>
        </div>
        <p class="hero-sub">FREE DEMO resets nightly · trial keeps your house checklists</p>
    </header>

    <div class="band-mint">
    <section class="section article" id="article">
        <h2>Opening, closing, and prep — without the clipboard chaos</h2>
        <p class="lead">A short guide for independent restaurant owners who want consistency without a corporate ops team.</p>

        <p>
            Most independent restaurants already <em>have</em> checklists — taped to the walk-in,
            photocopied for hosts, scribbled on a prep board. Opening covers lights, POS, bathrooms,
            and the first sidework sweep. Closing covers counts, clean-down, and locking the night away.
            Prep lists tell the line what to pull, portion, and fire before doors. FOH and BOH each own a slice.
        </p>

        <p>
            The problem is rarely “we need more tasks.” It’s that the list lives in one place while the
            people who need it live in another — and nobody can prove the shift actually finished the job.
        </p>

        <h3>Why paper checklists fail on a busy night</h3>
        <p>
            Paper is honest until it isn’t. A clipboard gets shoved under the bar. The printer jam means
            yesterday’s list is still taped up. A new hire follows a half-erased version. The closer initials
            everything at 1:12&nbsp;a.m. because the opener “mostly” did it. When something goes wrong —
            a dirty restroom, a missed temp, an unlocked patio — you can’t tell whether the process failed
            or the paper did.
        </p>
        <p>
            Group texts and sticky notes have the same soft edges: easy to start, hard to audit, and
            terrible at handoff between morning prep and evening service.
        </p>

        <h3>What a shared digital checklist changes for a small crew</h3>
        <p>
            A shared digital checklist doesn’t replace good training. It replaces the fog. Everyone on the
            house — opener, prep cook, closer, manager — sees the same live list. Tasks get checked off as
            they happen, not reconstructed at the end. When someone calls out, the next person doesn’t
            inherit a mystery clipboard; they inherit a clear remaining set.
        </p>
        <p>
            For independent restaurants, that matters more than fancy features. You usually don’t have a
            dedicated ops manager. You have a chef-owner, a floor lead, and a crew that rotates. The tool
            has to be light enough to use mid-shift and clear enough that you trust it the next morning.
        </p>
    </section>
    </div>

    <section class="section wide">
        <h2>FOH + BOH, same house</h2>
        <p class="lead">Keep front and back aligned without running two separate binder systems.</p>
        <div class="card-grid">
            <div class="info-card">
                <h3>Opening</h3>
                <p>Lights, stations, bathrooms, POS, reservations glance, and the “ready for first ticket” pass — visible to whoever walks in first.</p>
            </div>
            <div class="info-card">
                <h3>Prep &amp; line</h3>
                <p>Pull lists, portion checks, and line-ready items that don’t vanish when the dry-erase board gets wiped for specials.</p>
            </div>
            <div class="info-card">
                <h3>Closing</h3>
                <p>Clean-down, cash/close steps, temps, locking, and the handoff note for tomorrow’s opener — finished, not “we’ll get it.”</p>
            </div>
        </div>
    </section>

    <div class="band-purple">
    <section class="section wide" id="try-free">
        <h2>Try it in the house</h2>
        <p class="lead">Peek with FREE DEMO, or start a real trial that keeps your checklists. Cost a plate while you’re there.</p>
        <div class="try-grid">
            <div class="try-card playground">
                <h3>FREE DEMO</h3>
                <p>
                    Username + email + password, no card. Explore opening/closing and prep-style ops
                    with the rest of the house. <strong>Resets every night.</strong>
                </p>
                <a class="btn btn-primary" href="/register?mode=playground">Enter FREE DEMO</a>
            </div>
            <div class="try-card trial">
                <h3>14-day free trial</h3>
                <p>
                    Create a real account free — <strong>no card to start</strong>. Your house and
                    checklists stay; convert only if you want after 14 days.
                </p>
                <a class="btn btn-purple" href="/register">Start a 14-day free trial (no card)</a>
            </div>
        </div>

        <div class="cta-band">
            <h2>Ready when the doors are</h2>
            <p>
                Start in FREE DEMO, open a no-card trial, or pair checklists with
                <a href="/food-cost-calculator" style="color:var(--mint);">plate cost &amp; food cost %</a>.
            </p>
            <div class="cta-actions">
                <a class="btn btn-primary" href="/register?mode=playground">Enter FREE DEMO</a>
                <a class="btn btn-ghost" href="/register">Start free trial</a>
                <a class="btn btn-ghost" href="/food-cost-calculator">Food cost</a>
            </div>
        </div>
    </section>
    </div>

    <footer class="footer">
        <p>ilovepbj ops — restaurant checklist and food cost app for independent restaurants.</p>
        <p>
            <a href="/">Home</a> ·
            <a href="/food-cost-calculator">Food cost calculator</a> ·
            <a href="/register?mode=playground">FREE DEMO</a> ·
            <a href="/register">Free trial</a> ·
            <a href="/login">Log in</a>
        </p>
        <p>
            <a href="/privacy">Privacy</a> ·
            <a href="/terms">Terms</a> ·
            <a href="/refunds">Cancel &amp; refunds</a>
            · <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
        </p>
    </footer>
</body>
</html>
