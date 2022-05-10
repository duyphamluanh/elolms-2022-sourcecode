    <?php

//  Display the course home page.

    require_once('../config.php');
    require_once('lib.php');
    require_once($CFG->libdir.'/completionlib.php');
    
    function elo_get_assign_dates_text($assignid)
    {
        global $DB;
        $params = array('id' => $assignid);
        $assigninstance = $DB->get_record('assign', $params, '*');
        if(!$assigninstance)
            return;        
        $result->starttimetext = $assigninstance->allowsubmissionsfromdate;
        $result->endtimetext = $assigninstance->duedate;
        return $result;
    }
    function elo_get_quiz_dates_text($quizid)
    {
        global $DB;
        $params = array('id' => $quizid);
        $quizinstance = $DB->get_record('quiz', $params, '*');
        if(!$quizinstance)
            return;        
        $result->starttimetext = $quizinstance->timeopen;
        $result->endtimetext = $quizinstance->timeclose;
        return $result;
    }    
   
    function elo_course_activitive_completion_statistic($course,$userid = null) {
        global $USER,$OUTPUT,$CFG;
        // Make sure we continue with a valid userid.
        if (empty($userid)) {
            $userid = $USER->id;
        }

        $elo_act_col = get_string('elo_activitive_col','course');
        $elo_start_col= get_string('elo_activitive_startdate_col','course');
        $elo_end_col= get_string('elo_activitive_enddate_col','course');
        $elo_completion_col= get_string('elo_activitive_completion_col','course');
                                

        $completion = new \completion_info($course);
        $progresses = $completion->get_progress_all('(u.id = ' . $userid . ')' );
        // First, let's make sure completion is enabled.
        $activities = $completion->get_activities();
        $elo_course_activitive_html = 
            '<script src="elo_w3.js"></script>	
            <script type="text/javascript">
                window.onload=function(){	
                        document.getElementById("elo_default_th").click();
            };
            </script>
            <div id = "elo_course_activitive_div">
            <table id = "elo_course_activitive_table_id" class="elo_course_activitive_table" style="overflow-y:scroll">
              <thead>
                <tr>
                  <th id = "elo_default_th" onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(1)\')" style="cursor:pointer">
                        ' . $elo_act_col . ' <i class="fa fa-sort" style="font-size:20px;"></i></th>
                  <th onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(2)\',1)" style="cursor:pointer">
                        ' . $elo_start_col . ' <i class="fa fa-sort" style="font-size:20px;"></i></th>
                  <th onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(3)\',1)" style="cursor:pointer">
                        ' . $elo_end_col . ' <i class="fa fa-sort" style="font-size:20px;"></i></th>
                  <th  onclick="w3.sortHTML(\'#elo_course_activitive_table_id\', \'.item\', \'td:nth-child(4)\')" style="cursor:pointer">
                        ' . $elo_completion_col . ' <i class="fa fa-sort" style="font-size:20px;"></i></th>
                </tr>
              </thead>
              <tbody>';
        
        foreach($activities as $activity) {
            $datepassed = $activity->completionexpected && $activity->completionexpected <= time();
            $datepassedclass = $datepassed ? 'completion-expired' : '';
            
            $startYYMMDDHHIISS = date('Y-m-d H:i:s', $activity->added);
            $startdatetext = userdate($activity->added,get_string('strftimedate','langconfig'));
            if ($activity->completionexpected) {
                $enddatetext=userdate($activity->completionexpected,get_string('strftimedate','langconfig'));
                $endYYMMDDHHIISS = date('Y-m-d H:i:s', $activity->completionexpected);
            }else if ($course->enddate){
                $enddatetext=userdate($course->enddate,get_string('strftimedate','langconfig'));
                $endYYMMDDHHIISS = date('Y-m-d H:i:s', $course->enddate);
            }   
            
            if($activity->modname == 'quiz'){
                $assigntimetext = elo_get_quiz_dates_text($activity->instance);
                if($assigntimetext->starttimetext){
                    $startdatetext = userdate($assigntimetext->starttimetext,get_string('strftimedate','langconfig'));
                    $startYYMMDDHHIISS = date('Y-m-d H:i:s',$assigntimetext->starttimetext);
                }
                if($assigntimetext->endtimetext){
                    $enddatetext = userdate($assigntimetext->endtimetext,get_string('strftimedate','langconfig'));
                    $endYYMMDDHHIISS = date('Y-m-d H:i:s',$assigntimetext->endtimetext);
                }
            }
            if($activity->modname == 'assign'){
                $assigntimetext = elo_get_assign_dates_text($activity->instance);
                if($assigntimetext->starttimetext){
                    $startdatetext = userdate($assigntimetext->starttimetext,get_string('strftimedate','langconfig'));
                    $startYYMMDDHHIISS = date('Y-m-d H:i:s', $assigntimetext->starttimetext);
                }
                if($assigntimetext->endtimetext){
                    $enddatetext = userdate($assigntimetext->endtimetext,get_string('strftimedate','langconfig'));
                    $endYYMMDDHHIISS = date('Y-m-d H:i:s',$assigntimetext->endtimetext);
                }
            }
            
            // Some names (labels) come URL-encoded and can be very long, so shorten them

            $displayname = format_string($activity->name, true, array('context' => $activity->context));
            $shortenedname = shorten_text($displayname);
            $elo_course_activitive_html .= '<tr class="item">';

                      
            $elo_course_activitive_html .= '<td>' .
                $OUTPUT->image_icon('icon', get_string('modulename', $activity->modname), $activity->modname) .
                '<a href="'.$CFG->wwwroot.'/mod/'.$activity->modname.
                '/view.php?id='.$activity->id.'" title="' . s($displayname) . '">'.
                '<span class="rotated-text">'.$displayname.'</span>'.
                '</a></td>';
            if(!$startdatetext){
                $startdatetext = 'N/A';
            }
            if(!$enddatetext){
                $enddatetext = 'N/A';
            }            
            $elo_course_activitive_html .= '<td><span style = "display:none">'.$startYYMMDDHHIISS.'</span><span class="elo-startdate">'.$startdatetext.'</span></td>';
            $elo_course_activitive_html .= '<td><span style = "display:none">'.$endYYMMDDHHIISS.'</span><span class="elo-enddate">'.$enddatetext.'</span></td>';
            

            $formattedactivities[$activity->id] = (object)array(
                'datepassedclass' => $datepassedclass,
                'displayname' => $displayname,
            );
            
            // Get progress information and state
            if (array_key_exists($activity->id, $progresses[$USER->id]->progress)) {
                $thisprogress = $progresses[$USER->id]->progress[$activity->id];
                $state = $thisprogress->completionstate;
                $overrideby = $thisprogress->overrideby;
                $date = userdate($thisprogress->timemodified);
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
            $a->user=fullname($userid);
            $a->activity = $formattedactivities[$activity->id]->displayname;
            $fulldescribe=get_string('progress-title','completion',$a);
            $celltext = $OUTPUT->pix_icon('i/' . $completionicon, s($fulldescribe));
            $elo_course_activitive_html .= '<td><span style = "display:none">'.$completiontype.'</span><div>'.
                $celltext . '</div></td>';
            
            $elo_course_activitive_html .= '</tr>';
            
        }
        $elo_course_activitive_html.= '</tbody></table></div>';
        return $elo_course_activitive_html;
    }
    function elo_export_course_completed_html($course = null,$userid = null) {
        //$completedhtml;
        global $COURSE;
        if ($course == null)
            $elocompletornotactivitives = elo_course_activitive_completion_statistic($COURSE,$userid);
        else 
            $elocompletornotactivitives = elo_course_activitive_completion_statistic($course,$userid);

        return $elocompletornotactivitives;
    }
 


    $id          = optional_param('id', 0, PARAM_INT);
    $name        = optional_param('name', '', PARAM_TEXT);
    $edit        = optional_param('edit', -1, PARAM_BOOL);
    $hide        = optional_param('hide', 0, PARAM_INT);
    $show        = optional_param('show', 0, PARAM_INT);
    $idnumber    = optional_param('idnumber', '', PARAM_RAW);
    $sectionid   = optional_param('sectionid', 0, PARAM_INT);
    $section     = optional_param('section', 0, PARAM_INT);
    $move        = optional_param('move', 0, PARAM_INT);
    $marker      = optional_param('marker',-1 , PARAM_INT);
    $switchrole  = optional_param('switchrole',-1, PARAM_INT); // Deprecated, use course/switchrole.php instead.
    $return      = optional_param('return', 0, PARAM_LOCALURL);

    $params = array();
    if (!empty($name)) {
        $params = array('shortname' => $name);
    } else if (!empty($idnumber)) {
        $params = array('idnumber' => $idnumber);
    } else if (!empty($id)) {
        $params = array('id' => $id);
    }else {
        print_error('unspecifycourseid', 'error');
    }

    $course = $DB->get_record('course', $params, '*', MUST_EXIST);
    
    $urlparams = array('id' => $course->id);

    // Sectionid should get priority over section number
    if ($sectionid) {
        $section = $DB->get_field('course_sections', 'section', array('id' => $sectionid, 'course' => $course->id), MUST_EXIST);
    }
    if ($section) {
        $urlparams['section'] = $section;
    }

    $PAGE->set_url('/course/view.php', $urlparams); // Defined here to avoid notices on errors etc

    // Prevent caching of this page to stop confusion when changing page after making AJAX changes
    $PAGE->set_cacheable(false);

    context_helper::preload_course($course->id);
    $context = context_course::instance($course->id, MUST_EXIST);

    // Remove any switched roles before checking login
    if ($switchrole == 0 && confirm_sesskey()) {
        role_switch($switchrole, $context);
    }

    require_login($course);

    // Switchrole - sanity check in cost-order...
    $reset_user_allowed_editing = false;
    if ($switchrole > 0 && confirm_sesskey() &&
        has_capability('moodle/role:switchroles', $context)) {
        // is this role assignable in this context?
        // inquiring minds want to know...
        $aroles = get_switchable_roles($context);
        if (is_array($aroles) && isset($aroles[$switchrole])) {
            role_switch($switchrole, $context);
            // Double check that this role is allowed here
            require_login($course);
        }
        // reset course page state - this prevents some weird problems ;-)
        $USER->activitycopy = false;
        $USER->activitycopycourse = NULL;
        unset($USER->activitycopyname);
        unset($SESSION->modform);
        $USER->editing = 0;
        $reset_user_allowed_editing = true;
    }

    //If course is hosted on an external server, redirect to corresponding
    //url with appropriate authentication attached as parameter
    if (file_exists($CFG->dirroot .'/course/externservercourse.php')) {
        include $CFG->dirroot .'/course/externservercourse.php';
        if (function_exists('extern_server_course')) {
            if ($extern_url = extern_server_course($course)) {
                redirect($extern_url);
            }
        }
    }


    require_once($CFG->dirroot.'/calendar/lib.php');    /// This is after login because it needs $USER

    // Must set layout before gettting section info. See MDL-47555.
    $PAGE->set_pagelayout('course');

    if ($section and $section > 0) {

        // Get section details and check it exists.
        $modinfo = get_fast_modinfo($course);
        $coursesections = $modinfo->get_section_info($section, MUST_EXIST);

        // Check user is allowed to see it.
        if (!$coursesections->uservisible) {
            // Check if coursesection has conditions affecting availability and if
            // so, output availability info.
            if ($coursesections->visible && $coursesections->availableinfo) {
                $sectionname     = get_section_name($course, $coursesections);
                $message = get_string('notavailablecourse', '', $sectionname);
                redirect(course_get_url($course), $message, null, \core\output\notification::NOTIFY_ERROR);
            } else {
                // Note: We actually already know they don't have this capability
                // or uservisible would have been true; this is just to get the
                // correct error message shown.
                require_capability('moodle/course:viewhiddensections', $context);
            }
        }
    }

    // Fix course format if it is no longer installed
    $course->format = course_get_format($course)->get_format();

    $PAGE->set_pagetype('course-view-' . $course->format);
    $PAGE->set_other_editing_capability('moodle/course:update');
    $PAGE->set_other_editing_capability('moodle/course:manageactivities');
    $PAGE->set_other_editing_capability('moodle/course:activityvisibility');
    if (course_format_uses_sections($course->format)) {
        $PAGE->set_other_editing_capability('moodle/course:sectionvisibility');
        $PAGE->set_other_editing_capability('moodle/course:movesections');
    }

    // Preload course format renderer before output starts.
    // This is a little hacky but necessary since
    // format.php is not included until after output starts
    if (file_exists($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php')) {
        require_once($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php');
        if (class_exists('format_'.$course->format.'_renderer')) {
            // call get_renderer only if renderer is defined in format plugin
            // otherwise an exception would be thrown
            $PAGE->get_renderer('format_'. $course->format);
        }
    }

    if ($reset_user_allowed_editing) {
        // ugly hack
        unset($PAGE->_user_allowed_editing);
    }

    if (!isset($USER->editing)) {
        $USER->editing = 0;
    }
    if ($PAGE->user_allowed_editing()) {
        if (($edit == 1) and confirm_sesskey()) {
            $USER->editing = 1;
            // Redirect to site root if Editing is toggled on frontpage
            if ($course->id == SITEID) {
                redirect($CFG->wwwroot .'/?redirect=0');
            } else if (!empty($return)) {
                redirect($CFG->wwwroot . $return);
            } else {
                $url = new moodle_url($PAGE->url, array('notifyeditingon' => 1));
                redirect($url);
            }
        } else if (($edit == 0) and confirm_sesskey()) {
            $USER->editing = 0;
            if(!empty($USER->activitycopy) && $USER->activitycopycourse == $course->id) {
                $USER->activitycopy       = false;
                $USER->activitycopycourse = NULL;
            }
            // Redirect to site root if Editing is toggled on frontpage
            if ($course->id == SITEID) {
                redirect($CFG->wwwroot .'/?redirect=0');
            } else if (!empty($return)) {
                redirect($CFG->wwwroot . $return);
            } else {
                redirect($PAGE->url);
            }
        }

        if (has_capability('moodle/course:sectionvisibility', $context)) {
            if ($hide && confirm_sesskey()) {
                set_section_visible($course->id, $hide, '0');
                redirect($PAGE->url);
            }

            if ($show && confirm_sesskey()) {
                set_section_visible($course->id, $show, '1');
                redirect($PAGE->url);
            }
        }

        if (!empty($section) && !empty($move) &&
                has_capability('moodle/course:movesections', $context) && confirm_sesskey()) {
            $destsection = $section + $move;
            if (move_section_to($course, $section, $destsection)) {
                if ($course->id == SITEID) {
                    redirect($CFG->wwwroot . '/?redirect=0');
                } else {
                    redirect(course_get_url($course));
                }
            } else {
                echo $OUTPUT->notification('An error occurred while moving a section');
            }
        }
    } else {
        $USER->editing = 0;
    }

    $SESSION->fromdiscussion = $PAGE->url->out(false);


    if ($course->id == SITEID) {
        // This course is not a real course.
        redirect($CFG->wwwroot .'/');
    }

    $completion = new completion_info($course);
    if ($completion->is_enabled()) {
        $PAGE->requires->string_for_js('completion-alt-manual-y', 'completion');
        $PAGE->requires->string_for_js('completion-alt-manual-n', 'completion');

        $PAGE->requires->js_init_call('M.core_completion.init');
    }

    // We are currently keeping the button here from 1.x to help new teachers figure out
    // what to do, even though the link also appears in the course admin block.  It also
    // means you can back out of a situation where you removed the admin block. :)
    if ($PAGE->user_allowed_editing()) {
        $buttons = $OUTPUT->edit_button($PAGE->url);
        $PAGE->set_button($buttons);
    }

    // If viewing a section, make the title more specific
    if ($section and $section > 0 and course_format_uses_sections($course->format)) {
        $sectionname = get_string('sectionname', "format_$course->format");
        $sectiontitle = get_section_name($course, $section);
        $PAGE->set_title(get_string('coursesectiontitle', 'moodle', array('course' => $course->fullname, 'sectiontitle' => $sectiontitle, 'sectionname' => $sectionname)));
    } else {
        $PAGE->set_title(get_string('coursetitle', 'moodle', array('course' => $course->fullname)));
    }

    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    
    //Nhien create tab content
    $summary = get_string('tongquan', 'course');
    $content = get_string('noidung', 'course');
    $learningplanweek = get_string('kehoachhoctaptheotuan', 'course');
    $learningschedule = get_string('lichtrinhhoctap', 'course');
    $nameobjectfor = get_string('tenmonhocho', 'course');
    $level = get_string('bacdaotao', 'course');
    $duration = get_string('thoiluong', 'course');
    $conditionfirst = get_string('dieukientienquyet', 'course');
    $descriptionobject = get_string('motamonhoc', 'course');
    $downloadcontentobject=get_string('tainoidungmonhoc', 'course');
    $seeforum=get_string('xemdiendan', 'course');
    
//----------------------------------------------start ul 
    print '<div class="block-myoverview" data-region="myoverview">';
    print '<ul class="nav nav-tabs mb-3">';
    //--------------------------------
    print '<li class="nav-item" role="presentation">';
    print '<a class="nav-link " aria-controls="summary" title="Tổng quan" href="#summary" data-toggle="tab" role="tab"><h4>'.$summary.'</h4></a>';
    print '</li">';
    //--------------------------------
    print '<li class="nav-item" role="presentation">';
    print '<a class="nav-link active" aria-controls="content" title="Nội dung" href="#content" data-toggle="tab" role="tab"><h4>'.$content.'</h4></a>';
    print '</li">';
    //--------------------------------
    print '<li class="nav-item" role="presentation">';
    print '<a class="nav-link" aria-controls="learningschedule"  title="Lịch trình học tập" href="#learningschedule" data-toggle="tab" role="tab"><h4>'.$learningschedule.'</h4></a>';
    print '</li">';
    print '</ul>';
    //------------------------------------------
    print '<div class="tab-content">'; // open div
    //*******************************TAB TONG QUAN MON HOC**********************
    if (empty($course->educationlevel && $course->time && $course->firstrequired && $course->file)) {
        $course->educationlevel =$course->file=$course->firstrequired= $course->time ="Không có dữ liệu";
    }
    $elocoursesummaryhtml = '
    <div class="tab-pane" id="summary">
    <div class="tab-pane" id="elosummary">
    <ul class="mota-monhoc">
    
    <li><span>' . $nameobjectfor . '</span>' .$course->fullname. '</li>
    <li><span>'.$level.'</span> '.$course->educationlevel.'</li>
    <li><span>'.$duration.'</span> '.$course->time.'</li>
    <li><span>'.$conditionfirst.'</span> '.$course->firstrequired.'</li>
    <li><span>'.$descriptionobject.'</span><br>'.$course->summary.'</li>';
    if($course->file) {
        $elocoursesummaryhtml .= '
            <li><span>'.$downloadcontentobject.'</span>&nbsp;&nbsp;<a download="'.$course->file.'"'
            . 'href="'.$course->file.'">'
            . '<i style="font-size:20px" class="fa fa-cloud-download"></i></a></li>';
    }
    $elocoursesummaryhtml .= '<ul>';
    $elocoursesummaryhtml .= '</div></div>'; 
    print $elocoursesummaryhtml;

//******************************Nhien Tab NOI DUNG MON HOC******************
    print '<div role="tabpanel" class="tab-pane active fade show" id="content">';
    echo html_writer::start_tag('div', array('class'=>'course-content'));

    // make sure that section 0 exists (this function will create one if it is missing)
    course_create_sections_if_missing($course, 0);

    // get information about course modules and existing module types
    // format.php in course formats may rely on presence of these variables
    $modinfo = get_fast_modinfo($course);
    $modnames = get_module_types_names();
    $modnamesplural = get_module_types_names(true);
    $modnamesused = $modinfo->get_used_module_names();
    $mods = $modinfo->get_cms();
    $sections = $modinfo->get_section_info_all();

    // CAUTION, hacky fundamental variable defintion to follow!
    // Note that because of the way course fromats are constructed though
    // inclusion we pass parameters around this way..
    $displaysection = $section;

    // Include the actual course format.
    require($CFG->dirroot .'/course/format/'. $course->format .'/format.php');
    // Content wrapper end.

    echo html_writer::end_tag('div');
    print '</div>'; 
    
//*******************************NHien Tab lich trinh hoc tap*******************

require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

$categoryid = optional_param('category', null, PARAM_INT);
$time = optional_param('time', 0, PARAM_INT);
$view = optional_param('view', 'month', PARAM_ALPHA);

$url = new moodle_url('/calendar/view.php');

if (empty($time)) {
    $time = time();
}

$url->param('course', $course->id);


if ($categoryid) {
    $url->param('categoryid', $categoryid);
}

if ($view !== 'upcoming') {
    $time = usergetmidnight($time);
    $url->param('view', $view);
}

$calendar = calendar_information::create($time, $course->id, $categoryid);

$renderer = $PAGE->get_renderer('core_calendar');
//$calendar->add_sidecalendar_blocks($renderer, true, $view);

list($data, $template) = calendar_get_view($calendar, $view);
list($dataupcoming, $templateupcoming) = calendar_get_view($calendar, 'upcoming');

$elocalendarhtml .= $renderer->start_layout();
$elocalendarhtml .= html_writer::start_tag('div', array('class'=>'eloheightcontainer'));
$elocalendarhtml .= $renderer->render_from_template($template, $data);
list($data, $template) = calendar_get_footer_options($calendar);
$elocalendarhtml .= $renderer->render_from_template($template, $data);
$elocalendarhtml .= html_writer::end_tag('div');

$elocalendarhtml .= '<div id = "elo_upcomming_calendar_block"> ';
$elocalendarhtml .= $renderer->render_from_template($templateupcoming, $dataupcoming);
$elocalendarhtml .= $renderer->render_from_template($template, $data);
$elocalendarhtml .= '</div> ';
$elocalendarhtml .= $renderer->complete_layout();

    print '<div class="tab-pane" id="learningschedule">';
    print $elocalendarhtml;

    $elocomplettionhtlm = elo_export_course_completed_html($course);
    
    print $elocomplettionhtlm;
    
//    print $course->studyplan;
//    if ($course->linkstudyplan){
//        print '<br />';
//        print '<a href="' . $course->linkstudyplan . '">Xem lịch trình</a>';
//    }
    print '</div>';   
    print '</div>'; 
    print '</div>'; //End div myoverview

//***************************Nhien create tab content end*****************************************

    if ($completion->is_enabled()) {
        // This value tracks whether there has been a dynamic change to the page.
        // It is used so that if a user does this - (a) set some tickmarks, (b)
        // go to another page, (c) clicks Back button - the page will
        // automatically reload. Otherwise it would start with the wrong tick
        // values.
        echo html_writer::start_tag('form', array('action'=>'.', 'method'=>'get'));
        echo html_writer::start_tag('div');
        echo html_writer::empty_tag('input', array('type'=>'hidden', 'id'=>'completion_dynamic_change', 'name'=>'completion_dynamic_change', 'value'=>'0'));
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('form');
    }
    
    // Course wrapper start.
    echo html_writer::start_tag('div', array('class'=>'course-content'));

    // make sure that section 0 exists (this function will create one if it is missing)
    course_create_sections_if_missing($course, 0);
/*
    // get information about course modules and existing module types
    // format.php in course formats may rely on presence of these variables
    $modinfo = get_fast_modinfo($course);
    $modnames = get_module_types_names();
    $modnamesplural = get_module_types_names(true);
    $modnamesused = $modinfo->get_used_module_names();
    $mods = $modinfo->get_cms();
    $sections = $modinfo->get_section_info_all();

    // CAUTION, hacky fundamental variable defintion to follow!
    // Note that because of the way course fromats are constructed though
    // inclusion we pass parameters around this way..
    $displaysection = $section;

    // Include the actual course format.
    require($CFG->dirroot .'/course/format/'. $course->format .'/format.php');
    // Content wrapper end.

    echo html_writer::end_tag('div');
*/
    // Trigger course viewed event.
    // We don't trust $context here. Course format inclusion above executes in the global space. We can't assume
    // anything after that point.
    course_view(context_course::instance($course->id), $section);

    // Include course AJAX
    include_course_ajax($course, $modnamesused);

    echo $OUTPUT->footer();
