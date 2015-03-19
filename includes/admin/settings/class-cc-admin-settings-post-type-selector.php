<?php

class CC_Admin_Settings_Post_Type_Selector extends CC_Admin_Settings_Checkboxes {

    public function render( $args ) {
        $skip_types = array('cc_product', 'revision', 'attachment' );
        $this->new_option( 'products', 'cc_product', true, false );
        $post_types = get_post_types();

        foreach( $post_types as $type ) {
            if ( ! in_array( $type, $skip_types ) ) {
                $this->new_option( $type, $type, false);
            }
        }

        $selected_post_types = CC_Admin_Setting::get_option( 'cart66_main_settings', 'product_post_types' );
        $selected_post_types[] = 'cc_product';
        $this->set_selected( $selected_post_types );

        parent::render( $args );
    }

}
