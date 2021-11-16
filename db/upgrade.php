<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * html5player module upgrade code
 *
 * This file keeps track of upgrades to
 * the resource module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package mod_html5player
 * @copyright  2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Webgl Module Upgrade function
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_html5player_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->libdir.'/db/upgradelib.php'); // Core Upgrade-related functions.
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.10.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.11.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2021111500) {
        // Add new fields to html5player: intro, introformat, timecreated,
        $table = new xmldb_table('html5player');
        $field1 = new xmldb_field('intro', XMLDB_TYPE_TEXT, '4', null, false, false);
        $field2 = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '10', true, true, false, 0);
        $field3 = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', true, true, false, 0);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }

        // html5player savepoint reached.
        upgrade_mod_savepoint(true, 2021111500, 'html5player');
    }
    if ($oldversion < 2021111504) {
        // Add new fields to html5player: account_id, video_id, width, height.
        $table = new xmldb_table('html5player');
        $field1 = new xmldb_field('account_id', XMLDB_TYPE_CHAR, '50', null, false, false, null);
        $field2 = new xmldb_field('video_id', XMLDB_TYPE_CHAR, '50', true, true, false, null);
        $field3 = new xmldb_field('width', XMLDB_TYPE_CHAR, '50', true, true, false, null);
        $field4 = new xmldb_field('height', XMLDB_TYPE_CHAR, '50', true, true, false, null);

        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }

        // html5player savepoint reached.
        upgrade_mod_savepoint(true, 2021111504, 'html5player');
    }

    return true;
}
