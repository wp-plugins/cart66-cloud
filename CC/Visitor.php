<?php

class CC_Visitor {

  protected static $_token = FALSE;
  protected static $_access_list = FALSE;
  protected static $_restricted_cats = NULL;
  protected static $_excluded_cats = NULL;
  protected static $_user_data;

  public function __construct() {
    $this->load_token();
    $this->load_restricted_cats();
    $this->load_excluded_category_ids();
  }

  public function set_access_list(array $list) {
    // CC_Log::write('Setting logged in vistor access list :: ' . print_r($list, true));
    self::$_access_list = $list;
  }

  public function load_restricted_cats() {
    if(!is_array(self::$_restricted_cats)) {
      self::$_restricted_cats = get_option('ccm_category_restrictions');
      // CC_Log::write("Loaded restricted categories: " . print_r(self::$_restricted_cats, TRUE));
    }
  }

  /**
   * Return an array of category ids that the current visitor does not have permission to view
   *
   * @return array
   */
  public function load_excluded_category_ids() {
    if(!is_array(self::$_excluded_cats)) {
      self::$_excluded_cats = array();
      $category_args = array(
        'type'         => 'post',
        'child_of'     => 0,
        'parent'       => '',
        'orderby'      => 'name',
        'order'        => 'ASC',
        'hide_empty'   => 0,
        'hierarchical' => 1,
        'taxonomy'     => 'category'
      );

      $categories = get_categories($category_args);

      if($categories && !isset($categories['errors'])){
        foreach($categories as $cat) {
          if(!$this->can_view_post_category($cat->cat_ID)) {
            // CC_Log::write("Looping and Excluding category id: " . $cat->cat_ID);
            self::$_excluded_cats[] = $cat->cat_ID;
          }
        }
      }
      
    }
  }

  public function excluded_category_ids() {
    if(!is_array(self::$_excluded_cats)) {
      $this->load_excluded_category_ids();
    }
    // CC_Log::write('Returning excluded category ids: ' . print_r(self::$_excluded_cats, TRUE));
    return self::$_excluded_cats;
  }

  public function load_access_list($force=false) {
    if($force || !is_array(self::$_access_list)) {
      $token = $this->get_token();
      $lib = new CC_Library();
      $access_list = $lib->get_memberships($token);
      // CC_Log::write("Loaded access list: " . print_r($access_list, true));
      $access_list = is_array($access_list) ? $access_list : array();
      $this->set_access_list($access_list);
    }
    else {
      // CC_Log::write('Not loading access list from cloud because it is already an array and is not forced to reload :: ' . print_r(self::$_access_list, true));
    }
    return $access_list;
  }

  public function drop_access_list() {
    self::$_access_list = false;
  }

  /**
   * Return an array of std objects that hold membership skus and days_in values
   *
   * If visitor is not logged in or has no memberships an empty array is returned
   *
   * Array (
   *   [0] => stdClass Object
   *     (
   *       [sku] => basic
   *       [days_in] => 0
   *     )
   * )
   *
   * @return array
   */
  public function get_access_list() {
    $list = is_array(self::$_access_list) ? self::$_access_list : $this->load_access_list();
    return $list;
  }

  public function load_token() {
    self::$_token = false;
    if(isset($_COOKIE['ccm_token'])) {
      self::$_token = $_COOKIE['ccm_token'];
    }
  }

  public function check_remote_login() {
    if(isset($_GET['cc_customer_token']) && isset($_GET['cc_customer_first_name'])) {
      $token = CC_Common::scrub('cc_customer_token', $_GET);
      $name = CC_Common::scrub('cc_customer_first_name', $_GET);
      $this->log_in($token, $name);
      $this->sign_in_redirect();
    }
  }

  public function sign_in_redirect() {
    $admin = new CC_Admin();
    $member_home = $admin->get_option('member_home');    
    $page_id = get_queried_object_id();
    CC_Log::write("Memeber home value: $member_home :: $page_id");

    if(empty($member_home)) {
      // redirect to order history
      $lib = new CC_Library();
      $url = $lib->order_history_url();      
      CC_Log::write("Sign in redirect to order history: $url");
      wp_redirect($url);
      exit();
    }
    elseif($page_id != $member_home) {
      // redirect to member home page
      $url = get_permalink($member_home);
      CC_Log::write("Sign in redirect to WordPress URL: $url");
      wp_redirect($url);
      exit();
    }
  }

  public function log_in($token, $name) {
    $expire = 0; // Expire cookie at end of session
    $data = $token . '~' . $name;
    $_COOKIE['ccm_token'] = $data;
    self::$_token = $data;
    setcookie('ccm_token', $data, $expire, COOKIEPATH, COOKIE_DOMAIN, false, true);
    if (COOKIEPATH != SITECOOKIEPATH) {
      setcookie('ccm_token', $data, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
      CC_Log::write("Logging in CC Member: $data");
    }
    $this->load_access_list(true); // Force the reloading of the access list even if already set
  }

  /**
   * Remove the member token cookie and set the token to false.
   */
  public function log_out() {
    self::$_token = false;
    unset($_COOKIE['ccm_token']);
	  setcookie('ccm_token', ' ', time() - 3600, COOKIEPATH);
    if (COOKIEPATH != SITECOOKIEPATH) {
      setcookie('ccm_token', ' ', time() - 3600, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
    }
  }

  /**
   * Return true if the visitor has a valid member token, otherwise false.
   * 
   * @return boolean
   */
  public function is_logged_in() {
    return $this->get_token() ? true : false;
  }

  /**
   * Return the member access token, member name, or both values for the logged in visitor.
   *
   * If the visitor is not logged in or does not have a token return
   * an empty string. Unless otherwise specified by the $type parameter, 
   * the member access token is returned.
   *
   * @param string $type [full, token, name]
   * @return string
   */
  public function get_token($type='token') {
    $allowed = array('full', 'token', 'name');
    if(!in_array($type, $allowed)) {
      throw new CC_Exception("Invalid token type requested: $type");
    }

    $data = '';
    if(self::$_token) {
      list($token, $name) = explode('~', self::$_token);
      $data = array(
        'full' => self::$_token,
        'token' => $token,
        'name' => $name
      );
      $data = $data[$type];
    }

    return $data;
  }

  /**
   * Return true if the visitor should be allowed to see the link in the navigation
   *
   * @return boolean
   */
  public function can_view_link($post_id) {
    $view = true;
    $memberships = get_post_meta($post_id, '_ccm_required_memberships', true);
    $override = ($this->is_logged_in()) ? get_post_meta($post_id, '_ccm_when_logged_in', true) : get_post_meta($post_id, '_ccm_when_logged_out', true);
     
    if($override == 'show') {
      $view = true;
      CC_Log::write('Can view link because show is forced to true');
    }
    elseif($override == 'hide') {
      CC_Log::write('Can NOT view link because show is forced to false');
      $view = false;
    }
    elseif(is_array($memberships) && count($memberships)) {
      if($this->can_view_post($post_id)) {
        CC_Log::write('Can view link because visitor is logged in and has been granted access');
        $view = true;
      }
      else {
        CC_Log::write('Can NOT view link because a membership is required to view post :: ' . print_r($memberships, true));
        $view = false;
      }
    }
    else {
      //CC_Log::write('Can view link because there are no restrictions on this post');
      $view = true;
    }

    return $view;
  }

  /**
   * Return true if the visitor is allowed to view the post with the given id.
   *
   * This function always returns false if the visitor is not logged in.
   *
   * @param int The post id
   * @return boolean
   */
  public function can_view_post($post_id) {
    $allow = true;
    $memberships = get_post_meta($post_id, '_ccm_required_memberships', true);
    $post_cat_ids = wp_get_post_categories($post_id);

    // CC_Log::write("Categories for post id $post_id" . print_r($post_cat_ids, TRUE));

    // Check if visitor may view the post category
    if(count($post_cat_ids) > 0) {
      $allow = false;
      foreach($post_cat_ids as $cat_id) {
        if($this->can_view_post_category($cat_id)) {
          $allow = true;
          CC_Log::write("Allowing access to category: $cat_id");
          break;
        }
      }
    }

    if($allow) {
      if(is_array($memberships) && count($memberships)) {
        // CC_Log::write('This post requires memberships: ' . print_r($memberships, true));
        $allow = false; // only grant permission to logged in visitors with active subscriptions
        if($this->is_logged_in()) {
          $days_in = get_post_meta($post_id, '_ccm_days_in', true);
          CC_Log::write("Checking if has permission on days in: $days_in :: "  . print_r($memberships, true));
          if($this->has_permission($memberships, $days_in)) {
            CC_Log::write('This visitor has permission to view this post:' . $post_id);
            $allow = true;
          }
          else {
            CC_Log::write('Can NOT view post because the logged in visitor does not have permission');
          }
        }
        else {
          CC_Log::write('Can NOT view post because the visitor is not logged in');
        }
      }
    }

    return $allow;
  }

  public function can_view_post_category($cat_id) {
    // CC_Log::write("Checking permission for category id: $cat_id");
    $allow = TRUE;

    if(is_array(self::$_restricted_cats) && isset(self::$_restricted_cats[$cat_id])) {
      $memberships = self::$_restricted_cats[$cat_id];
      $allow = $this->has_permission($memberships);
    }

    $dbg = $allow ? "Granting permission for category id: $cat_id" : "Denying permission for category id: $cat_id";
    // CC_Log::write($dbg);

    return $allow;
  }
  

  /**
   * Return true if one of the given memberships is in the access list and at least $days_in days old
   *
   * @param array $memberships An array of one or more membership SKUs
   * @param int $days_in The number of days a membership must be active before access is granted
   * @return boolean
   */
  public function has_permission(array $memberships, $days_in=0) {
    $access_list = $this->get_access_list();
    // CC_Log::write('Checking logged in visotors access list :: ' . print_r($access_list, true));
    foreach($memberships as $sku) {
      foreach($access_list as $item) {
        $days_active = is_numeric($item['days_in']) ? $item['days_in'] : 0;
        $days_in = is_numeric($days_in) ? $days_in : 0;
        // CC_Log::write("Days in: $days_in <=> Days active: $days_active");
        if($sku == $item['sku'] && $days_in <= $days_active) {
          CC_Log::write("Permission ok: $sku :: Days in: $days_in :: " . $item['days_in']);
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Get the user data for the logged in visitor
   *
   * Set the static member variable self::$_user_data to the array of retrieved data for the logged
   * in visitor. If not data could be retrieved, self::$_user_data is set to an empty array.
   */
  public function get_user_data($force_reload=false) {
    if(!is_array(self::$_user_data) || $force_reload) {
      $lib = new CC_Library();
      if($token = $this->get_token()) {
        CC_Log::write("Called load user data using token: $token");
        self::$_user_data = $lib->get_user_data($token);
      }
      else {
        CC_Log::write('Not loading user data because nobody is logged in');
      }
    }
    else {
      CC_Log::write('Reusing user data');
    }

    return self::$_user_data;
  }

  public function get_first_name() {
    $first_name = '';
    $user_data = $this->get_user_data();
    if(isset($user_data['first_name'])) {
      $first_name = $user_data['first_name'];
    }
    return $first_name;
  }

  public function get_last_name() {
    $last_name = '';
    $user_data = $this->get_user_data();
    if(isset($user_data['last_name'])) {
      $last_name = $user_data['last_name'];
    }
    return $last_name;
  }

  public function get_email() {
    $email = '';
    $user_data = $this->get_user_data();
    if(isset($user_data['email'])) {
      $email = $user_data['email'];
    }
    return $email;
  }

  public function get_phone_number() {
    $phone_number = '';
    $user_data = $this->get_user_data();
    if(isset($user_data['phone_number'])) {
      $phone_number = $user_data['phone_number'];
    }
    return $phone_number;
  }

}

