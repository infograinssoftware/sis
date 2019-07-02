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
require_once 'name_form.php';

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

$student_id = $_GET['student_id'];
if(!$student_id)
	throw new moodle_exception('Invalid parameter');

$cert = $DB->get_record('rc_certificate', array('id' => $id));

$student = $DB->get_record('rc_certificate_user', array('id' => $student_id));

//put before header so we can redirect
$return_url = new moodle_url('user.php', array('id' => $id));
$mform = new name_form($CFG->wwwroot.'/local/rcyci/certificate/update_name.php?id='.$id.'&student_id='.$student_id, array('student_id'=>$student_id));

$mform->set_data($student);

if ($mform->is_cancelled()) 
{
    redirect($return_url);
} 
else if ($data = $mform->get_data()) 
{	
	//need this part to escape blank value as database insert must be text
	if($data->custom_name == null)
		$data->custom_name = '';
	if($data->custom_name2 == null)
		$data->custom_name2 = '';
<<<<<<< HEAD
=======
	if($data->custom_award_date == null)
		$data->custom_award_date = '';
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7

	$DB->update_record('rc_certificate_user', $data);			
	redirect($return_url);
}
else
	echo $OUTPUT->header();
//content code starts here
rc_ui_page_header('Custom Student Name <br />Certificate : ' . $cert->title . ' (' . $cert->id . ')' . '<br />Student ID : '.$student->emplid . '<br />Name : ' . $student->name);

//$currenttab = 'add'; //change this according to tab
//include('tabs.php');

echo $OUTPUT->box_start('rc_box');

if($added)
{
	rc_ui_alert($success_list, 'Recepients Added Successfully :', 'success', true, true);
	if($fail_list != '')
		rc_ui_alert($fail_list, 'Failed to be added :', 'error', true, true);
}
$mform->display();

echo $OUTPUT->box_end();

echo $OUTPUT->footer();