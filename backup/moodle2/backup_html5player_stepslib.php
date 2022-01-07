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
 * @package   mod_html5player
 * @category  backup
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_page_activity_task
 */

/**
 * Define the complete html5player structure for backup, with file and id annotations
 */
class backup_html5player_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $html5player = new backup_nested_element('html5player', array('id'), array(
            'name', 'display', 'displayoptions', 'tobemigrated', 'intro', 'introformat','meta_info', 'account_id', 'player_id',
            'video_type', 'video_id','playlist_id','sizing','aspect_ratio','units','width','height'
            ));

        $html5videos = new backup_nested_element('html5videos');

        $html5video = new backup_nested_element('html5video', array('id'), array(
            'html5player', 'video_id', 'poster', 'thumbnail', 'duration', 'timecreated', 'timemodified'));

        $html5trackings = new backup_nested_element('html5trackings');

        $html5tracking = new backup_nested_element('html5tracking', array('id'), array(
            'html5player', 'html5video', 'user', 'progress', 'timecreated', 'timemodified'));


        // Build the tree.

        $html5player->add_child($html5videos);
        $html5videos->add_child($html5video);

        $html5player->add_child($html5trackings);
        $html5trackings->add_child($html5tracking);


        // Define sources
        $html5player->set_source_table('html5player', array('id' => backup::VAR_ACTIVITYID));

        $html5video->set_source_table('html5player_html5videos', array('html5player' => backup::VAR_PARENTID));
        if($userinfo) {
            $html5tracking->set_source_table('html5player_html5trackings', array('html5player' => '../../id'));
        }
        // Define id annotations
        // (none)

        // Define file annotations
        $html5player->annotate_files('mod_html5player', 'intro', null); // This file areas haven't itemid
        $html5player->annotate_files('mod_html5player', 'content', null); // This file areas haven't itemid

        // Return the root element (html5player), wrapped into standard activity structure
        return $this->prepare_activity_structure($html5player);
    }
}
