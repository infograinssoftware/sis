<?php

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
 * External Web Service Template
 *
 * @package    localsis
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/local/sis/lib/sis_lib.php'); //sis global library
require_once($CFG->dirroot.'/local/sis/lib/sis_ws_lib.php'); //sis web service library

class local_sis_external extends external_api 
{

///////// Universal Functions ///////////////////////
///////// Get Data Function ///////////////////////

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function sis_ws_get_data_parameters() {
        return new external_function_parameters(
                array('data' => new external_value(PARAM_TEXT, 'A valid SIS Class get data object', VALUE_DEFAULT, ''),
			)
        );
    }

    /**
     * Accept data get from sis 
     * @accept data get from sis
     */
    public static function sis_ws_get_data($data) {
        global $USER;
 
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::sis_ws_get_data_parameters(),
                array(
					'data' => $data,
				));

        //Context validation
        //OPTIONAL but in most web service it should present
//        $context = get_context_instance(CONTEXT_USER, $USER->id);
//        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
//        if (!has_capability('moodle/user:viewdetails', $context)) {
//            throw new moodle_exception('cannotviewprofile');
//        }
		
		//implement the function here
		$isError = false;
		if(isset($_POST['data']))
			$data = json_decode($_POST['data']); //decode the result. post data always received as a list. So result will be an array of object
		else if(isset($_GET['data']))
			$data = json_decode($_GET['data']); //decode the result. post data always received as a list. So result will be an array of object
		else
			$isError = true;
		//do processing
		//if successful, return 1 as status, else return 0
		$return_message = array();
		if(!$isError)
		{
			$result = rc_ws_export_data($data); //universal function to start the export process
			$return_message['status'] = '1'; //generic status field
			$return_message['result'] = $result; //if we put the result here, what ever received will be returned. Can be used for debug
		}
		else
		{
			$return_message['status'] = '0'; //generic status field
			$return_message['result'] = 'Error in web service data'; //if we put the result here, what ever received will be returned. Can be used for debug			
		}
		$return_message = json_encode($return_message); //encode it in json
		return array('message' => $return_message);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function sis_ws_get_data_returns() {
        return new external_single_structure(
                array(
					'message' => new external_value(PARAM_RAW, 'The result of get_data function'),
					)
				);
    }

///////// End Of Get Data Function ///////////////////////


}
