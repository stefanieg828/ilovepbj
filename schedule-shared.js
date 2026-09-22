/**
 * Schedules — house sync (evergreen) + shift trades.
 * Shared key: admin_schedules_v1 @ 2000-01-01
 * Local mirror: pbj_admin_schedules_v1  (labor / cash / nudges keep reading this)
 */
(function (global) {
    'use strict';

    var LS_KEY = 'pbj_admin_schedules_v1';
    var SHARED_KEY = 'admin_schedules_v1';
    var EVERGREEN = '2000-01-01';
    var ME_LS = 'pbj_schedule_me_id';
    var TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
    var DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    var PREFS_URL = '/user-prefs-api.php';

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

    function esc(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function mondayOf(d) {
        var x = new Date(d);
        x.setHours(12, 0, 0, 0);
        var day = x.getDay();
        var diff = day === 0 ? -6 : 1 - day;
        x.setDate(x.getDate() + diff);
        return x;
    }

    function weekKey(d) {
        var m = mondayOf(d);
        return m.getFullYear() + '-' + String(m.getMonth() + 1).padStart(2, '0') + '-' + String(m.getDate()).padStart(2, '0');
    }

    function defaultAlerts() {
        return {
            alertBefore: '30',
            alertCustomMinutes: 45,
            channels: { inApp: true, browser: true, sms: false }
        };
    }

    function defaultSettings() {
        return { requireApproval: true };
    }

    function normalizeAlerts(a) {
        var d = defaultAlerts();
        if (!a || typeof a !== 'object') return d;
        d.alertBefore = a.alertBefore != null ? String(a.alertBefore) : '30';
        var c = parseInt(a.alertCustomMinutes, 10);
        d.alertCustomMinutes = isNaN(c) ? 45 : c;
        var ch = a.channels && typeof a.channels === 'object' ? a.channels : {};
        d.channels = {
            inApp: ch.inApp !== false,
            browser: ch.browser !== false,
            sms: !!ch.sms
        };
        return d;
    }

    function normalizeShift(s) {
        if (!s || typeof s !== 'object') return null;
        var out = {
            id: String(s.id || uid()),
            day: String(s.day || ''),
            personId: s.personId != null ? String(s.personId) : '',
            name: String(s.name || '').trim(),
            role: String(s.role || '').trim(),
            start: String(s.start || ''),
            end: String(s.end || ''),
            notes: String(s.notes || '').trim()
        };
        if (s.open) out.open = true;
        if (s.originalPersonId) out.originalPersonId = String(s.originalPersonId);
        if (s.originalName) out.originalName = String(s.originalName);
        return out;
    }

    function normalizeRequest(r) {
        if (!r || typeof r !== 'object') return null;
        var type = String(r.type || '');
        if (type !== 'giveup' && type !== 'swap' && type !== 'claim') return null;
        var status = String(r.status || 'open');
        if (['open', 'pending_approval', 'approved', 'denied', 'cancelled'].indexOf(status) === -1) {
            status = 'open';
        }
        return {
            id: String(r.id || uid()),
            type: type,
            weekKey: String(r.weekKey || ''),
            shiftId: String(r.shiftId || ''),
            fromPersonId: r.fromPersonId != null ? String(r.fromPersonId) : '',
            fromName: String(r.fromName || '').trim(),
            toPersonId: r.toPersonId != null ? String(r.toPersonId) : '',
            toName: String(r.toName || '').trim(),
            targetShiftId: r.targetShiftId != null ? String(r.targetShiftId) : '',
            status: status,
            note: String(r.note || '').trim(),
            createdAt: typeof r.createdAt === 'number' ? r.createdAt : Date.now(),
            resolvedAt: typeof r.resolvedAt === 'number' ? r.resolvedAt : null,
            resolvedBy: r.resolvedBy != null ? String(r.resolvedBy) : ''
        };
    }

    function normalizePosted(posted) {
        var out = {};
        if (!posted || typeof posted !== 'object') return out;
        Object.keys(posted).forEach(function (wk) {
            var row = posted[wk];
            if (!row || typeof row !== 'object') return;
            out[String(wk)] = {
                at: typeof row.at === 'number' ? row.at : Date.now(),
                by: String(row.by || '').trim()
            };
        });
        return out;
    }

    /**
     * Legacy payloads (no posted key) treat any week that already has shifts as posted
     * so existing houses are not blank on My Schedule.
     */
    function migratePosted(state, hadPostedKey) {
        if (hadPostedKey) return state;
        Object.keys(state.weeks || {}).forEach(function (wk) {
            var list = state.weeks[wk];
            if (Array.isArray(list) && list.length && !state.posted[wk]) {
                state.posted[wk] = { at: Date.now(), by: 'migration' };
            }
        });
        return state;
    }

    function normalize(state) {
        var hadPostedKey = !!(state && state.posted && typeof state.posted === 'object');
        if (!state || typeof state !== 'object') state = {};
        var weeks = {};
        var rawWeeks = state.weeks && typeof state.weeks === 'object' ? state.weeks : {};
        Object.keys(rawWeeks).forEach(function (wk) {
            var list = rawWeeks[wk];
            if (!Array.isArray(list)) return;
            weeks[String(wk)] = list.map(normalizeShift).filter(Boolean);
        });
        var settings = defaultSettings();
        if (state.settings && typeof state.settings === 'object') {
            settings.requireApproval = state.settings.requireApproval !== false;
        }
        var requests = Array.isArray(state.requests)
            ? state.requests.map(normalizeRequest).filter(Boolean)
            : [];
        var next = {
            weeks: weeks,
            alerts: normalizeAlerts(state.alerts),
            posted: normalizePosted(state.posted),
            settings: settings,
            requests: requests,
            structureAt: typeof state.structureAt === 'number' ? state.structureAt : Date.now()
        };
        return migratePosted(next, hadPostedKey);
    }

    function loadLocal() {
        return normalize(loadJson(LS_KEY, { weeks: {}, alerts: defaultAlerts() }));
    }

    function saveLocal(state) {
        var next = normalize(state);
        next.structureAt = Date.now();
        saveJson(LS_KEY, next);
        return next;
    }

    function personRoles(p) {
        var roles = [];
        if (p && Array.isArray(p.roles) && p.roles.length) {
            p.roles.forEach(function (r) {
                if (r && typeof r === 'object' && r.role) r = String(r.role || '').trim();
                else r = String(r || '').trim();
                if (r && roles.indexOf(r) === -1) roles.push(r);
            });
        }
        if (p && p.role) {
            var single = String(p.role).trim();
            if (single && roles.indexOf(single) === -1) roles.unshift(single);
        }
        if (!roles.length) roles = ['Other'];
        return roles;
    }

    function loadTeam() {
        for (var i = 0; i < TEAM_KEYS.length; i++) {
            try {
                var r = JSON.parse(localStorage.getItem(TEAM_KEYS[i]) || 'null');
                if (r && Array.isArray(r.people) && r.people.length) {
                    return r.people
                        .filter(function (p) { return p && p.status !== 'inactive' && (p.name || '').trim(); })
                        .map(function (p) {
                            return {
                                id: p.id || '',
                                name: String(p.name || '').trim(),
                                phone: String(p.phone || '').trim(),
                                roles: personRoles(p)
                            };
                        })
                        .sort(function (a, b) { return a.name.localeCompare(b.name); });
                }
            } catch (e) {}
        }
        return [];
    }

    function findPerson(team, value) {
        if (!value) return null;
        team = team || loadTeam();
        return team.find(function (p) { return p.id === value || p.name === value; }) || null;
    }

    function findShift(state, weekKeyVal, shiftId) {
        var list = (state.weeks && state.weeks[weekKeyVal]) || [];
        for (var i = 0; i < list.length; i++) {
            if (list[i] && list[i].id === shiftId) return list[i];
        }
        return null;
    }

    function findRequest(state, id) {
        var list = state.requests || [];
        for (var i = 0; i < list.length; i++) {
            if (list[i] && list[i].id === id) return list[i];
        }
        return null;
    }

    function isWeekPosted(state, weekKeyVal) {
        return !!(state.posted && state.posted[weekKeyVal]);
    }

    function requireApproval(state) {
        return !state.settings || state.settings.requireApproval !== false;
    }

    function hasActiveRequest(state, shiftId, type) {
        return (state.requests || []).some(function (r) {
            if (!r || (r.status !== 'open' && r.status !== 'pending_approval')) return false;
            if (type && r.type !== type) return false;
            return r.shiftId === shiftId || r.targetShiftId === shiftId;
        });
    }

    function hoursBetween(start, end) {
        if (!start || !end) return 0;
        var a = String(start).split(':').map(Number);
        var b = String(end).split(':').map(Number);
        if (a.length < 2 || b.length < 2 || isNaN(a[0]) || isNaN(b[0])) return 0;
        var mins = (b[0] * 60 + (b[1] || 0)) - (a[0] * 60 + (b[1] || 0));
        if (mins < 0) mins += 24 * 60;
        return Math.round((mins / 60) * 10) / 10;
    }

    function fmtTime(t) {
        if (!t) return '';
        var p = String(t).split(':');
        var h = parseInt(p[0], 10);
        var m = p[1] || '00';
        var ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ap;
    }

    function setSyncPill(elOrId, info) {
        var pill = typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;
        if (!pill) return;
        var text = pill.querySelector('.sync-text') || document.getElementById('sync-pill-text') || pill;
        pill.classList.remove('offline', 'syncing');
        if (info && info.kind === 'offline') pill.classList.add('offline');
        if (info && info.kind === 'syncing') pill.classList.add('syncing');
        if (text && text !== pill) text.textContent = (info && info.text) || '';
        else if (text === pill) pill.textContent = (info && info.text) || pill.textContent;
    }

    function postWeek(state, weekKeyVal, by) {
        state = normalize(state);
        if (!state.weeks[weekKeyVal]) state.weeks[weekKeyVal] = [];
        state.posted[weekKeyVal] = { at: Date.now(), by: String(by || '').trim() || 'manager' };
        return state;
    }

    function unpostWeek(state, weekKeyVal) {
        state = normalize(state);
        if (state.posted && state.posted[weekKeyVal]) delete state.posted[weekKeyVal];
        return state;
    }

    function applyGiveUp(state, weekKeyVal, shiftId) {
        var shift = findShift(state, weekKeyVal, shiftId);
        if (!shift) return false;
        if (!shift.originalPersonId) {
            shift.originalPersonId = shift.personId || '';
            shift.originalName = shift.name || '';
        }
        shift.open = true;
        shift.personId = '';
        shift.name = 'Open';
        return true;
    }

    function applyClaim(state, weekKeyVal, shiftId, person) {
        var shift = findShift(state, weekKeyVal, shiftId);
        if (!shift) return false;
        shift.open = false;
        shift.personId = (person && person.id) ? String(person.id) : '';
        shift.name = (person && person.name) ? String(person.name) : 'Claimed';
        return true;
    }

    function applySwap(state, weekKeyVal, shiftId, targetShiftId) {
        var a = findShift(state, weekKeyVal, shiftId);
        var b = findShift(state, weekKeyVal, targetShiftId);
        if (!a || !b) return false;
        var tmpId = a.personId;
        var tmpName = a.name;
        var tmpOpen = !!a.open;
        a.personId = b.personId;
        a.name = b.name;
        a.open = !!b.open;
        b.personId = tmpId;
        b.name = tmpName;
        b.open = tmpOpen;
        if (a.open) { a.personId = ''; a.name = 'Open'; }
        if (b.open) { b.personId = ''; b.name = 'Open'; }
        return true;
    }

    function makeRequest(fields) {
        return normalizeRequest({
            id: uid(),
            type: fields.type,
            weekKey: fields.weekKey,
            shiftId: fields.shiftId,
            fromPersonId: fields.fromPersonId || '',
            fromName: fields.fromName || '',
            toPersonId: fields.toPersonId || '',
            toName: fields.toName || '',
            targetShiftId: fields.targetShiftId || '',
            status: fields.status || 'open',
            note: fields.note || '',
            createdAt: Date.now(),
            resolvedAt: fields.resolvedAt || null,
            resolvedBy: fields.resolvedBy || ''
        });
    }

    function fail(code, message) {
        return { ok: false, error: code, message: message || code };
    }

    function ok(state, extra) {
        var out = { ok: true, state: state };
        if (extra) {
            Object.keys(extra).forEach(function (k) { out[k] = extra[k]; });
        }
        return out;
    }

    function proposeGiveUp(state, opts) {
        opts = opts || {};
        state = normalize(state);
        if (!isWeekPosted(state, opts.weekKey)) return fail('not_posted', 'Week is not posted yet.');
        var shift = findShift(state, opts.weekKey, opts.shiftId);
        if (!shift || shift.open) return fail('bad_shift', 'That shift is not yours to give up.');
        if (opts.fromPersonId && shift.personId && shift.personId !== opts.fromPersonId && shift.name !== opts.fromName) {
            return fail('not_yours', 'That shift is not assigned to you.');
        }
        if (hasActiveRequest(state, opts.shiftId, null)) return fail('duplicate', 'A request is already open for this shift.');
        var req = makeRequest({
            type: 'giveup',
            weekKey: opts.weekKey,
            shiftId: opts.shiftId,
            fromPersonId: opts.fromPersonId || shift.personId,
            fromName: opts.fromName || shift.name,
            note: opts.note || ''
        });
        if (requireApproval(state)) {
            req.status = 'pending_approval';
            state.requests.push(req);
            return ok(state, { request: req, applied: false });
        }
        applyGiveUp(state, opts.weekKey, opts.shiftId);
        req.status = 'approved';
        req.resolvedAt = Date.now();
        req.resolvedBy = opts.actorName || opts.fromName || 'staff';
        state.requests.push(req);
        return ok(state, { request: req, applied: true });
    }

    function proposeClaim(state, opts) {
        opts = opts || {};
        state = normalize(state);
        if (!isWeekPosted(state, opts.weekKey)) return fail('not_posted', 'Week is not posted yet.');
        var shift = findShift(state, opts.weekKey, opts.shiftId);
        if (!shift || !shift.open) return fail('not_open', 'That shift is not open.');
        if (!opts.fromPersonId && !opts.fromName) return fail('who', 'Pick who you are first.');
        if (hasActiveRequest(state, opts.shiftId, 'claim')) return fail('duplicate', 'Someone already asked to claim this shift.');
        var req = makeRequest({
            type: 'claim',
            weekKey: opts.weekKey,
            shiftId: opts.shiftId,
            fromPersonId: opts.fromPersonId || '',
            fromName: opts.fromName || '',
            note: opts.note || ''
        });
        if (requireApproval(state)) {
            req.status = 'pending_approval';
            state.requests.push(req);
            return ok(state, { request: req, applied: false });
        }
        applyClaim(state, opts.weekKey, opts.shiftId, { id: opts.fromPersonId, name: opts.fromName });
        req.status = 'approved';
        req.resolvedAt = Date.now();
        req.resolvedBy = opts.actorName || opts.fromName || 'staff';
        state.requests.push(req);
        return ok(state, { request: req, applied: true });
    }

    function proposeSwap(state, opts) {
        opts = opts || {};
        state = normalize(state);
        if (!isWeekPosted(state, opts.weekKey)) return fail('not_posted', 'Week is not posted yet.');
        var mine = findShift(state, opts.weekKey, opts.shiftId);
        var theirs = findShift(state, opts.weekKey, opts.targetShiftId);
        if (!mine || !theirs) return fail('bad_shift', 'Pick two real shifts.');
        if (mine.id === theirs.id) return fail('same', 'Pick a different shift to swap with.');
        if (mine.open || theirs.open) return fail('open_shift', 'Can’t swap an open shift — claim it instead.');
        if (opts.fromPersonId && mine.personId && mine.personId !== opts.fromPersonId) {
            return fail('not_yours', 'That shift is not assigned to you.');
        }
        if (hasActiveRequest(state, opts.shiftId, null) || hasActiveRequest(state, opts.targetShiftId, null)) {
            return fail('duplicate', 'A request is already open for one of these shifts.');
        }
        var req = makeRequest({
            type: 'swap',
            weekKey: opts.weekKey,
            shiftId: opts.shiftId,
            targetShiftId: opts.targetShiftId,
            fromPersonId: opts.fromPersonId || mine.personId,
            fromName: opts.fromName || mine.name,
            toPersonId: theirs.personId || '',
            toName: theirs.name || '',
            note: opts.note || '',
            status: 'open'
        });
        state.requests.push(req);
        return ok(state, { request: req, applied: false, waitingOn: req.toName });
    }

    function acceptSwap(state, requestId, actorName) {
        state = normalize(state);
        var req = findRequest(state, requestId);
        if (!req || req.type !== 'swap' || req.status !== 'open') return fail('bad_request', 'That swap is not waiting.');
        if (requireApproval(state)) {
            req.status = 'pending_approval';
            return ok(state, { request: req, applied: false });
        }
        if (!applySwap(state, req.weekKey, req.shiftId, req.targetShiftId)) {
            return fail('apply', 'Could not swap those shifts.');
        }
        req.status = 'approved';
        req.resolvedAt = Date.now();
        req.resolvedBy = actorName || req.toName || 'staff';
        return ok(state, { request: req, applied: true });
    }

    function cancelRequest(state, requestId, actorName) {
        state = normalize(state);
        var req = findRequest(state, requestId);
        if (!req) return fail('bad_request', 'Request not found.');
        if (req.status !== 'open' && req.status !== 'pending_approval') {
            return fail('closed', 'That request is already closed.');
        }
        req.status = 'cancelled';
        req.resolvedAt = Date.now();
        req.resolvedBy = actorName || 'staff';
        return ok(state, { request: req });
    }

    function approveRequest(state, requestId, actorName, team) {
        state = normalize(state);
        var req = findRequest(state, requestId);
        if (!req || req.status !== 'pending_approval') return fail('bad_request', 'Nothing to approve.');
        var applied = false;
        if (req.type === 'giveup') {
            applied = applyGiveUp(state, req.weekKey, req.shiftId);
        } else if (req.type === 'claim') {
            var person = findPerson(team, req.fromPersonId) || { id: req.fromPersonId, name: req.fromName };
            applied = applyClaim(state, req.weekKey, req.shiftId, person);
        } else if (req.type === 'swap') {
            applied = applySwap(state, req.weekKey, req.shiftId, req.targetShiftId);
        }
        if (!applied) return fail('apply', 'Could not apply that trade.');
        req.status = 'approved';
        req.resolvedAt = Date.now();
        req.resolvedBy = actorName || 'manager';
        return ok(state, { request: req, applied: true });
    }

    function denyRequest(state, requestId, actorName) {
        state = normalize(state);
        var req = findRequest(state, requestId);
        if (!req || req.status !== 'pending_approval') return fail('bad_request', 'Nothing to deny.');
        req.status = 'denied';
        req.resolvedAt = Date.now();
        req.resolvedBy = actorName || 'manager';
        return ok(state, { request: req });
    }

    function setRequireApproval(state, on) {
        state = normalize(state);
        state.settings.requireApproval = !!on;
        return state;
    }

    function pendingRequests(state) {
        return (state.requests || []).filter(function (r) { return r.status === 'pending_approval'; })
            .sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
    }

    function openSwapsForPerson(state, personId) {
        if (!personId) return [];
        return (state.requests || []).filter(function (r) {
            return r.type === 'swap' && r.status === 'open' && r.toPersonId === personId;
        });
    }

    function myRequests(state, personId) {
        if (!personId) return [];
        return (state.requests || []).filter(function (r) {
            return r.fromPersonId === personId || r.toPersonId === personId;
        }).sort(function (a, b) { return (b.createdAt || 0) - (a.createdAt || 0); });
    }

    function loadMeId() {
        try { return String(localStorage.getItem(ME_LS) || ''); } catch (e) { return ''; }
    }

    function saveMeId(id) {
        try {
            if (id) localStorage.setItem(ME_LS, String(id));
            else localStorage.removeItem(ME_LS);
        } catch (e) {}
    }

    function pullMeFromPrefs(cb) {
        fetch(PREFS_URL, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.ok && data.prefs && data.prefs.schedule_me_id) {
                    var rec = data.prefs.schedule_me_id;
                    var id = typeof rec === 'string' ? rec : (rec && rec.id ? String(rec.id) : '');
                    if (id) saveMeId(id);
                }
                if (typeof cb === 'function') cb(loadMeId());
            })
            .catch(function () {
                if (typeof cb === 'function') cb(loadMeId());
            });
    }

    function pushMeToPrefs(id) {
        saveMeId(id);
        fetch(PREFS_URL, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                schedule_me_id: { id: String(id || ''), updatedAt: Date.now() }
            })
        }).catch(function () {});
    }

    function requestLabel(req, isSweet) {
        if (!req) return '';
        var t = req.type;
        if (t === 'giveup') {
            return isSweet
                ? ((req.fromName || 'Someone') + ' wants to give up a shift')
                : ((req.fromName || 'Someone') + ' — give up');
        }
        if (t === 'claim') {
            return isSweet
                ? ((req.fromName || 'Someone') + ' wants to claim an open shift')
                : ((req.fromName || 'Someone') + ' — claim');
        }
        return isSweet
            ? ((req.fromName || 'Someone') + ' ↔ ' + (req.toName || 'teammate') + ' swap')
            : ((req.fromName || 'Someone') + ' ↔ ' + (req.toName || 'teammate'));
    }

    function statusLabel(status, isSweet) {
        var map = {
            open: isSweet ? 'Waiting on teammate' : 'Waiting',
            pending_approval: isSweet ? 'Needs manager OK' : 'Pending approval',
            approved: isSweet ? 'Approved' : 'Approved',
            denied: isSweet ? 'Denied' : 'Denied',
            cancelled: isSweet ? 'Cancelled' : 'Cancelled'
        };
        return map[status] || status;
    }

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

    global.PbjSchedules = {
        LS_KEY: LS_KEY,
        SHARED_KEY: SHARED_KEY,
        DAYS: DAYS,
        uid: uid,
        esc: esc,
        mondayOf: mondayOf,
        weekKey: weekKey,
        hoursBetween: hoursBetween,
        fmtTime: fmtTime,
        normalize: normalize,
        loadLocal: loadLocal,
        saveLocal: saveLocal,
        loadTeam: loadTeam,
        findPerson: findPerson,
        findShift: findShift,
        findRequest: findRequest,
        isWeekPosted: isWeekPosted,
        requireApproval: requireApproval,
        hasActiveRequest: hasActiveRequest,
        postWeek: postWeek,
        unpostWeek: unpostWeek,
        setRequireApproval: setRequireApproval,
        proposeGiveUp: proposeGiveUp,
        proposeClaim: proposeClaim,
        proposeSwap: proposeSwap,
        acceptSwap: acceptSwap,
        cancelRequest: cancelRequest,
        approveRequest: approveRequest,
        denyRequest: denyRequest,
        pendingRequests: pendingRequests,
        openSwapsForPerson: openSwapsForPerson,
        myRequests: myRequests,
        loadMeId: loadMeId,
        saveMeId: saveMeId,
        pullMeFromPrefs: pullMeFromPrefs,
        pushMeToPrefs: pushMeToPrefs,
        requestLabel: requestLabel,
        statusLabel: statusLabel,
        wire: wire,
        setSyncPill: setSyncPill
    };
})(window);
