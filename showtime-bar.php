<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

$default_lists = [
    [
        'id' => 'opening',
        'title' => $is_sweet ? 'Open' : 'Opening',
        'icon' => '🌅',
        'hint' => $is_sweet ? 'Pour-ready' : 'Pour-ready',
        'items' => $is_sweet
            ? [
                'Bar bank counted & POS station open',
                'Ice wells filled & scoop stored correctly',
                'Speed rail stocked (call brands ready)',
                'Well liquors checked & topped',
                'Draft lines poured / tasted — nothing funky',
                'Garnish tray prepped (citrus, olives, cherries, herbs)',
                'Bar tools clean & staged (shakers, strainers, jiggers)',
                'Glassware polished & stocked by type',
                'Coolers restocked (beer, wine, mixers, NA)',
                'Bar mats down, sinks clean, hand soap ready',
                'Specials / 86 list reviewed with FOH + kitchen',
                'Bar top wiped — ready for the first pour 🍸',
            ]
            : [
                'Count bar bank and open POS station',
                'Fill ice wells; store scoop correctly',
                'Stock speed rail with call brands',
                'Check and top well liquors',
                'Pour / taste draft lines',
                'Prep garnish tray (citrus, olives, cherries, herbs)',
                'Stage clean bar tools (shakers, strainers, jiggers)',
                'Polish and stock glassware by type',
                'Restock coolers (beer, wine, mixers, NA)',
                'Set mats; clean sinks; stock hand soap',
                'Review specials / 86 list with FOH and kitchen',
                'Wipe bar top — ready to open',
            ],
    ],
    [
        'id' => 'running',
        'title' => $is_sweet ? 'Service' : 'Service',
        'icon' => '🍹',
        'hint' => $is_sweet ? 'Mid-shift' : 'Mid-shift',
        'items' => $is_sweet
            ? [
                'Ice wells topped before they run low',
                'Garnish tray refilled & citrus looking fresh',
                'Glassware restocked during lulls',
                'Speed rail / wells topped as needed',
                'Spill mats wiped; sticky spots gone',
                'Dirty glassware not piling up',
                'Ticket times watched — no drinks dying in the window',
                'Open tabs checked (no forgotten guests)',
                'Draft handles wiped; drips cleaned',
                'Mixers, juices & NA options refilled',
                'Check in with servers on large parties / rounds',
                'Bar guest water / menus kept tidy ✨',
            ]
            : [
                'Top ice wells before they run low',
                'Refill garnish tray; keep citrus fresh',
                'Restock glassware during lulls',
                'Top speed rail / wells as needed',
                'Wipe spill mats and sticky spots',
                'Keep dirty glassware from piling up',
                'Watch ticket times; no stalled drinks',
                'Monitor open tabs',
                'Wipe draft handles; clean drips',
                'Refill mixers, juices, and NA options',
                'Check in with servers on large parties',
                'Keep bar guest water and menus tidy',
            ],
    ],
    [
        'id' => 'closing',
        'title' => $is_sweet ? 'Close' : 'Closing',
        'icon' => '🌙',
        'hint' => $is_sweet ? 'Break down clean' : 'Break down clean',
        'items' => $is_sweet
            ? [
                'Last call announced & completed',
                'All open tabs closed / cash-out started',
                'Garnishes broken down & stored properly',
                'Wells covered / bottles wiped & put away',
                'Ice dumped or covered per house policy',
                'Glassware washed, polished & shelved',
                'Bar tools washed & air-drying',
                'Bar top, sinks & mats cleaned & sanitized',
                'Coolers wiped & restocked for tomorrow',
                'Trash & recycling out; liners replaced',
                'Floor swept / mopped as assigned',
                'POS closed, bank counted & drop complete 🍸',
            ]
            : [
                'Complete last call',
                'Close open tabs; begin cash-out',
                'Break down and store garnishes',
                'Cover wells; wipe and put away bottles',
                'Dump or cover ice per house policy',
                'Wash, polish, and shelf glassware',
                'Wash bar tools; air-dry',
                'Clean and sanitize bar top, sinks, mats',
                'Wipe coolers; restock for next day',
                'Take out trash and recycling; replace liners',
                'Sweep / mop floor as assigned',
                'Close POS; count bank and complete drop',
            ],
    ],
];

$page_title = 'Bar Operations';
$page_sub = $is_sweet ? 'Well, rail, garnishes — editable & live' : 'Bar open, service, and close checklists';
$hub_href = '/FOH';
$hub_label = pbj_back_to_hub('foh');
$intro_html = $is_sweet
    ? 'Build your bar bible — edit every task, add house pour rules, and keep the whole crew in sync 🍸'
    : 'Edit bar tasks and keep open/service/close lists live across the restaurant.';
$notes_enabled = false;
include 'editable-checklist-shell.php';
?>
<script src="/shared-state.js?v=3"></script>
<script src="/checklist-photos.js?v=1"></script>
<script src="/checklist-complete.js?v=1"></script>
<script src="/editable-checklist.js?v=5"></script>
<script>
PbjEditableChecklist.mount({
    isSweet: <?php echo $is_sweet ? 'true' : 'false'; ?>,
    sharedKey: 'showtime_bar_edit_v1',
    localKey: 'pbj_showtime_bar_edit_v1',
    pageTitle: 'Bar Operations',
    href: '/FOH/bar',
    userName: <?php echo json_encode($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Team'); ?>,
    notesEnabled: false,
    defaults: <?php echo json_encode($default_lists, JSON_UNESCAPED_UNICODE); ?>,
    perms: {
        add: 'foh.bar.add_tasks',
        uncheck: 'foh.bar.uncheck',
        print: 'foh.bar.print',
        checkOff: 'foh.bar.check_off',
        photos: 'foh.bar.attach_photos',
        edit: 'foh.bar.edit_tasks',
        restore: 'foh.bar.restore'
    }
});
</script>
</body>
</html>
