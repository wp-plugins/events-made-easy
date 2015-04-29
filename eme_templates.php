<?php
function eme_templates_page() {      
   global $wpdb;
   
   admin_show_warnings();
   if (!current_user_can( get_option('eme_cap_templates')) && (isset($_GET['eme_admin_action']) || isset($_POST['eme_admin_action']))) {
      $message = __('You have no right to update templates!','eme');
      eme_templates_table_layout($message);
      return;
   }
   if (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edit_template") { 
      // edit template  
      eme_templates_edit_layout();
      return;
   }

   $message = '';
   if (isset($_POST['eme_admin_action'])) {
      // Insert/Update/Delete Record
      $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
      if ($_POST['eme_admin_action'] == "do_edittemplate" ) {
         // template update required  
         $template = array();
         $template['format'] = trim(stripslashes($_POST['format']));
         $template['description'] = trim(stripslashes($_POST['description']));
         $validation_result = $wpdb->update( $templates_table, $template, array('id' => intval($_POST['template_id'])) );
         if ($validation_result !== false) {
            $message = __("Successfully edited the template.", "eme");
         } else {
            $message = __("There was a problem editing your template, please try again.","eme");
         }
      } elseif ($_POST['eme_admin_action'] == "do_addtemplate" ) {
         // Add a new template
         $template = array();
         $template['format'] = trim(stripslashes($_POST['format']));
         $template['description'] = trim(stripslashes($_POST['description']));
         $validation_result = $wpdb->insert($templates_table, $template);
         if ($validation_result !== false) {
            $message = __("Successfully added the template.", "eme");
         } else {
            $message = __("There was a problem adding your template, please try again.","eme");
         }
      } elseif ($_POST['eme_admin_action'] == "do_deletetemplate" && isset($_POST['templates'])) {
         // Delete template or multiple
         $templates = $_POST['templates'];
         if (is_array($templates)) {
            //Run the query if we have an array of template ids
            if (count($templates > 0)) {
               $validation_result = $wpdb->query( "DELETE FROM $templates_table WHERE id IN (". implode(",",$templates) .")" );
               if ($validation_result !== false)
                  $message = __("Successfully deleted the selected template(s).","eme");
               else
                  $message = __("There was a problem deleting the selected template(s), please try again.","eme");
            } else {
               $message = __("Couldn't delete the templates. Incorrect template IDs supplied. Please try again.","eme");
            }
         } else {
            $message = __("Couldn't delete the templates. Incorrect template IDs supplied. Please try again.","eme");
         }
      }
   }
   eme_templates_table_layout($message);
} 

function eme_templates_table_layout($message = "") {
   $templates = eme_get_templates();
   $destination = admin_url("admin.php?page=eme-templates"); 
   $table = "
      <div class='wrap nosubsub'>\n
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h2>".__('Templates', 'eme')."</h2>\n ";   
         
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
                  <input type='hidden' name='eme_admin_action' value='do_deletetemplate' />";
                  if (count($templates)>0) {
                     $table .= "<table class='widefat'>
                        <thead>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1' /></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Format description', 'eme')."</th>
                           </tr>
                        </thead>
                        <tfoot>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1' /></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Format description', 'eme')."</th>
                           </tr>
                        </tfoot>
                        <tbody>";
                     foreach ($templates as $this_template) {
                        $table .= "    
                           <tr>
                           <td><input type='checkbox' class ='row-selector' value='".$this_template['id']."' name='templates[]' /></td>
                           <td><a href='".admin_url("admin.php?page=eme-templates&amp;eme_admin_action=edit_template&amp;template_id=".$this_template['id'])."'>".$this_template['id']."</a></td>
                           <td><a href='".admin_url("admin.php?page=eme-templates&amp;eme_admin_action=edit_template&amp;template_id=".$this_template['id'])."'>".$this_template['description']."</a></td>
                           </tr>
                        ";
                     }
                     $delete_text=__("Are you sure you want to delete these templates?","eme");
                     $table .= <<<EOT
                        </tbody>
                        </table>

                        <div class='tablenav'>
                        <div class='alignleft actions'>
                        <input class='button-primary action' type='submit' name='doaction' value='Delete' onclick="return areyousure('$delete_text');" />
                        <br class='clear'/>
                        </div>
                        <br class='clear'/>
                        </div>
EOT;

                  } else {
                        $table .= "<p>".__('No templates have been inserted yet!', 'eme');
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
                  <h3>".__('Add template', 'eme')."</h3>
                      <form name='add' id='add' method='post' action='".$destination."' class='add:the-list: validate'>
                        <input type='hidden' name='eme_admin_action' value='do_addtemplate' />
                         <div class='form-field form-required'>
                           <label for='description'>".__('Template description', 'eme')."</label>
                           <input type='text' name='description' id='description' value='' size='40' />
                           <label for='format'>".__('Format', 'eme')."</label>
                           <textarea name='format' id='format' value='' rows='5' ></textarea>
                            <p>".__('The format of the template', 'eme').".</p>
                         </div>
                         <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Add template', 'eme')."' /></p>
                      </form>
                 </div>
               </div>
            </div>
            <?-- end col-left -->
         </div>
   </div>";
   echo $table;  
}

function eme_templates_edit_layout($message = "") {
   $template_id = intval($_GET['template_id']);
   $template = eme_get_template($template_id);
   $layout = "
   <div class='wrap'>
      <div id='icon-edit' class='icon32'>
         <br />
      </div>
         
      <h2>".__('Edit template', 'eme')."</h2>";   
      
      if($message != "") {
         $layout .= "
      <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
      </div>";
      }
      $layout .= "
      <div id='ajax-response'></div>

      <form name='edit_template' id='edit_template' method='post' action='".admin_url("admin.php?page=eme-templates")."' class='validate'>
      <input type='hidden' name='eme_admin_action' value='do_edittemplate' />
      <input type='hidden' name='template_id' value='".$template['id']."' />";
      
      $layout .= "
         <table class='form-table'>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='description'>".__('Template description', 'eme')."</label></th>
               <td><input type='text' name='description' id='description' value='".eme_sanitize_html($template['description'])."' size='40' /><br />
                 ".__('The description of the template', 'eme')."</td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='format'>".__('Template format', 'eme')."</label></th>
               <td><textarea name='format' id='format' rows='5' />".eme_sanitize_html($template['format'])."</textarea><br />
                 ".__('The format of the template', 'eme')."</td>
            </tr>
         </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Update template', 'eme')."' /></p>
      </form>
   </div>
   ";  
   echo $layout;
}

function eme_get_templates() {
   global $wpdb;
   $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
   return $wpdb->get_results("SELECT * FROM $templates_table ORDER BY description", ARRAY_A);
}

function eme_get_templates_array_by_id() {
   $templates = eme_get_templates();
   $templates_by_id=array();
   foreach ($templates as $template) {
      $templates_by_id[$template['id']]=$template['description'];
   }
   return $templates_by_id;
}

function eme_get_template($template_id) { 
   global $wpdb;
   $template_id = intval($template_id);
   $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
   $sql = "SELECT * FROM $templates_table WHERE id ='$template_id'";   
   return $wpdb->get_row($sql, ARRAY_A);
}

function eme_get_template_format($template_id) { 
   global $wpdb;
   $template_id = intval($template_id);
   $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
   $sql = "SELECT format FROM $templates_table WHERE id ='$template_id'";   
   return $wpdb->get_var($sql);
}

?>
