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
 * Contains functions called by core.
 *
 * @package    block_elo_notification
 * @copyright  2021 HCMC Open University <elo@oude.edu.vn>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

function block_elo_notification(){
    global $USER;

    $courses = get_all_unexpired_enrolled_course();

    $unsubmitassignments = array();
    foreach($courses as $course){
        $modinfo = get_fast_modinfo($course->id);
        $assignments = $modinfo->get_instances_of('assign');    
        foreach($assignments as $assignment){
            $submission = get_assign_submission($USER->id, $assignment->instance);
            $aka = $submission->status;
            if($aka == "draft"){
                $unsubmitassignments[$course->id]['assign'][] = array('assignment' => $assignment);
            }
        }
        if($unsubmitassignments[$course->id]['assign']){
            $unsubmitassignments[$course->id]['coursename'] = $course->fullname;
        }
    }

    $content = get_notification_renderer($unsubmitassignments);

    return $content;
}

function get_assign_submission($userid, $assignid){
    global $DB;
    $sql = "SELECT * from {assign_submission} WHERE userid = $userid AND assignment = $assignid";
    $submission = $DB->get_record_sql($sql);
    return $submission;
}

function get_all_unexpired_enrolled_course(){
    $courses = enrol_get_my_courses('*');
    $now = time();
    foreach($courses as $c => $course){
        if($course->enddate <  $now){
            unset($courses[$c]);
        }
    }
    return $courses;
}

function get_notification_renderer($unsubmitassignmentscourses){
    $content = "";
    foreach($unsubmitassignmentscourses as $unsubmitassignmentscourse){
        if($unsubmitassignmentscourse['assign'] != null){
            $content.="<div style='overflow-x: auto;'>
                <h5><strong>".$unsubmitassignmentscourse['coursename']."</strong></h5>";
            foreach($unsubmitassignmentscourse['assign'] as $unsubmitassignment){
            $newurl = new moodle_url('/mod/assign/view.php?id='.$unsubmitassignment['assignment']->id);
            $content.="<p class='my-0'><a class='ml-3 font-weight-bold' style='white-space: nowrap;'
                        target='_blank' 
                        href='$newurl' 
                        id='notification-id-4-link'>".$unsubmitassignment['assignment']->name.get_string("_draft","block_elo_notification")
                        . "</a></p>";
            }
            $content.="</div>";
        }

    }
    
    return $content;
}
