/**
 * Heat-style editable multi-list checklist with kitchen sync + print.
 * Config: { sharedKey, localKey, isSweet, defaults, hubHref, hubLabel, pageTitle,
 *           notesEnabled, userName, restoreLabel, listIdsForFullCheck?, href?,
 *           perms?: { add, uncheck, print, checkOff, edit, restore, handoff, photos } permission keys }
 * When notesEnabled: posts FOH handoffs into jelly shift-notes store (same as Messages).
 * Photos via PbjTaskPhotos; management completion alerts via PbjListComplete.
 */
(function (global) {
    'use strict';

    function mount(cfg) {
        var isSweet = !!cfg.isSweet;
        var KEY = cfg.localKey;
        var SHARED_KEY = cfg.sharedKey;
        var defaults = cfg.defaults || [];
        var requiredIds = cfg.requiredIds || defaults.map(function (d) { return d.id; });
        var P = cfg.perms || {};
        var pageHref = cfg.href || (typeof location !== 'undefined' ? location.pathname : '');
        var userName = cfg.userName || 'Team';

        function can(key) {
            if (!key) return true;
            if (global.PbjPerms && typeof global.PbjPerms.can === 'function' && global.PbjPerms.loaded) {
                return global.PbjPerms.can(key);
            }
            return true; // until perms load
        }
        function canPhotos() {
            if (P.photos) return can(P.photos);
            // Fallback: same as check-off when no explicit photo perm
            return can(P.checkOff);
        }
        function applyPermUi() {
            var map = [
                ['ecl-add-btn', P.add],
                ['ecl-uncheck-btn', P.uncheck],
                ['ecl-print-btn', P.print],
                ['ecl-restore-btn', P.restore]
            ];
            map.forEach(function (pair) {
                var el = document.getElementById(pair[0]);
                if (!el || !pair[1]) return;
                el.style.display = can(pair[1]) ? '' : 'none';
            });
            var hand = document.getElementById('ecl-handoff-card') || document.querySelector('.ecl-handoff, #ecl-handoff-form');
            if (hand && P.handoff) {
                var wrap = document.getElementById('ecl-handoff-card') || hand.closest('.card') || hand;
                if (wrap) wrap.style.display = can(P.handoff) ? '' : 'none';
            }
            // re-render list so edit/remove honor edit perm
            try { render(); } catch (e) {}
        }

        function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function totalItems(s) {
            var n = 0;
            (s && s.lists || []).forEach(function (l) { n += (l.items || []).length; });
            return n;
        }
        function hasFull(s) {
            if (!s || !s.lists || !s.lists.length) return false;
            var found = {};
            s.lists.forEach(function (l) {
                if ((l.items || []).length) found[l.id] = true;
            });
            return requiredIds.every(function (id) { return found[id]; });
        }
        function shells() {
            return {
                active: defaults[0] ? defaults[0].id : 'opening',
                structureAt: Date.now(),
                notes: '',
                notesAt: 0,
                lists: defaults.map(function (l) {
                    return {
                        id: l.id,
                        title: l.title,
                        icon: l.icon || '',
                        hint: l.hint || '',
                        items: (l.items || []).map(function (label, idx) {
                            return { id: l.id + '-' + idx, label: label, done: false, doneUpdatedAt: 0 };
                        })
                    };
                })
            };
        }
        function load() {
            try {
                var r = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (!r || !r.lists || !hasFull(r)) return shells();
                if (!r.active) r.active = r.lists[0].id;
                if (!r.structureAt) r.structureAt = Date.now();
                if (r.notes == null) r.notes = '';
                if (r.notesAt == null) r.notesAt = 0;
                r.lists.forEach(function (l) {
                    (l.items || []).forEach(function (it) {
                        if (!it.id) it.id = uid();
                        if (typeof it.done !== 'boolean') it.done = false;
                        if (it.doneUpdatedAt == null) it.doneUpdatedAt = 0;
                        if (it.photoAt == null) it.photoAt = 0;
                        if (it.assignedTo == null) it.assignedTo = '';
                    });
                });
                return r;
            } catch (e) { return shells(); }
        }

        function listIsComplete(list) {
            var items = (list && list.items) || [];
            return items.length > 0 && items.every(function (i) { return i.done; });
        }
        function notifyIfListComplete(list) {
            if (!list || !listIsComplete(list)) return;
            if (!global.PbjListComplete || !global.PbjListComplete.maybeNotify) return;
            global.PbjListComplete.maybeNotify({
                sourceKey: SHARED_KEY,
                listId: list.id,
                listTitle: ((list.icon ? list.icon + ' ' : '') + (list.title || 'List')).trim(),
                pageTitle: cfg.pageTitle || 'Checklist',
                href: pageHref,
                completedBy: userName,
                isSweet: isSweet
            });
        }
        function clearCompleteNotify(list) {
            if (!list || !global.PbjListComplete || !global.PbjListComplete.clearDedupe) return;
            global.PbjListComplete.clearDedupe(SHARED_KEY, list.id);
        }

        var state = load();
        var shared = null;
        var applyingRemote = false;
        var editId = null;
        var modal = document.getElementById('ecl-modal');
        var notesTimer = null;
        var assignFilter = 'all'; // all | mine | unassigned

        function loadTeamNames() {
            var names = [];
            var keys = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
            for (var i = 0; i < keys.length; i++) {
                try {
                    var r = JSON.parse(localStorage.getItem(keys[i]) || 'null');
                    if (r && Array.isArray(r.people)) {
                        r.people.forEach(function (p) {
                            if (p && p.status !== 'inactive' && (p.name || '').trim()) {
                                names.push(String(p.name).trim());
                            }
                        });
                        if (names.length) break;
                    }
                } catch (e) {}
            }
            if (userName && names.indexOf(userName) === -1) names.unshift(userName);
            return names.sort(function (a, b) { return a.localeCompare(b); });
        }
        function fillAssignDatalist() {
            var dl = document.getElementById('ecl-assign-list');
            if (!dl) return;
            dl.innerHTML = loadTeamNames().map(function (n) {
                return '<option value="' + esc(n) + '"></option>';
            }).join('');
        }
        function normName(s) {
            return String(s || '').trim().toLowerCase();
        }
        function isMine(item) {
            if (!item || !item.assignedTo) return false;
            return normName(item.assignedTo) === normName(userName);
        }

        function touchStructure() { state.structureAt = Date.now(); }
        function activeList() {
            return state.lists.find(function (l) { return l.id === state.active; }) || state.lists[0];
        }
        function save(toast, opts) {
            opts = opts || {};
            localStorage.setItem(KEY, JSON.stringify(state));
            if (toast) {
                var el = document.getElementById('ecl-toast');
                if (el) {
                    el.textContent = typeof toast === 'string' ? toast : (isSweet ? 'Saved 💾' : 'Saved');
                    el.classList.add('show');
                    setTimeout(function () { el.classList.remove('show'); }, 1100);
                }
            }
            if (!opts.skipRemote && shared) shared.queuePush(state);
        }
        function setSyncPill(info) {
            var pill = document.getElementById('ecl-sync-pill');
            var text = document.getElementById('ecl-sync-text');
            if (!pill || !text) return;
            pill.classList.remove('offline', 'syncing');
            if (info.kind === 'offline') pill.classList.add('offline');
            if (info.kind === 'syncing') pill.classList.add('syncing');
            text.textContent = info.text || '';
        }
        function applyRemote(payload) {
            if (!payload || !payload.lists) return;
            if (!hasFull(payload) && totalItems(payload) === 0) {
                state = shells();
                localStorage.setItem(KEY, JSON.stringify(state));
                render();
                if (shared) shared.push(state, { force: true });
                return;
            }
            applyingRemote = true;
            var keep = state.active;
            state = payload;
            if (keep && state.lists.some(function (l) { return l.id === keep; })) state.active = keep;
            if (!state.structureAt) state.structureAt = Date.now();
            if (state.notes == null) state.notes = '';
            localStorage.setItem(KEY, JSON.stringify(state));
            render();
            applyingRemote = false;
        }

        function renderTabs() {
            document.getElementById('ecl-tabs').innerHTML = state.lists.map(function (l) {
                return '<button type="button" class="tab' + (l.id === state.active ? ' active' : '') + '" data-tab="' + esc(l.id) + '">' +
                    esc((l.icon ? l.icon + ' ' : '') + l.title) + '</button>';
            }).join('');
        }
        function render() {
            renderTabs();
            var list = activeList();
            var items = list ? (list.items || []) : [];
            var done = items.filter(function (i) { return i.done; }).length;
            document.getElementById('ecl-progress-count').textContent = done + ' / ' + items.length;
            document.getElementById('ecl-progress-fill').style.width = items.length ? ((done / items.length) * 100) + '%' : '0%';
            document.getElementById('ecl-complete').classList.toggle('show', items.length > 0 && done === items.length);
            document.getElementById('ecl-progress-label').textContent = list
                ? ((list.icon ? list.icon + ' ' : '') + list.title + (list.hint ? ' · ' + list.hint : ''))
                : (isSweet ? 'Progress' : 'Progress');

            var root = document.getElementById('ecl-list-root');
            var filtered = items.filter(function (item) {
                if (assignFilter === 'mine') return isMine(item);
                if (assignFilter === 'unassigned') return !String(item.assignedTo || '').trim();
                return true;
            });
            // paint filter chips active state
            document.querySelectorAll('#ecl-assign-filters [data-assign-filter]').forEach(function (chip) {
                chip.classList.toggle('active', chip.getAttribute('data-assign-filter') === assignFilter);
            });
            if (!items.length) {
                root.innerHTML = '<div class="checklist"><div class="empty">' +
                    (isSweet ? 'No tasks yet — add one for this list ✨' : 'No tasks yet. Add one for this list.') +
                    '</div></div>';
            } else if (!filtered.length) {
                root.innerHTML = '<div class="checklist"><div class="empty">' +
                    (isSweet ? 'No tasks match this filter' : 'No tasks match this filter') +
                    '</div></div>';
            } else {
                var canEdit = can(P.edit);
                var canCheck = can(P.checkOff);
                var photosOk = canPhotos();
                var Photos = global.PbjTaskPhotos;
                root.innerHTML = '<div class="checklist">' + filtered.map(function (item) {
                    var photoHtml = Photos
                        ? Photos.renderPhotoUi(item, { canAttach: photosOk, isSweet: isSweet })
                        : '';
                    var assignHtml = '';
                    if (item.assignedTo) {
                        assignHtml = '<span class="assign-badge' + (isMine(item) ? ' mine' : '') + '">' +
                            (isSweet ? '👤 ' : '') + esc(item.assignedTo) +
                            (isMine(item) ? (isSweet ? ' · you' : ' · you') : '') +
                            '</span>';
                    }
                    return '<div class="check-item' + (item.done ? ' done' : '') + '">' +
                        '<button type="button" class="check-main" data-act="toggle" data-id="' + esc(item.id) + '"' +
                        (canCheck ? '' : ' disabled style="opacity:0.65;cursor:not-allowed;"') + '>' +
                        '<span class="checkbox" aria-hidden="true"></span>' +
                        '<span class="check-body"><div class="check-text">' + esc(item.label) + '</div>' +
                        assignHtml + '</span></button>' +
                        photoHtml +
                        (canEdit
                            ? ('<div class="item-actions">' +
                                '<button type="button" class="btn btn-small btn-ghost" data-act="edit" data-id="' + esc(item.id) + '">Edit</button>' +
                                '<button type="button" class="btn btn-small btn-danger" data-act="del" data-id="' + esc(item.id) + '">Remove</button>' +
                                '</div>')
                            : '') +
                        '</div>';
                }).join('') + '</div>';
            }

            if (cfg.notesEnabled && handoff) handoff.renderFeed();
        }

        // —— Shift handoffs (shared with Messages → Shift Notes) ——
        var HAND_LOCAL = 'pbj_jelly_shift_notes_v1';
        var HAND_SHARED = 'jelly_shift_notes_v1';
        var handoff = null;
        var handShared = null;

        function mountHandoff() {
            if (!cfg.notesEnabled || !document.getElementById('ecl-handoff-form')) return null;
            var userName = cfg.userName || 'Team';
            var J = global.Jelly;

            function todayStr() {
                var d = new Date();
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }
            function loadNotes() {
                try {
                    var r = JSON.parse(localStorage.getItem(HAND_LOCAL) || 'null');
                    return r && Array.isArray(r.notes) ? r : { notes: [] };
                } catch (e) { return { notes: [] }; }
            }
            function saveNotes(s, toastMsg) {
                localStorage.setItem(HAND_LOCAL, JSON.stringify(s));
                if (handShared) handShared.push(s);
                var st = document.getElementById('ecl-notes-status');
                if (st && toastMsg) {
                    st.textContent = toastMsg;
                    setTimeout(function () { st.textContent = ''; }, 1600);
                }
            }
            function when(ts) {
                if (J && J.when) return J.when(ts);
                try {
                    return new Date(ts).toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
                } catch (e) { return ''; }
            }
            function setAuthor() {
                if (J && J.attachAuthorPicker) {
                    J.attachAuthorPicker('ecl-h-author-sel', 'ecl-h-author', userName, isSweet);
                } else {
                    var sel = document.getElementById('ecl-h-author-sel');
                    if (sel) {
                        sel.innerHTML = '<option value="' + esc(userName) + '">' + esc(userName) + '</option>';
                    }
                }
            }
            function getAuthor() {
                if (J && J.getAuthorValue) return J.getAuthorValue('ecl-h-author-sel', 'ecl-h-author', userName);
                var sel = document.getElementById('ecl-h-author-sel');
                return (sel && sel.value) || userName || 'Team';
            }
            function renderFeed() {
                var root = document.getElementById('ecl-handoff-feed');
                if (!root) return;
                var s = loadNotes();
                var notes = (s.notes || []).filter(function (n) {
                    return n && (n.area === 'foh' || n.area === 'both' || !n.area);
                }).sort(function (a, b) {
                    if (a.date !== b.date) return String(b.date || '').localeCompare(String(a.date || ''));
                    return (b.at || 0) - (a.at || 0);
                }).slice(0, 8);
                if (!notes.length) {
                    root.innerHTML = '<div class="handoff-empty">' + (isSweet
                        ? 'No FOH handoffs yet — post the first one above 📝'
                        : 'No FOH handoffs yet. Post one above.') + '</div>';
                    return;
                }
                root.innerHTML = notes.map(function (n) {
                    var whenDay = '';
                    try {
                        whenDay = new Date(n.date + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                    } catch (e) { whenDay = n.date || ''; }
                    return '<div class="handoff-item" data-id="' + esc(n.id) + '">' +
                        '<div class="ht">' + esc(n.title || (isSweet ? 'Shift note' : 'Shift note')) + '</div>' +
                        '<div class="hm">' + esc(whenDay) + ' · ' + esc(n.shift || '') + ' · ' + esc(n.author || 'Team') +
                        (n.at ? ' · ' + esc(when(n.at)) : '') + '</div>' +
                        '<div class="hb">' + esc(n.body || '') + '</div>' +
                        '<button type="button" class="btn btn-small btn-danger" data-hand-del="' + esc(n.id) + '">' +
                        (isSweet ? 'Remove' : 'Remove') + '</button></div>';
                }).join('');
            }

            setAuthor();
            document.getElementById('ecl-handoff-form').addEventListener('submit', function (e) {
                e.preventDefault();
                var body = (document.getElementById('ecl-h-body').value || '').trim();
                if (!body) return;
                var s = loadNotes();
                s.notes.unshift({
                    id: uid(),
                    date: todayStr(),
                    shift: document.getElementById('ecl-h-shift').value || 'PM',
                    area: 'foh',
                    author: getAuthor(),
                    title: (document.getElementById('ecl-h-title').value || '').trim(),
                    body: body,
                    at: Date.now()
                });
                saveNotes(s, isSweet ? 'Handoff posted 💌' : 'Handoff posted');
                document.getElementById('ecl-h-body').value = '';
                document.getElementById('ecl-h-title').value = '';
                renderFeed();
                // soft toast on checklist toast if present
                save(isSweet ? 'Handoff posted 💌' : 'Handoff posted', { skipRemote: true });
            });
            document.getElementById('ecl-handoff-feed').addEventListener('click', function (e) {
                var btn = e.target.closest('[data-hand-del]');
                if (!btn) return;
                var id = btn.getAttribute('data-hand-del');
                if (!confirm(isSweet ? 'Remove this handoff?' : 'Remove this handoff?')) return;
                var s = loadNotes();
                s.notes = (s.notes || []).filter(function (n) { return n.id !== id; });
                saveNotes(s, isSweet ? 'Removed' : 'Removed');
                renderFeed();
            });

            if (global.PbjSharedState) {
                handShared = new global.PbjSharedState({
                    key: HAND_SHARED,
                    pollMs: 4000,
                    onRemote: function (payload) {
                        if (!payload || !Array.isArray(payload.notes)) return;
                        localStorage.setItem(HAND_LOCAL, JSON.stringify(payload));
                        renderFeed();
                    }
                });
                handShared.bootstrap(
                    function () { return loadNotes(); },
                    function (payload) {
                        if (payload && Array.isArray(payload.notes)) {
                            localStorage.setItem(HAND_LOCAL, JSON.stringify(payload));
                            renderFeed();
                        }
                    }
                ).then(function () {
                    if (handShared.startPolling) handShared.startPolling();
                });
            }

            renderFeed();
            return { renderFeed: renderFeed, loadNotes: loadNotes };
        }

        document.getElementById('ecl-tabs').addEventListener('click', function (e) {
            var tab = e.target.closest('[data-tab]');
            if (!tab) return;
            state.active = tab.dataset.tab;
            save(false, { skipRemote: true });
            render();
        });

        document.getElementById('ecl-list-root').addEventListener('click', function (e) {
            // Photo thumb links open full size — don't treat as button actions
            if (e.target.closest('a.task-photo-thumb')) return;
            var btn = e.target.closest('button[data-act]');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            var list = activeList();
            if (!list) return;
            var id = btn.dataset.id;
            var act = btn.dataset.act;
            if (act === 'toggle') {
                if (!can(P.checkOff)) return;
                var it = list.items.find(function (x) { return x.id === id; });
                if (it) {
                    var wasDone = !!it.done;
                    it.done = !it.done;
                    it.doneUpdatedAt = Date.now();
                    save(false);
                    render();
                    if (it.done && !wasDone) {
                        notifyIfListComplete(list);
                    } else if (!it.done && wasDone) {
                        clearCompleteNotify(list);
                    }
                }
                return;
            }
            if (act === 'photo') {
                if (!canPhotos() || !global.PbjTaskPhotos) return;
                var photoItem = list.items.find(function (x) { return x.id === id; });
                if (!photoItem) return;
                global.PbjTaskPhotos.openPicker(function (file) {
                    btn.classList.add('task-photo-busy');
                    global.PbjTaskPhotos.captureAndAttach(file).then(function (res) {
                        if (photoItem.photoUrl && photoItem.photoUrl !== res.photoUrl) {
                            global.PbjTaskPhotos.clearItemPhoto({ photoUrl: photoItem.photoUrl });
                        }
                        if (res.photoUrl) {
                            photoItem.photoUrl = res.photoUrl;
                            delete photoItem.photoDataUrl;
                            delete photoItem.photo;
                        } else if (res.photoDataUrl) {
                            photoItem.photoDataUrl = res.photoDataUrl;
                            delete photoItem.photoUrl;
                        }
                        photoItem.photoAt = res.photoAt || Date.now();
                        // Mark done when a completion photo is added
                        if (!photoItem.done) {
                            photoItem.done = true;
                            photoItem.doneUpdatedAt = Date.now();
                        }
                        save(isSweet ? 'Photo saved 📷' : 'Photo saved');
                        render();
                        notifyIfListComplete(list);
                    }).catch(function () {
                        save(isSweet ? 'Couldn’t save photo' : 'Could not save photo', { skipRemote: true });
                    }).then(function () {
                        btn.classList.remove('task-photo-busy');
                    });
                });
                return;
            }
            if (act === 'photo-del') {
                if (!canPhotos() || !global.PbjTaskPhotos) return;
                var delItem = list.items.find(function (x) { return x.id === id; });
                if (!delItem) return;
                if (!confirm(isSweet ? 'Remove this photo?' : 'Remove this photo?')) return;
                global.PbjTaskPhotos.clearItemPhoto(delItem).then(function () {
                    save(true);
                    render();
                });
                return;
            }
            if (act === 'edit') {
                if (!can(P.edit)) return;
                var item = list.items.find(function (x) { return x.id === id; });
                if (!item) return;
                editId = id;
                document.getElementById('ecl-modal-title').textContent = isSweet ? 'Edit task' : 'Edit task';
                document.getElementById('ecl-m-label').value = item.label;
                var assignEl = document.getElementById('ecl-m-assign');
                if (assignEl) assignEl.value = item.assignedTo || '';
                fillAssignDatalist();
                modal.classList.add('show');
                setTimeout(function () { document.getElementById('ecl-m-label').focus(); }, 40);
                return;
            }
            if (act === 'del') {
                if (!can(P.edit)) return;
                if (!confirm(isSweet ? 'Remove this task?' : 'Remove this task?')) return;
                var removing = list.items.find(function (x) { return x.id === id; });
                if (removing && global.PbjTaskPhotos) global.PbjTaskPhotos.clearItemPhoto(removing);
                list.items = list.items.filter(function (x) { return x.id !== id; });
                touchStructure();
                clearCompleteNotify(list);
                save(true);
                render();
            }
        });

        document.getElementById('ecl-add-btn').addEventListener('click', function () {
            if (!can(P.add)) return;
            editId = null;
            document.getElementById('ecl-modal-title').textContent = isSweet ? 'Add task' : 'Add task';
            document.getElementById('ecl-m-label').value = '';
            var assignEl = document.getElementById('ecl-m-assign');
            if (assignEl) assignEl.value = '';
            fillAssignDatalist();
            modal.classList.add('show');
            setTimeout(function () { document.getElementById('ecl-m-label').focus(); }, 40);
        });

        document.getElementById('ecl-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var label = document.getElementById('ecl-m-label').value.trim();
            if (!label) return;
            var assignTo = (document.getElementById('ecl-m-assign') && document.getElementById('ecl-m-assign').value.trim()) || '';
            var list = activeList();
            if (!list) return;
            if (editId) {
                var it = list.items.find(function (x) { return x.id === editId; });
                if (it) {
                    it.label = label;
                    it.assignedTo = assignTo;
                }
            } else {
                list.items.push({ id: uid(), label: label, done: false, doneUpdatedAt: 0, assignedTo: assignTo });
            }
            touchStructure();
            save(true);
            modal.classList.remove('show');
            render();
        });

        var assignFilters = document.getElementById('ecl-assign-filters');
        if (assignFilters) {
            assignFilters.addEventListener('click', function (e) {
                var chip = e.target.closest('[data-assign-filter]');
                if (!chip) return;
                assignFilter = chip.getAttribute('data-assign-filter') || 'all';
                render();
            });
        }
        document.getElementById('ecl-m-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });

        document.getElementById('ecl-uncheck-btn').addEventListener('click', function () {
            if (!can(P.uncheck)) return;
            var list = activeList();
            if (!list) return;
            var now = Date.now();
            list.items.forEach(function (i) { i.done = false; i.doneUpdatedAt = now; });
            clearCompleteNotify(list);
            save(true);
            render();
        });

        document.getElementById('ecl-restore-btn').addEventListener('click', function () {
            if (!can(P.restore)) return;
            if (!confirm(isSweet
                ? 'Restore starter lists for the whole team today? Custom tasks on these lists will be cleared.'
                : 'Restore starter lists for the team today?')) return;
            state = shells();
            save(isSweet ? 'Starter lists restored ✨' : 'Starter lists restored');
            render();
            if (shared) shared.push(state, { force: true });
        });

        document.getElementById('ecl-print-btn').addEventListener('click', function () {
            if (!can(P.print)) return;
            var d = new Date();
            document.getElementById('ecl-print-header').textContent =
                (cfg.pageTitle || 'Checklist') + ' · ' + d.toLocaleDateString() + ' · ' +
                d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            document.getElementById('ecl-print-all').innerHTML = state.lists.map(function (list) {
                var rows = (list.items || []).map(function (item) {
                    return '<label class="check-item"><span class="checkbox"></span>' +
                        '<span class="check-body"><div class="check-text">' + esc(item.label) +
                        (item.assignedTo ? ' <em>(' + esc(item.assignedTo) + ')</em>' : '') +
                        '</div></span></label>';
                }).join('');
                return '<div class="print-section-title">' + esc((list.icon ? list.icon + ' ' : '') + list.title) + '</div>' +
                    '<div class="checklist">' + (rows || '<div class="empty">—</div>') + '</div>';
            }).join('');
            if (cfg.notesEnabled && handoff) {
                var today = (function () {
                    var x = new Date();
                    return x.getFullYear() + '-' + String(x.getMonth() + 1).padStart(2, '0') + '-' + String(x.getDate()).padStart(2, '0');
                })();
                var notes = (handoff.loadNotes().notes || []).filter(function (n) {
                    return n && (n.area === 'foh' || n.area === 'both' || !n.area) && n.date === today;
                });
                if (notes.length) {
                    var blocks = notes.map(function (n) {
                        return '<div style="margin-bottom:8px;"><strong>' + esc(n.title || 'Handoff') + '</strong> · ' +
                            esc(n.shift || '') + ' · ' + esc(n.author || '') +
                            '<div style="white-space:pre-wrap;margin-top:4px;">' + esc(n.body || '') + '</div></div>';
                    }).join('');
                    document.getElementById('ecl-print-all').innerHTML +=
                        '<div class="print-section-title">' + (isSweet ? 'Today\'s FOH handoffs' : 'Today\'s FOH handoffs') + '</div>' +
                        '<div class="checklist" style="padding:12px;">' + blocks + '</div>';
                }
            }
            document.body.classList.add('print-all');
            setTimeout(function () {
                window.print();
                document.body.classList.remove('print-all');
            }, 60);
        });

        handoff = mountHandoff();
        render();
        applyPermUi();
        if (global.PbjPerms && global.PbjPerms.ready) {
            global.PbjPerms.ready.then(function () { applyPermUi(); });
        }
        document.addEventListener('pbj-perms-ready', function () { applyPermUi(); });

        if (global.PbjSharedState) {
            shared = new global.PbjSharedState({
                key: SHARED_KEY,
                pollMs: 3000,
                onStatus: setSyncPill,
                onRemote: function (payload) {
                    if (applyingRemote) return;
                    applyRemote(payload);
                }
            });
            shared.bootstrap(
                function () {
                    if (!hasFull(state)) state = shells();
                    return state;
                },
                function (payload) { applyRemote(payload); }
            ).then(function () {
                shared.startPolling();
            });
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only' : 'Local only' });
        }
    }

    global.PbjEditableChecklist = { mount: mount };
})(window);
