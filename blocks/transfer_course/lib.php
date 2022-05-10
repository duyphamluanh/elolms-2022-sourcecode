<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/completionlib.php');

function update_transfer_course($data,$options = NULL) {
    global $DB;
    
    $data->timemodified = time();
    // Prevent changes on front page course.
    if ($data->id == SITEID) {
        throw new moodle_exception('invalidcourse', 'error');
    }
    
    // Update with the new data
    OUHITECH_MovingAllDateTimeAvoidVacationInCourse($data);//Nhien OUHitech
    $DB->update_record('course', $data);
    // make sure the modinfo cache is reset
    rebuild_course_cache($data->id); 
}

// Nhien update_course_transfertimecourse
// Chi tinh cac ngay Le roi vao ngay T7, CN nen tru tuan Le


function OUHITECH_Is_LeTet($datetime){
   $datetext = $datetime->format('Y-m-d');
   $Tetdays = array(
    //----2022--------------------------------------------
        // Tết dương lịch 
        "2021-12-27", //T2
        "2021-12-28",
        "2021-12-29",
        "2021-12-30",
        "2021-12-31", //T3,4,5,6
        "2022-01-01", //T7
        "2022-01-02", //CN
        // Tết ta 
        "2022-01-17", //T2
        "2022-01-18", //T3
        "2022-01-19", //T4
        "2022-01-20", //T5
        "2022-01-21", //T6
        "2022-01-22", //T7
        "2022-01-23", //CN
        "2022-01-24", //T2
        "2022-01-25", //T3
        "2022-01-26", //T4
        "2022-01-27", //T5
        "2022-01-28", //T6
        "2022-01-29", //T7
        "2022-01-30", //CN
        "2022-01-31", //T2
        "2022-02-01", //T3
        "2022-02-02", //T4
        "2022-02-03", //T5
        "2022-02-04", //T6
        "2022-02-05", //T7
        "2022-02-06", //CN

        "2022-04-04", //T2
        "2022-04-05", //T3
        "2022-04-06", //T4
        "2022-04-07", //T5
        "2022-04-08", //T6
        "2022-04-09", //T7
        "2022-04-10", //CN 

        // 30-04 > 01-05
        "2022-04-25", //T2
        "2022-04-26", //T3
        "2022-04-27", //T4
        "2022-04-28", //T5
        "2022-04-29", //T6
        "2022-04-30", //T7
        "2022-05-01", //CN
        "2022-05-02", //T2
        "2022-05-03", //T3
        // "2022-09-02",              
//----2021--------------------------------------------
//---- 2021 Tet Duong Lich       
//                            "2021-01-01", // T6
//---- 2021 Tet Am Lich              
                            "2021-02-01", // T2
                            "2021-02-02", // T3
                            "2021-02-03", // T4
                            "2021-02-04", // T5
                            "2021-02-05", // T6
                            "2021-02-06", // T7
                            "2021-02-07", // CN
                            "2021-02-08", // T2
                            "2021-02-09", // T3
                            "2021-02-10", // T4
                            "2021-02-11", // T5 30 Tet
                            "2021-02-12", // T6
                            "2021-02-13", // T7 
                            "2021-02-14", // CN 
                            "2021-02-15", // T2            
                            "2021-02-16", // T3            
                            "2021-02-17", // T4
                            "2021-02-18", // T5
                            "2021-02-19", // T6
                            "2021-02-20", // T7
                            "2021-02-21", // CN
//---- 2021 Gio To Hung Vuong
//                            "2021-04-21", // T4 (Gio To Hung Vuong)
//---- 2021 Le 30.04 & 01.05
                            "2021-04-26", // T2
                            "2021-04-27", // T3
                            "2021-04-28", // T4
                            "2021-04-29", // T5      
                            "2021-04-30", // T6
                            "2021-05-01", // T7
                            "2021-05-02", // CN
//----2021 Quoc Khanh                     
//                            "2021-09-02", // T4
                            
//----2020--------------------------------------------
//----2020 Tet Duong Lich      
//                            "2020-01-01", // T4
//----2020 Tet Am Lich       
                            "2020-01-13", // T2
                            "2020-01-14", // T3
                            "2020-01-15", // T4         
                            "2020-01-16", // T5          
                            "2020-01-17", // T6
                            "2020-01-18", // T7
                            "2020-01-19", // CN
                            "2020-01-20", // T2
                            "2020-01-21", // T3
                            "2020-01-22", // T4
                            "2020-01-23", // T5 30 Tet
                            "2020-01-24", // T6 
                            "2020-01-25", // T7 
                            "2020-01-26", // CN 
                            "2020-01-27", // T2            
                            "2020-01-28", // T3
                            "2020-01-29", // T4
                            "2020-01-30", // T5
                            "2020-01-31", // T6
                            "2020-02-01", // T7
                            "2020-02-02", // CN
//----2020 Gio To Hung Vuong
//                            "2020-04-02", // T5 (Gio To Hung Vuong)
//----2020 Le 30/04 & 01/05       
                            "2020-04-27", // T2
                            "2020-04-28", // T3
                            "2020-04-29", // T4
                            "2020-04-30", // T5
                            "2020-05-01", // T6
                            "2020-05-02", // T7
                            "2020-05-03", // CN
//----2020 Quoc Khanh       
//                            "2020-09-02", // T4
                            
//----2019--------------------------------------------
//----2019 Tet Duong Lich
//                            "2019-01-01", // T3
//----2019 Tet Am Lich       
                            "2019-01-28", // T2
                            "2019-01-29", // T3
                            "2019-01-30", // T4
                            "2019-01-31", // T5
                            "2019-02-01", // T6
                            "2019-02-02", // T7
                            "2019-02-03", // CN
                            "2019-02-04", // T2
                            "2019-02-05", // T3 
                            "2019-02-06", // T4
                            "2019-02-07", // T5                          
                            "2019-02-08", // T6
                            "2019-02-09", // T7
                            "2019-02-10", // CN                          
//----2019 Gio To Hung Vuong
//                            "2019-04-14", // CN
//----2019 Le 30/04 & 01/05       
//                           "2019-04-30", // T3
//                           "2019-05-01", // T4
//----2019 Quoc Khanh       
//                           "2019-09-02", // T2
                              
//----2018--------------------------------------------
                            "2018-02-13", // T2 
                            "2018-02-14", // T3
                            "2018-02-14", // T4 
                            "2018-02-15", // T5 30 Tet
                            "2018-02-16", // T6 
                            "2018-02-17", // T7 
                            "2018-02-18", // CN          
                            "2018-02-19", // T2          
                            "2018-02-20", // T3     
                            "2018-02-21", // T4
                            "2018-02-22", // T5
                            "2018-02-23", // T6
                            "2018-02-24", // T7
                            "2018-02-25", // CN
//----2017--------------------------------------------
                            "2017-01-23", // T2
                            "2017-01-24", // T3
                            "2017-01-25", // T4
                            "2017-01-26", // T5 
                            "2017-01-27", // T6 30 Tet
                            "2017-01-28", // T7 
                            "2017-01-29", // CN 
                            "2017-01-30", // T2              
                            "2017-01-31", // T3              
                            "2017-02-01", // T4
                            "2017-02-02", // T5
                            "2017-02-03", // T6
                            "2017-02-04", // T7
                            "2017-02-05", // CN
//----2016--------------------------------------------
                            "2016-02-01", // T2
                            "2016-02-02", // T3
                            "2016-02-03", // T4
                            "2016-02-04", // T5
                            "2016-02-05", // T6
                            "2016-02-06", // T7
                            "2016-02-07", // CN 29 Tet
                            "2016-02-08", // T2 Mung 1
                            "2016-02-09", // T3 
                            "2016-02-10", // T4 
                            "2016-02-11", // T5 
                            "2016-02-12", // T6
                            "2016-02-13", // T7
                            "2016-02-14", // CN
//----2015--------------------------------------------
                            "2015-02-16", // T2 
                            "2015-02-17", // T3
                            "2015-02-18", // T4 
                            "2015-02-19", // T5 30 Tet 
                            "2015-02-20", // T6        
                            "2015-02-21", // T7        
                            "2015-02-22", // CN        
                            "2015-02-23", // T2 
                            "2015-02-24", // T3
                            "2015-02-25", // T4
                            "2015-02-26", // T5
                            "2015-02-27", // T6
                            "2015-02-28", // T7
                            "2015-03-01", // CN
//----2014--------------------------------------------
                            "2014-01-27", // T2
                            "2014-01-28", // T3 
                            "2014-01-29", // T4
                            "2014-01-30", // T5 30 Tet 
                            "2014-01-31", // T6 
                            "2014-02-01", // T7          
                            "2014-02-02", // CN          
                            "2014-02-03", // T2          
                            "2014-02-04", // T3          
                            "2014-02-05", // T4
                            "2014-02-06", // T5
                            "2014-02-07", // T6
                            "2014-02-08", // T7
                            "2014-02-09", // CN
//----2013--------------------------------------------
                            "2013-02-04", // T2
                            "2013-02-05", // T3
                            "2013-02-06", // T4
                            "2013-02-07", // T5
                            "2013-02-08", // T6
                            "2013-02-09", // T7 29 Tet
                            "2013-02-10", // CN Mung 1
                            "2013-02-11", // T2 
                            "2013-02-12", // T3 
                            "2013-02-13", // T4           
                            "2013-02-14", // T5           
                            "2013-02-15", // T6           
                            "2013-02-16", // T7           
                            "2013-02-17", // CN           
//----2012--------------------------------------------
                            "2012-01-16", // T2
                            "2012-01-17", // T3
                            "2012-01-18", // T4
                            "2012-01-19", // T5
                            "2012-01-20", // T6
                            "2012-01-21", // T7 
                            "2012-01-22", // CN 29 Tet
                            "2012-01-23", // T2 Mung 1
                            "2012-01-24", // T3 
                            "2012-01-25", // T4            
                            "2012-01-26", // T5            
                            "2012-01-27", // T6            
                            "2012-01-28", // T7            
                            "2012-01-29", // CN            
//----2011--------------------------------------------
                            "2011-01-31", // T2 
                            "2011-02-01", // T3 
                            "2011-02-02", // T4 30 Tet
                            "2011-02-03", // T5 
                            "2011-02-04", // T6              
                            "2011-02-05", // T7              
                            "2011-02-06", // CN              
                            "2011-02-07", // T2      
                            "2011-02-08", // T3
                            "2011-02-09", // T4
                            "2011-02-10", // T5
                            "2011-02-11", // T6
                            "2011-02-12", // T7
                            "2011-02-13", // CN
//----2010--------------------------------------------
                            "2010-02-08", // T2
                            "2010-02-09", // T3
                            "2010-02-10", // T4
                            "2010-02-11", // T5
                            "2010-02-12", // T6
                            "2010-02-13", // T7 30 Tet 
                            "2010-02-14", // CN
                            "2010-02-15", // T2 
                            "2010-02-16", // T3 
                            "2010-02-17", // T4             
                            "2010-02-18", // T5             
                            "2010-02-19", // T6             
                            "2010-02-20", // T7             
                            "2010-02-21", // CN                    
                         );
    if (in_array($datetext, $Tetdays)) {
        return true;
    }
   return false;
}

function OUHITECH_Is_HungVuong($datetime){
   $datetext = $datetime->format('Y-m-d');
   $hungvuongdays = array(
                            "2030-04-12", // T6
                            "2029-04-23", // T2
                            "2028-04-04", // T3
                            "2027-04-16", // T6
                            "2026-04-26", // CN
                            "2025-04-07", // T2
                            "2024-04-18", // T5
                            "2023-04-29", // T7
                            "2022-04-10", // CN
                            "2021-04-21", // T4          
                            "2020-04-02", // T5
                            "2019-04-14", // CN
                            "2018-04-25", // T4
                            "2017-04-06", // T5
                            "2016-04-16", // T7
                            "2015-04-28", // T3
                            "2014-04-09", // T4
                            "2013-04-19", // T6
                            "2012-03-31", // T7
                            "2011-04-12", // T3
                            "2010-04-23", // T6
                        );
    if (in_array($datetext, $hungvuongdays)) {
        return true;
    }
    return false;
}

function OUHITECH_Is_2thang9($datetime) {
    $dayinwweek = intval($datetime->format('w'));
   
    if($dayinwweek == 0 || $dayinwweek == 6){ // 0 : Sunday 6 :Sat . Mon Tue... : 1 2 3 4 5 
        return true;
    }
    $dayinmonth = intval($datetime->format('d'));
    $monthinyear = intval($datetime->format('m'));
    $year4number = intval($datetime->format('Y'));
    
    if($dayinmonth==2 && $monthinyear==9)
        return true;
    if($year4number < 2021)
    {
        if ($dayinwweek==1 && (($dayinmonth==3 || $dayinmonth==4) && $monthinyear==9))
            return true;
    }
    else 
    {
    $Ngay29NamDo = $year4number. '-02-09';
    $datetime29NamDo = new DateTime($Ngay29NamDo); 
    $Thu29namdo = intval($datetime29NamDo->format('w'));
    if ($Thu29namdo==6 && ($dayinmonth==4 || $dayinmonth==5) && $monthinyear==9)// Thu 7 la 2/9 nam do
        return true;        
    if ($Thu29namdo==0 && ($dayinmonth==3 || $dayinmonth==4) && $monthinyear==9)
        return true;
    if ($Thu29namdo==1 && ($dayinmonth==3) && $monthinyear==9)
        return true;
    if ($Thu29namdo==2 && ($dayinmonth==1) && $monthinyear==9)
        return true;
    if ($Thu29namdo==3 && ($dayinmonth==3) && $monthinyear==9)
        return true;        
    if ($Thu29namdo==4 && ($dayinmonth==3) && $monthinyear==9)
        return true;
    if ($Thu29namdo==5 && ($dayinmonth==1) && $monthinyear==9)
        return true;
    }
    return false;
}
//DateTime
function OUHITECH_Is_Vacation($datetime) { 
    global $CFG;
//    $dayinweek = dayofweek($datetime->$day, $datetime->$month, $datetime->$year);
//    $datetimeT7 = new DateTime('2019-12-21'); 
 //   $datetimeCN = new DateTime('2019-12-22');
//    $dayinweekT7 = dayofweek($datetimeT7->$day, $datetimeT7->$month, $datetimeT7->$year);
//   $dayinweekCN = dayofweek($datetimeCN->$day, $datetimeCN->$month, $datetimeCN->$year);
    
//    $dayinweekT7 = $datetimeT7->format('w');//Y-m-d H:i:s
 //   $dayinweekCN = $datetimeCN->format('w');//Y-m-d H:i:s

   // $dayinweekT7 = $datetimeT7->format('d');//Y-m-d H:i:s
   // $dayinweekCN = $datetimeCN->format('d');//Y-m-d H:i:s

// May Ngay Le Cung  
  //  $datetime= date('Y-m-d',$datetime);
//    $datetime = new \DateTime($datetime); 
//    $datetime = new DateTime(date('Y-m-d', $datetime));
//    $datetime = (new \DateTimeImmutable())->setTimestamp($datetime)->modify("+0 days");
    
//    $dayinwweek = intval($datetime->format('w'));
//    if (isset($CFG->block_transfer_course_getthubayvachunhatisvacation)) {
//        if ($CFG->block_transfer_course_getthubayvachunhatisvacation == true){
//            if($dayinwweek == 0 || $dayinwweek == 6) // 0 : Sunday 6 :Sat . Mon Tue... : 1 2 3 4 5 
//            return true;
//        }
//    }
/*    Minh tu tinh ngay le va ngay nghi bu
    $dayinmonth = intval($datetime->format('d'));
    $monthinyear = intval($datetime->format('m'));
    $choCHet = $datetime->format('Y');
    $year4number = (int)($choCHet);
       
    // Tet Duong Lich 01/01    
    if($dayinmonth==1 && $monthinyear==1)
        return true;

    if (isset($CFG->block_transfer_course_getthubayvachunhatisvacation)) {
        if ($CFG->block_transfer_course_getthubayvachunhatisvacation == true){
            if ($dayinwweek==1 && $dayinmonth==2 && $monthinyear==1)
                return true;
            if ($dayinwweek==1 && $dayinmonth==3 && $monthinyear==1)
                return true;
        }
    }
    // 30/04 and 01/05    
    if($dayinmonth==30 && $monthinyear==4)
        return true;
    if($dayinmonth==1 && $monthinyear==5)
        return true;
    if (isset($CFG->block_transfer_course_getthubayvachunhatisvacation)) {
        if ($CFG->block_transfer_course_getthubayvachunhatisvacation == true){
        if ($dayinwweek==1 && $dayinmonth==2 && $monthinyear==5)//  
            return true;
        if ($dayinwweek==1 && $dayinmonth==3 && $monthinyear==5)// 
            return true;
        if ($dayinwweek==2 && $dayinmonth==2 && $monthinyear==5)// 
            return true;
        if ($dayinwweek==2 && $dayinmonth==3 && $monthinyear==5)// 
            return true;
        }
    }
    
    // Gio To Hung Vuong
    $iHungVuong = OUHITECH_Is_HungVuong($datetime); // Nhien
    if($iHungVuong)
    {
        return true;
    }
    else
    {
        if (isset($CFG->block_transfer_course_getthubayvachunhatisvacation)) {
            if ($CFG->block_transfer_course_getthubayvachunhatisvacation == true){
                if($dayinwweek == 1)
                {
                    $datetimepre1 = clone($datetime);
                    $datetimepre1 = $datetimepre1->modify('-1 day');
                    $iHungVuong = OUHITECH_Is_HungVuong($datetimepre1);
                    if($iHungVuong)
                        return true;
                    $datetimepre2 = clone($datetime);
                    $datetimepre2 = $datetimepre2->modify('-2 day');
                    $iHungVuong = OUHITECH_Is_HungVuong($datetimepre1);
                    if($iHungVuong)
                        return true;
                }
            }
        }
    }
    
    // Quoc Khanh 02/09
    if($dayinmonth==2 && $monthinyear==9)
        return true;
    if (isset($CFG->block_transfer_course_getthubayvachunhatisvacation)) {
        if ($CFG->block_transfer_course_getthubayvachunhatisvacation == true){
            if ($dayinwweek==1 && $dayinmonth==3 && $monthinyear==9)
                return true;
            if ($dayinwweek==1 && $dayinmonth==4 && $monthinyear==9)
                return true;
        }
    }
    */
    // Le Tet
    if(OUHITECH_Is_LeTet($datetime))
        return true;
    return false;
}

function OUHITECH_CountVacationDays($startdatetime, $enddatetime)
{
//    $testFormat = $startdatetime->format('Y-m-d');
    $datetime1 = date_create($startdatetime->format('Y-m-d'));
    $datetime2 = date_create($enddatetime->format('Y-m-d'));
    $interval = date_diff($datetime1, $datetime2);
    if($interval->days < 0){
        return 0;
    }
    $numvacationday = 0;
    if(OUHITECH_Is_Vacation($datetime1)){
        $numvacationday = 1;
    }
    for($dayi = 0 ; $dayi < $interval->days ; $dayi++){
        $datetime1 = $datetime1->modify('+1 day');
        if(OUHITECH_Is_Vacation($datetime1)){
            $numvacationday = $numvacationday + 1;
        }
    }
    return $numvacationday;
}

function OUHITECH_CountWorkingDays($startdatetime, $enddatetime)
{
//    $testFormat = $startdatetime->format('Y-m-d');
    $datetime1 = date_create($startdatetime->format('Y-m-d'));
    $datetime2 = date_create($enddatetime->format('Y-m-d'));
    $interval = date_diff($datetime1, $datetime2);
    if($interval->days < 0)
        return 0;
    
    $numvacationday = 0;
    if(OUHITECH_Is_Vacation($datetime1))
        $numvacationday = 1;
    for($dayi = 0 ; $dayi < $interval->days ; $dayi++)
    {
        $datetime1 = $datetime1->modify('+1 day');
        if(OUHITECH_Is_Vacation($datetime1)){
            $numvacationday = $numvacationday + 1;
        }
    }
    return $interval->days - $numvacationday;
}

function OUHITECH_MovingCourseStartDateAvoidVacation($startnewdatetime)
{
    while(OUHITECH_Is_Vacation($startnewdatetime))
    {
        //$startnewdatetime = new DateTime(date('Y-m-d', $startnewdatetime));
        $startnewdatetime =  $startnewdatetime->modify('+1 day');
      //  $startnewdatetime = (new \DateTimeImmutable())->setTimestamp($startnewdatetime)->modify("+1 days");
    }
    return $startnewdatetime;
}
function OUHITECH_MovingCourseStartTimestampAvoidVacation($startnewtimestamp)
{
    $textDateTime = date('Y-m-d H:i:s',$startnewtimestamp);
    $startnewdatetime = new DateTime($textDateTime);
    $ResultDateTime = OUHITECH_MovingCourseStartDateAvoidVacation($startnewdatetime);
    return $ResultDateTime->getTimestamp();
}
function OUHITECH_MovingDateTimeAvoidVacation($CoureOldStartDatetime, $OldDatetimeInOldCourse, $CoureNewStartDatetime)
{
    //$CoureOldEndDatetime = $CoureOldEndDatetime->modify('+90 day');// Ma vi du
    $CountWorkingDays = OUHITECH_CountWorkingDays($CoureOldStartDatetime, $OldDatetimeInOldCourse);
    
    $NewDatetimeInNewCourse = clone($CoureNewStartDatetime);
    $NewDatetimeInNewCourse->modify('+' .$CountWorkingDays.'day');
    $CountVacationDays = OUHITECH_CountVacationDays($CoureNewStartDatetime,$NewDatetimeInNewCourse);
    While ($CountVacationDays > 0)
    {
        $NewDatetimeInNewCourse->modify('+1 day');
        if(OUHITECH_Is_Vacation($NewDatetimeInNewCourse)==false)
        {
            $CountVacationDays = $CountVacationDays - 1;
        }
    }
    $NewTimeStampInNewCourse = $NewDatetimeInNewCourse->getTimestamp();
    $OldTimeStampInOldCourse = $OldDatetimeInOldCourse->getTimestamp();
    $textDate = date('Y-m-d',$NewTimeStampInNewCourse);
    $textTime = date(' H:i:s',$OldTimeStampInOldCourse);
    $NewDatetimeInNewCourse = new DateTime($textDate. $textTime);
    
//    $textDate = date('Y-m-d',$NewTimeStampInNewCourse);
//    $textTime = date(' H:i:s',$OldDatetimeInOldCourse);
//    $NewDatetimeInNewCourse = new DateTime($textDate + $textTime);
    return $NewDatetimeInNewCourse;
}

function OUHITECH_MovingTimeStampAvoidVacation($CoureOldStartTimeStamp, $OldTimeStampInOldCourse, $CoureNewStartTimeStamp)
{
    $textDateTime = date('Y-m-d H:i:s',$CoureOldStartTimeStamp);
    $CoureOldStartDatetime = new DateTime($textDateTime);
    $textDateTime = date('Y-m-d H:i:s',$OldTimeStampInOldCourse);
    $OldDatetimeInOldCourse = new DateTime($textDateTime);
    $textDateTime = date('Y-m-d H:i:s',$CoureNewStartTimeStamp);
    $CoureNewStartDatetime = new DateTime($textDateTime);
    $DateTimeResult = OUHITECH_MovingDateTimeAvoidVacation($CoureOldStartDatetime, $OldDatetimeInOldCourse, $CoureNewStartDatetime);
    $TimestampResult = $DateTimeResult->getTimestamp();
    return $TimestampResult;
}

function OUHITECH_MoveTimeInJson($jsontext,$CoureOldStarttimestamp,$CoureNewStarttimestamp)
{
    $JsonStruct = json_decode($jsontext);
    if($JsonStruct->c)
    {
        foreach ($JsonStruct->c as $key => $datavar) {
            if(($datavar->type== "date") && ($datavar->t > 0))
            {
                $datavar->t = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, $datavar->t, $CoureNewStarttimestamp);
            }
        }
    }
    $newjsontext = json_encode($JsonStruct);
    //if lay duoc timstmaple in $availability
    //move do xong dan lai
    //string	"{"op":"&","c":[{"type":"completion","cm":52437,"e":0},{"type":"date","d":">=","t":1544979600}],"showc":[true,true]}"    
    return $newjsontext;
}

function OUHITECH_MoveTimeInHtml($htmltext,$OldStarttimestamp,$NewStarttimestamp)
{
    $textDateNew = date('d/m/Y',$NewStarttimestamp);
    $WeekDayNumNew =  intval(date('w',$NewStarttimestamp));
    $textDateOld = date('d/m/Y',$OldStarttimestamp);
    $WeekDayNum =  intval(date('w',$OldStarttimestamp));
    $ThuNgay = array("Chủ nhật","Thứ hai","Thứ ba","Thứ tư","Thứ năm","Thứ sáu","Thứ bảy");
    $WeekDay = $ThuNgay[$WeekDayNum];
    $WeekDayNew = $ThuNgay[$WeekDayNumNew];
//    $HtmlStruct = html_entity_decode($htmltext);
    ///Provides: You should eat pizza, beer, and ice cream every day
    //$phrase  = "You should eat fruits, vegetables, and fiber every day.";
    $OldDays = array($WeekDay, $textDateOld);
    $NewDays = array($WeekDayNew, $textDateNew);
    //$yummy   = array("pizza", "beer", "ice cream");

    $newhtmltext = str_replace($OldDays,$NewDays,$htmltext);

    //$newhtmltext = html_entity_encode($HtmlStruct);
    return $newhtmltext;
}

function OUHITECH_MoveTimeForum($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        $assesstimestart_old = $data->assesstimestart;
        $assesstimefinish_old = $data->assesstimefinish;
        
        if($data->assesstimestart > 0){
            $data->assesstimestart = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, $data->assesstimestart, $CoureNewStarttimestamp);
        }
        
        if($data->assesstimefinish > 0){
            $data->assesstimefinish = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, $data->assesstimefinish, $CoureNewStarttimestamp);
        }
        
        if($data->intro)
        {
            if($assesstimestart_old > 0){
                $data->intro = OUHITECH_MoveTimeInHtml($data->intro,$assesstimestart_old,$data->assesstimestart); 
            }
            if($assesstimefinish_old > 0){
                $data->intro = OUHITECH_MoveTimeInHtml($data->intro,$assesstimefinish_old,$data->assesstimefinish);
            }
        }
        
//        if ($data->assesstimestart > 0 && $data->assesstimefinish > 0){
//            $moddata = [
//                'id' => $act->instance,
//                'timemodified' => time(),
//                'assesstimestart'=> $data->assesstimestart,
//                'assesstimefinish'=> $data->assesstimefinish,
//                'intro'=> $data->intro
//            ];
//            $DB->update_record($act->modname, $moddata);
//        }
        $moddata = [
                'id' => $act->instance,
                'timemodified' => time(),
                'assesstimestart'=> $data->assesstimestart,
                'assesstimefinish'=> $data->assesstimefinish,
                'intro'=> $data->intro
            ];
        $DB->update_record($act->modname, $moddata);
        
        
        // 2020_11_01_Restricted
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            if($act->completionexpected > 0) {
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                    'id' => $act->id,
                    'availability' => $OU_availability,
                    'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                //update event schedule
                //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                \core_completion\api::update_completion_date_event($act->id, 'forum', $act->instance, $OU_completionexpected);
            }   
        }
        
        // 2020_11_01_No Restricted
        if($act->completionexpected > 0){
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                        'id' => $act->id,
                        'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                    //update event schedule
                    //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                 \core_completion\api::update_completion_date_event($act->id, 'forum', $act->instance, $OU_completionexpected);
        }
}

function OUHITECH_MoveTimeQuiz($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        if(intval($data->timeopen) > 0){
            $data->timeopen = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeopen), $CoureNewStarttimestamp);
        }
        if(intval($data->timeclose) > 0){
            $data->timeclose = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeclose), $CoureNewStarttimestamp);
        }
        $moddata = [
            'id' => $act->instance,
            'timemodified' => time(),
            'timeopen'=> $data->timeopen,
            'timeclose'=> $data->timeclose
                ];
        $DB->update_record($act->modname, $moddata);
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        }
        //update event schedule
        OUHITECH_UpdateQuizEventsSchedule($act->instance, $data); //mod/quiz/lib.php
}

function OUHITECH_UpdateQuizEventsSchedule($activity,$quiz) {
    global $DB;
    // Load the old events relating to this quiz.
    $conds = array('modulename'=>'quiz',
                   'instance'=>$activity);
    $oldevents = $DB->get_records('event', $conds, 'id ASC');
    //Check lịch trình cũ có chưa, nếu có rồi thì xóa đi và tạo lịch trình mới với ngày kết thúc mới
    foreach ($oldevents as $oldevent) {
        if (($oldevent->timestart !== $quiz->timeclose)) {	
            $DB->delete_records('event',array('id' => $oldevent->id));
        }
    }
    quiz_update_events($quiz);
}

function OUHITECH_MoveTimeAssign($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        
        if(intval($data->duedate) > 0){
            $data->duedate = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->duedate), $CoureNewStarttimestamp);
        }

        if(intval($data->cutoffdate) > 0){
            $data->cutoffdate = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->cutoffdate), $CoureNewStarttimestamp);
        }
        
        if(intval($data->gradingduedate) > 0){
            $data->gradingduedate = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->gradingduedate), $CoureNewStarttimestamp);
        }

        $moddata = [
            'id' => $act->instance, 
            'timemodified' => time(),
            'duedate'=> $data->duedate,
            'cutoffdate'=> $data->cutoffdate,
            'gradingduedate'=> $data->gradingduedate,
        ];
        
        $DB->update_record($act->modname, $moddata);
        
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        } 
        //update event schedule
        OUHITECH_UpdateAssignEventsSchedule($data, $act); //mod/assign/lib.php

        // 2020_11_01_Restricted
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            if($act->completionexpected > 0){
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                    'id' => $act->id,
                    'availability' => $OU_availability,
                    'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                //update event schedule
                //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                \core_completion\api::update_completion_date_event($act->id, 'assign', $act->instance, $OU_completionexpected);
            }   
        }
        
        // 2020_11_01_No Restricted
        if($act->completionexpected > 0){
                $OU_completionexpected = OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp,$act->completionexpected, $CoureNewStarttimestamp);
                $cmdata = [
                        'id' => $act->id,
                        'completionexpected' => $OU_completionexpected
                ];
                $DB->update_record('course_modules', $cmdata);

                    //update event schedule
                    //$OU_completionexpected = !empty($OU_completionexpected) ? $$OU_completionexpected : null;
                 \core_completion\api::update_completion_date_event($act->id, 'assign', $act->instance, $OU_completionexpected);
        }
        
}

function OUHITECH_UpdateAssignEventsSchedule($instance,$activity){
    global $DB, $CFG;
    require_once($CFG->dirroot.'/calendar/lib.php');
    define('ASSIGN_EVENT_TYPE_DUE', 'due');
    define('ASSIGN_EVENT_TYPE_GRADINGDUE', 'gradingdue');
    define('CALENDAR_EVENT_TYPE_ACTION', 1);
    $eventtype = ASSIGN_EVENT_TYPE_DUE;
    if ($instance->duedate) {
        $event->name = get_string('calendardue', 'assign', $instance->name);
        $intro = $instance->intro;
        $event->description = array(
            'text' => $intro,
            'format' => $instance->introformat
        );

        $event->eventtype = $eventtype;
        $event->timestart = $instance->duedate;
        $event->timesort = $instance->duedate;
        $event->courseid = $instance->course;
        $event->modulename = $activity->modname;
        $event->groupid = 0;
        $event->instance = $instance->id;
        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        $select = "modulename = :modulename
                   AND instance = :instance
                   AND eventtype = :eventtype
                   AND groupid = 0
                   AND courseid <> 0";
        $params = array('modulename' => 'assign', 'instance' => $instance->id, 'eventtype' => $eventtype);
        $event->id = $DB->get_field_select('event', 'id', $select, $params);
        // Now process the event.
        if ($event->id) {
            $calendarevent= calendar_event::load($event->id); 
            $calendarevent->update($event);
        }
        else {
            calendar_event::create($event); // Tạo sự kiện nếu chưa có
        }

    }else {
        $DB->delete_records('event', array('modulename' => 'assign', 'instance' => $instance->id,
            'eventtype' => $eventtype));
    }
    $eventtype = ASSIGN_EVENT_TYPE_GRADINGDUE;
    if ($instance->gradingduedate) {
        $event->name = get_string('calendargradingdue', 'assign', $instance->name);
        $intro = $instance->intro;
        $event->description = array(
            'text' => $intro,
            'format' => $instance->introformat
        );
        $event->eventtype = $eventtype;
        $event->timestart = $instance->gradingduedate;
        $event->timesort = $instance->gradingduedate;
        $event->courseid = $instance->course;
        $event->modulename = $activity->modname;
        $event->groupid = 0;
        $event->instance = $instance->id;
        $event->id = $DB->get_field('event', 'id', array('modulename' => 'assign',
            'instance' => $instance->id, 'eventtype' => $event->eventtype));

        // Now process the event.
        if ($event->id) {
            $calendarevent= calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            calendar_event::create($event);
        }
    } else {
        $DB->delete_records('event', array('modulename' => 'assign', 'instance' => $instance->id,
        'eventtype' => $eventtype));
    }
}

function OUHITECH_MoveTimeScorm($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
    global $DB,$CFG;
        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
        if(intval($data->timeopen) > 0){
            $data->timeopen = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeopen), $CoureNewStarttimestamp);
        }
        if(intval($data->timeclose) > 0){
            $data->timeclose = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeclose), $CoureNewStarttimestamp);
        }
        if (intval($data->timeopen) > 0 && intval($data->timeclose) > 0) {
            $moddata = [
                    'id' => $act->instance,
                    'timemodified' => time(),
                    'timeopen'=> $data->timeopen,
                    'timeclose'=> $data->timeclose
                   ];
            $DB->update_record($act->modname, $moddata);
        }
        if (intval($data->timeopen) == 0 && intval($data->timeclose) > 0) {
            $moddata = [
                    'id' => $act->instance,
                    'timemodified' => time(),
                    'timeclose'=> $data->timeclose
                   ];
            $DB->update_record($act->modname, $moddata);  
        }
        
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        } 
        require_once("$CFG->dirroot/mod/scorm/locallib.php");
        scorm_update_calendar($data, $act->id);
}

function OUHITECH_MoveTimeGeneral($act,$CoureOldStarttimestamp,$CoureNewStarttimestamp) {
     global $DB;
//        $data = $DB->get_record($act->modname, array('id'=>$act->instance), '*', MUST_EXIST);
//        if(intval($data->timeopen) > 0){
//            $data->timeopen = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeopen), $CoureNewStarttimestamp);
//        }
//        if(intval($data->timeclose) > 0){
//            $data->timeclose = (string)OUHITECH_MovingTimeStampAvoidVacation($CoureOldStarttimestamp, intval($data->timeclose), $CoureNewStarttimestamp);
//        }
//        if (intval($data->timeopen) == 0 && intval($data->timeclose) > 0) {
//            $moddata = [
//                'id' => $act->instance,
//                'timemodified' => time(),
//                'timeopen'=> $data->timeopen,
//                'timeclose'=> $data->timeclose
//            ];
//            $DB->update_record($act->modname, $moddata);
//        }
        if($act->availability){
            $OU_availability = OUHITECH_MoveTimeInJson($act->availability,$CoureOldStarttimestamp,$CoureNewStarttimestamp);
            $cmdata = [
                'id' => $act->id,
                'availability' => $OU_availability];
            $DB->update_record('course_modules', $cmdata);
        }        
}

function OUHITECH_MovingAllDateTimeAvoidVacationInCourse($data){	
//    global $DB;
    if($data->transfertimecourse != '1'){
        return;
    }
    $oldcourse = course_get_format($data->id)->get_course();
    $CoureNewStarttimestamp = OUHITECH_MovingCourseStartTimestampAvoidVacation($data->startdate);
    $CoureNewEndtimestamp = OUHITECH_MovingTimeStampAvoidVacation($oldcourse->startdate, $oldcourse->enddate, $CoureNewStarttimestamp);
    $data->startdate = $CoureNewStarttimestamp;
    $data->enddate = $CoureNewEndtimestamp;
/*    if ($data->startdate != $oldcourse->startdate && $data->enddate != $oldcourse->enddate) {
        
        $coursedata = ['id' => $data->id, 'startdate' => $CoureNewStarttimestamp, 'enddate'=> $CoureNewEndtimestamp];
        //$coursedata = ['id' => $data->id, 'startdate' => $CoureNewStartDatetime, 'enddate'=> $data->enddate];
        $DB->update_record('course', $coursedata);
    }
*/    
    //get activities
    $moduleinfo = get_fast_modinfo($data->id);
    $cmss = $moduleinfo->get_cms();
    $activities = array();
    $acceptact = array('label','bigbluebuttonbn','page');
    foreach ($cmss as $key => $cms) {
//        $mod = $moduleinfo->get_cm($key);
        if (in_array($cms->modname, $acceptact)) {
            continue;
        }
        if (!$cms->uservisible) {
            continue;
        }
        if ($cms->completion == COMPLETION_TRACKING_NONE) {//Bỏ các hoạt động không có completion
            continue;
        }
        if ($cms->modname == 'resource' && !isset($cms->availabitity)) {//Cac resource khong co dieu kien
            continue;
        }
        $activities[$key]= $cms;
    }
    // Luc Loi All Date Time Trong Course tu data base ke ca Date Ket Thuc cua $iCoureOldDatetime
    foreach ($activities as $key => $act) {
        if($act->modname == 'forum'){
            OUHITECH_MoveTimeForum($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }
        else if($act->modname == 'quiz'){
            OUHITECH_MoveTimeQuiz($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }  
        else if($act->modname == 'assign'){
            OUHITECH_MoveTimeAssign($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }  
        else if($act->modname == 'scorm'){
            OUHITECH_MoveTimeScorm($act,$oldcourse->startdate,$CoureNewStarttimestamp);
        }  
        else {
            OUHITECH_MoveTimeGeneral($act,$oldcourse->startdate,$CoureNewStarttimestamp);//resource
        }
    }
}
