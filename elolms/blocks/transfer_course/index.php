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
 * Course and category management interfaces.
 *
 * @package    core_course
 * @copyright  2013 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
$categoryid = optional_param('categoryid', null, PARAM_INT);
$selectedcategoryid = optional_param('selectedcategoryid', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', null, PARAM_INT);
$viewmode = optional_param('view', 'courses', PARAM_ALPHA); // Can be one of default, combined, courses, or categories.

// Search related params.
$search = optional_param('search', '', PARAM_RAW); // Search words. Shortname, fullname, idnumber and summary get searched.
$blocklist = optional_param('blocklist', 0, PARAM_INT); // Find courses containing this block.
$modulelist = optional_param('modulelist', '', PARAM_PLUGIN); // Find courses containing the given modules.

if (!in_array($viewmode, array('default', 'combined', 'courses', 'categories'))) {
    $viewmode = 'courses';
}

$issearching = ($search !== '' || $blocklist !== 0 || $modulelist !== '');
if ($issearching) {
    $viewmode = 'courses';
}

$url = new moodle_url('/blocks/transfer_course/index.php');
$systemcontext = $context = context_system::instance();

//use only for 3.7
//if ($courseid) {
//    $record = get_course($courseid);
//    $course = new core_course_list_element($record);
//    $category = core_course_category::get($course->category);
//    $categoryid = $category->id;
//    $context = context_coursecat::instance($category->id);
//    $url->param('categoryid', $categoryid);
//    $url->param('courseid', $course->id);
//
//} else if ($categoryid) {
//    $courseid = null;
//    $course = null;
//    $category = core_course_category::get($categoryid);
//    $context = context_coursecat::instance($category->id);
//    $url->param('categoryid', $category->id);
//
//} else {
//    $course = null;
//    $courseid = null;
//    $topchildren = core_course_category::top()->get_children();
//    if (empty($topchildren)) {
//        throw new moodle_exception('cannotviewcategory', 'error');
//    }
//    $category = reset($topchildren);
//    $categoryid = $category->id;
//    $context = context_coursecat::instance($category->id);
//    $url->param('categoryid', $category->id);
//}
//use only for 3.5
if ($courseid) {
    $record = get_course($courseid);
    $course = new course_in_list($record);
    $category = coursecat::get($course->category);
    $categoryid = $category->id;
    $context = context_coursecat::instance($category->id);
    $url->param('categoryid', $categoryid);
    $url->param('courseid', $course->id);
    $displaycoursedetail = (isset($courseid));

} else if ($categoryid) {
    $courseid = null;
    $course = null;
    $category = coursecat::get($categoryid);
    $context = context_coursecat::instance($category->id);
    $url->param('categoryid', $category->id);

} else {
    $course = null;
    $courseid = null;
    $category = coursecat::get_default();
    $categoryid = $category->id;
    $context = context_coursecat::instance($category->id);
    $url->param('categoryid', $category->id);
}

// Check if there is a selected category param, and if there is apply it.
if ($course === null && $selectedcategoryid !== null && $selectedcategoryid !== $categoryid) {
    $url->param('categoryid', $selectedcategoryid);
}

if ($page !== 0) {
    $url->param('page', $page);
}
if ($viewmode !== 'default') {
    $url->param('view', $viewmode);
}
if ($search !== '') {
    $url->param('search', $search);
}
if ($blocklist !== 0) {
    $url->param('blocklist', $search);
}
if ($modulelist !== '') {
    $url->param('modulelist', $search);
}

$pageheading = get_string('pluginname', 'block_transfer_course');
$title = get_string('ou:listcourses', 'block_transfer_course');
$PAGE->set_context($context);
$PAGE->set_url($url);
//$PAGE->set_pagelayout('base');
$PAGE->set_title($pageheading);
$PAGE->set_heading($pageheading);
$PAGE->navbar->add($title);

// This is a system level page that operates on other contexts.
require_login();

//if (!core_course_category::has_capability_on_any(array('moodle/category:manage', 'moodle/course:create'))) {
//    // The user isn't able to manage any categories. Lets redirect them to the relevant course/index.php page.
//    $url = new moodle_url('/blocks/transfer_course/index.php');
//    if ($categoryid) {
//        $url->param('categoryid', $categoryid);
//    }
//    redirect($url);
//}
//
//// If the user poses any of these capabilities then they will be able to see the admin
//// tree and the management link within it.
//// This is the most accurate form of navigation.
//$capabilities = array(
//    'moodle/site:config',
//    'moodle/backup:backupcourse',
//    'moodle/category:manage',
//    'moodle/course:create',
//    'moodle/site:approvecourse'
//);
//if ($category && !has_any_capability($capabilities, $systemcontext)) {
//    // If the user doesn't poses any of these system capabilities then we're going to mark the manage link in the settings block
//    // as active, tell the page to ignore the active path and just build what the user would expect.
//    // This will at least give the page some relevant navigation.
//    navigation_node::override_active_url(new moodle_url('/blocks/transfer_course/index.php', array('categoryid' => $category->id)));
//    $PAGE->set_category_by_id($category->id);
//    $PAGE->navbar->ignore_active(true);
//    $PAGE->navbar->add(get_string('coursemgmt', 'admin'), $PAGE->url->out_omit_querystring());
//} else {
//    // If user has system capabilities, make sure the "Manage courses and categories" item in Administration block is active.
//    navigation_node::require_admin_tree();
//    navigation_node::override_active_url(new moodle_url('/blocks/transfer_course/index.php'));
//}

$notificationspass = array();
$notificationsfail = array();

if (!is_null($perpage)) {
    set_user_preference('coursecat_management_perpage', $perpage);
} else {
    $perpage = get_user_preferences('coursecat_management_perpage', $CFG->coursesperpage);
}
if ((int)$perpage != $perpage || $perpage < 2) {
    $perpage = $CFG->coursesperpage;
}

$categorysize = 4;
$coursesize = 4;
$detailssize = 4;

if ($viewmode === 'courses') {
    if (isset($courseid)) {
        $coursesize = 6;
        $detailssize = 6;
        $class = 'columns-2';
    } else {
        $coursesize = 12;
        $class = 'columns-1';
    }
}
if ($viewmode === 'default' || $viewmode === 'combined') {
    $class .= ' viewmode-cobmined';
} else {
    $class .= ' viewmode-'.$viewmode;
}
if (($viewmode === 'default' || $viewmode === 'combined' || $viewmode === 'courses') && !empty($courseid)) {
    $class .= ' course-selected';
}

/* @var core_course_management_renderer|core_renderer $renderer */
$renderer = $PAGE->get_renderer('block_transfer_course');
$renderer->enhance_management_interface();
$displaycourselisting = true;
$displaycategorylisting= false;
$displaycoursedetail = (isset($courseid));
echo $renderer->header();

if (!$issearching) {
    echo $renderer->management_heading($title, $viewmode, $categoryid);
} else {
    echo $renderer->management_heading(new lang_string('searchresults'));
}

if (count($notificationspass) > 0) {
    echo $renderer->notification(join('<br />', $notificationspass), 'notifysuccess');
}
if (count($notificationsfail) > 0) {
    echo $renderer->notification(join('<br />', $notificationsfail));
}
echo $renderer->elo_transfer_course_search_form($search);
// Start the management form.
echo $renderer->management_form_start();

echo $renderer->accessible_skipto_links($displaycategorylisting, $displaycourselisting, $displaycoursedetail);

echo $renderer->grid_start('course-category-listings', $class);

if ($displaycourselisting) {
    echo $renderer->grid_column_start($coursesize, 'course-listing');
    if (!$issearching) {
        echo $renderer->course_listing($category, $course, $page, $perpage, $viewmode);
    } else {
        list($courses, $coursescount, $coursestotal) =
            \core_course\management\helper::search_courses($search, $blocklist, $modulelist, $page, $perpage);
        echo $renderer->search_listing($courses, $coursestotal, $course, $page, $perpage, $search);
    }
    echo $renderer->grid_column_end();
    if ($displaycoursedetail) {
        echo $renderer->grid_column_start($detailssize, 'course-detail');
        echo $renderer->course_detail($course);
        echo $renderer->grid_column_end();
    }
}
echo $renderer->grid_end();

// End of the management form.
echo $renderer->management_form_end();

echo $renderer->footer();
