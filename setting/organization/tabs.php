<?php

$tabs = array();	
if($isAdmin)
{
	$url = new moodle_url('index.php', array());
	$tabs[] = new tabobject('organization', $url, 'Organization');
	$url = new moodle_url('section.php', array());
	$tabs[] = new tabobject('section', $url, 'Section');
}

{
	$url = new moodle_url('campus.php', array());
	$tabs[] = new tabobject('campus', $url, 'Campus');
}
{
	$url = new moodle_url('institute.php', array());
	$tabs[] = new tabobject('institute', $url, 'Institute');
}
if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
    echo $OUTPUT->render($tab_controls);	 //we delay the tab output
}
else
	throw new moodle_exception('Invalid Access');