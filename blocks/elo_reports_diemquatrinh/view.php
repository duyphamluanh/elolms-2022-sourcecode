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
 * Progress Bar block overview page
 *
 * @package    contrib
 * @subpackage block_progress
 * @copyright  2010 Michael de Raadt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/elo_reports_diemquatrinh/lib.php');

$url = new moodle_url('/blocks/elo_reports_diemquatrinh/view.php');
$PAGE->set_url($url);

$PAGE->set_context(context_system::instance());
$title = get_string('blocktitle', 'block_elo_reports_diemquatrinh');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);

//Must import js css
$PAGE->requires->js('/blocks/elo_reports_diemquatrinh/js/chosen_v1.8.7/chosendiemquatrinh.jquery.min.js');
$PAGE->requires->css('/blocks/elo_reports_diemquatrinh/js/chosen_v1.8.7/chosendiemquatrinh.min.css', true);
$PAGE->requires->js('/blocks/elo_reports_diemquatrinh/js/advanced_searchdqt.js');

echo $OUTPUT->header();
echo $OUTPUT->container_start('block_elo_reports_diemquatrinh');
require_login();

//Report format
$params = elo_reports_diemquatrinh_get_filter_params();
$crformat = $params['crformat'];
echo '<div class="alert alert-info alert-block" role="alert">' . get_string('eloreportpointnotification', 'block_elo_reports_diemquatrinh') . '</div>';
echo block_elo_reports_diemquatrinh_dropdownlist_reportformat($params);
echo '<div id="elo_reports_datagird"></div>';
$PAGE->requires->js_call_amd('block_elo_reports_diemquatrinh/init', 'init', array('#elo_reports_datagird', null));
echo $OUTPUT->container_end();
echo $OUTPUT->footer();
