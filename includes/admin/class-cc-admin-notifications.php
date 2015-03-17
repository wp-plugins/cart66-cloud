<?php

/**
 * Static wrapper class for CC_Admin_Notification_Manager
 *
 * Using this staic class wrapper improves the efficiency of working with notifications
 */
class CC_Admin_Notifications {

    public static $manager;

    public static function instance() {
        if ( ! isset( self::$manager ) ) {
            self::$manager = new CC_Admin_Notification_Manager();
        }
    }

    public static function get_notifications() {
        self::instance();
        return self::$manager->get_notifications();
    }

    public static function clear( $name ) {
        self::instance();
        self::$manager->clear( $name );
        self::$manager->save();
    }

    public static function clear_all() {
        self::instance();
        self::$manager->clear_all();
        self::$manager->save();
    }

    public static function dismiss( $name ) {
        self::instance();
        self::$manager->dismiss( $name );
    }

    public static function show( $name ) {
        self::instance();
        return self::$manager->show( $name );
    }

}
