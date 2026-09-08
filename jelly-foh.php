<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'FOH';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'FOH Updates' : 'FOH Updates'; ?> • <?php echo pbj_hub_label('messages'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.6rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 90px; resize: vertical; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .post { border-left: 5px solid #5B9BD5; border-radius: 0 14px 14px 0; padding: 14px 16px; margin-bottom: 12px; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .post-title { font-size: 1.15rem; margin: 0 0 6px; }
        .post-meta { font-size: 0.9rem; opacity: 0.75; margin-bottom: 8px; }
        .post-body { font-size: 1.05rem; line-height: 1.45; white-space: pre-wrap; margin-bottom: 10px; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.78rem; background: #EAF2FA; color: #1A4A7A; margin-right: 6px; }
        .empty { text-align: center; padding: 28px; opacity: 0.8; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .chip { border: none; border-radius: 999px; padding: 8px 14px; font-size: 0.9rem; cursor: pointer; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .chip.active { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .search { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; margin-bottom: 12px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: white; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: white; color: #1A2A44;<?php endif; ?> }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A; }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
        .edit-banner { display: none; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.95rem; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC; color: #E55163;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE; color: #1A2A44;<?php endif; ?> }
        .edit-banner.show { display: block; }
        .form-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .form-actions .btn { flex: 1; min-width: 120px; }
        .post-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/messages" class="back-link">← <?php echo pbj_back_to_hub('messages'); ?></a>
        <h1><?php echo $is_sweet ? 'FOH Updates' : 'FOH Updates'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Front-of-house channel for service notes' : 'Front-of-house service notes'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill"><span class="dot"></span><span id="sync-pill-text">Connecting…</span></div>

        <div class="card">
            <h2 id="form-heading"><?php echo $is_sweet ? 'Post to FOH' : 'Post to FOH'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'Section notes, wait times, VIP heads-ups, sidework flips — the floor chat 🍽️' : 'Section notes, wait times, VIP heads-ups, sidework.'; ?></p>
            <div class="edit-banner" id="edit-banner"><?php echo $is_sweet ? 'Editing an FOH update — save when ready, or cancel ✏️' : 'Editing FOH update — save to update, or cancel.'; ?></div>
            <form id="post-form">
                <input type="hidden" id="f-edit-id" value="">
                <div class="field"><label><?php echo $is_sweet ? 'Title' : 'Title'; ?></label><input id="f-title" required placeholder="<?php echo $is_sweet ? 'e.g. Patio full · high chairs low' : 'Short title'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Update' : 'Update'; ?></label><textarea id="f-body" required placeholder="<?php echo $is_sweet ? 'What’s going on up front…' : 'Details…'; ?>"></textarea></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Tag' : 'Tag'; ?></label>
                        <select id="f-tag">
                            <option value="general"><?php echo $is_sweet ? 'General' : 'General'; ?></option>
                            <option value="floor"><?php echo $is_sweet ? 'Floor / sections' : 'Floor / sections'; ?></option>
                            <option value="guests"><?php echo $is_sweet ? 'Guests / VIPs' : 'Guests / VIPs'; ?></option>
                            <option value="sidework"><?php echo $is_sweet ? 'Sidework' : 'Sidework'; ?></option>
                            <option value="pos"><?php echo $is_sweet ? 'POS / cash' : 'POS / cash'; ?></option>
                        </select>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'From' : 'From'; ?></label>
                        <select id="f-author-sel"></select>
                        <input id="f-author" style="display:none;margin-top:8px;" placeholder="<?php echo $is_sweet ? 'Type a name…' : 'Type a name…'; ?>">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-edit" style="display:none;"><?php echo $is_sweet ? 'Cancel edit' : 'Cancel edit'; ?></button>
                    <button type="submit" class="btn btn-primary" id="submit-btn"><?php echo $is_sweet ? 'Post to FOH 🍽️' : 'Post to FOH'; ?></button>
                </div>
            </form>
        </div>

        <input type="search" class="search" id="search" placeholder="<?php echo $is_sweet ? 'Search FOH updates…' : 'Search FOH updates…'; ?>">
        <div class="filters" id="filters">
            <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
            <button type="button" class="chip" data-filter="floor"><?php echo $is_sweet ? 'Floor' : 'Floor'; ?></button>
            <button type="button" class="chip" data-filter="guests"><?php echo $is_sweet ? 'Guests' : 'Guests'; ?></button>
            <button type="button" class="chip" data-filter="sidework"><?php echo $is_sweet ? 'Sidework' : 'Sidework'; ?></button>
            <button type="button" class="chip" data-filter="pos">POS</button>
        </div>
        <div id="feed"></div>
        <div class="actions-bar">
            <a href="/messages/boh" class="btn btn-secondary"><?php echo $is_sweet ? 'BOH channel' : 'BOH channel'; ?></a>
            <a href="/messages" class="btn btn-primary"><?php echo pbj_hub_label('messages'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Posted 💾' : 'Posted'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="shared-state.js?v=3"></script>
    <script src="jelly-shared.js"></script>
    <script>
    (function () {
        var KEY = 'pbj_jelly_foh_v1';
        var SHARED_KEY = 'jelly_foh_v1';
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyFohUPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var canView = canP('messages.foh_updates.view') || canP('messages.foh_updates.post');
                var canPost = canP('messages.foh_updates.post');
                if (!canView) {
                    var c = document.querySelector('.content');
                    if (c && !document.getElementById('msg-denied')) {
                        c.insertAdjacentHTML('afterbegin', '<div class="intro" id="msg-denied">No permission to view this channel.</div>');
                    }
                }
                document.querySelectorAll('form, #composer, .composer, #post-form, #note-form, #msg-form, #thread-form').forEach(function(el) {
                    el.style.display = canPost ? '' : 'none';
                });
                document.querySelectorAll('button[type=submit], #post-btn, #send-btn, #del-thread').forEach(function(el) {
                    if (!canPost && el.id !== 'del-thread') el.style.display = 'none';
                });
                // remove buttons typically need post rights
                document.querySelectorAll('[data-act="del"]').forEach(function(el) {
                    el.style.display = canPost ? '' : 'none';
                });
                try { if (typeof render === 'function') render(); } catch (e) {}
                try { if (typeof paint === 'function') paint(); } catch (e) {}
            }

        var userName = <?php echo json_encode($user_name); ?>;
        var J = window.Jelly;
        var filter = 'all';
        var searchQ = '';
        var state = load();
        var shared = null;

        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                return r && Array.isArray(r.posts) ? r : { posts: [] };
            } catch (e) { return { posts: [] }; }
        }

        function persist(showToast, toastMsg) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (shared) shared.push(state);
            if (showToast) J.toast(toastMsg || (isSweet ? 'Saved 💾' : 'Saved'));
        }

        function tagLabel(t) {
            var map = {
                general: isSweet ? 'General' : 'General',
                floor: isSweet ? 'Floor' : 'Floor',
                guests: isSweet ? 'Guests' : 'Guests',
                sidework: isSweet ? 'Sidework' : 'Sidework',
                pos: 'POS'
            };
            return map[t] || t;
        }

        function setAuthor(name) {
            var n = name || userName || 'FOH';
            J.fillAuthorSelect(document.getElementById('f-author-sel'), n, isSweet);
            var inp = document.getElementById('f-author');
            inp.style.display = 'none';
            inp.value = n;
        }

        function clearForm() {
            document.getElementById('f-edit-id').value = '';
            document.getElementById('f-title').value = '';
            document.getElementById('f-body').value = '';
            document.getElementById('f-tag').value = 'general';
            document.getElementById('edit-banner').classList.remove('show');
            document.getElementById('cancel-edit').style.display = 'none';
            document.getElementById('form-heading').textContent = isSweet ? 'Post to FOH' : 'Post to FOH';
            document.getElementById('submit-btn').textContent = isSweet ? 'Post to FOH 🍽️' : 'Post to FOH';
            setAuthor(userName);
        }

        function startEdit(p) {
            document.getElementById('f-edit-id').value = p.id;
            document.getElementById('f-title').value = p.title || '';
            document.getElementById('f-body').value = p.body || '';
            document.getElementById('f-tag').value = p.tag || 'general';
            document.getElementById('edit-banner').classList.add('show');
            document.getElementById('cancel-edit').style.display = '';
            document.getElementById('form-heading').textContent = isSweet ? 'Edit FOH update' : 'Edit FOH update';
            document.getElementById('submit-btn').textContent = isSweet ? 'Save changes 💾' : 'Save changes';
            setAuthor(p.author || userName);
            document.getElementById('f-title').focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function render() {
            var posts = state.posts.slice().sort(function (a, b) { return (b.at || 0) - (a.at || 0); });
            if (filter !== 'all') posts = posts.filter(function (p) { return p.tag === filter; });
            if (searchQ) {
                var q = searchQ.toLowerCase();
                posts = posts.filter(function (p) {
                    return [p.title, p.body, p.author, p.tag].join(' ').toLowerCase().indexOf(q) !== -1;
                });
            }
            var root = document.getElementById('feed');
            if (!posts.length) {
                root.innerHTML = '<div class="card empty">' + (isSweet
                    ? (searchQ ? 'No matches — try another search 🔍' : 'FOH channel is quiet — post the first note 🍽️')
                    : (searchQ ? 'No matching updates.' : 'No FOH updates yet.')) + '</div>';
                return;
            }
            root.innerHTML = posts.map(function (p) {
                return '<div class="post"><span class="badge">' + J.esc(tagLabel(p.tag)) + '</span>' +
                    '<h3 class="post-title">' + J.esc(p.title) + '</h3>' +
                    '<div class="post-meta">' + J.esc(p.author || 'FOH') + ' · ' + J.esc(J.when(p.at)) + '</div>' +
                    '<div class="post-body">' + J.esc(p.body) + '</div>' +
                    '<div class="post-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-id="' + J.esc(p.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-id="' + J.esc(p.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        document.getElementById('post-form').addEventListener('submit', function (e) {
                if (!canP('messages.foh_updates.post')) { e.preventDefault(); return; }
            e.preventDefault();
            var title = document.getElementById('f-title').value.trim();
            var body = document.getElementById('f-body').value.trim();
            if (!title || !body) return;
            var tag = document.getElementById('f-tag').value || 'general';
            var author = J.getAuthorValue('f-author-sel', 'f-author', userName || 'FOH');
            var editId = document.getElementById('f-edit-id').value;
            if (editId) {
                var existing = state.posts.find(function (x) { return x.id === editId; });
                if (existing) {
                    existing.title = title;
                    existing.body = body;
                    existing.tag = tag;
                    existing.author = author;
                    existing.at = Date.now();
                }
                persist(true, isSweet ? 'Updated 💾' : 'Updated');
            } else {
                state.posts.push({
                    id: J.uid(),
                    title: title,
                    body: body,
                    tag: tag,
                    author: author,
                    at: Date.now()
                });
                persist(true, isSweet ? 'Posted 💾' : 'Posted');
            }
            clearForm();
            render();
        });

        document.getElementById('cancel-edit').addEventListener('click', function () {
            clearForm();
        });

        document.getElementById('search').addEventListener('input', function () {
            searchQ = this.value.trim();
            render();
        });

        document.getElementById('filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.chip');
            if (!chip) return;
            filter = chip.dataset.filter;
            document.querySelectorAll('#filters .chip').forEach(function (c) {
                c.classList.toggle('active', c === chip);
            });
            render();
        });

        document.getElementById('feed').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-act]');
            if (!btn) return;
            var p = state.posts.find(function (x) { return x.id === btn.dataset.id; });
            if (!p) return;
            if (btn.dataset.act === 'edit') {
                startEdit(p);
                return;
            }
            if (btn.dataset.act === 'del') {
                if (!confirm(isSweet ? 'Remove this FOH update?' : 'Remove this FOH update?')) return;
                state.posts = state.posts.filter(function (x) { return x.id !== p.id; });
                if (document.getElementById('f-edit-id').value === p.id) clearForm();
                persist(true, isSweet ? 'Removed' : 'Removed');
                render();
            }
        });

        J.attachAuthorPicker('f-author-sel', 'f-author', userName, isSweet);

        shared = J.wireShared(SHARED_KEY, function () {
            return state;
        }, function (payload) {
            if (!payload || !Array.isArray(payload.posts)) return;
            state = { posts: payload.posts };
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
        });

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyFohUPerms);
            document.addEventListener('pbj-perms-ready', applyFohUPerms);
    })();
    </script>
</body>
</html>
