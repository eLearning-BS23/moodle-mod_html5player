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

    $sizingoptions = [
        1 => get_string('responsive','mod_html5player'),
        2 => get_string('fixed','mod_html5player'),
    ];
    $settings->add(new admin_setting_configselect('html5player/sizing',
        get_string('sizing', 'mod_html5player'),
        get_string('sizing_help', 'mod_html5player'), '1', $sizingoptions));

    $aspectratios = [
        1 => get_string('one_by_one','mod_html5player'),
        2 => get_string('three_by_two','mod_html5player'),
        3 => get_string('four_by_three','mod_html5player'),
        4 => get_string('sixteen_by_nine','mod_html5player'),
        5 => get_string('twenty_one_by_nine','mod_html5player'),
        6 => get_string('nine_by_sixteen','mod_html5player'),
        7 => get_string('custom','mod_html5player'),
    ];
    $settings->add(new admin_setting_configselect('html5player/aspect_ratio',
        get_string('aspect_ratio', 'mod_html5player'),
        get_string('aspect_ratio_help', 'mod_html5player'), '7', $aspectratios));

    $units = [
        1 => get_string('pixel','mod_html5player'),
        2 => get_string('em','mod_html5player'),
        3 => get_string('percentage','mod_html5player'),
    ];
    $settings->add(new admin_setting_configselect('html5player/units',
        get_string('units', 'mod_html5player'),
        get_string('units_help', 'mod_html5player'), '1', $units));

    $settings->add(new admin_setting_configtext('html5player/width',
        get_string('width', 'mod_html5player'),
        get_string('width_help', 'mod_html5player'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('html5player/height',
        get_string('height', 'mod_html5player'),
        get_string('height_help', 'mod_html5player'), '', PARAM_TEXT, 50));
}
