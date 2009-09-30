<?php
include('../../../wp-blog-header.php');
auth_redirect();
if($wp_version >= '2.6.5') check_admin_referer('alo-easymail_main');

if(isset($_REQUEST['submit'])) {
        
    // prepare array with recipients' addresses
    $recipients = array();

    // prepare for error
    $error = "";
    
    // Retrieve post info for TAG
    $pID = $_REQUEST['select_post'];
    if ((int)$pID) {
        $obj_post = get_post($pID);
    }
    
    // If request add all REGISTERED users
    if ($_REQUEST['ck_all_users']) {
        $reg_users = array(); // prepare array
        $name_for_tag = array(); // for TAG
        $reg_users = $wpdb->get_results( "SELECT ID AS UID, user_email FROM $wpdb->users" );     
        foreach ($reg_users as $reg_user) {
            $name_for_tag[$reg_user->user_email] = ucfirst(get_usermeta($reg_user->UID, 'first_name'))." " .ucfirst(get_usermeta($reg_user->UID,'last_name'));
            $recipients[] = $reg_user->user_email;
        }
    }
    
    // If any add NO-REGISTERED E-MAILS
    if ($_REQUEST['emails_add']) {
        $non_reg_emails = explode(",", $_REQUEST['emails_add']);
        foreach ($non_reg_emails as $non_reg_email) {
            if (ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($non_reg_email))) {
                $recipients[] = trim($non_reg_email);
            } else {
                $error = "incorrect";
            }
        }
    }
     
    // ------------------
    // PREPARE THE E-MAIL
    // ------------------
    
    // From
    $mail_sender = "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
    
    // Headers
    $headers =  "MIME-Version: 1.0\n";
    $headers .= "From: ".get_option('blogname')." <".$mail_sender.">\n";
    //$headers .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\n";
    $headers .= "Content-Transfer-Encoding: 7bit\n\n";
    
    // Subject
    $subject = stripslashes($wpdb->escape($_REQUEST['input_subject']));
    
    // Main content
    $main_content = stripslashes($_REQUEST['content']);
    $main_content = str_replace("\n", "<br />", $main_content);
    
    // check input error: if any stop here  
    if ($subject == "" || $main_content == "")  $error = "incorrect";
    if ($_REQUEST['ck_all_users'] == '0' && trim($_REQUEST['emails_add']) == "") $error = "incorrect";
    if ($error != "") {
        wp_redirect( get_option ('siteurl')."/".'wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message='.$error);
        exit;
    }

    // Save content for next sending, if request
    if ($_REQUEST['ck_save_template']) update_option('ALO_em_template', $main_content);
    
    $r = 0; // count sent
    $s = 0; // count success sent
    $listnosent = ""; // list no sent mails
        
    // SEND EMAIL TO EACH RECIPIENT
    foreach ($recipients as $recipient) {
        
        $r ++; // add this send to count
        
        // For each recipient delete TAGs update
        $updated_content = $main_content;
       
        // TAG: [POST-TITLE]
        if ($pID) {
            $updated_content = str_replace("[POST-TITLE]", $obj_post->post_title, $updated_content);       
        } else {
            $updated_content = str_replace("[POST-TITLE]", "", $updated_content);
        }

        // TAG: [POST-EXCERPT] - if any
        if ($pID && !empty($obj_post->post_excerpt)) {
            $updated_content = str_replace("[POST-EXCERPT]", "<br />".$obj_post->post_excerpt."<br />", $updated_content);       
        } else {
            $updated_content = str_replace("[POST-EXCERPT]", "", $updated_content);
        }
             
        // TAG: [USER-NAME]
        if ($name_for_tag[$recipient]) {
            $updated_content = str_replace("[USER-NAME]", $name_for_tag[$recipient], $updated_content);       
        } else {
            $updated_content = str_replace("[USER-NAME]", "", $updated_content);
        }            
        
        // TAG: [LINK-SITO]
        $updated_content = str_replace("[SITE-LINK]", "<a href='".get_option ('siteurl')."'>".get_option('blogname')."</a>", $updated_content);       
            
        // ---- Send MAIL ----
        $mail_engine = @mail($recipient, $subject, $updated_content, $headers); 
        
        // DEBUG
        //echo "<br />".$headers."<br />".$recipient."<br />". $subject."<br />".  $updated_content ."<hr />" ;

        if($mail_engine) {
            $s ++;   // add to success count
        } else {
            $listnosent .= $recipient.';end;';
        }
        
        // some rest for the processor...
        if ( ($r % 50) == 0) {
			sleep(10); // every n° sent wait a little
		}
    }
    
    // format
    $listnosent = str_replace("@", ";at;", $listnosent);
   
    // DEBUG
    //print_r ($recipients);
    //print_r ($name_for_tag);
    //print_r ($_REQUEST);
    //echo $listnosent;                    
    
    // At the end: redirect
    wp_redirect( get_option ('siteurl')."/".'wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message=success&nsent='.$r.'&nsucc='.$s.'&listnosent='.$listnosent);
}
exit;
?>
