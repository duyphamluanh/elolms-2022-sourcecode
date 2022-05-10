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
* Heading and course images settings page file.
*
* @packagetheme_ouelofordson
* @copyright  2016 Chris Kenniburg
* @creditstheme_ouelofordson - MoodleHQ
* @licensehttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_ouelofordson_content', get_string('contentsettings', 'theme_ouelofordson'));
// Content Info
$name = 'theme_ouelofordson/textcontentinfo';
$heading = get_string('textcontentinfo', 'theme_ouelofordson');
$information = get_string('textcontentinfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Frontpage Textbox.
$name = 'theme_ouelofordson/fptextbox';
$title = get_string('fptextbox', 'theme_ouelofordson');
$description = get_string('fptextbox_desc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Frontpage Textbox Logged Out.
$name = 'theme_ouelofordson/fptextboxlogout';
$title = get_string('fptextboxlogout', 'theme_ouelofordson');
$description = get_string('fptextboxlogout_desc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Alert setting.
$name = 'theme_ouelofordson/alertbox';
$title = get_string('alert', 'theme_ouelofordson');
$description = get_string('alert_desc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
