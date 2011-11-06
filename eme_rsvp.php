<?php
$form_add_message = "";
$form_error_message = "";
$form_delete_message = "";

function eme_add_booking_form($event_id) {
   global $form_add_message, $form_error_message, $current_user;
   global $booking_id_done;

   $bookerName="";
   $bookerEmail="";
   $bookerComment="";
   $bookerPhone="";
   $bookedSeats=0;

   if (is_user_logged_in()) {
      get_currentuserinfo();
      $bookerName=$current_user->display_name;
      $bookerEmail=$current_user->user_email;
   }

   // check for previously filled in data
   // this in case people entered a wrong captcha
   if (isset($_POST['bookerName'])) $bookerName = eme_sanitize_html(eme_sanitize_request($_POST['bookerName']));
   if (isset($_POST['bookerEmail'])) $bookerEmail = eme_sanitize_html(eme_sanitize_request($_POST['bookerEmail']));
   if (isset($_POST['bookerPhone'])) $bookerPhone = eme_sanitize_html(eme_sanitize_request($_POST['bookerPhone']));
   if (isset($_POST['bookerComment'])) $bookerComment = eme_sanitize_html(eme_sanitize_request($_POST['bookerComment']));
   if (isset($_POST['bookedSeats'])) $bookedSeats = eme_sanitize_html(eme_sanitize_request($_POST['bookedSeats']));

   $event = eme_get_event($event_id);
   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only) {
      // we require a user to be WP registered to be able to book
      if (!is_user_logged_in()) {
         return;
      }
      $readonly="disabled='disabled'";
      $bookerPhone_required="";
   } else {
      $readonly="";
      $bookerPhone_required="*";
   }
   $destination = "?".$_SERVER['QUERY_STRING']."#eme-rsvp-message";

   $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
   if (time()+$event['rsvp_number_days']*60*60*24 > $event_start_datetime ) {
      $ret_string = "";
      if(!empty($form_add_message))
         $ret_string .= "<div class='eme-rsvp-message'>$form_add_message</div>";
      if(!empty($form_error_message))
         $ret_string .= "<div class='eme-rsvp-message'>$form_error_message</div>";
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer allowed on this date.', 'eme')."</div>";
   }

   # you did a successfull registration, so now we decide wether to show the form again, or the paypal form
   if(!empty($form_add_message) && empty($form_error_message) && $event['use_paypal']) {
      $ret_string = "<div class='eme-rsvp-message'>$form_add_message</div>";
      $ret_string .= eme_paypal_form($event,$booking_id_done);
      return $ret_string;
   }

   // you can book the available number of seats, with a max of 10 per time
   $min = intval(get_option('eme_rsvp_addbooking_min_spaces'));
   $max_allowed = intval(get_option('eme_rsvp_addbooking_max_spaces'));
   $max = eme_get_available_seats($event_id);
   if ($max > $max_allowed && $max_allowed>0) {
      $max = $max_allowed;
   }
   // no seats anymore? No booking form then ...
   if ($max == 0 && $max_allowed>0) {
      $ret_string = "<div id='eme-rsvp-message'>";
      if(!empty($form_add_message))
         $ret_string .= "<div class='eme-rsvp-message'>$form_add_message</div>";
      if(!empty($form_error_message))
         $ret_string .= "<div class='eme-rsvp-message'>$form_error_message</div>";
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer possible: no seats available anymore', 'eme')."</div></div>";
   }

   $form_html = "";
   if(!empty($form_add_message))
      $form_html .= "<div id='eme-rsvp-message' class='eme-rsvp-message'>$form_add_message</div>";
   if(!empty($form_error_message))
      $form_html .= "<div class='eme-rsvp-message'>$form_error_message</div>";
   $booked_places_options = array();
   for ( $i = $min; $i <= $max; $i++) 
      $booked_places_options[$i]=$i;
   
      $form_html  .= "<form id='eme-rsvp-form' name='booking-form' method='post' action='$destination'>
         <table class='eme-rsvp-form'>
            <tr><th scope='row'>".__('Name', 'eme')."*:</th><td><input type='text' name='bookerName' value='$bookerName' $readonly /></td></tr>
            <tr><th scope='row'>".__('E-Mail', 'eme')."*:</th><td><input type='text' name='bookerEmail' value='$bookerEmail' $readonly /></td></tr>
            <tr><th scope='row'>".__('Phone number', 'eme')."$bookerPhone_required:</th><td><input type='text' name='bookerPhone' value='$bookerPhone' /></td></tr>
            <tr><th scope='row'>".__('Seats', 'eme')."*:</th><td>";
            $form_html .= eme_ui_select($bookedSeats,"bookedSeats",$booked_places_options);
      $form_html .= "</td></tr>
            <tr><th scope='row'>".__('Comment', 'eme').":</th><td><textarea name='bookerComment'>$bookerComment</textarea></td></tr>";
      if (get_option('eme_captcha_for_booking')) {
         $form_html .= "
            <tr><th scope='row'>".__('Please fill in the code displayed here', 'eme').":</th><td><img src='".EME_PLUGIN_URL."captcha.php'><br>
                  <input type='text' name='captcha_check' /></td></tr>
            ";
      }
      // also add a honeypot field: if it gets completed with data, 
      // it's a bot, since a humand can't see this (using CSS to render it invisible)
      $form_html .= "
      </table>
      <span id='honeypot_check'>Keep this field blank: <input type='text' name='honeypot_check' value='' /></span>
      <p>".__('(* marks a required field)', 'eme')."</p>
      <input type='hidden' name='eme_eventAction' value='add_booking'/>
      <input type='hidden' name='event_id' value='$event_id'/>
      <input type='submit' value='".get_option('eme_rsvp_addbooking_submit_string')."'/>
   </form>";
   // $form_html .= "dati inviati: ";
   //    $form_html .= eme_sanitize_request($_POST['bookerName']);
   //print_r($_SERVER);
 
   //$form_html .= eme_delete_booking_form();
    
   if (has_filter('eme_add_booking_form_filter')) $form_html=apply_filters('eme_add_booking_form_filter',$form_html);
   return $form_html;
   
}

function eme_delete_booking_form($event_id) {
   global $form_delete_message, $current_user;
   
   if (is_user_logged_in()) {
      get_currentuserinfo();
      $bookerName=$current_user->display_name;
      $bookerEmail=$current_user->user_email;
   } else {
      $bookerName="";
      $bookerEmail="";
   }
   $form_html = "";
   $event = eme_get_event($event_id);
   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only) {
      // we require a user to be WP registered to be able to book
      if (!is_user_logged_in()) {
         return;
      }
      $readonly="disabled='disabled'";
   } else {
      $readonly="";
   }
   $destination = "?".$_SERVER['QUERY_STRING']."#eme-rsvp-message";
   
   $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
   if (time()+$event['rsvp_number_days']*60*60*24 > $event_start_datetime ) {
      $ret_string = "<div id='eme-rsvp-message'>";
      if(!empty($form_delete_message))
         $ret_string .= "<div class='eme-rsvp-message'>$form_delete_message</div>";
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer allowed on this date.', 'eme')."</div></div>";
   }

   if(!empty($form_delete_message))
      $form_html .= "<div id='eme-rsvp-message' class='eme-rsvp-message'>$form_delete_message</div>";

   $form_html  .= "<form name='booking-delete-form' method='post' action='$destination'>
      <table class='eme-rsvp-form'>
         <tr><th scope='row'>".__('Name', 'eme').":</th><td><input type='text' name='bookerName' value='$bookerName' $readonly /></td></tr>
         <tr><th scope='row'>".__('E-Mail', 'eme').":</th><td><input type='text' name='bookerEmail' value='$bookerEmail' $readonly /></td></tr>
      </table>
      <input type='hidden' name='eme_eventAction' value='delete_booking'/>
      <input type='hidden' name='event_id' value='$event_id'/>
      <input type='submit' value='".get_option('eme_rsvp_delbooking_submit_string')."'/>
   </form>";
   // $form_html .= "dati inviati: ";
   //    $form_html .= $_POST['bookerName'];

   if (has_filter('eme_delete_booking_form_filter')) $form_html=apply_filters('eme_delete_booking_form_filter',$form_html);
   return $form_html;
}

function eme_catch_rsvp() {
   global $current_user;
   global $form_add_message;
   global $form_error_message;
   global $form_delete_message; 
   global $booking_id_done;
   $result = "";

   if (isset($_GET['eme_eventAction']) && $_GET['eme_eventAction']=="ipn") {
      return eme_paypal_ipn();
   }
   // make sure we don't get too far without proper info
   if (!(isset($_POST['eme_eventAction']) && isset($_POST['event_id']))) {
      return;
   }

   if (get_option('eme_captcha_for_booking')) {
      // the captcha needs a session
      if (!session_id())
         session_start();
   }

   $event_id = intval($_POST['event_id']);
   $event = eme_get_event($event_id);
   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only && !is_user_logged_in()) {
      return;
   }

   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'add_booking') { 
      $booking_res = eme_book_seats($event);
      $result=$booking_res[0];
      $booking_id_done=$booking_res[1];
      // no booking? then fill in global error var
      if (!$booking_id_done)
         $form_error_message = $result;
      else
         $form_add_message = $result;
   } 

   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'delete_booking') { 
      $result = eme_cancel_seats($event);
      $form_delete_message = $result; 
   } 
   return $result;
   
}
add_action('init','eme_catch_rsvp');
 
function eme_cancel_seats($event) {
   global $current_user;
   $event_id = $event['event_id'];
   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only) {
      // we require a user to be WP registered to be able to book
      get_currentuserinfo();
      $booker_wp_id=$current_user->ID;
      // we also need name and email for sending the mail
      $bookerName = $current_user->display_name;
      $bookerEmail = $current_user->user_email;
      $booker = eme_get_person_by_wp_id($booker_wp_id); 
   } else {
      $bookerName = eme_strip_tags($_POST['bookerName']);
      $bookerEmail = eme_strip_tags($_POST['bookerEmail']);
      $booker = eme_get_person_by_name_and_email($bookerName, $bookerEmail); 
   }
   if ($booker) {
      $person_id = $booker['person_id'];
      $booking_ids=eme_get_booking_ids_by_person_event_id($person_id,$event_id);
      if (!empty($booking_ids)) {
         foreach ($booking_ids as $booking_id) {
            eme_email_rsvp_booking($booking_id,"cancelRegistration");
            eme_delete_booking($booking_id);
         }
         $result = __('Booking deleted', 'eme');
      } else {
         $result = __('There are no bookings associated to this name and e-mail', 'eme');
      }
   } else {
      $result = __('There are no bookings associated to this name and e-mail', 'eme');
   }
   return $result;
}

// the eme_book_seats can also be called from the admin backend, that's why for certain things, we check using is_admin where we are
function eme_book_seats($event) {
   global $current_user;
   $booking_id = 0;

   if (isset($_POST['bookerPhone']))
      $bookedSeats = intval($_POST['bookedSeats']);
   else
      $bookedSeats = 0;

   if (isset($_POST['bookerPhone']))
      $bookerPhone = eme_strip_tags($_POST['bookerPhone']); 
   else
      $bookerPhone = "";
   if (isset($_POST['bookerComment']))
      $bookerComment = eme_strip_tags($_POST['bookerComment']);
   else
      $bookerComment = "";

   if (isset($_POST['honeypot_check']))
      $honeypot_check = stripslashes($_POST['honeypot_check']);
   else
      $honeypot_check = "";

   $event_id = $event['event_id'];
   $registration_wp_users_only=$event['registration_wp_users_only'];
   // if we're booking via the admin backend, we don't care about registration_wp_users_only
   if (!is_admin() && $registration_wp_users_only) {
      // we require a user to be WP registered to be able to book
      get_currentuserinfo();
      $booker_wp_id=$current_user->ID;
      // we also need name and email for sending the mail
      $bookerName = $current_user->display_name;
      $bookerEmail = $current_user->user_email;
      $booker = eme_get_person_by_wp_id($booker_wp_id); 
   } else {
      $booker_wp_id=0;
      $bookerName = eme_strip_tags($_POST['bookerName']);
      $bookerEmail = eme_strip_tags($_POST['bookerEmail']);
      $booker = eme_get_person_by_name_and_email($bookerName, $bookerEmail); 
   }
   
   $msg="";
   if (!is_admin() && get_option('eme_captcha_for_booking')) {
      $msg = response_check_captcha("captcha_check",1);
   }
   $min_allowed = intval(get_option('eme_rsvp_addbooking_min_spaces'));
   $max_allowed = intval(get_option('eme_rsvp_addbooking_max_spaces'));
   if(!empty($msg)) {
      $result = __('You entered an incorrect code','eme');
   } elseif ($honeypot_check != "") {
      // a bot fills this in, but a human never will, since it's
      // a hidden field
      $result = __('You are a bad boy','eme');
   } elseif (!$bookerName || !$bookerEmail) {
      // if any of name, email or bookedseats are empty: return an error
      $result = __('Please fill in all the required fields','eme');
   } elseif (!filter_var($bookerEmail,FILTER_VALIDATE_EMAIL)) {
      $result = __('Please enter a valid mail address','eme');
   } elseif ($bookedSeats < $min_allowed) {
      $result = __('Please fill in a correct number of spaces to reserve','eme');
   } elseif ($max_allowed>0 && $bookedSeats>$max_allowed) {
      $result = __('Please fill in a correct number of spaces to reserve','eme');
   } elseif (!is_admin() && !$registration_wp_users_only && !$bookerPhone) {
      // no member of wordpress: we need a phonenumber then
      $result = __('Please fill in all the required fields','eme');
   } else {
      if (eme_are_seats_available_for($event_id, $bookedSeats)) {
         if (!$booker) {
            $booker = eme_add_person($bookerName, $bookerEmail, $bookerPhone, $booker_wp_id, $registration_wp_users_only);
         }

         // if the user enters a new phone numbe, update it
         if ($booker['person_phone'] != $bookerPhone) {
            eme_update_phone($booker,$bookerPhone);
         }

         $booking_id=eme_record_booking($event_id, $booker['person_id'], $bookedSeats,$bookerComment);
      
         $result = __('Your booking has been recorded','eme');
         eme_email_rsvp_booking($booking_id);

         // everything ok, so we unset the variables entered, so when the form is shown again, all is defaulted again
         unset($_POST['bookerName']);
         unset($_POST['bookerEmail']);
         unset($_POST['bookedSeats']);
         unset($_POST['bookerComment']);
         unset($_POST['bookerPhone']);
      } else {
         $result = __('Booking cannot be made: not enough seats available!', 'eme');
         // here we only unset the number of seats entered, so the user doesn't have to fill in the rest again
         unset($_POST['bookedSeats']);
      }
   }

   $res = array(0=>$result,1=>$booking_id);
   return $res;
}

function eme_get_booking($booking_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT * FROM $bookings_table WHERE booking_id = '$booking_id';" ;
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_bookings_by_person_id($person_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT * FROM $bookings_table WHERE person_id = '$person_id';" ;
   $result = $wpdb->get_results($sql, ARRAY_A);
   return $result;
}

function eme_get_booking_by_person_event_id($person_id,$event_id) {
   return eme_get_booking_ids_by_person_event_id($person_id,$event_id);
}
function eme_get_booking_ids_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "SELECT booking_id FROM $bookings_table WHERE person_id = '$person_id' AND event_id = '$event_id'";
   $result = $wpdb->get_col($sql);
   return $result;
}

function eme_get_booked_seats_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE person_id = '$person_id' AND event_id = '$event_id'";
   return $wpdb->get_var($sql);
}

function eme_get_event_ids_by_booker_id($person_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT DISTINCT event_id FROM $bookings_table WHERE person_id = '$person_id';" ;
   $result = $wpdb->get_col($sql);
   return $result;
}

function eme_record_booking($event_id, $person_id, $seats, $comment = "") {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $event_id = intval($event_id);
   $person_id = intval($person_id);
   $seats = intval($seats);
   $comment = eme_sanitize_request($comment);
   $booking['event_id']=$event_id;
   $booking['person_id']=$person_id;
   $booking['booking_seats']=$seats;
   $booking['booking_comment']=$comment;
   $booking['creation_date']=current_time('mysql', false);
   $booking['modif_date']=current_time('mysql', false);
   $booking['creation_date_gmt']=current_time('mysql', true);
   $booking['modif_date_gmt']=current_time('mysql', true);
   $event = eme_get_event($event_id);
   // only if we're not adding a booking in the admin backend, check for approval needed
   if (!is_admin() && $event['registration_requires_approval']) {
      $booking['booking_approved']=0;
   } else {
      $booking['booking_approved']=1;
   }

   // checking whether the booker has already booked places
// $sql = "SELECT * FROM $bookings_table WHERE event_id = '$event_id' and person_id = '$person_id'; ";
// //echo $sql;
// $previously_booked = $wpdb->get_row($sql);
// if ($previously_booked) {
//    $total_booked_seats = $previously_booked->booking_seats + $seats;
//    $where = array();
//    $where['booking_id'] =$previously_booked->booking_id;
//    $fields['booking_seats'] = $total_booked_seats;
//    $wpdb->update($bookings_table, $fields, $where);
// } else {
      //$sql = "INSERT INTO $bookings_table (event_id, person_id, booking_seats,booking_comment) VALUES ($event_id, $person_id, $seats,'$comment')";
      //$wpdb->query($sql);
      if ($wpdb->insert($bookings_table,$booking)) {
         $booking['booking_id'] = $wpdb->insert_id;
         $transfer_nbr_be97_main=sprintf("%010d",$booking['booking_id']);
         // the control number is the %97 result, or 97 in case %97=0
         $transfer_nbr_be97_check=$transfer_nbr_be97_main % 97;
	if ($transfer_nbr_be97_check==0)
		$transfer_nbr_be97_check = 97 ;
         $transfer_nbr_be97_check=sprintf("%02d",$transfer_nbr_be97_check);
         $transfer_nbr_be97 = $transfer_nbr_be97_main.$transfer_nbr_be97_check;
         $transfer_nbr_be97 = substr($transfer_nbr_be97,0,3)."/".substr($transfer_nbr_be97,3,4)."/".substr($transfer_nbr_be97,7,5);
         $booking['transfer_nbr_be97'] = $transfer_nbr_be97_main.$transfer_nbr_be97_check;
         $where = array();
         $fields = array();
         $where['booking_id'] = $booking['booking_id'];
         $fields['transfer_nbr_be97'] = $booking['transfer_nbr_be97'];
         $wpdb->update($bookings_table, $fields, $where);
         if (has_action('eme_insert_rsvp_action')) do_action('eme_insert_rsvp_action',$booking);
         return $booking['booking_id'];
      } else {
         return false;
      }
// }
} 
function eme_delete_all_bookings_for_person_id($person_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "DELETE FROM $bookings_table WHERE person_id = $person_id";
   $wpdb->query($sql);
   $person = eme_get_person($person_id);
   return 1;
}
function eme_delete_booking_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "DELETE FROM $bookings_table WHERE person_id = $person_id AND event_id= $event_id";
   return $wpdb->query($sql);
}
function eme_delete_booking($booking_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "DELETE FROM $bookings_table WHERE booking_id = $booking_id";
   return $wpdb->query($sql);
}
function eme_update_booking_payed($booking_id,$value) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = $wpdb->prepare("UPDATE $bookings_table set booking_payed=$value WHERE booking_id = %s",$booking_id);
   return $wpdb->query($sql);
}
function eme_approve_booking($booking_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 

   $where = array();
   $fields = array();
   $where['booking_id'] =$booking_id;
   $fields['booking_approved'] = 1;
   $fields['modif_date']=current_time('mysql', false);
   $fields['modif_date_gmt']=current_time('mysql', true);
   $wpdb->update($bookings_table, $fields, $where);
   //$sql = "UPDATE $bookings_table SET booking_approved='1' WHERE booking_id = $booking_id";
   //$wpdb->query($sql);
   return __('Booking approved', 'eme');
}
function eme_update_booking_seats($booking_id,$seats) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $where = array();
   $fields = array();
   $where['booking_id'] =$booking_id;
   $fields['booking_seats'] = $seats;
   $fields['modif_date']=current_time('mysql', false);
   $fields['modif_date_gmt']=current_time('mysql', true);
   $wpdb->update($bookings_table, $fields, $where);
   //$sql = "UPDATE $bookings_table SET booking_seats='$seats' WHERE booking_id = $booking_id";
   //$wpdb->query($sql);
   return __('Booking approved', 'eme');
}

function eme_get_available_seats($event_id) {
   $event = eme_get_event($event_id);
   $available_seats = $event['event_seats'] - eme_get_booked_seats($event_id);
   return $available_seats;
}

function eme_get_booked_seats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE event_id = $event_id"; 
   return $wpdb->get_var($sql);
}

function eme_get_approved_seats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE event_id = $event_id and booking_approved=1"; 
   return $wpdb->get_var($sql);
}

function eme_get_pending_seats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE event_id = $event_id and booking_approved=0"; 
   return $wpdb->get_var($sql);
}

function eme_get_pending_bookings($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT COUNT(*) AS pending_bookings FROM $bookings_table WHERE event_id = $event_id and booking_approved=0"; 
   return $wpdb->get_var($sql);
}

function eme_are_seats_available_for($event_id, $seats) {
   #$event = eme_get_event($event_id);
   $available_seats = eme_get_available_seats($event_id);
   $remaning_seats = $available_seats - $seats;
   return ($remaning_seats >= 0);
} 
 
function eme_bookings_table($event_id) {
   $bookings =  eme_get_bookings_for($event_id);
   $destination = admin_url("edit.php"); 
   $result = "<form id='bookings-filter' method='get' action='$destination'>
                  <input type='hidden' name='page' value='eme_registration_seats_page'/>
                  <input type='hidden' name='event_id' value='$event_id'/>
                  <input type='hidden' name='action' value='delete_bookings'/>
                  <div class='wrap'>
                     <h2>Bookings</h2>
                  <table id='eme-bookings-table' class='widefat post fixed'>";
   $result .="<thead>
                     <tr><th class='manage-column column-cb check-column' scope='col'>&nbsp;</th><th class='manage-column ' scope='col'>".__('Booker','eme')."</th><th scope='col'>".__('E-mail','eme')."</th><th scope='col'>".__('Phone number','eme')."</th><th scope='col'>".__('Seats','eme')."</th><th scope='col'>".__('Unique nbr','eme')."</th></tr>
                  </thead>" ;
   foreach ($bookings as $booking) {
      $result .= "<tr> <td><input type='checkbox' value='".$booking['booking_id']."' name='bookings[]'/></td>
                              <td>".eme_sanitize_html($booking['person_name'])."</td>
                              <td>".eme_sanitize_html($booking['person_email'])."</td>
                              <td>".eme_sanitize_html($booking['person_phone'])."</td>
                              <td>".$booking['booking_seats']."</td>
                              <td>".eme_sanitize_html($booking['transfer_nbr_be97'])."</td>
                  </tr>";
   }
   $available_seats = eme_get_available_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   $result .= "<tfoot><tr><th scope='row' colspan='4'>".__('Booked spaces','eme').":</th><td class='booking-result' id='booked-seats'>$booked_seats</td></tr>
                   <tr><th scope='row' colspan='4'>".__('Available spaces','eme').":</th><td class='booking-result' id='available-seats'>$available_seats</td></tr></tfoot>
                     </table></div>
                     <div class='tablenav'>
                        <div class='alignleft actions'>
                         <input class=button-secondary action' type='submit' name='doaction2' value='Delete'/>
                           <br class='clear'/>
                        </div>
                        <br class='clear'/>
                     </div>
                  </form>";
   echo $result;
}

function eme_bookings_compact_table($event_id) {
   $bookings =  eme_get_bookings_for($event_id);
   $destination = admin_url("edit.php"); 
   $available_seats = eme_get_available_seats($event_id);
   $approved_seats = eme_get_approved_seats($event_id);
   $pending_seats = eme_get_pending_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   if ($pending_seats>0) {
      $booked_seats_info="$booked_seats ($approved_seats ".__('approved','eme').", $pending_seats ".__('pending','eme');
   } else {
      $booked_seats_info=$booked_seats;
   }
   $printable_address = admin_url("/admin.php?page=events-manager-people&amp;action=booking_printable&amp;event_id=$event_id");
   $csv_address = admin_url("/admin.php?page=events-manager-people&amp;action=booking_csv&amp;event_id=$event_id");
   $count_respondents=count($bookings);
   if ($count_respondents>0) { 
      $table = 
      "<div class='wrap'>
            <h4>$count_respondents ".__('respondents so far').":</h4>
            <table id='eme-bookings-table-$event_id' class='widefat post fixed'>
               <thead>
                  <tr>
                     <th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
                     <th class='manage-column ' scope='col'>".__('Respondent', 'eme')."</th>
                     <th scope='col'>".__('Spaces', 'eme')."</th>
                  </tr>
               </thead>
               <tfoot>
                  <tr>
                     <th scope='row' colspan='2'>".__('Booked spaces','eme').":</th><td class='booking-result' id='booked-seats'>$booked_seats_info</td></tr>
                  <tr><th scope='row' colspan='2'>".__('Available spaces','eme').":</th><td class='booking-result' id='available-seats'>$available_seats</td>
                  </tr>
               </tfoot>
               <tbody>" ;
      foreach ($bookings as $booking) {
         ($booking['booking_comment']) ? $baloon = " <img src='".EME_PLUGIN_URL."images/baloon.png' title='".__('Comment:','eme')." ".$booking['booking_comment']."' alt='comment'/>" : $baloon = "";
         $pending_string="";
         if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
            $pending_string=__('(pending)','eme');
         }
         $table .= 
         "<tr id='booking-".$booking['booking_id']."'> 
            <td><a id='booking-check-".$booking['booking_id']."' class='bookingdelbutton'>X</a></td>
            <td><a title=\"".eme_sanitize_html($booking['person_email'])." - ".eme_sanitize_html($booking['person_phone'])."\">".eme_sanitize_html($booking['person_name'])."</a>$baloon</td>
            <td>".$booking['booking_seats']." $pending_string </td>
          </tr>";
      }
    
      $table .=  "</tbody>
         </table>
         </div>
         <br class='clear'/>
         <div id='major-publishing-actions'>
         <div id='publishing-action'> 
            <a id='printable'  target='' href='$printable_address'>".__('Printable view','eme')."</a>
            <br class='clear'/>
         </div>
         <div id='publishing-action-csv'> 
            <a id='printable'  target='' href='$csv_address'>".__('CSV export','eme')."</a>
            <br class='clear'/>
         </div>
         <br class='clear'/>
         </div> ";
   } else {
      $table = "<p><em>".__('No responses yet!','eme')."</em></p>";
   } 
   echo $table;
}

function eme_get_bookings_for($event_ids,$pending_approved=0) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   
   $booking_data = array();
   if (!$event_ids)
      return $booking_data;
   
   if (is_array($event_ids)) {
      $where="event_id IN (".join(",",$event_ids).")";
   } else {
      $where="event_id = $event_ids";
   }
   if ($pending_approved==1) {
      $sql = "SELECT * FROM $bookings_table WHERE $where AND booking_approved=0";
   } elseif ($pending_approved==2) {
      $sql = "SELECT * FROM $bookings_table WHERE $where AND booking_approved=1";
   } else {
      $sql = "SELECT * FROM $bookings_table WHERE $where";
   }
   $bookings = $wpdb->get_results($sql, ARRAY_A);
   if ($bookings) {
      foreach ($bookings as $booking) {
         $person = eme_get_person($booking['person_id']);
         $booking['person_name'] = $person['person_name']; 
         $booking['person_email'] = $person['person_email'];
         $booking['person_phone'] = $person['person_phone'];
         array_push($booking_data, $booking);
      }
   }
   return $booking_data;
}

function eme_get_bookings_list_for($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT DISTINCT person_id FROM $bookings_table WHERE event_id = %s",$event_id);
   $person_ids = $wpdb->get_col($sql);
   if ($person_ids) {
      $attendees=eme_get_persons($person_ids);
      $res="<ul class='eme_bookings_list_ul'>";
      foreach ($attendees as $attendee) {
         $res.=eme_replace_attendees_placeholders(get_option('eme_attendees_list_format'),$attendee,$event_id);
      }
      $res.="</ul>";
   } else {
      $res="<p class='eme_no_bookings'>".__('No responses yet!','eme')."</p>";
   }
   return $res;
}

function eme_replace_attendees_placeholders($format, $attendee, $event_id, $target="html") {
   preg_match_all("/#_?[A-Za-z0)9_]+/", $format, $placeholders);
   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      if (preg_match('/#_(NAME|PHONE|ID|EMAIL)$/', $result)) {
         $field = "person_".ltrim(strtolower($result), "#_");
         $replacement = $attendee[$field];
         $replacement = eme_sanitize_html($replacement);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 

      } elseif (preg_match('/#_USER_(RESERVEDSPACES|BOOKEDSEATS)$/', $result)) {
         $replacement = eme_get_booked_seats_by_person_event_id($attendee['person_id'],$event_id);
      } else {
         $found = 0;
      }
      if ($found) {
         $format = str_replace($result, $replacement ,$format );
      }
   }
   return do_shortcode($format);   
}

function eme_email_rsvp_booking($booking_id,$action="") {
   // first check if a mail should be send at all
   $mailing_is_active = get_option('eme_rsvp_mail_notify_is_active');
   if (!$mailing_is_active) {
      return;
   }

   $booking = eme_get_booking ($booking_id);
   $person  = eme_get_person ($booking['person_id']);
   $event = eme_get_event($booking['event_id']);
   $event_name = $event['event_name'];
   $contact = eme_get_contact ($event);
   $contact_email = $contact->user_email;
   $contact_name = $contact->display_name;
   
   $contact_body = ( $event['event_contactperson_email_body'] != '' ) ? $event['event_contactperson_email_body'] : get_option('eme_contactperson_email_body' );
   $contact_body = eme_replace_placeholders($contact_body, $event, "text");
   $confirmed_body = ( $event['event_respondent_email_body'] != '' ) ? $event['event_respondent_email_body'] : get_option('eme_respondent_email_body' );
   $confirmed_body = eme_replace_placeholders($confirmed_body, $event, "text");
   $pending_body = ( $event['event_registration_pending_email_body'] != '' ) ? $event['event_registration_pending_email_body'] : get_option('eme_registration_pending_email_body' );
   $pending_body = eme_replace_placeholders($pending_body, $event, "text");
   $denied_body = get_option('eme_registration_denied_email_body' );
   $denied_body = eme_replace_placeholders($denied_body, $event, "text");
   $cancelled_body = get_option('eme_registration_cancelled_email_body' );
   $cancelled_body = eme_replace_placeholders($cancelled_body, $event, "text");
   
   // rsvp specific placeholders
   $placeholders = array('#_RESPNAME' => $person['person_name'], '#_RESPEMAIL' => $person['person_email'], '#_RESPPHONE' => $person['person_phone'], '#_SPACES' => $booking['booking_seats'],'#_COMMENT' => $booking['booking_comment'], '#_TRANSFER_NBR_BE97' => $booking['transfer_nbr_be97'] );

   foreach($placeholders as $key => $value) {
      $contact_body = str_replace($key, $value, $contact_body);
      $confirmed_body = str_replace($key, $value, $confirmed_body);
      $pending_body = str_replace($key, $value, $pending_body);
      $denied_body = str_replace($key, $value, $denied_body);
      $cancelled_body = str_replace($key, $value, $cancelled_body);
   }

   if($action!="") {
      if ($action == 'approveRegistration') {
         eme_send_mail(sprintf(__("Reservation for '%s' confirmed",'eme'),$event_name),$confirmed_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
      } elseif ($action == 'denyRegistration') {
         eme_send_mail(sprintf(__("Reservation for '%s' denied",'eme'),$event_name),$denied_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
      } elseif ($action == 'cancelRegistration') {
         eme_send_mail(sprintf(__("Reservation for '%s' cancelled",'eme'),$event_name),$cancelled_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
         eme_send_mail(sprintf(__("A reservation has been cancelled for '%s'",'eme'),$event_name), $contact_body, $contact_email, $contact_name, $contact_email, $contact_name);
      }
   } else {
      // send different mails depending on approval or not
      if ($event['registration_requires_approval']) {
         eme_send_mail(sprintf(__("Approval required for new booking for '%s'",'eme'),$event_name), $contact_body, $contact_email, $contact_name, $contact_email, $contact_name);
         eme_send_mail(sprintf(__("Reservation for '%s' is pending",'eme'),$event_name),$pending_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
      } else {
         eme_send_mail(sprintf(__("New booking for '%s'",'eme'),$event_name), $contact_body, $contact_email,$contact_name, $contact_email, $contact_name);
         eme_send_mail(sprintf(__("Reservation for '%s' confirmed",'eme'),$event_name),$confirmed_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
      }
   }
} 

function eme_registration_seats_page() {
   global $wpdb;

   if (current_user_can( get_option('eme_cap_registrations'))) {
      // do the actions if required
      if (isset($_GET['action']) && $_GET['action'] == "delete_bookings" && isset($_GET['bookings'])) {
         $bookings = $_GET['bookings'];
         if (is_array($bookings)) {
            foreach($bookings as $booking_id) {
               eme_delete_booking(intval($booking_id));
            }
         }
      } else {
         $action = isset($_POST ['action']) ? $_POST ['action'] : '';

         if ($action == 'addRegistration') {
            $event_id = intval($_POST['event_id']);
            $booking_payed = isset($_POST ['booking_payed']) ? intval($_POST ['booking_payed']) : 0;
            $event = eme_get_event($event_id);
            $booking_res = eme_book_seats($event);
            $result=$booking_res[0];
            $booking_id_done=$booking_res[1];
            eme_update_booking_payed($booking_id_done,$booking_payed);
            print $result;
         }

         $bookings = isset($_POST ['bookings']) ? $_POST ['bookings'] : array();
         $selected_bookings = isset($_POST ['selected_bookings']) ? $_POST ['selected_bookings'] : array();
         $bookings_seats = isset($_POST ['bookings_seats']) ? $_POST ['bookings_seats'] : array();
         $bookings_payed = isset($_POST ['bookings_payed']) ? $_POST ['bookings_payed'] : array();
         foreach ( $bookings as $key=>$booking_id ) {
            if (!in_array($booking_id,$selected_bookings)) {
               continue;
            }
            // make sure the seats are integers
            $bookings_seats[$key]=intval($bookings_seats[$key]);
            $booking = eme_get_booking ($booking_id);
            if ($action == 'approveRegistration') {
               if ($booking['booking_payed']!= intval($bookings_payed[$key]))
                  eme_update_booking_payed($booking_id,intval($bookings_payed[$key]));
               if ($booking['booking_seats']!= $bookings_seats[$key]) {
                  eme_update_booking_seats($booking_id,$bookings_seats[$key]);
                  eme_email_rsvp_booking($booking_id,$action);
               }
            } elseif ($action == 'denyRegistration') {
               eme_email_rsvp_booking($booking_id,$action);
               eme_delete_booking($booking_id);
            }
         }
      }
   }
   
   // now show the menu
   $event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
   eme_registration_seats_form_table($event_id);
}

function eme_registration_seats_form_table($event_id=0) {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Change reserved spaces or cancel registrations','eme'); ?></h2>
<?php admin_show_warnings();?>
   <form id='add-booking' action="" method="post">
   <input type='hidden' name='page' value='events-manager-registration-seats' />
   <input type='hidden' name='action' value='addRegistration' />
   <table class="widefat">
   <tbody>
            <tr><th scope='row'><?php _e('Name', 'eme'); ?>*:</th><td><input type='text' name='bookerName' value='' /></td></tr>
            <tr><th scope='row'><?php _e('E-Mail', 'eme'); ?>*:</th><td><input type='text' name='bookerEmail' value='' /></td></tr>
            <tr><th scope='row'><?php _e('Phone number', 'eme'); ?>:</th><td><input type='text' name='bookerPhone' value='' /></td></tr>
            <tr><th scope='row'><?php _e('Event', 'eme'); ?>*:</th><td>
   <select name="event_id">
   <?php
   $all_events=eme_get_events(0,"future");
   $events_with_pending_bookings=array();
   foreach ( $all_events as $event ) {
      if ($event ['event_rsvp']) {
         $option_text=$event['event_name']." (".date_i18n (get_option('date_format'), strtotime($event['event_start_date'])).")"; 
         echo "<option value='".$event['event_id']."' >".$option_text."</option>  ";
      }
   }
   ?>
   </select>
                </td>
            </tr>
            <tr><th scope='row'><?php _e('Seats', 'eme'); ?>*:</th><td><input type='text' name='bookedSeats' value='' /></td></tr>
            <tr><th scope='row'><?php _e('Paid', 'eme'); ?>:</th><td><?php echo eme_ui_select_binary(0,"booking_payed"); ?></td></tr>
   </tbody>
   </table>
   <input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Register booking' )?>" />
   </form>

   <div class="clear"></div>

   <form id="posts-filter" action="" method="post">
   <input type='hidden' name='page' value='events-manager-registration-seats' />
   <div class="tablenav">

   <div class="alignleft actions">
   <select name="action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
   <option value="approveRegistration"><?php _e ( 'Update registration','eme' ); ?></option>
   <option value="denyRegistration"><?php _e ( 'Deny registration','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
   <select name="event_id">
   <option value='0'><?php _e ( 'All events' ); ?></option>
   <?php
   $all_events=eme_get_events(0,"future");
   $events_with_bookings=array();
   foreach ( $all_events as $event ) {
      if (eme_get_approved_seats($event['event_id'])>0) {
         $events_with_bookings[]=$event['event_id'];
         $selected = "";
         if ($event_id && ($event['event_id'] == $event_id))
            $selected = "selected='selected'";
         echo "<option value='".$event['event_id']."' $selected>".$event['event_name']."</option>  ";
      }
   }
   ?>
   </select>
   <input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
   </div>
   <div class="clear"></div>
   <table class="widefat">
   <thead>
      <tr>
         <th class='manage-column column-cb check-column' scope='col'><input
            class='select-all' type="checkbox" value='1' /></th>
         <th><?php _e ( 'Name', 'eme' ); ?></th>
         <th><?php _e ( 'Date and time', 'eme' ); ?></th>
         <th><?php _e ('Booker','eme'); ?></th>
         <th><?php _e ('Seats','eme'); ?></th>
         <th><?php _e ('Unique nbr','eme'); ?></th>
         <th><?php _e ('Paid','eme'); ?></th>
      </tr>
   </thead>
   <tbody>
     <?php
      $i = 1;
      if ($event_id) {
         $bookings = eme_get_bookings_for($event_id,2);
      } else {
         $bookings = eme_get_bookings_for($events_with_bookings,2);
      }
      foreach ( $bookings as $event_booking ) {
         $event=eme_get_event($event_booking['event_id']);
         $class = ($i % 2) ? ' class="alternate"' : '';
         $localised_start_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_start_date']));
         $localised_end_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_end_date']));
         $style = "";
         $today = date ( "Y-m-d" );
         
         if ($event['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         ?>
      <tr <?php echo "$class $style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='selected_bookings[]' />
             <input type='hidden' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='bookings[]' /></td>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_event&amp;event_id=".$event_booking ['event_id']); ?>"><?php echo eme_sanitize_html($event ['event_name']); ?></a>
         </strong>
         <?php
             $approved_seats = eme_get_approved_seats($event['event_id']);
             $pending_seats = eme_get_pending_seats($event['event_id']);
             $total_seats = $event ['event_seats'];
             echo "<br />".__('Approved: ','eme' ).$approved_seats.", ".__('Pending: ','eme').$pending_seats.", ".__('Max: ','eme').$total_seats;
         ?>
         </td>
         <td>
            <?php echo $localised_start_date; if ($localised_end_date !='') echo " - " . $localised_end_date; ?><br />
            <?php echo substr ( $event['event_start_time'], 0, 5 ) . " - " . substr ( $event['event_end_time'], 0, 5 ); ?>
         </td>
         <td>
            <?php echo eme_sanitize_html($event_booking['person_name']) ."(".eme_sanitize_html($event_booking['person_phone']).", ". eme_sanitize_html($event_booking['person_email']).")";?>
         </td>
         <td>
            <input type="text" name="bookings_seats[]" value="<?php echo $event_booking['booking_seats'];?>" />
         </td>
         <td>
            <?php echo eme_sanitize_html($event_booking['transfer_nbr_be97']); ?>
         </td>
         <td>
            <?php echo eme_ui_select_binary($event_booking['booking_payed'],"bookings_payed[]"); ?>
         </td>
      </tr>
      <?php
         $i++;
      }
      ?>
   </tbody>
   </table>

   <div class='tablenav'>
   <div class="alignleft actions"><br class='clear' />
   </div>
   <br class='clear' />
   </div>

   </div>
   </form>
</div>
<?php
}
function eme_registration_approval_page() {
        global $wpdb;

   if (current_user_can( get_option('eme_cap_approve'))) {
      // do the actions if required
      $action = isset($_POST ['action']) ? $_POST ['action'] : '';
      $pending_bookings = isset($_POST ['pending_bookings']) ? $_POST ['pending_bookings'] : array();
      $selected_bookings = isset($_POST ['selected_bookings']) ? $_POST ['selected_bookings'] : array();
      $bookings_seats = isset($_POST ['bookings_seats']) ? $_POST ['bookings_seats'] : array();
      $bookings_payed = isset($_POST ['bookings_payed']) ? $_POST ['bookings_payed'] : array();
      foreach ( $pending_bookings as $key=>$booking_id ) {
         if (!in_array($booking_id,$selected_bookings)) {
            continue;
         }
         $booking = eme_get_booking ($booking_id);
         $person  = eme_get_person ($booking['person_id']);
         // update the db
         if ($action == 'approveRegistration') {
            eme_approve_booking($booking_id);
            // 0 seats is not possible, then you should remove the booking
            if ($bookings_seats[$key]==0)
               $bookings_seats[$key]=1;
            if ($booking['booking_payed']!= intval($bookings_payed[$key]))
               eme_update_booking_payed($booking_id,intval($bookings_payed[$key]));
            if ($booking['booking_seats']!= intval($bookings_seats[$key])) {
               eme_update_booking_seats($booking_id,intval($bookings_seats[$key]));
            }
            eme_email_rsvp_booking($booking_id,$action);
         } elseif ($action == 'denyRegistration') {
            eme_email_rsvp_booking($booking_id,$action);
            eme_delete_booking($booking_id);
         }
      }
   }
   // now show the menu
   $event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
   eme_registration_approval_form_table($event_id);
}

function eme_registration_approval_form_table($event_id=0) {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Pending Approvals','eme'); ?></h2>
<?php admin_show_warnings();?>
   <form id="posts-filter" action="" method="post">
   <input type='hidden' name='page' value='events-manager-registration-approval' />
   <div class="tablenav">

   <div class="alignleft actions">
   <select name="action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
   <option value="approveRegistration"><?php _e ( 'Approve registration','eme' ); ?></option>
   <option value="denyRegistration"><?php _e ( 'Deny registration','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
   <select name="event_id">
   <option value='0'><?php _e ( 'All events' ); ?></option>
   <?php
   $all_events=eme_get_events(0,"future");
   $events_with_pending_bookings=array();
   foreach ( $all_events as $event ) {
      if (eme_get_pending_bookings($event['event_id'])>0) {
         $events_with_pending_bookings[]=$event['event_id'];
         $selected = "";
         if ($event_id && ($event['event_id'] == $event_id))
            $selected = "selected='selected'";
         echo "<option value='".$event['event_id']."' $selected>".$event['event_name']."</option>  ";
      }
   }
   ?>
   </select>
   <input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
   </div>
   <div class="clear"></div>
   <table class="widefat">
   <thead>
      <tr>
         <th class='manage-column column-cb check-column' scope='col'><input
            class='select-all' type="checkbox" value='1' /></th>
         <th><?php _e ( 'Name', 'eme' ); ?></th>
         <th><?php _e ( 'Date and time', 'eme' ); ?></th>
         <th><?php _e ('Booker','eme'); ?></th>
         <th><?php _e ('Seats','eme'); ?></th>
         <th><?php _e ('Unique nbr','eme'); ?></th>
         <th><?php _e ('Paid','eme'); ?></th>
      </tr>
   </thead>
   <tbody>
     <?php
      $i = 1;
      if ($event_id) {
         $pending_bookings = eme_get_bookings_for($event_id,1);
      } else {
         $pending_bookings = eme_get_bookings_for($events_with_pending_bookings,1);
      }
      foreach ( $pending_bookings as $event_booking ) {
         $event=eme_get_event($event_booking['event_id']);
         $class = ($i % 2) ? ' class="alternate"' : '';
         $localised_start_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_start_date']));
         $localised_end_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_end_date']));
         $style = "";
         $today = date ( "Y-m-d" );
         
         if ($event['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         ?>
      <tr <?php echo "$class $style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='selected_bookings[]' /></td>
             <input type='hidden' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='pending_bookings[]' /></td>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_event&amp;event_id=".$event_booking ['event_id']); ?>"><?php echo eme_sanitize_html($event ['event_name']); ?></a>
         </strong>
         <?php
             $approved_seats = eme_get_approved_seats($event['event_id']);
             $pending_seats = eme_get_pending_seats($event['event_id']);
             $total_seats = $event ['event_seats'];
             echo "<br />".__('Approved: ','eme' ).$approved_seats.", ".__('Pending: ','eme').$pending_seats.", ".__('Max: ','eme').$total_seats;
         ?>
         </td>
         <td>
            <?php echo $localised_start_date; if ($localised_end_date !='') echo " - " . $localised_end_date; ?><br />
            <?php echo substr ( $event['event_start_time'], 0, 5 ) . " - " . substr ( $event['event_end_time'], 0, 5 ); ?>
         </td>
         <td>
            <?php echo eme_sanitize_html($event_booking['person_name']) ."(".eme_sanitize_html($event_booking['person_phone']).", ". eme_sanitize_html($event_booking['person_email']).")";?>
         </td>
         <td>
            <input type="text" name="bookings_seats[]" value="<?php echo $event_booking['booking_seats'];?>" />
         </td>
         <td>
            <?php echo eme_sanitize_html($event_booking['transfer_nbr_be97']); ?>
         </td>
         <td>
            <?php echo eme_ui_select_binary($event_booking['booking_payed'],"bookings_payed[]"); ?>
         </td>
      </tr>
      <?php
         $i++;
      }
      ?>
   </tbody>
   </table>

   <div class='tablenav'>
   <div class="alignleft actions"><br class='clear' />
   </div>
   <br class='clear' />
   </div>

   </div>
   </form>
</div>
<?php
}

function eme_paypal_form($event,$booking_id) {
   $booking = eme_get_booking($booking_id);
   $events_page_link = eme_get_events_page(true, false);
   if (stristr ( $events_page_link, "?" ))
      $joiner = "&amp;";
   else
      $joiner = "?";
   $ipn_link = $events_page_link.$joiner."eme_eventAction=ipn";

   $form_html = "<div id='eme-rsvp-message' class='eme-rsvp-message'>".__('Payment handling','eme')."</div>";
   $form_html = "<p>".__("You can pay for this event via paypal. If you wish to do so, click the 'Pay via Paypal' button below.",'eme')."</p>";
   require "paypal/Paypal.php";
   $p = new Paypal;

   // the paypal or paypal sandbox url
   //$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
   $p->paypal_url = get_option('eme_paypal_url');

   // the timeout in seconds before the button form is submitted to paypal
   // this needs the included addevent javascript function
   // 0 = no delay
   // false = disable auto submission
   $p->timeout = false;

   // the button label
   // false to disable button (if you want to rely only on the javascript auto-submission) not recommended
   $p->button = __('Pay via Paypal','eme');

   // use encryption (strongly recommended!)
   $p->encrypt = false;

   // the actual button parameters
   // https://www.paypal.com/IntegrationCenter/ic_std-variable-reference.html
   $p->add_field('charset','utf-8');
   $p->add_field('business', get_option('eme_paypal_business'));
   $p->add_field('return', eme_event_url($event));
   $p->add_field('cancel_return', eme_event_url($event));
   $p->add_field('notify_url', $ipn_link);
   $p->add_field('item_name', sprintf(__("Booking for '%s'","eme"),eme_sanitize_html($event['event_name'])));
   $p->add_field('item_number', $booking_id);
   $p->add_field('amount', $event['price']);
   $p->add_field('currency_code',$event['currency']);
   $p->add_field('quantity', $booking['booking_seats']);

   $form_html .= $p->get_button();
   return $form_html;
}

function eme_paypal_ipn() {
   require 'paypal/IPN.php';
   $ipn = new IPN;

   // the paypal url, or the sandbox url, or the ipn test url
   //$ipn->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
   $ipn->paypal_url = get_option('eme_paypal_url');

   // your paypal email (the one that receives the payments)
   $ipn->paypal_email = get_option('eme_paypal_business');

   // log to file options
   $ipn->log_to_file = false;					// write logs to file
   $ipn->log_filename = '/path/to/ipn.log';  	// the log filename (should NOT be web accessible and should be writable)

   // log to e-mail options
   $ipn->log_to_email = false;					// send logs by e-mail
   $ipn->log_email = '';		// where you want to receive the logs
   $ipn->log_subject = 'IPN Log: ';			// prefix for the e-mail subject

   // database information
   $ipn->log_to_db = false;						// false not recommended
   $ipn->db_host = 'localhost';				// database host
   $ipn->db_user = '';				// database user
   $ipn->db_pass = '';			// database password
   $ipn->db_name = '';						// database name

   // array of currencies accepted or false to disable
   $ipn->currencies = array('USD','EUR');

   // date format on log headers (default: dd/mm/YYYY HH:mm:ss)
   // see http://php.net/date
   $ipn->date_format = 'd/m/Y H:i:s';

   // Prefix for file and mail logs
   $ipn->pretty_ipn = "IPN Values received:\n\n";

   // configuration ended, do the actual check

   if($ipn->ipn_is_valid()) {
      /*
         A valid ipn was received and passed preliminary validations
         You can now do any custom validations you wish to ensure the payment was correct
         You can access the IPN data with $ipn->ipn['value']
         The complete() method below logs the valid IPN to the places you choose
       */
      $booking_id=$ipn->ipn['item_number'];
      eme_update_booking_payed($booking_id,1);
      $ipn->complete();
   }
}

// template function
function eme_is_event_rsvpable() {
   if (eme_is_single_event_page() && isset($_REQUEST['event_id'])) {
      $event = eme_get_event(intval($_REQUEST['event_id']));
      if($event)
         return $event['event_rsvp'];
   }
   return 0;
}

function eme_event_needs_approval($event_id) {
   global $wpdb;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $sql = "SELECT registration_requires_approval from $events_table where event_id=$event_id";
   return $wpdb->get_var( $wpdb->prepare($sql) );
}

?>
