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
require($CFG->dirroot.'/local/enlightencatalog/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$contextid = optional_param('contextid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$searchquery  = optional_param('search', '', PARAM_RAW);

require_login();

$context = get_context_instance(CONTEXT_SYSTEM);

$manager = true;
$manager = has_capability('local/crowd:manage', $context);

if (!$manager) {
    require_capability('local/crowd:view', $context);
}

$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog/jquery.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog/jquery.colorbox-min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog/jquery.cookie.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog/module.js') );
admin_externalpage_setup('crowd_addedit', '', null, '', array('pagelayout'=>'report'));

echo $OUTPUT->header();

$crowds = crowd_get_crowds($context->id, $page, 25, $searchquery);

$count = '';
if ($crowds['allcrowds'] > 0) {
    if ($searchquery === '') {
        $count = ' ('.$crowds['allcrowds'].')';
    } else {
        $count = ' ('.$crowds['totalcrowds'].'/'.$crowds['allcrowds'].')';
    }
}

echo $OUTPUT->heading(get_string('crowdsin', 'local_enlightencatalog').$count);

// Add search form.
$search  = html_writer::start_tag('form', array('id'=>'searchcrowdquery', 'method'=>'get'));
$search .= html_writer::start_tag('div');
$search .= html_writer::label(get_string('searchcrowd', 'local_enlightencatalog'), 'crowd_search_q'); // No : in form labels!
$search .= html_writer::empty_tag('input', array('id'=>'crowd_search_q', 'type'=>'text', 'name'=>'search', 'value'=>$searchquery));
$search .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('search', 'local_enlightencatalog')));
$search .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'contextid', 'value'=>$contextid));
$search .= html_writer::end_tag('div');
$search .= html_writer::end_tag('form');
echo $search;


// Output pagination bar.
$params = array('page' => $page);
if ($contextid) {
    $params['contextid'] = $contextid;
}
if ($search) {
    $params['search'] = $searchquery;
}
$baseurl = new moodle_url('/local/enlightencatalog/index.php', $params);
echo $OUTPUT->paging_bar($crowds['totalcrowds'], $page, 25, $baseurl);

$data = array();
foreach($crowds['crowds'] as $crowd) {
    $line = array();
    $line[] = format_string($crowd->name);
    $line[] = s($crowd->idnumber); // All idnumbers are plain text.
    $line[] = format_text($crowd->description, $crowd->descriptionformat);

    $line[] = $DB->count_records('ecatalog_crowd_members', array('crowdid'=>$crowd->id));

    if ($crowd->option_val==1) {
        $line[] = 'Visible, but not enrollable';//get_string('nocomponent', 'cohort');
    } else {
        $line[] = 'Hidden';//get_string('pluginname', $cohort->component);
    }

    $buttons = array();
    if ($manager) {
         $buttons[] = html_writer::link(new moodle_url('/local/enlightencatalog/edit.php', array('id'=>$crowd->id, 'delete'=>1)), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>get_string('delete'), 'class'=>'iconsmall')));
         $buttons[] = html_writer::link(new moodle_url('/local/enlightencatalog/edit.php', array('id'=>$crowd->id)), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>get_string('edit'), 'class'=>'iconsmall')));
         $buttons[] = html_writer::link(new moodle_url('/local/enlightencatalog/assign.php', array('id'=>$crowd->id)), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/users'), 'alt'=>get_string('assign', 'local_enlightencatalog'), 'class'=>'iconsmall')), array('class'=>'assign_cohort_link','pid'=>$crowd->id));
         $buttons[] = html_writer::tag('input','',array('value'=>new moodle_url('/local/enlightencatalog/assign.php', array('id'=>$crowd->id)),'id'=>'assign_cohort_link_val'.$crowd->id, 'type'=>'hidden'));
    }
    $line[] = implode(' ', $buttons);

    $buttons = array();
    if ($manager) {
          $buttons[] = html_writer::link(new moodle_url('/local/enlightencatalog/assign_control.php', array('id'=>$crowd->id)), html_writer::empty_tag('img', array('src'=>'pix/cr_edit.png', 'alt'=>get_string('assign', 'local_enlightencatalog'), 'class'=>'iconbig')));
    }
    $line[] = implode(' ', $buttons);

    $data[] = $line;
}
$table = new html_table();
$table->head  = array(get_string('name', 'local_enlightencatalog'), get_string('idnumber', 'local_enlightencatalog'), get_string('description', 'local_enlightencatalog'),
                      get_string('memberscount', 'local_enlightencatalog'), get_string('option_val', 'local_enlightencatalog'), get_string('edit'), get_string('crowd_assigment', 'local_enlightencatalog'));
$table->colclasses = array('leftalign name', 'leftalign id', 'leftalign description', 'leftalign size','centeralign source', 'centeralign action', 'centeralign assign');
$table->id = 'cohorts';
$table->attributes['class'] = 'admintable generaltable';
$table->data  = $data;
echo html_writer::table($table);
echo $OUTPUT->paging_bar($crowds['totalcrowds'], $page, 25, $baseurl);

if ($manager) {
    echo $OUTPUT->single_button(new moodle_url('/local/enlightencatalog/edit.php', array('contextid'=>$context->id)), get_string('add'));
}

echo $OUTPUT->footer();
