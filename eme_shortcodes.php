<?php

add_shortcode('eme_calendar', 'eme_get_calendar_shortcode');
add_shortcode('eme_events', 'eme_get_events_list_shortcode');
add_shortcode('eme_event', 'eme_display_single_event_shortcode');
add_shortcode('eme_events_page', 'eme_get_events_page_shortcode');
add_shortcode('eme_countdown', 'eme_countdown');
add_shortcode('eme_filterform', 'eme_filter_form_shortcode');
add_shortcode('eme_if', 'eme_if_shortcode');
add_shortcode('eme_if2', 'eme_if_shortcode');
add_shortcode('eme_if3', 'eme_if_shortcode');
add_shortcode('eme_if4', 'eme_if_shortcode');
add_shortcode('eme_if5', 'eme_if_shortcode');
add_shortcode('eme_if6', 'eme_if_shortcode');
add_shortcode('eme_rss_link', 'eme_rss_link_shortcode' );
add_shortcode('eme_ical_link', 'eme_ical_link_shortcode');
add_shortcode('eme_locations_map', 'eme_global_map'); 
add_shortcode('eme_location_map', 'eme_single_location_map_shortcode');
add_shortcode('eme_locations','eme_get_locations_shortcode');
add_shortcode('eme_location','eme_get_location_shortcode');
add_shortcode('eme_add_booking_form','eme_add_booking_form_shortcode');
add_shortcode('eme_delete_booking_form','eme_delete_booking_form_shortcode');
add_shortcode('eme_bookings','eme_booking_list_shortcode');
add_shortcode('eme_attendees','eme_attendee_list_shortcode');

// old shortcode names
add_shortcode('events_calendar', 'eme_get_calendar_shortcode');
add_shortcode('events_list', 'eme_get_events_list_shortcode');
add_shortcode('display_single_event', 'eme_display_single_event_shortcode');
add_shortcode('events_page', 'eme_get_events_page_shortcode');
add_shortcode('events_rss_link', 'eme_rss_link_shortcode');
add_shortcode('events_countdown', 'eme_countdown');
add_shortcode('events_filterform', 'eme_filter_form_shortcode');
add_shortcode('events_if', 'eme_if_shortcode');
add_shortcode('events_if2', 'eme_if_shortcode');
add_shortcode('events_if3', 'eme_if_shortcode');
add_shortcode('events_if4', 'eme_if_shortcode');
add_shortcode('events_if5', 'eme_if_shortcode');
add_shortcode('events_if6', 'eme_if_shortcode');
add_shortcode('events_ical_link', 'eme_ical_link_shortcode');
add_shortcode('locations_map', 'eme_global_map'); 
add_shortcode('display_single_location', 'eme_single_location_map_shortcode');
add_shortcode('events_locations','eme_get_locations_shortcode');
add_shortcode('events_add_booking_form','eme_add_booking_form_shortcode');
add_shortcode('events_delete_booking_form','eme_delete_booking_form_shortcode');
?>
