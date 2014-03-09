<?php

function eme_sanitize_ical($value, $keep_html=0) {
   $value = preg_replace('/"/', '', $value);
   $value = preg_replace('/\\\\/', '\\\\', $value);
   $value = preg_replace('/\r\n|\n/', '\\n', $value);
   $value = preg_replace('/(;|\,)/', '\\\${1}', $value);
   if (!$keep_html)
      return apply_filters('eme_text', $value);
   else
      return $value;
}

function eme_ical_single_event($event, $title_format, $description_format) {
   $title = eme_sanitize_ical (eme_replace_placeholders ( $title_format, $event, "text" ));
   $description = eme_sanitize_ical (eme_replace_placeholders ( $description_format, $event, "text" ));
   $html_description = eme_sanitize_ical (eme_replace_placeholders ( $description_format, $event, "html" ),1);

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
   $tzstring = get_option('timezone_string');

   $res = "";
   $res .= "BEGIN:VEVENT\r\n";
   //DTSTAMP must be in UTC format, so adding "Z" as well
   $res .= "DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z\r\n";
   if ($event['event_properties']['all_day']) {
      // ical standard for an all day event: specify only the day, meaning
      // an 'all day' event is flagged as starting at the beginning of one day and lasting until the beginning of the next
      // so it is the same as adding "T000000" as time spec to the start/end datestring
      // But since it "ends" at the beginning of the next day, we should add 24 hours, otherwise the event ends one day too soon
      $dtenddate=date_i18n("Ymd",$endstring+86400);
      $res .= "DTSTART;VALUE=DATE:$dtstartdate\r\n";
      $res .= "DTEND;VALUE=DATE:$dtenddate\r\n";
   } else {
      $res .= "DTSTART;TZID=$tzstring:$dtstart\r\n";
      $res .= "DTEND;TZID=$tzstring:$dtend\r\n";
   }
   $res .= "UID:$dtstart-$dtend-".$event['event_id']."@".$_SERVER['SERVER_NAME']."\r\n";
   $res .= "SUMMARY:$title\r\n";
   $res .= "DESCRIPTION:$description\r\n";
   $res .= "X-ALT-DESC;FMTTYPE=text/html:$html_description\r\n";
   $res .= "URL:$event_link\r\n";
   $res .= "ATTACH:$event_link\r\n";
   if ($event['event_image_id']) {
      $thumb_array = image_downsize( $event['event_image_id'], get_option('eme_thumbnail_size') );
      $thumb_url = $thumb_array[0];
      $res .= "ATTACH:$thumb_url\r\n";
   }
   if (isset($event['location_id']) && $event['location_id']) {
      $location = eme_sanitize_ical (eme_replace_placeholders ( "#_LOCATION, #_ADDRESS, #_TOWN", $event, "text" ));
      $res .= "LOCATION:$location\r\n";
   }
   $res .= "END:VEVENT\r\n";
   return $res;
}

#function eme_ical_link($justurl = 0, $echo = 1, $text = "ICAL", $category = "", $location_id="") {
function eme_ical_link($justurl = 0, $echo = 1, $text = "ICAL", $category = "", $location_id="", $scope="future", $author='',$contact_person='', $notcategory = "") {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'echo' => 1, 'text' => 'ICAL', 'scope' => 'future', 'category' => '', 'author' => '', 'contact_person' => '', 'location_id' => '', 'notcategory' => '' );

      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
   }
   $echo = ($echo==="true" || $echo==="1") ? true : $echo;
   $justurl = ($justurl==="true" || $justurl==="1") ? true : $justurl;
   $echo = ($echo==="false" || $echo==="0") ? false : $echo;
   $justurl = ($justurl==="false" || $justurl==="0") ? false : $justurl;

   if ($text == '')
      $text = "ICAL";
   $url = site_url ("/?eme_ical=public");
   if (!empty($location_id))
      $url = add_query_arg( array( 'location_id' => $location_id ), $url );
   if (!empty($category))
      $url = add_query_arg( array( 'category' => $category ), $url );
   if (!empty($notcategory))
      $url = add_query_arg( array( 'notcategory' => $notcategory ), $url );
   if (!empty($scope))
      $url = add_query_arg( array( 'scope' => $scope ), $url );
   if (!empty($author))
      $url = add_query_arg( array( 'author' => $author ), $url );
   if (!empty($contact_person))
      $url = add_query_arg( array( 'contact_person' => $contact_person ), $url );

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
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => 'ICAL', 'category' => '', 'location_id' =>'', 'scope' => 'future', 'author' => '', 'contact_person' => '', 'notcategory' => ''  ), $atts ) );

   $justurl = ($justurl==="true" || $justurl==="1") ? true : $justurl;
   $justurl = ($justurl==="false" || $justurl==="0") ? false : $justurl;
   $result = eme_ical_link ( $justurl,0,$text,$category,$location_id, $scope,$author,$contact_person,$notcategory );
   return $result;
}

function eme_ical_single() {
   echo "BEGIN:VCALENDAR\r\n";
   echo "METHOD:PUBLISH\r\n";
   echo "VERSION:2.0\r\n";
   echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
   $event=eme_get_event(intval($_GET ['event_id']));
   $title_format = get_option('eme_ical_title_format' );
   $description_format = get_option('eme_ical_description_format');
   echo eme_ical_single_event($event,$title_format,$description_format);
   echo "END:VCALENDAR\r\n";
}

function eme_ical() {
   echo "BEGIN:VCALENDAR\r\n";
   echo "METHOD:PUBLISH\r\n";
   echo "VERSION:2.0\r\n";
   echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
   $title_format = get_option('eme_ical_title_format' );
   $description_format = get_option('eme_ical_description_format');
   $location_id = isset( $_GET['location_id'] ) ? urldecode($_GET['location_id']) : '';
   $category = isset( $_GET['category'] ) ? urldecode($_GET['category']) : '';
   $notcategory = isset( $_GET['notcategory'] ) ? urldecode($_GET['notcategory']) : '';
   $scope = isset( $_GET['scope'] ) ? urldecode($_GET['scope']) : '';
   $author = isset( $_GET['author'] ) ? urldecode($_GET['author']) : '';
   $contact_person = isset( $_GET['contact_person'] ) ? urldecode($_GET['contact_person']) : '';
   $events = eme_get_events ( 0,$scope,"ASC",0,$location_id,$category, $author, $contact_person, 1, $notcategory);
   foreach ( $events as $event ) {
      echo eme_ical_single_event($event,$title_format,$description_format);
   }
   echo "END:VCALENDAR\r\n";
}

?>
