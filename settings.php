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
 * html5player plugin settings.
 *
 * @package mod_html5player
 * @copyright  2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('html5player/account_id',
        get_string('account_id', 'mod_html5player'),
        get_string('account_id_help', 'mod_html5player'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('html5player/width',
        get_string('width', 'mod_html5player'),
        get_string('width_help', 'mod_html5player'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('html5player/height',
        get_string('height', 'mod_html5player'),
        get_string('height_help', 'mod_html5player'), '', PARAM_TEXT, 50));

}
