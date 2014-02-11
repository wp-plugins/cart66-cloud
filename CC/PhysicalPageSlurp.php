<?php

class CC_PhysicalPageSlurp {

  public static function detect_slurp() {
    $is_slurp = false;
    self::detect_slurp_by_named_page_id();
    if(self::detect_slurp_by_slug() || self::detect_slurp_by_numeric_page_id()) {
      $is_slurp = true;
      CC_Log::write('This is a page slurp');
      add_filter('wp_title', 'CC_PhysicalPageSlurp::set_page_title');
      add_filter('the_title', 'CC_PhysicalPageSlurp::set_page_heading');
      self::check_for_receipt();
    }
    else {
      CC_Log::write('Did not detect a physical page slurp');
    }

    return $is_slurp;
  }


  /**
   * Return the id of the page slurp template.
   *
   * If the page slurp template cannot be found, return false.
   *
   * @return mixed int or false
   */
  public static function page_id() {
    $page_id = false;
    $slurp_mode = get_site_option('cc_page_slurp_mode', 'virtual');
    if(is_admin() || $slurp_mode == 'physical') {
      $page = get_page_by_title('{{cart66_title}}');
      if(is_object($page) && $page->ID > 0) {
        $page_id = $page->ID;
      }
    }
    return $page_id;
  }

  public static function detect_slurp_by_slug() {
		global $wp;
		global $wp_query;

    $is_slurp = false;
    if(strtolower($wp->request) == 'page-slurp-template') {
      $is_slurp = true;
      CC_Log::write('Slurp detected by slug');
    }
    else {
      CC_Log::write('Did not detect page slurp by slug :: ' . $wp->request);
    }

    return $is_slurp;
  }

  /**
   * Look for requests to the page slurp template by name with permalinks turned off.
   * If this is a named page slurp request, redirect to the numeric page slurp URL
   */
  public static function detect_slurp_by_named_page_id() {
		global $wp;
		global $wp_query;

    if(isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == 'page-slurp-template') {
      $page_slurp_id = self::page_id();
      if($page_slurp_id) {
        CC_Log::write('Named slurp request detected. Building redirect');

        $params = array(
          'page_id' => 'page_id=' . $page_slurp_id,
          'cc_order_id' => null,
          'cc_page_name' => null,
          'cc_page_title' => null
        );

        foreach($params as $key => $value) {
          if(CC_Common::starts_with($key, 'cc_')) {
            if(isset($_GET[$key])) {
              $params[$key] = $key . '=' . $_GET[$key];
            }
            else {
              unset($params[$key]);
            }
          }
        }

        CC_Log::write('Params array: ' . print_r($params, true));
        $query_string = implode('&', $params);
        $link = home_url() . '?' . $query_string;
        CC_Log::write("Redirecting to $link");
        wp_redirect($link, 302);
        // http://cart66.dev/?page_id=page-slurp-template&cc_order_id=350F6340B98454B2F3CA0B2F&cc_page_name=receipt&cc_page_title=Receipt
        exit();

      } // End physical page slurp template was found
    } // End this is a request for the page-slurp-template
  }


  /**
   * Return true if the requested page is the page slurp page requested with permalinks turned off
   *
   * @return boolean True if the page slurp templated is requested, otherwise false.
   */
  public static function detect_slurp_by_numeric_page_id() {
		global $wp;

    $is_slurp = false;
    $page_slurp_id = self::page_id();

    if($page_slurp_id) {
      if(isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == $page_slurp_id) {
          $is_slurp = true;
      } // End page_id is in query vars
    } // End physical page slurp template was found

    return $is_slurp;
  }
  
  public static function set_page_title($content) {
    if(strpos($content, '{{cart66_title}}') !== false) {
      if(isset($_GET['cc_page_title'])) {
        $content = str_replace('{{cart66_title}}', $_GET['cc_page_title'], $content);
      }
    }
    return $content;
  }

  public static function set_page_heading($content) {
    if(strpos($content, '{{cart66_title}}') !== false) {
      if(isset($_GET['cc_page_name'])) {
        $content = str_replace('{{cart66_title}}', $_GET['cc_page_name'], $content);
      }
    }
    return $content;
  }

	public static function check_for_receipt() {
    // Drop the cart key cookie if the receipt page is requested
    if(isset($_GET['cc_order_id']) && isset($_GET['cc_page_name']) && strtolower($_GET['cc_page_name']) == 'receipt') {
      CC_Log::write("Receipt page requested - preparing to drop the cart");
      CC_Cart::drop_cart();
      add_filter('the_content', array('CC_PhysicalPageSlurp', 'load_receipt'));
    }
    else {
      CC_Log::write('This does not look like a page slurp receipt request: ', print_r($_REQUEST, true));
    }
	}

  public static function load_receipt($content) {
    $order_id = '';
    $receipt = '';

    if(isset($_REQUEST['cc_order_id'])) {
      $order_id = $_REQUEST['cc_order_id'];
      try {
        $lib = new CC_Library();
        $receipt = $lib->get_receipt_content($order_id);
        do_action('cc_load_receipt', $order_id);
      }
      catch(CC_Exception_Store_ReceiptNotFound $e) {
        $receipt = '<p>Unable to find receipt for the given order number.</p>';
      }
    }
    else {
      $receipt = '<p>Unable to find receipt because the order number was not provided.</p>';
    }

    $content = str_replace('{{cart66_content}}', $receipt, $content);

    return $content;
  }

  public static function hide_page_slurp($pages) {
    $page_slurp_id = self::page_id();
    if($page_slurp_id) {
      foreach($pages as $index => $page) {
        if($page->ID == $page_slurp_id) {
          unset($pages[$index]);
        }
      }
    }
    return $pages;
  }

  /**
   * Creates physical page slurp template page and returns the page id.
   *
   * If the page could not be created, return 0.
   *
   * @return int
   */
  public static function create_template() {
    $page_slurp_id = self::page_id();
    if(!$page_slurp_id) {
      $page = array(
        'post_title' => '{{cart66_title}}',
        'post_content' => '{{cart66_content}}',
        'post_name' => 'page-slurp-template',
        'post_parent' => 0,
        'post_status' => 'publish',
        'post_type' => 'page',
        'comment_status' => 'closed',
        'ping_status' => 'closed'
      );
      $page_slurp_id = wp_insert_post($page);
      CC_Log::write("Created page slurp template page with ID: $page_slurp_id");
    }
    else {
      $page = array(
        'ID' => $page_slurp_id,
        'post_title' => '{{cart66_title}}',
        'post_name' => 'page-slurp-template',
        'post_status' => 'publish',
        'post_type' => 'page',
        'comment_status' => 'closed',
        'ping_status' => 'closed'
      );
      wp_update_post($page);
      CC_Log::write("Updating an existing post for the page slurp template page: $page_slurp_id");
    }
    return $page_slurp_id;
  }

}
