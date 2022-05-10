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
 * @creditstheme_boost - MoodleHQ
 * @licensehttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_ouelofordson_menusettings', get_string('menusettings', 'theme_ouelofordson'));

// This is the descriptor for Course Management Panel
$name = 'theme_ouelofordson/coursemanagementinfo';
$heading = get_string('coursemanagementinfo', 'theme_ouelofordson');
$information = get_string('coursemanagementinfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Show/hide coursemanagement slider toggle.
$name = 'theme_ouelofordson/coursemanagementtoggle';
$title = get_string('coursemanagementtoggle', 'theme_ouelofordson');
$description = get_string('coursemanagementtoggle_desc', 'theme_ouelofordson');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Frontpage Textbox.
$name = 'theme_ouelofordson/coursemanagementtextbox';
$title = get_string('coursemanagementtextbox', 'theme_ouelofordson');
$description = get_string('coursemanagementtextbox_desc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Frontpage Textbox.
$name = 'theme_ouelofordson/studentdashboardtextbox';
$title = get_string('studentdashboardtextbox', 'theme_ouelofordson');
$description = get_string('studentdashboardtextbox_desc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Show/hide course editing cog.
$name = 'theme_ouelofordson/courseeditingcog';
$title = get_string('courseeditingcog', 'theme_ouelofordson');
$description = get_string('courseeditingcog_desc', 'theme_ouelofordson');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Show/hide student grades.
$name = 'theme_ouelofordson/showstudentgrades';
$title = get_string('showstudentgrades', 'theme_ouelofordson');
$description = get_string('showstudentgrades_desc', 'theme_ouelofordson');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Show/hide student completion.
$name = 'theme_ouelofordson/showstudentcompletion';
$title = get_string('showstudentcompletion', 'theme_ouelofordson');
$description = get_string('showstudentcompletion_desc', 'theme_ouelofordson');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Show/hide course settings for students.
$name = 'theme_ouelofordson/showcourseadminstudents';
$title = get_string('showcourseadminstudents', 'theme_ouelofordson');
$description = get_string('showcourseadminstudents_desc', 'theme_ouelofordson');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// This is the descriptor for course menu
$name = 'theme_ouelofordson/mycoursesmenuinfo';
$heading = get_string('mycoursesinfo', 'theme_ouelofordson');
$information = get_string('mycoursesinfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Toggle courses display in custommenu.
$name = 'theme_ouelofordson/displaymycourses';
$title = get_string('displaymycourses', 'theme_ouelofordson');
$description = get_string('displaymycoursesdesc', 'theme_ouelofordson');
$default = false;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Toggle courses display in custommenu.
$name = 'theme_ouelofordson/displaythiscourse';
$title = get_string('displaythiscourse', 'theme_ouelofordson');
$description = get_string('displaythiscoursedesc', 'theme_ouelofordson');
$default = false;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Set terminology for dropdown course list
$name = 'theme_ouelofordson/mycoursetitle';
$title = get_string('mycoursetitle','theme_ouelofordson');
$description = get_string('mycoursetitledesc', 'theme_ouelofordson');
$default = 'course';
$choices = array(
	'course' => get_string('mycourses', 'theme_ouelofordson'),
	'module' => get_string('mymodules', 'theme_ouelofordson'),
	'unit' => get_string('myunits', 'theme_ouelofordson'),
	'class' => get_string('myclasses', 'theme_ouelofordson'),
	'training' => get_string('mytraining', 'theme_ouelofordson'),
	'pd' => get_string('myprofessionaldevelopment', 'theme_ouelofordson'),
	'cred' => get_string('mycred', 'theme_ouelofordson'),
	'plan' => get_string('myplans', 'theme_ouelofordson'),
	'comp' => get_string('mycomp', 'theme_ouelofordson'),
	'program' => get_string('myprograms', 'theme_ouelofordson'),
	'lecture' => get_string('mylectures', 'theme_ouelofordson'),
	'lesson' => get_string('mylessons', 'theme_ouelofordson'),
	);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

//Drawer Menu
// This is the descriptor for nav drawer
$name = 'theme_ouelofordson/drawermenuinfo';
$heading = get_string('setting_navdrawersettings', 'theme_ouelofordson');
$information = get_string('setting_navdrawersettings_desc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

$name = 'theme_ouelofordson/shownavdrawer';
$title = get_string('shownavdrawer', 'theme_ouelofordson');
$description = get_string('shownavdrawer_desc', 'theme_ouelofordson');
$default = true;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/shownavclosed';
$title = get_string('shownavclosed', 'theme_ouelofordson');
$description = get_string('shownavclosed_desc', 'theme_ouelofordson');
$default = false;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);



// Must add the page after definiting all the settings!
$settings->add($page);
