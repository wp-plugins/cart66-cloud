<?php
/**
 * The template used for displaying product content
 *
 * @package Reality66
 * @since 2.0
 */

$thumbs = cc_get_product_thumb_sources( $post->ID );
$images = cc_get_product_gallery_image_sources( $post->ID );
?>

<header class="entry-header">
    <h1 class="entry-title"><?php the_title(); ?></h1>
</header>

<?php include_once( CC_PATH . 'templates/partials/single-product.php' ); ?>

<div style="clear:both;"></div>

<div class="entry-content">
    <?php the_content(); ?>
</div>
