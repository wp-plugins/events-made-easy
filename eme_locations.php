<?php
$feedback_message = "";
 
function eme_locations_page() {
   $current_userid=get_current_user_id();
   if (isset($_GET['action']) && $_GET['action'] == "edit") { 
      $location_id = intval($_GET['location_ID']);
      $location = eme_get_location($location_id);
      if (current_user_can( get_option('eme_cap_edit_locations')) ||
             (current_user_can( get_option('eme_cap_author_locations')) && ($location['location_author']==$current_userid))) {
         // edit location
         eme_locations_edit_layout($location);
      } else {
         $message = __('You have no right to edit this location!','eme');
         $locations = eme_get_locations();
         eme_locations_table_layout($locations, null, $message);
      }
   } elseif (isset($_POST['action']) && $_POST['action'] == "delete") { 
      $locations = $_POST['locations'];
      foreach($locations as $location_id) {
         $location = eme_get_location(intval($location_id));
         if (current_user_can( get_option('eme_cap_edit_locations')) ||
               (current_user_can( get_option('eme_cap_author_locations')) && ($location['location_author']==$current_userid))) {
            eme_delete_location(intval($location_id));
         }
      }
      $locations = eme_get_locations();
      eme_locations_table_layout($locations, null, "");
   } elseif (isset($_POST['action']) && $_POST['action'] == "editedlocation") { 
      $orig_location=eme_get_location(intval($_POST['location_ID']));
      if (current_user_can( get_option('eme_cap_edit_locations')) ||
            (current_user_can( get_option('eme_cap_author_locations')) && ($orig_location['location_author']==$current_userid))) {
         // location update required
         $location = array();
         $location['location_id'] = intval($_POST['location_ID']);
         $location['location_name'] = trim(stripslashes($_POST['location_name']));
         $location['location_address'] = stripslashes($_POST['location_address']); 
         $location['location_town'] = stripslashes($_POST['location_town']); 
         $location['location_latitude'] = $_POST['location_latitude'];
         $location['location_longitude'] = $_POST['location_longitude'];
         $location['location_description'] = stripslashes($_POST['content']);
         $location['location_url'] = isset($_POST ['location_url']) ? eme_strip_tags ( $_POST ['location_url'] ) : '';
         $location['location_slug'] = isset($_POST ['location_slug']) ? eme_permalink_convert(eme_strip_tags ( $_POST ['location_slug'] )) : eme_permalink_convert($location['location_name']);
         // we don't change the author
         //$location['location_author'] = $current_userid;
         if (isset ($_POST['location_category_ids'])) {
            // the category id's need to begin and end with a comma
            // this is needed so we can later search for a specific
            // cat using LIKE '%,$cat,%'
            $location ['location_category_ids']="";
            foreach ($_POST['location_category_ids'] as $cat) {
               if (is_numeric($cat)) {
                  if (empty($location ['location_category_ids'])) {
                     $location ['location_category_ids'] = "$cat";
                  } else {
                     $location ['location_category_ids'] .= ",$cat";
                  }
               }
            }
         } else {
            $location ['location_category_ids']="";
         }

         if(empty($location['location_latitude'])) {
            $location['location_latitude']  = 0;
            $location['location_longitude'] = 0;
         }

         $validation_result = eme_validate_location($location);
         if ($validation_result == "OK") {
            if (eme_update_location($location)) {
               $message = __('The location has been updated.', 'eme');
               if ($_FILES['location_image']['size'] > 0 )
                  eme_upload_location_picture($location);
            } else {
               $message = __('The location update failed.', 'eme');
            }
            $locations = eme_get_locations();
            eme_locations_table_layout($locations, $location, $message);
         } else {
            $message = $validation_result;
            eme_locations_edit_layout($location, $message);
         }
      } else {
         $message = __('You have no right to edit this location!','eme');
         $locations = eme_get_locations();
         eme_locations_table_layout($locations, null, $message);
      }
   } elseif(isset($_POST['action']) && $_POST['action'] == "addlocation") {
      if (current_user_can( get_option('eme_cap_add_locations'))) {
         $location = array();
         $location['location_name'] = trim(stripslashes($_POST['location_name']));
         $location['location_address'] = stripslashes($_POST['location_address']);
         $location['location_town'] = stripslashes($_POST['location_town']); 
         $location['location_latitude'] = $_POST['location_latitude'];
         $location['location_longitude'] = $_POST['location_longitude'];
         $location['location_description'] = stripslashes($_POST['content']);
         $location['location_author'] = $current_userid;
         $location['location_url'] = isset($_POST ['location_url']) ? eme_strip_tags ( $_POST ['location_url'] ) : '';
         $location['location_slug'] = isset($_POST ['location_slug']) ? eme_permalink_convert(eme_strip_tags ( $_POST ['location_slug'] )) : eme_permalink_convert($location['location_name']);
         if (isset ($_POST['location_category_ids'])) {
            // the category id's need to begin and end with a comma
            // this is needed so we can later search for a specific
            // cat using LIKE '%,$cat,%'
            $location ['location_category_ids']="";
            foreach ($_POST['location_category_ids'] as $cat) {
               if (is_numeric($cat)) {
                  if (empty($location ['location_category_ids'])) {
                     $location ['location_category_ids'] = "$cat";
                  } else {
                     $location ['location_category_ids'] .= ",$cat";
                  }
               }
            }
         } else {
            $location ['location_category_ids']="";
         }

         $validation_result = eme_validate_location($location);
         if ($validation_result == "OK") {
            $new_location = eme_insert_location($location);
            if ($new_location) {
               $message = __('The location has been added.', 'eme'); 
               // uploading the image
               if ($_FILES['location_image']['size'] > 0 )
                  eme_upload_location_picture($new_location);
            } else {
               $message = __('There has been a problem adding the location.', 'eme'); 
            }      
            $locations = eme_get_locations();
            eme_locations_table_layout($locations, null,$message);
         } else {
            $message = $validation_result;
            $locations = eme_get_locations();
            eme_locations_table_layout($locations, $location, $message);
         }
      } else {
         $message = __('You have no right to add a location!','eme');
         $locations = eme_get_locations();
         eme_locations_table_layout($locations, null, $message);
      }
   } else {
      // no action, just a locations list
      $locations = eme_get_locations();
      eme_locations_table_layout($locations, null, "");
   }
}

function eme_locations_edit_layout($location, $message = "") {
   ?>
   <div class="wrap">
      <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>
            
         <h2><?php _e('Edit location', 'eme') ?></h2>
             <?php admin_show_warnings(); ?>
         
         <?php if($message != "") : ?>
            <div id="message" class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
               <p><?php  echo $message ?></p>
            </div>
         <?php endif; ?>
         <div id="ajax-response"></div>
   
         <form enctype="multipart/form-data" name="editcat" id="editcat" method="post" action="<?php echo admin_url("admin.php?page=events-manager-locations"); ?>" class="validate">
         <input type="hidden" name="action" value="editedlocation" />
         <input type="hidden" name="location_ID" value="<?php echo $location['location_id'] ?>"/>
         
         <!-- we need titlediv and title for qtranslate as ID -->
         <div id="titlediv" class="form-required">
           <label><?php _e('Location name', 'eme') ?></label>
           <input name="location_name" id="title" type="text" value="<?php echo eme_sanitize_html($location['location_name']); ?>" size="40" />
           <input type="hidden" name="translated_location_name" value="<?php echo eme_trans_sanitize_html($location['location_name']); ?>" />
           <p><?php _e('The name of the location', 'eme') ?>.</p>
                        <?php if ($location ['location_name'] != "") {
                                 _e ('Permalink: ', 'eme' );
                                 echo trailingslashit(home_url()).eme_permalink_convert(get_option ( 'eme_permalink_events_prefix')).$location['location_id']."/";
                                 $slug = $location['location_slug'] ? $location['location_slug'] : $location['location_name'];
                                 $slug = untrailingslashit(eme_permalink_convert($slug));
                        ?>
                                 <input type="text" id="slug" name="location_slug" value="<?php echo $slug; ?>" /><?php echo user_trailingslashit(""); ?>
                        <?php
                              }
                        ?>

         </div>
         <div class="form-field">
            <label for="location_address"><?php _e('Location address', 'eme') ?></label>
            <input id="location_address" name="location_address" type="text" value="<?php echo eme_sanitize_html($location['location_address']); ?>" size="40"  />
            <p><?php _e('The address of the location', 'eme') ?>.</p>
         </div>
 
         <div class="form-field ">
            <label for="location_town"><?php _e('Location town', 'eme') ?></label>
            <input name="location_town" id="location_town" type="text" value="<?php echo eme_sanitize_html($location['location_town']); ?>" size="40"  />
            <p><?php _e('The town of the location', 'eme') ?>.</p>
         </div>
                        
         <div class="form-field" style="display:none;">
            <label for="location_latitude">LAT</label>
            <input id="location_latitude" name="location_latitude" type="text" value="<?php echo eme_sanitize_html($location['location_latitude']); ?>" size="40"  />
         </div>
         <div class="form-field" style="display:none;">
            <label for="location_longitude">LONG</label>
            <input id="location_longitude" name="location_longitude" type="text" value="<?php echo eme_sanitize_html($location['location_longitude']); ?>" size="40"  />
         </div>
         <div class="form-field">
            <label for="location_image"><?php _e('Location image', 'eme') ?></label>
            <input id="location_image" name="location_image" type="file" size="35" />
            <p><?php _e('Select an image to upload', 'eme') ?>.</p>
            <?php if (isset($location['location_image_url']) && !empty($location['location_image_url'])) {
                     _e('Current image:', 'eme');
                     echo "<img src='".$location['location_image_url']."' alt='".eme_trans_sanitize_html($location['location_name'])."'/>";
                  }
            ?>
        </div>
 
        <?php if(get_option('eme_categories_enabled')) :?>
        <div>
           <label for="location_category_ids"><?php _e('Category', 'eme') ?></label><br />
           <?php
           $categories = eme_get_categories();
           foreach ( $categories as $category) {
              if ($location['location_category_ids'] && in_array($category['category_id'],explode(",",$location['location_category_ids']))) {
                 $selected = "checked='checked'";
              } else {
                 $selected = "";
              }
           ?>
              <input type="checkbox" name="location_category_ids[]" value="<?php echo $category['category_id']; ?>" <?php echo $selected ?> /><?php echo $category['category_name']; ?><br />
           <?php
           }
           ?>
        </div>
        <?php endif; ?>

         <?php 
            $gmap_is_active = get_option('eme_gmap_is_active');
            if ($gmap_is_active) :
          ?>   
         <div><?php 
               if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
                  _e("Because qtranslate is active, the title of the location will not update automatically in the balloon, so don't panic there.");
               }
              ?>
         </div>
         <div id="eme-admin-map-not-found"><p><?php _e('Map not found','eme') ?></p></div>
         <div id="eme-admin-location-map"</div>
         <br style="clear:both;" />
         <?php endif; ?>
         <div id="loc_description">
            <label><?php _e('Location description', 'eme') ?></label>
            <div class="inside">
               <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
                  <?php the_editor($location['location_description']); ?>
               </div>
               <?php _e('A description of the Location. You may include any kind of info here.', 'eme') ?>
            </div>
         </div>
         <div class="form-field">
            <label for="location_url" ><?php _e ( 'External link', 'eme' ); ?></label>
            <input name="location_url" id="location_url" type="text" value="<?php echo eme_sanitize_html($location['location_url']); ?>" size="40"  />
            <p><?php _e ( 'If this is filled in, the single event URL will point to this url instead of the standard event page.', 'eme' )?></p>
         </div>
         <p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e('Update location', 'eme') ?>" /></p>

         </form>
      </div>
   </div>
   <?php
}

function eme_locations_table_layout($locations, $new_location, $message = "") {
   if (!is_array($new_location)) {
      $new_location = array();
      $new_location['location_name'] = '';
      $new_location['location_address'] = '';
      $new_location['location_town'] = '';
      $new_location['location_latitude'] = '';
      $new_location['location_longitude'] = '';
      $new_location['location_description'] = '';
      $new_location['location_category_ids'] = '';
      $new_location['location_url'] = '';
      $new_location['location_slug'] = '';
   }

   ob_start();
   ?>
      <div class="wrap nosubsub">
       <div id="poststuff">
         <div id="icon-edit" class="icon32">
            <br />
         </div>
         <h2><?php _e('Locations', 'eme') ?></h2>
         <?php admin_show_warnings(); ?>
         
         <?php if($message != "") : ?>
            <div id="message" class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
               <p><?php echo $message ?></p>
            </div>
         <?php endif; ?>
         <div id="col-container">
            <div id="col-right">
             <div class="col-wrap">
                <form id="locations-filter" method="post" action="<?php echo admin_url("admin.php?page=events-manager-locations"); ?>">
                  <input type="hidden" name="action" value="delete"/>
                  <?php if (count($locations)>0) : ?>
                  <table class="widefat">
                     <thead>
                        <tr>
                           <th class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="select-all" value="1"/></th>
                           <th><?php _e('Name', 'eme') ?></th>
                           <th><?php _e('Address', 'eme') ?></th>
                           <th><?php _e('Town', 'eme') ?></th>
                        </tr> 
                     </thead>
                     <tfoot>
                        <tr>
                           <th class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="select-all" value="1"/></th>
                           <th><?php _e('Name', 'eme') ?></th>
                           <th><?php _e('Address', 'eme') ?></th>
                           <th><?php _e('Town', 'eme') ?></th>
                        </tr>
                     </tfoot>
                     <tbody>
                        <?php foreach ($locations as $this_location) : ?>  
                        <tr>
                           <td><input type="checkbox" class ="row-selector" value="<?php echo $this_location['location_id'] ?>" name="locations[]"/></td>
                           <td><a href="<?php echo admin_url("admin.php?page=events-manager-locations&amp;action=edit&amp;location_ID=".$this_location['location_id']); ?>"><?php echo eme_trans_sanitize_html($this_location['location_name']); ?></a></td>
                           <td><?php echo eme_trans_sanitize_html($this_location['location_address']); ?></td>
                           <td><?php echo eme_trans_sanitize_html($this_location['location_town']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                     </tbody>

                  </table>

                  <div class="tablenav">
                     <div class="alignleft actions">
                     <input class="button-secondary action" type="submit" name="doaction" value="Delete"/>
                     <br class="clear"/> 
                     </div>
                     <br class="clear"/>
                  </div>
                  <?php else: ?>
                     <p><?php _e('No venues have been inserted yet!', 'eme') ?></p>
                  <?php endif; ?>
                  </form>
               </div>
            </div>  <!-- end col-right -->
            
            <div id="col-left">
            <div class="col-wrap">
                  <div class="form-wrap"> 
                     <div id="ajax-response"/>
                  <h3><?php _e('Add location', 'eme') ?></h3>
                      <form enctype="multipart/form-data" name="addlocation" id="addlocation" method="post" action="<?php echo admin_url("admin.php?page=events-manager-locations"); ?>" class="add:the-list: validate">
                        <input type="hidden" name="action" value="addlocation" />
                        <div id="titlediv" class="form-field form-required">
                          <label><?php _e('Location name', 'eme') ?></label>
                          <input name="location_name" id="title" type="text" value="<?php echo eme_sanitize_html($new_location['location_name']); ?>" size="40" />
                          <input type="hidden" name="translated_location_name" value="<?php echo eme_trans_sanitize_html($new_location['location_name']); ?>" />
                          <p><?php _e('The name of the location', 'eme') ?>.</p>
                        </div>

                        <div class="form-field">
                           <label for="location_address"><?php _e('Location address', 'eme') ?></label>
                           <input id="location_address" name="location_address" type="text" value="<?php echo eme_sanitize_html($new_location['location_address']); ?>" size="40"  />
                           <p><?php _e('The address of the location', 'eme') ?>.</p>
                        </div>
 
                        <div class="form-field ">
                           <label for="location_town"><?php _e('Location town', 'eme') ?></label>
                           <input id="location_town" name="location_town" type="text" value="<?php echo eme_sanitize_html($new_location['location_town']); ?>" size="40"  />
                           <p><?php _e('The town of the location', 'eme') ?>.</p>
                        </div>
                        
                        <div class="form-field" style="display:none;">
                           <label for="location_latitude">LAT</label>
                           <input id="location_latitude" name="location_latitude" type="text" value="<?php echo eme_sanitize_html($new_location['location_latitude']); ?>" size="40"  />
                        </div>
                        <div class="form-field" style="display:none;">
                           <label for="location_longitude">LONG</label>
                           <input id="location_longitude" name="location_longitude" type="text" value="<?php echo eme_sanitize_html($new_location['location_longitude']); ?>" size="40"  />
                        </div>
                        
                        <div class="form-field">
                           <label for="location_image"><?php _e('Location image', 'eme') ?></label>
                           <input id="location_image" name="location_image" type="file" size="35" />
                            <p><?php _e('Select an image to upload', 'eme') ?>.</p>
                        </div>

                        <?php if(get_option('eme_categories_enabled')) :?>
                           <div>
                           <label for="location_category_ids"><?php _e('Category', 'eme') ?></label><br />
                           <?php
                           $categories = eme_get_categories();
                           foreach ( $categories as $category) {
                              if ($new_location['location_category_ids'] && in_array($category['category_id'],explode(",",$new_location['location_category_ids']))) {
                                 $selected = "checked='checked'";
                              } else {
                                 $selected = "";
                              }
                           ?>
                           <input type="checkbox" name="location_category_ids[]" value="<?php echo $category['category_id']; ?>" <?php echo $selected ?> /><?php echo $category['category_name']; ?><br />
                           <?php
                           }
                           ?>
                           </div>
                        <?php endif; ?>

                        <?php 
                           $gmap_is_active = get_option('eme_gmap_is_active');
                              if ($gmap_is_active) :
                         ?>   
                           <div id="eme-admin-map-not-found"><p><?php _e('Map not found','eme') ?></p></div>
                           <div id="eme-admin-location-map"></div>
                           <br style="clear:both;" />
                         <?php endif; ?>
                           <div id="loc_description">
                              <label><?php _e('Location description', 'eme') ?></label>
                              <div class="inside">
                                 <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
                                    <?php the_editor($new_location['location_description']); ?>
                                 </div>
                                 <?php _e('A description of the Location. You may include any kind of info here.', 'eme') ?>
                              </div>
                           </div>
         <div class="form-field">
            <label for="location_url" ><?php _e ( 'External link', 'eme' ); ?></label>
            <input name="location_url" id="location_url" type="text" value="<?php echo eme_sanitize_html($new_location['location_url']); ?>" size="40"  />
            <p><?php _e ( 'If this is filled in, the single event URL will point to this url instead of the standard event page.', 'eme' )?></p>
         </div>
                         <p class="submit"><input type="submit" class="button" name="submit" value="<?php _e('Add location', 'eme') ?>" /></p>
                      </form>

                 </div>
               </div> 
            </div>  <!-- end col-left -->
         </div> <!-- end col-container -->
      </div> 
   </div>
   <?php
   echo ob_get_clean();
}

function eme_get_locations($eventful = false, $scope="all", $category = '', $offset = 0) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $locations = array();

   // for the query: we don't do "SELECT *" because the data returned from this function is also used in the function eme_global_map_json()
   // and some fields from the events table contain carriage returns, which can't be passed along
   // The function eme_global_map_json tries to remove these, but the data is not needed and better be safe than sorry
   if ($eventful) {
      $events = eme_get_events(0, $scope, "ASC", $offset, "", $category);
      if ($events) {
         foreach ($events as $event) {
            $location_id=$event['location_id'];
            if ($location_id && $event['location_name'] != "") {
               $this_location = array();
               $this_location['location_id'] = $location_id;
               $this_location['location_name'] = $event['location_name'];
               $this_location['location_address'] = $event['location_address'];
               $this_location['location_town'] = $event['location_town'];
               $this_location['location_latitude'] = $event['location_latitude'];
               $this_location['location_longitude'] = $event['location_longitude'];
               $this_location['location_description'] = $event['location_description'];
               $this_location['location_category_ids'] = $event['location_category_ids'];
               $this_location['location_url'] = $event['location_url'];
               $this_location['location_slug'] = $event['location_slug'];
               // the key is based on the location name first and the location id (if different locations have the same name)
               // using this method we can then sort on the name
               $locations[$this_location['location_name'].$location_id]=$this_location;
            }
         }
         // sort on the key (name/id pair)
         ksort($locations);
      }
   } else {
      $conditions = array ();
      if (get_option('eme_categories_enabled')) {
         if (is_numeric($category)) {
            if ($category>0)
               $conditions [] = " FIND_IN_SET($category,location_category_ids)";
         } elseif ($category == "none") {
            $conditions [] = "event_category_ids=''";
         } elseif ( preg_match('/,/', $category) ) {
            $category = explode(',', $category);
            $category_conditions = array();
            foreach ($category as $cat) {
               if (is_numeric($cat) && $cat>0) {
                  $category_conditions[] = " FIND_IN_SET($cat,location_category_ids)";
               } elseif ($cat == "none") {
                  $category_conditions[] = " location_category_ids=''";
               }
            }
            $conditions [] = "(".implode(' OR', $category_conditions).")";
         } elseif ( preg_match('/\+/', $category) ) {
            $category = explode('+', $category);
            $category_conditions = array();
            foreach ($category as $cat) {
               if (is_numeric($cat) && $cat>0)
                  $category_conditions[] = " FIND_IN_SET($cat,location_category_ids)";
            }
            $conditions [] = "(".implode(' AND ', $category_conditions).")";
         }
      }

      // extra conditions for authors: if we're in the admin itf, return only the locations for which you have the right to change anything
      $current_userid=get_current_user_id();
      if (is_admin() && !current_user_can( get_option('eme_cap_edit_locations')) && current_user_can( get_option('eme_cap_author_locations'))) {
         $conditions [] = "(location_author = $current_userid)";
      }

      $where = implode ( " AND ", $conditions );
      if ($where != "")
         $where = " AND " . $where;

      $sql = "SELECT * FROM $locations_table WHERE location_name != '' $where ORDER BY location_name";
      $locations = $wpdb->get_results($sql, ARRAY_A); 
   }
   if (has_filter('eme_location_list_filter')) $locations=apply_filters('eme_location_list_filter',$locations);
   return $locations;
}

function eme_get_location($location_id=0) { 
   global $wpdb;

   $location=array();
   if (!$location_id) {
      $location ['location_id']='';
      $location ['location_name']='';
      $location ['location_address']='';
      $location ['location_town']='';
      $location ['location_latitude']='';
      $location ['location_longitude']='';
      $location ['location_image_url']='';
      $location ['location_url']='';
      $location ['location_slug']='';
   } else {
      $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
      $sql = "SELECT * FROM $locations_table WHERE location_id ='$location_id'";
      $location = $wpdb->get_row($sql, ARRAY_A);
      $location['location_image_url'] = eme_image_url_for_location_id($location['location_id']);
   }

   if (has_filter('eme_location_filter')) $location=apply_filters('eme_location_filter',$location);
   return $location;
}

function eme_get_town_location_ids($towns) {
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   $location_ids = array();
   if ( is_array($towns) ) {
      $town_conditions = array();
      foreach ($towns as $town) {
         $town_conditions[] = " location_town = '$town'";
      }
      $conditions = "(".implode(' OR', $town_conditions).")";
   } elseif (!empty($towns)) {
      $conditions = " location_town = '$towns'";
   }
   if (!empty($conditions)) {
      $sql = "SELECT DISTINCT location_id FROM $locations_table WHERE ".$conditions;
      $location_ids = $wpdb->get_col($sql); 
   }
   return $location_ids;
}

function eme_image_url_for_location_id($location_id) {
   $file_name= IMAGE_UPLOAD_DIR."/location-".$location_id;
   $mime_types = array('gif','jpg','png');foreach($mime_types as $type) { 
      $file_path = "$file_name.$type";
      if (file_exists($file_path)) {
         $result = IMAGE_UPLOAD_URL."/location-$location_id.$type";
         return $result;
      }
   }
   return '';
}

function eme_get_identical_location($location) { 
   global $wpdb;
   $locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
   //$sql = "SELECT * FROM $locations_table WHERE location_name ='".$location['location_name']."' AND location_address ='".$location['location_address']."' AND location_town ='".$location['location_town']."';";
  $prepared_sql=$wpdb->prepare("SELECT * FROM $locations_table WHERE location_name = %s AND location_address = %s AND location_town = %s", stripcslashes($location['location_name']), stripcslashes($location['location_address']), stripcslashes($location['location_town']) );
   $cached_location = $wpdb->get_row($prepared_sql, ARRAY_A);
   return $cached_location;
}

function eme_validate_location($location) {
   global $location_required_fields;
   $troubles = "";
   foreach ($location_required_fields as $field => $description) {
      if ($location[$field] == "" ) {
         $troubles .= "<li>".$description.__(" is missing!", "eme")."</li>";
      }
   }
   if ($_FILES['location_image']['size'] > 0 ) { 
      if (is_uploaded_file($_FILES['location_image']['tmp_name'])) {
         $mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
         $maximum_size = get_option('eme_image_max_size'); 
         if ($_FILES['location_image']['size'] > $maximum_size) 
               $troubles = "<li>".__('The image file is too big! Maximum size:', 'eme')." $maximum_size</li>";
         list($width, $height, $type, $attr) = getimagesize($_FILES['location_image']['tmp_name']);
         $maximum_width = get_option('eme_image_max_width'); 
         $maximum_height = get_option('eme_image_max_height'); 
         if (($width > $maximum_width) || ($height > $maximum_height)) 
               $troubles .= "<li>". __('The image is too big! Maximum size allowed:')." $maximum_width x $maximum_height</li>";
         if (($type!=1) && ($type!=2) && ($type!=3)) 
                  $troubles .= "<li>".__('The image is in a wrong format!')."</li>";
      } 
   }

   if ($troubles == "") {
      return "OK";
   } else {
      $message = __('Ach, some problems here:', 'eme')."<ul>\n$troubles</ul>";
      return $message; 
   }
}

function eme_update_location($location) {
   global $wpdb;
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME;
   $where ['location_id'] = $location['location_id'];
   // we can't check the return code for wpdb->update,
   // since sometimes the update returns 0 because of no rows
   // updated (eg, when you just add an image)
   // DONE: add modif timestamps, so that changes for each update,
   // and then we can check for the return code again (as for events)
   $location['location_modif_date']=current_time('mysql', false);
   $location['location_modif_date_gmt']=current_time('mysql', true);
   if (!$wpdb->update ( $table_name, $location, $where )) {
      $wpdb->print_error();
      return false;
   } else {
      return true;
   }
}

function eme_insert_location($location) {
   global $wpdb;  
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME; 
   // if GMap is off the hidden fields are empty, so I add a custom value to make the query work
   if (empty($location['location_longitude']))
      $location['location_longitude'] = 0;
   if (empty($location['location_latitude']))
      $location['location_latitude'] = 0;

   $location['location_creation_date']=current_time('mysql', false);
   $location['location_modif_date']=current_time('mysql', false);
   $location['location_creation_date_gmt']=current_time('mysql', true);
   $location['location_modif_date_gmt']=current_time('mysql', true);

   $wpdb->show_errors(true);
   if (!$wpdb->insert($table_name,$location)) {
      $wpdb->print_error();
      return false;
   } else {
      $location_ID = $wpdb->insert_id;
      $new_location = eme_get_location($location_ID);
      return $new_location;
   }
}

function eme_delete_location($location) {
   global $wpdb;  
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME;
   $sql = "DELETE FROM $table_name WHERE location_id = '$location';";
   $wpdb->query($sql);
   eme_delete_image_files_for_location_id($location);
}

function eme_location_has_events($location_id) {
   global $wpdb;  
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $condition = "AND event_status IN (".STATUS_PUBLIC.",".STATUS_PRIVATE.")";
      } else {
         $condition = "AND event_status=".STATUS_PUBLIC;
      }
   }

   $sql = "SELECT COUNT(event_id) FROM $events_table WHERE location_id = $location_id $condition";
   $affected_events = $wpdb->get_results($sql);
   return ($affected_events > 0);
}

function eme_upload_location_picture($location) {
   if(!file_exists(IMAGE_UPLOAD_DIR))
            mkdir(IMAGE_UPLOAD_DIR, 0777);
   eme_delete_image_files_for_location_id($location['location_id']);
   $mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
   list($width, $height, $type, $attr) = getimagesize($_FILES['location_image']['tmp_name']);
   $image_path = IMAGE_UPLOAD_DIR."/location-".$location['location_id'].".".$mime_types[$type];
   if (!move_uploaded_file($_FILES['location_image']['tmp_name'], $image_path)) 
      $msg = "<p>".__('The image could not be loaded','eme')."</p>";
}

function eme_delete_image_files_for_location_id($location_id) {
   $file_name= IMAGE_UPLOAD_DIR."/location-".$location_id;
   $mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
   foreach($mime_types as $type) { 
      if (file_exists($file_name.".".$type))
      unlink($file_name.".".$type);
   }
}

function eme_global_map($atts) {
   global $eme_need_gmap_js;

   if (get_option('eme_gmap_is_active') == '1') {
      // the locations shortcode has been deteced, so we indicate
      // that we want the javascript in the footer as well
      $eme_need_gmap_js=1;
      extract(shortcode_atts(array(
                  'eventful' => false,
                  'scope' => 'all',
                  'paging' => 0,
                  'category' => '',
                  'width' => 450,
                  'height' => 300,
                  'list_location' => 'after'
                  ), $atts));
      $eventful = (bool) $eventful;
      $events_page_link = eme_get_events_page(true, false);
      if (stristr($events_page_link, "?"))
         $joiner = "&amp;";
      else
         $joiner = "?";

      $prev_text = "";
      $next_text = "";
      // for browsing: if paging=1 and only for this_week,this_month or today
      if ($paging==1) {
         $scope_offset=0;
         if (isset($_GET['eme_offset']))
            $scope_offset=$_GET['eme_offset'];
         $prev_offset=$scope_offset-1;
         $next_offset=$scope_offset+1;
         if ($scope=="this_week") {
            $day_offset=date('w');
            $start_day=time()-$day_offset*86400;
            $end_day=$start_day+6*86400;
            $limit_start = date('Y-m-d',$start_day+$scope_offset*7*86400);
            $limit_end   = date('Y-m-d',$end_day+$scope_offset*7*86400);
            $scope = "$limit_start--$limit_end";
            $scope_text = date_i18n (get_option('date_format'),$start_day+$scope_offset*7*86400)."--".date_i18n (get_option('date_format'),$end_day+$scope_offset*7*86400);
            $prev_text = __('Previous week','eme');
            $next_text = __('Next week','eme');
         }
         elseif ($scope=="this_month") {
            // "first day of this month, last day of this month" works for newer versions of php (5.3+), but for compatibility:
            // the year/month should be based on the first of the month, so if we are the 13th, we substract 12 days to get to day 1
            // Reason: monthly offsets needs to be calculated based on the first day of the current month, not the current day,
            //    otherwise if we're now on the 31st we'll skip next month since it has only 30 days
            $day_offset=date('j')-1;
            $year=date('Y', strtotime("$scope_offset month")-$day_offset*86400);
            $month=date('m', strtotime("$scope_offset month")-$day_offset*86400);
            $number_of_days_month=eme_days_in_month($month,$year);
            $limit_start = "$year-$month-01";
            $limit_end   = "$year-$month-$number_of_days_month";
            $scope = "$limit_start--$limit_end";
            //$prev_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$prev_offset month")-$day_offset*86400);
            //$next_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$next_offset month")-$day_offset*86400);
            $scope_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$scope_offset month")-$day_offset*86400);
            $prev_text = __('Previous month','eme');
            $next_text = __('Next month','eme');
         }
         elseif ($scope=="this_year") {
            $year=date('Y', strtotime("$scope_offset year")-$day_offset*86400);
            $limit_start = "$year-01-01";
            $limit_end   = "$year-12-31";
            $scope = "$limit_start--$limit_end";
            $scope_text = date_i18n (get_option('eme_show_period_yearly_dateformat'), strtotime("$scope_offset year")-$day_offset*86400);
            $prev_text = __('Previous year','eme');
            $next_text = __('Next year','eme');
         }
         elseif ($scope=="today") {
            $scope = date('Y-m-d',strtotime("$scope_offset days"));
            $limit_start = $scope;
            $limit_end   = $scope;
            //$prev_text = date_i18n (get_option('date_format'), strtotime("$prev_offset days"));
            //$next_text = date_i18n (get_option('date_format'), strtotime("$next_offset days"));
            $scope_text = date_i18n (get_option('date_format'), strtotime("$scope_offset days"));
            $prev_text = __('Previous day','eme');
            $next_text = __('Next day','eme');
         }
         elseif ($scope=="tomorrow") {
            $scope_offset++;
            $scope = date('Y-m-d',strtotime("$scope_offset days"));
            $limit_start = $scope;
            $limit_end   = $scope;
            $scope_text = date_i18n (get_option('date_format'), strtotime("$scope_offset days"));
            $prev_text = __('Previous day','eme');
            $next_text = __('Next day','eme');
         }

         // to prevent going on indefinitely and thus allowing search bots to go on for ever,
         // we stop providing links if there are no more events left
         if (eme_count_events_older_than($limit_start) == 0)
            $prev_text = "";
         if (eme_count_events_newer_than($limit_end) == 0)
            $next_text = "";
      }

      $result = "<div id='eme_global_map' style='width: {$width}px; height: {$height}px'>map</div>";
      // get the paging output ready
      if ($paging==1) {
         $pagination_top = "<div id='locations-pagination-top'> ";
         $this_page_url=$_SERVER['REQUEST_URI'];
         // remove the offset info
         $this_page_url= preg_replace("/\&eme_offset=-?\d+/","",$this_page_url);
         $this_page_url= preg_replace("/\?eme_offset=-?\d+$/","",$this_page_url);
         $this_page_url= preg_replace("/\?eme_offset=-?\d+\&(.*)/","?$1",$this_page_url);
         if (stristr($this_page_url, "?"))
            $joiner = "&amp;";
         else
            $joiner = "?";
         if ($prev_text != "")
            $pagination_top.= "<a class='eme_nav_left' href='" . $this_page_url.$joiner."eme_offset=$prev_offset'>&lt;&lt; $prev_text</a>";
         if ($next_text != "")
            $pagination_top.= "<a class='eme_nav_right' href='" . $this_page_url.$joiner."eme_offset=$next_offset'>$next_text &gt;&gt;</a>";
         $pagination_top.= "<span class='eme_nav_center'>$scope_text</span>";
         $pagination_top.= "</div>";
         $pagination_bottom = str_replace("locations-pagination-top","locations-pagination-bottom",$pagination_top);
         $result = $pagination_top.$result.$pagination_bottom;
      }

      $result .= "<script type='text/javascript'>
         <!--// 
      eventful = '$eventful';
      scope = '$scope';
      category = '$category';
      events_page_link = '$events_page_link';
      joiner = '$joiner'
         //-->
         </script>";
      //$result .= "<script src='".EME_PLUGIN_URL."eme_global_map.js' type='text/javascript'></script>";

      // we add the list if wanted (only for "before" or "after")
      if ($list_location=="before") {
         $locations = eme_get_locations((bool) $eventful,$scope,$category,0);
         $loc_list = "<ol id='eme_locations_list'>"; 
         foreach($locations as $location) {
            $loc_list.="<li id='location-". $location['location_id'].
                              "' style='list-style-type: upper-alpha'><a >".
                              $location['location_name']."</a></li>";
         }
         $loc_list .= "</ol>"; 
         $result = $loc_list.$result;
      } elseif ($list_location=="after") {
         $locations = eme_get_locations((bool) $eventful,$scope,$category,0);
         $loc_list = "<ol id='eme_locations_list'>"; 
         foreach($locations as $location) {
            $loc_list.="<li id='location-". $location['location_id'].
                              "' style='list-style-type: upper-alpha'><a >".
                              $location['location_name']."</a></li>";
         }
         $loc_list .= "</ol>"; 
         $result .= $loc_list;
      }
   } else {
      $result = "";
   }
   return $result;
}
add_shortcode('locations_map', 'eme_global_map'); 

function eme_display_single_location_shortcode($atts){
   extract ( shortcode_atts ( array ('id'=>''), $atts ) );
   $location=eme_get_location($id);
   $map_div = eme_single_location_map($location);
   return $map_div;
}
add_shortcode('display_single_location', 'eme_display_single_location_shortcode');

function get_locations_shortcode($atts) {
   global $wpdb, $jquery_override_lang;
   extract(shortcode_atts(array(
      'eventful'  => false,
      'category'  => '',
      'scope'     => 'all',
      'offset'    => 0,
      'link'      => false,
      'class'     => ''
   ), $atts));
   $class = $class ? "class=\"{$class}\"" : "";
   $locations = eme_get_locations($eventful, $scope, $category, $offset);

   $out = "<ul id=\"eme_locations\" {$class}>";
   //if (!$link)
   //   $out .= "<li class=\"location-0\">All</li>";
   foreach ($locations as $location) {
      $location_name = $location['location_name'];
      if ($link) {
         $location_page_link = eme_location_url($location);
         $location_name = "<a href=\"{$location_page_link}\" title=\"{$location_name}\">{$location_name}</a>";  
      }
      $out .= "<li class=\"location-{$location['location_id']}\">{$location_name}</li>";
   }
   $out .= "</ul>";
   $out .= <<<EOD
      <script type="text/javascript">
      //<![CDATA[
      var \$j_eme_loc_cal=jQuery.noConflict();

      \$j_eme_loc_cal(document).ready(function() {
         \$j_eme_loc_cal('#eme_locations.calendar li').each(function(){
               \$j_eme_loc_cal(this).click(function(){
                  location_id = \$j_eme_loc_cal(this).attr('class').replace('location-','');
                  \$j_eme_loc_cal('.location_chosen').text(location_id);
                  prev_month_link = \$j_eme_loc_cal('.prev-month:first');
                  tableDiv = \$j_eme_loc_cal(prev_month_link).closest('table').parent();
                  (\$j_eme_loc_cal(prev_month_link).hasClass('full-link')) ?
                     fullcalendar = 1 : fullcalendar = 0;
                  (\$j_eme_loc_cal(prev_month_link).hasClass('long_events')) ?
                     long_events = 1 : long_events = 0;
                  loadCalendar(tableDiv, fullcalendar, long_events);
               } );
         } );
      });   
      //]]> 

      </script>
EOD;
   return $out;
}
add_shortcode('events_locations','get_locations_shortcode');

function eme_replace_locations_placeholders($format, $location, $target="html", $do_shortcode=1) {

   preg_match_all("/#(ESC|URL)?@?_?[A-Za-z0-9_\[\]]+/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'sort_stringlenth');

   foreach($placeholders[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      $found=1;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";

      // echo "RESULT: $result <br>";
      // matches alla fields placeholder
      if (preg_match('/#_MAP$/', $result)) {
         $replacement = eme_single_location_map($location);

      } elseif (preg_match('/#_PASTEVENTS$/', $result)) {
         $replacement = eme_events_in_location_list($location, "past");

      } elseif (preg_match('/#_NEXTEVENTS$/', $result)) {
         $replacement = eme_events_in_location_list($location);

      } elseif (preg_match('/#_ALLEVENTS$/', $result)) {
         $replacement = eme_events_in_location_list($location, "all");

      } elseif (preg_match('/#_(ADDRESS|TOWN)$/', $result)) {
         $field = "location_".ltrim(strtolower($result), "#_");
         if (isset($location[$field]))
            $replacement = $location[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 

      } elseif (preg_match('/#_(NAME|LOCATIONNAME|LOCATION)$/', $result)) {
         $field = "location_name";
         if (isset($location[$field]))
            $replacement = $location[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html")
            $replacement = apply_filters('eme_general', $replacement); 
         else 
            $replacement = apply_filters('eme_general_rss', $replacement); 

      } elseif (preg_match('/#_LOCATIONID$/', $result)) {
         $field = "location_id";
         $replacement = $location[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement);
         }

      } elseif (preg_match('/#_(IMAGE|LOCATIONIMAGE)$/', $result)) {
         if($location['location_image_url'] != '')
            $replacement = "<img src='".$location['location_image_url']."' alt='".eme_trans_sanitize_html($location['location_name'])."'/>";

      } elseif (preg_match('/#_(IMAGEURL|LOCATIONIMAGEURL)$/', $result)) {
         if($location['location_image_url'] != '')
            $replacement = $location['location_image_url'];

      } elseif (preg_match('/#_LOCATIONPAGEURL$/', $result)) {
         $replacement = eme_location_url($location);

      } elseif (preg_match('/#_LATITUDE$/', $result)) {
         $field = "location_latitude";
         $replacement = $location[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement);
         }

      } elseif (preg_match('/#_LONGITUDE$/', $result)) {
         $field = "location_longitude";
         $replacement = $location[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement);
         }

      } elseif (preg_match('/#_DIRECTIONS/', $result)) {
         $replacement = eme_add_directions_form($location);

      } elseif (preg_match('/^#_CATEGORIES|#_LOCATIONCATEGORIES$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_location_categories($location['location_id']);
         $replacement = eme_trans_sanitize_html(join(", ",$categories));
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement);
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement);
         }

      } elseif (preg_match('/#_IS_SINGLE_LOC/', $result)) {
         if (eme_is_single_location_page())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_LOGGED_IN/', $result)) {
         if (is_user_logged_in())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_ADMIN_PAGE/', $result)) {
         if (is_admin())
            $replacement = 1;
         else
            $replacement = 0;

      } else {
         $found = 0;
      }

      if ($need_escape) {
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      } elseif ($need_urlencode) {
         $replacement = rawurlencode($replacement);
      }
      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );
   }

   # we handle DESCRIPTION the last, so no placeholder replacement happens accidentaly in the text of #_DESCRIPTION
   if (preg_match('/#_DESCRIPTION|#_LOCATIONDETAILS/', $format, $placeholders)) {
      $result=$placeholders[0];
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

      $field = "location_description";
      if (isset($location[$field]))
         $replacement = $location[$field];

      // no real sanitizing needed, but possible translation
      // this is the same as for an event in fact
      $replacement = eme_translate($replacement);
      if ($target == "html")
         $replacement = apply_filters('eme_notes', $replacement);
      else
         if ($target == "map")
            $replacement = apply_filters('eme_notes_map', $replacement);
         else
            $replacement = apply_filters('eme_notes_rss', $replacement);
      if ($need_escape) {
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      } elseif ($need_urlencode) {
         $replacement = rawurlencode($replacement);
      }
      $format = str_replace($orig_result, $replacement ,$format );
   }

   if ($do_shortcode)
      return do_shortcode($format);   
   else
      return $format;   
}

function eme_add_directions_form($location) {
   $locale_code = substr ( get_locale (), 0, 2 );
   $res = "";
   if (isset($location['location_address']) && isset($location['location_town'])) {
      $res .= '<form action="http://maps.google.com/maps" method="get" target="_blank" style="text-align:left;">';
      $res .= '<div><label for="saddr">'.__('Your Street Address','eme').'</label><br />';
      $res .= '<input type="text" name="saddr" id="saddr" value="" />';
      $res .= '<input type="hidden" name="daddr" value="'.$location['location_address'].', '.$location['location_town'].'" />';
      $res .= '<input type="hidden" name="hl" value="'.$locale_code.'" /></div>';
      $res .= '<input type="submit" value="'.__('Get Directions','eme').'" />';
      $res .= '</form>';
   }

   # some people might want to change the form to their liking
   if (has_filter('eme_directions_form_filter')) $res=apply_filters('eme_directions_form_filter',$res);

   return $res;
}

function eme_single_location_map($location) {
   global $eme_need_gmap_js;

   $gmap_is_active = get_option('eme_gmap_is_active'); 
   $map_text = addslashes(eme_replace_locations_placeholders(get_option('eme_location_baloon_format'), $location));
   $map_text = preg_replace("/\r\n|\n\r|\n/","<br />",$map_text);
   // if gmap is not active: we don't show the map
   // if the location name is empty: we don't show the map
   if ($gmap_is_active && !empty($location['location_name']) && !empty($location['location_address']) && !empty($location['location_town'])) {
      $eme_need_gmap_js=1;
      //$id_base = $location['location_id'];
      // we can't create a unique <div>-id based on location id alone, because you can have multiple maps on the sampe page for
      // different events but they can go to the same location...
      // So we also use the event_id (if present) and the microtime for this, and replace all non digits by underscore (otherwise the generated javascript will error)
      $id_base = preg_replace("/\D/","_",microtime(1));
      // the next is only possible when called from within an event (events-manager.php)
      if (isset($location['event_id'])) {
         $id_base = $location['event_id']."_".$id_base;
      }
      $id="eme-location-map_".$id_base;
      $latitude_string="latitude_".$id_base;
      $longitude_string="longitude_".$id_base;
      $map_text_string="map_text_".$id_base;
      $enable_zooming_string="enable_zooming_".$id_base;
      $enable_zooming=get_option('eme_gmap_zooming') ? 'true' : 'false';
      #$latitude_string="latitude";
      #$longitude_string="longitude";
         //$map_div = "<div id='$id' style=' background: green; width: 400px; height: 300px'></div>" ;
         $map_div = "<div id='$id' class=\"eme-location-map\"></div>" ;
         $map_div .= "<script type='text/javascript'>
         <!--// 
      $latitude_string = parseFloat('".$location['location_latitude']."');
      $longitude_string = parseFloat('".$location['location_longitude']."');
      $map_text_string = '$map_text';
      $enable_zooming_string = '$enable_zooming';
      //-->
      </script>";
      // $map_div .= "<script src='".EME_PLUGIN_URL."eme_single_location_map.js' type='text/javascript'></script>";
   } else {
      $map_div = "";
   }
   return $map_div;
}

function eme_events_in_location_list($location, $scope = "") {
   $eme_event_list_number_events=get_option('eme_event_list_number_items' );
   $events = eme_get_events($eme_event_list_number_events,$scope,"","",$location['location_id']);
   $list = "";
   if (count($events) > 0) {
      foreach($events as $event)
         $list .= eme_replace_placeholders(get_option('eme_location_event_list_item_format'), $event);
   } else {
      $list = get_option('eme_location_no_events_message');
   }
   return $list;
}

add_action ('admin_head', 'eme_locations_autocomplete');

function eme_locations_autocomplete() {
        $use_select_for_locations = get_option('eme_use_select_for_locations');
   // qtranslate there? Then we need the select
   if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
      $use_select_for_locations=1;
   }

   if ((isset($_REQUEST['action']) && ($_REQUEST['action'] == 'edit_event' || $_REQUEST['action'] == 'edit_recurrence')) || (isset($_GET['page']) && $_GET['page'] == 'events-manager-new_event')) {
      ?>
      <link rel="stylesheet" href="<?php echo EME_PLUGIN_URL; ?>js/jquery-autocomplete/jquery.autocomplete.css" type="text/css"/>

      <script src="<?php echo EME_PLUGIN_URL; ?>js/jquery-autocomplete/lib/jquery.bgiframe.min.js" type="text/javascript"></script>
      <script src="<?php echo EME_PLUGIN_URL; ?>js/jquery-autocomplete/lib/jquery.ajaxQueue.js" type="text/javascript"></script> 
      <script src="<?php echo EME_PLUGIN_URL; ?>js/jquery-autocomplete/jquery.autocomplete.min.js" type="text/javascript"></script>

      <script type="text/javascript">
      //<![CDATA[
      $j_eme_loc=jQuery.noConflict();

      $j_eme_loc(document).ready(function() {
         function htmlDecode(value){ 
            return $j_eme_loc('<div/>').html(value).text(); 
         }

         var gmap_enabled = <?php echo get_option('eme_gmap_is_active'); ?>; 

         <?php if(!$use_select_for_locations) :?>
         $j_eme_loc("input#location_name").autocomplete("<?php echo EME_PLUGIN_URL; ?>locations-search.php", {
            width: 260,
            selectFirst: false,
            formatItem: function(row) {
               item = eval("(" + row + ")");
               return htmlDecode(item.name)+'<br /><small>'+htmlDecode(item.address)+' - '+htmlDecode(item.town)+ '</small>';
            },
            formatResult: function(row) {
               item = eval("(" + row + ")");
               return htmlDecode(item.name);
            } 
         });
         $j_eme_loc('input#location_name').result(function(event,data,formatted) {
            item = eval("(" + data + ")"); 
            $j_eme_loc('input#location_address').val(item.address);
            $j_eme_loc('input#location_town').val(item.town);
            if(gmap_enabled) {
               eventLocation = $j_eme_loc("input#location_name").val(); 
               eventTown = $j_eme_loc("input#location_town").val(); 
               eventAddress = $j_eme_loc("input#location_address").val();
               loadMap(eventLocation, eventTown, eventAddress)
            } 
         });
         <?php else : ?>
         $j_eme_loc('#location-select-id').change(function() {
            $j_eme_loc.getJSON("<?php echo EME_PLUGIN_URL; ?>locations-search.php",{id: $j_eme_loc(this).val()}, function(data){
               eventLocation = data.name;
               eventAddress = data.address;
               eventTown = data.town;
               $j_eme_loc("input[name='location-select-name']").val(eventLocation);
               $j_eme_loc("input[name='location-select-address']").val(eventAddress); 
               $j_eme_loc("input[name='location-select-town']").val(eventTown); 
               loadMap(eventLocation, eventTown, eventAddress)
               })
         });
         <?php endif; ?>
      });   
      //]]> 

      </script>

      <?php

   }
}
