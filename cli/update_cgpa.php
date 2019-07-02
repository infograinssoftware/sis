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
require_once ($CFG->dirroot.'/local/rcyci/lib/rc_ls_lib.php');
require_once($CFG->dirroot.'/local/rcyci/lib/rclib.php');

$trace->output('The process may take some time. Please be patient.....');

$trace->output('');

$trace->output('Updating cgpa to logsis');
$count = 0;
$all_rec = 0;
$students = rc_ps_get_all_user('student');
foreach($students as $stu)
{
	$id = $stu['EMPLID'];
	$student = rc_ls_get_stu_info($id);
	if($student !== false)
	{	
		$opr = rc_ls_get_student_operation($student['APP_ID']);
		if($opr === false || $opr->OPERATION != 1) //no specific operation or is not graduated (i.e. 1)
		{
			$ps = rc_ps_get_cumulative($id, $CFG->semester);
			$obj = new stdClass();
			$obj->app_id = $student['APP_ID'];
			$obj->id = $student['ID'];
			$obj->semester = $CFG->lsemester;

			$obj->semester_credit = $ps['UNT_TAKEN_GPA'];
			$obj->q_credit = $ps['UNT_TAKEN_GPA'];
			$obj->semester_earned_credit = $ps['UNT_PASSD_GPA'];
			$obj->sem_point = $ps['GRADE_POINTS'];
			$obj->gpa = $ps['CUR_GPA'];
			$obj->qhrs = $ps['TOT_TAKEN_GPA'];
			$obj->ehrs = $ps['TOT_PASSD_GPA'];
			$obj->qpts = $ps['TOT_GRADE_POINTS'];
			$obj->cgpa = $ps['CUM_GPA'];
			$obj->sem_m_credit = '0';
			$obj->sem_m_point = '0';
			$obj->sem_mgpa = '0';
			$obj->sem_m_earned_credit = '0';
			$obj->m_cum_credits = '0';
			$obj->m_cum_points = '0';
			$obj->mgpa = '0';
			$obj->m_cum_earned = '0';
			
			$trace->output('Update ' . $obj->id);
			rc_ls_save_cgpa($obj);
			$count++;
		}
	}
	$all_rec++;
}



$trace->output('');
$trace->output($all_rec . ' records checked');
$trace->output($count . ' records updated');

$trace->output('The process completed');

/** Any code ends here */
$timenow  = time();
$trace->output("");
$difftime = microtime_diff($starttime, microtime());
$trace->output("Execution took ".$difftime." seconds"); 
$trace->output("Server Time: " . date('r',$timenow));
