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
require_once 'suspend_form.php'; //local library

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

$mform = new suspend_form();

$user_id = optional_param('id', '', PARAM_TEXT);
$username = optional_param('username', '', PARAM_TEXT);

$success = false;
if ($mform->is_cancelled()) 
{
	$cancelurl = new moodle_url('suspend.php', array());
	redirect($cancelurl);
} 
else if ($data = $mform->get_data()) 
{	
	//message will be saved in the description field for the admin
	$message =$data->message['text'];
	$user_id =$data->user_id;
	$username =$data->username;
	if($user_id != '') //for specific user
	{
		$adm = $DB->get_record('rc_suspend_message', array('emplid' => $username));
		if($adm) //existing
		{
			$adm->message = $message;
			$DB->update_record('rc_suspend_message', $adm);
		}
		else //new, create it
		{
			$adm = new stdClass;
			$adm->moodle_user_id = $user_id;
			$adm->emplid = $username;
			$adm->message = $message;
			$DB->insert_record('rc_suspend_message', $adm);
		}
		$success = true;
	}		
	else //universal message
	{
		$adm = $DB->get_record('user', array('username' => 'admin'));
		if($adm)
		{
			$adm->description = $message;
			$DB->update_record('user', $adm);
			$success = true;
		}
		else
			rc_ui_alert('Failed to save message', 'Note', 'error', true, false);
	}
}
if(!$success)
	$mform->display();
else
{
	rc_ui_alert('Message has been saved', 'Note', 'success', true, false);
	notice('', new moodle_url('suspend.php', array()));
}


echo $OUTPUT->box_end();
//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/rcyci/user/user.js');
echo $OUTPUT->footer();
