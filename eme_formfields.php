<?php
function eme_formfields_page() {      
   global $wpdb;
   
   admin_show_warnings();
   if (!current_user_can( get_option('eme_cap_forms')) && (isset($_GET['action']) || isset($_POST['action']))) {
      $message = __('You have no right to update form fields!','eme');
      eme_formfields_table_layout($message);
   } elseif (isset($_GET['action']) && $_GET['action'] == "editformfield") { 
      // edit formfield  
      $field_id = intval($_GET['field_id']);
      eme_formfields_edit_layout($field_id);
   } else {
      // Insert/Update/Delete Record
      $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME;
      $validation_result = '';
      if (isset($_POST['action']) && $_POST['action'] == "edit" ) {
         // formfield update required
         $formfield = array();
         $field_id = intval($_POST['field_id']);
         $formfield['field_name'] = trim(stripslashes($_POST['field_name']));
         $formfield['field_type'] = intval($_POST['field_type']);
         $formfield['field_info'] = trim(stripslashes($_POST['field_info']));
         $formfield['field_tags'] = trim(stripslashes($_POST['field_tags']));
         $db_formfield=eme_get_formfield_byname($formfield['field_name']);
         if ($db_formfield && $db_formfield['field_id']!=$field_id) {
            $message = __('Error: the field name must be unique.','eme');
            eme_formfields_edit_layout($field_id,$message);
            return;
         } elseif (eme_is_multifield($formfield['field_type']) && empty($formfield['field_info'])) {
            $message = __('Error: the field value can not be empty for this type of field.','eme');
            eme_formfields_edit_layout($field_id,$message);
            return;
         } else {
            $message = __("Successfully edited the field", "eme");
            $validation_result = $wpdb->update( $formfields_table, $formfield, array('field_id' => $field_id) );
         }
      } elseif ( isset($_POST['action']) && $_POST['action'] == "add" ) {
         // Add a new formfield
         $formfield = array();
         $formfield['field_name'] = trim(stripslashes($_POST['field_name']));
         $formfield['field_type'] = intval($_POST['field_type']);
         $formfield['field_info'] = trim(stripslashes($_POST['field_info']));
         $formfield['field_tags'] = trim(stripslashes($_POST['field_tags']));
         if (eme_get_formfield_byname($formfield['field_name'])) {
            $message = __('Error: the field name must be unique.','eme');
            $validation_result = false;
         } elseif (eme_is_multifield($formfield['field_type']) && empty($formfield['field_info'])) {
            $message = __('Error: the field value can not be empty for this type of field.','eme');
            $validation_result = false;
         } else {
            $message = __("Successfully added the field", "eme");
            $validation_result = $wpdb->insert( $formfields_table, $formfield );
         }
      } elseif ( isset($_POST['action']) && $_POST['action'] == "delete" ) {
         // Delete formfield or multiple
         $formfields = $_POST['formfields'];
         if (is_array($formfields)) {
            //Make sure the array is only numbers
            foreach ($formfields as $field_id) {
               if (is_numeric($field_id)) {
                  $fields[] = $field_id;
               }
            }
            //Run the query if we have an array of formfield ids
            if (count($fields > 0)) {
               $validation_result = $wpdb->query( "DELETE FROM $formfields_table WHERE field_id IN (". implode(",", $fields).")" );
               $message = __("Successfully deleted the field(s)", "eme");
            } else {
               $validation_result = false;
               $message = __("Couldn't delete the form fields. Incorrect field IDs supplied. Please try again.","eme");
            }
         }
      }

      if (is_numeric($validation_result) ) {
         eme_formfields_table_layout($message);
      } elseif ( $validation_result === false ) {
         eme_formfields_table_layout($message);
      } else {
         // no action, just a formfield list
         eme_formfields_table_layout();   
      }
   }
} 

function eme_formfields_table_layout($message="") {
   $formfields = eme_get_formfields();
   $fieldtypes = eme_get_fieldtypes();
   $destination = admin_url("admin.php?page=eme-formfields"); 
   $table = "
      <div class='wrap nosubsub'>\n
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h2>".__('Form fields', 'eme')."</h2>\n ";   
         
         if(!empty($message)) {
            $table .= "
            <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
               <p>$message</p>
            </div>";
         }
         
         $table .= "
         <div id='col-container'>
         
            <?-- begin col-right -->
            <div id='col-right'>
             <div class='col-wrap'>
                <form id='bookings-filter' method='post' action='".$destination."'>
                  <input type='hidden' name='action' value='delete'/>";
                  if (count($formfields)>0) {
                     $table .= "<table class='widefat'>
                        <thead>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Name', 'eme')."</th>
                              <th>".__('Type', 'eme')."</th>
                           </tr>
                        </thead>
                        <tfoot>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Name', 'eme')."</th>
                              <th>".__('Type', 'eme')."</th>
                           </tr>
                        </tfoot>
                        <tbody>";
                     foreach ($formfields as $this_formfield) {
                        $table .= "    
                           <tr>
                           <td><input type='checkbox' class ='row-selector' value='".$this_formfield['field_id']."' name='formfields[]'/></td>
                           <td><a href='".admin_url("admin.php?page=eme-formfields&amp;action=editformfield&amp;field_id=".$this_formfield['field_id'])."'>".$this_formfield['field_id']."</a></td>
                           <td><a href='".admin_url("admin.php?page=eme-formfields&amp;action=editformfield&amp;field_id=".$this_formfield['field_id'])."'>".$this_formfield['field_name']."</a></td>
                           <td><a href='".admin_url("admin.php?page=eme-formfields&amp;action=editformfield&amp;field_id=".$this_formfield['field_id'])."'>".eme_get_fieldtype($this_formfield['field_type'])."</a></td>
                           </tr>
                        ";
                     }
                     $table .= "
                        </tbody>
                     </table>
   
                     <div class='tablenav'>
                        <div class='alignleft actions'>
                        <input class='button-primary action' type='submit' name='doaction2' value='Delete'/>
                        <br class='clear'/>
                        </div>
                        <br class='clear'/>
                     </div>";
                  } else {
                        $table .= "<p>".__('No fields defined yet!', 'eme');
                  }
                   $table .= "
                  </form>
               </div>
            </div> 
            <?-- end col-right -->
            
            <?-- begin col-left -->
            <div id='col-left'>
            <div class='col-wrap'>
                  <div class='form-wrap'>
                     <div id='ajax-response'/>
                  <h3>".__('Add field', 'eme')."</h3>
                      <form name='add' id='add' method='post' action='".$destination."' class='add:the-list: validate'>
                        <input type='hidden' name='action' value='add' />
                         <div class='form-field form-required'>
                           <label for='field_name'>".__('Field name', 'eme')."</label>
                           <input name='field_name' id='field_name' type='text' value='' size='40' />
                           <label for='field_type'>".__('Field type', 'eme')."</label>
			". eme_ui_select("","field_type",$fieldtypes)
                            ."
                           <label for='field_info'>".__('Field values', 'eme')."</label>
                           <input name='field_info' id='field_info' type='text' value='' size='40' />
                           <br />".__('Tip: for multivalue field types (like Drop Down), use "||" to seperate the different values (e.g.: a1||a2||a3)','eme')."
                           <label for='field_tags'>".__('Field tags', 'eme')."</label>
                           <input name='field_tags' id='field_tags' type='text' value='' size='40' />
                           <br />".__('For multivalue fields, you can here enter the "visible" tags people will see. If left empty, the field values will be used. Use "||" to seperate the different tags (e.g.: a1||a2||a3)','eme')."
                         </div>
                         <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Add field', 'eme')."' /></p>
                      </form>
                 </div>
                 <p>".__('For more information about form fields, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'eme')."</a></p>
               </div>
            </div>
            <?-- end col-left -->
         </div>
   </div>";
   echo $table;  
}

function eme_formfields_edit_layout($field_id,$message = "") {
   $formfield = eme_get_formfield_byid($field_id);
   $fieldtypes = eme_get_fieldtypes();
   $layout = "
   <div class='wrap'>
      <div id='icon-edit' class='icon32'>
         <br />
      </div>
         
      <h2>".__('Edit field', 'eme')."</h2>";   
      
   if($message != "") {
      $layout .= "
      <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
      </div>";
   }
   $layout .= "
      <div id='warning' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
         <p>".__('Warning: changing the field name might result in some answers not being visible when using the #_BOOKINGS placeholder, since the answers are based on the field name', 'eme')."</p>
      </div>
      <div id='ajax-response'></div>

      <form name='editcat' id='editcat' method='post' action='".admin_url("admin.php?page=eme-formfields")."' class='validate'>
      <input type='hidden' name='action' value='edit' />
      <input type='hidden' name='field_id' value='".$formfield['field_id']."' />
      
      <table class='form-table'>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_name'>".__('Field name', 'eme')."</label></th>
               <td><input name='field_name' id='field-name' type='text' value='".eme_sanitize_html($formfield['field_name'])."' size='40'  /></td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_type'>".__('Field type', 'eme')."</label></th>
               <td>".eme_ui_select($formfield['field_type'],"field_type",$fieldtypes)."</td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='field_info'>".__('Field values', 'eme')."</label></th>
               <td><input name='field_info' id='field-info' type='text' value='".eme_sanitize_html($formfield['field_info'])."' size='40'  />
                  <br />".__('Tip: for multivalue field types (like Drop Down), use "||" to seperate the different values (e.g.: a1||a2||a3)','eme')."
               </td>
            </tr>
            <tr class='form-tags form-required'>
               <th scope='row' valign='top'><label for='field_tags'>".__('Field tags', 'eme')."</label></th>
               <td><input name='field_tags' id='field-tags' type='text' value='".eme_sanitize_html($formfield['field_tags'])."' size='40'  />
                  <br />".__('For multivalue fields, you can here enter the "visible" tags people will see. If left empty, the field values will be used. Use "||" to seperate the different tags (e.g.: a1||a2||a3)','eme')."
               </td>
            </tr>
      </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Update field', 'eme')."' /></p>
      </form>
         
   </div>
   <p>".__('For more information about form fields, see ', 'eme')."<a target='_blank' href='http://www.e-dynamics.be/wordpress/?cat=44'>".__('the documentation', 'eme')."</a></p>
   ";  
   echo $layout;
}

function eme_get_formfields(){
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME; 
   $formfields = array();
   #$orderby = " ORDER BY field_name ASC";
   return $wpdb->get_results("SELECT * FROM $formfields_table", ARRAY_A);
}

function eme_get_fieldtypes(){
   global $wpdb;
   $fieldtypes_table = $wpdb->prefix.FIELDTYPES_TBNAME; 
   $formfields = array();
   return $wpdb->get_results("SELECT * FROM $fieldtypes_table", ARRAY_N);
}

function eme_get_formfield_byid($field_id) { 
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME; 
   $sql = $wpdb->prepare("SELECT * FROM $formfields_table WHERE field_id=%d",$field_id);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_formfield_byname($field_name) { 
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME; 
   $sql = $wpdb->prepare("SELECT * FROM $formfields_table WHERE field_name=%s",$field_name);
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_formfield_id_byname($field_name) { 
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME; 
   $sql = $wpdb->prepare("SELECT field_id  FROM $formfields_table WHERE field_name=%s",$field_name);
   return $wpdb->get_var($sql);
}

function eme_get_fieldtype($type_id){
   global $wpdb;
   $fieldtypes_table = $wpdb->prefix.FIELDTYPES_TBNAME; 
   $formfields = array();
   $sql = "SELECT type_info FROM $fieldtypes_table WHERE type_id ='$type_id'";   
   return $wpdb->get_var($sql);
}

function eme_is_multifield($type_id){
   global $wpdb;
   $fieldtypes_table = $wpdb->prefix.FIELDTYPES_TBNAME; 
   $formfields = array();
   $sql = "SELECT is_multi FROM $fieldtypes_table WHERE type_id ='$type_id'";   
   return $wpdb->get_var($sql);
}

function eme_get_formfield_html($field_id, $entered_val) {
   $formfield = eme_get_formfield_byid($field_id);
   if (!$formfield) return;
   $field_info = $formfield['field_info'];
   $field_tags = $formfield['field_tags'];
   $deprecated = get_option('eme_deprecated');
   $field_name='FIELD'.$field_id;
   switch($formfield['field_type']) {
      case 1:
	      # for text field
         $value=$entered_val;
         if (empty($value))
            $value=eme_translate($field_tags);
         if (empty($value))
            $value=$field_info;
         $value = eme_sanitize_html($value);
         $html = "<input type='text' name='$field_name' value='$value'>";
         break;
      case 2:
         # dropdown
         $values = eme_convert_multi2array($field_info);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_select($entered_val,$field_name,$my_arr);
         break;
      case 3:
         # textarea
         $value=$entered_val;
         if (empty($value))
            $value=eme_translate($field_tags);
         if (empty($value))
            $value=$field_info;
         $value = eme_sanitize_html($value);
         $html = "<textarea name='$field_name'>$value</textarea>";
         break;
      case 4:
         # radiobox
         $values = eme_convert_multi2array($field_info);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_radio($entered_val,$field_name,$my_arr);
         break;
      case 5:
         # radiobox, vertical
         $values = eme_convert_multi2array($field_info);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_radio($entered_val,$field_name,$my_arr,false);
         break;
      case 6:
      	# checkbox
         $values = eme_convert_multi2array($field_info);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_checkbox($entered_val,$field_name,$my_arr);
         break;
      case 7:
      	# checkbox, vertical
         $values = eme_convert_multi2array($field_info);
         $tags = eme_convert_multi2array($field_tags);
         $my_arr = array();
         foreach ($values as $key=>$val) {
            $tag=$tags[$key];
            $my_arr[$val]=eme_translate($tag);
         }
         $html = eme_ui_checkbox($entered_val,$field_name,$my_arr,false);
         break;
   }
   return $html;
}

function eme_replace_cancelformfields_placeholders ($event) {
   global $current_user;
   // not used from the admin backend, but we check to be sure
   if (is_admin()) return;

   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only) {
      $readonly="disabled='disabled'";
   } else {
      $readonly="";
   }

   $format = $event['event_cancel_form_format'];
   if (empty($format)) {
      $format = get_option('eme_cancel_form_format');
   }

   $eme_captcha_for_booking=get_option('eme_captcha_for_booking');

   $required_fields_count = 0;
   // We need at least #_NAME, #_EMAIL and #_SUBMIT
   $required_fields_min = 3;
   // if we require the captcha: add 1
   if ($eme_captcha_for_booking)
      $required_fields_min++;

   $bookerName="";
   $bookerEmail="";
   if (is_user_logged_in()) {
      get_currentuserinfo();
      $bookerName=$current_user->display_name;
      $bookerEmail=$current_user->user_email;
   }
   // check for previously filled in data
   // this in case people entered a wrong captcha
   if (isset($_POST['bookerName'])) $bookerName = eme_sanitize_html(stripslashes_deep($_POST['bookerName']));
   if (isset($_POST['bookerEmail'])) $bookerEmail = eme_sanitize_html(stripslashes_deep($_POST['bookerEmail']));

   // the 2 placeholders that can contain extra text are treated seperately first
   // the question mark is used for non greedy (minimal) matching
   if (preg_match('/#_CAPTCHAHTML\{.+\}/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if ($eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '' ,$format );
   }

   if (preg_match('/#_SUBMIT\{.+\}/', $format)) {
      $format = preg_replace('/#_SUBMIT\{(.+?)\}/', "<input type='submit' value='".eme_trans_sanitize_html('$1')."'/>" ,$format );
      $required_fields_count++;
   }

   $deprecated = get_option('eme_deprecated');
   if ($deprecated && preg_match('/#_CAPTCHAHTML\[.+\]/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if ($eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\[(.+?)\]/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\[(.+?)\]/', '' ,$format );
   }

   if ($deprecated && preg_match('/#_SUBMIT\[.+\]/', $format)) {
      $format = preg_replace('/#_SUBMIT\[(.+?)\]/', "<input type='submit' value='".eme_trans_sanitize_html('$1')."'/>" ,$format );
      $required_fields_count++;
   }

   // now the normal placeholders
   preg_match_all("/#(REQ)?_[A-Z0-9_]+/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'sort_stringlenth');
   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless
   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $html5_wanted=0;
      $replacement = "";
      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
      }

      // also support RESPNAME, RESPEMAIL, ...
      if (strstr($result,'#_RESP')) {
         $result = str_replace("#_RESP","#_",$result);
      }

      if (preg_match('/#_NAME/', $result)) {
         $replacement = "<input type='text' name='bookerName' value='$bookerName' $readonly />";
         $required_fields_count++;
         // #_NAME is always required
         $required=1;
      } elseif (preg_match('/#_HTML5_EMAIL/', $result)) {
         $replacement = "<input type='email' name='bookerEmail' value='$bookerEmail' $readonly />";
         $required_fields_count++;
         // #_EMAIL is always required
         $required=1;
      } elseif (preg_match('/#_EMAIL/', $result)) {
         $replacement = "<input type='text' name='bookerEmail' value='$bookerEmail' $readonly />";
         $required_fields_count++;
         // #_EMAIL is always required
         $required=1;
      } elseif (preg_match('/#_CAPTCHA/', $result) && $eme_captcha_for_booking) {
         $replacement = "<img src='".EME_PLUGIN_URL."captcha.php?sessionvar=eme_del_booking'><br><input type='text' name='captcha_check' />";
         $required_fields_count++;
      } elseif (preg_match('/#_SUBMIT/', $result, $matches)) {
         $replacement = "<input type='submit' value='".eme_trans_sanitize_html(get_option('eme_rsvp_delbooking_submit_string'))."'/>";
         $required_fields_count++;
      } else {
         $found = 0;
      }

      if ($required)
         $replacement .= "<div class='eme-required-field'>&nbsp;".__('(Required field)','eme')."</div>";

      if ($found) {
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   // now any leftover event placeholders
   $format = eme_replace_placeholders($format, $event);

   // now, replace any language tags found in the format itself
   $format = eme_translate($format);

   if ($required_fields_count >= $required_fields_min) {
      return $format;
   } else {
      return __('Not all required fields are present in the cancel form.', 'eme');
   }
}

function eme_replace_formfields_placeholders ($event,$booking="") {
   global $current_user;

   $registration_wp_users_only=$event['registration_wp_users_only'];
   if ($registration_wp_users_only || (is_admin() && $booking)) {
      $readonly="disabled='disabled'";
   } else {
      $readonly="";
   }

   $format = $event['event_registration_form_format'];
   if (empty($format)) {
      $format = get_option('eme_registration_form_format');
   }

   $min_allowed = $event['event_properties']['min_allowed'];
   $max_allowed = $event['event_properties']['max_allowed'];
   if (is_admin() && $booking) {
      // in the admin itf, and editing a booking
      // then the avail seats are the total seats
      if (eme_is_multi($event['event_seats'])) {
         $avail_seats = eme_get_multitotal($event['event_seats']);
      } else {
         $avail_seats = $event['event_seats'];
      }
   } else {
      // the next gives the number of available seats, even for multiprice
      $avail_seats = eme_get_available_seats($event['event_id']);
   }

   $booked_places_options = array();
   if (eme_is_multi($max_allowed)) {
      $multi_max_allowed=eme_convert_multi2array($max_allowed);
      $max_allowed_is_multi=1;
   } else {
      $max_allowed_is_multi=0;
   }
   if (eme_is_multi($min_allowed)) {
      $multi_min_allowed=eme_convert_multi2array($min_allowed);
      $min_allowed_is_multi=1;
   } else {
      $min_allowed_is_multi=0;
   }
   if (eme_is_multi($event['event_seats'])) {
      // in the admin itf, and editing a booking
      // then the avail seats are the total seats
      if (is_admin() && $booking)
         $multi_avail = eme_convert_multi2array($event['event_seats']);
      else
         $multi_avail = eme_get_available_multiseats($event['event_id']);

      foreach ($multi_avail as $key => $avail_seats) {
         $booked_places_options[$key] = array();
         if ($max_allowed_is_multi)
            $real_max_allowed=$multi_max_allowed[$key];
         else
            $real_max_allowed=$max_allowed;
         
         // don't let people choose more seats than available
         if ($real_max_allowed>$avail_seats || $real_max_allowed==0)
            $real_max_allowed=$avail_seats;

         if ($min_allowed_is_multi)
            $real_min_allowed=$multi_min_allowed[$key];
         else
            // it's no use to have a non-multi minimum for multiseats
            $real_min_allowed=0;
         
         for ( $i = $real_min_allowed; $i <= $real_max_allowed; $i++) 
            $booked_places_options[$key][$i]=$i;
      }
   } elseif (eme_is_multi($event['price'])) {
      // we just need to loop through the same amount of seats as there are prices
      foreach (eme_convert_multi2array($event['price']) as $key => $value) {
         $booked_places_options[$key] = array();
         if ($max_allowed_is_multi)
            $real_max_allowed=$multi_max_allowed[$key];
         else
            $real_max_allowed=$max_allowed;

         // don't let people choose more seats than available
         if ($real_max_allowed>$avail_seats || $real_max_allowed==0)
            $real_max_allowed=$avail_seats;

         if ($min_allowed_is_multi)
            $real_min_allowed=$multi_min_allowed[$key];
         else
            // it's no use to have a non-multi minimum for multiseats/multiprice
            $real_min_allowed=0;

         for ( $i = $real_min_allowed; $i <= $real_max_allowed; $i++)
            $booked_places_options[$key][$i]=$i;
      }
   } else {
      if ($max_allowed_is_multi)
         $real_max_allowed=$multi_max_allowed[0];
      else
         $real_max_allowed=$max_allowed;

      // don't let people choose more seats than available
      if ($real_max_allowed > $avail_seats || $real_max_allowed==0)
         $real_max_allowed = $avail_seats;

      if ($min_allowed_is_multi)
         $real_min_allowed=$multi_min_allowed[0];
      else
         $real_min_allowed=$min_allowed;

      for ( $i = $real_min_allowed; $i <= $real_max_allowed; $i++) 
         $booked_places_options[$i]=$i;
   }

   $required_fields_count = 0;
   $eme_captcha_for_booking=get_option('eme_captcha_for_booking');
   # we need 4 required fields: #_NAME, #_EMAIL, #_SEATS and #_SUBMIT
   # for multiprice: 3 + number of possible prices (we add those later on)
   if (eme_is_multi($event['price']))
      $required_fields_min = 3;
   else
      $required_fields_min = 4;
   // if we require the captcha: add 1
   if (!is_admin() && $eme_captcha_for_booking)
      $required_fields_min++;

   $bookerName="";
   $bookerEmail="";
   $bookerComment="";
   $bookerPhone="";
   $bookedSeats=0;

   if (is_user_logged_in()) {
      get_currentuserinfo();
      $bookerName=$current_user->display_name;
      $bookerEmail=$current_user->user_email;
   }

   if (is_admin() && $booking) {
      $person = eme_get_person ($booking['person_id']);
      // when editing a booking
      $bookerName = eme_sanitize_html($person['person_name']);
      $bookerEmail = eme_sanitize_html($person['person_email']);
      $bookerPhone = eme_sanitize_html($person['person_phone']);
      $bookerComment = eme_sanitize_html($booking['booking_comment']);
      $bookedSeats = eme_sanitize_html($booking['booking_seats']);
      if ($booking['booking_seats_mp']) {
         $booking_seats_mp=eme_convert_multi2array($booking['booking_seats_mp']);
         foreach ($booking_seats_mp as $key=>$val) {
            $field_index=$key+1;
            ${"bookedSeats".$field_index}=eme_sanitize_html($val);
         }
      }
   } else {
      // check for previously filled in data
      // this in case people entered a wrong captcha
      if (isset($_POST['bookerName'])) $bookerName = eme_sanitize_html(stripslashes_deep($_POST['bookerName']));
      if (isset($_POST['bookerEmail'])) $bookerEmail = eme_sanitize_html(stripslashes_deep($_POST['bookerEmail']));
      if (isset($_POST['bookerPhone'])) $bookerPhone = eme_sanitize_html(stripslashes_deep($_POST['bookerPhone']));
      if (isset($_POST['bookerComment'])) $bookerComment = eme_sanitize_html(stripslashes_deep($_POST['bookerComment']));
      if (isset($_POST['bookedSeats'])) $bookedSeats = eme_sanitize_html(stripslashes_deep($_POST['bookedSeats']));
   }

   // first we do the custom attributes, since these can contain other placeholders
   preg_match_all("/#(ESC|URL)?_ATT\{.+?\}(\{.+?\})?/", $format, $results);
   foreach($results[0] as $resultKey => $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      //Strip string of placeholder and just leave the reference
      $attRef = substr( substr($result, 0, strpos($result, '}')), 6 );
      if (isset($event['event_attributes'][$attRef])) {
         $replacement = $event['event_attributes'][$attRef];
      }
      if( trim($replacement) == ''
            && isset($results[2][$resultKey])
            && $results[2][$resultKey] != '' ) {
         //Check to see if we have a second set of braces;
         $replacement = substr( $results[2][$resultKey], 1, strlen(trim($results[2][$resultKey]))-2 );
      }

      if ($need_escape)
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      if ($need_urlencode)
         $replacement = rawurlencode($replacement);
      $format = str_replace($orig_result, $replacement ,$format );
   }

   // the 2 placeholders that can contain extra text are treated seperately first
   // the question mark is used for non greedy (minimal) matching
   if (preg_match('/#_CAPTCHAHTML\{.+\}/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if (!is_admin() && $eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\{(.+?)\}/', '' ,$format );
   }

   if (preg_match('/#_SUBMIT\{.+\}/', $format)) {
      if (is_admin() && $booking)
         $format = preg_replace('/#_SUBMIT\{(.+?)\}/', "<input type='submit' value='".__('Update booking','eme')."'/>" ,$format );
      else
         $format = preg_replace('/#_SUBMIT\{(.+?)\}/', "<input type='submit' value='".eme_trans_sanitize_html('$1')."'/>" ,$format );
      $required_fields_count++;
   }

   $deprecated = get_option('eme_deprecated');
   if ($deprecated && preg_match('/#_CAPTCHAHTML\[.+\]/', $format)) {
      // only show the captcha when booking via the frontend, not the admin backend
      if (!is_admin() && $eme_captcha_for_booking)
         $format = preg_replace('/#_CAPTCHAHTML\[(.+?)\]/', '$1' ,$format );
      else
         $format = preg_replace('/#_CAPTCHAHTML\[(.+?)\]/', '' ,$format );
   }

   if ($deprecated && preg_match('/#_SUBMIT\[.+\]/', $format)) {
      if (is_admin() && $booking)
         $format = preg_replace('/#_SUBMIT\[(.+?)\]/', "<input type='submit' value='".__('Update booking','eme')."'/>" ,$format );
      else
         $format = preg_replace('/#_SUBMIT\[(.+?)\]/', "<input type='submit' value='".eme_trans_sanitize_html('$1')."'/>" ,$format );
      $required_fields_count++;
   }

   // now the normal placeholders
   preg_match_all("/#(REQ)?_?[A-Z0-9_]+(\{[A-Z0-9_]+\})?/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'sort_stringlenth');
   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless
   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $html5_wanted=0;
      $replacement = "";
      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
      }

      // also support RESPNAME, RESPEMAIL, ...
      if (strstr($result,'#_RESP')) {
         $result = str_replace("#_RESP","#_",$result);
      }

      if (preg_match('/#_NAME/', $result)) {
         $replacement = "<input type='text' name='bookerName' value='$bookerName' $readonly />";
         $required_fields_count++;
         // #_NAME is always required
         $required=1;
      } elseif (preg_match('/#_HTML5_EMAIL/', $result)) {
         $replacement = "<input type='email' name='bookerEmail' value='$bookerEmail' $readonly />";
         $required_fields_count++;
         // #_EMAIL is always required
         $required=1;
      } elseif (preg_match('/#_EMAIL/', $result)) {
         $replacement = "<input type='text' name='bookerEmail' value='$bookerEmail' $readonly />";
         $required_fields_count++;
         // #_EMAIL is always required
         $required=1;
      } elseif (preg_match('/#_HTML5_PHONE/', $result)) {
         $replacement = "<input type='tel' name='bookerPhone' value='$bookerPhone' />";
      } elseif (preg_match('/#_PHONE/', $result)) {
         $replacement = "<input type='text' name='bookerPhone' value='$bookerPhone' />";
      } elseif (preg_match('/#_SEATS$|#_SPACES$/', $result)) {
         $replacement = eme_ui_select($bookedSeats,"bookedSeats",$booked_places_options);
         $required_fields_count++;
      } elseif (($deprecated && preg_match('/#_(SEATS|SPACES)(\d+)/', $result, $matches)) ||
                 preg_match('/#_(SEATS|SPACES)\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[2]);
         $postfield_name="bookedSeats".$field_id;

         if ($booking && isset(${"bookedSeats".$field_id}))
            $entered_val=${"bookedSeats".$field_id};
         elseif (isset($_POST[$postfield_name]))
            $entered_val = eme_trans_sanitize_html(stripslashes_deep($_POST[$postfield_name]));
         else
            $entered_val=0;

         if (eme_is_multi($event['event_seats']) || eme_is_multi($event['price']))
            $replacement = eme_ui_select($entered_val,$postfield_name,$booked_places_options[$field_id-1]);
         else
            $replacement = eme_ui_select($entered_val,$postfield_name,$booked_places_options);
         $required_fields_count++;
      } elseif (preg_match('/#_COMMENT/', $result)) {
         $replacement = "<textarea name='bookerComment'>$bookerComment</textarea>";
      } elseif (preg_match('/#_CAPTCHA/', $result) && $eme_captcha_for_booking) {
         $replacement = "<img src='".EME_PLUGIN_URL."captcha.php?sessionvar=eme_add_booking'><br><input type='text' name='captcha_check' />";
         $required_fields_count++;
      } elseif (($deprecated && preg_match('/#_FIELDNAME(\d+)/', $result, $matches)) || preg_match('/#_FIELDNAME\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[1]);
         $formfield = eme_get_formfield_byid($field_id);
         $replacement = eme_trans_sanitize_html($formfield['field_name']);
      } elseif (($deprecated && preg_match('/#_FIELD(\d+)/', $result, $matches)) || preg_match('/#_FIELD\{(\d+)\}/', $result, $matches)) {
         $field_id = intval($matches[1]);
         $postfield_name="FIELD".$field_id;
         if ($booking) {
            $answers = eme_get_answers($booking['booking_id']);
            $formfield = eme_get_formfield_byid($field_id);
            foreach ($answers as $answer) {
               if ($answer['field_name'] == $formfield['field_name']) {
                  // the entered value for the function eme_get_formfield_html needs to be an array for multiple values
                  // since we store them with "||", we can use the good old eme_is_multi function and split in an array then
                  $entered_val = $answer['answer'];
                  if (eme_is_multi($entered_val)) {
                     $entered_val = eme_convert_multi2array($entered_val);
                  }
               }
            }
         } elseif (isset($_POST[$postfield_name])) {
            $entered_val = stripslashes_deep($_POST[$postfield_name]);
         } else {
            $entered_val = "";
         }
         $replacement = eme_get_formfield_html($field_id,$entered_val);
      } elseif (preg_match('/#_SUBMIT/', $result, $matches)) {
         if (is_admin() && $booking)
            $replacement = "<input type='submit' value='".__('Update booking','eme')."'/>";
         else
            $replacement = "<input type='submit' value='".eme_trans_sanitize_html(get_option('eme_rsvp_addbooking_submit_string'))."'/>";
         $required_fields_count++;
      } else {
         $found = 0;
      }

      if ($required)
         $replacement .= "<div class='eme-required-field'>&nbsp;".__('(Required field)','eme')."</div>";

      if ($found) {
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   // now any leftover event placeholders
   $format = eme_replace_placeholders($format, $event);

   // now, replace any language tags found in the format itself
   $format = eme_translate($format);

   # we need 4 required fields: #_NAME, #_EMAIL, #_SEATS and #_SUBMIT
   # for multiprice: 3 + number of possible prices
   # if these are not present: we don't replace anything and the form is worthless
   if (eme_is_multi($event['price'])) {
      $matches=preg_split('/\|\|/', $event['price']);
      $count=count($matches);
      // the count can be >3+$count if conditional tags are used to combine a form for single and multiple prices
      if ($required_fields_count >= $required_fields_min+$count) {
         return $format;
      } else {
         $res = __('Not all required fields are present in the booking form.', 'eme');
         $res.= '<br />'.__("Since this is a multiprice event, make sure you changed the setting 'Registration Form Format' for the event to include #_SEATxx placeholders for each price.",'eme');
         $res.= '<br />'.__("See the documentation about multiprice events.",'eme');
         return "<div id='message' class='eme-rsvp-message'>$res</div>";
      }
   } elseif ($required_fields_count >= $required_fields_min) {
      // the count can be > 4 if conditional tags are used to combine a form for single and multiple prices
      return $format;
   } else {
      return __('Not all required fields are present in the booking form.', 'eme');
   }
}

function eme_find_required_formfields ($format) {
   if (empty($format)) {
      $format = get_option('eme_registration_form_format');
   }
   preg_match_all("/#REQ_?[A-Z0-9_]+(\{[A-Z0-9_]+\})?/", $format, $placeholders);
   usort($placeholders[0],'sort_stringlenth');
   return preg_replace("/#REQ_|\{|\}/","",$placeholders[0]);
}

?>
