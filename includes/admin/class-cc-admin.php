<?php

class CC_Admin {

    public static $tabs;

    public function __construct() {
        self::$tabs = array( 'main-settings', 'post-type-settings' );

        // Initialize the main settings for Cart66
        CC_Admin_Main_Settings::init( 'cart66', 'cart66_main_settings' );
        CC_Admin_Post_Type_Settings::init( 'cart66', 'cart66_post_type_settings' );

        // Add the main cart66 admin pages to the menu
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );

        // Add the cart66 product insert media button to the editor
        add_action( 'current_screen', array($this, 'add_media_button_to_editor' ) );

        // Check for cart66 theme support
        add_action( 'after_setup_theme', array( $this, 'check_theme_support' ) );

        // Check for the page slurp page
        add_action( 'after_setup_theme', array( $this, 'check_page_slurp_exists' ) );
        
        // Check for permalinks
        add_action( 'after_setup_theme', array( $this, 'check_permalinks' ) );
        
        // Check for cart66 1.x migration
        add_action( 'after_setup_theme', array( $this, 'check_migration' ) );
    }

    public function add_menu_pages() {
        $page_title = __( 'Cart66 Cloud Settings', 'cart66' );
        $menu_title = __( 'Cart66 Cloud', 'cart66' );
        $capability = 'manage_options';
        $menu_slug = 'cart66';
        $display_callback = array( $this, 'render_main_settings' );
        $icon_url = 'dashicons-cart';
        add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $display_callback, $icon_url );

        // Admin page for secure console
        $parent_slug = 'cart66';
        $page_title = __( 'Cart66 Cloud Secure Console', 'cart66');
        $menu_title = __( 'Secure Console', 'cart66' );
        $menu_slug = 'cart66_secure_console';
        add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, 'secure_console') );
    }

    public static function get_active_tab() {
        $default_tab = self::$tabs[0]; // The first tab is the deafault tab
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab;
        $tab = in_array( $tab, self::$tabs ) ? $tab : $default_tab;
        return $tab;
    }

    public function render_main_settings() {
        $active_class = array();

        foreach( self::$tabs as $tab ) {
            $active_class[ $tab ] = '';
        }

        $active_tab = $this->get_active_tab();
        $active_class[ $active_tab ] = 'nav-tab-active';

        $data = array (
            'active_class' => $active_class,
            'active_tab'   => $active_tab
        );

        $view = CC_View::get(CC_PATH . 'views/admin/html-main-settings.php', $data );
        echo $view;
    }

    public function secure_console() {
        $view = CC_View::get(CC_PATH . 'views/admin/html-secure-console.php');
        echo $view;
    }

    public function init_settings() {
        $this->register_main_settings();
    }

    public function add_media_button_to_editor() {
        $screen = get_current_screen();

        // Add media button for cart66 shortcodes to post pages
        if ( 'post' == $screen->base ) {
            // CC_Log::write( 'Adding media button. Screen base: ' . $screen->base );
            add_action('media_buttons', array('CC_Admin_Media_Button', 'add_media_button'));
            add_action('admin_footer',  array('CC_Admin_Media_Button', 'add_media_button_popup'));
            add_action('admin_enqueue_scripts', array('CC_Admin_Media_Button', 'enqueue_select2'));
        }
    }

    public function check_theme_support() {
        if ( ! current_theme_supports( 'cart66' ) ) {
            add_action( 'admin_notices', 'cc_theme_support_notice' );
        }
    }

    public function check_page_slurp_exists() {
        $page = get_page_by_path('page-slurp-template');
        if( ! isset( $page ) ) {
            add_action( 'admin_notices', 'cc_page_slurp_notice' );
        }
    }

    public function check_permalinks() {
        if ( ! get_option('permalink_structure') ) {
            add_action( 'admin_notices', 'cc_permalinks_notice' );
        }
    }
    
    public function check_migration() {
        if ( get_option('cc_subdomain') ) {
            add_action( 'admin_notices', 'cc_migration_notice' );
        }
    }
}

return new CC_Admin();
