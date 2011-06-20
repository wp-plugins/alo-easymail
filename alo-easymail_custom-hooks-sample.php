<?php
/**
 * You can use this file to add you custom hooks to EasyMail plugin.
 *
 * To make loading this file you have to rename it to 'alo-easymail_custom-hooks.php'.
 * Some examples of custom hooks on http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
 *
 * IMPORTANT! To avoid the loss of the file when you use the automatic WP upgrade,
 * I suggest that you move the file into folder /wp-content/mu-plugins 
 * (if the directory doesn't exist, simply create it).
 *
*/




/*******************************************************************************
 * 
 * EXAMPLE 
 *
 * The following set of functions adds a new placeholder that includes the latest 
 * published posts inside newsletter
 *
 * @since: 2.0
 *
 ******************************************************************************/


/**
 * Add placeholder to table in new/edit newsletter screen
 *
 * Standard placeholder indexes are: 
 * 'easymail_post', 'easymail_subscriber', 'easymail_misc'
 */
function custom_easymail_placeholders ( $placeholders ) {
	$placeholders["custom_latest"] = array (
		"title" 		=> __("Latest posts", "alo-easymail"),
		"tags" 			=> array (
			"[LATEST-POSTS]"		=> __("A list with the latest published posts", "alo-easymail")
		)
	);
	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'custom_easymail_placeholders' );


/**
 * Add select in placeholders table
 * 
 * Note that the hook name is based upon the name of placeholder given in previous function as index:
 * alo_easymail_newsletter_placeholders_title_{your_placeholder}
 * If placeholder is 'my_archive' the hook will be:
 * alo_easymail_newsletter_placeholders_title_my_archive
 *
 */
function custom_easymail_placeholders_title_custom_latest ( $post_id ) {
	echo __("Select how many posts", "alo-easymail"). ": ";	
	echo '<select name="placeholder_custom_latest" id="placeholder_custom_latest" >';
	for ( $i = 3; $i <= 10; $i++ ) {
	    $select_custom_latest = ( get_post_meta ( $post_id, '_placeholder_custom_latest', true) == $i ) ? 'selected="selected"': '';
	    echo '<option value="'.$i.'" '. $select_custom_latest .'>'. $i. '</option>';
	}
	echo '</select>'; 
}
add_action('alo_easymail_newsletter_placeholders_title_custom_latest', 'custom_easymail_placeholders_title_custom_latest' );


/**
 * Save latest post number when the newsletter is saved
 */
function custom_save_placeholder_custom_latest ( $post_id ) {
	if ( isset( $_POST['placeholder_custom_latest'] ) && is_numeric( $_POST['placeholder_custom_latest'] ) ) {
		update_post_meta ( $post_id, '_placeholder_custom_latest', $_POST['placeholder_custom_latest'] );
	}
} 
add_action('alo_easymail_save_newsletter_meta_extra', 'custom_save_placeholder_custom_latest' );


/**
 * Replace the placeholder when the newsletter is sending 
 * @param	str		the newsletter text
 * @param	obj		newsletter object, with all post values
 * @param	obj		recipient object
 * @param	bol    	if apply "the_content" filters: useful to avoid recursive and infinite loop
 */ 
function custom_easymail_placeholders_get_latest ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {  
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$limit = get_post_meta ( $newsletter->ID, '_placeholder_custom_latest', true );
	$latest = "";
	if ( $limit ) {
		$args = array( 'numberposts' => $limit, 'order' => 'DESC', 'orderby' => 'date' );
		$myposts = get_posts( $args );
		if ( $myposts ) :
			$latest .= "<ul>\r\n";
			foreach( $myposts as $post ) :	// setup_postdata( $post );
				$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $post->post_title ) );
	   			$latest .= "<li><a href='". esc_url ( alo_em_translate_url( get_permalink( $post->ID ), $recipient->lang ) ). "'>". $post_title ."</a></li>\r\n"; 
			endforeach; 
			$latest .= "</ul>\r\n";
		endif;	     
	} 
	$content = str_replace("[LATEST-POSTS]", $latest, $content);
   
	return $content;	
}
add_filter ( 'alo_easymail_newsletter_content',  'custom_easymail_placeholders_get_latest', 10, 4 );




/*******************************************************************************
 * 
 * EXAMPLE 
 *
 * Do actions when a newsletter delivery is complete
 *
 * @since: 2.0 
 *
 ******************************************************************************/

/**
 * Send a notification to author and to admin when a newsletter delivery is complete
 */ 
function custom_easymail_newsletter_is_delivered ( $newsletter ) {	
	$title = apply_filters( 'alo_easymail_newsletter_title', $newsletter->post_title, $newsletter, false );
	$content = "The newsletter **" . stripslashes ( $title ) . "**  was delivered to all recipients.";
	$content .= "\r\nTo disable this notification you have to edit: ". ALO_EM_PLUGIN_URL . "/alo-easymail_custom-hooks.php";
	
  	$author = get_userdata( $newsletter->post_author );
  	wp_mail( $author->user_email, "Newsletter delivered!", $content );
  	wp_mail( get_option('admin_email'), "Newsletter delivered!", $content );
}
add_action ( 'alo_easymail_newsletter_delivered',  'custom_easymail_newsletter_is_delivered' );




/*******************************************************************************
 * 
 * EXAMPLE 
 *
 * Do actions when subscribers do something: eg. subscribe, unsubscribe,
 * edit subscription
 *
 * @since: 2.0 
 *
 ******************************************************************************/

 
/**
 * Send a notification to admin when there is a new subscriber
 * @param	obj
 * @param	int		user id optional: only if subscriber is also a registered user
 */ 
function custom_easymail_new_subscriber_is_added ( $subscriber, $user_id=false ) {
	if ( $user_id ) {
		$content = "A registered user has subscribed the newsletter:";
	} else {
		$content = "There is a new public subscriber:";
	}
	$content .= "\n\nemail: " . $subscriber->email ."\nname: ". $subscriber->name . "\nactivation: ". $subscriber->active . "\nlanguage: ". $subscriber->lang . "\n";
	if ( $user_id ) $content .= "user id: " . $user_id;
	$content .= "\r\nTo disable this notification you have to edit: ". ALO_EM_PLUGIN_URL . "/alo-easymail_custom-hooks.php";
	wp_mail( get_option('admin_email'), "New subscriber", $content );
}
add_action('alo_easymail_new_subscriber_added',  'custom_easymail_new_subscriber_is_added', 10, 2 );


/**
 * Do something when a subscriber updates own subscription info
 * @param	obj
 * @param	str 
 */ 
function custom_easymail_subscriber_is_updated ( $subscriber, $old_email ) {
	// do something...
}
add_action ( 'alo_easymail_subscriber_updated',  'custom_easymail_subscriber_is_updated', 10, 2);


/**
 * Do something when a subscriber unsubscribes
 * @param	str
 * @param	int		user id optional: only if subscriber is also a registered user
 */ 
function custom_easymail_subscriber_is_deleted ( $email, $user_id=false ) {
	// do something...
}
add_action('alo_easymail_subscriber_deleted',  'custom_easymail_subscriber_is_deleted', 10, 2 );



?>
