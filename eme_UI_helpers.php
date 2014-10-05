<?php 
function eme_option_items($array, $saved_value) {
   $output = "";
   foreach($array as $key => $item) {
      $selected ='';
      if (is_array($saved_value)) {
         in_array($key,$saved_value) ? $selected = "selected='selected' " : $selected = '';
      } else {
         "$key" == $saved_value ? $selected = "selected='selected' " : $selected = '';
      }
      $output .= "<option value='$key' $selected >$item</option>\n";
   } 
   echo $output;
}

function eme_checkbox_items($name, $array, $saved_values, $horizontal = true) { 
   $output = "";
   foreach($array as $key => $item) {
      
      $checked = "";
      if (in_array($key, $saved_values))
         $checked = "checked='checked'";
      $output .=  "<input type='checkbox' name='$name' value='$key' $checked /> $item ";
      if(!$horizontal)  
         $output .= "<br />\n";
   }
   echo $output;
   
}

function eme_options_input_text($title, $name, $description) {
   $value= preg_replace("/\r\n|\n\r|\n/","<br />",get_option($name));
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title, 'eme') ?></th>
       <td>
         <input name="<?php echo $name ?>" type="text" id="<?php echo $name ?>" style="width: 95%" value="<?php echo eme_sanitize_html($value); ?>" size="45" />
                  <?php if (!empty($description)) echo "<br />".$description; ?>
       </td>
   </tr>
   <?php
}

function eme_options_input_password($title, $name, $description) {
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title, 'eme') ?></th>
       <td>
         <input name="<?php echo $name ?>" type="password" id="<?php echo $name ?>" style="width: 95%" value="<?php echo get_option($name); ?>" size="45" />
                  <?php if (!empty($description)) echo "<br />".$description; ?>
         </td>
      </tr>
   <?php
}

function eme_options_textarea($title, $name, $description) {
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title,'eme')?></th>
         <td><textarea name="<?php echo $name ?>" id="<?php echo $name ?>" rows="6" style="width: 95%"><?php echo eme_sanitize_html(get_option($name));?></textarea>
            <?php if (!empty($description)) echo "<br />".$description; ?></td>
      </tr>
   <?php
}

function eme_options_radio_binary($title, $name, $description, $option_value=false) {
      if (!$option_value)
         $option_value = get_option($name);
      if ($name == "eme_permalink_events_prefix" || $name == "eme_permalink_locations_prefix") {
         $option_value = eme_permalink_convert($option_value);
      }
?>
       
         <tr valign="top" id='<?php echo $name;?>_row'>
            <th scope="row"><?php _e($title,'eme'); ?></th>
            <td>
            <input id="<?php echo $name ?>_yes" name="<?php echo $name ?>" type="radio" value="1" <?php if($option_value) echo "checked='checked'"; ?> /><?php _e('Yes'); ?> <br />
            <input  id="<?php echo $name ?>_no" name="<?php echo $name ?>" type="radio" value="0" <?php if(!$option_value) echo "checked='checked'"; ?> /><?php _e('No'); ?>
            <?php if (!empty($description)) echo "<br />".$description; ?>
         </td>
         </tr>
<?php 
}

function eme_options_select($title, $name, $list, $description, $option_value=false) {
      if (!$option_value)
         $option_value = get_option($name);
?>
         <tr valign="top" id='<?php echo $name;?>_row'>
            <th scope="row"><?php _e($title,'eme'); ?></th>
            <td>
            <select name="<?php echo $name; ?>" > 
               <?php
                 foreach($list as $key => $value) {
                    "$key" == $option_value ? $selected = "selected='selected' " : $selected = '';
                    echo "<option value='$key' $selected>$value</option>";
                 }
               ?>
            </select>
            <?php if (!empty($description)) echo "<br />".$description; ?>
         </td>
         </tr>
<?php 
}

function eme_ui_select_binary ($option_value, $name, $required=0) {
   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   $val = "<select $required_att name='$name'>";
   $selected_YES="";
   $selected_NO="";
   if ($option_value==1)
      $selected_YES = "selected='selected'";
   else
      $selected_NO = "selected='selected'";
   $val.= "<option value='0' $selected_NO>".__('No')."</option>";
   $val.= "<option value='1' $selected_YES>".__('Yes')."</option>";
   $val.=" </select>";
   return $val;
}

function eme_ui_select($option_value, $name, $list, $required=0) {
   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   $val = "<select $required_att id='$name' name='$name'>";
   foreach($list as $key => $value) {
      if (is_array($value)) {
         $t_key=$value[0];
         $t_value=$value[1];
      } else {
         $t_key=$key;
         $t_value=$value;
      }
      "$t_key" == $option_value ? $selected = "selected='selected' " : $selected = '';
      $val.= "<option value='".eme_sanitize_html($t_key)."' $selected>".eme_sanitize_html($t_value)."</option>";
   }
   $val.=" </select>";
   return $val;
}

function eme_ui_multiselect($option_value, $name, $list, $size=3, $required=0) {
   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   $val = "<select $required_att multiple='multiple' name='${name}[]' size='$size'>";
   foreach($list as $key => $value) {
      if (is_array($option_value)) {
         in_array($key,$option_value) ? $selected = "selected='selected' " : $selected = '';
      } else {
         "$key" == $option_value ? $selected = "selected='selected' " : $selected = '';
      }
      $val.= "<option value='".eme_sanitize_html($key)."' $selected>".eme_sanitize_html($value)."</option>";
   }
   $val.=" </select>";
   return $val;
}

function eme_ui_radio($option_value, $name, $list,$horizontal = true, $required=0) {
   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   $val = "";
   foreach($list as $key => $value) {
      if (is_array($value)) {
         $t_key=$value[0];
         $t_value=$value[1];
      } else {
         $t_key=$key;
         $t_value=$value;
      }
      "$t_key" == $option_value ? $selected = "checked='checked' " : $selected = '';
      $val.= "<input $required_att type='radio' id='$name' name='$name' value='".eme_sanitize_html($t_key)."' $selected />".eme_sanitize_html($t_value);
      if(!$horizontal)  
         $val .= "<br />\n";
   }
   return $val;
}

function eme_ui_checkbox($option_value, $name, $list, $horizontal = true, $required=0) {
   if ($required)
      $required_att="required='required'";
   else
      $required_att="";

   $val = "";
   foreach($list as $key => $value) {
      if (is_array($option_value)) {
         in_array($key,$option_value) ? $selected = "checked='checked' " : $selected = '';
      } else {
         "$key" == $option_value ? $selected = "checked='checked' " : $selected = '';
      }
      $val.= "<input $required_att type='checkbox' name='${name}[]' value='".eme_sanitize_html($key)."' $selected />".eme_sanitize_html($value);
      if(!$horizontal)  
         $val .= "<br />\n";
   }
   return $val;
}

?>
