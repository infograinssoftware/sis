<?php

$tabs = array();	
$url = new moodle_url('user.php', array('id' => $id));
$tabs[] = new tabobject('recepient', $url, 'Recepients');
$url = new moodle_url('user_add.php', array('id' => $id));
$tabs[] = new tabobject('add', $url, 'Add Recepients');

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');