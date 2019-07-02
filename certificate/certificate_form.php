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

class certificate_form extends moodleform {

	//Add elements to form
	public function definition() {
		global $CFG, $USER;
		$mform = $this->_form; // Don't forget the underscore! 
 		$attributes = array();

		$mform->addElement('hidden', 'id', '');	
		
		$mform->addElement('hidden', 'created_by', $USER->idnumber);		
		
		$mform->addElement('header', 'headergradetemplate', 'Certificate Information');
		$mform->addElement('text', 'title', 'Certificate Title', array('size' => 50)); // Add elements to your form
		$mform->addRule('title', 'Title cannot be empty', 'required', '', 'server', false, false);
		$mform->addElement('text', 'certifified', 'Certified Text', array('size' => 40)); // Add elements to your form
		$mform->addElement('text', 'award', 'Award Text', array('size' => 40)); // Add elements to your form
		$mform->addElement('text', 'award_title', 'Award Title', array('size' => 50)); // Add elements to your form
		$mform->addElement('text', 'award_date', 'Award Detail 1', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'award_reason', 'Award Detail 2', array('size' => 30)); // Add elements to your form
		$mform->addElement('text', 'award_detail', 'Award Detail 3', array('size' => 30)); // Add elements to your form
		
<<<<<<< HEAD
		$preview_url = new moodle_url('/local/rcyci/certificate/certificate_sample.php', array());
		$preview = html_writer::link($preview_url, rc_ui_icon('search', '1.2', true), array('title' => 'View Field Location', 'target' => '_blank'));
=======
		$preview_url = new moodle_url('/local/sis/certificate/certificate_sample.php', array());
		$preview = html_writer::link($preview_url, sis_ui_icon('search', '1', true), array('title' => 'View Field Location', 'target' => '_blank'));
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
		
		$mform->addElement('static', 'description', '', 'Preview the location of each field in the certificate => ' . $preview);

		$mform->addElement('header', 'headergradetemplate', 'Certificate Design');
		$mform->addElement('text', 'signature_name', 'Signature Name'); // Add elements to your form
		$mform->addRule('signature_name', 'Signature Name cannot be empty', 'required', '', 'server', false, false);
		
		//key must be the name of the signature file
		$signature = array(
			'salem' => 'Dr. Salem Aletani', 
			'raed' => 'Dr. Raed Althomali', 
			'hamzah' => 'Hamzah Artik',
			'makallawi' => 'Mohammad Makallawi',
			);
			
		$college = array(
			'-' => 'None', 
			'rcyci' => 'RCYCI', 
//			'yic' => 'YIC', 
//			'yucm' => 'YUC-M',
//			'yucf' => 'YUC-F',
//			'yti' => 'YTI',
//			'hiei' => 'HIEI',
			);
			
		$is_active = array('1' => 'Yes', '2' => 'No');
		
		$certificate_type = array(
			'a4_arabic_yucf' => 'A4 Arabic YUC-F',
			'a4_arabic_yucf_2' => 'A4 Arabic YUC-F 2',
			'a4_arabic_yucf_3' => 'A4 Arabic YUC-F 3',
<<<<<<< HEAD
=======
			'a4_arabic_yucf_4' => 'A4 Arabic YUC-F 4',
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
			'a4_english_yucf' => 'A4 English YUC-F',
			'a4_english_yucf_2' => 'A4 English YUC-F 2',
			'a4_arabic_yic' => 'A4 Arabic YIC',
			'a4_arabic_yic_2' => 'A4 Arabic YIC 2',
			'a4_english_yic' => 'A4 English YIC',
		);

		$border_type = array(
<<<<<<< HEAD
			'Fancy1-black' => 'Standard-black',
			'Fancy1-blue' => 'Standard-blue',
=======
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
			'yuc_female_1' => 'YUC Female 1',
			'yuc_female_2' => 'YUC Female 2',
			'yuc_female_3' => 'YUC Female 3',
			'yuc_female_4' => 'YUC Female 4',
			'yuc_female_5' => 'YUC Female 5',
			'yuc_female_6' => 'YUC Female 6',
<<<<<<< HEAD
=======
			'yuc_female_7' => 'YUC Female 7',
>>>>>>> 62fad2890f1d8f1375bdbd60832d51a0727f78a7
			'yic_1' => 'YIC 1',
			'yic_2' => 'YIC 2',
		);
		
		$border_show = array('0' => 'No', '1' => 'Yes');

		$rule = array('1' => 'Eligible List', '2' => 'Custom Rule (Admin only)');
		
		$mform->addElement('select', 'signature_image', 'Signature', $signature, $attributes);
		$mform->addElement('select', 'stamp', 'Stamp', $college, $attributes);
		$mform->addElement('select', 'watermark', 'Watermark', $college, $attributes);
		$mform->addElement('select', 'certificate_type', 'Certificate Type', $certificate_type, $attributes);
		$mform->addElement('select', 'border_style', 'Border Style', $border_type, $attributes);
		$mform->addElement('select', 'border', 'Show Border Box', $border_show, $attributes);
		$mform->addElement('select', 'rule', 'Eligible Rule', $rule, $attributes);
		$mform->addElement('select', 'is_active', 'Is Active?', $is_active, $attributes);
				
		$this->add_action_buttons($cancel=true);		
	}
	
	//Custom validation should be added here
	function validation($data, $files) {
		return array();
	}
}
