<?php

class CC_Admin_Settings_Editor extends CC_Admin_Settings_Field {

    public function render( $args ) {
        $out = wp_editor(
            $this->value, 
            $args['option_name'] . '_' . $this->key,
            array( 'textarea_name' => $args['option_name'] . '[' . $this->key . ']'  ) 
        );
        $out .= '<label for="' . $args['option_name'] . '">' . $this->description . '</label>';
        echo $out;
    }

}
