<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Manager Broadcasts' : 'Manager Broadcasts'; ?> • <?php echo pbj_hub_label('messages'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.3rem; margin: 0; line-height: 1.15; }
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
        .post { border-radius: 14px; padding: 14px 16px; margin-bottom: 12px; border-left: 5px solid #E8A838; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .post.urgent { border-left-color: #E57373; <?php if ($is_sweet): ?>background: #FFF5F5;<?php else: ?>background: #FDF0F0;<?php endif; ?> }
        .post.schedule { border-left-color: #5B9BD5; }
        .post-title { font-size: 1.15rem; margin: 0 0 6px; }
        .post-meta { font-size: 0.9rem; opacity: 0.75; margin-bottom: 8px; }
        .post-body { font-size: 1.05rem; line-height: 1.45; white-space: pre-wrap; margin-bottom: 10px; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 0.78rem; margin-right: 6px; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; }
        .badge.urgent { background: #FDECEA; color: #B71C1C; }
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
        .page-nav { display: none; align-items: center; justify-content: center; gap: 10px; margin: 8px 0 16px; }
        .page-nav.show { display: flex; }
        .page-nav button { border: none; border-radius: 12px; width: 42px; height: 42px; font-size: 1.1rem; cursor: pointer; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.08); color: inherit; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .page-nav button:disabled { opacity: 0.35; cursor: default; }
        .page-nav .page-lbl { font-size: 0.95rem; opacity: 0.75; min-width: 80px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/messages" class="back-link">← <?php echo pbj_back_to_hub('messages'); ?></a>
        <h1><?php echo $is_sweet ? 'Manager Broadcasts' : 'Manager Broadcasts'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Urgent alerts, schedule changes & reminders' : 'Urgent alerts, schedule changes, and reminders'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill"><span class="dot"></span><span id="sync-pill-text">Connecting…</span></div>

        <input type="search" class="search" id="search" placeholder="<?php echo $is_sweet ? 'Search broadcasts…' : 'Search broadcasts…'; ?>">
        <div class="filters" id="filters">
            <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
            <button type="button" class="chip" data-filter="urgent"><?php echo $is_sweet ? 'Urgent' : 'Urgent'; ?></button>
            <button type="button" class="chip" data-filter="schedule"><?php echo $is_sweet ? 'Schedule' : 'Schedule'; ?></button>
            <button type="button" class="chip" data-filter="reminder"><?php echo $is_sweet ? 'Reminders' : 'Reminders'; ?></button>
        </div>
        <div id="feed"></div>
        <div class="page-nav" id="page-nav">
            <button type="button" id="page-prev" aria-label="<?php echo $is_sweet ? 'Previous page' : 'Previous page'; ?>">←</button>
            <span class="page-lbl" id="page-lbl">1 / 1</span>
            <button type="button" id="page-next" aria-label="<?php echo $is_sweet ? 'Next page' : 'Next page'; ?>">→</button>
        </div>

        <div class="card" id="compose-card">
            <h2 id="form-heading"><?php echo $is_sweet ? 'Send broadcast' : 'Send broadcast'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'For time-sensitive stuff — schedule flips, weather calls, “read this before you clock in.”' : 'For time-sensitive updates and reminders.'; ?></p>
            <div class="edit-banner" id="edit-banner"><?php echo $is_sweet ? 'Editing a broadcast — save when ready, or cancel ✏️' : 'Editing broadcast — save to update, or cancel.'; ?></div>
            <form id="post-form">
                <input type="hidden" id="f-edit-id" value="">
                <div class="field"><label><?php echo $is_sweet ? 'Headline' : 'Headline'; ?></label><input id="f-title" required placeholder="<?php echo $is_sweet ? 'What needs attention?' : 'Headline'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Message' : 'Message'; ?></label><textarea id="f-body" required placeholder="<?php echo $is_sweet ? 'Details the team needs…' : 'Details…'; ?>"></textarea></div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Type' : 'Type'; ?></label>
                        <select id="f-type">
                            <option value="reminder"><?php echo $is_sweet ? 'Reminder' : 'Reminder'; ?></option>
                            <option value="schedule"><?php echo $is_sweet ? 'Schedule change' : 'Schedule change'; ?></option>
                            <option value="urgent"><?php echo $is_sweet ? 'Urgent' : 'Urgent'; ?></option>
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
                    <button type="submit" class="btn btn-primary" id="submit-btn"><?php echo $is_sweet ? 'Broadcast 📣' : 'Broadcast'; ?></button>
                </div>
            </form>
        </div>

        <div class="actions-bar">
            <a href="/messages" class="btn btn-primary"><?php echo pbj_back_to_hub('messages'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Broadcast sent 📣' : 'Broadcast sent'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="shared-state.js?v=3"></script>
    <script src="jelly-shared.js"></script>
    <script>
    (function () {
        var KEY = 'pbj_jelly_broadcasts_v1';
        var SHARED_KEY = 'jelly_broadcasts_v1';
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyBcPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var canView = canP('messages.broadcasts.view') || canP('messages.broadcasts.post');
                var canPost = canP('messages.broadcasts.post');
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
        var page = 0;
        var PER_PAGE = 5;
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

        function typeLabel(t) {
            if (t === 'urgent') return isSweet ? 'Urgent' : 'Urgent';
            if (t === 'schedule') return isSweet ? 'Schedule' : 'Schedule';
            return isSweet ? 'Reminder' : 'Reminder';
        }

        function setAuthor(name) {
            var n = name || userName || 'Manager';
            J.fillAuthorSelect(document.getElementById('f-author-sel'), n, isSweet);
            var inp = document.getElementById('f-author');
            inp.style.display = 'none';
            inp.value = n;
        }

        function clearForm() {
            document.getElementById('f-edit-id').value = '';
            document.getElementById('f-title').value = '';
            document.getElementById('f-body').value = '';
            document.getElementById('f-type').value = 'reminder';
            document.getElementById('edit-banner').classList.remove('show');
            document.getElementById('cancel-edit').style.display = 'none';
            document.getElementById('form-heading').textContent = isSweet ? 'Send broadcast' : 'Send broadcast';
            document.getElementById('submit-btn').textContent = isSweet ? 'Broadcast 📣' : 'Broadcast';
            setAuthor(userName);
        }

        function startEdit(p) {
            document.getElementById('f-edit-id').value = p.id;
            document.getElementById('f-title').value = p.title || '';
            document.getElementById('f-body').value = p.body || '';
            document.getElementById('f-type').value = p.type || 'reminder';
            document.getElementById('edit-banner').classList.add('show');
            document.getElementById('cancel-edit').style.display = '';
            document.getElementById('form-heading').textContent = isSweet ? 'Edit broadcast' : 'Edit broadcast';
            document.getElementById('submit-btn').textContent = isSweet ? 'Save changes 💾' : 'Save changes';
            setAuthor(p.author || userName);
            document.getElementById('f-title').focus();
            var compose = document.getElementById('compose-card');
            if (compose) compose.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        /** Newest first; urgent still floats to the top of the list */
        function sortPosts(list) {
            return list.slice().sort(function (a, b) {
                var aU = a.type === 'urgent' ? 1 : 0, bU = b.type === 'urgent' ? 1 : 0;
                if (aU !== bU) return bU - aU;
                return (b.at || 0) - (a.at || 0);
            });
        }

        function filteredPosts() {
            var posts = sortPosts(state.posts);
            if (filter !== 'all') posts = posts.filter(function (p) { return p.type === filter; });
            if (searchQ) {
                var q = searchQ.toLowerCase();
                posts = posts.filter(function (p) {
                    return [p.title, p.body, p.author, p.type].join(' ').toLowerCase().indexOf(q) !== -1;
                });
            }
            return posts;
        }

        function render() {
            var posts = filteredPosts();
            var root = document.getElementById('feed');
            var nav = document.getElementById('page-nav');
            var total = posts.length;
            var pages = Math.max(1, Math.ceil(total / PER_PAGE) || 1);
            if (page > pages - 1) page = pages - 1;
            if (page < 0) page = 0;

            if (!total) {
                nav.classList.remove('show');
                root.innerHTML = '<div class="card empty">' + (isSweet
                    ? (searchQ ? 'No matches — try another search 🔍' : 'No broadcasts yet — send one below 📣')
                    : (searchQ ? 'No matching broadcasts.' : 'No broadcasts yet — send one below.')) + '</div>';
                return;
            }

            nav.classList.toggle('show', pages > 1);
            document.getElementById('page-lbl').textContent = (page + 1) + ' / ' + pages;
            document.getElementById('page-prev').disabled = page <= 0;
            document.getElementById('page-next').disabled = page >= pages - 1;

            var slice = posts.slice(page * PER_PAGE, page * PER_PAGE + PER_PAGE);
            root.innerHTML = slice.map(function (p) {
                return '<div class="post ' + J.esc(p.type || 'reminder') + '" id="post-' + J.esc(p.id) + '">' +
                    '<span class="badge' + (p.type === 'urgent' ? ' urgent' : '') + '">' + J.esc(typeLabel(p.type)) + '</span>' +
                    '<h3 class="post-title">' + J.esc(p.title) + '</h3>' +
                    '<div class="post-meta">' + J.esc(p.author || 'Manager') + ' · ' + J.esc(J.when(p.at)) + '</div>' +
                    '<div class="post-body">' + J.esc(p.body) + '</div>' +
                    '<div class="post-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-id="' + J.esc(p.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-id="' + J.esc(p.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        function showNewestOnFeed(postId) {
            filter = 'all';
            searchQ = '';
            page = 0;
            var searchEl = document.getElementById('search');
            if (searchEl) searchEl.value = '';
            document.querySelectorAll('#filters .chip').forEach(function (c) {
                c.classList.toggle('active', c.dataset.filter === 'all');
            });
            state.posts = sortPosts(state.posts);
            render();
            var el = postId ? document.getElementById('post-' + postId) : document.getElementById('feed');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.getElementById('post-form').addEventListener('submit', function (e) {
                if (!canP('messages.broadcasts.post')) { e.preventDefault(); return; }
            e.preventDefault();
            var title = document.getElementById('f-title').value.trim();
            var body = document.getElementById('f-body').value.trim();
            if (!title || !body) return;
            var type = document.getElementById('f-type').value || 'reminder';
            var author = J.getAuthorValue('f-author-sel', 'f-author', userName || 'Manager');
            var editId = document.getElementById('f-edit-id').value;
            var postId = editId;
            if (editId) {
                var existing = state.posts.find(function (x) { return x.id === editId; });
                if (existing) {
                    existing.title = title;
                    existing.body = body;
                    existing.type = type;
                    existing.author = author;
                    existing.at = Date.now(); // bump to top
                }
                persist(true, isSweet ? 'Updated 💾' : 'Updated');
            } else {
                postId = J.uid();
                state.posts.unshift({
                    id: postId,
                    title: title,
                    body: body,
                    type: type,
                    author: author,
                    at: Date.now()
                });
                persist(true, isSweet ? 'Broadcast sent 📣' : 'Broadcast sent');
            }
            clearForm();
            showNewestOnFeed(postId);
        });

        document.getElementById('cancel-edit').addEventListener('click', function () {
            clearForm();
        });

        document.getElementById('search').addEventListener('input', function () {
            searchQ = this.value.trim();
            page = 0;
            render();
        });

        document.getElementById('filters').addEventListener('click', function (e) {
            var chip = e.target.closest('.chip');
            if (!chip) return;
            filter = chip.dataset.filter;
            page = 0;
            document.querySelectorAll('#filters .chip').forEach(function (c) {
                c.classList.toggle('active', c === chip);
            });
            render();
        });

        document.getElementById('page-prev').addEventListener('click', function () {
            if (page > 0) {
                page--;
                render();
                document.getElementById('feed').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
        document.getElementById('page-next').addEventListener('click', function () {
            var pages = Math.max(1, Math.ceil(filteredPosts().length / PER_PAGE) || 1);
            if (page < pages - 1) {
                page++;
                render();
                document.getElementById('feed').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
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
                if (!confirm(isSweet ? 'Remove this broadcast?' : 'Remove this broadcast?')) return;
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
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyBcPerms);
            document.addEventListener('pbj-perms-ready', applyBcPerms);
    })();
    </script>
</body>
</html>
