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
 * Elo Reminder users block.
 *
 * @package    block_elo_reminder_users
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_elo_reminder_users\fetcher;

/**
 * This block needs to be reworked.
 * The new roles system does away with the concepts of rigid student and
 * teacher roles.
 */
class block_elo_reminder_users extends block_base {
    function init() {
        global $COURSE;
        $this->title = get_string('pluginname','block_elo_reminder_users');
        // $sitelevel = $this->page->course->id == SITEID || $this->page->context->contextlevel < CONTEXT_COURSE;
        // if($sitelevel){
        //     $this->title .= ' - '.$COURSE->fullname;
        // }
        $this->title = '<span id="viewberu">'.$this->title.'</span>';
    }

    function has_config() {
        return true;
    }

    //Nhien elo 18_09_2019
    /**
     * Format a date/time (seconds) as weeks, days, hours etc as needed
     *
     * Given an amount of time in seconds, returns string
     * formatted nicely as years, days, hours etc as needed
     *
     * @package core
     * @category time
     * @uses MINSECS
     * @uses HOURSECS
     * @uses DAYSECS
     * @uses YEARSECS
     * @param int $totalsecs Time in seconds
     * @param stdClass $str Should be a time object
     * @return string A nicely formatted date/time string
     */
    function minutes_to_time($minutes,$role) {

        $seconds = $minutes * 60;
        $string = "";


        // $years = intval(intval($minutes) / 86400) % 365;
        $days = intval(intval($seconds) / (3600*24));
        $hours = (intval($seconds) / 3600) % 24;
        $minutes = (intval($seconds) / 60) % 60;
        // $seconds = (intval($seconds)) % 60;

        // if($years> 0){
        //     $plural = ($minutes > 1 ? 's' : '');
        //     $string .= get_string('elonminute'.$plural, 'block_elo_reminder_users', $years);
        // }
        if($days> 0){
            $plural = ($days > 1 ? 's' : '');
            $string .= ' '.get_string('eloday'.$plural, 'block_elo_reminder_users', $days);
        }
        if($hours > 0){
            $plural = ($hours > 1 ? 's' : '');
            $string .= ' '.get_string('elohour'.$plural, 'block_elo_reminder_users', $hours);
        }
        if($minutes > 0){
            $plural = ($minutes > 1 ? 's' : '');
            $string .= ' '.get_string('elominute'.$plural, 'block_elo_reminder_users', $minutes);
        }
        // if ($seconds > 0){
        //     $string .= "$seconds seconds";
        // }
        return get_string('elo'.$role,'block_elo_reminder_users') . ' ' . get_string('eloperiod','block_elo_reminder_users',$string);
    }
    //End Nhien elo 18_09_2019

    function get_content() {
        global $USER, $CFG, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        //Nhien elo 14_10_2019 Only for manager - admin - moodle 35
//        $id = optional_param('id','1', PARAM_INT);
//        if (!$course = $DB->get_record('course', array('id'=>$id))) {
//            return $this->content;
//        }
//        $contextper = context_course::instance($course->id);
//        $permissionaccesslink = has_capability('moodle/role:switchroles', $contextper);
//
//        if ($permissionaccesslink == false) {
//            return $this->content;
//        }
        //End Nhien elo 14_10_2019
        
        //Nhien elo 25_10_2019 Only for manager - admin - moodle 37
        $hasteacherrole = has_capability('moodle/course:viewhiddenactivities', $this->context);
        if ($hasteacherrole == false) {
            return $this->content;
        }
        //End Nhien elo 25_10_2019

        //Nhien elo 17_09_2019
        $timetoshowteachers = 60 * 24 * 2; //default 2 days
        if (isset($CFG->block_elo_reminder_users_timetosee)) {
            $timetoshowteachers = $CFG->block_elo_reminder_users_timetosee * 60;
        }
        $now = time();
        //End Nhien elo 17_09_2019

        $timetoshowstudents = 60 * 24 * 14; //default 14 days_ 2 weeks
        if (isset($CFG->block_elo_reminder_users_timetoseestudent)) {
            $timetoshowstudents = $CFG->block_elo_reminder_users_timetoseestudent * 60;
        }

        //Calculate if we are in separate groups
        $isseparategroups = ($this->page->course->groupmode == SEPARATEGROUPS
                             && $this->page->course->groupmodeforce
                             && !has_capability('moodle/site:accessallgroups', $this->page->context));

        //Get the user current group
        $currentgroup = $isseparategroups ? groups_get_course_group($this->page->course) : NULL;

        $sitelevel = $this->page->course->id == SITEID || $this->page->context->contextlevel < CONTEXT_COURSE;

        //get params
        $berutype = optional_param('berutype', '', PARAM_ALPHANUMEXT);
        $beruifirst = optional_param('beruifirst', '', PARAM_ALPHANUMEXT);
        $beruilast = optional_param('beruilast', '', PARAM_ALPHANUMEXT);
        $berusort = optional_param('berusort', '', PARAM_ALPHANUMEXT);
        $tdir = optional_param('tdir', '', PARAM_INT);

        //set params
        $para = ($sitelevel ? '' : 'view.php?id='.$this->page->course->id);
        $urlfilter = $PAGE->url.$para.($para != '' ? '&' : '?');
        $urlfilterall = $PAGE->url.$para;
        $paragroup = '';
        if($berutype != ''){
            $paragroup .= 'berutype='.$berutype;
        }
        if($beruifirst != ''){
            $paragroup .= ($paragroup != '' ? '&' : ''). 'beruifirst='.$beruifirst;
        }
        if($beruilast != ''){
            $paragroup .= ($paragroup != '' ? '&' : ''). 'beruilast='.$beruilast;
        }
        if($berusort != ''){
            $paragroup .= ($paragroup != '' ? '&' : '').'berusort='.$berusort;
        }
        if($tdir != ''){
            $paragroup .= ($paragroup != '' ? '&' : '').'tdir='.$tdir;
        }
        $pageurl = $urlfilter.$paragroup;

        //get list
        $offlineusers = new fetcher($currentgroup, $now, $timetoshowteachers, $timetoshowstudents, $this->page->context,
                $sitelevel, $this->page->course->id,$berutype);

        //Calculate minutes
        $minutesteacher  = floor($timetoshowteachers/60);
        $minutesstudent  = floor($timetoshowstudents/60);
        $periodminutesstudent = $this->minutes_to_time($minutesstudent,'student');
        $periodminutesteacher = $this->minutes_to_time($minutesteacher,'teacher');

        // Count users.
        $usercount = $offlineusers->count_users();
        if ($usercount === 0) {
            $usercount = get_string('elonouser', 'block_elo_reminder_users');
        } else if ($usercount === 1) {
            $usercount = get_string('elonumuser', 'block_elo_reminder_users', $usercount);
        } else {
            $usercount = get_string('elonumusers', 'block_elo_reminder_users', $usercount);
        }

        $this->content->text = '<div class="info">'.$usercount.' ('.$periodminutesteacher.' - '.$periodminutesstudent.')</div>';

        // Verify if we can see the list of users, if not just print number of users
//        if (!has_capability('block/elo_reminder_users:viewlist', $this->page->context)) {
//            return $this->content;
//        }

        //important for flexible_table
        require_once('tablelib.php');

        //html
        $html = '';
        $userlimit = 10; //50; // We'll just take the most recent 50 maximum.
        // $perpage = round($usercount/$userlimit);

        // Define table columns.
        $columns = array();
        $headers = array();
        $columns[] = 'select';
        $headers[] = get_string('select');
        // $columns[] = 'checkbox';
        // $headers[] = html_writer::empty_tag('input', array('type' => 'checkbox', 'id' => 'cb_sendmail' ,'name' => 'cb_sendmail[]'));
        $columns[] = 'fullname';
        $headers[] = get_string('fullname', 'block_elo_reminder_users');
        $columns[] = 'email';
        $headers[] = get_string('email', 'block_elo_reminder_users');
        $columns[] = 'shortname';
        $headers[] = get_string('role', 'block_elo_reminder_users');

        if($sitelevel){
            $columns[] = 'lastaccess';
            $headers[] = get_string('lastsiteaccess', 'block_elo_reminder_users');
        }
        else{
            $columns[] = 'timeaccess';
            $headers[] = get_string('lastcourseaccess', 'block_elo_reminder_users');
        }

        $columns[] = 'elotimecreated';
        $headers[] = get_string('lastsentmail', 'block_elo_reminder_users');
        $columns[] = 'elocountmail';
        $headers[] = get_string('elocountmail', 'block_elo_reminder_users');
        $columns[] = 'action';
        $headers[] = get_string('action', 'block_elo_reminder_users');

        $table = new \elo_flexible_table('block-elo-reminder-users');

        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->define_baseurl($pageurl.'#viewberu');

        // $table->sortable(true,'lastaccess', SORT_ASC);
        $table->sortable(true);
        $table->collapsible(true);

        $table->no_sorting('action');
        $table->no_sorting('select');
        $table->pageable(true);

        // $table->column_class('fullname', '');
        // foreach ($extrafields as $field) {
        //     $table->column_suppress($field);
        // }
        // $table->set_attribute('id', '');
        // $table->set_attribute('cellspacing', '0');
        // $table->set_attribute('class', '');
        $table->set_control_variables(array(
                    TABLE_VAR_SORT    => 'berusort',
                    TABLE_VAR_IFIRST  => 'beruifirst',
                    TABLE_VAR_ILAST   => 'beruilast',
                    TABLE_VAR_PAGE    => 'berupage'
                    ));


        $table->setup();
        // $table->initialbars(true);//tool bar
        if ($berusort != '' && $table->get_sql_sort()) {
            $sort = $table->get_sql_sort() ?? '';
        }
        else{
            $sort = '';
        }

        $showall = false;
        if ($showall) {
            $userlimit = false;
            $startpage = false;
            // $pagecount = false;
        } else {
            $table->pagesize($userlimit, $usercount);
            $startpage = $table->get_page_start();
            // $pagecount = $table->get_page_size();
        }


        // $userlimit = 2; //50; // We'll just take the most recent 50 maximum.
        if ($users = $offlineusers->get_users($userlimit,$startpage,$sort)) {
            foreach ($users as $user) {
                $users[$user->id]->fullname = fullname($user);

                //Nhien elo 17_09_2019
                $lastaccess = 0;
                if ($sitelevel && $user->lastaccess != 0 && $user->lastaccess) {
                    $users[$user->id]->strlastaccess = format_time($now - $user->lastaccess);
                    $lastaccess = $user->lastaccess;
                }
                else if (!$sitelevel && $user->timeaccess != 0 && $user->timeaccess) {
                    $users[$user->id]->strlastaccess = format_time($now - $user->timeaccess);
                    $lastaccess = $user->timeaccess;
                } else {
                    $users[$user->id]->strlastaccess = get_string('never');
                }
                $users[$user->id]->dataaccess = $lastaccess;
                //End Nhien elo 17_09_2019


                if ($user->elotimecreated != 0 && $user->elotimecreated) {
                    $users[$user->id]->strelotimecreated = format_time($now - $user->elotimecreated);
                } else {
                    $users[$user->id]->strelotimecreated = get_string('never');
                }
            }
        } else {
            $users = array();
        }

        //Now, we have in users, the list of users to show
        //Because they are online
        if (!empty($users)) {
            // $this->page->requires->js_call_amd('block_elo_reminder_users/change_user_visibility', 'init');

			//Nhien elo 10_09_2019
            $this->page->requires->js_call_amd('block_elo_reminder_users/mail_to_user', 'init');
            //End Nhien elo 10_09_2019

            //Accessibility: Don't want 'Alt' text for the user picture; DO want it for the envelope/message link (existing lang string).
            //Accessibility: Converted <div> to <ul>, inherit existing classes & styles.
            $this->content->text .= "<ul class='list'>\n";
            if (isloggedin() && has_capability('moodle/site:sendmessage', $this->page->context)
                           && !empty($CFG->messaging) && !isguestuser()) {
                $canshowicon = true;
            } else {
                $canshowicon = false;
            }

            $editingteacher = get_string('editingteacher','block_elo_reminder_users');
            $teacher = get_string('teacher','block_elo_reminder_users');
            $student = get_string('student','block_elo_reminder_users');

            //Add rows
            foreach ($users as $user) {
                $colcheckbox = '';$colprofile = '';$colemail = '';$colrole = '';$coltimeago = '';$collastsentmail = '';$colelocountmail = '';$colbtnaction = '';

                $titletime = $user->strlastaccess;
                $sendmail = '';
                $sendmessage = '';

                $shortname = get_string($user->archetype,'block_elo_reminder_users');

                //checkbox to send mail
                $colcheckbox .= html_writer::empty_tag('input', array('type' => 'checkbox', 'class' => 'beruusercheckbox' ,'name' => 'user'.$user->id));

                //profile
                $colprofile .= '<div class="user">';
                $colprofile .= '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$this->page->course->id.'" title="'.$user->fullname.'">';
                $colprofile .= $OUTPUT->user_picture($user, array('size'=>16, 'alttext'=>false, 'link'=>false)) .$user->fullname.'</a></div>';

                //email
                $colemail .= '<div class="email">';
                $colemail .= '<a title="'.$user->email.'">';
                $colemail .= $user->email.'</a></div>';

                //role
                $colrole .= '<div class="role">';
                $colrole .= '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$this->page->course->id.'" title="'.$shortname.'">';
                $colrole .= $shortname.'</a></div>';

                //time ago
                $coltimeago .= '<div class="userlastaccess">';
                $coltimeago .= '<a href="'.$CFG->wwwroot.'/report/log/user.php?id='.$user->id.'&amp;course='.$this->page->course->id.'&mode=all" title="'.$titletime.'">';
                $coltimeago .= $titletime . '</a>';
                $coltimeago .= '</div>';

                //last sent mail
                $collastsentmail .= '<div class="lastsentmail">';
                $collastsentmail .= $user->strelotimecreated;
                $collastsentmail .= '</div>';

                //elocountmail
                $viewhistories = html_writer::link("", $user->elocountmail,
                    array('title' => $user->elocountmail,'data-courseid' => $this->page->course->id,'data-userid' => $user->id,'class' => 'viewhistories'));
                $colelocountmail .= '<div class="elocountmail">'.$viewhistories.'</div>';

                //btn action group
                $colbtnaction .= '<div class="userright">';

                //sendmail
                $action = ($user->emailstop == 0 ? 'send' : 'nosend');
                $urlclick = ($user->emailstop == 0 ? "" : 'javascript:void(0)');

                //for 3.7
                // $sendornosendmail = ($user->emailstop == 0 ? 'sendmessage' : 't/emailno');
                // $mailtagcontents = $OUTPUT->pix_icon( 't/'.$sendornosendmail,
                    // get_string('mail_status:' . $action, 'block_elo_reminder_users') .' '.$user->email);

                //for 3.5
                if($user->emailstop != 0){
                    $mailtagcontents = $OUTPUT->pix_icon( 't/emailno',
                        get_string('mail_status:' . $action, 'block_elo_reminder_users') .' '.$user->email);
                }else{
                    // $mailtagcontents = $OUTPUT->image_url('sendmessage', 'block_elo_reminder_users')->out();
                    // $mailtagcontents = $OUTPUT->pix_icon('sendmessage', get_string('mail_status:' . $action, 'block_elo_reminder_users') .' '.$user->email, 'block_elo_reminder_users');

                    $mailtagcontents = html_writer::tag('i','',array(
                        'class' => 'icon fa fa-paper-plane fa-fw',
                        'aria-hidden' => true,
                        'title' => get_string('mail_status:' . $action, 'block_elo_reminder_users') .' '.$user->email,
                        'aria-label' => get_string('mail_status:' . $action, 'block_elo_reminder_users') .' '.$user->email,
                    ));
                }

                $mailtag = html_writer::link($urlclick, $mailtagcontents,
                    array('title' => get_string('mail_status:' . $action, 'block_elo_reminder_users'),
                        'data-action' => $action, 'data-email' => $user->email, 'data-courseid' => $this->page->course->id,'data-userid' => $user->id,'data-lastaccess' => $user->dataaccess, 'class' => 'mail-to-user'));
                $sendmail .= '<div class="usermail">' . $mailtag . '</div>';

                //send message
                if ($canshowicon) {  // Only when logged in and messaging active etc.
                    $anchortagcontents = $OUTPUT->pix_icon('t/message', get_string('messageselectadd'));
                    $anchorurl = new moodle_url('/message/index.php', array('id' => $user->id));
                    $anchortag = html_writer::link($anchorurl, $anchortagcontents,
                        array('title' => get_string('messageselectadd')));
                    $sendmessage .= '<div class="message">'.$anchortag.'</div>';
                }
                $colbtnaction .= $sendmail.$sendmessage.'</div>';

                //create and add row
                $row = array(
                    $colcheckbox,
                    $colprofile,
                    $colemail,
                    $colrole,
                    $coltimeago,
                    $collastsentmail,
                    $colelocountmail,
                    $colbtnaction
                );

                $rowclass = '';
                $html .= $table->elo_add_data($row, $rowclass);

            }
        }

        //all - teacher - student
        $teacherstudentallbtn = html_writer::tag('button', get_string('teacherstudentall','block_elo_reminder_users'), array('class' => 'btn btn-secondary' .($berutype == '' ? ' active':'')));
        $teacherallbtn = html_writer::tag('button', get_string('teacherall','block_elo_reminder_users'), array('class' => 'btn btn-secondary' .($berutype == 'teacher' ? ' active':'')));
        $studentallbtn = html_writer::tag('button', get_string('studentall','block_elo_reminder_users'), array('class' => 'btn btn-secondary' .($berutype == 'student' ? ' active':'')));

        $this->content->text .= '<br /><div class="buttons"><div class="form-inline">';
        $this->content->text .= html_writer::start_tag('div', array('class' => 'btn-group group-berutype'));
        $this->content->text .= html_writer::link($urlfilterall.'#viewberu', $teacherstudentallbtn, array());
        $this->content->text .= html_writer::link($urlfilter.'berutype=teacher#viewberu', $teacherallbtn, array());
        $this->content->text .= html_writer::link($urlfilter.'berutype=student#viewberu', $studentallbtn, array());
        $this->content->text .= html_writer::end_tag('div');
        $this->content->text .= '</div></div>';

        //Render
        $html .= $table->elo_finish_output();
        $this->content->text .= $html;

        if (!empty($users)) {

            //open form
            $this->content->text .= '<form action="'.$CFG->wwwroot.'/blocks/elo_reminder_users/action_redir.php" method="post" id="beruparticipantsform">';
            $this->content->text .= '<div>';
            $this->content->text .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
            $this->content->text .= '<input type="hidden" name="returnto" value="'.s($PAGE->url->out(false)).'" />';
            $this->content->text .= '<input type="hidden" name="berutype" value="'.$berutype.'" />';


            //select all - deselect all
            $this->content->text .= '<br /><div class="buttons"><div class="form-inline">';
            $this->content->text .= html_writer::start_tag('div', array('class' => 'btn-group'));
            $this->content->text .= html_writer::tag('input', "", array('type' => 'button', 'id' => 'berucheckallonpage', 'class' => 'btn btn-secondary',
                'value' => get_string('selectall')));
            $this->content->text .= html_writer::tag('input', "", array('type' => 'button', 'id' => 'beruchecknone', 'class' => 'btn btn-secondary',
                'value' => get_string('deselectall')));
            $this->content->text .= html_writer::end_tag('div');


            //Option for download
            $displaylist = array();
            $displaylist['#messageselectmail'] = get_string('messageselectmail','block_elo_reminder_users');

            $params = ['operation' => 'download_participants'];
            $downloadoptions = [];
            $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
            foreach ($formats as $format) {
                if ($format->is_enabled()) {
                    $params = ['operation' => 'download_participants', 'dataformat' => $format->name];
                    $url = new moodle_url('bulkchange.php', $params);
                    $downloadoptions[$url->out(false)] = get_string('dataformat', $format->component);
                }
            }

            if (!empty($downloadoptions)) {
                $displaylist[] = [get_string('downloadas', 'table') => $downloadoptions];
            }

            if ($context->id != $frontpagectx->id) {
                $instances = $manager->get_enrolment_instances();
                $plugins = $manager->get_enrolment_plugins(false);
                foreach ($instances as $key => $instance) {
                    if (!isset($plugins[$instance->enrol])) {
                        // Weird, some broken stuff in plugin.
                        continue;
                    }
                    $plugin = $plugins[$instance->enrol];
                    $bulkoperations = $plugin->get_bulk_operations($manager);

                    $pluginoptions = [];
                    foreach ($bulkoperations as $key => $bulkoperation) {
                        $params = ['plugin' => $plugin->get_name(), 'operation' => $key];
                        $url = new moodle_url('bulkchange.php', $params);
                        $pluginoptions[$url->out(false)] = $bulkoperation->get_title();
                    }
                    if (!empty($pluginoptions)) {
                        $name = get_string('pluginname', 'enrol_' . $plugin->get_name());
                        $displaylist[] = [$name => $pluginoptions];
                    }
                }
            }

            //With selected users - selectbox
            $this->content->text .= html_writer::tag('div', html_writer::tag('label', get_string("withselectedusers"),
                array('for' => 'beruformactionid', 'class' => 'col-form-label d-inline')) .
                html_writer::select($displaylist, 'formaction', '', array('' => 'choosedots'), array('id' => 'beruformactionid')),
                array('class' => 'ml-2'));
            $this->content->text .= '</div></div>';

            //close form
            $this->content->text .= '<input type="hidden" name="id" value="'.$this->page->course->id.'" />';
            $this->content->text .= '<noscript style="display:inline">';
            $this->content->text .= '<div><input type="submit" value="'.get_string('ok').'" /></div>';
            $this->content->text .= '</noscript>';
            $this->content->text .= '</div>';
            $this->content->text .= '</form>';

            //icon loading - default invisible
            $loadingtagcontents = $OUTPUT->pix_icon('y/loading', get_string('mail_status:sending', 'block_elo_reminder_users'),'moodle',array('id'=>'mail-to-user-sending'));
            $this->content->text .= '</ul><div class="eloloadinghide">'.$loadingtagcontents.'</div><div class="clearer"><!-- --></div>';
        }

        return $this->content;
    }
}


