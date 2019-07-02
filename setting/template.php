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
require_once 'template_form.php';

$urlparams = array();
$PAGE->set_url('/local/rcyci/setting', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
if(!$id = optional_param('id', '', PARAM_INT)) // get the course id
	$id = 0;

$isAdmin = is_siteadmin();
if(!$isAdmin) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci_column2');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

//if we need to delete the category
if(isset($_POST['delete_id'])) //delete category
{
	$sql = 'delete from {rc_grade_categories} where id = ' . $_POST['delete_id'];
	$DB->execute($sql);
}

$baseurl = $PAGE->url;
$currenttab = 'template'; //change this according to tab
if ($tab_controls = rc_setting_tab_controls($baseurl, $currenttab)) 
{
    echo $OUTPUT->render($tab_controls);
}
echo $OUTPUT->box_start('rc_tabbox');
//content code starts here

$mform = new template_form();
if ($mform->is_cancelled()) 
{
//    redirect($CFG->wwwroot.'/index.php');
} 
else if ($data = $mform->get_data()) 
{	
  //In this case you process validated data. $mform->get_data() returns data posted in form.
  //first, check to make sure that the category is not in the list
	$sql = "select * from {rc_grade_categories} where semester = '0' and fullname = '$data->category' and category = '$data->group'";
	$record = $DB->get_records_sql($sql);
	if(!$record)
	{		
		$sql = "insert into {rc_grade_categories} (fullname, category, depth, semester, is_lab, is_final_exam) values('$data->category', '$data->group', 2, '0', '$data->is_lab', '$data->is_final')";
		$DB->execute($sql);
		rc_ui_alert('Category added successfully', 'Success', 'success', true);
	}
	else
	{
		rc_ui_alert('Category already exist in the group. Please enter another category', 'Error', 'error', true, false);
	}
}

$mform->display();

echo '<br />';
rc_setting_print_template();
//print a form to be used for delete
echo '
<form name="form1" method="post">
	<input type="hidden" name="delete_id" value="" />
</form>
';

//content code ends here
echo $OUTPUT->box_end();
$PAGE->requires->js('/local/rcyci/setting/setting.js');
echo $OUTPUT->footer();
