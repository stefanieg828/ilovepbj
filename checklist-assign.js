/**
 * Shared assignee helpers for ilovepbj checklists (FOH/BOH/prep/cleaning).
 * Optional assign-to name on tasks + All/Mine/Unassigned filters.
 */
(function (global) {
    'use strict';

    function loadTeamNames(userName) {
        var names = [];
        var keys = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
        for (var i = 0; i < keys.length; i++) {
            try {
                var r = JSON.parse(localStorage.getItem(keys[i]) || 'null');
                if (r && Array.isArray(r.people)) {
                    r.people.forEach(function (p) {
                        if (p && p.status !== 'inactive' && (p.name || '').trim()) {
                            names.push(String(p.name).trim());
                        }
                    });
                    if (names.length) break;
                }
            } catch (e) {}
        }
        if (userName && names.indexOf(userName) === -1) names.unshift(userName);
        return names.sort(function (a, b) { return a.localeCompare(b); });
    }

    function fillDatalist(datalistId, userName, escFn) {
        var dl = document.getElementById(datalistId);
        if (!dl) return;
        var esc = escFn || function (s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        };
        dl.innerHTML = loadTeamNames(userName).map(function (n) {
            return '<option value="' + esc(n) + '"></option>';
        }).join('');
    }

    function norm(s) {
        return String(s || '').trim().toLowerCase();
    }

    function isMine(item, userName) {
        if (!item || !item.assignedTo) return false;
        return norm(item.assignedTo) === norm(userName);
    }

    function matchesFilter(item, filter, userName) {
        if (filter === 'mine') return isMine(item, userName);
        if (filter === 'unassigned') return !String(item.assignedTo || '').trim();
        return true;
    }

    function filterItems(items, filter, userName) {
        return (items || []).filter(function (it) {
            return matchesFilter(it, filter, userName);
        });
    }

    function paintFilterChips(containerSel, filter) {
        var root = typeof containerSel === 'string' ? document.querySelector(containerSel) : containerSel;
        if (!root) return;
        root.querySelectorAll('[data-assign-filter]').forEach(function (chip) {
            chip.classList.toggle('active', chip.getAttribute('data-assign-filter') === filter);
        });
    }

    function badgeHtml(item, userName, isSweet, escFn) {
        if (!item || !item.assignedTo) return '';
        var esc = escFn || function (s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        };
        var mine = isMine(item, userName);
        return '<span class="assign-badge' + (mine ? ' mine' : '') + '">' +
            (isSweet ? '👤 ' : '') + esc(item.assignedTo) +
            (mine ? (isSweet ? ' · you' : ' · you') : '') +
            '</span>';
    }

    function ensureItemFields(item) {
        if (!item) return item;
        if (item.assignedTo == null) item.assignedTo = '';
        if (typeof item.done !== 'boolean') item.done = !!item.done;
        if (item.doneUpdatedAt == null) item.doneUpdatedAt = 0;
        if (item.photoAt == null) item.photoAt = 0;
        return item;
    }

    /** Inject filter chip row before an element if missing */
    function ensureFilterRow(beforeEl, isSweet) {
        if (document.getElementById('assign-filters')) return document.getElementById('assign-filters');
        if (!beforeEl || !beforeEl.parentNode) return null;
        var wrap = document.createElement('div');
        wrap.className = 'assign-filters no-print';
        wrap.id = 'assign-filters';
        wrap.innerHTML =
            '<button type="button" class="filter-chip active" data-assign-filter="all">' +
            (isSweet ? 'All tasks' : 'All') + '</button>' +
            '<button type="button" class="filter-chip" data-assign-filter="mine">' +
            (isSweet ? 'Mine' : 'Mine') + '</button>' +
            '<button type="button" class="filter-chip" data-assign-filter="unassigned">' +
            (isSweet ? 'Unassigned' : 'Unassigned') + '</button>';
        beforeEl.parentNode.insertBefore(wrap, beforeEl);
        return wrap;
    }

    function wireFilterClicks(getFilter, setFilter, onChange) {
        var root = document.getElementById('assign-filters');
        if (!root || root._pbjAssignWired) return;
        root._pbjAssignWired = true;
        root.addEventListener('click', function (e) {
            var chip = e.target.closest('[data-assign-filter]');
            if (!chip) return;
            setFilter(chip.getAttribute('data-assign-filter') || 'all');
            paintFilterChips(root, getFilter());
            if (onChange) onChange();
        });
    }

    /** Shared CSS for badges + chips (inject once) */
    function ensureStyles(isSweet) {
        if (document.getElementById('pbj-assign-styles')) return;
        var s = document.createElement('style');
        s.id = 'pbj-assign-styles';
        var activeBg = isSweet ? '#E55163' : '#1A2A44';
        var badgeBg = isSweet ? '#FFF5F6' : '#EEF2F8';
        var badgeFg = isSweet ? '#E55163' : '#1A2A44';
        var mineBg = isSweet ? '#E8F8F1' : '#EAF1FA';
        var mineFg = isSweet ? '#1F6B4A' : '#1A2A44';
        s.textContent =
            '.assign-badge{display:inline-block;margin-top:4px;font-size:0.78rem;border-radius:999px;padding:2px 8px;background:' + badgeBg + ';color:' + badgeFg + ';}' +
            '.assign-badge.mine{background:' + mineBg + ';color:' + mineFg + ';}' +
            '.assign-filters{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;}' +
            '.assign-filters .filter-chip,.filter-chip{border:none;border-radius:999px;padding:8px 14px;font-size:0.9rem;cursor:pointer;background:white;box-shadow:0 2px 8px rgba(0,0,0,0.06);color:inherit;}' +
            '.assign-filters .filter-chip.active,.filter-chip.active{background:' + activeBg + ';color:white;}' +
            '@media print{.assign-filters{display:none!important;}}';
        document.head.appendChild(s);
    }

    global.PbjChecklistAssign = {
        loadTeamNames: loadTeamNames,
        fillDatalist: fillDatalist,
        isMine: isMine,
        matchesFilter: matchesFilter,
        filterItems: filterItems,
        paintFilterChips: paintFilterChips,
        badgeHtml: badgeHtml,
        ensureItemFields: ensureItemFields,
        ensureFilterRow: ensureFilterRow,
        wireFilterClicks: wireFilterClicks,
        ensureStyles: ensureStyles,
        norm: norm
    };
})(window);
