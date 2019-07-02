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

echo ('The process may take some time. Please be patient.....');
echo "\r\n";

echo "\r\n";

echo ('Updating grade to logsis');
echo "\r\n";
echo "\r\n";

$grade_letter = rc_ls_get_gradeletter();
$grade_code = array_flip($grade_letter); //flip it to have the code
$coop_courses = rc_ls_get_coop_courses();

$count = 0;
$total_count = 0;
$not_enrol = 0;

//custom semester - change the semester below
$semester = $CFG->semester;
$lsemester = $CFG->lsemester;

$semester = '2182'; //remove this once done
$lsemester = '20182'; //remove this once done

$campus = array('YIC', 'YUC-M', 'YUC-F');


foreach($campus as $cm)
{
	$rs = rc_ps_get_all_student_enrol_rec($semester, $cm); //use this for the campus coz probably too many records. Need to break it up
	while(!$rs->EOF) 
	{
		$r = $rs->fields;
		
		//have to get the matching course in logsis
		if($r['CAMPUS'] == 'YUC-M' || $r['CAMPUS'] == 'YUC-F') //yuc no space and has -M or -F
		{
			$code = $r['SUBJECT'] . $r['CATALOG_NBR'];
			if($r['CAMPUS'] == 'YUC-M')
				$code = $code . '-M';
			else
				$code = $code . '-F';
		}
		else
		{
			$code = $r['SUBJECT'] . ' ' . $r['CATALOG_NBR'];		
		}
		//some exception (PE 001-F and PE 002-F). These courses seems to have both PE001-F and PE 001-F but student registered in the later
//		if($code == 'PE001-F')
//			$code = 'PE 001-F';
//		if($code == 'PE002-F')
//			$code = 'PE 002-F';
		//now have to find the course in logsis
		$course_code = '';
		$ls_course = rc_ls_get_course($code);
		if($ls_course !== false) //found
		{
			$course_code = $ls_course['CODE']; //remember the code
		}
		else
		{
			//if not found, then there is a possibility that there is a space for YUC or no space for YIC.
			if($r['CAMPUS'] == 'YUC-M' || $r['CAMPUS'] == 'YUC-F') //yuc no space and has -M or -F
			{
				$code = $r['SUBJECT'] . ' ' . $r['CATALOG_NBR'];
				if($r['CAMPUS'] == 'YUC-M')
					$code = $code . '-M';
				else
					$code = $code . '-F';
			}
			else
			{
				$code = $r['SUBJECT'] . $r['CATALOG_NBR'];
			}
			//now have to find the course in logsis again
			$ls_course = rc_ls_get_course($code);
			if($ls_course !== false)
			{
				$course_code = $ls_course['CODE'];
			}
			else
			{
				//if not found, then it could be EGT where it is still using YIC code, but in YUC
				$code = $r['SUBJECT'] . ' ' . $r['CATALOG_NBR'];		
				$ls_course = rc_ls_get_course($code);
				if($ls_course !== false)
				{
					$course_code = $ls_course['CODE'];
				}
				else
				{
					echo ($r['EMPLID'] . ' -> ' . $code . ' : ' . 'PS(' . $r['CRSE_GRADE_INPUT'] . ') => ' . ' - Course Not Found in Logsis');
					echo "\r\n";
				}
			}
		}
		if($course_code != '') //found
		{
			$ls_rec = rc_ls_get_student_grade($r['EMPLID'], $course_code, $lsemester); //get student grade in logsis
			if($ls_rec !== false) //found
			{
				$ls_letter = $grade_letter[$ls_rec['GRADE']]; //grade letter in logsis (A+, A, B C ...)
				$ps_letter = $r['CRSE_GRADE_INPUT']; //grade letter in peoplesoft
				if($ls_letter != $ps_letter) //logsis has different letter than ps, then we update ps to logsis
				{				
					$update = true;
					$ls_code = $grade_code[$ps_letter]; //the new grade code (0, 1, 2, 3.... ) based on peoplesoft grade
					if($update)
					{
						rc_ls_update_student_grade($ls_rec['CODE'], $ls_code);
						echo ($r['EMPLID'] . ' -> ' . $code . ' : ' . 'PS(' . $r['CRSE_GRADE_INPUT'] . ') => ' . 'LS(' . $grade_letter[$ls_rec['GRADE']] . ')-' . $ls_rec['GRADE'] . ' UPDATE => ' . $ls_code);					
						echo "\r\n";
						$count++;
					}
				}
			}
			else //course in ps, but not in logsis. Probably student not registered for the course
			{
				echo ($r['EMPLID'] . ' -> ' . $code . ' : ' . 'PS(' . $r['CRSE_GRADE_INPUT'] . ') => ' . 'Logsis Student Not Registered ' . $code . ' (' . $course_code . ')');
				echo "\r\n";
				$not_enrol++;
			}
		}
		$rs->MoveNext();
		$total_count++;
	//	if($count == 10)
	//		break;
	}
}

echo "\r\n";
echo ($total_count . ' records checked');
echo "\r\n";
echo ($count . ' records updated');
echo "\r\n";
echo ($not_enrol . ' records student not enrolled in Logsis');
echo "\r\n";


echo ('The process completed');
echo "\r\n";

/** Any code ends here */
$timenow  = time();
echo "\r\n";
$difftime = microtime_diff($starttime, microtime());
echo ("Execution took ".$difftime." seconds"); 
echo "\r\n";
echo ("Server Time: " . date('r',$timenow));
echo "\r\n";
