<?php
//we are using the official bootstrap 4 tabs instead of Moodle tab as it provides more flexibility
$tabs = array();	
//a normal tab
$url = new moodle_url($CFG->wwwroot . '/local/tplus/admin/index.php', array('tab' => 'home'));
$tabs[] = new tabobject('home', $url, tp_ui_icon('home', '', '', true) . ' ' . get_string('home', 'local_tplus'), get_string('home', 'local_tplus'));

$url = new moodle_url($CFG->wwwroot . '/local/tplus/admin/organization/organization.php', array('tab' => 'organization'));
$tabs[] = new tabobject('organization', $url, tp_ui_icon('institution', '', '', true) . ' ' . get_string('organization', 'local_tplus'), get_string('organization', 'local_tplus'));

$url = new moodle_url($CFG->wwwroot . '/local/tplus/admin/index.php', array('tab' => 'configuration'));
$a = new tabobject('configuration', $url, tp_ui_icon('cog', '', '', true) . ' ' . get_string('configurations', 'local_tplus'), get_string('configuration', 'local_tplus'));
$a->extra_class = 'pull-right'; //add a pull right extra class element
$tabs[] = $a;

if (count($tabs) >= 1) {
	$tab_controls = new tabtree($tabs, $currenttab);
	echo tp_ui_tab_renderer($tab_controls->subtree); //use the custom tplus renderer to have full bootstrap tab control
//    echo $OUTPUT->render($tab_controls);	 //official Moodle renderer
}
else
	throw new moodle_exception('No tab item');