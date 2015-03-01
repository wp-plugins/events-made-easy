<?php

function eme_payment_form($event,$payment_id,$form_result_message="") {

   $ret_string = "<div id='eme-rsvp-message'>";
   if(!empty($form_result_message))
      $ret_string .= "<div class='eme-rsvp-message'>$form_result_message</div>";
   $ret_string .= "</div>";

   $booking_ids = eme_get_payment_booking_ids($payment_id);
   $booking_id = $booking_ids[0];
   $booking = eme_get_booking($booking_id);
   if (!is_array($booking))
      return $ret_string;
   if ($booking['booking_payed'])
      return $ret_string."<div class='eme-already-payed'>".__('This booking has already been payed for','eme')."</div>";

   if (empty($event))
      $event = eme_get_event($booking['event_id']);
   $cur = $event['currency'];
   if (eme_event_can_pay_online($event)) {
      $eme_payment_form_header_format=get_option('eme_payment_form_header_format');
      $total_price = eme_get_total_booking_price($event,$booking);
      if (!empty($eme_payment_form_header_format)) {
            $result = eme_replace_placeholders($eme_payment_form_header_format, $event,"html",0);
            $result = eme_replace_booking_placeholders($result, $event, $booking);
            $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
            $ret_string .= $result;
            $ret_string .= "</div>";
      } else {
         $ret_string .= "<div id='eme-payment-handling' class='eme-payment-handling'>".__('Payment handling','eme')."</div>";
         $ret_string .= "<div id='eme-payment-price-info' class='eme-payment-price-info'>".sprintf(__("The booking price in %s is: %01.2f",'eme'),$cur,$total_price)."</div>";
      }
      $ret_string .= "<div id='eme-payment-form' class='eme-payment-form'>";
      if ($event['use_paypal'])
         $ret_string .= eme_paypal_form($event,$payment_id, $total_price,$booking['lang']);
      if ($event['use_2co'])
         $ret_string .= eme_2co_form($event,$payment_id, $total_price,$booking['lang']);
      if ($event['use_webmoney'])
         $ret_string .= eme_webmoney_form($event,$payment_id, $total_price,$booking['lang']);
      if ($event['use_fdgg'])
         $ret_string .= eme_fdgg_form($event,$payment_id, $total_price,$booking['lang']);
      if ($event['use_mollie'])
         $ret_string .= eme_mollie_form($event,$payment_id, $total_price,$booking['lang']);
      $ret_string .= "</div>";

      $eme_payment_form_footer_format=get_option('eme_payment_form_footer_format');
      if (!empty($eme_payment_form_footer_format)) {
            $result = eme_replace_placeholders($eme_payment_form_footer_format, $event,"html",0);
            $result = eme_replace_booking_placeholders($result, $event, $booking);
            $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
            $ret_string .= $result;
            $ret_string .= "</div>";
      }
   }

   return $ret_string;
}

function eme_multipayment_form($payment_id,$form_result_message="") {

   $ret_string = "<div id='eme-rsvp-message'>";
   if(!empty($form_result_message))
      $ret_string .= "<div class='eme-rsvp-message'>$form_result_message</div>";
   $ret_string .= "</div>";

   $booking_ids = eme_get_payment_booking_ids($payment_id);
   if (!$booking_ids)
      return $ret_string;

   $bookings = eme_get_bookings($booking_ids);
   $total_price=eme_bookings_total_booking_price($bookings);

   // we take the currency of the first event in the series
   $event=eme_get_event_by_booking_id($booking_ids[0]);
   $booking=eme_get_booking($booking_ids[0]);
   $cur = $event['currency'];

   $eme_multipayment_form_header_format=get_option('eme_multipayment_form_header_format');
   if (!empty($eme_multipayment_form_header_format)) {
      $result = eme_replace_placeholders($eme_multipayment_form_header_format, $event,"html",0);
      $result = eme_replace_booking_placeholders($result, $event, $booking);
      $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   } else {
      $ret_string .= "<div id='eme-payment-handling' class='eme-payment-handling'>".__('Payment handling','eme')."</div>";
      $ret_string .= "<div id='eme-payment-price-info' class='eme-payment-price-info'>".sprintf(__("The booking price in %s is: %01.2f",'eme'),$cur,$total_price)."</div>";
   }
   $ret_string .= "<div id='eme-payment-form' class='eme-payment-form'>";
   if ($event['use_paypal'])
      $ret_string .= eme_paypal_form($event,$payment_id, $total_price,$booking['lang'],1);
   if ($event['use_2co'])
      $ret_string .= eme_2co_form($event,$payment_id, $total_price,$booking['lang'],1);
   if ($event['use_webmoney'])
      $ret_string .= eme_webmoney_form($event,$payment_id, $total_price,$booking['lang'],1);
   if ($event['use_fdgg'])
      $ret_string .= eme_fdgg_form($event,$payment_id, $total_price,$booking['lang'],1);
   if ($event['use_mollie'])
      $ret_string .= eme_mollie_form($event,$payment_id, $total_price,$booking['lang'],1);
   $ret_string .= "</div>";

   $eme_multipayment_form_footer_format=get_option('eme_multipayment_form_footer_format');
   if (!empty($eme_multipayment_form_footer_format)) {
      $result = eme_replace_placeholders($eme_multipayment_form_footer_format, $event,"html",0);
      $result = eme_replace_booking_placeholders($result, $event, $booking);
      $ret_string .= "<div id='eme-payment-formtext' class='eme-payment-formtext'>";
      $ret_string .= $result;
      $ret_string .= "</div>";
   }
   return $ret_string;
}

function eme_payment_provider_extra_charge($price,$provider) {
   $extra=get_option('eme_'.$provider.'_cost');
   $result=0;
   if ($extra) {
	   if (strstr($extra,"%")) {
		   $extra=str_replace("%","",$extra);
		   $result += sprintf("%01.2f",$price*$extra/100);
	   } else {
		   $result += sprintf("%01.2f",$extra);
	   }
   }
   $extra=get_option('eme_'.$provider.'_cost2');
   if ($extra) {
	   if (strstr($extra,"%")) {
		   $extra=str_replace("%","",$extra);
		   $result += sprintf("%01.2f",$price*$extra/100);
	   } else {
		   $result += sprintf("%01.2f",$extra);
	   }
   }
   return $result;
}

function eme_webmoney_form($event,$payment_id,$price,$lang,$multi_booking=0) {
   global $post;
   $charge=eme_payment_provider_extra_charge($price,'webmoney');
   $price+=$charge;
   $events_page_link = eme_get_events_page(true, false);
   if ($multi_booking) {
      $success_link = get_permalink($post->ID);
      $fail_link = $success_link;
      $name = __("Multiple booking request","eme");
   } else {
      $success_link = eme_payment_return_url($event,$payment_id,1);
      $fail_link = eme_payment_return_url($event,$payment_id,2);
      $name = eme_sanitize_html(sprintf(__("Booking for '%s'","eme"),$event['event_name']));
   }

   $button_above = eme_replace_payment_provider_placeholders(get_option('eme_webmoney_button_above'),$charge,$event['currency'],$lang);
   $button_label = eme_replace_payment_provider_placeholders(get_option('eme_webmoney_button_label'),$charge,$event['currency'],$lang);
   $button_below = eme_replace_payment_provider_placeholders(get_option('eme_webmoney_button_below'),$charge,$event['currency'],$lang);
   $button_img_url = get_option('eme_webmoney_button_img_url');

   require_once('payment_gateways/webmoney/webmoney.inc.php');
   $wm_request = new WM_Request();
   $wm_request->payment_amount =$price;
   $wm_request->payment_desc = $name;
   $wm_request->payment_no = $payment_id;
   $wm_request->payee_purse = get_option('eme_webmoney_purse');
   $wm_request->success_method = WM_POST;
   $result_link = add_query_arg(array('eme_eventAction'=>'webmoney'),$events_page_link);

   $wm_request->result_url = $result_link;
   $wm_request->success_url = $success_link;
   $wm_request->fail_url = $fail_link;
   if (get_option('eme_webmoney_demo')) {
      $wm_request->sim_mode = WM_ALL_SUCCESS;
   }
   $wm_request->btn_label = $button_label;
   if (!empty($button_img_url))
      $wm_request->btn_img_url = $button_img_url;

   $form_html = $button_above;
   $form_html .= $wm_request->SetForm(false);
   $form_html .= $button_below;
   return $form_html;
}

function eme_2co_form($event,$payment_id,$price,$lang,$multi_booking=0) {
   global $post;
   $charge=eme_payment_provider_extra_charge($price,'2co');
   $price+=$charge;
   $events_page_link = eme_get_events_page(true, false);
   if ($multi_booking) {
      $success_link = get_permalink($post->ID);
      $fail_link = $success_link;
      $name = __("Multiple booking request","eme");
   } else {
      $success_link = eme_payment_return_url($event,$payment_id,1);
      $fail_link = eme_payment_return_url($event,$payment_id,2);
      $name = eme_sanitize_html(sprintf(__("Booking for '%s'","eme"),$event['event_name']));
   }
   $business=get_option('eme_2co_business');
   $url=CO_URL;
   $quantity=1;
   $cur=$event['currency'];

   $button_above = eme_replace_payment_provider_placeholders(get_option('eme_2co_button_above'),$charge,$event['currency'],$lang);
   $button_label = eme_replace_payment_provider_placeholders(get_option('eme_2co_button_label'),$charge,$event['currency'],$lang);
   $button_below = eme_replace_payment_provider_placeholders(get_option('eme_2co_button_below'),$charge,$event['currency'],$lang);
   $button_img_url = get_option('eme_2co_button_img_url');
   $form_html = $button_above;
   $form_html .= $wm_request->SetForm(false);
   $form_html.="<form action='$url' method='post'>";
   $form_html.="<input type='hidden' name='sid' value='$business' />";
   $form_html.="<input type='hidden' name='mode' value='2CO' />";
   $form_html.="<input type='hidden' name='return_url' value='$success_link' />";
   $form_html.="<input type='hidden' name='li_0_type' value='product' />";
   $form_html.="<input type='hidden' name='li_0_product_id' value='$payment_id' />";
   $form_html.="<input type='hidden' name='li_0_name' value='$name' />";
   $form_html.="<input type='hidden' name='li_0_price' value='$price' />";
   $form_html.="<input type='hidden' name='li_0_quantity' value='$quantity' />";
   $form_html.="<input type='hidden' name='currency_code' value='$cur' />";
   $button_label=htmlentities($button_label);
   if (!empty($button_img_url))
      $form_html.="<input type='image' alt='$button_label' title='$button_label' src='$button_img_url' />";
   else
      $form_html.="<input type='submit' value='$button_label' />";
   if (get_option('eme_2co_demo')) {
      $form_html.="<input type='hidden' name='demo' value='Y' />";
   }
   $form_html.="</form>";
   $form_html.= $button_below;
   return $form_html;
}

function eme_fdgg_form($event,$payment_id,$price,$lang,$multi_booking=0) {
   global $post;
   $charge=eme_payment_provider_extra_charge($price,'fdgg');
   $price+=$charge;
   $events_page_link = eme_get_events_page(true, false);
   if ($multi_booking) {
      $success_link = get_permalink($post->ID);
      $fail_link = $success_link;
      $name = __("Multiple booking request","eme");
   } else {
      $success_link = eme_payment_return_url($event,$payment_id,1);
      $fail_link = eme_payment_return_url($event,$payment_id,2);
      $name = eme_sanitize_html(sprintf(__("Booking for '%s'","eme"),$event['event_name']));
   }
   $store_name = get_option('eme_fdgg_store_name');
   $shared_secret = get_option('eme_fdgg_shared_secret');
   // the live or sandbox url
   $url = get_option('eme_fdgg_url');
   $quantity=1;
   //$cur=$event['currency'];
   // First Data only allows USD
   $cur="USD";
   $payment=eme_get_payment($payment_id);
   $datetime=date("Y:m:d-H:i:s",strtotime($payment['creation_date_gmt']));
   $timezone_short="GMT";

   $button_above = eme_replace_payment_provider_placeholders(get_option('eme_fdgg_button_above'),$charge,$event['currency'],$lang);
   $button_label = eme_replace_payment_provider_placeholders(get_option('eme_fdgg_button_label'),$charge,$event['currency'],$lang);
   $button_below = eme_replace_payment_provider_placeholders(get_option('eme_fdgg_button_below'),$charge,$event['currency'],$lang);
   $button_img_url = get_option('eme_fdgg_button_img_url');

   require_once('payment_gateways/fdgg/fdgg-util_sha2.php');
   $form_html = $button_above;
   $form_html.="<form action='$url' method='post'>";
   $form_html.="<input type='hidden' name='timezone' value='$timezone_short' />";
   $form_html.="<input type='hidden' name='authenticateTransaction' value='false' />";
   $form_html.="<input type='hidden' name='txntype' value='sale' />";
   $form_html.="<input type='hidden' name='mode' value='payonly' />";
   $form_html.="<input type='hidden' name='trxOrigin' value='ECI' />";
   $form_html.="<input type='hidden' name='txndatetime' value='$datetime' />";
   $form_html.="<input type='hidden' name='hash' value='".fdgg_createHash($store_name . $datetime . $price . $shared_secret)."' />";
   $form_html.="<input type='hidden' name='storename' value='$store_name' />";
   $form_html.="<input type='hidden' name='chargetotal' value='$price' />";
   $form_html.="<input type='hidden' name='subtotal' value='$price' />";
   $form_html.="<input type='hidden' name='invoicenumber' value='$payment_id' />";
   $form_html.="<input type='hidden' name='oid' value='$payment_id' />";
   $form_html.="<input type='hidden' name='responseSuccessURL' value='$success_link' />";
   $form_html.="<input type='hidden' name='responseFailURL' value='$fail_link' />";
   $form_html.="<input type='hidden' name='eme_eventAction' value='fdgg_notification' />";
   $button_label=htmlentities($button_label);
   if (!empty($button_img_url))
      $form_html.="<input type='image' src='$button_img_url' alt='$button_label' title='$button_label' />";
   else
      $form_html.="<input type='submit' value='$button_label' />";
   $form_html.="</form>";
   $form_html.= $button_below;
   return $form_html;
}

function eme_mollie_form($event,$payment_id,$price,$lang,$multi_booking=0) {
   global $post;
   $charge=eme_payment_provider_extra_charge($price,'mollie');
   $price+=$charge;
   $events_page_link = eme_get_events_page(true, false);
   if ($multi_booking) {
      $success_link = get_permalink($post->ID);
      $fail_link = $success_link;
      $name = __("Multiple booking request","eme");
   } else {
      $success_link = eme_payment_return_url($event,$payment_id,1);
      $fail_link = eme_payment_return_url($event,$payment_id,2);
      $name = sprintf(__("Booking for '%s'","eme"),$event['event_name']);
   }
   $notification_link = add_query_arg(array('eme_eventAction'=>'mollie_notification'),$events_page_link);
   $mollie_api_key = get_option('eme_mollie_api_key');

   $button_above = eme_replace_payment_provider_placeholders(get_option('eme_mollie_button_above'),$charge,$event['currency'],$lang);
   $button_label = eme_replace_payment_provider_placeholders(get_option('eme_mollie_button_label'),$charge,$event['currency'],$lang);
   $button_img_url = get_option('eme_mollie_button_img_url');
   $button_below = eme_replace_payment_provider_placeholders(get_option('eme_mollie_button_below'),$charge,$event['currency'],$lang);

   require_once 'payment_gateways/Mollie/API/Autoloader.php';
   $mollie = new Mollie_API_Client;
   $mollie->setApiKey($mollie_api_key);

   try {
      $payment = $mollie->payments->create(
            array(
               'amount'      => $price,
               'description' => $name,
               'redirectUrl' => $success_link,
               'webhookUrl'  => $notification_link,
               'metadata'    => array(
                  'payment_id' => $payment_id
                  )
               )
            );
      $url = $payment->getPaymentUrl();
   }
   catch (Mollie_API_Exception $e) {
      $url="";
      $form_html = "Mollie API call failed: " . htmlspecialchars($e->getMessage()) . " on field " . htmlspecialchars($e->getField());
   }

   $button_label=htmlentities($button_label);
   if (!empty($url)) {
      $form_html = $button_above;
      $form_html.="<form action='$url' method='post'>";
      if (!empty($button_img_url))
         $form_html.="<input type='image' src='$button_img_url' alt='$button_label' title='$button_label' />";
      else
         $form_html.="<input type='submit' value='$button_label' /><br />";
      $form_html.= $button_below;
      $methods = $mollie->methods->all();
      foreach ($methods as $method) {
         $form_html.= '<img src="' . htmlspecialchars($method->image->normal) . '" alt="'.htmlspecialchars($method->description).'" title="'.htmlspecialchars($method->description).'"> ';
      }
      $form_html.="</form>";
   }
   return $form_html;
}

function eme_paypal_form($event,$payment_id,$price,$lang,$multi_booking=0) {
   global $post;
   $quantity=1;
   $charge=eme_payment_provider_extra_charge($price,'paypal');
   $price+=$charge;
   $events_page_link = eme_get_events_page(true, false);
   if ($multi_booking) {
      $success_link = get_permalink($post->ID);
      $fail_link = $success_link;
      $name = __("Multiple booking request","eme");
   } else {
      $success_link = eme_payment_return_url($event,$payment_id,1);
      $fail_link = eme_payment_return_url($event,$payment_id,2);
      $name = eme_sanitize_html(sprintf(__("Booking for '%s'","eme"),$event['event_name']));
   }
   $notification_link = add_query_arg(array('eme_eventAction'=>'paypal_notification'),$events_page_link);

   $button_above = eme_replace_payment_provider_placeholders(get_option('eme_paypal_button_above'),$charge,$event['currency'],$lang);
   $button_label = eme_replace_payment_provider_placeholders(get_option('eme_paypal_button_label'),$charge,$event['currency'],$lang);
   $button_below = eme_replace_payment_provider_placeholders(get_option('eme_paypal_button_below'),$charge,$event['currency'],$lang);
   $button_img_url = get_option('eme_paypal_button_img_url');

   require_once "payment_gateways/paypal/Paypal.php";
   $p = new Paypal;

   // the paypal or paypal sandbox url
   $p->paypal_url = get_option('eme_paypal_url');

   // the timeout in seconds before the button form is submitted to paypal
   // this needs the included addevent javascript function
   // 0 = no delay
   // false = disable auto submission
   $p->timeout = false;

   // the button label
   // false to disable button (if you want to rely only on the javascript auto-submission) not recommended
   $button_label=htmlentities($button_label);
   $p->button = $button_label;
   if (!empty($button_img_url))
      $p->button_img_url = $button_img_url;

   if (get_option('eme_paypal_s_encrypt')) {
      // use encryption (strongly recommended!)
      $p->encrypt = true;
      $p->private_key = get_option('eme_paypal_s_privkey');
      $p->public_cert = get_option('eme_paypal_s_pubcert');
      $p->paypal_cert = get_option('eme_paypal_s_paypalcert');
      $p->cert_id = get_option('eme_paypal_s_certid');
   } else {
      $p->encrypt = false;
   }

   // the actual button parameters
   // https://www.paypal.com/IntegrationCenter/ic_std-variable-reference.html
   $p->add_field('charset','utf-8');
   $p->add_field('business', get_option('eme_paypal_business'));
   $p->add_field('return', $success_link);
   $p->add_field('cancel_return', $fail_link);
   $p->add_field('notify_url', $notification_link);
   $p->add_field('item_name', $name);
   $p->add_field('item_number', $payment_id);
   $p->add_field('currency_code',$event['currency']);
   $p->add_field('amount', $price);
   $p->add_field('quantity', $quantity);

   $form_html = $button_above;
   $form_html .= $p->get_button();
   $form_html .= $button_below;
   return $form_html;
}

function eme_paypal_notification() {
   require_once 'payment_gateways/paypal/IPN.php';
   $ipn = new IPN;

   // the paypal url, or the sandbox url, or the ipn test url
   //$ipn->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
   $ipn->paypal_url = get_option('eme_paypal_url');

   // your paypal email (the one that receives the payments)
   $ipn->paypal_email = get_option('eme_paypal_business');

   // log to file options
   $ipn->log_to_file = false;					// write logs to file
   $ipn->log_filename = '/path/to/ipn.log';  	// the log filename (should NOT be web accessible and should be writable)

   // log to e-mail options
   $ipn->log_to_email = false;					// send logs by e-mail
   $ipn->log_email = '';		// where you want to receive the logs
   $ipn->log_subject = 'IPN Log: ';			// prefix for the e-mail subject

   // database information
   $ipn->log_to_db = false;						// false not recommended
   $ipn->db_host = 'localhost';				// database host
   $ipn->db_user = '';				// database user
   $ipn->db_pass = '';			// database password
   $ipn->db_name = '';						// database name

   // array of currencies accepted or false to disable
   //$ipn->currencies = array('USD','EUR');
   $ipn->currencies = false;

   // date format on log headers (default: dd/mm/YYYY HH:mm:ss)
   // see http://php.net/date
   $ipn->date_format = 'd/m/Y H:i:s';

   // Prefix for file and mail logs
   $ipn->pretty_ipn = "IPN Values received:\n\n";

   // configuration ended, do the actual check

   if($ipn->ipn_is_valid()) {
      /*
         A valid ipn was received and passed preliminary validations
         You can now do any custom validations you wish to ensure the payment was correct
         You can access the IPN data with $ipn->ipn['value']
         The complete() method below logs the valid IPN to the places you choose
       */
      $payment_id=intval($ipn->ipn['item_number']);
      eme_update_payment_payed($payment_id);
      $ipn->complete();
   }
}

function eme_2co_notification() {
   $business=get_option('eme_2co_business');
   $secret=get_option('eme_2co_secret');

   if ($_POST['message_type'] == 'ORDER_CREATED'
       || $_POST['message_type'] == 'INVOICE_STATUS_CHANGED') {
      $insMessage = array();
      foreach ($_POST as $k => $v) {
         $insMessage[$k] = $v;
      }
 
      $hashSid = $insMessage['vendor_id'];
      if ($hashSid != $business) {
         die ('Not the 2Checkout Account number it should be ...');
      }
      $hashOrder = $insMessage['sale_id'];
      $hashInvoice = $insMessage['invoice_id'];
      $StringToHash = strtoupper(md5($hashOrder . $hashSid . $hashInvoice . $secret));
 
      if ($StringToHash != $insMessage['md5_hash']) {
         die('Hash Incorrect');
      }

      if ($insMessage['invoice_status'] == 'approved' || $insMessage['invoice_status'] == 'deposited') {
         $payment_id=intval($insMessage['item_id_1']);
         eme_update_payment_payed($payment_id);
      }
   }
}

function eme_webmoney_notification() {
   $webmoney_purse = get_option('eme_webmoney_purse');
   $webmoney_secret = get_option('eme_webmoney_secret');

   require_once('payment_gateways/webmoney/webmoney.inc.php');
   $wm_notif = new WM_Notification(); 
   if ($wm_notif->GetForm() != WM_RES_NOPARAM) {
      $amount=$wm_notif->payment_amount;
      if ($webmoney_purse != $wm_notif->payee_purse) {
         die ('Not the webmoney purse it should be ...');
      }
      #if ($price != $amount) {
      #   die ('Not the webmoney amount I expected ...');
      #}
      $payment_id=intval($wm_notif->payment_no);
      if ($wm_notif->CheckMD5($webmoney_purse, $amount, $payment_id, $webmoney_secret) == WM_RES_OK) {
         eme_update_payment_payed($payment_id);
      }
   }
}

function eme_fdgg_notification() {
   $store_name = get_option('eme_fdgg_store_name');
   $shared_secret = get_option('eme_fdgg_shared_secret');
   require_once('payment_gateways/fdgg/fdgg-util_sha2.php');

   $payment_id      = intval($_POST['invoicenumber']);
   $charge_total    = $_POST['charge_total'];
   $approval_code   = $_POST['approval_code'];
   $response_hash   = $_POST['response_hash'];
   $response_status = $_POST['status'];

   //$cur=$event['currency'];
   // First Data only allows USD
   $cur="USD";
   $payment=eme_get_payment($payment_id);
   $datetime=date("Y:m:d-H:i:s",strtotime($payment['creation_date_gmt']));
   $timezone_short="GMT";
   $calc_hash=fdgg_createHash($shared_secret.$approval_code.$charge_total.$cur.$datetime.$store_name);

   if ($response_hash != $calc_hash) {
      die('Hash Incorrect');
   }

   // TODO: do some extra checks, like the price payed and such
   #$price=eme_get_total_booking_price($event,$booking);

   if (strtolower($response_status) == 'approved') {
      eme_update_payment_payed($payment_id);
   }
}

function eme_mollie_notification() {
   $api_key = get_option('eme_mollie_api_key');
   require_once 'payment_gateways/Mollie/API/Autoloader.php';

   $mollie = new Mollie_API_Client;
   $mollie->setApiKey($api_key);
   $payment = $mollie->payments->get($_POST["id"]);
   $payment_id = $payment->metadata->payment_id;
   if ($payment->isPaid()) {
      eme_update_payment_payed($payment_id);
   }
}

function eme_event_can_pay_online ($event) {
   if ($event['use_paypal'] || $event['use_2co'] || $event['use_webmoney'] || $event['use_fdgg'] || $event['use_mollie'])
      return 1;
   else
      return 0;
}

function eme_create_payment($booking_ids) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;

   // some safety
   if (!$booking_ids)
      return false;

   $payment_id = false;
   $payment=array();
   $payment['booking_ids']=$booking_ids;
   $payment['creation_date_gmt']=current_time('mysql', true);
   if ($wpdb->insert($payments_table,$payment)) {
      $payment_id = $wpdb->insert_id;
      $booking_ids_arr=explode(",",$booking_ids);
      foreach ($booking_ids_arr as $booking_id) {
         $where = array();
         $fields = array();
         $where['booking_id'] = $booking_id;
         $fields['transfer_nbr_be97'] = eme_transfer_nbr_be97($payment_id);
         $wpdb->update($bookings_table, $fields, $where);
      }
   }
   #return $payment_id;
}

function eme_get_payment($payment_id) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $payments_table WHERE id=%d",$payment_id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_payment_booking_ids($payment_id) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT booking_ids FROM $payments_table WHERE id=%d",$payment_id);
   $booking_ids=$wpdb->get_var($sql);
   return explode(",",$booking_ids);
}

function eme_get_booking_payment_id($booking_id) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT id FROM $payments_table WHERE FIND_IN_SET(%d,booking_ids) ORDER BY id DESC LIMIT 1",$booking_id);
   return $wpdb->get_var($sql);
}

function eme_delete_payment_booking_id($booking_id) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $sql = $wpdb->prepare("DELETE FROM $payments_table WHERE FIND_IN_SET(%d,booking_ids)",$booking_id);
   return $wpdb->get_var($sql);
}

function eme_get_bookings_payment_id($booking_ids) {
   global $wpdb;
   $payments_table = $wpdb->prefix.PAYMENTS_TBNAME;
   $sql = $wpdb->prepare("SELECT id FROM $payments_table WHERE booking_ids = %s",$booking_ids);
   return $wpdb->get_var($sql);
}

function eme_update_payment_payed($payment_id) {
   $booking_ids=eme_get_payment_booking_ids($payment_id);
   foreach ($booking_ids as $booking_id) {
       $booking=eme_get_booking($booking_id);
       $event = eme_get_event_by_booking_id($booking_id);
       if ($event['event_properties']['auto_approve'] == 1 && $booking['booking_approved']==0)
           eme_update_booking_payed($booking_id,1,1);
       else
           eme_update_booking_payed($booking_id,1,0);
       if (has_action('eme_ipn_action')) do_action('eme_ipn_action',$booking);
   }
}

function eme_replace_payment_provider_placeholders($format, $charge, $currency, $lang) {
   preg_match_all("/#_?[A-Za-z0-9_]+/", $format, $placeholders);

   usort($placeholders[0],'sort_stringlenth');
   foreach($placeholders[0] as $result) {
      $replacement='';
      $found = 1;
      $orig_result = $result;
      if (preg_match('/#_EXTRACHARGE$/', $result)) {
         $replacement = $charge;
      } elseif (preg_match('/#_CURRENCY$/', $result)) {
         $replacement = $currency;
      } else {
         $found = 0;
      }
      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );
   }

   // now, replace any language tags found in the format itself
   $format = eme_translate($format,$lang);

   return do_shortcode($format);
}
?>
