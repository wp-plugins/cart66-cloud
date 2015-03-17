<script type="text/javascript">
    function cc_insert_product_shortcode(){
        var product_info = JSON.parse(jQuery('#cc_product_id').val());
        var display_type = jQuery("#display_type").val();
        var display_quantity = jQuery("#display_quantity").is(":checked") ? 'true' : 'false';
        var display_price = jQuery("#display_price").is(":checked") ? 'true' : 'false';

        if(product_info.length == 0 || product_info == "0" || product_info == ""){
            alert("<?php _e("Please select a product", "cart66") ?>");
            return;
        }
        console.log(product_info);
        window.send_to_editor("[cc_product sku=\"" + product_info.sku + "\" display=\"" + display_type + "\" quantity=\"" + display_quantity + "\" price=\"" + display_price + "\"]");
    }

    jQuery(document).ready(function($) {
        $('#cc_product_id').select2({
            width: '100%',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function (term, page) {
                    return {
                        action: 'cc_ajax_product_search',
                        search: term
                    };
                },
                results: function (data, page) {
                  return { results: data };
                }
            }
        });
    });
</script>

<div id="cc_editor_pop_up" style="display:none;">
    <div id="cart66_pop_up" class="wrap">
        <div>
            <div style="padding:15px 15px 0 15px;">
                <h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;"><?php _e("Insert A Product", "cart66"); ?></h3>
                <span><?php _e("Select a product below to add it to your post or page.", "cart66"); ?></span>
            </div>

            <div style="padding:15px 15px 0 15px;">
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="cc_product_id">Products</label></th>
                            <td>
                                <input type="hidden" name="cc_product_id" id="cc_product_id" />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="display_type">Display mode</label></th>
                            <td>
                                <select name="display_type" id="display_type">
                                    <option value="inline">inline</option>
                                    <option value="vertical">vertical</option>
                                    <option value="horizontal">horizontal</option>
                                </select>
                                <p class="description"><?php _e('If the product has no options, we recommend choosing the "inline" display mode.', 'cart66'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="display_type">Show quantity field</label></th>
                            <td>
                                <input type="checkbox" id="display_quantity" checked='checked' /> <label for="display_quantity"><?php _e("Yes", "cart66"); ?></label>
                                &nbsp;&nbsp;&nbsp;
                                <p class="description"><?php _e('Allow the buyer to set the quanity when adding to cart', 'cart66'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="display_type">Show product price</label></th>
                            <td>
                                <input type="checkbox" id="display_price" checked='checked' /> <label for="display_price"><?php _e("Yes", "cart66"); ?></label>
                                &nbsp;&nbsp;&nbsp;
                                <p class="description"><?php _e('Do you want to show the price of the product?', 'cart66'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="padding:15px;">
                <input type="button" class="button-primary" value="Insert Product" onclick="cc_insert_product_shortcode();"/>
                &nbsp;&nbsp;&nbsp;
                <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "cart66"); ?></a>
            </div>
        </div>
    </div>
</div>
