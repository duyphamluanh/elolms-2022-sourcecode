<?php
/**
 * PLUGIN external file
 *
 * @package    block_plugin
 * @copyright  2019 Elo Tech
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('lib.php');
require_once("$CFG->libdir/externallib.php");


class block_elo_reminder_users_external extends external_api {
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function mailtouser_parameters() {
        // mailtouser_parameters() always return an external_function_parameters(). 
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
                // a external_description can be: external_value, external_single_structure or external_multiple structure
                array(
                    'value' => new external_value(PARAM_INT, 'Status is invalid'),
                    'userid' => new external_value(PARAM_INT, 'User Id is invalid'),
                    'lastaccess' => new external_value(PARAM_INT, 'Last access is invalid'),
                    'courseid' => new external_value(PARAM_INT, 'Course is invalid')
                ) 
        );
    }
    
    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function mailtouser_returns() { // BIG concerns here. See below.
        return new external_single_structure(
            array(
                'elodata' => new external_value(PARAM_TEXT, 'Invalid JSON'),
            )
        );
    }
    
    /**
     * The function itself
     * @return string welcome message
     */
    public static function mailtouser($param_value,$param_userid,$param_lastaccess,$param_courseid) {
        //Note: don't forget to validate the context and check capabilities
        $params = self::validate_parameters(self::mailtouser_parameters(),
                    array(
                        'value' => $param_value,
                        'userid' => $param_userid,
                        'lastaccess' => $param_lastaccess,
                        'courseid' => $param_courseid
                    )
                  );
        $params['elodata'] = elo_send_mail($params);
        return $params;
 
//        foreach ($args as $user) {
            // all the parameter/behavioural checks and security constrainsts go here,
            // throwing exceptions if neeeded and and calling low level (userlib)
            // add_user() function that will be one in charge of the functionality without
            // further checks.
//        }
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function viewhistories_parameters() {
        // mailtouser_parameters() always return an external_function_parameters(). 
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
                // a external_description can be: external_value, external_single_structure or external_multiple structure
                array(
                    'userid' => new external_value(PARAM_INT, 'User Id is invalid'),
                    'courseid' => new external_value(PARAM_INT, 'Course is invalid')
                ) 
        );
    }
    
    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function viewhistories_returns() { // BIG concerns here. See below.
        return new external_single_structure(
            array(
                'elodata' => new external_value(PARAM_TEXT, 'Invalid JSON'),
            )
        );
    }
    
    /**
     * The function itself
     * @return string welcome message
     */
    public static function viewhistories($param_userid,$param_courseid) {
        //Note: don't forget to validate the context and check capabilities
        $params = self::validate_parameters(self::viewhistories_parameters(),
                    array(
                        'userid' => $param_userid,
                        'courseid' => $param_courseid
                    )
                  );
        $params['elodata'] = elo_view_mail_histories($params);
        return $params;
    }
}


