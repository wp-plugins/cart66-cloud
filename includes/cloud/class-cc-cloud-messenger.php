<?php

class CC_Cloud_Messenger {

    public static $cloud;

    public function __construct() {
        if ( !isset( self::$cloud ) || !is_object( self::$cloud ) ) {
            self::$cloud = new CC_Cloud_API_V1();
        }
    }

    public function send_version_info() {
        $info = array( 'plugin_version' => CC_VERSION_NUMBER );
        $url = self::$cloud->api . 'stores';
        $options = self::$cloud->basic_auth_header();
        $options['method'] = 'PUT';
        $options['body'] = json_encode( $info );
        CC_Log::write( 'Send version info options: ' . print_r( $options, true ) );
        $response = wp_remote_request( $url, $options );
        CC_Log::write( 'Send version info response: ' . print_r( $response, true ) );
        return $response;
    }

}
