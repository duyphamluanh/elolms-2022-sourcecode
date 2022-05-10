<?php

defined('MOODLE_INTERNAL') || die();

/* Marketing Spot Settings temp*/
$page = new admin_settingpage('theme_ouelofordson_marketing', get_string('marketingheading', 'theme_ouelofordson'));

// Toggle FP Textbox Spots.
$name = 'theme_ouelofordson/togglemarketing';
$title = get_string('togglemarketing' , 'theme_ouelofordson');
$description = get_string('togglemarketing_desc', 'theme_ouelofordson');
$displaytop = get_string('displaytop', 'theme_ouelofordson');
$displaybottom = get_string('displaybottom', 'theme_ouelofordson');
$default = '2';
$choices = array('1'=>$displaytop, '2'=>$displaybottom);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// This is the descriptor for Marketing Spot One
$name = 'theme_ouelofordson/marketing1info';
$heading = get_string('marketing1', 'theme_ouelofordson');
$information = get_string('marketinginfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Marketing Spot One
$name = 'theme_ouelofordson/marketing1';
$title = get_string('marketingtitle', 'theme_ouelofordson');
$description = get_string('marketingtitledesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Background image setting.
$name = 'theme_ouelofordson/marketing1image';
$title = get_string('marketingimage', 'theme_ouelofordson');
$description = get_string('marketingimage_desc', 'theme_ouelofordson');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing1content';
$title = get_string('marketingcontent', 'theme_ouelofordson');
$description = get_string('marketingcontentdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing1buttontext';
$title = get_string('marketingbuttontext', 'theme_ouelofordson');
$description = get_string('marketingbuttontextdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing1buttonurl';
$title = get_string('marketingbuttonurl', 'theme_ouelofordson');
$description = get_string('marketingbuttonurldesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing1target';
$title = get_string('marketingurltarget' , 'theme_ouelofordson');
$description = get_string('marketingurltargetdesc', 'theme_ouelofordson');
$target1 = get_string('marketingurltargetself', 'theme_ouelofordson');
$target2 = get_string('marketingurltargetnew', 'theme_ouelofordson');
$target3 = get_string('marketingurltargetparent', 'theme_ouelofordson');
$default = 'target1';
$choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// This is the descriptor for Marketing Spot Two
$name = 'theme_ouelofordson/marketing2info';
$heading = get_string('marketing2', 'theme_ouelofordson');
$information = get_string('marketinginfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Marketing Spot Two.
$name = 'theme_ouelofordson/marketing2';
$title = get_string('marketingtitle', 'theme_ouelofordson');
$description = get_string('marketingtitledesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Background image setting.
$name = 'theme_ouelofordson/marketing2image';
$title = get_string('marketingimage', 'theme_ouelofordson');
$description = get_string('marketingimage_desc', 'theme_ouelofordson');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing2content';
$title = get_string('marketingcontent', 'theme_ouelofordson');
$description = get_string('marketingcontentdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing2buttontext';
$title = get_string('marketingbuttontext', 'theme_ouelofordson');
$description = get_string('marketingbuttontextdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing2buttonurl';
$title = get_string('marketingbuttonurl', 'theme_ouelofordson');
$description = get_string('marketingbuttonurldesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing2target';
$title = get_string('marketingurltarget' , 'theme_ouelofordson');
$description = get_string('marketingurltargetdesc', 'theme_ouelofordson');
$target1 = get_string('marketingurltargetself', 'theme_ouelofordson');
$target2 = get_string('marketingurltargetnew', 'theme_ouelofordson');
$target3 = get_string('marketingurltargetparent', 'theme_ouelofordson');
$default = 'target1';
$choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// This is the descriptor for Marketing Spot Three
$name = 'theme_ouelofordson/marketing3info';
$heading = get_string('marketing3', 'theme_ouelofordson');
$information = get_string('marketinginfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Marketing Spot Three.
$name = 'theme_ouelofordson/marketing3';
$title = get_string('marketingtitle', 'theme_ouelofordson');
$description = get_string('marketingtitledesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Background image setting.
$name = 'theme_ouelofordson/marketing3image';
$title = get_string('marketingimage', 'theme_ouelofordson');
$description = get_string('marketingimage_desc', 'theme_ouelofordson');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing3content';
$title = get_string('marketingcontent', 'theme_ouelofordson');
$description = get_string('marketingcontentdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing3buttontext';
$title = get_string('marketingbuttontext', 'theme_ouelofordson');
$description = get_string('marketingbuttontextdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing3buttonurl';
$title = get_string('marketingbuttonurl', 'theme_ouelofordson');
$description = get_string('marketingbuttonurldesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing3target';
$title = get_string('marketingurltarget' , 'theme_ouelofordson');
$description = get_string('marketingurltargetdesc', 'theme_ouelofordson');
$target1 = get_string('marketingurltargetself', 'theme_ouelofordson');
$target2 = get_string('marketingurltargetnew', 'theme_ouelofordson');
$target3 = get_string('marketingurltargetparent', 'theme_ouelofordson');
$default = 'target1';
$choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// This is the descriptor for Marketing Spot Four
$name = 'theme_ouelofordson/marketing4info';
$heading = get_string('marketing4', 'theme_ouelofordson');
$information = get_string('marketinginfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Marketing Spot
$name = 'theme_ouelofordson/marketing4';
$title = get_string('marketingtitle', 'theme_ouelofordson');
$description = get_string('marketingtitledesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Background image setting.
$name = 'theme_ouelofordson/marketing4image';
$title = get_string('marketingimage', 'theme_ouelofordson');
$description = get_string('marketingimage_desc', 'theme_ouelofordson');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing4image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing4content';
$title = get_string('marketingcontent', 'theme_ouelofordson');
$description = get_string('marketingcontentdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing4buttontext';
$title = get_string('marketingbuttontext', 'theme_ouelofordson');
$description = get_string('marketingbuttontextdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing4buttonurl';
$title = get_string('marketingbuttonurl', 'theme_ouelofordson');
$description = get_string('marketingbuttonurldesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing4target';
$title = get_string('marketingurltarget' , 'theme_ouelofordson');
$description = get_string('marketingurltargetdesc', 'theme_ouelofordson');
$target1 = get_string('marketingurltargetself', 'theme_ouelofordson');
$target2 = get_string('marketingurltargetnew', 'theme_ouelofordson');
$target3 = get_string('marketingurltargetparent', 'theme_ouelofordson');
$default = 'target1';
$choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// This is the descriptor for Marketing Spot Four
$name = 'theme_ouelofordson/marketing5info';
$heading = get_string('marketing5', 'theme_ouelofordson');
$information = get_string('marketinginfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Marketing Spot
$name = 'theme_ouelofordson/marketing5';
$title = get_string('marketingtitle', 'theme_ouelofordson');
$description = get_string('marketingtitledesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Background image setting.
$name = 'theme_ouelofordson/marketing5image';
$title = get_string('marketingimage', 'theme_ouelofordson');
$description = get_string('marketingimage_desc', 'theme_ouelofordson');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing5image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing5content';
$title = get_string('marketingcontent', 'theme_ouelofordson');
$description = get_string('marketingcontentdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing5buttontext';
$title = get_string('marketingbuttontext', 'theme_ouelofordson');
$description = get_string('marketingbuttontextdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing5buttonurl';
$title = get_string('marketingbuttonurl', 'theme_ouelofordson');
$description = get_string('marketingbuttonurldesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing5target';
$title = get_string('marketingurltarget' , 'theme_ouelofordson');
$description = get_string('marketingurltargetdesc', 'theme_ouelofordson');
$target1 = get_string('marketingurltargetself', 'theme_ouelofordson');
$target2 = get_string('marketingurltargetnew', 'theme_ouelofordson');
$target3 = get_string('marketingurltargetparent', 'theme_ouelofordson');
$default = 'target1';
$choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// This is the descriptor for Marketing Spot Four
$name = 'theme_ouelofordson/marketing6info';
$heading = get_string('marketing6', 'theme_ouelofordson');
$information = get_string('marketinginfodesc', 'theme_ouelofordson');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Marketing Spot
$name = 'theme_ouelofordson/marketing6';
$title = get_string('marketingtitle', 'theme_ouelofordson');
$description = get_string('marketingtitledesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Background image setting.
$name = 'theme_ouelofordson/marketing6image';
$title = get_string('marketingimage', 'theme_ouelofordson');
$description = get_string('marketingimage_desc', 'theme_ouelofordson');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing6image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing6content';
$title = get_string('marketingcontent', 'theme_ouelofordson');
$description = get_string('marketingcontentdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing6buttontext';
$title = get_string('marketingbuttontext', 'theme_ouelofordson');
$description = get_string('marketingbuttontextdesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing6buttonurl';
$title = get_string('marketingbuttonurl', 'theme_ouelofordson');
$description = get_string('marketingbuttonurldesc', 'theme_ouelofordson');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_ouelofordson/marketing6target';
$title = get_string('marketingurltarget' , 'theme_ouelofordson');
$description = get_string('marketingurltargetdesc', 'theme_ouelofordson');
$target1 = get_string('marketingurltargetself', 'theme_ouelofordson');
$target2 = get_string('marketingurltargetnew', 'theme_ouelofordson');
$target3 = get_string('marketingurltargetparent', 'theme_ouelofordson');
$default = 'target1';
$choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);