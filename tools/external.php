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
$isAdmin = is_siteadmin();
$roles = rc_get_user_all_role($USER->idnumber, 'external_resource');
$hasAccess = rc_has_access(array('admin'), $roles);
if(!$isAdmin && !$hasAccess) //not admin and not attendance, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by External Resource administrator.');
	
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here
rc_ui_page_header('RCYCI External Resources Administrator');

$option = 0;
if(isset($_GET['action']))
	$option = $_GET['action'];

if(isset($_POST['search']))
{
	$post_data = $_POST;
	rc_set_session('external_resource_search', $post_data);
}
else
{
	$post_data = rc_get_session('external_resource_search');
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



if($option == 2)
{
	$id = $_GET['id'];
	//next delete the external resource
	$DB->delete_records('rc_external_resource', array('id' => $id));
	$option = 0;
}

$add_url = new moodle_url('/local/rcyci/tools/external_resource.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, rc_ui_icon('plus-circle', '1.2', true) . ' Add New Resource', array('title' => 'Add New Resource')) . '</div>';

$form = rc_tool_external_resource_search_form($post_data);

rc_ui_box('', $form);

$where = '';
if($post_data['search'] != '')
{
	if(is_numeric($post_data['search']))
		$where = "where (id = '" . $post_data['search'] . "' or title like '%" . $post_data['search'] . "%' or link like '%" . $_POST['search'] . "%')";
	else
		$where = "where (title like '%" . $post_data['search'] . "%' or link like '%" . $_POST['search'] . "%')";
}

if($post_data['resource_type'] != '')
{
	if($where == '')
		$where = "where ";
	else
		$where = $where . " and ";
	$where = $where . "resource_type = '" . $post_data['resource_type'] . "'";
}

if($post_data['access_type'] != '')
{
	if($where == '')
		$where = "where ";
	else
		$where = $where . " and ";
	$where = $where . "access_type = '" . $post_data['access_type'] . "'";
}

if($post_data['access_context'] != '')
{
	if($where == '')
		$where = "where ";
	else
		$where = $where . " and ";
	$where = $where . "access_context = '" . $post_data['access_context'] . "'";
}

if($post_data['active'] != '')
{
	if($where == '')
		$where = "where ";
	else
		$where = $where . " and ";
	$where = $where . "is_active = '" . $post_data['active'] . "'";
}

$sql = "select * from {rc_external_resource} $where order by id";

$templates = $DB->get_records_sql($sql);

$table = new html_table();
$table->attributes['class'] = 'table table-bordered table-striped';
$table->width = "100%";
$table->head[] = 'ID';
$table->size[] = '5%';
$table->align[] = 'center';
$table->head[] = 'Title';
$table->size[] = '25%';
$table->align[] = 'left';
$table->head[] = 'URL Link';
$table->size[] = '25%';	
$table->align[] = 'left';
$table->head[] = 'Type';
$table->size[] = '5%';	
$table->align[] = 'left';
$table->head[] = 'Access';
$table->size[] = '12%';	
$table->align[] = 'left';
$table->head[] = 'Active';
$table->size[] = '5%';	
$table->align[] = 'center';
$table->head[] = 'Date Added';
$table->size[] = '15%';	
$table->align[] = 'center';
$table->head[] = 'Action';
$table->size[] = '8%';	
$table->align[] = 'center';
foreach($templates as $template)
{
	$data[] = $template->id;
	$data[] = $template->title;
	$data[] = $template->link;
	$data[] = $template->resource_type;
	$data[] = $template->access_type . ' (' . $template->access_context . ')';
	$data[] = $template->is_active == 1 ? 'Yes' : 'No';
	$data[] = date('d-M-Y, h:i', $template->added_date);

	$delete_url = "javascript:delete_record('$template->id')";
	$update_url = new moodle_url('/local/rcyci/tools/external_resource.php', array('id'=>$template->id));		
	$preview_url = new moodle_url($template->link, array());
	
	$data[] = html_writer::link($delete_url, rc_ui_icon('trash', '1.2', true), array('title' => 'Delete External Resource')) . '&nbsp;' . 
			  html_writer::link($update_url, rc_ui_icon('pencil', '1.2', true), array('title' => 'Update External Resource')) . '&nbsp;' . 
			  html_writer::link($preview_url, rc_ui_icon('search', '1.2', true), array('title' => 'Preview External Resource', 'target' => '_blank'));
	$table->data[] = $data;
	unset($data);				
}

echo html_writer::table($table);

//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/rcyci/tools/tools.js');
echo $OUTPUT->footer();