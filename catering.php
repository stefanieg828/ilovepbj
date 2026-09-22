<?php
/**
 * Public catering landing — explain trays + CTA to inquire (house code) or admin hub.
 */
require_once __DIR__ . '/config.php';

$isLoggedIn = !empty($_SESSION['user_id']);
$canCatering = false;
if ($isLoggedIn && function_exists('pbj_can')) {
    if (is_file(__DIR__ . '/pbj-permissions.php')) {
        require_once __DIR__ . '/pbj-permissions.php';
    }
    $canCatering = pbj_can('admin.catering.view') || pbj_can('admin.catering.edit');
}
$house = isset($_GET['house']) ? strtoupper(preg_replace('/\s+/', '', (string) $_GET['house'])) : '';
$inquireHref = '/catering/inquire' . ($house !== '' ? ('?house=' . rawurlencode($house)) : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restaurant catering orders &amp; tray quotes | ilovepbj</title>
<meta name="description" content="Capture catering inquiries, build tray menus from your recipes, and quote with real food-cost math. Share a house inquire link — leads land in Admin → Catering.">
<link rel="canonical" href="https://ilovepbj.shop/catering">
<meta property="og:title" content="Catering inquiries &amp; tray quotes | ilovepbj">
<meta property="og:description" content="Inquiry → event → trays from recipes → food-cost quote. Built for real kitchens.">
<meta property="og:url" content="https://ilovepbj.shop/catering">
<meta property="og:type" content="website">
<?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
<style>
@font-face{font-family:'DreamingOutLoudPro';src:url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype')}
@font-face{font-family:'ModernLoveCaps';src:url('/Fonts/modern-love-caps.ttf') format('truetype')}
:root{--pink:#E55163;--cream:#FCF8EE;--ink:#3a2f1f;--mint:#BBE7DA;--purple:#6B4A8C}
*{box-sizing:border-box}
body{margin:0;font-family:'DreamingOutLoudPro',Georgia,serif;background:radial-gradient(ellipse 80% 50% at 10% -10%,rgba(187,231,218,.55),transparent 55%),var(--cream);color:var(--ink);line-height:1.5}
a{color:var(--pink)}
.nav{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 22px;background:linear-gradient(105deg,var(--pink) 0%,var(--pink) 55%,var(--purple) 100%);color:#fff;position:sticky;top:0;z-index:20;box-shadow:0 4px 18px rgba(107,74,140,.22)}
.nav a{color:#fff;text-decoration:none;opacity:.95}
.nav-brand{font-family:'ModernLoveCaps',serif;font-size:1.35rem}
.nav-links{display:flex;gap:14px;flex-wrap:wrap;align-items:center}
.btn{display:inline-block;border:none;border-radius:14px;padding:12px 18px;font-size:1.05rem;text-decoration:none;cursor:pointer;font-family:'DreamingOutLoudPro',Georgia,serif}
.btn-primary{background:var(--pink);color:#fff}
.btn-mint{background:var(--mint);color:var(--ink)}
.btn-ghost{background:transparent;color:#fff;border:2px solid rgba(255,255,255,.7)}
.hero{text-align:center;padding:56px 20px 36px;max-width:820px;margin:0 auto}
.hero h1{font-family:'ModernLoveCaps',serif;font-size:clamp(2.2rem,6vw,3.4rem);color:var(--pink);margin:0 0 12px;line-height:1.15}
.hero p{font-size:1.15rem;opacity:.88;margin:0 0 22px}
.ctas{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
.section{max-width:900px;margin:0 auto;padding:10px 20px 40px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
@media(max-width:720px){.grid{grid-template-columns:1fr}}
.card{background:#fff;border-radius:18px;padding:18px;box-shadow:0 5px 15px rgba(0,0,0,.08)}
.card h3{font-family:'ModernLoveCaps',serif;color:var(--pink);margin:0 0 8px;font-size:1.25rem}
.card p{margin:0;opacity:.85;font-size:.98rem}
.band{background:#fff;border-radius:20px;padding:22px;margin:18px 0 28px;box-shadow:0 5px 15px rgba(0,0,0,.08);text-align:center}
.band h2{font-family:'ModernLoveCaps',serif;color:var(--pink);margin:0 0 10px}
.footer{text-align:center;padding:30px 16px 50px;opacity:.75;font-size:.95rem}
</style>
</head>
<body>
<nav class="nav">
  <a class="nav-brand" href="/">ilovepbj ops</a>
  <div class="nav-links">
    <a href="/food-cost-calculator">Food cost</a>
    <a href="/login">Log in</a>
    <a class="btn btn-ghost" style="padding:8px 14px;font-size:.95rem" href="/demo">Peek demo</a>
  </div>
</nav>
<header class="hero">
  <h1>Catering that knows your food cost</h1>
  <p>Inquiries land in your kitchen inbox. Build trays from the recipes you already cost. Quote with a clear food-cost rollup — not a guess on a napkin.</p>
  <div class="ctas">
    <?php if ($canCatering): ?>
      <a class="btn btn-primary" href="/admin/catering">Open Catering hub</a>
    <?php elseif ($isLoggedIn): ?>
      <a class="btn btn-primary" href="/admin">Back to Admin</a>
    <?php else: ?>
      <a class="btn btn-primary" href="/register">Start free trial</a>
      <a class="btn btn-mint" href="/demo">Peek free demo</a>
    <?php endif; ?>
    <a class="btn btn-mint" href="<?php echo htmlspecialchars($inquireHref); ?>">Inquire for a house</a>
  </div>
</header>
<section class="section">
  <div class="grid">
    <div class="card"><h3>1. Inquire</h3><p>Share <code>/catering/inquire?house=YOUR-CODE</code>. Guests send date, headcount, and notes — no account needed.</p></div>
    <div class="card"><h3>2. Build trays</h3><p>GM opens Admin → Catering, pulls trays from house recipes, and snapshots plate cost.</p></div>
    <div class="card"><h3>3. Quote</h3><p>Suggested sell uses your target food-cost % (default 30%, with a 25–30% band). See total food cost vs quote.</p></div>
  </div>
  <div class="band">
    <h2>Have a house invite code?</h2>
    <p>Paste it on the inquire form so your request reaches the right kitchen.</p>
    <p style="margin-top:14px"><a class="btn btn-primary" href="<?php echo htmlspecialchars($inquireHref); ?>">Go to inquire form</a></p>
  </div>
</section>
<footer class="footer">
  <p><a href="/">Home</a> · <a href="/food-cost-calculator">Food cost calculator</a> · <a href="/privacy">Privacy</a> · <a href="/terms">Terms</a></p>
  <p>ilovepbj ops — catering that sticks like jelly.</p>
</footer>
</body>
</html>
