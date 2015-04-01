<?php

class CC_Cloud_URL {

    public static $cloud;

    public function __construct() {
        if ( ! isset( self::$cloud ) ) {
            self::$cloud = new CC_Cloud_API_V1(); 
        }
    }

    public function sign_in( $send_return_url = false ) {
        $url = self::$cloud->subdomain_url() . 'sign_in';
        $send_return_url = is_bool( $send_return_url ) ? $send_return_url : false; // A non-boolean may be passed in from CC_Library

        if ( $send_return_url ) {
            $return_url = '';
            $page_id = CC_Admin_Setting::get_option( 'cart66_members_notifications', 'member_home' );

            if ( $page_id > 0 ) {
                $return_url = get_permalink( $page_id );
                $encoded_return_url = empty( $return_url ) ? '' : '?return_url=' . urlencode( $return_url );
                $url .=  $encoded_return_url;
            }

        }

        return $url;
    }

    public function sign_out( $return_url = '' ) {
        $return_url = empty( $return_url ) ? home_url() : $return_url;
        $return_url = urlencode( $return_url );
        $url = self::$cloud->subdomain_url() . 'sign_out?redirect_url=' . $return_url;
        return $url;
    }

    public function order_history() {
        return self::$cloud->subdomain_url();
    }

    public function profile() {
        return self::$cloud->subdomain_url() . 'profile';
    }

    /**
     * Get the URL to view the secure cart in the cloud
     *
     * The cart must exist and have a cart key in order to view the cart. If no
     * cart key exists, the view cart URL is null
     *
     * @param boolean $force_create_cart When true, create a cart if no cart key exists
     * @return string
     */
    public function view_cart( $force_create_cart = false ) {
        $url = null;

        // Do not create a cart if the id is not available in the cookie unless it is forced
        $cloud_cart = new CC_Cloud_Cart();
        $cart_key = $cloud_cart->get_cart_key( $force_create_cart );

        if ( $cart_key ) {
            $subdomain_url = self::$cloud->subdomain_url();
            if ( $subdomain_url ) {
                $url =  $subdomain_url . 'carts/' . $cart_key;
            }
        }

        CC_Log::write( "Cart Key: $cart_key :: view cart URL: $url" );

        return $url;
    }

    /**
     * Return the URL to the checkout page on the cloud
     *
     * @return string
     */
    public function checkout( $force_create_cart = false ) {
        $url = null;
        $force_create_cart = is_bool( $force_create_cart ) ? $force_create_cart : false; // A non-boolean may be passed in from CC_Library
        $cloud_cart = new CC_Cloud_Cart();
        $cart_key = $cloud_cart->get_cart_key( $force_create_cart );
        $subdomain_url = self::$cloud->subdomain_url();

        if ( $cart_key && $subdomain_url ) {
            $url = $subdomain_url . 'checkout/' . $cart_key;
        }

        return $url;
    }
}
