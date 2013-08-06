<?php

class CC_CartWidget extends WP_Widget {

  public function __construct() {
    $widget_ops = array('classname' => 'CC_CartWidget', 'description' => 'Sidebar widget for Cart66 Cloud');
    $this->WP_Widget('CC_CartWidget', 'Cart66 Cloud Shopping Cart', $widget_ops);
    
    // Add actions for ajax rendering for cart widget
    add_action('wp_ajax_render_cart66_cart_widget', array('CC_CartWidget', 'ajax_render_content'));
    add_action('wp_ajax_nopriv_render_cart66_cart_widget', array('CC_CartWidget', 'ajax_render_content'));
  }

  /**
   * The form in the WordPress admin for configuring the widget
   */
  public function form($instance) {
    $instance = wp_parse_args($instance, array('title' => 'Your Cart'));
    $title = esc_attr($instance['title']);
    $data = array(
      'widget' => $this,
      'title' => $title
    );
    $view = CC_View::get(CC_PATH . 'views/widget/cart_admin.phtml', $data);
    echo $view;
  }

  /**
   * Process the widget options to be saved
   */
  public function update($new, $instance) {
    $instance['title'] = esc_attr($new['title']);
    return $instance;
  }

  /**
   * Render the content of the widget
   */
  public function widget($args, $instance) {

    // Enqueue and localize javascript for rendering ajax cart widget content
    wp_enqueue_script('cc_ajax_widget', CC_URL . 'resources/js/cart_widget.js');
    wp_enqueue_script('cc_ajax_spin', CC_URL . 'resources/js/spin.min.js');
    wp_enqueue_script('cc_ajax_spinner', CC_URL . 'resources/js/spinner.js', array('cc_ajax_spin'));
    $ajax_url = admin_url('admin-ajax.php');
    wp_localize_script('cc_ajax_widget', 'cc_widget', array('ajax_url' => $ajax_url));

    extract($args);
    $cart_summary = CC_Cart::get_summary();
    $data = array(
      'before_title' => $before_title,
      'after_title' => $after_title,
      'before_widget' => $before_widget,
      'after_widget' => $after_widget,
      'title' => $instance['title']
    );
    $view = CC_View::get(CC_PATH . 'views/widget/cart_sidebar.phtml', $data);
    echo $view;
  }

  public static function ajax_render_content() {
    // CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Call to CC_CartWidget::ajax_render_content()");
    $cart_summary = CC_Cart::get_summary();
    // CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] cart summary from ajax rendering: " . print_r($cart_summary, 1));
    $data = array(
      'view_cart_url' => CC_Cart::view_cart_url(),
      'checkout_url' => CC_Cart::checkout_url(), 
      'item_count' => $cart_summary->item_count,
      'subtotal' => $cart_summary->subtotal,
      'api_ok' => $cart_summary->api_ok
    );
    $view = CC_View::get(CC_PATH . 'views/widget/cart_sidebar_content.phtml', $data);
    echo $view;
    die();
  }

}
