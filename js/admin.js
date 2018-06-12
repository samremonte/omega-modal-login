(function($){

    $.selectedThemeLocation = function(themeLocation){

        var dataToSend = {
            action: 'selectedThemeLocation',
            security: ajaxOperations.ajax_nonce,
            menu: themeLocation
        }

        $.ajax({
            type: 'POST',
            url: ajaxOperations.ajax_url,
            data: dataToSend,
            success: function(response){
                (response.length != 0) ? $('span.themelocation').text(response) : $('span.themelocation').text('None');;
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });

    }

})(jQuery);

jQuery(document).ready(function($){

    $('#oml-menus').change(function(event){
        event.preventDefault();
        $.selectedThemeLocation($(this).val());
    });

});
