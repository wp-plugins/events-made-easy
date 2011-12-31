=== Events Made Easy ===  
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, manager, booking, calendar, gigs, concert, maps, geotagging, paypal  
Requires at least: 3.0.0
Tested up to: 3.2.1
Stable tag: 1.0.1

Manage and display events. Includes recurring events; locations; widgets; Google maps; RSVP; ICAL and RSS feeds; PAYPAL support. SEO compatible.
             
== Description ==
Events Made Easy (formally called 'Events Manager Extended') is a full-featured event management solution for Wordpress. Events Made Easy supports public, private, draft and recurring events, locations management, RSVP (+ approval if wanted) and maps. With Events Made Easy you can plan and publish your event, or let people reserve spaces for your weekly meetings. You can add events list, calendars and description to your blog using multiple sidebar widgets or shortcodes; if you are a web designer you can simply employ the template tags provided by Events Made Easy. 

Events Made Easy (EME) is a fork (NOT an extension) of the older Events Manager (EM) version 2.2.2 (April 2010). After months, the original plugin came back to life with a new codebase, but I added so much features already that it is very hard to go back to being one plugin. Read here for the differences since 2.2.2: http://www.e-dynamics.be/wordpress/?page_id=2 
Events Made Easy integrates with Google Maps; thanks the geocoding, Events Made Easy can find the location of your events, and accordingly display a map. 

Events Made Easy provides also a RSS and ICAL feed, to keep your subscribers updated about the events you're organising. 

Events Made Easy also integrates payments for events using paypal. 

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

= Older versions =
* See the Changelog of the Events Manager Extended plugin

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

= 1.0.1 =
* Bugfix: fix replacement in menu for page title by events title when viewing a single event
* Bugfix: fixed a closing-div tag, preventing the rich html-editor to appear sometimes for locations

= 1.0.2 =
* Feature: placeholders #_TOTALSPACES and #_TOTALSEATS added (gives the total amount of spaces for an event)
* Feature: events can now also have a featured image, like locations
* Extra: included the plugin events-made-easy-frontend-submit, see the dir extras/plugins/events-made-easy-frontend-submit
* Bugfix: typo fix in eme_events.php influencing location showing
* Bugfix: typo fix in eme_events.php for a jquery statement
* Bugfix: when adding a registration via the backend and approval was required, the registered person would get a pending message although it was already approved
* Bugfix: #_USER_RESERVEDSPACES wasn't working correctly for the attendees format setting
* Bugfix: location title was not qtranslate-ready in the [events_location] shortcode
