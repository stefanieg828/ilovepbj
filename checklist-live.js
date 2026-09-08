/**
 * Live restaurant-group checklist wiring for static Showtime-style pages.
 * Expects .check-item[data-key] markup and optional notes textarea.
 */
(function (global) {
    'use strict';

    function emptyPayload() {
        return { structureAt: 1, checks: {}, notes: '', notesAt: 0 };
    }

    function normalizePayload(raw) {
        var p = emptyPayload();
        if (!raw || typeof raw !== 'object') return p;

        // Legacy flat map: { "opening-0": true }
        var looksLegacy = !raw.checks && !raw.lists && !raw.stations;
        if (looksLegacy) {
            Object.keys(raw).forEach(function (k) {
                if (k === 'structureAt' || k === 'notes' || k === 'notesAt') return;
                var v = raw[k];
                if (typeof v === 'boolean' || v === 0 || v === 1) {
                    p.checks[k] = { done: !!v, doneUpdatedAt: v ? 1 : 0 };
                }
            });
            return p;
        }

        p.structureAt = raw.structureAt || 1;
        p.notes = typeof raw.notes === 'string' ? raw.notes : '';
        p.notesAt = raw.notesAt || 0;
        p.checks = {};
        if (raw.checks && typeof raw.checks === 'object') {
            Object.keys(raw.checks).forEach(function (k) {
                var v = raw.checks[k];
                if (!isArrayishObject(v)) {
                    p.checks[k] = { done: !!v, doneUpdatedAt: v ? 1 : 0 };
                } else {
                    p.checks[k] = {
                        done: !!v.done,
                        doneUpdatedAt: v.doneUpdatedAt || (v.done ? 1 : 0)
                    };
                }
            });
        }
        return p;
    }

    function isArrayishObject(v) {
        return v && typeof v === 'object' && !Array.isArray(v);
    }

    /**
     * @param {object} opts
     * @param {string} opts.sharedKey
     * @param {string} opts.localKey
     * @param {boolean} opts.isSweet
     * @param {function} opts.getActiveTab - returns current tab id
     * @param {object} opts.panels - map tabId -> element
     * @param {HTMLElement} [opts.notesEl]
     * @param {HTMLElement} [opts.notesStatusEl]
     * @param {HTMLElement} opts.progressCount
     * @param {HTMLElement} opts.progressFill
     * @param {HTMLElement} opts.completeBanner
     * @param {HTMLElement} [opts.syncPill]
     * @param {HTMLElement} [opts.syncPillText]
     * @param {HTMLElement} [opts.resetBtn]
     * @param {HTMLElement} [opts.printBtn]
     * @param {string} [opts.printTitle]
     */
    function mount(opts) {
        var payload = emptyPayload();
        var shared = null;
        var applyingRemote = false;
        var notesTimer = null;
        var isSweet = !!opts.isSweet;

        function loadLocal() {
            try {
                return normalizePayload(JSON.parse(localStorage.getItem(opts.localKey) || 'null'));
            } catch (e) {
                return emptyPayload();
            }
        }

        function saveLocal() {
            localStorage.setItem(opts.localKey, JSON.stringify(payload));
        }

        function setSyncPill(info) {
            var pill = opts.syncPill;
            var text = opts.syncPillText;
            if (!pill || !text) return;
            pill.classList.remove('offline', 'syncing');
            if (info.kind === 'offline') pill.classList.add('offline');
            if (info.kind === 'syncing') pill.classList.add('syncing');
            text.textContent = info.text || '';
        }

        function isDone(key) {
            return !!(payload.checks[key] && payload.checks[key].done);
        }

        function applyToDom() {
            document.querySelectorAll('.check-item[data-key]').forEach(function (item) {
                var key = item.dataset.key;
                var checked = isDone(key);
                item.classList.toggle('done', checked);
                var input = item.querySelector('input');
                if (input) input.checked = checked;
            });
            if (opts.notesEl && document.activeElement !== opts.notesEl) {
                opts.notesEl.value = payload.notes || '';
            }
            updateProgress();
        }

        function updateProgress() {
            var tab = opts.getActiveTab();
            var panel = opts.panels[tab];
            if (!panel) return;
            var items = panel.querySelectorAll('.check-item[data-key]');
            var total = items.length;
            var done = 0;
            items.forEach(function (item) {
                if (item.classList.contains('done')) done++;
            });
            if (opts.progressCount) opts.progressCount.textContent = done + ' / ' + total;
            if (opts.progressFill) opts.progressFill.style.width = total ? ((done / total) * 100) + '%' : '0%';
            if (opts.completeBanner) opts.completeBanner.classList.toggle('show', total > 0 && done === total);
        }

        function pushKitchen() {
            saveLocal();
            if (shared) shared.queuePush(payload);
        }

        function applyRemote(remote) {
            if (!remote) return;
            applyingRemote = true;
            payload = normalizePayload(remote);
            saveLocal();
            applyToDom();
            applyingRemote = false;
        }

        // Toggle handlers
        document.querySelectorAll('.check-item[data-key]').forEach(function (item) {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                var key = item.dataset.key;
                var input = item.querySelector('input');
                var next = !(input && input.checked);
                if (input) input.checked = next;
                item.classList.toggle('done', next);
                payload.checks[key] = {
                    done: next,
                    doneUpdatedAt: Date.now()
                };
                updateProgress();
                pushKitchen();
            });
        });

        if (opts.resetBtn) {
            opts.resetBtn.addEventListener('click', function () {
                var tab = opts.getActiveTab();
                var panel = opts.panels[tab];
                if (!panel) return;
                var now = Date.now();
                panel.querySelectorAll('.check-item[data-key]').forEach(function (item) {
                    var key = item.dataset.key;
                    item.classList.remove('done');
                    var input = item.querySelector('input');
                    if (input) input.checked = false;
                    payload.checks[key] = { done: false, doneUpdatedAt: now };
                });
                updateProgress();
                pushKitchen();
            });
        }

        if (opts.notesEl) {
            opts.notesEl.addEventListener('input', function () {
                payload.notes = opts.notesEl.value;
                payload.notesAt = Date.now();
                saveLocal();
                if (opts.notesStatusEl) {
                    opts.notesStatusEl.textContent = isSweet ? 'Saved 💾' : 'Saved';
                    clearTimeout(notesTimer);
                    notesTimer = setTimeout(function () {
                        if (opts.notesStatusEl) opts.notesStatusEl.textContent = '';
                    }, 1200);
                }
                if (shared) shared.queuePush(payload);
            });
        }

        if (opts.printBtn) {
            opts.printBtn.addEventListener('click', function () {
                // expand all panels for print
                Object.keys(opts.panels).forEach(function (k) {
                    if (opts.panels[k]) opts.panels[k].classList.add('active');
                });
                var header = document.getElementById('print-check-header');
                if (header) {
                    var d = new Date();
                    header.textContent = (opts.printTitle || 'Checklist') + ' · ' +
                        d.toLocaleDateString() + ' · ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                setTimeout(function () {
                    window.print();
                    // restore active tab only
                    var tab = opts.getActiveTab();
                    Object.keys(opts.panels).forEach(function (k) {
                        if (opts.panels[k]) opts.panels[k].classList.toggle('active', k === tab);
                    });
                }, 50);
            });
        }

        // Expose for tab changes
        global._pbjChecklistUpdateProgress = updateProgress;

        payload = loadLocal();
        applyToDom();

        if (global.PbjSharedState) {
            shared = new global.PbjSharedState({
                key: opts.sharedKey,
                pollMs: 3000,
                onStatus: setSyncPill,
                onRemote: function (remote) {
                    if (applyingRemote) return;
                    applyRemote(remote);
                }
            });
            shared.bootstrap(
                function () { return payload; },
                function (remote) { applyRemote(remote); }
            ).then(function () {
                shared.startPolling();
            });
        } else {
            setSyncPill({ kind: 'offline', text: isSweet ? 'Local only' : 'Local only' });
        }

        return {
            updateProgress: updateProgress,
            getPayload: function () { return payload; }
        };
    }

    global.PbjChecklistLive = { mount: mount, normalizePayload: normalizePayload };
})(window);
