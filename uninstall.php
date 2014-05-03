<?php

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

include("events-manager.php");

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


?>
