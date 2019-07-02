<<<<<<< HEAD
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

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; //The main sis functions include. This will include the dblib. So no need to include anymore
require_once '../../lib/sis_ui_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once 'lib.php'; //local library

require_login(); //always require login

//Role checking code here
$isAdmin = is_siteadmin();
if(!isAdmin) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$urlparams = array();
$PAGE->set_url('/local/sis/setting/organization/add_organization.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

$PAGE->set_pagelayout('sis');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

//set up breadcrumb
$PAGE->navbar->add('Add Organization', new moodle_url('index.php'));
//end of breadcrumb

echo $OUTPUT->header();


sis_ui_page_title('SIS Add organization');
//$currenttab = 'organization'; //change this according to tab
//include('tabs.php');

    echo $OUTPUT->box_start('sis_tabbox');
	 $id = $_REQUEST['id'];
	 if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_org')
		{

			 $orgcode = $_REQUEST['orgcode'];
			 $nameen = $_REQUEST['nameen'];
			 $status = $_REQUEST['status'];
			 $namear = $_REQUEST['namear'];
			 $orgtype = $_REQUEST['orgtype'];
			 $institute = $_REQUEST['institute'];
			 $campus = $_REQUEST['campus'];
			 $id_o = $_REQUEST['id_o'];
             $id = $_REQUEST['id'];
			if(isset($id_o) && $id_o != '') //has id, update
			{
				 'yes';
				 $sql = "update m_si_organization set 
						organization = '$orgcode',
						organization_name = '$nameen',
						organization_name_a = '$namear',
						organization_type = '$orgtype',
						institute = '$institute',
						campus = '$campus',
						eff_status = '$status'
						where id = $id_o";
				$DB->execute($sql);
				rc_ui_alert('Organization updated', 'Note', 'success', true, true);
				echo '1';
			}
			else //no id, means add new
			{
			echo	$sql = "insert into m_si_organization (organization, organization_name, organization_name_a, organization_type, institute, campus, eff_status) values('$orgcode', '$nameen', '$namear', '$orgtype', '$institute', '$campus', '$status')";
				$DB->execute($sql);
				rc_ui_alert('Organization added', 'Note', 'info', true, true);
				echo '0';
			}
		}

$form = sis_organization_add($id);
sis_ui_box('', $form);

echo('<div id="ajax-content"></div>');

echo $OUTPUT->box_end();

//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/sis/setting/organization/organization.js');
=======
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

require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; //The main sis functions include. This will include the dblib. So no need to include anymore
require_once '../../lib/sis_ui_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once 'lib.php'; //local library

require_login(); //always require login

//Role checking code here
$isAdmin = is_siteadmin();
if(!isAdmin) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$urlparams = array();
$PAGE->set_url('/local/sis/setting/organization/add_organization.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

$PAGE->set_pagelayout('sis');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

//set up breadcrumb
$PAGE->navbar->add('Add Organization', new moodle_url('index.php'));
//end of breadcrumb

echo $OUTPUT->header();

sis_ui_page_title('SIS Add organization');
//$currenttab = 'organization'; //change this according to tab
//include('tabs.php');

    echo $OUTPUT->box_start('sis_tabbox');
	 $id = $_REQUEST['id'];
	 if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_org')
		{

			 $orgcode = $_REQUEST['orgcode'];
			 $nameen = $_REQUEST['nameen'];
			 $status = $_REQUEST['status'];
			 $namear = $_REQUEST['namear'];
			 $orgtype = $_REQUEST['orgtype'];
			 $institute = $_REQUEST['institute'];
			 $campus = $_REQUEST['campus'];
			 $id_o = $_REQUEST['id_o'];
             $id = $_REQUEST['id'];
			if(isset($id_o) && $id_o != '') //has id, update
			{
				 'yes';
				 $sql = "update m_si_organization set 
						organization = '$orgcode',
						organization_name = '$nameen',
						organization_name_a = '$namear',
						organization_type = '$orgtype',
						institute = '$institute',
						campus = '$campus',
						eff_status = '$status'
						where id = $id_o";
				$DB->execute($sql);
				rc_ui_alert('Organization updated', 'Note', 'success', true, true);
				echo '1';
			}
			else //no id, means add new
			{
			echo	$sql = "insert into m_si_organization (organization, organization_name, organization_name_a, organization_type, institute, campus, eff_status) values('$orgcode', '$nameen', '$namear', '$orgtype', '$institute', '$campus', '$status')";
				$DB->execute($sql);
				rc_ui_alert('Organization added', 'Note', 'info', true, true);
				echo '0';
			}
		}

$form = sis_organization_add($id);
sis_ui_box('', $form);

echo('<div id="ajax-content"></div>');

echo $OUTPUT->box_end();

//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/sis/setting/organization/organization.js');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
echo $OUTPUT->footer();