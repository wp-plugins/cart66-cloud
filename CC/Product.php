<?php

class CC_Product extends CC_Model {

  protected $_post;

  /**
   * Optionally construct object with Cart66 Cloud product id
   */
  public function __construct($id='') {
    $this->_data = array(
      'id' => '',
      'name' => '',
      'sku' => '',
      'price' => '',
      'on_sale' => '',
      'sale_price' => '',
      'currency_symbol'
    );
  }

  /**
   * Set the WordPress post and the Cart66 Cloud product id.
   *
   * If the post does not have a Cart66 Cloud product id, the current
   * value of the product id is not changed.
   */
  public function set_post($post) {
    $this->_post = $post;
    $product_id = get_post_meta($post->ID, 'cc_product_id', true);
    if(!empty($product_id)) {
      $this->id = $product_id;
    }
  }

  /**
   * Return the WordPress post associated with the Cart66 Cloud product id
   *
   * If no post is found or if no product id is available return false;
   *
   * @return mixed stdClass WordPress Post or false
   */
  public function get_post() {
    $post = false;
    if(isset($this->_post)) {
      $post = $this->_post;
    }
    elseif(strlen($this->id) > 1) {
      $posts = wp_get_posts(array('meta_key' => 'cc_product_id', 'meta_value' => $this->id));
      if(count($posts)) {
        $this->_post = $posts[0];
        $post = $this->_post;
      }
    }
    return $post;
  }

   /**
   *  /products/<id>/add_to_cart_form
   *  Returns HTML
   */

  public function get_order_form($display_quantity='true', $display_price='true', $display_mode=null) {
    $html = 'Product not available';

    // Figure out about the product id vs sku
    $product_id = $this->id;
    if(strlen($this->sku) > 0) {
      $product_id = $this->sku;
    }

    if(strlen($product_id) > 0) {
      try {
        $lib = new CC_Library();
        $redirect_url = CC_Cart::get_redirect_url();
        $html = $lib->get_order_form($product_id, $redirect_url, $display_quantity, $display_price, $display_mode);
      }
      catch(CC_Exception_API $e) {
        $html = "Unable to retrieve product order form";
      }
    }
    else {
      throw new CC_Exception_Product('Unable to get add to cart form because the product id is not available');
    }

    return $html;
  }

}
