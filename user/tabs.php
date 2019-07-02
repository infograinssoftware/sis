<?php

$tabs = array();	
if($isAdmin)
{
	$url = new moodle_url('index.php', array());
	$tabs[] = new tabobject('user', $url, get_string('user'));
	$url = new moodle_url('role.php', array());
	$tabs[] = new tabobject('role', $url, get_string('role'));
}
//if($isAdmin || sis_has_access(array('suspend'), $roles))
{
	$url = new moodle_url('suspend.php', array());
	$tabs[] = new tabobject('suspend', $url, 'Suspend User');
}
if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');