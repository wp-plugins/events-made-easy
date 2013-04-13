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
   if ($contact_id < 1)
      $contact_id = get_current_user_id();
   $userinfo=get_userdata($contact_id);
   return $userinfo;
}

function eme_get_user_phone($user_id) {
   return get_user_meta($user_id, 'eme_phone',true);
}

function eme_get_date_format() {
   $format="";
   $current_userid=get_current_user_id();
   if ($current_userid)
      $format = get_user_meta($current_userid, 'eme_date_format',true);
   if ($format == '') $format=get_option('date_format');
   return $format;
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
      $url_mode=1;
      if (function_exists('qtrans_getLanguage')) {
         $language=qtrans_getLanguage();
         $url_mode=get_option('qtranslate_url_mode');
      } elseif (defined('ICL_LANGUAGE_CODE')) {
         $language=ICL_LANGUAGE_CODE;
      } else {
         $language="";
      }
      if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
         $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
         $slug = $event['event_slug'] ? $event['event_slug'] : $event['event_name'];
         $name=$events_prefix.$event['event_id']."/".eme_permalink_convert($slug);
         if (!empty($language)) {
            if ($url_mode==2) {
               $the_link = trailingslashit(home_url())."$language/".user_trailingslashit($name);
            } else {
               $the_link = trailingslashit(home_url()).user_trailingslashit($name);
               $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
            }
         } else {
            $the_link = trailingslashit(home_url()).user_trailingslashit($name);
         }
      } else {
         $the_link = eme_get_events_page(true, false);
         $the_link = add_query_arg( array( 'event_id' => $event['event_id'] ), $the_link );
         if (!empty($language))
            $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
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
      $url_mode=1;
      if (function_exists('qtrans_getLanguage')) {
         $language=qtrans_getLanguage();
         $url_mode=get_option('qtranslate_url_mode');
      } elseif (defined('ICL_LANGUAGE_CODE')) {
         $language=ICL_LANGUAGE_CODE;
      } else {
         $language="";
      }
      if (isset($location['location_id']) && isset($location['location_name'])) {
         if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
            $locations_prefix=eme_permalink_convert(get_option ( 'eme_permalink_locations_prefix'));
            $slug = $location['location_slug'] ? $location['location_slug'] : $location['location_name'];
            $name=$locations_prefix.$location['location_id']."/".eme_permalink_convert($slug);
            if (!empty($language)) {
               if ($url_mode==2) {
                  $the_link = trailingslashit(home_url())."$language/".user_trailingslashit($name);
               } else {
                  $the_link = trailingslashit(home_url()).user_trailingslashit($name);
                  $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
               }
            } else {
               $the_link = trailingslashit(home_url()).user_trailingslashit($name);
            }
         } else {
            $the_link = eme_get_events_page(true, false);
            $the_link = add_query_arg( array( 'location_id' => $location['location_id'] ), $the_link );
            if (!empty($language))
               $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
         }
      }
   }
   return $the_link;
}

function eme_calendar_day_url($day) {
   global $wp_rewrite;

   $url_mode=1;
   if (function_exists('qtrans_getLanguage')) {
      $language=qtrans_getLanguage();
      $url_mode=get_option('qtranslate_url_mode');
   } elseif (defined('ICL_LANGUAGE_CODE')) {
      $language=ICL_LANGUAGE_CODE;
   } else {
      $language="";
   }
   if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
      $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
      $name=$events_prefix.eme_permalink_convert($day);
      if (!empty($language)) {
         if ($url_mode==2) {
            $the_link = trailingslashit(home_url())."$language/".user_trailingslashit($name);
         } else {
            $the_link = trailingslashit(home_url()).user_trailingslashit($name);
            $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
         }
      } else {
         $the_link = trailingslashit(home_url()).user_trailingslashit($name);
      }
   } else {
      $the_link = eme_get_events_page(true, false);
      $the_link = add_query_arg( array( 'calendar_day' => $day ), $the_link );
      if (!empty($language))
         $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   }
   return $the_link;
}

function eme_payment_url($booking_id) {
   global $wp_rewrite;

   $url_mode=1;
   if (function_exists('qtrans_getLanguage')) {
      $language=qtrans_getLanguage();
      $url_mode=get_option('qtranslate_url_mode');
   } elseif (defined('ICL_LANGUAGE_CODE')) {
      $language=ICL_LANGUAGE_CODE;
   } else {
      $language="";
   }
   if (isset($wp_rewrite) && $wp_rewrite->using_permalinks() && get_option('eme_seo_permalink')) {
      $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
      $name=$events_prefix."p$booking_id";
      if (!empty($language)) {
         if ($url_mode==2) {
            $the_link = trailingslashit(home_url())."$language/".user_trailingslashit($name);
         } else {
            $the_link = trailingslashit(home_url()).user_trailingslashit($name);
            $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
         }
      } else {
         $the_link = trailingslashit(home_url()).user_trailingslashit($name);
      }
   } else {
      $the_link = eme_get_events_page(true, false);
      $the_link = add_query_arg( array( 'eme_pmt_id' => $booking_id ), $the_link );
      if (!empty($language))
         $the_link = add_query_arg( array( 'lang' => $language ), $the_link );
   }
   return $the_link;
}

function eme_check_event_exists($event_id) {
   global $wpdb;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $sql = "SELECT COUNT(*) from $events_table WHERE event_id='".$event_id."'";
   return $wpdb->get_var($sql);
}

function eme_check_location_exists($location_id) {
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME;
   $sql = "SELECT COUNT(*) from $locations_table WHERE location_id='".$location_id."'";
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
   $capabilities = array();

   foreach ( $wp_roles->roles as $role ) {
      if ($role['capabilities']) {
         foreach ( $role['capabilities'] as $cap=>$val ) {
           if (!preg_match("/^level/",$cap))
	      $capabilities[$cap]=eme_capNamesCB($cap);
         }
      }
   }

#   $sys_caps = get_option('syscaps');
#   if ( is_array($sys_caps) ) {
#      $capabilities = array_merge($sys_caps, $capabilities);
#   }

   asort($capabilities);
   return $capabilities;
}

function eme_daydifference($date1,$date2) {
   $ConvertToTimeStamp_Date1 = strtotime($date1);
   $ConvertToTimeStamp_Date2 = strtotime($date2);
   $DateDifference = intval($ConvertToTimeStamp_Date2) - intval($ConvertToTimeStamp_Date1);
   return abs(round($DateDifference/86400));
}

function eme_delete_image_files($image_basename) {
   $mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
   foreach($mime_types as $type) {
      if (file_exists($image_basename.".".$type))
         unlink($image_basename.".".$type);
   }
}

function eme_status_array() {
   $event_status_array = array();
   $event_status_array[STATUS_PUBLIC] = __ ( 'Public', 'eme' );
   $event_status_array[STATUS_PRIVATE] = __ ( 'Private', 'eme' );
   $event_status_array[STATUS_DRAFT] = __ ( 'Draft', 'eme' );
   return $event_status_array;
}

function eme_localised_date($mydate) {
   $date_format = eme_get_date_format();
   return date_i18n ( $date_format, strtotime($mydate));
}

function eme_localised_time($mydate) {
   $date_format = get_option('time_format');
   return date_i18n ( $date_format, strtotime($mydate));
}

function eme_currency_array() {
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
   return $currency_array;
}
?>
