/**
 * Drag-to-reorder hub category cards.
 * Usage: add class "hub-grid" + data-hub-key="showtime" to a grid of .card links.
 * Order is saved per-device in localStorage.
 */
(function () {
  function storageKey(hubKey) {
    return 'pbj_hub_card_order_v1_' + hubKey;
  }

  function loadOrder(hubKey) {
    try {
      var raw = localStorage.getItem(storageKey(hubKey));
      var arr = raw ? JSON.parse(raw) : null;
      return Array.isArray(arr) ? arr : [];
    } catch (e) {
      return [];
    }
  }

  function saveOrder(hubKey, ids) {
    try {
      localStorage.setItem(storageKey(hubKey), JSON.stringify(ids));
    } catch (e) {}
  }

  function cardId(card) {
    return card.getAttribute('data-card-id') || card.getAttribute('href') || '';
  }

  function applyOrder(grid, hubKey) {
    var order = loadOrder(hubKey);
    if (!order.length) return;
    var map = {};
    Array.prototype.forEach.call(grid.querySelectorAll('.card'), function (c) {
      map[cardId(c)] = c;
    });
    order.forEach(function (id) {
      if (map[id]) grid.appendChild(map[id]);
    });
  }

  function currentIds(grid) {
    return Array.prototype.map.call(grid.querySelectorAll('.card'), cardId);
  }

  function initGrid(grid) {
    var hubKey = grid.getAttribute('data-hub-key') || 'hub';
    // Ensure each card has an id
    Array.prototype.forEach.call(grid.querySelectorAll('.card'), function (card, i) {
      if (!card.getAttribute('data-card-id')) {
        card.setAttribute('data-card-id', card.getAttribute('href') || ('card-' + i));
      }
      card.setAttribute('draggable', 'true');
      card.classList.add('hub-card-draggable');
    });

    applyOrder(grid, hubKey);

    var dragEl = null;

    grid.addEventListener('dragstart', function (e) {
      var card = e.target.closest('.card');
      if (!card || !grid.contains(card)) return;
      dragEl = card;
      card.classList.add('dragging');
      try {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', cardId(card));
      } catch (err) {}
    });

    grid.addEventListener('dragend', function (e) {
      var card = e.target.closest('.card');
      if (card) card.classList.remove('dragging');
      Array.prototype.forEach.call(grid.querySelectorAll('.card'), function (c) {
        c.classList.remove('drag-over');
      });
      dragEl = null;
      saveOrder(hubKey, currentIds(grid));
    });

    grid.addEventListener('dragover', function (e) {
      e.preventDefault();
      var over = e.target.closest('.card');
      if (!over || !dragEl || over === dragEl || !grid.contains(over)) return;
      var rect = over.getBoundingClientRect();
      var before = (e.clientY - rect.top) < rect.height / 2;
      // Also support horizontal grids: use X when wider than tall cells
      if (rect.width > rect.height * 1.2) {
        before = (e.clientX - rect.left) < rect.width / 2;
      }
      if (before) {
        grid.insertBefore(dragEl, over);
      } else {
        grid.insertBefore(dragEl, over.nextSibling);
      }
    });

    // Touch-friendly long-press reorder via pointer (simple: hold handle)
    // Optional tip: double-click title area not used; drag works on desktop.
  }

  function injectStyles() {
    if (document.getElementById('pbj-hub-card-order-css')) return;
    var style = document.createElement('style');
    style.id = 'pbj-hub-card-order-css';
    style.textContent = [
      '.hub-grid .card.hub-card-draggable { cursor: grab; touch-action: manipulation; position: relative; }',
      '.hub-grid .card.hub-card-draggable:active { cursor: grabbing; }',
      '.hub-grid .card.dragging { opacity: 0.55; transform: scale(0.98); z-index: 5; }',
      '.hub-grid .card.hub-card-draggable::after {',
      '  content: "⋮⋮"; position: absolute; top: 8px; right: 10px;',
      '  font-size: 0.75rem; opacity: 0.35; letter-spacing: -1px; pointer-events: none;',
      '}',
      '.hub-reorder-hint {',
      '  text-align: center; font-size: 0.85rem; opacity: 0.65; margin: 0 0 12px;',
      '}'
    ].join('\n');
    document.head.appendChild(style);
  }

  function boot() {
    var grids = document.querySelectorAll('.hub-grid[data-hub-key]');
    if (!grids.length) return;
    injectStyles();
    Array.prototype.forEach.call(grids, initGrid);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
