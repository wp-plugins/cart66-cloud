<?php
class CC_Cloud_Subdomain {

    public static $subdomain;

    /**
     * Attempt to load subdomain from WordPress database. If not available, load from the cloud.
     */
    public static function load_from_wp() {
        $subdomain = null;

        if ( isset( self::$subdomain ) ) {
            $subdomain = self::$subdomain;
        } else {
            $subdomain = CC_Admin_Setting::get_option( 'cart66_main_settings', 'subdomain' );
        }

        return $subdomain;
    }

    public static function load_from_cloud( $secret_key = null ) {
        self::$subdomain = null;

        $cloud = new CC_Cloud_API_V1();
        if( isset( $secret_key ) ) {
            $cloud->secret_key = $secret_key;
        }
        $url = $cloud->api . 'subdomain';
        $headers = array('Accept' => 'text/html');
        $response = wp_remote_get( $url, $cloud->basic_auth_header($headers) );

        if( $cloud->response_ok($response) ) {
            $subdomain = $response['body'];
            self::$subdomain = $subdomain;
            CC_Log::write( 'Successfully retrieved subdomain from the cloud: ' . self::$subdomain );

            // Send plugin version information to the cloud
            $messenger = new CC_Cloud_Messenger();
            $messenger->send_version_info();
            CC_Log::write( 'Sent version information to cloud after loading subdomain' );
        }

        return self::$subdomain;
    }

}
