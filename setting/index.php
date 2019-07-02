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
require_once 'lib.php'; //local library

$urlparams = array();
$PAGE->set_url('/local/rcyci/setting', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login
if(!is_siteadmin()) //not admin, do not allow
	throw new moodle_exception('Access denied. This module is only accessible by administrator.');

//frontpage - for 2 columns with standard menu on the right
//rcyci - 1 column
$PAGE->set_pagelayout('rcyci_column2');

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here
//display of tab
$baseurl = $PAGE->url;
if(!$currenttab = optional_param('tab', '', PARAM_ALPHANUM)) // get the query string
	$currenttab = 'global';
//$currenttab = 'global';
if ($tab_controls = rc_setting_tab_controls($baseurl, $currenttab)) 
{
    echo $OUTPUT->render($tab_controls);
}
//$renderer = $PAGE->get_renderer('local_rcyci'); //get the rcyci output renderer
//$content = $renderer->local_rcyci_content(); //get the content
//echo $content; //output the content
echo $OUTPUT->box_start('rc_tabbox');
//code starts here
if($currenttab == 'global')
{
	if(isset($_POST['option']) && $_POST['option'] == 'global') //need to save
		rc_setting_save_global($_POST);
	rc_setting_global();
}
else if($currenttab == 'module')
{
}
else if($currenttab == 'attend')
{
	if(isset($_POST['option']) && $_POST['option'] == 'attendance') //need to save
		rc_setting_save_attendance($_POST);
	rc_setting_attendance();
}
else if($currenttab == 'projector')
{
	
}

$PAGE->requires->js('/local/rcyci/setting/setting.js');
echo $OUTPUT->box_end();
//content code ends here
echo $OUTPUT->footer();
