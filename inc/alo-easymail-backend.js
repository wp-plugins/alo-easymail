jQuery(document).ready( function($) {
	//console.log ( easymailJs.pagenow);

	/*
	 * Edit or New newsletter pages
	 */
	
	if ( easymailJs.pagenow == 'post-new.php' || easymailJs.pagenow == 'post.php' ) {
	
	 	jQuery( "#easymail-filter-ul-languages" ).hide();
	 	jQuery( "#easymail-filter-ul-lists" ).hide(); 	
	 	jQuery( "#easymail-filter-ul-roles" ).hide();
	 	
		jQuery('.easymail-filter-subscribers-by-languages').live( "click", function() {
			jQuery( "#easymail-filter-ul-languages" ).toggle(); 
			return false;
		});
		jQuery('.easymail-filter-subscribers-by-lists').live( "click", function() {
			jQuery( "#easymail-filter-ul-lists" ).toggle(); 
			return false;		
		});
		jQuery('.easymail-filter-regusers-by-roles').live( "click", function() {
			jQuery( "#easymail-filter-ul-roles" ).toggle(); 
			return false;		
		});			
		
		jQuery( "#easymail-recipients-all-subscribers" ).live( "click", function() {
			var status = jQuery( this ).is(':checked');
			jQuery( ".check_list" ).prop( "checked", status );
		});

		jQuery( "#easymail-recipients-all-regusers" ).live( "click", function() {
			var status = jQuery( this ).is(':checked');
			jQuery( ".check_role" ).prop( "checked", status );
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

	// Preview in newsletter theme
	if ( jQuery("#easymail-open-preview").length > 0 ) {
		jQuery("#easymail-open-preview")
			.insertAfter('a#post-preview')
			.click(function(event) {
				event.preventDefault();

				jQuery("#easymail-open-preview-loading").show();
				
				var theme = jQuery( '#easymail-theme-select' ).val();
							
				var data = {
					action			: 	'alo_easymail_save_newsletter_content_transient',
					newsletter		:	easymailJs.postID,
					theme			:	theme,
					_ajax_nonce		: 	easymailJs.nonce
				};				
				
				jQuery.post( easymailJs.ajaxurl, data, function(response) {
					jQuery( '#easymail-modal-preview-loading' ).hide();
					
					if ( response == "-1" ) {
						// error
						alert( easymailJs.errGeneric );
						jQuery("#easymail-open-preview-loading").hide();
						
					} else {

						autosave();

						setTimeout(function(){
							jQuery("#easymail-open-preview-loading").hide();
							window.open ( easymailJs.pluginPath + 'alo-easymail_preview.php?newsletter=' + easymailJs.postID + '&_wpnonce=' + easymailJs.nonce, 'easymail-preview-'+ easymailJs.postID ); 
						}, 1000);
				
					}
				});

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
			jQuery( '#easymail-subscriber-delete-and-unsubscribe_'+ id  ).hide();					
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
			//edit : added all this for
			var alo_cf_array_val = new Array();
			for( k in alo_cf_array ){
				alo_cf_array_val[ k ] = jQuery('#subscriber-' + id + '-' + alo_cf_array[k] + '-new').val();
			}
			var lists = "";
			jQuery('.subscriber-'+ id +'-lists-new:checked').each ( function () { 
			 	lists = lists + jQuery(this).val() +","; 
			});
			
			//console.log( lists );
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
			//edit : added all this for
			for( k in alo_cf_array_val ){
				data[ 'new_' + alo_cf_array[k] ] = alo_cf_array_val[ k ];
			}
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
			jQuery( '#easymail-subscriber-delete-and-unsubscribe_'+ id  ).hide();						
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

			var to_unsubscribe;
			if ( jQuery( this ).hasClass('and-unsubscribe') ) {
				to_unsubscribe = 1;
			} else {
				to_unsubscribe = 0;
			}

			if ( to_unsubscribe == 1 ) {
				if ( !confirm( easymailJs.confirmDelSubscriberAndUnsubscribe ) ) return false;
			} else {
				if ( !confirm( easymailJs.confirmDelSubscriber ) ) return false;
			}
			
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'delete',
				subscriber		: 	id,
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce,
				to_unsubscribe	:	to_unsubscribe
			};

			jQuery( '#easymail-subscriber-edit-inline_'+ id  ).hide();
			jQuery( '#easymail-subscriber-delete_'+ id  ).hide();
			jQuery( '#easymail-subscriber-delete-and-unsubscribe_'+ id  ).hide();			
			jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			jQuery.post( easymailJs.ajaxurl, data, function(response) {
				jQuery( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				if ( response == "-1" ) { // error
					alert ( "ERROR" );
				} else if ( response == "-ok-deleted" ) {
					jQuery('tr#subscriber-row-'+ id ).animate({backgroundColor:'#ff0000'}, 500).fadeOut(
						'fast', 
						function() { 
							jQuery(this).remove(); 
						});
				}
			});
						
			return false;		
		});	

		// Disable Enter key when edit-inline
		var disable_classes = '.subscriber-email-new, .subscriber-name-new, .subscriber-active-new, .subscriber-lists-new'; //edit : added all this line
		//edit : added all this for
		for( k in alo_cf_array ){
			disable_classes = disable_classes + ', .subscriber-' + alo_cf_array[k] + '-new';
		}
		jQuery( disable_classes ).live("keypress", function(e) { //edit : orig : jQuery('.subscriber-email-new, .subscriber-name-new, .subscriber-active-new, .subscriber-lists-new').live("keypress", function(e) {
		 	if (e.keyCode == 13) return false;
		});		
				
	}	


	/*
	 * List of recipient modal
	 */
	var $listModal = jQuery("#easymail-recipient-list-modal");

	if ( $listModal.length > 0 ) {
		
		$listModal.dialog({                   
			dialogClass   : 'wp-dialog',           
			modal         : true,
			autoOpen      : false, 
			closeOnEscape : false,
			width			: 700,
			height		: 400,
			title			: easymailJs.titleRecListModal,
			resizable		: true,
			buttons       : [{
									text: easymailJs.txtClose,
									click: function() { $(this).dialog("close"); },
									class: 'button'
								}],
			beforeClose	:	function( event, ui ) {
									if ( jQuery('.easymail-recipients-pause-loop').is(':visible') ) {
										jQuery('.easymail-recipients-pause-loop').trigger( "click" );
									}
									jQuery(this).easymailUpdateColumStatus( jQuery(this).data('current-id') );
								},
			open			:	function( event, ui ) {
									// Modal about a new newsletter recipient list: clear bar and response and show disclaimer
									if ( jQuery(this).data('previous-id') != jQuery(this).data('current-id') ) {
										jQuery('#alo-easymail-list-disclaimer').show();

										jQuery('#ajaxloop-response').html('');
										jQuery('#alo-easymail-bar-outer').hide();
										jQuery('#alo-easymail-bar-inner').css( 'width', "0" );

										jQuery('.easymail-recipients-start-loop').show();
										jQuery('.easymail-recipients-start-loop-and-send').show();
										jQuery('.easymail-recipients-pause-loop').hide();
										jQuery('.easymail-recipients-restart-loop').hide();																
									}
								}								
		});
		
	}
	
	/*
	 * Functions
	 */
	
	jQuery.fn.easymailReportPopup = function( url, newsletter, lang ) {
		tb_show ( easymailJs.reportPopupTitle, url +"&newsletter=" + newsletter + "&lang=" + lang + "&TB_iframe=true&height=570&width=800", false );
		return false;
	}	

	jQuery('.easymail-reciepient-list-open').live( "click", function(event) {
		event.preventDefault();
		$listModal.data('previous-id', $listModal.data('current-id') );
		$listModal.data('current-id', jQuery(this).attr('rel') );
		$listModal.dialog('open');
	});
    
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


	/*
	 * Recipient list modal
	 */
	if ( $listModal.length > 0 ) {
			
		jQuery.fn.easymailStartRecipientsLoop = function( send ) {
			var sendnow = false;
			if ( send == true ) sendnow = "yes";

			jQuery('#ajaxloop-response').smartupdater( {
					url : easymailJs.ajaxurl,
					data: { 
						action: 			'alo_easymail_recipient_list_ajaxloop', 
						newsletter:			$listModal.data('current-id'), 
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
					jQuery(this).easymailReturnFromUpdate( data, sendnow, false );
			});
			
			jQuery('.easymail-recipients-start-loop').hide();
			jQuery('.easymail-recipients-start-loop-and-send').hide();
			jQuery('.easymail-recipients-pause-loop').show();
			jQuery('.easymail-recipients-restart-loop').hide();
			jQuery('#alo-easymail-list-disclaimer').hide();

			jQuery('#alo-easymail-bar-outer').show();
			jQuery('#ajaxloop-response').html( "<p>0% ...</p>" );
		}
		
		// After each periodic update...
		jQuery.fn.easymailReturnFromUpdate = function( data, sendnow, handle ) {
			if ( data.error == '' )
			{
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
					jQuery(this).easymailUpdateColumStatus( $listModal.attr('rel') );

					jQuery('#ajaxloop-response').smartupdaterStop();
				} else {
					jQuery('#ajaxloop-response').html( data.perc + "% <small>(" + data.n_done + "/" + data.n_tot + ")</small>" );
				}
			}
			else
			{
				jQuery('#ajaxloop-response').html( '<strong>' + data.error + '</strong>' );
			}
		}
		
		
		jQuery.fn.easymailSendMailTest = function() {
			var email = jQuery('#easymail-testmail').val();
			jQuery('#easymail-testmail-yes,#easymail-testmail-no').hide();
			jQuery('#easymail-testmail-loading').show();
			jQuery.post( easymailJs.ajaxurl, {
				action:			'easymail_send_mailtest',
				newsletter:		$listModal.attr('rel'), 
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
			jQuery( '#easymail-refresh-column-status-loading-'+ postId ).show();
			jQuery( '#alo-easymail-column-status-'+postId).html('');		
			var data = {
				action: 'alo_easymail_update_column_status',
				post_id: postId
			};
			jQuery.post( easymailJs.ajaxurl, data, function(response) {
				jQuery( '#easymail-refresh-column-status-loading-'+ postId ).hide();
				jQuery( '#alo-easymail-column-status-'+postId).html( response );
			});
			return false;
		};
		
		// Click Send Test Mail button
		jQuery('.easymail-send-testmail').live( "click", function(event) {
			event.preventDefault();
			jQuery(this).easymailSendMailTest();
		});	
			
		// Click Start "Put in queue" loop button
		jQuery('.easymail-recipients-start-loop').live( "click", function(event) {
			event.preventDefault();
			jQuery(this).easymailStartRecipientsLoop( false );
		});	

		// Click Start "Send now" loop button
		jQuery('.easymail-recipients-start-loop-and-send').live( "click", function(event) {
			event.preventDefault();
			jQuery(this).easymailStartRecipientsLoop( true );
		});	
			
		jQuery('.easymail-recipients-restart-loop').live( "click", function(event) {
			event.preventDefault();
			jQuery('#ajaxloop-response').smartupdaterRestart();

			jQuery('.easymail-recipients-pause-loop').show();
			jQuery('.easymail-recipients-restart-loop').hide();
			jQuery( '#alo-easymail-bar-inner').removeClass ( 'stopped' );
		});

		jQuery('.easymail-recipients-pause-loop').live( "click", function(event) {
			event.preventDefault();
			jQuery('#ajaxloop-response').smartupdaterStop();

			jQuery('.easymail-recipients-restart-loop').show();
			jQuery('.easymail-recipients-pause-loop').hide();
			jQuery( '#alo-easymail-bar-inner').addClass ( 'stopped' );		
		});

	}  // if $modal
			   	
});

