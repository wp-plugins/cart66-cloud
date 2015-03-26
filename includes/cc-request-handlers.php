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

        if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            // Authenticated requests
            if ( cc_auth_verify_secret_key( $_SERVER['PHP_AUTH_USER'] ) ) { 
                switch ( $action ) {
                    case 'product-update':
                        cc_auth_product_update();
                        break;
                    case 'product-create':
                        CC_Log::write( 'About to create a product' );
                        cc_auth_product_create();
                        break;
                    case 'settings-create':
                        cc_auth_settings_create();
                        break;
                }
            }
            // Authentication failed
            else {
                CC_Log::write( "Protected request failed authentication: $action" );
                status_header('401');
                exit();
            }

        }
        else { 
            // Open requests
            switch ( $action ) {
                case 'sign-in':
                    wp_redirect( $url->sign_in() );
                    exit();
                    break;
                case 'sign-out':
                    if( class_exists( 'CM_Visitor' ) ) {
                        $visitor = new CM_Visitor();
                        $visitor->sign_out();
                    }
                    wp_redirect( $url->sign_out() );
                    exit();
                    break;
                case 'view-cart':
                    wp_redirect( $url->view_cart( true ) );
                    exit();
                    break;
                case 'checkout':
                    wp_redirect( $url->checkout( true ) );
                    exit();
                    break;
                case 'order-history':
                    wp_redirect( $url->order_history() );
                    exit();
                    break;
                case 'profile':
                    wp_redirect( $url->profile() );
                    exit();
                    break;
                case 'receipts':
                    $order_id = $wp->query_vars[ 'cc-order-number' ];
                    CC_Log::write( "Getting receipt for order number: $order_id" );

                    $_GET['cc_page_title'] = 'Receipt';
                    $_GET['cc_page_name']  = 'Receipt';
                    $_GET['cc_order_id'] = $order_id;

                    add_action( 'pre_get_posts', 'CC_Page_Slurp::set_query_to_slurp');
                    add_filter( 'wp_title',      'CC_Page_Slurp::set_page_title' );
                    add_filter( 'the_title',     'CC_Page_Slurp::set_page_heading' );

                    CC_Page_Slurp::check_receipt();

                    break;
                case 'plugin-info':
                    $data = cc_plugin_info();
                    header('Content-Type: application/json');
                    echo json_encode( $data );
                    exit();
                    break;
                case 'save-secret-key':
                    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
                        $post_body = file_get_contents('php://input');

                        if ( $settings = json_decode( $post_body ) ) {
                            $main_settings = CC_Admin_Setting::get_options( 'cart66_main_settings' );
                            if ( ! isset( $main_settings['secret_key'] ) || empty( $main_settings['secret_key'] ) ) {
                                $main_settings['secret_key'] = $settings->secret_key;
                                CC_Admin_Setting::update_options( 'cart66_main_settings', $main_settings );                        
                                status_header('201');
                            }
                            else {
                                CC_Log::write( 'Not overwriting existing secret key' );
                                status_header('412');
                            }
                        }

                        exit();
                    }
                    break; 
                default:
                    CC_Log::write( "Unknown open request: $action" );
                    status_header( '404' );
                    exit();
            } // end switch $action

        } // end open requests

    } // end if $action

} // end cc_route_handler
