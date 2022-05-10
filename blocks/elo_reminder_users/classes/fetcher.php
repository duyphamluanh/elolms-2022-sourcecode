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
 * @package    block_elo_reminder_users
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_elo_reminder_users;

defined('MOODLE_INTERNAL') || die();

/**
 * Class used to list and count elo reminder users
 *
 * @package    block_elo_reminder_users
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fetcher {

    /** @var string The SQL query for retrieving a list of elo reminder users */
    public $sql;
    /** @var string The SQL query for counting the number of elo reminder users */
    public $csql;
    /** @var string The params for the SQL queries */
    public $params;

    public $sitelevel;
    public $orderby;
    public $limitnum;
    public $paginatenum;

    /**
     * Class constructor
     *
     * @param int $currentgroup The group (if any) to filter on
     * @param int $now Time now
     * @param int $timetoshowteachers Number of seconds to show elo reminder teachers
     * @param int $timetoshowstudents Number of seconds to show elo reminder students
     * @param context $context Context object used to generate the sql for users enrolled in a specific course
     * @param bool $sitelevel Whether to check elo reminder users at site level.
     * @param int $courseid The course id to check
     */
    public function __construct($currentgroup, $now, $timetoshowteachers, $timetoshowstudents, $context, $sitelevel = true, $courseid = null, $berutype = '') {
        $this->set_sql($currentgroup, $now, $timetoshowteachers, $timetoshowstudents, $context, $sitelevel, $courseid, $berutype);
    }

    /**
     * Store the SQL queries & params for listing elo reminder users
     *
     * @param int $currentgroup The group (if any) to filter on
     * @param int $now Time now
     * @param int $timetoshowteachers Number of seconds to show elo reminder teachers
     * @param int $timetoshowstudents Number of seconds to show elo reminder students
     * @param context $context Context object used to generate the sql for users enrolled in a specific course
     * @param bool $sitelevel Whether to check elo reminder users at site level.
     * @param int $courseid The course id to check
     */
    protected function set_sql($currentgroup, $now, $timetoshowteachers, $timetoshowstudents, $context, $sitelevel, $courseid, $berutype) {
        global $USER, $DB;

        $timefrom = 100 * floor(($now - $timetoshowteachers) / 100); // Round to nearest 100 seconds for better query cache.
        $timefromstudent = 100 * floor(($now - $timetoshowstudents) / 100);

        $groupmembers = "";
        $groupselect  = "";
        $groupby       = "";
        $lastaccess    = ", u.lastaccess";
        $shortname    = ", r.shortname, r.archetype";
        $timeaccess    = ", ul.timeaccess AS timeaccess";
        // $uservisibility = ", up.value AS uservisibility";
        // $elolastaccess = ", MAX(beru.lastaccess) AS elolastaccess";
        $elolastaccess = ", MAX(beru.timecreated) AS elotimecreated";
        $elocountmail = ", COUNT(DISTINCT beru.id) AS elocountmail";
        $params = array();

        $userfields = \user_picture::fields('u', array('username'));
        $userfields .= ' ,u.emailstop ';

        // Add this to the SQL to show only group users.
        if ($currentgroup !== null) {
//            $groupmembers = ", {groups_members} gm";
//            $groupselect = "AND u.id = gm.userid AND gm.groupid = :currentgroup";
            $groupby = "GROUP BY $userfields";
            $lastaccess = ", MAX(u.lastaccess) AS lastaccess";
            $timeaccess = ", MAX(ul.timeaccess) AS timeaccess";
//            $uservisibility = ", MAX(up.value) AS uservisibility";
            $params['currentgroup'] = $currentgroup;
        }

        $params['now'] = $now;
        $params['timefrom'] = $timefrom;
        $params['timefromstudent'] = $timefromstudent;
        $params['userid'] = $USER->id;
        // $params['name'] = 'block_elo_reminder_users_uservisibility';
        $params['name'] = 'block_elo_reminder_users_mailtouser';
        $params['courseid'] = $courseid;

        if ($sitelevel) {

            if($berutype == 'teacher'){
                $wheretimerole = " AND (u.lastaccess < :timefrom AND r.shortname LIKE 'teacher') ";
            }
            else if($berutype == 'student'){
                $wheretimerole = " AND (u.lastaccess < :timefromstudent AND r.shortname LIKE 'student') ";
            }
            else {
                $wheretimerole = " AND ((u.lastaccess < :timefrom AND (r.shortname LIKE 'teacher'))
                            OR (u.lastaccess < :timefromstudent AND (r.shortname LIKE 'student'))) ";
            }

            $groupby = " GROUP BY $userfields $lastaccess $shortname ";
            $sql = "SELECT DISTINCT $userfields $lastaccess $shortname $elolastaccess $elocountmail
                        FROM {user} u $groupmembers
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid
                        LEFT JOIN {block_elo_reminder_users} beru ON beru.usertoid = u.id AND beru.status = 1
                       WHERE (u.auth LIKE 'manual' AND u.deleted = 0 AND u.suspended = 0 AND u.password NOT LIKE 'not cached')
                            $wheretimerole $groupselect $groupby ";

                    // ORDER BY lastaccess ASC ";

              $csql = "SELECT COUNT(DISTINCT u.id)
                         FROM {user} u $groupmembers
                        INNER JOIN {role_assignments} ra ON ra.userid = u.id
                        INNER JOIN {role} r ON r.id = ra.roleid
                        WHERE (u.auth LIKE 'manual' AND u.deleted = 0 AND u.suspended = 0 AND u.password NOT LIKE 'not cached')
                            $wheretimerole $groupselect ";

            $orderby = "ORDER BY (CASE 
                                    WHEN r.archetype = 'teacher' THEN 1
                                    ELSE 2
                                END) ASC,
                         u.lastaccess DESC";
        } else {
            // Course level - show only enrolled users for now.
            // TODO: add a new capability for viewing of all users (guests+enrolled+viewing).
            // list($esqljoin, $eparams) = get_enrolled_sql($context);
            // $params = array_merge($params, $eparams);

            // $sql = "SELECT $userfields $timeaccess $uservisibility
            //           FROM {user_lastaccess} ul $groupmembers, {user} u
            //           JOIN ($esqljoin) euj ON euj.id = u.id
            //      LEFT JOIN {user_preferences} up ON up.userid = u.id
            //                AND up.name = :name
            //          WHERE ul.timeaccess > :timefrom
            //                AND u.id = ul.userid
            //                AND ul.courseid = :courseid
            //                AND ul.timeaccess <= :now
            //                AND u.deleted = 0
            //                AND (" . $DB->sql_cast_char2int('up.value') . " = 1
            //                    OR up.value IS NULL
            //                    OR u.id = :userid)
            //                $groupselect $groupby
            //       ORDER BY lastaccess DESC";

            // $csql = "SELECT COUNT(u.id)
            //           FROM {user_lastaccess} ul $groupmembers, {user} u
            //           JOIN ($esqljoin) euj ON euj.id = u.id
            //      LEFT JOIN {user_preferences} up ON up.userid = u.id
            //                AND up.name = :name
            //          WHERE ul.timeaccess > :timefrom
            //                AND u.id = ul.userid
            //                AND ul.courseid = :courseid
            //                AND ul.timeaccess <= :now
            //                AND u.deleted = 0
            //                AND (" . $DB->sql_cast_char2int('up.value') . " = 1
            //                    OR up.value IS NULL
            //                    OR u.id = :userid)
            //                $groupselect";

            if($berutype == 'teacher'){
                $wheretimerole = " AND (ul.timeaccess < :timefrom OR u.lastaccess < :tfromtoo) AND (r.shortname LIKE 'teacher') ";
            }
            else if($berutype == 'student'){
                $wheretimerole = " AND (ul.timeaccess < :timefrom OR u.lastaccess < :tfromtoo) AND (r.shortname LIKE 'student') ";
            }
            else {
                $wheretimerole = "AND (
                            ((ul.timeaccess < :timefrom OR u.lastaccess < :tfromtoo) AND (r.shortname LIKE 'teacher'))
                            OR ((ul.timeaccess < :timefromstudent OR u.lastaccess < :tfromstudenttoo) AND (r.shortname LIKE 'student'))
                        ) ";
            }

            $groupby = " GROUP BY $userfields $lastaccess $shortname ";
            $sql = "SELECT DISTINCT $userfields $timeaccess $lastaccess $shortname $elolastaccess $elocountmail
                    FROM  {user} u $groupmembers
                    INNER JOIN {role_assignments} ra ON ra.userid = u.id
                    INNER JOIN {role} r ON r.id = ra.roleid
                    INNER JOIN {context} c ON c.id = ra.contextid
                    LEFT JOIN (SELECT userid, max(timeaccess) AS timeaccess, courseid FROM {user_lastaccess} WHERE courseid = :courseidtoo GROUP BY userid, courseid) AS ul ON ul.userid = u.id
                    LEFT JOIN {block_elo_reminder_users} beru ON beru.usertoid = u.id AND beru.status = 1 AND beru.courseid = c.instanceid
                    WHERE (c.instanceid = :courseid AND u.auth LIKE 'manual' AND u.deleted = 0 AND u.suspended = 0 AND u.password NOT LIKE 'not cached')
                       $wheretimerole $groupselect $groupby ";
                  // ORDER BY lastaccess ASC";

            $csql = "SELECT COUNT(DISTINCT u.id)
                    FROM {user} u $groupmembers
                    INNER JOIN {role_assignments} ra ON ra.userid = u.id
                    INNER JOIN {role} r ON r.id = ra.roleid
                    INNER JOIN {context} c ON c.id = ra.contextid
                    LEFT JOIN (SELECT userid, max(timeaccess) as timeaccess, courseid FROM {user_lastaccess} WHERE courseid = :courseidtoo GROUP BY userid, courseid) AS ul ON ul.userid = u.id
                    WHERE (c.instanceid = :courseid AND u.auth LIKE 'manual' AND u.deleted = 0 AND u.suspended = 0 AND u.password NOT LIKE 'not cached')
                        $wheretimerole $groupselect";

            $params['tfromtoo'] = $timefrom;
            $params['tfromstudenttoo'] = $timefromstudent;
            $params['courseidtoo'] = $courseid;
            $orderby = "ORDER BY (CASE 
                                    WHEN r.archetype like 'teacher' THEN 1
                                    ELSE 2
                                END) ASC,
                        ul.timeaccess DESC, u.lastaccess DESC";
        }
        $this->sql = $sql;
        $this->csql = $csql;
        $this->params = $params;

        $this->orderby = $orderby;
        $this->limitnum = 20;
        $this->paginatenum = 0;
        $this->sitelevel = $sitelevel;
    }

    /**
     * Get a list of the most recent elo reminder users
     *
     * @param int $userlimit The maximum number of users that will be returned (optional, unlimited if not set)
     * @return array
     */
    public function get_users_export($userlimit = 0, $paginate = 0, $sort = '') {
        global $DB;

        // $this->sql = "SELECT DISTINCT firstname,lastname,email
        //                 FROM {user} u
        //                 JOIN {role_assignments} ra ON ra.userid = u.id
        //                 JOIN {role} r ON r.id = ra.roleid
        //                 LEFT JOIN {block_elo_reminder_users} beru ON beru.usertoid = u.id AND beru.status = 1
        //                WHERE (u.deleted = 0 AND u.suspended = 0)
        //                     AND ((u.lastaccess < :timefrom AND (r.archetype like 'teacher' OR r.archetype like 'editingteacher'))
        //                     OR (u.lastaccess < :timefromstudent AND (r.archetype like 'student')))
        //                       ";

        if($sort == '' || !$sort){
          $this->sql .= " ".$this->orderby;
        }else{
            if($this->sitelevel){
            }
            else{
                // $sort = str_replace("lastaccess", "ul.timeaccess", $sort);
            }
            // $sort = str_replace("role", "shortname", $sort);
            // $sort = str_replace("lastsentmail", "beru.lastaccess", $sort);
            // $sort = str_replace("timemodified", "elotimecreated", $sort);

            $this->sql .= " ORDER BY ".$sort;
        }

        if(!is_numeric($paginate)) $paginate = 0;

        // $users = $DB->get_records_sql($this->sql, $this->params, 0, $userlimit);
        $users = $DB->get_recordset_sql($this->sql, $this->params, $paginate, $userlimit);
        return $users;
    }

    /**
     * Get a list of the most recent elo reminder users
     *
     * @param int $userlimit The maximum number of users that will be returned (optional, unlimited if not set)
     * @return array
     */
    public function get_users($userlimit = 0, $paginate = 0, $sort = '') {
        global $DB;

        if($sort == '' || !$sort){
          $this->sql .= " ".$this->orderby;
        }else{
            if($this->sitelevel){
            }
            else{
                // $sort = str_replace("lastaccess", "ul.timeaccess", $sort);
            }
            // $sort = str_replace("role", "shortname", $sort);
            // $sort = str_replace("lastsentmail", "beru.lastaccess", $sort);
            // $sort = str_replace("timemodified", "elotimecreated", $sort);

            $this->sql .= " ORDER BY ".$sort;
        }

        if(!is_numeric($paginate)) $paginate = 0;

        // $users = $DB->get_records_sql($this->sql, $this->params, 0, $userlimit);
        $users = $DB->get_records_sql($this->sql, $this->params, $paginate, $userlimit);
        return $users;
    }

    /**
     * Count the number of elo reminder users
     *
     * @return int
     */
    public function count_users() {
        global $DB;
        return $DB->count_records_sql($this->csql, $this->params);
    }

}
