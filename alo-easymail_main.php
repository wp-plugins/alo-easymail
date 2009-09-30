<?php // No direct access, only through WP
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('You can\'t call this page directly.'); ?>

<?php // msg feedback
if($_REQUEST['message'] == 'success') { // Sent with success!
	print '<div id="message" class="updated fade"><p><strong>Report about sending e-mail</strong>
	<p>Sent e-mails: '.$_REQUEST['nsucc'].' of '.$_REQUEST['nsent'].'.</p>';
	
	// failed e-mails list
	if ($_REQUEST['listnosent']) {
echo <<<EOD
<script type="text/javascript">
    function toggle_visibility(id) {
        var e = document.getElementById(id);
        if(e.style.display == 'block')
          e.style.display = 'none';
        else
          e.style.display = 'block';
    }
</script>
EOD;
	    $listnosent = str_replace(";at;", "@", $_REQUEST['listnosent']);
        $nosendmails = explode(";end;", $listnosent);
        
	    echo "<p><a href='javascript:;' onclick=\"toggle_visibility('listnosent');return false\" >&raquo; Show no sent e-mail (one per line)</a></p>";    
	    echo "<div id='listnosent' style='color:#ff0000;display:none;margin:10px'><ul>";
	    foreach ($nosendmails as $mail) {
	        echo "<li>".$mail."</li>";
	    }
	    echo "</ul></div>";
	    echo "<p><a href='javascript:;' onclick=\"toggle_visibility('listnosent_comma');return false\" >&raquo; Show no sent e-mail (comma separated)</a></p>";
	    echo "<div id='listnosent_comma' style='color:#ff0000;display:none;margin:10px'>";
	    foreach ($nosendmails as $mail) {
	        echo $mail.",";
	    }
	    echo "</div>";
	}
	
	echo "</div>"; // end div message
}

if($_REQUEST['message'] == 'incorrect') { // incorrect data in fields
	print '<div id="message" class="updated fade"><p>Inserted inputs are incompled or wrong. Please check and try again.</p></div>';
}
if($_REQUEST['message'] == 'error') { // Generic error during sending
	print '<div id="message" class="updated fade"><p>Error during process. No sending, please try again.</p></div>';
}
?>

<div class="wrap">
    <div id="icon-index" class="icon32"><br /></div>
    <h2>Alo EasyMail</h2>
    <div id="dashboard-widgets-wrap">
    
    
<?php 
/**
 * --- start MAIN --------------------------------------------------------------
 */
?>

<form name="post" action="<?php echo get_option ('siteurl').'/' ?>wp-content/plugins/alo-easymail/alo-easymail_action.php" method="post" id="post">

<h3>Type of e-mail</h3>
<p style='margin-top:20px;'>Choose to send a simple generic e-mail or one concerning a specific post (in this case you can use specific tags listed below).
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

<h3>Recipients</h3>

<p style='margin-top:20px;'>To send to all users registered to the blog check this option:</p>
<p><input type="checkbox" name="ck_all_users" id="ck_all_users" value="checked" checked="checked" />
<label for="ck_all_users">All registered users</label></p>

<p style='margin-top:20px;'>To send to non-registered people insert a list of e-mail addressed, separated by <strong>comma</strong> (,):</p>
<textarea id="emails_add" value="" name="emails_add" rows="5" cols="70"></textarea>


<h3>Text</h3>

<p style='margin-top:20px;'><strong>Subject</strong>:</p>
<input type="text" size="70" name="input_subject" id="input_subject" value="" maxlength="150" />

<?php
// include found at: http://blog.zen-dreams.com/en/2008/11/06/how-to-include-tinymce-in-your-wp-plugin/ 
wp_admin_css('thickbox');
wp_print_scripts('jquery-ui-core');
wp_print_scripts('jquery-ui-tabs');
wp_print_scripts('post');
wp_print_scripts('editor');
add_thickbox();
wp_print_scripts('media-upload');
if (function_exists('wp_tiny_mce')) wp_tiny_mce();

?>

<p style='margin-top:20px;'><strong>Main body</strong> (you can use the tags listed below):</p>

<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
<?php the_editor(get_option('ALO_em_template')); ?>
</div></div>

<table style='background-color:#ffffff;padding:3px;width:100%;border:1px grey dotted;'>
<tr><td>[POST-TITLE]</td><td style='font-size:80%'><em>The title of the selected post.</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[POST-EXCERPT]</td><td style='font-size:80%'><em>The excerpt (if any) of the post.</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[USER-NAME]</td><td style='font-size:80%'><em>Name and surname of registered user.</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[SITE-LINK]</td><td style='font-size:80%'><i>The link to the site.</i>E.g.: <?php echo "<a href='".get_option ('siteurl')."'>".get_option('blogname')."</a>" ?></td></tr>
</table>


<h3 style='margin-top:30px;'>Send</h3>

<p><input type="checkbox" name="ck_save_template" id="ck_save_template" value="checked" checked="checked" />
<label for="ck_save_template">Save the main body as template for next sending (template always available on <em>Settings</em>)</label></p>

<p>Click <strong>once</strong> and <strong>wait</strong> while sending.</p>

<?php // Submit ?>
    <span class="submit">
    <?php wp_nonce_field('alo-easymail_main'); ?>
    <input type="submit" name="submit" value="Send" style='font-weight:bold' onclick="this.value='PLEASE WAIT: sending...';"/>
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
