/**
 * Work Music — personal multi-service queue + player for the home dashboard.
 * Supports Spotify, YouTube, Apple Music, SoundCloud, and custom links.
 * Transport: Prev / Play-Pause / Next (queue navigation always works;
 * true play/pause via API on YouTube & SoundCloud when possible).
 */
(function (global) {
    'use strict';

    var STORAGE_KEY = 'pbj_dash_music_v2';
    var LEGACY_KEY = 'pbj_dash_music_v1';
    var COLLAPSE_KEY = 'pbj_dash_music_collapsed';

    function uid() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    }

    function esc(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function providerMeta(provider) {
        var map = {
            spotify: { label: 'Spotify', emoji: '🟢' },
            youtube: { label: 'YouTube', emoji: '▶️' },
            apple: { label: 'Apple Music', emoji: '🍎' },
            soundcloud: { label: 'SoundCloud', emoji: '🟠' },
            link: { label: 'Link', emoji: '🔗' }
        };
        return map[provider] || map.link;
    }

    /**
     * Parse a music URL into provider + embed info.
     * @returns {object|null}
     */
    function parseMusic(url) {
        url = String(url || '').trim();
        if (!url) return null;
        if (!/^https?:\/\//i.test(url) && !/^spotify:/i.test(url)) {
            url = 'https://' + url;
        }

        var out = {
            url: url,
            provider: 'link',
            embed: null,
            videoId: null,
            height: 152,
            title: '',
            openLabel: 'Open ↗',
            controllable: null
        };

        // Spotify URI
        var spUri = url.match(/^spotify:(track|album|playlist|episode|show):([a-zA-Z0-9]+)/i);
        if (spUri) {
            out.provider = 'spotify';
            out.url = 'https://open.spotify.com/' + spUri[1].toLowerCase() + '/' + spUri[2];
            out.embed = 'https://open.spotify.com/embed/' + spUri[1].toLowerCase() + '/' + spUri[2] + '?utm_source=generator&theme=0';
            out.height = spUri[1].toLowerCase() === 'track' || spUri[1].toLowerCase() === 'episode' ? 152 : 352;
            out.openLabel = 'Open in Spotify ↗';
            out.title = 'Spotify ' + spUri[1].toLowerCase();
            return out;
        }

        // Spotify web
        var sp = url.match(/open\.spotify\.com\/(track|album|playlist|episode|show)\/([a-zA-Z0-9]+)/i);
        if (sp) {
            out.provider = 'spotify';
            out.embed = 'https://open.spotify.com/embed/' + sp[1].toLowerCase() + '/' + sp[2] + '?utm_source=generator&theme=0';
            out.height = (sp[1].toLowerCase() === 'track' || sp[1].toLowerCase() === 'episode') ? 152 : 352;
            out.openLabel = 'Open in Spotify ↗';
            out.title = 'Spotify ' + sp[1].toLowerCase();
            return out;
        }

        // YouTube
        var yt = url.match(/(?:youtube\.com\/watch\?[^#]*v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/|music\.youtube\.com\/watch\?[^#]*v=)([a-zA-Z0-9_-]{6,})/i);
        if (yt) {
            out.provider = 'youtube';
            out.videoId = yt[1];
            out.embed = 'https://www.youtube.com/embed/' + yt[1] + '?enablejsapi=1&rel=0&playsinline=1';
            out.height = 200;
            out.openLabel = 'Open in YouTube ↗';
            out.title = 'YouTube video';
            out.controllable = 'youtube';
            return out;
        }

        // Apple Music
        var am = url.match(/music\.apple\.com\/([a-z]{2})\/(album|playlist|song|station)\/([^/?#]+)/i)
            || url.match(/music\.apple\.com\/([a-z]{2})\/(album|playlist|song)\/[^/]+\/(\d+)/i);
        if (/music\.apple\.com\//i.test(url)) {
            out.provider = 'apple';
            out.openLabel = 'Open in Apple Music ↗';
            out.title = 'Apple Music';
            // Official embed path when we can extract locale + type + id
            var amEmbed = url.match(/music\.apple\.com\/([a-z]{2})\/(album|playlist|song)\/(?:[^/]+\/)?(\d+)/i);
            if (amEmbed) {
                out.embed = 'https://embed.music.apple.com/' + amEmbed[1].toLowerCase() + '/' + amEmbed[2].toLowerCase() + '/' + amEmbed[3];
                out.height = 175;
            } else {
                // Fallback: open-only (playlist slugs without numeric id)
                out.embed = null;
            }
            return out;
        }

        // SoundCloud
        if (/soundcloud\.com\//i.test(url) && !/soundcloud\.com\/?$/i.test(url)) {
            out.provider = 'soundcloud';
            out.embed = 'https://w.soundcloud.com/player/?url=' + encodeURIComponent(url) +
                '&color=%23ff5500&auto_play=false&hide_related=true&show_comments=false&show_user=true&show_reposts=false&show_teaser=false&visual=false';
            out.height = 166;
            out.openLabel = 'Open in SoundCloud ↗';
            out.title = 'SoundCloud';
            out.controllable = 'soundcloud';
            return out;
        }

        // Custom / generic link
        try {
            var u = new URL(url);
            out.title = u.hostname.replace(/^www\./, '') + u.pathname;
            if (out.title.length > 48) out.title = out.title.slice(0, 45) + '…';
        } catch (e) {
            out.title = 'Custom link';
        }
        out.openLabel = 'Open link ↗';
        return out;
    }

    function defaultState() {
        return { queue: [], index: 0, updatedAt: Date.now() };
    }

    function loadState() {
        try {
            var raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
            if (raw && Array.isArray(raw.queue)) {
                raw.index = Math.max(0, Math.min(parseInt(raw.index, 10) || 0, Math.max(0, raw.queue.length - 1)));
                return raw;
            }
        } catch (e) {}
        // Migrate v1 single URL
        try {
            var legacy = JSON.parse(localStorage.getItem(LEGACY_KEY) || 'null');
            if (legacy && legacy.url) {
                var parsed = parseMusic(legacy.url);
                if (parsed) {
                    var st = defaultState();
                    st.queue.push({
                        id: uid(),
                        url: parsed.url,
                        provider: parsed.provider,
                        title: parsed.title || providerMeta(parsed.provider).label,
                        addedAt: Date.now()
                    });
                    saveState(st);
                    return st;
                }
            }
        } catch (e2) {}
        return defaultState();
    }

    function saveState(st) {
        st.updatedAt = Date.now();
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(st));
        } catch (e) {}
    }

    function init(options) {
        options = options || {};
        var fun = !!options.funNames;
        var root = document.getElementById('work-music');
        if (!root) return;

        var state = loadState();
        var ytPlayer = null;
        var ytReady = false;
        var ytApiLoading = false;
        var scWidget = null;
        var isPlaying = false;
        var currentId = null;

        var els = {
            url: document.getElementById('wm-url'),
            title: document.getElementById('wm-title'),
            add: document.getElementById('wm-add'),
            now: document.getElementById('wm-now'),
            nowIco: document.getElementById('wm-now-ico'),
            nowTitle: document.getElementById('wm-now-title'),
            nowSub: document.getElementById('wm-now-sub'),
            prev: document.getElementById('wm-prev'),
            play: document.getElementById('wm-play'),
            next: document.getElementById('wm-next'),
            player: document.getElementById('wm-player'),
            actions: document.getElementById('wm-player-actions'),
            open: document.getElementById('wm-open'),
            removeCur: document.getElementById('wm-remove-current'),
            note: document.getElementById('wm-note'),
            queueList: document.getElementById('wm-queue-list'),
            clearQueue: document.getElementById('wm-clear-queue'),
            svcCustom: document.getElementById('wm-svc-custom'),
            toggle: document.getElementById('wm-toggle'),
            body: document.getElementById('wm-body'),
            headNow: document.getElementById('wm-head-now')
        };

        function isCollapsed() {
            return root.classList.contains('is-collapsed');
        }

        function setCollapsed(collapsed) {
            collapsed = !!collapsed;
            root.classList.toggle('is-collapsed', collapsed);
            if (els.body) {
                if (collapsed) els.body.setAttribute('hidden', '');
                else els.body.removeAttribute('hidden');
            }
            if (els.toggle) {
                els.toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }
            try {
                localStorage.setItem(COLLAPSE_KEY, collapsed ? '1' : '0');
            } catch (e) {}
        }

        function paintHeadNow(item) {
            if (!els.headNow) return;
            if (!item) {
                els.headNow.textContent = '';
                return;
            }
            var meta = providerMeta(item.provider);
            var label = item.title || meta.label;
            els.headNow.textContent = '· ' + label;
        }

        // Restore open/closed preference (default: collapsed to save dashboard space)
        var savedCollapsed = true;
        try {
            var rawCollapse = localStorage.getItem(COLLAPSE_KEY);
            if (rawCollapse === '0') savedCollapsed = false;
            else if (rawCollapse === '1') savedCollapsed = true;
        } catch (e) {}
        setCollapsed(savedCollapsed);

        if (els.toggle) {
            els.toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                setCollapsed(!isCollapsed());
            });
        }

        function currentItem() {
            if (!state.queue.length) return null;
            if (state.index < 0) state.index = 0;
            if (state.index >= state.queue.length) state.index = state.queue.length - 1;
            return state.queue[state.index] || null;
        }

        function setNote(msg) {
            if (els.note) els.note.textContent = msg || '';
        }

        function setPlayIcon(playing) {
            isPlaying = !!playing;
            if (els.play) {
                els.play.textContent = isPlaying ? '⏸' : '▶️';
                els.play.setAttribute('aria-label', isPlaying ? 'Pause' : 'Play');
            }
        }

        function destroyYt() {
            if (ytPlayer && typeof ytPlayer.destroy === 'function') {
                try { ytPlayer.destroy(); } catch (e) {}
            }
            ytPlayer = null;
            ytReady = false;
        }

        function destroySc() {
            scWidget = null;
        }

        function ensureYtApi(cb) {
            if (global.YT && global.YT.Player) {
                cb();
                return;
            }
            var prev = global.onYouTubeIframeAPIReady;
            global.onYouTubeIframeAPIReady = function () {
                if (typeof prev === 'function') {
                    try { prev(); } catch (e) {}
                }
                cb();
            };
            if (ytApiLoading) return;
            ytApiLoading = true;
            var tag = document.createElement('script');
            tag.src = 'https://www.youtube.com/iframe_api';
            tag.async = true;
            document.head.appendChild(tag);
        }

        function ensureScApi(cb) {
            if (global.SC && global.SC.Widget) {
                cb();
                return;
            }
            var existing = document.querySelector('script[data-sc-widget]');
            if (existing) {
                existing.addEventListener('load', function () { cb(); });
                return;
            }
            var tag = document.createElement('script');
            tag.src = 'https://w.soundcloud.com/player/api.js';
            tag.async = true;
            tag.setAttribute('data-sc-widget', '1');
            tag.onload = function () { cb(); };
            document.head.appendChild(tag);
        }

        function paintQueue() {
            if (!els.queueList) return;
            if (!state.queue.length) {
                els.queueList.innerHTML = '<div class="wm-empty">' +
                    (fun ? 'Queue is empty — paste a link and hit Add 💕' : 'Queue is empty. Add a link above.') +
                    '</div>';
                return;
            }
            els.queueList.innerHTML = state.queue.map(function (item, i) {
                var meta = providerMeta(item.provider);
                var active = i === state.index ? ' active' : '';
                return '<div class="wm-qitem' + active + '" data-qi="' + i + '">' +
                    '<span class="wm-q-badge">' + esc(meta.emoji + ' ' + meta.label) + '</span>' +
                    '<span class="wm-q-label">' + esc(item.title || item.url) + '</span>' +
                    '<button type="button" class="wm-q-del" data-del="' + i + '" aria-label="Remove">×</button>' +
                    '</div>';
            }).join('');
        }

        function paintNow(item, parsed) {
            paintHeadNow(item);
            if (!els.now) return;
            if (!item) {
                els.now.hidden = true;
                if (els.nowTitle) els.nowTitle.textContent = fun ? 'Nothing queued yet' : 'Nothing queued';
                if (els.nowSub) els.nowSub.textContent = '';
                if (els.actions) els.actions.hidden = true;
                if (els.player) {
                    els.player.hidden = true;
                    els.player.innerHTML = '';
                }
                setPlayIcon(false);
                setNote('');
                if (els.prev) els.prev.disabled = true;
                if (els.next) els.next.disabled = true;
                if (els.play) els.play.disabled = true;
                return;
            }
            els.now.hidden = false;
            var meta = providerMeta(item.provider);
            if (els.nowIco) els.nowIco.textContent = meta.emoji;
            if (els.nowTitle) els.nowTitle.textContent = item.title || meta.label;
            if (els.nowSub) {
                els.nowSub.textContent = meta.label +
                    (state.queue.length > 1 ? ' · ' + (state.index + 1) + ' / ' + state.queue.length : '');
            }
            if (els.prev) els.prev.disabled = state.queue.length < 2;
            if (els.next) els.next.disabled = state.queue.length < 2;
            if (els.play) els.play.disabled = false;

            if (els.open) {
                els.open.href = item.url;
                els.open.textContent = (parsed && parsed.openLabel) || (fun ? 'Open in app ↗' : 'Open ↗');
            }
            if (els.actions) els.actions.hidden = false;

            // Control notes by provider
            if (parsed && parsed.controllable === 'youtube') {
                setNote(fun
                    ? 'YouTube: Play/Pause works from the buttons. Back/Next skip your queue.'
                    : 'YouTube: transport buttons control playback. Back/Next move the queue.');
            } else if (parsed && parsed.controllable === 'soundcloud') {
                setNote(fun
                    ? 'SoundCloud: Play/Pause works when the embed is ready. Back/Next skip your queue.'
                    : 'SoundCloud: Play/Pause when embed is ready. Back/Next move the queue.');
            } else if (parsed && parsed.provider === 'spotify') {
                setNote(fun
                    ? 'Spotify: use the green embed’s own play button (Spotify rules). Back/Next skip your queue 💕'
                    : 'Spotify: use the embed’s play control. Back/Next move your queue.');
            } else if (parsed && parsed.provider === 'apple') {
                setNote(parsed.embed
                    ? (fun ? 'Apple Music embed loaded — use its controls, or Open in app. Back/Next skip queue.'
                        : 'Apple Music embed — use its controls. Back/Next move the queue.')
                    : (fun ? 'Apple Music link saved — Open in app to play. Back/Next skip queue.'
                        : 'Apple Music: open in app to play. Back/Next move the queue.'));
            } else {
                setNote(fun
                    ? 'Custom link: Open in app to play. Back/Next still skip your queue.'
                    : 'Custom link: open to play. Back/Next move the queue.');
            }
        }

        function loadEmbed(item) {
            destroyYt();
            destroySc();
            setPlayIcon(false);
            if (!item) {
                paintNow(null, null);
                if (els.player) {
                    els.player.hidden = true;
                    els.player.innerHTML = '';
                }
                currentId = null;
                return;
            }
            if (!els.player) {
                paintNow(item, parseMusic(item.url));
                return;
            }
            var parsed = parseMusic(item.url);
            paintNow(item, parsed);
            currentId = item.id;

            if (!parsed || !parsed.embed) {
                // No embed — show open-only card
                els.player.hidden = false;
                els.player.innerHTML =
                    '<div class="wm-empty" style="padding:16px;text-align:center;">' +
                    esc(providerMeta(item.provider).emoji + ' ' + (item.title || item.url)) +
                    '<br><span style="opacity:0.75;font-size:0.85rem;">' +
                    (fun ? 'No embed for this link — use Open in app' : 'No embed available — use Open') +
                    '</span></div>';
                return;
            }

            if (parsed.controllable === 'youtube' && parsed.videoId) {
                els.player.hidden = false;
                els.player.innerHTML = '<div id="wm-yt-host" style="height:' + (parsed.height || 200) + 'px;"></div>';
                ensureYtApi(function () {
                    if (currentId !== item.id) return;
                    try {
                        ytPlayer = new global.YT.Player('wm-yt-host', {
                            height: String(parsed.height || 200),
                            width: '100%',
                            videoId: parsed.videoId,
                            playerVars: {
                                rel: 0,
                                playsinline: 1,
                                modestbranding: 1
                            },
                            events: {
                                onReady: function () {
                                    ytReady = true;
                                },
                                onStateChange: function (ev) {
                                    // 1 playing, 2 paused, 0 ended
                                    if (ev.data === 1) setPlayIcon(true);
                                    else if (ev.data === 2 || ev.data === 0) setPlayIcon(false);
                                    if (ev.data === 0) {
                                        // Auto-advance queue
                                        goNext(true);
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        // Fallback plain iframe
                        els.player.innerHTML = '<iframe src="' + esc(parsed.embed) + '" height="' + (parsed.height || 200) +
                            '" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" allowfullscreen title="Work music"></iframe>';
                    }
                });
                return;
            }

            if (parsed.controllable === 'soundcloud') {
                els.player.hidden = false;
                els.player.innerHTML = '<iframe id="wm-sc-frame" src="' + esc(parsed.embed) + '" height="' + (parsed.height || 166) +
                    '" allow="autoplay" title="SoundCloud"></iframe>';
                ensureScApi(function () {
                    if (currentId !== item.id) return;
                    var frame = document.getElementById('wm-sc-frame');
                    if (!frame || !global.SC || !global.SC.Widget) return;
                    try {
                        scWidget = global.SC.Widget(frame);
                        scWidget.bind(global.SC.Widget.Events.PLAY, function () { setPlayIcon(true); });
                        scWidget.bind(global.SC.Widget.Events.PAUSE, function () { setPlayIcon(false); });
                        scWidget.bind(global.SC.Widget.Events.FINISH, function () { goNext(true); });
                    } catch (e) {}
                });
                return;
            }

            // Spotify / Apple / generic embed
            els.player.hidden = false;
            els.player.innerHTML = '<iframe src="' + esc(parsed.embed) + '" height="' + (parsed.height || 152) +
                '" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" title="Work music"></iframe>';
        }

        function selectIndex(i, opts) {
            opts = opts || {};
            if (!state.queue.length) {
                loadEmbed(null);
                paintQueue();
                return;
            }
            i = ((i % state.queue.length) + state.queue.length) % state.queue.length;
            state.index = i;
            saveState(state);
            paintQueue();
            loadEmbed(state.queue[i]);
            if (opts.autoplay) {
                setTimeout(function () { togglePlay(true); }, 400);
            }
        }

        function goPrev() {
            if (state.queue.length < 2) return;
            selectIndex(state.index - 1, { autoplay: false });
        }

        function goNext(fromEnded) {
            if (state.queue.length < 2) {
                if (fromEnded) setPlayIcon(false);
                return;
            }
            selectIndex(state.index + 1, { autoplay: !!fromEnded });
        }

        function togglePlay(forcePlay) {
            var item = currentItem();
            if (!item) return;
            var parsed = parseMusic(item.url);

            if (parsed && parsed.controllable === 'youtube' && ytPlayer && ytReady) {
                try {
                    var st = ytPlayer.getPlayerState && ytPlayer.getPlayerState();
                    // 1 = playing
                    if (forcePlay === true || st !== 1) {
                        ytPlayer.playVideo();
                        setPlayIcon(true);
                    } else {
                        ytPlayer.pauseVideo();
                        setPlayIcon(false);
                    }
                    return;
                } catch (e) {}
            }

            if (parsed && parsed.controllable === 'soundcloud' && scWidget) {
                try {
                    if (forcePlay === true) {
                        scWidget.play();
                        setPlayIcon(true);
                    } else if (forcePlay === false) {
                        scWidget.pause();
                        setPlayIcon(false);
                    } else {
                        scWidget.toggle();
                    }
                    return;
                } catch (e2) {}
            }

            // Spotify / Apple / custom — can't remote-control embed reliably
            if (forcePlay === false) {
                setPlayIcon(false);
                return;
            }
            // Open in new tab as play action for non-controllable
            if (parsed && !parsed.controllable) {
                try {
                    global.open(item.url, '_blank', 'noopener,noreferrer');
                } catch (e3) {}
                setNote(fun
                    ? 'Opened in a new tab — use that player’s controls. Queue Back/Next still work here 💕'
                    : 'Opened in a new tab. Use that player’s controls; Back/Next still work here.');
            } else {
                setNote(fun
                    ? 'Player still loading — try Play again in a sec'
                    : 'Player still loading — try again.');
            }
        }

        function addUrl(url, title) {
            url = String(url || '').trim();
            if (!url) {
                setNote(fun ? 'Paste a link first 💕' : 'Paste a link first.');
                return;
            }
            var parsed = parseMusic(url);
            if (!parsed) {
                setNote(fun ? 'That doesn’t look like a valid link' : 'Invalid link.');
                return;
            }
            // Dedupe by URL
            var existing = -1;
            state.queue.forEach(function (q, i) {
                if (q.url === parsed.url) existing = i;
            });
            if (existing >= 0) {
                selectIndex(existing);
                setNote(fun ? 'Already in your queue — playing it' : 'Already in queue.');
                return;
            }
            var customTitle = String(title || '').trim();
            var item = {
                id: uid(),
                url: parsed.url,
                provider: parsed.provider,
                title: customTitle || parsed.title || providerMeta(parsed.provider).label,
                addedAt: Date.now()
            };
            state.queue.push(item);
            state.index = state.queue.length - 1;
            saveState(state);
            if (els.url) els.url.value = '';
            if (els.title) els.title.value = '';
            paintQueue();
            loadEmbed(item);
            setNote(fun ? 'Added to your queue ✨' : 'Added to queue.');
        }

        function removeAt(i) {
            if (i < 0 || i >= state.queue.length) return;
            state.queue.splice(i, 1);
            if (!state.queue.length) {
                state.index = 0;
                saveState(state);
                paintQueue();
                loadEmbed(null);
                return;
            }
            if (state.index >= state.queue.length) state.index = state.queue.length - 1;
            else if (i < state.index) state.index -= 1;
            saveState(state);
            paintQueue();
            loadEmbed(currentItem());
        }

        // Events
        if (els.add) {
            els.add.addEventListener('click', function () {
                addUrl(els.url && els.url.value, els.title && els.title.value);
            });
        }
        if (els.url) {
            els.url.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addUrl(els.url.value, els.title && els.title.value);
                }
            });
        }
        if (els.title) {
            els.title.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addUrl(els.url && els.url.value, els.title.value);
                }
            });
        }
        if (els.prev) els.prev.addEventListener('click', goPrev);
        if (els.next) els.next.addEventListener('click', function () { goNext(false); });
        if (els.play) els.play.addEventListener('click', function () { togglePlay(); });
        if (els.removeCur) {
            els.removeCur.addEventListener('click', function () {
                removeAt(state.index);
            });
        }
        if (els.clearQueue) {
            els.clearQueue.addEventListener('click', function () {
                if (!state.queue.length) return;
                if (!confirm(fun ? 'Clear your whole music queue?' : 'Clear the entire queue?')) return;
                state.queue = [];
                state.index = 0;
                saveState(state);
                destroyYt();
                destroySc();
                paintQueue();
                loadEmbed(null);
            });
        }
        if (els.queueList) {
            els.queueList.addEventListener('click', function (e) {
                var del = e.target.closest('[data-del]');
                if (del) {
                    e.preventDefault();
                    e.stopPropagation();
                    removeAt(parseInt(del.getAttribute('data-del'), 10));
                    return;
                }
                var row = e.target.closest('[data-qi]');
                if (row) {
                    selectIndex(parseInt(row.getAttribute('data-qi'), 10));
                }
            });
        }
        if (els.svcCustom) {
            els.svcCustom.addEventListener('click', function () {
                if (els.url) {
                    els.url.focus();
                    els.url.placeholder = fun
                        ? 'Paste any music link…'
                        : 'Paste any URL…';
                }
            });
        }

        // Boot
        paintQueue();
        if (state.queue.length) {
            loadEmbed(currentItem());
        } else {
            loadEmbed(null);
            setNote(fun
                ? 'Tip: open Spotify/YouTube above, copy a share link, paste here, Add 💕'
                : 'Tip: open a service, copy a share link, paste and Add.');
        }
    }

    global.PbjWorkMusic = { init: init, parseMusic: parseMusic };
})(window);
