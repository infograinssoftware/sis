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
 * A4_standard_english certificate type
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
//require_once $CFG->libdir.'/tcpdf/tcpdf.php';
require_once($CFG->dirroot.'/local/rcyci/certificate/lib_cert.php'); //local library
require_once($CFG->dirroot.'/local/rcyci/lib/rc_ls_lib.php'); //local library
require_once($CFG->dirroot.'/local/rcyci/lib/rclib.php'); //local library
require_once("$CFG->libdir/pdflib.php");

$id = $_GET['id'];
if(!$id)
	throw new moodle_exception('Invalid parameter');

$student_id = $_GET['student_id'];
if(!$student_id) //not student id from querystring, get it from the user, i.e. it must be from student access
	$student_id = $USER->idnumber;
if($student_id != 1)
{
	if($student_id == '' || strlen($student_id < 6))
		throw new moodle_exception('Only a valid student can access to this module');
}
$cert = $DB->get_record('rc_certificate', array('id' => $id));
if($student_id == 1) //dummy for preview
{
	$student_id = '3710100';
	$student = new stdClass();
	$student->username = '000001';
	$student->firstname = 'شهد عبدالله الذبياني 3710100';
	$student->lastname = '';
	$student_name_en = 'Shad Abdullah Alzibiyani';
	$student_name_ar = 'شهد عبدالله الذبياني';
	$college_en = 'Yanbu Industrial College';
	$college_ar = 'كلية ينبع الصناعية';
}
else
{
	$student_data = rc_ls_get_stu_info($student_id);
//	print_object($student_data);
//	die;
//	$student = $DB->get_record('user', array('username' => $student_id));

	$student_rec = $DB->get_record('rc_certificate_user', array('certificate_id' => $id, 'emplid' => $student_id));
	if($student_rec && $student_rec->custom_name != '')
		$student_name_ar = $student_rec->custom_name;
	else
		$student_name_ar = $student_data['A_OFFICIAL_NAME'];

	if($student_rec && $student_rec->custom_name2 != '')
		$student_name_en = ucwords($student_rec->custom_name2);
	else
		$student_name_en = ucwords($student_data['E_OFFICIAL_NAME']);
//	$student_name_ar = $student_data['A_F_NAME'] . ' ' . $student_data['A_FA_NAME'] . ' '  . $student_data['A_FAM_NAME'] . ' '  . $student_data['A_G_NAME'];
	$college_en = rc_ls_college_english($student_data['STU_CAT']);
	$college_ar = rc_ls_college_arabic($student_data['STU_CAT']);	
}


//fields for certificate
$certificate = new stdClass();
$certificate->name = $cert->title;
$certificate->orientation = 'L'; //L for landscape
$certificate->certified = $cert->certifified;
$certificate->recepient = $student_name;
$certificate->statement = $cert->award;
$certificate->title = $cert->award_title;
$certificate->cert_date = $cert->award_date;
$certificate->detail1 = $cert->award_reason;
$certificate->detail2 = $cert->award_detail;
$certificate->signature_name = $cert->signature_name;
$certificate->printsignature = $cert->signature_image . '.jpg';
$certificate->printseal = $cert->stamp . '.png';
$certificate->certificatetype = $cert->certificate_type;
$certificate->borderstyle = $cert->border_style . '.jpg';
$certificate->bordercolor = $cert->border;
$certificate->printwmark = $cert->watermark . '.png';
$certificate->printdate = 0;
$certificate->introformat = 1;
$certificate->emailteachers = 0;
$certificate->emailothers = '';
$certificate->savecert = 0;
$certificate->reportcert = 0;
$certificate->delivery = 0;
$certificate->requiredtime = 0;
$certificate->datefmt = 1;
$certificate->printnumber = 0;
$certificate->printgrade = 0;
$certificate->gradefmt = 1;
$certificate->printoutcome = 0;
$certificate->printhours = '';
$certificate->printteacher = 0;


$pdf = new PDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($certificate->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->SetMargins(0, 0, 15, true);
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 10;
    $y = 42;
    $sealx = 230;
    $sealy = 165;
    $custx = 50;
    $custy = 167;
    $wmarkx = 85;
    $wmarky = 43;
    $wmarkw = 130;
    $wmarkh = 130;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = 175;
	
} else { // Portrait
    $x = 10;
    $y = 40;
    $sealx = 150;
    $sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;
}
// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');

// Add text
$pdf->SetTextColor(128, 0, 0);

// use font 'tradbdo' if arabic
//font Helvetica for English
certificate_print_text($pdf, $x, $y, 'C', 'tradbdo', '', 40, $certificate->certified);
certificate_print_text($pdf, $x, $y + 17, 'C', 'helvetica', '', 30, $certificate->statement);
$pdf->SetTextColor(0, 0, 0);
$line1_en = 'Royal Commission of Yanbu Colleges and Institutes';
$line1_ar = 'يشهد قطاع الكليات والمعاهد بأن الطالب' . '';
certificate_print_text($pdf, $x + 5, $y + 40, 'L', 'helvetica', '', 18, $line1_en);
certificate_print_text($pdf, $x + 5, $y + 48, 'L', 'helvetica', '', 16, 'Hereby Certifices That');
certificate_print_text($pdf, $x + 5, $y + 40, 'R', 'tradbdo', '', 24, $line1_ar);

$recepient_en = $student_name_en;
$recepient_ar = $student_name_ar;

certificate_print_text($pdf, $x + 5, $y + 59, 'L', 'helvetica', '', 15, $recepient_en);
certificate_print_text($pdf, $x + 5, $y + 58, 'R', 'tradbdo', '', 20, $recepient_ar);

$student_id_ar = rc_arabic_number($student_id);

$id_en = 'ID Number: ' . $student_id . ' ('.$college_en.')';
$id_ar = 'رقم:' . $student_id_ar . ' ('.$college_ar.')';
certificate_print_text($pdf, $x + 5, $y + 68, 'L', 'helvetica', '', 15, $id_en);
certificate_print_text($pdf, $x + 5, $y + 68, 'R', 'tradbdo', '', 20, $id_ar);

$line2_en = $certificate->title;
$line2_ar = $certificate->detail1;

certificate_print_text($pdf, $x + 5, $y + 78, 'L', 'helvetica', '', 15, $line2_en);
certificate_print_text($pdf, $x + 5, $y + 77, 'R', 'tradbdo', '', 20, $line2_ar);

$line3_en = $certificate->cert_date;
$line3_ar = $certificate->detail2;

certificate_print_text($pdf, $x + 5, $y + 88, 'L', 'helvetica', '', 15, $line3_en);
certificate_print_text($pdf, $x + 5, $y + 87, 'R', 'tradbdo', '', 20, $line3_ar);

//signature
$sigx = 120;
$sigy = 146;
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');
//line below signature
$style = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'phase' => 10, 'color' => array(105, 105, 105));
$pdf->Line(108, 167, 185, 167, $style);


//certificate_print_text($pdf, $x, $y + 55, 'C', 'tradbdo', '', 20, $certificate->title);
//certificate_print_text($pdf, $x, $y + 72, 'C', 'tradbdo', '', 26, $certificate->recepient);
//certificate_print_text($pdf, $x, $y + 140, 'C', 'helvetica', '', 12, $certificate->detail2);
//certificate_print_text($pdf, $x, $y + 146, 'C', 'helvetica', '', 12, $certificate->cert_date);

$title_ar = 'وكيل الكليات والمعاهد لشؤون الطلاب';

certificate_print_text($pdf, $x, $y + 128, 'C', 'tradbdo', '', 16, $title_ar);
certificate_print_text($pdf, $x, $y + 134, 'C', 'tradbdo', '', 16, $certificate->signature_name);

$hod_en = 'Dr. Raed Althomali';
$title_en = 'Colleges DMD, Student Affairs';

certificate_print_text($pdf, $x, $y + 143, 'C', 'helvetica', '', 12, $title_en);
certificate_print_text($pdf, $x, $y + 149, 'C', 'helvetica', '', 12, $hod_en);

