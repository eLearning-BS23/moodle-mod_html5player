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
 * External API library for Html5player
 *
 * @package    mod_html5player
 * @copyright  2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/html5player/locallib.php');

use mod_lesson\external\lesson_summary_exporter;
class mod_html5player_external extends external_api
{

    public static function html5player_set_progress_parameters()
    {
        return new external_function_parameters (
            array(
                'id' => new external_value(PARAM_INT, 'course module id', VALUE_REQUIRED,0,false),
                'videoid' => new external_value(PARAM_INT, 'video id /playlist video id', VALUE_REQUIRED,0,false),
                'progress' => new external_value(PARAM_FLOAT, 'progress percentage', VALUE_REQUIRED,0,false),
                'userid' => new external_value(PARAM_INT, 'html5player id', VALUE_OPTIONAL,0,false),
            )
        );
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function html5player_set_progress(int $id, int $videoid, float $progress, int $userid=null)
    {
        global $USER, $DB;

        $warnings = array();

        $params = array(
            'id' => $id,
            'videoid' => $videoid,
            'userid' => $userid ?? $USER->id,
            'progress' => $progress,
        );
        $params = self::validate_parameters(self::html5player_set_progress_parameters(), $params);

        $cm = get_coursemodule_from_id('html5player', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $html5player = $DB->get_record('html5player', array('id' => $cm->instance), '*', MUST_EXIST);
    }

    public static function html5player_set_progress_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL,'Database record updated or not',VALUE_REQUIRED,false,false),
                'warnings' => new external_warnings(),
            )
        );
    }

}