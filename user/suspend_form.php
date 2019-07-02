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
 * Change password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

class suspend_form extends moodleform {

    function definition() {
        global $CFG, $DB;
		$user_id = optional_param('id', '', PARAM_TEXT);
		$username = optional_param('username', '', PARAM_TEXT);
		if($user_id != '')
		{
			$student = $DB->get_record('user', array('id' => $user_id));
			if($student)
				$name = ' : <strong>' . $student->firstname . '</strong>';
			else
				$name = '';
		}
		else
			$name = '';
        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        $mform->addElement('header', 'duplicateactivity', 'Message to student under suspension' . $name, '');
        // visible elements
		if($user_id != '') //for specific user
		{
			$adm = $DB->get_record('rc_suspend_message', array('emplid' => $username));
			if($adm) //existing
				$description = $adm->message;
			else
				$description = '';
		}
		else
		{
			$adm = $DB->get_record('user', array('username' => 'admin'));
			$description = $adm->description;
		}
		$mform->addElement('editor', 'message', 'Suspend Message');
		$mform->addElement('hidden', 'user_id', $user_id);
		$mform->addElement('hidden', 'username', $username);
		$mform->setType('message', PARAM_RAW);
		$mform->setDefault('message', array('text'=>$description));
        $this->add_action_buttons(true);

    }

/// perform extra password change validation
    function validation($data, $files) {
    }
}
