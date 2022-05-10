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
 * Create elo reminder users.
 *
 * @package    block_elo_reminder_users
 * @copyright  2006 vinkmar
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');

// Check access.
if(!isloggedin() || !confirm_sesskey()){
    // print_error('invalidsection','block_elo_reminder_users');
    $result = [
        'error' => [
            'code' => 404,
            'message' => get_string('invalidsection','block_elo_reminder_users'),
            'errors' => []
        ]
    ];
    print responseToJs($result);
    die;
}

// Get the param of the preference to update
if(($listusersid = optional_param_array('listusersid', null, PARAM_INT)) == null){
    if(($listusersid = optional_param('listusersid', null, PARAM_INT)) == null){
        // print_error('invalidlistusersid','block_elo_reminder_users');
        $result = [
            'error' => [
                'code' => 404,
                'message' => get_string('invalidlistusersid','block_elo_reminder_users'),
                'errors' => []
            ]
        ];
        print responseToJs($result);
        die;
    }else $listusersid = array($listusersid);
}

$arrMailSucess = array();
$arrMailFailed = array();
$flagSucess = false;
foreach ($listusersid as $key => $value) {
    $userbyid = core_user::get_user($value);
    //$flagSucess = elo_mail_to_user($userbyid,$USER);
    $infoUser = [
        'id'=>$userbyid->id,
        'fullname'=>fullname($userbyid)
    ];
    if($flagSucess === true){
        $arrMailSucess[] = $infoUser;
    }else {
        $arrMailFailed[] = $infoUser;
    }
}

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
print responseToJs($result);
die;