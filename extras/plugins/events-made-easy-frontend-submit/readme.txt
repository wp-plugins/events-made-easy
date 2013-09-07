Customization
=============
The plugin will look for configuration, form template and style files in the following paths, in that priority:

   1. ./wp-content/themes/your-current-theme/events-made-easy-frontend-submit/
   2. ./wp-content/themes/your-current-theme/emefs/
   3. ./wp-content/themes/your-current-theme/events-made-easy/
   4. ./wp-content/themes/your-current-theme/eme/

The overloadable files at this moment are:

   1. form.php which controls the html form
   2. style.css which controls the style loaded automatically by the plugin
   3. config.php which controls configuration options for the plugin's behaviour


Configuration
=============
The config.php file will be loaded upon plugin start. At the moment there are four options that you should configure:

    * $config['success_page'] = false; - Id of the page where user will be redirected hen submission is successful. Defaults to false, which triggers a warning message and disables the plugin usage.
    * $config['auto_publish'] = false; - Auto publishing of the submitted events. Defaults to false, which inserts the events as drafts. If you wish to set autopublishin, set this to the status you want by default. 1 for public (or the STATUS_PUBLIC constant set by EME), 2 for private (or the STATUS_PRIVATE).
    * $config['public_submit'] = true; - Allows submission by users who are not registered. Defaults to true. If set to false, the next value has to be set with the correct configuration or you will recieve an error instead of the form.
    * $config['public_not_allowed_page'] = false; – Page Id to redirect to if public_submit is not allowed and current user is not at least a registered contributor.

Your configuration file should look like this:

<?php
$config['success_page'] = 174;
$config['auto_publish'] = STATUS_PUBLIC;
$config['public_submit'] = false;
$config['public_not_allowed_page'] = 154;
?>

don't forget the <?php and ?> or it won't work!!
Form and Style

By copying form.php and style.css from ./wp-content/plugins/events-made-easy-frontend-submit/templates/ to any of the paths listed above, you will be able to override their contents and fit the form to your purposes.

[Usage]

Just put the shortcode [submit_event_form] on a page and the event submit form will show, if the config.php file is present.
