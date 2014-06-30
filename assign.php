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
require_once($CFG->dirroot.'/local/crowd/selector/lib.php');
require_once($CFG->dirroot.'/local/crowd/lib.php');

$id = required_param('id', PARAM_INT);

require_login();

$crowd = $DB->get_record('crowd', array('id'=>$id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_SYSTEM);

require_capability('local/crowd:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/crowd/assign.php', array('id'=>$id));

$returnurl = new moodle_url('/local/crowd/index.php', array('contextid'=>$crowd->contextid));

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}

navigation_node::override_active_url(new moodle_url('/local/crowd/index.php', array()));
$PAGE->set_pagelayout('admin');

$PAGE->navbar->add(get_string('assign', 'local_crowd'));

$PAGE->set_title(get_string('assign', 'local_crowd'));
$PAGE->set_heading($crowd->name);

if (!crowd_is_ajax_request())echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignto', 'local_crowd', format_string($crowd->name)));

// Get the user_selector we will need.
$potentialcohortselector = new cohort_potential_selector('addselect', array('crowdid'=>$crowd->id, 'accesscontext'=>$context));
$existingcohortselector = new cohort_existing_selector('removeselect', array('crowdid'=>$crowd->id, 'accesscontext'=>$context));

// Process incoming user assignments to the cohort

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $cohortstoassign = $potentialcohortselector->get_selected_rows();
    if (!empty($cohortstoassign)) {

        foreach ($cohortstoassign as $cohort) {
            crowd_add_member($crowd->id, $cohort->id);
        }

        $potentialcohortselector->invalidate_selected_rows();
        $existingcohortselector->invalidate_selected_rows();
    }
}

// Process removing user assignments to the cohort
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $cohortstoremove = $existingcohortselector->get_selected_rows();
    if (!empty($cohortstoremove)) {
        foreach ($cohortstoremove as $removecohort) {
            crowd_remove_member($crowd->id, $removecohort->id);
        }
        $potentialcohortselector->invalidate_selected_rows();
        $existingcohortselector->invalidate_selected_rows();
    }
}

// Print the form.
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />

  <table summary="" class="generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect"><?php print_string('currentcohorts', 'local_crowd'); ?></label></p>
          <?php  $existingcohortselector->display() ?>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.s(get_string('add')); ?>" title="<?php p(get_string('add')); ?>" /><br />
          </div>

          <div id="removecontrols">
              <input name="remove" id="remove" type="submit" value="<?php echo s(get_string('remove')).'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php p(get_string('remove')); ?>" />
          </div>
      </td>
      <td id="potentialcell">
          <p><label for="addselect"><?php print_string('potcohorts', 'local_crowd'); ?></label></p>
          <?php $potentialcohortselector->display() ?>
      </td>
    </tr>
    <tr><td colspan="3" id='backcell'>
      <input type="submit" id="back_to_list" name="cancel" value="<?php p(get_string('backtocrowds', 'local_crowd')); ?>" />
    </td></tr>
  </table>
</div></form>

<?php

if (!crowd_is_ajax_request()) echo $OUTPUT->footer();
