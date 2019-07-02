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
require_once($CFG->dirroot.'/local/rcyci/lib/rclib.php');

$trace->output('The process may take some time. Please be patient.....');

$trace->output('');

$trace->output('Deleting absence recorded before the enrollment date');

//first, get all the attendance records in the current semester
$recs = $DB->get_records('si_absence', array('strm' => $CFG->semester));
$count = 0;
foreach($recs as $rec)
{
	//get the enrollment record in peoplesoft
	if($ps_rec = rc_ps_get_student_enrol($rec->emplid, $rec->class_nbr, $rec->strm));
	{
		$m_dt = $rec->class_attend_dt_php;
		$p_dt = strtotime($ps_rec['ENRL_ADD_DT']);
		if($p_dt >= $m_dt) //enrollment date is larger than the attendance date, means we need to delete
		{
			$DB->delete_records('si_absence', array('id' => $rec->id));
			$trace->output('Delete: ' . $ps_rec['EMPLID'] . 
							' c_nbr: ' . $ps_rec['CLASS_NBR'] . 
							' Abs date : ' . date('d-M-Y', $m_dt) . 
							' Enrl date : ' . date('d-M-Y', $p_dt)
							);
			$count++;
		}
	}
}

$trace->output('Total records checked: ' . count($recs));
$trace->output('Total records deleted: ' . $count);

$trace->output('The process completed');
$trace->output($count . ' sections processed');

/** Any code ends here */
$timenow  = time();
$trace->output("");
$difftime = microtime_diff($starttime, microtime());
$trace->output("Execution took ".$difftime." seconds"); 
$trace->output("Server Time: " . date('r',$timenow));
