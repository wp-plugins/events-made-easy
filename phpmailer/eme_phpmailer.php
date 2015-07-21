<?php
// phpmailer support
function eme_send_mail($subject,$body, $receiveremail, $receivername='', $replytoemail='', $replytoname='') {

   // don't send empty mails
   if (empty($body) || empty($subject)) return;

   $eme_rsvp_mail_send_method = get_option('eme_rsvp_mail_send_method');
   if (get_option('eme_mail_sender_address') == "") {
      $fromMail = $replytoemail;
      $fromName = $replytoname;
   } else {
      $fromMail = get_option('eme_mail_sender_address');
      $fromName = get_option('eme_mail_sender_name'); // This is the from name in the email, you can put anything you like here
   }
   $eme_bcc_address= get_option('eme_mail_bcc_address');

   if ($eme_rsvp_mail_send_method == 'wp_mail') {
      // Set the correct mail headers
      $headers[] = "From: $fromName <$fromMail>";
      if ($replytoemail != "")
         $headers[] = "ReplyTo: $replytoname <$replytoemail>";
      if (!empty($eme_bcc_address))
         $headers[] = "Bcc: $eme_bcc_address";

      // set the correct content type
      if (get_option('eme_rsvp_send_html') == '1')
          add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

      // now send it
      wp_mail( $receiveremail, $subject, $body, $headers );  

      // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
      if (get_option('eme_rsvp_send_html') == '1')
         remove_filter('wp_mail_content_type', 'set_html_content_type' );

   } else {
      require_once(ABSPATH . WPINC . "/class-phpmailer.php");
      // there's a bug in class-phpmailer from wordpress, so we need to copy class-smtp.php
      // in this dir for smtp to work
      
      if (class_exists('PHPMailer')) {
         $mail = new PHPMailer();
         $mail->ClearAllRecipients();
         $mail->ClearAddresses();
         $mail->ClearAttachments();
         $mail->CharSet = 'utf-8';
         $mail->SetLanguage('en', dirname(__FILE__).'/');

         $mail->PluginDir = dirname(__FILE__).'/';
         if ($eme_rsvp_mail_send_method == 'qmail')
            $mail->IsQmail();
         else
            $mail->Mailer = $eme_rsvp_mail_send_method;

         if ($eme_rsvp_mail_send_method == 'smtp') {
            if (get_option('eme_smtp_host'))
               $mail->Host = get_option('eme_smtp_host');
            else
               $mail->Host = "localhost";

            if (strstr($mail->Host,'ssl://')) {
               $mail->SMTPSecure="ssl";
               $mail->Host = str_replace("ssl://","",$mail->Host);
            }
            if (strstr($mail->Host,'tls://')) {
               $mail->SMTPSecure="tls";
               $mail->Host = str_replace("tls://","",$mail->Host);
            }

            if (get_option('eme_smtp_port'))
               $mail->port = get_option('eme_smtp_port');
            else
               $mail->port = 25;

            if (get_option('eme_rsvp_mail_SMTPAuth') == '1') {
               $mail->SMTPAuth = true;
               $mail->Username = get_option('eme_smtp_username');
               $mail->Password = get_option('eme_smtp_password');
            }
            if (get_option('eme_smtp_debug'))
               $mail->SMTPDebug = 2;
         }
         $mail->From = $fromMail;
         $mail->FromName = $fromName;
         if (get_option('eme_rsvp_send_html') == '1')
            $mail->MsgHTML($body);
         else
            $mail->Body = $body;
         $mail->Subject = $subject;
         if (!empty($replytoemail))
            $mail->AddReplyTo($replytoemail,$replytoname);
         if (!empty($eme_bcc_address))
            $mail->AddBCC($eme_bcc_address);

         if (!empty($receiveremail)) {
            $mail->AddAddress($receiveremail,$receivername);
            if(!$mail->Send()){
               #echo "<br />Message was not sent<br/ >";
               #echo "Mailer Error: " . $mail->ErrorInfo;
               return false;
            } else {
               return true;
            }
         } else {
            return false;
         }
      }
   }
}
?>
