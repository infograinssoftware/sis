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
$PAGE->set_url('/local/rcyci/tools/external.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
	
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here
rc_ui_page_header('RCYCI Forms Library');

$emplid = $USER->idnumber;
$ps_user = rc_ps_get_user($emplid);
if($ps_user)
{
	$u = new stdClass();
	$u->emplid = $ps_user['EMPLID'];
	$u->type = $ps_user['TYPE'];
	$u->institute = $ps_user['INSTITUTE'];	
	if($u->type == 'teacher')
	{
		//if it is teacher, we need to get the gender
		$d = rc_ps_get_user_department($u->emplid);
		$u->gender = rc_ps_get_department_gender($d['ACAD_ORG']);
		$u->program = $d['ACAD_ORG'];
	}
	else //student
	{
		if($u->institute == 'YUC-F')
			$u->gender = 'F';
		else
			$u->gender = 'M';
		$u->program = $ps_user['PROGRAM'];
	}
}
else //could be admin or internal user. So default to staff
{
	$u = new stdClass();
	$u->emplid = $USER->idnumber;
	$u->type = 'teacher';
	$u->institute = 'RCYCI';	
	$u->gender = 'M';
	$u->program = '';
}

$option = 0;
if(isset($_GET['action']))
	$option = $_GET['action'];

if(isset($_POST['search']))
{
	$post_data = $_POST;
	rc_set_session('resource_search', $post_data);
}
else
{
	$post_data = rc_get_session('resource_search');
	if($post_data == '') //if session not defined
	{		
		$post_data = array();
		$post_data['search'] = '';
		$post_data['resource_type'] = '';
		$post_data['access_type'] = '';
		$post_data['access_context'] = '';
		$post_data['active'] = 1;
		$post_data['sort'] = 1;		
	}
}

$form = rc_tool_external_resource_search_form($post_data, true);

rc_ui_box('', $form);

$where = " where is_active = 1";
if($post_data['search'] != '')
{
	$where = $where . " and title like '%" . $post_data['search'] . "%'";
}

if($post_data['resource_type'] != '')
{
	$where = $where . " and resource_type = '" . $post_data['resource_type'] . "'";
}

//mandatory filter for access type
$where = $where . " and (access_type = 'all' or access_type = '$u->type')";

//mandatory filter for access context
//need to build the access context based on the data
if($u->gender == 'F') //female, always YUC-F
{
	$where = $where . " and (access_context = 'all' or access_context = 'YUC-F')";
}
else //male
{
	if($u->type == 'teacher')
	{
		$where = $where . " and access_context <> 'YUC-F'";
	}
	else //student
	{
		$where = $where . " and (access_context = 'all' or access_context = '$u->institute' or access_context = 'YIC_YUC-M')";
	}
}

$sql = "select * from {rc_external_resource} $where order by title";

$templates = $DB->get_records_sql($sql);

$count = 1;
$table = new html_table();
$table->attributes['class'] = 'table table-bordered table-striped';
$table->width = "100%";
$table->head[] = 'No';
$table->size[] = '5%';
$table->align[] = 'center';
$table->head[] = 'Title';
$table->size[] = '70%';
$table->align[] = 'left';
$table->head[] = 'Type';
$table->size[] = '5%';	
$table->align[] = 'left';
$table->head[] = 'Date Added';
$table->size[] = '15%';	
$table->align[] = 'center';
$table->head[] = 'Action';
$table->size[] = '5%';	
$table->align[] = 'center';
foreach($templates as $template)
{
	$preview_url = new moodle_url($template->link, array());
	
	$data[] = html_writer::link($preview_url, $count, array('title' => 'Open External Resource', 'target' => '_blank'));
	$data[] = html_writer::link($preview_url, $template->title, array('title' => 'Open External Resource', 'target' => '_blank'));
	$data[] = html_writer::link($preview_url, ucfirst($template->resource_type), array('title' => 'Open External Resource', 'target' => '_blank'));

	$data[] = html_writer::link($preview_url, date('d-M-Y, h:i', $template->added_date), array('title' => 'Open External Resource', 'target' => '_blank'));
	
	$data[] = html_writer::link($preview_url, rc_ui_icon('external-link', '1.2', true), array('title' => 'Open External Resource', 'target' => '_blank'));
	
	$table->data[] = $data;
	unset($data);				
	$count++;
}

echo html_writer::table($table);

//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/rcyci/tools/tools.js');
echo $OUTPUT->footer();