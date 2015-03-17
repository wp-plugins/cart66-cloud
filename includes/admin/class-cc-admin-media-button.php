<?php

class CC_Admin_Media_Button {

    public static function add_media_button( $context ) {
        // CC_Log::write( 'Called add_media_button. Context: ' . print_r( $context, true ) );

        $style = <<<EOL
<style type="text/css">
    #cart66-menu-button-icon {
        padding: 4px 0px;
        font-size: 1.3em;
        color: #888;
    }
</style>

EOL;

        $title = __( 'Insert Product Into Content', 'cart66' );

        $button = '<a id="cc_product_shortcodes" href="#TB_inline?width=480&height=600&inlineId=cc_editor_pop_up" class="button thickbox" title="' . $title . '">';
        $button .= '<span id="cart66-menu-button-icon" class="dashicons dashicons-cart">  </span>';
        $button .= 'Cart66 Product';
        $button .= '</a>';

        $out = $style . $button;
        echo $out;
    }

    public static function add_media_button_popup() {
        $view = CC_View::get(CC_PATH . 'views/html-editor-pop-up.php');
        echo $view;
    }

    public static function enqueue_select2() {
        $url = cc_url();
        wp_enqueue_style( 'select2', $url .'resources/js/select2/select2.css' );
        wp_enqueue_script( 'select2', $url . 'resources/js/select2/select2.min.js' );
    }

}
