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
    <title><?php echo $is_sweet ? 'Quick Tools' : 'Quick Tools'; ?> • <?php echo pbj_hub_label('boh'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; font-size: 1rem; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.6rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; opacity: 0.9; font-size: 1.02rem; }
        .link-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .chip { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC; font-family: 'DreamingOutLoudPro', serif;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE; font-family: 'Lora', serif;<?php endif; ?> }
        .tabs { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .tab { flex: 1; min-width: 90px; border: none; border-radius: 14px; padding: 12px 8px; font-size: 0.95rem; cursor: pointer; box-shadow: 0 3px 10px rgba(0,0,0,0.08); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .tab.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .panel { display: none; } .panel.active { display: block; }
        .card { background: white; border-radius: 18px; padding: 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.45rem; margin: 0 0 14px; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; align-items: flex-end; }
        .field { flex: 1; min-width: 110px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 12px; font-size: 1.05rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field input:focus, .field select:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .result { margin-top: 8px; padding: 16px; border-radius: 14px; font-size: 1.35rem; text-align: center; <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .timer-card { border: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; border-radius: 16px; padding: 14px; margin-bottom: 12px; }
        .timer-card h3 { margin: 0 0 8px; font-size: 1.1rem; }
        .timer-display { font-size: 2.6rem; text-align: center; letter-spacing: 0.04em; margin: 6px 0 12px; <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> }
        .timer-display.warn { color: #C62828; }
        .timer-display.done { color: #1F6B4A; }
        .btn-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { flex: 1; min-width: 80px; border: none; border-radius: 14px; padding: 12px 10px; font-size: 0.98rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .presets { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
        .preset { border: none; border-radius: 999px; padding: 8px 12px; font-size: 0.9rem; cursor: pointer; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; color: inherit; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .notes-area { width: 100%; min-height: 140px; box-sizing: border-box; border-radius: 14px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 14px; font-size: 1.05rem; resize: vertical; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .notes-area:focus { outline: none; border-color: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; }
        .notes-status { margin-top: 8px; font-size: 0.9rem; opacity: 0.65; min-height: 1.2em; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; }
        .actions-bar .btn { flex: 1; }
        .cheat { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .cheat-item { padding: 12px; border-radius: 12px; text-align: center; font-size: 0.95rem; line-height: 1.35; position: relative; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?> }
        .cheat-item strong { display: block; font-size: 1.05rem; margin-bottom: 2px; }
        .cheat-item .cheat-actions { display: flex; justify-content: center; gap: 6px; margin-top: 8px; }
        .cheat-item .btn-tiny { padding: 4px 8px; font-size: 0.78rem; border-radius: 8px; border: none; cursor: pointer; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f; box-shadow: 0 1px 4px rgba(0,0,0,0.08);<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44; box-shadow: 0 1px 4px rgba(0,0,0,0.08);<?php endif; ?> }
        .cheat-item .btn-tiny.danger { background: #FDECEA; color: #B71C1C; box-shadow: none; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; align-items: flex-end; justify-content: center; padding: 16px; }
        .modal-backdrop.show { display: flex; }
        .modal { background: white; border-radius: 20px; width: 100%; max-width: 480px; padding: 22px 20px; }
        .modal h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 14px; }
        .field-block { margin-bottom: 12px; }
        .field-block label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field-block input { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; }
        .print-only { display: none; }
        .pad-print { display: none; white-space: pre-wrap; font-size: 11pt; line-height: 1.45; border: 1px solid #ccc; padding: 12px; min-height: 200px; }
        @media print {
            .no-print, .back-link, .tabs, .link-row, .intro, .actions-bar, .bottom-nav, #bottom-nav, nav, .btn, .modal-backdrop, .cheat-actions, .hint { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; padding-bottom: 0; color: #000; }
            .header { background: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 12px 16px; }
            h1 { font-size: 1.4rem; }
            .subtitle { display: none; }
            .content { padding: 8px 12px; max-width: none; }
            .panel { display: none !important; }
            body.print-cheat #panel-cheat { display: block !important; }
            body.print-pad #panel-notes { display: block !important; }
            .card { box-shadow: none; border: 1px solid #ccc; }
            .cheat { gap: 6px; }
            .cheat-item { border: 1px solid #ccc; background: white !important; padding: 8px; break-inside: avoid; }
            .notes-area { display: none !important; }
            body.print-pad .pad-print { display: block !important; }
            body.print-pad .notes-status { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/BOH" class="back-link">← <?php echo pbj_back_to_hub('boh'); ?></a>
        <h1><?php echo $is_sweet ? 'Quick Tools' : 'Quick Tools'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Converters, timers & kitchen shortcuts' : 'Converters, timers, and kitchen shortcuts'; ?></p>
    </div>

    <div class="content">
        <div class="intro no-print">
            <?php echo $is_sweet
                ? 'Line helpers for mid-rush: convert units, run timers, scale yields, produce/meat yield charts, and jot notes 🛠️'
                : 'Line helpers: converters, timers, yield charts, and notes.'; ?>
        </div>
        <div class="link-row no-print">
            <a class="chip" href="/BOH/yields"><?php echo $is_sweet ? '🥗 Yields' : '🥗 Yields'; ?></a>
            <a class="chip" href="/BOH/recipe-cards"><?php echo $is_sweet ? '📖 Recipes' : '📖 Recipes'; ?></a>
            <a class="chip" href="/BOH/prep"><?php echo $is_sweet ? '🔪 Prep lists' : '🔪 Prep lists'; ?></a>
            <a class="chip" href="/BOH/temps"><?php echo $is_sweet ? '🌡️ Temps' : '🌡️ Temps'; ?></a>
            <a class="chip" href="/admin/costing"><?php echo $is_sweet ? '💰 Costing' : '💰 Costing'; ?></a>
        </div>

        <div class="tabs no-print">
            <button type="button" class="tab active" data-tab="convert"><?php echo $is_sweet ? '⚖️ Convert' : 'Convert'; ?></button>
            <button type="button" class="tab" data-tab="timer"><?php echo $is_sweet ? '⏱️ Timers' : 'Timers'; ?></button>
            <button type="button" class="tab" data-tab="scale"><?php echo $is_sweet ? '🔢 Scale' : 'Scale'; ?></button>
            <button type="button" class="tab" data-tab="notes"><?php echo $is_sweet ? '📝 Pad' : 'Pad'; ?></button>
            <button type="button" class="tab" data-tab="cheat"><?php echo $is_sweet ? '📌 Cheat' : 'Cheat'; ?></button>
            <button type="button" class="tab" data-tab="yields"><?php echo $is_sweet ? '🥗 Yields' : 'Yields'; ?></button>
        </div>
        <div class="print-only" id="print-tools-header" style="margin-bottom:8px;font-weight:600;"></div>

        <div class="panel active" id="panel-convert">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Unit converter' : 'Unit converter'; ?></h2>
                <div class="field-row">
                    <div class="field">
                        <label for="conv-value"><?php echo $is_sweet ? 'Amount' : 'Amount'; ?></label>
                        <input type="number" id="conv-value" step="any" value="1">
                    </div>
                    <div class="field">
                        <label for="conv-from"><?php echo $is_sweet ? 'From' : 'From'; ?></label>
                        <select id="conv-from">
                            <option value="cup">Cup</option>
                            <option value="tbsp">Tbsp</option>
                            <option value="tsp">Tsp</option>
                            <option value="floz">Fl oz</option>
                            <option value="ml">ml</option>
                            <option value="l">Liter</option>
                            <option value="oz">oz (weight)</option>
                            <option value="lb">lb</option>
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="f">°F</option>
                            <option value="c">°C</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="conv-to"><?php echo $is_sweet ? 'To' : 'To'; ?></label>
                        <select id="conv-to">
                            <option value="tbsp">Tbsp</option>
                            <option value="cup">Cup</option>
                            <option value="tsp">Tsp</option>
                            <option value="floz">Fl oz</option>
                            <option value="ml" selected>ml</option>
                            <option value="l">Liter</option>
                            <option value="oz">oz (weight)</option>
                            <option value="lb">lb</option>
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="f">°F</option>
                            <option value="c">°C</option>
                        </select>
                    </div>
                </div>
                <div class="result" id="conv-result">—</div>
                <p class="hint" style="margin-top:12px;"><?php echo $is_sweet ? 'Volume & weight are separate families. Temps convert °F ↔ °C only.' : 'Volume and weight are separate families. Temps convert °F ↔ °C only.'; ?></p>
            </div>
        </div>

        <div class="panel" id="panel-timer">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Kitchen timers' : 'Kitchen timers'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'Two timers for the line — fries + steak, pasta + sauce, whatever you need.' : 'Two independent timers for the line.'; ?></p>
                <div id="timers-root"></div>
            </div>
        </div>

        <div class="panel" id="panel-scale">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Recipe scaler' : 'Recipe scaler'; ?></h2>
                <p class="hint"><?php echo $is_sweet ? 'Scale any amount up or down by yield. For full recipe cards, open Standardized Recipes.' : 'Scale any amount by yield. Full cards live under Standardized Recipes.'; ?></p>
                <div class="field-row">
                    <div class="field">
                        <label for="scale-amount"><?php echo $is_sweet ? 'Original amount' : 'Original amount'; ?></label>
                        <input type="number" id="scale-amount" step="any" value="1">
                    </div>
                    <div class="field">
                        <label for="scale-from"><?php echo $is_sweet ? 'Original yield' : 'Original yield'; ?></label>
                        <input type="number" id="scale-from" step="any" value="4" placeholder="serves / batch">
                    </div>
                    <div class="field">
                        <label for="scale-to"><?php echo $is_sweet ? 'New yield' : 'New yield'; ?></label>
                        <input type="number" id="scale-to" step="any" value="10">
                    </div>
                </div>
                <div class="result" id="scale-result">—</div>
                <a href="/BOH/recipe-cards" class="btn btn-secondary" style="width:100%;margin-top:12px;box-sizing:border-box;"><?php echo $is_sweet ? 'Open recipe cards →' : 'Open recipe cards →'; ?></a>
            </div>
        </div>

        <div class="panel" id="panel-notes">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Scratch pad' : 'Scratch pad'; ?></h2>
                <p class="hint no-print"><?php echo $is_sweet ? 'Quick kitchen notes for this device — 86s, ticket reminders, oil change times…' : 'Quick kitchen notes for this device.'; ?></p>
                <textarea class="notes-area no-print" id="scratch-pad" placeholder="<?php echo $is_sweet ? 'e.g. 86 tomato jam · fryer oil change after lunch · prep more pickles…' : 'e.g. 86 tomato jam · oil change after lunch…'; ?>"></textarea>
                <div class="pad-print" id="pad-print"></div>
                <div class="notes-status no-print" id="notes-status"></div>
                <div class="btn-row no-print" style="margin-top:12px;">
                    <button type="button" class="btn btn-secondary" id="print-pad-btn"><?php echo $is_sweet ? '🖨️ Print pad' : 'Print pad'; ?></button>
                    <button type="button" class="btn btn-secondary" id="clear-pad-btn"><?php echo $is_sweet ? 'Clear pad' : 'Clear pad'; ?></button>
                </div>
            </div>
        </div>

        <div class="panel" id="panel-cheat">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Quick reference' : 'Quick reference'; ?></h2>
                <p class="hint no-print"><?php echo $is_sweet ? 'Handy kitchen numbers — add your house notes. Not a substitute for local code.' : 'Handy kitchen numbers. Add house notes. Follow local code.'; ?></p>
                <div class="cheat" id="cheat-grid"></div>
                <div class="btn-row no-print" style="margin-top:14px;">
                    <button type="button" class="btn btn-primary" id="add-cheat-btn"><?php echo $is_sweet ? '+ Add reference' : '+ Add reference'; ?></button>
                    <button type="button" class="btn btn-secondary" id="print-cheat-btn"><?php echo $is_sweet ? '🖨️ Print reference' : 'Print reference'; ?></button>
                </div>
                <div class="btn-row no-print" style="margin-top:8px;">
                    <button type="button" class="btn btn-secondary" id="reset-cheat-btn"><?php echo $is_sweet ? 'Reset starters' : 'Reset starters'; ?></button>
                    <a href="/BOH/temps" class="btn btn-secondary"><?php echo $is_sweet ? 'Temp logs →' : 'Temp logs →'; ?></a>
                </div>
            </div>
        </div>

        <div class="panel" id="panel-yields">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Produce & meat yields' : 'Produce & meat yields'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'Tomato ~96%, cantaloupe ~60%, whole chicken ~65%… Yield waste must land in food cost. Open the full chart for EP cost math, then set <strong>usable yield %</strong> on Costing.'
                    : 'AP→EP yield charts and EP cost calculator. Set usable yield % on Costing for true food cost.'; ?></p>
                <div class="cheat">
                    <div class="cheat-item"><strong>Tomato</strong>~96% EP</div>
                    <div class="cheat-item"><strong>Cantaloupe</strong>~60% EP</div>
                    <div class="cheat-item"><strong>Onion</strong>~89% EP</div>
                    <div class="cheat-item"><strong>Avocado</strong>~67% EP</div>
                    <div class="cheat-item"><strong>Whole chicken</strong>~65% meat</div>
                    <div class="cheat-item"><strong>Shrimp shell-on</strong>~55% peeled</div>
                    <div class="cheat-item"><strong>EP cost</strong>AP $ ÷ yield%</div>
                    <div class="cheat-item"><strong>Buy for 5 lb EP</strong>5 ÷ yield%</div>
                </div>
                <a href="/BOH/yields" class="btn btn-primary" style="width:100%;margin-top:14px;box-sizing:border-box;"><?php echo $is_sweet ? 'Open full yield chart →' : 'Open full yield chart →'; ?></a>
                <a href="/admin/costing" class="btn btn-secondary" style="width:100%;margin-top:10px;box-sizing:border-box;"><?php echo $is_sweet ? 'Costing (set yield %) →' : 'Costing (set yield %) →'; ?></a>
            </div>
        </div>

        <div class="actions-bar no-print">
            <a href="/BOH" class="btn btn-primary"><?php echo pbj_back_to_hub('boh'); ?></a>
        </div>
    </div>

    <div class="modal-backdrop no-print" id="cheat-modal">
        <div class="modal">
            <h2 id="cheat-modal-title"><?php echo $is_sweet ? 'Reference item' : 'Reference item'; ?></h2>
            <form id="cheat-form">
                <input type="hidden" id="cheat-edit-id">
                <div class="field-block">
                    <label for="cheat-title"><?php echo $is_sweet ? 'Title' : 'Title'; ?></label>
                    <input id="cheat-title" required maxlength="80" placeholder="<?php echo $is_sweet ? 'e.g. Hot hold' : 'e.g. Hot hold'; ?>">
                </div>
                <div class="field-block">
                    <label for="cheat-body"><?php echo $is_sweet ? 'Detail' : 'Detail'; ?></label>
                    <input id="cheat-body" required maxlength="160" placeholder="<?php echo $is_sweet ? 'e.g. 135°F+' : 'e.g. 135°F+'; ?>">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cheat-cancel"><?php echo $is_sweet ? 'Cancel' : 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $is_sweet ? 'Save' : 'Save'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>

    <script>
    (function () {
        const isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyToolsPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var ok = canP('boh.tools.use');
                var c = document.querySelector('.content');
                if (c) {
                    var banner = document.getElementById('tools-denied');
                    if (!ok) {
                        if (!banner) {
                            banner = document.createElement('div');
                            banner.id = 'tools-denied';
                            banner.className = 'intro';
                            banner.style.margin = '0 0 14px';
                            banner.textContent = 'You do not have permission to use Quick Tools.';
                            c.insertBefore(banner, c.firstChild);
                        }
                        banner.style.display = '';
                        // Hide interactive tools without destroying the page
                        c.querySelectorAll('.tabs, .tab, .panel, .card, .toolbar, .actions, .actions-bar').forEach(function (el) {
                            if (el.id === 'tools-denied') return;
                            el.style.display = 'none';
                        });
                    } else if (banner) {
                        banner.style.display = 'none';
                        c.querySelectorAll('.tabs, .tab, .panel, .card, .toolbar, .actions, .actions-bar').forEach(function (el) {
                            el.style.display = '';
                        });
                    }
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
                try { if (typeof renderAll === 'function') renderAll(); } catch (e) {}
                try { if (typeof renderEquip === 'function') renderEquip(); } catch (e) {}
                try { if (typeof renderList === 'function') renderList(); } catch (e) {}
            }


        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }

        document.querySelectorAll('.tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var key = tab.dataset.tab;
                document.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t === tab); });
                ['convert', 'timer', 'scale', 'notes', 'cheat', 'yields'].forEach(function (k) {
                    document.getElementById('panel-' + k).classList.toggle('active', k === key);
                });
            });
        });

        // Converter
        var toBase = {
            tsp: 4.92892, tbsp: 14.7868, floz: 29.5735, cup: 236.588, ml: 1, l: 1000,
            oz: 28.3495, lb: 453.592, g: 1, kg: 1000
        };
        var volume = ['tsp', 'tbsp', 'floz', 'cup', 'ml', 'l'];
        var weight = ['oz', 'lb', 'g', 'kg'];
        var temp = ['f', 'c'];

        function round(n) {
            if (Math.abs(n) >= 100) return Math.round(n * 10) / 10;
            if (Math.abs(n) >= 10) return Math.round(n * 100) / 100;
            return Math.round(n * 1000) / 1000;
        }
        function labelUnit(u) {
            var map = { tsp: 'tsp', tbsp: 'tbsp', floz: 'fl oz', cup: 'cup', ml: 'ml', l: 'L', oz: 'oz', lb: 'lb', g: 'g', kg: 'kg' };
            return map[u] || u;
        }
        function convert() {
            var val = parseFloat(document.getElementById('conv-value').value);
            var from = document.getElementById('conv-from').value;
            var to = document.getElementById('conv-to').value;
            var out = document.getElementById('conv-result');
            if (isNaN(val)) { out.textContent = '—'; return; }
            if (temp.indexOf(from) !== -1 || temp.indexOf(to) !== -1) {
                if (!(temp.indexOf(from) !== -1 && temp.indexOf(to) !== -1)) {
                    out.textContent = isSweet ? 'Temps only convert °F ↔ °C' : 'Temps only convert °F ↔ °C';
                    return;
                }
                var c = from === 'c' ? val : (val - 32) * 5 / 9;
                var result = to === 'c' ? c : (c * 9 / 5) + 32;
                out.textContent = round(result) + ' °' + to.toUpperCase();
                return;
            }
            var fromVol = volume.indexOf(from) !== -1;
            var toVol = volume.indexOf(to) !== -1;
            var fromWt = weight.indexOf(from) !== -1;
            var toWt = weight.indexOf(to) !== -1;
            if ((fromVol && toWt) || (fromWt && toVol)) {
                out.textContent = isSweet ? 'Can\'t mix volume ↔ weight' : 'Can\'t mix volume ↔ weight';
                return;
            }
            out.textContent = round((val * toBase[from]) / toBase[to]) + ' ' + labelUnit(to);
        }
        ['conv-value', 'conv-from', 'conv-to'].forEach(function (id) {
            document.getElementById(id).addEventListener('input', convert);
            document.getElementById(id).addEventListener('change', convert);
        });
        convert();

        // Dual timers
        var timers = [
            { id: 0, label: isSweet ? 'Timer A' : 'Timer A', remaining: 5 * 60, total: 5 * 60, interval: null, min: 5, sec: 0 },
            { id: 1, label: isSweet ? 'Timer B' : 'Timer B', remaining: 10 * 60, total: 10 * 60, interval: null, min: 10, sec: 0 }
        ];

        function fmt(sec) {
            sec = Math.max(0, Math.floor(sec));
            var m = Math.floor(sec / 60);
            var s = sec % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }

        function paintTimers() {
            document.getElementById('timers-root').innerHTML = timers.map(function (t) {
                var cls = 'timer-display';
                if (t.remaining <= 0 && !t.interval) cls += ' done';
                else if (t.remaining > 0 && t.remaining <= 10) cls += ' warn';
                var display = (t.remaining <= 0 && !t.interval) ? (isSweet ? 'Done! 🔔' : 'Done!') : fmt(t.remaining);
                return '<div class="timer-card" data-tid="' + t.id + '">' +
                    '<h3>' + t.label + '</h3>' +
                    '<div class="presets">' +
                    [3,5,10,15,20,30].map(function (m) {
                        return '<button type="button" class="preset" data-tid="' + t.id + '" data-mins="' + m + '">' + m + 'm</button>';
                    }).join('') +
                    '</div>' +
                    '<div class="field-row">' +
                    '<div class="field"><label>' + (isSweet ? 'Minutes' : 'Minutes') + '</label>' +
                    '<input type="number" class="t-min" data-tid="' + t.id + '" min="0" max="180" value="' + t.min + '"></div>' +
                    '<div class="field"><label>' + (isSweet ? 'Seconds' : 'Seconds') + '</label>' +
                    '<input type="number" class="t-sec" data-tid="' + t.id + '" min="0" max="59" value="' + t.sec + '"></div>' +
                    '</div>' +
                    '<div class="' + cls + '">' + display + '</div>' +
                    '<div class="btn-row">' +
                    '<button type="button" class="btn btn-primary t-start" data-tid="' + t.id + '">' + (isSweet ? 'Start' : 'Start') + '</button>' +
                    '<button type="button" class="btn btn-secondary t-pause" data-tid="' + t.id + '">' + (isSweet ? 'Pause' : 'Pause') + '</button>' +
                    '<button type="button" class="btn btn-secondary t-reset" data-tid="' + t.id + '">' + (isSweet ? 'Reset' : 'Reset') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function stopTimer(t) {
            if (t.interval) { clearInterval(t.interval); t.interval = null; }
        }

        function readTimerInputs(t) {
            return Math.max(0, (parseInt(t.min, 10) || 0) * 60 + (parseInt(t.sec, 10) || 0));
        }

        document.getElementById('timers-root').addEventListener('click', function (e) {
            var preset = e.target.closest('.preset');
            if (preset) {
                var t = timers[parseInt(preset.dataset.tid, 10)];
                stopTimer(t);
                var mins = parseInt(preset.dataset.mins, 10);
                t.min = mins; t.sec = 0;
                t.remaining = mins * 60; t.total = t.remaining;
                paintTimers();
                return;
            }
            var start = e.target.closest('.t-start');
            if (start) {
                var t2 = timers[parseInt(start.dataset.tid, 10)];
                if (t2.interval) return;
                if (t2.remaining <= 0) {
                    t2.remaining = readTimerInputs(t2);
                    t2.total = t2.remaining;
                }
                if (t2.remaining <= 0) return;
                paintTimers();
                t2.interval = setInterval(function () {
                    t2.remaining -= 1;
                    if (t2.remaining <= 0) {
                        stopTimer(t2);
                        t2.remaining = 0;
                        try { if (navigator.vibrate) navigator.vibrate([200, 100, 200, 100, 200]); } catch (err) {}
                    }
                    paintTimers();
                }, 1000);
                return;
            }
            var pause = e.target.closest('.t-pause');
            if (pause) {
                stopTimer(timers[parseInt(pause.dataset.tid, 10)]);
                paintTimers();
                return;
            }
            var reset = e.target.closest('.t-reset');
            if (reset) {
                var t3 = timers[parseInt(reset.dataset.tid, 10)];
                stopTimer(t3);
                t3.remaining = readTimerInputs(t3);
                t3.total = t3.remaining;
                paintTimers();
            }
        });

        document.getElementById('timers-root').addEventListener('change', function (e) {
            var el = e.target;
            if (!el.classList.contains('t-min') && !el.classList.contains('t-sec')) return;
            var t = timers[parseInt(el.dataset.tid, 10)];
            if (t.interval) return;
            if (el.classList.contains('t-min')) t.min = parseInt(el.value, 10) || 0;
            if (el.classList.contains('t-sec')) t.sec = parseInt(el.value, 10) || 0;
            t.remaining = readTimerInputs(t);
            t.total = t.remaining;
            paintTimers();
        });

        paintTimers();

        // Scaler
        function scale() {
            var amount = parseFloat(document.getElementById('scale-amount').value);
            var from = parseFloat(document.getElementById('scale-from').value);
            var to = parseFloat(document.getElementById('scale-to').value);
            var out = document.getElementById('scale-result');
            if (!amount || !from || !to || from === 0) { out.textContent = '—'; return; }
            out.textContent = round(amount * (to / from));
        }
        ['scale-amount', 'scale-from', 'scale-to'].forEach(function (id) {
            document.getElementById(id).addEventListener('input', scale);
        });
        scale();

        // Scratch pad
        var padKey = 'pbj_heat_tools_pad_v1';
        var pad = document.getElementById('scratch-pad');
        var status = document.getElementById('notes-status');
        var padTimer = null;
        pad.value = localStorage.getItem(padKey) || '';
        pad.addEventListener('input', function () {
            localStorage.setItem(padKey, pad.value);
            status.textContent = isSweet ? 'Saved 💾' : 'Saved';
            clearTimeout(padTimer);
            padTimer = setTimeout(function () { status.textContent = ''; }, 1200);
        });

        function stampToolsHeader(label) {
            var d = new Date();
            document.getElementById('print-tools-header').textContent =
                label + ' · ' + d.toLocaleDateString() + ' · ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        document.getElementById('print-pad-btn').addEventListener('click', function () {
            var text = pad.value.trim();
            if (!text) {
                alert(isSweet ? 'Pad is empty — jot something first ✨' : 'Pad is empty.');
                return;
            }
            document.getElementById('pad-print').textContent = pad.value;
            document.body.classList.remove('print-cheat');
            document.body.classList.add('print-pad');
            // ensure pad panel is active for print CSS
            document.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t.dataset.tab === 'notes'); });
            ['convert', 'timer', 'scale', 'notes', 'cheat', 'yields'].forEach(function (k) {
                document.getElementById('panel-' + k).classList.toggle('active', k === 'notes');
            });
            stampToolsHeader(isSweet ? 'Kitchen scratch pad' : 'Kitchen scratch pad');
            setTimeout(function () {
                window.print();
                document.body.classList.remove('print-pad');
            }, 60);
        });

        document.getElementById('clear-pad-btn').addEventListener('click', function () {
            if (!pad.value.trim()) return;
            if (!confirm(isSweet ? 'Clear the scratch pad?' : 'Clear the scratch pad?')) return;
            pad.value = '';
            localStorage.setItem(padKey, '');
            status.textContent = isSweet ? 'Cleared' : 'Cleared';
            setTimeout(function () { status.textContent = ''; }, 1200);
        });

        // Editable quick reference
        var cheatKey = 'pbj_heat_tools_cheat_v1';
        var defaultCheat = [
            { id: 'c1', title: '1 cup', body: '16 tbsp · 48 tsp · ~237 ml' },
            { id: 'c2', title: '1 lb', body: '16 oz · ~454 g' },
            { id: 'c3', title: '1 gal', body: '16 cups · ~3.8 L' },
            { id: 'c4', title: 'Hot hold', body: '135°F+' },
            { id: 'c5', title: 'Cold hold', body: '41°F or below' },
            { id: 'c6', title: 'Poultry', body: '165°F internal' },
            { id: 'c7', title: 'Cooling', body: '135→70 in 2h · 70→41 in 4h' },
            { id: 'c8', title: 'Danger zone', body: '41–135°F' }
        ];
        function loadCheat() {
            try {
                var r = JSON.parse(localStorage.getItem(cheatKey) || 'null');
                if (!r || !Array.isArray(r.items) || !r.items.length) return { items: defaultCheat.map(function (x) { return Object.assign({}, x); }) };
                return r;
            } catch (e) { return { items: defaultCheat.map(function (x) { return Object.assign({}, x); }) }; }
        }
        function saveCheat() { localStorage.setItem(cheatKey, JSON.stringify(cheatState)); }
        var cheatState = loadCheat();
        var cheatModal = document.getElementById('cheat-modal');

        function renderCheat() {
            var root = document.getElementById('cheat-grid');
            if (!cheatState.items.length) {
                root.innerHTML = '<div class="cheat-item" style="grid-column:1/-1;opacity:0.75;">' +
                    (isSweet ? 'No reference items yet — add your house numbers ✨' : 'No reference items yet. Add your house numbers.') +
                    '</div>';
                return;
            }
            root.innerHTML = cheatState.items.map(function (it) {
                return '<div class="cheat-item" data-id="' + esc(it.id) + '">' +
                    '<strong>' + esc(it.title) + '</strong>' + esc(it.body) +
                    '<div class="cheat-actions no-print">' +
                    '<button type="button" class="btn-tiny" data-act="edit-cheat" data-id="' + esc(it.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn-tiny danger" data-act="del-cheat" data-id="' + esc(it.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function openCheatModal(item) {
            document.getElementById('cheat-edit-id').value = item ? item.id : '';
            document.getElementById('cheat-modal-title').textContent = item
                ? (isSweet ? 'Edit reference' : 'Edit reference')
                : (isSweet ? 'Add reference' : 'Add reference');
            document.getElementById('cheat-title').value = item ? item.title : '';
            document.getElementById('cheat-body').value = item ? item.body : '';
            cheatModal.classList.add('show');
            setTimeout(function () { document.getElementById('cheat-title').focus(); }, 40);
        }

        document.getElementById('add-cheat-btn').addEventListener('click', function () { openCheatModal(null); });
        document.getElementById('cheat-cancel').addEventListener('click', function () { cheatModal.classList.remove('show'); });
        cheatModal.addEventListener('click', function (e) { if (e.target === cheatModal) cheatModal.classList.remove('show'); });

        document.getElementById('cheat-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var title = document.getElementById('cheat-title').value.trim();
            var body = document.getElementById('cheat-body').value.trim();
            if (!title || !body) return;
            var id = document.getElementById('cheat-edit-id').value;
            if (id) {
                var it = cheatState.items.find(function (x) { return x.id === id; });
                if (it) { it.title = title; it.body = body; }
            } else {
                cheatState.items.push({ id: uid(), title: title, body: body });
            }
            saveCheat();
            cheatModal.classList.remove('show');
            renderCheat();
        });

        document.getElementById('cheat-grid').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]');
            if (!btn) return;
            var id = btn.dataset.id;
            if (btn.dataset.act === 'edit-cheat') {
                var it = cheatState.items.find(function (x) { return x.id === id; });
                if (it) openCheatModal(it);
                return;
            }
            if (btn.dataset.act === 'del-cheat') {
                if (!confirm(isSweet ? 'Remove this reference?' : 'Remove this reference?')) return;
                cheatState.items = cheatState.items.filter(function (x) { return x.id !== id; });
                saveCheat();
                renderCheat();
            }
        });

        document.getElementById('reset-cheat-btn').addEventListener('click', function () {
            if (!confirm(isSweet ? 'Reset quick reference to starter cards?' : 'Reset quick reference to starters?')) return;
            cheatState = { items: defaultCheat.map(function (x) { return Object.assign({}, x); }) };
            saveCheat();
            renderCheat();
        });

        document.getElementById('print-cheat-btn').addEventListener('click', function () {
            document.body.classList.remove('print-pad');
            document.body.classList.add('print-cheat');
            document.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t.dataset.tab === 'cheat'); });
            ['convert', 'timer', 'scale', 'notes', 'cheat', 'yields'].forEach(function (k) {
                document.getElementById('panel-' + k).classList.toggle('active', k === 'cheat');
            });
            stampToolsHeader(isSweet ? 'Kitchen quick reference' : 'Kitchen quick reference');
            setTimeout(function () {
                window.print();
                document.body.classList.remove('print-cheat');
            }, 60);
        });

        renderCheat();
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyToolsPerms);
            document.addEventListener('pbj-perms-ready', applyToolsPerms);
})();
    </script>
</body>
</html>
