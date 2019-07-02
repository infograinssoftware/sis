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
$rc_user_type = rc_get_user_type();
$isAdmin = is_siteadmin();
if(!$isAdmin && $rc_user_type != 'teacher') //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by teacher or administrator.');

$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

//content code starts here
if(isset($_GET['code']))
	$code = $_GET['code'];
else
	$code = null;
if($code == null)
	rc_ui_alert('The room cannot be found', 'Error', 'error', true, false);
else
{
	//content code starts here
	if(isset($_GET['week']))
		$week = $_GET['week'];
	else
		$week = 0;
	if(isset($_GET['day']))
	{
		$day = $_GET['day'];
		$time = $_GET['time'];
		$period = $_GET['period'];
		$description = $_GET['description'];
		print_object($day . ' ' . $time . ' ' . $period . ' ' . $description);		
	}
	
	$start_time = 1;
	$print_url = new moodle_url('/local/rcyci/timetable/print_room.php', array('code' => $code));
	$printTxt = '<div class="pull-right">' . html_writer::link($print_url, rc_ui_icon('print', '', true), array('title' => 'Print Room Timetable', 'target' => '_blank')) . '</div>';
	$title = 'Room : ' . $code;
	$title = $title . $printTxt;
	echo $OUTPUT->heading($title);
	//reservation
	$str = '<div class="links_black">Room: <?php echo $room; ?>&nbsp;&nbsp;&nbsp;<a href="javascript:reserve_room()">(Make Reservation)</a><br />&nbsp;</div>
	<div id="room-form" style="display:none;">
	<form id="form2" name="form2" method="post" action="" onsubmit="return search_timetable()">
		<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="DarkBlueBorder">
			<tr>
				<td>
					<table width="100%" border="1" cellspacing="1" cellpadding="10">
						<tr class="lightBrown">
							<td valign="top">
								<p><b>Select Free Time for Room Reservation</b></p>
								<p>' . print_room_timetable_booking_form($start_time, $week) . '</p>
							</td>
						</tr>
					</table>
				</td>
		  </tr>
		</table>
	</form>
	<br />
	</div>';
//	echo $str; //temporary disable it
	
	echo $OUTPUT->box_start('rc_box');
	rc_room_timetable($code);
	echo $OUTPUT->box_end();
}
