/**
 * First ~10 minutes guided path (peek guests + new trials).
 * LocalStorage + optional user-prefs sync. Soft, dismissible, no walls.
 */
(function (global) {
  'use strict';

  var LS_KEY = 'pbj_onboarding_10min_v1';
  var PREF_KEY = 'onboarding_10min';
  var API = '/user-prefs-api.php';

  var STEPS = [
    {
      id: 'recipe',
      href: '/BOH/recipe-cards?from=10min',
      labelSweet: 'Open a recipe',
      labelClassic: 'Open a recipe',
      hintSweet: 'FREE-DEMO already has samples — peek one card 📖',
      hintClassic: 'Demo house has sample recipes — open one'
    },
    {
      id: 'plate_cost',
      href: '/admin/costing?from=10min&step=plate',
      labelSweet: 'See plate cost',
      labelClassic: 'See plate cost',
      hintSweet: 'Costing sheet turns ingredients into $ per plate 🧮',
      hintClassic: 'Costing sheet shows $ per plate'
    },
    {
      id: 'menu_price',
      href: '/BOH/menu?from=10min',
      labelSweet: 'See suggested menu price',
      labelClassic: 'See suggested menu price / FC%',
      hintSweet: '25–30% food cost → suggested sell price 💕',
      hintClassic: 'Suggested sell price at 25–30% FC'
    },
    {
      id: 'save_account',
      href: '/register?mode=playground',
      labelSweet: 'Save your progress',
      labelClassic: 'Save progress / keep exploring',
      hintSweet: 'Create a real login so your work sticks (or keep peeking)',
      hintClassic: 'Create an account to keep work, or keep exploring',
      soft: true
    }
  ];

  function nowMs() { return Date.now(); }

  function defaultState() {
    return {
      started: true,
      dismissed: false,
      completed: false,
      steps: { recipe: false, plate_cost: false, menu_price: false, save_account: false },
      updatedAt: nowMs()
    };
  }

  function normalize(raw) {
    var base = defaultState();
    if (!raw || typeof raw !== 'object') return base;
    base.started = raw.started !== false;
    base.dismissed = !!raw.dismissed;
    base.completed = !!raw.completed;
    base.updatedAt = parseInt(raw.updatedAt || raw.updated_at || 0, 10) || 0;
    var s = raw.steps && typeof raw.steps === 'object' ? raw.steps : {};
    base.steps.recipe = !!s.recipe;
    base.steps.plate_cost = !!s.plate_cost;
    base.steps.menu_price = !!s.menu_price;
    base.steps.save_account = !!s.save_account;
    return base;
  }

  function loadLocal() {
    try {
      return normalize(JSON.parse(localStorage.getItem(LS_KEY) || 'null'));
    } catch (e) {
      return defaultState();
    }
  }

  function saveLocal(state) {
    try { localStorage.setItem(LS_KEY, JSON.stringify(state)); } catch (e) {}
    return state;
  }

  function syncToPrefs(state) {
    if (!state) return;
    try {
      fetch(API, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ onboarding_10min: state })
      }).catch(function () {});
    } catch (e) {}
  }

  function mergeFromPrefs(local, remote) {
    if (!remote) return local;
    var r = normalize(remote);
    if (r.updatedAt > local.updatedAt) return saveLocal(r);
    if (local.updatedAt > r.updatedAt) {
      syncToPrefs(local);
      return local;
    }
    ['recipe', 'plate_cost', 'menu_price', 'save_account'].forEach(function (k) {
      if (r.steps[k]) local.steps[k] = true;
    });
    if (r.dismissed || local.dismissed) local.dismissed = true;
    if (r.completed || local.completed) local.completed = true;
    return saveLocal(local);
  }

  function pullPrefs(cb) {
    try {
      fetch(API, { credentials: 'same-origin', cache: 'no-store' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          var remote = data && data.prefs && data.prefs[PREF_KEY] ? data.prefs[PREF_KEY] : null;
          var merged = mergeFromPrefs(loadLocal(), remote);
          if (cb) cb(merged);
        })
        .catch(function () { if (cb) cb(loadLocal()); });
    } catch (e) {
      if (cb) cb(loadLocal());
    }
  }

  function patch(mutator) {
    var state = loadLocal();
    mutator(state);
    state.updatedAt = nowMs();
    if (state.steps.recipe && state.steps.plate_cost && state.steps.menu_price && state.steps.save_account) {
      state.completed = true;
    }
    saveLocal(state);
    syncToPrefs(state);
    try {
      global.dispatchEvent(new CustomEvent('pbj-10min-change', { detail: state }));
    } catch (e) {}
    return state;
  }

  function markStep(id) {
    if (!id || !(id in defaultState().steps)) return loadLocal();
    return patch(function (s) {
      s.steps[id] = true;
      s.started = true;
    });
  }

  function dismiss() {
    return patch(function (s) { s.dismissed = true; });
  }

  function start() {
    return patch(function (s) { s.started = true; s.dismissed = false; });
  }

  function nextStep(state) {
    state = state || loadLocal();
    for (var i = 0; i < STEPS.length; i++) {
      if (!state.steps[STEPS[i].id]) return STEPS[i];
    }
    return null;
  }

  function doneCount(state) {
    state = state || loadLocal();
    var n = 0;
    STEPS.forEach(function (st) { if (state.steps[st.id]) n++; });
    return n;
  }

  function shouldShow(opts) {
    opts = opts || {};
    var state = loadLocal();
    if (state.dismissed || state.completed) return false;
    if (opts.force) return true;
    if (opts.isAudience) return true;
    return !!state.started && doneCount(state) < STEPS.length;
  }

  function esc(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function mountPageHint(opts) {
    opts = opts || {};
    var params = new URLSearchParams(global.location.search || '');
    if (params.get('from') !== '10min' && !opts.force) return null;

    var stepId = opts.stepId || params.get('step') || '';
    if (stepId === 'plate') stepId = 'plate_cost';
    if (stepId === 'price' || stepId === 'suggest') stepId = 'menu_price';
    if (!stepId && opts.defaultStep) stepId = opts.defaultStep;
    if (stepId) markStep(stepId);

    var sweet = !!opts.sweet;
    var stepMeta = null;
    for (var i = 0; i < STEPS.length; i++) {
      if (STEPS[i].id === stepId) { stepMeta = STEPS[i]; break; }
    }
    var nxt = nextStep();

    var bar = document.createElement('div');
    bar.id = 'pbj-10min-page-hint';
    bar.setAttribute('role', 'status');
    bar.style.cssText = [
      'position:sticky', 'top:0', 'z-index:40',
      'margin:0', 'padding:10px 14px',
      'background:' + (sweet ? '#FFF5F6' : '#EEF2F8'),
      'border-bottom:1px solid ' + (sweet ? '#F3C5CC' : '#C5D0DE'),
      'font-size:0.95rem', 'line-height:1.4', 'text-align:center'
    ].join(';');

    var title = stepMeta
      ? ((sweet ? 'First 10 minutes · ' : 'First 10 minutes · ') + (sweet ? stepMeta.labelSweet : stepMeta.labelClassic))
      : (sweet ? 'First 10 minutes ⏱️' : 'First 10 minutes');
    var hint = stepMeta
      ? (sweet ? stepMeta.hintSweet : stepMeta.hintClassic)
      : (sweet ? 'You’re on the guided path — keep going for the plate-cost aha.' : 'Guided path — keep going to see plate cost.');

    var nextHtml;
    if (nxt && nxt.id !== stepId) {
      nextHtml = ' <a href="' + esc(nxt.href) + '" style="font-weight:600;margin-left:6px;">' +
        esc((sweet ? 'Next: ' : 'Next: ') + (sweet ? nxt.labelSweet : nxt.labelClassic) + ' →') + '</a>';
    } else {
      nextHtml = ' <a href="/home" style="font-weight:600;margin-left:6px;">' +
        esc(sweet ? 'Checklist on home 💕' : 'Home checklist') + '</a>';
    }

    bar.innerHTML = '<strong>' + esc(title) + '</strong> — ' + esc(hint) + nextHtml;
    document.body.insertBefore(bar, document.body.firstChild);
    return bar;
  }

  function renderDashboardCard(root, opts) {
    if (!root) return;
    opts = opts || {};
    var sweet = !!opts.sweet;
    var isGuest = !!opts.isGuest;

    function paint(state) {
      if (!shouldShow({ isAudience: opts.isAudience, force: opts.force })) {
        root.hidden = true;
        root.innerHTML = '';
        return;
      }
      root.hidden = false;
      var done = doneCount(state);
      var total = STEPS.length;
      var nxt = nextStep(state);

      var stepsHtml = STEPS.map(function (st, idx) {
        var ok = !!state.steps[st.id];
        var label = sweet ? st.labelSweet : st.labelClassic;
        var hint = sweet ? st.hintSweet : st.hintClassic;
        var href = st.href;
        if (st.id === 'save_account' && !isGuest) {
          href = '/BOH/menu?from=10min';
          label = sweet ? 'Keep exploring (you’re saved)' : 'Keep exploring (you’re signed in)';
          hint = sweet ? 'Soft step — poke around, dismiss anytime 💕' : 'Soft step — explore freely, dismiss anytime.';
        }
        var mark = ok ? '✓' : String(idx + 1);
        return (
          '<a class="pbj-10min-step' + (ok ? ' is-done' : '') + (nxt && nxt.id === st.id ? ' is-next' : '') + '" href="' + esc(href) + '">' +
            '<span class="pbj-10min-num" aria-hidden="true">' + mark + '</span>' +
            '<span class="pbj-10min-step-body">' +
              '<strong>' + esc(label) + '</strong>' +
              '<span class="pbj-10min-hint">' + esc(hint) + '</span>' +
            '</span>' +
          '</a>'
        );
      }).join('');

      var cta;
      if (nxt && nxt.id === 'save_account' && !isGuest) {
        cta = '<button type="button" class="pbj-10min-cta" data-10min-dismiss="1">' +
          esc(sweet ? 'Nice — dismiss checklist ✨' : 'Done — dismiss') + '</button>';
      } else if (nxt) {
        var nlabel = sweet ? nxt.labelSweet : nxt.labelClassic;
        if (nxt.id === 'save_account' && !isGuest) nlabel = sweet ? 'Keep exploring' : 'Keep exploring';
        cta = '<a class="pbj-10min-cta" href="' + esc(nxt.href) + '">' +
          esc((sweet ? 'Continue → ' : 'Continue → ') + nlabel) + '</a>';
      } else {
        cta = '<button type="button" class="pbj-10min-cta" data-10min-dismiss="1">' +
          esc(sweet ? 'You got the aha — dismiss ✨' : 'All done — dismiss') + '</button>';
      }

      root.innerHTML =
        '<div class="pbj-10min-card">' +
          '<div class="pbj-10min-head">' +
            '<div>' +
              '<p class="pbj-10min-kicker">' + esc(sweet ? 'First 10 minutes ⏱️' : 'First 10 minutes') + '</p>' +
              '<p class="pbj-10min-title">' +
                esc(sweet
                  ? 'Plate cost → suggested price — the quick aha'
                  : 'Plate cost → suggested price') +
              '</p>' +
              '<p class="pbj-10min-meta">' + done + ' of ' + total + ' · soft path, dismiss anytime</p>' +
            '</div>' +
            '<button type="button" class="pbj-10min-x" data-10min-dismiss="1" aria-label="Dismiss">✕</button>' +
          '</div>' +
          '<div class="pbj-10min-steps">' + stepsHtml + '</div>' +
          '<div class="pbj-10min-foot">' + cta + '</div>' +
        '</div>';
    }

    root.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-10min-dismiss]');
      if (!btn) return;
      e.preventDefault();
      if (!isGuest) markStep('save_account');
      dismiss();
      paint(loadLocal());
    });

    var initial = loadLocal();
    if (!initial.started && !initial.dismissed) {
      patch(function (s) { s.started = true; });
      initial = loadLocal();
    }
    paint(initial);
    pullPrefs(function (state) { paint(state); });
    global.addEventListener('pbj-10min-change', function (ev) {
      paint(ev.detail || loadLocal());
    });
  }

  function noteNamedAccount() {
    return markStep('save_account');
  }

  global.PbjFirst10 = {
    STEPS: STEPS,
    load: loadLocal,
    markStep: markStep,
    dismiss: dismiss,
    start: start,
    nextStep: nextStep,
    doneCount: doneCount,
    shouldShow: shouldShow,
    pullPrefs: pullPrefs,
    mountPageHint: mountPageHint,
    renderDashboardCard: renderDashboardCard,
    noteNamedAccount: noteNamedAccount
  };
})(window);
