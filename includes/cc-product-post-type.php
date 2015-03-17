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

    $post_type_attrs = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
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
	register_post_type( 'cc_product', $post_type_attrs );
}
