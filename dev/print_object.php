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
require_once '../lib/rclib.php'; //The main rcyci functions include. This will include the dblib. So no need to include anymore
require_once '../lib/rc_ui_lib.php'; //The ui library
require_once 'lib.php'; //local library

$urlparams = array();
$url = new moodle_url('print_object.php', array());
$PAGE->set_url('/local/rcyci/dev/print_object.php', $urlparams);

$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login

//frontpage - for 2 columns with standard menu on the right
//tplus - 1 column
$PAGE->set_pagelayout('rcyci');
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
//$tab = optional_param('tab', 'organization', PARAM_TEXT);
//$sub = optional_param('sub', '', PARAM_TEXT);

//$currenttab = $tab; //change this according to tab
//include('../tabs.php');
//include('toolbars.php'); //include the toolbars

$action = optional_param('action', '', PARAM_TEXT);
if($action && $action = 'clear')
{
	rc_reset_debug();
}
$refreshBtn = rc_ui_button('Refresh', $url, 'info', '', '', true);

$clearUrl = new moodle_url('print_object.php', array('action' => 'clear'));
$clearBtn = rc_ui_button('Clear Output', $clearUrl, 'warning', '', '', true);

echo rc_ui_page_header('RCYCI Print Object Output' . '<span class="pull-right">' . $clearBtn . rc_ui_space() . $refreshBtn . '</span>');

$rec = $DB->get_records('rc_debug');
foreach($rec as $r)
{
	echo $r->output;
}

echo rc_ui_contentbox_end();

//content code ends here
//load the custom javascripts
$PAGE->requires->js('/local/rcyci/dev/script.js'); //local javascripts
//standard moodle footer
echo $OUTPUT->footer();
