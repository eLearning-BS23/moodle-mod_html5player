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
                'ended' => new external_value(PARAM_BOOL, 'progress percentage', VALUE_REQUIRED, false,false),
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
    public static function html5player_set_progress(int $id, int $videoid, float $progress, bool $ended=false)
    {
        global $USER, $DB;

        $warnings = array();

        $params = array(
            'id' => $id,
            'videoid' => $videoid,
            'ended' => $ended,
            'progress' => $progress ? $progress * 1000 : 0,
        );
        $params = self::validate_parameters(self::html5player_set_progress_parameters(), $params);

        $params['userid'] =$USER->id;

        $transaction = $DB->start_delegated_transaction();

        try {
            list($html5player, $video, $tracking) = self::get_tracking_realted_info($id, $videoid, $params);
            if (!empty($params['ended'])){
                $params['progress'] = $video->duration;
            }

            if (empty($tracking)){
                html5player_add_tracking_record($html5player,$video, $params);
            }else{
                html5player_update_tracking_record($tracking, $params);
            }
            $viewcompletiondata = html5player_is_video_view_completed($html5player->id);

            if ($viewcompletiondata->completed){
                list($cm, $course) = html5player_get_cm_course_from_cm($id);
                $context = context_module::instance($cm->id);
                html5player_view($html5player, $course, $cm, $context);
            }

            $DB->commit_delegated_transaction($transaction);

            return array(
                'completed' => boolval($viewcompletiondata->completed),
                'status' => true,
                'warnings' => $warnings
            );
        }catch (Exception $exception){
            $DB->rollback_delegated_transaction($transaction, $exception);
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
                'videoid' => new external_value(PARAM_INT, 'Course module id', VALUE_REQUIRED,0,false)
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

    /**
     * @return external_function_parameters
     */
    public static function html5player_get_progresses_parameters()
    {
        return new external_function_parameters (
            array(
                'id' => new external_value(PARAM_INT, 'course module id', VALUE_REQUIRED,0,false)
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
    public static function html5player_get_progresses(int $id)
    {
        global $USER, $DB;

        $warnings = array();

        $params = array(
            'id' => $id,
        );
        $params = self::validate_parameters(self::html5player_get_progresses_parameters(), $params);

        $params['userid'] = $USER->id;
        try {
            $html5player = html5player_get_html5player_from_cm($id);
            $params['html5player'] = $html5player->id;
            $sql = "SELECT v.video_id, v.duration , IF(v.duration <= t.progress, true, false) as completed,  t.* 
                    FROM {html5videos} v LEFT  JOIN {html5tracking} t ON v.id = t.html5videoid
                    WHERE v.html5player = :html5player AND t.user = :userid ORDER by completed ASC, t.html5videoid ASC";

            $progresses = $DB->get_records_sql($sql, $params);

            return array(
                'status' => true,
                'progresses' => $progresses,
                'warnings' => $warnings
            );
        }catch (Exception $exception){
            return array(
                'status' => false,
                'progresses' => array(),
                'warnings' => $warnings
            );
        }

    }

    /**
     * @return external_single_structure
     */
    public static function html5player_get_progresses_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL,'Database record updated or not',VALUE_DEFAULT,false,false),
                'progresses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'id' => new external_value(PARAM_INT,'Video tracking id'),
                        'video_id' => new external_value(PARAM_INT,'Brightcove video id'),
                        'html5player' => new external_value(PARAM_INT,'Course module id'),
                        'html5videoid' => new external_value(PARAM_INT,'html5video table id'),
                        'user' => new external_value(PARAM_INT,'user id'),
                        'completed' => new external_value(PARAM_BOOL,'video completed'),
                        'duration' => new external_value(PARAM_INT,'video duration in ms'),
                        'progress' => new external_value(PARAM_INT,'video progress in ms'),
                        'timecreated' => new external_value(PARAM_INT,'record create time'),
                        'timemodified' => new external_value(PARAM_INT,'record modified time'),
                        )
                    )
                ),
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
        ));



        return array($html5player, $video, $tracking);
    }

}