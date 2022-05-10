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
 * @package    block_elo_reports_diemquatrinh
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/blocks/elo_reports_diemquatrinh/lib.php');
//Loc filter
define("BER_FILTER_COURSE", array(21));

//class get users and grade items
class diemquatrinh_users_iterator {

    /**
     * The couse whose users we are interested in
     */
    protected $courseid;

    /**
     * A recordset of graded users
     */
    protected $users_rs;
    protected  $teacherid = array();
    /**
     * A recordset of user grades (grade_grade instances)
     */
    protected $grades_rs;
    protected $gradestack;
    protected $courseitem;

    public function __construct($course, $courseitem = null) {
        $this->courseid = $course;
        $this->courseitem = $courseitem;
        $this->gradestack = array();
    }

    public function init() {
        global $CFG, $DB;
        $getparams = array();
        $getparams['ej1_courseid'] = $this->courseid;
        $getparams['grbr5'] = $CFG->gradebookroles;
        $getparams['ej1_enabled'] = 0;
        $getparams['ej1_active'] = 0;
        $getparams['ej1_now1'] = round(time(), -2);
        $getparams['ej1_now2'] = round(time(), -2);
        $getparams['eu1_guestid'] = '1';

        $userfields = '';
        $customfieldssql = '';

        $customfieldscount = 0;
        $customfieldsarray = \tool_diemquatrinh_utils::get_diemquatrinh_user_profile_fields();
        foreach ($customfieldsarray as $field) {
            if (!empty($field->customid)) {
                $customfieldssql .= "
                        LEFT JOIN (SELECT * FROM {user_info_data}
                            WHERE fieldid = :cf$customfieldscount) cf$customfieldscount
                        ON eu1_u.id = cf$customfieldscount.userid";
                $userfields .= ", cf$customfieldscount.data AS customfield_{$field->customid}";
                $getparams['cf' . $customfieldscount] = $field->customid;
                $customfieldscount++;
            }
        }

        $users_sql = "SELECT 
                DISTINCT eu1_u.id as id, 
                eu1_u.lastname AS ho,
                eu1_u.firstname AS ten $userfields
            FROM {user} eu1_u
                JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = eu1_u.id
                JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = :ej1_courseid)
                $customfieldssql
                JOIN {role_assignments} ra ON ra.userid = eu1_u.id
                JOIN {context} ctx ON ra.contextid = ctx.id
                JOIN {role} r ON r.id = ra.roleid
            WHERE 1 = 1 
                    AND ra.roleid = :grbr5
                    AND FIND_IN_SET(ra.contextid,REPLACE(ctx.path,'/',','))
                    AND ej1_ue.status = :ej1_active 
                    AND ej1_e.status = :ej1_enabled 
                    AND ej1_ue.timestart < :ej1_now1 
                    AND (ej1_ue.timeend = 0 OR ej1_ue.timeend > :ej1_now2) 
                    AND eu1_u.id <> :eu1_guestid 
                    AND eu1_u.deleted = 0 
            ORDER BY ho ASC, ten ASC, id ASC";
        $this->users_rs = $DB->get_recordset_sql($users_sql, $getparams);
        return true;
    }

    public function get_teacher() {
        global $DB;
        $role = $DB->get_record('role', array('shortname' => 'teacher'));
        $context = get_context_instance(CONTEXT_COURSE, $this->courseid);
        $roleusers = get_role_users($role->id, $context);

        foreach ($roleusers as $key=> $user) {
            $this->teacherid[$key] =  $key;
            $strroleusers = fullname($user);
        }
        return $strroleusers;
    }
    //get teacher user id 
    public function getteacheruserid() {
        return $this->teacherid;
    }
    
    public function next_user() {
        if (!$this->users_rs) {
            return false; // no users present
        }

        if (!$this->users_rs->valid()) {
            if ($current = $this->_pop()) {
                // this is not good - user or grades updated between the two reads above :-(
            }

            return false; // no more users
        } else {
            $user = $this->users_rs->current();
            $this->users_rs->next();
        }
        $coursegrade = new grade_grade(array('itemid' => $this->courseitem->id, 'userid' => $user->id));
        $coursegrade->grade_item = & $this->courseitem;
        $finalgrade = $coursegrade->finalgrade;

        // Set user suspended status.
        $result = new stdClass();
        $result->user = $user;
        $result->finalgrade = $finalgrade;
        return $result;
    }

    private function _pop() {
        if (empty($this->gradestack)) {
            if (empty($this->grades_rs) || !$this->grades_rs->valid()) {
                return null; // no grades present
            }

            $current = $this->grades_rs->current();

            $this->grades_rs->next();

            return $current;
        } else {
            return array_pop($this->gradestack);
        }
    }

    public function close() {
        if ($this->users_rs) {
            $this->users_rs->close();
            $this->users_rs = null;
        }
        if ($this->grades_rs) {
            $this->grades_rs->close();
            $this->grades_rs = null;
        }
        $this->gradestack = array();
    }

}

class tool_diemquatrinh_utils {

    /**
     * This class can not be instantiated
     */
    private function __construct() {
        
    }

    public static function get_user_field_value_elo($user, $field) {
        if (!empty($field->customid)) {
            $fieldname = 'customfield_' . $field->customid;
            if (!empty($user->{$fieldname}) || is_numeric($user->{$fieldname})) {
                $fieldvalue = self::format_row($user, $field);
                return $fieldvalue;
            } else {
                $fieldvalue = '';
            }
        } else {
            if ($fieldvalue = $user->{$field->shortname}) {
                return $fieldvalue;
            } else {
                $fieldvalue = '';
            }
        }
        return $fieldvalue;
    }

    public static function format_row($user, $field) {
        if (is_array($field)) {
            $field = (object) $field;
        }
        $colmethodname = 'col_' . $field->shortname;
//        if ($field->shortname === 'ngaysinh') {
//            $formattedcolumn = self::$colmethodname($user, $field);
//        } else 
        if ($field->shortname === 'ghichu') {
            $formattedcolumn = self::$colmethodname($user, $field);
        } else {
            $fieldname = 'customfield_' . $field->customid;
            $formattedcolumn = $user->{$fieldname};
        }
        return $formattedcolumn;
    }

    public static function col_ghichu($user, $field) {
        $fieldname = 'customfield_' . $field->customid;
        return format_string($user->{$fieldname});
    }

    public static function get_diemquatrinh_user_profile_fields_import() {
        global $DB;

        $fields = array();
        // Sets the list of custom profile fields
        $customprofilefields = array('masosinhvien', 'tenlop', 'manhomdangkymon');
        if (!empty($customprofilefields)) {
            list($wherefields, $whereparams) = $DB->get_in_or_equal($customprofilefields);
            $customfields = $DB->get_records_sql("SELECT f.*
                                                    FROM {user_info_field} f
                                                    JOIN {user_info_category} c ON f.categoryid=c.id
                                                    WHERE f.shortname $wherefields
                                                    ORDER BY c.sortorder ASC, f.sortorder ASC", $whereparams);

            foreach ($customfields as $id => $field) {
                // Make sure we can display this custom field
                if (!in_array($field->shortname, $customprofilefields)) {
                    continue;
                }
                $fields[$id] = format_string($field->name);
            }
        }
        return $fields;
    }

    public static function user_get_default_fields_elo() {
        return array('masosinhvien', 'ho', 'ten', 'tenlop', 'diemso', 'manhomdangkymon');
    }

    public static function get_diemquatrinh_user_profile_fields() {
        global $DB;

        $fields = array();
        $userdefaultfields = self::user_get_default_fields_elo();


        $customprofilefields = array('masosinhvien');
        if (!empty($customprofilefields)) {
            list($wherefields, $whereparams) = $DB->get_in_or_equal($customprofilefields);
            $customfields = $DB->get_records_sql("SELECT f.*
                                                    FROM {user_info_field} f
                                                    JOIN {user_info_category} c ON f.categoryid=c.id
                                                    WHERE f.shortname $wherefields
                                                    ORDER BY c.sortorder ASC, f.sortorder ASC", $whereparams);

            foreach ($customfields as $field) {
                // Make sure we can display this custom field
                if (!in_array($field->shortname, $customprofilefields)) {
                    continue;
                }
                $obj = new stdClass();
                $obj->customid = $field->id;
                $obj->shortname = $field->shortname;
                $obj->fullname = format_string($field->name);
                $obj->datatype = $field->datatype;
                $obj->default = '';
                $fields[] = $obj;
            }
        }

        // Sets the list of profile fields
        $userprofilefields = array_map('trim', explode(',', 'ho,ten'));
        if (!empty($userprofilefields)) {
            foreach ($userprofilefields as $field) {
                $field = trim($field);
                if (!in_array($field, $userdefaultfields)) {
                    continue;
                }
                $obj = new stdClass();
                $obj->customid = 0;
                $obj->shortname = $field;
                $obj->fullname = get_string($field, 'block_elo_reports_diemquatrinh');
                $fields[] = $obj;
            }
        }

        // Sets the list of custom profile fields
        $customprofilefields = array('tenlop', 'manhomdangkymon');
        if (!empty($customprofilefields)) {
            list($wherefields, $whereparams) = $DB->get_in_or_equal($customprofilefields);
            $customfields = $DB->get_records_sql("SELECT f.*
                                                    FROM {user_info_field} f
                                                    JOIN {user_info_category} c ON f.categoryid=c.id
                                                    WHERE f.shortname $wherefields
                                                    ORDER BY c.sortorder ASC, f.sortorder ASC", $whereparams);

            foreach ($customfields as $field) {
                // Make sure we can display this custom field
                if (!in_array($field->shortname, $customprofilefields)) {
                    continue;
                }
                $obj = new stdClass();
                $obj->customid = $field->id;
                $obj->shortname = $field->shortname;
                $obj->fullname = format_string($field->name);
                $obj->datatype = $field->datatype;
                $obj->default = '';
                $fields[] = $obj;
            }
        }
        return $fields;
    }

}

function elo_reports_diemquatrinh_get_filter_params($params = array()) {
    global $DB;

    if (!empty($params)) {
        $crformat = $params['crformat'] ?? 21;
        $bercourseid = $params['bercourseid'] ?? 0;
        $berlength = $params['berlength'] ?? BER_LENGTH_MENU_DEFAULT;
        $berpage = $params['berpage'] ?? 0;
        $bersort = $params['bersort'] ?? '';
    } else {
        //Report format
        $crformat = optional_param('crformat', '21', PARAM_INT);
        $bercourseid = optional_param('bercourseid', 0, PARAM_INT);
        $berlength = optional_param('berlength', BER_LENGTH_MENU_DEFAULT, PARAM_INT);
        $berpage = optional_param('berpage', 0, PARAM_INT);
        $bersort = optional_param('bersort', '', PARAM_INT);
    }

    //default not use length, it will be used 25
    if (!in_array($berlength, BER_LENGTH_MENU)) {
        $berlength = BER_LENGTH_MENU_DEFAULT;
    }

    $params = [
        'crformat' => $crformat,
        'bercourseid' => $bercourseid,
        'berlength' => $berlength,
        'berpage' => $berpage,
        'bersort' => $bersort,
    ];

    return $params;
}

function block_elo_reports_diemquatrinh_dropdownlist_reportformat($crformat = "21", $params = array(), $dashboard = true) {
    global $CFG, $DB, $PAGE;

    $htmlhidden = '';

    //create varibles php from params
    foreach ($params as $key => $value) {
        ${$key} = $value;
        if (in_array($key, array('crformat'))) {
            continue;
        }
        $htmlhidden .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
    }
    $url = new moodle_url('/blocks/elo_reports_diemquatrinh/view.php');
    //optgroup option
    $options = [
        "group_two" => [
            "key" => "Phần 1. Xuất báo cáo điểm theo lớp",
            "value" => [
                "21" => "2.1. Xuất điểm quá trình theo nhóm ĐKMH và tên GV",
            ]
        ]
    ];

    $courseshow = (in_array($crformat, BER_FILTER_COURSE) ? '' : 'berhide');
    $titleeloreports = '';

    $html = '<div id="elo_reports_diemquatrinh_advanced_search" class="elo_reports_diemquatrinh_advanced_search">';

    if ($dashboard) {
        //form
        $html .= '<form id="eloreportsform_advancedsearch" class="boxcontents advanced-search" action="' . $url . '" method="GET">';
    }
    //select
    $html .= '<div class="g-report-format">';
    $html .= '<select id="crformat" name="crformat" title="' . get_string('choosereportformat', 'block_elo_reports_diemquatrinh') . '" data-placeholder="' . get_string('choosereportformat', 'block_elo_reports_diemquatrinh') . '" class="chosen-select">';
    foreach ($options as $groups => $group) {
        $html .= '<optgroup label="' . $group["key"] . '">';
        foreach ($group["value"] as $key => $value) {
            $selected = ($crformat . '' == $key ? 'selected' : '');
            if ($selected != '')
                $titleeloreports = $value;
            $html .= '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
        }
        $html .= '</optgroup>';
    }
    $html .= '</select></div>';

    $html .= '<input type="hidden" id="berstartdate" name="berstartdate" value="">';
    $html .= '<input type="hidden" id="berenddate" name="berenddate" value="">';

    //
    //course
    $html .= '<div class="g-course mt-2">';
    ;
    $html .= '<select id="diemquatrinhcourseid" name="diemquatrinhcourseid" title="' . get_string('entercoursenameorshortname', 'block_elo_reports_diemquatrinh') . '" data-placeholder="' . get_string('entercoursenameorshortname', 'block_elo_reports_diemquatrinh') . '"multiple class="chosen-select-diemquatrinh">';
    $html .= '<optgroup label="' . get_string('entercoursenameorshortname', 'block_elo_reports_diemquatrinh') . '">';
    $html .= '<option value=""></option>';

    $sql = "SELECT id, fullname, shortname FROM {course} WHERE startdate > 0 AND enddate > 0 AND id != 1 AND visible <> 0 AND summary != '' AND shortname REGEXP '(.*)-(.*)' ORDER BY fullname ";
    $courses = $DB->get_records_sql($sql);
    foreach ($courses as $course) {
        $selected = ($course->id == $bercourseid ? ' selected' : '');
        $html .= '<option value="' . $course->id . '"' . $selected . '>' . $course->fullname . ' (' . $course->shortname . ')</option>';
    }

    $html .= '</optgroup></select>';
    $html .= '</div>';

    //end input search
    if ($dashboard) {
        //close form
        $html .= '</form>';
    }
    if ($dashboard == false) {
        //
        //titleeloreports
        $html .= '<div class="g-titlediemquatrinheloreports">';
        //open form
        $html .= '<form class="boxcontents" action="' . $CFG->wwwroot . '/blocks/elo_reports_diemquatrinh/action_redir.php" method="post" id="bercourseslmsform">';
        $html .= '<div class="boxcontents">';
        $html .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
        $html .= '<input type="hidden" name="returnto" value="' . s($PAGE->url->out(false)) . '" />';

        $html .= '<div class="boxcontents buttons"><div class="boxcontents form-inline">';
        $btnberexporttoexcel = get_string('btneloreports', 'block_elo_reports_diemquatrinh');
        $html .= html_writer::tag('button', $btnberexporttoexcel, array('id' => 'btnberexporttoexcel', 'class' => 'btn btn-primary btnberexporttoexcel mt-2', 'type' => 'button', 'title' => $titleeloreports));
        $html .= '</div></div>';
        $html .= '<input type="hidden" name="id" value="" />';
        $html .= '<noscript style="display:inline">';
        $html .= '<div class="boxcontents"><input type="submit" value="' . get_string('ok') . '" /></div>';
        $html .= '</noscript>';
        $html .= '</div>';
        //close form
        $html .= '</form>';
    }
    $html .= '</div>';
    return $html;
}

function block_elo_reports_diemquatrinh_view_ajax($paramsAjax) {
    global $CFG;
    require_once($CFG->dirroot . '/blocks/elo_reports_diemquatrinh/view_ajax.php');
    return block_elo_reports_diemquatrinh_response_to_js(block_elo_reports_diemquatrinh_getviewajax_datagird($paramsAjax));
}

function get_headers_diemquatrinh() {
    return array(
        get_string('no', 'block_elo_reports_diemquatrinh'),
        get_string('studentno', 'block_elo_reports_diemquatrinh'),
        get_string('ho', 'block_elo_reports_diemquatrinh'),
        get_string('ten', 'block_elo_reports_diemquatrinh'),
        get_string('tenlop', 'block_elo_reports_diemquatrinh'),
        get_string('manhomdangkymon', 'block_elo_reports_diemquatrinh'),
        get_string('diemso', 'block_elo_reports_diemquatrinh')
    );
}

function export_diemquatrinh_to_excel(array $exportdatas) {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php');
    require_once("$CFG->libdir/phpexcel/PHPExcel.php");
    $sheet = new PHPExcel();
    $date = date('d-m-Y');
    $i = 0;
    $stringname = '';
    $header = get_headers_diemquatrinh();
    $styleArray = array(
        'font' => array(
            'bold' => false,
            'size' => 12,
            'name' => 'Times New Roman'
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        )
    );
    $styleArray1 = array(
        'font' => array(
            'bold' => true,
            'size' => 16
    ));

    $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
            array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => true
            )
    );
    $sheet->getDefaultStyle()->applyFromArray($styleArray);

    foreach ($exportdatas as $name => $exportdata) {
        if(empty($exportdata->row)){// not data
            continue;
        }
        $MAX_COL_INDEX = count($header);
        $numrow = 10 + count($exportdata->row);
        if ($i === 0) {
            $sheet->setActiveSheetIndex($i);
        } else {
            $sheet->createSheet();
            $sheet->setActiveSheetIndex($i);
        }

        $activeSheet = $sheet->getActiveSheet();
        $activeSheet->setTitle($name);
        $activeSheet->getStyle('A7')->getFont()->setBold(true);
        $activeSheet->getStyle('D7')->getFont()->setBold(true);
        $activeSheet->getStyle('A8')->getFont()->setBold(true);
        $activeSheet->getStyle('A10:G10')->getFont()->setBold(true);
        $activeSheet->getStyle('A10:G10')->getFill()->applyFromArray(array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'a6a6a6'
            )
        ));
        $activeSheet->getStyle('E'.($numrow + 2).'')->getFont()->setBold(true);
        $activeSheet->getStyle('E'.($numrow + 7).'')->getFont()->setBold(true);
        for ($index = 0; $index < $MAX_COL_INDEX; $index++) {
            $col = PHPExcel_Cell::stringFromColumnIndex($index);
            $activeSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $activeSheet->mergeCells('A1:D1');
        $activeSheet->getCell('A1')->setValue('Trường Đại Học Mở TP Hồ Chí Minh');
        $activeSheet->mergeCells('A2:D2');
        $activeSheet->getCell('A2')->setValue('Trung tâm Đào tạo Trực tuyến');
        
        $activeSheet->mergeCells('A4:G4');
        $activeSheet->getStyle("A4:G4")->applyFromArray($styleArray1);
        $activeSheet->getRowDimension('4')->setRowHeight(20);
        $activeSheet->getCell('A4')->setValue('Danh Sách Sinh Viên');

        $activeSheet->mergeCells('A5:G5');
        $activeSheet->getStyle('A5:G5')->applyFromArray($styleArray1);
        $activeSheet->getRowDimension('5')->setRowHeight(20);
        $stringyearsemester = 'Điểm quá trình - Học Kỳ '.$exportdata->semester.' Năm Học '.$exportdata->year.'';
        $activeSheet->getCell('A5')->setValue($stringyearsemester);
        //full name
        $activeSheet->getCell('A7')->setValue('Môn học:');
        $activeSheet->getCell('B7')->setValue($exportdata->fullname); // full name
        //short name
        $activeSheet->getCell('D7')->setValue('MMH:');
        $activeSheet->getCell('E7')->setValue($exportdata->shortname); // short name
        //giang vien
        $activeSheet->getCell('A8')->setValue('Giảng viên:');
        $activeSheet->getCell('B8')->setValue($exportdata->teachername); // tên giảng viên

        $activeSheet->fromArray($header, null, 'A10'); //start A10
        $activeSheet->getRowDimension('10')->setRowHeight(40);
        $activeSheet->getStyle('C10:C' . $numrow . '')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $activeSheet->fromArray($exportdata->row, null, 'A11'); //start A11
        $activeSheet->getCell('E'.($numrow + 2).'')->setValue('GIẢNG VIÊN'); // col giảng viên string
        $activeSheet->getCell('E'.($numrow + 7).'')->setValue($exportdata->teachername); // name giảng viên string
        $i++;
        $stringname .= $name . '_';
    }
    $stringname = substr($stringname, 0, -1);
    $objWriter = PHPExcel_IOFactory::createWriter($sheet, 'Excel2007');
    $objWriter->save($CFG->tempdir . '/dqttheolop_' . $date);
    $tempzip = tempnam($CFG->tempdir . '/', 'rpexceldqttheolop_');
    $filesforzipping = array();
    $pathfilename = $stringname . '/Điểm quá trình theo lớp_' . date('Hi_Ymd') . '.xlsx';
    $filename = 'DiemQuaTrinhTheoLop_' . $date . '.zip';
    $filesforzipping[$pathfilename] = $CFG->tempdir . '/dqttheolop_' . $date;
    $zipper = new zip_packer();
    $zipper->archive_to_pathname($filesforzipping, $tempzip);
    send_temp_file($tempzip, $filename);
}
