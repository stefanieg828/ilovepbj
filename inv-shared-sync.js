/**
 * House-wide inventory sync for Product Setup + Count Stock.
 * - Ingredients master: evergreen shared key inv_ingredients_v1 (date 2000-01-01)
 * - Count sessions: daily key inv_count_sessions_v1 (business date)
 * LocalStorage remains the offline source of truth; server is multi-device merge.
 */
(function (global) {
    'use strict';

    var ING_KEY = 'pbj_heat_ingredients_v1';
    var COUNT_KEY = 'pbj_inv_count_sessions_v1';
    var ING_SHARED = 'inv_ingredients_v1';
    var COUNT_SHARED = 'inv_count_sessions_v1';
    var EVERGREEN = '2000-01-01';

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

    function touchItems(master) {
        if (!master || typeof master !== 'object') master = { items: {} };
        if (!master.items || typeof master.items !== 'object') master.items = {};
        master.structureAt = Date.now();
        return master;
    }

    function setSyncPill(elOrId, info) {
        var pill = typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;
        if (!pill) return;
        var text = pill.querySelector('.sync-text') || pill;
        pill.classList.remove('offline', 'syncing');
        if (info && info.kind === 'offline') pill.classList.add('offline');
        if (info && info.kind === 'syncing') pill.classList.add('syncing');
        if (text && text !== pill) text.textContent = (info && info.text) || '';
        else if (pill.dataset) pill.dataset.status = (info && info.text) || '';
        if (pill.querySelector && pill.querySelector('#inv-sync-text')) {
            pill.querySelector('#inv-sync-text').textContent = (info && info.text) || '';
        }
    }

    /**
     * @param {object} opts
     * @param {function} opts.getMaster - () => {items:{}}
     * @param {function} opts.setMaster - (m) => void  (apply + localStorage)
     * @param {function} [opts.onMasterRemote]
     * @param {function} [opts.getSessions]
     * @param {function} [opts.setSessions]
     * @param {function} [opts.onSessionsRemote]
     * @param {string|HTMLElement} [opts.statusEl]
     * @param {boolean} [opts.syncSessions]
     */
    function wire(opts) {
        opts = opts || {};
        if (!global.PbjSharedState) {
            setSyncPill(opts.statusEl, { kind: 'offline', text: 'Local only' });
            return { pushMaster: function () {}, pushSessions: function () {} };
        }

        var ingShared = null;
        var sessShared = null;
        var applying = false;

        function status(info) {
            setSyncPill(opts.statusEl, info);
        }

        ingShared = new global.PbjSharedState({
            key: ING_SHARED,
            date: EVERGREEN,
            pollMs: 8000,
            onStatus: status,
            onRemote: function (payload) {
                if (applying || !payload || typeof payload.items !== 'object') return;
                applying = true;
                try {
                    var next = {
                        items: payload.items || {},
                        structureAt: payload.structureAt || Date.now()
                    };
                    if (typeof opts.setMaster === 'function') opts.setMaster(next, { remote: true });
                    else {
                        saveJson(ING_KEY, next);
                    }
                    if (typeof opts.onMasterRemote === 'function') opts.onMasterRemote(next);
                } finally {
                    applying = false;
                }
            }
        });

        ingShared.bootstrap(
            function () {
                var m = typeof opts.getMaster === 'function' ? opts.getMaster() : loadJson(ING_KEY, { items: {} });
                return touchItems(m);
            },
            function (payload) {
                if (!payload || typeof payload.items !== 'object') return;
                applying = true;
                try {
                    var next = { items: payload.items || {}, structureAt: payload.structureAt || Date.now() };
                    if (typeof opts.setMaster === 'function') opts.setMaster(next, { remote: true });
                    else saveJson(ING_KEY, next);
                    if (typeof opts.onMasterRemote === 'function') opts.onMasterRemote(next);
                } finally {
                    applying = false;
                }
            }
        ).then(function () {
            ingShared.startPolling();
        });

        if (opts.syncSessions) {
            sessShared = new global.PbjSharedState({
                key: COUNT_SHARED,
                pollMs: 10000,
                onStatus: function () { /* keep ingredient status primary */ },
                onRemote: function (payload) {
                    if (applying || !payload || !Array.isArray(payload.sessions)) return;
                    applying = true;
                    try {
                        var next = { sessions: payload.sessions };
                        if (typeof opts.setSessions === 'function') opts.setSessions(next, { remote: true });
                        else saveJson(COUNT_KEY, next);
                        if (typeof opts.onSessionsRemote === 'function') opts.onSessionsRemote(next);
                    } finally {
                        applying = false;
                    }
                }
            });
            sessShared.bootstrap(
                function () {
                    return typeof opts.getSessions === 'function'
                        ? opts.getSessions()
                        : loadJson(COUNT_KEY, { sessions: [] });
                },
                function (payload) {
                    if (!payload || !Array.isArray(payload.sessions)) return;
                    applying = true;
                    try {
                        var next = { sessions: payload.sessions };
                        if (typeof opts.setSessions === 'function') opts.setSessions(next, { remote: true });
                        else saveJson(COUNT_KEY, next);
                        if (typeof opts.onSessionsRemote === 'function') opts.onSessionsRemote(next);
                    } finally {
                        applying = false;
                    }
                }
            ).then(function () {
                sessShared.startPolling();
            });
        }

        return {
            pushMaster: function (master) {
                if (!ingShared || applying) return;
                var m = touchItems(master || (opts.getMaster && opts.getMaster()) || loadJson(ING_KEY, { items: {} }));
                // stamp each item lightly if missing
                Object.keys(m.items || {}).forEach(function (k) {
                    if (m.items[k] && !m.items[k].updatedAt) {
                        m.items[k].updatedAt = Date.now();
                    }
                });
                ingShared.queuePush(m);
            },
            pushSessions: function (sessionsState) {
                if (!sessShared || applying) return;
                var s = sessionsState || (opts.getSessions && opts.getSessions()) || loadJson(COUNT_KEY, { sessions: [] });
                s.structureAt = Date.now();
                sessShared.queuePush(s);
            },
            markItemTouched: function (item) {
                if (item && typeof item === 'object') item.updatedAt = Date.now();
                return item;
            }
        };
    }

    global.PbjInvSync = {
        wire: wire,
        ING_KEY: ING_KEY,
        COUNT_KEY: COUNT_KEY,
        touchItems: touchItems
    };
})(window);
