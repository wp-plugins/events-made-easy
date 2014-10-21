jQuery(document).ready( function() {
   jQuery("#eme_localised_scope_filter").show();
   jQuery("#eme_scope_filter").hide();

   jQuery.datepick.setDefaults( jQuery.datepick.regionalOptions[locale_code] );
   jQuery.datepick.setDefaults({
      changeMonth: true,
      changeYear: true,
      altFormat: "yyyy-mm-dd",
      firstDay: firstDayOfWeek
   });
   jQuery("#eme_localised_scope_filter").datepick({ rangeSelect: true, rangeSeparator: '--', monthsToShow: 2, altField: "#eme_scope_filter" });
});
