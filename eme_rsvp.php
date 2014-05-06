<?php

function eme_payment_form($event,$booking_id,$form_result_message) {

   $ret_string = "<div id='eme-rsvp-message'>";
   if(!empty($form_result_message))
      $ret_string .= "<div class='eme-rsvp-message'>$form_result_message</div>";
   $ret_string .= "</div>";

   if (empty($event)) {
      $event_id=eme_get_event_id_by_booking_id($booking_id);
      if ($event_id)
         $event = eme_get_event($event_id);
   }

   $booking = eme_get_booking($booking_id);
   if (!is_array($booking))
      return $ret_string;
   if ($booking['booking_payed'])
      return $ret_string."<div class='eme-already-payed'>".__('This booking has already been payed for','eme')."</div>";

   if (is_array($event) && eme_event_needs_payment($event)) {
      $eme_payment_form_header_format=get_option('eme_payment_form_header_format');
      if (!empty($eme_payment_form_header_format)) {
            $result = eme_replace_placeholders($eme_payment_form_header_format, $event,"html",0);
            $result = eme_replace_booking_placeholders($result, $event, $booking);
            $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
            $ret_string .= $result;
            $ret_string .= "</div>";
      } else {
         $total_price = eme_get_total_booking_price($event,$booking);
         $ret_string .= "<div id='eme-payment-handling' class='eme-payment-handling'>".__('Payment handling','eme')."</div>";
         $ret_string .= "<div id='eme-payment-price-info' class='eme-payment-price-info'>".sprintf(__("The booking price in %s is: %01.2f",'eme'),$event['currency'],$total_price)."</div>";
      }
      $ret_string .= "<div id='eme-payment-form' class='eme-payment-form'>";
      if ($event['use_paypal'])
         $ret_string .= eme_paypal_form($event,$booking_id);
      if ($event['use_2co'])
         $ret_string .= eme_2co_form($event,$booking_id);
      if ($event['use_webmoney'])
         $ret_string .= eme_webmoney_form($event,$booking_id);
      if ($event['use_google'])
         $ret_string .= eme_google_form($event,$booking_id);
      if ($event['use_fdgg'])
         $ret_string .= eme_fdgg_form($event,$booking_id);
      $ret_string .= "</div>";

      $eme_payment_form_footer_format=get_option('eme_payment_form_footer_format');
      if (!empty($eme_payment_form_footer_format)) {
            $result = eme_replace_placeholders($eme_payment_form_footer_format, $event,"html",0);
            $result = eme_replace_booking_placeholders($result, $event, $booking);
            $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
            $ret_string .= $result;
            $ret_string .= "</div>";
      }
   }

   return $ret_string;
}

function eme_add_booking_form($event_id) {
   $event = eme_get_event($event_id);
   // rsvp not active or no rsvp for this event, then return
   if (!eme_is_event_rsvp($event)) {
      return;
   }
   
   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only) {
      // we require a user to be WP registered to be able to book
      if (!is_user_logged_in()) {
         return;
      }
   }

   #$destination = eme_event_url($event)."#eme-rsvp-message";
   if (isset($_GET['lang'])) {
      $language=eme_strip_tags($_GET['lang']);
      $destination = "?lang=".$language."#eme-rsvp-message";
   } else {
      $destination = "#eme-rsvp-message";
   }

   // after the add or delete booking, we do a POST to the same page using javascript to show just the result
   // this has 2 advantages: you can give arguments in the post, and refreshing the page won't repeat the booking action, just the post showing the result
   // a javascript redir using window.replace + GET would work too, but that leaves an ugly GET url
   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'add_booking' && isset($_POST['event_id'])) {
      $event_id = intval($_POST['event_id']);
      $event = eme_get_event($event_id);
      $booking_res = eme_book_seats($event);
      $form_result_message = $booking_res[0];
      $booking_id_done=$booking_res[1];
      $post_string="{";
      if ($booking_id_done && eme_event_needs_payment($event)) {
         // you did a successfull registration, so now we decide wether to show the form again, or the payment form
         // but to make sure people don't mess with the booking id in the url, we use wp_nonce
         // by default the nonce is valid for 24 hours
         $eme_payment_nonce=wp_create_nonce('eme_booking_id_'.$booking_id_done);
         // create the JS array that will be used to post
         $post_arr = array (
               "eme_eventAction" => 'pay_booking',
               "eme_message" => $form_result_message,
               "booking_id" => $booking_id_done,
               "eme_payment_nonce" => $eme_payment_nonce
               );
      } elseif ($booking_id_done) {
         $post_arr = array (
               "eme_eventAction" => 'message',
               "eme_message" => $form_result_message,
               );
      } else {
         // booking failed: we add $_POST to the json, so we can pre-fill the form so the user can just correct the mistake
         $post_arr = stripslashes_deep($_POST);
         $post_arr['eme_eventAction'] = 'message';
         $post_arr['eme_message'] = $form_result_message;
      }
      $post_string=json_encode($post_arr);
      ?>
      <script type="text/javascript">
      function postwith (to,p) {
         var myForm = document.createElement("form");
         myForm.method="post" ;
         myForm.action = to ;
         for (var k in p) {
            var myInput = document.createElement("input") ;
            myInput.setAttribute("name", k) ;
            myInput.setAttribute("value", p[k]);
            myForm.appendChild(myInput) ;
         }
         document.body.appendChild(myForm) ;
         myForm.submit() ;
         document.body.removeChild(myForm) ;
      }
      <?php echo "postwith('$destination',$post_string);"; ?>
      </script>
      <?php
      return;
   }

   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'pay_booking' && isset($_POST['eme_message']) && isset($_POST['booking_id'])) {
      $booking_id = intval($_POST['booking_id']);
      // verify the nonce, to make sure people didn't mess with the booking id
      if (!isset($_POST['eme_payment_nonce']) || !wp_verify_nonce($_POST['eme_payment_nonce'], 'eme_booking_id_'.$booking_id)) {
         return;
      } else {
         // due to the double POST javascript, the eme_message is escaped again, so we need stripslashes
         // but the message may contain html, so no html sanitize
         $form_result_message = eme_translate(stripslashes_deep($_POST['eme_message']));
         // when the add and delete forms are shown on the same page, the message would also be shown twice, this prevents that
         unset($_POST['eme_message']);
         return eme_payment_form($event,$booking_id,$form_result_message);
      }
   }
   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'message' && isset($_POST['eme_message'])) {
      // due to the double POST javascript, the eme_message is escaped again, so we need stripslashes
      // but the message may contain html, so no html sanitize
      $form_result_message = eme_translate(stripslashes_deep($_POST['eme_message']));
      // when the add and delete forms are shown on the same page, the message would also be shown twice, this prevents that
      unset($_POST['eme_message']);
   }

   $ret_string = "<div id='eme-rsvp-message'>";
   if(!empty($form_result_message))
      $ret_string .= "<div class='eme-rsvp-message'>$form_result_message</div>";

   $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
   if (time()+$event['rsvp_number_days']*60*60*24+$event['rsvp_number_hours']*60*60 > $event_start_datetime ) {
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer allowed on this date.', 'eme')."</div></div>";
   }

   // you can book the available number of seats, with a max of x per time
   $min_allowed = $event['event_properties']['min_allowed'];
   // the next gives the number of available seats, even for multiprice
   $avail_seats = eme_get_available_seats($event_id);
   // no seats anymore? No booking form then ... but only if it is required that the min number of
   // bookings should be >0 (it can be=0 for attendance bookings)
   if (eme_is_multi($min_allowed))
      $min=eme_get_multitotal($min_allowed);
   else
      $min=$min_allowed;

   if ($avail_seats == 0 && $min>0) {
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer possible: no seats available anymore', 'eme')."</div></div>";
   }

   $ret_string .= "<form id='eme-rsvp-form' name='booking-form' method='post' action='$destination'>";
   $ret_string .= eme_replace_formfields_placeholders ($event);
   // add a nonce for extra security
   $ret_string .= wp_nonce_field('add_booking','eme_rsvp_nonce',false,false);
   // also add a honeypot field: if it gets completed with data, 
   // it's a bot, since a humand can't see this (using CSS to render it invisible)
   $ret_string .= "<span id='honeypot_check'>Keep this field blank: <input type='text' name='honeypot_check' value='' /></span>
      <p>".__('(* marks a required field)', 'eme')."</p>
      <input type='hidden' name='eme_eventAction' value='add_booking'/>
      <input type='hidden' name='event_id' value='$event_id'/>
   </form></div>";
 
   if (has_filter('eme_add_booking_form_filter')) $ret_string=apply_filters('eme_add_booking_form_filter',$form_html);
   return $ret_string;
   
}

function eme_add_booking_form_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>0), $atts));
   return eme_add_booking_form($id);
}

function eme_booking_list_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>0,'template_id'=>0,'template_id_header'=>0,'template_id_footer'=>0), $atts));
   $event = eme_get_event(intval($id));
   if ($event)
      return eme_get_bookings_list_for($event,$template_id,$template_id_header,$template_id_footer);
}

function eme_attendee_list_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>0,'template_id'=>0,'template_id_header'=>0,'template_id_footer'=>0), $atts));
   $event = eme_get_event(intval($id));
   if ($event)
      return eme_get_attendees_list_for($event,$template_id,$template_id_header,$template_id_footer);
}

function eme_delete_booking_form($event_id) {
   global $current_user;
   
   $form_html = "";
   $event = eme_get_event($event_id);
   // rsvp not active or no rsvp for this event, then return
   if (!eme_is_event_rsvp($event)) {
      return;
   }
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

   #$destination = eme_event_url($event)."#eme-rsvp-message";
   if (isset($_GET['lang'])) {
      $language=eme_strip_tags($_GET['lang']);
      $destination = "?lang=".$language."#eme-rsvp-message";
   } else {
      $destination = "#eme-rsvp-message";
   }
   
   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'delete_booking' && isset($_POST['event_id'])) {
      $form_result_message = eme_cancel_seats($event);
      // post to a page showing the result of the booking
      // create the JS array that will be used to post
      $post_arr = array (
            "eme_eventAction" => 'message',
            "eme_message" => $form_result_message,
            );
      $post_string=json_encode($post_arr);
      ?>
      <script type="text/javascript">
      function postwith (to,p) {
         var myForm = document.createElement("form");
         myForm.method="post" ;
         myForm.action = to ;
         for (var k in p) {
            var myInput = document.createElement("input") ;
            myInput.setAttribute("name", k) ;
            myInput.setAttribute("value", p[k]);
            myForm.appendChild(myInput) ;
         }
         document.body.appendChild(myForm) ;
         myForm.submit() ;
         document.body.removeChild(myForm) ;
      }
      <?php echo "postwith('$destination',$post_string);"; ?>
      </script>
      <?php
      return;
   }
   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'message' && isset($_POST['eme_message'])) {
      $form_result_message = eme_sanitize_html($_POST['eme_message']);
      // when the add and delete forms are shown on the same page, the message would also be shown twice, this prevents that
      unset($_POST['eme_message']);
   }

   $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
   if (time()+$event['rsvp_number_days']*60*60*24+$event['rsvp_number_hours']*60*60 > $event_start_datetime ) {
      $ret_string = "<div id='eme-rsvp-message'>";
      if(!empty($form_result_message))
         $ret_string .= "<div class='eme-rsvp-message'>$form_result_message</div>";
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer allowed on this date.', 'eme')."</div></div>";
   }

   if(!empty($form_result_message)) {
      $form_html = "<div id='eme-rsvp-message'>";
      $form_html .= "<div class='eme-rsvp-message'>$form_result_message</div>";
      $form_html .= "</div>";
   }

   $form_html .= "<form id='booking-delete-form' name='booking-delete-form' method='post' action='$destination'>
      <input type='hidden' name='eme_eventAction' value='delete_booking'/>
      <input type='hidden' name='event_id' value='$event_id'/>";
   $form_html .= wp_nonce_field('del_booking','eme_rsvp_nonce',false,false);
   $form_html .= eme_replace_cancelformfields_placeholders($event);
   $form_html .= "<span id='honeypot_check'>Keep this field blank: <input type='text' name='honeypot_check' value='' /></span>
      <p>".__('(* marks a required field)', 'eme')."</p>";
   $form_html .= "</form>";

   if (has_filter('eme_delete_booking_form_filter')) $form_html=apply_filters('eme_delete_booking_form_filter',$form_html);
   return $form_html;
}

function eme_delete_booking_form_shortcode($atts) {
   extract ( shortcode_atts ( array ('id' => 0), $atts));
   return eme_delete_booking_form($id);
}

 // eme_cancel_seats is NOT called from the admin backend, but to be sure: we check for it
function eme_cancel_seats($event) {
   global $current_user;
   $event_id = $event['event_id'];
   $registration_wp_users_only=$event['registration_wp_users_only'];

   if (is_admin()) {
      return __('This function is not allowed from the admin backend.', 'eme');
   }

   // check for spammers as early as possible
   if (isset($_POST['honeypot_check'])) {
      $honeypot_check = stripslashes($_POST['honeypot_check']);
   } elseif (!is_admin() && !isset($_POST['honeypot_check'])) {
      $honeypot_check = "bad boy";
   } else {
      $honeypot_check = "";
   }

   if (!is_admin() && get_option('eme_captcha_for_booking')) {
      $captcha_err = response_check_captcha("captcha_check","eme_del_booking");
   } else {
      $captcha_err = "";
   }

   if (!is_admin() && (! isset( $_POST['eme_rsvp_nonce'] ) ||
       ! wp_verify_nonce( $_POST['eme_rsvp_nonce'], 'del_booking' ))) {
      $nonce_err = "bad boy";
   } else {
      $nonce_err = "";
   }

   if(!empty($captcha_err)) {
      return __('You entered an incorrect code','eme');
   } elseif (!empty($honeypot_check) ||  !empty($nonce_err)) {
      // a bot fills this in, but a human never will, since it's
      // a hidden field
      return __('You are a bad boy','eme');
   } 

   if ($registration_wp_users_only && is_user_logged_in()) {
      // we require a user to be WP registered to be able to book
      get_currentuserinfo();
      $booker_wp_id=$current_user->ID;
      // we also need name and email for sending the mail
      $bookerName = $current_user->display_name;
      $bookerEmail = $current_user->user_email;
      $booker = eme_get_person_by_wp_info($bookerName, $bookerEmail,$booker_wp_id);
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
function eme_book_seats($event, $send_mail=1) {
   global $current_user;
   $booking_id = 0;

   // check for spammers as early as possible
   if (isset($_POST['honeypot_check'])) {
      $honeypot_check = stripslashes($_POST['honeypot_check']);
   } elseif (!is_admin() && !isset($_POST['honeypot_check'])) {
      $honeypot_check = "bad boy";
   } else {
      $honeypot_check = "";
   }

   if (!is_admin() && get_option('eme_captcha_for_booking')) {
      $captcha_err = response_check_captcha("captcha_check","eme_add_booking");
   } else {
      $captcha_err = "";
   }

   if (!is_admin() && (! isset( $_POST['eme_rsvp_nonce'] ) ||
       ! wp_verify_nonce( $_POST['eme_rsvp_nonce'], 'add_booking' ))) {
      $nonce_err = "bad boy";
   } else {
      $nonce_err = "";
   }

   if(!empty($captcha_err)) {
      $result = __('You entered an incorrect code','eme');
      return array(0=>$result,1=>$booking_id);
   } elseif (!empty($honeypot_check) ||  !empty($nonce_err)) {
      // a bot fills this in, but a human never will, since it's
      // a hidden field
      $result = __('You are a bad boy','eme');
      return array(0=>$result,1=>$booking_id);
   } 


   // now do regular checks

   $all_required_fields=eme_find_required_formfields($event['event_registration_form_format']);
   $deprecated=get_option('eme_deprecated');
   $min_allowed = $event['event_properties']['min_allowed'];
   $max_allowed = $event['event_properties']['max_allowed'];

   if (isset($_POST['bookedSeats']))
      $bookedSeats = intval($_POST['bookedSeats']);
   else
      $bookedSeats = 0;

   // for multiple prices, we have multiple booked Seats as well
   // the next foreach is only valid when called from the frontend
   $bookedSeats_mp = array();
   if (eme_is_multi($event['price'])) {
      // make sure the array contains the correct keys already, since
      // later on in the function eme_record_booking we do a join
      $booking_prices_mp=eme_convert_multi2array($event['price']);
      foreach ($booking_prices_mp as $key=>$value) {
         $bookedSeats_mp[$key] = 0;
      }
      foreach($_POST as $key=>$value) {
         if (preg_match('/bookedSeats(\d+)/', $key, $matches)) {
            $field_id = intval($matches[1])-1;
            $bookedSeats += $value;
            $bookedSeats_mp[$field_id]=$value;
         }
      }
   }

   if (isset($_POST['bookerPhone']))
      $bookerPhone = eme_strip_tags($_POST['bookerPhone']); 
   else
      $bookerPhone = "";

   if (isset($_POST['bookerComment']))
      $bookerComment = eme_strip_tags($_POST['bookerComment']);
   else
      $bookerComment = "";

   $missing_required_fields=array();
   // check all required fields
   if (!is_admin()) {
      foreach ($all_required_fields as $required_field) {
         if (preg_match ("/NAME|EMAIL|SEATS/",$required_field)) {
            // we already check these seperately, and EMAIL regex also catches _HTML5_EMAIL
            continue;
         } elseif (preg_match ("/PHONE/",$required_field)) {
            // PHONE regex also catches _HTML5_PHONE
            if (empty($bookerPhone)) array_push($missing_required_fields, __('Phone number','eme'));
         } elseif (preg_match ("/COMMENT/",$required_field)) {
            if (empty($bookerComment)) array_push($missing_required_fields, __('Comment','eme'));
         } elseif (!isset($_POST[$required_field]) || empty($_POST[$required_field])) {
            if (preg_match('/FIELD(.+)/', $required_field, $matches)) {
               $field_id = intval($matches[1]);
               $formfield = eme_get_formfield_byid($field_id);
               array_push($missing_required_fields, $formfield['field_name']);
            } else {
               array_push($missing_required_fields, $required_field);
            }
         }
      }
   }

   $event_id = $event['event_id'];
   $registration_wp_users_only=$event['registration_wp_users_only'];
   if (!is_admin() && $registration_wp_users_only && is_user_logged_in()) {
      // we require a user to be WP registered to be able to book
      get_currentuserinfo();
      $booker_wp_id=$current_user->ID;
      // we also need name and email for sending the mail
      $bookerName = $current_user->display_name;
      $bookerEmail = $current_user->user_email;
      $booker = eme_get_person_by_wp_info($bookerName, $bookerEmail,$booker_wp_id);
   } elseif (!is_admin() && is_user_logged_in()) {
      $booker_wp_id=get_current_user_id();
      $bookerName = eme_strip_tags($_POST['bookerName']);
      $bookerEmail = eme_strip_tags($_POST['bookerEmail']);
      $booker = eme_get_person_by_name_and_email($bookerName, $bookerEmail); 
   } else {
      // when called from the admin backend, we don't care about registration_wp_users_only
      $booker_wp_id=0;
      $bookerName = eme_strip_tags($_POST['bookerName']);
      $bookerEmail = eme_strip_tags($_POST['bookerEmail']);
      $booker = eme_get_person_by_name_and_email($bookerName, $bookerEmail); 
   }
   
   if (!$bookerName) {
      // if any required field is empty: return an error
      $result = __('Please fill out your name','eme');
   } elseif (!$bookerEmail) {
      // if any required field is empty: return an error
      $result = __('Please fill out your e-mail','eme');
   } elseif (count($missing_required_fields)>0) {
      // if any required field is empty: return an error
      $missing_required_fields_string=join(", ",$missing_required_fields);
      $result = sprintf(__('Please make sure all of the following required fields are filled out correctly: %s','eme'),$missing_required_fields_string);
   } elseif (!filter_var($bookerEmail,FILTER_VALIDATE_EMAIL)) {
      $result = __('Please enter a valid mail address','eme');
   } elseif (!eme_is_multi($min_allowed) && $bookedSeats < $min_allowed) {
      $result = __('Please enter a correct number of spaces to reserve','eme');
   } elseif (eme_is_multi($min_allowed) && eme_is_multi($event['event_seats']) && $bookedSeats_mp < eme_convert_multi2array($min_allowed)) {
      $result = __('Please enter a correct number of spaces to reserve','eme');
   } elseif (!eme_is_multi($max_allowed) && $max_allowed>0 && $bookedSeats>$max_allowed) {
      // we check the max, but only is max_allowed>0, max_allowed=0 means no limit
      $result = __('Please enter a correct number of spaces to reserve','eme');
   } elseif (eme_is_multi($max_allowed) && eme_is_multi($event['event_seats']) && eme_get_multitotal($max_allowed)>0 && $bookedSeats_mp >  eme_convert_multi2array($max_allowed)) {
      // we check the max, but only is the total max_allowed>0, max_allowed=0 means no limit
      // currently we don't support 0 as being no limit per array element
      $result = __('Please enter a correct number of spaces to reserve','eme');
   } elseif (!is_admin() && $registration_wp_users_only && !$booker_wp_id) {
      // spammers might get here, but we catch them
      $result = __('WP membership is required for registration','eme');
   } else {
      $language=eme_detect_lang();
      if (eme_is_multi($event['event_seats']))
         $seats_available=eme_are_multiseats_available_for($event_id, $bookedSeats_mp);
      else
         $seats_available=eme_are_seats_available_for($event_id, $bookedSeats);
      if ($seats_available) {
         if (!$booker) {
            $booker = eme_add_person($bookerName, $bookerEmail, $bookerPhone, $booker_wp_id,$language);
         }

         // ok, just to be safe: check the person_id of the booker
         if ($booker['person_id']>0) {
            // we can only use the filter here, since the booker needs to be created first if needed
            if (has_filter('eme_eval_booking_form_filter'))
               $eval_filter_return=apply_filters('eme_eval_booking_form_filter',$event,$booker);
            else
               $eval_filter_return=array(0=>1,1=>'');
            if (is_array($eval_filter_return) && !$eval_filter_return[0]) {
               // the result of own eval rules failed, so let's use that as a result
               $result = $eval_filter_return[1];
            } else {
               // if the user enters a new phone number, update it
               if ($booker['person_phone'] != $bookerPhone) {
                  eme_update_phone($booker,$bookerPhone);
               }
               $booking_id=eme_record_booking($event, $booker['person_id'], $bookedSeats,$bookedSeats_mp,$bookerComment,$language);
               $booking = eme_get_booking ($booking_id);
               $format = ( $event['event_registration_recorded_ok_html'] != '' ) ? $event['event_registration_recorded_ok_html'] : get_option('eme_registration_recorded_ok_html' );
               // don't let eme_replace_placeholders replace other shortcodes yet, let eme_replace_booking_placeholders finish and that will do it
               $result = eme_replace_placeholders($format, $event, "html", 0);
               $result = eme_replace_booking_placeholders($result, $event, $booking);
               if (is_admin()) {
                  $action="approveRegistration";
               } else {
                  $action="";
               }
               if ($send_mail) eme_email_rsvp_booking($booking_id,$action);

               // everything ok, so we unset the variables entered, so when the form is shown again, all is defaulted again
               foreach($_POST as $key=>$value) {
                  unset($_POST[$key]);
               }
            }
         } else {
            $result = __('No booker ID found, something is wrong here','eme');
            unset($_POST['bookedSeats']);
         }
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
   // for older bookings, the booking_price field might be empty
   if ($result['booking_price']==="")
      $result['booking_price'] = eme_get_event_price($result['event_id']);
   return $result;
}

function eme_get_event_price($event_id) {
   global $wpdb; 
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT price FROM $events_table WHERE event_id =%d",$event_id);
   $result = $wpdb->get_var($sql);
   return $result;
   }

function eme_get_bookings_by_person_id($person_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $bookings_table WHERE person_id = %d",$person_id);
   $result = $wpdb->get_results($sql, ARRAY_A);
   return $result;
}

function eme_get_booking_by_person_event_id($person_id,$event_id) {
   return eme_get_booking_ids_by_person_event_id($person_id,$event_id);
}
function eme_get_booking_ids_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = $wpdb->prepare("SELECT booking_id FROM $bookings_table WHERE person_id = %d AND event_id = %d",$person_id,$event_id);
   $result = $wpdb->get_col($sql);
   return $result;
}

function eme_get_booking_ids_by_wp_id($wp_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = $wpdb->prepare("SELECT booking_id FROM $bookings_table WHERE wp_id = %d AND event_id = %d",$wp_id,$event_id);
   $result = $wpdb->get_col($sql);
   return $result;
}

function eme_get_booked_seats_by_wp_event_id($wp_id,$event_id) {
   global $wpdb;
   if (eme_is_event_multiseats($event_id))
      return array_sum(eme_get_booked_multiseats_by_wp_event_id($wp_id,$event_id));
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE wp_id = %d AND event_id = %d",$wp_id,$event_id);
   return $wpdb->get_var($sql);
}

function eme_get_booked_multiseats_by_wp_event_id($wp_id,$event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT booking_seats_mp FROM $bookings_table WHERE event_id = $event_id"; 
   $sql = $wpdb->prepare("SELECT booking_seats_mp FROM $bookings_table WHERE wp_id = %d AND event_id = %d",$wp_id,$event_id);
   $booking_seats_mp = $wpdb->get_col($sql);
   $result=array();
   foreach($booking_seats_mp as $booked_seats) {
      $multiseats = eme_convert_multi2array($booked_seats);
      foreach ($multiseats as $key=>$value) {
         if (!isset($result[$key]))
            $result[$key]=$value;
         else
            $result[$key]+=$value;
      }
   }
   return $result;
}

function eme_get_booked_seats_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   if (eme_is_event_multiseats($event_id))
      return array_sum(eme_get_booked_multiseats_by_person_event_id($person_id,$event_id));
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE person_id = %d AND event_id = %d",$person_id,$event_id);
   return $wpdb->get_var($sql);
}

function eme_get_booked_multiseats_by_person_event_id($person_id,$event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT booking_seats_mp FROM $bookings_table WHERE event_id = $event_id"; 
   $sql = $wpdb->prepare("SELECT booking_seats_mp FROM $bookings_table WHERE person_id = %d AND event_id = %d",$person_id,$event_id);
   $booking_seats_mp = $wpdb->get_col($sql);
   $result=array();
   foreach($booking_seats_mp as $booked_seats) {
      $multiseats = eme_convert_multi2array($booked_seats);
      foreach ($multiseats as $key=>$value) {
         if (!isset($result[$key]))
            $result[$key]=$value;
         else
            $result[$key]+=$value;
      }
   }
   return $result;
}

function eme_get_event_id_by_booking_id($booking_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT DISTINCT event_id FROM $bookings_table WHERE booking_id = %d",$booking_id);
   $result = $wpdb->get_var($sql);
   return $result;
}

function eme_get_event_ids_by_booker_id($person_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT DISTINCT event_id FROM $bookings_table WHERE person_id = %d",$person_id);
   $result = $wpdb->get_col($sql);
   return $result;
}

function eme_record_booking($event, $person_id, $seats, $seats_mp, $comment, $lang) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $person_id = intval($person_id);
   $seats = intval($seats);
   // sanitize not needed: wpdb->insert does it already
   //$comment = eme_sanitize_request($comment);
   $booking['event_id']=$event['event_id'];
   $booking['person_id']=$person_id;
   $booking['wp_id']=get_current_user_id();
   $booking['booking_seats']=$seats;
   $booking['booking_seats_mp']=eme_convert_array2multi($seats_mp);
   $booking['booking_price']=$event['price'];
   $booking['booking_comment']=$comment;
   $booking['lang']=$lang;
   $booking['creation_date']=current_time('mysql', false);
   $booking['modif_date']=current_time('mysql', false);
   $booking['creation_date_gmt']=current_time('mysql', true);
   $booking['modif_date_gmt']=current_time('mysql', true);
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

      // we insert the booking in the DB, then calc the transfer_nbr for it based on the new booking id
      if ($wpdb->insert($bookings_table,$booking)) {
         $booking_id = $wpdb->insert_id;
         $booking['booking_id'] = $booking_id;
         $booking['transfer_nbr_be97'] = eme_transfer_nbr_be97($booking_id);
         $where = array();
         $fields = array();
         $where['booking_id'] = $booking_id;
         $fields['transfer_nbr_be97'] = $booking['transfer_nbr_be97'];
         $wpdb->update($bookings_table, $fields, $where);
         eme_record_answers($booking_id);
         // now that everything is (or should be) correctly entered in the db, execute possible actions for the new booking
         if (has_action('eme_insert_rsvp_action')) do_action('eme_insert_rsvp_action',$booking);
         return $booking['booking_id'];
      } else {
         return false;
      }
// }
}

function eme_record_answers($booking_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME; 
   foreach($_POST as $key =>$value) {
		if (preg_match('/FIELD(.+)/', $key, $matches)) { 
         $field_id = intval($matches[1]);
         $formfield = eme_get_formfield_byid($field_id);
         // for multivalue fields like checkbox, the value is in fact an array
         // to store it, we make it a simple "multi" string using eme_convert_array2multi, so later on when we need to parse the values 
         // (when editing a booking), we can re-convert it to an array with eme_convert_multi2array (see eme_formfields.php)
         if (is_array($value)) $value=eme_convert_array2multi($value);
         $sql = $wpdb->prepare("INSERT INTO $answers_table (booking_id,field_name,answer) VALUES (%d,%s,%s)",$booking_id,$formfield['field_name'],stripslashes($value));
         $wpdb->query($sql);
      }
   }
}

function eme_get_answers($booking_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME; 
   $sql = $wpdb->prepare("SELECT * FROM $answers_table WHERE booking_id=%d",$booking_id);
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_delete_answers($booking_id) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME; 
   $sql = $wpdb->prepare("DELETE FROM $answers_table WHERE booking_id=%d",$booking_id);
   $wpdb->query($sql);
}

function eme_convert_answer2tag($answer) {
   $formfield=eme_get_formfield_byname($answer['field_name']);
   $field_info=$formfield['field_info'];
   $field_tags=$formfield['field_tags'];

   if (!empty($field_tags) && eme_is_multifield($formfield['field_type'])) {
      $answers = eme_convert_multi2array($answer['answer']);
      $values = eme_convert_multi2array($field_info);
      $tags = eme_convert_multi2array($field_tags);
      $my_arr = array();
      foreach ($answers as $ans) {
         foreach ($values as $key=>$val) {
            if ($val==$ans) {
               $my_arr[]=$tags[$key];
            }
         }
      }
      return eme_convert_array2multi($my_arr);
   } else {
      return $answer['answer'];
   }
} 

function eme_get_answercolumns($booking_ids) {
   global $wpdb;
   $answers_table = $wpdb->prefix.ANSWERS_TBNAME; 
   $sql = "SELECT DISTINCT field_name FROM $answers_table WHERE booking_id IN (".join(",",$booking_ids).")";
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_delete_all_bookings_for_person_id($person_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "DELETE FROM $bookings_table WHERE person_id = $person_id";
   $wpdb->query($sql);
   return 1;
}

function eme_transfer_all_bookings($person_id,$to_person_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $where = array();
   $fields = array();
   $where['person_id'] = $person_id;
   $fields['person_id'] = $to_person_id;
   $fields['modif_date']=current_time('mysql', false);
   $fields['modif_date_gmt']=current_time('mysql', true);
   return $wpdb->update($bookings_table, $fields, $where);
}

function eme_delete_booking_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = $wpdb->prepare("DELETE FROM $bookings_table WHERE person_id = %d AND event_id= %d",$person_id,$event_id);
   return $wpdb->query($sql);
}

function eme_delete_booking($booking_id) {
   global $wpdb;
   // first delete all the answers
   eme_delete_answers($booking_id);
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = $wpdb->prepare("DELETE FROM $bookings_table WHERE booking_id = %d",$booking_id);
   return $wpdb->query($sql);
}

function eme_update_booking_payed($booking_id,$booking_payed,$approve_pending=0) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   
   $where = array();
   $fields = array();
   $where['booking_id'] = intval($booking_id);
   $fields['booking_payed'] = intval($booking_payed) ;
   $fields['modif_date']=current_time('mysql', false);
   $fields['modif_date_gmt']=current_time('mysql', true);
   if ($booking_payed==1 && $approve_pending == 1)
      $fields['booking_approved'] = 1;
   $res = $wpdb->update($bookings_table, $fields, $where);
   if ($res && $approve_pending == 1 && $booking_payed==1)
      eme_email_rsvp_booking($booking_id,"approveRegistration");
   return $res;
   
}

function eme_approve_booking($booking_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 

   $where = array();
   $fields = array();
   $where['booking_id'] = $booking_id;
   $fields['booking_approved'] = 1;
   $fields['modif_date']=current_time('mysql', false);
   $fields['modif_date_gmt']=current_time('mysql', true);
   return $wpdb->update($bookings_table, $fields, $where);
   //$sql = "UPDATE $bookings_table SET booking_approved='1' WHERE booking_id = $booking_id";
   //$wpdb->query($sql);
   //return __('Booking approved', 'eme');
}

function eme_update_booking($booking_id,$event_id,$seats,$booking_price,$comment="") {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $where = array();
   $fields = array();
   $where['booking_id'] =$booking_id;

   # if it is a multi-price event, the total number of seats is the sum of the other ones
   if (eme_is_multi($booking_price)) {
      $fields['booking_seats']=0;
      # make sure the correct amount of seats is defined for multiprice
      $booking_prices_mp=eme_convert_multi2array($booking_price);
      $booking_seats_mp=eme_convert_multi2array($seats);
      foreach ($booking_prices_mp as $key=>$value) {
         if (!isset($booking_seats_mp[$key]))
            $booking_seats_mp[$key] = 0;
         $fields['booking_seats'] += intval($booking_seats_mp[$key]);
      }
      $fields['booking_seats_mp'] = eme_convert_array2multi($booking_seats_mp);
   } else {
      $fields['booking_seats'] = intval($seats);
   }
   $fields['booking_comment']=$comment;
   $fields['modif_date']=current_time('mysql', false);
   $fields['modif_date_gmt']=current_time('mysql', true);
   $returncode=$wpdb->update($bookings_table, $fields, $where);
   if ($returncode) {
      eme_delete_answers($booking_id);
      eme_record_answers($booking_id);
   }
   // now that everything is (or should be) correctly entered in the db, execute possible actions for the booking
   if (has_action('eme_update_rsvp_action')) {
      $booking=eme_get_booking($booking_id);
      do_action('eme_update_rsvp_action',$booking);
   }
   return $returncode;
}

function eme_get_available_seats($event_id) {
   $event = eme_get_event($event_id);
   if (eme_is_multi($event['event_seats']))
      return array_sum(eme_get_available_multiseats($event_id));

   if ($event['event_properties']['ignore_pending'] == 1)
      $available_seats = $event['event_seats'] - eme_get_approved_seats($event_id);
   else
      $available_seats = $event['event_seats'] - eme_get_booked_seats($event_id);
   // the number of seats left can be <0 if more than one booking happened at the same time and people fill in things slowly
   if ($available_seats<0) $available_seats=0;
   return $available_seats;
}

function eme_get_available_multiseats($event_id) {
   $event = eme_get_event($event_id);
   $multiseats = eme_convert_multi2array($event['event_seats']);
   $available_seats=array();
   if ($event['event_properties']['ignore_pending'] == 1) {
      $used_multiseats=eme_get_approved_multiseats($event_id);
   } else {
      $used_multiseats=eme_get_booked_multiseats($event_id);
   }
   foreach ($multiseats as $key=>$value) {
      if (isset($used_multiseats[$key]))
         $available_seats[$key] = $value - $used_multiseats[$key];
      else
         $available_seats[$key] = $value;
      // the number of seats left can be <0 if more than one booking happened at the same time and people fill in things slowly
      if ($available_seats[$key]<0) $available_seats[$key]=0;
   }
   return $available_seats;
}

function eme_get_booked_seats($event_id) {
   global $wpdb; 
   if (eme_is_event_multiseats($event_id))
      return array_sum(eme_get_booked_multiseats($event_id));
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE event_id = %d",$event_id);
   return $wpdb->get_var($sql);
}

function eme_get_booked_multiseats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT booking_seats_mp FROM $bookings_table WHERE event_id = %d",$event_id);
   $booking_seats_mp = $wpdb->get_col($sql);
   $result=array();
   foreach($booking_seats_mp as $booked_seats) {
      $multiseats = eme_convert_multi2array($booked_seats);
      foreach ($multiseats as $key=>$value) {
         if (!isset($result[$key]))
            $result[$key]=$value;
         else
            $result[$key]+=$value;
      }
   }
   return $result;
}

function eme_get_approved_seats($event_id) {
   global $wpdb; 
   if (eme_is_event_multiseats($event_id))
      return array_sum(eme_get_approved_multiseats($event_id));
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE event_id = %d and booking_approved=1",$event_id);
   return $wpdb->get_var($sql);
}

function eme_get_approved_multiseats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT booking_seats_mp FROM $bookings_table WHERE event_id = %d and booking_approved=1",$event_id);
   $booking_seats_mp = $wpdb->get_col($sql);
   $result=array();
   foreach($booking_seats_mp as $booked_seats) {
      $multiseats = eme_convert_multi2array($booked_seats);
      foreach ($multiseats as $key=>$value) {
         if (!isset($result[$key]))
            $result[$key]=$value;
         else
            $result[$key]+=$value;
      }
   }
   return $result;
}

function eme_get_pending_seats($event_id) {
   global $wpdb; 
   if (eme_is_event_multiseats($event_id))
      return array_sum(eme_get_pending_multiseats($event_id));
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE event_id = %d and booking_approved=0",$event_id);
   return $wpdb->get_var($sql);
}

function eme_get_pending_multiseats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT booking_seats_mp FROM $bookings_table WHERE event_id = %d and booking_approved=0",$event_id);
   $booking_seats_mp = $wpdb->get_col($sql);
   $result=array();
   foreach($booking_seats_mp as $booked_seats) {
      $multiseats = eme_convert_multi2array($booked_seats);
      foreach ($multiseats as $key=>$value) {
         if (!isset($result[$key]))
            $result[$key]=$value;
         else
            $result[$key]+=$value;
      }
   }
   return $result;
}

function eme_get_pending_bookings($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT COUNT(*) AS pending_bookings FROM $bookings_table WHERE event_id = $event_id and booking_approved=0"; 
   $sql = $wpdb->prepare("SELECT COUNT(*) AS pending_bookings FROM $bookings_table WHERE event_id = %d and booking_approved=0",$event_id);
   return $wpdb->get_var($sql);
}

function eme_are_seats_available_for($event_id, $seats) {
   $available_seats = eme_get_available_seats($event_id);
   $remaining_seats = $available_seats - $seats;
   return ($remaining_seats >= 0);
} 

function eme_are_multiseats_available_for($event_id, $multiseats) {
   $available_seats = eme_get_available_multiseats($event_id);
   foreach ($available_seats as $key=> $value) {
   	$remaining_seats = $value - $multiseats[$key];
	if ($remaining_seats<0)
		return 0;
   }
   return 1;
} 
 
function eme_bookings_compact_table($event_id) {
   $bookings =  eme_get_bookings_for($event_id);
   $destination = admin_url("edit.php"); 
   $available_seats = eme_get_available_seats($event_id);
   $approved_seats = eme_get_approved_seats($event_id);
   $pending_seats = eme_get_pending_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   if (eme_is_event_multiseats($event_id)) {
	   $available_seats_ms=eme_convert_array2multi(eme_get_available_multiseats($event_id));
	   $approved_seats_ms=eme_convert_array2multi(eme_get_approved_multiseats($event_id));
	   $booked_seats_ms=eme_convert_array2multi(eme_get_booked_multiseats($event_id));
	   $pending_seats_ms=eme_convert_array2multi(eme_get_pending_multiseats($event_id));
	   if ($pending_seats>0) {
		   $booked_seats_info="$booked_seats: $booked_seats_ms ($approved_seats_ms ".__('approved','eme').", $pending_seats_ms ".__('pending','eme');
	   } else {
	      $booked_seats_info="$booked_seats: $booked_seats_ms";
	   }
	   $available_seats_info="$available_seats: $available_seats_ms";
   } else {
	   if ($pending_seats>0) {
		   $booked_seats_info="$booked_seats ($approved_seats ".__('approved','eme').", $pending_seats ".__('pending','eme');
	   } else {
		   $booked_seats_info=$booked_seats;
	   }
	   $available_seats_info=$available_seats;
   }
   $count_bookings=count($bookings);
   if ($count_bookings>0) { 
      $printable_address = admin_url("/admin.php?page=eme-people&amp;eme_admin_action=booking_printable&amp;event_id=$event_id");
      $csv_address = admin_url("/admin.php?page=eme-people&amp;eme_admin_action=booking_csv&amp;event_id=$event_id");
      $table = 
      "<div class='wrap'>
            <h4>$count_bookings ".__('bookings so far','eme').":</h4>
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
                  <tr><th scope='row' colspan='2'>".__('Available spaces','eme').":</th><td class='booking-result' id='available-seats'>$available_seats_info</td>
                  </tr>
               </tfoot>
               <tbody>" ;
      foreach ($bookings as $booking) {
         $person  = eme_get_person ($booking['person_id']);
         ($booking['booking_comment']) ? $baloon = " <img src='".EME_PLUGIN_URL."images/baloon.png' title='".__('Comment:','eme')." ".$booking['booking_comment']."' alt='comment'/>" : $baloon = "";
         if (eme_is_event_multiprice($event_id))
            $booking_info = $booking['booking_seats'].': '.$booking['booking_seats_mp'];
         else
            $booking_info = $booking['booking_seats'];
         if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
            $booking_info.=" ".__('(pending)','eme');
         }
         $table .= 
         "<tr id='booking-".$booking['booking_id']."'> 
            <td><a id='booking-check-".$booking['booking_id']."' class='bookingdelbutton'>X</a></td>
            <td><a title=\"".eme_sanitize_html($person['person_email'])." - ".eme_sanitize_html($person['person_phone'])."\">".eme_sanitize_html($person['person_name'])."</a>$baloon</td>
            <td>$booking_info</td>
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

function eme_get_bookingids_for($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT booking_id FROM $bookings_table WHERE event_id=%d",$event_id);
   return $wpdb->get_col($sql);
}

function eme_get_bookings_for($event_ids,$pending_approved=0,$only_unpayed=0) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   
   $bookings = array();
   if (!$event_ids)
      return $bookings;
   
   if (is_array($event_ids)) {
      $where="event_id IN (".join(",",$event_ids).")";
   } else {
      $where="event_id = $event_ids";
   }
   $sql = "SELECT * FROM $bookings_table WHERE $where";
   if ($pending_approved==1) {
      $sql .= " AND booking_approved=0";
   } elseif ($pending_approved==2) {
      $sql .= " AND booking_approved=1";
   }
   if ($only_unpayed) {
      $sql .= " AND booking_payed=0";
   }
   return $wpdb->get_results($sql, ARRAY_A);
}

function eme_get_attendees_for($event_id,$pending_approved=0,$only_unpayed=0) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = $wpdb->prepare("SELECT DISTINCT person_id FROM $bookings_table WHERE event_id = %s",$event_id);
   if ($pending_approved==1) {
      $sql .= " AND booking_approved=0";
   } elseif ($pending_approved==2) {
      $sql .= " AND booking_approved=1";
   }
   if ($only_unpayed) {
      $sql .= " AND booking_payed=0";
   }

   $person_ids = $wpdb->get_col($sql);
   if ($person_ids) {
      $attendees = eme_get_persons($person_ids);
   } else {
      $attendees= array();
   }
   return $attendees;
}

function eme_get_attendees_list_for($event,$template_id=0,$template_id_header=0,$template_id_footer=0) {
   $attendees = eme_get_attendees_for($event['event_id']);
   $format=get_option('eme_attendees_list_format');
   $eme_format_header="<ul class='eme_bookings_list_ul'>";
   $eme_format_footer="</ul>";

   // rsvp not active or no rsvp for this event, then return
   if (!eme_is_event_rsvp($event)) {
      return;
   }
   
   if ($template_id) {
      $format_arr = eme_get_template($template_id);
      $format=$format_arr['format'];
   }

   // header and footer can't contain per booking info, so we don't replace booking placeholders there
   if ($template_id_header) {
      $format_arr = eme_get_template($template_id_header);
      $format_header = $format_arr['format'];
      $eme_format_header=eme_replace_placeholders($format_header, $event);
   }
   if ($template_id_footer) {
      $format_arr = eme_get_template($template_id_footer);
      $format_footer = $format_arr['format'];
      $eme_format_footer=eme_replace_placeholders($format_footer, $event);
   }

   if ($attendees) {
      $res=$eme_format_header;
      // don't let eme_replace_placeholders replace other shortcodes yet, let eme_replace_attendees_placeholders finish and that will do it
      $format = eme_replace_placeholders($format, $event, "html", 0);
      foreach ($attendees as $attendee) {
         $res.=eme_replace_attendees_placeholders($format,$event,$attendee);
      }
      $res.=$eme_format_footer;
   } else {
      $res="<p class='eme_no_bookings'>".__('No responses yet!','eme')."</p>";
   }
   return $res;
}

function eme_get_bookings_list_for($event,$template_id=0,$template_id_header=0,$template_id_footer=0) {
   global $wpdb; 
   $bookings=eme_get_bookings_for($event['event_id']);
   $format=get_option('eme_bookings_list_format');
   $eme_format_header=get_option('eme_bookings_list_header_format');
   $eme_format_footer=get_option('eme_bookings_list_footer_format');

   // rsvp not active or no rsvp for this event, then return
   if (!eme_is_event_rsvp($event)) {
      return;
   }
   
   if ($template_id) {
      $format_arr = eme_get_template($template_id);
      $format=$format_arr['format'];
   }

   // header and footer can't contain per booking info, so we don't replace booking placeholders there
   if ($template_id_header) {
      $format_arr = eme_get_template($template_id_header);
      $format_header = $format_arr['format'];
      $eme_format_header=eme_replace_placeholders($format_header, $event);
   }
   if ($template_id_footer) {
      $format_arr = eme_get_template($template_id_footer);
      $format_footer = $format_arr['format'];
      $eme_format_footer=eme_replace_placeholders($format_footer, $event);
   }

   if ($bookings) {
      $res=$eme_format_header;
      // don't let eme_replace_placeholders replace other shortcodes yet, let eme_replace_booking_placeholders finish and that will do it
      $format = eme_replace_placeholders($format, $event, "html", 0);
      foreach ($bookings as $booking) {
         $res.= eme_replace_booking_placeholders($format,$event,$booking);
      }
      $res.=$eme_format_footer;
   } else {
      $res="<p class='eme_no_bookings'>".__('No responses yet!','eme')."</p>";
   }
   return $res;
}

function eme_replace_booking_placeholders($format, $event, $booking, $target="html",$lang='') {
   $deprecated=get_option('eme_deprecated');

   preg_match_all("/#(ESC)?_?[A-Za-z0-9_]+(\{[A-Za-z0-9_]+\})?/", $format, $placeholders);
   $person  = eme_get_person ($booking['person_id']);
   $answers = eme_get_answers($booking['booking_id']);

   usort($placeholders[0],'sort_stringlenth');
   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      $need_escape=0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      }
      if (preg_match('/#_RESP(NAME|PHONE|ID|EMAIL)/', $result)) {
         $field = preg_replace("/#_RESP/","",$result);
         $field = "person_".strtolower($field);
         $replacement = $person[$field];
         $replacement = eme_sanitize_html($replacement);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 
      } elseif (preg_match('/#_(RESPCOMMENT|COMMENT)/', $result)) {
         $replacement = $booking['booking_comment'];
         $replacement = eme_sanitize_html($replacement);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 
      } elseif (($deprecated && preg_match('/#_(RESPSPACES|SPACES|BOOKEDSEATS)(\d+)/', $result, $matches)) ||
                preg_match('/#_(RESPSPACES|SPACES|BOOKEDSEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         if (eme_is_multi($booking['booking_price'])) {
             $seats=eme_convert_multi2array($booking['booking_seats_mp']);
             if (array_key_exists($field_id,$seats))
                $replacement = $seats[$field_id];
         }
      } elseif (preg_match('/#_TOTALPRICE$/', $result)) {
         $replacement = eme_get_total_booking_price($event,$booking);
      } elseif (preg_match('/#_TOTALPRICE\{(\d+)\}/', $result, $matches)) {
         // total price to pay per price if multiprice
         $total_prices=eme_get_total_booking_multiprice($event,$booking);
         $field_id = intval($matches[1])-1;
         if (array_key_exists($field_id,$total_prices))
            $replacement = $total_prices[$field_id];
       } elseif ($deprecated && preg_match('/#_TOTALPRICE(\d+)/', $result, $matches)) {
         // total price to pay per price if multiprice
         $total_prices=eme_get_total_booking_multiprice($event,$booking);
         $field_id = intval($matches[1])-1;
         if (array_key_exists($field_id,$total_prices))
            $replacement = $total_prices[$field_id];
      } elseif (preg_match('/#_RESPSPACES$|#_SPACES$|#_BOOKEDSEATS$/', $result)) {
         $replacement = eme_get_multitotal($booking['booking_seats']);
      } elseif (preg_match('/#_USER_(RESERVEDSPACES|BOOKEDSEATS)/', $result)) {
         $replacement = eme_get_multitotal($booking['booking_seats']);
      } elseif (preg_match('/#_BOOKINGCREATIONDATE/', $result)) {
         $replacement = eme_localised_date($booking['creation_date']);
      } elseif (preg_match('/#_BOOKINGMODIFDATE/', $result)) {
         $replacement = eme_localised_date($booking['modif_date']);
      } elseif (preg_match('/#_BOOKINGCREATIONTIME/', $result)) {
         $replacement = eme_localised_time($booking['creation_date']);
      } elseif (preg_match('/#_BOOKINGMODIFTIME/', $result)) {
         $replacement = eme_localised_time($booking['modif_date']);
      } elseif (preg_match('/#_BOOKINGID/', $result)) {
         $replacement = $booking['booking_id'];
      } elseif (preg_match('/#_TRANSFER_NBR_BE97/', $result)) {
         $replacement = $booking['transfer_nbr_be97'];
      } elseif (preg_match('/#_PAYMENT_URL/', $result)) {
         $replacement = eme_payment_url($booking['booking_id']);
      } elseif (preg_match('/#_FIELDS/', $result)) {
         $field_replace = "";
         foreach ($answers as $answer) {
            $field_replace.=$answer['field_name'].": ".eme_convert_answer2tag($answer)."\n";
         }
         $replacement = eme_trans_sanitize_html($field_replace,$lang);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 
      } elseif (preg_match('/#_PAYED/', $result)) {
         $replacement = ($booking['booking_payed'])? __('Yes') : __('No');
      } elseif (($deprecated && preg_match('/#_FIELDNAME(\d+)/', $result, $matches)) ||
                preg_match('/#_FIELDNAME\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[1]);
         $formfield = eme_get_formfield_byid($field_id);
         $replacement = eme_trans_sanitize_html($formfield['field_name'],$lang);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 
      } elseif (($deprecated && preg_match('/#_FIELD(\d+)/', $result, $matches)) ||
                preg_match('/#_FIELD\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[1]);
         $formfield = eme_get_formfield_byid($field_id);
         foreach ($answers as $answer) {
            if ($answer['field_name'] == $formfield['field_name'])
               $replacement = eme_trans_sanitize_html(eme_convert_answer2tag($answer),$lang);
         }
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 
      } else {
         $found = 0;
      }

      if ($found) {
         if ($need_escape)
            $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   // now, replace any language tags found in the format itself
   $format = eme_translate($format,$lang);

   return do_shortcode($format);   
}

function eme_replace_attendees_placeholders($format, $event, $attendee, $target="html", $lang='') {
   preg_match_all("/#_?[A-Za-z0-9_]+/", $format, $placeholders);

   usort($placeholders[0],'sort_stringlenth');
   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      $orig_result = $result;
      if (preg_match('/#_(ATTEND)?(NAME|PHONE|ID|EMAIL)/', $result)) {
         $field = preg_replace("/#_ATTEND|#_/","",$result);
         $field = "person_".strtolower($field);
         $replacement = $attendee[$field];
         $replacement = eme_sanitize_html($replacement);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 

      } elseif (preg_match('/#_USER_(RESERVEDSPACES|BOOKEDSEATS)/', $result)) {
         $replacement = eme_get_booked_seats_by_person_event_id($attendee['person_id'],$event['event_id']);
      } elseif (preg_match('/#_ATTENDSPACES$/', $result)) {
         $replacement = eme_get_booked_seats_by_person_event_id($attendee['person_id'],$event['event_id']);
      } else {
         $found = 0;
      }
      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );
   }

   // now, replace any language tags found in the format itself
   $format = eme_translate($format,$lang);

   return do_shortcode($format);   
}

function eme_email_rsvp_booking($booking_id,$action="") {
   // first check if a mail should be send at all
   $mailing_is_active = get_option('eme_rsvp_mail_notify_is_active');
   if (!$mailing_is_active) {
      return;
   }

   $booking = eme_get_booking ($booking_id);

   $person = eme_get_person ($booking['person_id']);
   $event = eme_get_event($booking['event_id']);
   $event_name = $event['event_name'];
   $contact = eme_get_contact ($event);
   $contact_email = $contact->user_email;
   $contact_name = $contact->display_name;
   
   $contact_body = ( $event['event_contactperson_email_body'] != '' ) ? $event['event_contactperson_email_body'] : get_option('eme_contactperson_email_body' );
   $contact_body = eme_replace_placeholders($contact_body, $event, "text",0);
   $contact_body = eme_replace_booking_placeholders($contact_body, $event, $booking, "text");
   $confirmed_body = ( $event['event_respondent_email_body'] != '' ) ? $event['event_respondent_email_body'] : get_option('eme_respondent_email_body' );
   $confirmed_body = eme_replace_placeholders($confirmed_body, $event, "text",0,$booking['lang']);
   $confirmed_body = eme_replace_booking_placeholders($confirmed_body, $event, $booking, "text",$booking['lang']);
   $pending_body = ( $event['event_registration_pending_email_body'] != '' ) ? $event['event_registration_pending_email_body'] : get_option('eme_registration_pending_email_body' );
   $pending_body = eme_replace_placeholders($pending_body, $event, "text",0,$booking['lang']);
   $pending_body = eme_replace_booking_placeholders($pending_body, $event, $booking, "text",$booking['lang']);
   $denied_body = get_option('eme_registration_denied_email_body' );
   $denied_body = eme_replace_placeholders($denied_body, $event, "text",0,$booking['lang']);
   $denied_body = eme_replace_booking_placeholders($denied_body, $event, $booking, "text",$booking['lang']);
   $updated_body = ( $event['event_registration_updated_email_body'] != '' ) ? $event['event_registration_updated_email_body'] : get_option('eme_registration_updated_email_body' );
   $updated_body = eme_replace_placeholders($updated_body, $event, "text",0,$booking['lang']);
   $updated_body = eme_replace_booking_placeholders($updated_body, $event, $booking, "text",$booking['lang']);
   $cancelled_body = get_option('eme_registration_cancelled_email_body' );
   $cancelled_body = eme_replace_placeholders($cancelled_body, $event, "text",0,$booking['lang']);
   $cancelled_body = eme_replace_booking_placeholders($cancelled_body, $event, $booking, "text",$booking['lang']);
   $contact_cancelled_body = get_option('eme_contactperson_cancelled_email_body' );
   $contact_cancelled_body = eme_replace_placeholders($contact_cancelled_body, $event, "text",0,$booking['lang']);
   $contact_cancelled_body = eme_replace_booking_placeholders($contact_cancelled_body, $event, $booking, "text",$booking['lang']);
   $contact_pending_body = get_option('eme_contactperson_pending_email_body' );
   $contact_pending_body = eme_replace_placeholders($contact_pending_body, $event, "text",0,$booking['lang']);
   $contact_pending_body = eme_replace_booking_placeholders($contact_pending_body, $event, $booking, "text",$booking['lang']);

   // possible translations are handled last 
   $contact_body = eme_translate($contact_body); 
   $contact_cancelled_body = eme_translate($contact_cancelled_body); 
   $contact_pending_body = eme_translate($contact_pending_body); 
   $contact_event_name = eme_translate($event_name);  
   $confirmed_body = eme_translate($confirmed_body,$booking['lang']); 
   $updated_body = eme_translate($updated_body,$booking['lang']); 
   $pending_body = eme_translate($pending_body,$booking['lang']); 
   $denied_body = eme_translate($denied_body,$booking['lang']); 
   $cancelled_body = eme_translate($cancelled_body,$booking['lang']);  
   $event_name = eme_translate($event_name,$booking['lang']);  

   if ($action == 'approveRegistration') {
      eme_send_mail(sprintf(__("Reservation for '%s' confirmed",'eme'),$event_name),$confirmed_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
   } elseif ($action == 'denyRegistration') {
      eme_send_mail(sprintf(__("Reservation for '%s' denied",'eme'),$event_name),$denied_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
   } elseif ($action == 'updateRegistration') {
      eme_send_mail(sprintf(__("Reservation for '%s' updated",'eme'),$event_name),$updated_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
   } elseif ($action == 'cancelRegistration') {
      eme_send_mail(sprintf(__("Reservation for '%s' cancelled",'eme'),$event_name),$cancelled_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
      eme_send_mail(sprintf(__("A reservation has been cancelled for '%s'",'eme'),$event_name), $contact_cancelled_body, $contact_email, $contact_name, $contact_email, $contact_name);
   } elseif (empty($action)) {
      // send different mails depending on approval or not
      if ($event['registration_requires_approval']) {
         eme_send_mail(sprintf(__("Approval required for new booking for '%s'",'eme'),$event_name), $contact_pending_body, $contact_email, $contact_name, $contact_email, $contact_name);
         eme_send_mail(sprintf(__("Reservation for '%s' is pending",'eme'),$contact_event_name),$pending_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
      } else {
         eme_send_mail(sprintf(__("New booking for '%s'",'eme'),$contact_event_name), $contact_body, $contact_email,$contact_name, $contact_email, $contact_name);
         eme_send_mail(sprintf(__("Reservation for '%s' confirmed",'eme'),$event_name),$confirmed_body, $person['person_email'], $person['person_name'], $contact_email, $contact_name);
      }
   }
} 

function eme_registration_approval_page() {
   eme_registration_seats_page(1);
}

function eme_registration_seats_page($pending=0) {
   global $wpdb,$plugin_page;

   // do the actions if required
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "editRegistration" && isset($_GET['booking_id'])) {
      $booking_id = intval($_GET['booking_id']);
      $booking = eme_get_booking($booking_id);
      $event_id = $booking['event_id'];
      $event = eme_get_event($event_id);
      // we need to set the action url, otherwise the GET parameters stay and we will fall in this if-statement all over again
      $action_url = admin_url("admin.php?page=$plugin_page");
      $ret_string = "<form id='eme-rsvp-form' name='booking-form' method='post' action='$action_url'>";
      $ret_string.= __('Send mails for changed registration?','eme') . eme_ui_select_binary(1,"send_mail");
      $ret_string.= eme_replace_formfields_placeholders ($event,$booking);
      $ret_string .= "
         <input type='hidden' name='eme_admin_action' value='updateRegistration'/>
         <input type='hidden' name='booking_id' value='$booking_id'/>
         </form></div>";
      print $ret_string;
      return;
   } else {
      $action = isset($_POST ['eme_admin_action']) ? $_POST ['eme_admin_action'] : '';
      $send_mail = isset($_POST ['send_mail']) ? intval($_POST ['send_mail']) : 1;

      if ($action == 'newRegistration') {
         $event_id = intval($_POST['event_id']);
         $event = eme_get_event($event_id);
         $ret_string = "<form id='eme-rsvp-form' name='booking-form' method='post' action=''>";
         $ret_string.= __('Send mails for new registration?','eme') . eme_ui_select_binary(1,"send_mail");
         $ret_string.= eme_replace_formfields_placeholders ($event);
         $ret_string .= "
            <input type='hidden' name='eme_admin_action' value='addRegistration'/>
            <input type='hidden' name='event_id' value='$event_id'/>
            </form></div>";
         print $ret_string;
         return;

      } elseif ($action == 'addRegistration') {
         $event_id = intval($_POST['event_id']);
         $booking_payed = isset($_POST ['booking_payed']) ? intval($_POST ['booking_payed']) : 0;
         $event = eme_get_event($event_id);
         $booking_res = eme_book_seats($event, $send_mail);
         $result=$booking_res[0];
         $booking_id_done=$booking_res[1];
         if (!$booking_id_done) {
            print "<div id='message' class='error'><p>$result</p></div>";
         } else {
            print "<div id='message' class='updated'><p>$result</p></div>";
            eme_update_booking_payed($booking_id_done,$booking_payed);
         }
      } elseif ($action == 'updateRegistration') {
         $booking_id = intval($_POST['booking_id']);
         $booking = eme_get_booking ($booking_id);
         $deprecated=get_option('eme_deprecated');
         //$event_id = $booking['event_id'];
         //$event = eme_get_event($event_id);

         if (isset($_POST['bookerComment']))
            $bookerComment = eme_strip_tags($_POST['bookerComment']);
         else
            $bookerComment = "";

         if (isset($_POST['bookedSeats']))
            $bookedSeats = intval($_POST['bookedSeats']);
         else
            $bookedSeats = 0;

         // for multiple prices, we have multiple booked Seats as well
         // the next foreach is only valid when called from the frontend
         $bookedSeats_mp = array();
         //if (eme_is_multi($event['price'])) {
         if (eme_is_multi($booking['booking_price'])) {
            // make sure the array contains the correct keys already, since
            // later on in the function eme_record_booking we do a join
            //$booking_prices_mp=eme_convert_multi2array($event['price']);
            $booking_prices_mp=eme_convert_multi2array($booking['booking_price']);
            foreach ($booking_prices_mp as $key=>$value) {
               $bookedSeats_mp[$key] = 0;
            }
            foreach($_POST as $key=>$value) {
               if (preg_match('/bookedSeats(\d+)/', $key, $matches)) {
                  $field_id = intval($matches[1])-1;
                  $bookedSeats += $value;
                  $bookedSeats_mp[$field_id]=$value;
               }
            }
            eme_update_booking($booking_id,$booking['event_id'],eme_convert_array2multi($bookedSeats_mp),$booking['booking_price'],$bookerComment);
         } else {
            eme_update_booking($booking_id,$booking['event_id'],$bookedSeats,$booking['booking_price'],$bookerComment);
         }

         if (isset($_POST['bookerPhone'])) {
            $bookerPhone = eme_strip_tags($_POST['bookerPhone']);
            $booker=eme_get_person($booking['person_id']);
            if ($booker['person_phone'] != $bookerPhone)
               eme_update_phone($booker,$bookerPhone);
         }

         if ($send_mail) eme_email_rsvp_booking($booking_id,$action);
         print "<div id='message' class='updated'><p>".__("Booking updated","eme")."</p></div>";

      } elseif ($action == 'approveRegistration' || $action == 'denyRegistration' || $action == 'updatePayedStatus') {
         $bookings = isset($_POST ['bookings']) ? $_POST ['bookings'] : array();
         $selected_bookings = isset($_POST ['selected_bookings']) ? $_POST ['selected_bookings'] : array();
         $bookings_seats = isset($_POST ['bookings_seats']) ? $_POST ['bookings_seats'] : array();
         $bookings_payed = isset($_POST ['bookings_payed']) ? $_POST ['bookings_payed'] : array();

         foreach ( $bookings as $key=>$booking_id ) {
            if (!in_array($booking_id,$selected_bookings)) {
               continue;
            }
            // make sure the seats are integers
            $booking = eme_get_booking ($booking_id);
            if ($action == 'updatePayedStatus') {
               if ($booking['booking_payed']!= intval($bookings_payed[$key]))
                  eme_update_booking_payed($booking_id,intval($bookings_payed[$key]));
            } elseif ($action == 'approveRegistration') {
               eme_approve_booking($booking_id);
               if ($booking['booking_payed']!= intval($bookings_payed[$key]))
                  eme_update_booking_payed($booking_id,intval($bookings_payed[$key]));
               if ($send_mail) eme_email_rsvp_booking($booking_id,$action);
            } elseif ($action == 'denyRegistration') {
               // deny registration: this means the booking id will be deleted, so
               // if we want to sent a mail, we need to do that first
               if ($send_mail) eme_email_rsvp_booking($booking_id,$action);
               eme_delete_booking($booking_id);
            }
         }
      }
   }

   // now show the menu
   eme_registration_seats_form_table($pending);
}

function eme_registration_seats_form_table($pending=0) {
   global $plugin_page;

   $scope_names = array ();
   $scope_names['past'] = __ ( 'Past events', 'eme' );
   $scope_names['all'] = __ ( 'All events', 'eme' );
   $scope_names['future'] = __ ( 'Future events', 'eme' );

   $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
   $scope = isset($_POST['scope']) ? $_POST['scope'] : 'future';
   if (isset($_GET['search'])) {
      $scope="all";
      $search = "[person_id=".intval($_GET['search'])."]";
   }
   $all_events=eme_get_events(0,$scope);

?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Add a registration for an event','eme'); ?></h2>
<div class="wrap">
<br />
<?php admin_show_warnings();?>
   <form id='add-booking' name='add-booking' action="" method="post">
   <input type='hidden' name='eme_admin_action' value='newRegistration' />
   <table class="widefat">
   <tbody>
            <tr><th scope='row'><?php _e('Event', 'eme'); ?>:</th><td>
   <select name="event_id">
   <?php
   foreach ( $all_events as $event ) {
      if ($event ['event_rsvp']) {
         $option_text=$event['event_name']." (".eme_localised_date($event['event_start_date']).")"; 
         echo "<option value='".$event['event_id']."' >".$option_text."</option>  ";
      }
   }
   ?>
   </select>
                </td>
            </tr>
   </tbody>
   </table>
   <input type="submit" class="button-primary action" value="<?php _e ( 'Register new booking','eme' )?>" />
   </form>
<br />
</div>
<div class="clear"></div>
<h2><?php 
   if ($pending) 
      _e ('Pending Approvals','eme');
   else
      _e ('Change reserved spaces or cancel registrations','eme');
   ?>
</h2>
<div class="wrap">
<br />

   <div class="tablenav">
   <div class="alignleft">
   <form id="eme-admin-regsearchform" name="eme-admin-regsearchform" action="<?php echo admin_url("admin.php?page=$plugin_page"); ?>" method="post">

   <select name="scope">
   <?php
   foreach ( $scope_names as $key => $value ) {
      $selected = "";
      if ($key == $scope)
         $selected = "selected='selected'";
      echo "<option value='$key' $selected>$value</option>  ";
   }
   ?>
   </select>

   <select name="event_id">
   <option value='0'><?php _e ( 'All events' ); ?></option>
   <?php
   $events_with_bookings=array();
   foreach ( $all_events as $event ) {
      $selected = "";
      if ($event_id && ($event['event_id'] == $event_id))
         $selected = "selected='selected'";

      if ($pending && eme_get_pending_bookings($event['event_id'])>0) {
         $events_with_bookings[]=$event['event_id'];
         echo "<option value='".$event['event_id']."' $selected>".$event['event_name']."</option>  ";
      } elseif (eme_get_approved_seats($event['event_id'])>0) {
         $events_with_bookings[]=$event['event_id'];
         echo "<option value='".$event['event_id']."' $selected>".$event['event_name']."</option>  ";
      }
   }
   ?>
   </select>

   <input class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
   </form>
   </div>
   <br />
   <br />
   <form id="eme-admin-regform" name="eme-admin-regform" action="" method="post">
   <select name="eme_admin_action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
<?php if ($pending) { ?>
   <option value="approveRegistration"><?php _e ( 'Approve registration','eme' ); ?></option>
<?php } ?>
   <option value="updatePayedStatus"><?php _e ( 'Update payed status','eme' ); ?></option>
   <option value="denyRegistration"><?php _e ( 'Deny registration','eme' ); ?></option>
   </select>
   <input type="submit" class="button-secondary" value="<?php _e ( 'Apply' )?>" />

   <div class="clear"><p>
   <?php _e('Send mails to attendees upon changes being made?','eme'); echo eme_ui_select_binary(1,"send_mail"); ?>
   </p></div>
<?php 
      if ($pending) {
         $booking_status=1;
         // different table id for pending bookings, so the save-state from datatables doesn't interfere with the one from non-pending
         $table_id="eme_pending_admin_bookings";
      } else {
         $booking_status=2;
         $table_id="eme_admin_bookings";
      }

      if ($event_id)
         $bookings = eme_get_bookings_for($event_id,$booking_status);
      else
         $bookings = eme_get_bookings_for($events_with_bookings,$booking_status);
      if (!empty($bookings)) {
?>
   <table class="widefat hover stripe" id="<?php print "$table_id";?>">
   <thead>
      <tr>
         <th class='manage-column column-cb check-column' scope='col'><input
            class='select-all' type="checkbox" value='1' /></th>
         <th>hidden for person id search</th>
         <th><?php _e ('ID','eme'); ?></th>
         <th><?php _e ('Name','eme'); ?></th>
         <th><?php _e ('Date and time','eme'); ?></th>
         <th><?php _e ('Booker','eme'); ?></th>
         <th><?php _e ('Booking date','eme'); ?></th>
         <th><?php _e ('Seats','eme'); ?></th>
         <th><?php _e ('Event price','eme'); ?></th>
         <th><?php _e ('Total price','eme'); ?></th>
         <th><?php _e ('Unique nbr','eme'); ?></th>
         <th><?php _e ('Paid','eme'); ?></th>
      </tr>
   </thead>
   <tbody>
     <?php

      $search_dest=admin_url("admin.php?page=eme-people");
      foreach ( $bookings as $event_booking ) {
         $person = eme_get_person ($event_booking['person_id']);
         $search_url=add_query_arg(array('search'=>$person['person_id']),$search_dest);
         $event = eme_get_event($event_booking['event_id']);
         $localised_start_date = eme_localised_date($event['event_start_date']);
         $localised_start_time = eme_localised_time($event['event_start_time']);
         $localised_end_date = eme_localised_date($event['event_end_date']);
         $localised_end_time = eme_localised_time($event['event_end_time']);
         $localised_booking_date = eme_localised_date($event_booking['creation_date']);
         $localised_booking_time = eme_localised_time($event_booking['creation_date']);
         $startstring=strtotime($event['event_start_date']." ".$event['event_start_time']);
         $bookingtimestamp=strtotime($event_booking['creation_date']);
         $style = "";
         $today = date ( "Y-m-d" );
         
         if ($event['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         ?>
      <tr <?php echo "$style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='selected_bookings[]' />
             <input type='hidden' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='bookings[]' /></td>
          <td>[person_id=<?php echo $person['person_id']; ?>]</td>
         <td><a class="row-title" href="<?php echo admin_url("admin.php?page=$plugin_page&amp;eme_admin_action=editRegistration&amp;booking_id=".$event_booking ['booking_id']); ?>" title="<?php _e('Click the booking ID in order to see the details and/or edit the booking.','eme')?>"><?php echo $event_booking ['booking_id']; ?></a>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event_booking ['event_id']); ?>"><?php echo eme_trans_sanitize_html($event ['event_name']); ?></a>
         </strong>
         <?php
             $approved_seats = eme_get_approved_seats($event['event_id']);
             $pending_seats = eme_get_pending_seats($event['event_id']);
             $total_seats = $event ['event_seats'];
             echo "<br />".__('Approved: ','eme' ).$approved_seats.", ".__('Pending: ','eme').$pending_seats.", ".__('Max: ','eme').$total_seats;
             if ($approved_seats>0) {
                $printable_address = admin_url("/admin.php?page=eme-people&amp;eme_admin_action=booking_printable&amp;event_id=".$event['event_id']);
                $csv_address = admin_url("/admin.php?page=eme-people&amp;eme_admin_action=booking_csv&amp;event_id=".$event['event_id']);
                echo " (<a id='booking_printable_".$event['event_id']."'  target='' href='$printable_address'>".__('Printable view','eme')."</a>)";
                echo " (<a id='booking_csv_".$event['event_id']."'  target='' href='$csv_address'>".__('CSV export','eme')."</a>)";
             }
         ?>
         </td>
         <td data-sort="<?php echo $startstring; ?>">
            <?php echo $localised_start_date; if ($localised_end_date !='' && $localised_end_date != $localised_start_date) echo " - " . $localised_end_date; ?><br />
            <?php echo "$localised_start_time - $localised_end_time"; ?>
         </td>
         <td><a href="<?php echo $search_url; ?>"><?php echo eme_sanitize_html($person['person_name']) ."(".eme_sanitize_html($person['person_phone']).", ". eme_sanitize_html($person['person_email']).")";?></a>
         </td>
         <td data-sort="<?php echo $bookingtimestamp; ?>">
            <?php echo $localised_booking_date ." ". $localised_booking_time;?>
         </td>
         <?php if (eme_is_multi(eme_get_booking_price($event,$event_booking))) { ?>
         <td>
            <?php echo $event_booking['booking_seats_mp'] .'<br />'. __('(Multiprice)','eme');?>
         </td>
         <?php } else { ?>
         <td>
            <?php echo $event_booking['booking_seats'];?>
         </td>
         <?php } ?>
         <td>
            <?php echo eme_get_booking_price($event,$event_booking); ?>
         </td>
         <td>
            <?php echo eme_get_total_booking_price($event,$event_booking); ?>
         </td>
         <td>
            <?php echo eme_sanitize_html($event_booking['transfer_nbr_be97']); ?>
         </td>
         <td>
            <?php echo eme_ui_select_binary($event_booking['booking_payed'],"bookings_payed[]"); ?>
         </td>
      </tr>
      <?php
      }
      ?>
   </tbody>
   </table>

<script type="text/javascript">
   jQuery(document).ready( function() {
         jQuery('#<?php print "$table_id";?>').dataTable( {
            <?php
            // jquery datatables locale loading
            $locale_code = get_locale();
            $locale_file = EME_PLUGIN_DIR. "/js/jquery-datatables/i18n/$locale_code.json";
            $locale_file_url = EME_PLUGIN_URL. "/js/jquery-datatables/i18n/$locale_code.json";
            if ($locale_code != "en_US" && file_exists($locale_file)) {
            ?>
            "language": {
               "url": "<?php echo $locale_file_url; ?>"
               },
            <?php
            }
            ?> 
            "stateSave": true,
            <?php
            if (!empty($search)) {
               // If datatables state is saved, the initial search
               // is ignored and we need to use stateloadparams
               // So we give the 2 options
            ?> 
            "stateLoadParams": function (settings, data) {
               data.oSearch.sSearch = "<?php echo $search; ?>";
            },
            "search": {
               "search":  "<?php echo $search; ?>"
            },
            <?php
            }
            ?> 
            "pagingType": "full",
            "columnDefs": [
               { "sortable": false, "targets": 0 },
               { "visible": false, "targets": 1 }
            ]
         } );
   } );
</script>

<?php } ?>

   <div class='tablenav'>
   <div class="alignleft actions"><br class='clear' />
   </div>
   <br class='clear' />
   </div>

   </div>
   </form>
</div>
</div>
<?php
}

function eme_send_mails_page() {
   global $wpdb;

   $event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
   $action = isset($_POST ['eme_admin_action']) ? $_POST ['eme_admin_action'] : '';
   $message = isset($_POST ['message']) ? $_POST ['message'] : '';
   $subject = isset($_POST ['subject']) ? $_POST ['subject'] : '';

   if ($event_id>0 && $action == 'send_mail') {
      $pending_approved = isset($_POST ['pending_approved']) ? $_POST ['pending_approved'] : 0;
      $only_unpayed = isset($_POST ['only_unpayed']) ? $_POST ['only_unpayed'] : 0;
      $target = isset($_POST ['target']) ? $_POST ['target'] : 'attendees';
	   if (empty($subject) || empty($message)) {
		   print "<div id='message' class='error'><p>".__('Please enter both subject and message for the mail to be sent.','eme')."</p></div>";
	   } else {
		   $event = eme_get_event($event_id);
		   $current_userid=get_current_user_id();
		   if (current_user_can( get_option('eme_cap_send_other_mails')) ||
				   (current_user_can( get_option('eme_cap_send_mails')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {  

			   $event_name = $event['event_name'];
			   $contact = eme_get_contact ($event);
			   $contact_email = $contact->user_email;
			   $contact_name = $contact->display_name;

            if ($target == 'attendees') {
               $attendees = eme_get_attendees_for($event_id,$pending_approved,$only_unpayed);
               foreach ( $attendees as $attendee ) {
                  $tmp_message = eme_replace_placeholders($message, $event, "text",0,$attendee['lang']);
                  $tmp_subject = eme_replace_placeholders($subject, $event, "text",0,$attendee['lang']);

                  $tmp_message = eme_replace_attendees_placeholders($tmp_message, $event, $attendee, "text",0,$attendee['lang']);
                  $tmp_message = eme_strip_tags($tmp_message);
                  $tmp_subject = eme_replace_attendees_placeholders($tmp_subject, $event, $attendee, "text",0,$attendee['lang']);
                  $tmp_subject = eme_strip_tags($tmp_subject);
                  eme_send_mail($tmp_subject,$tmp_message, $attendee['person_email'], $attendee['person_name'], $contact_email, $contact_name);
               }
            } elseif ($target == 'bookings') {
               $bookings = eme_get_bookings_for($event_id,$pending_approved,$only_unpayed);
               foreach ( $bookings as $booking ) {
                  $tmp_message = eme_replace_placeholders($message, $event, "text",0,$booking['lang']);
                  $tmp_subject = eme_replace_placeholders($subject, $event, "text",0,$booking['lang']);
                  $attendee = eme_get_person($booking['person_id']);
                  if ($attendee && is_array($attendee)) {
                     $tmp_message = eme_replace_booking_placeholders($message, $event, $booking, "text",0,$booking['lang']);
                     $tmp_message = eme_strip_tags($tmp_message);
                     $tmp_subject = eme_replace_booking_placeholders($subject, $event, $booking, "text",0,$booking['lang']);
                     $tmp_subject = eme_strip_tags($tmp_subject);
                     eme_send_mail($tmp_subject,$tmp_message, $attendee['person_email'], $attendee['person_name'], $contact_email, $contact_name);
                  }
               }
			   }
			   print "<div id='message' class='updated'><p>".__('The mail has been sent.','eme')."</p></div>";
		   } else {
			   print "<div id='message' class='error'><p>".__('You do not have the permission to send mails for this event.','eme')."</p></div>";
		   }
	   }
   }

   // now show the form
   eme_send_mail_form($event_id);
}

function eme_send_mail_form($event_id=0) {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Send Mails to attendees or bookings for a event','eme'); ?></h2>
<?php admin_show_warnings();?>
   <div id='message' class='updated'><p>
<?php
      _e('Warning: using this functionality to send mails to attendees can result in a php timeout, so not everybody will receive the mail then. This depends on the number of attendees, the load on the server, ... . If this happens, use the CSV export link to get the list of all attendees and use mass mailing tools (like OpenOffice) for your mailing.','eme');
?>
   </p></div>
   <form id='send-mail' name='send-mail' action="" method="post">
   <input type='hidden' name='page' value='eme-send-mails' />
   <input type='hidden' name='eme_admin_action' value='send_mail' />
   <select name="event_id" onchange="this.form.submit()">
   <?php
   $all_events=eme_get_events(0,"future");
   $event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
   $current_userid=get_current_user_id();
   echo "<option value='0' >".__('Select the event','eme')."</option>  ";
   foreach ( $all_events as $event ) {
         $option_text=$event['event_name']." (".eme_localised_date($event['event_start_date']).")";
	 if ($event['event_rsvp'] && current_user_can( get_option('eme_cap_send_other_mails')) ||
			 (current_user_can( get_option('eme_cap_send_mails')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {  
		 if ($event['event_id'] == $event_id) {
			 echo "<option selected='selected' value='".$event['event_id']."' >".$option_text."</option>  ";
		 } else {
			 echo "<option value='".$event['event_id']."' >".$option_text."</option>  ";
		 }
	 }
   }
   ?>
   </select>
   <p>
   <?php if ($event_id>0) {?>
      <table>
      <tr>
	   <td><label><?php _e('Select the type of mail','eme'); ?></td>
      <td>
           <select name="target">
           <option value='attendees'><?php _e('Attendee mails','eme'); ?></option>
           <option value='bookings'><?php _e('Booking mails','eme'); ?></option>
           </select>
      </td>
      </tr>
      <tr>
	   <td><label><?php _e('Select your target audience','eme'); ?></td>
      <td>
           <select name="pending_approved">
           <option value=0><?php _e('All','eme'); ?></option>
           <option value=2><?php _e('Exclude pending registrations','eme'); ?></option>
           <option value=1><?php _e('Only pending registrations','eme'); ?></option>
           </select></p><p>
      </td>
      </tr>
      <tr>
      <td><?php _e('Only send mails to attendees who did not pay yet','eme'); ?>&nbsp;</td>
      <td>
           <input type="checkbox" name="only_unpayed" value="1" />
      </td>
      </tr>
      </table>
	   <div id="titlediv" class="form-field form-required"><p>
		   <label><?php _e('Subject','eme'); ?></label><br>
		   <input type="text" name="subject" value="" /></p>
	   </div>
	   <div class="form-field form-required"><p>
	   <label><?php _e('Message','eme'); ?></label><br>
	   <textarea name="message" value="" rows=10></textarea> </p>
	   </div>
	   <div>
	   <?php _e('You can use any placeholders mentioned here:','eme');
	   print "<br><a href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('Event placeholders','eme')."</a>";
	   print "<br><a href='http://www.e-dynamics.be/wordpress/?cat=48'>".__('Attendees placeholders','eme')."</a> (".__('for ','eme').__('Attendee mails','eme').")";
	   print "<br><a href='http://www.e-dynamics.be/wordpress/?cat=45'>".__('Booking placeholders','eme')."</a> (".__('for ','eme').__('Booking mails','eme').")";
	   ?>
	   </div>
      <br />
	   <input type="submit" value="<?php _e ( 'Send Mail', 'eme' ); ?>" class="button-primary action" />
	   </form>

   <?php
	   $csv_address = admin_url("/admin.php?page=eme-people&amp;eme_admin_action=booking_csv&amp;event_id=".$event['event_id']);
	   $available_seats = eme_get_available_seats($event['event_id']);
	   $total_seats = $event ['event_seats'];
	   if ($total_seats!=$available_seats)
		   echo "<br><br> <a id='booking_csv_".$event['event_id']."'  target='' href='$csv_address'>".__('CSV export','eme')."</a>";
   }
}

function eme_webmoney_form($event,$booking_id) {
   $booking = eme_get_booking($booking_id);
   $events_page_link = eme_get_events_page(true, false);
   $name = eme_sanitize_html(sprintf(__("Booking for '%s'","eme"),$event['event_name']));
   $price=eme_get_total_booking_price($event,$booking);

   require_once('webmoney/webmoney.inc.php');
   $wm_request = new WM_Request();
   $wm_request->payment_amount =$price;
   $wm_request->payment_desc = $name;
   $wm_request->payment_no = $booking_id;
   $wm_request->payee_purse = get_option('eme_webmoney_purse');
   $wm_request->success_method = WM_POST;
   $result_link = add_query_arg(array('eme_eventAction'=>'webmoney'),$events_page_link);

   $wm_request->result_url = $result_link;
   $wm_request->success_url = eme_payment_return_url($event,$booking_id,1);
   $wm_request->fail_url = eme_payment_return_url($event,$booking_id,2);
   if (get_option('eme_webmoney_demo')) {
      $wm_request->sim_mode = WM_ALL_SUCCESS;
   }
   $wm_request->btn_label = 'Pay via Webmoney';

   $form_html = "<br>".__("You can pay for this event via 2Checkout. If you wish to do so, click the button below.",'eme');
   $form_html .= $wm_request->SetForm(false);
   return $form_html;
}

function eme_2co_form($event,$booking_id) {
   $booking = eme_get_booking($booking_id);
   $events_page_link = eme_get_events_page(true, false);
   $business=get_option('eme_2co_business');
   $url=CO_URL;
   $name = eme_sanitize_html(sprintf(__("Booking for '%s'","eme"),$event['event_name']));
   $price=eme_get_total_booking_price($event,$booking);
   $quantity=1;
   $cur=$event['currency'];
   $return_url=eme_payment_return_url($event,$booking_id,1);

   $form_html = "<br>".__("You can pay for this event via 2Checkout. If you wish to do so, click the button below.",'eme');
   $form_html.="<form action='$url' method='post'>";
   $form_html.="<input type='hidden' name='sid' value='$business' >";
   $form_html.="<input type='hidden' name='mode' value='2CO' >";
   $form_html.="<input type='hidden' name='return_url' value='$return_url' >";
   $form_html.="<input type='hidden' name='li_0_type' value='product' >";
   $form_html.="<input type='hidden' name='li_0_product_id' value='$booking_id' >";
   $form_html.="<input type='hidden' name='li_0_name' value='$name' >";
   $form_html.="<input type='hidden' name='li_0_price' value='$price' >";
   $form_html.="<input type='hidden' name='li_0_quantity' value='$quantity' >";
   $form_html.="<input type='hidden' name='currency_code' value='$cur' >";
   $form_html.="<input name='submit' type='submit' value='Pay via 2Checkout' >";
   if (get_option('eme_2co_demo')) {
      $form_html.="<input type='hidden' name='demo' value='Y' >";
   }
   $form_html.="</form>";
   return $form_html;
}

function eme_fdgg_form($event,$booking_id) {
   $booking = eme_get_booking($booking_id);
   $events_page_link = eme_get_events_page(true, false);
   $store_name = get_option('eme_fdgg_store_name');
   $shared_secret = get_option('eme_fdgg_shared_secret');
   // the live or sandbox url
   $url = get_option('eme_fdgg_url');
   $name = eme_sanitize_html(sprintf(__("Booking for '%s'","eme"),$event['event_name']));
   $price=eme_get_total_booking_price($event,$booking);
   $quantity=1;
   //$cur=$event['currency'];
   // First Data only allows USD
   $cur="USD";
   $datetime=date("Y:m:d-H:i:s",strtotime($booking['creation_date_gmt']));
   $timezone_short="GMT";

   require_once('fdgg/fdgg-util_sha2.php');
   $form_html = "<br>".__("You can pay for this event via First Data. If you wish to do so, click the button below.",'eme');
   $form_html.="<form action='$url' method='post'>";
   $form_html.="<input type='hidden' name='timezone' value='$timezone_short' />";
   $form_html.="<input type='hidden' name='authenticateTransaction' value='false' />";
   $form_html.="<input type='hidden' name='txntype' value='sale'/>";
   $form_html.="<input type='hidden' name='mode' value='payonly' />";
   $form_html.="<input type='hidden' name='trxOrigin' value='ECI' />";
   $form_html.="<input type='hidden' name='txndatetime' value='$datetime />";
   $form_html.="<input type='hidden' name='hash' value='".fdgg_createHash($store_name . $datetime . $price . $shared_secret)."' />";
   $form_html.="<input type='hidden' name='storename' value='$store_name'/>";
   $form_html.="<input type='hidden' name='chargetotal' value='$price'/>";
   $form_html.="<input type='hidden' name='subtotal' value='$price'/>";
   $form_html.="<input type='hidden' name='invoicenumber' value='$booking_id' />";
   $form_html.="<input type='hidden' name='oid' value='$booking_id' />";
   $form_html.="<input type='hidden' name='responseSuccessURL' value='".eme_payment_return_url($event,$booking_id,1)."' >";
   $form_html.="<input type='hidden' name='responseFailURL' value='".eme_payment_return_url($event,$booking_id,2)."' >";
   $form_html.="<input type='hidden' name='eme_eventAction' value='fdgg_ipn' />";
   $form_html.="<input name='submit' type='submit' value='Pay via First Data' >";
   $form_html.="</form>";
   return $form_html;
}


function eme_google_form($event,$booking_id) {
   $booking = eme_get_booking($booking_id);
   $price=eme_get_total_booking_price($event,$booking);
   $quantity=1;
   $events_page_link = eme_get_events_page(true, false);

   require_once('google_checkout/googlecart.php');
   require_once('google_checkout/googleitem.php');
   $merchant_id = get_option('eme_google_merchant_id');  // Your Merchant ID
   $merchant_key = get_option('eme_google_merchant_key');  // Your Merchant Key
   $server_type = get_option('eme_google_checkout_type');
   $cart = new GoogleCart($merchant_id, $merchant_key, $server_type, $event['currency']);
   $return_url=eme_payment_return_url($event,$booking_id,1);
   $cart->SetContinueShoppingUrl($return_url);
   $item_1 = new GoogleItem("Booking", // Item name
                            sprintf(__("Booking for '%s'","eme"),eme_sanitize_html($event['event_name'])), // Item description
                            $quantity, // Quantity
                            $price); // Unit price
   $item_1->SetMerchantItemId($booking_id);
   $cart->AddItem($item_1);
   $form_html = "<br>".__("You can pay for this event via Google Checkout. If you wish to do so, click the button below.",'eme');
   return $form_html.$cart->CheckoutButtonCode("SMALL");
}

function eme_paypal_form($event,$booking_id) {
   $booking = eme_get_booking($booking_id);
   $price=eme_get_total_booking_price($event,$booking);
   $quantity=1;
   $events_page_link = eme_get_events_page(true, false);
   $notification_link = add_query_arg(array('eme_eventAction'=>'paypal_notification'),$events_page_link);

   $form_html = "<br>".__("You can pay for this event via paypal. If you wish to do so, click the 'Pay via Paypal' button below.",'eme');
   require_once "paypal/Paypal.php";
   $p = new Paypal;

   // the paypal or paypal sandbox url
   $p->paypal_url = get_option('eme_paypal_url');

   // the timeout in seconds before the button form is submitted to paypal
   // this needs the included addevent javascript function
   // 0 = no delay
   // false = disable auto submission
   $p->timeout = false;

   // the button label
   // false to disable button (if you want to rely only on the javascript auto-submission) not recommended
   $p->button = __('Pay via Paypal','eme');

   if (get_option('eme_paypal_s_encrypt')) {
      // use encryption (strongly recommended!)
      $p->encrypt = true;
      $p->private_key = get_option('eme_paypal_s_privkey');
      $p->public_cert = get_option('eme_paypal_s_pubcert');
      $p->paypal_cert = get_option('eme_paypal_s_paypalcert');
      $p->cert_id = get_option('eme_paypal_s_certid');
   } else {
      $p->encrypt = false;
   }

   // the actual button parameters
   // https://www.paypal.com/IntegrationCenter/ic_std-variable-reference.html
   $p->add_field('charset','utf-8');
   $p->add_field('business', get_option('eme_paypal_business'));
   $p->add_field('return', eme_payment_return_url($event,$booking_id,1));
   $p->add_field('cancel_return', eme_payment_return_url($event,$booking_id,2));
   $p->add_field('notify_url', $notification_link);
   $p->add_field('item_name', sprintf(__("Booking for '%s'","eme"),eme_sanitize_html($event['event_name'])));
   $p->add_field('item_number', $booking_id);
   $p->add_field('currency_code',$event['currency']);
   $p->add_field('amount', $price);
   $p->add_field('quantity', $quantity);

   $form_html .= $p->get_button();
   return $form_html;
}

function eme_paypal_notification() {
   require_once 'paypal/IPN.php';
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
   //$ipn->currencies = array('USD','EUR');
   $ipn->currencies = false;

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
      $booking_id=intval($ipn->ipn['item_number']);
      $booking=eme_get_booking($booking_id);
      $event_id = eme_get_event_id_by_booking_id($booking_id);
      $event = eme_get_event($event_id);
      if ($event['event_properties']['auto_approve'] == 1 && $booking['booking_approved']==0)
         eme_update_booking_payed($booking_id,1,1);
      else
         eme_update_booking_payed($booking_id,1,0);
      $ipn->complete();
   }
}

function eme_google_notification() {
  // this function is here for google payment handling, but since that
  // needs a certificate, I don't use it yet
  // Even for just the callback uri, https is required if not using the sandbox
  require_once('google_checkout/googleresponse.php');
  require_once('google_checkout/googleresult.php');
  require_once('google_checkout/googlerequest.php');

  define('RESPONSE_HANDLER_ERROR_LOG_FILE', 'googleerror.log');
  define('RESPONSE_HANDLER_LOG_FILE', 'googlemessage.log');

  $merchant_id = get_option('eme_google_merchant_id');  // Your Merchant ID
  $merchant_key = get_option('eme_google_merchant_key');  // Your Merchant Key
  $server_type = get_option('eme_google_checkout_type');

  $Gresponse = new GoogleResponse($merchant_id, $merchant_key);
  $Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type, $event['currency']);
  $GRequest->SetCertificatePath($certificate_path);

  //Setup the log file
  //$Gresponse->SetLogFiles(RESPONSE_HANDLER_ERROR_LOG_FILE, 
  //                                      RESPONSE_HANDLER_LOG_FILE, L_ALL);

  // Retrieve the XML sent in the HTTP POST request to the ResponseHandler
  $xml_response = isset($HTTP_RAW_POST_DATA)?
                    $HTTP_RAW_POST_DATA:file_get_contents("php://input");
  if (get_magic_quotes_gpc()) {
    $xml_response = stripslashes($xml_response);
  }
  list($root, $data) = $Gresponse->GetParsedXML($xml_response);
  $Gresponse->SetMerchantAuthentication($merchant_id, $merchant_key);

  /*$status = $Gresponse->HttpAuthentication();
  if(! $status) {
    die('authentication failed');
  }*/

  /* Commands to send the various order processing APIs
   * Send charge order : $Grequest->SendChargeOrder($data[$root]
   *    ['google-order-number']['VALUE'], <amount>);
   * Send process order : $Grequest->SendProcessOrder($data[$root]
   *    ['google-order-number']['VALUE']);
   * Send deliver order: $Grequest->SendDeliverOrder($data[$root]
   *    ['google-order-number']['VALUE'], <carrier>, <tracking-number>,
   *    <send_mail>);
   * Send archive order: $Grequest->SendArchiveOrder($data[$root]
   *    ['google-order-number']['VALUE']);
   *
   */

  switch ($root) {
    case "request-received": {
      break;
    }
    case "error": {
      break;
    }
    case "diagnosis": {
      break;
    }
    case "checkout-redirect": {
      break;
    }
    case "merchant-calculation-callback": {
      break;
    }
    case "new-order-notification": {
      $Gresponse->SendAck();
      break;
    }
    case "order-state-change-notification": {
      $Gresponse->SendAck();
      $new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
      $new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];

      switch($new_financial_state) {
        case 'REVIEWING': {
          break;
        }
        case 'CHARGEABLE': {
          $Grequest->SendProcessOrder($data[$root]['google-order-number']['VALUE']);
          $Grequest->SendChargeOrder($data[$root]['google-order-number']['VALUE'],'');
          break;
        }
        case 'CHARGING': {
          break;
        }
        case 'CHARGED': {
          $booking_id=intval($data[$root]['google-order-number']['VALUE']);
          $booking=eme_get_booking($booking_id);
          $event_id = eme_get_event_id_by_booking_id($booking_id);
          $event = eme_get_event($event_id);
          if ($event['event_properties']['auto_approve'] == 1 && $booking['booking_approved']==0)
             eme_update_booking_payed($booking_id,1,1);
          else
             eme_update_booking_payed($booking_id,1,0);
          break;
        }
        case 'PAYMENT_DECLINED': {
          break;
        }
        case 'CANCELLED': {
          break;
        }
        case 'CANCELLED_BY_GOOGLE': {
          //$Grequest->SendBuyerMessage($data[$root]['google-order-number']['VALUE'],
          //    "Sorry, your order is cancelled by Google", true);
          break;
        }
        default:
          break;
      }

      break;
    }
    case "charge-amount-notification": {
      //$Grequest->SendDeliverOrder($data[$root]['google-order-number']['VALUE'],
      //    <carrier>, <tracking-number>, <send-email>);
      //$Grequest->SendArchiveOrder($data[$root]['google-order-number']['VALUE'] );
      $Gresponse->SendAck();
      break;
    }
    case "chargeback-amount-notification": {
      $Gresponse->SendAck();
      break;
    }
    case "refund-amount-notification": {
      $Gresponse->SendAck();
      break;
    }
    case "risk-information-notification": {
      $Gresponse->SendAck();
      break;
    }
    default:
      $Gresponse->SendBadRequestStatus("Invalid or not supported Message");
      break;
  }
}

function eme_2co_notification() {
   $business=get_option('eme_2co_business');
   $secret=get_option('eme_2co_secret');

   if ($_POST['message_type'] == 'ORDER_CREATED'
       || $_POST['message_type'] == 'INVOICE_STATUS_CHANGED') {
      $insMessage = array();
      foreach ($_POST as $k => $v) {
         $insMessage[$k] = $v;
      }
 
      $hashSid = $insMessage['vendor_id'];
      if ($hashSid != $business) {
         die ('Not the 2Checkout Account number it should be ...');
      }
      $hashOrder = $insMessage['sale_id'];
      $hashInvoice = $insMessage['invoice_id'];
      $StringToHash = strtoupper(md5($hashOrder . $hashSid . $hashInvoice . $secret));
 
      if ($StringToHash != $insMessage['md5_hash']) {
         die('Hash Incorrect');
      }

      if ($insMessage['invoice_status'] == 'approved' || $insMessage['invoice_status'] == 'deposited') {
         $booking_id=intval($insMessage['item_id_1']);
         // TODO: do some extra checks, like the price payed and such
#$booking=eme_get_booking($booking_id);
#$event = eme_get_event($booking['event_id']);
         $booking=eme_get_booking($booking_id);
         $event_id = eme_get_event_id_by_booking_id($booking_id);
         $event = eme_get_event($event_id);
         if ($event['event_properties']['auto_approve'] == 1 && $booking['booking_approved']==0)
            eme_update_booking_payed($booking_id,1,1);
         else
            eme_update_booking_payed($booking_id,1,0);
      }
   }
}

function eme_webmoney_notification() {
   $webmoney_purse = get_option('eme_webmoney_purse');
   $webmoney_secret = get_option('eme_webmoney_secret');

   require_once('webmoney/webmoney.inc.php');
   $wm_notif = new WM_Notification(); 
   if ($wm_notif->GetForm() != WM_RES_NOPARAM) {
      $booking_id=intval($wm_notif->payment_no);
      $booking=eme_get_booking($booking_id);
      $event_id = eme_get_event_id_by_booking_id($booking_id);
      $event = eme_get_event($event_id);
      // TODO: do some extra checks, like the price payed and such
      #$price=eme_get_total_booking_price($event,$booking)
      $amount=$wm_notif->payment_amount;
      if ($webmoney_purse != $wm_notif->payee_purse) {
         die ('Not the webmoney purse it should be ...');
      }
      #if ($price != $amount) {
      #   die ('Not the webmoney amount I expected ...');
      #}
      if ($wm_notif->CheckMD5($webmoney_purse, $amount, $booking_id, $webmoney_secret) == WM_RES_OK) {
         if ($event['event_properties']['auto_approve'] == 1 && $booking['booking_approved']==0)
            eme_update_booking_payed($booking_id,1,1);
         else
            eme_update_booking_payed($booking_id,1,0);
      }
   }
}

function eme_fdgg_notification() {
   $store_name = get_option('eme_fdgg_store_name');
   $shared_secret = get_option('eme_fdgg_shared_secret');
   require_once('fdgg/fdgg-util_sha2.php');

   $booking_id      = intval($_POST['invoicenumber']);
   $charge_total    = $_POST['charge_total'];
   $approval_code   = $_POST['approval_code'];
   $response_hash   = $_POST['response_hash'];
   $response_status = $_POST['status'];

   //$cur=$event['currency'];
   // First Data only allows USD
   $cur="USD";
   $booking=eme_get_booking($booking_id);
   $event_id = eme_get_event_id_by_booking_id($booking_id);
   $event = eme_get_event($event_id);
   $datetime=date("Y:m:d-H:i:s",strtotime($booking['creation_date_gmt']));
   $timezone_short="GMT";
   $calc_hash=fdgg_createHash($shared_secret.$approval_code.$charge_total.$cur.$datetime.$store_name);

   if ($response_hash != $calc_hash) {
      die('Hash Incorrect');
   }

   // TODO: do some extra checks, like the price payed and such
   #$price=eme_get_total_booking_price($event,$booking);

   if (strtolower($response_status) == 'approved') {
      if ($event['event_properties']['auto_approve'] == 1 && $booking['booking_approved']==0)
         eme_update_booking_payed($booking_id,1,1);
      else
         eme_update_booking_payed($booking_id,1,0);
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
   $sql = $wpdb->prepare("SELECT registration_requires_approval from $events_table where event_id=%d",$event_id);
   return $wpdb->get_var( $sql );
}

function eme_get_booking_price($event,$booking) {
   if ($booking['booking_price']!=="")
      $basic_price=$booking['booking_price'];
   else
      $basic_price=$event['price'];
   // don't convert to int or float or whatever; it can be multiprice
   return $basic_price;
}

function eme_get_total_booking_price($event,$booking) {
   $price=0;
   $basic_price= eme_get_booking_price($event,$booking);

   if (eme_is_multi($basic_price)) {
      $prices=eme_convert_multi2array($basic_price);
      $seats=eme_convert_multi2array($booking['booking_seats_mp']);
      foreach ($prices as $key=>$val) {
         $price += $val*$seats[$key];
      }
   } else {
      $price = $basic_price*$booking['booking_seats'];
   }
   return $price;
}

function eme_get_total_booking_multiprice($event,$booking) {
   $price=array();
   $basic_price= eme_get_booking_price($event,$booking);

   if (eme_is_multi($basic_price)) {
      $prices=eme_convert_multi2array($basic_price);
      $seats=eme_convert_multi2array($booking['booking_seats_mp']);
      foreach ($prices as $key=>$val) {
         $price[] = $val*$seats[$key];
      }
   }
   return $price;
}

function eme_is_event_rsvp ($event) {
   $rsvp_is_active = get_option('eme_rsvp_enabled');
   if ($rsvp_is_active && $event['event_rsvp'])
      return 1;
   else
      return 0;
}

function eme_event_needs_payment ($event) {
   if ($event['use_paypal'] || $event['use_google'] || $event['use_2co'] || $event['use_webmoney'] || $event['use_fdgg'])
      return 1;
   else
      return 0;
}

function eme_is_event_multiprice($event_id) {
   global $wpdb;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT price from $events_table where event_id=%d",$event_id);
   $price = $wpdb->get_var( $sql );
   return eme_is_multi($price);
}

function eme_is_multi($price) {
   if (preg_match("/\|\|/",$price))
      return 1;
   else
      return 0;
}

function eme_convert_multi2array($multistring) {
   return preg_split("/\|\|/",$multistring);
}

function eme_convert_array2multi($multiarr) {
   return join("||",$multiarr);
}

function eme_is_event_multiseats($event_id) {
   global $wpdb;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT event_seats from $events_table where event_id=%d",$event_id);
   $seats = $wpdb->get_var( $sql );
   return eme_is_multi($seats);
}

function eme_get_multitotal($multistring) {
   return array_sum(eme_convert_multi2array($multistring));
}

?>
