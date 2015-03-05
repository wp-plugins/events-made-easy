function updateShowHideCustomReturnPage () {
   if (jQuery('input[name=eme_payment_show_custom_return_page]').attr("checked")) {
         jQuery('tr#eme_payment_succes_format_row').show();
         jQuery('tr#eme_payment_fail_format_row').show();
         jQuery('tr#eme_payment_add_bookingid_to_return_row').show(); 
   } else {
         jQuery('tr#eme_payment_succes_format_row').hide();
         jQuery('tr#eme_payment_fail_format_row').hide();
         jQuery('tr#eme_payment_add_bookingid_to_return_row').hide(); 
   }
}

function updateShowHidePaypalSEncrypt () {
   if (jQuery('input[name=eme_paypal_s_encrypt]').attr("checked")) {
         jQuery('tr#eme_paypal_s_pubcert_row').show(); 
         jQuery('tr#eme_paypal_s_privkey_row').show();
         jQuery('tr#eme_paypal_s_paypalcert_row').show();
         jQuery('tr#eme_paypal_s_certid_row').show();
   } else {
         jQuery('tr#eme_paypal_s_pubcert_row').hide(); 
         jQuery('tr#eme_paypal_s_privkey_row').hide();
         jQuery('tr#eme_paypal_s_paypalcert_row').hide();
         jQuery('tr#eme_paypal_s_certid_row').hide();
   }
}

function updateShowHideRsvpMailNotify () {
   if (jQuery('input[name=eme_rsvp_mail_notify_is_active]').attr("checked")) {
      jQuery("table#rsvp_mail_notify-data").show();
   } else {
      jQuery("table#rsvp_mail_notify-data").hide();
   }
}

function updateShowHideRsvpMailSendMethod () {
   if (jQuery('select[name=eme_rsvp_mail_send_method]').val() == "smtp") {
         jQuery('tr#eme_smtp_host_row').show();
         jQuery('tr#eme_smtp_port_row').show(); 
         jQuery('tr#eme_rsvp_mail_SMTPAuth_row').show();
         jQuery('tr#eme_smtp_username_row').show(); 
         jQuery('tr#eme_smtp_password_row').show(); 
   } else {
         jQuery('tr#eme_smtp_host_row').hide();
         jQuery('tr#eme_smtp_port_row').hide(); 
         jQuery('tr#eme_rsvp_mail_SMTPAuth_row').hide();
         jQuery('tr#eme_smtp_username_row').hide(); 
         jQuery('tr#eme_smtp_password_row').hide();
   }
}

function updateShowHideRsvpMailSMTPAuth () {
   if (jQuery('input[name=eme_rsvp_mail_SMTPAuth]').attr("checked")) {
         jQuery('tr#eme_smtp_username_row').show(); 
         jQuery('tr#eme_smtp_password_row').show(); 
   } else {
         jQuery('tr#eme_smtp_username_row').hide(); 
         jQuery('tr#eme_smtp_password_row').hide();
   }
}

jQuery(document).ready( function() {
   // for the eme-options pages
   updateShowHideCustomReturnPage();
   updateShowHidePaypalSEncrypt();
   updateShowHideRsvpMailNotify ();
   updateShowHideRsvpMailSendMethod ();
   updateShowHideRsvpMailSMTPAuth ();
   jQuery('input[name=eme_payment_show_custom_return_page]').change(updateShowHideCustomReturnPage);
   jQuery('input[name=eme_paypal_s_encrypt]').change(updateShowHidePaypalSEncrypt);
   jQuery('input[name=eme_rsvp_mail_notify_is_active]').change(updateShowHideRsvpMailNotify);
   jQuery('select[name=eme_rsvp_mail_send_method]').change(updateShowHideRsvpMailSendMethod);
   jQuery('input[name=eme_rsvp_mail_SMTPAuth]').change(updateShowHideRsvpMailSMTPAuth);
});

