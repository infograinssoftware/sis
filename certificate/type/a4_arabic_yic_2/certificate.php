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
	$student = new stdClass();
	$student->username = '000001';
	$student->firstname = 'شهد عبدالله الذبياني 3710100';
	$student->lastname = '';
	$student_name = $student->firstname;
}
else
{
	$student_data = rc_ls_get_stu_info($student_id);
//	$student = $DB->get_record('user', array('username' => $student_id));

	$student_rec = $DB->get_record('rc_certificate_user', array('certificate_id' => $id, 'emplid' => $student_id));
	if($student_rec && $student_rec->custom_name != '')
		$student_name = $student_rec->custom_name;
	else
		$student_name = $student_data['A_OFFICIAL_NAME'];
	$student_name = $student_name . ' ' . $student_id;	
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
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 10;
    $y = 65;
    $sealx = 230;
    $sealy = 160;
    $sigx = 40;
    $sigy = 170;
    $custx = 38; //signature
    $custy = 162; //signature
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
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 120);

// use font 'tradbdo' if arabic
//font Helvetica for English
$pdf->SetTextColor(255, 0, 0); //red
certificate_print_text($pdf, $x, $y, 'C', 'tradbdo', '', 30, $certificate->title);
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y + 20, 'C', 'tradbdo', '', 20, $certificate->certified);
certificate_print_text($pdf, $x, $y + 36, 'C', 'tradbdo', '', 26, $certificate->recepient);
certificate_print_text($pdf, $x, $y + 55, 'C', 'tradbdo', '', 26, $certificate->statement);
$pdf->SetTextColor(255, 0, 0); //red
certificate_print_text($pdf, $x, $y + 72, 'C', 'tradbdo', '', 24, $certificate->name);
$pdf->SetTextColor(0, 0, 0); //red
certificate_print_text($pdf, $x, $y + 92, 'C', 'tradbdo', '', 20, $certificate->cert_date);
certificate_print_text($pdf, $x, $y + 112, 'C', 'tradbdo', '', 16, $certificate->detail1);
//certificate_print_text($pdf, $x, $y + 112, 'C', 'tradbdo', '', 10, $certificate->detail2);

certificate_print_text($pdf, $custx, $custy, 'L', 'tradbdo', '', 16, $certificate->detail2);
certificate_print_text($pdf, $custx + 5, $custy + 27, 'L', 'tradbdo', '', 16, $certificate->signature_name);
