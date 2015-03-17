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

    public function create_post( $sku ) {
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
}
