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
            if (ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $trim_non_reg_email )) {
                $recipients[$trim_non_reg_email]['email'] = $trim_non_reg_email;
            } else {
                $wrong_add_email .= $trim_non_reg_email." ";
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
    if ($subject == "" || $main_content == "")  $error .= "- Fill 'subject' and 'main body' fields.<br />";
    if ($_REQUEST['select_recipients'] == 'none' && trim($_REQUEST['emails_add']) == "") $error .= "- No recipients specified.<br />";
    if ($error != "" || $wrong_add_email != "") {
        //wp_redirect( get_option ('siteurl')."/".'wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message='.$error);
        echo " <script type='text/javascript'> opener.document.getElementById('submit').value='Send'; </script> ";
        echo "<div id='message' class='updated fade'><h3>Error.</h3>";
        if ($error !="") echo "<p>$error</p>";
        if ($wrong_add_email !="") echo "<p>- Some inserted email addesses are incorrect, please check:<br /><strong>$wrong_add_email</strong>.</p>";
        echo "</div>";
        echo "<p><a href='javascript:window.close()' >close</a> </p>";
        exit;
    }

    // Save content for next sending, if request
    if ($_REQUEST['ck_save_template']) update_option('ALO_em_template', $main_content);
    
    // Save emails'list for next sending, if request
    if ($_REQUEST['ck_save_list']) update_option('ALO_em_list', trim($_REQUEST['emails_add']) );
    
    $r = 0; // count sent
    $s = 0; // count sending success
    $e = 0; // count sending error
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
             
        // TAG: [USER-NAME]
        if ($recipient['name']) {
            $updated_content = str_replace("[USER-NAME]", $recipient['name'], $updated_content);       
        } else {
            $updated_content = str_replace("[USER-NAME]", "", $updated_content);
        }            
        
        //>>>>>>> added GAL
        // TAG: [USER-FIRST-NAME]
        if ($recipient['firstname']) {
            $updated_content = str_replace("[USER-FIRST-NAME]", $recipient['firstname'], $updated_content);       
        } else {
            $updated_content = str_replace("[USER-FIRST-NAME]", "", $updated_content);
        }            
        //<<<<<<<<< end added GAL
        
        // TAG: [SITE-LINK]
        $updated_content = str_replace("[SITE-LINK]", "<a href='".get_option ('siteurl')."'>".get_option('blogname')."</a>", $updated_content);       
        
        // Unsubscribe link, only if subscriber
        if ($recipient['unikey']) {
			$div_email = explode("@", $recipient['email']); // for link
            $updated_content .= "<p><em>You have received this message because you subscribed our newsletter. If you want to unsubscribe please ";
            $updated_content .= "<a href='".get_option ('siteurl') . "/?page_id=". get_option('ALO_em_subsc_page');
            $updated_content .= "&amp;ac=unsubscribe&amp;em1=" .$div_email[0] . "&amp;em2=" .$div_email[1] . "&amp;uk=" .$recipient['unikey']."'>click here</a>.";            
            $updated_content .= "</em></p>";
        }
            
        // ---- Send MAIL ----
        $mail_engine = @wp_mail($recipient['email'], $subject, $updated_content, $headers);  
        
        if($mail_engine) {
            $s ++;   // add to success count
            $recipients[$recipient['email']]['result'] = 1;
        } else {
            $listnosent .= $recipient['email'].",";
            $e ++;  // add to error count
            $recipients[$recipient['email']]['result'] = 0;
        }
        
        // some rest for the processor...
        if ( ($r % 50) == 0) {
			sleep(10); // every n° sent wait a little
		}
		
        // DEBUG
        //echo "<br />".$headers."<br />".$recipient['email']."<br />". $subject."<br />".  $updated_content ."<hr />" ;
    }
    
    // format
    // $listnosent = str_replace("@", ";at;", $listnosent);
   
    // DEBUG
    //print_r ($recipients);
    //print_r ($name_for_tag);
    //print_r ($_REQUEST);
    //echo $listnosent;                    
    /*foreach ($recipients as $rec) {
        echo "<pre>";print_r ($rec);echo "</pre>";
    }*/
    
    // At the end: redirect
    //wp_redirect( get_option ('siteurl')."/".'wp-admin/edit.php?page=alo-easymail/alo-easymail_main.php&message=success&nsent='.$r.'&nsucc='.$s.'&listnosent='.$listnosent);
    
    echo "
    <script type='text/javascript'>
        	opener.document.getElementById('submit').value='Send';
    </script>
    "; // reset Submit text
    
    //wp_enqueue_script( 'listman' );
    //wp_print_scripts();

    // REPORT
    echo "<h2>RESULT</h2>";
    echo "<img src='".get_option ('siteurl')."/wp-content/plugins/alo-easymail/images/email.png' /> <strong>$r sent</strong>";
    echo "<br /><img src='".get_option ('siteurl')."/wp-content/plugins/alo-easymail/images/yes.png' /> $s successful delivered";
    if ($e > 0 && $r!=$s) echo "<br /><img src='".get_option ('siteurl')."/wp-content/plugins/alo-easymail/images/no.png' /> $e not delivered";
    
    // Summary table ?>
    <br />
    <br />
    <table >
        <thead>
	    <tr>
		    <th scope="col"></th>
		    <th scope="col">E-mail</th>
		    <th scope="col">Name</th>
		    <th scope="col">Delivered</th>
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
        echo "<td><img src='".get_option ('siteurl')."/wp-content/plugins/alo-easymail/images/".(($recipient['result'] == 1)? "yes.png":"no.png") ."' /></td></tr>";
    }
    echo "</tbody></table>";
    
    // $listnosent (cut last comma)
	if ($listnosent != "") {
		echo "<br /><br />If you want you can copy the not delivered addresses from box below and paste them in recipients' field of the previous page to try again:<br />";
		echo "<br /><textarea cols='70' rows='3' onClick='this.select();'>".substr($listnosent,0,-1) ."</textarea>";
	}
    
    // go back
    //echo "<p><a href='javascript:history.back()' >&laquo; back</a> ";
    echo "<p><a href='javascript:window.close()' >close</a> </p>";
}
exit;
?>
