/**
 * Allergen menu — US Big 9 staff matrix + house sync (evergreen).
 * Shared key: allergen_menu_v1 @ 2000-01-01
 * Local mirror: pbj_allergen_menu_v1
 * Menu source of truth fields on pbj_menu_v1 items: allergens[], allergenNote
 */
(function (global) {
    'use strict';

    var LS_KEY = 'pbj_allergen_menu_v1';
    var SHARED_KEY = 'allergen_menu_v1';
    var EVERGREEN = '2000-01-01';
    var MENU_KEY = 'pbj_menu_v1';
    var RECIPE_KEY = 'pbj_heat_recipes_v1';

    /** US FDA Big 9 — stable ids for checkboxes / matrix columns */
    var BIG9 = [
        { id: 'milk', label: 'Milk', short: 'Milk' },
        { id: 'eggs', label: 'Eggs', short: 'Eggs' },
        { id: 'fish', label: 'Fish', short: 'Fish' },
        { id: 'shellfish', label: 'Shellfish', short: 'Shell' },
        { id: 'tree_nuts', label: 'Tree nuts', short: 'Nuts' },
        { id: 'peanuts', label: 'Peanuts', short: 'Pnuts' },
        { id: 'wheat', label: 'Wheat', short: 'Wheat' },
        { id: 'soybeans', label: 'Soybeans', short: 'Soy' },
        { id: 'sesame', label: 'Sesame', short: 'Sesame' }
    ];

    var BIG9_IDS = BIG9.map(function (a) { return a.id; });

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

    function normalizeAllergens(list) {
        if (!Array.isArray(list)) return [];
        var seen = {};
        var out = [];
        list.forEach(function (id) {
            id = String(id || '').trim();
            if (BIG9_IDS.indexOf(id) === -1 || seen[id]) return;
            seen[id] = true;
            out.push(id);
        });
        return out;
    }

    function normalizeOverlay(state) {
        if (!state || typeof state !== 'object') state = {};
        var raw = state.items;
        var items = {};
        if (raw && typeof raw === 'object' && !Array.isArray(raw)) {
            Object.keys(raw).forEach(function (menuId) {
                var row = raw[menuId];
                if (!row || typeof row !== 'object') return;
                var allergens = normalizeAllergens(row.allergens);
                var note = String(row.allergenNote || '').trim();
                if (!allergens.length && !note && !row.name) return;
                items[String(menuId)] = {
                    allergens: allergens,
                    allergenNote: note,
                    name: row.name != null ? String(row.name).trim() : undefined
                };
            });
        }
        return {
            items: items,
            structureAt: typeof state.structureAt === 'number' ? state.structureAt : Date.now()
        };
    }

    function loadOverlayLocal() {
        return normalizeOverlay(loadJson(LS_KEY, { items: {}, structureAt: Date.now() }));
    }

    function saveOverlayLocal(state) {
        var next = normalizeOverlay(state);
        next.structureAt = Date.now();
        saveJson(LS_KEY, next);
        return next;
    }

    function loadMenuRaw() {
        var raw = loadJson(MENU_KEY, null);
        if (!raw) return { items: [] };
        if (Array.isArray(raw)) return { items: raw };
        if (Array.isArray(raw.items)) return raw;
        return { items: [] };
    }

    function saveMenuRaw(menu) {
        if (!menu || typeof menu !== 'object') return;
        if (!Array.isArray(menu.items)) menu.items = [];
        saveJson(MENU_KEY, menu);
    }

    function loadMenuItems() {
        return loadMenuRaw().items
            .filter(function (it) {
                return it && String(it.name || '').trim() !== '';
            })
            .map(function (it) {
                return {
                    id: String(it.id || ''),
                    name: String(it.name || '').trim(),
                    category: String(it.category || 'other'),
                    price: it.price,
                    notes: String(it.notes || ''),
                    allergens: normalizeAllergens(it.allergens),
                    allergenNote: String(it.allergenNote || '').trim()
                };
            })
            .sort(function (a, b) {
                var c = (a.category || '').localeCompare(b.category || '');
                if (c) return c;
                return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
            });
    }

    /**
     * Merge menu items (source of truth for names) with house overlay.
     * Overlay wins for allergens / note when present for that menuId (multi-device sync).
     * Missing overlay falls back to menu item fields.
     */
    function mergeMatrixRows(overlay) {
        overlay = normalizeOverlay(overlay || loadOverlayLocal());
        var menu = loadMenuItems();
        var used = {};
        var rows = menu.map(function (m) {
            used[m.id] = true;
            var o = overlay.items[m.id];
            var allergens = o ? normalizeAllergens(o.allergens) : m.allergens;
            var note = o ? String(o.allergenNote || '').trim() : m.allergenNote;
            // If overlay exists but is empty and menu has tags, prefer menu (fresh local edit)
            if (o && !allergens.length && !note && (m.allergens.length || m.allergenNote)) {
                allergens = m.allergens;
                note = m.allergenNote;
            }
            return {
                id: m.id,
                name: m.name,
                category: m.category,
                price: m.price,
                allergens: allergens,
                allergenNote: note,
                source: 'menu',
                tagged: allergens.length > 0 || !!note
            };
        });

        // Overlay-only entries (menu item deleted on this device but still shared)
        Object.keys(overlay.items).forEach(function (menuId) {
            if (used[menuId]) return;
            var o = overlay.items[menuId];
            if (!o) return;
            var allergens = normalizeAllergens(o.allergens);
            var note = String(o.allergenNote || '').trim();
            if (!allergens.length && !note) return;
            rows.push({
                id: menuId,
                name: String(o.name || 'Menu item').trim() || 'Menu item',
                category: 'other',
                price: null,
                allergens: allergens,
                allergenNote: note,
                source: 'overlay',
                tagged: true
            });
        });

        // Recipe-only rows: recipes with allergens[] / allergenNote and no menuItemId
        // (or menuItemId not in menu) — optional extras for staff reference
        try {
            var recipes = loadJson(RECIPE_KEY, null);
            var cats = recipes && recipes.categories ? recipes.categories : [];
            cats.forEach(function (cat) {
                (cat.recipes || []).forEach(function (rec) {
                    if (!rec) return;
                    var allergens = normalizeAllergens(rec.allergens);
                    var note = String(rec.allergenNote || '').trim();
                    if (!allergens.length && !note) return;
                    var mid = rec.menuItemId != null && rec.menuItemId !== '' ? String(rec.menuItemId) : '';
                    if (mid && used[mid]) return; // already covered via menu
                    var rid = 'recipe:' + String(rec.id || uid());
                    if (used[rid]) return;
                    used[rid] = true;
                    rows.push({
                        id: rid,
                        name: String(rec.title || rec.name || 'Recipe').trim(),
                        category: (cat && cat.id) || 'other',
                        price: null,
                        allergens: allergens,
                        allergenNote: note,
                        source: 'recipe',
                        recipeId: rec.id,
                        tagged: true
                    });
                });
            });
        } catch (e) {}

        return rows.sort(function (a, b) {
            var c = (a.category || '').localeCompare(b.category || '');
            if (c) return c;
            return String(a.name).localeCompare(String(b.name), undefined, { sensitivity: 'base' });
        });
    }

    /**
     * Write allergens onto a menu item (local) AND house overlay.
     * @returns {{ menu, overlay }}
     */
    function setItemAllergens(menuId, allergens, allergenNote, nameOpt) {
        menuId = String(menuId || '');
        if (!menuId || menuId.indexOf('recipe:') === 0) {
            return { menu: loadMenuRaw(), overlay: loadOverlayLocal() };
        }
        allergens = normalizeAllergens(allergens);
        allergenNote = String(allergenNote || '').trim();

        var menu = loadMenuRaw();
        var found = null;
        menu.items.forEach(function (it) {
            if (String(it.id) === menuId) found = it;
        });
        if (found) {
            found.allergens = allergens;
            found.allergenNote = allergenNote;
            if (nameOpt) found.name = String(nameOpt).trim() || found.name;
            saveMenuRaw(menu);
        }

        var overlay = loadOverlayLocal();
        if (!allergens.length && !allergenNote) {
            delete overlay.items[menuId];
        } else {
            overlay.items[menuId] = {
                allergens: allergens,
                allergenNote: allergenNote,
                name: (found && found.name) || nameOpt || (overlay.items[menuId] && overlay.items[menuId].name) || undefined
            };
        }
        overlay = saveOverlayLocal(overlay);
        return { menu: menu, overlay: overlay };
    }

    function clearItemAllergens(menuId) {
        return setItemAllergens(menuId, [], '', null);
    }

    function labelFor(id) {
        for (var i = 0; i < BIG9.length; i++) {
            if (BIG9[i].id === id) return BIG9[i].label;
        }
        return id;
    }

    function shortFor(id) {
        for (var i = 0; i < BIG9.length; i++) {
            if (BIG9[i].id === id) return BIG9[i].short;
        }
        return id;
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
     * @param {function} opts.setState
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
                    var next = normalizeOverlay(payload);
                    saveOverlayLocal(next);
                    if (typeof opts.setState === 'function') opts.setState(next, { remote: true });
                } finally {
                    applying = false;
                }
            }
        });

        shared
            .bootstrap(
                function () {
                    return typeof opts.getState === 'function'
                        ? normalizeOverlay(opts.getState())
                        : loadOverlayLocal();
                },
                function (payload) {
                    if (!payload) return;
                    applying = true;
                    try {
                        var next = normalizeOverlay(payload);
                        saveOverlayLocal(next);
                        if (typeof opts.setState === 'function') opts.setState(next, { remote: true });
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
                var next = normalizeOverlay(
                    state || (opts.getState && opts.getState()) || loadOverlayLocal()
                );
                next.structureAt = Date.now();
                shared.queuePush(next);
            }
        };
    }

    /** Build checkbox HTML for Big 9 (ids prefixed). */
    function renderCheckboxGrid(selected, idPrefix, className) {
        selected = normalizeAllergens(selected);
        idPrefix = idPrefix || 'alg';
        className = className || 'allergen-checks';
        return (
            '<div class="' + className + '">' +
            BIG9.map(function (a) {
                var checked = selected.indexOf(a.id) !== -1 ? ' checked' : '';
                return (
                    '<label class="allergen-check">' +
                    '<input type="checkbox" data-allergen="' +
                    a.id +
                    '" id="' +
                    idPrefix +
                    '-' +
                    a.id +
                    '"' +
                    checked +
                    '> ' +
                    '<span>' +
                    a.label +
                    '</span></label>'
                );
            }).join('') +
            '</div>'
        );
    }

    function readCheckboxes(root) {
        var out = [];
        if (!root) return out;
        root.querySelectorAll('input[data-allergen]').forEach(function (el) {
            if (el.checked) out.push(el.getAttribute('data-allergen'));
        });
        return normalizeAllergens(out);
    }

    global.PbjAllergenMenu = {
        LS_KEY: LS_KEY,
        SHARED_KEY: SHARED_KEY,
        MENU_KEY: MENU_KEY,
        BIG9: BIG9,
        BIG9_IDS: BIG9_IDS,
        uid: uid,
        normalizeAllergens: normalizeAllergens,
        normalizeOverlay: normalizeOverlay,
        loadOverlayLocal: loadOverlayLocal,
        saveOverlayLocal: saveOverlayLocal,
        loadMenuItems: loadMenuItems,
        loadMenuRaw: loadMenuRaw,
        saveMenuRaw: saveMenuRaw,
        mergeMatrixRows: mergeMatrixRows,
        setItemAllergens: setItemAllergens,
        clearItemAllergens: clearItemAllergens,
        labelFor: labelFor,
        shortFor: shortFor,
        wire: wire,
        setSyncPill: setSyncPill,
        renderCheckboxGrid: renderCheckboxGrid,
        readCheckboxes: readCheckboxes
    };
})(window);
