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
 * This file contains main class for the course format Weeks
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns navigation controls (tabtree) to be displayed on cohort management pages
 *
 * @param context $context system or category context where cohorts controls are about to be displayed
 * @param moodle_url $currenturl
 * @return null|renderable
 */
 
//display basic timetable
function rc_timetable($user, $isPrinting = false) 
{
	$unit = array();
	$timetable = rc_get_timetable_object($user, $unit);
	rc_timetable_display($timetable, $unit, $isPrinting);
}

//merge a user timetable with a section timetable
function rc_timetable_merge($user, $class_nbr)
{
	$unit = array();
	$timetable = rc_get_timetable_object($user, $unit);
	rcyci_timetable_merge_section($timetable, $class_nbr);
	rc_timetable_display($timetable, $unit);
}

//given a timetable object, display it
function rc_timetable_display($timetable, $unit, $isPrinting = false)
{
	if($isPrinting)
		$tableClass = 'custom-table-2';
	else
		$tableClass = 'custom-table-1';
	$courselist = array();
	$workload = rc_get_timetable_workload($timetable, $courselist, $unit);
	$str = '';
	$str = $str . '<div class="' . $tableClass . '">';
	$str = $str . '<table width="100%" border="1">';
	//header
	//get an object for the time
	$data = rc_timetable_get_empty_timeslot();
	$str = $str . '<tr align="center">';
	$str = $str . '<th>DAY</th>';
	if($isPrinting) //if it is printing, we have to fixed the column widht. For screen no need
	{
		$dayWidth = 'width="5%"';
		$aWidth = 95 / count($data);
		$columnWidth = 'width="'.$aWidth.'%"';
	}
	else
	{
		$dayWidth = '';
		$columnWidth = '';
	}
	foreach($data as $key => $d)
	{
		if($isPrinting)
			$str = $str . '<th width="'.$columnWidth.'">'.$key.'</th>';
		else
			$str = $str . '<th>'.$key.'</th>';
	}
	$str = $str . '</tr>';
	foreach($timetable as $theDay => $rec)
	{
		//check how many row to merge down
		$rows = count($rec);
		if($rows > 1)
			$color = 1;
		else
			$color = 0;
		$rowspanned = false;
		//for each row
		foreach($rec as $t)
		{
			$tableStr = '<tr align="center">';
			if(!$rowspanned)
			{
				if($rows > 1)
				{
					$tableStr = $tableStr . '<td '.$dayWidth.' valign="top" rowspan="'.$rows.'"><strong>'. $theDay . '</strong></td>'; //sort order
					$rowspanned = true;
				}
				else
					$tableStr = $tableStr . '<td '.$dayWidth.'><strong><p>'. $theDay . '</p></strong></td>'; //sort order
			}
			$content = '';
			$prevCol = '~!@#';
			$colSpan = 1;
			$prevKey = '';
			foreach($t as $key => $col)
			{
				if($prevCol != '~!@#' && ($col == '' || $col != $prevCol)) //if content is empty, we write out
				{
					$timetableContent = rc_timetable_get_display_content($prevCol);
					if($colSpan > 1) //need to combine column
						$content = '<td '.$columnWidth.' colspan="' . $colSpan . '" class="highlight-'.$color.'">' . $timetableContent . '</td>';
					else
					{
						if($timetableContent != '') //has some class, must look at the highlighting
							$content = '<td '.$columnWidth.' class="highlight-'.$color.'">'. $timetableContent . '</td>';
						else
							$content = '<td '.$columnWidth.'>'. $timetableContent . '</td>';
					}
					$tableStr = $tableStr . $content;
					$colSpan = 1;
				}
				else
				{
					if($col == $prevCol) //same as previous content, increase the span
						$colSpan++;
				}
				$prevCol = $col;
				$prevKey = $key;
			}
			$timetableContent = rc_timetable_get_display_content($prevCol);
			//we have to write it at least once because we skip the first round
			if($colSpan > 1) //need to combine column
				$content = '<td '.$columnWidth.' colspan="' . $colSpan . '" class="highlight-'.$color.'">' . $timetableContent . '</td>';
			else
			{
				if($timetableContent != '') //has some class, must look at the highlighting
					$content = '<td '.$columnWidth.' class="highlight-'.$color.'">'. $timetableContent . '</td>';
				else
					$content = '<td '.$columnWidth.'>'. $timetableContent . '</td>';
			}
			$tableStr = $tableStr . $content;
			$tableStr = $tableStr . '</tr>';		
			$str = $str . $tableStr;
		}
	}
	$str = $str . '</table>';
	$str = $str . '</div>';
	$str = $str . '<br />';
	$cList = implode(', ', $courselist);
	$unit_value = 0;
	foreach($unit as $u)
		$unit_value = $unit_value + $u;

	$load = 'Total Class Hour : <span class="pull-right">'.$workload.' Hours</span>';
	if($unit_value != 0)
	{
		$load = $load . '<br />Total Credit Hour : <span class="pull-right">'.$unit_value.' Credits</span>';
	}

	//for the footer
	$str = $str . '<table width="100%" border="0" cellpadding="4">';
	$str = $str . '<tr>';
	$str = $str . '<td width="100" valign="top"><strong>Course(s)</strong></td>';
	$str = $str . '<td valign="top"><strong>'.$cList.'</strong></td>';
	$str = $str . '<td width="100" valign="top">&nbsp;</td>';
	$str = $str . '<td width="250" valign="top"><strong>'.$load.'</strong></td>';
	$str = $str . '</tr>';

	$str = $str . '</table>';
	$str = $str . '<br />&nbsp;';
	echo $str;
}

function rc_get_timetable_workload($timetable, &$courses, $unit)
{
	$w = 0;
	$count = 1;
	foreach($timetable as $t)
	{
		foreach($t as $u)
		{
			foreach($u as $v)
				if($v != '')
				{
					$w++;
					$arr = explode(' ', $v);
					$code = $arr[0];
					$brr = explode('-', $code);
					$code_raw = $brr[0];
					if(isset($unit[$code_raw]))
					{
						$u = $unit[$code_raw];
						$courses[$code . ' (' . $u . ')'] = $count;
					}
					else
						$courses[$code] = $count;
					$count++;
				}
		}
	}
	ksort($courses);
	$courses = array_flip($courses);
	return $w;
}
//retrieve the timetable object
function rc_get_timetable_object($user, &$unit, $room = false)
{
	global $CFG;
	$user_type = '';
	if($room) //it is room timetable
	{
		$rs = rc_ps_get_room_timetable($user); //the user is actually the room code
	}
	else
	{
		$user_type = rc_get_user_type($user); //remember the user type as well
		if($user_type == 'teacher')
		{
			$rs = rc_ps_get_teacher_timetable($user);
			$student_courses = array();
		}
		else
		{
			$rs = rc_ps_get_student_timetable($user);
			
			$student_courses = rc_get_user_courses($user->id, $user_type, false);
		}
	}
	$t = rc_timetable_get_empty_timetable();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		$proceed = true;
		if($user_type == 'student')
		{
			$code = $rec['CATALOG_NBR'];
			if($rec['CAMPUS'] == 'YUC-M')
				$code = $code . '-M';
			else if($rec['CAMPUS'] == 'YUC-F')
				$code = $code . '-F';			
			if(!isset($student_courses[$code])) //only do it if the course is in the student course list
				$proceed = false;
			else if(!isset($unit[$rec['CATALOG_NBR']]) || $unit[$rec['CATALOG_NBR']] == 0)
				$unit[$rec['CATALOG_NBR']] = $rec['UNT_TAKEN'];
		}
		if($proceed)
		{
			rcyci_timetable_explode_record($t, $rec);
		}
		$rs->MoveNext();
	}
	//after we have finish, we have to loop and fill up any day which does not have period. It must at least has one
	$emptySlot = rc_timetable_get_empty_timeslot(); //get an empty time slot that represents the array
	foreach($t as $key => $aDay)
	{
		if(empty($aDay))
		{
			$t[$key][] = $emptySlot;
		}
	}
	return($t);
}

function rcyci_timetable_extract_lecturer_slot($slotText)
{
	$arr = explode('<br />(', $slotText);
	if(isset($arr[1])) //has lecturer
	{
		$brr = explode(')', $arr[1]);
		return $brr[0];
	}
	else
		return '';
}

//check if it is a multi lecturer course in the same slot
function rcyci_timetable_multi_lecturer_slot($slotText, $classText)
{
	$slotArr = explode('|', $slotText);
	$classArr = explode('|', $classText);
	if(isset($slotArr[1]) && isset($classArr[1]))
	{
		if($slotArr[1] == $classArr[1]) //same section with different teacher
		{
			//merge the lecturer
			$arr[] = rcyci_timetable_extract_lecturer_slot($slotArr[0]);
			$arr[] = rcyci_timetable_extract_lecturer_slot($classArr[0]);
			$str = '';
			foreach($arr as $a)
			{
				if($str != '')
					$str = $str . ',<br />';
				$str = $str . $a;
			}
			if($str != '')
				return $slotArr[1] . '<br />(' . $str . ')' . '|' . $slotArr[1];
			else
				return false;
		}
		else
			return false;
	}
	else
		return false;
}

//one record could have multiple schedule (if same time but different day. We explode it before adding)
function rcyci_timetable_explode_record(&$t, $rec)
{			
	if($rec['MON'] == 'Y')
	{
		$rec['DAYINDEX'] = rc_timetable_get_class_day_index('MON');
		rcyci_timetable_add_to_timetable($t, $rec);
	}
	if($rec['TUES'] == 'Y')
	{
		$rec['DAYINDEX'] = rc_timetable_get_class_day_index('TUES');
		rcyci_timetable_add_to_timetable($t, $rec);
	}
	if($rec['WED'] == 'Y')
	{
		$rec['DAYINDEX'] = rc_timetable_get_class_day_index('WED');
		rcyci_timetable_add_to_timetable($t, $rec);
	}
	if($rec['THURS'] == 'Y')
	{
		$rec['DAYINDEX'] = rc_timetable_get_class_day_index('THURS');
		rcyci_timetable_add_to_timetable($t, $rec);
	}
	if($rec['FRI'] == 'Y')
	{
		$rec['DAYINDEX'] = rc_timetable_get_class_day_index('FRI');
		rcyci_timetable_add_to_timetable($t, $rec);
	}
	if($rec['SAT'] == 'Y')
	{
		$rec['DAYINDEX'] = rc_timetable_get_class_day_index('SAT');
		rcyci_timetable_add_to_timetable($t, $rec);
	}
	if($rec['SUN'] == 'Y')
	{
		$rec['DAYINDEX'] = rc_timetable_get_class_day_index('SUN');
		rcyci_timetable_add_to_timetable($t, $rec);
	}	
}
//since every day is an array of periods, we have to merge the period to minimize the number of rows
//validate will only validate if there is conflict. If valicate is false, then it will add to timetable for display purpose
function rcyci_timetable_add_to_timetable(&$timetable, $rec)
{				
	$duration_per_slot = 60 * 60;
	$key = rc_get_day_text($rec['DAYINDEX']); //get the day
	if(!isset($timetable[$key])) //if no key, then it is unassigned section. Don't do anything
	{
		return;
	}
	//get the fields for display
	$aColor = rc_timetable_get_slot_display($rec, $rec['CAMPUS'], true);
	$fieldList = array('subject' => '0');
	$classText = rc_timetable_get_slot_display($rec, $rec['CAMPUS']) . '|' . $aColor;
	$aDay = $timetable[$key]; //get the day element
	$added = false;
	
	$sTime = strtotime('1-JAN-1970 ' . $rec['START_TIME']);
	$eTime = strtotime('1-JAN-1970 ' . $rec['END_TIME']);
	$duration = rc_timetable_get_class_duration($sTime, $eTime);
	$dayIndex = $rec['DAYINDEX'];
	
	$added = false;
	foreach($aDay as $x => $t) //loop the period array
	{
		//first check if the period can fit, i.e. no course in it
		$ok = true;			
		for($i = $sTime; $i < $eTime; $i = $i + $duration_per_slot)
		{
			$aTime = rc_timetable_format_formal_time($i);
			if(isset($t[$aTime]) && $t[$aTime] != '')// has something, can't fit
			{
				//check if it is due to multiple lecturers
				$multiLecturer = rcyci_timetable_multi_lecturer_slot($t[$aTime], $classText);
				if($multiLecturer !== false) //update it
				{
					$t[$aTime] = $multiLecturer;
					$added = true;
				}
				$ok = false;
				break;
			}				
		}
		if($ok) //pass the checking, add it
		{
			for($i = $sTime; $i < $eTime; $i = $i + $duration_per_slot)
			{
				$aTime = rc_timetable_format_formal_time($i);
				$t[$aTime] = $classText;
			}
			$added = true;
		}
		if($added) //already added, no need to loop anymore
		{
			$aDay[$x] = $t; //reinitialize the array
			$timetable[$key] = $aDay;
			break;
		}
	}
	if(!$added) //after all the process and not added, get a new empty row
	{
		$emptySlot = rc_timetable_get_empty_timeslot(); //get an empty time slot that represents the array
		for($i = $sTime; $i < $eTime; $i = $i + $duration_per_slot) //now add it
		{
			$aTime = rc_timetable_format_formal_time($i);
			$emptySlot[$aTime] = $classText;
		}
		$added = true;
		$timetable[$key][] = $emptySlot; //reinitialize the timetable
	}
}

function rc_timetable_get_empty_timeslot()
{
	$period = 17;
	$brr = array();
	for($i = 1; $i < $period; $i++) //from 7:15 to 22:15
	{
		$brr[rc_timetable_get_period_time($i)] = '';
	}
	return $brr;
}


function rc_timetable_get_display_content($content)
{
	if($content == '' || strlen($content) == 1)
		return '';
	else //it is content, split the content
	{
		$arr = explode('|', $content);
		$theContent = $arr[0];
		return $theContent;
	}
}


/////////////////////////////////////////////////////////
//////generic timetable processing functions
////////////////////////////////////////////////
//format the time to 13:15 format. $aTime is php timestamp
function rc_timetable_format_formal_time($aTime)
{
	$t = date('H:i', $aTime);
	return $t;
}

//get the class duration given php timestamp
function rc_timetable_get_class_duration($f, $t)
{
	$x = ($t - $f)/3600;
	return $x;
}
	

function rc_timetable_get_class_day($rec)
{
	if($rec['MON'] == 'Y')
		return 3;
	else if($rec['TUES'] == 'Y')
		return 4;
	else if($rec['WED'] == 'Y')
		return 5;
	else if($rec['THURS'] == 'Y')
		return 6;
	else if($rec['FRI'] == 'Y')
		return 7;
	else if($rec['SAT'] == 'Y')
		return 1;
	else if($rec['SUN'] == 'Y')
		return 2;
	else
		return '';
}

function rc_timetable_get_class_day_index($theDay)
{
	if($theDay == 'MON')
		return 3;
	else if($theDay == 'TUES')
		return 4;
	else if($theDay == 'WED')
		return 5;
	else if($theDay == 'THURS')
		return 6;
	else if($theDay == 'FRI')
		return 7;
	else if($theDay == 'SAT')
		return 1;
	else if($theDay == 'SUN')
		return 2;
	else
		return '';
}

//-1 means no break time
function rc_timetable_get_period_time($period)
{
	$arr = array(
		'1' => '07:15', 
		'2' => '08:15', 
		'3' => '09:15', 
		'4' => '10:15',
		'5' => '11:15',
		'6' => '12:15',
		'7' => '13:15',
		'8' => '14:15',
		'9' => '15:15',
		'10' => '16:15',
		'11' => '17:15',
		'12' => '18:15',
		'13' => '19:15',
		'14' => '20:15',
		'15' => '21:15',
		'16' => '22:15',
		'17' => '23:15',
	);
	return $arr[$period];
}
	
function rc_timetable_get_break_text($num)
{
	if($num == 2)
		return "B";
	else if($num == 3)
		return "R";
	else if($num == 4)
		return "E";
	else if($num == 5)
		return "A";
	else if($num == 6)
		return "K";
	else
		return "";
}

function rc_timetable_get_time_period($i = -1)
{
	$breakPeriod = 6;
	$breakTime = '12:15'; //same as period
	$num_period = '17';
	$brr = array();
	for($j = 1; $j < $num_period; $j++) //from 7:15 to 22:15
	{
		$period = rc_timetable_get_period_time($j);
		if($j == $breakPeriod)
		{
			if($i != -1)
				$brr[$period] = rc_timetable_get_break_text($i);
			else
				$brr[$period] = '';
		}
		else
			$brr[$period] = '';
	}
	return $brr;
}

function rc_timetable_get_empty_timetable()
{
	$arr = array();
	//it is from sunday to thursday
	//each day holds an array of timetslot array. In other word, every assignment in the day is a complete set of array of time slot from start to end
	for($i = 2; $i <= 6; $i++)
	{
		$arr[rc_get_day_text($i)] = array();
	}
	return $arr;
}

//given section text in peoplesoft, format it to readable format
function rc_timetable_format_section($sec)
{
	$ct = $sec[0] . $sec[1];
	$n = $sec[2] . $sec[3];
	if($ct == 'LC')
		$ct = 'T';
	else
		$ct = 'L';
	return $n . '/' . $ct;
}

//given a class record (in array), return the field to be display in a time slot (use for course schedule)
//unique signature will produce the unique text without lecturer. This will allow us to make sure that section 2 with 2 different lecturers can be combined
function rc_timetable_get_slot_display($rec, $campus, $uniqueSignature = false)
{
	$space = '<br />';
	$lecturer = '';
	if(isset($rec['FACILITY_ID']))
		$room = $space . $rec['FACILITY_ID'];
	else
		$room = '';
	//for now we avoid shoing lecturer in timetable
	if(isset($rec['SAFEER']) && $rec['SAFEER'] != '' && $uniqueSignature == false) //has employee id. If unique, we skip lecturer
		$lecturer = '<br />(' . $rec['NAME_FORMAL'] . ')';
	if($campus == 'YUC-F')
		$campusCode = '-F';
	else if($campus == 'YUC-M')
		$campusCode = '-M';
	else
		$campusCode = '';
	

	$str =  $rec['CATALOG_NBR'] . $campusCode . ' (' . 
			rc_timetable_format_section($rec['CLASS_SECTION']) . ')' . 
			$room;
	$str = $str . $lecturer;
	return $str;
}

function rc_timetable_user_form($idnumber = '')
{
	$str = '<form id="form1" name="form1" method="post" onsubmit="return validateForm()" action="">
		Employee ID / Student ID: <input name="user" type="text" id="user" size="10" maxlength="15" value="'.$idnumber.'" />&nbsp;&nbsp;&nbsp;
		<input type="submit" name="button2" id="button2" value="Search" />
	</form>';
	return $str;
}

//given a timetable object and a course, validate if there is conflict in the schedule
function rcyci_timetable_merge_section(&$t, $class_nbr)
{
	$rs = rc_ps_get_section_timetable($class_nbr, true);
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		rcyci_timetable_explode_record($t, $rec);
		$rs->MoveNext();
	}
}

//check if a given user has timetable conflict
function rcyci_timetable_timetable_conflict($emplid)
{
	global $DB;
	$user = $DB->get_record('user', array('idnumber' => $emplid));
	if($user)
	{
		$unit = array();
		$timetable = rc_get_timetable_object($user, $unit);
		foreach($timetable as $day)
		{
			if(count($day) > 1) //if any of the day have more than one records, it means there is conflict
				return true;
		}
	}
	return false;
}

//given a timetable object and a course, validate if there is conflict in the schedule
function rcyci_timetable_schedule_conflict($timetable, $course)
{
	$t = $timetable; //clone it so we will nto alter the timetable (remember in PHP array is alway a copy not reference)
	$rs = rc_ps_get_section_timetable($course['idnumber'], true);
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		if(!rcyci_timetable_validate_timetable($t, $rec)) //if it return false, means there is conflict
			return true; //return true to indicate that there is conflict
		$rs->MoveNext();
	}
	return false;
}

//since every day is an array of periods, we have to merge the period to minimize the number of rows
//validate will only validate if there is conflict. If valicate is false, then it will add to timetable for display purpose
function rcyci_timetable_validate_timetable(&$timetable, $rec, $validate = true)
{				
	$duration_per_slot = 60 * 60;
	$key = rc_get_day_text($rec['DAYINDEX']); //get the day
	if(!isset($timetable[$key])) //if no key, then it is unassigned section. Don't do anything
	{
		return;
	}
	//get the fields for display
	$aColor = rc_timetable_get_slot_display($rec, $rec['CAMPUS'], true);
	$fieldList = array('subject' => '0');
	$classText = rc_timetable_get_slot_display($rec, $rec['CAMPUS']) . '|' . $aColor;
	
	$aDay = $timetable[$key]; //get the day element
	$added = false;
	
	$sTime = strtotime('1-JAN-1970 ' . $rec['START_TIME']);
	$eTime = strtotime('1-JAN-1970 ' . $rec['END_TIME']);
	$duration = rc_timetable_get_class_duration($sTime, $eTime);
	$dayIndex = $rec['DAYINDEX'];
	
	$added = false;
	foreach($aDay as $x => $t) //loop the period array
	{
		//first check if the period can fit, i.e. no course in it
		$ok = true;			
		for($i = $sTime; $i < $eTime; $i = $i + $duration_per_slot)
		{
			$aTime = rc_timetable_format_formal_time($i);
			if(isset($t[$aTime]) && $t[$aTime] != '')// has something, can't fit
			{
				//check if it is due to multiple lecturers
				$multiLecturer = rcyci_timetable_multi_lecturer_slot($t[$aTime], $classText);
				if($multiLecturer !== false) //update it
				{
					$t[$aTime] = $multiLecturer;
					$added = true;
				}
				$ok = false;
				break;
			}				
		}
		if($validate) //if it is validate only
		{
			if(!$ok) //if the validation fail, then it means has conflict
				return false;
		}
		if($ok) //pass the checking, add it
		{
			for($i = $sTime; $i < $eTime; $i = $i + $duration_per_slot)
			{
				$aTime = rc_timetable_format_formal_time($i);
				$t[$aTime] = $classText;
			}
			$added = true;
		}
		if($added) //already added, no need to loop anymore
		{
			$aDay[$x] = $t; //reinitialize the array
			$timetable[$key] = $aDay;
			break;
		}
	}
	if(!$added) //after all the process and not added, get a new empty row
	{
		$emptySlot = rc_timetable_get_empty_timeslot(); //get an empty time slot that represents the array
		for($i = $sTime; $i < $eTime; $i = $i + $duration_per_slot) //now add it
		{
			$aTime = rc_timetable_format_formal_time($i);
			$emptySlot[$aTime] = $classText;
		}
		$added = true;
		$timetable[$key][] = $emptySlot; //reinitialize the timetable
	}
	return true;
}

/////////////room schedule//////////////
function rc_timetable_room_form($campus)
{
	if(isset($_SESSION['rc_timetable_room']))
		$defaultRoom = $_SESSION['rc_timetable_room'];
	else
		$defaultRoom = '';
	$roomList = rc_ps_get_roomlist($campus, false);
	$campuses = rc_campus();
	
	//get the weekly
	$course = rc_get_tmp_course();
    $modinfo = get_fast_modinfo($course);
	$a = $modinfo->get_section_info_all();
	$startdate = $course->startdate;
	$totalweek = count($a); //total week starts with 0, but 0 is not use as week

	$weekStr = '<select name="week">';
	$current_week = $startdate;						
	$weekStr = $weekStr . '<option value="0">All Weeks</option>';
	for($i = 1; $i <= $totalweek; $i++)
	{
		if($i < 10)
			$pad = '0';
		else
			$pad = '';
		$end_week = strtotime(date("Y-m-d", $current_week) . " +1 week") - 1;
		$week_text = 'Week ' . $pad.$i . ' (' . date('d-M-Y', $current_week).' - '.date('d-M-Y', $end_week) . ')';
		$weekStr = $weekStr . '<option value="'.$i.'">'.$week_text.'</option>';
		$current_week = strtotime(date("Y-m-d", $current_week) . " +1 week");
	}

	$weekStr = $weekStr . '	</select>';
				
	
	$str = '<form id="form1" name="form1" method="post" onsubmit="return search_room()" action="">
		Room Code: <select name="room" id="room" value="'.$defaultRoom.'">';
	foreach($roomList as $r)
	{
		if(trim($r['FACILITY_ID']) != '')
			$str = $str . '<option value="'.$r['FACILITY_ID'].'">'.$r['FACILITY_ID'].'</option>';
	}
	$str = $str . '</select>
		&nbsp;&nbsp;
		Campus: <select name="campus" id="campus" value="" onchange="refresh_room()">';
	foreach($campuses as $key => $c)
	{
		if($campus == $key)
			$select = 'selected';
		else
			$select = '';
		$str = $str . '<option value="'.$key.'" '.$select.'>'.$c.'</option>';
	}
	$str = $str . '</select>
		&nbsp;&nbsp;
		Week: '.$weekStr.'
		&nbsp;&nbsp;
		<input type="button" name="button2" id="button2" value="Search" onclick="search_room()"/>
	</form>';
	return $str;	
}

//display basic timetable
function rc_room_timetable($code, $isPrinting = false) 
{
	$unit = array();
	$timetable = rc_get_timetable_object($code, $unit, true); //true to indicate it is a room timetable
	rc_timetable_display($timetable, $unit, $isPrinting);
}

function print_room_timetable_booking_form($start_time, $week)
{	
	$days = rc_get_day_array();
	$str = '';
	$dayStr = '<select name="day">';
	foreach($days as $key => $t)
	{
		if($key >= 2 && $key < 7)
			$dayStr = $dayStr . '<option value="'.$key.'">'.$t.'</option>';
	}
	$dayStr = $dayStr . '	</select>';
	$period = rc_timetable_get_empty_timeslot();
	$tempTime = $startTime;
	$totalPeriod = 17;
	$opt = '<select name="time">';
	$period = '<select name="period">';
	for($i = 1; $i < $totalPeriod; $i++) //from 7:15 to 22:15
	{
		$theTime = rc_timetable_get_period_time($i) . ' - ' . rc_timetable_get_period_time($i + 1);
		$opt = $opt . '<option value="'.$i.'">'.$theTime.'</option>';
		$period = $period . '<option value="'.$i.'">'.$i.'</option>';
		
	}
	$opt = $opt . '</select>';
	$period = $period . '</select>';
		
	$str = $str . 'Day : '.$dayStr . '&nbsp;&nbsp';
	$str = $str . 'Time : '.$opt . '&nbsp;&nbsp;';
	$str = $str . 'Number of Periods : '.$period . '&nbsp;&nbsp;';
	$str = $str . 'Purpose : <input type="text" name="description" size="30" maxlength="40" />&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button3" id="button3" value="Book room" onclick="book_room()" />' . '&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button4" id="button4" value="  Cancel  " onclick="cancel_book_room()" />';
	$str = $str . '<input type="hidden" name="week" value="'.$week.'">';
	return $str;
}

	//save the room booking into database
	function rc_room_add_room_booking($d, $t, $p, $w, $des, $roomID, $tt)
	{
		global $USER, $CFG;
		$startTime = $tt->getOracleTime($t);
		$endTime = $tt->getOracleTime($t + $p);
		$sql = "INSERT INTO m_yic_room_booking (DAY, STARTS, ENDS, ROOM, E_FIRST_NAME, E_FAMILY_NAME, EFFECTIVE_WEEK, REPEAT_WEEK, DESCRIPTION, STATUS, MOODLE_USERID, LOGSIS_USERID, SEMESTER) values($d, $startTime, $endTime, $roomID, '$USER->firstname', '$USER->lastname', $w, $w, '$des', 1, '$USER->id', '$USER->idnumber', '$CFG->semester')";
		execute_sql($sql, false);		
	}

