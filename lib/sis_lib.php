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

//when subfield is used, it means we want the specific value
function sis_get_config($varname, $subfield='')
{
	global $DB;
	if($subfield != '')
	{
		$sql = "select * from {si_config} where name = ? and subfield = ?";
		$params = array($varname, $subfield);
		if($result = $DB->get_record_sql($sql, $params))
		{
			if(isset($result->var_value))
				return $result->var_value;
			else
				return '';
		}
		else
			return '';
	}
	else
	{
		$sql = "select * from {si_config} where name = ?";
		$params = array($varname);
		$result = $DB->get_records_sql($sql, $params);
		return $result;
	}
}

//if only single variable, put the subfiled name similar to varname to allow value retrieval
function sis_update_config($varname, $subfield, $value)
{
	global $DB;
	//delete the value first.
	if($subfield != '')
		$sub = " and subfield = '$subfield'";
	else
		$sub = '';
	$sql = "delete from {si_config} where name = '$varname'" . $sub;
	$DB->execute($sql);
	//now add it
	$sql = "insert into {si_config} (name, subfield, var_value) values('$varname', '$subfield', '$value')";
	$DB->execute($sql);
}

function sis_get_session($name)
{
	if(isset($_SESSION[$name]))
		return $_SESSION[$name];
	else
		return '';
}

function sis_set_session($name, $value)
{
	$_SESSION[$name] = $value;
}

//get all the users in a role and sub role
function sis_get_role_users($role, $subrole='', $role_value='')
{
	global $DB;
	if($subrole != '')
		$and = " and subrole = '$subrole'";
	else
		$and = '';
	$sql = "select a.*, b.username, b.firstname, b.lastname from {si_role} a inner join m_user b on a.moodle_user_id = b.id where a.role = '$role' $and order by a.role, a.moodle_user_id";
	return $DB->get_records_sql($sql);
}

//get a user role
function sis_get_user_role($moodle_user_id, $role='', $subrole='', $role_value='')
{
	global $DB;
	$params['moodle_user_id'] = $moodle_user_id;
	if($role != '')
		$params['role'] = $role;
	if($subrole != '')
		$params['subrole'] = $subrole;
	if($role_value != '')
		$params['role_value'] = $role_value;
	if($subrole == '' || $role_value != '') //no subrole, there can be more than 1 records
		return $DB->get_records('sis_role', $params, 'role');
	else	
		return $DB->get_record('sis_role', $params);
}

//get the sub roles for user within one role
function sis_get_user_all_role($moodle_user_id, $role)
{
	global $DB;
	$sql = "select subrole, id, role_value from {sis_role} where role = '$role' and moodle_user_id = '$moodle_user_id'";
	if($rec = $DB->get_records_sql($sql))
		return $rec;
	else
		return array();
}

//if roles is null, provide moodle_user_id and the role to retrieve the role from database
function sis_has_access($user_roles, $roles = null, $moodle_user_id = '', $role = '')
{
	if($roles == null)
		$roles = sis_get_user_all_role($moodle_user_id, $role);
	if(isset($roles['all'])) //if it has all, always return true
		return true;
	foreach($user_roles as $ur)
	{
		if(isset($roles[$ur])) //if one of the role is set, then we return true. This is like an OR statement
			return true;
	}
	return false;
}

function sis_has_user_role($moodle_user_id, $role='', $subrole='', $role_value='')
{
	if(sis_get_user_role($moodle_user_id, $role, $subrole, $role_value) === false)
		return false;
	else
		return true;
}

//get a list of roles (for now hard coded)
function sis_get_roles()
{
	$arr['attendance'] = 'Attendance';
	$arr['user'] = 'User';
	$arr['survey'] = 'Survey';
	$arr['position'] = 'Position';
	$arr['certificate'] = 'Certificate';
	$arr['external_resource'] = 'External Resources';
	$arr['schedule'] = 'Scheduling';
	$arr['course'] = 'Courses';
	return $arr;
}

function sis_get_subroles($role)
{	
	$arr['attendance'] = array(
		'unlock' => 'Unlock',
		'all' => 'All Operations',
		'report' => 'Report',
		'delete' => 'Delete Attendance',
		'dn' => 'DN List',
		'dn_read' => 'DN List (Read Only)',
		'excuse' => 'Excuse',
		'manage' => 'Manage Unlock',
		);
	$arr['user'] = array(
		'suspend' => 'Suspend',
		'logsis' => 'Logsis',
		);
	$arr['survey'] = array(
		'admin' => 'Admin',
		);
	$arr['position'] = array(
		'md' => 'MD',
		'dmd' => 'DMD',
		'hod' => 'HOD',
		);
	$arr['certificate'] = array(
		'admin' => 'Admin',
		);
	$arr['schedule'] = array(
		'all' => 'All',
		'college' => 'College',
		'department' => 'Department',
		);
	$arr['course'] = array(
		'all' => 'All',
		'college' => 'College',
		'department' => 'Department',
		);
	
	$ret = $arr[$role];		
	return $ret;
}

//tmp course is the most important template course that defines the weekly structure
function sis_get_tmp_course()
{
	global $DB;
	$course = $DB->get_record('course', array('shortname' => 'TMP101'), '*', MUST_EXIST);
	return $course;
}

//get an item from array. 
function sis_array_item($arr, $key)
{
	if(isset($arr[$key]))
		return $arr[$key];
	else
		return '';
}

function sis_get_day_array()
{
	$arr = array(
		1 => 'Saturday', 
		2 => 'Sunday', 
		3 => 'Monday', 
		4 => 'Tuesday',
		5 => 'Wednesday',
		6 => 'Thursday',
		7 => 'Friday'
	);
	return $arr;
}

function sis_get_day_text($aDayIndex)
{
	$arr = sis_get_day_array();
	if(isset($arr[$aDayIndex]))
		return $arr[$aDayIndex];
	else
		return '';
}

//get the weekly structure of SIS courses. This will provide an array with week number or vacation info which will replace the text in weekly structure
//detail = true to have the reason. Otherwise, just put true (included) or false (excluded)
function sis_academic_week($detail = true)
{
	global $DB;
	$sql = "select * from {si_config} where name = ? and subfield like ?";	
	$reason = $DB->get_records_sql($sql, array('attendance', 'reason_%'));
	$arr = array();
	$reason_arr = array();
	$count = 1;
	foreach($reason as $r)
	{
		$a = explode('_', $r->subfield);
		$reason_arr[$a[1]] = $r->var_value;
	}
	ksort($reason_arr);
	foreach($reason_arr as $key => $r)
	{
		if($r == '') //no reason, so not excluded
		{
			if($detail)
				$arr[$key] = 'Week ' . $count; //create the week label
			else
				$arr[$key] = $count;
			$count++; //increase the count if we have an included week
		}
		else
		{
			if($detail)
				$arr[$key] = $r; //Use the reason. We never increase the week of vacation
			else
				$arr[$key] = false;
		}
	}
	return $arr;
}

function sis_get_course_teacher($course)
{
	global $DB;
	$role = $DB->get_record('role', array('shortname' => 'editingteacher'));
	$context = get_context_instance(CONTEXT_COURSE, $course->id);
	$teachers = get_role_users($role->id, $context);
	return $teachers;
}

//given the role short name, check if user has the role
function sis_has_role($user, $role)
{
	global $DB;
	$role = $DB->get_record('role', array('shortname' => $role));
	$context = get_context_instance(CONTEXT_SYSTEM);
	$roles = get_role_users($role->id, $context);
	if(isset($roles[$user->id]))	
		return true;
	else
		return false;
}

function sis_is_suspended($redir = true)
{
	global $CFG;
	if(isset($_SESSION['sis_suspended']))
	{
		if($_SESSION['sis_suspended'] === true)
		{			
			if($redir)
			{
				$url = new moodle_url($CFG->wwwroot . '/local/sis/suspended.php');
			    redirect($url);
			}
			else
				return true;
		}
	}	
}

//this is the function that will run on every page to check if any function needs to be executed first
function sis_bootstraper($redir = true)
{
	global $CFG, $PAGE, $COURSE, $USER;
	//check if user is suspended
	sis_is_suspended();
	//for course page bootstraper
	if($PAGE->pagelayout == 'course')
	{
		//Check for QC Survey
		if(sis_has_incomplete_survey($COURSE, $USER)) //if has survey, redirect to do survey
		{
			$survey_url = new moodle_url($CFG->wwwroot.'/local/rcyci/survey/survey.php', array('id' => $COURSE->id));		
			redirect($survey_url);
		}		
	}
	return false;
}

function sis_get_current_week($now)
{
	$sis_week = sis_academic_week(false); //get the rc vacation week (it is an array of week replacement)
	$max = count($sis_week);	
	for($i = 1; $i <= $max; $i++)
	{
		$w = sis_week_date($i);
		if($now >= $w['Sunday']['date_php'] && $now < $w['Saturday']['date_php'] + 86400) //add to make it to 11.59pm
			return $i;
	}
}

//given a week, return an array of day => date
function sis_week_date($week)
{
	$week--;
	$course = sis_get_tmp_course();
	$startdate = $course->startdate;
	$currentweek = strtotime(date('Y-m-d', $startdate) . " +$week week");
	$arr = array();
	for($i = 0; $i < 7; $i++)
	{
		$arr[date('l', $currentweek)] = array('date_php' => $currentweek, 'date' => date('d-M-Y', $currentweek), 'day' => date('l', $currentweek));
		$currentweek = strtotime(date('Y-m-d', $currentweek) . " +1 day");
	}
	return $arr;
}

//given a name, return no space so it will not wrap
function sis_no_space($name)
{
	$name = str_replace(' ', '_', $name);
	$name = str_replace('-', '_', $name);
	return $name;
}

//convert date to hijrah given a date in the format d-M-Y
function sis_to_hijrah($aDate, $format = "d/m/Y")
{
	global $CFG;
	require_once $CFG->dirroot . '/local/rcyci/lib/sis_hdate.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
	$hdate = new HijriDateTime();
	return $hdate->gregorianToHijrah($aDate, $format);	
}

//given a western number, replace with arabic number
function sis_arabic_number($num)
{
	$western_arabic = array('0','1','2','3','4','5','6','7','8','9');
	$eastern_arabic = array('٠','١','٢','٣','٤','٥','٦','٧','٨','٩');

	return str_replace($western_arabic, $eastern_arabic, $num);	
}

//because web service is very difficult to debug, we have a custom print_object function that writes to a database field.
//then a custom debug page will show the content of the output. New session will clear the database. If not, each call will
//write to a new record
function sis_print_object($object, $new_session = false)
{
	global $DB;
	if($new_session)
		sis_reset_debug();
	$rec = new stdClass();
	$rec->output = '<pre>' . htmlspecialchars(print_r($object,true)) . '</pre>';
	$DB->insert_record('sis_debug', $rec, false);
} 

//empty the debug table
function sis_reset_debug()
{
	global $DB;
	$sql = "delete from {sis_debug}";
	$DB->execute($sql);
}

//log the action
function sis_log($data)
{
	global $DB;
	$DB->insert_record('sis_log', $data, false);
}
