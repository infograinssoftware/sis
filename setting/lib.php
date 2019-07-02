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
function rc_setting_tab_controls(moodle_url $currenturl, $currenttab) 
{
	global $CFG;
    $tabs = array();
    $url = new moodle_url($currenturl . '/index.php', array('tab' => 'global'));
    $tabs[] = new tabobject('global', $url, 'Global Settings');
	
    $url = new moodle_url($currenturl . '/index.php', array('tab' => 'module'));
    $tabs[] = new tabobject('module', $url, 'Module Settings');

    $url = new moodle_url($currenturl . '/index.php', array('tab' => 'attend'));
    $tabs[] = new tabobject('attend', $url, 'Attendance Settings');

    $url = new moodle_url($currenturl . '/projector.php', array('tab' => 'projector'));
    $tabs[] = new tabobject('projector', $url, 'Projectors');

    $url = new moodle_url($currenturl . '/template.php', array('tab' => 'template'));
    $tabs[] = new tabobject('template', $url, 'Grade Template');

    return new tabtree($tabs, $currenttab);
	
}

function rc_setting_table_row($title, $control)
{
	$str = '<tr>
			  	<td width="30%" valign="top" align="right"><b>'.$title.': </b></td>
				<td width="70%" valign="top">' . $control . '</td>
			</tr>
	';
	return $str;
	
}
/////////////////////////////////////////////////////
/////////////global settings/////////////////////////
function rc_setting_global()
{
	global $DB;
	//first get the generic template course
	
	$yesno = array('yes' => 'Yes', 'no' => 'No');
	
	$str = '<form id="form1" name="form1" method="post" onsubmit="return validateGlobalSetting()" action="">';

	//settings for examination
	$exam_type = array('1' => 'Mid Term Examination', '2' => 'Final Examination');

	$selected = rc_get_config('exam', 'show_exam_schedule');
	$show_exam_schedule = rc_ui_radio('show_exam_schedule', $yesno, $selected);

	$value = rc_get_config('exam', 'exam_schedule_message');
	$exam_schedule_message = rc_ui_input('exam_schedule_message', 80, $value);
	
	$selected = rc_get_config('exam', 'show_exam_venue');
	$show_exam_venue = rc_ui_radio('show_exam_venue', $yesno, $selected);
	
	$selected = rc_get_config('exam', 'exam_type');
	$exam_type = rc_ui_radio('exam_type', $exam_type, $selected);

	$selected = rc_get_config('exam', 'allow_printing_envelope');
	$allow_printing_envelope = rc_ui_radio('allow_printing_envelope', $yesno, $selected);

	$selected = rc_get_config('exam', 'show_lecturer_exam_schedule');
	$show_lecturer_exam_schedule = rc_ui_radio('show_lecturer_exam_schedule', $yesno, $selected);

	$selected = rc_get_config('exam', 'show_proctor_schedule');
	$show_proctor_schedule = rc_ui_radio('show_proctor_schedule', $yesno, $selected);

	$value = rc_get_config('exam', 'proctor_exam_message');
	$proctor_exam_message = rc_ui_input('proctor_exam_message', 80, $value);

	$selected = rc_get_config('exam', 'is_tentative');
	$is_tentative = rc_ui_radio('is_tentative', $yesno, $selected);

	$value = rc_get_config('exam', 'tentative_message');
	$tentative_message = rc_ui_input('tentative_message', 80, $value);
	
	$data = '<table width="100%" border="0" cellspacing="0" cellpadding="5">';
	$data = $data . rc_setting_table_row('Show Student Exam Schedule', $show_exam_schedule);
	$data = $data . rc_setting_table_row('Student Exam Message', $exam_schedule_message);
	$data = $data . rc_setting_table_row('Show Venue', $show_exam_venue);
	$data = $data . rc_setting_table_row('Examination Type', $exam_type);
	$data = $data . rc_setting_table_row('Allow Printing of Exam Envelop', $allow_printing_envelope);
	$data = $data . rc_setting_table_row('Show Lecturer Exam Schedule', $show_lecturer_exam_schedule);
	$data = $data . rc_setting_table_row('Show Proctor Schedule', $show_proctor_schedule);
	$data = $data . rc_setting_table_row('Proctor Exam Message', $proctor_exam_message);
	$data = $data . rc_setting_table_row('Is Tentative Schedule', $is_tentative);
	$data = $data . rc_setting_table_row('Tentative Message', $tentative_message);
	$data = $data . '</table>';	
	$str = $str . rc_ui_box($data, 'Examination Settings', true);

	//Name update settings
	$selected = rc_get_config('name_update', 'name_update_show');
	$name_update_show = rc_ui_radio('name_update_show', $yesno, $selected);

	$value = rc_get_config('name_update', 'name_update_associate');
	$name_update_associate = rc_ui_input('name_update_associate', 10, $value);

	$value = rc_get_config('name_update', 'name_update_bachelor');
	$name_update_bachelor = rc_ui_input('name_update_bachelor', 10, $value);

	$value = rc_get_config('name_update', 'name_update_message');
	$name_update_message = rc_ui_textarea('name_update_message', 3, 80, $value);
	
	$value = rc_get_config('name_update', 'name_update_link');
	$name_update_link = rc_ui_input('name_update_link', 80, $value);

	
	$data = '<table width="100%" border="0" cellspacing="0" cellpadding="5">';
	$data = $data . rc_setting_table_row('Show Message for Name Update', $name_update_show);	
	$data = $data . rc_setting_table_row('Associate Credit', $name_update_associate);	
	$data = $data . rc_setting_table_row('Bachelor Credit', $name_update_bachelor);	
	$data = $data . rc_setting_table_row('Message', $name_update_message);	
	$data = $data . rc_setting_table_row('URL', $name_update_link);	
	$data = $data . '</table>';	
	$str = $str . rc_ui_box($data, 'Name Update Settings', true);
	
	
	//settings for grade
	$selected = rc_get_config('grade', 'enable_rcyci_gradebook');
	$enable_rcyci_gradebook = rc_ui_radio('enable_rcyci_gradebook', $yesno, $selected);

	$selected = rc_get_config('grade', 'readonly_gradebook');
	$readonly_gradebook = rc_ui_radio('readonly_gradebook', $yesno, $selected);

	$selected = rc_get_config('grade', 'coursewide_gradebook');
	$coursewide_gradebook = rc_ui_radio('coursewide_gradebook', $yesno, $selected);

	$selected = rc_get_config('grade', 'fail_if_absence');
	$fail_if_absence = rc_ui_radio('fail_if_absence', $yesno, $selected);
	
	$selected = rc_get_config('grade', 'fail_if_cheating');
	$fail_if_cheating = rc_ui_radio('fail_if_cheating', $yesno, $selected);
	
	$selected = rc_get_config('grade', 'clo_data_entry');
	$clo_data_entry = rc_ui_radio('clo_data_entry', $yesno, $selected);

	$value = rc_get_config('grade', 'pass_fail_courses');
	$pass_fail_courses = rc_ui_input('pass_fail_courses', 80, $value);
	
	$value = rc_get_config('grade', 'pass_fail_value');
	$pass_fail_value = rc_ui_input('pass_fail_value', 10, $value);
	
	$value = rc_get_config('grade', 'hod_unlock_date');
	$hod_unlock_date = rc_ui_input('hod_unlock_date', 10, $value);
	
	$data = '<table width="100%" border="0" cellspacing="0" cellpadding="5">';
	$data = $data . rc_setting_table_row('Enable RCYCI Gradebook', $enable_rcyci_gradebook);
	$data = $data . rc_setting_table_row('Read Only Gradebook', $readonly_gradebook);
	$data = $data . rc_setting_table_row('Coursewide Gradebook', $coursewide_gradebook);
	$data = $data . rc_setting_table_row('Fail if Absence', $fail_if_absence);
	$data = $data . rc_setting_table_row('Fail if Cheating', $fail_if_cheating);
	$data = $data . rc_setting_table_row('Pass/Fail Courses', $pass_fail_courses);
	$data = $data . rc_setting_table_row('Pass/Fail Value', $pass_fail_value . ' (Enter passing mark. Must be value)');
	$data = $data . rc_setting_table_row('HOD Unlock Cut-Off Date', $hod_unlock_date . ' (format: DD/MMM/YYY, eg 5/10/2017)<br />Enter cut-off date where HOD is allowed to unlock grade (blank for no cut-off date)');
	$data = $data . rc_setting_table_row('CLO Data Entry', $clo_data_entry);
	
	$data = $data . '</table>';	
	$str = $str . rc_ui_box($data, 'Grade Settings', true);
	///end of settings for grade

	//setting for survey
	$selected = rc_get_config('survey', 'rc_survey');
	$rc_survey = rc_ui_radio('rc_survey', $yesno, $selected); //setting as no will avoid unnecessary checking if survey is not required

	$selected = rc_get_config('survey', 'rc_survey_display');
	$rc_survey_display = rc_ui_radio('rc_survey_display', $yesno, $selected); //setting as no will avoid unnecessary checking if survey is not required

	$data = '<table width="100%" border="0" cellspacing="0" cellpadding="5">';
	$data = $data . rc_setting_table_row('Enable RC Survey', $rc_survey);	
	$data = $data . rc_setting_table_row('Display Survey Result (Faculty)', $rc_survey_display);	
	$data = $data . '</table>';	
	$str = $str . rc_ui_box($data, 'Surveys', true);
	
	$str = $str . '<input type="hidden" name="option" value="global"><div class="form-actions text-center"><input type="submit" name="button2" id="button2" value="Update" /></div>';
			
	$str = $str . '</form>';
	echo $str;
}

function rc_setting_save_global($data)
{
	rc_update_config('grade', 'enable_rcyci_gradebook', $data['enable_rcyci_gradebook']);
	rc_update_config('grade', 'readonly_gradebook', $data['readonly_gradebook']);
	rc_update_config('grade', 'coursewide_gradebook', $data['coursewide_gradebook']);
	rc_update_config('grade', 'fail_if_absence', $data['fail_if_absence']);
	rc_update_config('grade', 'fail_if_cheating', $data['fail_if_cheating']);
	rc_update_config('grade', 'clo_data_entry', $data['clo_data_entry']);
	rc_update_config('grade', 'pass_fail_courses', $data['pass_fail_courses']);
	rc_update_config('grade', 'pass_fail_value', $data['pass_fail_value']);
	rc_update_config('grade', 'hod_unlock_date', $data['hod_unlock_date']);
	
	rc_update_config('exam', 'show_exam_schedule', $data['show_exam_schedule']);
	rc_update_config('exam', 'exam_schedule_message', $data['exam_schedule_message']);
	rc_update_config('exam', 'show_exam_venue', $data['show_exam_venue']);
	rc_update_config('exam', 'exam_type', $data['exam_type']);
	rc_update_config('exam', 'allow_printing_envelope', $data['allow_printing_envelope']);
	rc_update_config('exam', 'show_proctor_schedule', $data['show_proctor_schedule']);
	rc_update_config('exam', 'proctor_exam_message', $data['proctor_exam_message']);
	rc_update_config('exam', 'show_lecturer_exam_schedule', $data['show_lecturer_exam_schedule']);
	rc_update_config('exam', 'is_tentative', $data['is_tentative']);
	rc_update_config('exam', 'tentative_message', $data['tentative_message']);
	
	rc_update_config('survey', 'rc_survey', $data['rc_survey']);
	rc_update_config('survey', 'rc_survey_display', $data['rc_survey_display']);
	
	rc_update_config('name_update', 'name_update_show', $data['name_update_show']);
	rc_update_config('name_update', 'name_update_associate', $data['name_update_associate']);
	rc_update_config('name_update', 'name_update_bachelor', $data['name_update_bachelor']);
	rc_update_config('name_update', 'name_update_message', $data['name_update_message']);
	rc_update_config('name_update', 'name_update_link', $data['name_update_link']);
	
}

/////////////////////////////////////////////////////
/////////////attendance setting//////////////////////
function rc_setting_save_attendance($data)
{
	global $CFG, $DB;
	rc_update_config('attendance', 'enable_attendance', $data['enable_attendance']);
	rc_update_config('attendance', 'lock_by_day', $data['lock_by_day']);
	rc_update_config('attendance', 'lock_previous', $data['lock_previous']);
	rc_update_config('attendance', 'lock_yti', $data['lock_yti']);
	rc_update_config('attendance', 'grace_period', $data['grace_period']);
	rc_update_config('attendance', 'academic_week', $data['academic_week']);
	rc_update_config('attendance', 'public_holiday', $data['public_holiday']);
	rc_update_config('attendance', 'dn_percentage', $data['dn_percentage']);
	rc_update_config('attendance', 'excuse_percentage', $data['excuse_percentage']);
	rc_update_config('attendance', 'update_ps', $data['update_ps']);
	rc_update_config('attendance', 'green', $data['green']);
	rc_update_config('attendance', 'yellow', $data['yellow']);
	rc_update_config('attendance', 'orange', $data['orange']);
	rc_update_config('attendance', 'red', $data['red']);
	//save the weeks
	$course = $DB->get_record('course', array('shortname' => 'TMP101'), '*', MUST_EXIST);
    $modinfo = get_fast_modinfo($course);
	$a = $modinfo->get_section_info_all();
	$totalweek = count($a); //total week starts with 0, but 0 is not use as week
	for($i = 1; $i <= $totalweek; $i++) //for attendance we start with 1 as 0 is for all sections
	{
		rc_update_config('attendance', 'reason_' . $i, $data['reason_' . $i]);
	}

}

function rc_setting_attendance()
{
	global $CFG, $DB;
	//first get the generic template course
	$course = rc_get_tmp_course();
    $modinfo = get_fast_modinfo($course);
	$a = $modinfo->get_section_info_all();
	$startdate = $course->startdate;
	$totalweek = count($a); //total week starts with 0, but 0 is not use as week
	
	$yesno = array('yes' => 'Yes', 'no' => 'No');

	$selected = rc_get_config('attendance', 'enable_attendance');
	$enable_attendance = rc_ui_radio('enable_attendance', $yesno, $selected);
	
	$selected = rc_get_config('attendance', 'lock_by_day');
	$lock_by_day = rc_ui_radio('lock_by_day', $yesno, $selected);
	
	$selected = rc_get_config('attendance', 'lock_previous');
	$lock_previous = rc_ui_radio('lock_previous', $yesno, $selected);
	
	$selected = rc_get_config('attendance', 'lock_yti');
	$lock_yti = rc_ui_radio('lock_yti', $yesno, $selected);

	$grace = rc_get_config('attendance', 'grace_period');
	$public_holiday = rc_get_config('attendance', 'public_holiday');
	$academic_week = rc_get_config('attendance', 'academic_week');
	$dn_percentage = rc_get_config('attendance', 'dn_percentage');
	$excuse_percentage = rc_get_config('attendance', 'excuse_percentage');
	
	$selected = rc_get_config('attendance', 'update_ps');
	$update_ps = rc_ui_radio('update_ps', $yesno, $selected);

	$green = rc_get_config('attendance', 'green');
	$yellow = rc_get_config('attendance', 'yellow');
	$orange = rc_get_config('attendance', 'orange');
	$red = rc_get_config('attendance', 'red');

	$str = '<form id="form1" name="form1" method="post" onsubmit="return validateAttendanceSetting()" action="">
		  <div align="right">
			<table width="100%" border="0" cellspacing="0" cellpadding="5">
			  <tr>
				<td width="100%" valign="top"><b>Enable Attendance Module: </b> ' . $enable_attendance . '
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Academic Weeks: </b><input name="academic_week" type="text" id="academic_week" size="5" maxlength="2" value="'.$academic_week.'" /> weeks
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Lock Attendance Entry by Day: </b> ' . $lock_by_day . '
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Lock Entry for Completed Week: </b>'.$lock_previous.' &nbsp;&nbsp;&nbsp;
				  Grace period <select name="grace_period" id="grace_period">';
	for($j = 0; $j < 30; $j++)
	{
		if($j == $grace)
			$g_select = "selected";
		else
			$g_select = "";
		$str = $str . '<option value="'.$j.'" '.$g_select.'>'.$j.'</option>';
	}
	$str = $str .'</select> day(s) 
				</td>					
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Lock YTI Courses for Completed Week: </b> '.$lock_yti.'
				</td>
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Public Holiday (Enter date in format dd-MMM-yyyy separated by comma): </b>
				  <input type="text" name="public_holiday" value="'.$public_holiday.'" size="40"/>
				</td>
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Real Time Update to Peoplesoft: </b> '.$update_ps.'
				</td>
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>DN Percentage (Must be number): </b>
				  <input type="text" name="dn_percentage" value="'.$dn_percentage.'" size="5"/> %
				</td>
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Max Excuse Percentage (Must be number): </b>
				  <input type="text" name="excuse_percentage" value="'.$excuse_percentage.'" size="5"/> %
				</td>
			  </tr>
			  <tr>
				<td width="100%" valign="top"><b>Exclude Week (Enter the Reason for exclusion, leave blank to include the week):&nbsp;&nbsp;&nbsp;
				</td>
			  </tr>
			  <tr>
				<td width="100%" valign="top">
				<table width="100%" border="0" cellspacing="0" cellpadding="2">
				  <tr>
					<td width="100"><b>Week</b></td>
					<td width="250"><b>Date</b></td>
					<td><b>Reason</b></td>
				  </tr>'; 
				$current_week = $startdate;						
				for($i = 1; $i <= $totalweek; $i++)
				{
					if($i < 10)
						$pad = '0';
					else
						$pad = '';
					$reason = rc_get_config('attendance', 'reason_' . $i);
					
					$end_week = strtotime(date("Y-m-d", $current_week) . " +1 week") - 1;
					$str = $str . '<tr>';
					$str = $str . '<td>Week '.$pad.$i.'</td>';
					$str = $str . '<td>'.date('d-M-Y', $current_week).' - '.date('d-M-Y', $end_week).'</td>';
					$str = $str . '<td><input type="text" name="reason_'.$i.'" id="reason_'.$i.'" size="50" value="'.$reason.'"/></td></tr>';
					$current_week = strtotime(date("Y-m-d", $current_week) . " +1 week");
				}
					
	$str = $str .'</table></td>
			  </tr>
			  <tr>
				<td valign="top">&nbsp;</td>
			  </tr>
			  <tr>
				<td valign="top">';
	$str = $str . 'Absence Highlight:&nbsp;&nbsp;&nbsp;';
	$str = $str . '<img src="'.$CFG->wwwroot . '/local/rcyci/images/green.png'.'" width="12" height="12"> <input name="green" type="text" id="green" size="3" maxlength="4" value="'.$green.'" />%&nbsp;&nbsp;&nbsp;';
	$str = $str . '<img src="'.$CFG->wwwroot . '/local/rcyci/images/yellow.png'.'" width="12" height="12"> <input name="yellow" type="text" id="yellow" size="3" maxlength="4" value="'.$yellow.'" />%&nbsp;&nbsp;&nbsp;';
	$str = $str . '<img src="'.$CFG->wwwroot . '/local/rcyci/images/orange.png'.'" width="12" height="12"> <input name="orange" type="text" id="orange" size="3" maxlength="4" value="'.$orange.'" />%&nbsp;&nbsp;&nbsp;';
	$str = $str . '<img src="'.$CFG->wwwroot . '/local/rcyci/images/red.png'.'" width="12" height="12"> <input name="red" type="text" id="red" size="3" maxlength="4" value="'.$red.'" />%';
	$str = $str . '</td>
			  </tr>
			  <tr>
			  	<td>				  
					<input type="hidden" name="option" value="attendance">
					<div class="form-actions text-center"><input type="submit" name="button2" id="button2" value="Update" /></div>
				</td>
			  </tr>
			</table>
		  </div>
	</form>';
	echo $str;
}

////GRADE TEMPLATE///////////////
function rc_setting_print_template()
{
	global $DB;
	$sql = "select * from {rc_grade_categories} where semester = '0' order by category, is_final_exam, fullname";
	$records = $DB->get_records_sql($sql);
	$table = new html_table();
	$table->attributes['class'] = 'custom-table-1';
	$table->width = "100%";
	$table->head[] = 'Group';
	$table->align[] = 'center';
	$table->size[] = '15%';	
	$table->head[] = 'Category';
	$table->align[] = 'left';
	$table->size[] = '10%';
	$table->head[] = 'Description';
	$table->align[] = 'left';
	$table->size[] = '35%';
	$table->head[] = 'Is Lab?';
	$table->align[] = 'center';
	$table->size[] = '15%';
	$table->head[] = 'Is Final Exam?';
	$table->align[] = 'center';
	$table->size[] = '15%';
	$table->head[] = 'Action';
	$table->align[] = 'center';
	$table->size[] = '10%';

	$groups = rc_grade_group();	 
	$prevGroup = '';
	foreach($records as $r)
	{
//		$actionLink = "<a href=\"javascript:delete_category('$r->id')\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" /></a>";
		$actionLink = "javascript:delete_category('$r->id')";
		if($prevGroup != $r->category)
			$g = $groups[$r->category];
		else
			$g = '';
		$data[] = $g;
		$data[] = $r->fullname;
		$data[] = $r->description;
		$data[] = $r->is_lab == 0 ? 'No' : 'Yes';
		$data[] = $r->is_final_exam == 0 ? 'No' : 'Yes';
//		$data[] = $actionLink;
		$data[] = html_writer::link($actionLink, rc_ui_icon('trash', '1', true), array('title' => 'Delete category'));
		$table->data[] = $data;
		unset($data); //unset data
		$prevGroup = $r->category;
	}
	echo html_writer::table($table);
}

