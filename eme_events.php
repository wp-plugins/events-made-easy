<?php

function eme_new_event() {

   $event = array (
      "event_id" => '',
      "event_name" => '',
      "event_status" => get_option('eme_event_initial_state'),
      "event_start_date" => '',
      "event_start_time" => '',
      "event_end_date" => '',
      "event_end_time" => '',
      "event_notes" => '',
      "event_rsvp" => get_option('eme_rsvp_reg_for_new_events')? 1:0,
      "use_paypal" => get_option('eme_paypal_business')? 1:0,
      "use_google" => get_option('eme_google_merchant_id')? 1:0,
      "use_2co" => get_option('eme_2co_business')? 1:0,
      "use_webmoney" => get_option('eme_webmoney_purse')? 1:0,
      "use_fdgg" => get_option('eme_fdgg_store_name')? 1:0,
      "price" => get_option('eme_default_price'),
      "currency" => get_option('eme_default_currency'),
      "rsvp_number_days" => get_option('eme_rsvp_number_days'),
      "rsvp_number_hours" => get_option('eme_rsvp_number_hours'),
      "registration_requires_approval" => get_option('eme_rsvp_require_approval')? 1:0,
      "registration_wp_users_only" => get_option('eme_rsvp_registered_users_only')? 1:0,
      "event_seats" => get_option('eme_rsvp_default_number_spaces'),
      "location_id" => 0,
      "event_author" => 0,
      "event_contactperson_id" => get_option('eme_default_contact_person'),
      "event_category_ids" => '',
      "event_attributes" => array(),
      "event_properties" => array(),
      "event_page_title_format" => '',
      "event_single_event_format" => '',
      "event_contactperson_email_body" => '',
      "event_respondent_email_body" => '',
      "event_registration_pending_email_body" => '',
      "event_registration_updated_email_body" => '',
      "event_registration_form_format" => '',
      "event_cancel_form_format" => '',
      "event_registration_recorded_ok_html" => '',
      "event_slug" => '',
      "event_image_url" => '',
      "event_image_id" => 0,
      "event_url" => '',
      "recurrence_id" => 0,
      "recurrence_freq" => '',
      "recurrence_start_date" => '',
      "recurrence_end_date" => '',
      "recurrence_interval" => '',
      "recurrence_byweekno" => '',
      "recurrence_byday" => '',
      "recurrence_specific_days" => '',
   );
   $event['event_properties'] = eme_init_event_props($event['event_properties']);
   return $event;
}

function eme_init_event_props($props) {
   if (!isset($props['auto_approve']))
      $props['auto_approve']=0;
   if (!isset($props['ignore_pending']))
      $props['ignore_pending']=0;
   if (!isset($props['all_day']))
      $props['all_day']=0;
   if (!isset($props['min_allowed']))
      $props['min_allowed']=get_option('eme_rsvp_addbooking_min_spaces');
   if (!isset($props['max_allowed']))
      $props['max_allowed']=get_option('eme_rsvp_addbooking_max_spaces');
   return $props;
}

function eme_new_event_page() {
   // check the user is allowed to make changes
   if ( !current_user_can( get_option('eme_cap_add_event')  ) ) {
      return;
   }

   $title = __ ( "Insert New Event", 'eme' );
   $event = eme_new_event();

   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "insert_event") {
      eme_events_page();
   } else {
      eme_event_form ($event, $title, 0);
   }
}

function eme_events_page() {
   global $wpdb;

   $extra_conditions = array();
   $action = isset($_GET['eme_admin_action']) ? $_GET['eme_admin_action'] : '';
   $event_ID = isset($_GET['event_id']) ? intval($_GET['event_id']) : '';
   $recurrence_ID = isset($_GET['recurrence_id']) ? intval($_GET['recurrence_id']) : '';
   $selectedEvents = isset($_GET['events']) ? $_GET['events'] : '';

   $current_userid=get_current_user_id();

   // if the delete event button is pushed while editing an event, set the action
   if (isset($_POST['event_delete_button'])) {
      $selectedEvents=array($event_ID);
      $action = "deleteEvents";
   }
   // if the delete recurrence button is pushed while editing a recurrence, set the action
   if (isset($_POST['event_deleteRecurrence_button'])) {
      $recurrence=eme_get_recurrence($recurrence_ID);
      $selectedEvents=array($recurrence['event_id']);
      $action = "deleteRecurrence";
   }

   // in case some generic actions were taken (like disable hello or disable donate), ignore all other actions
   if (isset($_GET['disable_hello_to_user']) || isset($_GET['disable_donate_message']) || isset($_GET['dbupdate']) || isset($_GET['disable_legacy_warning'])) {
      $action ="";
   }
   
   // DELETE action (either from the event list, or when the delete button is pushed while editing an event)
   if ($action == 'deleteEvents') {
      if (current_user_can( get_option('eme_cap_edit_events')) ||
         (current_user_can( get_option('eme_cap_author_event')) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {  
         foreach ( $selectedEvents as $event_ID ) {
            $tmp_event = array();
            $tmp_event = eme_get_event ( $event_ID );
            if ($tmp_event['recurrence_id']>0) {
               # if the event is part of a recurrence and it is the last event of the recurrence, delete the recurrence
               # else just delete the singe event
               if (eme_recurrence_count($tmp_event['recurrence_id'])==1) {
                  eme_remove_recurrence ( $tmp_event['recurrence_id'] );
               } else {
                  eme_db_delete_event ( $tmp_event );
               }
            } else {
               eme_db_delete_event ( $tmp_event );
            }
         }
         $feedback_message = __ ( 'Event(s) deleted!', 'eme' );
      } else {
         $feedback_message = __ ( 'You have no right to delete events!', 'eme' );
      }
      
      echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
      eme_events_table ();
      return;
   }

   // DELETE action (either from the event list, or when the delete button is pushed while editing a recurrence)
   if ($action == 'deleteRecurrence') {
      foreach ( $selectedEvents as $event_ID ) {
         $tmp_event = array();
         $tmp_event = eme_get_event ( $event_ID );
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {  
            if ($tmp_event['recurrence_id']>0) {
               eme_remove_recurrence ( $tmp_event['recurrence_id'] );
            }
         }
      }
      eme_events_table ();
      return;
   }

   // UPDATE or CREATE action
   if ($action == 'insert_event' || $action == 'update_event' || $action == 'update_recurrence') {
      if ( ! (current_user_can( get_option('eme_cap_add_event')) || current_user_can( get_option('eme_cap_edit_events'))) ) {
         $feedback_message = __('You have no right to insert or update events','eme');
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         eme_events_table ();
         return;
      }

      $event = array();
      $location = eme_new_location ();
      $event['event_name'] = isset($_POST['event_name']) ? trim(stripslashes ( $_POST['event_name'] )) : '';
      if (!current_user_can( get_option('eme_cap_publish_event')) ) {
         $event['event_status']=STATUS_DRAFT;   
      } else {
         $event['event_status'] = isset($_POST['event_status']) ? stripslashes ( $_POST['event_status'] ) : get_option('eme_event_initial_state');
      }
      $event['event_start_date'] = isset($_POST['event_start_date']) ? $_POST['event_start_date'] : '';
      // for compatibility: check also the POST variable event_date
      $event['event_start_date'] = isset($_POST['event_date']) ? $_POST['event_date'] : $event['event_start_date'];
      $event['event_end_date'] = isset($_POST['event_end_date']) ? $_POST['event_end_date'] : '';
      if (!_eme_is_date_valid($event['event_start_date']))
          $event['event_start_date'] = "";
      if (!_eme_is_date_valid($event['event_end_date']))
          $event['event_end_date'] = "";
      if (isset($_POST['event_start_time']) && !empty($_POST['event_start_time'])) {
         $event['event_start_time'] = date ("H:i:00", strtotime ($_POST['event_start_time']));
      } else {
         $event['event_start_time'] = "00:00:00";
      }
      if (isset($_POST['event_end_time']) && !empty($_POST['event_end_time'])) {
         $event['event_end_time'] = date ("H:i:00", strtotime ($_POST['event_end_time']));
      } else {
         $event['event_end_time'] = "00:00:00";
      }
      $recurrence['recurrence_freq'] = isset($_POST['recurrence_freq']) ? $_POST['recurrence_freq'] : '';
      if ($recurrence['recurrence_freq'] == 'specific') {
         $recurrence['recurrence_specific_days'] = isset($_POST['recurrence_start_date']) ? $_POST['recurrence_start_date'] : $event['event_start_date'];
         $recurrence['recurrence_start_date'] = "";
         $recurrence['recurrence_end_date'] = "";
      } else {
         $recurrence['recurrence_specific_days'] = "";
         $recurrence['recurrence_start_date'] = isset($_POST['recurrence_start_date']) ? $_POST['recurrence_start_date'] : $event['event_start_date'];
         $recurrence['recurrence_end_date'] = isset($_POST['recurrence_end_date']) ? $_POST['recurrence_end_date'] : $event['event_end_date'];
      }
      if (!_eme_is_date_valid($recurrence['recurrence_start_date']))
          $recurrence['recurrence_start_date'] = "";
      if (!_eme_is_date_valid($recurrence['recurrence_end_date']))
          $recurrence['recurrence_end_date'] = $recurrence['recurrence_start_date'];
      if (!_eme_are_dates_valid($recurrence['recurrence_specific_days']))
          $recurrence['recurrence_specific_days'] = "";
      if ($recurrence['recurrence_freq'] == 'weekly') {
         if (isset($_POST['recurrence_bydays'])) {
            $recurrence['recurrence_byday'] = implode ( ",", $_POST['recurrence_bydays']);
         } else {
            $recurrence['recurrence_byday'] = '';
         }
      } else {
         if (isset($_POST['recurrence_byday'])) {
            $recurrence['recurrence_byday'] = $_POST['recurrence_byday'];
         } else {
            $recurrence['recurrence_byday'] = '';
         }
      }
      $recurrence['recurrence_interval'] = isset($_POST['recurrence_interval']) ? $_POST['recurrence_interval'] : 1;
      if ($recurrence['recurrence_interval'] ==0)
         $recurrence['recurrence_interval']=1;
      $recurrence['recurrence_byweekno'] = isset($_POST['recurrence_byweekno']) ? $_POST['recurrence_byweekno'] : '';
      
      $event['event_rsvp'] = (isset ($_POST['event_rsvp']) && is_numeric($_POST['event_rsvp'])) ? $_POST['event_rsvp']:0;
      $event['rsvp_number_days'] = (isset ($_POST['rsvp_number_days']) && is_numeric($_POST['rsvp_number_days'])) ? $_POST['rsvp_number_days']:0;
      $event['rsvp_number_hours'] = (isset ($_POST['rsvp_number_hours']) && is_numeric($_POST['rsvp_number_hours'])) ? $_POST['rsvp_number_hours']:0;
      $event['registration_requires_approval'] = (isset ($_POST['registration_requires_approval']) && is_numeric($_POST['registration_requires_approval'])) ? $_POST['registration_requires_approval']:0;
      $event['registration_wp_users_only'] = (isset ($_POST['registration_wp_users_only']) && is_numeric($_POST['registration_wp_users_only'])) ? $_POST['registration_wp_users_only']:0;
      $event['event_seats'] = isset ($_POST['event_seats']) ? $_POST['event_seats']:0;
      if (preg_match("/\|\|/",$event['event_seats'])) {
         $multiseat=preg_split("/\|\|/",$event['event_seats']);
         foreach ($multiseat as $key=>$value) {
            if (!is_numeric($value)) $multiseat[$key]=0;
         }
         $event['event_seats'] = eme_convert_array2multi($multiseat);
      } else {
         if (!is_numeric($event['event_seats'])) $event['event_seats'] = 0;
      }
      
      $event['use_paypal'] = (isset ($_POST['use_paypal']) && is_numeric($_POST['use_paypal'])) ? $_POST['use_paypal']:0;
      $event['use_2co'] = (isset ($_POST['use_2co']) && is_numeric($_POST['use_2co'])) ? $_POST['use_2co']:0;
      $event['use_webmoney'] = (isset ($_POST['use_webmoney']) && is_numeric($_POST['use_webmoney'])) ? $_POST['use_webmoney']:0;
      $event['use_google'] = (isset ($_POST['use_google']) && is_numeric($_POST['use_google'])) ? $_POST['use_google']:0;
      $event['price'] = isset ($_POST['price']) ? $_POST['price']:0;
      if (preg_match("/\|\|/",$event['price'])) {
         $multiprice=preg_split("/\|\|/",$event['price']);
         foreach ($multiprice as $key=>$value) {
            if (!is_numeric($value)) $multiprice[$key]=0;
         }
         $event['price'] = eme_convert_array2multi($multiprice);
      } else {
         if (!is_numeric($event['price'])) $event['price'] = 0;
      }

      $event['currency'] = isset ($_POST['currency']) ? $_POST['currency']:"";

      if (isset ( $_POST['event_contactperson_id'] ) && $_POST['event_contactperson_id'] != '') {
         $event['event_contactperson_id'] = $_POST['event_contactperson_id'];
      } else {
         $event['event_contactperson_id'] = 0;
      }
      
      //if (! _eme_is_time_valid ( $event_end_time ))
      // $event_end_time = $event_start_time;
      
      $location['location_name'] = isset($_POST['location_name']) ? trim(stripslashes($_POST['location_name'])) : '';
      $location['location_address'] = isset($_POST['location_address']) ? stripslashes($_POST['location_address']) : '';
      $location['location_town'] = isset($_POST['location_town']) ? stripslashes($_POST['location_town']) : '';
      $location['location_latitude'] = isset($_POST['location_latitude']) ? $_POST['location_latitude'] : '';
      $location['location_longitude'] = isset($_POST['location_longitude']) ? $_POST['location_longitude'] : '';
      $location['location_author']=$current_userid;
      $location['location_description'] = "";
      //switched to WP TinyMCE field
      //$event['event_notes'] = stripslashes ( $_POST['event_notes'] );
      $event['event_notes'] = isset($_POST['content']) ? stripslashes($_POST['content']) : '';
      $event['event_page_title_format'] = isset($_POST['event_page_title_format']) ? stripslashes ( $_POST['event_page_title_format'] ) : '';
      $event['event_single_event_format'] = isset($_POST['event_single_event_format']) ? stripslashes ( $_POST['event_single_event_format'] ) : '';
      $event['event_contactperson_email_body'] = isset($_POST['event_contactperson_email_body']) ? stripslashes ( $_POST['event_contactperson_email_body'] ) : '';
      $event['event_registration_recorded_ok_html'] = isset($_POST['event_registration_recorded_ok_html']) ? stripslashes ( $_POST['event_registration_recorded_ok_html'] ) : '';
      $event['event_respondent_email_body'] = isset($_POST['event_respondent_email_body']) ? stripslashes ( $_POST['event_respondent_email_body'] ) : '';
      $event['event_registration_pending_email_body'] = isset($_POST['event_registration_pending_email_body']) ? stripslashes ( $_POST['event_registration_pending_email_body'] ) : '';
      $event['event_registration_updated_email_body'] = isset($_POST['event_registration_updated_email_body']) ? stripslashes ( $_POST['event_registration_updated_email_body'] ) : '';
      $event['event_registration_form_format'] = isset($_POST['event_registration_form_format']) ? stripslashes ( $_POST['event_registration_form_format'] ) : '';
      $event['event_cancel_form_format'] = isset($_POST['event_cancel_form_format']) ? stripslashes ( $_POST['event_cancel_form_format'] ) : '';
      $event['event_url'] = isset($_POST['event_url']) ? eme_strip_tags ( $_POST['event_url'] ) : '';
      $event['event_image_url'] = isset($_POST['event_image_url']) ? eme_strip_tags ( $_POST['event_image_url'] ) : '';
      $event['event_image_id'] = isset($_POST['event_image_id']) ? intval ( $_POST['event_image_id'] ) : 0;
      $event['event_slug'] = isset($_POST['event_slug']) ? eme_permalink_convert(eme_strip_tags ( $_POST['event_slug'] )) : eme_permalink_convert($event['event_name']);
      if (isset ($_POST['event_category_ids'])) {
         // the category id's need to begin and end with a comma
         // this is needed so we can later search for a specific
         // cat using LIKE '%,$cat,%'
         $event['event_category_ids']="";
         foreach ($_POST['event_category_ids'] as $cat) {
            if (is_numeric($cat)) {
               if (empty($event['event_category_ids'])) {
                  $event['event_category_ids'] = "$cat";
               } else {
                  $event['event_category_ids'] .= ",$cat";
               }
            }
         }
      } else {
         $event['event_category_ids']="";
      }
      
      $event_attributes = array();
      for($i=1 ; isset($_POST["mtm_{$i}_ref"]) && trim($_POST["mtm_{$i}_ref"])!='' ; $i++ ) {
         if(trim($_POST["mtm_{$i}_name"]) != '') {
            $event_attributes[$_POST["mtm_{$i}_ref"]] = stripslashes($_POST["mtm_{$i}_name"]);
         }
      }
      $event['event_attributes'] = serialize($event_attributes);

      $event_properties = array();
      $event_properties = eme_init_event_props($event_properties);
      foreach($_POST as $key=>$value) {
         if (preg_match('/eme_prop_(.+)/', $key, $matches)) {
            $event_properties[$matches[1]] = stripslashes($value);
         }
      }
      $event['event_properties'] = serialize($event_properties);
      
      $validation_result = eme_validate_event ( $event );
      if ($validation_result != "OK") {
         // validation unsuccessful       
         echo "<div id='message' class='error '>
                  <p>" . __ ( "Ach, there's a problem here:", "eme" ) . " $validation_result</p>
              </div>";
         eme_event_form ( $event, "Edit event $event_ID", $event_ID );
         return;
      }

      // validation successful
      if(isset($_POST['location-select-id']) && $_POST['location-select-id'] != "") {
         $event['location_id'] = $_POST['location-select-id'];
      } else {
         if (empty($location['location_name']) && empty($location['location_address']) && empty($location['location_town'])) {
            $event['location_id'] = 0;
         } else {
            $related_location = eme_get_identical_location ( $location );
            // print_r($related_location); 
            if ($related_location) {
               $event['location_id'] = $related_location['location_id'];
            } else {
               $new_location = eme_insert_location ( $location );
               if (!$new_location) {
                  echo "<div id='message' class='error '>
                        <p>" . __ ( "Could not create the new location for this event: either you don't have the right to insert locations or there's a DB problem.", "eme" ) . "</p>
                        </div>";
                  return;
               }
               $event['location_id'] = $new_location['location_id'];
            }
         }
      }
      if (! $event_ID && ! $recurrence_ID) {
         $event['event_author']=$current_userid;
         // new event or new recurrence
         if (isset($_POST['repeated_event']) && $_POST['repeated_event']) {
            //insert new recurrence
            if (!eme_db_insert_recurrence ( $event, $recurrence )) {
               $feedback_message = __ ( 'Database insert failed!', 'eme' );
            } else {
               $feedback_message = __ ( 'New recurrent event inserted!', 'eme' );
               //if (has_action('eme_insert_event_action')) do_action('eme_insert_event_action',$event);
            }
         } else {
            // INSERT new event 
            if (!eme_db_insert_event($event)) {
               $feedback_message = __ ( 'Database insert failed!', 'eme' );
            } else {
               $feedback_message = __ ( 'New event successfully inserted!', 'eme' );
            }
         }
      } else {
         // something exists
         if ($recurrence_ID) {
            $tmp_recurrence = eme_get_recurrence ( $recurrence_ID );
            if (current_user_can( get_option('eme_cap_edit_events')) ||
                (current_user_can( get_option('eme_cap_author_event')) && ($tmp_recurrence['event_author']==$current_userid || $tmp_recurrence['event_contactperson_id']==$current_userid))) {
               // UPDATE old recurrence
               $recurrence['recurrence_id'] = $recurrence_ID;
               //print_r($recurrence); 
               if (eme_db_update_recurrence ($event, $recurrence )) {
                  $feedback_message = __ ( 'Recurrence updated!', 'eme' );
                  //if (has_action('eme_update_event_action')) do_action('eme_update_event_action',$event);
               } else {
                  $feedback_message = __ ( 'Something went wrong with the recurrence update...', 'eme' );
               }
            } else {
               $feedback_message = sprintf(__("You have no right to update '%s'",'eme'),$tmp_event['event_name']);
            }
         } else {
            $tmp_event = eme_get_event ( $event_ID );
            if (current_user_can( get_option('eme_cap_edit_events')) ||
                (current_user_can( get_option('eme_cap_author_event')) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {
               if (isset($_POST['repeated_event']) && $_POST['repeated_event']) {
                  // we go from single event to recurrence: create the recurrence and delete the single event
                  eme_db_insert_recurrence ( $event, $recurrence );
                  eme_db_delete_event ( $tmp_event );
                  $feedback_message = __ ( 'New recurrent event inserted!', 'eme' );
                  //if (has_action('eme_insert_event_action')) do_action('eme_insert_event_action',$event);
               } else {
                  // UPDATE old event
                  // unlink from recurrence in case it was generated by one
                  $event['recurrence_id'] = 0;
                  if (eme_db_update_event ($event,$event_ID)) {
                     $feedback_message = sprintf(__("Updated '%s'",'eme'),$event['event_name']);
                  } else {
                     $feedback_message = sprintf(__("Failed to update '%s'",'eme'),$event['event_name']);
                  }
                  //if (has_action('eme_update_event_action')) do_action('eme_update_event_action',$event);
               }
            } else {
               $feedback_message = sprintf(__("You have no right to update '%s'",'eme'),$tmp_event['event_name']);
            }
         }
      }
         
      //$wpdb->query($sql); 
      echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
      eme_events_table ();
      return;
   }

   if ($action == 'edit_event') {
      if (! $event_ID) {
         if (current_user_can( get_option('eme_cap_add_event'))) {
            $title = __ ( "Insert New Event", 'eme' );
            eme_event_form ( $event, $title, $event_ID );
         } else {
            $feedback_message = __('You have no right to add events!','eme');
            echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
            eme_events_table ();
         }
      } else {
         $event = eme_get_event ( $event_ID );
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            // UPDATE event
            $title = sprintf(__("Edit Event '%s'",'eme'),$event['event_name']);
            eme_event_form ( $event, $title, $event_ID );
         } else {
            $feedback_message = sprintf(__("You have no right to update '%s'",'eme'),$event['event_name']);
            echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
            eme_events_table ();
         }
      }
      return;
   }

   //Add duplicate event if requested
   if ($action == 'duplicate_event') {
      $event = eme_get_event ( $event_ID );
      // make it look like a new event
      unset($event['event_id']);
      unset($event['recurrence_id']);
      $event['event_name'].= __(" (Copy)","eme");

      if (current_user_can( get_option('eme_cap_edit_events')) ||
          (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
         $title = sprintf(__("Edit event copy '%s'",'eme'),$event['event_name']);
         eme_event_form ( $event, $title, 0 );
      } else {
         $feedback_message = sprintf(__("You have no right to copy '%s'",'eme'),$event['event_name']);
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         eme_events_table ();
      }
      return;
   }

   if ($action == 'edit_recurrence') {
      $recurrence = eme_get_recurrence ( $recurrence_ID );
      if (current_user_can( get_option('eme_cap_edit_events')) ||
          (current_user_can( get_option('eme_cap_author_event')) && ($recurrence['event_author']==$current_userid || $recurrence['event_contactperson_id']==$current_userid))) {
         $title = __ ( "Reschedule", 'eme' ) . " '" . $recurrence['event_name'] . "'";
         eme_event_form ( $recurrence, $title, $recurrence_ID );
      } else {
         $feedback_message = __('You have no right to update','eme'). " '" . $recurrence['event_name'] . "' !";
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         eme_events_table ();
      }
      return;
   }
   
   if ($action == "-1" || $action == "") {
      // No action, only showing the events list
      $scope = isset($_GET['scope']) ? $_GET['scope'] : '';
      switch ($scope) {
         case "past" :
            $title = __ ( 'Past Events', 'eme' );
            break;
         case "all" :
            $title = __ ( 'All Events', 'eme' );
            break;
         default :
            $title = __ ( 'Future Events', 'eme' );
            $scope = "future";
      }

      eme_events_table ( $scope );
      return;
   }
}

// array of all pages, bypasses the filter I set up :)
function eme_get_all_pages() {
   global $wpdb;
   $query = "SELECT id, post_title FROM " . $wpdb->prefix . "posts WHERE post_type = 'page' AND post_status='publish'";
   $pages = $wpdb->get_results ( $query, ARRAY_A );
   // get_pages() is better, but uses way more memory and it might be filtered by eme_filter_get_pages()
   //$pages = get_pages();
   $output = array ();
   $output[] = __( 'Please select a page','eme' );
   foreach ( $pages as $page ) {
      $output[$page['id']] = $page['post_title'];
   // $output[$page->ID] = $page->post_title;
   }
   return $output;
}

//This is the content of the event page
function eme_events_page_content() {
   global $wpdb;

   $format_header = eme_replace_placeholders(get_option('eme_event_list_item_format_header' ));
   $format_header = ( $format_header != '' ) ?  $format_header : "<ul class='eme_events_list'>";
   $format_footer = eme_replace_placeholders(get_option('eme_event_list_item_format_footer' ));
   $format_footer = ( $format_footer != '' ) ?  $format_footer : "</ul>";

   if (get_query_var('eme_pmt_result') && get_option('eme_payment_show_custom_return_page')) {
      // show the result of a payment
      $result=get_query_var('eme_pmt_result');
      if ($result == 'succes') {
         $format = get_option('eme_payment_succes_format');
      } else {
         $format = get_option('eme_payment_fail_format');
      }
      if (get_option('eme_payment_add_bookingid_to_return') && get_query_var('eme_pmt_id') && get_query_var('event_id')) {
         $event = eme_get_event(intval(get_query_var('event_id')));
         $booking = eme_get_booking(intval(get_query_var('eme_pmt_id')));
         return eme_replace_booking_placeholders($format,$event,$booking);
      } elseif (get_query_var('event_id')) {
         $event = eme_get_event(intval(get_query_var('event_id')));
         return eme_replace_placeholders($format,$event);
      } else {
         return $format;
      }
   } elseif (get_query_var('eme_pmt_id')) {
      $page_body = eme_payment_form("",get_query_var('eme_pmt_id'));
      return $page_body;
   }

   if (get_query_var('eme_town')) {
      $eme_town=eme_sanitize_request(get_query_var('eme_town'));
      $location_ids = join(',',eme_get_town_location_ids($eme_town));
      $stored_format = get_option('eme_event_list_item_format');
      if (count($location_ids)>0) {
         $format_header = eme_replace_placeholders(get_option('eme_location_list_item_format_header' ));
         $format_header = ( $format_header != '' ) ?  $format_header : "<ul class='eme_events_list'>";
         $format_footer = eme_replace_placeholders(get_option('eme_location_list_item_format_footer' ));
         $format_footer = ( $format_footer != '' ) ?  $format_footer : "</ul>";
         $page_body = $format_header . eme_get_events_list ( get_option('eme_event_list_number_items' ), "future", "ASC", $stored_format, 0, '','',0,'','',0,$location_ids) .  $format_footer;
      } else {
         $page_body = "<div id='events-no-events'>" . get_option('eme_no_events_message') . "</div>";
      }
      return $page_body;
   }
   if (get_query_var('location_id')) {
      $location = eme_get_location ( intval(get_query_var('location_id')));
      $single_location_format = get_option('eme_single_location_format' );
      $page_body = eme_replace_locations_placeholders ( $single_location_format, $location );
      return $page_body;
   }
   if (!get_query_var('calendar_day') && get_query_var('eme_event_cat')) {
      $eme_event_cat=eme_sanitize_request(get_query_var('eme_event_cat'));
      $cat_ids = join(',',eme_get_category_ids($eme_event_cat));
      $stored_format = get_option('eme_event_list_item_format');
      if (!empty($cat_ids)) {
         $page_body = $format_header . eme_get_events_list ( get_option('eme_event_list_number_items' ), "future", "ASC", $stored_format, 0, $cat_ids) .  $format_footer;
      } else {
         $page_body = "<div id='events-no-events'>" . get_option('eme_no_events_message') . "</div>";
      }
      return $page_body;
   }
   //if (isset ( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] != '') {
   if (eme_is_single_event_page()) {
      // single event page
      $event_ID = intval(get_query_var('event_id'));
      $event = eme_get_event ( $event_ID );
      $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
      //$page_body = eme_replace_placeholders ( $single_event_format, $event, 'stop' );
      if (count($event) > 0 && ($event['event_status'] == STATUS_PRIVATE && is_user_logged_in() || $event['event_status'] != STATUS_PRIVATE))
         $page_body = eme_replace_placeholders ( $single_event_format, $event );
      return $page_body;
   } elseif (get_query_var('calendar_day')) {
      $scope = eme_sanitize_request(get_query_var('calendar_day'));
      $events_N = eme_events_count_for ( $scope );
      $location_id = isset( $_GET['location_id'] ) ? urldecode($_GET['location_id']) : '';
      $category = isset( $_GET['category'] ) ? urldecode($_GET['category']) : '';
      $notcategory = isset( $_GET['notcategory'] ) ? urldecode($_GET['notcategory']) : '';
      $author = isset( $_GET['author'] ) ? urldecode($_GET['author']) : '';
      $contact_person = isset( $_GET['contact_person'] ) ? urldecode($_GET['contact_person']) : '';

      if ($events_N > 1) {
         $event_list_item_format = get_option('eme_event_list_item_format' );
         //Add headers and footers to the events list
         $page_body = $format_header . eme_get_events_list( 0, $scope, "ASC", $event_list_item_format, $location_id,$category,'',0, $author, $contact_person, 0,'',0,1,0, $notcategory ) . $format_footer;
      } else {
         # there's only one event for that day, so we show that event, but only if the event doesn't point to an external url
         $events = eme_get_events ( 0, $scope);
         $event = $events[0];
         if ($event['event_url'] != '') {
            $event_list_item_format = get_option('eme_event_list_item_format' );
            //Add headers and footers to the events list
            $page_body = $format_header . eme_get_events_list( 0, $scope, "ASC", $event_list_item_format, $location_id,$category,'',0, $author, $contact_person, 0,'',0,1,0, $notcategory ) . $format_footer;
         } else {
            $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
            $page_body = eme_replace_placeholders ( $single_event_format, $event );
         }
      }
      return $page_body;
   } else {
      // Multiple events page
      (isset($_GET['scope'])) ? $scope = eme_sanitize_request($_GET['scope']) : $scope = "future";
      $stored_format = get_option('eme_event_list_item_format' );
      if (get_option('eme_display_calendar_in_events_page' )){
         $page_body = eme_get_calendar ('full=1');
      }else{
         $page_body = $format_header . eme_get_events_list ( get_option('eme_event_list_number_items' ), $scope, "ASC", $stored_format, 0 ) . $format_footer;
      }
      return $page_body;
   }
}

function eme_events_count_for($date) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   $conditions = array ();
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions[] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $conditions[] = "event_status=".STATUS_PUBLIC;
      }
   }
   $conditions[] = "((event_start_date  like '$date') OR (event_start_date <= '$date' AND event_end_date >= '$date'))";
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   $sql = "SELECT COUNT(*) FROM  $table_name $where";
   return $wpdb->get_var ( $sql );
}

// filter function to call the event page when appropriate
function eme_filter_events_page($data) {
 global $wp_current_filter;

   // we need to make sure we do this only once. Reason being: other plugins can call the_content as well
   // Suppose you add a shortcode from another plugin to the detail part of an event and that other plugin
   // calls apply_filter('the_content'), then this would cause recursion since that call would call our filter again
   // If the_content is the current filter definition (last element in the array), when there's more than one
   // (this is possible since one filter can call another, apply_filters does this), we can be in such a loop
   // And since our event content is only meant to be shown as content of a page (the_content is then the only element
   // in the $wp_current_filter array), we can then skip it
   //print_r($wp_current_filter);
   $eme_count_arr=array_count_values($wp_current_filter);
   $eme_event_parsed=0;
   $eme_loop_protection=get_option('eme_loop_protection');
   switch ($eme_loop_protection) {
      case "default":
         if (count($wp_current_filter)>1 && end($wp_current_filter)=='the_content')
            $eme_event_parsed=1;
         break;
      case "older":
         if (count($wp_current_filter)>1 && end($wp_current_filter)=='the_content' && $eme_count_arr['the_content']>1)
            $eme_event_parsed=1;
         break;
      case "desperate":
         if ((count($wp_current_filter)>1 && end($wp_current_filter)=='the_content') || $eme_count_arr['the_content']>1)
            $eme_event_parsed=1;
         break;
   }
   // we change the content of the page only if we're "in the loop",
   // otherwise this filter also gets applied if e.g. a widget calls
   // the_content or the_excerpt to get the content of a page
   if (in_the_loop() && eme_is_events_page() && !$eme_event_parsed) {
      return eme_events_page_content ();
   } else {
      return $data;
   }
}
add_filter ( 'the_content', 'eme_filter_events_page' );

function eme_page_title($data) {
   $events_page_id = get_option('eme_events_page' );
   $events_page = get_page ( $events_page_id );
   $events_page_title = $events_page->post_title;

   // make sure we only replace the title for the events page, not anything
   // from the menu (which is also in the loop ...)
   if (($data == $events_page_title) && in_the_loop() && eme_is_events_page()) {
      if (get_query_var('calendar_day')) {
         
         $date = eme_sanitize_request(get_query_var('calendar_day'));
         $events_N = eme_events_count_for ( $date );
         
         if ($events_N == 1) {
            $events = eme_get_events ( 0, eme_sanitize_request(get_query_var('calendar_day')));
            $event = $events[0];
            $stored_page_title_format = ( $event['event_page_title_format'] != '' ) ? $event['event_page_title_format'] : get_option('eme_event_page_title_format' );
            $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
            return $page_title;
         }
      }
      
      if (eme_is_single_event_page()) {
         // single event page
         $event_ID = intval(get_query_var('event_id'));
         $event = eme_get_event ( $event_ID );
         if (isset( $event['event_page_title_format']) && ( $event['event_page_title_format'] != '' )) {
            $stored_page_title_format = $event['event_page_title_format'];
         } else {
            $stored_page_title_format = get_option('eme_event_page_title_format' );
         }
         $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
         return $page_title;
      } elseif (eme_is_single_location_page()) {
         $location = eme_get_location ( intval(get_query_var('location_id')));
         $stored_page_title_format = get_option('eme_location_page_title_format' );
         $page_title = eme_replace_locations_placeholders ( $stored_page_title_format, $location );
         return $page_title;
      } else {
         // Multiple events page
         $page_title = get_option('eme_events_page_title' );
         return $page_title;
      }
   } else {
      return $data;
   }
}

function eme_html_title($data) {
   //$events_page_id = get_option('eme_events_page' );
   if (eme_is_events_page()) {
      if (get_query_var('calendar_day')) {
         
         $date = eme_sanitize_request(get_query_var('calendar_day'));
         $events_N = eme_events_count_for ( $date );
         
         if ($events_N == 1) {
            $events = eme_get_events ( 0, eme_sanitize_request(get_query_var('calendar_day')));
            $event = $events[0];
            $stored_html_title_format = get_option('eme_event_html_title_format' );
            $html_title = eme_strip_tags(eme_replace_placeholders ( $stored_html_title_format, $event ));
            return $html_title;
         }
      }
      if (eme_is_single_event_page()) {
         // single event page
         $event_ID = intval(get_query_var('event_id'));
         $event = eme_get_event ( $event_ID );
         $stored_html_title_format = get_option('eme_event_html_title_format' );
         $html_title = eme_strip_tags(eme_replace_placeholders ( $stored_html_title_format, $event ));
         return $html_title;
      } elseif (eme_is_single_location_page()) {
         $location = eme_get_location ( intval(get_query_var('location_id')));
         $stored_html_title_format = get_option('eme_location_html_title_format' );
         $html_title = eme_strip_tags(eme_replace_locations_placeholders ( $stored_html_title_format, $location ));
         return $html_title;
      } else {
         // Multiple events page
         $html_title = get_option('eme_events_page_title' );
         return $html_title;
      }
   } else {
      return $data;
   }
}

// the filter single_post_title influences the html header title and the page title
// we want to prevent html tags in the html header title (if you add html in the 'single event title format', it will show)
add_filter ( 'single_post_title', 'eme_html_title' );
add_filter ( 'the_title', 'eme_page_title' );

function eme_template_redir() {
# We need to catch the request as early as possible, but
# since it needs to be working for both permalinks and normal,
# I can't use just any action hook. parse_query seems to do just fine
   if (get_query_var('event_id')) {
      $event_id = intval(get_query_var('event_id'));
      if (!eme_check_event_exists($event_id)) {
//         header('Location: '.home_url('404.php'));
         status_header(404);
         nocache_headers();
         include( get_404_template() );
         exit;
      }
   }
   if (get_query_var('location_id')) {
      $location_id = intval(get_query_var('location_id'));
      if (!eme_check_location_exists($location_id)) {
//         header('Location: '.home_url('404.php'));
         status_header(404);
         nocache_headers();
         include( get_404_template() );
         exit;
      }
   }

   // Enqueing jQuery script to make sure it's loaded
   wp_enqueue_script ( 'jquery' );
}

// filter out the events page in the get_pages call
function eme_filter_get_pages($data) {
   //$output = array ();
   $events_page_id = get_option('eme_events_page' );
   $list_events_page = get_option('eme_list_events_page' );
   // if we want the page to be shown, just return everything unfiltered
   if ($list_events_page) {
      return $data;
   } else {
      foreach ($data as $key => $item) {
         if ($item->ID == $events_page_id) {
            //$output[] = $item;
            unset($data[$key]);
         }
      }
      //return $output;
      return $data;
   }
}
add_filter ( 'get_pages', 'eme_filter_get_pages' );

//filter out the events page in the admin section
function exclude_this_page( $query ) {
   if( !is_admin() )
      return $query;

   global $pagenow;
   $events_page_id = get_option('eme_events_page' );

   if( 'edit.php' == $pagenow && ( get_query_var('post_type') && 'page' == get_query_var('post_type') ) )
      $query->set( 'post__not_in', array($events_page_id) );
   return $query;
}

// TEMPLATE TAGS

// exposed function, for theme  makers
   //Added a category option to the get events list method and shortcode
function eme_get_events_list($limit, $scope = "future", $order = "ASC", $format = '', $echo = 1, $category = '',$showperiod = '', $long_events = 0, $author = '', $contact_person='', $paging=0, $location_id = "", $user_registered_only = 0, $show_ongoing=1, $link_showperiod=0, $notcategory = '', $show_recurrent_events_once= 0, $template_id = 0, $template_id_header=0, $template_id_footer=0) {
   global $post;
   if ($limit === "") {
      $limit = get_option('eme_event_list_number_items' );
   }
   if (strpos ( $limit, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $eme_event_list_number_events=get_option('eme_event_list_number_items' );
      $defaults = array ('limit' => $eme_event_list_number_events, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'echo' => 1 , 'category' => '', 'showperiod' => '', $author => '', $contact_person => '', 'paging'=>0, 'long_events' => 0, 'location_id' => 0, 'show_ongoing' => 1, 'link_showperiod' => 0, 'notcategory' => '', 'show_recurrent_events_once' => 0, 'template_id' => 0, 'template_id_header' => 0, 'template_id_footer' => 0);
      $r = wp_parse_args ( $limit, $defaults );
      extract ( $r );
      // for AND categories: the user enters "+" and this gets translated to " " by wp_parse_args
      // so we fix it again
      $category = preg_replace("/ /","+",$category);
   }
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $long_events = ($long_events==="true" || $long_events==="1") ? true : $long_events;
   $paging = ($paging==="true" || $paging==="1") ? true : $paging;
   $show_ongoing = ($show_ongoing==="true" || $show_ongoing==="1") ? true : $show_ongoing;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $long_events = ($long_events==="false" || $long_events==="0") ? false : $long_events;
   $paging = ($paging==="false" || $paging==="0") ? false : $paging;
   $show_ongoing = ($show_ongoing==="false" || $show_ongoing==="0") ? false : $show_ongoing;
   if ($scope == "")
      $scope = "future";
   if ($order != "DESC")
      $order = "ASC";

   $eme_format_header="";
   $eme_format_footer="";

   if ($template_id) {
      $format_arr = eme_get_template($template_id);
      $format=$format_arr['format'];
   }
   if ($template_id_header) {
      $format_arr = eme_get_template($template_id_header);
      $format_header = $format_arr['format'];
      $eme_format_header=eme_replace_placeholders($format_header);
   }
   if ($template_id_footer) {
      $format_arr = eme_get_template($template_id_footer);
      $format_footer = $format_arr['format'];
      $eme_format_footer=eme_replace_placeholders($format_footer);
   }
   if (empty($format)) {
      $format = get_option('eme_event_list_item_format' );
      if (empty($eme_format_header)) {
	      $eme_format_header = eme_replace_placeholders(get_option('eme_event_list_item_format_header' ));
	      $eme_format_header = ( $eme_format_header != '' ) ? $eme_format_header : "<ul class='eme_events_list'>";
      }
      if (empty($eme_format_footer)) {
	      $eme_format_footer = eme_replace_placeholders(get_option('eme_event_list_item_format_footer' ));
	      $eme_format_footer = ( $eme_format_footer != '' ) ? $eme_format_footer : "</ul>";
      }
   }

   if ($limit>0 && $paging==1 && isset($_GET['eme_offset'])) {
      $offset=intval($_GET['eme_offset']);
   } else {
      $offset=0;
   }

   // for registered users: we'll add a list of event_id's for that user only
   $extra_conditions = "";
   if ($user_registered_only == 1 && is_user_logged_in()) {
      $current_userid=get_current_user_id();
      $person_id=eme_get_person_id_by_wp_id($current_userid);
      $list_of_event_ids=join(",",eme_get_event_ids_by_booker_id($person_id));
      if (!empty($list_of_event_ids)) {
         $extra_conditions = " (event_id in ($list_of_event_ids))";
      } else {
         // user has no registered events, then make sure none are shown
         $extra_conditions = " (event_id = 0)";
      }
   }

   $prev_text = "";
   $next_text = "";
   // for browsing: if limit=0,paging=1 and only for this_week,this_month or today
   if ($paging==1 && $limit==0) {
      $scope_offset=0;
      if (isset($_GET['eme_offset']))
         $scope_offset=$_GET['eme_offset'];
      $prev_offset=$scope_offset-1;
      $next_offset=$scope_offset+1;
      if ($scope=="this_week") {
         $start_of_week = get_option('start_of_week');
         $day_offset=date('w')-$start_of_week;
         if ($day_offset<0) $day_offset+=7;
         $start_day=time()-$day_offset*86400;
         $end_day=$start_day+6*86400;
         $limit_start = date('Y-m-d',$start_day+$scope_offset*7*86400);
         $limit_end   = date('Y-m-d',$end_day+$scope_offset*7*86400);
         $scope = "$limit_start--$limit_end";
         //$prev_text = date_i18n (get_option('date_format'),$start_day+$prev_offset*7*86400)."--".date_i18n (get_option('date_format'),$end_day+$prev_offset*7*86400);
         //$next_text = date_i18n (get_option('date_format'),$start_day+$next_offset*7*86400)."--".date_i18n (get_option('date_format'),$end_day+$next_offset*7*86400);
         $scope_text = date_i18n (get_option('date_format'),$start_day+$scope_offset*7*86400)." -- ".date_i18n (get_option('date_format'),$end_day+$scope_offset*7*86400);
         $prev_text = __('Previous week','eme');
         $next_text = __('Next week','eme');
      }
      elseif ($scope=="this_month") {
         // "first day of this month, last day of this month" works for newer versions of php (5.3+), but for compatibility:
         // the year/month should be based on the first of the month, so if we are the 13th, we substract 12 days to get to day 1
         // Reason: monthly offsets needs to be calculated based on the first day of the current month, not the current day,
         //    otherwise if we're now on the 31st we'll skip next month since it has only 30 days
         $day_offset=date('j')-1;
         $year=date('Y', strtotime("$scope_offset month")-$day_offset*86400);
         $month=date('m', strtotime("$scope_offset month")-$day_offset*86400);
         $number_of_days_month=eme_days_in_month($month,$year);
         $limit_start = "$year-$month-01";
         $limit_end   = "$year-$month-$number_of_days_month";
         $scope = "$limit_start--$limit_end";
         //$prev_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$prev_offset month")-$day_offset*86400);
         //$next_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$next_offset month")-$day_offset*86400);
         $scope_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$scope_offset month")-$day_offset*86400);
         $prev_text = __('Previous month','eme');
         $next_text = __('Next month','eme');
      }
      elseif ($scope=="this_year") {
         $year=date('Y')+$scope_offset;
         $limit_start = "$year-01-01";
         $limit_end   = "$year-12-31";
         $scope = "$limit_start--$limit_end";
         $scope_text = date_i18n (get_option('eme_show_period_yearly_dateformat'), strtotime($limit_start));
         $prev_text = __('Previous year','eme');
         $next_text = __('Next year','eme');
      }
      elseif ($scope=="today") {
         $scope = date('Y-m-d',strtotime("$scope_offset days"));
         $limit_start = $scope;
         $limit_end   = $scope;
         //$prev_text = date_i18n (get_option('date_format'), strtotime("$prev_offset days"));
         //$next_text = date_i18n (get_option('date_format'), strtotime("$next_offset days"));
         $scope_text = date_i18n (get_option('date_format'), strtotime("$scope_offset days"));
         $prev_text = __('Previous day','eme');
         $next_text = __('Next day','eme');
      }
      elseif ($scope=="tomorrow") {
         $scope_offset++;
         $scope = date('Y-m-d',strtotime("$scope_offset days"));
         $limit_start = $scope;
         $limit_end   = $scope;
         $scope_text = date_i18n (get_option('date_format'), strtotime("$scope_offset days"));
         $prev_text = __('Previous day','eme');
         $next_text = __('Next day','eme');
      }

      // to prevent going on indefinitely and thus allowing search bots to go on for ever,
      // we stop providing links if there are no more events left
      if (eme_count_events_older_than($limit_start) == 0)
         $prev_text = "";
      if (eme_count_events_newer_than($limit_end) == 0)
         $next_text = "";
   }
   // We request $limit+1 events, so we know if we need to show the pagination link or not.
   if ($limit==0) {
      $events = eme_get_events ( 0, $scope, $order, $offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
   } else {
      $events = eme_get_events ( $limit+1, $scope, $order, $offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
   }
   $events_count=count($events);

   // get the paging output ready
   $pagination_top = "<div id='events-pagination-top'> ";
   if ($paging==1 && $limit>0) {
      // for normal paging and there're no events, we go back to offset=0 and try again
      if ($events_count==0) {
         $offset=0;
         $events = eme_get_events ( $limit+1, $scope, $order, $offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $show_recurrent_events_once, $extra_conditions );
         $events_count=count($events);
      }
      $prev_text=__('Previous page','eme');
      $next_text=__('Next page','eme');
      $page_number = floor($offset/$limit) + 1;
      $this_page_url=get_permalink($post->ID);
      //$this_page_url=$_SERVER['REQUEST_URI'];
      // remove the offset info
      $this_page_url= remove_query_arg('eme_offset',$this_page_url);

      // we add possible fields from the filter section
      $eme_filters["eme_eventAction"]=1;
      $eme_filters["eme_cat_filter"]=1;
      $eme_filters["eme_loc_filter"]=1;
      $eme_filters["eme_town_filter"]=1;
      $eme_filters["eme_scope_filter"]=1;
      foreach ($_REQUEST as $key => $item) {
         if (isset($eme_filters[$key])) {
            # if you selected multiple items, $item is an array, but rawurlencode needs a string
            if (is_array($item)) $item=join(',',eme_sanitize_request($item));
            $this_page_url=add_query_arg(array($key=>$item),$this_page_url);
         }
      }

      $left_nav_hidden_class="";
      $right_nav_hidden_class="";
      if ($events_count > $limit) {
         $forward = $offset + $limit;
         $backward = $offset - $limit;
         if ($backward < 0)
            $left_nav_hidden_class="style='visibility:hidden;'";
         $pagination_top.= "<a class='eme_nav_left' $left_nav_hidden_class href='".add_query_arg(array('eme_offset'=>$backward),$this_page_url)."'>&lt;&lt; $prev_text</a>";
         $pagination_top.= "<a class='eme_nav_right' $right_nav_hidden_class href='".add_query_arg(array('eme_offset'=>$forward),$this_page_url)."'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>".__('Page ','eme').$page_number."</span>";
      }
      if ($events_count <= $limit && $offset>0) {
         $forward = 0;
         $backward = $offset - $limit;
         if ($backward < 0)
            $left_nav_hidden_class="style='visibility:hidden;'";
         $right_nav_hidden_class="style='visibility:hidden;'";
         $pagination_top.= "<a class='eme_nav_left' $left_nav_hidden_class href='".add_query_arg(array('eme_offset'=>$backward),$this_page_url) ."'>&lt;&lt; $prev_text</a>";
         $pagination_top.= "<a class='eme_nav_right' $right_nav_hidden_class href='".add_query_arg(array('eme_offset'=>$forward),$this_page_url) ."'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>".__('Page ','eme').$page_number."</span>";
      }
   }
   if ($paging==1 && $limit==0) {
      $this_page_url=$_SERVER['REQUEST_URI'];
      // remove the offset info
      $this_page_url= remove_query_arg('eme_offset',$this_page_url);
      if ($prev_text != "")
         $pagination_top.= "<a class='eme_nav_left' href='".add_query_arg(array('eme_offset'=>$prev_offset),$this_page_url) ."'>&lt;&lt; $prev_text</a>";
      if ($next_text != "")
         $pagination_top.= "<a class='eme_nav_right' href='".add_query_arg(array('eme_offset'=>$next_offset),$this_page_url) ."'>$next_text &gt;&gt;</a>";
      $pagination_top.= "<span class='eme_nav_center'>$scope_text</span>";
   }
   $pagination_top.= "</div>";
   $pagination_bottom = str_replace("events-pagination-top","events-pagination-bottom",$pagination_top);

   $output = "";
   if ($events_count>0) {
      # if we want to show events per period, we first need to determine on which days events occur
      # this code is identical to that in eme_calendar.php for "long events"
      if (! empty ( $showperiod )) {
         $eventful_days= array();
         $i=1;
         foreach ( $events as $event ) {
            // we requested $limit+1 events, so we need to break at the $limit, if reached
            if ($limit>0 && $i>$limit)
               break;
            $event_start_date = strtotime($event['event_start_date']);
            $event_end_date = strtotime($event['event_end_date']);
            if ($event_end_date < $event_start_date)
               $event_end_date=$event_start_date;
            if ($long_events) {
               //Show events on every day that they are still going on
               while( $event_start_date <= $event_end_date ) {
                  $event_eventful_date = date('Y-m-d', $event_start_date);
                  if(isset($eventful_days[$event_eventful_date]) &&  is_array($eventful_days[$event_eventful_date]) ) {
                     $eventful_days[$event_eventful_date][] = $event;
                  } else {
                     $eventful_days[$event_eventful_date] = array($event);
                  }
                  $event_start_date += (60*60*24);
               }
            } else {
               //Only show events on the day that they start
               if ( isset($eventful_days[$event['event_start_date']]) && is_array($eventful_days[$event['event_start_date']]) ) {
                  $eventful_days[$event['event_start_date']][] = $event;
               } else {
                  $eventful_days[$event['event_start_date']] = array($event);
               }
            }
            $i++;
         }

         # now that we now the days on which events occur, loop through them
         $curyear="";
         $curmonth="";
         $curday="";
         foreach($eventful_days as $day_key => $day_events) {
            $theyear = date ("Y", strtotime($day_key));
            $themonth = date ("m", strtotime($day_key));
            $theday = date ("d", strtotime($day_key));
            if ($showperiod == "yearly" && $theyear != $curyear) {
               $output .= "<li class='eme_period'>".date_i18n (get_option('eme_show_period_yearly_dateformat'), strtotime($day_key))."</li>";
               $curyear=$theyear;
            } elseif ($showperiod == "monthly" && $themonth != $curmonth) {
               $output .= "<li class='eme_period'>".date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime($day_key))."</li>";
               $curmonth=$themonth;
            } elseif ($showperiod == "daily" && $theday != $curday) {
               $output .= "<li class='eme_period'>";
               if ($link_showperiod) {
                  $eme_link=eme_calendar_day_url($theyear."-".$themonth."-".$theday);
                  $output .= "<a href=\"$eme_link\">".date_i18n (get_option('date_format'), strtotime($day_key))."</a>";
               } else {
                  $output .= date_i18n (get_option('date_format'), strtotime($day_key));
               }
               $output .= "</li>";
               $curday=$theday;
            }
            foreach($day_events as $event) {
               $output .= eme_replace_placeholders ( $format, $event );
            }
         }
      } else {
         $i=1;
         foreach ( $events as $event ) {
            // we requested $limit+1 events, so we need to break at the $limit, if reached
            if ($limit>0 && $i>$limit)
               break;
            $output .= eme_replace_placeholders ( $format, $event );
            $i++;
         }
      } // end if (! empty ( $showperiod )) {

      //Add headers and footers to output
      $output =  $eme_format_header .  $output . $eme_format_footer;
   } else {
      $output = "<div id='events-no-events'>" . get_option('eme_no_events_message') . "</div>";
   }

   // add the pagination if needed
   if ($paging==1 && $events_count>0)
   	$output = $pagination_top . $output . $pagination_bottom;
  
   // see how to return the output
   if ($echo)
      echo $output;
   else
      return $output;
}

function eme_get_events_list_shortcode($atts) {
   $eme_event_list_number_events=get_option('eme_event_list_number_items' );
   extract ( shortcode_atts ( array ('limit' => $eme_event_list_number_events, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'category' => '', 'showperiod' => '', 'author' => '', 'contact_person' => '', 'paging' => 0, 'long_events' => 0, 'location_id' => 0, 'user_registered_only' => 0, 'show_ongoing' => 1, 'link_showperiod' => 0, 'notcategory' => '', 'show_recurrent_events_once' => 0, 'template_id' => 0, 'template_id_header' => 0, 'template_id_footer' => 0 ), $atts ) );

   // the filter list overrides the settings
   if (isset($_REQUEST['eme_eventAction']) && $_REQUEST['eme_eventAction'] == 'filter') {
      if (isset($_REQUEST['eme_scope_filter'])) {
         $scope = eme_sanitize_request($_REQUEST['eme_scope_filter']);
      }

      if (isset($_REQUEST['eme_loc_filter'])) {
         if (is_array($_REQUEST['eme_loc_filter']))
            $location_id=join(',',eme_sanitize_request($_REQUEST['eme_loc_filter']));
         else
            $location_id=eme_sanitize_request($_REQUEST['eme_loc_filter']);
      }
      if (isset($_REQUEST['eme_town_filter'])) {
         $towns=eme_sanitize_request($_REQUEST['eme_town_filter']);
         if (empty($location_id))
            $location_id = join(',',eme_get_town_location_ids($towns));
         else
            $location_id .= ",".join(',',eme_get_town_location_ids($towns));
      }
      if (isset($_REQUEST['eme_cat_filter'])) {
         if (is_array($_REQUEST['eme_cat_filter']))
            $category=join(',',eme_sanitize_request($_REQUEST['eme_cat_filter']));
         else
            $category=eme_sanitize_request($_REQUEST['eme_cat_filter']);
      }
   }

   // if format is given as argument, sometimes people need url-encoded strings inside so wordpress doesn't get confused, so we decode them here again
   $format = urldecode($format);
   // for format: sometimes people want to give placeholders as options, but when using the shortcode inside
   // another (e.g. when putting[eme_events format="#_EVENTNAME"] inside the "display single event" setting,
   // the replacement of the placeholders happens too soon (placeholders get replaced first, before any other
   // shortcode is interpreted). So we add the option that people can use "#OTHER_", and we replace this with
   // "#_" here
   $format = preg_replace('/#OTHER/', "#", $format);
   $result = eme_get_events_list ( $limit,$scope,$order,$format,0,$category,$showperiod,$long_events,$author,$contact_person,$paging,$location_id,$user_registered_only,$show_ongoing,$link_showperiod,$notcategory,$show_recurrent_events_once,$template_id,$template_id_header,$template_id_footer);
   return $result;
}

function eme_display_single_event($event_id,$template_id=0) {
   $event = eme_get_event ( intval($event_id) );
   if ($template_id) {
      $format_arr = eme_get_template($template_id);
      $single_event_format=$format_arr['format'];
   } else {
      $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
   }
   $page_body = eme_replace_placeholders ($single_event_format, $event);
   return $page_body;
}

function eme_display_single_event_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>'','template_id'=>0), $atts ) );
   return eme_display_single_event($id,$template_id);
}

function eme_get_events_page($justurl = 0, $echo = 1, $text = '') {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'text' => '', 'echo' => 1 );
      
      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
   }
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $justurl = ($justurl==="true" || $justurl==="1") ? true : $justurl;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $justurl = ($justurl==="false" || $justurl==="0") ? false : $justurl;
   
   $page_link = get_permalink ( get_option ( 'eme_events_page' ) );
   if ($justurl) {
      $result = $page_link;
   } else {
      if ($text == '')
         $text = get_option ( 'eme_events_page_title' );
      $result = "<a href='$page_link' title='$text'>$text</a>";
   }
   if ($echo)
      echo $result;
   else
      return $result;
}

function eme_get_events_page_shortcode($atts) {
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => '' ), $atts ) );
   $result = eme_get_events_page ( "justurl=$justurl&text=$text&echo=0" );
   return $result;
}

// API function
function eme_are_events_available($scope = "future",$order = "ASC", $location_id = "", $category = '', $author = '', $contact_person = '') {
   if ($scope == "")
      $scope = "future";
   $events = eme_get_events ( 1, $scope, $order, 0, $location_id, $category, $author, $contact_person);
   
   if (empty ( $events ))
      return FALSE;
   else
      return TRUE;
}

function eme_count_events_older_than($scope) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_start_date<'".$scope."'";
   return $wpdb->get_var($sql);
}

function eme_count_events_newer_than($scope) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_end_date>'".$scope."'";
   return $wpdb->get_var($sql);
}

// main function querying the database event table
function eme_get_events($o_limit, $scope = "future", $order = "ASC", $o_offset = 0, $location_id = "", $category = "", $author = "", $contact_person = "",  $show_ongoing=1, $notcategory = "", $show_recurrent_events_once=0, $extra_conditions = "") {
   global $wpdb;

   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   if ($o_limit === "") {
      $o_limit = get_option('eme_event_list_number_items' );
   }
   if ($o_limit > 0) {
      $limit = "LIMIT ".intval($o_limit);
   } else {
      $limit="";
   }
   if ($o_offset >0) {
      if ($o_limit == 0) {
          $limit = "LIMIT ".intval($o_offset);
      }
      $offset = "OFFSET ".intval($o_offset);
   } else {
      $offset="";
   }

   if ($order != "DESC")
      $order = "ASC";
   
   $today = date("Y-m-d");
   $this_time = date ("H:i:00");
   
   $conditions = array ();
   // extra sql conditions we put in front, most of the time this is the most
   // effective place
   if ($extra_conditions != "") {
      $conditions[] = $extra_conditions;
   }

   // if we're not in the admin itf, we don't want draft events
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions[] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $conditions[] = "event_status=".STATUS_PUBLIC;
      }
      if (get_option('eme_rsvp_hide_full_events')) {
         // COALESCE is used in case the SUM returns NULL
         // this is a correlated subquery, so the FROM clause should specify events_table again, so it will search in the outer query for events_table.event_id
         $conditions[] = "(event_rsvp=0 OR (event_rsvp=1 AND event_seats > (SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE $bookings_table.event_id = $events_table.event_id)))";
      }
   }
   if (preg_match ( "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $scope )) {
      //$conditions[] = " event_start_date like '$scope'";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date LIKE '$scope') OR (event_start_date <= '$scope' AND event_end_date >= '$scope'))";
      else
         $conditions[] = " (event_start_date LIKE '$scope') ";
   } elseif (preg_match ( "/^0000-([0-9]{2})$/", $scope, $matches )) {
      $year=date('Y');
      $month=$matches[1];
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_week") {
      // this comes from global wordpress preferences
      $start_of_week = get_option('start_of_week');
      $day_offset=date('w')-$start_of_week;
      if ($day_offset<0) $day_offset+=7;
      $start_day=time()-$day_offset*86400;
      $end_day=$start_day+6*86400;
      $limit_start = date('Y-m-d',$start_day);
      $limit_end   = date('Y-m-d',$end_day);
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "next_week") {
      // this comes from global wordpress preferences
      $start_of_week = get_option('start_of_week');
      $day_offset=date('w')-$start_of_week;
      if ($day_offset<0) $day_offset+=7;
      $start_day=time()-$day_offset*86400+7*6400;
      $end_day=$start_day+6*86400;
      $limit_start = date('Y-m-d',$start_day);
      $limit_end   = date('Y-m-d',$end_day);
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_month") {
      $year=date('Y');
      $month=date('m');
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_year") {
      $year=date('Y');
      $limit_start = "$year-01-01";
      $limit_end = "$year-12-31";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "next_month") {
      // the year/month should be based on the first of the month, so if we are the 13th, we substract 12 days to get to day 1
      // Reason: monthly offsets needs to be calculated based on the first day of the current month, not the current day,
      //    otherwise if we're now on the 31st we'll skip next month since it has only 30 days
      $day_offset=date('j')-1;
      $year=date('Y', strtotime("+1 month")-$day_offset*86400);
      $month=date('m', strtotime("+1 month")-$day_offset*86400);
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $limit_start=$matches[1];
      $limit_end=$matches[2];
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--today$/", $scope, $matches )) {
      $limit_start=$matches[1];
      $limit_end=$today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^today--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $limit_start=$today;
      $limit_end=$matches[1];
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\+(\d+)d$/", $scope, $matches )) {
      $limit_start = $today;
      $limit_end=date('Y-m-d',time()+$matches[1]*86400);
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\-(\d+)d$/", $scope, $matches )) {
      $limit_end = $today;
      $limit_start=date('Y-m-d',time()-$matches[1]*86400);
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^(\-?\+?\d+)d--(\-?\+?\d+)d$/", $scope, $matches )) {
      $limit_start=date('Y-m-d',time()+$matches[1]*86400);
      $limit_end=date('Y-m-d',time()+$matches[2]*86400);
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\+(\d+)m$/", $scope, $matches )) {
      // the year/month should be based on the first of the month, so if we are the 13th, we substract 12 days to get to day 1
      // Reason: monthly offsets needs to be calculated based on the first day of the current month, not the current day,
      //    otherwise if we're now on the 31st we'll skip next month since it has only 30 days
      $day_offset=date('j')-1;
      $months_in_future=$matches[1]++;
      $start_year=date('Y', strtotime("+1 month")-$day_offset*86400);
      $start_month=date('m', strtotime("+1 month")-$day_offset*86400);
      $limit_start = "$start_year-$start_month-01";
      $end_year=date('Y', strtotime("+$months_in_future month")-$day_offset*86400);
      $end_month=date('m', strtotime("+$months_in_future month")-$day_offset*86400);
      $number_of_days_month=eme_days_in_month($end_month,$end_year);
      $limit_end = "$end_year-$end_month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\-(\d+)m$/", $scope, $matches )) {
      // the year/month should be based on the first of the month, so if we are the 13th, we substract 12 days to get to day 1
      // Reason: monthly offsets needs to be calculated based on the first day of the current month, not the current day,
      //    otherwise if we're now on the 31st we'll skip next month since it has only 30 days
      $day_offset=date('j')-1;
      $months_in_past=$matches[1]++;
      $start_year=date('Y', strtotime("-$months_in_past month")-$day_offset*86400);
      $start_month=date('m', strtotime("-$months_in_past month")-$day_offset*86400);
      $limit_start = "$start_year-$start_month-01";
      $end_year=date('Y', strtotime("-1 month")-$day_offset*86400);
      $end_month=date('m', strtotime("-1 month")-$day_offset*86400);
      $number_of_days_month=eme_days_in_month($end_month,$end_year);
      $limit_end = "$end_year-$end_month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^(\-?\+?\d+)m--(\-?\+?\d+)m$/", $scope, $matches )) {
      $day_offset=date('j')-1;
      $start_year=date('Y', strtotime("$matches[1] month")-$day_offset*86400);
      $start_month=date('m', strtotime("$matches[1] month")-$day_offset*86400);
      $limit_start = "$start_year-$start_month-01";
      $end_year=date('Y', strtotime("$matches[2] month")-$day_offset*86400);
      $end_month=date('m', strtotime("$matches[2] month")-$day_offset*86400);
      $number_of_days_month=eme_days_in_month($end_month,$end_year);
      $limit_end = "$end_year-$end_month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_week") {
      $start_of_week = get_option('start_of_week');
      $day_offset=date('w')-$start_of_week;
      if ($day_offset<0) $day_offset+=7;
      $start_day=time()-$day_offset*86400;
      $end_day=$start_day+6*86400;
      $limit_start = $today;
      $limit_end   = date('Y-m-d',$end_day);
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_week_plus_one") {
      $start_of_week = get_option('start_of_week');
      $day_offset=date('w')-$start_of_week;
      if ($day_offset<0) $day_offset+=7;
      $start_day=time()-$day_offset*86400;
      $end_day=$start_day+7*86400;
      $limit_start = $today;
      $limit_end   = date('Y-m-d',$end_day);
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
    } elseif ($scope == "today--this_month") {
      $year=date('Y');
      $month=date('m');
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = $today;
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_year") {
      $limit_start = $today;
      $limit_end = "$year-12-31";
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
    } elseif ($scope == "this_week--today") {
      $start_of_week = get_option('start_of_week');
      $day_offset=date('w')-$start_of_week;
      if ($day_offset<0) $day_offset+=7;
      $start_day=time()-$day_offset*86400;
      $limit_start = date('Y-m-d',$start_day);
      $limit_end = $today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_month--today") {
      $year=date('Y');
      $month=date('m');
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = $today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_year--today") {
      $limit_start = "$year-01-01";
      $limit_end = $today;
      if ($show_ongoing)
         $conditions[] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions[] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "tomorrow--future") {
      if ($show_ongoing)
         $conditions[] = " (event_start_date > '$today' OR (event_end_date > '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL))";
      else
         $conditions[] = " (event_start_date > '$today')";
   } elseif ($scope == "past") {
      //$conditions[] = " (event_end_date < '$today' OR (event_end_date = '$today' and event_end_time < '$this_time' )) ";
      // not taking the hour into account until we can enter timezone info as well
      if ($show_ongoing)
         $conditions[] = " event_end_date < '$today'";
      else
         $conditions[] = " event_start_date < '$today'";
   } elseif ($scope == "today") {
      if ($show_ongoing)
         $conditions[] = " (event_start_date = '$today' OR (event_start_date <= '$today' AND event_end_date >= '$today'))";
      else
         $conditions[] = " (event_start_date = '$today')";
   } elseif ($scope == "tomorrow") {
      $tomorrow = date("Y-m-d",strtotime($today)+86400);
      if ($show_ongoing)
         $conditions[] = " (event_start_date = '$tomorrow' OR (event_start_date <= '$tomorrow' AND event_end_date >= '$tomorrow'))";
      else
         $conditions[] = " (event_start_date = '$tomorrow')";
   } else {
      if ($scope != "all")
         $scope = "future";
      if ($scope == "future") {
         //$conditions[] = " ((event_start_date = '$today' AND event_start_time >= '$this_time') OR (event_start_date > '$today') OR (event_end_date > '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL) OR (event_end_date = '$today' AND event_end_time >= '$this_time'))";
         // not taking the hour into account until we can enter timezone info as well
         if ($show_ongoing)
            $conditions[] = " (event_start_date >= '$today' OR (event_end_date >= '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL))";
         else
            $conditions[] = " (event_start_date >= '$today')";
      }
   }
   
   // when used inside a location description, you can use this_location to indicate the current location being viewed
   if ($location_id == "this_location" && get_query_var('location_id')) {
      $location_id = get_query_var('location_id');
   }

   if (is_numeric($location_id)) {
      if ($location_id>0)
         $conditions[] = " location_id = $location_id";
   } elseif ($location_id == "none") {
      $conditions[] = " location_id = ''";
   } elseif ( preg_match('/,/', $location_id) ) {
      $location_ids=explode(',', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0) {
            $location_conditions[] = " location_id = $loc";
         } elseif ($loc == "none") {
            $location_conditions[] = " location_id = ''";
         }
      }
      $conditions[] = "(".implode(' OR', $location_conditions).")";
   } elseif ( preg_match('/\+/', $location_id) ) {
      $location_ids=explode('+', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0)
               $location_conditions[] = " location_id = $loc";
         }
         $conditions[] = "(".implode(' AND', $location_conditions).")";
   } elseif ( preg_match('/ /', $location_id) ) {
      // url decoding of '+' is ' '
      $location_ids=explode(' ', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0)
               $location_conditions[] = " location_id = $loc";
         }
         $conditions[] = "(".implode(' AND', $location_conditions).")";
   }

   if (get_option('eme_categories_enabled')) {
      if (is_numeric($category)) {
         if ($category>0)
            $conditions[] = " FIND_IN_SET($category,event_category_ids)";
      } elseif ($category == "none") {
         $conditions[] = "event_category_ids = ''";
      } elseif ( preg_match('/,/', $category) ) {
         $category = explode(',', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0) {
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
            } elseif ($cat == "none") {
               $category_conditions[] = " event_category_ids = ''";
            }
         }
         $conditions[] = "(".implode(' OR', $category_conditions).")";
      } elseif ( preg_match('/\+/', $category) ) {
         $category = explode('+', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions[] = "(".implode(' AND ', $category_conditions).")";
      } elseif ( preg_match('/ /', $category) ) {
         // url decoding of '+' is ' '
         $category = explode(' ', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions[] = "(".implode(' AND ', $category_conditions).")";
      }
   }

   if (get_option('eme_categories_enabled')) {
      if (is_numeric($notcategory)) {
         if ($notcategory>0)
            $conditions[] = " NOT FIND_IN_SET($notcategory,event_category_ids)";
      } elseif ($notcategory == "none") {
         $conditions[] = "event_category_ids != ''";
      } elseif ( preg_match('/,/', $notcategory) ) {
         $notcategory = explode(',', $notcategory);
         $category_conditions = array();
         foreach ($notcategory as $cat) {
            if (is_numeric($cat) && $cat>0) {
               $category_conditions[] = " NOT FIND_IN_SET($cat,event_category_ids)";
            } elseif ($cat == "none") {
               $category_conditions[] = " event_category_ids != ''";
            }
         }
         $conditions[] = "(".implode(' OR', $category_conditions).")";
      } elseif ( preg_match('/\+/', $notcategory) ) {
         $notcategory = explode('+', $notcategory);
         $category_conditions = array();
         foreach ($notcategory as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " NOT FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions[] = "(".implode(' AND ', $category_conditions).")";
      }
   }

   // now filter the author ID
   if ($author != '' && !preg_match('/,/', $author)){
      $authinfo=get_user_by('login', $author);
      $conditions[] = " event_author = ".$authinfo->ID;
   }elseif( preg_match('/,/', $author) ){
      $authors = explode(',', $author);
      $author_conditions = array();
      foreach($authors as $authname) {
            $authinfo=get_user_by('login', $authname);
            $author_conditions[] = " event_author = ".$authinfo->ID;
      }
      $conditions[] = "(".implode(' OR ', $author_conditions).")";
   }

   // now filter the contact ID
   if ($contact_person != '' && !preg_match('/,/', $contact_person)){
      $authinfo=get_user_by('login', $contact_person);
      $conditions[] = " event_contactperson_id = ".$authinfo->ID;
   }elseif( preg_match('/,/', $contact_person) ){
      $contact_persons = explode(',', $contact_person);
      $contact_person_conditions = array();
      foreach($contact_persons as $authname) {
            $authinfo=get_user_by('login', $authname);
            $contact_person_conditions[] = " event_contactperson_id = ".$authinfo->ID;
      }
      $conditions[] = "(".implode(' OR ', $contact_person_conditions).")";
   }

   // extra conditions for authors: if we're in the admin itf, return only the events for which you have the right to change anything
   $current_userid=get_current_user_id();
   if (is_admin() && !current_user_can( get_option('eme_cap_edit_events')) && !current_user_can( get_option('eme_cap_list_events')) && current_user_can( get_option('eme_cap_author_event'))) {
      $conditions[] = "(event_author = $current_userid OR event_contactperson_id= $current_userid)";
   }
   
   $where = implode ( " AND ", $conditions );
   if ($show_recurrent_events_once) {
      if ($where != "")
         $where = " AND " . $where;
       $sql = "SELECT * FROM $events_table
         WHERE (recurrence_id>0 $where)
         group by recurrence_id union all
         SELECT * FROM $events_table
         WHERE (recurrence_id=0 $where)
         ORDER BY event_start_date $order , event_start_time $order
         $limit 
         $offset";
   } else {
      if ($where != "")
         $where = " WHERE " . $where;
      $sql = "SELECT * FROM $events_table
         $where
         ORDER BY event_start_date $order , event_start_time $order
         $limit 
         $offset";
   }
   $wpdb->show_errors = true;
   $events = $wpdb->get_results ( $sql, ARRAY_A );
   $inflated_events = array ();
   if (! empty ( $events )) {
      //$wpdb->print_error(); 
      foreach ( $events as $this_event ) {
         if ($this_event['event_status'] == STATUS_PRIVATE && !is_user_logged_in()) {
            continue;
         }
         // if we're not in the admin itf, we don't want draft events
         if (!is_admin() && $this_event['event_status'] == STATUS_DRAFT) {
            continue;
         }
         
         $this_event = eme_get_event_data($this_event);
         array_push ( $inflated_events, $this_event );
      }
      if (has_filter('eme_event_list_filter')) $inflated_events=apply_filters('eme_event_list_filter',$inflated_events);
   }
   return $inflated_events;
}

function eme_get_event($event_id) {
   global $wpdb;
   $event_id = intval($event_id);

   if (!$event_id) {
      return eme_new_event();
   }

   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $conditions = array ();
   $conditions[] = "event_id = $event_id";

   // if we're not in the admin itf, we don't want draft events
   if (!is_admin()) {
      if (is_user_logged_in()) {
         if (! (current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_author_event')) ||
                current_user_can( get_option('eme_cap_add_event')) || current_user_can( get_option('eme_cap_publish_event')) )) {
             $conditions[] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
         }
      } else {
         $conditions[] = "event_status=".STATUS_PUBLIC;
      }
   }
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   $sql = "SELECT * FROM $events_table
      $where";
   
   //$wpdb->show_errors(true);
   $event = $wpdb->get_row ( $sql, ARRAY_A );
   //$wpdb->print_error();
   if (!$event) {
      return eme_new_event();
   }

   $event = eme_get_event_data($event);
   return $event;
}

function eme_get_event_data($event) {
   if ($event['event_end_date'] == "") {
      $event['event_end_date'] = $event['event_start_date'];
   }
      
   $event['event_attributes'] = @unserialize($event['event_attributes']);
   $event['event_attributes'] = (!is_array($event['event_attributes'])) ?  array() : $event['event_attributes'] ;

   $event['event_properties'] = @unserialize($event['event_properties']);
   $event['event_properties'] = (!is_array($event['event_properties'])) ?  array() : $event['event_properties'] ;
   $event['event_properties'] = eme_init_event_props($event['event_properties']);

   // don't forget the images (for the older events that didn't use the wp gallery)
   if (empty($event['event_image_id']) && empty($event['event_image_url']))
      $event['event_image_url'] = eme_image_url_for_event($event);
   if (has_filter('eme_event_filter')) $event=apply_filters('eme_event_filter',$event);
   return $event;
}

function eme_events_table($scope="future") {

   //$list_limit = get_option('eme_events_admin_limit');
   //if ($list_limit<5 || $list_limit>200) {
   //   $list_limit=20;
   //   update_option('eme_events_admin_limit',$list_limit);
   //}
   //$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
   //$events = eme_get_events ( $limit+1, "future", "ASC", $offset );
   $o_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
   $status = isset($_GET['event_status']) ? intval($_GET['event_status']) : '';
   if(!empty($status)) {
      $extra_conditions = 'event_status = '.$status;
   } else {
         $extra_conditions = '';
   }
 
   //$events = eme_get_events ( 0, $scope, "ASC", $offset, "", $o_category, '', '', 1, '', 0, $extra_conditions);
   $events = eme_get_events ( 0, $scope, "ASC", 0, "", $o_category, '', '', 1, '', 0, $extra_conditions);
   $events_count = count ( $events );
   $scope_names = array ();
   $scope_names['past'] = __ ( 'Past events', 'eme' );
   $scope_names['all'] = __ ( 'All events', 'eme' );
   $scope_names['future'] = __ ( 'Future events', 'eme' );
   ?>

<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php echo $scope_names[$scope]; ?></h2>
   <?php
      admin_show_warnings();
   ?>
   <!--<div id='new-event' class='switch-tab'><a href="<?php
   echo admin_url("admin.php?page=events-manager&amp;eme_admin_action=edit_event")?>><?php
   _e ( 'New Event ...', 'eme' );
   ?></a></div>-->
   <?php
   

   $event_status_array = eme_status_array ();

   ?> 
      
   <div class="tablenav">
   <form id="posts-filter" action="" method="get">
   <input type='hidden' name='page' value='events-manager' />
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
   <select id="event_status" name="event_status">
      <option value="0"><?php _e('Event Status','eme'); ?></option>
      <?php foreach($event_status_array as $event_status_key => $event_status_value): ?>
         <option value="<?php echo $event_status_key; ?>" <?php if (isset($_GET['event_status']) && ($_GET['event_status'] == $event_status_key)) echo 'selected="selected"'; ?>><?php echo $event_status_value; ?></option>
      <?php endforeach; ?>
   </select>
   <select name="category">
   <option value='0'><?php _e('All categories','eme'); ?></option>
   <?php
   $categories = eme_get_categories();
   foreach ( $categories as $category) {
      $selected = "";
      if ($o_category == $category['category_id'])
         $selected = "selected='selected'";
      echo "<option value='".$category['category_id']."' $selected>".$category['category_name']."</option>";
   }
   ?>
   </select>
   <input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
   </form>
   <br />
   <br />

   <?php
   if ($events_count>0) {
   ?>

   <form id="eme_events_actions" action="" method="get">
   <input type='hidden' name='page' value='events-manager' />
   <select name="eme_admin_action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
   <option value="deleteEvents"><?php _e ( 'Delete selected events','eme' ); ?></option>
   <option value="deleteRecurrence"><?php _e ( 'Delete selected recurrent events','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
   <div class="clear"></div>
   <br />

   <table class="widefat hover stripe" id="eme_admin_events">
   <thead>
      <tr>
         <th class='manage-column column-cb check-column' scope='col'><input
            class='select-all' type="checkbox" value='1' /></th>
         <th><?php _e ('ID','eme'); ?></th>
         <th><?php _e ( 'Name', 'eme' ); ?></th>
         <th><?php _e ( 'Status', 'eme' ); ?></th>
         <th></th>
         <th><?php _e ( 'Location', 'eme' ); ?></th>
         <th><?php _e ( 'Date and time', 'eme' ); ?></th>
         <th></th>
      </tr>
   </thead>
   <tbody>
   <?php
      foreach ( $events as $event ) {
         $localised_start_date = eme_localised_date($event['event_start_date']);
         $localised_start_time = eme_localised_time($event['event_start_time']);
         $localised_end_date = eme_localised_date($event['event_end_date']);
         $localised_end_time = eme_localised_time($event['event_end_time']);
         $startstring=strtotime($event['event_start_date']." ".$event['event_start_time']);

         $today = date ( "Y-m-d" );
         
         $location_summary = "";
         if (isset($event['location_id']) && $event['location_id']) {
            $location = eme_get_location ( $event['location_id'] );
            $location_summary = "<b>" . eme_trans_sanitize_html($location['location_name']) . "</b><br />" . eme_trans_sanitize_html($location['location_address']) . " - " . eme_trans_sanitize_html($location['location_town']);
         }
         
         $style = "";
         if ($event['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         
     ?>
     <tr <?php echo "$style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event['event_id']; ?>' name='events[]' /></td>
         <td><?php echo $event['event_id']; ?></td>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event['event_id']); ?>"><?php echo eme_trans_sanitize_html($event['event_name']); ?></a>
         </strong>
         <?php
         $categories = explode(',', $event['event_category_ids']);
         foreach($categories as $cat){
            $category = eme_get_category($cat);
            if($category)
               echo "<br /><span title='".__('Category','eme').": ".eme_trans_sanitize_html($category['category_name'])."'>".eme_trans_sanitize_html($category['category_name'])."</span>";
         }
         if ($event['event_rsvp']) {
            $booked_seats = eme_get_booked_seats($event['event_id']);
            $available_seats = eme_get_available_seats($event['event_id']);
            $pending_seats = eme_get_pending_seats($event['event_id']);
            $total_seats = $event['event_seats'];
            if (eme_is_multi($event['event_seats'])) {
               $available_seats_string = $available_seats.' ('.eme_convert_array2multi(eme_get_available_multiseats($event['event_id'])).')';
               $pending_seats_string = $pending_seats.' ('.eme_convert_array2multi(eme_get_pending_multiseats($event['event_id'])).')';
               $total_seats_string = eme_get_multitotal($total_seats) .' ('.$event['event_seats'].')';
            } else {
               $available_seats_string = $available_seats;
               $pending_seats_string = $pending_seats;
               $total_seats_string = $total_seats;
            }
            if ($pending_seats >0)
               echo "<br />".__('RSVP Info: ','eme').__('Free: ','eme' ).$available_seats_string.", ".__('Pending: ','eme').$pending_seats_string.", ".__('Max: ','eme').$total_seats_string;
            else
               echo "<br />".__('RSVP Info: ','eme').__('Free: ','eme' ).$available_seats_string.", ".__('Max: ','eme').$total_seats_string;
            if ($booked_seats>0) {
               $printable_address = admin_url("/admin.php?page=eme-people&amp;eme_admin_action=booking_printable&amp;event_id=".$event['event_id']);
               $csv_address = admin_url("/admin.php?page=eme-people&amp;eme_admin_action=booking_csv&amp;event_id=".$event['event_id']);
               echo " (<a id='booking_printable_".$event['event_id']."' href='$printable_address'>".__('Printable view','eme')."</a>)";
               echo " (<a id='booking_csv_".$event['event_id']."' href='$csv_address'>".__('CSV export','eme')."</a>)";
            }
         }

         ?> 
         </td>
         <td>
         <?php
         if (isset ($event_status_array[$event['event_status']])) {
            echo $event_status_array[$event['event_status']];
            $event_url = eme_event_url($event);
            if ($event['event_status'] == STATUS_DRAFT)
               echo "<br /> <a href='$event_url'>".__('Preview event','eme')."</a>";
            else
               echo "<br /> <a href='$event_url'>".__('View event','eme')."</a>";
         }
         ?> 
         </td>
         <td>
         <a href="<?php echo admin_url("admin.php?page=events-manager&amp;eme_admin_action=duplicate_event&amp;event_id=".$event['event_id']); ?>" title="<?php _e ( 'Duplicate this event', 'eme' ); ?>"><strong>+</strong></a>
         </td>
         <td>
             <?php echo $location_summary; ?>
         </td>
         <td data-sort="<?php echo $startstring; ?>">
            <?php echo $localised_start_date; if ($localised_end_date !='' && $localised_end_date!=$localised_start_date) echo " - " . $localised_end_date; ?><br />
            <?php if ($event['event_properties']['all_day']==1)
                     _e('All day','eme');
                  else
                     echo "$localised_start_time - $localised_end_time"; ?>
         </td>
         <td>
             <?php
            if ($event['recurrence_id']) {
               $recurrence_desc = eme_get_recurrence_desc ( $event['recurrence_id'] );
            ?>
               <b><?php echo $recurrence_desc; ?>
            <br />
            <a href="<?php echo admin_url("admin.php?page=events-manager&amp;eme_admin_action=edit_recurrence&amp;recurrence_id=".$event['recurrence_id']); ?>"><?php _e ( 'Reschedule', 'eme' ); ?></a></b>
            <?php
            }
            ?>
         </td>
   </tr>
   <?php
   }
   ?>
   
   </tbody>
   </table>

   </form>

<?php
   if (0) {
      if ($events_count > $limit) {
         $forward = $offset + $limit;
         $backward = $offset - $limit;
         echo "<div id='events-pagination'> ";
         echo "<a style='float: right' href='" . admin_url("admin.php?page=events-manager&amp;scope=$scope&amp;category=$o_category&amp;offset=$forward")."'>&gt;&gt;</a>";
         if ($backward >= 0)
            echo "<a style='float: left' href='" . admin_url("admin.php?page=events-manager&amp;scope=$scope&amp;category=$o_category&amp;offset=$backward")."'>&lt;&lt;</a>";
         echo "</div>";
      }
      if ($events_count <= $limit && $offset>0) {
         $backward = $offset - $limit;
         echo "<div id='events-pagination'> ";
         if ($backward >= 0)
            echo "<a style='float: left' href='" . admin_url("admin.php?page=events-manager&amp;scope=$scope&amp;category=$o_category&amp;offset=$backward")."'>&lt;&lt;</a>";
         echo "</div>";
      }
   }
   } else {
      echo "<div id='events-admin-no-events'>" . get_option('eme_no_events_message') . "</div></div>";
   }
?>

   <script type="text/javascript">
   jQuery(document).ready( function() {
         jQuery('#eme_admin_events').dataTable( {
<?php
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
            "pagingType": "full",
            "columnDefs": [
               { "sortable": false, "targets": [0,4,7] }
            ]
         } );
   } );
   </script>

   </div>
</div>
<?php
}

function eme_event_form($event, $title, $element) {
   
   admin_show_warnings();
   eme_admin_map_script();
   global $plugin_page;
   $event_status_array = eme_status_array ();
   $saved_bydays = array();
   $currency_array = eme_currency_array();

   // let's determine if it is a new event, handy
   // or, in case of validation errors, $event can already contain info, but no $element (=event id)
   // so we create a new event and copy over the info into $event for the elements that do not exist
   if (! $element) {
      $is_new_event=1;
      $new_event=eme_new_event();
      $event = array_replace_recursive($new_event,$event);
   } else {
      $is_new_event=0;
   }

   $show_recurrent_form = 0;
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_recurrence") {
      $pref = "recurrence";
      $form_destination = "admin.php?page=events-manager&amp;eme_admin_action=update_recurrence&amp;recurrence_id=" . $element;
      $saved_bydays = explode ( ",", $event['recurrence_byday'] );
      $show_recurrent_form = 1;
   } else {
      $pref = "event";
      // even for new events, after the 'save' button is clicked, we want to go to the list of events
      // so we use page=events-manager too, not page=eme-new_event
      if ($is_new_event)
         $form_destination = "admin.php?page=events-manager&amp;eme_admin_action=insert_event";
      else
         $form_destination = "admin.php?page=events-manager&amp;eme_admin_action=update_event&amp;event_id=" . $element;

      if (isset($event['recurrence_id']) && $event['recurrence_id']) {
         # editing a single event of an recurrence: don't show the recurrence form
         $show_recurrent_form = 0;
      } else {
         # for single non-recurrent events: we show the form, so we can make it recurrent if we want to
         # but for that, we need to set the recurrence fields, otherwise we get warnings
         $show_recurrent_form = 1;
         $event["recurrence_id"] = 0;
         $event["recurrence_freq"] = '';
         $event["recurrence_start_date"] = '';
         $event["recurrence_end_date"] = '';
         $event["recurrence_interval"] = '';
         $event["recurrence_byweekno"] = '';
         $event["recurrence_byday"] = '';
         $event["recurrence_specific_days"] = '';
      }
   }
   
   if (!isset($event['recurrence_start_date'])) $event['recurrence_start_date']="";
   if (!isset($event['recurrence_end_date'])) $event['recurrence_end_date']="";

   $freq_options = array ("daily" => __ ( 'Daily', 'eme' ), "weekly" => __ ( 'Weekly', 'eme' ), "monthly" => __ ( 'Monthly', 'eme' ), "specific" => __('Specific days', 'eme' ) );
   $days_names = array (1 => __ ( 'Mon' ), 2 => __ ( 'Tue' ), 3 => __ ( 'Wed' ), 4 => __ ( 'Thu' ), 5 => __ ( 'Fri' ), 6 => __ ( 'Sat' ), 7 => __ ( 'Sun' ) );
   $weekno_options = array ("1" => __ ( 'first', 'eme' ), '2' => __ ( 'second', 'eme' ), '3' => __ ( 'third', 'eme' ), '4' => __ ( 'fourth', 'eme' ), '5' => __ ( 'fifth', 'eme' ), '-1' => __ ( 'last', 'eme' ), "none" => __('Start day') );
   
   $event_RSVP_checked = ($event['event_rsvp']) ? "checked='checked'" : "";
   $event_number_spaces=$event['event_seats'];
   $registration_wp_users_only = ($event['registration_wp_users_only']) ? "checked='checked'" : "";
   $registration_requires_approval = ($event['registration_requires_approval']) ? "checked='checked'" : "";

   $use_paypal_checked = ($event['use_paypal']) ? "checked='checked'" : "";
   $use_google_checked = ($event['use_google']) ? "checked='checked'" : "";
   $use_2co_checked = ($event['use_2co']) ? "checked='checked'" : "";
   $use_webmoney_checked = ($event['use_webmoney']) ? "checked='checked'" : "";
   $use_fdgg_checked = ($event['use_fdgg']) ? "checked='checked'" : "";

   // all properties
   $eme_prop_auto_approve_checked = ($event['event_properties']['auto_approve']) ? "checked='checked'" : "";
   $eme_prop_ignore_pending_checked = ($event['event_properties']['ignore_pending']) ? "checked='checked'" : "";
   $eme_prop_all_day_checked = ($event['event_properties']['all_day']) ? "checked='checked'" : "";

// the next javascript will fill in the values for localised-start-date, ... form fields and jquery datepick will fill in also to "to_submit" form fields
   ?>

<script type="text/javascript">
   jQuery(document).ready( function() {
   var dateFormat = jQuery("#localised-start-date").datepick( "option", "dateFormat" );

   var loc_start_date = jQuery.datepick.newDate(<?php echo eme_convert_date_format('Y,m,d',$event['event_start_date']); ?>);
   jQuery("#localised-start-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, loc_start_date));

   var loc_end_date = jQuery.datepick.newDate(<?php echo eme_convert_date_format('Y,m,d',$event['event_end_date']); ?>);
   jQuery("#localised-end-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, loc_end_date));
   <?php if ($pref == "recurrence" && $event['recurrence_freq'] == 'specific') { ?>
      var mydates = [];
      <?php foreach (explode(',',$event['recurrence_specific_days']) as $specific_day) { ?>
	      mydates.push(jQuery.datepick.newDate(<?php echo eme_convert_date_format('Y,m,d',$specific_day); ?>));
      <?php } ?>
      jQuery("#localised-rec-start-date").datepick("setDate", mydates);
   <?php } else { ?>
      var rec_start_date = jQuery.datepick.newDate(<?php echo eme_convert_date_format('Y,m,d',$event['recurrence_start_date']); ?>);
      jQuery("#localised-rec-start-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, rec_start_date));
   <?php } ?>
   var rec_end_date = jQuery.datepick.newDate(<?php echo eme_convert_date_format('Y,m,d',$event['recurrence_end_date']); ?>);
   jQuery("#localised-rec-end-date").datepick("setDate", jQuery.datepick.formatDate(dateFormat, rec_end_date));
 });
</script>

   <form id="eventForm" name="eventForm" method="post" enctype="multipart/form-data" action="<?php echo $form_destination; ?>">
      <div class="wrap">
         <div id="icon-events" class="icon32"><br /></div>
         <h2><?php echo eme_trans_sanitize_html($title); ?></h2>
         <?php
         if ($event['recurrence_id']) {
            ?>
         <p id='recurrence_warning'>
            <?php
               if (isset ( $_GET['eme_admin_action'] ) && ($_GET['eme_admin_action'] == 'edit_recurrence')) {
                  _e ( 'WARNING: This is a recurrence.', 'eme' )?>
            <br />
            <?php
                  _e ( 'Modifying these data all the events linked to this recurrence will be rescheduled', 'eme' );
               
               } else {
                  _e ( 'WARNING: This is a recurring event.', 'eme' );
                  _e ( 'If you change these data and save, this will become an independent event.', 'eme' );
               }
               ?>
         </p>
         <?php
         }
         ?>
         <div id="poststuff" class="metabox-holder has-right-sidebar">
            <!-- SIDEBAR -->
            <div id="side-info-column" class='inner-sidebar'>
               <div id='side-sortables' class="meta-box-sortables">
                  <?php if(current_user_can( get_option('eme_cap_author_event'))) { ?>
                  <!-- status postbox -->
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Event Status', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <p><?php _e('Status','eme'); ?>
                        <select id="event_status" name="event_status">
                        <?php
                           foreach ( $event_status_array as $key=>$value) {
                              if ($event['event_status'] && ($event['event_status']==$key)) {
                                 $selected = "selected='selected'";
                              } else {
                                 $selected = "";
                              }
                              echo "<option value='$key' $selected>$value</option>";
                           }
                        ?>
                        </select><br />
                        <?php
                           _e('Private events are only visible for logged in users, draft events are not visible from the front end.','eme');
                        ?>
                        </p>
                     </div>
                  </div>
                  <?php } ?>
                  <?php if(get_option('eme_recurrence_enabled') && $show_recurrent_form ) : ?>
                  <!-- recurrence postbox -->
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( "Recurrence", 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <?php 
                           $recurrence_YES = "";
                           if ($event['recurrence_id'])
                              $recurrence_YES = "checked='checked' disabled='disabled'";
                        ?>
                        <p>
                           <input id="event-recurrence" type="checkbox" name="repeated_event"
                              value="1" <?php echo $recurrence_YES; ?> />
                        </p>
                        <div id="event_recurrence_pattern">
                           <p>Frequency:
                              <select id="recurrence-frequency" name="recurrence_freq">
                                 <?php eme_option_items ( $freq_options, $event['recurrence_freq'] ); ?>
                              </select>
                           </p>
			   <div id="recurrence-intervals">
                           <p>
                              <?php _e ( 'Every', 'eme' )?>
                              <input id="recurrence-interval" name='recurrence_interval'
                                size='2' value='<?php if (isset ($event['recurrence_interval'])) echo $event['recurrence_interval']; ?>' />
                              <span class='interval-desc' id="interval-daily-singular">
                              <?php _e ( 'day', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-daily-plural">
                              <?php _e ( 'days', 'eme' ) ?>
                              </span> <span class='interval-desc' id="interval-weekly-singular">
                              <?php _e ( 'week', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-weekly-plural">
                              <?php _e ( 'weeks', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-monthly-singular">
                              <?php _e ( 'month', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-monthly-plural">
                              <?php _e ( 'months', 'eme' )?>
                              </span> </p>
                           <p class="alternate-selector" id="weekly-selector">
                              <?php eme_checkbox_items ( 'recurrence_bydays[]', $days_names, $saved_bydays ); ?>
                              <br />
                              <?php _e ( 'If you leave this empty, the recurrence start date will be used as a reference.', 'eme' )?>
                           </p>
                           <p class="alternate-selector" id="monthly-selector">
                              <?php _e ( 'Every', 'eme' )?>
                              <select id="monthly-modifier" name="recurrence_byweekno">
                                 <?php eme_option_items ( $weekno_options, $event['recurrence_byweekno'] ); ?>
                              </select>
                              <select id="recurrence-weekday" name="recurrence_byday">
                                 <?php eme_option_items ( $days_names, $event['recurrence_byday'] ); ?>
                              </select>
                              <?php _e ( 'Day of month', 'eme' )?>
                              <br />
                              <?php _e ( 'If you use "Start day" as day of the month, the event start date will be used as a reference.', 'eme' )?>
                              &nbsp;
                           </p>
                           </div>
                        </div>
                        <p id="recurrence-tip">
                           <?php _e ( 'Check if your event happens more than once.', 'eme' )?>
                        </p>
                        <p id="recurrence-tip-2">
                           <?php _e ( 'The event start and end date only define the duration of an event in case of a recurrence.', 'eme' )?>
                        </p>
                     </div>
                  </div>
                  <?php endif; ?>

                  <?php if($event['event_author']) : ?>
                  <!-- owner postbox -->
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Author', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <p><?php _e('Author of this event: ','eme'); ?>
                           <?php
                           $owner_user_info = get_userdata($event['event_author']);
                           echo eme_sanitize_html($owner_user_info->display_name);
                           ?>
                        </p>
                     </div>
                  </div>
                  <?php endif; ?>
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Contact Person', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <p><?php _e('Contact','eme'); ?>
                           <?php
                           wp_dropdown_users ( array ('name' => 'event_contactperson_id', 'show_option_none' => __ ( "Event author", 'eme' ), 'selected' => $event['event_contactperson_id'] ) );
                           ?>
                        </p>
                     </div>
                  </div>
                  <?php if(get_option('eme_rsvp_enabled')) : ?>
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span><?php _e('RSVP','eme'); ?></span></h3>
                     <div class="inside">
                        <p>
                           <input id="rsvp-checkbox" name='event_rsvp' value='1' type='checkbox' <?php echo $event_RSVP_checked; ?> />
                           <?php _e ( 'Enable registration for this event', 'eme' )?>
                        </p>
                        <div id='rsvp-data'>
                           <p>
                              <input id="approval_required-checkbox" name='registration_requires_approval' value='1' type='checkbox' <?php echo $registration_requires_approval; ?> />
                              <?php _e ( 'Require approval for registration','eme' ); ?>
                           <br />
                              <input id="eme_prop_auto_approve" name='eme_prop_auto_approve' value='1' type='checkbox' <?php echo $eme_prop_auto_approve_checked; ?> />
                              <?php _e ( 'Auto-approve registration upon payment','eme' ); ?>
                           <br />
                              <input id="eme_prop_ignore_pending" name='eme_prop_ignore_pending' value='1' type='checkbox' <?php echo $eme_prop_ignore_pending_checked; ?> />
                              <?php _e ( 'Consider pending registrations as available seats for new bookings','eme' ); ?>
                           <br />
                              <input id="wp_member_required-checkbox" name='registration_wp_users_only' value='1' type='checkbox' <?php echo $registration_wp_users_only; ?> />
                              <?php _e ( 'Require WP membership for registration','eme' ); ?>
                           <br /><table>
                              <tr>
                              <td><?php _e ( 'Spaces','eme' ); ?> :</td>
                              <td><input id="seats-input" type="text" name="event_seats" maxlength='125' title="<?php _e('For multiseat events, seperate the values by \'||\'','eme'); ?>" value="<?php echo $event_number_spaces; ?>" /></td>
                              </tr>
                              <tr>
                              <td><?php _e ( 'Price: ','eme' ); ?></td>
                              <td><input id="price" type="text" name="price" maxlength='125' title="<?php _e('For multiprice events, seperate the values by \'||\'','eme'); ?>" value="<?php echo $event['price']; ?>" /></td>
                              </tr>
                              <tr>
                              <td><?php _e ( 'Currency: ','eme' ); ?></td>
                              <td><select id="currency" name="currency">
                              <?php
                                 foreach ( $currency_array as $key=>$value) {
                                    if ($event['currency'] && ($event['currency']==$key)) {
                                       $selected = "selected='selected'";
                                    } elseif (!$event['currency'] && ($key==get_option('eme_default_currency'))) {
                                       $selected = "selected='selected'";
                                    } else {
                                       $selected = "";
                                    }
                                    echo "<option value='$key' $selected>$value</option>";
                                 }
                              ?>
                              </select></td>
                              </tr>
                              <tr>
                              <td><?php _e ( 'Max number of spaces to book','eme' ); ?></td>
                              <td><input id="eme_prop_max_allowed" type="text" name="eme_prop_max_allowed" maxlength='125' title="<?php echo __('The maximum number of spaces a person can book in one go.','eme').' '.__('(is multi-compatible)','eme'); ?>" value="<?php echo $event['event_properties']['max_allowed']; ?>" /></td>
                              </tr>
                              <tr>
                              <td><?php _e ( 'Min number of spaces to book','eme' ); ?></td>
                              <td><input id="eme_prop_min_allowed" type="text" name="eme_prop_min_allowed" maxlength='125' title="<?php echo __('The minimum number of spaces a person can book in one go (it can be 0, for e.g. just an attendee list).','eme').' '.__('(is multi-compatible)','eme'); ?>" value="<?php echo $event['event_properties']['min_allowed']; ?>" /></td>
                              </tr></table>
                           <br />
                              <?php _e ( 'Allow RSVP until ','eme' ); ?>
                           <br />
                              <input id="rsvp_number_days" type="text" name="rsvp_number_days" maxlength='2' size='2' value="<?php echo $event['rsvp_number_days']; ?>" />
                              <?php _e ( ' days before the event starts.','eme' ); ?>
                           <br />
                              <input id="rsvp_number_hours" type="text" name="rsvp_number_hours" maxlength='2' size='2' value="<?php echo $event['rsvp_number_hours']; ?>" />
                              <?php _e ( ' hours before the event starts.','eme' ); ?>
                           <br />
                           <br />
                              <?php _e ( 'Payment methods','eme' ); ?><br />
                              <input id="paypal-checkbox" name='use_paypal' value='1' type='checkbox' <?php echo $use_paypal_checked; ?> /><?php _e ( 'Paypal','eme' ); ?><br />
                              <input id="2co-checkbox" name='use_2co' value='1' type='checkbox' <?php echo $use_2co_checked; ?> /><?php _e ( '2Checkout','eme' ); ?><br />
                              <input id="webmoney-checkbox" name='use_webmoney' value='1' type='checkbox' <?php echo $use_webmoney_checked; ?> /><?php _e ( 'Webmoney','eme' ); ?><br />
                              <input id="google-checkbox" name='use_google' value='1' type='checkbox' <?php echo $use_google_checked; ?> /><?php _e ( 'Google Checkout','eme' ); ?><br />
                              <input id="fdgg-checkbox" name='use_fdgg' value='1' type='checkbox' <?php echo $use_fdgg_checked; ?> /><?php _e ( 'First Data','eme' ); ?><br />
                           </p>
                           <?php if ($event['event_rsvp']) {
                                 eme_bookings_compact_table ( $event['event_id'] );
                              }
                           ?>
                        </div>
                     </div>
                  </div>
                  <?php endif; ?>
                  <?php if(get_option('eme_categories_enabled')) :?>
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Category', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                     <?php
                     $categories = eme_get_categories();
                     foreach ( $categories as $category) {
                        if ($event['event_category_ids'] && in_array($category['category_id'],explode(",",$event['event_category_ids']))) {
                           $selected = "checked='checked'";
                        } else {
                           $selected = "";
                        }
                     ?>
            <input type="checkbox" name="event_category_ids[]" value="<?php echo $category['category_id']; ?>" <?php echo $selected ?> /><?php echo $category['category_name']; ?><br />
                     <?php
                     }
                     ?>
                     </div>
                  </div> 
                  <?php endif; ?>
               </div>
            </div>
            <!-- END OF SIDEBAR -->
            <div id="post-body">
               <div id="post-body-content" class="meta-box-sortables">
               <?php  if($plugin_page === 'eme-new_event' && get_option("eme_fb_app_id")) { ?>
                  <div id="fb-root"></div>
                  <script>
                    window.fbAsyncInit = function() {
                      // init the FB JS SDK
                      FB.init({
                        appId      : '<?php echo get_option("eme_fb_app_id");?>',// App ID from the app dashboard
                        channelUrl : '<?php echo plugins_url( "eme_fb_channel.php", __FILE__ )?>', // Channel file for x-domain comms
                        status     : true,  // Check Facebook Login status
                        xfbml      : true   // Look for social plugins on the page
                      });

                      // Additional initialization code such as adding Event Listeners goes here
                     FB.Event.subscribe('auth.authResponseChange', function(response) {
                        if (response.status === 'connected') {
                           jQuery('#fb-import-box').show();
                         } else if (response.status === 'not_authorized') {
                           jQuery('#fb-import-box').hide();
                         } else {
                           jQuery('#fb-import-box').hide();
                         }
                        });
                     };


                     // Load the SDK asynchronously
                     (function(d, s, id){
                       var js, fjs = d.getElementsByTagName(s)[0];
                       if (d.getElementById(id)) {return;}
                       js = d.createElement(s); js.id = id;
                       js.src = "//connect.facebook.net/en_US/all.js";
                       fjs.parentNode.insertBefore(js, fjs);
                     }(document, 'script', 'facebook-jssdk'));

                  </script>
                  <fb:login-button id="fb-login-button" width="200" autologoutlink="true" scope="user_events" max-rows="1"></fb:login-button>
                  <br>
                  <br>
                  <div id='fb-import-box' style='display:none'>
                     Facebook event url : <input type='text' id='fb-event-url' class='widefat' /> 
                     <br>
                     <br>

                     <input type='button' class='button' value='Import' id='import-fb-event-btn' />

                     <br>
                     <br>
                  </div>
               <?php } ?>

               <?php 
               $screens = array( 'events_page_eme-new_event', 'toplevel_page_events-manager' );
               foreach ($screens as $screen) {
                  if ($event['event_page_title_format']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_page_title_format','eme_closed');
                  if ($event['event_single_event_format']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_single_event_format','eme_closed');
                  if ($event['event_contactperson_email_body']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_contactperson_email_body','eme_closed');
                  if ($event['event_registration_recorded_ok_html']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_registration_recorded_ok_html','eme_closed');
                  if ($event['event_respondent_email_body']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_respondent_email_body','eme_closed');
                  if ($event['event_registration_pending_email_body']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_registration_pending_email_body','eme_closed');
                  if ($event['event_registration_updated_email_body']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_registration_updated_email_body','eme_closed');
                  if ($event['event_registration_form_format']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_registration_form_format','eme_closed');
                  if ($event['event_cancel_form_format']=="")
                     add_filter('postbox_classes_'.$screen.'_div_event_cancel_form_format','eme_closed');
               }

               if ($is_new_event) {
                  // we add the meta boxes only on the page we're currently at, so for duplicate event it is the same as for edit event
                  // see the eme_admin_event_boxes function
                  if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == 'duplicate_event')
                     do_meta_boxes('toplevel_page_events-manager',"post",$event);
                  else
                     do_meta_boxes('events_page_eme-new_event',"post",$event);

               } else {
                  do_meta_boxes('toplevel_page_events-manager',"post",$event);
               }
               ?>
               </div>
               <p class="submit">
                  <?php if ($is_new_event) { ?>
                     <input type="submit" class="button-primary" id="event_update_button" name="event_update_button" value="<?php _e ( 'Save' ); ?> &raquo;" />
                  <?php } else { 
                     $delete_button_text=__ ( 'Are you sure you want to delete this event?', 'eme' );
                     $deleteRecurrence_button_text=__ ( 'Are you sure you want to delete this recurrence?', 'eme' );
                  ?>
                     <?php if ($pref == "recurrence") { ?>
                     <input type="submit" class="button-primary" id="event_update_button" name="event_update_button" value="<?php _e ( 'Update' ); ?> &raquo;" />
                     <?php } else { ?>
                     <input type="submit" class="button-primary" id="event_update_button" name="event_update_button" value="<?php _e ( 'Update' ); ?> &raquo;" />
                     <?php } ?>
                     <input type="submit" class="button-primary" id="event_delete_button" name="event_delete_button" value="<?php _e ( 'Delete Event', 'eme' ); ?> &raquo;" onclick="return areyousure('<?php echo $delete_button_text; ?>');" />
                     <?php if ($event['recurrence_id']) { ?>
                     <input type="submit" class="button-primary" id="event_deleteRecurrence_button" name="event_deleteRecurrence_button" value="<?php _e ( 'Delete Recurrence', 'eme' ); ?> &raquo;" onclick="return areyousure('<?php echo $deleteRecurrence_button_text; ?>');" />
                     <?php } ?> 
                  <?php } ?>
               </p>
            </div>
         </div>
      </div>
      <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
      <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
   </form>
<?php
}

function eme_validate_event($event) {
   $required_fields = array("event_name" => __('The event name', 'eme'));
   $troubles = "";
   if (empty($event['event_name'])) {
      $troubles .= "<li>".$required_fields['event_name'].__(" is missing!", "eme")."</li>";
   }  
   if (isset($_POST['repeated_event']) && $_POST['repeated_event'] == "1" && (!isset($_POST['recurrence_end_date']) || $_POST['recurrence_end_date'] == ""))
      $troubles .= "<li>".__ ( 'Since the event is repeated, you must specify an event date for the recurrence.', 'eme' )."</li>";

   if (eme_is_multi($event['event_seats']) && !eme_is_multi($event['price']))
      $troubles .= "<li>".__ ( 'Since the event contains multiple seat categories (multiseat), you must specify the price per category (multiprice) as well.', 'eme' )."</li>";
   if (eme_is_multi($event['event_seats']) && eme_is_multi($event['price'])) {
      $count1=count(eme_convert_multi2array($event['event_seats']));
      $count2=count(eme_convert_multi2array($event['price']));
      if ($count1 != $count2)
         $troubles .= "<li>".__ ( 'Since the event contains multiple seat categories (multiseat), you must specify the exact same amount of prices (multiprice) as well.', 'eme' )."</li>";
   }

   $event_properties = unserialize($event['event_properties']);
   if (eme_is_multi($event_properties['max_allowed']) && eme_is_multi($event['price'])) {
      $count1=count(eme_convert_multi2array($event_properties['max_allowed']));
      $count2=count(eme_convert_multi2array($event['price']));
      if ($count1 != $count2)
         $troubles .= "<li>".__ ( 'Since this is a multiprice event and you decided to limit the max amount of seats to book (for one booking) per price category, you must specify the exact same amount of "max seats to book" as you did for the prices.', 'eme' )."</li>";
   }
   if (eme_is_multi($event_properties['min_allowed']) && eme_is_multi($event['price'])) {
      $count1=count(eme_convert_multi2array($event_properties['min_allowed']));
      $count2=count(eme_convert_multi2array($event['price']));
      if ($count1 != $count2)
         $troubles .= "<li>".__ ( 'Since this is a multiprice event and you decided to limit the min amount of seats to book (for one booking) per price category, you must specify the exact same amount of "min seats to book" as you did for the prices.', 'eme' )."</li>";
   }

   if (empty($troubles)) {
      return "OK";
   } else {
      $message = __('Ach, some problems here:', 'eme')."<ul>\n$troubles</ul>";
      return $message; 
   }
}

function eme_closed($data) {
   $data[]="closed";
   return $data;
}

function eme_admin_general_css() {
   echo "<link rel='stylesheet' href='".EME_PLUGIN_URL."events_manager.css' type='text/css'/>\n";
   $file_name= get_stylesheet_directory()."/eme.css";
   if (file_exists($file_name)) {
      echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme.css' type='text/css'/>\n";
   }
   echo "<link rel='stylesheet' href='".EME_PLUGIN_URL."js/jquery-datatables/css/jquery.dataTables.css' type='text/css'/>\n";
}

// General script to make sure hidden fields are shown when containing data
function eme_admin_general_script() {
   eme_admin_general_css();
   ?>
<script src="<?php echo EME_PLUGIN_URL; ?>js/eme.js" type="text/javascript"></script>
<script src="<?php echo EME_PLUGIN_URL; ?>js/timeentry/jquery.plugin.min.js" type="text/javascript"></script>
<script src="<?php echo EME_PLUGIN_URL; ?>js/timeentry/jquery.timeentry.min.js" type="text/javascript"></script>
<?php
   
   // all the rest below is needed on 3 pages only (for now), so we return if not there
   global $plugin_page;
   if ( !in_array( $plugin_page, array('eme-locations', 'eme-new_event', 'events-manager','eme-options') ) ) {
      return;
   }

   // check if the user wants AM/PM or 24 hour notation
   $time_format = get_option('time_format');
   $show24Hours = 'true';
   if (preg_match ( "/g|h/", $time_format ))
      $show24Hours = 'false';
   
   // jquery ui locales are with dashes, not underscores
   $locale_code = get_locale();
   $locale_code = preg_replace( "/_/","-", $locale_code );
   $locale_file = EME_PLUGIN_DIR. "/js/jquery-datepick/jquery.datepick-$locale_code.js";
   $locale_file_url = EME_PLUGIN_URL. "/js/jquery-datepick/jquery.datepick-$locale_code.js";
   // for english, no translation code is needed
   if (!file_exists($locale_file)) {
      $locale_code = substr ( $locale_code, 0, 2 );
      $locale_file = EME_PLUGIN_DIR. "/js/jquery-datepick/jquery.datepick-$locale_code.js";
      $locale_file_url = EME_PLUGIN_URL. "/js/jquery-datepick/jquery.datepick-$locale_code.js";
   }
   if ($locale_code != "en_US" && file_exists($locale_file)) {
?>
<script src="<?php echo $locale_file_url ?>" type="text/javascript"></script>
<?php
   }
?>
<style type='text/css' media='all'>
   @import "<?php echo EME_PLUGIN_URL; ?>js/jquery-datepick/jquery.datepick.css";
</style>
<script type="text/javascript">
   //<![CDATA[
function areyousure(message) {
   if (!confirm(message)) {
         return false;
   } else {
         return true;
   }
}
 
function updateIntervalDescriptor () { 
   jQuery(".interval-desc").hide();
   var number = "-plural";
   if (jQuery('input#recurrence-interval').val() == 1 || jQuery('input#recurrence-interval').val() == "") {
      number = "-singular";
   }
   var descriptor = "span#interval-"+jQuery("select#recurrence-frequency").val()+number;
   jQuery(descriptor).show();
}
function updateIntervalSelectors () {
   jQuery('p.alternate-selector').hide();
   jQuery('p#'+ jQuery('select#recurrence-frequency').val() + "-selector").show();
   //jQuery('p.recurrence-tip').hide();
   //jQuery('p#'+ jQuery(this).val() + "-tip").show();
}
function updateShowHideRecurrence () {
   if(jQuery('input#event-recurrence').attr("checked")) {
      jQuery("#event_recurrence_pattern").fadeIn();
      jQuery("span#event-date-recursive-explanation").show();
      jQuery("div#div_recurrence_date").show();
      jQuery("p#recurrence-tip").hide();
      jQuery("p#recurrence-tip-2").show();
   } else {
      jQuery("#event_recurrence_pattern").hide();
      jQuery("span#event-date-recursive-explanation").hide();
      jQuery("div#div_recurrence_date").hide();
      jQuery("p#recurrence-tip").show();
      jQuery("p#recurrence-tip-2").hide();
   }
}

function updateShowHideRecurrenceSpecificDays () {
   if (jQuery('select#recurrence-frequency').val() == "specific") {
      jQuery("div#recurrence-intervals").hide();
      jQuery("input#localised-rec-end-date").hide();
      jQuery("span#recurrence-dates-explanation").hide();
      jQuery("span#recurrence-dates-explanation-specificdates").show();
      jQuery("#localised-rec-start-date").datepick('option','multiSelect',999);
   } else {
      jQuery("div#recurrence-intervals").show();
      jQuery("input#localised-rec-end-date").show();
      jQuery("span#recurrence-dates-explanation").show();
      jQuery("span#recurrence-dates-explanation-specificdates").hide();
      jQuery("#localised-rec-start-date").datepick('option','multiSelect',0);
   }
}

function updateShowHideRsvp () {
   if (jQuery('input#rsvp-checkbox').attr("checked")) {
      jQuery("div#rsvp-data").fadeIn();
      jQuery("div#div_event_contactperson_email_body").fadeIn();
      jQuery("div#div_event_registration_recorded_ok_html").fadeIn();
      jQuery("div#div_event_respondent_email_body").fadeIn();
      jQuery("div#div_event_registration_pending_email_body").fadeIn();
      jQuery("div#div_event_registration_updated_email_body").fadeIn();
      jQuery("div#div_event_registration_form_format").fadeIn();
      jQuery("div#div_event_cancel_form_format").fadeIn();
   } else {
      jQuery("div#rsvp-data").fadeOut();
      jQuery("div#div_event_contactperson_email_body").fadeOut();
      jQuery("div#div_event_registration_recorded_ok_html").fadeOut();
      jQuery("div#div_event_respondent_email_body").fadeOut();
      jQuery("div#div_event_registration_pending_email_body").fadeOut();
      jQuery("div#div_event_registration_updated_email_body").fadeOut();
      jQuery("div#div_event_cancel_form_format").fadeOut();
   }
}

function updateShowHideTime () {
   if (jQuery('input#eme_prop_all_day').attr("checked")) {
      jQuery("div#div_event_time").hide();
   } else {
      jQuery("div#div_event_time").show();
   }
}

function updateShowHideCustomReturnPage () {
   if (jQuery('input[name=eme_payment_show_custom_return_page]').attr("checked")) {
         jQuery('tr#eme_payment_succes_format_row').show();
         jQuery('tr#eme_payment_fail_format_row').show();
         jQuery('tr#eme_payment_add_bookingid_to_return_row').show(); 
   } else {
         jQuery('tr#eme_payment_succes_format_row').hide();
         jQuery('tr#eme_payment_fail_format_row').hide();
         jQuery('tr#eme_payment_add_bookingid_to_return_row').hide(); 
   }
}

function updateShowHidePaypalSEncrypt () {
   if (jQuery('input[name=eme_paypal_s_encrypt]').attr("checked")) {
         jQuery('tr#eme_paypal_s_pubcert_row').show(); 
         jQuery('tr#eme_paypal_s_privkey_row').show();
         jQuery('tr#eme_paypal_s_paypalcert_row').show();
         jQuery('tr#eme_paypal_s_certid_row').show();
   } else {
         jQuery('tr#eme_paypal_s_pubcert_row').hide(); 
         jQuery('tr#eme_paypal_s_privkey_row').hide();
         jQuery('tr#eme_paypal_s_paypalcert_row').hide();
         jQuery('tr#eme_paypal_s_certid_row').hide();
   }
}

function updateShowHideRsvpMailNotify () {
   if (jQuery('input[name=eme_rsvp_mail_notify_is_active]').attr("checked")) {
      jQuery("table#rsvp_mail_notify-data").show();
   } else {
      jQuery("table#rsvp_mail_notify-data").hide();
   }
}

function updateShowHideRsvpMailSendMethod () {
   if (jQuery('select[name=eme_rsvp_mail_send_method]').val() == "smtp") {
         jQuery('tr#eme_smtp_host_row').show();
         jQuery('tr#eme_rsvp_mail_SMTPAuth_row').show();
         jQuery('tr#eme_smtp_username_row').show(); 
         jQuery('tr#eme_smtp_password_row').show(); 
         jQuery('tr#eme_rsvp_mail_port_row').show(); 
   } else {
         jQuery('tr#eme_smtp_host_row').hide();
         jQuery('tr#eme_rsvp_mail_SMTPAuth_row').hide();
         jQuery('tr#eme_smtp_username_row').hide(); 
         jQuery('tr#eme_smtp_password_row').hide();
         jQuery('tr#eme_rsvp_mail_port_row').hide(); 
   }
}

function updateShowHideRsvpMailSMTPAuth () {
   if (jQuery('input[name=eme_rsvp_mail_SMTPAuth]').attr("checked")) {
         jQuery('tr#eme_smtp_username_row').show(); 
         jQuery('tr#eme_smtp_password_row').show(); 
   } else {
         jQuery('tr#eme_smtp_username_row').hide(); 
         jQuery('tr#eme_smtp_password_row').hide();
   }
}

jQuery(document).ready( function() {
   jQuery("#div_recurrence_date").hide();
   jQuery("#localised-start-date").show();
   jQuery("#localised-end-date").show();
   jQuery("#start-date-to-submit").hide();
   jQuery("#end-date-to-submit").hide(); 
   jQuery("#rec-start-date-to-submit").hide();
   jQuery("#rec-end-date-to-submit").hide(); 

   jQuery.datepick.setDefaults( jQuery.datepick.regional["<?php echo $locale_code; ?>"] );
   jQuery.datepick.setDefaults({
      changeMonth: true,
      changeYear: true
   });
   jQuery("#localised-start-date").datepick({ altField: "#start-date-to-submit", altFormat: "yyyy-mm-dd" });
   jQuery("#localised-end-date").datepick({ altField: "#end-date-to-submit", altFormat: "yyyy-mm-dd" });
   jQuery("#localised-rec-start-date").datepick({ altField: "#rec-start-date-to-submit", altFormat: "yyyy-mm-dd" });
   jQuery("#localised-rec-end-date").datepick({ altField: "#rec-end-date-to-submit", altFormat: "yyyy-mm-dd" });

   jQuery("#start-time").timeEntry({spinnerImage: '', show24Hours: <?php echo $show24Hours; ?> });
   jQuery("#end-time").timeEntry({spinnerImage: '', show24Hours: <?php echo $show24Hours; ?>});

   // if any of event_single_event_format,event_page_title_format,event_contactperson_email_body,event_respondent_email_body,event_registration_pending_email_body, event_registration_form_format, event_registration_updated_email_body
   // is empty: display default value on focus, and if the value hasn't changed from the default: empty it on blur

   jQuery('textarea#event_page_title_format').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_event_page_title_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_page_title_format').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_event_page_title_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_single_event_format').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_single_event_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_single_event_format').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_single_event_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_contactperson_email_body').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_contactperson_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_contactperson_email_body').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_contactperson_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_respondent_email_body').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_respondent_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_respondent_email_body').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_respondent_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_registration_recorded_ok_html').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_recorded_ok_html' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_registration_recorded_ok_html').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_recorded_ok_html' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   });
   jQuery('textarea#event_registration_pending_email_body').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_pending_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_registration_pending_email_body').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_pending_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   });
   jQuery('textarea#event_registration_updated_email_body').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_pending_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_registration_updated_email_body').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_pending_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   });
   jQuery('textarea#event_registration_form_format').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_form_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_registration_form_format').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_form_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_cancel_form_format').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_cancel_form_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_cancel_form_format').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_cancel_form_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 


   updateIntervalDescriptor(); 
   updateIntervalSelectors();
   updateShowHideRecurrence();
   updateShowHideRsvp();
   updateShowHideRecurrenceSpecificDays();
   updateShowHideTime();
   jQuery('input#event-recurrence').change(updateShowHideRecurrence);
   jQuery('input#rsvp-checkbox').change(updateShowHideRsvp);
   jQuery('input#eme_prop_all_day').change(updateShowHideTime);
   // recurrency elements
   jQuery('input#recurrence-interval').keyup(updateIntervalDescriptor);
   jQuery('select#recurrence-frequency').change(updateIntervalDescriptor);
   jQuery('select#recurrence-frequency').change(updateIntervalSelectors);
   jQuery('select#recurrence-frequency').change(updateShowHideRecurrenceSpecificDays);

   // for the eme-options pages
   updateShowHideCustomReturnPage();
   updateShowHidePaypalSEncrypt();
   updateShowHideRsvpMailNotify ();
   updateShowHideRsvpMailSendMethod ();
   updateShowHideRsvpMailSMTPAuth ();
   jQuery('input[name=eme_payment_show_custom_return_page]').change(updateShowHideCustomReturnPage);
   jQuery('input[name=eme_paypal_s_encrypt]').change(updateShowHidePaypalSEncrypt);
   jQuery('input[name=eme_rsvp_mail_notify_is_active]').change(updateShowHideRsvpMailNotify);
   jQuery('select[name=eme_rsvp_mail_send_method]').change(updateShowHideRsvpMailSendMethod);
   jQuery('input[name=eme_rsvp_mail_SMTPAuth]').change(updateShowHideRsvpMailSMTPAuth);

   // Add a "+" to the collapsable postboxes
   //jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');

   // hiding or showing notes according to their content 
   //          if(jQuery("textarea[@name=event_notes]").val()!="") {
      //    jQuery("textarea[@name=event_notes]").parent().parent().removeClass('closed');
      // }
   //jQuery('#event_notes h3').click( function() {
   //       jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
        //});

   // users cannot submit the event form unless some fields are filled
   function validateEventForm() {
      var errors = "";
      var recurring = jQuery("input[name=repeated_event]:checked").val();
      //requiredFields= new Array('event_name', 'localised_event_start_date', 'location_name','location_address','location_town');
      var requiredFields = ['event_name', 'localised_event_start_date'];
      var localisedRequiredFields = {'event_name':"<?php _e ( 'Name', 'eme' )?>",
                      'localised_event_start_date':"<?php _e ( 'Date', 'eme' )?>"
                     };
      
      var missingFields = [];
      var i;
      for (i in requiredFields) {
         if (jQuery("input[name=" + requiredFields[i]+ "]").val() == 0) {
            missingFields.push(localisedRequiredFields[requiredFields[i]]);
            jQuery("input[name=" + requiredFields[i]+ "]").css('border','2px solid red');
         } else {
            jQuery("input[name=" + requiredFields[i]+ "]").css('border','1px solid #DFDFDF');
         }
      }
   
      if (missingFields.length > 0) {
         errors = "<?php echo _e ( 'Some required fields are missing:', 'eme' )?> " + missingFields.join(", ") + ".\n";
      }
      if (recurring && jQuery("input#localised-rec-end-date").val() == "" && jQuery("select#recurrence-frequency").val() != "specific") {
         errors = errors +  "<?php _e ( 'Since the event is repeated, you must specify an end date', 'eme' )?>."; 
         jQuery("input#localised-rec-end-date").css('border','2px solid red');
      } else {
         jQuery("input#localised-rec-end-date").css('border','1px solid #DFDFDF');
      }
      if (errors != "") {
         alert(errors);
         return false;
      }
      return true;
   }

   jQuery('#eventForm').bind("submit", validateEventForm);
});

//]]>
</script>

<?php
}

//function eme_admin_options_save() {
//   if (is_admin() && isset($_GET['settings-updated']) && $_GET['settings-updated']) {
//     return; 
//   }
//}

function eme_admin_event_boxes() {
   global $plugin_page;
   $screens = array( 'events_page_eme-new_event', 'toplevel_page_events-manager' );
   foreach ($screens as $screen) {
        if (preg_match("/$plugin_page/",$screen)) {
           // we need titlediv for qtranslate as ID
           add_meta_box("titlediv", __('Name', 'eme'), "eme_meta_box_div_event_name",$screen,"post");
           add_meta_box("div_recurrence_date", __('Recurrence dates', 'eme'), "eme_meta_box_div_recurrence_date",$screen,"post");
           add_meta_box("div_event_date", __('Event date', 'eme'), "eme_meta_box_div_event_date",$screen,"post");
           add_meta_box("div_event_time", __('Event time', 'eme'), "eme_meta_box_div_event_time",$screen,"post");
           add_meta_box("div_event_page_title_format", __('Single Event Title Format', 'eme'), "eme_meta_box_div_event_page_title_format",$screen,"post");
           add_meta_box("div_event_single_event_format", __('Single Event Format', 'eme'), "eme_meta_box_div_event_single_event_format",$screen,"post");
           add_meta_box("div_event_contactperson_email_body", __('Contact Person Email Format', 'eme'), "eme_meta_box_div_event_contactperson_email_body",$screen,"post");
           add_meta_box("div_event_registration_recorded_ok_html", __('Booking recorded html Format', 'eme'), "eme_meta_box_div_event_registration_recorded_ok_html",$screen,"post");
           add_meta_box("div_event_respondent_email_body", __('Respondent Email Format', 'eme'), "eme_meta_box_div_event_respondent_email_body",$screen,"post");
           add_meta_box("div_event_registration_pending_email_body", __('Registration Pending Email Format', 'eme'), "eme_meta_box_div_event_registration_pending_email_body",$screen,"post");
           add_meta_box("div_event_registration_updated_email_body", __('Registration Updated Email Format', 'eme'), "eme_meta_box_div_event_registration_updated_email_body",$screen,"post");
           add_meta_box("div_event_registration_form_format", __('Registration Form Format', 'eme'), "eme_meta_box_div_event_registration_form_format",$screen,"post");
           add_meta_box("div_event_cancel_form_format", __('Cancel Registration Form Format', 'eme'), "eme_meta_box_div_event_cancel_form_format",$screen,"post");
           add_meta_box("div_location_name", __('Location', 'eme'), "eme_meta_box_div_location_name",$screen,"post");
           add_meta_box("div_event_notes", __('Details', 'eme'), "eme_meta_box_div_event_notes",$screen,"post");
           add_meta_box("div_event_image", __('Event image', 'eme'), "eme_meta_box_div_event_image",$screen,"post");
           if (get_option('eme_attributes_enabled'))
              add_meta_box("div_event_attributes", __('Attributes', 'eme'), "eme_meta_box_div_event_attributes",$screen,"post");
           add_meta_box("div_event_url", __('External link', 'eme'), "eme_meta_box_div_event_url",$screen,"post");
        }
   }
}

function eme_meta_box_div_event_name($event){
?>
   <!-- we need title for qtranslate as ID -->
   <input type="text" id="title" name="event_name" value="<?php echo eme_sanitize_html($event['event_name']); ?>" />
   <br />
   <?php _e ( 'The event name. Example: Birthday party', 'eme' )?>
   <br />
   <br />
   <?php if ($event['event_id'] && $event['event_name'] != "") {
      _e ('Permalink: ', 'eme' );
      echo trailingslashit(home_url()).eme_permalink_convert(get_option ( 'eme_permalink_events_prefix')).$event['event_id']."/";
      $slug = $event['event_slug'] ? $event['event_slug'] : $event['event_name'];
      $slug = untrailingslashit(eme_permalink_convert($slug));
      ?>
         <input type="text" id="slug" name="event_slug" value="<?php echo $slug; ?>" /><?php echo user_trailingslashit(""); ?>
         <?php
   }
}

function eme_meta_box_div_event_date($event){
   $eme_prop_all_day_checked = ($event['event_properties']['all_day']) ? "checked='checked'" : "";
?>
      <input id="localised-start-date" type="text" name="localised_event_start_date" value="" style="display: none;" readonly="readonly" />
      <input id="start-date-to-submit" type="text" name="event_start_date" value="" style="background: #FCFFAA" />
      <input id="localised-end-date" type="text" name="localised_event_end_date" value="" style="display: none;" readonly="readonly" />
      <input id="end-date-to-submit" type="text" name="event_end_date" value="" style="background: #FCFFAA" />
      <span id='event-date-explanation'>
      <?php _e ( 'The event beginning and end date.', 'eme' ); ?>
      </span>
      <br />
      <span id='event-date-recursive-explanation'>
      <?php _e ( 'In case of a recurrent event, use the beginning and end date to just indicate the duration of one event in days. The real start date is determined by the recurrence scheme being used.', 'eme' ); ?>
      </span>
      <br />
      <br />
      <input id="eme_prop_all_day" name='eme_prop_all_day' value='1' type='checkbox' <?php echo $eme_prop_all_day_checked; ?> />
      <?php _e ( 'This event lasts all day', 'eme' ); ?>
<?php
}

function eme_meta_box_div_recurrence_date($event){
?>
   <input id="localised-rec-start-date" type="text" name="localised_recurrence_date" value="" readonly="readonly" />
   <input id="rec-start-date-to-submit" type="text" name="recurrence_start_date" value="" style="background: #FCFFAA" />
   <input id="localised-rec-end-date" type="text" name="localised_recurrence_end_date" value="" readonly="readonly" />
   <input id="rec-end-date-to-submit" type="text" name="recurrence_end_date" value="" style="background: #FCFFAA" />
   <br />
   <span id='recurrence-dates-explanation'>
   <?php _e ( 'The recurrence beginning and end date.', 'eme' ); ?>
   </span>
   <span id='recurrence-dates-explanation-specificdates'>
   <?php _e ( 'Select all the dates you want the event to begin on.', 'eme' ); ?>
   </span>
<?php
}

function eme_meta_box_div_event_page_title_format($event) {
?>
   <textarea name="event_page_title_format" id="event_page_title_format" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_page_title_format']);?></textarea>
   <br />
   <p><?php _e ( 'The format of the single event title.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_time($event) {
   // check if the user wants AM/PM or 24 hour notation
   $time_format = get_option('time_format');
   $hours_locale = '24';
   if (preg_match ( "/g|h/", $time_format )) {
      $event_start_time = date("h:iA", strtotime($event['event_start_time']));
      $event_end_time = date("h:iA", strtotime($event['event_end_time']));
   } else {
      $event_start_time = $event['event_start_time'];
      $event_end_time = $event['event_end_time'];
   }

?>
   <input id="start-time" type="text" size="8" maxlength="8" name="event_start_time" value="<?php echo $event_start_time; ?>" />
   -
   <input id="end-time" type="text" size="8" maxlength="8" name="event_end_time" value="<?php echo $event_end_time; ?>" />
   <br />
   <?php _e ( 'The time of the event beginning and end', 'eme' )?>
   .
<?php
}

function eme_meta_box_div_event_single_event_format($event) {
?>
   <textarea name="event_single_event_format" id="event_single_event_format" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_single_event_format']);?></textarea>
   <br />
   <p><?php _e ( 'The format of the single event page.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_contactperson_email_body($event) {
?>
   <textarea name="event_contactperson_email_body" id="event_contactperson_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_contactperson_email_body']);?></textarea>
   <br />
   <p><?php _e ( 'The format of the email which will be sent to the contact person.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_registration_recorded_ok_html($event) {
?>
   <textarea name="event_registration_recorded_ok_html" id="event_registration_recorded_ok_html" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_registration_recorded_ok_html']);?></textarea>
   <br />
   <p><?php _e ( 'The text (html allowed) shown to the user when the booking has been made successfully.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_respondent_email_body($event) {
?>
   <textarea name="event_respondent_email_body" id="event_respondent_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_respondent_email_body']);?></textarea>
   <br />
   <p><?php _e ( 'The format of the email which will be sent to the respondent.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_registration_pending_email_body($event) {
?>
   <textarea name="event_registration_pending_email_body" id="event_registration_pending_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_registration_pending_email_body']);?></textarea>
   <br />
   <p><?php _e ( 'The format of the email which will be sent to the respondent if the registration is pending.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_registration_updated_email_body($event) {
?>
   <textarea name="event_registration_updated_email_body" id="event_registration_updated_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_registration_updated_email_body']);?></textarea>
   <br />
   <p><?php _e ( 'The format of the email which will be sent to the respondent if the registration has been updated by an admin.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_registration_form_format($event) {
?>
   <textarea name="event_registration_form_format" id="event_registration_form_format" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_registration_form_format']);?></textarea>
   <br />
   <p><?php _e ( 'The registration form format.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_event_cancel_form_format($event) {
?>
   <textarea name="event_cancel_form_format" id="event_cancel_form_format" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_cancel_form_format']);?></textarea>
   <br />
   <p><?php _e ( 'The cancel registration form format.','eme');?>
   <br />
   <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
   </p>
<?php
}

function eme_meta_box_div_location_name($event) {
   $use_select_for_locations = get_option('eme_use_select_for_locations');
   // qtranslate there? Then we need the select, otherwise locations will be created again...
   if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') || defined('ICL_LANGUAGE_CODE')) {
      $use_select_for_locations=1;
   }
   $location = eme_get_location ( $event['location_id'] );
?>
   <table id="eme-location-data">
   <tr>
   <?php
   if($use_select_for_locations) {
      $location_0 = eme_new_location();
      $location_0['location_id']=0;
      $locations = eme_get_locations();
   ?>
      <th><?php _e('Location','eme') ?></th>
      <td> 
      <select name="location-select-id" id='location-select-id' size="1">
      <option value="<?php echo $location_0['location_id'] ?>" ><?php echo eme_trans_sanitize_html($location_0['location_name']) ?></option>
      <?php 
      $selected_location=$location_0;
      foreach($locations as $tmp_location) {
         $selected = "";
         if (isset($location['location_id']) && $location['location_id'] == $tmp_location['location_id']) {
            $selected_location=$location;
            $selected = "selected='selected' ";
         }
         ?>
         <option value="<?php echo $tmp_location['location_id'] ?>" <?php echo $selected ?>><?php echo eme_trans_sanitize_html($tmp_location['location_name']) ?></option>
      <?php
      }
      ?>
      </select>
      <input type='hidden' name='location-select-name' value='<?php echo eme_trans_sanitize_html($selected_location['location_name'])?>'/>
      <input type='hidden' name='location-select-town' value='<?php echo eme_trans_sanitize_html($selected_location['location_town'])?>'/>
      <input type='hidden' name='location-select-address' value='<?php echo eme_trans_sanitize_html($selected_location['location_address'])?>'/>      
      <input type='hidden' name='location-select-latitude' value='<?php echo eme_trans_sanitize_html($selected_location['location_latitude'])?>'/>      
      <input type='hidden' name='location-select-longitude' value='<?php echo eme_trans_sanitize_html($selected_location['location_longitude'])?>'/>      
      </td>
   <?php
   } else {
   ?>
      <th><?php _e ( 'Name','eme' )?>&nbsp;</th>
      <td><input name="translated_location_name" type="hidden" value="<?php echo eme_trans_sanitize_html($location['location_name'])?>" /><input id="location_name" type="text" name="location_name" value="<?php echo eme_trans_sanitize_html($location['location_name'])?>" /></td>
   <?php
   }
   $gmap_is_active = get_option('eme_gmap_is_active' );
   if ($gmap_is_active) {
      ?>
      <td rowspan='6'>
      <div id='eme-admin-map-not-found'>
      <p>
      <?php _e ( 'Map not found','eme' ); ?>
      </p>
      </div>
      <div id='eme-admin-location-map'></div></td>
      <?php
   }
   // end of IF_GMAP_ACTIVE ?>
   </tr>
   <?php
   if (!$use_select_for_locations) {
   ?>
      <tr>
      <td colspan='2'>
      <?php _e ( 'The name of the location where the event takes place. You can use the name of a venue, a square, etc', 'eme' );?>
      <br />
      <?php _e ( 'If you leave this empty, the map will NOT be shown for this event', 'eme' );?>
      </td>
      </tr>
    <?php
    } else {
    ?>
       <tr >
       <td colspan='2'  rowspan='5' style='vertical-align: top'>
       <?php _e ( 'Select a location for your event', 'eme' )?>
       </td>
       </tr>
    <?php } ?>
    <?php if (!$use_select_for_locations) { ?> 
       <tr>
       <th><?php _e ( 'Address:', 'eme' )?> &nbsp;</th>
       <td><input id="location_address" type="text" name="location_address" value="<?php echo $location['location_address']; ?>" /></td>
       </tr>
       <tr>
       <td colspan='2'>
       <?php _e ( 'The address of the location where the event takes place. Example: 21, Dominick Street', 'eme' )?>
       </td>
       </tr>
       <tr>
       <th><?php _e ( 'Town:', 'eme' )?> &nbsp;</th>
       <td><input id="location_town" type="text" name="location_town" value="<?php echo $location['location_town']?>" /></td>
       </tr>
       <tr>
       <td colspan='2'>
       <?php _e ( 'The town where the location is located. If you\'re using the Google Map integration and want to avoid geotagging ambiguities include the country in the town field. Example: Verona, Italy.', 'eme' )?>
       </td>
       </tr>
       <tr>
       <th><?php _e ( 'Latitude:', 'eme' )?> &nbsp;</th>
       <td><input id="location_latitude" type="text" name="location_latitude" value="<?php echo $location['location_latitude']?>" /></td>
       </tr>
       <tr>
       <th><?php _e ( 'Longitude:', 'eme' )?> &nbsp;</th>
       <td><input id="location_longitude" type="text" name="location_longitude" value="<?php echo $location['location_longitude']?>" /></td>
       </tr>
       <tr>
       <td colspan='2'>
       <?php _e ( 'If you\'re using the Google Map integration and are really serious about the correct place, use these.', 'eme' )?>
       </td>
       </tr>
    <?php } ?>
    </table>
<?php
}
 
function eme_meta_box_div_event_notes($event) {
?>
   <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
   <!-- we need content for qtranslate as ID -->
   <?php wp_editor($event['event_notes'],"content"); ?>
   </div>
   <br />
   <?php _e ( 'Details about the event', 'eme' )?>
<?php
}

function eme_meta_box_div_event_image($event) {
    if (isset($event['event_image_id']) && !empty($event['event_image_id']))
       $event['event_image_url'] = wp_get_attachment_url($event['event_image_id']);
?>
   <div id="event_current_image" class="postarea">
   <?php if (isset($event['event_image_url']) && !empty($event['event_image_url'])) {
      _e('Current image:', 'eme');
      echo "<img id='eme_event_image_example' src='".$event['event_image_url']."' width='200' />";
      echo "<input type='hidden' name='event_image_url' id='event_image_url' value='".$event['event_image_url']."' />";
   } else {
      echo "<img id='eme_event_image_example' src='' alt='' width='200' />";
      echo "<input type='hidden' name='event_image_url' id='event_image_url' />";
   }
   if (isset($event['event_image_id']) && !empty($event['event_image_id'])) {
      echo "<input type='hidden' name='event_image_id' id='event_image_id' value='".$event['event_image_id']."' />";
   } else {
      echo "<input type='hidden' name='event_image_id' id='event_image_id' />";
   }
   // based on code found at http://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
   ?>
   </div>
   <br />

   <div class="uploader">
   <input type="button" name="event_image_button" id="event_image_button" value="<?php _e ( 'Set a featured image', 'eme' )?>" />
   <input type="button" id="eme_remove_old_image" name="eme_remove_old_image" value=" <?php _e ( 'Unset featured image', 'eme' )?>" />
   </div>
<script>
jQuery(document).ready(function($){

  $('#eme_remove_old_image').click(function(e) {
        $('#event_image_url').val('');
        $('#event_image_id').val('');
        $('#eme_event_image_example' ).attr("src",'');
  });
  $('#event_image_button').click(function(e) {
    var button = $(this);
    var _orig_send_attachment = wp.media.editor.send.attachment;
    var eme_custom_media = true;

    wp.media.editor.send.attachment = function(props, attachment){
      if ( eme_custom_media ) {
        $('#event_image_url').val(attachment.url);
        $('#event_image_id').val(attachment.id);
        $('#eme_event_image_example' ).attr("src",attachment.url);
      } else {
        return _orig_send_attachment.apply( this,[props, attachment] );
      };
      eme_custom_media = false;
    }

    wp.media.editor.open(button);
    return false;
  });
});
</script>
 
<?php
}

function eme_meta_box_div_event_attributes($event) {
    eme_attributes_form($event);
}

function eme_meta_box_div_event_url($event) {
?>
   <input type="text" id="event_url" name="event_url" value="<?php echo eme_sanitize_html($event['event_url']); ?>" />
   <br />
   <?php _e ( 'If this is filled in, the single event URL will point to this url instead of the standard event page.', 'eme' )?>
<?php
}

function eme_admin_map_script() {
   global $plugin_page;
   if (!get_option('eme_gmap_is_active' ))
      return;
?>
<script src="//maps.google.com/maps/api/js?v=3.1&amp;sensor=false" type="text/javascript"></script>
<script type="text/javascript">
         //<![CDATA[
          function loadMap(location, town, address) {
            var latlng = new google.maps.LatLng(-34.397, 150.644);
            var myOptions = {
               zoom: 13,
               center: latlng,
               scrollwheel: <?php echo get_option('eme_gmap_zooming') ? 'true' : 'false'; ?>,
               disableDoubleClickZoom: true,
               mapTypeControlOptions: {
                  mapTypeIds:[google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
               },
               mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            jQuery("#eme-admin-location-map").show();
            var map = new google.maps.Map(document.getElementById("eme-admin-location-map"), myOptions);
            var geocoder = new google.maps.Geocoder();
            if (address !="") {
               searchKey = address + ", " + town;
            } else {
               searchKey =  location + ", " + town;
            }
               
            if (address !="" || town!="") {
               geocoder.geocode( { 'address': searchKey}, function(results, status) {
                  if (status == google.maps.GeocoderStatus.OK) {
                     map.setCenter(results[0].geometry.location);
                     var marker = new google.maps.Marker({
                        map: map, 
                        position: results[0].geometry.location
                     });
                     var infowindow = new google.maps.InfoWindow({
                        content: '<div class=\"eme-location-balloon\"><strong>' + location +'</strong><p>' + address + '</p><p>' + town + '</p></div>'
                     });
                     infowindow.open(map,marker);
                     jQuery('input#location_latitude').val(results[0].geometry.location.lat());
                     jQuery('input#location_longitude').val(results[0].geometry.location.lng());
                     jQuery("#eme-admin-location-map").show();
                     jQuery('#eme-admin-map-not-found').hide();
                  } else {
                     jQuery("#eme-admin-location-map").hide();
                     jQuery('#eme-admin-map-not-found').show();
                  }
               });
            } else {
               jQuery("#eme-admin-location-map").hide();
               jQuery('#eme-admin-map-not-found').show();
            }
         }
      
         function loadMapLatLong(location, town, address, lat, long) {
            if (lat != 0 && long != 0) {
               var latlng = new google.maps.LatLng(lat, long);
               var myOptions = {
                  zoom: 13,
                  center: latlng,
                  scrollwheel: <?php echo get_option('eme_gmap_zooming') ? 'true' : 'false'; ?>,
                  disableDoubleClickZoom: true,
                  mapTypeControlOptions: {
                     mapTypeIds:[google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
                  },
                  mapTypeId: google.maps.MapTypeId.ROADMAP
               }
               jQuery("#eme-admin-location-map").show();
               var map = new google.maps.Map(document.getElementById("eme-admin-location-map"), myOptions);
               var marker = new google.maps.Marker({
                  map: map, 
                  position: latlng
               });
               var infowindow = new google.maps.InfoWindow({
                  content: '<div class=\"eme-location-balloon\"><strong>' + location +'</strong><p>' + address + '</p><p>' + town + '</p></div>'
               });
               infowindow.open(map,marker);
               jQuery("#eme-admin-location-map").show();
               jQuery('#eme-admin-map-not-found').hide();
            } else {
               loadMap(location, town, address);
            }
         }
 
         jQuery(document).ready(function() {
            <?php 
            $use_select_for_locations = get_option('eme_use_select_for_locations');
            // qtranslate there? Then we need the select
            if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') || defined('ICL_LANGUAGE_CODE')) {
               $use_select_for_locations=1;
            }

            // if we're creating a new event, or editing an event *AND*
            // the use_select_for_locations options is on or qtranslate is installed
            // then we do the select thing
            // We check on the new/edit event because this javascript is also executed for editing locations, and then we don't care
            // about the use_select_for_locations parameter
            if (
               ((isset($_REQUEST['eme_admin_action']) && ($_REQUEST['eme_admin_action'] == 'edit_event' || $_REQUEST['eme_admin_action'] == 'duplicate_event' || $_REQUEST['eme_admin_action'] == 'edit_recurrence')) || ( $plugin_page == 'eme-new_event')) && $use_select_for_locations) { ?>
               eventLocation = jQuery("input[name='location-select-name']").val(); 
               eventTown = jQuery("input[name='location-select-town']").val();
               eventAddress = jQuery("input[name='location-select-address']").val(); 
               eventLat = jQuery("input[name='location-select-latitude']").val();
               eventLong = jQuery("input[name='location-select-longitude']").val();
            <?php } else { ?>
               eventLocation = jQuery("input[name='translated_location_name']").val(); 
               eventTown = jQuery("input#location_town").val(); 
               eventAddress = jQuery("input#location_address").val();
               eventLat = jQuery("input#location_latitude").val();
               eventLong = jQuery("input#location_longitude").val();
            <?php } ?>

            loadMapLatLong(eventLocation, eventTown, eventAddress, eventLat, eventLong);
         
            jQuery("input[name='location_name']").focus(function(){
               eventLocation = jQuery("input[name='location_name']").val();
            });

            jQuery("input[name='location_name']").blur(function(){
               newEventLocation = jQuery("input[name='location_name']").val();
               eventTown = jQuery("input#location_town").val(); 
               eventAddress = jQuery("input#location_address").val();
               eventLat = jQuery("input#location_latitude").val();
               eventLong = jQuery("input#location_longitude").val();
               if (newEventLocation != eventLocation) {
                  loadMapLatLong(newEventLocation, eventTown, eventAddress, eventLat, eventLong); 
               }
            });
            jQuery("input#location_town").focus(function(){
               eventTown = jQuery("input#location_town").val(); 
            });
            jQuery("input#location_town").blur(function(){
               eventLocation = jQuery("input[name='translated_location_name']").val(); 
               newEventTown = jQuery("input#location_town").val();
               eventAddress = jQuery("input#location_address").val();
               eventLat = jQuery("input#location_latitude").val();
               eventLong = jQuery("input#location_longitude").val();
               if (newEventTown != eventTown) {
                  loadMap(eventLocation, newEventTown, eventAddress); 
               }
            });
            jQuery("input#location_address").focus(function(){
               eventAddress = jQuery("input#location_address").val();
            });
            jQuery("input#location_address").blur(function(){
               eventLocation = jQuery("input[name='translated_location_name']").val(); 
               eventTown = jQuery("input#location_town").val(); 
               newEventAddress = jQuery("input#location_address").val();
               eventLat = jQuery("input#location_latitude").val();
               eventLong = jQuery("input#location_longitude").val();
               if (newEventAddress != eventAddress) {
                  loadMap(eventLocation, eventTown, newEventAddress); 
               }
            });
            jQuery("input#location_latitude").focus(function(){
               eventLat = jQuery("input#location_latitude").val();
            });
            jQuery("input#location_latitude").blur(function(){
               eventLocation = jQuery("input[name='translated_location_name']").val(); 
               eventTown = jQuery("input#location_town").val(); 
               eventAddress = jQuery("input#location_address").val();
               newLat = jQuery("input#location_latitude").val();
               eventLong = jQuery("input#location_longitude").val();
               if (newLat != eventLat) {
                  loadMapLatLong(eventLocation, eventTown, eventAddress, newLat, eventLong); 
               }
            });
            jQuery("input#location_longitude").focus(function(){
               eventLong = jQuery("input#location_longitude").val();
            });
            jQuery("input#location_longitude").blur(function(){
               eventLocation = jQuery("input[name='translated_location_name']").val(); 
               eventTown = jQuery("input#location_town").val(); 
               eventAddress = jQuery("input#location_address").val();
               eventLat = jQuery("input#location_latitude").val();
               newLong = jQuery("input#location_longitude").val();
               if (newLong != eventLong) {
                  loadMapLatLong(eventLocation, eventTown, eventAddress, eventLat, newLong); 
               }
            });
            }); 
            jQuery(document).unload(function() {
            GUnload();
         });
          //]]>
      </script>
<?php
}

function eme_rss_link($justurl = 0, $echo = 1, $text = "RSS", $scope="future", $order = "ASC",$category='',$author='',$contact_person='',$limit=5, $location_id='',$title='') {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'echo' => 1, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'contact_person' => '', 'limit' => 5, 'location_id' => '', 'title' => '' );
      
      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = $r['echo'];
   }
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $echo = ($echo==="false" || $echo==="O") ? false : $echo;
   if ($text == '')
      $text = "RSS";
   $url = site_url ("/?eme_rss=main&scope=$scope&order=$order&category=$category&author=$author&contact_person=$contact_person&limit=$limit&location_id=$location_id&title=".urlencode($title));
   $link = "<a href='$url'>$text</a>";
   
   if ($justurl)
      $result = $url;
   else
      $result = $link;
   if ($echo)
      echo $result;
   else
      return $result;
}

function eme_rss_link_shortcode($atts) {
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'contact_person' => '', 'limit' => 5, 'location_id' => '', 'title' => '' ), $atts ) );
   $result = eme_rss_link ( "justurl=$justurl&echo=0&text=$text&limit=$limit&scope=$scope&order=$order&category=$category&author=$author&contact_person=$contact_person&location_id=$location_id&title=".urlencode($title) );
   return $result;
}

function eme_rss() {
      if (isset($_GET['limit'])) {
         $limit=intval($_GET['limit']);
      } else {
         $limit=get_option('eme_event_list_number_items' );
      }
      if (isset($_GET['author'])) {
         $author=$_GET['author'];
      } else {
         $author="";
      }
      if (isset($_GET['contact_person'])) {
         $author=$_GET['contact_person'];
      } else {
         $contact_person="";
      }
      if (isset($_GET['order'])) {
         $order=$_GET['order'];
      } else {
         $order="ASC";
      }
      if (isset($_GET['category'])) {
         $category=$_GET['category'];
      } else {
         $category=0;
      }
      if (isset($_GET['location_id'])) {
         $location_id=$_GET['location_id'];
      } else {
         $location_id='';
      }
      if (isset($_GET['scope'])) {
         $scope=$_GET['scope'];
      } else {
         $scope="future";
      }
      if (isset($_GET['title'])) {
         $main_title=$_GET['title'];
      } else {
         $main_title=get_option('eme_rss_main_title' );
      }
      echo "<?xml version='1.0'?>\n";
      
      ?>
<rss version="2.0">
<channel>
<title><?php
      echo eme_sanitize_rss($main_title);
      ?></title>
<link><?php
      $events_page_link = eme_get_events_page(true, false);
      echo eme_sanitize_rss($events_page_link);
      ?></link>
<description><?php
      echo eme_sanitize_rss(get_option('eme_rss_main_description' ));
      ?></description>
<docs>
http://blogs.law.harvard.edu/tech/rss
</docs>
<generator>
Weblog Editor 2.0
</generator>
<?php
      $title_format = get_option('eme_rss_title_format');
      $description_format = get_option('eme_rss_description_format');
      $events = eme_get_events ( $limit, $scope, $order, 0, $location_id, $category, $author, $contact_person );
      # some RSS readers don't like it when an empty feed without items is returned, so we add a dummy item then
      if (empty ( $events )) {
         echo "<item>\n";
         echo "<title></title>\n";
         echo "<link></link>\n";
         echo "</item>\n";
      } else {
         foreach ( $events as $event ) {
             $title = eme_sanitize_rss(eme_replace_placeholders ( $title_format, $event, "rss" ));
             $description = eme_sanitize_rss(eme_replace_placeholders ( $description_format, $event, "rss" ));
             $event_link = eme_sanitize_rss(eme_event_url($event));
             echo "<item>\n";
             echo "<title>$title</title>\n";
             echo "<link>$event_link</link>\n";
             if (get_option('eme_rss_show_pubdate' )) {
                if (get_option('eme_rss_pubdate_startdate' )) {
                   $timezoneoffset=date('O');
                   echo "<pubDate>".date_i18n ('D, d M Y H:i:s $timezoneoffset', strtotime($event['event_start_date']." ".$event['event_start_time']))."</pubDate>\n";
                } else {
                   echo "<pubDate>".date_i18n ('D, d M Y H:i:s +0000', strtotime($event['modif_date_gmt']))."</pubDate>\n";
                }
             }
             echo "<description>$description</description>\n";
             if (get_option('eme_categories_enabled')) {
                $categories = eme_sanitize_rss(eme_replace_placeholders ( "#_CATEGORIES", $event, "rss" ));
                echo "<category>$categories</category>\n";
             }
             echo "</item>\n";
         }
      }
      ?>

</channel>
</rss>

<?php
}

function eme_general_head() {
   if (eme_is_single_event_page()) {
      $event=eme_get_event(get_query_var('event_id'));
      // I don't know if the canonical rel-link is needed, but since WP adds it by default ...
      $canon_url=eme_event_url($event);
      echo "<link rel=\"canonical\" href=\"$canon_url\" />\n";
      $extra_headers_format=get_option('eme_event_html_headers_format');
      if ($extra_headers_format != "") {
         $extra_headers_lines = explode ("\n",$extra_headers_format);
         foreach ($extra_headers_lines as $extra_header_format) {
            # the text format already removes most of html code, so let's use that
            $extra_header = strip_shortcodes(eme_replace_placeholders ($extra_header_format, $event, "text",0 ));
            # the text format converts \n to \r\n but we want one line only
            $extra_header = trim(preg_replace('/\r\n/', "", $extra_header));
            if ($extra_header != "")
               echo $extra_header."\n";
         }
      }
   } elseif (eme_is_single_location_page()) {
      $location=eme_get_location(get_query_var('location_id'));
      $canon_url=eme_location_url($location);
      echo "<link rel=\"canonical\" href=\"$canon_url\" />\n";
      $extra_headers_format=get_option('eme_location_html_headers_format');
      if ($extra_headers_format != "") {
         $extra_headers_lines = explode ("\n",$extra_headers_format);
         foreach ($extra_headers_lines as $extra_header_format) {
            # the text format already removes most of html code, so let's use that
            $extra_header = strip_shortcodes(eme_replace_locations_placeholders ($extra_header_format, $location, "text", 0 ));
            # the text format converts \n to \r\n but we want one line only
            $extra_header = trim(preg_replace('/\r\n/', "", $extra_header));
            if ($extra_header != "")
               echo $extra_header."\n";
         }
      }
   }
   $gmap_is_active = get_option('eme_gmap_is_active' );
   $load_js_in_header = get_option('eme_load_js_in_header' );
   if ($gmap_is_active && $load_js_in_header) {
      echo "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_location_map.js'></script>\n";
   }
}

function eme_change_canonical_url() {
   if (eme_is_single_event_page() || eme_is_single_location_page()) {
      remove_action( 'wp_head', 'rel_canonical' );
   }
}

function eme_general_css() {
   $eme_css_url= EME_PLUGIN_URL."events_manager.css";
   wp_register_style('eme_stylesheet',$eme_css_url);
   wp_enqueue_style('eme_stylesheet'); 

   $eme_css_name=get_stylesheet_directory()."/eme.css";
   $eme_css_url=get_stylesheet_directory_uri()."/eme.css";
   if (file_exists($eme_css_name))
      wp_register_style('eme_stylesheet_extra',$eme_css_url,'eme_stylesheet');
   wp_enqueue_style('eme_stylesheet_extra'); 
}

function eme_general_footer() {
   global $eme_need_gmap_js;
   $gmap_is_active = get_option('eme_gmap_is_active' );
   $load_js_in_header = get_option('eme_load_js_in_header' );
   // we only include the map js if wanted/needed
   if (!$load_js_in_header && $gmap_is_active && $eme_need_gmap_js) {
      echo "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_location_map.js'></script>\n";
   }
}

function eme_db_insert_event($event,$event_is_part_of_recurrence=0) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;

   $event['creation_date']=current_time('mysql', false);
   $event['modif_date']=current_time('mysql', false);
   $event['creation_date_gmt']=current_time('mysql', true);
   $event['modif_date_gmt']=current_time('mysql', true);

   // remove possible unwanted fields
   if (isset($event['event_id'])) {
      unset($event['event_id']);
   }

   // some sanity checks
   if ($event['event_end_date']<$event['event_start_date']) {
      $event['event_end_date']=$event['event_start_date'];
   }
   $event_properties = @unserialize($event['event_properties']);
   if ($event_properties['all_day']) {
      $event['event_start_time']="00:00:00";
      $event['event_end_time']="23:59:59";
   }
   // if the end day/time is lower than the start day/time, then put
   // the end day one day (86400 secs) ahead, but only if
   // the end time has been filled in, if it is empty then we keep
   // the end date as it is
   if ($event['event_end_time'] != "00:00:00") {
      $startstring=strtotime($event['event_start_date']." ".$event['event_start_time']);
      $endstring=strtotime($event['event_end_date']." ".$event['event_end_time']);
      if ($endstring<$startstring) {
         $event['event_end_date']=date("Y-m-d",strtotime($event['event_start_date'])+86400);
      }
   }

   if (has_filter('eme_event_preinsert_filter')) $event=apply_filters('eme_event_preinsert_filter',$event);

   $wpdb->show_errors(true);
   if (!$wpdb->insert ( $table_name, $event )) {
      $wpdb->print_error();
      return false;
   } else {
      $event_ID = $wpdb->insert_id;
      $event['event_id']=$event_ID;
      // the eme_insert_event_action is only executed for single events, not those part of a recurrence
      if (!$event_is_part_of_recurrence && has_action('eme_insert_event_action')) do_action('eme_insert_event_action',$event);
      return $event_ID;
   }
}

function eme_db_update_event($event,$event_id,$event_is_part_of_recurrence=0) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;


   // backwards compatible: older versions gave directly the where array instead of the event_id
   if (!is_array($event_id)) 
      $where=array('event_id' => $event_id);
   else
      $where = $event_id;

   $event['modif_date']=current_time('mysql', false);
   $event['modif_date_gmt']=current_time('mysql', true);

   // some sanity checks
   if ($event['event_end_date']<$event['event_start_date']) {
      $event['event_end_date']=$event['event_start_date'];
   }
   $event_properties = @unserialize($event['event_properties']);
   if ($event_properties['all_day']) {
      $event['event_start_time']="00:00:00";
      $event['event_end_time']="23:59:59";
   }
   // if the end day/time is lower than the start day/time, then put
   // the end day one day (86400 secs) ahead, but only if
   // the end time has been filled in, if it is empty then we keep
   // the end date as it is
   if ($event['event_end_time'] != "00:00:00") {
      $startstring=strtotime($event['event_start_date']." ".$event['event_start_time']);
      $endstring=strtotime($event['event_end_date']." ".$event['event_end_time']);
      if ($endstring<=$startstring) {
         $event['event_end_date']=date("Y-m-d",strtotime($event['event_start_date'])+86400);
      }
   }

   $wpdb->show_errors(true);
   if (!$wpdb->update ( $table_name, $event, $where )) {
      $wpdb->print_error();
      return false;
   } else {
      $event['event_id']=$event_id;
      // the eme_update_event_action is only executed for single events, not those part of a recurrence
      if (!$event_is_part_of_recurrence && has_action('eme_update_event_action')) {
         // we do this call so all parameters for the event are filled, otherwise for an update this might not be the case
         $event = eme_get_event($event_id);
         do_action('eme_update_event_action',$event);
      }
      return true;
   }
}

function eme_db_delete_event($event) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   $sql = "DELETE FROM $table_name WHERE event_id = '".$event['event_id']."';";
   // also delete associated image
   $image_basename= IMAGE_UPLOAD_DIR."/event-".$event['event_id'];
   eme_delete_image_files($image_basename);
   if ($wpdb->query ( $sql )) {
      if (has_action('eme_delete_event_action')) do_action('eme_delete_event_action',$event);
   }
}

add_filter ( 'favorite_actions', 'eme_favorite_menu' );
function eme_favorite_menu($actions) {
   // add quick link to our favorite plugin
   $actions['admin.php?page=eme-new_event'] = array (__ ( 'Add an event', 'eme' ), get_option('eme_cap_add_event') );
   return $actions;
}

function eme_alert_events_page() {
   global $pagenow;
   $events_page_id = get_option('eme_events_page' );
   if ($pagenow == 'post.php' && ( get_query_var('post_type') && 'page' == get_query_var('post_type') ) && isset ( $_GET['eme_admin_action'] ) && $_GET['eme_admin_action'] == 'edit' && isset ( $_GET['post'] ) && $_GET['post'] == "$events_page_id") {
      $message = sprintf ( __ ( "This page corresponds to <strong>Events Made Easy</strong> events page. Its content will be overriden by <strong>Events Made Easy</strong>. If you want to display your content, you can can assign another page to <strong>Events Made Easy</strong> in the the <a href='%s'>Settings</a>. ", 'eme' ), 'admin.php?page=eme-options' );
      $notice = "<div class='error'><p>$message</p></div>";
      echo $notice;
   }
}

function eme_enqueue_js(){
   global $plugin_page;
   if ( in_array( $plugin_page, array('eme-locations', 'eme-new_event', 'events-manager') ) ) {
      // we need this to have the "postbox" javascript loaded, so closing/opening works for those divs
      wp_enqueue_script('post');
   }
   if ( in_array( $plugin_page, array('eme-locations', 'eme-new_event', 'events-manager','eme-options') ) ) {
      wp_enqueue_script('jquery-datepick',EME_PLUGIN_URL."js/jquery-datepick/jquery.datepick.js");
   }
   if ( in_array( $plugin_page, array('eme-registration-approval','eme-registration-seats','events-manager','eme-people') ) ) {
      wp_enqueue_script('jquery-datatables',EME_PLUGIN_URL."js/jquery-datatables/js/jquery.dataTables.min.js");
      wp_enqueue_script('datatables-clearsearch',EME_PLUGIN_URL."js/jquery-datatables/plugins/datatables_clearsearch.js");
   }
}

# return number of days until next event or until the specified event
function eme_countdown($atts) {
   extract ( shortcode_atts ( array ('id'=>''), $atts ) );

   $now = date("Y-m-d");
   if ($id!="") {
      $event=eme_get_event($id);
   } else {
      $newest_event_array=eme_get_events(1);
      $event=$newest_event_array[0];
   }
   $start_date=$event['event_start_date'];
   return eme_daydifference($now,$start_date);
}

function eme_image_url_for_event($event) {
   if (isset($event['recurrence_id']) && $event['recurrence_id']>0) {
      $image_basename= IMAGE_UPLOAD_DIR."/recurrence-".$event['recurrence_id'];
      $image_baseurl= IMAGE_UPLOAD_URL."/recurrence-".$event['recurrence_id'];
   } else {
      $image_basename= IMAGE_UPLOAD_DIR."/event-".$event['event_id'];
      $image_baseurl= IMAGE_UPLOAD_URL."/event-".$event['event_id'];
   }
   $mime_types = array('gif','jpg','png');
   foreach($mime_types as $type) {
      $file_path = $image_basename.".".$type;
      $file_url = $image_baseurl.".".$type;
      if (file_exists($file_path)) {
         return $file_url;
      }
   }
   return '';
}

?>
