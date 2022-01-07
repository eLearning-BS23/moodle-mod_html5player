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

/**
 * Define all the restore steps that will be used by the restore_page_activity_task
 */

/**
 * Structure step to restore one html5player activity
 */
class restore_html5player_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('html5player', '/activity/html5player');
        $paths[] = new restore_path_element('html5player_html5videos', '/activity/html5player/html5videos/html5video');
        if ($userinfo) {
            $paths[] = new restore_path_element('html5player_html5trackings', '/activity/html5player/html5trackings/html5tracking');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_html5player($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.

        // insert the html5player record
        $newitemid = $DB->insert_record('html5player', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_html5player_html5videos($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->html5player = $this->get_new_parentid('html5player');

        $newitemid = $DB->insert_record('html5player_html5videos', $data);
        $this->set_mapping('html5player_html5video', $oldid, $newitemid);
    }

    protected function process_html5player_html5trackings($data) {
        global $DB;

        $data = (object)$data;

        $data->html5player = $this->get_new_parentid('html5player');
        $data->html5videoid = $this->get_mappingid('html5player_html5video', $data->optionid);
        $data->user = $this->get_mappingid('user', $data->user);

        $newitemid = $DB->insert_record('html5player_html5trackings', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function after_execute() {
        // Add html5player related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_html5player', 'intro', null);
        $this->add_related_files('mod_html5player', 'content', null);
    }
}
