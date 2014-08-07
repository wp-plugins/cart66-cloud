<?php
class CC_Cart66Cloud {

  public function __construct() {
    add_action('init', array($this, 'init_public'));
    add_action('init', array($this, 'init_admin'));

    // Handle tasks passed via query strings and post backs
    add_action('init', array('CC_TaskDispatcher', 'dispatch_init'));

    // Register sidebar widgets
    add_action('widgets_init', create_function('', 'return register_widget("CC_CartWidget");'));
    add_action('widgets_init', create_function('', 'return register_widget("CC_AccountWidget");'));

    // Enqueue jQuery
    add_action('wp_enqueue_scripts', array('CC_Cart', 'enqueue_jquery'));

    // Enqueue Chosen
    add_action('admin_enqueue_scripts', array('CC_Cart', 'enqueue_chosen'));

    // Add actions to process all add to cart requests via ajax
    add_action('wp_enqueue_scripts', array('CC_Cart', 'enqueue_ajax_add_to_cart'));
    add_action('wp_ajax_cc_ajax_add_to_cart', array('CC_Cart', 'ajax_add_to_cart'));
    add_action('wp_ajax_nopriv_cc_ajax_add_to_cart', array('CC_Cart', 'ajax_add_to_cart'));

    add_action('wp_ajax_cc_product_search', array('CC', 'product_search'));
  }

  public function init_public() {
    if(!is_admin()) {
      $this->members_public_init();
      // Check for page slurp
      if(CC_PhysicalPageSlurp::page_id()) {
        add_action('template_redirect', array('CC_PhysicalPageSlurp', 'detect_slurp'));
      }
      else {
        add_action('wp_loaded', array('CC_PageSlurp', 'check'));
      }

      // Always hide the page slurp template from the list of pages
      add_filter('get_pages', 'CC_PhysicalPageSlurp::hide_page_slurp');

      add_action('wp_loaded', array('CC_Cart', 'get_summary'));
      add_action('wp_loaded', array('CC_ShortcodeManager', 'register_shortcodes'));
      add_action('template_redirect', array('CC_Cart', 'redirect_cart_links'));
      // add_action('template_redirect', array('CC_PageSlurp', 'debug'));

      // Enqueue cart66 styles
      add_action('wp_enqueue_scripts', array('CC_Cart', 'enqueue_cart66_styles'));
      add_action('wp_enqueue_scripts', array('CC_Library', 'enqueue_scripts'));

      // Add footer messages
      add_action('wp_footer', array('CC_Cart', 'show_errors'));
    }
    else {
      CC_Log::write('Not initializing public actions because we are in the admin environment');
    }
  }

  public function init_admin() {
    if(is_admin()) {
      $this->members_admin_init();
      add_action('add_meta_boxes', array('CC_ProductMetaBox', 'add'));
      add_action('save_post', array('CC_ProductMetaBox', 'save'));
      add_action('admin_menu', array($this, 'attach_settings_page'));
      add_action('admin_notices', array($this, 'show_cart66_account_notice'));

      // Add media button for cart66 shortcodes
      if(in_array(CC_CURRENT_PAGE, array('post.php', 'page.php', 'page-new.php', 'post-new.php'))) {
        add_action('media_buttons_context', array('CC_ShortcodeManager', 'add_media_button'));
        add_action('admin_footer',  array('CC_ShortcodeManager', 'add_media_button_popup'));
      }

      add_filter('plugin_action_links', array($this, 'add_settings_link'), 10, 2);
    }
    else {
      CC_Log::write('Not initializing admin actions because we are not in the admin environment');
    }
  }


  public function members_public_init() {
    $monitor = new CC_Monitor();

    // Redirect to access denied page
    add_action('template_redirect', array($monitor, 'access_denied_redirect'));

    // Remove content from restricted pages
    add_filter('the_content', array($monitor, 'restrict_pages'));
    add_filter('the_posts',   array($monitor, 'filter_posts'));

    // Filter restricted pages that are not part of nav menus
    add_filter('get_pages',          array($monitor, 'filter_pages'));
    add_filter('nav_menu_css_class', array($monitor, 'filter_menus'), 10, 2);
    add_action('wp_enqueue_scripts', array($monitor, 'enqueue_css'));

    // Remove restricted categores from the category widget
    add_filter('widget_categories_args', array($monitor, 'filter_category_widget'), 10, 2);

    $visitor = new CC_Visitor();
    add_action('wp_loaded', array($visitor, 'check_remote_login'));
  }

  public static function members_admin_init() {
    if(is_admin()) {
      $cc_admin = new CC_Admin();
      add_action('admin_init', array($cc_admin, 'register_settings'), 20);
      add_action('admin_menu', array($cc_admin, 'add_secure_console_submenu'), 20);
      add_action('admin_menu', array($cc_admin, 'add_members_submenu'), 20);
      add_action('add_meta_boxes', array('CC_MetaBox', 'add_memberships_box'), 20);
      add_action('save_post', array('CC_MetaBox', 'save_membership_requirements'), 20);
    }
  }

  public function attach_settings_page() {
    $settings_page = new CC_SettingsPage();
    add_menu_page(
      __('Cart66 Cloud', 'cart66'),
      __('Cart66 Cloud', 'cart66'),
      'administrator',
      'cart66',
      array($settings_page, 'render'),
      CC_URL . 'resources/images/icon.png'
    );
  }

  public function show_cart66_account_notice() {
    if(!(get_site_option('cc_secret_key'))) {
      echo '<div class="updated"><p>Please <a href="http://cart66.com/pricing" target="_blank">create a Cart66 Cloud account</a> then enter your <a href="admin.php?page=cart66">Cart66 Cloud secret key</a>.</p></div>';
    }
  }

  public function add_settings_link($links, $file) {
    $pattern = DIRECTORY_SEPARATOR . 'cart66-cloud.php';
    if(strpos($file, $pattern) > 0) {
      $settings_link = '<a href="admin.php?page=cart66">' . __('Settings', 'cart66') . '</a>';
      array_unshift($links, $settings_link);
    }
    return $links;
  }

}
