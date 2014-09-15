<?php
if (!function_exists('optional_param_array')) {

function optional_param_array($parname, $default, $type) {
    if (func_num_args() != 3 or empty($parname) or empty($type)) {
        throw new coding_exception('optional_param_array() requires $parname, $default and $type to be specified (parameter: '.$parname.')');
    }

    if (isset($_POST[$parname])) {       // POST has precedence
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        return $default;
    }
    if (!is_array($param)) {
        debugging('optional_param_array() expects array parameters only: '.$parname);
        return $default;
    }

    $result = array();
    foreach($param as $key=>$value) {
        if (!preg_match('/^[a-z0-9_-]+$/i', $key)) {
            debugging('Invalid key name in optional_param_array() detected: '.$key.', parameter: '.$parname);
            continue;
        }
        $result[$key] = clean_param($value, $type);
    }

    return $result;
}

}

if (!function_exists('optional_param_array')) {

	function optional_param($parname, $default, $type) {
    if (func_num_args() != 3 or empty($parname) or empty($type)) {
        throw new coding_exception('optional_param() requires $parname, $default and $type to be specified (parameter: '.$parname.')');
    }
    if (!isset($default)) {
        $default = null;
    }

    if (isset($_POST[$parname])) {       // POST has precedence
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        return $default;
    }

    if (is_array($param)) {
        debugging('Invalid array parameter detected in required_param(): '.$parname);
        // TODO: switch to $default in Moodle 2.3
        //return $default;
        return optional_param_array($parname, $default, $type);
    }

    return clean_param($param, $type);
}

	
}

define('CROWD_SELECTOR_DEFAULT_ROWS', 20);

abstract class crowd_selector_base {
    protected $name;
    protected $extrafields;
    protected $accesscontext;
    protected $multiselect = true;
    protected $rows = CROWD_SELECTOR_DEFAULT_ROWS;
    protected $exclude = array();
    protected $selected = null;
    protected $preserveselected = false;
    protected $autoselectunique = false;
    protected $searchanywhere = false;
    protected $validatingids = null;
    protected $crowdid;

    private static $searchoptionsoutput = false;

    public $maxrowsperpage = 100;

    // Public API ==============================================================

    /**
     * Constructor. Each subclass must have a constructor with this signature.
     *
     * @param string $name the control name/id for use in the HTML.
     * @param array $options other options needed to construct this selector.
     * You must be able to clone a userselector by doing new get_class($us)($us->get_name(), $us->get_options());
     */
    public function __construct($name, $options = array()) {
        global $CFG, $PAGE;

        // Initialise member variables from constructor arguments.
        $this->name = $name;

        // Use specified context for permission checks, system context if not
        // specified
        if (isset($options['accesscontext'])) {
            $this->accesscontext = $options['accesscontext'];
        } else {
            $this->accesscontext = context_system::instance();
        }

        if (isset($options['extrafields'])) {
            $this->extrafields = $options['extrafields'];
        } else if (!empty($CFG->showcohortidentity) &&
                has_capability('local/enlightencatalog:manage', $this->accesscontext)) {
            $this->extrafields = explode(',', $CFG->showcohortidentity);
        } else {
            $this->extrafields = array();
        }
        if (isset($options['exclude']) && is_array($options['exclude'])) {
            $this->exclude = $options['exclude'];
        }
        if (isset($options['multiselect'])) {
            $this->multiselect = $options['multiselect'];
        }
        if (isset($options['crowdid'])) {
            $this->crowdid = $options['crowdid'];
        }

        // Read the user prefs / optional_params that we use.
        $this->preserveselected = $this->initialise_option('rowselector_preserveselected', $this->preserveselected);
        $this->autoselectunique = $this->initialise_option('rowselector_autoselectunique', $this->autoselectunique);
        $this->searchanywhere = $this->initialise_option('rowselector_searchanywhere', $this->searchanywhere);

        if (!empty($CFG->maxroesperpage)) {
            $this->maxrowsperpage = $CFG->maxrowsperpage;
        }
    }

    /**
     * All to the list of user ids that this control will not select. For example,
     * on the role assign page, we do not list the users who already have the role
     * in question.
     *
     * @param array $arrayofuserids the user ids to exclude.
     */
    public function exclude($arrayofids) {
        $this->exclude = array_unique(array_merge($this->exclude, $arrayofids));
    }

    /**
     * Clear the list of excluded user ids.
     */
    public function clear_exclusions() {
        $this->exclude = array();
    }

    /**
     * @return array the list of user ids that this control will not select.
     */
    public function get_exclusions() {
        return clone($this->exclude);
    }

    /**
     * @return array of user objects. The users that were selected. This is a more sophisticated version
     * of optional_param($this->name, array(), PARAM_INT) that validates the
     * returned list of ids against the rules for this user selector.
     */
    public function get_selected_rows() {
        // Do a lazy load.
        if (is_null($this->selected)) {
            $this->selected = $this->load_selected_rows();
        }
        return $this->selected;
    }

    /**
     * Convenience method for when multiselect is false (throws an exception if not).
     * @return object the selected user object, or null if none.
     */
    public function get_selected_row() {
        if ($this->multiselect) {
            throw new moodle_exception('cannotcallusgetselectedrow');
        }
        $users = $this->get_selected_rows();
        if (count($rows) == 1) {
            return reset($rows);
        } else if (count($rows) == 0) {
            return null;
        } else {
            throw new moodle_exception('rowselectortoomany');
        }
    }

    /**
     * If you update the database in such a way that it is likely to change the
     * list of users that this component is allowed to select from, then you
     * must call this method. For example, on the role assign page, after you have
     * assigned some roles to some users, you should call this.
     */
    public function invalidate_selected_rows() {
        $this->selected = null;
    }

    /**
     * Output this user_selector as HTML.
     * @param boolean $return if true, return the HTML as a string instead of outputting it.
     * @return mixed if $return is true, returns the HTML as a string, otherwise returns nothing.
     */
    public function display($return = false) {
        global $PAGE;

        // Get the list of requested users.
        $search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
        if (optional_param($this->name . '_clearbutton', false, PARAM_BOOL)) {
            $search = '';
        }
        $groupedrows = $this->find_rows($search);

        // Output the select.
        $name = $this->name;
        $multiselect = '';
        if ($this->multiselect) {
            $name .= '[]';
            $multiselect = 'multiple="multiple" ';
        }
        $output = '<div class="userselector" id="' . $this->name . '_wrapper">' . "\n" .
                '<select name="' . $name . '" id="' . $this->name . '" ' .
                $multiselect . 'size="' . $this->rows . '">' . "\n";

        // Populate the select.
        $output .= $this->output_options($groupedrows, $search);

        // Output the search controls.
        $output .= "</select>\n<div>\n";
        $output .= '<input type="text" name="' . $this->name . '_searchtext" id="' .
                $this->name . '_searchtext" size="15" value="' . s($search) . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_searchbutton" id="' .
                $this->name . '_searchbutton" value="' . $this->search_button_caption() . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_clearbutton" id="' .
                $this->name . '_clearbutton" value="' . get_string('clear') . '" />';

        // And the search options.
        $optionsoutput = false;
        if (!crowd_selector_base::$searchoptionsoutput && false) {
            $output .= print_collapsible_region_start('', 'rowselector_options',
                    get_string('searchoptions'), 'rowselector_optionscollapsed', true, true);
            $output .= $this->option_checkbox('preserveselected', $this->preserveselected, get_string('rowselectorpreserveselected','local_enlightencatalog'));
            $output .= $this->option_checkbox('autoselectunique', $this->autoselectunique, get_string('rowselectorautoselectunique','local_enlightencatalog'));
            $output .= $this->option_checkbox('searchanywhere', $this->searchanywhere, get_string('rowselectorsearchanywhere','local_enlightencatalog'));
            $output .= print_collapsible_region_end(true);

            crowd_selector_base::$searchoptionsoutput = true;
        }
        $output .= "</div>\n</div>\n\n";

        // Initialise the ajax functionality.
        $output .= $this->initialise_javascript($search);

        // Return or output it.
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * The height this control will be displayed, in rows.
     *
     * @param integer $numrows the desired height.
     */
    public function set_rows($numrows) {
        $this->rows = $numrows;
    }

    /**
     * @return integer the height this control will be displayed, in rows.
     */
    public function get_rows() {
        return $this->rows;
    }

    /**
     * Whether this control will allow selection of many, or just one user.
     *
     * @param boolean $multiselect true = allow multiple selection.
     */
    public function set_multiselect($multiselect) {
        $this->multiselect = $multiselect;
    }

    /**
     * @return boolean whether this control will allow selection of more than one user.
     */
    public function is_multiselect() {
        return $this->multiselect;
    }

    /**
     * @return string the id/name that this control will have in the HTML.
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Set the user fields that are displayed in the selector in addition to the
     * user's name.
     *
     * @param array $fields a list of field names that exist in the user table.
     */
    public function set_extra_fields($fields) {
        $this->extrafields = $fields;
    }

    // API for sublasses =======================================================

    /**
     * Search the database for users matching the $search string, and any other
     * conditions that apply. The SQL for testing whether a user matches the
     * search string should be obtained by calling the search_sql method.
     *
     * This method is used both when getting the list of choices to display to
     * the user, and also when validating a list of users that was selected.
     *
     * When preparing a list of users to choose from ($this->is_validating()
     * return false) you should probably have an maximum number of users you will
     * return, and if more users than this match your search, you should instead
     * return a message generated by the too_many_results() method. However, you
     * should not do this when validating.
     *
     * If you are writing a new user_selector subclass, I strongly recommend you
     * look at some of the subclasses later in this file and in admin/roles/lib.php.
     * They should help you see exactly what you have to do.
     *
     * @param string $search the search string.
     * @return array An array of arrays of users. The array keys of the outer
     *      array should be the string names of optgroups. The keys of the inner
     *      arrays should be userids, and the values should be user objects
     *      containing at least the list of fields returned by the method
     *      required_fields_sql(). If a user object has a ->disabled property
     *      that is true, then that option will be displayed greyed out, and
     *      will not be returned by get_selected_users.
     */
    public abstract function find_rows($search);

    /**
     *
     * Note: this function must be implemented if you use the search ajax field
     *       (e.g. set $options['file'] = '/admin/filecontainingyourclass.php';)
     * @return array the options needed to recreate this user_selector.
     */
    protected function get_options() {
        return array(
            'class' => get_class($this),
            'name' => $this->name,
            'exclude' => $this->exclude,
            'extrafields' => $this->extrafields,
            'multiselect' => $this->multiselect,
            'accesscontext' => $this->accesscontext,
        );
    }

    // Inner workings ==========================================================

    /**
     * @return boolean if true, we are validating a list of selected users,
     *      rather than preparing a list of uesrs to choose from.
     */
    protected function is_validating() {
        return !is_null($this->validatingids);
    }

    /**
     * Get the list of users that were selected by doing optional_param then
     * validating the result.
     *
     * @return array of user objects.
     */
    protected function load_selected_rows() {
        // See if we got anything.
        if ($this->multiselect) {
            $rowids = optional_param_array($this->name, array(), PARAM_INT);
        } else if ($rowid = optional_param($this->name, 0, PARAM_INT)) {
            $rowids = array($rowid);
        }
        // If there are no users there is nobody to load
        if (empty($rowids)) {
            return array();
        }

        // If we did, use the find_users method to validate the ids.
        $this->validatingids = $rowids;
        $groupedrows = $this->find_rows('');
        $this->validatingids = null;

        // Aggregate the resulting list back into a single one.
        $rows = array();
        foreach ($groupedrows as $group) {
            foreach ($group as $row) {
                if (!isset($rows[$row->id]) && empty($row->disabled) && in_array($row->id, $rowids)) {
                    $rows[$row->id] = $row;
                }
            }
        }

        // If we are only supposed to be selecting a single user, make sure we do.
        if (!$this->multiselect && count($rows) > 1) {
            $rows = array_slice($rows, 0, 1);
        }

        return $rows;
    }

    /**
     * @param string $u the table alias for the user table in the query being
     *      built. May be ''.
     * @return string fragment of SQL to go in the select list of the query.
     */
    protected function required_fields_sql($u) {
        // Raw list of fields.
        $fields = array('id');
        $fields = array_merge($fields, $this->extrafields);

        // Prepend the table alias.
        if ($u) {
            foreach ($fields as &$field) {
                $field = $u . '.' . $field;
            }
        }
        return implode(',', $fields);
    }


    /**
     * Used to generate a nice message when there are too many users to show.
     * The message includes the number of users that currently match, and the
     * text of the message depends on whether the search term is non-blank.
     *
     * @param string $search the search term, as passed in to the find users method.
     * @param int $count the number of users that currently match.
     * @return array in the right format to return from the find_users method.
     */
    protected function too_many_results($search, $count) {
        if ($search) {
            $a = new stdClass;
            $a->count = $count;
            $a->search = $search;
            return array(get_string('toomanyrowsmatchsearch', 'local_enlightencatalog', $a) => array(),
                    get_string('pleasesearchmore') => array());
        } else {
            return array(get_string('toomanyrowstoshow', 'local_enlightencatalog', $count) => array(),
                    get_string('pleaseusesearch') => array());
        }
    }

    /**
     * Output the list of <optgroup>s and <options>s that go inside the select.
     * This method should do the same as the JavaScript method
     * user_selector.prototype.handle_response.
     *
     * @param array $groupedusers an array, as returned by find_users.
     * @return string HTML code.
     */
    protected function output_options($groupedrows, $search) {
        $output = '';

        // Ensure that the list of previously selected users is up to date.
        $this->get_selected_rows();

        // If $groupedusers is empty, make a 'no matching users' group. If there is
        // only one selected user, set a flag to select them if that option is turned on.
        $select = false;
        if (empty($groupedrows)) {
            if (!empty($search)) {
                $groupedrows = array(get_string('nomatchingrows', 'local_enlightencatalog', $search) => array());
            } else {
                $groupedrows = array(get_string('none') => array());
            }
        } else if ($this->autoselectunique && count($groupedrows) == 1 &&
                count(reset($groupedrows)) == 1) {
            $select = true;
            if (!$this->multiselect) {
                $this->selected = array();
            }
        }

        // Output each optgroup.
        foreach ($groupedrows as $groupname => $rows) {
            $output .= $this->output_optgroup($groupname, $rows, $select);
        }

        // If there were previously selected users who do not match the search, show them too.
        if ($this->preserveselected && !empty($this->selected)) {
            $output .= $this->output_optgroup(get_string('previouslyselectedrows', '', $search), $this->selected, true);
        }

        // This method trashes $this->selected, so clear the cache so it is
        // rebuilt before anyone tried to use it again.
        $this->selected = null;

        return $output;
    }

    /**
     * Output one particular optgroup. Used by the preceding function output_options.
     *
     * @param string $groupname the label for this optgroup.
     * @param array $users the users to put in this optgroup.
     * @param boolean $select if true, select the users in this group.
     * @return string HTML code.
     */
    protected function output_optgroup($groupname, $rows, $select) {
        if (!empty($rows)) {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . ' (' . count($rows) . ')">' . "\n";
            foreach ($rows as $row) {
                $attributes = '';
                if (!empty($row->disabled)) {
                    $attributes .= ' disabled="disabled"';
                } else if ($select || isset($this->selected[$row->id])) {
                    $attributes .= ' selected="selected"';
                }
                unset($this->selected[$row->id]);
                $output .= '    <option' . $attributes . ' value="' . $row->id . '">' .
                        $this->output_row($row) . "</option>\n";
                if (!empty($row->infobelow)) {
                    // 'Poor man's indent' here is because CSS styles do not work
                    // in select options, except in Firefox.
                    $output .= '    <option disabled="disabled" class="userselector-infobelow">' .
                            '&nbsp;&nbsp;&nbsp;&nbsp;' . s($row->infobelow) . '</option>';
                }
            }
        } else {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . '">' . "\n";
            $output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
        }
        $output .= "  </optgroup>\n";
        return $output;
    }

    /**
     * Convert a user object to a string suitable for displaying as an option in the list box.
     *
     * @param object $user the user to display.
     * @return string a string representation of the user.
     */
    public function output_row($row) {
    	if (isset($row->name)) {
			$out = $row->name;
    	} elseif (isset($row->fullname)) {
			$out = $row->fullname;
    	}
        if ($this->extrafields) {
            $displayfields = array();
            foreach ($this->extrafields as $field) {
                $displayfields[] = $row->{$field};
            }
            $out .= ' (' . implode(', ', $displayfields) . ')';
        }
        return $out;
    }

    /**
     * @return string the caption for the search button.
     */
    protected function search_button_caption() {
        return get_string('search');
    }

    // Initialise one of the option checkboxes, either from
    // the request, or failing that from the user_preferences table, or
    // finally from the given default.
    private function initialise_option($name, $default) {
        $param = optional_param($name, null, PARAM_BOOL);
        if (is_null($param)) {
            return get_user_preferences($name, $default);
        } else {
            set_user_preference($name, $param);
            return $param;
        }
    }

    // Output one of the options checkboxes.
    private function option_checkbox($name, $on, $label) {
    	//not needed for now
    	return '';
        if ($on) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }
        $name = 'rowselector_' . $name;
        $output = '<p><input type="hidden" name="' . $name . '" value="0" />' .
                // For the benefit of brain-dead IE, the id must be different from the name of the hidden form field above.
                // It seems that document.getElementById('frog') in IE will return and element with name="frog".
                '<input type="checkbox" id="' . $name . 'id" name="' . $name . '" value="1"' . $checked . ' /> ' .
                '<label for="' . $name . 'id">' . $label . "</label></p>\n";
        user_preference_allow_ajax_update($name, PARAM_BOOL);
        return $output;
    }

    /**
     * @param boolean $optiontracker if true, initialise JavaScript for updating the user prefs.
     * @return any HTML needed here.
     */
    protected function initialise_javascript($search) {
        global $USER, $PAGE, $OUTPUT;
        $output = '';

        // Put the options into the session, to allow search.php to respond to the ajax requests.
        $options = $this->get_options();
        $hash = md5(serialize($options));
        $USER->userselectors[$hash] = $options;

        // Initialise the selector.
        return $output;
    }
}

// User selectors for managing group members ==================================

/**
 * Base class to avoid duplicating code.
 */
abstract class groups_crowd_selector_base extends crowd_selector_base {
    protected function convert_array_format($rows, $search) {
        $groupedrows = array();
        foreach ($rows as $row) {
        	if (isset($row->fullname)) $row->name = $row->fullname;
        	$groupedrows['list'][] = $row;
        }
        return $groupedrows;
    }
}

class cohort_potential_selector extends groups_crowd_selector_base {
    public function find_rows($search) {
        global $DB;
		
		$searchcondition = '';
		if (!empty($search)) $searchcondition = " AND name LIKE '%$search%'";
		
        // Build the SQL
        $fields = "SELECT id, name ";
        $sql = " FROM {cohort} as c WHERE id NOT in (SELECT cohortid from {crowd_members} WHERE crowdid = {$this->crowdid}) $searchcondition";

        $orderby = ' ORDER BY name';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql("SELECT COUNT(DISTINCT id) $sql");
            if ($potentialmemberscount > $this->maxrowsperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $rs = $DB->get_recordset_sql("$fields $sql $orderby");

        $result = $this->convert_array_format($rs, $search);
        
        $rs->close();
        
        return $result;
    }
}

class cohort_existing_selector extends groups_crowd_selector_base {
    public function find_rows($search) {
        global $DB;
		
		$searchcondition = '';
		if (!empty($search)) $searchcondition = " AND name LIKE '%$search%'";
		
        // Build the SQL
        $fields = "SELECT c.id, c.name ";
        $sql = " FROM {cohort} as c WHERE id in (SELECT cohortid from {crowd_members} WHERE crowdid = {$this->crowdid}) $searchcondition";

        $orderby = ' ORDER BY name';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql("SELECT COUNT(DISTINCT id) $sql");
            if ($potentialmemberscount > $this->maxrowsperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $rs = $DB->get_recordset_sql("$fields $sql $orderby");

        $result = $this->convert_array_format($rs, $search);
        
        $rs->close();
        
        return $result;
    }
}

class course_potential_selector extends groups_crowd_selector_base {
    public function find_rows($search) {
        global $DB;
		
		$searchcondition = '';
		if (!empty($search)) $searchcondition = " AND fullname LIKE '%$search%'";
		
        // Build the SQL
        $fields = "SELECT id, fullname ";
        $sql = " FROM {course} as c WHERE id NOT in (SELECT courseid from {crowd_course} WHERE crowdid = {$this->crowdid}) $searchcondition";

        $orderby = ' ORDER BY fullname';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql("SELECT COUNT(DISTINCT id) $sql");
            if ($potentialmemberscount > $this->maxrowsperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $rs = $DB->get_recordset_sql("$fields $sql $orderby");

        $result = $this->convert_array_format($rs, $search);
        
        $rs->close();
        
        return $result;
    }
}

class course_existing_selector extends groups_crowd_selector_base {
    public function find_rows($search) {
        global $DB;
		
		$searchcondition = '';
		if (!empty($search)) $searchcondition = " AND fullname LIKE '%$search%'";
		
        // Build the SQL
        $fields = "SELECT id, fullname ";
        $sql = " FROM {course} as c WHERE id in (SELECT courseid from {crowd_course} WHERE crowdid = {$this->crowdid}) $searchcondition";

        $orderby = ' ORDER BY fullname';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql("SELECT COUNT(DISTINCT id) $sql");
            if ($potentialmemberscount > $this->maxrowsperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $rs = $DB->get_recordset_sql("$fields $sql $orderby");

        $result = $this->convert_array_format($rs, $search);
        
        $rs->close();
        
        return $result;
    }
}

class course_cat_potential_selector extends groups_crowd_selector_base {
    public function find_rows($search) {
        global $DB;
		
		$searchcondition = '';
		if (!empty($search)) $searchcondition = " AND name LIKE '%$search%'";
		
        // Build the SQL
        $fields = "SELECT id, name ";
        $sql = " FROM {course_categories} as c WHERE id NOT in (SELECT	coursecategoryid from {crowd_course_categories} WHERE crowdid = {$this->crowdid}) $searchcondition";

        $orderby = ' ORDER BY name';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql("SELECT COUNT(DISTINCT id) $sql");
            if ($potentialmemberscount > $this->maxrowsperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $rs = $DB->get_recordset_sql("$fields $sql $orderby");

        $result = $this->convert_array_format($rs, $search);
        
        $rs->close();
        
        return $result;
    }
}

class course_cat_existing_selector extends groups_crowd_selector_base {
    public function find_rows($search) {
        global $DB;
		
		$searchcondition = '';
		if (!empty($search)) $searchcondition = " AND name LIKE '%$search%'";
		
        // Build the SQL
        $fields = "SELECT c.id, c.name ";
        $sql = " FROM {course_categories} as c WHERE id in (SELECT	coursecategoryid from {crowd_course_categories} WHERE crowdid = {$this->crowdid}) $searchcondition";

        $orderby = ' ORDER BY name';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql("SELECT COUNT(DISTINCT id) $sql");
            if ($potentialmemberscount > $this->maxrowsperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $rs = $DB->get_recordset_sql("$fields $sql $orderby");

        $result = $this->convert_array_format($rs, $search);
        
        $rs->close();
        
        return $result;
    }
}
