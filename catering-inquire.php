<?php
/**
 * Public shareable catering inquire form.
 * Requires ?house=INVITE-CODE so the lead lands in that restaurant's Catering inbox.
 */
require_once __DIR__ . '/config.php';

$house = isset($_GET['house']) ? strtoupper(preg_replace('/\s+/', '', (string) $_GET['house'])) : '';
if ($house === '' && isset($_POST['house'])) {
    $house = strtoupper(preg_replace('/\s+/', '', (string) $_POST['house']));
}

$restaurantName = '';
$houseOk = false;
if ($house !== '' && isset($pdo) && $pdo instanceof PDO && function_exists('pbj_find_restaurant_by_code')) {
    $rest = pbj_find_restaurant_by_code($pdo, $house);
    if ($rest) {
        $houseOk = true;
        $restaurantName = (string) ($rest['name'] ?? '');
    }
}

$flash = null;
$flashErr = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$houseOk) {
        $flashErr = 'We need a valid house invite code to route your request.';
    } else {
        // Proxy to API logic inline to keep one write path
        require_once __DIR__ . '/catering.inc.php';
        pbj_catering_ensure_tables($pdo);
        $name = pbj_catering_clip((string) ($_POST['contact_name'] ?? ''), 120);
        $email = pbj_catering_clip((string) ($_POST['contact_email'] ?? ''), 190);
        $phone = pbj_catering_clip((string) ($_POST['contact_phone'] ?? ''), 40);
        $eventName = pbj_catering_clip((string) ($_POST['event_name'] ?? ''), 190);
        $notes = pbj_catering_clip((string) ($_POST['notes'] ?? ''), 4000);
        $delivery = pbj_catering_clip((string) ($_POST['delivery_notes'] ?? ''), 2000);
        $eventDate = trim((string) ($_POST['event_date'] ?? ''));
        $eventTime = pbj_catering_clip((string) ($_POST['event_time'] ?? ''), 40);
        $headcount = isset($_POST['headcount']) && $_POST['headcount'] !== '' ? max(0, (int) $_POST['headcount']) : null;
        if ($name === '') {
            $flashErr = 'Please add your name.';
        } elseif ($email === '' && $phone === '') {
            $flashErr = 'Add an email or phone so the kitchen can reach you.';
        } elseif ($eventDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            $flashErr = 'Event date looks off — use YYYY-MM-DD.';
        } else {
            $now = time();
            $last = (int) ($_SESSION['catering_inquire_last'] ?? 0);
            if ($last > 0 && ($now - $last) < 20) {
                $flashErr = 'Please wait a few seconds and try again.';
            } else {
                $_SESSION['catering_inquire_last'] = $now;
                if ($eventDate === '') {
                    $eventDate = null;
                }
                $rid = (int) $rest['id'];
                $stmt = $pdo->prepare(
                    'INSERT INTO catering_events
                     (restaurant_id, status, contact_name, contact_email, contact_phone,
                      event_name, event_date, event_time, headcount, delivery_notes, notes,
                      source, target_fc_pct, created_by)
                     VALUES (?, \'inquiry\', ?, ?, ?, ?, ?, ?, ?, ?, ?, \'share_link\', 30.00, NULL)'
                );
                $stmt->execute([
                    $rid, $name,
                    $email !== '' ? $email : null, $phone !== '' ? $phone : null,
                    $eventName !== '' ? $eventName : null, $eventDate,
                    $eventTime !== '' ? $eventTime : null, $headcount,
                    $delivery !== '' ? $delivery : null, $notes !== '' ? $notes : null,
                ]);
                $flash = 'Thanks' . ($restaurantName !== '' ? (' — ' . $restaurantName . ' got your inquire') : '') . '. The kitchen will follow up soon.';
                $_POST = [];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catering inquire<?php echo $restaurantName !== '' ? (' · ' . htmlspecialchars($restaurantName)) : ''; ?> | ilovepbj</title>
<meta name="robots" content="noindex,follow">
<link rel="canonical" href="https://ilovepbj.shop/catering/inquire">
<?php if (function_exists('pbj_render_favicon_links')) { pbj_render_favicon_links(); } ?>
<style>
@font-face{font-family:'DreamingOutLoudPro';src:url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype')}
@font-face{font-family:'ModernLoveCaps';src:url('/Fonts/modern-love-caps.ttf') format('truetype')}
:root{--pink:#E55163;--cream:#FCF8EE;--ink:#3a2f1f;--mint:#BBE7DA}
*{box-sizing:border-box}
body{margin:0;font-family:'DreamingOutLoudPro',Georgia,serif;background:var(--cream);color:var(--ink);line-height:1.5}
.wrap{max-width:560px;margin:0 auto;padding:28px 16px 60px}
.brand{text-align:center;margin-bottom:18px}
.brand a{color:var(--pink);text-decoration:none;font-family:'ModernLoveCaps',serif;font-size:1.4rem}
h1{font-family:'ModernLoveCaps',serif;color:var(--pink);font-size:2.1rem;margin:0 0 8px;text-align:center}
.sub{text-align:center;opacity:.8;margin:0 0 18px}
.card{background:#fff;border-radius:18px;padding:18px;box-shadow:0 5px 15px rgba(0,0,0,.08)}
.field{margin-bottom:12px}
.field label{display:block;font-size:.85rem;opacity:.7;margin-bottom:4px}
.field input,.field textarea{width:100%;border-radius:12px;border:2px solid #F3C5CC;padding:11px 12px;font-size:1rem;font-family:'DreamingOutLoudPro',Georgia,serif;background:#FFFBF8}
.row{display:flex;gap:10px;flex-wrap:wrap}
.row .field{flex:1;min-width:140px}
.btn{width:100%;border:none;border-radius:14px;padding:14px;font-size:1.1rem;background:var(--pink);color:#fff;cursor:pointer;font-family:'DreamingOutLoudPro',Georgia,serif}
.flash{background:#E8F7F2;border:1px solid #BBE7DA;border-radius:14px;padding:12px 14px;margin-bottom:14px}
.err{background:#FDECEA;border:1px solid #F5C6CB;border-radius:14px;padding:12px 14px;margin-bottom:14px}
.hint{font-size:.9rem;opacity:.75;margin:8px 0 0}
</style>
</head>
<body>
<div class="wrap">
  <div class="brand"><a href="/">ilovepbj ops</a></div>
  <h1>Catering inquire</h1>
  <p class="sub"><?php echo $houseOk
    ? ('Sending to <strong>' . htmlspecialchars($restaurantName !== '' ? $restaurantName : $house) . '</strong>')
    : 'Add the house invite code from your host so we route this to the right kitchen.'; ?></p>

  <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>
  <?php if ($flashErr): ?><div class="err"><?php echo htmlspecialchars($flashErr); ?></div><?php endif; ?>

  <div class="card">
    <form method="post" action="">
      <div class="field">
        <label>House invite code</label>
        <input type="text" name="house" value="<?php echo htmlspecialchars($house); ?>" placeholder="e.g. ABCD-1234" required <?php echo $houseOk ? 'readonly' : ''; ?>>
        <?php if (!$houseOk && $house !== ''): ?><p class="hint">That code wasn't found — double-check with your host.</p><?php endif; ?>
      </div>
      <div class="field"><label>Your name</label><input type="text" name="contact_name" required value="<?php echo htmlspecialchars((string) ($_POST['contact_name'] ?? '')); ?>"></div>
      <div class="row">
        <div class="field"><label>Email</label><input type="email" name="contact_email" value="<?php echo htmlspecialchars((string) ($_POST['contact_email'] ?? '')); ?>"></div>
        <div class="field"><label>Phone</label><input type="tel" name="contact_phone" value="<?php echo htmlspecialchars((string) ($_POST['contact_phone'] ?? '')); ?>"></div>
      </div>
      <div class="field"><label>Event name (optional)</label><input type="text" name="event_name" value="<?php echo htmlspecialchars((string) ($_POST['event_name'] ?? '')); ?>"></div>
      <div class="row">
        <div class="field"><label>Event date</label><input type="date" name="event_date" value="<?php echo htmlspecialchars((string) ($_POST['event_date'] ?? '')); ?>"></div>
        <div class="field"><label>Time</label><input type="text" name="event_time" placeholder="Noon drop" value="<?php echo htmlspecialchars((string) ($_POST['event_time'] ?? '')); ?>"></div>
        <div class="field"><label>Headcount</label><input type="number" name="headcount" min="0" step="1" value="<?php echo htmlspecialchars((string) ($_POST['headcount'] ?? '')); ?>"></div>
      </div>
      <div class="field"><label>Delivery / setup notes</label><textarea name="delivery_notes" rows="2"><?php echo htmlspecialchars((string) ($_POST['delivery_notes'] ?? '')); ?></textarea></div>
      <div class="field"><label>What are you hoping for?</label><textarea name="notes" rows="3" placeholder="Trays, dietary needs, budget vibe…"><?php echo htmlspecialchars((string) ($_POST['notes'] ?? '')); ?></textarea></div>
      <button class="btn" type="submit">Send inquire</button>
      <p class="hint">This goes straight to the restaurant's Catering inbox in ilovepbj — not a public board.</p>
    </form>
  </div>
  <p class="hint" style="text-align:center;margin-top:18px"><a href="/catering">What is ilovepbj catering?</a></p>
</div>
</body>
</html>
