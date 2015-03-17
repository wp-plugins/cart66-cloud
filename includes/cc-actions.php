<?php

/** Cart66 Cloud activation process **/
function cc_activate() {
    CC_Log::write( 'Cart66 Cloud has been activated. Flushing rewrite rules' );
    add_action( 'admin_init', 'flush_rewrite_rules' );
}

function cc_deactivate() {
    CC_Log::write( 'Cart66 Cloud has been deactivated. Flushing rewrite rules' );
    flush_rewrite_rules();
}

/**
 * Return true if the global $post is a WP_Post and the content contains a cc_product shortcode
 *
 * @return boolean
 */
function cc_page_has_products() {
    global $post;
    $has_products = false;
    $post_type = get_post_type();

    // Check if this is the cart66 product post type
    if( 'cc_product' == $post_type ) {
        $has_products = true;
    } 
    // Check if this is a post containing a cart66 product shortcode
    elseif ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cc_product' )  ) {
        $has_products = true;
    }

    return $has_products;
}

/**
 * Enqueue the javascript file used for client side product loading
 *
 * This script is only enqued if:
 *   - the product loader type is client
 *   - the $post is a WP_Post
 *   - the post's content has the shortcode cc_product
 */
function cc_enqueue_cart66_wordpress_js() {
    $product_loader = CC_Admin_Setting::get_option( 'cart66_main_settings', 'product_loader' );
    $post_type = get_query_var( 'post_type' );

    wp_enqueue_script( 'jquery' ); // Always include jQuery for the sake of the sidebar widgets

    $product_post_types = array( 'cc_product' );

    if ( has_filter('cc_product_post_types') ) {
        $product_post_types = apply_filter( 'cc_product_post_types', $product_post_types );
    }

    if( in_array($post_type, $product_post_types) || ( 'client' == $product_loader && cc_page_has_products() ) ) {
        $cloud = new CC_Cloud_API_V1();
        $source = $cloud->protocol . 'manage.' . $cloud->app_domain . '/assets/cart66.wordpress.js';
        wp_enqueue_script('cart66-wordpress', $source, 'jquery', '1.0', true);
    }
}

/**
 * Enqueue javascript to implement ajax add to cart
 *
 * The script is only enqued if :
 *   - the $post is a WP_Post
 *   - the post's content has the shortcode cc_product
 */
function cc_enqueue_ajax_add_to_cart() {
    if( cc_page_has_products() ) {
        $url = cc_url();
        wp_enqueue_script(
            'cc-add-to-cart',
            $url . 'resources/js/add-to-cart.js',
            array( 'jquery' )
        );
        $ajax_url = admin_url('admin-ajax.php');
        wp_localize_script('cc-add-to-cart', 'cc_cart', array('ajax_url' => $ajax_url));
    }
}

/**
 * Enqueue cart66 styles for basic product layout
 */
function cc_enqueue_cart66_styles() {
    $url = cc_url();
    wp_enqueue_style( 'cart66-wp', $url . 'resources/css/cart66-wp.css' );

    $default_css = CC_Admin_Setting::get_option( 'cart66_main_settings', 'default_css' );
    if ( 'no' != $default_css ) {
        wp_enqueue_style( 'cart66-templates', $url . 'templates/css/cart66-templates.css' );
    }
}

/**
 * Enqueue featherlight feature for gallery
 */
function cc_enqueue_featherlight() {
    wp_enqueue_style ( 'featherlight-styles', '//cdn.rawgit.com/noelboss/featherlight/1.2.2/release/featherlight.min.css' );
    wp_enqueue_script( 'featherlight', '//cdn.rawgit.com/noelboss/featherlight/1.2.2/release/featherlight.min.js', array( 'jquery' ) );
}

/**
 * Write custom css to the head if there is custom css saved in the cart66 main settings
 */
function cc_custom_css() {
    $css = CC_Admin_Setting::get_option( 'cart66_main_settings', 'custom_css' );
    if( ! empty( $css ) ) {
        $styles = '<style type="text/css">' . $css . '</style>';
        echo $styles;
    }
}


function cc_theme_support_notice() {
    $product_templates = CC_Admin_Setting::get_option( 'cart66_post_type_settings', 'product_templates' );
    if ( ! current_theme_supports( 'cart66' ) && 'yes' == $product_templates && CC_Admin_Notifications::show( 'cart66_theme_support' ) ) {
        ?>
        <div class="error">
            <p> 
                <?php _e( 'The active theme does not declare support for the Cart66 product post type. ', 'cart66' ); ?> <br />
                <?php _e( 'Here are some times for <a href="http://help.cart66.com/article/330-fixing-layout-problems">fixing layout problems</a> and making <a href="http://help.cart66.com/article/329-custom-page-layouts">custom page layouts</a>.', 'cart66'); ?><br/>
                <a href="<?php echo add_query_arg( 'cc-task', 'dismiss_notification_theme_support' ); ?>" class="button" style="margin-top: 10px;" ><?php _e('Dismiss this message', 'cart66'); ?></a>
            </p>
        </div>
        <?php
    }
}

function cc_page_slurp_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'The page slurp page is not found. Please be sure to creat a page with the slug <strong>page-slurp-template</strong>', 'cart66' ); ?></p>
        <p>
            <a href="http://cart66.com/tutorial/page-slurp" class="button"><?php _e( 'More information', 'cart66' ); ?></a>
            <a href="<?php echo add_query_arg( 'cc-task', 'create_slurp_page' ); ?>" class="button"><?php _e('Create Slurp Page', 'cart66' ); ?></a>
        </p>
    </div>
    <?php
}

function cc_permalinks_notice() {
    if ( CC_Admin_Notifications::show( 'cart66_permalinks' ) ) {
        ?>
        <div class="error">
            <p><strong><?php _e( 'Permalinks Not Enabled', 'cart66' ); ?></strong></p>
            <p>
                <?php _e( 'Please enable permalinks in your WordPress settings to take full advantage of Cart66.', 'cart66' ); ?><br>
                <?php _e( '<strong>Post Name</strong> is a popular choice, but anything other than Default willl enable permalinks.', 'cart66' ); ?>
            </p>
            <p>
                <a href="http://cart66.com/tutorials/permalinks" class="button"><?php _e( 'More information', 'cart66' ); ?></a>
                <a href="options-permalink.php" class="button"><?php _e('Go To Permalinks Settings', 'cart66' ); ?></a>
                <a href="<?php echo add_query_arg( 'cc-task', 'dismiss_notification_permalinks' ); ?>" class="button"><?php _e('Dismiss this message', 'cart66'); ?></a>
            </p>
        </div>
        <?php
    }
}

function cc_migration_notice() {
    if ( CC_Admin_Notifications::show( 'cart66_migration' ) ) {
        ?>
        <div class="error">
            <p><strong><?php _e( 'Migrate To Cart66 2.0', 'cart66' ); ?></strong></p>
            <p>
                <?php _e( 'You have just installed Cart66 2.0 and will need to migrate your old Cart66 settings.', 'cart66' ); ?><br>
                <?php _e( 'Would you like to migrate your Cart66 settings now?', 'cart66' ); ?>
            </p>
            <p>
                <a href="http://cart66.com/tutorials/cart66-migration" class="button"><?php _e( 'More information', 'cart66' ); ?></a>
                <a href="<?php echo add_query_arg( 'cc-task', 'migrate_settings' ); ?>" class="button"><?php _e('Migrate Settings', 'cart66'); ?></a>
                <a href="<?php echo add_query_arg( 'cc-task', 'dismiss_notification_migration' ); ?>" class="button"><?php _e('Dismiss this message', 'cart66'); ?></a>
            </p>
        </div>
        <?php
    }
}

function cc_reset_theme_notices() {
    CC_Admin_Notifications::clear( 'cart66_theme_support' );
}

function cc_updater_init() {

	/* Load Plugin Updater */
	require_once CC_PATH . 'includes/plugin-updater.php';

	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( CC_PLUGIN_FILE ), //required
		'dashboard' => false,
		'username'  => false,
		'key'       => '',
		'repo_uri'  => 'http://cart66.com',  //required
		'repo_slug' => 'cart66-cloud',  //required
	);

	/* Load Updater Class */
	new Cart66_Cloud_Plugin_Updater( $config );
}

