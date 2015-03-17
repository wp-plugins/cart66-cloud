<?php

/** 
 * Handle admin tasks for cart66
 */
function cc_task_dispatcher() {
    $task = cc_get( 'cc-task', 'key' );
    // CC_Log::write( "Task dispatcher found: $task" );

    if ( $task ) {
        switch ( $task ) {
            case 'dismiss_notification_theme_support':
                CC_Admin_Notifications::dismiss( 'cart66_theme_support' );
                break;
            case 'dismiss_notification_permalinks':
                CC_Admin_Notifications::dismiss( 'cart66_permalinks' );
                break;
            case 'dismiss_notification_migration':
                CC_Admin_Notifications::dismiss( 'cart66_migration' );
                break;
            case 'download_log':
                CC_Log::download();
                break;
            case 'reset_log':
                CC_Log::reset();
                break;
            case 'test_remote_calls':
                $tests = new CC_Cloud_Remote_Check();
                $tests->run();
                break;
            case 'create_slurp_page':
                CC_Page_Slurp::create_slurp_page();
                break;
            case 'migrate_settings':
                $migration = new CC_Migration();
                $migration->run();
                break;
        }
    }

}

/**
 * Handle public actions for cart66
 */
function cc_route_handler() {
    global $wp;

    // If the cc-action is not available forget about doing anything else here
    if ( ! isset( $wp->query_vars[ 'cc-action' ] ) ) {
        return;
    }

    $action = $wp->query_vars[ 'cc-action' ];
    CC_Log::write( "Route handler found action: $action" );


    if ( $action ) {
        unset( $wp->query_vars[ 'cc-action' ] );
        $url = new CC_Cloud_URL();
        switch ( $action ) {
            case 'sign-in':
                wp_redirect( $url->sign_in() );
                exit();
            case 'sign-out':
                if( class_exists( 'CM_Visitor' ) ) {
                    $visitor = new CM_Visitor();
                    $visitor->sign_out();
                }
                wp_redirect( $url->sign_out() );
                exit();
            case 'view-cart':
                wp_redirect( $url->view_cart() );
                exit();
            case 'checkout':
                wp_redirect( $url->checkout() );
                exit();
            case 'order-history':
                wp_redirect( $url->order_history() );
                exit();
            case 'profile':
                wp_redirect( $url->profile() );
                exit();
            case 'product-update':
                if ( 'PUT' == $_SERVER['REQUEST_METHOD'] ) {
                    $sku = $wp->query_vars[ 'cc-sku' ];
                    $product = new CC_Product();
                    $product->update_info( $sku );
                    exit();
                }
            case 'product-create':
                if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
                    $post_body = file_get_contents('php://input');
                    if ( $product_data = json_decode( $post_body ) ) {
                        $product = new CC_Product();
                        $product->create_post( $product_data->sku );
                    }
                    exit();
                }

        }
    }

}
