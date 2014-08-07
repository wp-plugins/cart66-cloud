<?php

class CC_ShortcodeManager {

  public static function add_media_button($context) {
    $style =  '<style type="text/css">';
    $style .= '.cart66-button-icon { ';
    $style .= '  background: url("' . CC_URL . 'resources/images/icon.png");';
    $style .= '  display: inline-block;';
    $style .= '  width: 16px;';
    $style .= '  height: 16px;';
    $style .= '  vertical-align: text-top;';
    $style .= '}';
    $style .= '</style>';
   
    $button = '<span class="cart66-button-icon"></span> Insert Product';
    $button = '<a href="#TB_inline?width=480&height=600&inlineId=cc_editor_pop_up" class="button thickbox" id="cc_product_shortcodes" title="' . __("Add Cart66 Product", 'cart66') . '">'.$button.'</a>';
    return $context . $style . $button;
  }

  public static function add_media_button_popup() {
    $product_data = array();

    try {
      $lib = new CC_Library();
      $product_data = $lib->get_products();
    }
    catch(CC_Exception_API $e) {
      $product_data = CC_Common::unavailable_product_data();
      CC_Log::write("Unable to retreive products for media button pop up: " . $e->get_message());
    }

    $data = array('product_data' => $product_data);
    $view = CC_View::get(CC_PATH . 'views/editor_pop_up.phtml', $data);
    echo $view;
  }
  
  public static function register_shortcodes() {
    add_shortcode('cc_product',              array('CC_ShortcodeManager', 'cc_product'));
    add_shortcode('cc_product_link',         array('CC_ShortcodeManager', 'cc_product_link'));
    add_shortcode('cc_show_to',              array('CC_ShortcodeManager', 'cc_show_to'));
    add_shortcode('cc_hide_from',            array('CC_ShortcodeManager', 'cc_hide_from'));
    add_shortcode('cc_cart_item_count',      array('CC_ShortcodeManager', 'cc_cart_item_count'));
    add_shortcode('cc_cart_subtotal',        array('CC_ShortcodeManager', 'cc_cart_subtotal'));
    add_shortcode('cc_visitor_name',         array('CC_ShortcodeManager', 'cc_visitor_name'));
    add_shortcode('cc_product_price',        array('CC_ShortcodeManager', 'cc_product_price'));
    add_shortcode('cc_visitor_name',         array('CC_ShortcodeManager', 'cc_visitor_name'));
    add_shortcode('cc_visitor_first_name',   array('CC_ShortcodeManager', 'cc_visitor_first_name'));
    add_shortcode('cc_visitor_last_name',    array('CC_ShortcodeManager', 'cc_visitor_last_name'));
    add_shortcode('cc_visitor_email',        array('CC_ShortcodeManager', 'cc_visitor_email'));
    add_shortcode('cc_visitor_phone_number', array('CC_ShortcodeManager', 'cc_visitor_phone_number'));
  }

  public static function cc_cart_item_count($args, $content) {
    return CC::cart_item_count();
  }

  public static function cc_cart_subtotal($args, $content) {
    return CC::cart_subtotal();
  }

  public static function cc_visitor_name($args, $content) {
    return CC::visitor_name();
  }

  public static function cc_visitor_first_name($args, $content) {
    return CC::visitor_first_name();
  }

  public static function cc_visitor_last_name($args, $content) {
    return CC::visitor_last_name();
  }

  public static function cc_visitor_email($args, $content) {
    return CC::visitor_email();
  }

  public static function cc_visitor_phone_number($args, $content) {
    return CC::visitor_phone_number();
  }

  public static function cc_product_price($args) {
    $price = '';
    $product_sku = isset($args['sku']) ? $args['sku'] : false;
    if($product_sku) {
      $lib = new CC_Library();
      try {
        $products = $lib->get_products();
        foreach($products as $p) {
          if($p['sku'] == $product_sku) {
            CC_Log::write("Getting price for product: " . print_r($p, TRUE));
            $price = $p['on_sale'] == 1 ? $p['formatted_sale_price'] : $p['formatted_price'];
          }
        }  
      }
      catch (CC_Exception_API $e) {
        $price = "Error: " . $e->getMessage();
      }
      
    }

    CC_Log::write("Returning product price for $product_sku: $price");
    return $price;
  }

  public static function cc_product($args, $content) {
    $product_loader = get_site_option('cc_product_loader', 'server');
    $lib = new CC_Library();
    $subdomain = $lib->get_subdomain();
    $id = CC_Common::rand_string(12, 'lower');
    $product_form = '';
    $client_loading = 'true';

    $product_id = isset($args['id']) ? $args['id'] : false;
    $product_sku = isset($args['sku']) ? $args['sku'] : false;
    $display_quantity = isset($args['quantity']) ? $args['quantity'] : 'true';
    $display_price = isset($args['price']) ? $args['price'] : 'true';
    $display_mode = isset($args['display']) ? $args['display'] : '';

    if($product_loader == 'server' || preg_match('/(?i)msie [2-9]/',$_SERVER['HTTP_USER_AGENT'])) {
      // if IE<=9 do not use the ajax product form method
      $product_form =  self::cc_product_via_api($args, $content);
      $client_loading = 'false';
    }

    $out = "<div class=\"cc_product_wrapper\"><div id='" . $id . "' class='cc_product' data-subdomain='$subdomain' data-sku='$product_sku' data-quantity='$display_quantity' data-price='$display_price' data-display='$display_mode' data-client='$client_loading'>$product_form</div></div>";

    return $out;
  }

  public static function cc_product_via_api($args, $content) {
    $form = ''; 
    if($error_message = CC_FlashData::get('api_error')) {
      $form .= "<p class=\"cc_error\">$error_message</p>";
    }
 
    $product_id = isset($args['id']) ? $args['id'] : false;
    $product_sku = isset($args['sku']) ? $args['sku'] : false;
    $display_quantity = isset($args['quantity']) ? $args['quantity'] : 'true';
    $display_price = isset($args['price']) ? $args['price'] : 'true';
    $display_mode = isset($args['display']) ? $args['display'] : null;
 
    if($form_with_errors = CC_FlashData::get($product_sku)) {
      $form .= $form_with_errors;
    }
    else {
      $product = new CC_Product();
      if($product_sku) {
        $product->sku = $product_sku;
      }
      elseif($product_id) {
        $product->id = $product_id;
      }
      else {
        throw new CC_Exception_Product('Unable to add product to cart without know the product sku or id');
      }
 
      try {
        $form .= $product->get_order_form($display_quantity, $display_price, $display_mode);
      }
      catch(CC_Exception_Product $e) {
        $form = "Product order form unavailable";
      }
    }
 
    return $form;
  }
 

  public static function cc_product_link($args, $content) {
    $sku = isset($args['sku']) ? $args['sku'] : false;
    if($sku) {
      $quantity = isset($args['quantity']) ? (int)$args['quantity'] : 1;
      $query_string = array(
        'cc_task=add_to_cart',
        'sku=' . $args['sku'],
        'quantity=' . $quantity
      );

      if(isset($args['redirect'])) {
        $query_string['redirect'] = 'redirect=' . urlencode($args['redirect']);
      }
      else {
        $redirect_type = get_site_option('cc_redirect_type');
        if($redirect_type == 'stay' || $redirect_type == 'stay_ajax') {
          $current_page = get_permalink();
          $url = urlencode($current_page);
          $query_string['redirect'] = "redirect=$url";
        }
      }

      $query_string = implode('&', $query_string);
      $link = get_site_url() . '?' . $query_string;
    }
    $link = "<a href='$link' rel='nofollow'>$content</a>";
    return $link;
  }

  /**
   * Only show the enclosed content to visitors with an active subscription
   * to one or more of the provided SKUs. All SKUs will be lowercased before
   * evaluation.
   *
   * Special SKU values: 
   *   members: all logged in users regardless of subscriptions or subscription status
   *   guests: all vistors who are not logged in 
   *
   * Attributes:
   *   sku: Comma separated list of SKUs required to view content
   *   days_in: The number of days old the subscription must be before the content is available
   *
   * @param array $attrs An associative array of attributes, or an empty string if no attributes are given
   * @param string $content The content enclosed by the shortcode
   * @param string $tag The shortcode tag
   */
  public static function cc_show_to($attrs, $content, $tag) {
    if(!self::visitor_in_group($attrs)) {
      $content = '';
    }
    return do_shortcode($content);
  }

  public static function cc_hide_from($attrs, $content, $tag) {
    if(self::visitor_in_group($attrs)) {
      $content = '';
    }
    return do_shortcode($content);
  }

  public static function visitor_in_group($attrs) {
    $in_group = false;
    if(is_array($attrs)) {
      $visitor = new CC_Visitor();
      $member_id = $visitor->get_token();
      $days_in = (isset($attrs['days_in'])) ? (int) $attrs['days_in'] : 0;
      
      if(isset($attrs['sku'])) {
        $skus = explode(',', strtolower(trim(str_replace(' ', '', $attrs['sku']))));
      }
      
      if(strlen($member_id) == 0 && in_array('guests', $skus)) {
        // Show content to all non-logged in visitors if "guests" is in the array of SKUs
        $in_group = true;
        CC_Log::write('Show to everyone not logged in because the sku is guests');
      }
      elseif(strlen($member_id) > 0 && !in_array('guests', $skus)) {
        // If the visitor is logged in
        if(in_array('members', $skus)) {
          // Show content to all logged in visitors if "members" is in the array of SKUs
          $in_group = true;
          CC_Log::write('Show to everyone logged in because the sky is members');
        }
        else {
          $ccm_library = new CC_Library();
          if($ccm_library->has_permission($member_id, $skus, $days_in)) {
            $in_group = true;
            CC_Log::write("Show to $member_id: " . print_r($skus, TRUE));
          }
          else {
            CC_Log::write("Cloud says member does not have permission");
          }
        }
      }
    }
    
    $dbg = $in_group ? 'YES the visitor is in the group' : 'NO the visitor is NOT in the group';
    // CC_Log::write("Visitor in group final assessment :: $dbg");
    
    return $in_group;
  }

}
