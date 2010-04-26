<?php
/*
Plugin Name: ALO EasyMail Newsletter
Plugin URI: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Description: Allows you to send e-mails and newsletters to your subscribers, to registered users and to other e-mail addresses. Includes a widget to collect subscribers.
Version: 1.6
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
define("ALO_EM_FOOTER","<p style='margin-top:25px'>&raquo; <em>Please visit my site and leave your feedback: <a href='http://www.eventualo.net/blog/wp-alo-easymail-newsletter/' target='_blank'>www.eventualo.net</a></em></p>
	<p>&raquo; <em>If you use this plugin consider the idea of donating and supporting its development:</em></p><form action='https://www.paypal.com/cgi-bin/webscr' method='post style='display:inline'>
	<input name='cmd' value='_s-xclick' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
	<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='PayPal' border='0' type='image'>
	<img alt='' src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'><br>	</form>");
define("ALO_EM_INTERVAL_MIN", 10); 	// cron interval in minutes (default: 10) (NOTE: to apply the change you need to reactivate the plugin)
define("ALO_EM_MAX_ONE_SEND", 100);	// max mails sent in one sending (default: 100)

/**
 * On plugin activation 
 */
function ALO_em_install() {
    global $wpdb;
    
	if (!get_option('ALO_em_template')) add_option('ALO_em_template', 'Hi [USER-NAME],<br /><br />
	    I have published a new post <strong>[POST-TITLE]</strong>.<br />[POST-EXCERPT]<br />Please visit my site [SITE-LINK] to read it and leave your comment about it.<br />
        Hope to see you online!<br /><br />[SITE-LINK]');
	if (!get_option('ALO_em_list')) add_option('ALO_em_list', '');
    if (!get_option('ALO_em_lastposts')) add_option('ALO_em_lastposts', 10);
    if (!get_option('ALO_em_dayrate')) add_option('ALO_em_dayrate', 1500);
	if (!get_option('ALO_em_sender_email')) {
		$admin_email = get_option('admin_email');
	    add_option('ALO_em_sender_email', $admin_email);
	}
	
	if (!get_option('ALO_em_optin_msg')) add_option('ALO_em_optin_msg', 'Yes, I would like to receive the Newsletter');
	if (!get_option('ALO_em_optout_msg')) add_option('ALO_em_optout_msg', 'No, please do not email me');
	    
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
    //-------------------------------------------------------------------------
	// TO MODIFY IF UPDATE NEEDED
	$database_version = '1.12';
	
	// Db version
	$installed_db = get_option('ALO_em_db_version');

	//if ( $database_version != $installed_db ) {
    	
    	$table_name = $wpdb->prefix . "easymail_subscribers";
    	
        if($wpdb->get_var("show tables like '$table_name'") != $table_name || $database_version != $installed_db) {
		    
		
		    // Create the table structure
		    $sql = "CREATE TABLE ".$table_name." (
					    ID int(11) unsigned NOT NULL auto_increment,
					    email varchar(100) NOT NULL,
					    name varchar(100) NOT NULL,
					    join_date datetime NOT NULL,
					    active INT( 1 ) NOT NULL DEFAULT '0',
					    unikey varchar(24) NOT NULL,
					    PRIMARY KEY  (ID),
					    UNIQUE KEY  `email` (`email`)
					    );
					    
					CREATE TABLE {$wpdb->prefix}easymail_sendings (
						ID int(11) unsigned NOT NULL auto_increment,
						start_at datetime DEFAULT NULL,
						last_at datetime DEFAULT NULL,
						user int(11) unsigned DEFAULT NULL,
						headers text DEFAULT NULL,
						subject varchar(250) DEFAULT NULL,
						content text DEFAULT NULL,
						recipients mediumtext DEFAULT NULL,
						sent INT( 1 ) NOT NULL DEFAULT '0',
						PRIMARY KEY  (ID)
						);
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
    $my_page['post_title'] = 'Newsletter Subscription';
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
        // The page's ID for later updates
        add_option('ALO_em_subsc_page', $my_page_id);
    }
    
    // add scheduled cleaner
    wp_schedule_event(time(), 'twicedaily', 'ALO_em_schedule');
    // add scheduled cron batch
    wp_schedule_event( time() +60, 'ALO_em_interval', 'ALO_em_batch' ); /* hourly */
}

register_activation_hook(__FILE__,'ALO_em_install');


/**
 * For batch sending (every tot mins)
 */

function ALO_em_more_reccurences() {
	return array(
		'ALO_em_interval' => array('interval' => 60*(ALO_EM_INTERVAL_MIN), 'display' => 'ALO EasyMail interval')
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
    // delete subscription page
    wp_delete_post( get_option('ALO_em_subsc_page') );
    // delete scheduled cleaner
    wp_clear_scheduled_hook('ALO_em_schedule');
    // delete cron batch sending
    wp_clear_scheduled_hook('ALO_em_batch');
}
register_deactivation_hook( __FILE__, 'ALO_em_uninstall' );


/**
 * Add menu pages 
 */
function ALO_em_add_admin_menu() {
    add_options_page('EasyMail Newsletter', 'EasyMail Newsletter', 8, 'alo-easymail-options', 'ALO_em_option_page');
	//add_management_page ('Alo EasyMail', 'Alo EasyMail', 8, 'alo-easymail/alo-easymail_main.php'); deleted GAL
    // GAL change access level to 3 if editors can send emails
    if (get_option('alo_em_editor_ok')=='checked') $access_level = 3; else $access_level = 8; // added GAL
	add_management_page ('EasyMail Newsletter', 'EasyMail Newsletter', $access_level, 'alo-easymail/alo-easymail_main.php'); // added GAL

	add_submenu_page('users.php', 'Subscribers', 'Subscribers', 8, 'alo-easymail/alo-easymail_subscribers.php');
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
    
    $html = "<h3>ALO EasyMail Option</h3>\n";
    $html .= "<table class='form-table'>\n";
    $html .= "  <tr>\n";
    $html .= "    <th><label for='alo_em_option'>Receive Newsletters?</label></th>\n";
    $html .= "    <td>\n";
    $html .= "		<select name='alo_easymail_option' id='alo_easymail_option'>\n";
    $html .= "        <option value='yes' $optin_selected>Yes</option>\n";
    $html .= "        <option value='no' $optout_selected>No</option>\n";
    $html .= "      </select>\n";
    $html .= "    </td>\n";
    $html .= "  </tr>\n";
    $html .= "</table>\n";
 
    echo $html;
}

add_action( 'personal_options_update', 'ALO_em_save_profile_optin' );
add_action( 'edit_user_profile_update', 'ALO_em_save_profile_optin' );

function ALO_em_save_profile_optin($user_id) {
     global $user_ID, $user_email;
     
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
    
    // added ALO
    if (isset($_POST['alo_easymail_option'])) {
        if ( $_POST['alo_easymail_option'] == "yes") {
            ALO_em_add_subscriber($user_email, get_usermeta($user_ID, 'first_name')." ".get_usermeta($user_ID,'last_name') , 1);
        } else{
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
 * Option page 
 */
function ALO_em_option_page() { 
    global $wp_version;
    
    if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
	    if(isset($_POST['content'])) {
	        $main_content = stripslashes($_REQUEST['content']);
            $main_content = str_replace("\n", "<br />", $main_content);
	        update_option('ALO_em_template', $main_content);
	    }
	    if(isset($_POST['alo_em_editor_ok'])) update_option('alo_em_editor_ok', 'checked'); else update_option('alo_em_editor_ok', '');	// added GAL
	    if(isset($_POST['emails_add'])) update_option('ALO_em_list', trim($_POST['emails_add']));
	    if(isset($_POST['sender_email'])) update_option('ALO_em_sender_email', trim($_POST['sender_email']));
	    if(isset($_POST['lastposts']) && (int)$_POST['lastposts'] > 0) update_option('ALO_em_lastposts', trim($_POST['lastposts']));
	   	if(isset($_POST['dayrate']) && (int)$_POST['dayrate'] >= 300 && (int)$_POST['dayrate'] <= 7000 ) update_option('ALO_em_dayrate', trim($_POST['dayrate']));
	    echo '<div id="message" class="updated fade"><p>Updated.</p></div>';
    }?>
    
    <div class="wrap">
    <h2>Alo EasyMail Newsletter Options</h2>
    
    <form action="" method="post">
    
    <h4>General settings</h4>
    <p><label for="lastposts">Number of last posts to display:</label>
    <input type="text" name="lastposts" value="<?php echo get_option('ALO_em_lastposts') ?>" id="lastposts" size="2" maxlength="2" /></p>
    
    <p><label for="sender_email">Sender's email address:</label>
    <input type="text" name="sender_email" value="<?php echo get_option('ALO_em_sender_email') ?>" id="sender_email" size="30" maxlength="100" /></p>
    
    <p><?php echo 'Default template for the email content:'; ?></p>
    <?php
    // include found at http://blog.zen-dreams.com/en/2008/11/06/how-to-include-tinymce-in-your-wp-plugin/ 
    // and http://blog.zen-dreams.com/en/2009/06/30/integrate-tinymce-into-your-wordpress-plugins/

    if($wp_version >= '2.8') {
        wp_enqueue_script( 'common' );
	    wp_enqueue_script( 'jquery-color' );
	    wp_print_scripts('editor');
	    if (function_exists('add_thickbox')) add_thickbox();
	    wp_print_scripts('media-upload');
	    if (function_exists('wp_tiny_mce')) wp_tiny_mce();
	    wp_admin_css();
	    wp_enqueue_script('utils');
	    do_action("admin_print_styles-post-php");
	    do_action('admin_print_styles');

    } else {

        wp_admin_css('thickbox');
        wp_print_scripts('jquery-ui-core');
        wp_print_scripts('jquery-ui-tabs');
        wp_print_scripts('post');
        wp_print_scripts('editor');
        add_thickbox();
        wp_print_scripts('media-upload');
        if (function_exists('wp_tiny_mce')) wp_tiny_mce();
    }
    ?>
	<div id="poststuff">
    <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
    <?php the_editor(get_option('ALO_em_template')); ?>
    </div></div>
    
    <h4>Permissions</h4>
    <!-- added GAL -->
    <p style='margin-top:20px;'>To allow editors to send emails, check this option:</p>
    <p><input type="checkbox" name="alo_em_editor_ok" id="alo_em_editor_ok" value="checked" <?php echo get_option('ALO_em_editor_ok'); ?> />
    <label for="alo_em_editor_ok">Editors can send emails</label></p>
    <!-- end added GAL -->
    
    <h4>Batch sending</h4>
    <p><label for="dayrate">Maximum number of emails that can be sent in a 24-hr period:</label>
    <input type="text" name="dayrate" value="<?php echo get_option('ALO_em_dayrate') ?>" id="dayrate" size="5" maxlength="5" /> (300 - 7000)</p>
    <div style="background-color:#ddd;padding:2px 20px 10px 20px"><h5>Important advice to calculate the best limit</h5>
    <ol style="font-size:80%;">
    	<li>Ask your provider the cut-off of emails you can send per day. Multiplying the hourly limit by 24 is not the right way to calculate it: very often the resulting number is much higher than the actual cut-off.</li>
    	<li>Subtract from this cut-off the number of emails you want to send from your blog (e.g. registration procedures, activation and unsubscribing of EasyMail, notices from other plugins etc.).</li>
    	<li>If in doubt, just choose a number definitely lower than the cut-off: you'll have more chances to have your mail delivered, and less chances to end up in a blacklist...</li>
    	<li>For more info, visit the FAQ of the site.</li>      	  
    </ol>
    </div>
    
    <p class="submit">
    <input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
    <span id="autosave"></span>
    <input type="submit" name="submit" value="<?php echo 'Update'; ?>" style="font-weight: bold;" />
    </p>
    </form>
    
    <p><?php echo ALO_EM_FOOTER; ?></p>
    </div>
<?php	
} 


/**
 * Exclude the easymail page from pages' list
 */ 
function ALO_init_delete_page() {
	add_filter('get_pages','ALO_exclude_page');
}
add_action( 'init', 'ALO_init_delete_page' );

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


/*************************************************************************
 * SUBSCRIPTION FUNCTIONS
 *************************************************************************/ 

// Check is there is already a subscriber with that email and return ID subscriber

function ALO_em_is_subscriber($email) {
    global $wpdb;
    $is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return (($is_subscriber)? $is_subscriber : 0); // ID in db tab subscribers
} 


// Check the state of a subscriber (active/not-active)

function ALO_em_check_subscriber_state($email) {
    global $wpdb;
    $is_activated = $wpdb->get_var( $wpdb->prepare("SELECT active FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return $is_activated;
} 


// Modify the state of a subscriber (active/not-active) (BY ADMIN)

function ALO_em_edit_subscriber_state_by_id($id, $newstate) {
    global $wpdb;
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                array ( 'active' => $newstate ),
                                array ( 'ID' => $id)
                            );
    return $output;
} 


// Modify the state of a subscriber (active/not-active) (BY SUBSCRIBER)

function ALO_em_edit_subscriber_state_by_email($email, $newstate="1", $unikey) {
    global $wpdb;
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                array ( 'active' => $newstate ),
                                array ( 'email' => $email, 'unikey' => $unikey )
                            );
    return $output;
} 


// Add a new subscriber 

function ALO_em_add_subscriber($email, $name, $newstate=0) {
    global $wpdb;
 
    // if there is NOT a subscriber with this email address: add new subscriber and send activation email
    if (ALO_em_is_subscriber($email) == false){
        $unikey = substr(md5(uniqid(rand(), true)), 0,24);    // a personal key to manage the subscription
           
        // try to send activation mail, otherwise will not add subscriber
        if ($newstate == 0) {
            if ( !ALO_em_send_activation_email($email, $name, $unikey) ) return false;
        }
        
        $wpdb->insert   ("{$wpdb->prefix}easymail_subscribers",
                        array( 'email' => $email, 'name' => $name, 'join_date' => date("Y-m-d H:i:s"), 'active' => $newstate, 'unikey' => $unikey)
                        );
        return true;
        
    } else {
        // if there is ALREADY a subscriber with this email address, and if is NOT confirmed yet: re-send an activation email
        if ( ALO_em_check_subscriber_state($email) == 0) {
            // retrieve existing unique key 
            $exist_unikey = $wpdb->get_var( $wpdb->prepare("SELECT unikey FROM {$wpdb->prefix}easymail_subscribers WHERE ID='%s' LIMIT 1", ALO_em_is_subscriber($email) ) );
            
            if ( ALO_em_send_activation_email($email, $name, $exist_unikey) ) {
                // update join date to today
                $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                            array ( 'join_date' => date("Y-m-d H:i:s") ),
                                            array ( 'ID' => ALO_em_is_subscriber($email) )
                                        );
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
} 


// Delete a subscriber (BY ADMIN/REGISTERED-USER)

function ALO_em_delete_subscriber_by_id($id) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id ) );
    return $output;
} 


// Delete a subscriber (BY SUBSCRIBER)

function ALO_em_delete_subscriber_by_email($email, $unikey) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey ) );
    return $output;
} 


// Check if can access subscription page (BY SUBSCRIBER) (check EMAIL<->UNIKEY)
// @action  activate|unsubscribe

function ALO_em_can_access_subscrpage ($email, $unikey) {
    global $wpdb;
    // check if email and unikey match
    $check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey) );
    if ($check == true) {
        return true;
    } else {
        return false;        
    }
    //return true;
} 


// Send email with activation link

function ALO_em_send_activation_email($email, $name, $unikey) {
    // Headers
    $mail_sender = "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
    $headers =  "MIME-Version: 1.0\n";
    $headers .= "From: ".get_option('blogname')." <".$mail_sender.">\n";
    $headers .= "Content-Type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
    // Subject
    $subject = "Confirm your subscription to " .get_option('blogname') . " Newsletter";
    // Main content
    $content = "Hi ". $name ."\r\nto complete your subscription to ".get_option('blogname') ."'s newsletter you need to click the follow link (or paste it in the address bar of your browser):\r\n";

 	$div_email = explode("@", $email); // for link

    $content .= get_option ('home') . "/?page_id=". get_option('ALO_em_subsc_page'). "&ac=activate&em1=" . $div_email[0] . "&em2=" . $div_email[1] . "&uk=" . $unikey . "\r\n";
    $content .= "If you didn't require this subscription simply ignore this message.\r\n";
    $content .= "Thank you\r\n".get_option('blogname')."\r\n";
    
    //echo "<br />".$headers."<br />".$subscriber->email."<br />". $subject."<br />".  $content ."<hr />" ; // DEBUG
    $sending = @wp_mail( $email, $subject, $content, $headers);  
    return ($sending ? true : false);
} 



/*************************************************************************
 * AJAX 'SACK' FUNCTION
 *************************************************************************/ 

add_action('wp_head', 'ALO_em_ajax_js' );


function ALO_em_ajax_js()
{
  // use JavaScript SACK library for Ajax
  wp_print_scripts( array( 'sack' ));

  // Define custom JavaScript function
?>
<script type="text/javascript">
//<![CDATA[
function alo_em_user_form ( opt )
{
  // updating...
  document.getElementById('alo_easymail_widget_feedback').innerHTML = 'Updating...';
  document.getElementById('alo_easymail_widget_feedback').className = 'alo_easymail_widget_error';
  
   var mysack = new sack( 
       "<?php echo get_option ('home'); ?>/wp-admin/admin-ajax.php" );    

  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "alo_em_user_form_check" );
  mysack.setVar( "alo_easymail_option", opt );
  mysack.onError = function() { alert('Ajax error' )};
  mysack.runAJAX();

  return true;

} 
function alo_em_pubblic_form ()
{
  document.alo_easymail_widget_form.submit.value="sending...";
  document.alo_easymail_widget_form.submit.disabled = true;
  
   var mysack = new sack( 
       "<?php echo get_option ('home'); ?>/wp-admin/admin-ajax.php" );    

  mysack.execute = 1;
  mysack.method = 'POST';
  mysack.setVar( "action", "alo_em_pubblic_form_check" );
  mysack.setVar( "alo_em_opt_name", document.alo_easymail_widget_form.alo_em_opt_name.value );
  mysack.setVar( "alo_em_opt_email", document.alo_easymail_widget_form.alo_em_opt_email.value );
  mysack.onError = function() { alert('Ajax error' )};
  mysack.runAJAX();

  return true;

} 
//]]>
</script>
<?php
} // end ALO_em_ajax_js

add_action('wp_ajax_alo_em_user_form_check', 'ALO_em_user_form_callback');				// logged in
add_action('wp_ajax_nopriv_alo_em_pubblic_form_check', 'ALO_em_pubblic_form_callback'); 	// pubblic, no logged in

// For logged-in users
function ALO_em_user_form_callback() {
	global $wpdb, $user_ID, $user_email, $current_user;
	get_currentuserinfo();
	//die ("alert(\"".$_POST['alo_easymail_option']."\")");
   	if ( $user_ID && isset($_POST['alo_easymail_option'])) {
        if ( $_POST['alo_easymail_option'] == "yes") {
            ALO_em_add_subscriber($user_email, $current_user->user_firstname." ". $current_user->user_lastname , 1);
        } else {
            ALO_em_delete_subscriber_by_id( ALO_em_is_subscriber($user_email) );
        }
    	// Compose JavaScript for return
		$feedback = "";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = 'Successfully updated.';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = 'alo_easymail_widget_ok';";
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
        //if (!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/' , trim($_POST['alo_em_opt_email']) )) {
        if (!is_email(trim($_POST['alo_em_opt_email']))) {
            $error_on_adding .= "The e-email address is not correct.<br />";
        }
        if ( stripslashes(trim($_POST['alo_em_opt_name'])) == "") {
            $error_on_adding .= "The name field is blank.<br />";
        }
        if ($error_on_adding == "") { // if no error
            // try to add new subscriber (and send mail if necessary) and return TRUE if success
            if ( ALO_em_add_subscriber( stripslashes(trim($_POST['alo_em_opt_email'])), trim($_POST['alo_em_opt_name']), 0) ) {
                $just_added = true;
            } else {
                $error_on_adding = "Error during sending: please try again.<br />";
            }
        } 
        if ($just_added == true) {
			$output = "<strong>Success!</strong><br />Now we are sending an e-mail to you. Check your e-mail account and click the activation link in the e-mail to complete your subscription.";        
       		$classfeedback = "alo_easymail_widget_ok";
        } else {
			$output = $error_on_adding;
        	$classfeedback = "alo_easymail_widget_error";
       	}
		// Compose JavaScript for return
		$feedback = "";
		$feedback .= "document.alo_easymail_widget_form.submit.disabled = false;";
		$feedback .= "document.alo_easymail_widget_form.submit.value = 'Subscribe';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '$output';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = '$classfeedback';";
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
function ALO_em_add_new_batch ( $headers, $user_ID, $subject, $content, $recipients ) {
	global $wpdb;
	$add_newsletter = $wpdb->insert(
                "{$wpdb->prefix}easymail_sendings", 
                array( 'start_at' => date("Y-m-d H:i:s"), 'last_at' => date("Y-m-d H:i:s"), 'headers' => $headers, 'user' => $user_ID, 'subject' => $subject, 
                'content' => $content, 'sent' => '0', 'recipients' => $recipients )
            );
    return $add_newsletter;
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
	$diff_time = strtotime(date("Y-m-d H:i:s")) - strtotime($sending_info->last_at);
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

		$updated_content = str_replace("\n", "<br />", $updated_content);
		       
        // TAG: [USER-NAME]
        if ($recipients[$r]['name']) {
            $updated_content = str_replace("[USER-NAME]", $recipients[$r]['name'], $updated_content);       
        } else {
            $updated_content = str_replace("[USER-NAME]", "", $updated_content);
        }            
        
        //>>>>>>> added GAL
        // TAG: [USER-FIRST-NAME]
        if ($recipients[$r]['firstname']) {
            $updated_content = str_replace("[USER-FIRST-NAME]", $recipients[$r]['firstname'], $updated_content);       
        } else {
            $updated_content = str_replace("[USER-FIRST-NAME]", "", $updated_content);
        }            
        //<<<<<<<<< end added GAL

	    // Unsubscribe link, only if subscriber
		if ($recipient['unikey']) {
			$div_email = explode("@", $recipients[$r]['email']); // for link
		    $updated_content .= "<p><em>You have received this message because you subscribed our newsletter. If you want to unsubscribe please ";
		    $updated_content .= "<a href='".get_option ('home') . "/?page_id=". get_option('ALO_em_subsc_page');
		    $updated_content .= "&amp;ac=unsubscribe&amp;em1=" .$div_email[0] . "&amp;em2=" .$div_email[1] . "&amp;uk=" .$recipients[$r]['unikey']."'>click here</a>.";            
		    $updated_content .= "</em></p>";
	    }
                      
        // ---- Send MAIL ----
        $mail_engine = @wp_mail($recipients[$r]['email'], $sending_info->subject, $updated_content, $sending_info->headers);  
        
        if($mail_engine) {
            $recipients[$r]['result'] = 1;
        } else {
            $recipients[$r]['result'] = -1;
        }
        
        // add as sent
  		$n_recs ++;
  		
        // sent to all of this sending? or too much sending stop sending!
        if ($n_recs == $tot_recs || $n_recs >= ALO_EM_MAX_ONE_SEND) break;
        
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
                  array( 'last_at' => date("Y-m-d H:i:s"), 'recipients' => serialize ($recipients), 'sent' => $has_finished ),
                  array( 'ID' => $sending_info->ID )
                 );
   	   	
}

?>
