<?php

class CC_Admin {

  protected $_options = null;
  protected static $_memberships = null;

  public function __construct() {
    $this->_options = get_option('ccm_access_notifications');
    $this->_restricted_cats = get_option('ccm_category_restrictions');
  }

  public function load_memberships() {
    $memberships = array();

    if(is_array(self::$_memberships)) {
      $memberships = self::$_memberships;
      CC_Log::write('Reusing memberships');
    }
    else {
      $lib = new CC_Library();
      try {
        $products = $lib->get_expiring_products();
        if(is_array($products)) {
          foreach($products as $p) {
            $memberships[$p['name']] = $p['sku'];
          }
        }
        // CC_Log::write('Loaded memberships: ' . print_r($memberships, TRUE));
        self::$_memberships = $memberships;
      }
      catch(Exception $e) {
        CC_Log::write("Failed to load memberships: " . $e->getMessage());
      }
    }

    return $memberships;
  }

  public function add_members_submenu() {
    add_submenu_page(
      'cart66',
      __('Cart66 Members', 'cart66'),
      __('Members', 'cart66'),
      'administrator',
      'cart66_members',
      array('CC_Admin', 'render_members_settings_page')
    );
  }

  public static function render_members_settings_page() {
    $data = array(
      'notifications_tab' => '',
      'restrict_categories_tab' => ''
    );
    
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'notifications';
    if ($active_tab == 'notifications') {
      $data['notifications_tab'] = 'nav-tab-active';
    }
    else {
      $data['restrict_categories_tab'] = 'nav-tab-active';
    }
    $view = CC_PATH . 'views/admin/member_settings.phtml';
    echo CC_View::get($view, $data);
  }

  public function add_secure_console_submenu() {
    add_submenu_page(
      'cart66',
      __('Secure Console', 'cart66'),
      __('Secure Console', 'cart66'),
      'administrator',
      'secure_console',
      array('CC_Admin', 'render_secure_console_page')
    );
  }

  public static function render_secure_console_page() {
    $view = CC_PATH . 'views/admin/secure_console.phtml';
    echo CC_View::get($view);
  }

  public function get_page_list() {
    $args = array(
      'sort_order' => 'ASC',
      'sort_column' => 'post_title',
      'hierarchical' => 1,
      'exclude' => '',
      'include' => '',
      'meta_key' => '',
      'meta_value' => '',
      'authors' => '',
      'child_of' => 0,
      'parent' => -1,
      'exclude_tree' => '',
      'number' => '',
      'offset' => 0,
      'post_type' => 'page',
      'post_status' => 'publish'
    ); 
    return get_pages($args); 
  }

  public function register_settings() {

    add_settings_section(
      'ccm_access_notifications',                                    // ID
      __('Access Notification Settings', 'cart66'),                  // Title
      array('CC_Admin','render_access_notifications_description'),   // Callback to render options
      'ccm_access_notifications'                                     // Page where options will be located
    ); 

    $member_home = new stdClass();
    $member_home->id = 'member_home';
    $member_home->title = __('Member home page', 'cart66');
    $member_home->description = __('The page where members will be directed after logging in', 'cart66');

    $post_types = new stdClass();
    $post_types->id = 'member_post_types';
    $post_types->title = __('Post types', 'cart66');
    $post_types->description = __('Enable membership restrictions for the selected post types.', 'cart66');

    $login_required = new stdClass();
    $login_required->id = 'login_required';
    $login_required->title = __('Login required', 'cart66');
    $login_required->description = __('Text displayed when a user must log in to access the content', 'cart66');

    $not_included = new stdClass();
    $not_included->id = 'not_included';
    $not_included->title = __('Not included', 'cart66');
    $not_included->description = __('Text displayed when the content being accessed is not included in the member\'s subscription', 'cart66');

    $fields = array($member_home, $post_types, $login_required, $not_included);
    $this->add_settings_fields_for_section($fields, 'ccm_access_notifications', 'ccm_access_notifications');
    
    register_setting( 'ccm_access_notifications', 'ccm_access_notifications' );

    add_settings_section(
      'ccm_category_restrictions',                                    // ID
      __('Restrict Access to Post Categories', 'cart66'),                          // Title
      array('CC_Admin','render_category_restrictions_description'),   // Callback to render options
      'ccm_category_restrictions'                                     // Page where options will be located
    ); 

    $category_restrictions = new stdClass();
    $category_restrictions->id = 'category_restrictions';
    $category_restrictions->title = __('Categories', 'cart66');
    $category_restrictions->description = __('Require a membership to view posts in certain categories', 'cart66');

    $fields = array($category_restrictions);
    $this->add_settings_fields_for_section($fields, 'ccm_category_restrictions', 'ccm_category_restrictions');

    register_setting( 'ccm_category_restrictions', 'ccm_category_restrictions' );
  }

  public function add_settings_fields_for_section($fields, $page, $section) {
    foreach($fields as $field) {
      $id = $section . '-' . $field->id;
      $name = $section . '[' . $field->id . ']';
      $description = $field->description;
      $title = $field->title;
      $callback = array($this, 'render_' . $field->id);
      $args = array('id' => $id, 'name' => $name, 'description' => $description);
      add_settings_field( $id, $title, $callback, $page, $section, $args );
    }
  }

  public static function render_access_notifications_description() {
    //echo '<p>CCM Access Notifications</p>';
  }

  public static function render_category_restrictions_description() {
    echo '<p>Select the memberships that are required in order to access posts for the listed categories.<br/>';
    echo 'Do not select any memberships for categories open to the public.</p>';
  }

  public function render_member_home($args) {
    $value = $this->get_option('member_home');
    $out = '<select id="' . $args['id'] . '" name="' . $args['name'] . '">';
    $out .= '<option value="">Order History</option>';

    $pages = $this->get_page_list();
    foreach($pages as $page) {
      $selected = ($value == $page->ID) ? 'selected="selected"' : '';
      $title = str_repeat('&ndash; ', count($page->ancestors)) . $page->post_title;
      $out .= '<option value="' . $page->ID . '"' . $selected . '>' . $title. '</option>';
    }

    $out .= '</select><br/>';
    $out .= '<label for="' . $args['id'] . '">' . $args['description'] . '</label>';
    echo $out;
  }

  public function render_login_required($args) {
    $value = $this->get_option('login_required');
    $out = wp_editor($value, $args['id'], array('textarea_name' => $args['name']));
    $out .= '<label for="' . $args['id'] . '">' . $args['description'] . '</label>';
    echo $out;
  }

  public function render_not_included($args) {
    $value = $this->get_option('not_included');
    $out = wp_editor($value, $args['id'], array('textarea_name' => $args['name']));
    $out .= '<label for="' . $args['id'] . '">' . $args['description'] . '</label>';
    echo $out;
  }

  public function render_member_post_types($args) {
    $selected_types = $this->get_option('member_post_types');
    if(!is_array($selected_types)) {
      $selected_types = array();
    }
    $out = '<p>' . $args['description'] . '</p>';
    $post_types = get_post_types(array('public' => TRUE));
    $post_types = array_diff($post_types, array('attachment'));
    foreach($post_types as $pt) {
      $checked = in_array($pt, $selected_types) ? 'checked="checked"' : '';
      $out .= '<input type="checkbox" name="' . $args['name'] . '[]" value="' . $pt . '" ' . $checked . ' /> '  . $pt . '<br/>';
    }
    $out .= '<input type="hidden" name="' . $args['name'] . '[]" value="none" />';
    echo $out;
  }

  public function get_option($key) {
    return isset($this->_options[$key]) ? $this->_options[$key] : '';
  }
  
  public function render_category_restrictions($args) {
    $list = $this->category_tree($args);
    echo '<p>' . $args['description'] . '</p>';
    echo $list;
  }
  

  public function category_tree($args, $parent='0', &$level=0) {
    $out = '';

    $category_args = array(
    	'type'         => 'post',
    	'child_of'     => 0,
    	'parent'       => $parent,
    	'orderby'      => 'name',
    	'order'        => 'ASC',
    	'hide_empty'   => 0,
    	'hierarchical' => 1,
    	'taxonomy'     => 'category'
    );

    $categories = get_categories($category_args);
    
    if(is_array($categories)) {
      foreach($categories as $cat) {
        $indent = str_repeat('&mdash;&nbsp;', $level);
        $out .= '<h3 class="cc_bar_head cc_gradient">' . $indent . $cat->name . '</h3>';

        $out .= '<div class="cc_cat_list">';
        $memberships = $this->load_memberships();
        foreach($memberships as $name => $id) {
          $checked = '';
          if(isset($this->_restricted_cats[$cat->term_id]) && is_array($this->_restricted_cats[$cat->term_id]) && in_array($id, $this->_restricted_cats[$cat->term_id])) {
            $checked = 'checked="checked"';
          }
          $out .= '<input type="checkbox" name="ccm_category_restrictions[' . $cat->term_id . '][]  " value="' . $id . '" ' . $checked . '> ' . $name . '<br/>';
        }
        $out .= '</div>';

        $depth = $level+1;
        $out .= $this->category_tree($args, $cat->term_id, $depth);
      }
    }
    else {
      echo "Categories is not an array. Parent: $parent :: Level: $level<br/>\n";
    }
    
    return $out;
  }  

}
