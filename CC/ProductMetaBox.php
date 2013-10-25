<?php

class CC_ProductMetaBox {
  
  public static function add() {
    
  	// Metabox location settings
  	$post_types = apply_filters('cc_product_meta_box_post_types', array('post') ); // only show on product custom post type
  	$context = apply_filters('cc_product_meta_box_context', 'side');
  	$priority = apply_filters('cc_product_meta_box_priority', 'high');

    foreach($post_types as $post_type) {
      add_meta_box(  
        'cc_product_meta_box',                 // id  
        'Cart66 Cloud Products',               // title  
        array('CC_ProductMetaBox', 'draw'),    // callback  
        $post_type,
        $context,
        $priority
      );
    }
  }
  
  public static function draw($post) {
    $cc = new CC_Library();
    
    try {
      $product_data = $cc->get_products();
    }
    catch(CC_Exception_API $e) {
      $product_data = CC_Common::unavailable_product_data();
    }
    
    $products = array();
    foreach($product_data as $p) {
      $product = new CC_Product();
      $product->id = $p['id'];
      $product->name = $p['name'];
      $products[] = $product;
    }
    
    $view = CC_PATH . 'views/product_meta_box.phtml';
    $cc_product_id = get_post_meta($post->ID, 'cc_product_id', true);
    $data = array('post_id' => $post->ID, 'cc_product_id' => $cc_product_id, 'products' => $products);
    echo CC_View::get($view, $data);
  }
  
  public static function save($post_id) {
    // Do not save during autosaves
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not saving because I think it is doing an autosave");
      return;
    }
        
    // Only save when the post type is cart66_product
    if(isset($_POST['post_type'])) {
      if('cart66_product' == $_POST['post_type']) {
        if(!current_user_can('edit_page', $post_id)) {
          CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The current user may not perform this action");
          return;
        }
      }
      else {
        CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not saving because not a cart66 custom post type: " . $_POST['post_type']);
      }
      // Do not save unless nonce can be verified
      if(!isset($_POST['cc_nonce']) || !wp_verify_nonce($_POST['cc_nonce'], 'cc_save_product_id')) {
        CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not saving cart66 product id due to nonce failure");
        return;
      }
      
      // Everything looks good, so update the post meta
      $meta_key = 'cc_product_id';
      $meta_value = CC_Common::scrub('cc_product_id', $_POST);
      update_post_meta($post_id, $meta_key, $meta_value);
    }
    
  }
  
}
