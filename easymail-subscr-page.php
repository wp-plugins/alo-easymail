<?php
 global $wpdb;

$concat_email = $_GET['em1'] . "@" . $_GET['em2']; 

$email  = stripslashes($wpdb->escape($concat_email)); 
$unikey = stripslashes($wpdb->escape($_GET['uk']));
$action = stripslashes($wpdb->escape($_GET['ac']));


// If there is not an activation/unsubscribe request
if (ALO_em_can_access_subscrpage ($email, $unikey) == false ) : // if cannot
	// if there is action show error msg
	if(isset($_GET['ac'])) echo "<p>".__("Error during operation.", "alo-easymail") ."</p>";
	
    echo "<div id='alo_easymail_page'>";
	echo ALO_em_show_widget_form();
	echo "</div>";
	
else: // if can go on
 

// Activate
if ($action == 'activate') {
    if (ALO_em_edit_subscriber_state_by_email($email, "1", $unikey)) {
        echo "<p>".__("Your subscription was successfully activated. You will receive the next newsletter. Thank you.", "alo-easymail")."</p>";
    } else {
        echo "<p>".__("Error during activation. Please check the activation link.", "alo-easymail")."</p>";
    }
}
    
// Unsubscribe
if ($action == 'unsubscribe') {
    if (ALO_em_delete_subscriber_by_email($email, $unikey)) {
        echo "<p>".__("Your subscription was successfully deleted. Bye bye.", "alo-easymail")."</p>";;
    } else {
        echo "<p>".__("Error during unsubscription.", "alo-easymail")." ". __("Try again.", "alo-easymail"). "</p>";
        echo "<p>".__("If it fails again you can contact the administrator", "alo-easymail").": <a href='mailto:".get_option('admin_email')."?Subject=Unsubscribe'>".get_option('admin_email')."</a></p>";
    }
}

endif; //  end CHECK IF CAN ACCESS
?>
