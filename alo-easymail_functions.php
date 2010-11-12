<?php

/**
 * Add help image with tooltip
 */
function ALO_em_help_tooltip ( $text ) {
	$text = str_replace( array("'", '"'), "", $text );
	$html = "<img src='".ALO_EM_PLUGIN_URL."/images/12-help.png' title='$text' style='cursor:help;vertical-align:middle;margin-left:3px' />";
	return $html;
}


/**
 * Compatibility with older WP version
 * get_usermeta (deprecated from 3.0)
 */
if ( !function_exists('get_user_meta') ) {
	function get_user_meta ( $user, $key, $single=false ) {
		return get_usermeta ( $user, $key );
	}
}



/**
 * Sort a multidimensional array on a array kay (found on http://php.net/manual/en/function.sort.php)
 * @array		array	the array
 * @key			str		the field to use as key to sort
 * @order		str		sort method: "ASC", "DESC"
 */

function ALO_em_msort  ($array, $key, $order = "ASC") {
	$tmp = array();
	foreach($array as $akey => $array2)  {
		$tmp[$akey] = $array2[$key];
	}
    if ($order == "DESC") {
    	arsort($tmp , SORT_NUMERIC );
    } else {
    	asort($tmp , SORT_NUMERIC );
    }
	$tmp2 = array();       
 	foreach($tmp as $key => $value) {
		$tmp2[$key] = $array[$key];
	}       
	return $tmp2; 
}
        

/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 * (based on http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page )
 */
function ALO_em_html2plain ( $text ) {
	// transform in utf-8 if not yet
	if ( mb_detect_encoding($text, "UTF-8") != "UTF-8" ) $text = utf8_encode($text);
    $text = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0" 
        ),
        $text );
    // from <br> to \n
   	$text = preg_replace('/<br(\s+)?\/?>/i', "\n", $text );
	// reduce 2 or more consecutive <br> to one
	$text = preg_replace ("|(\\n\s*){2,}|s","\n", $text);
 	
    return strip_tags( $text );
}



/*************************************************************************
 * SUBSCRIPTION FUNCTIONS
 *************************************************************************/ 


/**
 * Count the n° of subscribers
 * return a array: total (active + not active), active, not active
 */
function ALO_em_count_subscribers () {
    global $wpdb;
    $search = $wpdb->get_results( "SELECT active, COUNT(active) AS count FROM {$wpdb->prefix}easymail_subscribers GROUP BY active ORDER BY active ASC" );
    $total = $noactive = $active = false;
    if ($search) {
		foreach ($search as $s) {
			switch ($s->active) {
				case 0: 	$noactive = $s->count; break;
				case 1: 	$active = $s->count; break;
			}
		}
		$total = $noactive + $active;
	} 
    return array ( $total, $active, $noactive );
} 


/**
 * Check is there is already a subscriber with that email and return ID subscriber
 */
function ALO_em_is_subscriber($email) {
    global $wpdb;
    $is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return (($is_subscriber)? $is_subscriber : 0); // ID in db tab subscribers
} 


/**
 * Check the state of a subscriber (active/not-active)
 */
function ALO_em_check_subscriber_state($email) {
    global $wpdb;
    $is_activated = $wpdb->get_var( $wpdb->prepare("SELECT active FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return $is_activated;
} 


/**
 * Modify the state of a subscriber (active/not-active) (BY ADMIN)
 */
function ALO_em_edit_subscriber_state_by_id($id, $newstate) {
    global $wpdb;
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                array ( 'active' => $newstate ),
                                array ( 'ID' => $id)
                            );
    return $output;
} 


/**
 * Modify the state of a subscriber (active/not-active) (BY SUBSCRIBER)
 */
function ALO_em_edit_subscriber_state_by_email($email, $newstate="1", $unikey) {
    global $wpdb;
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                array ( 'active' => $newstate ),
                                array ( 'email' => $email, 'unikey' => $unikey )
                            );
    return $output;
} 


/**
 * Add a new subscriber 
 * return bol/str:
 *		false					= generic error
 *		"OK"					= success
 *		"NO-ALREADYACTIVATED"	= not added because: email is already added and activated
 *		"NO-ALREADYADDED"		= not added because: email is already added but not activated; so send activation msg again
 */
function ALO_em_add_subscriber($email, $name, $newstate=0) {
    global $wpdb;
 	$output = true;
    // if there is NOT a subscriber with this email address: add new subscriber and send activation email
    if (ALO_em_is_subscriber($email) == false){
        $unikey = substr(md5(uniqid(rand(), true)), 0,24);    // a personal key to manage the subscription
           
        // try to send activation mail, otherwise will not add subscriber
        if ($newstate == 0) {
            if ( !ALO_em_send_activation_email($email, $name, $unikey) ) $output = false; // DEBUG ON LOCALHOST: comment this line to avoid error on sending mail
        }
        
        $wpdb->insert   ("{$wpdb->prefix}easymail_subscribers",
                        array( 'email' => $email, 'name' => $name, 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'active' => $newstate, 'unikey' => $unikey, 'lists' => "_")
                        );
        if ( $output ) $output = "OK"; //return true;
        
    } else {
        // if there is ALREADY a subscriber with this email address, and if is NOT confirmed yet: re-send an activation email
        if ( ALO_em_check_subscriber_state($email) == 0) {
            // retrieve existing unique key 
            $exist_unikey = $wpdb->get_var( $wpdb->prepare("SELECT unikey FROM {$wpdb->prefix}easymail_subscribers WHERE ID='%d' LIMIT 1", ALO_em_is_subscriber($email) ) );
            
            if ( ALO_em_send_activation_email($email, $name, $exist_unikey) ) {
                // update join date to today
                $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                            array ( 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ) ),
                                            array ( 'ID' => ALO_em_is_subscriber($email) )
                                        );
             	// tell that there is already added but not active: so it has sent another activation mail.......
                $output = "NO-ALREADYADDED";
            } else {
                $output = false;
                //$output = "NO-ALREADYADDED"; // DEBUG ON LOCALHOST: comment the previous line and uncomment this one to avoid error on sending mail
            }
        } else {
	        // tell that there is already an activated subscriber.....
            $output = "NO-ALREADYACTIVATED"; 
        }
    }
    return $output;
} 


/**
 * Delete a subscriber (BY ADMIN/REGISTERED-USER)
 */
function ALO_em_delete_subscriber_by_id($id) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id ) );
    return $output;
} 


/**
 * Delete a subscriber (BY SUBSCRIBER)
 */
function ALO_em_delete_subscriber_by_email($email, $unikey) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey ) );
    return $output;
} 


/**
 * Check if can access subscription page (BY SUBSCRIBER)
 */
function ALO_em_can_access_subscrpage ($email, $unikey) {
    global $wpdb;
    // check if email and unikey match
    $check = ALO_em_check_subscriber_email_and_unikey ( $email, $unikey );
    return $check;
} 


/**
 * Check if subscriber email and unikey match (BY SUBSCRIBER) (check EMAIL<->UNIKEY)
 */
function ALO_em_check_subscriber_email_and_unikey ( $email, $unikey ) {
    global $wpdb;
    $check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey) );
    return $check;
} 


/**
 * Send email with activation link
 */
function ALO_em_send_activation_email($email, $name, $unikey) {
	$blogname = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
    // Headers
    $mail_sender = "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
    $headers =  "MIME-Version: 1.0\n";
    $headers .= "From: ". $blogname ." <".$mail_sender.">\n";
    $headers .= "Content-Type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
    // Subject
    $subject = sprintf(__("Confirm your subscription to %s Newsletter", "alo-easymail"), $blogname );
    // Main content
    $content = sprintf(__("Hi %s\nto complete your subscription to %s newsletter you need to click on the following link (or paste it in the address bar of your browser):\n", "alo-easymail"), $name, $blogname );

 	$div_email = explode("@", $email); // for link

    //$content .= get_option ('home') . "/?page_id=". get_option('ALO_em_subsc_page'). "&ac=activate&em1=" . $div_email[0] . "&em2=" . $div_email[1] . "&uk=" . $unikey . "\r\n";
    $arr_params = array ('ac' => 'activate', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $unikey );
	$uns_link = add_query_arg( $arr_params, get_page_link (get_option('ALO_em_subsc_page')) );
    $content .=	$uns_link . "\r\n";
    
    $content .= __("If you did not ask for this subscription ignore this message.", "alo-easymail"). "\n";
    $content .= __("Thank you", "alo-easymail")."\n". $blogname ."\n";
    
    //echo "<br />".$headers."<br />".$subscriber->email."<br />". $subject."<br />".  $content ."<hr />" ; // DEBUG
    $sending = @wp_mail( $email, $subject, $content, $headers);  
    return $sending;
} 


/**
 * Print table with tags summay
 */
function ALO_em_tags_table () { ?>
	<table class="widefat">
	<thead><tr><th scope="col" style="width:20%"><?php _e("Post tags", "alo-easymail") ?></th><th scope="col"></th></tr></thead>
	<tbody>
	<tr><td>[POST-TITLE]</td><td style='font-size:80%'><span class="description"><?php _e("The link to the title of the selected post.", "alo-easymail") ?></span></td></tr>
	<tr><td>[POST-EXCERPT]</td><td style='font-size:80%'><span class="description"><?php _e("The excerpt (if any) of the post.", "alo-easymail") ?></span></td></tr>
	<tr><td>[POST-CONTENT]</td><td style='font-size:80%'><span class="description"><?php _e("The main content of the post.", "alo-easymail") ?> <?php _e("Warning: this tag inserts the test as it is, including shortcodes from other plugins.", "alo-easymail") ?></span></td></tr>
	</tbody></table>

	<table class="widefat">
	<thead><tr><th scope="col" style="width:20%"><?php _e("Subscriber tags", "alo-easymail") ?></th><th scope="col"></th></tr></thead>
	<tbody>
	<tr><td>[USER-NAME]</td><td style='font-size:80%'><span class="description"><?php _e("Name and surname of registered user.", "alo-easymail") ?> (<?php _e("For subscribers: the name used for registration", "alo-easymail") ?>)</span></td></tr>
	<!-- Following [USER-FIRST-NAME] added GAL -->
	<tr><td>[USER-FIRST-NAME]</td><td style='font-size:80%'><span class="description"><?php _e("First name of registered user.", "alo-easymail") ?> (<?php _e("For subscribers: the name used for registration", "alo-easymail") ?>).</span></td></tr>
	</tbody></table>

	<table class="widefat">
	<thead><tr><th scope="col" style="width:20%"><?php _e("Other tags", "alo-easymail") ?></th><th scope="col"></th></tr></thead>
	<tbody>
	<tr><td>[SITE-LINK]</td><td style='font-size:80%'><span class="description"><?php _e("The link to the site", "alo-easymail") ?>: <?php echo "<a href='".get_option ('siteurl')."'>".get_option('blogname')."</a>" ?></span></td></tr>
	</tbody></table>
<?php 
}
 


/*************************************************************************
 * AJAX 'SACK' FUNCTION
 *************************************************************************/ 

add_action('wp_head', 'ALO_em_ajax_js' );


function ALO_em_ajax_js()
{
  // use JavaScript SACK library for Ajax
  wp_print_scripts( array( 'sack' ));

?>
<script type="text/javascript">
//<![CDATA[
<?php if ( is_user_logged_in() ) { // if logged in ?>
function alo_em_user_form ( opt )
{
  // updating...
  document.getElementById('alo_easymail_widget_feedback').innerHTML = '';
  document.getElementById('alo_easymail_widget_feedback').className = 'alo_easymail_widget_error';
  document.getElementById('alo_em_widget_loading').style.display = "inline";  
  
   var mysack = new sack( 
       "<?php echo admin_url() ?>admin-ajax.php" );       

  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "alo_em_user_form_check" );
  mysack.setVar( "alo_easymail_option", opt );
  
  var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');
  var length = cbs.length;
  var lists = "";
  for (var i=0; i < length; i++) {
  	if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') {
  		if ( cbs[i].checked ) lists += cbs[i].value + ",";
  	}
  }
  mysack.setVar( "alo_em_form_lists", lists );
  mysack.onError = function() { alert('Ajax error' )};
  mysack.runAJAX();

  return true;

} 
<?php } else {  // if not is_user_logged_in() ?>
function alo_em_pubblic_form ()
{
  document.alo_easymail_widget_form.submit.value="<?php _e("sending...", "alo-easymail") ?>";
  document.alo_easymail_widget_form.submit.disabled = true;
  document.getElementById('alo_em_widget_loading').style.display = "inline";
  document.getElementById('alo_easymail_widget_feedback').innerHTML = "";
  
   var mysack = new sack( 
       "<?php echo admin_url() ?>admin-ajax.php" );    

  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "alo_em_pubblic_form_check" );
  mysack.setVar( "alo_em_opt_name", document.alo_easymail_widget_form.alo_em_opt_name.value );
  mysack.setVar( "alo_em_opt_email", document.alo_easymail_widget_form.alo_em_opt_email.value );
  
  var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');
  var length = cbs.length;
  var lists = "";
  for (var i=0; i < length; i++) {
  	if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') {
  		if ( cbs[i].checked ) lists += cbs[i].value + ",";
  	}
  }
  mysack.setVar( "alo_em_form_lists", lists );
  mysack.onError = function() { alert('Ajax error' )};
  mysack.runAJAX();

  return true;

} 
//]]>
<?php } // end if is_user_logged_in() ?>
</script>
<?php
} // end ALO_em_ajax_js

add_action('wp_ajax_alo_em_user_form_check', 'ALO_em_user_form_callback');				// logged in
add_action('wp_ajax_nopriv_alo_em_pubblic_form_check', 'ALO_em_pubblic_form_callback'); // pubblic, no logged in

// For logged-in users
function ALO_em_user_form_callback() {
	global $wpdb, $user_ID, $user_email, $current_user;
	get_currentuserinfo();
	//die ("alert(\"".$_POST['alo_easymail_option']."\")");
   	if ( $user_ID && isset($_POST['alo_easymail_option'])) {
   		switch ( $_POST['alo_easymail_option'] ) {
   			case "yes":
	            ALO_em_add_subscriber($user_email, $current_user->user_firstname." ". $current_user->user_lastname , 1);
	            break;
			case "no":		
				ALO_em_delete_subscriber_by_id( ALO_em_is_subscriber($user_email) );
				break;
        	case "lists":
				$subscriber_id = ALO_em_is_subscriber ( $user_email );
				$mailinglists = ALO_em_get_mailinglists( 'public' );
				$lists = ( isset($_POST['alo_em_form_lists'])) ? explode ( ",", trim ( $_POST['alo_em_form_lists'] , "," ) ) : array();
				if ($mailinglists) {
					foreach ( $mailinglists as $mailinglist => $val) {					
						if ( in_array ( $mailinglist, $lists ) ) {
							ALO_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
						} else {
							ALO_em_delete_subscriber_from_list ( $subscriber_id, $mailinglist ); // remove from list
						}
					}
				}
				break;
		}
		// Compose JavaScript for return
		$feedback = "";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '". __("Successfully updated", "alo-easymail").".';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = 'alo_easymail_widget_ok';";
		$feedback .= "document.getElementById('alo_em_widget_loading').style.display = 'none';";
		// if unsubscribe deselect all lists
		if ( isset($_POST['alo_easymail_option']) && $_POST['alo_easymail_option']=="no" ) {
			$feedback .= "var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');";
			$feedback .= "var length = cbs.length;";
			$feedback .= "for (var i=0; i < length; i++) {";
			$feedback .= 	"if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') { cbs[i].checked = false; }";
			$feedback .= "}";
		}
		// END!	
		die($feedback);
    }
}

// For NOT-logged-in pubblic visitors
function ALO_em_pubblic_form_callback() {
	global $wpdb, $user_ID;
    if (isset($_POST['alo_em_opt_name']) && isset($_POST['alo_em_opt_email'])){
        $error_on_adding = "";
        $just_added = false;
		$name 	= stripslashes(trim($_POST['alo_em_opt_name']));
		$email	= stripslashes(trim($_POST['alo_em_opt_email']));
        if ( !is_email($email) ) {
            $error_on_adding .= __("The e-email address is not correct", "alo-easymail"). "<br />";
        }
        if ( $name == "") {
            $error_on_adding .= __("The name field is empty", "alo-easymail") . ".<br />";
        }
        if ($error_on_adding == "") { // if no error
            // try to add new subscriber (and send mail if necessary) and return TRUE if success
            $try_to_add = ALO_em_add_subscriber( $email, $name, 0); 
            switch ($try_to_add) {
            	case "OK":
            		$just_added = true;
            		break;
            	case "NO-ALREADYADDED":
            		$error_on_adding = __("Warning: this email address has already been subscribed, but not activated. We are now sending another activation email", "alo-easymail"). ".<br />";
	            	break;
               	case "NO-ALREADYACTIVATED":
               		$error_on_adding = __("Warning: this email address has already been subscribed", "alo-easymail"). ".<br />";
	            	break;
	            default: // false
	            	$error_on_adding = __("Error during sending: please try again", "alo-easymail"). ".<br />";
            }
            
            // if requested, add to lists
            if ( isset($_POST['alo_em_form_lists']) && count($_POST['alo_em_form_lists']) ) {
	            $lists = explode ( ",", trim ( $_POST['alo_em_form_lists'] , "," ) );
	            $subscriber = ALO_em_is_subscriber ( $email );
	            foreach ( $lists as $list ) {
					ALO_em_add_subscriber_to_list ( $subscriber, $list );
				}
	      	}
        } 
        if ($just_added == true) {
			$output = __("Subscription successful. You will receive an e-mail with a link. You have to click on the link to activate your subscription.", "alo-easymail");   
       		$classfeedback = "alo_easymail_widget_ok";
        } else {
			$output = $error_on_adding;
        	$classfeedback = "alo_easymail_widget_error";
       	}

		// Compose JavaScript for return
		$feedback = "";
		$feedback .= "document.alo_easymail_widget_form.submit.disabled = false;";
		$feedback .= "document.alo_easymail_widget_form.submit.value = '". __("Subscribe", "alo-easymail"). "';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '$output';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = '$classfeedback';";
		$feedback .= "document.getElementById('alo_em_widget_loading').style.display = 'none';";
		// END!	
		die($feedback);
    }
}

/*************************************************************************
 * BATCH SENDING
 *************************************************************************/ 

/**
 * Add a new newsletter to batch sending
 */
function ALO_em_add_new_batch ( $user_ID, $subject, $content, $content_plain, $recipients, $tracking ) {
	global $wpdb;
	$add_newsletter = $wpdb->insert(
                "{$wpdb->prefix}easymail_sendings", 
                array( 'start_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'last_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'user' => $user_ID, 'subject' => $subject, 
                'content' => $content, 'content_plain' => $content_plain, 'sent' => '0', 'recipients' => $recipients, 'tracking' => $tracking )
            );
    return $add_newsletter;
}
	

/**
 * Delete a sent newsletter 
 */
function ALO_em_delete_newsletter ( $newsletter ) {
	global $wpdb;
	// delete newsletter
	$delete = $wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_sendings WHERE ID = %d", $newsletter ));
	// delete trackings
	$wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_trackings WHERE newsletter = %d", $newsletter ));
    return $delete;
}

	
/**
 * Send the newsletter to a fraction of recipients every X minutes
 */
function ALO_em_batch_sending () {
	global $wpdb;
	
	// retrieve info of oldest newsletter to send
	$sending_info =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}easymail_sendings WHERE sent = 0 ORDER BY ID ASC LIMIT 1");
	
	// if no sending there is nothing to send: batch has finished
	if ($sending_info == false) return;
	
	// the recipient of sending
	$recipients = unserialize( $sending_info->recipients );
	
	// search the interval between now and previous sending
	$diff_time = strtotime( get_date_from_gmt( date("Y-m-d H:i:s") ) ) - strtotime($sending_info->last_at);
	// so... how much recipients for this interval? // (86400 = seconds in a day)
	$day_rate = get_option('ALO_em_dayrate');
	$tot_recs = max ( floor(($day_rate * $diff_time / 86400)) , 1); 
		
	// for each sent mail add 1 to recs
	$n_recs = 0;
 
    for ($r=0; $r < count($recipients); $r++) {  
    
    	// if already sent to this recipient skip it
    	if ($recipients[$r]['result'] != "") {
    	   	continue; // go to next rec
    	}
    	
        // For each recipient delete TAGs update
        $updated_content = $sending_info->content;
        $updated_content_plain = $sending_info->content_plain;
		       
        // TAG: [USER-NAME]
        if ($recipients[$r]['name']) {
            $updated_content = str_replace("[USER-NAME]", $recipients[$r]['name'], $updated_content);     
            $updated_content_plain = str_replace("[USER-NAME]", $recipients[$r]['name'], $updated_content_plain);    
        } else {
            $updated_content = str_replace("[USER-NAME]", "", $updated_content);
            $updated_content_plain = str_replace("[USER-NAME]", "", $updated_content_plain);
        }            
        
        //>>>>>>> added GAL
        // TAG: [USER-FIRST-NAME]
        if ($recipients[$r]['firstname']) {
            $updated_content = str_replace("[USER-FIRST-NAME]", $recipients[$r]['firstname'], $updated_content);       
            $updated_content_plain = str_replace("[USER-FIRST-NAME]", $recipients[$r]['firstname'], $updated_content_plain);    
        } else {
            $updated_content = str_replace("[USER-FIRST-NAME]", "", $updated_content);
            $updated_content_plain = str_replace("[USER-FIRST-NAME]", "", $updated_content_plain);
        }            
        //<<<<<<<<< end added GAL

	    // Unsubscribe link, only if subscriber
		if ($recipients[$r]['unikey']) {
			$div_email = explode("@", $recipients[$r]['email']); // for link
		    
		    //$updated_content .= "<a href='".get_option ('home') . "/?page_id=". get_option('ALO_em_subsc_page');
		    //$updated_content .= "&amp;ac=unsubscribe&amp;em1=" .$div_email[0] . "&amp;em2=" .$div_email[1] . "&amp;uk=" .$recipients[$r]['unikey']."'>".__("click here", "alo-easymail") ."</a>.";            
		   	$arr_params = array ('ac' => 'unsubscribe', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $recipients[$r]['unikey'] );
			$uns_link = add_query_arg( $arr_params, get_page_link (get_option('ALO_em_subsc_page')) );
			
		    $updated_content .= "<p><em>". __("You have received this message because you subscribed to our newsletter. If you want to unsubscribe: ", "alo-easymail")." ";
			$updated_content .=	"<a href='" . $uns_link ."'>". __("click here", "alo-easymail") ."</a>.";
		    $updated_content .= "</em></p>";
		    
   		    $updated_content_plain .= "\n\n". __("You have received this message because you subscribed to our newsletter. If you want to unsubscribe: ", "alo-easymail")." ";
			$updated_content_plain .= __("visit this link", "alo-easymail");
		    $updated_content_plain .= "\n". $uns_link;
		    
		 	// TRACKING, if requested
			if ( $sending_info->tracking ) {
				switch ( $sending_info->tracking ) {
					case "ALO_EM": 	// default tracking: add a png image through a link to a php tracking page
						$updated_content .= "<img src='". ALO_EM_PLUGIN_URL ."/tr.php?n=".$sending_info->ID."&amp;e1=".$div_email[0]."&amp;e2=".$div_email[1]."&amp;k=".$recipients[$r]['unikey']."' width='1' height='1' border='0' >";
						break;
				}
			}
	    }
		
		$subject = stripslashes ( $sending_info->subject );

		// Generate a random boundary string
		$mime_boundary = "_boundary_";//'_x'.sha1(time()).'x';

		$headers_html  = "\n\n";
		$headers_html  .= "--$mime_boundary\n";
		$headers_html  .= "Content-Type: text/html; charset=" . get_option('blog_charset') . "\n";
		$headers_html  .= "Content-Transfer-Encoding: 8bit\n\n"; 

		$headers_plain = "\n";
		$headers_plain .= "--$mime_boundary\n";
		$headers_plain .= "Content-Type: text/plain; charset=" . get_option('blog_charset') . "\n";
		$headers_plain .= "Content-Transfer-Encoding: 8bit\n\n"; 
				
		// Initial headers
		$mail_sender = (get_option('ALO_em_sender_email')) ? get_option('ALO_em_sender_email') : "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
		$from_name = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
		$headers =  "MIME-Version: 1.0\n";
		$headers .= "From: ". $from_name ." <".$mail_sender.">\n";
		$headers .= "Content-Type: multipart/alternative; boundary=$mime_boundary\n\n" ; 
		
		// End
		$close_msg = "\n\n--$mime_boundary--"; 
		
		// Set up the full mail content
		$full_content = $headers_plain . $updated_content_plain . $headers_html . $updated_content . $close_msg;
		
        // ---- Send MAIL ----
        $mail_engine = @wp_mail($recipients[$r]['email'], $subject, $full_content, $headers );  
        
        if( $mail_engine && is_email($recipients[$r]['email']) ) {
            $recipients[$r]['result'] = 1;
        } else {
            $recipients[$r]['result'] = -1;
        }
        
        // add as sent
  		$n_recs ++;
  		
        // sent to all of this sending? or too much sending stop sending!
        if ( $n_recs == $tot_recs || $n_recs >= get_option('ALO_em_batchrate') ) break;
        
        // after each email it sleep a little: (x')/n°recipients 
        //$timesleep = max (floor ( ALO_EM_INTERVAL_MIN *60 / $tot_recs ), 1);
		//sleep($timesleep);
		//sleep(1);
    }
		
   	// check if batch completed
   	$has_finished = 1;
   	foreach ($recipients as $recipient) {
   		if ( !isset($recipient['result']) ) {
   			$has_finished = 0;
   			break;
   		}
   	}
   		
	// update sending info
	$wpdb->update("{$wpdb->prefix}easymail_sendings",
                  array( 'last_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'recipients' => serialize ($recipients), 'sent' => $has_finished ),
                  array( 'ID' => $sending_info->ID )
                 );
   	   	
}


/*************************************************************************
 * MAILING LISTS & RECIPIENTS FUNCTIONS
 *************************************************************************/ 


/**
 * Get all registered users of the blog 
 * return object with info as in table column
 */
function ALO_em_get_recipients_registered () {
	global $wpdb;
	return $wpdb->get_results( "SELECT ID AS UID, user_email FROM $wpdb->users" );     
}


/**
 * Get ALL subscribers OR only by SELECTED lists
 * @lists	array	only by selected lists 		
 * return object with info as in table column 
 */
function ALO_em_get_recipients_subscribers ( $lists=false ) {
	global $wpdb;
	$where_lists = "";
	if ( $lists && !is_array($lists) ) $lists = array ( $lists );
	if ( $lists ) {
		$where_lists .= " AND (";
		foreach ( $lists as $list ) {
			$where_lists .= "lists LIKE '%_".$list."_%' OR ";
		}
		$where_lists = substr( $where_lists , 0, -3); // cut last "OR"
		$where_lists .= ")";
	}
	return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE active='1' $where_lists" );
}


/**
 * Get the mailing lists (as array)
 * @types  str		list types requested (a string with comma: eg. 'hidden,admin,public')
 */
function ALO_em_get_mailinglists ( $types = false ) {
	$get = get_option('ALO_em_mailinglists');
	if ( $types == false ) {
		$types = array ( 'hidden', 'admin', 'public' ); // default types	
	} else {
		$types = explode (",", $types);
	}
 	if ( empty($get) ) {
		return false;
	} else {
		$mailinglists = maybe_unserialize($get);
		$mailinglists = ALO_em_msort ($mailinglists,'order', 'ASC');//($mailinglists,'order', false);
		foreach ( $mailinglists as $list => $val) { // don't return unrequested types
			if ( !in_array( $val['available'], $types ) ) unset ($mailinglists[$list]);
		}
		return (array)$mailinglists;
	}
}


/**
 * Save the mailing lists
 * @lists  array
 */
function ALO_em_save_mailinglists ( $lists ) {
	if ( !is_array ($lists) ) return false;
	$arraylists = maybe_serialize( $lists );
	update_option ( 'ALO_em_mailinglists', $arraylists );
	return true;
}


/**
 * Add a mailing list subscription to a subscriber (and save in db the new list)
 * @subscriber		
 * @list			
 */
function ALO_em_add_subscriber_to_list ( $subscriber, $list ) {
	global $wpdb;
	$user_lists = ALO_em_get_user_mailinglists ( $subscriber );
	if ( $user_lists && in_array($list, $user_lists) ) return; // if already, exit
	$user_lists[] = $list; // add the list
	asort ( $user_lists ); // order id from min to max, 1->9
	$updated_lists = implode ( "_", $user_lists );
	$updated_lists = "_".$updated_lists."_";
    return $wpdb->update( "{$wpdb->prefix}easymail_subscribers", array ( 'lists' => $updated_lists ), array ( 'ID' => $subscriber ) );
}


/**
 * Delete subscriber from mailing list
 * @subscriber		
 * @list		
 */
function ALO_em_delete_subscriber_from_list ( $subscriber, $list ) {
	global $wpdb;
	return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}easymail_subscribers SET lists = REPLACE(lists, '_%d_', '_') WHERE ID=%d", $list, $subscriber ) );
}


/**
 * Delete ALL subscribers from mailing list(s)
 * @lists	array of lists ID
 */
function ALO_em_delete_all_subscribers_from_lists ( $lists ) {
	global $wpdb;
	if ( !is_array($lists) ) $lists = array ( $lists );
	foreach ( $lists as $list ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}easymail_subscribers SET lists = REPLACE(lists, '_%d_', '_')", $list ) );
	}
	return true;
}


/**
 * Get the user mailing lists
 * @array_lists		array of lists ID
 */
function ALO_em_get_user_mailinglists ( $subscr_id ) {
	global $wpdb;
	$lists = $wpdb->get_var ( $wpdb->prepare( "SELECT lists FROM {$wpdb->prefix}easymail_subscribers WHERE ID = %d", $subscr_id ) );
	if ( $lists	) {
		$array_lists = explode ( "_", trim ($lists, "_" ) );
		if ( is_array($array_lists) && $array_lists[0] != false  ) {
			asort ( $array_lists ); // order id from min to max, 1->9
			return (array)$array_lists;
		} else {
			return false;
		}		
	} else {
		return false;
	}
}


/**
 * Creates a html table with checkbox lists to edit own subscription
 * @user_email		str		subscriber email
 * @cssclass		str		the class css for the html table
 */
function ALO_em_html_mailinglists_table_to_edit ( $user_email, $cssclass="" ) {
	$html = "";
	$lists_msg 	= (get_option('ALO_em_lists_msg') !="")? get_option('ALO_em_lists_msg') : __("You can also sign up for specific lists", "alo-easymail");  
    $mailinglists = ALO_em_get_mailinglists( 'public' );
    if ( $mailinglists ) {
	    $subscriber_id = ALO_em_is_subscriber( $user_email );
	    $user_lists = ALO_em_get_user_mailinglists ( $subscriber_id );
		$html .= "<table ". (($cssclass!="")? " class='$cssclass' " : "") ."><tbody>\n"; 
		$html .= "<tr><th ". (($cssclass=="")? " style='width:50%' ":"") .">". $lists_msg	.":</th>\n";
		$html .= "<td>\n";
		foreach ( $mailinglists as $list => $val ) {
			$checked = ( $user_lists && in_array ( $list, $user_lists )) ? "checked='checked'" : "";
			$html .= "<input type='checkbox' name='alo_em_profile_lists[]' id='alo_em_profile_list_$list' value='$list' $checked /> " . $val["name"] ."<br />\n";
		}
		$html .= "</td></tr>\n";
		$html .= "</tbody></table>\n";
	} 
	return $html;
}



/*************************************************************************
 * TRACKING FUNCTIONS
 *************************************************************************/ 

/**
 * If recipient view has already tracked (eg. if he has opened the newsletter)
 * @type	str 	the type of tracking, now available: 'V' = when newsletter is openend and viewed
 * return ID tracking, otherwise false
 */
function ALO_em_recipient_is_tracked ( $email, $newsletter, $type='V' ) {
	global $wpdb;
	$check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_trackings WHERE email='%s' AND newsletter=%d AND type=%s LIMIT 1", $email, $newsletter, $type ) );
	return $check;
}


/**
 * insert a new tracking in db
 */
function ALO_em_add_tracking ( $email, $newsletter, $type='V' ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}easymail_trackings ( newsletter, email, type ) VALUES ( %d, %s, %s )", array( $newsletter, $email, $type ) ) );
}


/*************************************************************************
 * TEMPLATES
 *************************************************************************/ 

/**
 * Add a new template
 */
function ALO_em_add_new_template ( $user_ID, $subject, $content ) {
	global $wpdb;
	$add_newsletter = $wpdb->insert(
                "{$wpdb->prefix}easymail_sendings", 
                array( 'start_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'last_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'user' => $user_ID, 'subject' => $subject, 
                'content' => $content, 'sent' => '9', 'recipients' => '', 'tracking' => '' )
            );
    return $add_newsletter;
}


/**
 * Update an existing template
 */
function ALO_em_update_template ( $tpl_id, $user_ID, $subject, $content ) {
	global $wpdb;
    $update = $wpdb->update(    "{$wpdb->prefix}easymail_sendings",
                            array ( 'last_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'subject' => $subject, 'content' => $content ),
                            array ( 'ID' => $tpl_id )
                        );
    return $update;
}


/**
 * Count how many templates of an user
 */
function ALO_em_how_user_templates ( $user_ID ) {
	global $wpdb;
	$tpls = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->prefix}easymail_sendings WHERE sent='9' AND user='%d'", $user_ID ) );
    return (int) $tpls;
}

?>
