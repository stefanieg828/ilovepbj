/**
 * Ops nudges: unapplied invoice costs + schedule vs actual labor.
 */
(function (global) {
    'use strict';

    var APPLIED_KEY = 'pbj_invoice_cost_applied_v1';
    var SCHED_KEY = 'pbj_admin_schedules_v1';
    var LABOR_KEY = 'pbj_admin_labor_v2';
    var LABOR_OLD = 'pbj_admin_labor_v1';
    var TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
    var DAY_NAMES = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    function loadJson(key, fallback) {
        try {
            var r = JSON.parse(localStorage.getItem(key) || 'null');
            return r != null ? r : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function loadAppliedAll() {
        var r = loadJson(APPLIED_KEY, {});
        return r && typeof r === 'object' ? r : {};
    }

    function isInvoiceApplied(invId, appliedMap) {
        var m = appliedMap || loadAppliedAll();
        var meta = m[invId];
        return !!(meta && meta.at && (meta.count == null || meta.count > 0));
    }

    /**
     * @param {Array} invoices from invoices-api
     * @returns {{ total:number, unapplied:Array, applied:number }}
     */
    function unappliedFromInvoices(invoices) {
        var appliedMap = loadAppliedAll();
        var list = Array.isArray(invoices) ? invoices : [];
        var unapplied = [];
        var applied = 0;
        list.forEach(function (inv) {
            if (!inv || !inv.id) return;
            if (isInvoiceApplied(inv.id, appliedMap)) applied++;
            else unapplied.push(inv);
        });
        // newest first
        unapplied.sort(function (a, b) {
            return (b.createdAt || 0) - (a.createdAt || 0);
        });
        return {
            total: list.length,
            unapplied: unapplied,
            applied: applied,
            count: unapplied.length
        };
    }

    function fetchUnapplied() {
        return fetch('/invoices-api.php', { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    return { total: 0, unapplied: [], applied: 0, count: 0, error: (data && data.error) || 'fail' };
                }
                return unappliedFromInvoices(data.invoices || []);
            })
            .catch(function () {
                return { total: 0, unapplied: [], applied: 0, count: 0, error: 'network' };
            });
    }

    function hoursBetween(start, end, breakMins) {
        if (!start || !end) return 0;
        var a = String(start).split(':').map(Number);
        var b = String(end).split(':').map(Number);
        if (a.length < 2 || b.length < 2 || isNaN(a[0]) || isNaN(b[0])) return 0;
        var mins = (b[0] * 60 + (b[1] || 0)) - (a[0] * 60 + (a[1] || 0));
        if (mins < 0) mins += 24 * 60;
        mins -= (parseInt(breakMins, 10) || 0);
        if (mins < 0) mins = 0;
        return Math.round((mins / 60) * 100) / 100;
    }

    function dateStr(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function mondayOf(d) {
        var x = new Date(d);
        x.setHours(12, 0, 0, 0);
        var day = x.getDay();
        var monOffset = day === 0 ? 6 : day - 1;
        x.setDate(x.getDate() - monOffset);
        return x;
    }

    function weekKey(d) {
        return dateStr(mondayOf(d));
    }

    function dayNameForDate(dateStrYmd) {
        var d = new Date(dateStrYmd + 'T12:00:00');
        var js = d.getDay(); // 0 Sun
        // Mon=0 in DAY_NAMES
        var idx = js === 0 ? 6 : js - 1;
        return DAY_NAMES[idx];
    }

    function loadTeamPeople() {
        for (var i = 0; i < TEAM_KEYS.length; i++) {
            var r = loadJson(TEAM_KEYS[i], null);
            if (r && Array.isArray(r.people)) return r.people;
        }
        return [];
    }

    function teamWageFor(personId, name, role) {
        var people = loadTeamPeople();
        var person = people.find(function (p) {
            if (!p || p.status === 'inactive') return false;
            if (personId && p.id && String(p.id) === String(personId)) return true;
            return name && p.name && String(p.name).trim().toLowerCase() === String(name).trim().toLowerCase();
        });
        if (!person) return null;
        var wages = person.roleWages || {};
        var rate = null;
        if (role && wages[role] != null && wages[role] !== '') rate = parseFloat(wages[role]);
        if ((rate == null || isNaN(rate)) && person.role && wages[person.role] != null) rate = parseFloat(wages[person.role]);
        if (rate == null || isNaN(rate)) {
            var keys = Object.keys(wages);
            for (var k = 0; k < keys.length; k++) {
                var n = parseFloat(wages[keys[k]]);
                if (!isNaN(n)) { rate = n; break; }
            }
        }
        if ((rate == null || isNaN(rate)) && person.wage != null && person.wage !== '') rate = parseFloat(person.wage);
        return (rate != null && !isNaN(rate)) ? rate : null;
    }

    function loadScheduleShifts(dateStrYmd) {
        try {
            var sched = loadJson(SCHED_KEY, null);
            if (!sched || !sched.weeks) return [];
            var wk = weekKey(new Date(dateStrYmd + 'T12:00:00'));
            var dayName = dayNameForDate(dateStrYmd);
            return (sched.weeks[wk] || []).filter(function (s) { return s.day === dayName; });
        } catch (e) {
            return [];
        }
    }

    function loadLaborDay(dateStrYmd) {
        var labor = loadJson(LABOR_KEY, null);
        if (!labor || !Array.isArray(labor.days)) labor = loadJson(LABOR_OLD, { days: [] });
        var days = (labor && labor.days) || [];
        for (var i = 0; i < days.length; i++) {
            if (days[i] && days[i].date === dateStrYmd) return days[i];
        }
        return null;
    }

    function entryActualHours(e) {
        return hoursBetween(
            e.actualStart || e.clockIn || '',
            e.actualEnd || e.clockOut || '',
            e.breakMins
        );
    }

    function entrySchedHours(e) {
        return hoursBetween(e.schedStart || e.start || '', e.schedEnd || e.end || '', 0);
    }

    function entryActualCost(e) {
        var lc = parseFloat(e.laborCost);
        if (!isNaN(lc)) return lc;
        var rate = parseFloat(e.wageRate != null ? e.wageRate : e.wage);
        if (isNaN(rate)) return null;
        return Math.round(rate * entryActualHours(e) * 100) / 100;
    }

    /**
     * Schedule vs actual for one calendar day.
     * Prefer labor day entries when present; else schedule + team wages for scheduled side only.
     */
    function scheduleVsActual(dateStrYmd) {
        var schedShifts = loadScheduleShifts(dateStrYmd);
        var laborDay = loadLaborDay(dateStrYmd);
        var entries = (laborDay && Array.isArray(laborDay.entries)) ? laborDay.entries : [];

        var schedH = 0;
        var sched$ = 0;
        var sched$any = false;
        var missingWageH = 0;

        // Scheduled from labor entries if they carry sched times; else pure schedule
        if (entries.length) {
            var anySchedOnEntries = entries.some(function (e) {
                return (e.schedStart || e.start) && (e.schedEnd || e.end);
            });
            if (anySchedOnEntries) {
                entries.forEach(function (e) {
                    var h = entrySchedHours(e);
                    schedH += h;
                    var rate = parseFloat(e.wageRate);
                    if (isNaN(rate)) rate = teamWageFor(e.personId, e.name, e.role);
                    if (rate != null && !isNaN(rate)) {
                        sched$ += rate * h;
                        sched$any = true;
                    } else if (h) {
                        missingWageH += h;
                    }
                });
            } else {
                schedShifts.forEach(function (s) {
                    var h = hoursBetween(s.start, s.end, 0);
                    schedH += h;
                    var rate = teamWageFor(s.personId, s.name, s.role);
                    if (rate != null) {
                        sched$ += rate * h;
                        sched$any = true;
                    } else if (h) {
                        missingWageH += h;
                    }
                });
            }
        } else {
            schedShifts.forEach(function (s) {
                var h = hoursBetween(s.start, s.end, 0);
                schedH += h;
                var rate = teamWageFor(s.personId, s.name, s.role);
                if (rate != null) {
                    sched$ += rate * h;
                    sched$any = true;
                } else if (h) {
                    missingWageH += h;
                }
            });
        }

        var actH = 0;
        var act$ = 0;
        var act$any = false;
        if (entries.length) {
            entries.forEach(function (e) {
                actH += entryActualHours(e);
                var c = entryActualCost(e);
                if (c != null) {
                    act$ += c;
                    act$any = true;
                }
            });
            // day-level cost override wins for $
            if (laborDay && laborDay.cost != null && laborDay.cost !== '') {
                var dc = parseFloat(laborDay.cost);
                if (!isNaN(dc)) {
                    act$ = dc;
                    act$any = true;
                }
            }
            if (laborDay && laborDay.hours != null && laborDay.hours !== '' && !entries.length) {
                var dh = parseFloat(laborDay.hours);
                if (!isNaN(dh)) actH = dh;
            }
        }

        schedH = Math.round(schedH * 100) / 100;
        actH = Math.round(actH * 100) / 100;
        sched$ = Math.round(sched$ * 100) / 100;
        act$ = Math.round(act$ * 100) / 100;

        var varH = (schedH || actH) ? Math.round((actH - schedH) * 100) / 100 : null;
        var var$ = (sched$any || act$any) ? Math.round((act$ - sched$) * 100) / 100 : null;

        return {
            date: dateStrYmd,
            schedHours: schedH,
            actualHours: actH,
            varHours: varH,
            schedCost: sched$any ? sched$ : null,
            actualCost: act$any ? act$ : null,
            varCost: var$,
            schedShiftCount: schedShifts.length,
            entryCount: entries.length,
            missingWageHours: Math.round(missingWageH * 100) / 100,
            hasSchedule: schedShifts.length > 0 || schedH > 0,
            hasActual: entries.length > 0 || actH > 0
        };
    }

    /** Sum variance over last N calendar days (inclusive of today). */
    function scheduleVsActualRange(daysBack) {
        var n = daysBack || 7;
        var today = new Date();
        today.setHours(12, 0, 0, 0);
        var total = {
            schedHours: 0,
            actualHours: 0,
            schedCost: 0,
            actualCost: 0,
            sched$any: false,
            act$any: false,
            daysWithSched: 0,
            daysWithActual: 0,
            days: []
        };
        for (var i = n - 1; i >= 0; i--) {
            var d = new Date(today);
            d.setDate(today.getDate() - i);
            var one = scheduleVsActual(dateStr(d));
            total.days.push(one);
            if (one.hasSchedule) total.daysWithSched++;
            if (one.hasActual) total.daysWithActual++;
            total.schedHours += one.schedHours || 0;
            total.actualHours += one.actualHours || 0;
            if (one.schedCost != null) {
                total.schedCost += one.schedCost;
                total.sched$any = true;
            }
            if (one.actualCost != null) {
                total.actualCost += one.actualCost;
                total.act$any = true;
            }
        }
        total.schedHours = Math.round(total.schedHours * 100) / 100;
        total.actualHours = Math.round(total.actualHours * 100) / 100;
        total.schedCost = Math.round(total.schedCost * 100) / 100;
        total.actualCost = Math.round(total.actualCost * 100) / 100;
        total.varHours = Math.round((total.actualHours - total.schedHours) * 100) / 100;
        total.varCost = (total.sched$any || total.act$any)
            ? Math.round((total.actualCost - total.schedCost) * 100) / 100
            : null;
        return total;
    }

    function formatMoney(n) {
        if (n == null || isNaN(n)) return '—';
        var sign = n < 0 ? '-' : '';
        return sign + '$' + Math.abs(Math.round(n * 100) / 100).toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function formatVarHours(v) {
        if (v == null || isNaN(v)) return '—';
        if (Math.abs(v) < 0.05) return '0h';
        return (v > 0 ? '+' : '') + (Math.round(v * 10) / 10) + 'h';
    }

    function formatVarMoney(v) {
        if (v == null || isNaN(v)) return '—';
        if (Math.abs(v) < 0.5) return '$0';
        return (v > 0 ? '+' : '') + formatMoney(v);
    }

    /**
     * Merge day-log arrays by calendar date (newer updatedAt wins).
     * Used for multi-device sales / labor / cash.
     */
    function mergeDaysByDate(localDays, remoteDays) {
        var map = {};
        function consider(d) {
            if (!d || !d.date) return;
            var key = String(d.date);
            var ts = parseInt(d.updatedAt, 10) || 0;
            var prev = map[key];
            if (!prev || ts >= (parseInt(prev.updatedAt, 10) || 0)) {
                map[key] = d;
            }
        }
        (localDays || []).forEach(consider);
        (remoteDays || []).forEach(consider);
        return Object.keys(map).sort().map(function (k) { return map[k]; });
    }

    function loadCashDays() {
        var cash = loadJson('pbj_admin_cash_v1', { days: [] });
        return (cash && Array.isArray(cash.days)) ? cash.days : [];
    }

    function loadSalesDays() {
        var sales = loadJson('pbj_admin_sales_v2', null);
        if (!sales || !Array.isArray(sales.days)) sales = loadJson('pbj_admin_sales_v1', { days: [] });
        return (sales && Array.isArray(sales.days)) ? sales.days : [];
    }

    function cashDayIsClosed(row) {
        if (!row) return false;
        // Closed if expected + counted filled (core of a cash close)
        var exp = parseFloat(row.expected);
        var cnt = parseFloat(row.counted);
        if (!isNaN(exp) && !isNaN(cnt)) return true;
        // Or deposit logged alone
        var dep = parseFloat(row.deposit);
        return !isNaN(dep) && dep > 0;
    }

    function salesDayHasNet(row) {
        if (!row) return false;
        var n = parseFloat(row.net);
        if (!isNaN(n) && n !== 0) return true;
        n = parseFloat(row.gross);
        return !isNaN(n) && n !== 0;
    }

    /**
     * Cash close habit: days with sales but no cash close (and optional "today not closed").
     * @param {number} daysBack inclusive of today
     */
    function cashCloseStatus(daysBack) {
        daysBack = daysBack || 7;
        var cashBy = {};
        loadCashDays().forEach(function (d) {
            if (d && d.date) cashBy[d.date] = d;
        });
        var salesBy = {};
        loadSalesDays().forEach(function (d) {
            if (d && d.date) salesBy[d.date] = d;
        });
        var today = new Date();
        today.setHours(12, 0, 0, 0);
        var missing = [];
        var closed = 0;
        var salesDays = 0;
        for (var i = daysBack - 1; i >= 0; i--) {
            var d = new Date(today);
            d.setDate(today.getDate() - i);
            var ds = dateStr(d);
            var isToday = i === 0;
            var hasSales = salesDayHasNet(salesBy[ds]);
            var closedOk = cashDayIsClosed(cashBy[ds]);
            if (hasSales) salesDays++;
            if (closedOk) closed++;
            // Flag: past days with sales and no close; today if sales logged and no close after noon-ish
            // Always list gaps for days with sales missing close (incl today) so managers see habit
            if (hasSales && !closedOk) {
                missing.push({ date: ds, isToday: isToday, hasSales: true });
            } else if (!hasSales && !closedOk && isToday) {
                // soft: today no sales yet — still surface evening close reminder only via separate flag
            }
        }
        var todayRow = cashBy[dateStr(today)];
        return {
            missing: missing,
            missingCount: missing.length,
            closedCount: closed,
            salesDays: salesDays,
            todayClosed: cashDayIsClosed(todayRow),
            todayHasSales: salesDayHasNet(salesBy[dateStr(today)]),
            todayNeedsClose: salesDayHasNet(salesBy[dateStr(today)]) && !cashDayIsClosed(todayRow)
        };
    }

    /**
     * Theoretical vs actual food cost for a window (default last 7 days).
     * Actual: Auto-Order food lines, else sales food estimate is not used (needs real $).
     * Theo: avg plate food-cost % × net sales from recipes/menu.
     */
    function foodCostVariance(daysBack) {
        daysBack = daysBack || 7;
        var today = new Date();
        today.setHours(12, 0, 0, 0);
        var dates = [];
        for (var i = daysBack - 1; i >= 0; i--) {
            var d = new Date(today);
            d.setDate(today.getDate() - i);
            dates.push(dateStr(d));
        }
        var set = {};
        dates.forEach(function (x) { set[x] = true; });

        var salesDays = loadSalesDays();
        var totalSales = 0;
        var salesN = 0;
        salesDays.forEach(function (row) {
            if (!row || !set[row.date]) return;
            var n = parseFloat(row.net);
            if (isNaN(n)) n = parseFloat(row.gross);
            if (!isNaN(n)) {
                totalSales += n;
                salesN++;
            }
        });

        // Actual food from Auto-Orders (food categories)
        var FOOD_CATS = {
            produce: 1, dairy: 1, meat: 1, seafood: 1, poultry: 1, grocery: 1,
            canned_dry: 1, frozen: 1, bakery: 1, beverage: 1, food: 1, protein: 1
        };
        var orders = loadJson('pbj_admin_auto_orders_v1', { orders: [] });
        var foodActual = 0;
        var orderCount = 0;
        (orders.orders || []).forEach(function (o) {
            if (!o) return;
            var od = o.businessDate || (o.at ? dateStr(new Date(o.at)) : null);
            if (!od || !set[od]) return;
            orderCount++;
            (o.lines || []).forEach(function (line) {
                var cat = String(line.category || '').toLowerCase().replace(/\s+/g, '_');
                if (!FOOD_CATS[cat] && cat.indexOf('food') < 0 && cat.indexOf('produce') < 0 && cat.indexOf('meat') < 0) {
                    // also allow common aliases
                    if (!/produce|dairy|meat|seafood|poultry|grocery|protein|frozen|bakery/.test(cat)) return;
                }
                var q = parseFloat(line.qty);
                if (isNaN(q)) return;
                var c;
                if ((line.orderBy === 'case' || line.parBy === 'case') && line.casePrice != null && line.casePrice !== '') {
                    c = parseFloat(line.casePrice);
                } else {
                    c = parseFloat(line.costPerUnit);
                }
                if (isNaN(c)) return;
                foodActual += c * q;
            });
        });
        foodActual = Math.round(foodActual * 100) / 100;

        // Theo from recipes
        var recipes = loadJson('pbj_heat_recipes_v1', { recipes: [] });
        var ing = loadJson('pbj_heat_ingredients_v1', { items: {} });
        var menu = loadJson('pbj_menu_v1', null);
        var items = (ing && ing.items) ? ing.items : {};
        var menuItems = [];
        if (menu && Array.isArray(menu.items)) menuItems = menu.items;
        else if (menu && Array.isArray(menu.menu)) menuItems = menu.menu;

        function norm(s) { return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim(); }
        function plateCost(recipe) {
            var ings = recipe.ingredients || recipe.ings || [];
            var total = 0, any = false;
            ings.forEach(function (ingRow) {
                var key = norm(ingRow.name || ingRow.ingredient);
                var item = items[key];
                if (!item) {
                    Object.keys(items).forEach(function (k) {
                        if (norm(items[k].name) === key) item = items[k];
                    });
                }
                var qty = parseFloat(ingRow.qty);
                if (isNaN(qty) || !item) return;
                var caseP = item.casePrice != null && item.casePrice !== '' ? parseFloat(item.casePrice) : null;
                var packs = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
                var perPack = parseFloat(item.recipeUnitsPerPack);
                var cpu = null;
                if (caseP != null && !isNaN(caseP) && !isNaN(perPack) && perPack > 0) {
                    if (isNaN(packs) || packs <= 0) packs = 1;
                    cpu = caseP / (packs * perPack);
                } else if (item.directRecipeUnitCost != null && item.directRecipeUnitCost !== '') {
                    cpu = parseFloat(item.directRecipeUnitCost);
                } else if (item.costPerUnit != null && item.costPerUnit !== '') {
                    cpu = parseFloat(item.costPerUnit);
                }
                if (cpu == null || isNaN(cpu)) return;
                var y = parseFloat(item.usableYieldPct);
                if (!isNaN(y) && y > 0 && y < 100) cpu = cpu / (y / 100);
                total += qty * cpu;
                any = true;
            });
            return any ? total : null;
        }
        function menuPriceFor(recipe) {
            var title = norm(recipe.title || recipe.name);
            for (var mi = 0; mi < menuItems.length; mi++) {
                var m = menuItems[mi];
                if (norm(m.name || m.title) === title || (m.recipeId && m.recipeId === recipe.id)) {
                    var p = parseFloat(m.price);
                    return isNaN(p) ? null : p;
                }
            }
            var rp = parseFloat(recipe.menuPrice || recipe.price);
            return isNaN(rp) ? null : rp;
        }
        var sumPct = 0, nPct = 0;
        var recipeList = Array.isArray(recipes.recipes) ? recipes.recipes
            : (Array.isArray(recipes) ? recipes : []);
        recipeList.forEach(function (r) {
            if (!r || typeof r !== 'object') return;
            var cost = plateCost(r);
            var price = menuPriceFor(r);
            if (cost != null && price != null && price > 0) {
                sumPct += (cost / price) * 100;
                nPct++;
            }
        });
        var theoPct = nPct ? (sumPct / nPct) : null;
        var theo$ = (theoPct != null && salesN && totalSales > 0)
            ? Math.round(totalSales * theoPct / 100 * 100) / 100
            : null;

        var hasActual = orderCount > 0 && foodActual > 0;
        var variance = (hasActual && theo$ != null) ? Math.round((foodActual - theo$) * 100) / 100 : null;
        var overTheo = variance != null && variance > 0;
        // alert if over by $25+ or 10%+ of theo
        var alert = false;
        if (variance != null && theo$ != null && theo$ > 0) {
            alert = variance >= 25 || (variance / theo$) >= 0.1;
        }

        return {
            daysBack: daysBack,
            totalSales: salesN ? Math.round(totalSales * 100) / 100 : null,
            salesDays: salesN,
            foodActual: hasActual ? foodActual : null,
            foodFromOrders: hasActual,
            orderCount: orderCount,
            theoPct: theoPct != null ? Math.round(theoPct * 10) / 10 : null,
            theo$: theo$,
            variance: variance,
            overTheo: overTheo,
            alert: alert,
            recipeCount: nPct
        };
    }

    /** Best-effort kitchen push for day-log keys (sales / labor / cash). */
    function pushDayLog(sharedKey, localState) {
        if (!global.PbjSharedState || !localState || typeof localState !== 'object') return;
        try {
            var ss = new global.PbjSharedState({
                key: sharedKey,
                date: '2000-01-01',
                pollMs: 60000,
                onRemote: function () {},
                onStatus: function () {}
            });
            ss.version = 0;
            ss.push(localState, { force: true });
        } catch (e) {}
    }

    global.PbjOpsNudges = {
        APPLIED_KEY: APPLIED_KEY,
        loadAppliedAll: loadAppliedAll,
        isInvoiceApplied: isInvoiceApplied,
        unappliedFromInvoices: unappliedFromInvoices,
        fetchUnapplied: fetchUnapplied,
        scheduleVsActual: scheduleVsActual,
        scheduleVsActualRange: scheduleVsActualRange,
        formatMoney: formatMoney,
        formatVarHours: formatVarHours,
        formatVarMoney: formatVarMoney,
        hoursBetween: hoursBetween,
        mergeDaysByDate: mergeDaysByDate,
        cashCloseStatus: cashCloseStatus,
        cashDayIsClosed: cashDayIsClosed,
        foodCostVariance: foodCostVariance,
        pushDayLog: pushDayLog
    };
})(window);
