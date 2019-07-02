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
require_once '../lib/rc_output_lib.php'; //The main RCYCI UI functions

require_once 'lib.php'; //local library

$urlparams = array();
$PAGE->set_url('/local/rcyci/timetable', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$rc_user_type = rc_get_user_type();
$isAdmin = is_siteadmin();
if(!$isAdmin && $rc_user_type != 'teacher') //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by teacher or administrator.');

//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci_print');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here

//content code starts here
if(isset($_GET['code']))
	$code = $_GET['code'];
else
	$code = null;
if($code == null)
	rc_ui_alert('The room cannot be found', 'Error', 'error', true, false);
else
{
	$title = 'Room : ' . $code;
	echo rc_output_print_timetable_header('ROOM SCHEDULE');
	echo $OUTPUT->heading($title, '3');
	rc_room_timetable($code, true);
}


//for now no need js yet
//$PAGE->requires->js('/local/rcyci/setting/timetable.js');
//content code ends here
$PAGE->requires->js('/local/rcyci/timetable/timetable.js');
echo $OUTPUT->footer();
