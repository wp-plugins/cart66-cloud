<?php

class CC_Cloud_Receipt {

    public static $receipt_content;

    public static function get_receipt_content( $order_number ) {

        if ( empty ( self::$receipt_content ) ) {
            $cloud = new CC_Cloud_API_V1();
            $url = $cloud->subdomain_url() . "receipt/$order_number";
            $response = wp_remote_get( $url, array('sslverify' => false) );
            if ( ! is_wp_error( $response ) ) {
                if ( $response['response']['code'] == '200' ) {
                  self::$receipt_content = $response['body'];
                }
            }
            else {
                CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to locate a receipt with the order number: $order_number");
                throw new CC_Exception_Store_ReceiptNotFound('Unable to locate a receipt with the given order number.');
            }
        }

        return self::$receipt_content;
    }

}
