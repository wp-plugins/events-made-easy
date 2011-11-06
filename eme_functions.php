<?php

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', 'value' => '', 'notvalue' => '', 'lt' => '', 'gt' => '', 'contains'=>'', 'notcontains'=>'', 'is_empty'=>0 ), $atts ) );
   if ($is_empty) {
      if (empty($tag)) return do_shortcode($content);
   } elseif (is_numeric($value) || !empty($value)) {
      if ($tag==$value) return do_shortcode($content);
   } elseif (is_numeric($notvalue) || !empty($notvalue)) {
      if ($tag!=$notvalue) return do_shortcode($content);
   } elseif (is_numeric($lt) || !empty($lt)) {
      if ($tag<$lt) return do_shortcode($content);
   } elseif (is_numeric($gt) || !empty($gt)) {
      if ($tag>$gt) return do_shortcode($content);
   } elseif (is_numeric($contains) || !empty($contains)) {
      if (strpos($tag,"$contains")!== false) return do_shortcode($content);
   } elseif (is_numeric($notcontains) || !empty($notcontains)) {
      if (strpos($tag,"$notcontains")===false) return do_shortcode($content);
   } else {
      if (!empty($tag)) return do_shortcode($content);
   }
}
add_shortcode ( 'events_if', 'eme_if_shortcode');
add_shortcode ( 'events_if2', 'eme_if_shortcode');
add_shortcode ( 'events_if3', 'eme_if_shortcode');

// Returns true if the page in question is the events page
function eme_is_events_page() {
   $events_page_id = get_option('eme_events_page' );
   if ($events_page_id) {
      return is_page ( $events_page_id );
   } else {
      return false;
   }
}

function eme_is_single_day_page() {
   global $wp_query;
   return (eme_is_events_page () && (isset ( $wp_query->query_vars ['calendar_day'] ) && $wp_query->query_vars ['calendar_day'] != ''));
}

function eme_is_single_event_page() {
   global $wp_query;
   return (eme_is_events_page () && (isset ( $wp_query->query_vars ['event_id'] ) && $wp_query->query_vars ['event_id'] != ''));
}

function eme_is_multiple_events_page() {
   global $wp_query;
   return (eme_is_events_page () && ! (isset ( $wp_query->query_vars ['event_id'] ) && $wp_query->query_vars ['event_id'] != ''));
}

function eme_is_single_location_page() {
   global $wp_query;
   return (eme_is_events_page () && (isset ( $wp_query->query_vars ['location_id'] ) && $wp_query->query_vars ['location_id'] != ''));
}

function eme_is_multiple_locations_page() {
   global $wp_query;
   return (eme_is_events_page () && ! (isset ( $wp_query->query_vars ['location_id'] ) && $wp_query->query_vars ['location_id'] != ''));
}

function eme_get_contact($event) {
   if ($event['event_contactperson_id'] >0 )
      $contact_id = $event['event_contactperson_id'];
   else
      $contact_id = get_option('eme_default_contact_person');
   // suppose the user has been deleted ...
   if (!get_userdata($contact_id)) $contact_id = get_option('eme_default_contact_person');
   if ($contact_id < 1)
      $contact_id = $event['event_author'];
   $userinfo=get_userdata($contact_id);
   return $userinfo;
}

function eme_get_user_phone($user_id) {
   return get_usermeta($user_id, 'eme_phone');
}

// got from http://davidwalsh.name/php-email-encode-prevent-spam
function eme_ascii_encode($e) {
    $output = "";
    if (has_filter('eme_email_filter')) {
       $output=apply_filters('eme_email_filter',$e);
    } else {
       for ($i = 0; $i < strlen($e); $i++) { $output .= '&#'.ord($e[$i]).';'; }
    }
    return $output;
}

function eme_permalink_convert ($val) {
   // WP provides a function to convert accents to their ascii counterparts
   // called remove_accents, but we also want to replace spaces with "-"
   // and trim the last space. sanitize_title_with_dashes does all that
   // and then, add a trailing slash
   $val = sanitize_title_with_dashes(remove_accents($val));
   return trailingslashit($val);
}

function eme_event_url($event) {
   global $wp_rewrite;

   if ($event['event_url'] != '') {
      $the_link = $event['event_url'];
   } else {
      if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
         $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
         $slug = $event['event_slug'] ? $event['event_slug'] : $event['event_name'];
         $name=$events_prefix.$event['event_id']."/".eme_permalink_convert($slug);
         $the_link = trailingslashit(home_url()).user_trailingslashit($name);
      } else {
         $events_page_link = eme_get_events_page(true, false);
         if (stristr ( $events_page_link, "?" ))
            $joiner = "&amp;";
         else
            $joiner = "?";
         $the_link = $events_page_link.$joiner."event_id=".$event['event_id'];
      }
   }
   return $the_link;
}

function eme_location_url($location) {
   global $wp_rewrite;

   $the_link = "";
   if ($location['location_url'] != '') {
      $the_link = $location['location_url'];
   } else {
      if (isset($location['location_id']) && isset($location['location_name'])) {
         if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
            $locations_prefix=eme_permalink_convert(get_option ( 'eme_permalink_locations_prefix'));
            $slug = $location['location_slug'] ? $location['location_slug'] : $location['location_name'];
            $name=$locations_prefix.$location['location_id']."/".eme_permalink_convert($slug);
            $the_link = trailingslashit(home_url()).user_trailingslashit($name);
         } else {
            $events_page_link = eme_get_events_page(true, false);
            if (stristr ( $events_page_link, "?" )) {
               $joiner = "&amp;";
            } else {
               $joiner = "?";
            }
            $the_link = $events_page_link.$joiner."location_id=".$location['location_id'];
         }
      }
   }
   return $the_link;
}

function eme_calendar_day_url($day) {
   global $wp_rewrite;

   if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
      $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
      $name=$events_prefix.eme_permalink_convert($day);
      $the_link = trailingslashit(home_url()).user_trailingslashit($name);
   } else {
      $events_page_link = eme_get_events_page(true, false);
      if (stristr ( $events_page_link, "?" ))
         $joiner = "&amp;";
      else
         $joiner = "?";
      $the_link = $events_page_link.$joiner."calendar_day=".$day;
   }
   return $the_link;
}

function eme_check_exists($event_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_id='".$event_id."'";
   return $wpdb->get_var($sql);
}

function _eme_is_date_valid($date) {
   if (strlen($date) != 10)
      return false;
   $year = intval(substr ( $date, 0, 4 ));
   $month = intval(substr ( $date, 5, 2 ));
   $day = intval(substr ( $date, 8 ));
   return (checkdate ( $month, $day, $year ));
}
function eme_is_time_valid($time) {
   $result = preg_match ( "/([01]\d|2[0-3])(:[0-5]\d)/", $time );
   return ($result);
}

function eme_capNamesCB ( $cap ) {
   $cap = str_replace('_', ' ', $cap);
   $cap = ucfirst($cap);
   return $cap;
}
function eme_get_all_caps() {
   global $wp_roles;
   $caps = array();

   foreach ( $wp_roles->role_names as $role=>$name ) {
   	$role_caps = get_role($role);
      $caps = array_merge($caps, $role_caps->capabilities);
   }

   $keys = array_keys($caps);
   $names = array_map('eme_capNamesCB', $keys);
   $capabilities = array_combine($keys, $names);

   $sys_caps = get_option('syscaps');
   if ( is_array($sys_caps) ) {
      $capabilities = array_merge($sys_caps, $capabilities);
   }

   asort($capabilities);
   return $capabilities;
}

function eme_daydifference($date1,$date2) {
   $ConvertToTimeStamp_Date1 = strtotime($date1);
   $ConvertToTimeStamp_Date2 = strtotime($date2);
   $DateDifference = intval($ConvertToTimeStamp_Date2) - intval($ConvertToTimeStamp_Date1);
   return abs(round($DateDifference/86400));
}

?>
