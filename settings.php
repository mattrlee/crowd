<?php
require_once($CFG->dirroot.'/local/crowd/settingslib.php');
if (!$ADMIN->locate('crowd_menuitem'))
{
	$ADMIN->add('root', new admin_category('crowd_menuitem', get_string('crowd_menuitem', 'local_crowd')));
}
$ADMIN->add('crowd_menuitem', new admin_externalpage('crowd_addedit', get_string('crowd_addedit','local_crowd'), "$CFG->wwwroot/local/crowd/index.php"));

$settings = $ADMIN->locate('frontpagesettings');

if ($settings)
$settings->add(new admin_setting_cccourselist_frontpage());

?>