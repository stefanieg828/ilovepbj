<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

$default_lists = [
    [
        'id' => 'running',
        'title' => $is_sweet ? 'Running' : 'Running',
        'icon' => '✨',
        'hint' => $is_sweet ? 'During service' : 'During service',
        'items' => $is_sweet
            ? [
                'Tables set — silverware, napkins, clean & cute',
                'Water pitchers full & ice bin topped off',
                'Ketchup, jams & condiments wiped + refilled',
                'Bread / snack baskets restocked as needed',
                'High chairs & booster seats wiped & ready',
                'Kids menus, crayons & activities stocked',
                'Side station trash not overflowing',
                'Menus wiped (no sticky fingerprints!)',
                'Takeout extras staged (utensils, sauces, bags)',
                'Section floors quick-swept between rushes',
                'Empty glasses & bus bins not piling up',
                'Check in with host on waits & large parties',
            ]
            : [
                'Reset tables (silverware, napkins, clean tops)',
                'Keep water pitchers full; ice bin topped off',
                'Wipe and refill condiments',
                'Restock bread / snack baskets as needed',
                'Wipe high chairs and booster seats',
                'Stock kids menus, crayons, and activities',
                'Empty side station trash before full',
                'Wipe menus (remove fingerprints and smudges)',
                'Stage takeout extras (utensils, sauces, bags)',
                'Quick-sweep section floors between rushes',
                'Clear empty glasses and bus bins regularly',
                'Check in with host on waits and large parties',
            ],
    ],
    [
        'id' => 'closing',
        'title' => 'Closing',
        'icon' => '🌙',
        'hint' => $is_sweet ? 'End of shift' : 'End of shift',
        'items' => $is_sweet
            ? [
                'All tables in section wiped & reset',
                'Chairs pushed in + under-table crumbs gone',
                'Side station wiped, stocked & closed out',
                'Condiments wiped, refilled & lids tight',
                'Silverware rolled / polished for tomorrow',
                'Glassware polished & put away',
                'Ice bins emptied / drained as directed',
                'Trash & recycling taken out; new liners in',
                'High chairs wiped & stacked neatly',
                'Floor swept (and mopped if assigned)',
                'Server books balanced & cash-out complete',
                'Handoff notes written for the next shift 💕',
            ]
            : [
                'Wipe and reset all tables in section',
                'Push in chairs; clear under-table debris',
                'Wipe, stock, and close out side station',
                'Wipe, refill, and secure condiments',
                'Roll / polish silverware for next day',
                'Polish glassware and put away',
                'Empty / drain ice bins as directed',
                'Take out trash & recycling; replace liners',
                'Wipe high chairs and stack neatly',
                'Sweep floor (mop if assigned)',
                'Balance server book and complete cash-out',
                'Write handoff notes for the next shift',
            ],
    ],
];

$page_title = 'Server Sidework';
$page_sub = $is_sweet ? 'Station duties, stocking & handoffs' : 'Station duties, stocking, and handoffs';
$hub_href = '/FOH';
$hub_label = pbj_back_to_hub('foh');
$intro_html = $is_sweet
    ? 'Build your house sidework — edit any task, add new ones, and <strong>post handoffs</strong> for the next FOH crew (same notes as Messages → Shift Notes) ⭐'
    : 'Edit sidework tasks and post FOH handoffs that show up under Messages → Shift Notes.';
$notes_enabled = true;
$user_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Team';
include 'editable-checklist-shell.php';
?>
<script src="/shared-state.js?v=3"></script>
<script src="/jelly-shared.js"></script>
<script src="/checklist-photos.js?v=1"></script>
<script src="/checklist-complete.js?v=1"></script>
<script src="/editable-checklist.js?v=5"></script>
<script>
PbjEditableChecklist.mount({
    isSweet: <?php echo $is_sweet ? 'true' : 'false'; ?>,
    sharedKey: 'showtime_sidework_edit_v1',
    localKey: 'pbj_showtime_sidework_edit_v1',
    pageTitle: 'Server Sidework',
    href: '/FOH/sidework',
    notesEnabled: true,
    userName: <?php echo json_encode($user_name); ?>,
    defaults: <?php echo json_encode($default_lists, JSON_UNESCAPED_UNICODE); ?>,
    perms: {
        add: 'foh.sidework.add_tasks',
        uncheck: 'foh.sidework.uncheck',
        print: 'foh.sidework.print',
        checkOff: 'foh.sidework.check_off',
        photos: 'foh.sidework.attach_photos',
        edit: 'foh.sidework.edit_tasks',
        restore: 'foh.sidework.restore',
        handoff: 'foh.sidework.handoff'
    }
});
</script>
</body>
</html>
