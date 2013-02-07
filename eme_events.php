<?php

function eme_new_event() {

   $event = array (
      "event_id" => '',
      "event_name" => '',
      "event_status" => get_option('eme_event_initial_state'),
      "event_start_date" => '',
      "event_start_day" => '',
      "event_start_month" => '',
      "event_start_year" => '',
      "event_start_time" => '',
      "event_start_12h_time" => '',
      "event_start_24h_time" => '',
      "event_end_date" => '',
      "event_end_day" => '',
      "event_end_month" => '',
      "event_end_year" => '',
      "event_end_time" => '',
      "event_end_12h_time" => '',
      "event_end_24h_time" => '',
      "event_notes" => '',
      "event_rsvp" => 0,
      "use_paypal" => 0,
      "price" => 0,
      "currency" => "EUR",
      "rsvp_number_days" => 0,
      "registration_requires_approval" => 0,
      "registration_wp_users_only" => 0,
      "event_seats" => 0,
      "location_id" => 0,
      "event_author" => 0,
      "event_contactperson_id" => get_option('eme_default_contact_person'),
      "event_category_ids" => '',
      "event_attributes" => '',
      "event_page_title_format" => '',
      "event_single_event_format" => '',
      "event_contactperson_email_body" => '',
      "event_respondent_email_body" => '',
      "event_registration_pending_email_body" => '',
      "event_registration_form_format" => '',
      "event_slug" => '',
      "event_image_url" => '',
      "event_url" => '',
      "recurrence_id" => 0,
      "recurrence_freq" => '',
      "recurrence_start_date" => '',
      "recurrence_end_date" => '',
      "recurrence_interval" => '',
      "recurrence_byweekno" => '',
      "recurrence_byday" => '',
      "location_name" => '',
      "location_address" => '',
      "location_town" => '',
      "location_latitude" => '',
      "location_longitude" => '',
      "location_image_url" => '',
      "location_slug" => '',
      "location_url" => ''
   );
   return $event;
}

function eme_new_event_page() {
   // check the user is allowed to make changes
   if ( !current_user_can( get_option('eme_cap_add_event')  ) ) {
      return;
   }

   $title = __ ( "Insert New Event", 'eme' );
   $event = eme_new_event();

   if (isset($_GET['action']) && $_GET['action'] == "insert_event") {
      eme_events_page();
   } else {
      eme_event_form ($event, $title, '');
   }
}

function eme_events_page() {
   global $wpdb;

   $extra_conditions = array();
   $action = isset($_GET ['action']) ? $_GET ['action'] : '';
   $event_ID = isset($_GET ['event_id']) ? intval($_GET ['event_id']) : '';
   $recurrence_ID = isset($_GET ['recurrence_id']) ? intval($_GET ['recurrence_id']) : '';
   $scope = isset($_GET ['scope']) ? $_GET ['scope'] : '';
   $offset = isset($_GET ['offset']) ? intval($_GET ['offset']) : '';
   $category = isset($_GET ['category']) ? intval($_GET ['category']) : 0;
   $order = isset($_GET ['order']) ? $_GET ['order'] : '';
   $selectedEvents = isset($_GET ['events']) ? $_GET ['events'] : '';
   $status = isset($_GET ['event_status']) ? intval($_GET ['event_status']) : '';
   if(!empty($status)) {
      $extra_conditions[] = 'event_status = '.$status;
   }

   
   // check if the user is allowed to do anything
   if ( !current_user_can( get_option('eme_cap_add_event') ) ) {
      $action="";
   }
   $current_userid=get_current_user_id();

   // Disable Hello to new user if requested
   if (current_user_can( get_option('eme_cap_settings') ) && isset ( $_GET ['disable_hello_to_user'] ) && $_GET ['disable_hello_to_user'] == 'true')
      update_option('eme_hello_to_user', 0 );

   if (current_user_can( get_option('eme_cap_settings') ) && isset ( $_GET ['disable_donate_message'] ) && $_GET ['disable_donate_message'] == 'true')
      update_option('eme_donation_done', 1 );

   // do the UTF-8 conversion if wanted
   if (current_user_can( get_option('eme_cap_settings') ) && isset ( $_GET ['do_character_conversion'] ) && $_GET ['do_character_conversion'] == 'true' && $wpdb->has_cap('collation')) {
                if ( ! empty($wpdb->charset)) {
                        $charset = "CHARACTER SET $wpdb->charset";
         $collate="";
         if ( ! empty($wpdb->collate) )
            $collate = "COLLATE $wpdb->collate";
                        eme_convert_charset(EVENTS_TBNAME,$charset,$collate);
                        eme_convert_charset(RECURRENCE_TBNAME,$charset,$collate);
                        eme_convert_charset(LOCATIONS_TBNAME,$charset,$collate);
                        eme_convert_charset(BOOKINGS_TBNAME,$charset,$collate);
                        eme_convert_charset(PEOPLE_TBNAME,$charset,$collate);
                        eme_convert_charset(CATEGORIES_TBNAME,$charset,$collate);
                }
      update_option('eme_conversion_needed', 0 );
      print "<div id=\"message\" class=\"updated\">".__('Conversion done, please check your events and restore from backup if you see any sign of troubles.')."</div>";
   }
   
   if ($order != "DESC")
      $order = "ASC";
   if ($offset == "")
      $offset = "0";

   $list_limit = get_option('eme_events_admin_limit');
   if ($list_limit<5 || $list_limit>200) {
      $list_limit=20;
      update_option('eme_events_admin_limit',$list_limit);
   }
   
   // DELETE action
   if ($action == 'deleteEvents') {
      foreach ( $selectedEvents as $event_ID ) {
         $tmp_event = array();
         $tmp_event = eme_get_event ( $event_ID );
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {  
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
      }
      
      $events = eme_get_events ( $list_limit+1, "future", $order, $offset );
      eme_events_table ( $events, $list_limit, __ ( 'Future events', 'eme' ), "future", $offset );
   }
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
      $events = eme_get_events ( $list_limit+1, "future", $order, $offset );
      eme_events_table ( $events, $list_limit, __ ( 'Future events', 'eme' ), "future", $offset );
   }

   // UPDATE or CREATE action
   if ($action == 'insert_event' || $action == 'update_event' || $action == 'update_recurrence') {
      $event = array();
      $location = array ();
      $event ['event_name'] = isset($_POST ['event_name']) ? trim(stripslashes ( $_POST ['event_name'] )) : '';
      if (!current_user_can( get_option('eme_cap_publish_event')) ) {
         $event['event_status']=STATUS_DRAFT;   
      } else {
         $event ['event_status'] = isset($_POST ['event_status']) ? stripslashes ( $_POST ['event_status'] ) : get_option('eme_event_initial_state');
      }
      $event ['event_start_date'] = isset($_POST ['event_start_date']) ? $_POST ['event_start_date'] : '';
      // for compatibility: check also the POST variable event_date
      $event ['event_start_date'] = isset($_POST ['event_date']) ? $_POST ['event_date'] : $event ['event_start_date'];
      $event ['event_end_date'] = isset($_POST ['event_end_date']) ? $_POST ['event_end_date'] : '';
      if (!_eme_is_date_valid($event['event_start_date']))
          $event['event_start_date'] = "";
      if (!_eme_is_date_valid($event['event_end_date']))
          $event['event_end_date'] = "";
      if (isset($_POST ['event_start_time']) && !empty($_POST ['event_start_time'])) {
         $event ['event_start_time'] = date ("H:i:00", strtotime ($_POST ['event_start_time']));
      } else {
         $event ['event_start_time'] = "00:00:00";
      }
      if (isset($_POST ['event_end_time']) && !empty($_POST ['event_end_time'])) {
         $event ['event_end_time'] = date ("H:i:00", strtotime ($_POST ['event_end_time']));
      } else {
         $event ['event_end_time'] = "00:00:00";
      }
      $recurrence ['recurrence_start_date'] = isset($_POST ['recurrence_start_date']) ? $_POST ['recurrence_start_date'] : $event ['event_start_date'];
      $recurrence ['recurrence_end_date'] = isset($_POST ['recurrence_end_date']) ? $_POST ['recurrence_end_date'] : $event ['event_end_date'];
      if (!_eme_is_date_valid($recurrence['recurrence_start_date']))
          $recurrence['recurrence_start_date'] = "";
      if (!_eme_is_date_valid($recurrence['recurrence_end_date']))
          $recurrence['recurrence_end_date'] = $recurrence['recurrence_end_date'];
      $recurrence ['recurrence_freq'] = isset($_POST['recurrence_freq']) ? $_POST['recurrence_freq'] : '';
      if ($recurrence ['recurrence_freq'] == 'weekly') {
         if (isset($_POST['recurrence_bydays'])) {
            $recurrence ['recurrence_byday'] = implode ( ",", $_POST['recurrence_bydays']);
         } else {
            $recurrence ['recurrence_byday'] = '';
         }
      } else {
         if (isset($_POST['recurrence_byday'])) {
            $recurrence ['recurrence_byday'] = $_POST['recurrence_byday'];
         } else {
            $recurrence ['recurrence_byday'] = '';
         }
      }
      $recurrence ['recurrence_interval'] = isset($_POST['recurrence_interval']) ? $_POST['recurrence_interval'] : 1;
      if ($recurrence ['recurrence_interval'] ==0)
         $recurrence['recurrence_interval']=1;
      $recurrence ['recurrence_byweekno'] = isset($_POST['recurrence_byweekno']) ? $_POST ['recurrence_byweekno'] : '';
      
      $event ['event_rsvp'] = (isset ($_POST ['event_rsvp']) && is_numeric($_POST ['event_rsvp'])) ? $_POST ['event_rsvp']:0;
      $event ['rsvp_number_days'] = (isset ($_POST ['rsvp_number_days']) && is_numeric($_POST ['rsvp_number_days'])) ? $_POST ['rsvp_number_days']:0;
      $event ['registration_requires_approval'] = (isset ($_POST ['registration_requires_approval']) && is_numeric($_POST ['registration_requires_approval'])) ? $_POST ['registration_requires_approval']:0;
      $event ['registration_wp_users_only'] = (isset ($_POST ['registration_wp_users_only']) && is_numeric($_POST ['registration_wp_users_only'])) ? $_POST ['registration_wp_users_only']:0;
      $event ['event_seats'] = (isset ($_POST ['event_seats']) && is_numeric($_POST ['event_seats'])) ? $_POST ['event_seats']:0;
      
      $event ['use_paypal'] = (isset ($_POST ['use_paypal']) && is_numeric($_POST ['use_paypal'])) ? $_POST ['use_paypal']:0;
      $event ['price'] = (isset ($_POST ['price']) && is_numeric($_POST ['price'])) ? $_POST ['price']:0;
      $event ['currency'] = isset ($_POST ['currency']) ? $_POST ['currency']:"EUR";

      if (isset ( $_POST ['event_contactperson_id'] ) && $_POST ['event_contactperson_id'] != '') {
         $event ['event_contactperson_id'] = $_POST ['event_contactperson_id'];
      } else {
         $event ['event_contactperson_id'] = 0;
      }
      
      //if (! _eme_is_time_valid ( $event_end_time ))
      // $event_end_time = $event_start_time;
      
      $location ['location_name'] = isset($_POST ['location_name']) ? trim(stripslashes($_POST ['location_name'])) : '';
      $location ['location_address'] = isset($_POST ['location_address']) ? stripslashes($_POST ['location_address']) : '';
      $location ['location_town'] = isset($_POST ['location_town']) ? stripslashes($_POST ['location_town']) : '';
      $location ['location_latitude'] = isset($_POST ['location_latitude']) ? $_POST ['location_latitude'] : '';
      $location ['location_longitude'] = isset($_POST ['location_longitude']) ? $_POST ['location_longitude'] : '';
      $location ['location_author']=$current_userid;
      $location ['location_description'] = "";
      //switched to WP TinyMCE field
      //$event ['event_notes'] = stripslashes ( $_POST ['event_notes'] );
      $event ['event_notes'] = isset($_POST ['content']) ? stripslashes($_POST ['content']) : '';
      $event ['event_page_title_format'] = isset($_POST ['event_page_title_format']) ? stripslashes ( $_POST ['event_page_title_format'] ) : '';
      $event ['event_single_event_format'] = isset($_POST ['event_single_event_format']) ? stripslashes ( $_POST ['event_single_event_format'] ) : '';
      $event ['event_contactperson_email_body'] = isset($_POST ['event_contactperson_email_body']) ? stripslashes ( $_POST ['event_contactperson_email_body'] ) : '';
      $event ['event_respondent_email_body'] = isset($_POST ['event_respondent_email_body']) ? stripslashes ( $_POST ['event_respondent_email_body'] ) : '';
      $event ['event_registration_pending_email_body'] = isset($_POST ['event_registration_pending_email_body']) ? stripslashes ( $_POST ['event_registration_pending_email_body'] ) : '';
      $event ['event_registration_form_format'] = isset($_POST ['event_registration_form_format']) ? stripslashes ( $_POST ['event_registration_form_format'] ) : '';
      $event ['event_url'] = isset($_POST ['event_url']) ? eme_strip_tags ( $_POST ['event_url'] ) : '';
      $event ['event_slug'] = isset($_POST ['event_slug']) ? eme_permalink_convert(eme_strip_tags ( $_POST ['event_slug'] )) : eme_permalink_convert($event ['event_name']);
      if (isset ($_POST['event_category_ids'])) {
         // the category id's need to begin and end with a comma
         // this is needed so we can later search for a specific
         // cat using LIKE '%,$cat,%'
         $event ['event_category_ids']="";
         foreach ($_POST['event_category_ids'] as $cat) {
            if (is_numeric($cat)) {
               if (empty($event ['event_category_ids'])) {
                  $event ['event_category_ids'] = "$cat";
               } else {
                  $event ['event_category_ids'] .= ",$cat";
               }
            }
         }
      } else {
         $event ['event_category_ids']="";
      }
      
      $event_attributes = array();
      for($i=1 ; isset($_POST["mtm_{$i}_ref"]) && trim($_POST["mtm_{$i}_ref"])!='' ; $i++ ) {
         if(trim($_POST["mtm_{$i}_name"]) != '') {
            $event_attributes[$_POST["mtm_{$i}_ref"]] = stripslashes($_POST["mtm_{$i}_name"]);
         }
      }
      $event['event_attributes'] = serialize($event_attributes);
      
      $validation_result = eme_validate_event ( $event );
      if ($validation_result == "OK") {
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
                  $event['location_id'] = $new_location['location_id'];
               }
            }
         }
         if (! $event_ID && ! $recurrence_ID) {
            $event['event_author']=$current_userid;
            // new event or new recurrence
            if (isset($_POST ['repeated_event']) && $_POST ['repeated_event']) {
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
                  $recurrence ['recurrence_id'] = $recurrence_ID;
                  //print_r($recurrence); 
                  if (eme_db_update_recurrence ($event, $recurrence )) {
                     $feedback_message = __ ( 'Recurrence updated!', 'eme' );
                     //if (has_action('eme_update_event_action')) do_action('eme_update_event_action',$event);
                  } else {
                     $feedback_message = __ ( 'Something went wrong with the recurrence update...', 'eme' );
                  }
               } else {
                  $feedback_message = __('You have no right to update','eme'). " '" . $tmp_event ['event_name'] . "' !";
               }
            } else {
               $tmp_event = eme_get_event ( $event_ID );
               if (current_user_can( get_option('eme_cap_edit_events')) ||
                   (current_user_can( get_option('eme_cap_author_event')) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {
                  if (isset($_POST ['repeated_event']) && $_POST ['repeated_event']) {
                     // we go from single event to recurrence: create the recurrence and delete the single event
                     eme_db_insert_recurrence ( $event, $recurrence );
                     eme_db_delete_event ( $tmp_event );
                     $feedback_message = __ ( 'New recurrent event inserted!', 'eme' );
                     //if (has_action('eme_insert_event_action')) do_action('eme_insert_event_action',$event);
                  } else {
                     // UPDATE old event
                     // unlink from recurrence in case it was generated by one
                     $event ['recurrence_id'] = 0;
                     if (eme_db_update_event ($event,$event_ID)) {
                        $feedback_message = "'" . $event ['event_name'] . "' " . __ ( 'updated', 'eme' ) . "!";
                     } else {
                        $feedback_message = __('Failed to update','eme')." '" . $event ['event_name']. "' !";
                     }
                     //if (has_action('eme_update_event_action')) do_action('eme_update_event_action',$event);
                  }
               } else {
                  $feedback_message = __('You have no right to update','eme'). " '" . $tmp_event ['event_name'] . "' !";
               }
            }
         }
         
         //$wpdb->query($sql); 
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         $events = eme_get_events ( $list_limit+1, "future" );
         eme_events_table ( $events, $list_limit, __ ( 'Future events', 'eme' ), "future", $offset );
      } else {
         // validation unsuccessful       
         echo "<div id='message' class='error '>
                  <p>" . __ ( "Ach, there's a problem here:", "eme" ) . " $validation_result</p>
              </div>";
         eme_event_form ( $event, "Edit event $event_ID", $event_ID );
      }
   }
   if ($action == 'edit_event') {
      if (! $event_ID) {
         $title = __ ( "Insert New Event", 'eme' );
         eme_event_form ( $event, $title, $event_ID );
      } else {
         $event = eme_get_event ( $event_ID );
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
                     // UPDATE old event
            $title = __ ( "Edit Event", 'eme' ) . " '" . $event ['event_name'] . "'";
            eme_event_form ( $event, $title, $event_ID );
         } else {
            $feedback_message = __('You have no right to update','eme'). " '" . $event ['event_name'] . "' !";
            echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
            $events = eme_get_events ( $list_limit+1, "future" );
            eme_events_table ( $events, $list_limit, __ ( 'Future events', 'eme' ), "future", $offset );
         }
      }
      
   }

   //Add duplicate event if requested
   if ($action == 'duplicate_event') {
      $event = eme_get_event ( $event_ID );
      if (current_user_can( get_option('eme_cap_edit_events')) ||
          (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
         eme_duplicate_event ( $event_ID );
      } else {
         $feedback_message = __('You have no right to update','eme'). " '" . $event ['event_name'] . "' !";
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         $events = eme_get_events ( $list_limit+1, "future" );
         eme_events_table ( $events, $list_limit, __ ( 'Future events', 'eme' ), "future", $offset );
      }
   }
   if ($action == 'edit_recurrence') {
      $event_ID = intval($_GET ['recurrence_id']);
      $recurrence = eme_get_recurrence ( $event_ID );
      if (current_user_can( get_option('eme_cap_edit_events')) ||
          (current_user_can( get_option('eme_cap_author_event')) && ($recurrence['event_author']==$current_userid || $recurrence['event_contactperson_id']==$current_userid))) {
         $title = __ ( "Reschedule", 'eme' ) . " '" . $recurrence ['event_name'] . "'";
         eme_event_form ( $recurrence, $title, $event_ID );
      } else {
         $feedback_message = __('You have no right to update','eme'). " '" . $recurrence ['event_name'] . "' !";
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         $events = eme_get_events ( $list_limit+1, "future" );
         eme_events_table ( $events, $list_limit, __ ( 'Future events', 'eme' ), "future", $offset );
      }
   }
   
   if ($action == "-1" || $action == "") {
      // No action, only showing the events list
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

      if (count($extra_conditions) > 0) {
         $extra_conditions = implode(' AND ', $extra_conditions);
      } else {
         $extra_conditions = '';
      }
 
      $events = eme_get_events ( $list_limit+1, $scope, $order, $offset, "", $category, '', '', 1, '', $extra_conditions);
      eme_events_table ( $events, $list_limit, $title, $scope, $offset, $category );
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
   $output [] = __( 'Please select a page','eme' );
   foreach ( $pages as $page ) {
      $output [$page ['id']] = $page ['post_title'];
   // $output [$page->ID] = $page->post_title;
   }
   return $output;
}

// Function composing the options page
function eme_options_page() {
   ?>
<div class="wrap">
<div id='icon-options-general' class='icon32'><br />
</div>
<h2><?php _e ( 'Event Manager Options', 'eme' ); ?></h2>
<?php admin_show_warnings();?>
<p> 
<?php printf(__( "Please also check <a href='%s'>your profile</a> for some per-user EME settings.", 'eme' ),admin_url('profile.php')); ?>
</p>
<form id="eme_options_form" method="post" action="options.php">
<h3><?php _e ( 'General options', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Use dropdown for locations?' ), 'eme_use_select_for_locations', __ ( 'Select yes to select location from a drop-down menu; location selection will be faster, but you will lose the ability to insert locations with events.','eme' )."<br />".__ ( 'When the qtranslate plugin is installed and activated, this setting will be ignored and always considered \'Yes\'.','eme' ) );
   eme_options_radio_binary ( __ ( 'Use recurrence?' ), 'eme_recurrence_enabled', __ ( 'Select yes to enable the possibility to create recurrent events.','eme' ) ); 
   eme_options_radio_binary ( __ ( 'Use RSVP?' ), 'eme_rsvp_enabled', __ ( 'Select yes to enable the RSVP feature so people can register for an event and book places.','eme' ) );
   eme_options_radio_binary ( __ ( 'Use categories?' ), 'eme_categories_enabled', __ ( 'Select yes to enable the category features.','eme' ) );
   eme_options_radio_binary ( __ ( 'Use attributes?' ), 'eme_attributes_enabled', __ ( 'Select yes to enable the attributes feature.','eme' ) );
   eme_options_radio_binary ( __ ( 'Enable Google Maps integration?' ), 'eme_gmap_is_active', __ ( 'Check this option to enable Google Map integration.','eme' ) );
   eme_options_radio_binary ( __ ( 'Enable map scroll-wheel zooming?' ), 'eme_gmap_zooming', __ ( 'Yes, enables map scroll-wheel zooming. No, enables scroll-wheel page scrolling over maps. (It will be necessary to refresh your web browser on a map page to see the effect of this change.)', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Always include JS in header?' ), 'eme_load_js_in_header', __ ( 'Some themes are badely designed and can have issues showing the google maps or advancing in the calendar. If so, try activating this option which will cause the javascript to always be included in the header of every page (off by default).','eme' ) );
   eme_options_radio_binary ( __ ( 'Use the client computer clock for the calendar', 'eme' ), 'eme_use_client_clock', __ ( 'Check this option if you want to use the clock of the client as base to calculate current day for the calendar.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Delete all EME data when upgrading or deactivating?', 'eme' ), 'eme_uninstall_drop_data', __ ( 'Check this option if you want to delete all EME data (database tables and options) when upgrading or deactivating the plugin.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Enable shortcodes in widgets', 'eme' ), 'eme_shortcodes_in_widgets', __ ( 'Check this option if you want to enable the use of shortcodes in widgets (affects shortcodes of any plugin used in widgets, so use with care).', 'eme' ) );
   eme_options_select (__('State for new event','eme'), 'eme_event_initial_state', eme_status_array(), __ ('Initial state for a new event','eme') );
   ?>
</table>

<h3><?php _e ( 'Permalink options', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Enable event permalinks if possible?','eme' ), 'eme_seo_permalink', __ ( 'If Yes, EME will render SEO permalinks if permalinks are activated.', 'eme' ) . "<br \><strong>" . __ ( 'It is necessary to click \'Save Changes\' on the  WordPress \'Settings/Permalinks\' page before you will see the effect of this change.','eme' )."</strong>");
   eme_options_input_text ( __('Events permalink prefix', 'eme' ), 'eme_permalink_events_prefix', __( 'The permalink prefix used for events and the calendar.','eme') );
   eme_options_input_text ( __('Locations permalink prefix', 'eme' ), 'eme_permalink_locations_prefix', __( 'The permalink prefix used for locations.','eme') );
   ?>
</table>

<h3><?php _e ( 'Access rights', 'eme' ); ?></h3>
<p><?php _e ( 'Tip: Use a plugin like "User Role Editor" to add/edit capabilities and roles.', 'eme' ); ?></p>
<table class="form-table">
   <?php
   eme_options_select (__('Add event','eme'), 'eme_cap_add_event', eme_get_all_caps (), sprintf(__('Permission needed to add a new event. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_ADD_EVENT)) );
   eme_options_select (__('Author event','eme'), 'eme_cap_author_event', eme_get_all_caps (), sprintf(__('Permission needed to edit own events. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_AUTHOR_EVENT)) );
   eme_options_select (__('Publish event','eme'), 'eme_cap_publish_event', eme_get_all_caps (), sprintf(__('Permission needed to make an event public. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_PUBLISH_EVENT)) );
   eme_options_select (__('List events','eme'), 'eme_cap_list_events', eme_get_all_caps (), sprintf(__('Permission needed to just list all events, useful for CSV exports for bookings and such. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_LIST_EVENTS)) . "<br><b>". __('All your event admins need this as well, otherwise the menu will not show.','eme')."</b>" );
   eme_options_select (__('Edit events','eme'), 'eme_cap_edit_events', eme_get_all_caps (), sprintf(__('Permission needed to edit all events. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_EDIT_EVENTS)) );
   eme_options_select (__('Add location','eme'), 'eme_cap_add_locations', eme_get_all_caps (), sprintf(__('Permission needed to add locations. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_ADD_LOCATION)) );
   eme_options_select (__('Author location','eme'), 'eme_cap_author_locations', eme_get_all_caps (), sprintf(__('Permission needed to edit own locations. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_AUTHOR_LOCATION)) );
   eme_options_select (__('Edit location','eme'), 'eme_cap_edit_locations', eme_get_all_caps (), sprintf(__('Permission needed to edit all locations. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_EDIT_LOCATIONS)) );
   eme_options_select (__('Edit categories','eme'), 'eme_cap_categories', eme_get_all_caps (), sprintf(__('Permission needed to edit all categories. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_CATEGORIES)) );
   eme_options_select (__('View people','eme'), 'eme_cap_people', eme_get_all_caps (), sprintf(__('Permission needed to view registered people info. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_PEOPLE)) );
   eme_options_select (__('Approve registrations','eme'), 'eme_cap_approve', eme_get_all_caps (), sprintf(__('Permission needed to approve pending registrations. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_APPROVE)) );
   eme_options_select (__('Edit registrations','eme'), 'eme_cap_registrations', eme_get_all_caps (), sprintf(__('Permission needed to edit approved registrations. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_REGISTRATIONS)) );
   eme_options_select (__('Edit form fields','eme'), 'eme_cap_forms', eme_get_all_caps (), sprintf(__('Permission needed to edit form fields. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_FORMS)) );
   eme_options_select (__('Cleanup','eme'), 'eme_cap_cleanup', eme_get_all_caps (), sprintf(__('Permission needed to execute cleanup actions. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_CLEANUP)) );
   eme_options_select (__('Edit settings','eme'), 'eme_cap_settings', eme_get_all_caps (),sprintf(__('Permission needed to edit settings. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_SETTINGS)) );
   ?>
</table>
<h3><?php _e ( 'Events page', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_select ( __ ( 'Events page' ), 'eme_events_page', eme_get_all_pages (), __ ( 'This option allows you to select which page to use as an events page.', 'eme' )."<br /><strong>".__ ( 'The content of this page (including shortcodes of any kind) will be ignored completely and dynamically replaced by events data.','eme' )."</strong>" );
   eme_options_radio_binary ( __ ( 'Show events page in lists?', 'eme' ), 'eme_list_events_page', __ ( 'Check this option if you want the events page to appear together with other pages in pages lists.', 'eme' )."<br /><strong>".__ ( 'This option should no longer be used, it will be deprecated. Using the [events_list] shortcode in a self created page is recommended.', 'eme' )."</strong>" ); 
   eme_options_radio_binary ( __ ( 'Display calendar in events page?', 'eme' ), 'eme_display_calendar_in_events_page', __ ( 'This option allows to display the calendar in the events page, instead of the default list. It is recommended not to display both the calendar widget and a calendar page.','eme' ) );
   eme_options_input_text ( __('Number of events to show per page in admin interface', 'eme' ), 'eme_events_admin_limit', __( 'Indicates the number of events shown on one page in the admin interface (min. 5, max. 200)','eme') );
   eme_options_input_text ( __('Number of events to show in lists', 'eme' ), 'eme_event_list_number_items', __( 'The number of events to show in a list if no specific limit is specified (used in the shortcode events_list, RSS feed, the placeholders #_NEXTEVENTS and #_PASTEVENTS, ...). Use 0 for no limit.','eme') );
   ?>
</table>
<h3><?php _e ( 'Events format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Remove leading zeros from minutes?', 'eme' ), 'eme_time_remove_leading_zeros', __ ( 'PHP date/time functions have no notation to show minutes without leading zeros. Checking this option will return e.g. 9 for 09 and empty for 00.', 'eme' ) ); 
   eme_options_textarea ( __ ( 'Default event list format header', 'eme' ), 'eme_event_list_item_format_header', __( 'This content will appear just above your code for the default event list format. If you leave this empty, the value <code>&lt;ul class=\'eme_events_list\'&gt;</code> will be used.', 'eme' ) );
   eme_options_textarea ( __ ( 'Default event list format', 'eme' ), 'eme_event_list_item_format', __ ( 'The format of any events in a list.<br/>Insert one or more of the following placeholders: <code>#_EVENTNAME</code>, <code>#_LOCATIONNAME</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_NOTES</code>.<br/> Use <code>#_EXCERPT</code> to show <code>#_NOTES</code> until you place a <code>&lt;!&ndash;&ndash;more&ndash;&ndash;&gt;</code> marker.<br/> Use <code>#_LINKEDNAME</code> for the event name with a link to the given event page.<br/> Use <code>#_EVENTPAGEURL</code> to print the event page URL and make your own customised links.<br/> Use <code>#_LOCATIONPAGEURL</code> to print the location page URL and make your own customised links.<br/>Use <code>#_EDITEVENTLINK</code> to add a link to edit page for the event, which will appear only when a user is logged in.<br/>To insert date and time values, use <a href="http://www.php.net/manual/en/function.date.php">PHP time format characters</a>  with a <code>#</code> symbol before them, i.e. <code>#m</code>, <code>#M</code>, <code>#j</code>, etc.<br/> For the end time, put <code>#@</code> in front of the character, e.g. <code>#@h</code>, <code>#@i</code>, etc.<br/> You can also create a date format without prepending <code>#</code> by wrapping it in #_{} or #@_{} (e.g. <code>#_{d/m/Y}</code>). If there is no end date, the value is not shown.<br/>Use <code>#_12HSTARTTIME</code> and <code>#_12HENDTIME</code> for AM/PM start-time/end-time notation, idem <code>#_24HSTARTTIME</code> and <code>#_24HENDTIME</code>.<br/>Feel free to use HTML tags as <code>li</code>, <code>br</code> and so on.<br/>For custom attributes, you use <code>#_ATT{key}{alternative text}</code>, the second braces are optional and will appear if the attribute is not defined or left blank for that event. This key will appear as an option when adding attributes to your event.', 'eme' )."<br />".__('Use <code>#_PAST_FUTURE_CLASS</code> to return a class name indicating this event is future or past (<code>eme-future-event</code> or <code>eme-past-event</code>), use the returned value in e.g. the li-statement for each event in the list of events','eme') .'<br />'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Default event list format footer', 'eme' ), 'eme_event_list_item_format_footer', __ ( 'This content will appear just below your code for the default event list format. If you leave this empty, the value <code>&lt;/ul&gt;</code> will be used.', 'eme' ) );

   eme_options_input_text ( __ ( 'Single event page title format', 'eme' ), 'eme_event_page_title_format', __ ( 'The format of a single event page title. Follow the previous formatting instructions.', 'eme' ) );
   eme_options_input_text ( __ ( 'Single event html title format', 'eme' ), 'eme_event_html_title_format', __ ( 'The format of a single event html page title. Follow the previous formatting instructions.', 'eme' ). __( ' The default is: ','eme'). DEFAULT_EVENT_HTML_TITLE_FORMAT);
   eme_options_textarea ( __ ( 'Default single event format', 'eme' ), 'eme_single_event_format', __ ( 'The format of a single event page.<br/>Follow the previous formatting instructions. <br/>Use <code>#_MAP</code> to insert a map.<br/>Use <code>#_CONTACTNAME</code>, <code>#_CONTACTEMAIL</code>, <code>#_CONTACTPHONE</code> to insert respectively the name, e-mail address and phone number of the designated contact person. <br/>Use <code>#_ADDBOOKINGFORM</code> to insert a form to allow the user to respond to your events reserving one or more places (RSVP).<br/> Use <code>#_REMOVEBOOKINGFORM</code> to insert a form where users, inserting their name and e-mail address, can remove their bookings.', 'eme' ).__('<br/>Use <code>#_ADDBOOKINGFORM_IF_NOT_REGISTERED</code> to insert the booking form only if the user has not registered yet. Similar use <code>#_REMOVEBOOKINGFORM_IF_REGISTERED</code> to insert the booking removal form only if the user has already registered before. These two codes only work for WP users.','eme').__('<br/> Use <code>#_DIRECTIONS</code> to insert a form so people can ask directions to the event.','eme').__('<br/> Use <code>#_CATEGORIES</code> to insert a comma-seperated list of categories an event is in.','eme').__('<br/> Use <code>#_ATTENDEES</code> to get a list of the names attending the event.','eme') .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'eme').'</a>' );
   eme_options_input_text ( __ ( 'Monthly period date format', 'eme' ), 'eme_show_period_monthly_dateformat', __ ( 'The format of the date-string used when you use showperiod=monthly as an option to &#91;the events_list] shortcode, also used for monthly pagination. Use php date() compatible settings.', 'eme') . __( ' The default is: ','eme'). DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT );
   eme_options_input_text ( __ ( 'Yearly period date format', 'eme' ), 'eme_show_period_yearly_dateformat', __ ( 'The format of the date-string used when you use showperiod=yearly as an option to &#91;the events_list] shortcode, also used for yearly pagination. Use php date() compatible settings.', 'eme') . __( ' The default is: ','eme'). DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT );
   eme_options_input_text ( __ ( 'Events page title', 'eme' ), 'eme_events_page_title', __ ( 'The title on the multiple events page.', 'eme' ) );
   eme_options_input_text ( __ ( 'No events message', 'eme' ), 'eme_no_events_message', __ ( 'The message displayed when no events are available.', 'eme' ) );
   ?>
</table>
<h3><?php _e ( 'Events filtering format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Default event list filtering format', 'eme' ), 'eme_filter_form_format', __ ( 'This defines the layout of the event list filtering form when using the shortcode <code>[events_filterform]</code>. Use <code>#_FILTER_CATS</code>, <code>#_FILTER_LOCS</code>, <code>#_FILTER_TOWNS</code>, <code>#_FILTER_WEEKS</code>, <code>#_FILTER_MONTHS</code>.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=28'>".__('the documentation', 'eme').'</a>' );
   ?>
</table>
<h3><?php _e ( 'Calendar format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'Small calendar title', 'eme' ), 'eme_small_calendar_event_title_format', __ ( 'The format of the title, corresponding to the text that appears when hovering on an eventful calendar day.', 'eme' ) );
   eme_options_input_text ( __ ( 'Small calendar title separator', 'eme' ), 'eme_small_calendar_event_title_separator', __ ( 'The separator appearing on the above title when more than one event is taking place on the same day.', 'eme' ) );
   eme_options_input_text ( __ ( 'Full calendar events format', 'eme' ), 'eme_full_calendar_event_format', __ ( 'The format of each event when displayed in the full calendar. Remember to include <code>li</code> tags before and after the event.', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'Locations format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Default location list format header', 'eme' ), 'eme_location_list_format_header', __( 'This content will appear just above your code for the default location list format. If you leave this empty, the value <code>&lt;ul class=\'eme_locations_list\'&gt;</code> will be used.<br/>Used by the shortcode <code>[events_locations]</code>', 'eme' ) );
   eme_options_textarea ( __ ( 'Default location list item format', 'eme' ), 'eme_location_list_format_item', __ ( 'The format of a location in a location list. If you leave this empty, the value <code>&lt;li class=\"location-#_LOCATIONID\"&gt;#_LOCATIONNAME&lt;/li&gt;</code> will be used.<br/>See the documentation for a list of available placeholders for locations.<br/>Used by the shortcode <code>[events_locations]</code>', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=26'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Default location list format footer', 'eme' ), 'eme_location_list_format_footer', __ ( 'This content will appear just below your code for the default location list format. If you leave this empty, the value <code>&lt;/ul&gt;</code> will be used.<br/>Used by the shortcode <code>[events_locations]</code>', 'eme' ) );

   eme_options_input_text ( __ ( 'Single location page title format', 'eme' ), 'eme_location_page_title_format', __ ( 'The format of a single location page title.<br/>Follow the previous formatting instructions.', 'eme' ) );
   eme_options_input_text ( __ ( 'Single location html title format', 'eme' ), 'eme_location_html_title_format', __ ( 'The format of a single location html page title.<br/>Follow the previous formatting instructions.', 'eme' ). __( ' The default is: ','eme'). DEFAULT_LOCATION_HTML_TITLE_FORMAT);
   eme_options_textarea ( __ ( 'Default single location page format', 'eme' ), 'eme_single_location_format', __ ( 'The format of a single location page.<br/>Insert one or more of the following placeholders: <code>#_LOCATIONNAME</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_DESCRIPTION</code>.<br/> Use <code>#_MAP</code> to display a map of the event location, and <code>#_IMAGE</code> to display an image of the location.<br/> Use <code>#_NEXTEVENTS</code> to insert a list of the upcoming events, <code>#_PASTEVENTS</code> for a list of past events, <code>#_ALLEVENTS</code> for a list of all events taking place in this location.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=26'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Default location balloon format', 'eme' ), 'eme_location_baloon_format', __ ( 'The format of the text appearing in the balloon describing the location in the map.<br/>Insert one or more of the following placeholders: <code>#_LOCATIONNAME</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_DESCRIPTION</code>,<code>#_IMAGE</code>, <code>#_LOCATIONPAGEURL</code> or <code>#_DIRECTIONS</code>.', 'eme' ) );
   eme_options_textarea ( __ ( 'Default location event list format', 'eme' ), 'eme_location_event_list_item_format', __ ( 'The format of the events list inserted in the location page through the <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> element. <br/> Follow the events formatting instructions', 'eme' ) );
   eme_options_textarea ( __ ( 'Default no events message', 'eme' ), 'eme_location_no_events_message', __ ( 'The message to be displayed in the list generated by <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> when no events are available.', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSS feed format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'RSS main title', 'eme' ), 'eme_rss_main_title', __ ( 'The main title of your RSS events feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS main description', 'eme' ), 'eme_rss_main_description', __ ( 'The main description of your RSS events feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS title format', 'eme' ), 'eme_rss_title_format', __ ( 'The format of the title of each item in the events RSS feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS description format', 'eme' ), 'eme_rss_description_format', __ ( 'The format of the description of each item in the events RSS feed. Follow the previous formatting instructions.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'RSS Pubdate usage', 'eme' ), 'eme_rss_show_pubdate', __ ( 'Show the event creation/modification date as PubDate info in the in the events RSS feed.', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSVP: registrations and bookings', 'eme' ); ?></h3>
<table class='form-table'>
     <?php
   $indexed_users[-1]=__('Event author','eme');
   $indexed_users+=eme_get_indexed_users();
   eme_options_select ( __ ( 'Default contact person', 'eme' ), 'eme_default_contact_person', $indexed_users, __ ( 'Select the default contact person. This user will be employed whenever a contact person is not explicitly specified for an event', 'eme' ) );
   eme_options_radio_binary ( __ ( 'By default require WP membership to be able to register?', 'eme' ), 'eme_rsvp_registered_users_only', __ ( 'Check this option if you want by default that only WP registered users can book for an event.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'By default enable registrations for new events?', 'eme' ), 'eme_rsvp_reg_for_new_events', __ ( 'Check this option if you want to enable registrations by default for new events.', 'eme' ) );
   eme_options_input_text ( __ ( 'Default number of spaces', 'eme' ), 'eme_rsvp_default_number_spaces', __ ( 'The default number of spaces an event has.', 'eme' ) );
   eme_options_input_text ( __ ( 'Min number of spaces to book', 'eme' ), 'eme_rsvp_addbooking_min_spaces', __ ( 'The minimum number of spaces a person can book in one go (it can be 0, for e.g. just an attendee list).', 'eme' ) );
   eme_options_input_text ( __ ( 'Max number of spaces to book', 'eme' ), 'eme_rsvp_addbooking_max_spaces', __ ( 'The maximum number of spaces a person can book in one go.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Use captcha for booking form?', 'eme' ), 'eme_captcha_for_booking', __ ( 'Check this option if you want to use a captcha on the booking form, to thwart spammers a bit.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Hide fully booked events?', 'eme' ), 'eme_rsvp_hide_full_events', __ ( 'Check this option if you want to hide events that are fully booked from the calendar and events listing in the front.', 'eme' ) );
   eme_options_input_text ( __ ( 'Add booking form submit text', 'eme' ), 'eme_rsvp_addbooking_submit_string', __ ( "The string of the submit button on the add booking form", 'eme' ) );
   eme_options_input_text ( __ ( 'Delete booking form submit text', 'eme' ), 'eme_rsvp_delbooking_submit_string', __ ( "The string of the submit button on the delete booking form", 'eme' ) );
   eme_options_input_text ( __ ( 'Attendees list format', 'eme' ), 'eme_attendees_list_format', __ ( "The format for the attendees list when using the <code>#_ATTENDEES</code> placeholder. Use <code>#_NAME</code>, <code>#_EMAIL</code>, <code>#_PHONE</code>, <code>#_ID</code>.", 'eme' ). __("Use <code>#_USER_RESERVEDSPACES</code> to show the number of seats for that person.", 'eme' ) );
   eme_options_input_text ( __ ( 'Bookings list header format', 'eme' ), 'eme_bookings_list_header_format', __ ( "The header format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'eme' ). sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_BOOKINGS_LIST_HEADER_FORMAT)));
   eme_options_input_text ( __ ( 'Bookings list format', 'eme' ), 'eme_bookings_list_format', __ ( "The format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'eme' ). __('For all placeholders you can use here, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=45'>".__('the documentation', 'eme').'</a>' .__('For more information about form fields, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'eme').'</a>' );
   eme_options_input_text ( __ ( 'Bookings list footer format', 'eme' ), 'eme_bookings_list_footer_format', __ ( "The footer format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'eme' ). sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_BOOKINGS_LIST_FOOTER_FORMAT)));
   eme_options_input_text ( __ ( 'Booking recorded message', 'eme' ), 'eme_registration_recorded_ok_html', __ ( "The text (html allowed) shown to the user when the booking has been made successfully.", 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSVP: mail options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_radio_binary ( __ ( 'Enable the RSVP e-mail notifications?', 'eme' ), 'eme_rsvp_mail_notify_is_active', __ ( 'Check this option if you want to receive an email when someone books places for your events.', 'eme' ) );
   eme_options_textarea ( __ ( 'Contact person email format', 'eme' ), 'eme_contactperson_email_body', __ ( 'The format of the email which will be sent to the contact person. Follow the events formatting instructions. <br/>Use <code>#_RESPNAME</code>, <code>#_RESPEMAIL</code> and <code>#_RESPPHONE</code> to display respectively the name, e-mail, address and phone of the respondent.<br/>Use <code>#_SPACES</code> to display the number of spaces reserved by the respondent. Use <code>#_COMMENT</code> to display the respondent\'s comment. <br/> Use <code>#_RESERVEDSPACES</code> and <code>#_AVAILABLESPACES</code> to display respectively the number of booked and available seats.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Contact person cancelled email format', 'eme' ), 'eme_contactperson_cancelled_email_body', __ ( 'The format of the email which will be sent to the contact person for a cancellation. Follow the events formatting instructions. <br/>Use <code>#_RESPNAME</code>, <code>#_RESPEMAIL</code> and <code>#_RESPPHONE</code> to display respectively the name, e-mail, address and phone of the respondent.<br/>Use <code>#_SPACES</code> to display the number of spaces reserved by the respondent. Use <code>#_COMMENT</code> to display the respondent\'s comment. <br/> Use <code>#_RESERVEDSPACES</code> and <code>#_AVAILABLESPACES</code> to display respectively the number of booked and available seats.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Contact person pending email format', 'eme' ), 'eme_contactperson_pending_email_body', __ ( 'The format of the email which will be sent to the contact person if approval is needed. Follow the events formatting instructions. <br/>Use <code>#_RESPNAME</code>, <code>#_RESPEMAIL</code> and <code>#_RESPPHONE</code> to display respectively the name, e-mail, address and phone of the respondent.<br/>Use <code>#_SPACES</code> to display the number of spaces reserved by the respondent. Use <code>#_COMMENT</code> to display the respondent\'s comment. <br/> Use <code>#_RESERVEDSPACES</code> and <code>#_AVAILABLESPACES</code> to display respectively the number of booked and available seats.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Respondent email format', 'eme' ), 'eme_respondent_email_body', __ ( 'The format of the email which will be sent to the respondent. Follow the events formatting instructions. <br/>Use <code>#_RESPNAME</code> to display the name of the respondent.<br/>Use <code>#_CONTACTNAME</code> and <code>#_PLAIN_CONTACTEMAIL</code> to display respectively the name and e-mail of the contact person.<br/>Use <code>#_SPACES</code> to display the number of spaces reserved by the respondent. Use <code>#_COMMENT</code> to display the respondent\'s comment.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Registration pending email format', 'eme' ), 'eme_registration_pending_email_body', __ ( 'The format of the email which will be sent to the respondent when the event requires registration approval.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Registration cancelled email format', 'eme' ), 'eme_registration_cancelled_email_body', __ ( 'The format of the email which will be sent to the respondent when the respondent cancels the registrations for an event.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Registration denied email format', 'eme' ), 'eme_registration_denied_email_body', __ ( 'The format of the email which will be sent to the respondent when the admin denies the registration request if the event requires registration approval.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_input_text ( __ ( 'Notification sender name', 'eme' ), 'eme_mail_sender_name', __ ( "Insert the display name of the notification sender.", 'eme' ) );
   eme_options_input_text ( __ ( 'Notification sender address', 'eme' ), 'eme_mail_sender_address', __ ( "Insert the address of the notification sender. It must correspond with your Gmail account user", 'eme' ) );
   eme_options_select ( __ ( 'Mail sending method', 'eme' ), 'eme_rsvp_mail_send_method', array ('smtp' => 'SMTP', 'mail' => __ ( 'PHP mail function', 'eme' ), 'sendmail' => 'Sendmail', 'qmail' => 'Qmail' ), __ ( 'Select the method to send email notification.', 'eme' ) );
   eme_options_input_text ( 'SMTP host', 'eme_smtp_host', __ ( "The SMTP host. Usually it corresponds to 'localhost'. If you use Gmail, set this value to 'ssl://smtp.gmail.com:465'.", 'eme' ) );
   eme_options_input_text ( 'Mail sending port', 'eme_rsvp_mail_port', __ ( "The port through which you e-mail notifications will be sent. Make sure the firewall doesn't block this port", 'eme' ) );
   eme_options_radio_binary ( __ ( 'Use SMTP authentication?', 'eme' ), 'eme_rsvp_mail_SMTPAuth', __ ( 'SMTP authentication is often needed. If you use Gmail, make sure to set this parameter to Yes', 'eme' ) );
   eme_options_input_text ( __ ( 'SMTP username', 'eme' ), 'eme_smtp_username', __ ( "Insert the username to be used to access your SMTP server.", 'eme' ) );
   eme_options_input_password ( __ ( 'SMTP password', 'eme' ), "eme_smtp_password", __ ( "Insert the password to be used to access your SMTP server", 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSVP: paypal options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      eme_options_select ( __('PayPal live or test','eme'), 'eme_paypal_url', array ('https://www.sandbox.paypal.com/cgi-bin/webscr' => __('Paypal Sandbox (for testing)','eme'), 'https://www.paypal.com/cgi-bin/webscr' => __ ( 'Paypal Live', 'eme' )), __('Choose wether you want to test paypal in a paypal sandbox or go live and really use paypal.','eme') );
     # eme_options_input_text (__('PayPal url','eme'),'eme_paypal_url', sprintf(__("The URL for paypal payments. The default is: %s",'eme'),DEFAULT_PAYPAL_URL));
      eme_options_input_text (__('PayPal business info','eme'),'eme_paypal_business', __("Paypal business ID or email.",'eme'));
   ?>
</table>

<h3><?php _e ( 'RSVP: form format', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      eme_options_textarea (__('Form format','eme'),'eme_registration_form_format', __("The look and feel of the form for registrations. #_NAME, #_EMAIL and #_SEATS are obligated fields, if not present then the form will not be shown. Use #_FIELD1, #_FIELD2, ... for own defined fields.",'eme')  .'<br/>'.__('For more information about form fields, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'eme').'</a>');
   ?>
</table>

<h3><?php _e ( 'Images size', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_input_text ( __ ( 'Maximum width (px)', 'eme' ), 'eme_image_max_width', __ ( 'The maximum allowed width for images uploaded, in pixels', 'eme' ) );
   eme_options_input_text ( __ ( 'Maximum height (px)', 'eme' ), 'eme_image_max_height', __ ( "The maximum allowed height for images uploaded, in pixels", 'eme' ) );
   eme_options_input_text ( __ ( 'Maximum size (bytes)', 'eme' ), 'eme_image_max_size', __ ( "The maximum allowed size for images uploaded, in bytes", 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'Extra html headers', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Extra event html headers', 'eme' ), 'eme_event_html_headers_format', __ ( 'Here you can define extra html headers when viewing a single event, typically used to add meta tags for facebook or SEO. All event placeholders can be used, but will be stripped from resulting html.', 'eme' ) );
   eme_options_textarea ( __ ( 'Extra location html headers', 'eme' ), 'eme_location_html_headers_format', __ ( 'Here you can define extra html headers when viewing a single location, typically used to add meta tags for facebook or SEO. All location placeholders can be used, but will be stripped from resulting html.', 'eme' ) );
   ?>
</table>

<p class="submit"><input type="submit" id="eme_options_submit" name="Submit" value="<?php _e ( 'Save Changes' )?>" /></p>
   <?php
   settings_fields ( 'eme-options' );
   ?> 
</form>
</div>
<?php

}

//This is the content of the event page
function eme_events_page_content() {
   global $wpdb,$wp_query;
   if (isset ( $wp_query->query_vars ['location_id'] ) && $wp_query->query_vars ['location_id'] != '') {
      $location = eme_get_location ( intval($wp_query->query_vars ['location_id']));
      $single_location_format = get_option('eme_single_location_format' );
      $page_body = eme_replace_locations_placeholders ( $single_location_format, $location );
      return $page_body;
   }
   //if (isset ( $_REQUEST ['event_id'] ) && $_REQUEST ['event_id'] != '') {
   if (eme_is_single_event_page()) {
      // single event page
      $event_ID = intval($wp_query->query_vars ['event_id']);
      $event = eme_get_event ( $event_ID );
      $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
      //$page_body = eme_replace_placeholders ( $single_event_format, $event, 'stop' );
      if (count($event) > 0 && ($event['event_status'] == STATUS_PRIVATE && is_user_logged_in() || $event['event_status'] != STATUS_PRIVATE))
         $page_body = eme_replace_placeholders ( $single_event_format, $event );
      return $page_body;
   } elseif (isset ( $wp_query->query_vars ['calendar_day'] ) && $wp_query->query_vars ['calendar_day'] != '') {
      $scope = eme_sanitize_request($wp_query->query_vars ['calendar_day']);
      $events_N = eme_events_count_for ( $scope );
      if ($events_N > 1) {
         $event_list_item_format = get_option('eme_event_list_item_format' );
         //Add headers and footers to the events list
         $event_list_format_header = get_option('eme_event_list_item_format_header' );
         $event_list_format_header = ( $event_list_format_header != '' ) ? $event_list_format_header : "<ul class='eme_events_list'>";
         $event_list_format_footer = get_option('eme_event_list_item_format_footer' );
         $event_list_format_footer = ( $event_list_format_footer != '' ) ? $event_list_format_footer : "</ul>";
         $page_body = $event_list_format_header .  eme_get_events_list ( 0, $scope, "ASC", $event_list_item_format, 0 ) . $event_list_format_footer;
      } else {
         # there's only one event for that day, so we show that event, but only if the event doesn't point to an external url
         $events = eme_get_events ( 0, $scope);
         $event = $events [0];
         if ($event['event_url'] != '') {
            $event_list_item_format = get_option('eme_event_list_item_format' );
            //Add headers and footers to the events list
            $event_list_format_header = get_option('eme_event_list_item_format_header' );
            $event_list_format_header = ( $event_list_format_header != '' ) ? $event_list_format_header : "<ul class='eme_events_list'>";
            $event_list_format_footer = get_option('eme_event_list_item_format_footer' );
            $event_list_format_footer = ( $event_list_format_footer != '' ) ? $event_list_format_footer : "</ul>";
            $page_body = $event_list_format_header .  eme_get_events_list ( 0, $scope, "ASC", $event_list_item_format, 0 ) . $event_list_format_footer;
         } else {
            $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
            $page_body = eme_replace_placeholders ( $single_event_format, $event );
         }
      }
      return $page_body;
   } else {
      // Multiple events page
      (isset($_GET ['scope'])) ? $scope = eme_sanitize_request($_GET ['scope']) : $scope = "future";
      $stored_format = get_option('eme_event_list_item_format' );
      if (get_option('eme_display_calendar_in_events_page' )){
         $page_body = eme_get_calendar ('full=1');
      }else{
         $event_list_format_header = get_option('eme_event_list_item_format_header' );
         $event_list_format_header = ( $event_list_format_header != '' ) ? $event_list_format_header : "<ul class='eme_events_list'>";
         $event_list_format_footer = get_option('eme_event_list_item_format_footer' );
         $event_list_format_footer = ( $event_list_format_footer != '' ) ? $event_list_format_footer : "</ul>";
         $page_body = $event_list_format_header . eme_get_events_list ( get_option('eme_event_list_number_items' ), $scope, "ASC", $stored_format, 0 ) . $event_list_format_footer;
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
         $conditions [] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $conditions [] = "event_status=".STATUS_PUBLIC;
      }
   }
   $conditions [] = "((event_start_date  like '$date') OR (event_start_date <= '$date' AND event_end_date >= '$date'))";
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
   if (count($wp_current_filter)>1 && end($wp_current_filter)=='the_content' && $eme_count_arr['the_content']>1) {
      $eme_event_parsed=1;
   } else {
      $eme_event_parsed=0;
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
   global $wp_query;
   $events_page_id = get_option('eme_events_page' );
   $events_page = get_page ( $events_page_id );
   $events_page_title = $events_page->post_title;

   // make sure we only replace the title for the events page, not anything
   // from the menu (which is also in the loop ...)
   if (($data == $events_page_title) && in_the_loop() && eme_is_events_page()) {
      if (isset ( $wp_query->query_vars['calendar_day'] ) && $wp_query->query_vars['calendar_day'] != '') {
         
         $date = eme_sanitize_request($wp_query->query_vars['calendar_day']);
         $events_N = eme_events_count_for ( $date );
         
         if ($events_N == 1) {
            $events = eme_get_events ( 0, eme_sanitize_request($wp_query->query_vars['calendar_day']));
            $event = $events [0];
            $stored_page_title_format = ( $event['event_page_title_format'] != '' ) ? $event['event_page_title_format'] : get_option('eme_event_page_title_format' );
            $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
            return $page_title;
         }
      }
      
      if (eme_is_single_event_page()) {
         // single event page
         $event_ID = intval($wp_query->query_vars['event_id']);
         $event = eme_get_event ( $event_ID );
         if (isset( $event['event_page_title_format']) && ( $event['event_page_title_format'] != '' )) {
            $stored_page_title_format = $event['event_page_title_format'];
         } else {
            $stored_page_title_format = get_option('eme_event_page_title_format' );
         }
         $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
         return $page_title;
      } elseif (eme_is_single_location_page()) {
         $location = eme_get_location ( intval($wp_query->query_vars['location_id']));
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
   global $wp_query;

   //$events_page_id = get_option('eme_events_page' );
   if (eme_is_events_page()) {
      if (isset ( $wp_query->query_vars['calendar_day'] ) && $wp_query->query_vars['calendar_day'] != '') {
         
         $date = eme_sanitize_request($wp_query->query_vars['calendar_day']);
         $events_N = eme_events_count_for ( $date );
         
         if ($events_N == 1) {
            $events = eme_get_events ( 0, eme_sanitize_request($wp_query->query_vars['calendar_day']));
            $event = $events [0];
            $stored_html_title_format = get_option('eme_event_html_title_format' );
            $html_title = eme_strip_tags(eme_replace_placeholders ( $stored_html_title_format, $event ));
            return $html_title;
         }
      }
      if (eme_is_single_event_page()) {
         // single event page
         $event_ID = intval($wp_query->query_vars['event_id']);
         $event = eme_get_event ( $event_ID );
         $stored_html_title_format = get_option('eme_event_html_title_format' );
         $html_title = eme_strip_tags(eme_replace_placeholders ( $stored_html_title_format, $event ));
         return $html_title;
      } elseif (eme_is_single_location_page()) {
         $location = eme_get_location ( intval($wp_query->query_vars['location_id']));
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
   global $wp_query;
# We need to catch the request as early as possible, but
# since it needs to be working for both permalinks and normal,
# I can't use just any action hook. parse_query seems to do just fine
   if (isset ( $wp_query->query_vars ['event_id'])) {
      $event_ID = intval($wp_query->query_vars ['event_id']);
      if (!eme_check_exists($event_ID)) {
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
//add_action( 'parse_query', 'eme_redir_nonexisting' );
add_action( 'template_redirect', 'eme_template_redir' );

// filter out the events page in the get_pages call
function eme_filter_get_pages($data) {
   $output = array ();
   $events_page_id = get_option('eme_events_page' );
   for($i = 0; $i < count ( $data ); ++ $i) {
      if(isset($data [$i])) {
         if ($data [$i]->ID == $events_page_id) {
            $list_events_page = get_option('eme_list_events_page' );
            if ($list_events_page) {
               $output [] = $data [$i];
            }
         } else {
            $output [] = $data [$i];
         }
      }
   }
   return $output;
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
// it works just fine, but then people can't disable comments on this page
// TODO: until I figure this out, we put this in comment
// add_action( 'pre_get_posts' ,'exclude_this_page' );

// TEMPLATE TAGS

// exposed function, for theme  makers
   //Added a category option to the get events list method and shortcode
function eme_get_events_list($limit, $scope = "future", $order = "ASC", $format = '', $echo = 1, $category = '',$showperiod = '', $long_events = 0, $author = '', $contact_person='', $paging=0, $location_id = "", $user_registered_only = 0, $show_ongoing=1, $link_showperiod=0, $notcategory = '') {
   global $post;
   if ($limit === "") {
      $limit = get_option('eme_event_list_number_items' );
   }
   if (strpos ( $limit, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $eme_event_list_number_events=get_option('eme_event_list_number_items' );
      $defaults = array ('limit' => $eme_event_list_number_events, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'echo' => 1 , 'category' => '', 'showperiod' => '', $author => '', $contact_person => '', 'paging'=>0, 'long_events' => 0, 'location_id' => 0, 'show_ongoing' => 1, 'link_showperiod' => 0, 'notcategory' => '');
      $r = wp_parse_args ( $limit, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
      // for AND categories: the user enters "+" and this gets translated to " " by wp_parse_args
      // so we fix it again
      $category = preg_replace("/ /","+",$category);
   }
   if ($scope == "")
      $scope = "future";
   if ($order != "DESC")
      $order = "ASC";
   if ($format == '') {
      $orig_format = true;
      $format = get_option('eme_event_list_item_format' );
   } else {
      $orig_format = false;
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
      $events = eme_get_events ( 0, $scope, $order, $offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $extra_conditions );
   } else {
      $events = eme_get_events ( $limit+1, $scope, $order, $offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $extra_conditions );
   }
   $events_count=count($events);

   // get the paging output ready
   $pagination_top = "<div id='events-pagination-top'> ";
   if ($paging==1 && $limit>0) {
      // for normal paging and there're no events, we go back to offset=0 and try again
      if ($events_count==0) {
         $offset=0;
         $events = eme_get_events ( $limit+1, $scope, $order, $offset, $location_id, $category, $author, $contact_person, $show_ongoing, $notcategory, $extra_conditions );
         $events_count=count($events);
      }
      $prev_text=__('Previous page','eme');
      $next_text=__('Next page','eme');
      $page_number = floor($offset/$limit) + 1;
      $this_page_url=get_permalink($post->ID);
      //$this_page_url=$_SERVER['REQUEST_URI'];
      // remove the offset info
      $this_page_url= preg_replace("/\&eme_offset=-?\d+/","",$this_page_url);
      $this_page_url= preg_replace("/\?eme_offset=-?\d+$/","",$this_page_url);
      $this_page_url= preg_replace("/\?eme_offset=-?\d+\&(.*)/","?$1",$this_page_url);
      if (stristr($this_page_url, "?"))
         $joiner = "&amp;";
      else
         $joiner = "?";

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
            $this_page_url.=$joiner.rawurlencode($key)."=".rawurlencode($item);
            $joiner = "&amp;";
         }
      }

      $left_nav_hidden_class="";
      $right_nav_hidden_class="";
      if ($events_count > $limit) {
         $forward = $offset + $limit;
         $backward = $offset - $limit;
         if ($backward < 0)
            $left_nav_hidden_class="style='visibility:hidden;'";
         $pagination_top.= "<a class='eme_nav_left' $left_nav_hidden_class href='" . $this_page_url.$joiner."eme_offset=$backward'>&lt;&lt; $prev_text</a>";
         $pagination_top.= "<a class='eme_nav_right' $right_nav_hidden_class href='" . $this_page_url.$joiner."eme_offset=$forward'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>".__('Page ','eme').$page_number."</span>";
      }
      if ($events_count <= $limit && $offset>0) {
         $forward = 0;
         $backward = $offset - $limit;
         if ($backward < 0)
            $left_nav_hidden_class="style='visibility:hidden;'";
         $right_nav_hidden_class="style='visibility:hidden;'";
         $pagination_top.= "<a class='eme_nav_left' $left_nav_hidden_class href='" . $this_page_url.$joiner."eme_offset=$backward'>&lt;&lt; $prev_text</a>";
         $pagination_top.= "<a class='eme_nav_right' $right_nav_hidden_class href='" . $this_page_url.$joiner."eme_offset=$forward'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>".__('Page ','eme').$page_number."</span>";
      }
   }
   if ($paging==1 && $limit==0) {
      $this_page_url=$_SERVER['REQUEST_URI'];
      // remove the offset info
      $this_page_url= preg_replace("/\&eme_offset=-?\d+/","",$this_page_url);
      $this_page_url= preg_replace("/\?eme_offset=-?\d+$/","",$this_page_url);
      $this_page_url= preg_replace("/\?eme_offset=-?\d+\&(.*)/","?$1",$this_page_url);
      if (stristr($this_page_url, "?"))
         $joiner = "&amp;";
      else
         $joiner = "?";
      if ($prev_text != "")
         $pagination_top.= "<a class='eme_nav_left' href='" . $this_page_url.$joiner."eme_offset=$prev_offset'>&lt;&lt; $prev_text</a>";
      if ($next_text != "")
         $pagination_top.= "<a class='eme_nav_right' href='" . $this_page_url.$joiner."eme_offset=$next_offset'>$next_text &gt;&gt;</a>";
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
      if( $orig_format ){
         $eme_event_list_item_format_header = get_option('eme_event_list_item_format_header' );
         $eme_event_list_item_format_header = ( $eme_event_list_item_format_header != '' ) ? $eme_event_list_item_format_header : "<ul class='eme_events_list'>";
         $eme_event_list_item_format_footer = get_option('eme_event_list_item_format_footer' );
         $eme_event_list_item_format_footer = ( $eme_event_list_item_format_footer != '' ) ? $eme_event_list_item_format_footer : "</ul>";
         $output =  $eme_event_list_item_format_header .  $output . $eme_event_list_item_format_footer;
      }
   } else {
      $output = get_option('eme_no_events_message' );
   }

   // now add the pagination if needed
   if ($paging==1)
   	$output = $pagination_top . $output . $pagination_bottom;
  
   // now see how to return the output
   if ($echo)
      echo $output;
   else
      return $output;
}

function eme_get_events_list_shortcode($atts) {
   $eme_event_list_number_events=get_option('eme_event_list_number_items' );
   extract ( shortcode_atts ( array ('limit' => $eme_event_list_number_events, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'category' => '', 'showperiod' => '', 'author' => '', 'contact_person' => '', 'paging' => 0, 'long_events' => 0, 'location_id' => 0, 'user_registered_only' => 0, 'show_ongoing' => 1, 'link_showperiod' => 0, 'notcategory' => '' ), $atts ) );

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

   // for format: sometimes people want to give placeholders as options, but when using the shortcode inside
   // another (e.g. when putting [events_list format="#_EVENTNAME"] inside the "display single event" setting,
   // the replacement of the placeholders happens too soon (placeholders get replaced first, before any other
   // shortcode is interpreted). So we add the option that people can use "#OTHER_", and we replace this with
   // "#_" here
   $format = preg_replace('/#OTHER/', "#", $format);
   $result = eme_get_events_list ( $limit,$scope,$order,$format,0,$category,$showperiod,$long_events,$author,$contact_person,$paging,$location_id,$user_registered_only,$show_ongoing,$link_showperiod,$notcategory );
   return $result;
}
add_shortcode ( 'events_list', 'eme_get_events_list_shortcode' );

function eme_display_single_event($event_id) {
   $event = eme_get_event ( intval($event_id) );
   $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
   $page_body = eme_replace_placeholders ($single_event_format, $event);
   return $page_body;
}

function eme_display_single_event_shortcode($atts) {
   extract ( shortcode_atts ( array ('id'=>''), $atts ) );
   return eme_display_single_event($id);
}
add_shortcode('display_single_event', 'eme_display_single_event_shortcode');

function eme_get_events_page($justurl = 0, $echo = 1, $text = '') {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'text' => '', 'echo' => 1 );
      
      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
   }
   
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
add_shortcode ( 'events_page', 'eme_get_events_page_shortcode' );

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
   $sql = "SELECT COUNT(*) from $events_table WHERE event_start_date<='".$scope."'";
   return $wpdb->get_var($sql);
}

function eme_count_events_newer_than($scope) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_end_date>='".$scope."'";
   return $wpdb->get_var($sql);
}

// main function querying the database event table
function eme_get_events($o_limit, $scope = "future", $order = "ASC", $o_offset = 0, $location_id = "", $category = "", $author = "", $contact_person = "",  $show_ongoing=1, $notcategory = "", $extra_conditions = "") {
   global $wpdb, $wp_query;

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
      $conditions [] = $extra_conditions;
   }

   // if we're not in the admin itf, we don't want draft events
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions [] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $conditions [] = "event_status=".STATUS_PUBLIC;
      }
      if (get_option('eme_rsvp_hide_full_events')) {
         // COALESCE is used in case the SUM returns NULL
         // this is a correlated subquery, so the FROM clause should specify events_table again, so it will search in the outer query for events_table.event_id
         $conditions [] = "(event_rsvp=0 OR (event_rsvp=1 AND event_seats > (SELECT COALESCE(SUM(booking_seats),0) AS booked_seats FROM $bookings_table WHERE $bookings_table.event_id = $events_table.event_id)))";
      }
   }
   if (preg_match ( "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $scope )) {
      //$conditions [] = " event_start_date like '$scope'";
      if ($show_ongoing)
         $conditions [] = " ((event_start_date LIKE '$scope') OR (event_start_date <= '$scope' AND event_end_date >= '$scope'))";
      else
         $conditions [] = " (event_start_date LIKE '$scope') ";
   } elseif (preg_match ( "/^0000-([0-9]{2})$/", $scope, $matches )) {
      $year=date('Y');
      $month=$matches[1];
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
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
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
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
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_month") {
      $year=date('Y');
      $month=date('m');
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_year") {
      $year=date('Y');
      $limit_start = "$year-01-01";
      $limit_end = "$year-12-31";
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
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
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $limit_start=$matches[1];
      $limit_end=$matches[2];
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--today$/", $scope, $matches )) {
      $limit_start=$matches[1];
      $limit_end=$today;
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^today--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $limit_start=$today;
      $limit_end=$matches[1];
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\+(\d+)d$/", $scope, $matches )) {
      $limit_start = $today;
      $limit_end=date('Y-m-d',time()+$matches[1]*86400);
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^\-(\d+)d$/", $scope, $matches )) {
      $limit_end = $today;
      $limit_start=date('Y-m-d',time()-$matches[1]*86400);
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif (preg_match ( "/^(\-?\+?\d+)d--(\-?\+?\d+)d$/", $scope, $matches )) {
      $limit_start=date('Y-m-d',time()+$matches[1]*86400);
      $limit_end=date('Y-m-d',time()+$matches[2]*86400);
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
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
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
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
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
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
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_week") {
      $start_of_week = get_option('start_of_week');
      $day_offset=date('w')-$start_of_week;
      if ($day_offset<0) $day_offset+=7;
      $start_day=time()-$day_offset*86400;
      $end_day=$start_day+6*86400;
      $limit_start = $today;
      $limit_end   = date('Y-m-d',$end_day);
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_month") {
      $year=date('Y');
      $month=date('m');
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = $today;
      $limit_end   = "$year-$month-$number_of_days_month";
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "today--this_year") {
      $limit_start = $today;
      $limit_end = "$year-12-31";
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
    } elseif ($scope == "this_week--today") {
      $start_of_week = get_option('start_of_week');
      $day_offset=date('w')-$start_of_week;
      if ($day_offset<0) $day_offset+=7;
      $start_day=time()-$day_offset*86400;
      $limit_start = date('Y-m-d',$start_day);
      $limit_end = $today;
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_month--today") {
      $year=date('Y');
      $month=date('m');
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = $today;
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "this_year--today") {
      $limit_start = "$year-01-01";
      $limit_end = $today;
      if ($show_ongoing)
         $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end') OR (event_start_date <= '$limit_start' AND event_end_date >= '$limit_end'))";
      else
         $conditions [] = " (event_start_date BETWEEN '$limit_start' AND '$limit_end')";
   } elseif ($scope == "tomorrow--future") {
      if ($show_ongoing)
         $conditions [] = " (event_start_date > '$today' OR (event_end_date > '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL))";
      else
         $conditions [] = " (event_start_date > '$today')";
   } elseif ($scope == "past") {
      //$conditions [] = " (event_end_date < '$today' OR (event_end_date = '$today' and event_end_time < '$this_time' )) ";
      // not taking the hour into account until we can enter timezone info as well
      if ($show_ongoing)
         $conditions [] = " event_end_date < '$today'";
      else
         $conditions [] = " event_start_date < '$today'";
   } elseif ($scope == "today") {
      if ($show_ongoing)
         $conditions [] = " (event_start_date = '$today' OR (event_start_date <= '$today' AND event_end_date >= '$today'))";
      else
         $conditions [] = " (event_start_date = '$today')";
   } elseif ($scope == "tomorrow") {
      $tomorrow = date("Y-m-d",strtotime($today)+86400);
      if ($show_ongoing)
         $conditions [] = " (event_start_date = '$tomorrow' OR (event_start_date <= '$tomorrow' AND event_end_date >= '$tomorrow'))";
      else
         $conditions [] = " (event_start_date = '$tomorrow')";
   } else {
      if ($scope != "all")
         $scope = "future";
      if ($scope == "future") {
         //$conditions [] = " ((event_start_date = '$today' AND event_start_time >= '$this_time') OR (event_start_date > '$today') OR (event_end_date > '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL) OR (event_end_date = '$today' AND event_end_time >= '$this_time'))";
         // not taking the hour into account until we can enter timezone info as well
         if ($show_ongoing)
            $conditions [] = " (event_start_date >= '$today' OR (event_end_date >= '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL))";
         else
            $conditions [] = " (event_start_date >= '$today')";
      }
   }
   
   // when used inside a location description, you can use this_location to indicate the current location being viewed
   if ($location_id == "this_location" && isset($wp_query->query_vars['location_id'])) {
      $location_id = $wp_query->query_vars['location_id'];
   }

   if (is_numeric($location_id)) {
      if ($location_id>0)
         $conditions [] = " location_id = $location_id";
   } elseif ( preg_match('/,/', $location_id) ) {
      $location_ids=explode(',', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0)
               $location_conditions[] = " location_id = $loc";
         }
         $conditions [] = "(".implode(' OR', $location_conditions).")";
   } elseif ( preg_match('/ /', $location_id) ) {
      $location_ids=explode(' ', $location_id);
      $location_conditions = array();
      foreach ($location_ids as $loc) {
         if (is_numeric($loc) && $loc>0)
               $location_conditions[] = " location_id = $loc";
         }
         $conditions [] = "(".implode(' AND', $location_conditions).")";
   }

   if (get_option('eme_categories_enabled')) {
      if (is_numeric($category)) {
         if ($category>0)
            $conditions [] = " FIND_IN_SET($category,event_category_ids)";
      } elseif ($category == "none") {
         $conditions [] = "event_category_ids = ''";
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
         $conditions [] = "(".implode(' OR', $category_conditions).")";
      } elseif ( preg_match('/\+/', $category) ) {
         $category = explode('+', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions [] = "(".implode(' AND ', $category_conditions).")";
      }
   }

   if (get_option('eme_categories_enabled')) {
      if (is_numeric($notcategory)) {
         if ($notcategory>0)
            $conditions [] = " NOT FIND_IN_SET($notcategory,event_category_ids)";
      } elseif ($notcategory == "none") {
         $conditions [] = "event_category_ids != ''";
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
         $conditions [] = "(".implode(' OR', $category_conditions).")";
      } elseif ( preg_match('/\+/', $notcategory) ) {
         $notcategory = explode('+', $notcategory);
         $category_conditions = array();
         foreach ($notcategory as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " NOT FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions [] = "(".implode(' AND ', $category_conditions).")";
      }
   }

   // now filter the author ID
   if ($author != '' && !preg_match('/,/', $author)){
      $authinfo=get_userdatabylogin($author);
      $conditions [] = " event_author = ".$authinfo->ID;
   }elseif( preg_match('/,/', $author) ){
      $authors = explode(',', $author);
      $author_conditions = array();
      foreach($authors as $authname) {
            $authinfo=get_userdatabylogin($authname);
            $author_conditions[] = " event_author = ".$authinfo->ID;
      }
      $conditions [] = "(".implode(' OR ', $author_conditions).")";
   }

   // now filter the contact ID
   if ($contact_person != '' && !preg_match('/,/', $contact_person)){
      $authinfo=get_userdatabylogin($contact_person);
      $conditions [] = " event_contactperson_id = ".$authinfo->ID;
   }elseif( preg_match('/,/', $contact_person) ){
      $contact_persons = explode(',', $contact_person);
      $contact_person_conditions = array();
      foreach($contact_persons as $authname) {
            $authinfo=get_userdatabylogin($authname);
            $contact_person_conditions[] = " event_contactperson_id = ".$authinfo->ID;
      }
      $conditions [] = "(".implode(' OR ', $contact_person_conditions).")";
   }

   // extra conditions for authors: if we're in the admin itf, return only the events for which you have the right to change anything
   $current_userid=get_current_user_id();
   if (is_admin() && !current_user_can( get_option('eme_cap_edit_events')) && !current_user_can( get_option('eme_cap_list_events')) && current_user_can( get_option('eme_cap_author_event'))) {
      $conditions [] = "(event_author = $current_userid OR event_contactperson_id= $current_userid)";
   }
   
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   
   $sql = "SELECT *, 
         DATE_FORMAT(event_start_date, '%e') AS 'event_start_day',
         DATE_FORMAT(event_start_date, '%m') AS 'event_start_month',
         DATE_FORMAT(event_start_date, '%Y') AS 'event_start_year',
         DATE_FORMAT(event_start_time, '%k') AS 'event_start_hh',
         DATE_FORMAT(event_start_time, '%i') AS 'event_start_mm',
         DATE_FORMAT(event_start_time, '%h:%i%p') AS 'event_start_12h_time', 
         DATE_FORMAT(event_start_time, '%H:%i') AS 'event_start_24h_time', 
         DATE_FORMAT(event_end_date, '%e') AS 'event_end_day',
         DATE_FORMAT(event_end_date, '%m') AS 'event_end_month',
         DATE_FORMAT(event_end_date, '%Y') AS 'event_end_year',
         DATE_FORMAT(event_end_time, '%k') AS 'event_end_hh',
         DATE_FORMAT(event_end_time, '%i') AS 'event_end_mm',
         DATE_FORMAT(event_end_time, '%h:%i%p') AS 'event_end_12h_time',
         DATE_FORMAT(event_end_time, '%H:%i') AS 'event_end_24h_time'
         FROM $events_table
         $where
         ORDER BY event_start_date $order , event_start_time $order
         $limit 
         $offset";
   $wpdb->show_errors = true;
   $events = $wpdb->get_results ( $sql, ARRAY_A );
   $inflated_events = array ();
   if (! empty ( $events )) {
      //$wpdb->print_error(); 
      foreach ( $events as $this_event ) {
         if ($this_event ['event_status'] == STATUS_PRIVATE && !is_user_logged_in()) {
            continue;
         }
         // if we're not in the admin itf, we don't want draft events
         if (!is_admin() && $this_event ['event_status'] == STATUS_DRAFT) {
            continue;
         }
         
         if ($this_event ['location_id'] ) {
            $this_location = eme_get_location ( $this_event ['location_id'] );
            // add all location info to the event
            foreach ($this_location as $key => $value) {
               $this_event [$key] = $value;
            }
         }

         $this_event ['event_attributes'] = @unserialize($this_event ['event_attributes']);
         $this_event ['event_attributes'] = (!is_array($this_event ['event_attributes'])) ?  array() : $this_event ['event_attributes'] ;
         $this_event ['event_image_url'] = eme_image_url_for_event($this_event);
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
   $conditions [] = "event_id = $event_id";

   // if we're not in the admin itf, we don't want draft events
   if (!is_admin()) {
      if (is_user_logged_in()) {
         if (! (current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_author_event')) ||
                current_user_can( get_option('eme_cap_add_event')) || current_user_can( get_option('eme_cap_publish_event')) )) {
             $conditions [] = "event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
         }
      } else {
         $conditions [] = "event_status=".STATUS_PUBLIC;
      }
   }
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   $sql = "SELECT *, 
         DATE_FORMAT(event_start_date, '%e') AS 'event_start_day',
         DATE_FORMAT(event_start_date, '%m') AS 'event_start_month',
         DATE_FORMAT(event_start_date, '%Y') AS 'event_start_year',
         DATE_FORMAT(event_start_time, '%k') AS 'event_start_hh',
         DATE_FORMAT(event_start_time, '%i') AS 'event_start_mm',
         DATE_FORMAT(event_start_time, '%h:%i%p') AS 'event_start_12h_time', 
         DATE_FORMAT(event_start_time, '%H:%i') AS 'event_start_24h_time', 
         DATE_FORMAT(event_end_date, '%e') AS 'event_end_day',
         DATE_FORMAT(event_end_date, '%m') AS 'event_end_month',
         DATE_FORMAT(event_end_date, '%Y') AS 'event_end_year',
         DATE_FORMAT(event_end_time, '%k') AS 'event_end_hh',
         DATE_FORMAT(event_end_time, '%i') AS 'event_end_mm',
         DATE_FORMAT(event_end_time, '%h:%i%p') AS 'event_end_12h_time',
         DATE_FORMAT(event_end_time, '%H:%i') AS 'event_end_24h_time'
      FROM $events_table
      $where";
   
   //$wpdb->show_errors(true);
   $event = $wpdb->get_row ( $sql, ARRAY_A );
   //$wpdb->print_error();

   if ($event['event_end_date'] == "") {
      $event['event_end_date'] = $event['event_start_date'];
      $event['event_end_day'] = $event['event_start_day'];
      $event['event_end_month'] = $event['event_start_month'];
      $event['event_end_year'] = $event['event_start_year'];
   }
      
   if ($event && count($event>0)) {
      $location = eme_get_location ( $event ['location_id'] );
      // add all location info to the event
      foreach ($location as $key => $value) {
         $event [$key] = $value;
      }

      $event ['event_attributes'] = @unserialize($event ['event_attributes']);
      $event ['event_attributes'] = (!is_array($event ['event_attributes'])) ?  array() : $event ['event_attributes'] ;
   }   
   $event['event_image_url'] = eme_image_url_for_event($event);
   if (has_filter('eme_event_filter')) $event=apply_filters('eme_event_filter',$event);
   return $event;
}

function eme_duplicate_event($event_id) {
   global $wpdb;

   $list_limit = get_option('eme_events_admin_limit');

   //First, duplicate.
   $event_table_name = $wpdb->prefix . EVENTS_TBNAME;
   $eventArray = $wpdb->get_row("SELECT * FROM {$event_table_name} WHERE event_id={$event_id}", ARRAY_A );
   // unset the old event id
   unset($eventArray['event_id']);
   // set the new authorID
   $current_userid=get_current_user_id();
   $eventArray['event_author']=$current_userid;
   $event_ID = eme_db_insert_event($eventArray);
   if ( $event_ID ) {
      //Get the ID of the new item
      $event = eme_get_event ( $event_ID );
      $event['event_id'] = $event_ID;
      //Now we edit the duplicated item
      $title = __ ( "Edit Event", 'eme' ) . " '" . $event ['event_name'] . "'";
      echo "<div id='message' class='updated below-h2'>You are now editing the duplicated event.</div>";
      eme_event_form ( $event, $title, $event_ID );
   } else {
      echo "<div class='error'><p>There was an error duplicating the event. Try again maybe? Here are the errors:</p>";
      foreach ($EZSQL_ERROR as $errorArray) {
         echo "<p>{$errorArray['error_str']}</p>";
      }  
      echo "</div>";
      $scope = $_GET ['scope'];
      $offset = intval($_GET ['offset']);
      $order = $_GET ['order'];
      $events = eme_get_events ( $list_limit+1, $scope, $order, $offset );
      eme_events_table ( $events, $list_limit, $title, $scope, $offset );
   }
}

function eme_events_table($events, $limit, $title, $scope="future", $offset=0, $o_category=0) {

   $events_count = count ( $events );
   ?>

<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php echo $title; ?></h2>
   <?php
      admin_show_warnings();
   ?>
   <!--<div id='new-event' class='switch-tab'><a href="<?php
   echo admin_url("admin.php?page=events-manager&amp;action=edit_event")?>><?php
   _e ( 'New Event ...', 'eme' );
   ?></a></div>-->
      <?php
   
   $scope_names = array ();
   $scope_names ['past'] = __ ( 'Past events', 'eme' );
   $scope_names ['all'] = __ ( 'All events', 'eme' );
   $scope_names ['future'] = __ ( 'Future events', 'eme' );

   $event_status_array = eme_status_array ();

   ?> 
      
   <form id="posts-filter" action="" method="get">
   <input type='hidden' name='page' value='events-manager' />
   <ul class="subsubsub">
      <li><?php _e ( 'Total', 'eme' ); ?> <span class="count">(<?php if ($events_count>$limit) echo $limit; else echo count($events); echo " ". __('Events','eme'); ?>)</span></li>
   </ul>

   <div class="tablenav">

   <div class="alignleft actions">
   <select name="action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
   <option value="deleteEvents"><?php _e ( 'Delete selected events','eme' ); ?></option>
   <option value="deleteRecurrence"><?php _e ( 'Delete selected recurrent events','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
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
   </div>
   <div class="clear"></div>

   </div>
   <?php
   if (empty ( $events )) {
      _e ('No events', 'eme');
   } else {
      ?>
      
   <table class="widefat">
   <thead>
      <tr>
         <th class='manage-column column-cb check-column' scope='col'><input
            class='select-all' type="checkbox" value='1' /></th>
         <th><?php _e ( 'Name', 'eme' ); ?></th>
            <th><?php _e ( 'Status', 'eme' ); ?></th>
            <th></th>
            <th><?php _e ( 'Location', 'eme' ); ?></th>
         <th colspan="2"><?php _e ( 'Date and time', 'eme' ); ?></th>
      </tr>
   </thead>
   <tbody>
     <?php
      $i = 1;
      foreach ( $events as $event ) {
         if ($limit && $i>$limit)
            break;
         $class = ($i % 2) ? ' class="alternate"' : '';

         $localised_start_date = eme_admin_localised_date($event['event_start_date']);
         $localised_end_date = eme_admin_localised_date($event['event_end_date']);

         $today = date ( "Y-m-d" );
         
         if (isset($event ['location_name']))
            $location_summary = "<b>" . eme_trans_sanitize_html($event ['location_name']) . "</b><br />" . eme_trans_sanitize_html($event ['location_address']) . " - " . eme_trans_sanitize_html($event ['location_town']);
         else
            $location_summary = "";
         
         $style = "";
         if ($event ['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         
         ?>
     <tr <?php echo "$class $style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event ['event_id']; ?>' name='events[]' /></td>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_event&amp;event_id=".$event ['event_id']); ?>"><?php echo eme_trans_sanitize_html($event ['event_name']); ?></a>
         </strong>
         <?php
         $categories = explode(',', $event ['event_category_ids']);
         foreach($categories as $cat){
            $category = eme_get_category($cat);
            if($category)
               echo "<br /><span title='".__('Category','eme').": ".eme_trans_sanitize_html($category['category_name'])."'>".eme_trans_sanitize_html($category['category_name'])."</span>";
         }
         if ($event ['event_rsvp']) {
            $printable_address = admin_url("/admin.php?page=eme-people&amp;action=booking_printable&amp;event_id=".$event['event_id']);
            $csv_address = admin_url("/admin.php?page=eme-people&amp;action=booking_csv&amp;event_id=".$event['event_id']);
            $available_seats = eme_get_available_seats($event['event_id']);
            $pending_seats = eme_get_pending_seats($event['event_id']);
            $total_seats = $event ['event_seats'];
            if ($pending_seats >0)
               echo "<br />".__('RSVP Info: ','eme').__('Free: ','eme' ).$available_seats.", ".__('Pending: ','eme').$pending_seats.", ".__('Max: ','eme').$total_seats;
            else
               echo "<br />".__('RSVP Info: ','eme').__('Free: ','eme' ).$available_seats.", ".__('Max: ','eme').$total_seats;
            if ($total_seats!=$available_seats) {
               echo " (<a id='booking_printable_".$event['event_id']."'  target='' href='$printable_address'>".__('Printable view','eme')."</a>)";
               echo " (<a id='booking_csv_".$event['event_id']."'  target='' href='$csv_address'>".__('CSV export','eme')."</a>)";
            }
         }

         ?> 
         </td>
         <td>
         <?php
         if (isset ($event_status_array[$event['event_status']])) {
            echo $event_status_array[$event['event_status']];
            $event_url = eme_event_url($event);
            if ($event ['event_status'] == STATUS_DRAFT)
               echo "<br /> <a target='' href='$event_url'>".__('Preview event','eme')."</a>";
            else
               echo "<br /> <a target='' href='$event_url'>".__('View event','eme')."</a>";
         }
         ?> 
         </td>
         <td>
         <a href="<?php echo admin_url("admin.php?page=events-manager&amp;action=duplicate_event&amp;event_id=".$event ['event_id']); ?>" title="<?php _e ( 'Duplicate this event', 'eme' ); ?>"><strong>+</strong></a>
         </td>
         <td>
             <?php echo $location_summary; ?>
         </td>
         <td>
            <?php echo $localised_start_date; if ($localised_end_date !='' && $localised_end_date!=$localised_start_date) echo " - " . $localised_end_date; ?><br />
            <?php echo substr ( $event ['event_start_time'], 0, 5 ) . " - " . substr ( $event ['event_end_time'], 0, 5 ); ?>
         </td>
         <td>
             <?php
            if ($event ['recurrence_id']) {
               $recurrence_desc = eme_get_recurrence_desc ( $event ['recurrence_id'] );
            ?>
               <b><?php echo $recurrence_desc; ?>
            <br />
            <a href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_recurrence&amp;recurrence_id=".$event ['recurrence_id']); ?>"><?php _e ( 'Reschedule', 'eme' ); ?></a></b>
            <?php
            }
            ?>
         </td>
   </tr>
      <?php
         $i ++;
      }
      ?>
   
   </tbody>
   </table>
   <?php
   } // end of table
   ?>

   </form>

<?php
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
   ?>

</div>
<?php
}

function eme_event_form($event, $title, $element) {
   
   admin_show_warnings();

   $use_select_for_locations = get_option('eme_use_select_for_locations');
   // qtranslate there? Then we need the select, otherwise locations will be created again...
   if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
      $use_select_for_locations=1;
   }
   $event_status_array = eme_status_array ();
   $saved_bydays = array();
   $currency_array = array ();
   $currency_array ['AUD'] = __ ( 'Australian Dollar', 'eme' );
   $currency_array ['CAD'] = __ ( 'Canadian Dollar', 'eme' );
   $currency_array ['CZK'] = __ ( 'Czech Koruna', 'eme' );
   $currency_array ['DKK'] = __ ( 'Danish Krone', 'eme' );
   $currency_array ['EUR'] = __ ( 'Euro', 'eme' );
   $currency_array ['HKD'] = __ ( 'Hong Kong Dollar', 'eme' );
   $currency_array ['HUF'] = __ ( 'Hungarian Forint', 'eme' );
   $currency_array ['ILS'] = __ ( 'Israeli New Sheqel', 'eme' );
   $currency_array ['JPY'] = __ ( 'Japanese Yen', 'eme' );
   $currency_array ['MXN'] = __ ( 'Mexican Peso', 'eme' );
   $currency_array ['NOK'] = __ ( 'Norwegian Krone', 'eme' );
   $currency_array ['NZD'] = __ ( 'New Zealand Dollar', 'eme' );
   $currency_array ['PHP'] = __ ( 'Philippine Peso', 'eme' );
   $currency_array ['PLN'] = __ ( 'Polish Zloty', 'eme' );
   $currency_array ['GBP'] = __ ( 'Pound Sterling', 'eme' );
   $currency_array ['SGD'] = __ ( 'Singapore Dollar', 'eme' );
   $currency_array ['SEK'] = __ ( 'Swedish Krona', 'eme' );
   $currency_array ['CHF'] = __ ( 'Swiss Franc', 'eme' );
   $currency_array ['THB'] = __ ( 'Thai Baht', 'eme' );
   $currency_array ['USD'] = __ ( 'U.S. Dollar', 'eme' );

   // let's determine if it is a new event, handy
   if (! $element) {
      $is_new_event=1;
   } else {
      $is_new_event=0;
   }

   $show_recurrent_form = 0;
   // change prefix according to event/recurrence
   if (isset($_GET ['action']) && $_GET ['action'] == "edit_recurrence") {
      $pref = "recurrence_";
      $form_destination = "admin.php?page=events-manager&amp;action=update_recurrence&amp;recurrence_id=" . $element;
      $saved_bydays = explode ( ",", $event ['recurrence_byday'] );
      $show_recurrent_form = 1;
   } else {
      $pref = "event_";
      if ($is_new_event)
         $form_destination = "admin.php?page=eme-new_event&amp;action=insert_event";
      else
         $form_destination = "admin.php?page=events-manager&amp;action=update_event&amp;event_id=" . $element;

      if ($event ['recurrence_id']) {
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
      }
   }
   
   $hours_locale = "24";
   // Setting 12 hours format for those countries using it
   $locale_code = substr ( get_locale (), 0, 2 );
   if (preg_match ( "/en|sk|zh|us|uk/", $locale_code ))
      $hours_locale = "12";

   if ($event['event_start_12h_time'] == "")
         $event['event_start_12h_time'] = date('h:iA');
   if ($event['event_start_24h_time'] == "")
         $event['event_start_24h_time'] = date('H:i');
   if ($event['event_end_12h_time'] == "")
         $event['event_end_12h_time'] = date('h:iA',time()+3600);
   if ($event['event_end_24h_time'] == "")
         $event['event_end_24h_time'] = date('H:i',time()+3600);
   
   $localised_example = eme_localised_date("2008-11-28");
   $localised_end_example = eme_localised_date("2008-11-28");
   
   $localised_start_date = eme_localised_date($event['event_start_date']);
   $localised_end_date = eme_localised_date($event['event_end_date']);
   if (!isset($event['recurrence_start_date'])) $event['recurrence_start_date']="";
   $localised_rec_start_date = eme_localised_date($event['recurrence_start_date']);
   if (!isset($event['recurrence_end_date'])) $event['recurrence_end_date']="";
   $localised_rec_end_date = eme_localised_date($event['recurrence_end_date']);
   //if($event[$pref.'rsvp'])
    //   echo (eme_bookings_table($event[$pref.'id']));
   

   $freq_options = array ("daily" => __ ( 'Daily', 'eme' ), "weekly" => __ ( 'Weekly', 'eme' ), "monthly" => __ ( 'Monthly', 'eme' ) );
   $days_names = array (1 => __ ( 'Mon' ), 2 => __ ( 'Tue' ), 3 => __ ( 'Wed' ), 4 => __ ( 'Thu' ), 5 => __ ( 'Fri' ), 6 => __ ( 'Sat' ), 7 => __ ( 'Sun' ) );
   $weekno_options = array ("1" => __ ( 'first', 'eme' ), '2' => __ ( 'second', 'eme' ), '3' => __ ( 'third', 'eme' ), '4' => __ ( 'fourth', 'eme' ), '5' => __ ( 'fifth', 'eme' ), '-1' => __ ( 'last', 'eme' ), "none" => __('Start day') );
   
   // for new events, check the setting wether or not to enable RSVP
   if ($is_new_event) {
      if (get_option('eme_rsvp_reg_for_new_events'))
         $event_RSVP_checked = "checked='checked'";
      else
         $event_RSVP_checked = "";
      $event_number_spaces=intval(get_option('eme_rsvp_default_number_spaces'));
      if (get_option('eme_rsvp_registered_users_only'))
         $registration_wp_users_only = "checked='checked'";
      else
         $registration_wp_users_only = "";
   } else {
      $event ['event_rsvp'] ? $event_RSVP_checked = "checked='checked'" : $event_RSVP_checked = '';
      $event_number_spaces=$event ['event_seats'];
      $event ['registration_wp_users_only'] ? $registration_wp_users_only = "checked='checked'" : $registration_wp_users_only = '';
   }
   $event ['registration_requires_approval'] ? $registration_requires_approval = "checked='checked'" : $registration_requires_approval = '';
   $event ['use_paypal'] ? $use_paypal_checked = "checked='checked'" : $use_paypal_checked = '';
   
   ob_start();
   ?>
   <form id="eventForm" method="post" enctype="multipart/form-data" action="<?php echo $form_destination; ?>">
      <div class="wrap">
         <div id="icon-events" class="icon32"><br /></div>
         <h2><?php echo eme_trans_sanitize_html($title); ?></h2>
         <?php
         if ($event ['recurrence_id']) {
            ?>
         <p id='recurrence_warning'>
            <?php
               if (isset ( $_GET ['action'] ) && ($_GET ['action'] == 'edit_recurrence')) {
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
                           if ($event ['recurrence_id'])
                              $recurrence_YES = "checked='checked' disabled='disabled'";
                        ?>
                        <p>
                           <input id="event-recurrence" type="checkbox" name="repeated_event"
                              value="1" <?php echo $recurrence_YES; ?> />
                        </p>
                        <div id="event_recurrence_pattern">
                           <p>Frequency:
                              <select id="recurrence-frequency" name="recurrence_freq">
                                 <?php eme_option_items ( $freq_options, $event ['recurrence_freq'] ); ?>
                              </select>
                           </p>
                           <p>
                              <?php _e ( 'Every', 'eme' )?>
                              <input id="recurrence-interval" name='recurrence_interval'
                                size='2' value='<?php if (isset ($event ['recurrence_interval'])) echo $event ['recurrence_interval']; ?>' />
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
                                 <?php eme_option_items ( $weekno_options, $event ['recurrence_byweekno'] ); ?>
                              </select>
                              <select id="recurrence-weekday" name="recurrence_byday">
                                 <?php eme_option_items ( $days_names, $event ['recurrence_byday'] ); ?>
                              </select>
                              <?php _e ( 'Day of month', 'eme' )?>
                              <br />
                              <?php _e ( 'If you use "Start day" as day of the month, the event start date will be used as a reference.', 'eme' )?>
                              &nbsp;</p>
                        </div>
                        <p id="recurrence-tip">
                           <?php _e ( 'Check if your event happens more than once according to a regular pattern', 'eme' )?>
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
                           wp_dropdown_users ( array ('name' => 'event_contactperson_id', 'show_option_none' => __ ( "Event author", 'eme' ), 'selected' => $event ['event_contactperson_id'] ) );
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
                              <input id="wp_member_required-checkbox" name='registration_wp_users_only' value='1' type='checkbox' <?php echo $registration_wp_users_only; ?> />
                              <?php _e ( 'Require WP membership for registration','eme' ); ?>
                           <br />
                              <?php _e ( 'Spaces','eme' ); ?> :
                              <input id="seats-input" type="text" name="event_seats" size='5' value="<?php echo $event_number_spaces; ?>" />
                           <br />
                              <?php _e ( 'Allow RSVP until ','eme' ); ?>
                              <input id="rsvp_number_days" type="text" name="rsvp_number_days" maxlength='2' size='2' value="<?php echo $event ['rsvp_number_days']; ?>" />
                              <?php _e ( ' days before the event starts.','eme' ); ?>
                           <br />
                              <?php _e ( 'Price: ','eme' ); ?>
                              <input id="price" type="text" name="price" maxlength='9' size='3' value="<?php echo $event ['price']; ?>" />
                              <select id="currency" name="currency">
                              <?php
                                 foreach ( $currency_array as $key=>$value) {
                                    if ($event['currency'] && ($event['currency']==$key)) {
                                       $selected = "selected='selected'";
                                    } else {
                                       $selected = "";
                                    }
                                    echo "<option value='$key' $selected>$value</option>";
                                 }
                              ?>
                              </select><br />
                            <br />
                              <?php _e ( 'Payment methods','eme' ); ?><br />
                              <input id="paypal-checkbox" name='use_paypal' value='1' type='checkbox' <?php echo $use_paypal_checked; ?> /><?php _e ( 'Paypal ','eme' ); ?><br />
                           </p>
                           <?php if ($event ['event_rsvp']) {
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
                  <!-- we need titlediv for qtranslate as ID -->
                  <div id="titlediv" class="stuffbox">
                     <h3>
                        <?php _e ( 'Name', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <!-- we need title for qtranslate as ID -->
                        <input type="text" id="title" name="event_name" value="<?php echo eme_sanitize_html($event ['event_name']); ?>" />
                        <br />
                        <?php _e ( 'The event name. Example: Birthday party', 'eme' )?>
                        <br />
                        <br />
                        <?php if ($event ['event_name'] != "") {
                                 _e ('Permalink: ', 'eme' );
                                 echo trailingslashit(home_url()).eme_permalink_convert(get_option ( 'eme_permalink_events_prefix')).$event['event_id']."/";
                                 $slug = $event['event_slug'] ? $event['event_slug'] : $event['event_name'];
				 $slug = untrailingslashit(eme_permalink_convert($slug));
                        ?>
                                 <input type="text" id="slug" name="event_slug" value="<?php echo $slug; ?>" /><?php echo user_trailingslashit(""); ?>
                        <?php
                              }
                        ?>
                     </div>
                  </div>
                  <div id="div_event_date" class="stuffbox">
                     <h3 id='event-date-title'>
                        <?php _e ( 'Event date', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input id="localised-start-date" type="text" name="localised_event_start_date" value="<?php echo $localised_start_date?>" style="display: none;" readonly="readonly" />
                        <input id="start-date-to-submit" type="text" name="event_start_date" value="<?php echo $event ['event_start_date']?>" style="background: #FCFFAA" />
                        <input id="localised-end-date" type="text" name="localised_event_end_date" value="<?php echo $localised_end_date?>" style="display: none;" readonly="readonly" />
                        <input id="end-date-to-submit" type="text" name="event_end_date" value="<?php echo $event ['event_end_date']?>" style="background: #FCFFAA" />
                        <br />
                        <span id='event-date-explanation'>
                        <?php _e ( 'The event date.', 'eme' ); ?>
                        </span>
                     </div>
                  </div>
                  <div id="div_recurrence_date" class="stuffbox">
                     <h3 id='recurrence-dates-title'>
                        <?php _e ( 'Recurrence dates', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input id="localised-rec-start-date" type="text" name="localised_recurrence_date" value="<?php echo $localised_rec_start_date?>" readonly="readonly" />
                        <input id="rec-start-date-to-submit" type="text" name="recurrence_start_date" value="<?php echo $event ['recurrence_start_date']?>" style="background: #FCFFAA" />
                        <input id="localised-rec-end-date" type="text" name="localised_recurrence_end_date" value="<?php echo $localised_rec_end_date?>" readonly="readonly" />
                        <input id="rec-end-date-to-submit" type="text" name="recurrence_end_date" value="<?php echo $event ['recurrence_end_date']?>" style="background: #FCFFAA" />
                        <br />
                        <span id='recurrence-dates-explanation'>
                        <?php _e ( 'The recurrence beginning and end date.', 'eme' ); ?>
                        </span>
                     </div>
                  </div>
                  <div id="div_event_time" class="stuffbox">
                     <h3>
                        <?php _e ( 'Event time', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input id="start-time" type="text" size="8" maxlength="8" name="event_start_time" value="<?php echo $event ['event_start_' . $hours_locale . "h_time"]; ?>" />
                        -
                        <input id="end-time" type="text" size="8" maxlength="8" name="event_end_time" value="<?php echo $event ['event_end_' . $hours_locale . "h_time"]; ?>" />
                        <br />
                        <?php _e ( 'The time of the event beginning and end', 'eme' )?>
                        . </div>
                  </div>
                  <div id="div_location_coordinates" class="stuffbox" style='display: none;'>
                     <h3>
                        <?php _e ( 'Coordinates', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input id='location_latitude' name='location_latitude' type='text' value='<?php echo $event ['location_latitude']; ?>' size='15' />
                        -
                        <input id='location_longitude' name='location_longitude' type='text' value='<?php echo $event ['location_longitude']; ?>' size='15' />
                     </div>
                  </div>
                  <div id="div_event_page_title_format" class="postbox <?php if ($event['event_page_title_format']=="") echo "closed"; ?>">
                     <div class="handlediv" title="Click to toggle">
                        <br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Single Event Title Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_page_title_format" id="event_page_title_format" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_page_title_format']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the single event title.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_single_event_format" class="postbox <?php if ($event['event_single_event_format']=="") echo "closed"; ?>">
                                                        <div class="handlediv" title="Click to toggle">
                        <br />
                                                        </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Single Event Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_single_event_format" id="event_single_event_format" rows="6" cols="60"><?php echo eme_sanitize_html($event ['event_single_event_format']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the single event page.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_contactperson_email_body" class="postbox <?php if ($event['event_contactperson_email_body']=="") echo "closed"; ?>">
                     <div class="handlediv" title="Click to toggle">
                        <br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Contact Person Email Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_contactperson_email_body" id="event_contactperson_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_contactperson_email_body']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the email which will be sent to the contact person.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_registration_recorded_ok_html" class="postbox <?php if ($event['event_registration_recorded_ok_html']=="") echo "closed"; ?>">
                     <div class="handlediv" title="Click to toggle">
                        <br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Booking recorded html Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_registration_recorded_ok_html" id="event_registration_recorded_ok_html" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_registration_recorded_ok_html']);?></textarea>
                        <br />
                        <p><?php _e ( 'The text (html allowed) shown to the user when the booking has been made successfully.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_respondent_email_body" class="postbox <?php if ($event['event_respondent_email_body']=="") echo "closed"; ?>">
                     <div class="handlediv" title="Click to toggle">
                        <br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Respondent Email Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_respondent_email_body" id="event_respondent_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_respondent_email_body']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the email which will be sent to the respondent.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_registration_pending_email_body" class="postbox <?php if ($event['event_registration_pending_email_body']=="") echo "closed"; ?>">
                     <div class="handlediv" title="Click to toggle">
                        <br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Registration Pending Email Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_registration_pending_email_body" id="event_registration_pending_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_registration_pending_email_body']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the email which will be sent to the respondent if the registration is pending.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_registration_form_format" class="postbox <?php if ($event['event_registration_form_format']=="") echo "closed"; ?>">
                     <div class="handlediv" title="Click to toggle">
                        <br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Registration Form Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_registration_form_format" id="event_registration_form_format" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_registration_form_format']);?></textarea>
                        <br />
                        <p><?php _e ( 'The registration form format.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                   <div id="div_location_name" class="stuffbox" style='overflow: hidden;'>
                     <h3>
                        <?php _e ( 'Location', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <table id="eme-location-data">
                           <tr>
                           <?php  if($use_select_for_locations) {
                              $location_0['location_id']=0;
                              $location_0['location_name']= '';
                              $locations = eme_get_locations();
                           ?>
                              <th><?php _e('Location','eme') ?></th>
                              <td> 
                                 <select name="location-select-id" id='location-select-id' size="1">
         <option value="<?php echo $location_0['location_id'] ?>" ><?php echo eme_trans_sanitize_html($location_0['location_name']) ?></option>
                                 <?php 
                                 $selected_location=$location_0;
                                 foreach($locations as $location) :
                                    $selected = "";
                                    if(isset($event['location_id']))  { 
                                       $location_id =  $event['location_id'];
                                       if ($location_id == $location['location_id']) {
                                          $selected_location=$location;
                                          $selected = "selected='selected' ";
                                       }
                                    }
                                 ?>
         <option value="<?php echo $location['location_id'] ?>" <?php echo $selected ?>><?php echo eme_trans_sanitize_html($location['location_name']) ?></option>
                                 <?php endforeach; ?>
                                 </select>
                                 <input type='hidden' name='location-select-name' value='<?php echo eme_trans_sanitize_html($selected_location['location_name'])?>'/>
                                 <input type='hidden' name='location-select-town' value='<?php echo eme_trans_sanitize_html($selected_location['location_town'])?>'/>
                                 <input type='hidden' name='location-select-address' value='<?php echo eme_trans_sanitize_html($selected_location['location_address'])?>'/>      
                              </td>
                           <?php } else { ?>
                              <th><?php _e ( 'Name','eme' )?>
                                 &nbsp;</th>
                              <td><input name="translated_location_name" type="hidden" value="<?php echo eme_trans_sanitize_html($event ['location_name'])?>" /><input id="location_name" type="text" name="location_name" value="<?php echo eme_trans_sanitize_html($event ['location_name'])?>" /></td>
                           <?php } ?>
                           <?php
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
         ; // end of IF_GMAP_ACTIVE ?>
                           </tr>
                            <?php  if(!$use_select_for_locations) : ?>
                           <tr>
<td colspan='2'><p><?php _e ( 'The name of the location where the event takes place. You can use the name of a venue, a square, etc', 'eme' );?>
<br />
      <?php _e ( 'If you leave this empty, the map will NOT be shown for this event', 'eme' );?></p></td>
                           </tr>
                           <?php else: ?>
                           <tr >
<td colspan='2'  rowspan='5' style='vertical-align: top'>
                                    <p><?php
   _e ( 'Select a location for your event', 'eme' )?></p>
</td>
                           </tr>
                           <?php endif; ?>
                            <?php  if(!$use_select_for_locations) : ?> 
                           <tr>
                              <th><?php _e ( 'Address:', 'eme' )?> &nbsp;</th>
                              <td><input id="location_address" type="text" name="location_address" value="<?php echo $event ['location_address']; ?>" /></td>
                           </tr>
                           <tr>
                              <td colspan='2'><p>
                                    <?php _e ( 'The address of the location where the event takes place. Example: 21, Dominick Street', 'eme' )?>
                              </p></td>
                           </tr>
                           <tr>
                              <th><?php _e ( 'Town:', 'eme' )?> &nbsp;</th>
                              <td><input id="location_town" type="text" name="location_town" value="<?php echo $event ['location_town']?>" /></td>
                           </tr>
                           <tr>
                              <td colspan='2'><p>
                                    <?php _e ( 'The town where the location is located. If you\'re using the Google Map integration and want to avoid geotagging ambiguities include the country in the town field. Example: Verona, Italy.', 'eme' )?>
                                 </p></td>
                           </tr>
                           <?php endif; ?>
                        </table>
                     </div>
                  </div>
                  <div id="div_event_notes" class="postbox">
                     <h3>
                        <?php _e ( 'Details', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
                  <!-- we need content for qtranslate as ID -->
                           <?php wp_editor($event ['event_notes'],"content"); ?>
                        </div>
                        <br />
                        <?php _e ( 'Details about the event', 'eme' )?>
                     </div>
                  </div>
                  <div id="div_event_image" class="postbox">
                     <h3>
                        <?php _e ( 'Event image', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <div id="event_current_image" class="postarea">
                        <?php if (isset($event['event_image_url']) && !empty($event['event_image_url'])) {
                                 _e('Current image:', 'eme');
                                 echo "<img src='".$event['event_image_url']."' alt='".eme_trans_sanitize_html($event['event_name'])."'/>";
                              }
                        ?>
                        </div>
                        <br />
                        <input id="event_image" name="event_image" type="file" size="35" />
                        <?php _e ( 'Select an image to upload', 'eme' )?><br />
                        <input id="eme_remove_old_image" name="eme_remove_old_image" type="checkbox" value="1" />
                        <?php _e ( 'Remove existing image', 'eme' )?>
                     </div>
                  </div>
                  <?php if(get_option('eme_attributes_enabled')) : ?>
                  <div id="div_event_attributes" class="postbox">
                     <h3>
                        <?php _e ( 'Attributes', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <?php eme_attributes_form($event); ?>
                     </div>
                  </div>
                  <?php endif; ?>
                  <div id="urldiv" class="stuffbox">
                     <h3>
                        <?php _e ( 'External link', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input type="text" id="event_url" name="event_url" value="<?php echo eme_sanitize_html($event ['event_url']); ?>" />
                        <br />
                        <?php _e ( 'If this is filled in, the single event URL will point to this url instead of the standard event page.', 'eme' )?>
                     </div>
                  </div>
               </div>
               <p class="submit">
                  <input type="submit" name="events_update" value="<?php _e ( 'Submit Event', 'eme' ); ?> &raquo;" />
               </p>

            </div>
         </div>
      </div>
   </form>
<?php
   echo ob_get_clean();
}

function eme_validate_event($event) {
   global $required_fields;
   $errors = array ();
   foreach ( $required_fields as $field ) {
      if ($event [$field] == "") {
         $errors [] = $field;
      }
   }
   $error_message = "";
   if (count ( $errors ) > 0)
      $error_message = __ ( 'Missing fields: ','eme' ) . implode ( ", ", $errors ) . ". ";
   if (isset($_POST ['repeated_event']) && $_POST ['repeated_event'] == "1" && (!isset($_POST ['recurrence_end_date']) || $_POST ['recurrence_end_date'] == ""))
      $error_message .= __ ( 'Since the event is repeated, you must specify an event date for the recurrence.', 'eme' );
   if (isset($_FILES['event_image']) && ($_FILES['event_image']['size'] > 0) ) {
      if (is_uploaded_file($_FILES['event_image']['tmp_name'])) {
         $mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
         $maximum_size = get_option('eme_image_max_size');
         if ($_FILES['event_image']['size'] > $maximum_size)
               $error_message .= "<li>".__('The image file is too big! Maximum size:', 'eme')." $maximum_size</li>";
         list($width, $height, $type, $attr) = getimagesize($_FILES['event_image']['tmp_name']);
         $maximum_width = get_option('eme_image_max_width');
         $maximum_height = get_option('eme_image_max_height');
         if (($width > $maximum_width) || ($height > $maximum_height))
               $error_message .= "<li>". __('The image is too big! Maximum size allowed:', 'eme')." $maximum_width x $maximum_height</li>";
         if (($type!=1) && ($type!=2) && ($type!=3))
                  $error_message .= "<li>".__('The image is in a wrong format!', 'eme')."</li>";
      } else {
	 $error_message .= "<li>".__('The image upload failed!', 'eme')."</li>";
      }
   }

   if ($error_message != "")
      return $error_message;
   else
      return "OK";

}

function eme_admin_general_css() {
   echo "<link rel='stylesheet' href='".EME_PLUGIN_URL."events_manager.css' type='text/css'/>\n";
   $file_name= get_stylesheet_directory()."/eme.css";
   if (file_exists($file_name)) {
      echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme.css' type='text/css'/>\n";
   }
}
// General script to make sure hidden fields are shown when containing data
function eme_admin_general_script() {
   eme_admin_general_css();
   ?>
<script src="<?php echo EME_PLUGIN_URL; ?>js/eme.js" type="text/javascript"></script>
<script src="<?php echo EME_PLUGIN_URL; ?>js/jquery-ui-datepicker/ui.datepicker.js" type="text/javascript"></script>
<script src="<?php echo EME_PLUGIN_URL; ?>js/timeentry/jquery.timeentry.js" type="text/javascript"></script>
<?php
   
   // Check if the locale is there and loads it
   $locale_code = substr ( get_locale (), 0, 2 );
   
   $show24Hours = 'true';
   // Setting 12 hours format for those countries using it
   if (preg_match ( "/en|sk|zh|us|uk/", $locale_code ))
      $show24Hours = 'false';
   
   $locale_file = EME_PLUGIN_URL. "/js/jquery-ui-datepicker/i18n/ui.datepicker-$locale_code.js";
   // for english, no translation code is needed
   if ($locale_code != "en") {
      ?>
<script src="<?php echo $locale_file ?>" type="text/javascript"></script>
<?php
   }
   ?>

<style type='text/css' media='all'>
@import
   "<?php echo EME_PLUGIN_URL; ?>js/jquery-ui-datepicker/ui.datepicker.css"
   ;
</style>
<script type="text/javascript">
   //<![CDATA[
   // TODO: make more general, to support also latitude and longitude (when added)
$j_eme_event=jQuery.noConflict();

function updateIntervalDescriptor () { 
   $j_eme_event(".interval-desc").hide();
   var number = "-plural";
   if ($j_eme_event('input#recurrence-interval').val() == 1 || $j_eme_event('input#recurrence-interval').val() == "")
   number = "-singular"
   var descriptor = "span#interval-"+$j_eme_event("select#recurrence-frequency").val()+number;
   $j_eme_event(descriptor).show();
}
function updateIntervalSelectors () {
   $j_eme_event('p.alternate-selector').hide();
   $j_eme_event('p#'+ $j_eme_event('select#recurrence-frequency').val() + "-selector").show();
   //$j_eme_event('p.recurrence-tip').hide();
   //$j_eme_event('p#'+ $j_eme_event(this).val() + "-tip").show();
}
function updateShowHideRecurrence () {
   if($j_eme_event('input#event-recurrence').attr("checked")) {
      $j_eme_event("#event_recurrence_pattern").fadeIn();
      $j_eme_event("div#div_recurrence_date").show();
      $j_eme_event("p#recurrence-tip").hide();
      $j_eme_event("p#recurrence-tip-2").show();
   } else {
      $j_eme_event("#event_recurrence_pattern").hide();
      $j_eme_event("div#div_recurrence_date").hide();
      $j_eme_event("p#recurrence-tip").show();
      $j_eme_event("p#recurrence-tip-2").hide();
   }
}

function updateShowHideRsvp () {
   if($j_eme_event('input#rsvp-checkbox').attr("checked")) {
      $j_eme_event("div#rsvp-data").fadeIn();
   } else {
      $j_eme_event("div#rsvp-data").hide();
   }
}

$j_eme_event(document).ready( function() {
   locale_format = "ciao";
 
   $j_eme_event("#div_recurrence_date").hide();
   $j_eme_event("#localised-start-date").show();
   $j_eme_event("#localised-end-date").show();

   $j_eme_event("#start-date-to-submit").hide();
   $j_eme_event("#end-date-to-submit").hide(); 
   $j_eme_event("#rec-start-date-to-submit").hide();
   $j_eme_event("#rec-end-date-to-submit").hide(); 
   $j_eme_event("#localised-start-date").datepicker($j_eme_event.extend({},
      ($j_eme_event.datepicker.regional["<?php echo $locale_code; ?>"], 
      {altField: "#start-date-to-submit", 
      altFormat: "yy-mm-dd"})));
   $j_eme_event("#localised-end-date").datepicker($j_eme_event.extend({},
      ($j_eme_event.datepicker.regional["<?php echo $locale_code; ?>"], 
      {altField: "#end-date-to-submit", 
      altFormat: "yy-mm-dd"})));
   $j_eme_event("#localised-rec-start-date").datepicker($j_eme_event.extend({},
      ($j_eme_event.datepicker.regional["<?php echo $locale_code; ?>"], 
      {altField: "#rec-start-date-to-submit", 
      altFormat: "yy-mm-dd"})));
   $j_eme_event("#localised-rec-end-date").datepicker($j_eme_event.extend({},
      ($j_eme_event.datepicker.regional["<?php echo $locale_code; ?>"], 
      {altField: "#rec-end-date-to-submit", 
      altFormat: "yy-mm-dd"})));

   $j_eme_event("#start-time").timeEntry({spinnerImage: '', show24Hours: <?php echo $show24Hours; ?> });
   $j_eme_event("#end-time").timeEntry({spinnerImage: '', show24Hours: <?php echo $show24Hours; ?>});

   $j_eme_event('input.select-all').change(function(){
      if($j_eme_event(this).is(':checked'))
         $j_eme_event('input.row-selector').attr('checked', true);
      else
         $j_eme_event('input.row-selector').attr('checked', false);
   });

   // if any of event_single_event_format,event_page_title_format,event_contactperson_email_body,event_respondent_email_body,event_registration_pending_email_body, event_registration_form_format
   // is empty: display default value on focus, and if the value hasn't changed from the default: empty it on blur

   $j_eme_event('textarea#event_page_title_format').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_event_page_title_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   }); 
   $j_eme_event('textarea#event_page_title_format').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_event_page_title_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 
   $j_eme_event('textarea#event_single_event_format').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_single_event_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   }); 
   $j_eme_event('textarea#event_single_event_format').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_single_event_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 
   $j_eme_event('textarea#event_contactperson_email_body').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_contactperson_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   });
   $j_eme_event('textarea#event_contactperson_email_body').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_contactperson_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 
   $j_eme_event('textarea#event_respondent_email_body').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_respondent_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   }); 
   $j_eme_event('textarea#event_respondent_email_body').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_respondent_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 
   $j_eme_event('textarea#event_registration_recorded_ok_html').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_recorded_ok_html' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   });
   $j_eme_event('textarea#event_registration_recorded_ok_html').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_recorded_ok_html' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   });
   $j_eme_event('textarea#event_registration_pending_email_body').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_pending_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   });
   $j_eme_event('textarea#event_registration_pending_email_body').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_pending_email_body' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   });
   $j_eme_event('textarea#event_registration_form_format').focus(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_form_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   }); 
   $j_eme_event('textarea#event_registration_form_format').blur(function(){
      var tmp_value='<?php echo rawurlencode(get_option('eme_registration_form_format' )); ?>';
      tmp_value=unescape(tmp_value).replace(/\r\n/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 

   if ($j_eme_event('[name=eme_rsvp_mail_send_method]').val() != "smtp") {
      $j_eme_event('tr#eme_smtp_host_row').hide();
      $j_eme_event('tr#eme_rsvp_mail_SMTPAuth_row').hide();
      $j_eme_event('tr#eme_smtp_username_row').hide(); 
      $j_eme_event('tr#eme_smtp_password_row').hide();
   }
   $j_eme_event('[name=eme_rsvp_mail_send_method]').change(function() {
      if($j_eme_event(this).val() == "smtp") {
         $j_eme_event('tr#eme_smtp_host_row').show();
         $j_eme_event('tr#eme_rsvp_mail_SMTPAuth_row').show();
         $j_eme_event('tr#eme_smtp_username_row').show(); 
         $j_eme_event('tr#eme_smtp_password_row').show(); 
         $j_eme_event('tr#eme_rsvp_mail_port_row').show(); 
      } else {
         $j_eme_event('tr#eme_smtp_host_row').hide();
         $j_eme_event('tr#eme_rsvp_mail_SMTPAuth_row').hide();
         $j_eme_event('tr#eme_smtp_username_row').hide(); 
         $j_eme_event('tr#eme_smtp_password_row').hide();
         $j_eme_event('tr#eme_rsvp_mail_port_row').hide(); 
      }
   });
   if ($j_eme_event('input[name=eme_rsvp_mail_SMTPAuth]:checked').val() != 1) {
      $j_eme_event('tr#eme_smtp_username_row').hide(); 
      $j_eme_event('tr#eme_smtp_password_row').hide();
   }
   $j_eme_event('input[name=eme_rsvp_mail_SMTPAuth]').change(function() {
      if($j_eme_event(this).val() == 1) {
         $j_eme_event('tr#eme_smtp_username_row').show(); 
         $j_eme_event('tr#eme_smtp_password_row').show(); 
      } else {
         $j_eme_event('tr#eme_smtp_username_row').hide(); 
         $j_eme_event('tr#eme_smtp_password_row').hide();
      }
   });
   updateIntervalDescriptor(); 
   updateIntervalSelectors();
   updateShowHideRecurrence();
   updateShowHideRsvp();
   $j_eme_event('input#event-recurrence').change(updateShowHideRecurrence);
   $j_eme_event('input#rsvp-checkbox').change(updateShowHideRsvp);
   // recurrency elements
   $j_eme_event('input#recurrence-interval').keyup(updateIntervalDescriptor);
   $j_eme_event('select#recurrence-frequency').change(updateIntervalDescriptor);
   $j_eme_event('select#recurrence-frequency').change(updateIntervalSelectors);

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
      errors = "";
      var recurring = $j_eme_event("input[name=repeated_event]:checked").val();
      //requiredFields= new Array('event_name', 'localised_event_start_date', 'location_name','location_address','location_town');
      requiredFields= new Array('event_name', 'localised_event_start_date');
      var localisedRequiredFields = {'event_name':"<?php _e ( 'Name', 'eme' )?>",
                      'localised_event_start_date':"<?php _e ( 'Date', 'eme' )?>"
                     };
      
      missingFields = new Array;
      for (var i in requiredFields) {
         if ($j_eme_event("input[name=" + requiredFields[i]+ "]").val() == 0) {
            missingFields.push(localisedRequiredFields[requiredFields[i]]);
            $j_eme_event("input[name=" + requiredFields[i]+ "]").css('border','2px solid red');
         } else {
            $j_eme_event("input[name=" + requiredFields[i]+ "]").css('border','1px solid #DFDFDF');
         }
      }
   
      //    alert('ciao ' + recurring+ " end: " + $j_eme_event("input[@name=localised_event_end_date]").val());
      if (missingFields.length > 0) {
         errors = "<?php echo _e ( 'Some required fields are missing:', 'eme' )?> " + missingFields.join(", ") + ".\n";
      }
      if(recurring && $j_eme_event("input[name=localised_recurrence_end_date]").val() == "") {
         errors = errors +  "<?php _e ( 'Since the event is repeated, you must specify an end date', 'eme' )?>."; 
         $j_eme_event("input[name=localised_recurrence_end_date]").css('border','2px solid red');
      } else {
         $j_eme_event("input[name=localised_recurrence_end_date]").css('border','1px solid #DFDFDF');
      }
      if(errors != "") {
         alert(errors);
         return false;
      }
      return true;
   }

   $j_eme_event('#eventForm').bind("submit", validateEventForm);


   function areyousuretodelete() {
      if ($j_eme_event("select[name=action]").val() == "denyRegistration") {
        if (!confirm("<?php _e('Are you sure you want to deny registration for these bookings?','eme');?>")) {
           return false;
        } else {
           return true;
        }
      }
      return true;
   }
   $j_eme_event('#eme-admin-pendingform').bind("submit", areyousuretodelete);
   $j_eme_event('#eme-admin-changeregform').bind("submit", areyousuretodelete);
      
});
//]]>
</script>

<?php
}

function eme_admin_map_script() {
   // when the action is the POST of a new event, don't do the javascript
   if (isset ( $_GET ['page'] ) && $_GET ['page'] == 'eme-new_event' && isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'insert_event') {
      return;
   }

   # we also do this for locations, since the locations page also needs the loadMap javascript function
   if ((isset ( $_GET ['page'] ) && ($_GET ['page'] == 'eme-locations' || $_GET ['page'] == 'eme-new_event')) ||
       (isset ( $_REQUEST ['action'] ) && ($_REQUEST ['action'] == 'edit_event' || $_REQUEST ['action'] == 'edit_recurrence'))) {
         if (isset($_REQUEST ['event_id']))
            $event_ID = intval($_REQUEST ['event_id']);
         else
            $event_ID =0;
         $event = eme_get_event ( $event_ID );
         
            ?>
<style type="text/css">
/* div#location_town, div#location_address, div#location_name {
               width: 480px;
            }
            table.form-table {
               width: 50%;
            }     */
</style>
<script src="http://maps.google.com/maps/api/js?v=3.1&amp;sensor=false" type="text/javascript"></script>
<script type="text/javascript">
         //<![CDATA[
            $j_eme_admin=jQuery.noConflict();
      
         function loadMap(location, town, address) {
            var latlng = new google.maps.LatLng(-34.397, 150.644);
            var myOptions = {
               zoom: 13,
               center: latlng,
               scrollwheel: <?php echo get_option('eme_gmap_zooming') ? 'true' : 'false'; ?>,
               disableDoubleClickZoom: true,
               mapTypeControlOptions: {
                  mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
               },
               mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            $j_eme_admin("#eme-admin-location-map").show();
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
                     $j_eme_admin('input#location_latitude').val(results[0].geometry.location.lat());
                     $j_eme_admin('input#location_longitude').val(results[0].geometry.location.lng());
                     $j_eme_admin("#eme-admin-location-map").show();
                     $j_eme_admin('#eme-admin-map-not-found').hide();
                  } else {
                     $j_eme_admin("#eme-admin-location-map").hide();
                     $j_eme_admin('#eme-admin-map-not-found').show();
                  }
               });
            } else {
               $j_eme_admin("#eme-admin-location-map").hide();
               $j_eme_admin('#eme-admin-map-not-found').show();
            }
         }
 
         $j_eme_admin(document).ready(function() {
            <?php 
            // if we're creating a new event, or editing an event *AND*
            // the use_select_for_locations options is on or qtranslate is installed
            // then we do the select thing
            // We check on the new/edit event because this javascript is also executed for editing locations, and then we don't care
            // about the use_select_for_locations parameter
            if (
               ((isset($_REQUEST['action']) && ($_REQUEST['action'] == 'edit_event' || $_REQUEST['action'] == 'edit_recurrence')) || (isset($_GET['page']) && $_GET['page'] == 'eme-new_event')) && 
                     (get_option('eme_use_select_for_locations') || function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))) { ?>
            eventLocation = $j_eme_admin("input[name='location-select-name']").val(); 
            eventTown = $j_eme_admin("input[name='location-select-town']").val();
            eventAddress = $j_eme_admin("input[name='location-select-address']").val(); 
   
               <?php } else { ?>
            eventLocation = $j_eme_admin("input[name='translated_location_name']").val(); 
            eventTown = $j_eme_admin("input#location_town").val(); 
            eventAddress = $j_eme_admin("input#location_address").val();
               <?php } ?>

            loadMap(eventLocation, eventTown, eventAddress);
         
            $j_eme_admin("input[name='location_name']").blur(function(){
                  newEventLocation = $j_eme_admin("input[name='location_name']").val();
                  if (newEventLocation !=eventLocation) {
                     loadMap(newEventLocation, eventTown, eventAddress); 
                     eventLocation = newEventLocation;
                  }
            });
            $j_eme_admin("input#location_town").blur(function(){
                  newEventTown = $j_eme_admin("input#location_town").val(); 
                  if (newEventTown !=eventTown) {
                     loadMap(eventLocation, newEventTown, eventAddress); 
                     eventTown = newEventTown;
                  } 
            });
            $j_eme_admin("input#location_address").blur(function(){
                  newEventAddress = $j_eme_admin("input#location_address").val(); 
                  if (newEventAddress != eventAddress) {
                     loadMap(eventLocation, eventTown, newEventAddress);
                     eventAddress = newEventAddress; 
                  }
            });
            }); 
            $j_eme_admin(document).unload(function() {
            GUnload();
         });
          //]]>
      </script>
<?php
   }
}
$gmap_is_active = get_option('eme_gmap_is_active' );
if ($gmap_is_active) {
   add_action ( 'admin_head', 'eme_admin_map_script' );

}

function eme_rss_link($justurl = 0, $echo = 1, $text = "RSS", $scope="future", $order = "ASC",$category='',$author='',$contact_person='',$limit=5) {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'echo' => 1, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'contact_person' => '', 'limit' => 5 );
      
      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
   }
   if ($text == '')
      $text = "RSS";
   $url = site_url ("/?eme_rss=main&scope=$scope&order=$order&category=$category&author=$author&contact_person=$contact_person&limit=$limit");
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
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'contact_person' => '', 'limit' => 5 ), $atts ) );
   $result = eme_rss_link ( "justurl=$justurl&echo=0&text=$text&limit=$limit&scope=$scope&order=$order&category=$category&author=$author&contact_person=$contact_person" );
   return $result;
}
add_shortcode ( 'events_rss_link', 'eme_rss_link_shortcode' );

function eme_rss() {
   if (isset ( $_GET ['eme_rss'] ) && $_GET ['eme_rss'] == 'main') {
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
      if (isset($_GET['scope'])) {
         $scope=$_GET['scope'];
      } else {
         $scope="future";
      }
      header ( "Content-type: text/xml" );
      echo "<?xml version='1.0'?>\n";
      
      ?>
<rss version="2.0">
<channel>
<title><?php
      echo eme_sanitize_html(get_option('eme_rss_main_title' ));
      ?></title>
<link><?php
      $events_page_link = eme_get_events_page(true, false);
      echo $events_page_link;
      ?></link>
<description><?php
      echo eme_sanitize_html(get_option('eme_rss_main_description' ));
      ?></description>
<docs>
http://blogs.law.harvard.edu/tech/rss
</docs>
<generator>
Weblog Editor 2.0
</generator>
<?php
      $title_format = get_option('eme_rss_title_format' );
      $description_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option('eme_rss_description_format' ) ) );
      $events = eme_get_events ( $limit, $scope, $order, 0, "", $category, $author, $contact_person );
      # some RSS readers don't like it when an empty feed without items is returned, so we add a dummy item then
      if (empty ( $events )) {
         echo "<item>\n";
         echo "<title></title>\n";
         echo "<link></link>\n";
         echo "</item>\n";
      } else {
         foreach ( $events as $event ) {
             $title = eme_replace_placeholders ( $title_format, $event, "rss" );
             $description = eme_replace_placeholders ( $description_format, $event, "rss" );
             $event_link = eme_event_url($event);
             echo "<item>\n";
             echo "<title>$title</title>\n";
             echo "<link>$event_link</link>\n";
             if (get_option('eme_rss_show_pubdate' ))
                echo "<pubDate>".date_i18n ('D, d M Y H:i:s +0000', strtotime($event['modif_date_gmt']))."</pubDate>\n";
             echo "<description>$description</description>\n";
             if (get_option('eme_categories_enabled')) {
                $categories = eme_replace_placeholders ( "#_CATEGORIES", $event, "rss" );
                echo "<category>$categories</category>\n";
             }
             echo "</item>\n";
         }
      }
      ?>

</channel>
</rss>

<?php
      die ();
   }
}
add_action ( 'init', 'eme_rss' );
function eme_general_head() {
   global $wp_query;
   if (eme_is_single_event_page()) {
      $event=eme_get_event($wp_query->query_vars ['event_id']);
      // I don't know if the canonical rel-link is needed, but since WP adds it by default ...
      $canon_url=eme_event_url($event);
      echo "<link rel=\"canonical\" href=\"$canon_url\" />\n";
      $extra_headers_format=get_option('eme_event_html_headers_format');
      if ($extra_headers_format != "") {
         $extra_headers_lines = explode ("\n",$extra_headers_format);
         foreach ($extra_headers_lines as $extra_header_format) {
            # the RSS format already removes most of html code, so let's use that
            $extra_header = eme_replace_placeholders ($extra_header_format, $event, "rss" );
            # the RSS format converts \n to \r\n but we want one line only
            $extra_header = trim(preg_replace('/\r\n/', "", $extra_header));
            if ($extra_header != "")
               echo $extra_header."\n";
         }
      }
   } elseif (eme_is_single_location_page()) {
      $location=eme_get_location($wp_query->query_vars ['location_id']);
      $canon_url=eme_location_url($location);
      echo "<link rel=\"canonical\" href=\"$canon_url\" />\n";
      $extra_headers_format=get_option('eme_location_html_headers_format');
      if ($extra_headers_format != "") {
         $extra_headers_lines = explode ("\n",$extra_headers_format);
         foreach ($extra_headers_lines as $extra_header_format) {
            # the RSS format already removes most of html code, so let's use that
            $extra_header = eme_replace_locations_placeholders ($extra_header_format, $location, "rss" );
            # the RSS format converts \n to \r\n but we want one line only
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
   if ($load_js_in_header) {
      eme_ajaxize_calendar();
   }
}
add_action ( 'wp_head', 'eme_general_head' );

function eme_change_canonical_url() {
   if (eme_is_single_event_page() || eme_is_single_location_page()) {
      remove_action( 'wp_head', 'rel_canonical' );
   }
}
add_action( 'template_redirect', 'eme_change_canonical_url' );


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
add_action('wp_print_styles','eme_general_css');

function eme_general_footer() {
   global $eme_need_gmap_js;
   $gmap_is_active = get_option('eme_gmap_is_active' );
   $load_js_in_header = get_option('eme_load_js_in_header' );
   // we only include the map js if wanted/needed
   if (!$load_js_in_header && $gmap_is_active && $eme_need_gmap_js) {
      echo "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_location_map.js'></script>\n";
   }
}
add_action('wp_footer', 'eme_general_footer');

function eme_db_insert_event($event,$event_is_part_of_recurrence=0) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;

   $event['creation_date']=current_time('mysql', false);
   $event['modif_date']=current_time('mysql', false);
   $event['creation_date_gmt']=current_time('mysql', true);
   $event['modif_date_gmt']=current_time('mysql', true);

   // some sanity checks
   if (isset($event['event_id'])) {
      unset($event['event_id']);
   }
   if ($event['event_end_date']<$event['event_start_date']) {
      $event['event_end_date']=$event['event_start_date'];
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
   if (!$wpdb->insert ( $table_name, $event )) {
      $wpdb->print_error();
      return false;
   } else {
      $event_ID = $wpdb->insert_id;
      $event['event_id']=$event_ID;
      // the eme_insert_event_action is only executed for single events, not those part of a recurrence
      if (!$event_is_part_of_recurrence && has_action('eme_insert_event_action')) do_action('eme_insert_event_action',$event);
      eme_upload_event_picture($event);
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
      eme_upload_event_picture($event);
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
   $actions ['admin.php?page=eme-new_event'] = array (__ ( 'Add an event', 'eme' ), get_option('eme_cap_add_event') );
   return $actions;
}

function eme_alert_events_page() {
   global $pagenow;
   $events_page_id = get_option('eme_events_page' );
   if ($pagenow == 'post.php' && ( get_query_var('post_type') && 'page' == get_query_var('post_type') ) && isset ( $_GET ['action'] ) && $_GET ['action'] == 'edit' && isset ( $_GET ['post'] ) && $_GET ['post'] == "$events_page_id") {
      $message = sprintf ( __ ( "This page corresponds to <strong>Events Made Easy</strong> events page. Its content will be overriden by <strong>Events Made Easy</strong>. If you want to display your content, you can can assign another page to <strong>Events Made Easy</strong> in the the <a href='%s'>Settings</a>. ", 'eme' ), 'admin.php?page=eme-options' );
      $notice = "<div class='error'><p>$message</p></div>";
      echo $notice;
   }
}
add_action ( 'admin_notices', 'eme_alert_events_page' );

//This adds the tinymce editor
function eme_tinymce(){
   global $plugin_page;
   if ( in_array( $plugin_page, array('eme-locations', 'eme-new_event', 'events-manager') ) ) {
      add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );
      if (function_exists('wp_tiny_mce_preload_dialogs')) {
         add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );
      }
      wp_enqueue_script('post');
      if ( user_can_richedit() )
         wp_enqueue_script('editor');
      add_thickbox();
      wp_enqueue_script('media-upload');
      wp_enqueue_script('word-count');
      wp_enqueue_script('quicktags');  
   }
}
add_action ( 'admin_init', 'eme_tinymce' );

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
   $end_date=$event['event_start_date'];
   return eme_daydifference($now,$end_date);
}
add_shortcode('events_countdown', 'eme_countdown');

function eme_image_url_for_event($event) {
   if ($event['recurrence_id']>0) {
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

function eme_upload_event_picture($event) {
   if(!file_exists(IMAGE_UPLOAD_DIR))
            mkdir(IMAGE_UPLOAD_DIR, 0777);
   if ($event['recurrence_id']>0)
      $image_basename= IMAGE_UPLOAD_DIR."/recurrence-".$event['recurrence_id'];
   else
      $image_basename= IMAGE_UPLOAD_DIR."/event-".$event['event_id'];
   if (isset ($_POST['eme_remove_old_image']) && ($_POST['eme_remove_old_image']==1))
      eme_delete_image_files($image_basename);
   $mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
   if (isset($_FILES['event_image']) && isset($_FILES['event_image']['tmp_name']) && ($_FILES['event_image']['size'] > 0)) {
      list($width, $height, $type, $attr) = getimagesize($_FILES['event_image']['tmp_name']);
      $image_path = $image_basename.".".$mime_types[$type];
      if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $image_path))
         $msg = "<p>".__('The image could not be loaded','eme')."</p>";
   }
}

?>
