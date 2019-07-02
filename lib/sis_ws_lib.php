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
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contain all the global functions for RCYCI module
*/

// This is the library for global RCYCI functions 
defined('MOODLE_INTERNAL') || die();
require_once 'dblib.php'; //$PSDB and $LSDB are global variable automatically created once it is included. n function use global $PSDB and $LSDB
require_once 'rc_ps_lib.php'; //$PSDB and $LSDB are global variable automatically created once it is included. n function use global $PSDB and $LSDB
require_once 'rc_ls_lib.php'; //$PSDB and $LSDB are global variable automatically created once it is included. n function use global $PSDB and $LSDB

function rc_ws_export_data($data)
{
	global $DB;
	$records = false;
	if($data->method == 'authenticate') //authenticating student
	{
		$record = rc_ps_check_user($data->field, $data->value);
		if($record === true)
			return array("true"); //end result must be returned as array
		else
			return array("false"); //end result must be returned as array
	}
	else if($data->method == 'student_info')
	{
//		$record = rc_ls_get_stu_info_all($data->value); //from logsis
		$record = rc_ps_student_info($data->value);		//from ps
		return array($record); //end result must be returned as array. If result is in single array, enclose it as an array again
	}
	else if($data->method == 'schedule') //get the class schedule
	{
		$user = $DB->get_record('user', array('idnumber' => $data->value));
		$records = rc_ps_get_student_timetable($user, false);		//from ps
		return $records; //end result must be returned as array
	}
	else if($data->method == 'student_course')
	{
		$records = rc_ws_get_student_course($data); //for more complicated processing, we call a function
		return $records; //result already returned as a collection
	}
	else if($data->method == 'student_cgpa')
	{
		$records = rc_ps_get_student_cgpa($data->value); 
		return array($records);
	}
	else if($data->method == 'student_cumulative')
	{
		$records = rc_ps_get_cumulative($data->value); 
		return $records;
	}
	else if($data->method == 'student_dormitory')
	{
		$records = rc_ps_room_get_student_room($data->value); 
		return array($records);
	}
	else if($data->method == 'student_suspension')
	{
		$records = rc_ws_get_suspension($data); //for more complicated processing, we call a function
		return array($records);
	}
	else if($data->method == 'exam_schedule')
	{
		$records = rc_ws_get_exam_schedule($data); //for more complicated processing, we call a function
		return array($records);
	}
	else if($data->method == 'academic_program')
	{
		$records = rc_ps_get_acad_prog($data->value); //for more complicated processing, we call a function
		return array($records);
	}
	return array();
}


/* FUNCTIONS TO RETRIEVE AND PROCESS DATA */

function rc_ws_get_suspension($data)
{
	global $DB;
	$user = $DB->get_record('user', array('idnumber' => $data->value));
	$arr = array();
	if($user)
	{
		$arr['emplid'] = $user->idnumber;		
		if($user->alternatename == 1)
		{
			$arr['suspended'] = 'true';		
			$arr['suspend_date'] = $user->firstnamephonetic;			
			$suspend_message = $DB->get_record('rc_suspend_message', array('moodle_user_id' => $user->id));
			if($suspend_message)
				$arr['message'] = $suspend_message->message;
			else
				$arr['message'] = '';				
		}
		else
		{
			$arr['suspended'] = 'false';		
			$arr['suspend_date'] = '';			
			$arr['message'] = '';							
		}
	}
	return $arr;
}
//get list of enrolled courses with lecturer (if more than 1 lecturer, split by | symbol)
function rc_ws_get_student_course($data)
{
	global $DB;
	$user = $DB->get_record('user', array('idnumber' => $data->value));
	$records = rc_get_user_courses($user->id, 'student', true);
	$courses = array(); //reprocess to simplify the teacher info
	foreach($records as $code => $r)
	{
		foreach($r['sections'] as $section)
		{
			if(isset($section['teacher']))
			{
				$tStr = '';
				foreach($section['teacher'] as $teacher)
				{
					if($tStr != '')
						$tStr = $tStr . ' | ';
					$tStr = $tStr . $teacher->firstname;
				}
			}
			$section['teacher'] = $tStr;
			$new_section[] = $section;
		}
		$courses[$code]['fullname'] = $r['fullname'];
		$courses[$code]['sections'] = $new_section;
	}
	print_object($courses);
	return $courses;
}
function rc_ws_get_exam_schedule($data)
{
	global $DB, $CFG, $USER;
	$show_exam = rc_get_config('exam', 'show_exam_schedule'); //check if we need to display the exam
	
	//get the exam information
	$schedule = array();
	if($show_exam == 'yes')
	{		
		$sql = "select * from {rc_exam_student} where idnumber = '$data->value' and semester = '$CFG->semester' and exam_type = '$exam_type' order by exam_date_php";
		$rec = $DB->get_records_sql($sql);
		if($rec)
		{
			$exam_schedule_message = rc_get_config('exam', 'exam_schedule_message');	
			$exam_type = rc_get_config('exam', 'exam_type');
			$is_tentative = rc_get_config('exam', 'is_tentative');
			
			$schedule['exam_message'] = $exam_schedule_message;
			$schedule['exam_type'] = $exam_type == 1 ? 'Mid Term Examination' : 'Final Examination';
			$schedule['is_tentative'] = $is_tentative;			
			if($is_tentative == 'yes')
			{
				$schedule['tentative_message'] = rc_get_config('exam', 'tentative_message');
			}
			else
				$schedule['tentative_message'] = '';
			$exam_courses = array();
			foreach($rec as $exam)
			{
				$obj = new stdClass();
				$obj->course = $exam->course;
				$obj->exam_date = $exam->exam_date.' ('.$exam->exam_day.')';
				$obj->exam_time = substr($exam->exam_time, 0, 8);
				if($show_exam_venue == 'yes')
					$obj->room = $exam->room;
				else
					$obj->room = '-';
				$exam_courses[$exam->course] = $obj;
			}
			$schedule['schedule'] = $exam_courses;
		}
	}
	return $schedule;
}