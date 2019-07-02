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
require_once '../lib/rc_output_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once 'lib.php'; //local library

$urlparams = array();
$PAGE->set_url('/local/rcyci/tools/envelope.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$rc_user_type = rc_get_user_type();
if(!is_siteadmin() && $rc_user_type != 'teacher') //not admin and not teacher, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');
	
$exam_envelope = rc_get_config('exam', 'allow_printing_envelope');
if($exam_envelope != 'yes')
	throw new moodle_exception('Printing of examination envelope is currently close.');
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

$coordinator = $_GET['coordinator'];
$code = $_GET['code'];
$venue = $_GET['venue'];
if($venue == 0) //print all
{
	$type = $_GET['type'];
	$sql = "select * from {rc_exam_data} where course_code = '$code' and exam_type = $type AND semester = '$CFG->semester' order by venue";
	$rec = $DB->get_records_sql($sql);
	foreach($rec as $r)
	{
		$_GET['venue'] = $r->id;
		$html = '';
		$html = $html . rc_output_get_styles_pdf_print();
		$html = $html . rc_tools_get_envelope_print_html($_GET);
		$htmlArray[] = $html;
	}
}
else //print individual room
{
	$html = '';
	$html = $html . rc_output_get_styles_pdf_print();
	$html = $html . rc_tools_get_envelope_print_html($_GET);
	$htmlArray[] = $html;
}
rc_tools_print_envelope_pdf($htmlArray);
