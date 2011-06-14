jQuery(document).ready( function($) {
	
	jQuery.fn.easymailStartRecipientsLoop = function( send ) {
		var sendnow = false;
		if ( send == true ) sendnow = "yes";
    	jQuery('#ajaxloop-response').smartupdater( {
				url : easymailJs.em_ajaxurl,
				data: { 
					action: 			easymailJs.action, 
					newsletter:			easymailJs.newsletter, 
					_ajax_nonce: 		easymailJs.nonce, 
					txt_success_added: 	easymailJs.txt_success_added, 
					txt_success_sent: 	easymailJs.txt_success_sent, 
					sendnow: 			sendnow 
				},
				type: 'POST',
				dataType: 'json',
				minTimeout: 100
			},
			function ( data ) {
				jQuery('#alo-easymail-bar-outer').show();
				jQuery('#alo-easymail-bar-inner').css( 'width', data.perc + "%" );
				jQuery('#ajaxloop-response').empty();
				if ( data.n_done >= data.n_tot ) {
					var txt_succ = ( sendnow == "yes" ) ? easymailJs.txt_success_sent : easymailJs.txt_success_added ;
					jQuery('#ajaxloop-response').html( "<p>"+ txt_succ + "!</p>" );
					jQuery( '#alo-easymail-bar-inner').addClass ( 'stopped' );
					jQuery('.easymail-recipients-start-loop').hide();
					jQuery('.easymail-recipients-start-loop-and-send').hide();
					jQuery('.easymail-recipients-pause-loop').hide();
					jQuery('.easymail-recipients-restart-loop').hide();					
					jQuery('#ajaxloop-response').smartupdaterStop();
					jQuery(this).easymailUpdateColumStatus( easymailJs.newsletter );
				} else {
					jQuery('#ajaxloop-response').html( data.perc + "% <small>(" + data.n_done + "/" + data.n_tot + ")</small>" );
				}
		});
		jQuery('.easymail-recipients-start-loop').hide();
		jQuery('.easymail-recipients-start-loop-and-send').hide();
		jQuery('.easymail-recipients-pause-loop').show();
		jQuery('.easymail-recipients-restart-loop').hide();
	};
	
	jQuery.fn.easymailSendMailTest = function() {
		var email = jQuery('#easymail-testmail').val();
		jQuery('#easymail-testmail-yes,#easymail-testmail-no').hide();
		jQuery('#easymail-testmail-loading').show();
		jQuery.post( easymailJs.ajaxurl, {
			action:			'easymail_send_mailtest',
			newsletter:		easymailJs.newsletter, 
			_ajax_nonce: 	easymailJs.nonce, 
			email: 			email
		   }, 
		   function ( response ) {
		   		jQuery('#easymail-testmail-loading').hide();
			  	if ( response == 'yes' ) {
			  		jQuery('#easymail-testmail-yes').show();
			 	} else {
			  		jQuery('#easymail-testmail-no').show();
			  	}
		   }
		);
	};

	jQuery.fn.easymailUpdateColumStatus = function( postId ) {
		var data = {
			action: 'alo_easymail_update_column_status',
			post_id: postId
		};
		jQuery.post( easymailJs.ajaxurl, data, function(response) {
			jQuery( '#alo-easymail-column-status-'+postId, window.parent.document ).html( response );
		});
		return false;
	};
	
	// Click Send Test Mail button
	jQuery('.easymail-send-testmail').live( "click", function() {
		jQuery(this).easymailSendMailTest();
	});	
		
	// Click Start "Put in queue" loop button
	jQuery('.easymail-recipients-start-loop').live( "click", function() {
		jQuery(this).easymailStartRecipientsLoop( false );
	});	

	// Click Start "Send now" loop button
	jQuery('.easymail-recipients-start-loop-and-send').live( "click", function() {
		jQuery(this).easymailStartRecipientsLoop( true );
	});	
	
	// Close thickbox and update status column
	jQuery('.easymail-recipients-close-popup').live( "click", function() {
		var postId = jQuery( this ).attr( 'rel' );
		jQuery(this).easymailUpdateColumStatus( postId );
		setTimeout( function(){ self.parent.tb_remove(); }, 100);
	});
	
	jQuery('.easymail-recipients-restart-loop').live( "click", function() {
		jQuery('#ajaxloop-response').smartupdaterRestart();
		jQuery('.easymail-recipients-pause-loop').show();
		jQuery('.easymail-recipients-restart-loop').hide();
		jQuery( '#alo-easymail-bar-inner').removeClass ( 'stopped' );
		return false;
	});

	jQuery('.easymail-recipients-pause-loop').live( "click", function() {
		jQuery('#ajaxloop-response').smartupdaterStop();
		jQuery('.easymail-recipients-restart-loop').show();
		jQuery('.easymail-recipients-pause-loop').hide();
		jQuery( '#alo-easymail-bar-inner').addClass ( 'stopped' );		
		return false;
	});	
});

