<?php



function eme_filter_form_shortcode($atts) {
   extract ( shortcode_atts ( array ('multiple' => 0, 'multisize' => 5, 'scope_count' => 12, 'submit' => 'Submit', 'fields'=> 'all', 'category' => '', 'notcategory' => '', 'template_id' => 0 ), $atts ) );
   $multiple = ($multiple==="true" || $multiple==="1") ? true : $multiple;
   $multiple = ($multiple==="false" || $multiple==="0") ? false : $multiple;

   if ($template_id) {
      $format_arr = eme_get_template($template_id);
      $filter_form_format=$format_arr['format'];
   } else {
      $filter_form_format = get_option('eme_filter_form_format');
   }

   $content=eme_replace_filter_form_placeholders($filter_form_format,$multiple,$multisize,$scope_count,$fields,$category,$notcategory);
   # using the current page as action, so we can leave action empty in the html form definition
   # this helps to keep the language and any other parameters, and works with permalinks as well
   $form = "<form action='' method='POST'>";
   $form .= "<input type='hidden' name='eme_eventAction' value='filter' />";
#   foreach ($_REQUEST as $key => $item) {
#      $form .= "<input type='hidden' name='$key' value='$item' />";
#   }
   $form .= $content;
   $form .= "<input type='submit' value='$submit' /></form>";
   return $form;
}

function eme_create_week_scope($count) {
   $day_offset=date('w');
   $start_day=time()-$day_offset*86400;
   $end_day=$start_day+6*86400;
   $scope=array();
   $scope[0] = __('Select Week','eme');
   for ( $i = 0; $i < $count; $i++) {
      $this_scope=date('Y-m-d',$start_day+$i*7*86400)."--".date('Y-m-d',$end_day+$i*7*86400);
      $scope_text = date_i18n (get_option('date_format'),$start_day+$i*7*86400)." -- ".date_i18n (get_option('date_format'),$end_day+$i*7*86400);
      $scope[$this_scope] = $scope_text;
   }
   return $scope;
}

function eme_create_month_scope($count) {
   $day_offset=date('j')-1;
   $scope=array();
   $scope[0] = __('Select Month','eme');
   for ( $i = 0; $i < $count; $i++) {
      $year=date('Y', strtotime("$i month")-$day_offset*86400);
      $month=date('m', strtotime("$i month")-$day_offset*86400);
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      $this_scope = "$limit_start--$limit_end";
      $scope_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$i month")-$day_offset*86400);
      $scope[$this_scope] = $scope_text;
   }
   return $scope;
}

function eme_create_year_scope($count) {
   $day_offset=date('j')-1;
   $scope=array();
   $scope[0] = __('Select Year','eme');
   for ( $i = 0; $i < $count; $i++) {
      $year=date('Y', strtotime("$i year")-$day_offset*86400);
      $limit_start = "$year-01-01";
      $limit_end   = "$year-12-31";
      $this_scope = "$limit_start--$limit_end";
      $scope_text = date_i18n (get_option('eme_show_period_yearly_dateformat'), strtotime("$i year")-$day_offset*86400);
      $scope[$this_scope] = $scope_text;
   }
   return $scope;
}

function eme_replace_filter_form_placeholders($format, $multiple, $multisize, $scope_count, $fields, $category, $notcategory) {
   if ($fields == "all")
      $fields="categories,locations,towns,weeks,months";

   preg_match_all("/#_[A-Za-z0-9_]+/", $format, $placeholders);
   usort($placeholders[0],'sort_stringlenth');

   // if one of these changes, also the eme_events.php needs changing for the "Next page" part
   $cat_post_name="eme_cat_filter";
   $loc_post_name="eme_loc_filter";
   $town_post_name="eme_town_filter";
   $scope_post_name="eme_scope_filter";

   $selected_scope = isset($_REQUEST[$scope_post_name]) ? eme_sanitize_request($_REQUEST[$scope_post_name]) : '';
   $selected_location = isset($_REQUEST[$loc_post_name]) ? eme_sanitize_request($_REQUEST[$loc_post_name]) : '';
   $selected_town = isset($_REQUEST[$town_post_name]) ? eme_sanitize_request($_REQUEST[$town_post_name]) : '';
   $selected_category = isset($_REQUEST[$cat_post_name]) ? eme_sanitize_request($_REQUEST[$cat_post_name]) : '';

   $extra_conditions_arr=array();
   if ($category != '')
      $extra_conditions_arr[]="(category_id IN ($category))";
   if ($notcategory != '')
      $extra_conditions_arr[]="(category_id NOT IN ($notcategory))";
   $extra_conditions = implode(' AND ',$extra_conditions_arr);

   foreach($placeholders[0] as $result) {
      $replacement = "";
      $eventful=0;
      $found = 1;
      $orig_result = $result;

      if (preg_match('/#_(EVENTFUL_)?FILTER_CATS/', $result) && get_option('eme_categories_enabled')) {
         if (strstr($result,'#_EVENTFUL')) {
            $eventful=1;
         }

         $categories = eme_get_categories($eventful,"future",$extra_conditions);
         if (strstr($fields,'categories') && $categories) {
            $cat_list = array();
            foreach ($categories as $this_category) {
               $id=$this_category['category_id'];
               $cat_list[$id]=eme_translate($this_category['category_name']);
            }
            asort($cat_list);
            if ($multiple) {
               $cat_list = array(0=>__('Select one or more categories','eme'))+$cat_list;
               $replacement = eme_ui_multiselect($selected_category,$cat_post_name,$cat_list,$multisize);
            } else {
               $cat_list = array(0=>__('Select a category','eme'))+$cat_list;
               $replacement = eme_ui_select($selected_category,$cat_post_name,$cat_list);
            }
         }

      } elseif (preg_match('/#_(EVENTFUL_)?FILTER_LOCS/', $result)) {
         if (strstr($result,'#_EVENTFUL')) {
            $eventful=1;
         }
         $locations = eme_get_locations($eventful,"future");

         if (strstr($fields,'locations') && $locations) {
            $loc_list = array();
            foreach ($locations as $this_location) {
               $id=$this_location['location_id'];
               $loc_list[$id]=eme_translate($this_location['location_name']);
            }
            asort($loc_list);
            if ($multiple) {
               $loc_list = array(0=>__('Select one or more locations','eme'))+$loc_list;
               $replacement = eme_ui_multiselect($selected_location,$loc_post_name,$loc_list,$multisize);
            } else {
               $loc_list = array(0=>__('Select a location','eme'))+$loc_list;
               $replacement = eme_ui_select($selected_location,$loc_post_name,$loc_list);
            }
         }

      } elseif (preg_match('/#_(EVENTFUL_)?FILTER_TOWNS/', $result)) {
         if (strstr($result,'#_EVENTFUL')) {
            $eventful=1;
         }
         $towns = eme_get_locations($eventful,"future");
         if (strstr($fields,'towns') && $towns) {
            $town_list = array();
            foreach ($towns as $this_town) {
               $id=eme_translate($this_town['location_town']);
               $town_list[$id]=$id;
            }
            asort($town_list);
            if ($multiple) {
               $town_list = array(0=>__('Select one or more towns','eme'))+$town_list;
               $replacement = eme_ui_multiselect($selected_town,$town_post_name,$town_list,$multisize);
            } else {
               $town_list = array(0=>__('Select a town','eme'))+$town_list;
               $replacement = eme_ui_select($selected_town,$town_post_name,$town_list);
            }
         }

      } elseif (preg_match('/#_FILTER_WEEKS/', $result)) {
         if (strstr($fields,'weeks'))
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_week_scope($scope_count));
      } elseif (preg_match('/#_FILTER_MONTHS/', $result)) {
         if (strstr($fields,'months'))
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_month_scope($scope_count));
      } elseif (preg_match('/#_FILTER_YEARS/', $result)) {
         if (strstr($fields,'years'))
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_year_scope($scope_count));
      } else {
         $found = 0;
      }

      if ($found) {
         $replacement = apply_filters('eme_general', $replacement);
         $format = str_replace($orig_result, $replacement ,$format );
      }
   }

   return do_shortcode($format);
}

?>
