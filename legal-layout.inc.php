<?php
/**
 * Shared chrome for public legal pages (privacy, terms, refunds).
 * Expects: $legal_title (string), $legal_lead (string optional)
 * Body HTML is captured by caller via ob and passed as $legal_body, OR
 * caller includes this after setting $legal_title and opens content between start/end helpers.
 */
if (!function_exists('pbj_legal_render_start')) {
    function pbj_legal_render_start(string $title, string $lead = ''): void
    {
        $pageTitle = $title . ' · ilovepbj ops';
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($title); ?> for ilovepbj ops — restaurant operations that stick.">
    <meta name="robots" content="index, follow">
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
        body {
            margin: 0;
            font-family: 'DreamingOutLoudPro', Georgia, serif;
            background: var(--cream);
            color: var(--ink);
            line-height: 1.55;
        }
        a { color: var(--pink); }
        .nav {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 16px 22px;
            background: linear-gradient(105deg, var(--pink) 0%, var(--pink) 55%, var(--purple) 100%);
            color: white;
        }
        .nav-brand { font-family: 'ModernLoveCaps', serif; font-size: 1.45rem; text-decoration: none; color: white; }
        .nav-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .nav-link { color: white; text-decoration: none; opacity: 0.95; padding: 8px 12px; font-size: 0.98rem; }
        .nav-link:hover { text-decoration: underline; color: var(--mint); }
        .wrap { max-width: 720px; margin: 0 auto; padding: 28px 18px 48px; }
        .card {
            background: white; border-radius: 20px; padding: 28px 26px 32px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.07); border: 2px solid rgba(187, 231, 218, 0.65);
        }
        h1 {
            font-family: 'ModernLoveCaps', serif; color: var(--pink);
            font-size: clamp(1.9rem, 5vw, 2.5rem); margin: 0 0 8px; line-height: 1.15;
        }
        .lead { opacity: 0.8; margin: 0 0 18px; font-size: 1.05rem; }
        .meta { font-size: 0.9rem; opacity: 0.65; margin: 0 0 22px; }
        h2 {
            font-family: 'ModernLoveCaps', serif; color: var(--purple);
            font-size: 1.35rem; margin: 26px 0 8px;
        }
        p, li { font-size: 1.02rem; }
        p { margin: 0 0 12px; }
        ul { margin: 0 0 14px; padding-left: 1.25rem; }
        li { margin-bottom: 6px; }
        .note {
            background: var(--mint-soft); border-left: 4px solid var(--mint);
            border-radius: 0 12px 12px 0; padding: 12px 14px; margin: 18px 0 0;
            font-size: 0.95rem; opacity: 0.95;
        }
        .footer {
            text-align: center; padding: 28px 16px 40px; background: var(--purple); color: white;
        }
        .footer a { color: var(--mint); text-decoration: underline; text-underline-offset: 3px; }
        .footer p { margin: 6px 0; opacity: 0.95; }
        @media (max-width: 560px) {
            .nav { flex-direction: column; align-items: stretch; text-align: center; }
            .nav-actions { justify-content: center; }
            .card { padding: 22px 16px 26px; }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a class="nav-brand" href="/">ilovepbj ops</a>
        <div class="nav-actions">
            <a class="nav-link" href="/">Home</a>
            <a class="nav-link" href="/login">Log in</a>
            <a class="nav-link" href="/register">Create account</a>
        </div>
    </nav>
    <div class="wrap">
        <article class="card">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <?php if ($lead !== ''): ?>
                <p class="lead"><?php echo htmlspecialchars($lead); ?></p>
            <?php endif; ?>
            <p class="meta">Effective date: July 12, 2026 · ilovepbj.shop</p>
        <?php
    }

    function pbj_legal_render_end(): void
    {
        ?>
            <p class="note">
                Questions? Email
                <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
                — we read every note with care.
            </p>
        </article>
    </div>
    <footer class="footer">
        <p>ilovepbj ops · restaurant ops with heart</p>
        <p>
            <a href="/privacy">Privacy</a> ·
            <a href="/terms">Terms</a> ·
            <a href="/refunds">Cancel &amp; refunds</a>
        </p>
        <p><a href="/">Back to home</a></p>
    </footer>
</body>
</html>
        <?php
    }
}
