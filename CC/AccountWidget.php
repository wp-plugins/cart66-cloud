<?php

class CC_AccountWidget extends WP_Widget {

  public function __construct() {
    $widget_ops = array('classname' => 'CC_AccountWidget', 'description' => 'Sign in and out of customer Cart66 Cloud accounts.');
    $this->WP_Widget('CC_AccountWidget', 'Cart66 Cloud Accounts', $widget_ops);
    
    // Add actions for ajax rendering for cart widget
    add_action('wp_ajax_render_cart66_account_widget', array('CC_AccountWidget', 'ajax_render_content'));
    add_action('wp_ajax_nopriv_render_cart66_account_widget', array('CC_AccountWidget', 'ajax_render_content'));
  }

  /**
   * The form in the WordPress admin for configuring the widget
   */
  public function form($instance) {
    $defaults = array(
      'title' => 'My Account', 
      'logged_out_message' => 'Please sign in',
      'logged_in_message' => 'Welcome %name%',
      'show_link_history' => '0',
      'show_link_profile' => '0',
    );
    $instance = wp_parse_args($instance, $defaults);
    $title = esc_attr($instance['title']);
    $logged_out_message = esc_attr($instance['logged_out_message']);
    $logged_in_message = esc_attr($instance['logged_in_message']);
    $history = $instance['show_link_history'] == 1 ? 'checked="checked"' : '';
    $profile = $instance['show_link_profile'] == 1 ? 'checked="checked"' : '';

    // TODO: implement %name% token replacement for logged in message
    $data = array(
      'widget' => $this,
      'title' => $title,
      'logged_out_message' => $logged_out_message,
      'logged_in_message' => $logged_in_message,
      'history' => $history,
      'profile' => $profile,
    );
    $view = CC_View::get(CC_PATH . 'views/widget/account_admin.phtml', $data);
    echo $view;
  }

  /**
   * Process the widget options to be saved
   */
  public function update($new, $instance) {
    $instance['title'] = esc_attr($new['title']);
    $instance['logged_in_message'] = esc_attr($new['logged_in_message']);
    $instance['logged_out_message'] = esc_attr($new['logged_out_message']);
    $instance['show_link_history'] = empty($new['show_link_history']) ? 0 : 1;
    $instance['show_link_profile'] = empty($new['show_link_profile']) ? 0 : 1;
    return $instance;
  }

  /**
   * Render the content of the widget
   */
  public function widget($args, $instance) {
    CC_Log::write('Running AccountWidget::widget()');

    // Enqueue and localize javascript for rendering ajax cart widget content
    wp_enqueue_script('cc_ajax_account_widget', CC_URL . 'resources/js/account_widget.js');
    wp_enqueue_script('cc_ajax_spin', CC_URL . 'resources/js/spin.min.js');
    wp_enqueue_script('cc_ajax_spinner', CC_URL . 'resources/js/spinner.js', array('cc_ajax_spin'));
    $ajax_url = admin_url('admin-ajax.php');
    $widget_data = array(
      'ajax_url' => $ajax_url,
      'logged_out_message' => $instance['logged_out_message'],
      'logged_in_message' => $instance['logged_in_message'],
      'show_link_history' => $instance['show_link_history'],
      'show_link_profile' => $instance['show_link_profile'],
    );
    wp_localize_script('cc_ajax_account_widget', 'cc_account_widget', $widget_data);

    extract($args);
    $data = array(
      'before_title' => $before_title,
      'after_title' => $after_title,
      'before_widget' => $before_widget,
      'after_widget' => $after_widget,
      'title' => $instance['title']
    );
    $view = CC_View::get(CC_PATH . 'views/widget/account_sidebar.phtml', $data);
    echo $view;
  }

  public static function ajax_render_content() {
    // TODO: Get real history and profile links
    $history_url = ($_POST['show_link_history'] == 1) ? CC_Cart::order_history_url() : false;
    $profile_url = ($_POST['show_link_profile'] == 1) ? CC_Cart::profile_url() : false;

    $home_url = home_url();
    $sign_in_url = $home_url . '/sign_in/';
    $sign_out_url = $home_url . '/sign_out/';

    $visitor = new CC_Visitor();
    $logged_in_message = str_replace('%name%', '<span class="cc_visitor_name">' . $visitor->get_token('name') . '</span>', CC_Common::scrub('logged_in_message'));
    $data = array(
      'history_url' => $history_url,
      'profile_url' => $profile_url,
      'sign_in_url' => $sign_in_url,
      'sign_out_url' => $sign_out_url,
      'is_logged_in' => $visitor->is_logged_in(),
      'logged_out_message' => CC_Common::scrub('logged_out_message'),
      'logged_in_message' => $logged_in_message,
    );
    $view = CC_View::get(CC_PATH . 'views/widget/account_sidebar_content.phtml', $data);
    echo $view;
    die();
  }

}
