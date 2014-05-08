<?php
function eme_categories_page() {      
   global $wpdb;
   
   admin_show_warnings();
   if (!current_user_can( get_option('eme_cap_categories')) && (isset($_GET['eme_admin_action']) || isset($_POST['eme_admin_action']))) {
      $message = __('You have no right to update categories!','eme');
      eme_categories_table_layout($message);
   } elseif (isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == "editcat") { 
      // edit category  
      eme_categories_edit_layout();
   } else {
      // Insert/Update/Delete Record
      $categories_table = $wpdb->prefix.CATEGORIES_TBNAME;
      $validation_result = '';
      if (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "edit" ) {
         // category update required  
         $category = array();
         $category['category_name'] = trim(stripslashes($_POST['category_name']));
         $validation_result = $wpdb->update( $categories_table, $category, array('category_id' => intval($_POST['category_ID'])) );
      } elseif ( isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "add" ) {
         // Add a new category
         $category = array();
         $category['category_name'] = trim(stripslashes($_POST['category_name']));
         $validation_result = $wpdb->insert($categories_table, $category);
      } elseif ( isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == "delete" ) {
         // Delete category or multiple
         $categories = $_POST['categories'];
         if (is_array($categories)) {
            //Make sure the array is only numbers
            foreach ($categories as $cat_id) {
               if (is_numeric($cat_id)) {
                  $cats[] = "category_id = $cat_id";
               }
            }
            //Run the query if we have an array of category ids
            if (count($cats > 0)) {
               $validation_result = $wpdb->query( "DELETE FROM $categories_table WHERE ". implode(" OR ", $cats) );
               if (is_numeric($validation_result))
                  $message = __("Successfully deleted the selected categories.","eme");
            } else {
               $validation_result = false;
               $message = __("Couldn't delete the categories. Incorrect category IDs supplied. Please try again.","eme");
            }
         } else {
            $validation_result = false;
            $message = __("Couldn't delete the categories. Incorrect category IDs supplied. Please try again.","eme");
         }
      }
      //die(print_r($_POST));
      if (is_numeric($validation_result) ) {
         $message = (isset($message)) ? $message : __("Successfully {$_POST['eme_admin_action']}ed category", "eme");
         eme_categories_table_layout($message);
      } elseif ( $validation_result === false ) {
         $message = (isset($message)) ? $message : __("There was a problem {$_POST['eme_admin_action']}ing your category, please try again.");
         eme_categories_table_layout($message);
      } else {
         // no action, just a categories list
         eme_categories_table_layout();
      }
   }
} 

function eme_categories_table_layout($message = "") {
   $categories = eme_get_categories();
   $destination = admin_url("admin.php?page=eme-categories"); 
   $table = "
      <div class='wrap nosubsub'>\n
         <div id='icon-edit' class='icon32'>
            <br />
         </div>
         <h2>".__('Categories', 'eme')."</h2>\n ";   
         
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
                  if (count($categories)>0) {
                     $table .= "<table class='widefat'>
                        <thead>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Name', 'eme')."</th>
                           </tr>
                        </thead>
                        <tfoot>
                           <tr>
                              <th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
                              <th>".__('ID', 'eme')."</th>
                              <th>".__('Name', 'eme')."</th>
                           </tr>
                        </tfoot>
                        <tbody>";
                     foreach ($categories as $this_category) {
                        $table .= "    
                           <tr>
                           <td><input type='checkbox' class ='row-selector' value='".$this_category['category_id']."' name='categories[]'/></td>
                           <td><a href='".admin_url("admin.php?page=eme-categories&amp;eme_admin_action=editcat&amp;category_ID=".$this_category['category_id'])."'>".$this_category['category_id']."</a></td>
                           <td><a href='".admin_url("admin.php?page=eme-categories&amp;eme_admin_action=editcat&amp;category_ID=".$this_category['category_id'])."'>".$this_category['category_name']."</a></td>
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
                        $table .= "<p>".__('No categories have been inserted yet!', 'eme');
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
                  <h3>".__('Add category', 'eme')."</h3>
                      <form name='add' id='add' method='post' action='".$destination."' class='add:the-list: validate'>
                        <input type='hidden' name='eme_admin_action' value='add' />
                         <div class='form-field form-required'>
                           <label for='category_name'>".__('Category name', 'eme')."</label>
                           <input name='category_name' id='category_name' type='text' value='' size='40' />
                            <p>".__('The name of the category', 'eme').".</p>
                         </div>
                         <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Add category', 'eme')."' /></p>
                      </form>
                 </div>
               </div>
            </div>
            <?-- end col-left -->
         </div>
   </div>";
   echo $table;  
}

function eme_categories_edit_layout($message = "") {
   $category_id = intval($_GET['category_ID']);
   $category = eme_get_category($category_id);
   $layout = "
   <div class='wrap'>
      <div id='icon-edit' class='icon32'>
         <br />
      </div>
         
      <h2>".__('Edit category', 'eme')."</h2>";   
      
      if($message != "") {
         $layout .= "
      <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
      </div>";
      }
      $layout .= "
      <div id='ajax-response'></div>

      <form name='editcat' id='editcat' method='post' action='".admin_url("admin.php?page=eme-categories")."' class='validate'>
      <input type='hidden' name='eme_admin_action' value='edit' />
      <input type='hidden' name='category_ID' value='".$category['category_id']."'/>";
      
      $layout .= "
         <table class='form-table'>
            <tr class='form-field form-required'>
               <th scope='row' valign='top'><label for='category_name'>".__('Category name', 'eme')."</label></th>
               <td><input name='category_name' id='category_name' type='text' value='".eme_sanitize_html($category['category_name'])."' size='40'  /><br />
                 ".__('The name of the category', 'eme')."</td>
            </tr>
         </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Update category', 'eme')."' /></p>
      </form>
   </div>
   ";  
   echo $layout;
}

function eme_get_categories($eventful=false,$scope="future",$extra_conditions=""){
   global $wpdb;
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $categories = array();
   $orderby = " ORDER BY category_name ASC";
   if ($eventful) {
      $events = eme_get_events(0, $scope, "ASC");
      if ($events) {
         foreach ($events as $event) {
            if (!empty($event['event_category_ids'])) {
               $event_cats=explode(",",$event['event_category_ids']);
               if (!empty($event_cats)) {
                  foreach ($event_cats as $category_id) {
                     $categories[$category_id]=$category_id;
                  }
               }
            }
         }
      }
      if (!empty($categories)) {
         $event_cats=join(",",$categories);
         if ($extra_conditions !="")
            $extra_conditions = " AND ($extra_conditions)";
         $result = $wpdb->get_results("SELECT * FROM $categories_table where category_id in ($event_cats) $extra_conditions $orderby", ARRAY_A);
      }
   } else {
      if ($extra_conditions !="")
         $extra_conditions = " WHERE ($extra_conditions)";
      $result = $wpdb->get_results("SELECT * FROM $categories_table $extra_conditions $orderby", ARRAY_A);
   }
   if (has_filter('eme_categories_filter')) $result=apply_filters('eme_categories_filter',$result); 
   return $result;
}

function eme_get_category($category_id) { 
   global $wpdb;
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $sql = "SELECT * FROM $categories_table WHERE category_id ='$category_id'";   
   $category = $wpdb->get_row($sql, ARRAY_A);
   return $category;
}

function eme_get_event_categories($event_id,$extra_conditions="") { 
   global $wpdb;
   $event_table = $wpdb->prefix.EVENTS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   if ($extra_conditions !="")
      $extra_conditions = " AND ($extra_conditions)";
   $sql = "SELECT category_name FROM $categories_table, $event_table where event_id ='$event_id' AND FIND_IN_SET(category_id,event_category_ids) $extra_conditions";
   $category = $wpdb->get_col($sql);
   return $category;
}

function eme_get_location_categories($location_id) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $sql = "SELECT category_name FROM $categories_table, $locations_table where location_id ='$location_id' AND FIND_IN_SET(category_id,location_category_ids)";
   $category = $wpdb->get_col($sql);
   return $category;
}

function eme_get_category_ids($cat_name) {
   global $wpdb;
   $categories_table = $wpdb->prefix.CATEGORIES_TBNAME; 
   $cat_ids = array();
   $conditions="";
   if (!empty($cat_name)) {
      $conditions = " category_name = '$cat_name'";
   }
   if (!empty($conditions)) {
      $sql = "SELECT DISTINCT category_id FROM $categories_table WHERE ".$conditions;
      $cat_ids = $wpdb->get_col($sql);
   }
   return $cat_ids;
}

?>
