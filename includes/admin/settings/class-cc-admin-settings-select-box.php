<?php

class CC_Admin_Settings_Select_Box extends CC_Admin_Settings_Input_Group {

    public function render( $args ) {
        $choices = array();
        foreach( $this->options as $option ) {
            $selected = $option->is_selected ? ' selected="selected"' : '';
            $choices[] = '<option value="' . $option->value . '"' . $selected . '>' . $option->display . '</option>';
        }

        $field = '<select name="' . $args['option_name'] . '[' . $this->key . ']">';
        $field .= implode( "\n", $choices);
        $field .= '</select>';

        if ( isset( $this->description ) ) {
            $field .= '<p class="description">' . $this->description . '</p>';
        }

        if ( isset( $this->footer ) ) {
            $field .= $this->footer;
        }

        echo $field;
    }
}
