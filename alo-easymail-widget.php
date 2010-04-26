<?php
// some constants
define('ALO_EM_OPT_METAKEY','alo_easymail_optin_setting');

//============= Widget functions ==============================================


/**
 * Get the optin/out option for the user
 */
function ALO_easymail_get_optin ($uid=FALSE) {
    global $user_ID;
    if (!$uid) $uid = $user_ID;
    $optin = get_usermeta( $uid, ALO_EM_OPT_METAKEY );
    if (!$optin) return 'yes'; // default setting
    return $optin;
}

/**
 * Show the widget form for registered/pubblic
 */
function ALO_em_show_widget_form ($optin_msg , $optout_msg) {
	global $user_ID, $user_email, $wpdb;
	
	if (is_user_logged_in()) {
        // For REGISTERED USER
        if (ALO_em_is_subscriber($user_email)){
            $optin_checked = "checked='checked'";            
            $optout_checked = "";            
        }
        else{
            $optin_checked = "";            
            $optout_checked = "checked='checked'";            
        }        
        
        $html = "<div id='alo_easymail_widget_feedback'></div>"; // if any
        $html .= "<form name='alo_easymail_widget_form' id='alo_easymail_widget_form' method='post' action='' >\n"; //action='{$_SERVER['REQUEST_URI']}'
        $html .= "<table>\n";
        $html .= "  <tr>\n";
        $html .= "    <td><input onchange='alo_em_user_form(\"yes\");return false;' type='radio' $optin_checked name='alo_easymail_option' value='yes' /></td>\n";
        $html .= "    <td>$optin_msg</td>\n";
        $html .= "  </tr><tr>\n";
        $html .= "    <td><input onchange='alo_em_user_form(\"no\");return false;' type='radio' $optout_checked name='alo_easymail_option' value='no' /></td>\n";
        $html .= "    <td>$optout_msg</td>\n";
        $html .= "  </tr>\n";
        $html .= "</table>\n";        
        $html .= "</form>\n";
        
    } else {
        // For NOT-REGISTERED, PUBBLIC SUBSCRIBER
        $html = "<div id='alo_easymail_widget_feedback'></div>"; // if any
        $html .= "<form name='alo_easymail_widget_form' id='alo_easymail_widget_form' method='post' action='' onsubmit='alo_em_pubblic_form();return false;'>\n";
        $html .= "<table>\n";
        $html .= "  <tr>\n";
        $html .= "    <td>Name</td>";
        $html .= "    <td><input type='text' name='alo_em_opt_name' value='". stripslashes($_POST['alo_em_opt_name'])."' id='opt_name' size='10' maxlength='50' /></td>\n";
        $html .= "  </tr>\n";
        $html .= "  <tr>\n";
        $html .= "    <td>E-mail</td>\n";
        $html .= "    <td><input type='text' name='alo_em_opt_email' value='". stripslashes($_POST['alo_em_opt_email'])."' id='opt_email' size='10' maxlength='50' /></td>\n";
        $html .= "  </tr>\n";
        $html .= "</table>\n";        
        $html .= "<input type='submit' name='submit' id='submit' value='Subscribe' />\n";
        $html .= "</form>\n";    
    } 
    
    // and output it
    return $html;

}

//============= Widget Class ==============================================
class ALO_Easymail_Widget extends WP_Widget {
    // this constructor cannot be __construct!! causes 500 server error
	function ALO_Easymail_Widget() {
		/* Widget settings. NOTE: Class name must be lower case*/
		$widget_ops = array( 'classname' => 'alo_easymail_widget', 'description' => __('Allow users to opt in/out of email', 'alo_easymail') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'alo-easymail-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'alo-easymail-widget', __('ALO Easymail Widget', 'alo_easymail'), $widget_ops, $control_ops );
	}

	/**
	 * Display the widget on the screen.
	 */
	/* args array
        [name] => Sidebar 1
        [id] => sidebar-1
        [description] => 
        [before_widget] => <li id="example-widget-4" class="widget example">
        [after_widget] => </li>
        [before_title] => <h2 class="widgettitle">
        [after_title] => </h2>
        [widget_id] => example-widget-4
        [widget_name] => Example Widget
    */	 
	function widget( $args, $instance ) {
        global $user_ID, $user_email, $wpdb;
        
		extract( $args );
        
        // add ALO: hide the widget in subscriber page
        if ( is_page(get_option('ALO_em_subsc_page')) ) return;
        
 		// Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		
        // Get the the user's optin setting
        //if (ALO_easymail_get_optin()=='yes'){
        if (ALO_em_is_subscriber($user_email)){
            $optin_checked = "checked='checked'";            
            $optout_checked = "";            
        }
        else{
            $optin_checked = "";            
            $optout_checked = "checked='checked'";            
        }        
		
		// Before widget (defined by themes). 
		echo $before_widget;

		// Display the widget title if one was input (before and after defined by themes). 
		if ( $title )
			echo $before_title . $title . $after_title;

        // get the message optin/out messages
        $optin_msg = $instance['alo_easymail_optin_msg'];
        $optout_msg = $instance['alo_easymail_optout_msg'];
        
        // add ALO: print the form
        echo ALO_em_show_widget_form ($optin_msg , $optout_msg);
        
        // and output it
        echo $html;
		
		// After widget (defined by themes). 
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags for title and name to remove HTML
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['alo_easymail_optin_msg'] = strip_tags( $new_instance['alo_easymail_optin_msg'] );
		$instance['alo_easymail_optout_msg'] = strip_tags( $new_instance['alo_easymail_optout_msg'] );
		
		// add ALO: add option text to use form outside widget
		update_option( "ALO_em_optin_msg", $instance['alo_easymail_optin_msg'] );
		update_option( "ALO_em_optout_msg", $instance['alo_easymail_optout_msg'] );
		
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Newsletter', 'alo_easymail'), 
		            'alo_easymail_optin_msg' => __('Yes, I would like to receive the Newsletter', 'alo_easymail'),
		            'alo_easymail_optout_msg' => __('No, please do not email me', 'alo_easymail'));
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		$html = "";
		$html .= "\r\n".'<!-- Widget Title: Text Input -->';
		$html .= "\r\n".'<p>';
		$html .= "\r\n".'	<label for="'.$this->get_field_id( 'title' ).'">Title</label>';
		$html .= "\r\n".'	<input id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" value="'.$instance['title'].'" style="width:100%;" />';
		$html .= "\r\n".'</p>';

		$html .= "\r\n".'<!-- alo_easymail_optin_msg: Text Input -->';
		$html .= "\r\n".'<p>';
		$html .= "\r\n".'	<label for="'.$this->get_field_id( 'alo_easymail_optin_msg' ).'">Optin Message</label>';
		$html .= "\r\n".'	<input id="'.$this->get_field_id( 'alo_easymail_optin_msg' ).'" name="'.$this->get_field_name( 'alo_easymail_optin_msg' ).'" value="'.$instance['alo_easymail_optin_msg'].'" style="width:100%;" />';
		$html .= "\r\n".'</p>';
		
		$html .= "\r\n".'<!-- alo_easymail_optout_msg: Text Input -->';
		$html .= "\r\n".'<p>';
		$html .= "\r\n".'	<label for="'.$this->get_field_id( 'alo_easymail_optout_msg' ).'">Optout Message</label>';
		$html .= "\r\n".'	<input id="'.$this->get_field_id( 'alo_easymail_optout_msg' ).'" name="'.$this->get_field_name( 'alo_easymail_optout_msg' ).'" value="'.$instance['alo_easymail_optout_msg'].'" style="width:100%;" />';
		$html .= "\r\n".'</p>';
				
		echo $html;

	}
}//=========== End  Widget Class ==========================================?>
