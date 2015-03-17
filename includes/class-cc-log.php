<?php

// Allow a defined constant to override the database setting for debugging
if( ! defined( 'CC_DEBUG' ) ) {
    $debug = CC_Admin_Setting::get_option( 'cart66_main_settings', 'debug' );
    CC_Log::write( 'Debug value: ' . $debug );
    $debug = ( 'on' == $debug ) ? true : false;
    define( 'CC_DEBUG', $debug );
}

class CC_Log {

    public static $log_file;

    public static function init() {
        if ( ! isset( self::$log_file ) ) {
            self::$log_file = CC_PATH . 'log.txt';
        }
    }

    public static function write( $data ) {
        if ( defined( 'CC_DEBUG' ) && CC_DEBUG ) {
            self::init();
            $backtrace = debug_backtrace();
            $file = $backtrace[0]['file'];
            $line = $backtrace[0]['line'];
            $date = current_time('m/d/Y g:i:s A') . ' ' . get_option('timezone_string');
            $out = "========== $date ==========\nFile: $file" . ' :: Line: ' . $line . "\n$data";

            if( is_writable( CC_PATH ) ) {
                file_put_contents( self::$log_file, $out . "\n\n", FILE_APPEND );
            }
        }
    }

    public static function download() {
        self::init();
        $data = 'The cart66 log file contains no data';

        if ( file_exists( self::$log_file ) ) {
            $data = file_get_contents( self::$log_file );
        }

        header( 'Content-type: text/plain' );
        header( 'Content-Disposition: attachment; filename="cart66_log.txt"' );
        echo $data;
        exit();
    }

    /**
     * Delete all of the contents from the log file
     */
    public static function reset() {
        self::init();
        if ( file_exists( self::$log_file ) ) {
            file_put_contents( self::$log_file, '' );
        }
    }

}
