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
require_once '../timetable/lib.php'; //timetable library
require_once 'lib.php'; //local library

require_login(); //always require login
$isAdmin = is_siteadmin();
$roles = rc_get_user_all_role($USER->idnumber, 'user');
$hasAccess = rc_has_access(array('suspend'), $roles);
if(!$isAdmin && !$hasAccess) //not admin and not attendance, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

//content code starts here
$emplid = trim($_GET['emplid']);

$student = $DB->get_record('user', array('idnumber' => $emplid));
if($student)
{
	$str = $emplid . ' - ' . $student->firstname . ' ' . $student->lastname;
	$suspend_message = rc_get_session('suspend_message');
	$msg = '<form id="form2" name="form2" method="post" action="">
	<br>Suspend Message: ' . rc_ui_input('suspend_message', '40', $suspend_message) . '<br />
	<input type="hidden" name="emplid" value="'.$emplid.'" />
	Add to suspend list?
	<input type="submit" name="button3" id="button3" value=" Yes "/>
	</form>
	';
	
	rc_ui_alert($msg, $str, 'warning', false, false);
}
else
	rc_ui_alert('Student not found', 'Error', 'error', false, false);
