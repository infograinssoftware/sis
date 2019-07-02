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
 * This page is provided for compatability and redirects the user to the default grade report
 *
 * @package   core_grades
 * @copyright 2005 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once '../lib/rclib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once '../lib/rc_ui_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once '../lib/rc_ps_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once '../timetable/lib.php'; //timetable library
require_once 'lib.php'; //local library

require_login(); //always require login
$isAdmin = is_siteadmin();
if(!$isAdmin) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

//content code starts here
$role = trim($_GET['role']);
$subrole = trim($_GET['subrole']);
$action = $_GET['action'];

if($action == 1) //add user
{
	if(isset($_GET['emplid']) && $_GET['emplid'] != '')
	{
		$emplid = $_GET['emplid'];
		//first, make sure he is a valid moodle user
		if($u = $DB->get_record('user', array('idnumber' => $emplid)))
		{
			$role_value = $_GET['role_value'];
			//first, make sure the user not in the role
			if($role == 'position') //for position we must include the parameter
				$existing = rc_get_user_role($emplid, $role, $subrole, $role_value);
			else
				$existing = rc_get_user_role($emplid, $role, $subrole);
			if(!$existing)
			{
				$obj = new stdClass();
				$obj->emplid = $emplid;
				$obj->role = $role;
				$obj->subrole = $subrole;
				$obj->role_value = $role_value;
				$obj->added_by = $USER->username;
				$DB->insert_record('rc_role', $obj);
				rc_ui_alert('User added to the role', 'Note', 'success', true, true);
			}
			else
				rc_ui_alert('User already in the role', 'Alert', 'info', true, true);
		}
		else
			rc_ui_alert('Invalid user id', 'error', 'error', true, true);		
	}
//	$DB->delete_records('rc_user', array('emplid' => $emplid));
}
else if($action == 2)
{
	if(isset($_GET['emplid']))
	{
		$role_id = $_GET['emplid'];
		$DB->delete_records('rc_role', array('id' => $role_id));
	}
}
$complete = true;
echo rc_ui_box_start();
if($role != '' && $subrole != '')
{
	echo rc_user_role_add_form($role, $subrole);
	$users = rc_get_role_users($role, $subrole);
	$table = new html_table();
	$table->width = '100%';
	$table->attributes['class'] = 'custom-table-1';
	$table->head[] = 'No';
	$table->size[] = '5%';
	$table->align[] = 'center';
	$table->head[] = 'EMPLID';
	$table->size[] = '15%';
	$table->align[] = 'left';
	$table->head[] = 'Name';
	$table->size[] = '50%';
	$table->align[] = 'left';
	$table->head[] = 'Parameters';
	$table->size[] = '15%';
	$table->align[] = 'left';
	$table->head[] = 'Added By';
	$table->size[] = '10%';
	$table->align[] = 'left';
	$table->head[] = 'Action';
	$table->size[] = '5%';
	$table->align[] = 'center';
	$count = 1;
	foreach($users as $user)
	{
		$url = new moodle_url('/user/profile.php', array('id' => $user->id));
		$action_url = "javascript:delete_role('$user->id')";
		$data[] = html_writer::link($url, $count, array('title' => 'View Course', 'target' => '_blank'));
		$data[] = html_writer::link($url, $user->username, array('title' => 'View User Profile', 'target' => '_blank'));
		$data[] = html_writer::link($url, $user->firstname . ', ' . $user->lastname, array('title' => 'View User Profile', 'target' => '_blank'));
		$data[] = html_writer::link($url, $user->role_value, array('title' => 'View User Profile', 'target' => '_blank'));
		$data[] = $user->added_by;
		$data[] = html_writer::link($action_url, rc_ui_icon('minus-circle', '1', true), array('title' => 'Remove from role'));		
		$count++;
		$table->data[] = $data;
		unset($data);
	}
	echo html_writer::table($table);
	echo '<br ?>* For attendance unlock, the parameter is the cut-off week number, i.e. if the unlock is from week 3 onward, then the parameter will be 3. Leave blank for no limit.';
}
echo rc_ui_box_end();
