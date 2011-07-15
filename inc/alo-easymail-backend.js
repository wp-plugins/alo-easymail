jQuery(document).ready( function($) {
	//console.log ( easymailJs.pagenow);

	/*
	 * Edit or New newsletter pages
	 */
	
	if ( easymailJs.pagenow == 'post-new.php' || easymailJs.pagenow == 'post.php' ) {
	
	 	jQuery( "#easymail-filter-ul-languages" ).hide();
	 	jQuery( "#easymail-filter-ul-lists" ).hide(); 	
	 	
		jQuery('.easymail-filter-subscribers-by-languages').live( "click", function() {
			jQuery( "#easymail-filter-ul-languages" ).toggle(); 
			return false;
		});
		jQuery('.easymail-filter-subscribers-by-lists').live( "click", function() {
			jQuery( "#easymail-filter-ul-lists" ).toggle(); 
			return false;		
		});	
		
		jQuery( "#easymail-recipients-all-subscribers" ).live( "click", function() {
			var status = jQuery( this ).is(':checked');
			jQuery( ".check_list" ).prop( "checked", status );
		});
	
		jQuery( "#easymail-theme-select-preview" ).live( "click", function() {
			var theme = jQuery( '#easymail-theme-select' ).val();
			if ( theme != "" ) jQuery.fn.easymailThemePreviewPopup( theme );
			return false;
		});
			
	}

	/*
	 * Newsletters' Table page
	 */

	if ( easymailJs.pagenow == 'edit.php' ) {
	
		jQuery( ".easymail-column-short-summary" ).hide();
		
		jQuery('.easymail-toggle-short-summary').live( "click", function() {
			var postId = jQuery( this ).attr( 'rel' );
			jQuery( "#easymail-column-short-summary-"+ postId ).toggle(); 
			return false;
		});
		
		
		// Column status: Refresh
		jQuery('.easymail-refresh-column-status').live( "click", function() {
			var postId = jQuery( this ).attr( 'rel' );
			var data = {
				action: 'alo_easymail_update_column_status',
				post_id: postId
			};
			jQuery( '#easymail-refresh-column-status-loading-'+ postId ).show();
			jQuery( '#alo-easymail-column-status-'+ postId).hide();
			
			jQuery.post( easymailJs.ajaxurl, data, function(response) {
				jQuery( '#easymail-refresh-column-status-loading-'+ postId ).hide();
				jQuery( '#alo-easymail-column-status-'+postId).html( response ).show();
			});
			return false;
		});

		// Column status: Pause
		jQuery('.easymail-pause-column-status').live( "click", function() {

		});		
	
	}	
	

	/*
	 * Subscribers' Table page
	 */

	if ( easymailJs.pagenow == 'edit.php' && easymailJs.screenID == 'alo-easymail/alo-easymail_subscribers' ) {
		
		// Start inline-editing a subscriber
		jQuery('.easymail-subscriber-edit-inline').live( "click", function() {
			var id = jQuery( this ).attr('rel');
			var row_index = jQuery.trim( jQuery('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );
			
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'edit',
				subscriber		: 	id,
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce
			};

			jQuery( '#easymail-subscriber-edit-inline_'+ id  ).hide();
			jQuery( '#easymail-subscriber-delete_'+ id  ).hide();			
			jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			jQuery.post( easymailJs.ajaxurl, data, function(response) {
				jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				if ( response == "-1" ) { // error
					alert ( "ERROR" );
				} else {
					//console.log ( response );
					jQuery('tr#subscriber-row-'+ id ).html( response );
				}
			});
			
			return false;		
		});	
		
		
		// Save inline-editing subscriber
		jQuery('.easymail-subscriber-edit-inline-save').live( "click", function() {
			var id = jQuery( this ).attr('rel');
			var row_index = jQuery.trim( jQuery('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );
			
			// Prepare new info
			var email = jQuery('#subscriber-'+ id +'-email-new').val();
			var sname = jQuery('#subscriber-'+ id +'-name-new').val();
			var lang = jQuery('#subscriber-'+ id +'-lang-new').val();
			var active = ( jQuery('#subscriber-'+ id +'-active-new').is(':checked') ) ? 1 : 0;
			
			var lists = "";
			jQuery('.subscriber-'+ id +'-lists-new:checked').each ( function () { 
			 	lists = lists + jQuery(this).val() +","; 
			});
			
			console.log( lists );
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'save',
				subscriber		: 	id,
				new_name		:	sname,
				new_email		:	email,	
				new_active		:	active,
				new_lang		:	lang,
				new_lists		:	lists,					
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce
			};

			jQuery( '#easymail-subscriber-edit-inline-save_'+ id  ).hide();
			jQuery( '#easymail-subscriber-edit-inline-cancel_'+ id  ).hide();		
			jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			jQuery.post( easymailJs.ajaxurl, data, function(response) {
				jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				switch ( response ) {
					case "-1":
						alert ( "ERROR" );
						break;
						
					case "-error-email-is-not-valid":
						jQuery( '#easymail-subscriber-edit-inline-save_'+ id  ).show();
						jQuery( '#easymail-subscriber-edit-inline-cancel_'+ id  ).show();	
						alert ( easymailJs.errEmailNotValid );
						break;

					case "-error-name-is-empty":
						jQuery( '#easymail-subscriber-edit-inline-save_'+ id  ).show();
						jQuery( '#easymail-subscriber-edit-inline-cancel_'+ id  ).show();	
						alert ( easymailJs.errNameIsBlank );
						break;

					case "-error-email-already-subscribed":
						jQuery( '#easymail-subscriber-edit-inline-save_'+ id  ).show();
						jQuery( '#easymail-subscriber-edit-inline-cancel_'+ id  ).show();	
						alert ( easymailJs.errEmailAlreadySubscribed );
						break;							
												
					default: 
						//console.log ( response );
						jQuery('tr#subscriber-row-'+ id ).html( response );
				}
			});
			
			return false;				
		});	
		
		// Cancel inline-editing subscriber
		jQuery('.easymail-subscriber-edit-inline-cancel').live( "click", function() {
			var id = jQuery( this ).attr('rel');
			var row_index = jQuery.trim( jQuery('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );
			
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'cancel',
				subscriber		: 	id,
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce
			};

			jQuery( '#easymail-subscriber-edit-inline-save_'+ id  ).hide();
			jQuery( '#easymail-subscriber-edit-inline-cancel_'+ id  ).hide();			
			jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			jQuery.post( easymailJs.ajaxurl, data, function(response) {
				jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				if ( response == "-1" ) { // error
					alert ( "ERROR" );
				} else {
					//console.log ( response );
					jQuery('tr#subscriber-row-'+ id ).html( response );
				}
			});
			return false;		
		});	

		// Delete a subscriber
		jQuery('.easymail-subscriber-delete').live( "click", function() {
			var id = jQuery( this ).attr('rel');
			var row_index = jQuery.trim( jQuery('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );
			
			if ( !confirm( easymailJs.confirmDelSubscriber ) ) return false;
			
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'delete',
				subscriber		: 	id,
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce
			};

			jQuery( '#easymail-subscriber-edit-inline_'+ id  ).hide();
			jQuery( '#easymail-subscriber-delete_'+ id  ).hide();			
			jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			jQuery.post( easymailJs.ajaxurl, data, function(response) {
				jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				if ( response == "-1" ) { // error
					alert ( "ERROR" );
				} else if ( response == "-ok-deleted" ) {
					jQuery('tr#subscriber-row-'+ id ).slideToggle(
						'fast', 
						function() { 
							jQuery(this).remove(); 
						});
				}
			});
						
			return false;		
		});	
				
	}	
	
	
	/*
	 * Functions
	 */
	
	jQuery.fn.easymailReportPopup = function( url, newsletter, lang ) {
		tb_show ( easymailJs.reportPopupTitle, url +"&newsletter=" + newsletter + "&lang=" + lang + "&TB_iframe=true&height=570&width=800", false );
		return false;
	}	
	
	jQuery.fn.easymailRecipientsGenPopup = function ( url, newsletter, lang ){
		tb_show ( easymailJs.subscribersPopupTitle, url +"&newsletter=" + newsletter + "&lang=" + lang + "&action=open_popup&TB_iframe=true&height=400&width=700&modal=true", false );
		return false;
    }
    
	jQuery.fn.easymailThemePreviewPopup = function( theme ) {
		window.open ( easymailJs.themePreviewUrl + theme );
		return false;
	}	    
    
	jQuery.fn.easymailPausePlay = function( postId, button ) {
		var data = {
			action: 'alo_easymail_pauseplay_column_status',
			post_id: postId,
			button: button
		};
		jQuery( '#easymail-refresh-column-status-loading-'+ postId ).show();
		jQuery( '#alo-easymail-column-status-'+ postId).hide();
		
		jQuery.post( easymailJs.ajaxurl, data, function(response) {
			jQuery( '#easymail-refresh-column-status-loading-'+ postId ).hide();
			jQuery( '#alo-easymail-column-status-'+postId).html( response ).show();
		});
		return false;
	}	   
		   	
});

