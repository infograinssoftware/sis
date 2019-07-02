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
 * This file contains main class for the course format Weeks
 *
 * @since     Moodle 2.0
 * @package   format_sis
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//This method will be called in every page as it is for navigation menu customization. Although we are not customizing the navigation menu
//we can use this method as a global hook that will be called to execute a global function in every page.
//This is useful to check if user needs to perform survey etc. In this way, we don't have to customize the index.php
function local_sis_extend_navigation(global_navigation $navigation) 
{
	
}
//for cron
function local_sis_cron()
{
}