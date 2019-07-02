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

echo ("\r\n");
echo ("Server Time: " . date('r',$timenow));
echo ("\r\n");
/** Any code starts here */
require_once($CFG->dirroot.'/local/rcyci/lib/rc_ps_lib.php');
require_once ($CFG->dirroot.'/local/rcyci/lib/rc_ls_lib.php');
require_once($CFG->dirroot.'/local/rcyci/lib/rclib.php');

echo ('The process may take some time. Please be patient.....');
echo ("\r\n");

echo ('Start In Out Operation Update');
echo ("\r\n");


$count = 1;
$rs = rc_ps_get_operation('');
$processed = array();
/////CHANGE TO TRUE IF YOU WANT TO LET IT DO THE UPDATE
$performUpdate = true;
////////////////////////////////////////////////////////
$semester = rc_ls_get_current_semester();

foreach($rs as $rec)
{
//	break;/////temporary disable it
	if(!isset($processed[$rec['EMPLID']]))
	{
		//get the student latest operation in logsis
		$app_id = rc_ls_get_app_id($rec['EMPLID']);
		if($app_id != null) //has record in logsis
		{
			$ls_op = rc_ls_get_student_operation($app_id, $semester);
			$mapping = rc_ps_operation_mapping(trim($rec['PROG_STATUS']), trim($rec['PROG_ACTION']), trim($rec['PROG_REASON']));
			if($mapping != '')
			{
				$str = $count . '. ' . $rec['EMPLID'] . ' (' . $app_id . ') :: (' . $rec['PROG_STATUS'] . ') (' . 
					   $rec['PROG_ACTION'] . ') (' . $rec['PROG_REASON'] . ') [' . $mapping . '] (' . $rec['EFFECTIVE_DT'] . ') <=> ' . 
					   $ls_op->OPERATION . ' (' . $ls_op->OPERATION_TYPE . ')' . ' (' . date('d-M-Y', strtotime($ls_op->ACTION_DATE)) . ')'; 
				//student is active in PS but Out in Logsis or vice versa
				if(($rec['PROG_STATUS'] == 'AC' && $ls_op->OPERATION_TYPE == 'O') || ($rec['PROG_STATUS'] != 'AC' && $ls_op->OPERATION_TYPE == 'I')) 
				{
					if($ls_op->OPERATION_TYPE == 'O') //peoplesoft is active but in logsis it is not active
					{
//						if($ls_op->OPERATION != 1) //only fix if logsis is not 1, i.e. graduation. Since graduation is done in logsis, if a student is graduated in logsis, we must not fix anything
						{
							//if logsis latest operation has larger timestamp than peoplesoft, put a remark
							if($ls_op->ACTION_DATE_SORT < $rec['DT_SORT']) //we only update if the transaction date in logsis is smaller than that of peoplesoft
							{
								//need to make sure that student has no record in si_stu_operation
								if(rc_ls_check_operation_exist($app_id, 'I')) //I to make student from in active to active
									$str = $str . ' *update failed due to similar operation already exist in the current semester';
								else
								{
									//make the student active
									if($performUpdate) 
									{
										if(rc_ls_is_effective($rec['EFFECTIVE_DT']))
										{
											if(rc_ls_make_student_active($app_id, $mapping, $rec['EFFECTIVE_DT']))
												$str = $str . ' *updated';
											else	
												$str = $str . ' error in update';
										}
										else
											$str = $str . ' (update pending until effective date reached)';
									}
								}
							}							
							else
								$str = $str . ' *not updated';
							echo ($str);
							echo ("\r\n");
							$count++;
						}
					}
					else //peoplesoft is not active but logsis is active
					{
						//if logsis latest operation has larger timestamp than peoplesoft, put a remark
						if($ls_op->ACTION_DATE_SORT <= $rec['DT_SORT'] && $rec['PROG_STATUS'] != 'CM') //we only update if the transaction date in logsis is smaller than that of peoplesoft and also it is not graduation in PS
						{
							//need to make sure that student has no record in si_stu_operation
							if(rc_ls_check_operation_exist($app_id, 'O')) //I to make student from in active to active
								$str = $str . ' #update failed due to similar operation already exist in the current semester';
							else
							{
								$str = $str . ' #updated';
								//make the student inactive
								if($performUpdate) 
								{
									if(rc_ls_is_effective($rec['EFFECTIVE_DT']))
										rc_ls_make_student_not_active($app_id, $mapping, $rec['EFFECTIVE_DT']);
									else
										$str = $str . ' (update pending until effective date reached)';
								}
							}
						}
						else
							$str = $str . ' #not updated';
						$count++;
						echo($str);
						echo ("\r\n");
					}
				}
			}
			$processed[$rec['EMPLID']] = 1;
		}
	}
}
$legend = "* PS active but LOGSIS not active \r\n# PS not active but LOGSIS active \r\n";
echo $legend;
echo ("\r\n");
echo ("\r\n");

echo ('The process completed');
echo ("\r\n");
echo (($count - 1) . ' records updated');
echo ("\r\n");

/** Any code ends here */
$timenow  = time();
echo ("\r\n");
$difftime = microtime_diff($starttime, microtime());
echo ("Execution took ".$difftime." seconds"); 
echo ("\r\n");
echo ("Server Time: " . date('r',$timenow));
echo ("\r\n");
