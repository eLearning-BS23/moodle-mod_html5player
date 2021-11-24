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

    html5player_render_embed_html($html5player, $cm, $course,$completioninfo);
    echo $OUTPUT->footer();

    die;
}


function  html5player_render_embed_html($html5player, $cm, $course, $completioninfo) {
    global $OUTPUT, $COURSE, $PAGE, $USER;
    echo $OUTPUT->activity_navigation();
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
            <?php  html5player_generate_code($html5player); ?>
        </div>
    </div>
    <?php  echo $OUTPUT->render_from_template('theme_allergan_blank/core_course/completion_percentage', $data); ?>

    <div class="row mt-5">
        <div class="col-md-8 ">
            <?php
            html5player_print_intro($html5player, $cm, $course,true);
            ?>
        </div>

        <div class="col-md-4">
            <?php
            $html5playermeta_info  = trim($html5player->meta_info);
            if (!empty($html5playermeta_info)) :
                $meta_infos = explode("\n", trim($html5player->meta_info));
                echo html_writer::start_tag('ul',array('class' => 'mod-custommod-right-content mt-3'));
                foreach ($meta_infos as $meta_info):
                    $infos = explode(":",$meta_info);
                    if (isset($infos[0] ) && isset($infos[1])){
                        echo "<p class='mod-custommod-task'>$infos[0]:<span class='mod-custommod-subject'>$infos[1]</span></p>";
                    }
                endforeach;
                echo html_writer::end_tag('ul');
            endif;
            ?>
        </div>
    </div>
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

    echo html_writer::start_div('row mb-5');
    echo html_writer::start_div('col');
    echo html_writer::tag('p',get_string('video','mod_html5player'),array('class' => 'sub-heading'));
    echo $OUTPUT->heading(format_string($html5player->name), 2, 'text-primary');
    echo html_writer::end_div();
    echo html_writer::end_div();

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
 * @throws coding_exception
 */
function html5player_generate_code($html5player) {
    global $PAGE, $OUTPUT;
    echo html_writer::tag('h1', $html5player->name, ['class' => 'mb-5']);

    if ($html5player->video_type == 2) {
        $html5player->playlist_id = $html5player->video_id;
    }
    $html5player->unitstxt = html5player_get_unit($html5player->units);
    echo $OUTPUT->render_from_template('mod_html5player/brightcove/video-renderer',$html5player);
}

/**
 * @param $course
 * @param $cm
 * @throws dml_exception
 */
function html5player_set_module_viewed($html5player, $course, $cm){
    global $USER;
    $context = context_course::instance($course->id);
    $is_enrolled =  is_enrolled($context, $USER->id, '', true);

    if ($is_enrolled && $html5player->completed){
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);
    }
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

    return $DB->get_record(HTML5_TRACKING_TABLE_NAME,$conditions);
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

    $existing_record = $DB->get_record(HTML5_TRACKING_TABLE_NAME,$conditions);

    if ($existing_record){
        $existing_record->progress = $progress;
        $existing_record->timemodified = time();
        return $DB->update_record(HTML5_TRACKING_TABLE_NAME, $existing_record);
    }

    $progressions = new stdClass();
    $progressions->course = $course;
    $progressions->cm = $module;
    $progressions->user = $userid;
    $progressions->progress = $progress;
    $progressions->timecreated = time();
    $progressions->timemodified = time();

    return $DB->insert_record(HTML5_TRACKING_TABLE_NAME,$progressions,false);
}