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
 * This file contains main class for the course format Weeks
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once '../../../../config.php';
require_once '../../lib/sis_lib.php'; //The main sis functions include. This will include the dblib. So no need to include anymore
require_once '../../lib/sis_ui_lib.php';
<<<<<<< HEAD
=======
require_once '../../lib/sis_lookup_lib.php';
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
/**
 * Returns navigation controls (tabtree) to be displayed on cohort management pages
 *
 * @param context $context system or category context where cohorts controls are about to be displayed
 * @param moodle_url $currenturl
 * @return null|renderable
 */

///////search user function//////////////////

function sis_organization_index()
{
	global $DB;
	$sqli = "select * from {si_organization}";
	$orgrecord = $DB->get_records_sql($sqli);
    $array_organization[''];
	$str = '<form id="form1" name="form1" method="post" onsubmit="return search_user()" action="">
				Search: <input name="search" type="text" id="search" size="15" maxlength="100" value="" onkeyup="search_handler()" />&nbsp;
			</form>
			<table class="table table-bordered table-striped" id="org_table" width="100%">
			<thead>
			<tr>
			<th class="header" style="text-align:center;width:35%;" scope="col">Name</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Type</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Institute</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Campus</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Action</th>
			</tr>
			</thead>
			<tbody>';
			foreach($orgrecord as $org)
	        {
			$kkr = '<tr class="">
			<td class="cell" style="text-align:center;width:35%;">'.$org->organization_name.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$org->organization_type.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$org->institute.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$org->campus.'</td>
			<td class="cell lastcol" style="text-align:center;width:30%;">
			<a title="Delete Section" href="javascript:delete_organization('.$org->id.')">
			<i class="fa fa-trash fa-lg"></i></a>&nbsp;<a title="Update Section" href="add_organization.php?id='.$org->id.'"><i class="fa fa-pencil fa-lg"></i></a></td>
			</tr>'; $str = $str." ".$kkr;
			}
	      return $str . '</tbody></table>';
}

function sis_organization_add($id)
{
	
	global $DB;
	if (!empty($id))
	{
	$sqliu = "select * from {si_organization} where id =$id";
	$orgrecord = $DB->get_records_sql($sqliu);
    $array_organization[''];
	foreach($orgrecord as $org){
		$orgc = isset($org->organization) ? $org->organization : null;
		$name = isset($org->organization_name) ? $org->organization_name : null;
		$name_ar = isset($org->organization_name_a) ? $org->organization_name_a : null;
		$org_type = isset($org->organization_type) ? $org->organization_type : null;
    } }
	if(is_null($orgc)) {  $orgc = rand(); } else { $orgc = $orgc; }			
	$sqli = "select institute, institute_name from {si_institute}";
	$insrecord = $DB->get_records_sql($sqli);
    $array_institute[''];
	foreach($insrecord as $ins)
	{
	$array_institute[$ins->institute] = $ins->institute_name;
	}
	$sql_campus = "select campus, campus_name from {si_campus}";
	$campus_record = $DB->get_records_sql($sql_campus);
	$campus_array[''] ;
    foreach($campus_record as $campus){
	$campus_array[$campus->campus] = $campus->campus_name;
	}
<<<<<<< HEAD
	$options = array(
		'A' => 'Active',
		'I' => 'Inactive',
	);
=======
	$options = sis_lookup_active();
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
	$str = '
	<form id="form1" name="form1" method="post" onsubmit="return add_org()" action="">
           <div class="form-group row">
		<div class="col-md-3"><label>Org. Code:</label></div><div class="col-md-9"><input name="orgcode" type="text" id="orgcode" size="15" maxlength="100" value="'.$orgc.'" readonly onkeypress="handleKeyPress(event)" /> </div> </div>&nbsp;
		  <div class="form-group row">
		<div class="col-md-3"><label>Name EN:</div><div class="col-md-9"><input name="nameen" type="text" id="nameen" size="15" maxlength="100" value="'.$name.'" onkeypress="handleKeyPress(event)" /> <input name="id_o" type="hidden" id="id_o" size="15" maxlength="100" value="'.$id.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		  <div class="form-group row">
		<div class="col-md-3"><label>Name AR:</div><div class="col-md-9"><input name="namear" type="text" id="namear" size="15" maxlength="100" value="'.$name_ar.'" onkeypress="handleKeyPress(event)" /> </div> </div>&nbsp;
		  <div class="form-group row">
		<div class="col-md-3"><label>Org. Type:</div><div class="col-md-9"><input name="orgtype" type="text" id="orgtype" size="15" maxlength="100" value="'.$org_type.'" onkeypress="handleKeyPress(event)" /> </div> </div>&nbsp;
		  <div class="form-group row">
		<div class="col-md-3"><label>Institute:</div><div class="col-md-9">' . sis_ui_select('institute', $array_institute) . ' </div> </div>&nbsp;
		  <div class="form-group row">
		<div class="col-md-3"><label>Campus:</div><div class="col-md-9">' . sis_ui_select('campus', $campus_array) . ' </div> </div>&nbsp;
		  <div class="form-group row">
		<div class="col-md-3"><label>Status:</div><div class="col-md-9">' . sis_ui_select('status', $options) . ' </div> </div>&nbsp;
		 <div class="form-group row"><div class="col-md-3"><label></label></div><div class="col-md-9"><input type="button" name="button2" id="button2" value="Save Changes" class="btn btn-primary" onclick="add_org()"/>
		<input type="button" name="button3" id="button3" value="Cancel" class="btn btn-primary" onclick="cancelhref();"/></div> </div>&nbsp;
	</form>';
	return $str;
}
function sis_organization_section()
{
	global $DB;
	$sqli = "select * from {si_organizaiton_section}";
	$sectionrecord = $DB->get_records_sql($sqli);
    $array_section[''];
	$str = '
			<form id="form1" name="form1" method="post" onsubmit="return search_user()" action="">
				Search: <input name="search" type="text" id="search" size="15" maxlength="100" value="" onkeyup="search_handler()" />&nbsp;
			</form>
			<table class="table table-bordered table-striped" id="org_table" width="100%">
			<thead>
			<tr>
			<th class="header" style="text-align:center;width:35%;" scope="col">Organization ID</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Section</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Section Name</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Section Type</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Institute</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Campus</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Action</th>
			</tr>
			</thead>
			<tbody>';
			foreach($sectionrecord as $section)
	        {
			$sect = '<tr class="">
			<td class="cell" style="text-align:center;width:35%;">'.$section->organization_id.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$section->section.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$section->section_name.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$section->section_type.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$section->institute.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$section->campus.'</td>
			<td class="cell lastcol" style="text-align:center;width:30%;">
			<a title="Delete Section" href="javascript:delete_section('.$section->id.')">
			<i class="fa fa-trash fa-lg"></i></a>&nbsp;<a title="Update Section" href="add_section.php?id='.$section->id.'"><i class="fa fa-pencil fa-lg"></i></a></td>
			</tr>';$str = $str." ".$sect; 
			}
	      return $str . '</tbody></table>';
}

function sis_organization_section_add($id)
{
	global $DB;
	if (!empty($id))
	{ 
	$sqliu = "select * from {si_organizaiton_section} where id =$id";
	$sectionrecord = $DB->get_records_sql($sqliu);
    $array_section[''];
	foreach($sectionrecord as $section){
		$sectionn = isset($section->section) ? $section->section : null;
		$name = isset($section->section_name) ? $section->section_name : null;
		$name_ar = isset($section->section_name_a) ? $section->section_name_a : null;
		$sec_type = isset($section->section_type) ? $section->section_type : null;
    } }
	$sqli = "select institute, institute_name from {si_institute}";
	$insrecord = $DB->get_records_sql($sqli);
    $array_institute[''];
	foreach($insrecord as $ins){
	$array_institute[$ins->institute] = $ins->institute_name;
	}
	$sql_campus = "select campus, campus_name from {si_campus}";
	$campus_record = $DB->get_records_sql($sql_campus);
	$campus_array[''] ;
    foreach($campus_record as $campus){
	$campus_array[$campus->campus] = $campus->campus_name;
	}
	$sql_org = "select organization from {si_organization}";
	$org_record = $DB->get_records_sql($sql_org);
	$org_array[''] ;
    foreach($org_record as $org){
	$org_array[$org->organization] = $org->organization;
	}
	$options = array(
		'A' => 'Active',
		'I' => 'Inactive',
	);
	$str = '
	<form id="form1" name="form1" method="post" onsubmit="return add_section()" action="">
		<div class="form-group row">
		<div class="col-md-3"><label>Org. Id:</label></div><div class="col-md-9"> ' . sis_ui_select('orgid', $org_array) . '</div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Section:</label></div><div class="col-md-9"> <input name="section" type="text" id="section" size="15" maxlength="100" value="'.$sectionn.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Section Name EN:</label></div><div class="col-md-9"> <input name="sectionen" type="text" id="sectionen" size="15" maxlength="100" value="'.$name.'" onkeypress="handleKeyPress(event)" /><input name="id_s" type="hidden" id="id_s" size="15" maxlength="100" value="'.$id.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Section name AR:</label></div><div class="col-md-9"> <input name="sectionar" type="text" id="sectionar" size="15" maxlength="100" value="'.$name_ar.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Section Type:</label></div><div class="col-md-9"> <input name="sectionty" type="text" id="sectionty" size="15" maxlength="100" value="'.$sec_type.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Institute:</label></div><div class="col-md-9"> ' . sis_ui_select('institute', $array_institute) . '</div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Campus:</label></div><div class="col-md-9"> ' . sis_ui_select('campus', $campus_array) . '</div> </div>&nbsp;
		<div class="form-group row">
		 <div class="form-group row"><div class="col-md-3"><label>Status:</label></div><div class="col-md-9"> ' . sis_ui_select('status', $options) . '</div> </div>&nbsp;
			<div class="col-md-3"><label></label></div><div class="col-md-9"><input type="button" name="button2" id="button2" value="Save Changes" class="btn btn-primary" onclick="add_section()"/>
		<input type="button" name="button3" id="button3" value="Cancel" class="btn btn-primary" onclick="cancelhref();"/></div> </div>&nbsp;
	</form>';
	return $str; 
}
function sis_organization_campus_add($id)
{   global $DB;
	if (!empty($id))
	{ 
	$sqliu = "select * from {si_campus} where id =$id";
	$campusrecord = $DB->get_records_sql($sqliu);
    $array_section[''];
	foreach($campusrecord as $campus){
		$campuss = isset($campus->campus) ? $campus->campus : null;
		$name = isset($campus->campus_name) ? $campus->campus_name : null;
		$name_ar = isset($campus->campus_name_a) ? $campus->campus_name_a : null;
    } }
	$sqli = "select institute, institute_name from {si_institute}";
	$insrecord = $DB->get_records_sql($sqli);
    $array_institute[''];
	foreach($insrecord as $ins){
	$array_institute[$ins->institute] = $ins->institute_name;
	}
		$options = array(
		'A' => 'Active',
		'I' => 'Inactive',
	);

	$str = '
	<form id="form1" name="form1" method="post" onsubmit="return add_campus()" action="">
		<div class="form-group row">
		<div class="col-md-3"><label>Campus:</label></div><div class="col-md-9"> <input name="campus" type="text" id="campus" size="15" maxlength="100" value="'.$campuss.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Campus Name ER:</label></div><div class="col-md-9"> <input name="campuser" type="text" id="campuser" size="15" maxlength="100" value="'.$name.'" onkeypress="handleKeyPress(event)" /><input name="id_c" type="hidden" id="id_c" size="15" maxlength="100" value="'.$id.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Campus Name AR:</label></div><div class="col-md-9"> <input name="campusar" type="text" id="campusar" size="15" maxlength="100" value="'.$name_ar.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Institute:</label></div><div class="col-md-9"> ' . sis_ui_select('institute', $array_institute) . '</div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Status:</label></div><div class="col-md-9"> ' . sis_ui_select('status', $options) . '</div> </div>&nbsp;
		 <div class="form-group row">	<div class="col-md-3"><label></label></div><div class="col-md-9"><input type="button" name="button2" id="button2" value="Save Changes" class="btn btn-primary" onclick="add_campus()"/>
		<input type="button" name="button3" id="button3" value="Cancel" class="btn btn-primary" onclick="cancelhref();"/></div> </div>&nbsp;
	</form>';
	return $str;
}
function sis_organization_campus()
{
	global $DB;
	$sqli = "select * from {si_campus}";
	$campusrecord = $DB->get_records_sql($sqli);
    $array_campus[''];
	$str = '
			<form id="form1" name="form1" method="post" onsubmit="return search_user()" action="">
				Search: <input name="search" type="text" id="search" size="15" maxlength="100" value="" onkeyup="search_handler()" />&nbsp;
			</form>
			<table class="table table-bordered table-striped" id="org_table" width="100%">
			<thead>
			<tr>
			<th class="header" style="text-align:center;width:35%;" scope="col">Campus</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Campus Name</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Institute</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Action</th>
			</tr>
			</thead>
			<tbody>';
			foreach($campusrecord as $campus)
	        {
			$sect = '<tr class="">
			<td class="cell" style="text-align:center;width:35%;">'.$campus->campus.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$campus->campus_name.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$campus->institute.'</td>
			<td class="cell lastcol" style="text-align:center;width:30%;">
			<a title="Delete Section" href="javascript:delete_campus('.$campus->id.')">
			<i class="fa fa-trash fa-lg"></i></a>&nbsp;<a title="Update Section" href="add_campus.php?id='.$campus->id.'"><i class="fa fa-pencil fa-lg"></i></a></td>
			</tr>';
			$str = $str." ".$sect; 
			}
	      return $str . '</tbody></table>';
}
function sis_organization_institute()
{
     global $DB;
	$sqli = "select * from {si_institute}";
	$instituterecord = $DB->get_records_sql($sqli);
    $array_institute[''];

	$str = '
			<form id="form1" name="form1" method="post" onsubmit="return search_user()" action="">
				Search: <input name="search" type="text" id="search" size="15" maxlength="100" value="" onkeyup="search_handler()" />&nbsp;
			</form>
			<table class="table table-bordered table-striped" id="org_table" width="100%">
			<thead>
			<tr>
			<th class="header" style="text-align:center;width:35%;" scope="col">Institute</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Institute Name</th>
			<th class="header" style="text-align:center;width:35%;" scope="col">Action</th>
			</tr>
			</thead>
			<tbody>';
			foreach($instituterecord as $institute)
	        {
			$sect = '<tr class="">
			<td class="cell" style="text-align:center;width:35%;">'.$institute->institute.'</td>
			<td class="cell" style="text-align:center;width:35%;">'.$institute->institute_name.'</td>
			<td class="cell lastcol" style="text-align:center;width:30%;">
			<a title="Delete Section" href="javascript:delete_institute('.$institute->id.')">
			<i class="fa fa-trash fa-lg"></i></a>&nbsp;<a title="Update Section" href="add_institute.php?id='.$institute->id.'"><i class="fa fa-pencil fa-lg"></i></a></td>
			</tr>'; 
			$str = $str." ".$sect; 
			}
	      return $str . '</tbody></table>';
}
function sis_organization_institute_add($id)
{
	  global $DB;
	if (!empty($id))
	{ 
	$sqliu = "select * from {si_institute} where id =$id";
	$instituterecord = $DB->get_records_sql($sqliu);
    $array_section[''];
	foreach($instituterecord as $institute){
		$institutee = isset($institute->institute) ? $institute->institute : null;
		$name = isset($institute->institute_name) ? $institute->institute_name : null;
		$name_ar = isset($institute->institute_name_a) ? $institute->institute_name_a : null;
    } }
	$options = array(
		'A' => 'Active',
		'I' => 'Inactive',
	);
	$str = '
	<form id="form1" name="form1" method="post" onsubmit="return add_institute()" action="">
		<div class="form-group row">
		<div class="col-md-3"><label>Institute:</label></div><div class="col-md-9"> <input name="institute" type="text" id="institute" size="15" maxlength="100" value="'.$institutee.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Institute Name ER:</label></div><div class="col-md-9"> <input name="instituteer" type="text" id="instituteer" size="15" maxlength="100" value="'.$name.'" onkeypress="handleKeyPress(event)" /><input name="id_i" type="hidden" id="id_i" size="15" maxlength="100" value="'.$id.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Institute Name AR:</label></div><div class="col-md-9"> <input name="institutear" type="text" id="institutear" size="15" maxlength="100" value="'.$name_ar.'" onkeypress="handleKeyPress(event)" /></div> </div>&nbsp;
		<div class="form-group row">
		<div class="col-md-3"><label>Status:</label></div><div class="col-md-9"> ' . sis_ui_select('status', $options) . '</div> </div>&nbsp;
		 <div class="form-group row"><div class="col-md-3"><label></label></div><div class="col-md-9"><input type="button" name="button2" id="button2" value="Save Changes" class="btn btn-primary" onclick="add_institute()"/>
		<input type="button" name="button3" id="button3" value="Cancel" class="btn btn-primary" onclick="cancelhref();"/></div> </div>&nbsp;
	</form>';
	return $str;
}
