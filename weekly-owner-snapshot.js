/**
 * Weekly Owner Snapshot — client aggregator.
 * Real local data only; empty sections when sources are missing.
 */
(function (global) {
    'use strict';

    function pad(n) { return String(n).padStart(2, '0'); }
    function ymd(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function parseYmd(s) {
        if (!s || !/^\d{4}-\d{2}-\d{2}$/.test(s)) return null;
        var p = s.split('-');
        return new Date(+p[0], +p[1] - 1, +p[2]);
    }
    function addDays(d, n) {
        var x = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        x.setDate(x.getDate() + n);
        return x;
    }
    function loadJson(key, fallback) {
        try {
            var r = JSON.parse(localStorage.getItem(key) || 'null');
            return r != null ? r : fallback;
        } catch (e) {
            return fallback;
        }
    }
    function money(n) {
        if (n == null || isNaN(n)) return '—';
        return '$' + (Math.round(n * 100) / 100).toLocaleString(undefined, {
            minimumFractionDigits: 0, maximumFractionDigits: 0
        });
    }
    function money2(n) {
        if (n == null || isNaN(n)) return '—';
        return '$' + (Math.round(n * 100) / 100).toLocaleString(undefined, {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }
    function pct(n) {
        if (n == null || isNaN(n)) return '—';
        return (Math.round(n * 10) / 10) + '%';
    }

    function lastWeekRange(ref) {
        ref = ref || new Date();
        var day = ref.getDay();
        var mondayThis = addDays(ref, day === 0 ? -6 : 1 - day);
        var start = addDays(mondayThis, -7);
        var end = addDays(start, 6);
        return { start: ymd(start), end: ymd(end), label: 'Last week (Mon–Sun)' };
    }

    function rolling7Range(ref) {
        ref = ref || new Date();
        var end = addDays(ref, -1);
        var start = addDays(end, -6);
        return { start: ymd(start), end: ymd(end), label: 'Last 7 days' };
    }

    function datesInRange(startStr, endStr) {
        var a = parseYmd(startStr), b = parseYmd(endStr);
        if (!a || !b || a > b) return [];
        var out = [];
        for (var d = a; d <= b; d = addDays(d, 1)) out.push(ymd(d));
        return out;
    }

    function netOf(day) {
        if (!day) return null;
        var n = parseFloat(day.net);
        if (!isNaN(n)) return n;
        n = parseFloat(day.gross);
        return isNaN(n) ? null : n;
    }

    function salesDays() {
        var state = loadJson('pbj_admin_sales_v2', null) || loadJson('pbj_admin_sales_v1', { days: [] });
        return (state && state.days) || [];
    }
    function laborDays() {
        var labor = loadJson('pbj_admin_labor_v2', null) || loadJson('pbj_admin_labor_v1', { days: [] });
        return (labor && labor.days) || [];
    }

    function laborAmountForDay(row, salesDay) {
        if (row) {
            var amount = parseFloat(
                row.cost != null && row.cost !== '' ? row.cost
                    : (row.totalLabor != null ? row.totalLabor : row.labor$)
            );
            if (isNaN(amount) && Array.isArray(row.entries)) {
                amount = 0;
                var any = false;
                row.entries.forEach(function (e) {
                    var lc = parseFloat(e.laborCost);
                    if (!isNaN(lc)) { amount += lc; any = true; return; }
                    var w = parseFloat(e.wageRate != null ? e.wageRate : e.wage);
                    var h = parseFloat(e.hours);
                    if (!isNaN(w) && !isNaN(h)) { amount += w * h; any = true; }
                });
                if (!any) amount = NaN;
            }
            if (!isNaN(amount)) return amount;
        }
        if (salesDay && salesDay.labor != null && salesDay.labor !== '') {
            var a = parseFloat(salesDay.labor);
            return isNaN(a) ? null : a;
        }
        return null;
    }

    function laborHoursForDay(row) {
        if (!row) return null;
        var hours = row.hours != null && row.hours !== '' ? parseFloat(row.hours) : null;
        if (hours != null && !isNaN(hours)) return hours;
        if (Array.isArray(row.entries)) {
            var sum = 0, any = false;
            row.entries.forEach(function (e) {
                var h = parseFloat(e.hours);
                if (!isNaN(h)) { sum += h; any = true; }
            });
            return any ? sum : null;
        }
        return null;
    }

    function salesLabor(start, end) {
        var set = {};
        datesInRange(start, end).forEach(function (d) { set[d] = true; });
        var sMap = {}, lMap = {};
        salesDays().forEach(function (d) { if (d && d.date && set[d.date]) sMap[d.date] = d; });
        laborDays().forEach(function (d) { if (d && d.date && set[d.date]) lMap[d.date] = d; });

        var totalSales = 0, salesN = 0, totalLabor = 0, laborN = 0, totalHours = 0, hoursN = 0;
        datesInRange(start, end).forEach(function (date) {
            var s = sMap[date], l = lMap[date];
            var net = netOf(s);
            var labor$ = laborAmountForDay(l, s);
            var hrs = laborHoursForDay(l);
            if (net != null) { totalSales += net; salesN++; }
            if (labor$ != null) { totalLabor += labor$; laborN++; }
            if (hrs != null) { totalHours += hrs; hoursN++; }
        });
        return {
            hasSales: salesN > 0,
            hasLabor: laborN > 0,
            salesDays: salesN,
            laborDays: laborN,
            totalSales: salesN ? Math.round(totalSales * 100) / 100 : null,
            totalLabor: laborN ? Math.round(totalLabor * 100) / 100 : null,
            totalHours: hoursN ? Math.round(totalHours * 10) / 10 : null,
            laborPct: (salesN && totalSales > 0 && laborN)
                ? Math.round((totalLabor / totalSales) * 1000) / 10
                : null
        };
    }

    function dishes() {
        var FC = global.PbjFoodCost;
        if (!FC || typeof FC.loadMenu !== 'function') {
            return { worst: [], best: [], rows: [], empty: true };
        }
        var menu = FC.loadMenu();
        var items = (menu && menu.items) || [];
        var master = typeof FC.loadMaster === 'function' ? FC.loadMaster() : null;
        var rows = [];
        items.forEach(function (it) {
            if (!it) return;
            var sell = parseFloat(it.price);
            if (isNaN(sell) || sell <= 0) return;
            var plate = null;
            try { plate = FC.plateCostForMenuId(it.id, master); } catch (e) { plate = null; }
            if (!plate || plate.per == null || isNaN(plate.per)) return;
            var fc = Math.round((plate.per / sell) * 1000) / 10;
            rows.push({
                id: it.id,
                name: it.name || 'Item',
                sell: sell,
                plate: Math.round(plate.per * 100) / 100,
                fc: fc,
                band: fc > 35 ? 'high' : (fc >= 30 ? 'watch' : 'ok')
            });
        });
        rows.sort(function (a, b) { return b.fc - a.fc; });
        return {
            rows: rows,
            worst: rows.slice(0, 5),
            best: rows.slice().sort(function (a, b) { return a.fc - b.fc; }).slice(0, 5),
            empty: !rows.length
        };
    }

    function eightySix() {
        var raw = (global.Pbj86Board && global.Pbj86Board.loadLocal)
            ? global.Pbj86Board.loadLocal()
            : loadJson('pbj_86_board_v1', { board: [] });
        var board = [];
        if (raw && Array.isArray(raw.board)) board = raw.board;
        else if (raw && Array.isArray(raw.items)) board = raw.items;
        var items = board.filter(function (it) {
            return it && String(it.name || '').trim() !== '';
        });
        var count86 = 0, countLow = 0;
        items.forEach(function (it) {
            if (it.status === 'low') countLow++;
            else count86++;
        });
        return { items: items, count86: count86, countLow: countLow, total: items.length };
    }

    function checklists(start, end) {
        var set = {};
        datesInRange(start, end).forEach(function (d) { set[d] = true; });
        var local = loadJson('pbj_ops_list_completions_v1', { events: [] });
        var events = (local && local.events) || [];
        var inRange = events.filter(function (ev) {
            if (!ev) return false;
            if (ev.date && set[ev.date]) return true;
            if (ev.at) {
                var d = new Date(ev.at);
                if (!isNaN(d.getTime()) && set[ymd(d)]) return true;
            }
            return false;
        });
        var titles = {};
        inRange.forEach(function (ev) {
            var t = ev.listTitle || ev.pageTitle || 'List';
            titles[t] = (titles[t] || 0) + 1;
        });
        var byDay = {};
        inRange.forEach(function (ev) {
            var d = ev.date;
            if (!d && ev.at) {
                var dt = new Date(ev.at);
                d = isNaN(dt.getTime()) ? '' : ymd(dt);
            }
            if (!d) return;
            byDay[d] = (byDay[d] || 0) + 1;
        });
        var topLists = Object.keys(titles).map(function (k) {
            return { title: k, count: titles[k] };
        }).sort(function (a, b) { return b.count - a.count; }).slice(0, 6);
        return {
            hasData: inRange.length > 0,
            completions: inRange.length,
            daysWithCompletions: Object.keys(byDay).length,
            topLists: topLists
        };
    }

    function buildSnapshot(opts) {
        opts = opts || {};
        var range = opts.range === 'rolling7' ? rolling7Range(opts.ref) : lastWeekRange(opts.ref);
        if (opts.start && opts.end) {
            range = { start: opts.start, end: opts.end, label: opts.label || (opts.start + ' → ' + opts.end) };
        }
        return {
            range: range,
            salesLabor: salesLabor(range.start, range.end),
            dishes: dishes(),
            eightySix: eightySix(),
            checklists: checklists(range.start, range.end),
            generatedAt: new Date().toISOString()
        };
    }

    function formatEmailBody(snap, houseName, isSweet) {
        snap = snap || buildSnapshot();
        var lines = [];
        lines.push(isSweet ? 'Weekly owner snapshot 💕' : 'Weekly owner snapshot');
        if (houseName) lines.push(houseName);
        lines.push(snap.range.label + ': ' + snap.range.start + ' → ' + snap.range.end);
        lines.push('');
        lines.push('—— Sales & labor ——');
        if (snap.salesLabor.hasSales || snap.salesLabor.hasLabor) {
            lines.push('Net sales: ' + money(snap.salesLabor.totalSales) +
                (snap.salesLabor.salesDays ? ' (' + snap.salesLabor.salesDays + ' days)' : ''));
            lines.push('Labor $: ' + money(snap.salesLabor.totalLabor) +
                (snap.salesLabor.laborDays ? ' (' + snap.salesLabor.laborDays + ' days)' : ''));
            if (snap.salesLabor.laborPct != null) lines.push('Labor %: ' + pct(snap.salesLabor.laborPct));
            if (snap.salesLabor.totalHours != null) lines.push('Labor hours: ' + snap.salesLabor.totalHours);
        } else {
            lines.push(isSweet ? 'No sales/labor logged for this week yet.' : 'No sales/labor logged for this week.');
        }
        lines.push('');
        lines.push('—— Food cost % (menu) ——');
        if (snap.dishes.worst && snap.dishes.worst.length) {
            lines.push('Highest FC%:');
            snap.dishes.worst.forEach(function (r) {
                lines.push('  · ' + r.name + ' — ' + pct(r.fc) + ' (plate ' + money2(r.plate) + ' / sell ' + money2(r.sell) + ')');
            });
            if (snap.dishes.best && snap.dishes.best.length) {
                lines.push('Lowest FC%:');
                snap.dishes.best.forEach(function (r) {
                    lines.push('  · ' + r.name + ' — ' + pct(r.fc));
                });
            }
        } else {
            lines.push(isSweet ? 'No costed dishes yet — link recipes + prices on Menu / Costing.' : 'No costed dishes yet.');
        }
        lines.push('');
        lines.push('—— 86 board ——');
        if (snap.eightySix.total > 0) {
            lines.push(snap.eightySix.count86 + ' 86 · ' + snap.eightySix.countLow + ' low');
            snap.eightySix.items.slice(0, 12).forEach(function (it) {
                lines.push('  · [' + (it.status === 'low' ? 'low' : '86') + '] ' + it.name +
                    (it.note ? ' — ' + it.note : ''));
            });
        } else {
            lines.push(isSweet ? 'Board is clear ✨' : "Nothing currently 86'd.");
        }
        lines.push('');
        lines.push('—— Checklists ——');
        if (snap.checklists.hasData) {
            lines.push(snap.checklists.completions + ' completion(s) across ' +
                snap.checklists.daysWithCompletions + ' day(s)');
            snap.checklists.topLists.forEach(function (t) {
                lines.push('  · ' + t.title + ' ×' + t.count);
            });
        } else {
            lines.push(isSweet ? 'No list completions recorded this week yet.' : 'No checklist completions in range.');
        }
        lines.push('');
        lines.push('Open in app: /admin/reports/weekly-snapshot');
        lines.push('— ilovepbj ops');
        return lines.join('\n');
    }

    global.PbjWeeklyOwnerSnapshot = {
        lastWeekRange: lastWeekRange,
        rolling7Range: rolling7Range,
        buildSnapshot: buildSnapshot,
        formatEmailBody: formatEmailBody,
        money: money,
        money2: money2,
        pct: pct
    };
})(window);
