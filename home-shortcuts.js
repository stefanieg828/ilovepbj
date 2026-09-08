/**
 * Optional home dashboard shortcuts — up to 4 pins.
 * - Per-device localStorage cache
 * - Syncs to server (users.prefs_json) so the same account sees pins on every device
 * - Drag to reorder (desktop + touch)
 *
 * Expects window.PBJ_HOME_SHORTCUTS = { max, funNames, groups, catalog, labels, apiUrl? }.
 */
(function () {
  var STORAGE_KEY = 'pbj_home_shortcuts_v1';
  var META_KEY = 'pbj_home_shortcuts_meta_v1';
  var cfg = window.PBJ_HOME_SHORTCUTS || {};
  var MAX = Math.min(4, Math.max(1, parseInt(cfg.max, 10) || 4));
  var catalog = Array.isArray(cfg.catalog) ? cfg.catalog : [];
  var groups = cfg.groups || {};
  var labels = cfg.labels || {};
  var apiUrl = cfg.apiUrl || '/user-prefs-api.php';

  var byId = {};
  catalog.forEach(function (item) {
    if (item && item.id) byId[item.id] = item;
  });

  function nowMs() {
    return Date.now ? Date.now() : new Date().getTime();
  }

  function sanitizeIds(arr) {
    if (!Array.isArray(arr)) return [];
    var out = [];
    arr.forEach(function (id) {
      if (typeof id !== 'string' || !byId[id]) return;
      if (out.indexOf(id) !== -1) return;
      out.push(id);
    });
    return out.slice(0, MAX);
  }

  function loadLocal() {
    var ids = [];
    var updatedAt = 0;
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      var parsed = raw ? JSON.parse(raw) : null;
      if (Array.isArray(parsed)) {
        ids = sanitizeIds(parsed);
      } else if (parsed && typeof parsed === 'object' && Array.isArray(parsed.ids)) {
        ids = sanitizeIds(parsed.ids);
        updatedAt = parseInt(parsed.updatedAt, 10) || 0;
      }
    } catch (e) {}
    if (!updatedAt) {
      try {
        var meta = JSON.parse(localStorage.getItem(META_KEY) || 'null');
        if (meta && meta.updatedAt) updatedAt = parseInt(meta.updatedAt, 10) || 0;
      } catch (e2) {}
    }
    return { ids: ids, updatedAt: updatedAt };
  }

  function saveLocal(state, opts) {
    opts = opts || {};
    state.ids = sanitizeIds(state.ids);
    if (!opts.keepTs) {
      state.updatedAt = nowMs();
    } else if (!state.updatedAt) {
      state.updatedAt = nowMs();
    }
    try {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({ ids: state.ids, updatedAt: state.updatedAt })
      );
      localStorage.setItem(META_KEY, JSON.stringify({ updatedAt: state.updatedAt }));
    } catch (e) {}
    return state;
  }

  function idsEqual(a, b) {
    if (!a || !b || a.length !== b.length) return false;
    for (var i = 0; i < a.length; i++) {
      if (a[i] !== b[i]) return false;
    }
    return true;
  }

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function iconHtml(item) {
    if (item.icon) {
      return '<span class="sc-ico"><img src="' + esc(item.icon) + '" alt=""></span>';
    }
    return '<span class="sc-ico" aria-hidden="true">' + esc(item.emoji || '•') + '</span>';
  }

  var root = document.getElementById('home-shortcuts');
  var grid = document.getElementById('home-shortcuts-grid');
  var emptyEl = document.getElementById('home-shortcuts-empty');
  var editBtn = document.getElementById('home-shortcuts-edit');
  var backdrop = document.getElementById('sc-modal-backdrop');
  var pickerBody = document.getElementById('sc-picker-body');
  var modalClose = document.getElementById('sc-modal-close');
  var modalHint = document.getElementById('sc-modal-hint');
  var syncEl = document.getElementById('home-shortcuts-sync');
  var reorderHint = document.getElementById('home-shortcuts-reorder-hint');

  if (!root || !grid || !editBtn) return;

  var state = loadLocal();
  var ids = state.ids;
  var editing = false;
  var pendingSlot = null;
  var syncTimer = null;
  var syncing = false;
  var localOnly = false;
  var dragEl = null;
  var didDrag = false;
  var suppressClick = false;

  // Touch reorder state
  var touchDrag = null;

  function setSyncStatus(kind, text) {
    if (!syncEl) return;
    syncEl.textContent = text || '';
    syncEl.setAttribute('data-kind', kind || '');
    syncEl.hidden = !text;
  }

  function persist(opts) {
    opts = opts || {};
    state.ids = ids.slice();
    saveLocal(state, opts);
    if (!opts.skipSync) {
      scheduleSync();
    }
  }

  function scheduleSync() {
    if (localOnly) return;
    if (syncTimer) clearTimeout(syncTimer);
    syncTimer = setTimeout(function () {
      syncTimer = null;
      pushServer();
    }, 280);
  }

  function fetchJson(url, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.cache = 'no-store';
    return fetch(url, options).then(function (res) {
      return res.text().then(function (text) {
        var data = null;
        try {
          data = text ? JSON.parse(text) : null;
        } catch (e) {
          throw new Error('bad_json');
        }
        return { res: res, data: data };
      });
    });
  }

  function applyRemote(remote) {
    if (!remote || !Array.isArray(remote.ids)) return false;
    var remoteIds = sanitizeIds(remote.ids);
    var remoteTs = parseInt(remote.updatedAt, 10) || 0;
    var localTs = state.updatedAt || 0;

    // Prefer newer timestamp; if equal timestamps but different lists, prefer remote
    if (remoteTs > localTs || (remoteTs === localTs && !idsEqual(remoteIds, ids) && remoteTs > 0)) {
      ids = remoteIds;
      state.ids = remoteIds;
      state.updatedAt = remoteTs || localTs || nowMs();
      saveLocal(state, { keepTs: true });
      render();
      return true;
    }
    // Local is newer or only local has pins — push up
    if (localTs > remoteTs && ids.length) {
      scheduleSync();
    } else if (!remoteTs && ids.length && !remoteIds.length) {
      // Seed server from this device
      scheduleSync();
    } else if (remoteTs === 0 && remoteIds.length && !ids.length) {
      ids = remoteIds;
      state.ids = remoteIds;
      state.updatedAt = nowMs();
      saveLocal(state, { keepTs: true });
      render();
      return true;
    }
    return false;
  }

  function pullServer() {
    setSyncStatus('syncing', labels.syncing || (cfg.funNames ? 'Syncing pins…' : 'Syncing…'));
    return fetchJson(apiUrl, { method: 'GET' })
      .then(function (pack) {
        var data = pack.data;
        if (!pack.res.ok || !data || !data.ok) throw new Error((data && data.error) || 'http');
        if (data.localOnly) {
          localOnly = true;
          setSyncStatus('local', labels.localOnly || (cfg.funNames ? 'On this device' : 'This device only'));
          return;
        }
        localOnly = false;
        var hs = data.prefs && data.prefs.home_shortcuts;
        if (hs) {
          applyRemote(hs);
        } else if (ids.length) {
          scheduleSync();
        }
        setSyncStatus(
          'live',
          labels.synced || (cfg.funNames ? 'Synced across your devices 💕' : 'Synced across your devices')
        );
        // Fade status after a moment when idle
        setTimeout(function () {
          if (!syncing) setSyncStatus('live', '');
        }, 3200);
      })
      .catch(function () {
        setSyncStatus(
          'offline',
          labels.offline || (cfg.funNames ? 'Offline — saved on this device' : 'Offline — saved on this device')
        );
      });
  }

  function pushServer() {
    if (localOnly || syncing) {
      if (syncing) scheduleSync();
      return Promise.resolve();
    }
    syncing = true;
    setSyncStatus('syncing', labels.syncing || (cfg.funNames ? 'Saving pins…' : 'Saving…'));
    var payload = {
      home_shortcuts: {
        ids: sanitizeIds(ids),
        updatedAt: state.updatedAt || nowMs(),
      },
    };
    return fetchJson(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(function (pack) {
        syncing = false;
        var data = pack.data;
        if (!pack.res.ok || !data || !data.ok) throw new Error((data && data.error) || 'http');
        if (data.localOnly) {
          localOnly = true;
          setSyncStatus('local', labels.localOnly || 'This device only');
          return;
        }
        if (data.conflict && data.prefs && data.prefs.home_shortcuts) {
          applyRemote(data.prefs.home_shortcuts);
        } else if (data.prefs && data.prefs.home_shortcuts) {
          var hs = data.prefs.home_shortcuts;
          if (hs.updatedAt) {
            state.updatedAt = parseInt(hs.updatedAt, 10) || state.updatedAt;
            saveLocal(state, { keepTs: true });
          }
        }
        setSyncStatus(
          'live',
          labels.synced || (cfg.funNames ? 'Synced across your devices 💕' : 'Synced across your devices')
        );
        setTimeout(function () {
          if (!syncing) setSyncStatus('live', '');
        }, 2200);
      })
      .catch(function () {
        syncing = false;
        setSyncStatus(
          'offline',
          labels.offline || (cfg.funNames ? 'Offline — saved on this device' : 'Offline — saved on this device')
        );
      });
  }

  function setEditing(on) {
    editing = !!on;
    root.classList.toggle('is-editing', editing);
    editBtn.textContent = editing
      ? labels.done || 'Done'
      : labels.edit || 'Customize';
    render();
  }

  function openPicker(slotIndex) {
    if (ids.length >= MAX && (slotIndex == null || slotIndex >= ids.length)) {
      if (modalHint) {
        modalHint.textContent = labels.full || 'You already have 4 shortcuts.';
      }
    } else if (modalHint) {
      modalHint.textContent = cfg.funNames
        ? 'Choose something you open a lot — max ' + MAX + ' pins on your home.'
        : 'Choose a destination. You can pin up to ' + MAX + ' shortcuts on home.';
    }
    pendingSlot = slotIndex;
    renderPicker();
    if (backdrop) backdrop.hidden = false;
  }

  function closePicker() {
    if (backdrop) backdrop.hidden = true;
    pendingSlot = null;
  }

  function addId(id) {
    if (!byId[id]) return;
    if (ids.indexOf(id) !== -1) return;
    if (typeof pendingSlot === 'number' && pendingSlot >= 0 && pendingSlot < ids.length) {
      ids[pendingSlot] = id;
    } else {
      if (ids.length >= MAX) return;
      ids.push(id);
    }
    persist();
    closePicker();
    render();
  }

  function removeAt(index) {
    if (index < 0 || index >= ids.length) return;
    ids.splice(index, 1);
    persist();
    render();
  }

  function commitOrderFromDom() {
    var next = [];
    Array.prototype.forEach.call(grid.querySelectorAll('a.home-shortcut[data-id]'), function (el) {
      var id = el.getAttribute('data-id');
      if (id && byId[id] && next.indexOf(id) === -1) next.push(id);
    });
    if (!idsEqual(next, ids) && next.length) {
      ids = next;
      persist();
    }
  }

  function renderPicker() {
    if (!pickerBody) return;
    var selected = {};
    ids.forEach(function (id) {
      selected[id] = true;
    });

    var order = ['boh', 'foh', 'admin', 'messages'];
    var html = '';
    order.forEach(function (g) {
      var items = catalog.filter(function (c) {
        return c.group === g;
      });
      if (!items.length) return;
      html += '<div class="sc-group-label">' + esc(groups[g] || g) + '</div>';
      html += '<div class="sc-picker-list">';
      items.forEach(function (item) {
        var isOn = !!selected[item.id];
        html +=
          '<button type="button" class="sc-picker-item' +
          (isOn ? ' is-selected' : '') +
          '" data-id="' +
          esc(item.id) +
          '"' +
          (isOn ? ' disabled' : '') +
          '>' +
          iconHtml(item) +
          '<span class="sc-name">' +
          esc(item.label) +
          '</span>' +
          (isOn ? '<span class="sc-check">✓ pinned</span>' : '') +
          '</button>';
      });
      html += '</div>';
    });
    pickerBody.innerHTML = html;
  }

  function render() {
    var hasAny = ids.length > 0;
    if (emptyEl) {
      emptyEl.hidden = hasAny && !editing;
    }
    if (reorderHint) {
      reorderHint.hidden = ids.length < 2;
    }

    if (!editing && !hasAny) {
      grid.innerHTML = '';
      return;
    }

    var showSlots = editing ? MAX : Math.max(ids.length, 0);
    var html = '';
    var canDrag = hasAny && ids.length > 1;

    for (var i = 0; i < showSlots; i++) {
      var id = ids[i];
      if (id && byId[id]) {
        var item = byId[id];
        html +=
          '<a href="' +
          esc(item.href) +
          '" class="home-shortcut' +
          (canDrag ? ' sc-draggable' : '') +
          '" data-id="' +
          esc(item.id) +
          '" draggable="' +
          (canDrag ? 'true' : 'false') +
          '">' +
          (editing
            ? '<button type="button" class="sc-remove" data-remove="' +
              i +
              '" aria-label="Remove" draggable="false">×</button>'
            : '') +
          (canDrag ? '<span class="sc-grip" aria-hidden="true">⋮⋮</span>' : '') +
          iconHtml(item) +
          '<span class="sc-label">' +
          esc(item.label) +
          '</span>' +
          '</a>';
      } else if (editing) {
        html +=
          '<button type="button" class="home-shortcut is-empty" data-add-slot="' +
          i +
          '">' +
          '<span class="sc-ico" aria-hidden="true">＋</span>' +
          '<span class="sc-label">' +
          esc(labels.add || 'Add') +
          '</span>' +
          '</button>';
      }
    }

    grid.innerHTML = html;
  }

  editBtn.addEventListener('click', function () {
    setEditing(!editing);
  });

  grid.addEventListener('click', function (e) {
    if (suppressClick) {
      e.preventDefault();
      e.stopPropagation();
      suppressClick = false;
      return;
    }
    var removeBtn = e.target.closest('[data-remove]');
    if (removeBtn) {
      e.preventDefault();
      e.stopPropagation();
      removeAt(parseInt(removeBtn.getAttribute('data-remove'), 10));
      return;
    }
    var addBtn = e.target.closest('[data-add-slot]');
    if (addBtn) {
      e.preventDefault();
      openPicker(parseInt(addBtn.getAttribute('data-add-slot'), 10));
      return;
    }
    // Edit mode: drag reorders; X removes; empty adds — don't open replace on pin click
  });

  // —— HTML5 drag reorder ——
  grid.addEventListener('dragstart', function (e) {
    var card = e.target.closest('a.home-shortcut');
    if (!card || !grid.contains(card) || !card.classList.contains('sc-draggable')) return;
    if (e.target.closest('.sc-remove')) {
      e.preventDefault();
      return;
    }
    dragEl = card;
    didDrag = false;
    card.classList.add('dragging');
    try {
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', card.getAttribute('data-id') || '');
    } catch (err) {}
  });

  grid.addEventListener('dragend', function (e) {
    var card = e.target.closest('a.home-shortcut');
    if (card) card.classList.remove('dragging');
    Array.prototype.forEach.call(grid.querySelectorAll('.home-shortcut'), function (c) {
      c.classList.remove('drag-over');
    });
    if (didDrag) {
      suppressClick = true;
      commitOrderFromDom();
      render();
    }
    dragEl = null;
    didDrag = false;
  });

  grid.addEventListener('dragover', function (e) {
    if (!dragEl) return;
    e.preventDefault();
    var over = e.target.closest('a.home-shortcut');
    if (!over || over === dragEl || !grid.contains(over)) return;
    didDrag = true;
    var rect = over.getBoundingClientRect();
    var before = e.clientX - rect.left < rect.width / 2;
    if (rect.height > rect.width * 1.1) {
      before = e.clientY - rect.top < rect.height / 2;
    }
    if (before) {
      grid.insertBefore(dragEl, over);
    } else {
      grid.insertBefore(dragEl, over.nextSibling);
    }
  });

  // —— Touch / pointer reorder (mobile-friendly) ——
  grid.addEventListener(
    'pointerdown',
    function (e) {
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      // Prefer native HTML5 drag on mouse
      if (e.pointerType === 'mouse') return;
      var card = e.target.closest('a.home-shortcut.sc-draggable');
      if (!card || !grid.contains(card)) return;
      if (e.target.closest('.sc-remove')) return;

      touchDrag = {
        el: card,
        startX: e.clientX,
        startY: e.clientY,
        moved: false,
        pointerId: e.pointerId,
      };
    },
    { passive: true }
  );

  grid.addEventListener(
    'pointermove',
    function (e) {
      if (!touchDrag || e.pointerId !== touchDrag.pointerId) return;
      var dx = e.clientX - touchDrag.startX;
      var dy = e.clientY - touchDrag.startY;
      if (!touchDrag.moved && dx * dx + dy * dy < 64) return;

      if (!touchDrag.moved) {
        touchDrag.moved = true;
        touchDrag.el.classList.add('dragging');
        try {
          touchDrag.el.setPointerCapture(e.pointerId);
        } catch (err) {}
      }

      e.preventDefault();
      var el = document.elementFromPoint(e.clientX, e.clientY);
      var over = el && el.closest ? el.closest('a.home-shortcut') : null;
      if (!over || over === touchDrag.el || !grid.contains(over)) return;

      var rect = over.getBoundingClientRect();
      var before = e.clientX - rect.left < rect.width / 2;
      if (before) {
        grid.insertBefore(touchDrag.el, over);
      } else {
        grid.insertBefore(touchDrag.el, over.nextSibling);
      }
    },
    { passive: false }
  );

  function endTouchDrag(e) {
    if (!touchDrag || (e && e.pointerId !== touchDrag.pointerId)) return;
    if (touchDrag.moved) {
      suppressClick = true;
      touchDrag.el.classList.remove('dragging');
      commitOrderFromDom();
      render();
    }
    touchDrag = null;
  }

  grid.addEventListener('pointerup', endTouchDrag);
  grid.addEventListener('pointercancel', endTouchDrag);

  if (pickerBody) {
    pickerBody.addEventListener('click', function (e) {
      var btn = e.target.closest('.sc-picker-item');
      if (!btn || btn.disabled) return;
      var id = btn.getAttribute('data-id');
      if (id) addId(id);
    });
  }

  if (modalClose) {
    modalClose.addEventListener('click', closePicker);
  }
  if (backdrop) {
    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) closePicker();
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop && !backdrop.hidden) closePicker();
  });

  // Flush pending sync when leaving the page
  window.addEventListener('pagehide', function () {
    if (syncTimer) {
      clearTimeout(syncTimer);
      syncTimer = null;
      // best-effort beacon
      try {
        if (!localOnly && navigator.sendBeacon) {
          var blob = new Blob(
            [
              JSON.stringify({
                home_shortcuts: { ids: sanitizeIds(ids), updatedAt: state.updatedAt || nowMs() },
              }),
            ],
            { type: 'application/json' }
          );
          navigator.sendBeacon(apiUrl, blob);
        }
      } catch (err) {}
    }
  });

  render();
  pullServer();
})();
