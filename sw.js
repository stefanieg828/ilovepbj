/**
 * ilovepbj ops service worker
 * Offline shells: inventory loop + auto-order + FOH/BOH checklists + pulse deps
 * Strategy: network-first for navigations; cache-first for static assets.
 */
var CACHE = 'pbj-ops-v13';
var PRECACHE = [
  '/manifest.webmanifest',
  '/icon-192.png',
  '/icon-512.png',
  '/favicon.ico',
  '/Fonts/dreaming-outloud-pro-regular.otf',
  '/Fonts/modern-love-caps.ttf',
  '/shared-state.js?v=4',
  '/permissions.js?v=1',
  '/checklist-complete.js?v=1',
  '/checklist-assign.js?v=1',
  '/checklist-photos.js?v=1',
  '/editable-checklist.js?v=5',
  '/inv-shared-sync.js?v=1',
  '/pos-sync-client.js?v=2',
  '/ops-nudges.js?v=2',
  '/daily-pulse.js?v=4',
  '/admin/cash',
  '/bottom-nav.php',
  '/admin/count',
  '/admin/product-setup',
  '/admin/inventory',
  '/admin/auto-order',
  '/admin/checklist-overview',
  '/admin/pos-import',
  '/admin/pos-connect',
  '/admin/setup',
  '/admin/sales',
  '/admin/labor',
  '/admin/cash',
  '/admin/pnl',
  '/admin/waste',
  '/admin/pmix',
  '/food-cost-shared.js?v=2',
  '/pos-sync-client.js?v=3',
  '/FOH/opening-closing',
  '/FOH/sidework',
  '/FOH/bar',
  '/BOH/opening-closing',
  '/BOH/prep',
  '/BOH/cleaning',
  '/BOH/86',
  '/BOH/86/display',
  '/86-board-shared.js?v=1',
  '/BOH/allergens',
  '/allergen-menu-shared.js?v=1',
  '/schedule-shared.js?v=1',
  '/schedule',
  '/admin/schedules',
  '/home'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE).then(function (cache) {
      return Promise.all(
        PRECACHE.map(function (url) {
          return cache.add(url).catch(function () {});
        })
      );
    }).then(function () {
      return self.skipWaiting();
    })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.map(function (k) {
          if (k !== CACHE) return caches.delete(k);
        })
      );
    }).then(function () {
      return self.clients.claim();
    })
  );
});

function isApi(url) {
  return /-(api)\.php(\?|$)/.test(url.pathname) ||
    /shared-state-api\.php|permissions-api\.php|invoices-api\.php|user-prefs-api\.php|pos-api\.php/.test(url.pathname);
}

function isStaticAsset(url) {
  return /\.(js|css|png|jpg|jpeg|gif|webp|svg|woff2?|ttf|otf|ico|webmanifest)(\?|$)/i.test(url.pathname + url.search);
}

function isOfflineShell(url) {
  var p = url.pathname.replace(/\/$/, '') || '/';
  var shells = [
    '/admin/count', '/admin/product-setup', '/admin/inventory', '/admin/auto-order',
    '/admin/checklist-overview', '/admin/pos-import', '/admin/pos-connect', '/admin/setup',
    '/admin/sales', '/admin/labor', '/admin/cash', '/admin/pnl',
    '/admin/waste', '/admin/pmix', '/BOH/menu',
    '/FOH/opening-closing', '/FOH/sidework', '/FOH/bar',
    '/BOH/opening-closing', '/BOH/prep', '/BOH/cleaning', '/BOH/86', '/BOH/86/display', '/BOH/allergens',
    '/schedule', '/admin/schedules',
    '/home', '/dashboard'
  ];
  if (shells.indexOf(p) !== -1) return true;
  return /admin-inventory|admin-auto-order|admin-checklist|admin-pos-import|admin-pos-connect|admin-house-setup|admin-sales|admin-labor|admin-cash|admin-pnl|admin-waste|admin-pmix|admin-schedules|my-schedule|the-heat-menu|showtime-|the-heat-(prep|cleaning|opening|86|allergens)|schedule-shared/.test(url.pathname);
}

self.addEventListener('fetch', function (event) {
  var req = event.request;
  if (req.method !== 'GET') return;

  var url;
  try {
    url = new URL(req.url);
  } catch (e) {
    return;
  }
  if (url.origin !== self.location.origin) return;

  if (isApi(url)) {
    event.respondWith(
      fetch(req).catch(function () {
        return new Response(JSON.stringify({ ok: false, error: 'offline' }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' }
        });
      })
    );
    return;
  }

  if (isStaticAsset(url)) {
    event.respondWith(
      caches.match(req).then(function (hit) {
        if (hit) return hit;
        return fetch(req).then(function (res) {
          if (res && res.ok) {
            var clone = res.clone();
            caches.open(CACHE).then(function (c) { c.put(req, clone); });
          }
          return res;
        }).catch(function () {
          return hit || Response.error();
        });
      })
    );
    return;
  }

  if (isOfflineShell(url) || req.mode === 'navigate') {
    event.respondWith(
      fetch(req).then(function (res) {
        if (res && res.ok) {
          var clone = res.clone();
          caches.open(CACHE).then(function (c) {
            c.put(req, clone);
            try { c.put(url.pathname, clone.clone()); } catch (e) {}
          });
        }
        return res;
      }).catch(function () {
        return caches.match(req).then(function (hit) {
          if (hit) return hit;
          return caches.match(url.pathname).then(function (hit2) {
            if (hit2) return hit2;
            if (isOfflineShell(url)) {
              return new Response(
                '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' +
                '<title>Offline · ilovepbj</title><style>body{font-family:Georgia,serif;background:#FCF8EE;color:#3a2f1f;padding:24px;text-align:center}' +
                'a{color:#E55163;font-weight:600}</style></head><body>' +
                '<h1>You\'re offline 📡</h1><p>Open this page once online so your phone can cache it for walks &amp; service.</p>' +
                '<p><a href="/admin/count">Count Stock</a> · <a href="/BOH/prep">Prep</a> · <a href="/home">Home</a></p>' +
                '</body></html>',
                { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
              );
            }
            return Response.error();
          });
        });
      })
    );
  }
});
