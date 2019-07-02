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

class template_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG;
		$groups = rc_grade_group();	 
		$is_lab = array('0' => 'No', '1' => 'Yes');
		$is_final = array('0' => 'No', '1' => 'Yes');
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();
		$mform->addElement('header', 'headergradetemplate', 'Add New Category');
		$mform->addElement('text', 'category', 'Add Category'); // Add elements to your form
		$mform->addRule('category', 'Category cannot be empty', 'required', '', 'server', false, false);
		$mform->addElement('select', 'is_lab', 'Is Lab?', $is_lab, $attributes);
		$mform->addElement('select', 'is_final', 'Is Final Exam?', $is_final, $attributes);
		$mform->addElement('select', 'group', 'Category Group', $groups, $attributes);
		$this->add_action_buttons($cancel=false);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
