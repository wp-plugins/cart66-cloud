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

    public function __construct( $display, $value, $is_selected=false ) {
        $this->display = $display;
        $this->value = $value;
        $this->is_selected = $is_selected;
    }

}