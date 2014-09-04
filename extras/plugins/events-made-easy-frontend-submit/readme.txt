=== EME Frontend Submit ===
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, frontend
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin that displays a form to allow people to enter events for the Event Made Easy plugin on a regular wordpress page.

== Description ==

A simple plugin that displays a form to allow people to enter events for the Event Made Easy plugin on a regular wordpress page (called "Frontend Submit").

Get The Events Made Easy plugin:
http://wordpress.org/extend/plugins/events-made-easy/

== Installation ==

1. Download the plugin archive and expand it
2. Upload the events-made-easy-frontend-submit folder to your /wp-content/plugins/ directory
3. Go to the plugins page and click 'Activate' for EME Frontend Submit
4. Navigate to the Settings section within Wordpress and change the settings appropriately.
5. Ensure the Events Made Easy plugin is installed and configured - http://wordpress.org/extend/plugins/events-made-easy/
6. Put the shortcode [emefs_submit_event_form] on a regular page to display the form


The plugin will look for form template and style files in the following paths, in that priority:

   1. ./wp-content/themes/your-current-theme/eme-frontend-submit/
   2. ./wp-content/themes/your-current-theme/events-made-easy-frontend-submit/
   3. ./wp-content/themes/your-current-theme/emefs/
   4. ./wp-content/themes/your-current-theme/events-made-easy/

The overloadable files at this moment are:

   1. form.php which controls the html form. The default version can be found in the templates subdir.
   2. style.css which controls the style loaded automatically by the plugin. The default version can be found in the templates subdir.

== Changelog ==

= 1.0.0 =
Released as seperate wordpress plugin, using it's own WP settings (no config file anymore)
