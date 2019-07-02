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

// This is the library for database access to external system (Peoplesoft). It includes all the functions to retrieve or update LOGSIS 
defined('MOODLE_INTERNAL') || die();
require_once 'dblib.php'; //$PSDB and $LSDB are global variable automatically created once it is included. n function use global $PSDB and $LSDB

$LSDB = rc_get_logsisdb(); //initialize peoplesoft database connection

//////standard functions//////////////
function rc_ls_execute_query($query, $recordset, $obj = false)
{
	global $CFG;
	$LSDB = rc_get_logsisdb(); //initialize peoplesoft database connection
	$rs = $LSDB->Execute($query); //normal execute and return a record set
	if (!$rs) 
	{
		if($CFG->production)
			return 'Error executing query due to connection error';
		else	
			return rc_query_error($query);
	}
	return rc_ls_return_value($rs, $recordset, $obj);
}

function rc_ls_return_value($rs, $recordset, $obj)
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
function rc_ls_execute_query_get_field($query, $field)
{
	$rec = rc_ls_execute_query($query, false, false);
	if($rec)
	{
		foreach($rec as $row)
			return $row[$field];
	}
	return '';
}

//obtain a single record
function rc_ls_execute_query_get_record($query)
{
	$rec = rc_ls_execute_query($query, false, false);
	if($rec)
	{
		foreach($rec as $row)
			return $row;
	}
	return false;
}

//just execute a query with no return value. Usually for insert or updated
function rc_ls_execute($query)
{
	global $LSDB;
	return $LSDB->Execute($query); //normal execute and return a record set
}
/////end of standard functions/////

//////custom functions starts here///////////////
function rc_ls_get_app_id($id)
{
	$query = "select app_id from logsis2.si_app_admission where id = '$id' order by semester desc";	
	return rc_ls_execute_query_get_field($query, 'APP_ID');	
}

//get single record, the max
function rc_ls_get_stu_info($id)
{
	$query = "select * from logsis2.si_app_admission where id = '$id' order by semester desc";	
	return rc_ls_execute_query_get_record($query);
}

//get all records
function rc_ls_get_stu_info_all($id)
{
	$query = "select * from logsis2.si_app_admission where id = '$id' order by semester desc";	
	return rc_ls_execute_query($query);
}

//student need to fill up iban number
function rc_ls_need_fill_iban($id)
{
	global $CFG;
	$LSDB = rc_get_logsisdb(); //initialize peoplesoft database connection
	$query = "select * from logsis2.YIC_PAY_BANK_ACCOUNTS where id = '$id'";	
	$rs = $LSDB->Execute($query); //normal execute and return a record set
	$rec = rc_ls_return_value($rs, false, false);
	if($rec) //student already has iban. So no need to fill
		return false;
	else //make sure he is not parallel student and he must be new student, i.e. enrol in the semester
	{
		$query = "select a.id, a.semester, b.parallel from logsis2.si_app_admission a inner join yic_stu_info b on a.id = b.id where a.id = '$id'";
		$rs = $LSDB->Execute($query); //normal execute and return a record set
		$rec = rc_ls_return_value($rs, false, false);
		if($rec) //student already has iban. So no need to fill
		{
			foreach($rec as $stu)
				break;
			$logsis_sem = $stu['SEMESTER']; 
			$ps_sem = $logsis_sem[0] . $logsis_sem[2] . $logsis_sem[3] . $logsis_sem[4];
			if($ps_sem == $CFG->semester && $stu['PARALLEL'] != '1')
				return true;
			else
				return false;
		}
		else //not found record, ignore
			return false;
	}
//	return rc_ls_execute_query_get_record($query);
}

//if all_dn is true, then we have to add him to the salary suspend directive table in the case of add
//in the case of remove, then we have to remove him
function rc_ls_add_wf_logsis($rec, $status, $all_dn)
{
	global $CFG, $DB;
	
	$emplid = $rec->emplid;
	if($status == 1) //add
	{
		$stu_info = rc_ls_get_stu_info($emplid);
		$today = date('d-M-Y', $rec->date_generated); //standardize the date
		$hijri = rc_to_hijrah($today, "Y/m/d");
		$app_id = $stu_info['APP_ID'];
		$stu_cat = $stu_info['STU_CAT'];
		$e_name = $stu_info['E_OFFICIAL_NAME'];
		$a_name = $stu_info['A_OFFICIAL_NAME'];
		
		$ps_course = rc_ps_get_crse_id($rec->theory_class_nbr);
	
		$query = "insert into logsis2.yic_wf_attendance(APP_ID, DIRECTIVE_NO, TRN_DATE, TRN_HDATE, P_PERC, P_RASHEDI, ID, E_NAME, A_NAME, STU_CATEGORY, SEMESTER, 
														COURSE_NUM, E_ABR_NAME, COURSE, ABSENCE_DATE, PERIOD_NUM, ABSENCE_PERIODS, PERC_PERIODS, ACTUAL, SELECTED, REMARKS) 
				   values('$app_id', '$rec->cut_off_week', TO_DATE('$today', 'DD-MON-YYYY'), '$hijri', '15', '45', '$emplid', '$e_name', '$a_name', '$stu_cat', '$CFG->lsemester', '$rec->catalog_nbr', '$ps_course->descr', '$ps_course->crse_id', TO_DATE('$today', 'DD-MON-YYYY'), '1', '1', '$rec->percent', '$rec->percent', '1', '')";	
		rc_ls_execute($query);
		if($all_dn) //all dn is true
		{
			//first, check to make sure that the record is not in logsis
			$query = "select logsis2.yic_drop_attendance where id = '$emplid' AND directive_no = '$rec->cut_off_week' AND semester = '$CFG->lsemester'";
			if(rc_ls_execute_query_get_record($query) === false)
			{
				$query = "insert into logsis2.yic_drop_attendance(APP_ID, DIRECTIVE_NO, TRN_DATE, TRN_HDATE, P_PERC, P_RASHEDI, ID, E_NAME, A_NAME, STU_CATEGORY, SEMESTER, 
																DROPPED, REMARKS) 
						   values('$app_id', '$rec->cut_off_week', TO_DATE('$today', 'DD-MON-YYYY'), '$hijri', '15', '45', '$emplid', '$e_name', '$a_name', '$stu_cat', '$CFG->lsemester', '1', '')";	
				rc_ls_execute($query);
			}
			rc_ls_remove_dn_all($rec);
		}
	}
	else //delete
	{
		$query = "delete from logsis2.yic_wf_attendance where id = '$emplid' and directive_no = '$rec->cut_off_week' and course_num = '$rec->catalog_nbr' and semester = '$CFG->lsemester'";
		rc_ls_execute($query);
		//for delete, we always execute all DN because any delete will result in removal of All DN
		$query = "delete from logsis2.yic_drop_attendance where id = '$emplid' and directive_no = '$rec->cut_off_week' and semester = '$CFG->lsemester'";
		rc_ls_execute($query);
	}
}

//function to just update the all dn table without putting into the DN list
function rc_ls_add_all_dn($rec)
{
	global $CFG, $DB;
	
	$emplid = $rec->emplid;
	$stu_info = rc_ls_get_stu_info($emplid);
	$today = date('d-M-Y', $rec->date_generated); //standardize the date
	$hijri = rc_to_hijrah($today, "Y/m/d");
	$app_id = $stu_info['APP_ID'];
	$stu_cat = $stu_info['STU_CAT'];
	$e_name = $stu_info['E_OFFICIAL_NAME'];
	$a_name = $stu_info['A_OFFICIAL_NAME'];
	
	$ps_course = rc_ps_get_crse_id($rec->theory_class_nbr);

	//first, check to make sure that the record is not in logsis
	$query = "select logsis2.yic_drop_attendance where id = '$emplid' AND directive_no = '$rec->cut_off_week' AND semester = '$CFG->lsemester'";
	if(rc_ls_execute_query_get_record($query) === false)
	{
		$query = "insert into logsis2.yic_drop_attendance(APP_ID, DIRECTIVE_NO, TRN_DATE, TRN_HDATE, P_PERC, P_RASHEDI, ID, E_NAME, A_NAME, STU_CATEGORY, SEMESTER, 
														DROPPED, REMARKS) 
				   values('$app_id', '$rec->cut_off_week', TO_DATE('$today', 'DD-MON-YYYY'), '$hijri', '15', '45', '$emplid', '$e_name', '$a_name', '$stu_cat', '$CFG->lsemester', '1', '')";	
		rc_ls_execute($query);
	}
}

//when a student is in all dn, they must be removed from the regular dn list
function rc_ls_remove_dn_all($rec)
{
	global $DB, $CFG;
	$emplid = $rec->emplid;
	$query = "delete from logsis2.yic_wf_attendance where id = '$rec->emplid' and directive_no = '$rec->cut_off_week' and semester = '$CFG->lsemester'";
	rc_ls_execute($query);
	
}

//get grade letters
function rc_ls_get_gradeletter()
{
	$query = "select code, e_name from logsis2.si_grade_letter";	
	$rec = rc_ls_execute_query($query, false, true);
	$arr = array();
	foreach($rec as $r)
		$arr[$r->CODE] = $r->E_NAME;
	return $arr;
}

//given a course code, try to find the matching course in logsis
function rc_ls_get_course($course_code)
{
	$query = "select * from logsis2.si_course where course_num = '$course_code'";	
	return rc_ls_execute_query_get_record($query);
}

function rc_ls_get_student_grade($emplid, $course_code, $semester)
{
	global $CFG, $DB;
	
	$stu_infos = rc_ls_get_stu_info_all($emplid);
	foreach($stu_infos as $stu_info)
	{
		$app_id = $stu_info['APP_ID'];		
		$query = "select * from logsis2.si_taken where app_id = '$app_id' and semester = $semester and course = '$course_code'";	
		$x = rc_ls_execute_query_get_record($query, false, true);
		if($x)
			return $x;
	}
	return false;
}

function rc_ls_update_student_grade($si_taken_code, $ls_grade)
{
	$query = "update logsis2.si_taken set grade = $ls_grade where code = '$si_taken_code'";	
	rc_ls_execute($query);	
}

function rc_ls_get_coop_courses()
{
	$query = "select b.code, course_num, c.e_name as group_name, b.e_name as course_name from logsis2.si_course_in_grp a inner join si_course b on a.course = b.code inner join logsis2.si_course_group c on a.course_group = c.code WHERE (course_group = 1 OR course_group = 2)";
	$rs = rc_ls_execute_query($query, true, false);
	$result = array();
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		$result[$rec['CODE']] = $rec;
		$rs->MoveNext();
	}
	return $result;
}

//get from logsis2.yic_co_okay
function rc_ls_get_coop_student()
{
	$query = "select * from logsis2.yic_co_okay where CUM_NET_GPA is null";
	$rs = rc_ls_execute_query($query, true, false);
	$result = array();
	$count = 1;
	while(!$rs->EOF) 
	{
		$rec = $rs->fields;
		$result[$count] = $rec;
		$rs->MoveNext();
		$count++;
	}
	return $result;
}

function rc_ls_get_current_semester()
{
	$query = "select semester from si_logsis_module where form_name = 'REGM01'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r->SEMESTER;
	return '';
}
//update the cgpa and total credit in yic_co_okay
function rc_ls_update_coop_student($app_id, $cum_gpa, $tot_credit)
{
	$query = "update logsis2.yic_co_okay set CUM_NET_GPA = '$cum_gpa', CUM_PASS_CRD = '$tot_credit' where app_id = '$app_id'";
	rc_ls_execute($query);
}

//save the cgpa
function rc_ls_save_cgpa($data)
{
	//before we save, we must delete the existing one
	$query = "delete from logsis2.si_cumulative where app_id = '$data->app_id' and semester = '$data->semester'";
	rc_ls_execute($query);
	$query = "INSERT INTO logsis2.si_cumulative(
		APP_ID,
		SEMESTER,
		SEM_REG_CRD,
		SEM_NET_CRD,
		SEM_PASS_CRD,
		SEM_NET_POINTS,
		SEM_GPA,
		CUM_NET_CRD,
		CUM_PASS_CRD,
		CUM_NET_POINTS,
		CUM_NET_GPA,
		SEM_MCRD,
		SEM_MPOINTS,
		SEM_MGPA,
		SEM_PASS_MCRD,
		CUM_MCRD,
		CUM_MPOINTS,
		CUM_MGPA,
		CUM_PASS_MCRD
	) values(
		'$data->app_id',
		'$data->semester',
		'$data->semester_credit',
		'$data->q_credit',
		'$data->semester_earned_credit',
		'$data->sem_point',
		'$data->gpa',
		'$data->qhrs',
		'$data->ehrs',
		'$data->qpts',
		'$data->cgpa',
		'$data->sem_m_credit',
		'$data->sem_m_point',
		'$data->sem_mgpa',
		'$data->sem_m_earned_credit',
		'$data->m_cum_credits',
		'$data->m_cum_points',
		'$data->mgpa',
		'$data->m_cum_earned'
		)
		";
	rc_ls_execute($query);	
}

function rc_ls_get_yic_stu_info($app_id)
{
	$query = "select * from yic_stu_info where app_id = '$app_id'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return null;
}

function rc_ls_get_yic_stu_info_id($id)
{
	$query = "select * from yic_stu_info where id = '$id' order by app_id desc";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return null;
}

function rc_ls_get_operation($code)
{
	$query = "select * from si_operation where dsp_code = '$code'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return '';
}
///////////////////////////////////////////////////////////////////
//////////////////START OF IN OUT OPERATION////////////////////////
///////////////////////////////////////////////////////////////////

//check if a given date is effective
function rc_ls_is_effective($effective_date)
{
	$now = strtotime(date('d-M-Y', time())); //get today's date without time
	$effdt = strtotime($effective_date);
	if($now >= $effdt)
		return true;
	else
		return false;
}
//get the student operation
function rc_ls_get_student_operation($app_id, $semester)
{
	$query = "
		select 
			APP_ID,
			SEMESTER,
			OPERATION,
			ACTION_DATE, 	
			OPERATION_TYPE, 
			to_char(action_date,'YYYY-MM-DD') as action_date_sort
		from 
			logsis2.si_stu_operation 
		where 
		app_id = '$app_id' 
		order by 
			action_date_sort desc";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return false;
}

function rc_ls_validate_si_stu_major($app_id, $semester)
{
	$query = "select * from logsis2.si_stu_major where app_id = '$app_id' and semester = '$semester'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r) //if the student has record, no need to do anything
	{
		return;
	}
	//else create an si_stu_major record
	$query = "select * from logsis2.si_stu_major where app_id = '$app_id' order by semester desc"; //get the max semester
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r) //if have something, then do it
	{
		$query = "
			INSERT INTO LOGSIS2.SI_STU_MAJOR (
				APP_ID
				,SEMESTER
				,MAJPROG_ID
				,FACULTY_ID
				,STUDY_BASIS
				,IS_ADM_SEM
				,REG_CREDIT_LOAD_TYPE
				,FAC_CLASS
				,STU_CATEGORY
				,BRANCH
				,LANGUAGE_TRACK
				,PLAN_SEMESTER
				,ACADEMIC_STATUS
				,ID
				,E_NAME
				,A_NAME
				,IS_REG_CLEAR
				,IS_SAC_CLEAR
				,IS_GRADE_CLEAR
				,OP_REG_MES
				,IS_GRAD_SEM
				,DUMMY_FIELD
				,GENDER
				,FORCED
			)
			VALUES (
				'$r->APP_ID'
				,'$semester'
				,'$r->MAJPROG_ID'
				,'$r->FACULTY_ID'
				,'$r->STUDY_BASIS'
				,'N'
				,'$r->REG_CREDIT_LOAD_TYPE'
				,'$r->FAC_CLASS'
				,'$r->STU_CATEGORY'
				,'$r->BRANCH'
				,'$r->LANGUAGE_TRACK'
				,'$r->PLAN_SEMESTER'
				,'$r->ACADEMIC_STATUS'
				,'$r->ID'
				,'" . str_replace("'", "''", $r->E_NAME) . "'
				,'" . str_replace("'", "''", $r->A_NAME) . "'
				,'N'
				,'$r->IS_SAC_CLEAR'
				,'N'
				,'$r->OP_REG_MES'
				,'N'
				,'$r->DUMMY_FIELD'
				,'$r->GENDER'
				,'$r->FORCED'			
			)
		";
		rc_ls_execute($query);			
		break;
	}	
}
//action_date is the effective date from ps
function rc_ls_insert_operation($app_id, $operation, $action_date, $opt, $in_out)
{
	$semester = rc_ls_get_current_semester();
	rc_ls_validate_si_stu_major($app_id, $semester); //have to validate to make sure student has si_stu_major record
	//insert an in record in si_stu_operation
	$query = "INSERT INTO logsis2.si_stu_operation(
		APP_ID,
		SEMESTER,
		OPERATION,
		ACTION_DATE,
		OPERATION_TYPE
	) values(
		'$app_id',
		'$semester',
		'$operation',
		TO_DATE('$action_date', 'DD-MON-YYYY'),
		'$in_out'
		)
		";
	$success = rc_ls_execute($query);	
	if($success)
	{		
		//insert an in record in si_app_entry_exit
		$query = "INSERT INTO logsis2.si_app_entry_exit(
			CODE,
			APP_ID,
			ENTRY_EXIT,
			SEMESTER,
			ACTION_DATE,
			OPERATION,
			OPERATION_TYPE
		) values(
			logsis2.si_app_entry_exit_seq.NEXTVAL,
			'$app_id',
			'$opt->ENTRY_EXIT',
			'$semester',
			TO_DATE('$action_date', 'DD-MON-YYYY'),
			'$operation',
			'$in_out'
			)
			";
		rc_ls_execute($query);		
		return true;
	}
	else
	{
		print_object("Something went wrong for " . $app_id);
		return false;
	}
}

//make a student from inactive to active
function rc_ls_make_student_active($app_id, $operation, $effective_date)
{
	$opt = rc_ls_get_operation($operation); //get the operation
	//insert an operation
	$success = rc_ls_insert_operation($app_id, $operation, $effective_date, $opt, 'I'); //I for in
	if($success)
	{
		//update payroll suspend
		$query = "select * from logsis2.si_pay_suspend where app_id = '$app_id' and pay_suspend_reason = 0 and till_date is null";
		$rec = rc_ls_execute_query($query, false, true);
		foreach($rec as $r) //if there is record, then it will enter the loop, so we do the update. If not, do nothing
		{
			$query = "update logsis2.si_pay_suspend set till_date = TO_DATE('$effective_date', 'DD-MON-YYYY') WHERE code = '$r->CODE'";
			rc_ls_execute($query);		
		}
		return true;
	}
	return false;
}
	
//make a student from active to not active
function rc_ls_make_student_not_active($app_id, $operation, $effective_date)
{
	$opt = rc_ls_get_operation($operation); //get the operation
	//the date is the next 1 day to effective date
	$next_day =  strtotime($effective_date . ' +1 day');
	$next_day_text = date('d-M-Y', $next_day);
	//insert an operation
	$success = rc_ls_insert_operation($app_id, $operation, $effective_date, $opt, 'O'); //O for out
	if($success)
	{
		//insert into payroll suspend
		$query = "
			insert into logsis2.si_pay_suspend (
				CODE,
				APP_ID,
				FROM_DATE,
				E_REASON,
				A_REASON,
				PAY_SUSPEND_REASON,
				IS_SYSTEM
			)
			values (
				logsis2.si_pay_suspend_seq.NEXTVAL,
				'$app_id',
				TO_DATE('$next_day_text', 'DD-MON-YYYY'),
				'$opt->F_NAME',
				'$opt->A_NAME',
				'0',
				'Y'
			)
			";
		rc_ls_execute($query);	

		//update payroll suspend
		$query = "select * from logsis2.si_pay_suspend where app_id = '$app_id' and pay_suspend_reason = 41 and till_date is null";
		$rec = rc_ls_execute_query($query, false, true);
		foreach($rec as $r) //if there is record, then it will enter the loop, so we do the update. If not, do nothing
		{
			//check yic_stu_info if it is parallel
			$stu_info = rc_ls_get_yic_stu_info($app_id);
			if($stu_info->PARALLEL == 1) //if parallel, close the suspension by filling up the till date
			{
				//for parallel student, they operate in opposite. An active parallel student will always have the pay suspended because they don't receive any pay
				//so when a parallel student become not active, we remove the suspension
				$query = "update logsis2.si_pay_suspend set till_date = TO_DATE('$effective_date', 'DD-MON-YYYY') WHERE code = '$r->CODE'";
				rc_ls_execute($query);					
			}
		}	
	}
}

function rc_ls_check_operation_exist($app_id, $operation)
{
	$semester = rc_ls_get_current_semester();
	$query = "
		select 
			APP_ID,
			SEMESTER,
			OPERATION,
			ACTION_DATE, 	
			OPERATION_TYPE, 
			to_char(action_date,'YYYY-MM-DD') as action_date_sort
		from 
			logsis2.si_stu_operation 
		where 
		app_id = '$app_id' and semester = '$semester' and operation_type = '$operation'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return true;
	return false;
}

////convert to parallel or regular
function rc_ls_app_id($student_id)
{
	$query = "select max(app_id) as app_id from si_app_admission where id = '$student_id'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r->APP_ID;
	return false;
}
function rc_ls_active_student($student_id)
{
	$app_id = rc_ls_app_id($student_id);
	if($app_id)
	{
		$query = "
			select logsis2.si_app_active('$app_id', sysdate) as active from dual
		";
		$rec = rc_ls_execute_query($query, false, true);
		foreach($rec as $r)
			return $r->ACTIVE;
		return false;		
	}
	return false;
}

function rc_ls_parallel_status($student_id)
{
	$query = "select parallel from yic_stu_info where id = '$student_id'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r->PARALLEL;
	return false;	
}

//set the parallel status. 0 = regular, 1 = parallel
function rc_ls_change_parallel_status($student_id, $status)
{
/*	$query = "
	update yic_stu_info 
		set parallel = '$status'
		where id = '$student_id'
	";
*/
	$query = "
	update yic_stu_info ys
	   set ys.parallel = $status
	where ys.id = '$student_id'
	   and ys.app_id = (select max(yx.app_id)
                      from yic_stu_info yx
                     where yx.civil_id = ys.civil_id)
	and logsis2.si_app_active(ys.app_id,sysdate) = 'Y'
	";
	rc_ls_execute($query);  
}

//if it is for regular to parallel, the operation is 41
function rc_ls_suspend_pay($app_id, $effective_date)
{	
	//Check if student already has a record
	$query = "select * from logsis2.si_pay_suspend where app_id = '$app_id' and pay_suspend_reason = 41 and till_date is null";
	$rec = rc_ls_execute_query($query, false, true);
	$found = false;
	foreach($rec as $r) //if there is record, then it will enter the loop
	{
		$found = true; //if found, means student already has pay suspension. So no need to do anything
	}	
	if(!$found) //if no record, we create one. We make sure that TILL_DATE is null to indicate that the pay is suspended as long as the TILL_DATE is null
	{
		//insert into payroll suspend
		$query = "
			insert into logsis2.si_pay_suspend (
				CODE,
				APP_ID,
				FROM_DATE,
				E_REASON,
				PAY_SUSPEND_REASON,
				IS_SYSTEM
			)
			values (
				logsis2.si_pay_suspend_seq.NEXTVAL,
				'$app_id',
				TO_DATE('$effective_date', 'DD-MON-YYYY'),
				'No-Stipend Decision',
				'41',
				'N'
			)
			";
		rc_ls_execute($query);	
		
	}
}

function rc_ls_activate_pay($app_id, $effective_date)
{
	//Check if student already has a record
	$query = "select * from logsis2.si_pay_suspend where app_id = '$app_id' and pay_suspend_reason = 41 and till_date is null";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r) //if there is record, then it will enter the loop, so we do the update. It means student has a suspension
	{
		$query = "update logsis2.si_pay_suspend set till_date = TO_DATE('$effective_date', 'DD-MON-YYYY') WHERE code = '$r->CODE'"; //update the till date to lift the suspension
		rc_ls_execute($query);					
	}
	//if student does not have a record, we simply ignore it
}

function rc_ls_hijrah()
{
	
}
function rc_ls_student_ifadah($id)
{
/*
	//query with hijrah date
	$query = "
		SELECT sm.ID, sm.app_id, sm.a_name, 
			   NVL(SUBSTR(A_MAJ_PROG_NAME,4,LENGTH(A_MAJ_PROG_NAME)), 'السنة التحضيرية') as major,
			   DECODE(STU_CATEGORY,1,  'كلية ينبع الصناعية',
                      21, 'معهد ينبع التقني', 
                      71, 'كلية ينبع الجامعية',
                      72, 'كلية ينبع الجامعية', 
                      77, 'معهد المطاط', ' ') CAMPUS,			   
			   v.PROGRAM, sm.SEMESTER, decode(v.PROGRAM, 21, 'الجامعية المتوسطة', 
						  21, 'الجامعية المتوسطة', 
						  22, 'الجامعية المتوسطة', 
						  41, 'الدبلوم (سنتين)',
						  64, 'الدبلوم (سنتين)',
						 244, 'الدبلوم',
						  42, 'البرنامج الخاص',
						  62, 'البرنامج الخاص',
						 123, 'البرنامج الخاص',
						  63, 'البكالوريوس',
						 564, 'البكالوريوس',
						 103, 'البكالوريوس',
						 544, 'البكالوريوس') \"PROGRAM_A\",
			   SUBSTR(logsis2.HIJRAH(SYSDATE),7,4)||'/'||SUBSTR(logsis2.HIJRAH(SYSDATE),4,2)||'/'||SUBSTR(logsis2.HIJRAH(SYSDATE),1,2) HDATE,
			   'الدراسي '||s.a_name||' للعام '||TO_HYEAR||'/'||FR_HYEAR||' هـ ' as semester_id,
				TO_CHAR(SYSDATE, 'YYYY/MM/DD') as GDATE,
				E_MAJ_PROG_NAME
		FROM   SI_STU_MAJOR sm, logsis2.SI_V_MAJOR_PROGRAM v, logsis2.yic_si_semester s
		WHERE  SM.STU_CATEGORY IN(1, 71, 77) AND
			   sm.APP_ID = main.app_id('$id') and
			   SEMESTER = (SELECT MAX(SEMESTER) FROM SI_STU_MAJOR WHERE APP_ID = sm.app_id) and
			   v.MAJPROG_ID = sm.MAJPROG_ID and
			   s.sem = (select semester from logsis2.si_logsis_module where form_name = 'REGM01') and 
			   sm.semester = s.sem	
	";
*/	
	$query = "
		SELECT sm.ID, sm.app_id, sm.a_name, 
			   NVL(SUBSTR(A_MAJ_PROG_NAME,4,LENGTH(A_MAJ_PROG_NAME)), 'السنة التحضيرية') as major,
			   DECODE(STU_CATEGORY,1,  'كلية ينبع الصناعية',
                      21, 'معهد ينبع التقني', 
                      71, 'كلية ينبع الجامعية',
                      72, 'كلية ينبع الجامعية', 
                      77, 'المعهد العالي للصناعات المطاطية', ' ') CAMPUS,			   
			   v.PROGRAM, sm.SEMESTER, decode(v.PROGRAM, 21, 'الجامعية المتوسطة', 
						  21, 'الجامعية المتوسطة', 
						  22, 'الجامعية المتوسطة', 
						  41, 'الدبلوم (سنتين)',
						  64, 'الدبلوم (سنتين)',
						 244, 'الدبلوم',
						  42, 'البرنامج الخاص',
						  62, 'البرنامج الخاص',
						 123, 'البرنامج الخاص',
						  63, 'البكالوريوس',
						 564, 'البكالوريوس',
						 103, 'البكالوريوس',
						 544, 'البكالوريوس') \"PROGRAM_A\",
			   '-' HDATE,
			   'الدراسي '||s.a_name||' للعام '||TO_HYEAR||'/'||FR_HYEAR||' هـ ' as semester_id,
				TO_CHAR(SYSDATE, 'YYYY/MM/DD') as GDATE,
				E_MAJ_PROG_NAME
		FROM   SI_STU_MAJOR sm, logsis2.SI_V_MAJOR_PROGRAM v, logsis2.yic_si_semester s
		WHERE  SM.STU_CATEGORY IN(1, 71, 72, 77) AND
			   sm.APP_ID = main.app_id('$id') and
			   SEMESTER = (SELECT MAX(SEMESTER) FROM SI_STU_MAJOR WHERE APP_ID = sm.app_id) and
			   v.MAJPROG_ID = sm.MAJPROG_ID and
			   s.sem = (select semester from logsis2.si_logsis_module where form_name = 'REGM01') and 
			   sm.semester = s.sem	
	";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return false;	
}

function rc_ls_get_yic_semester($sem)
{
	$query = "select * from logsis2.yic_si_semester where sem = '$sem'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return false;	
}
//given an app_id, get the latest program of an active student. 
function rc_ls_get_student_program($app_id)
{
	$query = "
		SELECT sm.ID, sm.app_id, sm.a_name, stu_category, v.majprog_id, 
			   v.PROGRAM, v.E_PROG_NAME, v.program_type, sm.SEMESTER, E_MAJ_PROG_NAME
		FROM   SI_STU_MAJOR sm, logsis2.SI_V_MAJOR_PROGRAM v
		WHERE  SM.STU_CATEGORY IN(1, 71, 72, 77) AND
			   sm.APP_ID = '$app_id' and
			   sm.SEMESTER = (SELECT MAX(SEMESTER) FROM SI_STU_MAJOR WHERE APP_ID = sm.app_id) and
			   v.MAJPROG_ID = sm.MAJPROG_ID
		";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return false;
}

//return the student info if student qualified to graduate. If false, not qualified
function rc_ls_is_graduate($student_id)
{
	$app_id = rc_ls_app_id($student_id); //here the max app_id will be retrieved. So technically we will get his max program
	$stu_prog = rc_ls_get_student_program($app_id);
	if($stu_prog)
	{
		//first, we must check if the student is for associate or bachelor. Note that the same id can be for both. 
		if($stu_prog->PROGRAM_TYPE == 1) //1 = associate
		{
			$query = "
				select aa.id,
					   aa.app_id,
					   aa.a_name,
					   aa.e_name,
					   decode(m1.majprog_id,26,'ACCT',
											27,'MTRL',
											28,'OFFC',
										   107,'GEO',
											21,'EPT',
											22,'ELNIC',
											23,'ICT',
											24,'INSTN',
											25,'POCAT',
											29,'MNFG',
											30,'MAINT') major,
					   decode(stu_category,1,'YIC', 71, 'YUC Male', 72, 'YUC Female', 21, 'YTI', ' ') Campus,					   
					   m1.majprog_id,
					   vm.e_maj_name,
					   c1.sem_gpa      sem_gpa,
					   c1.cum_pass_crd cum_cre,
					   c1.cum_net_gpa  cum_gpa,
					   c1.cum_mgpa     maj_cgpa
				 from
					   si_cumulative      c1,
					   si_app_admission   aa,
					   si_stu_major       m1,
					   logsis2.si_v_major_program vm
				where
					   logsis2.si_app_active(c1.app_id,sysdate) = 'Y'
				  and  aa.app_id = '$app_id'
				  and  aa.app_id = c1.app_id
				  and  vm.majprog_id = m1.majprog_id
				  and  vm.program_type = 1 -- Associate
				  and  c1.semester = (select max(c2.semester)
										from si_cumulative c2
									   where c2.app_id = c1.app_id
										 and nvl(c2.cum_net_gpa,0) != 0)
				  and  m1.app_id = c1.app_id
				  and  m1.semester = c1.semester
				  and  (
					(c1.cum_pass_crd > 69 and m1.majprog_id in(26,28,107,21,22,24,25,29,30))
					or (c1.cum_pass_crd > 70 and m1.majprog_id in (23,26,27))
					)
				  and  c1.cum_net_gpa >= 2
				  and  c1.cum_mgpa    >= 2
			";
			
		}
		else if($stu_prog->PROGRAM_TYPE == 22) //22 = bachelor
		{
			$query = "
				select aa.id,
					   aa.app_id,
					   aa.a_name,
					   aa.e_name,
					   decode(m1.majprog_id,145, 'CET',  146, 'EPET', 147, 'MGMT',
											148, 'MET',  169, 'EET',  209, 'IT',   549, 'IET',
											703, 'APL',  704, 'ACS',  705, 'CE',
											707, 'MIS',  708, 'HRM',  709, 'MKTM',
											710, 'ACCT', 961, 'SCM',
											713, 'APL',  714, 'ACS',  715, 'CE',   716, 'ID',
											717, 'MIS',  718, 'HRM',  719, 'MKTM',
											720, 'ACCT', 971, 'SCM',
							  m1.majprog_id) major,
					   decode(stu_category,1,'YIC', 71, 'YUC Male', 72, 'YUC Female', 21, 'YTI', ' ') Campus,					   
					   m1.majprog_id,
					   vm.e_maj_name,
					   c1.sem_gpa      sem_gpa,
					   c1.cum_pass_crd cum_cre,
					   c1.cum_net_gpa  cum_gpa,
					   c1.cum_mgpa     maj_cgpa
				 from 
					   si_cumulative      c1,
					   si_app_admission   aa,
					   si_stu_major       m1,
					   logsis2.si_v_major_program vm
				where
				  logsis2.si_app_active(c1.app_id,sysdate) = 'Y'
				  and  aa.app_id = '$app_id'
				  and  aa.app_id = c1.app_id
				  and  vm.majprog_id = m1.majprog_id
				  and  vm.program_type = 22 -- Bachelor
				  and  c1.semester = (select max(c2.semester)
										from si_cumulative c2
									   where c2.app_id = c1.app_id
										 and nvl(c2.cum_net_gpa,0) != 0)
				  and  m1.app_id = c1.app_id
				  and  m1.semester = c1.semester
				  and  (
						(c1.cum_pass_crd >= (124) and m1.majprog_id in(708,709,710,718,719,720,961,971))
						 or
						(c1.cum_pass_crd >= (126) and m1.majprog_id in(707,717))         
						or
						(c1.cum_pass_crd >= (131) and m1.majprog_id = 716)
						 or
						(c1.cum_pass_crd >= (132) and m1.majprog_id = 147)
						 or
						(c1.cum_pass_crd >= (134) and m1.majprog_id in(704,705,714,715))
						 or
						(c1.cum_pass_crd >= (135) and m1.majprog_id in(703,713))
						 or
						(c1.cum_pass_crd >= (140) and m1.majprog_id in(145,146,169,209,549))
						 or
						(c1.cum_pass_crd >= (142) and m1.majprog_id = 148)
					   )
				  and  c1.cum_net_gpa >= 2
				  and  c1.cum_mgpa    >= 2
			";	
		}
		$rec = rc_ls_execute_query($query, false, true);
		foreach($rec as $r)
			return $r;
	}
	//for bachelor, if the student admitted before 2162, the credit is 70, if admitted on 2162 and onward, it will be 140
	return false;
}

//return the college in arabic based on student cat
function rc_ls_college_english($stu_cat)
{
	if($stu_cat == 1) //YIC
		return 'Yanbu Industrial College';
	else if($stu_cat == 21)
		return 'Yanbu Technical Institute';
	else if($stu_cat == 71)
		return 'Yanbu University College';
	else if($stu_cat == 72)
		return 'Yanbu University College';
	else if($stu_cat == 77)
		return 'High Institute of Elastomer Industries';
	else
		return '';	
}

//return the college in arabic based on student cat
function rc_ls_college_arabic($stu_cat)
{
	if($stu_cat == 1) //YIC
		return 'كلية ينبع الصناعية';
	else if($stu_cat == 21)
		return 'معهد ينبع التقني';
	else if($stu_cat == 71)
		return 'كلية ينبع الجامعية';
	else if($stu_cat == 72)
		return 'كلية ينبع الجامعية';
	else if($stu_cat == 77)
		return 'معهد المطاط';
	else
		return '';
	
}

function rc_ls_get_coop_stud($student_id, $semester)
{
	$query = "select * from logsis2.yic_coop_stud where id = '$student_id' and semester = '$semester'";
	$rec = rc_ls_execute_query($query, false, true);
	foreach($rec as $r)
		return $r;
	return false;	
}

function rc_ls_update_coop_stud($data)
{
	$query = "update logsis2.yic_coop_stud set 
		SUPERVISOR_NAME = '$data->supervisor_name', 
		EMAIL_COMPANY = '$data->email_company', 
		EMAIL_PERSONAL = '$data->email_personal', 
		PHONE_OFFICE = '$data->phone_office', 
		PHONE_MOBILE = '$data->phone_mobile',
		DATE_UPDATED = '$data->date_updated'	
	where id = '$data->student_id' and semester = '$data->semester'
	";
	rc_ls_execute($query);	
}
