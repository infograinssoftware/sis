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
require_once 'lib.php'; //local library

$urlparams = array();
$PAGE->set_url('/local/rcyci/user/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login

$isAdmin = is_siteadmin();
$roles = rc_get_user_all_role($USER->idnumber, 'user');
$hasAccess = rc_has_access(array('suspend'), $roles);
if(!$isAdmin && !$hasAccess) //not admin and not attendance, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');
	
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci_column2');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here
rc_ui_page_header('User Management');
$currenttab = 'suspend'; //change this according to tab
include('tabs.php');
echo $OUTPUT->box_start('rc_tabbox');
if(isset($_POST['button3'])) //add to suspend list
{
	$emplid = $_POST['emplid'];
	$suspend_message = $_POST['suspend_message'];
	$student = $DB->get_record('user', array('idnumber' => $emplid));
	$student->alternatename = 1;
	$student->middlename = $USER->idnumber; //the person who did it
	$student->firstnamephonetic = time();
	$DB->update_record('user', $student);
	if($suspend_message != '') //has suspend message, create it
	{
		$adm = new stdClass;
		$adm->moodle_user_id = $student->id;
		$adm->emplid = $student->idnumber;
		$adm->message = $suspend_message;
		$DB->insert_record('rc_suspend_message', $adm);		
		rc_set_session('suspend_message', $suspend_message);
	}
}
if(isset($_POST['action']) && isset($_POST['delete_id'])) //remove suspend list
{
	$emplid = $_POST['delete_id'];
	$student = $DB->get_record('user', array('idnumber' => $emplid));
	$student->alternatename = '';
	$student->middlename = '';
	$student->firstnamephonetic = '';
	$DB->update_record('user', $student);
	//delete any message in there
	$DB->delete_records('rc_suspend_message', array('emplid' => $emplid));
}

$form = rc_user_suspend_form();
rc_ui_box('', $form);

$users = rc_user_get_suspended_user();

$table = new html_table();
$table->width = '100$';
$table->attributes['class'] = 'custom-table-1';
$table->head[] = 'No';
$table->size[] = '5%';
$table->align[] = 'center';
$table->head[] = 'EMPLID';
$table->size[] = '15%';
$table->align[] = 'left';
if($isAdmin) //admin, show the person that suspend it
{
	$table->head[] = 'Name';
	$table->size[] = '40%';
	$table->align[] = 'left';
	$table->head[] = 'Suspended By';
	$table->size[] = '15%';
	$table->align[] = 'center';	
	$table->head[] = 'Suspended On';
	$table->size[] = '15%';
	$table->align[] = 'center';	
}
else
{
	$table->head[] = 'Name';
	$table->size[] = '80%';
	$table->align[] = 'left';	
}
$table->head[] = 'Action';
$table->size[] = '5%';
$table->align[] = 'center';
$count = 1;
foreach($users as $user)
{
	$url = new moodle_url('/user/profile.php', array('id' => $user->id));
	$action_url = "javascript:remove_suspension('$user->username')";
	
	$action_url2 = new moodle_url('suspend_message.php', array('id' => $user->id, 'username' => $user->username));
	$commenting = html_writer::link($action_url2, rc_ui_icon('commenting', '1', true), array('title' => 'Add comment to individual student'));
	
	$data[] = html_writer::link($url, $count, array('title' => 'View Course', 'target' => '_blank'));
	$data[] = html_writer::link($url, $user->idnumber, array('title' => 'View User Profile', 'target' => '_blank'));
	$data[] = html_writer::link($url, $user->firstname . ', ' . $user->lastname, array('title' => 'View User Profile', 'target' => '_blank'));
	if($isAdmin)
	{		
		if($user->firstnamephonetic != '')
			$suspended_on = date('d-M-y, h:i', $user->firstnamephonetic);
		else
			$suspended_on = '';
		$data[] = html_writer::link($url, $user->middlename, array('title' => 'View User Profile', 'target' => '_blank'));
		$data[] = html_writer::link($url, $suspended_on, array('title' => 'View User Profile', 'target' => '_blank'));
	}
	$data[] = html_writer::link($action_url, rc_ui_icon('minus-circle', '1', true), array('title' => 'Remove from suspension')) . ' ' . 
			  $commenting;		
	$count++;
	$table->data[] = $data;
	unset($data);
}
echo html_writer::table($table);

echo $OUTPUT->box_end();
//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/rcyci/user/user.js');
echo $OUTPUT->footer();
