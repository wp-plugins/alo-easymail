<?php
include('../../../wp-blog-header.php');
//require_once('alo-easymail-widget.php'); // added GAL
auth_redirect();

//print_r ($_REQUEST); // DEBUG

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
    
    // If request add all SUBSCRIBERS
    if ($_REQUEST['select_recipients'] == 'subscr') {
        $subs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE active='1'" );     
        foreach ($subs as $sub) {
            $recipients[$sub->email]['email'] = $sub->email;
            $recipients[$sub->email]['name'] = $sub->name;
            $recipients[$sub->email]['firstname'] = $sub->name;
            $recipients[$sub->email]['unikey'] = $sub->unikey; 
        }
    
    // If request add all REGISTERED users
    } else if ($_REQUEST['select_recipients'] == 'users') {
        $reg_users = $wpdb->get_results( "SELECT ID AS UID, user_email FROM $wpdb->users" );     
        foreach ($reg_users as $reg_user) {
            $recipients[$reg_user->user_email]['email'] = $reg_user->user_email;
            $recipients[$reg_user->user_email]['name'] = ucfirst(get_usermeta($reg_user->UID, 'first_name'))." " .ucfirst(get_usermeta($reg_user->UID,'last_name'));
            $recipients[$reg_user->user_email]['firstname'] = ucfirst(get_usermeta($reg_user->UID, 'first_name')); 
        }
    }
    
    // If any add NO-REGISTERED E-MAILS
    if ($_REQUEST['emails_add']) {
        $wrong_add_email = "";  // show them in error div
        $non_reg_emails = explode(",", $_REQUEST['emails_add']);
        foreach ($non_reg_emails as $non_reg_email) {
            $trim_non_reg_email = trim($non_reg_email);
            $recipients[$trim_non_reg_email]['email'] = $trim_non_reg_email; // no check, add
            /*
            //if (preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/' , $trim_non_reg_email )) {
            if (is_email($trim_non_reg_email)) {
                $recipients[$trim_non_reg_email]['email'] = $trim_non_reg_email;
            } else {
                $wrong_add_email .= $trim_non_reg_email."; ";
            }
            */
        }
    }
    
    // Subject
    $subject = stripslashes($wpdb->escape($_REQUEST['input_subject']));
    
    // Main content
    $main_content = stripslashes($_REQUEST['content']);
    //$main_content = str_replace("\n", "<br />", $main_content);
    
      
    // --------------------------------------
    // check input error: if any stop here 
    // --------------------------------------

    if (count($recipients) < 1 ) {
        wp_redirect( get_option ('home')."/".'wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message=norecipients');
        exit;    
    }
    //if ($subject == "" || $main_content == "")  $error .= "Fill subject and main body fields";
    if ($subject == "" || $main_content == "") {
        wp_redirect( get_option ('home')."/".'wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message=error');
        exit;    
    }
    if ($error != "" || $wrong_add_email != "") {
        wp_redirect( get_option ('home')."/".'wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message=error');
        exit;
    }
    
    
    // ----------------------------------------------------
    //           PREPARE THE MAIL & THE BATCH
    // ----------------------------------------------------
    
    // From
    $mail_sender = (get_option('ALO_em_sender_email')) ? get_option('ALO_em_sender_email') : "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
    
    // Headers
    $headers =  "MIME-Version: 1.0\n";
    $headers .= "From: ".get_option('blogname')." <".$mail_sender.">\n";
    //$headers .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
    //$headers .= "Content-Type: text/html; charset=UTF-8\n";
    $headers .= "Content-Type: text/html; charset=" . get_option('blog_charset') . "\n";
    $headers .= "Content-Transfer-Encoding: 7bit\n\n";   

    // Save content for next sending, if request
    if ($_REQUEST['ck_save_template']) update_usermeta( $user_ID, 'ALO_em_template', $main_content); 
    
    // Save emails'list for next sending, if request
    if ($_REQUEST['ck_save_list']) update_usermeta( $user_ID, 'ALO_em_list', trim($_REQUEST['emails_add']) ); 
    
    
    // DEBUG
    /*
    echo "HEADERS: ".$headers."<br />";
    echo "SUBJECT: ".$subject."<br />";
    echo "CONTENT: ".$main_content."<br />";
    foreach ($recipients as $rec) {
        echo "<pre>";print_r ($rec);echo "</pre>";
    }
    */
    
    // need a numeric index array
    $num_rec = array();
    $n =0;
    foreach($recipients as $rec) {
    	$num_rec[$n]= $rec;
    	$n ++;
    }
    
    // adjust post tags
    $updated_content = $main_content;
   
    // TAG: [POST-TITLE]
    if ($pID) {
        $updated_content = str_replace("[POST-TITLE]", "<a href='".get_permalink($obj_post->ID). "'>".$obj_post->post_title."</a>", $updated_content);      
    } else {
        $updated_content = str_replace("[POST-TITLE]", "", $updated_content);
    }

    // TAG: [POST-CONTENT]
    if ($pID) {
        $updated_content = str_replace("[POST-CONTENT]", $obj_post->post_content, $updated_content);      
    } else {
        $updated_content = str_replace("[POST-CONTENT]", "", $updated_content);
    }
    
    // TAG: [POST-EXCERPT] - if any
    if ($pID && !empty($obj_post->post_excerpt)) {
        $updated_content = str_replace("[POST-EXCERPT]", $obj_post->post_excerpt, $updated_content);       
    } else {
        $updated_content = str_replace("[POST-EXCERPT]", "", $updated_content);
    }
    
    // TAG: [SITE-LINK]
    $updated_content = str_replace("[SITE-LINK]", "<a href='".get_option ('siteurl')."'>".get_option('blogname')."</a>", $updated_content);       
       
    //echo "<pre>";print_r($num_rec);echo "</pre>";
    
    // add the newsletter to db
    if ( ALO_em_add_new_batch ($headers, $user_ID, $subject, $updated_content, serialize($num_rec) ) == true) {
	    wp_redirect( get_option ('home').'/wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message=success');
	} else {
		wp_redirect( get_option ('home').'/wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message=nosending');
	}	
    exit;
}
exit;
?>
