<?php

class CC_Admin_Settings_Input_Option {

    /**
     * The text to display for this option.
     *
     * @var string
     */
    public $display;

    /**
     * The value to submit for this option
     *
     * @var string
     */
    public $value;

    /**
     * Whether or not this option has been selected
     *
     * @var boolean
     */
    public $is_selected = false;

    /**
     * Whether or not this option is disabled
     *
     * @var boolean
     */
    public $is_enabled = true;

    public function __construct( $display, $value, $is_selected=false, $is_enabled = true ) {
        $this->display = $display;
        $this->value = $value;
        $this->is_selected = $is_selected;
        $this->is_enabled = $is_enabled;
    }

}
