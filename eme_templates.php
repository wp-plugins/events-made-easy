<?php
function eme_templates_page() {      
   global $wpdb;
   
   admin_show_warnings();
   if (!current_user_can( get_option('eme_cap_templates')) && (isset($_GET['eme_admin_action']) || isset($_POST['eme_admin_action']))) {
      $message = __('You have no right to update templates!','eme');
      eme_templates_table_layout($message);
   } elseif (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "edittemplate") { 
      // edit template  
      eme_templates_edit_layout();
   } else {
      // Insert/Update/Delete Record
      $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
      $validation_result = '';
      if (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "edit" ) {
         // template update required  
         $template = array();
         $template['format'] = trim(stripslashes($_POST['format']));
         $template['description'] = trim(stripslashes($_POST['description']));
         $validation_result = $wpdb->update( $templates_table, $template, array('id' => intval($_POST['template_ID'])) );
      } elseif ( isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add" ) {
         // Add a new template
         $template = array();
         $template['format'] = trim(stripslashes($_POST['format']));
         $template['description'] = trim(stripslashes($_POST['description']));
         $validation_result = $wpdb->insert($templates_table, $template);
      } elseif ( isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "delete" ) {
         // Delete template or multiple
         $templates = $_POST['templates'];
         if (is_array($templates)) {
            //Make sure the array is only numbers
            foreach ($templates as $template_id) {
               if (is_numeric($template_id)) {
                  $templates[] = "id = $template_id";
               }
            }
            //Run the query if we have an array of template ids
            if (count($templates > 0)) {
               $validation_result = $wpdb->query( "DELETE FROM $templates_table WHERE ". implode(" OR ", $templates) );
               if (is_numeric($validation_result) )
                  $message = __("Successfully deleted the template(s).","eme");
            } else {
               $validation_result = false;
               $message = __("Couldn't delete the templates. Incorrect template IDs supplied. Please try again.","eme");
            }
         } else {
            $validation_result = false;
            $message = __("Couldn't delete the templates. Incorrect template IDs supplied. Please try again.","eme");
         }
      }
      //die(print_r($_POST));
      if (is_numeric($validation_result) ) {
         $message = (isset($message)) ? $message : __("Successfully {$_POST['eme_admin_action']}ed template", "eme");
         eme_templates_table_layout($message);
      } elseif ( $validation_result === false ) {
         $message = (isset($message)) ? $message : __("There was a problem {$_POST['eme_admin_action']}ing the template, please try again.");
         eme_templates_table_layout($message);
      } else {
         // no action, just a template list
         eme_templates_table_layout();
      }
   }
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
                  <input type='hidden' name='eme_admin_action' value='delete'/>";
                  if (count($templates)>0) {
                     $table .= "<table class='widefat'>
                        <thead>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Format description', 'eme')."</th>
                           </tr>
                        </thead>
                        <tfoot>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Format description', 'eme')."</th>
                           </tr>
                        </tfoot>
                        <tbody>";
                     foreach ($templates as $this_template) {
                        $table .= "    
                           <tr>
                           <td><input type='checkbox' class ='row-selector' value='".$this_template['id']."' name='templates[]'/></td>
                           <td><a href='".admin_url("admin.php?page=eme-templates&amp;eme_admin_action=edittemplate&amp;template_ID=".$this_template['id'])."'>".$this_template['id']."</a></td>
                           <td><a href='".admin_url("admin.php?page=eme-templates&amp;eme_admin_action=edittemplate&amp;template_ID=".$this_template['id'])."'>".$this_template['description']."</a></td>
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
                        <input type='hidden' name='eme_admin_action' value='add' />
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
   $template_id = intval($_GET['template_ID']);
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

      <form name='edittemplate' id='edittemplate' method='post' action='".admin_url("admin.php?page=eme-templates")."' class='validate'>
      <input type='hidden' name='eme_admin_action' value='edit' />
      <input type='hidden' name='template_ID' value='".$template['id']."'/>";
      
      $layout .= "
         <table class='form-table'>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='description'>".__('Template description', 'eme')."</label></th>
               <td><input type='text' name='description' id='description' value='".eme_sanitize_html($template['description'])."' size='40'  /><br />
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
   $templates = array();
   return $wpdb->get_results("SELECT * FROM $templates_table", ARRAY_A);
}

function eme_get_template($template_id) { 
   global $wpdb;
   $template_id = intval($template_id);
   $templates_table = $wpdb->prefix.TEMPLATES_TBNAME;
   $sql = "SELECT * FROM $templates_table WHERE id ='$template_id'";   
   $template = $wpdb->get_row($sql, ARRAY_A);
   return $template;
}

?>
