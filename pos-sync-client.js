/**
 * Client helpers: merge POS sales/labor into localStorage + call sync APIs.
 */
(function (global) {
    'use strict';

    var SALES_KEY = 'pbj_admin_sales_v2';
    var SALES_OLD = 'pbj_admin_sales_v1';
    var LABOR_KEY = 'pbj_admin_labor_v2';
    var LABOR_OLD = 'pbj_admin_labor_v1';
    var API = '/pos-api.php';

    function uid() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    }

    function loadSales() {
        try {
            var r = JSON.parse(localStorage.getItem(SALES_KEY) || 'null');
            if (r && Array.isArray(r.days)) return r;
            var old = JSON.parse(localStorage.getItem(SALES_OLD) || 'null');
            if (old && Array.isArray(old.days)) return { days: old.days, structureAt: Date.now() };
        } catch (e) {}
        return { days: [], structureAt: Date.now() };
    }

    function saveSales(state) {
        state.structureAt = Date.now();
        localStorage.setItem(SALES_KEY, JSON.stringify(state));
        try {
            localStorage.setItem(SALES_OLD, JSON.stringify({ days: state.days }));
        } catch (e) {}
    }

    function loadLabor() {
        try {
            var r = JSON.parse(localStorage.getItem(LABOR_KEY) || 'null');
            if (r && Array.isArray(r.days)) return r;
            var old = JSON.parse(localStorage.getItem(LABOR_OLD) || 'null');
            if (old && Array.isArray(old.days)) {
                return { days: old.days, structureAt: Date.now() };
            }
        } catch (e) {}
        return { days: [], structureAt: Date.now() };
    }

    function saveLabor(state) {
        state.structureAt = Date.now();
        localStorage.setItem(LABOR_KEY, JSON.stringify(state));
        try {
            localStorage.setItem(LABOR_OLD, JSON.stringify({
                days: (state.days || []).map(function (d) {
                    return {
                        id: d.id,
                        date: d.date,
                        hours: d.hours,
                        cost: d.cost,
                        foh: d.foh,
                        boh: d.boh,
                        notes: d.notes
                    };
                })
            }));
        } catch (e) {}
    }

    var COMPS_KEY = 'pbj_admin_comps_v1';
    var PMIX_KEY = 'pbj_admin_pmix_v1';

    function loadPmixState() {
        try {
            var r = JSON.parse(localStorage.getItem(PMIX_KEY) || 'null');
            if (r && Array.isArray(r.sessions)) return r;
        } catch (e) {}
        return { sessions: [] };
    }

    function savePmixState(state) {
        localStorage.setItem(PMIX_KEY, JSON.stringify(state));
    }

    /**
     * Map API PMIX lines into session item rows (with menu match when FoodCost helpers exist).
     */
    function mapPmixApiItems(apiItems) {
        var FC = global.PbjFoodCost;
        var out = [];
        (apiItems || []).forEach(function (it) {
            if (!it || !it.name) return;
            var qty = parseFloat(it.qty);
            if (isNaN(qty) || qty <= 0) return;
            var unitPrice = it.unitPrice != null && it.unitPrice !== '' ? parseFloat(it.unitPrice) : null;
            if (unitPrice != null && isNaN(unitPrice)) unitPrice = null;
            var menuIt = null;
            if (FC && FC.findMenuItemByName) {
                try { menuIt = FC.findMenuItemByName(it.name); } catch (e) {}
            }
            if (unitPrice == null && menuIt && menuIt.price != null) {
                var mp = parseFloat(menuIt.price);
                if (!isNaN(mp)) unitPrice = mp;
            }
            out.push({
                name: it.name,
                qty: Math.round(qty * 1000) / 1000,
                unitPrice: unitPrice,
                menuItemId: menuIt ? menuIt.id : '',
                source: 'pos',
                posProvider: it.provider || ''
            });
        });
        return out;
    }

    /**
     * Upsert a POS PMIX session (replace same sourceKey).
     * opts: { start, end, provider, label, sourceKey, byDay, splitDays }
     * @returns {{ session: object|null, sessionsCreated: number, itemCount: number }}
     */
    function mergePmixFromPos(apiItems, opts) {
        opts = opts || {};
        var items = mapPmixApiItems(apiItems);
        var state = loadPmixState();
        if (!state.sessions) state.sessions = [];
        var start = opts.start || '';
        var end = opts.end || start;
        var provider = opts.provider || 'pos';
        var sourceKey = opts.sourceKey || ('pos:' + provider + ':' + start + ':' + end);
        var created = 0;

        // Optional: one session per business day
        if (opts.splitDays && opts.byDay && typeof opts.byDay === 'object') {
            var days = Object.keys(opts.byDay).sort();
            days.forEach(function (day) {
                var dayItems = mapPmixApiItems(opts.byDay[day]);
                if (!dayItems.length) return;
                var sk = 'pos:' + provider + ':day:' + day;
                var existingIdx = -1;
                for (var i = 0; i < state.sessions.length; i++) {
                    if (state.sessions[i] && state.sessions[i].sourceKey === sk) {
                        existingIdx = i;
                        break;
                    }
                }
                var sess = {
                    id: existingIdx >= 0 ? state.sessions[existingIdx].id : uid(),
                    label: (opts.dayLabelPrefix || 'POS') + ' · ' + day,
                    date: day,
                    notes: 'Synced from ' + provider + ' item sales',
                    items: dayItems,
                    source: 'pos',
                    posProvider: provider,
                    sourceKey: sk,
                    start: day,
                    end: day,
                    updatedAt: Date.now()
                };
                if (existingIdx >= 0) state.sessions[existingIdx] = sess;
                else {
                    state.sessions.push(sess);
                    created++;
                }
            });
            savePmixState(state);
            return { session: null, sessionsCreated: created, itemCount: items.length, state: state };
        }

        if (!items.length) {
            return { session: null, sessionsCreated: 0, itemCount: 0, state: state };
        }

        var label = opts.label;
        if (!label) {
            if (start && end && start !== end) {
                label = 'POS mix · ' + start + ' → ' + end;
            } else {
                label = 'POS mix · ' + (start || 'today');
            }
            if (provider && provider !== 'pos') label = provider.charAt(0).toUpperCase() + provider.slice(1) + ' · ' + label.replace(/^POS mix · /, '');
        }

        var idx = -1;
        for (var j = 0; j < state.sessions.length; j++) {
            if (state.sessions[j] && state.sessions[j].sourceKey === sourceKey) {
                idx = j;
                break;
            }
        }
        var session = {
            id: idx >= 0 ? state.sessions[idx].id : uid(),
            label: label,
            date: start || end || '',
            notes: 'Synced from POS item sales' + (provider && provider !== 'pos' ? ' (' + provider + ')' : ''),
            items: items,
            source: 'pos',
            posProvider: provider,
            sourceKey: sourceKey,
            start: start,
            end: end,
            updatedAt: Date.now()
        };
        if (idx >= 0) {
            state.sessions[idx] = session;
        } else {
            state.sessions.push(session);
            created = 1;
        }
        savePmixState(state);
        return { session: session, sessionsCreated: created, itemCount: items.length, state: state };
    }

    /** Apply pmix payload from a sync response (soft — only when items present). */
    function applyPmixFromSync(res) {
        if (!res) return { itemCount: 0 };
        var items = res.pmixItems || res.items || null;
        // Prefer top-level; also merge per-provider from sync_all results
        if ((!items || !items.length) && res.results) {
            var lists = [];
            Object.keys(res.results).forEach(function (p) {
                var one = res.results[p];
                if (one && one.pmixItems && one.pmixItems.length) lists = lists.concat(one.pmixItems);
            });
            items = lists;
        }
        if (!items || !items.length) {
            return { itemCount: 0, pmixError: res.pmixError || null };
        }
        var provider = res.pmixProvider || (res.providers && res.providers.length === 1 ? res.providers[0] : 'pos');
        return mergePmixFromPos(items, {
            start: res.start || '',
            end: res.end || '',
            provider: provider,
            byDay: res.pmixByDay || res.byDay || null,
            splitDays: false
        });
    }

    function mergeCompEntries(apiEntries) {
        if (!apiEntries || !apiEntries.length) return { added: 0 };
        var state;
        try {
            state = JSON.parse(localStorage.getItem(COMPS_KEY) || 'null');
        } catch (e) { state = null; }
        if (!state || !Array.isArray(state.entries)) state = { entries: [] };
        var byExt = {};
        state.entries.forEach(function (e, i) {
            if (e && e.externalId) byExt[e.externalId] = i;
        });
        var added = 0;
        apiEntries.forEach(function (e) {
            if (!e || !e.date) return;
            var ext = e.externalId || e.id || '';
            var row = {
                id: e.id || uid(),
                date: e.date,
                type: e.type || 'void',
                amount: e.amount,
                reason: e.reason || 'POS refund',
                notes: e.notes || '',
                source: 'pos',
                posProvider: e.posProvider || 'square',
                externalId: ext,
                updatedAt: e.updatedAt || Date.now()
            };
            if (ext && byExt[ext] != null) {
                state.entries[byExt[ext]] = row;
            } else {
                state.entries.push(row);
                added++;
                if (ext) byExt[ext] = state.entries.length - 1;
            }
        });
        localStorage.setItem(COMPS_KEY, JSON.stringify(state));
        return { added: added };
    }

    /** Merge sales day rows from POS API (replace by date). */
    function mergeSalesDays(apiDays) {
        if (!apiDays || !apiDays.length) return { added: 0, replaced: 0 };
        var state = loadSales();
        var added = 0;
        var replaced = 0;
        apiDays.forEach(function (r) {
            if (!r || !r.date) return;
            var existing = state.days.find(function (d) { return d.date === r.date; });
            var id = existing ? existing.id : uid();
            if (existing) {
                state.days = state.days.filter(function (d) { return d.date !== r.date; });
                replaced++;
            } else {
                added++;
            }
            state.days.push({
                id: id,
                date: r.date,
                source: 'pos',
                posProvider: r.posProvider || '',
                gross: r.gross != null ? r.gross : '',
                net: r.net != null ? r.net : '',
                tax: r.tax != null ? r.tax : '',
                tips: r.tips != null ? r.tips : '',
                covers: r.covers != null ? r.covers : '',
                checks: r.checks != null ? r.checks : '',
                tenderCash: r.tenderCash != null ? r.tenderCash : '',
                tenderCard: r.tenderCard != null ? r.tenderCard : '',
                tenderOther: r.tenderOther != null ? r.tenderOther : '',
                labor: r.labor != null ? r.labor : '',
                notes: r.notes || '',
                updatedAt: Date.now()
            });
        });
        saveSales(state);
        return { added: added, replaced: replaced };
    }

    /** Merge labor day rows (punches + cost) into Labor Snapshot store. */
    function mergeLaborDays(apiDays) {
        if (!apiDays || !apiDays.length) return { added: 0, replaced: 0 };
        var state = loadLabor();
        var added = 0;
        var replaced = 0;
        apiDays.forEach(function (r) {
            if (!r || !r.date) return;
            var existing = state.days.find(function (d) { return d.date === r.date; });
            var id = existing ? existing.id : uid();
            if (existing) {
                state.days = state.days.filter(function (d) { return d.date !== r.date; });
                replaced++;
            } else {
                added++;
            }
            var entries = (r.entries || []).map(function (e) {
                var clockIn = e.actualStart || e.clockIn || '';
                var clockOut = e.actualEnd || e.clockOut || '';
                return {
                    id: e.id || uid(),
                    shiftId: e.shiftId || '',
                    personId: e.personId || e.externalId || '',
                    name: e.name || '',
                    role: e.role || '',
                    schedStart: e.schedStart || '',
                    schedEnd: e.schedEnd || '',
                    actualStart: clockIn,
                    actualEnd: clockOut,
                    clockIn: clockIn,
                    clockOut: clockOut,
                    breakMins: e.breakMins != null ? e.breakMins : 0,
                    timeSource: 'pos',
                    wageRate: e.wageRate != null ? e.wageRate : '',
                    laborCost: e.laborCost != null ? e.laborCost : '',
                    externalId: e.externalId || '',
                    punchId: e.punchId || '',
                    notes: e.notes || ''
                };
            });
            state.days.push({
                id: id,
                date: r.date,
                hours: r.hours != null ? r.hours : '',
                cost: r.cost != null ? r.cost : '',
                costSource: 'pos',
                foh: r.foh != null ? r.foh : '',
                boh: r.boh != null ? r.boh : '',
                notes: r.notes || 'Synced from POS',
                source: 'pos',
                posProvider: r.posProvider || 'square',
                entries: entries,
                updatedAt: Date.now()
            });
        });
        saveLabor(state);
        return { added: added, replaced: replaced };
    }

    /** Apply a full sync API response (days + laborDays + optional PMIX). */
    function applySyncResult(res) {
        var sales = mergeSalesDays(res && res.days);
        var labor = mergeLaborDays(res && res.laborDays);
        var comps = { added: 0 };
        // Square full sync may include refunds as comp entries
        if (res && res.compEntries) {
            comps = mergeCompEntries(res.compEntries);
        }
        // Also check per-provider results from sync_all
        if (res && res.results) {
            Object.keys(res.results).forEach(function (p) {
                var one = res.results[p];
                if (one && one.compEntries) {
                    var c = mergeCompEntries(one.compEntries);
                    comps.added += c.added || 0;
                }
            });
        }
        var pmix = applyPmixFromSync(res);
        try {
            if (global.PbjOpsNudges && global.PbjOpsNudges.pushDayLog) {
                global.PbjOpsNudges.pushDayLog('admin_sales_v1', loadSales());
                global.PbjOpsNudges.pushDayLog('admin_labor_v1', loadLabor());
            }
        } catch (e) {}
        try {
            document.dispatchEvent(new CustomEvent('pbj-pos-synced', { detail: { sales: sales, labor: labor, comps: comps, pmix: pmix, res: res } }));
        } catch (e) {}
        return { sales: sales, labor: labor, comps: comps, pmix: pmix };
    }

    function api(action, body) {
        var opts = { credentials: 'same-origin', cache: 'no-store' };
        if (body) {
            opts.method = 'POST';
            opts.headers = { 'Content-Type': 'application/json' };
            body.action = action;
            opts.body = JSON.stringify(body);
            return fetch(API, opts).then(function (r) { return r.json(); });
        }
        return fetch(API + '?action=' + encodeURIComponent(action), opts).then(function (r) { return r.json(); });
    }

    function status() {
        return api('status');
    }

    function syncProvider(provider, days) {
        return api('sync', { provider: provider, days: days || 7 }).then(function (res) {
            if (res && res.ok) applySyncResult(res);
            return res;
        });
    }

    function syncAll(days) {
        return api('sync_all', { days: days || 7 }).then(function (res) {
            if (res && res.ok) applySyncResult(res);
            return res;
        });
    }

    /**
     * Pull item-level PMIX only (Square / Toast / both).
     * opts: { days, provider, splitDays }
     */
    function syncPmix(opts) {
        opts = opts || {};
        var body = { days: opts.days || 7 };
        if (opts.provider) body.provider = opts.provider;
        return api('sync_pmix', body).then(function (res) {
            if (res && res.ok) {
                var merged = mergePmixFromPos(res.items || [], {
                    start: res.start || '',
                    end: res.end || '',
                    provider: res.provider || (res.providers && res.providers.join('+')) || 'pos',
                    byDay: res.byDay || null,
                    splitDays: !!opts.splitDays
                });
                res.merged = merged;
            }
            return res;
        });
    }

    function formatSyncAgo(isoOrMysql) {
        if (!isoOrMysql) return '';
        var s = String(isoOrMysql).replace(' ', 'T');
        if (!/Z|[+-]\d{2}:?\d{2}$/.test(s)) s += 'Z';
        var t = Date.parse(s);
        if (isNaN(t)) {
            // treat as local MySQL datetime
            t = Date.parse(String(isoOrMysql).replace(' ', 'T'));
        }
        if (isNaN(t)) return String(isoOrMysql);
        var mins = Math.round((Date.now() - t) / 60000);
        if (mins < 1) return 'just now';
        if (mins < 60) return mins + 'm ago';
        var hrs = Math.round(mins / 60);
        if (hrs < 48) return hrs + 'h ago';
        var days = Math.round(hrs / 24);
        return days + 'd ago';
    }

    global.PbjPosSync = {
        mergeSalesDays: mergeSalesDays,
        mergeLaborDays: mergeLaborDays,
        mergePmixFromPos: mergePmixFromPos,
        applyPmixFromSync: applyPmixFromSync,
        applySyncResult: applySyncResult,
        status: status,
        syncProvider: syncProvider,
        syncAll: syncAll,
        syncPmix: syncPmix,
        formatSyncAgo: formatSyncAgo,
        api: api
    };
})(window);
