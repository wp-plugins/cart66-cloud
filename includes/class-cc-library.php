<?php

class CC_Library {

    protected static $_protocol;
    protected static $_app_domain;
    protected static $_api;
    protected static $_hosted_api;
    protected static $_secure;
    protected static $_subdomain_url;
    protected static $_subdomain = NULL;
    protected static $_products;
    protected static $_receipt_content;
    protected static $_expiring_products;

    public function __construct() {
        self::init();
    }

    public static function init() {
        if ( empty( self::$_api ) ) {
            self::$_protocol = 'https://';
            self::$_app_domain = 'cart66.com';
            self::$_api = self::$_protocol . 'api.' . self::$_app_domain . '/1/';
            self::$_hosted_api = self::$_protocol . 'api.' . self::$_app_domain . '/hosted/1/';
            self::$_secure = self::$_protocol . 'secure.' . self::$_app_domain . '/';
            self::$_subdomain = self::get_subdomain();
            self::$_subdomain_url = self::$_protocol . self::$_subdomain . '.' . self::$_app_domain . '/';
        }
    }

    /**
     * Return the custom subdomain for the account or null if no subdomain is set
     * 
     * @return mixed String 
     */
    public static function get_subdomain( $force = false ) {
        return $force ? CC_Cloud_Subdomain::load_from_cloud() : CC_Cloud_Subdomain::load_from_wp();
    }

    /**
     * Return the custom subdomain from the cloud or null if the subdomain could not be retrieved
     */
    public static function get_subdomain_form_cloud() {
        return CC_Cloud_Subdomain::load_from_cloud();
    }
    
    /**
     * Enqueue the JS for client side product loading
     */
    public static function enqueue_scripts() {
        self::init();
        $source = self::$_protocol . 'manage.' . self::$_app_domain . '/assets/cart66.wordpress.js';
        wp_enqueue_script('cart66-wordpress', $source, 'jquery', '1.0', true);
    }


    /**
     * Return and array of arrays for all the products
     *
     * @param boolean Depricated in place to keep consistent with legacy syntax
     * @return @array
     */
    public function get_products( $force = FALSE ) { 
        $products = new CC_Cloud_Product();
        return $products->get_products();
    }

    public function get_search_products( $query="" ) {
        $products = new CC_Cloud_Product();
        return $products->search( $query );
    }

    public function get_expiring_products() {
        $product = array();

        if ( class_exists( 'CM_Cloud_Expiring_Products' ) ) {
            $p = CM_Cloud_Expiring_Products::instance();
            $products = $p->load();
        }

        return $products;
    }

    public function get_order_data($order_id) {
        return CC_Cloud_Order::get_data( $order_id );
    }

    public function get_order_form( $product_id, $redirect_url, $display_quantity=null, $display_price=null, $display_mode=null ) {
        return CC_Cart::get_order_form( $product_id, $redirect_url, $display_quantity=null, $display_price=null, $display_mode=null );
    }

    public function create_cart( $force = true ) {
        return CC_Cart::get_cart_key( $force );
    }

    /**
     * Returns summary information for the given shopping cart
     *
     * subtotal: the sum of the totals of all the items (not including shipping, taxes, or discounts)
     * item_count: the number of items in the cart
     *
     * @return stdClass object
     */
    public function cart_summary( $cart_key ) {
        $cart = new CC_Cloud_Cart();
        return $cart->summary( $cart_key );
    }

    /**
     * The $cart_key optional parameter is only there for consistent legacy syntax
     */
    public function view_cart_url( $cart_key = '' ) {
        $url = new CC_Cloud_URL();
        return $url->view_cart();
    }

    public function checkout_url( $force_create = false ) {
        $url = new CC_Cloud_URL();
        return $url->checkout( $force_create );
    }

    public function sign_in_url( $send_return_url = false ) {
        $url = new CC_Cloud_URL();
        return $url->checkout( $send_return_url );
    }

    public function sign_out_url ( $return_url = '' ) {
        $url = new CC_Cloud_URL();
        return $url->sign_out( $return_url );
    }

    public function order_history_url() {
        $url = new CC_Cloud_URL();
        return $url->order_history();
    }

    public function profile_url() {
        $url = new CC_Cloud_URL();
        return $url->profile();
    }

    public function add_to_cart( $cart_key, $post_data ) {
        $cart = new CC_Cloud_Cart();
        return $cart->add_to_cart( $cart_key, $post_data );        
    }

    public function get_receipt_content( $order_number ) {
        return CC_Cloud_Receipt::get_receipt_content( $order_number );
    }


    /**
     * Return true if the member has an active subscription to one or more of the memberships
     * and the membership has been active for at least $days_in days.
     *
     * @param string $member_token The token for the given member
     * @param array $skus An array of product SKUs
     * @param int $days_in The number of days the membership must be active before permission is granted
     * @return boolean True if permission is granted otherwise false
     */ 
    public function has_permission( $member_token, $skus, $days_in=0 ) {
        $has_permission = false;

        if ( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $has_permission = $visitor->has_permission( $skus, $days_in );
        }

        return $has_permission;
    }

    public function get_memberships( $token, $status='active' ) {
        $data = array();

        if ( class_exists( 'CM_Cloud_Visitor' ) ) {
            $visitor = new CM_Cloud_Visitor();
            $data = $visitor->get_memberships( $token, $status );
        }

        return $data;
    }

    public function get_user_data( $token ) {
        $data = array();
        if ( class_exists( 'CM_Visitor' ) ) {
            $visitor = new CM_Visitor();
            $data = $visitor->get_user_data();
        }
        return $data;
    }

}
