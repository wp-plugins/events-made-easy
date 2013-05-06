=== Events Made Easy ===  
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, manager, booking, calendar, gigs, concert, maps, geotagging, paypal  
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 1.1.2

Manage and display events. Includes recurring events; locations; widgets; Google maps; RSVP; ICAL and RSS feeds; PAYPAL, 2Checkout, Webmoney and Google Checkout support. SEO compatible.
             
== Description ==
Events Made Easy (formally called 'Events Manager Extended') is a full-featured event management solution for Wordpress. Events Made Easy supports public, private, draft and recurring events, locations management, RSVP (+ optional approval), Paypal, 2Checkout, Google Checkout and Google maps. With Events Made Easy you can plan and publish your event, or let people reserve spaces for your weekly meetings. You can add events list, calendars and description to your blog using multiple sidebar widgets or shortcodes; if you are a web designer you can simply employ the template tags provided by Events Made Easy. 

Events Made Easy integrates with Google Maps; thanks to geocoding, Events Made Easy can find the location of your event and accordingly display a map. 
Events Made Easy also integrates payments for events using paypal. 

Events Made Easy provides also a RSS and ICAL feed, to keep your subscribers updated about the events you're organising. 

Events Made Easy is fully customisable; you can customise the amount of data displayed and their format in events lists, pages and in the RSS/ICAL feed. You can choose to show or hide the events page, and change its title.  

Events Made Easy is fully localisable and already partially localised in Italian, Spanish, German, Swedish, French and Dutch. 

For more information visit the [Documentation Page](http://www.e-dynamics.be/wordpress/) and [Support Forum](http://www.e-dynamics.be/bbpress/). 

== Installation ==

Always take a backup of your db before doing the upgrade, just in case ...  
1. Upload the `events-made-easy` folder to the `/wp-content/plugins/` directory  
2. Activate the plugin through the 'Plugins' menu in WordPress  
3. Add events list or calendars following the instructions in the Usage section.  
== Upgrade from the older Events Manager plugin ==

Events Made Easy is completely backwards compatible with the old data from Events Manager 2.2.2. Just deactivate the old plugin, remove the files if you want, and proceed with the Events Made Easy installation as usual. Events Made Easy takes care of your events database migration automatically. 
Again my note of warning: Events Made Easy (EME) is a fork (NOT an extension) of the older Events Manager (EM) version 2.2.2 (April 2010). After months, the original plugin came back to life with a new codebase, but I added so much features already that it is very hard to go back to being one plugin. Read here for the differences since 2.2.2: http://www.e-dynamics.be/wordpress/?page_id=2

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

= I enabled the Google Maps integration, but instead of the map there is a green background. What should I do? =

I call that "the green screen of death", but it's quite easy to fix your issue. If you see that green background, your theme has a little problem that should be fixed. Open the `header.php` page of your theme; if your theme hasn't any `header.php` page, just open the `index.php page` and/or any page containing the `<head>` section of the html code. Make sure that the page contains a line like this:              

    <?php wp_head(); ?>              

If your page(s) doesn't contain such line, add it just before the line containing `</head>`. Now everything should work allright.    
For curiosity's sake, `<?php wp_head(); ?>` is an action hook, that is a function call allowing plugins to insert their stuff in Wordpress pages; if you're a theme maker, you should make sure to include `<?php wp_head(); ?> ` and all the necessary hooks in your theme.

= How do I resize the single events map? Or change the font color or any style of the balloon? = 

Create a file called 'myown.css' in the plugin directory and put in there eg.:  
  
.eme-location-map {  
width: 600px;  
height: 400px;  
}  
.eme-location-balloon {  
        color: #FF7146;  
}  

You can start from events_manager.css as a base and just change the parts you want.  
Warning: when wordpress updates a plugin automatically, it removes the plugin directory completely. So be sure to have a backup of myown.css somewhere to put back in place afterwards.
  
For the multiple locations map, see the shortcode [locations_map] with its possible parameters on the documentation site.

= Can I customise the event page? =

Sure, you can do that by editing the page and changing its [template](http://codex.wordpress.org/Pages#Page_Templates). For heavy customisation, you can use the some of the plugin's own conditional tags, described in the *Template Tags* section.

= How does Events Made Easy work? =   

When installed, Events Made Easy creates a special "Events" page. This page is used for the dynamic content of the events. All the events link actually link to this page, which gets rendered differently for each event.

= Are events posts? =

Events aren't posts. They are stored in a different table and have no relationship whatsoever with posts.

= Why aren't events posts? =

I decided to treat events as a separate class because my priority was the usability of the user interface in the administration; I wanted my users to have a simple, straightforward way of inserting the events, without confusing them with posts. I wanted to make my own simple event form.  
If you need to treat events like posts, you should use one of the other excellent events plugin.

= Is Events Made Easy available in my language? = 

At this stage, Events Made Easy is only available in English and Italian. Yet, the plugin is fully localisable; I will welcome any translator willing to add to this package a translation of Events Made Easy into his mother tongue.

== Screenshots ==

1. A default event page with a map automatically pulled from Google Maps through the #_MAP placeholder.
2. The events management page.
3. The Events Made Easy Menu.

== Changelog ==

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
* Improvement: revamped the admin settings interface, it was getting too much for one page so I switched to tabs

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

