<?php

class CC_Cart_Widget extends WP_Widget {

    public function __construct() {
        $description = __( 'Show shopping cart in your sidebar', 'cart66' );
        $widget_ops = array('classname' => 'CC_Cart_Widget', 'description' => $description);

        $description = __( 'Cart66 Cloud Shopping Cart', 'cart66' );
        $this->WP_Widget('CC_Cart_Widget', $description, $widget_ops);

        // Add actions for ajax rendering for cart widget
        add_action('wp_ajax_render_cart66_cart_widget', array('CC_Cart_Widget', 'ajax_render_content'));
        add_action('wp_ajax_nopriv_render_cart66_cart_widget', array('CC_Cart_Widget', 'ajax_render_content'));
    }

    /**
     * The form in the WordPress admin for configuring the widget
     */
    public function form( $instance ) {
        $title = __( 'Your Cart', 'cart66' );
        $instance = wp_parse_args( $instance, array( 'title' => $title ) );
        $title = esc_attr( $instance['title'] );
        $data = array(
            'widget' => $this,
            'title' => $title
        );
        $view = CC_View::get( CC_PATH . 'views/widget/cart-admin.php', $data);
        echo $view;
    }

    /**
     * Process the widget options to be saved
     */
    public function update( $new, $instance ) {
        $instance['title'] = esc_attr( $new['title'] );
        return $instance;
    }

    /**
     * Render the content of the widget
     */
    public function widget( $args, $instance ) {
        $url = cc_url();

        // Enqueue and localize javascript for rendering ajax cart widget content
        wp_enqueue_script('cc_ajax_widget',  $url . 'resources/js/cart-widget.js');
        wp_enqueue_script('cc_ajax_spin',    $url . 'resources/js/spin.min.js');
        wp_enqueue_script('cc_ajax_spinner', $url . 'resources/js/spinner.js', array('cc_ajax_spin'));
        $ajax_url = admin_url('admin-ajax.php');
        wp_localize_script('cc_ajax_widget', 'cc_widget', array('ajax_url' => $ajax_url));

        extract($args);
        $cart_summary = CC_Cart::get_summary();

        $data = array(
            'before_title'  => $before_title,
            'after_title'   => $after_title,
            'before_widget' => $before_widget,
            'after_widget'  => $after_widget,
            'title'         => $instance['title']
        );

        $view = CC_View::get(CC_PATH . 'views/widget/cart-sidebar.php', $data);
        echo $view;
    }

    public static function ajax_render_content() {
        $item_count = 0;
        $subtotal = 0;
        $api_ok = false;
        $cart_summary = CC_Cart::get_summary();

        if ( is_object( $cart_summary ) ) {
            $item_count = $cart_summary->item_count;
            $subtotal   = $cart_summary->subtotal;
            $api_ok     = $cart_summary->api_ok;
        }

        $url = new CC_Cloud_URL();
        $data = array(
            'view_cart_url' => $url->view_cart(),
            'checkout_url'  => $url->checkout(),
            'item_count'    => $item_count,
            'subtotal'      => $subtotal,
            'api_ok'        => $api_ok
        );

        $view = CC_View::get(CC_PATH . 'views/widget/cart-sidebar-content.php', $data);
        echo $view;
        die();
    }

}
