/**
 * Client-side role permissions for ilovepbj ops.
 * Usage: PbjPerms.ready.then(function () { if (PbjPerms.can('foh.floor.edit_layout')) ... })
 * Markup: data-perm="key" hides if denied; data-perm-any="a,b" if any; data-perm-disable only disables.
 */
(function (global) {
    'use strict';

    var grants = {};
    var role = 'foh';
    var roleLabel = 'FOH';
    var canManage = false;
    var loaded = false;
    var failOpen = false; // after load, unknown key = deny; before load, can() waits

    function can(key) {
        if (!key) return true;
        if (!loaded) return failOpen;
        if (role === 'owner') return true;
        return !!grants[key];
    }

    function canAny(keys) {
        if (!keys || !keys.length) return true;
        for (var i = 0; i < keys.length; i++) {
            if (can(keys[i])) return true;
        }
        return false;
    }

    function canAll(keys) {
        if (!keys || !keys.length) return true;
        for (var i = 0; i < keys.length; i++) {
            if (!can(keys[i])) return false;
        }
        return true;
    }

    function applyDom(root) {
        root = root || document;
        if (!root.querySelectorAll) return;

        root.querySelectorAll('[data-perm]').forEach(function (el) {
            var key = el.getAttribute('data-perm');
            var ok = can(key);
            var mode = el.getAttribute('data-perm-mode') || 'hide';
            if (mode === 'disable') {
                el.disabled = !ok;
                el.setAttribute('aria-disabled', ok ? 'false' : 'true');
                if (!ok) el.title = el.title || 'No permission';
                el.classList.toggle('perm-denied', !ok);
            } else {
                el.style.display = ok ? '' : 'none';
                if (ok) el.removeAttribute('hidden');
                else el.setAttribute('hidden', 'hidden');
            }
        });

        root.querySelectorAll('[data-perm-any]').forEach(function (el) {
            var raw = el.getAttribute('data-perm-any') || '';
            var keys = raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
            var ok = canAny(keys);
            el.style.display = ok ? '' : 'none';
            if (ok) el.removeAttribute('hidden');
            else el.setAttribute('hidden', 'hidden');
        });

        root.querySelectorAll('[data-perm-disable]').forEach(function (el) {
            var key = el.getAttribute('data-perm-disable');
            var ok = can(key);
            el.disabled = !ok;
            el.classList.toggle('perm-denied', !ok);
            if (!ok) el.title = el.title || 'No permission';
        });
    }

    function applyAll() {
        applyDom(document);
        try {
            document.documentElement.setAttribute('data-pbj-role', role);
        } catch (e) {}
    }

    var readyResolve;
    var ready = new Promise(function (resolve) {
        readyResolve = resolve;
    });

    function ingest(data) {
        if (!data || !data.ok) {
            failOpen = true;
            loaded = true;
            readyResolve(api);
            return api;
        }
        role = data.role || 'foh';
        roleLabel = data.roleLabel || role;
        grants = data.grants || {};
        canManage = !!data.canManage;
        loaded = true;
        failOpen = false;
        applyAll();
        readyResolve(api);
        try {
            document.dispatchEvent(new CustomEvent('pbj-perms-ready', { detail: api }));
        } catch (e) {}
        return api;
    }

    function load() {
        return fetch('permissions-api.php?labels=1', {
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(function (r) { return r.json(); })
            .then(ingest)
            .catch(function () {
                failOpen = true;
                loaded = true;
                readyResolve(api);
                return api;
            });
    }

    var api = {
        ready: ready,
        can: can,
        canAny: canAny,
        canAll: canAll,
        applyDom: applyDom,
        applyAll: applyAll,
        load: load,
        get role() { return role; },
        get roleLabel() { return roleLabel; },
        get grants() { return grants; },
        get canManage() { return canManage; },
        get loaded() { return loaded; }
    };

    global.PbjPerms = api;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { load(); });
    } else {
        load();
    }
})(window);
