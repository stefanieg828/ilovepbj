<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';

$default_lists = [
    [
        'id' => 'opening',
        'title' => 'Opening',
        'icon' => '🌅',
        'hint' => $is_sweet ? 'Before first guests' : 'Before first guests',
        'items' => $is_sweet
            ? [
                'Unlock doors & disarm the alarm',
                'Lights on + playlist vibes started',
                'Walk the floor — chairs, tables, crumbs',
                'Host stand wiped, pens & menus ready',
                'Restrooms stocked (soap, TP, paper towels)',
                'Water stations filled & glassware polished',
                'POS open, drawer counted, printers online',
                'Review today\'s reservations & waitlist notes',
                'Sidework board posted for the shift',
                'Check specials board / 86 list with BOH',
                'Takeout station stocked (bags, utensils, sauces)',
                'Final walkthrough — ready for guests ✨',
            ]
            : [
                'Unlock doors & disarm alarm',
                'Turn on lights and music',
                'Inspect dining room (tables, chairs, floors)',
                'Prep host stand (menus, pens, waitlist)',
                'Stock restrooms (soap, TP, paper towels)',
                'Fill water stations & polish glassware',
                'Open POS, count drawer, check printers',
                'Review reservations and waitlist notes',
                'Post sidework assignments for the shift',
                'Confirm specials / 86 list with BOH',
                'Stock takeout station (bags, utensils, sauces)',
                'Final walkthrough — ready to open',
            ],
    ],
    [
        'id' => 'closing',
        'title' => 'Closing',
        'icon' => '🌙',
        'hint' => $is_sweet ? 'End clean & lock it in' : 'End clean and secure',
        'items' => $is_sweet
            ? [
                'Last tables cleared & wiped down',
                'Chairs pushed in, floor sweep complete',
                'Host stand reset for tomorrow',
                'Restrooms cleaned & restocked',
                'Water stations emptied / glassware put away',
                'Side stations wiped & restocked',
                'Takeout area cleaned & restocked',
                'POS closed, drawer counted & dropped',
                'Lost & found logged / guest items secured',
                'Trash emptied, liners replaced',
                'Lights & music off',
                'Doors locked & alarm set 🌙',
            ]
            : [
                'Clear and wipe all tables',
                'Push in chairs and sweep floors',
                'Reset host stand for next day',
                'Clean and restock restrooms',
                'Empty water stations / put away glassware',
                'Wipe and restock side stations',
                'Clean and restock takeout area',
                'Close POS, count drawer, complete drop',
                'Log lost & found / secure guest items',
                'Empty trash and replace liners',
                'Turn off lights and music',
                'Lock doors and set alarm',
            ],
    ],
];

$page_title = 'Opening & Closing';
$page_sub = $is_sweet ? 'FOH checklists — editable & live with the team' : 'FOH opening and closing checklists';
$hub_href = '/FOH';
$hub_label = pbj_back_to_hub('foh');
$intro_html = $is_sweet
    ? 'Add, edit, or remove any FOH task — progress is live across your restaurant group. Restore starters anytime ✨'
    : 'Add, edit, or remove FOH tasks. Progress syncs live across your restaurant group.';
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
    sharedKey: 'showtime_open_close_edit_v1',
    localKey: 'pbj_showtime_open_close_edit_v1',
    pageTitle: 'FOH Opening & Closing',
    href: '/FOH/opening-closing',
    userName: <?php echo json_encode($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Team'); ?>,
    notesEnabled: false,
    defaults: <?php echo json_encode($default_lists, JSON_UNESCAPED_UNICODE); ?>,
    perms: {
        add: 'foh.opening.add_tasks',
        uncheck: 'foh.opening.uncheck',
        print: 'foh.opening.print',
        checkOff: 'foh.opening.check_off',
        photos: 'foh.opening.attach_photos',
        edit: 'foh.opening.edit_tasks',
        restore: 'foh.opening.restore'
    }
});
</script>
</body>
</html>
