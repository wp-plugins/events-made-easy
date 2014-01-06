<?php
function eme_format_templates_page() {      
   global $wpdb;
   
   admin_show_warnings();
   if (!current_user_can( get_option('eme_cap_format_templates')) && (isset($_GET['action']) || isset($_POST['action']))) {
      $message = __('You have no right to update format templates!','eme');
      eme_format_templates_table_layout($message);
   } elseif (isset($_GET['action']) && $_GET['action'] == "editformattemplate") { 
      // edit format template  
      eme_format_templates_edit_layout();
   } else {
      // Insert/Update/Delete Record
      $format_templates_table = $wpdb->prefix.FORMAT_TEMPLATES_TBNAME;
      $validation_result = '';
      if (isset($_POST['action']) && $_POST['action'] == "edit" ) {
         // format template update required  
         $template = array();
         $template['format_template'] = trim(stripslashes($_POST['format_template']));
         $template['format_description'] = trim(stripslashes($_POST['format_description']));
         $validation_result = $wpdb->update( $format_templates_table, $template, array('id' => intval($_POST['template_ID'])) );
      } elseif ( isset($_POST['action']) && $_POST['action'] == "add" ) {
         // Add a new template
         $template = array();
         $template['format_template'] = trim(stripslashes($_POST['format_template']));
         $template['format_description'] = trim(stripslashes($_POST['format_description']));
         $validation_result = $wpdb->insert($format_templates_table, $template);
      } elseif ( isset($_POST['action']) && $_POST['action'] == "delete" ) {
         // Delete template or multiple
         $format_templates = $_POST['format_templates'];
         if (is_array($format_templates)) {
            //Make sure the array is only numbers
            foreach ($format_templates as $template_id) {
               if (is_numeric($template_id)) {
                  $templates[] = "id = $template_id";
               }
            }
            //Run the query if we have an array of template ids
            if (count($templates > 0)) {
               $validation_result = $wpdb->query( "DELETE FROM $format_templates_table WHERE ". implode(" OR ", $templates) );
            } else {
               $validation_result = false;
               $message = __("Couldn't delete the templates. Incorrect template IDs supplied. Please try again.","eme");
            }
         }
      }
      //die(print_r($_POST));
      if (is_numeric($validation_result) ) {
         $message = (isset($message)) ? $message : __("Successfully {$_POST['action']}ed format template", "eme");
         eme_format_templates_table_layout($message);
      } elseif ( $validation_result === false ) {
         $message = (isset($message)) ? $message : __("There was a problem {$_POST['action']}ing the format template, please try again.");
         eme_format_templates_table_layout($message);
      } else {
         // no action, just a template list
         eme_format_templates_table_layout();
      }
   }
} 

function eme_format_templates_table_layout($message = "") {
   $format_templates = eme_get_format_templates();
   $destination = admin_url("admin.php?page=eme-format-templates"); 
   $table = "
      <div class='wrap nosubsub'>\n
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h2>".__('Format templates', 'eme')."</h2>\n ";   
         
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
                  if (count($format_templates)>0) {
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
                     foreach ($format_templates as $this_format_template) {
                        $table .= "    
                           <tr>
                           <td><input type='checkbox' class ='row-selector' value='".$this_format_template['id']."' name='format_templates[]'/></td>
                           <td><a href='".admin_url("admin.php?page=eme-format-templates&amp;action=editformattemplate&amp;template_ID=".$this_format_template['id'])."'>".$this_format_template['id']."</a></td>
                           <td><a href='".admin_url("admin.php?page=eme-format-templates&amp;action=editformattemplate&amp;template_ID=".$this_format_template['id'])."'>".$this_format_template['format_description']."</a></td>
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
                        $table .= "<p>".__('No format templates have been inserted yet!', 'eme');
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
                  <h3>".__('Add format template', 'eme')."</h3>
                      <form name='add' id='add' method='post' action='".$destination."' class='add:the-list: validate'>
                        <input type='hidden' name='action' value='add' />
                         <div class='form-field form-required'>
                           <label for='format_description'>".__('Description', 'eme')."</label>
                           <input type='text' name='format_description' id='format_description' value='' size='40' />
                           <label for='format_template'>".__('Format', 'eme')."</label>
                           <textarea name='format_template' id='format_template' value='' rows='5' ></textarea>
                            <p>".__('The format of the new template', 'eme').".</p>
                         </div>
                         <p class='submit'><input type='submit' class='button' name='submit' value='".__('Add format template', 'eme')."' /></p>
                      </form>
                 </div>
               </div>
            </div>
            <?-- end col-left -->
         </div>
   </div>";
   echo $table;  
}

function eme_format_templates_edit_layout($message = "") {
   $template_id = intval($_GET['template_ID']);
   $template = eme_get_format_template($template_id);
   $layout = "
   <div class='wrap'>
      <div id='icon-edit' class='icon32'>
         <br />
      </div>
         
      <h2>".__('Edit format template', 'eme')."</h2>";   
      
      if($message != "") {
         $layout .= "
      <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
      </div>";
      }
      $layout .= "
      <div id='ajax-response'></div>

      <form name='editformattemplate' id='editformattemplate' method='post' action='".admin_url("admin.php?page=eme-format-templates")."' class='validate'>
      <input type='hidden' name='action' value='edit' />
      <input type='hidden' name='template_ID' value='".$template['id']."'/>";
      
      $layout .= "
         <table class='form-table'>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='format_description'>".__('Format description', 'eme')."</label></th>
               <td><input type='text' name='format_description' id='format_description' value='".eme_sanitize_html($template['format_description'])."' size='40'  /><br />
                 ".__('The description of the template', 'eme')."</td>
            </tr>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='format_template'>".__('Format', 'eme')."</label></th>
               <td><textarea name='format_template' id='format_template' rows='5' />".eme_sanitize_html($template['format_template'])."</textarea><br />
                 ".__('The format of the template', 'eme')."</td>
            </tr>
         </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Update format template', 'eme')."' /></p>
      </form>
   </div>
   ";  
   echo $layout;
}

function eme_get_format_templates() {
   global $wpdb;
   $format_templates_table = $wpdb->prefix.FORMAT_TEMPLATES_TBNAME;
   $format_templates = array();
   return $wpdb->get_results("SELECT * FROM $format_templates_table", ARRAY_A);
}

function eme_get_format_template($template_id) { 
   global $wpdb;
   $template_id = intval($template_id);
   $format_templates_table = $wpdb->prefix.FORMAT_TEMPLATES_TBNAME;
   $sql = "SELECT * FROM $format_templates_table WHERE id ='$template_id'";   
   $format_template = $wpdb->get_row($sql, ARRAY_A);
   return $format_template;
}

?>
