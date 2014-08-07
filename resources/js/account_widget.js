jQuery(document).ready(function($) {
  var data = { 
    action: 'render_cart66_account_widget',
    logged_in_message: cc_account_widget.logged_in_message,
    logged_out_message: cc_account_widget.logged_out_message,
    show_link_history: cc_account_widget.show_link_history,
    show_link_profile: cc_account_widget.show_link_profile
  };

  $.post(cc_account_widget.ajax_url, data, function(response) {
    $('.cc_account_widget').html(response);
  });

  $('.cc_account_widget').spin('small');
});

