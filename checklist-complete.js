/**
 * FOH/BOH checklist & prep completion alerts for management.
 * - Publishers call PbjListComplete.maybeNotify(...) when a list finishes for the day.
 * - Listeners (managers with ops.receive_list_completion) poll shared state and get
 *   browser + in-app toasts. Personal prefs: localStorage pbj_whiskings_notifications_v1.listComplete
 */
(function (global) {
    'use strict';

    var SHARED_KEY = 'ops_list_completions_v1';
    var LOCAL_KEY = 'pbj_ops_list_completions_v1';
    var SEEN_KEY = 'pbj_ops_list_completions_seen_v1';
    var PREFS_KEY = 'pbj_whiskings_notifications_v1';
    var PERM = 'ops.receive_list_completion';
    var MAX_EVENTS = 80;

    function todayStr() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function uid() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    }
    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function loadLocal() {
        try {
            var r = JSON.parse(localStorage.getItem(LOCAL_KEY) || 'null');
            return r && Array.isArray(r.events) ? r : { events: [] };
        } catch (e) {
            return { events: [] };
        }
    }
    function saveLocal(s) {
        try {
            localStorage.setItem(LOCAL_KEY, JSON.stringify(s));
        } catch (e) {}
    }
    function prefsAllow() {
        try {
            var p = JSON.parse(localStorage.getItem(PREFS_KEY) || 'null');
            if (!p) return true;
            return p.listComplete !== false;
        } catch (e) {
            return true;
        }
    }
    function canReceive() {
        if (global.PbjPerms && global.PbjPerms.loaded) {
            return global.PbjPerms.can(PERM);
        }
        // Before perms load, don't spam; listener re-checks after ready
        return false;
    }
    function loadSeen() {
        try {
            var r = JSON.parse(localStorage.getItem(SEEN_KEY) || 'null');
            return r && typeof r === 'object' ? r : {};
        } catch (e) {
            return {};
        }
    }
    function markSeen(id) {
        var seen = loadSeen();
        seen[id] = Date.now();
        // prune old
        var cutoff = Date.now() - 7 * 24 * 60 * 60 * 1000;
        Object.keys(seen).forEach(function (k) {
            if (seen[k] < cutoff) delete seen[k];
        });
        try {
            localStorage.setItem(SEEN_KEY, JSON.stringify(seen));
        } catch (e) {}
    }

    var shared = null;
    var applyingRemote = false;
    var listenerStarted = false;
    var lastToastAt = 0;

    function ensureShared(onRemote) {
        if (!global.PbjSharedState) return null;
        if (shared) return shared;
        shared = new global.PbjSharedState({
            key: SHARED_KEY,
            pollMs: 8000,
            onRemote: function (payload) {
                if (applyingRemote) return;
                if (!payload || !Array.isArray(payload.events)) return;
                applyingRemote = true;
                saveLocal(payload);
                if (typeof onRemote === 'function') onRemote(payload);
                applyingRemote = false;
            }
        });
        shared.bootstrap(
            function () { return loadLocal(); },
            function (payload) {
                if (payload && Array.isArray(payload.events)) {
                    saveLocal(payload);
                    if (typeof onRemote === 'function') onRemote(payload);
                }
            }
        ).then(function () {
            if (shared.startPolling) shared.startPolling();
        });
        return shared;
    }

    /**
     * Publish a completion event (once per sourceKey+listId+date until reset).
     * opts: { sourceKey, listId, listTitle, pageTitle, href, completedBy, isSweet }
     * Returns true if a new event was published.
     */
    function maybeNotify(opts) {
        opts = opts || {};
        var date = todayStr();
        var sourceKey = String(opts.sourceKey || '');
        var listId = String(opts.listId || 'all');
        if (!sourceKey) return false;

        var dedupeKey = sourceKey + '::' + listId + '::' + date;
        var state = loadLocal();
        var exists = (state.events || []).some(function (ev) {
            return ev && ev.dedupeKey === dedupeKey;
        });
        if (exists) return false;

        var ev = {
            id: uid(),
            at: Date.now(),
            date: date,
            sourceKey: sourceKey,
            listId: listId,
            listTitle: opts.listTitle || 'List',
            pageTitle: opts.pageTitle || 'Checklist',
            href: opts.href || '',
            completedBy: opts.completedBy || 'Team',
            dedupeKey: dedupeKey
        };
        state.events = state.events || [];
        state.events.unshift(ev);
        if (state.events.length > MAX_EVENTS) {
            state.events = state.events.slice(0, MAX_EVENTS);
        }
        state.structureAt = Date.now();
        saveLocal(state);

        var sh = ensureShared(null);
        if (sh) {
            sh.queuePush ? sh.queuePush(state) : sh.push(state);
        }

        // Mark as seen for the person who completed it so they don't get their own alert
        markSeen(ev.id);
        return true;
    }

    /** Clear dedupe for a list so re-completing after uncheck can notify again. */
    function clearDedupe(sourceKey, listId) {
        var date = todayStr();
        var dedupeKey = String(sourceKey || '') + '::' + String(listId || 'all') + '::' + date;
        var state = loadLocal();
        var before = (state.events || []).length;
        state.events = (state.events || []).filter(function (ev) {
            return !ev || ev.dedupeKey !== dedupeKey;
        });
        if (state.events.length !== before) {
            state.structureAt = Date.now();
            saveLocal(state);
            var sh = ensureShared(null);
            if (sh) {
                sh.queuePush ? sh.queuePush(state) : sh.push(state);
            }
        }
    }

    function eventLabel(ev, isSweet) {
        var who = ev.completedBy || 'Team';
        var title = (ev.pageTitle || 'Checklist') +
            (ev.listTitle && ev.listTitle !== ev.pageTitle ? ' · ' + ev.listTitle : '');
        return isSweet
            ? (title + ' is done — ' + who + ' finished it 🎉')
            : (title + ' completed by ' + who);
    }

    function showInAppToast(message) {
        var now = Date.now();
        if (now - lastToastAt < 2500) return;
        lastToastAt = now;
        var el = document.getElementById('pbj-list-complete-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'pbj-list-complete-toast';
            el.setAttribute('role', 'status');
            el.style.cssText = 'position:fixed;bottom:100px;left:50%;transform:translateX(-50%) translateY(12px);' +
                'background:#1A2A44;color:#fff;padding:12px 18px;border-radius:999px;opacity:0;transition:all .25s;' +
                'z-index:2200;pointer-events:none;max-width:90vw;text-align:center;font-size:0.95rem;box-shadow:0 6px 20px rgba(0,0,0,.2);';
            document.body.appendChild(el);
        }
        el.textContent = message;
        el.classList.add('show');
        el.style.opacity = '1';
        el.style.transform = 'translateX(-50%) translateY(0)';
        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transform = 'translateX(-50%) translateY(12px)';
        }, 4200);
    }

    function browserNotify(title, body) {
        if (typeof Notification === 'undefined') return;
        if (Notification.permission === 'granted') {
            try {
                new Notification(title, { body: body, tag: 'pbj-list-complete' });
            } catch (e) {}
        } else if (Notification.permission === 'default') {
            try {
                Notification.requestPermission();
            } catch (e) {}
        }
    }

    function processIncoming(payload) {
        if (!canReceive() || !prefsAllow()) return;
        var seen = loadSeen();
        var events = (payload && payload.events) || loadLocal().events || [];
        var today = todayStr();
        var fresh = events.filter(function (ev) {
            return ev && ev.id && !seen[ev.id] && ev.date === today;
        });
        if (!fresh.length) return;
        // newest first already; notify top few
        fresh.slice(0, 3).forEach(function (ev) {
            markSeen(ev.id);
            var isSweet = document.documentElement.getAttribute('data-pbj-theme') === 'sweet' ||
                (document.body && /DreamingOutLoud|sweet/i.test(document.body.className || ''));
            var msg = eventLabel(ev, isSweet);
            showInAppToast(msg);
            browserNotify(ev.pageTitle || 'Checklist complete', msg);
        });
        // mark rest seen without toast flood
        fresh.slice(3).forEach(function (ev) { markSeen(ev.id); });
        try {
            document.dispatchEvent(new CustomEvent('pbj-list-complete', { detail: { events: fresh } }));
        } catch (e) {}
    }

    function startListener() {
        if (listenerStarted) return;
        listenerStarted = true;

        function go() {
            if (!canReceive()) return;
            ensureShared(processIncoming);
            processIncoming(loadLocal());
            // soft request permission once for managers
            if (prefsAllow() && typeof Notification !== 'undefined' && Notification.permission === 'default') {
                // don't force; wait until first real event or user opens settings
            }
        }

        if (global.PbjPerms && global.PbjPerms.ready) {
            global.PbjPerms.ready.then(go);
        } else {
            document.addEventListener('pbj-perms-ready', go);
            setTimeout(go, 1200);
        }
        // also re-process on focus
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) processIncoming(loadLocal());
        });
    }

    /** Today's completions for dashboard (permission-gated). */
    function todaysEvents() {
        if (!canReceive()) return [];
        var today = todayStr();
        return (loadLocal().events || []).filter(function (ev) {
            return ev && ev.date === today;
        });
    }

    global.PbjListComplete = {
        PERM: PERM,
        maybeNotify: maybeNotify,
        clearDedupe: clearDedupe,
        startListener: startListener,
        todaysEvents: todaysEvents,
        eventLabel: eventLabel,
        prefsAllow: prefsAllow,
        canReceive: canReceive
    };

    // Auto-start listener on pages that include bottom-nav (after DOM ready)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(startListener, 400);
        });
    } else {
        setTimeout(startListener, 400);
    }
})(window);
