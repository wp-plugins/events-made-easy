<?php
/*
Plugin Name: Events Made Easy
Version: 1.4.3
Plugin URI: http://www.e-dynamics.be/wordpress
Description: Description: Manage and display events. Includes recurring events; locations; widgets; Google maps; RSVP; ICAL and RSS feeds; Paypal, 2Checkout and Google Checkout. <a href="admin.php?page=eme-options">Settings</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=SMGDS4GLCYWNG&lc=BE&item_name=To%20support%20development%20of%20EME&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted">Donate</a>
Author: Franky Van Liedekerke
Author URI: http://www.e-dynamics.be/
*/

/*
Copyright (c) 2010, Franky Van Liedekerke.
Copyright (c) 2011, Franky Van Liedekerke.
Copyright (c) 2012, Franky Van Liedekerke.
Copyright (c) 2013, Franky Van Liedekerke.
Copyright (c) 2014, Franky Van Liedekerke.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*************************************************/ 

function eme_client_clock_enqueue_scripts() {
   // Embed client-clock.js in webpage header.
   wp_enqueue_script('client_clock_submit', plugin_dir_url( __FILE__ ) . 'js/client-clock.js', array('jquery'));  
}

function eme_client_clock_callback() {
   // Set php clock values in an array
   $phptime = getdate();
   // if clock data not set
   if (!isset($_SESSION['eme_client_unixtime'])) {
      // Preset php clock values in client session variables for fall-back if valid client clock data isn't received.
      $_SESSION['eme_client_clock_valid'] = false; // Will be set true if all client clock data passes sanity tests
      $_SESSION['eme_client_php_difference'] = 0; // Client-php clock difference integer seconds
      $_SESSION['eme_client_unixtime'] = (int) $phptime['0']; // Integer seconds since 1/1/1970 @ 12:00 AM
      $_SESSION['eme_client_seconds'] = (int) $phptime['seconds']; // Integer second this minute (0-59)
      $_SESSION['eme_client_minutes'] = (int) $phptime['minutes']; // Integer minute this hour (0-59)
      $_SESSION['eme_client_hours'] = (int) $phptime['hours']; // Integer hour this day (0-23)
      $_SESSION['eme_client_wday'] = (int) $phptime['wday']; // Integer day this week (0-6), 0 = Sunday, ... , 6 = Saturday
      $_SESSION['eme_client_mday'] = (int) $phptime['mday']; // Integer day this month 1-31)
      $_SESSION['eme_client_month'] = (int) $phptime['mon']; // Integer month this year (1-12)
      $_SESSION['eme_client_fullyear'] = (int) $phptime['year']; // Integer year (1970-9999)
      $ret = '1'; // reload from server
   } else {
      $ret = '0';
   }
   
   // Cast client clock values as integers to avoid mathematical errors and set in temporary local variables.
   $client_unixtime = (int) $_POST['client_unixtime'];
   $client_seconds = (int) $_POST['client_seconds'];
   $client_minutes = (int) $_POST['client_minutes'];
   $client_hours = (int) $_POST['client_hours'];
   $client_wday = (int) $_POST['client_wday'];
   $client_mday = (int) $_POST['client_mday'];
   $client_month = (int) $_POST['client_month'];
   $client_fullyear = (int) $_POST['client_fullyear'];
   
   // Client clock sanity tests
   $valid = true;
   if (abs($client_unixtime - $_SESSION['eme_client_unixtime']) > 300) $valid = false; // allow +/-5 min difference
   if (abs($client_seconds - 30) > 30) $valid = false; // Seconds <0 or >60
   if (abs($client_minutes - 30) > 30) $valid = false; // Minutes <0 or >60
   if (abs($client_hours - 12) > 12) $valid = false; // Hours <0 or >24
   if (abs($client_wday - 3) > 3) $valid = false; // Weekday <0 or >6
   if (abs($client_mday - $_SESSION['eme_client_mday']) > 30) $valid = false; // >30 day difference
   if (abs($client_month - $_SESSION['eme_client_month']) > 11) $valid = false; // >11 month difference
   if (abs($client_fullyear - $_SESSION['eme_client_fullyear']) > 1) $valid = false; // >1 year difference

   // To insure mutual consistency, don't use any client values unless they all passed the tests.
   If ($valid) {
      $_SESSION['eme_client_unixtime'] = $client_unixtime;
      $_SESSION['eme_client_seconds'] = $client_seconds;
      $_SESSION['eme_client_minutes'] = $client_minutes;
      $_SESSION['eme_client_hours'] = $client_hours;
      $_SESSION['eme_client_wday'] = $client_wday;
      $_SESSION['eme_client_mday'] = $client_mday;
      $_SESSION['eme_client_month'] = $client_month;
      $_SESSION['eme_client_fullyear'] = $client_fullyear;
      $_SESSION['eme_client_clock_valid'] = true;
      // Set  date & time clock strings
      $php_clock_str = $phptime['year'] . "-" . $phptime['mon'] . "-" . $phptime['mday'] . " ";
      $php_clock_str .= $phptime['hours'] . ":" . $phptime['minutes'] . ":" . $phptime['seconds'];
      $client_clock_str = $_SESSION['eme_client_fullyear'] . "-" . $_SESSION['eme_client_month'] . "-" . $_SESSION['eme_client_mday'] . " ";
      $client_clock_str .= $_SESSION['eme_client_hours'] . ":" . $_SESSION['eme_client_minutes'] . ":" . $_SESSION['eme_client_seconds'];
      $_SESSION['eme_client_php_difference'] = (int) (strtotime($client_clock_str) - strtotime($php_clock_str));
   }
   
   echo $ret;
   die(); //  because this is an AJAX instance
}

// Setting constants
define('EME_DB_VERSION', 54);
define('EME_PLUGIN_URL', plugins_url('',plugin_basename(__FILE__)).'/'); //PLUGIN URL
define('EME_PLUGIN_DIR', ABSPATH.PLUGINDIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))); //PLUGIN DIRECTORY
define('EVENTS_TBNAME','eme_events');
define('RECURRENCE_TBNAME','eme_recurrence');
define('LOCATIONS_TBNAME','eme_locations');
define('BOOKINGS_TBNAME','eme_bookings');
define('PEOPLE_TBNAME','eme_people');
define('CATEGORIES_TBNAME', 'eme_categories');
define('TEMPLATES_TBNAME', 'eme_templates');
define('FORMFIELDS_TBNAME', 'eme_formfields');
define('FIELDTYPES_TBNAME', 'eme_fieldtypes');
define('ANSWERS_TBNAME', 'eme_answers');
define('DEFAULT_EVENT_PAGE_NAME', 'Events');
define('MIN_CAPABILITY', 'edit_posts');   // Minimum user level to edit own events
define('AUTHOR_CAPABILITY', 'publish_posts');   // Minimum user level to put an event in public/private state
define('EDIT_CAPABILITY', 'edit_others_posts'); // Minimum user level to edit any event
define('SETTING_CAPABILITY', 'activate_plugins');  // Minimum user level to edit settings
define('DEFAULT_CAP_ADD_EVENT','edit_posts');
define('DEFAULT_CAP_AUTHOR_EVENT','publish_posts');
define('DEFAULT_CAP_PUBLISH_EVENT','publish_posts');
define('DEFAULT_CAP_LIST_EVENTS','edit_posts');
define('DEFAULT_CAP_EDIT_EVENTS','edit_others_posts');
define('DEFAULT_CAP_ADD_LOCATION','edit_others_posts');
define('DEFAULT_CAP_AUTHOR_LOCATION','edit_others_posts');
define('DEFAULT_CAP_EDIT_LOCATIONS','edit_others_posts');
define('DEFAULT_CAP_CATEGORIES','activate_plugins');
define('DEFAULT_CAP_TEMPLATES','activate_plugins');
define('DEFAULT_CAP_PEOPLE','edit_posts');
define('DEFAULT_CAP_APPROVE','edit_others_posts');
define('DEFAULT_CAP_REGISTRATIONS','edit_others_posts');
define('DEFAULT_CAP_FORMS','edit_others_posts');
define('DEFAULT_CAP_CLEANUP','activate_plugins');
define('DEFAULT_CAP_SETTINGS','activate_plugins');
define('DEFAULT_CAP_SEND_MAILS','edit_posts');
define('DEFAULT_CAP_SEND_OTHER_MAILS','edit_others_posts');
define('DEFAULT_EVENT_LIST_ITEM_FORMAT', '<li>#j #M #Y - #H:#i<br /> #_LINKEDNAME<br />#_TOWN </li>');
define('DEFAULT_SINGLE_EVENT_FORMAT', '<p>#j #M #Y - #H:#i</p><p>#_TOWN</p><p>#_NOTES</p><p>#_ADDBOOKINGFORM</p><p>#_MAP</p>'); 
define('DEFAULT_EVENTS_PAGE_TITLE',__('Events','eme') ) ;
define('DEFAULT_EVENT_PAGE_TITLE_FORMAT', '#_EVENTNAME'); 
define('DEFAULT_EVENT_HTML_TITLE_FORMAT', '#_EVENTNAME'); 
define('DEFAULT_ICAL_DESCRIPTION_FORMAT',"#_NOTES");
define('DEFAULT_RSS_DESCRIPTION_FORMAT',"#j #M #y - #H:#i <br /> #_NOTES <br />#_LOCATIONNAME <br />#_ADDRESS <br />#_TOWN");
define('DEFAULT_RSS_TITLE_FORMAT',"#_EVENTNAME");
define('DEFAULT_ICAL_TITLE_FORMAT',"#_EVENTNAME");
define('DEFAULT_MAP_TEXT_FORMAT', '<strong>#_LOCATIONNAME</strong><p>#_ADDRESS</p><p>#_TOWN</p>');
define('DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT','<li>#_LINKEDNAME<ul><li>#j #M #y</li><li>#_TOWN</li></ul></li>');
define('DEFAULT_NO_EVENTS_MESSAGE', __('No events', 'eme'));
define('DEFAULT_SINGLE_LOCATION_FORMAT', '<p>#_ADDRESS</p><p>#_TOWN</p>#_DESCRIPTION #_MAP'); 
define('DEFAULT_LOCATION_PAGE_TITLE_FORMAT', '#_LOCATIONNAME'); 
define('DEFAULT_LOCATION_HTML_TITLE_FORMAT', '#_LOCATIONNAME'); 
define('DEFAULT_LOCATION_BALLOON_FORMAT', "<strong>#_LOCATIONNAME</strong><br />#_ADDRESS - #_TOWN<br /><a href='#_LOCATIONPAGEURL'>Details</a>");
define('DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT', "<li>#_EVENTNAME - #j #M #Y - #H:#i</li>");
define('DEFAULT_LOCATION_NO_EVENTS_MESSAGE', __('<li>No events in this location</li>', 'eme'));
define('DEFAULT_FULL_CALENDAR_EVENT_FORMAT', '<li>#_LINKEDNAME</li>');
define('DEFAULT_SMALL_CALENDAR_EVENT_TITLE_FORMAT', "#_EVENTNAME" );
define('DEFAULT_SMALL_CALENDAR_EVENT_TITLE_SEPARATOR', ", ");
define('DEFAULT_USE_SELECT_FOR_LOCATIONS', false);
define('DEFAULT_ATTRIBUTES_ENABLED', true);
define('DEFAULT_RECURRENCE_ENABLED', true);
define('DEFAULT_RSVP_ENABLED', true);
define('DEFAULT_RSVP_ADDBOOKINGFORM_SUBMIT_STRING', __('Send your booking', 'eme'));
define('DEFAULT_RSVP_DELBOOKINGFORM_SUBMIT_STRING', __('Cancel your booking', 'eme'));
define('DEFAULT_ATTENDEES_LIST_FORMAT','<li>#_ATTENDNAME (#_ATTENDSPACES)</li>');
define('DEFAULT_BOOKINGS_LIST_FORMAT','<li>#_RESPNAME (#_RESPSPACES)</li>');
define('DEFAULT_BOOKINGS_LIST_HEADER_FORMAT',"<ul class='eme_bookings_list_ul'>");
define('DEFAULT_BOOKINGS_LIST_FOOTER_FORMAT','</ul>');
define('DEFAULT_CATEGORIES_ENABLED', true);
define('DEFAULT_GMAP_ENABLED', true);
define('DEFAULT_GMAP_ZOOMING', true);
define('DEFAULT_GLOBAL_ZOOM_FACTOR', 3);
define('DEFAULT_INDIV_ZOOM_FACTOR', 14);
define('DEFAULT_GLOBAL_MAPTYPE', "ROADMAP");
define('DEFAULT_INDIV_MAPTYPE', "ROADMAP");
define('DEFAULT_SEO_PERMALINK', true);
define('DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT', "F, Y");
define('DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT', "Y");
define('DEFAULT_FILTER_FORM_FORMAT', "#_FILTER_CATS #_FILTER_LOCS");
define('STATUS_PUBLIC', 1);
define('STATUS_PRIVATE', 2);
define('STATUS_DRAFT', 5);
$upload_info = wp_upload_dir();
define("IMAGE_UPLOAD_DIR", $upload_info['basedir']."/locations-pics");
define("IMAGE_UPLOAD_URL", $upload_info['baseurl']."/locations-pics");
define("CO_URL","https://www.2checkout.com/checkout/spurchase");
define("PAYPAL_LIVE_URL","https://www.paypal.com/cgi-bin/webscr");
define("PAYPAL_SANDBOX_URL","https://www.sandbox.paypal.com/cgi-bin/webscr");
define("GOOGLE_LIVE","production");
define("GOOGLE_SANDBOX","sandbox");
define("FDGG_LIVE_URL","https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing");
define("FDGG_SANDBOX_URL","https://connect.firstdataglobalgateway.com/IPGConnect/gateway/processing");

// DEBUG constant for developing
// if you are hacking this plugin, set to TRUE, a log will show in admin pages
define('DEBUG', false);

function eme_load_textdomain() {
   $thisDir = dirname( plugin_basename( __FILE__ ) );
   load_plugin_textdomain('eme', false, $thisDir.'/langs'); 
}

// To enable activation through the activate function
register_activation_hook(__FILE__,'eme_install');
// when deactivation is needed
register_deactivation_hook(__FILE__,'eme_uninstall');
// when a new blog is added for network installation and the plugin is network activated
add_action( 'wpmu_new_blog', 'eme_new_blog', 10, 6);      
// to execute a db update after auto-update of EME
//add_action( 'plugins_loaded', 'eme_install' );

// filters for general events field (corresponding to those of  "the_title")
add_filter('eme_general', 'wptexturize');
add_filter('eme_general', 'convert_chars');
add_filter('eme_general', 'trim');
// filters for the notes field  (corresponding to those of  "the_content")
add_filter('eme_notes', 'wptexturize');
add_filter('eme_notes', 'convert_smilies');
add_filter('eme_notes', 'convert_chars');
add_filter('eme_notes', 'wpautop');
add_filter('eme_notes', 'shortcode_unautop');
add_filter('eme_notes', 'prepend_attachment');
// RSS general filters (corresponding to those of  "the_content_rss")
add_filter('eme_general_rss', 'ent2ncr', 8);
// RSS excerpt filter (corresponding to those of  "the_excerpt_rss")
add_filter('eme_excerpt_rss', 'convert_chars', 8);
add_filter('eme_excerpt_rss', 'ent2ncr', 8);

// TEXT content filter
add_filter('eme_text', 'wp_strip_all_tags');
add_filter('eme_text', 'ent2ncr', 8);

// we only want the google map javascript to be loaded if needed, so we set a global
// variable to 0 here and if we detect #_MAP, we set it to 1. In a footer filter, we then
// check if it is 1 and if so: include it
$eme_need_gmap_js=0;

// we only want the jquery for the calendar to load if/when needed
$eme_need_calendar_js=0;

// set the timezone
$tzstring = get_option('timezone_string');
if (!empty($tzstring) ) {
   @date_default_timezone_set ($tzstring);
}

// enable shortcodes in widgets, if wanted
if (!is_admin() && get_option('eme_shortcodes_in_widgets')) {
   add_filter('widget_text', 'do_shortcode', 11);
}

// the next is executed on activation/deaction of EME, so as to set the rewriterules correctly
function eme_flushRules() {
   global $wp_rewrite;
   $wp_rewrite->flush_rules();
}

// Adding a new rule
function eme_insertMyRewriteRules($rules) {
   // using pagename as param to index.php causes rewrite troubles if the page is a subpage of another
   // luckily for us we have the page id, and this works ok
   $page_id=get_option ( 'eme_events_page' );
   $events_prefix=eme_permalink_convert(get_option ( 'eme_permalink_events_prefix'));
   $locations_prefix=eme_permalink_convert(get_option ( 'eme_permalink_locations_prefix'));
   $newrules = array();
   $newrules[$events_prefix.'(\d{4})-(\d{2})-(\d{2})/c(\d*)'] = 'index.php?page_id='.$page_id.'&calendar_day=$matches[1]-$matches[2]-$matches[3]'.'&eme_event_cat=$matches[4]';
   $newrules[$events_prefix.'(\d{4})-(\d{2})-(\d{2})'] = 'index.php?page_id='.$page_id.'&calendar_day=$matches[1]-$matches[2]-$matches[3]';
   $newrules[$events_prefix.'(\d*)/'] = 'index.php?page_id='.$page_id.'&event_id=$matches[1]';
   $newrules[$events_prefix.'p(\d*)'] = 'index.php?page_id='.$page_id.'&eme_pmt_id=$matches[1]';
   $newrules[$events_prefix.'town/(.*)'] = 'index.php?page_id='.$page_id.'&eme_town=$matches[1]';
   $newrules[$events_prefix.'cat/(.*)'] = 'index.php?page_id='.$page_id.'&eme_event_cat=$matches[1]';
   $newrules[$locations_prefix.'(\d*)/'] = 'index.php?page_id='.$page_id.'&location_id=$matches[1]';
   return $newrules + $rules;
}
add_filter('rewrite_rules_array','eme_insertMyRewriteRules');

// Adding the id var so that WP recognizes it
function eme_insertMyRewriteQueryVars($vars) {
    array_push($vars, 'event_id');
    array_push($vars, 'location_id');
    array_push($vars, 'calendar_day');
    array_push($vars, 'eme_town');
    array_push($vars, 'eme_event_cat');
    // a bit cryptic for the booking id
    array_push($vars, 'eme_pmt_id');
    // for the payment result
    array_push($vars, 'eme_pmt_result');
    return $vars;
}
add_filter('query_vars','eme_insertMyRewriteQueryVars');

// INCLUDES
// We let the includes happen at the end, so all init-code is done
// (like eg. the load_textdomain). Some includes do stuff based on _GET
// so they need the correct info before doing stuff
include("captcha_check.php");
include("eme_settings.php");
include("eme_functions.php");
include("eme_filters.php");
include("eme_events.php");
include("eme_calendar.php");
include("eme_widgets.php");
include("eme_rsvp.php");
include("eme_locations.php"); 
include("eme_people.php");
include("eme_recurrence.php");
include("eme_UI_helpers.php");
include("eme_categories.php");
include("eme_templates.php");
include("eme_attributes.php");
include("eme_ical.php");
include("eme_cleanup.php");
include("eme_formfields.php");
include("eme_shortcodes.php");
include("eme_actions.php");

require_once("phpmailer/eme_phpmailer.php") ;
//require_once("phpmailer/language/phpmailer.lang-en.php") ;

function eme_install($networkwide) {
   global $wpdb;
   if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if ($networkwide) {
         $old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            _eme_install();
         }
         switch_to_blog($old_blog);
         return;
      }  
   } 
   // executed if no network activation
   _eme_install();     
}

// the private function; for activation
function _eme_install() {
   eme_add_options();

   $db_version = get_option('eme_version');
   if ($db_version == EME_DB_VERSION) {
      return;
   }
   if ($db_version>0 && $db_version<20) {
      eme_rename_tables();
   }
   if ($db_version>0 && $db_version<49) {
      delete_option('eme_events_admin_limit');
   }

   // always reset the drop data option
   update_option('eme_uninstall_drop_data', 0); 
   
   // always reset the donation option
   update_option('eme_donation_done', 0); 

   // Create events page if necessary
   $events_page_id = get_option('eme_events_page');

   if ($events_page_id != "" ) {
      if (!get_page($events_page_id))
         eme_create_events_page(); 
   } else {
      eme_create_events_page(); 
   }

   eme_create_tables();

   // SEO rewrite rules
   eme_flushRules();
   
   // now set the version correct
   update_option('eme_version', EME_DB_VERSION); 
}

function eme_uninstall($networkwide) {
   global $wpdb;

   if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if ($networkwide) {
         $old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            _eme_uninstall();
         }
         switch_to_blog($old_blog);
         return;
      }  
   } 
   // executed if no network activation
   _eme_uninstall();
}

function _eme_uninstall($force_drop=0) {
   $drop_data = get_option('eme_uninstall_drop_data');
   if ($drop_data || $force_drop) {
      eme_drop_table(EVENTS_TBNAME);
      eme_drop_table(RECURRENCE_TBNAME);
      eme_drop_table(LOCATIONS_TBNAME);
      eme_drop_table(BOOKINGS_TBNAME);
      eme_drop_table(PEOPLE_TBNAME);
      eme_drop_table(CATEGORIES_TBNAME);
      eme_drop_table(TEMPLATES_TBNAME);
      eme_drop_table(FORMFIELDS_TBNAME);
      eme_drop_table(FIELDTYPES_TBNAME);
      eme_drop_table(ANSWERS_TBNAME);
      eme_delete_events_page();
      eme_options_delete();
      eme_metabox_options_delete();
   }

    // SEO rewrite rules
    eme_flushRules();
}

function eme_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
   global $wpdb;
 
   if (is_plugin_active_for_network(plugin_basename( __FILE__ ))) {
      $old_blog = $wpdb->blogid;
      switch_to_blog($blog_id);
      _eme_install();
      switch_to_blog($old_blog);
   }
}

function eme_create_tables() {
   global $wpdb;
   // Creates the events table if necessary
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   $charset="";
   $collate="";
   if ( $wpdb->has_cap('collation') ) {
      if ( ! empty($wpdb->charset) )
         $charset = "DEFAULT CHARACTER SET $wpdb->charset";
      if ( ! empty($wpdb->collate) )
         $collate = "COLLATE $wpdb->collate";
   }
   eme_create_events_table($charset,$collate);
   eme_create_recurrence_table($charset,$collate);
   eme_create_locations_table($charset,$collate);
   eme_create_bookings_table($charset,$collate);
   eme_create_people_table($charset,$collate);
   eme_create_categories_table($charset,$collate);
   eme_create_templates_table($charset,$collate);
   eme_create_formfields_table($charset,$collate);
   eme_create_answers_table($charset,$collate);
}

function eme_drop_table($table) {
   global $wpdb;
   $table = $wpdb->prefix.$table;
   $wpdb->query("DROP TABLE IF EXISTS $table");
}

function eme_convert_charset($table,$charset,$collate) {
   global $wpdb;
   $table = $wpdb->prefix.$table;
   $sql = "ALTER TABLE $table CONVERT TO $charset $collate;";
   $wpdb->query($sql);
}

function eme_rename_tables() {
   global $wpdb;
   $table_names = array ($wpdb->prefix.EVENTS_TBNAME, $wpdb->prefix.RECURRENCE_TBNAME, $wpdb->prefix.LOCATIONS_TBNAME, $wpdb->prefix.BOOKINGS_TBNAME, $wpdb->prefix.PEOPLE_TBNAME, $wpdb->prefix.CATEGORIES_TBNAME);
   $prefix=$wpdb->prefix."eme_";
   $old_prefix=$wpdb->prefix."dbem_";
   foreach ($table_names as $table_name) {
      $old_table_name=preg_replace("/$prefix/",$old_prefix,$table_name);
      $sql = "RENAME TABLE $old_table_name TO $table_name;";
      $wpdb->query($sql); 
   }
}

function eme_create_events_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   
   $table_name = $wpdb->prefix.EVENTS_TBNAME;
   
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      // Creating the events table
      $sql = "CREATE TABLE ".$table_name." (
         event_id mediumint(9) NOT NULL AUTO_INCREMENT,
         event_status mediumint(9) DEFAULT 1,
         event_author mediumint(9) DEFAULT 0,
         event_name text NOT NULL,
         event_slug text default NULL,
         event_url text default NULL,
         event_start_time time NOT NULL,
         event_end_time time NOT NULL,
         event_start_date date NOT NULL,
         event_end_date date NULL, 
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         event_notes longtext DEFAULT NULL,
         event_rsvp bool DEFAULT 0,
         use_paypal bool DEFAULT 0,
         use_google bool DEFAULT 0,
         use_2co bool DEFAULT 0,
         use_webmoney bool DEFAULT 0,
         use_fdgg bool DEFAULT 0,
         price text DEFAULT NULL,
         currency text DEFAULT NULL,
         rsvp_number_days tinyint unsigned DEFAULT 0,
         rsvp_number_hours tinyint unsigned DEFAULT 0,
         event_seats text DEFAULT NULL,
         event_contactperson_id mediumint(9) DEFAULT 0,
         location_id mediumint(9) DEFAULT 0,
         recurrence_id mediumint(9) DEFAULT 0,
         event_category_ids text default NULL,
         event_attributes text NULL, 
         event_properties text NULL, 
         event_page_title_format text NULL, 
         event_single_event_format text NULL, 
         event_contactperson_email_body text NULL, 
         event_respondent_email_body text NULL, 
         event_registration_recorded_ok_html text NULL, 
         event_registration_pending_email_body text NULL, 
         event_registration_updated_email_body text NULL, 
         event_registration_form_format text NULL, 
         event_cancel_form_format text NULL, 
         registration_requires_approval bool DEFAULT 0,
         registration_wp_users_only bool DEFAULT 0,
         event_image_url text NULL,
         event_image_id mediumint(9) DEFAULT 0 NOT NULL,
         UNIQUE KEY (event_id)
         ) $charset $collate;";
      
      maybe_create_table($table_name,$sql);
      //--------------  DEBUG CODE to insert a few events n the new table
      // get the current timestamp into an array
      $timestamp = time();
      $date_time_array = getdate($timestamp);

      $hours = $date_time_array['hours'];
      $minutes = $date_time_array['minutes'];
      $seconds = $date_time_array['seconds'];
      $month = $date_time_array['mon'];
      $day = $date_time_array['mday'];
      $year = $date_time_array['year'];

      // use mktime to recreate the unix timestamp
      // adding 19 hours to $hours
      $in_one_week = strftime('%Y-%m-%d', mktime($hours,$minutes,$seconds,$month,$day+7,$year));
      $in_four_weeks = strftime('%Y-%m-%d',mktime($hours,$minutes,$seconds,$month,$day+28,$year)); 
      $in_one_year = strftime('%Y-%m-%d',mktime($hours,$minutes,$seconds,$month,$day,$year+1)); 
      
      $wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
            VALUES ('Orality in James Joyce Conference', '$in_one_week', '16:00:00', '18:00:00', 1)");
      $wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
            VALUES ('Traditional music session', '$in_four_weeks', '20:00:00', '22:00:00', 2)");
      $wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
               VALUES ('6 Nations, Italy VS Ireland', '$in_one_year','22:00:00', '24:00:00', 3)");
   } else {
      // eventual maybe_add_column() for later versions
      maybe_add_column($table_name, 'event_status', "alter table $table_name add event_status mediumint(9) DEFAULT 1;"); 
      maybe_add_column($table_name, 'event_start_date', "alter table $table_name add event_start_date date NOT NULL;"); 
      maybe_add_column($table_name, 'event_end_date', "alter table $table_name add event_end_date date NULL;");
      maybe_add_column($table_name, 'event_start_time', "alter table $table_name add event_start_time time NOT NULL;"); 
      maybe_add_column($table_name, 'event_end_time', "alter table $table_name add event_end_time time NOT NULL;"); 
      maybe_add_column($table_name, 'event_rsvp', "alter table $table_name add event_rsvp bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_paypal', "alter table $table_name add use_paypal bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_google', "alter table $table_name add use_google bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_2co', "alter table $table_name add use_2co bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_webmoney', "alter table $table_name add use_webmoney bool DEFAULT 0;");
      maybe_add_column($table_name, 'use_fdgg', "alter table $table_name add use_fdgg bool DEFAULT 0;");
      maybe_add_column($table_name, 'rsvp_number_days', "alter table $table_name add rsvp_number_days tinyint unsigned DEFAULT 0;");
      maybe_add_column($table_name, 'rsvp_number_hours', "alter table $table_name add rsvp_number_hours tinyint unsigned DEFAULT 0;");
      maybe_add_column($table_name, 'price', "alter table $table_name add price text DEFAULT NULL;");
      maybe_add_column($table_name, 'currency', "alter table $table_name add currency text DEFAULT NULL;");
      maybe_add_column($table_name, 'event_seats', "alter table $table_name add event_seats text DEFAULT NULL;");
      maybe_add_column($table_name, 'location_id', "alter table $table_name add location_id mediumint(9) DEFAULT 0;");
      maybe_add_column($table_name, 'recurrence_id', "alter table $table_name add recurrence_id mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'event_contactperson_id', "alter table $table_name add event_contactperson_id mediumint(9) DEFAULT 0;");
      maybe_add_column($table_name, 'event_attributes', "alter table $table_name add event_attributes text NULL;"); 
      maybe_add_column($table_name, 'event_properties', "alter table $table_name add event_properties text NULL;"); 
      maybe_add_column($table_name, 'event_url', "alter table $table_name add event_url text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'event_slug', "alter table $table_name add event_slug text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'event_category_ids', "alter table $table_name add event_category_ids text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'event_page_title_format', "alter table $table_name add event_page_title_format text NULL;"); 
      maybe_add_column($table_name, 'event_single_event_format', "alter table $table_name add event_single_event_format text NULL;"); 
      maybe_add_column($table_name, 'event_contactperson_email_body', "alter table $table_name add event_contactperson_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_respondent_email_body', "alter table $table_name add event_respondent_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_pending_email_body', "alter table $table_name add event_registration_pending_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_updated_email_body', "alter table $table_name add event_registration_updated_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_registration_recorded_ok_html', "alter table $table_name add event_registration_recorded_ok_html text NULL;"); 
      maybe_add_column($table_name, 'registration_requires_approval', "alter table $table_name add registration_requires_approval bool DEFAULT 0;"); 
      $registration_wp_users_only=get_option('eme_rsvp_registered_users_only');
      maybe_add_column($table_name, 'registration_wp_users_only', "alter table $table_name add registration_wp_users_only bool DEFAULT $registration_wp_users_only;"); 
      maybe_add_column($table_name, 'event_author', "alter table $table_name add event_author mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date', "alter table $table_name add modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date_gmt', "alter table $table_name add modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'event_registration_form_format', "alter table $table_name add event_registration_form_format text NULL;"); 
      maybe_add_column($table_name, 'event_cancel_form_format', "alter table $table_name add event_cancel_form_format text NULL;"); 
      maybe_add_column($table_name, 'event_image_url', "alter table $table_name add event_image_url text NULL;"); 
      maybe_add_column($table_name, 'event_image_id', "alter table $table_name add event_image_id mediumint(9) DEFAULT 0 NOT NULL;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_name text;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_notes longtext;");
      }
      if ($db_version<4) {
         $wpdb->query("ALTER TABLE $table_name CHANGE event_category_id event_category_ids text default NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_author mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_contactperson_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_seats mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY location_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY recurrence_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_rsvp bool DEFAULT 0;");
      }
      if ($db_version<5) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_rsvp bool DEFAULT 0;");
      }
      if ($db_version<11) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN event_author;");
         $wpdb->query("ALTER TABLE $table_name CHANGE event_creator_id event_author mediumint(9) DEFAULT 0;");
      }
      if ($db_version<29) {
         $wpdb->query("ALTER TABLE $table_name MODIFY price text default NULL;");
      }
      if ($db_version<33) {
         $post_table_name = $wpdb->prefix."posts";
         $wpdb->query("UPDATE $table_name SET event_image_id = (select ID from $post_table_name where post_type = 'attachment' AND guid = $table_name.event_image_url);");
      }
      if ($db_version<38) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_seats text default NULL;");
      }
   }
}

function eme_create_recurrence_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.RECURRENCE_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE ".$table_name." (
         recurrence_id mediumint(9) NOT NULL AUTO_INCREMENT,
         recurrence_start_date date NOT NULL,
         recurrence_end_date date NOT NULL,
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         recurrence_interval tinyint NOT NULL, 
         recurrence_freq tinytext NOT NULL,
         recurrence_byday tinytext NOT NULL,
         recurrence_byweekno tinyint NOT NULL,
         recurrence_specific_days text NULL,
         UNIQUE KEY (recurrence_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date', "alter table $table_name add modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date_gmt', "alter table $table_name add modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'recurrence_specific_days', "alter table $table_name add recurrence_specific_days text NULL;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY recurrence_byday tinytext NOT NULL ;");
      }
      if ($db_version<4) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN recurrence_name, DROP COLUMN recurrence_start_time, DROP COLUMN recurrence_end_time, DROP COLUMN recurrence_notes, DROP COLUMN location_id, DROP COLUMN event_contactperson_id, DROP COLUMN event_category_id, DROP COLUMN event_page_title_format, DROP COLUMN event_single_event_format, DROP COLUMN event_contactperson_email_body, DROP COLUMN event_respondent_email_body, DROP COLUMN registration_requires_approval ");
      }
      if ($db_version<13) {
         $wpdb->query("UPDATE $table_name set creation_date=NOW() where creation_date='0000-00-00 00:00:00'");
         $wpdb->query("UPDATE $table_name set modif_date=NOW() where modif_date='0000-00-00 00:00:00'");
      }
   }
}

function eme_create_locations_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         location_id mediumint(9) NOT NULL AUTO_INCREMENT,
         location_name text NOT NULL,
         location_slug text default NULL,
         location_url text default NULL,
         location_address tinytext NOT NULL,
         location_town tinytext NOT NULL,
         location_latitude float DEFAULT NULL,
         location_longitude float DEFAULT NULL,
         location_description text DEFAULT NULL,
         location_author mediumint(9) DEFAULT 0,
         location_category_ids text default NULL,
         location_creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         location_creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         location_modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         location_modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         location_image_url text NULL,
         location_image_id mediumint(9) DEFAULT 0 NOT NULL,
         location_attributes text NULL, 
         UNIQUE KEY (location_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
      
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
               VALUES ('Arts Millenium Building', 'Newcastle Road','Galway', 53.275, -9.06532)");
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
               VALUES ('The Crane Bar', '2, Sea Road','Galway', 53.2683224, -9.0626223)");
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
               VALUES ('Taaffes Bar', '19 Shop Street','Galway', 53.2725, -9.05321)");
   } else {
      maybe_add_column($table_name, 'location_author', "alter table $table_name add location_author mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'location_category_ids', "alter table $table_name add location_category_ids text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'location_creation_date', "alter table $table_name add location_creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'location_creation_date_gmt', "alter table $table_name add location_creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'location_modif_date', "alter table $table_name add location_modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'location_modif_date_gmt', "alter table $table_name add location_modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'location_url', "alter table $table_name add location_url text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'location_slug', "alter table $table_name add location_slug text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'location_image_url', "alter table $table_name add location_image_url text NULL;"); 
      maybe_add_column($table_name, 'location_image_id', "alter table $table_name add location_image_id mediumint(9) DEFAULT 0 NOT NULL;"); 
      maybe_add_column($table_name, 'location_attributes', "alter table $table_name add location_attributes text NULL;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY location_name text NOT NULL ;");
      }
      if ($db_version<33) {
         $post_table_name = $wpdb->prefix."posts";
         $wpdb->query("UPDATE $table_name SET location_image_id = (select ID from $post_table_name where post_type = 'attachment' AND guid = $table_name.location_image_url);");
      }
   }
}

function eme_create_bookings_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.BOOKINGS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         booking_id mediumint(9) NOT NULL AUTO_INCREMENT,
         event_id mediumint(9) NOT NULL,
         person_id mediumint(9) NOT NULL, 
         booking_seats mediumint(9) NOT NULL,
         booking_seats_mp varchar(250),
         booking_approved bool DEFAULT 0,
         booking_comment text DEFAULT NULL,
         booking_price text DEFAULT NULL,
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         booking_payed bool DEFAULT 0,
         transfer_nbr_be97 varchar(20),
         wp_id bigint(20) unsigned DEFAULT NULL,
         lang varchar(10) DEFAULT '',
         UNIQUE KEY  (booking_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'booking_comment', "ALTER TABLE $table_name add booking_comment text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'booking_approved', "ALTER TABLE $table_name add booking_approved bool DEFAULT 0;"); 
      maybe_add_column($table_name, 'booking_payed', "ALTER TABLE $table_name add booking_payed bool DEFAULT 0;"); 
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date', "alter table $table_name add modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date_gmt', "alter table $table_name add modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'transfer_nbr_be97', "alter table $table_name add transfer_nbr_be97 varchar(20);"); 
      maybe_add_column($table_name, 'booking_seats_mp', "alter table $table_name add booking_seats_mp varchar(250);"); 
      maybe_add_column($table_name, 'booking_price', "alter table $table_name add booking_price text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'wp_id', "ALTER TABLE $table_name add wp_id bigint(20) unsigned DEFAULT NULL;"); 
      maybe_add_column($table_name, 'lang', "ALTER TABLE $table_name add lang varchar(10) DEFAULT '';"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_id mediumint(9) NOT NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY person_id mediumint(9) NOT NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY booking_seats mediumint(9) NOT NULL;");
      }
      if ($db_version<47) {
         $people_table_name = $wpdb->prefix.PEOPLE_TBNAME;
         $wpdb->query("update $table_name a JOIN $people_table_name b on (a.person_id = b.person_id)  set a.wp_id=b.wp_id;");
      }
   }
}

function eme_create_people_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.PEOPLE_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         person_id mediumint(9) NOT NULL AUTO_INCREMENT,
         person_name tinytext NOT NULL, 
         person_email tinytext NOT NULL,
         person_phone tinytext DEFAULT NULL,
         wp_id bigint(20) unsigned DEFAULT NULL,
         lang varchar(10) DEFAULT '',
         UNIQUE KEY (person_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      maybe_add_column($table_name, 'wp_id', "ALTER TABLE $table_name add wp_id bigint(20) unsigned DEFAULT NULL;"); 
      maybe_add_column($table_name, 'lang', "ALTER TABLE $table_name add lang varchar(10) DEFAULT '';"); 
      if ($db_version<10) {
         $wpdb->query("ALTER TABLE $table_name MODIFY person_phone tinytext DEFAULT 0;");
      }
   }
} 

function eme_create_categories_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.CATEGORIES_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         category_id int(11) NOT NULL auto_increment,
         category_name tinytext NOT NULL,
         UNIQUE KEY  (category_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   }
}

function eme_create_templates_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.TEMPLATES_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         id int(11) NOT NULL auto_increment,
         description tinytext DEFAULT NULL,
         format text NOT NULL,
         UNIQUE KEY  (id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      if ($db_version<41) {
         $wpdb->query("ALTER TABLE $table_name MODIFY format text NOT NULL;");
      }
   }
}

function eme_create_formfields_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.FORMFIELDS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         field_id int(11) NOT NULL auto_increment,
         field_type mediumint(9) NOT NULL,
         field_name tinytext NOT NULL,
         field_info text NOT NULL,
         field_tags text,
         UNIQUE KEY  (field_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      if ($db_version<54) {
         maybe_add_column($table_name, 'field_tags', "ALTER TABLE $table_name add field_tags text;"); 
         $wpdb->query("UPDATE ".$table_name." SET field_tags=field_info");
      }
   }

   $table_name = $wpdb->prefix.FIELDTYPES_TBNAME;
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         type_id int(11) NOT NULL,
         type_info tinytext NOT NULL,
         is_multi int(1) DEFAULT 0,
         UNIQUE KEY  (type_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
      $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (1,'Text',0)");
      $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (2,'DropDown',1)");
      $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (3,'TextArea',0)");
      $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (4,'RadioBox',1)");
      $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (5,'RadioBox (Vertical)',1)");
      $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (6,'CheckBox',1)");
      $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (7,'CheckBox (Vertical)',1)");
   } else {
      if ($db_version<43) {
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info) VALUES (4,'RadioBox')");
      }
      if ($db_version<44) {
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info) VALUES (5,'RadioBox (Vertical)')");
	 $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info) VALUES (6,'CheckBox')");
	 $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info) VALUES (7,'CheckBox (Vertical)')");
      }
      if ($db_version<54) {
         maybe_add_column($table_name, 'is_multi', "ALTER TABLE $table_name add is_multi int(1) DEFAULT 0;"); 
         $wpdb->query("DELETE FROM ".$table_name);
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (1,'Text',0)");
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (2,'DropDown',1)");
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (3,'TextArea',0)");
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (4,'RadioBox',1)");
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (5,'RadioBox (Vertical)',1)");
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (6,'CheckBox',1)");
         $wpdb->query("INSERT INTO ".$table_name." (type_id,type_info,is_multi) VALUES (7,'CheckBox (Vertical)',1)");
      }
   }
}

function eme_create_answers_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.ANSWERS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         booking_id mediumint(9) NOT NULL,
         field_name tinytext NOT NULL,
         answer text NOT NULL,
         KEY  (booking_id)
         ) $charset $collate;";
      maybe_create_table($table_name,$sql);
   } else {
      if ($db_version==23) {
         $wpdb->query("ALTER TABLE ".$table_name." DROP PRIMARY KEY");
         $wpdb->query("ALTER TABLE ".$table_name." ADD KEY (booking_id)");
      }
   }
}

function eme_create_events_page() {
   global $wpdb;
   $postarr = array(
      'post_status'=> 'publish',
      'post_title' => DEFAULT_EVENT_PAGE_NAME,
      'post_name'  => wp_strip_all_tags(__('events','eme')),
      'post_type'  => 'page',
   );
   if ($int_post_id = wp_insert_post($postarr)) {
      update_option('eme_events_page', $int_post_id);
   }
}

function eme_delete_events_page() {
   $events_page_id = get_option('eme_events_page' );
   if ($events_page_id)
      wp_delete_post($events_page_id);
}

// Create the Manage Events and the Options submenus 
add_action('admin_menu','eme_create_events_submenu');
function eme_create_events_submenu () {
   $events_page_id = get_option('eme_events_page');
   if (!$events_page_id || !get_page($events_page_id))
      add_action('admin_notices', "eme_explain_events_page_missing");

   if(function_exists('add_submenu_page')) {
      add_object_page(__('Events', 'eme'),__('Events', 'eme'),get_option('eme_cap_list_events'),'events-manager','eme_events_page', EME_PLUGIN_URL.'images/calendar-16.png');
      // Add a submenu to the custom top-level menu: 
      // edit event also needs just "add" as capability, otherwise you will not be able to edit own created events
      $plugin_page = add_submenu_page('events-manager', __('Edit'),__('Edit'),get_option('eme_cap_list_events'),'events-manager','eme_events_page');
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_event_boxes' );
      $plugin_page = add_submenu_page('events-manager', __('Add new', 'eme'), __('Add new','eme'), get_option('eme_cap_add_event'), 'eme-new_event', "eme_new_event_page");
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_event_boxes' );
      $plugin_page = add_submenu_page('events-manager', __('Locations', 'eme'), __('Locations', 'eme'), get_option('eme_cap_add_locations'), 'eme-locations', "eme_locations_page");
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      if (get_option('eme_categories_enabled')) {
         $plugin_page = add_submenu_page('events-manager', __('Event Categories','eme'),__('Categories','eme'), get_option('eme_cap_categories'), "eme-categories", 'eme_categories_page');
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      }
      $plugin_page = add_submenu_page('events-manager', __('Templates','eme'),__('Templates','eme'), get_option('eme_cap_templates'), "eme-templates", 'eme_templates_page');
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      if (get_option('eme_rsvp_enabled')) {
         $plugin_page = add_submenu_page('events-manager', __('People', 'eme'), __('People', 'eme'), get_option('eme_cap_people'), 'eme-people', "eme_people_page");
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
         $plugin_page = add_submenu_page('events-manager', __('Pending Approvals', 'eme'), __('Pending Approvals', 'eme'), get_option('eme_cap_approve'), 'eme-registration-approval', "eme_registration_approval_page");
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
         $plugin_page = add_submenu_page('events-manager', __('Change Registration', 'eme'), __('Change Registration', 'eme'), get_option('eme_cap_registrations'), 'eme-registration-seats', "eme_registration_seats_page");
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
         if (get_option('eme_rsvp_mail_notify_is_active')) {
            $plugin_page = add_submenu_page('events-manager', __('Send Mails', 'eme'), __('Send Mails', 'eme'), get_option('eme_cap_send_mails'), 'eme-send-mails', "eme_send_mails_page");
            add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
         }
         $plugin_page = add_submenu_page('events-manager', __('Form Fields','eme'),__('Form Fields','eme'), get_option('eme_cap_forms'), "eme-formfields", 'eme_formfields_page');
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      }
      $plugin_page = add_submenu_page('events-manager', __('Cleanup', 'eme'), __('Cleanup', 'eme'), get_option('eme_cap_cleanup'), 'eme-cleanup', "eme_cleanup_page");
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );

      # just in case: make sure the Settings page can be reached if something is not correct with the security settings
      if (get_option('eme_cap_settings') =='')
         $cap_settings=DEFAULT_CAP_SETTINGS;
      else
         $cap_settings=get_option('eme_cap_settings');
      $plugin_page = add_submenu_page('events-manager', __('Events Made Easy Settings','eme'),__('Settings','eme'), $cap_settings, "eme-options", 'eme_options_page');
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      // do some option checking after the options have been updated
      // add_action( 'load-'. $plugin_page, 'eme_admin_options_save');
   }
}

function eme_replace_notes_placeholders($format, $event="", $target="html") {
   if ($event && preg_match_all('/#(ESC)?_(DETAILS|NOTES|EXCERPT|EVENTDETAILS)/', $format, $placeholders)) {
      foreach($placeholders[0] as $result) {
         $need_escape = 0;
         $orig_result = $result;
         $found = 1;
         if (strstr($result,'#ESC')) {
            $result = str_replace("#ESC","#",$result);
            $need_escape=1;
         }
         $replacement = "";
         $field = "event_".ltrim(strtolower($result), "#_");
         // to catch every alternative (we just need to know if it is an excerpt or not)
         if ($field != "event_excerpt")
            $show_excerpt=0;
         else
            $show_excerpt=1;

         // when on the single event page, never show just the excerpt
         if (eme_is_single_event_page() && $target == "html") {
            $show_excerpt=0;
         }

         // If excerpt, we use more link text
         if ($show_excerpt) {
            if (isset($event['event_notes'])) {
               $matches = explode('<!--more-->', $event['event_notes']);
               $replacement = $matches[0];
            }
         } elseif (isset($event['event_notes'])) {
            // remove the more-part
            $replacement = str_replace('<!--more-->', '' ,$event['event_notes'] );
         }
         $replacement = eme_translate($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_notes', $replacement);
         } else {
            if ($target == "rss") {
               if ($show_excerpt)
                  $replacement = apply_filters('eme_excerpt_rss', $replacement);
               else
                  $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }
         if ($found) {
            if ($need_escape)
               $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
            $format = str_replace($orig_result, $replacement ,$format );
         }
      }
   }
   return $format;
}

function eme_replace_placeholders($format, $event="", $target="html", $do_shortcode=1, $lang='') {
   global $wp_query;
   global $eme_need_gmap_js, $booking_id_done;

   // some variables we'll use further down more than once
   $current_userid=get_current_user_id();
   $person_id=eme_get_person_id_by_wp_id($current_userid);
   $eme_enable_notes_placeholders = get_option('eme_enable_notes_placeholders'); 
   if (isset($event['location_id']) && $event['location_id'])
      $location = eme_get_location ( $event['location_id'] );
   else
      $location = eme_new_location ();

   // first replace the notes sections, since these can contain other placeholders
   if ($eme_enable_notes_placeholders)
      $format = eme_replace_notes_placeholders ( $format, $event, $target );

   // then we do the custom attributes, since these can contain other placeholders
   preg_match_all("/#(ESC|URL)?_ATT\{.+?\}(\{.+?\})?/", $format, $results);
   foreach($results[0] as $resultKey => $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      //Strip string of placeholder and just leave the reference
      $attRef = substr( substr($result, 0, strpos($result, '}')), 6 );
      if (isset($event['event_attributes'][$attRef])) {
         $replacement = $event['event_attributes'][$attRef];
      }
      if( trim($replacement) == ''
         && isset($results[2][$resultKey])
         && $results[2][$resultKey] != '' ) {
         //Check to see if we have a second set of braces;
         $replacement = substr( $results[2][$resultKey], 1, strlen(trim($results[2][$resultKey]))-2 );
      }

      if ($need_escape)
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      if ($need_urlencode)
         $replacement = rawurlencode($replacement);
      $format = str_replace($orig_result, $replacement ,$format );
   }

   // and now all the other placeholders
   $legacy=get_option('eme_legacy');
   $deprecated=get_option('eme_deprecated');

   if ($legacy)
      preg_match_all("/#(ESC|URL)?@?_?[A-Za-z0-9_]+(\[.*\])?(\[.*\])?/", $format, $placeholders);
   else
      preg_match_all("/#(ESC|URL)?@?_?[A-Za-z0-9_]+(\{.*?\})?(\{.*?\})?/", $format, $placeholders);

   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'sort_stringlenth');

   foreach($placeholders[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      $found = 1;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      // matches all fields placeholder
      if ($event && preg_match('/#_EDITEVENTLINK/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = "<a href=' ".admin_url("admin.php?page=events-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event['event_id'])."'>".__('Edit')."</a>";
         }

      } elseif ($event && preg_match('/#_EDITEVENTURL/', $result)) { 
         if (current_user_can( get_option('eme_cap_edit_events')) ||
             (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
            $replacement = admin_url("admin.php?page=events-manager&amp;eme_admin_action=edit_event&amp;event_id=".$event['event_id']);
         }

      } elseif ($event && preg_match('/#_24HSTARTTIME/', $result)) { 
         $replacement = $event['event_start_time'];

      } elseif ($event && preg_match('/#_24HENDTIME/', $result)) { 
         $replacement = $event['event_end_time'];

      } elseif ($event && preg_match('/#_PAST_FUTURE_CLASS/', $result)) { 
         if (strtotime($event['event_start_date']." ".$event['event_start_time']) > time()) {
            $replacement="eme-future-event";
         } elseif (strtotime($event['event_end_date']." ".$event['event_end_time']) > time()) {
            $replacement="eme-ongoing-event";
         } else {
            $replacement="eme-past-event";
         }

      } elseif ($event && preg_match('/#_12HSTARTTIME/', $result)) {
         $replacement = date("h:i A", strtotime($event['event_start_time']));

      } elseif ($event && preg_match('/#_12HENDTIME/', $result)) {
         $replacement = date("h:i A", strtotime($event['event_end_time']));

      } elseif ($event && preg_match('/#_MAP/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } elseif (isset($event['location_id']) && $event['location_id']) {
            $replacement = eme_single_location_map($location);
         }

      } elseif ($event && preg_match('/#_DIRECTIONS/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } elseif (isset($event['location_id']) && $event['location_id']) {
            $replacement = eme_add_directions_form($location);
         }

      } elseif ($event && preg_match('/#_EVENTS_FILTERFORM/', $result)) {
         if ($target == "rss" || $target == "text" || eme_is_single_event_page()) {
            $replacement = "";
         } else {
            $replacement = eme_filter_form();
         }

      } elseif ($event && preg_match('/#_ADDBOOKINGFORM$/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } else {
            if ($booking_id_done && eme_event_needs_payment($event))
               $replacement = eme_payment_form($event,$booking_id_done);
            else
               $replacement = eme_add_booking_form($event['event_id']);
         }

      } elseif ($event && preg_match('/#_ADDBOOKINGFORM_IF_NOT_REGISTERED/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } elseif (is_user_logged_in() ) {
            // we show the form if the user did not register yet, or after registration to show the paypal form
            if ($booking_id_done && eme_event_needs_payment($event))
               $replacement = eme_payment_form($event,$booking_id_done);
            elseif (!eme_get_booking_ids_by_wp_id($current_userid,$event['event_id']))
               $replacement = eme_add_booking_form($event['event_id']);
         }

      } elseif ($event && preg_match('/#_REMOVEBOOKINGFORM$/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } else {
            // when the booking just happened and the user needs to pay, we don't show the remove booking form
            if ($booking_id_done && eme_event_needs_payment($event))
               $replacement = "";
            else
               $replacement = eme_delete_booking_form($event['event_id']);
         }

      } elseif ($event && preg_match('/#_REMOVEBOOKINGFORM_IF_REGISTERED/', $result)) {
         if ($target == "rss" || $target == "text") {
            $replacement = "";
         } elseif (is_user_logged_in() ) {
            // when the booking just happened and the user needs to pay, we don't show the remove booking form
            if ($booking_id_done && eme_event_needs_payment($event))
               $replacement = "";
            elseif (eme_get_booking_ids_by_wp_id($current_userid,$event['event_id']))
               $replacement = eme_delete_booking_form($event['event_id']);
         }

      } elseif ($event && preg_match('/#_(AVAILABLESPACES|AVAILABLESEATS)$/', $result)) {
         $replacement = eme_get_available_seats($event['event_id']);

      } elseif (($deprecated && preg_match('/#_(AVAILABLESPACES|AVAILABLESEATS)(\d+)/', $result, $matches)) ||
                preg_match('/#_(AVAILABLESPACES|AVAILABLESEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         if (eme_is_multi($event['event_seats'])) {
            $seats=eme_get_available_multiseats($event['event_id']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_(TOTALSPACES|TOTALSEATS)$/', $result)) {
         $replacement = $event['event_seats'];

      } elseif (($deprecated && preg_match('/#_(TOTALSPACES|TOTALSEATS)(\d+)/', $result, $matches)) ||
                preg_match('/#_(TOTALSPACES|TOTALSEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         if (eme_is_multi($event['event_seats'])) {
            $seats = eme_convert_multi2array($event['event_seats']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_(RESERVEDSPACES|BOOKEDSEATS)$/', $result)) {
         $replacement = eme_get_booked_seats($event['event_id']);

      } elseif (($deprecated && preg_match('/#_(RESERVEDSPACES|BOOKEDSEATS)(\d+)/', $result, $matches)) ||
                preg_match('/#_(RESERVEDSPACES|BOOKEDSEATS)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2])-1;
         if (eme_is_multi($event['event_seats'])) {
            $seats=eme_get_booked_multiseats($event['event_id']);
            if (array_key_exists($field_id,$seats))
               $replacement = $seats[$field_id];
         }

      } elseif ($event && preg_match('/#_USER_(RESERVEDSPACES|BOOKEDSEATS)$/', $result)) {
         if (is_user_logged_in()) {
            $replacement = eme_get_booked_seats_by_wp_event_id($current_userid,$event['event_id']);
         }

      } elseif ($event && preg_match('/#_LINKEDNAME/', $result)) {
         $event_link = eme_event_url($event,$lang);
         $replacement="<a href='$event_link' title='".eme_trans_sanitize_html($event['event_name'],$lang)."'>".eme_trans_sanitize_html($event['event_name'],$lang)."</a>";
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_ICALLINK/', $result)) {
         $url = site_url ("/?eme_ical=public_single&amp;event_id=".$event['event_id']);
         $replacement = "<a href='$url'>ICAL</a>";
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_ICALURL/', $result)) {
         $replacement = site_url ("/?eme_ical=public_single&amp;event_id=".$event['event_id']);

      } elseif ($event && preg_match('/#_EVENTIMAGE$/', $result)) {
         if (!empty($event['event_image_id']))
            $event['event_image_url'] = wp_get_attachment_url($event['event_image_id']);
         if($event['event_image_url'] != '') {
            $replacement = "<img src='".$event['event_image_url']."' alt='".eme_trans_sanitize_html($event['event_name'],$lang)."'/>";
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTIMAGEURL$/', $result)) {
         if (!empty($event['event_image_id']))
            $event['event_image_url'] = wp_get_attachment_url($event['event_image_id']);
         if($event['event_image_url'] != '') {
            $replacement = $event['event_image_url'];
         }

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMB$/', $result)) {
         if (!empty($event['event_image_id'])) {
            $thumb_array = image_downsize( $event['event_image_id'], get_option('eme_thumbnail_size') );
            $thumb_url = $thumb_array[0];
            $thumb_width = $thumb_array[1];
            $thumb_height = $thumb_array[2];
            $replacement = "<img width='$thumb_width' height='$thumb_height' src='".$thumb_url."' alt='".eme_trans_sanitize_html($event['event_name'],$lang)."'/>";
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMBURL$/', $result)) {
         if (!empty($event['event_image_id'])) {
            $thumb_array = image_downsize( $event['event_image_id'], get_option('eme_thumbnail_size') );
            $thumb_url = $thumb_array[0];
            $replacement = $thumb_url;
         }

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMB\{(.+)\}/', $result, $matches)) {
         if (!empty($event['event_image_id'])) {
            $thumb_array = image_downsize( $event['event_image_id'], $matches[1]);
            $thumb_url = $thumb_array[0];
            $thumb_width = $thumb_array[1];
            $thumb_height = $thumb_array[2];
            $replacement = "<img width='$thumb_width' height='$thumb_height' src='".$thumb_url."' alt='".eme_trans_sanitize_html($event['event_name'],$lang)."'/>";
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($legacy && $event && preg_match('/#_EVENTIMAGETHUMB\[(.+)\]/', $result, $matches)) {
         if (!empty($event['event_image_id'])) {
            $thumb_array = image_downsize( $event['event_image_id'], $matches[1]);
            $thumb_url = $thumb_array[0];
            $thumb_width = $thumb_array[1];
            $thumb_height = $thumb_array[2];
            $replacement = "<img width='$thumb_width' height='$thumb_height' src='".$thumb_url."' alt='".eme_trans_sanitize_html($event['event_name'],$lang)."'/>";
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_EVENTIMAGETHUMBURL\{(.+)\}/', $result, $matches)) {
         if (!empty($event['event_image_id'])) {
            $thumb_array = image_downsize( $event['event_image_id'], $matches[1]);
            $thumb_url = $thumb_array[0];
            $replacement = $thumb_url;
         }

      } elseif ($legacy && $event && preg_match('/#_EVENTIMAGETHUMBURL\[(.+)\]/', $result, $matches)) {
         if (!empty($event['event_image_id'])) {
            $thumb_array = image_downsize( $event['event_image_id'], $matches[1]);
            $thumb_url = $thumb_array[0];
            $replacement = $thumb_url;
         }

      } elseif ($event && preg_match('/#_EVENTPAGEURL\{(.+)\}/', $result, $matches)) {
         $events_page_link = eme_get_events_page(true, false);
         $replacement = add_query_arg(array('event_id'=>intval($matches[1])),$events_page_link);
         if (!empty($lang))
            $replacement = add_query_arg(array('lang'=>$lang),$replacement);

      } elseif ($legacy && $event && preg_match('/#_EVENTPAGEURL\[(.+)\]/', $result, $matches)) {
         $events_page_link = eme_get_events_page(true, false);
         $replacement = add_query_arg(array('event_id'=>intval($matches[1])),$events_page_link);
         if (!empty($lang))
            $replacement = add_query_arg(array('lang'=>$lang),$replacement);

      } elseif ($event && preg_match('/#_EVENTPAGEURL$/', $result)) {
         $replacement = eme_event_url($event,$lang);

      } elseif ($event && preg_match('/#_(NAME|EVENTNAME)$/', $result)) {
         $field = "event_name";
         if (isset($event[$field]))  $replacement = $event[$field];
         if ($target == "html") {
            $replacement = eme_trans_sanitize_html($replacement,$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate($replacement,$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate($replacement,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_EVENTID/', $result)) {
         $field = "event_id";
         $replacement = $event[$field];
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_DAYS_TILL_START/', $result)) {
         $now = date("Y-m-d");
         $replacement = eme_daydifference($now,$event['event_start_date']);

      } elseif ($event && preg_match('/#_DAYS_TILL_END/', $result)) {
         $now = date("Y-m-d");
         $replacement = eme_daydifference($now,$event['event_end_date']);

      } elseif ($event && preg_match('/#_EVENTPRICE$|#_PRICE$/', $result)) {
         $field = "price";
         if ($event[$field])
            $replacement = $event[$field];
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (($deprecated && $event && preg_match('/#_(EVENT)?PRICE(\d+)/', $result, $matches)) ||
                ($event && preg_match('/#_(EVENT)?PRICE\{(\d+)\}/', $result, $matches))) {
         $field_id = intval($matches[2]-1);
         if ($event["price"] && eme_is_multi($event["price"])) {
            $prices = eme_convert_multi2array($event["price"]);
            if (is_array($prices) && array_key_exists($field_id,$prices)) {
               $replacement = $prices[$field_id];
               if ($target == "html") {
                  $replacement = apply_filters('eme_general', $replacement); 
               } elseif ($target == "rss")  {
                  $replacement = apply_filters('eme_general_rss', $replacement);
               } else {
                  $replacement = apply_filters('eme_text', $replacement);
               }
            }
         }

      } elseif ($event && preg_match('/#_CURRENCY/', $result)) {
         $field = "currency";
         // currency is only important if the price is not empty as well
         if ($event['price'])
            $replacement = $event[$field];
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_ATTENDEES/', $result)) {
         $replacement=eme_get_attendees_list_for($event);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_BOOKINGS/', $result)) {
         $replacement=eme_get_bookings_list_for($event);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(CONTACTNAME|CONTACTPERSON)/', $result)) {
         $contact = eme_get_contact($event);
         if ($contact)
            $replacement = $contact->display_name;
         $replacement = eme_trans_sanitize_html($replacement,$lang);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(CONTACTEMAIL|PLAIN_CONTACTEMAIL)/', $result)) {
         $contact = eme_get_contact($event);
         if ($contact) {
            $replacement = $contact->user_email;
            if ($target == "html") {
               // ascii encode for primitive harvesting protection ...
               $replacement = eme_ascii_encode($replacement);
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_CONTACTPHONE/', $result)) {
         $contact = eme_get_contact($event);
         if ($contact) {
            $phone = eme_get_user_phone($contact->ID);
            // ascii encode for primitive harvesting protection ...
            $replacement=eme_ascii_encode($phone);
         }
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#[A-Za-z]$/', $result)) {
         // matches all PHP date placeholders for startdate-time
         $replacement=date_i18n( ltrim($result,"#"), strtotime( $event['event_start_date']." ".$event['event_start_time']));
         if (get_option('eme_time_remove_leading_zeros') && $result=="#i") {
            $replacement=ltrim($replacement,"0");
         }

      } elseif ($event && preg_match('/#@[A-Za-z]$/', $result)) {
         // matches all PHP time placeholders for enddate-time
         $replacement=date_i18n( ltrim($result,"#@"), strtotime( $event['event_end_date']." ".$event['event_end_time']));
         if (get_option('eme_time_remove_leading_zeros') && $result=="#@i") {
            $replacement=ltrim($replacement,"0");
         }

      } elseif ($event && preg_match('/#_EVENTCATEGORYIDS$/', $result) && get_option('eme_categories_enabled')) {
         $categories = $event['event_category_ids'];
         if ($target == "html") {
            $replacement = eme_trans_sanitize_html($categories,$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_trans_sanitize_html($categories,$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_trans_sanitize_html($categories,$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_(EVENT)?CATEGORIES$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_event_categories($event['event_id']);
         if ($target == "html") {
            $replacement = eme_trans_sanitize_html(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_LINKED(EVENT)?CATEGORIES$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_event_categories($event['event_id']);
         $cat_links = array();
         foreach ($categories as $category) {
            $cat_link=eme_event_category_url($category);
            if ($target == "html")
               array_push($cat_links,"<a href='$cat_link' title='".eme_trans_sanitize_html($category,$lang)."'>".eme_trans_sanitize_html($category,$lang)."</a>");
            else
               array_push($cat_links,"<a href='$cat_link' title='".eme_translate($category,$lang)."'>".eme_translate($category,$lang)."</a>");
         }
         $replacement = join(", ",$cat_links);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/^#_(EVENT)?CATEGORIES\{(.*?)\}\{(.*?)\}/', $result, $matches) && get_option('eme_categories_enabled')) {
         $include_cats=$matches[2];
         $exclude_cats=$matches[3];
         $extra_conditions_arr = array();
         if (!empty($include_cats))
            array_push($extra_conditions_arr, "category_id IN ($include_cats)");
         if (!empty($exclude_cats))
            array_push($extra_conditions_arr, "category_id NOT IN ($exclude_cats)");
         $extra_conditions = join(" AND ",$extra_conditions_arr);
         $categories = eme_get_event_categories($event['event_id'],$extra_conditions);
         if ($target == "html") {
            $replacement = eme_trans_sanitize_html(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($legacy && $event && preg_match('/^#_(EVENT)?CATEGORIES\[(.*?)\]\[(.*?)\]/', $result, $matches) && get_option('eme_categories_enabled')) {
         $include_cats=$matches[2];
         $exclude_cats=$matches[3];
         $extra_conditions_arr = array();
         if (!empty($include_cats))
            array_push($extra_conditions_arr, "category_id IN ($include_cats)");
         if (!empty($exclude_cats))
            array_push($extra_conditions_arr, "category_id NOT IN ($exclude_cats)");
         $extra_conditions = join(" AND ",$extra_conditions_arr);
         $categories = eme_get_event_categories($event['event_id'],$extra_conditions);
         if ($target == "html") {
            $replacement = eme_trans_sanitize_html(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$categories),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_LINKED(EVENT)?CATEGORIES\{(.*?)\}\{(.*?)\}/', $result, $matches) && get_option('eme_categories_enabled')) {
         $include_cats=$matches[2];
         $exclude_cats=$matches[3];
         $extra_conditions_arr = array();
         if (!empty($include_cats))
            array_push($extra_conditions_arr, "category_id IN ($include_cats)");
         if (!empty($exclude_cats))
            array_push($extra_conditions_arr, "category_id NOT IN ($exclude_cats)");
         $extra_conditions = join(" AND ",$extra_conditions_arr);
         $categories = eme_get_event_categories($event['event_id'],$extra_conditions);
         $cat_links = array();
         foreach ($categories as $category) {
            $cat_link=eme_event_category_url($category);
            if ($target == "html")
               array_push($cat_links,"<a href='$cat_link' title='".eme_trans_sanitize_html($category,$lang)."'>".eme_trans_sanitize_html($category,$lang)."</a>");
            else
               array_push($cat_links,"<a href='$cat_link' title='".eme_translate($category,$lang)."'>".eme_translate($category,$lang)."</a>");
         }
         $replacement = join(", ",$cat_links);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_LINKED(EVENT)?CATEGORIES\[(.*?)\]\[(.*?)\]/', $result, $matches) && get_option('eme_categories_enabled')) {
         $include_cats=$matches[2];
         $exclude_cats=$matches[3];
         $extra_conditions_arr = array();
         if (!empty($include_cats))
            array_push($extra_conditions_arr, "category_id IN ($include_cats)");
         if (!empty($exclude_cats))
            array_push($extra_conditions_arr, "category_id NOT IN ($exclude_cats)");
         $extra_conditions = join(" AND ",$extra_conditions_arr);
         $categories = eme_get_event_categories($event['event_id'],$extra_conditions);
         $cat_links = array();
         foreach ($categories as $category) {
            $cat_link=eme_event_category_url($category);
            if ($target == "html")
               array_push($cat_links,"<a href='$cat_link' title='".eme_trans_sanitize_html($category,$lang)."'>".eme_trans_sanitize_html($category,$lang)."</a>");
            else
               array_push($cat_links,"<a href='$cat_link' title='".eme_translate($category,$lang)."'>".eme_translate($category,$lang)."</a>");
         }
         $replacement = join(", ",$cat_links);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = eme_translate(join(", ",$cat_links),$lang);
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif (preg_match('/#_CALENDAR_DAY/', $result)) {
         $day_key = get_query_var('calendar_day');
         $replacement = date_i18n (get_option('date_format'), strtotime($day_key));
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } elseif ($target == "rss")  {
            $replacement = apply_filters('eme_general_rss', $replacement);
         } else {
            $replacement = apply_filters('eme_text', $replacement);
         }

      } elseif ($event && preg_match('/#_RECURRENCEDESC/', $result)) {
         if ($event ['recurrence_id']) {
            $replacement = eme_get_recurrence_desc ( $event ['recurrence_id'] );
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } elseif ($target == "rss")  {
               $replacement = apply_filters('eme_general_rss', $replacement);
            } else {
               $replacement = apply_filters('eme_text', $replacement);
            }
         }

      } elseif ($event && preg_match('/#_RSVPEND/', $result)) {
         // show the end date+time for which a user can rsvp for an event
         if (eme_is_event_rsvp($event)) {
               $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
               $rsvp_end_datetime = $event_start_datetime - $event['rsvp_number_days']*60*60*24 - $event['rsvp_number_hours']*60*60;
               $rsvp_end_date = eme_localised_date($rsvp_end_datetime,1);
               $rsvp_end_time = eme_localised_time($rsvp_end_datetime,1);
               $replacement = $rsvp_end_date." ".$rsvp_end_time;
         }

      } elseif (preg_match('/#_IS_RSVP_ENDED/', $result)) {
         if (eme_is_event_rsvp($event)) {
            $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
            if (time()+$event['rsvp_number_days']*60*60*24+$event['rsvp_number_hours']*60*60 > $event_start_datetime )
               $replacement = 1;
            else
               $replacement = 0;
         }

      } elseif (preg_match('/#_IS_SINGLE_DAY/', $result)) {
         if (eme_is_single_day_page())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_SINGLE_EVENT/', $result)) {
         if (eme_is_single_event_page())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_LOGGED_IN/', $result)) {
         if (is_user_logged_in())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_ADMIN_PAGE/', $result)) {
         if (is_admin())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_RSVP_ENABLED/', $result)) {
         if (eme_is_event_rsvp($event))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_PRIVATE_EVENT/', $result)) {
         if ($event ['event_status'] == STATUS_PRIVATE)
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_RECURRENT_EVENT/', $result)) {
         if ($event ['recurrence_id'])
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_ONGOING_EVENT/', $result)) {
         if (strtotime($event['event_start_date']." ".$event['event_start_time']) <= time() &&
             strtotime($event['event_end_date']." ".$event['event_end_time']) >= time())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_REGISTERED/', $result)) {
         if (is_user_logged_in() && eme_get_booking_ids_by_wp_id($current_userid,$event['event_id']))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_MULTIPRICE/', $result)) {
         if (eme_is_multi($event['price']))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_MULTISEAT/', $result)) {
         if (eme_is_multi($event['event_seats']))
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_ALLDAY/', $result)) {
         if ($event['event_properties']['all_day'])
            $replacement = 1;
         else
            $replacement = 0;

      } elseif ($event && preg_match('/#_IS_MULTIDAY/', $result)) {
         if (strtotime($event['event_start_date']) != strtotime($event['event_end_date']))
            $replacement = 1;
         else
            $replacement = 0;

      } else {
         $found = 0;
      }

      if ($found) {
         if ($need_escape)
            $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
         if ($need_urlencode)
            $replacement = rawurlencode($replacement);
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   # now handle all possible location placeholders
   # but the eme_replace_locations_placeholders can't do "do_shortcode" at the end, because
   # this would cause [eme_if] tags to be replaced here already, while some placeholders of the
   # event haven't been replaced yet (like time placeholders, and event details)
   $format = eme_replace_locations_placeholders ( $format, $location, $target, 0, $lang );

  // for extra date formatting, eg. #_{d/m/Y}
   preg_match_all("/#(ESC|URL)?@?_\{.*?\}/", $format, $results);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($results[0],'sort_stringlenth');
   foreach($results[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = '';
      if(substr($result, 0, 3 ) == "#@_") {
         $my_date = "event_end_date";
         $my_time = "event_end_time";
         $offset = 4;
      } else {
         $my_date = "event_start_date";
         $my_time = "event_start_time";
         $offset = 3;
      }

      $replacement = date_i18n(substr($result, $offset, (strlen($result)-($offset+1)) ), strtotime($event[$my_date]." ".$event[$my_time]));

      if ($need_escape)
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      if ($need_urlencode)
         $replacement = rawurlencode($replacement);
      $format = str_replace($orig_result, $replacement ,$format );
   }

   # we handle NOTES the last, this used to be the default behavior
   # so no placeholder replacement happened accidentaly in possible shortcodes inside #_NOTES
   # but since we have templates to aid in all that ...
   if (!$eme_enable_notes_placeholders)
      $format = eme_replace_notes_placeholders ( $format, $event, $target );
 
   // now, replace any language tags found in the format itself
   $format = eme_translate($format,$lang);

   if ($do_shortcode)
      return do_shortcode($format);
   else
      return $format;
}

function eme_sanitize_request( $value ) {
   global $wpdb;
   $value = esc_sql(strip_shortcodes($value));
   return $value;
}

function sort_stringlenth($a,$b){
   return strlen($b)-strlen($a);
}

function eme_trans_sanitize_html( $value, $lang='') {
   return eme_sanitize_html(eme_translate( $value,$lang));
}

function eme_translate ( $value, $lang='') {
   if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
      if (empty($lang))
         return qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
      else
         return qtrans_use($lang,$value);
   } else {
      return $value;
   }
}

function eme_sanitize_rss( $value ) {
   #$value =  str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", $value ) );
   return "<![CDATA[".$value."]]>";
}



function eme_htmlspecialchars(&$value) {
  //$value = htmlspecialchars($value, ENT_QUOTES');
  $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function eme_sanitize_html( $value ) {
   //return htmlentities($value,ENT_QUOTES,get_option('blog_charset'));
   if (!is_array($value))
      $value=htmlspecialchars($value,ENT_QUOTES);
   else
      array_walk_recursive($value, "eme_htmlspecialchars");
   return $value;
}

function eme_strip_tags ( $value ) {
   return preg_replace("/^\s*$/","",strip_tags(stripslashes($value)));
}

function admin_show_warnings() {
   global $plugin_page;

   $donation_done = get_option('eme_donation_done');
   if (!$donation_done)
      eme_explain_donation ();

   $say_hello = get_option('eme_hello_to_user');
   if ($say_hello)
      eme_hello_to_new_user ();

   $show_legacy_warning = get_option('eme_legacy_warning');
   if ($show_legacy_warning)
      eme_show_legacy_warning();

   if (get_option('eme_legacy'))
      eme_show_legacy_message();
   if (get_option('eme_deprecated'))
      eme_show_deprecated_message();
}


function eme_explain_dbupdate_done() {
   $advice = sprintf(__("It seems you upgraded Events Made Eeasy, the events database has been updated accordingly. Click <a href='%s'>here</a> to dismiss this message.",'eme'),add_query_arg(array("disable_update_message"=>"true")));
   ?>
<div id="message" class="update-nag"><p> <?php echo $advice; ?> </p></div>
<?php
}

function eme_explain_events_page_missing() {
   $advice = sprintf(__("Error: the special events page is not set or no longer exist, please set the option '%s' to an existing page or EME will not work correctly!",'eme'),__ ( 'Events page', 'eme' ));
   ?>
<div id="message" class="error"><p> <?php echo $advice; ?> </p></div>
<?php
}

function eme_explain_donation() {
   ?>
<div style="padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
    <div>
        <h3><?php echo __('Donate', 'eme'); ?></h3>
<?php
_e('If you find this plugin useful to you, please consider making a small donation to help contribute to my time invested and to further development. Thanks for your kind support!', 'eme');
?>
  <br /><br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHPwYJKoZIhvcNAQcEoIIHMDCCBywCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYCMdFm7KQ32WfqTnPlBvAYkyldCfENPogludyK+VXxu1KC6+sS4Rgy4FbimhwWBUoyF4GKgI8rzr4vDP30yAhK63B7wV/RVN+4TqPI66RIMkbVjA0Q3WahkgST77COLlAlhuSFgp2PdXzE3mDjj/FjaFHiZEnkQq5dPl+9E4bQ/nTELMAkGBSsOAwIaBQAwgbwGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIy2T+AYRc6zyAgZg6z1W2OuKxaEuCxOvo0SXEr5dBCsbRl0hmgKbX61UW4kXnGPzZalfE9N+Rv7hriPUoOppL8Q6w5CGjmBitc5GM5Aa2owrL0MJZUoK3ETbmJEOvr9u0Az2HkqumYi6NpMq+Zy1+pcb1JRLrm2Gdep4UVw7jVgqbh4FptDGJJ8p2mWiIKNMRQzk3B1IztehAtgsAxdC5wnqIVqCCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTExMDExOTE0MzU0NFowIwYJKoZIhvcNAQkEMRYEFKi6BynDfzarMWLtPReeeGpOfxi2MA0GCSqGSIb3DQEBAQUABIGAifGWMzPLVJ3Q+EcZ1lsnAZi+ATnUrz2mDCNi2Endh7oJEgZOa7iP08MgAJJHvRi8GIkt9aVquYa7KzEYr7JwLhJnhEoZ6YdG/EQC8xBlR6pe41aneNeR8GPBY8WC8S11OpsuQ4K3RdD5wvZFmTAuAjdSGIExS8Zyzj1tqk8/yas=-----END PKCS7-----
">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<?php
echo sprintf ( __ ( "<a href=\"%s\" title=\"I already donated\">I already donated.</a>", 'eme' ), add_query_arg (array("disable_donate_message"=>"true")));
?>
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

   </div>
</div>

<?php
}

function eme_hello_to_new_user() {
   global $current_user;
   get_currentuserinfo();
   $advice = sprintf ( __ ( "<p>Hey, <strong>%s</strong>, welcome to <strong>Events Made Easy</strong>! We hope you like it around here.</p> 
   <p>Now it's time to insert events lists through  <a href=\"%s\" title=\"Widgets page\">widgets</a>, <a href=\"%s\" title=\"Template tags documentation\">template tags</a> or <a href=\"%s\" title=\"Shortcodes documentation\">shortcodes</a>.</p>
   <p>By the way, have you taken a look at the <a href=\"%s\" title=\"Change settings\">Settings page</a>? That's where you customize the way events and locations are displayed.</p>
   <p>What? Tired of seeing this advice? I hear you, <a href=\"%s\" title=\"Don't show this advice again\">click here</a> and you won't see this again!</p>", 'eme' ), $current_user->display_name, admin_url("widgets.php"), 'http://www.e-dynamics.be/wordpress/#template-tags', 'http://www.e-dynamics.be/wordpress/#shortcodes', admin_url("admin.php?page=eme-options"), add_query_arg (array("disable_hello_to_user"=>"true")) );
   ?>
<div id="message" class="updated">
      <?php
   echo $advice;
   ?>
   </div>
<?php
}

function eme_show_legacy_warning() {
   $advice = sprintf ( __ ( "<p><strong>Events Made Easy placeholders warning</strong></p>
   <p>The legacy placeholders of Events Made Easy have been disabled. More info can be found in <a href=\"%s\" title=\"Legacy doc\">the documention</a></p>
   <p>What? Tired of seeing this advice? I hear you, <a href=\"%s\" title=\"Don't show this advice again\">click here</a> and you won't see this again!</p>", 'eme' ), 'http://www.e-dynamics.be/wordpress/?p=51559', add_query_arg (array("disable_legacy_warning"=>"true")) );
   ?>
<div id="message" class="updated">
      <?php
   echo $advice;
   ?>
   </div>
<?php
}

function eme_show_legacy_message() {
   $advice = sprintf ( __ ( "<p><strong>Events Made Easy placeholders warning</strong></p>
   <p>You have activated the use of legacy placeholder syntax. Please note that, although it works just fine, you should switch to the new syntax to avoid issues with regular wordpress shortcodes. More info can be found in <a href=\"%s\" title=\"Legacy doc\">the documention</a>. This message will go away when the use of legacy placeholder syntax has been disabled.</p>", 'eme' ), 'http://www.e-dynamics.be/wordpress/?p=51559');
   ?>
<div id="message" class="error">
      <?php
   echo $advice;
   ?>
   </div>
<?php
}

function eme_show_deprecated_message() {
   $advice = sprintf ( __ ( "<p><strong>Events Made Easy placeholders warning</strong></p>
   <p>The use of deprecated placeholders is still allowed. Please note that, although these work just fine, you should switch to the new syntax because these might go away at some time in the future. More info can be found in <a href=\"%s\" title=\"Deprecated doc\">the documention</a>. This message will go away when the use of deprecated placeholders has been disabled.</p>", 'eme' ), 'http://www.e-dynamics.be/wordpress/?p=51559');
   ?>
<div id="message" class="updated">
      <?php
   echo $advice;
   ?>
   </div>
<?php
}


?>
