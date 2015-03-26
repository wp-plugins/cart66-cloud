<?php
/**
 * Cart66 Cloud Custom Routes
 *
 * @author Reality66
 * @since  2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CC_Routes {

	public function __construct() {

		// add query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );

		// register endpoints
		add_action( 'init', array( 'CC_Routes', 'add_routes'), 0 );
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'cc-action';
        $vars[] = 'cc-sku';
        $vars[] = 'cc-order-number';
		return $vars;
	}

	public static function add_routes() {
        add_rewrite_rule( 'sign-in',          'index.php?cc-action=sign-in',       'top' );
        add_rewrite_rule( 'sign-out',         'index.php?cc-action=sign-out',      'top' );
        add_rewrite_rule( 'view-cart',        'index.php?cc-action=view-cart',     'top' );
        add_rewrite_rule( 'checkout',         'index.php?cc-action=checkout',      'top' );
        add_rewrite_rule( 'order-history',    'index.php?cc-action=order-history', 'top' );
        add_rewrite_rule( 'profile',          'index.php?cc-action=profile',       'top' );
        add_rewrite_rule( 'receipts/([^/]*)', 'index.php?cc-action=receipts&cc-order-number=$matches[1]', 'top' );

        // Legacy URL formats
        add_rewrite_rule( 'view_cart',     'index.php?cc-action=view-cart',     'top' );
        add_rewrite_rule( 'sign_in',       'index.php?cc-action=sign-in',       'top' );
        add_rewrite_rule( 'sign_out',      'index.php?cc-action=sign-out',      'top' );
        add_rewrite_rule( 'order_history', 'index.php?cc-action=order-history', 'top' );

        // API end points
        add_rewrite_rule( 'cc-api/v1/products/([^/]*)', 'index.php?cc-action=product-update&cc-sku=$matches[1]', 'top' );
        add_rewrite_rule( 'cc-api/v1/products', 'index.php?cc-action=product-create', 'top' );
        add_rewrite_rule( 'cc-api/v1/settings', 'index.php?cc-action=settings-create', 'top' );
        add_rewrite_rule( 'cc-api/v1/plugin', 'index.php?cc-action=plugin-info', 'top' );
        add_rewrite_rule( 'cc-api/v1/init', 'index.php?cc-action=save-secret-key', 'top' );

        CC_Log::write( 'Cart66 routes have been added' );
	}

}

return new CC_Routes();
