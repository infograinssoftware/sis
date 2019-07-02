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
rc_ui_page_header('Student Certificates List');

$emplid = $USER->idnumber;

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
		$post_data['active'] = 1;
		$post_data['sort'] = 1;		
	}
}

$form = rc_certificate_student_search_form($post_data);

rc_ui_box('', $form);

$where = " where b.is_active = 1 and a.moodle_user_id = '$USER->id'";

if($post_data['search'] != '')
{
	if(is_numeric($post_data['search']))
		$where = $where . " and (b.id = '" . $post_data['search'] . "' or b.title like '%" . $post_data['search'] . "%')";
	else
		$where = $where . " and b.title like '%" . $post_data['search'] . "%'";
}

$sql = "select b.id, a.name, a.date_added, b.title from {rc_certificate_user} a inner join {rc_certificate} b on a.certificate_id = b.id $where order by a.date_added";

$templates = $DB->get_records_sql($sql);

if($templates)
{
	$table = new html_table();
	$table->attributes['class'] = 'table table-bordered table-striped';
	$table->width = "100%";
	$table->head[] = 'Certificate ID';
	$table->size[] = '10%';
	$table->align[] = 'center';
	$table->head[] = 'Certificate Title';
	$table->size[] = '65%';
	$table->align[] = 'left';
	$table->head[] = 'Certificate Date';
	$table->size[] = '15%';	
	$table->align[] = 'left';
	$table->head[] = 'Action';
	$table->size[] = '10%';	
	$table->align[] = 'center';
	foreach($templates as $template)
	{
		$data[] = $template->id;
		$data[] = $template->title;
		$data[] = date('d-M-Y, h:i', $template->date_added);

		$preview_url = new moodle_url('/local/rcyci/certificate/student_print.php?id=' . $template->id, array());
		
		$data[] = html_writer::link($preview_url, rc_ui_icon('print', '1.2', true), array('title' => 'Print Certificate', 'target' => '_blank')) . '&nbsp;';
		
		$table->data[] = $data;
		unset($data);				
	}

	echo html_writer::table($table);
}
else
{
	rc_ui_alert('You do not have any printable certificate in our record.', 'No Record Found', 'info', true);
}
//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/rcyci/certificate/certificate.js');
echo $OUTPUT->footer();