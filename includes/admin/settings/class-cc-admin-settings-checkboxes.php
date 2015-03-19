<?php

class CC_Admin_Settings_Checkboxes extends CC_Admin_Settings_Input_Group {

    public function set_selected( $selected_values ) {
        foreach ( $this->options as $option ) {
            $option->is_selected = in_array( $option->value, $selected_values);
        }
    }

    public function render( $args ) {

        $field = '<fieldset>';
        $field .= '<legend class="screen-reader-text"><span>' . $this->title . '</span></legend>';

        foreach( $this->options as $option ) {
            $checked  = $option->is_selected ? ' checked="checked"' : '';
            $disabled = $option->is_enabled ? '' : 'disabled="disabled"';

            $field .= '<label for="' . $args['option_name'] . '_' . $option->value . '">';
            $field .= '<input name="' . $args['option_name'] . '[' . $this->key . '][]" type="checkbox"';
            $field .= ' id="' . $args['option_name'] . '_' . $option->value . '" value="' . $option->value . '"';
            $field .= $checked . $disabled;
            $field .= '>';
            $field .= $option->display;
            $field .= '</label><br />';
        }

        if ( isset( $this->description ) ) {
            $field .= '<p class="description">' . $this->description . '</p>';
        }

        if ( isset( $this->footer ) ) {
            $field .= $this->footer;
        }

        $field .= '</fieldset>';

        echo $field;
    }

}
