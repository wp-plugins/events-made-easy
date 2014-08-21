<?php
require_once('../../../wp-load.php');

// prevent caching
header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// make sure we use the correct charset in the return
header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

if(isset($_GET['id']) && $_GET['id'] != "") {
   $item = eme_get_location($_GET['id']);
   $record = array();
   $record['id']      = $item['location_id'];
   $record['name']    = eme_trans_sanitize_html($item['location_name']); 
   $record['address'] = eme_trans_sanitize_html($item['location_address']);
   $record['town']    = eme_trans_sanitize_html($item['location_town']); 
   $record['latitude']    = eme_trans_sanitize_html($item['location_latitude']); 
   $record['longitude']    = eme_trans_sanitize_html($item['location_longitude']); 
   echo json_encode($record);
   
} else {

   $locations = eme_get_locations();
   $return = array();

   foreach($locations as $item) {
      $record = array();
      $record['id']      = $item['location_id'];
      $record['name']    = eme_trans_sanitize_html($item['location_name']); 
      $record['address'] = eme_trans_sanitize_html($item['location_address']);
      $record['town']    = eme_trans_sanitize_html($item['location_town']); 
      $record['latitude']    = eme_trans_sanitize_html($item['location_latitude']); 
      $record['longitude']    = eme_trans_sanitize_html($item['location_longitude']); 
      $return[]  = $record;
   }

   $q = strtolower($_GET["q"]);
   if (!$q) return;
 
   $result=array();
   foreach($return as $row) {
      if (strpos(strtolower($row['name']), $q) !== false)
         $result[]=$row;
   }
   echo json_encode($result);
}
?>
