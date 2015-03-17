<?php
require_once CC_PATH . 'includes/cc-template-filters.php';
require_once CC_PATH . 'includes/cc-template-actions.php';

if ( ! function_exists( 'cc_get_template_part' ) ) {

    /**
     * Load the template for the given slug and name.
     *
     * Look in the following loactions for templates and pick the first one found:
     * - active-theme/slug-name.php
     * - active-theme/cart66/slug-name.php
     * - CC_PATH /templates/slugn-name.php
     * - active-theme/slug.php
     * - active-theme/cart66/slug.php
     * - allow 3rd party plugin to provide a template path
     */
    function cc_get_template_part( $slug, $name = '' ) {
        $template = '';

        // Look in active-theme/slug-name.php and active-theme/cart66/slug-name.php
        if ( $name && ! CC_TEMPLATE_DEBUG_MODE ) {
            $template = locate_template( array( "{$slug}-{$name}.php", 'cart66/' . "{$slug}-{$name}.php" ) );
        }

        // Get default slug-name.php
        if ( ! $template && $name && file_exists( CC_PATH . "/templates/{$slug}-{$name}.php" ) ) {
            $template = CC_PATH . "templates/{$slug}-{$name}.php";
        }

        // If template file doesn't exist, look in active-theme/slug.php and active-theme/cart66/slug.php
        if ( ! $template && ! CC_TEMPLATE_DEBUG_MODE ) {
            $template = locate_template( array( "{$slug}.php", 'cart66/' . "{$slug}.php" ) );
        }

        // Allow 3rd party plugin filter template file from their plugin
        if ( ( ! $template && CC_TEMPLATE_DEBUG_MODE ) || $template ) {
            $template = apply_filters( 'cc_get_template_part', $template, $slug, $name );
        }

        if ( $template ) {
            load_template( $template, false );
        }
    }
}



if ( ! function_exists( 'cc_page_title' ) ) {

	/**
	 * Return the title for the page depending on the context of the request
	 *
	 * @return string
	 */
	function cc_page_title( ) {

		if ( is_search() ) {

			$page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'cart66' ), get_search_query() );

            if ( get_query_var( 'paged' ) ) {
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'cart66' ), get_query_var( 'paged' ) );
            }

		} elseif ( is_tax() ) {

			$page_title = single_term_title( "", false );

		} else {
            $page_title = CC_Admin_Setting::get_option( 'cart66_post_type_settings', 'shop_name', 'Shop' );
		}

		$page_title = apply_filters( 'cc_page_title', $page_title );

	   	return $page_title;
	}

}

if ( ! function_exists( 'cc_get_template' ) ) {
    /**
     * Load cart66 templates
     *
     * @param string $template_name
     * @param array $args (default: array())
     * @param string $template_path (default: '')
     * @param string $default_path (default: '')
     */
    function cc_get_template( $template_name, $args = array(), $template_path = 'cart66/', $default_path = '' ) {
        if ( $args && is_array( $args ) ) {
            extract( $args );
        }

        $found_path = cc_locate_template( $template_name, $template_path, $default_path );

        if ( ! file_exists( $found_path ) ) {
            _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $found_path ), '2.0' );
            return;
        }

        // Allow 3rd party plugin to filter template file from their plugin
        $found_path = apply_filters( 'cc_get_template', $found_path, $template_name, $args, $template_path, $default_path );

        CC_Log::write( 'cc_get_template found path: ' . $found_path );

        do_action( 'cart66_before_template_part', $template_name, $template_path, $found_path, $args );

        include( $found_path );

        do_action( 'cart66_after_template_part', $template_name, $template_path, $found_path, $args );
    }
}



if ( ! function_exists( 'cc_locate_tempalte' ) ) {
    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     *		yourtheme		/	$template_path	/	$template_name
     *		yourtheme		/	$template_name
     *		$default_path	/	$template_name
     *
     * @access public
     * @param string $template_name
     * @param string $template_path (default: '')
     * @param string $default_path (default: '')
     * @return string
     */
    function cc_locate_template( $template_name, $template_path = 'cart66/', $default_path = '' ) {

        if ( ! $default_path ) {
            $default_path = CC_PATH . 'templates/';
        }

        // Look within passed path within the theme - this is priority
        $template_names = array(
            trailingslashit( $template_path ) . $template_name,
            $template_name
        );

        CC_Log::write( 'Locating template: ' . print_r( $template_names, true ) );

        $template = locate_template( $template_names );

        CC_Log::write( 'Found template: ' . print_r( $template, true ) );

        // Get default template
        if ( ! $template ) {
            $template = $default_path . $template_name;
        }

        // Return what we found
        return apply_filters('cart66_locate_template', $template, $template_name, $template_path);
    }
}


if ( ! function_exists( 'cc_get_sidebar' ) ) {

    /**
     * The same as the core WordPress get_sidebar() function except 
     * it returns the side bar rather than echo it out
     *
     * @return string 
     */
    function cc_get_sidebar( $name ) { 
        ob_start();
        get_sidebar( $name );
        $contents = ob_get_clean();
        return $contents;
    }

}
