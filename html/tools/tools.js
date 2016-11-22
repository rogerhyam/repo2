$( document ).ready(function() {
    
    // show the dialogue box if we need to login
    
    if($('#repo-show-login-flag').data('repo-login-flag')){
	    $( "#login-dialogue" ).dialog(
	        { 
	            width: 440,
	            modal: true,
	            closeOnEscape: false,
	            draggable: false,
	            position: {my: 'center', at: 'center', of:window },
                close: function(event, ui) { window.location = "/"; }
         });
	}

	// cancel button goes to home page
	$('#login-dialogue').on('click', '#login-cancel-button', function(event) {
	    event.preventDefault();
        window.location.href = '/';
    });
	
	

});
