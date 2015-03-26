<?php

/**
 * Register custom post type and taxonomy for cart66 products
 */

function cc_register_product_post_type() {

    register_taxonomy(
		'product-category',
		'cc_product',
		array(
			'label' => __( 'Product Categories' ),
            'name' => 'Product Categories',
            'singular_name' => 'Product Category',
			'rewrite' => array( 'slug' => 'product-category' ),
            'hierarchical' => true
		)
	);

    $labels = array(
        'name' => 'Products',
        'singular_name' => 'Product',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Product',
        'edit_item' => 'Edit Product',
        'new_item' => 'New Product',
        'all_items' => 'All Products',
        'view_item' => 'View Product',
        'search_items' => 'Search Products',
        'not_found' => 'No products found',
        'not_found_in_trash' => 'No products found in trash',
        'parent_item_colon' => '',
        'menu_name' => 'Products'
    );

    $show = false;

    if ( 'no' != CC_Admin_Setting::get_option( 'cart66_post_type_settings', 'use_product_post_type' ) ) {
        $show = true;
    }

    $post_type_attrs = array(
        'labels' => $labels,
        'public' => $show,
        'publicly_queryable' => true,
        'show_ui' => $show,
        'show_in_menu' => $show,
        'show_in_nav_menus' => $show,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'products' ),
        'capability_type' => 'post',
        'taxonomies' => array( 'product-category' ),
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'menu_icon' => 'dashicons-tag',
        'supports' => array ( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions' )
    );

	/* Register the post type. */
    CC_Log::write( 'Registering Cart66 product post type: cc_product' );
    register_post_type( 'cc_product', $post_type_attrs );
}
