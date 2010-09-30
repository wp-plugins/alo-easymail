<?php
/*
Plugin Name: ALO EasyMail Newsletter
Plugin URI: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Description: To send e-mails and newsletters. Features: collect subcribers on registration or with an ajax widget, mailing lists, cron batch sending.
Version: 1.8.1
Author: Alessandro Massasso
Author URI: http://www.eventualo.net
*/

/*  Copyright 2010  Alessandro Massasso  (email : alo@eventualo.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Settings
 */
define("ALO_EM_FOOTER","<p style='margin-top:25px'>&raquo; <em>Please visit plugin site for more info and feedback: <a href='http://www.eventualo.net/blog/wp-alo-easymail-newsletter/' target='_blank'>www.eventualo.net</a></em></p>
	<p>&raquo; <em>If you use this plugin consider the idea of donating and supporting its development:</em></p><form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline'>
	<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
	<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='PayPal' border='0' type='image'>
	<img alt='' src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'><br>	</form>");
define("ALO_EM_INTERVAL_MIN", 10); 	// cron interval in minutes (default: 10) (NOTE: to apply the change you need to reactivate the plugin)
define("ALO_EM_MAX_ONE_SEND", 80);	// max mails sent in one sending (default: 80)


/**
 * Required functions
 */
require_once( 'alo-easymail_functions.php');


/**
 * On plugin activation 
 */
function ALO_em_install() {
    global $wpdb, $wp_roles;
    
	if (!get_option('ALO_em_template')) add_option('ALO_em_template', 'Hi [USER-NAME],<br /><br />
	    I have published a new post <strong>[POST-TITLE]</strong>.<br />[POST-EXCERPT]<br />Please visit my site [SITE-LINK] to read it and leave your comment about it.<br />
        Hope to see you online!<br /><br />[SITE-LINK]');
	if (!get_option('ALO_em_list')) add_option('ALO_em_list', '');
    if (!get_option('ALO_em_lastposts')) add_option('ALO_em_lastposts', 10);
    if (!get_option('ALO_em_dayrate')) add_option('ALO_em_dayrate', 1200);
	if (!get_option('ALO_em_sender_email')) {
		$admin_email = get_option('admin_email');
	    add_option('ALO_em_sender_email', $admin_email);
	}
	
	if (!get_option('ALO_em_optin_msg')) add_option('ALO_em_optin_msg', '' );
	if (!get_option('ALO_em_optout_msg')) add_option('ALO_em_optout_msg', '');	
	if (!get_option('ALO_em_lists_msg')) add_option('ALO_em_lists_msg', '');
	update_option('ALO_em_import_alert', "show" );
	if (!get_option('ALO_em_delete_on_uninstall')) add_option('ALO_em_delete_on_uninstall', 'no');
	if (!get_option('ALO_em_show_subscripage')) add_option('ALO_em_show_subscripage', 'no');
	    	    
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
    //-------------------------------------------------------------------------
	// TO MODIFY IF UPDATE NEEDED
	$database_version = '1.21';
	
	// Db version
	$installed_db = get_option('ALO_em_db_version');

	//if ( $database_version != $installed_db ) {
    	
    	$table_name = $wpdb->prefix . "easymail_subscribers";
    	
        if($wpdb->get_var("show tables like '$table_name'") != $table_name || $database_version != $installed_db) {
		    
			if( defined( 'DB_COLLATE' ) && constant( 'DB_COLLATE' ) != '' ) {
				$collate = constant( 'DB_COLLATE' );
			} else {
				$collate = constant( 'DB_CHARSET' );
			}
			
		    // Create the table structure
		    $sql = "CREATE TABLE ".$table_name." (
					    ID int(11) unsigned NOT NULL auto_increment,
					    email varchar(100) NOT NULL,
					    name varchar(100) NOT NULL,
					    join_date datetime NOT NULL,
					    active INT( 1 ) NOT NULL DEFAULT '0',
					    unikey varchar(24) NOT NULL,
					    lists varchar(255) DEFAULT '_',
					    PRIMARY KEY  (ID),
					    UNIQUE KEY  `email` (`email`)
					    ) DEFAULT CHARSET=".$collate.";
					    
					CREATE TABLE {$wpdb->prefix}easymail_sendings (
						ID int(11) unsigned NOT NULL auto_increment,
						start_at datetime DEFAULT NULL,
						last_at datetime DEFAULT NULL,
						user int(11) unsigned DEFAULT NULL,
						headers text DEFAULT NULL,
						subject varchar(250) DEFAULT NULL,
						content text DEFAULT NULL,
						recipients longtext DEFAULT NULL,
					    tracking varchar(10) DEFAULT NULL,						
						sent INT( 1 ) NOT NULL DEFAULT '0',
						PRIMARY KEY  (ID)
						) DEFAULT CHARSET=".$collate.";
						
					CREATE TABLE {$wpdb->prefix}easymail_trackings (
						ID int(11) unsigned NOT NULL auto_increment,
						newsletter int(11) unsigned DEFAULT NULL,
						email varchar(100) NOT NULL,
						type varchar(10) DEFAULT NULL,
						PRIMARY KEY  (ID)
						) DEFAULT CHARSET=".$collate.";
				    ";

		    dbDelta($sql);
		    update_option( "ALO_em_db_version", $database_version );
        }
	//}
	
	//-------------------------------------------------------------------------
	// Create/update the page with subscription
	
	// check if page already exists
	$my_page_id = get_option('ALO_em_subsc_page');
	
	$my_page = array();
    $my_page['post_title'] = 'Newsletter';
    $my_page['post_content'] = '[ALO-EASYMAIL-PAGE]';
    $my_page['post_status'] = 'publish';
    $my_page['post_author'] = 1;
    $my_page['comment_status'] = 'closed';
    $my_page['post_type'] = 'page';
    
    if ($my_page_id) {
        // if exists update
        $my_page['ID'] = $my_page_id;
        wp_update_post($my_page);
    } else {
        // insert the post into the database
        $my_page_id = wp_insert_post( $my_page );
        update_option('ALO_em_subsc_page', $my_page_id);
    }
    
    // add scheduled cleaner
    wp_schedule_event(time(), 'twicedaily', 'ALO_em_schedule');
    // add scheduled cron batch
    wp_schedule_event( time() +60, 'ALO_em_interval', 'ALO_em_batch' ); /* hourly */
    
    // default permission
	$wp_roles->add_cap( 'administrator', 'manage_easymail_options');
	$wp_roles->add_cap( 'administrator', 'manage_easymail_subscribers');		
	$wp_roles->add_cap( 'administrator', 'manage_easymail_newsletters');
	$wp_roles->add_cap( 'administrator', 'send_easymail_newsletters');
	$wp_roles->add_cap( 'editor', 'send_easymail_newsletters');
}
register_activation_hook(__FILE__,'ALO_em_install');


/**
 * For batch sending (every tot mins)
 */

function ALO_em_more_reccurences() {
	return array(
		'ALO_em_interval' => array('interval' => 59*(ALO_EM_INTERVAL_MIN), 'display' => 'EasyMail every ' .ALO_EM_INTERVAL_MIN. ' minutes' )
	);
}
add_filter('cron_schedules', 'ALO_em_more_reccurences');


/**
 * Clean the new subscription not yet activated after too much time
 */

function ALO_em_clean_no_actived() {
	global $wpdb;
	// delete subscribes not yet activated after 5 days
	$limitdate = date ("Y-m-d",mktime(0,0,0,date("m"),date("d")-5,date("Y")));
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE join_date <= '%s' AND active = '0'", $limitdate ) );
    //return $output;.
}

add_action('ALO_em_schedule', 'ALO_em_clean_no_actived');

add_action( 'ALO_em_batch' , 'ALO_em_batch_sending');


/**
 * On plugin adectivation 
 */
function ALO_em_uninstall() {
	global $wpdb, $wp_roles, $wp_version;
	
    // delete subscription page
    if ( version_compare ( $wp_version , '2.9', '>=' ) ) {
    	wp_delete_post( get_option('ALO_em_subsc_page'), true ); // skip trash, from wp 2.9
	} else {
	    wp_delete_post( get_option('ALO_em_subsc_page') );
	}
	// and the option with page id
	delete_option ('ALO_em_subsc_page');
	
    // delete scheduled cleaner
    wp_clear_scheduled_hook('ALO_em_schedule');
    // delete cron batch sending
    wp_clear_scheduled_hook('ALO_em_batch');
    
    // if required delete all plugin data (options, db tables)
   	if ( get_option('ALO_em_delete_on_uninstall') == "yes" ) {
   		$tables = array ( "easymail_sendings", "easymail_subscribers", "easymail_trackings" );
   		foreach ( $tables as $tab ) {
   			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$tab");
   		}
		// delete option from db
		$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '%ALO_em%'" );
	}
	
	// reset cap
	$roles = $wp_roles->get_names(); // get a list of values, containing pairs of: $role_name => $display_name
	foreach ( $roles as $rolename => $key) {
		$wp_roles->remove_cap( $rolename, 'manage_easymail_options');
		$wp_roles->remove_cap( $rolename, 'manage_easymail_subscribers');		
		$wp_roles->remove_cap( $rolename, 'manage_easymail_newsletters');
		$wp_roles->remove_cap( $rolename, 'send_easymail_newsletters');
	}
}
register_deactivation_hook( __FILE__, 'ALO_em_uninstall' );


/**
 * Add menu pages 
 */
function ALO_em_add_admin_menu() {
    add_options_page( __("Newsletter", "alo-easymail") , __("Newsletter", "alo-easymail"), 'manage_easymail_options', 'alo-easymail/alo-easymail_options.php');
	add_management_page ( __("Send newsletter", "alo-easymail"), __("Send newsletter", "alo-easymail"), 'send_easymail_newsletters', 'alo-easymail/alo-easymail_main.php');
	add_submenu_page('users.php', __("Newsletter subscribers", "alo-easymail"), __("Newsletter subscribers", "alo-easymail"), 'manage_easymail_subscribers', 'alo-easymail/alo-easymail_subscribers.php');
}

add_action('admin_menu', 'ALO_em_add_admin_menu');


//>>>>>>>>>>>>>>> added GAL
require_once('alo-easymail-widget.php');

add_action( 'show_user_profile', 'ALO_em_user_profile_optin' );
add_action( 'edit_user_profile', 'ALO_em_user_profile_optin' );

function ALO_em_user_profile_optin($user) { 

    // get the current setting
    //if (ALO_easymail_get_optin($user->ID)=='yes'){    // deleted ALO
    if (ALO_em_is_subscriber($user->user_email)){       // added ALO
        $optin_selected = 'selected';            
        $optout_selected = '';            
    }
    else{
        $optin_selected = '';            
        $optout_selected = 'selected';            
    }        
    
    $html = "<h3>". __("Newsletter", "alo-easymail") ."</h3>\n";
    $html .= "<table class='form-table'>\n";
    $html .= "  <tr>\n";
    $html .= "    <th><label for='alo_em_option'>". __("Receive Newsletters?", "alo-easymail")."</label></th>\n";
    $html .= "    <td>\n";
    $html .= "		<select name='alo_easymail_option' id='alo_easymail_option'>\n";
    $html .= "        <option value='yes' $optin_selected>". __("Yes", "alo-easymail")."</option>\n";
    $html .= "        <option value='no' $optout_selected>". __("No", "alo-easymail")."</option>\n";
    $html .= "      </select>\n";
    $html .= "    </td>\n";
    $html .= "  </tr>\n";
    $html .= "</table>\n";
 
	// add mailing lists html table
	$html .= ALO_em_html_mailinglists_table_to_edit ( $user->user_email, "form-table" );
 	
    echo $html;
}

add_action( 'personal_options_update', 'ALO_em_save_profile_optin' );
add_action( 'edit_user_profile_update', 'ALO_em_save_profile_optin' );

function ALO_em_save_profile_optin($user_id) {
     
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
    
    $user_info = get_userdata( $user_id );
    $user_email = $user_info->user_email;
    
    if (isset($_POST['alo_easymail_option'])) {
        if ( $_POST['alo_easymail_option'] == "yes") {
            ALO_em_add_subscriber( $user_email, $user_info->first_name ." ".$user_info->first_name, 1);
            
            // if subscribing, save also lists
        	$mailinglists = ALO_em_get_mailinglists( 'public' );
			if ($mailinglists) {
				$subscriber_id = ALO_em_is_subscriber( $user_email );
				foreach ( $mailinglists as $mailinglist => $val) {					
					if ( isset ($_POST['alo_em_profile_lists']) && is_array ($_POST['alo_em_profile_lists']) && in_array ( $mailinglist, $_POST['alo_em_profile_lists'] ) ) {
						ALO_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
					} else {
						ALO_em_delete_subscriber_from_list ( $subscriber_id, $mailinglist ); // remove from list
					}
				}
			}				
        } else {
            ALO_em_delete_subscriber_by_id( ALO_em_is_subscriber($user_email) );
        }
    }
}

// Widget activation

add_action( 'widgets_init', 'ALO_em_load_widgets' );

function ALO_em_load_widgets() {
	register_widget( 'ALO_Easymail_Widget' );
}
//<<<<<<<<<<<<<<< end added GAL


/**
 * Add javascript on admin side
 */
function ALO_add_admin_js() {
	if (isset($_GET['page']) && $_GET['page'] == "alo-easymail/alo-easymail_options.php") {
		wp_enqueue_script('jquery-ui-tabs');
		echo '<link rel="stylesheet" href="'.get_option ('siteurl').'/wp-content/plugins/alo-easymail/css/jquery.ui.tabs.css" type="text/css" media="print, projection, screen" />'."\n";
	}
}
add_action('admin_print_scripts', 'ALO_add_admin_js' );


/**
 * On plugin init
 */
 
function ALO_em_init_method() {
	// if required, exclude the easymail page from pages' list
	if ( get_option('ALO_em_show_subscripage') == "no" ) add_filter('get_pages','ALO_exclude_page');
	// load localization files
	load_plugin_textdomain ("alo-easymail", false, "alo-easymail/languages");
}
add_action( 'init', 'ALO_em_init_method' );


function ALO_exclude_page( $pages ) {
    for ( $i=0; $i<count($pages); $i++ ) {
		$page = & $pages[$i];
        if ($page->ID == get_option('ALO_em_subsc_page')) unset ($pages[$i]);
    }
    return $pages;
}

/**
 * Manage the newsletter subscription page
 */
function ALO_em_subscr_page ($atts, $content = null) {
	ob_start();
	include(ABSPATH . 'wp-content/plugins/alo-easymail/easymail-subscr-page.php');
	$contents = ob_get_contents();
	ob_end_clean();
	return $contents;
}
add_shortcode('ALO-EASYMAIL-PAGE', 'ALO_em_subscr_page');


/**
 * Add to favorites top menu
 */
function ALO_em_add_favorite ($actions) {
	if ( current_user_can( "send_easymail_newsletters") ) {
		$actions['edit.php?page=alo-easymail/alo-easymail_main.php'] = array( __("Newsletters", "alo-easymail") , 'send_easymail_newsletters' );
	}
	return $actions;
}
add_filter('favorite_actions', 'ALO_em_add_favorite', 10000); // inspired by http://wordpress.org/extend/plugins/favorites-menu-manager/



/**
 * Add a dashboard widget
 */
function ALO_em_dashboard_widget_function() {
	global $wpdb;
	echo "<h4>". __("Newsletters scheduled for sending", "alo-easymail")."</h4>";
	$news_on_queue =  $wpdb->get_results("SELECT * FROM {$wpdb->prefix}easymail_sendings WHERE sent = 0 ORDER BY ID ASC LIMIT 4");
	if (count($news_on_queue)) {
		echo "<ul>";
		$row_count = 1;
		foreach ($news_on_queue as $q) {
			echo "<li style='margin:10px auto'>";
			if ($row_count == 1) { // the 1st, now on sending
				echo '<img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/16-email-forward.png" title="'.__("now sending", "alo-easymail").'" alt="" style="vertical-align:text-bottom" />';
			} else {
				echo "#".($row_count - 1);
			}
			echo " <strong>" . stripslashes ( $q->subject ) ."</strong><br />";
			if ($row_count == 1) { 
				$q_recipients = unserialize( $q->recipients );
				$q_tot = count($q_recipients);
				$n_sent = 0;
				foreach ($q_recipients as $qr) {
			   		if ( isset($qr['result']) ) $n_sent ++;
			   	}
				echo __("Progress", "alo-easymail") .": " . round($n_sent*100/ $q_tot ) . " %<br />" ;			
			}
			echo "<em>".__("Added on", "alo-easymail") . " ". date("d/m/Y", strtotime($q->start_at))." h.".date("H:i", strtotime($q->start_at)) . " - "; 
			echo __("Scheduled by", "alo-easymail") . " ". get_user_meta($q->user, 'nickname',true). "</em>";
		    echo "</li>";
			$row_count++;
		}
		echo "</ul>";
	} else {
		echo "<p>". __("There are no newsletters in queue", "alo-easymail") . ".</p>";
	}
	echo "<br /><h4>". __("Subscribers", "alo-easymail") ."</h4>";
	list ( $total, $active, $noactive ) = ALO_em_count_subscribers ();
	if ($total) {
		echo "<p>". sprintf( __("There are %d subscribers: %d activated, %d not activated", "alo-easymail"), $total, $active, $noactive ) . ".</p>";
	} else {
		echo "<p>". __("No subscribers", "alo-easymail") . ".</p>";
	}
} 

function ALO_em_add_dashboard_widgets() {
	if ( current_user_can ( 'manage_easymail_subscribers' ) && current_user_can ( 'manage_easymail_newsletters' ) ) {
		wp_add_dashboard_widget('alo-easymail-widget', 'EasyMail Newsletter', 'ALO_em_dashboard_widget_function');	
	}
} 
add_action('wp_dashboard_setup', 'ALO_em_add_dashboard_widgets' );


/**
 * SHOW the optin/optout on registration form
 */
function ALO_em_show_registration_optin () {
	echo '<p style="margin-bottom:16px"><input type="checkbox" id="alo_em_opt" name="alo_em_opt" value="yes" class="input" checked="checked" /> ';
	echo '<label for="alo_em_opt" >' . __("Receive Newsletters?", "alo-easymail") .'</label></p>';
}
add_action('register_form','ALO_em_show_registration_optin');


/**
 * SAVE the optin/optout on registration form
 */
function ALO_em_save_registration_optin ($user_id, $password="", $meta=array())  {
	$user = get_userdata($user_id);
	if (!empty($user->first_name) && !empty($user->last_name)) {
		$name = $user->first_name.' '.$user->last_name;	
	} else {
		$name = $user->display_name;
	}
	if ( isset ($_POST['alo_em_opt']) && $_POST['alo_em_opt'] == "yes" ) {
		ALO_em_add_subscriber( $user->user_email, $name , 1);
	}
}
add_action( 'user_register', 'ALO_em_save_registration_optin' );


?>
