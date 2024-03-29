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
 * Change password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once '../lib/rclib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once '../lib/rc_ps_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require_once '../lib/rc_ui_lib.php'; //The main RCYCI functions include. This will include the dblib. So no need to include anymore
require('lib.php');
require_once('change_password_form.php');

$id     = optional_param('id', SITEID, PARAM_INT); // current course
$return = optional_param('return', 0, PARAM_BOOL); // redirect after password change

$systemcontext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_url('/local/rcyci/user/change_password.php', array('id'=>$id));

$PAGE->set_context($systemcontext);

if ($return) {
    // this redirect prevents security warning because https can not POST to http pages
    if (empty($SESSION->wantsurl)
            or stripos(str_replace('https://', 'http://', $SESSION->wantsurl), str_replace('https://', 'http://', $CFG->wwwroot.'/login/change_password.php')) === 0) {
        $returnto = "$CFG->wwwroot/user/preferences.php?userid=$USER->id&course=$id";
    } else {
        $returnto = $SESSION->wantsurl;
    }
    unset($SESSION->wantsurl);

    redirect($returnto);
}

$strparticipants = get_string('participants');

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

// require proper login; guest user can not change password
if (!isloggedin() or isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->httpswwwroot.'/login/change_password.php';
    }
    redirect(get_login_url());
}

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_pagelayout('maintenance'); //set to maintenance where there is no redirect function to avoid circular redirection
$PAGE->set_course($course);

$rc_user_type = rc_get_user_type();
$isAdmin = is_siteadmin();
if($rc_user_type != 'teacher') //only teacher is allow to change password
{
    print_error('nopasswordchange', 'auth');
}

$mform = new login_change_password_form();
$mform->set_data(array('id'=>$course->id));

$navlinks = array();
$navlinks[] = array('name' => $strparticipants, 'link' => "$CFG->wwwroot/user/index.php?id=$course->id", 'type' => 'misc');

if ($mform->is_cancelled()) 
{
	if(rc_need_change_password(false)) //user not activated his account and he choose cancel, redirect to logout
	{
		$sesskey = $USER->sesskey;
		$url = new moodle_url($CFG->wwwroot.'/login/logout.php', array('sesskey' => $sesskey));
	    redirect($url);
	}
	else
	    redirect($CFG->wwwroot.'/index.php');
} 
else if ($data = $mform->get_data()) 
{	
	if(rc_user_update_password($USER, $data->newpassword1)) //if successfully change password
		$_SESSION['rc_change_password'] = false; //cancel the password change session
    if (!empty($CFG->passwordchangelogout)) {
        \core\session\manager::kill_user_sessions($USER->id, session_id());
    }
    // Reset login lockout - we want to prevent any accidental confusion here.
    login_unlock_account($USER);

    // register success changing password
    unset_user_preference('auth_forcepasswordchange', $USER);
    unset_user_preference('create_password', $USER);

    $strpasswordchanged = get_string('passwordchanged');

    $fullname = fullname($USER, true);

    $PAGE->set_title($strpasswordchanged);
    $PAGE->set_heading(fullname($USER));
    echo $OUTPUT->header();

    notice($strpasswordchanged, new moodle_url($CFG->wwwroot.'/index.php', array('return'=>1)));

    echo $OUTPUT->footer();
    exit;
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();

$strchangepassword = get_string('changepassword');

$fullname = fullname($USER, true);

$PAGE->set_title($strchangepassword);
$PAGE->set_heading($fullname);
echo $OUTPUT->header();

if (get_user_preferences('auth_forcepasswordchange')) {
    echo $OUTPUT->notification(get_string('forcepasswordchangenotice'));
}
if(rc_need_change_password(false)) //first time
{
	rc_ui_alert('The system noticed that this is the first time you log in to Moodle. You have to change your current password to activate your Moodle account. Your initial Current password is your national id/iqamah number or the default password given to you by the administrator.', 'Note', 'success', true, false);
}
$mform->display();
echo $OUTPUT->footer();
