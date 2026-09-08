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

    <title><?php echo $is_sweet ? 'Menu & Standardized Recipes' : 'Menu & Standardized Recipes'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 90px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.6rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 14px 40px; max-width: 720px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; text-align: center; }
        .loop { background: white; border-radius: 18px; padding: 14px 16px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); font-size: 0.95rem; line-height: 1.45; opacity: 0.9; text-align: center; }
        .loop strong { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .section-label { font-size: 0.95rem; opacity: 0.65; margin: 6px 4px 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 480px) { .grid { grid-template-columns: 1fr; } }
        .card { background: white; border-radius: 16px; padding: 22px 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: block; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .card h3 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 1.2rem; margin: 10px 0 6px; }
        .card p { margin: 0; font-size: 0.88rem; opacity: 0.75; line-height: 1.35; }
        .card-icon { width: 64px; height: 64px; border-radius: 14px; margin: 0 auto 4px; display: flex; align-items: center; justify-content: center; font-size: 1.9rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 2px solid #E55163;<?php else: ?>background: #EEF2F8; border: 2px solid #1A2A44;<?php endif; ?> }
    
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
        <a href="/BOH" class="back-link">← <?php echo pbj_back_to_hub('boh'); ?></a>
        <h1><?php echo $is_sweet ? 'Menu & Standardized Recipes' : 'Menu & Standardized Recipes'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Guest sell prices and house specs' : 'Menu prices and house recipes'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Pick a path — menu engineering or standardized recipes. Ingredients on recipe cards feed costing 💕'
                : 'Menu engineering (prices + food cost) or standardized recipes for costing.'; ?>
        </div>
        <div class="loop">
            <?php echo $is_sweet
                ? '<strong>The loop:</strong> Recipes → Yields → Costing sheet → Menu FC%. Stock counts & product lists live under Sandwich HQ → Inventory (not here).'
                : '<strong>Loop:</strong> Recipes → Yields → Costing → Menu FC%. Product setup and lists are under Admin → Inventory only.'; ?>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Build & price' : 'Build & price'; ?></div>
        <div class="grid">
            <a href="/BOH/menu" data-perm-any="boh.recipes.menu_view,boh.recipes.menu_edit" class="card">
                <?php pbj_render_card_icon('recipes-hub/menu-engineering', 'Menu Engineering', '📈'); ?>
                <h3><?php echo $is_sweet ? 'Menu Engineering' : 'Menu Engineering'; ?></h3>
                <p><?php echo $is_sweet ? 'Plate cost · FC% · optional Star matrix' : 'Plate cost, FC%, optional Star matrix'; ?></p>
            </a>
            <a href="/BOH/recipe-cards" data-perm-any="boh.recipes.std_view,boh.recipes.std_edit" class="card">
                <?php pbj_render_card_icon('recipes-hub/standardized-recipes', 'Standardized Recipes', '🧾'); ?>
                <h3><?php echo $is_sweet ? 'Standardized Recipes' : 'Standardized Recipes'; ?></h3>
                <p><?php echo $is_sweet ? 'Specs, ingredients, method & plate' : 'Specs, ingredients, method, and plate'; ?></p>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Cost & yields' : 'Cost & yields'; ?></div>
        <div class="grid">
            <a href="/admin/costing" data-perm-any="boh.recipes.costing_view,boh.recipes.costing_edit" class="card">
                <?php pbj_render_card_icon('recipes-hub/costing', 'Costing Sheet', '🧮'); ?>
                <h3><?php echo $is_sweet ? 'Costing Sheet' : 'Costing Sheet'; ?></h3>
                <p><?php echo $is_sweet ? 'Case prices, conversions & plate cost' : 'Case prices, conversions, plate cost'; ?></p>
            </a>
            <a href="/BOH/yields" data-perm="boh.recipes.yields_use" class="card">
                <?php pbj_render_card_icon('recipes-hub/yields', 'Produce & Meat Yields', '🥩'); ?>
                <h3><?php echo $is_sweet ? 'Produce & Meat Yields' : 'Produce & Meat Yields'; ?></h3>
                <p><?php echo $is_sweet ? 'Usable yield % for AP → EP cost' : 'Usable yield % for AP to EP cost'; ?></p>
            </a>
        </div>

        <div class="section-label"><?php echo $is_sweet ? 'Optional food-cost tools' : 'Optional food-cost tools'; ?></div>
        <div class="grid">
            <a href="/admin/pmix" class="card">
                <?php pbj_render_card_icon('recipes-hub/menu-engineering', 'PMIX', '📊'); ?>
                <h3><?php echo $is_sweet ? 'PMIX / ideal FC' : 'PMIX / ideal FC'; ?></h3>
                <p><?php echo $is_sweet ? 'Qty sold × plate cost (optional)' : 'Qty × plate cost (optional)'; ?></p>
            </a>
            <a href="/admin/waste" class="card">
                <?php pbj_render_card_icon('recipes-hub/costing', 'Waste log', '🗑️'); ?>
                <h3><?php echo $is_sweet ? 'Waste log' : 'Waste log'; ?></h3>
                <p><?php echo $is_sweet ? 'Spoilage &amp; over-prep (optional)' : 'Spoilage & over-prep (optional)'; ?></p>
            </a>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
</body>
</html>
