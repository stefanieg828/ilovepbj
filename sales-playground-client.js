/**
 * Playground client (DEMO-PBJ + SALES-PBJ): when reset epoch changes,
 * wipe local pbj_* storage so deleted/edited pages reload gold starters.
 */
(function () {
    'use strict';
    var STORAGE_EPOCH = 'pbj_sales_reset_epoch';
    var STORAGE_FLAG = 'pbj_sales_playground_active';

    function clearPbjLocal() {
        var keys = [];
        try {
            for (var i = 0; i < localStorage.length; i++) {
                var k = localStorage.key(i);
                if (!k) continue;
                // App module state (not auth/session)
                if (k.indexOf('pbj_') === 0 || k.indexOf('PBJ_') === 0) {
                    keys.push(k);
                }
            }
            keys.forEach(function (k) {
                try { localStorage.removeItem(k); } catch (e) {}
            });
            // Also session flags some pages use
            try {
                for (var j = sessionStorage.length - 1; j >= 0; j--) {
                    var sk = sessionStorage.key(j);
                    if (sk && sk.indexOf('pbj_') === 0 && sk.indexOf('pbj_sales_reloaded_') !== 0) {
                        sessionStorage.removeItem(sk);
                    }
                }
            } catch (e2) {}
        } catch (e) {}
        return keys.length;
    }

    function isPlaygroundMeta(meta) {
        return !!(meta && (meta.sales_playground || meta.playground_reset));
    }

    function applyMeta(meta) {
        if (!meta || !meta.ok) return;
        if (!isPlaygroundMeta(meta)) {
            try {
                localStorage.removeItem(STORAGE_FLAG);
            } catch (e) {}
            return;
        }
        try {
            localStorage.setItem(STORAGE_FLAG, '1');
        } catch (e) {}

        var serverEpoch = parseInt(meta.sales_reset_epoch || 0, 10) || 0;
        var localEpoch = 0;
        try {
            localEpoch = parseInt(localStorage.getItem(STORAGE_EPOCH) || '0', 10) || 0;
        } catch (e) {}

        if (serverEpoch > 0 && serverEpoch !== localEpoch) {
            var n = clearPbjLocal();
            try {
                localStorage.setItem(STORAGE_EPOCH, String(serverEpoch));
                localStorage.setItem(STORAGE_FLAG, '1');
            } catch (e) {}
            // Soft reload once so pages re-bootstrap from shared starters
            try {
                if (!sessionStorage.getItem('pbj_sales_reloaded_' + serverEpoch)) {
                    sessionStorage.setItem('pbj_sales_reloaded_' + serverEpoch, '1');
                    // Avoid reload loops on login/public pages
                    if (document.body && document.querySelector('.bottom-nav')) {
                        window.location.reload();
                        return;
                    }
                }
            } catch (e) {}
            if (typeof console !== 'undefined' && console.info) {
                console.info('[playground-reset] applied, cleared ' + n + ' local keys, epoch=' + serverEpoch);
            }
        }
    }

    function poll() {
        fetch('/sales-playground-api.php?_=' + Date.now(), {
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(function (r) { return r.json(); })
            .then(applyMeta)
            .catch(function () { /* offline ok */ });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', poll);
    } else {
        poll();
    }
    // Re-check every 10 minutes (in case reset runs while tab is open overnight)
    setInterval(poll, 10 * 60 * 1000);
})();
