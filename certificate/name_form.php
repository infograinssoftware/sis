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

class name_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG;

		$student_id = $this->_customdata['student_id'];
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', $student_id);		
		
		//optional details
		$mform->addElement('header', 'headergradetemplate', 'Name override (overrides the default name from student system)');
		$mform->addElement('text', 'custom_name', 'Custom Name 1', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'custom_name2', 'Custom Name 2', array('size' => 50)); // Add elements to your form
<<<<<<< HEAD
=======
		$mform->addElement('text', 'custom_award_date', 'Detail 1', array('size' => 50)); // Add elements to your form
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
