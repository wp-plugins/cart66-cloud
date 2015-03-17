<div class="cc-category-widget-wrapper widget_links">
    <?php echo $before_widget; ?>

    <?php echo $before_title; ?>
    <span class="cc-category-widget-title"><?php echo $title; ?></span>
    <?php echo $after_title; ?>

    <div class="cc-category-widget">
        <ul>
            <?php foreach ( $categories as $cat ): ?>
                <li><a href="<?php echo get_term_link( $cat->slug, 'product-category' ); ?>" class="cc-category-widget-link"><?php echo $cat->name ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php echo $after_widget; ?>
</div>
