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
require($CFG->dirroot.'/local/enlightencatalog//lib.php');
require($CFG->dirroot.'/local/enlightencatalog//edit_form.php');

$id        = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);

require_login();

$category = null;
if ($id) {
    $crowd = $DB->get_record('crowd', array('id'=>$id), '*', MUST_EXIST);
    $context = get_context_instance(CONTEXT_SYSTEM);
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    if ($context->contextlevel != CONTEXT_MODULE and $context->contextlevel != CONTEXT_SYSTEM) {
        print_error('invalidcontext');
    }
    
    $crowd = new stdClass();
    $crowd->id          = 0;
    $crowd->contextid   = $context->id;
    $crowd->name        = '';
    $crowd->description = '';
}

require_capability('local/enlightencatalog:manage', $context);

$returnurl = new moodle_url('/local/enlightencatalog//index.php', array('contextid'=>$context->id));

$PAGE->set_context($context);
$PAGE->set_url('/local/enlightencatalog//edit.php', array('contextid'=>$context->id, 'id'=>$crowd->id));
$PAGE->set_context($context);

navigation_node::override_active_url(new moodle_url('/local/enlightencatalog//index.php', array()));
$PAGE->set_pagelayout('admin');

if ($delete and $crowd->id) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        crowd_delete_crowd($crowd);
        redirect($returnurl);
    }
    $strheading = get_string('delcrowd', 'local_enlightencatalog');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
//    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/enlightencatalog//edit.php', array('id'=>$crowd->id, 'delete'=>1, 'confirm'=>1,'sesskey'=>sesskey()));
    $message = get_string('delconfirm', 'local_enlightencatalog', format_string($crowd->name));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$editoroptions = array('maxfiles'=>0, 'context'=>$context);
if ($crowd->id) {
    // Edit existing.
    $crowd = file_prepare_standard_editor($crowd, 'description', $editoroptions, $context);
    $strheading = get_string('editcrowd', 'local_enlightencatalog');

} else {
    // Add new.
    $crowd = file_prepare_standard_editor($crowd, 'description', $editoroptions, $context);
    $strheading = get_string('addcrowd', 'local_enlightencatalog');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add($strheading);

$editform = new crowd_edit_form(null, array('editoroptions'=>$editoroptions, 'data'=>$crowd));

if ($editform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $editform->get_data()) {
    $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $context);

    if ($data->id) {
        crowd_update_crowd($data);
    } else {
        crowd_add_crowd($data);
    }

    // Use new context id, it could have been changed.
    redirect(new moodle_url('/local/enlightencatalog//index.php', array('contextid'=>$data->contextid)));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
echo $editform->display();
echo $OUTPUT->footer();

