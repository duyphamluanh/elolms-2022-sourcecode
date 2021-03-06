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
 * Activity eloprogress reports
 *
 * @package    report
 * @subpackage eloprogress
 * @copyright  2008 Sam Marshall
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/completionlib.php');

define('COMPLETION_REPORT_PAGE', 25);

// Get course
$id = required_param('course',PARAM_INT);
$course = $DB->get_record('course',array('id'=>$id));
if (!$course) {
    print_error('invalidcourseid');
}
$context = context_course::instance($course->id);

// Sort (default lastname, optionally firstname)
$sort = optional_param('sort','',PARAM_ALPHA);
$firstnamesort = $sort == 'firstname';

// CSV format
$format = optional_param('format','',PARAM_ALPHA);
$excel = $format == 'excelcsv';
$csv = $format == 'csv' || $excel;

// Paging
$start   = optional_param('start', 0, PARAM_INT);
$sifirst = optional_param('sifirst', 'all', PARAM_NOTAGS);
$silast  = optional_param('silast', 'all', PARAM_NOTAGS);
$start   = optional_param('start', 0, PARAM_INT);

// Whether to show extra user identity information
$extrafields = get_extra_user_fields($context);
$leftcols = 1 + count($extrafields);

function csv_quote($value) {
    global $excel;
    if ($excel) {
        return core_text::convert('"'.str_replace('"',"'",$value).'"','UTF-8','UTF-16LE');
    } else {
        return '"'.str_replace('"',"'",$value).'"';
    }
}

$url = new moodle_url('/report/eloprogress/index.php', array('course'=>$id));
if ($sort !== '') {
    $url->param('sort', $sort);
}
if ($format !== '') {
    $url->param('format', $format);
}
if ($start !== 0) {
    $url->param('start', $start);
}
if ($sifirst !== 'all') {
    $url->param('sifirst', $sifirst);
}
if ($silast !== 'all') {
    $url->param('silast', $silast);
}
$PAGE->set_url($url);
//$PAGE->set_pagetype('report-dotambayprogress-index');// nhien
$PAGE->set_pagelayout('report');

require_login($course);

// Check basic permission
require_capability('report/eloprogress:view',$context);

// Get group mode
$group = groups_get_course_group($course,true); // Supposed to verify group
if ($group===0 && $course->groupmode==SEPARATEGROUPS) {
    require_capability('moodle/site:accessallgroups',$context);
}

// Get data on activities and eloprogress of all users, and give error if we've
// nothing to display (no users or no activities)
$reportsurl = $CFG->wwwroot.'/course/report.php?id='.$course->id;
$completion = new completion_info($course);
$activities = $completion->get_activities();

if ($sifirst !== 'all') {
    set_user_preference('ifirst', $sifirst);
}
if ($silast !== 'all') {
    set_user_preference('ilast', $silast);
}

if (!empty($USER->preference['ifirst'])) {
    $sifirst = $USER->preference['ifirst'];
} else {
    $sifirst = 'all';
}

if (!empty($USER->preference['ilast'])) {
    $silast = $USER->preference['ilast'];
} else {
    $silast = 'all';
}

// Generate where clause
$where = array();
//StartNhien 
$has_viewall_course_grade = has_capability('moodle/grade:viewall', $context, $USER->id);
if(!$has_viewall_course_grade){
    $where[] = '(u.id = ' . $USER->id . ')';
    $sifirst = 'all';
    $silast = 'all';        
}//EndNhien
$where_params = array();

if ($sifirst !== 'all') {
    $where[] = $DB->sql_like('u.firstname', ':sifirst', false);
    $where_params['sifirst'] = $sifirst.'%';
}

if ($silast !== 'all') {
    $where[] = $DB->sql_like('u.lastname', ':silast', false);
    $where_params['silast'] = $silast.'%';
}

// Get user match count
$total = $completion->get_num_tracked_users(implode(' AND ', $where), $where_params, $group);

// Total user count
$grandtotal = $completion->get_num_tracked_users('', array(), $group);

// Get user data
$eloprogress = array();

if ($total) {
    $eloprogress = $completion->get_progress_all(
        implode(' AND ', $where),
        $where_params,
        $group,
        $firstnamesort ? 'u.firstname ASC, u.lastname ASC' : 'u.lastname ASC, u.firstname ASC',
        $csv ? 0 : COMPLETION_REPORT_PAGE,
        $csv ? 0 : $start,
        $context
    );
    $params = array("select" => false,"paging" => false, "scrollX" => true);
    if (count($eloprogress) > 10) {
        $params['scrollY'] = 550;
        $params['searching'] = true;
        $params['fixedColumns'] = array('leftColumns' => 1);
        $params['info'] = false;
    }
    else {
        $params['info'] = false;
        $params['fixedColumns'] = array('leftColumns' => 1);
    }
    $PAGE->requires->js_call_amd('report_eloprogress/init', 'init', array('#completion-eloprogress-dttable', $params));
}

if ($csv && $grandtotal && count($activities)>0) { // Only show CSV if there are some users/actvs

    $shortname = format_string($course->shortname, true, array('context' => $context));
    header('Content-Disposition: attachment; filename=eloprogress.'.
        preg_replace('/[^a-z0-9-]/','_',core_text::strtolower(strip_tags($shortname))).'.csv');
    // Unicode byte-order mark for Excel
    if ($excel) {
        header('Content-Type: text/csv; charset=UTF-16LE');
        print chr(0xFF).chr(0xFE);
        $sep="\t".chr(0);
        $line="\n".chr(0);
    } else {
        header('Content-Type: text/csv; charset=UTF-8');
        $sep=",";
        $line="\n";
    }
} else {

    // Navigation and header
    $strreports = get_string("reports");
    $strcompletion = get_string('activitycompletion', 'completion');

    $PAGE->set_title($strcompletion);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    $PAGE->requires->js_call_amd('report_eloprogress/completion_override', 'init', [fullname($USER)]);

    // Handle groups (if enabled)
    groups_print_course_menu($course,$CFG->wwwroot.'/report/eloprogress/?course='.$course->id);
}

if (count($activities)==0) {
    echo $OUTPUT->container(get_string('err_noactivities', 'completion'), 'errorbox errorboxcontent');
    echo $OUTPUT->footer();
    exit;
}

// If no users in this course what-so-ever
if (!$grandtotal) {
    echo $OUTPUT->container(get_string('err_nousers', 'completion'), 'errorbox errorboxcontent');
    echo $OUTPUT->footer();
    exit;
}

// Build link for paging
$link = $CFG->wwwroot.'/report/eloprogress/?course='.$course->id;
if (strlen($sort)) {
    $link .= '&amp;sort='.$sort;
}
$link .= '&amp;start=';

$pagingbar = '';
$atoz_bar = '';
// Initials bar.
$prefixfirst = 'sifirst';
$prefixlast = 'silast';

if($has_viewall_course_grade){ // Nhien
$atoz_bar .= $OUTPUT->initials_bar($sifirst, 'firstinitial', get_string('firstname'), $prefixfirst, $url);
$atoz_bar .= $OUTPUT->initials_bar($silast, 'lastinitial', get_string('lastname'), $prefixlast, $url);
}
// Do we need a paging bar?
if ($total > COMPLETION_REPORT_PAGE) {

    // Paging bar
    $pagingbar .= '<div class="paging">';
    $pagingbar .= get_string('page').': ';

    $sistrings = array();
    if ($sifirst != 'all') {
        $sistrings[] =  "sifirst={$sifirst}";
    }
    if ($silast != 'all') {
        $sistrings[] =  "silast={$silast}";
    }
    $sistring = !empty($sistrings) ? '&amp;'.implode('&amp;', $sistrings) : '';

    // Display previous link
    if ($start > 0) {
        $pstart = max($start - COMPLETION_REPORT_PAGE, 0);
        $pagingbar .= "(<a class=\"previous\" href=\"{$link}{$pstart}{$sistring}\">".get_string('previous').'</a>)&nbsp;';
    }

    // Create page links
    $curstart = 0;
    $curpage = 0;
    while ($curstart < $total) {
        $curpage++;

        if ($curstart == $start) {
            $pagingbar .= '&nbsp;'.$curpage.'&nbsp;';
        } else {
            $pagingbar .= "&nbsp;<a href=\"{$link}{$curstart}{$sistring}\">$curpage</a>&nbsp;";
        }

        $curstart += COMPLETION_REPORT_PAGE;
    }

    // Display next link
    $nstart = $start + COMPLETION_REPORT_PAGE;
    if ($nstart < $total) {
        $pagingbar .= "&nbsp;(<a class=\"next\" href=\"{$link}{$nstart}{$sistring}\">".get_string('next').'</a>)';
    }

    $pagingbar .= '</div>';
}

// Okay, let's draw the table of eloprogress info,

// Start of table
if (!$csv) {
    print '<br class="clearer"/>'; // ugh

    print $atoz_bar;
    print $pagingbar;
    
    if (!$total) {
        echo $OUTPUT->heading(get_string('nothingtodisplay'));
        echo $OUTPUT->footer();
        exit;
    }
    print '<div class="dataTables_wrapper">';
    print '<table id="completion-eloprogress-dttable" class="cell-border nowrap" style="width:100%;"><thead><tr style="vertical-align:top">';

    // User heading / sort option
    print '<th class="completion-header">';
    $sistring = "&amp;silast={$silast}&amp;sifirst={$sifirst}";

    if ($firstnamesort) {
        print
            get_string('firstname')." / <a href=\"./?course={$course->id}{$sistring}\">".
            get_string('lastname').'</a>';
    } else {
        print "<a href=\"./?course={$course->id}&amp;sort=firstname{$sistring}\">".
            get_string('firstname').'</a> / '.
            get_string('lastname');
    }
    print '</th>';

    // Print user identity columns
    foreach ($extrafields as $field) {
        echo '<th scope="col" class="completion-identifyfield">' .
                get_user_field_name($field) . '</th>';
    }
} else {
    foreach ($extrafields as $field) {
        //Nhien Elo start
        if($field!="phone2"){
         echo $sep . csv_quote(get_user_field_name($field));
        }
        //Nhien end
    }
}

// Activities
$formattedactivities = array();
foreach($activities as $activity) {
    if ($activity->modname == 'label') {
        continue;
    }//Nhien elo 15_01_2019 fix l???i label kh??ng c???n ho??n th??nh ho???t ?????ng
    if ($activity->visible == 0) {
        continue;
    }
    /*$ischamdiem = get_eloprogress_gradetype($course, $activity);//Nhien elo 15_01_2019 check cac hoat dong ko cham diem
    if (!$ischamdiem) {
        continue;
    }*/
    $datepassed = $activity->completionexpected && $activity->completionexpected <= time();
    $datepassedclass = $datepassed ? 'completion-expired' : '';

    if ($activity->completionexpected) {
        $datetext=userdate($activity->completionexpected,get_string('strftimedate','langconfig'));
    } else {
        $datetext='';
    }

    // Some names (labels) come URL-encoded and can be very long, so shorten them
    $displayname = format_string($activity->name, true, array('context' => $activity->context));

    if ($csv) {
        print $sep.csv_quote($displayname);
    } else {
        $shortenedname = shorten_text($displayname);
        print '<th scope="col" class="completion-header '.$datepassedclass.'">'.
            '<div class="modicon">'.
            $OUTPUT->image_icon('icon', get_string('modulename', $activity->modname), $activity->modname) .
            '</div>'.
            '<a href="'.$CFG->wwwroot.'/mod/'.$activity->modname.
            '/view.php?id='.$activity->id.'" title="' . s($displayname) . '">'.
            '<div class="rotated-text-container"><span class="rotated-text">'.$shortenedname.'</span></div>'.
            '</a>';
        if ($activity->completionexpected) {
            print '<div class="completion-expected"><span>'.$datetext.'</span></div>';
        }
        print '</th>';
    }
    $formattedactivities[$activity->id] = (object)array(
        'datepassedclass' => $datepassedclass,
        'displayname' => $displayname,
    );
}

if ($csv) {
    print $line;
} else {
    print '</tr></thead><tbody>';
}

// Row for each user
foreach($eloprogress as $user) {
    // User name
    if ($csv) {
        print csv_quote(fullname($user));
        foreach ($extrafields as $field) {
            //Nhien start
            if($field!="phone2"){
               echo $sep.csv_quote($user->{$field}); 
            }
            //Nhien end  
        }
    } else {
        print '<tr><th scope="row"><a href="'.$CFG->wwwroot.'/user/view.php?id='.
            $user->id.'&amp;course='.$course->id.'">'.fullname($user).'</a></th>';
        foreach ($extrafields as $field) {
            //Nhien start             <a href="mailto:
            if($field=="email"){
                echo '<td><a href="mailto:'. s($user->{$field}) .'">'.s($user->{$field}).'</a></td>';
            }
            else{
             echo '<td>' . s($user->{$field}) . '</td>';
            }
            //Nhien end
        }
    }

    // Eloprogress for each activity
    foreach($activities as $activity) {
        if ($activity->modname == 'label') {
            continue;
        }//Nhien elo 15_01_2019 fix l???i label kh??ng c???n ho??n th??nh ho???t ?????ng
        if ($activity->visible == 0) {
            continue;
        }
        /*$ischamdiem = get_eloprogress_gradetype($course, $activity);
        if (!$ischamdiem) {
            continue;
        }*/
        // Get eloprogress information and state
        if (array_key_exists($activity->id, $user->progress)) {
            $thiseloprogress = $user->progress[$activity->id];
            $state = $thiseloprogress->completionstate;
            $overrideby = $thiseloprogress->overrideby;
            $date = userdate($thiseloprogress->timemodified);
        } else {
            $state = COMPLETION_INCOMPLETE;
            $overrideby = 0;
            $date = '';
        }

        // Work out how it corresponds to an icon
        switch($state) {
            case COMPLETION_INCOMPLETE :
                $completiontype = 'n'.($overrideby ? '-override' : '');
                break;
            case COMPLETION_COMPLETE :
                $completiontype = 'y'.($overrideby ? '-override' : '');
                break;
            case COMPLETION_COMPLETE_PASS :
                $completiontype = 'pass';
                break;
            case COMPLETION_COMPLETE_FAIL :
                $completiontype = 'fail';
                break;
        }
        $completiontrackingstring = $activity->completion == COMPLETION_TRACKING_AUTOMATIC ? 'auto' : 'manual';
        $completionicon = 'completion-' . $completiontrackingstring. '-' . $completiontype;

        if ($overrideby) {
            $overridebyuser = \core_user::get_user($overrideby, '*', MUST_EXIST);
            $describe = get_string('completion-' . $completiontype, 'completion', fullname($overridebyuser));
        } else {
            $describe = get_string('completion-' . $completiontype, 'completion');
        }
        $a=new StdClass;
        $a->state=$describe;
        $a->date=$date;
        $a->user=fullname($user);
        $a->activity = $formattedactivities[$activity->id]->displayname;
        $fulldescribe=get_string('eloprogress-title','report_eloprogress',$a);
        //Nhien start    
        if($csv) {
            if($describe == "Not completed" or $describe == "Ch??a ho??n th??nh"){
                $describe = '';
                print $sep.csv_quote($describe);   
                }
            else {
                $describe = 'X';
                print $sep.csv_quote($describe);  
            }
            }
        //Nhien end
        else {
            $celltext = $OUTPUT->pix_icon('i/' . $completionicon, s($fulldescribe));
            if (has_capability('moodle/course:overridecompletion', $context) &&
                    $state != COMPLETION_COMPLETE_PASS && $state != COMPLETION_COMPLETE_FAIL) {
                $newstate = ($state == COMPLETION_COMPLETE) ? COMPLETION_INCOMPLETE : COMPLETION_COMPLETE;
                $changecompl = $user->id . '-' . $activity->id . '-' . $newstate;
                $url = new moodle_url($PAGE->url, ['sesskey' => sesskey()]);
                $celltext = html_writer::link($url, $celltext, array('class' => 'changecompl', 'data-changecompl' => $changecompl,
                                                                     'data-activityname' => $a->activity,
                                                                     'data-userfullname' => $a->user,
                                                                     'data-completiontracking' => $completiontrackingstring,
                                                                     'aria-role' => 'button'));
            }
            print '<td class="completion-eloprogresscell '.$formattedactivities[$activity->id]->datepassedclass.'">'.
                $celltext . '</td>';
        }
    }

    if ($csv) {
        print $line;
    } else {
        print '</tr>';
    }
}

if ($csv) {
    exit;
}
print '</tbody></table>';
print '</div>';
print $pagingbar;
print $atoz_bar;
print '<ul class="eloprogress-actions"><li><a href="index.php?course='.$course->id.
    '&amp;format=csv">'.get_string('csvdownload','completion').'</a></li>
    <li><a href="index.php?course='.$course->id.'&amp;format=excelcsv">'.
    get_string('excelcsvdownload','completion').'</a></li></ul>';

echo $OUTPUT->footer();

