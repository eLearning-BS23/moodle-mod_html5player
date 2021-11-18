<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for html5player.
 *
 * @package    mod_html5player
 * @copyright  2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

const HTML5_TABLE_NAME = 'html5player';
/**
 * Returns the information on whether the module supports a feature
 *
 * See {@see plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function html5player_supports(string $feature): ?bool {

    switch ($feature) {
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_MOD_INTRO:
        case FEATURE_GRADE_HAS_GRADE:
        return true;
        default:
            return null;
    }
}

/**
* Add html5player instance.
 * @param object $data
* @param object $mform
* @return int new url instance id
*/
function html5player_add_instance($data, $mform) {
    global $CFG, $DB;

    $data->timecreated = time();
    $data->timemodified = time();
    $data->id = $DB->insert_record(HTML5_TABLE_NAME, $data);

    return $data->id;
}

/**
 * Update html5player instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function html5player_update_instance($data, $mform) {
    global $CFG, $DB;
    $data->timemodified = time();
    $data->id           = $data->instance;
    $DB->update_record('custommod', $data);
    return true;
}

/**
 * Delete html5player instance.
 * @param int $id
 * @return bool true
 */
function html5player_delete_instance($id) {
    global $DB;

    if (!$html5player = $DB->get_record(HTML5_TABLE_NAME, array('id'=>$id))) {
        return false;
    }

    $DB->delete_records(HTML5_TABLE_NAME, array('id'=>$html5player->id));

    return true;
}
