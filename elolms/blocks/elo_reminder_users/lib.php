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
 * @package    block_elo_reminder_users
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Callback to define user preferences.
 *
 * @return array
 */
function block_elo_reminder_users_user_preferences() {
    $preferences = array();
    // $preferences['block_elo_reminder_users_uservisibility'] = array(
    //     'type' => PARAM_INT,
    //     'null' => NULL_NOT_ALLOWED,
    //     'default' => 1,
    //     'choices' => array(0, 1),
    //     'permissioncallback' => function($user, $preferencename) {
    //         global $USER;
    //         return $user->id == $USER->id;
    //     }
    // );
    $preferences['block_elo_reminder_users_mailtouser'] = array(
        'type' => PARAM_INT,
        'null' => NULL_NOT_ALLOWED,
        'default' => 1,
        'choices' => array(0, 1),
        'permissioncallback' => function($user, $preferencename) {
            global $USER;
            return $user->id == $USER->id;
        }
    );

    return $preferences;
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
function minutes_to_timelib($minutes) {

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
    return get_string('eloperiod','block_elo_reminder_users',$string);
}
//End Nhien elo 18_09_2019

//Nhien elo 11_09_2019
function elo_mail_to_user($touser,$fromuser){
    global $OUTPUT, $SITE;

    $message = new stdClass();
    // $message->courseid = $SITE->id;
    // $message->component = 'block_elo_reminder_users';
    // $message->name = 'reminder';
    // $message->userfrom = $fromuser;
    // $message->replyto = $fromuser->email;
    // $message->replytoname = fullname($fromuser->email);

    // Prepare the context data for the email message body.
    $messagetextdata = [
        'fullname' => fullname($touser),
        'message' => get_string('reminder:message', 'block_elo_reminder_users'),
        'sign' => get_string('reminder:sign', 'block_elo_reminder_users')
    ];

    $subject = get_string('reminder:subject', 'block_elo_reminder_users');
    // $message->subject           = $subject;
    // $message->fullmessageformat = FORMAT_HTML;
    // $message->userto = $touser;

    // Render message email body.
    $messagehtml = $OUTPUT->render_from_template('block_elo_reminder_users/email_reminder', $messagetextdata);
    $message->fullmessage = html_to_text($messagehtml);
    $message->fullmessagehtml = $messagehtml;

    // print_object($message);

    $success = email_to_user($touser, $fromuser, $subject, $message->fullmessage, $messagehtml);

    return $success;
}
//End Nhien elo 11_09_2019

//Nhien elo 11_09_2019
function response_to_js($result){
    // header("Content-Type: application/json; charset=UTF-8");
    return json_encode($result,JSON_FORCE_OBJECT);
}
//End Nhien elo 11_09_2019


//Nhien elo 11_09_2019
function elo_send_mail($params){
// $DB->insert_records($table, $dataobjects);

    global $DB, $USER;
    $arrMailSucess = array();
    $arrMailFailed = array();

//    foreach ($params as $key => $infoUser) {
        $lastaccess = $params['lastaccess'];
        $userbyid = core_user::get_user($params['userid']);
        $courseid = $params['courseid']; // Course id.
        $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

        $flagSucess = false;
        $lastsentmail = '';

        try {
            try {
                $transaction = $DB->start_delegated_transaction();
                // Do something here.

                //Send mail
                $flagSucess = $userbyid->emailstop == 0 ? elo_mail_to_user($userbyid,$USER) : false;
                // $flagSucess = true;
                $now = time();
                //Insert DB
                $dataobject = new stdClass();
                $dataobject->userfromid = $USER->id;
                $dataobject->usertoid = $userbyid->id;
                $dataobject->courseid = $course->id;
                $dataobject->status = ($flagSucess ? 1 : 0);
                $dataobject->lastaccess = $lastaccess;
                $dataobject->timecreated = $now;
                $dataobject->timemodified = $now;
                $index = $DB->insert_record('block_elo_reminder_users',$dataobject);
                if (!$index) {
                    $lastsentmail = format_time(time() - $now);
                }
                //Valid
                $transaction->allow_commit();
            } catch (Exception $e) {
                // Make sure transaction is valid.
                if (!empty($transaction) && !$transaction->is_disposed()) {
                    $transaction->rollback($e);
                }
            }
        } catch (Exception $e) {
            // Silence the rollback exception or do something else.
        }

        $infoUser = [
            'id'=>$userbyid->id,
            'fullname'=>fullname($userbyid),
            'lastsentmail'=>$lastsentmail
        ];
        if($flagSucess === true){
            $arrMailSucess[] = $infoUser;
        }else {
            $arrMailFailed[] = $infoUser;
        }
//    }//End foreach

    if(count($arrMailSucess) > 0){
        $result = [
            'success' => [
                'code' => 200,
                'message' => get_string('send:success','block_elo_reminder_users'),
                'data' => [
                    'listusers' => $arrMailSucess,
                ],
                'errors' => [
                    'listusers' => $arrMailFailed,
                ]
            ]
        ];
    }
    else{
        $result = [
            'error' => [
                'code' => 405,
                'message' => get_string('send:failed','block_elo_reminder_users'),
                'errors' => [
                    'listusers' => $arrMailFailed,
                ]
            ]
        ];
    }
    return response_to_js($result);
}
//End Nhien elo 11_09_2019


function renderhtmlhistories($users){
    $html = '';
    $html .= '<table>';
    $html .= '<thead><th>No.</th><th>By whom</th><th>Course name</th><th>Time of sending</th></thead>';
    $html .= '<tbody>';
    $no = 0;
    $strftimedatetimeshort = get_string("strftimedatetimeshort");
    foreach($users as $user){
        $no++;
        $html .= '<tr>';
        $html .= '<td>'.$no.'</td>';
        $html .= '<td>'.$user->firstname . ' ' .$user->lastname.'</td>';
        $html .= '<td>'.$user->coursefullname.'</td>';
        $html .= '<td>'.userdate($user->timemodified, $strftimedatetimeshort).'</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

function addrownumber($users){
    $listnew = array();
    $row = 0;
    foreach ($users as $user) {
        $row++;
        $newrow = new stdClass();
        $newrow->rownum = $row;
        foreach ($user as $key => $value) {
            $newrow->{$key} = $value;
        }
        $listnew[] = $newrow;
        
    }
    return $listnew;
}

function elo_view_mail_histories($params){
    global $DB;

    $userid = $params['userid'];
    $courseid = $params['courseid'];

    $params = array('userid'=>$userid,'courseid'=>$courseid);
    $sql = "SELECT beru.*, u.firstname, u.lastname, c.fullname as coursefullname, c.shortname
            FROM {block_elo_reminder_users} beru
            LEFT JOIN {user} u ON u.id = beru.userfromid
            LEFT JOIN {course} c ON c.id = beru.courseid
            WHERE usertoid = $userid AND beru.status = 1
            ORDER BY beru.timemodified DESC
        ";
    $users = $DB->get_records_sql($sql, null, 0, 50);
    
    $users = addrownumber($users);

    $columns = [
        get_string('histories:no','block_elo_reminder_users'),
        get_string('histories:bywhom','block_elo_reminder_users'),
        get_string('histories:coursename','block_elo_reminder_users'),
        get_string('histories:timeofsending','block_elo_reminder_users')
    ];

    if(count($users) > 0){
        $result = [
            'success' => [
                'code' => 200,
                'message' => get_string('view:success','block_elo_reminder_users'),
                'data' => [
                    'columns' => $columns,
                    'listusers' => $users
                ]
            ]
        ];
    }
    else{
        $result = [
            'error' => [
                'code' => 405,
                'message' => get_string('view:failed','block_elo_reminder_users'),
                'errors' => []
            ]
        ];
    }

    return response_to_js($result);
}


function elo_export_rowcolumns($row){
    $columns = array(
        'firstname',
        'lastname',
        'email'
    );
    $newrow = new stdClass();
    foreach($row as $col => $value){
        if(in_array($col, $columns)){
            $newrow->{$col} = $value;
        }
    }
    return $newrow;
}