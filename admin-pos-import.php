<?php
/**
 * POS CSV bridge — import daily sales (Toast / Square / Clover style exports).
 * Merges into pbj_admin_sales_v2 local store (client-side) with source=pos.
 */
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
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
    <title><?php echo $is_sweet ? 'POS CSV Import' : 'POS CSV Import'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; }
        .subtitle { margin: 10px 0 0; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 820px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.3rem; margin: 0 0 10px; }
        .hint { font-size: 0.92rem; opacity: 0.75; line-height: 1.45; margin: 0 0 12px; }
        .hint a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field select, .field textarea, .field input {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;
            <?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?>
        }
        .field textarea { min-height: 140px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 0.88rem; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 140px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .btn-row .btn { flex: 1; min-width: 120px; }
        .preview { overflow-x: auto; max-height: 360px; overflow-y: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th, td { padding: 7px 6px; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; text-align: left; vertical-align: top; }
        th { opacity: 0.65; font-weight: normal; font-size: 0.78rem; }
        td.num { text-align: right; white-space: nowrap; }
        .ok { color: #1F6B4A; }
        .warn { color: #C62828; }
        .pill { display: inline-block; font-size: 0.72rem; border-radius: 999px; padding: 2px 8px; <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; max-width: 90vw; text-align: center; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .map-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        @media (max-width: 560px) { .map-grid { grid-template-columns: 1fr; } }
        .status { font-size: 0.9rem; opacity: 0.8; min-height: 1.3em; margin: 0 0 10px; }
        .status.err { color: #B71C1C; opacity: 1; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/sales" class="back-link">← <?php echo $is_sweet ? 'Back to Daily Sales' : 'Back to Daily Sales'; ?></a>
        <h1><?php echo $is_sweet ? 'POS CSV Import' : 'POS CSV Import'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Presets for Toast · Square · Clover · Aloha · Micros' : 'Brand presets + any day-level close report'; ?></p>
    </div>
    <div class="content">
        <div class="card">
            <h2><?php echo $is_sweet ? 'How it works' : 'How it works'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? 'Pick your POS brand for smarter column matching + a sample export. Paste or upload a day-level close report. Days merge into <a href="/admin/sales">Daily Sales</a> with a <strong>From POS</strong> badge — same store as <a href="/admin/pos-connect">live Square / Clover / Toast</a>. Aloha &amp; Micros use this CSV path (no public OAuth yet) 💕'
                : 'Choose a brand preset for column aliases + sample CSV. Live API for Square/Clover/Toast; Aloha/Micros via CSV.'; ?></p>
            <div class="field-row">
                <div class="field">
                    <label><?php echo $is_sweet ? 'POS brand / preset' : 'POS brand / preset'; ?></label>
                    <select id="pos-provider">
                        <option value="toast">Toast</option>
                        <option value="square">Square</option>
                        <option value="clover">Clover</option>
                        <option value="aloha">Aloha (NCR)</option>
                        <option value="micros">Micros / Simphony (Oracle)</option>
                        <option value="spoton">SpotOn</option>
                        <option value="other"><?php echo $is_sweet ? 'Other / generic' : 'Other / generic'; ?></option>
                    </select>
                </div>
                <div class="field">
                    <label><?php echo $is_sweet ? 'On duplicate date' : 'On duplicate date'; ?></label>
                    <select id="dup-mode">
                        <option value="replace"><?php echo $is_sweet ? 'Replace existing day' : 'Replace existing day'; ?></option>
                        <option value="skip"><?php echo $is_sweet ? 'Skip existing days' : 'Skip existing days'; ?></option>
                    </select>
                </div>
            </div>
            <p class="hint" id="preset-hint" style="margin-top:4px;"></p>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? '1. Load CSV' : '1. Load CSV'; ?></h2>
            <div class="btn-row" style="margin-bottom:12px;">
                <label class="btn btn-primary" for="file-csv" style="cursor:pointer;"><?php echo $is_sweet ? '📁 Choose file' : 'Choose file'; ?></label>
                <button type="button" class="btn btn-ghost" id="btn-sample"><?php echo $is_sweet ? 'Insert brand sample' : 'Insert brand sample'; ?></button>
            </div>
            <input type="file" id="file-csv" accept=".csv,text/csv,text/plain" style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;">
            <div class="field">
                <label><?php echo $is_sweet ? 'Or paste CSV text' : 'Or paste CSV text'; ?></label>
                <textarea id="csv-text" placeholder="date,net_sales,gross_sales,covers,..."></textarea>
            </div>
            <button type="button" class="btn btn-primary" id="btn-parse" style="width:100%;"><?php echo $is_sweet ? 'Parse & preview ✨' : 'Parse & preview'; ?></button>
            <p class="status" id="status"></p>
        </div>

        <div class="card" id="map-card" style="display:none;">
            <h2><?php echo $is_sweet ? '2. Column map' : '2. Column map'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Auto-matched where possible — tweak if a column landed wrong.' : 'Adjust auto-detected column mapping.'; ?></p>
            <div class="map-grid" id="map-grid"></div>
            <button type="button" class="btn btn-secondary" id="btn-remap" style="width:100%;margin-top:10px;"><?php echo $is_sweet ? 'Re-preview with map' : 'Re-preview'; ?></button>
        </div>

        <div class="card" id="preview-card" style="display:none;">
            <h2><?php echo $is_sweet ? '3. Preview' : '3. Preview'; ?></h2>
            <p class="hint" id="preview-meta"></p>
            <div class="preview" id="preview"></div>
            <div class="btn-row" style="margin-top:14px;">
                <button type="button" class="btn btn-primary" id="btn-import"><?php echo $is_sweet ? 'Import into Daily Sales ✨' : 'Import into Daily Sales'; ?></button>
                <a href="/admin/sales" class="btn btn-secondary"><?php echo $is_sweet ? 'Open Daily Sales' : 'Open Daily Sales'; ?></a>
                <a href="/admin/pos-connect" class="btn btn-ghost"><?php echo $is_sweet ? '🔗 Live POS connect' : 'Live POS connect'; ?></a>
            </div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Tips by brand' : 'Tips by brand'; ?></h2>
            <p class="hint" style="margin:0;"><?php echo $is_sweet
                ? '• <strong>Square / Clover / Toast</strong> — prefer <a href="/admin/pos-connect">live connect</a>; CSV is backup.<br>• <strong>Aloha</strong> — sales summary / “Sales by Day” from Aloha Insight or Back Office.<br>• <strong>Micros / Simphony</strong> — daily sales or tender report from Reporting / mymicros; map Net / Gross / Covers.<br>• Always export <em>one row per business day</em> (not item-level).<br>• Labor $ columns land on the day log for P&amp;L.'
                : 'Prefer live connect for Square/Clover/Toast. Aloha/Micros: day-level sales exports. Map date + net/gross at minimum.'; ?></p>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var KEY = 'pbj_admin_sales_v2';
        var OLD_KEY = 'pbj_admin_sales_v1';
        var SHARED_KEY = 'admin_sales_v1';
        var headers = [];
        var rows = [];
        var mapped = [];
        var map = {};

        /** Base aliases + brand-specific headers from common day exports */
        var BASE_ALIASES = {
            date: ['date', 'business date', 'business_date', 'day', 'sales date', 'order date', 'report date', 'businessday', 'biz date', 'trading date', 'calendar date'],
            gross: ['gross', 'gross sales', 'gross_sales', 'total sales', 'sales total', 'gross revenue', 'net sales + tax', 'gross receipts'],
            net: ['net', 'net sales', 'net_sales', 'sales', 'revenue', 'total net', 'sales net', 'net revenue', 'net sales amount'],
            tax: ['tax', 'tax collected', 'sales tax', 'taxes', 'vat'],
            tips: ['tips', 'tip', 'gratuity', 'gratuities', 'tip amount'],
            covers: ['covers', 'guests', 'guest count', 'guest_count', 'customers', 'pax', 'headcount', 'guest cnt'],
            checks: ['checks', 'tickets', 'orders', 'check count', 'ticket count', 'transactions', 'check cnt'],
            tenderCash: ['cash', 'cash sales', 'tender cash', 'cash total', 'cash tender'],
            tenderCard: ['card', 'credit', 'credit card', 'card sales', 'visa', 'payments card', 'card total', 'cc sales', 'credit tender'],
            tenderOther: ['other', 'other tender', 'gift card', 'third party', 'online', 'other total', 'gift'],
            labor: ['labor', 'labor $', 'labor cost', 'wages', 'payroll', 'labor total', 'labor dollars']
        };
        var BRAND_ALIASES = {
            toast: {
                date: ['business date', 'dining date', 'order date'],
                net: ['net sales', 'void net sales', 'sales amount'],
                gross: ['gross sales', 'total amount'],
                covers: ['guest count', 'number of guests'],
                checks: ['check count', 'number of checks', 'order count'],
                tips: ['tip total', 'tips'],
                labor: ['labor cost', 'total labor']
            },
            square: {
                date: ['date', 'timezone', 'payment date'],
                net: ['net sales', 'net total', 'collected'],
                gross: ['gross sales', 'total collected', 'amount'],
                tax: ['tax', 'taxes'],
                tips: ['tips', 'tip'],
                tenderCard: ['card', 'card payments'],
                tenderCash: ['cash', 'cash payments'],
                checks: ['transactions', 'payment count']
            },
            clover: {
                date: ['date', 'order date', 'business date'],
                net: ['net sales', 'amount', 'net amount'],
                gross: ['gross sales', 'total sales', 'gross amount'],
                tax: ['tax amount', 'tax'],
                tips: ['tip amount', 'tips'],
                tenderCash: ['cash', 'cash payments'],
                tenderCard: ['credit card', 'card', 'card payments'],
                checks: ['orders', 'payments', 'transactions']
            },
            aloha: {
                date: ['business date', 'date', 'system date', 'doy'],
                net: ['net sales', 'netsales', 'net sls', 'sales net'],
                gross: ['gross sales', 'grosssales', 'gross sls', 'total sales'],
                tax: ['tax', 'sales tax', 'tax amount'],
                tips: ['tips', 'gratuity', 'svc chg'],
                covers: ['covers', 'guest count', 'guests', 'cust count'],
                checks: ['checks', 'check count', 'chk count', 'transactions'],
                tenderCash: ['cash', 'cash sales', 'cash tenders'],
                tenderCard: ['credit', 'credit card', 'cc sales', 'media credit'],
                labor: ['labor', 'labor cost', 'labor $', 'payroll']
            },
            micros: {
                date: ['business date', 'revenue center date', 'rvc date', 'date', 'calendar day'],
                net: ['net sales', 'net sls', 'sales net', 'revenue'],
                gross: ['gross sales', 'gross sls', 'total sales', 'sales total'],
                tax: ['tax', 'tax total', 'sales tax'],
                tips: ['tips', 'service charge', 'svc charge', 'gratuity'],
                covers: ['covers', 'guest count', 'num guests', 'cover count'],
                checks: ['checks', 'check count', 'guest checks', 'num checks'],
                tenderCash: ['cash', 'cash tender', 'tender cash'],
                tenderCard: ['credit card', 'credit', 'cc tender', 'card tender'],
                labor: ['labor', 'labor cost', 'payroll cost', 'employee cost']
            },
            spoton: {
                date: ['business date', 'date', 'day'],
                net: ['net sales', 'sales'],
                gross: ['gross sales', 'total sales'],
                covers: ['guests', 'covers'],
                checks: ['tickets', 'checks'],
                tenderCash: ['cash'],
                tenderCard: ['card', 'credit']
            },
            other: {}
        };
        var BRAND_HINTS = {
            toast: isSweet ? 'Toast preset: expects Business Date, Net Sales, Guest Count style headers. Live API also available under POS Connections.' : 'Toast day export headers. Live connect available.',
            square: isSweet ? 'Square preset: sales summary CSV. Prefer live Square OAuth when secrets are set.' : 'Square CSV or live OAuth.',
            clover: isSweet ? 'Clover preset: payments/sales by day export. Live Clover OAuth available under POS Connections.' : 'Clover CSV or live OAuth.',
            aloha: isSweet ? 'Aloha preset: maps NetSales / GrossSales / Covers / Media columns from Insight or BOH day summaries.' : 'Aloha day summary headers.',
            micros: isSweet ? 'Micros / Simphony preset: Business Date, Net Sls, Gross Sls, Covers, tender columns from Oracle reporting.' : 'Micros/Simphony day report headers.',
            spoton: isSweet ? 'SpotOn day export headers.' : 'SpotOn day export.',
            other: isSweet ? 'Generic auto-detect — tweak the column map if needed.' : 'Generic auto-detect.'
        };
        var BRAND_SAMPLES = {
            toast: function (d) {
                return 'Business Date,Net Sales,Gross Sales,Tax,Tips,Guest Count,Check Count,Cash,Credit Card,Labor Cost\n'
                    + d(2) + ',4820.50,5210.00,389.50,612.00,142,68,890.00,3930.50,1240.00\n'
                    + d(1) + ',5102.25,5510.00,407.75,640.00,151,72,920.00,4182.25,1310.00\n'
                    + d(0) + ',4675.00,5050.00,375.00,590.00,138,65,850.00,3825.00,1195.00\n';
            },
            square: function (d) {
                return 'Date,Gross Sales,Net Sales,Tax,Tips,Transactions,Cash,Card,Other\n'
                    + d(2) + ',5210.00,4820.50,389.50,612.00,68,890.00,3930.50,0.00\n'
                    + d(1) + ',5510.00,5102.25,407.75,640.00,72,920.00,4182.25,0.00\n'
                    + d(0) + ',5050.00,4675.00,375.00,590.00,65,850.00,3825.00,0.00\n';
            },
            clover: function (d) {
                return 'Order Date,Gross Amount,Net Amount,Tax Amount,Tip Amount,Orders,Cash Payments,Card Payments\n'
                    + d(2) + ',5210.00,4820.50,389.50,612.00,68,890.00,3930.50\n'
                    + d(1) + ',5510.00,5102.25,407.75,640.00,72,920.00,4182.25\n'
                    + d(0) + ',5050.00,4675.00,375.00,590.00,65,850.00,3825.00\n';
            },
            aloha: function (d) {
                return 'Business Date,NetSales,GrossSales,Sales Tax,Tips,Guest Count,Chk Count,Cash Tenders,Media Credit,Labor $\n'
                    + d(2) + ',4820.50,5210.00,389.50,612.00,142,68,890.00,3930.50,1240.00\n'
                    + d(1) + ',5102.25,5510.00,407.75,640.00,151,72,920.00,4182.25,1310.00\n'
                    + d(0) + ',4675.00,5050.00,375.00,590.00,138,65,850.00,3825.00,1195.00\n';
            },
            micros: function (d) {
                return 'Business Date,Net Sls,Gross Sls,Tax Total,Service Charge,Cover Count,Check Count,Cash Tender,Credit Card,Labor Cost\n'
                    + d(2) + ',4820.50,5210.00,389.50,612.00,142,68,890.00,3930.50,1240.00\n'
                    + d(1) + ',5102.25,5510.00,407.75,640.00,151,72,920.00,4182.25,1310.00\n'
                    + d(0) + ',4675.00,5050.00,375.00,590.00,138,65,850.00,3825.00,1195.00\n';
            },
            spoton: function (d) {
                return 'Business Date,Net Sales,Gross Sales,Guests,Tickets,Cash,Card,Labor\n'
                    + d(2) + ',4820.50,5210.00,142,68,890.00,3930.50,1240.00\n'
                    + d(1) + ',5102.25,5510.00,151,72,920.00,4182.25,1310.00\n'
                    + d(0) + ',4675.00,5050.00,138,65,850.00,3825.00,1195.00\n';
            },
            other: function (d) {
                return 'Business Date,Net Sales,Gross Sales,Guests,Checks,Cash,Credit,Labor\n'
                    + d(2) + ',4820.50,5210.00,142,68,890.00,3930.50,1240.00\n'
                    + d(1) + ',5102.25,5510.00,151,72,920.00,4182.25,1310.00\n'
                    + d(0) + ',4675.00,5050.00,138,65,850.00,3825.00,1195.00\n';
            }
        };

        function currentProvider() {
            var el = document.getElementById('pos-provider');
            return (el && el.value) || 'other';
        }
        function fieldAliases() {
            var brand = currentProvider();
            var out = {};
            Object.keys(BASE_ALIASES).forEach(function (k) {
                var extra = (BRAND_ALIASES[brand] && BRAND_ALIASES[brand][k]) || [];
                // brand-first so preferred headers win
                out[k] = extra.concat(BASE_ALIASES[k]);
            });
            return out;
        }
        function paintPresetHint() {
            var el = document.getElementById('preset-hint');
            if (!el) return;
            el.innerHTML = BRAND_HINTS[currentProvider()] || BRAND_HINTS.other;
        }

        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 2200);
        }
        function setStatus(msg, err) {
            var el = document.getElementById('status');
            el.textContent = msg || '';
            el.classList.toggle('err', !!err);
        }
        function esc(s) {
            return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function money(n) {
            if (n == null || isNaN(n)) return '—';
            return '$' + (Math.round(n * 100) / 100).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 8); }

        function parseCsv(text) {
            text = String(text || '').replace(/^\uFEFF/, '');
            var out = [];
            var i = 0, field = '', row = [], inQ = false;
            while (i < text.length) {
                var c = text.charAt(i);
                if (inQ) {
                    if (c === '"') {
                        if (text.charAt(i + 1) === '"') { field += '"'; i += 2; continue; }
                        inQ = false; i++; continue;
                    }
                    field += c; i++; continue;
                }
                if (c === '"') { inQ = true; i++; continue; }
                if (c === ',') { row.push(field); field = ''; i++; continue; }
                if (c === '\n' || c === '\r') {
                    if (c === '\r' && text.charAt(i + 1) === '\n') i++;
                    row.push(field); field = '';
                    if (row.some(function (x) { return String(x).trim() !== ''; })) out.push(row);
                    row = []; i++; continue;
                }
                field += c; i++;
            }
            row.push(field);
            if (row.some(function (x) { return String(x).trim() !== ''; })) out.push(row);
            return out;
        }

        function normHead(h) {
            return String(h || '').toLowerCase().replace(/[_./]+/g, ' ').replace(/\s+/g, ' ').trim();
        }
        function autoMap(hdrs) {
            var m = {};
            var FIELD_ALIASES = fieldAliases();
            Object.keys(FIELD_ALIASES).forEach(function (field) {
                m[field] = '';
                var aliases = FIELD_ALIASES[field];
                for (var i = 0; i < hdrs.length; i++) {
                    var h = normHead(hdrs[i]);
                    // normalize glued headers like netsales
                    var hFlat = h.replace(/\s+/g, '');
                    for (var j = 0; j < aliases.length; j++) {
                        var al = aliases[j];
                        var alFlat = al.replace(/\s+/g, '');
                        if (h === al || hFlat === alFlat || h.indexOf(al) === 0 || al.indexOf(h) === 0) {
                            m[field] = hdrs[i];
                            return;
                        }
                    }
                }
                // fuzzy contains
                for (var k = 0; k < hdrs.length; k++) {
                    var hh = normHead(hdrs[k]);
                    var hhFlat = hh.replace(/\s+/g, '');
                    for (var a = 0; a < aliases.length; a++) {
                        var aa = aliases[a];
                        var aaFlat = aa.replace(/\s+/g, '');
                        if (hh.indexOf(aa) >= 0 || hhFlat.indexOf(aaFlat) >= 0) {
                            m[field] = hdrs[k];
                            return;
                        }
                    }
                }
            });
            return m;
        }

        function parseMoney(v) {
            if (v == null || v === '') return null;
            var s = String(v).replace(/[$,\s]/g, '').replace(/^\((.*)\)$/, '-$1');
            var n = parseFloat(s);
            return isNaN(n) ? null : Math.round(n * 100) / 100;
        }
        function parseDate(v) {
            if (v == null || v === '') return '';
            var s = String(v).trim();
            // YYYY-MM-DD
            var m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
            if (m) return m[1] + '-' + String(m[2]).padStart(2, '0') + '-' + String(m[3]).padStart(2, '0');
            // M/D/YYYY or M/D/YY
            m = s.match(/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})/);
            if (m) {
                var y = parseInt(m[3], 10);
                if (y < 100) y += 2000;
                return y + '-' + String(m[1]).padStart(2, '0') + '-' + String(m[2]).padStart(2, '0');
            }
            var d = new Date(s);
            if (!isNaN(d.getTime())) {
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }
            return '';
        }

        function colIndex(name) {
            if (!name) return -1;
            for (var i = 0; i < headers.length; i++) {
                if (headers[i] === name) return i;
            }
            return -1;
        }
        function cell(row, field) {
            var idx = colIndex(map[field]);
            if (idx < 0) return '';
            return row[idx] != null ? row[idx] : '';
        }

        function buildMapped() {
            mapped = [];
            rows.forEach(function (row) {
                var date = parseDate(cell(row, 'date'));
                if (!date) return;
                var gross = parseMoney(cell(row, 'gross'));
                var net = parseMoney(cell(row, 'net'));
                if (net == null && gross != null) net = gross;
                if (gross == null && net != null) gross = net;
                if (net == null && gross == null) return;
                mapped.push({
                    date: date,
                    gross: gross,
                    net: net,
                    tax: parseMoney(cell(row, 'tax')),
                    tips: parseMoney(cell(row, 'tips')),
                    covers: parseMoney(cell(row, 'covers')),
                    checks: parseMoney(cell(row, 'checks')),
                    tenderCash: parseMoney(cell(row, 'tenderCash')),
                    tenderCard: parseMoney(cell(row, 'tenderCard')),
                    tenderOther: parseMoney(cell(row, 'tenderOther')),
                    labor: parseMoney(cell(row, 'labor'))
                });
            });
            // de-dupe same date in file — keep last
            var byDate = {};
            mapped.forEach(function (r) { byDate[r.date] = r; });
            mapped = Object.keys(byDate).sort().map(function (d) { return byDate[d]; });
        }

        function paintMap() {
            var fields = [
                ['date', isSweet ? 'Date *' : 'Date *'],
                ['net', isSweet ? 'Net sales' : 'Net sales'],
                ['gross', isSweet ? 'Gross sales' : 'Gross sales'],
                ['tax', isSweet ? 'Tax' : 'Tax'],
                ['tips', isSweet ? 'Tips' : 'Tips'],
                ['covers', isSweet ? 'Covers / guests' : 'Covers'],
                ['checks', isSweet ? 'Checks / tickets' : 'Checks'],
                ['tenderCash', isSweet ? 'Cash tender' : 'Cash'],
                ['tenderCard', isSweet ? 'Card tender' : 'Card'],
                ['tenderOther', isSweet ? 'Other tender' : 'Other'],
                ['labor', isSweet ? 'Labor $' : 'Labor $']
            ];
            var opts = '<option value="">—</option>' + headers.map(function (h) {
                return '<option value="' + esc(h) + '">' + esc(h) + '</option>';
            }).join('');
            document.getElementById('map-grid').innerHTML = fields.map(function (f) {
                return '<div class="field"><label>' + esc(f[1]) + '</label>' +
                    '<select data-map="' + esc(f[0]) + '">' + opts + '</select></div>';
            }).join('');
            document.querySelectorAll('#map-grid [data-map]').forEach(function (sel) {
                var field = sel.getAttribute('data-map');
                if (map[field]) sel.value = map[field];
                sel.addEventListener('change', function () {
                    map[field] = sel.value;
                });
            });
            document.getElementById('map-card').style.display = 'block';
        }

        function paintPreview() {
            buildMapped();
            var root = document.getElementById('preview');
            var meta = document.getElementById('preview-meta');
            if (!mapped.length) {
                document.getElementById('preview-card').style.display = 'none';
                setStatus(isSweet ? 'No valid day rows — need a date + sales $ column.' : 'No valid rows (need date + sales).', true);
                return;
            }
            setStatus('');
            meta.textContent = isSweet
                ? (mapped.length + ' day(s) ready · provider ' + document.getElementById('pos-provider').value)
                : (mapped.length + ' day(s) · ' + document.getElementById('pos-provider').value);
            root.innerHTML = '<table><thead><tr>' +
                '<th>Date</th><th class="num">Net</th><th class="num">Gross</th><th class="num">Covers</th><th class="num">Labor</th><th>Status</th>' +
                '</tr></thead><tbody>' +
                mapped.map(function (r) {
                    var existing = loadSales().days.some(function (d) { return d.date === r.date; });
                    return '<tr><td>' + esc(r.date) + '</td>' +
                        '<td class="num">' + money(r.net) + '</td>' +
                        '<td class="num">' + money(r.gross) + '</td>' +
                        '<td class="num">' + (r.covers != null ? r.covers : '—') + '</td>' +
                        '<td class="num">' + money(r.labor) + '</td>' +
                        '<td>' + (existing
                            ? '<span class="pill">' + (isSweet ? 'exists' : 'exists') + '</span>'
                            : '<span class="ok">' + (isSweet ? 'new' : 'new') + '</span>') +
                        '</td></tr>';
                }).join('') + '</tbody></table>';
            document.getElementById('preview-card').style.display = 'block';
        }

        function loadSales() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (r && Array.isArray(r.days)) return r;
                var old = JSON.parse(localStorage.getItem(OLD_KEY) || 'null');
                if (old && Array.isArray(old.days)) return { days: old.days, structureAt: Date.now() };
            } catch (e) {}
            return { days: [], structureAt: Date.now() };
        }
        function saveSales(state) {
            state.structureAt = Date.now();
            localStorage.setItem(KEY, JSON.stringify(state));
            try {
                localStorage.setItem(OLD_KEY, JSON.stringify({ days: state.days }));
            } catch (e) {}
            // best-effort kitchen sync
            if (window.PbjSharedState) {
                try {
                    var ss = new window.PbjSharedState({
                        key: SHARED_KEY,
                        date: '2000-01-01',
                        pollMs: 60000,
                        onRemote: function () {},
                        onStatus: function () {}
                    });
                    ss.version = 0;
                    ss.push(state, { force: true });
                } catch (e2) {}
            }
        }

        function doImport() {
            if (!mapped.length) {
                toast(isSweet ? 'Nothing to import' : 'Nothing to import');
                return;
            }
            var state = loadSales();
            var dup = document.getElementById('dup-mode').value;
            var provider = document.getElementById('pos-provider').value || 'other';
            var added = 0, replaced = 0, skipped = 0;
            mapped.forEach(function (r) {
                var existing = state.days.find(function (d) { return d.date === r.date; });
                if (existing && dup === 'skip') {
                    skipped++;
                    return;
                }
                var id = existing ? existing.id : uid();
                if (existing) {
                    state.days = state.days.filter(function (d) { return d.date !== r.date; });
                    replaced++;
                } else {
                    added++;
                }
                state.days.push({
                    id: id,
                    date: r.date,
                    source: 'pos',
                    posProvider: provider,
                    gross: r.gross != null ? r.gross : '',
                    net: r.net != null ? r.net : '',
                    tax: r.tax != null ? r.tax : '',
                    tips: r.tips != null ? r.tips : '',
                    covers: r.covers != null ? r.covers : '',
                    checks: r.checks != null ? r.checks : '',
                    tenderCash: r.tenderCash != null ? r.tenderCash : '',
                    tenderCard: r.tenderCard != null ? r.tenderCard : '',
                    tenderOther: r.tenderOther != null ? r.tenderOther : '',
                    labor: r.labor != null ? r.labor : '',
                    notes: (isSweet ? ('Imported from ' + provider + ' CSV') : ('Imported from ' + provider + ' CSV')),
                    updatedAt: Date.now()
                });
            });
            saveSales(state);
            toast(isSweet
                ? ('Imported ' + added + ' new · ' + replaced + ' replaced · ' + skipped + ' skipped ✨')
                : ('+' + added + ' / ~' + replaced + ' / skip ' + skipped));
            paintPreview();
        }

        function runParse(text) {
            var table = parseCsv(text);
            if (table.length < 2) {
                setStatus(isSweet ? 'Need a header row + at least one data row.' : 'Need header + data rows.', true);
                return;
            }
            headers = table[0].map(function (h) { return String(h || '').trim(); });
            rows = table.slice(1);
            map = autoMap(headers);
            if (!map.date) {
                setStatus(isSweet ? 'Couldn’t find a date column — map it below.' : 'Map a date column below.', true);
            } else {
                setStatus(isSweet ? ('Parsed ' + rows.length + ' rows · ' + headers.length + ' columns') : (rows.length + ' rows parsed'));
            }
            paintMap();
            paintPreview();
        }

        document.getElementById('btn-parse').addEventListener('click', function () {
            runParse(document.getElementById('csv-text').value);
        });
        document.getElementById('btn-remap').addEventListener('click', function () {
            document.querySelectorAll('#map-grid [data-map]').forEach(function (sel) {
                map[sel.getAttribute('data-map')] = sel.value;
            });
            paintPreview();
        });
        document.getElementById('btn-import').addEventListener('click', doImport);
        document.getElementById('file-csv').addEventListener('change', function () {
            var f = this.files && this.files[0];
            if (!f) return;
            var reader = new FileReader();
            reader.onload = function () {
                document.getElementById('csv-text').value = String(reader.result || '');
                runParse(document.getElementById('csv-text').value);
            };
            reader.readAsText(f);
            this.value = '';
        });
        document.getElementById('btn-sample').addEventListener('click', function () {
            var today = new Date();
            function d(offset) {
                var x = new Date(today);
                x.setDate(x.getDate() - offset);
                return x.toISOString().slice(0, 10);
            }
            var brand = currentProvider();
            var gen = BRAND_SAMPLES[brand] || BRAND_SAMPLES.other;
            document.getElementById('csv-text').value = gen(d);
            runParse(document.getElementById('csv-text').value);
            toast(isSweet ? (brand + ' sample loaded ✨') : (brand + ' sample loaded'));
        });
        document.getElementById('pos-provider').addEventListener('change', function () {
            paintPresetHint();
            // Re-map if we already have headers
            if (headers.length) {
                map = autoMap(headers);
                paintMap();
                paintPreview();
            }
        });
        paintPresetHint();
    })();
    </script>
</body>
</html>
