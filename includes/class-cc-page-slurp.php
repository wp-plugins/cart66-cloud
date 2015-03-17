<?php 

class CC_Page_Slurp {

    public static function check_slurp() {
        global $post, $wp, $wp_query;

        $is_slurp = false;

        if ( is_object( $post ) && self::slurp_page_id() == $post->ID ) {
            CC_Log::write( 'Slurp with permalinks ON: Setting up filters to load content into slurped page' );
            $is_slurp = true;
        } elseif ( isset( $wp->query_vars['page_id'] ) &&  $wp->query_vars['page_id'] == 'page-slurp-template' ) {
            CC_Log::write( 'Slurp with permalinks OFF: Setting up filters to load content into slurped page' );
            unset( $wp->query_vars['page_id'] );

            $args = array( 
                'page_id' => self::slurp_page_id()
            );
            $wp_query = new WP_Query( $args );
            CC_Log::write( 'WP Query: ' . print_r( $wp_query, true ) );

            $is_slurp = true;
        }

        if ( $is_slurp ) {
            add_filter( 'wp_title',  'CC_Page_Slurp::set_page_title' );
            add_filter( 'the_title', 'CC_Page_Slurp::set_page_heading' );
            self::check_receipt();
        }
    }

    /**
     * Return the id of the page slurp template.
     *
     * If the page slurp template cannot be found, return false.
     *
     * @return mixed int or false
     */
    public static function slurp_page_id() {
        $page_id = false;
        $page = get_page_by_path('page-slurp-template');

        if ( is_object( $page ) && $page->ID > 0 ) {
            $page_id = $page->ID;
        }

       return $page_id;
    }

    
    public static function set_page_title( $content ) {
        CC_Log::write( 'Starting to set page title with original content: ' . $content );

        if( false !== strpos( $content, '{{cart66_title}}' ) ) {
            $title = cc_get( 'cc_page_title', 'text_field' );
            $content = str_replace('{{cart66_title}}', $title, $content);
            CC_Log::write( 'Slurp title changed: ' . $content );
        }
        else {
            CC_Log::write( 'Not setting slurp page title because the token is not in the content: ' . $content );
        }

        return $content;
    }

    public static function set_page_heading( $content ) {

        if( false !== strpos( $content, '{{cart66_title}}' ) ) {
            if ( isset( $_GET['cc_page_name'] ) ) {
                $content = str_replace('{{cart66_title}}', $_GET['cc_page_name'], $content);
            }
        }

        return $content;
    }

	public static function check_receipt() {
        // Drop the cart key cookie if the receipt page is requested
        if( isset( $_GET['cc_order_id'] ) && isset( $_GET['cc_page_name'] ) && strtolower( $_GET['cc_page_name'] ) == 'receipt' ) {
            CC_Log::write("Receipt page requested - preparing to drop the cart");
            CC_Cart::drop_cart();
            add_filter( 'the_content', array( 'CC_Page_Slurp', 'load_receipt' ) );
        }
	}

    public static function load_receipt( $content ) {
        $order_id = '';
        $receipt = '';

        if ( isset( $_REQUEST['cc_order_id'] ) ) {
            $order_id = $_REQUEST['cc_order_id'];
            try {
                $receipt = CC_Cloud_Receipt::get_receipt_content( $order_id );
                do_action('cc_load_receipt', $order_id);
            }
            catch(CC_Exception_Store_ReceiptNotFound $e) {
                $receipt = '<p>Unable to find receipt for the given order number.</p>';
            }
        }
        else {
            $receipt = '<p>Unable to find receipt because the order number was not provided.</p>';
        }

        $content = str_replace('{{cart66_content}}', $receipt, $content);

        return $content;
    }

    public static function hide_page_slurp( $pages ) {
        $page_slurp_id = self::slurp_page_id();
        if( $page_slurp_id ) {
            foreach ( $pages as $index => $page ) {
                if( $page->ID == $page_slurp_id ) {
                    unset( $pages[$index] );
                }
            }
        }
        return $pages;
    }

    /**
     * Creates physical page slurp template page and returns the page id.
     *
     * If the page could not be created, return 0.
     *
     * @return int
     */
    public static function create_slurp_page() {
        $page_slurp_id = self::slurp_page_id();

        if ( !$page_slurp_id ) {
            $page = array(
                'post_title' => '{{cart66_title}}',
                'post_content' => '{{cart66_content}}',
                'post_name' => 'page-slurp-template',
                'post_parent' => 0,
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            );
            $page_slurp_id = wp_insert_post( $page );
            CC_Log::write("Created page slurp template page with ID: $page_slurp_id");
        } else {
            $page = array(
                'ID' => $page_slurp_id,
                'post_title' => '{{cart66_title}}',
                'post_name' => 'page-slurp-template',
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            );
            wp_update_post($page);
            CC_Log::write("Updating an existing post for the page slurp template page: $page_slurp_id");
        }

        return $page_slurp_id;
    }

}
