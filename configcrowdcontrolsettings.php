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
require_once($CFG->dirroot.'/local/enlightencatalog/selector/lib.php');
require_once($CFG->dirroot.'/local/enlightencatalog/lib.php');


require_login();


$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog/jquery.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog/jquery.colorbox-min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/enlightencatalog/module.js') );

$PAGE->set_context($context);

navigation_node::override_active_url(new moodle_url('/local/enlightencatalog/index.php', array()));
$PAGE->set_pagelayout('admin');

$PAGE->navbar->add(get_string('config_control', 'local_enlightencatalog'));

$PAGE->set_title(get_string('config_control', 'local_enlightencatalog'));
$PAGE->set_heading($crowd->name);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('config_control', 'local_enlightencatalog', format_string($crowd->name)));
?>
<table>
	<tr>
		<td>
			<?php print_string('collapse_expand_text','local_enlightencatalog') ?> 
			<input type="checkbox" name="chkcollapsed" id="chkcollapsed"/>
		</td>
	</tr>
	<tr>
		<td>
			<?php print_string('front_icon_size','local_enlightencatalog') ?>
			<input type="text" id="txtlength" name="txtlength"/>&nbsp;&nbsp;X&nbsp;&nbsp;
			<input type="text" id="txtwidth" name="txtwidth"/>&nbsp;&nbsp;
			<?php print_string('pixels','local_enlightencatalog') ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php print_string('font_color','local_enlightencatalog') ?>
			<input type="text" id="txtfontcolor" name="txtfontcolor"/>
		</td>
	</tr>
	<tr>
		<td>
			<?php print_string('link_color','local_enlightencatalog') ?>
			<input type="text" id="txtlinkcolor" name="txtlinkcolor"/>
		</td>
	</tr>
	<tr>
		<td><?php print_string('font_style','local_enlightencatalog') ?></td>
	</tr>
	<tr>
		<td><?php print_string('include_icon_border','local_enlightencatalog') ?></td>
	</tr>
	<tr>
		<td><?php print_string('default_category_icon','local_enlightencatalog') ?></td>
	</tr>
	<tr>
		<td><?php print_string('default_course_icon','local_enlightencatalog') ?></td>
	</tr>
	<tr>
		<td><?php print_string('default_program_icon','local_enlightencatalog') ?></td>
	</tr>
	<tr>
		<td><?php print_string('description_bubble_off','local_enlightencatalog') ?></td>
	</tr>
	<tr>
		<td><?php print_string('description_bubble_dimensions','local_enlightencatalog') ?></td>
	</tr>
</table>