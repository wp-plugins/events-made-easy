<?php
   function response_check_captcha($post_var,$session_var="",$cleanup=1) {
      if (empty($session_var))
         $session_var="captcha";
      if (!isset($_POST[$post_var]) || (md5($_POST[$post_var]) != $_SESSION[$session_var])) {
         return _('You entered an incorrect code. Please fill in the correct code.');
      } else {
         if ($cleanup==1) {
            unset($_SESSION[$session_var]);
            unset($_POST[$post_var]);
      }
         return ('');
      }
   }
?>
