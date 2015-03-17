<?php
/**
 * The Template for displaying all single products
 *
 * @package Cart66/Templates
 * @since 2.0
 */

get_header(); ?>

<?php do_action( 'cart66_before_main_content' ); ?>

<?php while ( have_posts() ) : the_post(); ?>

    <?php cc_get_template_part( 'content', 'product' ); ?>

<?php endwhile; // end of the loop. ?>

<?php do_action( 'cart66_after_main_content' ); ?>

<?php 
get_sidebar(); 
get_footer(); 
