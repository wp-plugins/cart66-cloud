jQuery(document).ready(function($) {

    $('.cc-gallery-thumb-link').on( 'click', function( event ) {
        event.preventDefault();
        var ref = $(this).attr('data-ref');
        $('.cc-gallery-full-image').hide();
        $('#' + ref).show();
    });

});
