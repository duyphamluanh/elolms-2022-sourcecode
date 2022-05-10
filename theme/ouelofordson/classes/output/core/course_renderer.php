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
 * Course renderer.
 *
 * @package    theme_noanme
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_ouelofordson\output\core;
defined('MOODLE_INTERNAL') || die();

use moodle_url;
use lang_string;
use coursecat_helper;
use coursecat;
use stdClass;
use course_in_list;
use context_course;
use pix_url;
use html_writer;
use heading;
use pix_icon;
use image_url;
use single_select;
use core_user;
require_once($CFG->dirroot . '/course/renderer.php');
global $PAGE;
/**
 * Course renderer class.
 *
 * @package    theme_noanme
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if ($PAGE->theme->settings->coursetilestyle < 8) {
    class course_renderer extends \theme_boost\output\core\course_renderer {

        protected $countcategories = 0;

        public function frontpage_available_courses($id = 0) {
            /* available courses */
            global $CFG, $OUTPUT, $PAGE;
            require_once ($CFG->libdir . '/coursecatlib.php');

            $chelper = new coursecat_helper();
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->set_courses_display_options(array(
                'recursive' => true,
                'limit' => $CFG->frontpagecourselimit,
                'viewmoreurl' => new moodle_url('/course/index.php') ,
                'viewmoretext' => new lang_string('fulllistofcourses')
            ));

            $chelper->set_attributes(array(
                'class' => 'frontpage-course-list-all'
            ));
            $courses = coursecat::get($id)->get_courses($chelper->get_courses_display_options());
            $totalcount = coursecat::get($id)->get_courses_count($chelper->get_courses_display_options());
// Nhien Elo            
            $trialcourses = enrol_get_my_courses(null, 'sortorder ASC',0,null,true);
            $rcourseids = array_keys($trialcourses);
            $rcourseids = $rcourseids + array_keys($courses);
//End             $rcourseids = array_keys($courses); //oldcode
            $acourseids = array_chunk($rcourseids, 3);
            $newcourse = get_string('availablecourses');

            $header = '
                <div id="category-course-list">
                    <div class="courses category-course-list-all">
                    <hr>
                    <div class="class-list">
                        <h4>' . $newcourse . ' ('.$totalcount.')</h4>
                    </div>';

            $content = '';

            $footer = '
                    </div>
                </div>';

            if (count($rcourseids) > 0) {
                foreach ($acourseids as $courseids) {
                    $content .= '<div class="container-fluid"> <div class="row">';
                    $rowcontent = '';

                    foreach ($courseids as $courseid) {
                        $course = get_course($courseid);

                        $trimtitlevalue = $PAGE->theme->settings->trimtitle;
                        $trimsummaryvalue = $PAGE->theme->settings->trimsummary;

                        $trimtitle = theme_ouelofordson_course_trim_char($course->fullname, $trimtitlevalue);

                        $summary = theme_ouelofordson_strip_html_tags($course->summary);
                        $summary = theme_ouelofordson_course_trim_char($summary, $trimsummaryvalue);

                        $noimgurl = $OUTPUT->image_url('noimg', 'theme');
                        $courseurl = new moodle_url('/course/view.php', array(
                            'id' => $courseid
                        ));

                        if ($course instanceof stdClass) {
                            require_once ($CFG->libdir . '/coursecatlib.php');
                            $course = new course_in_list($course);
                        }

                        // Load from config if usea a img from course summary file if not exist a img then a default one ore use a fa-icon.
                        $imgurl = null; // origin
                        $imageurl30 = $course->__get('image');//Nhien elo img
                        $eloopenfile=fopen($imageurl30,"r");
                        if ($eloopenfile){
                           $imgurl= $imageurl30;
                        }
                        $context = context_course::instance($course->id);

                        foreach ($course->get_course_overviewfiles() as $file) {
                            $isimage = $file->is_valid_image();
                            $imgurl = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename() , !$isimage);
                            if (!$isimage) {
                                $imgurl = $noimgurl;
                            }
                        }
                        if (empty($imgurl)) {
                            $imgurl = $PAGE->theme->setting_file_url('headerdefaultimage', 'headerdefaultimage', true);
                            if (!$imgurl) {
                                $imgurl = $noimgurl; //Nhien elo origin
                                $courseimageid  = $courseid % 10;
                                $imgurl = $CFG->wwwroot. '/theme/ouelofordson/pix/courseimages/courseimage'. $courseimageid . '.jpg';
                                //isset($course->image) ? $imgurl = $course->image : $imgurl = $CFG->wwwroot. '/theme/ouelofordson/pix/courseimages/covercousedemo.jpg';
                            }
                                
                        }
                        if ($PAGE->theme->settings->coursetilestyle == 1) {
                            $rowcontent .= '
                        <div class="col-md-4">';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed1'
                            ));
                            $rowcontent .= '
                            <div class="class-box">
                                ';

                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-tooltip="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            }
                            else {
                                $tooltiptext = '';
                            }

                            $rowcontent .= '
                                    <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                    <div class="courseimagecontainer">
                                    <div class="course-image-view" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                    </div>
                                    <div class="course-overlay">
                                    <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
                                    </div>
                                    
                                    </div>
                                    <div class="course-title">
                                    <h4>' . $trimtitle . '</h4>
                                    </div>
                                    </a>
                                    <div class="course-summary">
                                    ';
                            if ($course->has_course_contacts()) {

                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul'); // .teachers
                                
                            }
                            $rowcontent .= '
                                    </div>
                                </div>
                        </div>
                        </div>';
                        }
                        if ($PAGE->theme->settings->coursetilestyle == 2) {
                            // Display course contacts. See course_in_list::get_course_contacts().
                            $enrollbutton = get_string('enrollcoursecard', 'theme_ouelofordson');
                            $rowcontent .= '
                    <div class="col-md-4">
                        ';
                            $rowcontent .= '
                    <div class="tilecontainer">
                            <figure class="coursestyle2">
                                <div class="class-box-courseview" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                ';
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-tooltip="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            }
                            else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed2'
                            ));
                            $rowcontent .= '
                                <figcaption>
                                    <h3>' . $trimtitle . '</h3>
                                    <div class="course-card">
                                    <button type="button" class="btn btn-primary btn-sm coursestyle2btn">' . $enrollbutton . '   <i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button>
                                    ';
                            if ($course->has_course_contacts()) {

                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul'); 
                                
                            }
                            $rowcontent .= '
                                </div>
                                </div>

                                </figcaption>
                                    <a ' . $tooltiptext . ' href="' . $courseurl . '" class="coursestyle2url"></a>
                                </div>
                            </figure>
                    </div>
                    </div>
                        ';
                        }
                        if ($PAGE->theme->settings->coursetilestyle == 3) {
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-tooltip="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            }
                            else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= '
                        <div class="col-md-4">
                        <div class="tilecontainer">
                            <div class="class-box-fp" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                <a ' . $tooltiptext . ' href="' . $courseurl . '" class="coursestyle3url">';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed3'
                            ));
                            $rowcontent .= '
                                    <div class="course-title">
                                    <h4><a href="' . $courseurl . '">' . $trimtitle . '</a></h4>
                                    </div>
                                    </div>
                                    </a>
                                </div>
                               </div> 
                        </div>';
                        }
                        if ($PAGE->theme->settings->coursetilestyle == 4) {
                            $rowcontent .= '
                        <div class="col-md-4">';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed1'
                            ));
                            $rowcontent .= '
                            <div class="class-box">
                                ';

                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-tooltip="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            }
                            else {
                                $tooltiptext = '';
                            }

                            $rowcontent .= '
                                    <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                    <div class="courseimagecontainer">
                                    <div class="course-image-view" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                    </div>
                                    <div class="course-overlay">
                                    <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
                                    </div>
                                    
                                    </div>
                                    <div class="course-title">
                                    <h4>' . $trimtitle . '</h4>
                                    </div>
                                    </a>
                                    <div class="course-summary">
                                    ' . $summary . '
                                    ';
                            if ($course->has_course_contacts()) {

                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul'); // .teachers
                                
                            }
                            $rowcontent .= '
                                    </div>
                                </div>
                        </div>
                        </div>';
                        }

                    if ($PAGE->theme->settings->coursetilestyle == 5) {
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? 'col-12 d-flex flex-sm-row flex-column class-fullbox coursevisible' : 'col-12 d-flex flex-sm-row flex-column class-fullbox coursedimmed1'
                            ));
                            
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }

                            $rowcontent .= '
                            <div class="col-md-2">
                                <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                   <img src="' . $imgurl . '" class="img-fluid" alt="Responsive image" width="200px">
                                </a>
                            </div>';
                            $rowcontent .='
                            <div class="col-md-4">';
                            $rowcontent .='
                                <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                    <div class="course-title-fullbox">
                                        <h4>' . $trimtitle . '</h4>
                                </a>
                                </div>';
                            if ($course->has_course_contacts()) {
                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                            }
                            
                            $rowcontent .= '</div>';
                            $rowcontent .= '<div class="col-md-6">
                                    <div class="course-summary">
                                    ' . $summary . '
                                    </div> 
                                    </div> ';

                            $rowcontent .= html_writer::end_tag('div');
                        }

                        if ($PAGE->theme->settings->coursetilestyle == 6) {
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= '
                        <div class="col-md-12">
                            <div class="class-fullbox" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                <div class="fullbox">
                                ';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? 'coursevisible' : 'coursedimmed3'
                            ));
                            $rowcontent .= '
                            
                                <div class="course-info-inner">

                                    <div class="course-title-fullboxbkg">
                                    <h4><a href="' . $courseurl . '">' . $trimtitle . '</a></h4>
                                    </div>
                                    
                                    </div>
                                    
                                ';
                            $rowcontent .= '<div class="d-flex flex-sm-row flex-column coursedata">';
                            if ($course->has_course_contacts()) {
                                $rowcontent .= '<div class="col-md-6">';
                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                                $rowcontent .= '</div>';
                            }

                        $rowcontent .= '<div class="col-md-6">
                                    <div class="course-summary">
                                    ' . $summary . '
                                    </div> 
                                    </div> </div></div>';
                        $rowcontent .='
                                        </div>
                                    
                                </div>
                        </div>';
                        } 
                    if ($PAGE->theme->settings->coursetilestyle == 7) {
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= '
                        <div class="col-md-12">
                            <div class="class-fullbox7" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center; background-color: rgba(0,0,0,0.3);
    background-blend-mode: overlay;">
                                <div class="fullbox7">
                                ';
                            
                            $rowcontent .= '<div class="course-info-inner">';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? 'coursevisible course-title-fullboxbkg7 d-flex flex-sm-row flex-column' : 'course-title-fullboxbkg coursedimmed3 d-flex flex-sm-row flex-column'
                            ));
                            $rowcontent .= '<div class="col-md-6">
                                    <h4><a href="' . $courseurl . '">' . $trimtitle . '</a></h4>
                                    </div>';
                                    if ($course->has_course_contacts()) {
                                $rowcontent .= '<div class="col-md-6">';
                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                                $rowcontent .= '</div>';
                            }
                            $rowcontent .= '</div>
                                     </div>
                                    
                                    </div>
                                </div>
                        </div>';
                        }

                    }

                    $content .= $rowcontent;
                    $content .= '</div> </div>';
                }
            }

            $coursehtml = $header . $content . $footer;
            if ($id == 0) {
                echo $coursehtml;

            }
            else {
                $coursehtml .= '<br/><br/>';
                return $coursehtml;
            }

        }
        //Nhien elo test hoc vien
        public function view_table_hocvien($id = 0, $courses = null, $totalcount = null) {
 
            global $CFG;
            $rcourseids = array_keys($courses);
            $content = '<table class="tablestudent table table-bordered '
            . 'table-hover text-center"><thead class="table-active"><tr>';
                //thead
                $content.= '<th scope="col">'.
                        get_string('course').'</th>';
                $content.= '<th scope="col">'.
                        get_string('listofstudentselo','block_myeloactivities').'</th>';
                $content.= '<th scope="col">'.
                        get_string('coursestaffelo','block_myeloactivities').'</th>';
                $content.= '</tr></thead><tbody>';
            if (count($rcourseids) > 0) {
                foreach ($rcourseids as $courses) {        
                        $course = get_course($courses);
                        if ($course->visible == 0 ) {// show course
                            continue;
                        }
                        $courseurl = new moodle_url('/report/eloprogress/index.php', array(
                        'course' => $course->id));
                        $thanhvienurl = new moodle_url('/user/index.php', array(
                        'id' => $course->id));
                       $total = user_get_total_participants($course->id, false, 0,
                       0, 0,-1, null, null, null);
                        if ($course instanceof stdClass) {
                         require_once ($CFG->libdir . '/coursecatlib.php');
                         $course = new course_in_list($course);
                        }

                    if ($total > 0 && $course->has_course_contacts()) {
                        $dimmed = $course->visible ? '' : 'text-muted';
                        $content.='<tr><th scope="row"><a class="'.$dimmed.'" target="_blank" href="'.
                        $courseurl.'" '. 'title="'.$course->fullname . '">'.
                        $course->shortname.'</a></th>';//Link Activity completion
                        $content.='<td><a class="'.$dimmed.'" target="_blank" href="'.
                        $thanhvienurl.'" '. 'title="Có '.$total . ' học viên">'.
                        $total.'</a></td>';//Link danh sach sinh vien
                        $content.='<td class="'.$dimmed.'">';
                        $content .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'));
                        foreach ($course->get_course_contacts() as $coursecontact) {
                            $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                            $content .= html_writer::tag('li', $name);
                        }
                        $content .= html_writer::end_tag('ul'); 
                        $content.='</td></tr>';
                    }  
                }
                $content.= '</tbody></table>';
            }
            return $content;
        }
        //end Nhien elo test hoc vien 
        
        public function elo_online_hours_count_by_user_work_to_course($userid, $courseid) {
            global $DB;
            $params['userid'] = $userid;
            $params['courseid'] = $courseid;
            $where = "userid = :userid AND courseid = :courseid"; //AND (action = 'loggedin' OR action = 'loggedout')
            $eventrecordset = $DB->get_recordset_select('logstore_standard_log',
                     $where, 
                     $params,'timecreated DESC, id DESC','*',0,0);
            //$recordlogin;
            $recordloginflag = 0;
            //$recordlogout;
            $countonlinetime = 0;
            $number = 0;
            foreach ($eventrecordset as $record) {
                //$timecreated = transform::datetime($record->timecreated);
                if($recordloginflag==0){
                    $recordloginflag=1;
                }
                else{// May vo 1 lan thoi nen tinh cho
                    $countonlinsection = ($recordlogin->timecreated - $record->timecreated);
                    if($countonlinsection/60/60 > 1)
                        $countonlinsection = 5*60;
                    $countonlinetime += $countonlinsection;
                }
                $recordlogin = $record;
                $number++;
            }
            if($number) {
                $countonlinetime += 5*60;
            }      
            //$countonlinetime = round($countonlinetime/60/60);
            $eventrecordset->close();
            $textsay->time = '0';
            if($countonlinetime)
                $textsay->time = format_time($countonlinetime); //round($countonlinetime/60) . '\'';
            $textsay->number = $number;
            return $textsay;
        }
        
        //Nhien elo test giang vien
        public function view_table_giangvien($courses = null, $totalcount = null) {
        global $OUTPUT ,$CFG;
        $rcourseids = array_keys($courses);
        $cache = \cache::make_from_params(\cache_store::MODE_REQUEST, 'core_analytics', 'rolearchetypes');
        if (!$teacherroles = $cache->get('teacher')) {
            $teacherroles = array_keys(get_archetype_roles('editingteacher') + get_archetype_roles('teacher'));
            $cache->set('teacher', $teacherroles);
        }
        $teachers_courses = [];
        if (count($rcourseids) > 0) {
            foreach ($rcourseids as $courses) {      
                $course = get_course($courses);
                $courseman = new \core_analytics\course($courses);
                $teacherids = $courseman->get_user_ids($teacherroles); 
                if (array_keys($teacherids)) {
                    foreach( $teacherids as $teacherid){
                        $coursecontext = context_course::instance($course->id);
                        if (has_capability('moodle/grade:viewall', $coursecontext, $teacherid)) {
                            $textsay = $this->elo_online_hours_count_by_user_work_to_course($teacherid,$course->id);
                            $teachers_courses[$teacherid][$courses] = $course;
                            $teachers_courses[$teacherid][$courses]->lastaccess   = $textsay->time;
                            $teachers_courses[$teacherid][$courses]->numberaccess = $textsay->number;
                            $teachers_courses[$teacherid][$courses]->activities   = elo_get_assigns_topics($course,$teacherid);    
                        }
                    }
                } 
            }
            $FullTable[0][0] = get_string('teacherelo','block_myeloactivities');
            $FullTable[0][1] = get_string('courseelo','block_myeloactivities');
            $FullTable[0][2] = get_string('numberaccess','block_myeloactivities');
            $FullTable[0][3] = get_string('timeaccess','block_myeloactivities');
            $FullTable[0][4] = get_string('percentsucess','block_myeloactivities');
            $countcols = count($FullTable[0]);
            foreach($teachers_courses as $teachers_course){
                foreach($teachers_course as $teacher_course){
                    foreach($teacher_course->activities as $act){
                        if($act->eloname == 'assign'){
                            $iconassign = $OUTPUT->image_icon('icon', get_string('modulename', $act->eloname), $act->eloname);
                            $FullTable[0][$countcols -1 +$act->eloorder] = '<div class="modicon">'.$iconassign.'</div>' . $act->eloviewname;
                        } 
                    }
                }
            }
            $countcols = count($FullTable[0]);
            foreach($teachers_courses as $teachers_course){
                foreach($teachers_course as $teacher_course){
                    foreach($teacher_course->activities as $act){
                        if($act->eloname == 'forum'){
                            $iconforum = $OUTPUT->image_icon('icon', get_string('modulename', $act->eloname), $act->eloname);
                            $FullTable[0][$countcols -1 +$act->eloorder] ='<div class="modicon">'.$iconforum.'</div>' . $act->eloviewname;
                        }
                    }
                }
            }
            $row = 1;
            $StartTopic = $countcols;
            foreach($teachers_courses as $TeachId => $teachers_course ){
                $userteacher = core_user::get_user($TeachId);
                $tearow = 0;
                foreach($teachers_course as $courseid => $teacher_course){
                    if (!empty($teacher_course->activities)) { // không có activities nào thì  không in row  dòng ra
                        if($tearow == 0){
                            $FullTable[$row][0] = fullname($userteacher).'<a href="mailto:'.$userteacher->email.'"> '.$OUTPUT->pix_icon('t/subscribed', get_string('sendmailtoelo', 'block_myeloactivities',$userteacher->email), 'mod_forum').'</a>';// Lay Ten Giao Vien;
                        }
                        $FullTable[$row][1] = '<a target="_blank" href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'" title="'.$teacher_course->fullname.'">'.$teacher_course->shortname.'</a>';
                        $FullTable[$row][2] = $teacher_course->numberaccess;
                        $FullTable[$row][3] = $teacher_course->lastaccess;
                        $countact = 0;
                        $countcompletedact = 0;
                        foreach($teacher_course->activities as $act){
                            if($act->eloname == 'assign'){
                                //$FullTable[$row][4 +$act->eloorder] = '<a title ="'.$act->shortname.'">'.$act->elonum.'</a>';  
                                $FullTable[$row][4 +$act->eloorder] = '<a target="_blank" href="'.$CFG->wwwroot.'/mod/'.$act->eloname.
                                '/view.php?id='.$act->id.'" title="' . s($act->shortname) . '">'.$act->elonum.'</a>';
                            }
                            if($act->eloname == 'forum'){
                                //$FullTable[$row][$StartTopic -1 +$act->eloorder] = '<a title ="'.$act->shortname.'">'.$act->elonum.'</a>';
                               $FullTable[$row][$StartTopic -1 +$act->eloorder] = '<a target="_blank" href="'.$CFG->wwwroot.'/mod/'.$act->eloname.
                                '/view.php?id='.$act->id.'" title="' . s($act->shortname) . '">'.$act->elonum.'</a>';
                            }
                            if($act->elonum == 0){
                                $countcompletedact++;
                            }
                            $countact++;
                            }
                            if($countact){
                                round($countcompletedact/$countact*100) < 100 ?
                                $FullTable[$row][4] = '<p class="text-danger">'.round($countcompletedact/$countact*100) . '%</p>'
                                : $FullTable[$row][4] = round($countcompletedact/$countact*100) . '%'; 
                            }
                            else {
                                $FullTable[$row][4] = '<p class="text-muted">N/A</p>';
                            } 
                            $tearow ++;  
                            $row ++; 
                    }
                }
            }
            ksort($FullTable);
            $countFullTable = count($FullTable);
            for($i = 0 ; $i < $countFullTable ; $i++){
                ksort($FullTable[$i]);
            }
            $htmlteacher.= '<link href="'.$CFG->wwwroot.'/blocks/myeloactivities/classes/output/fixleftcoltoprow/css/fixedheadertable.css" rel="stylesheet" media="screen" />';
            $htmlteacher.= '<link href="'.$CFG->wwwroot.'/blocks/myeloactivities/classes/output/fixleftcoltoprow/css/custom.css" rel="stylesheet" media="screen" />';
            $htmlteacher.= '<script src="'.$CFG->wwwroot.'/blocks/myeloactivities/classes/output/fixleftcoltoprow/js/jquery-1.7.2.min.js"></script>';
            $htmlteacher.= '<script src="'.$CFG->wwwroot.'/blocks/myeloactivities/classes/output/fixleftcoltoprow/js/jquery.fixedheadertable.js"></script>';
            $htmlteacher.= '<script type="text/javascript">$(document).ready(function() {$(\'#myDemoTable\').fixedHeaderTable({altClass : \'odd\',footer : true,fixedColumns : 1});});</script>';
            $htmlteacher.= '<script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/myeloactivities/classes/output/fixleftcoltoprow/js/eloactivities-jquery.js"></script>';
            $htmlteacher.= '<style type="text/css"></style>';         
            //--------------------------------------------------------------------------
            $htmlteacher.= '<div class="outerbox">';
            $htmlteacher.= '<div class="innerbox" style="height:' .min(750,250 + 50*32). 'px;">';
            $htmlteacher.= '<table class="bluetable" id="myDemoTable" cellpadding="0" cellspacing="0"><thead><tr>';

            $colcount = count($FullTable[0]);// So cot Header;
            $rowcount = count($FullTable);// So dong Header;
            // Header row
            for($icol = 0 ; $icol < $colcount ; $icol++){
                $htmlteacher .= '<th scope="col" class="myeloactivities-header">' . $FullTable[0][$icol] . '</th>';
            }
            $htmlteacher .= '</tr></thead><tbody>';

        // Rows
            for($irow = 1 ; $irow < $rowcount ; $irow++){
                $FullTable[$irow][0] == null ?  $htmlteacher.= '<tr><th class="emptycells"  scope="row">&nbsp;</th>':$htmlteacher.= '<tr><th scope="row">' . $FullTable[$irow][0]. '</th>';
                for($icol = 1 ; $icol < $colcount ; $icol++){
                    $htmlteacher.= '<td>' . $FullTable[$irow][$icol] . '</td>';
                }
                $htmlteacher.= '</tr>';
            }
            $htmlteacher .= '</tbody></table></div></div>';   
        }
        return $htmlteacher;
    }
        //End nhien elo test giang vien
        
        public function view_available_courses($id = 0, $courses = null, $totalcount = null) {

            /* available courses */
            global $CFG, $OUTPUT, $PAGE;

            $rcourseids = array_keys($courses);
            $acourseids = array_chunk($rcourseids, 4);

            if ($id != 0) {
                $newcourse = get_string('availablecourses');
            }
            else {
                $newcourse = null;
            }

            $header = '
                <div id="category-course-list">
                    <div class="courses category-course-list-all">
                    
                    <div class="class-list">
                        <h4>' . $newcourse . '</h4>
                    </div>';

            $content = '';

            $footer = '<hr>
                   </div>
                </div>';

            if (count($rcourseids) > 0) {
                foreach ($acourseids as $courseids) {
                    $content .= '<div class="container-fluid"> <div class="row">';
                    $rowcontent = '';

                    foreach ($courseids as $courseid) {
                        $course = get_course($courseid);

                        $trimtitlevalue = $PAGE->theme->settings->trimtitle;
                        $trimsummaryvalue = $PAGE->theme->settings->trimsummary;

                        $summary = theme_ouelofordson_strip_html_tags($course->summary);
                        $summary = theme_ouelofordson_course_trim_char($summary, $trimsummaryvalue);

                        $trimtitle = theme_ouelofordson_course_trim_char($course->fullname, $trimtitlevalue);

                        $noimgurl = $OUTPUT->image_url('noimg', 'theme');//origin         

                        $courseurl = new moodle_url('/course/view.php', array(
                            'id' => $courseid
                        ));

                        if ($course instanceof stdClass) {
                            require_once ($CFG->libdir . '/coursecatlib.php');
                            $course = new course_in_list($course);
                        }

                        // Load from config if usea a img from course summary file if not exist a img then a default one ore use a fa-icon.
                        $imgurl = ''; // origin
                        //Nhien $imgurl= $course->__get('image'); 
                        // Nhien View 3.0.dom;
                        $imageurl30 = $course->__get('image');
                        $eloopenfile=fopen($imageurl30,"r");
                        if ($eloopenfile){
                           $imgurl= $imageurl30;
                        }
                         
                        $context = context_course::instance($course->id);      
                        foreach ($course->get_course_overviewfiles() as $file) {
                            $isimage = $file->is_valid_image();
                            $imgurl = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename() , !$isimage);
                            if (!$isimage) {
                                $imgurl = $noimgurl;
                            }
                        }
                        if (empty($imgurl)) {
                            $imgurl = $PAGE->theme->setting_file_url('headerdefaultimage', 'headerdefaultimage', true);
                            
                            if (!$imgurl) {
                                $courseimageid  = $courseid % 10;//nhien imagecourse
                                $courseimage = $CFG->wwwroot. '/theme/ouelofordson/pix/courseimages/courseimage'. $courseimageid . '.jpg';//nhien imagecourse
                                //$courseimage = $CFG->wwwroot. '/theme/ouelofordson/pix/courseimages/covercousedemo.jpg';
                                $imgurl = $courseimage;//Nhien fix loi hien thi image course
                                //$imgurl = $noimgurl;//origin
                            }
                        }

                        if ($PAGE->theme->settings->coursetilestyle == 1) {
                            $rowcontent .= '
                        <div class="col-xs-12 col-sm-8 col-md-6 col-xl-3">';//Nhien elo
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed1'
                            ));
                            $rowcontent .= '
                            <div class="class-box">
                                ';

                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-tooltip="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }

                            $rowcontent .= '
                                    <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                    <div class="courseimagecontainer">
                                    <div class="course-image-view" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                    </div>
                                    <div class="course-overlay">
                                    <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
                                    </div>
                                    
                                    </div>
                                    <div class="course-title">
                                    <h4>' . $trimtitle . '</h4>
                                    </div>
                                    </a>
                                    <div class="course-summary">
                                    
                                    ';
                            if ($course->has_course_contacts()) {

                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                                
                            }
                            $rowcontent .= '
                                    </div>
                                </div>
                        </div>
                        </div>';
                        }
                        if ($PAGE->theme->settings->coursetilestyle == 2) {
                            // display course contacts. See course_in_list::get_course_contacts().
                            $enrollbutton = get_string('enrollcoursecard', 'theme_ouelofordson');
                            $rowcontent .= '
                    <div class="col-xs-12 col-sm-8 col-md-6 col-xl-3">
                        ';
                            $rowcontent .= '
                    <div class="tilecontainer">
                            <figure class="coursestyle2">
                                <div class="class-box-courseview" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                ';
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed2'
                            ));
                            $rowcontent .= '
                                <figcaption>
                                    <h3>' . $trimtitle . '</h3>
                                    <div class="course-card">
                                    <button type="button" class="btn btn-primary btn-sm coursestyle2btn">' . $enrollbutton . '   <i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button>
                                    ';
                            /*if ($course->has_course_contacts()) {

                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul'); 
                                
                            }*/
                            $rowcontent .= '
                                </div>

                                </figcaption>
                                    <a ' . $tooltiptext . ' href="' . $courseurl . '" class="coursestyle2url"></a>
                                </div>
                            </figure>
                    </div>
                    </div>
                        ';
                        }
                        if ($PAGE->theme->settings->coursetilestyle == 3) {
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= '
                        <div class="col-xs-12 col-sm-8 col-md-6 col-xl-3">
                        <div class="tilecontainer">
                            <div class="class-box-fp" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                <a ' . $tooltiptext . ' href="' . $courseurl . '" class="coursestyle3url">';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed3'
                            ));
                            $rowcontent .= '
                                    <div class="course-title">
                                    <h4><a href="' . $courseurl . '">' . $trimtitle . '</a></h4>
                                    </div>
                                    </div>
                                    </a>
                                </div>
                               </div> 
                        </div>';
                        }
                        if ($PAGE->theme->settings->coursetilestyle == 4) {
                            $rowcontent .= '
                        <div class="col-xs-12 col-sm-8 col-md-6 col-xl-3">';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? '' : 'coursedimmed1'
                            ));
                            $rowcontent .= '
                            <div class="class-box">
                                ';

                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }

                            $rowcontent .= '
                                    <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                    <div class="courseimagecontainer">
                                    <div class="course-image-view" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                    </div>
                                    <div class="course-overlay">
                                    <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
                                    </div>
                                    
                                    </div>
                                    <div class="course-title">
                                    <h4>' . $trimtitle . '</h4>
                                    </div>
                                    </a>
                                    <div class="course-summary">
                                    ' . $summary . '
                                    ';
                            if ($course->has_course_contacts()) {

                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                                
                            }
                            $rowcontent .= '
                                    </div>
                                </div>
                        </div>
                        </div>';
                        }

                    if ($PAGE->theme->settings->coursetilestyle == 5) {
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? 'col-12 d-flex flex-sm-row flex-column class-fullbox coursevisible' : 'col-12 d-flex flex-sm-row flex-column class-fullbox coursedimmed1'
                            ));
                            
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }

                            $rowcontent .= '
                            <div class="col-md-2">
                                <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                   <img src="' . $imgurl . '" class="img-fluid" alt="Responsive image" width="200px">
                                </a>
                            </div>';
                            $rowcontent .='
                            <div class="col-md-4">';
                            $rowcontent .='
                                <a ' . $tooltiptext . ' href="' . $courseurl . '">
                                    <div class="course-title-fullbox">
                                        <h4>' . $trimtitle . '</h4>
                                </a>
                                </div>';
                            if ($course->has_course_contacts()) {
                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                            }
                            
                            $rowcontent .= '</div>';
                            $rowcontent .= '<div class="col-md-6">
                                    <div class="course-summary">
                                    ' . $summary . '
                                    </div> 
                                    </div> ';

                            $rowcontent .= html_writer::end_tag('div');
                        }

                    if ($PAGE->theme->settings->coursetilestyle == 6) {
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= '
                        <div class="col-md-12">
                            <div class="class-fullbox" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center;">
                                <div class="fullbox">
                                ';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? 'coursevisible' : 'coursedimmed3'
                            ));
                            $rowcontent .= '
                            
                                <div class="course-info-inner">

                                    <div class="course-title-fullboxbkg">
                                    <h4><a href="' . $courseurl . '">' . $trimtitle . '</a></h4>
                                    </div>
                                    
                                    </div>
                                    
                                ';
                            $rowcontent .= '<div class="d-flex flex-sm-row flex-column coursedata">';
                            if ($course->has_course_contacts()) {
                                $rowcontent .= '<div class="col-md-6">';
                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                                $rowcontent .= '</div>';
                            }

                        $rowcontent .= '<div class="col-md-6">
                                    <div class="course-summary">
                                    ' . $summary . '
                                    </div> 
                                    </div> </div></div>';
                        $rowcontent .='
                                        </div>
                                    
                                </div>
                        </div>';
                        }
                    if ($PAGE->theme->settings->coursetilestyle == 7) {
                            if ($PAGE->theme->settings->titletooltip) {
                                $tooltiptext = 'data-toggle="tooltip" data-placement= "top" title="' . $course->fullname . '"';
                            } else {
                                $tooltiptext = '';
                            }
                            $rowcontent .= '
                        <div class="col-md-12">
                            <div class="class-fullbox7" style="background-image: url(' . $imgurl . ');background-repeat: no-repeat;background-size:cover; background-position:center; background-color: rgba(0,0,0,0.3);
    background-blend-mode: overlay;">
                            <div class="fullbox7">
                                ';
                            
                            $rowcontent .= '<div class="course-info-inner">';
                            $rowcontent .= html_writer::start_tag('div', array(
                                'class' => $course->visible ? 'coursevisible course-title-fullboxbkg7 d-flex flex-sm-row flex-column' : 'course-title-fullboxbkg coursedimmed3 d-flex flex-sm-row flex-column'
                            ));
                            $rowcontent .= '<div class="col-md-6">
                                    <h4><a href="' . $courseurl . '">' . $trimtitle . '</a></h4>
                                    </div>';
                                    if ($course->has_course_contacts()) {
                                $rowcontent .= '<div class="col-md-6">';
                                $rowcontent .= html_writer::start_tag('ul', array(
                                    'class' => 'teacherscourseview'
                                ));
                                foreach ($course->get_course_contacts() as $userid => $coursecontact) {

                                    $name = $coursecontact['rolename'] . ': ' . $coursecontact['username'];
                                    $rowcontent .= html_writer::tag('li', $name);
                                }
                                $rowcontent .= html_writer::end_tag('ul');
                                $rowcontent .= '</div>';
                            }
                            $rowcontent .= '</div>
                                     </div>
                                    
                                    </div>
                                </div>
                        </div>';
                        }


                    }

                    $content .= $rowcontent;
                    $content .= '</div> </div>';
                }
            }

            $coursehtml = $header . $content . $footer;

            return $coursehtml;
        }

        /**
         * Returns HTML to display the subcategories and courses in the given category
         *
         * This method is re-used by AJAX to expand content of not loaded category
         *
         * @param coursecat_helper $chelper various display options
         * @param coursecat $coursecat
         * @param int $depth depth of the category in the current tree
         * @return string
         */
        protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth) {
            if (!theme_ouelofordson_get_setting('enablecategoryicon')) {
                return parent::coursecat_category($chelper, $coursecat, $depth);
            }

            global $CFG, $OUTPUT;

            $classes = array(
                'category'
            );
            if (empty($coursecat->visible)) {
                $classes[] = 'dimmed_category';
            }
            if ($chelper->get_subcat_depth() > 0 && $depth >= $chelper->get_subcat_depth()) {
                $categorycontent = '';
                $classes[] = 'notloaded';
                if ($coursecat->get_children_count() || ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_COLLAPSED && $coursecat->get_courses_count())) {
                    $classes[] = 'with_children';
                    $classes[] = 'collapsed';
                }
            } else {
                $categorycontent = $this->coursecat_category_content($chelper, $coursecat, $depth);
                $classes[] = 'loaded';
                if (!empty($categorycontent)) {
                    $classes[] = 'with_children';
                }
            }

            $totalcount = coursecat::get(0)->get_children_count();

            $content = '';
            if ($this->countcategories == 0 || ($this->countcategories % 3) == 0) {
                if (($this->countcategories % 3) == 0 && $totalcount != $this->countcategories) {
                    $content .= '</div> </div>';
                }
                if ($totalcount != $this->countcategories || $this->countcategories == 0) {
                    $categoryparam = optional_param('categoryid', 0, PARAM_INT);
                    if ($categoryparam) {
                        $content .= $OUTPUT->heading(get_string('categories'));
                    }
                    $content .= '<div class="container-fluid"><div class="row">';
                }
            }

            $classes[] = 'col-md-3 box-class';
            $content = '<div class="' . join(' ', $classes) . '" data-categoryid="' . $coursecat->id . '" data-depth="' . $depth . '" data-showcourses="' . $chelper->get_show_courses() . '" data-type="' . self::COURSECAT_TYPE_CATEGORY . '">';
            $content .= '<div class="cat-icon">';

            $val = theme_ouelofordson_get_setting('catsicon');
            $url = new moodle_url('/course/index.php', array(
                'categoryid' => $coursecat->id
            ));
            $content .= '<a href="' . $url . '">';
            $content .= '<i class="fa fa-5x fa-' . $val . '"></i>';

            $categoryname = $coursecat->get_formatted_name();
            $content .= '<div>';
            $content .= '<div class="info-enhanced">';
            $content .= '<span class="class-category">' . $categoryname . '</span>';

            if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_COUNT) {
                $coursescount = $coursecat->get_courses_count();
                $content .= '  <span class="numberofcourses" title="' . get_string('numberofcourses') . '">(' . $coursescount . ')</span>';
            }
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</a>';

            $content .= '</div>'; 
            $content .= '</div>';
            if ($totalcount == $this->countcategories) {
            }
            ++$this->countcategories;
            return $content;

        }
        //Nhien quanly hoc vien coursecat_courses
        protected function coursecat_courses_elo_quanlyhocvien(coursecat_helper $chelper, $courses, $totalcount = null) {

            global $CFG;

            if ($totalcount === null) {
                $totalcount = count($courses);
            }
            if (!$totalcount) {
                // Courses count is cached during courses retrieval.
                return '';
            }

            if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {

                if ($totalcount <= $CFG->courseswithsummarieslimit) {
                    $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
                } else {
                    $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
                }
            }

            $paginationurl = $chelper->get_courses_display_option('paginationurl');
            $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
            if ($totalcount > count($courses)) {

                if ($paginationurl) {
                    $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                    $page = $chelper->get_courses_display_option('offset') / $perpage;
                    $pagingbar = $this->paging_bar($totalcount, $page, $perpage, $paginationurl->out(false, array(
                        'perpage' => $perpage
                    )));
                    if ($paginationallowall) {
                        $pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array(
                            'perpage' => 'all'
                        )) , get_string('showall', '', $totalcount)) , array(
                            'class' => 'paging paging-showall'
                        ));
                    }
                } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {

                    $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                    $morelink = html_writer::tag('div', html_writer::tag('a', html_writer::start_tag('i', array(
                        'class' => 'fa-graduation-cap' . ' fa fa-fw'
                    )) . html_writer::end_tag('i') . $viewmoretext, array(
                        'href' => $viewmoreurl,
                        'class' => 'btn btn-primary coursesmorelink'
                    )) , array(
                        'class' => 'paging paging-morelink'
                    ));

                }
            } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {

                $pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array(
                    'perpage' => $CFG->coursesperpage
                )) , get_string('showperpage', '', $CFG->coursesperpage)) , array(
                    'class' => 'paging paging-showperpage'
                ));
            }

            $attributes = $chelper->get_and_erase_attributes('courses');
            $content = html_writer::start_tag('div', $attributes);

            if (!empty($pagingbar)) {
                $content .= $pagingbar;
            }
            $categoryid = optional_param('categoryid', 0, PARAM_INT);
            $coursecount = 0;

            $content .= $this->view_table_hocvien($categoryid, $courses, $totalcount);

            if (!empty($pagingbar)) {
                $content .= $pagingbar;
            }
            if (!empty($morelink)) {
                $content .= $morelink;
            }

            $content .= html_writer::end_tag('div');
            

            $content .= '<div class="clearfix"></div>';

            return $content;
        }
        //nhien elo test giang vien
        protected function coursecat_courses_elo_quanlygiangvien(coursecat_helper $chelper, $courses, $totalcount = null) {

            global $CFG;

            if ($totalcount === null) {
                $totalcount = count($courses);
            }
            if (!$totalcount) {
                // Courses count is cached during courses retrieval.
                return '';
            }
            if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {

                if ($totalcount <= $CFG->courseswithsummarieslimit) {
                    $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
                } else {
                    $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
                }
            }

            $paginationurl = $chelper->get_courses_display_option('paginationurl');
            $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
            if ($totalcount > count($courses)) {

                if ($paginationurl) {
                    $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                    $page = $chelper->get_courses_display_option('offset') / $perpage;
                    $pagingbar = $this->paging_bar($totalcount, $page, $perpage, $paginationurl->out(false, array(
                        'perpage' => $perpage
                    )));
                    if ($paginationallowall) {
                        $pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array(
                            'perpage' => 'all'
                        )) , get_string('showall', '', $totalcount)) , array(
                            'class' => 'paging paging-showall'
                        ));
                    }
                } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {

                    $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                    $morelink = html_writer::tag('div', html_writer::tag('a', html_writer::start_tag('i', array(
                        'class' => 'fa-graduation-cap' . ' fa fa-fw'
                    )) . html_writer::end_tag('i') . $viewmoretext, array(
                        'href' => $viewmoreurl,
                        'class' => 'btn btn-primary coursesmorelink'
                    )) , array(
                        'class' => 'paging paging-morelink'
                    ));

                }
            } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {

                $pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array(
                    'perpage' => $CFG->coursesperpage
                )) , get_string('showperpage', '', $CFG->coursesperpage)) , array(
                    'class' => 'paging paging-showperpage'
                ));
            }

            $attributes = $chelper->get_and_erase_attributes('courses');
            $content = html_writer::start_tag('div', $attributes);

            if (!empty($pagingbar)) {
                $content .= $pagingbar;
            }
            //$categoryid = optional_param('categoryid', 0, PARAM_INT);
            //$coursecount = 0;

            $content .= $this->view_table_giangvien($courses, $totalcount);

            if (!empty($pagingbar)) {
                $content .= $pagingbar;
            }
            if (!empty($morelink)) {
                $content .= $morelink;
            }

            $content .= html_writer::end_tag('div');
            

            $content .= '<div class="clearfix"></div>';

            return $content;
        }
        //end nhien elo test giang vien
        
        //Nhien quanly hoc vien coursecat_courses end
        protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null) {

            global $CFG;

            if ($totalcount === null) {
                $totalcount = count($courses);
            }
            if (!$totalcount) {
                // Courses count is cached during courses retrieval.
                return '';
            }

            if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {

                if ($totalcount <= $CFG->courseswithsummarieslimit) {
                    $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
                } else {
                    $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
                }
            }

            $paginationurl = $chelper->get_courses_display_option('paginationurl');
            $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
            if ($totalcount > count($courses)) {

                if ($paginationurl) {
                    $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                    $page = $chelper->get_courses_display_option('offset') / $perpage;
                    $pagingbar = $this->paging_bar($totalcount, $page, $perpage, $paginationurl->out(false, array(
                        'perpage' => $perpage
                    )));
                    if ($paginationallowall) {
                        $pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array(
                            'perpage' => 'all'
                        )) , get_string('showall', '', $totalcount)) , array(
                            'class' => 'paging paging-showall'
                        ));
                    }
                } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {

                    $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                    $morelink = html_writer::tag('div', html_writer::tag('a', html_writer::start_tag('i', array(
                        'class' => 'fa-graduation-cap' . ' fa fa-fw'
                    )) . html_writer::end_tag('i') . $viewmoretext, array(
                        'href' => $viewmoreurl,
                        'class' => 'btn btn-primary coursesmorelink'
                    )) , array(
                        'class' => 'paging paging-morelink'
                    ));

                }
            } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {

                $pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array(
                    'perpage' => $CFG->coursesperpage
                )) , get_string('showperpage', '', $CFG->coursesperpage)) , array(
                    'class' => 'paging paging-showperpage'
                ));
            }

            $attributes = $chelper->get_and_erase_attributes('courses');
            $content = html_writer::start_tag('div', $attributes);

            if (!empty($pagingbar)) {
                $content .= $pagingbar;
            }
            $categoryid = optional_param('categoryid', 0, PARAM_INT);
            $coursecount = 0;

            $content .= $this->view_available_courses($categoryid, $courses, $totalcount);

            if (!empty($pagingbar)) {
                $content .= $pagingbar;
            }
            if (!empty($morelink)) {
                $content .= $morelink;
            }

            $content .= html_writer::end_tag('div');
            

            $content .= '<div class="clearfix"></div>';

            return $content;
        }

        protected static function timeaccesscompare($a, $b) {
            // timeaccess is lastaccess entry and timestart an enrol entry.
            if ((!empty($a->timeaccess)) && (!empty($b->timeaccess))) {
                // Both last access.
                if ($a->timeaccess == $b->timeaccess) {
                    return 0;
                }
                return ($a->timeaccess > $b->timeaccess) ? -1 : 1;
            } else if ((!empty($a->timestart)) && (!empty($b->timestart))) {
                // Both enrol.
                if ($a->timestart == $b->timestart) {
                    return 0;
                }
                return ($a->timestart > $b->timestart) ? -1 : 1;
            }
            // Must be comparing an enrol with a last access.
            // -1 is to say that 'a' comes before 'b'.
            if (!empty($a->timestart)) {
                // 'a' is the enrol entry.
                return -1;
            }
            // 'b' must be the enrol entry.
            return 1;
        }

        public function frontpage_my_courses() {
            global $USER, $CFG, $DB;

            if (!isloggedin() or isguestuser()) {
                return '';
            }

            $nomycourses = '<div class="alert alert-info alert-block">' . get_string('nomycourses', 'theme_ouelofordson') . '</div>';

            $lastaccess = '';

            $output = '';
            if (theme_ouelofordson_get_setting('frontpagemycoursessorting')) {
                $courses = enrol_get_my_courses(null, 'sortorder ASC');
                if ($courses) {
                    // We have something to work with.  Get the last accessed information for the user and populate.
                    global $DB, $USER;
                    $lastaccess = $DB->get_records('user_lastaccess', array('userid' => $USER->id), '', 'courseid, timeaccess');
                    if ($lastaccess) {
                        foreach ($courses as $course) {
                            if (!empty($lastaccess[$course->id])) {
                                $course->timeaccess = $lastaccess[$course->id]->timeaccess;
                            }
                        }
                    }
                    // Determine if we need to query the enrolment and user enrolment tables.
                    $enrolquery = false;
                    foreach ($courses as $course) {
                        if (empty($course->timeaccess)) {
                            $enrolquery = true;
                            break;
                        }
                    }
                    if ($enrolquery) {
                        // We do.
                        $params = array('userid' => $USER->id);
                        $sql = "SELECT ue.id, e.courseid, ue.timestart
                            FROM {enrol} e
                            JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)";
                        $enrolments = $DB->get_records_sql($sql, $params, 0, 0);
                        if ($enrolments) {
                            // Sort out any multiple enrolments on the same course.
                            $userenrolments = array();
                            foreach ($enrolments as $enrolment) {
                                if (!empty($userenrolments[$enrolment->courseid])) {
                                    if ($userenrolments[$enrolment->courseid] < $enrolment->timestart) {
                                        // Replace.
                                        $userenrolments[$enrolment->courseid] = $enrolment->timestart;
                                    }
                                } else {
                                    $userenrolments[$enrolment->courseid] = $enrolment->timestart;
                                }
                            }
                            // We don't need to worry about timeend etc. as our course list will be valid for the user from above.
                            foreach ($courses as $course) {
                                if (empty($course->timeaccess)) {
                                    $course->timestart = $userenrolments[$course->id];
                                }
                            }
                        }
                    }
                    uasort($courses, array($this, 'timeaccesscompare'));
                } else {
                    
                    return $nomycourses;

                }

            $sortorder = $lastaccess;

            } else if (!empty($CFG->navsortmycoursessort)) {
                // sort courses the same as in navigation menu
                $sortorder = 'visible DESC,'. $CFG->navsortmycoursessort.' ASC';
                $courses  = enrol_get_my_courses('summary, summaryformat', $sortorder);
                if (!$courses) {
                    return $nomycourses;
                }
            } else {
                $sortorder = 'visible DESC,sortorder ASC';
                $courses  = enrol_get_my_courses('summary, summaryformat', $sortorder);
                if (!$courses) {
                    return $nomycourses;
                }
            }
            
            $rhosts   = array();
            $rcourses = array();
            if (!empty($CFG->mnet_dispatcher_mode) && $CFG->mnet_dispatcher_mode==='strict') {
                $rcourses = get_my_remotecourses($USER->id);
                $rhosts   = get_my_remotehosts();
            }

            if (!empty($courses) || !empty($rcourses) || !empty($rhosts)) {

                $chelper = new coursecat_helper();
                if (count($courses) > $CFG->frontpagecourselimit) {
                    // There are more enrolled courses than we can display, display link to 'My courses'.
                    $totalcount = count($courses);
                    $courses = array_slice($courses, 0, $CFG->frontpagecourselimit, true);
                    $chelper->set_courses_display_options(array(
                            'viewmoreurl' => new moodle_url('/my/'),
                            'viewmoretext' => new lang_string('mycourses')
                        ));
                } else {
                    // All enrolled courses are displayed, display link to 'All courses' if there are more courses in system.
                    $chelper->set_courses_display_options(array(
                            'viewmoreurl' => new moodle_url('/course/index.php'),
                            'viewmoretext' => new lang_string('fulllistofcourses')
                        ));
                    $totalcount = $DB->count_records('course') - 1;
                }
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
                        set_attributes(array('class' => 'frontpage-course-list-enrolled'));
                $output .= $this->coursecat_courses($chelper, $courses, $totalcount);

                // MNET
                if (!empty($rcourses)) {
                    // at the IDP, we know of all the remote courses
                    $output .= html_writer::start_tag('div', array('class' => 'courses'));
                    foreach ($rcourses as $course) {
                        $output .= $this->frontpage_remote_course($course);
                    }
                    $output .= html_writer::end_tag('div'); // .courses
                } elseif (!empty($rhosts)) {
                    // non-IDP, we know of all the remote servers, but not courses
                    $output .= html_writer::start_tag('div', array('class' => 'courses'));
                    foreach ($rhosts as $host) {
                        $output .= $this->frontpage_remote_host($host);
                    }
                    $output .= html_writer::end_tag('div'); // .courses
                }
            }
            return $output;
        }

        public function course_modchooser($modules, $course) {

            // This HILLBROOK function is overridden here to refer to the local theme's copy of modchooser to render a modified.
            // Activity chooser for Hillbrook.
            if (!$this->page->requires->should_create_one_time_item_now('core_course_modchooser')) {
                return '';
            }
            $modchooser = new \theme_ouelofordson\output\modchooser($course, $modules);
            return $this->render($modchooser);
        }

    }
} else {
    class course_renderer extends \theme_boost\output\core\course_renderer {
        public function course_modchooser($modules, $course) {

            // This HILLBROOK function is overridden here to refer to the local theme's copy of modchooser to render a modified.
            // Activity chooser for Hillbrook.
            if (!$this->page->requires->should_create_one_time_item_now('core_course_modchooser')) {
                return '';
            }
            $modchooser = new \theme_ouelofordson\output\modchooser($course, $modules);
            return $this->render($modchooser);
        }

    }
}