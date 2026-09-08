<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Me';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Direct Messages' : 'Direct Messages'; ?> • <?php echo pbj_hub_label('messages'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.5rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 70px; resize: vertical; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .thread { display: flex; justify-content: space-between; gap: 12px; padding: 14px 0; border-bottom: 1px solid <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>; cursor: pointer; }
        .thread:last-child { border-bottom: none; }
        .thread:hover { opacity: 0.85; }
        .thread-name { font-size: 1.15rem; margin-bottom: 4px; }
        .thread-preview { font-size: 0.95rem; opacity: 0.7; line-height: 1.3; }
        .thread-time { font-size: 0.85rem; opacity: 0.6; white-space: nowrap; }
        .chat-log { max-height: 360px; overflow-y: auto; margin-bottom: 12px; padding: 8px 0; }
        .bubble { max-width: 85%; padding: 10px 14px; border-radius: 16px; margin-bottom: 10px; line-height: 1.4; font-size: 1.02rem; white-space: pre-wrap; }
        .bubble.me { margin-left: auto; <?php if ($is_sweet): ?>background: #E55163; color: white; border-bottom-right-radius: 4px;<?php else: ?>background: #1A2A44; color: white; border-bottom-right-radius: 4px;<?php endif; ?> }
        .bubble.them { margin-right: auto; <?php if ($is_sweet): ?>background: #FFF5F6; border: 1px solid #F3C5CC; border-bottom-left-radius: 4px;<?php else: ?>background: #EEF2F8; border: 1px solid #C5D0DE; border-bottom-left-radius: 4px;<?php endif; ?> }
        .bubble .who { font-size: 0.78rem; opacity: 0.8; margin-bottom: 4px; }
        .bubble .when { font-size: 0.75rem; opacity: 0.65; margin-top: 4px; }
        .empty { text-align: center; padding: 28px; opacity: 0.8; line-height: 1.4; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px); background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white; padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s; z-index: 2100; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .hidden { display: none !important; }
        .hint { font-size: 0.95rem; opacity: 0.7; margin: 0 0 12px; line-height: 1.4; }
        .sync-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.88rem; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); <?php if ($is_sweet): ?>background: #FFF5F6; color: #E55163; border: 1px solid #F3C5CC;<?php else: ?>background: #EEF2F8; color: #1A2A44; border: 1px solid #C5D0DE;<?php endif; ?> }
        .sync-pill.offline { <?php if ($is_sweet): ?>background: #FFF8E8; color: #8A6D1F; border-color: #E8D59A;<?php else: ?>background: #FFF8E8; color: #5C4B1A; border-color: #E0D2A0;<?php endif; ?> }
        .sync-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9B63; }
        .sync-pill.offline .dot { background: #C9A227; }
        .sync-pill.syncing .dot { background: #5B8DEF; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.35; } }
    </style>
</head>
<body>
    <div class="header">
        <a href="/messages" class="back-link" id="top-back">← <?php echo pbj_back_to_hub('messages'); ?></a>
        <h1 id="page-title"><?php echo $is_sweet ? 'Direct Messages' : 'Direct Messages'; ?></h1>
        <p class="subtitle" id="page-sub"><?php echo $is_sweet ? 'One-on-one chats with teammates' : 'One-on-one chats with teammates'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill" title="<?php echo $is_sweet ? 'Shared with your restaurant group' : 'Shared with your restaurant group'; ?>">
            <span class="dot"></span>
            <span id="sync-pill-text"><?php echo $is_sweet ? 'Connecting…' : 'Connecting…'; ?></span>
        </div>

        <div id="list-view">
            <div class="card">
                <h2><?php echo $is_sweet ? 'Start a chat' : 'Start a chat'; ?></h2>
                <p class="hint"><?php echo $is_sweet
                    ? 'Pick someone from the roster (or type a name). Chats sync across kitchen devices when you\'re online 💬💕'
                    : 'Pick a teammate from the roster or type a name. Chats sync across devices when online.'; ?></p>
                <div class="field-row">
                    <div class="field">
                        <label><?php echo $is_sweet ? 'With' : 'With'; ?></label>
                        <select id="new-with"></select>
                        <input id="new-with-custom" type="text" style="display:none;margin-top:8px;" placeholder="<?php echo $is_sweet ? 'Type a name…' : 'Type a name…'; ?>">
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'You are' : 'You are'; ?></label>
                        <input id="my-name" value="<?php echo htmlspecialchars($user_name); ?>">
                    </div>
                </div>
                <button type="button" class="btn btn-primary" id="start-chat" style="width:100%;"><?php echo $is_sweet ? 'Open chat ✨' : 'Open chat'; ?></button>
            </div>
            <div class="card">
                <h2><?php echo $is_sweet ? 'Conversations' : 'Conversations'; ?></h2>
                <div id="thread-list"></div>
            </div>
        </div>

        <div id="chat-view" class="hidden">
            <div class="card">
                <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:12px;">
                    <button type="button" class="btn btn-small btn-ghost" id="back-list">← <?php echo $is_sweet ? 'All chats' : 'All chats'; ?></button>
                    <button type="button" class="btn btn-small btn-danger" id="del-thread"><?php echo $is_sweet ? 'Remove chat' : 'Remove chat'; ?></button>
                </div>
                <h2 id="chat-with" style="margin-top:0;"></h2>
                <div class="chat-log" id="chat-log"></div>
                <div class="field"><textarea id="msg-body" placeholder="<?php echo $is_sweet ? 'Type a message…' : 'Type a message…'; ?>"></textarea></div>
                <button type="button" class="btn btn-primary" id="send-msg" style="width:100%;"><?php echo $is_sweet ? 'Send 💬' : 'Send'; ?></button>
            </div>
        </div>

        <div class="actions-bar" id="bottom-bar">
            <a href="/messages" class="btn btn-primary"><?php echo pbj_back_to_hub('messages'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Sent 💾' : 'Sent'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="shared-state.js?v=3"></script>
    <script src="jelly-shared.js"></script>
    <script>
    (function () {
        var KEY = 'pbj_jelly_dms_v1';
        var SHARED_KEY = 'jelly_dms_v1';
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applyDmPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var canView = canP('messages.dms.view') || canP('messages.dms.send');
                var canPost = canP('messages.dms.send');
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

        var defaultName = <?php echo json_encode($user_name); ?>;
        var J = window.Jelly;
        if (!J) return;

        var uid = J.uid;
        var esc = J.esc;
        var when = J.when;
        var toast = J.toast;

        function norm(n) { return String(n || '').trim().toLowerCase(); }

        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                return r && Array.isArray(r.threads) ? r : { threads: [] };
            } catch (e) {
                return { threads: [] };
            }
        }

        var state = load();
        var activeId = null;
        var sharedHandle = null;

        function save(showToast, toastMsg) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (sharedHandle && sharedHandle.push) sharedHandle.push(state);
            if (showToast) toast(toastMsg || (isSweet ? 'Sent 💬' : 'Sent'));
        }

        function fillWithSelect() {
            var sel = document.getElementById('new-with');
            var custom = document.getElementById('new-with-custom');
            if (!sel) return;
            var team = J.loadTeam();
            var html = '<option value="">' + (isSweet ? 'Choose a teammate…' : 'Choose a teammate…') + '</option>';
            team.forEach(function (p) {
                var label = p.name;
                if (p.roles && p.roles.length) label += ' · ' + p.roles[0];
                html += '<option value="' + esc(p.name) + '" data-person-id="' + esc(p.id || '') + '">' + esc(label) + '</option>';
            });
            html += '<option value="__custom__">' + (isSweet ? 'Other name…' : 'Other name…') + '</option>';
            sel.innerHTML = html;
            if (custom) {
                custom.style.display = 'none';
                custom.value = '';
            }
        }

        function getWithSelection() {
            var sel = document.getElementById('new-with');
            var custom = document.getElementById('new-with-custom');
            if (!sel) return { name: '', personId: '' };
            if (sel.value === '__custom__') {
                return { name: (custom && custom.value ? custom.value : '').trim(), personId: '' };
            }
            var name = (sel.value || '').trim();
            var opt = sel.options[sel.selectedIndex];
            var personId = opt && opt.getAttribute ? (opt.getAttribute('data-person-id') || '') : '';
            return { name: name, personId: personId };
        }

        function findThread(withName, personId) {
            var key = norm(withName);
            if (personId) {
                var byId = state.threads.find(function (t) {
                    return t.personId && String(t.personId) === String(personId);
                });
                if (byId) return byId;
            }
            return state.threads.find(function (t) { return norm(t.with) === key; });
        }

        function openThread(id) {
            activeId = id;
            var t = state.threads.find(function (x) { return x.id === id; });
            if (!t) return;
            document.getElementById('list-view').classList.add('hidden');
            document.getElementById('chat-view').classList.remove('hidden');
            document.getElementById('bottom-bar').classList.add('hidden');
            document.getElementById('page-title').textContent = t.with;
            document.getElementById('page-sub').textContent = isSweet ? 'Direct message 💬' : 'Direct message';
            document.getElementById('chat-with').textContent = t.with;
            renderChat(t);
        }

        function showList() {
            activeId = null;
            document.getElementById('list-view').classList.remove('hidden');
            document.getElementById('chat-view').classList.add('hidden');
            document.getElementById('bottom-bar').classList.remove('hidden');
            document.getElementById('page-title').textContent = isSweet ? 'Direct Messages' : 'Direct Messages';
            document.getElementById('page-sub').textContent = isSweet
                ? 'One-on-one chats with teammates'
                : 'One-on-one chats with teammates';
            renderList();
        }

        function renderList() {
            var root = document.getElementById('thread-list');
            var threads = state.threads.slice().sort(function (a, b) {
                var am = (a.messages && a.messages.length) ? a.messages[a.messages.length - 1].at : (a.at || 0);
                var bm = (b.messages && b.messages.length) ? b.messages[b.messages.length - 1].at : (b.at || 0);
                return bm - am;
            });
            if (!threads.length) {
                root.innerHTML = '<div class="empty">' + (isSweet
                    ? 'No chats yet — pick a teammate above and say hi 💬'
                    : 'No chats yet. Start one above.') + '</div>';
                return;
            }
            root.innerHTML = threads.map(function (t) {
                var last = t.messages && t.messages.length ? t.messages[t.messages.length - 1] : null;
                var preview = last ? last.body : (isSweet ? 'No messages yet — say hi 💕' : 'No messages yet');
                if (preview.length > 60) preview = preview.slice(0, 60) + '…';
                var whenStr = last ? when(last.at) : '';
                return '<div class="thread" data-open="' + esc(t.id) + '">' +
                    '<div><div class="thread-name">' + esc(t.with) + '</div>' +
                    '<div class="thread-preview">' + esc(preview) + '</div></div>' +
                    '<div class="thread-time">' + esc(whenStr) + '</div></div>';
            }).join('');
        }

        function renderChat(t) {
            var log = document.getElementById('chat-log');
            var me = document.getElementById('my-name').value.trim() || defaultName || 'Me';
            if (!t.messages || !t.messages.length) {
                log.innerHTML = '<div class="empty">' + (isSweet ? 'Say hi 💕' : 'No messages yet') + '</div>';
                return;
            }
            log.innerHTML = t.messages.map(function (m) {
                var mine = norm(m.from) === norm(me);
                return '<div class="bubble ' + (mine ? 'me' : 'them') + '">' +
                    (!mine ? '<div class="who">' + esc(m.from) + '</div>' : '') +
                    esc(m.body) +
                    '<div class="when">' + esc(when(m.at)) + '</div></div>';
            }).join('');
            log.scrollTop = log.scrollHeight;
        }

        function applyRemote(payload) {
            if (!payload || !Array.isArray(payload.threads)) return;
            var wasActive = activeId;
            state = { threads: payload.threads };
            localStorage.setItem(KEY, JSON.stringify(state));
            if (wasActive) {
                var still = state.threads.find(function (t) { return t.id === wasActive; });
                if (still) {
                    openThread(still.id);
                } else {
                    showList();
                }
            } else {
                renderList();
            }
        }

        document.getElementById('new-with').addEventListener('change', function () {
            var custom = document.getElementById('new-with-custom');
            if (this.value === '__custom__') {
                custom.style.display = 'block';
                custom.value = '';
                custom.focus();
            } else {
                custom.style.display = 'none';
                custom.value = '';
            }
        });

        document.getElementById('start-chat').addEventListener('click', function () {
            var sel = getWithSelection();
            if (!sel.name) {
                toast(isSweet ? 'Pick who to chat with 💬' : 'Choose who to chat with');
                return;
            }
            var existing = findThread(sel.name, sel.personId);
            if (!existing) {
                existing = {
                    id: uid(),
                    with: sel.name,
                    personId: sel.personId || undefined,
                    messages: [],
                    at: Date.now()
                };
                state.threads.push(existing);
                save(false);
            } else if (sel.personId && !existing.personId) {
                existing.personId = sel.personId;
                save(false);
            }
            document.getElementById('new-with').value = '';
            var custom = document.getElementById('new-with-custom');
            custom.style.display = 'none';
            custom.value = '';
            openThread(existing.id);
        });

        document.getElementById('thread-list').addEventListener('click', function (e) {
            var row = e.target.closest('[data-open]');
            if (!row) return;
            openThread(row.dataset.open);
        });

        document.getElementById('back-list').addEventListener('click', showList);

        document.getElementById('send-msg').addEventListener('click', send);
        document.getElementById('msg-body').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                send();
            }
        });

        function send() {
            if (!activeId) return;
            var t = state.threads.find(function (x) { return x.id === activeId; });
            if (!t) return;
            var body = document.getElementById('msg-body').value.trim();
            if (!body) return;
            var me = document.getElementById('my-name').value.trim() || defaultName || 'Me';
            if (!Array.isArray(t.messages)) t.messages = [];
            t.messages.push({ id: uid(), from: me, body: body, at: Date.now() });
            t.at = Date.now();
            document.getElementById('msg-body').value = '';
            save(true, isSweet ? 'Sent 💬' : 'Sent');
            renderChat(t);
        }

        document.getElementById('del-thread').addEventListener('click', function () {
            if (!activeId) return;
            if (!confirm(isSweet ? 'Remove this whole chat? 🗑️' : 'Remove this chat?')) return;
            state.threads = state.threads.filter(function (t) { return t.id !== activeId; });
            save(true, isSweet ? 'Chat deleted' : 'Chat deleted');
            showList();
        });

        fillWithSelect();
        renderList();

        sharedHandle = J.wireShared(SHARED_KEY, function () {
            return state;
        }, applyRemote);
                if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applyDmPerms);
            document.addEventListener('pbj-perms-ready', applyDmPerms);
})();
    </script>
</body>
</html>
