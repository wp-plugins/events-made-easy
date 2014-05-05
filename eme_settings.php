<?php

function eme_add_options($reset=0) {
   $contact_person_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) will attend #_EVENTNAME on #m #d, #Y. He wants to reserve #_RESPSPACES space(s).<br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'eme') ;
   $contactperson_cancelled_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) has cancelled for #_EVENTNAME on #m #d, #Y. <br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'eme') ;
   $contact_person_pending_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) would like to attend #_EVENTNAME on #m #d, #Y. He wants to reserve #_RESPSPACES space(s).<br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'eme') ;
   $respondent_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>you have successfully reserved #_RESPSPACES space(s) for #_EVENTNAME.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_pending_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_RESPSPACES space(s) for #_EVENTNAME is pending.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_cancelled_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_RESPSPACES space(s) for #_EVENTNAME has been cancelled.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_denied_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_RESPSPACES space(s) for #_EVENTNAME has been denied.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_updated_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_RESPSPACES space(s) for #_EVENTNAME has been updated.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_recorded_ok_html_localizable = __('Your booking has been recorded','eme');
   $registration_form_format_localizable = "<table class='eme-rsvp-form'>
            <tr><th scope='row'>".__('Name', 'eme')."*:</th><td>#_NAME</td></tr>
            <tr><th scope='row'>".__('E-Mail', 'eme')."*:</th><td>#_EMAIL</td></tr>
            <tr><th scope='row'>".__('Phone number', 'eme').":</th><td>#_PHONE</td></tr>
            <tr><th scope='row'>".__('Seats', 'eme')."*:</th><td>#_SPACES</td></tr>
            <tr><th scope='row'>".__('Comment', 'eme').":</th><td>#_COMMENT</td></tr>
            #_CAPTCHAHTML{<tr><th scope='row'>Please fill in the code displayed here:</th><td>#_CAPTCHA</td></tr>}
            </table>
            #_SUBMIT
            ";
   $cancel_form_format_localizable = "<table class='eme-rsvp-form'>
            <tr><th scope='row'>".__('Name', 'eme')."*:</th><td>#_NAME</td></tr>
            <tr><th scope='row'>".__('E-Mail', 'eme')."*:</th><td>#_EMAIL</td></tr>
            #_CAPTCHAHTML{<tr><th scope='row'>Please fill in the code displayed here:</th><td>#_CAPTCHA</td></tr>}
            </table>
            #_SUBMIT
            ";
   
   $eme_options = array('eme_event_list_item_format' => DEFAULT_EVENT_LIST_ITEM_FORMAT,
   'eme_display_calendar_in_events_page' => 0,
   'eme_single_event_format' => DEFAULT_SINGLE_EVENT_FORMAT,
   'eme_event_page_title_format' => DEFAULT_EVENT_PAGE_TITLE_FORMAT,
   'eme_event_html_title_format' => DEFAULT_EVENT_HTML_TITLE_FORMAT,
   'eme_show_period_monthly_dateformat' => DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT,
   'eme_show_period_yearly_dateformat' => DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT,
   'eme_filter_form_format' => DEFAULT_FILTER_FORM_FORMAT,
   'eme_list_events_page' => 0,
   'eme_events_page_title' => DEFAULT_EVENTS_PAGE_TITLE,
   'eme_no_events_message' => DEFAULT_NO_EVENTS_MESSAGE,
   'eme_location_page_title_format' => DEFAULT_LOCATION_PAGE_TITLE_FORMAT,
   'eme_location_html_title_format' => DEFAULT_LOCATION_HTML_TITLE_FORMAT,
   'eme_location_baloon_format' => DEFAULT_LOCATION_BALLOON_FORMAT,
   'eme_location_event_list_item_format' => DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT,
   'eme_location_no_events_message' => DEFAULT_LOCATION_NO_EVENTS_MESSAGE,
   'eme_single_location_format' => DEFAULT_SINGLE_LOCATION_FORMAT,
   'eme_ical_title_format' => DEFAULT_ICAL_TITLE_FORMAT,
   'eme_ical_description_format' => DEFAULT_ICAL_DESCRIPTION_FORMAT,
   'eme_rss_main_title' => get_bloginfo('title')." - ".__('Events'),
   'eme_rss_main_description' => get_bloginfo('description')." - ".__('Events'),
   'eme_rss_description_format' => DEFAULT_RSS_DESCRIPTION_FORMAT,
   'eme_rss_title_format' => DEFAULT_RSS_TITLE_FORMAT,
   'eme_rss_show_pubdate' => 1,
   'eme_rss_pubdate_startdate' => 0,
   'eme_gmap_is_active'=> DEFAULT_GMAP_ENABLED,
   'eme_gmap_zooming'=> DEFAULT_GMAP_ZOOMING,
   'eme_global_zoom_factor'=> DEFAULT_GLOBAL_ZOOM_FACTOR,
   'eme_indiv_zoom_factor'=> DEFAULT_INDIV_ZOOM_FACTOR,
   'eme_global_maptype'=> DEFAULT_GLOBAL_MAPTYPE,
   'eme_indiv_maptype'=> DEFAULT_INDIV_MAPTYPE,
   'eme_seo_permalink'=> DEFAULT_SEO_PERMALINK,
   'eme_permalink_events_prefix' => 'events',
   'eme_permalink_locations_prefix' => 'locations',
   'eme_default_contact_person' => -1,
   'eme_captcha_for_booking' => 0 ,
   'eme_rsvp_mail_notify_is_active' => 1 ,
   'eme_contactperson_email_body' => preg_replace("/<br ?\/?>/", "\n", $contact_person_email_body_localizable),
   'eme_contactperson_cancelled_email_body' => preg_replace("/<br ?\/?>/", "\n", $contactperson_cancelled_email_body_localizable),
   'eme_contactperson_pending_email_body' => preg_replace("/<br ?\/?>/", "\n", $contact_person_pending_email_body_localizable),
   'eme_respondent_email_body' => preg_replace("/<br ?\/?>/", "\n", $respondent_email_body_localizable),
   'eme_registration_pending_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_pending_email_body_localizable),
   'eme_registration_cancelled_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_cancelled_email_body_localizable),
   'eme_registration_denied_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_denied_email_body_localizable),
   'eme_registration_updated_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_updated_email_body_localizable),
   'eme_registration_recorded_ok_html' => $registration_recorded_ok_html_localizable,
   'eme_registration_form_format' => $registration_form_format_localizable,
   'eme_cancel_form_format' => $cancel_form_format_localizable,
   'eme_rsvp_mail_port' => 25,
   'eme_deny_mail_event_edit' => 0,
   'eme_smtp_host' => 'localhost',
   'eme_mail_sender_name' => '',
   'eme_rsvp_mail_send_method' => 'smtp',
   'eme_rsvp_send_html' => 0,
   'eme_rsvp_mail_SMTPAuth' => 0,
   'eme_rsvp_registered_users_only' => 0,
   'eme_rsvp_reg_for_new_events' => 0,
   'eme_rsvp_require_approval' => 0,
   'eme_attendees_list_format' => DEFAULT_ATTENDEES_LIST_FORMAT,
   'eme_bookings_list_format' => DEFAULT_BOOKINGS_LIST_FORMAT,
   'eme_bookings_list_header_format' => DEFAULT_BOOKINGS_LIST_HEADER_FORMAT,
   'eme_bookings_list_footer_format' => DEFAULT_BOOKINGS_LIST_FOOTER_FORMAT,
   'eme_full_calendar_event_format' => DEFAULT_FULL_CALENDAR_EVENT_FORMAT,
   'eme_small_calendar_event_title_format' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_FORMAT,
   'eme_small_calendar_event_title_separator' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_SEPARATOR, 
   'eme_hello_to_user' => 1,
   'eme_smtp_debug' => 0,
   'eme_shortcodes_in_widgets' => 0,
   'eme_load_js_in_header' => 0,
   'eme_use_client_clock' => 0,
   'eme_donation_done' => 0,
   'eme_event_list_number_items'  => 10,
   'eme_use_select_for_locations' => DEFAULT_USE_SELECT_FOR_LOCATIONS,
   'eme_attributes_enabled' => DEFAULT_ATTRIBUTES_ENABLED,
   'eme_recurrence_enabled' => DEFAULT_RECURRENCE_ENABLED,
   'eme_rsvp_enabled' => DEFAULT_RSVP_ENABLED,
   'eme_rsvp_addbooking_submit_string' => DEFAULT_RSVP_ADDBOOKINGFORM_SUBMIT_STRING,
   'eme_rsvp_addbooking_min_spaces' => 1,
   'eme_rsvp_addbooking_max_spaces' => 10,
   'eme_rsvp_delbooking_submit_string' => DEFAULT_RSVP_DELBOOKINGFORM_SUBMIT_STRING,
   'eme_categories_enabled' => DEFAULT_CATEGORIES_ENABLED,
   'eme_cap_add_event' => DEFAULT_CAP_ADD_EVENT, 
   'eme_cap_author_event' => DEFAULT_CAP_AUTHOR_EVENT, 
   'eme_cap_publish_event' => DEFAULT_CAP_PUBLISH_EVENT,
   'eme_cap_list_events' => DEFAULT_CAP_LIST_EVENTS,
   'eme_cap_edit_events' => DEFAULT_CAP_EDIT_EVENTS,
   'eme_cap_add_locations' => DEFAULT_CAP_ADD_LOCATION,
   'eme_cap_author_locations' => DEFAULT_CAP_AUTHOR_LOCATION,
   'eme_cap_edit_locations' => DEFAULT_CAP_EDIT_LOCATIONS,
   'eme_cap_categories' => DEFAULT_CAP_CATEGORIES,
   'eme_cap_templates' => DEFAULT_CAP_TEMPLATES,
   'eme_cap_people' => DEFAULT_CAP_PEOPLE,
   'eme_cap_approve' => DEFAULT_CAP_APPROVE,
   'eme_cap_registrations' => DEFAULT_CAP_REGISTRATIONS,
   'eme_cap_forms' => DEFAULT_CAP_FORMS,
   'eme_cap_cleanup' => DEFAULT_CAP_CLEANUP,
   'eme_cap_settings' => DEFAULT_CAP_SETTINGS,
   'eme_cap_send_mails' => DEFAULT_CAP_SEND_MAILS,
   'eme_cap_send_other_mails' => DEFAULT_CAP_SEND_OTHER_MAILS,
   'eme_event_html_headers_format' => '',
   'eme_location_html_headers_format' => '',
   'eme_paypal_url' => PAYPAL_LIVE_URL,
   'eme_paypal_business' => '',
   'eme_paypal_s_encrypt' => 0,
   'eme_paypal_s_pubcert' => '',
   'eme_paypal_s_privkey' => '',
   'eme_paypal_s_paypalcert' => '',
   'eme_paypal_s_certid' => '',
   'eme_google_checkout_type' => GOOGLE_LIVE,
   'eme_google_merchant_id' => '',
   'eme_google_merchant_key' => '',
   'eme_2co_demo' => 0,
   'eme_2co_business' => '',
   'eme_2co_secret' => '',
   'eme_webmoney_demo' => 0,
   'eme_webmoney_purse' => '',
   'eme_webmoney_secret' => '',
   'eme_fdgg_url' => FDGG_LIVE_URL,
   'eme_fdgg_store_name' => '',
   'eme_fdgg_shared_secret' => '',
   'eme_event_initial_state' => STATUS_DRAFT,
   'eme_default_currency' => 'EUR',
   'eme_default_price' => '0',
   'eme_rsvp_number_days' => 0,
   'eme_rsvp_number_hours' => 0,
   'eme_thumbnail_size' => 'thumbnail',
   'eme_fb_app_id' => '',
   'eme_payment_form_header_format' => '',
   'eme_payment_form_footer_format' => '',
   'eme_payment_show_custom_return_page' => 0,
   'eme_payment_succes_format' => '',
   'eme_payment_fail_format' => '',
   'eme_payment_add_bookingid_to_return' => 0,
   'eme_loop_protection' => 'simple',
   'eme_enable_notes_placeholders' => 0,
   'eme_deprecated' => 1,
   'eme_legacy' => 0,
   'eme_legacy_warning' => 1
   );
   
   foreach($eme_options as $key => $value){
      eme_add_option($key, $value, $reset);
   }

   // remove some deprecated options
   $options = array ('eme_image_max_width', 'eme_image_max_height', 'eme_image_max_size');
   foreach ( $options as $opt ) {
      delete_option ( $opt );
   }
}

function eme_add_option($key, $value, $reset) {
   $option_val = get_option($key,"non_existing");
   if ($option_val=="non_existing" || $reset) {
      update_option($key, $value);
   }
}

////////////////////////////////////
// WP options registration/deletion
////////////////////////////////////
function eme_options_delete() {
   $options = array ('eme_version', 'eme_events_page', 'eme_display_calendar_in_events_page', 'eme_event_list_item_format_header', 'eme_event_list_item_format', 'eme_event_list_item_format_footer', 'eme_event_page_title_format', 'eme_event_html_title_format', 'eme_single_event_format', 'eme_list_events_page', 'eme_events_page_title', 'eme_no_events_message', 'eme_location_page_title_format','eme_location_html_title_format', 'eme_location_baloon_format', 'eme_single_location_format', 'eme_location_event_list_item_format', 'eme_show_period_monthly_dateformat','eme_show_period_yearly_dateformat', 'eme_location_no_events_message', 'eme_gmap_is_active', 'eme_gmap_zooming', 'eme_seo_permalink', 'eme_rss_main_title', 'eme_rss_main_description', 'eme_rss_title_format', 'eme_rss_description_format', 'eme_rss_show_pubdate', 'eme_rss_pubdate_startdate', 'eme_rsvp_mail_notify_is_active', 'eme_contactperson_email_body', 'eme_contactperson_cancelled_email_body', 'eme_contactperson_pending_email_body', 'eme_respondent_email_body', 'eme_registration_recorded_ok_html', 'eme_mail_sender_name', 'eme_smtp_username', 'eme_smtp_password', 'eme_default_contact_person','eme_captcha_for_booking', 'eme_mail_sender_address', 'eme_mail_receiver_address', 'eme_smtp_host', 'eme_rsvp_mail_send_method', 'eme_rsvp_mail_port', 'eme_rsvp_send_html', 'eme_rsvp_mail_SMTPAuth', 'eme_rsvp_registered_users_only', 'eme_rsvp_reg_for_new_events', 'eme_rsvp_require_approval', 'eme_rsvp_default_number_spaces', 'eme_rsvp_addbooking_submit_string', 'eme_rsvp_delbooking_submit_string', 'eme_image_max_width', 'eme_image_max_height', 'eme_image_max_size', 'eme_full_calendar_event_format', 'eme_use_select_for_locations', 'eme_attributes_enabled', 'eme_recurrence_enabled','eme_rsvp_enabled','eme_categories_enabled','eme_small_calendar_event_title_format','eme_small_calendar_event_title_separator','eme_registration_pending_email_body','eme_registration_denied_email_body','eme_registration_updated_email_body','eme_registration_cancelled_email_body','eme_attendees_list_format','eme_bookings_list_format','eme_bookings_list_header_format','eme_bookings_list_footer_format','eme_uninstall_drop_tables','eme_uninstall_drop_data','eme_time_remove_leading_zeros','eme_rsvp_hide_full_events','eme_donation_done','eme_hello_to_user','eme_filter_form_format','eme_rsvp_addbooking_min_spaces','eme_rsvp_addbooking_max_spaces','eme_shortcodes_in_widgets','eme_load_js_in_header','eme_use_client_clock','eme_event_list_number_items', 'eme_cap_add_event', 'eme_cap_author_event', 'eme_cap_publish_event', 'eme_cap_edit_events', 'eme_cap_list_events', 'eme_cap_add_locations', 'eme_cap_edit_locations', 'eme_cap_author_locations', 'eme_cap_categories','eme_cap_templates', 'eme_cap_people', 'eme_cap_approve', 'eme_cap_registrations', 'eme_cap_forms', 'eme_cap_cleanup', 'eme_cap_settings', 'eme_cap_send_mails', 'eme_cap_send_other_mails', 'eme_event_html_headers_format', 'eme_location_html_headers_format','eme_permalink_events_prefix','eme_permalink_locations_prefix','eme_paypal_url','eme_paypal_business', 'eme_2co_business', 'eme_2co_secret', 'eme_2co_demo', 'eme_webmoney_purse', 'eme_webmoney_secret', 'eme_webmoney_demo', 'eme_google_checkout_type', 'eme_google_merchant_id', 'eme_google_merchant_key', 'eme_location_list_format_header', 'eme_location_list_format_item', 'eme_location_list_format_footer','eme_event_initial_state', 'eme_registration_form_format', 'eme_cancel_form_format', 'eme_smtp_debug','eme_default_currency','eme_default_price', 'eme_rsvp_number_days', 'eme_rsvp_number_hours', 'eme_paypal_s_encrypt', 'eme_paypal_s_pubcert', 'eme_paypal_s_privkey', 'eme_paypal_s_paypalcert', 'eme_paypal_s_certid', 'eme_thumbnail_size','eme_fdgg_url','eme_fdgg_store_name','eme_fdgg_shared_secret','eme_fb_app_id','eme_loop_protection','eme_ical_title_format','eme_ical_description_format','eme_global_zoom_factor','eme_indiv_zoom_factor','eme_global_maptype','eme_indiv_maptype','eme_payment_form_header_format','eme_payment_form_footer_format','eme_enable_notes_placeholders','eme_payment_succes_format','eme_payment_fail_format','eme_payment_add_bookingid_to_return','eme_payment_show_custom_return_page','eme_deny_mail_event_edit','eme_legacy','eme_legacy_warning','eme_deprecated');
   foreach ( $options as $opt ) {
      delete_option ( $opt );
   }
}

function eme_metabox_options_delete() {
   global $wpdb;
   $screens = array( 'events_page_eme-new_event', 'toplevel_page_events-manager' );
   foreach ($screens as $screen) {
      foreach ( array( 'metaboxhidden', 'closedpostboxes', 'wp_metaboxorder','meta-box-order', 'screen_layout' ) as $option )
         $keys[] = "'{$option}_{$screen}'";
   }
   $keys = '( ' . implode( ', ', $keys ) . ' )';
   $wpdb->query( "
         DELETE FROM {$wpdb->usermeta}
         WHERE meta_key IN {$keys}
         " );
}

function eme_options_register() {

   // only the options you want changed in the Settings page, not eg. eme_hello_to_user, eme_donation_done
   // and only those for the tab shown, otherwise the others get reset to empty values
   // The tab value is set in the form in the function eme_options_page. It needs to be set there as a hidden value when calling options.php, otherwise
   //    it won't be known here and all values will be lost.
   if (!isset($_POST['option_page']) || ($_POST['option_page'] != "eme-options"))
      return;
   $options = array();
   $tab = isset( $_POST['tab'] ) ? esc_attr($_POST['tab']) : 'general';
   switch ( $tab ){
	      case 'general' :
                 $options = array ('eme_use_select_for_locations','eme_recurrence_enabled', 'eme_rsvp_enabled', 'eme_categories_enabled', 'eme_attributes_enabled', 'eme_gmap_is_active', 'eme_gmap_zooming', 'eme_load_js_in_header','eme_use_client_clock','eme_uninstall_drop_data','eme_shortcodes_in_widgets','eme_loop_protection','eme_enable_notes_placeholders','eme_legacy','eme_deprecated');
	         break;
	      case 'seo' :
                 $options = array ('eme_seo_permalink','eme_permalink_events_prefix','eme_permalink_locations_prefix');
	         break;
	      case 'access' :
                 $options = array ('eme_cap_add_event', 'eme_cap_author_event', 'eme_cap_publish_event', 'eme_cap_list_events', 'eme_cap_edit_events', 'eme_cap_add_locations', 'eme_cap_author_locations', 'eme_cap_edit_locations', 'eme_cap_categories', 'eme_cap_templates', 'eme_cap_people', 'eme_cap_approve', 'eme_cap_registrations', 'eme_cap_forms', 'eme_cap_cleanup', 'eme_cap_settings','eme_cap_send_mails','eme_cap_send_other_mails');
	         break;
	      case 'events' :
                 $options = array ('eme_events_page','eme_list_events_page','eme_display_calendar_in_events_page','eme_event_list_number_items','eme_event_initial_state','eme_time_remove_leading_zeros','eme_event_list_item_format_header','eme_event_list_item_format','eme_event_list_item_format_footer','eme_event_page_title_format','eme_event_html_title_format','eme_single_event_format','eme_show_period_monthly_dateformat','eme_show_period_yearly_dateformat','eme_events_page_title','eme_no_events_message','eme_filter_form_format');
	         break;
	      case 'calendar' :
                 $options = array ('eme_small_calendar_event_title_format','eme_small_calendar_event_title_separator','eme_full_calendar_event_format');
	         break;
	      case 'locations' :
                 $options = array ('eme_location_list_format_header','eme_location_list_format_item','eme_location_list_format_footer','eme_location_page_title_format','eme_location_html_title_format','eme_single_location_format','eme_location_baloon_format','eme_location_event_list_item_format','eme_location_no_events_message',);
	         break;
	      case 'rss' :
                 $options = array ('eme_rss_main_title','eme_rss_main_description','eme_rss_title_format','eme_rss_description_format','eme_rss_show_pubdate','eme_rss_pubdate_startdate','eme_ical_description_format','eme_ical_title_format');
	         break;
	      case 'rsvp' :
                 $options = array ('eme_default_contact_person','eme_rsvp_registered_users_only','eme_rsvp_reg_for_new_events','eme_rsvp_require_approval','eme_rsvp_default_number_spaces','eme_rsvp_addbooking_min_spaces','eme_rsvp_addbooking_max_spaces','eme_captcha_for_booking','eme_rsvp_hide_full_events','eme_rsvp_addbooking_submit_string','eme_rsvp_delbooking_submit_string','eme_attendees_list_format','eme_bookings_list_header_format','eme_bookings_list_format','eme_bookings_list_footer_format','eme_registration_recorded_ok_html','eme_registration_form_format', 'eme_cancel_form_format', 'eme_rsvp_number_days', 'eme_rsvp_number_hours');
	         break;
	      case 'mail' :
                 $options = array ('eme_rsvp_mail_notify_is_active','eme_deny_mail_event_edit','eme_contactperson_email_body','eme_contactperson_cancelled_email_body','eme_contactperson_pending_email_body','eme_respondent_email_body','eme_registration_pending_email_body','eme_registration_cancelled_email_body','eme_registration_denied_email_body','eme_registration_updated_email_body','eme_mail_sender_name','eme_mail_sender_address','eme_rsvp_mail_send_method','eme_smtp_host','eme_rsvp_mail_port','eme_rsvp_mail_SMTPAuth','eme_smtp_username','eme_smtp_password', 'eme_smtp_debug','eme_rsvp_send_html');
	         break;
	      case 'payments' :
                 $options = array ('eme_payment_form_header_format','eme_payment_form_footer_format','eme_payment_show_custom_return_page','eme_payment_succes_format','eme_payment_fail_format','eme_payment_add_bookingid_to_return','eme_default_currency','eme_default_price','eme_paypal_url','eme_paypal_business','eme_2co_demo','eme_2co_business','eme_2co_secret','eme_google_checkout_type','eme_google_merchant_id','eme_google_merchant_key','eme_webmoney_purse', 'eme_webmoney_secret', 'eme_webmoney_demo', 'eme_paypal_s_encrypt', 'eme_paypal_s_pubcert', 'eme_paypal_s_privkey', 'eme_paypal_s_paypalcert', 'eme_paypal_s_certid','eme_fdgg_url','eme_fdgg_store_name','eme_fdgg_shared_secret');
	         break;
	      case 'other' :
                 $options = array ('eme_thumbnail_size','eme_image_max_width','eme_image_max_height','eme_image_max_size','eme_event_html_headers_format','eme_location_html_headers_format','eme_fb_app_id','eme_global_zoom_factor','eme_indiv_zoom_factor','eme_global_maptype','eme_indiv_maptype');
	         break;
   }

   foreach ( $options as $opt ) {
      register_setting ( 'eme-options', $opt, '' );
   }
}

function eme_handle_get() {
   global $plugin_page;
   if ( !is_admin() || !preg_match('/^eme-|events-manager/', $plugin_page) )
      return;

   // Disable Hello to new user if requested
   if (current_user_can( get_option('eme_cap_settings') ) && isset($_GET['disable_hello_to_user']) && $_GET['disable_hello_to_user'] == 'true')
      update_option('eme_hello_to_user', 0 );

   // Disable donation message if requested
   if (current_user_can( get_option('eme_cap_settings') ) && isset($_GET['disable_donate_message']) && $_GET['disable_donate_message'] == 'true')
      update_option('eme_donation_done', 1 );

   // Disable legacy warning
   if (current_user_can( get_option('eme_cap_settings') ) && isset($_GET['disable_legacy_warning']) && $_GET['disable_legacy_warning'] == 'true')
      update_option('eme_legacy_warning', 0 );

   // do the UTF-8 conversion if wanted
   if (current_user_can( get_option('eme_cap_settings') ) && isset($_GET['do_character_conversion']) && $_GET['do_character_conversion'] == 'true' && $wpdb->has_cap('collation')) {
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

   // if settings have been changed, check if the SEO rules need to be flushed
   if ($plugin_page == 'eme-options' && isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
      $old_events_page=get_option('eme_old_events_page');
      $events_page=get_option('eme_events_page');
      if ($events_page != $old_events_page) {
         eme_flushRules();
         update_option('eme_old_events_page',$events_page);
      }
   }
}

function eme_admin_tabs( $current = 'homepage' ) {
    $tabs = array( 'general' => 'General',
                   'access' => 'Access',
                   'seo' => 'SEO',
                   'events' => 'Events',
                   'locations' => 'Locations',
                   'calendar' => 'Calendar',
                   'rss' =>'RSS',
                   'rsvp' =>'RSVP',
                   'mail' =>'Mail',
                   'payments' =>'Payments',
                   'other' =>'Other'
                 );
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    $eme_settings_url=admin_url("admin.php?page=eme-options");
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='$eme_settings_url&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

// Function composing the options page
function eme_options_page() {
   global $plugin_page;
   if ($plugin_page == 'eme-options') {
      $tab = isset( $_GET['tab'] ) ? esc_attr($_GET['tab']) : 'general';
      eme_admin_tabs($tab);
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
<input type='hidden' name='tab' value='<?php echo $tab;?>'>
<?php
   settings_fields ( 'eme-options' );
   switch ( $tab ) {
	      case 'general' :
?>

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
   eme_options_select ( __('Theme loop protection','eme'), 'eme_loop_protection', array ('simple' => __('Simple loop protection (default)','eme'), 'older' => __ ( 'Loop protection for older or misbehaving themes', 'eme' ), 'desperate' => __('Last attempt at loop protection (if all else fails)','eme')), __('Choose the level of loop protection against the_content filter you want. Depending on the theme you may need to change this.','eme') );
   eme_options_radio_binary ( __ ( 'Delete all EME data when upgrading or deactivating?', 'eme' ), 'eme_uninstall_drop_data', __ ( 'Check this option if you want to delete all EME data (database tables and options) when upgrading or deactivating the plugin.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Enable shortcodes in widgets', 'eme' ), 'eme_shortcodes_in_widgets', __ ( 'Check this option if you want to enable the use of shortcodes in widgets (affects shortcodes of any plugin used in widgets, so use with care).', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Enable placeholders in event notes', 'eme' ), 'eme_enable_notes_placeholders', __ ( 'Check this option if you want to enable the use of placeholders in the event notes. By default placeholders in notes are not being touched at all so as not to interfere with possible format settings for other shortcodes you can/want to use, so use with care.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Allow deprecated placeholders', 'eme' ), 'eme_deprecated', __ ( 'Check this option if you want to use some deprecated placeholders.', 'eme' ) .' '.__('For more information, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?p=51559'>".__('the documentation', 'eme').'</a>' );
   eme_options_radio_binary ( __ ( 'Re-enable legacy placeholder syntax', 'eme' ), 'eme_legacy', __ ( 'Check this option if you want to re-enable the use of the older placeholder syntax with square brackets. Be aware of the fact that the new placeholder syntax will not work then.', 'eme' ) .' '.__('For more information, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?p=51559'>".__('the documentation', 'eme').'</a>' );
   ?>
</table>

<?php
	      break;
	      case 'seo' :
?>

<h3><?php _e ( 'Permalink options', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Enable event permalinks if possible?','eme' ), 'eme_seo_permalink', __ ( 'If Yes, EME will render SEO permalinks if permalinks are activated.', 'eme' ) . "<br \><strong>" . __ ( 'It is necessary to click \'Save Changes\' on the  WordPress \'Settings/Permalinks\' page before you will see the effect of this change.','eme' )."</strong>");
   eme_options_input_text ( __('Events permalink prefix', 'eme' ), 'eme_permalink_events_prefix', __( 'The permalink prefix used for events and the calendar.','eme') );
   eme_options_input_text ( __('Locations permalink prefix', 'eme' ), 'eme_permalink_locations_prefix', __( 'The permalink prefix used for locations.','eme') );
   ?>
</table>

<?php
	      break;
	      case 'access' :
?>

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
   eme_options_select (__('Edit templates','eme'), 'eme_cap_templates', eme_get_all_caps (), sprintf(__('Permission needed to edit all templates. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_TEMPLATES)) );
   eme_options_select (__('View people','eme'), 'eme_cap_people', eme_get_all_caps (), sprintf(__('Permission needed to view registered people info. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_PEOPLE)) );
   eme_options_select (__('Approve registrations','eme'), 'eme_cap_approve', eme_get_all_caps (), sprintf(__('Permission needed to approve pending registrations. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_APPROVE)) );
   eme_options_select (__('Edit registrations','eme'), 'eme_cap_registrations', eme_get_all_caps (), sprintf(__('Permission needed to edit approved registrations. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_REGISTRATIONS)) );
   eme_options_select (__('Send Mails','eme'), 'eme_cap_send_mails', eme_get_all_caps (), sprintf(__('Permission needed to send mails for own events. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_SEND_MAILS)) );
   eme_options_select (__('Send Other Mails','eme'), 'eme_cap_send_other_mails', eme_get_all_caps (), sprintf(__('Permission needed to send mails for any event. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_SEND_OTHER_MAILS)) );
   eme_options_select (__('Edit form fields','eme'), 'eme_cap_forms', eme_get_all_caps (), sprintf(__('Permission needed to edit form fields. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_FORMS)) );
   eme_options_select (__('Cleanup','eme'), 'eme_cap_cleanup', eme_get_all_caps (), sprintf(__('Permission needed to execute cleanup actions. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_CLEANUP)) );
   eme_options_select (__('Edit settings','eme'), 'eme_cap_settings', eme_get_all_caps (),sprintf(__('Permission needed to edit settings. Default: %s','eme'), eme_capNamesCB(DEFAULT_CAP_SETTINGS)) );
   ?>
</table>

<?php
	      break;
	      case 'events' :
?>

<h3><?php _e ( 'Events page', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_select ( __ ( 'Events page', 'eme' ), 'eme_events_page', eme_get_all_pages (), __ ( 'This option allows you to select which page to use as an events page.', 'eme' )."<br /><strong>".__ ( 'The content of this page (including shortcodes of any kind) will be ignored completely and dynamically replaced by events data.','eme' )."</strong>" );
   eme_options_radio_binary ( __ ( 'Show events page in lists?', 'eme' ), 'eme_list_events_page', __ ( 'Check this option if you want the events page to appear together with other pages in pages lists.', 'eme' )."<br /><strong>".__ ( 'This option should no longer be used, it will be deprecated. Using the [eme_events] shortcode in a self created page is recommended.', 'eme' )."</strong>" ); 
   eme_options_radio_binary ( __ ( 'Display calendar in events page?', 'eme' ), 'eme_display_calendar_in_events_page', __ ( 'This option allows to display the calendar in the events page, instead of the default list. It is recommended not to display both the calendar widget and a calendar page.','eme' ) );
   eme_options_input_text ( __('Number of events to show in lists', 'eme' ), 'eme_event_list_number_items', __( 'The number of events to show in a list if no specific limit is specified (used in the shortcode eme_events, RSS feed, the placeholders #_NEXTEVENTS and #_PASTEVENTS, ...). Use 0 for no limit.','eme') );
   eme_options_select (__('State for new event','eme'), 'eme_event_initial_state', eme_status_array(), __ ('Initial state for a new event','eme') );
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
   eme_options_input_text ( __ ( 'Monthly period date format', 'eme' ), 'eme_show_period_monthly_dateformat', __ ( 'The format of the date-string used when you use showperiod=monthly as an option to &#91;the eme_events] shortcode, also used for monthly pagination. Use php date() compatible settings.', 'eme') . __( ' The default is: ','eme'). DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT );
   eme_options_input_text ( __ ( 'Yearly period date format', 'eme' ), 'eme_show_period_yearly_dateformat', __ ( 'The format of the date-string used when you use showperiod=yearly as an option to &#91;the eme_events] shortcode, also used for yearly pagination. Use php date() compatible settings.', 'eme') . __( ' The default is: ','eme'). DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT );
   eme_options_input_text ( __ ( 'Events page title', 'eme' ), 'eme_events_page_title', __ ( 'The title on the multiple events page.', 'eme' ) );
   eme_options_input_text ( __ ( 'No events message', 'eme' ), 'eme_no_events_message', __ ( 'The message displayed when no events are available.', 'eme' ) );
   ?>
</table>
<h3><?php _e ( 'Events filtering format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Default event list filtering format', 'eme' ), 'eme_filter_form_format', __ ( 'This defines the layout of the event list filtering form when using the shortcode <code>[eme_filterform]</code>. Use <code>#_FILTER_CATS</code>, <code>#_FILTER_LOCS</code>, <code>#_FILTER_TOWNS</code>, <code>#_FILTER_WEEKS</code>, <code>#_FILTER_MONTHS</code>.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=28'>".__('the documentation', 'eme').'</a>' );
   ?>
</table>

<?php
	      break;
	      case 'calendar' :
?>

<h3><?php _e ( 'Calendar format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'Small calendar title', 'eme' ), 'eme_small_calendar_event_title_format', __ ( 'The format of the title, corresponding to the text that appears when hovering on an eventful calendar day.', 'eme' ) );
   eme_options_input_text ( __ ( 'Small calendar title separator', 'eme' ), 'eme_small_calendar_event_title_separator', __ ( 'The separator appearing on the above title when more than one event is taking place on the same day.', 'eme' ) );
   eme_options_input_text ( __ ( 'Full calendar events format', 'eme' ), 'eme_full_calendar_event_format', __ ( 'The format of each event when displayed in the full calendar. Remember to include <code>li</code> tags before and after the event.', 'eme' ) );
   ?>
</table>

<?php
	      break;
	      case 'locations' :
?>

<h3><?php _e ( 'Locations format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Default location list format header', 'eme' ), 'eme_location_list_format_header', __( 'This content will appear just above your code for the default location list format. If you leave this empty, the value <code>&lt;ul class=\'eme_locations_list\'&gt;</code> will be used.<br/>Used by the shortcode <code>[eme_locations]</code>', 'eme' ) );
   eme_options_textarea ( __ ( 'Default location list item format', 'eme' ), 'eme_location_list_format_item', __ ( 'The format of a location in a location list. If you leave this empty, the value <code>&lt;li class=\"location-#_LOCATIONID\"&gt;#_LOCATIONNAME&lt;/li&gt;</code> will be used.<br/>See the documentation for a list of available placeholders for locations.<br/>Used by the shortcode <code>[eme_locations]</code>', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=26'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Default location list format footer', 'eme' ), 'eme_location_list_format_footer', __ ( 'This content will appear just below your code for the default location list format. If you leave this empty, the value <code>&lt;/ul&gt;</code> will be used.<br/>Used by the shortcode <code>[eme_locations]</code>', 'eme' ) );

   eme_options_input_text ( __ ( 'Single location page title format', 'eme' ), 'eme_location_page_title_format', __ ( 'The format of a single location page title.<br/>Follow the previous formatting instructions.', 'eme' ) );
   eme_options_input_text ( __ ( 'Single location html title format', 'eme' ), 'eme_location_html_title_format', __ ( 'The format of a single location html page title.<br/>Follow the previous formatting instructions.', 'eme' ). __( ' The default is: ','eme'). DEFAULT_LOCATION_HTML_TITLE_FORMAT);
   eme_options_textarea ( __ ( 'Default single location page format', 'eme' ), 'eme_single_location_format', __ ( 'The format of a single location page.<br/>Insert one or more of the following placeholders: <code>#_LOCATIONNAME</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_DESCRIPTION</code>.<br/> Use <code>#_MAP</code> to display a map of the event location, and <code>#_IMAGE</code> to display an image of the location.<br/> Use <code>#_NEXTEVENTS</code> to insert a list of the upcoming events, <code>#_PASTEVENTS</code> for a list of past events, <code>#_ALLEVENTS</code> for a list of all events taking place in this location.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=26'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Default location balloon format', 'eme' ), 'eme_location_baloon_format', __ ( 'The format of the text appearing in the balloon describing the location in the map.<br/>Insert one or more of the following placeholders: <code>#_LOCATIONNAME</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_DESCRIPTION</code>,<code>#_IMAGE</code>, <code>#_LOCATIONPAGEURL</code> or <code>#_DIRECTIONS</code>.', 'eme' ) );
   eme_options_textarea ( __ ( 'Default location event list format', 'eme' ), 'eme_location_event_list_item_format', __ ( 'The format of the events list inserted in the location page through the <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> element. <br/> Follow the events formatting instructions', 'eme' ) );
   eme_options_textarea ( __ ( 'Default no events message', 'eme' ), 'eme_location_no_events_message', __ ( 'The message to be displayed in the list generated by <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> when no events are available.', 'eme' ) );
   ?>
</table>

<?php
	      break;
	      case 'rss' :
?>

<h3><?php _e ( 'RSS and ICAL feed format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'RSS main title', 'eme' ), 'eme_rss_main_title', __ ( 'The main title of your RSS events feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS main description', 'eme' ), 'eme_rss_main_description', __ ( 'The main description of your RSS events feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS title format', 'eme' ), 'eme_rss_title_format', __ ( 'The format of the title of each item in the events RSS feed.', 'eme' ) );
   eme_options_textarea ( __ ( 'RSS description format', 'eme' ), 'eme_rss_description_format', __ ( 'The format of the description of each item in the events RSS feed. Follow the previous formatting instructions.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'RSS Pubdate usage', 'eme' ), 'eme_rss_show_pubdate', __ ( 'Show the event creation/modification date as PubDate info in the in the events RSS feed.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'RSS Pubdate is start date', 'eme' ), 'eme_rss_pubdate_startdate', __ ( 'If you select this, the pubDate field in RSS will be the event start date, not the modification date.', 'eme' ) );
   eme_options_input_text ( __ ( 'ICAL title format', 'eme' ), 'eme_ical_title_format', __ ( 'The format of the title of each item in the events ICAL feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'ICAL description format', 'eme' ), 'eme_ical_description_format', __ ( 'The format of the description of each item in the events ICAL feed. Follow the previous formatting instructions.', 'eme' ) );
   ?>
</table>

<?php
	      break;
	      case 'rsvp' :
?>

<h3><?php _e ( 'RSVP: registrations and bookings', 'eme' ); ?></h3>
<table class='form-table'>
     <?php
   $indexed_users[-1]=__('Event author','eme');
   $indexed_users+=eme_get_indexed_users();
   eme_options_select ( __ ( 'Default contact person', 'eme' ), 'eme_default_contact_person', $indexed_users, __ ( 'Select the default contact person. This user will be employed whenever a contact person is not explicitly specified for an event', 'eme' ) );
   eme_options_radio_binary ( __ ( 'By default enable registrations for new events?', 'eme' ), 'eme_rsvp_reg_for_new_events', __ ( 'Check this option if you want to enable registrations by default for new events.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'By default require approval for registrations?', 'eme' ), 'eme_rsvp_require_approval', __ ( 'Check this option if you want by default that new registrations require approval.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'By default require WP membership to be able to register?', 'eme' ), 'eme_rsvp_registered_users_only', __ ( 'Check this option if you want by default that only WP registered users can book for an event.', 'eme' ) );
   eme_options_input_text ( __ ( 'Default number of spaces', 'eme' ), 'eme_rsvp_default_number_spaces', __ ( 'The default number of spaces an event has.', 'eme' ) );
   eme_options_input_text ( __ ( 'Min number of spaces to book', 'eme' ), 'eme_rsvp_addbooking_min_spaces', __ ( 'The minimum number of spaces a person can book in one go (it can be 0, for e.g. just an attendee list).', 'eme' ) );
   eme_options_input_text ( __ ( 'Max number of spaces to book', 'eme' ), 'eme_rsvp_addbooking_max_spaces', __ ( 'The maximum number of spaces a person can book in one go.', 'eme' ) );
   eme_options_input_text ( __ ( 'By default allow RSVP until this many days before the event starts', 'eme' ), 'eme_rsvp_number_days', __ ( 'By default allow RSVP until this many days before the event starts.', 'eme' ) );
   eme_options_input_text ( __ ( 'By default allow RSVP until this many hours before the event starts', 'eme' ), 'eme_rsvp_number_hours', __ ( 'By default allow RSVP until this many hours before the event starts.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Use captcha for booking form?', 'eme' ), 'eme_captcha_for_booking', __ ( 'Check this option if you want to use a captcha on the booking form, to thwart spammers a bit.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Hide fully booked events?', 'eme' ), 'eme_rsvp_hide_full_events', __ ( 'Check this option if you want to hide events that are fully booked from the calendar and events listing in the front.', 'eme' ) );
   eme_options_input_text ( __ ( 'Add booking form submit text', 'eme' ), 'eme_rsvp_addbooking_submit_string', __ ( "The string of the submit button on the add booking form", 'eme' ) );
   eme_options_input_text ( __ ( 'Delete booking form submit text', 'eme' ), 'eme_rsvp_delbooking_submit_string', __ ( "The string of the submit button on the delete booking form", 'eme' ) );
   eme_options_input_text ( __ ( 'Attendees list format', 'eme' ), 'eme_attendees_list_format', __ ( "The format for the attendees list when using the <code>#_ATTENDEES</code> placeholder.", 'eme' ). __('For all placeholders you can use here, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=48'>".__('the documentation', 'eme').'</a>' );
   eme_options_input_text ( __ ( 'Bookings list header format', 'eme' ), 'eme_bookings_list_header_format', __ ( "The header format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'eme' ). sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_BOOKINGS_LIST_HEADER_FORMAT)));
   eme_options_input_text ( __ ( 'Bookings list format', 'eme' ), 'eme_bookings_list_format', __ ( "The format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'eme' ). __('For all placeholders you can use here, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=45'>".__('the documentation', 'eme').'</a>' .__('For more information about form fields, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'eme').'</a>' );
   eme_options_input_text ( __ ( 'Bookings list footer format', 'eme' ), 'eme_bookings_list_footer_format', __ ( "The footer format for the bookings list when using the <code>#_BOOKINGS</code> placeholder.", 'eme' ). sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_BOOKINGS_LIST_FOOTER_FORMAT)));
   eme_options_input_text ( __ ( 'Booking recorded message', 'eme' ), 'eme_registration_recorded_ok_html', __ ( "The text (html allowed) shown to the user when the booking has been made successfully.", 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSVP: form format', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      eme_options_textarea (__('Form format','eme'),'eme_registration_form_format', __("The look and feel of the form for registrations. #_NAME, #_EMAIL and #_SEATS are obligated fields, if not present then the form will not be shown.",'eme')  .'<br/>'.__('For more information about form fields, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'eme').'</a>');
      eme_options_textarea (__('Cancel form format','eme'),'eme_cancel_form_format', __("The look and feel of the cancel form for registrations. #_NAME and #_EMAIL are obligated fields, if not present then the form will not be shown.", 'eme').'<br/>'.__('For more information about form fields, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'eme').'</a>');
   ?>
</table>


<?php
	      break;
	      case 'mail' :
?>

<h3><?php _e ( 'RSVP: mail options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_radio_binary ( __ ( 'Enable the RSVP e-mail notifications?', 'eme' ), 'eme_rsvp_mail_notify_is_active', __ ( 'Check this option if you want to receive an email when someone books places for your events.', 'eme' ) );
   ?>
</table>
<table id="rsvp_mail_notify-data" class='form-table'>
   <?php
   eme_options_radio_binary ( __ ( 'Send HTML mails', 'eme' ), 'eme_rsvp_send_html', __ ( 'Check this option if you want to use html in the mails being sent.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Quick deny send mails', 'eme' ), 'eme_deny_mail_event_edit', __ ( 'Check this option if you want to sent mails when denying a registration while editing an event.', 'eme' ) );
   eme_options_textarea ( __ ( 'Contact person email format', 'eme' ), 'eme_contactperson_email_body', __ ( 'The format of the email which will be sent to the contact person.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Contact person cancelled email format', 'eme' ), 'eme_contactperson_cancelled_email_body', __ ( 'The format of the email which will be sent to the contact person for a cancellation.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Contact person pending email format', 'eme' ), 'eme_contactperson_pending_email_body', __ ( 'The format of the email which will be sent to the contact person if approval is needed.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Respondent email format', 'eme' ), 'eme_respondent_email_body', __ ( 'The format of the email which will be sent to the respondent.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Registration pending email format', 'eme' ), 'eme_registration_pending_email_body', __ ( 'The format of the email which will be sent to the respondent when the event requires registration approval.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Registration cancelled email format', 'eme' ), 'eme_registration_cancelled_email_body', __ ( 'The format of the email which will be sent to the respondent when the respondent cancels the registrations for an event.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Registration denied email format', 'eme' ), 'eme_registration_denied_email_body', __ ( 'The format of the email which will be sent to the respondent when the admin denies the registration request if the event requires registration approval.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Registration updated email format', 'eme' ), 'eme_registration_updated_email_body', __ ( 'The format of the email which will be sent to the respondent when the admin updates the registration request.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_input_text ( __ ( 'Notification sender name', 'eme' ), 'eme_mail_sender_name', __ ( "Insert the display name of the notification sender.", 'eme' ) );
   eme_options_input_text ( __ ( 'Notification sender address', 'eme' ), 'eme_mail_sender_address', __ ( "Insert the address of the notification sender. It must correspond with your Gmail account user", 'eme' ) );
   eme_options_select ( __ ( 'Mail sending method', 'eme' ), 'eme_rsvp_mail_send_method', array ('smtp' => 'SMTP', 'mail' => __ ( 'PHP mail function', 'eme' ), 'sendmail' => 'Sendmail', 'qmail' => 'Qmail', 'wp_mail' => 'WP Mail' ), __ ( 'Select the method to send email notification.', 'eme' ) );
   eme_options_input_text ( 'SMTP host', 'eme_smtp_host', __ ( "The SMTP host. Usually it corresponds to 'localhost'. If you use Gmail, set this value to 'ssl://smtp.gmail.com:465'.", 'eme' ) );
   eme_options_input_text ( 'Mail sending port', 'eme_rsvp_mail_port', __ ( "The port through which you e-mail notifications will be sent. Make sure the firewall doesn't block this port", 'eme' ) );
   eme_options_radio_binary ( __ ( 'Use SMTP authentication?', 'eme' ), 'eme_rsvp_mail_SMTPAuth', __ ( 'SMTP authentication is often needed. If you use Gmail, make sure to set this parameter to Yes', 'eme' ) );
   eme_options_input_text ( __ ( 'SMTP username', 'eme' ), 'eme_smtp_username', __ ( "Insert the username to be used to access your SMTP server.", 'eme' ) );
   eme_options_input_password ( __ ( 'SMTP password', 'eme' ), 'eme_smtp_password', __ ( "Insert the password to be used to access your SMTP server", 'eme' ) );
   eme_options_radio_binary ( __ ( 'Debug SMTP?', 'eme' ), 'eme_smtp_debug', __ ( 'Check this option if you have issues sending mail via SMTP. Only do this for debugging purposes and deactivate it afterwards!', 'eme' ) );
   ?>
   </div>
</table>

<?php
	      break;
	      case 'payments' :
?>

<h3><?php _e ( 'RSVP: price options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_select ( __ ( 'Default currency', 'eme' ), 'eme_default_currency', eme_currency_array(), __ ( 'Select the default currency for payments.', 'eme' ) );
   eme_options_input_text ( __ ( 'Default price', 'eme' ), 'eme_default_price', __ ( 'The default price for an event.', 'eme' ) );
   eme_options_textarea ( __ ( 'Payment form header format', 'eme' ), 'eme_payment_form_header_format', __ ( 'The format of the text shown above the payment buttons. If left empty, a standard text will be shown.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Payment form footer format', 'eme' ), 'eme_payment_form_footer_format', __ ( 'The format of the text shown below the payment buttons. Default: empty.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=27'>".__('the documentation', 'eme').'</a>' );
   eme_options_radio_binary ( __ ( 'Show custom payment return page', 'eme' ), 'eme_payment_show_custom_return_page', __ ( 'Check this option if you want to define a custom page format for the sucess or failure of the payment.', 'eme' ) );
   eme_options_textarea ( __ ( 'Payment succes return page format', 'eme' ), 'eme_payment_succes_format', __ ( 'The format of the return page when the payment is succesfull.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'eme').'</a>' );
   eme_options_textarea ( __ ( 'Payment failure return page format', 'eme' ), 'eme_payment_fail_format', __ ( 'The format of the return page when the payment failed or has been canceled.', 'eme' ) .'<br/>'.__('For all possible placeholders, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=25'>".__('the documentation', 'eme').'</a>' );
   eme_options_radio_binary ( __ ( 'Add booking id to return page info', 'eme' ), 'eme_payment_add_bookingid_to_return', __ ( 'Check this option if you want to add the booking id to the return page. This will allow you to also use booking placeholders next to the regular event placeholders, but beware that other people can change the url and see other booking info then!', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSVP: paypal options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      eme_options_select ( __('PayPal live or test','eme'), 'eme_paypal_url', array (PAYPAL_SANDBOX_URL => __('Paypal Sandbox (for testing)','eme'), PAYPAL_LIVE_URL => __ ( 'Paypal Live', 'eme' )), __('Choose wether you want to test paypal in a paypal sandbox or go live and really use paypal.','eme') );
      eme_options_input_text (__('PayPal business info','eme'),'eme_paypal_business', __("Paypal business ID or email.",'eme'));
      eme_options_radio_binary ( __ ( 'Use paypal encryption?' ), 'eme_paypal_s_encrypt', __ ( 'Select yes to encrypt the paypal button using certificates.','eme' ) );
      eme_options_input_text (__('Paypal public cert','eme'),'eme_paypal_s_paypalcert', __("Path to paypal public certificate file.",'eme'));
      eme_options_input_text (__('Own public cert','eme'),'eme_paypal_s_pubcert', __("Path to own public certificate file.",'eme'));
      eme_options_input_text (__('Own private key','eme'),'eme_paypal_s_privkey', __("Path to own private key file.",'eme'));
      eme_options_input_text (__('Certificate ID','eme'),'eme_paypal_s_certid', __("Certificate ID of your cert at paypal.",'eme'));
   ?>
</table>

<h3><?php _e ( 'RSVP: 2Checkout options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      $events_page_link = eme_get_events_page(true, false);
      $notification_link = add_query_arg(array('eme_eventAction'=>'2co_notification'),$events_page_link);

      eme_options_select ( __('2Checkout live or test','eme'), 'eme_2co_demo', array (1 => __('2Checkout Sandbox (for testing)','eme'), 0 => __ ( '2Checkout Live', 'eme' )), __('Choose wether you want to test 2Checkout in a sandbox or go live and really use 2Checkout.','eme') );
      eme_options_input_text (__('2Checkout Account number','eme'),'eme_2co_business', __("2Checkout Account number.",'eme'));
      eme_options_input_text (__('2Checkout Secret','eme'),'eme_2co_secret', __("2Checkout secret.",'eme'));
      echo "<tr>".__('Info: the url for payment notifications is: ','eme').$notification_link.'</tr>';
   ?>
</table>

<h3><?php _e ( 'RSVP: Webmoney options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      $events_page_link = eme_get_events_page(true, false);
      $notification_link = add_query_arg(array('eme_eventAction'=>'webmoney_notification'),$events_page_link);

      eme_options_select ( __('Webmoney live or test','eme'), 'eme_webmoney_demo', array (1 => __('Webmoney Sandbox (for testing)','eme'), 0 => __ ( 'Webmoney Live', 'eme' )), __('Choose wether you want to test Webmoney in a sandbox or go live and really use Webmoney.','eme') );
      eme_options_input_text (__('Webmoney Purse','eme'),'eme_webmoney_purse', __("Webmoney Purse.",'eme'));
      eme_options_input_text (__('Webmoney Secret','eme'),'eme_webmoney_secret', __("Webmoney secret.",'eme'));
      echo "<tr>".__('Info: the url for payment notifications is: ','eme').$notification_link.'</tr>';
   ?>
</table>

<h3><?php _e ( 'RSVP: Google Checkout options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      eme_options_select ( __('Google Checkout live or test','eme'), 'eme_google_checkout_type', array (GOOGLE_SANDBOX => __('Google Checkout Sandbox (for testing)','eme'), GOOGLE_LIVE => __ ( 'Google Checkout Live', 'eme' )), __('Choose wether you want to test Google Checkout in a sandbox or go live and really use Google Checkout.','eme') );
      eme_options_input_text (__('Google Checkout merchant ID','eme'),'eme_google_merchant_id', __("Google Checkout Merchant ID.",'eme'));
      eme_options_input_text (__('Google Checkout merchant Key','eme'),'eme_google_merchant_key', __("Google Checkout Merchant Key.",'eme'));
   ?>
</table>

<h3><?php _e ( 'RSVP: First Data options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
      eme_options_select ( __('First Data live or test','eme'), 'eme_fdgg_url', array (FDGG_SANDBOX_URL => __('First Data Sandbox (for testing)','eme'), FDGG_LIVE_URL => __ ( 'First Data Live', 'eme' )), __('Choose wether you want to test First Data in a sandbox or go live and really use First Datal.','eme') );
      eme_options_input_text (__('First Data Store Name','eme'),'eme_fdgg_store_name', __("First Data Store Name.",'eme'));
      eme_options_input_text (__('First Data Shared Secret','eme'),'eme_fdgg_shared_secret', __("First Data Shared Secret.",'eme'));
   ?>
</table>

<?php
	      break;
	      case 'other' :
?>

<h3><?php _e ( 'Images size', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_select ( __('Thumbnail size','eme'), 'eme_thumbnail_size', eme_thumbnail_sizes(), __('Choose thumbnail size to be shown when placeholders involging thumbnails like e.g. #_EVENTIMAGETHUMB, #_LOCATIONIMAGETHUMB ...','eme') );
   ?>
</table>

<h3><?php _e ( 'Map options', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_input_text ( __('Global map zoom factor','eme'), 'eme_global_zoom_factor', __('The zoom factor used for the global map (max: 14).','eme').sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_GLOBAL_ZOOM_FACTOR)) );
   eme_options_input_text ( __('Individual map zoom factor','eme'), 'eme_indiv_zoom_factor', __('The zoom factor used when showing a single map (max: 14).','eme').sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_INDIV_ZOOM_FACTOR))  );
   eme_options_select ( __('Global map type','eme'), 'eme_global_maptype', array('ROADMAP' => __('Road map view','eme'),'SATELLITE' => __('Google Earth satellite images','eme'),'HYBRID' => __('Hybrid: a mixture of normal and satellite views','eme'), 'TERRAIN' => __('Terrain: a physical map based on terrain information')), __('The map type used for the global map (max: 14).','eme').sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_GLOBAL_MAPTYPE)) );
   eme_options_select ( __('Individual map type','eme'), 'eme_indiv_maptype', array('ROADMAP' => __('Road map view','eme'),'SATELLITE' => __('Google Earth satellite images','eme'),'HYBRID' => __('Hybrid: a mixture of normal and satellite views','eme'), 'TERRAIN' => __('Terrain: a physical map based on terrain information')), __('The map type used when showing a single map (max: 14).','eme').sprintf(__(" The default is '%s'",'eme'),eme_sanitize_html(DEFAULT_INDIV_MAPTYPE))  );
   ?>
</table>

<h3><?php _e ( 'Extra html headers', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_textarea ( __ ( 'Extra event html headers', 'eme' ), 'eme_event_html_headers_format', __ ( 'Here you can define extra html headers when viewing a single event, typically used to add meta tags for facebook or SEO. All event placeholders can be used, but will be stripped from resulting html.', 'eme' ) );
   eme_options_textarea ( __ ( 'Extra location html headers', 'eme' ), 'eme_location_html_headers_format', __ ( 'Here you can define extra html headers when viewing a single location, typically used to add meta tags for facebook or SEO. All location placeholders can be used, but will be stripped from resulting html.', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'Facebook info', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_input_text ( __ ( 'Facebook app id', 'eme' ), 'eme_fb_app_id', __ ( 'Provide facebook app id. This is needed to be able to import info from a facebook event when creating a new event.', 'eme' ) );
   ?>
</table>
<?php
	      break;
         }
?>


<p class="submit"><input type="submit" class="button-primary" id="eme_options_submit" name="Submit" value="<?php _e ( 'Save Changes' )?>" /></p>
</form>
</div>
<?php
   }
}
?>
