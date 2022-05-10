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
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/elo_import_data/lib.php');
require_once($CFG->dirroot . '/blocks/elo_import_data/import_form.php');
require_once($CFG->dirroot . '/blocks/elo_import_data/mapping_form.php');

$verbosescales = optional_param('verbosescales', 1, PARAM_BOOL);
$iid = optional_param('iid', null, PARAM_INT);
$forceimport = optional_param('forceimport', true, PARAM_BOOL);
$url = new moodle_url('/blocks/elo_import_data/import.php');

if ($verbosescales !== 1) {
    $url->param('verbosescales', $verbosescales);
}
$PAGE->set_url($url);
require_login();
$title = get_string('importblocktitle', 'block_elo_import_data');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('block_elo_import_data');
$profilefields = tool_import_utils::get_point_user_profile_fields_import();
if (!$iid) {
    $mform = new blockselo_import_data_import_form(null, array('includeseparator' => true, 'verbosescales' => true, 'acceptedtypes' =>
        array('.csv', '.txt')));
    if ($formdata = $mform->get_data()) {
        $text = $formdata->userdata;
        $csvimport = new gradeimport_csv_load_data_elo();
        $csvimport->load_csv_content($text, $formdata->encoding, 'tab', $formdata->previewrows);
        $csvimporterror = $csvimport->get_error();
        if (!empty($csvimporterror)) {
            echo $renderer->errors(array($csvimport->get_error()));
            echo $OUTPUT->footer();
            die();
        }
        $iid = $csvimport->get_iid();
        echo $renderer->import_preview_page($csvimport->get_headers(), $csvimport->get_previewdata());
    } else {
        echo $mform->display();
        echo $OUTPUT->footer();
        die();
    }
}
// Data has already been submitted so we can use the $iid to retrieve it.
$csvimport = new csv_import_reader_elo($iid, 'point');
$header = $csvimport->get_columns();
// Get a new import code for updating to the grade book.
if (empty($importcode)) {
    $importcode = get_new_importcode_elo();
}

$mappingformdata = array(
    'gradeitems' => $profilefields,
    'header' => $header,
    'iid' => $iid,
    'id' => $id,
    'forceimport' => $forceimport,
    'importcode' => $importcode,
    'verbosescales' => $verbosescales
);
// We create a form to handle mapping data from the file to the database.
$mform2 = new blockselo_import_data_mapping_form(null, $mappingformdata);
if ($formdata2 = $mform2->get_data()) {
    $gradeimport = new gradeimport_csv_load_data_elo($profilefields);
    $gradeimport->init();
    $status = $gradeimport->prepare_import_grade_data($header, $formdata2, $csvimport, false, false, $verbosescales);
    if ($status) {
        point_import_commit();
    } else {
        $errors = $gradeimport->get_gradebookerrors();
        $errors[] = get_string('importfailed', 'grades');
        echo $renderer->errors($errors);
    }
    echo $OUTPUT->footer();
} else {
    // If data hasn't been submitted then display the data mapping form.
    echo $mform2->display();
    echo $OUTPUT->footer();
}


