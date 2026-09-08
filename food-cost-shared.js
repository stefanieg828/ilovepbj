/**
 * Shared food-cost helpers: plate cost, menu enrichment, PMIX ideal cost.
 * Optional tools only — pages work without full data.
 */
(function (global) {
    'use strict';

    var RECIPE_KEY = 'pbj_heat_recipes_v1';
    var ING_KEY = 'pbj_heat_ingredients_v1';
    var MENU_KEY = 'pbj_menu_v1';
    var PMIX_KEY = 'pbj_admin_pmix_v1';
    var WASTE_KEY = 'pbj_admin_waste_v1';

    function loadJson(key, fallback) {
        try {
            var r = JSON.parse(localStorage.getItem(key) || 'null');
            return r != null ? r : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function normName(s) {
        return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim().replace(/\s+/g, ' ');
    }

    function loadMaster() {
        var r = loadJson(ING_KEY, { items: {} });
        return r && typeof r.items === 'object' ? r : { items: {} };
    }

    function loadMenu() {
        var r = loadJson(MENU_KEY, { items: [] });
        return r && Array.isArray(r.items) ? r : { items: [] };
    }

    function loadRecipesRaw() {
        return loadJson(RECIPE_KEY, null);
    }

    /** Recipes as flat list { id, title, menuItemId, ingredients, portions } */
    function flatRecipes() {
        var raw = loadRecipesRaw();
        var out = [];
        if (!raw) return out;
        if (Array.isArray(raw.recipes)) {
            raw.recipes.forEach(function (r) { if (r) out.push(r); });
            return out;
        }
        // category structure
        if (Array.isArray(raw)) {
            raw.forEach(function (cat) {
                (cat.recipes || []).forEach(function (r) { if (r) out.push(r); });
            });
            return out;
        }
        if (raw.categories && Array.isArray(raw.categories)) {
            raw.categories.forEach(function (cat) {
                (cat.recipes || []).forEach(function (r) { if (r) out.push(r); });
            });
        }
        // also try top-level objects that look like cats
        Object.keys(raw).forEach(function (k) {
            if (k === 'recipes' || k === 'categories' || k === 'structureAt') return;
            var cat = raw[k];
            if (cat && Array.isArray(cat.recipes)) {
                cat.recipes.forEach(function (r) { if (r) out.push(r); });
            }
        });
        return out;
    }

    /**
     * Cost of one edible recipe unit — matches admin-costing.
     * Conversion (case ÷ packs × recipe units) first; optional directRecipeUnitCost;
     * do NOT fall back to inventory costPerUnit (pack/case, not recipe unit).
     */
    function costPerRecipeUnit(item) {
        if (!item) return null;
        var caseP = item.casePrice != null && item.casePrice !== '' ? parseFloat(item.casePrice) : null;
        var packs = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
        var perPack = parseFloat(item.recipeUnitsPerPack);
        var cpu = null;
        if (caseP != null && !isNaN(caseP) && !isNaN(perPack) && perPack > 0) {
            if (isNaN(packs) || packs <= 0) packs = 1;
            cpu = caseP / (packs * perPack);
        } else if (item.directRecipeUnitCost != null && item.directRecipeUnitCost !== '') {
            cpu = parseFloat(item.directRecipeUnitCost);
        }
        // TODO: inventory costPerUnit is pack/case — intentionally not used (aligns with admin-costing)
        if (cpu == null || isNaN(cpu)) return null;
        var y = parseFloat(item.usableYieldPct);
        if (!isNaN(y) && y > 0 && y < 100) cpu = cpu / (y / 100);
        return cpu;
    }

    function findIngredient(master, name) {
        var n = normName(name);
        if (!n || !master || !master.items) return null;
        if (master.items[n]) return master.items[n];
        var keys = Object.keys(master.items);
        for (var i = 0; i < keys.length; i++) {
            var it = master.items[keys[i]];
            if (normName(it.name) === n || keys[i] === n) return it;
        }
        // fuzzy contains
        for (var j = 0; j < keys.length; j++) {
            var it2 = master.items[keys[j]];
            var nn = normName(it2.name || keys[j]);
            if (nn.indexOf(n) >= 0 || n.indexOf(nn) >= 0) return it2;
        }
        return null;
    }

    /**
     * Plate food cost for a menu item id (from linked recipe).
     * @returns {{ per:number, batch:number, recipe:string, portions:number }|null}
     */
    function plateCostForMenuId(menuId, master) {
        if (!menuId) return null;
        master = master || loadMaster();
        var recipes = flatRecipes();
        var best = null;
        recipes.forEach(function (rec) {
            if (String(rec.menuItemId || '') !== String(menuId) &&
                String(rec.menuId || '') !== String(menuId)) return;
            var ings = rec.ingredients || rec.ings || [];
            if (!ings.length) return;
            var total = 0, missing = 0;
            ings.forEach(function (ing) {
                var item = findIngredient(master, ing.name || ing.ingredient);
                var qty = parseFloat(ing.qty);
                var cpu = costPerRecipeUnit(item);
                if (cpu == null || isNaN(qty)) missing++;
                else total += qty * cpu;
            });
            if (missing && missing === ings.length) return;
            // allow partial if majority costed
            if (missing > ings.length / 2) return;
            var portions = parseFloat(rec.portions);
            var per = (!isNaN(portions) && portions > 0) ? total / portions : total;
            if (best == null || per < best.per) {
                best = {
                    per: Math.round(per * 10000) / 10000,
                    batch: total,
                    recipe: rec.title || rec.name || '',
                    portions: portions || 1
                };
            }
        });
        return best;
    }

    /** Match menu item by name */
    function findMenuItemByName(name) {
        var menu = loadMenu();
        var n = normName(name);
        if (!n) return null;
        var exact = null, fuzzy = null;
        (menu.items || []).forEach(function (it) {
            var nn = normName(it.name);
            if (nn === n) exact = it;
            else if (!fuzzy && (nn.indexOf(n) >= 0 || n.indexOf(nn) >= 0)) fuzzy = it;
        });
        return exact || fuzzy;
    }

    function loadPmix() {
        var r = loadJson(PMIX_KEY, null);
        if (!r || !Array.isArray(r.sessions)) return { sessions: [] };
        return r;
    }

    function savePmix(state) {
        localStorage.setItem(PMIX_KEY, JSON.stringify(state));
    }

    function loadWaste() {
        var r = loadJson(WASTE_KEY, null);
        if (!r || !Array.isArray(r.entries)) return { entries: [] };
        return r;
    }

    function saveWaste(state) {
        localStorage.setItem(WASTE_KEY, JSON.stringify(state));
    }

    /**
     * Ideal food cost from a PMIX session: sum(qty * plateCost).
     * Also returns sales $ if unit prices present.
     */
    function idealFoodCostFromSession(session) {
        var master = loadMaster();
        var rows = (session && session.items) || [];
        var foodCost = 0;
        var sales = 0;
        var matched = 0;
        var unmatched = 0;
        var detail = [];
        rows.forEach(function (row) {
            if (!row) return;
            var qty = parseFloat(row.qty);
            if (isNaN(qty) || qty <= 0) return;
            var menuIt = row.menuItemId
                ? (loadMenu().items || []).find(function (m) { return String(m.id) === String(row.menuItemId); })
                : findMenuItemByName(row.name);
            var sell = parseFloat(row.unitPrice != null ? row.unitPrice : (menuIt && menuIt.price));
            if (!isNaN(sell)) sales += sell * qty;
            var plate = menuIt ? plateCostForMenuId(menuIt.id, master) : null;
            if (plate && plate.per != null) {
                var line = Math.round(plate.per * qty * 100) / 100;
                foodCost += line;
                matched++;
                detail.push({
                    name: row.name || (menuIt && menuIt.name) || '',
                    qty: qty,
                    plateCost: plate.per,
                    ideal: line,
                    sell: !isNaN(sell) ? sell : null,
                    matched: true,
                    recipe: plate.recipe
                });
            } else {
                unmatched++;
                detail.push({
                    name: row.name || '',
                    qty: qty,
                    plateCost: null,
                    ideal: null,
                    sell: !isNaN(sell) ? sell : null,
                    matched: false
                });
            }
        });
        foodCost = Math.round(foodCost * 100) / 100;
        sales = Math.round(sales * 100) / 100;
        var idealPct = sales > 0 ? Math.round((foodCost / sales) * 1000) / 10 : null;
        return {
            foodCost: foodCost,
            sales: sales > 0 ? sales : null,
            idealPct: idealPct,
            matched: matched,
            unmatched: unmatched,
            detail: detail
        };
    }

    /**
     * Menu engineering matrix classification (Kasavana/Smith style).
     * Popularity: qty >= avg qty
     * Profitability: contribution margin >= avg contribution
     * Star / Plowhorse / Puzzle / Dog
     */
    function menuEngineeringMatrix(pmixSession) {
        var master = loadMaster();
        var menu = loadMenu();
        var qtyByMenuId = {};
        var qtyByName = {};
        if (pmixSession && Array.isArray(pmixSession.items)) {
            pmixSession.items.forEach(function (row) {
                var qty = parseFloat(row.qty) || 0;
                if (row.menuItemId) {
                    qtyByMenuId[String(row.menuItemId)] = (qtyByMenuId[String(row.menuItemId)] || 0) + qty;
                }
                var n = normName(row.name);
                if (n) qtyByName[n] = (qtyByName[n] || 0) + qty;
            });
        }
        var rows = [];
        (menu.items || []).forEach(function (it) {
            var sell = parseFloat(it.price);
            var plate = plateCostForMenuId(it.id, master);
            var per = plate ? plate.per : null;
            var contrib = (per != null && !isNaN(sell)) ? Math.round((sell - per) * 100) / 100 : null;
            var fc = (per != null && !isNaN(sell) && sell > 0) ? Math.round((per / sell) * 1000) / 10 : null;
            var qty = qtyByMenuId[String(it.id)];
            if (qty == null) qty = qtyByName[normName(it.name)] || 0;
            rows.push({
                id: it.id,
                name: it.name,
                category: it.category || 'other',
                sell: !isNaN(sell) ? sell : null,
                plate: per,
                contrib: contrib,
                fc: fc,
                qty: qty,
                recipe: plate ? plate.recipe : '',
                sales$: (!isNaN(sell) && qty) ? Math.round(sell * qty * 100) / 100 : null
            });
        });

        // Only classify items with qty + contrib (optional: include zero qty as dogs)
        var classifiable = rows.filter(function (r) {
            return r.qty > 0 && r.contrib != null && r.sell != null;
        });
        var avgQty = 0, avgContrib = 0;
        if (classifiable.length) {
            avgQty = classifiable.reduce(function (s, r) { return s + r.qty; }, 0) / classifiable.length;
            avgContrib = classifiable.reduce(function (s, r) { return s + r.contrib; }, 0) / classifiable.length;
        }
        rows.forEach(function (r) {
            if (r.qty <= 0 || r.contrib == null) {
                r.bucket = r.qty > 0 ? 'unknown' : 'no_sales';
                r.bucketLabel = r.qty > 0 ? 'Needs cost' : 'No sales data';
                return;
            }
            var popular = r.qty >= avgQty;
            var profitable = r.contrib >= avgContrib;
            if (popular && profitable) {
                r.bucket = 'star';
                r.bucketLabel = 'Star';
            } else if (popular && !profitable) {
                r.bucket = 'plowhorse';
                r.bucketLabel = 'Plowhorse';
            } else if (!popular && profitable) {
                r.bucket = 'puzzle';
                r.bucketLabel = 'Puzzle';
            } else {
                r.bucket = 'dog';
                r.bucketLabel = 'Dog';
            }
        });
        return {
            rows: rows,
            avgQty: Math.round(avgQty * 10) / 10,
            avgContrib: Math.round(avgContrib * 100) / 100,
            classifiable: classifiable.length,
            hasPmix: !!(pmixSession && pmixSession.items && pmixSession.items.length)
        };
    }

    /**
     * Sum logged waste $ for a list of Y-m-d dates (or all if empty).
     * @returns {{ total:number, count:number, byReason:Object, entries:Array }}
     */
    function wasteTotalForDates(dates) {
        var set = null;
        if (dates && dates.length) {
            set = {};
            dates.forEach(function (d) { set[d] = true; });
        }
        var entries = [];
        var total = 0;
        var byReason = {};
        (loadWaste().entries || []).forEach(function (e) {
            if (!e || !e.date) return;
            if (set && !set[e.date]) return;
            var c = parseFloat(e.cost);
            if (isNaN(c)) c = 0;
            total += c;
            var r = e.reason || 'Other';
            byReason[r] = (byReason[r] || 0) + c;
            entries.push(e);
        });
        return {
            total: Math.round(total * 100) / 100,
            count: entries.length,
            byReason: byReason,
            entries: entries
        };
    }

    /**
     * Pick best PMIX session overlapping [start,end] (prefer POS, then newest).
     * Falls back to latest session with items.
     */
    function bestPmixSessionForRange(start, end) {
        var sessions = (loadPmix().sessions || []).slice();
        if (!sessions.length) return null;
        function overlaps(s) {
            var sd = s.start || s.date || '';
            var ed = s.end || s.date || sd;
            if (!sd || !start || !end) return false;
            return sd <= end && ed >= start;
        }
        var scored = sessions.filter(function (s) {
            return s && s.items && s.items.length;
        }).map(function (s) {
            var score = s.updatedAt || 0;
            if (s.source === 'pos') score += 1e15;
            if (overlaps(s)) score += 1e14;
            return { s: s, score: score };
        });
        if (!scored.length) return null;
        scored.sort(function (a, b) { return b.score - a.score; });
        // Prefer overlapping; else newest with items
        var over = scored.filter(function (x) { return overlaps(x.s); });
        return (over[0] || scored[0]).s;
    }

    /**
     * Ideal food cost for a date range using best overlapping PMIX session.
     * If session is wider than range, still uses full session mix (period proxy).
     */
    function idealFoodCostForRange(start, end) {
        var session = bestPmixSessionForRange(start, end);
        if (!session) return null;
        var ideal = idealFoodCostFromSession(session);
        ideal.session = session;
        ideal.sessionLabel = session.label || session.date || '';
        return ideal;
    }


    /**
     * Suggested menu sell prices for a target food-cost band (25–30% FC).
     * @param {number} portionCost plate/portion food cost in $
     * @returns {{ at25:number, at275:number, at30:number }|null}
     */
    function suggestedSellPrices(portionCost) {
        var c = parseFloat(portionCost);
        if (isNaN(c) || c < 0) return null;
        function round2(n) { return Math.round(n * 100) / 100; }
        return {
            at25: round2(c / 0.25),
            at275: round2(c / 0.275),
            at30: round2(c / 0.30)
        };
    }

    global.PbjFoodCost = {
        RECIPE_KEY: RECIPE_KEY,
        ING_KEY: ING_KEY,
        MENU_KEY: MENU_KEY,
        PMIX_KEY: PMIX_KEY,
        WASTE_KEY: WASTE_KEY,
        loadJson: loadJson,
        normName: normName,
        loadMaster: loadMaster,
        loadMenu: loadMenu,
        flatRecipes: flatRecipes,
        costPerRecipeUnit: costPerRecipeUnit,
        findIngredient: findIngredient,
        plateCostForMenuId: plateCostForMenuId,
        suggestedSellPrices: suggestedSellPrices,
        findMenuItemByName: findMenuItemByName,
        loadPmix: loadPmix,
        savePmix: savePmix,
        loadWaste: loadWaste,
        saveWaste: saveWaste,
        idealFoodCostFromSession: idealFoodCostFromSession,
        menuEngineeringMatrix: menuEngineeringMatrix,
        wasteTotalForDates: wasteTotalForDates,
        bestPmixSessionForRange: bestPmixSessionForRange,
        idealFoodCostForRange: idealFoodCostForRange
    };
})(window);
