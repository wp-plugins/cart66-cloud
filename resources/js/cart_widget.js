jQuery(document).ready(function($) {
  var data = { 
    action: 'render_cart66_cart_widget'
  };
  $.post(cc_widget.ajax_url, data, function(response) {
    $('.cc_cart_widget').html(response);
  });

  $('.cc_cart_widget').spin('small');
});
