<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/adminlib.php');

class admin_setting_cccourselist_frontpage extends admin_setting {
    /** @var array Array of choices value=>label */
    public $choices;

    /**
     * Construct override, requires one param
     *
     * @param bool $loggedin Is the user logged in
     */
    public function __construct() {
        global $CFG;
        $name        = 'customfrontpage';
        $visiblename = get_string('customfrontpage','local_enlightencatalog');
        $description = get_string('customconfigfrontpage','local_enlightencatalog');
        $defaults    = array();
        parent::__construct($name, $visiblename, $description, $defaults);
    }

    /**
     * Loads the choices available
     *
     * @return bool always returns true
     */
    public function load_choices() {
        global $DB;
        if (is_array($this->choices)) {
            return true;
        }
        $this->choices = array(
            'courses'    => get_string('frontpagecourselist'),
            'categories' => get_string('frontpagecategorynames'),
        	'programs' => get_string('listofprograms','local_enlightencatalog'),
            'none'                 => get_string('none'));
        return true;
    }

    /**
     * Returns the selected settings
     *
     * @param mixed array or setting or null
     */
    public function get_setting() {
        $result = $this->config_read($this->name);
        if (is_null($result)) {
            return NULL;
        }
        if ($result === '') {
            return array();
        }
        return explode(',', $result);
    }

    /**
     * Save the selected options
     *
     * @param array $data
     * @return mixed empty string (data is not an array) or bool true=success false=failure
     */
    public function write_setting($data) {
        if (!is_array($data)) {
            return '';
        }
        $this->load_choices();
        $save = array();
        foreach($data as $datum) {
            if ($datum == 'none' or !array_key_exists($datum, $this->choices)) {
                continue;
            }
            $save[$datum] = $datum; // no duplicates
        }
        return ($this->config_write($this->name, implode(',', $save)) ? '' : get_string('errorsetting', 'local_enlightencatalog'));
    }

    /**
     * Return XHTML select field and wrapping div
     *
     * @todo Add vartype handling to make sure $data is an array
     * @param array $data Array of elements to select by default
     * @return string XHTML select field and wrapping div
     */
    public function output_html($data, $query='') {
        $this->load_choices();
        $currentsetting = array();
        if (is_array($data))
        foreach ($data as $key) {
            if ($key != 'none' and array_key_exists($key, $this->choices)) {
                $currentsetting[] = $key; // already selected first
            }
        }

        $return = '<div class="form-group">';
        for ($i = 0; $i < count($this->choices) - 1; $i++) {
            if (!array_key_exists($i, $currentsetting)) {
                $currentsetting[$i] = 'none'; //none
            }
            $return .='<select class="form-select" id="'.$this->get_id().$i.'" name="'.$this->get_full_name().'[]">';
            foreach ($this->choices as $key => $value) {
                $return .= '<option value="'.$key.'"'.("$key" == $currentsetting[$i] ? ' selected="selected"' : '').'>'.$value.'</option>';
            }
            $return .= '</select>';
            if ($i !== count($this->choices) - 2) {
                $return .= '<br />';
            }
        }
        $return .= '</div>';

        return format_admin_setting($this, $this->visiblename, $return, $this->description, false, '', NULL, $query);
    }
}

	
?>