(function($){

	$.hrefToggleTab = function(href){

		var modalDialog = jQuery('.modal-dialog');
		switch(href){

			case '#oml-register':
				modalDialog.attr('data-active-tab', '#oml-register');
				break;

			case '#oml-login':
				modalDialog.attr('data-active-tab', '#oml-login');
				break;

			default:
				modalDialog.attr('data-active-tab', '#oml-login');
		}

	}

})(jQuery);

jQuery(document).ready(function(){

	$('[href="#oml-login"], [href="#oml-register"]').on('click', function(event){
		event.preventDefault();
		$.hrefToggleTab($(this).attr('href'));

	});

	$('#oml_login_form').on('submit', function(event){
		event.preventDefault();
        
		$('.oml-login-button').text('Logging in...');
		$.post(ajaxOperations.ajax_url, $('#oml_login_form').serialize(), function(response){
            var obj = $.parseJSON(response);    
	        
            $('.oml-errors').slideDown(500, function(){
                $(this).html(obj.message)
            });
            $('.oml-login-button').text('Log in');
            
            if(obj.error == true && $('.login-error').length > 0){
                $('html').mousedown(function(){
                    $('.oml-errors').fadeOut(400);
                });
			}
            if(obj.error == false){
                setTimeout(function(){
                    window.location.reload();     
                },1000);
               
            }
            
		});
	});

	$('#oml_registration_form').on('submit', function(event){
		event.preventDefault();
        
		$('.oml-register-button').text('Signing up...');
		$.post(ajaxOperations.ajax_url, $('#oml_registration_form').serialize(), function(response){
			var objr = $.parseJSON(response);  
            
			$('.oml-register .oml-errors').slideDown(500, function(){
                $(this).html(objr.message)
            });
            $('.oml-register-button').text('Sign up');
            
            if(objr.error == true && $('.register-error').length > 0){
                $('html').mousedown(function(){
                    $('.oml-register .oml-errors').fadeOut(400);
                });
            }
            if(objr.error == false){
                setTimeout(function(){
                    window.location.reload();     
                },1500);
               
            }
			
		});
	});

});
