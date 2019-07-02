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
require_once 'user_form.php';

$urlparams = array();
$PAGE->set_url('/local/rcyci/certificate/certificate.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$isAdmin = is_siteadmin();
$roles = rc_get_user_all_role($USER->idnumber, 'certificate');
$hasAccess = rc_has_access(array('admin'), $roles);
if(!$isAdmin && !$hasAccess) //not admin and not attendance, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by certificate administrator.');
	
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

$id = $_GET['id'];
if(!$id)
	throw new moodle_exception('Invalid parameter');

$cert = $DB->get_record('rc_certificate', array('id' => $id));

//put before header so we can redirect
$return_url = new moodle_url('user.php', array('id' => $id));
$mform = new user_form($CFG->wwwroot.'/local/rcyci/certificate/user_add.php?id='.$id, array('id'=>$id));
$added = false;
$success_list = '';
$fail_list = '';
if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
else if ($data = $mform->get_data()) 
{	
	//need this part to escape blank value as database insert must be text
	if($data->award_title == null)
		$data->award_title = '';
	if($data->award_date == null)
		$data->award_date = '';
	if($data->award_reason == null)
		$data->award_reason = '';
	if($data->award_detail == null)
		$data->award_detail = '';
	$users = $data->users;
	$users = str_replace(PHP_EOL, ',', $users);
	$users = str_replace(' ', '', $users);
	$arr = explode(',', $users);
	$now = time();
	foreach($arr as $user_id)
	{
		$user_id = trim($user_id);
		if($user_id != '')
		{
			//make sure the id is a valid student
			$stud = $DB->get_record('user', array('username' => $user_id));
			if($stud)
			{
				//make sure the student is not in the list
				if(!$DB->record_exists('rc_certificate_user', array('certificate_id' => $id, 'emplid' => $user_id)))
				{
					//add to list
					$name = $stud->firstname . ' ' . $stud->lastname;
					$name = str_replace("'", "", $name);
					if(strpos($name, '?') !== false) //if there is ? in name (illegal char), we have to remove name as it cause problem for sql
						$name = '';
					$sql = "insert into {rc_certificate_user} (certificate_id, moodle_user_id, emplid, name, custom_award_title, custom_award_date, custom_award_reason, custom_award_detail, allow_print, date_added, added_by)
					values('$id', '$stud->id', '$user_id', '$name', '$data->award_title', '$data->award_date', '$data->award_reason', '$data->award_detail', '1', '$now', '$USER->id')";
					/*
					$obj = new stdClass();
					$obj->certificate_id = $id;
					$obj->moodle_user_id = $stud->id;
					$obj->emplid = $user_id;
					$obj->name = $stud->firstname . ' ' . $stud->lastname;
					$obj->custom_award_title = $data->award_title;
					$obj->custom_award_date = $data->award_date;
					$obj->custom_award_reason = $data->award_reason;
					$obj->custom_award_detail = $data->award_detail;
					$obj->allow_print = 1;
					$obj->date_added = $now;
					$obj->added_by = $USER->id;
					$DB->insert_record('rc_certificate', $obj);	
					print_object($obj);
					*/
					$DB->execute($sql);
					$added = true;
					if($success_list != '')
						$success_list = $success_list . ', ';
					$success_list = $success_list . $user_id;
				}				
			}
			else
			{
				if($fail_list != '')
					$fail_list = $fail_list . ', ';
				$fail_list = $fail_list . $user_id;
			}
		}
	}
	
	echo $OUTPUT->header();
}
else
	echo $OUTPUT->header();
//content code starts here
rc_ui_page_header($cert->title . ' (' . $cert->id . ') : Manage Recepients');

$currenttab = 'add'; //change this according to tab
include('tabs.php');

echo $OUTPUT->box_start('rc_tabbox');

if($added)
{
	rc_ui_alert($success_list, 'Recepients Added Successfully :', 'success', true, true);
	if($fail_list != '')
		rc_ui_alert($fail_list, 'Failed to be added :', 'error', true, true);
}
$mform->display();

echo $OUTPUT->box_end();

echo $OUTPUT->footer();