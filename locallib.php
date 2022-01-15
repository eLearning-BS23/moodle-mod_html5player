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

use core\activity_dates;
use core_completion\cm_completion_details;
use core_completion\progress;
use core_favourites\service_factory;
use mod_html5player\BasicAPI;

defined('MOODLE_INTERNAL') || die();


require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/html5player/lib.php");


/**
 * File browsing support class
 */
class html5player_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}

function html5player_get_editor_options($context) {
    global $CFG;
    return array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'changeformat'=>1, 'context'=>$context, 'noclean'=>1, 'trusttext'=>0);
}

/**
 * Display embedded resource file.
 * @param object $html5player
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function html5player_display_embed_video($html5player, $cm, $course) {
    global $PAGE, $OUTPUT, $USER;
    html5player_print_header($html5player, $cm, $course);
    html5player_print_heading($html5player, $cm, $course);
    // Display any activity information (eg completion requirements / dates).
    $cminfo = cm_info::create($cm);
    $completiondetails = cm_completion_details::get_instance($cminfo, $USER->id);
    $activitydates = activity_dates::get_dates_for_module($cminfo, $USER->id);
    $completioninfo = $OUTPUT->activity_information($cminfo, $completiondetails, $activitydates);
    echo $OUTPUT->activity_information($cminfo, $completiondetails, $activitydates);
    html5player_render_embed_html($html5player, $cm, $course,$completioninfo);

    //echo html_writer::tag('h1', $html5player->name, ['class' => 'heading-1']);
    echo $OUTPUT->footer();

    die;
}


function  html5player_render_embed_html($html5player, $cm, $course, $completioninfo) {
    global $OUTPUT, $COURSE, $PAGE, $USER;
    //echo $OUTPUT->activity_navigation();
    $is_favourite = html5player_is_favourite();
    $progressPercentage = progress::get_course_progress_percentage($COURSE);
    $progressPercentage = floor($progressPercentage);

    $modules = get_fast_modinfo($course->id)->get_cms();

    $certificate_module = null;

    foreach ($modules as $cms):
        if ($cms->modname == 'customcert'){
            $certificate_module = $cms;
        }
    endforeach;

    $modules = array_filter($modules,function ($cms){
        return $cms->modname != 'customcert';
    });

    $last_module = end($modules);

    if ($PAGE->cm->id == $last_module->id && !empty($certificate_module) ){
        $downloadable = true;
    } else {
        $downloadable = false;
    }
    if($progressPercentage == 100) {
        $iscomplete = true;
    } else {
        $iscomplete = false;
    }
    $downloadcertificateurl = $certificate_module ?  $certificate_module->url.'&downloadown=1' : null;

    // Data to be passed in the template.
    $data = [
        'completioninfo' => $completioninfo,
        'progresspercentage' => $progressPercentage,
        'courseid' => $course->id,
        'userid' => $USER->id,
        'favourite' => $is_favourite,
        'downloadable' => $downloadable,
        'iscomplete' => $iscomplete,
        'downloadcertificateurl' => $downloadcertificateurl,
    ];

    ?>
    <br>
    <div class="row">
        <div class="col">
            <?php  html5player_generate_code($html5player,$cm,$course); ?>
        </div>
    </div>
<!--    --><?php // echo $OUTPUT->render_from_template('theme_allergan_blank/core_course/completion_percentage', $data); ?>

<!--    <div class="row mt-5">-->
<!--        <div class="col-md-8 ">-->
<!--            --><?php
//            html5player_print_intro($html5player, $cm, $course,true);
//            ?>
<!--        </div>-->
<!---->
<!--        <div class="col-md-4">-->
<!--            --><?php
//            $html5playermeta_info  = trim($html5player->meta_info);
//            if (!empty($html5playermeta_info)) :
//                $meta_infos = explode("\n", trim($html5player->meta_info));
//                echo html_writer::start_tag('ul',array('class' => 'mod-custommod-right-content mt-3'));
//                foreach ($meta_infos as $meta_info):
//                    $infos = explode(":",$meta_info);
//                    if (isset($infos[0] ) && isset($infos[1])){
//                        echo "<p class='mod-custommod-task'>$infos[0]:<span class='mod-custommod-subject'>$infos[1]</span></p>";
//                    }
//                endforeach;
//                echo html_writer::end_tag('ul');
//            endif;
//            ?>
<!--        </div>-->
<!--    </div>-->
    <?php

}

/**
 * Print resource introduction.
 * @param object $html5player
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function html5player_print_intro($html5player, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($html5player->displayoptions) ? array() : unserialize($html5player->displayoptions);

    $extraintro = '';

    if ($ignoresettings || !empty($options['printintro']) || $extraintro) {
        $gotintro = trim(strip_tags($html5player->intro));
        if ($gotintro || $extraintro) {
            echo $OUTPUT->box_start('mr-md-5 pr-2', 'resourceintro');
            if ($gotintro) {
                echo format_module_intro('resource', $html5player, $cm->id);
            }
            echo $extraintro;
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Print resource header.
 * @param object $html5player
 * @param object $cm
 * @param object $course
 * @return void
 */
function html5player_print_header($html5player, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$html5player->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($html5player);
    echo $OUTPUT->header();
}

/**
 * Print resource heading.
 * @param object $html5player
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used
 * @return void
 */
function html5player_print_heading($html5player, $cm, $course, $notused = false) {
    global $OUTPUT;

//    echo html_writer::start_div('row mb-5');
//    echo html_writer::start_div('col');
//    echo html_writer::tag('p',get_string('video','mod_html5player'),array('class' => 'sub-heading'));
//    echo $OUTPUT->heading(format_string($html5player->name), 2, 'text-primary');
//    echo html_writer::end_div();
//    echo html_writer::end_div();

    echo $OUTPUT->heading(format_string($html5player->name), 2);

}


/**
 * @return bool
 */
function html5player_is_favourite() {
    global $USER, $COURSE;
    $usercontext = context_user::instance($USER->id);
    $ufservice = service_factory::get_service_for_user_context($usercontext);
    return $ufservice->favourite_exists('core_course', 'courses', $COURSE->id,
        \context_course::instance($COURSE->id));

}

/**
 * @return array
 * @throws coding_exception
 */
function html5player_get_unit($key) {

    $units = [
        1 => get_string('pixel','mod_html5player'),
        2 => get_string('em','mod_html5player'),
        3 => get_string('percentage','mod_html5player'),
    ];

    if ($key){
        return $units[$key];
    }
    return $units;
}

/**
 * @param $html5player
 * @param $cm
 * @param $course
 * @return void|null
 * @throws coding_exception
 * @throws dml_exception
 */
function html5player_generate_code($html5player, $cm, $course=null) {
    global $OUTPUT, $PAGE, $USER;
//    echo html_writer::tag('h1', $html5player->name, ['class' => 'mb-5']);

    if ($html5player->video_type == 2) {
        $html5player->playlist_id = $html5player->video_id;
    }
    $html5player->unitstxt = html5player_get_unit($html5player->units);
    $html5player->cmid = $cm->id;
    $context = context_course::instance($html5player->course);
    $module_context = context_module::instance($cm->id);

    $html5player->is_student = is_enrolled($context, $USER->id, '', true) &&
        !has_capability('mod/html5player:addinstance', $module_context);
    $interval = get_config('html5player','trackinginterval');
    $forwardscrubbing = get_config('html5player','forwardscrubbing');
    $html5player->progress_interval = $interval ? $interval * 1000 : 5000;
    $html5player->forwardscrubbing = $forwardscrubbing;

    $html5player->cmcompleted = html5player_is_module_completed($course, $cm, $USER->id);
    echo $OUTPUT->render_from_template('mod_html5player/brightcove/video-renderer',$html5player);
    $PAGE->requires->js_call_amd('mod_html5player/brightcove', 'init',[json_encode($html5player)]);
}

/**
 * @param $course
 * @param $cm
 * @param $userid
 * @return bool|int
 */
function html5player_is_module_completed($course,$cm, $userid) {
    $completion = new \completion_info($course);

    // First, let's make sure completion is enabled.
    if ($completion->is_enabled() && $completion->is_tracked_user($userid)) {
        if ($completion->is_course_complete($userid)) {
            return  true;
        }
        $data = $completion->get_data($cm, true, $userid);
        return !($data->completionstate == COMPLETION_INCOMPLETE);
    }

    return false;
}

/**
 * @param $course
 * @param $cm
 */
function html5player_set_module_viewed($course, $cm){
    global $USER;
    $context = context_course::instance($course->id);
    $is_enrolled =  is_enrolled($context, $USER->id, '', true);

    if ($is_enrolled){
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);
    }
}

/**
 * @param int $id
 * @return false|mixed|stdClass
 * @throws coding_exception
 * @throws dml_exception
 */
function html5player_get_html5player_from_cm(int $id) {
    global $DB;
    $cm = get_coursemodule_from_id('html5player', $id, 0, false, MUST_EXIST);
    return $DB->get_record('html5player', array('id' => $cm->instance), '*', MUST_EXIST);
}

/**
 * @param int $id
 * @return array
 * @throws coding_exception
 * @throws dml_exception
 */
function html5player_get_cm_course_from_cm(int $id) {
    global $DB;
    $cm = get_coursemodule_from_id('html5player', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    return array($cm, $course);
}

/**
 * @param stdClass $html5player
 * @param stdClass $video
 * @param array $params
 * @return bool|int
 * @throws dml_exception
 */
function html5player_add_tracking_record(stdClass $html5player,stdClass $video, array $params){
    global $DB;
    $tracking =new stdClass();
    $tracking->html5player = $html5player->id;
    $tracking->html5videoid = $video->id;
    $tracking->user = $params['userid'];
    $tracking->progress = $params['progress'];
    $tracking->timecreated = time();
    $tracking->timemodified = time();

    return $DB->insert_record(HTML5PLYAER_VIDEO_TRACKING_TABLE_NAME, $tracking);
}

/**
 * @param stdClass $tracking
 * @param array $params
 * @throws dml_exception
 */
function html5player_update_tracking_record(stdClass $tracking, array $params){
    global $DB;
    $tracking->progress = $params['progress'];
    $tracking->timemodified = time();
    $DB->update_record(HTML5PLYAER_VIDEO_TRACKING_TABLE_NAME, $tracking);
}

/**
 * @param int $courseid
 * @param int $moduleid
 * @param int $userid
 * @return false|mixed|stdClass
 * @throws dml_exception
 */
function html5player_get_module_progress(int $courseid, int $moduleid, int $userid) {
    global $DB;

    $conditions = array(
        'course' => $courseid,
        'cm' => $moduleid,
        'user' => $userid,
    );

    return $DB->get_record(HTML5PLYAER_VIDEO_TABLE_NAME,$conditions);
}


/**
 * @param object $data
 * @throws dml_exception
 * @throws moodle_exception
 */
function html5player_add_videos(object $data) {
    $videos = html5player_get_playlist_videos_description($data->account_id,$data->video_id);
    foreach ($videos as $details):
        html5player_add_video($data->id, $details);
    endforeach;

}


/**
 * @throws dml_exception
 */
function html5player_add_video(int $html5playerid, stdClass $video) {
    global $DB;

    $html5video = new stdClass();
    $html5video->html5player = $html5playerid;
    $html5video->video_id = $video->id;
    $html5video->duration = $video->duration;
    $html5video->poster = $video->images->poster ? $video->images->poster->src : null;
    $html5video->thumbnail = $video->images->thumbnail ? $video->images->thumbnail->src: null;
    $html5video->timecreated = time();
    $html5video->timemodified = time();

    return $DB->insert_record(HTML5PLYAER_VIDEO_TABLE_NAME,$html5video);
}


/**
 * @param stdClass $data
 * @throws dml_exception
 * @throws moodle_exception
 */
function html5player_update_videos(stdClass $data) {
    global $DB;

    $playlistsvideodetails = html5player_get_playlist_videos_description($data->account_id,$data->video_id);

    $existing_videos = $DB->get_records(HTML5PLYAER_VIDEO_TABLE_NAME,array('html5player' => $data->instance));

    $video_lists = array();
    foreach ($playlistsvideodetails as $videodetails):
        $video_lists[] = $videodetails->id;
        $existing_video = array_filter($existing_videos, function ($video) use ($videodetails){
            return $video->video_id == $videodetails->id;
        });

        $reversed_video = array_reverse($existing_video);
        $video = array_pop($reversed_video);
        if (!empty($video)){
            html5player_update_video($video, $videodetails);
        }else{
            html5player_add_video($data->instance,$videodetails);
        }

    endforeach;

    remove_unused_video_record_from_player($DB, $video_lists, $data->instance);

}

/**
 * @param moodle_database $DB
 * @param array $video_lists
 * @param int $instance
 * @throws coding_exception
 * @throws dml_exception
 */
function remove_unused_video_record_from_player(moodle_database $DB, array $video_lists, int $instance): void
{
    if (count($video_lists)) {
        list($notinsql, $params) = $DB->get_in_or_equal($video_lists, SQL_PARAMS_NAMED, 'param', false);
        $params['html5player'] = $instance;
        $html5videoids = $DB->get_fieldset_select(HTML5PLYAER_VIDEO_TABLE_NAME,'id',"html5player = :html5player AND video_id $notinsql", $params);

        $DB->delete_records_select(HTML5PLYAER_VIDEO_TABLE_NAME, "html5player = :html5player AND video_id $notinsql", $params);

        // Remove tracking record as video deleted
        if (!empty($html5videoids)){
            list($insql, $videoparams) = $DB->get_in_or_equal($html5videoids, SQL_PARAMS_NAMED);
            $videoparams['html5player'] = $instance;
            $DB->delete_records_select(HTML5PLYAER_VIDEO_TRACKING_TABLE_NAME, "html5player = :html5player AND html5videoid $insql", $videoparams);
        }
    }

}

/**
 * @param moodle_database $DB
 * @param stdClass $data
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function html5player_onupdate_process_signle_video(moodle_database $DB, stdClass $data){
    $video_details = html5player_get_video_description($data->account_id,$data->video_id);
    remove_unused_video_record_from_player($DB,[$data->video_id],$data->instance);
    $html5player = $DB->get_record(HTML5PLYAER_VIDEO_TABLE_NAME,array('html5player' => $data->instance));
    if ($html5player){
        html5player_update_video($html5player, $video_details);
    }else{
        html5player_add_video($data->instance, $video_details);
    }
}

/**
 * @param stdClass $video
 * @param stdClass $video_details
 * @return bool
 * @throws dml_exception
 */
function html5player_update_video(stdClass $video, stdClass $video_details) {
    global $DB;

    $video->video_id = $video_details->id;
    $video->duration = $video_details->duration;
    $video->poster = $video_details->images->poster ? $video_details->images->poster->src : null;
    $video->thumbnail = $video_details->images->thumbnail ? $video_details->images->thumbnail->src: null;
    $video->timemodified = time();

    return $DB->update_record(HTML5PLYAER_VIDEO_TABLE_NAME,$video);
}


/**
 * @param int $course
 * @param int $module
 * @param int $userid
 * @param float $progress
 * @return bool
 * @throws dml_exception
 */
function html5player_set_module_progress(int $course, int $module, int $userid, float $progress) {
    global $DB;

    $conditions = array(
        'course' => $course,
        'cm' => $module,
        'user' => $userid,
    );

    $existing_record = $DB->get_record(HTML5PLYAER_VIDEO_TABLE_NAME,$conditions);

    if ($existing_record){
        $existing_record->progress = $progress;
        $existing_record->timemodified = time();
        return $DB->update_record(HTML5PLYAER_VIDEO_TABLE_NAME, $existing_record);
    }

    $progressions = new stdClass();
    $progressions->course = $course;
    $progressions->cm = $module;
    $progressions->user = $userid;
    $progressions->progress = $progress;
    $progressions->timecreated = time();
    $progressions->timemodified = time();

    return $DB->insert_record(HTML5PLYAER_VIDEO_TABLE_NAME,$progressions,false);
}


/**
 * @throws dml_exception|moodle_exception
 */
function html5player_get_token(){
    /**
     * access-token-proxy.php - proxy for Brightcove RESTful APIs
     * gets an access token and returns it
     * Accessing:
     *         (note you should *always* access the proxy via HTTPS)
     *     Method: POST
     *
     * @post {string} client_id - OAuth2 client id with sufficient permissions for the request
     * @post {string} client_secret - OAuth2 client secret with sufficient permissions for the request
     *
     * @returns {string} $response - JSON response received from the OAuth API
     */
    $client_id = get_config('html5player','clientid');
    $client_secret= get_config('html5player','clientsecrete');
    $auth_string = base64_encode($client_id.':'.$client_secret);
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://oauth.brightcove.com/v4/access_token?grant_type=client_credentials',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.$auth_string
        ),
    ));

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($response === FALSE) {
        throw new moodle_exception('generalexceptionmessage','error','',curl_error($ch));
    } else {
        if ($httpcode === 200){
            return json_decode($response);
        }

        if ($httpcode === 400){
            throw new moodle_exception('generalexceptionmessage','error','',
                'Invalid credentials!');
        }
        throw new moodle_exception('generalexceptionmessage','error','',
            'something went wrong in authorization token request');
    }

}

/**
 * @param string $account_id
 * @param string $video_id
 * @return mixed
 * @throws dml_exception
 * @throws moodle_exception
 */
function html5player_get_video_description(string $account_id, string $video_id){
    $token = html5player_get_token();
    $request = "https://cms.api.brightcove.com/v1/accounts/$account_id/videos/$video_id";
    $ch            = curl_init($request);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_TIMEOUT=> 60,
        CURLOPT_HTTPHEADER     => array(
            "Content-type: application/json",
            "Authorization: {$token->token_type} {$token->access_token}",
        )
    ));
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($response === FALSE) {
        throw new moodle_exception('generalexceptionmessage','error','',curl_error($ch));
    } else {
        if ($httpcode === 200){
            return json_decode($response);
        }
        throw new moodle_exception('generalexceptionmessage','error','',
            'something went wrong in video duration response');
    }
}


/**
 * @param string $account_id
 * @param string $video_id
 * @return mixed
 * @throws dml_exception
 * @throws moodle_exception
 */
function html5player_get_playlist_videos_description(string $account_id, string $playlist_id){
    $token = html5player_get_token();
    $request = "https://cms.api.brightcove.com/v1/accounts/$account_id/playlists/$playlist_id/videos";
    $ch            = curl_init($request);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_TIMEOUT=> 60,
        CURLOPT_HTTPHEADER     => array(
            "Content-type: application/json",
            "Authorization: {$token->token_type} {$token->access_token}",
        )
    ));
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($response === FALSE) {
        throw new moodle_exception('generalexceptionmessage','error','',curl_error($ch));
    } else {
        if ($httpcode === 200){
            $details = json_decode($response);
            return array_filter($details,function ($item) {
                return $item->state == 'ACTIVE';
            });
        }
        throw new moodle_exception('generalexceptionmessage','error','',
            'something went wrong in playlists videos response');
    }
}

/**
 * check if the course video view completed
 * @param int $html5player
 * @param int $user
 * @return false|mixed
 * @throws dml_exception
 */
function html5player_is_video_view_completed(int $html5player){
    global $DB, $USER;
    $sql = "select p.course, p.name,
    IF( (select SUM(v.duration) from {html5player_html5videos} v where v.html5player = p.id group by v.html5player)<=
    (select SUM(t.progress) from {html5player_html5trackings} t where t.html5player = p.id AND t.user = :user group by t.html5player)
    ,true, false) completed from {html5player} p where id= :id limit 1 offset 0";
    return $DB->get_record_sql($sql, array('id' => $html5player, 'user' => $USER->id));
}