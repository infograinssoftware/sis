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
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contain all the global functions for RCYCI module
*/

// This is the library for database access to external system (Peoplesoft). It includes all the functions to retrieve from PS or update peoplesoft 
defined('MOODLE_INTERNAL') || die();
require_once 'dblib.php'; //$PSDB and $LSDB are global variable automatically created once it is included. n function use global $PSDB and $LSDB

$PSDB = rc_get_psdb(); //initialize peoplesoft database connection

///////standard functions///////////////////
function rc_ps_execute_query($query, $recordset, $obj = false)
{
	global $PSDB, $CFG;
	if(!isset($PSDB)) //sometimes in some places, the variable may n ot be initialized
		$PSDB = rc_get_psdb(); //initialize peoplesoft database connection
	$rs = $PSDB->Execute($query); //normal execute and return a record set
	if (!$rs) 
	{
		if($CFG->production)
			return 'Error executing query due to query or connection error';
		else	
			return rc_query_error($query);
	}
	return rc_ps_return_value($rs, $recordset, $obj);
}

function rc_ps_return_value($rs, $recordset, $obj)
{
	if(!$recordset || $obj) //not record set, or require object means return as array
	{
		$result = array();
		while(!$rs->EOF) 
		{
			$rec = $rs->fields;
			if($obj)
				$result[] = (object) $rec; //cast it
			else
				$result[] = $rec;
			$rs->MoveNext();
		}
		return $result;
	}
	else
		return $rs;
}

//obtain a single field
function rc_ps_execute_query_get_field($query, $field)
{
	$rec = rc_ps_execute_query($query, false, false);
	if($rec)
	{
		foreach($rec as $row)
			return $row[$field];
	}
	return '';
}

//obtain a single record
function rc_ps_execute_query_get_record($query)
{
	$rec = rc_ps_execute_query($query, false, false);
	if($rec)
	{
		foreach($rec as $row)
			return $row;
	}
	return false;
}

//just execute a query with no return value. Usually for insert or updated
function rc_ps_execute($query)
{
	global $PSDB;
	$PSDB->Execute($query); //normal execute and return a record set
}

/////end of standard functions/////

//////custom functions starts here///////////////
function rc_ps_get_course_catalog($crse_id)
{
	$query = "select * from sysadm.ps_crse_catalog where crse_id = '$crse_id' order by efft desc";
	return rc_ps_execute_query_get_record($query);
}

function rc_ps_get_credit_hour($class_nbr, $strm)
{
	$query = "select * from sysadm.ps_stdnt_enrl where class_nbr = '$class_nbr' and strm = '$strm'";
	return rc_ps_execute_query_get_field($query, 'UNT_TAKEN');	
}

//check if a section is graded or non graded
function rc_ps_get_grading_basis($emplid, $class_nbr, $strm)
{
	$rec = rc_ps_get_student_enrol($emplid, $class_nbr, $strm);
	return $rec['GRADING_BASIS_ENRL'];
	
}

function rc_ps_get_student_enrol($emplid, $class_nbr, $strm)
{
	$query = "select EMPLID, CLASS_NBR, STRM, STDNT_ENRL_STATUS, ENRL_STATUS_REASON, ENRL_ACTION_LAST, GRADING_BASIS_ENRL,
				TO_CHAR(STATUS_DT, 'DD-MON-YYYY') as status_dt, 
				TO_CHAR(ENRL_ADD_DT, 'DD-MON-YYYY') as ENRL_ADD_DT,
				TO_CHAR(LAST_UPD_DT_STMP, 'DD-MON-YYYY') as LAST_UPD_DT_STMP,
				TO_CHAR(LAST_ENRL_DT_STMP, 'DD-MON-YYYY') as LAST_ENRL_DT_STMP
			from sysadm.ps_stdnt_enrl where class_nbr = '$class_nbr' and emplid = '$emplid' and strm = '$strm'";
	return rc_ps_execute_query_get_record($query);
}

function rc_ps_check_user($emplid, $national_id)
{
	$query = "select * from sysadm.ps_user_login_vw where emplid = '$emplid' and national_id = '$national_id'";	
	$rs = rc_ps_execute_query($query, true);
	if(!$rs->EOF)
		return true;
	else
		return false;
}

//given emplid check if user exist
function rc_ps_user_exist($emplid)
{
	$query = "select * from sysadm.ps_user_login_vw where emplid = '$emplid'";	
	$rs = rc_ps_execute_query($query, true);
	if(!$rs->EOF)
		return true;
	else
		return false;
}

//get all the users
function rc_ps_get_all_user($type = '')
{
	if($type != '')
		$where = " where type = '$type'";
	else
		$where = '';
	$query = "select * from sysadm.ps_user_login_vw $where";	
	$rs = rc_ps_execute_query($query, false);
	return $rs;
}

//get users info
function rc_ps_get_user($emplid)
{
	$query = "select * from sysadm.ps_user_login_vw where emplid = '$emplid'";	
	$rec = rc_ps_execute_query_get_record($query);
	return $rec;
}

function rc_ps_student_info($emplid)
{
	$id = strtoupper($emplid);
	$query = "select * from SYSADM.PS_USER_LOGIN_VW where emplid = '$id' and type = 'student'";
	$rec = rc_ps_execute_query_get_record($query);
	return $rec;
}

function rc_ps_is_active($emplid)
{
	$id = strtoupper($emplid);
	$query = "select * from SYSADM.PS_USER_LOGIN_VW where emplid = '$id' and type = 'student'";
	$rec = rc_ps_execute_query_get_record($query);
	if($rec === false) //not exist, student not active
		return false;
	else
		return true;
}

//check if a student is active (not sure if this is working, so temporary don't use it)
function rc_ps_is_active_old($emplid)
{
	$id = strtoupper($emplid);
	$query = "
		SELECT LOWER(D.EMPLID) AS EMPLID 
			,D.CAMPUS AS INSTITUTE 
			,D.ACAD_PROG AS PROGRAM 
  		FROM SYSADM.PS_ACAD_PROG D 
		WHERE D.EMPLID = '$id' 
			 AND D.PROG_STATUS='AC' 
			 AND D.EFFDT = ( 
			 SELECT MAX(D_ED.EFFDT) 
			  FROM SYSADM.PS_ACAD_PROG D_ED 
			 WHERE D.EMPLID = D_ED.EMPLID 
			   AND D.ACAD_CAREER = D_ED.ACAD_CAREER 
			   AND D.STDNT_CAR_NBR = D_ED.STDNT_CAR_NBR 
			   AND D_ED.EFFDT <= SYSDATE) 
			   AND D.EFFSEQ = ( 
			 SELECT MAX(D_ES.EFFSEQ) 
			  FROM SYSADM.PS_ACAD_PROG D_ES 
			 WHERE D.EMPLID = D_ES.EMPLID 
			   AND D.ACAD_CAREER = D_ES.ACAD_CAREER 
			   AND D.STDNT_CAR_NBR = D_ES.STDNT_CAR_NBR 
			   AND D.EFFDT = D_ES.EFFDT)
		";
	$rec = rc_ps_execute_query_get_record($query);
	if($rec === false) //not exist, student not active
		return false;
	else
		return true;
}

//check if a student is active
function rc_ps_count_active_student()
{
	$query = "
		SELECT COUNT(D.EMPLID) AS TOTAL, D.CAMPUS  
  		FROM SYSADM.PS_ACAD_PROG D 
		WHERE D.PROG_STATUS='AC' 
			 AND D.EFFDT = ( 
			 SELECT MAX(D_ED.EFFDT) 
			  FROM SYSADM.PS_ACAD_PROG D_ED 
			 WHERE D.EMPLID = D_ED.EMPLID 
			   AND D.ACAD_CAREER = D_ED.ACAD_CAREER 
			   AND D.STDNT_CAR_NBR = D_ED.STDNT_CAR_NBR 
			   AND D_ED.EFFDT <= SYSDATE) 
			   AND D.EFFSEQ = ( 
			 SELECT MAX(D_ES.EFFSEQ) 
			  FROM SYSADM.PS_ACAD_PROG D_ES 
			 WHERE D.EMPLID = D_ES.EMPLID 
			   AND D.ACAD_CAREER = D_ES.ACAD_CAREER 
			   AND D.STDNT_CAR_NBR = D_ES.STDNT_CAR_NBR 
			   AND D.EFFDT = D_ES.EFFDT)
		GROUP BY D.CAMPUS
		";
	$rs = rc_ps_execute_query($query, false);
	return $rs;
}

//use in attendance to check if previous semester it has failed
function rc_ps_get_previous_grade($emplid, $crse_id)
{
	global $CFG;
	$query = "
		select 
			a.emplid, a.strm, a.class_nbr, a.CRSE_GRADE_OFF, b.CRSE_ID, b.CRSE_OFFER_NBR, b.SUBJECT, b.CATALOG_NBR 
		from 
			SYSADM.PS_STDNT_ENRL a inner join SYSADM.PS_CLASS_TBL b on a.strm = b.strm and a.class_nbr = b.class_nbr
		where 
			a.emplid = '$emplid' and crse_id = '$crse_id' 
			and a.crse_grade_off <> ' ' and a.strm < $CFG->semester
		order by strm desc
	";
	$rs = rc_ps_execute_query($query, false, true);
	return $rs;
}

//count the number of prep semester
function rc_ps_count_prep_semester($emplid)
{
	$query = "
		select 
			a.emplid, a.strm, a.class_nbr, a.CRSE_GRADE_OFF, b.CRSE_ID, b.CRSE_OFFER_NBR, (b.SUBJECT || b.CATALOG_NBR) as COURSE_CODE 
		from 
			SYSADM.PS_STDNT_ENRL a inner join SYSADM.PS_CLASS_TBL b on a.strm = b.strm and a.class_nbr = b.class_nbr
		where 
			a.emplid = '$emplid'
		order by strm desc
	";
	$arr = array();
	$rec = rc_ps_execute_query($query, false, true);
	foreach($rec as $r)
	{
		if($r->COURSE_CODE <> 'ENG000') //don't do it for ENG000 as it is not consider as a semester
			$arr[$r->STRM] = 1;
	}
	return count($arr);
}
//use distince from class_tbl to get list of department
function rc_ps_get_department_list($fullname = true)
{
	$query = "select * from SYSADM.PS_ACAD_ORG_TBL ORDER BY ACAD_ORG";
	$rs = rc_ps_execute_query($query, true);
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		if($fullname)
			$result[$rec['ACAD_ORG']] = $rec['DESCR']; //the moodle name as the key
		else
			$result[$rec['ACAD_ORG']] = $rec['ACAD_ORG']; //the moodle name as the key
		$rs->MoveNext();
	}
	return $result;
}

//can either use crse_id or class nbr. Pass a '' if not use
//rec is to return entire record, or return just the field
function rc_ps_get_course_department($crse_id, $class_nbr, $rec)
{
	global $CFG;
	if($crse_id != '')
		$where = "crse_id = '$crse_id'";
	else if($class_nbr != '')
		$where = "class_nbr = '$class_nbr'";
	else //if nothing supplied, return blank
		return '';
	$query = "select * from SYSADM.PS_CLASS_TBL where $where AND strm = '$CFG->semester'";
	if(!$rec) //return field
		return rc_ps_execute_query_get_field($query, 'ACAD_ORG');	
	else
		return rc_ps_execute_query_get_record($query);
}

//use distince from class_tbl to get list of department
function rc_ps_get_course_department_list($strm)
{
	$query = "select distinct acad_org, descr from SYSADM.PS_CLASS_TBL where strm = '$strm' ORDER BY acad_org";
	$rs = rc_ps_execute_query($query, true);
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		$result[$rec['ACAD_ORG']] = $rec['ACAD_ORG']; //the moodle name as the key
		$rs->MoveNext();
	}
	return $result;
}

//use distince from class_tbl to get list of campus
function rc_ps_get_course_campus_list($strm)
{
	$query = "select distinct campus from SYSADM.PS_CLASS_TBL where strm = '$strm' ORDER BY campus";
	$rs = rc_ps_execute_query($query, true);
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		$result[$rec['CAMPUS']] = $rec['CAMPUS']; //the moodle name as the key
		$rs->MoveNext();
	}
	return $result;
}

function rc_ps_get_user_department($emplid, $code_only = false)
{
	$query = "select * from SYSADM.PS_INSTR_ADVISOR where emplid = '$emplid' ORDER BY effdt DESC";
	$dept = rc_ps_execute_query_get_field($query, 'ACAD_ORG');
	if($code_only)
		return $dept;
	if($dept != '') //has department, get the full department record
	{
		$query = "select * from SYSADM.PS_ACAD_ORG_TBL where acad_org = '$dept' ORDER BY effdt DESC";
		return rc_ps_execute_query_get_record($query);
	}
}

function rc_ps_get_department($acad_org, $field)
{
	$query = "select * from SYSADM.PS_ACAD_ORG_TBL where acad_org = '$acad_org'";
	return rc_ps_execute_query_get_field($query, $field);
}

//because we cannot tell which user is male or female, we based on the department
function rc_ps_get_department_gender($acad_org)
{
	$a = array(
		'ICT' => 'M',
		'MSD-M' => 'M',
		'ELC-YUCF' => 'F',
		'GS' => 'M',
		'ALD-M' => 'M',
		'CSD' => 'M',
		'GS-YIC' => 'M',
		'GS-YUC' => 'M',
		'MET' => 'M',
		'ELC' => 'M',
		'BSD' => 'M',
		'MESD' => 'M',
		'MSD-F' => 'F',
		'SPD' => 'M',
		'YUC' => 'M',
		'GET' => 'M',
		'ALD-F' => 'F',
		'CECSD' => 'M',
		'RCYCI' => 'M',
		'EIET' => 'M',
		'CSED-M' => 'M',
		'YIC' => 'M',
		'YTI' => 'M',
		'CHET' => 'M',
		'CSCE' => 'M',
		'EEET' => 'M',
		'IDD' => 'M',
		'EPET' => 'M',
		'CSED-F' => 'F',
		'EEISD' => 'M',
		'GS-YUCF' => 'F',
		'IDD-F' => 'F',
	);
	if(isset($a[$acad_org]))
	{
		$g = $a[$acad_org];
		return $g;
	}
	else
		return 'M'; //default to male
}
//get all the attendance record for a class_nbr by the cut off date
function rc_ps_get_attendance_class($class_nbr, $last_date, $first_date)
{
	global $CFG;

	if($first_date != '') //has first date
		$fd = " AND CLASS_ATTEND_DT >= TO_DATE('$first_date', 'dd-mon-yyyy')";
	else
		$fd = '';

	$query = "select A.*, UPPER(TO_CHAR(class_attend_dt, 'dd-mon-yyyy')) AS ATTEND_DATE, TO_CHAR(ATTEND_FROM_TIME, 'HH24:MI') AS START_TIME, TO_CHAR(ATTEND_FROM_TIME, 'DD-Mon-RR HH24:MI:SS.FF') AS START_TIME_24, TO_CHAR(ATTEND_TO_TIME, 'HH24:MI') AS END_TIME, TO_CHAR(ATTEND_TO_TIME, 'DD-Mon-RR HH24:MI:SS.FF') AS END_TIME_24 from SYSADM.PS_CLASS_ATTENDNCE A where strm = '$CFG->semester' AND class_nbr = '$class_nbr' AND CLASS_ATTEND_DT <= TO_DATE('$last_date', 'dd-mon-yyyy') $fd";
	return rc_ps_execute_query($query, true, false);
}

//update an attendance record. Receive an attendance record. absence = true for absence and false for present
//existing is the attendance record in moodle
function rc_ps_update_attendance($rec, $existing, $absence)
{
	$last_date = $rec['ATTEND_DATE'];
	if($absence)
	{
		$start_time = $rec['START_TIME_24'];
		if($existing->excused != '0') //has excuse
			$excused = "ATTEND_REASON = '" . $existing->excused . "'";
		else
			$excused = "ATTEND_REASON = ' '";
		$query = "
			update sysadm.PS_CLASS_ATTENDNCE
				set ATTEND_FROM_TIME = NULL,
				ATTEND_PRESENT = 'N',
				CONTACT_MINUTES = 0,
				$excused
			where
				strm = '".$rec['STRM']."'
				and class_nbr = '".$rec['CLASS_NBR']."'
				and emplid = '".$rec['EMPLID']."'
				and class_attend_dt = TO_DATE('$last_date', 'dd-mon-yyyy')
				and ATTEND_TMPLT_NBR = '".$rec['ATTEND_TMPLT_NBR']."'
		";
	//		and attend_from_time = TO_TIMESTAMP('$start_time', 'DD-Mon-RR HH24:MI:SS.FF')
		rc_ps_execute($query);
	}
	else
	{
		$end_time = $rec['END_TIME'];
		$arr = explode(':', $end_time);
		$arr[0] = $arr[0] - 1;
		$start_time = $arr[0] . ':' . $arr[1];
		$end_time_24 = $rec['END_TIME_24'];
		$arr = explode($end_time, $end_time_24);
		$start_time_24 = $arr[0] . $start_time . $arr[1];
		$query = "
			update sysadm.PS_CLASS_ATTENDNCE
				set attend_from_time = TO_TIMESTAMP('$start_time_24', 'DD-Mon-RR HH24:MI:SS.FF'),
				ATTEND_PRESENT = 'Y',
				CONTACT_MINUTES = 60,
				ATTEND_REASON = ' '
			where
				strm = '".$rec['STRM']."'
				and class_nbr = '".$rec['CLASS_NBR']."'
				and emplid = '".$rec['EMPLID']."'
				and class_attend_dt = TO_DATE('$last_date', 'dd-mon-yyyy')
				and ATTEND_TMPLT_NBR = '".$rec['ATTEND_TMPLT_NBR']."'
		";
		rc_ps_execute($query);
	}
}

//get the active student or teacher for a section
function rc_ps_get_enrol_user_class($class_nbr, $role)
{
	global $CFG;
	$tableName = 'SYSADM.PS_MOODLE_ENRL_'.$CFG->semester.'_VW';
	$query = "SELECT * FROM $tableName WHERE CLASS_NBR = '$class_nbr' AND ROLE = '$role' ORDER BY EMPLID";
	$rs = rc_ps_execute_query($query, false, true);
	return $rs;
}


//get all the defined section in PS_MOODLE_ENRL_XXXX_VW. We need to check if the class_nbr in PS still the same as that in moodle. 
//this could happen if user deleted and added new section
function rc_ps_get_moodle_sections($field)
{
	global $CFG;
	$tableName = 'SYSADM.PS_TERM_CLASS_'.$CFG->semester.'_VW';
	$query = "SELECT * FROM $tableName ORDER BY M_NAME";
	$rs = rc_ps_execute_query($query, true);
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		$result[$rec[$field]] = $rec; //the moodle name as the key
		$rs->MoveNext();
	}
	return $result;
}

function rc_ps_get_section($class_nbr, $inStr = '')
{
	global $CFG;
	if($inStr != '')
		$in = " IN($inStr)";
	else
		$in = " = '$class_nbr'";
	$query = "SELECT * FROM SYSADM.PS_CLASS_TBL WHERE STRM = '$CFG->semester' and CLASS_NBR $in";
	$rs = rc_ps_execute_query($query, true);
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		if($inStr == '')
			return $rec;
		$result[$rec['CLASS_NBR']] = $rec;
		$rs->MoveNext();
	}
	if(count($result) > 0)
		return $result;
	else
		return false;
}

function rc_ps_course_type($course_code, $campus)
{
	$all_sections = rc_ps_get_all_section($course_code, '', $campus);
	$arr = array();
	foreach($all_sections as $s)
		$arr[$s->SSR_COMPONENT] = $s->SSR_COMPONENT;
	if(isset($arr['LEC']) && isset($arr['LAB'])) //theory and lab
		return 1; //Theoy and lab
	else if(isset($arr['LEC'])) //theory only
		return 2; //Theory only
	else
		return 3; //lab only
}

//return null if not found. Return the record if found
function rc_ps_get_all_section($course_code, $class_type = '', $campus = '')
{
	global $CFG;
	if($class_type != '')
		$ct_condition = "  and SSR_COMPONENT = '$class_type'";
	else		
		$ct_condition = '';

	if($campus != '')
		$cm_condition = "  and CAMPUS = '$campus'";
	else		
		$cm_condition = '';

	$query = "SELECT * FROM SYSADM.PS_CLASS_TBL WHERE STRM = '$CFG->semester' and (SUBJECT || CATALOG_NBR) = '$course_code' $ct_condition $cm_condition ORDER BY CLASS_SECTION";
	$result = rc_ps_execute_query($query, false, true);
	return $result;
}

//return null if not found. Return the record if found
function rc_ps_get_theory_section($course_code, $emplid)
{
	global $CFG;
	$query = "SELECT * FROM SYSADM.PS_CLASS_TBL_SE_VW WHERE STRM = '$CFG->semester' and (SUBJECT || CATALOG_NBR) = '$course_code' and EMPLID = '$emplid' and SSR_COMPONENT = 'LEC'";
	$result = rc_ps_execute_query($query, false, true);
	foreach($result as $r)
		return $r;
	return null;
}

//return null if not found. Return the record if found
function rc_ps_get_lab_section($course_code, $emplid)
{
	global $CFG;
	$query = "SELECT * FROM SYSADM.PS_CLASS_TBL_SE_VW WHERE STRM = '$CFG->semester' and (SUBJECT || CATALOG_NBR) = '$course_code' and EMPLID = '$emplid' and SSR_COMPONENT = 'LAB'";
	$result = rc_ps_execute_query($query, false, true);
	foreach($result as $r)
		return $r;
	return null;
}

//return all the sections in the semester
function rc_ps_get_semester_section($class_type = '', $distinct = false)
{
	global $CFG;
	if($class_type == '')
		$condition = '';
	else
		$condition = " AND SSR_COMPONENT = '$class_type'";
	if(!$distinct)
		$query = "SELECT * FROM SYSADM.PS_CLASS_TBL WHERE STRM = '$CFG->semester'" . $condition . " ORDER BY CRSE_ID";
	else
		$query = "SELECT distinct crse_id, class_nbr, campus FROM SYSADM.PS_CLASS_TBL WHERE STRM = '$CFG->semester'" . $condition . " ORDER BY CRSE_ID"; //this is for individual course
	$result = rc_ps_execute_query($query, false, true);
	return $result;
}

//given a class_nbr, get the crse_id
function rc_ps_get_crse_id($class_nbr)
{
	$section = rc_ps_get_section($class_nbr);
	if($section !== false)
	{
		$obj = new stdClass();
		$obj->crse_id = $section['CRSE_ID'];
		$obj->crse_offer_nbr = $section['CRSE_OFFER_NBR'];
		$obj->campus = $section['CAMPUS'];
		$obj->subject = $section['SUBJECT'];
		$obj->catalog_nbr = $section['CATALOG_NBR'];
		$obj->descr = $section['DESCR'];
		$obj->acad_org = $section['ACAD_ORG'];
		return $obj;
	}
	else
		return false;
}

function rc_ps_get_arabic_name($emplid)
{
	$query = "SELECT * FROM SYSADM.PS_NAMES WHERE EFFDT = (SELECT MAX(EFFDT) FROM SYSADM.PS_NAMES WHERE NAME_TYPE = 'PRI'  and emplid = '$emplid') and emplid = '$emplid'";
//	$query = "select * from sysadm.ps_names_ara where emplid = '$emplid'";
	$rec = rc_ps_execute_query($query, false);
	foreach($rec as $r)
		return $r['FIRST_NAME'] . ' ' . $r['MIDDLE_NAME'] . ' ' . $r['LAST_NAME'];
	return ''; //if not found
}

function rc_ps_get_roomlist($building_filter, $recordset = true)
{
	global $CFG;
	$semester = $CFG->semester;
	if($building_filter != '')
		$building = " AND A.CAMPUS = '$building_filter'";
	else
		$building = '';
	$query = "
		select distinct(facility_id) 
		FROM SYSADM.PS_CLASS_TBL A INNER JOIN SYSADM.PS_CLASS_MTG_PAT B ON 
			A.CRSE_ID = B.CRSE_ID AND 
			A.CRSE_OFFER_NBR = B.CRSE_OFFER_NBR AND 
			A.CLASS_SECTION = B.CLASS_SECTION 
		WHERE A.strm = '$semester' $building
		ORDER BY FACILITY_ID
		";
	return rc_ps_execute_query($query, $recordset);
}

//get all the classes, given the class type as filter. Blank for no filter
function rc_ps_get_all_class($class_type = '', $recordset = true, $obj = false, $condition = array())
{
	global $CFG;
	if($class_type != '')
		$filter = " AND ssr_component = '$class_type'";
	else
		$filter = '';
	foreach($condition as $key => $c)
	{
		if($filter != '')
			$filter = $filter . ' AND ';
		$filter = $filter . $key . " = '$c'";
	}
	$query = "select * from SYSADM.PS_CLASS_TBL where strm = '$CFG->semester' $filter ORDER BY CLASS_NBR";
	return rc_ps_execute_query($query, $recordset, $obj);
}

//get all student in the theory sections
function rc_ps_get_all_theory($class_nbr)
{
	$ps_course = rc_ps_get_crse_id($class_nbr);
	global $CFG;
	$query = "SELECT A.emplid, B.name, A.strm, A.class_nbr, A.SUBJECT, A.CATALOG_NBR, A.CLASS_SECTION FROM SYSADM.PS_CLASS_TBL_SE_VW A INNER JOIN 
				SYSADM.PS_NAMES_ENG B on A.emplid = B.emplid 
			  WHERE A.STRM = '$CFG->semester' and A.CRSE_ID = '$ps_course->crse_id' AND A.CAMPUS = '$ps_course->campus' AND A.SSR_COMPONENT = 'LEC' AND  A.STDNT_ENRL_STATUS = 'E' AND ENRL_STATUS_REASON = 'ENRL' AND CRSE_GRADE_OFF NOT IN ('W', 'WP', 'WF', 'DN')
			  ORDER BY A.EMPLID
	";

	$rs = rc_ps_execute_query($query, true);
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		$result[$rec['EMPLID']] = (object) $rec; //cast it
		$rs->MoveNext();
	}
	return $result;
}

//get student in a lab with proper theory section
//filter class is the filter that will only include student that matches the theory class_nbr. 
function rc_ps_get_lab_with_theory($course, $filter_class = '')
{
	//first we get all the students
	$students = rc_ps_get_course_student($course, false, true);
	$all_theory = rc_ps_get_all_theory($course->idnumber);
	$unset_arr = array();
	foreach($students as $key => $student) //find the theory section
	{
		if(isset($all_theory[$student->EMPLID]))
		{
			$class_section = $all_theory[$student->EMPLID]->CLASS_SECTION;
			$section = $class_section[2] . $class_section[3];
			$student->theory_class_nbr = $all_theory[$student->EMPLID]->CLASS_NBR;
			$student->theory_course_code = $all_theory[$student->EMPLID]->SUBJECT . $all_theory[$student->EMPLID]->CATALOG_NBR;
			$student->theory_class_section = $class_section;
			$student->theory_section = $section;
			if($filter_class != '')
			{
				if($student->theory_class_nbr != $filter_class) //not matching, add to the unset list
					$unset_arr[] = $key;
			}
		}
		else
			$unset_arr[] = $key;
	}
	foreach($unset_arr as $a)
		unset($students[$a]);
	return $students;
}

//Get the list of student enrolled in a course (section)
//due to the problem with lab student, we do not allow recordset
function rc_ps_get_course_student($course, $obj = false)
{
	global $CFG;
	$query = "SELECT A.emplid, B.name, A.strm, A.class_nbr FROM SYSADM.PS_STDNT_ENRL A INNER JOIN 
				SYSADM.PS_NAMES_ENG B on A.emplid = B.emplid 
			  WHERE A.STRM = '$CFG->semester' AND 
			  A.CLASS_NBR = '$course->idnumber' AND 
			  A.STDNT_ENRL_STATUS = 'E' AND 
			  ENRL_STATUS_REASON = 'ENRL' AND 
			  CRSE_GRADE_OFF NOT IN ('W', 'WP', 'WF', 'DN')
			  ORDER BY A.EMPLID
	";
	$c = rc_parse_course($course, false);
	$rs = rc_ps_execute_query($query, true);

	//1 = theory and lab, 2 = theory only, 3 = lab only
	$coursetype = rc_ps_course_type($c['catalog_nbr'], $c['campus']);
	if($c['class_type'] == 'Lab' && $coursetype == 1) //only for theory and lab
	{
		$all_theory = rc_ps_get_all_theory($course->idnumber);
	}
	else
		$all_theory = array();
	
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		if($c['class_type'] == 'Lab') //if it is lab, we have to make sure that the student exist in theory. Coz student could have been dropped in theory
		{
			if($coursetype != 1 || isset($all_theory[$rec['EMPLID']])) //if it is theory or lab, just add, but if theory and lab, check to make sure student exist in theory
			{
				if($obj)
					$result[] = (object) $rec; //cast it
				else
					$result[] = $rec;
			}
		}
		else //if it is theory, we just add it
		{
			if($obj)
				$result[] = (object) $rec; //cast it
			else
				$result[] = $rec;
		}
		$rs->MoveNext();
	}
//	print_object($result);
	return $result;
}

function rc_ps_get_section_timetable($class_nbr, $recordset = true)
{
	global $CFG;
	$arr = explode(',', $class_nbr);
	if(count($arr) > 1) //more than one courses
		$inStr = "B.CLASS_NBR in($class_nbr)";
	else
		$inStr = "B.CLASS_NBR = '$class_nbr'";
	$query = "
         select
                A.CRSE_ID, B.CRSE_OFFER_NBR, A.CLASS_SECTION, A.CLASS_MTG_NBR, A.FACILITY_ID, TO_CHAR(A.MEETING_TIME_START, 'HH24:MI') AS START_TIME, TO_CHAR(A.MEETING_TIME_END, 'HH24:MI') AS END_TIME, A.MON, A.TUES, A.WED, A.THURS, A.FRI, A.SAT, A.SUN, (B.SUBJECT || B.CATALOG_NBR) AS CATALOG_NBR, B.CAMPUS, C.EMPLID, (D.FIRST_NAME || ' ' || D.LAST_NAME) AS NAME_FORMAL,
				CASE WHEN 
					A.SUN = 'Y' THEN 2 ELSE 
					CASE WHEN
						A.MON = 'Y' THEN 3 ELSE 
						CASE WHEN
							A.TUES = 'Y' THEN 4 ELSE 
							CASE WHEN
								A.WED = 'Y' THEN 5 ELSE 6 
							END
						END
					END
				END AS DAYINDEX
                FROM SYSADM.PS_CLASS_MTG_PAT A
                INNER JOIN  SYSADM.PS_CLASS_TBL B ON
                    A.CRSE_ID = B.CRSE_ID AND
                    A.CRSE_OFFER_NBR = B.CRSE_OFFER_NBR AND
                    A.CLASS_SECTION = B.CLASS_SECTION
                LEFT JOIN SYSADM.PS_CLASS_INSTR C ON
                    A.CRSE_ID = C.CRSE_ID AND
                    A.CRSE_OFFER_NBR = C.CRSE_OFFER_NBR AND
                    A.CLASS_SECTION = C.CLASS_SECTION AND
                    A.CLASS_MTG_NBR = C.CLASS_MTG_NBR   
                LEFT JOIN SYSADM.PS_NAMES_ENG D ON
                    C.EMPLID = D.EMPLID
                WHERE
                    $inStr AND A.STRM = '$CFG->semester' and B.STRM = '$CFG->semester' and C.STRM = '$CFG->semester'	
				ORDER BY DAYINDEX, START_TIME					
	";
	return rc_ps_execute_query($query, $recordset);
}

//given a user id, return the timetable data with teacher (this is for student)
//the difference between student and teacher is that a section can be without teacher, so need to show the sectiona as well.
//we have to use left join
function rc_ps_get_room_timetable($code, $recordset = true)
{
	global $CFG;
	//we use the enrollment view to have the universal sql for the schedule for lecturer and student. See the end for lecturer only SQL
	$query = "
         select
                A.CRSE_ID, B.CRSE_OFFER_NBR, A.CLASS_SECTION, A.CLASS_MTG_NBR, TO_CHAR(A.MEETING_TIME_START, 'HH24:MI') AS START_TIME, TO_CHAR(A.MEETING_TIME_END, 'HH24:MI') AS END_TIME, A.MON, A.TUES, A.WED, A.THURS, A.FRI, A.SAT, A.SUN, (B.SUBJECT || B.CATALOG_NBR) AS CATALOG_NBR, B.CAMPUS, C.EMPLID AS SAFEER, (D.FIRST_NAME || ' ' || D.LAST_NAME) AS NAME_FORMAL,
				CASE WHEN 
					A.SUN = 'Y' THEN 2 ELSE 
					CASE WHEN
						A.MON = 'Y' THEN 3 ELSE 
						CASE WHEN
							A.TUES = 'Y' THEN 4 ELSE 
							CASE WHEN
								A.WED = 'Y' THEN 5 ELSE 6 
							END
						END
					END
				END AS DAYINDEX
                FROM SYSADM.PS_CLASS_MTG_PAT A
                INNER JOIN  SYSADM.PS_CLASS_TBL B ON
                    A.CRSE_ID = B.CRSE_ID AND
                    A.CRSE_OFFER_NBR = B.CRSE_OFFER_NBR AND
                    A.CLASS_SECTION = B.CLASS_SECTION
                LEFT JOIN SYSADM.PS_CLASS_INSTR C ON
                    A.CRSE_ID = C.CRSE_ID AND
                    A.CRSE_OFFER_NBR = C.CRSE_OFFER_NBR AND
                    A.CLASS_SECTION = C.CLASS_SECTION AND
                    A.CLASS_MTG_NBR = C.CLASS_MTG_NBR   
                LEFT JOIN SYSADM.PS_NAMES_ENG D ON
                    C.EMPLID = D.EMPLID
                WHERE
                    A.FACILITY_ID = '$code' AND A.STRM = '$CFG->semester' and B.STRM = '$CFG->semester' and C.STRM = '$CFG->semester'	
				ORDER BY DAYINDEX, START_TIME					
		";
	return rc_ps_execute_query($query, $recordset);
}

//given a user id, return the timetable data with teacher (this is for student)
//the difference between student and teacher is that a section can be without teacher, so need to show the sectiona as well.
//we have to use left join
function rc_ps_get_student_timetable($user, $recordset = true)
{
	global $CFG;
	$emplid = strtoupper($user->idnumber); //oracle store any character in upper case, while moodle in lower case
	//we use the enrollment view to have the universal sql for the schedule for lecturer and student. See the end for lecturer only SQL
	$query = "
		select A.EMPLID, B.CRSE_OFFER_NBR ,A.STRM, B.CLASS_NBR, (B.SUBJECT || B.CATALOG_NBR) AS CATALOG_NBR, C.CLASS_SECTION, A.UNT_TAKEN, C.CLASS_MTG_NBR, C.FACILITY_ID, TO_CHAR(c.MEETING_TIME_START, 'HH24:MI:SS') AS START_TIME, TO_CHAR(C.MEETING_TIME_END, 'HH24:MI:SS') AS END_TIME, C.MON, C.TUES, C.WED, C.THURS, C.FRI, C.SAT, C.SUN, B.CRSE_ID, B.CAMPUS, E.EMPLID AS SAFEER, (E.FIRST_NAME || ' ' || E.LAST_NAME) AS NAME_FORMAL,
				CASE WHEN 
					C.SUN = 'Y' THEN 2 ELSE 
					CASE WHEN
						C.MON = 'Y' THEN 3 ELSE 
						CASE WHEN
							C.TUES = 'Y' THEN 4 ELSE 
							CASE WHEN
								C.WED = 'Y' THEN 5 ELSE 6 
							END
						END
					END
				END AS DAYINDEX
		from SYSADM.PS_STDNT_ENRL A
				INNER JOIN SYSADM.PS_CLASS_TBL B ON
				   A.STRM=B.STRM AND
				   A.CLASS_NBR=B.CLASS_NBR
				INNER JOIN SYSADM.PS_CLASS_MTG_PAT C ON 
				   B.CRSE_OFFER_NBR=C.CRSE_OFFER_NBR AND
				   B.CRSE_ID=C.CRSE_ID AND
				   B.CLASS_SECTION = C.CLASS_SECTION
				LEFT JOIN SYSADM.PS_CLASS_INSTR D ON 
				   C.CRSE_OFFER_NBR=D.CRSE_OFFER_NBR AND
				   C.CRSE_ID=D.CRSE_ID AND
				   C.CLASS_SECTION = D.CLASS_SECTION AND
				   C.CLASS_MTG_NBR = D.CLASS_MTG_NBR
				LEFT JOIN SYSADM.PS_NAMES_ENG E ON
				   D.EMPLID = E.EMPLID 				   
		WHERE A.STDNT_ENRL_STATUS = 'E'
			  AND ENRL_STATUS_REASON = 'ENRL' 
			  AND CRSE_GRADE_OFF NOT IN ('W', 'WP', 'WF', 'DN')
			  AND A.EMPLID = '$emplid' and A.STRM = '$CFG->semester' and B.STRM = '$CFG->semester' and C.STRM = '$CFG->semester' and D.STRM = '$CFG->semester'
		ORDER BY B.CATALOG_NBR, C.CLASS_SECTION, DAYINDEX, START_TIME, E.EMPLID
		";
/*
The left join for PS_NAMES is a bit unique because Oracle does not allow subquery in outer join (i.e. left join). So we push the subquery into the outer join by basically joining the query with the subquery (imagine the SELECT * PS_NAMES as a table. In other word, we already have a table with the latest effective date)
				LEFT JOIN SYSADM.PS_NAMES E ON 
				   D.EMPLID = E.EMPLID AND 
				   E.NAME_TYPE = 'PRF'

*/	
	return rc_ps_execute_query($query, $recordset);
}

//given user id return a teacher's timetable
function rc_ps_get_teacher_timetable($user, $recordset = true)
{
	global $CFG;
	$emplid = strtoupper($user->idnumber); //oracle store any character in upper case, while moodle in lower case
	//we use the enrollment view to have the universal sql for the schedule for lecturer and student. See the end for lecturer only SQL
	$query = "
		select D.EMPLID, B.CRSE_OFFER_NBR ,B.STRM, B.CLASS_NBR, (B.SUBJECT || B.CATALOG_NBR) AS CATALOG_NBR, C.CLASS_SECTION, C.CLASS_MTG_NBR, C.FACILITY_ID, TO_CHAR(c.MEETING_TIME_START, 'HH24:MI:SS') AS START_TIME, TO_CHAR(C.MEETING_TIME_END, 'HH24:MI:SS') AS END_TIME, C.MON, C.TUES, C.WED, C.THURS, C.FRI, C.SAT, C.SUN, B.CRSE_ID, CAMPUS, E.EMPLID AS SAFEER, (E.FIRST_NAME || ' ' || E.LAST_NAME) AS NAME_FORMAL,
				CASE WHEN 
					C.SUN = 'Y' THEN 2 ELSE 
					CASE WHEN
						C.MON = 'Y' THEN 3 ELSE 
						CASE WHEN
							C.TUES = 'Y' THEN 4 ELSE 
							CASE WHEN
								C.WED = 'Y' THEN 5 ELSE 6 
							END
						END
					END
				END AS DAYINDEX
		from SYSADM.PS_CLASS_TBL B
				INNER JOIN SYSADM.PS_CLASS_MTG_PAT C ON 
				   B.CRSE_OFFER_NBR=C.CRSE_OFFER_NBR AND
				   B.CRSE_ID=C.CRSE_ID AND
				   B.CLASS_SECTION = C.CLASS_SECTION
				INNER JOIN SYSADM.PS_CLASS_INSTR D ON 
				   C.CRSE_OFFER_NBR=D.CRSE_OFFER_NBR AND
				   C.CRSE_ID=D.CRSE_ID AND
				   C.CLASS_SECTION = D.CLASS_SECTION AND
				   C.CLASS_MTG_NBR = D.CLASS_MTG_NBR
				INNER JOIN SYSADM.PS_NAMES_ENG E ON
				   D.EMPLID = E.EMPLID AND
				   D.EMPLID = E.EMPLID
		WHERE D.EMPLID = '$emplid' and B.STRM = '$CFG->semester' and C.STRM = '$CFG->semester' and D.STRM = '$CFG->semester'
		ORDER BY B.CATALOG_NBR, C.CLASS_SECTION, DAYINDEX, START_TIME		
		";
	return rc_ps_execute_query($query, $recordset);
}

///Grade book functions/////////
function rc_ps_get_system_grade_category()
{
	global $CFG;
	$query = "select * from SYSADM.PS_LAM_TYPE WHERE EFF_STATUS = 'A' order by lam_type";
	return rc_ps_execute_query($query, false, true);
}

function rc_ps_get_class_grade_category($class_nbr)
{
	global $CFG;
	$query = "select a.*, b.descr from SYSADM.PS_LAM_CLAS_TYP_PR a INNER JOIN SYSADM.PS_LAM_TYPE b ON a.lam_type = b.lam_type where a.class_nbr = '$class_nbr' and a.strm = $CFG->semester";
	return rc_ps_execute_query($query, false, true);
}

function rc_ps_get_class_activity($class_nbr, $lam_type = '')
{
	global $CFG;
	if($lam_type != '')
		$where = " and lam_type = '$lam_type'";
	else
		$where = '';
	$query = "select * from sysadm.ps_lam_class_actv where class_nbr = '$class_nbr' and strm = $CFG->semester $where";
	return rc_ps_execute_query($query, false, true);
}

function rc_ps_get_class_activity_grade($class_nbr, $sequence_no = '')
{
	global $CFG;
	if($sequence_no != '')
		$where = " and sequence_no = '$sequence_no'";
	else
		$where = '';
	$query = "select * from sysadm.ps_stdnt_grade_dtl where class_nbr = '$class_nbr' and strm = $CFG->semester $where order by emplid";
	return rc_ps_execute_query($query, false, true);
}

//get all the student enrollment in a semester. Only get the graded record
function rc_ps_get_all_student_enrol($strm)
{
	global $CFG;
	$query = "select * from SYSADM.PS_CLASS_TBL_SE_VW where strm = '$strm' and CRSE_GRADE_INPUT <> ' ' and campus = 'YIC' order by emplid";
	return rc_ps_execute_query($query, true);	
}

//get all the student enrollment in a semester. Only get the graded record. This one breaks the data into campus to avoid too many records
function rc_ps_get_all_student_enrol_rec($strm, $campus)
{
	global $CFG;
	$query = "select * from SYSADM.PS_CLASS_TBL_SE_VW where strm = '$strm' and CRSE_GRADE_INPUT <> ' ' and campus = '$campus' order by emplid";
	return rc_ps_execute_query($query, true);	
}

function rc_ps_get_student_cgpa($emplid)
{
	$query = "SELECT A.EMPLID, A.ACAD_CAREER, A.CAMPUS, A.ACAD_PROG, A.PROG_STATUS, B.ACAD_PLAN, C.TOT_TAKEN_GPA, C.CUM_GPA, E.DESCR 
  FROM SYSADM.PS_ACAD_PROG A, (SYSADM.PS_ACAD_PLAN B LEFT OUTER JOIN  SYSADM.PS_ACAD_PLAN_TBL E ON  B.ACAD_CAREER = E.ACAD_CAREER AND B.ACAD_PLAN = E.ACAD_PLAN ), SYSADM.PS_STDNT_CAR_TERM C 
  WHERE ( A.EFFDT = 
        (SELECT MAX(A_ED.EFFDT) FROM SYSADM.PS_ACAD_PROG A_ED 
        WHERE A.EMPLID = A_ED.EMPLID 
          AND A.ACAD_CAREER = A_ED.ACAD_CAREER 
          AND A.STDNT_CAR_NBR = A_ED.STDNT_CAR_NBR 
          AND A_ED.EFFDT <= SYSDATE) 
    AND A.EFFSEQ = 
        (SELECT MAX(A_ES.EFFSEQ) FROM SYSADM.PS_ACAD_PROG A_ES 
        WHERE A.EMPLID = A_ES.EMPLID 
          AND A.ACAD_CAREER = A_ES.ACAD_CAREER 
          AND A.STDNT_CAR_NBR = A_ES.STDNT_CAR_NBR 
          AND A.EFFDT = A_ES.EFFDT) 
     AND A.EMPLID = B.EMPLID 
     AND A.ACAD_CAREER = B.ACAD_CAREER 
     AND A.STDNT_CAR_NBR = B.STDNT_CAR_NBR 
     AND A.EFFSEQ = B.EFFSEQ 
     AND B.EFFDT = 
        (SELECT MAX(B_ED.EFFDT) FROM SYSADM.PS_ACAD_PLAN B_ED 
        WHERE B.EMPLID = B_ED.EMPLID 
          AND B.ACAD_CAREER = B_ED.ACAD_CAREER 
          AND B.STDNT_CAR_NBR = B_ED.STDNT_CAR_NBR 
          AND B_ED.EFFDT <= SYSDATE) 
    AND B.EFFSEQ = 
        (SELECT MAX(B_ES.EFFSEQ) FROM SYSADM.PS_ACAD_PLAN B_ES 
        WHERE B.EMPLID = B_ES.EMPLID 
          AND B.ACAD_CAREER = B_ES.ACAD_CAREER 
          AND B.STDNT_CAR_NBR = B_ES.STDNT_CAR_NBR 
          AND B.EFFDT = B_ES.EFFDT) 
     AND A.EMPLID = C.EMPLID 
     AND A.ACAD_CAREER = C.ACAD_CAREER 
     AND A.STDNT_CAR_NBR = C.STDNT_CAR_NBR 
     AND C.INSTITUTION = A.INSTITUTION 
     AND C.STRM = (SELECT Max( D.STRM) 
  FROM SYSADM.PS_STDNT_CAR_TERM D 
  WHERE C.EMPLID = D.EMPLID 
     AND C.ACAD_CAREER = D.ACAD_CAREER) 
     AND A.PROG_STATUS = 'AC' 
     AND A.EMPLID = '$emplid' )";
	 
	return rc_ps_execute_query_get_record($query);
}

//get all the cumulative result of student (cgpa, gpa, etc)
function rc_ps_get_cumulative($emplid, $strm = '')
{
	if($strm != '')
		$strmTxt = " AND strm = '$strm'";
	else
		$strmTxt = '';
	$query = "select A.emplid, B.name, strm, ACAD_PROG_PRIMARY, UNT_TAKEN_GPA, UNT_PASSD_GPA, GRADE_POINTS, CUR_GPA,
			TOT_TAKEN_GPA, TOT_PASSD_GPA, TOT_CUMULATIVE, TOT_GRADE_POINTS, CUM_GPA
			from SYSADM.PS_STDNT_CAR_TERM A INNER JOIN SYSADM.PS_NAMES_ENG B ON A.EMPLID = B.EMPLID
			where A.EMPLID = '$emplid'" . $strmTxt . " order by strm";
	if($strm != '')
		return rc_ps_execute_query_get_record($query);
	else
		return rc_ps_execute_query($query, false);
}

//delete attendance for late enrolled
//get all the student enrollment
function rc_ps_get_all_enrol($strm)
{
	$query = "select EMPLID, CLASS_NBR, STDNT_ENRL_STATUS, ENRL_ACTION_LAST, TO_CHAR(enrl_add_dt, 'DD-MON-YYYY') as ENRL_ADD_DT, TO_CHAR(STATUS_DT, 'DD-MON-YYYY') AS STATUS_DT, CRSE_GRADE_OFF 
			  from 
					sysadm.ps_stdnt_enrl 
			  where 
					strm = $strm AND CRSE_GRADE_OFF = ' '";	
	$rs = rc_ps_execute_query($query, false);
	return $rs;
}

//get the academic program given the acad_prog code
function rc_ps_get_acad_prog($acad_prog)
{
	$query = "select acad_prog, institution, descr, campus from SYSADM.PS_ACAD_PROG_TBL where acad_prog = '$acad_prog'";
	return rc_ps_execute_query_get_record($query);
}
//get the peoplesoft-logsis operation mapping
function rc_ps_operation_mapping($prog_status, $prog_action, $prog_reason)
{
	/*
		The PSCS/LOGSIS In-Out Cross-ref table is as follows:

		PROG_STATUS  PROG_ACTION  PROG_REASON  LOG_OPER  DESCRIPTION
		===========  ===========  ===========  ========  ==============
		AC           PLNC                             -  Plan change
		AC           PRGC                             -  Program change
		---------------------------------------------------------------
		AC           MATR                             9  Admission
		AC           MATR         ADMI                9  Admission
		---------------------------------------------------------------
		AC           ACTV                             8  Reactivation
		AC           RADM                             8  Reactivation
		AC           RADM         DISM                8  Reactivation
		AC           RADM         SUSP                8  Reactivation
		AC           RADM         WADM                8  Reactivation
		AC           RLOA                             8  Reactivation
		---------------------------------------------------------------
		CM           COMP                             1  Graduation
		CM           COMP         GRAD                1  Graduation
		---------------------------------------------------------------
		CN           WADM         NOSH                7  No-show
		---------------------------------------------------------------
		DC           DISC                            12  CW-PR
		DC           DISC         PERS               12  CW-PR
		DE           DISC         DEAT                2  Deceased
		---------------------------------------------------------------
		DM           DISM                             6  SW-AF
		DM           DISM         ACFA                6  SW-AF
		DM           DISM         EXPL                3  CW-Expelled
		---------------------------------------------------------------
		LA           LEAV                             4  SW-PR
		LA           LEAV         PERS                4  SW-PR
		---------------------------------------------------------------
		SP           SPND                             4  SW-PR
		SP           SPND         ABSE                5  SW-Absenteeism
		SP           SPND         ACFA                6  SW-AF
		---------------------------------------------------------------
	*/
	$code = array();
	$code['AC_PLNC'] = '';
	$code['AC_PRGC'] = '';
	$code['AC_MATR'] = 9;
	$code['AC_MATR_ADMI'] = 9;
	$code['AC_ACTV'] = 8;
	$code['AC_RADM'] = 8;
	$code['AC_RADM_DISM'] = 8;
	$code['AC_RADM_SUSP'] = 8;
	$code['AC_RADM_WADM'] = 8;
	$code['AC_RLOA'] = 8;
	$code['CM_COMP'] = 1;
	$code['CM_COMP_GRAD'] = 1;
	$code['CN_WADM_NOSH'] = 7;
	$code['DC_DISC'] = 12;
	$code['DC_DISC_PERS'] = 12;
	$code['DE_DISC_DEAT'] = 2;
	$code['DM_DISM'] = 6;
	$code['DM_DISM_ACFA'] = 6;
	$code['DM_DISM_EXPL'] = 3;
	$code['LA_LEAV'] = 10;
	$code['LA_LEAV_PERS'] = 4;
	$code['SP_SPND'] = 4;
	$code['SP_SPND_ABSE'] = 5;
	$code['SP_SPND_ACFA'] = 6;

	$ps_code = trim($prog_status);
	if($prog_action != '')
		$ps_code = $ps_code . '_' . $prog_action;
	if($prog_reason != '')
		$ps_code = $ps_code . '_' . $prog_reason;
	
	if(!isset($code[$ps_code]))
		return '';
	else
		return $code[$ps_code];
	
}
//get the operation that happen to a student by a cut off date
function rc_ps_get_operation($cut_off_date)
{
	$query = "
		select 
			emplid,
			campus,
			acad_prog,
			prog_status,
			prog_action,
			prog_reason,
			stdnt_car_nbr,
			to_char(effdt,'YYYY-MM-DD') as dt_sort,
			to_char(action_dt,'DD-MON-YYYY') as action_dt,
			to_char(effdt,'DD-MON-YYYY') as effective_dt,
			to_char(scc_row_upd_dttm, 'DD-MON-YYYY') as updated_dt
		from 
			sysadm.ps_acad_prog d
		where 
			effdt = (select max(d_ed.effdt)
							from sysadm.ps_acad_prog d_ed
						   where d.emplid        = d_ed.emplid
							 and d.acad_career   = d_ed.acad_career
							 and d.stdnt_car_nbr = d_ed.stdnt_car_nbr
							 and d_ed.effdt     <= sysdate
							 and d_ed.prog_action not in('PRGC','PLNC', 'DATA'))
			and effseq = (select max(d_es.effseq)
							 from sysadm.ps_acad_prog d_es
							where d.emplid        = d_es.emplid
							  and d.acad_career   = d_es.acad_career
							  and d.stdnt_car_nbr = d_es.stdnt_car_nbr
							  and d.effdt         = d_es.effdt
							  and d_es.prog_action not in('PRGC','PLNC', 'DATA'))
			and effdt >= TO_DATE('1-OCTOBER-2018', 'DD-MON-YYYY')
			and campus in('YIC','YUC-F','YUC-M','HIEI')
			and prog_action not in('PRGC','PLNC')
		order by 
			emplid, stdnt_car_nbr desc, dt_sort desc, scc_row_upd_dttm desc
	";
	$rs = rc_ps_execute_query($query, false);
	return $rs;	
}

////////////////////////////////////////////////////////
//////////////DORMITORY/////////////////////////////////
////////////////////////////////////////////////////////

//Get list of students who are checked in to room
function rc_ps_room_get_ci_student()
{
	$query = "select * from SYSADM.PS_A_STD_ASIGN_APP where end_date is null and A_APPOINT_STATUS = 'CI'";
	$rs = rc_ps_execute_query($query, false);
	return $rs;	
}

//given an id, get the student room
function rc_ps_room_get_student_room($emplid)
{
	$query = "select NATIONAL_ID, FACILITY_ID, TO_CHAR(START_DT, 'DD-MM-YYYY') as start_date from SYSADM.PS_A_STD_ASIGN_APP where end_date is null and A_APPOINT_STATUS = 'CI' and emplid = '$emplid'";
	$rs = rc_ps_execute_query_get_record($query);
	return $rs; //if not found, then false		
}

function rc_ps_room_checkout_student($emplid)
{
	$now = date('d-M-Y', time());
	$query = "update SYSADM.PS_A_STD_ASIGN_APP set end_date = TO_DATE('$now', 'dd-mon-yyyy'), A_APPOINT_STATUS = 'CO' where end_date is null and A_APPOINT_STATUS = 'CI' and emplid = '$emplid'";
	rc_ps_execute($query);

}

function rc_ps_room_single_student()
{
	$query = "SELECT B.FACILITY_ID, B.BLDG_CD, B.ROOM, B.DESCR, B.EFF_STATUS, B.FACILITY_TYPE, TO_CHAR(SYSDATE,'YYYY-MM-DD') as A, TO_CHAR(SYSDATE,'YYYY-MM-DD') as B
  FROM SYSADM.PS_A_ROOM_WITH_1_S A, SYSADM.PS_FACILITY_TBL B
  WHERE ( B.FACILITY_ID = A.FACILITY_ID
     AND B.EFFDT =
        (SELECT MAX(B_ED.EFFDT) FROM SYSADM.PS_FACILITY_TBL B_ED
        WHERE B.SETID = B_ED.SETID
          AND B.FACILITY_ID = B_ED.FACILITY_ID
          AND B_ED.EFFDT <= SYSDATE) )";
	$rs = rc_ps_execute_query($query, false);
	return $rs;	
}

//get all dormitory
function rc_ps_get_dormitory()
{
	$query = "select * from SYSADM.PS_FACILITY_TBL A where A.FACILITY_TYPE = 'DORM'
     AND EFFDT =
        (SELECT MAX(B_ED.EFFDT) FROM SYSADM.PS_FACILITY_TBL B_ED
        WHERE A.SETID = B_ED.SETID
          AND A.FACILITY_ID = B_ED.FACILITY_ID
          AND B_ED.EFFDT <= SYSDATE)
	";
	$rs = rc_ps_execute_query($query, false);
	return $rs;	
}
//get all the cumulative result of student (cgpa, gpa, etc)
function rc_ps_get_facility($facility_id)
{
//	$query = "select * from SYSADM.PS_FACILITY_TBL where facility_id = '$facility_id' order by facility_id, effdt desc";
	$query = "select * from SYSADM.PS_FACILITY_TBL A where facility_id = '$facility_id'
     AND EFFDT =
        (SELECT MAX(B_ED.EFFDT) FROM SYSADM.PS_FACILITY_TBL B_ED
        WHERE A.SETID = B_ED.SETID
          AND A.FACILITY_ID = B_ED.FACILITY_ID
          AND B_ED.EFFDT <= SYSDATE)
	";
	return rc_ps_execute_query_get_record($query);

}

//status A = active, I = inactive
function rc_ps_make_room_active($facility_id, $status)
{
	$query = "update SYSADM.PS_FACILITY_TBL A set EFF_STATUS = '$status' where facility_id = '$facility_id'
     AND EFFDT =
        (SELECT MAX(B_ED.EFFDT) FROM SYSADM.PS_FACILITY_TBL B_ED
        WHERE A.SETID = B_ED.SETID
          AND A.FACILITY_ID = B_ED.FACILITY_ID
          AND B_ED.EFFDT <= SYSDATE)
	";
	rc_ps_execute($query);	
}

//get the latest cumulative and credit of student
function rc_ps_get_cumulative_hour($emplid)
{
	$query = "
		select emplid, strm, ACAD_PROG_PRIMARY, TOT_TAKEN_GPA, TOT_PASSD_GPA, TOT_CUMULATIVE, TOT_GRADE_POINTS, CUM_GPA
		from SYSADM.PS_STDNT_CAR_TERM 
		WHERE emplid = '$emplid'
		ORDER BY strm desc
	";
	return rc_ps_execute_query_get_record($query);
}

