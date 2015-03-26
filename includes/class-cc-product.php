<?php

class CC_Product extends CC_Model {

    protected $post;
    protected $json_key;
    protected $prefix;

    /**
     * Optionally construct object with Cart66 Cloud product id
     */
    public function __construct( $id='' ) {
        $data = array( 'id' => $id, 'sku' => '' );
        parent::__construct( $data );
        $this->json_key = '_cc_product_json';
        $this->prefix   = '_cc_product_';
    }

    /**
     * Set the WordPress post and the Cart66 Cloud product id.
     *
     * If the post does not have a Cart66 Cloud product id, the current
     * value of the product id is not changed.
     */
    public function set_post( $post ) {
        $this->post = $post;
        $product_id = getpost_meta( $post->ID, 'cc_product_id', true );
        if ( !empty( $product_id ) ) {
            $this->id = $product_id;
        }
    }

    /**
     * Return the WordPress post associated with the Cart66 Cloud product id
     *
     * If no post is found or if no product id is available return false;
     *
     * @return mixed stdClass WordPress Post or false
     */
    public function get_post() {
        $post = false;

        if( isset( $this->post ) ) {
            $post = $this->post;
        } elseif( strlen( $this->id ) > 1 ) {
            $posts = wp_getposts(array('meta_key' => 'cc_product_id', 'meta_value' => $this->id));

            if ( count( $posts ) ) {
                $this->post = $posts[0];
                $post = $this->post;
            }
        }

        return $post;
    }

     /**
      *  /products/<id>/add_to_cart_form
      *  Returns HTML
      */
    public function get_order_form( $display_quantity='true', $display_price='true', $display_mode=null ) {
        $html = 'Product not available';

        // Figure out about the product id vs sku
        $product_id = $this->id;
        if ( strlen( $this->sku ) > 0 ) {
            $product_id = $this->sku;
        }

        CC_Log::write( "Get order form for product id: $product_id" );

        if ( strlen( $product_id ) > 0 ) {
            try {
                $redirect_url = CC_Cart::get_redirect_url();
                $html = CC_Cart::get_order_form( $product_id, $redirect_url, $display_quantity, $display_price, $display_mode );
            }
            catch(CC_Exception_API $e) {
                $html = "Unable to retrieve product order form";
            }
        } 
        else {
            throw new CC_Exception_Product('Unable to get add to cart form because the product id is not available');
        }

        return $html;
    }

    public function update_info( $sku ) {
        $args = array(
            'post_type' => 'cc_product',
            'meta_key' => '_cc_product_sku',
            'meta_value' => $sku,
            'posts_per_page' => 1
        );
        $posts = get_posts( $args );

        if ( count( $posts ) ) {
            $post = array_shift( $posts );
            if ( is_object( $post ) && $post->ID > 0 ) {
                $results = CC_Cloud_Product::search( $sku );
                // CC_Log::write( 'Updating product info for post id: ' . $post->ID . " :: " . print_r( $results, true ) );
                if( is_array( $results ) && count( $results ) ) {
                    $product_info = array_shift( $results ); 
                    update_post_meta( $post->ID, $this->json_key, $product_info );
                    foreach( $product_info as $key => $value ) {
                        update_post_meta( $post->ID, $this->prefix . $key, $value );
                    }
                }
            }
        }

    }

    public function create_post( $sku, $content = '', $excerpt = '' ) {
        $post_id = null;
        $search_results = CC_Cloud_Product::search( $sku );
        if ( is_array( $search_results ) && count( $search_results ) ) {
            $product_info = array_shift( $search_results );
            if ( is_array( $product_info ) && count( $product_info ) ) {
                $slug  = sanitize_key( str_replace( ' ', '-', strtolower( $product_info[ 'name' ] ) ) );
                $title = cc_sanitize( 'name', 'text_field', $product_info );

                if ( null == $this->page_exists( $title ) ) {
                    $post_data = array(
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_author' => 1,
                        'post_name' => $slug,
                        'post_title' => $title,
                        'post_status' => 'publish',
                        'post_type' => 'cc_product'
                    );

                    if ( ! empty( $content ) ) {
                        $post_data['post_content'] = $content;
                    }

                    if ( ! empty( $excerpt ) ) {
                        $post_data['post_excerpt'] = $excerpt;
                    }

                    CC_Log::write( 'About to create cart66 product post with this post data: ' . print_r( $post_data, true ) );
                    $post_id = wp_insert_post( $post_data );

                    if ( $post_id > 0 ) {
                        CC_Log::write( 'Created cart66 product post with id: ' . $post_id . "\nNow adding meta data: " . print_r( $product_info, true ) );
                        update_post_meta( $post_id, $this->json_key, $product_info );
                        foreach( $product_info as $key => $value ) {
                            update_post_meta( $post_id, $this->prefix . $key, $value );
                        }
                    }
                    else {
                        CC_Log::write( 'Unable to create cart66 product post: ' . $post_id );
                    }
                }
                else {
                    CC_Log::write( 'Not creating new product post because a post already exists with title: ' . $title );
                }
            }

        }
        else {
            CC_Log::write( 'Unable to retrieve product information for SKU: ' . $sku );
        }

        return $post_id;
    }

    /**
     * Look for a cart66 product post with the given title and return the post id.
     * If the page does not exist return null.
     *
     * @param string $title
     * @return int The post id or null if no post was found
     */
    public function page_exists( $title ) {
        $id = null;
        $post = get_page_by_title( $title, 'OBJECT', 'cc_product' );
        if ( $post ) {
            CC_Log::write( 'Found page id: ' . print_r( $post->ID, true ) );
            $id = $post->ID;
        }
        return $id;
    }

    /**
     * Attach a remote image to a the post with the given post id
     *
     * If successful, return the id of the attachment otherwise return WP_Error
     *
     * @param int $post_id
     * @param string $url URL to remote image
     * @return int or WP_Error
     */
    public function attach_image_to_post( $post_id, $url ) {
        require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/media.php');

        $tmp = download_url( $url );
        $file_info = array( 'name' => basename( $url ), 'tmp_name' => $tmp );

        // Abort if the file download failed
        if ( is_wp_error( $tmp ) ) {
            @unlink( $file_info[ 'tmp_name' ] );
            return $tmp;
        }

        cc_add_gallery_image_sizes(); // Add the custom image sizes
        $attachment_id = media_handle_sideload( $file_info, $post_id );
        CC_Log::write( 'Media handle sideload attachment id: ' . print_r( $attachment_id, true ) );

        // Abort if the sideload failed
        if ( is_wp_error( $attachment_id ) ) {
            @unlink( $file_info['tmp_name'] );
        }
        else {
            $metadata = wp_get_attachment_metadata( $attachment_id );
            // CC_Log::write( 'Metadata: ' . print_r( $metadata, true ) );
            $upload_dir = wp_upload_dir();
            $file = $upload_dir['path'] . '/' . $metadata['file'];
        }

        return $attachment_id;
    }

    public function attach_cellerciser_images( $post_id ) {
        $urls = array(
            '_product_image_1' => 'http://cart66-com.s3.amazonaws.com/images/fast-track/half-fold.png',
            '_product_image_2' => 'http://cart66-com.s3.amazonaws.com/images/fast-track/half-fold-legs.jpg',
            '_product_image_3' => 'http://cart66-com.s3.amazonaws.com/images/fast-track/half-fold-springs-bottom.jpg',
            '_product_image_4' => 'http://cart66-com.s3.amazonaws.com/images/fast-track/half-fold-springs.jpg',
            '_product_image_5' => 'http://cart66-com.s3.amazonaws.com/images/fast-track/half-fold-closed.jpg'
        );

        foreach( $urls as $meta_key => $url ) {
            $attachment_id = $this->attach_image_to_post( $post_id, $url );
            if ( is_numeric( $attachment_id ) ) {
                update_post_meta( $post_id, $meta_key, $attachment_id );
            }
        }

    }

    public function cellerciser_content() {
        $content = '<h2>The Perfect Exercise!</h2>';
        $content .= '<p>People of all ages and sizes are enjoying the results of Cellercise – the zero-impact workout that <strong>burns over 700 calories per hour</strong>, melts fat, builds muscle and provides a long list of health-building benefits – including improved digestion, sleep, immunity, coordination, sleep and sex!</p>';
        $content .= '<p>Typical exercise is limited to specific muscles or muscle groups. It works by applying weight on specific muscles or muscle groups, generally by lifting weight away from gravity. Cellercise works by increasing the weight of gravity on <strong>every cell of your body over 100 times per minute</strong>. That means every muscle, bone, ligament, tendon, connective tissue, even the collagen and skin. The whole body begins to grow stronger, leaner, and more toned from the inside out.</p>';
        return $content;
    }

    public function cellerciser_excerpt() {
        $excerpt = 'The perfect exercise to gain strength and lose weight.';
        return $excerpt;
    }
}
