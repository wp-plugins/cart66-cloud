<?php

add_theme_support( 'post-thumbnails', array( 'cc_product' ) );

/**
 * Image gallery filters
 */
function cc_custom_image_size_list( $sizes ) {
    $custom_sizes = array(
        'cc-gallery-full' => 'Cart66 Gallery Image',
        'cc-gallery-thumb' => 'Cart66 Gallery Thumbnail'
    );

    return array_merge( $sizes, $custom_sizes );
}

add_filter( 'image_size_names_choose', 'cc_custom_image_size_list' );


/**
 * Image gallery actions
 */

function cc_add_gallery_image_sizes() {
    add_image_size( 'cc-gallery-thumb', 30 ); // 30 pixels wide, unlimited height
    add_image_size( 'cc-gallery-full', 250 ); // 250 pixels wide, unlimited height
}

add_action( 'admin_init', 'cc_add_gallery_image_sizes' );


function add_image_metabox() {
	$post_types = apply_filters( 'cc_post_types_with_images', array( 'cc_product' ) );
	foreach( $post_types as $post ) {
		add_meta_box( 'cc-gallery-images', __('Add Photos'), 'cc_gallery_images', $post, 'normal', 'core' );
    }
}

add_action( 'admin_init', 'add_image_metabox' );


function cc_save_image_metabox( $post_id ) { 
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;
    
   	$images = cc_list_product_image_slots();
    foreach( $images as $key => $value ){
	    if ( isset( $_POST[ $key ] ) ) {
			check_admin_referer('cc-gallery-images-save_' . $_POST['post_ID'], 'cc-gallery-images-nonce' );
			update_post_meta($post_id, $value, esc_html( $_POST[ $key ] ) ); 
		}
	}
}

add_action('save_post', 'cc_save_image_metabox'); 


/**
 * Image gallery functions
 */

function cc_gallery_images( $post ) {
	$list_images = cc_list_product_image_slots();

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script( 'quicktags' );
	wp_enqueue_script( 'jquery-ui-resizable' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-position' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'wpdialogs' );
	wp_enqueue_script( 'wplink' );
	wp_enqueue_script( 'wpdialogs-popup' );
	wp_enqueue_script( 'wp-fullscreen' );
	wp_enqueue_script( 'editor' );
	wp_enqueue_script( 'word-count' );
	wp_enqueue_script( 'img-mb', CC_URL . 'resources/js/get-images.js', array( 'jquery','media-upload','thickbox','set-post-thumbnail' ) );
	wp_enqueue_style( 'thickbox' );

	wp_nonce_field( 'cc-gallery-images-save_' . $post->ID, 'cc-gallery-images-nonce' );

	echo '<div id="droppable">';
	$z =1;
	foreach( $list_images as $k => $i ){
		$meta = get_post_meta( $post->ID, $i, true );
		$img = (isset($meta)) ? '<img src="'. wp_get_attachment_thumb_url( $meta ) . '" width="100" height="100" alt="" draggable="false">' : '';
		echo '<div class="image-entry" draggable="true">';
		echo '<input type="hidden" name="' . $k .'" id="' . $k . '" class="id_img" data-num="' . $z . '" value="' . $meta . '">';
		echo '<div class="img-preview" data-num="'.$z.'">'.$img.'</div>';
		echo '<a href="javascript:void(0);" class="get-image button-secondary" data-num="'.$z.'">'._x('Add New','file').'</a><a href="javascript:void(0);" class="del-image button-secondary" data-num="'.$z.'">'.__('Delete').'</a>';
		echo '</div>';
		$z++;
	}
	echo '</div>';

    $page = CC_View::get( CC_PATH . 'views/admin/html-image-meta-box.php' );
    echo $page;
}

function cc_list_product_image_slots( $cpt = false ){
    $image_slots = array(
        'image1' => '_product_image_1',
        'image2' => '_product_image_2',
        'image3' => '_product_image_3',
        'image4' => '_product_image_4',
        'image5' => '_product_image_5',
	);
	$images = apply_filters('cc_list_product_images', $image_slots, $cpt );
	return $images;
}

function cc_get_product_image_ids( $post_id = false, $fall_back = false ){
	global $post;
	$post_id = ($post_id) ? $post_id : $post->ID;

	$list_images = cc_list_product_image_slots( get_post_type( $post_id ) );
	$product_images = array();

	foreach( $list_images as $key => $img ) {
		if ( $post_meta = get_post_meta( $post_id, $img, true ) ) {
			$product_images[ $key ] = $post_meta;
        }
	}

    // If there are no product images, see about loading up the featured image
    if ( $fall_back && 0 == count( $product_images ) ) {
		if ( $post_meta = get_post_meta( $post_id, '_thumbnail_id', true ) ) {
			$product_images[ 'image1' ] = $post_meta;
        }
    }

	return $product_images;
}

function cc_get_product_image_sources( $size = 'cc-gallery-full', $id = false, $fall_back = false, $full_info = false ) {
	$sources = array();
    $images = cc_get_product_image_ids( $id, $fall_back );

    foreach($images as $key => $value) {

        if ( $full_info ) {
            $sources[ $key ] = wp_get_attachment_image_src( $value, $size );
        } else {
            $info = wp_get_attachment_image_src( $value, $size );
            $sources[ $key ] = $info[0];
        }

    }

    // CC_Log::write( 'Product image source data: ' . print_r( $sources, true ) );

	return $sources;
}

/**
 * Rerturn an array with the sources for
 * - The full size gallery image
 * - The full size image for the lightbox
 *
 * If no product images are set, fall back to use featured image if 
 * $fall_back_to_featured_image is TRUE, default is FALSE
 *
 * @param int $post_id The id of the product post to which the images are attached
 * @param boolean $fall_back_to_featured_image (optional, default: FALSE)
 * @return array
 */
function cc_get_product_gallery_image_sources( $post_id, $fall_back_to_featured_image = false ) {
	$sources = array();
    $images = cc_get_product_image_ids( $post_id, $fall_back_to_featured_image );

    foreach( $images as $key => $attachment_id ) {
        $gallery_info = wp_get_attachment_image_src( $attachment_id, 'cc-gallery-full' );
        $full_info = wp_get_attachment_image_src( $attachment_id, 'full' );
        $sources[ $key ] = array( $gallery_info[0], $full_info[0] );
    }

    return $sources;
}

function cc_get_product_thumb_sources( $post_id ) {
    $thumbs = cc_get_product_image_sources( 'cc-gallery-thumb', $post_id );
    return $thumbs;
}

function cc_get_multi_product_image_sources( $small = 'cc-gallery-thumb', $large = 'cc-gallery-full', $id = false ) {
    $sources = array();
    $images = cc_get_product_image_ids( $id );

    foreach( $images as $k => $i ) {
		$sources[ $k ] = array( wp_get_attachment_image_src( $i, $small ), wp_get_attachment_image_src( $i, $large ) );
    }

	return $sources;
}
