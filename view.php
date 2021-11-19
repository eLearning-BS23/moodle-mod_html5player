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
 * html5player main user interface/view.
 *
 * @package    mod_html5player
 * @copyright  2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_course\output\activity_navigation;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
$n = optional_param('n', 0, PARAM_INT); // HTML5Player instance ID - it should be named as the first character of the module.

if ($id) {
    $cm = get_coursemodule_from_id('html5player', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $html5player = $DB->get_record('html5player', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $html5player = $DB->get_record('html5player', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $html5player->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('html5player', $html5player->id, $course->id, false, MUST_EXIST);
} else {
    throw new Exception('You must specify a course_module ID or an instance ID');
}

require_course_login($course->id, false, $cm);

$event = \mod_html5player\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $html5player);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/html5player/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($html5player->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_cacheable(false);
$context = context_course::instance($course->id);

$units = [
    1 => get_string('pixel','mod_html5player'),
    2 => get_string('em','mod_html5player'),
    3 => get_string('percentage','mod_html5player'),
];

html5player_display_embed_video($html5player,$cm, $course);

