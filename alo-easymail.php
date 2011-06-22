<?php
/*
Plugin Name: ALO EasyMail Newsletter
Plugin URI: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Description: To send newsletters. Features: collect subcribers on registration or with an ajax widget, mailing lists, cron batch sending, multilanguage.
Version: 2.0.3
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


/*
 * Cron interval in minutes (default: 10)
 * If you like to modify the interval, add following line in your wp-config.php:	
 * define( "ALO_EM_INTERVAL_MIN", 8 );
 * NOTE: to apply the change you need to reactivate the plugin!
 */
if ( !defined( 'ALO_EM_INTERVAL_MIN' ) ) define( "ALO_EM_INTERVAL_MIN", 10 );

/**
 * Other stuff
 */
define( "ALO_EM_PLUGIN_DIR", basename( dirname(__FILE__) ) );
define( "ALO_EM_PLUGIN_URL", WP_PLUGIN_URL ."/" . ALO_EM_PLUGIN_DIR );
define( "ALO_EM_PLUGIN_ABS", WP_PLUGIN_DIR . "/". ALO_EM_PLUGIN_DIR );

/**
 * Required files
 */
require_once( ABSPATH . WPINC .'/registration.php' );
require_once( 'alo-easymail_functions.php' );
require_once( 'alo-easymail-widget.php' );


/**
 * File including custom hooks. See plugin homepage or inside that file for more info.
 */
if ( @file_exists ( ALO_EM_PLUGIN_ABS.'/alo-easymail_custom-hooks.php' ) ) include ( ALO_EM_PLUGIN_ABS. '/alo-easymail_custom-hooks.php' );


/**
 * On plugin activation 
 */
function alo_em_install() {
    global $wpdb, $wp_roles;
    
	if (!get_option('alo_em_template')) add_option('alo_em_template', 'Hi [USER-NAME],<br /><br />
	    I have published a new post <strong>[POST-TITLE]</strong>.<br />[POST-EXCERPT]<br />Please visit my site [SITE-LINK] to read it and leave your comment about it.<br />
        Hope to see you online!<br /><br />[SITE-LINK]');
	if (!get_option('alo_em_list')) add_option('alo_em_list', '');
    if (!get_option('alo_em_lastposts')) add_option('alo_em_lastposts', 10);
    if (!get_option('alo_em_dayrate')) add_option('alo_em_dayrate', 1500);
    if (!get_option('alo_em_batchrate')) add_option('alo_em_batchrate', 60);
    if (!get_option('alo_em_sleepvalue')) add_option('alo_em_sleepvalue', 0);
	if (!get_option('alo_em_sender_email')) {
		$admin_email = get_option('admin_email');
	    add_option('alo_em_sender_email', $admin_email);
	}
	if (!get_option('alo_em_sender_name')) {
		$sender_name = get_option('blogname');
	    add_option('alo_em_sender_name', $sender_name );
	}
		
	update_option('alo_em_import_alert', "show" );
	update_option('alo_em_timeout_alert', "show" );
	if (!get_option('alo_em_delete_on_uninstall')) add_option('alo_em_delete_on_uninstall', 'no');
	if (!get_option('alo_em_show_subscripage')) add_option('alo_em_show_subscripage', 'no');
	if (!get_option('alo_em_embed_css')) add_option('alo_em_embed_css', 'no');
	if (!get_option('alo_em_no_activation_mail')) add_option('alo_em_no_activation_mail', 'no');
	if (!get_option('alo_em_show_credit_banners')) add_option('alo_em_show_credit_banners', 'yes');
	if (!get_option('alo_em_filter_br')) add_option('alo_em_filter_br', 'no');
	if (!get_option('alo_em_filter_the_content')) add_option('alo_em_filter_the_content', 'yes');
	if (!get_option('alo_em_js_rec_list')) add_option('alo_em_js_rec_list', 'no');
	
	alo_em_setup_predomain_texts( false );
		    	    
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
    //-------------------------------------------------------------------------
	// TO MODIFY IF UPDATE NEEDED
	$database_version = '2014';
	
	// Db version
	$installed_db = get_option('alo_em_db_version');
	
	$missing_table = false; // Check if tables not yet installed
	$tables = array ( $wpdb->prefix."easymail_subscribers", $wpdb->prefix."easymail_recipients", $wpdb->prefix."easymail_stats" );
	foreach ( $tables as $table_name ) {
		if ( $wpdb->get_var("show tables like '$table_name'") != $table_name ) $missing_table = true;
	}
	
    if ( $missing_table || $database_version != $installed_db ) {
	    
		if( defined( 'DB_COLLATE' ) && constant( 'DB_COLLATE' ) != '' ) {
			$collate = constant( 'DB_COLLATE' );
		} else {
			$collate = constant( 'DB_CHARSET' );
		}
		
	    // Create the table structure
	    $sql = "CREATE TABLE {$wpdb->prefix}easymail_subscribers (
				    ID int(11) unsigned NOT NULL auto_increment,
				    email varchar(100) NOT NULL,
				    name varchar(100) NOT NULL,
				    join_date datetime NOT NULL,
				    active INT( 1 ) NOT NULL DEFAULT '0',
				    unikey varchar(24) NOT NULL,
				    lists varchar(255) DEFAULT '|',
				    lang varchar(5) DEFAULT NULL,					    
				    PRIMARY KEY  (ID),
				    UNIQUE KEY  `email` (`email`)
				    ) DEFAULT CHARSET=".$collate.";

				CREATE TABLE {$wpdb->prefix}easymail_recipients (
					ID int(11) unsigned NOT NULL auto_increment,
					newsletter int(11) unsigned NOT NULL,
					email varchar(100) NOT NULL, 
					result varchar(3) NOT NULL DEFAULT '0',	
					PRIMARY KEY  (ID)
					) DEFAULT CHARSET=".$collate.";

				CREATE TABLE {$wpdb->prefix}easymail_stats (
					ID int(11) unsigned NOT NULL auto_increment,
					recipient int(11) unsigned NOT NULL,
					newsletter int(11) unsigned NOT NULL,
					added_on datetime NOT NULL,
					request varchar(225) DEFAULT NULL,
					PRIMARY KEY  (ID)
					) DEFAULT CHARSET=".$collate.";
									
			    ";  
				
	    dbDelta($sql);
	    
		// Update the old "lists" field if upgrading from v. 1.x
		if ( $installed_db < 2012 ) {
			$wpdb->query( "UPDATE ".$table_name." SET lists = REPLACE( lists, '_', '|');" );
			$wpdb->query( "UPDATE {$wpdb->options} SET option_name = REPLACE( option_name, 'ALO_em_', 'alo_em_');" );
		}	    
		
	    update_option( "alo_em_db_version", $database_version );
    }
	
	//-------------------------------------------------------------------------
	// Create/update the page with subscription
	
	// check if page already exists
	$my_page_id = get_option('alo_em_subsc_page');
	
	$my_page = array();
    $my_page['post_title'] = 'Newsletter';
    $my_page['post_content'] = '[ALO-EASYMAIL-PAGE]';
    $my_page['post_status'] = 'publish';
    $my_page['post_author'] = 1;
    $my_page['comment_status'] = 'closed';
    $my_page['post_type'] = 'page';
    
    if ( !$my_page_id ) { // insert the post into the database
        $my_page_id = wp_insert_post( $my_page );
        update_option('alo_em_subsc_page', $my_page_id);
    }
    
    // add scheduled cleaner
    wp_schedule_event(time(), 'twicedaily', 'alo_em_schedule');
    // add scheduled cron batch
    wp_schedule_event( time() +60, 'alo_em_interval', 'alo_em_batch' );
    
    // default permission
	$wp_roles->add_cap( 'administrator', 'manage_easymail_options');
	$wp_roles->add_cap( 'administrator', 'manage_easymail_subscribers');		
	//$wp_roles->add_cap( 'administrator', 'manage_easymail_newsletters');
	//$wp_roles->add_cap( 'administrator', 'send_easymail_newsletters');
}
register_activation_hook(__FILE__,'alo_em_install');


/**
 * For batch sending (every tot mins)
 */
function alo_em_more_reccurences() {
	return array(
		'alo_em_interval' => array('interval' => 59*(ALO_EM_INTERVAL_MIN), 'display' => 'EasyMail every ' .ALO_EM_INTERVAL_MIN. ' minutes' )
	);
}
add_filter('cron_schedules', 'alo_em_more_reccurences');


/**
 * Clean the new subscription not yet activated after too much time
 */
function alo_em_clean_no_actived() {
	global $wpdb;
	// delete subscribes not yet activated after 5 days
	$limitdate = date ("Y-m-d",mktime(0,0,0,date("m"),date("d")-5,date("Y")));
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE join_date <= '%s' AND active = '0'", $limitdate ) );
    //return $output;.
}

add_action('alo_em_schedule', 'alo_em_clean_no_actived');
add_action( 'alo_em_batch' , 'alo_em_batch_sending');


/**
 * On plugin adectivation 
 */
function alo_em_uninstall() {
	global $wpdb, $wp_roles, $wp_version;
	
    // delete scheduled cleaner
    wp_clear_scheduled_hook('alo_em_schedule');
    wp_clear_scheduled_hook('ALO_em_schedule'); // old versions
    // delete cron batch sending
    wp_clear_scheduled_hook('alo_em_batch');
    wp_clear_scheduled_hook('ALO_em_batch'); // old versions
    
    // if required delete all plugin data (options, db tables, page)
   	if ( get_option('alo_em_delete_on_uninstall') == "yes" ) {
   		$tables = array ( "easymail_recipients", "easymail_subscribers", "easymail_stats", "easymail_sendings", "easymail_trackings" );
   		foreach ( $tables as $tab ) {
   			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$tab");
   		}

		// delete option from db
		$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'alo_em_%'" );

	    // delete subscription page
		if ( version_compare ( $wp_version , '2.9', '>=' ) ) {
			wp_delete_post( get_option('alo_em_subsc_page'), true ); // skip trash, from wp 2.9
		} else {
			wp_delete_post( get_option('alo_em_subsc_page') );
		}
		// and the option with page id
		delete_option ('alo_em_subsc_page');
	}
	
	// reset cap
	$roles = $wp_roles->get_names(); // get a list of values, containing pairs of: $role_name => $display_name
	foreach ( $roles as $rolename => $key) {
		$wp_roles->remove_cap( $rolename, 'manage_easymail_options');
		$wp_roles->remove_cap( $rolename, 'manage_easymail_subscribers');		
		//$wp_roles->remove_cap( $rolename, 'manage_easymail_newsletters');
		//$wp_roles->remove_cap( $rolename, 'send_easymail_newsletters');
	}
}
register_deactivation_hook( __FILE__, 'alo_em_uninstall' );


/**
 * Add menu pages 
 */
function alo_em_add_admin_menu() {
  	if ( current_user_can('manage_easymail_subscribers') ) 
  		add_submenu_page( 'edit.php?post_type=newsletter', __("Subscribers", "alo-easymail"), __("Subscribers", "alo-easymail"), 'manage_easymail_subscribers', 'alo-easymail/alo-easymail_subscribers.php' );
    if ( current_user_can('manage_easymail_options') ) 
    	add_submenu_page( 'edit.php?post_type=newsletter', __("Settings"), __("Settings"), 'manage_easymail_options', 'alo-easymail/alo-easymail_options.php' );
}

add_action('admin_menu', 'alo_em_add_admin_menu');


/**
 * Contextual help
 */
function alo_em_contextual_help() {
	global $hook_suffix;
	if (function_exists('add_contextual_help')) {
		$html = __("Resources about EasyMail Newsletter", "alo-easymail") . ': <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter/" target="_blank">homepage</a> |
				<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/" target="_blank">guide</a> |
				<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">faq</a> |
				<a href="http://www.eventualo.net/blog/easymail-newsletter-for-developers/" target="_blank">for developers</a> |
				<a href="http://www.eventualo.net/forum/forum/1" target="_blank">forum</a> |
				<a href="http://www.eventualo.net/blog/category/alo-easymail-newsletter/" target="_blank">news</a>';
		$html .= " | <form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline'>
			<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
			<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='Donate via PayPal' title='Donate via PayPal' border='0' type='image' style='vertical-align: middle'>
			<img src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'><br></form>";
		/*
		$html .= "\n<h3>". __("To enable the plugin work better you should increase the wp_cron and php timeouts", "alo-easymail") ."</h3>\n";
		$html .= "<div style='padding: 0 8px;border:1px dotted #ccc;background-color:#fafafa'>\n";
		$html .= "<p>". __("Here is a summary of some solutions that might help you", "alo-easymail") .":</p>\n";
		$html .= "<p>&raquo; ". sprintf( __("increase the cron timeout in %s to 20 seconds or more", "alo-easymail"), "<em>".includes_url()."<strong>cron.php</strong></em>" ) .":</p>";	
		$html .= '<pre>'.__("FROM", "alo-easymail").":\t".'<code>wp_remote_post( $cron_url, array(\'timeout\' => </code><strong>0.01</strong><code>, \'blocking\' => false, \'sslverify\' => apply_filters(\'https_local_ssl_verify\', true)) );</code></pre>';
		$html .= '<pre>'.__("TO", "alo-easymail").":\t".'<code>wp_remote_post( $cron_url, array(\'timeout\' => </code><strong>20</strong><code>, \'blocking\' => false, \'sslverify\' => apply_filters(\'https_local_ssl_verify\', true)) );</code></pre>';		
		$html .= "<p>&raquo; ". sprintf( __("add this code in %s", "alo-easymail"), "<em>".get_option('siteurl')."/<strong>wp-config.php</strong></em>" ) .":</p>";	
		$html .= "<pre><code>define('WP_MEMORY_LIMIT', '96M');\n@ini_set( 'upload_max_size', '100M' );\n@ini_set( 'post_max_size', '105M');\n@ini_set( 'max_execution_time', '600' );</code></pre>\n";
		$html .= "<p>&raquo; ". sprintf( __("add this code in %s", "alo-easymail"), "<em>".get_option('siteurl')."/<strong>.htaccess</strong></em>" ) ." (". __("if this file does not exist, create it", "alo-easymail") . "):</p>";	
		$html .= "<pre><code>php_value memory_limit 96M\nphp_value upload_max_filesize 100M\nphp_value post_max_size 105M\nphp_value max_execution_time 600\nphp_value max_input_time 600</code></pre>\n";		
		$html .= "<p>". __("If you have problems in sending you can try alterative cron", "alo-easymail") .": ". sprintf( __("add this code in %s", "alo-easymail"), "<em>".get_option('siteurl')."/<strong>wp-config.php</strong></em>" ) .":</p>\n";
		$html .= "<pre><code>define('ALTERNATE_WP_CRON', true);</code></pre>\n";
		$html .= "<p>". __("For more info, visit the FAQ of the site.", "alo-easymail") . ' <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">&raquo;</a>' ."</p>\n";
		$html .= "</div>";
		*/		
		add_contextual_help( $hook_suffix, $html );
	}
}
add_action( 'admin_head-alo-easymail/alo-easymail_options.php', 'alo_em_contextual_help' );
add_action( 'admin_head-alo-easymail/alo-easymail_subscribers.php', 'alo_em_contextual_help' );


/*
 * Add some links on the plugin page
 */
function alo_em_add_plugin_links($links, $file) {
	if ( $file == plugin_basename(__FILE__) ) {
		$links[] = '<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/" target="_blank">Guide</a>';
		$links[] = '<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">Faq</a>';
		$links[] = '<a href="http://www.eventualo.net/forum/" target="_blank">Forum</a>';
		$links[] = '<a href="http://www.eventualo.net/blog/category/alo-easymail-newsletter/" target="_blank">News</a>';
	}
    return $links;
} 
add_filter( 'plugin_row_meta', 'alo_em_add_plugin_links', 10, 2 );



/**
 * On plugin init
 */
function alo_em_init_method() {
	// if required, exclude the easymail page from pages' list
	if ( get_option('alo_em_show_subscripage') == "no" ) add_filter('get_pages','ALO_exclude_page');
	// load localization files
	load_plugin_textdomain ("alo-easymail", false, "alo-easymail/languages");
}
add_action( 'init', 'alo_em_init_method' );


/**
 * New custom post type: Newsletter
 */
function alo_em_register_newsletter_type () {

	$labels = array(
		'name' => __( 'Newsletters', "alo-easymail" ),
		'singular_name' => __( 'Newsletter', "alo-easymail" ),
		'add_new' => __( 'Add New', "alo-easymail" ),
		'add_new_item' => __( 'Add New Newsletter', "alo-easymail" ),
		'edit_item' => __( 'Edit Newsletter', "alo-easymail" ),
		'new_item' => __( 'New Newsletter', "alo-easymail" ) ,
		'view_item' => __( 'View Newsletter', "alo-easymail" ),
		'search_items' => __( 'Search Newsletters', "alo-easymail" ),
		'not_found' =>  __( 'No Newsletters found', "alo-easymail" ),
		'not_found_in_trash' => __( 'No Newsletters found in Trash', "alo-easymail" ), 
		'parent_item_colon' => __( 'Parent Newsletter', "alo-easymail" ),
		'menu_name' => 'Newsletters',
		'parent' => __( 'Parent Newsletter', "alo-easymail" ),
	);
	$args = array(
		'labels' => $labels,
		'public' => true, 
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'exclude_from_search' => false,
		'rewrite' => array('slug' => 'newsletters'),
		'capability_type' => 'post',
		/*'map_meta_cap' => true,*/
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => false,
		'menu_icon' => ALO_EM_PLUGIN_URL.'/images/16-email-letter.png',
		'can_export' => true,
		'supports' => array( 'title' , 'editor', 'thumbnail', 'excerpt', 'custom-fields' )
	); 
	register_post_type( 'newsletter', $args );
}
add_action('init', 'alo_em_register_newsletter_type');


/**
 * Texts when a Newsletter is updated
 */
function alo_em_newsletter_updated_messages( $messages ) {
	global $post, $post_ID;
	$messages['newsletter'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Newsletter updated. <a href="%s">View Newsletter</a>', "alo-easymail" ), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.', "alo-easymail"),
		3 => __('Custom field deleted.', "alo-easymail"),
		4 => __('Newsletter updated.', "alo-easymail"),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Newsletter restored to revision from %s', "alo-easymail"), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Newsletter published. <a href="%s">View Newsletter</a>', "alo-easymail"), esc_url( get_permalink($post_ID) ) ),
		7 => __('Newsletter saved.', "alo-easymail"),
		8 => sprintf( __('Newsletter submitted. <a target="_blank" href="%s">Preview Newsletter</a>', "alo-easymail"), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Newsletter scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Newsletter</a>', "alo-easymail"),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'j M Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Newsletter draft updated. <a target="_blank" href="%s">Preview Newsletter</a>', "alo-easymail"), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}
add_filter('post_updated_messages', 'alo_em_newsletter_updated_messages');


/**
 * Adds media upload in thickbox in Newsletter
 */
function alo_em_newsletter_add_media_upload_scripts() {
    if ($GLOBALS['post_type'] == 'newsletter') {
        add_thickbox();
        wp_enqueue_script('media-upload');
    }
}
add_action('admin_print_styles-post-new.php', 'alo_em_newsletter_add_media_upload_scripts');
add_action('admin_print_styles-post.php', 'alo_em_newsletter_add_media_upload_scripts');


/**
 * Dirty hack to hide "Quick edit" button // TODO: add easymail option in Quick edit view: http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
 */
 /*
function alo_em_inti_method () {
	add_action('admin_print_styles-edit.php','alo_em_hide_quick_edit_css');
}
add_action('admin_init','alo_em_inti_method');

function alo_em_hide_quick_edit_css() {
	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) : ?>
		<style type="text/css">span.inline { display:none!important }</style>
	<?php endif;
}
*/


/**
 * Adds an type column in table
 */
function alo_em_edit_table_columns ( $columns ) {
	$columns = array(
            "cb" => 	"<input type=\"checkbox\" />",
            "title" => 	__( 'Title' ) ." / " . __( 'Subject', "alo-easymail"),
			"easymail_recipients" => __( 'Recipients', "alo-easymail" ),            
			"easymail_status" => __( 'Newsletter status', "alo-easymail" ),
            "date" => 	__( 'Start', "alo-easymail" ),     
            'author' =>	__( 'Author' )
        );   	
  	return $columns;
}
add_filter ('manage_edit-newsletter_columns', 'alo_em_edit_table_columns');


/**
 * Fills the columns of Newsletter display table
 */
function alo_em_table_column_value ( $columns ) {
	global $post;
	$recipients = alo_em_get_recipients_from_meta ( $post->ID );
	if ( $columns == "easymail_recipients" ) {
		if ( !$recipients ) {
  			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo '<a href="'. alo_em_user_can_edit_newsletter( $post->ID ) . '">';
  			echo '<img src="'. ALO_EM_PLUGIN_URL. '/images/12-exclamation.png" alt="" /> <strong>' . __( 'No recipients selected yet', "alo-easymail").'</strong>';
  			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo '</a>';
  		} else {
  			//echo "<pre>". print_r ( $recipients, true ) . "</pre>";
  			echo "<a href='#' class='easymail-toggle-short-summary' rel='{$post->ID}'>".  __( 'Total recipients', "alo-easymail") .": ";
  			if ( alo_em_get_newsletter_status( $post->ID ) ) { // if already created list of recipients, count form db, otherwise from meta
  				echo alo_em_count_newsletter_recipients( $post->ID );
  			} else { 
  			 	echo alo_em_count_recipients_from_meta ( $post->ID );
  			}	 
  			echo "</a><br />\n";
  			echo "<div id='easymail-column-short-summary-{$post->ID}' class='easymail-column-short-summary'>\n". alo_em_recipients_short_summary ( $recipients ) ."</div>\n";
  		}
	}		
	if ( $columns == "easymail_status" ) {
  		if ( $recipients ) {
			echo '<img src="'. ALO_EM_PLUGIN_URL. '/images/wpspin_light.gif" style="display:none;vertical-align: middle;" id="easymail-refresh-column-status-loading-'. $post->ID.'" />';  		
  			echo "<span id=\"alo-easymail-column-status-{$post->ID}\">\n";
  			alo_em_update_column_status ( $post->ID );
  			echo "</span>\n"; 					  					
  		}
	}
}
add_action ('manage_posts_custom_column', 'alo_em_table_column_value' ); 


/**
 * Update status column after closing recipients thickbox
 */
function alo_em_ajax_column_status () {
	$newsletter = $_POST['post_id'];
	if ( $newsletter ) alo_em_update_column_status( $newsletter );
	die();
}
add_action('wp_ajax_alo_easymail_update_column_status', 'alo_em_ajax_column_status');


/**
 * Pause/Play Newsletter, then update status column after closing recipients thickbox
 */
function alo_em_ajax_pauseplay_column_status () {
	$newsletter = $_POST['post_id'];
	$button = $_POST['button']; // pause or play?
	if ( $newsletter ) {
		if ( $button == "pause" ) {
			alo_em_edit_newsletter_status ( $newsletter, 'paused' );
		} else {
			alo_em_edit_newsletter_status ( $newsletter, 'sendable' );
		}
		alo_em_update_column_status( $newsletter );
	}
	die();
}
add_action('wp_ajax_alo_easymail_pauseplay_column_status', 'alo_em_ajax_pauseplay_column_status');


/**
 * Print html of Status column of Newsletter in display table
 */
function alo_em_update_column_status ( $newsletter ) {
	$recipients = alo_em_get_recipients_from_meta ( $newsletter );
	if ( $recipients ) {
		$status = alo_em_get_newsletter_status( $newsletter );
		$report_url = wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?', 'alo-easymail_report');		
		$goto_report = "<a href=\"#\" onclick=\"jQuery(this).easymailReportPopup ( '$report_url', $newsletter, '". alo_em_get_language () ."' );\" title=\"". __( 'Report', "alo-easymail") ."\">";
		$goto_report .= "<img src=\"". ALO_EM_PLUGIN_URL. "/images/16-report.png\" alt=\"\" />". __( 'Report', "alo-easymail") ."</a>";  	
						
		switch ( $status ) {
		
			case "sent":
				echo "<span class='status-completed'>". __("Completed", "alo-easymail"). ": 100%</span><br />";
				$end = get_post_meta ( $newsletter, "_easymail_completed", current_time( 'mysql', 0 ) );
				if ( $end ) echo date_i18n( __( 'j M Y @ G:i' ), strtotime( $end ) ). "<br />";
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) echo $goto_report;  				
				break;
				
			case "sendable":
				$post_status = get_post_status( $newsletter );
				switch ( $post_status ) {
					case "publish":
						echo "<span class='status-onsending'>".__("On sending queue", "alo-easymail"). "...</span><br />";					
						echo __("Progress", "alo-easymail"). ": ". alo_em_newsletter_recipients_percentuage_already_sent( $newsletter ) . "%<br />";
						if ( alo_em_user_can_edit_newsletter( $newsletter ) ) {
							echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-refresh.png" class="easymail-refresh-column-status" alt="'. __( 'refresh', "alo-easymail"). '" title="'. __( 'refresh', "alo-easymail"). '" rel="'. $newsletter. '" />';
							echo "<a href=\"#\" onclick=\"jQuery(this).easymailPausePlay ( $newsletter, 'pause' );return false;\">";
							echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-pause.png" class="easymail-pause-column-status" alt="'. __( 'pause', "alo-easymail"). '" title="'. __( 'pause the sending', "alo-easymail"). '" rel="'. $newsletter. '" />';
							echo "</a>";
						}
						if ( alo_em_user_can_edit_newsletter( $newsletter ) ) echo " ". $goto_report; 
						break;
					case "pending":
						echo "<span class='status-paused'>".__("Pending Review"). "</span><br />";
						break;
					case "future":
						echo "<span class='status-paused'>".__("Scheduled"). "</span><br />";
						if ( alo_em_user_can_edit_newsletter( $newsletter ) ) {
							echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-refresh.png" class="easymail-refresh-column-status" alt="'. __( 'refresh', "alo-easymail"). '" title="'. __( 'refresh', "alo-easymail"). '" rel="'. $newsletter. '" />';
						}
						break;	
					case "draft":
						echo "<span class='status-paused'>".__("Draft"). "</span><br />";
						break;
					default:
						echo "<span class='status-paused'>".__("Pending"). "</span><br />";										
						break;
				} // $post_status	
				break;
				
			case "paused":
				echo "<span class='status-paused'>".__("Paused", "alo-easymail"). "!</span><br />";
				echo __("Progress", "alo-easymail"). ": ". alo_em_newsletter_recipients_percentuage_already_sent( $newsletter ) . "%<br />";
				//if ( alo_em_count_newsletter_recipients_already_sent ( $newsletter ) > 0 ) echo " <small>(".alo_em_count_newsletter_recipients_already_sent ( $newsletter ) ."/". alo_em_count_newsletter_recipients ( $newsletter ). ")</small><br />";
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) {
					echo "<a href=\"#\" onclick=\"jQuery(this).easymailPausePlay ( $newsletter, 'play' );return false;\">";
					echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-play.png" class="easymail-pause-column-status" alt="'. __( 'continue', "alo-easymail"). '" title="'. __( 'continue the sending', "alo-easymail"). '" rel="'. $newsletter. '" />';
					echo "</a>";
				}
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) echo " ". $goto_report; 
				break;
				
			case false:
			default:
				$rec_url = wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_recipients-list.php?', 'alo-easymail_recipients-list');
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) echo "<a href=\"#\" onclick=\"jQuery(this).easymailRecipientsGenPopup ( '$rec_url', $newsletter, '". alo_em_get_language () ."' );\">";
				echo "<img src=\"". ALO_EM_PLUGIN_URL. "/images/16-arrow-right.png\" alt=\"\" /> <strong>" . __( 'Required', "alo-easymail") .":</strong> " . __( 'Create list of recipients', "alo-easymail");
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) echo "</a>";
		}
	}
}


/**
 * Add "views" button in edit newsletter table
 */
function alo_em_edit_table_views ( $views ) {
	$class = ( isset ( $_GET['easymail_status'] ) && $_GET['easymail_status'] == "sent" ) ? "current" : false;
	if ( alo_em_count_newsletters_by_status( 'sent' ) > 0 ) {
		// post_status=true: to avoid "All" view is the current
		$views[ "easymail_status" ] = "\t<a href=\"edit.php?post_status=true&post_type=newsletter&easymail_status=sent\"". ( ( $class ) ? " class=\"current\"" : "") . ">". __( 'Sent', "alo-easymail") . sprintf( " <span class=\"count\">(%d)</span>", alo_em_count_newsletters_by_status( 'sent' ) /*TODO*/ ) ."</a>";
	}
	return $views;
}
add_filter( 'views_edit-newsletter', 'alo_em_edit_table_views' );


/**
 * Show required newsletters in edit newsletter table
 */
function alo_em_filter_newsletter_table ( $query ) {
	global $wp_version, $pagenow;
	if ( is_admin() && $pagenow == "edit.php" && isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) {
		if ( isset ( $_GET['easymail_status'] ) && $_GET['easymail_status'] == "sent" ) {
			// query meta: http://codex.wordpress.org/Function_Reference/WP_Query#Custom_Field_Parameters
			if ( version_compare ( $wp_version , '3.1', '>=' ) ) {
				$meta_1 = array( 'key' => '_easymail_status', 'value' => 'sent', 'compare' => '=' );
				$query->set ('meta_query', array( $meta_1 ) );
			} else {
				$query->set ('meta_key', '_easymail_status' );
				$query->set ('meta_value', 'sent' );
				$query->set ('meta_compare', '=' );			
			}
		}
	}
   	return $query;
}
add_action('pre_get_posts', 'alo_em_filter_newsletter_table' );


/**
 * On User Profile
 */
function alo_em_user_profile_optin ( $user ) { 

    // get the current setting
    //if (ALO_easymail_get_optin($user->ID)=='yes'){    // deleted ALO
    if (alo_em_is_subscriber($user->user_email)){       // added ALO
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
    $optin_txt = ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) : __("Yes, I would like to receive the Newsletter", "alo-easymail"); 
    $html .= "    <th><label for='alo_em_option'>". $optin_txt ."</label></th>\n";
    $html .= "    <td>\n";
    $html .= "		<select name='alo_easymail_option' id='alo_easymail_option'>\n";
    $html .= "        <option value='yes' $optin_selected>". __("Yes", "alo-easymail")."</option>\n";
    $html .= "        <option value='no' $optout_selected>". __("No", "alo-easymail")."</option>\n";
    $html .= "      </select>\n";
    $html .= "    </td>\n";
    $html .= "  </tr>\n";
    $html .= "</table>\n";
 
	// add mailing lists html table
	$html .= alo_em_html_mailinglists_table_to_edit ( $user->user_email, "form-table" );
 	
    echo $html;
}
add_action( 'show_user_profile', 'alo_em_user_profile_optin' );
add_action( 'edit_user_profile', 'alo_em_user_profile_optin' );


function alo_em_save_profile_optin($user_id) {
     
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
    
    $user_info = get_userdata( $user_id );
    $user_email = $user_info->user_email;
    
    if (isset($_POST['alo_easymail_option'])) {
        if ( $_POST['alo_easymail_option'] == "yes") {
        	$todo_update = false; 
        	
        	// if changed name and lastname 
        	if ( isset( $_POST[ 'first_name' ] ) && $user_info->first_name != $_POST[ 'first_name' ] ) {
        		$user_first_name = stripslashes( trim( $_POST[ 'first_name' ] ) );
        		$todo_update = true;
        	} else {
        		$user_first_name = $user_info->first_name;
        	}
        	if ( isset( $_POST[ 'last_name' ] ) && $user_info->last_name != $_POST[ 'last_name' ] ) {
        		$user_last_name = stripslashes( trim( $_POST[ 'last_name' ] ) );
        		$todo_update = true;
        	} else {
        		$user_last_name = $user_info->last_name;
        	}
        	$fullname = $user_first_name." ".$user_last_name;

        	// if changed email
        	if ( isset( $_POST[ 'email' ] ) && is_email( $_POST[ 'email' ] ) && $user_email != $_POST[ 'email' ] ) {
        		$user_email = stripslashes( trim( $_POST[ 'email' ] ) );
        		$todo_update = true;
        	} 
        	
        	if ( $todo_update ) {
        		//alo_em_update_subscriber_by_email ( $user_info->user_email, $user_email, $fullname, 1, alo_em_get_language(true) );
        		if ( alo_em_update_subscriber_by_email ( $user_info->user_email, $user_email, $fullname, 1, alo_em_get_language(true) ) ) {
        			$subscriber = alo_em_get_subscriber ( $user_email );
        			do_action ( 'alo_easymail_subscriber_updated', $subscriber, $user_info->user_email );
        		}
        	} else {
	            alo_em_add_subscriber( $user_email, $fullname, 1, alo_em_get_language(true) );
	      	}
            
            // if subscribing, save also lists
        	$mailinglists = alo_em_get_mailinglists( 'public' );
			if ($mailinglists) {
				$subscriber_id = alo_em_is_subscriber( $user_email );
				foreach ( $mailinglists as $mailinglist => $val) {					
					if ( isset ($_POST['alo_em_profile_lists']) && is_array ($_POST['alo_em_profile_lists']) && in_array ( $mailinglist, $_POST['alo_em_profile_lists'] ) ) {
						alo_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
					} else {
						alo_em_delete_subscriber_from_list ( $subscriber_id, $mailinglist ); // remove from list
					}
				}
			}				
        } else {
            alo_em_delete_subscriber_by_id( alo_em_is_subscriber($user_email) );
        }
    }
}
add_action( 'personal_options_update', 'alo_em_save_profile_optin' );
add_action( 'edit_user_profile_update', 'alo_em_save_profile_optin' );


/**
 * Widget activation
 */
function alo_em_load_widgets() {
	register_widget( 'ALO_Easymail_Widget' );
}
add_action( 'widgets_init', 'alo_em_load_widgets' );


/**
 * Add javascript on Admin panel
 */
function alo_em_add_admin_script () {
	global $post, $pagenow;
	if ( isset($_GET['page']) && $_GET['page'] == "alo-easymail/alo-easymail_options.php") {
		wp_enqueue_script('jquery-ui-tabs');
		echo '<link rel="stylesheet" href="'.ALO_EM_PLUGIN_URL.'/inc/jquery.ui.tabs.css" type="text/css" media="print, projection, screen" />'."\n";
	}
	if ( $pagenow == "post.php" || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) ) {
		wp_enqueue_script('thickbox');
		wp_enqueue_script( 'alo-easymail-backend', ALO_EM_PLUGIN_URL . '/inc/alo-easymail-backend.js' );
		wp_localize_script( 'alo-easymail-backend', 'easymailJs', alo_em_localize_admin_script() );
	}
}
add_action('admin_print_scripts', 'alo_em_add_admin_script' );

function alo_em_localize_admin_script () {
	global $post, $pagenow;
	$post_id = ( $post ) ? $post->ID : false;
    return array(
    	'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'pluginPath' => ALO_EM_PLUGIN_URL."/",
        'postID' => $post_id,
        'pagenow' => $pagenow,
        'reportPopupTitle' => __("Newsletter report", "alo-easymail"),
        'subscribersPopupTitle' => __("Newsletter subscribers creation", "alo-easymail")
    );
}


/**
 * Add CSS on Admin panel
 */
function alo_em_add_admin_styles () {
	global $post, $pagenow;
	if ( $pagenow == "post.php" || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) ) {
		wp_enqueue_style( 'alo-easymail-backend-css', ALO_EM_PLUGIN_URL.'/inc/alo-easymail-backend.css' );
		wp_enqueue_style( 'thickbox' );
	}
}
add_action( "admin_print_styles", 'alo_em_add_admin_styles' );


/**
 * Load scripts & styles on Frontend
 */
function alo_em_load_scripts() {
	if ( get_option('alo_em_embed_css') == "yes" ) {
		if ( @file_exists ( TEMPLATEPATH.'/alo-easymail.css' ) ) {
		  	wp_enqueue_style ('alo-easymail', get_bloginfo('template_directory') .'/alo-easymail.css' );
		} else {
		  	wp_enqueue_style ('alo-easymail', ALO_EM_PLUGIN_URL.'/alo-easymail.css' );
		}
	} 
	/* // TODO use jquery external js!
	wp_enqueue_script( 'alo-easymail-frontend', ALO_EM_PLUGIN_URL . '/inc/alo-easymail-frontend.js' );
	wp_localize_script( 'alo-easymail-frontend', 'easymail', 
		array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'postID' => 2 )  
	);
	*/
}
add_action('wp_enqueue_scripts', 'alo_em_load_scripts');


/**
 * Exclude the easymail page from pages' list
 */
function ALO_exclude_page( $pages ) {
	if ( !is_admin() ) {
		for ( $i=0; $i<count($pages); $i++ ) {
			$page = & $pages[$i];
		    if ($page->ID == get_option('alo_em_subsc_page')) unset ($pages[$i]);
		}
	}
    return $pages;
}


/**
 * Manage the newsletter subscription page
 */
function alo_em_subscr_page ( $atts, $content = null ) {
	ob_start();
	include( ALO_EM_PLUGIN_ABS .'/alo-easymail_subscr-page.php' );
	$contents = ob_get_contents();
	ob_end_clean();
	return $contents;
}
add_shortcode('ALO-EASYMAIL-PAGE', 'alo_em_subscr_page');


/**
 * Boxes meta in Newsletter edit/new pages
 */
function alo_em_newsletter_add_custom_box() {
    add_meta_box( "alo_easymail_newsletter_recipients", __("Recipients", "alo-easymail"), "alo_em_meta_recipients", "newsletter", "side", "high" );
    add_meta_box( "alo_easymail_newsletter_placeholders", __("Placeholders", "alo-easymail"), "alo_em_meta_placeholders", "newsletter", "normal", "high" );
}
add_action('add_meta_boxes', 'alo_em_newsletter_add_custom_box');


/**
 * Box meta: Recipients
 */
function alo_em_meta_recipients ( $post ) { 
	wp_nonce_field( ALO_EM_PLUGIN_DIR, "edit_newsletter" );
	//print_r ( alo_em_get_recipients_from_meta($post->ID) ); print_r ( alo_em_get_all_languages() );
	echo "<p " . ( ( alo_em_count_recipients_from_meta( $post->ID ) == 0 ) ? "class=\"easymail-txtwarning\"" : "" ) ." >";
	echo "<strong>" .__("Selected recipients", "alo-easymail") .": ". alo_em_count_recipients_from_meta( $post->ID ) ."</strong></p>";
	
	if ( alo_em_get_newsletter_status ( $post->ID ) == "sent" ) {
		echo "<div class=\"easymail-alert\"><p>". __("This newsletter was already sent", "alo-easymail") .".</p>";
		echo "</div>";	
		return;
	}
	
	if ( alo_em_count_newsletter_recipients ( $post->ID ) > 0 ) {
		echo "<div class=\"easymail-alert\"><p>". __("The creation of the recipients list has already started", "alo-easymail") .".</p>";
		echo "<p><input type=\"checkbox\" name=\"easymail-reset-all-recipients\" id=\"easymail-reset-all-recipients\" value=\"yes\" /> ";
		echo "<strong><label for=\"easymail-reset-all-recipients\">". __("Check this flag to delete the existing list and save new recipients now", "alo-easymail") .".</label></strong></p>";
		echo "</div>";
	}
	
	$recipients = alo_em_get_recipients_from_meta ( $post->ID );
	?>
	<div class="easymail-edit-recipients easymail-edit-recipients-registered">
		<ul class="level-1st">
			<li class="list-title"><?php _e( "Users" ); ?>:</li>
			<li>
				<?php $checked = ( isset( $recipients['registered']) ) ? ' checked="checked" ' : ''; ?>
				<label for="easymail-recipients-all-regusers" class="easymail-metabox-update-count"><?php echo __("All registered users", "alo-easymail"). " (". count ( alo_em_get_recipients_registered () ) .")"; ?></label>
				<input type="checkbox" name="easymail-recipients-all-regusers" id="easymail-recipients-all-regusers" value="checked" <?php echo $checked ?> class="easymail-metabox-update-count" />
			</li>						
		</ul>
	</div><!-- /easymail-edit-recipients-registered -->
	
	<div class="easymail-edit-recipients easymail-edit-recipients-subscribers">
		<ul class="level-1st">
			<li class="list-title"><?php _e("Newsletter subscribers", "alo-easymail"); ?>:</li>				
			<li>
				<?php $checked = ( isset( $recipients['subscribers']) ) ? ' checked="checked" ' : ''; ?>
				<label for="easymail-recipients-all-subscribers" class="easymail-metabox-update-count"><?php echo __("All subscribers", "alo-easymail"). " (". count( alo_em_get_recipients_subscribers() ) .")"; ?></label>
				<input type="checkbox" name="easymail-recipients-all-subscribers" id="easymail-recipients-all-subscribers" value="checked" <?php echo $checked ?> class="easymail-metabox-update-count" />
			</li>
			
			<?php // if mailing lists
			$mailinglists = alo_em_get_mailinglists( 'admin,public' );
			if ( $mailinglists ) : ?>
			<li><a href="#" class="easymail-filter-subscribers-by-lists"><?php _e("Filter subscribers according to lists", "alo-easymail"); ?>...</a></li>
			<li>
				<ul id="easymail-filter-ul-lists" class="level-2st">
					<?php	
					foreach ( $mailinglists as $list => $val) { 
						if ( $val['available'] == "deleted" || $val['available'] == "hidden" ) continue; 
							$checked = ( isset( $recipients['list'] ) && in_array( $list, $recipients['list'] ) ) ? ' checked="checked" ' : ''; 
							?>
							<li>
								<label for="list_<?php echo $list ?>" class="easymail-metabox-update-count"><?php echo alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) . " (".  count ( alo_em_get_recipients_subscribers( $list ) ).")"; ?></label>
								<input type="checkbox" name="check_list[]" class="check_list easymail-metabox-update-count" id="list_<?php echo $list ?>" value="<?php echo $list ?>" <?php echo $checked ?>  />
							</li>
						<?php } ?>
				</ul>	
			</li>
			<?php endif; // $mailinglists ?>
			
			<?php // if languages
			$languages = alo_em_get_all_languages( false );
			if ( $languages ) : ?>
			<li><a href="#" class="easymail-filter-subscribers-by-languages"><?php _e("Filter subscribers according to languages", "alo-easymail"); ?>...</a></li>	
			<li>
				<ul id="easymail-filter-ul-languages" class="level-2st">			
					<?php	
					foreach ( $languages as $index => $lang) {  
						$checked = ( ( isset( $recipients['lang'] ) && in_array( $lang, $recipients['lang'] )) || !isset( $recipients['lang'] ) ) ? ' checked="checked" ' : '';
						$tot_sub_x_lang = alo_em_count_subscribers_by_lang( $lang, true );
						?>
						<li>
							<label for="check_lang_<?php echo $lang ?>" class="easymail-metabox-update-count" > <?php echo esc_html ( alo_em_get_lang_name ( $lang ) ) . " (". $tot_sub_x_lang .")"; ?></label>
							<input type="checkbox" name="check_lang[]" class="check_lang easymail-metabox-update-count" id="check_lang_<?php echo $lang ?>" value="<?php echo $lang ?>" <?php echo $checked ?> />
						</li>
					<?php }
						$checked = ( (isset($recipients['lang']) && in_array( "UNKNOWN", $recipients['lang'] )) || !isset($recipients['lang']) ) ? ' checked="checked" ' : ''; ?>
						<li>
							<label for="check_lang_unknown" class="easymail-metabox-update-count"> <?php _e("Not specified / others", "alo-easymail"); ?> (<?php echo alo_em_count_subscribers_by_lang(false, true) ?>)</label>
							<input type="checkbox" name="check_lang[]" class="check_lang easymail-metabox-update-count" id="check_lang_unknown" value="UNKNOWN" <?php echo $checked ?> />
						</li>
				</ul>	
			</li>
			<?php endif; // $languages ?>			
			
		</ul>
		
	</div><!-- /easymail-edit-recipients-subscribers -->
	

	<?php
}


/**
 * Box meta: Placeholders
 */
function alo_em_meta_placeholders ( $post ) { 
	wp_nonce_field( ALO_EM_PLUGIN_DIR, "edit_newsletter" );
	alo_em_tags_table ( $post->ID );
}


/**
 * Add post select in Placeholders table
 */
function my_easymail_placeholders_title_easymail_post ( $post_id ) {
	$n_last_posts = (get_option('alo_em_lastposts'))? get_option('alo_em_lastposts'): 10;
	$args = array(
		'numberposts' => $n_last_posts,
		'order' => 'DESC',
		'orderby' => 'date'
		); 
	$get_posts = get_posts($args);
	echo __("Choose a post", "alo-easymail"). ": ";
	echo '<select name="placeholder_easymail_post" id="placeholder_easymail_post" >';
	if ( $get_posts ) { 
		foreach($get_posts as $post) :
		    $select_post_selected = ( get_post_meta ( $post_id, '_placeholder_easymail_post', true) == $post->ID ) ? 'selected="selected"': '';
		    echo '<option value="'.$post->ID.'" '. $select_post_selected .'>['. date_i18n( __( 'j M Y' ), strtotime( $post->post_date ) ) .'] '. get_the_title( $post->ID ).' </option>';
		endforeach;
	}
	echo '</select>'; 
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_post',  'my_easymail_placeholders_title_easymail_post' );


/**
 * Save Post select in Placeholder Box meta in Newsletter 
 */
function alo_em_save_newsletter_placeholders_easymail_post ( $post_id ) {
	if ( isset( $_POST['placeholder_easymail_post'] ) && is_numeric( $_POST['placeholder_easymail_post'] ) ) {
		update_post_meta ( $post_id, '_placeholder_easymail_post', $_POST['placeholder_easymail_post'] );
	}
} 
add_action('alo_easymail_save_newsletter_meta_extra',  'alo_em_save_newsletter_placeholders_easymail_post' );

 
/**
 * Save Boxes meta in Newsletter 
 */
function alo_em_save_newsletter_meta ( $post_id ) {
	
	if ( @!wp_verify_nonce( $_POST["edit_newsletter"], ALO_EM_PLUGIN_DIR )) {
		return $post_id;
	}

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

	// Check permissions
	if ( 'newsletter' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
	}
	
	do_action ( 'alo_easymail_save_newsletter_meta_extra', $post_id );
	
	// If a previous list exists already: if requested reset, otherwise don't save
	if ( alo_em_count_newsletter_recipients( $post_id ) > 0 ) {
		if ( isset( $_POST['easymail-reset-all-recipients'] ) ) {
			alo_em_delete_newsletter_recipients ( $post_id );
			alo_em_delete_newsletter_status ( $post_id );
			alo_em_delete_cache_recipients ( $post_id );
		} else {
			return $post_id; // don't save, exit
		}
	}
	
	// Save Recipients
	$recipients = array ();
	if ( isset( $_POST['easymail-recipients-all-regusers'] ) ) {
		$recipients['registered'] = "1";
	}
	if ( isset( $_POST['easymail-recipients-all-subscribers'] ) ) {
		$recipients['subscribers'] = "1";
	} else {
		if ( isset($_POST['check_list']) && is_array ($_POST['check_list']) ) {
			foreach ( $_POST['check_list'] as $list ) {
				$recipients['list'][] = $list;
			}
		}
	}
	if ( isset($_POST['check_lang']) && is_array ($_POST['check_lang']) ) {
		foreach ( $_POST['check_lang'] as $lang ) {
			$recipients['lang'][] = $lang;
		}
	}	
	
	// Save!
	delete_post_meta ( $post_id, "_easymail_recipients" );
	add_post_meta ( $post_id, "_easymail_recipients", $recipients );

}
add_action('save_post', 'alo_em_save_newsletter_meta');


/**
 * When a Newsletter is deleted: eg. delete recipients from db table
 */
function alo_em_newsletter_deleted ( $post_id ) {
	alo_em_delete_newsletter_recipients( $post_id );
}
add_action( 'delete_post', 'alo_em_newsletter_deleted' );


/**
 * Add a dashboard widget
 */
function alo_em_dashboard_widget_function() {
	global $wpdb;
	echo "<h4>". __("Newsletters scheduled for sending", "alo-easymail").": ". alo_em_count_newsletters_by_status( 'sendable' ) ."</h4>";
	$newsletter =  alo_em_get_newsletters_in_queue( 1 );
	if ( $newsletter ) {
		echo "<p>";
		echo '<img src="'.ALO_EM_PLUGIN_URL.'/images/16-email-forward.png" title="'.__("now sending", "alo-easymail").'" alt="" style="vertical-align:text-bottom" />';
		echo " <strong>" . stripslashes ( alo_em___( $newsletter[0]->post_title ) ) ."</strong><br />";				
		echo __("Progress", "alo-easymail") .": " . alo_em_newsletter_recipients_percentuage_already_sent( $newsletter[0]->ID ) . " %<br />" ;			
		echo "<em>".__("Added on", "alo-easymail") . " ". date_i18n( __( 'j M Y @ G:i' ), strtotime( $newsletter[0]->post_date ) ) . "  - ";
		echo __("Scheduled by", "alo-easymail") . " ". get_user_meta($newsletter[0]->post_author, 'nickname',true). "</em>";
		echo "</p>";
	} else {
		echo "<p>". __("There are no newsletters in queue", "alo-easymail") . ".</p>";
	}
	echo "<h4 style='margin-top:1.2em'>". __("Subscribers", "alo-easymail") ."</h4>";
	list ( $total, $active, $noactive ) = alo_em_count_subscribers ();
	if ($total) {
		echo "<p>". sprintf( __("There are %d subscribers: %d activated, %d not activated", "alo-easymail"), $total, $active, $noactive ) . ".</p>";
	} else {
		echo "<p>". __("No subscribers", "alo-easymail") . ".</p>";
	}
	
	echo "<h5 style='margin-bottom:0.4em'>". __("Updates from plugin developer", "alo-easymail") ."</h5>";
	$rss = fetch_feed( 'http://www.eventualo.net/blog/category/alo-easymail-newsletter/feed/' );
	if ( !is_wp_error( $rss ) ) {
		$maxitems = $rss->get_item_quantity( 3 ); 
		$rss_items = $rss->get_items(0, $maxitems); 
		echo "<ul style='padding-top: 0.5em'>";
		if ( $maxitems == 0 ) {
			echo '<li>No items.</li>';
		} else {
			// Loop through each feed item and display each item as a hyperlink.
			foreach ( $rss_items as $item ) : 
				$content = $item->get_content();
				$content = wp_html_excerpt( $content, 350 ) . ' [...]'; ?>
			<li>
				<a href='<?php echo $item->get_permalink(); ?>'
				title='<?php echo $content; ?>'>
				<?php echo $item->get_title(); ?></a>
				<?php echo date_i18n( __('j F Y'), strtotime( $item->get_date() ) ); ?> 
			</li>
			<?php endforeach; 
		} 
		echo "</ul>";
	}
	
} 

function alo_em_add_dashboard_widgets() {
	if ( current_user_can ( 'manage_easymail_subscribers' ) && current_user_can ( 'edit_posts' ) ) {
		wp_add_dashboard_widget('alo-easymail-widget', 'EasyMail Newsletter', 'alo_em_dashboard_widget_function');	
	}
} 
add_action('wp_dashboard_setup', 'alo_em_add_dashboard_widgets' );


/**
 * Show the optin/optout on Registration Form
 */
function alo_em_show_registration_optin () {
    $optin_txt = ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) : __("Yes, I would like to receive the Newsletter", "alo-easymail"); 
	echo '<p class="alo_easymail_reg_optin"><input type="checkbox" id="alo_em_opt" name="alo_em_opt" value="yes" class="input" checked="checked" /> ';
	echo '<label for="alo_em_opt" >' . $optin_txt .'</label></p>';
	 
    $mailinglists = alo_em_get_mailinglists( 'public' );
    if ( $mailinglists ) {
    	$lists_msg 	= ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) :  __("You can also sign up for specific lists", "alo-easymail"); 
		echo "<p class='alo_easymail_reg_list_msg'>". $lists_msg .":</p>\n";
		foreach ( $mailinglists as $list => $val ) {
			echo "<p class='alo_easymail_reg_list'><input type='checkbox' name='alo_em_register_lists[]' id='alo_em_register_list_$list' value='$list' /> <label for='alo_em_register_list_$list'>" . alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ."</label></p>\n";
		}
	} 

	echo '<input type="hidden" id="alo_em_lang" name="alo_em_lang" value="' . esc_attr(alo_em_get_language()).'" /> ';
}
add_action('register_form','alo_em_show_registration_optin');


/**
 * Save the optin/optout on Registration Form
 */
function alo_em_save_registration_optin ( $user_id, $password="", $meta=array() )  {
	$user = get_userdata($user_id);
	if (!empty($user->first_name) && !empty($user->last_name)) {
		$name = $user->first_name.' '.$user->last_name;	
	} else {
		$name = $user->display_name;
	}
	if ( isset ($_POST['alo_em_opt']) && $_POST['alo_em_opt'] == "yes" ) {
		$lang = ( isset($_POST['alo_em_lang']) && in_array ( $_POST['alo_em_lang'], alo_em_get_all_languages( false )) ) ? $_POST['alo_em_lang'] : "" ;
		alo_em_add_subscriber( $user->user_email, $name , 1, $lang );
		
		 // if subscribing, save also lists
    	$mailinglists = alo_em_get_mailinglists( 'public' );
		if ($mailinglists) {
			$subscriber_id = alo_em_is_subscriber( $user->user_email );
			foreach ( $mailinglists as $mailinglist => $val) {					
				if ( isset ($_POST['alo_em_register_lists']) && is_array ($_POST['alo_em_register_lists']) && in_array ( $mailinglist, $_POST['alo_em_register_lists'] ) ) {
					alo_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
				} 
			}
		}				
	}
}
add_action( 'user_register', 'alo_em_save_registration_optin' );


/**
 * Edit the e-mail message
 */ 
function alo_em_handle_email ( $args ) {
	// $args['to'], $args['subject'], $args['message'], $args['headers'], $args['attachments']
	
	// Check based on $args['subject']; more attrs in $args['message']
	global $_config;
	/*
	 * 1) Activation e-mail
	 */
	if ( strpos ( "#_EASYMAIL_ACTIVATION_#", $args['subject'] ) !== false) {
		
		// Get the parameters stored as a query in $args['message'] 
		$defaults = array( 'lang' => '', 'email' => '',	'name' => '', 'unikey' => '' );
		//$defaults = array( 'email' => '' );
		$customs = wp_parse_args( $args['message'], $defaults );
		extract( $customs, EXTR_SKIP );
		
		//$subscriber = alo_em_get_subscriber( $email );
		
		// Subject
	   	if ( $subject_text = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_subj', true ) ) {
			$subject = $subject_text;
		} else {
		   	$subject = alo_em___( __("Confirm your subscription to %BLOGNAME% Newsletter", "alo-easymail" ) );
		}
		$blogname = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
		$subject = str_replace ( "%BLOGNAME%", $blogname, $subject );
		$args['subject'] = $subject;
				
		// Content
	   	if ( $content_txt = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_mail', true ) ) {
			$content = $content_txt;
		} else {
		   	$content = __("Hi %NAME%\nto complete your subscription to %BLOGNAME% newsletter you need to click on the following link (or paste it in the address bar of your browser):\n", "alo-easymail");
		   	$content .= "%ACTIVATIONLINK%\n\n";
		   	$content .= __("If you did not ask for this subscription ignore this message.", "alo-easymail"). "\n";
		    $content .= __("Thank you", "alo-easymail")."\n". $blogname ."\n";
		}
		/*
	 	$div_email = explode("@", $email);
		$arr_params = array ('ac' => 'activate', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $unikey );
		$sub_link = add_query_arg( $arr_params, get_page_link (get_option('alo_em_subsc_page')) );
		$sub_link = alo_em_translate_url ( $sub_link, $lang );		
		*/
				
		//$div_email = explode("@", $email);
		$sub_vars = $email ."|" /*$div_email[0] . "|" . $div_email[1] . "|" */ . $unikey . "|" . $lang;
		
		//$sub_vars = $subscriber->ID . "|" . $subscriber->unikey;
		$sub_vars = urlencode( base64_encode( $sub_vars ) );
		$sub_link = add_query_arg( 'emact', $sub_vars, trailingslashit( get_home_url() ) );
		$sub_link = alo_em_translate_url ( $sub_link, $lang /*$subscriber->lang */ );

	  	$content = str_replace ( "%BLOGNAME%", $blogname, $content );
	   	$content = str_replace ( "%NAME%", /* $subscriber->name */ $name, $content );
	   	$content = str_replace ( "%ACTIVATIONLINK%", $sub_link, $content ); 
	   	
		$args['message'] = $content;
	}
	return $args;
}

add_filter('wp_mail', 'alo_em_handle_email');


/**
 * Add Newsletter menu in Admin bar (from WP 3.1)
 */
function alo_em_add_menu_admin_bar() {
    global $wp_admin_bar;
 	if ( !$wp_admin_bar ) return;
    if ( !is_admin_bar_showing() ) return;
 	
 	if ( current_user_can('edit_posts') ) {
		$wp_admin_bar->add_menu( array( 'id' => 'alo_easymail', 'title' =>__( 'Newsletters', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter" ) );
		$wp_admin_bar->add_menu( array( 'id' => 'alo_easymail_main', 'parent' => 'alo_easymail', 'title' => __( 'Newsletters', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter" ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail_main', 'title' => __( 'Send newsletter', "alo-easymail" ), 'href' => admin_url('post-new.php')."?post_type=newsletter" ) );       
			$wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail_main', 'title' => __( 'Show all', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter" ) );   
  	}
  	if ( current_user_can('manage_easymail_subscribers') ) {
	    $wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail', 'title' => __( 'Subscribers', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/alo-easymail_subscribers.php" ) );
    }
    if ( current_user_can('manage_easymail_options') ) {
    	$wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail', 'title' => __( 'Options', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/alo-easymail_options.php" ) );    
    }   
}
add_action( 'admin_bar_menu', 'alo_em_add_menu_admin_bar' ,  70);


/**
 * Send a newsletter to a test email
 */
function alo_em_send_mailtest () {
	$result = "no";
	check_ajax_referer( "alo-easymail_recipients-list" );
	$newsletter = ( isset( $_POST['newsletter'] ) && is_numeric( $_POST['newsletter'] ) ) ? (int) $_POST['newsletter'] : false;
	$email = ( isset( $_POST['email'] ) && is_email( $_POST['email'] ) ) ? $_POST['email'] : false;
	if ( $email && $newsletter ) {
		$recipient = (object) array ( 'newsletter' => $newsletter, 'email' => $email );
		if ( alo_em_send_newsletter_to ( $recipient, true ) ) $result = "yes";
	}
	usleep( 500000 );
	die ( $result );
}
add_action('wp_ajax_easymail_send_mailtest', 'alo_em_send_mailtest');


/**
 * Alert in admin panel
 */
function alo_em_admin_notice() {
	global $pagenow;
	$page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : false;
	if ( $pagenow == "edit.php" && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'newsletter' && $page != 'alo-easymail/alo-easymail_subscribers.php' ) {
		/*
		if ( get_option('alo_em_timeout_alert') != "hide" ) { 
			echo '<div class="updated fade">';
			echo '<p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> '. __("To enable the plugin work better you should increase the wp_cron and php timeouts", "alo-easymail") .". ";
			echo __("For more info you can use the Help button or visit the FAQ of the site", "alo-easymail");
			echo ' <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/#faq-3" target="_blank" title="'. __("For more info, visit the FAQ of the site.", "alo-easymail") .'">&raquo;</a></p>';
			echo "<p>(<a href='". "edit.php?post_type=newsletter&page=alo-easymail/alo-easymail_options.php" ."&amp;timeout_alert=stop' />". __('Do not show it again', 'alo-easymail') ."</a>)</p>";
			echo '</div>';
		}
		*/
		if ( get_option('ALO_em_debug_newsletters') != "" ) { 
			echo '<div class="updated fade">';
			echo '<p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> <strong>'. __("Debug mode is activated", "alo-easymail") ."</strong>: ";
			if ( get_option('ALO_em_debug_newsletters') == "to_author" ) 	_e("all messages will be sent to the newsletter author", "alo-easymail");
			if ( get_option('ALO_em_debug_newsletters') == "to_file" ) 		_e("all messages will be recorded into a log file", "alo-easymail");
			echo ".</p>";
			echo '</div>';
		}
	}
}
add_action('admin_notices', "alo_em_admin_notice");


/**
 * Manage user request made via GET vars: eg. activation link, unsubscribe link, external request
 */
function alo_em_check_get_vars () {
	
	// From unsubscribe link
	if ( isset( $_GET['emunsub'] ) ) {
		$get_vars = base64_decode( $_GET['emunsub'] );
		$get = explode( "|", $get_vars );	
		$subscriber = alo_em_get_subscriber_by_id ( $get[0] );

		$uns_link = "";
		if ( $subscriber ) {
			$div_email = explode( "@", $subscriber->email );
		   	$arr_params = array ('ac' => 'unsubscribe', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $get[1] );
			$uns_link = add_query_arg( $arr_params, get_page_link (get_option('alo_em_subsc_page')) );
			$uns_link = alo_em_translate_url ( $uns_link, $subscriber->lang );
		}
		wp_redirect( $uns_link );
		exit;
	}

	// From activation link
	if ( isset( $_GET['emact'] ) ) {
		$get_vars = base64_decode( $_GET['emact'] );
		$get = explode( "|", $get_vars );
		$subscriber = alo_em_get_subscriber ( $get[0] );
		
		$act_link = "";
		if ( $subscriber ) {
			$div_email = explode( "@", $subscriber->email );
			$arr_params = array ('ac' => 'activate', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $get[1] );
			$act_link = add_query_arg( $arr_params, get_page_link (get_option('alo_em_subsc_page')) );
			$act_link = alo_em_translate_url ( $act_link, $get[2] /* $subscriber->lang */ );
		}		
		wp_redirect( $act_link );
		exit;
	}
	
	// Called form external request (eg. cron task)
	if ( isset( $_GET['alo_easymail_doing_batch'] ) ) {
		//echo "OK let's do the batch!";
		alo_em_batch_sending ();
		exit;
	}	
}
add_action('init', 'alo_em_check_get_vars');


?>
