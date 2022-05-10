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
 * Class containing data for courses view in the myelostatistic block.
 *
 * @package    block_myelostatistic
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myelostatistic\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_completion\progress;
//use core_course\external\course_summary_exporter;
require_once($CFG->dirroot.'/mod/forum/lib.php');
use core_privacy\local\request\transform;


require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/overview/lib.php';


/**
 * Class containing data for courses view in the myelostatistic block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_view implements renderable, templatable {
    /** Quantity of courses per page. */
    const COURSES_PER_PAGE = 6;

    /** @var array $courses List of courses the user is enrolled in. */
    protected $courses = [];

    /** @var array $coursesprogress List of progress percentage for each course. */
    protected $coursesprogress = [];

    public $usersprogressesresult;
    /**
     * The courses_view constructor.
     *
     * @param array $courses list of courses.
     * @param array $coursesprogress list of courses progress.
     */
    public function __construct($courses, $coursesprogress) {
        $this->courses = $courses;
        $this->coursesprogress = $coursesprogress;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function calculate_friend_progresses($usersprogressesresult) {
        $friend_progresses = [];
        foreach ($usersprogressesresult as $userid => $usersprogresses){
            $elocoursespercent = 0;
            $countprogress = count($usersprogresses);
            if($countprogress > 0){
                foreach ($usersprogresses as $courseprogress){
                    $elocoursespercent += $courseprogress['progress'];
                }
                $elocoursespercent = round($elocoursespercent/$countprogress); 
            }
            $friend_progresses[$userid] = $elocoursespercent;
        }
        return $friend_progresses;
    }
    public function elo_get_class_friend_ids($courses) {
        //$usersincourses;
        foreach ($courses as $course) {
            $usersincourse = enrol_get_course_users($course->id);
            foreach ($usersincourse as $userincourse){
                $usersincourses[$userincourse->id][$course->id] = $course;
            }
        }
        //$classfriendids;
        foreach ($usersincourses as $userid => $userincourses ) {
            $countcource = count($userincourses);
            if($countcource== count($courses)){
                $classfriendids[$userid] = 1;
            }
        }
        return $classfriendids;
    }      
    public function leveling_friend_progresses($myprogress, $myuserid) {
        
        $classfriendcourse = $this->elo_get_class_friend_ids($this->courses);
        $usersprogressesresult = $this->elo_set_users_progresses($classfriendcourse,$this->courses);
        
        $friend_progresses = $this->calculate_friend_progresses($usersprogressesresult);
        $totalevel = count($friend_progresses);
        $mylevel = count($friend_progresses);
        foreach ($friend_progresses as $userid => $usersprogress){
            if($usersprogress <= $myprogress && $myuserid != $userid){
                $mylevel = $mylevel -1;
            }
        }
        $progresscard->mylevel = $mylevel . '/' . $totalevel;
        $progresscard->maxprogress = MAX($friend_progresses);
        $progresscard->minprogress = MIN($friend_progresses);
        $progresscard->avgprogress = round(array_sum($friend_progresses)/count($friend_progresses)) ;
        if(count($friend_progresses) == 0 &&  
                MAX($friend_progresses) == 0 && 
                MIN($friend_progresses) == 0) {
            $progresscard->avgprogress = 0;
            $progresscard->maxprogress = 0;
            $progresscard->minprogress = 0;
        }
        return $progresscard;
    }    
    public function elo_set_users_progresses($usersprogresses,$courses) {
        $usersprogressesresult;
        foreach ($usersprogresses as $userid => $usersprogress) {
            $coursesprogress = [];
            foreach ($courses as $course) {
                $completion = new \completion_info($course);
                // First, let's make sure completion is enabled.
                if (!$completion->is_enabled()) {
                    continue;
                }
                $percentage = progress::get_course_progress_percentage($course,$userid);
                if (!is_null($percentage)) {
                    $percentage = floor($percentage);
                }
                $coursesprogress[$course->id]['completed'] = $completion->is_course_complete($userid);
                $coursesprogress[$course->id]['progress'] = $percentage;
            }
            $usersprogressesresult[$userid] = $coursesprogress;
        }
        return $usersprogressesresult;
    }
    public function my_activitive_completion_statistic($courses,$userid = null) {

        global $USER, $DB;
        // Make sure we continue with a valid userid.
        if (empty($userid)) {
            $userid = $USER->id;
        }
        
        $elocompletornotactivitives = array();
        foreach ($courses as $course){
            $completion = new \completion_info($course);
            // First, let's make sure completion is enabled.
            if (!$completion->is_enabled()) {
                continue;
            }
            $course_complete = false;
            // Before we check how many modules have been completed see if the course has.
            if ($completion->is_course_complete($userid)) {
                $course_complete = true;
            }
            // Get the number of modules that support completion.
            $modules = $completion->get_activities();
            $count = count($modules);
            if (!$count) {
                continue;
            }
            // Get the number of modules that have been completed.
            $completed = 0;
            foreach ($modules as $module) {   
                $data = $completion->get_data($module, false, $userid);
                $itemcompleted = $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
                if($course_complete)
                    $itemcompleted = true;
                $completed += $itemcompleted;
                $finalmodname = $module->modname;
                if($module->modname =='resource' ||$module->modname =='chat'
                        ||$module->modname =='document'||$module->modname =='label'
                        ||$module->modname =='url'||$module->modname =='feedback'
                        ||$module->modname =='video'||$module->modname =='page'
                        ||$module->modname =='scorm'||$module->modname =='bigbluebuttonbn'){
                    continue;
                    /*if($module->icon == 'f/mpeg-24'){
                        $finalmodname = 'video';
                    }
                    else{
                        $finalmodname = 'document';
                    } */      
                }
                if ($module->modname =='quiz' ||$module->modname =='assign') {
                    //Nhien get activities active, not show activiti làm chơi 3/1/2019
                    $graderecset = $DB->get_recordset_select('grade_items', 'courseid = ' . $course->id . 
                        ' AND iteminstance = '. $module->instance , null,'','gradetype', 0,0);
                    $reccount = 0;
                    foreach($graderecset as $graderec){
                        if($graderec->gradetype != 0){
                            $reccount = 1;
                            break;
                        }
                    }
                    $graderecset->close();
                    if($reccount == 0){
                        continue;  
                    } 
                //Nhien end 3/1/2019
                }
                $elocompletornotactivitives[$finalmodname]->completed += $itemcompleted;
                $elocompletornotactivitives[$finalmodname]->notcompleted += !$itemcompleted;
            }
        }
        return $elocompletornotactivitives;
    }
    public function export_completed_html($usersprogressesresult) {
        //$completedhtml;
        global $OUTPUT;
        ksort($usersprogressesresult);
        foreach ($usersprogressesresult as $actname => $progressresult){
            if ($actname == 'label' ||$actname == 'chat' ||$actname == 'document' 
                    ||$actname == 'feedback' || $actname == 'page'||$actname == 'url' 
                    ||$actname == 'video'||$actname == 'bigbluebuttonbn'||$actname == 'scorm') {
                continue;
            }
            $actnamefinal = get_string('modulename',$actname);
            if($actname == 'document' || $actname == 'video' || $actname =='bigbluebuttonbn'){
                $actnamefinal = get_string($actname,'block_myelostatistic');   
            }
            if($actname == 'document' || $actname == 'video'){
                  $actname = "resource";
            }
            $elomoduleicon = $OUTPUT->image_icon('icon', get_string('modulename', $actname), $actname);
            $lineitem = '<tr><td>'.$elomoduleicon . $actnamefinal . 
                    '</td><td><span class="badgeelo bg-blue">' .
                    $progressresult->completed . '</span></td></tr>';
            $completedhtml .= $lineitem;
        }
        return $completedhtml;
    }
    public function export_notcompleted_html($usersprogressesresult) {
        global $OUTPUT;
        ksort($usersprogressesresult);
        foreach ($usersprogressesresult as $actname => $progressresult){
            if ($actname == 'label' ||$actname == 'chat' ||$actname == 'document' 
                    ||$actname == 'feedback' || $actname == 'page'||$actname == 'url' 
                    ||$actname == 'video'||$actname == 'bigbluebuttonbn'||$actname == 'scorm') {
                continue;
            }
            $actnamefinal = get_string('modulename',$actname);
            if($actname == 'document' || $actname == 'video'|| $actname =='bigbluebuttonbn'){
                $actnamefinal = get_string($actname,'block_myelostatistic');
            }
            if($actname == 'document' || $actname == 'video'){
                  $actname = "resource";
            }
            $elomoduleicon = $OUTPUT->image_icon('icon', get_string('modulename', $actname), $actname);
            $lineitem = '<tr><td>'.$elomoduleicon . $actnamefinal .
                    '</td><td><span class="badgeelo bg-red">' .
                    $progressresult->notcompleted . '</span></td></tr>';
            $completedhtml .= $lineitem;
        }
        return $completedhtml;
    }    
    //Nhien count post discussion in forum
    public function elo_forum_count_discussion_post_by_user($user) {
        $courses = forum_get_courses_user_posted_in($user,false);
        $result = forum_get_posts_by_user($user, $courses,false,false,0,0);
        $discussionpostcount->postcount = $result->totalcount;
        $courses = forum_get_courses_user_posted_in($user,true);
        $result = forum_get_posts_by_user($user, $courses,false,true,0,0);
        $discussionpostcount->discussioncount = $result->totalcount;
        return $discussionpostcount;

}
    public function elo_online_hours_count_by_user_version_login_out($userid) {
        global $DB;
        $params['userid'] = $userid;
        $where = "userid = :userid AND anonymous = 0 AND (action = 'loggedin' OR action = 'loggedout')";
        $eventrecordset = $DB->get_recordset_select('logstore_standard_log',
                 $where, 
                 $params,'timecreated DESC, id DESC','*',0,0);
        $recordlogin;
        $recordloginflag = 0;
        $recordlogout;
        $countonlinetime = 0;
        foreach ($eventrecordset as $record) {
            if($record->action == 'loggedin'){
                $timecreated = transform::datetime($record->timecreated);
                if($recordloginflag==0){
                    $recordloginflag=1;
                }
                else{// May login ma khong chiju logout rat xau ve hoc hanh va vo trach nhiem ne cho may 15 phut thoi
                    $countonlinetime += 15*60;
                }
                $recordlogin = $record;
            }
            else{
                if($recordloginflag == 1)
                {
                    $countonlinsection = ($recordlogin->timecreated - $record->timecreated);
                    if($countonlinsection/60/60 > 4)
                        $countonlinsection = 0;
                    $countonlinetime += $countonlinsection;
                    $recordloginflag = 0;
                }
            }
        }
        $countonlinetime = round($countonlinetime/60/60);
        $eventrecordset->close();
        return $countonlinetime;
    }
    public function elo_online_hours_count_by_user_only_care_event_time($userid, $continuehours) {
        global $DB;
        $params['userid'] = $userid;
        $where = "userid = :userid AND anonymous = 0";
        if(isguestuser()) { // Nhien 
            $eventrecordset = $DB->get_recordset_select('logstore_standard_log',
                     $where, 
                     $params,'timecreated DESC, id DESC','action,timecreated',0,10);
        }
        else {
            $eventrecordset = $DB->get_recordset_select('logstore_standard_log',
                     $where, 
                     $params,'timecreated DESC, id DESC','action,timecreated',0,0);            
        }
        $recordlogin;
        $recordloginflag = 0;
        $recordlogout;
        $countonlinetime = 0;
        $countonlinetimecompare = 0;
        foreach ($eventrecordset as $record) {
            if($record->action != 'loggedout'){
                if($recordloginflag == 0){
                    $recordloginflag= 1;
                }
                else{
                    $countonlinsection = ($recordlogin->timecreated - $record->timecreated);
                    if($countonlinsection/60/60 > $continuehours) {// Qua lau
                        $countonlinetimecompare += $countonlinsection/60/60;
                        $countonlinsection = 15*60;// May di dau roi a khong le 4h coi 1 trang ha
                    }
                    $countonlinetime += $countonlinsection;
                }
                $recordlogin->timecreated = $record->timecreated;
            }
            else{
                if($recordloginflag == 1)
                {
                    $countonlinsection = ($recordlogin->timecreated - $record->timecreated);
                    if($countonlinsection/60/60 > $continuehours){
                        $countonlinetimecompare += $countonlinsection/60/60;
                        $countonlinsection = 15*60;// May di dau roi a khong le 4h coi 1 trang ha
                    }
                    $countonlinetime += $countonlinsection;
                    $recordloginflag = 0;
                }
            }
        }
        $countonlinetime = round($countonlinetime/60/60);
        $eventrecordset->close();
        return $countonlinetime;
    }
    
    public function elo_create_chart_html_simple($years, $points) {
        global $CFG;
        
        $urljs1 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/Chart.bundle.js';
        $urljs2 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/utils.js';
        $urljs3 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/analyser.js';
//        $urlcss = $CFG->wwwroot . '/blocks/myelostatistic/style.css';
        
        $elocharthtml  = '<div>
	<link rel="stylesheet" type="text/css" href="' . $urlcss . '">
	<script src="' . $urljs1 . '"></script>
	<script src="' . $urljs2 . '"></script>
	<script src="' . $urljs3 . '"></script>
	<div class="Nhien" style = "width : 600px ; height : 400px">
		<canvas id="Nhien-2"></canvas>
	</div>
	<script>
		var presets = window.chartColors;
		var utils = Samples.utils;
		var inputs = {
			min: -100,
			max: 100,
			count: 8,
			decimals: 2,
			continuity: 1
		};
		function NhienYear(){
			return ["2015", "2011", "2015", "2016", "2017", "2018"];
		}
		function NhienPoints(){
			return ["1", "102", "2848", "126", "272", "1723"];
		}
		function generateData(config) {
			
			return utils.numbers(Chart.helpers.merge(inputs, config || {}));
		}
		function generateLabels(config) {
			return utils.months(Chart.helpers.merge({
				count: inputs.count,
				section: 3
			}, config || {}));
		}

		var options = {
			maintainAspectRatio: false,
			spanGaps: false,
			elements: {
				line: {
					tension: 0.000001
				}
			},
			plugins: {
				filler: {
					propagate: false
				}
			},
			scales: {
				xAxes: [{
					ticks: {
						autoSkip: false,
						maxRotation: 0
					}
				}]
			}
		};

			// reset the random seed to generate the same data for all charts
			//utils.srand(8);

			new Chart(\'Nhien-2\', {
				type: \'line\',

				data: {
					labels: NhienYear(),
					datasets: [{
						backgroundColor: utils.transparentize(presets.red),
						borderColor: presets.red,
						data: NhienPoints(),
						label: \'Diem Tich Luy\',
						fill: \'start\'
					},
					{
						backgroundColor: utils.transparentize(presets.green),
						borderColor: presets.red,
						data: [12, 50, 3, 5, 2, 3],
						label: \'Diem Tich Luy Cua Ban Cung Lop\',
						fill: \'start\'
					}

						]
				},
				options: Chart.helpers.merge(options, {title: {text: \'Day La Bieu Do Diem Trung Bifnh Tich Luy\',display: true
}
				})
			});


	</script>
	</div>';
        return $elocharthtml;
    }
    public function elo_create_chart_html($years, $points) {
        global $CFG;
        
        $urljs1 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/Chart.bundle.js';
        $urljs2 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/utils.js';
        $urljs3 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/analyser.js';
//        $urlcss = $CFG->wwwroot . '/blocks/myelostatistic/style.css';
        
        $elocharthtml  = '<div>
	<link rel="stylesheet" type="text/css" href="' . $urlcss . '">
	<script src="' . $urljs1 . '"></script>
	<script src="' . $urljs2 . '"></script>
	<script src="' . $urljs3 . '"></script>
	<div style = "width : 100% ; height: 340px">
		<canvas id="Nhien-2"></canvas>
	</div>
	<script>
		function NhienYear(){
			return'. $years . ';
		}
		function NhienPoints(){
			return' . $points . ';
		}
                var color = Chart.helpers.color;
		var lineChartData = {
			labels: NhienYear(),
			datasets: [{
                            backgroundColor: "rgb(240, 154, 17,0.7)",
                            borderColor: \'rgb(240, 154, 17,255)\',
                            data: NhienPoints(),
                            label: \''. get_string('gradeavg:overview', 'block_myelostatistic') . '\',
                            fill: \'start\'
                            }]
		};
                Chart.plugins.register({
                    afterDatasetsDraw: function(chart) {
			var ctx = chart.ctx;
			chart.data.datasets.forEach(function(dataset, i) {
                            var meta = chart.getDatasetMeta(i);
                            if (!meta.hidden) {
                                meta.data.forEach(function(element, index) {
                                // Draw the text in black, with the specified font
                                ctx.fillStyle = \'rgb(239, 105, 102,255)\';
                                var fontSize = 14;
                                var fontStyle = \'bold\';
                                var fontFamily = \'Arial\';
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);
                                // Just naively convert to string for now
                                var dataString = dataset.data[index].toString();

                                // Make sure alignment settings are correct
                                ctx.textAlign = \'center\';
                                ctx.textBaseline = \'middle\';

                                var padding = 12;
                                var position = element.tooltipPosition();
                                ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                                });
                            }
                        });
                    }
		});

                var ctx = document.getElementById(\'Nhien-2\').getContext(\'2d\');
                new Chart(ctx, {
                        type: \'line\',
                        data: lineChartData,
                        options: {
                                elements: { line: { tension: 0,}},
                                responsive: true,
                                maintainAspectRatio: false,
                                title: {
                                        display: false,
                                        text: \''. get_string('gradeavg:overview', 'block_myelostatistic') . '\'
                                }
                        }
                });

	</script>
	</div>';
        return $elocharthtml;
    }
    //Ham rat hay nhung ko su dung :)
    public function elo_calculate_averager_grades_per_haft_year_data() {
        global $USER,$COURSE;
	//return ["2011","2012","2013", "2014", "2015", "2016", "2017", "2018"];
	//return ["45", "70", "50", "36", "23", "82", "62", "45"];
        $context = \context_course::instance($COURSE->id);
        $gpr = new \grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$COURSE->id, 'userid'=>$USER->id));      
        $report = new \grade_report_overview($USER->id, $gpr, $context);
        //$coursegrade = new grade_grade(array('itemid' => $courseitem->id, 'userid' => $this->user->id));
        if ($report->courses){
            $coursegradesdata = $report->setup_courses_data(false);
        }
        $timenow = time();
        $yearnow =  date("20y", $timenow); 
        $monthnow =  date("m", $timenow); 
        $daynow =  date("d", $timenow); 
        $haftyearpoints;
        $countgrade = count($coursegradesdata);
        foreach($coursegradesdata as $coursegradefinal) {
            $itemid = $coursegradefinal['courseitem']->id;
            $userid = $USER->id;
            $coursegrade = new \grade_grade(array('itemid' => $itemid, 'userid' => $userid));
            $yeargrade =  date("20y", $coursegrade->timemodified); 
            $monthgrade =  date("m", $coursegrade->timemodified); 
                if($monthgrade>=1 && $monthgrade < 3)
                    $milestone = $yeargrade . '/03';
                else if($monthgrade>=3 && $monthgrade < 6)
                    $milestone = $yeargrade . '/6';
                else if($monthgrade>=6 && $monthgrade < 9)
                    $milestone = $yeargrade . '/9';                
                else
                    $milestone = $yeargrade . '/12';
            $haftyearpoints[$milestone] += $coursegradefinal['finalgrade']/$countgrade;
        }
        
        //"2011","2012","2013", "2014", "2015", "2016", "2017", "2018"]';
        //$points= '["45", "70", "50", "36", "23", "82", "62", "45"]';
        ksort($haftyearpoints);
       
        $isfirst = 1;
        $prehaftyear;
        foreach($haftyearpoints as $haftyear => $point) {
            if($isfirst==1){
                $prehaftyear = $haftyear;
            }
            else {
                $haftyearpoints[$haftyear] = $point + $haftyearpoints[$prehaftyear];
                $prehaftyear = $haftyear;
            }
//            $haftyearpoints[$haftyear] = round($haftyearpoints[$haftyear],2);
            $isfirst = 0;
        }
        foreach($haftyearpoints as $haftyear => $point) {
            $haftyearpoints[$haftyear] = round($haftyearpoints[$haftyear],2);
        }        
        $years= '[';
        $points= '[';
        $isfirst = 1;
        foreach($haftyearpoints as $haftyear => $point) {
            if($isfirst==1){
                $years .= '"' . $haftyear . '"';
                $points .= '"' . $point . '"';
            }
            else{
                $years .= ',"' . $haftyear . '"';
                $points .= ',"' . $point . '"';
            }   
            $isfirst = 0;
        }
        $years .= ']';
        $points.= ']';
        $result->years = $years;
        $result->points = $points;
        return $result;//elo_create_chart_html
    }
    public function elo_calculate_averager_grades_per_haft_year() {
        $result = $this->elo_calculate_averager_grades_per_haft_year_data();
        return $this->elo_create_chart_html($result->years, $result->points);//elo_create_chart_html
    }

    public function elo_create_bar_course_chart_html($years, $points) {
        global $CFG;
        
        $urljs1 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/Chart.bundle.js';
        $urljs2 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/utils.js';
        $urljs3 = $CFG->wwwroot . '/blocks/myelostatistic/amd/src/analyser.js';
        //$urlcss = $CFG->wwwroot . '/blocks/myelostatistic/style.css';
        
        $elocharthtml  = '<div>
	<link rel="stylesheet" type="text/css" href="' . $urlcss . '">
	<script src="' . $urljs1 . '"></script>
	<script src="' . $urljs2 . '"></script>
	<script src="' . $urljs3 . '"></script>
	<div class="fixbarchart">
		<canvas id="Nhien-3"></canvas>
	</div>
	<script>
		function NhienYear(){
			return'. $years . ';
		}
		function NhienPoints(){
			return' . $points . ';
		}
                var color = Chart.helpers.color;
		var barChartData = {
			labels: NhienYear(),
			datasets: [{
                            backgroundColor: "rgb(240,154,17,0.8)",
                            borderColor: window.chartColors.black,
                            data: NhienPoints(),
                            label: \''. get_string('gradeavg:overview', 'block_myelostatistic') . '\',
                            fill: \'start\'
                            }]
		};
                Chart.plugins.register({
                    afterDatasetsDraw: function(chart) {
			var ctx = chart.ctx;
			chart.data.datasets.forEach(function(dataset, i) {
                            var meta = chart.getDatasetMeta(i);
                            if (!meta.hidden) {
                                meta.data.forEach(function(element, index) {
                                // Draw the text in black, with the specified font
                                ctx.fillStyle = \'rgb(239, 105, 102,255)\';
                                var fontSize = 14;
                                var fontStyle = \'bold\';
                                var fontFamily = \'Arial\';
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);
                                // Just naively convert to string for now
                                var dataString = dataset.data[index].toString();

                                // Make sure alignment settings are correct
                                ctx.textAlign = \'center\';
                                ctx.textBaseline = \'middle\';

                                var padding = 5;
                                var position = element.tooltipPosition();
                                ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                                });
                            }
                        });
                    }
		});
                var ctx = document.getElementById(\'Nhien-3\').getContext(\'2d\');
                new Chart(ctx, {
                        type: \'bar\',
                        data: barChartData,
                        options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                title: {
                                        display: false,
                                        text: \''. get_string('gradeavg:overview', 'block_myelostatistic') . '\'
                                }
                        }
                });
	</script>
	</div>';
        return $elocharthtml;
    }
    
    //Ham rat hay nhung ko su dung :)
    
    public function elo_calculate_averager_grades_per_haft_year_course_data() {
        global $USER,$COURSE;
	//return ["2011","2012","2013", "2014", "2015", "2016", "2017", "2018"];
	//return ["45", "70", "50", "36", "23", "82", "62", "45"];
        $context = \context_course::instance($COURSE->id);
        $gpr = new \grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$COURSE->id, 'userid'=>$USER->id));      
        $report = new \grade_report_overview($USER->id, $gpr, $context);
        //$coursegrade = new grade_grade(array('itemid' => $courseitem->id, 'userid' => $this->user->id));
        if ($report->courses){
            $coursegradesdata = $report->setup_courses_data(false);
        }
        $timenow = time();
        $yearnow =  date("20y", $timenow); 
        $monthnow =  date("m", $timenow); 
        $daynow =  date("d", $timenow); 
        $haftyearpoints;
        $countgrade = count($coursegradesdata);
        foreach($coursegradesdata as $coursegradefinal) {
            $itemid = $coursegradefinal['courseitem']->id;
            $userid = $USER->id;
            $coursegrade = new \grade_grade(array('itemid' => $itemid, 'userid' => $userid));
            $milestone = "";
            if(isset($coursegrade->timemodified)){
                $yeargrade =  date("20y", $coursegrade->timemodified); 
                $monthgrade =  date("m", $coursegrade->timemodified);
                
                if($monthgrade>=1 && $monthgrade < 3)
                    $milestone = $yeargrade . '/03';
                else if($monthgrade>=3 && $monthgrade < 6)
                    $milestone = $yeargrade . '/6';
                else if($monthgrade>=6 && $monthgrade < 9)
                    $milestone = $yeargrade . '/9';                
                else
                    $milestone = $yeargrade . '/12';
                
            }
            $haftyearpoints[$milestone]->coursegrades[$coursegradefinal['course']->shortname] = $coursegradefinal['finalgrade'];
        }
        ksort($haftyearpoints);
        $listofcharts;
        foreach($haftyearpoints as $haftyear => $haftyearitem) {
            $arratid = 0;
            foreach($haftyearitem->coursegrades as $keyname => $coursegraditem) {
                $listofcharts[$arratid][$haftyear]->coursename = $keyname;
                $listofcharts[$arratid][$haftyear]->grade = $coursegraditem;
                $arratid++;
            }
        }
        foreach($listofcharts as $chartid => $chartitem) {
            $result->charts[$chartid] = '[';
        }
        $years= '[';
        $isfirst = 1;  
        foreach($haftyearpoints as $haftyear => $haftyearitem) {
            if($isfirst==1){
                $years .= '"' . $haftyear . '"';
                foreach($listofcharts as $chartid => $chartitem) {
                    $result->charts[$chartid] .= '"' . $chartitem[$haftyear]->grade . '"';
                }        
            }
            else{
                $years .= ',"' . $haftyear . '"';
                foreach($listofcharts as $chartid => $chartitem) {
                    $result->charts[$chartid] .= ',"' . $chartitem[$haftyear]->grade . '"';
                }                
            }   
            $isfirst = 0;
        }
        $years .= ']';
        foreach($listofcharts as $chartid => $chartitem) {
            $result->charts[$chartid] .= ']';
        }
        $result->years = $years;

        return $result;//elo_create_chart_html
    }
    
    public function elo_calculate_averager_grades_per_course_data() {
        global $USER,$COURSE;
        $context = \context_course::instance($COURSE->id);
        $gpr = new \grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$COURSE->id, 'userid'=>$USER->id));      
        $report = new \grade_report_overview($USER->id, $gpr, $context);
        //$coursegrade = new grade_grade(array('itemid' => $courseitem->id, 'userid' => $this->user->id));
        if ($report->courses){
            $coursegradesdata = $report->setup_courses_data(false);
        }
        $timenow = time();
        $yearnow =  date("20y", $timenow); 
        $monthnow =  date("m", $timenow); 
        $daynow =  date("d", $timenow); 
        $haftyearpoints;
        $countgrade = count($coursegradesdata);
        foreach($coursegradesdata as $coursegradefinal) {
            
            $itemid = $coursegradefinal['courseitem']->id;
            $userid = $USER->id;
            $coursegrade = new \grade_grade(array('itemid' => $itemid, 'userid' => $userid));
            $milestone = "";
            if(isset($coursegrade->timemodified)){
                $yeargrade =  date("20y", $coursegrade->timemodified); 
                $monthgrade =  date("m", $coursegrade->timemodified); 
                
                if($monthgrade>=1 && $monthgrade < 3)
                    $milestone = $yeargrade . '/03';
                else if($monthgrade>=3 && $monthgrade < 6)
                    $milestone = $yeargrade . '/6';
                else if($monthgrade>=6 && $monthgrade < 9)
                    $milestone = $yeargrade . '/9';                
                else
                    $milestone = $yeargrade . '/12';
                
                $milestone .= ' : ';
            }
            //$haftyearpoints[$milestone]->coursegrades[$coursegradefinal['course']->shortname] = $coursegradefinal['finalgrade'];
            $keynamefordatecourse = $milestone . $coursegradefinal['course']->shortname;
            $haftyearpoints[$keynamefordatecourse] = $coursegradefinal['finalgrade'];
        }
        ksort($haftyearpoints);
        foreach($haftyearpoints as $haftyear => $point) {
            $haftyearpoints[$haftyear] = round($haftyearpoints[$haftyear],2);
        }

//return ["2011","2012","2013", "2014", "2015", "2016", "2017", "2018"];
//return ["45", "70", "50", "36", "23", "82", "62", "45"];        
        $years= '[';
        $points= '[';
        $isfirst = 1;
        $spacechars = '';
        foreach($haftyearpoints as $haftyear => $point) {
            if($isfirst==1){
                $years .= '"' . $spacechars . $haftyear . '"';
                $points .= '"' . $point . '"';
            }
            else{
                $years .= ',"' . $spacechars . $haftyear . '"';
                $points .= ',"' . $point . '"';
            }   
            $isfirst = 0;
        }
        $years .= ']';
        $points.= ']';
        $result->years = $years;
        $result->points = $points;
        return $result;//elo_create_chart_html
    }

    public function elo_calculate_averager_grades_per_course() {

        $result = $this->elo_calculate_averager_grades_per_course_data();
        return $this->elo_create_bar_course_chart_html($result->years, $result->points);
    }
    
    public function elo_create_chart_tabs_html5only() {
	global $CFG;
        $htmlyear = $this->elo_calculate_averager_grades_per_course();
        $htmlcoures = $this->elo_calculate_averager_grades_per_haft_year();        
        $urlcss = $CFG->wwwroot . '/blocks/myelostatistic/styles.css';
        $htmlresult = '<div id="container_elo_charts">	
            <link rel="stylesheet" type="text/css" href="' . $urlcss . '">
	    <input id="tab-1" type="radio" name="tab-group" checked="checked" />
	    	<label for="tab-1">Course chart</label>
	    <input id="tab-2" type="radio" name="tab-group" />
	    	<label for="tab-2">Year chart</label>

	    <div id="content_elo_charts">
	        <div id="content_elo_charts-1">
		'.$htmlcoures.'
	        </div>
	        <div id="content_elo_charts-2">
		'.$htmlyear.'
	        </div>
	    </div>
	</div>';

        return $htmlresult;//$htmlyear . $htmlcoures;
    }
    public function elo_create_chart_tabs_html() {
        //$this->elo_calculate_averager_grades_per_haft_year_course_data();
	global $CFG;
        $htmlcoures = $this->elo_calculate_averager_grades_per_course();
        $htmlyear = $this->elo_calculate_averager_grades_per_haft_year();        
        $urlcss = $CFG->wwwroot . '/blocks/myelostatistic/styles.css';
        
        $htmlresult = '<div class="elo_charts_tab">
          <link rel="stylesheet" type="text/css" href="' . $urlcss . '">
          <button class="tablinks" onclick="openCity(event, \'London\')">'. get_string('coursegrade', 'block_myelostatistic') .'</button>
          <button class="tablinks" onclick="openCity(event, \'Paris\')">'. get_string('averagegrade', 'block_myelostatistic') .'</button>
        </div>

        <div id="London" class="elo_charts_tabcontent" >
          ' . $htmlcoures . '
        </div>

        <div id="Paris" class="elo_charts_tabcontent">
          ' . $htmlyear . '
        </div>
        <script>

        tablinks = document.getElementsByClassName("tablinks");
        tablinks[0].className += " active";
        document.getElementById("London").style.display = "block";

        function openCity(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("elo_charts_tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }
        </script>';
        return $htmlresult;//$htmlyear . $htmlcoures;
    }

    public function export_for_template(renderer_base $output) {
        global $CFG, $USER, $GLOBAL_myelostatistic_setting;
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->dirroot.'/lib/coursecatlib.php');
//  Nhien elocoursespercent count progress all cource
        //$a = array_filter($a);
        $elocoursespercent = 0;
        $countprogress = count($this->courses);
        if($countprogress > 0){
            foreach ($this->coursesprogress as $courseprogress){
                $elocoursespercent += $courseprogress['progress'];
            }
            $elocoursespercent = round($elocoursespercent/$countprogress);
            $elocoursespercent > 0  ? $elocoursespercent : 0;
        }
        $progresscard = $this->leveling_friend_progresses($elocoursespercent, $USER->id);
        $elocompletornotactivitives = $this->my_activitive_completion_statistic($this->courses,$USER->id);
        $completedhtml = $this->export_completed_html($elocompletornotactivitives);
        isset($completedhtml) ? $completedhtml : "No data" ;
        $notcompletedhtml = $this->export_notcompleted_html($elocompletornotactivitives);
        isset($notcompletedhtml) ? $notcompletedhtml :  "No data" ;
        $numbervideo = $elocompletornotactivitives['video']->completed;
        $numbervideo > 0 ? $numbervideo : $numbervideo = 0;
        $discussionpostcount = $this->elo_forum_count_discussion_post_by_user($USER);
        $discussionpostcount > 0 ? $discussionpostcount : 0;
        $onlinehourcount = $this->elo_online_hours_count_by_user_only_care_event_time($USER->id,$GLOBAL_myelostatistic_setting->eloestimateminute/60);
        //$elopointcharthtml = $this->elo_calculate_averager_grades_per_haft_year();
        $elopointcharthtml = $this->elo_create_chart_tabs_html();
        
        // Build courses view data structure.
        $coursesview = [
            'hascourses'                => !empty($this->courses),
            'elocoursespercent'         => $elocoursespercent, // Nhien elocoursespercent
            'elomylevel'                => $progresscard->mylevel,
            'elomaxprogress'            => $progresscard->maxprogress,
            'elominprogress'            => $progresscard->minprogress,
            'eloavgprogress'            => $progresscard->avgprogress,
            'elocompletedhtml'          => $completedhtml,
            'elonotcompletedhtml'       => $notcompletedhtml,
            'elonumbervideo'            => $numbervideo,
            'elopostcount'              => $discussionpostcount->postcount,
            'elodiscussioncount'        => $discussionpostcount->discussioncount,
            'eloonlinehourcount'        => $onlinehourcount,
            'elopointcharthtml'         => $elopointcharthtml
        ];
/* NhienBoCourseView
        // How many courses we have per status?
        $coursesbystatus = ['past' => 0, 'inprogress' => 0, 'future' => 0];
        foreach ($this->courses as $course) {
            $courseid = $course->id;
            $context = \context_course::instance($courseid);
            $exporter = new course_summary_exporter($course, [
                'context' => $context
            ]);
            $exportedcourse = $exporter->export($output);
            // Convert summary to plain text.
            $exportedcourse->summary = content_to_text($exportedcourse->summary, $exportedcourse->summaryformat);

            $course = new \course_in_list($course);
            // Nhien View 3.0.dom;
            $exportedcourse->courseimage = $course->__get('image');
            $exportedcourse->classes = 'courseimage';
            
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                if ($isimage) {
                    $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                    $exportedcourse->courseimage = $url;
                    $exportedcourse->classes = 'courseimage';
                    break;
                }
            }


            $exportedcourse->color = $this->coursecolor($course->id);

            if (!isset($exportedcourse->courseimage)) {
                $pattern = new \core_geopattern();
                $pattern->setColor($exportedcourse->color);
                $pattern->patternbyid($courseid);
                $exportedcourse->classes = 'coursepattern';
                $exportedcourse->courseimage = $pattern->datauri();
            }

            // Include course visibility.
            $exportedcourse->visible = (bool)$course->visible;

            $courseprogress = null;

            $classified = course_classify_for_timeline($course);

            if (isset($this->coursesprogress[$courseid])) {
                $courseprogress = $this->coursesprogress[$courseid]['progress'];
                $exportedcourse->hasprogress = !is_null($courseprogress);
                $exportedcourse->progress = $courseprogress;
            }

            if ($classified == COURSE_TIMELINE_PAST) {
                // Courses that have already ended.
                $pastpages = floor($coursesbystatus['past'] / $this::COURSES_PER_PAGE);

                $coursesview['past']['pages'][$pastpages]['courses'][] = $exportedcourse;
                $coursesview['past']['pages'][$pastpages]['active'] = ($pastpages == 0 ? true : false);
                $coursesview['past']['pages'][$pastpages]['page'] = $pastpages + 1;
                $coursesview['past']['haspages'] = true;
                $coursesbystatus['past']++;
            } else if ($classified == COURSE_TIMELINE_FUTURE) {
                // Courses that have not started yet.
                $futurepages = floor($coursesbystatus['future'] / $this::COURSES_PER_PAGE);

                $coursesview['future']['pages'][$futurepages]['courses'][] = $exportedcourse;
                $coursesview['future']['pages'][$futurepages]['active'] = ($futurepages == 0 ? true : false);
                $coursesview['future']['pages'][$futurepages]['page'] = $futurepages + 1;
                $coursesview['future']['haspages'] = true;
                $coursesbystatus['future']++;
            } else {
                // Courses still in progress. Either their end date is not set, or the end date is not yet past the current date.
                $inprogresspages = floor($coursesbystatus['inprogress'] / $this::COURSES_PER_PAGE);

                $coursesview['inprogress']['pages'][$inprogresspages]['courses'][] = $exportedcourse;
                $coursesview['inprogress']['pages'][$inprogresspages]['active'] = ($inprogresspages == 0 ? true : false);
                $coursesview['inprogress']['pages'][$inprogresspages]['page'] = $inprogresspages + 1;
                $coursesview['inprogress']['haspages'] = true;
                $coursesbystatus['inprogress']++;
            }
        }

        // Build courses view paging bar structure.
        foreach ($coursesbystatus as $status => $total) {
            $quantpages = ceil($total / $this::COURSES_PER_PAGE);

            if ($quantpages) {
                $coursesview[$status]['pagingbar']['disabled'] = ($quantpages <= 1);
                $coursesview[$status]['pagingbar']['pagecount'] = $quantpages;
                $coursesview[$status]['pagingbar']['first'] = ['page' => '&laquo;', 'url' => '#'];
                $coursesview[$status]['pagingbar']['last'] = ['page' => '&raquo;', 'url' => '#'];
                for ($page = 0; $page < $quantpages; $page++) {
                    $coursesview[$status]['pagingbar']['pages'][$page] = [
                        'number' => $page + 1,
                        'page' => $page + 1,
                        'url' => '#',
                        'active' => ($page == 0 ? true : false)
                    ];
                }
            }
        }
*/
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
