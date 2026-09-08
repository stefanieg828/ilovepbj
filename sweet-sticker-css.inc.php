<?php
/**
 * Shared Sweet PBJ Vibes sticker card-icon CSS.
 * Include inside a <style> block after .card-icon base rules if needed.
 */
?>
.card-icon {
    overflow: hidden;
}
.card-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.card-icon.sweet-sticker {
    width: 72px;
    height: 72px;
    border-radius: 16px;
    background: #FFFBFA;
    padding: 4px;
    box-sizing: border-box;
    <?php if (!empty($is_sweet)): ?>
    border: 2px solid #E55163;
    <?php endif; ?>
}
.card-icon.sweet-sticker img {
    object-fit: contain;
    border-radius: 12px;
}
.map-icon {
    overflow: hidden;
}
.map-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
.map-icon.sweet-sticker {
    width: 64px;
    height: 64px;
    min-width: 64px;
    border-radius: 16px;
    background: #FFFBFA;
    padding: 4px;
    box-sizing: border-box;
}
