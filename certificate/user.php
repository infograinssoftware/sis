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
if(!$cert)
	throw new moodle_exception('Invalid parameter');


if(isset($_GET['action']))
{
	$option = $_GET['action'];
	if($option == 2) //delete
	{
		$emplid = $_GET['emplid'];
		if($emplid)
			$DB->delete_records('rc_certificate_user', array('certificate_id' => $id, 'emplid' => $emplid));		
	}
}

if(isset($_POST['delete_all']))
{
	if($_POST['delete_all'] == 2) //delete allow
	{
		$DB->delete_records('rc_certificate_user', array('certificate_id' => $id));		
	}
}

if(isset($_POST['delete_all']))
{
	if($_POST['delete_all'] == 3) //generate serial
	{
		//first, get the last serial number
		$sql = "select max(serial_no) as max_no from {rc_certificate_user} where certificate_id = '$id'";
		$max_no = $DB->get_field_sql($sql, array());
		if($max_no == '')
			$serial = 1;
		else
			$serial = $max_no + 1;
		$now = time();
		$sql = "select * from {rc_certificate_user} where certificate_id = '$id' order by emplid";
		$recs = $DB->get_records_sql($sql, array());
		foreach($recs as $rec)
		{
			if($rec->serial_no == '')
			{
				$rec->serial_no = $serial;
				$rec->serial_no_date = $now;
				$serial++;
				$DB->update_record(rc_certificate_user, $rec);
			}
		}
	}
}

if(isset($_POST['search']))
{
	$post_data = $_POST;
	rc_set_session('certificate_user_search', $post_data);
}
else
{
	$post_data = rc_get_session('certificate_user_search');
	if($post_data == '') //if session not defined
	{		
		$post_data = array();
		$post_data['search'] = '';
		$post_data['sort'] = 1;		
	}
}

echo $OUTPUT->header();
//content code starts here
rc_ui_page_header($cert->title . ' (' . $cert->id . ') : Manage Recepients');

$currenttab = 'recepient'; //change this according to tab
include('tabs.php');

$return_url = new moodle_url('/local/rcyci/certificate/index.php', array());
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($return_url, rc_ui_icon('reply', '1.2', true) . ' Return to Certificate', array('title' => 'Return to Certificate')) . '</div>';


$form = rc_certificate_user_search_form($post_data);

rc_ui_box('', $form);

echo $OUTPUT->box_start('rc_tabbox');

$where = "where certificate_id = '$id'";

if($post_data['search'] != '')
{
	$searchTxt = $post_data['search'];
	$where = $where . " and (
		emplid like '%$searchTxt%'
		or name like '%$searchTxt%'
		or custom_award_title like '%$searchTxt%'
		or custom_award_date like '%$searchTxt%'
		or custom_award_reason like '%$searchTxt%'
		or custom_award_detail like '%$searchTxt%'
		) ";
}

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);

$sql = "select count(emplid) as total from {rc_certificate_user} $where";
$t = $DB->get_record_sql($sql);
$totalrecord = $t->total;

$start = $page * $perpage;
if ($start > $totalrecord) {
    $page = 0;
    $start = 0;
}

$baseurl = new moodle_url('/local/rcyci/certificate/user.php', array('id' => $id));

$count = 1 + $start;

$params = array();
$params['count'] = $count;
$params['totalrecord'] = $totalrecord;
$params['page'] = $page;
$params['perpage'] = $perpage;
$params['baseurl'] = $baseurl;


$sql = "select * from {rc_certificate_user} $where order by emplid";

$recepients = $DB->get_records_sql($sql, array(), $start, $perpage);

$table = new html_table();
$table->attributes['class'] = 'table table-bordered table-striped';
$table->width = "100%";
$table->head[] = 'No';
$table->size[] = '5%';
$table->align[] = 'center';
$table->head[] = 'Student ID';
$table->size[] = '10%';
$table->align[] = 'center';
$table->head[] = 'Name';
$table->size[] = '20%';
$table->align[] = 'left';
<<<<<<< HEAD
$table->head[] = 'Custom Name 1';
$table->size[] = '15%';	
$table->align[] = 'left';
$table->head[] = 'Custom Name 2';
$table->size[] = '15%';	
=======
$table->head[] = 'Detail 1';
$table->size[] = '10%';
$table->align[] = 'center';
$table->head[] = 'Custom Name_1';
$table->size[] = '10%';	
$table->align[] = 'left';
$table->head[] = 'Custom Name_2';
$table->size[] = '10%';	
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
$table->align[] = 'left';
$table->head[] = 'Serial No';
$table->size[] = '10%';	
$table->align[] = 'left';
$table->head[] = 'Date Added';
$table->size[] = '15%';	
$table->align[] = 'left';
$table->head[] = 'Action';
$table->size[] = '10%';	
$table->align[] = 'center';
foreach($recepients as $r)
{
	$data[] = $count;
	$data[] = $r->emplid;
	$data[] = $r->name;
<<<<<<< HEAD
=======
	$data[] = $r->custom_award_date == '' ? '-' : $r->custom_award_date;
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$data[] = $r->custom_name == '' ? '-' : $r->custom_name;
	$data[] = $r->custom_name2 == '' ? '-' : $r->custom_name2;
	$data[] = rc_certificate_serial_no($r->serial_no);
	$data[] = date('d-M-Y, h:i', $r->date_added);
	$delete_url = "javascript:delete_recepient('$id', '$r->emplid', '$page')";
	$update_url = new moodle_url('/local/rcyci/certificate/update_name.php?id=' . $id . '&student_id=' . $r->id, array());
	$preview_url = new moodle_url('/local/rcyci/certificate/preview.php?id=' . $id . '&student_id=' . $r->emplid, array());
	
	$data[] = html_writer::link($delete_url, rc_ui_icon('trash', '1.2', true), array('title' => 'Delete Recepient')) . '&nbsp;' . 
			  html_writer::link($update_url, rc_ui_icon('pencil', '1.2', true), array('title' => 'Update Recepient Name')) . '&nbsp;' . 
			  html_writer::link($preview_url, rc_ui_icon('search', '1.2', true), array('title' => 'Preview Recepient Certificate', 'target' => '_blank'));
	$table->data[] = $data;
	unset($data);				
	$count++;
}

echo html_writer::table($table);

echo '<br />' . $OUTPUT->paging_bar($totalrecord, $page, $perpage, $baseurl) . '(Total Records : '.$totalrecord.')';

echo $OUTPUT->box_end();

$PAGE->requires->js('/local/rcyci/certificate/certificate.js');
echo $OUTPUT->footer();