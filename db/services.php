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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localtpclass
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'local_sis_ws_get_data' => array(
                'classname'   => 'local_sis_external',
                'methodname'  => 'rcws_get_data',
                'classpath'   => 'local/sis/externallib.php',
                'description' => 'Universal Method to get data from any sis module',
                'type'        => 'read',
				'ajax'		=> true,
        ),

);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'SIS Core' => array(
                'functions' => array (
						'local_sis_ws_get_data',
				), //this will be the name of the function. 
				'shortname' => 'sis', //this short name is the service name to be used for the service in the call url
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);
