<?php

add_action( 'load-post.php',     'cc_product_meta_box_setup' );
add_action( 'load-post-new.php', 'cc_product_meta_box_setup' );
add_action( 'wp_ajax_cc_ajax_product_search', 'cc_ajax_product_search' );

function cc_ajax_product_search( ) {
    $products = CC_Cloud_Product::search( $_REQUEST['search'] );
    $options = array(); 

    /*
    Product info from search results: Array
    (
        [id] => 54d3e70dd2a57d1adc002eb1
        [name] => Ooma HD2 Handset
        [sku] => hd2
        [price] => 60.0
        [on_sale] => 
        [sale_price] => 
        [currency] => $
        [expires_after] => 
        [formatted_price] => $60.00
        [formatted_sale_price] => $
        [digital] => 
        [type] => product
        [status] => available
    )
     */
    foreach ( $products as $p ) {
        // CC_Log::write( 'Product info from search results: ' . print_r( $p, true ) );
        $options[] = array( 
            'id' => json_encode( $p ),
            'text' => $p['name'] 
        );
    }

    echo json_encode( $options );
    die();
}

function cc_product_meta_box_setup() {
    add_action( 'add_meta_boxes', 'cc_add_product_meta_box' );
    add_action( 'save_post', 'cc_save_product_meta_box', 10, 2 );
    
    $url = cc_url();
    wp_enqueue_style( 'select2', $url .'resources/js/select2/select2.css' );
    wp_enqueue_script( 'select2', $url . 'resources/js/select2/select2.min.js' );

}

function cc_add_product_meta_box() {
    add_meta_box(
        'cart66-product-box',             // unique id assigned to the meta box
        __( 'Cart66 Product', 'cart66' ), // title for metabox
        'cc_product_meta_box_render',     // callback to display the output for the meta box
        'cc_product',                        // the name of the post type on which to display the meta box
        'side',                           // where on the page to display the meta box (normal, side, advanced)
        'default'                         // priority (default, core, high, low)
    );
}

/**
 * Render the output for the cart66 product meta box on the product post type
 *
 * This function should echo the content
 */
function cc_product_meta_box_render( $post, $box ) {

    $value = get_post_meta( $post->ID, '_cc_product_name', true );

    if ( empty( $value ) ) {
        $value = 'Select Product';
    } 

    $data = array( 
        'post' => $post, 
        'box' => $box,
        'value' => $value
    );

    $template = CC_PATH . 'views/admin/html-product-meta-box.php';
    $view = CC_View::get( $template, $data );
    echo $view;
}

/**
 * Save the product id associated with this product post
 */
function cc_save_product_meta_box( $post_id, $post ) {

    /* Verify the nonce before proceeding. */
    if ( !isset( $_POST['cc_product_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['cc_product_meta_box_nonce'], 'cc_product_meta_box' ) ) {
        return $post_id;
    }

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    /* Store the meta key value in the wp_postmeta table */
    cc_store_meta_box_values( $post_id );
}

/**
 * Function to add, update, or delete post meta
 *
 * Note that the given $meta_key is both the HTML form field name and the 
 * meta_key in the wp_postmeta table
 * 
 * If a new product is selected, the submitted value will be in the format:
 * cloud_id~~product_name
 *
 * If the stored value is being displayed, the submitted value is empty
 *
 * @param int $post_id
 * @param string $meta_key
 */
function cc_store_meta_box_values( $post_id ) {
    $json_key = '_cc_product_json';
    $prefix   = '_cc_product_';

    // Get the posted data and sanitize it for use as an HTML class.
    $product_info = ( isset( $_POST[ '_cc_product_json' ] ) ? sanitize_text_field( $_POST[ '_cc_product_json' ] ) : '' );
    $product_info = stripslashes( $product_info );
    $product_info = json_decode( $product_info, true );

    if ( is_array( $product_info ) ) {

        // Get the meta value of the custom field key.
        $old_value = get_post_meta( $post_id, $json_key, true );

        if ( '' == $old_value ) {
            // If a new meta value was added and there was no previous value, add it.
            add_post_meta( $post_id, $json_key, $product_info, true );
            foreach( $product_info as $key => $value ) {
                add_post_meta( $post_id, $prefix . $key, $value, true );
            }
        } elseif ( $product_info != $old_value ) {
            // If the new meta value does not match the old value, update it.
            update_post_meta( $post_id, $json_key, $product_info );
            foreach( $product_info as $key => $value ) {
                update_post_meta( $post_id, $prefix . $key, $value );
            }
        } elseif ( '' == $product_info && $old_value ) {
            // TODO: $product_info will never be empty here because in order to get here it has to be an array
            // If there is no new meta value but an old value exists, delete it.
            delete_post_meta( $post_id, $json_key );
        }
        else {
            CC_Log::write( "Totally skipping saving meta data for a reason currently unknown to me." );
        }

    }

}
