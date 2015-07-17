<?php

/**
 * Include the appropriate templates for cart66 products
 *
 * @param string $template The template to be included
 * @return string The path to the template to be included
 */
function cc_template_include( $template ) {
    $post_type = get_post_type();

    if ( is_single() && 'cc_product' == $post_type ) {
        wp_enqueue_script( 'cc-gallery-toggle', CC_URL . 'resources/js/gallery-toggle.js', 'jquery' );
        $template = cc_get_template_part( 'single', 'product' );
    } elseif ( is_post_type_archive( 'cc_product' ) ) {
        $template = cc_get_template_part( 'archive', 'product' );
    } elseif ( is_tax( 'product-category' ) ) {
        $template = cc_get_template_part( 'taxonomy', 'product-category' );
    }

    // CC_Log::write( "Considering which template to include:\nTemplate: " . $template . "\nPost type: " . $post_type );

    return $template;
}

// add_filter( 'template_include', 'cc_template_include' );

function product_sort_order( $wp_query ) {
    if ( ! is_admin() && $wp_query->is_main_query() ) {
        $sort_method = CC_Admin_Setting::get_option( 'cart66_post_type_settings', 'sort_method' );
        $is_product_query = false;

        // Is this a query for cart66 products?
        if ( isset( $wp_query->query['post_type'] ) && 'cc_product' == $wp_query->query['post_type'] ) {
            $is_product_query = true;
            CC_Log::write( 'The post type is cc_product' );
        }
        elseif ( isset( $wp_query->query['product-category'] ) ) {
            $is_product_query = true;
            CC_Log::write( 'The product category is set' );
        }

        if ( $wp_query->is_main_query() && $is_product_query ) {
            // $wp_query->set('orderby', 'title');
            switch ( $sort_method ) {
                case 'price_desc':
                    $wp_query->set('orderby', 'meta_value_num');
                    $wp_query->set('meta_key', '_cc_product_price');
                    $wp_query->set('order', 'DESC');
                    break;
                case 'price_asc':
                    $wp_query->set('orderby', 'meta_value_num');
                    $wp_query->set('meta_key', '_cc_product_price');
                    $wp_query->set('order', 'ASC');
                    break;
                case 'name_desc':
                    $wp_query->set('orderby', 'title');
                    $wp_query->set('order', 'DESC');
                    break;
                case 'name_asc':
                    $wp_query->set('orderby', 'title');
                    $wp_query->set('order', 'ASC');
                    break;
                case 'menu_order':
                    $wp_query->set('orderby', 'menu_order');
                    $wp_query->set('order', 'ASC');
                    break;
            }

            // Set the number of products to show per page
            $max_products = CC_Admin_Setting::get_option( 'cart66_post_type_settings', 'max_products' );
            if ( ! ( is_numeric( $max_products ) && $max_products >= 2 ) ) {
                $max_products = 4;
            }
            $wp_query->set('posts_per_page', $max_products);
        }

    } // End of is_main_query

}

$use_product_post_type = CC_Admin_Setting::get_option( 'cart66_post_type_settings', 'use_product_post_type' );
if ( $use_product_post_type != 'disable' ) {
    add_filter('pre_get_posts', 'product_sort_order');
}

function cc_use_page_template( $template ) {
    $post_type = get_post_type();

    if ( is_single() && 'cc_product' == $post_type ) {
        $new_template = locate_template( array('single-product.php', 'page.php', 'single.php'), false );
        if ( ! empty( $new_template ) ) {
            $template = $new_template;
        }
    }

    return $template;
}
