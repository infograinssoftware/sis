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
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contain all the global functions for RCYCI module
*/

// This is the library for global RCYCI functions 
defined('MOODLE_INTERNAL') || die();

//when subfield is used, it means we want the specific value
function sis_lookup_get_lookup($category, $subcategory, $institute)
{
	global $DB;
	if($subcategory != '')
	{
		$sql = "select * from {si_lookup} where category = ? and subcategory = ?";
		$params = array($category, $subcategory);
		$arr = array();
		if($result = $DB->get_records_sql($sql, $params))
		{
			foreach($result as $r)
			{
				$arr[$r->value] = $r->value;
			}
		}
		return $arr;
	}
}

//when subfield is used, it means we want the specific value
function sis_lookup_active()
{
	$options = array(
		'A' => 'Active',
		'I' => 'Inactive',
	);
	return $options;
}

