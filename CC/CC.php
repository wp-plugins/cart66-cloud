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
    $name = $visitor->get_token('name');
    return $name;
  }

}
