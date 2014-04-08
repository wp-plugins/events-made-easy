<?php

function eme_get_recurrence_days($recurrence){

   $matching_days = array(); 
   
   if($recurrence['recurrence_freq'] == 'specific') {
   	$specific_days = explode(",", $recurrence['recurrence_specific_days']);
	foreach ($specific_days as $day) {
		$date = mktime(0, 0, 0, substr($day,5,2), substr($day,8,2), substr($day,0,4));
            	array_push($matching_days, $date);
	}
	return $matching_days;
   }
 
   $start_date = mktime(0, 0, 0, substr($recurrence['recurrence_start_date'],5,2), substr($recurrence['recurrence_start_date'],8,2), substr($recurrence['recurrence_start_date'],0,4));
   $end_date = mktime(0, 0, 0, substr($recurrence['recurrence_end_date'],5,2), substr($recurrence['recurrence_end_date'],8,2), substr($recurrence['recurrence_end_date'],0,4));
 
// $every_keys = array('every' => 1, 'every_second' => 2, 'every_third' => 3, 'every_fourth' => 4);
// $every_N = $every_keys[$recurrence['recurrence_modifier']]; 
   
// $month_position_keys = array('first_of_month'=>1, 'second_of_month' => 2, 'third_of_month' => 3, 'fourth_of_month' => 4);
// $month_position = $month_position_keys[$recurrence['recurrence_modifier']]; 
   
   $last_week_start = array(25, 22, 25, 24, 25, 24, 25, 25, 24, 25, 24, 25);
   
   $weekdays = explode(",", $recurrence['recurrence_byday']);
   
   $counter = 0;
   $daycounter = 0;
   $weekcounter = 0;
   $monthcounter=0;
   $start_monthday = date("j", $start_date);
   $cycle_date = $start_date;
   $aDay = 86400;  // a day in seconds

   while (date("d-M-Y", $cycle_date) != date('d-M-Y', $end_date + $aDay)) {
    //echo (date("d-M-Y", $cycle_date));
      $style = "";
      $monthweek =  floor(((date("d", $cycle_date)-1)/7))+1;
       if($recurrence['recurrence_freq'] == 'daily') {
         if($daycounter % $recurrence['recurrence_interval']== 0)
            array_push($matching_days, $cycle_date);
      }

      if($recurrence['recurrence_freq'] == 'weekly') {
         if (!$recurrence['recurrence_byday'] && eme_iso_N_date_value($cycle_date)==eme_iso_N_date_value($start_date)) {
         // no specific days given, so we use 7 days as interval
            //if($daycounter % 7*$recurrence['recurrence_interval'] == 0 ) {
            if($weekcounter % $recurrence['recurrence_interval'] == 0 )
               array_push($matching_days, $cycle_date);
         } elseif (in_array(eme_iso_N_date_value($cycle_date), $weekdays )) {
         // specific days, so we only check for those days
            if($weekcounter % $recurrence['recurrence_interval'] == 0 )
               array_push($matching_days, $cycle_date);
         }
      }

      if($recurrence['recurrence_freq'] == 'monthly') { 
         $monthday = date("j", $cycle_date); 
         $month = date("n", $cycle_date);
         // if recurrence_byweekno=none ==> means to use the startday as repeating day
         if ( $recurrence['recurrence_byweekno'] == 'none') {
            if ($monthday == $start_monthday) {
               if ($monthcounter % $recurrence['recurrence_interval'] == 0)
                  array_push($matching_days, $cycle_date);
               $counter++;
            }
         } elseif (in_array(eme_iso_N_date_value($cycle_date), $weekdays )) {
               if(($recurrence['recurrence_byweekno'] == -1) && ($monthday >= $last_week_start[$month-1])) {
               if ($monthcounter % $recurrence['recurrence_interval'] == 0)
                  array_push($matching_days, $cycle_date);
            } elseif($recurrence['recurrence_byweekno'] == $monthweek) {
               if ($monthcounter % $recurrence['recurrence_interval'] == 0)
                  array_push($matching_days, $cycle_date);
            }
            $counter++;
         }
      }
      $cycle_date = $cycle_date + $aDay;         //adding a day
      $daycounter++;
      if ($daycounter%7==0) {
         $weekcounter++;
      }
      if (date("j",$cycle_date)==1) {
         $monthcounter++;
      }
   }
   
   return $matching_days ;
}



///////////////////////////////////////////////

// backwards compatible: eme_insert_recurrent_event renamed to eme_db_insert_recurrence
function eme_insert_recurrent_event($event, $recurrence) {
   return eme_db_insert_recurrence($event, $recurrence);
}

function eme_db_insert_recurrence($event, $recurrence ){
   global $wpdb;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
      
   $recurrence['creation_date']=current_time('mysql', false);
   $recurrence['modif_date']=current_time('mysql', false);
   $recurrence['creation_date_gmt']=current_time('mysql', true);
   $recurrence['modif_date_gmt']=current_time('mysql', true);
   // never try to update a autoincrement value ...
   if (isset($recurrence['recurrence_id']))
      unset ($recurrence['recurrence_id']);

   // some sanity checks
   if ($recurrence['recurrence_freq'] != "specific") {
      $startstring=strtotime($recurrence['recurrence_start_date']);
      $endstring=strtotime($recurrence['recurrence_end_date']);
      if ($endstring<$startstring) {
         $recurrence['recurrence_end_date']=$recurrence['recurrence_start_date'];
      }
   }

   //$wpdb->show_errors(true);
   $wpdb->insert($recurrence_table, $recurrence);
   $recurrence_id = $wpdb->insert_id;

   //print_r($recurrence);

   $recurrence['recurrence_id'] = $recurrence_id;
   $event['recurrence_id'] = $recurrence['recurrence_id'];
   eme_insert_events_for_recurrence($event,$recurrence);
   if (has_action('eme_insert_recurrence_action')) do_action('eme_insert_recurrence_action',$event,$recurrence);
   return $recurrence_id;
}

function eme_insert_events_for_recurrence($event,$recurrence) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $matching_days = eme_get_recurrence_days($recurrence);
//   print_r($matching_days);
   sort($matching_days);

   if ($event['event_end_date']=='') {
      $duration_days_event = 0;
   } else {
      $duration_days_event = abs(eme_daydifference($event['event_start_date'],$event['event_end_date']));
   }
   foreach($matching_days as $day) {
      $event['event_start_date'] = date("Y-m-d", $day); 
      $event['event_end_date'] = date("Y-m-d", strtotime($event['event_start_date'] ." + $duration_days_event days"));
      eme_db_insert_event($event,1);
   }
}

// backwards compatible: eme_update_recurrence renamed to eme_db_update_recurrence
function eme_update_recurrence($event, $recurrence) {
   return eme_db_update_recurrence($event, $recurrence);
}

function eme_db_update_recurrence($event, $recurrence) {
   global $wpdb;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;

   $recurrence['modif_date']=current_time('mysql', false);
   $recurrence['modif_date_gmt']=current_time('mysql', true);

   // some sanity checks
   $startstring=strtotime($recurrence['recurrence_start_date']);
   $endstring=strtotime($recurrence['recurrence_end_date']);
   if ($endstring<$startstring) {
      $recurrence['recurrence_end_date']=$recurrence['recurrence_start_date'];
   }

   $where = array('recurrence_id' => $recurrence['recurrence_id']);
   $wpdb->show_errors(true);
   $wpdb->update($recurrence_table, $recurrence, $where); 
   $event['recurrence_id'] = $recurrence['recurrence_id'];
   eme_update_events_for_recurrence($event,$recurrence); 
   if (has_action('eme_update_recurrence_action')) do_action('eme_update_recurrence_action',$event,$recurrence);
   return 1;
}

function eme_update_events_for_recurrence($event,$recurrence) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $matching_days = eme_get_recurrence_days($recurrence);
   //print_r($matching_days);  
   sort($matching_days);

   if ($event['event_end_date']=='') {
      $duration_days_event = 0;
   } else {
      $duration_days_event = abs(eme_daydifference($event['event_start_date'],$event['event_end_date']));
   }

   // 2 steps for updating events for a recurrence:
   // First step: check the existing events and if they still match the recurrence days, update them
   //       otherwise delete the old event
   // Reason for doing this: we want to keep possible booking data for a recurrent event as well
   // and just deleting all current events for a recurrence and inserting new ones would break the link
   // between booking id and event id
   // Second step: check all days of the recurrence and if no event exists yet, insert it
   $sql = "SELECT * FROM $events_table WHERE recurrence_id = '".$recurrence['recurrence_id']."';";
   $events = $wpdb->get_results($sql, ARRAY_A);
   // Doing step 1
   foreach($events as $existing_event) {
      $update_needed=0;
      foreach($matching_days as $day) {
         if (!$update_needed && $existing_event['event_start_date'] == date("Y-m-d", $day)) {
            $update_needed=1; 
         }
      }
      if ($update_needed==1) {
         $event['event_start_date'] = $existing_event['event_start_date'];
         $event['event_end_date'] = date("Y-m-d", strtotime($event['event_start_date'] ." + $duration_days_event days")); 
         eme_db_update_event($event, $existing_event['event_id'], 1); 
      } else {
         eme_db_delete_event($existing_event);
      }
   }
   // Doing step 2
   foreach($matching_days as $day) {
      $insert_needed=1;
      $event['event_start_date'] = date("Y-m-d", $day);
      $event['event_end_date'] = date("Y-m-d", strtotime($event['event_start_date'] ." + $duration_days_event days")); 
      foreach($events as $existing_event) {
         if ($insert_needed && $existing_event['event_start_date'] == $event['event_start_date']) {
            $insert_needed=0;
         }
      }
      if ($insert_needed==1) {
         eme_db_insert_event($event,1);
      }
   }
   return 1;
}

function eme_remove_recurrence($recurrence_id) {
   global $wpdb;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
   $sql = "DELETE FROM $recurrence_table WHERE recurrence_id = '$recurrence_id';";
   $wpdb->query($sql);
   eme_remove_events_for_recurrence_id($recurrence_id);
   $image_basename= IMAGE_UPLOAD_DIR."/recurrence-".$recurrence_id;
   eme_delete_image_files($image_basename);
}

function eme_remove_events_for_recurrence_id($recurrence_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "DELETE FROM $events_table WHERE recurrence_id = '$recurrence_id';";
   $wpdb->query($sql);
}

function eme_get_recurrence($recurrence_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
   $sql = "SELECT * FROM $recurrence_table WHERE recurrence_id = $recurrence_id;";
   $recurrence = $wpdb->get_row($sql, ARRAY_A);

   // now add the info that has no column in the recurrence table
   // for that, we take the info from the first occurence
   $sql = "SELECT event_id FROM $events_table WHERE recurrence_id = '$recurrence_id' ORDER BY event_start_date ASC LIMIT 1;";
   $event_id = $wpdb->get_var($sql);
   $event = eme_get_event($event_id);
   foreach ($event as $key=>$val) {
      $recurrence[$key]=$val;
   }

   // now add the location info
   $location = eme_get_location($recurrence['location_id']);
   $recurrence['location_name'] = $location['location_name'];
   $recurrence['location_address'] = $location['location_address'];
   $recurrence['location_town'] = $location['location_town'];
   $recurrence['recurrence_description'] = eme_get_recurrence_desc($recurrence_id);
   return $recurrence;
}

function eme_get_recurrence_desc($recurrence_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
   $sql = "SELECT * FROM $recurrence_table WHERE recurrence_id = $recurrence_id;";
   $recurrence = $wpdb->get_row($sql, ARRAY_A);

   $weekdays_name = array(__('Monday'),__('Tuesday'),__('Wednesday'),__('Thursday'),__('Friday'),__('Saturday'),__('Sunday'));
   $monthweek_name = array('1' => __('the first %s of the month', 'eme'),'2' => __('the second %s of the month', 'eme'), '3' => __('the third %s of the month', 'eme'), '4' => __('the fourth %s of the month', 'eme'), '5' => __('the fifth %s of the month', 'eme'), '-1' => __('the last %s of the month', 'eme'));
   $output = sprintf (__('From %1$s to %2$s', 'eme'),  eme_localised_date($recurrence['recurrence_start_date']), eme_localised_date($recurrence['recurrence_end_date'])).", ";
   if ($recurrence['recurrence_freq'] == 'daily')  {
      $freq_desc =__('everyday', 'eme');
      if ($recurrence['recurrence_interval'] > 1 ) {
         $freq_desc = sprintf (__("every %s days", 'eme'), $recurrence['recurrence_interval']);
      }
   }
   elseif ($recurrence['recurrence_freq'] == 'weekly')  {
      if (!$recurrence['recurrence_byday']) {
         # no weekdays given for the recurrence, so we use the
         # so we use the day of the week of the startdate as
         # reference
         $recurrence['recurrence_byday']=date_i18n('w',strtotime($recurrence['recurrence_start_date']));
         # Sunday is 7, not 0
         if ($recurrence['recurrence_byday']==0)
            $recurrence['recurrence_byday']=7; 
      }
      $weekday_array = explode(",", $recurrence['recurrence_byday']);
      $natural_days = array();
      foreach($weekday_array as $day)
         array_push($natural_days, $weekdays_name[$day-1]);
      $and_string=__(" and ",'eme');
      $output .= implode($and_string, $natural_days);
      $freq_desc =", ".__('every week', 'eme');
      if ($recurrence['recurrence_interval'] > 1 ) {
         $freq_desc = ", ".sprintf (__("every %s weeks", 'eme'), $recurrence['recurrence_interval']);
      }
   } 
   elseif ($recurrence['recurrence_freq'] == 'monthly')  {
      if (!$recurrence['recurrence_byday']) {
         # no monthday given for the recurrence, so we use the
         # so we use the day of the month of the startdate as
         # reference
         $recurrence['recurrence_byday']=date_i18n('e',strtotime($recurrence['recurrence_start_date']));
      }
      $weekday_array = explode(",", $recurrence['recurrence_byday']);
      $natural_days = array();
      foreach($weekday_array as $day)
         array_push($natural_days, $weekdays_name[$day-1]);
      $and_string=__(" and ",'eme');
      $freq_desc = sprintf (($monthweek_name[$recurrence['recurrence_byweekno']]), implode($and_string, $natural_days));
      $freq_desc =", ".__('every month', 'eme');
      if ($recurrence['recurrence_interval'] > 1 ) {
         $freq_desc .= ", ".sprintf (__("every %s months",'eme'), $recurrence['recurrence_interval']);
      }
   } elseif ($recurrence['recurrence_freq'] == 'specific')  {
      return __("Specific days",'eme');
   } else {
      $freq_desc = "";
   }
   $output .= $freq_desc;
   return  $output;
}

function eme_recurrence_count($recurrence_id) {
   # return the number of events for an recurrence
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE recurrence_id='".$recurrence_id."'";
   return $wpdb->get_var($sql);
}

function eme_iso_N_date_value($date) {
   // date("N", $cycle_date)
   $n = date("w", $date);
   if ($n == 0)
      $n = 7;
   return $n;
}
?>
