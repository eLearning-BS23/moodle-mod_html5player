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
 * html5player activity form.
 *
 * @package    mod_html5player
 * @copyright  2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
// require_once($CFG->dirroot.'/mod/url/locallib.php');

class mod_html5player_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name of the mod.
        $mform->addElement('text', 'name', get_string('name', 'html5player'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Video ID.
        $mform->addElement('text', 'video_id', get_string('video_id', 'html5player'));
        $mform->setType('video_id', PARAM_TEXT);
        $mform->addHelpButton('video_id', 'video_id', 'html5player');
        $mform->addRule('video_id', null, 'required', null, 'client');

        // Account ID.
        $mform->addElement('text', 'account_id', get_string('account_id', 'html5player'));
        $mform->setType('account_id', PARAM_TEXT);
        $mform->addHelpButton('account_id', 'account_id', 'html5player');
        $mform->addRule('account_id', null, 'required', null, 'client');
        $account_id = get_config('html5player', 'account_id');
        $mform->setDefault('account_id', $account_id);

        // Width of the player.
        $mform->addElement('text', 'width', get_string('width', 'html5player'));
        $mform->setType('width', PARAM_TEXT);
        $mform->addHelpButton('width', 'width', 'html5player');
        $mform->addRule('width', null, 'required', null, 'client');
        $height = get_config('html5player', 'width');
        $mform->setDefault('width', $height);

        // Width of the player.
        $mform->addElement('text', 'height', get_string('height', 'html5player'));
        $mform->setType('height', PARAM_TEXT);
        $mform->addHelpButton('height', 'height', 'html5player');
        $mform->addRule('height', null, 'required', null, 'client');
        $height = get_config('html5player', 'height');
        $mform->setDefault('height', $height);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}

