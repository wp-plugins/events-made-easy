<?php
   function response_check_captcha($post_var,$cleanup=1) {
      if (!isset($_POST[$post_var]) || (md5($_POST[$post_var]) != $_SESSION['captcha'])) {
         return _('You entered an incorrect code. Please fill in the correct code.');
      } else {
         if ($cleanup==1) {
            unset($_SESSION['captcha']);
            unset($_POST[$post_var]);
      }
         return ('');
      }
   }
?>
