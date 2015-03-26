<?php
/**
 * Implementation of authenticated API calls from cc-request-handler.php
 */

function cc_auth_verify_secret_key( $key ) {
    $secret_key = CC_Admin_Setting::get_option( 'cart66_main_settings', 'secret_key' );
    return $secret_key == $key;
}

function cc_auth_product_update() {
    if ( 'PUT' == $_SERVER['REQUEST_METHOD'] ) {
        global $wp;
        $sku = $wp->query_vars[ 'cc-sku' ];
        $product = new CC_Product();
        $product->update_info( $sku );
        exit();
    }
}

function cc_auth_product_create() {
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $post_body = file_get_contents('php://input');
        if ( $product_data = json_decode( $post_body ) ) {
            $product = new CC_Product();
            
            // Check for demo product
            if ( 'cc-cellerciser' == $product_data->sku ) {
                $content = $product->cellerciser_content();
                $excerpt = $product->cellerciser_excerpt();
                $post_id = $product->create_post( $product_data->sku, $content, $excerpt );
                $product->attach_cellerciser_images( $post_id );
            }
            else {
                // Create a normal product pressed from the cloud
                $product->create_post( $product_data->sku );
            }

        }
        exit();
    }
}

function cc_auth_settings_create() {
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $post_body = file_get_contents('php://input');

        if ( $settings = json_decode( $post_body ) ) {
            $main_settings = CC_Admin_Setting::get_options( 'cart66_main_settings' );
            $main_settings['subdomain'] = $settings->subdomain;
            CC_Admin_Setting::update_options( 'cart66_main_settings', $main_settings );                        
            status_header('201');
        }

        exit();
    }
}
