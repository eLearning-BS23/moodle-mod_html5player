<?php
namespace mod_html5player;


use admin_setting;

class brightcove_credential extends admin_setting{

    /** @var mixed int means PARAM_XXX type, string is a allowed format in regex */
    public $paramtype;
    /** @var int default field size */
    public $size;

    /**
     * Config text constructor
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param string $defaultsetting
     * @param mixed $paramtype int means PARAM_XXX type, string is a allowed format in regex
     * @param int $size default field size
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_RAW, $size=null) {
        $this->paramtype = $paramtype;
        if (!is_null($size)) {
            $this->size  = $size;
        } else {
            $this->size  = ($paramtype === PARAM_INT) ? 5 : 30;
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Get whether this should be displayed in LTR mode.
     *
     * Try to guess from the PARAM type unless specifically set.
     */
    public function get_force_ltr() {
        $forceltr = parent::get_force_ltr();
        if ($forceltr === null) {
            return !is_rtl_compatible($this->paramtype);
        }
        return $forceltr;
    }

    /**
     * Return the setting
     *
     * @return mixed returns config if successful else null
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    public function write_setting($data) {
        if ($this->paramtype === PARAM_INT and $data === '') {
            // do not complain if '' used instead of 0
            $data = 0;
        }
        // $data is a string
        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }
        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Validate data before storage
     * @param string data
     * @return mixed true if ok string if error found
     */
    public function validate($data) {
        // allow paramtype to be a custom regex if it is the form of /pattern/
        if (preg_match('#^/.*/$#', $this->paramtype)) {
            if (preg_match($this->paramtype, $data)) {
                return true;
            } else {
                return get_string('validateerror', 'admin');
            }

        } else if ($this->paramtype === PARAM_RAW) {
            return true;

        } else {
            $cleaned = clean_param($data, $this->paramtype);
            if ("$data" === "$cleaned") { // implicit conversion to string is needed to do exact comparison
                return true;
            } else {
                return get_string('validateerror', 'admin');
            }
        }
    }

    /**
     * Return an XHTML string for the setting
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query='') {
        global $OUTPUT;

        $default = $this->get_defaultsetting();
        $context = (object) [
            'size' => $this->size,
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'value' => $data,
            'forceltr' => $this->get_force_ltr(),
            'readonly' => $this->is_readonly(),
        ];
        $element = $OUTPUT->render_from_template('mod_html5player/settings/credentials', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);
    }
}
