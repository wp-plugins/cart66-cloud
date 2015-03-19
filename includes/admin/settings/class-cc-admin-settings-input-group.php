<?php

class CC_Admin_Settings_Input_Group extends CC_Admin_Settings_Field {

    /**
     * The array of available input options for this setting
     *
     * @var array Holds CC_Admin_Settings_Input_Option objects
     */
    public $options;

    public function __construct( $title, $key ) {
        parent::__construct( $title, $key );
        $this->options = array();
    }

    public function clear_options() {
        $this->options = array();
    }

    public function add_option( CC_Admin_Settings_Input_Option $option ) {
        $this->options[] = $option;
    }

    public function new_option ( $display, $value, $is_selected = false, $is_enabled = true ) {
        $option = new CC_Admin_Settings_Input_Option( $display, $value, $is_selected, $is_enabled );
        $this->add_option( $option );
    }

    public function get_options() {
        return $this->options;
    }

    public function deselect_all_options() {
        foreach( $this->options as $option ) {
            $option->is_selected = false;
        }
    }

    /**
     * Set the option with with given value to be selected.
     *
     * If there is not an Input Option with the given value, the state of the selected input options will not be changed.
     *
     * This function returns the array of Input Options after selecting the option with the matching value.
     *
     * @return array CC_Admin_Setting_Input_Options objects
     */
    public function set_selected( $value ) {

        foreach( $this->options as $option ) {
            if( $value == $option->value ) {
                $this->deselect_all_options();
                $option->is_selected = true;
                break;
            }
        }

        return $this->options;
    }

    /**
     * Return the first selected input option.
     *
     * If none of the options are selected, null is returned.
     *
     * @return CC_Admin_Setting_Input_Option
     */
    public function get_selected() {
        foreach( $this->options as $option ) {
            if( $option->is_selected ) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Override this function to control the display of the input group.
     *
     * This funciton should echo its output
     */
    public function render( $args ) {

    }

}
