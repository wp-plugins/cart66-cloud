<?php if($api_ok): ?>
  <?php if(isset($item_count) && $item_count > 0): ?>
    <ul class="cc_cart_widget_list">
      <li class="cc_cart_widget_list_item">
        <div class="cc_cart_widget_summary">
          <a class="cc_cart_widget_link" href="<?php echo $view_cart_url ?>" rel="nofollow">
            <span class="cc_cart_widget_item_count"><?php echo $item_count; ?></span>
            <span class="cc_cart_widget_item_count_text"> <?php echo _n('item', 'items', $item_count, 'cart66'); ?></span> 
            <span class="cc_cart_widget_item_count_dash">&ndash;</span>
            <span class="cc_cart_widget_price"><?php echo $subtotal; ?></span>
          </a>
        </div>

        <div class="cc_cart_widget_links">
          <a class="cc_cart_widget_view_cart" href="<?php echo $view_cart_url; ?>" rel="nofollow"><?php _e('View Cart', 'cart66'); ?></a>
          <span class="cc_cart_widget_link_separator"> | </span>
          <a class="cc_cart_widget_checkout" href="<?php echo $checkout_url; ?>" rel="nofollow"><?php _e('Checkout', 'cart66'); ?></a>
        </div>
      </li>
    </ul>
  <?php else: ?>
    <ul class="cc_cart_widget_list">
      <li class="cc_cart_widget_list_item" class="cc_cart_empty"><?php _e('Your cart is empty.', 'cart66'); ?></li>
    </ul>
  <?php endif; ?>
<?php else: ?>
  <ul class="cc_cart_widget_list">
    <li class="cc_cart_widget_list_item" class="cc_cart_empty"><?php _e('Unable to retrieve cart.', 'cart66'); ?></li>
  </ul>
<?php endif; ?>
