<?php
/**
 * Members settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Use this identifier when calling add_settings_error()
settings_errors( 'cart66_main_settings_group' );
?>

<div class="wrap">
    <h2>Cart66 Settings</h2>
    <h2 class="nav-tab-wrapper">
        <a href="?page=cart66&tab=main-settings" class="nav-tab <?php echo $active_class['main-settings']; ?>">Main</a>
        <a href="?page=cart66&tab=post-type-settings" class="nav-tab <?php echo $active_class['post-type-settings']; ?>">Advanced</a>
    </h2>
</div>

<div class="wrap">
    <form method="post" action="options.php">
        <?php
        if ( 'main-settings' == $active_tab ) {
            do_settings_sections('cart66_main');            // menu_slug used in add_settings_section
            settings_fields('cart66_main_settings');        // option_group
        } elseif ( 'post-type-settings' == $active_tab ) {
            do_settings_sections('cart66_post_type');       // menu_slug used in add_settings_section
            settings_fields('cart66_post_type_settings');   // option_group
        }

        // Submit button.
        submit_button();
    ?>
    </form>
</div>
