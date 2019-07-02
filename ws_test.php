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

require_once '../../config.php';
require_once 'lib/sis_lib.php'; //The main sis functions include. This will include the dblib. So no need to include anymore

$urlparams = array();
$PAGE->set_url('/local/sis', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_cacheable(false);

require_login(); //always require login

//frontpage - for 2 columns with standard menu on the right
//sis - 1 column
$PAGE->set_pagelayout('sis');

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
//content code starts here

$data = new stdClass();
//$data->method = 'student_course';
$data->method = 'student_info';
$data->field = '';
$data->value = '11210019';
//$data->value = '10110114';

$result = rc_ws_export_data($data); //universal function to start the export process
print_object($result); //to see the raw data

$return_message['status'] = '1'; //generic status field
$return_message['result'] = $result; //if we put the result here, what ever received will be returned. Can be used for debug
$return_message = json_encode($return_message); //encode it in json


$actual_return = array('message' => $return_message);

print_object($actual_return); //to see the actual return json data


//content code ends here
echo $OUTPUT->footer();
