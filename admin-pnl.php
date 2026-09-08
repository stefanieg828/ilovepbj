<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'P&amp;L Statement' : 'P&amp;L Statement'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.4rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 800px; margin: 0 auto; }
        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
        .stats-row.three { grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 520px) { .stats-row.three { grid-template-columns: 1fr 1fr; } }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.15rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.78rem; opacity: 0.7; margin-top: 4px; line-height: 1.25; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0 0 10px; }
        .hint { font-size: 0.92rem; opacity: 0.75; margin: 0 0 12px; line-height: 1.45; }
        .hint a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .period-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .period-chip {
            border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer;
            background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
        }
        .period-chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .period-range { font-size: 0.92rem; opacity: 0.75; margin: -4px 0 14px; text-align: center; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?>
        }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .check-row { display: flex; align-items: flex-start; gap: 10px; margin: 10px 0 4px; font-size: 0.9rem; line-height: 1.35; }
        .check-row input { margin-top: 3px; flex-shrink: 0; }
        .pnl-table { width: 100%; border-collapse: collapse; font-size: 0.93rem; }
        .pnl-table th, .pnl-table td { padding: 9px 5px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; vertical-align: top; }
        .pnl-table th { opacity: 0.6; font-weight: normal; font-size: 0.8rem; text-align: right; }
        .pnl-table th:first-child, .pnl-table td:first-child { text-align: left; }
        .pnl-table td.amt { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .pnl-table td.pct { text-align: right; white-space: nowrap; opacity: 0.75; font-size: 0.85rem; width: 62px; }
        .pnl-table tr.section td { padding-top: 14px; font-size: 0.78rem; opacity: 0.55; letter-spacing: 0.04em; border-bottom: none; text-transform: uppercase; }
        .pnl-table tr.total td { font-weight: 700; border-top: 2px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; border-bottom: none; padding-top: 12px; }
        .pnl-table tr.subtotal td { font-weight: 600; <?php if ($is_sweet): ?>background: #FFF5F6;<?php else: ?>background: #EEF2F8;<?php endif; ?> }
        .pnl-table tr.memo td { opacity: 0.8; font-style: italic; }
        .pnl-table .sub { display: block; font-size: 0.76rem; opacity: 0.65; font-weight: normal; font-style: normal; margin-top: 2px; }
        .badge {
            display: inline-block; border-radius: 999px; padding: 2px 7px; font-size: 0.7rem; margin-left: 5px;
            <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?>
        }
        .badge.actual, .badge.orders, .badge.wages, .badge.pos { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .data-bar {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px;
            margin-bottom: 14px; padding: 12px 14px; border-radius: 14px;
            background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>;
            border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            font-size: 0.88rem; line-height: 1.4;
        }
        .data-bar .bits { flex: 1; min-width: 160px; opacity: 0.9; }
        .data-bar .bits strong { font-weight: 700; }
        .cov-pill {
            display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 0.72rem; font-weight: 600; margin-right: 4px;
        }
        .cov-pill.ok { background: #E8F8F1; color: #1F6B4A; }
        .cov-pill.warn { background: #FFF8E6; color: #8A6D1F; }
        .cov-pill.miss { background: #FFEBEE; color: #B71C1C; }
        .table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
        .table th, .table td { text-align: left; padding: 7px 4px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; }
        .table th { opacity: 0.65; font-weight: normal; }
        .table td.num, .table th.num { text-align: right; }
        .empty { text-align: center; padding: 20px; opacity: 0.8; line-height: 1.4; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 100px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.88rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .ok { color: #1F6B4A; }
        .warn { color: #C62828; }
        .exp-group { margin: 14px 0 8px; }
        .exp-group h3 {
            margin: 0 0 8px; font-size: 0.95rem; opacity: 0.7; letter-spacing: 0.02em;
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
        }
        .exp-row {
            display: grid; grid-template-columns: 1fr 110px 100px; gap: 8px; align-items: center;
            margin-bottom: 8px;
        }
        @media (max-width: 560px) {
            .exp-row { grid-template-columns: 1fr 1fr; }
            .exp-row .exp-label { grid-column: 1 / -1; font-size: 0.9rem; }
        }
        .exp-row .exp-label { font-size: 0.92rem; line-height: 1.3; }
        .exp-row input {
            width: 100%; box-sizing: border-box; border-radius: 10px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 8px 10px; font-size: 0.95rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5;<?php endif; ?>
        }
        .exp-head {
            display: grid; grid-template-columns: 1fr 110px 100px; gap: 8px;
            font-size: 0.75rem; opacity: 0.55; margin-bottom: 6px;
        }
        @media (max-width: 560px) { .exp-head { display: none; } }
        .exp-head span:not(:first-child) { text-align: right; }
        .other-list { margin: 8px 0 0; }
        .other-row {
            display: grid; grid-template-columns: 1fr 100px auto; gap: 8px; align-items: center;
            margin-bottom: 8px;
        }
        .other-row input {
            width: 100%; box-sizing: border-box; border-radius: 10px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 8px 10px; font-size: 0.95rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5;<?php endif; ?>
        }
        .form-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
        .opex-total {
            margin-top: 12px; padding: 12px 14px; border-radius: 12px; font-size: 0.95rem;
            display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap;
            <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE;<?php endif; ?>
        }
        .toast {
            position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(20px);
            background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white;
            padding: 12px 20px; border-radius: 999px; opacity: 0; pointer-events: none; transition: all 0.25s; z-index: 2000;
        }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .print-only { display: none; }
        @media print {
            .bottom-nav, .actions-bar, .period-chips, .no-print, .back-link, .toast, .data-bar { display: none !important; }
            .print-only { display: block !important; }
            body { padding-bottom: 0; background: white !important; color: #111 !important; }
            .header { background: white !important; color: black !important; padding: 8px 0 12px; border-bottom: 2px solid #333; }
            .header h1 { color: #111 !important; font-size: 1.6rem !important; }
            .header .subtitle { color: #333 !important; opacity: 1; }
            .card, .stat { box-shadow: none !important; border: 1px solid #ccc; break-inside: avoid; }
            .stat .num { color: #111 !important; }
            .pnl-table tr.total td { border-top: 2px solid #111 !important; }
            .content { max-width: 100%; padding: 12px 8px; }
            a { color: inherit !important; text-decoration: none !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports & Sales' : 'Back to Reports & Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'P&amp;L Statement' : 'P&amp;L Statement'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'POS sales &amp; labor · orders · rent &amp; opex' : 'POS sales & labor, orders, and opex'; ?></p>
    </div>
    <div class="content">
        <p class="hint"><?php echo $is_sweet
            ? 'Auto-fills from <a href="/admin/sales">Daily Sales</a> (incl. POS sync) · <a href="/admin/labor">Labor Snapshot</a> · <a href="/admin/auto-order">Auto-Order</a> · <a href="/admin/comps">Comps</a>. Sync POS above to refresh numbers. Monthly opex prorates to this window 💕'
            : 'Auto-fills Daily Sales, Labor, Auto-Order, Comps. Sync POS to refresh. Monthly opex is prorated to the period.'; ?></p>

        <div class="data-bar no-print" id="data-bar">
            <div class="bits" id="data-coverage"><?php echo $is_sweet ? 'Checking coverage…' : 'Checking coverage…'; ?></div>
            <button type="button" class="btn btn-primary btn-small" id="btn-sync-pos"><?php echo $is_sweet ? 'Sync POS' : 'Sync POS'; ?></button>
            <a href="/admin/pos-connect" class="btn btn-ghost btn-small"><?php echo $is_sweet ? 'Connections' : 'Connections'; ?></a>
        </div>

        <div class="period-chips no-print" id="period-chips">
            <button type="button" class="period-chip active" data-period="7d"><?php echo $is_sweet ? 'Last 7 days' : 'Last 7 days'; ?></button>
            <button type="button" class="period-chip" data-period="week"><?php echo $is_sweet ? 'This week' : 'This week'; ?></button>
            <button type="button" class="period-chip" data-period="mtd"><?php echo $is_sweet ? 'Month to date' : 'Month to date'; ?></button>
            <button type="button" class="period-chip" data-period="28d"><?php echo $is_sweet ? 'Last 4 weeks' : 'Last 4 weeks'; ?></button>
        </div>
        <p class="period-range" id="period-label">—</p>
        <div class="print-only" id="print-meta" style="margin-bottom:12px;font-size:0.9rem;"></div>

        <div class="stats-row three">
            <div class="stat"><div class="num" id="stat-sales">—</div><div class="lbl"><?php echo $is_sweet ? 'Net sales' : 'Net sales'; ?></div></div>
            <div class="stat"><div class="num" id="stat-prime">—</div><div class="lbl"><?php echo $is_sweet ? 'Prime cost %' : 'Prime cost %'; ?></div></div>
            <div class="stat"><div class="num" id="stat-contrib">—</div><div class="lbl"><?php echo $is_sweet ? 'Contribution' : 'Contribution'; ?></div></div>
        </div>
        <div class="stats-row three">
            <div class="stat"><div class="num" id="stat-labor-pct">—</div><div class="lbl"><?php echo $is_sweet ? 'Labor %' : 'Labor %'; ?></div></div>
            <div class="stat"><div class="num" id="stat-food-pct">—</div><div class="lbl"><?php echo $is_sweet ? 'Food %' : 'Food %'; ?></div></div>
            <div class="stat"><div class="num" id="stat-opex">—</div><div class="lbl"><?php echo $is_sweet ? 'Opex (period)' : 'Opex (period)'; ?></div></div>
        </div>
        <div class="stats-row three">
            <div class="stat"><div class="num" id="stat-cash-os">—</div><div class="lbl"><?php echo $is_sweet ? 'Cash over/short' : 'Cash over/short'; ?></div></div>
            <div class="stat"><div class="num" id="stat-theo-fc">—</div><div class="lbl"><?php echo $is_sweet ? 'Theo food cost $' : 'Theo food cost $'; ?></div></div>
            <div class="stat"><div class="num" id="stat-theo-var">—</div><div class="lbl"><?php echo $is_sweet ? 'Theo vs actual var' : 'Theo vs actual var'; ?></div></div>
            <div class="stat"><div class="num" id="stat-waste">—</div><div class="lbl"><?php echo $is_sweet ? 'Waste logged $' : 'Waste logged $'; ?></div></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Statement' : 'Statement'; ?></h2>
            <div id="statement-wrap"><div class="empty"><?php echo $is_sweet ? 'Loading…' : 'Loading…'; ?></div></div>
        </div>

        <div class="card no-print">
            <h2><?php echo $is_sweet ? 'Food cost overrides' : 'Food cost overrides'; ?></h2>
            <p class="hint" style="margin-top:-4px;"><?php echo $is_sweet
                ? 'By default food $ comes from <strong>saved Auto-Orders</strong> in this window (food categories). Type actual food invoices $ to override, or fall back to a % of net if there are no orders yet.'
                : 'Default: sum food lines on saved Auto-Orders in range. Optional actual $ override, else % of sales.'; ?></p>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'Fallback food cost % (if no orders)' : 'Fallback food cost %'; ?></label>
                    <input type="number" id="f-food-pct" min="0" max="100" step="0.1" value="30">
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'Actual food cost $ (override)' : 'Actual food cost $ (override)'; ?></label>
                    <input type="number" id="f-food-actual" min="0" step="0.01" placeholder="<?php echo $is_sweet ? 'Blank = orders or %' : 'Blank = orders or %'; ?>">
                </div>
            </div>
            <label class="check-row">
                <input type="checkbox" id="f-comps-reduce">
                <span><?php echo $is_sweet
                    ? 'Also subtract comps from contribution (only if comps are still inside net sales — usually leave off)'
                    : 'Subtract comps from contribution (only if not already removed from net)'; ?></span>
            </label>
            <label class="check-row">
                <input type="checkbox" id="f-waste-reduce">
                <span><?php echo $is_sweet
                    ? 'Subtract logged waste from contribution (optional — waste is a memo line by default)'
                    : 'Subtract waste log $ from contribution (optional; default is memo only)'; ?></span>
            </label>
            <p class="hint" id="orders-hint" style="margin-top:10px;"></p>
            <p class="hint" id="waste-hint" style="margin-top:6px;"></p>
        </div>

        <div class="card no-print">
            <h2><?php echo $is_sweet ? 'Operating expenses (monthly chart)' : 'Operating expenses (monthly)'; ?></h2>
            <p class="hint" style="margin-top:-4px;"><?php echo $is_sweet
                ? 'Enter typical <strong>monthly</strong> amounts. We prorate to this period (× days ÷ 30). Use the period column to override for just this window.'
                : 'Monthly amounts are prorated to the period (days ÷ 30). Period $ overrides the prorate for that line.'; ?></p>
            <div class="exp-head"><span><?php echo $is_sweet ? 'Category' : 'Category'; ?></span><span><?php echo $is_sweet ? 'Monthly $' : 'Monthly $'; ?></span><span><?php echo $is_sweet ? 'Period $' : 'Period $'; ?></span></div>
            <div id="expense-chart"></div>
            <div class="opex-total">
                <span><?php echo $is_sweet ? 'Period opex total (live)' : 'Period opex total'; ?></span>
                <strong id="opex-live">—</strong>
            </div>

            <div class="field" style="margin-top:16px;">
                <label><?php echo $is_sweet ? 'One-off / custom lines (this period only)' : 'Custom lines (this period)'; ?></label>
            </div>
            <div class="other-list" id="other-list"></div>
            <div class="form-actions">
                <button type="button" class="btn btn-ghost btn-small" id="btn-add-other"><?php echo $is_sweet ? '+ Custom line' : '+ Custom line'; ?></button>
                <button type="button" class="btn btn-primary btn-small" id="btn-save-pnl"><?php echo $is_sweet ? 'Save P&amp;L setup ✨' : 'Save P&amp;L setup'; ?></button>
            </div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Day-by-day' : 'Day-by-day'; ?></h2>
            <div id="table-wrap" style="overflow-x:auto;"></div>
        </div>

        <div class="actions-bar no-print">
            <a href="/admin/sales" class="btn btn-secondary"><?php echo $is_sweet ? '💰 Sales' : 'Sales'; ?></a>
            <a href="/admin/labor" class="btn btn-secondary"><?php echo $is_sweet ? '⏱️ Labor' : 'Labor'; ?></a>
            <a href="/admin/auto-order" class="btn btn-secondary"><?php echo $is_sweet ? '🛒 Auto-Order' : 'Auto-Order'; ?></a>
            <a href="/admin/pmix" class="btn btn-secondary"><?php echo $is_sweet ? 'PMIX' : 'PMIX'; ?></a>
            <a href="/admin/waste" class="btn btn-secondary"><?php echo $is_sweet ? 'Waste' : 'Waste'; ?></a>
            <a href="/admin/trends" class="btn btn-secondary"><?php echo $is_sweet ? '📊 Trends' : 'Trends'; ?></a>
            <button type="button" class="btn btn-secondary" id="btn-export-csv"><?php echo $is_sweet ? '⬇️ CSV export' : 'CSV export'; ?></button>
            <button type="button" class="btn btn-secondary" id="btn-print"><?php echo $is_sweet ? '🖨️ Print / PDF' : 'Print / PDF'; ?></button>
            <a href="/admin/reports" class="btn btn-primary"><?php echo $is_sweet ? 'Reports' : 'Reports'; ?></a>
        </div>
        <p class="hint no-print" style="text-align:center;margin-top:4px;"><?php echo $is_sweet
            ? 'CSV is bookkeeper-ready. Print → “Save as PDF” for a shareable statement 💕'
            : 'CSV for bookkeepers. Use Print → Save as PDF for a clean statement.'; ?></p>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/food-cost-shared.js?v=2"></script>
    <script src="/pos-sync-client.js?v=3"></script>
    <script src="/ops-nudges.js?v=2"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyRepPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var view = canP('admin.reports.view') || canP('admin.reports.edit');
                var edit = canP('admin.reports.edit');
                if (!view) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('rep-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="rep-denied">No permission to view reports.</div>');
                    }
                }
                if (!edit) {
                    document.querySelectorAll('input, select, textarea, button.btn-primary, button.btn-danger').forEach(function(el) {
                        if (el.closest('.bottom-nav') || el.tagName === 'A') return;
                        if (el.id && /back|print|export|tab/i.test(el.id)) return;
                        if (el.classList && el.classList.contains('tab')) return;
                        if (el.type === 'button' && /print|export|tab/i.test(el.textContent||'')) return;
                        // don't disable pure navigation
                        if (el.tagName === 'BUTTON' && el.closest('.actions-bar')) return;
                        if (el.matches('input,select,textarea')) el.disabled = true;
                    });
                }
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        var PNL_KEY = 'pbj_admin_pnl_v1';
        var ORDER_KEY = 'pbj_admin_auto_orders_v1';
        var period = '7d';
        /** Last rendered snapshot for CSV / print meta */
        var lastSnapshot = null;
        var pnlState = loadPnl();
        var otherDraft = [];
        var expenseUiBuilt = false;

        var EXPENSE_GROUPS = [
            {
                id: 'occupancy',
                title: isSweet ? 'Occupancy' : 'Occupancy',
                items: [
                    { id: 'rent', label: isSweet ? 'Rent / lease' : 'Rent / lease' },
                    { id: 'property_tax', label: isSweet ? 'Property taxes' : 'Property taxes' },
                    { id: 'building_insurance', label: isSweet ? 'Building / property insurance' : 'Building insurance' },
                    { id: 'cam', label: isSweet ? 'CAM / common area' : 'CAM / common area' },
                    { id: 'security', label: isSweet ? 'Security / alarm' : 'Security / alarm' }
                ]
            },
            {
                id: 'utilities',
                title: isSweet ? 'Utilities' : 'Utilities',
                items: [
                    { id: 'electric', label: isSweet ? 'Electric' : 'Electric' },
                    { id: 'gas', label: isSweet ? 'Gas' : 'Gas' },
                    { id: 'water', label: isSweet ? 'Water / sewer' : 'Water / sewer' },
                    { id: 'trash', label: isSweet ? 'Trash / recycling' : 'Trash / recycling' },
                    { id: 'internet', label: isSweet ? 'Internet / phone' : 'Internet / phone' }
                ]
            },
            {
                id: 'marketing',
                title: isSweet ? 'Sales & marketing' : 'Sales & marketing',
                items: [
                    { id: 'marketing', label: isSweet ? 'Marketing / advertising' : 'Marketing / advertising' },
                    { id: 'loyalty', label: isSweet ? 'Loyalty / promotions' : 'Loyalty / promotions' },
                    { id: 'online_fees', label: isSweet ? 'Website / online ordering fees' : 'Website / online fees' }
                ]
            },
            {
                id: 'admin',
                title: isSweet ? 'Admin & professional' : 'Admin & professional',
                items: [
                    { id: 'accounting', label: isSweet ? 'Accounting / bookkeeping' : 'Accounting / bookkeeping' },
                    { id: 'legal', label: isSweet ? 'Legal' : 'Legal' },
                    { id: 'licenses', label: isSweet ? 'Licenses & permits' : 'Licenses & permits' },
                    { id: 'cc_fees', label: isSweet ? 'Merchant / credit card fees' : 'Merchant / card fees' },
                    { id: 'bank_fees', label: isSweet ? 'Bank fees' : 'Bank fees' },
                    { id: 'software', label: isSweet ? 'Software / SaaS (POS, apps…)' : 'Software / SaaS' },
                    { id: 'office', label: isSweet ? 'Office supplies' : 'Office supplies' }
                ]
            },
            {
                id: 'ops',
                title: isSweet ? 'Ops & maintenance' : 'Ops & maintenance',
                items: [
                    { id: 'repairs', label: isSweet ? 'Repairs & maintenance' : 'Repairs & maintenance' },
                    { id: 'equip_lease', label: isSweet ? 'Equipment lease / rental' : 'Equipment lease' },
                    { id: 'smallwares', label: isSweet ? 'Smallwares' : 'Smallwares' },
                    { id: 'pest', label: isSweet ? 'Pest control' : 'Pest control' },
                    { id: 'linen', label: isSweet ? 'Linen / laundry' : 'Linen / laundry' },
                    { id: 'uniforms', label: isSweet ? 'Uniforms' : 'Uniforms' },
                    { id: 'training', label: isSweet ? 'Training / recruiting' : 'Training / recruiting' },
                    { id: 'travel', label: isSweet ? 'Travel / mileage' : 'Travel / mileage' }
                ]
            },
            {
                id: 'people',
                title: isSweet ? 'People (non-wage)' : 'People (non-wage)',
                items: [
                    { id: 'benefits', label: isSweet ? 'Benefits / workers\' comp' : 'Benefits / workers\' comp' },
                    { id: 'payroll_tax', label: isSweet ? 'Payroll taxes / burden' : 'Payroll taxes / burden' }
                ]
            },
            {
                id: 'other',
                title: isSweet ? 'Other' : 'Other',
                items: [
                    { id: 'misc', label: isSweet ? 'Miscellaneous' : 'Miscellaneous' }
                ]
            }
        ];

        var FOOD_CATS = { dairy: 1, meats: 1, frozen: 1, canned_dry: 1, produce: 1, dispenser_beverage: 1 };
        var PAPER_CATS = { paper_disposable: 1 };
        var CHEM_CATS = { chemical_janitorial: 1 };
        var SUPPLY_CATS = { supplies_equipment: 1 };

        function money(n) {
            if (n == null || isNaN(n)) return '—';
            var neg = n < 0;
            var a = Math.abs(Math.round(n * 100) / 100);
            return (neg ? '-$' : '$') + a.toFixed(2);
        }
        function num(v) {
            if (v === '' || v == null) return null;
            var n = parseFloat(v);
            return isNaN(n) ? null : n;
        }
        function pct(n) {
            if (n == null || isNaN(n)) return '—';
            return (Math.round(n * 10) / 10).toFixed(1) + '%';
        }
        function loadJson(key, fallback) {
            try { var r = JSON.parse(localStorage.getItem(key) || 'null'); return r || fallback; } catch (e) { return fallback; }
        }
        function loadPnl() {
            var s = loadJson(PNL_KEY, null);
            if (!s || typeof s !== 'object') s = { settings: { foodCostPct: 30, compsReduce: false, wasteReduce: false }, expenses: {}, periods: {} };
            if (!s.settings) s.settings = { foodCostPct: 30, compsReduce: false, wasteReduce: false };
            if (s.settings.foodCostPct == null) s.settings.foodCostPct = 30;
            if (s.settings.wasteReduce == null) s.settings.wasteReduce = false;
            if (!s.expenses || typeof s.expenses !== 'object') s.expenses = {};
            if (!s.periods) s.periods = {};
            return s;
        }
        function savePnl() {
            localStorage.setItem(PNL_KEY, JSON.stringify(pnlState));
        }
        function toast(msg) {
            var el = document.getElementById('toast');
            if (msg) el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1600);
        }
        function dateStr(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function parseDate(s) {
            return new Date(s + 'T12:00:00');
        }
        function uid() {
            return Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
        }
        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function hoursBetween(start, end, breakMins) {
            if (!start || !end) return 0;
            var a = start.split(':').map(Number), b = end.split(':').map(Number);
            var mins = (b[0] * 60 + b[1]) - (a[0] * 60 + a[1]);
            if (mins < 0) mins += 24 * 60;
            mins -= (parseInt(breakMins, 10) || 0);
            if (mins < 0) mins = 0;
            return Math.round((mins / 60) * 100) / 100;
        }

        function datesForPeriod(mode) {
            var out = [];
            var today = new Date();
            today.setHours(12, 0, 0, 0);
            if (mode === 'week') {
                var day = today.getDay();
                var monOffset = day === 0 ? 6 : day - 1;
                var start = new Date(today);
                start.setDate(today.getDate() - monOffset);
                for (var d = new Date(start); d <= today; d.setDate(d.getDate() + 1)) out.push(dateStr(d));
                return out;
            }
            if (mode === 'mtd') {
                var startM = new Date(today.getFullYear(), today.getMonth(), 1, 12, 0, 0, 0);
                for (var dm = new Date(startM); dm <= today; dm.setDate(dm.getDate() + 1)) out.push(dateStr(dm));
                return out;
            }
            var n = mode === '28d' ? 28 : 7;
            for (var i = n - 1; i >= 0; i--) {
                var x = new Date(today);
                x.setDate(today.getDate() - i);
                out.push(dateStr(x));
            }
            return out;
        }
        function periodKey(dates) {
            if (!dates.length) return 'empty';
            return dates[0] + '_' + dates[dates.length - 1];
        }
        function periodLabel(dates) {
            if (!dates.length) return '—';
            var a = parseDate(dates[0]).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            var b = parseDate(dates[dates.length - 1]).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            return a + ' → ' + b + ' · ' + dates.length + (isSweet ? ' days' : ' days');
        }

        // --- Source data (reload after POS sync) ---
        var sales = { days: [] };
        var labor = { days: [] };
        var comps = { entries: [] };
        var cash = { days: [] };
        var recipeState = { recipes: [] };
        var ingState = { items: {} };
        var menuState = null;
        var ordersState = { orders: [] };
        var salesByDate = {};
        var laborByDate = {};

        function reloadSources() {
            sales = loadJson('pbj_admin_sales_v2', null);
            if (!sales || !Array.isArray(sales.days)) sales = loadJson('pbj_admin_sales_v1', { days: [] });
            if (!Array.isArray(sales.days)) sales.days = [];
            labor = loadJson('pbj_admin_labor_v2', null);
            if (!labor || !Array.isArray(labor.days)) labor = loadJson('pbj_admin_labor_v1', { days: [] });
            if (!Array.isArray(labor.days)) labor.days = [];
            comps = loadJson('pbj_admin_comps_v1', { entries: [] });
            if (!Array.isArray(comps.entries)) comps.entries = [];
            cash = loadJson('pbj_admin_cash_v1', { days: [] });
            if (!Array.isArray(cash.days)) cash.days = [];
            recipeState = loadJson('pbj_heat_recipes_v1', { recipes: [] });
            ingState = loadJson('pbj_heat_ingredients_v1', { items: {} });
            menuState = loadJson('pbj_menu_v1', null);
            ordersState = loadJson(ORDER_KEY, { orders: [] });
            if (!Array.isArray(ordersState.orders)) ordersState.orders = [];
            salesByDate = {};
            (sales.days || []).forEach(function (d) { if (d && d.date) salesByDate[d.date] = d; });
            laborByDate = {};
            (labor.days || []).forEach(function (d) { if (d && d.date) laborByDate[d.date] = d; });
        }
        reloadSources();

        /** Average recipe food-cost % from Costing Sheet data (menu price + plate cost). */
        function avgTheoreticalFoodCostPct() {
            var recipes = (recipeState && Array.isArray(recipeState.recipes)) ? recipeState.recipes : [];
            var items = (ingState && ingState.items) ? ingState.items : {};
            var menuItems = [];
            if (menuState && Array.isArray(menuState.items)) menuItems = menuState.items;
            else if (menuState && Array.isArray(menuState.menu)) menuItems = menuState.menu;
            function norm(s) { return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim(); }
            function casePriceOf(item) {
                if (!item) return null;
                if (item.casePrice !== undefined && item.casePrice !== null && item.casePrice !== '') return parseFloat(item.casePrice);
                return null;
            }
            function plateCost(recipe) {
                var ings = recipe.ingredients || recipe.ings || [];
                var total = 0, any = false;
                ings.forEach(function (ing) {
                    var key = norm(ing.name || ing.ingredient);
                    var item = items[key];
                    if (!item) {
                        Object.keys(items).forEach(function (k) {
                            if (norm(items[k].name) === key) item = items[k];
                        });
                    }
                    var qty = parseFloat(ing.qty);
                    if (isNaN(qty) || !item) return;
                    var caseP = casePriceOf(item);
                    var packs = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
                    var perPack = parseFloat(item.recipeUnitsPerPack);
                    var cpu = null;
                    if (caseP != null && !isNaN(caseP) && !isNaN(perPack) && perPack > 0) {
                        if (isNaN(packs) || packs <= 0) packs = 1;
                        cpu = caseP / (packs * perPack);
                    } else if (item.directRecipeUnitCost != null && item.directRecipeUnitCost !== '') {
                        cpu = parseFloat(item.directRecipeUnitCost);
                    }
                    if (cpu == null || isNaN(cpu)) return;
                    var y = parseFloat(item.usableYieldPct);
                    if (!isNaN(y) && y > 0 && y < 100) cpu = cpu / (y / 100);
                    total += qty * cpu;
                    any = true;
                });
                return any ? total : null;
            }
            function menuPriceFor(recipe) {
                var title = norm(recipe.title || recipe.name);
                for (var i = 0; i < menuItems.length; i++) {
                    var m = menuItems[i];
                    if (norm(m.name || m.title) === title || (m.recipeId && m.recipeId === recipe.id)) {
                        var p = parseFloat(m.price);
                        return isNaN(p) ? null : p;
                    }
                }
                var rp = parseFloat(recipe.menuPrice || recipe.price);
                return isNaN(rp) ? null : rp;
            }
            var sum = 0, n = 0;
            recipes.forEach(function (r) {
                var cost = plateCost(r);
                var price = menuPriceFor(r);
                if (cost != null && price != null && price > 0) {
                    sum += (cost / price) * 100;
                    n++;
                }
            });
            return n ? (sum / n) : null;
        }
        function cashOverShortForDates(dates) {
            var set = {};
            dates.forEach(function (d) { set[d] = true; });
            var total = 0, n = 0;
            (cash.days || []).forEach(function (row) {
                if (!row || !set[row.date]) return;
                var exp = parseFloat(row.expected);
                var cnt = parseFloat(row.counted);
                if (isNaN(exp) || isNaN(cnt)) return;
                total += (cnt - exp);
                n++;
            });
            return n ? Math.round(total * 100) / 100 : null;
        }
        function netOf(d) {
            if (!d) return null;
            var n = num(d.net);
            if (n != null) return n;
            return num(d.gross);
        }
        function entryHours(e) {
            if (!e) return 0;
            return hoursBetween(
                e.actualStart || e.clockIn || e.schedStart || e.start || '',
                e.actualEnd || e.clockOut || e.schedEnd || e.end || '',
                e.breakMins
            );
        }
        function laborHoursOf(d) {
            if (!d) return null;
            if (Array.isArray(d.entries) && d.entries.length) {
                var h = 0;
                d.entries.forEach(function (e) { h += entryHours(e); });
                return Math.round(h * 100) / 100;
            }
            return num(d.hours);
        }
        function laborWagesFromEntries(d) {
            if (!d || !Array.isArray(d.entries) || !d.entries.length) return null;
            var total = 0, any = false;
            d.entries.forEach(function (e) {
                var lc = num(e.laborCost);
                if (lc != null) {
                    any = true;
                    total += lc;
                    return;
                }
                var rate = num(e.wageRate);
                if (rate == null) return;
                any = true;
                total += rate * entryHours(e);
            });
            return any ? Math.round(total * 100) / 100 : null;
        }
        /** Returns { amount, source } */
        function laborCostDetail(date) {
            var l = laborByDate[date];
            if (l) {
                var isPos = l.costSource === 'pos' || l.source === 'pos';
                var dayCost = num(l.cost) != null ? num(l.cost) : num(l.labor);
                if (dayCost != null) return { amount: dayCost, source: isPos ? 'pos' : 'snapshot' };
                var wages = laborWagesFromEntries(l);
                if (wages != null) return { amount: wages, source: isPos ? 'pos' : 'wages' };
            }
            var s = salesByDate[date];
            if (s && num(s.labor) != null) {
                return { amount: num(s.labor), source: (s.source === 'pos') ? 'pos' : 'sales' };
            }
            return { amount: null, source: null };
        }

        function lineEstCost(l) {
            if (!l) return null;
            var q = parseFloat(l.qty);
            if (isNaN(q)) return null;
            var c;
            if ((l.orderBy === 'case' || l.parBy === 'case') && l.casePrice !== '' && l.casePrice != null) {
                c = parseFloat(l.casePrice);
            } else {
                c = parseFloat(l.costPerUnit);
            }
            if (isNaN(c)) return null;
            return Math.round(c * q * 100) / 100;
        }
        function normalizeCat(cat) {
            return String(cat || '').toLowerCase().replace(/\s+/g, '_');
        }
        function bucketForCat(cat) {
            cat = normalizeCat(cat);
            if (FOOD_CATS[cat]) return 'food';
            if (PAPER_CATS[cat]) return 'paper';
            if (CHEM_CATS[cat]) return 'chem';
            if (SUPPLY_CATS[cat]) return 'supply';
            return 'other_purch';
        }
        /** Sum Auto-Order purchases in date window */
        function purchasesForDates(dates) {
            var set = {};
            dates.forEach(function (d) { set[d] = true; });
            var totals = { food: 0, paper: 0, chem: 0, supply: 0, other_purch: 0, orderCount: 0, lineCount: 0, hasAny: false, byDate: {} };
            ordersState.orders.forEach(function (o) {
                if (!o) return;
                var od = o.businessDate || (o.at ? dateStr(new Date(o.at)) : null);
                if (!od || !set[od]) return;
                totals.orderCount++;
                if (!totals.byDate[od]) totals.byDate[od] = { food: 0, paper: 0, chem: 0, supply: 0, other_purch: 0 };
                (o.lines || []).forEach(function (line) {
                    var cost = lineEstCost(line);
                    if (cost == null) return;
                    var b = bucketForCat(line.category);
                    totals[b] += cost;
                    totals.byDate[od][b] += cost;
                    totals.lineCount++;
                    totals.hasAny = true;
                });
            });
            return totals;
        }

        function compsForDates(dates) {
            var set = {};
            dates.forEach(function (d) { set[d] = true; });
            var total = 0, byDate = {};
            (comps.entries || []).forEach(function (e) {
                if (!e || !e.date || !set[e.date]) return;
                var a = num(e.amount) || 0;
                total += a;
                byDate[e.date] = (byDate[e.date] || 0) + a;
            });
            return { total: total, byDate: byDate };
        }

        function getPeriodSettings(key) {
            var p = pnlState.periods[key] || {};
            return {
                foodActual: p.foodActual != null ? num(p.foodActual) : null,
                other: Array.isArray(p.other) ? p.other.slice() : [],
                expenseOverrides: p.expenseOverrides && typeof p.expenseOverrides === 'object' ? p.expenseOverrides : {}
            };
        }

        function allExpenseDefs() {
            var list = [];
            EXPENSE_GROUPS.forEach(function (g) {
                g.items.forEach(function (it) { list.push(it); });
            });
            return list;
        }

        function buildExpenseChart() {
            var root = document.getElementById('expense-chart');
            var html = '';
            EXPENSE_GROUPS.forEach(function (g) {
                html += '<div class="exp-group" data-group="' + esc(g.id) + '"><h3>' + esc(g.title) + '</h3>';
                g.items.forEach(function (it) {
                    var saved = pnlState.expenses[it.id] || {};
                    var monthly = saved.monthly != null ? saved.monthly : '';
                    html += '<div class="exp-row" data-exp-id="' + esc(it.id) + '">' +
                        '<div class="exp-label">' + esc(it.label) + '</div>' +
                        '<input type="number" class="exp-monthly" min="0" step="0.01" placeholder="0" value="' + esc(monthly === '' ? '' : String(monthly)) + '" data-id="' + esc(it.id) + '">' +
                        '<input type="number" class="exp-period" min="0" step="0.01" placeholder="auto" value="" data-id="' + esc(it.id) + '">' +
                        '</div>';
                });
                html += '</div>';
            });
            root.innerHTML = html;
            expenseUiBuilt = true;
            root.querySelectorAll('.exp-monthly, .exp-period').forEach(function (inp) {
                inp.addEventListener('input', function () {
                    clearTimeout(window.__pnlOpexT);
                    window.__pnlOpexT = setTimeout(render, 180);
                });
            });
        }

        function seedPeriodOverrides(key, dates) {
            var ps = getPeriodSettings(key);
            allExpenseDefs().forEach(function (it) {
                var input = document.querySelector('.exp-period[data-id="' + it.id + '"]');
                if (!input) return;
                var ov = ps.expenseOverrides[it.id];
                input.value = ov != null && ov !== '' ? ov : '';
            });
        }

        function collectExpensesFromDom(dayCount) {
            var lines = [];
            var total = 0;
            allExpenseDefs().forEach(function (it) {
                var mEl = document.querySelector('.exp-monthly[data-id="' + it.id + '"]');
                var pEl = document.querySelector('.exp-period[data-id="' + it.id + '"]');
                var monthly = mEl ? num(mEl.value) : null;
                var periodOv = pEl ? num(pEl.value) : null;
                var amount = null;
                var how = null;
                if (periodOv != null) {
                    amount = periodOv;
                    how = 'period';
                } else if (monthly != null && monthly > 0) {
                    amount = Math.round(monthly * (dayCount / 30) * 100) / 100;
                    how = 'prorate';
                }
                if (amount != null && amount !== 0) {
                    lines.push({ id: it.id, label: it.label, amount: amount, monthly: monthly, how: how });
                    total += amount;
                }
            });
            return { lines: lines, total: Math.round(total * 100) / 100 };
        }

        function renderOtherList() {
            var root = document.getElementById('other-list');
            if (!otherDraft.length) {
                root.innerHTML = '<div class="empty" style="padding:10px;">' +
                    (isSweet ? 'No one-off lines — use the chart above for rent, marketing, etc.' : 'No custom period lines.') +
                    '</div>';
                return;
            }
            root.innerHTML = otherDraft.map(function (row, idx) {
                return '<div class="other-row" data-idx="' + idx + '">' +
                    '<input type="text" class="other-label" placeholder="' + (isSweet ? 'Label' : 'Label') + '" value="' + esc(row.label || '') + '">' +
                    '<input type="number" class="other-amt" min="0" step="0.01" placeholder="$" value="' + (row.amount != null && row.amount !== '' ? esc(String(row.amount)) : '') + '">' +
                    '<button type="button" class="btn btn-danger btn-small other-del">×</button>' +
                    '</div>';
            }).join('');
            root.querySelectorAll('.other-del').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var row = btn.closest('.other-row');
                    otherDraft.splice(parseInt(row.getAttribute('data-idx'), 10), 1);
                    renderOtherList();
                    render();
                });
            });
            root.querySelectorAll('input').forEach(function (inp) {
                inp.addEventListener('input', function () {
                    clearTimeout(window.__pnlOtherT);
                    window.__pnlOtherT = setTimeout(render, 200);
                });
            });
        }

        function collectOtherFromDom() {
            var rows = document.querySelectorAll('#other-list .other-row');
            var out = [];
            rows.forEach(function (row) {
                var label = (row.querySelector('.other-label').value || '').trim();
                var amount = num(row.querySelector('.other-amt').value);
                if (!label && amount == null) return;
                var idx = parseInt(row.getAttribute('data-idx'), 10);
                out.push({
                    id: (otherDraft[idx] && otherDraft[idx].id) || uid(),
                    label: label || (isSweet ? 'Expense' : 'Expense'),
                    amount: amount != null ? amount : 0
                });
            });
            return out;
        }

        function render() {
            if (!expenseUiBuilt) buildExpenseChart();

            var dates = datesForPeriod(period);
            var key = periodKey(dates);
            var dayCount = dates.length || 1;
            document.getElementById('period-label').textContent = periodLabel(dates);

            var ps = getPeriodSettings(key);
            var foodPct = num(document.getElementById('f-food-pct').value);
            if (foodPct == null) foodPct = num(pnlState.settings.foodCostPct);
            if (foodPct == null) foodPct = 30;

            var foodActual = num(document.getElementById('f-food-actual').value);
            if (document.getElementById('f-food-actual').dataset.seeded !== key) {
                document.getElementById('f-food-pct').value = foodPct;
                document.getElementById('f-food-actual').value = ps.foodActual != null ? ps.foodActual : '';
                document.getElementById('f-comps-reduce').checked = !!pnlState.settings.compsReduce;
                var wasteCb = document.getElementById('f-waste-reduce');
                if (wasteCb) wasteCb.checked = !!pnlState.settings.wasteReduce;
                otherDraft = (ps.other || []).map(function (o) {
                    return { id: o.id || uid(), label: o.label || '', amount: o.amount };
                });
                seedPeriodOverrides(key, dates);
                document.getElementById('f-food-actual').dataset.seeded = key;
                foodActual = ps.foodActual;
                renderOtherList();
            } else {
                otherDraft = collectOtherFromDom();
            }

            var compsReduce = document.getElementById('f-comps-reduce').checked;
            var wasteReduce = !!(document.getElementById('f-waste-reduce') && document.getElementById('f-waste-reduce').checked);
            var compData = compsForDates(dates);
            var purch = purchasesForDates(dates);
            var opex = collectExpensesFromDom(dayCount);
            var wasteData = { total: 0, count: 0 };
            if (window.PbjFoodCost && window.PbjFoodCost.wasteTotalForDates) {
                wasteData = window.PbjFoodCost.wasteTotalForDates(dates);
            } else {
                try {
                    var wRaw = JSON.parse(localStorage.getItem('pbj_admin_waste_v1') || 'null');
                    var wSet = {};
                    dates.forEach(function (d) { wSet[d] = true; });
                    var wTot = 0, wN = 0;
                    ((wRaw && wRaw.entries) || []).forEach(function (e) {
                        if (!e || !wSet[e.date]) return;
                        var c = parseFloat(e.cost);
                        if (isNaN(c)) return;
                        wTot += c; wN++;
                    });
                    wasteData = { total: Math.round(wTot * 100) / 100, count: wN };
                } catch (eW) {}
            }
            var waste$ = wasteData.count ? wasteData.total : (wasteData.total > 0 ? wasteData.total : null);
            document.getElementById('opex-live').textContent = opex.total ? money(opex.total) : '—';

            var totalSales = 0, salesN = 0;
            var totalLabor = 0, laborN = 0;
            var totalHours = 0, hoursN = 0;
            var laborSources = {};
            var salesSources = { pos: 0, manual: 0, csv: 0 };
            var covers = 0;
            var missingSales = 0;
            var missingLabor = 0;
            var posProviders = {};

            var dayData = dates.map(function (date) {
                var s = salesByDate[date];
                var l = laborByDate[date];
                var n = netOf(s);
                var lab = laborCostDetail(date);
                var labH = laborHoursOf(l);
                var cov = s ? num(s.covers) : null;

                if (n != null) {
                    totalSales += n;
                    salesN++;
                    if (s.source === 'pos') {
                        salesSources.pos++;
                        if (s.posProvider) posProviders[s.posProvider] = (posProviders[s.posProvider] || 0) + 1;
                    } else if (s.source === 'csv' || s.posProvider) {
                        salesSources.csv++;
                        if (s.posProvider) posProviders[s.posProvider] = (posProviders[s.posProvider] || 0) + 1;
                    } else {
                        salesSources.manual++;
                    }
                } else {
                    missingSales++;
                }
                if (lab.amount != null) {
                    totalLabor += lab.amount;
                    laborN++;
                    laborSources[lab.source || 'snapshot'] = (laborSources[lab.source || 'snapshot'] || 0) + 1;
                    if (l && l.posProvider) posProviders[l.posProvider] = (posProviders[l.posProvider] || 0) + 1;
                } else {
                    missingLabor++;
                }
                if (labH != null) { totalHours += labH; hoursN++; }
                if (cov != null) covers += cov;

                return {
                    date: date,
                    net: n,
                    labor$: lab.amount,
                    laborSrc: lab.source,
                    salesSrc: s ? (s.source || 'manual') : null,
                    posProvider: (s && s.posProvider) || (l && l.posProvider) || '',
                    hours: labH,
                    covers: cov,
                    comps: compData.byDate[date] || null,
                    foodOrders: purch.byDate[date] ? purch.byDate[date].food : null
                };
            });

            // Coverage strip
            var covEl = document.getElementById('data-coverage');
            if (covEl) {
                function covPill(have, total, label) {
                    var cls = have === 0 ? 'miss' : (have < total ? 'warn' : 'ok');
                    return '<span class="cov-pill ' + cls + '">' + have + '/' + total + ' ' + label + '</span>';
                }
                var provBits = Object.keys(posProviders);
                var provTxt = provBits.length
                    ? (isSweet ? ' · POS: ' : ' · POS: ') + provBits.map(function (p) {
                        return p.charAt(0).toUpperCase() + p.slice(1);
                    }).join(', ')
                    : '';
                var salesMix = '';
                if (salesSources.pos || salesSources.csv) {
                    salesMix = isSweet
                        ? (' · ' + salesSources.pos + ' live POS' + (salesSources.csv ? ' · ' + salesSources.csv + ' CSV' : '') + (salesSources.manual ? ' · ' + salesSources.manual + ' manual' : ''))
                        : (' · pos ' + salesSources.pos + ' / csv ' + salesSources.csv + ' / man ' + salesSources.manual);
                }
                // Cash closes in period (days with sales that also closed cash)
                var cashClosed = 0;
                var cashNeeded = 0;
                dates.forEach(function (dt) {
                    var s = salesByDate[dt];
                    var n = netOf(s);
                    if (n == null) return;
                    cashNeeded++;
                    var cRow = null;
                    (cash.days || []).forEach(function (cd) { if (cd && cd.date === dt) cRow = cd; });
                    var exp = cRow ? parseFloat(cRow.expected) : NaN;
                    var cnt = cRow ? parseFloat(cRow.counted) : NaN;
                    if (!isNaN(exp) && !isNaN(cnt)) cashClosed++;
                });
                covEl.innerHTML =
                    covPill(salesN, dayCount, isSweet ? 'sales' : 'sales') +
                    covPill(laborN, dayCount, isSweet ? 'labor' : 'labor') +
                    (cashNeeded
                        ? covPill(cashClosed, cashNeeded, isSweet ? 'cash close' : 'cash')
                        : '') +
                    (purch.orderCount ? covPill(purch.orderCount, purch.orderCount, isSweet ? 'orders' : 'orders') : '') +
                    '<span style="opacity:0.85;">' + salesMix + provTxt +
                    (missingSales && salesN
                        ? (isSweet ? ' · ' + missingSales + ' day(s) missing sales' : ' · ' + missingSales + ' missing sales')
                        : '') +
                    (cashNeeded && cashClosed < cashNeeded
                        ? (isSweet ? ' · <a href="/admin/cash">finish cash closes</a>' : ' · <a href="/admin/cash">cash gaps</a>')
                        : '') +
                    '</span>';
            }

            // Food priority: actual override → orders food → % estimate
            var foodSource = 'none';
            var food$ = null;
            if (foodActual != null) {
                food$ = foodActual;
                foodSource = 'actual';
            } else if (purch.hasAny && purch.food > 0) {
                food$ = Math.round(purch.food * 100) / 100;
                foodSource = 'orders';
            } else if (salesN) {
                food$ = Math.round(totalSales * (foodPct / 100) * 100) / 100;
                foodSource = 'estimate';
            }

            var paper$ = purch.paper > 0 ? Math.round(purch.paper * 100) / 100 : null;
            var chem$ = purch.chem > 0 ? Math.round(purch.chem * 100) / 100 : null;
            var supply$ = purch.supply > 0 ? Math.round(purch.supply * 100) / 100 : null;
            var otherPurch$ = purch.other_purch > 0 ? Math.round(purch.other_purch * 100) / 100 : null;

            var customOther = 0;
            otherDraft.forEach(function (o) { customOther += (num(o.amount) || 0); });

            var purchaseExtras = (paper$ || 0) + (chem$ || 0) + (supply$ || 0) + (otherPurch$ || 0);
            var opexTotal = opex.total + customOther;
            var compsLine = compData.total;
            var compsSubtract = compsReduce ? compsLine : 0;
            var wasteSubtract = (wasteReduce && waste$ != null) ? waste$ : 0;

            var prime = null;
            if (food$ != null || laborN) {
                prime = (food$ != null ? food$ : 0) + (laborN ? totalLabor : 0);
            }

            var contribution = null;
            if (salesN) {
                contribution = totalSales
                    - (food$ != null ? food$ : 0)
                    - (laborN ? totalLabor : 0)
                    - purchaseExtras
                    - opexTotal
                    - compsSubtract
                    - wasteSubtract;
            }

            var laborPct = (salesN && totalSales > 0 && laborN) ? (totalLabor / totalSales * 100) : null;
            var foodPctActual = (salesN && totalSales > 0 && food$ != null) ? (food$ / totalSales * 100) : null;
            var primePct = (salesN && totalSales > 0 && prime != null) ? (prime / totalSales * 100) : null;
            var contribPct = (salesN && totalSales > 0 && contribution != null) ? (contribution / totalSales * 100) : null;

            document.getElementById('stat-sales').textContent = salesN ? money(totalSales) : '—';
            var primeEl = document.getElementById('stat-prime');
            primeEl.textContent = pct(primePct);
            primeEl.className = 'num' + (primePct != null && primePct > 65 ? ' warn' : (primePct != null ? ' ok' : ''));
            var cEl = document.getElementById('stat-contrib');
            cEl.textContent = contribution != null ? money(contribution) : '—';
            cEl.className = 'num' + (contribution != null && contribution < 0 ? ' warn' : (contribution != null ? ' ok' : ''));
            var lpEl = document.getElementById('stat-labor-pct');
            lpEl.textContent = pct(laborPct);
            lpEl.className = 'num' + (laborPct != null && laborPct > 35 ? ' warn' : (laborPct != null ? ' ok' : ''));
            document.getElementById('stat-food-pct').textContent = pct(foodPctActual);
            document.getElementById('stat-opex').textContent = opexTotal ? money(opexTotal) : '—';

            // Snapshot for export
            lastSnapshot = {
                period: period,
                periodLabel: periodLabel(dates),
                start: dates[0] || '',
                end: dates[dates.length - 1] || '',
                dayCount: dayCount,
                totalSales: salesN ? totalSales : null,
                totalLabor: laborN ? totalLabor : null,
                totalHours: hoursN ? totalHours : null,
                laborPct: laborPct,
                food$: food$,
                foodSource: foodSource,
                foodPctActual: foodPctActual,
                foodPctFallback: foodPct,
                paper$: paper$,
                chem$: chem$,
                supply$: supply$,
                otherPurch$: otherPurch$,
                prime: prime,
                primePct: primePct,
                opexTotal: opexTotal || null,
                opexLines: (opex.lines || []).map(function (l) {
                    return { label: l.label, amount: l.amount, how: l.how, monthly: l.monthly };
                }),
                customLines: otherDraft.map(function (o) {
                    return { label: o.label || '', amount: num(o.amount) || 0 };
                }),
                comps: compsLine || null,
                compsReduce: compsReduce,
                waste$: waste$,
                wasteCount: wasteData.count || 0,
                wasteReduce: wasteReduce,
                contribution: contribution,
                contribPct: contribPct,
                theoPct: null,
                theo$: null,
                theoVar: null,
                theoSource: null,
                cashOS: null,
                dayData: dayData,
                salesN: salesN,
                laborN: laborN
            };

            // Cash over/short from Cash & Deposits
            var cashOS = cashOverShortForDates(dates);
            var cashEl = document.getElementById('stat-cash-os');
            if (cashEl) {
                if (cashOS == null) {
                    cashEl.textContent = '—';
                    cashEl.className = 'num';
                } else {
                    cashEl.textContent = money(cashOS) + (cashOS > 0 ? (isSweet ? ' over' : ' over') : (cashOS < 0 ? (isSweet ? ' short' : ' short') : ''));
                    cashEl.className = 'num' + (cashOS < 0 ? ' warn' : (cashOS > 0 ? ' ok' : ''));
                }
            }
            // Theoretical food cost: prefer PMIX ideal (qty × plate) when a session overlaps; else avg plate FC% × sales
            var theoPct = null;
            var theo$ = null;
            var theoSource = null;
            var theoLabel = '';
            var rangeStart = dates[0] || '';
            var rangeEnd = dates[dates.length - 1] || '';
            var pmixIdeal = null;
            if (window.PbjFoodCost && window.PbjFoodCost.idealFoodCostForRange && rangeStart) {
                pmixIdeal = window.PbjFoodCost.idealFoodCostForRange(rangeStart, rangeEnd);
            }
            if (pmixIdeal && pmixIdeal.matched > 0 && pmixIdeal.foodCost != null) {
                theo$ = pmixIdeal.foodCost;
                theoPct = pmixIdeal.idealPct;
                if (theoPct == null && salesN && totalSales > 0) {
                    theoPct = Math.round((theo$ / totalSales) * 1000) / 10;
                }
                theoSource = 'pmix';
                theoLabel = pmixIdeal.sessionLabel || 'PMIX';
            } else {
                theoPct = avgTheoreticalFoodCostPct();
                theo$ = (theoPct != null && salesN && totalSales > 0) ? (totalSales * theoPct / 100) : null;
                theoSource = theo$ != null ? 'avg_plate' : null;
            }
            var theoEl = document.getElementById('stat-theo-fc');
            if (theoEl) {
                theoEl.textContent = theo$ != null ? money(theo$) : '—';
                if (theoSource === 'pmix') {
                    theoEl.title = (isSweet ? 'Ideal from PMIX “' : 'PMIX ideal “') + theoLabel + '”' +
                        (theoPct != null ? (' · ' + theoPct + '%') : '');
                } else {
                    theoEl.title = theoPct != null
                        ? ((isSweet ? 'Avg plate FC ' : 'Avg plate FC ') + (Math.round(theoPct * 10) / 10) + '% × net sales')
                        : (isSweet ? 'Need PMIX or recipe costs + menu prices' : 'Need PMIX or recipe costs');
                }
            }
            var varEl = document.getElementById('stat-theo-var');
            var theoVarAmt = null;
            if (varEl) {
                if (theo$ != null && food$ != null) {
                    theoVarAmt = Math.round((food$ - theo$) * 100) / 100;
                    varEl.textContent = money(theoVarAmt) + (theoVarAmt > 0 ? (isSweet ? ' over theo' : ' over theo') : (theoVarAmt < 0 ? (isSweet ? ' under theo' : ' under theo') : ''));
                    varEl.className = 'num' + (theoVarAmt > 0 ? ' warn' : (theoVarAmt < 0 ? ' ok' : ''));
                } else {
                    varEl.textContent = '—';
                    varEl.className = 'num';
                }
            }
            var wasteStat = document.getElementById('stat-waste');
            if (wasteStat) {
                wasteStat.textContent = waste$ != null ? money(waste$) : '—';
                wasteStat.title = wasteData.count
                    ? (wasteData.count + (isSweet ? ' waste entries in period' : ' entries'))
                    : (isSweet ? 'Log waste on Waste log (optional)' : 'No waste logged');
            }
            if (lastSnapshot) {
                lastSnapshot.theoPct = theoPct;
                lastSnapshot.theo$ = theo$ != null ? Math.round(theo$ * 100) / 100 : null;
                lastSnapshot.theoVar = theoVarAmt;
                lastSnapshot.theoSource = theoSource;
                lastSnapshot.theoLabel = theoLabel;
                lastSnapshot.cashOS = cashOS;
                lastSnapshot.waste$ = waste$;
                lastSnapshot.wasteCount = wasteData.count || 0;
                lastSnapshot.wasteReduce = wasteReduce;
            }
            var printMeta = document.getElementById('print-meta');
            if (printMeta) {
                printMeta.textContent = (isSweet ? 'Period: ' : 'Period: ') + periodLabel(dates) +
                    ' · Generated ' + new Date().toLocaleString() + ' · ilovepbj ops';
            }

            // Orders hint
            var oh = document.getElementById('orders-hint');
            if (purch.orderCount) {
                oh.innerHTML = (isSweet ? '🛒 ' : '') + purch.orderCount + (isSweet ? ' saved Auto-Order(s) in this window · ' : ' saved order(s) in window · ') +
                    purch.lineCount + (isSweet ? ' priced lines · food ' : ' priced lines · food ') + money(purch.food) +
                    (isSweet ? ' · <a href="/admin/auto-order">Manage orders</a>' : ' · <a href="/admin/auto-order">Auto-Order</a>');
            } else {
                oh.innerHTML = (isSweet
                    ? 'No saved Auto-Orders in this window yet — food uses % or override. Build & save orders in <a href="/admin/auto-order">Auto-Order</a>.'
                    : 'No Auto-Orders in range. Save orders under Auto-Order, or set % / actual food $.');
            }
            var wh = document.getElementById('waste-hint');
            if (wh) {
                if (waste$ != null && wasteData.count) {
                    wh.innerHTML = (isSweet ? '🗑️ ' : '') + wasteData.count + (isSweet
                        ? ' waste entries · ' + money(waste$) + ' · <a href="/admin/waste">Waste log</a> (memo unless you tick subtract)'
                        : ' waste entries · ' + money(waste$) + ' · <a href="/admin/waste">Waste log</a>');
                } else {
                    wh.innerHTML = isSweet
                        ? 'No waste logged in this window — optional on <a href="/admin/waste">Waste log</a>. Theo FC prefers <a href="/admin/pmix">PMIX</a> when available.'
                        : 'Optional: log waste · PMIX improves theoretical food $.';
                }
            }

            var laborSourceLabel = '';
            if (laborSources.pos) laborSourceLabel = isSweet ? 'from POS labor sync' : 'from POS labor';
            else if (laborSources.wages) laborSourceLabel = isSweet ? 'from wages on Labor Snapshot' : 'from entry wages';
            else if (laborSources.snapshot) laborSourceLabel = isSweet ? 'from Labor Snapshot day totals' : 'from labor day totals';
            else if (laborSources.sales) laborSourceLabel = isSweet ? 'from Daily Sales labor $' : 'from sales labor field';

            var salesSourceLabel = '';
            if (salesSources.pos && !salesSources.manual && !salesSources.csv) {
                salesSourceLabel = isSweet ? 'From POS sync' : 'From POS';
            } else if (salesSources.pos || salesSources.csv) {
                salesSourceLabel = isSweet ? 'POS + manual mix' : 'Mixed sources';
            } else if (salesN) {
                salesSourceLabel = isSweet ? 'Manual / logged sales' : 'Manual sales';
            }

            // Statement
            var wrap = document.getElementById('statement-wrap');
            var hasAnything = salesN || laborN || purch.hasAny || opexTotal || compsLine || (waste$ != null && waste$ > 0) || theo$ != null;
            if (!hasAnything) {
                wrap.innerHTML = '<div class="empty">' + (isSweet
                    ? 'Nothing in this window yet — <strong>Sync POS</strong> above, log sales/labor, place Auto-Orders, or fill rent & opex 📈'
                    : 'No data in this period yet. Sync POS or log sales/labor.') +
                    '<div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;justify-content:center;">' +
                    '<a class="btn btn-secondary btn-small" href="/admin/pos-connect">' + (isSweet ? 'POS connect' : 'POS') + '</a>' +
                    '<a class="btn btn-secondary btn-small" href="/admin/sales">' + (isSweet ? 'Sales' : 'Sales') + '</a>' +
                    '<a class="btn btn-secondary btn-small" href="/admin/labor">' + (isSweet ? 'Labor' : 'Labor') + '</a>' +
                    '<a class="btn btn-secondary btn-small" href="/admin/auto-order">' + (isSweet ? 'Auto-Order' : 'Auto-Order') + '</a>' +
                    '</div></div>';
            } else {
                function row(label, amount, ofSales, cls, sub) {
                    return '<tr class="' + (cls || '') + '"><td>' + label +
                        (sub ? '<span class="sub">' + sub + '</span>' : '') +
                        '</td><td class="amt">' + money(amount) + '</td><td class="pct">' +
                        (ofSales != null ? pct(ofSales) : '') + '</td></tr>';
                }
                function ofS(amount) {
                    return (salesN && totalSales > 0 && amount != null) ? (amount / totalSales * 100) : null;
                }
                var foodBadge = foodSource === 'actual'
                    ? '<span class="badge actual">' + (isSweet ? 'actual' : 'actual') + '</span>'
                    : foodSource === 'orders'
                        ? '<span class="badge orders">' + (isSweet ? 'from orders' : 'from orders') + '</span>'
                        : foodSource === 'estimate'
                            ? '<span class="badge">' + (isSweet ? 'est. ' + pct(foodPct) : 'est. ' + pct(foodPct)) + '</span>'
                            : '';
                var foodSub = foodSource === 'orders'
                    ? (isSweet ? 'Food categories on Auto-Orders in this window' : 'Food categories on saved orders')
                    : foodSource === 'actual'
                        ? (isSweet ? 'Invoice override for this period' : 'Manual override')
                        : foodSource === 'estimate'
                            ? (isSweet ? 'Net sales × ' + pct(foodPct) + ' — no orders priced yet' : 'Net × estimate %')
                            : '';

                var salesBadge = salesSources.pos
                    ? '<span class="badge pos">' + (isSweet ? 'POS' : 'POS') + '</span>'
                    : (salesN ? '<span class="badge">' + (isSweet ? 'logged' : 'logged') + '</span>' : '');
                var laborBadge = laborSources.pos
                    ? '<span class="badge pos">' + (isSweet ? 'POS' : 'POS') + '</span>'
                    : (laborN ? '<span class="badge wages">' + (isSweet ? 'pulled' : 'pulled') + '</span>' : '');

                var html = '<table class="pnl-table"><thead><tr><th></th><th class="amt">Amount</th><th class="pct">% sales</th></tr></thead><tbody>';
                html += '<tr class="section"><td colspan="3">' + (isSweet ? 'Revenue' : 'Revenue') + '</td></tr>';
                html += row(
                    (isSweet ? 'Net sales' : 'Net sales') + salesBadge,
                    salesN ? totalSales : null,
                    salesN ? 100 : null,
                    '',
                    salesN
                        ? (salesN + '/' + dayCount + (isSweet ? ' days with sales' : ' days') +
                            (salesSourceLabel ? ' · ' + salesSourceLabel : '') +
                            (missingSales ? (isSweet ? ' · ' + missingSales + ' gap(s)' : ' · ' + missingSales + ' gaps') : ''))
                        : (isSweet ? 'Sync POS or log Daily Sales' : 'Sync POS or log sales')
                );
                html += row(
                    (isSweet ? 'Comps / voids / discounts' : 'Comps / voids') +
                    (!compsReduce ? '<span class="badge">' + (isSweet ? 'memo' : 'memo') + '</span>' : ''),
                    compsLine || null, ofS(compsLine || null),
                    compsReduce ? '' : 'memo',
                    compsReduce ? (isSweet ? 'Subtracted from contribution' : 'Subtracted') : (isSweet ? 'Control line — not subtracted by default' : 'Control only')
                );

                html += '<tr class="section"><td colspan="3">' + (isSweet ? 'Cost of sales / purchases' : 'Cost of sales / purchases') + '</td></tr>';
                html += row((isSweet ? 'Food / COGS' : 'Food / COGS') + foodBadge, food$, foodPctActual, '', foodSub);
                if (paper$ != null) html += row(isSweet ? 'Paper & disposables' : 'Paper & disposables', paper$, ofS(paper$), '', isSweet ? 'From Auto-Orders' : 'From orders');
                if (chem$ != null) html += row(isSweet ? 'Chemicals / janitorial' : 'Chemicals / janitorial', chem$, ofS(chem$), '', isSweet ? 'From Auto-Orders' : 'From orders');
                if (supply$ != null) html += row(isSweet ? 'Supplies / equipment' : 'Supplies / equipment', supply$, ofS(supply$), '', isSweet ? 'From Auto-Orders' : 'From orders');
                if (otherPurch$ != null) html += row(isSweet ? 'Other purchases' : 'Other purchases', otherPurch$, ofS(otherPurch$), '', isSweet ? 'Uncategorized order lines' : 'Other order lines');
                // Waste: memo by default (like comps); optional subtract
                if (waste$ != null || (wasteData && wasteData.count)) {
                    html += row(
                        (isSweet ? 'Waste (logged)' : 'Waste (logged)') +
                        (!wasteReduce ? '<span class="badge">' + (isSweet ? 'memo' : 'memo') + '</span>' : '<span class="badge actual">' + (isSweet ? 'in contrib' : 'in result') + '</span>'),
                        waste$ != null ? waste$ : 0,
                        ofS(waste$ != null ? waste$ : 0),
                        wasteReduce ? '' : 'memo',
                        wasteReduce
                            ? (isSweet ? (wasteData.count || 0) + ' entries · subtracted from contribution' : (wasteData.count || 0) + ' entries · subtracted')
                            : (isSweet ? (wasteData.count || 0) + ' entries · control only · <a href="/admin/waste">Waste log</a>' : (wasteData.count || 0) + ' entries · control only')
                    );
                }
                if (theo$ != null) {
                    html += row(
                        (isSweet ? 'Theoretical food cost' : 'Theoretical food cost') +
                        '<span class="badge">' + (theoSource === 'pmix' ? (isSweet ? 'PMIX ideal' : 'PMIX') : (isSweet ? 'avg plate' : 'avg plate')) + '</span>',
                        theo$,
                        theoPct,
                        'memo',
                        theoSource === 'pmix'
                            ? (isSweet ? 'Qty sold × plate cost · “' + esc(theoLabel) + '”' : 'PMIX × plate · ' + esc(theoLabel))
                            : (isSweet ? 'Avg plate FC% × net sales (pull PMIX for mix-based ideal)' : 'Avg plate FC% × sales')
                    );
                }
                html += row(
                    (isSweet ? 'Labor' : 'Labor') + laborBadge,
                    laborN ? totalLabor : null, laborPct, '',
                    (hoursN ? ((Math.round(totalHours * 10) / 10) + 'h') : '') +
                    (laborSourceLabel ? (hoursN ? ' · ' : '') + laborSourceLabel : '') +
                    (missingLabor && laborN ? (isSweet ? ' · ' + missingLabor + ' day(s) without labor' : ' · ' + missingLabor + ' gaps') : '') ||
                    (isSweet ? 'From Labor Snapshot / POS' : 'From labor log / POS')
                );
                html += row(
                    isSweet ? 'Prime cost (food + labor)' : 'Prime cost (food + labor)',
                    prime, primePct, 'subtotal',
                    primePct != null ? (primePct > 65
                        ? (isSweet ? 'Elevated — many shops aim under ~60–65%' : 'Elevated vs ~60–65%')
                        : (isSweet ? 'Food + labor only (purchases above still count below)' : 'Food + labor only')) : ''
                );

                html += '<tr class="section"><td colspan="3">' + (isSweet ? 'Operating expenses' : 'Operating expenses') + '</td></tr>';
                if (opex.lines.length || otherDraft.length) {
                    opex.lines.forEach(function (line) {
                        html += row(
                            esc(line.label),
                            line.amount,
                            ofS(line.amount),
                            '',
                            line.how === 'prorate'
                                ? (isSweet ? 'Monthly ' + money(line.monthly) + ' × ' + dayCount + '/30' : 'Monthly prorated')
                                : (isSweet ? 'Period override' : 'Period override')
                        );
                    });
                    otherDraft.forEach(function (o) {
                        var a = num(o.amount) || 0;
                        if (!a && !(o.label || '').trim()) return;
                        html += row(esc(o.label || (isSweet ? 'Custom' : 'Custom')), a, ofS(a), '', isSweet ? 'One-off this period' : 'One-off');
                    });
                    html += row(isSweet ? 'Total operating expenses' : 'Total operating expenses', opexTotal || null, ofS(opexTotal || null), 'subtotal', '');
                } else {
                    html += row(isSweet ? 'Operating expenses' : 'Operating expenses', null, null, '', isSweet ? 'Fill monthly chart below (rent, utilities, marketing…)' : 'Set monthly opex below');
                }

                html += row(
                    isSweet ? 'Contribution / operating result' : 'Contribution / operating result',
                    contribution, contribPct, 'total',
                    isSweet
                        ? ('Net − food − purchases − labor − opex' + (compsReduce ? ' − comps' : '') + (wasteReduce ? ' − waste' : ''))
                        : ('Net − purchases − labor − opex' + (compsReduce ? ' − comps' : '') + (wasteReduce ? ' − waste' : ''))
                );
                html += '</tbody></table>';
                wrap.innerHTML = html;
            }

            // Day table
            var tWrap = document.getElementById('table-wrap');
            if (!salesN && !laborN && !purch.hasAny) {
                tWrap.innerHTML = '<div class="empty">' + (isSweet ? 'Day rows appear once you log sales, labor, or orders 📅' : 'No day data.') + '</div>';
            } else {
                tWrap.innerHTML = '<table class="table"><thead><tr>' +
                    '<th>Day</th><th class="num">Net</th><th class="num">Food</th><th class="num">Labor</th><th class="num">Prime %</th><th class="num">Contrib*</th>' +
                    '</tr></thead><tbody>' +
                    dayData.map(function (d) {
                        var label = parseDate(d.date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                        var dayFood = null;
                        if (foodSource === 'actual' && salesN && totalSales > 0 && d.net != null) {
                            dayFood = foodActual * (d.net / totalSales);
                        } else if (foodSource === 'orders') {
                            dayFood = d.foodOrders != null ? d.foodOrders : (d.net != null ? 0 : null);
                        } else if (foodSource === 'estimate' && d.net != null) {
                            dayFood = d.net * (foodPct / 100);
                        }
                        var dayPrime = null;
                        if (dayFood != null || d.labor$ != null) dayPrime = (dayFood || 0) + (d.labor$ || 0);
                        var dayPrimePct = (d.net != null && d.net > 0 && dayPrime != null) ? (dayPrime / d.net * 100) : null;
                        var dayContrib = null;
                        if (d.net != null) {
                            dayContrib = d.net - (dayFood || 0) - (d.labor$ || 0);
                        }
                        var pCls = dayPrimePct != null && dayPrimePct > 65 ? 'warn' : (dayPrimePct != null ? 'ok' : '');
                        var srcTag = '';
                        if (d.salesSrc === 'pos' || d.laborSrc === 'pos') {
                            srcTag = ' <span class="badge pos">' + (d.posProvider || 'POS') + '</span>';
                        } else if (d.net == null && d.labor$ == null) {
                            srcTag = ' <span class="badge">' + (isSweet ? 'gap' : 'gap') + '</span>';
                        }
                        return '<tr><td>' + label + srcTag + '</td>' +
                            '<td class="num">' + money(d.net) + '</td>' +
                            '<td class="num">' + money(dayFood) + '</td>' +
                            '<td class="num">' + money(d.labor$) + '</td>' +
                            '<td class="num ' + pCls + '">' + pct(dayPrimePct) + '</td>' +
                            '<td class="num">' + money(dayContrib) + '</td></tr>';
                    }).join('') +
                    '</tbody></table>' +
                    '<p class="hint" style="margin-top:8px;">' + (isSweet
                        ? '* Day contribution is sales − day food − labor only (opex is period-level). POS badge = synced day.'
                        : '* Day contribution excludes period opex. POS badge = synced day.') + '</p>';
            }
        }

        // Events
        document.querySelectorAll('.period-chip').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.period-chip').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                period = btn.getAttribute('data-period') || '7d';
                document.getElementById('f-food-actual').dataset.seeded = '';
                render();
            });
        });

        document.getElementById('btn-add-other').addEventListener('click', function () {
            otherDraft = collectOtherFromDom();
            otherDraft.push({ id: uid(), label: '', amount: '' });
            renderOtherList();
        });

        document.getElementById('btn-save-pnl').addEventListener('click', function () {
            var dates = datesForPeriod(period);
            var key = periodKey(dates);
            var foodPct = num(document.getElementById('f-food-pct').value);
            if (foodPct == null) foodPct = 30;
            pnlState.settings.foodCostPct = foodPct;
            pnlState.settings.compsReduce = !!document.getElementById('f-comps-reduce').checked;
            pnlState.settings.wasteReduce = !!(document.getElementById('f-waste-reduce') && document.getElementById('f-waste-reduce').checked);

            // Save monthly chart
            if (!pnlState.expenses) pnlState.expenses = {};
            allExpenseDefs().forEach(function (it) {
                var mEl = document.querySelector('.exp-monthly[data-id="' + it.id + '"]');
                var monthly = mEl ? num(mEl.value) : null;
                if (!pnlState.expenses[it.id]) pnlState.expenses[it.id] = { label: it.label };
                pnlState.expenses[it.id].label = it.label;
                pnlState.expenses[it.id].monthly = monthly;
            });

            var overrides = {};
            allExpenseDefs().forEach(function (it) {
                var pEl = document.querySelector('.exp-period[data-id="' + it.id + '"]');
                var v = pEl ? num(pEl.value) : null;
                if (v != null) overrides[it.id] = v;
            });

            otherDraft = collectOtherFromDom();
            pnlState.periods[key] = {
                foodActual: num(document.getElementById('f-food-actual').value),
                other: otherDraft,
                expenseOverrides: overrides
            };
            savePnl();
            toast(isSweet ? 'P&L setup saved 💾' : 'Saved');
            document.getElementById('f-food-actual').dataset.seeded = key;
            render();
        });

        ['f-food-pct', 'f-food-actual', 'f-comps-reduce', 'f-waste-reduce'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('change', render);
            if (id !== 'f-comps-reduce' && id !== 'f-waste-reduce') {
                el.addEventListener('input', function () {
                    clearTimeout(window.__pnlT);
                    window.__pnlT = setTimeout(render, 200);
                });
            }
        });

        document.getElementById('btn-print').addEventListener('click', function () { window.print(); });

        function csvEscape(v) {
            if (v == null || v === '') return '';
            var s = String(v);
            if (/[",\n\r]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
            return s;
        }
        function csvMoney(n) {
            if (n == null || isNaN(n)) return '';
            return (Math.round(n * 100) / 100).toFixed(2);
        }
        document.getElementById('btn-export-csv').addEventListener('click', function () {
            if (!lastSnapshot) {
                toast(isSweet ? 'Nothing to export yet' : 'Nothing to export');
                return;
            }
            var s = lastSnapshot;
            var lines = [];
            lines.push(['Section', 'Line', 'Amount', 'Pct_of_sales', 'Notes'].join(','));
            function add(sec, line, amt, p, notes) {
                lines.push([csvEscape(sec), csvEscape(line), csvMoney(amt), p != null ? (Math.round(p * 10) / 10).toFixed(1) : '', csvEscape(notes || '')].join(','));
            }
            add('Meta', 'Period', '', '', s.periodLabel);
            add('Meta', 'Start', '', '', s.start);
            add('Meta', 'End', '', '', s.end);
            add('Revenue', 'Net sales', s.totalSales, s.totalSales != null ? 100 : null, s.salesN + ' days');
            add('Revenue', 'Comps (memo)', s.comps, null, s.compsReduce ? 'subtracted' : 'control only');
            add('COGS', 'Food / COGS', s.food$, s.foodPctActual, s.foodSource);
            if (s.paper$ != null) add('COGS', 'Paper & disposables', s.paper$, null, 'orders');
            if (s.chem$ != null) add('COGS', 'Chemicals', s.chem$, null, 'orders');
            if (s.supply$ != null) add('COGS', 'Supplies', s.supply$, null, 'orders');
            if (s.otherPurch$ != null) add('COGS', 'Other purchases', s.otherPurch$, null, 'orders');
            if (s.waste$ != null) add('COGS', 'Waste (logged)', s.waste$, null, s.wasteReduce ? 'subtracted' : 'memo');
            add('Labor', 'Labor $', s.totalLabor, s.laborPct, s.totalHours != null ? (s.totalHours + 'h') : '');
            add('Prime', 'Prime cost (food + labor)', s.prime, s.primePct, '');
            (s.opexLines || []).forEach(function (l) {
                add('Opex', l.label, l.amount, null, l.how === 'prorate' ? 'monthly prorate' : 'period override');
            });
            (s.customLines || []).forEach(function (l) {
                if (l.amount || l.label) add('Opex', l.label || 'Custom', l.amount, null, 'one-off');
            });
            add('Opex', 'Total operating expenses', s.opexTotal, s.contribPct != null && s.totalSales ? null : null, '');
            add('Result', 'Contribution / operating result', s.contribution, s.contribPct, '');
            if (s.theo$ != null) add('Food analysis', 'Theoretical food $', s.theo$, s.theoPct, s.theoSource === 'pmix' ? ('PMIX ideal · ' + (s.theoLabel || '')) : 'avg plate FC% × sales');
            if (s.theoVar != null) add('Food analysis', 'Theo vs actual variance', s.theoVar, null, s.theoVar > 0 ? 'over theo' : 'under theo');
            if (s.cashOS != null) add('Cash', 'Cash over/short (period)', s.cashOS, null, '');
            lines.push('');
            lines.push(['Date', 'Net', 'Food', 'Labor', 'Prime_pct', 'Day_contrib'].join(','));
            (s.dayData || []).forEach(function (d) {
                var dayFood = null;
                if (s.foodSource === 'actual' && s.totalSales > 0 && d.net != null) dayFood = s.food$ * (d.net / s.totalSales);
                else if (s.foodSource === 'orders') dayFood = d.foodOrders != null ? d.foodOrders : (d.net != null ? 0 : null);
                else if (s.foodSource === 'estimate' && d.net != null) dayFood = d.net * (s.foodPctFallback / 100);
                var dayPrime = null;
                if (dayFood != null || d.labor$ != null) dayPrime = (dayFood || 0) + (d.labor$ || 0);
                var dayPrimePct = (d.net != null && d.net > 0 && dayPrime != null) ? (dayPrime / d.net * 100) : null;
                var dayContrib = d.net != null ? (d.net - (dayFood || 0) - (d.labor$ || 0)) : null;
                lines.push([
                    d.date,
                    csvMoney(d.net),
                    csvMoney(dayFood),
                    csvMoney(d.labor$),
                    dayPrimePct != null ? (Math.round(dayPrimePct * 10) / 10).toFixed(1) : '',
                    csvMoney(dayContrib)
                ].join(','));
            });
            var blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8' });
            var a = document.createElement('a');
            var fname = 'pbj-pnl-' + (s.start || 'export') + '-to-' + (s.end || 'export') + '.csv';
            a.href = URL.createObjectURL(blob);
            a.download = fname;
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
                URL.revokeObjectURL(a.href);
                a.remove();
            }, 500);
            toast(isSweet ? 'CSV downloaded ✨' : 'CSV downloaded');
        });

        var btnSyncPos = document.getElementById('btn-sync-pos');
        if (btnSyncPos) {
            btnSyncPos.addEventListener('click', function () {
                if (!window.PbjPosSync) {
                    toast(isSweet ? 'Open POS Connections to connect first' : 'POS client missing');
                    return;
                }
                btnSyncPos.disabled = true;
                var prev = btnSyncPos.textContent;
                btnSyncPos.textContent = isSweet ? 'Syncing…' : 'Syncing…';
                window.PbjPosSync.syncAll(28).then(function (res) {
                    btnSyncPos.disabled = false;
                    btnSyncPos.textContent = prev;
                    reloadSources();
                    render();
                    if (!res || !res.ok) {
                        toast((res && (res.hint || res.error)) || (isSweet ? 'Sync failed — connect POS' : 'Sync failed'));
                        return;
                    }
                    var pmixN = (res.pmixCount != null ? res.pmixCount : (res.merged && res.merged.itemCount)) || 0;
                    // applySyncResult already stored PMIX when present
                    toast(isSweet
                        ? ('POS refreshed · ' + (res.count || 0) + ' sales · ' + (res.laborCount || 0) + ' labor' + (pmixN ? ' · ' + pmixN + ' PMIX items' : '') + ' ✨')
                        : ('Synced ' + (res.count || 0) + ' sales / ' + (res.laborCount || 0) + ' labor' + (pmixN ? ' / ' + pmixN + ' PMIX' : '')));
                }).catch(function () {
                    btnSyncPos.disabled = false;
                    btnSyncPos.textContent = prev;
                    toast(isSweet ? 'Network error' : 'Network error');
                });
            });
        }
        document.addEventListener('pbj-pos-synced', function () {
            reloadSources();
            render();
        });
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                reloadSources();
                render();
            }
        });

        // Init monthly values from saved state into chart
        document.getElementById('f-food-pct').value = pnlState.settings.foodCostPct != null ? pnlState.settings.foodCostPct : 30;
        document.getElementById('f-comps-reduce').checked = !!pnlState.settings.compsReduce;
        var wasteReduceEl = document.getElementById('f-waste-reduce');
        if (wasteReduceEl) wasteReduceEl.checked = !!pnlState.settings.wasteReduce;
        buildExpenseChart();
        // Apply saved monthly values (buildExpenseChart already reads pnlState.expenses)
        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyRepPerms);
            document.addEventListener('pbj-perms-ready', applyRepPerms);
    })();
    </script>
</body>
</html>
