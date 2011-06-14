<?php

/*************************************************************************
 * MISC UTILITIES FUNCTIONS
 *************************************************************************/ 

/**
 * Add help image with tooltip
 */
function alo_em_help_tooltip ( $text ) {
	$text = str_replace( array("'", '"'), "", $text );
	$html = "<img src='".ALO_EM_PLUGIN_URL."/images/12-help.png' title='". esc_attr($text) ."' style='cursor:help;vertical-align:middle;margin-left:3px' alt='(?)' />";
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

function alo_em_msort  ($array, $key, $order = "ASC") {
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
function alo_em_html2plain ( $text ) {
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


/**
 * Show credit and banners
 *@param	bol		only donate (false) or all banners (true)
 */
function alo_em_show_credit_banners ( $all=false ) { 
	if ( get_option('alo_em_show_credit_banners') == "no" ) return; ?>
	<style type="text/css">
		.alo-banner { border:1px solid #ccc; background-color: #efefef; width:300px; height: 130px; padding: 6px; margin-right: 15px; float: left }
		.alo-banner p { font-size: 0.9em; margin: 0.5em 0 }
	</style>
	<ul style="width:100%; margin-top:20px">
		<li class="alo-banner">
			<p><em><?php _e("Please visit the plugin site for more info and feedback", "alo-easymail") ?>.
			<?php if ( function_exists('add_contextual_help') ) : ?>
			<?php _e("For more links you can use the Help button", "alo-easymail") ?>.
			<?php endif; ?>
			<br /><a href='http://www.eventualo.net/blog/wp-alo-easymail-newsletter/' target='_blank'>www.eventualo.net</a>
			</em></p>
			
			<p><em><?php _e("If you use this plugin consider the idea of donating and supporting its development", "alo-easymail") ?>:</em></p><form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline'>
			<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
			<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='Donate via PayPal' title='Donate via PayPal' border='0' type='image'>
			<img src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'><br>	</form>
		</li>
		<?php if ( $all ) : ?>
		<li class="alo-banner">
			<a href="https://www.e-junkie.com/ecom/gb.php?cl=136641&c=ib&aff=152531" title="Original WP Themes by ThemeFuse"> <img border="0" src="http://themefuse.com/banners/125x125.jpg" alt="Original WP by ThemeFuse" width="125" height="125" style="float:right;margin-left:10px" /></a>		
			<p><em>If you are interested in buying an original wp theme I would recommend <a href="https://www.e-junkie.com/ecom/gb.php?cl=136641&c=ib&aff=152531" title="Original WP Themes by ThemeFuse">ThemeFuse</a>.</em></p>
		</li>		
		<?php endif; ?>
	</ul>
<?php
}


/*************************************************************************
 * NEWSLETTER FUNCTIONS
 *************************************************************************/ 


/**
 * User can edit Newsletter
 */
function alo_em_user_can_edit_newsletter ( $newsletter, $user_id=false ) {
	global $user_ID;
	if ( empty( $user_id ) ) $user_id = $user_ID;
	//return get_edit_post_link( $newsletter );
	//return user_can( $user_id, 'edit_post', $newsletter ); // TODO user_can c'è solo dalla 3.1
	//return current_user_can( 'edit_post', $newsletter );	
	$user = new WP_User( $user_id );
	return $user->has_cap( 'edit_post', $newsletter );
}


/**
 * Get Newsletter by id
 */
function alo_em_get_newsletter ( $newsletter ) {
	return get_post ( $newsletter );
}


/**
 * Get Newsletter Status from post meta
 */
function alo_em_get_newsletter_status ( $newsletter ) {
	return get_post_meta( $newsletter, '_easymail_status', true );
}


/**
 * Update the Newsletter Status
 *@param	int	
 *@param	str
 */
function alo_em_edit_newsletter_status ( $newsletter, $status ) {
	delete_post_meta ( $newsletter, "_easymail_status" );
	add_post_meta ( $newsletter, "_easymail_status", $status );
}


/**
 * Reset/delete the Newsletter Status
 */
function alo_em_delete_newsletter_status ( $newsletter ) {
	delete_post_meta ( $newsletter, "_easymail_status" );
}



/*************************************************************************
 * RECIPIENTS FUNCTIONS
 *************************************************************************/ 


/**
 * Get Newsletter Recipients from post meta
 */
function alo_em_get_recipients_from_meta ( $post_id ) {
	$recipients = get_post_meta ( $post_id, "_easymail_recipients" );
	return ( !empty( $recipients[0] ) ) ? $recipients[0] : false;
}


/**
 * Get the Recipients from meta
 * @return	arr		email as values	 
 */
function alo_em_get_all_recipients_from_meta ( $newsletter ) {
	$recipients = alo_em_get_recipients_from_meta ( $newsletter );
	$registered = $subscribers = $subscribers_from_list = false;
	
	$count = array();
		
	if ( isset( $recipients['registered'] ) )  {
		$registered = alo_em_get_recipients_registered();
		if ( $registered ) : foreach ( $registered as $reg ) :
			if ( !in_array( $reg->user_email, $count ) )  array_push( $count, $reg->user_email );
		endforeach; endif;
	}
	if ( isset( $recipients['subscribers'] ) && isset( $recipients['lang'] ) )  {
		$subscribers = alo_em_get_recipients_subscribers();
		if ( $subscribers ) : foreach ( $subscribers as $sub ) :
			$sub_lang = ( !empty( $sub->lang ) ) ? $sub->lang : "UNKNOWN";
			//if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;	
			if ( $sub_lang == "UNKNOWN" || !in_array( $sub_lang, alo_em_get_all_languages() ) ) { // unknown or not installed lang
				if ( !in_array( "UNKNOWN", $recipients['lang'] ) ) continue;
			} else { // installed lang 
				if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;
			}
			if ( !in_array( $sub->email, $count ) )  array_push( $count, $sub->email );
		endforeach; endif;		
	} else if ( isset( $recipients['list'] ) && isset( $recipients['lang'] ) ) {
		$subscribers_from_list = alo_em_get_recipients_subscribers( $recipients['list'] );
		if ( $subscribers_from_list ) : foreach ( $subscribers_from_list as $sub ) :
			$sub_lang = ( !empty( $sub->lang ) ) ? $sub->lang : "UNKNOWN";
			//if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;
			if ( $sub_lang == "UNKNOWN" || !in_array( $sub_lang, alo_em_get_all_languages() ) ) { // unknown or not installed lang
				if ( !in_array( "UNKNOWN", $recipients['lang'] ) ) continue;
			} else { // installed lang 
				if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;
			}
			if ( !in_array( $sub->email, $count ) )  array_push( $count, $sub->email );
		endforeach; endif;		
	}
	return $count;
}


/**
 * Count the Recipients from meta
 * @return	int	 
 */
function alo_em_count_recipients_from_meta ( $newsletter ) {
	return count( alo_em_get_all_recipients_from_meta ( $newsletter ) );
}


/**
 * A short summary of Recipients
 * @param	arr	 
 */
function alo_em_recipients_short_summary ( $recipients ) {
	$output = "<ul>";
	if ( isset( $recipients['registered'] ) ) $output .= "<li>" . __( 'All registered users', "alo-easymail") . "</li>";
	if ( isset( $recipients['subscribers'] ) ) {
		$output .= "<li>" . __( 'All subscribers', "alo-easymail") . "</li>";
	} else {
		if ( isset( $recipients['list'] ) ) $output .= "<li>" . count( $recipients['list'] ) ." ". __( 'Mailing Lists', "alo-easymail") . "</li>";
	}
	if ( isset( $recipients['subscribers'] ) || isset( $recipients['list'] ) ) {
		if ( isset( $recipients['lang'] ) ) $output .= "<li>" . count( $recipients['lang'] ) ." ". __( 'Languages', "alo-easymail") . "</li>";
	}
	$output .= "</ul>";
	return $output;
}


/**
 * Create the Recipients cache for Newsletter
 * @return	arr	 	array value are 0
 */
function alo_em_create_cache_recipients ( $newsletter ) {
	$recipients = alo_em_get_recipients_from_meta ( $newsletter );
	//echo "<pre>". print_r ( $recipients, true ). "</pre>";
	$cache = array();
	if ( isset( $recipients['registered'] ) && $recipients['registered'] == 1 ) $cache['registered'] = "0";
	if ( isset( $recipients['subscribers'] ) && $recipients['subscribers'] == 1 ) {
		$cache['subscribers'] = "0";
	} else {
		if ( isset( $recipients['list'] ) ) {
			$cache['list'] = array();
			foreach ( $recipients['list'] as $index => $id ) {
				$cache['list'][$id] = "0";
			}
		}
	}
	if ( isset( $recipients['lang'] ) ) $cache['lang'] = $recipients['lang'];
	
	delete_post_meta ( $newsletter, "_easymail_cache_recipients" );
	add_post_meta ( $newsletter, "_easymail_cache_recipients", $cache );
}


/**
 * Get the Recipients cache for Newsletter
 * @return	arr	 
 */
function alo_em_get_cache_recipients ( $newsletter ) {
	$recipients = get_post_meta ( $newsletter, "_easymail_cache_recipients" );
	return ( !empty( $recipients[0] ) ) ? $recipients[0] : false;
}


/**
 * Save the Recipients cache for Newsletter 
 */
function alo_em_save_cache_recipients ( $newsletter, $recipients ) {
	delete_post_meta ( $newsletter, "_easymail_cache_recipients" );
	add_post_meta ( $newsletter, "_easymail_cache_recipients", $recipients );
}


/**
 * Delete the Recipients cache for Newsletter 
 */
function alo_em_delete_cache_recipients ( $newsletter ) {
	delete_post_meta ( $newsletter, "_easymail_cache_recipients" );
}


/**
 * Get the Recipients cache for Newsletter
 * @param	int		limit: how many	 
 * @param	bol		if send now or add to queue
 */
function alo_em_add_recipients_from_cache_to_db ( $newsletter, $limit=10, $sendnow=false ) {
	$cache = alo_em_get_cache_recipients( $newsletter );
	//echo "CACHE BEFORE\n<pre>". print_r ( $cache, true ). "</pre>"; // DEBUG
	if ( $cache && is_array( $cache ) ) {
		//$recipients = array();
		
		$start = 0;
		$now_doing = false;
		$finished = false;
		
		// Get the 1st required group
		if ( isset( $cache['registered'] ) )  {
			$recipients = alo_em_get_recipients_registered();
			$now_doing = "registered";
			$start = $cache['registered'];
		}
		if ( isset( $cache['subscribers'] ) && !$now_doing )  {
			$recipients = alo_em_get_recipients_subscribers();
			$now_doing = "subscribers";
			$start = $cache['subscribers'];
		} else if ( isset( $cache['list'] ) && !$now_doing ) {
			$lists = array();
			foreach ( $cache['list'] as $id => $list_start ) {
				$recipients = alo_em_get_recipients_subscribers( $id );
				$now_doing = "list";
				$now_doing_list = $id;
				$start = $list_start;
				break; // the 1st list
			}
			//$recipients = alo_em_get_recipients_subscribers( $lists );
		}
		
		// If not registered round, check languages
		if ( $now_doing && $now_doing != "registered" && isset ( $cache['lang'] ) && is_array( $cache['lang'] ) ) {
			foreach ( $recipients as $index => $rec ) {			
				/*
				$search_lang = ( !empty( $rec->lang ) ) ? $rec->lang : "UNKNOWN"; // if subscriber has not specified lang
				if ( !in_array( $search_lang, $cache['lang'] ) ) unset ( $recipients[$index] );
				*/
				
				$rec_lang = ( !empty( $rec->lang ) ) ? $rec->lang : "UNKNOWN"; // if subscriber has not specified lang
				if ( $rec_lang == "UNKNOWN" || !in_array( $rec_lang, alo_em_get_all_languages() ) ) { // unknown or not installed lang
					if ( !in_array( "UNKNOWN", $cache['lang'] ) ) unset ( $recipients[$index] );
				} else { // installed lang 
					if ( !in_array( $rec_lang, $cache['lang'] ) ) unset ( $recipients[$index] );
				}
				
			}
		}
		
		//echo "RECIPIENTS\n<pre>". print_r ( $recipients, true ). "</pre>"; // DEBUG
		
		if ( $now_doing && $recipients ) {
		
			$added = 0; // to count how many added in this round
			
			end( $recipients );
			$end = key ( $recipients ); // the last index in recipients
			reset( $recipients );
			
			for ( $i = $start; $i <= $end; $i ++ ) {
				
				if ( $i == $end ) $finished = $now_doing; // // if end reached, group finished
						
				if ( !isset( $recipients[$i] ) ) {
					// if ( $i == count( $recipients )-1 ) break; else continue;
					continue;
				}
				
				$email = ( $now_doing == "registered" ) ? $recipients[$i]->user_email : $recipients[$i]->email;
				if ( alo_em_get_recipient_by_email_and_newsletter( $email, $newsletter ) ) continue; // if already added, skip
				$args = array( 
					'newsletter' => $newsletter,
					'email' => $email
				);
				$new_id = alo_em_add_recipient( $args, true );
				if ( $new_id ) {
					$added ++;
					
					if ( $sendnow ) { // send only one mail (and wait the the request sleep) and exit
						/*
						$recipient = alo_em_get_subscriber( $email );
						$recipient->subscriber = $recipient->ID;
						$recipient->ID = $new_id;
						$recipient->newsletter = $newsletter;
						*/
						//$recipient = (object) array ( 'newsletter' => $newsletter, 'email' => $email, 'ID' => $new_id );
						$recipient = alo_em_get_recipient_by_id( $new_id );
						alo_em_send_newsletter_to ( $recipient );
						
						if ( alo_em_get_sleepvalue() > 0 ) usleep ( alo_em_get_sleepvalue() * 1000 ); 
						break;
					}
						
					if ( $added >= $limit ) { // if limit reached, exit
						break;
					}										
				} 
				
				// Update the offset for next
				switch ( $now_doing ) {
					case "registered":		$cache['registered'] 	= $i; 			break;
					case "subscribers":		$cache['subscribers'] 	= $i; 			break;
					case "list":  			$cache['list'][$now_doing_list] = $i; 	break;
				}
				
				//echo "NOW $i\n<pre>". print_r ( $cache, true ). "</pre>"; // DEBUG
			}						
		}
				
		// If group finished, delete it from cache
		if ( $finished ) : switch ( $finished ) :
			case "registered":		unset ( $cache['registered'] ); 			break;
			case "subscribers":		unset ( $cache['subscribers'] ); 			break;
			case "list":  			unset ( $cache['list'][$now_doing_list] ); 	break;
		endswitch; endif;
		
		//echo "NEW CACHE $i\n<pre>". print_r ( $cache, true ). "</pre>"; // DEBUG
		
		// If completed ALL groups, delete cache and mark newsletter as "sendable"
		if ( !isset( $cache['registered'] ) && !isset( $cache['subscribers'] ) && empty( $cache['list'] ) ) {
			if ( count( alo_em_get_recipients_in_queue( 1, $newsletter ) ) == 0 && $sendnow ) {
				alo_em_set_newsletter_as_completed ( $newsletter );
			} else {
				alo_em_edit_newsletter_status ( $newsletter, 'sendable' );
			}
			alo_em_delete_cache_recipients( $newsletter );
		} else {
			alo_em_save_cache_recipients ( $newsletter, $cache );
		}		
	}
}


/**
 * Get single Recipient by email and newsletter
 */
function alo_em_get_recipient_by_email_and_newsletter ( $email, $newsletter ) {
    global $wpdb;
    //return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_recipients WHERE email=%s AND newsletter=%d", $email, $newsletter ) );
    
    $rec = $wpdb->get_row( $wpdb->prepare( "SELECT r.*, s.lang, s.unikey, s.name, s.ID AS subscriber FROM {$wpdb->prefix}easymail_recipients AS r 
    										LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email 
    										WHERE r.email=%s AND r.newsletter=%d", $email, $newsletter ) );	
    if ( $rec ) {
    	if ( $user_id = email_exists( $email ) ) {
    		if ( get_user_meta( $user_id, 'first_name', true ) != "" ) $rec->firstname = ucfirst( get_user_meta( $user_id, 'first_name', true ) );
	 	} else {
	 		$rec->firstname = $rec->name;
	 	}	
    }
    return $rec;    										
}


/**
 * Get single Recipient by ID
 */
function alo_em_get_recipient_by_id ( $recipient ) {
    global $wpdb;
    $rec = $wpdb->get_row( $wpdb->prepare( "SELECT r.*, s.lang, s.unikey, s.name, s.ID AS subscriber FROM {$wpdb->prefix}easymail_recipients AS r 
    										LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email 
    										WHERE r.ID=%d", $recipient ) );
    if ( $rec && isset( $rec->email ) ) {
    	if ( $user_id = email_exists( $rec->email ) ) {
			if ( get_user_meta( $user_id, 'first_name', true ) != "" ) $rec->firstname = ucfirst( get_user_meta( $user_id, 'first_name', true ) );
	 	} else {
	 		$rec->firstname = $rec->name;
	 	}	
    }
    return $rec;
}


/**
 * Add Recipient by email and newsletter
 *@param 	arr			recipient info: email. newsletter...
 *@param 	bol			add only if subscriber is active
 *@return 	int|bol		id added of false
 */
function alo_em_add_recipient ( $args, $only_if_active=true ) {
    global $wpdb;
    $defaults = array(
		'email' => false,
		'newsletter' => false,
		'result' => '0'
	);
	$fields = wp_parse_args( $args, $defaults );
	$added = false;
	if ( $fields['email'] && $fields['newsletter'] ) {
		if ( !$only_if_active || ( $only_if_active && ( alo_em_check_subscriber_state( $fields['email'] ) == 1 /*|| email_exists( $fields['email'] )*/ ) ) ) {
			$wpdb->insert ( "{$wpdb->prefix}easymail_recipients", $fields );
			$added = $wpdb->insert_id;
		}
	}
	return $added;
}


/**
 * Delete Newsletter Recipients
 */
function alo_em_delete_newsletter_recipients ( $newsletter ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d", $newsletter ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d", $newsletter ) );
}


/**
 * Get Newsletter Recipients from db
 * @param	bol 	if only recipients that have NOT yet received the newsletter
 */
function alo_em_get_newsletter_recipients ( $newsletter, $only_to_send=false ) {
	global $wpdb;
	$where_to_send = ( $only_to_send ) ? "AND r.result = 0" : "";
	return $wpdb->get_results( $wpdb->prepare( "SELECT r.*, s.lang, s.unikey, s.name FROM {$wpdb->prefix}easymail_recipients AS r LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email WHERE newsletter=%d ". $where_to_send ." ORDER BY r.email ASC", $newsletter ) );
}


/**
 * Count the Newsletter Recipients from db
 * @return	int	 
 */
function alo_em_count_newsletter_recipients ( $newsletter, $only_to_send=false ) {
	return count( alo_em_get_newsletter_recipients ( $newsletter, $only_to_send ) );
}


/**
 * Count the Newsletter Recipients from db already sent
 * @return	int	 
 */
function alo_em_count_newsletter_recipients_already_sent ( $newsletter ) {
	global $wpdb;
	$sent = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d AND result != 0 ", $newsletter ) );
	return count( $sent );
}


/**
 * Count the Newsletter Recipients from db already sent with Success
 * @return	int	 
 */
function alo_em_count_newsletter_recipients_already_sent_with_success ( $newsletter ) {
	global $wpdb;
	$sent = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d AND result = '1' ", $newsletter ) );
	return count( $sent );
}


/**
 * Count the Newsletter Recipients from db already sent with Error
 * @return	int	 
 */
function alo_em_count_newsletter_recipients_already_sent_with_error ( $newsletter ) {
	global $wpdb;
	$sent = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d AND result = '-1' ", $newsletter ) );
	return count( $sent );
}


/**
 * Count the Newsletter Recipients from db already sent
 * @return	int	 
 */
function alo_em_newsletter_recipients_percentuage_already_sent ( $newsletter ) {
	$sent = alo_em_count_newsletter_recipients_already_sent ( $newsletter );
	$total = alo_em_count_newsletter_recipients ( $newsletter );
	$perc = ( $sent > 0 && $total > 0 ) ? number_format ( ( $sent * 100 / $total ), 1 ) : 0;
	return $perc;
}



/*************************************************************************
 * SUBSCRIPTION FUNCTIONS
 *************************************************************************/ 


/**
 * Count the n° of subscribers
 * return a array: total (active + not active), active, not active
 */
function alo_em_count_subscribers () {
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
function alo_em_is_subscriber($email) {
    global $wpdb;
    $is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return (($is_subscriber)? $is_subscriber : 0); // ID in db tab subscribers
} 


/**
 * Check is there is a subscriber with this ID and return true/false
 */
function alo_em_is_subscriber_by_id ( $id ) {
    global $wpdb;
    $is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id) );
    return $is_subscriber;
} 


/**
 * Check the state of a subscriber (active/not-active)
 */
function alo_em_check_subscriber_state($email) {
    global $wpdb;
    $is_activated = $wpdb->get_var( $wpdb->prepare("SELECT active FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return $is_activated;
} 


/**
 * Modify the state of a subscriber (active/not-active) (BY ADMIN)
 */
function alo_em_edit_subscriber_state_by_id($id, $newstate) {
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
function alo_em_edit_subscriber_state_by_email($email, $newstate="1", $unikey) {
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
function alo_em_add_subscriber($email, $name, $newstate=0, $lang="" ) {
    global $wpdb;
 	$output = true;
    // if there is NOT a subscriber with this email address: add new subscriber and send activation email
    if (alo_em_is_subscriber($email) == false){
        $unikey = substr(md5(uniqid(rand(), true)), 0,24);    // a personal key to manage the subscription
           
        // try to send activation mail, otherwise will not add subscriber
        if ($newstate == 0) {
        	$lang_actmail = ( !empty( $lang ) ) ? $lang : alo_em_short_langcode ( get_locale() );
           	if ( !alo_em_send_activation_email($email, $name, $unikey, $lang_actmail) ) $output = false; // DEBUG ON LOCALHOST: comment this line to avoid error on sending mail
        }
        
        if ( $output ) {	
			$wpdb->insert ( "{$wpdb->prefix}easymail_subscribers",
           					array( 'email' => $email, 'name' => $name, 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'active' => $newstate, 'unikey' => $unikey, 'lists' => "|", 'lang' => $lang )
			);
        	$output = "OK"; //return true; 
        }
        
    } else {
        // if there is ALREADY a subscriber with this email address, and if is NOT confirmed yet: re-send an activation email
        if ( alo_em_check_subscriber_state($email) == 0) {
            // retrieve existing unique key 
            $exist_unikey = $wpdb->get_var( $wpdb->prepare("SELECT unikey FROM {$wpdb->prefix}easymail_subscribers WHERE ID='%d' LIMIT 1", alo_em_is_subscriber($email) ) );
            
            if ( alo_em_send_activation_email($email, $name, $exist_unikey, $lang) ) {
                // update join date to today
                $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                            array ( 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'lang' => $lang ),
                                            array ( 'ID' => alo_em_is_subscriber($email) )
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
function alo_em_delete_subscriber_by_id($id) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id ) );
    return $output;
} 



/**
 * Update a subscriber (BY ADMIN/REGISTERED-USER)
 */
function alo_em_update_subscriber_by_email ( $old_email, $new_email, $name, $newstate=0, $lang="" ) {
    global $wpdb;
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                array ( 'email' => $new_email, 'name' => $name, 'active' => $newstate, 'lang' => $lang ),
                                array ( 'email' => $old_email )
                            );
  	return $output;
} 


/**
 * Delete a subscriber (BY SUBSCRIBER)
 */
function alo_em_delete_subscriber_by_email($email, $unikey) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey ) );
    return $output;
} 


/**
 * Check if can access subscription page (BY SUBSCRIBER)
 */
function alo_em_can_access_subscrpage ($email, $unikey) {
    global $wpdb;
    // check if email and unikey match
    $check = alo_em_check_subscriber_email_and_unikey ( $email, $unikey );
    return $check;
} 


/**
 * Check if subscriber email and unikey match (BY SUBSCRIBER) (check EMAIL<->UNIKEY)
 */
function alo_em_check_subscriber_email_and_unikey ( $email, $unikey ) {
    global $wpdb;
    $check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey) );
    return $check;
} 


/**
 * Send email with activation link
 */
function alo_em_send_activation_email($email, $name, $unikey, $lang) {
	$blogname = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
    // Headers
    $mail_sender = "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
    $headers =  "";//"MIME-Version: 1.0\n";
    $headers .= "From: ". $blogname ." <".$mail_sender.">\n";
    $headers .= "Content-Type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
    
    /*
    // Subject
    // $subject = sprintf(__("Confirm your subscription to %s Newsletter", "alo-easymail"), $blogname );
   	$subject = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_subj', true ); 
   	$subject = str_replace ( "%BLOGNAME%", $blogname, $subject );
    */
       	
    // Main content    
    /*
 	$div_email = explode("@", $email); // for link
    $arr_params = array ('ac' => 'activate', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $unikey, 'lang' => $lang);
	$sub_link = add_query_arg( $arr_params, get_page_link (get_option('alo_em_subsc_page')) );
	//$sub_link = alo_em_translate_url ( $sub_link, $lang );
    */
    /*   
   	$content = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_mail', true ); 
   	$content = str_replace ( "%BLOGNAME%", $blogname, $content );
   	$content = str_replace ( "%NAME%", $name, $content );
   	$content = str_replace ( "%ACTIVATIONLINK%", $sub_link, $content );
   	*/
   	
   	$content = "lang=$lang&email=$email&name=$name&unikey=$unikey";
   	//$content = "email=$email";
   
    //echo "<br />".$headers."<br />".$subscriber->email."<br />". $subject."<br />".  $content ."<hr />" ; // DEBUG
    $sending = wp_mail( $email, /*$subject*/ "#_EASYMAIL_ACTIVATION_#", $content, $headers);  
    return $sending;
} 


/**
 * Print table with tags summay
 */
function alo_em_tags_table ( $post_id ) { 
	$placeholders = array (
		"easymail_post" => array (
			"title" 		=> __( "Post tags", "alo-easymail" ),
			"tags" 			=> array (
				"[POST-TITLE]" 		=> __("The link to the title of the selected post.", "alo-easymail") .". ". __("This tag works also in the <strong>subject</strong>", "alo-easymail"),
				"[POST-EXCERPT]" 	=> __("The excerpt (if any) of the post.", "alo-easymail"),
				"[POST-CONTENT]"	=> __("The main content of the post.", "alo-easymail")
			)
		),
		"easymail_subscriber" => array (
			"title" 		=> __( "Subscriber tags", "alo-easymail" ),
			"tags" 			=> array (
				"[USER-NAME]"		=> __("Name and surname of registered user.", "alo-easymail") ." (". __("For subscribers: the name used for registration", "alo-easymail") .")",
				"[USER-FIRST-NAME]"	=> __("First name of registered user.", "alo-easymail") ." (". __("For subscribers: the name used for registration", "alo-easymail") .")"
			)
		),
		"easymail_misc" => array (
			"title" 		=> __( "Other tags", "alo-easymail" ),
			"tags" 			=> array (
				"[SITE-LINK]"		=> __("The link to the site", "alo-easymail")
			)
		)				
	);
	
	$placeholders = apply_filters ( 'alo_easymail_newsletter_placeholders_table', $placeholders ); 
	
	if ( $placeholders ) :
		foreach ( $placeholders as $type => $placeholder ) : ?>
		
		<table class="widefat" style="margin-top:10px">
		<thead><tr><th scope="col" style="width:20%"><?php esc_html_e ( $placeholder['title'] ) ?></th>
		<th scope="col"><?php do_action ( 'alo_easymail_newsletter_placeholders_title_'.$type, $post_id ); ?></th></tr>
		</thead>
		<tbody>
		
			<?php if ( !empty( $placeholder['tags'] ) ) : foreach ( $placeholder['tags'] as $tag => $desc ) : ?>
				<tr><td><?php esc_html_e ( $tag ) ?></td><td style='font-size:80%'>
				<span class="description"><?php echo $desc ?></span></td></tr>
			<?php endforeach; endif; // $placeholder['tags'] ?>
			
		</tbody></table>
		<?php endforeach; // $placeholders
		
	endif; // if ( $placeholders ) ?>
	
<?php 
}
 


/*************************************************************************
 * AJAX 'SACK' FUNCTION
 *************************************************************************/ 

add_action('wp_head', 'alo_em_ajax_js' );


function alo_em_ajax_js()
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
  
   var alo_em_sack = new sack( 
       "<?php echo admin_url() ?>admin-ajax.php" );       

  alo_em_sack.execute = 1;
  alo_em_sack.method = 'POST';
  alo_em_sack.setVar( "action", "alo_em_user_form_check" );
  alo_em_sack.setVar( "alo_easymail_option", opt );
  <?php 
  $txt_ok 		= esc_attr( alo_em___(__("Successfully updated", "alo-easymail")) );	
  $lang_code 	= alo_em_get_language( true );
  ?>
  alo_em_sack.setVar( "alo_easymail_txt_success", '<?php echo $txt_ok ?>' );
  alo_em_sack.setVar( "alo_easymail_lang_code", '<?php echo $lang_code ?>' );
  
  var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');
  var length = cbs.length;
  var lists = "";
  for (var i=0; i < length; i++) {
  	if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') {
  		if ( cbs[i].checked ) lists += cbs[i].value + ",";
  	}
  }
  alo_em_sack.setVar( "alo_em_form_lists", lists );
  alo_em_sack.onError = function() { alert('Ajax error' )};
  alo_em_sack.runAJAX();

  return true;

} 
<?php } else {  // if not is_user_logged_in() ?>
function alo_em_pubblic_form ()
{
  <?php
  $error_email_incorrect 	= esc_attr( alo_em___(__("The e-email address is not correct", "alo-easymail")) );
  $error_name_empty 		= esc_attr( alo_em___(__("The name field is empty", "alo-easymail")) );
  $error_email_added		= esc_attr( alo_em___(__("Warning: this email address has already been subscribed, but not activated. We are now sending another activation email", "alo-easymail")) );
  $error_email_activated	= esc_attr( alo_em___(__("Warning: this email address has already been subscribed", "alo-easymail")) );  
  $error_on_sending			= esc_attr( alo_em___(__("Error during sending: please try again", "alo-easymail")) );
  if ( get_option('alo_em_no_activation_mail') != "yes" ) {
			$txt_ok			= esc_attr( alo_em___(__("Subscription successful. You will receive an e-mail with a link. You have to click on the link to activate your subscription.", "alo-easymail")) );  
  } else {
			$txt_ok			= esc_attr( alo_em___(__("Your subscription was successfully activated. You will receive the next newsletter. Thank you.", "alo-easymail")) );    
  }
  $txt_subscribe			= esc_attr( alo_em___(__("Subscribe", "alo-easymail")) );
  $txt_sending				= esc_attr( alo_em___(__("sending...", "alo-easymail")) );
  $lang_code				= alo_em_get_language( true );
  ?>
  document.alo_easymail_widget_form.submit.value="<?php echo $txt_sending ?>";
  document.alo_easymail_widget_form.submit.disabled = true;
  document.getElementById('alo_em_widget_loading').style.display = "inline";
  document.getElementById('alo_easymail_widget_feedback').innerHTML = "";
  
   var alo_em_sack = new sack( 
       "<?php echo admin_url() ?>admin-ajax.php" );    

  alo_em_sack.execute = 1;
  alo_em_sack.method = 'POST';
  alo_em_sack.setVar( "action", "alo_em_pubblic_form_check" );
  alo_em_sack.setVar( "alo_em_opt_name", document.alo_easymail_widget_form.alo_em_opt_name.value );
  alo_em_sack.setVar( "alo_em_opt_email", document.alo_easymail_widget_form.alo_em_opt_email.value );
  
  alo_em_sack.setVar( "alo_em_error_email_incorrect", "<?php echo $error_email_incorrect ?>");
  alo_em_sack.setVar( "alo_em_error_name_empty", "<?php echo $error_name_empty ?>");
  alo_em_sack.setVar( "alo_em_error_email_added", "<?php echo $error_email_added ?>");
  alo_em_sack.setVar( "alo_em_error_email_activated", "<?php echo $error_email_activated ?>");
  alo_em_sack.setVar( "alo_em_error_on_sending", "<?php echo $error_on_sending ?>");
  alo_em_sack.setVar( "alo_em_txt_ok", "<?php echo $txt_ok ?>");
  alo_em_sack.setVar( "alo_em_txt_subscribe", "<?php echo $txt_subscribe ?>");
  alo_em_sack.setVar( "alo_em_lang_code", "<?php echo $lang_code ?>");  
  
  var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');
  var length = cbs.length;
  var lists = "";
  for (var i=0; i < length; i++) {
  	if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') {
  		if ( cbs[i].checked ) lists += cbs[i].value + ",";
  	}
  }
  alo_em_sack.setVar( "alo_em_form_lists", lists );
  alo_em_sack.onError = function() { alert('Ajax error' )};
  alo_em_sack.runAJAX();

  return true;

} 
//]]>
<?php } // end if is_user_logged_in() ?>
</script>
<?php
} // end alo_em_ajax_js

add_action('wp_ajax_alo_em_user_form_check', 'alo_em_user_form_callback');				// logged in
add_action('wp_ajax_nopriv_alo_em_pubblic_form_check', 'alo_em_pubblic_form_callback'); // pubblic, no logged in

// For logged-in users
function alo_em_user_form_callback() {
	global $wpdb, $user_ID, $user_email, $current_user;
	get_currentuserinfo();
	//die ("alert(\"".$_POST['alo_easymail_option']."\")");
   	if ( $user_ID && isset($_POST['alo_easymail_option'])) {
   		switch ( $_POST['alo_easymail_option'] ) {
   			case "yes":
   				$lang = ( isset($_POST['alo_easymail_lang_code']) && in_array ( $_POST['alo_easymail_lang_code'], alo_em_get_all_languages( false )) ) ? $_POST['alo_easymail_lang_code'] : "" ;
   				if ( get_user_meta($user_ID, 'first_name', true) != "" || get_user_meta($user_ID, 'last_name', true) != "" ) {
	    	 	   	$reg_name = ucfirst(get_user_meta($user_ID, 'first_name',true))." " .ucfirst(get_user_meta($user_ID,'last_name',true));
	    	 	} else {
	    	 		$reg_name = get_user_meta($user_ID, 'nickname', true);
	    	 	}	    	
	    	 	//alo_em_add_subscriber($user_email, $reg_name, 1, $lang );
	            if ( alo_em_add_subscriber($user_email, $reg_name, 1, $lang ) == "OK" ) {
	            	$subscriber = alo_em_get_subscriber ( $user_email );
	            	do_action ( 'alo_easymail_new_subscriber_added', $subscriber, $user_ID );
	            }
	            break;
			case "no":		
				// alo_em_delete_subscriber_by_id( alo_em_is_subscriber($user_email) );
				if ( alo_em_delete_subscriber_by_id( alo_em_is_subscriber($user_email) ) ) do_action ( 'alo_easymail_subscriber_deleted', $user_email, $user_ID );
				break;
        	case "lists":
				$subscriber_id = alo_em_is_subscriber ( $user_email );
				$mailinglists = alo_em_get_mailinglists( 'public' );
				$lists = ( isset($_POST['alo_em_form_lists'])) ? explode ( ",", trim ( $_POST['alo_em_form_lists'] , "," ) ) : array();
				if ($mailinglists) {
					foreach ( $mailinglists as $mailinglist => $val) {					
						if ( in_array ( $mailinglist, $lists ) ) {
							alo_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
						} else {
							alo_em_delete_subscriber_from_list ( $subscriber_id, $mailinglist ); // remove from list
						}
					}
				}
				break;
		}
		// Compose JavaScript for return
		$feedback = "";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '". $_POST['alo_easymail_txt_success'] .".';";
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
function alo_em_pubblic_form_callback() {
	global $wpdb, $user_ID;
    if (isset($_POST['alo_em_opt_name']) && isset($_POST['alo_em_opt_email'])){
        $error_on_adding = "";
        $just_added = false;
		$name 	= stripslashes(trim($_POST['alo_em_opt_name']));
		$email	= stripslashes(trim($_POST['alo_em_opt_email']));
        if ( !is_email($email) ) {
            $error_on_adding .= stripslashes(trim($_POST['alo_em_error_email_incorrect'])). "<br />";
        }
        if ( $name == "") {
            $error_on_adding .= stripslashes(trim($_POST['alo_em_error_name_empty'])) . ".<br />";
        }
        if ($error_on_adding == "") { // if no error
            // try to add new subscriber (and send mail if necessary) and return TRUE if success
            $activated = ( get_option('alo_em_no_activation_mail') != "yes" ) ? 0 : 1;
            $try_to_add = alo_em_add_subscriber( $email, $name, $activated, stripslashes(trim($_POST['alo_em_lang_code'])) ); 
            switch ($try_to_add) {
            	case "OK":
            		$just_added = true;
            		$subscriber = alo_em_get_subscriber ( $email );
            		do_action ( 'alo_easymail_new_subscriber_added', $subscriber, false );
            		break;
            	case "NO-ALREADYADDED":
            		$error_on_adding = stripslashes(trim($_POST['alo_em_error_email_added'])). ".<br />";
	            	break;
               	case "NO-ALREADYACTIVATED":
               		$error_on_adding = stripslashes(trim($_POST['alo_em_error_email_activated'])). ".<br />";
	            	break;
	            default: // false
	            	$error_on_adding = stripslashes(trim($_POST['alo_em_error_on_sending'])) . ".<br />";
            }
            
            // if requested, add to lists
            if ( isset($_POST['alo_em_form_lists']) && count($_POST['alo_em_form_lists']) ) {
	            $lists = explode ( ",", trim ( $_POST['alo_em_form_lists'] , "," ) );
	            $subscriber = alo_em_is_subscriber ( $email );
	            foreach ( $lists as $list ) {
					alo_em_add_subscriber_to_list ( $subscriber, $list );
				}
	      	}
        } 
        if ($just_added == true) {
			$output = $_POST['alo_em_txt_ok'];   
       		$classfeedback = "alo_easymail_widget_ok";
        } else {
			$output = $error_on_adding;
        	$classfeedback = "alo_easymail_widget_error";
       	}

		// Compose JavaScript for return
		$feedback = "";
		$feedback .= "document.alo_easymail_widget_form.submit.disabled = false;";
		$feedback .= "document.alo_easymail_widget_form.submit.value = '". stripslashes(trim($_POST['alo_em_txt_subscribe'])). "';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '$output';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = '$classfeedback';";
		$feedback .= "document.getElementById('alo_em_widget_loading').style.display = 'none';";
		// END!	
		die($feedback);
    }
}



/*************************************************************************
 * NEWSLETTERS
 *************************************************************************/ 


/**
 * Get the Newsletter(s) using 'get_posts'
 *@param	str		status
 *@param	int		how many newsletter
 */
function alo_em_query_newsletters ( $status="sent", $limit=1 ) {
	global $wpdb, $wp_version;
	$args = array (
		"post_type" 	=> "newsletter", 
		"numberposts" 	=> $limit, 
		"orderby" 		=> "post_date", 
		"order" 		=> "ASC", 
		"post_status" 	=> "publish"
	);
	if ( version_compare ( $wp_version, '3.1', '>=' ) ) {
		$meta_1 = array( 'key' => '_easymail_status', 'value' => $status, 'compare' => '=' );
		$args['meta_query'] = array( $meta_1 );
	} else {
		$args['meta_key'] = '_easymail_status';
		$args['meta_value'] = $status;
		$args['meta_compare'] = '=';
	}	
	$newsletters = get_posts ( $args );
	return $newsletters;
}


/**
 * Count Newsletter(s) by status
 *@param	int		how many newsletter
 */
function alo_em_count_newsletters_by_status ( $status="sent" ) {
	return count( alo_em_query_newsletters ( $status, -1 ) );
}


/**
 * Get the Newsletter(s) on top of queue
 *@param	int		how many newsletter
 */
function alo_em_get_newsletters_in_queue ( $limit=1 ) {
	return alo_em_query_newsletters ( "sendable", $limit );
}


/**
 * Get the Newsletter(s) already sent
 *@param	int		how many newsletter
 */
function alo_em_get_newsletters_sent ( $limit=1 ) {
	return alo_em_query_newsletters ( "sent", $limit );
}



/*************************************************************************
 * BATCH SENDING
 *************************************************************************/ 


/**
 * Get dayrate by costant or option
 */
function alo_em_get_dayrate () {
	return ( defined( 'ALO_EM_DAYRATE' ) ) ? (int)ALO_EM_DAYRATE : (int)get_option('alo_em_dayrate');
}


/**
 * Get batchrate by costant or option
 */
function alo_em_get_batchrate () {
	return ( defined( 'ALO_EM_BATCHRATE' ) ) ? (int)ALO_EM_BATCHRATE : (int)get_option('alo_em_batchrate');
}


/**
 * Get sleepvalue by costant or option
 */
function alo_em_get_sleepvalue () {
	return ( defined( 'ALO_EM_SLEEPVALUE' ) ) ? (int)ALO_EM_SLEEPVALUE : (int)get_option('alo_em_sleepvalue');
}


/**
 * Add a new newsletter to batch sending
 */
 /*
function alo_em_add_new_batch ( $user_ID, $subject, $content, $recipients, $tracking, $tag ) {
	global $wpdb;
	$wpdb->insert(
                "{$wpdb->prefix}easymail_sendings", 
                array( 'start_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'last_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'user' => $user_ID, 'subject' => $subject, 
                'content' => $content, 'sent' => '0', 'recipients' => '', 'tracking' => $tracking, 'tag' => $tag )
            );
	$newsletter = $wpdb->insert_id;
	if ( $newsletter && is_array( $recipients ) ) alo_em_add_newsletter_recipients( $newsletter, $recipients );
    return $newsletter;
}
*/

/**
 * Add newsletter Recipients
 *
 *@param	int		newsletter id
 *@param	arr		recipients
 */
 /*
function alo_em_add_newsletter_recipients ( $newsletter, $recipients ) {
	global $wpdb;
	foreach ( $recipients as $rec ) {
		$email 		= ( isset( $rec['email'] ) ) ?		$rec['email'] 		: '';
		$lang 		= ( isset( $rec['lang'] ) ) ?		$rec['lang'] 		: '';
		$name 		= ( isset( $rec['name'] ) ) ?		$rec['name'] 		: '';
		$firstname 	= ( isset( $rec['firstname'] ) ) ? 	$rec['firstname'] 	: '';
		$unikey 	= ( isset( $rec['unikey'] ) )	? 	$rec['unikey'] 		: '';
		if ( empty( $email ) ) continue;
		$wpdb->insert(
	            "{$wpdb->prefix}easymail_recipients", 
	            	array( 'newsletter' => $newsletter, 'email' => $email,	'lang' => $lang, 'name' => $name, 'firstname' => $firstname, 'unikey' => $unikey
	             )
	        );  	
	}	
}
*/

/**
 * Get newsletter Recipients
 *
 *@param	int		newsletter id
 *@return	arr		recipients
 */
 /*
function alo_em_get_newsletter_recipients ( $newsletter ) {
	global $wpdb;
	$recipients = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}easymail_recipients WHERE newsletter = %d ORDER BY email ASC", $newsletter ), ARRAY_A );

	// TODO se vuoto e se esiste la vecchia tabella stas cercare nel campo recipients della newsletter (per compatibilità v.<2 )
	$old_table = $wpdb->prefix . "easymail_trackings";
	if ( empty( $recipients ) && $wpdb->get_var("show tables like '$old_table'") == $old_table ) {
		$rec_field = $wpdb->get_var( $wpdb->prepare("SELECT recipients FROM {$wpdb->prefix}easymail_sendings WHERE ID =%d", $newsletter ) );
		if ( $rec_field ) $recipients = unserialize( $rec_field );
	}
	
	return $recipients;
}
*/

/**
 * Delete a sent newsletter 
 */
 /*
function alo_em_delete_newsletter ( $newsletter ) {
	global $wpdb;
	// delete newsletter
	$delete = $wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_sendings WHERE ID = %d", $newsletter ));
	// delete trackings
	$wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_trackings WHERE newsletter = %d", $newsletter ));
	do_action ( 'alo_easymail_newsletter_deleted', $newsletter );
    return $delete;
}
*/



/**
 * Wrap text and create alt text content before sending newsletter 
 */
function alo_em_alt_mail_body( $phpmailer ) {
	$phpmailer->WordWrap = 50;
	if( $phpmailer->ContentType == 'text/html' && $phpmailer->AltBody == '') {
		$plain_text = alo_em_html2plain ( $phpmailer->Body );
		$phpmailer->AltBody = $plain_text;
	}
}
add_action( 'phpmailer_init', 'alo_em_alt_mail_body' );


/**
 * Get the first Recipients on sending queue: the oldest in recipients db table 
 * checking the newsletter is not paused (opt. filtered by a newsletter id)
 *
 * @param 	int		limit: how many
 * @param 	int		newsletter id
 * @return	obj		recipients
 */
function alo_em_get_recipients_in_queue ( $limit=false, $newsletter=false ) {
	global $wpdb;
	if ( !$limit ) $limit = alo_em_get_batchrate ();
	$query_limit = ( $limit ) ? " LIMIT ".$limit : "";
	$query_newsletter = ( $newsletter ) ? " AND newsletter =". $newsletter ." " : "";
	$recipients = $wpdb->get_results( 
		"SELECT r.*, s.lang, s.unikey, s.name, s.ID AS subscriber FROM {$wpdb->prefix}easymail_recipients AS r 
		LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email 
		INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = r.newsletter 
		INNER JOIN {$wpdb->posts} AS p ON p.ID = r.newsletter 
		WHERE pm.meta_key = '_easymail_status' AND pm.meta_value = 'sendable' AND r.result = 0 AND p.post_status = 'publish' ". $query_newsletter ." 
		ORDER BY r.ID ASC" . $query_limit );
	if ( $recipients ) : foreach ( $recipients as $recipient ) :
			if ( $user_id = email_exists( $recipient->email ) ) {
				if ( get_user_meta( $user_id, 'first_name', true ) != "" ) {
					$recipient->firstname = ucfirst( get_user_meta( $user_id, 'first_name', true ) );
				} else {
					$recipient->firstname = $recipient->name;
				}
		 	} else {
		 		$recipient->firstname = $recipient->name;
		 	}
	endforeach; endif;
	return $recipients;
}


/**
 * Send the Newsletter to Recipient
 * @param	arr		a recipient object: email, newsletter, ID (opt), lang (opt), name (opt), unikey (opt), subsriber (opt)
 * @param	bol		if true forse to send, ignore debug setting
 * @return	bol		
 */
function alo_em_send_newsletter_to ( $recip, $force_send=false ) {
	global $wpdb;
	$defaults = array(
		'email' => false,
		'newsletter' => false, 
		'ID' => false,	// if false, it's a test sending
		'lang' => alo_em_get_language (),
		'name' => false,
		'firstname' => false,
		'subscriber' => false,
		'unikey' => false
	);
	$args = wp_parse_args( (array)$recip, $defaults );
	$recipient = (object)$args;
	
	if ( !is_email( $recipient->email ) ) return; 
		
	// Get newsletter details
	$newsletter = alo_em_get_newsletter( $recipient->newsletter );
	
	$subject = stripslashes ( alo_em_translate_text ( $recipient->lang, $newsletter->post_title ) );
	$subject = apply_filters( 'alo_easymail_newsletter_title', $subject, $newsletter, $recipient ); 
	   
	$content = alo_em_translate_text( $recipient->lang, $newsletter->post_content ); 
	
	// easymail standard and custom filters
	$content = apply_filters( 'alo_easymail_newsletter_content', $content, $newsletter, $recipient, false ); 
	
	// general filters and shortcodes applied to 'the_content'?
	if ( get_option('alo_em_filter_the_content') != "no" ) {
		add_filter ( 'the_content', 'do_shortcode', 11 );
		$content = apply_filters( "the_content", $content );
	}
	
	/* // maybe useless in v.2...
	if ( get_option('alo_em_filter_br') != "no" ) {
		$content = wpautop( $content, 1 );
		$content = str_replace("\n", "<br />\r\n", $content);
		$content = str_replace( array("<br /><t", "<br/><t", "<br><t"), "<t", $content);
		$content = str_replace( array("<br /></t", "<br/></t", "<br></t"), "</t", $content);
	}
	*/
	
	$viewonline_url = alo_em_translate_url ( get_permalink( $recipient->newsletter ), $recipient->lang );
   	if ( $viewonline_msg = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_viewonline_msg', true ) ) {
		$content .= $viewonline_msg;
	} else {
		$content .= "\r\n<p><em>". __("To read the newsletter online you can visit this link", "alo-easymail");
		$content .=	": %NEWSLETTERLINK%</em></p>\r\n";
	}
	$content = str_replace ( "%NEWSLETTERLINK%", " <a href='".$viewonline_url."'>". $viewonline_url ."</a>", $content );

	// Unsubscribe link and tracking, only if subscriber
	if ( $recipient->unikey && $recipient->ID ) {
	
		/*         
		$div_email = explode( "@", $recipient->email ); // for link
	   	$arr_params = array ('ac' => 'unsubscribe', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $recipient->unikey );
		$uns_link = add_query_arg( $arr_params, get_page_link (get_option('alo_em_subsc_page')) );
		$uns_link = alo_em_translate_url ( $uns_link, $recipient->lang );
		*/
		
		$uns_vars = $recipient->subscriber . "|" . $recipient->unikey;
		$uns_vars = urlencode( base64_encode( $uns_vars ) );
		$uns_link = add_query_arg( 'emunsub', $uns_vars, trailingslashit( get_home_url() ) );
		$uns_link = alo_em_translate_url ( $uns_link, $recipient->lang );
		
	   	if ( $unsubfooter = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_unsub_footer', true ) ) {
			$content .= $unsubfooter;
		} else {
			$content .= "\r\n<p><em>". __("You have received this message because you subscribed to our newsletter. If you want to unsubscribe: ", "alo-easymail")." ";
			$content .=	__("visit this link", "alo-easymail") ."<br />\r\n%UNSUBSCRIBELINK%";
			$content .= "</em></p>\r\n";
		}
		$content = str_replace ( "%UNSUBSCRIBELINK%", " <a href='".$uns_link."'>". $uns_link ."</a>", $content );
		
		$track_vars = $recipient->ID . "|" . $recipient->unikey;
        $track_vars = urlencode( base64_encode( $track_vars ) );
        
        $nossl_plugin_url = str_replace( "https", "http", ALO_EM_PLUGIN_URL );
		$content .= "\r\n<img src='". $nossl_plugin_url ."/tr.php?v=". $track_vars ."' width='1' height='1' border='0' >";
		//$content .= "<pre>". print_r ( $recipient, true ) . "</pre>";
	}
		

	
	$mail_sender = ( get_option('alo_em_sender_email') ) ? get_option('alo_em_sender_email') : "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
	$from_name = html_entity_decode ( wp_kses_decode_entities ( get_option('alo_em_sender_name') ) );

	$headers = "From: ". $from_name ." <".$mail_sender.">\n";
	$headers .= "Content-Type: text/html; charset=\"" . strtolower( get_option('blog_charset') ) . "\"\n";		

    // ---- Send MAIL (or DEBUG) ----
    $send_mode = ( $force_send ) ? "" : get_option('alo_em_debug_newsletters');
    switch ( $send_mode ) {
    	case "to_author":
	    		$author = get_userdata( $newsletter->post_author );
    			$debug_subject = "( DEBUG - TO: ". $recipient->email ." ) " . $subject;
    			$mail_engine = wp_mail( $author->user_email, $debug_subject, $content, $headers );
				break;
    	case "to_file":
    			$log = fopen( WP_CONTENT_DIR . "/user_{$newsletter->post_author}_newsletter_{$newsletter->ID}.log", 'a+' );
    			$log_message = 	"\n------------------------------ ". date_i18n( __( 'j M Y @ G:i' ) ) ." ------------------------------\n\n";
    			$log_message .=	"HEADERS:\n". $headers ."\n";
    			$log_message .=	"TO:\t\t\t". $recipient->email ."\n";
    			$log_message .=	"SUBJECT:\t". $subject ."\n\n";
    			$log_message .=	"CONTENT:\n". $content ."\n\n";
				$mail_engine = ( fwrite ( $log, $log_message ) ) ? true : false;
				fclose ( $log );
				break;
    	default:  // no debug: send it!
				$mail_engine = wp_mail( $recipient->email, $subject, $content, $headers );       					        					
    }
      
    $sent = ( $mail_engine ) ? "1" : "-1";
	
	// If recipient is in db (eg. ID exists) update db
	if ( $recipient->ID ) {
		$wpdb->update(    "{$wpdb->prefix}easymail_recipients",
		    array ( 'result' => $sent ),
		    array ( 'ID' => $recipient->ID )
		);
	}
	return ( $mail_engine ) ? true : false;
}


/**
 * When the newsletter has been sent, mark it as completed
 */
function alo_em_set_newsletter_as_completed ( $newsletter ) {
	global $wpdb;
	alo_em_edit_newsletter_status ( $newsletter, 'sent' );
	add_post_meta ( $newsletter, "_easymail_completed", current_time( 'mysql', 0 ) );
	$newsletter_obj = alo_em_get_newsletter ( $newsletter );
	do_action ( 'alo_easymail_newsletter_delivered', $newsletter_obj );
}


/**
 * Called by wp_cron: send the newsletter to a fraction of recipients every X minutes
 */
function alo_em_batch_sending () {
	global $wpdb;
	
	// search the interval between now and previous sending (or from default cron interval)
	$prev_time = ( get_option ( 'alo_em_last_cron' ) ) ? strtotime( get_option ( 'alo_em_last_cron' ) ) : current_time( 'timestamp', 0 ) - ALO_EM_INTERVAL_MIN * 60;
	$diff_time = current_time( 'timestamp', 0 ) - $prev_time; 
	
	// so... how much recipients for this interval? // (86400 = seconds in a day)
	$day_rate = alo_em_get_dayrate();
	$tot_recs = max ( floor( ( $day_rate * $diff_time / 86400 ) ) , 1 ); 
	// not over the limit
	$limit_recs = min ( $tot_recs, alo_em_get_batchrate () );
			
	// the recipients to whom send
	$recipients = alo_em_get_recipients_in_queue ( $limit_recs );
	
	// update 'last cron time' option
	update_option ( 'alo_em_last_cron', current_time( 'mysql', 0 ) );
	
	// if no recipients exit!
	if ( !$recipients ) return;
	
	foreach ( $recipients as $recipient ) {
		if ( alo_em_get_newsletter_status ( $recipient->newsletter ) != "sendable" ) continue;
		
		ob_start();
		
		// Prepare and send the newsletter to this user!
		alo_em_send_newsletter_to ( $recipient );
	
		// if no more recipient of this newsletter, it has been sent
		if ( count( alo_em_get_recipients_in_queue( 1, $recipient->newsletter ) ) == 0 ) {
			alo_em_set_newsletter_as_completed ( $recipient->newsletter );
		}
		ob_end_flush();
		if ( (int)get_option('alo_em_sleepvalue') > 0 ) usleep ( (int)get_option('alo_em_sleepvalue') * 1000 );
	}		
}


/**
 * Filter Newsletter Title when sending
 */
function alo_em_filter_title( $subject, $newsletter, $recipient ) {
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
	if ( $obj_post ) {
		$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title ) );
	    $subject = str_replace('[POST-TITLE]', $post_title, $subject);
	} else {
	    $subject = str_replace('[POST-TITLE]', "", $subject);
	}
	return $subject;
}
add_filter ( 'alo_easymail_newsletter_title',  'alo_em_filter_title', 10, 3 );


/**
 * Filter Newsletter Title when in title bar in site
 */
function alo_em_filter_title_bar( $subject ) {
	global $post;
	if ( get_post_type( $post->ID ) == 'newsletter' ) {
		$post_id = get_post_meta ( $post->ID, '_placeholder_easymail_post', true );
		$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
		if ( $obj_post ) {
			$post_title = stripslashes ( alo_em_translate_text ( alo_em_get_language (), $obj_post->post_title ) );
			$subject = str_replace('[POST-TITLE]', $post_title, $subject);
		} else {
			$subject = str_replace('[POST-TITLE]', "", $subject);
		}
	}
	return $subject;
}
add_filter ( 'single_post_title',  'alo_em_filter_title_bar' );


/**
 * Filter Newsletter Title when viewed in site
 */
function alo_em_filter_title_in_site ( $subject ) {
	global $post, $pagenow;
	// in frontend and in 'edit.php' screen in backend
	if ( ( $post && !is_admin() ) || $pagenow == 'edit.php' ) {
		$post_id = get_post_meta ( $post->ID, '_placeholder_easymail_post', true );
		$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
		if ( $obj_post ) {
			$post_title = stripslashes ( alo_em_translate_text ( false, $obj_post->post_title ) );
			$subject = str_replace('[POST-TITLE]', $post_title, $subject);
		} else {
			$subject = str_replace('[POST-TITLE]', "", $subject);
		}
	}
	return $subject;
}
add_filter ( 'the_title',  'alo_em_filter_title_in_site' );


/**
 * Filter Newsletter Content when sending
 */
function alo_em_filter_content ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {  
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$obj_post = ( $post_id ) ? get_post( $post_id ) : false;

	if ( $obj_post ) {
		$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title ) );
	    $content = str_replace("[POST-TITLE]", "<a href='". esc_url ( alo_em_translate_url( get_permalink( $obj_post->ID ), $recipient->lang ) ). "'>". $post_title ."</a>", $content);      
	} else {
	    $content = str_replace("[POST-TITLE]", "", $content);
	}
	
	if ( $obj_post ) {
		$postcontent =  stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_content ) );
		/*
		if ( get_option('alo_em_filter_br') != "no" ) {
			$postcontent = str_replace("\n", "<br />", $postcontent);
			// trim <br> added when rendering html tables (thanks to gunu)
			$postcontent = str_replace( array("<br /><t", "<br/><t", "<br><t"), "<t", $postcontent);
			$postcontent = str_replace( array("<br /></t", "<br/></t", "<br></t"), "</t", $postcontent);
		}
		*/
		if ( get_option('alo_em_filter_the_content') != "no" && !$stop_recursive_the_content ) $postcontent = apply_filters('the_content', $postcontent);
	    $content = str_replace("[POST-CONTENT]", $postcontent, $content);
	} else {
	    $content = str_replace("[POST-CONTENT]", "", $content);
	}
	
	if ( $obj_post && !empty($obj_post->post_excerpt)) {
		$post_excerpt = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_excerpt ) );
	    $content = str_replace("[POST-EXCERPT]", $post_excerpt, $content);       
	} else {
	    $content = str_replace("[POST-EXCERPT]", "", $content);
	}
	
    if ( $recipient ) {	
		if ( isset( $recipient->name ) ) {
		    $content = str_replace("[USER-NAME]", stripslashes ( $recipient->name ), $content);     
		} else {
		    $content = str_replace("[USER-NAME]", "", $content);
		}            
		if ( isset( $recipient->firstname ) ) {
		    $content = str_replace("[USER-FIRST-NAME]", stripslashes ( $recipient->firstname ), $content);       
		} else {
		    $content = str_replace("[USER-FIRST-NAME]", "", $content);
		}        	
    }
    
    $content = str_replace("[SITE-LINK]", "<a href='". esc_url ( alo_em_translate_url ( get_option ('siteurl'), $recipient->lang ) ) ."'>".get_option('blogname')."</a>", $content);  
    
	return $content;	
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_filter_content', 10, 4 );


/**
 * Apply filters when newsletter is read on blog
 */ 
function alo_em_filter_content_in_site ( $content ) {  
	global $post;
	if ( !is_admin() && $post ) {
		$recipient = (object) array( "name" => __( "Subscriber", "alo-easymail" ), "firstname" => __( "Subscriber", "alo-easymail" ) );
		$content = apply_filters( 'alo_easymail_newsletter_content', $content, $post, $recipient, true ); 
	}
	return $content;	
}
add_filter ( 'the_content',  'alo_em_filter_content_in_site' );



/*************************************************************************
 * MAILING LISTS & RECIPIENTS FUNCTIONS
 *************************************************************************/ 


/**
 * Get Subscriber by e-mail
 */
function alo_em_get_subscriber ( $email ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE email = %s", $email ) );
}


/**
 * Get Subscriber by ID
 */
function alo_em_get_subscriber_by_id ( $ID ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE ID = %d", $ID ) );
}


/**
 * Get all registered users of the blog 
 * return object with info as in table column
 */
function alo_em_get_recipients_registered () {
	global $wpdb, $blog_id;    
    if ( function_exists( 'get_users' ) ) { // For WP >= 3.1
    	$get_users = get_users();
   	} else { // For WP < 3.1
   		$get_users = get_users_of_blog();
   	}
   	for ( $i = 0; $i < count ($get_users); $i ++ ) {
		$get_users[$i]->lang = $wpdb->get_var ( $wpdb->prepare( "SELECT lang FROM {$wpdb->prefix}easymail_subscribers WHERE email = %s", $get_users[$i]->user_email ) );
		$get_users[$i]->UID = $get_users[$i]->ID;
	} 
    //echo "<pre>";print_r($get_users); echo "</pre>";
    return $get_users;
}


/**
 * Get ALL subscribers OR only by SELECTED lists
 * @lists	array	only by selected lists 		
 * return object with info as in table column 
 */
function alo_em_get_recipients_subscribers ( $lists=false ) {
	global $wpdb;
	$where_lists = "";
	if ( $lists && !is_array($lists) ) $lists = array ( $lists );
	if ( $lists ) {
		$where_lists .= " AND (";
		foreach ( $lists as $list ) {
			$where_lists .= "lists LIKE '%|".$list."|%' OR ";
		}
		$where_lists = substr( $where_lists , 0, -3); // cut last "OR"
		$where_lists .= ")";
	}
	return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE active='1' $where_lists" );
}


/**
 * Count subscribers reading the selected language
 * param	lang		if false return no langs or no longer available langs
 * param	active		if only activated subscribers or all subscribers
 * return int
 */
function alo_em_count_subscribers_by_lang ( $lang=false, $only_activated=false ) {
	global $wpdb;
	if ( $lang ) {
		$str_lang = "lang='$lang'";
	} else {
		// search with no selected langs or old langs now not requested
		$langs = alo_em_get_all_languages();
		$str_lang = "lang IS NULL OR lang NOT IN (";
		if ( is_array($langs) ) { 
			foreach ( $langs as $k => $l ) {
				$str_lang .= "'$l',";
			}
		}
		$str_lang = rtrim ($str_lang, ",");
		$str_lang .= ")" ;
	}
	$str_activated = ( $only_activated ) ? " AND active = '1'" : "";
	return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}easymail_subscribers WHERE $str_lang $str_activated" );
}


/**
 * Get the mailing lists (as array)
 * @types  str		list types requested (a string with comma: eg. 'hidden,admin,public')
 */
function alo_em_get_mailinglists ( $types = false ) {
	$get = get_option('alo_em_mailinglists');
	if ( $types == false ) {
		$types = array ( 'hidden', 'admin', 'public' ); // default types	
	} else {
		$types = explode (",", $types);
	}
 	if ( empty($get) ) {
		return false;
	} else {
		$mailinglists = maybe_unserialize($get);
		$mailinglists = alo_em_msort ($mailinglists,'order', 'ASC');//($mailinglists,'order', false);
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
function alo_em_save_mailinglists ( $lists ) {
	if ( !is_array ($lists) ) return false;
	$arraylists = $lists; // maybe_serialize( $lists );
	update_option ( 'alo_em_mailinglists', $arraylists );
	return true;
}


/**
 * Add a mailing list subscription to a subscriber (and save in db the new list)
 * @subscriber		
 * @list			
 */
function alo_em_add_subscriber_to_list ( $subscriber, $list ) {
	global $wpdb;
	$user_lists = alo_em_get_user_mailinglists ( $subscriber );
	if ( $user_lists && in_array($list, $user_lists) ) return; // if already, exit
	$user_lists[] = $list; // add the list
	asort ( $user_lists ); // order id from min to max, 1->9
	$updated_lists = implode ( "|", $user_lists );
	$updated_lists = "|".$updated_lists."|";
    return $wpdb->update( "{$wpdb->prefix}easymail_subscribers", array ( 'lists' => $updated_lists ), array ( 'ID' => $subscriber ) );
}


/**
 * Delete subscriber from mailing list
 * @subscriber		
 * @list		
 */
function alo_em_delete_subscriber_from_list ( $subscriber, $list ) {
	global $wpdb;
	return $wpdb->query( "UPDATE {$wpdb->prefix}easymail_subscribers SET lists = REPLACE(lists, '|".$list."|', '|') WHERE ID=" . $subscriber );
}


/**
 * Delete ALL subscribers from mailing list(s)
 * @lists	array of lists ID
 */
function alo_em_delete_all_subscribers_from_lists ( $lists ) {
	global $wpdb;
	if ( !is_array($lists) ) $lists = array ( $lists );
	foreach ( $lists as $list ) {
		$wpdb->query( "UPDATE {$wpdb->prefix}easymail_subscribers SET lists = REPLACE(lists, '|".$list."|', '|')" );
	}
	return true;
}


/**
 * Get the user mailing lists
 * @array_lists		array of lists ID
 */
function alo_em_get_user_mailinglists ( $subscr_id ) {
	global $wpdb;
	$lists = $wpdb->get_var ( $wpdb->prepare( "SELECT lists FROM {$wpdb->prefix}easymail_subscribers WHERE ID = %d", $subscr_id ) );
	if ( $lists	) {
		$array_lists = explode ( "|", trim ($lists, "|" ) );
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
function alo_em_html_mailinglists_table_to_edit ( $user_email, $cssclass="" ) {
	$html = "";
	$lists_msg 	= ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) :  __("You can also sign up for specific lists", "alo-easymail");  
    $mailinglists = alo_em_get_mailinglists( 'public' );
    if ( $mailinglists ) {
	    $subscriber_id = alo_em_is_subscriber( $user_email );
	    $user_lists = alo_em_get_user_mailinglists ( $subscriber_id );
		$html .= "<table ". (($cssclass!="")? " class='$cssclass' " : "") ."><tbody>\n"; 
		$html .= "<tr><th ". (($cssclass=="")? " style='width:50%' ":"") .">". $lists_msg	.":</th>\n";
		$html .= "<td>\n";
		foreach ( $mailinglists as $list => $val ) {
			$checked = ( $user_lists && in_array ( $list, $user_lists )) ? "checked='checked'" : "";
			$html .= "<input type='checkbox' name='alo_em_profile_lists[]' id='alo_em_profile_list_$list' value='$list' $checked /> " . alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ."<br />\n";
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
 * If recipient has been tracked (eg. if he has opened the newsletter)
 *@param	int		recipient
 *@param	str		url clicked
 *@return 	bol
 */
function alo_em_recipient_is_tracked ( $recipient, $request='' ) {
	global $wpdb;
	$trackings = alo_em_get_recipient_trackings( $recipient, $request );
	return ( $trackings ) ? true : false;
}


/**
 * Get all trackings of a recipient
 *@param	int		recipient
 *@param	str		url clicked, blank for view
 *@return 	arr		array of object
 */
function alo_em_get_recipient_trackings ( $recipient, $request='' ) {
	global $wpdb;
	return $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_stats WHERE recipient=%d AND request='%s'", $recipient, $request ) );
}


/**
 * Tracking when a recipient views newsletter
 *@param	int		recipient
 *@param	int		newsletter: if empty get it from recipient 
 *@param	str		url clicked, blank for view
 */
function alo_em_tracking_recipient ( $recipient, $newsletter=false, $request='' ) {
    global $wpdb;
    if ( empty( $newsletter ) ) {
    	$rec_info = alo_em_get_recipient_by_id( $recipient );
    	$newsletter = $rec_info->newsletter;
    }    
	return $wpdb->insert ( "{$wpdb->prefix}easymail_stats",
           					array( 'recipient' => $recipient, 'newsletter' => $newsletter, 'added_on' => current_time( 'mysql', 0 ), 'request' => '' )
	);
} 


/**
 * Count all trackings about a newsletter
 *@param	int		newsletter
 *@param	str		url clicked, blank for view 
 *@return 	arr		array of object: each object contains recipient and number of views/clicks
 */
function alo_em_all_newsletter_trackings ( $newsletter, $request='' ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare("SELECT recipient, COUNT(ID) AS numitems FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d AND request='%s' GROUP BY recipient ORDER BY numitems DESC", $newsletter, $request ));
}




/*************************************************************************
 * MULTILANGUAGE
 *************************************************************************/ 


/**
 * Check if there is a multiplanguage enabled plugin 
 * return the name of plugin, or false
 */
function alo_em_multilang_enabled_plugin () {
	// 1st choice: qTranslate
	global $q_config;
	if( function_exists( 'qtrans_init') && isset($q_config) ) {
		return "qTrans";
	}
	
	// TODO other choices...
	
	// no plugin: return false
	return false;
}


/**
 * Return a text after applying a multilanguage filter 
 */
function alo_em___ ( $text ) {
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') ) {
		return qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage ( $text );
	}
	// TODO other choices...

	// last case: return without translating
	return $text ;
}

/**
 * Echo a text after applying a multilanguage filter (based on 'alo_em___')
 */
function alo_em__e ( $text ) {
	echo alo_em___ ( $text );
}



/**
 * Return a text after applying a multilanguage filter 
 */
function alo_em_translate_text ( $lang, $text ) {
	// if blank lang or not installed on blog, get default lang
	if ( empty($lang) || !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = alo_em_short_langcode ( get_locale() );
	
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_use') ) {
		return qtrans_use ( $lang, $text, false);
	}
	// TODO other choices...

	// last case: return as is
	return $text ;
}


/**
 * Return a text of the requested lang from a saved option or default option
 * param	fallback	if requested lang not exists and fallback true returns a lang default
 */
function alo_em_translate_option ( $lang, $key , $fallback=true ) {
	$default_lang = alo_em_short_langcode ( get_locale() ); // default lang
	$fallback_lang = "en"; // latest default...
	$text_1 = $text_2 = $text_3 = false;

	// from default option if exists
	if ( get_option( $key."_default" ) ) {
		$get = get_option( $key."_default" );
		if ( is_array($get) ) {
			foreach ( $get as $k => $v ) {
				if ( $k == $lang )			$text_1 = $v;	// the requested lang
				if ( $k == $default_lang )	$text_2 = $v;	// the default lang
				if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
			}
		}
	}
		
	// from option
	if ( get_option( $key ) ) {
		$get = get_option( $key );
		if ( is_array($get) ) {
			foreach ( $get as $k => $v ) {
				if ( !empty($v) ) { // if not empty
					if ( $k == $lang )			$text_1 = $v;	// the requested lang
					if ( $k == $default_lang )	$text_2 = $v;	// the default lang
					if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
				}
			}
		}
	}
	
	if ( $text_1 ) return $text_1;
	if ( $text_2 && $fallback ) return $text_2;	
	if ( $text_3 && $fallback ) return $text_3;
	return false;
}


/**
 * Return a text of the requested lang from an array with same text in several langs ( "en" => "hi", "es" => "hola"...)
 * param	fallback	if requested lang not exists and fallback true returns a lang default
 */
function alo_em_translate_multilangs_array ( $lang, $array, $fallback=true ) {
	if ( !is_array($array) ) return $array; // if not array, return the text
	
	$default_lang = alo_em_short_langcode ( get_locale() ); // default lang
	$fallback_lang = "en"; // latest default...
	$text_1 = $text_2 = $text_3 = false;
	
	foreach ( $array as $k => $v ) {
		if ( $k == $lang ) 			$text_1 = $v;	// the requested lang
		if ( $k == $default_lang ) 	$text_2 = $v;	// the default lang
		if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
	}
	
	if ( $text_1 ) return $text_1;
	if ( $text_2 && $fallback ) return $text_2;	
	if ( $text_3 && $fallback ) return $text_3;
	return false;
}


/** 
 * Return the url localised for the requested lang 
 */
function alo_em_translate_url ( $url, $lang ) {

	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_convertURL') ) {
		//return qtrans_convertURL( $url, $lang ); // TODO
		return add_query_arg( "lang", $lang, $url );
	}
	
	// TODO other choices...
	
	// last case: return th url with a "lang" var... maybe it could be useful...
	return add_query_arg( "lang", $lang, $url );
}


/**
 * Return the current language 
 *
 * param	bol		try lang detection form browser (eg. useful for subscription if multilang plugin not installed)
 */
function alo_em_get_language ( $detect_from_browser=false ) {
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_getLanguage') ) {
		return strtolower( qtrans_getLanguage() );
	}
	
	// TODO other choices...
	
	// get from browser only if requested and the lang .mo is available on blog
	if ( $detect_from_browser ) {
		$lang = alo_em_short_langcode ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		if ( !empty($lang) && in_array($lang, alo_em_get_all_languages(false)) ) {
			return $lang;
		} else {
			return "";
		}
	} else {	
		// otherwise return default blog language
		return alo_em_short_langcode ( get_locale() );
	}
}

/**
 * Return 2 chars lowercase lang code (eg. from "it_IT" to "it")
 */
function alo_em_short_langcode ( $lang ) {
	return strtolower ( substr( $lang, 0, 2) );
}

/**
 * Return the long name of language
 */
function alo_em_get_lang_name ( $lang_code ) {
	global $q_config;
	$lang_code = alo_em_short_langcode( $lang_code );
	if ( alo_em_multilang_enabled_plugin() == "qTrans" && isset($q_config) ) { // qTranslate
		$name = $q_config['language_name'][$lang_code];
	} else { // default
		$longname = alo_em_format_code_lang ( $lang_code );
		$splitname = explode ( ";", $longname );
		$name = $splitname[0];
	}
	return $name;
}


/**
 * Return the lang flag
 * param 	fallback	if there is not the image, return the lang code ('code') or lang name ('name') or nothing
 */
function alo_em_get_lang_flag ( $lang_code, $fallback=false ) {
	global $q_config;
	if ( empty($lang_code) ) return; 
	$flag = false;
	$lang_code =  alo_em_short_langcode ( $lang_code );
	if ( alo_em_multilang_enabled_plugin() == "qTrans" && isset($q_config) ) { // qTranslate
		if ( $lang_code == "en" && !file_exists ( trailingslashit(WP_CONTENT_DIR).$q_config['flag_location']. $lang_code .".png" ) ) {
			$img_code = "gb";
		} else {
			$img_code = $lang_code;
		}
		$flag = "<img src='". trailingslashit(WP_CONTENT_URL).$q_config['flag_location']. $img_code .".png' alt='".$q_config['language_name'][$lang_code]."' title='".$q_config['language_name'][$lang_code]."' />" ;
	} else { // default
		if ( $fallback == "code" ) $flag = $lang_code;
		if ( $fallback == "name" ) $flag = alo_em_get_lang_name ( $lang_code );
	}
	return $flag;
}


/**
 * Return an array with availables languages
 * param 	by_users	if true and no other translation plugins get all langs chosen by users, if not only langs installed on blog
 */
function alo_em_get_all_languages ( $fallback_by_users=false ) {
	global $wp_version;
	
	// Case: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_getSortedLanguages') ) {
		return qtrans_getSortedLanguages();
	}
	
	// TODO other plugins
	
	// Case: search for setting
	if ( get_option( 'alo_em_langs_list' ) != "" ) {
		$languages = explode ( ",", get_option( 'alo_em_langs_list' ) );
	} 
	/* // Disabled to avoid auto-loading languages...
	else {
		// Case: wp default detection
		$languages = array();
		// WP_CONTENT_DIR. '/languages/' instead of WP_LANG_DIR: if qtranslate previously installed and then de-activated, the WP_LANG_DIR will remain 'wp-includes/languages/'
		foreach( (array)glob( WP_CONTENT_DIR. '/languages/*.mo' ) as $lang_file ) {
			$lang_file = basename($lang_file, '.mo');
			if ( 0 !== strpos( $lang_file, 'continents-cities' ) && 0 !== strpos( $lang_file, 'ms-' ) )
				$languages[] = alo_em_short_langcode( $lang_file );
		}
	}
	*/
	
	// If languages, add locale lang (if not yet) and return
	if ( !empty ($languages[0]) ) {
		$default = alo_em_short_langcode ( get_locale() );
		if ( !in_array( $default, $languages ) ) $languages[] = $default;
		return $languages;
	}
	
	
	// Last case: return all langs chosen by users or default
	if ( $fallback_by_users ) {
		return alo_em_get_all_languages_by_users();
	} else {
		return array( alo_em_short_langcode ( get_locale() ) );
	}	
}


/**
 * Return an array with all languages chosen by users
 */
function alo_em_get_all_languages_by_users () {
	global $wpdb;
	$langs = $wpdb->get_results( "SELECT lang FROM {$wpdb->prefix}easymail_subscribers GROUP BY lang" , ARRAY_N );
	if ( $langs ) {
		$output = array();
		foreach ( $langs as $key => $val ) {
			if ( !empty($val[0]) ) $output[] = $val[0];
		}
		return $output;
	} else {
		return array( alo_em_short_langcode ( get_locale() ) );
	}
}



/**
 * Return the long name of language
 */
function alo_em_format_code_lang( $code = '' ) {
	$code = strtolower( substr( $code, 0, 2 ) );
	$lang_codes = array(
		'aa' => 'Afar', 'ab' => 'Abkhazian', 'af' => 'Afrikaans', 'ak' => 'Akan', 'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic', 'an' => 'Aragonese', 'hy' => 'Armenian', 'as' => 'Assamese', 'av' => 'Avaric', 'ae' => 'Avestan', 'ay' => 'Aymara', 'az' => 'Azerbaijani', 'ba' => 'Bashkir', 'bm' => 'Bambara', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali',
		'bh' => 'Bihari', 'bi' => 'Bislama', 'bs' => 'Bosnian', 'br' => 'Breton', 'bg' => 'Bulgarian', 'my' => 'Burmese', 'ca' => 'Catalan; Valencian', 'ch' => 'Chamorro', 'ce' => 'Chechen', 'zh' => 'Chinese', 'cu' => 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic', 'cv' => 'Chuvash', 'kw' => 'Cornish', 'co' => 'Corsican', 'cr' => 'Cree',
		'cs' => 'Czech', 'da' => 'Danish', 'dv' => 'Divehi; Dhivehi; Maldivian', 'nl' => 'Dutch; Flemish', 'dz' => 'Dzongkha', 'en' => 'English', 'eo' => 'Esperanto', 'et' => 'Estonian', 'ee' => 'Ewe', 'fo' => 'Faroese', 'fj' => 'Fijjian', 'fi' => 'Finnish', 'fr' => 'French', 'fy' => 'Western Frisian', 'ff' => 'Fulah', 'ka' => 'Georgian', 'de' => 'German', 'gd' => 'Gaelic; Scottish Gaelic',
		'ga' => 'Irish', 'gl' => 'Galician', 'gv' => 'Manx', 'el' => 'Greek, Modern', 'gn' => 'Guarani', 'gu' => 'Gujarati', 'ht' => 'Haitian; Haitian Creole', 'ha' => 'Hausa', 'he' => 'Hebrew', 'hz' => 'Herero', 'hi' => 'Hindi', 'ho' => 'Hiri Motu', 'hu' => 'Hungarian', 'ig' => 'Igbo', 'is' => 'Icelandic', 'io' => 'Ido', 'ii' => 'Sichuan Yi', 'iu' => 'Inuktitut', 'ie' => 'Interlingue',
		'ia' => 'Interlingua (International Auxiliary Language Association)', 'id' => 'Indonesian', 'ik' => 'Inupiaq', 'it' => 'Italian', 'jv' => 'Javanese', 'ja' => 'Japanese', 'kl' => 'Kalaallisut; Greenlandic', 'kn' => 'Kannada', 'ks' => 'Kashmiri', 'kr' => 'Kanuri', 'kk' => 'Kazakh', 'km' => 'Central Khmer', 'ki' => 'Kikuyu; Gikuyu', 'rw' => 'Kinyarwanda', 'ky' => 'Kirghiz; Kyrgyz',
		'kv' => 'Komi', 'kg' => 'Kongo', 'ko' => 'Korean', 'kj' => 'Kuanyama; Kwanyama', 'ku' => 'Kurdish', 'lo' => 'Lao', 'la' => 'Latin', 'lv' => 'Latvian', 'li' => 'Limburgan; Limburger; Limburgish', 'ln' => 'Lingala', 'lt' => 'Lithuanian', 'lb' => 'Luxembourgish; Letzeburgesch', 'lu' => 'Luba-Katanga', 'lg' => 'Ganda', 'mk' => 'Macedonian', 'mh' => 'Marshallese', 'ml' => 'Malayalam',
		'mi' => 'Maori', 'mr' => 'Marathi', 'ms' => 'Malay', 'mg' => 'Malagasy', 'mt' => 'Maltese', 'mo' => 'Moldavian', 'mn' => 'Mongolian', 'na' => 'Nauru', 'nv' => 'Navajo; Navaho', 'nr' => 'Ndebele, South; South Ndebele', 'nd' => 'Ndebele, North; North Ndebele', 'ng' => 'Ndonga', 'ne' => 'Nepali', 'nn' => 'Norwegian Nynorsk; Nynorsk, Norwegian', 'nb' => 'Bokmål, Norwegian, Norwegian Bokmål',
		'no' => 'Norwegian', 'ny' => 'Chichewa; Chewa; Nyanja', 'oc' => 'Occitan, Provençal', 'oj' => 'Ojibwa', 'or' => 'Oriya', 'om' => 'Oromo', 'os' => 'Ossetian; Ossetic', 'pa' => 'Panjabi; Punjabi', 'fa' => 'Persian', 'pi' => 'Pali', 'pl' => 'Polish', 'pt' => 'Portuguese', 'ps' => 'Pushto', 'qu' => 'Quechua', 'rm' => 'Romansh', 'ro' => 'Romanian', 'rn' => 'Rundi', 'ru' => 'Russian',
		'sg' => 'Sango', 'sa' => 'Sanskrit', 'sr' => 'Serbian', 'hr' => 'Croatian', 'si' => 'Sinhala; Sinhalese', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'se' => 'Northern Sami', 'sm' => 'Samoan', 'sn' => 'Shona', 'sd' => 'Sindhi', 'so' => 'Somali', 'st' => 'Sotho, Southern', 'es' => 'Spanish; Castilian', 'sc' => 'Sardinian', 'ss' => 'Swati', 'su' => 'Sundanese', 'sw' => 'Swahili',
		'sv' => 'Swedish', 'ty' => 'Tahitian', 'ta' => 'Tamil', 'tt' => 'Tatar', 'te' => 'Telugu', 'tg' => 'Tajik', 'tl' => 'Tagalog', 'th' => 'Thai', 'bo' => 'Tibetan', 'ti' => 'Tigrinya', 'to' => 'Tonga (Tonga Islands)', 'tn' => 'Tswana', 'ts' => 'Tsonga', 'tk' => 'Turkmen', 'tr' => 'Turkish', 'tw' => 'Twi', 'ug' => 'Uighur; Uyghur', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'uz' => 'Uzbek',
		've' => 'Venda', 'vi' => 'Vietnamese', 'vo' => 'Volapük', 'cy' => 'Welsh','wa' => 'Walloon','wo' => 'Wolof', 'xh' => 'Xhosa', 'yi' => 'Yiddish', 'yo' => 'Yoruba', 'za' => 'Zhuang; Chuang', 'zu' => 'Zulu' );
	//$lang_codes = apply_filters( 'lang_codes', $lang_codes, $code );
	return strtr( $code, $lang_codes );
}


/**
 * Create options (if not exist yet) with array of pre-domain text in all languages
 * param 	reset_defaults		if yes create defaults (useful also if new langs installed)
 */
 
function alo_em_setup_predomain_texts( $reset_defaults = false ) {
	//Required pre-domain text
	require_once( 'languages/alo-easymail-predomain.php');
	
	global $alo_em_textpre;
	foreach ( $alo_em_textpre as $key => $sub ) {
		// add/update only if not exists or forced
		if ( !get_option($key.'_default') || $reset_defaults ) {
			update_option ( $key.'_default', $sub );
		}
	}
}

/**
 * Assign a subscriber to a language	
 */
function alo_em_assign_subscriber_to_lang ( $subscriber, $lang ) {
	global $wpdb;
	$wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
		            array ( 'lang' => $lang ),
		            array ( 'ID' => $subscriber )
		        );
}



/*************************************************************************
 * FUNCTIONS FOR FRONTEND 
 *************************************************************************/ 


/**
 * Get the selected Newsletters using 'get_posts'
 *
 * Include this code in your template file:
 * <?php if ( function_exists('alo_easymail_get_newsletters') ) alo_easymail_get_newsletters(); ?>
 * @param	arr		there is the custom arg "newsletter_status" (values: sent, sendable , paused); for other args see: http://codex.wordpress.org/Template_Tags/get_posts
 */
 
function alo_easymail_get_newsletters ( $args=false ) {
	global $wp_version;
	if ( !is_array( $args ) ) $args = array();
	$args["post_type"] = "newsletter";
	$status = ( isset( $args["newsletter_status"] ) && in_array( $args["newsletter_status"], array( 'sent', 'sendable', 'paused' ) ) ) ? $args["newsletter_status"] : 'sent';
	if ( version_compare ( $wp_version, '3.1', '>=' ) ) {
		$meta_1 = array( 'key' => '_easymail_status', 'value' => $status, 'compare' => '=' );
		$args['meta_query'] = array( $meta_1 );
	} else {
		$args['meta_key'] = '_easymail_status';
		$args['meta_value'] = $status;
		$args['meta_compare'] = '=';
	}	
	return get_posts ( $args );
}


/**
 * Get the selected Newsletters using a Shortcode
 *
 * Using 'alo_easymail_get_newsletters' to get posts.
 * Put [ALO-EASYMAIL-ARCHIVE] in a page or post
 * @param	arr		there are 3 custom args: 	"newsletter_status" (values: sent, sendable , paused), 
 *												"ul_class", 
 * 												"li_format" (values: 'title_date', 'date_title', 'title')
 *					for other args see: http://codex.wordpress.org/Template_Tags/get_posts 
 */
function alo_easymail_print_archive ( $atts=false, $content="" ) {
	global $post;
	$defaults = array( 'ul_class' => 'easymail-newsletter-archive', 'li_format' => 'title_date' );
	$args = wp_parse_args( $atts, $defaults );
	$newsletters = alo_easymail_get_newsletters( $args );
	if ( $newsletters ) { 
		echo "<ul class='". $args['ul_class'] ."'>";
		foreach( $newsletters as $post ) : setup_postdata( $post ); 
			switch ( $args['li_format'] ) : 
				case "date_title": ?>
					<li><span><?php echo get_the_date() ?></span> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
					<?php break;
				case "title": ?>						
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
					<?php break;					
				case "title_date":
				default: ?>						
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span><?php echo get_the_date() ?></span></li>					
		<?php endswitch;
		endforeach; 
		echo "</ul>";
		wp_reset_postdata();
	}
}
add_shortcode('ALO-EASYMAIL-ARCHIVE', 'alo_easymail_print_archive');

?>
