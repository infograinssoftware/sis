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
require_once 'lib.php'; //local library
require_once 'certificate_form.php';

$urlparams = array();
$PAGE->set_url('/local/rcyci/certificate/index.php', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
$isAdmin = is_siteadmin();
$roles = rc_get_user_all_role($USER->idnumber, 'certificate');
$hasAccess = rc_has_access(array('admin'), $roles);
if(!$isAdmin && !$hasAccess) //not admin and not attendance, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by certificate administrator.');
	
//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here
rc_ui_page_header('RCYCI Certificate Administrator');

echo '<div class="text-center">';
$img = '<img src="'.$CFG->wwwroot . '/local/rcyci/certificate/pix/certificate_sample.jpg">';

rc_ui_box($img, 'Certificate Text Layout');

echo '</div>';



//content code ends here
$PAGE->requires->js('/local/rcyci/certificate/certificate.js');
echo $OUTPUT->footer();