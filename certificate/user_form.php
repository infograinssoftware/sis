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

class user_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG;

		$id = $this->_customdata['id'];
		
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', $id);		
		
		$mform->addElement('header', 'headergradetemplate', 'Students list');
		$mform->addElement('textarea', 'users', 'List of Recepients', 'wrap="virtual" rows="6" cols="50"');		
		$mform->addElement('static', 'description', '', 'Enter each student id separated by a comma (,) or a new line.<br />Example : 3500321,3500322,3700201,3700203');
		//optional details
		$mform->addElement('header', 'headergradetemplate', 'Optional fields override (overrides the default value in certificate)');
		$mform->addElement('text', 'award_title', 'Award Title', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'award_date', 'Award Detail 1', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'award_reason', 'Award Detail 2', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'award_detail', 'Award Detail 3', array('size' => 30)); // Add elements to your form
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
