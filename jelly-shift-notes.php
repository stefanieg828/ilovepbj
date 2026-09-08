<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$user_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Team';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <title><?php echo $is_sweet ? 'Shift Notes & Handoffs' : 'Shift Notes & Handoffs'; ?> • <?php echo pbj_hub_label('messages'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.2rem; margin: 0; line-height: 1.15; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 720px; margin: 0 auto; }
        .card { background: white; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.4rem; margin: 0 0 12px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.85rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field textarea, .field select { width: 100%; box-sizing: border-box; border-radius: 12px; border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; padding: 10px 12px; font-size: 1rem; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?> }
        .field textarea { min-height: 100px; resize: vertical; }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 110px; }
        .btn { border: none; border-radius: 14px; padding: 12px 16px; font-size: 1rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-small { padding: 8px 12px; font-size: 0.9rem; border-radius: 10px; }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; color: inherit; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .note { border-radius: 14px; padding: 14px 16px; margin-bottom: 12px; border-left: 5px solid <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; <?php if ($is_sweet): ?>background: #FFFBF8;<?php else: ?>background: #FAF8F5;<?php endif; ?> }
        .note.foh { border-left-color: #5B9BD5; }
        .note.boh { border-left-color: #E57373; }
        .note.both { border-left-color: #9B7EDE; }
        .note-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; margin-bottom: 6px; }
        .note-title { font-size: 1.15rem; margin: 0; }
        .badge { border-radius: 999px; padding: 3px 10px; font-size: 0.78rem; background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; white-space: nowrap; }
        .meta { font-size: 0.9rem; opacity: 0.75; margin-bottom: 8px; }
        .body { font-size: 1.05rem; line-height: 1.45; white-space: pre-wrap; margin-bottom: 10px; }
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
        <h1><?php echo $is_sweet ? 'Shift Notes & Handoffs' : 'Shift Notes & Handoffs'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'What the next crew needs to know' : 'Pass along what the next shift needs'; ?></p>
    </div>
    <div class="content">
        <div class="sync-pill syncing" id="sync-pill"><span class="dot"></span><span id="sync-pill-text">Connecting…</span></div>

        <div class="card">
            <h2 id="form-heading"><?php echo $is_sweet ? 'Leave a handoff' : 'Leave a handoff'; ?></h2>
            <p class="hint"><?php echo $is_sweet ? 'VIPs, 86s, equipment quirks, weather vibes — future-you will thank past-you 💕' : 'VIPs, 86s, equipment issues, notes for next shift.'; ?></p>
            <div class="edit-banner" id="edit-banner"><?php echo $is_sweet ? 'Editing a handoff — save when ready, or cancel ✏️' : 'Editing handoff — save to update, or cancel.'; ?></div>
            <form id="note-form">
                <input type="hidden" id="f-edit-id" value="">
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Date' : 'Date'; ?></label><input type="date" id="f-date" required></div>
                    <div class="field"><label><?php echo $is_sweet ? 'Shift' : 'Shift'; ?></label>
                        <select id="f-shift">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                            <option value="Close">Close</option>
                            <option value="All day"><?php echo $is_sweet ? 'All day' : 'All day'; ?></option>
                        </select>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field"><label><?php echo $is_sweet ? 'Area' : 'Area'; ?></label>
                        <select id="f-area">
                            <option value="both"><?php echo $is_sweet ? 'Whole house' : 'Whole house'; ?></option>
                            <option value="foh">FOH</option>
                            <option value="boh">BOH</option>
                        </select>
                    </div>
                    <div class="field">
                        <label><?php echo $is_sweet ? 'From' : 'From'; ?></label>
                        <select id="f-author-sel"></select>
                        <input id="f-author" style="display:none;margin-top:8px;" placeholder="<?php echo $is_sweet ? 'Type a name…' : 'Type a name…'; ?>">
                    </div>
                </div>
                <div class="field"><label><?php echo $is_sweet ? 'Headline' : 'Headline'; ?></label><input id="f-title" placeholder="<?php echo $is_sweet ? 'e.g. Busy patio · 86 jam' : 'Short headline'; ?>"></div>
                <div class="field"><label><?php echo $is_sweet ? 'Notes' : 'Notes'; ?></label><textarea id="f-body" required placeholder="<?php echo $is_sweet ? 'What should the next shift know?' : 'What should the next shift know?'; ?>"></textarea></div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-edit" style="display:none;"><?php echo $is_sweet ? 'Cancel edit' : 'Cancel edit'; ?></button>
                    <button type="submit" class="btn btn-primary" id="submit-btn"><?php echo $is_sweet ? 'Save handoff 📝' : 'Save handoff'; ?></button>
                </div>
            </form>
        </div>

        <input type="search" class="search" id="search" placeholder="<?php echo $is_sweet ? 'Search handoffs…' : 'Search handoffs…'; ?>">
        <div class="filters" id="filters">
            <button type="button" class="chip active" data-filter="all"><?php echo $is_sweet ? 'All' : 'All'; ?></button>
            <button type="button" class="chip" data-filter="foh">FOH</button>
            <button type="button" class="chip" data-filter="boh">BOH</button>
            <button type="button" class="chip" data-filter="today"><?php echo $is_sweet ? 'Today' : 'Today'; ?></button>
        </div>
        <div id="feed"></div>
        <div class="actions-bar">
            <a href="/messages" class="btn btn-primary"><?php echo pbj_back_to_hub('messages'); ?></a>
        </div>
    </div>
    <div class="toast" id="toast"><?php echo $is_sweet ? 'Saved 💾' : 'Saved'; ?></div>
    <?php include 'bottom-nav.php'; ?>
    <script src="shared-state.js?v=3"></script>
    <script src="jelly-shared.js"></script>
    <script>
    (function () {
        var KEY = 'pbj_jelly_shift_notes_v1';
        var SHARED_KEY = 'jelly_shift_notes_v1';
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
            function canP(key) {
                if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
                return true;
            }
            function applySnPerms() {
                if (window.PbjPerms) window.PbjPerms.applyDom();
                var canView = canP('messages.shift_notes.view') || canP('messages.shift_notes.post');
                var canPost = canP('messages.shift_notes.post');
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

        function todayStr() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                return r && Array.isArray(r.notes) ? r : { notes: [] };
            } catch (e) { return { notes: [] }; }
        }

        function persist(showToast, toastMsg) {
            localStorage.setItem(KEY, JSON.stringify(state));
            if (shared) shared.push(state);
            if (showToast) J.toast(toastMsg || (isSweet ? 'Saved 💾' : 'Saved'));
        }

        function areaLabel(a) {
            if (a === 'foh') return 'FOH';
            if (a === 'boh') return 'BOH';
            return isSweet ? 'Whole house' : 'Whole house';
        }

        function setAuthor(name) {
            var n = name || userName || 'Team';
            J.fillAuthorSelect(document.getElementById('f-author-sel'), n, isSweet);
            var inp = document.getElementById('f-author');
            inp.style.display = 'none';
            inp.value = n;
        }

        function clearForm() {
            document.getElementById('f-edit-id').value = '';
            document.getElementById('f-date').value = todayStr();
            document.getElementById('f-shift').value = 'AM';
            document.getElementById('f-area').value = 'both';
            document.getElementById('f-title').value = '';
            document.getElementById('f-body').value = '';
            document.getElementById('edit-banner').classList.remove('show');
            document.getElementById('cancel-edit').style.display = 'none';
            document.getElementById('form-heading').textContent = isSweet ? 'Leave a handoff' : 'Leave a handoff';
            document.getElementById('submit-btn').textContent = isSweet ? 'Save handoff 📝' : 'Save handoff';
            setAuthor(userName);
        }

        function startEdit(n) {
            document.getElementById('f-edit-id').value = n.id;
            document.getElementById('f-date').value = n.date || todayStr();
            document.getElementById('f-shift').value = n.shift || 'AM';
            document.getElementById('f-area').value = n.area || 'both';
            document.getElementById('f-title').value = n.title || '';
            document.getElementById('f-body').value = n.body || '';
            document.getElementById('edit-banner').classList.add('show');
            document.getElementById('cancel-edit').style.display = '';
            document.getElementById('form-heading').textContent = isSweet ? 'Edit handoff' : 'Edit handoff';
            document.getElementById('submit-btn').textContent = isSweet ? 'Save changes 💾' : 'Save changes';
            setAuthor(n.author || userName);
            document.getElementById('f-title').focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function render() {
            var notes = state.notes.slice().sort(function (a, b) {
                if (a.date !== b.date) return String(b.date || '').localeCompare(String(a.date || ''));
                return (b.at || 0) - (a.at || 0);
            });
            var today = todayStr();
            if (filter === 'foh') notes = notes.filter(function (n) { return n.area === 'foh' || n.area === 'both'; });
            if (filter === 'boh') notes = notes.filter(function (n) { return n.area === 'boh' || n.area === 'both'; });
            if (filter === 'today') notes = notes.filter(function (n) { return n.date === today; });
            if (searchQ) {
                var q = searchQ.toLowerCase();
                notes = notes.filter(function (n) {
                    return [n.title, n.body, n.author, n.shift, n.area, n.date].join(' ').toLowerCase().indexOf(q) !== -1;
                });
            }

            var root = document.getElementById('feed');
            if (!notes.length) {
                root.innerHTML = '<div class="card empty">' + (isSweet
                    ? (searchQ ? 'No matches — try another search 🔍' : 'No handoffs yet — leave the first one above 📝')
                    : (searchQ ? 'No matching handoffs.' : 'No handoffs yet.')) + '</div>';
                return;
            }
            root.innerHTML = notes.map(function (n) {
                var when = '';
                try {
                    when = new Date(n.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                } catch (e) { when = n.date || ''; }
                return '<div class="note ' + J.esc(n.area || 'both') + '">' +
                    '<div class="note-top"><h3 class="note-title">' + J.esc(n.title || (isSweet ? 'Shift note' : 'Shift note')) + '</h3>' +
                    '<span class="badge">' + J.esc(areaLabel(n.area)) + '</span></div>' +
                    '<div class="meta">' + J.esc(when) + ' · ' + J.esc(n.shift || '') + ' · ' + J.esc(n.author || 'Team') +
                    (n.at ? ' · ' + J.esc(J.when(n.at)) : '') + '</div>' +
                    '<div class="body">' + J.esc(n.body) + '</div>' +
                    '<div class="post-actions">' +
                    '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-id="' + J.esc(n.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                    '<button type="button" class="btn btn-small btn-danger" data-act="del" data-id="' + J.esc(n.id) + '">' + (isSweet ? 'Remove' : 'Remove') + '</button>' +
                    '</div></div>';
            }).join('');
        }

        document.getElementById('note-form').addEventListener('submit', function (e) {
                if (!canP('messages.shift_notes.post')) { e.preventDefault(); return; }
            e.preventDefault();
            var body = document.getElementById('f-body').value.trim();
            var date = document.getElementById('f-date').value;
            if (!body || !date) return;
            var author = J.getAuthorValue('f-author-sel', 'f-author', userName || 'Team');
            var editId = document.getElementById('f-edit-id').value;
            if (editId) {
                var existing = state.notes.find(function (x) { return x.id === editId; });
                if (existing) {
                    existing.date = date;
                    existing.shift = document.getElementById('f-shift').value;
                    existing.area = document.getElementById('f-area').value;
                    existing.author = author;
                    existing.title = document.getElementById('f-title').value.trim();
                    existing.body = body;
                    existing.at = Date.now();
                }
                persist(true, isSweet ? 'Updated 💾' : 'Updated');
            } else {
                state.notes.push({
                    id: J.uid(),
                    date: date,
                    shift: document.getElementById('f-shift').value,
                    area: document.getElementById('f-area').value,
                    author: author,
                    title: document.getElementById('f-title').value.trim(),
                    body: body,
                    at: Date.now()
                });
                persist(true, isSweet ? 'Saved 💾' : 'Saved');
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
            var n = state.notes.find(function (x) { return x.id === btn.dataset.id; });
            if (!n) return;
            if (btn.dataset.act === 'edit') {
                startEdit(n);
                return;
            }
            if (btn.dataset.act === 'del') {
                if (!confirm(isSweet ? 'Remove this handoff?' : 'Remove this handoff?')) return;
                state.notes = state.notes.filter(function (x) { return x.id !== n.id; });
                if (document.getElementById('f-edit-id').value === n.id) clearForm();
                persist(true, isSweet ? 'Removed' : 'Removed');
                render();
            }
        });

        document.getElementById('f-date').value = todayStr();
        J.attachAuthorPicker('f-author-sel', 'f-author', userName, isSweet);

        shared = J.wireShared(SHARED_KEY, function () {
            return state;
        }, function (payload) {
            if (!payload || !Array.isArray(payload.notes)) return;
            state = { notes: payload.notes };
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
        });

        render();
            if (window.PbjPerms && window.PbjPerms.ready) window.PbjPerms.ready.then(applySnPerms);
            document.addEventListener('pbj-perms-ready', applySnPerms);
    })();
    </script>
</body>
</html>
