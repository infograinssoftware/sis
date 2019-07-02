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
 * Readme file for local customisations
 *
 * @package    local
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

Function Name
=============
All function name must preceed with rc_ prefix
Global variable for RC which is initialized into CFG
 => rc_is_admin			:	indicates if user is admin (init at RCYCI block)
 => rc_courses			:	an associative object of processed courses  (init at RCYCI block)
 => rc_user_type		:	indicate if user is teacher or student (from PS database)  (init at RCYCI block)


Useful Reference
================
$pix = new pix_icon('i/calendar', 'View your schedule', 'moodle', array('class' => 'iconlarge'));		
$OUTPUT->pix_url('i/course')
$OUTPUT->pix_url('icon', $mod->modname)

$PAGE->requires->css('mod/mymod/styles.css');
$PAGE->requires->js('mod/mymod/script.js');

is_siteadmin() //moodle function to check if user is admin

$url = new moodle_url('/user/profile.php', array('id' => $user->id));
$link = html_writer::link($url, $user->idnumber, array('title' => 'View User Profile'));
$DB->set_debug(true); // to debug
