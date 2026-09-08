/**
 * Restaurant-shared state client for ilovepbj ops checklists.
 * Polls server, pushes on save, merges concurrent check-offs.
 */
(function (global) {
    'use strict';

    function businessDate() {
        var d = new Date();
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function fetchJson(url, options, timeoutMs) {
        timeoutMs = timeoutMs || 12000;
        var ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
        var timer = null;
        var opts = options || {};
        if (ctrl) {
            opts.signal = ctrl.signal;
            timer = setTimeout(function () { try { ctrl.abort(); } catch (e) {} }, timeoutMs);
        }
        return fetch(url, opts)
            .then(function (res) {
                return res.text().then(function (text) {
                    var data = null;
                    try { data = text ? JSON.parse(text) : null; } catch (e) {
                        throw new Error('bad_json');
                    }
                    return { res: res, data: data };
                });
            })
            .finally(function () {
                if (timer) clearTimeout(timer);
            });
    }

    function SharedState(options) {
        this.key = options.key;
        this.apiUrl = options.apiUrl || 'shared-state-api.php';
        this.pollMs = options.pollMs || 3000;
        this.onRemote = typeof options.onRemote === 'function' ? options.onRemote : function () {};
        this.onStatus = typeof options.onStatus === 'function' ? options.onStatus : function () {};
        this.version = 0;
        this.restaurantId = null;
        // Optional fixed business date (e.g. evergreen inventory master "2000-01-01")
        this.date = options.date || businessDate();
        this._timer = null;
        this._pushTimer = null;
        this._pushing = false;
        this._pending = null;
        this._pendingGen = 0;
        this._activePushGen = 0;
        this._lastLocalHash = '';
        this.enabled = true;
        this.online = true;
        this._visHandler = null;
    }

    SharedState.prototype._setStatus = function (kind, text) {
        this.onStatus({ kind: kind, text: text, restaurantId: this.restaurantId, version: this.version });
    };

    SharedState.prototype._hash = function (payload) {
        try {
            return JSON.stringify(payload);
        } catch (e) {
            return String(Date.now());
        }
    };

    SharedState.prototype.fetchRemote = function () {
        var self = this;
        var url = this.apiUrl + '?key=' + encodeURIComponent(this.key) + '&date=' + encodeURIComponent(this.date) + '&_=' + Date.now();
        return fetchJson(url, { credentials: 'same-origin', cache: 'no-store', method: 'GET' }, 10000)
            .then(function (pack) {
                var data = pack.data;
                if (!pack.res.ok || !data || !data.ok) throw new Error((data && data.error) || 'http');
                self.online = true;
                self.restaurantId = data.restaurantId;
                if (data.exists && data.payload) {
                    // do not clobber version mid-push; pollOnce handles compare
                    return data;
                }
                return data;
            })
            .catch(function (err) {
                self.online = false;
                self._setStatus('offline', 'Offline — saved on this device only');
                throw err;
            });
    };

    /**
     * Bootstrap: pull server state if any; otherwise seed server from local.
     */
    SharedState.prototype.bootstrap = function (getLocal, applyRemote) {
        var self = this;
        this._setStatus('syncing', 'Syncing with kitchen…');
        return this.fetchRemote()
            .then(function (data) {
                if (data.exists && data.payload) {
                    self.version = data.version || 0;
                    applyRemote(data.payload, data.version);
                    self._lastLocalHash = self._hash(data.payload);
                    self._setStatus('live', 'Live · kitchen sync on');
                    return { source: 'server', data: data };
                }
                var local = getLocal();
                if (local && typeof local === 'object') {
                    return self.push(local, { force: true }).then(function (saved) {
                        self._setStatus('live', 'Live · kitchen sync on');
                        return { source: 'seeded', data: saved };
                    });
                }
                self.version = 0;
                self._setStatus('live', 'Live · kitchen sync on');
                return { source: 'empty', data: data };
            })
            .catch(function () {
                self._setStatus('offline', 'Offline — saved on this device only');
                return { source: 'offline' };
            });
    };

    SharedState.prototype.push = function (payload, opts) {
        var self = this;
        opts = opts || {};
        if (!payload || typeof payload !== 'object') {
            return Promise.resolve(null);
        }

        // Always keep latest intended payload
        this._pending = payload;
        this._pendingGen += 1;
        var myGen = this._pendingGen;

        if (this._pushing) {
            // Another push in flight — will flush this pending when it finishes
            return Promise.resolve(null);
        }

        this._pushing = true;
        this._activePushGen = myGen;
        var sentPayload = payload;
        var sentHash = this._hash(payload);
        var body = {
            key: this.key,
            date: this.date,
            payload: sentPayload,
            baseVersion: this.version,
            force: !!opts.force
        };

        this._setStatus('syncing', 'Saving to kitchen…');

        return fetchJson(this.apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }, 15000)
            .then(function (pack) {
                self._pushing = false;
                var data = pack.data;
                if (!pack.res.ok || !data || !data.ok) {
                    self.online = false;
                    self._setStatus('offline', 'Couldn’t reach kitchen — local only');
                    return null;
                }

                self.online = true;
                self.version = data.version || self.version;
                self.restaurantId = data.restaurantId || self.restaurantId;

                // Prefer server payload as truth (includes merges)
                var resultPayload = data.payload || sentPayload;
                self._lastLocalHash = self._hash(resultPayload);

                // Apply merged / server result once — pages must NOT re-queuePush from this
                if (data.merged && typeof self.onRemote === 'function') {
                    self.onRemote(resultPayload, data.version);
                }

                // Only push again if user edited AFTER this push started
                if (self._pendingGen > myGen && self._pending) {
                    var next = self._pending;
                    // Don't clear pendingGen — next push will track
                    self._setStatus('syncing', 'Saving to kitchen…');
                    return self.push(next, opts);
                }

                self._pending = null;
                self._setStatus('live', 'Live · kitchen sync on');
                return data;
            })
            .catch(function () {
                self._pushing = false;
                self.online = false;
                self._setStatus('offline', 'Offline — saved on this device only');
                // Retry once later if we still have pending edits
                if (self._pending) {
                    clearTimeout(self._pushTimer);
                    self._pushTimer = setTimeout(function () {
                        if (self._pending && !self._pushing) self.push(self._pending, opts);
                    }, 2500);
                }
                return null;
            });
    };

    /** Debounced push after local edits */
    SharedState.prototype.queuePush = function (payload) {
        var self = this;
        this._pending = payload;
        this._pendingGen += 1;
        clearTimeout(this._pushTimer);
        this._pushTimer = setTimeout(function () {
            if (self._pending) self.push(self._pending);
        }, 400);
    };

    SharedState.prototype.pollOnce = function () {
        var self = this;
        if (this._pushing) return Promise.resolve();
        return this.fetchRemote()
            .then(function (data) {
                if (!data.exists || !data.payload) {
                    if (!self._pushing) self._setStatus('live', 'Live · kitchen sync on');
                    return;
                }
                var remoteVer = data.version || 0;
                if (remoteVer > self.version) {
                    self.version = remoteVer;
                    self._lastLocalHash = self._hash(data.payload);
                    self.onRemote(data.payload, remoteVer);
                    self._setStatus('live', 'Updated from kitchen');
                    setTimeout(function () {
                        if (self.online && !self._pushing) self._setStatus('live', 'Live · kitchen sync on');
                    }, 1400);
                } else if (!self._pushing) {
                    self._setStatus('live', 'Live · kitchen sync on');
                }
            })
            .catch(function () { /* status already offline */ });
    };

    SharedState.prototype.startPolling = function () {
        var self = this;
        this.stopPolling();
        this._timer = setInterval(function () {
            if (document.hidden) return;
            if (self._pushing) return;
            self.pollOnce();
        }, this.pollMs);
        this._visHandler = function () {
            if (!document.hidden) self.pollOnce();
        };
        document.addEventListener('visibilitychange', this._visHandler);
    };

    SharedState.prototype.stopPolling = function () {
        if (this._timer) {
            clearInterval(this._timer);
            this._timer = null;
        }
        if (this._visHandler) {
            document.removeEventListener('visibilitychange', this._visHandler);
            this._visHandler = null;
        }
    };

    global.PbjSharedState = SharedState;
    global.pbjBusinessDate = businessDate;
})(window);
