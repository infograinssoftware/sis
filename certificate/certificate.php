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
<<<<<<< HEAD
require_once '../lib/rclib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once '../lib/rc_ui_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once '../lib/rc_ps_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
=======
require_once '../lib/sis_lib.php'; //
require_once '../lib/sis_ui_lib.php'; //
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
require_once 'lib.php'; //local library
require_once 'certificate_form.php';

$urlparams = array();
<<<<<<< HEAD
$PAGE->set_url('/local/rcyci/certificate/certificate.php', $urlparams);
=======
$PAGE->set_url('/local/sis/certificate/certificate.php', $urlparams);
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$isAdmin = is_siteadmin();
<<<<<<< HEAD
$roles = rc_get_user_all_role($USER->idnumber, 'certificate');
$hasAccess = rc_has_access(array('admin'), $roles);
if(!$isAdmin && !$hasAccess) //not admin and not attendance, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by certificate administrator.');
	
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci');
=======
if(!$isAdmin) //not admin and not attendance, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by certificate administrator.');

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

//put before header so we can redirect
$mform = new certificate_form();
if ($mform->is_cancelled()) 
{
    redirect('index.php');
} 
else if ($data = $mform->get_data()) 
{	
	$data->date_created = time();
	if($data->id == '') //create new
<<<<<<< HEAD
		$DB->insert_record('rc_certificate', $data);	
	else
		$DB->update_record('rc_certificate', $data);			
=======
		$DB->insert_record('si_certificate', $data);	
	else
		$DB->update_record('si_certificate', $data);			
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
    redirect('index.php');
}

echo $OUTPUT->header();
//content code starts here
<<<<<<< HEAD
rc_ui_page_header('RCYCI Certificate Administrator');
=======
sis_ui_page_title('SIS Certificate Administrator');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7

if(isset($_GET['id']))
{
	$id = $_GET['id'];
<<<<<<< HEAD
	$toform = $DB->get_record('rc_certificate', array('id' => $id));
=======
	$toform = $DB->get_record('si_certificate', array('id' => $id));
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	if($toform)
		$mform->set_data($toform);
}

$mform->display();

echo $OUTPUT->footer();