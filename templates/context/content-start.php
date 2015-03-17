<?php
/**
 * Output necessary content wrappers based on active theme
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$template = get_option( 'template' );
$out = '';

CC_Log::write('template for starting out: ' . $template );

$wrapper = CC_Admin_Setting::get_options( 'cart66_content_wrapper' );

if ( is_array( $wrapper ) && isset( $wrapper['start_markup'] ) && ! empty( $wrapper['start_markup'] ) ) {
    $out = $wrapper['start_markup'];
    CC_Log::write( "Set content wrapping start output: $out" );
}
else {
    switch( $template ) {

        case 'twentyeleven' :
            $out = '<div id="primary" class="site-content"><div id="content" role="main">';
            break;
        case 'twentytwelve' :
            $out = '<div id="primary" class="site-content"><div id="content" role="main">';
            break;
        case 'twentythirteen' :
            $out = '<div id="primary" class="content-area"><div id="content" role="main" class="site-content entry-content twentythirteen"><article class="hentry">';
            break;
        case 'twentyfourteen' :
            $url = cc_url();
            wp_enqueue_style( 'cc_twentyfourteen', $url .'templates/css/twentyfourteen.css' );
            $out = '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="cc-twentyfourteen">';
            break;
        case 'twentyfifteen' :
            $url = cc_url();
            wp_enqueue_style( 'cc_twentyfifteen', $url .'templates/css/twentyfifteen.css' );
            $out = '<div id="primary" class="content-area"><main id="main" class="site-main"><article class="page hentry">';
            break;
        case 'reddle':
            $out = '<div id="primary"><div id="content" role="main">';
            break;
        case 'patus':
            $out = '<div id="primary" class="content-area"> <main id="main" class="site-main" role="main">';
            break;
        case 'sparkling':
            $out = '<div id="primary" class="content-area"> <main id="main" class="site-main" role="main"> <div class="post-inner-content">';
            break;
        default :
            $out = '<div id="container"><div id="content" role="main">';
            break;

    }
}

// Allow 3rd party themes and plugins to override the output
echo apply_filters( 'cc_before_main_content_markup', $out );
