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
require_once '../lib/sis_lib.php'; 
require_once '../lib/sis_ui_lib.php'; 
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
require_once 'lib.php'; //local library
require_once 'certificate_form.php';

$urlparams = array();
<<<<<<< HEAD
$PAGE->set_url('/local/rcyci/certificate/index.php', $urlparams);
=======
$PAGE->set_url('/local/sis/certificate/index.php', $urlparams);
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

echo $OUTPUT->header();
//content code starts here
<<<<<<< HEAD
rc_ui_page_header('RCYCI Certificate Administrator');
=======
sis_ui_page_title('Certificate Administrator');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7

$option = 0;
if(isset($_GET['action']))
	$option = $_GET['action'];

if(isset($_POST['search']))
{
	$post_data = $_POST;
	if(!isset($post_data['mine']))
		$post_data['mine'] = 2;		
<<<<<<< HEAD
	rc_set_session('certificate_search', $post_data);
}
else
{
	$post_data = rc_get_session('certificate_search');
=======
	sis_set_session('certificate_search', $post_data);
}
else
{
	$post_data = sis_get_session('certificate_search');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	if($post_data == '') //if session not defined
	{		
		$post_data = array();
		$post_data['search'] = '';
		$post_data['type'] = '';
		$post_data['active'] = '';
		$post_data['sort'] = 1;		
		$post_data['mine'] = 2;		
	}
}

if($option == 2)
{
	$id = $_GET['id'];
	//first delete any user assigned to the certificate
<<<<<<< HEAD
	$DB->delete_records('rc_certificate_user', array('certificate_id' => $id));
	//next delete the certificate
	$DB->delete_records('rc_certificate', array('id' => $id));
	$option = 0;
}

$add_url = new moodle_url('/local/rcyci/certificate/certificate.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, rc_ui_icon('plus-circle', '1.2', true) . ' Add New Certificate', array('title' => 'Add New Certificate')) . '</div>';

$form = rc_certificate_search_form($post_data);
rc_ui_box('', $form);
=======
	$DB->delete_records('si_certificate_user', array('certificate_id' => $id));
	//next delete the certificate
	$DB->delete_records('si_certificate', array('id' => $id));
	$option = 0;
}

$add_url = new moodle_url('/local/sis/certificate/certificate.php', array('action' => '1'));
echo '<div class="pull-right rc-attendance-teacher-print">' . html_writer::link($add_url, sis_ui_icon('plus-circle', '1', true) . ' Add New Certificate', array('title' => 'Add New Certificate')) . '</div>';

$form = sis_certificate_search_form($post_data);
sis_ui_box('', $form);
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7

$where = '';
if($post_data['search'] != '')
{
	if(is_numeric($post_data['search']))
		$where = "where (id = '" . $post_data['search'] . "' or title like '%" . $post_data['search'] . "%')";
	else
		$where = "where title like '%" . $post_data['search'] . "%'";
}
if($post_data['type'] != '')
{
	if($where == '')
		$where = "where ";
	else
		$where = $where . " and ";
	$where = $where . "certificate_type = '" . $post_data['type'] . "'";
}

if($post_data['active'] != '')
{
	if($where == '')
		$where = "where ";
	else
		$where = $where . " and ";
	$where = $where . "is_active = '" . $post_data['active'] . "'";
}

if($post_data['mine'] == '1') //only show the one created by himself
{
	if($where == '')
		$where = "where ";
	else
		$where = $where . " and ";
	$where = $where . "created_by = '" . $USER->idnumber . "'";
}

<<<<<<< HEAD
$sql = "select * from {rc_certificate} $where order by id";

$templates = $DB->get_records_sql($sql);
=======
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);

$sql = "select count(id) as total from {si_certificate} $where";
$t = $DB->get_record_sql($sql);
$totalrecord = $t->total;

$start = $page * $perpage;
if ($start > $totalrecord) {
    $page = 0;
    $start = 0;
}

$baseurl = new moodle_url('/local/sis/certificate/index.php', array());

$count = 1 + $start;

$params = array();
$params['count'] = $count;
$params['totalrecord'] = $totalrecord;
$params['page'] = $page;
$params['perpage'] = $perpage;
$params['baseurl'] = $baseurl;

$sql = "select * from {si_certificate} $where order by id desc";

$templates = $DB->get_records_sql($sql, array(), $start, $perpage);
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7

$table = new html_table();
$table->attributes['class'] = 'table table-bordered table-striped';
$table->width = "100%";
$table->head[] = 'Certificate ID';
$table->size[] = '10%';
$table->align[] = 'center';
$table->head[] = 'Certificate Title';
$table->size[] = '30%';
$table->align[] = 'left';
$table->head[] = 'Certificate Type';
$table->size[] = '20%';	
$table->align[] = 'left';
$table->head[] = 'Date Created';
$table->size[] = '15%';	
$table->align[] = 'left';
$table->head[] = 'Is Active';
$table->size[] = '10%';	
$table->align[] = 'center';
$table->head[] = 'Creator';
$table->size[] = '5%';	
$table->align[] = 'center';
$table->head[] = 'Action';
$table->size[] = '10%';	
$table->align[] = 'center';
foreach($templates as $template)
{
	$data[] = $template->id;
	$data[] = $template->title;
	$data[] = $template->certificate_type . ' (' . $template->border_style . ')';
	$data[] = date('d-M-Y, h:i', $template->date_created);
	$data[] = $template->is_active == 1 ? 'Yes' : 'No';
	$data[] = $template->created_by;

	$delete_url = "javascript:delete_record('$template->id')";
<<<<<<< HEAD
	$update_url = new moodle_url('/local/rcyci/certificate/certificate.php', array('id'=>$template->id));		
	$preview_url = new moodle_url('/local/rcyci/certificate/preview.php?id=' . $template->id . '&student_id=1', array());
	$user_url = new moodle_url('/local/rcyci/certificate/user.php?id=' . $template->id, array());
	
	$data[] = html_writer::link($delete_url, rc_ui_icon('trash', '1.2', true), array('title' => 'Delete Certificate')) . '&nbsp;' . 
			  html_writer::link($update_url, rc_ui_icon('pencil', '1.2', true), array('title' => 'Update Certificate')) . '&nbsp;' . 
			  html_writer::link($preview_url, rc_ui_icon('search', '1.2', true), array('title' => 'Preview Certificate', 'target' => '_blank')) . '&nbsp;' .
			  html_writer::link($user_url, rc_ui_icon('user', '1.2', true), array('title' => 'Manage Recepients'));
=======
	$update_url = new moodle_url('/local/sis/certificate/certificate.php', array('id'=>$template->id));		
	$preview_url = new moodle_url('/local/sis/certificate/preview.php?id=' . $template->id . '&student_id=1', array());
	$user_url = new moodle_url('/local/sis/certificate/user.php?id=' . $template->id, array());
	
	$data[] = html_writer::link($delete_url, sis_ui_icon('trash', '1', true), array('title' => 'Delete Certificate')) . '&nbsp;' . 
			  html_writer::link($update_url, sis_ui_icon('pencil', '1', true), array('title' => 'Update Certificate')) . '&nbsp;' . 
			  html_writer::link($preview_url, sis_ui_icon('search', '1', true), array('title' => 'Preview Certificate', 'target' => '_blank')) . '&nbsp;' .
			  html_writer::link($user_url, sis_ui_icon('user', '1', true), array('title' => 'Manage Recepients'));
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$table->data[] = $data;
	unset($data);				
}

echo html_writer::table($table);

<<<<<<< HEAD
//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/rcyci/certificate/certificate.js');
=======
echo '<br />' . $OUTPUT->paging_bar($totalrecord, $page, $perpage, $baseurl) . '(Total Records : '.$totalrecord.')';

//for now no need js yet
//$PAGE->requires->js('/local/sis/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/sis/certificate/certificate.js');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
echo $OUTPUT->footer();