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
 * Class containing data for courses view in the myeloworks block.
 *
 * @package    block_myeloworks
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myeloworks\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
//use core_completion\progress;
//use core_course\external\course_summary_exporter;
use context_course;

/**
 * Class containing data for courses view in the myeloworks block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eloworks_view implements renderable, templatable {
    /** Quantity of courses per page. */
    const COURSES_PER_PAGE = 6;

    /** @var array $courses List of courses the user is enrolled in. */
    protected $courses = [];

    /** @var array $coursesprogress List of progress percentage for each course. */
    protected $coursesprogress = [];

    /**
     * The eloworks_view constructor.
     *
     * @param array $courses list of courses.
     * @param array $coursesprogress list of courses progress.
     */
    public function __construct($courses, $coursesprogress) {
        $this->courses = $courses;
        $this->coursesprogress = $coursesprogress;
    }

    public function elo_get_submissionsneedgradingcount($id) {
            global $CFG;
            require_once($CFG->dirroot . '/mod/assign/locallib.php');
            list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');
            $context = \context_module::instance($cm->id);
            $assign = new \assign($context, $cm, $course);
            $assignsumary = $assign->get_assign_grading_summary_renderable();
            return $assignsumary->submissionsneedgradingcount;
        }

        public function elo_create_works_html() {
	global $USER, $DB,$OUTPUT;
        $userid = $USER->id;
        $roleids = explode(',', get_config('moodle', 'gradebookroles'));
            
        //$urlcss = $CFG->wwwroot . '/blocks/myeloworks/style.css';
	
            
        $htmlresult = '<div class="elo_works_block p-1">';
        $htmlresult .= '<link rel="stylesheet" type="text/css" href="' . $urlcss . '">';
        foreach ($this->courses as $course){
            $coursecontext = context_course::instance($course->id);
            $notteacher = 0;
            foreach ($roleids as $roleid) {
                if (user_has_role_assignment($userid, $roleid, $coursecontext->id)) {
                    $this->studentcourseids[$course->id] = $course->id;
                    // We only need to check if one of the roleids has been assigned.
                    $notteacher = 1;
                    break;
                }
            }
           // if($notteacher)
               // continue;
            if (!has_capability('moodle/grade:viewall', $coursecontext, $userid)) {
                continue;
            }
            $completion = new \completion_info($course);
            // First, let's make sure completion is enabled.
            if (!$completion->is_enabled()) {
                continue;
            }
            // Before we check how many modules have been completed see if the course has.
            if ($completion->is_course_complete($userid)) {
                continue;
            }
            // Get the number of modules that support completion.
            $modules = $completion->get_activities();
            $count = count($modules);
            if (!$count) {
                continue;
            }
// 3.11.2018 fix hien thi enddatecourse
            $classified = course_classify_for_timeline($course);
            
            //COURSE_TIMELINE_PAST', 'past');
            //COURSE_TIMELINE_INPROGRESS', 'inprogress');
            //COURSE_TIMELINE_FUTURE', 'future');
            if ($classified == COURSE_TIMELINE_PAST || $classified == COURSE_TIMELINE_FUTURE) {
                continue;
            }
            //end 3.11.2018 fix hien thi enddatecourse

            // Get the number of modules that have been completed.
            $eloworkforumnamehtml = '';
            $eloworkassignnamehtml = '';
            foreach ($modules as $module) {
                $graderecset = $DB->get_recordset_select('grade_items', 'courseid = ' . $course->id . 
                        ' AND iteminstance = '. $module->instance , null,'','gradetype', 0,0);
                $reccount = 0;
                foreach($graderecset as $graderec){
                    if($graderec->gradetype!=0)
                    {
                        $reccount = 1;
                        break;
                    }
                }
                $graderecset->close();
                if($reccount==0)
                    continue;
                //$data = $completion->get_data($module, false, $userid);
                //$itemcompleted = $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1; // code khong xai de kiem tra sau
                //$finalmodname = $module->modname;
                $markeloworks = get_string('markeloworks:elowworks', 'block_myeloworks');                
                if($module->modname =='forum'){
                    $count_post_notrated = elo_get_count_post_notrated($course->id,$module->instance,0);
                    if($count_post_notrated >0)
                    {
                        $elomoduleicon = $OUTPUT->image_icon('icon', get_string('modulename', $module->modname), $module->modname);
                        //$CFG->wwwroot .  '/theme/image.php/ouelofordson/forum/1533870850/icon
                                
                        $ForumPathIcon = '<span>'. $elomoduleicon.'</span>';    
                        $ForumPath = '<a class="elo_works_block_forum" target="_blank" href="'. $module->url->get_scheme() .'://'. $module->url->get_host() . $module->url->get_path() . '?id=' . $module->id . '">' 
                                            . $ForumPathIcon . '' . $markeloworks . ' ' . $count_post_notrated . ' ' . $module->name . '</a>';
                        $eloworkforumnamehtml .= '<div class = "eloworkforumname">' . $ForumPath. '</div>';
                    }
                }
                if($module->modname =='assign'){
                    $submissionsneedgradingcount = $this->elo_get_submissionsneedgradingcount($module->id);
                    if($submissionsneedgradingcount>0)
                    {
                        //$CFG->wwwroot .  '/theme/image.php/ouelofordson/assign/1533868393/icon
                        $elomoduleicon = $OUTPUT->image_icon('icon', get_string('modulename', $module->modname), $module->modname);
                        $AssignPathIcon = '<span>' . $elomoduleicon . '</span>';
                        $AssignPath = '<a class="elo_works_block_assign" target="_blank" href="'. $module->url->get_scheme() .'://'. $module->url->get_host() . $module->url->get_path() . '?id=' . $module->id . '">' 
                                            . $AssignPathIcon . '' . $markeloworks . ' ' . $submissionsneedgradingcount . ' ' . $module->name . '</a>';
                        $eloworkassignnamehtml .= '<div class = "eloworkassignname">' . $AssignPath . '</div>';
                    }
                }                
                //|| $module->modname =='assign'
            }
            if($eloworkforumnamehtml || $eloworkassignnamehtml ){
                $htmlresult .= '<div class = "eloworkscoursename">' . $course->shortname . $eloworkforumnamehtml . $eloworkassignnamehtml . '</div> ';
            }
        }
        $htmlresult .= '</div>';
        return $htmlresult;//$htmlyear . $htmlcoures;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->dirroot.'/lib/coursecatlib.php');        

        $eloworkshtml = $this->elo_create_works_html();      
        // Build courses view data structure.
        $coursesview = [
            'hascourses'                => !empty($this->courses),
            'eloworkshtml'                => $eloworkshtml // Nhien elocoursespercent
        ];
        return $coursesview;
    }

    /**
     * Generate a semi-random color based on the courseid number (so it will always return
     * the same color for a course)
     *
     * @param int $courseid
     * @return string $color, hexvalue color code.
     */
    /*protected function coursecolor($courseid) {
        // The colour palette is hardcoded for now. It would make sense to combine it with theme settings.
        $basecolors = ['#81ecec', '#74b9ff', '#a29bfe', '#dfe6e9', '#00b894', '#0984e3', '#b2bec3', '#fdcb6e', '#fd79a8', '#6c5ce7'];

        $color = $basecolors[$courseid % 10];
        return $color;
    }*/
}
