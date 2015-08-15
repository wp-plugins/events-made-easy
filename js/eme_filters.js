jQuery(document).ready( function() {
   jQuery.datepick.setDefaults( jQuery.datepick.regionalOptions[datepick_locale_code] );
   jQuery.datepick.setDefaults({
      changeMonth: true,
      changeYear: true,
      altFormat: "yyyy-mm-dd",
      firstDay: firstDayOfWeek
   });
   jQuery("#eme_localised_scope_filter").datepick({ rangeSelect: true, rangeSeparator: '--', monthsToShow: 2, altField: "#eme_scope_filter" });
});
