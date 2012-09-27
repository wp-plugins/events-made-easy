=== Events Manager Extended ===  
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, manager, booking, calendar, gigs, concert, maps, geotagging, paypal  
Requires at least: 3.0.0
Tested up to: 3.2.1
Stable tag: 4.0.1

Manage and display events. Includes recurring events; locations; widgets; Google maps; RSVP; ICAL and RSS feeds; PAYPAL support. SEO compatible.
             
== Description ==
Events Manager Extended is a full-featured event management solution for Wordpress. Events Manager Extended supports public, private, draft and recurring events, locations management, RSVP (+ approval if wanted) and maps. With Events Manager Extended you can plan and publish your event, or let people reserve spaces for your weekly meetings. You can add events list, calendars and description to your blog using multiple sidebar widgets or shortcodes; if you are a web designer you can simply employ the template tags provided by Events Manager Extended. 

Events Manager Extended (EME) is a fork (NOT an extension) of the older Events Manager (EM) version 2.2.2 (April 2010). After months, the original plugin came back to life with a new codebase, but I added so much features already that it is very hard to go back to being one plugin. Read here for the differences since 2.2.2: http://www.e-dynamics.be/wordpress/?page_id=2 
Events Manager Extended integrates with Google Maps; thanks the geocoding, Events Manager Extended can find the location of your events, and accordingly display a map. 

Events Manager Extended provides also a RSS and ICAL feed, to keep your subscribers updated about the events you're organising. 

Events Manager Extended also integrates payments for events using paypal. 

Events Manager Extended is fully customisable; you can customise the amount of data displayed and their format in events lists, pages and in the RSS/ICAL feed. You can choose to show or hide the events page, and change its title.  

Events Manager Extended is fully localisable and already partially localised in Italian, Spanish, German, Swedish, French and Dutch. 

For more information visit the [Documentation Page](http://www.e-dynamics.be/wordpress/) and [Support Forum](http://www.e-dynamics.be/bbpress/). 

== Installation ==

Always take a backup of your db before doing the upgrade, just in case ...  
1. Upload the `events-manager-extended` folder to the `/wp-content/plugins/` directory  
2. Activate the plugin through the 'Plugins' menu in WordPress  
3. Add events list or calendars following the instructions in the Usage section.  
== Upgrade from the older Events Manager plugin ==

Events Manager Extended is completely backwards compatible with the old data from Events Manager 2.2.2. Just deactivate the old plugin, remove the files if you want, and proceed with the Events Manager Extended installation as usual. Events Manager Extended takes care of your events database migration automatically. 
Again my note of warning: Events Manager Extended (EME) is a fork (NOT an extension) of the older Events Manager (EM) version 2.2.2 (April 2010). After months, the original plugin came back to life with a new codebase, but I added so much features already that it is very hard to go back to being one plugin. Read here for the differences since 2.2.2: http://www.e-dynamics.be/wordpress/?page_id=2

== Usage == 

After the installation, Events Manager Extended add a top level "Events" menu to your Wordpress Administration.

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

Events list and calendars can be added to your blogs through widgets, shortcodes and template tags. See the full documentation at the [Events Manager Extended Support Page](http://www.e-dynamics.be/wordpress/).
 
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

= How does Events Manager Extended work? =   

When installed, Events Manager Extended creates a special "Events" page. This page is used for the dynamic content of the events. All the events link actually link to this page, which gets rendered differently for each event.

= Are events posts? =

Events aren't posts. They are stored in a different table and have no relationship whatsoever with posts.

= Why aren't events posts? =

I decided to treat events as a separate class because my priority was the usability of the user interface in the administration; I wanted my users to have a simple, straightforward way of inserting the events, without confusing them with posts. I wanted to make my own simple event form.  
If you need to treat events like posts, you should use one of the other excellent events plugin.

= Is Events Manager Extended available in my language? = 

At this stage, Events Manager Extended is only available in English and Italian. Yet, the plugin is fully localisable; I will welcome any translator willing to add to this package a translation of Events Manager Extended into his mother tongue.

== Screenshots ==

1. A default event page with a map automatically pulled from Google Maps through the #_MAP placeholder.
2. The events management page.
3. The Events Manager Extended Menu.

== Changelog ==

= Older versions =
* See the Changelog of the Events Manager plugin

= 3.0.0 =
* Bugfix: Fix for green screen caused by newlines in the location balloon
* Bugfix: Fix for rsvp contact mail (new: #_PLAIN_CONTACTEMAIL)
* Change: #_BOOKEDSEATS en #_AVAILABLESEATS are deprecated, in favor of #_RESERVEDSPACES and #_AVAILABLESPACES
* Change: The "add booking form" now shows only the number of available seats, not just the number 10
* Change: In order to not show a dropdown of 1000, we limit the number of seats you can book to a max of 10 default settings were not being set when activating the plugin
* Bugfix: Event_id, person_id in bookings table are not tinyints, also removed the
* Bugfix: remove the limit of tinyint for the number of seats
* Change: No seats available anymore? Then no booking form as well.
* Change: Now an error is returned to the user if on a booking form not all required fields are filled in
* Feature: Captcha added for booking form
* Bugfix: The shortcode [locations_map] once again works, failure was also due to newlines in the location balloon (fix in function eme_global_map_json in dbem_people.php)
* Rewrite of the widgets to the api used from wordpress 2.8 onwards, resulting in cleaner code and multi-instance widgets
* Bugfix: Some html cleanup for w3 markup validation
* Change: If the location name is empty: we don't show the map for the event
* Feature: You can now use normal placeholders in custom attribute values. Eg, in a template, you just add #_{MYOWNDATE} to the template. And then in the event, you can define this attribute with the value "#l #F #j, #Y" or with a complete string to your liking.
* Feature: You can now use custom attributes in email templates as well (eg. for different payment options per event).
* Bugfix: AM/PM notation now correct when using #_12HSTARTTIME and #_12HENDTIME as placeholders
* Feature: You can now have custom email settings and custom page formats per event, very convenient if the default is not ok for a special event.
* Feature: Recursion has been made a bit more complete: you can now have recursion based on the current day of the month. This makes it now possible to have eg. yearly recursion for a birthday or so (just start on the correct day and choose 12 months for recursion).
* Bugfix: Some change to the DB for recursion description to be correct (recurrence_byday is in fact a comma-seperated string containing the days of the week this event happens on)
* Bugfix: the shortcode [locations_map] once again accepts "scope" as a parameter. Eg. [locations_map eventful=true scope=future]
* Change: submenu pagename cleanup, html cleanup
* Bugfix: small category fix on the event overview/edit page (the event_id was used instead of event_category_id)

= 3.0.1 =
* Feature: now you can choose a category in the events widget, so only events of that category are shown

= 3.0.2 =
* Feature (for real now): now you can choose a category in the events widget, so only events of that category are shown  
  If you disable categories, the widget will show all events again as well.

= 3.0.3 =
* Change: now the single event formatting works also for recurring events
* Change: lots of code cleanups and extra checks
* Bugfix: editing a recurrence instance now changes it to a normal event as expected
* Bugfix: settings dbem_small_calendar_event_title_format and dbem_small_calendar_event_title_seperator are no longer ignored
* Bugfix: location deletion works again
* Bugfix/feature: more than one map on one page is now possible (for single/global maps mixed as well)

= 3.0.4 =
* Improvement: add Dutch translation (thanks to Paul Jonker)
* Feature: use google maps API v3, no more API key needed. But: 
  ==> no more IE6 support in API v3, so please don't ask me about it
* Feature: better CSS, create in the plugindir the file 'myown.css' if you want to override the CSS in events_manager.css (see the FAQ section)  
  ==> read the FAQ about how to size/style the balloon in the google map
* Bugfix: the RSVP form was only shown when google maps integration was active, now it is correctly shown when RSVP is wanted

= 3.0.5 =
* Improvement: for single events editing, the format windows are in the state closed by default
* Feature: #_LOCATION now also possible in the calendar title formatting
* Improvement: map only shown if location name/address/town all have a value
* Improvement: if any of event_single_event_format, event_page_title_format, event_contactperson_email_body, event_respondent_email_body is empty: display default value on focus, and if the value hasn't changed from the default: empty it on blur
* Improvement: make it more clear that a page needs to be chosen to show the events on
* Advertise that showing the event page itself is going to be deprecated
* Feature: captcha can be disabled now if you want, plus the session is hopefully started earlier so other plugins can't interfere anymore

= 3.1.0 =
* Bugfix: stripslashes needed for custom attributes
* Bugfix: when using scope=today, the sql query was wrong and thus ignored other conditions
* Bugfix: characters now get escaped ok in locations as well
* Improvement: changed the document to include better info concerning custom attributes
* Feature: you can now choose whether or not registrations need approvements, and then manage pending registrations
* Feature: you can now edit the number of seats somebody registered for, in case they change their minds
* Improvement: force the use of the datepicker for start/end dates by making the field readonly, so no more empty dates

= 3.1.1 =
* Improvement: use constants DBEM_PLUGIN_URL and DBEM_PLUGIN_DIR
* Feature: categories possible for events_calendar widget and events_calendar shortcode
* Bugfix: javascript error fix when editing/creating an event

= 3.1.2 = 
* Feature: qtranslate can now be used together with Events Manager Extended
* Bugfix: better checking for special characters used in events name/location/...
* Bugfix/feature: event attributes are now also taken into account for recurring events
* Bugfix: language setting now happens on init action, better for qtranslate and all
* Bugfix: autocomplete is working again for locations when creating an event
* Bugfix: sort by day and time for the full calendar
* Improvement: English, French languages updates (thanks to Sebastian), Dutch updated by me

= 3.1.3 = 
* Improvement: French, German language updates (thanks to Sebastian), Spanish language updates (thanks to Ricardo)
* Workaround: hopefully no more google balloon scrollbars
* Feature: events can belong to multiple categories now
* Feature: #_CATEGORIES shortcode available, will return a comma-seperated list of categories an event is in
* Feature: #_DIRECTIONS shortcode available, so you can ask for driving directions to an event/location
* Feature: new shortcode available: [display_single_event], eg: [display_single_event id=23]
* Feature: show month or day in events_list if wanted (new parameter for shortcode [events_list]: showperiod=daily|monthly)
* Feature: the attribute 'scope' for the shortcode [events_list] can now contain a date range, eg. [events_list scope=2010-00-00--2010-12-31 limit=200] 
* Feature: "limit=0" now shows all events (pending other restrictions) for the shortcode [events_list]
* Bugfix: updating a recurrent event with booking enabled, deleted all existing bookings for each event of the recurrence

= 3.1.4 = 
* Improvement: use the wordpress defined charset and collation for the DB tables, this will benefit those with weird character sets
* Bugfix: Changing the registration (number of reserved places) of a user for an event works again
* Bugfix: the showperiod option to the [events_list] resulted in non-translated names for month/day. Has been fixed.
* Bugfix: the special events page no longer changes the menu title
* Feature: #_ATTENDEES shortcode available, will return a html-list of names attending the event
* Feature: when editing an event, you can now make it recurrent

= 3.1.5 = 
* Improvement: if you forget to deactivate/activate the plugin for needed DB updates, you'll get a warning now
* Bugfix: don't overwrite widget content anymore
* Bugfix: calendar ajax fixes for full, long_events and category options
* Cleanup: strip many trailing spaces, and resolve all possible php warnings
* Feature: honeypot field implemented, this is a hidden field that humans can't see, but a bot will enter something in it and that's something we can check on
* Feature: you can now require that people need to be registered to wordpress in order to make a booking
* Feature: next to "OR" for categories, you can now have "AND" as well: [events_list category=1,3] is for "OR", [events_list category=1+3] is for "AND"

= 3.1.6 = 
* Bugfix: booking name/email fields were readonly, has been fixed

= 3.2.0 = 
* Bugfix: tablenav issue caused events list to dissapear in the admin interface using IE7
* Bugfix: when duplicating an event, we now edit the duplicate event afterwards, not the original mixed in
* Bugfix: ajax fix for calendar (thanks to wsherliker)
* Feature: status field for events: Public, Private, Draft. Private events are only visible for logged in users, draft events are not visible from the front end.
* Feature: permissions now being checked for creation/editing of events:
	- a user with role "Editor" can do anything
	- with role "Author" you can only add events or edit existing events for which you are the author or the contact person
	- with role "Contributor" you can only add events *in draft* or edit existing events for which you are the author or the contact person
* Renamed all dbem_* functions to eme_ functions, just not the DB tables yet (later). As a result there are some actions required:
	- people using the API in their templates will need to change these to match the new naming convention (just rename "dbem_" to "eme_")
	- people using their own CSS will need to change these as well ((just rename "dbem_" to "eme_")

= 3.2.1 =
* Bugfix: typo fix for capabilities for categories, the categories menu didn't show up in the admin menu

= 3.2.2 =
* Bugfix: add/delete location now works again
* Bugfix: when duplicating an event, the author of the new event is now set correctly
* Bugfix: categories working again
* Bugfix: languages working again

= 3.2.3 =
* Bugfix: sending mails works again
* Feature: new parameter for shortcode [events_list]: author, so you can show only events created by a specific person. Eg: [events_list author=admin] to show events from author with loginname "admin", [events_list author=admin,admin2] for authors admin OR admin2
* Feature: ical subscription is now possible for public events. Just use "?eme_ical=public" after your WP url, and you'll get the ical feed. Eg.: http://www.e-dynamics.be/wordpress/?eme_ical=public. Shortcode [events_ical_link] has been created for your convenience.

= 3.2.4 =
* Improvement: CSS fixes
* Feature: new placeholder #_ICALLINK for a single event, so you get a link to an ical event just for that link. The shortcode [events_ical_link] can of course still be used.
* Feature: calendar and event list widgets now also support author as a filter
* Feature: you can now customize the date format for the monthly period in the EME Settings page, used when you give the option "showperiod=monthly" to the shortcode [events_list]
* Feature: specifying a closing day for RSVP is now possible
* Feature: you can now change the text on the submit buttons for RSVP forms in the EME Settings page.

= 3.2.5 = 
* Bugfix: make location autocomplete work again when editing an event
* Bugfix: when creating an event, the location map was not updated automatically anymore
* Feature: #_DIRECTIONS now also possible for the location infowindow (balloon). I don't recommend it though, since you need to increase the size of the balloon way too much using extra html break-tags.
* Feature: if you use "scope=this_month" as a parameter to the [events_list] shortcode, it will now show all events in the current month
* Feature: if you use "scope=0000-04" as a parameter to the [events_list] shortcode, it will now show all events in month 04 of the current year
* Feature: for bookings where you need to be a WP member, the phone number is no longer required.
* Feature: the format of the attendees list can now be customized
* Improvement: show RSVP info in the events list in the admin backend as well
* Improvement: you can use #_DETAILS as an alternative to #_NOTES as placeholder for the event description
* Improvement: when deleting a booking using the provided form, now only the booking for that event gets deleted, no longer all bookings for that person
* Improvement: the RSVP closing date now also stops showing the delete booking form
* Improvement: for RSVP that require WP membership, the user info (mail/name) is always gotten again from WP info when showing the RSVP members and such. So when a user changes his name/email it immediately shows on the list

= 3.2.6 =
* Bugfix: location table gets created again

= 3.2.7 =
* Bugfix: rsvp is by default allowed until the hour the event starts, not just the day
* Bugfix: booking name/email once again shows correctly (was bugged and fixed in 3.2.5, but the fix didn't make it in 3.2.6)

= 3.2.8 =
* Bugfix: deleting a category now works again
* Bugfix: use plugins_url() the get the url, this is https safe (apparently WP_PLUGIN_URL is not)
* Bugfix: one could only add phonenumbers to profiles other than his own, has been fixed
* Bugfix: shortcode #_ATTENDEES should be an empty string if the event is not RSVP-able
* Bugfix: use .text() for jquery in eme_calendar.php, works in Chrome also
* Bugfix: take server timezone into account for ical and calendar
* Bugfix: the title of an event wasn't escaped properly when editing
* Bugfix: the calendar wouldn't show events on months later/before the current day
* Bugfix: correct pagination
* Bugfix: show all events of a day if that day is requested (calendar_day scope) and not just 10
* Feature: 5th occurence of a weekday in a monthly recurrence can now be chosen
* Feature: direct link to the printable list of reservations shown from the page with the list of events
* Feature: you can now enable registrations by default for new events
* Feature: you can now specify a default number of spaces for RSVP-able events
* Feature: added an admin option to delete all EME tables/option when uninstalling, usefull as well when EM was in the picture

= 3.2.9 =
* Bugfix: small bugfix in javascript for other languages when generating the unique map ID
* Bugfix: RSS and ICAL feeds don't need the booking forms, it messes up the layout
* Bugfix: show events on current day (bug created in 3.2.8)
* Bugfix: in the admin itf, not all editable events were shown when logged in as an author
* Bugfix in datepicker jquery for Finnish language
* Feature: "scope=next_month" now possible as a parameter to the [events_list] shortcode
* Feature: when using the dropdown for locations, you can now select an empty location as well

= 3.2.10 =
* Bugfix: let the includes happen later on, so all init code happens first
* Bugfix: better placeholder matching/replacing
* Bugfix: don't use mysql2date, it doesn't respect the "G" for the date function, we now use date_i18n+strtotime
* Feature: added an option to remove leading zeros from minutes: 09 becomes 9, 00 becomes empty
* Feature: you can now hide RSVP-able events that have no more spaces available
* Feature: filter event list by category in admin interface is now possible
* Feature: pagination in the events is now possible in the frontend using a new parameter for shortcode [events_list]: paging=1 (default=0)
* Feature: you can now remove people and their associated bookings via the people page in the admin backend

= 3.2.11 =
* Bugfix: make limit=0 work again
* Bugfix: again better placeholder matching/replacing

= 3.2.12 =
* Security fix: extra code so no html can be inserted into a name/phone/comment when doing a booking
* Minor bugfix: remove function br2nl, not used and can conflict with other plugins
* Minor bugfix: some label corrections
* Minor bugfix: jquery for ajax calendar now supports the language as well
* Minor bugfix: show events spanning over multiple days (long events) as such when using the show_period option in [events_list]
* Minor bugfix: when editing your profile, the phonenumber of admin was always shown, even though you changed it correctly to your own
* Minor bugfix: full calendar also supports multiple categories now
* Bugfix: eme_install could fail, corrected
* Improvement: better multiline support for ICAL events and a bit support for outlook 2003
* Improvement: the categories of an event are now shown if any in the RSS feed
* Improvement: add locales nn and nb to localised_date_format info
* Improvement: NL language update (thanks to Paul Jonker)
* Improvement: CSS added for navigation arrows in the frontend (eme_nav_left and eme_nav_right)
* Feature: you can now specify the number of events to be shown in the RSS feed, as well as specify the order, category, author and scope (like for eme_get_events)
* Feature: better CSS adaptation possible: create in your theme CSS dir the file 'eme.css' if you want to override the CSS in events_manager.css
  (see the FAQ section)  
* Deprecated: the use of 'myown.css' in the plugin dir (use 'eme.css' in your theme CSS dir)

= 3.2.13 =
* Bugfix: showperiod works correctly again
* Bugfix: paging count in events_list was wrong if you used paging=1 and showperiod options together
* Bugfix: re-added missing template function eme_is_event_rsvpable
* Bugfix: eventful can now be a boolean (true/false) next to 1/0
* Bugfix: finally a good method to match all placeholders and not ending up replacing e.g. #_LOCATION in #_LOCATIONPAGEURL by something not wanted
* Bugfix: monthly offsets needs to be calculated based on the first day of the current month, not the current day, otherwise if we're now on the 31st we'll skip next month since it has only 30 days
* Workaround: work around a bug in wordpress phpmailer, where it searches for class-smtp.php in the wrong location
* Feature: added long_events to eme_events (shortcode and template function) as well
* Feature: scope=this_week now possible for shortcode [events_list]
* Feature: filter by contact_person now possible for shortcodes [events_list] and [events_calendar]
* Feature: period paging is now possible if you use paging=1, limit=0 and scope=today,this_week,this_month in the [events_list] shortcode. Eg:
  [events_list paging=1 limit=0 scope=this_week]
* Feature: period paging is now possible if you use eventful=true, paging=1 and scope=today,this_week,this_month in the [locations_map] shortcode. Eg:
  [locations_map eventful=true paging=1 scope=this_week]
* Feature: new placeholder #_PAST_FUTURE_CLASS, returning a string that indicates wether this event is in the future or not (eme-future-event or eme-past-event), can be used as extra CSS to the event list 
* Feature: shortcode [locations_map] can now use the same values for scope and category as the shortcode [events_list], these are only honoured if the parameter eventful=true
* Feature: new shortcode [display_single_location], accepts 'id' as parameter with value the location ID you want to show
* Feature: new placeholder #_LOCATIONID that just gives you the location ID
* Feature: you change now change the number of events shown in the admin interface settings
* Feature: you can now specify the default number of events to show in a list if no specific limit is specified (used in the shortcode events_list, RSS feed, the placeholders #_NEXTEVENTS and #_PASTEVENTS, ...)
* Improvement: the google map javascript code will now only get loaded if/when needed at the bottom of pages, and no longer always at the top
* Improvement: the calendar jquery javascript code will now only get loaded if/when needed at the bottom of pages, and no longer always at the top
* Improvement: show the database error if event inserting fails
* Improvement: the zoomlevel was not correct on the [locations_map] shortcode: due to map.fitbounds, the map zoomed out too much if there were no markers, or zoomed in too much if only one marker
* Improvement: the submenus concerning RSVP are no longer shown if RSVP is not active, the same goes for categories

= 3.2.14 =
* Bugfix: "missing argument" fix

= 3.2.15 =
* Bugfix: better indication for eme_need_gmap_js and eme_need_calendar_js, so it works for templating as well
* Bugfix: fix broken empty attributes detection again

= 3.3.0 = 
* Feature: email now also sent when someone cancels a registration
* Feature: new setting, to enable shortcodes in widgets as well
* Feature: the global map now shows the same balloon info as the single map created from the placeholder #_MAP
* Feature: the calendar options "Small calendar title" and "Full calendar events format" can now also use custom attributes as placeholders
* Feature: EME installs fine for subsites now if you network activate the plugin for a multisite setup
* Feature: added action hooks:
  eme_insert_event_action (1 parameter: $event)
  eme_update_event_action (1 parameter: $event)
  eme_insert_recurrence_action (2 parameters: $event,$recurrence)
  eme_update_recurrence_action (2 parameters: $event,$recurrence)
  eme_insert_rsvp_action (1 parameter: $booking), executed after inserting rsvp info into the db
* Feature: added filters:
  eme_event_filter (1 parameter: $event array)
  eme_event_list_filter (1 parameter: array of events)
  eme_location_filter (1 parameter: $location array)
  eme_location_list_filter (1 parameter: array of locations)
* Feature: making location_id also a possible filter for the calendar and events_list shortcode
* Feature: extra "scope=tomorrow" possible for events_list shortcode
* Feature: new shortcode [events_locations], giving you a list of all locations (for all possible parameters: see the doc site), when used in combo with the calendar it can be used to show only specific events in the calendar (use the 'class' parameter then with value 'calendar')
* Feature: new shortcode [events_filterform], creates a form with dropdowns for categories, locations, towns, weeks, years  (configurable in the settings). To be used on the same page as [events_list], otherwise it has no effect. Accepts 4 parameters: multiple (0/1), if you want to be able to select multiple categories, locations, towns (not weeks, years), multiple_size (the size of the dropdown window if multiple), scope_count (the number of future weeks/months to be shown), submit (the text on the submit button)
* Feature: you can now use conditional placeholder formatting using a new shortcode [events_if] in your formatting, by prepending them with "#ESC". An example for default single event formatting:
  <p>#j, #M #Y - #H:#i</p><p>#_NOTES</p>#_LOCATIONPAGEURL
  [events_if tag='#ESC_ATT{color}' value='red'] color: #_ATT{color} [/events_if]
  [events_if tag='#ESC_ATT{price}'] price: #_ATT{price} [/events_if]
  [events_if tag='#ESC_TOWN'] Town: #_TOWN [/events_if]
* Also for conditional tags: added [events_if2] and [events_if3], if you want to use more than one level of logic (wordpress doesn't like a shortcode with the same name enclosed in another one)
* Feature: for conditional tags, I added 4 extra shortcodes:
  #_IS_SINGLE_EVENT ('1' if you're viewing a single event details, '0' otherwise)
  #_IS_SINGLE_LOC ('1' if you're viewing a single location details, '0' otherwise)
  #_IS_LOGGED_IN ('1' if user is logged into WP, '0' otherwise)
  #_IS_ADMIN_PAGE ('1' if on the admin pages)
* Feature: you can now specify header/footer html code for the list widget as well
* Feature: widget title is allowed to be empty
* Feature: "require WP membership for registration" can now be set per event
* Feature: added #_ICALURL, giving you just the url to the ical, so you can build your own link
* Feature: added #_ADDBOOKINGFORM_IF_NOT_REGISTERED and #_REMOVEBOOKINGFORM_IF_REGISTERED
* Feature: you can now define a min/max number of spaces a person can book in one go (0 is possible)
* Improvement: some browser incompatibilities solved
* Improvement: more robust javascript for prefilling the formatting per event
* Improvement: if the setting "Events page" is not set, no more will other pages be overwritten with events data
* Change: the #_EXCERPT now only works (show the part before the more-tag) if not on the single event page, there it would show the whole content
* Bugfix: #_EXCERPT layout was not ok, should be the same as for #_NOTES
* Bugfix: the setting for "default contact person" was being ignored and has been corrected. Also I specified that the default contact person is the event author from now on (can still be changed in the settings page)
* Bugfix: stricter matching, only replace placeholders that are found, not everything starting with #
* Bugfix:  bug that was triggered when "hide RSVP full events" was selected and no RSVP was made yet. It triggered a mysql statement that returned NULL where it should've been 0

= 3.3.1 = 
* Feature: you can now use "contains" in the conditional tag [events_if]
* Feature: added #_EVENTID, gives you the event ID if you want some unique identifier
* Feature: #URL prefix possible for all placeholders, forces raw_urlencode on it, so you can use every info in custom links
* Feature: for the filter form, you can now use #_EVENTFUL_FILTER_CATS, #_EVENTFUL_FILTER_LOCS and #_EVENTFUL_FILTER_TOWNS to just show those that have events
* Feature: you can indicate "none" as a category as well for the events_list, to find events without categories: [events_list category=none] or [events_list category=2,none]
* Feature: the shortcode [events_filterform] can now also be used to filter a calendar rendered by [events_calendar] (not the scope though)
* Feature: you can now use [events_list] in a location description, with paging!
* Feature: when using [events_list] in a location description, you can use "location_id=this_location" to limit the events to just that location
* Improvement: the month in the calendar widget shown depends on the calendar day clicked
* Bugfix: #_CONTACTEMAIL was no longer correctly replaced
* Bugfix: using #@_{} notation resulted in empty string if end and start date were the same, not intended behaviour ...
* Bugfix: using #_{} or #@_{} resulted in the minutes being ignored ...
* Bugfix: choosing the author in the calendar widget wasn't working (typo)
* Bugfix: using scope in [events_list] resulted in other conditions to be mangled

= 3.3.2 =
* Feature: SEO/permalinks for single events, locations and calendar days
* Feature: filter form now also works for calendar concerning the scope (week or month)
* Feature: [events_filter] has a new parameter "fields", can contain a comma seperated list of fields that should be shown (the filter format is general, and using this, you can hide certain fields in certain forms). For a calendar, you may want to block showing weeks, so you can use eg: [events_filter fields=categories,months] (and make sure the "filter form format" contains at least the filters for month and cats)
* Feature: new placeholder for events: #_USER_RESERVEDSPACES (or #_USER_BOOKEDSEATS), gives the number of seats a users has registered for an event
* Feature: new parameter for [events_list]: "user_registered_only". If value=1, it will only give a list of events the user has registered for (only works for WP registered users of course)
* Feature: "showperiod=yearly" now also possible, also filtering per year
* Feature: external link possible for an event, so when you click on the single event details, you go to that page
* Feature: added the ability to clean up old events
* Feature: you can now disable/enable the use of the scrollwheel inside google maps
* Feature: you can now use "notcontains" in the conditional tag [events_if]
* Improvement: Czech language update (thanks to Alan Eckhardt)
* Improvement: hour also taken into account for future/past events list
* Improvement: to prevent going on indefinitely and thus allowing search bots to go on for ever, we stop providing links if there are no more events left
* Improvement: JQuery lib updates for timeentry
* Improvement: removed more php warnings
* Bugfix: #_CATEGORIES should seperate categories by ", " and not just ","
* Bugfix: the filter form was not performing the correct sql for multiple town filtering
* Bugfix: the filter form was not correctly escaping the input
* Bugfix: the calendar prev/next links are now javascripted per calendar div, important if you have more than one calendar on the page (otherwise it could cause an avalange of jquery requests)
* Bugfix: the ajax code for the calendar and locations was not returning the data in the UTF-8 (or blog) charset
* Bugfix: prevent ajax caching

= 3.3.3 = 
* Feature: added #_LATITUDE and #_LONGITUDE placeholders
* Feature: added #_IS_RSVP_ENABLED shortcode, returns 1 if RSVP is enabled and active for the event, 0 otherwise
* Feature: you can disable EME SEO permalinks, if it doesn't work or you just don't want to use them ...
* Bugfix: rewrite rules (for permalinks) should use home_url(), not site_url()
* Bugfix: #_LOCATIONPAGEURL didn't return a SEO permalink
* Bugfix: #_PAST_FUTURE_CLASS didn't work as expected
* Bugfix: list widget was not escaping all characters correctly when editing its settings
* Bugfix: filter form fix

= 3.3.4 = 
* Feature: added #_LATITUDE and #_LONGITUDE placeholders also for locations page
* Feature: added #_IMAGEURL placeholder, returns just the url of the image
* Feature: no event_id? then redir to 404
* Feature: CSV export of attendees is now possible
* Feature: the calendar can now be based on the client date/time if configured so (using php sessions for that, thanks to admintiger)
* Feature: #_PAST_FUTURE_CLASS also generates a new class for ongoing events (class 'eme-ongoing-event')
* Feature: new parameter for the shortcode [locations_map]: list_location. Specifies the place where the location list will be shown: before the map, after the map, or not (values: 'before','after','none')
* Improvement: enqueue_style used for CSS in frontend. Style ids enqueued: eme_stylesheet and eme_stylesheet_extra (if eme.css is present in your theme)
* Improvement: the filters for locations, town and categories now return sorted values
* Improvement: full calendar now also shows full month name
* Improvement: list of attendees is now sorted
* Improvement: more than 26 location markers on the map are now supported
* Improvement: added Polish translation (thanks to Michal)
* Improvement: generalize JS in eme_locations.php, so it works with newer jquery as well
* Bugfix: tinymce editor fix for 3.1
* Bugfix: some calendar js fixes
* Bugfix: event #_NOTES and location #_DESCRIPTION placeholders must be replaced after the other placeholders, otherwise unwanted replacement in their content can take place
* Bugfix: the special events page can now be a subpage of another (although not recommended) and SEO will continue to work (workaround for WP weird behaviour when using pagename as param to index.php for rewriting rules: page_id is more reliable)
* Bugfix: for recurrent events, in case the end time crosses midnight (and as such is lower than the start time), the end day should be the next day
* Bugfix: for single events, if end day equals start day and end time is smaller than start time, put end day one day ahead
* Bugfix: #_NOTES should be in RSS as well, not just the excerpt
* Bugfix: the 'hide events when full' sql was wrong

= 3.3.5 =
* Feature: added WP filter eme_directions_form_filter, so you can change the form generated by #_DIRECTIONS to your liking
* Feature: Hebrew translation (thanks to Edna)
* Feature: filter on status now possible in admin events screen
* Feature: the attendee list can now also show the number of reserved spaces per person
* Feature: the conditional tags now also can check if a tag is empty by adding "is_empty=1" as extra condition
* Feature: scopes "this_year" and "next_week" are now possible for shortcode events_list
* Feature: scope="YYYY-MM-DD--today" and "today--YYYY-MM-DD" are now possible, to show events from a certain day in the past till now or from now till some day in the future, also "this_week--today", "this_month--today", "this_year--today", "today--this_week", "today--this_month", "today--this_year"
* Feature: scope=+Nd, scope=-Nd, scope=+Nm, scope=-Nm, with "N" being a number, so you can now go N days/months in the past/future.
* Feature: scope=Nm--Mm, to get the events from month N in the past/future till month M in the past future (eg. scope=-3m--2m , scope=0m--3m)
* Feature: for conditional tags, I added a new shortcode:
  #_IS_PRIVATE_EVENT ('1' if event is private, '0' otherwise)
* Improvement: Updated pt_BR language (thanks to  Gustavo Sousa)
* Improvement: fix some php notices
* Improvement: you can use calmonth and calyear as url parameters to influence the year/month of the calendar being shown
* Bugfix: WP changed the function sanitize_title_with_dashes in 3.1, so it didn't replace accented characters anymore. Workaround has been put in place
* Bugfix: quote the person id in the SQL query for bookings to account for empty variables
* Bugfix: better avoidance of duplicate div-id's for location maps when called in the event list
* Bugfix: when no bookings are made, 0 is now returned for RESERVEDSPACES and other shortcodes alike, and no longer an empty string 
* Bugfix: removed some old code that was checking for magic_quotes_gpc, no longer needed
* Bugfix: for single events, if end day equals start day and end time is smaller than start time, put end day one day ahead but only if the end time has a value (if not: keep the end day intact)
* Bugfix: Hack to make the google maps window size correct before the map is shown, so it's not cut off the first time
* Bugfix: Fixed private events to be totally hidden
* Bugfix: for correct RSS validation, <item> should start on a newline
* Bugfix: escape some chars for RSS feeds

= 4.0.0 = 
* Feature: added option "show_ongoing" to [events_list], to indicate you want the scopes to include end dates (ongoing events) upon evaluation (like e.g. future events include events starting in the past, but ending in the future), or just the start date. Default: 1
* Feature: you can now set the desired capability a user must have in order to do things (access right management). Use a plugin like "User Role Editor" to add/edit capabilities and roles.
* Feature: eme_add_booking_form_filter and eme_delete_booking_form_filter filters added, so you can change the form html to your liking
* Feature: split the function eme_display_single_event_shortcode into 2 parts, creating the function eme_display_single_event() that takes as single argument the event id, returning the html output for that event
* Feature: recurrent events can now span multiple days
* Feature: new shortcode [events_countdown], with one optional parameter (event ID): returns the number of days until the next event or the event specified
* Feature: the shortcode [events_ical_link] now also accepts category as an option
* Feature: locations now also can have categories on their own, and the "category"-option in [events_locations] will now show locations in those categories if the "eventful"-option is not set
* Feature: locations now also have an author, and people can have the right to add, edit own or edit all locations
* Feature: #_CATEGORIES for locations now returns the categories a location is in (as for events)
* Feature: new option link_showperiod for shortcode [events_list]: if showperiod=daily and link_showperiod=1, then the shown days are links that will go to events for just that day
* Feature: new option notcategory for shortcode [events_list]: works as the category option but serves to exclude categories
* Feature: for conditional tags, I added 1 extra shortcode:
  #_IS_SINGLE_DAY ('1' if you're viewing a single day, '0' otherwise)
* Feature: new shortcode #_CALENDAR_DAY, returning the day being viewed when viewing a specific day on the calendar
* Feature: added filter to do own email obfuscating: eme_email_filter. If defined, the standard ascii obfuscating won't take place and you can use your own
  filters, eg. from an obfuscating plugin, if you define it in functions.php:
  add_filter( 'eme_email_filter', 'c2c_obfuscate_email' );
* Feature: locations now also can point to an external url, as events can already
* Feature: extra html headers can now be added, usefull for e.g. meta tags for facebook or seo
* Feature: now you can delete events of an recurrence or the whole recurrence (no longer needed to take an event out of an recurrence before being able to delete just that event)
* Feature: the permalink prefix for events and locations can now be changed in the settings page. After each change, you need to press save on the wordpress permalinks settings page before changes take effect.
* Feature: the event or location permalink slug can now be changed manually, but for now the event/location ID remains required
* Feature: the pending email body text can now also be changed per event
* Feature: basic Paypal integration
* Improvement: the RSVP form now always prefills the name and email if you're logged in, if no WP membership is required you can change the values
* Improvement: the no-events-message needs to be formatted by the user, not in the code using ul/li constructs
* Improvement: for format: sometimes people want to give placeholders as options, but when using the shortcode inside another (e.g. when putting [events_list format="#_EVENTNAME"] inside the "display single event" setting, the replacement of the placeholders happens too soon (placeholders get replaced first, before any other shortcode is interpreted). So we add the option that people can use "#OTHER" as prefix for any placeholder inside format (like #ESC works)
* Improvement: "change registrations" now only shows the approved registrations, not pending ones
* Improvement: in the events/locations admin section (add/edit), the categories are now sorted alphabetically
* Improvement: #_NAME has been deprecated in favor of #_EVENTNAME
* Improvement: #_LOCATION has been deprecated in favor of #_LOCATIONNAME
* Improvement: #_PLAIN_CONTACTEMAIL has been deprecated in favor of the existing #_CONTACTEMAIL
* Bugfix: AND categories for [events_list] were no longer working and resulted in all categories being used
* Bugfix: some filtering fixes in admin panel
* Bugfix: scope=this_week/next_week now takes the "start day of week" WP setting into account
* Bugfix: cancelled registrations reported the wrong number of seats cancelled in the mail
* Bugfix: an inappropriate mysql warning when updating a location without changing anything (wpdb->update returns 0 because 0 rows changed) has been eliminated
* Bugfix: the names of users logged into WordPress were not pre-filled in the delete booking form
* Bugfix: fixing a bug with quotes in category names
* Bugfix: cancel emails were not being sent
* Bugfix: #_IMAGEURL didn't return the location image url for events (it worked ok for locations itself)
* Bugfix: wordpress inserts canonical url's since 3.0, but these point to the page url. Fixed so the correct canonical url is inserted for single locations or events.
* Bugfix: get rid of some php notices in the event creation form

= 4.0.1 = 
* Feature: added placeholders #_PRICE and #_CURRENCY, so you can show the price for an event
* Feature: you can now specify any scope in the events list widget
* Feature: added scope='tomorrow--future', so you can show events in the future but not happening today. The normal scope=future once again shows events happening today as well
* Feature: you can now add a registration in the backend
* Improvement: for location formatting, #_NAME has been deprecated in favor of #_LOCATIONNAME
* Improvement: added payed status to the printable booking report
* Bugfix: price can now be more than two digits
* Bugfix: fixed two small issuess in eme_rsvp.php
* Bugfix: the array for the eme_insert_event_action action hook didn't have the event ID
* Bugfix: fixed a recurrence insert when the day of the week was not checked
* Bugfix: fixed a recurrence insert when the end day of the single event wasn't set (it resulted in a 2-day event)

= 4.0.2 =
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
