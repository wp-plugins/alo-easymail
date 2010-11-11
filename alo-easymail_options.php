<?php
auth_redirect();
if ( !current_user_can('manage_easymail_options') ) 	wp_die(__('Cheatin&#8217; uh?'));
	
global $wp_version, $wpdb, $user_ID, $wp_roles;

if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
	// -------- Options permitted to all ('manage_easymail_options')
    if(isset($_POST['optin_msg'])) update_option('ALO_em_optin_msg', stripslashes(trim($_POST['optin_msg'])));
    if(isset($_POST['optout_msg'])) update_option('ALO_em_optout_msg', stripslashes(trim($_POST['optout_msg'])));	    
	if(isset($_POST['lists_msg'])) update_option('ALO_em_lists_msg', stripslashes(trim($_POST['lists_msg'])));	        
	// --------
	
	// -------- Options permitted ONLY to ADMIN ('manage_options')
	if ( current_user_can('manage_options') ) {
		if(isset($_POST['content'])) {
		    $main_content = stripslashes($_REQUEST['content']);
		    $main_content = str_replace("\n", "<br />", $main_content);
		    update_option('ALO_em_template', $main_content);
		}
		if(isset($_POST['sender_email'])) update_option('ALO_em_sender_email', trim($_POST['sender_email']));
		if(isset($_POST['lastposts']) && (int)$_POST['lastposts'] > 0) update_option('ALO_em_lastposts', trim($_POST['lastposts']));	
		if(isset($_POST['dayrate']) && (int)$_POST['dayrate'] >= 300 && (int)$_POST['dayrate'] <= 10000 ) update_option('ALO_em_dayrate', trim($_POST['dayrate']));
		if(isset($_POST['batchrate']) && (int)$_POST['batchrate'] >= 10 && (int)$_POST['batchrate'] <= 300 ) update_option('ALO_em_batchrate', trim($_POST['batchrate']));
		if(isset($_POST['subsc_page']) && (int)$_POST['subsc_page'] ) update_option('ALO_em_subsc_page', trim($_POST['subsc_page']));
		
		if ( isset($_POST['show_subscripage']) ) {
			update_option('ALO_em_show_subscripage', "yes");
		} else {
			update_option('ALO_em_show_subscripage', "no") ;
		}
		if ( isset($_POST['embed_css']) ) {
			update_option('ALO_em_embed_css', "yes");
		} else {
			update_option('ALO_em_embed_css', "no") ;
		}

		if ( isset($_POST['delete_on_uninstall']) && isset($_POST['delete_on_uninstall_2']) ) {
			update_option('ALO_em_delete_on_uninstall', "yes");
		} else {
			update_option('ALO_em_delete_on_uninstall', "no") ;
		}
		
		// get roles to update cap
		$role_author = get_role( 'author' );
		$role_editor = get_role( 'editor' );
		
		if ( isset($_POST['can_manage_newsletters']) ) {
			switch ( $_POST['can_manage_newsletters'] ) {
				case "editor":
					$role_editor->add_cap( 'manage_easymail_newsletters' );
					$role_editor->add_cap( 'send_easymail_newsletters' );
					break;
				case "administrator":
				default:
					$role_editor->remove_cap( 'manage_easymail_newsletters' );
			}
		}		
		if ( isset($_POST['can_send_newsletters']) ) {	
			switch ( $_POST['can_send_newsletters'] ) {
				case "author":
					$role_author->add_cap( 'send_easymail_newsletters' );
					$role_editor->add_cap( 'send_easymail_newsletters' );				
					break;
				case "editor":
					$role_editor->add_cap( 'send_easymail_newsletters' );
					$role_author->remove_cap( 'send_easymail_newsletters' );	
					break;
				case "administrator":
				default:
					$role_author->remove_cap( 'send_easymail_newsletters' );
					$role_editor->remove_cap( 'send_easymail_newsletters' );
					$role_editor->remove_cap( 'manage_easymail_newsletters' );
			}
		}
		if ( isset($_POST['can_manage_subscribers']) ) {
			switch ( $_POST['can_manage_subscribers'] ) {
				case "editor":
					$role_editor->add_cap( 'manage_easymail_subscribers' );
					break;
				case "administrator":
				default:
					$role_editor->remove_cap( 'manage_easymail_subscribers' );
			}
		}
		if ( isset($_POST['can_manage_options']) ) {
			switch ( $_POST['can_manage_options'] ) {
				case "editor":
					$role_editor->add_cap( 'manage_easymail_options' );
					break;
				case "administrator":
				default:
					$role_editor->remove_cap( 'manage_easymail_options' );
			}
		}
		//echo "<pre style='font-size:80%'>";print_r($wp_roles);echo "</pre>";			
	}
	// --------
    echo '<div id="message" class="updated fade"><p>'. __("Updated", "alo-easymail") .'</p></div>';
}?>

<script type="text/javascript">
	jQuery(function() {
		jQuery('#slider').tabs({ fx: { opacity: 'toggle', duration:'fast' }  });
	});
</script>

<!--<div class="wrap">-->


<div id="slider" class="wrap">
<div class="icon32" id="icon-options-general"><br></div>
<h2>Alo EasyMail Newsletter Options</h2>

<ul id="tabs">
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#general">' . __("General", "alo-easymail") .'</a></li>'; ?>
	<li><a href="#texts"><?php _e("Texts", "alo-easymail") ?></a></li>
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#batchsending">' . __("Batch sending", "alo-easymail") .'</a></li>'; ?>
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#permissions">' . __("Permissions", "alo-easymail") .'</a></li>'; ?>
	<li><a href="#mailinglists"><?php _e("Mailing Lists", "alo-easymail") ?></a></li>
</ul>


<!-- --------------------------------------------
GENERAL
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<div id="general">

<form action="#general" method="post">
<h2><?php _e("General", "alo-easymail") ?></h2>

<table class="form-table"><tbody>
<tr valign="top">
<th scope="row"><label for="lastposts"><?php _e("Number of last posts to display", "alo-easymail") ?>:</label></th>
<td><input type="text" name="lastposts" value="<?php echo get_option('ALO_em_lastposts') ?>" id="lastposts" size="2" maxlength="2" />
<span class="description"><?php _e("Number of recent posts to show in the dropdown list of the newsletter sending form", "alo-easymail");?></span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="sender_email"><?php _e("Sender's email address", "alo-easymail") ?>:</label></th>
<td><input type="text" name="sender_email" value="<?php echo get_option('ALO_em_sender_email') ?>" id="sender_email" size="30" maxlength="100" /></td>
</tr>

<?php 
if ( get_option('ALO_em_subsc_page') ) {
	$selected_subscripage = get_option('ALO_em_subsc_page');
} else {
	$selected_subscripage = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Subscription page", "alo-easymail") ?>:</th>
<td>
<?php
$args = array(
	'numberposts' => -1,
	'post_type' => 'page',
	'order' => 'ASC',
	'orderby' => 'title'
); 
$get_pages = get_posts($args);
if ( count($get_pages) ) {
	echo "<select name='subsc_page' id='subsc_page'>";
	foreach($get_pages as $page) :
		echo "<option value='".$page->ID."' ". ( ($page->ID == $selected_subscripage)? " selected='selected'": "") .">#". $page->ID ." ". get_the_title ($page->ID) ." </option>";
	endforeach;
	echo "</select>\n";
}
?>
<br /><span class="description"><?php _e("This should be the page that includes the [ALO-EASYMAIL-PAGE] shortcode. By default, this page is titled &#39;Newsletter&#39;", "alo-easymail") ?>.</span></td>
</tr>


<?php 
if ( get_option('ALO_em_show_subscripage') == "yes" ) {
	$checked_show_subscripage = 'checked="checked"';
} else {
	$checked_show_subscripage = "";
}
//$subcripage_link = "<a href='" . get_permalink(get_option('ALO_em_subsc_page')) . "'>" . get_the_title (get_option('ALO_em_subsc_page')) . "</a>";
?>
<tr valign="top">
<th scope="row"><?php _e("Show subscription page", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="show_subscripage" id="show_subscripage" value="yes" <?php echo $checked_show_subscripage ?> /> <span class="description"><?php _e("If yes, the subscription page appears in menu or widget that list all blog pages", "alo-easymail") ?>.</span></td>
</tr>

<?php 
if ( get_option('ALO_em_embed_css') == "yes" ) {
	$checked_embed_css = 'checked="checked"';
} else {
	$checked_embed_css = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Embed CSS file", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="embed_css" id="embed_css" value="yes" <?php echo $checked_embed_css ?> /> <span class="description"><?php _e("If yes, the plugin loads the CSS styles from a file in its directory", "alo-easymail") ?>. <?php _e("Tip: copy &#39;alo-easymail.css&#39; to your theme directory and edit it there. Useful to prevent the loss of styles when you upgrade the plugin", "alo-easymail") ?>.</span></td>
</tr>


<tr valign="top">
<th scope="row"><?php _e("Default template for the email content", "alo-easymail") ?>:</th>
<td>    

<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
<?php the_editor(get_option('ALO_em_template')); ?>
</div></div>
</td></tr>


<?php 
if ( get_option('ALO_em_delete_on_uninstall') == "yes" ) {
	$checked_delete_on_uninstall = 'checked="checked"';
} else {
	$checked_delete_on_uninstall = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Delete all plugin data on deactivation", "alo-easymail") ?>:</th>
<td><span class="description"><?php _e("On plugin deactivation, all plugin options, preferences and database tables (including all newsletters and subscribers data) will be definitely deleted", "alo-easymail");?>. <?php _e("If you need these data make sure you do a database backup before plugin deactivation", "alo-easymail");?>.</span><br />
<input type="checkbox" name="delete_on_uninstall" id="delete_on_uninstall" value="yes" <?php echo $checked_delete_on_uninstall ?> /><label for="delete_on_uninstall"> <?php _e("Delete all plugin data on deactivation", "alo-easymail") ?></label><br />
<input type="checkbox" name="delete_on_uninstall_2" id="delete_on_uninstall_2" value="yes" <?php echo $checked_delete_on_uninstall ?> /><label for="delete_on_uninstall_2"> <?php _e("Yes, I understand", "alo-easymail") ?>. <?php _e("Delete all plugin data on deactivation", "alo-easymail") ?></label>
</td>
</tr>

</tbody> </table>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end general -->

<?php endif; /* only admin can */ ?>

<!-- --------------------------------------------
TEXTS
--------------------------------------------  -->

<div id="texts">

<form action="#texts" method="post">
<h2><?php _e("Widget/Page Texts", "alo-easymail") ?></h2>

<table class="form-table"><tbody>
<tr valign="top">
<th scope="row">
<h4><?php _e("For registered users", "alo-easymail") ?></h4>
</th><td></td>
</tr>

<tr valign="top">
<th scope="row"><label for="optin_msg"><?php _e("Optin message", "alo-easymail") ?>:</label></th>
<td><input type="text" name="optin_msg" value="<?php echo get_option('ALO_em_optin_msg') ?>" id="optin_msg" size="50" maxlength="100" />
<br /><span class="description"><?php _e("Default", "alo-easymail");?>: <?php _e("Yes, I would like to receive the Newsletter", "alo-easymail");?></span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="optout_msg"><?php _e("Optout message", "alo-easymail") ?>:</label></th>
<td><input type="text" name="optout_msg" value="<?php echo get_option('ALO_em_optout_msg') ?>" id="optout_msg" size="50" maxlength="100" />
<br /><span class="description"><?php _e("Default", "alo-easymail");?>: <?php _e("No, please do not email me", "alo-easymail");?></span></td>
</tr>

<tr valign="top">
<th scope="row">
<h4><?php _e("For all subscribers", "alo-easymail") ?></h4>
</th><td></td>
</tr>
<tr valign="top">
<th scope="row"><label for="lists_msg"><?php _e("Invite to join mailing lists", "alo-easymail") ?>:</label></th>
<td><input type="text" name="lists_msg" value="<?php echo get_option('ALO_em_lists_msg') ?>" id="lists_msg" size="50" maxlength="100" />
<br /><span class="description"><?php _e("Default", "alo-easymail");?>: <?php _e("You can also sign up for specific lists", "alo-easymail");?></span></td>
</tr>
</tbody> </table>
    
<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end Texts -->


<!-- --------------------------------------------
BATCH SENDING
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<div id="batchsending">

<form action="#batchsending" method="post">
<h2><?php _e("Batch sending", "alo-easymail") ?></h2>



<table class="form-table"><tbody>
<tr valign="top">
<th scope="row"><label for="batchrate"><?php _e("Maximum number of emails that can be sent per batch", "alo-easymail") ?>:</label></th>
<td><input type="text" name="batchrate" value="<?php echo get_option('ALO_em_batchrate') ?>" id="batchrate" size="5" maxlength="3" />
<span class="description">(10 - 300)</span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="dayrate"><?php _e("Maximum number of emails that can be sent in a 24-hr period", "alo-easymail") ?>:</label></th>
<td><input type="text" name="dayrate" value="<?php echo get_option('ALO_em_dayrate') ?>" id="dayrate" size="5" maxlength="5" />
<span class="description">(300 - 10000)</span></td>
</tr>
</tbody> </table>

<div style="background-color:#ddd;margin-top:15px;padding:10px 20px 15px 20px"><h4><?php _e("Important advice to calculate the best limit", "alo-easymail") ?></h4>
<ol style="font-size:80%;">
	<li><?php _e("Ask your provider the cut-off of emails you can send per day. Multiplying the hourly limit by 24 is not the right way to calculate it: very often the resulting number is much higher than the actual cut-off.", "alo-easymail") ?></li>
	<li><?php _e("Subtract from this cut-off the number of emails you want to send from your blog (e.g. registration procedures, activation and unsubscribing of EasyMail, notices from other plugins etc.).", "alo-easymail") ?></li>
	<li><?php _e("If in doubt, just choose a number definitely lower than the cut-off: you'll have more chances to have your mail delivered, and less chances to end up in a blacklist...", "alo-easymail") ?></li>
	<li><?php _e("For more info, visit the FAQ of the site.", "alo-easymail") ?> <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/#faq-8" target="_blank" title="<?php _e("For more info, visit the FAQ of the site.", "alo-easymail") ?>">&raquo;</a></li>      	  
</ol>
</div>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end Batch sending -->

<?php endif; /* only admin can */ ?>

<!-- --------------------------------------------
PERMISSIONS
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<?php // load roles names
$rolenames = $wp_roles->get_names(); // get a list of values, containing pairs of: $role_name => $display_name
// get roles to check cap
$get_author = get_role( 'author' );
$get_editor = get_role( 'editor' );
?>
<div id="permissions">

<form action="#permissions" method="post">
<h2><?php _e("Permissions", "alo-easymail") ?></h2>

<table class="form-table"><tbody>
<tr valign="top">
<th scope="row"><?php _e("The lowest role can send newsletters", "alo-easymail") ?>:</th>
<td>
<?php
if ( $get_author ->has_cap ('send_easymail_newsletters') ) {
	$selected_editor	= "";
	$selected_author	= "selected='selected'";
	$selected_admin		= "";
} else if ( $get_editor ->has_cap ('send_easymail_newsletters') ) {
	$selected_editor	= "selected='selected'";
	$selected_author	= "";
	$selected_admin		= "";
} else { // admin
	$selected_editor	= "";
	$selected_author	= "";
	$selected_admin		= "selected='selected'";
}
?>
<select name="can_send_newsletters" id="can_send_newsletters">
	<option value='admin' <?php echo $selected_admin; ?> ><?php echo translate_user_role ($rolenames['administrator']) ?> </option>
	<option value='editor' <?php echo $selected_editor; ?> ><?php echo translate_user_role ($rolenames['editor']) ?> </option>
	<option value='author' <?php echo $selected_author; ?> ><?php echo translate_user_role ($rolenames['author']) ?> </option>
</select><br />
<span class="description"> <?php _e("The user with this capability can only send newletters, but cannot manage them (view the report, delete)", "alo-easymail") ?>.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("The lowest role can manage newsletters", "alo-easymail") ?>:</th>
<td>
<?php 
if ( $get_editor ->has_cap ('manage_easymail_newsletters') ) {
	$selected_editor	= "selected='selected'";
	$selected_admin		= "";
} else { // admin
	$selected_editor	= "";
	$selected_admin		= "selected='selected'";
}
?>
<select name="can_manage_newsletters" id="can_manage_newsletters">
	<option value='admin' <?php echo $selected_admin; ?> ><?php echo translate_user_role ($rolenames['administrator']) ?> </option>
	<option value='editor' <?php echo $selected_editor; ?> ><?php echo translate_user_role ($rolenames['editor']) ?> </option>
</select><br />
<span class="description"> <?php _e("The user with this capability can manage own newsletters (view the report, delete)", "alo-easymail") ?>.<br />
<?php _e("Note: to let a user manage newsletters of other users, this user must have the capability to manage subscribers too", "alo-easymail") ?>.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("The lowest role can manage subscribers", "alo-easymail") ?>:</th>
<td>
<?php 
if ( $get_editor ->has_cap ('manage_easymail_subscribers') ) {
	$selected_editor	= "selected='selected'";
	$selected_admin		= "";
} else { // admin
	$selected_editor	= "";
	$selected_admin		= "selected='selected'";
}
?>
<select name="can_manage_subscribers" id="can_manage_subscribers">
	<option value='admin' <?php echo $selected_admin; ?> ><?php echo translate_user_role ($rolenames['administrator']) ?> </option>
	<option value='editor' <?php echo $selected_editor; ?> ><?php echo translate_user_role ($rolenames['editor']) ?> </option>
</select>
<br />
<span class="description"> <?php _e("The user with this capability can manage subscribers (add, delete, assign to mailing lists...)", "alo-easymail") ?>.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("The lowest role can manage options", "alo-easymail") ?>:</th>
<td>
<?php 
if ( $get_editor ->has_cap ('manage_easymail_options') ) {
	$selected_editor	= "selected='selected'";
	$selected_admin		= "";
} else { // admin
	$selected_editor	= "";
	$selected_admin		= "selected='selected'";
}
?>
<select name="can_manage_options" id="can_manage_options">
	<option value='admin' <?php echo $selected_admin; ?> ><?php echo translate_user_role ($rolenames['administrator']) ?> </option>
	<option value='editor' <?php echo $selected_editor; ?> ><?php echo translate_user_role ($rolenames['editor']) ?> </option>
</select><br />
<span class="description"> <?php _e("The user with this capability can set up these setting sections", "alo-easymail") ?>: 
<?php _e("Texts", "alo-easymail") ?>, 
<?php _e("Mailing Lists", "alo-easymail") ?>.<br />
<?php _e("Other sections can be modified only by administrators", "alo-easymail") ?>.
</span>
</td>
</tr>

</tbody> </table>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="" /> <?php // reset task ?>
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>"  class="button-primary" />
</p>
</form>

</div> <!-- end permissions -->

<?php endif; /* only admin can */ ?>

<!-- --------------------------------------------
MAILING LISTS 
--------------------------------------------  -->
<div id="mailinglists">

<h2><?php _e("Mailing Lists", "alo-easymail"); ?></h2>

<?php //echo "<pre style='font-size:80%'>"; print_r( $_REQUEST ); echo "</pre>"; // DEBUG ?>

<?php 
// If exists, get the id list to work on	
if ( isset( $_REQUEST['list_id'] ) ) {
	$list_id = stripslashes ( $wpdb->escape ( $_REQUEST['list_id'] ) );
	if ( !is_numeric ( $list_id ) ) $list_id = false;
} else {
	$list_id = false;
}
	
// Updating Request...
if ( isset( $_REQUEST['task'] ) ) {
	switch ( $_REQUEST['task'] ) {
		case "edit_list":	// EDIT an existing Mailing list
			if ( $list_id ) {
				$mailinglists = ALO_em_get_mailinglists ( 'hidden,admin,public' );
				$list_name = $mailinglists [$list_id]["name"];
				$list_available = $mailinglists [$list_id]["available"];	
				$list_order = $mailinglists [$list_id]["order"];		
			} else {
				echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
			}				
			break;
		case "save_list":	// SAVE a mailing list (add or update)
			if ( isset($_REQUEST['submit_list']) ) {
				$list_name = stripslashes( trim( $_POST['elp_list_name'] ) );
				$list_available = stripslashes( trim( $_POST['elp_list_available'] ) );
				$list_order = stripslashes( trim( $_POST['elp_list_order'] ) );
				if ( $list_name && $list_available && is_numeric($list_order) ) {
					$mailinglists = ALO_em_get_mailinglists ( 'hidden,admin,public' );
					if ( $list_id )  { // update
						$mailinglists [$list_id] = array ( "name" => $list_name, "available" => $list_available, "order" => $list_order );
					} else { // or add a new
						if ( empty($mailinglists) ) { // if 1st list, skip index 0
							$mailinglists [] = array ( "name" => "not-used", "available" => "deleted", "order" => "");
						}	
						$mailinglists [] = array ( "name" => $list_name, "available" => $list_available, "order" => $list_order);
					}
					if ( ALO_em_save_mailinglists ( $mailinglists ) ) {
						unset ( $list_id );
						unset ( $list_name );
						unset ( $list_available );						
						unset ( $list_order );	
						echo '<div id="message" class="updated fade"><p>'. __("Updated", "alo-easymail") .'</p></div>';
					} else {
						echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
					}
				} else {
					echo '<div id="message" class="error"><p>'. __("Inputs are incompled or wrong. Please check and try again.", "alo-easymail") .'</p></div>';
				}
			}	
			break;
		case "del_list":	// DELETE a Mailing list
			if ( $list_id  ) {
				$mailinglists = ALO_em_get_mailinglists ( 'hidden,admin,public' );
				//$mailinglists [$list_id]["available"] = "deleted";
				unset ( $mailinglists [$list_id] );
				if ( ALO_em_save_mailinglists ( $mailinglists ) && ALO_em_delete_all_subscribers_from_lists ($list_id) ) {	
					unset ( $list_id );
					unset ( $list_name );
					unset ( $list_available );	
					unset ( $list_order );				
					echo '<div id="message" class="updated fade"><p>'. __("Updated", "alo-easymail") .'</p></div>';
				} else {
					echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
				}					
			} else {
				echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
			}				
			break;								
	}
}
?>
	   	
<div style="padding: 10px">
<?php _e("You can setup mailing lists. For each you have to specify the name, the order (the lowest appear at top) and the availability", "alo-easymail") ?>:
<ul style="margin:10px">
<li><code><?php _e('hidden', 'alo-easymail')?></code>: <span class="description"><?php _e('the list can be shown only here in settings and nowhere in the site', 'alo-easymail')?></span></li>
<li><code><?php _e('admin side only', 'alo-easymail')?></code>: <span class="description"><?php _e('the list is available only for administratrion use (settings, sending page, subscribers), so subscribers cannot see it', 'alo-easymail')?></span></li>
<li><code><?php _e('entire site', 'alo-easymail')?></code>: <span class="description"><?php _e('the list is available in the whole site, so subscribers can see it', 'alo-easymail')?></span></li>
</ul>
</div>

<h3><?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) { _e("Edit list", "alo-easymail"); } else { _e("New list", "alo-easymail"); } ?></h3>
<!-- Edit the new/selected list-->
<form action="#mailinglists" method="post">
<table <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id) echo "style='background-color:#FFFFC0'" ?> ><tbody>
<tr valign="top">
	<th><?php _e('List name', 'alo-easymail') ?></th>
	<th><?php _e('Availability', 'alo-easymail') ?></th>
	<th><?php _e('Order', 'alo-easymail') ?></th>
	<th></th>
</tr>	
<tr><td><input type="text" name="elp_list_name" value="<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) echo $list_name; ?>" id="elp_list_name" size="30" maxlength="50" /></td>
<td><select name="elp_list_available" id="elp_list_available">
		<option value='hidden' <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id && $list_available == 'hidden') echo 'selected="selected"'; ?> ><?php _e('hidden', 'alo-easymail') ?> </option>
		<option value='admin' <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id && $list_available == 'admin') echo 'selected="selected"'; ?> ><?php echo __('admin side only', 'alo-easymail') ?> </option>
		<option value='public' <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id && $list_available == 'public') echo 'selected="selected"'; ?> ><?php echo __('entire site', 'alo-easymail') ?> </option>
	</select></td>
<td><input type="text" name="elp_list_order" value="<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_order ) { echo $list_order; }else{ echo '0'; }; ?>" id="elp_list_order" size="3" maxlength="3" /></td>
<td>
	<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
	<input type="hidden" name="task" value="save_list" />
	<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) { ?>
		<input type="hidden" name="list_id" value="<?php echo $list_id ?>" />
	<?php } else { ?>
		<input type="hidden" name="list_id" value="" />	
	<?php }  ?>
	<input type="submit" name="submit_list" value="<?php _e('Save', 'alo-easymail') ?>"  class="button-primary" />
	<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) { ?>
		<a href='options-general.php?page=alo-easymail/alo-easymail_options.php#mailinglists' title="<?php _e('Cancel', 'alo-easymail') ?>" ><?php _e('Cancel', 'alo-easymail') ?></a>
	<?php } ?>
</td>
</tr>
</tbody> </table>

</form>

<h3><?php _e("Mailing Lists", "alo-easymail") ?></h3>    
<table class="widefat">
<thead><tr valign="top">
<!--<th scope="col"><?php _e('ID', 'alo-easymail') ?></th>-->
<th scope="col" style="width:40%"><?php _e('List name', 'alo-easymail') ?></th>
<th scope="col"><?php _e('Availability', 'alo-easymail') ?></th>
<th scope="col"><?php _e('Order', 'alo-easymail') ?></th>
<th scope="col"><?php _e('Subscribers', 'alo-easymail') ?></th>
<th scope="col"><?php _e('Action', 'alo-easymail') ?></th>
</tr></thead>
<tbody>
<?php

$tab_mailinglists = ALO_em_get_mailinglists( 'hidden,admin,public' );
if ($tab_mailinglists) {
	foreach ( $tab_mailinglists as $list => $val) { 
		if ($val['available'] == "deleted") continue; 
		?>
		<tr>
			<!--<td><?php echo $list // id list ?></td>-->
			<td><strong><?php echo $val['name'] ?></strong></td>
			<td><?php
				switch ($val['available']) {
					case "hidden":
						echo __('hidden', 'alo-easymail');
						break;
					case "admin":
						echo __('admin side only', 'alo-easymail');
						break;
					case "public":
						echo __('entire site', 'alo-easymail');
						break;
					default:
				}
				?>
			</td>
			<td><strong><?php echo $val['order'] ?></strong></td>
			
			<td><?php echo count ( ALO_em_get_recipients_subscribers( $list ) ) ?></td>
			
			<td><?php
				echo "<a href='options-general.php?page=alo-easymail/alo-easymail_options.php&amp;task=edit_list&amp;list_id=". $list . "&amp;rand=".rand(1,99999)."#mailinglists' title='".__("Edit list", "alo-easymail")."' >";
				echo "<img src='".ALO_EM_PLUGIN_URL."/images/16-edit.png' /></a>";
				echo " ";
				echo "<a href='options-general.php?page=alo-easymail/alo-easymail_options.php&amp;task=del_list&amp;list_id=". $list . "&amp;rand=".rand(1,99999)."#mailinglists' title='".__("Delete list", "alo-easymail")."' ";
				echo " onclick=\"return confirm('".__("Do you really want to DELETE this list?", "alo-easymail")."');\">";
				echo "<img src='".ALO_EM_PLUGIN_URL."/images/trash.png' /></a>";
				?>
			</td>
		</tr>
	<?php 
	}
} else { ?>
	<tr><td colspan="4"><?php _e('There are no available lists', 'alo-easymail') ?></td></tr>
<?php
}
?>
</tbody> </table>

<?php //echo "<pre style='font-size:80%'>"; print_r( $tab_mailinglists ); echo "</pre>"; // DEBUG ?>

</div> <!-- end Mailing Lists -->

<p><?php echo ALO_EM_FOOTER; ?></p>

</div><!-- end wrap -->
