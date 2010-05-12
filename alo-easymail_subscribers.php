<?php // No direct access, only through WP
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('You can\'t call this page directly.'); ?>


<?php // action and feedback

// change state activity of subscriber
if($_REQUEST['task'] == 'active' && is_numeric($_REQUEST['subscriber_id'])) {
    if ( ALO_em_edit_subscriber_state_by_id($_REQUEST['subscriber_id'], $_REQUEST['act']) ) {
	    print '<div id="message" class="updated fade"><p>'.__("Activation state updated", "alo-easymail").'.</p></div>';
	} else {
	    print '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail") ." ". __("Not updated", "alo-easymail").'.</p></div>';
	}
}

// delete partecipation
if($_REQUEST['task'] == 'delete' && is_numeric($_REQUEST['subscriber_id'])) {
    if ( ALO_em_delete_subscriber_by_id ($_REQUEST['subscriber_id']) ) {
	    print '<div id="message" class="updated fade"><p>'.__("Subscriber deleted", "alo-easymail").'.</p></div>';
	} else {
	    print '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail") ." ". __("Not deleted", "alo-easymail").'.</p></div>';
	}
}

?>

<div class="wrap">
    <div id="icon-index" class="icon32"><br /></div>
    <h2>Alo EasyMail Newsletter: <?php _e("Subscribers", "alo-easymail") ?></h2>
    <div id="dashboard-widgets-wrap">
    
    
<?php 
/**
 * --- start MAIN --------------------------------------------------------------
 */
?>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

	
<?php
// pagination info
$offset = 0;
$page = 1;
$items_per_page = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : 20;

if(isset($_REQUEST['paged']) and $_REQUEST['paged']) {
	$page = intval($_REQUEST['paged']);
	$offset = ($page - 1) * $items_per_page;
}

// order default
if( !isset($_GET['sortby']) ) {
	$_GET['sortby'] = 'join_date'; //'ID';
}

// string to search
$s = wp_specialchars( trim( $_GET[ 's' ] ) );

?>

<?php //TODO ?
//<p><a href='#new'>Add new subscribers</a></p>
?>

<form action="" method="get" class="search-form">
	<p class="search-box">
	<input type="text" name="s" value="<?php if (!empty( $s )) echo stripslashes( $s ) ; ?>" class="search-input" id="s" />
	<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
	<input  type="hidden" name="paged"  value="1" />
	<input  type="hidden" name="num"    value="<?php echo $items_per_page ?>" />
	<input  type="hidden" name="sortby" value="<?php echo $_GET['sortby'] ?>" />
	<input  type="hidden" name="order"  value="<?php echo ( $_GET['order'] == 'DESC' ) ? 'DESC' : 'ASC'; ?>" />
	
	<input type="submit" value="<?php _e("Search e-mail or name", "alo-easymail") ?>" class="button" />
	
	<?php if ($_GET['s']) echo "&nbsp;&nbsp;<a href='users.php?page=alo-easymail/alo-easymail_subscribers.php&amp;pid=".$post_id."'>".__("Show all", "alo-easymail")."</a>" ?>
	
	</p>
</form>

<?php 
// Prepare link string (with common vars)
$link_base = "users.php?page=alo-easymail/alo-easymail_subscribers.php";
$link_string = $link_base . "&amp;paged=".$page."&amp;num=".$items_per_page. (($_GET['s'])? "&amp;s=".$s : "") ;
?>

<?php 
//SELECT NUM PER PAGE (items per page) 

// prepare url string
$link_string_js = str_replace("&amp;", "&", $link_string);
// use regexpr to set always page 1
if(preg_match('/\s*paged=\s*(\d+)\s*/', $link_string_js, $matches)) {
	$link_string_js = str_replace($matches[1], "1", $link_string_js);
	//print_r($matches[1]);
}
?>

<script type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
    window.location.href = "<?php echo $link_string_js?>&num=" + selObj.options[selObj.selectedIndex].value;
}
//-->
</script>

<?php
$array_num = array(10,20,50,100,200);
echo __("Per page", "alo-easymail").": <select name='select_num' id='select_num' onchange=\"MM_jumpMenu('parent',this,0)\" style='vertical-align:middle'>";
foreach($array_num as $n) {
    $selected_test = ($id_test == $test->ID ? ' selected="selected" ': '');
    echo "<option value='$n' ".($items_per_page == $n ? "selected='selected'": "").">$n</option>";
}
echo "</select>";
?>

<table class="widefat" style='margin-top:10px'>
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;">#</div></th>
		<th scope="col"><div style="text-align: center;"><!-- Avatar --></div></th>
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=email".( ($_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='".__("Order by e-mail", "alo-easymail")."'>".__("E-mail", "alo-easymail")."</a>"; ?>	</th>
		<th scope="col"><?php _e("Name", "alo-easymail") ?></th>
		<th scope="col"><?php echo __("Username", "alo-easymail") . ALO_em_help_tooltip( __("The username of registered users. It is blank for public subscribers.", "alo-easymail") ) ?></th>
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=join_date".( ($_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='".__("Order by join date", "alo-easymail")."'>".__("Join date", "alo-easymail")."</a>"; ?></th>
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=active".( ($_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='".__("Order by activation state", "alo-easymail")."'>".__("Activated", "alo-easymail")."</a>" .ALO_em_help_tooltip(__("For registered users the dafault state is activated. For public subscribers the default state is deactivated: it will be activated by clicking on the activation link in the e-mail.", "alo-easymail")); ?></th>
		<th scope="col"><?php _e("Delete", "alo-easymail") ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php

// BUILD THE QUERY
$query =    "SELECT * FROM {$wpdb->prefix}easymail_subscribers";
            
if( !empty( $s ) ) {
	$search = '%' . trim( $s ) . '%';
	$where_search = " WHERE email LIKE '$search' OR name LIKE '$search' ";
	$query .= $where_search;
}

// order
if( $_GET['sortby'] == 'email' ) {
	$query .= ' ORDER BY email ';
} elseif( $_GET['sortby'] == 'join_date' ) {
	$query .= ' ORDER BY join_date ';
} elseif( $_GET['sortby'] == 'active' ) {
    $query .= ' ORDER BY active ';
} 

$query .= ( $_GET['order'] == 'ASC' ) ? 'ASC' : 'DESC';

$query .= " LIMIT $offset, $items_per_page ";

// The QUERY on subscribers
$all_subscribers = $wpdb->get_results($query);

if (count($all_subscribers)) {
	$class = 'alternate';
	$row_count = 0;
	foreach($all_subscribers as $subscriber) {
		$row_count++;
		
		$class = ('alternate' == $class) ? '' : 'alternate';
		print "<tr id='res-{$subscriber->ID}' class='$class'>\n";
		?>
		
		<th scope="row" style="text-align: center;">
		    <?php echo ( ($page -1) * $items_per_page + $row_count); ?>
        </th>
		<td><?php 
          echo get_avatar($subscriber->email, 30) ;
        ?></td>
		<td>
		    <?php echo $subscriber->email; ?>
		</td>
		<td>
		    <?php echo $subscriber->name; ?>
		</td>
		<td><?php // search for user detail (if user)
		    if ( email_exists($subscriber->email) ) {
		        $user_info = get_userdata( email_exists($subscriber->email) );
                echo "<a href='".get_option ('siteurl')."/wp-admin/profile.php?user_id={$user_info->ID}' title='".__("View user profile", "alo-easymail")."'>{$user_info->user_login}</a>";
		    }
		?>
		</td>
		<td>
		    <?php echo date("d/m/Y", strtotime($subscriber->join_date))." h.".date("H:i", strtotime($subscriber->join_date)) ?></td>
		<td><?php // Check the state (active/no-active)
    		echo "<a href='".$link_string."&amp;task=active&amp;subscriber_id=".$subscriber->ID. "&amp;act=".(($subscriber->active == 1)? "0":"1")."&amp;sortby=".$_GET['sortby']. "&amp;order=".$_GET['order']. "' title='".__("Modify activation state", "alo-easymail")."' ";
		    echo " onclick=\"return confirm('". __("Do you really want to modify the activation state?", "alo-easymail") ."');\">";
		    echo "<img src='".get_option ('siteurl')."/wp-content/plugins/alo-easymail/images/".(($subscriber->active == 1)? "yes.png":"no.png") ."' />";
    		?>
        </td>
		<td><?php // Delete subscriber
    		echo "<a href='".$link_string."&amp;task=delete&amp;subscriber_id=".$subscriber->ID. "&amp;sortby=".$_GET['sortby']."&amp;order=".$_GET['order']. "' title='".__("Delete subscriber", "alo-easymail")."' ";
		    echo " onclick=\"return confirm('".__("Do you really want to DELETE this subscriber?", "alo-easymail")."');\">";
		    echo "<img src='".get_option ('siteurl')."/wp-content/plugins/alo-easymail/images/trash.png' />";
    		?>
		</td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="8"><?php _e("No subscribers", "alo-easymail") ?>.</td>
	</tr>
<?php
}
?>
	</tbody>
</table>


<?php if(count($all_subscribers)) { ?>
<div class="tablenav">
<?php
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}easymail_subscribers" . (!empty( $s )?$where_search : ""));

$total_pages = ceil($total_items / $items_per_page);

$arr_params = array ('paged' => '%#%', 
                    'subscriber_id' => '' /* unset to avoid updated msg in next page */
                    );                     
$page_links = paginate_links( array(
	'base' => add_query_arg( $arr_params/*'paged', '%#%' */),
	'format' => '',
	'total' => $total_pages,
	'show_all' => true,
	'current' => $page
));
/*
$page_links = paginate_links( array(
	'base' => $link_base ."&amp;num=".$items_per_page. (($_GET['s'])? "&amp;s=".$s : "") ,
	'format' => '&amp;paged=%#%',
	'total' => $total_pages,
	'show_all' => true,
	'current' => $page
));*/
if ( $page_links ) echo "<div class='tablenav-pages'>$page_links</div>";
?>
</div>
<?php } ?>

<?php //TODO ? 
//<h4 id='new'>Add new subscribers</h4>
?>


<?php 
// DEBUG ------------------------------------------
//print "<pre>".$query."</pre>";
//print "<br />Totali = ".$total_items."<br />";
//print_r($_GET)    
// end DEBUG --------------------------------------
?>

<?php 
/**
 * --- end MAIN ----------------------------------------------------------------
 */
?>

        </div> <?php // Closes #dashboard-widgets ?>
        
        
        <div class="clear">
        </div>
    </div><!-- dashboard-widgets-wrap -->
</div><!-- wrap -->	
