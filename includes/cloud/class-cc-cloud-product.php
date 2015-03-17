<?php

class CC_Cloud_Product {

    /**
     * @var Cart66 Cloud API
     */
    protected static $cloud;

    /**
     * An array of product data from the cloud
     *
     * @var array
     */
    protected static $products;

    public function __construct() {
        self::init();
    }

    public static function init() {
        if ( !isset( self::$cloud ) || !is_object( self::$cloud ) ) {
            self::$cloud = new CC_Cloud_API_V1();
        }
    }

    /**
     * Return an array of arrays of product data
     *
     *    [0] => Array (
     *        [id] => 522f543ddab99857e9000047
     *        [name] => Boomerang Hiking Boot
     *        [sku] => boot
     *        [price] => 65.0
     *        [on_sale] =>
     *        [sale_price] =>
     *        [currency] => $
     *        [expires_after] =>
     *        [formatted_price] => $65.00
     *        [formatted_sale_price] => $
     *        [digital] =>
     *        [type] => product
     *        [status] => available
     *    )
     *
     * @return array
     */
    public function get_products() {
        if ( is_array( self::$products ) ) {
            // CC_Log::write('Called get_products() :: Reusing static product data');
        } else {
            $this->load_all();
        }

        return self::$products;
    }

    /**
     * Attempt to load all cloud products into self::$products
     *
     * @return void
     */
    public function load_all() {
        $url = self::$cloud->api . 'products';
        $headers = array( 'Accept' => 'application/json' );
        $response = wp_remote_get( $url, self::$cloud->basic_auth_header( $headers ) );

        if ( !self::$cloud->response_ok( $response ) ) {
            // CC_Log::write("CC_Library::get_products failed: $url :: " . print_r( $response, true ) );
            throw new CC_Exception_API( "Failed to retrieve products from Cart66 Cloud" );
        }
        else {
            self::$products = json_decode( $response['body'], true );
            CC_Log::write('Called get_products() :: Loaded product data from the cloud');
        }
    }

    /**
     * Return an array of arrays of product data
     *
     * The returned array looks like this:
     *
     *  [0] => Array
     *     (
     *       [id] => 521e468adab9981ae6000709
     *       [name] => Lifetime Member
     *       [sku] => lifetime
     *       [price] => 49.0
     *       [on_sale] => 
     *       [sale_price] => 
     *       [currency] => $
     *       [expires_after] => 0
     *       [formatted_price] => $49.00
     *       [formatted_sale_price] => $
     *       [digital] => 
     *       [type] => membership
     *       [status] => available
     *     )
     * 
     *
     * @param string $query
     * @return array
     */
    public static function search( $query ) {
        self::init();
        $products = array();
        $url = self::$cloud->api . 'products/search/?search=' . $query;
        $headers = self::$cloud->basic_auth_header( array( 'Accept' => 'application/json' ) );

        if ( $headers ) {
            $response = wp_remote_get( $url, $headers );

            if( self::$cloud->response_ok( $response ) ) {
                $products = json_decode( $response['body'], true );
            } else {
                CC_Log::write( "Product search failed: $url :: " . print_r( $response, true ) );
                throw new CC_Exception_API( "Failed to retrieve products from Cart66 Cloud" );
            }
        }

        return $products;
    }

    /**
     * Return an array with a single, empty, product
     *
     * @return array
     */
    public function unavailable() {
        $unavailable = __( 'Products unavailable', 'cart66' );
        $product_data = array(
            array('id' => 0, 'sku' => '', 'price' => '', 'name' => $unavailable )
        );

        return $product_data;
    }

}
