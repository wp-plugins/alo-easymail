<?php // No direct access, only through WP
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('You can\'t call this page directly.'); 
if ( !current_user_can('send_easymail_newsletters') && !current_user_can('manage_easymail_newsletters') ) 	wp_die(__('Cheatin&#8217; uh?'));
?>


<div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2>Alo EasyMail Newsletter</h2>
    <div id="dashboard-widgets-wrap">

    
<?php 
/**
 * --- start MAIN --------------------------------------------------------------
 */
?>

<?php
// Possible levelIf can manage all newsletters
$can_edit_all	= ( current_user_can('manage_easymail_newsletters') && current_user_can('manage_easymail_subscribers') ) ? true: false;
$can_edit_own	= ( current_user_can('manage_easymail_newsletters') ) ? true: false;
$can_see_own	= ( current_user_can('send_easymail_newsletters') ) ? true: false;

// $can_see_all 	= ( current_user_can('manage_easymail_newsletters') ) ? true: false; //($user_level >= 8)?
?>

<?php
/**
 * Cancel one of own newsletter in sending queue
 */
if ( isset( $_REQUEST['task']) && $_REQUEST['task'] == "del_send" && isset( $_REQUEST['id'])) {
	$where_user = ( $can_edit_all )? "" : " AND user = %d ";
	$check_id = $wpdb->query( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_sendings WHERE ID = %d {$where_user}", $_REQUEST['id'], $user_ID ) );
	if ($check_id) {
		if ( ALO_em_delete_newsletter ( $_REQUEST['id'] ) ) {		
			echo '<div id="message" class="updated fade"><p><strong>'.__("Newsletter successfully deleted", "alo-easymail").'</strong></p></div>';
		} else {
			echo '<div id="message" class="error"><p><strong>'.__("Impossible to delete the selected newsletter", "alo-easymail").'</strong></p></div>';		
		}
		
	}
}
/**
 * If feedback message 
 */
if ( isset( $_REQUEST['message'])) :
	switch($_REQUEST['message']) {
		/*case 'inprogress':	// others newsletter in queue
			$fbk_msg .= '<p><strong>Already sending a newsletter.</strong></p>';
			break;*/
		case 'success':		// ok, sending scheduled
			$fbk_msg = '<div id="message" class="updated fade">';
			$fbk_msg .= '<p><img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/16-email-add.png" /> ';
			$fbk_msg .= '<strong>'.__("New sending added with success!", "alo-easymail").'</strong></p>';
			$fbk_msg .= "</div>";
			break;
		case 'error':		// error in inputs
			$fbk_msg = '<div id="message" class="error">';
			$fbk_msg .= '<p><img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/no.png" /> ';
			$fbk_msg .= '<strong>'.__("Inputs are incompled or wrong. Please check and try again.", "alo-easymail").'</strong></p>';
			$fbk_msg .= "</div>";
			break;
		case 'norecipients': // no recipients selected
			$fbk_msg = '<div id="message" class="error">';
			$fbk_msg .= '<p><img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/no.png" /> ';
			$fbk_msg .= '<strong>'.__("No recipients selected.", "alo-easymail").'</strong></p>';
			$fbk_msg .= "</div>";
			break;
		case 'nosending':	// error on sending
			$fbk_msg = '<div id="message" class="error">';
			$fbk_msg .= '<p><img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/no.png" /> ';
			$fbk_msg .= '<strong>'.__("Impossible to send. Please try again.", "alo-easymail").'</strong></p>';
			$fbk_msg .= "</div>";
			break;
		default:
	}
	// print feedback
	echo $fbk_msg;
endif; // end if ( isset( $_REQUEST['message']))
?>

<?php
$linkthick = wp_nonce_url( get_option ('home').'/wp-content/plugins/alo-easymail/alo-easymail_report.php?', 'alo-easymail_main');
?>

<script language="javascript">
function openReport(id){
    // tb_show('REPORT',"<?php echo get_option ('siteurl').'/' ?>wp-content/plugins/alo-easymail/alo-easymail_action.php?TB_iframe=true&height=430&width=600",false);
    tb_show( '<?php _e("Newsletter report", "alo-easymail") ?>',"<?php echo $linkthick ?>&id="+id+"&TB_iframe=true&height=430&width=600",false);
    //alert("<?php echo $linkthick ?>&TB_iframe=true&height=430&width=600");
    return false;
}
</script>

<?php
/**
 * Search for newsletters TO SEND in queue
 */
$news_on_queue =  $wpdb->get_results("SELECT * FROM {$wpdb->prefix}easymail_sendings WHERE sent = 0 ORDER BY ID ASC");
//echo "<pre>";print_r($news_on_queue);echo "</pre>";
if (count($news_on_queue)) { ?>
	<table class="widefat" style='margin-top:10px'>
		<caption><strong><?php _e("Newsletters scheduled for sending", "alo-easymail") ?></strong> (<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?page=alo-easymail/alo-easymail_main.php"><?php _e("refresh", "alo-easymail") ?>&raquo;</a>)</caption>
		<thead><tr>
			<th scope="col" style="width:5%"><div style="text-align: center;"><?php _e("Queue", "alo-easymail") ?></div></th>
			<th scope="col" style="width:15%"><?php _e("Scheduled by", "alo-easymail") ?></th>
			<th scope="col" ><?php _e("Added on", "alo-easymail") ?></th>
			<th scope="col"><?php _e("Subject", "alo-easymail") ?></th>
			<th scope="col" style="width:10%"><?php _e("Progress", "alo-easymail") ?></th>			
			<th scope="col" style="width:15%"><?php _e("Action", "alo-easymail") ?></th>
		</tr></thead>
		<tbody id="the-list">
	<?php
	$class = 'alternate';
	$row_count = 0;
	foreach ($news_on_queue as $q) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		$class = ($row_count == 0) ? 'updated': $class;
		echo "<tr id='que-{$q->ID}' class='$class'>\n"; ?>
		<th scope="row" style="text-align: center;">
		    <?php if ($row_count == 0) {
		    	echo '<img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/16-email-forward.png" title="'.__("now sending", "alo-easymail").'" alt="" />';
			   } else {
			    echo $row_count; 
			   }
			   ?>
        </th>
		<td><?php 
			if ($q->user == $user_ID) {
				echo "<strong>".__("you", "alo-easymail")."</strong>";
			} else {
				if ( $can_edit_all ) {
					echo get_user_meta($q->user, 'nickname', true);
				} else {
					echo"<em>".__("another user", "alo-easymail")."</em>";
				}
			}
		?></td>
		<td><?php echo date("d/m/Y", strtotime($q->start_at))." h.".date("H:i", strtotime($q->start_at)) ?></td>
		<td><?php echo ($q->user == $user_ID || $can_edit_all )? stripslashes ( $q->subject ) : ""; ?></td>
		<td><?php 
			$q_recipients = unserialize( $q->recipients );
			$q_tot = count($q_recipients);
			$n_sent = 0;
			foreach ($q_recipients as $qr) {
		   		if ( isset($qr['result']) ) $n_sent ++;
		   	}
			echo round($n_sent*100/ $q_tot ) . " %" ;
		?></td>
		<td>
			<?php if ( ( $q->user == $user_ID && $can_edit_own ) || $can_edit_all ) {
				echo "<a href='edit.php?page=alo-easymail/alo-easymail_main.php&amp;task=del_send&amp;id=".$q->ID."' title='".__("Cancel", "alo-easymail")."' ";
				echo " onclick=\"return confirm('".__("Do you really want to stop and cancel this sending?", "alo-easymail")."');\">";
				echo __("Cancel", "alo-easymail"). "</a>";
			} 
		?></td>
		<?php
		echo "</tr>";
		$row_count++;
	}
	echo "</tbody></table>";
	echo "<p>&nbsp;</p>";
}
?>

<?php
/**
 * Search for newsletters ALREADY sent by the USER (of by ALL users, if admin)
 */
$where_user = ( $can_edit_all )? "" : "AND user=".$user_ID;
$news_done =  $wpdb->get_results("SELECT * FROM {$wpdb->prefix}easymail_sendings WHERE sent = 1 {$where_user} ORDER BY ID DESC");
//echo "<pre>";print_r($news_on_queue);echo "</pre>";
if (count($news_done)) { ?>
	<table class="widefat" style='margin-top:10px'>
		<caption><strong><?php echo ( $can_edit_all ==false)? __("Newsletters sent BY YOU", "alo-easymail") : __("Newsletters sent BY ALL USERS", "alo-easymail") ?></strong></caption>
		<thead><tr>
			<th scope="col" style="width:5%"><div style="text-align: center;">#</div></th>
			<?php if ( $can_edit_all ) echo '<th scope="col" style="width:15%">'.__("Scheduled by", "alo-easymail").'</th>'; ?>
			<th scope="col"><?php _e("Added on", "alo-easymail") ?></th>
			<th scope="col"><?php _e("Completed", "alo-easymail") ?></th>
			<th scope="col"><?php _e("Subject", "alo-easymail") ?></th>
			<th scope="col" style="width:15%"><?php _e("Report", "alo-easymail") ?></th>
		</tr></thead>
		<tbody id="the-list">
	<?php
	$class = 'alternate';
	$row_count = 0;
	foreach ($news_done as $q) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		echo "<tr id='news-done-{$q->ID}' class='$class'>\n"; ?>
		<th scope="row" style="text-align: center;">
		    <?php echo count($news_done) - $row_count;?>
        </th>
		<?php if ( $can_edit_all ) {
			echo "<td>". ( ($q->user == $user_ID)? "<strong>".__("you", "alo-easymail")."</strong>": get_user_meta($q->user, 'nickname', true) ). "</td>";
		} ?>
		<td><?php echo date("d/m/Y", strtotime($q->start_at))." h.".date("H:i", strtotime($q->start_at)) ?></td>
		<td><?php echo date("d/m/Y", strtotime($q->last_at))." h.".date("H:i", strtotime($q->last_at)) ?></td>
		<td><?php echo ($q->user == $user_ID || $can_edit_all )? stripslashes ( $q->subject ) : "" ?></td>
		<td>
			<?php if ( ($q->user == $user_ID && $can_edit_own ) || $can_edit_all ) {
				echo "<a href='edit.php?page=alo-easymail/alo-easymail_main.php&amp;task=del_send&amp;id=".$q->ID."' title='".__("Delete", "alo-easymail")."' ";
				echo " onclick=\"return confirm('".__("Do you really want to delete the report of this newsletter?", "alo-easymail")."');\">";
				echo __("Delete", "alo-easymail"). "</a> - ";
				echo "<a href='' title='".__("View", "alo-easymail")."' ";
				echo " onclick=\"return openReport({$q->ID})\">";
				echo __("View", "alo-easymail"). "</a>";
			} 
		?></td>
		<?php
		echo "</tr>";
		$row_count++;
	}
	echo "</tbody></table>";
	echo "<p>&nbsp;</p>";
}
?>

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


<?php wp_enqueue_script( 'jquery-form' );?>

<script type="text/javascript">
function openPopup(){
    var popup = window.open('','popup','toolbar=no,location=yes,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=740,height=300,left=0,top=0');
    post.submit();
    return false;
}

function checkEmailList () {
	var emaillist = document.getElementById("emails_add").value;
	// cut last comma, if any
	if ( emaillist.charAt(emaillist.length -1) == "," ) { 
	    document.getElementById("emails_add").value = emaillist.slice(0, -1);
	    emaillist = emaillist.slice(0, -1);
	}
	var wrong_list = "";
	if (emaillist) {
		document.getElementById("response-emails-add").innerHTML = "<?php _e("Checking...", "alo-easymail") ?>";
		// each addresses
		var lines = emaillist.split(",");
		
		for (x=0; x < lines.length; x++){
			var regmail = /^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/;
			if (!regmail.test(lines[x]))	{
				wrong_list += lines[x] + ", ";
			}
		}

	}
	if (wrong_list != "") {
		wrong_list = wrong_list.slice(0, -2);
		document.getElementById("response-emails-add").innerHTML = "<p style='color:#f00;'><em><?php _e("Warning! Some addresses seem to be wrong", "alo-easymail") ?>:</em><br />" + wrong_list + ".</p>";
	} else {
		document.getElementById("response-emails-add").innerHTML = "";
	}
    return false;
}

</script>

<h2><?php _e("Send newsletter", "alo-easymail") ?></h2>

<form name="post" action="<?php echo get_option ('siteurl').'/' ?>wp-content/plugins/alo-easymail/alo-easymail_action.php" method="post" id="post" name="post" >

<h3><?php _e("Recipients", "alo-easymail") ?></h3>

<table class="form-table">
<tbody>

<tr valign="top">
<th scope="row"><?php _e("Choose the kind of recipients", "alo-easymail") ?>:</th>
<td>
<div style="float:left;margin-right:40px"><strong><?php _e("Main groups", "alo-easymail"); ?>:</strong><ul>
	<li><input type="checkbox" name="all_subscribers" id="all_subscribers" value="checked" /><label for="all_subscribers"><?php echo __("All subscribers", "alo-easymail"). " (". count( ALO_em_get_recipients_subscribers() ) .")"; ?></label></li>
	<li><input type="checkbox" name="all_regusers" id="all_regusers" value="checked" /><label for="all_regusers"><?php echo __("All registered users", "alo-easymail"). " (". count ( ALO_em_get_recipients_registered () ) .")"; ?></label></li>	
</ul></div>
<?php // mailing lists
$mailinglists = ALO_em_get_mailinglists( 'admin,public' );
if ($mailinglists) { ?>
	<div style="float:left;margin-right:40px"><strong><?php _e("Mailing Lists", "alo-easymail"); ?>:</strong><ul>
	<?php	
	foreach ( $mailinglists as $list => $val) { 
		if ( $val['available'] == "deleted" || $val['available'] == "hidden" ) continue; ?>
		<li><input type="checkbox" name="check_list[]" id="list_<?php echo $list ?>" value="<?php echo $list ?>" /><label for="list_<?php echo $list ?>"><?php echo $val['name'] . " (".  count ( ALO_em_get_recipients_subscribers( $list ) ).")"; ?></label></li>
	<?php } ?>
	</ul></div>
<?php } // end if ?>
<div style="float:left;margin-right:40px;width:300px">
	<span class="description"><?php _e("Between brackets the number of recipients belonging to each group or list", "alo-easymail") ?>.<br />
	<?php _e("Do not worry about recipients belonging to more than one group or list: the plugin avoids sending twice to the same recipient", "alo-easymail") ?>.</span>
</div>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("To send to other people insert a list of e-mail addresses separated by comma (,)", "alo-easymail") ?>:</th>
<td><textarea id="emails_add" value="" name="emails_add" rows="3" cols="70" onblur="checkEmailList()"><?php echo get_option ( 'ALO_em_list_user_'.$user_ID, "" ); ?></textarea>
<div id="response-emails-add"></div></td>
</tr>

<tr valign="top">
<th scope="row"><label for="ck_save_list"><?php _e("Save the list of email addresses for next sending", "alo-easymail") ?></label></th>
<td valign="middle"><input type="checkbox" name="ck_save_list" id="ck_save_list" value="checked" checked="checked" /></td>
</tr>
</tbody>
</table>

<h3 style='margin-top:20px;'><?php _e("Subject and text of the e-mail", "alo-easymail") ?></h3>

<table class="form-table">
<tbody>

<tr valign="top">
<th scope="row"><?php _e("Choose to send a simple generic e-mail or one about a specific post", "alo-easymail") ?>
</th>

<td>
<?php
$n_last_posts = (get_option('ALO_em_lastposts'))? get_option('ALO_em_lastposts'): 10;
$args = array(
	'numberposts' => $n_last_posts,
	'order' => 'DESC',
	'orderby' => 'date'
	); 

$get_posts = get_posts($args);
$tot_posts = count($get_posts);

echo '<select name="select_post" id="select_post" >';
echo '<option value="0">['.__("generic e-mail: no post selected", "alo-easymail").']</option>';
if ($tot_posts) { 
    foreach($get_posts as $post) :
        $pID = $post->ID; // course ID
        echo '<option value="'.$post->ID.'" >&middot; '. $post->post_title.' </option>';
    endforeach;
}
echo '</select>'; 
?>
<br /><span class="description"><?php _e("If you choose a post you can use the post tags (see below) in the main content", "alo-easymail") ?></span>
</td>
</tr>

<tr valign="top">
<th scope="row"><strong><?php _e("Subject", "alo-easymail") ?></strong>:</th>
<td><input type="text" size="70" name="input_subject" id="input_subject" value="" maxlength="150" /></td>
</tr>


<tr valign="top">
<th scope="row"><strong><?php _e("Main body", "alo-easymail") ?></strong> <span class="description"><br />(<?php _e("you can use the tags listed below", "alo-easymail") ?>)</span>:</th>
<td> <?php // open td ?>
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
<?php if ( get_option ( 'ALO_em_template_user_'.$user_ID ) == "") {
	$main_content = get_option('ALO_em_template'); // default template
} else {
	$main_content = get_option ( 'ALO_em_template_user_'.$user_ID ) ;
}
the_editor ($main_content); ?>
</div></div>

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

</td> <?php // close td ?>
</tr>

<tr valign="top">
<th scope="row"><label for="ck_save_template"><?php _e("Save the main body as template for next sending", "alo-easymail") ?></label></th>
<td valign="middle"><input type="checkbox" name="ck_save_template" id="ck_save_template" value="checked" checked="checked" /></td>
</tr>
</tbody>
</table>

<h3 style='margin-top:20px;'><?php _e("Send", "alo-easymail") ?></h3>

<table class="form-table">
<tbody>
<tr valign="top">
<th scope="row"><label for="ck_tracking"><?php _e("Track when viewed", "alo-easymail") ?></label></th>
<td><input type="checkbox" name="ck_tracking" id="ck_tracking" value="ALO_EM" checked="checked" />
<span class="description"><?php echo __("The plugin tries to count how many recipients open the newsletter", "alo-easymail").". (". __("This feedback depends on recipients&#39; e-mail client, so the result is approximate and probably undersized", "alo-easymail").")." ?></span>
</td>
</tr>
<tr valign="top">
<th scope="row" style="text-align:right">
<?php // Submit ?>
    <span class="submit">
    <?php wp_nonce_field('alo-easymail_main'); ?>
    <input type="submit" name="submit" id="submit" value="<?php echo (count($news_on_queue))? __('Add to sending queue', 'alo-easymail') : __('Send', 'alo-easymail'); ?>" class="button-primary" onclick="this.value='<?php _e("PLEASE WAIT: sending...", "alo-easymail") ?>';"/>
    </span>   
</th>
<td valign="middle"><strong><?php _e("Click ONCE and wait for the sending to be over", "alo-easymail") ?>.</strong></td>
</tr>
</tbody>
</table>

</form>

<p></p>
<p><?php echo ALO_EM_FOOTER; ?></p>


<?php
/**
 * --- end MAIN ----------------------------------------------------------------
 */
?>

        </div>	
        
        
        <div class="clear">
        </div>
    </div>
</div><!-- wrap -->	
