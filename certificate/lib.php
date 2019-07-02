<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate module internal API,
 * this is in separate file to reduce memory use on non-certificate pages.
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

<<<<<<< HEAD
function rc_certificate_search_form($data)
{
	$certType = rc_certificate_get_type();
=======
function sis_certificate_search_form($data)
{
	$certType = sis_certificate_get_type();
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$certList[''] = 'All';
	foreach($certType as $c)
	{
		$certList[$c->certificate_type] = $c->certificate_type;
	}
	
	$activeList = array(
		'' => 'All',
		'1' => 'Active',
		'2' => 'Inactive',
	);
	
	if($data['mine'] == 1)
		$mine_check = 'checked';
	else
		$mine_check = '';
	$str = '<form id="form1" name="form1" method="post" onsubmit="return certificate_search()" action="">';
<<<<<<< HEAD
	$str = $str . 'Search : ' . rc_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . 'Certificate Type : ' . rc_ui_select('type', $certList, $data['type'], 'certificate_search()', '');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . 'Active Status : ' . rc_ui_select('active', $activeList, $data['active'], 'certificate_search()', '');
=======
	$str = $str . 'Search : ' . sis_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . 'Certificate Type : ' . sis_ui_select('type', $certList, $data['type'], 'certificate_search()', '');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . 'Active Status : ' . sis_ui_select('active', $activeList, $data['active'], 'certificate_search()', '');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<input type="checkbox" name="mine" value="1" onchange="certificate_search()" '.$mine_check.'> My Certificates';
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<span class="pull-right"><input type="button" name="button3" id="button3" value="Refresh" onclick="certificate_search()"/></span>';
<<<<<<< HEAD
	$str = $str . rc_ui_hidden('sort', 1);
=======
	$str = $str . sis_ui_hidden('sort', 1);
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$str = $str . '</form>';
	return $str;
}

<<<<<<< HEAD
function rc_certificate_get_type()
{
	global $DB;
	$sql = "select distinct certificate_type from {rc_certificate} order by certificate_type";
=======
function sis_certificate_get_type()
{
	global $DB;
	$sql = "select distinct certificate_type from {si_certificate} order by certificate_type";
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$rec = $DB->get_records_sql($sql);
	return $rec;
}

<<<<<<< HEAD
function rc_certificate_user_search_form($data)
{
	$str = '<form id="form1" name="form1" method="post" onsubmit="return certificate_search()" action="">';
	$str = $str . 'Search : ' . rc_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
=======
function sis_certificate_user_search_form($data)
{
	$str = '<form id="form1" name="form1" method="post" onsubmit="return certificate_search()" action="">';
	$str = $str . 'Search : ' . sis_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button3" id="button3" value="Refresh" onclick="certificate_search()"/>';
	$str = $str . '<span class="pull-right">';
	$str = $str . '<input type="button" name="button4" id="button4" value="Generate Serial No" onclick="generate_serial()"/>';
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button4" id="button4" value="Delete All Recepients" onclick="delete_all_recepient()"/>';
	$str = $str . '</span>';
<<<<<<< HEAD
	$str = $str . rc_ui_hidden('delete_all', 1);
	$str = $str . rc_ui_hidden('sort', 1);
=======
	$str = $str . sis_ui_hidden('delete_all', 1);
	$str = $str . sis_ui_hidden('sort', 1);
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$str = $str . '</form>';
	return $str;
}

<<<<<<< HEAD
function rc_certificate_student_search_form($data)
{
	$str = '<form id="form1" name="form1" method="post" onsubmit="return certificate_student_search()" action="">';
	$str = $str . 'Search : ' . rc_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button3" id="button3" value="Refresh" onclick="certificate_search()"/>';
	$str = $str . rc_ui_hidden('sort', 1);
=======
function sis_certificate_student_search_form($data)
{
	$str = '<form id="form1" name="form1" method="post" onsubmit="return certificate_student_search()" action="">';
	$str = $str . 'Search : ' . sis_ui_input('search', '20', $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<input type="button" name="button3" id="button3" value="Refresh" onclick="certificate_search()"/>';
	$str = $str . sis_ui_hidden('sort', 1);
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$str = $str . '</form>';
	return $str;
}

<<<<<<< HEAD
function rc_certificate_serial_no($str)
=======
function sis_certificate_serial_no($str)
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
{
	if($str == '')
		return '-';
	else
		return '#' . str_pad($str, 5, "0", STR_PAD_LEFT);
}

