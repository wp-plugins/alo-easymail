<?php
include('../../../wp-load.php');
//auth_redirect();

if ( !current_user_can( "edit_posts" ) ) 	wp_die( __('Cheatin&#8217; uh?') );

//print_r ($_REQUEST); // DEBUG

check_admin_referer('alo-easymail_report');


/*
 * Checks Required vars
 */
if ( isset( $_REQUEST['newsletter'] ) ) {
	$newsletter = (int)$_REQUEST['newsletter'];
	if ( get_post_type( $newsletter ) != "newsletter" ) wp_die( __('The required newsletter does not exist', "alo-easymail") ); 
	if ( !get_post( $newsletter ) ) wp_die( __('The required newsletter does not exist', "alo-easymail") );
	if ( !alo_em_user_can_edit_newsletter( $newsletter ) ) wp_die( __('Cheatin&#8217; uh?') ); 
} else {
	wp_die(__('Cheatin&#8217; uh?') );    
}


if ( $newsletter ) { 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php  _e('Newsletter report', "alo-easymail") ?></title>
<?php

	// TODO 
	
    // ID of newsletter (to make the report)
    $id = $newsletter; // $_REQUEST['newsletter'];
    
    // Lang
    $lang = ( isset($_REQUEST['lang'])) ? $_REQUEST['lang'] : false;

	$newsletter_post = alo_em_get_newsletter( $newsletter );
	
	if ( !$newsletter ) {
		die("The requested page doesn't exists.");
	} else { 
		?>
		
		<style type="text/css">
			#tabs-1 {padding:1px 12px;border-bottom:1px dotted #aaa}
			dl {font-size:80%}
			dd {margin-bottm:5px}
			dt {font-style: italic;}
	
			#tabs-2 {padding:10px 12px;}
			.tot, .success, .error, .done, .views {font-size:300%;}
			.tot {color:#666;font-weight:bold;}
			.done {color:#999;font-weight:bold;}
			.success {color:#0c6;font-weight:bold;}
			.error {color:#f00;font-weight:bold;}
			.views {color:#fa0;font-weight:bold;}
			table {font-size:75%;width:550px;margin:0 auto;}
			td {padding:4px}
			td.center {text-align:center}
			table.summary { width:100%;margin-top:10px;background-color:#eef;padding: 1em 0.5em; }
			#mailbody { font-weight:normal; font-size:90%;height: 50px; overflow: auto; border-left:1px dotted #aaa; padding-left: 1em;}
			#mailbody img { height: 5em; width: auto; display: block; }
			#mailbody p { margin:0 }
			
			.new-win-link { font-size:75%; float:right; }
		</style>
		</head>
		
		<body>
		
		<div id="slider" class="wrap">
			<?php if ( isset($_GET['_wpnonce']) && !isset($_GET['isnewwin']) ) : ?>
				<a href="<?php echo ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?_wpnonce='.$_GET['_wpnonce'].'&newsletter='.$newsletter.'&lang='.$lang.'&isnewwin=1'; ?>" target="_blank" class="new-win-link">
				<?php _e("open in a new window", "alo-easymail") ?></a>
			<?php endif; ?>				
			
			<!-- Newsletter's general details -->
			<div id="tabs-1">
				<dl>
					<dt><?php _e("Subject", "alo-easymail");  ?>:</dt>
					<dd><strong><?php 
					$subject = get_the_title( $newsletter );
					/*
					TODO tag title in subject
					
					if ( $newsletter->tag ) {
						$obj_post = get_post( $newsletter->tag );
						$post_title = stripslashes ( alo_em___ ( $obj_post->post_title ) );
						$subject = str_replace('[POST-TITLE]', $post_title, $subject);
						echo "<strong>". stripslashes ( alo_em_translate_text ( $lang, $subject ) ) . "</strong>";
						echo "<br /><em>". stripslashes ( alo_em_translate_text ( $lang, $newsletter->subject ) ) ."</em>";
					} else {
						echo "<strong>". stripslashes ( alo_em_translate_text ( $lang, $subject ) ) . "</strong>";
					}
					*/
					echo $subject;
					?></strong></dd>
				</dl>
					<dl><dt><?php _e("Scheduled by", "alo-easymail") ?></dt>
					<dd><?php echo get_user_meta( $newsletter_post->post_author, 'nickname', true ) ?></dd></dl>
				<dl>
					<dt><?php _e("Start", "alo-easymail") ?>:</dt>
					<dd><?php echo date_i18n( __( 'j M Y @ G:i' ), strtotime( $newsletter_post->post_date ) ) ?></dd>
				</dl>
				<dl>
					<dt><?php _e("Completed", "alo-easymail") ?>:</dt>
					<dd><?php 
						$end = get_post_meta ( $newsletter, "_easymail_completed", current_time( 'mysql', 0 ) );
						echo ( $end ) ? date_i18n( __( 'j M Y @ G:i' ), strtotime( $end ) ) : __("No", "alo-easymail" );
					 ?></dd>
				</dl>		
				<dl>
					<dt><?php _e("Main body", "alo-easymail") ?> (<?php _e("without formatting", "alo-easymail") ?>):</dt>
					<dd id="mailbody">
						<?php echo strip_tags( alo_em_translate_text ( $lang, $newsletter_post->post_content ), "<img>");
						//echo apply_filters('the_content', $newsletter_post->post_content ) ?>
					</dd>
				</dl>	
			</div>
		
			<!-- Newsletter's recipients list -->
			<div id="tabs-2">
				<?php
				// List of recipients
				$recipients = alo_em_get_newsletter_recipients( $newsletter ); 
				?>	
				
				<table class="summary">
					<thead><tr>
						<th scope="col"><?php _e("Total recipients", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sendings done", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sendings succesful", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sendings failed", "alo-easymail") ?></th>
						<th scope="col"><?php 
							echo __("Unique views", "alo-easymail") . " "; 
							echo alo_em_help_tooltip( 
								__("The plugin tries to count how many recipients open the newsletter", "alo-easymail"). ". "
								. __("The number includes max a view per recipient", "alo-easymail"). ". "
							);
						?></th>						
						<!--<th scope="col"><?php 
							echo __("Clicks", "alo-easymail") . " "; 
							echo alo_em_help_tooltip( 
								__("The number includes all clicks by all recipients", "alo-easymail"). ". "
							);						
						?></th>-->
					</tr></thead>
				<tbody><tr>
					<td class="tot center" style="width:20%"><?php echo alo_em_count_newsletter_recipients ( $newsletter ) ?>
					<td class="done center" style="width:20%"><?php echo alo_em_count_newsletter_recipients_already_sent ( $newsletter ) ?>
					<td class="success center" style="width:15%"><?php echo alo_em_count_newsletter_recipients_already_sent_with_success( $newsletter )  ?>
					<td class="error center" style="width:15%"><?php echo alo_em_count_newsletter_recipients_already_sent_with_error( $newsletter )  ?>	
					<td class="views center" style="width:15%"><?php echo count( alo_em_all_newsletter_trackings ( $newsletter, '' ) );  ?>					
					<!--<td class="success center" style="width:15%"><?php //TODO  ?>-->
					</tr></tbody>
				</table>
											
				<table style="margin-top:25px;width:100%">
					<thead>
					<tr>
						<th scope="col"></th>
						<th scope="col"><?php _e("E-mail", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Name", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Language", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sent", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Viewed", "alo-easymail") ?></th>						
						<!--<th scope="col"><?php _e("Clicks", "alo-easymail") ?></th>--><?php // TODO! ?>
					</tr>
				</thead>

				<tbody>
				<?php
				$class = "";
				$n = 0;
				foreach ($recipients as $recipient) {
					$class = ('' == $class) ? "style='background-color:#eee;'" : "";
					$n ++;
					echo "<tr $class ><td>".$n."</td><td>".$recipient->email."</td><td>".$recipient->name."</td>";
					echo "<td class='center'>";
					if ( isset( $recipient->lang ) ) echo alo_em_get_lang_flag( $recipient->lang, 'name' ) ;
					echo "</td>";
					echo "<td class='center'><img src='".ALO_EM_PLUGIN_URL."/images/".( ( $recipient->result == "1" ) ? "yes.png":"no.png" ) ."' alt='". ( ( $recipient->result == "1" ) ? __("Yes", "alo-easymail" ) : __("No", "alo-easymail" ) ) ."' /></td>";
					echo "<td class='center'>";
					echo "<img src='".ALO_EM_PLUGIN_URL."/images/".( ( $recipient->result == "1" && alo_em_recipient_is_tracked ( $recipient->ID, '' ) )? "yes.png":"no.png" ) ."' />";
					if ( count( alo_em_get_recipient_trackings( $recipient->ID, '' ) ) > 1 ) echo " ". count( alo_em_get_recipient_trackings( $recipient->ID, '' ) );
					echo "</td>";
					// echo "<td></td>"; // TODO! clicks
					echo "</tr>";
					//echo "<pre>"; print_r($recipient);echo "</pre>";
				}
				?>
			</tbody></table>
			</div>
			
		</div> <!-- end slider -->
		
		</body>
		</html>
	<?php } // end if $newsletter
} // edn if (isset($_REQUEST['id']) && (int)$_REQUEST['id'])
exit;
?>
