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

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);


        $mform->addElement('textarea','meta_info','Meta info',array(
            'rows' => '8',
            'cols' => '60',
        ));

        // Add Video Content heading.
        $mform->addElement('header', 'videocontent', get_string('header:videocontent', 'html5player'));

        // Account ID.
        $mform->addElement('text', 'account_id', get_string('account_id', 'html5player'));
        $mform->setType('account_id', PARAM_TEXT);
        $mform->addHelpButton('account_id', 'account_id', 'html5player');
        $mform->addRule('account_id', null, 'required', null, 'client');
        $account_id = get_config('html5player', 'account_id');
        $mform->setDefault('account_id', $account_id);

        // Player ID.
        $mform->addElement('text', 'player_id', get_string('player_id', 'html5player'));
        $mform->setType('player_id', PARAM_TEXT);
        $mform->addHelpButton('player_id', 'player_id', 'html5player');
        $mform->addRule('player_id', get_string('player_id','mod_html5player'), 'required', null, 'client');
        $player_id = get_config('html5player', 'player_id');
        $mform->setDefault('player_id', $player_id);

        // Video type.
        $videotypes = [
            HTML5PLYAER_VIDEO_TYPE_SINGLE => get_string('single','mod_html5player'),
            HTML5PLYAER_VIDEO_TYPE_PLAYLIST => get_string('playlist','mod_html5player'),
        ];
        $mform->addElement('select', 'video_type', get_string('video_type', 'html5player'), $videotypes);
        $mform->addHelpButton('video_type', 'video_type', 'html5player');
        $mform->addRule('video_type', null, 'required', null, 'client');
        $videotype = get_config('html5player', 'video_type');
        $mform->setDefault('video_type', $videotype);

        // Video ID.
        $mform->addElement('text', 'video_id', get_string('video_id', 'html5player'));
        $mform->setType('video_id', PARAM_TEXT);
        $mform->addHelpButton('video_id', 'video_id', 'html5player');
         $mform->addRule('video_id', null, 'required', null, 'client');


        $mform->addElement('header', 'videocontent', get_string('header:videoappearance', 'html5player'));

        // Sizing.
        $sizingoptions = [
            1 => get_string('responsive','mod_html5player'),
            2 => get_string('fixed','mod_html5player'),
        ];
        $mform->addElement('select', 'sizing', get_string('sizing', 'html5player'),$sizingoptions);
        $mform->addHelpButton('sizing', 'sizing', 'html5player');
        $mform->addRule('sizing', null, 'required', null, 'client');
        $sizingoption = get_config('html5player', 'sizing');
        $mform->setDefault('sizing', $sizingoption);

        // Aspect Ratio.
        $aspectratios = [
            1 => get_string('one_by_one','mod_html5player'),
            2 => get_string('three_by_two','mod_html5player'),
            3 => get_string('four_by_three','mod_html5player'),
            4 => get_string('sixteen_by_nine','mod_html5player'),
            5 => get_string('twenty_one_by_nine','mod_html5player'),
            6 => get_string('nine_by_sixteen','mod_html5player'),
            7 => get_string('custom','mod_html5player'),
        ];
        $mform->addElement('select', 'aspect_ratio', get_string('aspect_ratio', 'html5player'), $aspectratios);
        $mform->addHelpButton('aspect_ratio', 'sizing', 'html5player');
        $mform->addRule('aspect_ratio', null, 'required', null, 'client');
        $aspectratio = get_config('html5player', 'aspect_ratio');
        $mform->setDefault('aspect_ratio', $aspectratio);

        // Unit of height and width.
        $units = [
            1 => get_string('pixel','mod_html5player'),
            2 => get_string('em','mod_html5player'),
            3 => get_string('percentage','mod_html5player'),
        ];
        $mform->addElement('select', 'units', get_string('units', 'html5player'), $units);
        $mform->addHelpButton('units', 'units', 'html5player');
        $mform->addRule('units', null, 'required', null, 'client');
        $unit = get_config('html5player', 'units');
        $mform->setDefault('units', $unit);

        // Width of the player.
        $mform->addElement('text', 'width', get_string('width', 'html5player'));
        $mform->setType('width', PARAM_TEXT);
        $mform->addHelpButton('width', 'width', 'html5player');
        $mform->addRule('width', null, 'required', null, 'client');
        $height = get_config('html5player', 'width');
        $mform->setDefault('width', $height);

        // Height of the player.
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

