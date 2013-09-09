<?php

class CC_TaskDispatcher {

  /**
   * Keys: Query string or hidden form field C66_task values
   * Values: Function names to handle the specified task
   */
  private static $_init_tasks = array(
    'admin_save_settings' => 'admin_save_settings',
    'add_to_cart'         => 'add_to_cart',
    'download_log'        => 'download_log',
    'reset_log'           => 'reset_log',
    'sky'                 => 'sky_link',
    'version'             => 'get_plugin_version'
  );
  
  /**
   * Dispatch tasks to be run during the init action
   */
  public static function dispatch_init() {
    self::dispatch_tasks(self::$_init_tasks);
  }

  /**
   * Make sure the task is a valid task name then call it
   * 
   * @param array 
   */
  public static function dispatch_tasks(array $tasks) {
    $ajax_call = false;
    $url = $_SERVER['REQUEST_URI'];
    if(strpos($url, 'admin-ajax.php') > 0) {
      $ajax_call = true;
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Doing AJAX :: Not dispatching any tasks");
    }

    if(!$ajax_call && isset($_REQUEST['cc_task'])) {
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not doing AJAX :: Preparing to process task from $url");
      $task = $_REQUEST['cc_task'];
      if(in_array($task, array_keys($tasks))) {
        $dispatch = $tasks[$task];
        CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Dispatching task: $task :: $dispatch");
        self::$dispatch();
      }
    }
  }

  public static function admin_save_settings() {
    $settings_page = new CC_SettingsPage();
    $settings_page->save_settings();
  }

  public static function add_to_cart() {
    $post_data = false;

    CC_Cart::get_cart_key(); // Create cart if one does not already exist.
    $redirect_url = CC_Cart::get_redirect_url();
    CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Default redirect url is now set to: $redirect_url");

    // Run hook before the product is added to the cart
    do_action('cc_before_add_to_cart');

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
      $post_data = $_POST;
    }
    else {
      if(isset($_GET['sku'])) {
        $product_id = $_GET['sku'];
        $quantity = 1;
        if(isset($_GET['quantity'])) {
          $quantity = (int)$_GET['quantity'];
        }

        if(isset($_GET['redirect'])) {
          CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirect is set in $_GET: " . $_GET['redirect']);
          $redirect = strtolower($_GET['redirect']);
          if($redirect == 'checkout') {
            $redirect_url = CC_Cart::checkout_url();
            CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set redirect url to the checkout url: $redirect_url");
          }
          elseif($redirect == 'cart') {
            $redirect_url = CC_Cart::view_cart_url();
            CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set redirect url to the view cart url: $redirect_url");
          }
          else {
            $redirect_url = $redirect;
            CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set redirect url to a custom url: $redirect_url");
          }
        }
        $post_data = array(
          'product_id' => $product_id,
          'quantity' => $quantity
        );
      }      
    }

    CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Calling add to cart with this data: " . print_r($post_data, true));
    $response = CC_Cart::add_to_cart($post_data);
    CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Add to cart response: " . print_r($response, true));
    $response_code = $response['response']['code'];
    if($response_code == '201') {
      do_action('cc_after_add_to_cart');
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] After adding to cart - about to redirect: $redirect_url");
      wp_redirect($redirect_url);
      die();
    }
    else {
      $sku = $_POST['sku'];
      CC_FlashData::set($sku, $response['body']);
    }
  }

  public static function download_log() {
    $filename = CC_PATH . '/log.txt';
    $log = file_get_contents($filename);
    header('Content-Disposition: attachment; filename="cart66-log.txt"');
    echo $log;
    die();
  }

  public static function reset_log() {
    $filename = CC_PATH . '/log.txt';
    if(file_exists($filename)) {
      if(is_writeable($filename)) {
        file_put_contents($filename, '');
        CC_FlashData::set('task_message', 'The log file has been reset');
      }
      else {
        $message = __('Unable to reset the log file because the log file cannot be written to', 'cart66');
        CC_FlashData::set('task_message', $message);
      }
    }
    else {
      $message = __('Unable to reset the log file because the log file does not exist', 'cart66');
      CC_FlashData::set('task_message', $message);
    }
  }

  /**
   * Attempt to retrieve and save the Cart66 Cloud key for the specified sky account
   *
   * http://site.com/?cc_task=sky&account_id=123&domain_id=123&salt=123
   */
  public static function sky_link() {
    $account_id = (int)$_GET['account_id'];
    $domain_id = (int)$_GET['domain_id'];
    $salt = $_GET['salt'];
    $hash = md5($account_id . $domain_id . $salt);
    CC_Log::write("Sky link MD5: $hash");

    $lib = new CC_Library();
    $value = $lib->get_secret_key($hash, $domain_id);
    if($value) {
      if(!update_site_option('cc_secret_key', $value)) {
        CC_Log::write('Failed to save Cart66 Cloud key');
      }
    }
  }

  public static function get_plugin_version() {
    $version = CC_Common::get_version_number();
    die($version);
    return $version;
  }
}
