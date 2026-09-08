/**
 * Daily Pulse — owner/GM/manager home strip.
 * Reads local sales, labor, cash, inventory, and checklist completion signals.
 * Optional POS status + one-tap sync via PbjPosSync.
 */
(function (global) {
    'use strict';

    function todayStr() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function money(n) {
        if (n == null || isNaN(n)) return '—';
        return '$' + (Math.round(n * 100) / 100).toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
    function pct(n) {
        if (n == null || isNaN(n)) return '—';
        return (Math.round(n * 10) / 10) + '%';
    }
    function loadJson(key, fallback) {
        try {
            var r = JSON.parse(localStorage.getItem(key) || 'null');
            return r != null ? r : fallback;
        } catch (e) {
            return fallback;
        }
    }
    function canSeePulse() {
        if (!global.PbjPerms || !global.PbjPerms.loaded) return false;
        var r = global.PbjPerms.role;
        if (r === 'owner' || r === 'gm' || r === 'admin' || r === 'manager') return true;
        return global.PbjPerms.can('admin.reports.view') || global.PbjPerms.can('admin.schedules.view_wages');
    }

    function netOf(d) {
        if (!d) return null;
        var n = parseFloat(d.net);
        if (!isNaN(n)) return n;
        n = parseFloat(d.gross);
        return isNaN(n) ? null : n;
    }

    function salesToday() {
        var state = loadJson('pbj_admin_sales_v2', null) || loadJson('pbj_admin_sales_v1', { days: [] });
        var days = (state && state.days) || [];
        var t = todayStr();
        for (var i = 0; i < days.length; i++) {
            if (days[i] && days[i].date === t) return days[i];
        }
        return null;
    }

    function laborToday() {
        var labor = loadJson('pbj_admin_labor_v2', null) || loadJson('pbj_admin_labor_v1', { days: [] });
        var days = (labor && labor.days) || [];
        var t = todayStr();
        var row = null;
        days.forEach(function (d) {
            if (d && d.date === t) row = d;
        });
        if (!row) {
            // sales day labor field only
            var sOnly = salesToday();
            if (sOnly && sOnly.labor != null && sOnly.labor !== '') {
                var a0 = parseFloat(sOnly.labor);
                return { amount: isNaN(a0) ? null : a0, hours: null, fromPos: sOnly.source === 'pos' };
            }
            return { amount: null, hours: null, fromPos: false };
        }
        var amount = parseFloat(
            row.cost != null && row.cost !== '' ? row.cost
                : (row.totalLabor != null ? row.totalLabor : row.labor$)
        );
        if (isNaN(amount) && Array.isArray(row.entries)) {
            amount = 0;
            var any = false;
            row.entries.forEach(function (e) {
                var lc = parseFloat(e.laborCost);
                if (!isNaN(lc)) {
                    amount += lc;
                    any = true;
                    return;
                }
                var w = parseFloat(e.wageRate != null ? e.wageRate : e.wage);
                var h = parseFloat(e.hours);
                if (isNaN(h) && e.actualStart && e.actualEnd) {
                    // rough: not computed here
                    h = NaN;
                }
                if (!isNaN(w) && !isNaN(h)) {
                    amount += w * h;
                    any = true;
                }
            });
            if (!any) amount = null;
        }
        if (isNaN(amount)) amount = null;
        // Fallback: sales day labor field
        if (amount == null) {
            var s = salesToday();
            if (s && s.labor != null && s.labor !== '') {
                amount = parseFloat(s.labor);
                if (isNaN(amount)) amount = null;
            }
        }
        var hours = row.hours != null && row.hours !== '' ? parseFloat(row.hours) : null;
        if (hours != null && isNaN(hours)) hours = null;
        return {
            amount: amount,
            hours: hours,
            fromPos: row.source === 'pos' || row.costSource === 'pos'
        };
    }

    function cashToday() {
        var cash = loadJson('pbj_admin_cash_v1', { days: [] });
        var t = todayStr();
        var row = null;
        (cash.days || []).forEach(function (d) {
            if (d && d.date === t) row = d;
        });
        if (!row) return null;
        var exp = parseFloat(row.expected);
        var cnt = parseFloat(row.counted);
        if (isNaN(exp) || isNaN(cnt)) return null;
        return Math.round((cnt - exp) * 100) / 100;
    }

    function belowParCount() {
        var inv = loadJson('pbj_heat_ingredients_v1', { items: {} });
        var items = inv.items || {};
        var n = 0;
        Object.keys(items).forEach(function (k) {
            var it = items[k];
            var oh = parseFloat(it.onHand);
            var par = parseFloat(it.par);
            if (!isNaN(oh) && !isNaN(par) && oh < par) n++;
        });
        return n;
    }

    function openChecklistHint() {
        var local = loadJson('pbj_ops_list_completions_v1', { events: [] });
        var t = todayStr();
        var done = 0;
        (local.events || []).forEach(function (ev) {
            if (!ev || !ev.at) return;
            var d = new Date(ev.at);
            var ds = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            if (ds === t) done++;
        });
        return done;
    }

    var posStatusCache = null;
    var posStatusLoading = false;

    function paintPosBar() {
        var bar = document.getElementById('pulse-pos-bar');
        var statusEl = document.getElementById('pulse-pos-status');
        var btn = document.getElementById('pulse-pos-sync');
        if (!bar || !statusEl) return;

        // Only managers with connect rights effectively — hide on 401/403
        if (!canSeePulse()) {
            bar.hidden = true;
            return;
        }

        if (!global.PbjPosSync) {
            bar.hidden = false;
            statusEl.textContent = 'POS tools loading…';
            if (btn) btn.hidden = true;
            return;
        }

        function render(data) {
            posStatusCache = data;
            bar.hidden = false;
            if (!data || !data.ok) {
                statusEl.innerHTML = 'POS status unavailable · <a href="/admin/pos-connect">Connect</a>';
                if (btn) {
                    btn.disabled = true;
                    btn.hidden = false;
                }
                return;
            }
            var active = data.activeProviders || [];
            if (!active.length) {
                statusEl.innerHTML = 'No POS linked · <a href="/admin/pos-connect">Connect Square / Clover / Toast</a>';
                if (btn) {
                    btn.disabled = true;
                    btn.hidden = false;
                    btn.textContent = 'Sync POS';
                }
                return;
            }
            var ago = data.latestSyncAt ? global.PbjPosSync.formatSyncAgo(data.latestSyncAt) : 'never';
            var labels = active.map(function (p) {
                return p.charAt(0).toUpperCase() + p.slice(1);
            }).join(' · ');
            var pending = data.pendingSync ? ' · <strong>new POS activity</strong>' : '';
            statusEl.innerHTML = '<strong>' + labels + '</strong> · last sync ' + ago + ' · sales + labor' + pending;
            if (btn) {
                btn.disabled = false;
                btn.hidden = false;
                btn.textContent = data.pendingSync ? 'Sync now' : 'Sync all';
            }
            // Auto-pull once when Square webhook marked pending
            if (data.pendingSync && global.PbjPosSync && !btn._autoSyncing) {
                btn._autoSyncing = true;
                global.PbjPosSync.syncAll(3).then(function (res) {
                    btn._autoSyncing = false;
                    posStatusCache = null;
                    paint();
                }).catch(function () {
                    btn._autoSyncing = false;
                });
            }
        }

        if (posStatusCache) {
            render(posStatusCache);
        }
        if (posStatusLoading) return;
        posStatusLoading = true;
        global.PbjPosSync.status().then(function (data) {
            posStatusLoading = false;
            render(data);
        }).catch(function () {
            posStatusLoading = false;
            bar.hidden = false;
            statusEl.innerHTML = 'POS status offline · <a href="/admin/pos-connect">Open connections</a>';
            if (btn) btn.disabled = true;
        });
    }

    function bindPosSync() {
        var btn = document.getElementById('pulse-pos-sync');
        if (!btn || btn._pbjBound) return;
        btn._pbjBound = true;
        btn.addEventListener('click', function () {
            if (!global.PbjPosSync) return;
            btn.disabled = true;
            var prev = btn.textContent;
            btn.textContent = 'Syncing…';
            global.PbjPosSync.syncAll(7).then(function (res) {
                btn.disabled = false;
                btn.textContent = prev;
                posStatusCache = null;
                paint();
                if (!res || !res.ok) {
                    var statusEl = document.getElementById('pulse-pos-status');
                    if (statusEl) {
                        statusEl.textContent = (res && (res.hint || res.error)) || 'Sync failed';
                    }
                    return;
                }
                var statusEl2 = document.getElementById('pulse-pos-status');
                if (statusEl2) {
                    statusEl2.textContent = 'Synced ' + (res.count || 0) + ' sales' +
                        ((res.laborCount) ? (' · ' + res.laborCount + ' labor') : '') + ' ✓';
                }
                // refresh status meta after short delay
                setTimeout(function () {
                    posStatusCache = null;
                    paintPosBar();
                }, 400);
            }).catch(function () {
                btn.disabled = false;
                btn.textContent = prev;
            });
        });
    }

    function paint() {
        var root = document.getElementById('daily-pulse');
        if (!root) return;
        if (!canSeePulse()) {
            root.hidden = true;
            root.setAttribute('hidden', 'hidden');
            root.classList.add('is-user-hidden');
            return;
        }
        root.hidden = false;
        root.removeAttribute('hidden');
        root.classList.remove('is-user-hidden');

        var sale = salesToday();
        var net = netOf(sale);
        var lab = laborToday();
        var laborPct = (net != null && net > 0 && lab.amount != null) ? (lab.amount / net * 100) : null;
        var cash = cashToday();
        var low = belowParCount();
        var listsDone = openChecklistHint();

        var elSales = document.getElementById('pulse-sales');
        var elLabor = document.getElementById('pulse-labor');
        var elCash = document.getElementById('pulse-cash');
        var elLow = document.getElementById('pulse-par');
        var elLists = document.getElementById('pulse-lists');
        var elSub = document.getElementById('pulse-sub');

        if (elSales) {
            elSales.textContent = net != null ? money(net) : '—';
            elSales.className = 'pulse-num' + (net != null ? ' ok' : '');
        }
        if (elLabor) {
            elLabor.textContent = laborPct != null ? pct(laborPct) : (lab.amount != null ? money(lab.amount) : '—');
            elLabor.className = 'pulse-num' + (laborPct != null && laborPct > 35 ? ' warn' : (laborPct != null ? ' ok' : ''));
            elLabor.title = lab.fromPos
                ? ('From POS' + (lab.amount != null ? ' · $' + lab.amount : ''))
                : (lab.amount != null ? 'Labor $' + lab.amount : '');
        }
        if (elCash) {
            if (cash == null) {
                elCash.textContent = '—';
                elCash.className = 'pulse-num';
            } else {
                elCash.textContent = money(cash) + (cash > 0 ? ' over' : (cash < 0 ? ' short' : ''));
                elCash.className = 'pulse-num' + (cash < 0 ? ' warn' : (cash > 0 ? ' ok' : ''));
            }
        }
        if (elLow) {
            elLow.textContent = String(low);
            elLow.className = 'pulse-num' + (low > 0 ? ' warn' : ' ok');
        }
        if (elLists) {
            elLists.textContent = String(listsDone);
            elLists.className = 'pulse-num' + (listsDone > 0 ? ' ok' : '');
        }
        if (elSub) {
            var bits = [];
            if (net == null) bits.push('log sales');
            if (lab.amount == null) bits.push('labor');
            if (cash == null) bits.push('cash close');
            if (low > 0) bits.push(low + ' below par');
            elSub.textContent = bits.length
                ? ('Needs: ' + bits.join(' · '))
                : ('All quiet — ' + todayStr());
        }

        bindPosSync();
        paintPosBar();
        paintOpsNudges();
    }

    function paintOpsNudges() {
        var bar = document.getElementById('pulse-ops-nudge');
        var statusEl = document.getElementById('pulse-ops-status');
        if (!bar || !statusEl || !canSeePulse()) {
            if (bar) bar.hidden = true;
            return;
        }
        var bits = [];

        // Schedule vs actual (today)
        if (global.PbjOpsNudges) {
            var v = global.PbjOpsNudges.scheduleVsActual(todayStr());
            if (v && (v.hasSchedule || v.hasActual)) {
                var hPart = global.PbjOpsNudges.formatVarHours(v.varHours);
                var $Part = v.varCost != null ? global.PbjOpsNudges.formatVarMoney(v.varCost) : null;
                var tone = '';
                if (v.varHours != null && v.varHours > 0.5) tone = ' ⚠';
                bits.push(
                    '<a href="/admin/labor" style="color:inherit;font-weight:600;text-decoration:none;">Labor today ' +
                    hPart + ($Part ? ' · ' + $Part : '') + ' vs schedule</a>' + tone
                );
            }
        }

        function finish() {
            if (!bits.length) {
                bar.hidden = true;
                statusEl.innerHTML = '';
                return;
            }
            bar.hidden = false;
            statusEl.innerHTML = bits.join(' · ');
        }

        // Cash close habit
        if (global.PbjOpsNudges && global.PbjOpsNudges.cashCloseStatus) {
            var cashSt = global.PbjOpsNudges.cashCloseStatus(7);
            if (cashSt.todayNeedsClose) {
                bits.push(
                    '<a href="/admin/cash" style="color:inherit;font-weight:700;text-decoration:none;">💵 Close today\'s drawer</a>'
                );
            } else if (cashSt.missingCount > 0) {
                bits.push(
                    '<a href="/admin/cash" style="color:inherit;font-weight:600;text-decoration:none;">💵 ' +
                    cashSt.missingCount + ' cash close' + (cashSt.missingCount === 1 ? '' : 's') + ' missing</a>'
                );
            }
        }

        // Theo vs actual food cost (7d)
        if (global.PbjOpsNudges && global.PbjOpsNudges.foodCostVariance) {
            var fc = global.PbjOpsNudges.foodCostVariance(7);
            if (fc.alert && fc.variance != null) {
                bits.push(
                    '<a href="/admin/pnl" style="color:inherit;font-weight:700;text-decoration:none;">🥗 Food $ ' +
                    global.PbjOpsNudges.formatMoney(fc.variance) + ' over theo (7d)</a>'
                );
            } else if (fc.variance != null && fc.overTheo) {
                bits.push(
                    '<a href="/admin/pnl" style="color:inherit;font-weight:600;text-decoration:none;">🥗 Food slightly over theo</a>'
                );
            } else if (fc.theo$ != null && !fc.foodFromOrders) {
                // soft tip only if recipes exist but no order food yet
                bits.push(
                    '<a href="/admin/auto-order" style="color:inherit;opacity:0.9;text-decoration:none;">🥗 Save Auto-Orders to track food vs theo</a>'
                );
            }
        }

        if (global.PbjOpsNudges && global.PbjOpsNudges.fetchUnapplied) {
            global.PbjOpsNudges.fetchUnapplied().then(function (info) {
                if (info && info.count > 0) {
                    bits.unshift(
                        '<a href="/admin/invoices" style="color:inherit;font-weight:700;text-decoration:none;">🧾 ' +
                        info.count + ' invoice' + (info.count === 1 ? '' : 's') + ' need cost apply</a>'
                    );
                }
                finish();
            }).catch(finish);
        } else {
            finish();
        }
    }

    function boot() {
        paint();
        if (global.PbjPerms && global.PbjPerms.ready) {
            global.PbjPerms.ready.then(paint);
        }
        document.addEventListener('pbj-perms-ready', paint);
        document.addEventListener('pbj-pos-synced', paint);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                posStatusCache = null;
                paint();
            }
        });
        setInterval(function () {
            if (!document.hidden) paint();
        }, 60000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    global.PbjDailyPulse = { paint: paint };
})(window);
