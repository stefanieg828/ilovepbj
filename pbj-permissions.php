<?php
/**
 * Role permissions catalog, defaults, load/save for ilovepbj ops.
 * Roles: owner, gm, manager, foh, boh (admin aliases to gm).
 */

if (!defined('PBJ_PERMISSIONS_LOADED')) {
    define('PBJ_PERMISSIONS_LOADED', true);
}

/** @return array<string,string> role key => label */
function pbj_permission_roles(): array {
    return [
        'owner' => 'Owner',
        'gm' => 'General Manager',
        'manager' => 'Manager / Supervisor',
        'foh' => 'FOH (Server / Host / Bartender)',
        'boh' => 'BOH (Line / Prep / Busser / Dish)',
    ];
}

function pbj_normalize_access_role(?string $role): string {
    $r = strtolower(trim((string) $role));
    if ($r === 'admin') {
        return 'gm';
    }
    $allowed = array_keys(pbj_permission_roles());
    if (!in_array($r, $allowed, true)) {
        return 'foh';
    }
    return $r;
}

/**
 * Catalog: section groups with permission keys and labels.
 * @return list<array{id:string,label:string,items:list<array{key:string,label:string}>}>
 */
function pbj_permission_catalog(): array {
    $chk = static function (string $prefix, string $handoff = ''): array {
        $items = [
            ['key' => $prefix . '.add_tasks', 'label' => 'Add Tasks'],
            ['key' => $prefix . '.uncheck', 'label' => 'Uncheck List'],
            ['key' => $prefix . '.print', 'label' => 'Print List'],
            ['key' => $prefix . '.check_off', 'label' => 'Check Off Tasks'],
            ['key' => $prefix . '.attach_photos', 'label' => 'Attach Task Photos'],
            ['key' => $prefix . '.edit_tasks', 'label' => 'Edit / Remove Tasks'],
            ['key' => $prefix . '.restore', 'label' => 'Restore Starter Lists'],
        ];
        if ($handoff !== '') {
            $items[] = ['key' => $handoff, 'label' => 'Shift Handoff Notes'];
        }
        return $items;
    };

    return [
        [
            'id' => 'foh_opening',
            'label' => 'FOH · Opening & Closing',
            'items' => $chk('foh.opening'),
        ],
        [
            'id' => 'foh_sidework',
            'label' => 'FOH · Server Sidework',
            'items' => $chk('foh.sidework', 'foh.sidework.handoff'),
        ],
        [
            'id' => 'foh_bar',
            'label' => 'FOH · Bar Operations',
            'items' => $chk('foh.bar'),
        ],
        [
            'id' => 'foh_floor',
            'label' => 'FOH · Floor Plan & Tables',
            'items' => [
                ['key' => 'foh.floor.edit_layout', 'label' => 'Edit Floor Plan & Tables'],
                ['key' => 'foh.floor.select_fill', 'label' => 'Select Table & Fill Out Information'],
                ['key' => 'foh.floor.edit_sections', 'label' => 'Add / Edit / Remove / Reorder Sections'],
                ['key' => 'foh.floor.fill_sections', 'label' => 'Fill Out Sections (server names)'],
                ['key' => 'foh.floor.reset', 'label' => 'Reset Floor'],
                ['key' => 'foh.floor.turn_goal', 'label' => 'Fill in Turn Goal'],
            ],
        ],
        [
            'id' => 'foh_res',
            'label' => 'FOH · Reservations, Waitlist & To-Gos',
            'items' => [
                ['key' => 'foh.res.add', 'label' => 'Add Reservation'],
                ['key' => 'foh.res.seat_status', 'label' => 'Seat / No-show / Cancel Reservation'],
                ['key' => 'foh.res.remove', 'label' => 'Remove Reservation'],
                ['key' => 'foh.res.print', 'label' => 'Print / Save Reservation Lists'],
                ['key' => 'foh.wait.add', 'label' => 'Add Waitlist'],
                ['key' => 'foh.wait.notify_seat', 'label' => 'Notify / Seat Waitlist'],
                ['key' => 'foh.wait.remove', 'label' => 'Remove Waitlist'],
                ['key' => 'foh.togo.add', 'label' => 'Add To-Go Order'],
                ['key' => 'foh.togo.status', 'label' => 'Update To-Go Status'],
                ['key' => 'foh.togo.remove', 'label' => 'Remove To-Go Order'],
            ],
        ],
        [
            'id' => 'foh_pos',
            'label' => 'FOH · POS Quick Reference',
            'items' => [
                ['key' => 'foh.pos.add_category', 'label' => 'Add New Category'],
                ['key' => 'foh.pos.expand', 'label' => 'Expand All & Collapse All'],
                ['key' => 'foh.pos.add_entry', 'label' => 'Add Entry'],
                ['key' => 'foh.pos.edit_category', 'label' => 'Edit & Remove Category'],
                ['key' => 'foh.pos.reset', 'label' => 'Reset to Empty Shells'],
            ],
        ],
        [
            'id' => 'boh_prep',
            'label' => 'BOH · Prep Lists & Line Checks',
            'items' => [
                ['key' => 'boh.prep.sync_recipes', 'label' => 'Sync From Recipes'],
                ['key' => 'boh.prep.open_recipes', 'label' => 'Open Recipes'],
                ['key' => 'boh.prep.add_station', 'label' => 'Add Station'],
                ['key' => 'boh.prep.uncheck', 'label' => 'Uncheck All'],
                ['key' => 'boh.prep.print_all', 'label' => 'Print All Stations'],
                ['key' => 'boh.prep.check_off', 'label' => 'Check Off / Strike Through Items'],
                ['key' => 'boh.prep.attach_photos', 'label' => 'Attach Task Photos'],
                ['key' => 'boh.prep.add_item', 'label' => 'Add Prep Item'],
                ['key' => 'boh.prep.print_one', 'label' => 'Print Individual List'],
                ['key' => 'boh.prep.edit_station', 'label' => 'Edit Station'],
                ['key' => 'boh.prep.delete_station', 'label' => 'Remove Station'],
                ['key' => 'boh.prep.reset', 'label' => 'Reset Shells'],
            ],
        ],
        [
            'id' => 'boh_opening',
            'label' => 'BOH · Opening & Closing',
            'items' => $chk('boh.opening'),
        ],
        [
            'id' => 'boh_recipes',
            'label' => 'BOH · Menu & Standardized Recipes',
            'items' => [
                ['key' => 'boh.recipes.menu_view', 'label' => 'Menu Engineering (View)'],
                ['key' => 'boh.recipes.menu_edit', 'label' => 'Menu Engineering (Edit)'],
                ['key' => 'boh.recipes.std_view', 'label' => 'Standardized Recipes (View)'],
                ['key' => 'boh.recipes.std_edit', 'label' => 'Standardized Recipes (Edit)'],
                ['key' => 'boh.recipes.costing_view', 'label' => 'Costing Sheet (View)'],
                ['key' => 'boh.recipes.costing_edit', 'label' => 'Costing Sheet (Edit)'],
                ['key' => 'boh.recipes.yields_use', 'label' => 'Produce & Meat Yields (Use)'],
            ],
        ],
        [
            'id' => 'boh_cleaning',
            'label' => 'BOH · Cleaning Schedule',
            'items' => [
                ['key' => 'boh.cleaning.add_list', 'label' => 'Add List'],
                ['key' => 'boh.cleaning.uncheck', 'label' => 'Uncheck All'],
                ['key' => 'boh.cleaning.print', 'label' => 'Print Schedule'],
                ['key' => 'boh.cleaning.export', 'label' => 'Export CSV'],
                ['key' => 'boh.cleaning.check_off', 'label' => 'Check Off Tasks'],
                ['key' => 'boh.cleaning.attach_photos', 'label' => 'Attach Task Photos'],
                ['key' => 'boh.cleaning.edit_tasks', 'label' => 'Edit & Remove Tasks'],
                ['key' => 'boh.cleaning.add_task', 'label' => 'Add Task'],
                ['key' => 'boh.cleaning.edit_list', 'label' => 'Edit & Remove List'],
                ['key' => 'boh.cleaning.restore', 'label' => 'Restore Starter Lists'],
            ],
        ],
        [
            'id' => 'boh_temps',
            'label' => 'BOH · Temps & Food Safety',
            'items' => [
                ['key' => 'boh.temps.print', 'label' => 'Print Log / History'],
                ['key' => 'boh.temps.export', 'label' => 'Export History CSV'],
                ['key' => 'boh.temps.add_equipment', 'label' => 'Add Equipment'],
                ['key' => 'boh.temps.save_round', 'label' => 'Save This Round'],
                ['key' => 'boh.temps.temp_round', 'label' => 'Temp Round'],
                ['key' => 'boh.temps.edit_equipment', 'label' => 'Edit & Remove Equipment'],
                ['key' => 'boh.temps.enter_temps', 'label' => 'Enter Temps'],
                ['key' => 'boh.temps.reset', 'label' => 'Reset Equipment Shells'],
            ],
        ],
        [
            'id' => 'boh_tools',
            'label' => 'BOH · Quick Tools',
            'items' => [
                ['key' => 'boh.tools.use', 'label' => 'Use Quick Tools'],
            ],
        ],
        [
            'id' => 'admin_team',
            'label' => 'Admin · Team & Roles',
            'items' => [
                ['key' => 'admin.team.roster.add', 'label' => 'Crew Roster — Add Teammate'],
                ['key' => 'admin.team.roster.print', 'label' => 'Crew Roster — Print'],
                ['key' => 'admin.team.roster.export', 'label' => 'Crew Roster — Export CSV'],
                ['key' => 'admin.team.spotlight.view', 'label' => 'Employee Spotlight — View'],
                ['key' => 'admin.team.spotlight.manage', 'label' => 'Employee Spotlight — Crown / Edit'],
                ['key' => 'admin.team.permissions_manage', 'label' => 'Permissions Management'],
                ['key' => 'admin.team.onboarding.create_invite', 'label' => 'Create Invite Code'],
                ['key' => 'admin.team.onboarding.edit_tracks', 'label' => 'Edit & Remove Training Tracks'],
                ['key' => 'admin.team.onboarding.add_track', 'label' => 'Add Training Track'],
                ['key' => 'admin.team.onboarding.uncheck_training', 'label' => 'Uncheck All Training'],
                ['key' => 'admin.team.docs.edit_guide', 'label' => 'Handbook / Training — Editable Guide'],
                ['key' => 'admin.team.docs.upload', 'label' => 'Handbook / Training — Upload File'],
                ['key' => 'admin.team.docs.print', 'label' => 'Handbook / Training — Print'],
                ['key' => 'admin.team.docs.reset', 'label' => 'Handbook / Training — Reset Starters'],
                ['key' => 'admin.team.docs.reorder', 'label' => 'Handbook / Training — Move Up & Down'],
                ['key' => 'admin.team.docs.edit_section', 'label' => 'Handbook / Training — Edit & Remove Sections'],
                ['key' => 'admin.team.docs.add_section', 'label' => 'Handbook / Training — Add Section'],
            ],
        ],
        [
            'id' => 'admin_ops',
            'label' => 'Admin · Restaurant Operations',
            'items' => [
                ['key' => 'admin.ops.settings.location', 'label' => 'Settings — Location'],
                ['key' => 'admin.ops.settings.hours', 'label' => 'Settings — Hours'],
                ['key' => 'admin.ops.settings.policies', 'label' => 'Settings — House Policies'],
                ['key' => 'admin.ops.settings.phone', 'label' => 'Settings — Phone List'],
                ['key' => 'admin.ops.docs.edit_guide', 'label' => 'SOPs / Health — Editable Guide'],
                ['key' => 'admin.ops.docs.upload', 'label' => 'SOPs / Health — Upload File'],
                ['key' => 'admin.ops.docs.sections', 'label' => 'SOPs / Health — Sections (print/reset/edit)'],
                ['key' => 'ops.receive_list_completion', 'label' => 'Receive checklist & prep completion alerts'],
            ],
        ],
        [
            'id' => 'admin_schedules',
            'label' => 'Admin · Schedules & Shifts',
            'items' => [
                ['key' => 'admin.schedules.view', 'label' => 'View Schedules / My Schedule'],
                ['key' => 'admin.schedules.add_shift', 'label' => 'Add Shift'],
                ['key' => 'admin.schedules.clear_week', 'label' => 'Clear This Week'],
                ['key' => 'admin.schedules.print', 'label' => 'Print / Save Schedule'],
                ['key' => 'admin.schedules.view_wages', 'label' => 'View labor $ / wages on schedule (Owner, GM, Manager)'],
                ['key' => 'admin.schedules.trade', 'label' => 'Give up / swap / claim shifts'],
                ['key' => 'admin.schedules.approve', 'label' => 'Approve shift trades'],
                ['key' => 'admin.schedules.manage_settings', 'label' => 'Schedule trade settings (Owner / GM)'],
            ],
        ],
        [
            'id' => 'admin_reports',
            'label' => 'Admin · Reports & Sales',
            'items' => [
                ['key' => 'admin.reports.view', 'label' => 'View Reports & Sales'],
                ['key' => 'admin.reports.edit', 'label' => 'Edit Reports & Sales'],
            ],
        ],
        [
            'id' => 'admin_inventory',
            'label' => 'Admin · Inventory & Vendors',
            'items' => [
                ['key' => 'admin.inventory.view', 'label' => 'View Inventory & Vendors'],
                ['key' => 'admin.inventory.edit', 'label' => 'Edit Inventory & Vendors'],
            ],
        ],
        [
            'id' => 'admin_catering',
            'label' => 'Admin · Catering',
            'items' => [
                ['key' => 'admin.catering.view', 'label' => 'View Catering'],
                ['key' => 'admin.catering.edit', 'label' => 'Edit Catering'],
            ],
        ],
        [
            'id' => 'admin_compliance',
            'label' => 'Admin · Compliance & Certs',
            'items' => [
                ['key' => 'admin.compliance.view', 'label' => 'View Compliance and Certs'],
                ['key' => 'admin.compliance.edit', 'label' => 'Edit Compliance and Certs'],
                ['key' => 'admin.compliance.add_cert', 'label' => 'Add Log Certification'],
                ['key' => 'admin.compliance.signoff', 'label' => 'Record Sign-off'],
                ['key' => 'admin.compliance.setup', 'label' => 'Set Up (Edit & Remove)'],
                ['key' => 'admin.compliance.alerts', 'label' => 'Alert Window'],
            ],
        ],
        [
            'id' => 'messages',
            'label' => 'Messages',
            'items' => [
                ['key' => 'messages.announcements.post', 'label' => 'Team Announcements — Post'],
                ['key' => 'messages.announcements.view', 'label' => 'Team Announcements — View'],
                ['key' => 'messages.broadcasts.post', 'label' => 'Manager Broadcasts — Post'],
                ['key' => 'messages.broadcasts.view', 'label' => 'Manager Broadcasts — View'],
                ['key' => 'messages.shift_notes.post', 'label' => 'Shift Notes — Post'],
                ['key' => 'messages.shift_notes.view', 'label' => 'Shift Notes — View'],
                ['key' => 'messages.dms.send', 'label' => 'Direct Messages — Send'],
                ['key' => 'messages.dms.view', 'label' => 'Direct Messages — View'],
                ['key' => 'messages.foh_updates.post', 'label' => 'FOH Updates — Post'],
                ['key' => 'messages.foh_updates.view', 'label' => 'FOH Updates — View'],
                ['key' => 'messages.boh_updates.post', 'label' => 'BOH Updates — Post'],
                ['key' => 'messages.boh_updates.view', 'label' => 'BOH Updates — View'],
            ],
        ],
        [
            'id' => 'settings',
            'label' => 'Settings (User Level)',
            'items' => [
                ['key' => 'settings.theme', 'label' => 'Change Own Theme'],
                ['key' => 'settings.profile', 'label' => 'Edit Own Profile'],
                ['key' => 'settings.view_own_perms', 'label' => 'View Own Permissions'],
                ['key' => 'settings.notifications', 'label' => 'Manage Personal Notifications'],
            ],
        ],
    ];
}

/** Flat list of all permission keys */
function pbj_permission_all_keys(): array {
    $keys = [];
    foreach (pbj_permission_catalog() as $group) {
        foreach ($group['items'] as $item) {
            $keys[] = $item['key'];
        }
    }
    return $keys;
}

/**
 * Default matrix: role => key => bool
 * @return array<string, array<string, bool>>
 */
function pbj_permissions_default_matrix(): array {
    $keys = pbj_permission_all_keys();
    $allTrue = [];
    foreach ($keys as $k) {
        $allTrue[$k] = true;
    }

    $set = static function (array $base, array $on, array $off = []) use ($keys): array {
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = false;
        }
        foreach ($on as $k) {
            if (isset($out[$k]) || in_array($k, $keys, true)) {
                $out[$k] = true;
            }
        }
        // prefix helpers: 'foh.opening.*'
        foreach ($on as $pat) {
            if (substr($pat, -2) === '.*') {
                $prefix = substr($pat, 0, -1); // keep trailing .
                foreach ($keys as $k) {
                    if (strpos($k, $prefix) === 0) {
                        $out[$k] = true;
                    }
                }
            }
        }
        foreach ($off as $k) {
            if (isset($out[$k])) {
                $out[$k] = false;
            }
            if (substr($k, -2) === '.*') {
                $prefix = substr($k, 0, -1);
                foreach ($keys as $kk) {
                    if (strpos($kk, $prefix) === 0) {
                        $out[$kk] = false;
                    }
                }
            }
        }
        return $out;
    };

    $managerOn = [
        'foh.*',
        'boh.*',
        'admin.team.roster.*',
        'admin.team.spotlight.*',
        'admin.team.onboarding.*',
        'admin.team.docs.*',
        'admin.ops.*',
        'ops.receive_list_completion',
        'admin.schedules.*',
        'admin.reports.*',
        'admin.inventory.*',
        'admin.catering.*',
        'admin.compliance.*',
        'messages.*',
        'settings.*',
    ];

    $fohOn = [
        'foh.opening.check_off', 'foh.opening.print', 'foh.opening.attach_photos',
        'foh.sidework.check_off', 'foh.sidework.print', 'foh.sidework.handoff', 'foh.sidework.attach_photos',
        'foh.bar.check_off', 'foh.bar.print', 'foh.bar.attach_photos',
        'foh.floor.select_fill', 'foh.floor.fill_sections', 'foh.floor.turn_goal',
        'foh.res.add', 'foh.res.seat_status', 'foh.res.print',
        'foh.wait.add', 'foh.wait.notify_seat',
        'foh.togo.add', 'foh.togo.status',
        'foh.pos.expand',
        'boh.recipes.menu_view', 'boh.recipes.std_view', 'boh.recipes.yields_use',
        'messages.announcements.view',
        'messages.broadcasts.view',
        'messages.shift_notes.post', 'messages.shift_notes.view',
        'messages.dms.send', 'messages.dms.view',
        'messages.foh_updates.post', 'messages.foh_updates.view',
        'admin.team.spotlight.view',
        'admin.schedules.view',
        'admin.schedules.trade',
        'settings.*',
    ];

    $bohOn = [
        'boh.prep.check_off', 'boh.prep.print_all', 'boh.prep.print_one', 'boh.prep.open_recipes', 'boh.prep.uncheck', 'boh.prep.attach_photos',
        'boh.opening.check_off', 'boh.opening.print', 'boh.opening.attach_photos',
        'boh.recipes.menu_view', 'boh.recipes.std_view', 'boh.recipes.costing_view', 'boh.recipes.yields_use',
        'boh.cleaning.check_off', 'boh.cleaning.print', 'boh.cleaning.uncheck', 'boh.cleaning.attach_photos',
        'boh.temps.print', 'boh.temps.enter_temps', 'boh.temps.save_round', 'boh.temps.temp_round',
        'boh.tools.use',
        'messages.announcements.view',
        'messages.broadcasts.view',
        'messages.shift_notes.post', 'messages.shift_notes.view',
        'messages.dms.send', 'messages.dms.view',
        'messages.boh_updates.post', 'messages.boh_updates.view',
        'admin.team.spotlight.view',
        'admin.schedules.view',
        'admin.schedules.trade',
        'settings.*',
    ];

    return [
        'owner' => $allTrue,
        'gm' => $allTrue,
        'manager' => $set([], $managerOn, ['admin.team.permissions_manage', 'admin.schedules.manage_settings']),
        'foh' => $set([], $fohOn),
        'boh' => $set([], $bohOn),
    ];
}

function pbj_permissions_ensure_settings_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_settings (
        restaurant_id INT NOT NULL PRIMARY KEY,
        settings_json LONGTEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function pbj_permissions_resolve_restaurant_id(PDO $pdo): int {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid > 0) {
        try {
            $own = $pdo->prepare('SELECT id FROM restaurants WHERE owner_id = ? ORDER BY id DESC LIMIT 1');
            $own->execute([$uid]);
            $ownedId = (int) ($own->fetchColumn() ?: 0);
            if ($ownedId > 0) {
                return $ownedId;
            }
        } catch (Exception $e) {
            // ignore
        }
        $stmt = $pdo->prepare('SELECT restaurant_id FROM user_restaurant WHERE user_id = ? ORDER BY joined_at ASC LIMIT 1');
        $stmt->execute([$uid]);
        $rid = $stmt->fetchColumn();
        if ($rid) {
            return (int) $rid;
        }
    }
    // Never dump strangers into DEMO-PBJ
    if (defined('AUTH_BYPASS') && AUTH_BYPASS) {
        $stmt = $pdo->query("SELECT id FROM restaurants WHERE invite_code = 'DEMO-PBJ' LIMIT 1");
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }
    }
    return 0;
}

function pbj_permissions_load_settings(PDO $pdo, int $rid): array {
    pbj_permissions_ensure_settings_table($pdo);
    $stmt = $pdo->prepare('SELECT settings_json FROM restaurant_settings WHERE restaurant_id = ?');
    $stmt->execute([$rid]);
    $raw = $stmt->fetchColumn();
    if (!$raw) {
        return [];
    }
    $j = json_decode((string) $raw, true);
    return is_array($j) ? $j : [];
}

function pbj_permissions_save_settings(PDO $pdo, int $rid, array $settings): void {
    pbj_permissions_ensure_settings_table($pdo);
    $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare(
        'INSERT INTO restaurant_settings (restaurant_id, settings_json) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE settings_json = VALUES(settings_json)'
    );
    $stmt->execute([$rid, $json]);
}

/**
 * Full-open matrix: every role has every permission key.
 * Used for playground houses so demos aren’t blocked by role.
 * @return array<string, array<string, bool>>
 */
function pbj_permissions_full_open_matrix(): array {
    if (function_exists('pbj_sales_full_permissions_matrix')) {
        return pbj_sales_full_permissions_matrix();
    }
    $keys = pbj_permission_all_keys();
    $allTrue = [];
    foreach ($keys as $k) {
        $allTrue[$k] = true;
    }
    $matrix = [];
    foreach (array_keys(pbj_permission_roles()) as $role) {
        $matrix[$role] = $allTrue;
    }
    return $matrix;
}

/** True when this restaurant is a demo/sales playground (all roles fully allowed). */
function pbj_permissions_restaurant_is_playground(PDO $pdo, int $rid): bool {
    if ($rid <= 0) {
        return false;
    }
    if (function_exists('pbj_restaurant_is_playground')) {
        return pbj_restaurant_is_playground($pdo, $rid);
    }
    if (function_exists('pbj_restaurant_is_playground_resetable')
        && pbj_restaurant_is_playground_resetable($pdo, $rid)) {
        return true;
    }
    try {
        $stmt = $pdo->prepare('SELECT invite_code FROM restaurants WHERE id = ? LIMIT 1');
        $stmt->execute([$rid]);
        $code = strtoupper(trim((string) $stmt->fetchColumn()));
        $codes = function_exists('pbj_playground_invite_codes')
            ? pbj_playground_invite_codes()
            : ['DEMO-PBJ', 'SALES-PBJ'];
        return $code !== '' && in_array($code, $codes, true);
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * @return array<string, array<string, bool>>
 */
function pbj_permissions_load_matrix(PDO $pdo, int $rid): array {
    // Playgrounds: every role can do everything (role choice is cosmetic for demos)
    if (pbj_permissions_restaurant_is_playground($pdo, $rid)) {
        return pbj_permissions_full_open_matrix();
    }

    $defaults = pbj_permissions_default_matrix();
    $settings = pbj_permissions_load_settings($pdo, $rid);
    $stored = $settings['permissions']['matrix'] ?? null;
    if (!is_array($stored)) {
        return $defaults;
    }
    $keys = pbj_permission_all_keys();
    $roles = array_keys(pbj_permission_roles());
    $out = [];
    foreach ($roles as $role) {
        $out[$role] = [];
        $row = is_array($stored[$role] ?? null) ? $stored[$role] : ($defaults[$role] ?? []);
        foreach ($keys as $k) {
            if ($role === 'owner') {
                $out[$role][$k] = true;
                continue;
            }
            if (array_key_exists($k, $row)) {
                $out[$role][$k] = (bool) $row[$k];
            } else {
                $out[$role][$k] = (bool) ($defaults[$role][$k] ?? false);
            }
        }
        // GM always can manage permissions
        if ($role === 'gm') {
            $out[$role]['admin.team.permissions_manage'] = true;
        }
    }
    return $out;
}

function pbj_permissions_user_role(PDO $pdo, int $uid, int $rid): string {
    if ($uid <= 0) {
        return pbj_normalize_access_role($_SESSION['role'] ?? 'foh');
    }
    $stmt = $pdo->prepare('SELECT role FROM user_restaurant WHERE user_id = ? AND restaurant_id = ?');
    $stmt->execute([$uid, $rid]);
    $r = $stmt->fetchColumn();
    if ($r) {
        return pbj_normalize_access_role((string) $r);
    }
    return pbj_normalize_access_role($_SESSION['role'] ?? 'foh');
}

/**
 * @return array<string, bool>
 */
function pbj_permissions_grants_for_role(array $matrix, string $role): array {
    $role = pbj_normalize_access_role($role);
    if ($role === 'owner') {
        $all = [];
        foreach (pbj_permission_all_keys() as $k) {
            $all[$k] = true;
        }
        return $all;
    }
    return $matrix[$role] ?? (pbj_permissions_default_matrix()[$role] ?? []);
}

function pbj_permissions_can_from_grants(array $grants, string $key): bool {
    return !empty($grants[$key]);
}

/**
 * Site operator (you only): always full access regardless of role matrix.
 * Exact email only — stefanieg828@gmail.com
 */
function pbj_permissions_omnipotent_email(): string {
    return 'stefanieg828@gmail.com';
}

function pbj_permissions_is_omnipotent(PDO $pdo, int $uid): bool {
    $want = pbj_permissions_omnipotent_email();
    $emails = [];
    if (!empty($_SESSION['email'])) {
        $emails[] = strtolower(trim((string) $_SESSION['email']));
    }
    if ($uid > 0) {
        try {
            $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$uid]);
            $row = $stmt->fetchColumn();
            if ($row) {
                $emails[] = strtolower(trim((string) $row));
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
    foreach ($emails as $e) {
        if ($e === $want) {
            return true;
        }
    }
    return false;
}

/** @return array<string,bool> */
function pbj_permissions_all_grants_true(): array {
    $all = [];
    foreach (pbj_permission_all_keys() as $k) {
        $all[$k] = true;
    }
    return $all;
}

/**
 * Resolve current session user's grants.
 * @return array{role:string,rid:int,grants:array<string,bool>,matrix?:array,omnipotent?:bool}
 */
function pbj_permissions_current(PDO $pdo, bool $includeMatrix = false): array {
    $rid = pbj_permissions_resolve_restaurant_id($pdo);
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    $role = pbj_permissions_user_role($pdo, $uid, $rid);
    $matrix = pbj_permissions_load_matrix($pdo, $rid);
    $grants = pbj_permissions_grants_for_role($matrix, $role);
    $omnipotent = pbj_permissions_is_omnipotent($pdo, $uid);
    $playground = pbj_permissions_restaurant_is_playground($pdo, $rid);
    // Owner always full; designated operator always full; playgrounds unlock every role
    if ($role === 'owner' || $omnipotent || $playground) {
        $grants = pbj_permissions_all_grants_true();
    }
    $out = [
        'role' => $role,
        'rid' => $rid,
        'uid' => $uid,
        'grants' => $grants,
        'omnipotent' => $omnipotent,
        'playground' => $playground,
    ];
    if ($includeMatrix) {
        $out['matrix'] = $matrix;
    }
    return $out;
}

function pbj_can(string $key): bool {
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return true; // fail open if no DB mid-bootstrap
    }
    static $cache = null;
    if ($cache === null) {
        try {
            $cache = pbj_permissions_current($pdo);
        } catch (Throwable $e) {
            return true;
        }
    }
    return pbj_permissions_can_from_grants($cache['grants'] ?? [], $key);
}

/**
 * Save matrix; forces owner all true; gm keeps permissions_manage.
 * @param array<string, array<string, bool>> $matrix
 */
function pbj_permissions_save_matrix(PDO $pdo, int $rid, array $matrix): array {
    $defaults = pbj_permissions_default_matrix();
    $keys = pbj_permission_all_keys();
    $roles = array_keys(pbj_permission_roles());

    // Playgrounds stay fully open — role is cosmetic; don’t let a matrix edit lock demos
    if (pbj_permissions_restaurant_is_playground($pdo, $rid)) {
        $clean = pbj_permissions_full_open_matrix();
        $settings = pbj_permissions_load_settings($pdo, $rid);
        $settings['permissions'] = [
            'version' => 1,
            'matrix' => $clean,
            'updatedAt' => time() * 1000,
            'playground_full_access' => true,
        ];
        $settings['floorAllowedRoles'] = ['owner', 'admin', 'gm', 'manager', 'foh', 'boh'];
        $settings['floorAllowedUserIds'] = [];
        pbj_permissions_save_settings($pdo, $rid, $settings);
        return $clean;
    }

    $clean = [];
    foreach ($roles as $role) {
        $clean[$role] = [];
        $src = is_array($matrix[$role] ?? null) ? $matrix[$role] : ($defaults[$role] ?? []);
        foreach ($keys as $k) {
            if ($role === 'owner') {
                $clean[$role][$k] = true;
            } else {
                $clean[$role][$k] = !empty($src[$k]);
            }
        }
        if ($role === 'gm') {
            $clean[$role]['admin.team.permissions_manage'] = true;
        }
    }

    $settings = pbj_permissions_load_settings($pdo, $rid);
    $settings['permissions'] = [
        'version' => 1,
        'matrix' => $clean,
        'updatedAt' => time() * 1000,
    ];

    // Keep floorAllowedRoles in sync for legacy floor API
    $floorRoles = [];
    foreach ($roles as $role) {
        if (!empty($clean[$role]['foh.floor.edit_layout'])) {
            $floorRoles[] = $role === 'gm' ? 'admin' : $role;
        }
    }
    if (!in_array('owner', $floorRoles, true)) {
        $floorRoles[] = 'owner';
    }
    $settings['floorAllowedRoles'] = array_values(array_unique($floorRoles));

    pbj_permissions_save_settings($pdo, $rid, $settings);
    return $clean;
}
