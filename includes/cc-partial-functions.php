<?php

/**
 * Return image source URL to the first product image assigned to the product with the given post id
 *
 * @return string
 */
function cc_primary_image_for_product( $post_id, $size = 'cc-gallery-full' ) {
    $primary_src = '';
    $images = cc_get_product_image_sources( $size, $post_id );

    if ( is_array( $images ) ) {
        $primary_src = array_shift( $images );
    }

    return $primary_src;
}

function cc_filter_product_single( $content ) {
    global $post;
    $post_type = get_post_type();

    if ( is_single() && 'cc_product' == $post_type ) {
        wp_enqueue_script( 'cc-gallery-toggle', CC_URL . 'resources/js/gallery-toggle.js', 'jquery' );
        $thumbs = cc_get_product_thumb_sources( $post->ID );
        $images = cc_get_product_gallery_image_sources( $post->ID );
        $data = array( 'images' => $images, 'thumbs' => $thumbs );
        $single_product_view = CC_View::get( CC_PATH . 'templates/partials/single-product.php', $data );
        $content = $single_product_view . $content;
    } 

    return $content;
}
