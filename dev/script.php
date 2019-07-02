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

$urlparams = array();
$PAGE->set_url('/local/tplus/dev/script.php', $urlparams);
//breadcrumb. Build an array of breadcrums (text => link)
$breadcrumbs[] = array('tplusadmin', null); //this tells the function that it is the link to tplus admin
$url = new moodle_url('script.php', array());
$breadcrumbs[] = array('Developer: SQL Query Execute', $url); //to organization page. We can recycle $PAGE->url as its the same
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

echo tp_ui_page_header('TPlus SQL Query Execute');

$sql = optional_param('script', '', PARAM_TEXT);
$debug_sql = optional_param('debug_sql', '', PARAM_TEXT);

$page = optional_param('page', '', PARAM_TEXT);

if($sql == '' && $page != '') //possibility that user click on pagination
{
	//try to get the data from session
	$sql = tp_get_session('dev_script_sql');
	$debug_sql = tp_get_session('dev_script_debug_sql');
}

if($debug_sql == '')
	$debug_sql = false;
else
	$debug_sql = true;

//remember the data in session for pagination
tp_set_session('dev_script_sql', $sql);
tp_set_session('dev_script_debug_sql', $debug_sql);

//setup the table options
$options = array(
	'sql' => $sql, //incase if we need to use sql
	'debug' => $debug_sql,
	'condition' => array(), //and sql condition
	'table_class' => '', //custom table class
	'responsive' => true,
	'condensed-table' => true,
	'hover-table' => true,
	'numbering' => false, //don't show numbering. Applicable when dumping
	'action' => false, //automatic add form and javascript for action edit and delete
	'sortable' => false, //enable clicking of heading to sort
	'detail_link' => false, //provide javascript to link to detail page
	'detail_field' => 'id', //provide javascript to link to detail page
	'search' => false, //allow search
	'perpage' => 30, //use large number to remove pagination
);

//output the table
$output = '';
$output .= '<form name="script" method="post">';
$output .= tp_ui_textarea('script', 120, 3, $sql);
$output .= tp_ui_newline(2);
$output .= tp_ui_submit_button('button');
$output .= tp_ui_space(2);
$output .= tp_ui_reset_button('button1');
$output .= tp_ui_space(3);
$output .= tp_ui_checkbox('debug_sql', 'yes', $debug_sql, 'Debug SQL');
$output .= '</form>';
$output .= tp_ui_hr();

echo $output;

if($sql != '')
	echo tp_ui_table('script', $options);
else
	echo tp_ui_alert('Enter the SQL script and click submit', 'info', false);

echo tp_ui_contentbox_end();
//content code ends here
//load the custom javascripts
$PAGE->requires->js('/local/tplus/dev/script.js'); //local javascripts
$PAGE->requires->js('/local/tplus/script.js'); //tplus global javascript
//standard moodle footer
echo $OUTPUT->footer();
