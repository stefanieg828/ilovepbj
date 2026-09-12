/**
 * 86 board — house sync (evergreen, like inventory ingredients).
 * Shared key: 86_board_v1 @ 2000-01-01
 * Local mirror: pbj_86_board_v1
 */
(function (global) {
    'use strict';

    var LS_KEY = 'pbj_86_board_v1';
    var SHARED_KEY = '86_board_v1';
    var EVERGREEN = '2000-01-01';
    var MENU_KEY = 'pbj_menu_v1';

    function loadJson(key, fallback) {
        try {
            var r = JSON.parse(localStorage.getItem(key) || 'null');
            return r != null ? r : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function saveJson(key, val) {
        try {
            localStorage.setItem(key, JSON.stringify(val));
        } catch (e) {}
    }

    function uid() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    }

    function coerceList(state) {
        // Prefer board[] (avoids shared-state inventory items{} merge). Migrate legacy items[].
        if (state && Array.isArray(state.board)) return state.board;
        if (state && Array.isArray(state.items)) return state.items;
        if (state && state.items && typeof state.items === 'object' && !Array.isArray(state.items)) {
            return Object.keys(state.items).map(function (k) {
                var row = state.items[k];
                if (!row || typeof row !== 'object') return null;
                if (!row.id) row = Object.assign({ id: k }, row);
                return row;
            }).filter(Boolean);
        }
        return [];
    }

    function normalize(state) {
        if (!state || typeof state !== 'object') state = {};
        var list = coerceList(state);
        var board = list
            .filter(function (it) {
                return it && typeof it === 'object' && String(it.name || '').trim() !== '';
            })
            .map(function (it) {
                var status = it.status === 'low' ? 'low' : '86';
                return {
                    id: String(it.id || uid()),
                    name: String(it.name || '').trim(),
                    menuId: it.menuId != null && it.menuId !== '' ? String(it.menuId) : null,
                    status: status,
                    note: String(it.note || '').trim(),
                    by: String(it.by || '').trim(),
                    at: typeof it.at === 'number' ? it.at : Date.now(),
                    updatedAt: typeof it.updatedAt === 'number' ? it.updatedAt : Date.now()
                };
            });
        return {
            board: board,
            structureAt: typeof state.structureAt === 'number' ? state.structureAt : Date.now()
        };
    }

    function loadLocal() {
        return normalize(loadJson(LS_KEY, { board: [], structureAt: Date.now() }));
    }

    function saveLocal(state) {
        var next = normalize(state);
        next.structureAt = Date.now();
        saveJson(LS_KEY, next);
        return next;
    }

    function loadMenu() {
        var raw = loadJson(MENU_KEY, null);
        var list = [];
        if (Array.isArray(raw)) list = raw;
        else if (raw && Array.isArray(raw.items)) list = raw.items;
        else if (raw && typeof raw === 'object') {
            Object.keys(raw).forEach(function (k) {
                var v = raw[k];
                if (v && typeof v === 'object' && v.name) list.push(v);
            });
        }
        return list
            .filter(function (it) {
                return it && String(it.name || '').trim() !== '';
            })
            .map(function (it) {
                return {
                    id: String(it.id || ''),
                    name: String(it.name || '').trim(),
                    category: String(it.category || 'other'),
                    price: it.price
                };
            })
            .sort(function (a, b) {
                return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
            });
    }

    function setSyncPill(elOrId, info) {
        var pill = typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;
        if (!pill) return;
        var text = pill.querySelector('.sync-text') || document.getElementById('sync-pill-text') || pill;
        pill.classList.remove('offline', 'syncing');
        if (info && info.kind === 'offline') pill.classList.add('offline');
        if (info && info.kind === 'syncing') pill.classList.add('syncing');
        if (text && text !== pill) text.textContent = (info && info.text) || '';
    }

    /**
     * @param {object} opts
     * @param {function} opts.getState
     * @param {function} opts.setState - (state, meta?) => void
     * @param {function} [opts.onRemote]
     * @param {string|HTMLElement} [opts.statusEl]
     * @param {number} [opts.pollMs]
     */
    function wire(opts) {
        opts = opts || {};
        if (!global.PbjSharedState) {
            setSyncPill(opts.statusEl, { kind: 'offline', text: 'Local only' });
            return { push: function () {} };
        }

        var applying = false;
        var shared = new global.PbjSharedState({
            key: SHARED_KEY,
            date: EVERGREEN,
            pollMs: opts.pollMs || 5000,
            onStatus: function (info) {
                setSyncPill(opts.statusEl, info);
            },
            onRemote: function (payload) {
                if (applying || !payload || typeof payload !== 'object') return;
                applying = true;
                try {
                    var next = normalize(payload);
                    if (typeof opts.setState === 'function') opts.setState(next, { remote: true });
                    else saveLocal(next);
                    if (typeof opts.onRemote === 'function') opts.onRemote(next);
                } finally {
                    applying = false;
                }
            }
        });

        shared
            .bootstrap(
                function () {
                    return typeof opts.getState === 'function' ? normalize(opts.getState()) : loadLocal();
                },
                function (payload) {
                    if (!payload) return;
                    applying = true;
                    try {
                        var next = normalize(payload);
                        if (typeof opts.setState === 'function') opts.setState(next, { remote: true });
                        else saveLocal(next);
                        if (typeof opts.onRemote === 'function') opts.onRemote(next);
                    } finally {
                        applying = false;
                    }
                }
            )
            .then(function () {
                shared.startPolling();
            });

        return {
            push: function (state) {
                if (!shared || applying) return;
                var next = normalize(state || (opts.getState && opts.getState()) || loadLocal());
                next.structureAt = Date.now();
                shared.queuePush(next);
            }
        };
    }

    function formatWhen(ts) {
        if (!ts) return '';
        try {
            var d = new Date(ts);
            if (isNaN(d.getTime())) return '';
            return d.toLocaleString(undefined, {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        } catch (e) {
            return '';
        }
    }

    global.Pbj86Board = {
        LS_KEY: LS_KEY,
        SHARED_KEY: SHARED_KEY,
        MENU_KEY: MENU_KEY,
        uid: uid,
        normalize: normalize,
        loadLocal: loadLocal,
        saveLocal: saveLocal,
        loadMenu: loadMenu,
        wire: wire,
        setSyncPill: setSyncPill,
        formatWhen: formatWhen
    };
})(window);
