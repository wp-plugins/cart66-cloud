<?php
/**
 * Close the content with the appropriate tags based on active theme.
 *
 * This file is used in conjunction with content-start.php to attempt to get 
 * the page content styled according to the active theme.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$template = get_option( 'template' );
$out = '';

$wrapper = CC_Admin_Setting::get_options( 'cart66_content_wrapper' );

if ( is_array( $wrapper ) && isset( $wrapper['end_markup'] ) && ! empty( $wrapper['end_markup'] ) ) {
    $out = $wrapper['end_markup'];
    CC_Log::write( "Set content wrapping end output: $out" );
}
else {
    switch( $template ) {
        case 'twentyeleven' :
            $out = '</div></div>';
            break;
        case 'twentytwelve' :
            $out = '</div></div>';
            break;
        case 'twentythirteen' :
            $out = '</article></div></div>';
            break;
        case 'twentyfourteen' :
            $out = '</div></div></div>';
            $out .= cc_get_sidebar( 'content' );
            break;
        case 'twentyfifteen' :
            $out = '</article></main></div>';
            break;
        case 'reddle':
            $out = '</div></div>';
            break;
        case 'patus':
            $out = '</main></div>';
            break;
        case 'sparkling':
            $out = '</div></main></div>';
            break;
        default :
            $out = '</div></div>';
            break;
    }
}

// Allow third part themes and plugins to override the output
echo apply_filters( 'cc_after_main_content_markup', $out );
