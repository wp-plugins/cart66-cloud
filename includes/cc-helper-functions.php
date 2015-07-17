<?php

function cc_help_secret_key() {
    return 'http://help.cart66.com/article/366-cart66-cloud-secret-key';
}

function cc_starts_with( $haystack, $needle ) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * Return a random string that contains only numbers or uppercase letters or
 * for added entropy, lowercase letters and symbols.
 *
 * The default length of the string is 14 characters.
 *
 * @param int (Optional) $length The number of characters in the string. Default: 14
 * @param string (Optional) $entropy 'lower' includes lower case letters, 'symbols' includes lower case letters and symbols
 * @return string
*/
function cc_rand_string($length = 14, $entropy='none') {
    $string = '';
    $chrs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    if ( $entropy == 'lower' ) {
        $chrs .= 'abcdefghijklmnopqrstuvwxyz';
    }
    elseif ( $entropy == 'symbols' ) {
       $chrs .= 'abcdefghijklmnopqrstuvwxyz!@#%^&*()+~:';
    }

    for ( $i=0; $i<$length; $i++ ) {
        $loc = mt_rand(0, strlen($chrs)-1);
        $string .= $chrs[$loc];
    }

    return $string;
}

/**
 * Return true if the provided slug is part of the page request
 */
function cc_match_page_request($slug) {
    global $wp;
    global $wp_query;

    $match = false;
    if ( strtolower( $wp->request ) == strtolower( $slug ) ||
       ( isset( $wp->query_vars['page_id'] ) && $wp->query_vars['page_id'] == $slug )
    ) { $match = true; }

    return $match;
}

/**
 * Return true if the provided array is an associative array.
 *
 * @param array $array The array to inspect
 * @return boolean True if array is assoc
 */
function cc_is_assoc($array) {
    return is_array( $array ) && !is_numeric( implode( '', array_keys( $array ) ) );
}

function cc_unavailable_product_data() {
    $product_data = array(
        array( 'id' => 0, 'sku' => '', 'price' => '', 'name' => 'Products Unavailable' )
    );

    return $product_data;
}

function cc_strip_slashes( $value ) {
    if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')) {
      $value = cc_strip_slashes_deep($value);
    }

    return $value;
}

function cc_strip_slashes_deep($value) {
    $value = is_array( $value ) ?  array_map( 'cc_strip_slashes_deep', $value ) : stripslashes( $value );
    return $value;
}

function cc_set_cookie( $name, $value, $expire_days = 30 ) {
    CC_Log::write( "Calling cc_set_cookie: $name :: $value" );
    $cookie_name = $name;
    $expire      = time() + 60 * 60 * 24 * $expire_days;
    $https       = false;
    $http_only   = true;
    setcookie( $cookie_name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $https, $http_only );
    CC_Log::write( "Setting cookie: $cookie_name :: $value :: " . COOKIEPATH . ' :: ' . COOKIE_DOMAIN );
    if(COOKIEPATH != SITECOOKIEPATH) {
        setcookie( $cookie_name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $https, $http_only );
        CC_Log::write( "Setting cookie with site cookie path: $cookie_name :: $value :: " . SITECOOKIEPATH . ' :: ' . COOKIE_DOMAIN );
    }
}

/**
 * Helper function for getting variable out of $_GET and $_POST or any other array
 *
 * First check if the given $key is set in the $source array
 * If the $key is set, return the sanitized value for the given value $type
 * If the $key is not set, return an empty string
 * 
 * The key type will return  lowercase alphanumeric  characters, dashes and underscores.
 * Uppercase characters will be converted to lowercase.
 *
 * Types of data include:
 *  - key: Lowercase alphanumeric characters, dashes and underscores
 *  - html_class: A-Z,a-z,0-9,_,- are allowed
 *  - text_field
 *  - email
 *  - file_name
 *  - int
 *
 * @param string $name The name of the GET parameter
 * @param string $type The type of expected data
 * @return string The sanitized string or an empty string
 */
function cc_sanitize( $key, $type, $source ) {
    $value = '';

    if ( is_array( $source ) && ! empty( $source ) ) {
        if ( isset( $source[ $key ] ) ) {
            switch( $type ) {
                case 'key':
                    $value = sanitize_key( $source[ $key ] );
                    break;
                case 'html_class':
                    $value = sanitize_html_class( $source[ $key ] );
                    break;
                case 'text_field':
                    $value = sanitize_text_field( $source[ $key ] );
                    break;
                case 'email':
                    $value = sanitize_email( $source[ $key ] );
                    break;
                case 'file_key':
                    $value = sanitize_file_key( $source[ $name ] );
                    break;
                case 'int':
                    $value = (int) $source[ $key ];
                    break;
            }
        }
        else {
            CC_Log::write( "$key was not set in: " . print_r( $source, true ) );
        }
    }

    return $value;
}

function cc_get( $name, $type='text_field' ) {
    return cc_sanitize( $name, $type, $_GET );
}

function cc_post( $name, $type='text_field' ) {
    return cc_sanitize( $name, $type, $_POST );
}

/**
 * Return the product sku associated with the given post id for the cart66 product post type
 *
 * If the sku cannot be found, an empty string is returned
 *
 * @param int $post_id
 * @return string
 */
function cc_product_meta_sku( $post_id ) {
    $sku = '';
    $info = get_post_meta( $post_id, 'cc_product_id', true ); 

    if ( is_array( $info ) && isset( $info[0] ) ) {
        $sku = $info[0];
    }

    return $sku;
}

function cc_product_meta_name( $post_id ) {
    $name = '';
    $info = get_post_meta( $post_id, 'cc_product_id', true ); 

    if ( is_array( $info ) && isset( $info[1] ) ) {
        $name = $info[1];
    }

    return $name;
}

function cc_url() {
    $url = CC_URL;
    $request_protocol = is_ssl() ? 'https' : 'http';

    if ( 'https' == $request_protocol ) {
        $location = strpos( $url, ':' );
        $default_protocol = substr( $url,  0, $location );
        $remainder = substr( $url, $location ); 
        $url = $request_protocol . $remainder;
    }

    return $url;
}

function cc_plugin_info() {
    $data = array(
        'version' => CC_VERSION_NUMBER
    );

    return $data;
}
