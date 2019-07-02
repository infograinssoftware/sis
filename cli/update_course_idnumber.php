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
 * Extdb user sync script.
 *
 * This script is meant to be called from a system cronjob to
 * sync moodle user accounts with external database.
 * It is required when using internal passwords (== passwords not defined in external database).
 *
 * Sample cron entry:
 * # 5 minutes past 4am
 * 5 4 * * * sudo -u www-data /usr/bin/php /var/www/moodle/auth/db/cli/sync_users.php
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *   - If you have a large number of users, you may want to raise the memory limits
 *     by passing -d memory_limit=256M
 *   - For debugging & better logging, you are encouraged to use in the command line:
 *     -d log_errors=1 -d error_reporting=E_ALL -d display_errors=0 -d html_errors=0
 *
 * Performance notes:
 * + The code is simpler, but not as optimized as its LDAP counterpart.
 *
 * @package    auth_db
 * @copyright  2006 Martin Langhoff
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once("$CFG->libdir/clilib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('noupdate'=>false, 'verbose'=>false, 'help'=>false), array('n'=>'noupdate', 'v'=>'verbose', 'h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute user account sync with external database.
The auth_db plugin must be enabled and properly configured.

Options:
-n, --noupdate        Skip update of existing users
-v, --verbose         Print verbose progress information
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php auth/db/cli/sync_users.php

Sample cron entry:
# 5 minutes past 4am
5 4 * * * sudo -u www-data /usr/bin/php /var/www/moodle/auth/db/cli/sync_users.php
";

    echo $help;
    die;
}

if (!is_enabled_auth('db')) {
    cli_error('auth_db plugin is disabled, synchronisation stopped', 2);
}

if (empty($options['verbose'])) {
    $trace = new null_progress_trace();
} else {
    $trace = new text_progress_trace();
}

$update = empty($options['noupdate']);

$starttime = microtime();
$timenow  = time();

$trace->output("");
$trace->output("Server Time: " . date('r',$timenow));
$trace->output("");
/** Any code starts here */
require_once($CFG->dirroot.'/local/rcyci/lib/rc_ps_lib.php');

$trace->output('The process may take some time. Please be patient.....');

$trace->output('');
$trace->output('First round, check name against the CLASS_NBR');
//first we check the class_nbr against the name
$ps_sections = rc_ps_get_moodle_sections('M_NAME'); 
$courses = $DB->get_records('course', array());

foreach ($courses as $course) 
{
	//try to get the section in peoplesoft
	if(isset($ps_sections[$course->shortname])) //found it
	{
		$ps_section = $ps_sections[$course->shortname];
		if($course->idnumber != $ps_section['CLASS_NBR']) //same course, but the CLASS_NBR has changed
		{
			//update the class_nbr in Moodle
			$sql = "UPDATE m_course set idnumber = '".$ps_section['CLASS_NBR']."' WHERE id = $course->id";
			$DB->execute($sql);
			$trace->output('Update Class NBR: ' . $course->shortname . ' CLASS_NBR from ' . $course->idnumber . ' to ' . $ps_section['CLASS_NBR']);
		}
		//also, check if the fullname has changed
		if($course->fullname != $ps_section['COURSE_TITLE_LONG']) //same course, but the FULLNAME has changed
		{
			//update the fullname in Moodle
			$sql = "UPDATE m_course set fullname = '".$ps_section['COURSE_TITLE_LONG']."' WHERE id = $course->id";
			$DB->execute($sql);
			$trace->output('Update Class Full Name: ' . $course->shortname . ' Full Name from ' . $course->fullname . ' to ' . $ps_section['COURSE_TITLE_LONG']);
		}
	}
	else
	{
		//shall we delete it?
	}
}
//next we check the name against the class nbr. This could happen if the section name was not defined properly in peoplesoft
$trace->output('');
$trace->output('Second round, check CLASS_NBR against the name');
$ps_sections = rc_ps_get_moodle_sections('CLASS_NBR'); 
foreach ($courses as $course) 
{
	//try to get the section in peoplesoft
	if(isset($ps_sections[$course->idnumber])) //found it
	{
		$ps_section = $ps_sections[$course->idnumber];
		if($course->shortname != $ps_section['M_NAME']) //same course, but the CLASS_NBR has changed
		{
			//update the class_nbr in Moodle
			$sql = "UPDATE m_course set shortname = '".$ps_section['M_NAME']."' WHERE id = $course->id";
			$DB->execute($sql);
			$trace->output('Update Course Name from ' . $course->shortname . ' to ' . $ps_section['M_NAME']);
		}
	}
}


$trace->output('The process completed');

/** Any code ends here */
$timenow  = time();
$trace->output("");
$difftime = microtime_diff($starttime, microtime());
$trace->output("Execution took ".$difftime." seconds"); 
$trace->output("Server Time: " . date('r',$timenow));
