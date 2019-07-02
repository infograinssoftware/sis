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

class external_resource_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');		
		
		$mform->addElement('header', 'headergradetemplate', 'External Resource Information');
		$mform->addElement('text', 'title', 'Title', array('size' => 255)); // Add elements to your form
		$mform->addRule('title', 'Title cannot be empty', 'required', '', 'server', false, false);
		$mform->addElement('text', 'link', 'URL', array('size' => 1000)); // Add elements to your form
		$mform->addRule('link', 'URL cannot be empty', 'required', '', 'server', false, false);
		$mform->addElement('static', '', '', 'Please include the http:// or https:// prefix in the URL');
		$mform->addElement('textarea', 'description', 'Description', 'wrap="virtual" rows="5" cols="50"');

		$resource_type = array(
			'link' => 'URL Link', 
			'form' => 'Form', 
			);
		
		$college = array(
			'all' => 'All', 
			'YIC' => 'YIC', 
			'YUC-M' => 'YUC-M',
			'YUC-F' => 'YUC-F',
			'YTI' => 'YTI',
			'HIEI' => 'HIEI',
			'YIC_YUC-M' => 'YIC and YUC Male',
		);

		$access_type = array('all' => 'All', 'student' => 'Student', 'teacher' => 'Staff');
			
		$is_active = array('1' => 'Yes', '2' => 'No');
				
		$mform->addElement('select', 'resource_type', 'Resource Type', $resource_type, $attributes);
		$mform->addElement('select', 'access_type', 'Access', $access_type, $attributes);
		$mform->addElement('select', 'access_context', 'College', $college, $attributes);
		$mform->addElement('select', 'is_active', 'Is Active', $is_active, $attributes);
				
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
