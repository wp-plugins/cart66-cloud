<?php
/*
Plugin Name: Cart66 Cloud
Plugin URI: http://cart66.com
Description: Securely Hosted Ecommerce For WordPress
Version: 2.0.3
Author: Reality66
Author URI: http://www.reality66.com

-------------------------------------------------------------------------
Cart66 Cloud
Copyright 2015  Reality66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists('Cart66_Cloud') ) {

    $plugin_file = __FILE__;
    if(isset($plugin)) { $plugin_file = $plugin; }
    elseif (isset($mu_plugin)) { $plugin_file = $mu_plugin; }
    elseif (isset($network_plugin)) { $plugin_file = $network_plugin; }

    define( 'CC_PLUGIN_FILE', $plugin_file );
    define( 'CC_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'CC_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'CC_TEMPLATE_DEBUG_MODE', false );

    /**
     * Cart66 main class
     *
     * The main Cart66 class should not be extended
     */
    final class Cart66_Cloud {

        protected static $instance;

        /**
         * Cart66 should only be loaded one time
         *
         * @since 2.0
         * @static
         * @return Cart66 instance
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            // Define constants
            define( 'CC_VERSION_NUMBER', '2.0.3' );

            // Register autoloader
            spl_autoload_register( array( $this, 'class_loader' ) );

            // Include files
            $this->include_core_files();

            // Register action hooks
            $this->register_actions();
        }

        public function include_core_files() {
            include_once( CC_PATH . 'includes/cc-helper-functions.php' );
            include_once( CC_PATH . 'includes/cc-partial-functions.php' );
            include_once( CC_PATH . 'includes/cc-actions.php');
            include_once( CC_PATH . 'includes/cc-product-post-type.php' );
            include_once( CC_PATH . 'includes/cc-template-manager.php' );
            include_once( CC_PATH . 'includes/cc-requests-authenticated.php' );
            include_once( CC_PATH . 'includes/cc-request-handlers.php' ); // Handle incoming tasks and custom routes
            include_once( CC_PATH . 'includes/class-cc-routes.php' );
            include_once( CC_PATH . 'includes/admin/cc-image-meta-box.php' );

            if( is_admin() ) {
                include_once( CC_PATH . 'includes/admin/class-cc-admin.php' );
                include_once( CC_PATH . 'includes/admin/cc-product-meta-box.php' );
            }
        }

        public function register_actions() {

            // Initialize core classes
            add_action( 'init', array( $this, 'init' ), 0 );

            // Check for incoming cart66 tasks and actions
            add_action( 'wp_loaded', 'cc_task_dispatcher' ); 
            add_action( 'parse_query', 'cc_route_handler' ); 

            // Register custom post type for products
            add_action( 'init', 'cc_register_product_post_type' );

            // Add actions to process all add to cart requests via ajax
            add_action( 'wp_enqueue_scripts',                 'cc_enqueue_ajax_add_to_cart' );
            add_action( 'wp_enqueue_scripts',                 'cc_enqueue_cart66_wordpress_js' );
            add_action( 'wp_enqueue_scripts',                 'cc_enqueue_cart66_styles' );
            add_action( 'wp_enqueue_scripts',                 'cc_enqueue_featherlight' );
            add_action( 'wp_ajax_cc_ajax_add_to_cart',        array('CC_Cart', 'ajax_add_to_cart') );
            add_action( 'wp_ajax_nopriv_cc_ajax_add_to_cart', array('CC_Cart', 'ajax_add_to_cart') );

            // Register sidebar widget
            add_action( 'widgets_init', create_function('', 'return register_widget("CC_Cart_Widget");') );

            // Write custom css to the head
            add_action( 'wp_head', 'cc_custom_css' );

            add_action( 'get_header', array('CC_Page_Slurp', 'check_slurp') );

            // Refresh notices after theme switch
            add_action( 'after_switch_theme', 'cc_reset_theme_notices' );

            // Register activation and deactivation hooks
            register_activation_hook( __FILE__, 'cc_activate' );
            register_deactivation_hook( __FILE__, 'cc_deactivate' );
            
            // Register plugin updater
            // add_action( 'init', 'cc_updater_init' );
            
            // Add filter for hiding slurp page from navigation
            add_filter( 'get_pages', 'CC_Page_Slurp::hide_page_slurp' );

            if ( 'yes' == CC_Admin_Setting::get_option( 'cart66_post_type_settings', 'product_templates' ) ) {
                // Add filter for rendering post type page templates
                add_filter( 'template_include', 'cc_template_include' );

                // Only register category widget when using product post type templates
                add_action( 'widgets_init', create_function('', 'return register_widget("CC_Category_Widget");') );
            }
            else {
                // Add filter for to attempt to get products showing as pages rather than posts
                add_filter( 'template_include', 'cc_use_page_template' );

                // Add filter for rendering product partial with gallery and order form
                add_filter( 'the_content', 'cc_filter_product_single' );
            }

        }

        public function init() {
            do_action( 'before_cart66_init' );

            CC_Shortcode_Manager::init();

            do_action ( 'after_cart66_init' );
        }

        public static function class_loader($class) {
            if(cc_starts_with($class, 'CC_')) {
                $class = strtolower($class);
                $file = 'class-' . str_replace( '_', '-', $class ) . '.php';
                $root = CC_PATH;

                if(cc_starts_with($class, 'cc_exception')) {
                    include_once $root . 'includes/exception-library.php';
                } elseif ( cc_starts_with( $class, 'cc_admin_setting' ) ) {
                    include_once $root . 'includes/admin/settings/' . $file;
                } elseif ( cc_starts_with( $class, 'cc_admin' ) ) {
                    include_once $root . 'includes/admin/' . $file;
                } elseif ( cc_starts_with( $class, 'cc_cloud' ) ) {
                    include_once $root . 'includes/cloud/' . $file;
                } else {
                    include_once $root . 'includes/' . $file;
                }

            } elseif($class == 'CC') {
                include CC_PATH . 'includes/class-cc.php';
            }
        }

        /** Helper functions ******************************************************/

        /**
         * Get the plugin path
         *
         * @return string
         */
        public function plugin_path() {
            return CC_PATH;
        }

        /**
         * Get the template path
         *
         * @return string
         */
        public function template_path() {
            return apply_filters( 'cart66_template_path', 'cart66/' );
        }

    }

}

Cart66_Cloud::instance();
