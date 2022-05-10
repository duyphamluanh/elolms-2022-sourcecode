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
 * Contains class used to return completion progress information.
 *
 * @package    core_completion
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_completion;

defined('MOODLE_INTERNAL') || die();

define('ACTIVITI_GIAHAN_ELO', 1);//Nhien elo
define('ACTIVITI_ZERO_ELO', 0);//Nhien elo
require_once($CFG->libdir . '/completionlib.php');

/**
 * Class used to return completion progress information.
 *
 * @package    core_completion
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress {

    /**
     * Returns the course percentage completed by a certain user, returns null if no completion data is available.
     * update 10_05_2019
     * @param \stdClass $course Moodle course object
     * @param int $userid The id of the user, 0 for the current user
     * @return null|float The percentage, or null if completion is not supported in the course,
     *         or there are no activities that support completion.
     */
    public static function get_course_progress_percentage($course, $userid = 0) {
        global $USER,$DB;

        // Make sure we continue with a valid userid.
        if (empty($userid)) {
            $userid = $USER->id;
        }
        $completion = new \completion_info($course);
        // First, let's make sure completion is enabled.
        if (!$completion->is_enabled()) {
            return null;
        }
        // Before we check how many modules have been completed see if the course has.
        if ($completion->is_course_complete($userid)) {
            return 100;
        }
        // Get the number of modules that support completion.
        $modules = $completion->get_activities();
        $count = count($modules);
        if (!$count) {
            return null;
        }
        // Get the number of modules that have been completed.
        $completed = 0;
        $khongtinhvaomauso = 0;
        $acceptmodname = array('assign','forum','lesson','quiz','scorm','workshop');
        foreach ($modules as $module) {
            $recount = 0;
            if ($module->modname == 'label') {
                $khongtinhvaomauso++;
                continue;  
            }
            if (in_array($module->modname, $acceptmodname)) {
//                $graderecset = $DB->get_recordset_select('grade_items', 'courseid = ' . $course->id . //nhien elo start 14_1_2019 count activities gia hạn
//                        ' AND iteminstance = '. $module->instance , null,'','gradetype,multfactor,hidden', 0,0);
//fix17032021
$params = ['courseid' => $course->id, 'iteminstance' => $module->instance, 'itemmodule' => $module->modname];
$sql = "courseid = :courseid AND iteminstance = :iteminstance AND itemmodule = :itemmodule";
$graderecset = $DB->get_recordset_select('grade_items', $sql, $params, 'gradetype,multfactor,hidden');
//fix17032021 
                if ($graderecset->valid()) {
                    foreach($graderecset as $graderec){
                        if ($graderec->gradetype == ACTIVITI_ZERO_ELO) {
                            $khongtinhvaomauso++;// cac loai hoat dong khong cham diem
                            $recount = 1;
                            break;  
                        }
                        if ($graderec->hidden == ACTIVITI_GIAHAN_ELO) {// các hoạt động ẩn đối với sinh viên
                            $khongtinhvaomauso++;
                            $recount = 1;
                            break;  
                        }
                        if ($graderec->multfactor < ACTIVITI_GIAHAN_ELO && $graderec->multfactor > ACTIVITI_ZERO_ELO ) {
                            $khongtinhvaomauso++;
                        }
                    }//Nhien elo 14_01_2019 count activities gia hạn  
                    $graderecset->close();
                    if ($recount == 1) {
                        continue;
                    }
                } 
                else{//Không có trong bảng grade_items nhưng vẫn nằm trong hoạt động -> cộng vào mẫu số 
                    $khongtinhvaomauso++;
                    $graderecset->close();
                    continue;
                }
            }
            $data = $completion->get_data($module, false, $userid);
            $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
        }
        $completed_percent = min(100,$completed/max(1,($count - $khongtinhvaomauso)) * 100);//Nhien elo 14_01_2019 fix percent
        
        return $completed_percent;
    }
}
