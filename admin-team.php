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

    <title><?php echo $is_sweet ? 'Team & Roles' : 'Team & Roles'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
            font-size: 2.8rem; margin: 0;
        }
        .subtitle { margin: 10px 0 0; font-size: 1.15rem; opacity: 0.9; }
        .content { padding: 24px 14px 40px; max-width: 1200px; margin: 0 auto; }
        .intro {
            background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.92;
        }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
        .card {
            background: white; border-radius: 16px; padding: 16px 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center;
            transition: all 0.3s; text-decoration: none; color: inherit; display: block;
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
        .section-label {
            font-size: 0.9rem; opacity: 0.7; margin: 18px 4px 10px;
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163; font-size: 1.1rem;
            <?php else: ?>font-weight: 600;<?php endif; ?>
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
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
        <h1><?php echo $is_sweet ? 'Team & Roles' : 'Team & Roles'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Roster, handbook & training manuals' : 'Roster, handbook, and training manuals'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Your people hub: who works here, the house handbook, and Training Manuals (FOH + BOH). Edit the built-in guides or upload your own Word / PDF 💕'
                : 'People hub: roster and permissions, employee handbook, and Training Manuals (FOH + BOH). Edit starters or upload Word / PDF.'; ?>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'People' : 'People'; ?></div>
        <div class="grid">
            <a href="/admin/roster" class="card">
                <?php pbj_render_card_icon('team-hub/roster', 'Crew roster', '📇'); ?>
                <h3><?php echo $is_sweet ? 'Crew roster' : 'Team roster'; ?></h3>
                <p><?php echo $is_sweet ? 'Staff list, multi-role wages & permissions' : 'Staff list, roles, wages, and permissions'; ?></p>
            </a>
            <a href="/admin/onboarding" class="card">
                <?php pbj_render_card_icon('team-hub/invite', 'Invite & Onboarding', '✉️'); ?>
                <h3><?php echo $is_sweet ? 'Invite & Onboarding' : 'Invite & Onboarding'; ?></h3>
                <p><?php echo $is_sweet ? 'Invite codes & editable training tracks' : 'Invite codes and training tracks'; ?></p>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Handbooks & manuals' : 'Handbooks & manuals'; ?></div>
        <div class="grid">
            <a href="/admin/docs?type=handbook" class="card">
                <?php pbj_render_card_icon('team-hub/handbook', 'Employee Handbook', '📘'); ?>
                <h3><?php echo $is_sweet ? 'Employee Handbook' : 'Employee Handbook'; ?></h3>
                <p><?php echo $is_sweet ? 'House rules everyone signs up for — edit or upload' : 'House policies for all staff — edit or upload'; ?></p>
            </a>
            <a href="/admin/docs?type=training_manuals" class="card">
                <?php pbj_render_card_icon('team-hub/foh-manual', 'Training Manuals', '📗'); ?>
                <h3><?php echo $is_sweet ? 'Training Manuals' : 'Training Manuals'; ?></h3>
                <p><?php echo $is_sweet ? 'FOH + BOH training in one place — edit or upload' : 'FOH and BOH training in one place — edit or upload'; ?></p>
            </a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
</body>
</html>
