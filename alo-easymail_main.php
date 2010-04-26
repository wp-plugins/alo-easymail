<?php // No direct access, only through WP
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('You can\'t call this page directly.'); ?>

<div class="wrap">
    <div id="icon-index" class="icon32"><br /></div>
    <h2>Alo EasyMail Newsletter</h2>
    <div id="dashboard-widgets-wrap">
    
    
<?php 
/**
 * --- start MAIN --------------------------------------------------------------
 */
?>

<?php
// If admin see more info about newsletters
$can_see_all = ($user_level >= 8)? true: false;

?>

<?php
/**
 * Cancel one of own newsletter in sending queue
 */
if ( isset( $_REQUEST['task']) && $_REQUEST['task'] == "del_send" && isset( $_REQUEST['id'])) {
	$where_user = ($can_see_all)? "" : " AND user = %d ";
	$check_id = $wpdb->query( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_sendings WHERE ID = %d {$where_user}", $_REQUEST['id'], $user_ID ) );
	if ($check_id) {
		if ( $wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_sendings WHERE ID = %d", $_REQUEST['id'], $user_ID )) ) {		
			echo '<div id="message" class="updated fade"><p><strong>Newsletter successfully deleted</strong></p></div>';
		} else {
			echo '<div id="message" class="error"><p><strong>Impossible to delete the selected newsletter</strong></p></div>';		
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
			$fbk_msg .= '<strong>New sending added with success!</strong></p>';
			$fbk_msg .= "</div>";
			break;
		case 'error':		// error in inputs
			$fbk_msg = '<div id="message" class="error">';
			$fbk_msg .= '<p><img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/no.png" /> ';
			$fbk_msg .= '<strong>Inputs are incompled or wrong. Please check and try again.</strong></p>';
			$fbk_msg .= "</div>";
			break;
		case 'nosending':	// error on sending
			$fbk_msg = '<div id="message" class="error">';
			$fbk_msg .= '<p><img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/no.png" /> ';
			$fbk_msg .= '<strong>Impossible to send. Please try again.</strong></p>';
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
    tb_show('NEWSLETTER REPORT',"<?php echo $linkthick ?>&id="+id+"&TB_iframe=true&height=430&width=600",false);
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
		<caption><strong>Newsletters scheduled for sending</strong> (<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?page=alo-easymail/alo-easymail_main.php">refresh&raquo;</a>)</caption>
		<thead><tr>
			<th scope="col" style="width:5%"><div style="text-align: center;">Queue</div></th>
			<th scope="col" style="width:15%">Scheduled by</th>
			<th scope="col" >Added on</th>
			<th scope="col">Subject</th>
			<th scope="col" style="width:10%">Progress</th>			
			<th scope="col" style="width:15%">Action</th>
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
		    	echo '<img src="'.get_option ('home').'/wp-content/plugins/alo-easymail/images/16-email-forward.png" title="now sending" alt="" />';
			   } else {
			    echo $row_count; 
			   }
			   ?>
        </th>
		<td><?php 
			if ($q->user == $user_ID) {
				echo "<strong>you</strong>";
			} else {
				if ($can_see_all) {
					echo get_usermeta($q->user, 'nickname');
				} else {
					echo"<em>another user</em>";
				}
			}
		?></td>
		<td><?php echo date("d/m/Y", strtotime($q->start_at))." h.".date("H:i", strtotime($q->start_at)) ?></td>
		<td><?php echo ($q->user == $user_ID || $can_see_all)? $q->subject : ""; ?></td>
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
			<?php if ($q->user == $user_ID || $can_see_all) {
				echo "<a href='edit.php?page=alo-easymail/alo-easymail_main.php&amp;task=del_send&amp;id=".$q->ID."' title='Stop and cancel' ";
				echo " onclick=\"return confirm('Do you really want to stop and cancel this sending');\">";
				echo "Cancel</a>";
			} 
		?></td>
		<?
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
$where_user = ($can_see_all)? "" : "AND user=".$user_ID;
$news_done =  $wpdb->get_results("SELECT * FROM {$wpdb->prefix}easymail_sendings WHERE sent = 1 {$where_user} ORDER BY ID DESC");
//echo "<pre>";print_r($news_on_queue);echo "</pre>";
if (count($news_done)) { ?>
	<table class="widefat" style='margin-top:10px'>
		<caption><strong>Newsletters sent BY <?php echo ($can_see_all==false)? "YOU" : "ALL USERS" ?></strong></caption>
		<thead><tr>
			<th scope="col" style="width:5%"><div style="text-align: center;">#</div></th>
			<?php if ($can_see_all) echo '<th scope="col" style="width:15%">Scheduled by</th>'; ?>
			<th scope="col">Added on</th>
			<th scope="col">Completed</th>
			<th scope="col">Subject</th>
			<th scope="col" style="width:15%">Report</th>
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
		<?php if ($can_see_all) {
			echo "<td>". ( ($q->user == $user_ID)? "<strong>you</strong>": get_usermeta($q->user, 'nickname') ). "</td>";
		} ?>
		<td><?php echo date("d/m/Y", strtotime($q->start_at))." h.".date("H:i", strtotime($q->start_at)) ?></td>
		<td><?php echo date("d/m/Y", strtotime($q->last_at))." h.".date("H:i", strtotime($q->last_at)) ?></td>
		<td><?php echo ($q->user == $user_ID || $can_see_all)? $q->subject : "" ?></td>
		<td>
			<?php if ($q->user == $user_ID || $can_see_all) {
				echo "<a href='edit.php?page=alo-easymail/alo-easymail_main.php&amp;task=del_send&amp;id=".$q->ID."' title='Delete the report' ";
				echo " onclick=\"return confirm('Do you really want to delete the report of this newsletter?');\">";
				echo "Cancel</a> - ";
				echo "<a href='' title='View the report' ";
				echo " onclick=\"return openReport({$q->ID})\">";
				echo "View</a>";
			} 
		?></td>
		<?
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
		document.getElementById("response-emails-add").innerHTML = "Checking...";
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
		document.getElementById("response-emails-add").innerHTML = "<p style='color:#f00'>WARNING: some addresses seem to be wrong:<br />" + wrong_list + ".</p>";
	} else {
		document.getElementById("response-emails-add").innerHTML = "";
	}
    return false;
}

</script>

<form name="post" action="<?php echo get_option ('siteurl').'/' ?>wp-content/plugins/alo-easymail/alo-easymail_action.php" method="post" id="post" name="post" >

<h3>Recipients</h3>

<p style='margin-top:20px;'>Choose the kind of recipients (people who subscribe the newsletter or the registered users):</p>
<p><select name="select_recipients" id="select_recipients" >
    <option value="subscr" selected="selected">Subscribers</option>';
    <option value="users">Registered users</option>';
    <option value="none">None of the above</option>';
</select></p>

<p style='margin-top:20px;'>To send to other people insert a list of e-mail addresses separated by <strong>comma</strong> (,):</p>
<textarea id="emails_add" value="" name="emails_add" rows="3" cols="70" onblur="checkEmailList()"><?php echo get_usermeta($user_ID,'ALO_em_list'); ?></textarea>
<div id="response-emails-add"></div>

<p><input type="checkbox" name="ck_save_list" id="ck_save_list" value="checked" checked="checked" />
<label for="ck_save_list">Save the list of email addresses for next sending</label></p>

<p>&nbsp;</p>

<h3>Subject and text of the e-mail</h3>
<p style='margin-top:20px;'>Choose to send a simple generic e-mail or one about a specific post (in the latter case you can use the specific tags listed below).
</p>

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
echo '<option value="0">[generic e-mail: no post selected]</option>';
if ($tot_posts) { 
    foreach($get_posts as $post) :
        $pID = $post->ID; // course ID
        echo '<option value="'.$post->ID.'" >&middot; '. $post->post_title.' </option>';
    endforeach;
}
echo '</select>'; 
?>


<p style='margin-top:20px;'><strong>Subject</strong>:</p>
<input type="text" size="70" name="input_subject" id="input_subject" value="" maxlength="150" />


<p style='margin-top:20px;'><strong>Main body</strong> (you can use the tags listed below):</p>

<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
<?php if (get_usermeta($user_ID,'ALO_em_template') == "") {
	$main_content = get_option('ALO_em_template');
} else {
	$main_content = get_usermeta($user_ID,'ALO_em_template'); // default template
}
the_editor ($main_content); ?>
</div></div>

<table style='background-color:#ffffff;padding:3px;width:100%;border:1px grey dotted;'>
<tr><td>[POST-TITLE]</td><td style='font-size:80%'><em>The link to the title of the selected post.</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[POST-EXCERPT]</td><td style='font-size:80%'><em>The excerpt (if any) of the post.</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[POST-CONTENT]</td><td style='font-size:80%'><em>The main content of the post. Warning: this tag inserts the test as it is, including shortcodes from other plugins.</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[USER-NAME]</td><td style='font-size:80%'><em>Name and surname of registered user. (For subscribers: the name used for registration)</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<!-- Following two lines [USER-FIRST-NAME] added GAL -->
<tr><td>[USER-FIRST-NAME]</td><td style='font-size:80%'><em>First name of registered user. (For subscribers: the name used for registration).</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[SITE-LINK]</td><td style='font-size:80%'><i>The link to the site.</i>E.g.: <?php echo "<a href='".get_option ('siteurl')."'>".get_option('blogname')."</a>" ?></td></tr>
</table>

<p><input type="checkbox" name="ck_save_template" id="ck_save_template" value="checked" checked="checked" />
<label for="ck_save_template">Save the main body as template for next sending</label></p>


<h3 style='margin-top:30px;'>Send</h3>

<p>Click <strong>once</strong> and <strong>wait</strong> for the sending to be over.</p>

<?php // Submit ?>
    <span class="submit">
    <?php wp_nonce_field('alo-easymail_main'); ?>
    <input type="submit" name="submit" id="submit" value="<?php echo (count($news_on_queue))? 'Add to sending queue' : 'Send';?>" style='font-weight:bold' onclick="this.value='PLEASE WAIT: sending...';"/>
    </span>     
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
