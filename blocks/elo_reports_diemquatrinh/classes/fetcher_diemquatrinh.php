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
 * File containing onlineusers class.
 *
 * @package    block_elo_reports_diemquatrinh
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_elo_reports_diemquatrinh;

require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->dirroot . '/blocks/elo_reports_diemquatrinh/lib.php');
require_once $CFG->dirroot . '/grade/lib.php';

use grade_item;

defined('MOODLE_INTERNAL') || die();

/**
 * Class used to list and count elo_reports_diemquatrinh
 *
 * @package    block_elo_reports_diemquatrinh
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fetcher_diemquatrinh {

    public $rawdata = array();
    public $teachername = null;
    public $shortcoursename = null;
    public $fullcoursename = null;
    public $fullshortname = null;
    public $semester = null;
    public $year = null;
    protected $users_rs;
    protected $courseid;
    protected $courseitem;

    /**
     * Class constructor
     *
     * @param int $crformat The group (if any) to filter on
     * @param array $params
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
        $this->getdetailcourse();
        $this->courseitem = grade_item::fetch_course_item($this->courseid);
        $this->init();
    }

    private function init() {

        $gui = new \diemquatrinh_users_iterator($this->courseid, $this->courseitem);
        $profilefields = \tool_diemquatrinh_utils::get_diemquatrinh_user_profile_fields();
        $this->teachername = $gui->get_teacher();
        $teacheruserid = $gui->getteacheruserid();
        $gui->init();
        $stt = 1;
        while ($userdata = $gui->next_user()) {
            $user = $userdata->user;
            if (!array_key_exists($user->id, $teacheruserid)) {// check user teacher id
                $finalgrade = $userdata->finalgrade;
                $row = array();
                //no
                $row[] = $stt++;
                //id number, firstname,lastname,tenlop,
                foreach ($profilefields as $field) {
                    if (in_array($field->shortname, array('diemso'))) {
                        continue;
                    }
                    $fieldvalue = \tool_diemquatrinh_utils::get_user_field_value_elo($user, $field);
                    if(in_array($field->shortname, array('ho','ten'))){
                        $row[] = mb_convert_case($fieldvalue, MB_CASE_TITLE, "UTF-8");//convert all uppercase string to first uppercase letter string
                    }else{
                        $row[] = $fieldvalue;
                    }
                }
                //points
                $gradestr = grade_format_gradevalue($finalgrade, $this->courseitem, false);
                $row[] = $gradestr;
                //group class
                $row[] = $user->name;
                $this->rawdata[] = $row;
            }
        }
        $gui->close();
    }

    public function getdetailcourse() {
        global $DB;
        $course = $DB->get_record('course', array('id' => $this->courseid), '*');
        $this->fullshortname = $course->shortname;
        $category = \coursecat::get($course->category);
        $semester = $category->get_formatted_name();
        //get shortname
        if (preg_grep('/(.*)-(.*)/', explode("\n", $course->shortname))) {
            $shortname = explode("-", $course->shortname);
            $shortname = trim($shortname[0]);
            $this->shortcoursename = $shortname;
        }
        //get fullname
        if (preg_grep('/(.*)-(.*)/', explode("\n", $course->fullname))) {
            $fullname = explode("-", $course->fullname);
            $fullname = trim($fullname[0]);
            $this->fullcoursename = $fullname;
        }
        //get year and semester
        if (preg_grep('/^HK\d{3,4}$/', explode("\n", $semester))) {
            $rangeyear = explode("HK", $semester); //HK203 - 20 is year 3 is semester
            if (strlen($rangeyear[1]) == 3) {
                $this->semester = substr($rangeyear[1], 2, 1); //return 1 digit
            } else {
                $this->semester = substr($rangeyear[1], 2, 2); //return 2 digits
            }
            $this->year = "20" . substr($rangeyear[1], 0, 2) . "-20" . (substr($rangeyear[1], 0, 2) + 1); // 2020-2099
        }
    }

}
