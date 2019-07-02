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

///////search user function//////////////////
function sis_user_search_form()
{
	$options = array(
		'staff' => get_string('staff', 'local_sis'),
		'student' => get_string('student', 'local_sis'),
		'all' => get_string('all'),
	);
	$str = '<form id="form1" name="form1" method="post" onsubmit="return search_user()" action="">
		' . get_string('id', 'local_sis') . ': <input name="emplid" type="text" id="emplid" size="15" maxlength="100" value="" onkeypress="handleKeyPress(event)" />&nbsp;
		' . get_string('name'). ': <input name="name" type="text" id="name" size="20" maxlength="100" value="" onkeypress="handleKeyPress(event)" />&nbsp;
		Type: ' . sis_ui_select('type', $options) . '&nbsp;
		<input type="button" name="button2" id="button2" value="Search" class="btn btn-primary" onclick="search_user()"/>
	</form>';
	return $str;
}


///////rc roles function//////////////////
function rc_user_role_search_form()
{
	if(isset($_GET['role']))
		$role = $_GET['role'];
	else
		$role = 'attendance';
	$roles = rc_get_roles();
	$str = '<form id="form1" name="form1" method="post" action="">';
	$str = $str . 'Role : ' . rc_ui_select('role', $roles, $role, 'refresh_role()');
	if($role == '')
	{
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . '<input type="button" name="button2" id="button2" value="Refresh" onclick="refresh_role()"/>';
	}
	else
	{
		$subroles = rc_get_subroles($role);
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . 'Permission : ' . rc_ui_select('subrole', $subroles, '', 'search_role()');
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . '<input type="button" name="button2" id="button2" value="Search" onclick="search_role()"/>';
	}
	$str = $str . '</form>';
	return $str;
}

//add a user to role
function rc_user_role_add_form($role, $subrole)
{
	global $CFG;
	$str = '<form id="form2" name="form2" method="post" action="">';
	$str = $str . 'Add User (EMPLID) : ' . rc_ui_input('user', 10, '', 'handleKeyPress2(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	if($role == 'position') //for these roles, use drop down
	{
		if($subrole == 'md' || $subrole == 'dmd') //list of colleges
			$list = rc_campus();
		else //for hod, list of departments
		{
			$list = rc_ps_get_department_list();
		}
		$str = $str . 'Parameter<sup>*</sup> : ' . rc_ui_select('role_value', $list);
	}
	else
		$str = $str . 'Parameter<sup>*</sup> : ' . rc_ui_input('role_value', 20, '', 'handleKeyPress2(event)');
	
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button4" id="button4" value="  Add  " onclick="add_role()"/>';
	$str = $str . rc_ui_hidden('role2', $role);
	$str = $str . rc_ui_hidden('subrole2', $subrole);
	$str = $str . '</form>';
	return $str;
}

///////suspend user function//////////////////
function rc_user_suspend_form()
{
	$url = new moodle_url('suspend_message.php', array());
	$suspend_message = html_writer::link($url, 'Suspend Message '.rc_ui_icon('commenting', '1.5', true).'</div>', array('title' => 'Suspend Message'));
	$str = '<form id="form1" name="form1" method="post" onsubmit="return search_suspend_user()" action="">
		Student ID: <input name="emplid" type="text" id="emplid" size="15" maxlength="100" value="" onkeypress="handleKeyPress3(event)" />&nbsp;
		<input type="hidden" name="action" value="2" />
		<input type="hidden" name="delete_id" value="" />
		<input type="button" name="button2" id="button2" value="Search" onclick="search_suspend_user()"/>
		<div class="pull-right">'.$suspend_message.'
	</form>';
	$str = $str . '<div id="ajax-content"></div>';
	return $str;
}

function rc_user_get_suspended_user()
{
	global $DB;
	$users = $DB->get_records('user', array('deleted' => 0, 'alternatename' => '1'));
	return $users;
}

