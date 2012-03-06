<?php

function eme_ical_single_event($event, $title_format, $description_format) {
   $title = eme_replace_placeholders ( $title_format, $event, "rss" );
   // no html tags allowed in ical
   $title = strip_tags($title);
   $description = eme_replace_placeholders ( $description_format, $event, "rss" );
   // no \r\n in description, only escaped \n is allowed
   $description = preg_replace('/\r\n/', "", $description);
   // no html tags allowed in ical, but we can convert br to escaped newlines to maintain readable output
   $description = strip_tags(preg_replace('/<br(\s+)?\/?>/i', "\\n", $description));
   $location = eme_replace_placeholders ( "#_LOCATION, #_ADDRESS, #_TOWN", $event, "rss" );
   $location = strip_tags($location);

   $event_link = eme_event_url($event);
   $startstring=strtotime($event['event_start_date']." ".$event['event_start_time']);
   $dtstartdate=date_i18n("Ymd",$startstring);
   $dtstarthour=date_i18n("His",$startstring);
   //$dtstart=$dtstartdate."T".$dtstarthour."Z";
   // we'll use localtime, so no "Z"
   $dtstart=$dtstartdate."T".$dtstarthour;
   if ($event['event_end_date'] == "")
      $event['event_end_date'] = $event['event_start_date'];
   if ($event['event_end_time'] == "")
      $event['event_end_time'] = $event['event_start_time'];
   $endstring=strtotime($event['event_end_date']." ".$event['event_end_time']);
   $dtenddate=date_i18n("Ymd",$endstring);
   $dtendhour=date_i18n("His",$endstring);
   //$dtend=$dtenddate."T".$dtendhour."Z";
   // we'll use localtime, so no "Z"
   $dtend=$dtenddate."T".$dtendhour;

   $res = "";
   $res .= "BEGIN:VEVENT\r\n";
   //echo "DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z\r\n";
   // we'll use localtime, so no "Z"
   $res .= "DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "\r\n";
   $res .= "DTSTART:$dtstart\r\n";
   $res .= "DTEND:$dtend\r\n";
   $res .= "UID:$dtstart-$dtend-".$event['event_id']."@".$_SERVER['SERVER_NAME']."\r\n";
   $res .= "SUMMARY:$title\r\n";
   $res .= "DESCRIPTION:$description\r\n";
   $res .= "URL:$event_link\r\n";
   $res .= "ATTACH:$event_link\r\n";
   $res .= "LOCATION:$location\r\n";
   $res .= "END:VEVENT\r\n";
   return $res;
}

function eme_ical_link($justurl = 0, $echo = 1, $text = "ICAL", $category = "") {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'echo' => 1, 'text' => 'ICAL', category=> '' );

      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
   }
   if ($text == '')
      $text = "ICAL";
   if (!empty($category)) {
      $url = site_url ("/?eme_ical=public&category=$category");
   } else {
      $url = site_url ("/?eme_ical=public");
   }
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

function eme_ical_link_shortcode($atts) {
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => 'ICAL', 'category' => '' ), $atts ) );
   $result = eme_ical_link ( "justurl=$justurl&echo=0&text=$text&category=$category" );
   return $result;
}
add_shortcode ( 'events_ical_link', 'eme_ical_link_shortcode' );

function eme_ical() {
   if (isset ( $_GET ['eme_ical'] ) && $_GET ['eme_ical'] == 'public_single' && isset ( $_GET ['event_id'] )) {
      header("Content-type: text/calendar; charset=utf-8");
      header("Content-Disposition: inline; filename=eme_single.ics");
   } elseif (isset ( $_GET ['eme_ical'] ) && $_GET ['eme_ical'] == 'public') {
      header("Content-type: text/calendar; charset=utf-8");
      header("Content-Disposition: inline; filename=eme_public.ics");
   } else {
      return;
   }

   // prevent caching
   header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
   header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
   header("Cache-Control: no-store, no-cache, must-revalidate");
   header("Cache-Control: post-check=0, pre-check=0", false);
   header("Pragma: no-cache");

   echo "BEGIN:VCALENDAR\r\n";
   echo "METHOD:PUBLISH\r\n";
   echo "VERSION:2.0\r\n";
   echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
   $title_format = get_option('eme_event_page_title_format' );
   $description_format = get_option('eme_single_event_format');
   if (isset ( $_GET ['eme_ical'] ) && $_GET ['eme_ical'] == 'public_single' && isset ( $_GET ['event_id'] )) {
      $event=eme_get_event(intval($_GET ['event_id']));
      echo eme_ical_single_event($event,$title_format,$description_format);
   } elseif (isset ( $_GET ['eme_ical'] ) && $_GET ['eme_ical'] == 'public') {
      if (isset( $_GET ['category'] )) {
         $events = eme_get_events ( 0,"future","ASC",0,"",$_GET ['category'] );
      } else {
         $events = eme_get_events ( 0 );
      }
      foreach ( $events as $event ) {
         echo eme_ical_single_event($event,$title_format,$description_format);
      }
   }
   echo "END:VCALENDAR\r\n";
   die ();
}
add_action ( 'init', 'eme_ical' );

?>
