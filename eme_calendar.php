<?php
function eme_get_calendar_shortcode($atts) { 
   extract(shortcode_atts(array(
         'category' => 0,
         'notcategory' => 0,
         'full' => 0,
         'month' => '',
         'year' => '',
         'echo' => 0,
         'long_events' => 0,
         'author' => '',
         'contact_person' => '',
         'location_id' => ''
      ), $atts)); 
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $full = ($full==="true" || $full==="1") ? true : $full;
   $long_events = ($long_events==="true" || $long_events==="1") ? true : $long_events;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $full = ($full==="false" || $full==="0") ? false : $full;
   $long_events = ($long_events==="false" || $long_events==="0") ? false : $long_events;

   // this allows people to use specific months/years to show the calendar on
   if(isset($_GET['calmonth']) && $_GET['calmonth'] != '')   {
      $month =  eme_sanitize_request($_GET['calmonth']) ;
   }
   if(isset($_GET['calyear']) && $_GET['calyear'] != '')   {
      $year =  eme_sanitize_request($_GET['calyear']) ;
   }

   // the filter list overrides the settings
   if (isset($_REQUEST['eme_eventAction']) && $_REQUEST['eme_eventAction'] == 'filter') {
      if (isset($_REQUEST['eme_scope_filter'])) {
         $scope = eme_sanitize_request($_REQUEST['eme_scope_filter']);
         if (preg_match ( "/^([0-9]{4})-([0-9]{2})-[0-9]{2}--[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $scope, $matches )) {
            $year=$matches[1];
            $month=$matches[2];
         }
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

   $result = eme_get_calendar("full={$full}&month={$month}&year={$year}&echo={$echo}&long_events={$long_events}&category={$category}&author={$author}&contact_person={$contact_person}&location_id={$location_id}&notcategory={$notcategory}");
   return $result;
}

function eme_get_calendar($args="") {
   global $wp_locale;
   global $wpdb;
   // the calendar is being used, so we need the jquery for the calendar
   global $eme_need_calendar_js;
   $eme_need_calendar_js=1;

   $defaults = array(
      'category' => 0,
      'notcategory' => 0,
      'full' => 0,
      'month' => '',
      'year' => '',
      'echo' => 1,
      'long_events' => 0,
      'author' => '',
      'contact_person' => '',
      'location_id' => ''
   );
   $r = wp_parse_args( $args, $defaults );
   extract( $r );
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $full = ($full==="true" || $full==="1") ? true : $full;
   $long_events = ($long_events==="true" || $long_events==="1") ? true : $long_events;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $full = ($full==="false" || $full==="0") ? false : $full;
   $long_events = ($long_events==="false" || $long_events==="0") ? false : $long_events;
   
   // this comes from global wordpress preferences
   $start_of_week = get_option('start_of_week');

   if (get_option('eme_use_client_clock')) {
      // these come from client unless their clock is wrong
      $curr_day= (int) $_SESSION['eme_client_mday'];
      $curr_month= (int) $_SESSION['eme_client_month'];
      $curr_year= (int) $_SESSION['eme_client_fullyear'];
   } else {
      $curr_day= (int) date('j');
      $curr_month= (int) date('m');
      $curr_year= (int) date('Y');
   }
   if ($month == '') $month = $curr_month;
   if ($year == '') $year = $curr_year;
   // let's get the day of the month, based on choosen month/year AND current day
   $date = mktime(0,0,0, (int) $month, (int) $curr_day, (int) $year); 
   $day = date('d', $date); 

   // Get friendly month name
   if ($full) {
      $month_name = date_i18n('F', strtotime("$year-$month-$day"));
   } else {
      $month_name = date_i18n('M', strtotime("$year-$month-$day"));
   }

   // take into account some locale info: some always best show full month name, some show month after year, some have a year suffix
   $locale_code = substr ( get_locale (), 0, 2 );
   $showMonthAfterYear=0;
   $yearSuffix="";
   switch($locale_code) { 
      case "hu": $showMonthAfterYear=1;break;
      case "ja": $showMonthAfterYear=1;$month_name = date_i18n('F', strtotime("$year-$month-$day"));$yearSuffix="年";break;
      case "ko": $showMonthAfterYear=1;$month_name = date_i18n('F', strtotime("$year-$month-$day"));$yearSuffix="년";break;
      case "zh": $showMonthAfterYear=1;$month_name = date_i18n('F', strtotime("$year-$month-$day"));$yearSuffix="年";break;
   }
   if ($showMonthAfterYear)
         $cal_datestring="$year$yearSuffix $month_name";
   else
         $cal_datestring="$month_name $year$yearSuffix";

   // Get the first day of the month 
   $month_start = mktime(0,0,0, (int) $month, 1, (int) $year);
   // Determine day of the week the month starts on.
   $month_start_day = date('D', $month_start);

   switch($month_start_day){ 
      case "Sun": $offset = 0; break; 
      case "Mon": $offset = 1; break; 
      case "Tue": $offset = 2; break; 
      case "Wed": $offset = 3; break; 
      case "Thu": $offset = 4; break; 
      case "Fri": $offset = 5; break; 
      case "Sat": $offset = 6; break;
   }

   $offset -= $start_of_week;
   if($offset<0)
      $offset += 7;
   
   // determine how many days are in the last month. 
   if($month == 1) { 
      $num_days_last = eme_days_in_month(12, ($year -1)); 
   } else { 
     $num_days_last = eme_days_in_month(($month-1), $year); 
   }
   // determine how many days are in the current month. 
   $num_days_current = eme_days_in_month($month, $year);
   // Build an array for the current days 
   // in the month 
   for($i = 1; $i <= $num_days_current; $i++){ 
      $num_days_array[] = $i; 
   }
   // Build an array for the number of days 
   // in last month 
   for($i = 1; $i <= $num_days_last; $i++){ 
       $num_days_last_array[] = $i; 
   }
   // If the $offset from the starting day of the 
   // week happens to be Sunday, $offset would be 0, 
   // so don't need an offset correction. 

   if($offset > 0){ 
       $offset_correction = array_slice($num_days_last_array, -$offset, $offset); 
       $new_count = array_merge($offset_correction, $num_days_array); 
       $offset_count = count($offset_correction); 
   } 

   // The else statement is to prevent building the $offset array. 
   else { 
       $offset_count = 0; 
       $new_count = $num_days_array;
   }
   // count how many days we have with the two 
   // previous arrays merged together 
   $current_num = count($new_count); 

   // Since we will have 5 HTML table rows (TR) 
   // with 7 table data entries (TD) 
   // we need to fill in 35 TDs 
   // so, we will have to figure out 
   // how many days to appened to the end 
   // of the final array to make it 35 days. 

   if($current_num > 35){ 
      $num_weeks = 6; 
      $outset = (42 - $current_num); 
   } elseif($current_num < 35){ 
      $num_weeks = 5; 
      $outset = (35 - $current_num); 
   } 
   if($current_num == 35){ 
      $num_weeks = 5; 
      $outset = 0; 
   } 
   // Outset Correction 
   for($i = 1; $i <= $outset; $i++){ 
      $new_count[] = $i; 
   }
   // Now let's "chunk" the $all_days array 
   // into weeks. Each week has 7 days 
   // so we will array_chunk it into 7 days. 
   $weeks = array_chunk($new_count, 7); 

   // the real links are created via jquery when clicking on the prev-month or next-month class-links
   $previous_link = "<a class='prev-month' href=\"#\">&lt;&lt;</a>"; 
   $next_link = "<a class='next-month' href=\"#\">&gt;&gt;</a>";

   $random = (rand(100,200));
   $full ? $class = 'eme-calendar-full' : $class='eme-calendar';
   $calendar="<div class='$class' id='eme-calendar-$random'>";
   
   $weekdays = array(__('Sunday'),__('Monday'),__('Tuesday'),__('Wednesday'),__('Thursday'),__('Friday'),__('Saturday'));
   $n = 0 ;
   while( $n < $start_of_week ) {
      $last_day = array_shift($weekdays);
      $weekdays[]= $last_day; 
      $n++;
   }

   $days_initials = "";
   foreach($weekdays as $weekday) {
      if ($full)
         $days_initials .= "<td>".$wp_locale->get_weekday_abbrev($weekday)."</td>";
      else
         $days_initials .= "<td>".$wp_locale->get_weekday_initial($weekday)."</td>";
   } 

   if ($full) {
      $fullclass = 'fullcalendar';
      $head = "<td class='month_name' colspan='7'>$previous_link $next_link $cal_datestring</td>\n";
   } else {
      $fullclass='';
      $head = "<td>$previous_link</td><td class='month_name' colspan='5'>$cal_datestring</td><td>$next_link</td>\n";
   }
   // Build the heading portion of the calendar table
   $calendar .=  "<table class='eme-calendar-table $fullclass'>\n".
                 "<thead>\n<tr>\n".$head."</tr>\n</thead>\n".
                 "<tr class='days-names'>\n".$days_initials."</tr>\n";

   // Now we break each key of the array
   // into a week and create a new table row for each 
   // week with the days of that week in the table data 

   $i = 0; 
   foreach ($weeks as $week) { 
      $calendar .= "<tr>\n"; 
      foreach ($week as $d) { 
         if ($i < $offset_count) { //if it is PREVIOUS month
            $t_month = date('m',strtotime("previous month"));
            $t_year = date('Y',strtotime("previous month"));
            $text_dayofweek=date('D', strtotime("$t_year-$t_month-$d"));
            $calendar .= "<td class='$text_dayofweek eventless-pre'>$d</td>\n"; 
         }
         if (($i >= $offset_count) && ($i < ($num_weeks * 7) - $outset)) {
            // if it is THIS month
            $text_dayofweek=date('D', strtotime("$year-$month-$d"));
            if($d == $curr_day && $month == $curr_month && $year == $curr_year) {
               $calendar .= "<td class='$text_dayofweek eventless-today'>$d</td>\n"; 
            } else { 
               $calendar .= "<td class='$text_dayofweek eventless'>$d</td>\n"; 
            } 
         } elseif(($outset > 0)) {
            //if it is NEXT month
            $t_month = date('m',strtotime("next month"));
            $t_year = date('Y',strtotime("next month"));
            $text_dayofweek=date('D', strtotime("$t_year-$t_month-$d"));
            if(($i >= ($num_weeks * 7) - $outset)) { 
               $calendar .= "<td class='$text_dayofweek eventless-post'>$d</td>\n"; 
            } 
         } 
         $i++; 
      } 
      $calendar .= "</tr>\n";
   } 
   
   $calendar .= " </table>\n</div>";

   // calc prev/next month/year
   if ($month == 1) {
      $month_pre=12;
      $month_post=2;
      $year_pre=$year-1;
      $year_post=$year;
   } elseif($month == 12) {
      $month_pre=11;
      $month_post=1;
      $year_pre=$year;
      $year_post=$year+1;
   } else {
      $month_pre=$month-1;
      $month_post=$month+1;
      $year_pre=$year;
      $year_post=$year;
   }

   // we generate the onclick javascript per calendar div
   // this is important if more than one calendar exists on the page
   $calendar .= "<script type='text/javascript'>
         \$j_eme_calendar=jQuery.noConflict();
         \$j_eme_calendar('#eme-calendar-".$random." a.prev-month').click(function(e){
            e.preventDefault();
            tableDiv = \$j_eme_calendar('#eme-calendar-".$random."');
            loadCalendar(tableDiv, '$full', '$long_events','$month_pre','$year_pre','$category','$author','$contact_person','$location_id','$notcategory');
         } );
         \$j_eme_calendar('#eme-calendar-".$random." a.next-month').click(function(e){
            e.preventDefault();
            tableDiv = \$j_eme_calendar('#eme-calendar-".$random."');
            loadCalendar(tableDiv, '$full', '$long_events','$month_post','$year_post','$category','$author','$contact_person','$location_id','$notcategory');
         } );
         </script>";
   
   // we'll look for events in the requested month and 7 days before and after
   $number_of_days_pre=eme_days_in_month($month_pre, $year_pre);
   $limit_pre=date("Y-m-d", mktime(0,0,0, (int) $month_pre, $number_of_days_pre-7 , (int) $year_pre));
   $limit_post=date("Y-m-d", mktime(0,0,0, (int) $month_post, 7 , (int) $year_post));
   $events = eme_get_events(0, "$limit_pre--$limit_post", "ASC", 0, $location_id, $category , $author , $contact_person, 1, $notcategory );


//----- DEBUG ------------
//foreach($events as $event) { //DEBUG
// $calendar .= ("$event->event_day / $event->event_month_n - $event->event_name<br />");
//}
// ------------------

   $eventful_days= array();
   if($events){   
      //Go through the events and slot them into the right d-m index
      foreach($events as $event) {
         if ($event ['event_status'] == STATUS_PRIVATE && !is_user_logged_in()) {
            continue;
         }

         if( $long_events ) {
            //If $long_events is set then show a date as eventful if there is an multi-day event which runs during that day
            $event_start_date = strtotime($event['event_start_date']);
            $event_end_date = strtotime($event['event_end_date']);
            if ($event_end_date < $event_start_date)
               $event_end_date=$event_start_date;
            while( $event_start_date <= $event_end_date ) {
               $event_eventful_date = date('Y-m-d', $event_start_date);
               //Only show events on the day that they start
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
      }
   }

   $event_format = get_option('eme_full_calendar_event_format'); 
   $event_title_format = get_option('eme_small_calendar_event_title_format');
   $event_title_separator_format = get_option('eme_small_calendar_event_title_separator');
   $cells = array() ;
   foreach($eventful_days as $day_key => $events) {
      // Set the date into the key
      $event_date = explode('-', $day_key);
      $cells[$day_key]['day'] = ltrim($event_date[2],'0');
      $cells[$day_key]['month'] = $event_date[1];
      $cells[$day_key]['year'] = $event_date[0];
      $events_titles = array();
      foreach($events as $event) { 
         $events_titles[] = eme_replace_placeholders($event_title_format, $event);
      }
      $link_title = implode($event_title_separator_format,$events_titles);
      
      $cal_day_link = eme_calendar_day_url($day_key);
      // Let's add the possible options
      if (!empty($location_id))
         $cal_day_link = add_query_arg( array( 'location_id' => $location_id ), $cal_day_link );
      if (!empty($category))
         $cal_day_link = add_query_arg( array( 'category' => $category ), $cal_day_link );
      if (!empty($notcategory))
         $cal_day_link = add_query_arg( array( 'notcategory' => $scope ), $cal_day_link );
      if (!empty($author))
         $cal_day_link = add_query_arg( array( 'author' => $author ), $cal_day_link );
      if (!empty($contact_person))
         $cal_day_link = add_query_arg( array( 'contact_person' => $contact_person ), $cal_day_link );

      $cells[$day_key]['cell'] = "<a title='$link_title' href='$cal_day_link'>{$cells[$day_key]['day']}</a>";
      if ($full) {
         $cells[$day_key]['cell'] .= "<ul class='eme-calendar-day-event'>";
      
         foreach($events as $event) {
            $cells[$day_key]['cell'] .= eme_replace_placeholders($event_format, $event);
         } 
         $cells[$day_key]['cell'] .= "</ul>";
         }
   }

   // The calendar html is now generated, now replace the eventless days with eventfull when needed
   // preg_replace is needed, because the first class for each day is the weekday, and we want to keep that info
   if($events){
      foreach($cells as $cell) {
         if ($cell['month'] == $month_pre && $cell['year'] == $year_pre) {
            $calendar=preg_replace("/<td class='(...) eventless-pre'>".$cell['day']."<\/td>/","<td class='$1 eventful-pre event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
         } elseif ($cell['month'] == $month_post && $cell['year'] == $year_post) {
            $calendar=preg_replace("/<td class='(...) eventless-post'>".$cell['day']."<\/td>/","<td class='$1 eventful-post event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
         } elseif ($cell['day'] == $day && $cell['month'] == $month && $day == $curr_day && $month == $curr_month) {
            $calendar=preg_replace("/<td class='(...) eventless-today'>".$cell['day']."<\/td>/","<td class='$1 eventful-today event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
         } elseif ($cell['month'] == $month && $cell['year'] == $year) {
            $calendar=preg_replace("/<td class='(...) eventless'>".$cell['day']."<\/td>/","<td class='$1 eventful event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
            }
      }
   }

   $output=$calendar;
   if ($echo)
      echo $output; 
   else
      return $output;
}

function eme_days_in_month($month, $year) {
   return (date("t",mktime(0,0,0, (int) $month, 1, (int) $year)));
}

function eme_ajaxize_calendar() {
   global $eme_need_calendar_js;

   if (function_exists('qtrans_getLanguage')) {
      // if permalinks are on, $_GET doesn't contain lang as a parameter
      // so we get it like this to be sure
      $language=qtrans_getLanguage();
      $jquery_override_lang=", lang: '".$language."'";
   } elseif (defined('ICL_LANGUAGE_CODE')) {
      // if permalinks are on, $_GET doesn't contain lang as a parameter
      // so we get it like this to be sure
      $language=ICL_LANGUAGE_CODE;
      $jquery_override_lang=", lang: '".$language."'";
   } elseif (isset($_GET['lang'])) {
      $jquery_override_lang=", lang: '".$_GET['lang']."'";
   } else {
      $jquery_override_lang="";
   }
   $load_js_in_header = get_option('eme_load_js_in_header' );
   # make sure we don't load the JS 2 times: if the option load_js_in_header
   # is set, we always load in the header and don't care about eme_need_calendar_js
   if ($load_js_in_header) {
      $eme_need_calendar_js=0;
   }
   if ($eme_need_calendar_js || $load_js_in_header) {
?>
   <script type='text/javascript'>
      $j_eme_calendar=jQuery.noConflict();

      function loadCalendar(tableDiv, fullcalendar, showlong_events, month, year, cat_chosen, author_chosen, contact_person_chosen, location_chosen, not_cat_chosen) {
         if (fullcalendar === undefined) {
             fullcalendar = 0;
         }
         if (showlong_events === undefined) {
             showlong_events = 0;
         }
         fullcalendar = (typeof fullcalendar == 'undefined')? 0 : fullcalendar;
         showlong_events = (typeof showlong_events == 'undefined')? 0 : showlong_events;
         month = (typeof month == 'undefined')? 0 : month;
         year = (typeof year == 'undefined')? 0 : year;
         cat_chosen = (typeof cat_chosen == 'undefined')? '' : cat_chosen;
         not_cat_chosen = (typeof not_cat_chosen == 'undefined')? '' : not_cat_chosen;
         author_chosen = (typeof author_chosen == 'undefined')? '' : author_chosen;
         contact_person_chosen = (typeof contact_person_chosen == 'undefined')? '' : contact_person_chosen;
         location_chosen = (typeof location_chosen == 'undefined')? '' : location_chosen;
         $j_eme_calendar.post(self.location.href, {
            eme_ajaxCalendar: 'true',
            calmonth: parseInt(month,10),
            calyear: parseInt(year,10),
            full : fullcalendar,
            long_events: showlong_events,
            category: cat_chosen,
            notcategory: not_cat_chosen,
            author: author_chosen,
            contact_person: contact_person_chosen,
            location_id: location_chosen <?php echo $jquery_override_lang; ?>
         }, function(data){
            tableDiv.replaceWith(data);
         });
      }
   </script>
   
<?php
   }
}
add_action('wp_footer', 'eme_ajaxize_calendar');

function eme_filter_calendar_ajax() {
   if(isset($_POST['eme_ajaxCalendar']) && $_POST['eme_ajaxCalendar'] == true) {
      (isset($_POST['full']) && $_POST['full']) ? $full = 1 : $full = 0;
      (isset($_POST['long_events']) && $_POST['long_events']) ? $long_events = 1 : $long_events = 0;
      (isset($_POST['category'])) ? $category = $_POST['category'] : $category = 0;
      (isset($_POST['notcategory'])) ? $notcategory = $_POST['notcategory'] : $notcategory = 0;
      (isset($_POST['calmonth'])) ? $month = eme_sanitize_request($_POST['calmonth']) : $month = ''; 
      (isset($_POST['calyear'])) ? $year = eme_sanitize_request($_POST['calyear']) : $year = ''; 
      (isset($_POST['author'])) ? $author = eme_sanitize_request($_POST['author']) : $author = ''; 
      (isset($_POST['contact_person'])) ? $contact_person = eme_sanitize_request($_POST['contact_person']) : $contact_person = ''; 
      (isset($_POST['location_id'])) ? $location_id = eme_sanitize_request($_POST['location_id']) : $location_id = '';

      // make sure we use the correct charset in the return
      header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

      // prevent caching
      header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
      header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
      header("Cache-Control: no-store, no-cache, must-revalidate");
      header("Cache-Control: post-check=0, pre-check=0", false);
      header("Pragma: no-cache");

      eme_get_calendar('echo=1&full='.$full.'&long_events='.$long_events.'&category='.$category.'&month='.$month.'&year='.$year.'&author='.$author.'&contact_person='.$contact_person.'&location_id='.$location_id.'&notcategory='.$notcategory);
      die();
   }
}
add_action('init','eme_filter_calendar_ajax');

?>
