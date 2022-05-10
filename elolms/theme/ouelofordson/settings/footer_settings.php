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
* Social networking settings page file.
*
* @package    theme_ouelofordson
* @copyright  2016 Chris Kenniburg
* @credits    theme_boost - MoodleHQ
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

/* Social Network Settings */
$page = new admin_settingpage('theme_ouelofordson_footer', get_string('footerheading', 'theme_ouelofordson'));
$page->add(new admin_setting_heading('theme_ouelofordson_footer', get_string('footerheadingsub', 'theme_ouelofordson'), format_text(get_string('footerdesc' , 'theme_ouelofordson'), FORMAT_MARKDOWN)));

// footer branding
$name = 'theme_ouelofordson/brandorganization';
$title = get_string('brandorganization', 'theme_ouelofordson');
$description = get_string('brandorganizationdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// footer branding
$name = 'theme_ouelofordson/brandwebsite';
$title = get_string('brandwebsite', 'theme_ouelofordson');
$description = get_string('brandwebsitedesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// footer branding
$name = 'theme_ouelofordson/brandphone';
$title = get_string('brandphone', 'theme_ouelofordson');
$description = get_string('brandphonedesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// footer branding
$name = 'theme_ouelofordson/brandemail';
$title = get_string('brandemail', 'theme_ouelofordson');
$description = get_string('brandemaildesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Footnote setting.
$name = 'theme_ouelofordson/footnote';
$title = get_string('footnote', 'theme_ouelofordson');
$description = get_string('footnotedesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);


// This is the descriptor for socialicons
$name = 'theme_ouelofordson/socialiconsinfo';
$heading = get_string('footerheadingsocial', 'theme_ouelofordson');
$information = get_string('footerdesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Website url setting.
$name = 'theme_ouelofordson/website';
$title = get_string('website', 'theme_ouelofordson');
$description = get_string('websitedesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Blog url setting.
$name = 'theme_ouelofordson/blog';
$title = get_string('blog', 'theme_ouelofordson');
$description = get_string('blogdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Facebook url setting.
$name = 'theme_ouelofordson/facebook';
$title = get_string(        'facebook', 'theme_ouelofordson');
$description = get_string(      'facebookdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Flickr url setting.
$name = 'theme_ouelofordson/flickr';
$title = get_string('flickr', 'theme_ouelofordson');
$description = get_string('flickrdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Twitter url setting.
$name = 'theme_ouelofordson/twitter';
$title = get_string('twitter', 'theme_ouelofordson');
$description = get_string('twitterdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Google+ url setting.
$name = 'theme_ouelofordson/googleplus';
$title = get_string('googleplus', 'theme_ouelofordson');
$description = get_string('googleplusdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// LinkedIn url setting.
$name = 'theme_ouelofordson/linkedin';
$title = get_string('linkedin', 'theme_ouelofordson');
$description = get_string('linkedindesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Tumblr url setting.
$name = 'theme_ouelofordson/tumblr';
$title = get_string('tumblr', 'theme_ouelofordson');
$description = get_string('tumblrdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Pinterest url setting.
$name = 'theme_ouelofordson/pinterest';
$title = get_string('pinterest', 'theme_ouelofordson');
$description = get_string('pinterestdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Instagram url setting.
$name = 'theme_ouelofordson/instagram';
$title = get_string('instagram', 'theme_ouelofordson');
$description = get_string('instagramdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// YouTube url setting.
$name = 'theme_ouelofordson/youtube';
$title = get_string('youtube', 'theme_ouelofordson');
$description = get_string('youtubedesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Vimeo url setting.
$name = 'theme_ouelofordson/vimeo';
$title = get_string('vimeo', 'theme_ouelofordson');
$description = get_string('vimeodesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Skype url setting.
$name = 'theme_ouelofordson/skype';
$title = get_string('skype', 'theme_ouelofordson');
$description = get_string('skypedesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// General social url setting 1.
$name = 'theme_ouelofordson/social1';
$title = get_string('sociallink', 'theme_ouelofordson');
$description = get_string('sociallinkdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Social icon setting 1.
$name = 'theme_ouelofordson/socialicon1';
$title = get_string('sociallinkicon', 'theme_ouelofordson');
$description = get_string('sociallinkicondesc', 'theme_ouelofordson');
$default = 'home';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// General social url setting 2.
$name = 'theme_ouelofordson/social2';
$title = get_string('sociallink', 'theme_ouelofordson');
$description = get_string('sociallinkdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Social icon setting 2.
$name = 'theme_ouelofordson/socialicon2';
$title = get_string('sociallinkicon', 'theme_ouelofordson');
$description = get_string('sociallinkicondesc', 'theme_ouelofordson');
$default = 'home';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// General social url setting 3.
$name = 'theme_ouelofordson/social3';
$title = get_string('sociallink', 'theme_ouelofordson');
$description = get_string('sociallinkdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Social icon setting 3.
$name = 'theme_ouelofordson/socialicon3';
$title = get_string('sociallinkicon', 'theme_ouelofordson');
$description = get_string('sociallinkicondesc', 'theme_ouelofordson');
$default = 'home';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
