<?php
class CC_Library {

  protected static $_protocol;
  protected static $_app_domain;
  protected static $_api;
  protected static $_hosted_api;
  protected static $_secure;
  protected static $_subdomain_url;
  protected static $_subdomain = NULL;
  protected static $_products;
  protected static $_receipt_content;
  protected static $_expiring_products;

  public function __construct() {
    self::init();
  }

  public static function init() {
    if(empty(self::$_api)) {
      self::$_protocol = 'https://';
      self::$_app_domain = 'cart66.com';
      self::$_api = self::$_protocol . 'api.' . self::$_app_domain . '/1/';
      self::$_hosted_api = self::$_protocol . 'api.' . self::$_app_domain . '/hosted/1/';
      self::$_secure = self::$_protocol . 'secure.' . self::$_app_domain . '/';
      self::get_subdomain();
      self::$_subdomain_url = self::$_protocol . self::$_subdomain . '.' . self::$_app_domain . '/';
    }
  }

  public static function enqueue_scripts() {
    self::init();
    $source = self::$_protocol . 'manage.' . self::$_app_domain . '/assets/cart66.wordpress.js';
    wp_enqueue_script('cart66-wordpress', $source, 'jquery', '1.0', true);
  }

  /**
   * Return an array of arrays of product data
   * 
   *  [0] => Array (
   *    [id] => 522f543ddab99857e9000047
   *    [name] => Boomerang Hiking Boot
   *    [sku] => boot
   *    [price] => 65.0
   *    [on_sale] =>
   *    [sale_price] =>
   *    [currency] => $
   *    [expires_after] =>
   *    [formatted_price] => $65.00
   *    [formatted_sale_price] => $
   *    [digital] =>
   *    [type] => product
   *    [status] => available
   *  )
   *
   * @return array
   */
  public function get_products($force = FALSE) {
    if($force || !is_array(self::$_products)) {
      $url = self::$_api . 'products';
      $headers = array('Accept' => 'application/json');
      $response = wp_remote_get($url, self::_basic_auth_header($headers));

      if(!self::_response_ok($response)) {
        CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CC_Library::get_products failed: $url :: " . print_r($response, true));
        throw new CC_Exception_API("Failed to retrieve products from Cart66 Cloud");
      }
      else {
        self::$_products = json_decode($response['body'], true);
        CC_Log::write('Called get_products() :: Loaded product data from the cloud: '); // . print_r(self::$_products, true));  
      }
      
    }
    else {
      CC_Log::write('Called get_products() :: Reusing static product data: '); // . print_r(self::$_products, true));
    }

    return self::$_products;
  }

  /**
   * Return an array of arrays of product data
   * 
   *  [0] => Array (
   *    [id] => 522f543ddab99857e9000047
   *    [name] => Boomerang Hiking Boot
   *    [sku] => boot
   *    [price] => 65.0
   *    [on_sale] =>
   *    [sale_price] =>
   *    [currency] => $
   *    [expires_after] =>
   *    [formatted_price] => $65.00
   *    [formatted_sale_price] => $
   *    [digital] =>
   *    [type] => product
   *    [status] => available
   *  )
   *
   * @return array
   */
  public function get_search_products($query="") {
    
    $url = self::$_api . 'products/search/?search=' . $query;
    $headers = array('Accept' => 'application/json');
    $response = wp_remote_get($url, self::_basic_auth_header($headers));

    if(!self::_response_ok($response)) {
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CC_Library::get_products failed: $url :: " . print_r($response, true));
      throw new CC_Exception_API("Failed to retrieve products from Cart66 Cloud");
    }
    else {
      $output = json_decode($response['body'], true);
      CC_Log::write('Called get_products() :: Loaded product data from the cloud: '. print_r(self::$_products, true));  
    }
      
    return $output;
  }
  
  /**
   * Return an array of the expiring products (memberships & subscriptions)
   *
   * @return array
   *
   * Example of data returned
   *
   * Expiring products: Array
   * (
   *     [0] => Array
   *         (
   *             [id] => 51d10788dab9988fc5000031
   *             [name] => Premium Membership
   *             [sku] => membership
   *             [price] => 10.0
   *             [on_sale] => 
   *             [sale_price] => 
   *             [currency] => $
   *             [expires_after] => 365
   *         )
   * 
   *     [1] => Array
   *         (
   *             [id] => 51d25dd0dab99830be0000b1
   *             [name] => E-commerce Training
   *             [sku] => training
   *             [price] => 10.0
   *             [on_sale] => 
   *             [sale_price] => 
   *             [currency] => $
   *             [expires_after] => 
   *         )
   * 
   * )
   */
  public function get_expiring_products() {
    if(!empty(self::$_expiring_products)) {
      $product_data = self::$_expiring_products;
      CC_Log::write('Reusing expiring product data in the Cart66 Cloud Library');
    }
    else {
      CC_Log::write('Getting expiring products from the cloud');
      $url = self::$_api . 'products/expiring';
      $headers = array('Accept' => 'application/json');
      $response = wp_remote_get($url, self::_basic_auth_header($headers));

      if(!self::_response_ok($response)) {
        CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CC_Library::get_expiring_products failed: $url :: " . print_r($response, true));
        throw new CC_Exception_API("Failed to retrieve expiring products from Cart66 Cloud");
      }

      $product_data = json_decode($response['body'], true);
      self::$_expiring_products = $product_data;
      CC_Log::write('Loaded expiring products from the cloud: ' . print_r(self::$_expiring_products, TRUE));
    }

    return $product_data;
  }

  /**
   * Return the custom subdomain for the account of false if no subdomain is set
   * 
   * @return mixed String or FALSE
   */
  public static function get_subdomain($force=FALSE) {
    self::init();
    if($force) {
      self::$_subdomain = self::get_subdomain_from_cloud();
      update_site_option('cc_subdomain', self::$_subdomain);
      CC_Log::write('Forcing the retrieval of the subdomain from the cloud: ' . self::$_subdomain);
    }
    elseif(empty(self::$_subdomain)) {
      self::$_subdomain = get_site_option('cc_subdomain');

      if(empty(self::$_subdomain)) {
        self::$_subdomain = self::get_subdomain_from_cloud();
        update_site_option('cc_subdomain', self::$_subdomain);
        CC_Log::write('Getting the subdomain from the cloud because it is not in the database: ' . self::$_subdomain);
      }
      else {
        CC_Log::write('Using the subdomain in the database: ' . self::$_subdomain);
      }
    }
    else {
      CC_Log::write('Reusing the subdomain from the static variable: ' . self::$_subdomain);
    }

    return self::$_subdomain;
  }

  /**
   * Return the subdomain from the cloud or false
   */
  public static function get_subdomain_from_cloud() {
    self::init();
    $subdomain = false;

    $url = self::$_api . 'subdomain';
    $headers = array('Accept' => 'text/html');
    CC_Log::write("Calling cloud for subdomain URL: $url");
    $response = wp_remote_get($url, self::_basic_auth_header($headers));
    CC_Log::write("Response from cloud to get subdomain: $url " . print_r($response, true));
    
    if(self::_response_ok($response)) {
      $subdomain = $response['body'];
    }

    return $subdomain;
  }

  public function get_order_data($order_id) {
    $order_data = array();
    $url = self::$_api . "orders/$order_id";
    $headers = array('Accept' => 'application/json');
    $response = wp_remote_get($url, self::_basic_auth_header($headers));
    if(self::_response_ok($response)) {
      $order_data = json_decode($response['body'], true);
      CC_Log::write('Order data: ' . print_r($order_data, true));
    }
    return $order_data;
  }

  /**
   * Returns the HTML markup for the add to cart form for the given product id
   *
   *  GET: /products/<id>/add_to_cart_form
   */
  public function get_order_form($product_id, $redirect_url, $display_quantity=null, $display_price=null, $display_mode=null) {

    // Prepare the query string
    $params = array(
      'redirect_url' => urlencode($redirect_url)
    );

    if(isset($display_mode)) {
      $params[] = 'display=' . $display_mode;
    }

    if(isset($display_quantity)) {
      $params[] = 'quantity=' . $display_quantity;
    }

    if(isset($display_price)) {
      $params[] = 'price=' . $display_price;
    }

    $query_string = '?' . implode('&', $params);

    // Prepare the url
    $headers = array('Accept' => 'text/html');
    $url = self::$_subdomain_url . 'products/' . $product_id . '/forms/add_to_cart' . $query_string;
    CC_Log::write("Getting order form get_order_form URL: $url");
    $response = wp_remote_get($url, self::_basic_auth_header($headers));

    if(is_wp_error($response)) {
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CC_Library::get_add_to_cart_form had an error: " . print_r($response, true));
      throw new CC_Exception_API("Failed to retrieve product add to cart form from Cart66 Cloud");
    }
    elseif($response['response']['code'] != 200) {
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CC_Library::get_add_to_cart_form invalid response code: " . print_r($response, true));
      throw new CC_Exception_API("Failed to retrieve product add to cart form from Cart66 Cloud :: Response code error :: " . $response['response']['code']);
    }

    $form_html = $response['body'];
    return $form_html;
  }

  /**
   * Create a cart on Cart66 Cloud and return the cart_key
   *
   * @return string
   */
  public function create_cart($slurp_url="") {
    $url = self::$_api . 'carts';

    // Build the headers to create the cart
    $headers = array('Accept' => 'application/json');
    $args = self::_basic_auth_header($headers);

    $data = array('ip_address' => $_SERVER['REMOTE_ADDR']);
    $data = json_encode($data);
    $args['body'] = $data;

    // Post to create cart
    CC_Log::write("Create cart via library call to Cart66 Cloud: $url " . print_r($args, true));
    $response = wp_remote_post($url, $args);

    if(!$this->_response_created($response)) {
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Failed to create new cart in Cart66 Cloud: $url :: " . print_r($response, true));
      throw new CC_Exception_API("Failed to create new cart in Cart66 Cloud");
    }

    $cart_data = json_decode($response['body']);
    return $cart_data->key;
  }

  /**
   * Returns summary information for the given shopping cart
   *
   * subtotal: the sum of the totals of all the items (not including shipping, taxes, or discounts)
   * item_count: the number of items in the cart
   *
   * @return stdClass object
     */
  public function cart_summary($cart_key) {
    $headers = array('Accept' => 'application/json');
    $url = self::$_api . "carts/$cart_key/summary";
    $response = wp_remote_get($url, self::_basic_auth_header($headers));
    if(!self::_response_ok($response)) {
      if(is_object($response)) {
        $error_code = $response->get_error_code();
        if($error_code == '500') {
          CC_Log::write("Cart summary response from library: $url :: 500 Server Error");
        }
        else {
          CC_Log::write("Cart summary response from library: $url :: " . print_r($response, true));
        }
        throw new CC_Exception_API("Unable to retrieve cart summary information for cart id: $cart_key");
      }
      elseif(is_array($response)) {
        if(isset($response['response']['code']) && $response['response']['code'] == '404') {
          CC_Log::write("Cart key not found. Drop the cart: $cart_key");
          throw new CC_Exception_API_CartNotFound("Cart key not found: $cart_key");
        }
      }
    }
    
    $summary = json_decode($response['body']);
    return $summary;
  }

  /**
   * Return the URL to the view cart page on the cloud
   *
   * @return string
   */
  public function view_cart_url($cart_key) {
    return self::$_subdomain_url . 'carts/' . $cart_key;
  }

  /**
   * Return the URL to the checkout page on the cloud
   *
   * @return string
   */
  public function checkout_url($cart_key) {
    return self::$_subdomain_url . 'checkout/' . $cart_key;
  }

  /**
   * Return the URL to sign in to a customer/member account in the cloud
   *
   * @return string
   */
  public function sign_in_url($redirect_url) {
    $encoded_redirect_url = empty($redirect_url) ? '' : '?redirect_url=' . urlencode($redirect_url);
    $url = self::$_subdomain_url . 'sign_in' . $encoded_redirect_url;
    return $url;
  }

  public function sign_out_url($redirect_url) {
    $redirect_url = urlencode($redirect_url);
    $url = self::$_subdomain_url . 'sign_out?redirect_url=' . $redirect_url;
    return $url;
  }

  public function order_history_url() {
    return self::$_subdomain_url;
  }

  public function profile_url() {
    return self::$_subdomain_url . 'profile';
  }

  public function add_to_cart($cart_key, $post_data) {
    $url = self::$_subdomain_url . "carts/$cart_key/items";
    $headers = self::_basic_auth_header();
    $headers = array(
      'sslverify' => false,
      'method' => 'POST',
      'body' => $post_data,
      'headers' => $headers['headers']
    );
    $response = wp_remote_post($url, $headers);
    return $response;
  }

  public function get_receipt_content($order_number) {

    if(!empty(self::$_receipt_content)) {
      // Only call the cloud if necessary
      CC_Log::write('Already have receipt content - not calling the cloud for it.');
      return self::$_receipt_content;
    }

    $url = self::$_subdomain_url . "receipt/$order_number";
    $response = wp_remote_get($url, array('sslverify' => false));
    CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Receipt content response from:\n$url\n\n" . print_r($response, true));
    if(!is_wp_error($response)) {
      if($response['response']['code'] == '200') {
        self::$_receipt_content = $response['body'];
        return self::$_receipt_content;
      }
    }
    else {
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to locate a receipt with the order number: $order_number");
      throw new CC_Exception_Store_ReceiptNotFound('Unable to locate a receipt with the given order number.');
    }
  }

  /**
   * Return true if the member has an active subscription to one or more of the memberships
   * and the membership has been active for at least $days_in days.
   *
   * @param string $member_token The token for the given member
   * @param array $skus An array of product SKUs
   * @param int $days_in The number of days the membership must be active before permission is granted
   * @return boolean True if permission is granted otherwise false
   */ 
  public function has_permission($member_token, $skus, $days_in=0) {
    $skus = urlencode(implode(',', $skus));
    $url = self::$_api . "memberships/verify/$member_token/$skus?days_in=$days_in"; 
    CC_Log::write("Checking for permission for member token: $member_token :: $url");
    $response = wp_remote_get($url, self::_basic_auth_header());
    $allow = self::_response_ok($response) ? true : false;
    return $allow;
  }

  /**
   * Return an array of memberships and subscriptions for the visitor identified by the given token
   *
   * Example return value
   * Array (
   *    [0] => Array
   *        (
   *            [sku] => lifetime
   *            [days_in] => 0
   *            [status] => active
   *        )
   *
   *    [1] => Array
   *        (
   *            [sku] => basic
   *            [days_in] => 50
   *            [status] => canceled
   *        )
   *
   *    [2] => Array
   *        (
   *            [sku] => premium
   *            [days_in] => 50
   *            [status] => expired
   *        )
   * )
   *
   * @param string $token The logged in member token
   * @param string $status The types of memberships and subscriptions to include (all, active, canceled, expired)
   * @return array
   */
  public function get_memberships($token, $status='active') {
    $memberships = array();
    if(!empty($token) && strlen($token) > 3) {
      $url = self::$_api . "memberships/$token";
      // CC_Log::write("Getting memberships from the cloud :: $url");
      $headers = array('Accept' => 'application/json');
      $response = wp_remote_get($url, self::_basic_auth_header($headers));
      if(self::_response_ok($response)) {
        $json = $response['body'];
        $all = json_decode($json, true);
        if($status == 'all') {
          $memberships = $all;
        }
        else {
          foreach ($all as $order) {
            if(isset($order['status']) && $order['status'] == $status) {
              $memberships[] = $order;
            }
          }
        }
      }
      CC_Log::write("$url\nReceived membership list: " . print_r($memberships, true));
    }
    return $memberships;
  }

  public function get_secret_key($hash, $domain_id) {
    $key = false;
    $url = self::$_hosted_api . "secret_key/$domain_id/$hash";
    CC_Log::write("Get secret key: $url");
    $response = wp_remote_get($url, $headers);
    if(self::_response_ok($response)) {
      $key = $response['body'];
    }
    return $key;
  }

  /**
   * Return an array of user data
   *
   * If no data could be retrieved and empty array is returned.
   *
   * @return array
   */
  public function get_user_data($token) {
    $user_data = array();
    if(!empty($token) && strlen($token) > 3) {
      $url = self::$_api . "accounts/$token";
      $headers = array('Accept' => 'application/json');
      $response = wp_remote_get($url, self::_basic_auth_header($headers));
      CC_Log::write('Get user data response: ' . print_r($response, true));
      if(self::_response_ok($response)) {
        $json = $response['body'];
        $user_data = json_decode($json, true);
        CC_Log::write('Received user data: ' . print_r($user_data, true));
      }
    }
    return $user_data;
  }

  /* ==========================================================================
   * Protected functions
   * ==========================================================================
   */

  protected static function _basic_auth_header($extra_headers=array()) {
    $username = get_site_option('cc_secret_key');
    $password = ''; // not in use
    $headers = array(
      'sslverify' => false,
      'timeout' => 30,
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
      )
    );

    if(is_array($extra_headers)) {
      foreach($extra_headers as $key => $value) {
        $headers['headers'][$key] = $value;
      }
    }

    //CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Built headers :: " . print_r($headers, true));
    return $headers;
  }

  protected static function _response_ok($response) {
    $ok = true;
    if(is_wp_error($response) || $response['response']['code'] != 200) {
      $ok = false;
    }
    return $ok;
  }

  protected function _response_created($response) {
    $ok = true;
    if(is_wp_error($response) || $response['response']['code'] != 201) {
      $ok = false;
    }
    return $ok;
  }

}
