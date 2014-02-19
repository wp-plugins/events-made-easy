<?php
function eme_people_page() {
   $message="";
   // Managing AJAX booking removal
   if (!current_user_can( get_option('eme_cap_people')) && isset($_REQUEST['action'])) {
      $message = __('You have no right to update people!','eme');
   } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'remove_booking') {
      if(isset($_REQUEST['booking_id']))
         eme_delete_booking(intval($_REQUEST['booking_id']));
   } elseif (isset ($_REQUEST['persons']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_people') {
         $persons = $_REQUEST['persons'];
         if(is_array($persons)){
            //Make sure the array is only numbers
            foreach ($persons as $person_id) {
               if (is_numeric($person_id)) {
                  $person=eme_get_person($person_id);
                  if (isset($_REQUEST['delete_assoc_bookings'])) {
                     $res=eme_delete_all_bookings_for_person_id($person_id);
                     if ($res) {
                        $message.=__("Deleted all bookings made by '".$person['person_name']."'", 'eme');
                        $message.="<br>";
                     }
                  }
                  $res=eme_delete_person($person_id);
                  if ($res) {
                     $message.=__("Deleted '".$person['person_name']."'", 'eme');
                     $message.="<br>";
                  }
               }
            }
         } else {
               $validation_result = false;
               $message = __("Couldn't delete the people. Please try again.","eme");
         }
   }
   ?>
   
   <div class='wrap'> 
   <div id="icon-users" class="icon32"><br /></div>
   <h2>People</h2>
   <?php admin_show_warnings(); eme_people_table($message); ?>
   </div> 

   <?php
}

add_action('init','eme_ajax_actions'); 
function eme_ajax_actions() {
   if (isset($_GET['eme_ajax_action']) && $_GET['eme_ajax_action'] == 'booking_data') {
      if (isset($_GET['event_id'])) {
         echo "[ {bookedSeats:".eme_get_booked_seats(intval($_GET['event_id'])).", availableSeats:".eme_get_available_seats(intval($_GET['event_id']))."}]"; 
      }
      die();
   }
   if (isset($_POST['eme_ajax_action']) && $_POST['eme_ajax_action'] == 'client_clock_submit') {
      eme_client_clock_callback();
      exit();
   }
   if (isset($_GET['action']) && $_GET['action'] == 'booking_printable') {
      if (is_admin() && isset($_GET['event_id']))
         eme_printable_booking_report(intval($_GET['event_id']));
   }
   if (isset($_GET['action']) && $_GET['action'] == 'booking_csv') {
      if (is_admin() && isset($_GET['event_id']))
         eme_csv_booking_report(intval($_GET['event_id']));
   }
   if (isset($_GET['query']) && $_GET['query'] == 'GlobalMapData') { 
      $eventful = isset($_GET['eventful'])?$_GET['eventful']:false;
      $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
      $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;

      eme_global_map_json((bool)$eventful,$_GET['scope'],$_GET['category']);
      die();
   }
}

function eme_global_map_json($eventful = false, $scope = "all", $category = '', $offset = 0) {
   $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
   $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;

   $locations = eme_get_locations((bool)$eventful,$scope,$category,$offset);
   $json_locations = array();
   foreach($locations as $location) {
      $json_location = array();

      # first we set the balloon info
      $tmp_loc=eme_replace_locations_placeholders(get_option('eme_location_baloon_format'), $location);
      # no newlines allowed, otherwise no map is shown
      $tmp_loc=preg_replace("/\r\n|\n\r|\n/","<br />",$tmp_loc);
      $json_location[] = '"location_balloon":"'.eme_trans_sanitize_html($tmp_loc).'"';

      # second, we fill in the rest of the info
      foreach($location as $key => $value) {
         # we skip some keys, since json is limited in size we only return what's needed in the javascript
         if (preg_match('/location_balloon|location_id|location_latitude|location_longitude/', $key)) {
            # no newlines allowed, otherwise no map is shown
            $value=preg_replace("/\r\n|\n\r|\n/","<br />",$value);
            $json_location[] = '"'.$key.'":"'.eme_trans_sanitize_html($value).'"';
         }
      }
      $json_locations[] = "{".implode(",",$json_location)."}";
   }

   $zoom_factor=get_option('eme_global_zoom_factor');
   $maptype=get_option('eme_global_maptype');
   if ($zoom_factor >14) $zoom_factor=14;

   $json = '{"locations":[';
   $json .= implode(",", $json_locations); 
   $json .= '],"enable_zooming":"';
   $json .= get_option('eme_gmap_zooming') ? 'true' : 'false';
   $json .= '","zoom_factor":"' ;
   $json .= $zoom_factor;
   $json .= '","maptype":"' ;
   $json .= $maptype;
   $json .= '"}' ;
   echo $json;
}

function fputcsv2 ($fh, $fields, $delimiter = ',', $enclosure = '"', $mysql_null = false) {
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
        if ($field === null && $mysql_null) {
            $output[] = 'NULL';
            continue;
        }

        $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s|\r|\t|\n)/", $field) ? (
            $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
        ) : $field;
    }

    fwrite($fh, join($delimiter, $output) . "\n");
}
function eme_csv_booking_report($event_id) {
   $event = eme_get_event($event_id);
   $is_multiprice = eme_is_multi($event['price']);
   $current_userid=get_current_user_id();
   if (!(current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
        (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid)))) {
        echo "No access";
        die;
   }

   header("Content-type: application/octet-stream");
   header("Content-Disposition: attachment; filename=\"export.csv\"");
   $bookings =  eme_get_bookings_for($event_id);
   $answer_columns = eme_get_answercolumns(eme_get_bookingids_for($event_id));
   $out = fopen('php://output', 'w');
   $line=array();
   $line[]=__('Name', 'eme');
   $line[]=__('E-mail', 'eme');
   $line[]=__('Phone number', 'eme');
   if ($is_multiprice)
      $line[]=__('Seats (Multiprice)', 'eme');
   else
      $line[]=__('Seats', 'eme');
   $line[]=__('Paid', 'eme');
   $line[]=__('Comment', 'eme');
   foreach($answer_columns as $col) {
      $line[]=$col['field_name'];
   }
   fputcsv2($out,$line);
   foreach($bookings as $booking) {
      $person = eme_get_person ($booking['person_id']);
      $line=array();
      $pending_string="";
      if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
         $pending_string=__('(pending)','eme');
      }
      $line[]=$person['person_name'];
      $line[]=$person['person_email'];
      $line[]=$person['person_phone'];
      if ($is_multiprice) {
         // in cases where the event switched to multiprice, but somebody already registered while it was still single price: booking_seats_mp is then empty
         if ($booking['booking_seats_mp'] == "")
            $booking['booking_seats_mp']=$booking['booking_seats'];
         $line[]=$booking['booking_seats']." (".$booking['booking_seats_mp'].") ".$pending_string;
      } else {
         $line[]=$booking['booking_seats']." ".$pending_string;
      }
      $line[]=$booking['booking_payed']? __('Yes'): __('No');
      $line[]=$booking['booking_comment'];
      $answers = eme_get_answers($booking['booking_id']);
      foreach($answer_columns as $col) {
         $found=0;
         foreach ($answers as $answer) {
            if ($answer['field_name'] == $col['field_name']) {
               $line[]=$answer['answer'];
               $found=1;
               break;
            }
         }
         # to make sure the number of columns are correct, we add an empty answer if none was found
         if (!$found)
            $line[]="";
      }
      fputcsv2($out,$line);
   }
   fclose($out);
   die();
}

function eme_printable_booking_report($event_id) {
   $event = eme_get_event($event_id);
   $current_userid=get_current_user_id();
   if (!(current_user_can( get_option('eme_cap_edit_events')) || current_user_can( get_option('eme_cap_list_events')) ||
        (current_user_can( get_option('eme_cap_author_event')) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid)))) {
        echo "No access";
        die;
   }

   $is_multiprice = eme_is_multi($event['price']);
   $is_multiseat = eme_is_multi($event['event_seats']);
   $bookings = eme_get_bookings_for($event_id);
   $answer_columns = eme_get_answercolumns(eme_get_bookingids_for($event_id));
   $available_seats = eme_get_available_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   $pending_seats = eme_get_pending_seats($event_id);
   if ($is_multiseat) {
      $available_seats_ms=join('||',eme_get_available_multiseats($event_id));
      $booked_seats_ms=join('||',eme_get_booked_multiseats($event_id));
      $pending_seats_ms=join('||',eme_get_pending_multiseats($event_id));
   }

   $stylesheet = EME_PLUGIN_URL."events_manager.css";
   foreach($answer_columns as $col) {
      $formfield[$col["field_name"]]=eme_get_formfield_id_byname($col["field_name"]);
   }
   ?>
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html>
      <head>
         <meta http-equiv="Content-type" content="text/html; charset=utf-8">
         <title>Bookings for <?php echo eme_trans_sanitize_html($event['event_name']);?></title>
          <link rel="stylesheet" href="<?php echo $stylesheet; ?>" type="text/css" media="screen" />
          <?php
            $file_name= get_stylesheet_directory()."/eme.css";
            if (file_exists($file_name))
               echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme.css' type='text/css' media='screen' />\n";
            $file_name= get_stylesheet_directory()."/eme_print.css";
            if (file_exists($file_name))
               echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme_print.css' type='text/css' media='print' />\n";
          ?>
      </head>
      <body id="printable">
         <div id="container">
         <h1>Bookings for <?php echo eme_trans_sanitize_html($event['event_name']);?></h1> 
         <p><?php echo eme_localised_date($event['event_start_date']); ?></p>
         <p><?php if ($event['location_id']) echo eme_replace_placeholders("#_LOCATIONNAME, #_ADDRESS, #_TOWN", $event); ?></p>
         <?php if ($event['price']) ?>
            <p><?php _e ( 'Price: ','eme' ); echo eme_replace_placeholders("#_CURRENCY #_PRICE", $event)?></p>
         <h2><?php _e('Bookings data', 'eme');?></h2>
         <table id="bookings-table">
            <tr>
               <th scope='col' class='eme_print_name'><?php _e('Name', 'eme')?></th>
               <th scope='col' class='eme_print_email'><?php _e('E-mail', 'eme')?></th>
               <th scope='col' class='eme_print_phone'><?php _e('Phone number', 'eme')?></th> 
               <th scope='col' class='eme_print_seats'><?php if ($is_multiprice) _e('Seats (Multiprice)', 'eme'); else _e('Seats', 'eme'); ?></th>
               <th scope='col' class='eme_print_paid'><?php _e('Paid', 'eme')?></th>
               <th scope='col' class='eme_print_comment'><?php _e('Comment', 'eme')?></th> 
            <?php
            foreach($answer_columns as $col) {
               $class="eme_print_formfield".$formfield[$col['field_name']];
               print "<th scope='col' class='$class'>".$col['field_name']."</th>";
            }
            ?>
            </tr>
            <?php
            foreach($bookings as $booking) {
               $person = eme_get_person ($booking['person_id']);
               $pending_string="";
               if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
                  $pending_string=__('(pending)','eme');
               }
                ?>
            <tr>
               <td class='eme_print_name'><?php echo $person['person_name']?></td> 
               <td class='eme_print_email'><?php echo $person['person_email']?></td>
               <td class='eme_print_phone'><?php echo $person['person_phone']?></td>
               <td class='eme_print_seats' class='seats-number'><?php 
               if ($is_multiprice) {
                  // in cases where the event switched to multiprice, but somebody already registered while it was still single price: booking_seats_mp is then empty
                  if ($booking['booking_seats_mp'] == "")
                     $booking['booking_seats_mp']=$booking['booking_seats'];
                  echo $booking['booking_seats']." (".$booking['booking_seats_mp'].") ".$pending_string;
               } else {
                  echo $booking['booking_seats']." ".$pending_string;
               }
               ?>
               </td>
               <td class='eme_print_paid'><?php if ($booking['booking_payed']) _e('Yes'); else _e('No'); ?></td>
               <td class='eme_print_comment'><?=$booking['booking_comment'] ?></td> 
               <?php
                  $answers = eme_get_answers($booking['booking_id']);
                  foreach($answer_columns as $col) {
                     $found=0;
                     foreach ($answers as $answer) {
                        $class="eme_print_formfield".$formfield[$col['field_name']];
                        if ($answer['field_name'] == $col['field_name']) {
                           print "<td class='$class'>".eme_sanitize_html($answer['answer'])."</td>";
                           $found=1;
                           break;
                        }
                     }
                     # to make sure the number of columns are correct, we add an empty answer if none was found
                     if (!$found)
                        print "<td class='$class'>&nbsp;</td>";
                  }
               ?>
            </tr>
               <?php } ?>
            <tr id='booked-seats'>
               <td colspan='2'>&nbsp;</td>
               <td class='total-label'><?php _e('Booked', 'eme')?>:</td>
               <td colspan='3' class='seats-number'><?php
               print $booked_seats;
               if ($is_multiseat) print " ($booked_seats_ms)";
			      if ($pending_seats>0) {
                  if ($is_multiseat)
                     print " ".sprintf( __('(%s pending)','eme'), $pending_seats . " ($pending_seats_ms)");
                  else
                     print " ".sprintf( __('(%s pending)','eme'), $pending_seats);
               }
			?>
		</td>
            </tr>
            <tr id='available-seats'>
               <td colspan='2'>&nbsp;</td> 
               <td class='total-label'><?php _e('Available', 'eme')?>:</td>
               <td colspan='3' class='seats-number'><?php print $available_seats; if ($is_multiseat) print " ($available_seats_ms)"; ?></td>
            </tr>
         </table>
         </div>
      </body>
      </html>
      <?php
      die();
} 

function eme_people_table($message="") {
   $persons = eme_get_persons();
   $destination = admin_url("admin.php?page=eme-people");
   if (count($persons) < 1 ) {
      _e("No people have responded to your events yet!", 'eme');
   } else { 
      $result = "<p>".__('This table shows the data about the people who responded to your events', 'eme')."</p>"; 
      if($message != "") {
            $result .= "
            <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
               <p>$message</p>
            </div>";
      }

      $result .= "<form id='people-filter' method='post' action='".$destination."'>
                  <input type='hidden' name='action' value='delete_people'/>";
      $result .=" <table id='eme-people-table' class='widefat post fixed'>
            <thead>
            <tr>
            <th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
            <th class='manage-column' scope='col'>Name</th>
            <th scope='col'>E-mail</th>
            <th scope='col'>Phone number</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
            <th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
            <th class='manage-column' scope='col'>Name</th>
            <th scope='col'>E-mail</th>
            <th scope='col'>Phone number</th>
            </tr>
            </tfoot>
         " ;
      foreach ($persons as $person) {
            $result .= "<tr><td><input type='checkbox' class ='row-selector' value='".$person['person_id']."' name='persons[]'/></td>
                  <td>".$person['person_name']."</td>
                  <td>".$person['person_email']."</td>
                  <td>".$person['person_phone']."</td></tr>";
      }

      $result .= "</table>
                     <div class='tablenav'>
                        <div class='alignleft actions'>
                        <input type='checkbox' name='delete_assoc_bookings' value='1'>".__('Also delete associated bookings','eme')."
                        <input class='button-secondary action' type='submit' name='doaction' value='Delete'/>
                        <br class='clear'/>
                        </div>
                        <br class='clear'/>
                     </div>";

      echo $result;
   }
} 

function eme_get_person_by_name_and_email($name, $email) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE person_name = %s AND person_email = %s",$name,$email);
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_person_by_wp_info($name, $email, $wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE person_name = %s AND person_email = %s AND wp_id = %d ",$name,$email,$wp_id);
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_person_by_wp_id($wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $wp_id = eme_sanitize_request($wp_id);
   $sql = "SELECT * FROM $people_table WHERE wp_id = '$wp_id';" ;
   $result = $wpdb->get_row($sql, ARRAY_A);
   if (!is_null($result['wp_id']) && $result['wp_id']) {
      $user_info = get_userdata($result['wp_id']);
      $result['person_name']=$user_info->display_name;
      $result['person_email']=$user_info->user_email;
   }
   return $result;
}

function eme_get_person_id_by_wp_id($wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $wp_id = eme_sanitize_request($wp_id);
   $sql = "SELECT person_id FROM $people_table WHERE wp_id = '$wp_id';" ;
   return($wpdb->get_var($sql));
}

function eme_delete_person($person_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = "DELETE FROM $people_table WHERE person_id = '$person_id';" ;
   $wpdb->query($sql);
   return 1;
}

function eme_get_person($person_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = "SELECT * FROM $people_table WHERE person_id = '$person_id';" ;
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_persons($person_ids="") {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   if ($person_ids != "") {
      $tmp_ids=join(",",$person_ids);
      $sql = "SELECT * FROM $people_table WHERE person_id IN ($tmp_ids);" ;
   } else {
      $sql = "SELECT *  FROM $people_table";
   }
   $lines = $wpdb->get_results($sql, ARRAY_A);
   $result = array();
   foreach ($lines as $line) {
      // if in the admin backend: also show the WP username if it exists
      if (is_admin() && !is_null($line['wp_id']) && $line['wp_id']) {
         $user_info = get_userdata($line['wp_id']);
         if ($line['person_name'] != $user_info->display_name)
            $line['person_name'].= " (WP username: ".$user_info->display_name.")";
         #$line['person_email']=$user_info->user_email;
         #$line['person_phone']=eme_get_user_phone($line['wp_id']);
      }
      # to be able to sort on person names, we need a hash starting with the name
      # but some people might have the same name (or register more than once),
      # so we add the ID to it
      $unique_id=$line['person_name']."_".$line['person_id'];
      $result[$unique_id]=$line;
   }
   # now do the sorting
   ksort($result);
   return $result;
}

function eme_add_person($name, $email, $phone, $wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $person=array();
   $person['person_name'] = eme_sanitize_request($name);
   $person['person_email'] = eme_sanitize_request($email);
   $person['person_phone'] = eme_sanitize_request($phone);
   $person['wp_id'] = eme_sanitize_request($wp_id);
   $wpdb->insert($people_table,$person);
   $person['person_id'] = $wpdb->insert_id;
   return ($person);
}

// when editing other profiles then your own
add_action('edit_user_profile', 'eme_user_profile') ;
// when editing your own profile
add_action('show_user_profile', 'eme_user_profile') ;

function eme_user_profile($user) {
   //$eme_phone=get_user_meta($user,'eme_phone',true);
   $eme_phone=$user->eme_phone;
   $eme_date_format=$user->eme_date_format;
   ?>
   <h3><?php _e('Events Made Easy settings', 'eme')?></h3>
   <table class='form-table'>
      <tr>
         <th><label for="eme_phone"><?php _e('Phone number','eme');?></label></th>
         <td><input type="text" name="eme_phone" id="eme_phone" value="<?php echo $eme_phone; ?>" class="regular-text" /> <br />
         <?php _e('The phone number used by Events Made Easy when the user is indicated as the contact person for an event.','eme');?></td>
      </tr>
      <tr>
         <th><label for="eme_date_format"><?php _e('Date format','eme');?></label></th>
         <td><input type="text" name="eme_date_format" id="eme_date_format" value="<?php echo $eme_date_format; ?>" class="regular-text" /> <br />
         <?php _e('The date format used by Events Made Easy in the admin section. If empty the general WP date format setting will be used.','eme');
               echo "\t<p>" . __('<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date and time formatting</a>.') . "</p>\n";
         ?>
         </td>
      </tr>
   </table>
   <?php
}

// when editing other profiles then your own
add_action('edit_user_profile_update','eme_update_user_profile');
// when editing your own profile
add_action('personal_options_update','eme_update_user_profile');

function eme_update_user_profile($wp_user_ID) {
   if(isset($_POST['eme_phone'])) {
      update_user_meta($wp_user_ID,'eme_phone', $_POST['eme_phone']);
   }
   if(isset($_POST['eme_date_format'])) {
      update_user_meta($wp_user_ID,'eme_date_format', $_POST['eme_date_format']);
   }
   
}

function eme_update_phone($person,$phone) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $phone = eme_sanitize_request($phone);
   $sql = "UPDATE $people_table SET person_phone='$phone' WHERE person_id=".$person['person_id'].";";
   $wpdb->query($sql);

   if (!is_null($person['wp_id']) && $person['wp_id']) {
      update_user_meta($person['wp_id'],'eme_phone', $phone);
   }
}

function eme_get_indexed_users() {
   global $wpdb;
   $sql = "SELECT display_name, ID FROM $wpdb->users";
   $users = $wpdb->get_results($sql, ARRAY_A);
   $indexed_users = array();
   foreach($users as $user) 
      $indexed_users[$user['ID']] = $user['display_name'];
   return $indexed_users;
}
?>
