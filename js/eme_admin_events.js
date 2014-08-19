function updateIntervalDescriptor () { 
   jQuery(".interval-desc").hide();
   var number = "-plural";
   if (jQuery('input#recurrence-interval').val() == 1 || jQuery('input#recurrence-interval').val() == "") {
      number = "-singular";
   }
   var descriptor = "span#interval-"+jQuery("select#recurrence-frequency").val()+number;
   jQuery(descriptor).show();
}
function updateIntervalSelectors () {
   jQuery('p.alternate-selector').hide();
   jQuery('p#'+ jQuery('select#recurrence-frequency').val() + "-selector").show();
   //jQuery('p.recurrence-tip').hide();
   //jQuery('p#'+ jQuery(this).val() + "-tip").show();
}
function updateShowHideRecurrence () {
   if(jQuery('input#event-recurrence').attr("checked")) {
      jQuery("#event_recurrence_pattern").fadeIn();
      jQuery("span#event-date-recursive-explanation").show();
      jQuery("div#div_recurrence_date").show();
      jQuery("p#recurrence-tip").hide();
      jQuery("p#recurrence-tip-2").show();
   } else {
      jQuery("#event_recurrence_pattern").hide();
      jQuery("span#event-date-recursive-explanation").hide();
      jQuery("div#div_recurrence_date").hide();
      jQuery("p#recurrence-tip").show();
      jQuery("p#recurrence-tip-2").hide();
   }
}

function updateShowHideRecurrenceSpecificDays () {
   if (jQuery('select#recurrence-frequency').val() == "specific") {
      jQuery("div#recurrence-intervals").hide();
      jQuery("input#localised-rec-end-date").hide();
      jQuery("span#recurrence-dates-explanation").hide();
      jQuery("span#recurrence-dates-explanation-specificdates").show();
      jQuery("#localised-rec-start-date").datepick('option','multiSelect',999);
   } else {
      jQuery("div#recurrence-intervals").show();
      jQuery("input#localised-rec-end-date").show();
      jQuery("span#recurrence-dates-explanation").show();
      jQuery("span#recurrence-dates-explanation-specificdates").hide();
      jQuery("#localised-rec-start-date").datepick('option','multiSelect',0);
   }
}

function updateShowHideRsvp () {
   if (jQuery('input#rsvp-checkbox').attr("checked")) {
      jQuery("div#rsvp-data").fadeIn();
      jQuery("div#div_event_contactperson_email_body").fadeIn();
      jQuery("div#div_event_registration_recorded_ok_html").fadeIn();
      jQuery("div#div_event_respondent_email_body").fadeIn();
      jQuery("div#div_event_registration_pending_email_body").fadeIn();
      jQuery("div#div_event_registration_updated_email_body").fadeIn();
      jQuery("div#div_event_registration_form_format").fadeIn();
      jQuery("div#div_event_cancel_form_format").fadeIn();
   } else {
      jQuery("div#rsvp-data").fadeOut();
      jQuery("div#div_event_contactperson_email_body").fadeOut();
      jQuery("div#div_event_registration_recorded_ok_html").fadeOut();
      jQuery("div#div_event_respondent_email_body").fadeOut();
      jQuery("div#div_event_registration_pending_email_body").fadeOut();
      jQuery("div#div_event_registration_updated_email_body").fadeOut();
      jQuery("div#div_event_cancel_form_format").fadeOut();
   }
}

function updateShowHideTime () {
   if (jQuery('input#eme_prop_all_day').attr("checked")) {
      jQuery("div#div_event_time").hide();
   } else {
      jQuery("div#div_event_time").show();
   }
}

jQuery(document).ready( function() {
   jQuery("#div_recurrence_date").hide();
   jQuery("#localised-start-date").show();
   jQuery("#localised-end-date").show();
   jQuery("#start-date-to-submit").hide();
   jQuery("#end-date-to-submit").hide(); 
   jQuery("#rec-start-date-to-submit").hide();
   jQuery("#rec-end-date-to-submit").hide(); 

   jQuery.datepick.setDefaults( jQuery.datepick.regional[locale_code] );
   jQuery.datepick.setDefaults({
      changeMonth: true,
      changeYear: true
   });
   jQuery("#localised-start-date").datepick({ altField: "#start-date-to-submit", altFormat: "yyyy-mm-dd" });
   jQuery("#localised-end-date").datepick({ altField: "#end-date-to-submit", altFormat: "yyyy-mm-dd" });
   jQuery("#localised-rec-start-date").datepick({ altField: "#rec-start-date-to-submit", altFormat: "yyyy-mm-dd" });
   jQuery("#localised-rec-end-date").datepick({ altField: "#rec-end-date-to-submit", altFormat: "yyyy-mm-dd" });

   jQuery("#start-time").timeEntry({spinnerImage: '', show24Hours: show24Hours });
   jQuery("#end-time").timeEntry({spinnerImage: '', show24Hours: show24Hours });

   // if any of event_single_event_format,event_page_title_format,event_contactperson_email_body,event_respondent_email_body,event_registration_pending_email_body, event_registration_form_format, event_registration_updated_email_body
   // is empty: display default value on focus, and if the value hasn't changed from the default: empty it on blur

   jQuery('textarea#event_page_title_format').focus(function(){
      var tmp_value=eme_event_page_title_format();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_page_title_format').blur(function(){
      var tmp_value=eme_event_page_title_format();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_single_event_format').focus(function(){
      var tmp_value=eme_single_event_format();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_single_event_format').blur(function(){
      var tmp_value=eme_single_event_format();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_contactperson_email_body').focus(function(){
      var tmp_value=eme_contactperson_email_body();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_contactperson_email_body').blur(function(){
      var tmp_value=eme_contactperson_email_body();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_respondent_email_body').focus(function(){
      var tmp_value=eme_respondent_email_body();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_respondent_email_body').blur(function(){
      var tmp_value=eme_respondent_email_body();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_registration_recorded_ok_html').focus(function(){
      var tmp_value=eme_registration_recorded_ok_html();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_registration_recorded_ok_html').blur(function(){
      var tmp_value=eme_registration_recorded_ok_html();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   });
   jQuery('textarea#event_registration_pending_email_body').focus(function(){
      var tmp_value=eme_registration_pending_email_body();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_registration_pending_email_body').blur(function(){
      var tmp_value=eme_registration_pending_email_body();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   });
   jQuery('textarea#event_registration_updated_email_body').focus(function(){
      var tmp_value=eme_registration_updated_email_body();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   });
   jQuery('textarea#event_registration_updated_email_body').blur(function(){
      var tmp_value=eme_registration_updated_email_body();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   });
   jQuery('textarea#event_registration_form_format').focus(function(){
      var tmp_value=eme_registration_form_format();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_registration_form_format').blur(function(){
      var tmp_value=eme_registration_form_format();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 
   jQuery('textarea#event_cancel_form_format').focus(function(){
      var tmp_value=eme_cancel_form_format();
      if (jQuery(this).val() == '') {
         jQuery(this).val(tmp_value);
      }
   }); 
   jQuery('textarea#event_cancel_form_format').blur(function(){
      var tmp_value=eme_cancel_form_format();
      if (jQuery(this).val() == tmp_value) {
         jQuery(this).val('');
      }
   }); 


   updateIntervalDescriptor(); 
   updateIntervalSelectors();
   updateShowHideRecurrence();
   updateShowHideRsvp();
   updateShowHideRecurrenceSpecificDays();
   updateShowHideTime();
   jQuery('input#event-recurrence').change(updateShowHideRecurrence);
   jQuery('input#rsvp-checkbox').change(updateShowHideRsvp);
   jQuery('input#eme_prop_all_day').change(updateShowHideTime);
   // recurrency elements
   jQuery('input#recurrence-interval').keyup(updateIntervalDescriptor);
   jQuery('select#recurrence-frequency').change(updateIntervalDescriptor);
   jQuery('select#recurrence-frequency').change(updateIntervalSelectors);
   jQuery('select#recurrence-frequency').change(updateShowHideRecurrenceSpecificDays);

   // users cannot submit the event form unless some fields are filled
   function validateEventForm() {
      var errors = "";
      var recurring = jQuery("input[name=repeated_event]:checked").val();
      //requiredFields= new Array('event_name', 'localised_event_start_date', 'location_name','location_address','location_town');
      var requiredFields = ['event_name', 'localised_event_start_date'];
      var localisedRequiredFields = {'event_name':"<?php _e ( 'Name', 'eme' )?>",
                      'localised_event_start_date':"<?php _e ( 'Date', 'eme' )?>"
                     };
      
      var missingFields = [];
      var i;
      for (i in requiredFields) {
         if (jQuery("input[name=" + requiredFields[i]+ "]").val() == 0) {
            missingFields.push(localisedRequiredFields[requiredFields[i]]);
            jQuery("input[name=" + requiredFields[i]+ "]").css('border','2px solid red');
         } else {
            jQuery("input[name=" + requiredFields[i]+ "]").css('border','1px solid #DFDFDF');
         }
      }
   
      if (missingFields.length > 0) {
         errors = "<?php echo _e ( 'Some required fields are missing:', 'eme' )?> " + missingFields.join(", ") + ".\n";
      }
      if (recurring && jQuery("input#localised-rec-end-date").val() == "" && jQuery("select#recurrence-frequency").val() != "specific") {
         errors = errors +  "<?php _e ( 'Since the event is repeated, you must specify an end date', 'eme' )?>."; 
         jQuery("input#localised-rec-end-date").css('border','2px solid red');
      } else {
         jQuery("input#localised-rec-end-date").css('border','1px solid #DFDFDF');
      }
      if (errors != "") {
         alert(errors);
         return false;
      }
      return true;
   }

   jQuery('#eventForm').bind("submit", validateEventForm);
});
