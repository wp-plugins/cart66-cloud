<?php
/**
 * Include opening HTML markup to render before the product content
 */
function cc_before_main_content() {
    $template = CC_PATH . 'templates/context/content-start.php';
    $template = apply_filters( 'cc_before_main_content_template', $template );   
    include_once $template;
}

add_action( 'cart66_before_main_content', 'cc_before_main_content' );

/**
 * Include opening HTML markup to render before the product content
 */
function cc_after_main_content() {
    $template = CC_PATH . 'templates/context/content-end.php';
    $template = apply_filters( 'cc_after_main_content_template', $template );   
    include_once $template;
}

add_action( 'cart66_after_main_content',  'cc_after_main_content' );

if ( ! function_exists( 'cart66_pagination' ) ) {

	/**
	 * Output the pagination.
	 *
	 * @subpackage	Loop
	 */
	function cart66_pagination() {
		cc_get_template( 'pagination.php' );
	}

    add_action( 'cart66_after_catalog_loop', 'cart66_pagination' );
}

