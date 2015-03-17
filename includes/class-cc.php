<?php 
/**
 * This is a class of helper methods to make accessing data in Cart66 Cloud easier, epecially for theme development
 */
class CC {

    /**
     * Return the number of items in the current visitor's shopping cart
     * 
     * @return int
     */
    public static function cart_item_count() {
        $count = 0;
        $cart = new CC_Cloud_Cart();
        $cart_key = $cart->get_cart_key( false );
        if($cart_key) {
            $summary = $cart->summary( $cart_key );
            $count = $summary->item_count;
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
        $cart = new CC_Cloud_Cart();
        if ( $cart_key ) {
            $summary = $cart->summary( $cart_key );
            $subtotal = $summary->subtotal;
        }
        return $subtotal;
    }


    /**
     * Return true if there cart has no items, otherwise false
     *
     * @return boolean
     */
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
        $is_logged_in = false;

        if ( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $is_logged_in = $visitor->is_logged_in();
        }

        return $is_logged_in;
    }

    /**
     * Return the logged in visitor's username or an empty string if the visitor is not logged in
     *
     * @return string
     */
    public static function visitor_name() {
        $name = '';

        if( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $first_name = $visitor->get_first_name();
            $last_name = $visitor->get_last_name();
            $name = $first_name . ' ' . $last_name;
        }

        return $name;
    }

    public static function visitor_first_name() {
        $first_name = '';

        if ( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $first_name = $visitor->get_first_name();
        }

        return $first_name;
    }

    public static function visitor_last_name() {
        $last_name = '';

        if ( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $last_name = $visitor->get_last_name();
        }

        return $last_name;
    }

    public static function visitor_email() {
        $email = '';

        if ( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $email = $visitor->get_email();
        }

        return $email;
    }

    public static function visitor_phone_number() {
        $phone_number = '';

        if ( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $phone_number = $visitor->get_phone_number();
        }

        return $phone_number;
    }

    public static function order_data( $order_id ) {
        $order_data = CC_Cloud_Order::get_data( $order_id );
        return $order_data;
    }

    public static function product_price( $product_sku ) {
        $price = '';

        if ( $product_sku ) {
            try {
                $product = new CC_Cloud_Product();
                $products = $product->get_products();
                foreach( $products as $p ) {
                    if( $p['sku'] == $product_sku ) {
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

}
