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

    /**
     * @return external_function_parameters
     */
    public static function html5player_set_progress_parameters()
    {
        return new external_function_parameters (
            array(
                'id' => new external_value(PARAM_INT, 'course module id', VALUE_REQUIRED,0,false),
                'videoid' => new external_value(PARAM_INT, 'video id /playlist video id', VALUE_REQUIRED,0,false),
                'progress' => new external_value(PARAM_FLOAT, 'progress percentage', VALUE_REQUIRED,0,false),
            )
        );
    }

    /**
     * @param int $id
     * @param int $videoid
     * @param float $progress
     * @param int|null $userid
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function html5player_set_progress(int $id, int $videoid, float $progress, int $userid=null)
    {
        global $USER, $DB;

        $warnings = array();

        $params = array(
            'id' => $id,
            'videoid' => $videoid,
            'progress' => $progress ? $progress * 1000 : 0,
        );
        $params = self::validate_parameters(self::html5player_set_progress_parameters(), $params);

        $params['userid'] = $userid ?? $USER->id;

        try {
            list($html5player, $video, $tracking) = self::get_tracking_realted_info($id, $videoid, $params);

            if (empty($tracking)){
                html5player_add_tracking_record($html5player,$video, $params);
            }else{
                html5player_update_tracking_record($tracking, $params);
            }
            return array(
                'completed' => false,
                'status' => true,
                'warnings' => $warnings
            );
        }catch (Exception $exception){
            return array(
                'completed' => false,
                'status' => false,
                'warnings' => $warnings
            );
        }

    }

    /**
     * @return external_single_structure
     */
    public static function html5player_set_progress_returns()
    {
        return new external_single_structure(
            array(
                'completed' => new external_value(PARAM_BOOL,'Database record updated or not',VALUE_REQUIRED,false,false),
                'status' => new external_value(PARAM_BOOL,'Database record updated or not',VALUE_REQUIRED,false,false),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function html5player_get_progress_parameters()
    {
        return new external_function_parameters (
            array(
                'id' => new external_value(PARAM_INT, 'course module id', VALUE_REQUIRED,0,false),
                'videoid' => new external_value(PARAM_INT, 'video id /playlist video id', VALUE_REQUIRED,0,false)
            )
        );
    }

    /**
     * @param int $id
     * @param int $videoid
     * @param int|null $userid
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function html5player_get_progress(int $id, int $videoid, int $userid=null)
    {
        global $USER, $DB;

        $warnings = array();

        $params = array(
            'id' => $id,
            'videoid' => $videoid,
        );

        $params = self::validate_parameters(self::html5player_get_progress_parameters(), $params);

        $params['userid'] = $userid ?? $USER->id;

        try {

            list($html5player, $video, $tracking) = self::get_tracking_realted_info($id, $videoid, $params);

            return array(
                'status' => true,
                'progress' => $tracking->progress,
                'warnings' => $warnings
            );
        }catch (Exception $exception){
            return array(
                'status' => false,
                'progress' => 0,
                'warnings' => $warnings
            );
        }

    }

    /**
     * @return external_single_structure
     */
    public static function html5player_get_progress_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL,'Database record updated or not',VALUE_REQUIRED,false,false),
                'progress' => new external_value(PARAM_FLOAT,'video progress in milliseconds',VALUE_REQUIRED,0,true),
                'warnings' => new external_warnings(),
            )
        );
    }

    public static function get_tracking_realted_info(int $id, int $videoid, array $params)
    {
        global $DB;
        $html5player = html5player_get_html5player_from_cm($id);
        $video = $DB->get_record(HTML5PLYAER_VIDEO_TABLE_NAME, array('html5player' => $html5player->id,
            'video_id' => $videoid,), '*',MUST_EXIST);

        $tracking = $DB->get_record(HTML5PLYAER_VIDEO_TRACKING_TABLE_NAME,array('html5player' => $html5player->id,
            'html5videoid' => $video->id,
            'user' => $params['userid'],
        ),'*',IGNORE_MISSING);

        return [
            $html5player,
            $video,
             $tracking
        ];
    }

}