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
   $location = eme_get_location($_GET['id']);
   echo '{"id":"'.$location['location_id'].'" , "name"  : "'.eme_trans_sanitize_html($location['location_name']).'","town" : "'.eme_trans_sanitize_html($location['location_town']).'","address" : "'.eme_trans_sanitize_html($location['location_address']).'", "latitude" : "'.eme_trans_sanitize_html($location['location_latitude']).'", "longitude" : "'.eme_trans_sanitize_html($location['location_longitude']).'" }';
   
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
 
   foreach($return as $row) {
      if (strpos(strtolower($row['name']), $q) !== false) { 
         $location = array();
         $rows =array();
         foreach($row as $key => $value)
            $location[] = "'$key' : '".str_replace("'", "\'", $value)."'";
         echo ("{".implode(" , ", $location)." }\n");
       }
   }
}
?>
