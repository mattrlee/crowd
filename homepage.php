<?php
	require('../../config.php');
	require($CFG->dirroot.'/local/enlightencatalog//lib.php');
	require_once($CFG->dirroot.'/local/enlightencatalog//settingslib.php');
	$cat_list = get_tree_of_courses();
	$course_list = get_list_of_courses();
	$settings = new admin_setting_cccourselist_frontpage();
	$settings = $settings->get_setting();
?>
<input type="hidden" id="crowd_wwroot" value=<?php echo $CFG->wwwroot; ?> /><?php
foreach ($settings as $setting) {
  switch ($setting) {
	case 'categories':
	if (count($cat_list)) {
?>
<div class="crowd_av_categories">Available categories</div>
<div class="crowd_arrow_up0"></div>
<div class="crowd_arrow_up1"></div>
<div class="crowd_arrow_up2"></div>
<div class="crowd_arrow_up3"></div>
<div class="crowd_av_categories_content"><?php 
display_categories($cat_list);
?><div class="crowd_clear"></div>
</div>
<?php
	}
	break;
	case 'courses':
	if (count($course_list)) {
?>
<div class="crowd_av_courses">Available courses</div>
<div class="crowd_av_courses_content">
<?php 
display_courses($course_list);
?> 
<div class="crowd_clear"></div>
</div><?php 
	}
	break;
  }
}	
?>
