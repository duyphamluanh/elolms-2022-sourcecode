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
 * Wrapper script redirecting user operations to correct destination.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */
require_once("../../config.php");
require_once($CFG->dirroot . '/blocks/elo_reports_diemquatrinh/lib.php');

use block_elo_reports_diemquatrinh\fetcher_diemquatrinh;
use stdClass;
$params = required_param('id', PARAM_TEXT);
$courses = explode(",", $params);
$datas = array();
foreach ($courses as $courseid) {
    $records = new stdClass();
    $courselms = new block_elo_reports_diemquatrinh\fetcher_diemquatrinh($courseid);
    $records->semester = $courselms->semester;
    $records->year = $courselms->year;
    $records->shortname = $courselms->shortcoursename;
    $records->fullname = $courselms->fullcoursename;
    $records->teachername = $courselms->teachername;
    $records->row = $courselms->rawdata;
    $datas[$courselms->fullshortname] = $records;
}
export_diemquatrinh_to_excel($datas);


