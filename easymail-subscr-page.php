<?php
 global $wpdb;

$concat_email = $_GET['em1'] . "@" . $_GET['em2']; 

$email  = stripslashes($wpdb->escape($concat_email)); 
$unikey = stripslashes($wpdb->escape($_GET['uk']));
$action = stripslashes($wpdb->escape($_GET['ac']));


// If there is not an activation/unsubscribe request
if (ALO_em_can_access_subscrpage ($email, $unikey) == false ) : // if cannot
	
	$optin_msg = get_option('ALO_em_optin_msg');
	$optout_msg = get_option('ALO_em_optout_msg');
    echo "<div id='alo_easymail_page'>";
	echo ALO_em_show_widget_form($optin_msg , $optout_msg);
	echo "</div>";
	
else: // if can go on
 

// Activate
if ($action == 'activate') {
    if (ALO_em_edit_subscriber_state_by_email($email, "1", $unikey)) {
        echo "<p>Your subscription is successfully activated. You'll receive the next newsletter. Thank you.</p>";
    } else {
        echo "<p>Error during activation. Please check the activation link in the e-mail you received.</p>";
    }
}
    
// Unsubscribe
if ($action == 'unsubscribe') {
    if (ALO_em_delete_subscriber_by_email($email, $unikey)) {
        echo "<p>Your subscription is successfully deleted. Cheers.</p>";;
    } else {
        echo "<p>Error during deleting your unsubscription. Please retry.</p>";
        echo "<p>If it fails again you can contact the administrator: <a href='mailto:".get_option('admin_email')."?Subject=Unsubscribe'>".get_option('admin_email')."</a></p>";
    }
}

endif; //  end CHECK IF CAN ACCESS
?>
