=== Events Made Easy ===  
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, manager, booking, calendar, gigs, concert, maps, geotagging, paypal, rsvp  
Requires at least: 3.5
Tested up to: 3.9
Stable tag: 1.4.3

Manage and display events, recurring events, locations and maps, widgets, RSVP, ICAL and RSS feeds, payment gateways support. SEO compatible.
             
== Description ==
Events Made Easy is a full-featured event management solution for Wordpress. Events Made Easy supports public, private, draft and recurring events, locations management, RSVP (+ optional approval), Paypal, 2Checkout, Google Checkout and Google maps. With Events Made Easy you can plan and publish your event, or let people reserve spaces for your weekly meetings. You can add events list, calendars and description to your blog using multiple sidebar widgets or shortcodes; if you are a web designer you can simply employ the template tags provided by Events Made Easy. 

Events Made Easy integrates with Google Maps; thanks to geocoding, Events Made Easy can find the location of your event and accordingly display a map. 
Events Made Easy handles RSVP and bookings, integrates payments for events using paypal and other payment gateways and allows payment tracking.

Events Made Easy provides also a RSS and ICAL feed, to keep your subscribers updated about the events you're organising. 

Events Made Easy is fully customisable; you can customise the amount of data displayed and their format in events lists, locations, attendees and in the RSS/ICAL feed. Also the RSVP form can be changed to your liking with extra fields, and by using EME templates let you change the layout even per page!

Events Made Easy is fully localisable and already partially localised in Italian, Spanish, German, Swedish, French and Dutch. 

Events Made Easy is also fully compatible with qtranslate (and mqtranslate): most of the settings allow for language tags so you can show your events in different languages to different people. The booking mails also take the choosen language into account.

For more information visit the [Documentation Page](http://www.e-dynamics.be/wordpress/) and [Support Forum](http://www.e-dynamics.be/bbpress/). 

== Installation ==

Always take a backup of your db before doing the upgrade, just in case ...  
1. Upload the `events-made-easy` folder to the `/wp-content/plugins/` directory  
2. Activate the plugin through the 'Plugins' menu in WordPress  
3. Add events list or calendars following the instructions in the Usage section.  

== Upgrade from the older Events Manager Extended plugin ==

Events Made Easy is completely backwards compatible with the old data from Events Manager Extended. Just deactivate the old plugin, remove the files if you want, and proceed with the Events Made Easy installation as usual. Events Made Easy takes care of your events database migration automatically. 

== Usage == 

After the installation, Events Made Easy add a top level "Events" menu to your Wordpress Administration.

*  The *Events* page lets you edit or delete the events. The *Add new* page lets you insert a new event.  
	In the event edit page you can specify the number of spaces available for your event. You just need to turn on RSVP for the event and specify the spaces available in the right sidebar box.  
	When a visitor responds to your events, the box sill show you his reservation. You can remove reservation by clicking on the *x* button or view the respondents data in a printable page.
	You can also specify the category the event is in, if you activated the Categories support in the Settings page.  
	Also fine grained control of the RSVP mails and the event layout are possible here, if the defaults you configured in the Settings page are not ok for this specific event.  
*  The *Locations* page lets you add, delete and edit locations directly. Locations are automatically added with events if not present, but this interface lets you customise your locations data and add a picture. 
*  The *Categories* page lets you add, delete and edit categories (if Categories are activated in the Settings page). 
*  The *People* page serves as a gathering point for the information about the people who reserved a space in your events. 
*  The *Pending approvals* page is used to manage registrations/bookings for events that require approval 
*  The *Change registration* page is used to change bookings for events 
*  The *Settings* page allows a fine-grained control over the plugin. Here you can set the [format](#formatting-events) of events in the Events page.
*  Access control is in place for managing events and such: 
        - a user with role "Editor" can do anything 
        - with role "Author" you can only add events or edit existing events for which you are the author or the contact person 
        - with role "Contributor" you can only add events *in draft* or edit existing events for which you are the author or the contact person 

Events list and calendars can be added to your blogs through widgets, shortcodes and template tags. See the full documentation at the [Events Made Easy Support Page](http://www.e-dynamics.be/wordpress/).
 
== Frequently Asked Questions ==

See the FAQ section at [the documentation site](http://www.e-dynamics.be/wordpress).

== Changelog ==

= 1.4.3 =
* Bugfix: the filter eme_eval_booking_form_filter was called too early, causing the second argument to be empty
* Bugfix: the captcha isn't taken into account when adding a booking via admin backend, but it prevented adding a booking then

= 1.4.2 =
* Bugfix: some undef values fixed
* Bugfix: make the default use the new notation too for captchas (for new installations)
* Bugfix: the frontend submission plugin has been updated to account for new jquery timeentry plugin too

= 1.4.1 =
* Bugfix: fixed a bug with a undefined var, preventing activation of the plugin for new installations
* Bugfix: for plugin deletion, the tables were not deleted for multisite blogs

= 1.4.0 =
* Feature: more consistent notation for placeholders, see http://www.e-dynamics.be/wordpress/?p=51559
* Feature: people page can now do merging of bookings, show all bookings per person and allows person editing
* Feature: RSS feed now shows html (no maps or forms), as does all other parts of wordpress do
* Feature: use language selected at booking time for sending mail concerning the booking or the attendee
* Feature: the cancel registration form can now also be formatted (also per event if wanted)
* Feature: locations can now also be duplicated
* Feature: added field tags to form fields, these are shown to the booker and are (m)qtranslate-compatible
* Bugfix: don't show the captcha when showing the booking form in the admin backend, it was ignored but still it's best not to confuse people
* Bugfix: if the current day had an event, the calendar didn't show the eventfull class
* Bugfix: don't match "[...]"  for location placeholders, solved more generically by the new placeholders notation feature.
* Bugfix: show weekday initials only again for small calendar format
* Bugfix: the wordpress nonce was being outputted too much times. Although the fields are hidden, it's not good to do so.
* Bugfix: when inserting or updating a booking, the action hook was executed before the answers for extra fields were stored in the db
* Improvement: the events database now gets updated upon first site visit (admin or not), so no more deactivate/reactivate action needed
* Improvement: when the events page setting changes, the SEO rules are flushed, so no more deactivate/reactivate action needed
* Improvement: code dedup for locations page
* Improvement: show a warning if a custom field requires a value but it was left empty

= 1.3.4 =
* Feature: when doing the "quick" deny for registrations while editing an event, no mails were being sent. There's now a general option in the Mail section that allows you to change that.
* Bugfix: make #_ADDBOOKINGFORM_IF_NOT_REGISTERED and #_REMOVEBOOKINGFORM_IF_REGISTERED work again
* Bugfix: added a CSS to the datatables when showing events, so the footer div following it is moved when the table grows or shrinks
* Bugfix: adding shortcodes to event details resulted in bad headers when using the setting "extra html headers" with placeholders like #_EXCERPT, and also gave problems for conditional tags

= 1.3.3 =
* Feature: you can now send html-mails for RSVP messages if wanted
* Bugfix: make the booking removal work again when in the event edit-window
* Bugfix: make start of month correct again in new calendar code

= 1.3.2 =
* Bugfix: remove some abiguous placeholder matching and correct the newly introduced image dimensions

= 1.3.1 =
* Improvement: add width and height to thumb images, just in case the resized versions don't exist
* Bugfix: make #_LOCATIONNAME work again
* Bugfix: calendar days with a single digit didn't show the events
* Bugfix: the booking recorded format may contain html, but was being sanitized

= 1.3.0 =
* Feature: add/edit booking is now all possible from the backend, and when editing all custom fields show as well
* Feature: added template functionality to the filter form as well (new option template_id to the shortcode eme_filterform)
* Feature: added option 'show_recurrent_events_once' to the shortcode eme_events to show recurrent events only once. Remember that people will only see a normal event unless you add recurrent info to it using the placeholder #_RECURRENCEDESC
* Feature: added placeholder #_EVENTCATEGORYIDS, returning the different category id's for an event. Not really usefull, unless you use the shortcode [eme_events category=#_EVENTCATEGORYIDS] inside a single event format, resulting in a list of events in the same categories as the one being viewed.
* Feature: use jquery datatables in the backend for event and bookings, makes it easier to search, sort, ... 
* Improvement: doing a javascript post after a booking add/delete to prevent double actions when refreshing the page, also avoids using global variables
* Improvement: German language updates, thanks to Wolfgang Löster
* Improvement: extra antispam measurements
* Behaviour change: make it possible for start and end date+time to be exactly the same
* Bugfix: cleanup function shouldn't change recurrences with specific days, since those "end date" is 0
* Major code rewrite in progress

= 1.2.9 =
* Feature: added the possibility to define a return page for payment succes or failure, with event and/or booking placeholders
* Feature: added placeholder #_RSVPEND that will show the date+time end of the registration period
* Feature: added conditional placeholder #_IS_RSVP_ENDED, returns 1 if the registration period has passed
* Feature: retain all filled in values if RSVP form validation proved wrong
* Improvement: show an admin warning if the special events page is not ok
* Improvement: German and Italian language updates, thanks to Stephan Oberarzbacher
* Bugfix: make location attributes actually work
* Bugfix: also deal with RESPNAME, RESPEMAIL, ... in the registration form format
* Bugfix: the payment page was not shown for the placeholder #_ADDBOOKINGFORM_IF_NOT_REGISTERED after booking was done
* Bugfix: fix double inclusion of eme_settings.php in uninstall.php, so uninstall works now

= 1.2.8 =
* Feature: added new filter eme_categories_filter, executed when searching for the categories (e.g. when creating an event). With this, you can e.g. limit the categories shown when creating an event or location or ... . Has one parameter: array of categories.
* Bugfix: make rsvp form work again (copy/paste error fix)

= 1.2.7 =
* Bugfix: let shortcodes in booking and attendees lists be replaced at the end, so conditionals can be used there as well.

= 1.2.6 =
* Feature: added width/height as optional parameters to shortcode eme_location_map
* Feature: added radiobox, 'radiobox vertical', 'checkbox' and 'checkbox vertical' as new formfield types
* Feature: added attributes for locations as well, and all templates are also searched for attribute definitions
* Feature: event notes can now contain all event placeholders as well, when activating the new option called 'Enable placeholders in event notes'. This
  changes old behavior, so by default it is disabled
* Improvement: make IS_REGISTERED work for all logged in users, not just when requiring WP membership for rsvp
* Bugfix: fix layout for location list (the default format setting was being ignored)
* Bugfix: some template header/footer fixes for attendee and booking lists
* Bugfix: make [eme_location] work again

= 1.2.5 =
* Feature: the payment form showing the buttons can now be customized in the EME settings, Payment section. The same placeholders as for bookings can be used.
  You can format the section above and below the payment buttons and everything is surrounded by CSS tags as well.
* Feature: max and min amount of seats to book for one booking can now also be used for multiprice events (multi-compatible)
* Feature: added option template_id to shortcode eme_single_event
* Feature: added shortcode eme_location, with location id as argument, and optional a template_id for content:
  [eme_location id=1 template_id=3]
* Feature: added shortcodes eme_bookings and eme_attendees, with event id as argument, and optional a template_id for header, content and footer
  [eme_attendees id=1 template_id=3 template_id_header=7 template_id_footer=9]
* Feature: added placeholder #_IS_MULTIDAY. Returns 1 if the event start date is different from the end date, 0 otherwise.
* Feature: added placeholder #_BOOKINGID for rsvp mails and booking info, in case you want to share the booking id
* Feature: added new filter eme_add_currencies, so you can add extra currencies to the list. Be aware that not all payment portals support all currencies.
  Example: to add Ghanaian Cedi (GHS) to the list of currencies, add the following to your theme's functions.php:

  function my_eme_add_currencies($currencies){
      $currencies['GHS'] = 'Ghanaian Cedi';
      return $currencies;
  }
  add_filter('eme_add_currencies','my_eme_add_currencies');

* Improvement: unified shortcode names:

  events_calendar             ==>   eme_calendar
  events_list                 ==>   eme_events
  display_single_event        ==>   eme_event
  events_page                 ==>   eme_events_page
  events_rss_link             ==>   eme_rss_link
  events_ical_link            ==>   eme_ical_link
  events_countdown            ==>   eme_countdown
  events_filterform           ==>   eme_filterform
  events_if                   ==>   eme_if
  events_if2                  ==>   eme_if2
  events_if3                  ==>   eme_if3
  events_if4                  ==>   eme_if4
  events_if5                  ==>   eme_if5
  events_if6                  ==>   eme_if6
  locations_map               ==>   eme_locations_map
  display_single_location     ==>   eme_location_map
  events_locations            ==>   eme_locations
  events_add_booking_form     ==>   eme_add_booking_form
  events_delete_booking_form  ==>   eme_delete_booking_form
  
  The old names are still valid, but will disappear from the doc

* Bugfix: make sure that relative protocol urls (no http: or https:) are used for google api's in the admin backend
* Bugfix: in the event edit window, the ajax method for removing rsvp's wasn't working anymore
* Bugfix: some plugins add the lang info to the home_url, remove it so we don't get into trouble or add it twice
* Bugfix: booking placeholders are also possible for the 'booking ok' message
* Bugfix: only show location info in the ical feed if there's actually a location
* Bugfix: ical fix for multiday allday events (they ended a day too soon)
* Bugfix: use str_replace for replacing placeholders, to avoid issues with replacement strings containing $13 (preg_replace interprets those as backreferences)
* Bugfix: the booking price is now shown correctly as floating point, not just integer
* Bugfix: correct the placeholder replacement sequence for attendees and bookings
* Bugfix: #ESC_NOTES was not working

= 1.2.4 =
* Bugfix: prevent double header/footer appearance in event lists

= 1.2.3 =
* Bugfix: prevent double header/footer appearance in event lists

= 1.2.2 =
* Fixed a small bug I introduced in 1.2.1 + better detection for sending mail for auto-approval

= 1.2.1 =
* Feature: added conditional tags #_IS_MULTISEAT and #_IS_ALLDAY
* Feature: add template_id_header and template_id_footer for events_list and events_locations shortcodes, so you can also use templates for the headers and footers
* Improvement: make #_IS_REGISTERED also work even when the option "Require WP membership for registration" is not checked, of course you still need to be logged in as a WP user for it to work.
* Bugfix: the template format field was too small in the database, limiting the number of characters
* Bugfix: booking list should show a single number for booked seats for multiseat events when asked for
* Bugfix: send approval mail also for auto-approve events upon payment
* Bugfix: events_locations shortcode no longer listed all locations (typo in 1.2.0 caused this)

= 1.2.0 =
* Feature: multi-seat events are now possible: the same as multiprice events, but now you can limit the number of seats per price category if wanted
* Feature: format templates can now be created and used in the events_list and events_locations shortcodes as follows:
  [events_list template_id=3]
  This renders the use of the 'format' parameter obsolete and is more powerfull as it allows conditional tags inside the format template.
* Feature: recurrence is now also possible on specific days, not just a pattern
* Feature: maptype for google maps can be defined in the settings for global and individual maps (Roadmap, Satellite, Hybrid or Terrain)
* Feature: added event placeholders #_TOTALSEATSxx, #_AVAILABLESEATSxx and #_BOOKEDSEATSxx to show seat info per seat category
* Improvement: remove some unneeded caching in the EME admin section.
* Improvement: Dutch language update, thanks to Guido
* Improvement: show which required fields are missing when filling out a rsvp form
* Bugfix: better all-day indication in ical
* Bugfix: make #_TOTALPRICE and #_TOTALPRICExx also work for "Booking mails" reminders
* Bugfix: jump to RSVP confirmation message upon successfull registration
* Bugfix: better matching rules for #_CATEGORIES with include/exclude categories
* Bugfix: all day event fix

= 1.1.6 =
* Improvement: #_MAP and #_DIRECTIONS for an event are only replaced/shown if the event is linked to a location
* Bugfix: forgot to escape the forward slash, so some placeholders might have resulted in errors

= 1.1.5 =
* Bugfix: better regex replacement for replacing placeholders

= 1.1.4 =
* Feature: added 'First Data Global Gateway Connect 2.0' payment gateway implementation
* Feature: new option: consider pending registrations as available seats for new bookings (meaning: ignore pending registrations for new rsvp's)
* Feature: implemented feature request "Automatic Approval after Payment is received", optional per event
* Feature: all day events can now be indicated as such
* Feature: placeholder #_TOTALPRICEx added for mail formats (with x being a number: gives the total price to pay per price for multiprice events: the amount of spaces booked times the specific price of the multiprice event)
* Feature: added a facebook import function when creating a new event. Thanks to Tom Chubb and Jashwant Singh Chaudhary.
* Feature: some themes result in weird behaviour because they use the_content filters, resulting in loops or unwanted content replacement. Added a setting against possible loop protection.
* Feature: RSS pubdate can now also be the event start time
* Feature: separate format for ICAL entries
* Feature: zoom factor can be changed for the global or individual maps
* Feature: added new filter eme_event_preinsert_filter, taking place just before the event is inserted in the DB
* Feature: added 2 placeholder options to #_CATEGORIES and #_LINKEDCATEGORIES to include/exclude categories. To be used like this:
     #_CATEGORIES[1,3][] ==> this will get all categories for the event, but only show cat 1 or 3
     #_CATEGORIES[][1,3] ==> this will get all categories for the event, but not show cat 1 or 3
* Feature: added option to define image size for placeholders #_EVENTIMAGETHUMB and #_EVENTIMAGETHUMBURL, to be used as:
  #_EVENTIMAGETHUMB[MyCustomSize] or #_EVENTIMAGETHUMBURL[MyCustomSize], where "MyCustomSize" is a custom size either known to wordpress or defined in your
  functions.php via the function add_image_size()
* Feature: the events_if shortcode now also supports "le" (lower than or equal to) and "ge" (greater than or equal to) comparisons
* Feature: new filter eme_eval_booking_form_filter, so you can create your own eval rules for a booking
* Feature: make #_SPACESxx also work in RSVP info, next to #_RESPSPACES
* Feature: new hook eme_update_rsvp_action, executed when updating booking info
* Bugfix: correct escaping of characters for ical format
* Bugfix: better regex replacement for replacing placeholders
* Bugfix: make sure URL's created by placeholders aren't touched by wordpress anymore
* Bugfix: when specifying a location by latitude and longitude, town and address are not needed
* Bugfix: fix a JS error on some admin pages
* Bugfix: the multiprice array was not correctly initialized, causing troubles if you used #_SEATSx form fields that were out of order
* Improvement: shortcodes [add_booking_form] and [delete_booking_form] now properly return the generated html instead of echoing it, and also return empty if rsvp not active
* Improvement: events_list header/footer can now also contain shortcodes, usefull for conditional tags. Idem for locations.
* Improvement: json is limited in size we only return what's needed in the javascript
* Improvement: booking date/time info is now visible in the backend registration pages
* Improvement: add image thumb url to the ical feed if present
* Improvement: the day difference function now returns a negative value as well, the countdown and DAYS_TILL* placeholders can take advantage of this
* Improvement: use the WP time setting for new/edit events as well when trying to detect 12 or 24 hour notation
* Code improvement: added event_properties to stop needing to change the event database table all the time. Ideal for new event properties.

= 1.1.3 =
* Feature: added #_HTML5_PHONE and #_HTML5_EMAIL to indicate you want the html5 input type field for phone and/or email
  So you can use e.g. #_HTML5_PHONE or #REQ_HTML5_PHONE and the result will be: the html5 tel-type field for phone, and required if #REQ used.
  Other html5 input types will be added.
* Feature: events_if4, events_if5 and events_if6 added
* Feature: support wp_mail as a method of sending mail, allowing other plugins to work on the mail as well via the existing wp_mail hooks and filters
* Feature: added placeholder #_LINKEDCATEGORIES: creates a link per category for the corresponding event, linking to a list of future events for that category
* Feature: new option 'title' for the shortcode events_rss_link, so the title can be given a specific name
* Feature: you can now exclude categories in the widget list and calendar as well, and in the regular shortcode events_calendar also with the new option 'notcategory'
* Feature: events_ical_link shortcode now also supports the options scope (default: future), author, contact_person and notcategory
* Feature: added placeholder #_EVENTIMAGETHUMB, to show a thumbnail of a featured image, so you can e.g. show a thumbnail of a featured image instead of the whole image. The size can be choosen in the EME settings (panel 'Other'), by default it is 'thumbnail' size.
* Feature: added placeholder #_EVENTIMAGETHUMBURL, to get just the url to the thumbnail. Also added #_LOCATIONIMAGETHUMB and #_LOCATIONIMAGETHUMBURL
* Improvement: mail sending is by default enabled for new installations
* Improvement: upon auto-update, the DB version of EME is now also checked and a DB update is done if needed
* Improvement: the 'No events' message now also has a div surrounding it, with div-id 'events-no-events'
* Improvement: extra plugin events-made-easy-frontend-submit now also uses AM/PM or 24 hours notation based on site preferences
* Bugfix: html encapsulated in RSS feed was needlessly escaped inside a CDATA section
* Bugfix: multiprice bookings were reset to "1" if the first booking was 0 upon approval
* Bugfix: default selected town was always the first town when using [events_filterform]
* Bugfix: make sure the correct scheme is used for admin_url
* Bugfix: the generated ical link didn't take the author or contact person into account
* Bugfix: the calendar links now take into account all options for contact person, categories etc ...
* Bugfix: fix class warnings in extra plugin events-made-easy-frontend-submit
* Bugfix: the featured image for locations was not retained after you re-edit and save the location without changing the image
* Bugfix: url-encoded strings in the format argument of the [events_list] shortcode were not being interpreted
* Bugfix: remove use of deprecated wordpress functions get_userdatabylogin and wpdb::escape

= 1.1.2 =
* Feature: new placeholder #_RESPSPACESxx for bookings, to indicate the bookings per price for multiprice events
* Feature: new placeholders #_BOOKINGCREATIONTIME and #_BOOKINGMODIFTIME for bookings list
* Feature: ability to use attributes and conditional tags in registration form added
* Feature: added conditional tag #_IS_MULTIPRICE
* Feature: location_id argument in shortcodes now supports 'none' to indicate no location
* Feature: query string eme_town (+SEO) has been added, so only events for a specific town are shown. Not really sure how I'll use it, but it's there ...
* Improvement: updated Italian translation, tx to Antonio Venneri
* Improvement: updated German translation, tx to Daniel Rohde-Kage
* Improvement: every column in the print preview for bookings now has a class, so you can CSS style it
* Change: placeholders #_RESPSPACES and #_RESPCOMMENT now preferred for bookings
* Bugfix: the 'Booking recorded html Format' setting for a single event was not being saved
* Bugfix: the div for a required field should have a class, not an id
* Bugfix: don't show a link to a month without events (occured if on the last day of the month an event was booked)
* Bugfix: better sanitize RSS feed by using CDATA

= 1.1.1 =
* Feature: new placeholder #_PAYMENT_URL for bookings, in case you want people to be able to pay later on, or for reminders
* Feature: you can now select payed/unpayed and pending status when sending mails, good for sending reminders for payments etc ...
* Feature: you can now specify the latitude/longitude of a location if wanted, overriding the detected values
* Feature: you can now specify the cut-off hours for RSVP as well
* Bugfix: make qtranslate work again (one-liner fix)
* Bugfix: make ical work correctly with server timezone included
* Bugfix: wpdb prepare doesn't use correct backticks for column names, resulting in multisite issues (I tried to use the correct prepare syntax in 1.1.0)
* Improvement: only include the datepicker locale if it exist (like in 1.0.18), and take into account 2-letter locales again if the full locale doesn't exist

= 1.1.0 =
* Feature: multiprice events are now possible (see wordpress site for explanation: price and booked seats need to be seperated by "||"). Also, for multiprice events, the min number of seats to book is always 0
* Feature: you can now send mails to all attendees for an event in the admin backend. This functionality has it's own access right settings as well.
* Feature: revamped the edit/add event interface: you can now use wordpress 'screen options' in the admin page to decide which parts to show and in what sequence
* Feature: added the possibility to use a print stylesheet called eme_print.css in your theme style dir
* Feature: shortcode events_ical_link now has 3 extra options: scope, author and contact_person
* Feature: the creation and modif date can now be shown for the bookings list (when using #_BOOKINGS) via 2 new placeholders: #_BOOKINGCREATIONDATE and #_BOOKINGMODIFDATE
* Improvement: the price for each event is stored per booking now, so if the price changes afterwards it doesn't affect the booking in question
* Improvement: ical format includes the timezone now
* Improvement: #ESC_ATT and #URL_ATT are now also recognized when looking for attributes definitions
* Improvement: the CSV export didn't show the paid status
* Improvement: when editing a single event, delete buttons to edit the event and/or recurrence are now there (and asked for confirmation)
* Improvement: when trying to view a non-existing location, now also a 404 is returned (as for events)
* Improvement: in the backend, you can now choose wether or not mails are being sent when approving or changing registrations
* Bugfix: for recurrent events, the wanted date/time format was not being taken into account when being shown in the admin interface or when using #_RECURRENTDESC
* Bugfix: #_EVENTDETAILS has never been working (#_NOTES and #_DETAILS did work ok)
* Bugfix: according to http://codex.wordpress.org/Plugin_API/Action_Reference/wp_print_styles: wp_print_styles should not be used to enqueue styles or scripts on the front page. Use wp_enqueue_scripts instead.
* Bugfix: when converting a single event into a recurrence, the featured image was not kept
* Bugfix: English has 24-hour format, plus a jquery datepicker correction

= 1.0.18 =
* Improvement: the booking list format (used with #_BOOKINGS) now also supports #ESC_* for placeholders, so you can safely use shortcodes (like conditional tags) inside it
* Bugfix: make drop down postbox expanding work again

= 1.0.17 =
* Feature: added support for paypal encrypted button
* Bugfix: url_decode should be urldecode
* Bugfix: remove remaining occurences of eme_upload_event_picture()

= 1.0.16 =
* Feature: for events and locations, the featured image now uses the WP media gallery
* Feature: Webmoney support added
* Feature: rss and ical shortcodes now support a location id, to limit events shown to a specific location
* Feature: a little extra for more WPML support (added, but not guaranteed)
* Feature: added #_IS_REGISTERED conditional tag, returns 1 if WP user has already registered for the event, 0 otherwise
* Improvement: you can now activate SMTP debugging if you have issues when sending mail via SMTP
* Improvement: the booking format now also can use #_PAYED to show the payed status
* Improvement: ability to set default currency, price and "Require approval for registration"
* Improvement: the "Allow RSVP until" can now also be given a default value
* Improvement: the week/month/year scopes in [events_filterform] now show the text 'select week/month/year' by default.
* Bugfix: when creating/editing an event, location creation is now also being checked for access rights

= 1.0.15 =
* Improvement: more options for shortcodes that were booleans with 0 or 1 have now true/false support too
* Bugfix: the page title for single location pages was not being set correctly

= 1.0.14 =
* Feature: new parameters "show_events" (default:0) and "show_locations" (default:1) for shortcode [locations_map], allows to show a list of events corresponding to the locations on the map
* Improvement: the "Required field" text has a div-id surrounding it now, so you can change the look of it as wanted using CSS
* Improvement: removed deprecated wp_tiny_mce as editor and solved some php warnings (and moved the minimum required version up to 3.3)
* Improvement: a number of options for shortcodes were booleans with 0 or 1, now we added true/false support too

= 1.0.13 =
* Feature: integrated 2Checkout. Instant Notification is also possible, but you have to specify the url in your 2Checkout account. The value for this will be shown in the EME settings.
* Feature: integrated Google Checkout, but no automatic payment handling since that requires client certificates. And for Google Checkout to work, the price must be in dollars or pounds (identical to your google wallet account, otherwise it will fail)
* Feature/Bugfix: the paypal class didn't really support the business ID, should work now
* Feature: added event scope "today--this_week_plus_one" so you can show the beginning of next week as well
* Improvement: revamped the admin settings interface, it was getting too much for one page so I switched to tabs
* Improvement: don't depend on wp-admin/ajax.php anymore, so as to better support https
* Improvement: use google maps https if the page is https as well 

= 1.0.12 =
* Bugfix: the participant info was not correctly replaced in mails sent
* Bugfix: make paypal work via https and HTTP/1.1
* Bugfix: the CSS classes in the calendar indicating the weekday were wrong for days in the previous/next month

= 1.0.11 =
* Bugfix release: the list of participants was not shown anymore

= 1.0.10 =
* Feature: added #_BOOKINGS placeholder (+ customizable bookings format)
* Feature: added #_FIELNAMExx to get the formfield name (can be used in the registration form, in #_BOOKINGS and RSVP mails)
* Feature: added shortcodes [events_add_booking_form] and [events_delete_booking_form] (with 'id' as parameter: the id of the event)
           This way you can have normal pages for events (using the url option), but still show the booking form as well
* Bugfix: make qtranslate work again
* Bugfix: the CSV export and print of custom fields was not being alligned properly
* Bugfix: allow empty contact phone
* Extra: updated Dutch translation, thanks to Peter Goldstein

= 1.0.9 =
* Feature: added #_DAYS_TILL_START and #_DAYS_TILL_END placeholders
* Bugfix: the full calendar was showing month 0 of the year 0 if the option "use client clock" was used
* Extra: when denying registrations, confirmation is now being asked
* Extra: updated Dutch translation, thanks to Peter Goldstein
* Extra: some warning for the capability 'List events'

= 1.0.8 =
* Bugfix: apparently some WP update changed the capability checking worked, so the code to get all caps has been updated
* Bugfix: custom (per event) event_registration_pending_email_body was not working
* Bugfix: fix a WP php notice for wp_enqueue_script: it should be called from within other wp_* calls, not directly. So I added it to the callback for add_action('wp_enqueue_scripts')
* Bugfix: fix typo with acl for "Edit events"
* Extra: the admin backend will now use a new date_format setting in the user's profile (if present) for all dates shown

= 1.0.7 =
* Feature: placeholder #_EDITEVENTURL added, gives you just the link to the admin page for editing the event
* Feature: added a setting to wether or not show the event creation/modification date as PubDate info in the in the events RSS feed
* Bugfix: calendar navigation now also respects the qtranslate language if permalinks are enabled
* Bugfix: filter forms now also respects the qtranslate language
* Bugfix: if phone or comment was defined as a required field, the booking was not working
* Bugfix: fix wrong call to event_rul(), must be eme_event_url(), bug added in changeset 649391
* Bugfix: [events_countdown] shortcode was not working as expected
* Extra: Added error if image upload fails

= 1.0.6 =
* Feature: extra registration field info can now also be mailed, using #_FIELDS as a placeholder in mail formats
* Feature: event SEO links now also take into account the qtranslate language if present
* Feature: added extra capability to just list events, so people with no edit cap can still do e.g. CSV exports. All your event admins would need this cap as well, otherwise the menu will not show.
* Feature: preview added for draft events
* Bugfix: the results for custom fields were not shown in the printable overview or the CSV export
* Bugfix: the table for answers had a wrong index (primary), which resulted in only the first custom field to be stored in it
* Extra: added Danish translation, thanks to Torben Bendixen
* Extra: updated Dutch translation, thanks to Peter Goldstein

= 1.0.5 =
* Feature: forms are customizable now, although extra defined fields can be viewed/exported but not changed in the admin backend (and is qtranslate compatible)
* Feature: contact person mails for cancellations and approvals are now customizable
* Feature: submit button for registration form is now qtranslate compatible
* Bugfix: make html title work correctly for locations too
* Bugfix: the html anchor was not always being shown for RSVP
* Bugfix: email body and subject can now contain qtranslate calls as well (code got removed when changing the plugin name)
* Bugfix: corrected and added some sql prepare statements
* Extra: give all RSVP forms a name and a html id
* Extra: give full day events a CSS class in the calendar (eme-calendar-day-event)
* Extra: some RSS readers don't like it when an empty feed without items is returned, so we add a dummy item then

= 1.0.4 =
* Bugfix: again better the_content filter recursion detection, so it should now work ok with Arras theme, Pagelines, TwentyEleven and hopefully all other ones.
* Bugfix: this_year and paging was not working ok due to a php bug

= 1.0.3 =
* Bugfix: scope=this_year was not working
* Bugfix: better the_content filter recursion detection, so it should now work ok with Arras theme and using page-include plugins inside event content
* Bugfix: removing a booking in the event edit window via ajax was no longer working
* Bugfix: #_EVENTIMAGEURL was not being replaced correctly
* Bugfix: the available number of seats can be <0 if more than one booking happened at the same time and people fill in things slowly ...
* Bugfix: events spanning multiple months were not correctly shown in the calendar or list
* Bugfix: some 'this_week' scopes did not take the start day of the week preference into account
* Bugfix: RSS needs "<category>" and not "<categories>" as valid tag
* Feature: the html title of a single event or location can now also be formatted
* Feature: new conditional tag #_IS_ONGOING_EVENT
* Extra: Romanian language added, thanks to Web Geek Science (http://webhostinggeeks.com/)

= 1.0.2 =
* Feature: placeholders #_TOTALSPACES and #_TOTALSEATS added (gives the total amount of spaces for an event)
* Feature: placeholder #_TOTALPRICE added for mail formats (gives the total price to pay: the amount of spaces booked times the price of the event)
* Feature: placeholder #_RECURRENCEDESC added, shows the recurrence info for an event like it does in the admin backend
* Feature: events can now also have a featured image, like locations, resulting also in 2 new placeholders: #_EVENTIMAGE and #_EVENTIMAGEURL
* Feature: location list formatting is now possible in the settings, when using the shortcode [events_locations], as it was already for [events_list]
* Feature: each day in the calendar now also has the short day name as an extra class
* Feature: list widget now also can choose to show ongoing events or not
* Feature: made the message 'Your booking has been recorded' formattable
* Feature: scope=Nd--Md, to get the events from day N in the past/future till day M in the past/future (eg. scope=-3d--2d , scope=0d--3d)
* Feature: initial state for a new event can now be set in the settings page
* Feature: new access right setting for publish events
* Extra: included the plugin events-made-easy-frontend-submit, see the dir extras/plugins/events-made-easy-frontend-submit
* Extra: if the end date is empty, it will always be the start date now. If you want to check if they are equal, use conditional tags
* Extra: French translation updated, thanks to Philippe Lambotte
* Bugfix: typo fix in eme_events.php influencing location showing
* Bugfix: typo fix in eme_events.php for a jquery statement
* Bugfix: when adding a registration via the backend and approval was required, the registered person would get a pending message although it was already approved
* Bugfix: #_USER_RESERVEDSPACES wasn't working correctly for the attendees format setting
* Bugfix: location title was not qtranslate-ready in the [events_location] shortcode
* Bugfix: better retreiving of new booker info, should resolve the booker being empty in some cases
* Bugfix: datepicker images were not in svn
* Bugfix: list widget was behaving incorrectly for the author option
* Bugfix: when clicking on calendar day and there's only 1 event, only show the event content directly if the event doesn't point to an external url
* Bugfix: the booking form was still shown for fully booked events if the max number of seats to book was not defined
* Bugfix: typo fix in the admin edit location pages
* Bugfix: #_PLAIN_CONTACTEMAIL was being replaced by empty string in mails

= 1.0.1 =
* Bugfix: fix replacement in menu for page title by events title when viewing a single event
* Bugfix: fixed a closing-div tag, preventing the rich html-editor to appear sometimes for locations

= 1.0.0 =
* Feature: added options 'category' and 'notcategory' to the shortcode [events_filterform], so you can choose to only show specific categories or exclude certain categories from the select box
* Feature: all location placeholders can now be used inside events (those that make sense of course). In order to make a distinction among event and location placeholders with the same name, some have been deprecated (see below)
* Feature: the end time can now be the same as the start time, so you can test on this to not show end date/time info (for eg. events without end)
* Feature: each booking now has a unique bank transfer number for belgian transfers, the placeholder "#_TRANSFER_NBR_BE97" can be used in booking mails
* Improvement: when adding a registration in the backend, you can now only choose from events that have RSVP activated
* Improvement: when the setting "Max number of spaces to book" is empty, it is now ignored so unlimited number of attendees is now possible
* Improvement/fix: price can be a decimal number as well
* Improvement: make sure the Settings page can be reached if something is not correct with the security settings
* Improvement: make sure the first event of an recurrent series is used to get the info from
* Improvement: for location formatting, #_IMAGE has been deprecated in favor of #_LOCATIONIMAGE
* Improvement: for location formatting, #_IMAGEURL has been deprecated in favor of #_LOCATIONIMAGEURL
* Improvement: for location formatting, #_DESCRIPTION has been deprecated in favor of #_LOCATIONDETAILS
* Improvement: for location formatting, #_CATEGORIES has been deprecated in favor of #_LOCATIONCATEGORIES
* Improvement: for event formatting, #_NOTES, #_DETAILS and #_DESCRIPTION have been deprecated in favor of #_EVENTDETAILS
* Improvement: for event formatting, #_CATEGORIES has been deprecated in favor of #_EVENTCATEGORIES
* Improvement: the html header now only shows the event name, and not the whole single event title format string
* Improvement: some rsvp info remains entered now if the user enters a wrong captcha
* Improvement: updated German translation (thanks to Jorgo Ananiadis)
* Improvement: locations are now also sorted alphabetically when using the eventfull option
* Improvement: email body text can now contain qtranslate calls as well
* Improvement: the paypal form now also shows that the registration was successfull
* Improvement: the RSS feed now includes the pubDate field for each event (value is the creation or modification date of the event)
* API change: eme_insert_recurrent_event renamed to eme_db_insert_recurrence (old function exists for backwards compatibility)
* API change: eme_update_recurrence renamed to eme_db_update_recurrence (old function exists for backwards compatibility)
* API change: function eme_db_update_event now takes the event_id as the second parameter (you can still use the where-array method directly, made it backwards compatible)
* API change: function eme_email_rsvp_booking now only takes booking_id as the first parameter and the action as the second
* Bugfix: attributes weren't taken into account for the new email formats pending, cancelled, denied
* Bugfix: the filtering threw an error when selecting multiple items
* Bugfix: the attendee list didn't return the correct number of booked seats
* Bugfix: for the filter form, the selected items were not highlighted upon submit
* Bugfix: true and 1 now work as value for the options full and long_events
* Bugfix: array in hook eme_update_event_action missed event_id and event_author
* Bugfix: typo: payed => paid
* Bugfix: the widget update refused anything different than future, past or all for scope
* Bugfix: number of events can now be 0 in the widget
* Bugfix: attribute values were not sanitized
* Bugfix: if the booked seats were 0, the pending screen wouldn't show the booking
* Bugfix: the capability for adding events in draft wasn't working ok
* Bugfix: when paypal was active, rsvp field validation results were ignored
* Bugfix: ampersand character in event title breaks RSS feed
* Bugfix: make sure that we apply the the_content filter only once

= Older versions =
* See the Changelog of the Events Manager Extended plugin

