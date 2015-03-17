<?php

class CC_Cloud_Order {

    public static function get_data( $order_id ) {
        $order_data = array();
        $cloud = new CC_Cloud_API_V1();
        $url = $cloud->api . "orders/$order_id";
        $headers = array('Accept' => 'application/json');
        $response = wp_remote_get( $url, $cloud->basic_auth_header( $headers ) );

        if ( $cloud->response_ok( $response ) ) {
            $order_data = json_decode( $response['body'], true );
            CC_Log::write('Order data: ' . print_r( $order_data, true ) );
        }

        return $order_data;
    }

}
