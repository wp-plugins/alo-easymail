<?php
/*
Plugin Name: ALO Easy Mail
Plugin URI: http://www.eventualo.net/blog/?p=365
Description: Allows you to send e-mails and newsletters to your registered users and to other e-mail addresses.
Version: 0.9.2
Author: Alessandro Massasso
Author URI: http://www.eventualo.net
*/

/*  Copyright 2009  Alessandro Massasso  (email : alo@eventualo.net)

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
define("ALO_EM_FOOTER","&raquo; <em>Please visit my site and leave your feedback: <a href='http://www.eventualo.net/blog/?p=365' target='_blank'>www.eventualo.net</a></em>");


/**
 * On plugin activation 
 */
function ALO_em_install() {
	if (!get_option('ALO_em_template'))
	    add_option('ALO_em_template', 'Hi [USER-NAME],<br /><br />
	    I have published a new post <strong>[POST-TITLE]</strong>.<br />[POST-EXCERPT]<br />Please visit my site [SITE-LINK] to read it and leave your comment about it.<br />
        Hope to see you online!<br /><br />[SITE-LINK]');
	if (!get_option('ALO_em_list'))
	    add_option('ALO_em_list', '');
    if (!get_option('ALO_em_lastposts'))
	    add_option('ALO_em_lastposts', 10);
}

register_activation_hook(__FILE__,'ALO_em_install');


/**
 * Add menu pages 
 */
function ALO_em_add_admin_menu() {
    add_options_page('Alo EasyMail', 'Alo EasyMail', 8, 'alo-easymail-options', 'ALO_em_option_page');
	add_management_page ('Alo EasyMail', 'Alo EasyMail', 8, 'alo-easymail/alo-easymail_main.php');
}

add_action('admin_menu', 'ALO_em_add_admin_menu');


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
	    if(isset($_POST['emails_add'])) update_option('ALO_em_list', trim($_POST['emails_add']));
	    if(isset($_POST['lastposts']) && (int)$_POST['lastposts'] > 0) update_option('ALO_em_lastposts', trim($_POST['lastposts']));
	    echo '<div id="message" class="updated fade"><p>Updated.</p></div>';
    }?>
    
    <div class="wrap">
    <h2>Alo EasyMail's Options</h2>

    <form action="" method="post">
    <label for="lastposts">Number of last posts to display:</label>
    <input type="text" name="lastposts" value="<?php echo get_option('ALO_em_lastposts') ?>" id="lastposts" size="2" maxlength="2" />
    <p><?php echo 'Text for the mail template:'; ?></p>
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
    
    <p style='margin-top:20px;'>List of e-mail addresses, separated by <strong>comma</strong> (,):</p>
    <textarea id="emails_add" value="" name="emails_add" rows="5" cols="70"><?php echo get_option('ALO_em_list'); ?></textarea>
    
    <p class="submit">
    <input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
    <span id="autosave"></span>
    <input type="submit" name="submit" value="<?php echo 'Update'; ?>" style="font-weight: bold;" />
    </p>
    </form>
    
    <p><?php echo ALO_EM_FOOTER; ?></p>
    </div>
<?php	
} ?>
