<div class="cc-product-form-no-gallery">
    <?php
        $product_sku = get_post_meta( get_the_ID(), '_cc_product_sku', true );
        echo do_shortcode( '[cc_product sku="' . $product_sku . '" quantity="true" price="true" display="vertical" ]' );                    
    ?>
</div>
