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
      $iNowDay= sprintf("%02d",$_SESSION['eme_client_mday']);
      $iNowMonth= sprintf("%02d",$_SESSION['eme_client_month']);
      $iNowYear= sprintf("%04d",$_SESSION['eme_client_fullyear']);
   } else {
	// Get current year, month and day
	list($iNowYear, $iNowMonth, $iNowDay) = explode('-', date('Y-m-d'));
   }

   $iSelectedYear = $year;
   $iSelectedMonth = $month;
   if ($iSelectedMonth == '') $iSelectedMonth = $iNowMonth;
   if ($iSelectedYear == '') $iSelectedYear = $iNowYear;
   $iSelectedMonth = sprintf("%02d",$iSelectedMonth);


   // Get name and number of days of specified month
   $iTimestamp = mktime(0, 0, 0, $iSelectedMonth, 1, $iSelectedYear);
   list($sMonthName, $iDaysInMonth) = explode('-', date('F-t', $iTimestamp));
   // Get friendly month name
   if ($full) {
      list($sMonthName, $iDaysInMonth) = explode('-', date_i18n('F-t', $iTimestamp));
   } else {
      list($sMonthName, $iDaysInMonth) = explode('-', date_i18n('M-t', $iTimestamp));
   }
   // take into account some locale info: some always best show full month name, some show month after year, some have a year suffix
   $locale_code = substr ( get_locale (), 0, 2 );
   $showMonthAfterYear=0;
   $yearSuffix="";
   switch($locale_code) { 
      case "hu": $showMonthAfterYear=1;break;
      case "ja": $showMonthAfterYear=1;$sMonthName = date_i18n('F', $iTimestamp);$yearSuffix="年";break;
      case "ko": $showMonthAfterYear=1;$sMonthName = date_i18n('F', $iTimestamp);$yearSuffix="년";break;
      case "zh": $showMonthAfterYear=1;$sMonthName = date_i18n('F', $iTimestamp);$yearSuffix="年";break;
   }
   if ($showMonthAfterYear)
         $cal_datestring="$iSelectedYear$yearSuffix $sMonthName";
   else
         $cal_datestring="$sMonthName $iSelectedYear$yearSuffix";

   // Get previous year and month
   $iPrevYear = $iSelectedYear;
   $iPrevMonth = $iSelectedMonth - 1;
   if ($iPrevMonth <= 0) {
	   $iPrevYear--;
	   $iPrevMonth = 12; // set to December
   }
   $iPrevMonth = sprintf("%02d",$iPrevMonth);

   // Get next year and month
   $iNextYear = $iSelectedYear;
   $iNextMonth = $iSelectedMonth + 1;
   if ($iNextMonth > 12) {
	   $iNextYear++;
	   $iNextMonth = 1;
   }
   $iNextMonth = sprintf("%02d",$iNextMonth);

   // Get number of days of previous month
   $iPrevDaysInMonth = (int)date('t', mktime(0, 0, 0, $iPrevMonth, $iNowDay, $iPrevYear));

   // Get numeric representation of the day of the week of the first day of specified (current) month
   // remember: first day of week is a Sunday
   // if you want the day of the week to begin on Monday: start_of_week=1, Tuesday: start_of_week=2, etc ...
   // So, if e.g. the month starts on a Sunday and start_of_week=1 (Monday), then $iFirstDayDow is 6
   $iFirstDayDow = (int)date('w', mktime(0, 0, 0, $iSelectedMonth, 1, $iSelectedYear)) - $start_of_week;
   if ($iFirstDayDow<0) $iFirstDayDow+=7;

   // On what day the previous month begins
   $iPrevShowFrom = $iPrevDaysInMonth - $iFirstDayDow + 1;

  // we'll look for events in the requested month and 7 days before and after
   $calbegin="$iPrevYear-$iPrevMonth-$iPrevShowFrom";
   $calend="$iNextYear-$iNextMonth-07";
   $events = eme_get_events(0, "$calbegin--$calend", "ASC", 0, $location_id, $category , $author , $contact_person, 1, $notcategory );

   $eventful_days= array();
   if ($events) {   
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

   // we found all the events for the wanted days, now get them in the correct format with a good link
   $event_format = get_option('eme_full_calendar_event_format'); 
   $event_title_format = get_option('eme_small_calendar_event_title_format');
   $event_title_separator_format = get_option('eme_small_calendar_event_title_separator');
   $cells = array() ;
   foreach ($eventful_days as $day_key => $events) {
      // Set the date into the key
      $events_titles = array();
      foreach($events as $event) { 
         $events_titles[] = eme_replace_placeholders($event_title_format, $event,"text");
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

      $event_date = explode('-', $day_key);
      $event_day = ltrim($event_date[2],'0');
      $cells[$day_key] = "<a title='$link_title' href='$cal_day_link'>$event_day</a>";
      if ($full) {
         $cells[$day_key] .= "<ul class='eme-calendar-day-event'>";
      
         foreach($events as $event) {
            $cells[$day_key] .= eme_replace_placeholders($event_format, $event);
         } 
         $cells[$day_key] .= "</ul>";
      }
   }

   // If previous month
   $isPreviousMonth = ($iFirstDayDow > 0);

   // Initial day on the calendar
   $iCalendarDay = ($isPreviousMonth) ? $iPrevShowFrom : 1;

   $isNextMonth = false;
   $sCalTblRows = '';

   // Generate rows for the calendar
   for ($i = 0; $i < 6; $i++) { // 6-weeks range
	   if ($isNextMonth) continue;
	   $sCalTblRows .= "<tr>";
	   for ($j = 0; $j < 7; $j++) { // 7 days a week

         // we need the calendar day with 2 digits for the planned events
         $iCalendarDay_padded = sprintf("%02d",$iCalendarDay);
         if ($isPreviousMonth) $calstring="$iPrevYear-$iPrevMonth-$iCalendarDay_padded";
         elseif ($isNextMonth) $calstring="$iNextYear-$iNextMonth-$iCalendarDay_padded";
         else $calstring="$iSelectedYear-$iSelectedMonth-$iCalendarDay_padded";

		   // each day in the calendar has the name of the day as a class by default
		   $sClass = date('D', strtotime($calstring));

		   if (isset($cells[$calstring])) {
			   if ($isPreviousMonth)
				   $sClass .= " eventful-pre event-day-$iCalendarDay";
			   elseif ($isNextMonth)
				   $sClass .= " eventful-post event-day-$iCalendarDay";
			   elseif ($calstring == "$iNowYear-$iNowMonth-$iNowDay")
				   $sClass .= " eventful-today event-day-$iCalendarDay";
			   else
				   $sClass .= " eventful event-day-$iCalendarDay";
			   $sCalTblRows .= '<td class="'.$sClass.'">'.$cells[$calstring]. "</td>\n";
		   } else {
			   if ($isPreviousMonth)
				   $sClass .= " eventless-pre";
			   elseif ($isNextMonth)
				   $sClass .= " eventless-post";
			   elseif ($calstring == "$iNowYear-$iNowMonth-$iNowDay")
				   $sClass .= " eventless-today";
			   else
				   $sClass .= " eventless";
			   $sCalTblRows .= '<td class="'.$sClass.'">'.$iCalendarDay. "</td>\n";
		   }

		   // Next day
		   $iCalendarDay++;
		   if ($isPreviousMonth && $iCalendarDay > $iPrevDaysInMonth) {
			   $isPreviousMonth = false;
			   $iCalendarDay = 1;
		   }
		   if (!$isPreviousMonth && !$isNextMonth && $iCalendarDay > $iDaysInMonth) {
			   $isNextMonth = true;
			   $iCalendarDay = 1;
		   }
	   }
	   $sCalTblRows .= "</tr>\n";
   }

   $weekdays = array(__('Sunday'),__('Monday'),__('Tuesday'),__('Wednesday'),__('Thursday'),__('Friday'),__('Saturday'));
   $sCalDayNames="";
   // respect the beginning of the week offset
   for ($i=$start_of_week; $i<$start_of_week+7; $i++) {
	   $j=$i;
	   if ($j==7) $j-=7;
      if ($full)
         $sCalDayNames.= "<td>".$wp_locale->get_weekday_abbrev($weekdays[$j])."</td>";
      else
         $sCalDayNames.= "<td>".$wp_locale->get_weekday_initial($weekdays[$j])."</td>";
   }

   // the real links are created via jquery when clicking on the prev-month or next-month class-links
   $previous_link = "<a class='prev-month' href=\"#\">&lt;&lt;</a>"; 
   $next_link = "<a class='next-month' href=\"#\">&gt;&gt;</a>";

   $random = (rand(100,200));
   $full ? $class = 'eme-calendar-full' : $class='eme-calendar';
   $calendar="<div class='$class' id='eme-calendar-$random'>";
   
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
                 "<tr class='days-names'>\n".$sCalDayNames."</tr>\n";
   $calendar .= $sCalTblRows;
   $calendar .=  "</table>\n</div>";

   // we generate the onclick javascript per calendar div
   // this is important if more than one calendar exists on the page
   $calendar .= "<script type='text/javascript'>
         jQuery('#eme-calendar-".$random." a.prev-month').click(function(e){
            e.preventDefault();
            tableDiv = jQuery('#eme-calendar-".$random."');
            loadCalendar(tableDiv, '$full', '$long_events','$iPrevMonth','$iPrevYear','$category','$author','$contact_person','$location_id','$notcategory');
         } );
         jQuery('#eme-calendar-".$random." a.next-month').click(function(e){
            e.preventDefault();
            tableDiv = jQuery('#eme-calendar-".$random."');
            loadCalendar(tableDiv, '$full', '$long_events','$iNextMonth','$iNextYear','$category','$author','$contact_person','$location_id','$notcategory');
         } );
         </script>";

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

   $language = eme_detect_lang();
   if (!empty($language)) {
      $jquery_override_lang=", lang: '".$language."'";
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
         jQuery.post(self.location.href, {
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

function eme_filter_calendar_ajax() {
   (isset($_POST['full']) && $_POST['full']) ? $full = 1 : $full = 0;
   (isset($_POST['long_events']) && $_POST['long_events']) ? $long_events = 1 : $long_events = 0;
   (isset($_POST['category'])) ? $category = $_POST['category'] : $category = 0;
   (isset($_POST['notcategory'])) ? $notcategory = $_POST['notcategory'] : $notcategory = 0;
   (isset($_POST['calmonth'])) ? $month = eme_sanitize_request($_POST['calmonth']) : $month = ''; 
   (isset($_POST['calyear'])) ? $year = eme_sanitize_request($_POST['calyear']) : $year = ''; 
   (isset($_POST['author'])) ? $author = eme_sanitize_request($_POST['author']) : $author = ''; 
   (isset($_POST['contact_person'])) ? $contact_person = eme_sanitize_request($_POST['contact_person']) : $contact_person = ''; 
   (isset($_POST['location_id'])) ? $location_id = eme_sanitize_request($_POST['location_id']) : $location_id = '';

   eme_get_calendar('echo=1&full='.$full.'&long_events='.$long_events.'&category='.$category.'&month='.$month.'&year='.$year.'&author='.$author.'&contact_person='.$contact_person.'&location_id='.$location_id.'&notcategory='.$notcategory);
}

?>
