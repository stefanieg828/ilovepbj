/**
 * Shared helpers for Jelly Jar channels (team roster + kitchen sync).
 */
(function (global) {
    'use strict';

    var TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];

    function uid() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
    }

    function esc(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function when(ts) {
        try {
            return new Date(ts).toLocaleString([], {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        } catch (e) {
            return '';
        }
    }

    function loadTeam() {
        for (var i = 0; i < TEAM_KEYS.length; i++) {
            try {
                var r = JSON.parse(localStorage.getItem(TEAM_KEYS[i]) || 'null');
                if (r && Array.isArray(r.people) && r.people.length) {
                    return r.people
                        .filter(function (p) {
                            return p && p.status !== 'inactive' && String(p.name || '').trim();
                        })
                        .map(function (p) {
                            var roles = [];
                            if (Array.isArray(p.roles)) {
                                p.roles.forEach(function (role) {
                                    if (role && typeof role === 'object') role = role.role || role.name || '';
                                    role = String(role || '').trim();
                                    if (role && roles.indexOf(role) === -1) roles.push(role);
                                });
                            }
                            if (p.role) {
                                var single = String(p.role).trim();
                                if (single && roles.indexOf(single) === -1) roles.unshift(single);
                            }
                            return {
                                id: String(p.id || ''),
                                name: String(p.name || '').trim(),
                                roles: roles
                            };
                        })
                        .sort(function (a, b) {
                            return a.name.localeCompare(b.name);
                        });
                }
            } catch (e) {}
        }
        return [];
    }

    function fillAuthorSelect(selectEl, currentName, isSweet) {
        if (!selectEl) return;
        var team = loadTeam();
        var cur = String(currentName || '').trim();
        var html = '';
        if (cur) {
            html += '<option value="' + esc(cur) + '">' + esc(cur) + (isSweet ? ' (you)' : ' (you)') + '</option>';
        }
        team.forEach(function (p) {
            if (cur && p.name.toLowerCase() === cur.toLowerCase()) return;
            html += '<option value="' + esc(p.name) + '">' + esc(p.name) + '</option>';
        });
        html += '<option value="__custom__">' + (isSweet ? 'Other name…' : 'Other name…') + '</option>';
        selectEl.innerHTML = html;
        if (cur) selectEl.value = cur;
    }

    function attachAuthorPicker(selectId, inputId, defaultName, isSweet) {
        var sel = document.getElementById(selectId);
        var inp = document.getElementById(inputId);
        if (!sel || !inp) return;
        fillAuthorSelect(sel, defaultName, isSweet);
        inp.style.display = 'none';
        inp.value = defaultName || '';
        sel.addEventListener('change', function () {
            if (sel.value === '__custom__') {
                inp.style.display = 'block';
                inp.value = '';
                inp.focus();
            } else {
                inp.style.display = 'none';
                inp.value = sel.value;
            }
        });
    }

    function getAuthorValue(selectId, inputId, fallback) {
        var sel = document.getElementById(selectId);
        var inp = document.getElementById(inputId);
        if (sel && sel.value === '__custom__' && inp) {
            return (inp.value || '').trim() || fallback || 'Team';
        }
        if (sel && sel.value && sel.value !== '__custom__') return sel.value.trim();
        if (inp && inp.value) return inp.value.trim();
        return fallback || 'Team';
    }

    function setSyncPill(info) {
        var pill = document.getElementById('sync-pill');
        var text = document.getElementById('sync-pill-text');
        if (!pill || !text) return;
        pill.classList.remove('offline', 'syncing');
        if (info.kind === 'offline') pill.classList.add('offline');
        if (info.kind === 'syncing') pill.classList.add('syncing');
        text.textContent = info.text || '';
    }

    /**
     * Wire PbjSharedState if available.
     * getState / applyRemote / onLocalChange callbacks.
     */
    function wireShared(sharedKey, getState, applyRemote) {
        if (!global.PbjSharedState) {
            setSyncPill({ kind: 'offline', text: 'Local only' });
            return null;
        }
        var applying = false;
        var shared = new global.PbjSharedState({
            key: sharedKey,
            pollMs: 4000,
            onStatus: setSyncPill,
            onRemote: function (payload) {
                if (applying) return;
                applying = true;
                try {
                    applyRemote(payload);
                } finally {
                    applying = false;
                }
            }
        });
        shared.bootstrap(
            function () {
                return getState();
            },
            function (payload) {
                applying = true;
                try {
                    applyRemote(payload);
                } finally {
                    applying = false;
                }
            }
        ).then(function () {
            shared.startPolling();
        });
        return {
            push: function (state) {
                if (!applying && shared) shared.queuePush(state);
            },
            isApplying: function () {
                return applying;
            }
        };
    }

    function toast(msg, fallback) {
        var el = document.getElementById('toast');
        if (!el) return;
        el.textContent = typeof msg === 'string' ? msg : fallback || 'Saved';
        el.classList.add('show');
        setTimeout(function () {
            el.classList.remove('show');
        }, 1100);
    }

    global.Jelly = {
        uid: uid,
        esc: esc,
        when: when,
        loadTeam: loadTeam,
        fillAuthorSelect: fillAuthorSelect,
        attachAuthorPicker: attachAuthorPicker,
        getAuthorValue: getAuthorValue,
        setSyncPill: setSyncPill,
        wireShared: wireShared,
        toast: toast
    };
})(window);
