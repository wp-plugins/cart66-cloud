<?php
/**
 * The CC_Flash_Data class manages a static array used to populate content in views.
 *
 * For example, if saving an object fails, this class can hold the error messages that
 * you would like to have rendered in your view. Likewise, any other data you want
 * in your view can be built up and stored in this class.
 *
 * NOTE: The data in this class does not persist between requests. Therefore, if a redirect
 * takes place all of this data will be reset.
 */
class CC_Flash_Data {

    protected static $_data = array();

    private function __construct() {
        // Do not instantiate this class
    }

    private static function init( $space='default' ) {
        if( !(isset( self::$_data ) && is_array( self::$_data ) ) ) {
            self::$_data = array( 'default' => array() );
        }

        if(!isset( self::$_data[$space] ) ) {
            self::$_data[$space] = array();
        }
    }

    public static function clear() {
        CC_Log::write( '[' . basename( __FILE__) . ' - line ' . __LINE__ . '] Clearing all flash data' );
        self::$_data = array();
    }

    public static function set( $key, $value, $space='default' ) {
        self::init( $space );
        self::$_data[ $space ][ $key ] = $value;
    }

    /**
     * Only set the key to the given value if the key does not exist.
     *
     * This function is primarily used to set default values before rendering a view
     */
    public static function set_if_empty( $key, $value, $space='default' ) {
        self::init( $space );
        if(!isset(self::$_data[ $space ][ $key ])) {
            self::$_data[ $space ][ $key ] = $value;
        }
    }

    /**
     * Return the value associated with the given key.
     *
     * If the key does not exist return an empty string.
     *
     * @return mixed
     */
    public static function get( $key, $space='default' ) {
        $value = '';

        if ( isset( self::$_data[ $space ]) && is_array( self::$_data[ $space ] ) && isset( self::$_data[ $space ][ $key ] ) ) {
            $value = self::$_data[ $space ][ $key ];
        }

        return $value;
    }

    /**
     * Return the entire array of $_data
     *
     * If no data has been set, return an empty array
     *
     * @return array
     */
    public static function get_all( $space='default' ) {
        CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting all flash data for space: $space :: " . print_r(self::$_data, true));
        $data = array();
        self::init();
        if ( isset( self::$_data[ $space ] ) ) {
            if ( is_array( self::$_data[$space] ) ) {
                $data = self::$_data[$space];
            }
        }

        return $data;
    }

    /**
     * Remove a key from the given space
     *
     * @param string $key
     * @param string $space
     * @return void
     */
    public static function remove( $key, $space='default' ) {
        self::init();
        if ( isset( self::$_data[ $space ][ $key ] ) ) {
            unset( self::$_data[ $space ][ $key ] );
        }
    }

    /**
     * Return true if there is content stored in the private $_data array
     *
     * If no parameters are provided, return true if there is any data available at all
     * If one parameter is provided, return true if the given key exists.
     * If two parameters are provided the 2nd param is the space
     *
     * @return boolean
     */
    public static function exists() {
        $exists = false;

        // Look for space name
        $num_args = func_num_args();
        $space = 'default';
        if ( $num_args == 2 ) {
            $space = func_get_arg(1);
        }

        if ( isset( self::$_data[ $space ] ) && is_array( self::$_data[ $space ] ) ) {
            if ( $num_args == 0 ) {
                if ( count( self::$_data[ $space ] ) > 0 ) {
                    $exists = true;
                }
            } elseif ( $num_args == 1 ) {
                $key = func_get_arg(0);
                if ( isset( self::$_data[ $space ][ $key ] ) ) {
                    $exists = true;
                }
            }
        }

        return $exists;
    }

}
