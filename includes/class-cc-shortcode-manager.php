<?php

class CC_Shortcode_Manager {

    public static function init() {
        self::register_shortcodes();
    }

    public static function register_shortcodes() {
        add_shortcode( 'cc_product',              array( 'CC_Shortcode_Manager', 'cc_product' ) );
        add_shortcode( 'cc_product_link',         array( 'CC_Shortcode_Manager', 'cc_product_link' ) );
        add_shortcode( 'cc_product_price',        array( 'CC_Shortcode_Manager', 'cc_product_price' ) );
        add_shortcode( 'cc_cart_item_count',      array( 'CC_Shortcode_Manager', 'cc_cart_item_count' ) );
        add_shortcode( 'cc_cart_subtotal',        array( 'CC_Shortcode_Manager', 'cc_cart_subtotal' ) );
        add_shortcode( 'cc_product_catalog',      array( 'CC_Shortcode_Manager', 'cc_product_catalog' ) );
    }

    public static function cc_product( $args, $content ) {
        $product_loader = CC_Admin_Setting::get_option( 'cart66_main_settings', 'product_loader' );
        $subdomain      = CC_Cloud_Subdomain::load_from_wp();
        $id             = cc_rand_string(12, 'lower');
        $product_form   = '';
        $client_loading = 'true';

        $product_id       = isset($args['id']) ? $args['id'] : false;
        $product_sku      = isset($args['sku']) ? $args['sku'] : false;
        $display_quantity = isset($args['quantity']) ? $args['quantity'] : 'true';
        $display_price    = isset($args['price']) ? $args['price'] : 'true';
        $display_mode     = isset($args['display']) ? $args['display'] : '';

        CC_Log::write( "cc_product shortcode: subdomain: $subdomain :: product loader: $product_loader" );

        if($product_loader == 'server' || preg_match( '/(?i)msie [2-9]/', $_SERVER['HTTP_USER_AGENT'] ) ) {
            // if IE<=9 do not use the ajax product form method
            $product_form   =  self::cc_product_via_api($args, $content);
            $client_loading = 'false';
        }

        $out = "<div class=\"cc_product_wrapper\"><div id='" . $id . "' class='cc_product' data-subdomain='$subdomain' data-sku='$product_sku' data-quantity='$display_quantity' data-price='$display_price' data-display='$display_mode' data-client='$client_loading'>$product_form</div></div>";

        return $out;
    }

    public static function cc_product_via_api( $args, $content ) {
        $form = '';
        if($error_message = CC_Flash_Data::get( 'api_error' )) {
            $form .= "<p class=\"cc_error\">$error_message</p>";
        }

        $product_id       = isset( $args['id'] ) ? $args['id'] : false;
        $product_sku      = isset( $args['sku'] ) ? $args['sku'] : false;
        $display_quantity = isset( $args['quantity'] ) ? $args['quantity'] : 'true';
        $display_price    = isset( $args['price'] ) ? $args['price'] : 'true';
        $display_mode     = isset( $args['display'] ) ? $args['display'] : null;

        if ( $form_with_errors = CC_Flash_Data::get( $product_sku ) ) {
            $form .= $form_with_errors;
        } else {
            $product = new CC_Product();

            if ( $product_sku ) {
                $product->sku = $product_sku;
            } elseif ( $product_id ) {
                $product->id = $product_id;
            } else {
                throw new CC_Exception_Product( 'Unable to add product to cart without know the product sku or id' );
            }

            try {
                $form .= $product->get_order_form( $display_quantity, $display_price, $display_mode );
            } catch ( CC_Exception_Product $e ) {
                $form = "Product order form unavailable";
                CC_Log::write( 'Failed to get product order form: ' . $e->getMessage() );
            }
        }

        return $form;
    }

    public static function cc_product_price( $args, $content ) {
        $product_sku = isset( $args['sku'] ) ? $args['sku'] : false;
        return CC::product_price( $product_sku );
    }

    /**
     * Show the product catalog
     *
     * Params:
     *  - category: Limit the list to a certain product category slug (default "all")
     *  - max: The max number of products to list per page. (default 6)
     *  - sort: How to sort the products
     *    - price_asc
     *    - price_desc
     *    - name_asc
     *    - name_desc
     *    - menu
     */
    public static function cc_product_catalog( $args, $content ) {


        // Look for limit 
        $no_paging = false;
        $limit = null;
        if ( isset( $args['limit'] ) && is_numeric( $args['limit'] ) && $args['limit'] >= 1 ) {
            $no_paging = true;
            $limit = $args['limit'];
        }

        // Look for page
        $page = (get_query_var('paged')) ? get_query_var('paged') : 1;
        if ( $page == 1 ) {
            $page = (get_query_var('page')) ? get_query_var('page') : 1;
        }

        // Look for number of posts to show per page
        $per_page = ( isset( $args['max'] ) ) ? (int) $args['max'] : 6;
        if ( $per_page < 1 ) {
            $per_page = 6;
        }

        // Look for catalog title
        $title = null;
        if ( isset( $args['title'] ) ) {
            $title = $args['title'];
        }

        $params = array(
            'post_type' => 'cc_product',
            'posts_per_page' => $per_page,
            'post_status' => 'publish',
            'paged' => $page,
            'nopaging' => $no_paging
        );

        // Limit by category
        if ( isset( $args['category'] ) ) {
            $category = strtolower( $args['category'] );
            if ( 'all' != $category ) {
                $params['product-category'] = $category;
            }
        }

        // Order the posts
        $params['orderby'] = 'menu_order';
        if ( isset( $args['sort'] ) ) {
            switch( $args['sort'] ) {
                case 'price_asc':
                    $params['orderby'] = array( 'meta_value_num' => 'ASC', 'menu_order' => 'ASC' );
                    $params['meta_key'] = '_cc_product_price';
                    break;
                case 'price_desc':
                    $params['orderby'] = array( 'meta_value_num' => 'DESC', 'menu_order' => 'ASC' );
                    $params['meta_key'] = '_cc_product_price';
                    break;
                case 'name_asc':
                    $params['orderby'] = 'title';
                    $params['order'] = 'ASC';
                    break;
                case 'name_desc':
                    $params['orderby'] = 'title';
                    $params['order'] = 'DESC';
                    break;
                case 'menu':
                    $params['orderby'] = 'menu_order';
                    break;
            }
        }

        // $products = get_posts( $params );

        global $post;
        $wp_query = new WP_Query( $params );
        $product_count = 0;
        $out = '';

        if ( $wp_query->have_posts() ) {
            if ( isset( $title ) ) {
                $out .= '<h3 class="cc-catalog-title">' . $title . '</h3>';
            }

            // Include title in output if provided
            $out .= '<ul class="cc-product-list">';

            while( $wp_query->have_posts() ) {
                $wp_query->the_post();
                $src = cc_primary_image_for_product( $post->ID );
                $out .= CC_View::get( CC_PATH . 'templates/partials/grid-item.php', array('post' => $post, 'thumbnail_src' => $src ) );

                if ( isset( $limit ) ) {
                    $product_count += 1;
                    if ( $product_count >= $limit) {
                        break;
                    }
                }
            }

            $out .= '</ul>';

            // Include catalog pagination
            $out .= CC_View::get( CC_PATH . 'templates/partials/pagination.php', array( 'query' => $wp_query, 'page' => $page ) );
        }

        return $out;
    }
}
