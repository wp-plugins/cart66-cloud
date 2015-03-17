<?php
/*
Template Name: Archives
*/
get_header(); ?>

<?php echo do_action('cart66_before_main_content'); ?>

<?php if ( have_posts() ) : ?>
    <header class="entry-header">
        <h1 class="entry-title"><?php echo cc_page_title(); ?></h1>
    </header>

    <div class="entry-content">
        <ul class="cc-product-list">

            <?php while ( have_posts() ) : the_post(); ?>

                <?php
                    /*
                     * Include the post format-specific template for the content. If you want to
                     * use this in a child theme, then include a file called called content-___.php
                     * (where ___ is the post format) and that will be used instead.
                     */
                    cc_get_template_part( 'content', 'product-grid-item' );
                ?>

            <?php endwhile; ?>

        </ul>

        <?php 
            /**
             * @hooked cart66_pagination
             */
            do_action( 'cart66_after_catalog_loop' ); 
        ?>
    </div>

    <div style="clear: both;"></div>

<? else: ?>

    <?php
        // If no content, include the "No posts found" template.
        get_template_part( 'content', 'none' );
    ?>

<?php endif; ?>
		

<?php echo do_action('cart66_after_main_content'); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
