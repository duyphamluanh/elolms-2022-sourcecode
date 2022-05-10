<?php
/**
 * Change length lastaccess
 *
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 */
function elo_reminder_users_upgrade_2019091722() {
    
    global $DB;
    $dbman = $DB->get_manager();

    // Define field flag to be change/add to block_elo_reminder_users.
    $table = new xmldb_table('block_elo_reminder_users');
    $field = new xmldb_field('lastaccess');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

    // Conditionally launch add field flag.
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field);
    }
}

/**
 * Elo_reminder_users module upgrade function.
 *
 * @param string $oldversion The version we are upgrading from
 *
 * @return bool Success
 */
function xmldb_block_elo_reminder_users_upgrade($oldversion) {

    $upgrades = [
        2019091722
    ];

    foreach ($upgrades as $version) {
        if ($oldversion < $version) {
            call_user_func("elo_reminder_users_upgrade_{$version}");
    		// Database savepoint reached.
            upgrade_block_savepoint(true, $version, 'elo_reminder_users');
        }
    }

    return true;
}