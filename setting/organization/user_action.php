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
require_once '../lib/sis_lib.php'; //The main sis functions include. This will include the dblib. So no need to include anymore
require_once '../lib/sis_ui_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once 'lib.php'; //local library

require_login(); //always require login
$isAdmin = is_siteadmin();
if(!$isAdmin) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);
//$PAGE->set_pagelayout('sis');


//content code starts here
$emplid = trim($_GET['emplid']);
$name = trim($_GET['name']);
$type = $_GET['type'];
$action = $_GET['action'];

$complete = true;
echo sis_ui_box_start();
if(isset($_GET['emplid']))
{
	$where = '';
	if($emplid != '')
	{
		$where = "username like '%$emplid%'";
	}
	if($name != '')
	{
		if($where != '')
			$where = $where . ' OR ';
		$where = $where . " (firstname like '%$name%' OR lastname like '%$name%')";
	}
	
	if($where != '')
		$where = ' WHERE ' . $where;
		
	$sql = "select * from {user} $where ORDER BY username";
	$users = $DB->get_records_sql($sql);
	
	$table = new html_table();
	$table->width = '100%';
	$table->attributes['class'] = '';
	$table->head[] = 'No';
	$table->size[] = '5%';
	$table->align[] = 'center';
	$table->head[] = 'ID';
	$table->size[] = '15%';
	$table->align[] = 'left';
	$table->head[] = 'Name';
	$table->size[] = '75%';
	$table->align[] = 'left';
	$table->head[] = 'Action';
	$table->size[] = '5%';
	$table->align[] = 'center';
	$count = 1;
	foreach($users as $user)
	{
		$url = new moodle_url('/user/profile.php', array('id' => $user->id));
		$action_url = "javascript:reset_password('$user->username')";
		$data[] = html_writer::link($url, $count, array('title' => 'View Course', 'target' => '_blank'));
		$data[] = html_writer::link($url, $user->username, array('title' => 'View User Profile', 'target' => '_blank'));
		$data[] = html_writer::link($url, $user->firstname . ', ' . $user->lastname, array('title' => 'View User Profile', 'target' => '_blank'));
		if($user->user_password != '') //has password, allow reset
			$data[] = html_writer::link($action_url, rc_ui_icon('minus-circle', '1', true), array('title' => 'Reset RCYCI Password'));		
		else
			$data[] = '';
		$count++;
		$table->data[] = $data;
		unset($data);
		if($count > 100)
		{
			$complete = false;
			break;
		}
	}
	sis_ui_print_table($table);
	
	if(!$complete) //not all are shown
	{
		echo '<br />';
		sis_ui_alert('Not all users are shown as the search result returns too many records. Try to refine your search by providing proper name or emplid', 'info', 'Note', true);
	}
}
echo sis_ui_box_end();
