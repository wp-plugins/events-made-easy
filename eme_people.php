<?php
function eme_people_page() {
   $message="";
   if (!current_user_can( get_option('eme_cap_people')) && isset($_POST['eme_admin_action'])) {
      $message = __('You have no right to update people!','eme');

   } elseif (isset ($_GET['person_id']) && isset($_GET['eme_admin_action']) && $_GET['eme_admin_action'] == 'editperson') {
      eme_people_edit_layout();
      // don't show the people table in this case, so we return from the function
      return;
   } elseif (isset ($_POST['person_id']) && isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == 'confirm_editperson') {
      $person_id=intval($_POST['person_id']);
      $basic_info_too=1;
      $person=eme_update_person_with_postinfo($person_id,$basic_info_too);
      if ($person)
               $message = __("Person info has been updated.","eme");
      else
               $message = __("Couldn't update the person. Please try again.","eme");
   } elseif (isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == 'deleteunusedpeople') {
      eme_delete_persons_without_bookings();
      $message = __("People without bookings have been deleted.","eme");
   } elseif (isset ($_POST['persons']) && isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == 'delete_people') {
      eme_people_delete_layout();
      // don't show the people table in this case, so we return from the function
      return;
   } elseif (isset ($_POST['persons']) && isset($_POST['eme_admin_action']) && $_POST['eme_admin_action'] == 'confirm_delete_people') {
         $persons = $_POST['persons'];
         if(is_array($persons)){
            //Make sure the array is only numbers
            foreach ($persons as $person_id) {
               if (is_numeric($person_id)) {
                  $person=eme_get_person($person_id);
                  $person_name=$person['lastname'].' '.$person['firstname'];
                  if (isset($_POST['delete_option']) && $_POST['delete_option'] == 'delete_assoc_bookings') {
                     $res=eme_delete_all_bookings_for_person_id($person_id);
                     if ($res) {
                        $message.=__("Deleted all bookings made by '".$person_name."'", 'eme');
                        $message.="<br />";
                     }
                  } elseif (isset($_POST['delete_option']) && $_POST['delete_option'] == 'transfer_assoc_bookings') {
                     $to_person_id=intval($_POST['to_person_id']);
                     $to_person=eme_get_person($to_person_id);
                     $to_person_name=$to_person['lastname'].' '.$to_person['firstname'];
                     $res=eme_transfer_all_bookings($person_id,$to_person_id);
                     if ($res===true) {
                        $message.=__("Transferred all bookings made by '".$person_name."' to '".$to_person_name."'", 'eme');
                        $message.="<br />";
                     }
                  }
                  $res=eme_delete_person($person_id);
                  if ($res) {
                     $message.=__("Deleted '".$person_name."'", 'eme');
                     $message.="<br />";
                  }
               }
            }
         } else {
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

function eme_people_edit_layout($message = "") {
   $person_id = intval($_GET['person_id']);
   $person = eme_get_person($person_id);
   $layout = "
      <div class='wrap'>
      <div id='icon-edit' class='icon32'>
      <br />
      </div>

      <h2>".__('Edit person', 'eme')."</h2>";

   if($message != "") {
      $layout .= "
         <div id='message' class='updated' style='background-color: rgb(255, 251, 204);'>
         <p>$message</p>
         </div>";
   }
   $layout .= "
      <div id='ajax-response'></div>

      <form name='editperson' id='editperson' method='post' action='".admin_url("admin.php?page=eme-people")."' class='validate'>
      <input type='hidden' name='eme_admin_action' value='confirm_editperson' />
      <input type='hidden' name='person_id' value='".$person_id."' />";

   $layout .= "
      <table class='form-table'>
      <tr class='form-field form-required'>
      <th scope='row' valign='top'><label for='lastname'>".__('Last Name', 'eme')."</label></th>
      <td><input name='lastname' id='lastname' type='text' value='".eme_sanitize_html($person['lastname'])."' size='40' /></td>
      </tr>
      <tr class='form-field form-required'>
      <th scope='row' valign='top'><label for='firstname'>".__('First Name', 'eme')."</label></th>
      <td><input name='firstname' id='firstname' type='text' value='".eme_sanitize_html($person['firstname'])."' size='40' /></td>
      </tr>
      <tr class='form-field'>
      <th scope='row' valign='top'><label for='address1'>".__('Address1', 'eme')."</label></th>
      <td><input name='address1' id='address1' type='text' value='".eme_sanitize_html($person['address1'])."' size='40' /></td>
      </tr>
      <tr class='form-field'>
      <th scope='row' valign='top'><label for='address2'>".__('Address2', 'eme')."</label></th>
      <td><input name='address2' id='address2' type='text' value='".eme_sanitize_html($person['address2'])."' size='40' /></td>
      </tr>
      <tr class='form-field'>
      <th scope='row' valign='top'><label for='city'>".__('City', 'eme')."</label></th>
      <td><input name='city' id='city' type='text' value='".eme_sanitize_html($person['city'])."' size='40' /></td>
      </tr>
      <tr class='form-field'>
      <th scope='row' valign='top'><label for='state'>".__('State', 'eme')."</label></th>
      <td><input name='state' id='state' type='text' value='".eme_sanitize_html($person['state'])."' size='40' /></td>
      </tr>
      <tr class='form-field'>
      <th scope='row' valign='top'><label for='zip'>".__('Zip', 'eme')."</label></th>
      <td><input name='zip' id='zip' type='text' value='".eme_sanitize_html($person['zip'])."' size='40' /></td>
      </tr>
      <tr class='form-field'>
      <th scope='row' valign='top'><label for='country'>".__('Country', 'eme')."</label></th>
      <td><input name='country' id='country' type='text' value='".eme_sanitize_html($person['country'])."' size='40' /></td>
      </tr>
      <tr class='form-field form-required'>
      <th scope='row' valign='top'><label for='email'>".__('E-mail', 'eme')."</label></th>
      <td><input name='email' id='email' type='text' value='".eme_sanitize_html($person['email'])."' size='40' /></td>
      </tr>
      <tr class='form-field form-required'>
      <th scope='row' valign='top'><label for='phone'>".__('Phone number', 'eme')."</label></th>
      <td><input name='phone' id='phone' type='text' value='".eme_sanitize_html($person['phone'])."' size='40' /></td>
      </tr>
      </table>
      <p class='submit'><input type='submit' class='button-primary' name='submit' value='".__('Update person', 'eme')."' /></p>
      </form>
      </div>
      ";
   echo $layout;
}

function eme_people_delete_layout($message = "") {
   $persons = $_POST['persons'];
   $destination = admin_url("admin.php?page=eme-people");
   ?>
      <h2><?php _e('Delete People','eme'); ?></h2>
      <?php admin_show_warnings(); ?>
      <form id='people-filter' method='post' action='<?php print $destination; ?>'>
      <p><?php _e('You have specified these people for deletion:','eme'); ?></p>
      <ul>
      <?php 
      if(is_array($persons)) {
         foreach ($persons as $person_id) {
            if (is_numeric($person_id)) {
               $person=eme_get_person($person_id);
               $person_name=$person['lastname'].' '.$person['firstname'];
               print "<li><input type='hidden' name='persons[]' value='".$person_id."' />".eme_sanitize_html($person_name)."</li>";
            }
         }
      }
      ?>
      </ul>
      <fieldset><p><legend><?php _e('What should be done with the bookings done by these people?','eme'); ?></legend></p>
      <ul style="list-style:none;">
      <li><label><input type="radio" id="delete_option0" name="delete_option" value="delete_assoc_bookings" /><?php _e('Delete associated bookings','eme');?></label></li>
      <?php
      $other_persons=eme_get_persons("",$persons);
      if ($other_persons) {
         ?>
         <li><input type="radio" id="delete_option1" name="delete_option" value="transfer_assoc_bookings" />
         <label for="delete_option1"><?php _e('Transfer associated bookings to:','eme');?></label>
         <select name='to_person_id' id='to_person_id' class=''>
         <?php
         foreach ($other_persons as $person) {
            $person_name=$person['lastname'].' '.$person['firstname'];
            print "<option value='".$person['person_id']."'>".eme_sanitize_html($person_name)."</option>";
         }
         ?>
         </select>
         <?php
      }
      ?>
      </li>
      </ul></fieldset>
      <input type="hidden" name="eme_admin_action" value="confirm_delete_people" />
      <p class="submit"><input type="submit" name="submit" id="submit" class="button" value="Confirm Deletion" /></p></div>
      </form>
      <script>
      jQuery(document).ready( function($) {
            var submit = $('#submit').prop('disabled', true);
            $('input[name=delete_option]').one('change', function() {
               submit.prop('disabled', false);
      });
      $('#to_person_id').focus( function() {
         $('#delete_option1').prop('checked', true).trigger('change');
         });
      }); 
      </script>

   <?php
}

function eme_global_map_json($eventful = false, $scope = "all", $category = '', $map_id, $offset = 0) {
   $eventful = ($eventful==="true" || $eventful==="1") ? true : $eventful;
   $eventful = ($eventful==="false" || $eventful==="0") ? false : $eventful;

   $locations = eme_get_locations((bool)$eventful,$scope,$category,$offset);
   $json_locations = array();
   foreach($locations as $location) {
      $json_location = array();

      # first we set the balloon info
      $tmp_loc=eme_replace_locations_placeholders(get_option('eme_location_baloon_format'), $location);
      # no newlines allowed, otherwise no map is shown
      $tmp_loc=preg_replace("/\r\n?|\n\r?/","<br />",$tmp_loc);
      $json_location[] = '"location_balloon":"'.eme_trans_sanitize_html($tmp_loc).'"';

      # second, we fill in the rest of the info
      foreach($location as $key => $value) {
         # we skip some keys, since json is limited in size we only return what's needed in the javascript
         if (preg_match('/location_balloon|location_id|location_latitude|location_longitude/', $key)) {
            # no newlines allowed, otherwise no map is shown
            $value=preg_replace("/\r\n?|\n\r/","<br />",$value);
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
   $json .= '","map_id":"' ;
   $json .= $map_id;
   $json .= '"}' ;
   echo $json;
}

function fputcsv2 ($fh, $fields, $delimiter = '', $enclosure = '"', $mysql_null = false) {
    if (empty($delimiter))
      $delimiter = get_option('eme_csv_separator');
    if (empty($delimiter))
      $delimiter = ',';

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
   global $eme_timezone;
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
   if (has_filter('eme_csv_header_filter')) {
      $line=apply_filters('eme_csv_header_filter',$event);
      fputcsv2($out,$line);
   }
   $line=array();
   $line[]=__('ID', 'eme');
   $line[]=__('Last Name', 'eme');
   $line[]=__('First Name', 'eme');
   $line[]=__('Address1', 'eme');
   $line[]=__('Address2', 'eme');
   $line[]=__('City', 'eme');
   $line[]=__('State', 'eme');
   $line[]=__('Zip', 'eme');
   $line[]=__('Country', 'eme');
   $line[]=__('E-mail', 'eme');
   $line[]=__('Phone number', 'eme');
   if ($is_multiprice)
      $line[]=__('Seats (Multiprice)', 'eme');
   else
      $line[]=__('Seats', 'eme');
   $line[]=__('Paid', 'eme');
   $line[]=__('Booking date','eme');
   $line[]=__('Total price','eme');
   $line[]=__('Unique nbr','eme');
   $line[]=__('Comment', 'eme');
   foreach($answer_columns as $col) {
      $line[]=$col['field_name'];
   }
   fputcsv2($out,$line);
   foreach($bookings as $booking) {
      $localised_booking_date = eme_localised_date($booking['creation_date']." ".$eme_timezone);
      $localised_booking_time = eme_localised_time($booking['creation_date']." ".$eme_timezone);
      $person = eme_get_person ($booking['person_id']);
      $line=array();
      $pending_string="";
      if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
         $pending_string=__('(pending)','eme');
      }
      $line[]=$booking['booking_id'];
      $line[]=$person['lastname'];
      $line[]=$person['firstname'];
      $line[]=$person['address1'];
      $line[]=$person['address2'];
      $line[]=$person['city'];
      $line[]=$person['state'];
      $line[]=$person['zip'];
      $line[]=$person['country'];
      $line[]=$person['email'];
      $line[]=$person['phone'];
      if ($is_multiprice) {
         // in cases where the event switched to multiprice, but somebody already registered while it was still single price: booking_seats_mp is then empty
         if ($booking['booking_seats_mp'] == "")
            $booking['booking_seats_mp']=$booking['booking_seats'];
         $line[]=$booking['booking_seats']." (".$booking['booking_seats_mp'].") ".$pending_string;
      } else {
         $line[]=$booking['booking_seats']." ".$pending_string;
      }
      $line[]=$booking['booking_payed']? __('Yes'): __('No');
      $line[]=$localised_booking_date." ".$localised_booking_time;
      $line[]=eme_get_total_booking_price($event,$booking);
      $line[]=$booking['transfer_nbr_be97'];
      $line[]=$booking['booking_comment'];
      $answers = eme_get_answers($booking['booking_id']);
      foreach($answer_columns as $col) {
         $found=0;
         foreach ($answers as $answer) {
            if ($answer['field_name'] == $col['field_name']) {
               $line[]=eme_convert_answer2tag($answer);
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
   if (has_filter('eme_csv_footer_filter')) {
      $line=apply_filters('eme_csv_footer_filter',$event);
      fputcsv2($out,$line);
   }
   fclose($out);
   die();
}

function eme_printable_booking_report($event_id) {
   global $eme_timezone;
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
      $available_seats_ms=eme_convert_array2multi(eme_get_available_multiseats($event_id));
      $booked_seats_ms=eme_convert_array2multi(eme_get_booked_multiseats($event_id));
      $pending_seats_ms=eme_convert_array2multi(eme_get_pending_multiseats($event_id));
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
      <body id="eme_printable_body">
         <div id="eme_printable_container">
         <h1>Bookings for <?php echo eme_trans_sanitize_html($event['event_name']);?></h1> 
         <p><?php echo eme_localised_date($event['event_start_date']." ".$eme_timezone); ?></p>
         <p><?php if ($event['location_id']) echo eme_replace_placeholders("#_LOCATIONNAME, #_ADDRESS, #_TOWN", $event); ?></p>
         <?php if ($event['price']) ?>
            <p><?php _e ( 'Price: ','eme' ); echo eme_replace_placeholders("#_CURRENCY #_PRICE", $event)?></p>
         <h2><?php _e('Bookings data', 'eme');?></h2>
         <table id="eme_printable_table">
            <tr>
               <th scope='col' class='eme_print_name'><?php _e('ID', 'eme')?></th>
               <th scope='col' class='eme_print_name'><?php _e('Last Name', 'eme')?></th>
               <th scope='col' class='eme_print_name'><?php _e('First Name', 'eme')?></th>
               <th scope='col' class='eme_print_email'><?php _e('E-mail', 'eme')?></th>
               <th scope='col' class='eme_print_phone'><?php _e('Phone number', 'eme')?></th> 
               <th scope='col' class='eme_print_seats'><?php if ($is_multiprice) _e('Seats (Multiprice)', 'eme'); else _e('Seats', 'eme'); ?></th>
               <th scope='col' class='eme_print_paid'><?php _e('Paid', 'eme')?></th>
               <th scope='col' class='eme_print_booking_date'><?php _e('Booking date', 'eme')?></th>
               <th scope='col' class='eme_print_total_price'><?php _e('Total price', 'eme')?></th>
               <th scope='col' class='eme_print_unique_nbr'><?php _e('Unique nbr', 'eme')?></th>
               <th scope='col' class='eme_print_comment'><?php _e('Comment', 'eme')?></th> 
            <?php
            $nbr_columns=11;
            foreach($answer_columns as $col) {
               $class="eme_print_formfield".$formfield[$col['field_name']];
               print "<th scope='col' class='$class'>".$col['field_name']."</th>";
               $nbr_columns++;
            }
            ?>
            </tr>
            <?php
            foreach($bookings as $booking) {
               $localised_booking_date = eme_localised_date($booking['creation_date']." ".$eme_timezone);
               $localised_booking_time = eme_localised_time($booking['creation_date']." ".$eme_timezone);
               $person = eme_get_person ($booking['person_id']);
               $pending_string="";
               if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
                  $pending_string=__('(pending)','eme');
               }
                ?>
            <tr>
               <td class='eme_print_id'><?php echo $booking['booking_id']?></td> 
               <td class='eme_print_name'><?php echo $person['lastname']?></td> 
               <td class='eme_print_name'><?php echo $person['firstname']?></td> 
               <td class='eme_print_email'><?php echo $person['email']?></td>
               <td class='eme_print_phone'><?php echo $person['phone']?></td>
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
               <td class='eme_print_booking_date'><?php echo $localised_booking_date." ".$localised_booking_time; ?></td>
               <td class='eme_print_total_price'><?php echo eme_get_total_booking_price($event,$booking); ?></td>
               <td class='eme_print_unique_nbr'><?php echo $booking['transfer_nbr_be97']; ?></td>
               <td class='eme_print_comment'><?=$booking['booking_comment'] ?></td> 
               <?php
                  $answers = eme_get_answers($booking['booking_id']);
                  foreach($answer_columns as $col) {
                     $found=0;
                     foreach ($answers as $answer) {
                        $class="eme_print_formfield".$formfield[$col['field_name']];
                        if ($answer['field_name'] == $col['field_name']) {
                           print "<td class='$class'>".eme_sanitize_html(eme_convert_answer2tag($answer))."</td>";
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
            <tr id='eme_printable_booked-seats'>
               <td colspan='<?php echo $nbr_columns-4;?>'>&nbsp;</td>
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
            <tr id='eme_printable_available-seats'>
               <td colspan='<?php echo $nbr_columns-4;?>'>&nbsp;</td>
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
   if (isset($_GET['search'])) {
      $search = "[person_id=".intval($_GET['search'])."]";
   }

   $persons = eme_get_persons();
   $destination = admin_url("admin.php?page=eme-people");
   if($message != "") {
      ?>
      <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
         <p><?php print $message; ?></p>
      </div>
      <?php
   }

   if (count($persons) < 1 ) {
      _e("No people have responded to your events yet!", 'eme');
   } else { 
   ?>
      <p><?php _e('This table shows the data about the people who responded to your events', 'eme') ?> </p> 
      <form id='people-deleteunused' method='post' action='<?php print $destination; ?>'>
      <input type="hidden" name="eme_admin_action" value="deleteunusedpeople" />
      <input type="submit" value="<?php _e ( 'Delete people without bookings','eme' ); ?>" name="doaction" id="doaction" class="button-primary action" />
      </form>
      <div class="clear"></div>
      <br />
      <form id='people-filter' method='post' action='<?php print $destination; ?>'>
      <select name="eme_admin_action">
      <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
      <option value="delete_people"><?php _e ( 'Delete selected','eme' ); ?></option>
      </select>
      <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
      <div class="clear"></div>
      <br />

      <table id='eme-people-table' class='widefat hover stripe'>
            <thead>
            <tr>
            <th class='manage-column column-cb check-column' scope='col'><input class='select-all' type='checkbox' value='1' /></th>
            <th>hidden for person id search</th>
            <th class='manage-column' scope='col'><?php _e('ID', 'eme'); ?></th>
            <th class='manage-column' scope='col'><?php _e('Name', 'eme'); ?></th>
            <th scope='col'><?php _e('E-mail', 'eme'); ?></th>
            <th scope='col'><?php _e('Phone number', 'eme'); ?></th>
            <th scope='col'></th>
            </tr>
            </thead>
            <tbody>
      <?php
      $search_dest=admin_url("admin.php?page=eme-registration-seats");
      foreach ($persons as $person) {
         $search_url=add_query_arg(array('search'=>$person['person_id']),$search_dest);
         print "<tr><td><input type='checkbox' class ='row-selector' value='".$person['person_id']."' name='persons[]' /></td>
                  <td>[person_id=".$person['person_id']."]</td>
                  <td><a href='".admin_url("admin.php?page=eme-people&amp;eme_admin_action=editperson&amp;person_id=".$person['person_id'])."' title='". __('Click the ID in order to edit the person.','eme')."'>".$person['person_id']."</a></td>
                  <td>".eme_sanitize_html($person['lastname'].' '.$person['firstname'])."</td>
                  <td>".eme_sanitize_html($person['email'])."</td>
                  <td>".eme_sanitize_html($person['phone'])."</td>
                  <td><a href='".$search_url."'>".__('Show all bookings','eme')."</a></td>
                  </tr>";
      }
      ?>

      </tbody></table>
<script type="text/javascript">
   jQuery(document).ready( function() {
            jQuery('#eme-people-table').dataTable( {
               "dom": 'CT<"clear">Rlfrtip',
               <?php
               // jquery datatables locale loading
               $locale_code = get_locale();
               $locale_file = EME_PLUGIN_DIR. "js/jquery-datatables/i18n/$locale_code.json";
               $locale_file_url = EME_PLUGIN_URL. "js/jquery-datatables/i18n/$locale_code.json";
               if ($locale_code != "en_US" && file_exists($locale_file)) {
               ?>
               "language": {
               "url": "<?php echo $locale_file_url; ?>"
               },
               <?php
               }
               ?> 
               "stateSave": true,
               <?php
               if (!empty($search)) {
                  // If datatables state is saved, the initial search
                  // is ignored and we need to use stateloadparams
                  // So we give the 2 options
               ?> 
               "stateLoadParams": function (settings, data) {
                  data.search.search = "<?php echo $search; ?>";
               },
               "search": {
                  "search":  "<?php echo $search; ?>"
               },
               <?php
               }
               ?> 
               "pagingType": "full",
               "columnDefs": [
                  { "sortable": false, "targets": 0 },
                  { "visible": false, "targets": 1 }
               ],
               "colVis": {
                  "exclude": [0,1,2,6]
               },
               "tableTools": {
                  "aButtons": [ { "sExtends": "csv", "mColumns": "visible"},
                                "print"
                              ],
                  "sSwfPath": "<?php echo EME_PLUGIN_URL;?>js/jquery-datatables/extensions/TableTools-2.2.4-dev/swf/copy_csv_xls.swf"
               }
           });
   } );
</script>
<?php
   }
} 

// API function for people wanting to check if somebody is already registered
function eme_get_person_by_post() {
   $booker=array();
   if (isset($_POST['lastname']) && isset($_POST['email'])) {
      $bookerLastName = eme_strip_tags($_POST['lastname']);
      if (isset($_POST['firstname']))
         $bookerFirstName = eme_strip_tags($_POST['firstname']);
      else
         $bookerFirstName = "";
      $bookerEmail = eme_strip_tags($_POST['email']);
      $booker = eme_get_person_by_name_and_email($bookerLastName, $bookerFirstName, $bookerEmail);
   }
   return $booker;
}

function eme_get_person_by_name_and_email($lastname, $firstname, $email) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   if (!empty($firstname))
      $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE lastname = %s AND firstname = %s AND email = %s",$lastname,$firstname,$email);
   else
      $sql = $wpdb->prepare("SELECT * FROM $people_table WHERE lastname = %s AND email = %s",$lastname,$email);
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
      $result['lastname']=$user_info->user_lastname;
      if (empty($result['lastname']))
         $result['lastname']=$user_info->display_name;
      $result['firstname']=$user_info->user_firstname;
      $result['email']=$user_info->user_email;
   }
   return $result;
}

function eme_get_person_id_by_wp_id($wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $wp_id = eme_sanitize_request($wp_id);
   $sql = "SELECT person_id FROM $people_table WHERE wp_id = '$wp_id'";
   return($wpdb->get_var($sql));
}

function eme_delete_person($person_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = "DELETE FROM $people_table WHERE person_id = '$person_id'";
   $wpdb->query($sql);
   return 1;
}

function eme_delete_persons_without_bookings() {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   // first, clean up unreferenced bookings
   $sql = "DELETE FROM $bookings_table WHERE event_id NOT IN (SELECT DISTINCT event_id FROM $events_table)";
   $wpdb->query($sql);
   // now, delete unreferenced persons
   $sql = "DELETE FROM $people_table WHERE person_id NOT IN (SELECT DISTINCT person_id FROM $bookings_table)";
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

function eme_get_persons($person_ids="",$not_person_ids="") {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   if ($person_ids != "") {
      $tmp_ids=join(",",$person_ids);
      $sql = "SELECT * FROM $people_table WHERE person_id IN ($tmp_ids) ORDER BY person_id" ;
   } elseif ($not_person_ids != "") {
      $tmp_ids=join(",",$not_person_ids);
      $sql = "SELECT * FROM $people_table WHERE person_id NOT IN ($tmp_ids) ORDER BY person_id" ;
   } else {
      $sql = "SELECT * FROM $people_table ORDER BY person_id";
   }
   $lines = $wpdb->get_results($sql, ARRAY_A);
   $result = array();
   foreach ($lines as $line) {
      // if in the admin backend: also show the WP username if it exists
      if (is_admin() && !is_null($line['wp_id']) && $line['wp_id']) {
         $user_info = get_userdata($line['wp_id']);
         if (($line['lastname'] != $user_info->display_name) && ($line['lastname'] != $user_info->user_lastname && $line['firstname'] != $user_info->user_firstname) )
            $line['lastname'].= " (WP username: ".$user_info->display_name.")";
         #$line['person_email']=$user_info->user_email;
         #$line['person_phone']=eme_get_user_phone($line['wp_id']);
      }
      # to be able to sort on person names, we need a hash starting with the name
      # but some people might have the same name (or register more than once),
      # so we add the ID to it
      $unique_id=$line['lastname']."_".$line['firstname']."_".$line['person_id'];
      $result[$unique_id]=$line;
   }
   # now do the sorting
   ksort($result);
   return $result;
}

function eme_add_person($lastname, $firstname, $email, $wp_id, $lang) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $person=array();
   $person['lastname'] = eme_strip_tags($lastname);
   $person['firstname'] = eme_strip_tags($firstname);
   $person['email'] = eme_strip_tags($email);
   if (isset($_POST['address1'])) $person['address1'] = eme_strip_tags($_POST['address1']);
   if (isset($_POST['address2'])) $person['address2'] = eme_strip_tags($_POST['address2']);
   if (isset($_POST['city'])) $person['city'] = eme_strip_tags($_POST['city']);
   if (isset($_POST['state'])) $person['state'] = eme_strip_tags($_POST['state']);
   if (isset($_POST['zip'])) $person['zip'] = eme_strip_tags($_POST['zip']);
   if (isset($_POST['country'])) $person['country'] = eme_strip_tags($_POST['country']);
   if (isset($_POST['phone'])) $person['phone'] = eme_strip_tags($_POST['phone']);
   $person['wp_id'] = intval($wp_id);
   $person['lang'] = eme_strip_tags($lang);
   $wpdb->insert($people_table,$person);
   $person_id = $wpdb->insert_id;
   return eme_get_person($person_id);
}

function eme_update_person_with_postinfo($person_id,$basic_info_too=0) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;

   $where = array();
   $where['person_id'] = intval($person_id);
   $fields = array();
   if (isset($_POST['address1'])) $fields['address1'] = eme_strip_tags($_POST['address1']);
   if (isset($_POST['address2'])) $fields['address2'] = eme_strip_tags($_POST['address2']);
   if (isset($_POST['city'])) $fields['city'] = eme_strip_tags($_POST['city']);
   if (isset($_POST['state'])) $fields['state'] = eme_strip_tags($_POST['state']);
   if (isset($_POST['zip'])) $fields['zip'] = eme_strip_tags($_POST['zip']);
   if (isset($_POST['country'])) $fields['country'] = eme_strip_tags($_POST['country']);
   if (isset($_POST['phone'])) $fields['phone'] = eme_strip_tags($_POST['phone']);
   if ($basic_info_too) {
      $fields['lastname'] = eme_strip_tags($_POST['lastname']);
      $fields['email'] = eme_strip_tags($_POST['email']);
      if (isset($_POST['firstname'])) $fields['firstname'] = eme_strip_tags($_POST['firstname']);
   }

   // take into account that $fields can be empty too (if $basic_info_too=0 and the other fields are not set)
   if (!empty($fields) && $wpdb->update($people_table, $fields, $where) === false)
      return false;
   else
      return eme_get_person($person_id);
}

function eme_user_profile($user) {
   //$eme_phone=get_user_meta($user,'eme_phone',true);
   $eme_phone=$user->eme_phone;
   $person = eme_get_person_by_wp_id($user->ID);
   // only show future bookings
   $future=1;
   // define a simple template
   $template="#_STARTDATE #_STARTTIME: #_EVENTNAME (#_RESPSPACES places). #_CANCEL_LINK<br />";
   ?>
   <h3><?php _e('Events Made Easy settings', 'eme')?></h3>
   <table class='form-table'>
      <tr>
         <th><label for="eme_phone"><?php _e('Phone number','eme');?></label></th>
         <td><input type="text" name="eme_phone" id="eme_phone" value="<?php echo $eme_phone; ?>" class="regular-text" /> <br />
         <?php _e('The phone number used by Events Made Easy when the user is indicated as the contact person for an event.','eme');?></td>
      </tr>
      <?php if ($person) { ?>
      <tr>
         <th><label for="eme_phone"><?php _e('Future bookings made','eme');?></label></th>
	 <td><?php echo eme_get_bookings_list_for_person($person,$future,$template); ?>
      </tr>
      <?php } ?>
   </table>
   <?php
}

function eme_update_user_profile($wp_user_ID) {
   if(isset($_POST['eme_phone'])) {
      update_user_meta($wp_user_ID,'eme_phone', $_POST['eme_phone']);
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

function eme_people_search_ajax() {
   $persons = eme_get_persons();
   $return = array();

   if (!isset($_GET["q"])) {
      echo json_encode($return);
      return;
   }
   foreach($persons as $item) {
      $record = array();
      $record['lastname']  = eme_trans_sanitize_html($item['lastname']); 
      $record['firstname'] = eme_trans_sanitize_html($item['firstname']); 
      $record['address1']  = eme_trans_sanitize_html($item['address1']); 
      $record['address2']  = eme_trans_sanitize_html($item['address2']); 
      $record['city']      = eme_trans_sanitize_html($item['city']); 
      $record['state']     = eme_trans_sanitize_html($item['state']); 
      $record['zip']       = eme_trans_sanitize_html($item['zip']); 
      $record['country']   = eme_trans_sanitize_html($item['country']); 
      $record['email']     = eme_trans_sanitize_html($item['email']);
      $record['phone']     = eme_trans_sanitize_html($item['phone']); 
      $return[]  = $record;
   }

   $q = strtolower($_GET["q"]);
   if (!$q) return;

   $result=array();
   foreach($return as $row) {
      if (strpos(strtolower($row['lastname']), $q) !== false)
         $result[]=$row;
   }
   echo json_encode($result);
}
?>
