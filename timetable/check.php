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
$PAGE->set_url('/local/rcyci/timetable', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$rc_user_type = rc_get_user_type();

$roles = rc_get_user_all_role($USER->idnumber, 'course');
$hasAccess = rc_has_access(array('all', 'college', 'department'), $roles);

if(!$hasAccess && !$isAdmin)
	throw new moodle_exception('Access denied. This module is only accessible by course administrator.');

//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here
$class_nbr = $_GET['course'];
$student = $_GET['student'];
if($class_nbr !== false && $student !== false)
{
	//try to get the user from database
	$user = $DB->get_record('user', array('idnumber' => $student));
	if(!$user) //if you cannot find user, then create the dummy user
	{
		$user = new stdClass;
		$user->idnumber = $student;
	}
	echo $OUTPUT->heading('Simulated Timetable for ' . $student);
	echo $OUTPUT->box_start('rc_box');
	rc_timetable_merge($user, $class_nbr);
	echo $OUTPUT->box_end();
}
else
	echo 'wrong';

echo $OUTPUT->footer();
