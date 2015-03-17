<?php

class CC_Migration {

    protected $main_settings;
    protected $labels;
    protected $product_options;
    protected $member_notifications;
    protected $member_restrictions;

    public function __construct() {
        $this->main_settings = array(
            'secret_key' => '',
            'add_to_cart_redirect_type' => 'view_cart',
            'product_loader' => 'client',
            'shop_name' => 'Shop',  
            'custom_css' => '',
            'debug' => 'off'
        );

        $this->labels = array(
            'price' => 'Price:',
            'on_sale' => 'Sale:',
            'view' => 'View Details'
        );

        $this->product_options = array(
            'sort_method' => 'price_desc',
            'max_products' => 4
        );

        $this->member_notifications = array(
            'member_home' => '',      // member_home
            'post_types' => '',       // member_post_types
            'sign_in_required' => '', // login_required
            'not_included' => ''      // not_included
        );

        /**
         * Restrictions
         *
         * $array[ category_id ] = array ( subscription_01, subscription_02 )
         */
        $this->member_restrictions = array(

        );
    }

    public function run() {
        $this->migrate_secret_key();
        $this->migrate_redirect_type();
        $this->migrate_product_loader();
        $this->migrate_subdomain();
        $this->update_core_settings();
        $this->migrate_member_notifications();
        $this->migrate_member_restrictions();
    }

    public function migrate_secret_key() {
        $secret_key = get_option( 'cc_secret_key' );
        $this->main_settings['secret_key'] = $secret_key;
    }

    public function migrate_redirect_type() {
        $type = get_option( 'cc_redirect_type' );
        $this->main_settings['add_to_cart_redirect_type'] = $type;
    }

    public function migrate_product_loader() {
        $loader = get_option( 'cc_product_loader' );
        $this->main_settings['product_loader'] = $loader;
    }

    public function migrate_subdomain() {
        $subdomain = get_option( 'cc_subdomain' );
        $this->main_settings['subdomain'] = $subdomain;
    }

    public function update_core_settings() {
        CC_Admin_Setting::update_options( 'cart66_main_settings', $this->main_settings );
        CC_Admin_Setting::update_options( 'cart66_labels', $this->labels );
        CC_Admin_Setting::update_options( 'cart66_product_options', $this->product_options );
        CC_Admin_Notifications::dismiss( 'cart66_migration' );
    }

    public function migrate_member_notifications() {
        $old = get_option('ccm_access_notifications');
        $this->member_notifications['member_home']      = $old['member_home'];
        $this->member_notifications['post_types']       = $old['member_post_types'];
        $this->member_notifications['sign_in_required'] = $old['login_required'];
        $this->member_notifications['not_included']     = $old['not_included'];
        CC_Admin_Setting::update_options( 'cart66_members_notifications', $this->member_notifications );
    }

    public function migrate_member_restrictions() {
        $old = get_option( 'ccm_category_restrictions' );
        $this->member_restrictions = $old;
        CC_Admin_Setting::update_options( 'cart66_members_restrictions', $this->member_restrictions );
    }
}
