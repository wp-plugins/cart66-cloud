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
		add_action( 'init', array( $this, 'add_routes'), 0 );
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'cc-action';
        $vars[] = 'cc-sku';
		return $vars;
	}

	public function add_routes() {
        add_rewrite_rule( 'sign-in',       'index.php?cc-action=sign-in',       'top' );
        add_rewrite_rule( 'sign-out',      'index.php?cc-action=sign-out',      'top' );
        add_rewrite_rule( 'view-cart',     'index.php?cc-action=view-cart',     'top' );
        add_rewrite_rule( 'checkout',      'index.php?cc-action=checkout',      'top' );
        add_rewrite_rule( 'order-history', 'index.php?cc-action=order-history', 'top' );
        add_rewrite_rule( 'profile',       'index.php?cc-action=profile',       'top' );

        add_rewrite_rule( 'cc-api/v1/products/([^/]*)', 'index.php?cc-action=product-update&cc-sku=$matches[1]', 'top' );
        add_rewrite_rule( 'cc-api/v1/products', 'index.php?cc-action=product-create', 'top' );
	}

}

return new CC_Routes();
