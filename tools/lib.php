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

require_once $CFG->libdir.'/tcpdf/tcpdf.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Returns navigation controls (tabtree) to be displayed on cohort management pages
 *
 * @param context $context system or category context where cohorts controls are about to be displayed
 * @param moodle_url $currenturl
 * @return null|renderable
 */

function rc_tools_get_envelope_print_html($data)
{
	global $CFG, $DB;		
	$code = $data['code'];
	$customTitle = '';
	$sem = rc_output_format_semester($CFG->semester);
	$course = rc_get_course_by_code($code);
	$ps_course = rc_ps_get_crse_id($course->idnumber);
	$college = rc_get_campus_name($ps_course->campus);
	if($customTitle != '')
		$pageTitle = $customTitle;
	else
	{
		$semesterTitle = "";
		if($sem['semester'] == 1)
			$semesterTitle = $semesterTitle . "First Semester";
		else if($sem['semester'] == 2)
			$semesterTitle = $semesterTitle . "Second Semester";
		else if($sem['semester'] == 3)
			$semesterTitle = $semesterTitle . "Summer Semester";
		$semesterTitle = $semesterTitle . " " . $sem['year_full'] . "-" . $sem['year_full_n'] . " (" . $sem['hijri'] . "-" . $sem['hijri_n'] . ")";
		$pageTitle = '<p><span style="font-size:1.3em;font-weight:bold;">' . $college . "</span></p><br>";
		$pageTitle = $pageTitle . '<span style="font-size:1.2em;">SCHEDULING AND EXAMINATION UNIT</span>';
	}
	$department = rc_ps_get_department($ps_course->acad_org, 'DESCRFORMAL');
	$type = $data['type'];
	if($type == 1)
		$exam_type = 'Mid Term Examination';
	else
		$exam_type = 'Final Examination';
	$venue = $data['venue'];
	$comment = $data['comment'];
	$sql = "select * from {rc_exam_data} where course_code = '$code' and exam_type = $type AND semester = '$CFG->semester' order by venue";
	$rec = $DB->get_records_sql($sql);
	$count = 1;
	$total = count($rec);
	foreach($rec as $key => $r)
	{
		if($key == $venue)
			break;
		$count++;
	}
	$total_script = $r->class_size + $data['paper'];
	$course_duration = $data['hour'] . ' H ' . $data['minute'] . ' M';
	//proctor
	$sql = "select * from {rc_exam_proctor} where venue = '$r->venue' and day_raw = '$r->day_raw' and exam_session = '$r->exam_session' and exam_type = $r->exam_type";
	$proctors = $DB->get_records_sql($sql);
	$arr = array('1' => 'First', '2' => 'Second', '3' => 'Third');
	if(trim($data['coordinator']) != '') //has coordinator	
		$coordinator = $DB->get_record('user', array('idnumber' => $data['coordinator']));
	else
		$coordinator = false;
	if($coordinator)
		$coor = $coordinator->firstname . ' ' . $coordinator->lastname;
	else
		$coor = '';
	$html = '
		<table width="100%" border="1" cellspacing="1" cellpadding="5">
		  <tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="5">
				  <tr>
					<td width="15%"><img src="../images/rcyci.jpg" width="100" height="100" /></td>
					<td width="70%" valign="top" align="center"><span style="font-size:1.4em;font-weight:bold;">ROYAL COMMISSION COLLEGES AND INSTITUTE IN YANBU</span>'.$pageTitle.'</td>
					<td width="15%" align="right" style="background-color:#CCC"><span style="font-size:3em;">'.$count.'/'.$total.'</span></td>
				  </tr>
				</table>
			</td>
		  </tr>
		  <tr>
			<td>
				<table width="100%" border="0" cellspacing="1" cellpadding="5">
				  <tr>
					<td align="center"><span style="font-size:1.3em;">'.$semesterTitle.'</span></td>
				  </tr>
				  <tr>
					<td align="center"><span style="font-size:1.3em;font-weight:bold;">'.$department.'</span></td>
				  </tr>
				  <tr>
					<td align="center"><span style="font-size:1.3em;font-weight:bold;">'.$exam_type.'</span></td>
				  </tr>
				</table>			
				<table width="100%" border="0" cellspacing="1" cellpadding="5">
				  <tr>
					<td width="25%">Course Code</td>
					<td width="45%">'.$r->course_code.'</td>
					<td width="15%">&nbsp;</td>
					<td width="15%">&nbsp;</td>
				  </tr>
				  <tr>
					<td>Course Title</td>
					<td>'.$r->course_name.'</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>Exam Date</td>
					<td>'.$r->day_raw.'</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>Exam Time</td>
					<td>'.$r->exam_time.'</td>
					<td>Exam Day</td>
					<td align="left">'.$r->exam_day.'</td>
				  </tr>
				  <tr>
					<td>Course Exam Duration</td>
					<td>'.$course_duration.'</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>Room Number for this Envelope</td>
					<td>'.$r->venue.'</td>
					<td>Comment</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>Number of Students in the Room</td>
					<td>'.$r->class_size.'</td>
					<td colspan="2" rowspan="3" valign="top">
						'.$comment.'
					</td>
				  </tr>
				  <tr>
					<td>Number of Question Papers</td>
					<td>'.$total_script.'</td>
				  </tr>
				  <tr>
					<td>Number of Answer Scripts</td>
					<td>'.$total_script.'</td>
				  </tr>';
	$num = 1;
	foreach($proctors as $proctor)
	{
		$dept_rec = rc_ps_get_user_department($proctor->lecturer_code);
		if(!$dept_rec)
			$dept = '';
		else
			$dept = $dept_rec['ACAD_ORG'];
		$html = $html . '<tr>
					<td>Name of '.$arr[$num].' Proctor</td>
					<td>'.$proctor->lecturer_name.'</td>
					<td>Department</td>
					<td align="left">'.$dept.'</td>
				  </tr>';
		$num++;
	}
	$html = $html . '<tr>
				<td>Name of Course Coordinator</td>
				<td>'.$coor.'</td>
				<td>Signature</td>
				<td align="left"></td>
			  </tr>';
	$html = $html . '</table>
			</td>
		  </tr>
		</table>	
	';
	return $html;
}

function rcyci_tools_envelope_form()
{
	global $CFG;
	
	$college_select = rc_campus(true);
	$examtype = rc_get_config('exam', 'exam_type');
	
	if(isset($_GET['college']))
		$c = $_GET['college'];
	else
		$c = '';
		
	$course = rc_get_tmp_course();
	
	$college = rc_ui_select('college', $college_select, $c, 'filter_college()');
	
	$rs = rc_tools_envelope_get_course_list($c, $examtype);
	$opt = '<select name="code" id="code">';
	foreach($rs as $rec)
	{
		$opt = $opt . '<option value="'.$rec->course_code.'">'.$rec->course_code . ' - ' . $rec->course_name .'</option>';
	}
	$opt = $opt . '</select>';
	
	if($examtype == 1)
	{
		$qq = 'selected';
		$rr = '';
	}
	else
	{
		$qq = '';
		$rr = 'selected';
	}
	/*
	$opt = $opt . '&nbsp;<select name="exam_type" id="exam_type">
				<option value="2" '.$rr.'>Final Exam</option>
				<option value="1" '.$qq.'>Mid Term Exam</option>
				</select>
	';
	*/
	$opt = $opt . rc_ui_hidden('exam_type', $examtype);
	$str = '<form id="form1" name="form1" method="post" action="">
			<table width="100%" border="0" cellspacing="0" cellpadding="5">
			  <tr>
				<td valign="top">College : '.$college.'&nbsp;&nbsp;&nbsp;Select Course : '.$opt.' &nbsp;&nbsp;&nbsp;
				  <input type="button" name="button2" id="button2" value="Search" onclick="show_envelope()" />&nbsp;&nbsp;&nbsp;
				</td>
				<td valign="top">&nbsp;</td>
			  </tr>
			</table>
			</form>';
	return $str;
}

function rc_tools_envlope_step2($code, $type)
{
	global $CFG, $DB;
	if($type == 1)
		$exam_type = 'Mid Term Exam';
	else
		$exam_type = 'Final Exam';

	if(!$course = rc_get_course_by_code($code))
	{
		//some course, like ECON is YUC in moodle but treated as YIC course because the exam is scheduled in YIC.
		//for such courses, it will not be found. So try again to add -M at the end
		if(!$course = rc_get_course_by_code($code . '-M'))		
			return rc_ui_alert('Invalid course', 'Error', 'info', true, false, true); //if failed, then return error
	}
	$coordinator = rc_get_coordinator($course);
	$teacherList = rc_get_possible_coordinator($course, $coordinator, 'document.form2.coordinator.value = this.value');
	$sql = "select * from {rc_exam_data} where course_code = '$code' and exam_type = $type AND semester = '$CFG->semester' order by venue";
	$rec = $DB->get_records_sql($sql);
	foreach($rec as $exam) //get one record for information
		break;
	if(isset($exam))
	{
		$str = '<form id="form2" name="form2" method="post" action="">
				<table width="100%" border="0" cellspacing="0" cellpadding="5">';
		$str = $str . '
				<tr>
					<td valign="top" width="250"><strong>Course Code</strong></td>
					<td valign="top">'.$exam->course_code.'<div class="pull-right"><input type="button" name="button3" id="button3" value="Print Envelope" onclick="print_envelope()" /></div></td>
				</tr>
				<tr>
					<td valign="top"><strong>Course Name</strong></td>
					<td valign="top" colspan="2">'.$exam->course_name.'</td>
				</tr>
				<tr>
					<td valign="top"><strong>Exam Type</strong></td>
					<td valign="top" colspan="2">'.$exam_type.'</td>
				</tr>
				<tr>
					<td valign="top"><strong>Exam Date</strong></td>
					<td valign="top" colspan="2">'.$exam->day_raw.'</td>
				</tr>
				<tr>
					<td valign="top"><strong>Exam Day</strong></td>
					<td valign="top" colspan="2">'.$exam->exam_day.'</td>
				</tr>
				<tr>
					<td valign="top" width="150px"><strong>Exam Time</strong></td>
					<td valign="top" colspan="2">'.$exam->exam_time.'</td>
				</tr>
			';
		$str = $str . '<tr>
				<td valign="top"><strong>Course Exam Duration</strong></td>
				<td valign="top" colspan="2">'.rc_tools_envelope_time_selector($rec).'
				</td>
			</tr>';
		$str = $str . '<tr>
				<td valign="top"><strong>Number of Extra Paper</strong></td>
				<td valign="top" colspan="2">
				<select name="extra_paper" id="extra_paper">
					<option value="0">0</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
				</select>
				</td>
			</tr>';
		$str = $str . '<tr>
				<td valign="top"><strong>Comment</strong></td>
				<td valign="top" colspan="2"><input name="comment" type="text" id="comment" maxlength="120" value="" size="80"/>
				</td>
			</tr>';
		$str = $str . '<tr>
				<td valign="top"><strong>Course Coordinator</strong></td>
				<td valign="top" colspan="2"><input name="coordinator" type="text" id="coordinator" maxlength="10" value="" /> '.$teacherList.'&nbsp;&nbsp;(If the name of coordinator not in the list, just enter his employee id into the coordinator box)
				</td>
			</tr>';
		$title = 'Select Exam Room';		
		//for print all
		$str = $str . '<tr>
					<td valign="top"><strong>'.$title.'</strong></td>
					<td valign="top" colspan="2"><input type="radio" name="venue" id="venue" checked value="0" />&nbsp;&nbsp;All Rooms</td>
					</tr>';
		$title = '';
		foreach($rec as $r)
		{
			$str = $str . '<tr>
					<td valign="top">'.$title.'</td>
					<td valign="top" colspan="2"><input type="radio" name="venue" id="venue" value="'.$r->id.'" />&nbsp;&nbsp;'.$r->venue.'&nbsp;&nbsp;&nbsp;(Number of Students : '.$r->class_size.')</td>
				</tr>';
		}
		
		$str = $str . '</table>';
		$str = $str . '<input type="hidden" name="code" value="'.$code.'">';
		$str = $str . '<input type="hidden" name="exam_type" value="'.$type.'">';
		$str = $str . '</form>';
	}
	else
		$str = rc_ui_alert('No examination schedule found for this course', 'Error', 'info', true, false, true);
	return $str;
}


function rc_tools_envelope_get_course_list($filter, $examtype)
{		
	global $DB, $CFG;
	if($filter != '') //need to filter, pick from logsis
	{
		$condition = " AND campus = '$filter'";
	}
	else //no need filter, just pick from moodle database
	{
		$condition = '';
	}
	$sql = "select distinct course_code, course_name from {rc_exam_data} WHERE exam_type = '$examtype' and semester = '$CFG->semester' $condition order by course_code";
	$rec = $DB->get_records_sql($sql);
	return $rec;
}

function rc_tools_envelope_time_selector($rec)
{
	global $CFG;
	//use new approach by fixing the time
	$str = '';
	foreach($rec as $r)
	{
		$str = $r->duration_raw;
		if($str != '')
		{
			$arr = explode(' ' , $str);
			$str = $str . '<input type="hidden" name="hour" id="hour" value="'.$arr[0].'">';
			$str = $str . '<input type="hidden" name="minute" id="minute" value="'.$arr[2].'">';
			break;
		}
	}
	if($str == '')
	{
		if($CFG->examtype == 1) //mid term, default to 2 hours
		{
			$twoHour = 'selected';
			$threeHour = '';
		}
		else
		{
			$twoHour = '';
			$threeHour = 'selected';
		}
		$str = '<select name="hour" id="hour">
					<option value="0">0</option>
					<option value="1">1</option>
					<option value="2" '.$twoHour.'>2</option>
					<option value="3" '.$threeHour.'>3</option>
				</select>&nbsp;Hour(s)&nbsp;&nbsp;&nbsp;';
		$str = $str . '<select name="minute" id="minute">';
		for($i = 0; $i < 60; $i = $i + 5)
			$str = $str . '<option value="'.$i.'">'.$i.'</option>';
		$str = $str . '</select>&nbsp;Minute(s)';
	}
	return $str;
}

function rc_tool_textbook()
{
	$sql = "INSERT INTO m_rc_textbook(
	COURSE_CODE,
	TITLE,
	AUTHOR,
	YEAR_PUBLISH,
	EDITION,PUBLISHER,
	ISBN,ORDERING,
	IS_ACTIVE) 
values('CHET103','Survey of Industrial Chemistry','Philip J. Chinier','2002','3rd','Kluwer Academic','0306472645', '1', '1')
";
	$DB->execute($sql);
}

//isEndUser means at the end user page where certain field are disabled
function rc_tool_external_resource_search_form($data, $isEndUser = false)
{
	$resourceList = array(
		'' => 'All',
		'link' => 'URL Link', 
		'form' => 'Form', 
	);

	$college = array(
		'' => 'All', 
		'YIC' => 'YIC', 
		'YUC-M' => 'YUC-M',
		'YUC-F' => 'YUC-F',
		'YTI' => 'YTI',
		'HIEI' => 'HIEI',
		'YIC_YUC-M' => 'YIC and YUC Male',
	);

	$access_type = array('' => 'All', 'student' => 'Student', 'teacher' => 'Staff');
	
	$activeList = array(
		'' => 'All',
		'1' => 'Active',
		'2' => 'Inactive',
	);
	if(!$isEndUser)
		$width = 15;
	else
		$width = 25;
	
	$str = '<form id="form1" name="form1" method="post" onsubmit="return external_resource_search()" action="">';
	$str = $str . 'Search : ' . rc_ui_input('search', $width, $data['search'], 'handleKeyPress(event)');
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . 'Resource : ' . rc_ui_select('resource_type', $resourceList, $data['resource_type'], 'external_resource_search()', '');
	if(!$isEndUser)
	{			
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . 'Type : ' . rc_ui_select('access_type', $access_type, $data['access_type'], 'external_resource_search()', '');
	}
	else
		$str = $str . rc_ui_hidden('access_type', $data['access_type']);
	if(!$isEndUser)
	{			
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . 'College : ' . rc_ui_select('access_context', $college, $data['access_context'], 'external_resource_search()', '');
	}
	else
		$str = $str . rc_ui_hidden('access_context', $data['access_context']);
	if(!$isEndUser)
	{		
		$str = $str . '&nbsp;&nbsp;&nbsp;';
		$str = $str . 'Status : ' . rc_ui_select('active', $activeList, $data['active'], 'external_resource_search()', '');
	}
	else
		$str = $str . rc_ui_hidden('active', $data['active']);
	$str = $str . '&nbsp;&nbsp;&nbsp;';
	$str = $str . '<span class="pull-right"><input type="button" name="button3" id="button3" value="Refresh" onclick="external_resource_search()"/></span>';
	$str = $str . rc_ui_hidden('sort', 1);
	$str = $str . '</form>';
	return $str;
}


function rc_tools_print_envelope_pdf($htmlArray, $customFooter='', $fontSize=12, $landscape=true)
{
	// create new PDF document
	if($landscape)
		$orient = 'L';
	else
		$orient = PDF_PAGE_ORIENTATION;
	$pdf = new MYPDF($orient, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Muhammad Rafiq');
	$pdf->SetTitle('Examination Envelop');
	$pdf->SetSubject('Examination');
	$pdf->SetKeywords('Examination Report');
	$pdf->setFontSubsetting(false);
	
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	
	// set default monospaced font
	//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetFont(PDF_FONT_NAME_MAIN);

		if($customFooter != '')
		{
			$pdf->customFooter = $customFooter;
		//set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, 50);
			$pdf->SetFooterMargin(20);
		}
		else
	{
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	}

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER - 2);
	
	
	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
//		$pdf->setCellHeightRatio(1.25);
	
	//set some language-dependent strings
//	$pdf->setLanguageArray($l);
	
	// ---------------------------------------------------------
	
	// set font
	$pdf->SetFont('helvetica', '', $fontSize);
	
	// add a page
//	$pdf->AddPage();
		
	// output the HTML content
//	$pdf->writeHTML($html, true, false, true, false, '');
	

	//multi page
	$count = 0;
	foreach($htmlArray as $html)
	{
		$pdf->AddPage();			
		$pdf->writeHTML($html, true, false, true, false, '');
		$count++;
	}
	
	
	
	//Close and output PDF document
	$pdf->Output('exam_envelop.pdf', 'I');
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	var $customFooter = '';
    //Page header
    public function Header() {
        // Logo
//        $image_file = 'YIC_100.jpg';
		$ormargins = $this->getOriginalMargins();
		$headerfont = $this->getHeaderFont();
		$headerdata = $this->getHeaderData();
		if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
			$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
			$imgy = $this->getImageRBY();
		} else {
			$imgy = $this->GetY();
		}
		$cell_height = round(($this->getCellHeightRatio() * $headerfont[2]) / $this->getScaleFactor(), 2);
		// set starting margin for text data cell
		if ($this->getRTL()) {
			$header_x = $ormargins['right'] + ($headerdata['logo_width'] * 1.1);
		} else {
			$header_x = $ormargins['left'] + ($headerdata['logo_width'] * 1.1);
		}
		$this->SetTextColor(0, 0, 0);
		// header title
		$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
		$this->SetX($header_x);
		$this->Cell(0, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
		// header string
		$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
		$this->SetX($header_x);
		$this->MultiCell(0, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false);
		// print an ending header line
		$this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));
		$this->SetY((2.835 / $this->getScaleFactor()) + max($imgy, $this->GetY()));
		if ($this->getRTL()) {
			$this->SetX($ormargins['right']);
		} else {
			$this->SetX($ormargins['left']);
		}
		$this->Cell(0, 0, '', 'T', 0, 'C');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        // Set font
        $this->SetFont('helvetica', 'N', 8);
		$printDate = date("d-M-Y : H:i:s", time());
		$iso_file = 'Y09-01-00-22/01'; //iso file
        // Page number
		if($this->customFooter == '') //use default footer
		{
	        $this->SetY(-15);
	        $this->Cell(0, 0, $iso_file, 0, false, 'L', 0, '', 0, false, 'T', 'M');
	        $this->Cell(0, 0, 'Date: '.$printDate, 0, false, 'R', 0, '', 0, false, 'T', 'M');
//	        $this->Cell(0, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
		}
		else //use custom footer
		{
	        $this->SetY(-40);
			$this->writeHTML($this->customFooter, true, false, true, false, '');
	        $this->Cell(0, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'T', 'M');
	        $this->Cell(0, 0, 'Date: '.$printDate, 0, false, 'R', 0, '', 0, false, 'T', 'M');
		}
    }
}

