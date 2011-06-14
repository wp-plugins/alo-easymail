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
			jQuery( ".check_list" ).attr( "checked", status );
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
	
	jQuery.fn.easymailReportPopup = function( url, newsletter, lang ) {
		tb_show ( easymailJs.reportPopupTitle, url +"&newsletter=" + newsletter + "&lang=" + lang + "&TB_iframe=true&height=570&width=800", false );
		return false;
	}	
	
	jQuery.fn.easymailRecipientsGenPopup = function ( url, newsletter, lang ){
		tb_show ( easymailJs.subscribersPopupTitle, url +"&newsletter=" + newsletter + "&lang=" + lang + "&action=open_popup&TB_iframe=true&height=400&width=700&modal=true", false );
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

