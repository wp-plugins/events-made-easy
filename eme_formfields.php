<?php
function eme_formfields_page() {      
   global $wpdb;
   
   admin_show_warnings();
   if (!current_user_can( get_option('eme_cap_forms')) && (isset($_GET['action']) || isset($_POST['action']))) {
      $message = __('You have no right to update form fields!','eme');
      eme_formfields_table_layout($message);
   } elseif (isset($_GET['action']) && $_GET['action'] == "editformfield") { 
      // edit formfield  
      eme_formfields_edit_layout();
   } else {
      // Insert/Update/Delete Record
      $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME;
      $validation_result = '';
      if (isset($_POST['action']) && $_POST['action'] == "edit" ) {
         // formfield update required  
         $formfield = array();
         $formfield['field_name'] = trim(stripslashes($_POST['field_name']));
         $formfield['field_type'] = intval($_POST['field_type']);
         $formfield['field_info'] = trim(stripslashes($_POST['field_info']));
         $validation_result = $wpdb->update( $formfields_table, $formfield, array('field_id' => intval($_POST['field_id'])) );
      } elseif ( isset($_POST['action']) && $_POST['action'] == "add" ) {
         // Add a new formfield
         $formfield = array();
         $formfield['field_name'] = trim(stripslashes($_POST['field_name']));
         $formfield['field_type'] = intval($_POST['field_type']);
         $formfield['field_info'] = trim(stripslashes($_POST['field_info']));
         $validation_result = $wpdb->insert( $formfields_table, $formfield );
      } elseif ( isset($_POST['action']) && $_POST['action'] == "delete" ) {
         // Delete formfield or multiple
         $formfields = $_POST['formfields'];
         if (is_array($formfields)) {
            //Make sure the array is only numbers
            foreach ($formfields as $field_id) {
               if (is_numeric($field_id)) {
                  $fields[] = "field_id = $field_id";
               }
            }
            //Run the query if we have an array of formfield ids
            if (count($fields > 0)) {
               $validation_result = $wpdb->query( "DELETE FROM $formfields_table WHERE ". implode(" OR ", $fields) );
            } else {
               $validation_result = false;
               $message = __("Couldn't delete the form fields. Incorrect field IDs supplied. Please try again.","eme");
            }
         }
      }
      //die(print_r($_POST));
      if (is_numeric($validation_result) ) {
         $message = (isset($message)) ? $message : __("Successfully {$_POST['action']}ed field", "eme");
         eme_formfields_table_layout($message);
      } elseif ( $validation_result === false ) {
         $message = (isset($message)) ? $message : __("There was a problem {$_POST['action']}ing the field, please try again.");                     
         eme_formfields_table_layout($message);
      } else {
         // no action, just a formfield list
         eme_formfields_table_layout();   
      }
   }
} 

function eme_formfields_table_layout($message = "") {
   $formfields = eme_get_formfields();
   $fieldtypes = eme_get_fieldtypes();
   $destination = admin_url("admin.php?page=eme-formfields"); 
   $table = "
      <div class='wrap nosubsub'>\n
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h2>".__('Form fields', 'eme')."</h2>\n ";   
         
         if($message != "") {
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
                              <th>".__('Title', 'eme')."</th>
                              <th>".__('Type', 'eme')."</th>
                           </tr>
                        </thead>
                        <tfoot>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Title', 'eme')."</th>
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
                        <input class='button-secondary action' type='submit' name='doaction2' value='Delete'/>
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
                           <input id='field-name' name='field_name' id='field_name' type='text' value='' size='40' />
                           <label for='field_type'>".__('Field type', 'eme')."</label>
			". eme_ui_select("","field_type",$fieldtypes)
                            ."
                           <label for='field_info'>".__('Field values', 'eme')."</label>
                           <input id='field-info' name='field_info' id='field_info' type='text' value='' size='40' />
                           <br />".__('Tip: for multivalue field types (like Drop Down), use "||" to seperate the different values (e.g.: a1||a2||a3)','eme')."
                         </div>
                         <p class='submit'><input type='submit' class='button' name='submit' value='".__('Add field', 'eme')."' /></p>
                      </form>
                 </div>
               </div>
            </div>
            <?-- end col-left -->
         </div>
   </div>";
   echo $table;  
}

function eme_formfields_edit_layout($message = "") {
   $field_id = intval($_GET['field_id']);
   $formfield = eme_get_formfield($field_id);
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
      </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Update field', 'eme')."' /></p>
      </form>
         
   </div>
         
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

function eme_get_formfield($field_id) { 
   global $wpdb;
   $formfields_table = $wpdb->prefix.FORMFIELDS_TBNAME; 
   $sql = "SELECT * FROM $formfields_table WHERE field_id ='$field_id'";   
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_fieldtype($type_id){
   global $wpdb;
   $fieldtypes_table = $wpdb->prefix.FIELDTYPES_TBNAME; 
   $formfields = array();
   $sql = "SELECT type_info FROM $fieldtypes_table WHERE type_id ='$type_id'";   
   return $wpdb->get_var($sql);
}

function eme_get_formfield_html($field_id) {
   $formfield = eme_get_formfield($field_id);
   $value = eme_sanitize_html($formfield['field_info']);
   switch($formfield['field_type']) {
      case 1:
	# for text field
	$html = "<input type='text' name='FIELD$field_id' value='$value'>";
	break;
      case 2:
        $values = explode("||",$value);
        $my_arr = array();
        foreach ($values as $val) {
		$my_arr[$val]=$val;
	}
	$html = eme_ui_select('',"FIELD$field_id",$my_arr);
	break;
      case 3:
	$html = "<textarea name='FIELD$field_id'>$value</textarea>";
	break;
   }
   return $html;
}

function eme_replace_formfields_placeholders ($format, $readonly, $bookerPhone_required, $bookedSeats, $booked_places_options, $bookerName, $bookerEmail, $bookerPhone, $bookerComment) {
   if (empty($format)) {
      $format = get_option('eme_registration_form_format');
   }
   if (preg_match('/#_CAPTCHAHTML\[(.+)\]/', $format)) {
	 if (get_option('eme_captcha_for_booking'))
            $format = preg_replace('/#_CAPTCHAHTML\[(.+)\]/', '$1' ,$format );
         else
            $format = preg_replace('/#_CAPTCHAHTML\[(.+)\]/', '' ,$format );
   }
   preg_match_all("/#(REQ)?_[A-Za-z0-9_]+/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'sort_stringlenth');

   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless
   $required_fields_count = 0;
   foreach($placeholders[0] as $result) {
      $orig_result = $result;
      $found=1;
      $required=0;
      $replacement = "";
      if (strstr($result,'#REQ')) {
         $result = str_replace("#REQ","#",$result);
         $required=1;
      }

      if (preg_match('/#_NAME$/', $result)) {
         $replacement = "<input type='text' name='bookerName' value='$bookerName' $readonly />";
         $required_fields_count++;
      } elseif (preg_match('/#_EMAIL$/', $result)) {
         $replacement = "<input type='text' name='bookerEmail' value='$bookerEmail' $readonly />";
         $required_fields_count++;
      } elseif (preg_match('/#_PHONE$/', $result)) {
         $replacement = "<input type='text' name='bookerPhone' value='$bookerPhone' />";
      } elseif (preg_match('/#_SEATS|#_SPACES$/', $result)) {
         $replacement = eme_ui_select($bookedSeats,"bookedSeats",$booked_places_options);
         $required_fields_count++;
      } elseif (preg_match('/#_COMMENT$/', $result)) {
         $replacement = "<textarea name='bookerComment'>$bookerComment</textarea>";
      } elseif (preg_match('/#_CAPTCHA$/', $result) && get_option('eme_captcha_for_booking')) {
         $replacement = "<img src='".EME_PLUGIN_URL."captcha.php'><br><input type='text' name='captcha_check' />";
      } elseif (preg_match('/#_FIELD(.+)/', $result, $matches)) {
         $field_id = intval($matches[1]);
         $replacement = eme_get_formfield_html($field_id);
      } else {
         $found = 0;
      }

      if ($required)
         $replacement .= "&nbsp;".__('(Required field)','eme');

      if ($found) {
         $replacement = eme_translate($replacement);
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   # we need 3 required fields: #_NAME, #_EMAIL and #_SEATS
   # if these are not present: we don't replace anything and the form is worthless
   if ($required_fields_count == 3) {
      return $format;
   } else {
      return __('Not all required fields are present in the form', 'eme');
   }
}

function eme_find_required_formfields ($format) {
   if (empty($format)) {
      $format = get_option('eme_registration_form_format');
   }
   preg_match_all("/#REQ_[A-Za-z0-9_]+/", $format, $placeholders);
   usort($placeholders[0],'sort_stringlenth');
   return str_replace("#REQ_","",$placeholders[0]);
}

?>
