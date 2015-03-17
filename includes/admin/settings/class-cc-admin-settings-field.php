<?php

class CC_Admin_Settings_Field {

    /**
     * The key in the serialized option values array
     *
     * @var string
     */
    public $key;

    /**
     * The value in the serialized options vaules array associated with the key for this settings field
     *
     * @var mixed
     */
    public $value;

    /**
     * Display title of the settings field
     *
     * @var string
     */
    public $title;

    /**
     * Content describing the use and purpose of this setting.
     *
     * @var string
     */
    public $description;

    /**
     * Content that appears before the input field
     *
     * @var string
     */
    public $header;

    /**
     * Additional content that is displayed below the description.
     *
     * @var string
     */
    public $footer;

    /**
     * Array of CSS classes to apply to the settings input field
     *
     * @var array
     */
    public $css_classes;


    /**
     * Construct an admin settings field
     *
     * @param string $title The label displayed for the setting
     * @param string $key The key for the options array
     * @param mixed $value The value assoicated with the key in the options array
     */
    public function __construct( $title, $key, $value='' ) {
        $this->title = $title;
        $this->key = $key;
        $this->value = $value;
        $this->description = null;
        $this->header = null;
        $this->footer = null;
    }

    /**
     * Add a CSS class to the array of class names
     *
     * @var string $class_name
     */
    public function add_css_class( $class_name ) {
        $this->css_classes[] = $class_name;
    }


    /**
     * Override this function to control the display of the settings field.
     *
     * This funciton should echo its output
     */
    public function render( $args ) {
        echo '<pre>' . print_r( $args ) .  '</pre>';
    }

}
