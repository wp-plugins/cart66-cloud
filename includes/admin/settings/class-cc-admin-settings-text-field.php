<?php

class CC_Admin_Settings_Text_Field extends CC_Admin_Settings_Field {

    public function render( $args ) {
        $field = $this->header;

        $field .= '<input type="text" name="' . $args['option_name'] . '[' . $this->key . ']" id="' . $args['option_name'] . '_' . $this->key . '" value="' . $this->value . '" class="regular-text" />';

        if ( isset( $this->description ) ) {
            $field .= '<p class="description">' . $this->description . '</p>';
        }

        if ( isset( $this->footer ) ) {
            $field .= $this->footer;
        }

        echo $field;
    }

}
