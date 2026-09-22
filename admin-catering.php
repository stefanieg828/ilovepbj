<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>

    <title><?php echo $is_sweet ? 'Catering' : 'Catering'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body {
            margin: 0; padding-bottom: 90px;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;
            <?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?>
        }
        .header {
            <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?>
            color: white; padding: 20px 25px 25px; text-align: center;
        }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?>
            font-size: 2.8rem; margin: 0; line-height: 1.15;
        }
        .subtitle { margin: 10px 0 0; font-size: 1.15rem; opacity: 0.9; }
        .content { padding: 24px 14px 40px; max-width: 1200px; margin: 0 auto; }
        .intro {
            background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.92;
        }
        .section-label {
            font-size: 0.9rem; opacity: 0.7; margin: 18px 4px 10px;
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163; font-size: 1.1rem;
            <?php else: ?>font-weight: 600;<?php endif; ?>
        }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
        .card {
            background: white; border-radius: 16px; padding: 16px 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center;
            transition: all 0.3s; text-decoration: none; color: inherit; display: block;
            position: relative;
        }
        .card:hover { transform: translateY(-6px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .card h3 {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?>
            font-size: 1.15rem; margin: 8px 0 4px;
        }
        .card p { margin: 0; font-size: 0.82rem; opacity: 0.75; line-height: 1.35; }
        .card-icon {
            width: 52px; height: 52px; border-radius: 12px; margin: 0 auto 8px;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;
            <?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?>
        }
        .card-icon { overflow: hidden; }
        .card-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
        <?php if ($is_sweet): ?>
        .card-icon.sweet-sticker {
            width: 72px; height: 72px; border-radius: 16px; background: #FFFBFA;
            padding: 4px; box-sizing: border-box;
        }
        .card-icon.sweet-sticker img { object-fit: contain; border-radius: 12px; }
        <?php endif; ?>
        .pill {
            display: inline-block; margin-top: 10px; font-size: 0.72rem; border-radius: 999px;
            padding: 3px 10px;
            <?php if ($is_sweet): ?>background: #FFF0F2; color: #E55163; border: 1px solid #F3C5CC;
            <?php else: ?>background: #EAF1FA; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?>
        }
        .soon-box {
            background: white; border-radius: 18px; padding: 18px 18px 16px; margin-top: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.5;
        }
        .soon-box h2 {
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
            font-size: 1.35rem; margin: 0 0 8px;
        }
        .soon-box ul { margin: 8px 0 0; padding-left: 1.2rem; opacity: 0.85; }
        .soon-box li { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
        <h1><?php echo $is_sweet ? 'Catering' : 'Catering'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Trays, parties & big sandwich energy' : 'Events, trays, and party orders'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Your catering kitchen sink — track inquiries, event dates, tray packages, and delivery notes. This is the first shell; full CRM tools roll out next 🥂💕'
                : 'Catering hub shell: inquiries, event dates, tray packages, and delivery notes. Full CRM tools come next.'; ?>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Quick lanes' : 'Quick lanes'; ?></div>
        <div class="grid">
            <div class="card" aria-disabled="true">
                <div class="card-icon" aria-hidden="true">📝</div>
                <h3><?php echo $is_sweet ? 'Inquiries' : 'Inquiries'; ?></h3>
                <p><?php echo $is_sweet ? 'Capture party requests & guest counts' : 'Capture party requests and guest counts'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Coming soon' : 'Coming soon'; ?></span>
            </div>
            <div class="card" aria-disabled="true">
                <div class="card-icon" aria-hidden="true">📅</div>
                <h3><?php echo $is_sweet ? 'Upcoming events' : 'Upcoming events'; ?></h3>
                <p><?php echo $is_sweet ? 'Calendar of booked trays & drop-offs' : 'Calendar of booked trays and drop-offs'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Coming soon' : 'Coming soon'; ?></span>
            </div>
            <div class="card" aria-disabled="true">
                <div class="card-icon" aria-hidden="true">🥪</div>
                <h3><?php echo $is_sweet ? 'Tray packages' : 'Tray packages'; ?></h3>
                <p><?php echo $is_sweet ? 'Standard party menus & price sheets' : 'Standard party menus and price sheets'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Coming soon' : 'Coming soon'; ?></span>
            </div>
            <div class="card" aria-disabled="true">
                <div class="card-icon" aria-hidden="true">🚚</div>
                <h3><?php echo $is_sweet ? 'Delivery notes' : 'Delivery notes'; ?></h3>
                <p><?php echo $is_sweet ? 'Address, window & setup reminders' : 'Address, window, and setup reminders'; ?></p>
                <span class="pill"><?php echo $is_sweet ? 'Coming soon' : 'Coming soon'; ?></span>
            </div>
        </div>

        <div class="soon-box">
            <h2><?php echo $is_sweet ? 'What\'s baking next' : 'What\'s next'; ?></h2>
            <p><?php echo $is_sweet
                ? 'This page is live so Catering has a home in Sandwich HQ. Next up:'
                : 'Catering now has a top-level Admin home. Planned next:'; ?></p>
            <ul>
                <li><?php echo $is_sweet ? 'Inquiry form → confirmed event pipeline' : 'Inquiry → confirmed event pipeline'; ?></li>
                <li><?php echo $is_sweet ? 'Tray package builder tied to Menu & Recipes' : 'Tray package builder tied to Menu & Recipes'; ?></li>
                <li><?php echo $is_sweet ? 'Staffing notes that talk to Schedules & Shifts' : 'Staffing notes linked to Schedules & Shifts'; ?></li>
                <li><?php echo $is_sweet ? 'Simple deposit / balance checklist' : 'Simple deposit / balance checklist'; ?></li>
            </ul>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
</body>
</html>
