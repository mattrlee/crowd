<?php
	require('../../config.php');
	require($CFG->dirroot.'/local/enlightencatalog/lib.php');
	require_once($CFG->dirroot.'/local/enlightencatalog/settingslib.php');
	$cat_list = get_tree_of_courses();
	$course_list = get_list_of_courses();
	$settings = new admin_setting_cccourselist_frontpage();
	$settings = $settings->get_setting();
	$progs_list = get_list_of_progs();
// 	$expanded = getCollpasedOrExpanded();
	$expanded = getSettingsFromDB('crowd_expand_collapse');
	$fontColor = getSettingsFromDB('font_color');
	$linkColor = getSettingsFromDB('link_color');
	$iconBorder = getSettingsFromDB('include_icon_border');
	$fontSize = getSettingsFromDB('fontsize');	
	$outlineColor = getSettingsFromDB('outlinecolor');
	$backgroundColor = getSettingsFromDB('backgroundcolor');
	$descriptionBubble = getSettingsFromDB('description_bubble_off');
	$bubbleHeight = getSettingsFromDB('description_bubble_dimensions_length');
	$bubbleWidth = getSettingsFromDB('description_bubble_dimensions_width');
	
	//echo $descriptionBubble;//exit;
	
// 	$course_set_list = getListOfCourseSet();
// 	foreach ($course_set_list as $course_set_l){
// 	echo $course_set_l['label'];
// 	}
?>
<style>

	.tooltipster-default .tooltipster-content {
	height:<?php echo $bubbleHeight;?>px;
	width:<?php echo $bubbleWidth;?>px;
	background-color:<?php echo $backgroundColor;?>;
	border:0px solid <?php echo $outlineColor;?>;
}
.tooltipster-default {
	border-radius: 5px; 
	border: 2px solid <?php echo $outlineColor;?>;
	background: <?php echo $backgroundColor;?>;
	color: #fff;	
}

	<?php
	if(!$descriptionBubble)
	{?>
	.tooltipster-default .tooltipster-content {display:"";}
	.tooltipster-arrow {display:"";}
	<?php }else{
		?>
		.tooltipster-default .tooltipster-content {display:none;}
		.tooltipster-default {border : none !important;}
		.tooltipster-arrow {display:none;}
		<?php }?>
		</style>
		
		
<input type="hidden" id="crowd_wwroot" value=<?php echo $CFG->wwwroot; ?> /><?php
foreach ($settings as $setting) {
  switch ($setting) {
	case 'categories':
	if (count($cat_list)) {
?>
<div class="crowd_av_categories">Available categories</div>
<!-- <div class="crowd_arrow_up0"></div> -->
<!-- <div class="crowd_arrow_up1"></div> -->
<!-- <div class="crowd_arrow_up2"></div> -->
<!-- <div class="crowd_arrow_up3"></div> -->
<div class="crowd_av_categories_content">
<?php 
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
  	case 'programs':
	if (count($progs_list)) {
?>
<div class="crowd_av_courses">Available programs</div>
<div class="crowd_arrow_up4"></div>

<div class="crowd_av_courses_content">
<?php 
 display_prgss($progs_list);
?> 
<div class="crowd_clear"></div>
</div><?php 
	}
	break;
  }
}	
?>