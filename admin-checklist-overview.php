<?php
/**
 * Manager checklist overview — all FOH/BOH lists + completion % + photo proof at a glance.
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
    <title><?php echo $is_sweet ? 'Checklist Overview' : 'Checklist Overview'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.05rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 820px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        .stat { background: white; border-radius: 16px; padding: 14px 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stat .num { font-size: 1.35rem; <?php if ($is_sweet): ?>color: #E55163;<?php else: ?>color: #1A2A44;<?php endif; ?> }
        .stat .lbl { font-size: 0.8rem; opacity: 0.7; margin-top: 4px; }
        .card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.25rem; margin: 0 0 10px; }
        .list-row {
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
            padding: 12px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>;
        }
        .list-row:last-child { border-bottom: none; }
        .list-main { flex: 1; min-width: 160px; }
        .list-title { font-size: 1.05rem; margin: 0 0 2px; }
        .list-meta { font-size: 0.82rem; opacity: 0.7; line-height: 1.35; }
        .bar { height: 10px; border-radius: 999px; background: <?php echo $is_sweet ? '#F7E0E4' : '#D9E0EA'; ?>; overflow: hidden; margin-top: 6px; max-width: 220px; }
        .bar > i { display: block; height: 100%; width: 0%; <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> }
        .badge {
            display: inline-block; border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600;
        }
        .badge.done { background: #E8F8F1; color: #1F6B4A; }
        .badge.partial { background: #FFF8E6; color: #8A6D1F; }
        .badge.empty { background: #ECEFF1; color: #546E7A; }
        .badge.open { <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163;<?php else: ?>background: #EEF2F8; color: #1A2A44;<?php endif; ?> }
        .btn { border: none; border-radius: 14px; padding: 10px 14px; font-size: 0.95rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> color: inherit; }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 7px 10px; font-size: 0.82rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; }
        .hint { font-size: 0.92rem; opacity: 0.72; margin: 0 0 10px; line-height: 1.4; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .photo-thumbs { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .photo-thumbs a { display: block; width: 52px; height: 52px; border-radius: 10px; overflow: hidden; border: 2px solid rgba(0,0,0,0.08); }
        .photo-thumbs img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .empty { text-align: center; padding: 20px; opacity: 0.75; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .section-label { font-size: 0.8rem; opacity: 0.55; letter-spacing: 0.04em; text-transform: uppercase; margin: 18px 0 8px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .denied { background: #FFEBEE; border: 1px solid #EF9A9A; color: #B71C1C; border-radius: 14px; padding: 14px; margin-bottom: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
        <h1><?php echo $is_sweet ? 'Checklist Overview' : 'Checklist Overview'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Who finished what — FOH, BOH & prep' : 'FOH, BOH & prep completion at a glance'; ?></p>
    </div>
    <div class="content">
        <div class="intro" data-perm="ops.receive_list_completion">
            <?php echo $is_sweet
                ? 'Owner / GM / manager control tower 💕 Live house sync for open/close, sidework, bar, prep, cleaning, and temps. Photos show when staff attached proof.'
                : 'Manager view of house checklists. Live sync of completion status and photo proof.'; ?>
        </div>
        <div class="denied" id="perm-denied" style="display:none;" hidden>
            <?php echo $is_sweet
                ? 'This board is for owners, GMs, and managers (permission: receive list completion alerts). Ask your house lead if you need access 💕'
                : 'Restricted to managers with list-completion permission.'; ?>
        </div>

        <div class="sync-pill" id="sync-pill"><span class="dot"></span><span id="sync-text"><?php echo $is_sweet ? 'Loading…' : 'Loading…'; ?></span></div>

        <div class="stats-row">
            <div class="stat"><div class="num" id="stat-lists">0</div><div class="lbl"><?php echo $is_sweet ? 'Lists tracked' : 'Lists'; ?></div></div>
            <div class="stat"><div class="num" id="stat-done">0</div><div class="lbl"><?php echo $is_sweet ? 'Fully done' : 'Complete'; ?></div></div>
            <div class="stat"><div class="num" id="stat-photos">0</div><div class="lbl"><?php echo $is_sweet ? 'Photos today' : 'Photos'; ?></div></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Today’s board' : "Today's board"; ?></h2>
            <p class="hint" id="date-label">—</p>
            <div id="board"><div class="empty"><?php echo $is_sweet ? 'Loading house lists…' : 'Loading…'; ?></div></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Recent completion pings' : 'Recent completion alerts'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Same feed managers get as toasts when a list finishes ✨' : 'From list-completion events.'; ?></p>
            <div id="alerts"></div>
        </div>

        <div class="actions-bar">
            <button type="button" class="btn btn-secondary" id="btn-refresh"><?php echo $is_sweet ? 'Refresh now' : 'Refresh'; ?></button>
            <a href="/admin" class="btn btn-primary"><?php echo pbj_back_to_hub('admin'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <?php include 'bottom-nav.php'; ?>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var API = '/shared-state-api.php';

        // Shared keys used across FOH/BOH checklists + prep
        var SOURCES = [
            { key: 'showtime_open_close_edit_v1', title: isSweet ? 'Showtime · Open / Close' : 'FOH Open/Close', href: '/FOH/opening-closing', group: 'FOH', kind: 'lists' },
            { key: 'showtime_sidework_edit_v1', title: isSweet ? 'Showtime · Sidework' : 'FOH Sidework', href: '/FOH/sidework', group: 'FOH', kind: 'lists' },
            { key: 'showtime_bar_edit_v1', title: isSweet ? 'Showtime · Bar' : 'FOH Bar', href: '/FOH/bar', group: 'FOH', kind: 'lists' },
            { key: 'heat_open_close_v3', title: isSweet ? 'The Heat · Open / Close' : 'BOH Open/Close', href: '/BOH/opening-closing', group: 'BOH', kind: 'lists' },
            { key: 'heat_prep_v1', title: isSweet ? 'The Heat · Prep' : 'BOH Prep', href: '/BOH/prep', group: 'BOH', kind: 'prep' },
            { key: 'heat_cleaning_v1', title: isSweet ? 'The Heat · Cleaning' : 'BOH Cleaning', href: '/BOH/cleaning', group: 'BOH', kind: 'lists' },
            // FOH editable lists (already on overview)
            { key: 'ops_list_completions_v1', title: 'Completions feed', href: '', group: 'meta', kind: 'events' }
        ];

        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return false;
        }
        function canView() {
            if (!window.PbjPerms || !window.PbjPerms.loaded) return false;
            var r = window.PbjPerms.role;
            if (r === 'owner' || r === 'gm' || r === 'admin' || r === 'manager') return true;
            return canP('ops.receive_list_completion') || canP('admin.ops.docs.sections');
        }
        function esc(s) {
            return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function businessDate() {
            if (window.pbjBusinessDate) return window.pbjBusinessDate();
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function toast(msg) {
            var el = document.getElementById('toast');
            el.textContent = msg;
            el.classList.add('show');
            setTimeout(function () { el.classList.remove('show'); }, 1600);
        }
        function setSync(kind, text) {
            var pill = document.getElementById('sync-pill');
            var t = document.getElementById('sync-text');
            pill.classList.toggle('offline', kind === 'offline');
            t.textContent = text || '';
        }

        function fetchKey(key, date) {
            var url = API + '?key=' + encodeURIComponent(key) + '&date=' + encodeURIComponent(date) + '&_=' + Date.now();
            return fetch(url, { credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .catch(function () { return { ok: false, data: null }; });
        }

        function summarizeLists(payload) {
            var lists = (payload && payload.lists) || [];
            var out = [];
            lists.forEach(function (list) {
                var items = list.items || [];
                var total = items.length;
                var done = 0;
                var photos = [];
                var lastBy = '';
                var lastAt = 0;
                var assignees = {};
                items.forEach(function (it) {
                    if (it.done) {
                        done++;
                        var ts = it.doneUpdatedAt || 0;
                        if (ts >= lastAt) {
                            lastAt = ts;
                            lastBy = it.completedBy || it.doneBy || '';
                        }
                    }
                    var src = it.photoUrl || it.photoDataUrl || it.photo;
                    if (src) photos.push(src);
                    if (it.assignedTo) assignees[String(it.assignedTo).trim()] = true;
                });
                out.push({
                    id: list.id,
                    title: ((list.icon ? list.icon + ' ' : '') + (list.title || 'List')).trim(),
                    total: total,
                    done: done,
                    photos: photos,
                    lastBy: lastBy,
                    lastAt: lastAt,
                    assignees: Object.keys(assignees)
                });
            });
            return out;
        }

        function summarizePrep(payload) {
            // prep: stations[].items[] with done + assignedTo
            var stations = (payload && payload.stations) || [];
            if (!stations.length && payload && payload.lists) return summarizeLists(payload);
            var total = 0, done = 0, photos = [], lastBy = '', lastAt = 0, assignees = {};
            stations.forEach(function (st) {
                (st.items || []).forEach(function (it) {
                    total++;
                    if (it.done) {
                        done++;
                        var ts = it.doneUpdatedAt || 0;
                        if (ts >= lastAt) {
                            lastAt = ts;
                            lastBy = it.completedBy || '';
                        }
                    }
                    var src = it.photoUrl || it.photoDataUrl || it.photo;
                    if (src) photos.push(src);
                    if (it.assignedTo) assignees[String(it.assignedTo).trim()] = true;
                });
            });
            return [{
                id: 'prep',
                title: isSweet ? 'All prep stations' : 'All prep stations',
                total: total,
                done: done,
                photos: photos,
                lastBy: lastBy,
                lastAt: lastAt,
                assignees: Object.keys(assignees)
            }];
        }

        function pct(done, total) {
            if (!total) return 0;
            return Math.round(done / total * 100);
        }
        function badgeFor(done, total) {
            if (!total) return { cls: 'empty', text: isSweet ? 'No tasks' : 'Empty' };
            if (done >= total) return { cls: 'done', text: isSweet ? 'Done ✨' : 'Complete' };
            if (done === 0) return { cls: 'open', text: isSweet ? 'Not started' : 'Not started' };
            return { cls: 'partial', text: done + '/' + total };
        }
        function fmtTime(ms) {
            if (!ms) return '';
            try {
                return new Date(ms).toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
            } catch (e) { return ''; }
        }

        var cache = {};

        function renderBoard() {
            var root = document.getElementById('board');
            var groups = { FOH: [], BOH: [] };
            var listsN = 0, doneN = 0, photosN = 0;

            SOURCES.forEach(function (src) {
                if (src.kind === 'events') return;
                var pack = cache[src.key];
                var rows = [];
                if (pack && pack.payload) {
                    rows = src.kind === 'prep' ? summarizePrep(pack.payload) : summarizeLists(pack.payload);
                }
                if (!rows.length) {
                    rows = [{ id: '_empty', title: src.title, total: 0, done: 0, photos: [], lastBy: '', lastAt: 0, empty: true }];
                }
                rows.forEach(function (row) {
                    listsN++;
                    if (row.total && row.done >= row.total) doneN++;
                    photosN += (row.photos || []).length;
                    var b = badgeFor(row.done, row.total);
                    var p = pct(row.done, row.total);
                    var meta = [];
                    if (row.total) meta.push(row.done + '/' + row.total + (isSweet ? ' checked' : ' done'));
                    if (row.lastBy) meta.push((isSweet ? 'last: ' : 'last: ') + row.lastBy + (row.lastAt ? ' · ' + fmtTime(row.lastAt) : ''));
                    if (row.assignees && row.assignees.length) meta.push((isSweet ? 'assigned: ' : 'assigned: ') + row.assignees.slice(0, 4).join(', ') + (row.assignees.length > 4 ? '…' : ''));
                    if (!row.total && row.empty) meta.push(isSweet ? 'No activity yet today' : 'No activity yet');
                    var thumbs = (row.photos || []).slice(0, 6).map(function (u) {
                        return '<a href="' + esc(u) + '" target="_blank" rel="noopener"><img src="' + esc(u) + '" alt=""></a>';
                    }).join('');
                    var html =
                        '<div class="list-row">' +
                        '<div class="list-main">' +
                        '<p class="list-title">' + esc(row.id === '_empty' || rows.length === 1 ? src.title : (src.title + ' · ' + row.title)) + '</p>' +
                        '<div class="list-meta">' + esc(meta.join(' · ')) + '</div>' +
                        (row.total ? '<div class="bar"><i style="width:' + p + '%"></i></div>' : '') +
                        (thumbs ? '<div class="photo-thumbs">' + thumbs + '</div>' : '') +
                        '</div>' +
                        '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">' +
                        '<span class="badge ' + b.cls + '">' + esc(b.text) + '</span>' +
                        (src.href ? '<a class="btn btn-ghost btn-small" href="' + esc(src.href) + '">' + (isSweet ? 'Open' : 'Open') + '</a>' : '') +
                        '</div></div>';
                    if (groups[src.group]) groups[src.group].push(html);
                });
            });

            document.getElementById('stat-lists').textContent = listsN;
            document.getElementById('stat-done').textContent = doneN;
            document.getElementById('stat-photos').textContent = photosN;

            var html = '';
            ['FOH', 'BOH'].forEach(function (g) {
                if (!groups[g].length) return;
                html += '<div class="section-label">' + g + '</div>' + groups[g].join('');
            });
            root.innerHTML = html || '<div class="empty">' + (isSweet ? 'No lists found for today' : 'No lists found') + '</div>';
        }

        function renderAlerts() {
            var root = document.getElementById('alerts');
            var pack = cache['ops_list_completions_v1'];
            var events = (pack && pack.payload && pack.payload.events) || [];
            // also local fallback
            if (!events.length) {
                try {
                    var local = JSON.parse(localStorage.getItem('pbj_ops_list_completions_v1') || 'null');
                    if (local && Array.isArray(local.events)) events = local.events;
                } catch (e) {}
            }
            events = events.slice().sort(function (a, b) { return (b.at || 0) - (a.at || 0); }).slice(0, 12);
            if (!events.length) {
                root.innerHTML = '<p class="hint">' + (isSweet ? 'No completion pings yet today — when a list finishes, it lands here 💕' : 'No completion events yet.') + '</p>';
                return;
            }
            root.innerHTML = events.map(function (ev) {
                var when = ev.at ? new Date(ev.at).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) : '';
                return '<div class="list-row"><div class="list-main">' +
                    '<p class="list-title">' + esc(ev.listTitle || ev.pageTitle || 'List done') + '</p>' +
                    '<div class="list-meta">' + esc((ev.completedBy ? ev.completedBy + ' · ' : '') + when) + '</div></div>' +
                    (ev.href ? '<a class="btn btn-ghost btn-small" href="' + esc(ev.href) + '">' + (isSweet ? 'Open' : 'Open') + '</a>' : '') +
                    '</div>';
            }).join('');
        }

        function refresh() {
            if (!canView()) {
                setSync('offline', isSweet ? 'No access' : 'No access');
                return;
            }
            setSync('syncing', isSweet ? 'Refreshing…' : 'Refreshing…');
            var date = businessDate();
            document.getElementById('date-label').textContent = isSweet
                ? ('Business day ' + date + ' · auto-refreshes while this page is open')
                : ('Business day ' + date);
            var keys = SOURCES.map(function (s) { return s.key; });
            Promise.all(keys.map(function (k) {
                return fetchKey(k, date).then(function (res) {
                    cache[k] = {
                        exists: !!(res.data && res.data.exists),
                        payload: res.data && res.data.payload ? res.data.payload : null
                    };
                });
            })).then(function () {
                setSync('live', isSweet ? 'Live · kitchen sync on' : 'Live');
                renderBoard();
                renderAlerts();
            }).catch(function () {
                setSync('offline', isSweet ? 'Offline — showing last known' : 'Offline');
                renderBoard();
                renderAlerts();
            });
        }

        function applyPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            var ok = canView();
            var denied = document.getElementById('perm-denied');
            if (denied) {
                denied.style.display = ok ? 'none' : 'block';
                if (ok) denied.setAttribute('hidden', 'hidden');
                else denied.removeAttribute('hidden');
            }
            if (ok) refresh();
            else {
                document.getElementById('board').innerHTML = '';
                document.getElementById('alerts').innerHTML = '';
            }
        }

        document.getElementById('btn-refresh').addEventListener('click', function () {
            refresh();
            toast(isSweet ? 'Refreshed ✨' : 'Refreshed');
        });

        // poll every 12s while visible
        setInterval(function () {
            if (document.hidden) return;
            if (canView()) refresh();
        }, 12000);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && canView()) refresh();
        });

        if (window.PbjPerms && window.PbjPerms.ready) {
            window.PbjPerms.ready.then(applyPerms);
        }
        document.addEventListener('pbj-perms-ready', applyPerms);
        // soft start if perms already loaded
        setTimeout(applyPerms, 80);
    })();
    </script>
</body>
</html>
