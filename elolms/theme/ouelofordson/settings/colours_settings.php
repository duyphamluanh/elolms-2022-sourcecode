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
 * Colours settings page file.
 *
 * @packagetheme_ouelofordson
 * @copyright  2016 Chris Kenniburg
 * @creditstheme_ouelofordson - MoodleHQ
 * @licensehttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_ouelofordson_colours', get_string('colours_settings', 'theme_ouelofordson'));
$page->add(new admin_setting_heading('theme_ouelofordson_colours', get_string('colours_headingsub', 'theme_ouelofordson'), format_text(get_string('colours_desc' , 'theme_ouelofordson'), FORMAT_MARKDOWN)));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_configtextarea('theme_ouelofordson/scsspre',
    get_string('rawscsspre', 'theme_ouelofordson'), get_string('rawscsspre_desc', 'theme_ouelofordson'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brandprimary.
    $name = 'theme_ouelofordson/brandprimary';
    $title = get_string('brandprimary', 'theme_ouelofordson');
    $description = get_string('brandprimary_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brandsuccess.
    $name = 'theme_ouelofordson/brandsuccess';
    $title = get_string('brandsuccess', 'theme_ouelofordson');
    $description = get_string('brandsuccess_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brandwarning.
    $name = 'theme_ouelofordson/brandwarning';
    $title = get_string('brandwarning', 'theme_ouelofordson');
    $description = get_string('brandwarning_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $branddanger.
    $name = 'theme_ouelofordson/branddanger';
    $title = get_string('branddanger', 'theme_ouelofordson');
    $description = get_string('branddanger_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brandinfo.
    $name = 'theme_ouelofordson/brandinfo';
    $title = get_string('brandinfo', 'theme_ouelofordson');
    $description = get_string('brandinfo_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // @bodyBackground setting.
    $name = 'theme_ouelofordson/bodybackground';
    $title = get_string('bodybackground', 'theme_ouelofordson');
    $description = get_string('bodybackground_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // @breadcrumbBackground setting.
    $name = 'theme_ouelofordson/breadcrumbbkg';
    $title = get_string('breadcrumbbkg', 'theme_ouelofordson');
    $description = get_string('breadcrumbbkg_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // marketing tile text background
    $name = 'theme_ouelofordson/markettextbg';
    $title = get_string('markettextbg', 'theme_ouelofordson');
    $description = get_string('markettextbg_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // layout card background
    $name = 'theme_ouelofordson/cardbkg';
    $title = get_string('cardbkg', 'theme_ouelofordson');
    $description = get_string('cardbkg_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // layout drawer background
    $name = 'theme_ouelofordson/drawerbkg';
    $title = get_string('drawerbkg', 'theme_ouelofordson');
    $description = get_string('drawerbkg_desc', 'theme_ouelofordson');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_configtextarea('theme_ouelofordson/scss', get_string('rawscss', 'theme_ouelofordson'),
    get_string('rawscss_desc', 'theme_ouelofordson'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);