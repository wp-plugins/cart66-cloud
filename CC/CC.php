<?php 
/**
 * This is a class of helper methods to make accessing data in Cart66 Cloud easier for theme development
 */

class CC {

  /**
   * Return the number of items in the current visitor's shopping cart
   * 
   * @return int
   */
  public static function cart_item_count() {
    $count = 0;
    $cart_key = CC_Cart::get_cart_key(false);
    if($cart_key) {
      $lib = new CC_Library();
      $cart = $lib->cart_summary($cart_key);
      $count = $cart->item_count;
    }
    return $count;
  }

  /**
   * Return the total price of the cart for the current visitor formatted as currency
   *
   * @return string
   */
  public static function cart_subtotal() {
    $subtotal = '';
    $cart_key = CC_Cart::get_cart_key(false);
    if($cart_key) {
      $lib = new CC_Library();
      $cart = $lib->cart_summary($cart_key);
      $subtotal = $cart->subtotal;
    }
    return $subtotal;
  }


  public static function is_cart_empty() {
    $count = self::cart_item_count();
    return $count == 0; 
  }

  /**
   * Return true if the current visitor is logged in, otherwise return false.
   * 
   * @return boolean
   */
  public static function is_logged_in() {
    $visitor = new CC_Visitor();
    $is_logged_in = $visitor->is_logged_in();
    return $is_logged_in;
  }

  /**
   * Return the logged in visitor's username or an empty string if the visitor is not logged in
   *
   * @return string
   */
  public static function visitor_name() {
    $visitor = new CC_Visitor();
    $first_name = $visitor->get_first_name();
    $last_name = $visitor->get_last_name();
    return $first_name . ' ' . $last_name;
  }

  public static function visitor_first_name() {
    $visitor = new CC_Visitor();
    $first_name = $visitor->get_first_name();
    return $first_name;
  }

  public static function visitor_last_name() {
    $visitor = new CC_Visitor();
    $last_name = $visitor->get_last_name();
    return $last_name;
  }

  public static function visitor_email() {
    $visitor = new CC_Visitor();
    $email = $visitor->get_email();
    return $email;
  }

  public static function visitor_phone_number() {
    $visitor = new CC_Visitor();
    $phone_number = $visitor->get_phone_number();
    return $phone_number;
  }

  public static function order_data($order_id) {
    $lib = new CC_Library();
    $order_data = $lib->get_order_data($order_id);
    return $order_data;
  }

  public static function product_search($query){
    $query = (isset($_REQUEST['q'])) ? $_REQUEST['q'] : '';
    $lib = new CC_Library();
    echo json_encode($lib->get_search_products($query));
    die();
  }
}
