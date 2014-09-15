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
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_cohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/local/enlightencatalog//selector/lib.php');
require_once($CFG->dirroot.'/local/enlightencatalog//lib.php');

$id = required_param('id', PARAM_INT);

require_login();

$crowd = $DB->get_record('crowd', array('id'=>$id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_SYSTEM);

require_capability('local/enlightencatalog:manage', $context);

$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog//jquery.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog//jquery.colorbox-min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog//module.js') );

$PAGE->set_context($context);
$PAGE->set_url('/local/enlightencatalog//assign_control.php', array('id'=>$id));

$returnurl = new moodle_url('/local/enlightencatalog//index.php', array('contextid'=>$crowd->contextid));

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}

if (optional_param('save', false, PARAM_BOOL) && confirm_sesskey()) {
	$crowd->option_val = optional_param('option_val', '', PARAM_RAW);
	crowd_update_crowd($crowd);
	redirect($returnurl);
}

navigation_node::override_active_url(new moodle_url('/local/enlightencatalog//index.php', array()));
$PAGE->set_pagelayout('admin');

$PAGE->navbar->add(get_string('assign_control', 'local_enlightencatalog'));

$PAGE->set_title(get_string('assign_control', 'local_enlightencatalog'));
$PAGE->set_heading($crowd->name);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assign_control', 'local_enlightencatalog', format_string($crowd->name)));

// Get the user_selector we will need.
$existingcourseselector = new course_existing_selector('courseselect', array('crowdid'=>$crowd->id, 'accesscontext'=>$context));
$existingcatselector = new course_cat_existing_selector('catselect', array('crowdid'=>$crowd->id, 'accesscontext'=>$context));

// Process incoming user assignments to the cohort



// Print the form.
?>
<div>

  <table summary="" class="generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td>
          <p><?php print_string('potcourses', 'local_enlightencatalog'); ?></p>
	  	  <form id="assign_course_form" method="post" action="<?php echo new moodle_url('/local/enlightencatalog//assign_course.php', array('id'=>$crowd->id)) ?>">
            <input name="assign" id="assign_course_link" pid="<?php echo $crowd->id ?>" type="submit" value="<?php p(get_string('assign', 'local_enlightencatalog')); ?>" /><br />
            <?php echo html_writer::tag('input','',array('value'=>new moodle_url('/local/enlightencatalog//assign_course.php', array('id'=>$crowd->id)),'id'=>'assign_course_link_val'.$crowd->id, 'type'=>'hidden'));?>
    	  </form>
      </td>
      <td>
          <p><?php print_string('potcats', 'local_enlightencatalog'); ?></p>
	  	  <form id="assign_cat_form" method="post" action="<?php echo new moodle_url('/local/enlightencatalog//assign_category.php', array('id'=>$crowd->id)) ?>">
            <input name="assign" id="assign_cat_link" pid="<?php echo $crowd->id ?>" type="submit" value="<?php p(get_string('assign', 'local_enlightencatalog')); ?>" /><br />
            <?php echo html_writer::tag('input','',array('value'=>new moodle_url('/local/enlightencatalog//assign_category.php', array('id'=>$crowd->id)),'id'=>'assign_cat_link_val'.$crowd->id, 'type'=>'hidden'));?>
    	  </form>
      </td>
      <td colspan="2">
      </td>
      </tr>
	  <form id="optionform" method="post" action="<?php echo $PAGE->url ?>">
	  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
      <tr>
      <td colspan="2">
      </td>
      <td>
       <select name="option_val">
       	<option value="0">Hidden</option>
       	<option value="1" <?php echo ($crowd->option_val == 1 ? 'selected="selected"' : '' ) ?> >Visible, but not enrollable</option>
       </select>
      </td>
      <td>
            <input name="save" id="save" type="submit" value="<?php p(get_string('save', 'local_enlightencatalog')); ?>" /><br />
      		<input type="submit" id="back_to_list" name="cancel" value="<?php p(get_string('backtocrowds', 'local_enlightencatalog')); ?>" />
      </td>
    </tr>
    <tr>
      <td>
          <p><label for="addselect"><?php print_string('currentcourses', 'local_enlightencatalog'); ?></label></p>
          <?php  $existingcourseselector->display() ?>
      </td>
      <td>
          <p><label for="addselect"><?php print_string('currentcats', 'local_enlightencatalog'); ?></label></p>
          <?php  $existingcatselector->display() ?>
      </td>
      <td>
      </td>
      <td>
      </td>
    </tr>
    </form>
  </table>
  
</div>

<?php

echo $OUTPUT->footer();
