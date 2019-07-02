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
   This file contains RCYCI Output rendering functions, such as logo and print header
*/

// This is the library for custom user interface
defined('MOODLE_INTERNAL') || die();

//given a semester, format it according to semester and year
function sis_output_format_semester($semester)
{
	$y = $semester[1] . $semester[2];
	$sem = $semester[3];
	$y_full = '20' . $y;
	$aGYear = $y_full - 579;
	$aGYear2 = substr($aGYear, 2, 2);
	$aGYear2 = $aGYear2 + 1;
	
	$arr = array(
			'year' => $y,
			'year_full' => $y_full,
			'year_n' => $y + 1,
			'year_full_n' => $y_full + 1,
			'hijri' => $aGYear,
			'hijri_n' => $aGYear + 1,
			'semester' => $sem,
		);
	return $arr;
}

function sis_output_show_arabic($string, $text, $font=3, $ltr=false)
{
	$str = $string[$text];
//	return $str; //temporary for utf wording
	$retStr = '<font size="'.$font.'">'.iconv("windows-1256", "UTF-8", $str).'</font>';
	if($ltr)
		$retStr = '<div dir="rtl">' . $retStr . '</div>';
	return $retStr;
}

function sis_output_show_arabic_plain($str, $font=3, $ltr=false)
{
//	return $str; //temporary for utf wording
	$retStr = '<font size="'.$font.'">'.iconv("windows-1256", "UTF-8", $str).'</font>';
	if($ltr)
		$retStr = '<div dir="rtl">' . $retStr . '</div>';
	return $retStr;
}

function sis_output_show_arabic_utf($string, $text, $font=3, $ltr=false)
{
	$str = $string[$text];
	$retStr = '<font size="'.$font.'">'.$str.'</font>';
	if($ltr)
		$retStr = '<div dir="rtl">' . $retStr . '</div>';
	return $retStr;
}


/********************************************************
******* UNVERIFIED FUNCTIONS ****************************/

//given a parsed course, return the format as required
function sis_output_format_course($course, $withName = true)
{
	$code = $course['course_code'] . ' (' . $course['section'] . ' ' . $course['class_type'] . ')';
	if($withName)
		$code = $code . ' - ' . $course['fullname'];
	return $code;
}

//given class type as LC or LB, return the format as Theory or Lab
function sis_output_format_class_type($ct)
{
	$s = $ct[0] . $ct[1]; //get the theory or lab
	if($s == 'LC')
		$class_type = 'Th';
	else
		$class_type = 'Lab';
	return $class_type;
}


//This create the title for the rc box
function sis_output_print_attendance_header($c, $week, $week_day, $isAdmin) 
{
	global $CFG, $USER;
	$course = sis_parse_course($c);
	if(!$isAdmin)
		$teacher = $USER->firstname;
	else
	{
		$teachers = $course['teacher'];
		$teacher = '';
		foreach($teachers as $t)
		{
			if($teacher != '')
				$teacher = $teacher . ', ';
			$teacher = $teacher . $t->firstname;
		}
	}
	$sun = $week_day['Sunday'];
	$friday = $week_day['Friday'];
	$from = $sun['date'] . ' - ' . $friday['date'];
	$semester = sis_output_format_semester($CFG->semester);
	$str = '';
	$str = $str . '<table width="100%" border="0" cellpadding="5">';
	$str = $str . '<tr align="center">';
	$str = $str . '<th colspan="6">
						<strong>
							ROYAL COMMISSION COLLEGES AND INSTITUTES<br>
							Administration and Registration Department<br>
							Course Attendance Sheet
						</strong>
				   </th>';
	$str = $str . '</tr>';
	$str = $str . '<tr valign="top">';
	$str = $str . '<td width="5%"><strong>Course</strong></td>';
	$str = $str . '<td width="40%">'.$course['course_code'] . ' - ' . $course['fullname'] .'</td>';
	$str = $str . '<td width="5%"><strong>Section</strong></td>';
	$str = $str . '<td width="30%">'.$course['section'] . ' (' . $course['class_type'] . ')</td>';
	$str = $str . '<td width="5%"><strong>Semester</strong></td>';
	$str = $str . '<td width="15%">'.$semester['semester'] . '(' . $semester['year_full'] . '-' . $semester['year_n'] .')</td>';
	$str = $str . '</tr>';	
	
	$str = $str . '<tr valign="top">';
	$str = $str . '<td><strong>Instructor</strong></td>';
	$str = $str . '<td>'.$teacher.'</td>';
	$str = $str . '<td><strong>From</strong></td>';
	$str = $str . '<td>'. $from. '</td>';
	$str = $str . '<td><strong>Week</strong></td>';
	$str = $str . '<td>'.$week .'</td>';
	$str = $str . '</tr>';	
	$str = $str . '</table>';
	$str = $str . '<br />';
	return $str;
}

//get the rc logo by URL
function sis_output_sis_logo()
{
	global $OUTPUT;
	$logo_url = new moodle_url('/local/rcyci/images/rcyci.jpg');
	return html_writer::empty_tag('img', array('src' => $logo_url, 'alt' => 'RCYCI Logo', 'width'=>'100', 'height' => '100'));;
}

function sis_output_print_timetable_header($title)
{
	global $OUTPUT, $CFG;
	$semester = sis_output_format_semester($CFG->semester);
	$str = '';
	$str = $str . '<table width="100%" border="0" cellpadding="2">';
	$str = $str . '<tr valign="top">';
	$str = $str . '<td width="15%">'.sis_output_sis_logo().'</td>';
	$str = $str . '<td width="85%">';
	$str = $str . $OUTPUT->heading('ROYAL COMMISSION COLLEGES AND INSTITUTES', '3');
	$str = $str . $OUTPUT->heading($title . ' FOR SEMESTER '.$semester['semester'] . ' (' . $semester['year_full'] . '-' . $semester['year_n'] . ')', '4');
	$str = $str . '</td>';
	$str = $str . '</tr>';		
	$str = $str . '</table>';
	return $str;
}

//given a user, print the student generic header
function sis_output_student_header($user)
{
	global $CFG;

	$semester = sis_output_format_semester($CFG->semester);
	$str = '';
	$str = $str . sis_output_print_generic_header('Student Attendance Report');
	$str = $str . '<br />';
	$str = $str . '<table width="100%" border="0" cellpadding="5">';
	$str = $str . '<tr valign="top">';
	$str = $str . '<td width="15%"><strong>Student</strong></td>';
	$str = $str . '<td width="85%">'.$user->idnumber . ' - ' . $user->firstname . ' ' . $user->lastname . '</td>';
	$str = $str . '</tr>';			
	$str = $str . '</table>';
	return sis_ui_box($str, '', true);
}

//given a user, print the student attendance
function sis_output_student_attendance_header($user, $c, $total_hour, $total_absence, $total_excuse, $isPrinting)
{
	global $CFG;
	$semester = sis_output_format_semester($CFG->semester);

	$green = sis_get_config('attendance', 'green');
	$yellow = sis_get_config('attendance', 'yellow');
	$orange = sis_get_config('attendance', 'orange');
	$red = sis_get_config('attendance', 'red');

	if($c != null)
	{
		$course = sis_parse_course($c, true);
		$teachers = $course['teacher'];
		$teacher = '';
		foreach($teachers as $t)
		{
			if($teacher != '')
				$teacher = $teacher . ', ';
			$teacher = $teacher . $t->firstname;
		}
	}
	else
		$course = null;
	$percent = number_format(($total_absence/$total_hour) * 100, 2);

	$percent_excuse = sis_format_attendance_compute_percent($total_hour, $total_absence, $total_excuse);

	if($percent_excuse >= $red)
		$highlight = 'red.png';
	else if($percent_excuse >= $orange)
		$highlight = 'orange.png';
	else if($percent_excuse >= $yellow)
		$highlight = 'yellow.png';
	else if($percent_excuse >= $green)
		$highlight = 'green.png';
	else
		$highlight = 'nocolor.png';
		
	$color = html_writer::empty_tag('img', array('src' => 'images/' . $highlight, 'alt' => ''));		
	if($total_excuse > 0)
	{
		if($total_excuse == 1)
			$excuse = ' (' . $total_excuse . ' hour with excuse)<sup>#</sup>';
		else
			$excuse = ' (' . $total_excuse . ' hours with excuse)<sup>#</sup>';
	}
	else
		$excuse = '';
	$str = '';
	$str = $str . '<table width="100%" border="0" cellpadding="5">';
	if($isPrinting) //output for printing
	{
		$str = $str . '<tr valign="top">';
		$str = $str . '<td width="10%"><strong>Student</strong></td>';
		$str = $str . '<td width="30%">'.$user->idnumber . ' - ' . $user->firstname . ' ' . $user->lastname . '</td>';
		$str = $str . '<td width="10%"><strong>Course</strong></td>';
		$str = $str . '<td width="25%" colspan="2">'.$course['course_code'] . ' - ' . $course['fullname'] . '</td>';
		$str = $str . '</tr>';	
		
		$str = $str . '<tr valign="top">';
		$str = $str . '<td width="10%"><strong>Semester</strong></td>';
		$str = $str . '<td width="30%">'.$semester['semester'] . '(' . $semester['year_full'] . '-' . $semester['year_n'] .')' .'</td>';
		$str = $str . '<td width="10%"><strong>Teacher</strong></td>';
		$str = $str . '<td width="25%">'.$teacher . '</td>';
		$str = $str . '<td width="15%"><strong>Section</strong></td>';
		$str = $str . '<td width="10%">'.$course['section'] . ' (' . $course['class_type'] . ')' . '</td>';
		$str = $str . '</tr>';	
		
		$str = $str . '<tr valign="top">';
		$str = $str . '<td><strong>Total Hour</strong></td>';
		$str = $str . '<td>'.$total_hour . ' Hours' .'</td>';
		$str = $str . '<td><strong>Total Absence</strong></td>';
		$str = $str . '<td>'.$total_absence . ' Hours ' . $excuse . '</td>';
		$str = $str . '<td><strong>Percent Absence</strong></td>';
		$str = $str . '<td>'.$percent_excuse . '%&nbsp;&nbsp;&nbsp;' . $color . '</td>';
		$str = $str . '</tr>';	
	}
	else
	{
		$str = $str . '<tr valign="top">';
		$str = $str . '<td width="15%"><strong>Total Hour</strong></td>';
		$str = $str . '<td width="15%">'. $total_hour . ' Hours' . '</td>';
		$str = $str . '<td width="15%"><strong>Total Absence</strong></td>';
		$str = $str . '<td width="30%">'.$total_absence . ' Hours ' . $excuse . '</td>';
		$str = $str . '<td width="15%"><strong> Percent Absence</strong></td>';
		$str = $str . '<td width="10%">'.$percent_excuse . '%&nbsp;&nbsp;&nbsp;' . $color . '</td>';
		$str = $str . '</tr>';	
		
	}
	$str = $str . '</table>';
	return sis_ui_box($str, '', true);
}

function sis_output_print_generic_header($title)
{
	global $OUTPUT, $CFG;
	$semester = sis_output_format_semester($CFG->semester);
	$str = '';
	$str = $str . '<table width="100%" border="0" cellpadding="2">';
	$str = $str . '<tr valign="top">';
	$str = $str . '<td width="10%">'.sis_output_sis_logo().'</td>';
	$str = $str . '<td width="90%">';
	$str = $str . $OUTPUT->heading('ROYAL COMMISSION COLLEGES AND INSTITUTES', '4');
	$str = $str . $OUTPUT->heading($title, '5');
	$str = $str . $OUTPUT->heading('SEMESTER '.$semester['semester'] . ' (' . $semester['year_full'] . '-' . $semester['year_n'] . ')', '5');
	$str = $str . '</td>';
	$str = $str . '</tr>';		
	$str = $str . '</table>';
	return $str;
}

function sis_output_get_styles_pdf_print($fontSize=10)
{
	$html = '
	<style>
		h1 {
			color: navy;
			font-family: times;
			font-size: 24pt;
			text-decoration: underline;
		}
		p.first {
			color: black;
			font-family: helvetica;
			font-size: 12pt;
		}
		p.first span {
			color: #006600;
			font-style: italic;
		}
		p#second {
			color: black;
			font-family: times;
			font-size: 12pt;
			text-align: justify;
		}
		p#second > span {
			background-color: #FFFFAA;
		}
		table.first {
			color: black;
			font-family: helvetica;
			font-size: 9pt;
			background-color: white;
		}
		table.second {
			color: black;
			font-family: helvetica;
			font-size: 9pt;
			border-left: none;
			border-right: none;
			border-top: none;
			border-bottom: none;
			background-color: white;
		}		
		td {
			background-color: white;
		}
		td.second {
		}
		td.third {
			border: none;
		}
		td.fourth {
		}
		div.test {
			color: #CC0000;
			background-color: #FFFFFF;
			font-family: helvetica;
			font-size: 10pt;
			border-style: solid solid solid solid;
			border-width: 2px 2px 2px 2px;
			border-color: black black black black;
			text-align: center;
		}
		.break { page-break-before: always; }		
		
		div.report_content {
			color: #000000;
			font-family: helvetica;
			font-size: '.$fontSize.'pt;
			border-style: solid solid solid solid;
			border-width: 1px 1px 1px 1px;
			border-color: black black black black;
			padding:3px 3px 3px 3px;
			margin:2px 2px 2px 2px;
			width:400px;
			height:15px;
		}

		div.report_content_small {
			color: #000000;
			font-family: helvetica;
			font-size: '.$fontSize.'pt;
			border-style: solid solid solid solid;
			border-width: 1px 1px 1px 1px;
			border-color: black black black black;
			padding:3px 3px 3px 3px;
			margin:2px 2px 2px 2px;
			width:50px;
			height:15px;
		}

		div.report_content_tall {
			color: #000000;
			font-family: helvetica;
			font-size: '.$fontSize.'pt;
			border-style: solid solid solid solid;
			border-width: 1px 1px 1px 1px;
			border-color: black black black black;
			padding:3px 3px 3px 3px;
			margin:2px 2px 2px 2px;
			width:400px;
			height:30px;
		}
		
	</style>
	';
return $html;
}