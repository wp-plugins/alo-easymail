<?php // No direct access, only through WP
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('You can\'t call this page directly.'); ?>


<?php 
/**
 * --- start MAIN --------------------------------------------------------------
 */
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
</script>

<form name="post" action="<?php echo get_option ('siteurl').'/' ?>wp-content/plugins/alo-easymail/alo-easymail_action.php" method="post" id="post" name="post" target="popup" onSubmit="return openPopup()">

<h3>Recipients</h3>

<p style='margin-top:20px;'>Choose which kind of recipients (people who subscribe the newsletter or the registered users):</p>
<p><select name="select_recipients" id="select_recipients" >
    <option value="subscr" selected="selected">Subscribers</option>';
    <option value="users">Registered users</option>';
    <option value="none">None</option>';
</select></p>

<p style='margin-top:20px;'>To send to other people insert a list of e-mail addresses separated by <strong>comma</strong> (,):</p>
<textarea id="emails_add" value="" name="emails_add" rows="5" cols="70"><?php echo get_option('ALO_em_list'); ?></textarea>

<p><input type="checkbox" name="ck_save_list" id="ck_save_list" value="checked" checked="checked" />
<label for="ck_save_list">Save the emails' list for next sending (always available on <em>Settings</em>)</label></p>

<p>&nbsp;</p>

<h3>Subject and text of the e-mail</h3>
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


<p style='margin-top:20px;'><strong>Subject</strong>:</p>
<input type="text" size="70" name="input_subject" id="input_subject" value="" maxlength="150" />


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
<tr><td>[POST-CONTENT]</td><td style='font-size:80%'><em>The main content of the post. NOTE: this tag inserts ALL post content as it is: so it includes shotcodes, [tags] and other plugin code.</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>
<tr><td>[USER-NAME]</td><td style='font-size:80%'><em>Name and surname of registered user. (For subscribers: name as inserted)</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>

<!-- Following two lines added GAL -->
<tr><td>[USER-FIRST-NAME]</td><td style='font-size:80%'><em>First name of registered user. (For subscribers: name as inserted).</em></td></tr>
<tr><td colspan='2' style='border-bottom:1px grey dotted;padding-bottom:5px'></td></tr>

<tr><td>[SITE-LINK]</td><td style='font-size:80%'><i>The link to the site.</i>E.g.: <?php echo "<a href='".get_option ('siteurl')."'>".get_option('blogname')."</a>" ?></td></tr>
</table>

<p><input type="checkbox" name="ck_save_template" id="ck_save_template" value="checked" checked="checked" />
<label for="ck_save_template">Save the main body as template for next sending (template always available on <em>Settings</em>)</label></p>


<h3 style='margin-top:30px;'>Send</h3>

<p>Click <strong>once</strong> and <strong>wait</strong> while sending.</p>

<?php // Submit ?>
    <span class="submit">
    <?php wp_nonce_field('alo-easymail_main'); ?>
    <input type="submit" name="submit" id="submit" value="Send" style='font-weight:bold' onclick="this.value='PLEASE WAIT: sending...';"/>
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
