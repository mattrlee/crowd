<?php
require_once($CFG->dirroot.'/local/enlightencatalog/settingslib.php');
if (!$ADMIN->locate('crowd_menuitem'))
{
	$ADMIN->add('root', new admin_category('crowd_menuitem', get_string('crowd_menuitem', 'local_enlightencatalog')));
}
$ADMIN->add('crowd_menuitem', new admin_externalpage('crowd_addedit', get_string('crowd_addedit','local_enlightencatalog'), "$CFG->wwwroot/local/enlightencatalog/index.php"));

$settings = $ADMIN->locate('frontpagesettings');

if ($settings)
$settings->add(new admin_setting_cccourselist_frontpage());


if ($hassiteconfig) { // needs this condition or there is error on login page

	$settings = new admin_settingpage('local_enlightencatalog', 'Crowd Control');
	$ADMIN->add('crowd_menuitem', $settings);

	
	 //add various options here...
	//Example of adding a text field as a configuration option:
	$settings->add(new admin_setting_configcheckbox('crowd_expand_collapse', get_string('collapse_expand_text','local_enlightencatalog'),
			get_string('collapse_expand_text','local_enlightencatalog'), 1));
	$settings->add(new admin_setting_configtext('front_icon_size_length', new lang_string('front_icon_size_length', 'local_enlightencatalog'), new lang_string('front_icon_size_length','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_configtext('front_icon_size_width', new lang_string('front_icon_size_width', 'local_enlightencatalog'), new lang_string('front_icon_size_width','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_configtext('fontsize', new lang_string('fontsize', 'local_enlightencatalog'), new lang_string('fontsize','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_configtext('font_color', new lang_string('font_color', 'local_enlightencatalog'), new lang_string('font_color','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_configtext('link_color', new lang_string('link_color', 'local_enlightencatalog'), new lang_string('link_color','local_enlightencatalog'), ''));
	
	
// 	$choices =    Array    ("Arial","Arial Black"," Arial Narrow"," Arial Rounded MT Bold","Avant Garde","Baskerville"," Big Caslon"," Bodoni MT"," Book Antiqua",
// 			"Comic Sans MS"," cursive","Charcoal","Georgia","Impact", " Lucida Console"," Helvetica","Times New Roman","Verdana"," sans-serif");
// 	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
// 	$settings->add($setting);
	
	 $options =   array("Arial"=>"Arial","Arial Black"=>"Arial Black"," Arial Narrow"=>" Arial Narrow"," Arial Rounded MT Bold"=>" Arial Rounded MT Bold","Avant Garde"=>"Avant Garde","Baskerville"=>"Baskerville"," Big Caslon"=>" Big Caslon"," Bodoni MT"=>" Bodoni MT"," Book Antiqua"=>" Book Antiqua",
            "Comic Sans MS"=>"Comic Sans MS"," cursive"=>" cursive","Charcoal"=>"Charcoal","Georgia"=>"Georgia","Impact"=>"Impact", " Lucida Console"=>" Lucida Console"," Helvetica"=>" Helvetica","Times New Roman"=>"Times New Roman","Verdana"=>"Verdana"," sans-serif"=>" sans-serif");
	$settings->add(new admin_setting_configselect('font_style',new lang_string('font_style','local_enlightencatalog'),new lang_string('font_style', 'local_enlightencatalog'),0,$options));
	
	
	
	$settings->add(new admin_setting_configcheckbox('include_icon_border', get_string('include_icon_border','local_enlightencatalog'),
			get_string('include_icon_border','local_enlightencatalog'), 0));
	
// 	$settings->add(new admin_setting_configfile('choosefile', get_string('choosefile','local_enlightencatalog'),
// 			get_string('choosefile','local_enlightencatalog'), '' ));
	
	
// 	$settings->add(new admin_setting_configfile('choosefile', new lang_string('choosefile', 'local_enlightencatalog'),  
// 			new lang_string('choosefile', 'local_enlightencatalog'),''));
	
// 	$settings->add(new admin_setting_configfilepicker('choosefilecategory', new lang_string('choosefilecategory', 'local_enlightencatalog'),
// 			new lang_string('choosefilecategory', 'local_enlightencatalog')));
// 	$settings->add(new admin_setting_configfilepicker('choosefilecourse', new lang_string('choosefilecourse', 'local_enlightencatalog'),
// 			new lang_string('choosefilecourse', 'local_enlightencatalog')));
// 	$settings->add(new admin_setting_configfilepicker('choosefileprogram', new lang_string('choosefileprogram', 'local_enlightencatalog'),
// 			new lang_string('choosefileprogram', 'local_enlightencatalog')));
	
	
	
	
	$settings->add(new admin_setting_configcheckbox('description_bubble_off', get_string('description_bubble_off','local_enlightencatalog'),
			get_string('description_bubble_off','local_enlightencatalog'), 1));
	
	
	$settings->add(new admin_setting_configtext('description_bubble_dimensions_length', new lang_string('description_bubble_dimensions_length', 'local_enlightencatalog'), new lang_string('description_bubble_dimensions_length','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_configtext('description_bubble_dimensions_width', new lang_string('description_bubble_dimensions_width', 'local_enlightencatalog'), new lang_string('description_bubble_dimensions_width','local_enlightencatalog'), ''));
	
	$settings->add(new admin_setting_confightmleditor('choosefilecategory', new lang_string('choosefilecategory', 'local_enlightencatalog'), new lang_string('choosefilecategory','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_confightmleditor('choosefilecourse', new lang_string('choosefilecourse', 'local_enlightencatalog'), new lang_string('choosefilecourse','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_confightmleditor('choosefileprogram', new lang_string('choosefileprogram', 'local_enlightencatalog'), new lang_string('choosefileprogram','local_enlightencatalog'), ''));
	
	$settings->add(new admin_setting_configtext('backgroundcolor', new lang_string('backgroundcolor', 'local_enlightencatalog'), new lang_string('backgroundcolor','local_enlightencatalog'), ''));
	$settings->add(new admin_setting_configtext('outlinecolor', new lang_string('outlinecolor', 'local_enlightencatalog'), new lang_string('outlinecolor','local_enlightencatalog'), ''));

 
 }

?>