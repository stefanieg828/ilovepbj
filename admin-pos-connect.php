<?php
/**
 * Connect Square (OAuth) and Toast (partner credentials + restaurant GUID).
 * Sync pulls daily sales into the browser Daily Sales store (same as CSV bridge).
 */
require_once 'pos-config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
if (!isset($pdo) || !($pdo instanceof PDO)) {
    require_once 'config.php';
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$can = pos_user_can_manage($pdo);
$rid = pos_resolve_restaurant_id($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'POS Connections' : 'POS Connections'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
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
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.35rem; margin: 0 0 8px; }
        .hint { font-size: 0.92rem; opacity: 0.75; line-height: 1.45; margin: 0 0 12px; }
        .hint a { <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> font-weight: 600; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> color: inherit; }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .btn-row .btn { flex: 1; min-width: 120px; }
        .btn:disabled { opacity: 0.45; cursor: default; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8;
            <?php else: ?>font-family: 'Lora', serif; background: #FAF8F5;<?php endif; ?>
        }
        .status-pill {
            display: inline-block; border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600;
        }
        .status-pill.on { background: #E8F8F1; color: #1F6B4A; }
        .status-pill.off { background: #FFF5F6; color: #C62828; }
        .status-pill.warn { background: #FFF8E6; color: #8A6D1F; }
        .meta { font-size: 0.88rem; opacity: 0.75; line-height: 1.4; margin: 8px 0; }
        .banner { border-radius: 14px; padding: 12px 14px; margin-bottom: 14px; font-size: 0.95rem; line-height: 1.4; }
        .banner.ok { background: #E8F8F1; border: 1px solid #B6E5CF; color: #1F6B4A; }
        .banner.err { background: #FFEBEE; border: 1px solid #EF9A9A; color: #B71C1C; }
        .banner.info { background: #EEF2F8; border: 1px solid #C5D0DE; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; max-width: 90vw; text-align: center; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        code { font-size: 0.85em; background: rgba(0,0,0,0.05); padding: 1px 6px; border-radius: 6px; }
        .denied { background: #FFEBEE; border: 1px solid #EF9A9A; color: #B71C1C; border-radius: 14px; padding: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/reports" class="back-link">← <?php echo $is_sweet ? 'Back to Reports' : 'Back to Reports'; ?></a>
        <h1><?php echo $is_sweet ? 'POS Connections' : 'POS Connections'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Square · Clover · Toast · live sales sync' : 'Square, Clover OAuth · Toast partner · sales sync'; ?></p>
    </div>
    <div class="content">
        <?php if (!$can): ?>
            <div class="denied"><?php echo $is_sweet
                ? 'Only owners, GMs, and managers can connect POS accounts 💕'
                : 'Managers only.'; ?></div>
        <?php else: ?>

        <div id="flash"></div>

        <div class="card" id="card-sync-all">
            <h2><?php echo $is_sweet ? 'Sync everything' : 'Sync everything'; ?></h2>
            <p class="meta" id="sync-all-meta">—</p>
            <p class="hint"><?php echo $is_sweet
                ? 'Pulls <strong>sales + labor</strong> from every connected POS (Square timecards, Toast time entries, Clover shifts) into <a href="/admin/sales">Daily Sales</a> &amp; <a href="/admin/labor">Labor Snapshot</a>.'
                : 'Syncs sales + labor from all connected POS into Daily Sales and Labor Snapshot.'; ?></p>
            <div class="btn-row">
                <button type="button" class="btn btn-primary" id="btn-sync-all" disabled><?php echo $is_sweet ? 'Sync all connected POS' : 'Sync all connected POS'; ?></button>
                <a class="btn btn-ghost" href="/admin/labor"><?php echo $is_sweet ? 'Open Labor' : 'Labor'; ?></a>
            </div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'How connections work' : 'How connections work'; ?></h2>
            <p class="hint"><?php echo $is_sweet
                ? '<strong>Square</strong> and <strong>Clover</strong> use seller OAuth (sign in &amp; grant access). <strong>Toast</strong> uses partner client-credentials + restaurant GUID. Synced days land in <a href="/admin/sales">Daily Sales</a> with a From POS badge. Aloha, Micros, and others use <a href="/admin/pos-import">CSV import presets</a>. Secrets: <code>/var/www/private/ilovepbj/pos-secrets.local.php</code>.'
                : 'Square &amp; Clover: seller OAuth. Toast: partner client credentials + GUID. Other POS: CSV presets. Secrets in private pos-secrets.local.php.'; ?></p>
        </div>

        <div class="card" id="card-square">
            <h2>Square <?php echo $is_sweet ? '🥪' : ''; ?></h2>
            <p class="meta" id="square-config-meta">—</p>
            <p class="meta" id="square-conn-meta">—</p>
            <div class="btn-row">
                <a class="btn btn-primary" id="btn-square-connect" href="/pos/oauth/start?provider=square"><?php echo $is_sweet ? 'Connect Square' : 'Connect Square'; ?></a>
                <button type="button" class="btn btn-secondary" id="btn-square-sync" disabled><?php echo $is_sweet ? 'Sync sales + labor (7d)' : 'Sync sales + labor (7d)'; ?></button>
                <button type="button" class="btn btn-ghost" id="btn-square-locations" disabled><?php echo $is_sweet ? 'Refresh locations' : 'Locations'; ?></button>
                <button type="button" class="btn btn-danger" id="btn-square-disconnect" disabled><?php echo $is_sweet ? 'Disconnect' : 'Disconnect'; ?></button>
            </div>
            <div class="field" id="square-loc-wrap" style="display:none;margin-top:12px;">
                <label><?php echo $is_sweet ? 'Location for sync' : 'Sync location'; ?></label>
                <select id="square-location"></select>
            </div>
        </div>

        <div class="card" id="card-clover">
            <h2>Clover <?php echo $is_sweet ? '🍀' : ''; ?></h2>
            <p class="meta" id="clover-config-meta">—</p>
            <p class="meta" id="clover-conn-meta">—</p>
            <p class="hint"><?php echo $is_sweet
                ? 'Seller OAuth via Clover Developer Dashboard. Register redirect <code>https://ilovepbj.shop/pos/oauth/callback</code> on your Clover app.'
                : 'Clover OAuth v2. Redirect URI must match secrets + Clover app settings.'; ?></p>
            <div class="btn-row">
                <a class="btn btn-primary" id="btn-clover-connect" href="/pos/oauth/start?provider=clover"><?php echo $is_sweet ? 'Connect Clover' : 'Connect Clover'; ?></a>
                <button type="button" class="btn btn-secondary" id="btn-clover-sync" disabled><?php echo $is_sweet ? 'Sync sales + labor (7d)' : 'Sync sales + labor (7d)'; ?></button>
                <button type="button" class="btn btn-danger" id="btn-clover-disconnect" disabled><?php echo $is_sweet ? 'Disconnect' : 'Disconnect'; ?></button>
            </div>
        </div>

        <div class="card" id="card-toast">
            <h2>Toast <?php echo $is_sweet ? '🍞' : ''; ?></h2>
            <p class="meta" id="toast-config-meta">—</p>
            <p class="meta" id="toast-conn-meta">—</p>
            <p class="hint"><?php echo $is_sweet
                ? 'Toast uses <strong>partner API credentials</strong> (not a browser “Sign in with Toast” button for most integrators). After Toast approves your app, paste the <strong>restaurant GUID</strong> from Toast admin and connect.'
                : 'Toast partner client-credentials + restaurant external GUID.'; ?></p>
            <div class="field">
                <label><?php echo $is_sweet ? 'Restaurant GUID (Toast-Restaurant-External-ID)' : 'Restaurant GUID'; ?></label>
                <input type="text" id="toast-guid" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" autocomplete="off">
            </div>
            <div class="field">
                <label><?php echo $is_sweet ? 'Label (optional)' : 'Label (optional)'; ?></label>
                <input type="text" id="toast-label" placeholder="<?php echo $is_sweet ? 'Main street location' : 'Location name'; ?>">
            </div>
            <div class="btn-row">
                <button type="button" class="btn btn-primary" id="btn-toast-connect"><?php echo $is_sweet ? 'Connect Toast' : 'Connect Toast'; ?></button>
                <button type="button" class="btn btn-secondary" id="btn-toast-sync" disabled><?php echo $is_sweet ? 'Sync sales + labor (7d)' : 'Sync sales + labor (7d)'; ?></button>
                <button type="button" class="btn btn-danger" id="btn-toast-disconnect" disabled><?php echo $is_sweet ? 'Disconnect' : 'Disconnect'; ?></button>
            </div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Also available' : 'Also available'; ?></h2>
            <div class="btn-row">
                <a class="btn btn-secondary" href="/admin/pos-import"><?php echo $is_sweet ? '📥 CSV presets (Aloha/Micros…)' : 'CSV presets'; ?></a>
                <a class="btn btn-secondary" href="/admin/sales"><?php echo $is_sweet ? 'Daily Sales log' : 'Daily Sales'; ?></a>
                <a class="btn btn-ghost" href="/admin/pnl"><?php echo $is_sweet ? 'P&amp;L' : 'P&amp;L'; ?></a>
            </div>
        </div>

        <?php endif; ?>
    </div>
    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="/pos-sync-client.js?v=1"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var canManage = <?php echo $can ? 'true' : 'false'; ?>;
        if (!canManage) return;

        var Pos = window.PbjPosSync;
        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 2800);
        }
        function esc(s) {
            return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function flashFromQuery() {
            var q = new URLSearchParams(location.search);
            var box = document.getElementById('flash');
            if (!box) return;
            if (q.get('ok') === 'square') {
                box.innerHTML = '<div class="banner ok">' + (isSweet
                    ? 'Square connected ✨ Sync pulls sales + labor (timecards) into Daily Sales &amp; Labor Snapshot.'
                    : 'Square connected. Sync sales + labor when ready.') + '</div>';
            } else if (q.get('ok') === 'clover') {
                box.innerHTML = '<div class="banner ok">' + (isSweet
                    ? 'Clover connected 🍀 Sync pulls payments + time-clock shifts into Sales &amp; Labor.'
                    : 'Clover connected. Sync sales + labor when ready.') + '</div>';
            }
            var err = q.get('err');
            if (err) {
                var map = {
                    square_config: isSweet ? 'Add Square app id + secret in pos-secrets.local.php first.' : 'Square not configured.',
                    clover_config: isSweet ? 'Add Clover app id + secret in pos-secrets.local.php first.' : 'Clover not configured.',
                    toast_config: isSweet ? 'Add Toast client id + secret in pos-secrets.local.php first.' : 'Toast not configured.',
                    denied: isSweet ? 'Authorization was denied.' : 'Denied.',
                    state: isSweet ? 'OAuth state mismatch — try Connect again.' : 'Bad OAuth state.',
                    token: isSweet ? 'Could not exchange the OAuth code — check redirect URI & secrets.' : 'Token exchange failed.',
                    expired: isSweet ? 'OAuth session expired — try again.' : 'Expired.',
                    no_house: isSweet ? 'Join or create a house first.' : 'No restaurant.',
                    forbidden: isSweet ? 'Managers only.' : 'Forbidden.',
                    provider: isSweet ? 'Unknown provider.' : 'Unknown provider.'
                };
                box.innerHTML = '<div class="banner err">' + esc(map[err] || err) + '</div>';
            }
        }

        function connByProvider(list, p) {
            for (var i = 0; i < (list || []).length; i++) {
                if (list[i].provider === p) return list[i];
            }
            return null;
        }

        function summarizeSync(res) {
            if (!res || !res.ok) {
                return (res && res.error) || (isSweet ? 'Sync failed' : 'Sync failed');
            }
            var parts = [];
            parts.push((res.count || 0) + (isSweet ? ' sales days' : ' sales days'));
            if (res.laborCount != null) {
                parts.push((res.laborCount || 0) + (isSweet ? ' labor days' : ' labor days'));
            }
            if (res.laborError) {
                parts.push(isSweet ? 'labor ⚠' : 'labor warn');
            }
            return parts.join(' · ');
        }

        function paintStatus(data) {
            var sqCfg = data.configured && data.configured.square;
            var toastCfg = data.configured && data.configured.toast;
            var clCfg = data.configured && data.configured.clover;
            var sq = connByProvider(data.connections, 'square');
            var toast = connByProvider(data.connections, 'toast');
            var cl = connByProvider(data.connections, 'clover');
            var any = !!(data.anyConnected);

            var syncMeta = document.getElementById('sync-all-meta');
            var active = data.activeProviders || [];
            if (!any) {
                syncMeta.innerHTML = '<span class="status-pill off">' + (isSweet ? 'Nothing connected' : 'None connected') + '</span> · ' +
                    (isSweet ? 'connect Square, Clover, or Toast below' : 'connect a POS below');
            } else {
                var ago = data.latestSyncAt && Pos ? Pos.formatSyncAgo(data.latestSyncAt) : '';
                syncMeta.innerHTML = '<span class="status-pill on">' + esc(active.join(', ')) + '</span>' +
                    (data.latestSyncAt
                        ? (' · last sync ' + esc(ago || data.latestSyncAt))
                        : (isSweet ? ' · never synced' : ' · never synced'));
            }
            document.getElementById('btn-sync-all').disabled = !any;

            document.getElementById('square-config-meta').innerHTML = sqCfg
                ? '<span class="status-pill on">' + (isSweet ? 'App configured' : 'Configured') + '</span> · env <code>' + esc(data.squareEnv || '') + '</code> · redirect <code>' + esc(data.redirectUri || '') + '</code>'
                : '<span class="status-pill off">' + (isSweet ? 'Secrets missing' : 'Not configured') + '</span> · add Square keys to pos-secrets.local.php';

            document.getElementById('clover-config-meta').innerHTML = clCfg
                ? '<span class="status-pill on">' + (isSweet ? 'App configured' : 'Configured') + '</span> · env <code>' + esc(data.cloverEnv || '') + '</code> · redirect <code>' + esc(data.cloverRedirectUri || '') + '</code>'
                : '<span class="status-pill off">' + (isSweet ? 'Secrets missing' : 'Not configured') + '</span> · add Clover app id/secret';

            document.getElementById('toast-config-meta').innerHTML = toastCfg
                ? '<span class="status-pill on">' + (isSweet ? 'Partner creds configured' : 'Configured') + '</span>'
                : '<span class="status-pill off">' + (isSweet ? 'Secrets missing' : 'Not configured') + '</span> · add Toast client id/secret';

            var sqOn = sq && sq.connected;
            var laborNote = '';
            if (sqOn && sq.meta && sq.meta.labor_last_sync_at) {
                laborNote = ' · labor ' + (Pos ? Pos.formatSyncAgo(sq.meta.labor_last_sync_at) : sq.meta.labor_last_sync_at);
            }
            document.getElementById('square-conn-meta').textContent = sqOn
                ? ((isSweet ? 'Connected' : 'Connected') +
                    (sq.location_name ? ' · ' + sq.location_name : (sq.location_id ? ' · loc ' + sq.location_id : '')) +
                    (sq.last_sync_at ? ' · sales ' + (Pos ? Pos.formatSyncAgo(sq.last_sync_at) : sq.last_sync_at) : '') +
                    laborNote)
                : (isSweet ? 'Not connected yet' : 'Not connected');
            document.getElementById('btn-square-connect').style.display = sqCfg ? '' : 'none';
            document.getElementById('btn-square-sync').disabled = !sqOn;
            document.getElementById('btn-square-locations').disabled = !sqOn;
            document.getElementById('btn-square-disconnect').disabled = !sqOn;

            var clOn = cl && cl.connected;
            var clLabor = '';
            if (clOn && cl.meta && cl.meta.labor_last_sync_at) {
                clLabor = ' · labor ' + (Pos ? Pos.formatSyncAgo(cl.meta.labor_last_sync_at) : cl.meta.labor_last_sync_at);
            }
            document.getElementById('clover-conn-meta').textContent = clOn
                ? ((isSweet ? 'Connected' : 'Connected') +
                    (cl.location_name ? ' · ' + cl.location_name : (cl.merchant_id ? ' · ' + cl.merchant_id : '')) +
                    (cl.last_sync_at ? ' · sales ' + (Pos ? Pos.formatSyncAgo(cl.last_sync_at) : cl.last_sync_at) : '') +
                    clLabor +
                    (cl.last_error ? ' · ⚠ ' + cl.last_error : ''))
                : (isSweet ? 'Not connected yet' : 'Not connected');
            document.getElementById('btn-clover-connect').style.display = clCfg ? '' : 'none';
            document.getElementById('btn-clover-sync').disabled = !clOn;
            document.getElementById('btn-clover-disconnect').disabled = !clOn;

            var toastOn = toast && toast.connected;
            var toastLabor = '';
            if (toastOn && toast.meta && toast.meta.labor_last_sync_at) {
                toastLabor = ' · labor ' + (Pos ? Pos.formatSyncAgo(toast.meta.labor_last_sync_at) : toast.meta.labor_last_sync_at);
            }
            document.getElementById('toast-conn-meta').textContent = toastOn
                ? ((isSweet ? 'Connected' : 'Connected') +
                    (toast.location_name ? ' · ' + toast.location_name : '') +
                    (toast.location_id ? ' · ' + toast.location_id : '') +
                    (toast.last_sync_at ? ' · sales ' + (Pos ? Pos.formatSyncAgo(toast.last_sync_at) : toast.last_sync_at) : '') +
                    toastLabor +
                    (toast.last_error ? ' · ⚠ ' + toast.last_error : ''))
                : (isSweet ? 'Not connected yet' : 'Not connected');
            if (toastOn && toast.location_id) {
                document.getElementById('toast-guid').value = toast.location_id;
            }
            document.getElementById('btn-toast-connect').disabled = !toastCfg;
            document.getElementById('btn-toast-sync').disabled = !toastOn;
            document.getElementById('btn-toast-disconnect').disabled = !toastOn;
        }

        function refresh() {
            return Pos.status().then(function (data) {
                if (!data || !data.ok) {
                    toast(isSweet ? 'Could not load status' : 'Status failed');
                    return;
                }
                paintStatus(data);
            });
        }

        document.getElementById('btn-sync-all').addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;
            toast(isSweet ? 'Syncing all POS…' : 'Syncing…');
            Pos.syncAll(7).then(function (res) {
                btn.disabled = false;
                if (!res || !res.ok) {
                    toast((res && (res.hint || res.error)) || (isSweet ? 'Sync failed' : 'Sync failed'));
                    refresh();
                    return;
                }
                toast(isSweet
                    ? ('All POS ✨ ' + summarizeSync(res))
                    : summarizeSync(res));
                refresh();
            }).catch(function () {
                btn.disabled = false;
                toast(isSweet ? 'Network error' : 'Network error');
            });
        });

        document.getElementById('btn-square-sync').addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;
            toast(isSweet ? 'Syncing Square sales + labor…' : 'Syncing Square…');
            Pos.syncProvider('square', 7).then(function (res) {
                btn.disabled = false;
                if (!res || !res.ok) {
                    toast((res && res.error) || (isSweet ? 'Sync failed' : 'Sync failed'));
                    return;
                }
                var msg = summarizeSync(res);
                if (res.laborError) {
                    msg += (isSweet ? ' · labor note: reconnect if scope missing' : ' · labor failed');
                }
                toast(msg);
                refresh();
            }).catch(function () {
                btn.disabled = false;
                toast(isSweet ? 'Network error' : 'Network error');
            });
        });

        document.getElementById('btn-square-locations').addEventListener('click', function () {
            Pos.api('list_locations', { provider: 'square' }).then(function (res) {
                if (!res || !res.ok) {
                    toast(isSweet ? 'Could not load locations' : 'Locations failed');
                    return;
                }
                var wrap = document.getElementById('square-loc-wrap');
                var sel = document.getElementById('square-location');
                sel.innerHTML = (res.locations || []).map(function (l) {
                    return '<option value="' + esc(l.id) + '">' + esc(l.name || l.id) + '</option>';
                }).join('');
                wrap.style.display = sel.options.length ? 'block' : 'none';
                if (!sel.options.length) toast(isSweet ? 'No active locations' : 'No locations');
            });
        });

        document.getElementById('square-location').addEventListener('change', function () {
            var id = this.value;
            var name = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
            Pos.api('set_location', { provider: 'square', locationId: id, locationName: name }).then(function (res) {
                if (res && res.ok) {
                    toast(isSweet ? 'Location saved ✨' : 'Location saved');
                    refresh();
                }
            });
        });

        document.getElementById('btn-square-disconnect').addEventListener('click', function () {
            if (!confirm(isSweet ? 'Disconnect Square for this house?' : 'Disconnect Square?')) return;
            Pos.api('disconnect', { provider: 'square' }).then(function () {
                toast(isSweet ? 'Disconnected' : 'Disconnected');
                refresh();
            });
        });

        document.getElementById('btn-clover-sync').addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;
            toast(isSweet ? 'Syncing Clover sales + labor…' : 'Syncing Clover…');
            Pos.syncProvider('clover', 7).then(function (res) {
                btn.disabled = false;
                if (!res || !res.ok) {
                    toast((res && res.error) || (isSweet ? 'Sync failed' : 'Sync failed'));
                    refresh();
                    return;
                }
                toast(summarizeSync(res));
                refresh();
            }).catch(function () {
                btn.disabled = false;
                toast(isSweet ? 'Network error' : 'Network error');
            });
        });

        document.getElementById('btn-clover-disconnect').addEventListener('click', function () {
            if (!confirm(isSweet ? 'Disconnect Clover for this house?' : 'Disconnect Clover?')) return;
            Pos.api('disconnect', { provider: 'clover' }).then(function () {
                toast(isSweet ? 'Disconnected' : 'Disconnected');
                refresh();
            });
        });

        document.getElementById('btn-toast-connect').addEventListener('click', function () {
            var guid = document.getElementById('toast-guid').value.trim();
            var label = document.getElementById('toast-label').value.trim();
            if (!guid) {
                toast(isSweet ? 'Paste the Toast restaurant GUID' : 'Restaurant GUID required');
                return;
            }
            var btn = this;
            btn.disabled = true;
            Pos.api('toast_connect', { restaurantGuid: guid, locationName: label }).then(function (res) {
                btn.disabled = false;
                if (!res || !res.ok) {
                    toast((res && res.error) || (isSweet ? 'Toast connect failed' : 'Failed'));
                    return;
                }
                toast(isSweet ? 'Toast connected ✨' : 'Toast connected');
                refresh();
            }).catch(function () {
                btn.disabled = false;
                toast(isSweet ? 'Network error' : 'Network error');
            });
        });

        document.getElementById('btn-toast-sync').addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;
            toast(isSweet ? 'Syncing Toast sales + labor…' : 'Syncing Toast…');
            Pos.syncProvider('toast', 7).then(function (res) {
                btn.disabled = false;
                if (!res || !res.ok) {
                    var msg = (res && res.error) || (isSweet ? 'Sync failed' : 'Sync failed');
                    if (res && res.hint) msg += ' — ' + res.hint;
                    toast(msg);
                    refresh();
                    return;
                }
                toast(summarizeSync(res));
                refresh();
            }).catch(function () {
                btn.disabled = false;
                toast(isSweet ? 'Network error' : 'Network error');
            });
        });

        document.getElementById('btn-toast-disconnect').addEventListener('click', function () {
            if (!confirm(isSweet ? 'Disconnect Toast for this house?' : 'Disconnect Toast?')) return;
            Pos.api('disconnect', { provider: 'toast' }).then(function () {
                toast(isSweet ? 'Disconnected' : 'Disconnected');
                refresh();
            });
        });

        flashFromQuery();
        refresh();
    })();
    </script>
</body>
</html>
