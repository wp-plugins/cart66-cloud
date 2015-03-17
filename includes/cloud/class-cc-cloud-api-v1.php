<?php
class CC_Cloud_API_V1 {

    public $protocol;
    public $app_domain;
    public $api;
    public $secure;
    public $secret_key;

    public function __construct() {
        $this->protocol   = 'https://';
        $this->app_domain = 'cart66.com';
        $this->api        = $this->protocol . 'api.' . $this->app_domain . '/1/';
        $this->secure     = $this->protocol . 'secure.' . $this->app_domain . '/';
        $this->secret_key = null;
    }

    public function get_secret_key() {

        if( !isset( $this->secret_key ) ) {
            $settings = CC_Admin_Setting::get_options('cart66_main_settings');
            $this->secret_key = $settings['secret_key'];
        }

        return $this->secret_key;
    }

    public function basic_auth_header( $extra_headers = array() ) {
        $headers = false;
        $username = $this->get_secret_key();

        if ( strlen( $username ) > 5 ) {
            $password = ''; // not in use
            $headers = array(
                'sslverify' => false,
                'timeout' => 30,
                'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ) )
            );

            if ( is_array( $extra_headers ) ) {
                foreach ( $extra_headers as $key => $value ) {
                    $headers['headers'][$key] = $value;
                }
            }

            // CC_Log::write( "Sending header for :: Authorization Basic $username:$password" );
            // CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Built headers :: " . print_r($headers, true));
        }

        return $headers;
    }

    /**
     * Return the subdomain URL or null if no subdomain is set
     *
     * @return string
     */
    public function subdomain_url() {
        $url = null;
        $subdomain = CC_Cloud_Subdomain::load_from_wp();

        if ( $subdomain ) {
            $url = $this->protocol . $subdomain . '.' . $this->app_domain . '/';
        }

        return $url;
    }

    public static function response_ok( $response ) {
        $ok = true;
        if(is_wp_error( $response ) || $response['response']['code'] != 200) {
            $ok = false;
        }
        return $ok;
    }

    public function response_created( $response ) {
        $ok = true;

        if(is_wp_error( $response ) || $response['response']['code'] != 201) {
            $ok = false;
        }

        return $ok;
    }

}
