<?php

// This file is part of Moodle - http://moodle.org/
// TOAN
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
 * @package    block_elo_import_data
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/blocks/elo_import_data/lib.php');

//Loc filter

class tool_import_utils {

    /**
     * This class can not be instantiated
     */
    private function __construct() {
        
    }

    public static function user_get_default_fields_elo() {
        return array('coursename', 'editingteacher', 'ho', 'ten', 'email', 'manhomdangkymon', 'studentno', 'tenlop', 'nganhhoc', 'khoa', 'ngaysinh', 'gioitinh', 'sodienthoai', 'loaivanbang', 'ghichu');
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
        if ($field->shortname === 'ngaysinh') {
            $formattedcolumn = self::$colmethodname($user, $field);
        } else if ($field->shortname === 'ghichu') {
            $formattedcolumn = self::$colmethodname($user, $field);
        } else {
            $fieldname = 'customfield_' . $field->customid;
            $formattedcolumn = $user->{$fieldname};
        }
        return $formattedcolumn;
    }

    public static function col_ngaysinh($user, $field) {
        $fieldname = 'customfield_' . $field->customid;
        return userdate($user->{$fieldname}, '%d/%m/%Y');
    }

    public static function col_ghichu($user, $field) {
        $fieldname = 'customfield_' . $field->customid;
        return html_to_text($user->{$fieldname});
    }

    public static function get_point_user_profile_fields_import() {
        global $DB;

        $fields = array();
        // Sets the list of custom profile fields
        $customprofilefields = array('masosinhvien', 'tenlop', 'manhomdangkymon', 'nganhhoc', 'khoa','firstname', 'lastname', 'gioitinh', 'ngaysinh', 'sodienthoai', 'loaivanbang', 'ghichu');
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

    public static function get_point_user_profile_fields() {
        global $DB;

        $fields = array();
//        require_once($CFG->dirroot . '/user/lib.php');                // Loads user_get_default_fields()
//        require_once($CFG->dirroot . '/user/profile/lib.php');        // Loads constants, such as PROFILE_VISIBLE_ALL
        $userdefaultfields = self::user_get_default_fields_elo();

        // Sets the list of profile fields
        $userprofilefields = array_map('trim', explode(',', 'coursename,editingteacher,ho,ten,email,manhomdangkymon'));
        if (!empty($userprofilefields)) {
            foreach ($userprofilefields as $field) {
                $field = trim($field);
                if (!in_array($field, $userdefaultfields)) {
                    continue;
                }
                $obj = new stdClass();
                $obj->customid = 0;
                $obj->shortname = $field;
                $obj->fullname = get_string($field, 'block_elo_import_data');
                $fields[] = $obj;
            }
        }

        // Sets the list of custom profile fields
        $customprofilefields = array('masosinhvien', 'tenlop', 'khoa', 'nganhhoc', 'ngaysinh', 'gioitinh', 'sodienthoai', 'loaivanbang', 'ghichu');
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

    public static function get_new_iid($type) {
        global $USER;

        $filename = make_temp_directory('csvimportpoint/' . $type . '/' . $USER->id);

        // use current (non-conflicting) time stamp
        $iiid = time();
        while (file_exists($filename . '/' . $iiid)) {
            $iiid--;
        }

        return $iiid;
    }

}

class gradeimport_csv_load_data_elo {

    /** @var string $error csv import error. */
    protected $error;

    /** @var int $iid Unique identifier for these csv records. */
    protected $iid;

    /** @var array $headers Column names for the data. */
    protected $headers;

    /** @var array $previewdata A subsection of the csv imported data. */
    protected $previewdata;
    // The map_user_data_with_value variables.

    /** @var array $newgrades Grades to be inserted into the gradebook. */
    protected $newgrades;

    /** @var int $studentid Student ID */
    protected $studentid;
    // The prepare_import_grade_data() variables.

    /** @var bool $status The current status of the import. True = okay, False = errors. */
    protected $status;

    /** @var int $importcode The code for this batch insert. */
    protected $importcode;

    /** @var array $gradebookerrors An array of errors from trying to import into the gradebook. */
    protected $gradebookerrors;

    /** @var array $newgradeitems An array of new grade items to be inserted into the gradebook. */
    protected $newgradeitems;
    protected $profilefields;

    /**
     * Load CSV content for previewing.
     *
     * @param string $text The grade data being imported.
     * @param string $encoding The type of encoding the file uses.
     * @param string $separator The separator being used to define each field.
     * @param int $previewrows How many rows are being previewed.
     */
    public function load_csv_content($text, $encoding, $separator, $previewrows) {
        $this->raise_limits();

        $this->iid = tool_import_utils::get_new_iid('point');
        $csvimport = new csv_import_reader_elo($this->iid, 'point');

        $csvimport->load_csv_content($text, $encoding, $separator);
        $this->error = $csvimport->get_error();

        // If there are no import errors then proceed.
        if (empty($this->error)) {

            // Get header (field names).
            $this->headers = $csvimport->get_columns();
            $this->trim_headers();

            $csvimport->init();
            $this->previewdata = array();

            for ($numlines = 0; $numlines <= $previewrows; $numlines++) {
                $lines = $csvimport->next();
                if ($lines) {
                    $this->previewdata[] = $lines;
                }
            }
        }
    }

    /**
     * Cleans the column headers from the CSV file.
     */
    protected function trim_headers() {
        foreach ($this->headers as $i => $h) {
            $h = trim($h); // Remove whitespace.
            $h = clean_param($h, PARAM_RAW); // Clean the header.
            $this->headers[$i] = $h;
        }
    }

    /**
     * Raises the php execution time and memory limits for importing the CSV file.
     */
    protected function raise_limits() {
        // Large files are likely to take their time and memory. Let PHP know
        // that we'll take longer, and that the process should be recycled soon
        // to free up memory.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);
    }

    /**
     * Inserts a record into the grade_import_values table. This also adds common record information.
     *
     * @param object $record The grade record being inserted into the database.
     * @param int $studentid The student ID.
     * @return bool|int true or insert id on success. Null if the grade value is too high.
     */
    protected function insert_point_record($record, $studentid) {//toan
        global $DB;
        $record->userid = $studentid;
        if (!empty($record->userid) && !empty($record->fieldid)) {
            return $DB->insert_record('user_info_data', $record);
        } else {
            $this->cleanup_import(get_string('invalidparam', 'block_elo_import_data'));
            return null;
        }
    }

    /**
     * Insert the new grade into the grade item buffer table.
     *
     * @param array $header The column headers from the CSV file.
     * @param int $key Current row identifier.
     * @param string $value The value for this row (final grade).
     * @return stdClass new grade that is ready for commiting to the gradebook.
     */
    protected function import_new_grade_item($header, $key, $value) {
        global $DB, $USER;
    }

    /**
     * Check that the user is in the system.
     *
     * @param string $value The value, from the csv file, being mapped to identify the user.
     * @param array $userfields Contains the field and label being mapped from.
     * @return int Returns the user ID if it exists, otherwise null.
     */
    protected function check_user_exists($value, $userfields) {
        global $DB;

        $usercheckproblem = false;
        $user = null;
        // The user may use the incorrect field to match the user. This could result in an exception.
        try {
            $user = $DB->get_record('user', array($userfields['field'] => $value));
        } catch (Exception $e) {
            $usercheckproblem = true;
        }
        // Field may be fine, but no records were returned.
        if (!$user || $usercheckproblem) {
            $usermappingerrorobj = new stdClass();
            $usermappingerrorobj->field = $userfields['label'];
            $usermappingerrorobj->value = $value;
            $this->cleanup_import(get_string('usermappingerrorelo', 'block_elo_import_data', $usermappingerrorobj));
            unset($usermappingerrorobj);
            return null;
        }
        return $user->id;
    }

    /**
     * This updates existing grade items.
     *

     * @param array $map Mapping information provided by the user.
     * @param int $key The line that we are currently working on.
     * @param bool $verbosescales Form setting for grading with scales.
     * @param string $value The grade value.
     * @return array grades to be updated.
     */
    protected function update_user_info_data($map, $key, $verbosescales, $value, $fieldid) {

        $newrecord = new stdClass();
        $newrecord->fieldid = $fieldid;
        $newrecord->data = format_string($value);
        $newrecord->dataformat = 0;
        $this->newgrades[] = $newrecord;
        return $this->newgrades;
    }

    /**
     * Clean up failed CSV grade import. Clears the temp table for inserting grades.
     *
     * @param string $notification The error message to display from the unsuccessful grade import.
     */
    protected function cleanup_import($notification) {
        $this->status = false;
        $this->gradebookerrors[] = $notification;
    }

    /**
     * Check user mapping.
     *
     * @param string $mappingidentifier The user field that we are matching together.
     * @param string $value The value we are checking / importing.
     * @param array $header The column headers of the csv file.
     * @param array $map Mapping information provided by the user.
     * @param int $key Current row identifier.
     * @param int $feedbackgradeid The ID of the grade item that the feedback relates to.
     * @param bool $verbosescales Form setting for grading with scales.
     */
    protected function map_user_data_with_value($mappingidentifier, $value, $header, $map, $key, $verbosescales) {

        // Fields that the user can be mapped from.
        $userfields = array(
            'userid' => array(
                'field' => 'id',
                'label' => 'id',
            ),
            'useridnumber' => array(
                'field' => 'idnumber',
                'label' => 'idnumber',
            ),
            'useremail' => array(
                'field' => 'email',
                'label' => 'email address',
            ),
            'username' => array(
                'field' => 'username',
                'label' => 'username',
            ),
        );

        switch ($mappingidentifier) {
//            case 'userid':
            case 'useridnumber':
            case 'useremail':
//            case 'username':
                // Skip invalid row with blank user field.
                if (!empty($value)) {
                    $this->studentid = $this->check_user_exists($value, $userfields[$mappingidentifier]);
                }
                break;
            default:
                // Existing grade items.
                if (!empty($map[$key])) {
                    $this->newgrades = $this->update_user_info_data($map, $key, $verbosescales, $value, $mappingidentifier);
                }
                // Otherwise, we ignore this column altogether because user has chosen
                // to ignore them (e.g. institution, address etc).
                break;
        }
    }

    public function init() {
        global $DB;

        $this->profilefields = array();
        // Sets the list of custom profile fields
        $customprofilefields = array('masosinhvien', 'tenlop', 'manhomdangkymon', 'nganhhoc', 'khoa','firstname', 'lastname','gioitinh', 'ngaysinh', 'sodienthoai', 'loaivanbang', 'ghichu');
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
                $this->profilefields[$id] = format_string($field->shortname);
            }
        }
        return $this->profilefields;
    }

    /**
     * Checks and prepares grade data for inserting into the gradebook.
     *
     * @param array $header Column headers of the CSV file.
     * @param object $formdata Mapping information from the preview page.
     * @param object $csvimport csv import reader object for iterating over the imported CSV file.
     * @param bool $separatemode If we have groups are they separate?
     * @param mixed $currentgroup current group information.
     * @param bool $verbosescales Form setting for grading with scales.
     * @return bool True if the status for importing is okay, false if there are errors.
     */
    public function prepare_import_grade_data($header, $formdata, $csvimport, $separatemode, $currentgroup, $verbosescales) {
        global $DB;

        // The import code is used for inserting data into the grade tables.
        $this->importcode = $formdata->importcode;
        $this->status = true;
        $this->headers = $header;
        $this->studentid = null;
        $this->gradebookerrors = null;
        $firstlaststr = array('firstname','lastname');
        // Temporary array to keep track of what new headers are processed.
        $this->newgradeitems = array();
        $this->trim_headers();
        $timeexportkey = null;
        $map = array();
        // Loops mapping_0, mapping_1 .. mapping_n and construct $map array.
        foreach ($header as $i => $head) {
            if (isset($formdata->{'mapping_' . $i})) {
                $map[$i] = $formdata->{'mapping_' . $i};
            }
            if ($head == get_string('timeexported', 'gradeexport_txt')) {
                $timeexportkey = $i;
            }
        }

        // If mapping information is supplied.
        $map[clean_param($formdata->mapfrom, PARAM_RAW)] = clean_param($formdata->mapto, PARAM_RAW);

        // Check for mapto collisions.
        $maperrors = array();
        foreach ($map as $i => $j) {
            if ($j == 0) {
                // You can have multiple ignores.
                continue;
            } else {
                if (!isset($maperrors[$j])) {
                    $maperrors[$j] = true;
                } else {
                    // Collision.
                    print_error('cannotmapfield', '', '', $j);
                }
            }
        }

        $this->raise_limits();

        $csvimport->init();

        while ($line = $csvimport->next()) {
            if (count($line) <= 1) {
                // There is no data on this line, move on.
                continue;
            }

            // Array to hold all grades to be inserted.
            $this->newgrades = array();
            // Array to hold all feedback.
            // Each line is a student record.
            foreach ($line as $key => $value) {
                $value = clean_param($value, PARAM_RAW);
                $value = trim($value);
                // Explode the mapping for feedback into a label 'feedback' and the identifying number.
                $mappingbase = explode("_", $map[$key]);
                $mappingidentifier = $mappingbase[0];
                $this->map_user_data_with_value($mappingidentifier, $value, $header, $map, $key, $verbosescales);
                if ($this->status === false) {
                    return $this->status;
                }
            }

            // No user mapping supplied at all, or user mapping failed.
            if (empty($this->studentid) || !is_numeric($this->studentid)) {
                // User not found, abort whole import.
                $this->cleanup_import(get_string('usermappingerrorusernotfound', 'grades'));
                break;
            }
            // Updating/inserting all comments here.
            if ($this->status and!empty($this->newgrades)) {
                foreach ($this->newgrades as $newgrade) {

                    $sql = "SELECT *
                              FROM {user_info_data}
                             WHERE userid=? AND fieldid=?";
                    $filteridentifier = trim($this->profilefields[$newgrade->fieldid]);
                    if (in_array($filteridentifier, $firstlaststr)) {
                        if ($user = $DB->get_record('user', array('id' => $this->studentid))) {
                            if (trim(strtolower($user->{$filteridentifier})) === trim(strtolower($newgrade->data))) {//field mới bằng field cũ
                                continue;// no update
                            }
                            else {
                                $user->{$filteridentifier} = strtoupper($newgrade->data);
                                $DB->update_record('user', $user);// update user
                                continue;
                            }
                        }
                    }
                    if ($oldrecord = $DB->get_record_sql($sql, array($this->studentid, $newgrade->fieldid))) {
                        switch ($filteridentifier) {
                            case 'masosinhvien':// add data to file munber id and extra filed masosinhvien
                                if (trim(strtolower($oldrecord->data)) === trim(strtolower($newgrade->data))) {//field mới bằng field cũ 
                                    continue;
                                } else { // field mới khác field cũ
                                    $pos = strpos(trim(strtolower($oldrecord->data)), trim(strtolower($newgrade->data)));
                                    if ($pos !== false) {
                                        continue;
                                    } else {
                                        self::update_user_data_elo($oldrecord, $newgrade, true);
                                    }
                                }
                                break;
                            case 'tenlop':
                            case 'manhomdangkymon':
                            case 'nganhhoc':
                            case 'khoa':
                                if (trim(strtolower($oldrecord->data)) === trim(strtolower($newgrade->data))) {//field mới bằng field cũ 
                                    continue;
                                } else { // field mới khác field cũ
                                    $pos = strpos(trim(strtolower($oldrecord->data)), trim(strtolower($newgrade->data)));
                                    if ($pos !== false) {
                                        continue;
                                    } else {
                                        self::update_user_data_elo($oldrecord, $newgrade, false);
                                    }
                                }
                                break;
                            default:
                                if (trim(strtolower($oldrecord->data)) === trim(strtolower($newgrade->data))) {
                                    continue;
                                }
                                $newgrade->id = $oldrecord->id;
                                $newgrade->userid = $this->studentid;
                                $DB->update_record('user_info_data', $newgrade);
                                break;
                        }
                    } else {
                        // The grade item for this is not updated.
                        switch ($filteridentifier) {
                            case 'masosinhvien':
                                $user = $DB->get_record('user', array('id' => $this->studentid));
                                $user->idnumber = trim($newgrade->data);
                                $DB->update_record('user', $user);
                                break;
                            default :
                                $insertid = self::insert_point_record($newgrade, $this->studentid);
                                // Check to see if the insert was successful.
                                if (empty($insertid)) {
                                    return null;
                                }
                                break;
                        }
                    }
                }
            }
        }
        return $this->status;
    }

    protected function update_user_data_elo($oldrecord, $newgrade, $isupdateusertbl = false) {
        global $DB;
        $newgrade->id = $oldrecord->id;
        $newgrade->userid = $this->studentid;
        $newgrade->data = trim($this->format_string_elo($oldrecord->data, $newgrade->data));
        $DB->update_record('user_info_data', $newgrade);
        //update table course field idnumber
        if ($isupdateusertbl) {
            $sqluser = "SELECT * FROM {user} WHERE id=?";
            $olduser = $DB->get_record_sql($sqluser, array($this->studentid));
            $olduser->idnumber = $newgrade->data;
            $DB->update_record('user', $olduser);
        }
    }

    protected function update_user_data_tbl($oldrecord, $newgrade) {
        global $DB;
        $oldrecord->idnumber = trim($this->format_string_elo($oldrecord->idnumber, $newgrade->data));
        $DB->update_record('user', $oldrecord);
    }

    public function format_string_elo($oldrecord, $newgrade) {
        $string = '';
        $string .= $oldrecord . "\r\n" . $newgrade;
        return $string;
//        return substr($string, 0, -2);
    }

    /**
     * Returns the headers parameter for this class.
     *
     * @return array returns headers parameter for this class.
     */
    public function get_headers() {
        return $this->headers;
    }

    /**
     * Returns the error parameter for this class.
     *
     * @return string returns error parameter for this class.
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Returns the iid parameter for this class.
     *
     * @return int returns iid parameter for this class.
     */
    public function get_iid() {
        return $this->iid;
    }

    /**
     * Returns the preview_data parameter for this class.
     *
     * @return array returns previewdata parameter for this class.
     */
    public function get_previewdata() {
        return $this->previewdata;
    }

    /**
     * Returns the gradebookerrors parameter for this class.
     *
     * @return array returns gradebookerrors parameter for this class.
     */
    public function get_gradebookerrors() {
        return $this->gradebookerrors;
    }

}

class csv_import_reader_elo {

    /**
     * @var int import identifier
     */
    private $_iid;

    /**
     * @var string which script imports?
     */
    private $_type;

    /**
     * @var string|null Null if ok, error msg otherwise
     */
    private $_error;

    /**
     * @var array cached columns
     */
    private $_columns;

    /**
     * @var object file handle used during import
     */
    private $_fp;

    /**
     * Contructor
     *
     * @param int $iid import identifier
     * @param string $type which script imports?
     */
    public function __construct($iid, $type) {
        $this->_iid = $iid;
        $this->_type = $type;
    }

    /**
     * Make sure the file is closed when this object is discarded.
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Parse this content
     *
     * @param string $content the content to parse.
     * @param string $encoding content encoding
     * @param string $delimiter_name separator (comma, semicolon, colon, cfg)
     * @param string $column_validation name of function for columns validation, must have one param $columns
     * @param string $enclosure field wrapper. One character only.
     * @return bool false if error, count of data lines if ok; use get_error() to get error string
     */
    public function load_csv_content($content, $encoding, $delimiter_name, $column_validation = null, $enclosure = '"') {
        global $USER, $CFG;

        $this->close();
        $this->_error = null;

        $content = core_text::convert($content, $encoding, 'utf-8');
        // remove Unicode BOM from first line
        $content = core_text::trim_utf8_bom($content);
        // Fix mac/dos newlines
        $content = preg_replace('!\r\n?!', "\n", $content);
        // Remove any spaces or new lines at the end of the file.
        if ($delimiter_name == 'tab') {
            // trim() by default removes tabs from the end of content which is undesirable in a tab separated file.
            $content = trim($content, chr(0x20) . chr(0x0A) . chr(0x0D) . chr(0x00) . chr(0x0B));
        } else {
            $content = trim($content);
        }

        $csv_delimiter = csv_import_reader_elo::get_delimiter($delimiter_name);

        // Create a temporary file and store the csv file there,
        // do not try using fgetcsv() because there is nothing
        // to split rows properly - fgetcsv() itself can not do it.
        $tempfile = tempnam(make_temp_directory('/csvimportpoint'), 'tmp');
        if (!$fp = fopen($tempfile, 'w+b')) {
            $this->_error = get_string('cannotsavedata', 'error');
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);
        // Create an array to store the imported data for error checking.
        $columns = array();
        // str_getcsv doesn't iterate through the csv data properly. It has
        // problems with line returns.
        while ($fgetdata = fgetcsv($fp, 0, $csv_delimiter, $enclosure)) {
            // Check to see if we have an empty line.
            if (count($fgetdata) == 1) {
                if ($fgetdata[0] !== null) {
                    // The element has data. Add it to the array.
                    $columns[] = $fgetdata;
                }
            } else {
                $columns[] = $fgetdata;
            }
        }
        $col_count = 0;

        // process header - list of columns
        if (!isset($columns[0])) {
            $this->_error = get_string('csvemptyfile', 'error');
            fclose($fp);
            unlink($tempfile);
            return false;
        } else {
            $col_count = count($columns[0]);
        }

        // Column validation.
        if ($column_validation) {
            $result = $column_validation($columns[0]);
            if ($result !== true) {
                $this->_error = $result;
                fclose($fp);
                unlink($tempfile);
                return false;
            }
        }

        $this->_columns = $columns[0]; // cached columns
        // check to make sure that the data columns match up with the headers.
        foreach ($columns as $rowdata) {
            if (count($rowdata) !== $col_count) {
                $this->_error = get_string('csvweirdcolumns', 'error');
                fclose($fp);
                unlink($tempfile);
                $this->cleanup();
                return false;
            }
        }

        $filename = $CFG->tempdir . '/csvimportpoint/' . $this->_type . '/' . $USER->id . '/' . $this->_iid;
        $filepointer = fopen($filename, "w");
        // The information has been stored in csv format, as serialized data has issues
        // with special characters and line returns.
        $storedata = csv_export_writer_elo::print_array($columns, ',', '"', true);
        fwrite($filepointer, $storedata);

        fclose($fp);
        unlink($tempfile);
        fclose($filepointer);

        $datacount = count($columns);
        return $datacount;
    }

    /**
     * Returns list of columns
     *
     * @return array
     */
    public function get_columns() {
        if (isset($this->_columns)) {
            return $this->_columns;
        }

        global $USER, $CFG;

        $filename = $CFG->tempdir . '/csvimportpoint/' . $this->_type . '/' . $USER->id . '/' . $this->_iid;
        if (!file_exists($filename)) {
            return false;
        }
        $fp = fopen($filename, "r");
        $line = fgetcsv($fp);
        fclose($fp);
        if ($line === false) {
            return false;
        }
        $this->_columns = $line;
        return $this->_columns;
    }

    /**
     * Init iterator.
     *
     * @global object
     * @global object
     * @return bool Success
     */
    public function init() {
        global $CFG, $USER;

        if (!empty($this->_fp)) {
            $this->close();
        }
        $filename = $CFG->tempdir . '/csvimportpoint/' . $this->_type . '/' . $USER->id . '/' . $this->_iid;
        if (!file_exists($filename)) {
            return false;
        }
        if (!$this->_fp = fopen($filename, "r")) {
            return false;
        }
        //skip header
        return (fgetcsv($this->_fp) !== false);
    }

    /**
     * Get next line
     *
     * @return mixed false, or an array of values
     */
    public function next() {
        if (empty($this->_fp) or feof($this->_fp)) {
            return false;
        }
        if ($ser = fgetcsv($this->_fp)) {
            return $ser;
        } else {
            return false;
        }
    }

    /**
     * Release iteration related resources
     *
     * @return void
     */
    public function close() {
        if (!empty($this->_fp)) {
            fclose($this->_fp);
            $this->_fp = null;
        }
    }

    /**
     * Get last error
     *
     * @return string error text of null if none
     */
    public function get_error() {
        return $this->_error;
    }

    /**
     * Cleanup temporary data
     *
     * @global object
     * @global object
     * @param boolean $full true means do a full cleanup - all sessions for current user, false only the active iid
     */
    public function cleanup($full = false) {
        global $USER, $CFG;

        if ($full) {
            @remove_dir($CFG->tempdir . '/csvimportpoint/' . $this->_type . '/' . $USER->id);
        } else {
            @unlink($CFG->tempdir . '/csvimportpoint/' . $this->_type . '/' . $USER->id . '/' . $this->_iid);
        }
    }

    /**
     * Get list of cvs delimiters
     *
     * @return array suitable for selection box
     */
    public static function get_delimiter_list() {
        global $CFG;
        $delimiters = array('comma' => ',', 'semicolon' => ';', 'colon' => ':', 'tab' => '\\t');
        if (isset($CFG->CSV_DELIMITER) and strlen($CFG->CSV_DELIMITER) === 1 and!in_array($CFG->CSV_DELIMITER, $delimiters)) {
            $delimiters['cfg'] = $CFG->CSV_DELIMITER;
        }
        return $delimiters;
    }

    /**
     * Get delimiter character
     *
     * @param string separator name
     * @return string delimiter char
     */
    public static function get_delimiter($delimiter_name) {
        global $CFG;
        switch ($delimiter_name) {
            case 'colon': return ':';
            case 'semicolon': return ';';
            case 'tab': return "\t";
            case 'cfg': if (isset($CFG->CSV_DELIMITER)) {
                    return $CFG->CSV_DELIMITER;
                } // no break; fall back to comma
            case 'comma': return ',';
            default : return ',';  // If anything else comes in, default to comma.
        }
    }

    /**
     * Get encoded delimiter character
     *
     * @global object
     * @param string separator name
     * @return string encoded delimiter char
     */
    public static function get_encoded_delimiter($delimiter_name) {
        global $CFG;
        if ($delimiter_name == 'cfg' and isset($CFG->CSV_ENCODE)) {
            return $CFG->CSV_ENCODE;
        }
        $delimiter = csv_import_reader_elo::get_delimiter($delimiter_name);
        return '&#' . ord($delimiter);
    }

    /**
     * Create new import id
     *
     * @global object
     * @param string who imports?
     * @return int iid
     */
    public static function get_new_iid($type) {
        global $USER;

        $filename = make_temp_directory('csvimportpoint/' . $type . '/' . $USER->id);

        // use current (non-conflicting) time stamp
        $iiid = time();
        while (file_exists($filename . '/' . $iiid)) {
            $iiid--;
        }

        return $iiid;
    }

}

class csv_export_writer_elo {

    /**
     * @var string $delimiter  The name of the delimiter. Supported types(comma, tab, semicolon, colon, cfg)
     */
    var $delimiter;

    /**
     * @var string $csvenclosure  How fields with spaces and commas are enclosed.
     */
    var $csvenclosure;

    /**
     * @var string $mimetype  Mimetype of the file we are exporting.
     */
    var $mimetype;

    /**
     * @var string $filename  The filename for the csv file to be downloaded.
     */
    var $filename;

    /**
     * @var string $path  The directory path for storing the temporary csv file.
     */
    var $path;

    /**
     * @var resource $fp  File pointer for the csv file.
     */
    protected $fp;

    /**
     * Constructor for the csv export reader
     *
     * @param string $delimiter      The name of the character used to seperate fields. Supported types(comma, tab, semicolon, colon, cfg)
     * @param string $enclosure      The character used for determining the enclosures.
     * @param string $mimetype       Mime type of the file that we are exporting.
     */
    public function __construct($delimiter = 'comma', $enclosure = '"', $mimetype = 'application/download') {
        $this->delimiter = $delimiter;
        // Check that the enclosure is a single character.
        if (strlen($enclosure) == 1) {
            $this->csvenclosure = $enclosure;
        } else {
            $this->csvenclosure = '"';
        }
        $this->filename = "Moodle-data-export.csv";
        $this->mimetype = $mimetype;
    }

    /**
     * Set the file path to the temporary file.
     */
    protected function set_temp_file_path() {
        global $USER, $CFG;
        make_temp_directory('csvimportpoint/' . $USER->id);
        $path = $CFG->tempdir . '/csvimportpoint/' . $USER->id . '/' . $this->filename;
        // Check to see if the file exists, if so delete it.
        if (file_exists($path)) {
            unlink($path);
        }
        $this->path = $path;
    }

    /**
     * Add data to the temporary file in csv format
     *
     * @param array $row  An array of values.
     */
    public function add_data($row) {
        if (!isset($this->path)) {
            $this->set_temp_file_path();
            $this->fp = fopen($this->path, 'w+');
        }
        $delimiter = csv_import_reader_elo::get_delimiter($this->delimiter);
        fputcsv($this->fp, $row, $delimiter, $this->csvenclosure);
    }

    /**
     * Echos or returns a csv data line by line for displaying.
     *
     * @param bool $return  Set to true to return a string with the csv data.
     * @return string       csv data.
     */
    public function print_csv_data($return = false) {
        fseek($this->fp, 0);
        $returnstring = '';
        while (($content = fgets($this->fp)) !== false) {
            if (!$return) {
                echo $content;
            } else {
                $returnstring .= $content;
            }
        }
        if ($return) {
            return $returnstring;
        }
    }

    /**
     * Set the filename for the uploaded csv file
     *
     * @param string $dataname    The name of the module.
     * @param string $extenstion  File extension for the file.
     */
    public function set_filename($dataname, $extension = '.csv') {
        $filename = clean_filename($dataname);
        $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
        $filename .= clean_filename("-{$this->delimiter}_separated");
        $filename .= $extension;
        $this->filename = $filename;
    }

    /**
     * Output file headers to initialise the download of the file.
     */
    protected function send_header() {
        global $CFG;

        if (defined('BEHAT_SITE_RUNNING')) {
            // For text based formats - we cannot test the output with behat if we force a file download.
            return;
        }
        if (is_https()) { // HTTPS sites - watch out for IE! KB812935 and KB316431.
            header('Cache-Control: max-age=10');
            header('Pragma: ');
        } else { //normal http - prevent caching at all cost
            header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
        }
        header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        header("Content-Type: $this->mimetype\n");
        header("Content-Disposition: attachment; filename=\"$this->filename\"");
    }

    /**
     * Download the csv file.
     */
    public function download_file() {
        $this->send_header();
        $this->print_csv_data();
        exit;
    }

    /**
     * Creates a file for downloading an array into a deliminated format.
     * This function is useful if you are happy with the defaults and all of your
     * information is in one array.
     *
     * @param string $filename    The filename of the file being created.
     * @param array $records      An array of information to be converted.
     * @param string $delimiter   The name of the delimiter. Supported types(comma, tab, semicolon, colon, cfg)
     * @param string $enclosure   How speical fields are enclosed.
     */
    public static function download_array($filename, array &$records, $delimiter = 'comma', $enclosure = '"') {
        $csvdata = new csv_export_writer_elo($delimiter, $enclosure);
        $csvdata->set_filename($filename);
        foreach ($records as $row) {
            $csvdata->add_data($row);
        }
        $csvdata->download_file();
    }

    /**
     * This will convert an array of values into a deliminated string.
     * Like the above function, this is for convenience.
     *
     * @param array $records     An array of information to be converted.
     * @param string $delimiter  The name of the delimiter. Supported types(comma, tab, semicolon, colon, cfg)
     * @param string $enclosure  How speical fields are enclosed.
     * @param bool $return       If true will return a string with the csv data.
     * @return string            csv data.
     */
    public static function print_array(array &$records, $delimiter = 'comma', $enclosure = '"', $return = false) {
        $csvdata = new csv_export_writer_elo($delimiter, $enclosure);
        foreach ($records as $row) {
            $csvdata->add_data($row);
        }
        $data = $csvdata->print_csv_data($return);
        if ($return) {
            return $data;
        }
    }

    /**
     * Make sure that everything is closed when we are finished.
     */
    public function __destruct() {
        fclose($this->fp);
        unlink($this->path);
    }

}

function get_new_importcode_elo() {
    $importcode = time();
    return $importcode;
}

function point_import_commit() {
    global $OUTPUT;
    echo $OUTPUT->notification(get_string('importsuccess', 'block_elo_import_data'), 'notifysuccess');
    echo $OUTPUT->continue_button(new moodle_url('/blocks/elo_import_data/import.php'));
    return true;
}
