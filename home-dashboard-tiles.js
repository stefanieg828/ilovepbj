/**
 * Movable, hideable home dashboard tiles.
 * Tiles keep content-driven sizes (full / half / third / auto) — not equal grid cells.
 * Order + visibility: localStorage + optional user-prefs-api sync.
 *
 * Markup: .dash-tiles > .dash-tile[data-tile-id][data-tile-size]
 * Optional: #dash-tiles-edit-btn, #dash-tiles-hint, #dash-tiles-hidden-panel
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'pbj_home_tiles_v1';
  var CREATOR_NEWS_HIDE_KEY = 'pbj_creator_news_hidden_v1';
  var API_URL = '/user-prefs-api.php';

  var DEFAULT_ORDER = [
    'daily-pulse',
    'star',
    'a2hs',
    'shortcuts',
    'list-complete',
    'stat-posts',
    'stat-handoffs',
    'stat-chats',
    'creator-news',
    'work-music',
    'platform-desk',
    'theme'
  ];

  var TILE_LABELS = {
    'daily-pulse': 'Daily Pulse',
    star: 'Star of the house',
    a2hs: 'Add to homepage',
    shortcuts: 'Shortcuts / quick pins',
    'list-complete': 'Checklists completed',
    'stat-posts': 'Open posts',
    'stat-handoffs': 'Handoffs today',
    'stat-chats': 'DM threads',
    'creator-news': "Creator's news",
    'work-music': 'Work music',
    'platform-desk': 'Platform desk',
    theme: 'Theme switcher'
  };

  var grid = null;
  var state = { order: [], hidden: [], updatedAt: 0 };
  var editing = false;
  var dragEl = null;
  var funNames = false;
  var saveTimer = null;
  var syncing = false;

  function nowMs() {
    return Date.now ? Date.now() : new Date().getTime();
  }

  function tileId(el) {
    return (el && el.getAttribute('data-tile-id')) || '';
  }

  function allTiles() {
    if (!grid) return [];
    return Array.prototype.slice.call(grid.querySelectorAll('.dash-tile[data-tile-id]'));
  }

  function tilesById() {
    var map = {};
    allTiles().forEach(function (t) {
      var id = tileId(t);
      if (id) map[id] = t;
    });
    return map;
  }

  function sanitizeOrder(arr) {
    if (!Array.isArray(arr)) return DEFAULT_ORDER.slice();
    var seen = {};
    var out = [];
    arr.forEach(function (id) {
      if (typeof id !== 'string' || !id || seen[id]) return;
      seen[id] = true;
      out.push(id);
    });
    DEFAULT_ORDER.forEach(function (id) {
      if (!seen[id]) out.push(id);
    });
    return out;
  }

  function sanitizeHidden(arr) {
    if (!Array.isArray(arr)) return [];
    var seen = {};
    var out = [];
    arr.forEach(function (id) {
      if (typeof id !== 'string' || !id || seen[id]) return;
      if (DEFAULT_ORDER.indexOf(id) === -1) return;
      seen[id] = true;
      out.push(id);
    });
    return out;
  }

  function loadLocal() {
    var order = DEFAULT_ORDER.slice();
    var hidden = [];
    var updatedAt = 0;
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      var parsed = raw ? JSON.parse(raw) : null;
      if (parsed && typeof parsed === 'object') {
        if (Array.isArray(parsed.order)) order = sanitizeOrder(parsed.order);
        if (Array.isArray(parsed.hidden)) hidden = sanitizeHidden(parsed.hidden);
        updatedAt = parseInt(parsed.updatedAt, 10) || 0;
      }
    } catch (e) {}

    // Migrate legacy creator-news hide flag
    try {
      if (localStorage.getItem(CREATOR_NEWS_HIDE_KEY) === '1') {
        if (hidden.indexOf('creator-news') === -1) hidden.push('creator-news');
      }
    } catch (e2) {}

    return { order: order, hidden: hidden, updatedAt: updatedAt };
  }

  function saveLocal(next, opts) {
    opts = opts || {};
    state.order = sanitizeOrder(next.order || state.order);
    state.hidden = sanitizeHidden(next.hidden || state.hidden);
    if (!opts.keepTs) {
      state.updatedAt = nowMs();
    } else if (!state.updatedAt) {
      state.updatedAt = nowMs();
    }
    try {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          order: state.order,
          hidden: state.hidden,
          updatedAt: state.updatedAt
        })
      );
    } catch (e) {}

    // Keep legacy key in sync for creator news
    try {
      localStorage.setItem(
        CREATOR_NEWS_HIDE_KEY,
        state.hidden.indexOf('creator-news') !== -1 ? '1' : '0'
      );
    } catch (e2) {}

    scheduleSync();
    return state;
  }

  function scheduleSync() {
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(function () {
      saveTimer = null;
      syncToServer();
    }, 400);
  }

  function syncToServer() {
    if (syncing) return;
    syncing = true;
    var payload = {
      home_tiles: {
        order: state.order,
        hidden: state.hidden,
        updatedAt: state.updatedAt || nowMs()
      }
    };
    fetch(API_URL, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(function (r) {
        return r.json().then(function (data) {
          return { okHttp: r.ok, data: data };
        });
      })
      .then(function (pack) {
        syncing = false;
        var data = pack.data;
        if (!pack.okHttp || !data || !data.ok) return;
        if (data.localOnly) return;
        var remote = data.prefs && data.prefs.home_tiles;
        if (remote && data.conflict && data.kept === 'server') {
          applyRemote(remote);
        }
      })
      .catch(function () {
        syncing = false;
      });
  }

  function pullFromServer() {
    fetch(API_URL, { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!data || !data.ok || data.localOnly) return;
        var remote = data.prefs && data.prefs.home_tiles;
        if (!remote) return;
        var remoteAt = parseInt(remote.updatedAt, 10) || 0;
        if (remoteAt > (state.updatedAt || 0)) {
          applyRemote(remote);
        }
      })
      .catch(function () {});
  }

  function applyRemote(remote) {
    state.order = sanitizeOrder(remote.order);
    state.hidden = sanitizeHidden(remote.hidden);
    state.updatedAt = parseInt(remote.updatedAt, 10) || nowMs();
    try {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          order: state.order,
          hidden: state.hidden,
          updatedAt: state.updatedAt
        })
      );
      localStorage.setItem(
        CREATOR_NEWS_HIDE_KEY,
        state.hidden.indexOf('creator-news') !== -1 ? '1' : '0'
      );
    } catch (e) {}
    applyOrder();
    applyHidden();
    paintHiddenPanel();
    dispatchChange();
  }

  function applyOrder() {
    if (!grid) return;
    var map = tilesById();
    state.order.forEach(function (id) {
      if (map[id]) grid.appendChild(map[id]);
    });
    // Any unknown tiles go to end
    allTiles().forEach(function (t) {
      var id = tileId(t);
      if (state.order.indexOf(id) === -1) grid.appendChild(t);
    });
  }

  function applyHidden() {
    allTiles().forEach(function (t) {
      var id = tileId(t);
      var hide = state.hidden.indexOf(id) !== -1;
      t.classList.toggle('is-user-hidden', hide);
      t.setAttribute('data-user-hidden', hide ? '1' : '0');
    });
  }

  function currentOrderFromDom() {
    return allTiles()
      .map(tileId)
      .filter(Boolean);
  }

  function isHidden(id) {
    return state.hidden.indexOf(id) !== -1;
  }

  function hideTile(id) {
    if (!id || isHidden(id)) return;
    state.hidden = sanitizeHidden(state.hidden.concat([id]));
    saveLocal(state);
    applyHidden();
    paintHiddenPanel();
    dispatchChange();

    // Creator-news has its own showNewsUi path
    if (id === 'creator-news') {
      var box = document.getElementById('creator-news');
      if (box && box.getAttribute('data-can-edit') !== '1') {
        box.hidden = true;
      }
    }
  }

  function showTile(id) {
    if (!id) return;
    state.hidden = state.hidden.filter(function (h) {
      return h !== id;
    });
    saveLocal(state);
    applyHidden();
    paintHiddenPanel();
    dispatchChange();

    if (id === 'creator-news') {
      var box = document.getElementById('creator-news');
      if (box) {
        // Let existing news script re-evaluate; force visible if content or editor
        try {
          localStorage.setItem(CREATOR_NEWS_HIDE_KEY, '0');
        } catch (e) {}
        if (box.getAttribute('data-can-edit') === '1') {
          box.hidden = false;
        } else {
          var body = document.getElementById('creator-news-body');
          var text = body ? String(body.textContent || '').trim() : '';
          box.hidden = !text;
        }
      }
    }
  }

  function dispatchChange() {
    try {
      document.dispatchEvent(
        new CustomEvent('pbj-home-tiles-change', {
          detail: { order: state.order.slice(), hidden: state.hidden.slice() }
        })
      );
    } catch (e) {}
  }

  function setEditing(on) {
    editing = !!on;
    if (!grid) return;
    grid.classList.toggle('is-editing', editing);
    document.body.classList.toggle('dash-tiles-editing', editing);

    var btn = document.getElementById('dash-tiles-edit-btn');
    var hint = document.getElementById('dash-tiles-hint');
    var panel = document.getElementById('dash-tiles-hidden-panel');

    if (btn) {
      btn.textContent = editing
        ? funNames
          ? 'Done arranging ✨'
          : 'Done'
        : funNames
          ? 'Arrange home'
          : 'Arrange home';
      btn.setAttribute('aria-pressed', editing ? 'true' : 'false');
    }
    if (hint) hint.hidden = !editing;
    if (panel) {
      panel.hidden = !editing;
      if (editing) paintHiddenPanel();
    }

    allTiles().forEach(function (t) {
      t.setAttribute('draggable', editing ? 'true' : 'false');
      t.classList.toggle('dash-tile-draggable', editing);
      var hideBtn = t.querySelector('.dash-tile-hide');
      if (hideBtn) hideBtn.hidden = !editing;
      var grip = t.querySelector('.dash-tile-grip');
      if (grip) grip.hidden = !editing;
    });
  }

  function ensureChrome(tile) {
    if (tile.querySelector('.dash-tile-chrome')) return;
    var chrome = document.createElement('div');
    chrome.className = 'dash-tile-chrome';
    chrome.setAttribute('aria-hidden', 'true');

    var grip = document.createElement('span');
    grip.className = 'dash-tile-grip';
    grip.textContent = '⋮⋮';
    grip.hidden = !editing;
    grip.title = funNames ? 'Drag to move' : 'Drag to reorder';

    var hideBtn = document.createElement('button');
    hideBtn.type = 'button';
    hideBtn.className = 'dash-tile-hide';
    hideBtn.setAttribute('aria-label', funNames ? 'Hide this tile' : 'Hide this tile');
    hideBtn.textContent = '×';
    hideBtn.hidden = !editing;
    hideBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var id = tileId(tile);
      if (id) hideTile(id);
    });

    chrome.appendChild(grip);
    chrome.appendChild(hideBtn);
    tile.insertBefore(chrome, tile.firstChild);
  }

  function paintHiddenPanel() {
    var panel = document.getElementById('dash-tiles-hidden-panel');
    if (!panel) return;

    var present = tilesById();
    var hiddenIds = state.hidden.filter(function (id) {
      return !!present[id] || DEFAULT_ORDER.indexOf(id) !== -1;
    });

    if (!hiddenIds.length) {
      panel.innerHTML =
        '<p class="dash-tiles-hidden-empty">' +
        (funNames
          ? 'Nothing hidden — tap × on a tile to tuck it away 💕'
          : 'Nothing hidden. Use × on a tile to hide it.') +
        '</p>';
      return;
    }

    var html =
      '<p class="dash-tiles-hidden-title">' +
      (funNames ? 'Hidden — tap to bring back' : 'Hidden tiles — tap to restore') +
      '</p><div class="dash-tiles-hidden-list">';

    hiddenIds.forEach(function (id) {
      var label = TILE_LABELS[id] || id;
      // Prefer live label from tile if present
      var el = present[id];
      if (el) {
        var live =
          el.getAttribute('data-tile-label') ||
          (el.querySelector('h2, .home-shortcuts-title, .creator-news-title, .sb-kicker') || {})
            .textContent;
        if (live && String(live).trim()) label = String(live).trim();
      }
      html +=
        '<button type="button" class="dash-tiles-restore" data-restore-id="' +
        id.replace(/"/g, '') +
        '">' +
        escapeHtml(label) +
        '</button>';
    });
    html += '</div>';
    panel.innerHTML = html;
  }

  function escapeHtml(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function bindDrag() {
    if (!grid) return;

    grid.addEventListener('dragstart', function (e) {
      if (!editing) return;
      var tile = e.target.closest('.dash-tile');
      if (!tile || !grid.contains(tile) || tile.classList.contains('is-user-hidden')) return;
      // Don't start drag from form controls
      if (e.target.closest('input, textarea, select, button:not(.dash-tile-hide)')) {
        // still allow grip area
        if (!e.target.closest('.dash-tile-grip, .dash-tile-chrome')) {
          e.preventDefault();
          return;
        }
      }
      dragEl = tile;
      tile.classList.add('is-dragging');
      try {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', tileId(tile));
      } catch (err) {}
    });

    grid.addEventListener('dragend', function (e) {
      var tile = e.target.closest('.dash-tile');
      if (tile) tile.classList.remove('is-dragging');
      allTiles().forEach(function (t) {
        t.classList.remove('drag-over');
      });
      dragEl = null;
      state.order = sanitizeOrder(currentOrderFromDom());
      saveLocal(state);
      dispatchChange();
    });

    grid.addEventListener('dragover', function (e) {
      if (!editing || !dragEl) return;
      e.preventDefault();
      var over = e.target.closest('.dash-tile');
      if (!over || over === dragEl || !grid.contains(over) || over.classList.contains('is-user-hidden')) {
        return;
      }
      var rect = over.getBoundingClientRect();
      var before;
      if (rect.width > rect.height * 1.15) {
        before = e.clientX - rect.left < rect.width / 2;
      } else {
        before = e.clientY - rect.top < rect.height / 2;
      }
      if (before) {
        grid.insertBefore(dragEl, over);
      } else {
        grid.insertBefore(dragEl, over.nextSibling);
      }
    });

    // Touch reorder (simple: long-press then move)
    var touch = { el: null, ox: 0, oy: 0, active: false, timer: null };

    grid.addEventListener(
      'touchstart',
      function (e) {
        if (!editing || !e.touches || e.touches.length !== 1) return;
        var tile = e.target.closest('.dash-tile');
        if (!tile || !grid.contains(tile) || tile.classList.contains('is-user-hidden')) return;
        if (e.target.closest('input, textarea, select, a, button')) {
          if (!e.target.closest('.dash-tile-grip, .dash-tile-hide')) return;
        }
        var t = e.touches[0];
        touch.el = tile;
        touch.ox = t.clientX;
        touch.oy = t.clientY;
        touch.active = false;
        if (touch.timer) clearTimeout(touch.timer);
        touch.timer = setTimeout(function () {
          if (!touch.el) return;
          touch.active = true;
          dragEl = touch.el;
          touch.el.classList.add('is-dragging');
          try {
            if (navigator.vibrate) navigator.vibrate(12);
          } catch (err) {}
        }, 280);
      },
      { passive: true }
    );

    grid.addEventListener(
      'touchmove',
      function (e) {
        if (!touch.el) return;
        var t = e.touches && e.touches[0];
        if (!t) return;
        var dx = Math.abs(t.clientX - touch.ox);
        var dy = Math.abs(t.clientY - touch.oy);
        if (!touch.active) {
          if (dx > 10 || dy > 10) {
            if (touch.timer) clearTimeout(touch.timer);
            touch.timer = null;
            touch.el = null;
          }
          return;
        }
        e.preventDefault();
        var over = document.elementFromPoint(t.clientX, t.clientY);
        over = over && over.closest ? over.closest('.dash-tile') : null;
        if (!over || over === dragEl || !grid.contains(over) || over.classList.contains('is-user-hidden')) {
          return;
        }
        var rect = over.getBoundingClientRect();
        var before = t.clientY - rect.top < rect.height / 2;
        if (rect.width > rect.height * 1.15) {
          before = t.clientX - rect.left < rect.width / 2;
        }
        if (before) grid.insertBefore(dragEl, over);
        else grid.insertBefore(dragEl, over.nextSibling);
      },
      { passive: false }
    );

    function endTouch() {
      if (touch.timer) clearTimeout(touch.timer);
      touch.timer = null;
      if (touch.active && dragEl) {
        dragEl.classList.remove('is-dragging');
        state.order = sanitizeOrder(currentOrderFromDom());
        saveLocal(state);
        dispatchChange();
      }
      touch.el = null;
      touch.active = false;
      dragEl = null;
    }

    grid.addEventListener('touchend', endTouch);
    grid.addEventListener('touchcancel', endTouch);

    // Prevent accidental navigation while editing
    grid.addEventListener(
      'click',
      function (e) {
        if (!editing) return;
        var a = e.target.closest('a');
        if (a && grid.contains(a) && !e.target.closest('.dash-tile-hide')) {
          e.preventDefault();
          e.stopPropagation();
        }
      },
      true
    );
  }

  function bindToolbar() {
    var btn = document.getElementById('dash-tiles-edit-btn');
    if (btn) {
      btn.addEventListener('click', function () {
        setEditing(!editing);
      });
    }
    var panel = document.getElementById('dash-tiles-hidden-panel');
    if (panel) {
      panel.addEventListener('click', function (e) {
        var b = e.target.closest('[data-restore-id]');
        if (!b) return;
        e.preventDefault();
        showTile(b.getAttribute('data-restore-id'));
      });
    }

    // Wire existing creator-news Hide button into tile system
    var hideBtn = document.getElementById('creator-news-hide-btn');
    if (hideBtn) {
      hideBtn.addEventListener(
        'click',
        function () {
          var newsBox = document.getElementById('creator-news');
          if (newsBox && newsBox.getAttribute('data-can-edit') === '1') return;
          hideTile('creator-news');
        },
        true
      );
    }
  }

  function boot() {
    grid = document.getElementById('dash-tiles');
    if (!grid) return;

    funNames = grid.getAttribute('data-fun') === '1';
    state = loadLocal();

    allTiles().forEach(function (t) {
      ensureChrome(t);
      t.setAttribute('draggable', 'false');
    });

    applyOrder();
    applyHidden();
    bindDrag();
    bindToolbar();
    paintHiddenPanel();
    pullFromServer();

    // Expose small API for settings page / other scripts
    window.PbjHomeTiles = {
      getState: function () {
        return { order: state.order.slice(), hidden: state.hidden.slice(), updatedAt: state.updatedAt };
      },
      hide: hideTile,
      show: showTile,
      isHidden: isHidden,
      setEditing: setEditing,
      labels: TILE_LABELS,
      reload: function () {
        state = loadLocal();
        applyOrder();
        applyHidden();
        paintHiddenPanel();
      }
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
