<?php
// bottom-nav.php
if (!function_exists('pbj_theme_id')) {
    require_once __DIR__ . '/config.php';
}
$pbjThemeId = pbj_theme_id();
$pbjFun = pbj_use_fun_names();
$pbjTokens = pbj_theme_tokens();
$pbjHubs = pbj_hub_labels();
$is_sweet = ($pbjThemeId === 'sweet'); // legacy flag some pages rely on after include

// Global paint for non-classic themes (Neon, future Urban, …)
// force=true so overrides land AFTER page-local styles (fonts + colors stick)
if (function_exists('pbj_render_theme_paint')) {
    pbj_render_theme_paint(true);
}
if (function_exists('pbj_render_icon_css')) {
    pbj_render_icon_css();
}
?>

<style>
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: <?php echo htmlspecialchars($pbjTokens['nav_bg']); ?>;
        padding: 8px 8px calc(8px + env(safe-area-inset-bottom, 0px));
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        justify-content: space-around;
        align-items: flex-end;
        gap: 2px;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .nav-item-bottom {
        color: <?php echo htmlspecialchars($pbjTokens['nav_text']); ?>;
        text-decoration: none;
        text-align: center;
        font-size: 0.72rem;
        line-height: 1.1;
        flex: 1 1 0;
        min-width: 52px;
        max-width: 96px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding: 4px 2px 2px;
    }

    .nav-item-bottom span {
        display: block;
        width: 100%;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nav-item-bottom img {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 2px solid <?php echo htmlspecialchars($pbjTokens['nav_border']); ?>;
        display: block;
        margin: 0 auto 3px;
        object-fit: cover;
        flex-shrink: 0;
        background: <?php
            if (in_array($pbjThemeId, ['neon_diner', 'urban'], true)) echo '#0A0A0A';
            elseif ($pbjThemeId === 'farm') echo '#F3E6D4';
            elseif ($pbjThemeId === 'coffee') echo '#F5EDE3';
            else echo 'transparent';
        ?>;
        <?php if ($pbjThemeId === 'neon_diner'): ?>
        box-shadow: 0 0 10px rgba(255, 46, 203, 0.35), 0 0 12px rgba(0, 240, 255, 0.2);
        <?php elseif ($pbjThemeId === 'urban'): ?>
        box-shadow: 0 0 10px rgba(255, 45, 0, 0.4);
        <?php elseif ($pbjThemeId === 'farm'): ?>
        box-shadow: 0 3px 10px rgba(44, 36, 22, 0.12);
        <?php elseif ($pbjThemeId === 'coffee'): ?>
        box-shadow: 0 3px 10px rgba(74, 44, 26, 0.14);
        <?php endif; ?>
    }

    /* Heat + Whiskings: same outer size as sibling imgs, zoom art inside */
    .nav-item-bottom .nav-ico-zoom {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 2px solid <?php echo htmlspecialchars($pbjTokens['nav_border']); ?>;
        display: block;
        margin: 0 auto 3px;
        overflow: hidden;
        flex-shrink: 0;
        /* content-box matches .nav-item-bottom img so border adds outside, not inside */
        box-sizing: content-box;
        background: <?php
            if (in_array($pbjThemeId, ['neon_diner', 'urban'], true)) echo '#0A0A0A';
            elseif ($pbjThemeId === 'farm') echo '#F3E6D4';
            elseif ($pbjThemeId === 'coffee') echo '#F5EDE3';
            else echo 'transparent';
        ?>;
        <?php if ($pbjThemeId === 'neon_diner'): ?>
        box-shadow: 0 0 10px rgba(255, 46, 203, 0.35), 0 0 12px rgba(0, 240, 255, 0.2);
        <?php elseif ($pbjThemeId === 'urban'): ?>
        box-shadow: 0 0 10px rgba(255, 45, 0, 0.4);
        <?php elseif ($pbjThemeId === 'farm'): ?>
        box-shadow: 0 3px 10px rgba(44, 36, 22, 0.12);
        <?php elseif ($pbjThemeId === 'coffee'): ?>
        box-shadow: 0 3px 10px rgba(74, 44, 26, 0.14);
        <?php endif; ?>
    }
    .nav-item-bottom .nav-ico-zoom img {
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        object-fit: cover;
        transform: scale(1.28);
        transform-origin: center center;
    }

    /* Coffee: bottom-nav labels stay readable (no faux-bold / heavy weight) */
    <?php if ($pbjThemeId === 'coffee'): ?>
    .nav-item-bottom,
    .nav-item-bottom span {
        font-weight: 400 !important;
        font-synthesis: none !important;
        -webkit-text-stroke: 0 !important;
        text-shadow: none !important;
        letter-spacing: 0.01em;
    }
    <?php endif; ?>

    /* Farm: dark outline around bottom-nav labels so handwriting stays crisp on green */
    <?php if ($pbjThemeId === 'farm'): ?>
    .nav-item-bottom,
    .nav-item-bottom span {
        -webkit-text-stroke: 0.7px #2C2416 !important;
        paint-order: stroke fill;
        text-shadow:
            0.6px 0 0 #2C2416,
           -0.6px 0 0 #2C2416,
            0 0.6px 0 #2C2416,
            0 -0.6px 0 #2C2416,
            0.45px 0.45px 0 #2C2416,
           -0.45px 0.45px 0 #2C2416,
            0.45px -0.45px 0 #2C2416,
           -0.45px -0.45px 0 #2C2416 !important;
    }
    <?php endif; ?>

    /* Keep a single row on all widths — slightly tighter on small screens */
    @media (max-width: 900px) {
        .bottom-nav {
            padding: 6px 4px calc(6px + env(safe-area-inset-bottom, 0px));
            gap: 0;
        }
        .nav-item-bottom {
            font-size: 0.62rem;
            min-width: 48px;
            padding: 2px 1px;
        }
        .nav-item-bottom img {
            width: 36px;
            height: 36px;
            margin-bottom: 2px;
        }
        .nav-item-bottom .nav-ico-zoom {
            width: 36px;
            height: 36px;
            margin-bottom: 2px;
        }
        body { padding-bottom: 78px !important; }
    }

    @media (min-width: 901px) {
        .bottom-nav {
            padding: 10px 16px calc(10px + env(safe-area-inset-bottom, 0px));
            gap: 8px;
        }
        .nav-item-bottom {
            font-size: 0.85rem;
            max-width: 120px;
        }
        .nav-item-bottom img {
            width: 42px;
            height: 42px;
            margin-bottom: 4px;
        }
        .nav-item-bottom .nav-ico-zoom {
            width: 42px;
            height: 42px;
            margin-bottom: 4px;
        }
    }
</style>

<div class="bottom-nav">
    <?php
    $navSlots = [
        ['href' => '/home', 'slot' => 'nav/home', 'key' => 'home'],
        ['href' => '/FOH', 'slot' => 'nav/foh', 'key' => 'foh'],
        ['href' => '/BOH', 'slot' => 'nav/boh', 'key' => 'boh'],
        ['href' => '/admin', 'slot' => 'nav/admin', 'key' => 'admin'],
        ['href' => '/messages', 'slot' => 'nav/messages', 'key' => 'messages'],
        ['href' => '/settings', 'slot' => 'nav/settings', 'key' => 'settings'],
    ];
    foreach ($navSlots as $item):
        $label = $pbjHubs[$item['key']];
        $src = pbj_icon($item['slot']);
        if ($src === '') {
            $src = 'basic-home-icon.png';
        }
        $zoomIco = in_array($item['key'], ['boh', 'settings'], true);
    ?>
        <a href="<?php echo htmlspecialchars($item['href']); ?>" class="nav-item-bottom">
            <?php if ($zoomIco): ?><span class="nav-ico-zoom"><?php endif; ?>
            <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($label); ?>">
            <?php if ($zoomIco): ?></span><?php endif; ?>
            <span><?php echo htmlspecialchars($label); ?></span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Vendor cut-off alerts (localStorage; works while app is open) -->
<style>
    .pbj-cutoff-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 3000;
        display: none;
        padding: 12px 16px 14px;
        background: <?php echo htmlspecialchars($pbjTokens['primary']); ?>;
        color: <?php echo htmlspecialchars($pbjTokens['primary_text']); ?>;
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        font-family: system-ui, -apple-system, sans-serif;
    }
    .pbj-cutoff-banner.show { display: block; }
    .pbj-cutoff-banner strong { display: block; font-size: 1.05rem; margin-bottom: 4px; }
    .pbj-cutoff-banner p { margin: 0 0 10px; font-size: 0.95rem; line-height: 1.35; opacity: 0.95; }
    .pbj-cutoff-banner .pbj-cutoff-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .pbj-cutoff-banner a, .pbj-cutoff-banner button {
        border: none;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.9rem;
        cursor: pointer;
        text-decoration: none;
        color: #111;
        background: white;
    }
    .pbj-cutoff-banner button.pbj-cutoff-dismiss { background: rgba(255,255,255,0.25); color: white; }
</style>
<div class="pbj-cutoff-banner" id="pbj-cutoff-banner" role="alert" aria-live="polite">
    <strong id="pbj-cutoff-title">Order cut-off soon</strong>
    <p id="pbj-cutoff-body"></p>
    <div class="pbj-cutoff-actions">
        <a href="/admin/auto-order" id="pbj-cutoff-order">Auto-Order</a>
        <a href="/admin/vendors" id="pbj-cutoff-vendors">Vendors</a>
        <button type="button" class="pbj-cutoff-dismiss" id="pbj-cutoff-dismiss">Dismiss</button>
    </div>
</div>
<script>
(function () {
    var VENDOR_KEY = 'pbj_admin_vendors_v1';
    var FIRED_KEY = 'pbj_vendor_cutoff_fired_v1';
    var DAY_CODES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var alertQueue = [];

    function loadVendors() {
        try {
            var r = JSON.parse(localStorage.getItem(VENDOR_KEY) || 'null');
            return (r && Array.isArray(r.vendors)) ? r.vendors : [];
        } catch (e) { return []; }
    }
    function loadFired() {
        try {
            var r = JSON.parse(localStorage.getItem(FIRED_KEY) || 'null');
            return r && typeof r === 'object' ? r : {};
        } catch (e) { return {}; }
    }
    function saveFired(f) {
        localStorage.setItem(FIRED_KEY, JSON.stringify(f));
    }
    function todayKey() {
        var d = new Date();
        var m = d.getMonth() + 1, day = d.getDate();
        return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
    }
    function alertMinutes(v) {
        if (!v || !v.alertBefore || v.alertBefore === 'off') return null;
        if (v.alertBefore === 'custom') {
            var c = parseInt(v.alertCustomMinutes, 10);
            return isNaN(c) || c < 1 ? null : c;
        }
        var n = parseInt(v.alertBefore, 10);
        return isNaN(n) || n < 1 ? null : n;
    }
    function formatTime12(hhmm) {
        if (!hhmm) return '';
        var p = String(hhmm).split(':');
        var h = parseInt(p[0], 10), m = p[1] || '00';
        if (isNaN(h)) return hhmm;
        var ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ap;
    }
    function isDeliveryDayToday(v) {
        if (!v.days || !v.days.length) return true;
        return v.days.indexOf(DAY_CODES[new Date().getDay()]) !== -1;
    }
    function getAlertTiming(v) {
        var mins = alertMinutes(v);
        if (mins == null || !v.cutoffTime) return null;
        if (!isDeliveryDayToday(v)) return null;

        var parts = String(v.cutoffTime).split(':');
        var ch = parseInt(parts[0], 10);
        var cm = parseInt(parts[1], 10) || 0;
        if (isNaN(ch)) return null;

        var now = new Date();
        var cutoff = new Date(now.getFullYear(), now.getMonth(), now.getDate(), ch, cm, 0, 0);
        var alertAt = new Date(cutoff.getTime() - mins * 60 * 1000);
        var windowEnd = new Date(cutoff.getTime() + 15 * 60 * 1000);
        if (now > windowEnd) return null;

        var fired = loadFired();
        var key = (v.id || v.name) + '|' + todayKey();
        if (fired[key]) return null;

        if (now >= alertAt) return 0;
        return alertAt.getTime() - now.getTime();
    }

    var banner = document.getElementById('pbj-cutoff-banner');
    var titleEl = document.getElementById('pbj-cutoff-title');
    var bodyEl = document.getElementById('pbj-cutoff-body');
    var showing = false;

    function markFired(v) {
        var fired = loadFired();
        fired[(v.id || v.name) + '|' + todayKey()] = Date.now();
        Object.keys(fired).forEach(function (k) {
            if (k.indexOf(todayKey()) === -1 && Object.keys(fired).length > 40) delete fired[k];
        });
        saveFired(fired);
    }

    function showBanner(v) {
        if (!banner) return;
        var caseMin = (v.caseMin !== '' && v.caseMin != null && !isNaN(parseFloat(v.caseMin)))
            ? (' Case minimum: ' + v.caseMin + '.')
            : '';
        titleEl.textContent = '⏰ ' + (v.name || 'Vendor') + ' cut-off soon';
        bodyEl.textContent = 'Order cut-off is ' + formatTime12(v.cutoffTime) + '.' + caseMin +
            ' Finish Auto-Order before you miss it!';
        banner.classList.add('show');
        showing = true;
        document.body.style.paddingTop = banner.offsetHeight + 'px';
    }

    function hideBanner() {
        if (!banner) return;
        banner.classList.remove('show');
        showing = false;
        document.body.style.paddingTop = '';
        if (alertQueue.length) {
            setTimeout(function () { fireAlert(alertQueue.shift()); }, 300);
        }
    }

    function fireAlert(v) {
        if (!v) return;
        markFired(v);
        showBanner(v);
        try {
            if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                var n = new Notification((v.name || 'Vendor') + ' order cut-off', {
                    body: 'Cut-off at ' + formatTime12(v.cutoffTime) + '. Open ilovepbj ops → Auto-Order.',
                    tag: 'pbj-cutoff-' + (v.id || v.name) + '-' + todayKey()
                });
                n.onclick = function () {
                    window.focus();
                    window.location.href = '/admin/auto-order';
                };
            }
        } catch (e) {}
    }

    function checkAndSchedule() {
        var vendors = loadVendors();
        var soonest = null;
        var soonestV = null;

        vendors.forEach(function (v) {
            var t = getAlertTiming(v);
            if (t === null) return;
            if (t === 0) {
                if (showing) alertQueue.push(v);
                else fireAlert(v);
                return;
            }
            if (soonest === null || t < soonest) {
                soonest = t;
                soonestV = v;
            }
        });

        if (soonest != null && soonestV && soonest < 24 * 60 * 60 * 1000) {
            setTimeout(function () {
                var t2 = getAlertTiming(soonestV);
                if (t2 === 0) fireAlert(soonestV);
                else checkAndSchedule();
            }, Math.min(soonest + 500, 2147483647));
        }
    }

    if (document.getElementById('pbj-cutoff-dismiss')) {
        document.getElementById('pbj-cutoff-dismiss').addEventListener('click', hideBanner);
    }

    setTimeout(checkAndSchedule, 800);
    setInterval(checkAndSchedule, 120000);
})();
</script>
<style>
    .pbj-shift-banner {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1100;
        background: linear-gradient(135deg, #1A2A44, #2E4A6E);
        color: white;
        padding: 12px 16px 14px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.18);
    }
    .pbj-shift-banner.show { display: block; }
    .pbj-shift-banner strong { display: block; font-size: 1.05rem; margin-bottom: 4px; }
    .pbj-shift-banner p { margin: 0 0 10px; font-size: 0.95rem; line-height: 1.35; opacity: 0.95; }
    .pbj-shift-banner .pbj-shift-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .pbj-shift-banner a, .pbj-shift-banner button {
        border: none;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.9rem;
        cursor: pointer;
        text-decoration: none;
        color: #111;
        background: white;
    }
    .pbj-shift-banner button.pbj-shift-dismiss { background: rgba(255,255,255,0.25); color: white; }
</style>
<div class="pbj-shift-banner" id="pbj-shift-banner" role="alert" aria-live="polite">
    <strong id="pbj-shift-title">Shift starting soon</strong>
    <p id="pbj-shift-body"></p>
    <div class="pbj-shift-actions">
        <a href="/admin/schedules" id="pbj-shift-open">Schedules</a>
        <button type="button" class="pbj-shift-dismiss" id="pbj-shift-dismiss">Dismiss</button>
    </div>
</div>
<script>
/** Shift start alerts (from Schedules settings) — in-app, browser, optional SMS */
(function () {
    var SCHED_KEY = 'pbj_admin_schedules_v1';
    var TEAM_KEYS = ['pbj_admin_team_v2', 'pbj_admin_team_v1'];
    var FIRED_KEY = 'pbj_shift_alert_fired_v1';
    var NOTIF_KEY = 'pbj_whiskings_notifications_v1';
    var DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var shiftQueue = [];
    var shiftShowing = false;

    function loadSched() {
        try {
            var r = JSON.parse(localStorage.getItem(SCHED_KEY) || 'null');
            return r && typeof r === 'object' ? r : { weeks: {}, alerts: null };
        } catch (e) { return { weeks: {}, alerts: null }; }
    }
    function loadTeamPeople() {
        for (var i = 0; i < TEAM_KEYS.length; i++) {
            try {
                var r = JSON.parse(localStorage.getItem(TEAM_KEYS[i]) || 'null');
                if (r && Array.isArray(r.people)) return r.people;
            } catch (e) {}
        }
        return [];
    }
    function loadFired() {
        try {
            var r = JSON.parse(localStorage.getItem(FIRED_KEY) || 'null');
            return r && typeof r === 'object' ? r : {};
        } catch (e) { return {}; }
    }
    function saveFired(f) {
        try { localStorage.setItem(FIRED_KEY, JSON.stringify(f)); } catch (e) {}
    }
    function schedulePrefsOn() {
        try {
            var r = JSON.parse(localStorage.getItem(NOTIF_KEY) || 'null');
            if (r && r.schedule === false) return false;
        } catch (e) {}
        return true;
    }
    function todayKey() {
        var d = new Date();
        var m = d.getMonth() + 1, day = d.getDate();
        return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
    }
    function mondayOf(d) {
        var x = new Date(d); x.setHours(12, 0, 0, 0);
        var day = x.getDay();
        var diff = day === 0 ? -6 : 1 - day;
        x.setDate(x.getDate() + diff);
        return x;
    }
    function weekKey(d) {
        var m = mondayOf(d);
        return m.getFullYear() + '-' + String(m.getMonth() + 1).padStart(2, '0') + '-' + String(m.getDate()).padStart(2, '0');
    }
    function alertMinutes(alerts) {
        if (!alerts || !alerts.alertBefore || alerts.alertBefore === 'off') return null;
        if (alerts.alertBefore === 'custom') {
            var c = parseInt(alerts.alertCustomMinutes, 10);
            return isNaN(c) || c < 1 ? null : c;
        }
        var n = parseInt(alerts.alertBefore, 10);
        return isNaN(n) || n < 1 ? null : n;
    }
    function formatTime12(hhmm) {
        if (!hhmm) return '';
        var p = String(hhmm).split(':');
        var h = parseInt(p[0], 10), m = p[1] || '00';
        if (isNaN(h)) return hhmm;
        var ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ap;
    }
    function phoneForShift(s, people) {
        if (s.phone) return String(s.phone).trim();
        for (var i = 0; i < people.length; i++) {
            var p = people[i];
            if (!p) continue;
            if ((s.personId && p.id === s.personId) || (s.name && p.name === s.name)) {
                return String(p.phone || '').trim();
            }
        }
        return '';
    }
    function todayShifts(sched) {
        var key = weekKey(new Date());
        var list = (sched.weeks && sched.weeks[key]) || [];
        var dayName = DAY_NAMES[new Date().getDay()];
        // Schedules store Mon–Sun labels; DAY_NAMES has Sun–Sat — map via SCHED_DAYS
        // Actually dayName from DAY_NAMES: Sun, Mon, ... matches schedule day labels
        return list.filter(function (s) { return s && s.day === dayName; });
    }
    function getShiftAlertTiming(s, mins) {
        if (!s || !s.start || mins == null) return null;
        var parts = String(s.start).split(':');
        var sh = parseInt(parts[0], 10);
        var sm = parseInt(parts[1], 10) || 0;
        if (isNaN(sh)) return null;
        var now = new Date();
        var startAt = new Date(now.getFullYear(), now.getMonth(), now.getDate(), sh, sm, 0, 0);
        var alertAt = new Date(startAt.getTime() - mins * 60 * 1000);
        // Stop nagging 10 min after start
        var windowEnd = new Date(startAt.getTime() + 10 * 60 * 1000);
        if (now > windowEnd) return null;
        var fired = loadFired();
        var fkey = (s.id || (s.name + s.start)) + '|' + todayKey();
        if (fired[fkey]) return null;
        if (now >= alertAt) return 0;
        return alertAt.getTime() - now.getTime();
    }

    var banner = document.getElementById('pbj-shift-banner');
    var titleEl = document.getElementById('pbj-shift-title');
    var bodyEl = document.getElementById('pbj-shift-body');

    function markFired(s) {
        var fired = loadFired();
        fired[(s.id || (s.name + s.start)) + '|' + todayKey()] = Date.now();
        var keys = Object.keys(fired);
        if (keys.length > 80) {
            keys.slice(0, keys.length - 60).forEach(function (k) { delete fired[k]; });
        }
        saveFired(fired);
    }

    function showShiftBanner(s, mins) {
        if (!banner) return;
        titleEl.textContent = '⏰ ' + (s.name || 'Shift') + ' starting soon';
        bodyEl.textContent = (s.role ? s.role + ' · ' : '') +
            formatTime12(s.start) + (s.end ? ' – ' + formatTime12(s.end) : '') +
            (mins ? ' · ~' + mins + ' min' : '') +
            (s.notes ? ' · ' + s.notes : '');
        banner.classList.add('show');
        shiftShowing = true;
        // Stack under vendor banner if both present
        var topPad = banner.offsetHeight;
        var other = document.getElementById('pbj-cutoff-banner');
        if (other && other.classList.contains('show')) topPad += other.offsetHeight;
        document.body.style.paddingTop = topPad + 'px';
    }

    function hideShiftBanner() {
        if (!banner) return;
        banner.classList.remove('show');
        shiftShowing = false;
        var other = document.getElementById('pbj-cutoff-banner');
        if (other && other.classList.contains('show')) {
            document.body.style.paddingTop = other.offsetHeight + 'px';
        } else {
            document.body.style.paddingTop = '';
        }
        if (shiftQueue.length) {
            setTimeout(function () { fireShiftAlert(shiftQueue.shift()); }, 300);
        }
    }

    function trySms(s, mins, phone) {
        if (!phone) return;
        fetch('/shift-alert-sms-api.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                phone: phone,
                name: s.name || '',
                role: s.role || '',
                start: s.start || '',
                end: s.end || '',
                minutes_before: mins || 0
            })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data && data.mode === 'device' && data.sms_link) {
                // Twilio not configured — open device SMS on mobile only once
                try {
                    if (/Mobi|Android/i.test(navigator.userAgent || '')) {
                        // Don't auto-open SMS app for every alert; leave link available via console only
                    }
                } catch (e) {}
            }
        }).catch(function () {});
    }

    function fireShiftAlert(payload) {
        if (!payload || !payload.shift) return;
        var s = payload.shift;
        var mins = payload.mins;
        var channels = payload.channels || {};
        markFired(s);
        if (channels.inApp !== false) showShiftBanner(s, mins);
        if (channels.browser !== false) {
            try {
                if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                    var n = new Notification((s.name || 'Shift') + ' starting soon', {
                        body: (s.role ? s.role + ' · ' : '') + formatTime12(s.start) +
                            (s.end ? ' – ' + formatTime12(s.end) : ''),
                        tag: 'pbj-shift-' + (s.id || s.name) + '-' + todayKey()
                    });
                    n.onclick = function () {
                        window.focus();
                        window.location.href = '/admin/schedules';
                    };
                }
            } catch (e) {}
        }
        if (channels.sms) {
            trySms(s, mins, payload.phone);
        }
    }

    function checkShiftAlerts() {
        if (!schedulePrefsOn()) return;
        var sched = loadSched();
        var mins = alertMinutes(sched.alerts);
        if (mins == null) return;
        var channels = (sched.alerts && sched.alerts.channels) || { inApp: true, browser: true, sms: false };
        if (channels.inApp === false && channels.browser === false && !channels.sms) return;

        var people = loadTeamPeople();
        var shifts = todayShifts(sched);
        var soonest = null;
        var soonestPayload = null;

        shifts.forEach(function (s) {
            var t = getShiftAlertTiming(s, mins);
            if (t === null) return;
            var payload = {
                shift: s,
                mins: mins,
                channels: channels,
                phone: phoneForShift(s, people)
            };
            if (t === 0) {
                if (shiftShowing) shiftQueue.push(payload);
                else fireShiftAlert(payload);
                return;
            }
            if (soonest === null || t < soonest) {
                soonest = t;
                soonestPayload = payload;
            }
        });

        if (soonest != null && soonestPayload && soonest < 24 * 60 * 60 * 1000) {
            setTimeout(function () {
                var t2 = getShiftAlertTiming(soonestPayload.shift, mins);
                if (t2 === 0) fireShiftAlert(soonestPayload);
                else checkShiftAlerts();
            }, Math.min(soonest + 500, 2147483647));
        }
    }

    if (document.getElementById('pbj-shift-dismiss')) {
        document.getElementById('pbj-shift-dismiss').addEventListener('click', hideShiftBanner);
    }

    setTimeout(checkShiftAlerts, 1200);
    setInterval(checkShiftAlerts, 120000);
})();
</script>
<script src="/permissions.js?v=1"></script>
<script src="/shared-state.js?v=4"></script>
<script src="/checklist-complete.js?v=1"></script>
<script src="/checklist-assign.js?v=1"></script>
<script src="/inv-shared-sync.js?v=1"></script>
<script src="/sales-playground-client.js?v=2"></script>
<script>
(function () {
    if (!('serviceWorker' in navigator)) return;
    // Register once per session load so Count Stock / Product Setup cache for offline walks
    navigator.serviceWorker.register('/sw.js?v=5').catch(function () {});
})();
</script>
