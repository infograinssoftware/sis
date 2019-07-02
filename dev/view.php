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
require_once '../lib/tp_lib.php'; //The main tplus functions include. This will include the dblib. So no need to include anymore
require_once '../lib/tp_ui_lib.php'; //The ui library
require_once 'lib.php'; //local library

$urlparams = $_GET;
$PAGE->set_url('/local/tplus/dev/view.php', $urlparams);

$table = optional_param('id', '', PARAM_TEXT);

//breadcrumb. Build an array of breadcrums (text => link)
$breadcrumbs[] = array('tplusadmin', null); //this tells the function that it is the link to tplus admin
$url = new moodle_url('index.php', array());
$breadcrumbs[] = array('Developer', $url); //to organization page. We can recycle $PAGE->url as its the same
$url = new moodle_url('view.php', $_GET);
$breadcrumbs[] = array($table, $url); //to organization page. We can recycle $PAGE->url as its the same
tp_ui_breadcrumb($PAGE, $breadcrumbs);

$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login

//frontpage - for 2 columns with standard menu on the right
//tplus - 1 column
$PAGE->set_pagelayout('tplus');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

//authentication code here
$isAdmin = is_siteadmin();
if(!$isAdmin) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

//end of authentication code
echo $OUTPUT->header();
//include('toolbars.php'); //include the toolbars

//content code starts here
$tab = optional_param('tab', 'organization', PARAM_TEXT);
$sub = optional_param('sub', '', PARAM_TEXT);

$currenttab = $tab; //change this according to tab
//include('../tabs.php');
//include('toolbars.php'); //include the toolbars

echo tp_ui_tabbox_start();

//add a back button for toolbar
$params = tp_get_session('information_schema_return_params');
if($params == '')
	$params = array();
$return_url = new moodle_url('index.php', $params);
$btn_text = get_string('back', 'local_tplus');
$btn_title = get_string('back', 'local_tplus');
$btn_icon = tp_ui_icon('reply') . ' ';
$btn_class = 'btn btn-success pull-right';

$back_button = html_writer::link($return_url, $btn_icon . $btn_text, array(
		'class' => $btn_class,
		'aria-label' => $btn_text,
		'title' => $btn_title,
		));

echo tp_ui_page_header('TPlus Web Tables : ' . $table . $back_button);


$sort = optional_param('sort', '', PARAM_TEXT);
$order = optional_param('order', 'asc', PARAM_TEXT);
if($sort)
	$sort_text = ' order by ' . $sort . ' ' . $order;
else
	$sort_text = '';
$search = optional_param('search', '', PARAM_TEXT);	
$field = optional_param('field', '', PARAM_TEXT);

$where = '';
if($search != '')
{
	$where = " WHERE $field like '%$search%'";
}

$sql = "SELECT * FROM $table $where $sort_text";

//setup the table options
$options = array(
	'sql' => $sql, //incase if we need to use sql
	'condition' => array(), //and sql condition
	'table_class' => '', //custom table class
	'responsive' => true,
	'condensed-table' => true,
	'hover-table' => true,
	'numbering' => false, //don't show numbering. Applicable when dumping
	'action' => false, //automatic add form and javascript for action edit and delete
	'sortable' => true, //enable clicking of heading to sort
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //provide javascript to link to detail page
	'search' => true, //allow search
	'perpage' => 30, //use large number to remove pagination
);


//output the table
if($table)
	echo tp_ui_table($table, $options);
else
	echo tp_ui_alert('<strong>'.get_string('error', 'local_tplus').'</strong>: ' . 'No table found', 'danger', false);

echo tp_ui_contentbox_end();
//content code ends here
//load the custom javascripts
$PAGE->requires->js('/local/tplus/dev/script.js'); //local javascripts
$PAGE->requires->js('/local/tplus/script.js'); //tplus global javascript
//standard moodle footer
echo $OUTPUT->footer();
