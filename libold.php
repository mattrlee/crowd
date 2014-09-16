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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/filelib.php');

if ($_SERVER['PHP_SELF'] == parse_url($CFG->wwwroot, PHP_URL_PATH).'/index.php') {
	global $PAGE, $DB, $USER;

	$visibility = 1;
	if ($visibility) {
		$PAGE->requires->css('/local/enlightencatalog/homepage_overrider.css'); 
		$PAGE->requires->css('/local/enlightencatalog/tooltipster.css'); 
		$PAGE->requires->js('/local/enlightencatalog/jquery.min.js', true); 
		$PAGE->requires->js('/local/enlightencatalog/jquery.tooltipster.min.js', true); 
		$PAGE->requires->js('/local/enlightencatalog/jquery.colorbox-min.js', true); 
		$PAGE->requires->js('/local/enlightencatalog/homepage_overrider.js', true); 
	}
}

/**
 * Add new cohort.
 *
 * @param  stdClass $cohort
 * @return int new cohort id
 */
function crowd_add_crowd($crowd) {
    global $DB;

    if (!isset($crowd->name)) {
        throw new coding_exception('Missing crowd name in crowd_add_crowd().');
    }
    if (!isset($crowd->idnumber)) {
        $crowd->idnumber = NULL;
    }
    if (!isset($crowd->description)) {
        $crowd->description = '';
    }
    if (!isset($crowd->descriptionformat)) {
        $crowd->descriptionformat = FORMAT_HTML;
    }
    if (empty($crowd->option_val)) {
        $crowd->option_val = 1;
    }
    if (!isset($crowd->timecreated)) {
        $crowd->timecreated = time();
    }
    if (!isset($crowd->timemodified)) {
        $crowd->timemodified = $crowd->timecreated;
    }

    $crowd->id = $DB->insert_record('crowd', $crowd);

    events_trigger('crowd_added', $crowd);

    return $crowd->id;
}

/**
 * Update existing crowd.
 * @param  stdClass $crowd
 * @return void
 */
function crowd_update_crowd($crowd) {
    global $DB;
    if ($crowd->option_val!=1) $crowd->option_val=0;
    $crowd->timemodified = time();
    $DB->update_record('crowd', $crowd);

    events_trigger('crowd_updated', $crowd);
}

/**
 * Delete crowd.
 * @param  stdClass $crowd
 * @return void
 */
function crowd_delete_crowd($crowd) {
    global $DB;

    $DB->delete_records('ecatalog_crowd_course', array('crowdid'=>$crowd->id));
    $DB->delete_records('ecatalog_crowd_course_cats', array('crowdid'=>$crowd->id));
    $DB->delete_records('ecatalog_crowd_members', array('crowdid'=>$crowd->id));
    $DB->delete_records('crowd', array('id'=>$crowd->id));

    events_trigger('crowd_deleted', $crowd);
}

function crowd_add_member($crowdid, $cohortid) {
    global $DB;
    if ($DB->record_exists('ecatalog_crowd_members', array('crowdid'=>$crowdid, 'cohortid'=>$cohortid))) {
        // No duplicates!
        return;
    }
    $record = new stdClass();
    $record->crowdid  = $crowdid;
    $record->cohortid    = $cohortid;
    $record->timeadded = time();
    $DB->insert_record('ecatalog_crowd_members', $record);

    events_trigger('crowd_member_added', (object)array('crowdid'=>$crowdid, 'cohortid'=>$cohortid));
}

function crowd_remove_member($crowdid, $cohortid) {
    global $DB;
    $DB->delete_records('ecatalog_crowd_members', array('crowdid'=>$crowdid, 'cohortid'=>$cohortid));

    events_trigger('crowd_member_removed', (object)array('crowdid'=>$crowdid, 'cohortid'=>$cohortid));
}

function crowd_add_course($crowdid, $courseid) {
    global $DB;
    if ($DB->record_exists('ecatalog_crowd_course', array('crowdid'=>$crowdid, 'courseid'=>$courseid))) {
        // No duplicates!
        return;
    }
    $record = new stdClass();
    $record->crowdid  = $crowdid;
    $record->courseid    = $courseid;
    $record->timeadded = time();
    $DB->insert_record('ecatalog_crowd_course', $record);

    events_trigger('ecatalog_crowd_course_added', (object)array('crowdid'=>$crowdid, 'courseid'=>$courseid));
}

function crowd_remove_course($crowdid, $courseid) {
    global $DB;
    $DB->delete_records('ecatalog_crowd_course', array('crowdid'=>$crowdid, 'courseid'=>$courseid));

    events_trigger('ecatalog_crowd_course_removed', (object)array('crowdid'=>$crowdid, 'courseid'=>$courseid));
}

function crowd_add_category($crowdid, $catid) {
    global $DB;
    if ($DB->record_exists('ecatalog_crowd_course_cats', array('crowdid'=>$crowdid, 'coursecategoryid'=>$catid))) {
        // No duplicates!
        return;
    }
    $record = new stdClass();
    $record->crowdid  = $crowdid;
    $record->coursecategoryid    = $catid;
    $record->timeadded = time();
    $DB->insert_record('ecatalog_crowd_course_cats', $record);

    events_trigger('crowd_category_added', (object)array('crowdid'=>$crowdid, 'coursecategoryid'=>$catid));
}

function crowd_remove_category($crowdid, $catid) {
    global $DB;
    $DB->delete_records('ecatalog_crowd_course_cats', array('crowdid'=>$crowdid, 'coursecategoryid'=>$catid));

    events_trigger('crowd_category_removed', (object)array('crowdid'=>$crowdid, 'coursecategoryid'=>$catid));
}

/**
 * Is this user a crowd member?
 * @param int $crowdid
 * @param int $cohortid
 * @return bool
 */
function crowd_is_member($crowdid, $cohortid) {
    global $DB;

    return $DB->record_exists('ecatalog_crowd_members', array('crowdid'=>$crowdid, 'userid'=>$cohortid));
}



/**
 * Get all the crowds defined in given context.
 *
 * @param int $contextid
 * @param int $page number of the current page
 * @param int $perpage items per page
 * @param string $search search string
 * @return array    Array(totalcrowds => int, crowds => array, allcrowds => int)
 */
function crowd_get_crowds($contextid, $page = 0, $perpage = 25, $search = '') {
    global $DB;

    // Add some additional sensible conditions
    $tests = array('contextid = ?');
    $params = array($contextid);

    if (!empty($search)) {
        $conditions = array('name', 'idnumber', 'description');
        $searchparam = '%' . $DB->sql_like_escape($search) . '%';
        foreach ($conditions as $key=>$condition) {
            $conditions[$key] = $DB->sql_like($condition, "?", false);
            $params[] = $searchparam;
        }
        $tests[] = '(' . implode(' OR ', $conditions) . ')';
    }
    $wherecondition = implode(' AND ', $tests);

    $fields = "SELECT *";
    $countfields = "SELECT COUNT(1)";
    $sql = " FROM {ecatalog}
             WHERE $wherecondition";
    $order = " ORDER BY name ASC, idnumber ASC";
    $allcrowds = $DB->count_records('crowd', array('contextid'=>$contextid));
    $totalcrowds = $DB->count_records_sql($countfields . $sql, $params);
    $crowds = $DB->get_records_sql($fields . $sql . $order, $params, $page*$perpage, $perpage);

    return array('totalcrowds' => $totalcrowds, 'crowds' => $crowds, 'allcrowds'=>$allcrowds);
}

function crowd_is_ajax_request() {
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		return true;
	} else {
		return false;
	}
}

function get_tree_of_courses($parent_id = 0, $parent_name = '') {
    global $DB, $USER;
    //Note: November 2013 - Jeff King (kingjj envisiontel)
    //I added 'sortorder' and ORDER BY sortorder so we can display things in the correct order.
    //Also added "visibile" to skip over visible=0 records
	$sql = "SELECT id, name, description, parent, coursecount, sortorder, visible, SUM(access) as access from ( 
			(SELECT cc.id, cc.name, cc.description, parent, coursecount, cc.sortorder, cc.visible, 1 as access FROM {course_categories} as cc 
			JOIN {ecatalog_crowd_course_cats} as ccc ON cc.id = ccc.coursecategoryid 
			JOIN {ecatalog_crowd_members} AS cm ON cm.crowdid = ccc.crowdid
			JOIN {ecatalog} AS cr ON cm.crowdid = cr.id
			JOIN {cohort_members} AS chm ON chm.cohortid = cm.cohortid
			JOIN {user} AS u ON u.id = chm.userid
			WHERE u.id ={$USER->id} and cc.parent = {$parent_id}) UNION 
			(SELECT cc.id, cc.name, cc.description, parent, coursecount, cc.sortorder, cc.visible, 0 as access FROM {course_categories} as cc 
			JOIN {ecatalog_crowd_course_cats} as ccc ON cc.id = ccc.coursecategoryid 
			JOIN {ecatalog} AS cr ON ccc.crowdid = cr.id
			WHERE cc.parent = {$parent_id} and cr.option_val = 1) UNION 
			(SELECT cc.id, cc.name, cc.description, parent, coursecount, cc.sortorder, cc.visible, 1 as access FROM {course_categories} as cc
			WHERE cc.parent = {$parent_id} AND cc.id NOT IN (SELECT coursecategoryid FROM {ecatalog_crowd_course_cats})
			)) t GROUP BY id";
		$sql.=' ORDER BY sortorder';
			
	$rs = $DB->get_recordset_sql($sql);
	$result = array();
	foreach ($rs as $row) {
		if (!$row->visible)
			continue;
		$rowid = $row->id;
		$context = get_context_instance(CONTEXT_COURSECAT, $rowid);
		$result[$rowid]['id'] = $row->id;
		$result[$rowid]['name'] = $row->name;
		$result[$rowid]['description'] = file_rewrite_pluginfile_urls($row->description, 'pluginfile.php', $context->id, 'coursecat', 'description', null);
		$result[$rowid]['parent'] = $row->parent;
		$result[$rowid]['coursecount'] = $row->coursecount;
		preg_match_all('/<img[^>]+>/i',$result[$rowid]['description'], $img);
		$result[$rowid]['description'] = '<h2>'.$row->name.'</h2>'.str_replace('"',"'",preg_replace("/<img[^>]+\>/i", "", $result[$row->id]['description'])); 
		$result[$rowid]['img'] = isset($img[0][0]) ? $img[0][0] : '';
		$result[$rowid]['access'] = $row->access;
		$result[$rowid]['subcategories'] = $row->access > 0 ? get_tree_of_courses($row->id, $row->name) : array();
		$result[$rowid]['courses'] = $row->access > 0 ? get_list_of_courses($row->id) : array();
		if ($row->access>0) {
			if (count($result[$rowid]['subcategories'])) {
				$result[$rowid]['description'] .= "<div class='crowd_tooltip_botom_link crowd_tooltip_botom_link_categories' pid='{$row->id}'><a href='javascript:void(0)'>View Sub Categories</a></div>";
			} else if (count($result[$rowid]['courses'])) {
				$result[$rowid]['description'] .= "<div class='crowd_tooltip_botom_link crowd_tooltip_botom_link_categories' pid='{$row->id}'><a href='javascript:void(0)'>View Courses</a></div>";
			}
		} else {
			$result[$rowid]['description'] .= "<div class='crowd_tooltip_botom_link crowd_hidden'><a href='javascript:void(0)'>View Courses</a></div>";
		}
		$result[$rowid]['box_name'] = (!empty($parent_name) ? $parent_name.' / ' : '').$result[$rowid]['name'];
		
		$result[$rowid]['sortorder'] = $row->sortorder; //kingjj 
	}
	$rs->close();
	
	return $result;
}

function get_list_of_courses($category_id = 0) {
    //Note: November 2013 - Jeff King (kingjj envisiontel)
    //I added 'sortorder' to the queries
    global $DB, $USER;
	$sql = "SELECT c.id, c.summary as description, fullname, shortname, sortorder, 1 as access FROM {course} as c 
			JOIN {ecatalog_crowd_course} as cc ON c.id = cc.courseid 
			JOIN {ecatalog_crowd_members} AS cm ON cm.crowdid = cc.crowdid
			JOIN {ecatalog} AS cr ON cm.crowdid = cr.id
			JOIN {cohort_members} AS chm ON chm.cohortid = cm.cohortid
			JOIN {user} AS u ON u.id = chm.userid
			WHERE u.id ={$USER->id}";
	if ($category_id) $sql.=" and c.category = {$category_id}";
	$sql2 = "SELECT c.id, c.summary as description, fullname, shortname, sortorder, 0 as access FROM {course} as c 
			JOIN {ecatalog_crowd_course} as cc ON c.id = cc.courseid 
			JOIN {ecatalog} AS cr ON cc.crowdid = cr.id
			WHERE cr.option_val = 1";
	if ($category_id) $sql2.=" and c.category = {$category_id}";
	$sql3 = "SELECT c.id, c.summary as description, fullname, shortname, sortorder, 1 as access FROM {course} as c 
			WHERE c.id NOT IN (SELECT courseid FROM {ecatalog_crowd_course})";
	if ($category_id) $sql3.=" and c.category = {$category_id}";
	$sql = "SELECT id, description, fullname, shortname, sortorder, sum(access) as access FROM (($sql) UNION ($sql2) UNION ($sql3)) t GROUP BY id";
	
	$sql .= " ORDER BY sortorder";

	$rs = $DB->get_recordset_sql($sql);
	$result = array();
	foreach ($rs as $row) {
		$context = get_context_instance(CONTEXT_COURSE, $row->id);
		$result[$row->id]['id'] = $row->id;
		$result[$row->id]['fullname'] = $row->fullname;
		$result[$row->id]['shortname'] = $row->shortname;
		$result[$row->id]['description'] = file_rewrite_pluginfile_urls($row->description, 'pluginfile.php', $context->id, 'course', 'summary', null);
		preg_match_all('/<img[^>]+>/i',$result[$row->id]['description'], $img);
		$result[$row->id]['description'] = '<h2>'.$row->fullname.'</h2>'.str_replace('"',"'",preg_replace("/<img[^>]+\>/i", "", $result[$row->id]['description']));
		if ($row->access > 0) {
			$result[$row->id]['description'] .= "<div class='crowd_tooltip_botom_link crowd_tooltip_botom_link_course' pid='{$row->id}'><a href='javascript:void(0)'>Learn More</a></div>"; 
		} else {
			$result[$row->id]['description'] .= "<div class='crowd_tooltip_botom_link crowd_hidden'><a href='javascript:void(0)'>Learn More</a></div>"; 
		}
		$result[$row->id]['img'] = isset($img[0][0]) ? $img[0][0] : '';
		$result[$row->id]['access'] = $row->access;
	}
	$rs->close();
	
	return $result;
}

function display_categories(&$cat, $level = 0) {
	reset($cat);
	$sub_courses = $sub_cats = array(); $cnt = 0;
	$course = current($cat);
	while ($course) {	
?><div class="crowd_category_box tooltip <?php echo $course['access'] > 0 ? 'crowd_visible_category' : 'crowd_hidden crowd_grey' ?>" <?php echo $course['access'] > 0 ? 'pid="'.$course['id'].'"' :'' ?> title="<?php echo $course['description']?>">
<div class="crowd_category_image_box"><?php echo $course['img']?></div>
<div class="crowd_empty_box"></div>
<span><?php echo $course['name']?></span>
</div><?php
		$cnt++;
		if (count($course['subcategories'])) {
			$sub_cats[$course['id']] = $course['subcategories'];
		}
		if (count($course['courses'])) {
			$sub_courses[$course['id']] = $course['courses'];
		}
		if ((($cnt % 4) == 0 )) {
		  if (count($sub_cats)) {
			foreach ($sub_cats as $key => $subcat) {
?>
<div class="crowd_clear"></div>
<div class="crowd_sub_level<?php echo $level ?> crowd_hidden_box" id="crowd_parent_<?php echo $key ?>">
<div class="crowd_box_label"><?php 

//echo $course['box_name'] 

echo $cat[$key]['box_name'] ?> / Categories</div>
<?php
			display_categories($subcat, $level + 1);
			if (isset($sub_courses[$key])) {
?>
<div class="crowd_box_label"><?php  
//echo $course['box_name']
echo $cat[$key]['box_name'] 
 ?> / Courses</div>
<?php			
				display_courses($sub_courses[$key]);
				unset($sub_courses[$key]);
			}
?>	
<div class="crowd_clear"></div>
</div>
<?php		
			}
		  $sub_cats = array();
		  }
		  if (count($sub_courses)) {
			foreach ($sub_courses as $key => $subcat) {
?>
<div class="crowd_clear"></div>
<div class="crowd_sub_level<?php echo $level ?> crowd_hidden_box" id="crowd_parent_<?php echo $key ?>">
<div class="crowd_box_label"><?php //echo $course['box_name'] 
	echo $cat[$key]['box_name'] 
?> / Courses</div>
<?php
			display_courses($subcat);
?>	
<div class="crowd_clear"></div>
</div>
<?php		
			}
		  $sub_courses = array();
		  }
		}
		if (next($cat)) {
			$course = current($cat);
		} else {
			$last_course = $course;
			$course = false;
		}
	}
		if (count($sub_cats)) {
			foreach ($sub_cats as $key => $subcat) {
?>
<div class="crowd_clear"></div>
<div class="crowd_sub_level<?php echo $level ?> crowd_hidden_box" id="crowd_parent_<?php echo $key ?>">
<div class="crowd_box_label"><?php //echo $last_course['box_name']
echo $cat[$key]['box_name']
 ?> / Categories</div>
<?php
			display_categories($subcat, $level + 1);
			if (isset($sub_courses[$key])) {
?>
<div class="crowd_box_label"><?php //echo $last_course['box_name']
echo $cat[$key]['box_name']
 ?> / Courses</div>
<?php			
				display_courses($sub_courses[$key]);
				unset($sub_courses[$key]);
			}
?>	
<div class="crowd_clear"></div>
</div>
<?php		
			}
		}
		if (count($sub_courses)) {
			foreach ($sub_courses as $key => $subcat) {
?>
<div class="crowd_clear"></div>
<div class="crowd_sub_level<?php echo $level ?> crowd_hidden_box" id="crowd_parent_<?php echo $key ?>">
<div class="crowd_box_label"><?php 
//echo $last_course['box_name'] 

echo $cat[$key]['box_name']
?> / Courses</div>
<?php
			display_courses($subcat);
?>	
<div class="crowd_clear"></div>
</div>
<?php		
			}
		}
}

function display_courses($courses) {
	foreach ($courses as $course) {
?><div class="crowd_category_box tooltip <?php echo $course['access'] > 0 ? 'crowd_visible_course' : 'crowd_hidden crowd_grey' ?>" <?php echo $course['access'] > 0 ? 'pid="'.$course['id'].'"' :'' ?> title="<?php echo $course['description']?>">
<div class="crowd_category_image_box"><?php echo $course['img']?></div>
<div class="crowd_empty_box"></div>
<span><?php echo $course['shortname']?></span>
</div><?php
	}
}

