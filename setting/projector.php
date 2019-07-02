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
require_once '../lib/rc_projector.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once 'lib.php'; //local library

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
//	$sql = 'delete from {rc_grade_categories} where id = ' . $_POST['delete_id'];
//	$DB->execute($sql);
}

$baseurl = $PAGE->url;
$currenttab = 'projector'; //change this according to tab
if ($tab_controls = rc_setting_tab_controls($baseurl, $currenttab)) 
{
    echo $OUTPUT->render($tab_controls);
}
echo $OUTPUT->box_start('rc_tabbox');
//content code starts here

if(isset($_GET['action']))
	$action = $_GET['action'];
else
	$action = 0;
if(!$action)
	$action = 0;
if(isset($_GET['id']))
	$id = $_GET['id'];
if($action == 4) //delete record
{
	if(isset($id))
	{
		$DB->delete_records('smartclassroom', array('id' => $id));
	}
	$action = 0;
}

if(isset($_POST['button']))
{
	$room = $_POST['room'];
	$building = $_POST['building'];
	$floor = $_POST['floor'];
	$name = $_POST['name'];
	$ip_address = $_POST['ip_address'];
	$mac_address = $_POST['mac_address'];
	$status = $_POST['status'];
	$available = $_POST['available'];	
	$id = $_POST['id'];
	if(isset($id) && $id != '') //has id, update
	{
		$sql = "update m_smartclassroom set 
				room = '$room',
				building = '$building',
				floor = '$floor',
				name = '$name',
				ip_address = '$ip_address',
				mac_address = '$mac_address',
				status = '$status',
				available = '$available'
				where id = $id";
		$DB->execute($sql);
		rc_ui_alert('Projector updated', 'Note', 'success', true, true);
		$action = 0;
	}
	else //no id, means add new
	{
		$sql = "insert into m_smartclassroom (room, building, floor, name, ip_address, mac_address, status, available) values('$room', '$building', '$floor', '$name', '$ip_address', '$mac_address', '$status', '$available')";
		$DB->execute($sql);
		rc_ui_alert('Projector added', 'Note', 'info', true, true);
		$action = 0;
	}
}



rc_ui_box(rc_setting_projector_icon(), '');

if($action == 0)
	rc_setting_projector_list(false);
else if($action == 2) //add projector
	rc_setting_add_projector();
else if($action == 3) //edit projector
	rc_setting_add_projector($id);

//content code ends here
echo $OUTPUT->box_end();
$PAGE->requires->js('/local/rcyci/setting/setting.js');
echo $OUTPUT->footer();


function rc_setting_projector_icon()
{
	global $OUTPUT, $CFG, $USER;
	$url = new moodle_url('/local/rcyci/setting/projector.php', array());
	$schedule = html_writer::link($url, rc_ui_icon('list', '2', true) . '<br />Projector List', array('title' => 'Projector List'));
	
	$url = new moodle_url('/local/rcyci/tools/projector.php', array());
	$power = html_writer::link($url, rc_ui_icon('power-off', '2', true) . '<br />Turn ON/OFF', array('title' => 'Turn Projector ON/OFF'));

	$url = new moodle_url('projector.php', array('action' => 2));
	$add = html_writer::link($url, rc_ui_icon('plus-circle', '2', true) . '<br />Add Projector', array('title' => 'Add Projector'));

	$html = '
		<table width="100%" border="0" cellspacing="1" cellpadding="2">
		  <tr>
			<td align="center" valign="top" width="120">'.$schedule.'</td>
			<td align="center" valign="top" width="20">&nbsp;</td>
			<td align="center" valign="top" width="120">'.$power.'</td>
			<td align="center" valign="top" width="70%">&nbsp;</td>
			<td align="center" valign="top" width="120">'.$add.'</td>
		  </tr>
		</table>		
	';
	return $html;
}

function rc_setting_projector_list($showStatus)
{
	global $CFG, $DB;
	set_time_limit(180);

	$table = new html_table();
	$table->attributes['class'] = 'table table-bordered table-striped';
	$table->width = "100%";
	
	$table->head[] = 'No';
	$table->align[] = 'center';
	$table->size[] = '5%';
	
	$table->head[] = 'Room';
	$table->align[] = 'center';
	$table->size[] = '10%';
	
	$table->head[] = 'Building';
	$table->align[] = 'left';
	$table->size[] = '5%';				

	$table->head[] = 'Floor';
	$table->align[] = 'left';
	$table->size[] = '5%';				

	$table->head[] = 'Name';
	$table->align[] = 'left';
	$table->size[] = '15%';				

	$table->head[] = 'IP Address';
	$table->align[] = 'left';
	$table->size[] = '15%';				

	$table->head[] = 'MAC Address';
	$table->align[] = 'left';
	$table->size[] = '15%';				

	$table->head[] = 'Status';
	$table->align[] = 'left';
	$table->size[] = '5%';				

	$table->head[] = 'Available';
	$table->align[] = 'left';
	$table->size[] = '10%';				
	
	if($showStatus)
	{
		$table->head[] = 'On/Off';
		$table->align[] = 'left';
		$table->size[] = '10%';				
	}

	$table->head[] = 'Action';
	$table->align[] = 'center';
	$table->size[] = '5%';				

	$sql = "select * from {smartclassroom} order by building, floor, room";
	$rooms = $DB->get_records_sql($sql);
	$count = 1;
	foreach($rooms as $room)
	{
		$data[] = $count;			
		$data[] = $room->room;
		$data[] = $room->building;
		$data[] = $room->floor;
		$data[] = $room->name;
		$data[] = $room->ip_address;
		$data[] = $room->mac_address;
		$data[] = $room->status;
		$data[] = $room->available;
		if($showStatus)
		{
			//get the on/off status
			$status = rc_projector_check_projector_status($room->ip_address);
			if($status == '%1POWR=0')
				$status = '<img src="'.$CFG->wwwroot.'/yic/smartclassroom/images/off.png" width="55" height="22" border="0"/>';
			else if($status == '%1POWR=1')
				$status = '<img src="'.$CFG->wwwroot.'/yic/smartclassroom/images/on.png" width="55" height="22" border="0"/>';
			else if($status == '%1POWR=3')
				$status = '<img src="'.$CFG->wwwroot.'/yic/smartclassroom/images/wu.png" width="55" height="22" border="0"/>';
			$data[] = $status;
		}
		//action buttons
		$edit_url = "javascript:edit_record('$room->id')";
		$delete_url = "javascript:delete_record('$room->id')";
		$data[] = html_writer::link($edit_url, rc_ui_icon('pencil', '1.3', true), array('title' => 'Edit Projector Information')) . ' ' . 
				  html_writer::link($delete_url, rc_ui_icon('times-circle', '1.3', true), array('title' => 'Edit Projector Information'));		
		$table->data[] = $data;
		unset($data);
		$count++;
	}

	echo html_writer::table($table);
}		

//if projector id is blank, means add new, otherwise edit
function rc_setting_add_projector($projector_id='')
{
	global $DB;
	if($projector_id != '') //editing
	{
		$rec = $DB->get_record('smartclassroom', array('id' => $projector_id));
		if($rec)
		{
			$room = $rec->room;
			$building = $rec->building;
			$floor = $rec->floor;
			$name = $rec->name;
			$ip_address = $rec->ip_address;
			$mac_address = $rec->mac_address;
			$status = $rec->status;
			$available = $rec->available;
		}
		$isEditing = 3; //editing record
	}
	else
	{
		$room = '';
		$building = '';
		$floor = '';
		$name = '';
		$ip_address = '';
		$mac_address = '';
		$status = '';
		$available = '';
		$isEditing = 2; //add new record
	}
	$table = new html_table();
	$table->attributes['class'] = 'table table-bordered table-striped table-condensed';
	$table->align = array ('right', 'left');
	$table->size = array ('20%', '80%');
	$table->width = '80%';
	$data[] = 'Room:';
	$data[] = '<input type="text" name="room" id="room" value="'.$room.'" />';
	$table->data[] = $data;
	unset($data);
	$data[] = 'Building:';		
	$data[] = '<input type="text" name="building" id="building" value="'.$building.'" />';
	$table->data[] = $data;
	unset($data);
	$data[] = 'Floor:';		
	$data[] = '<input type="text" name="floor" id="floor" value="'.$floor.'" />';
	$table->data[] = $data;
	unset($data);
	$data[] = 'Name:';		
	$data[] = '<input type="text" name="name" id="name" value="'.$name.'" />';
	$table->data[] = $data;
	unset($data);
	$data[] = 'IP Address:';		
	$data[] = '<input type="text" name="ip_address" id="ip_address" value="'.$ip_address.'" />';
	$table->data[] = $data;
	unset($data);
	$data[] = 'MAC Address:';		
	$data[] = '<input type="text" name="mac_address" id="mac_address" value="'.$mac_address.'" />';
	$table->data[] = $data;
	unset($data);
	$data[] = 'Status:';		
	$selected = '';
	if($available == 'No')
		$selected = 'selected';
	$data[] = '<select name="status" id="status">
				<option value="Ok">OK</option>
				<option value="No">NO</option>
				</select>';
	$table->data[] = $data;
	unset($data);
	$data[] = 'Available:';
	$selected = '';
	if($available == 'NO')
		$selected = 'selected';
	$data[] = '<select name="available" id="available">
				<option value="YES">YES</option>
				<option value="NO" '.$selected.'>NO</option>
				</select>';
	$table->data[] = $data;
	unset($data);
	$data[] = '<input type="hidden" name="action" id="action" value="'.$isEditing.'" /><input type="hidden" name="id" id="id" value="'.$projector_id.'" />';		
	$data[] = '<input type="submit" name="button" id="button" value="Submit" />';
	$table->data[] = $data;
	
	$str = '<form id="sc_menu_form" name="sc_menu_form" method="post">';
//	$str = $str . print_table($table, true);
	$str = $str . html_writer::table($table);
	$str = $str . '</form>';	
	echo $str;
}
