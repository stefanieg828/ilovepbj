<?php
/**
 * Gulf Coast leave-behind — printable one-pager (Mobile → Destin drop-ins).
 *
 * Pretty URL (nginx):
 *   location = /growth/gulf-one-pager { rewrite ^ /growth-gulf-one-pager.php last; }
 *
 * Open in browser → Print → Save as PDF / letter. Contact-agnostic: scan to try.
 */
require_once __DIR__ . '/config.php';

$demoUrl = 'https://ilovepbj.shop/demo';
$foodCostUrl = 'https://ilovepbj.shop/food-cost-calculator';
$qrSrc = '/assets/growth/qr-demo.png';
$qrSticker = '/assets/growth/scan-to-try-qr.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gulf Coast leave-behind — food cost + Peek | ilovepbj</title>
    <meta name="description" content="Printable Gulf Coast one-pager: plate cost, FC%, suggested menu price, and a free Peek demo — no signup. Scan to try.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="https://ilovepbj.shop/growth/gulf-one-pager">
    <?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
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
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'DreamingOutLoudPro', Georgia, serif;
            background: var(--cream);
            color: var(--ink);
            line-height: 1.45;
        }
        a { color: var(--pink); }
        .screen-bar {
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
            padding: 12px 18px;
            background: linear-gradient(105deg, var(--pink) 0%, var(--pink) 55%, var(--purple) 100%);
            color: white;
        }
        .screen-bar strong { font-family: 'ModernLoveCaps', serif; letter-spacing: 0.02em; }
        .screen-bar .hint { opacity: 0.92; font-size: 0.95rem; }
        .btn {
            display: inline-block; border: none; border-radius: 12px; padding: 10px 16px;
            font-size: 1rem; text-decoration: none; cursor: pointer;
            font-family: 'DreamingOutLoudPro', Georgia, serif;
        }
        .btn-mint { background: var(--mint); color: var(--purple); }
        .btn-white { background: white; color: var(--pink); }
        .sheet {
            max-width: 8.5in;
            margin: 18px auto 40px;
            padding: 0.45in 0.55in 0.5in;
            background: white;
            border-radius: 18px;
            box-shadow: 0 10px 36px rgba(107, 74, 140, 0.12);
            border: 3px solid rgba(187, 231, 218, 0.85);
            position: relative;
            overflow: hidden;
        }
        .sheet::before {
            content: '';
            position: absolute; inset: 0 0 auto 0; height: 10px;
            background: linear-gradient(90deg, var(--pink), var(--purple), var(--mint));
        }
        .top {
            display: grid;
            grid-template-columns: 1.35fr 0.9fr;
            gap: 18px;
            align-items: start;
            margin-top: 8px;
        }
        .brand-row {
            display: flex; align-items: center; gap: 12px; margin-bottom: 8px;
        }
        .brand-row img.logo {
            width: 56px; height: 56px; border-radius: 14px;
            object-fit: cover; box-shadow: 0 4px 12px rgba(229, 81, 99, 0.25);
        }
        .brand-name {
            font-family: 'ModernLoveCaps', serif;
            font-size: 1.55rem; color: var(--pink); margin: 0; line-height: 1.1;
        }
        .brand-sub { margin: 2px 0 0; font-size: 0.95rem; opacity: 0.75; }
        h1 {
            font-family: 'ModernLoveCaps', serif;
            font-size: clamp(1.85rem, 4vw, 2.35rem);
            color: var(--purple);
            margin: 10px 0 8px;
            line-height: 1.12;
        }
        .lede {
            font-size: 1.12rem;
            margin: 0 0 12px;
        }
        .lede strong { color: var(--pink); }
        .pill-row { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 14px; }
        .pill {
            background: var(--mint-soft);
            color: var(--purple);
            border: 2px solid var(--mint);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.92rem;
        }
        .pill.pink {
            background: var(--pink-soft);
            border-color: rgba(229, 81, 99, 0.35);
            color: var(--pink);
        }
        .bullets {
            list-style: none; padding: 0; margin: 0;
            display: grid; gap: 8px;
        }
        .bullets li {
            display: grid; grid-template-columns: 28px 1fr; gap: 8px; align-items: start;
            background: linear-gradient(90deg, var(--purple-soft), transparent);
            border-radius: 12px;
            padding: 8px 10px;
        }
        .bullets .mark {
            width: 26px; height: 26px; border-radius: 50%;
            background: var(--pink); color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: bold;
        }
        .bullets strong { color: var(--purple); }
        .qr-card {
            background: var(--cream);
            border: 2px dashed rgba(107, 74, 140, 0.35);
            border-radius: 16px;
            padding: 14px 12px 12px;
            text-align: center;
        }
        .qr-card h2 {
            font-family: 'ModernLoveCaps', serif;
            font-size: 1.25rem;
            color: var(--pink);
            margin: 0 0 4px;
        }
        .qr-card .scan {
            margin: 0 0 10px;
            font-size: 0.98rem;
            color: var(--purple);
        }
        .qr-card img.qr {
            width: 2.55in; height: 2.55in;
            max-width: 100%;
            background: white;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 4px 14px rgba(58, 47, 31, 0.08);
        }
        .qr-card .url {
            font-size: 0.82rem;
            word-break: break-all;
            margin: 8px 0 0;
            opacity: 0.8;
        }
        .secondary {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 14px;
            background: linear-gradient(180deg, var(--mint-soft), white);
            border: 2px solid var(--mint);
            display: grid;
            grid-template-columns: 1.4fr 0.7fr;
            gap: 12px;
            align-items: center;
        }
        .secondary h3 {
            font-family: 'ModernLoveCaps', serif;
            color: var(--purple);
            margin: 0 0 4px;
            font-size: 1.15rem;
        }
        .secondary p { margin: 0; font-size: 0.98rem; }
        .secondary .mini-qr {
            text-align: center;
        }
        .secondary .mini-qr img {
            width: 1.15in; height: 1.15in;
            background: white; border-radius: 10px; padding: 4px;
        }
        .secondary .mini-qr span {
            display: block; font-size: 0.72rem; margin-top: 4px; opacity: 0.75;
            word-break: break-all;
        }
        .foot {
            margin-top: 14px;
            display: flex; flex-wrap: wrap; gap: 8px 16px;
            justify-content: space-between; align-items: center;
            font-size: 0.92rem;
            opacity: 0.85;
            border-top: 2px solid var(--mint);
            padding-top: 10px;
        }
        .foot .tag {
            font-family: 'ModernLoveCaps', serif;
            color: var(--pink);
        }
        @media (max-width: 720px) {
            .top, .secondary { grid-template-columns: 1fr; }
            .sheet { margin: 10px; padding: 0.4in 0.35in; }
        }
        @media print {
            @page { size: letter; margin: 0.4in; }
            body { background: white; }
            .screen-bar { display: none !important; }
            .sheet {
                margin: 0; max-width: none; width: 100%;
                box-shadow: none; border: none; border-radius: 0;
                padding: 0;
            }
            .sheet::before { height: 8px; }
            a { color: inherit; text-decoration: none; }
            .qr-card img.qr { width: 2.4in; height: 2.4in; }
        }
    </style>
</head>
<body>
    <div class="screen-bar" role="region" aria-label="Print controls">
        <div>
            <strong>Gulf Coast leave-behind</strong>
            <div class="hint">Mobile → Destin drop-ins · Print this page (or Save as PDF)</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" class="btn btn-mint" onclick="window.print()">Print / Save PDF</button>
            <a class="btn btn-white" href="/demo">Open Peek</a>
        </div>
    </div>

    <article class="sheet" aria-label="ilovepbj Gulf Coast one-pager">
        <div class="top">
            <div>
                <div class="brand-row">
                    <img class="logo" src="/icon-192.png" alt="ilovepbj">
                    <div>
                        <p class="brand-name">ilovepbj ops</p>
                        <p class="brand-sub">Ops that stick like jelly</p>
                    </div>
                </div>
                <h1>Know your plate cost.<br>Peek the demo free.</h1>
                <p class="lede">
                    Restaurant food costing that shows <strong>plate cost</strong>, <strong>FC%</strong>, and a
                    <strong>suggested menu price</strong> — then a free Peek of the whole ops hub.
                    <strong>No signup wall.</strong> Just scan and try.
                </p>
                <div class="pill-row" aria-hidden="true">
                    <span class="pill pink">Gulf Coast kitchens</span>
                    <span class="pill">No cold call needed</span>
                    <span class="pill">Resets nightly</span>
                </div>
                <ul class="bullets">
                    <li>
                        <span class="mark" aria-hidden="true">1</span>
                        <span><strong>Plate cost</strong> — see what a dish really costs before it hits the ticket.</span>
                    </li>
                    <li>
                        <span class="mark" aria-hidden="true">2</span>
                        <span><strong>Food cost %</strong> — FC% on the recipe, not a guess on a napkin.</span>
                    </li>
                    <li>
                        <span class="mark" aria-hidden="true">3</span>
                        <span><strong>Suggested menu price</strong> — price from target FC%, not vibes alone.</span>
                    </li>
                    <li>
                        <span class="mark" aria-hidden="true">4</span>
                        <span><strong>Order guides</strong> — turn counts into what to buy next.</span>
                    </li>
                    <li>
                        <span class="mark" aria-hidden="true">5</span>
                        <span><strong>Free Peek</strong> — one tap into the demo kitchen. No username, no email, no password.</span>
                    </li>
                </ul>
            </div>
            <aside class="qr-card">
                <h2>Scan to try</h2>
                <p class="scan">Opens Peek — free demo, no signup</p>
                <img class="qr" src="<?= htmlspecialchars($qrSrc, ENT_QUOTES, 'UTF-8') ?>?v=gulf1" width="360" height="360" alt="QR code to peek free demo at ilovepbj.shop/demo">
                <p class="url"><a href="<?= htmlspecialchars($demoUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($demoUrl, ENT_QUOTES, 'UTF-8') ?></a></p>
            </aside>
        </div>

        <div class="secondary">
            <div>
                <h3>Want the food-cost tools first?</h3>
                <p>
                    Open the public calculator anytime:
                    <a href="<?= htmlspecialchars($foodCostUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($foodCostUrl, ENT_QUOTES, 'UTF-8') ?></a>
                    — then Peek the full house when you’re ready.
                </p>
            </div>
            <div class="mini-qr">
                <img src="/assets/growth/qr-food-cost.png?v=gulf1" width="160" height="160" alt="QR code to food cost calculator">
                <span>food-cost calculator</span>
            </div>
        </div>

        <div class="foot">
            <span class="tag">ilovepbj.shop</span>
            <span>Leave-behind · Mobile → Destin · scan when it fits your shift</span>
            <span>Branded sticker also at <code>/assets/growth/scan-to-try-qr.png</code></span>
        </div>
    </article>
</body>
</html>
