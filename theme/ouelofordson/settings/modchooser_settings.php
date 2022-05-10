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
* Presets settings page file.
*
* @package    theme_ouelofordson
* @copyright  2017 OCJ
* @credits    theme_boost - MoodleHQ
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_ouelofordson_presets', get_string('presets_settings', 'theme_ouelofordson'));

// modchooser settings tab.
$page = new admin_settingpage('theme_ouelofordson_modchooser', get_string('modchoosersettingspage', 'theme_ouelofordson'));

// Custom Menu label
$name = 'theme_ouelofordson/modchoosercustomlabel';
$title = get_string('modchoosercustomlabel', 'theme_ouelofordson');
$description = get_string('modchoosercustomlabel_desc', 'theme_ouelofordson');
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$setting = new admin_setting_configtextarea('theme_ouelofordson/commonlyused', get_string('commonlyused', 'theme_ouelofordson'), get_string('commonlyuseddesc', 'theme_ouelofordson'), '', PARAM_RAW);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// show only custom activities/resource
$name = 'theme_ouelofordson/showonlycustomactivities';
$title = get_string('showonlycustomactivities', 'theme_ouelofordson');
$description = get_string('showonlycustomactivities_desc', 'theme_ouelofordson');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// show only custom activities/resource
$name = 'theme_ouelofordson/showalltomanager';
$title = get_string('showalltomanager', 'theme_ouelofordson');
$description = get_string('showalltomanager_desc', 'theme_ouelofordson');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$settings->add($page);
