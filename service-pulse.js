/**
 * Service Pulse — live FOH snapshot for BOH (The Heat) and other hubs.
 * Reads reservations/waitlist/to-gos localStorage + optional kitchen shared-state poll.
 */
(function (global) {
    'use strict';

    var STORAGE_KEY = 'pbj_showtime_reservations_waitlist_v2';
    var SHARED_KEY = 'showtime_res_wait_v1';

    function todayStr() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function loadLocal() {
        try {
            var raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
            if (!raw || typeof raw !== 'object') {
                return { reservations: [], waitlist: [], togos: [], quoteMins: 20 };
            }
            return {
                reservations: Array.isArray(raw.reservations) ? raw.reservations : [],
                waitlist: Array.isArray(raw.waitlist) ? raw.waitlist : [],
                togos: Array.isArray(raw.togos) ? raw.togos : [],
                quoteMins: raw.quoteMins != null ? parseInt(raw.quoteMins, 10) || 20 : 20
            };
        } catch (e) {
            return { reservations: [], waitlist: [], togos: [], quoteMins: 20 };
        }
    }

    function startOfTodayMs() {
        var d = new Date();
        d.setHours(0, 0, 0, 0);
        return d.getTime();
    }

    function computePulse(state) {
        state = state || loadLocal();
        var today = todayStr();
        var dayStart = startOfTodayMs();

        var waiting = (state.waitlist || []).filter(function (w) {
            return w && (w.status === 'waiting' || w.status === 'notified');
        });
        var waitParties = waiting.length;
        var waitCovers = waiting.reduce(function (s, w) {
            return s + (parseInt(w.party, 10) || 0);
        }, 0);
        var quote = state.quoteMins != null ? parseInt(state.quoteMins, 10) || 0 : 0;
        var onWait = waitParties > 0;

        var longestWait = 0;
        waiting.forEach(function (w) {
            var mins = Math.floor((Date.now() - (w.addedAt || Date.now())) / 60000);
            if (mins > longestWait) longestWait = mins;
        });

        var seatedWaitToday = (state.waitlist || []).filter(function (w) {
            return w && w.status === 'seated' && (w.updatedAt || w.addedAt || 0) >= dayStart;
        }).length;
        var seatedResToday = (state.reservations || []).filter(function (r) {
            return r && r.status === 'seated' && r.date === today;
        }).length;
        var joinedWaitToday = (state.waitlist || []).filter(function (w) {
            return w && (w.addedAt || 0) >= dayStart;
        }).length;

        var togos = state.togos || [];
        var activeTogo = togos.filter(function (t) {
            return t && (t.status === 'new' || t.status === 'making' || t.status === 'ready') &&
                (!t.date || t.date === today);
        });
        var readyTogo = activeTogo.filter(function (t) { return t.status === 'ready'; });
        var makingTogo = activeTogo.filter(function (t) { return t.status === 'making'; });
        var newTogo = activeTogo.filter(function (t) { return t.status === 'new'; });

        return {
            onWait: onWait,
            quoteMins: quote,
            waitParties: waitParties,
            waitCovers: waitCovers,
            longestWaitMins: longestWait,
            seatedToday: seatedWaitToday + seatedResToday,
            joinedWaitToday: joinedWaitToday,
            togoActive: activeTogo.length,
            togoReady: readyTogo.length,
            togoMaking: makingTogo.length,
            togoNew: newTogo.length,
            hasSignal: onWait || activeTogo.length > 0
        };
    }

    function paint(el, pulse, fun) {
        if (!el) return;
        if (!pulse || !pulse.hasSignal) {
            el.hidden = true;
            el.classList.remove('is-alert', 'is-ready');
            return;
        }
        el.hidden = false;
        el.classList.toggle('is-alert', !!pulse.onWait);
        el.classList.toggle('is-ready', pulse.togoReady > 0 && !pulse.onWait);

        var bits = [];
        if (pulse.onWait) {
            bits.push(fun
                ? ('⏳ <strong>On a wait</strong> · ~' + pulse.quoteMins + ' min quote')
                : ('⏳ <strong>On a wait</strong> · ~' + pulse.quoteMins + ' min'));
            bits.push(fun
                ? (pulse.waitParties + ' parties · ' + pulse.waitCovers + ' covers waiting')
                : (pulse.waitParties + ' parties · ' + pulse.waitCovers + ' covers'));
            if (pulse.longestWaitMins > 0) {
                bits.push(fun
                    ? ('longest wait ~' + pulse.longestWaitMins + 'm')
                    : ('longest ~' + pulse.longestWaitMins + 'm'));
            }
        } else {
            bits.push(fun ? '✨ <strong>No wait</strong> right now' : '✨ <strong>No wait</strong>');
        }

        if (pulse.joinedWaitToday || pulse.seatedToday) {
            bits.push(fun
                ? ('today: ' + pulse.joinedWaitToday + ' joined wait · ' + pulse.seatedToday + ' seated')
                : (pulse.joinedWaitToday + ' joined wait · ' + pulse.seatedToday + ' seated today'));
        }

        if (pulse.togoActive > 0) {
            var t = fun
                ? ('🛍️ <strong>To-gos</strong> ' + pulse.togoActive + ' active')
                : ('🛍️ <strong>To-gos</strong> ' + pulse.togoActive + ' active');
            var detail = [];
            if (pulse.togoReady) detail.push(pulse.togoReady + (fun ? ' ready' : ' ready'));
            if (pulse.togoMaking) detail.push(pulse.togoMaking + (fun ? ' making' : ' making'));
            if (pulse.togoNew) detail.push(pulse.togoNew + (fun ? ' new' : ' new'));
            if (detail.length) t += ' · ' + detail.join(' · ');
            bits.push(t);
        }

        var body = el.querySelector('.service-pulse-body');
        if (body) {
            body.innerHTML = bits.map(function (b) {
                return '<span class="service-pulse-bit">' + b + '</span>';
            }).join('<span class="service-pulse-sep">·</span>');
        }
        var link = el.querySelector('.service-pulse-link');
        if (link) {
            link.href = '/FOH/reservations';
        }
    }

    function init(options) {
        options = options || {};
        var fun = !!options.funNames;
        var el = document.getElementById(options.elId || 'service-pulse');
        if (!el) return null;

        var pulse = computePulse();
        paint(el, pulse, fun);

        var shared = null;
        function refreshFromLocal() {
            paint(el, computePulse(), fun);
        }

        if (global.PbjSharedState) {
            shared = new global.PbjSharedState({
                key: SHARED_KEY,
                pollMs: options.pollMs || 5000,
                onRemote: function (payload) {
                    if (!payload) return;
                    try {
                        var cur = loadLocal();
                        var merged = {
                            reservations: payload.reservations || cur.reservations,
                            waitlist: payload.waitlist || cur.waitlist,
                            togos: Array.isArray(payload.togos) ? payload.togos : cur.togos,
                            quoteMins: payload.quoteMins != null ? payload.quoteMins : cur.quoteMins,
                            structureAt: payload.structureAt || Date.now(),
                            deletedReservationIds: payload.deletedReservationIds || {},
                            deletedWaitlistIds: payload.deletedWaitlistIds || {},
                            deletedTogoIds: payload.deletedTogoIds || {}
                        };
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(Object.assign({}, cur, merged)));
                    } catch (e) {}
                    paint(el, computePulse(loadLocal()), fun);
                },
                onStatus: function () {}
            });
            shared.bootstrap(
                function () { return loadLocal(); },
                function (payload) {
                    if (payload) {
                        try {
                            var cur = loadLocal();
                            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                                reservations: payload.reservations || cur.reservations,
                                waitlist: payload.waitlist || cur.waitlist,
                                togos: Array.isArray(payload.togos) ? payload.togos : cur.togos,
                                quoteMins: payload.quoteMins != null ? payload.quoteMins : cur.quoteMins,
                                structureAt: payload.structureAt || Date.now(),
                                deletedReservationIds: payload.deletedReservationIds || {},
                                deletedWaitlistIds: payload.deletedWaitlistIds || {},
                                deletedTogoIds: payload.deletedTogoIds || {}
                            }));
                        } catch (e) {}
                    }
                    paint(el, computePulse(), fun);
                }
            ).then(function () {
                if (shared.startPolling) shared.startPolling();
            }).catch(function () {});
        }

        setInterval(refreshFromLocal, options.localPollMs || 8000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') refreshFromLocal();
        });
        window.addEventListener('storage', function (e) {
            if (e.key === STORAGE_KEY) refreshFromLocal();
        });

        return { refresh: refreshFromLocal, compute: computePulse };
    }

    global.PbjServicePulse = {
        init: init,
        compute: computePulse,
        loadLocal: loadLocal
    };
})(window);
