<?php

class CC_Cart {

    protected static $cart_summary = null;

    /**
     * Drop the cc_cart_key cookie
     */
    public static function drop_cart() {
        cc_set_cookie( 'cc_cart_key', '' );
        unset( $_COOKIE['cc_cart_key'] );
    }

    public static function get_summary() {
        if ( !isset( self::$cart_summary ) ) {
            self::$cart_summary = self::load_summary();
        }

        return self::$cart_summary;
    }

    public static function get_cart_key( $create_if_empty = true ) {
        $cloud_cart = new CC_Cloud_Cart();
        $cart_key = $cloud_cart->get_cart_key( $create_if_empty );
        return $cart_key;
    }

    /**
     * Return stdClass summary of cart state
     *
     * If the cart is empty, the subtotal and item count will both be null.
     *
     * $summary->subtotal = '$125.00';
     * $summary->item_count = 3;
     *
     * @return stdClass
     */
    protected static function load_summary() {
        $summary             = new stdClass();
        $summary->subtotal   = null;
        $summary->item_count = null;
        $summary->api_ok     = true;

        $cloud_cart = new CC_Cloud_Cart();

        if ( $cart_key = $cloud_cart->get_cart_key( false ) ) {
            try {
                $summary = $cloud_cart->summary( $cart_key );
                if ( is_object( $summary ) ) {
                    $summary->api_ok = true;
                    self::$cart_summary = $summary;
                }
            }
            catch( CC_Exception_API_CartNotFound $e ) {
                CC_Log::write( "The cart key could not be found. Dropping the cart cookie: $cart_key" );
                $summary->api_ok = false;
                self::drop_cart();
            }
            catch( CC_Exception_API $e ) {
                CC_Log::write( "Unable to retrieve cart from Cart66 Cloud due to API failure: $cart_key" );
                $summary->api_ok = false;
            }
        }

        return $summary;
    }


    public static function get_redirect_url() {
        $redirect_type = CC_Admin_Setting::get_option( 'cart66_main_settings', 'add_to_cart_redirect_type' );
        $url = new CC_Cloud_URL();

        if ( $redirect_type == 'view_cart' ) {
            $redirect_url = $url->view_cart();
        } elseif ( $redirect_type == 'checkout' ) {
            $redirect_url = $url->checkout();
        } else {
            $redirect_url = $_SERVER['REQUEST_URI']; // Stay on same page
        }

        return $redirect_url;
    }

    public static function get_page_slurp_url() {
        $url = get_site_url() . '?page_id=page-slurp-template';
        return $url;
    }

    public static function get_order_form( $product_id, $redirect_url, $display_quantity, $display_price, $display_mode ) {
        $cloud_cart = new CC_Cloud_Cart();
        return $cloud_cart->get_order_form( $product_id, $redirect_url, $display_quantity, $display_price, $display_mode );
    }

    public static function add_to_cart( $post_data ) {
        $post_data = cc_strip_slashes( $post_data );
        $cloud_cart = new CC_Cloud_Cart();
        $cart_key = $cloud_cart->get_cart_key();
        $response = $cloud_cart->add_to_cart( $cart_key, $post_data );

        if( is_wp_error( $response ) ) {
            $response_code = $response->get_error_code();
            $response = array(
                'response' => array(
                    'code' => $response_code
                )
            );
        }

        return $response;
    }

    public static function ajax_add_to_cart() {
        $response = self::add_to_cart($_POST);

        if(is_wp_error($response)) {
            $response_code = $response->get_error_code();
        }
        else {
            $response_code = $response['response']['code'];
        }

        // CC_Log::write('Ajax response code: ' . print_r($response_code, TRUE));

        if($response_code == '500') {
            header('HTTP/1.1 500: SERVER ERROR', true, 500);
        }
        elseif($response_code != '201') {
            header('HTTP/1.1 422: UNPROCESSABLE ENTITY', true, 422);
            echo $response['body'];
        }
        else {
            $redirect_type = CC_Admin_Setting::get_option( 'cart66_main_settings', 'add_to_cart_redirect_type' );
            $out = array('task' => 'redirect');
            $url = new CC_Cloud_URL();

            if('view_cart' == $redirect_type) {
                $out['url'] = $url->view_cart();
            }
            elseif('checkout' == $redirect_type) {
                $out['url'] = $url->checkout();
            }
            else {
                $product_info = json_decode($response['body'], true);
                $product_name = $product_info['product_name'];
                $message = $product_name . ' added to cart';

                $view_cart = '<a href="' . $url->view_cart() . '" class="btn btn-small pull-right ajax_view_cart_button" rel="nofollow">View Cart <i class="icon-arrow-right" /></a>';

                $out = array(
                    'task' => 'stay',
                    'response' => $message . $view_cart
                );
            }

            CC_Log::write('Ajax created :: response code 201 :: output: ' . print_r($out, TRUE));

            header('HTTP/1.1 201 Created', true, 201);
            header('Content-Type: application/json');
            echo json_encode( $out );

            do_action('cc_after_add_to_cart');
        }
        die();
    }


    public static function item_count() {
        self::load_summary();
        return self::$cart_summary->item_count > 0 ? self::$cart_summary->item_count : 0;
    }

    public static function subtotal() {
        self::load_summary();
        return self::$cart_summary->subtotal;
    }

    public static function show_errors() {
        $data = CC_Flash_Data::get_all( 'cart_error' );
        if ( count( $data ) ) {
            $data['link'] = add_query_arg(array('cc-task' => FALSE, 'sku' => FALSE, 'quantity' => FALSE, 'redirect' => FALSE));
            CC_Log::write('Checking for cart errors in footer: ' . print_r( $data, true ) );
            $view = CC_View::get( CC_PATH . 'views/error-overlay.php', $data );
            echo $view;
        }
    }

}
