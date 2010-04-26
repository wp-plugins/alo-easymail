<?php
include('../../../wp-blog-header.php');
//require_once('alo-easymail-widget.php'); // added GAL
auth_redirect();

//print_r ($_REQUEST); // DEBUG

if($wp_version >= '2.6.5') check_admin_referer('alo-easymail_main');


if (isset($_REQUEST['id']) && (int)$_REQUEST['id']) {        
    // ID of newsletter (to make the report)
    $id = $_REQUEST['id'];
    
    // If admin he can see
	$can_see_all = ($user_level >= 8)? true: false;
    
    $where_user = ($can_see_all)? "" : " AND user = %d ";
	$newsletter = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_sendings WHERE sent=1 AND ID = %d {$where_user}", $id, $user_ID ) );
	
	if (!$newsletter) {
		die("The requested page doesn't exists.");
	} else {
		?>
		
		<?php  //TODO caricare tabs navigazione
		/*
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core','',array('jquery')); 
		wp_enqueue_script('jquery-ui-tabs','',array('jquery'));

		wp_head();
		

		<script type="text/javascript">
		jQuery(document).ready(function($){
			$("#tabs").tabs();
		});
		</script>
		*/
		?>		
		<style type="text/css">
			#tabs-1 {padding:1px 12px;border-bottom:1px dotted #aaa}
			dl {font-size:80%}
			dd {font-weight:bold;margin-bottm:5px}
	
			#tabs-2 {padding:10px 12px;}
			.tot {font-weight:bold;}
			.success {color:#0c6;}
			.error {color:#f00;}
			table {font-size:75%;width:400px;margin:0 auto;}
			td {padding:4px}
			td.center {text-align:center}
		</style>
		
		<?php //TODO SE PRIMA CHIAM wp_head() ALLORA POI CHIUDERE: //</head> ?>
		
		<div id="tabs">
			<!--<ul>
				<li><a href="#tabs-1">General</a></li>
				<li><a href="#tabs-2">Delivered</a></li>
			</ul>-->

			<!-- Newsletter's general details -->
			<div id="tabs-1">
				<dl>
					<dt>Subject:</dt>
					<dd><?php echo $newsletter->subject ?></dd>
				</dl>
				<?php if ($newsletter->user != $user_ID) {
					echo "<dl><dt>Scheduled by:</dt>";
					echo "<dd>".get_usermeta($newsletter->user, 'nickname') . "</dd></dl>";
				} ?>
				<dl>
					<dt>Added on:</dt>
					<dd><?php echo $newsletter->start_at ?></dd>
				</dl>
				<dl>
					<dt>Completed:</dt>
					<dd><?php echo $newsletter->last_at ?></dd>
				</dl>		
				<dl>
					<dt>Content (plain text):</dt>
					<dd style="font-weight:normal;font-size:90%"><?php echo strip_tags($newsletter->content) ?></dd>
				</dl>	
			</div>
		
			<!-- Newsletter's recipients list -->
			<div id="tabs-2">
				<?php
				// List of recipients
				$recipients = unserialize( $newsletter->recipients );
				$tot_rec = count($recipients);
			
				$ok_rec = 0; // count success
				$ko_rec = 0; // count failed
				foreach ($recipients as $recipient) {
	   				if ( $recipient['result'] >= 1) {
	   					$ok_rec ++;
	   				} else {
	   					$ko_rec ++;
	   				}
	   			}
				?>	
				<dl>
					<dt>Total sent:</dt>
					<dd class="tot"><?php echo $tot_rec ?></dd>
				</dl>
				<dl>
					<dt>Succesful sendings:</dt>
					<dd class="success"><?php echo $ok_rec ?></dd>
				</dl>
				<dl>
					<dt>Failed sendings:</dt>
					<dd class="error"><?php echo $ko_rec ?></dd>
				</dl>
			
				<table >
					<thead>
					<tr>
						<th scope="col"></th>
						<th scope="col">E-mail</th>
						<th scope="col">Name</th>
						<th scope="col">Sent</th>
					</tr>
				</thead>

				<tbody>
				<?php
				$class = "";
				$n = 0;
				foreach ($recipients as $recipient) {
					$class = ('' == $class) ? "style='background-color:#eee;'" : "";
					$n ++;
					echo "<tr $class ><td>".$n."</td><td>".$recipient['email']."</td><td>".$recipient['name']."</td>";
					echo "<td class='center'><img src='".get_option ('siteurl')."/wp-content/plugins/alo-easymail/images/".(($recipient['result'] == 1)? "yes.png":"no.png") ."' /></td></tr>";
				}
				echo "</tbody></table>";
			?>
			</div>
			
		</div> <!-- end #tabs -->
		
	<?php } // end if $newsletter
} // edn if (isset($_REQUEST['id']) && (int)$_REQUEST['id'])
exit;
?>
