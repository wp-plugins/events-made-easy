<?php

function eme_filter_form_shortcode($atts) {
   extract ( shortcode_atts ( array ('multiple' => 0, 'multisize' => 5, 'scope_count' => 12, 'submit' => 'Submit', 'fields'=> 'all', 'category' => '', 'notcategory' => '', 'template_id' => 0 ), $atts ) );
   $multiple = ($multiple==="true" || $multiple==="1") ? true : $multiple;
   $multiple = ($multiple==="false" || $multiple==="0") ? false : $multiple;

   if ($template_id) {
      // when using a template, don't bother with fields, the template should contain the things needed
      $filter_form_format= eme_get_template_format($template_id);
      $fields="";
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
   $start_of_week = get_option('start_of_week');
   $day_offset=date('w')-$start_of_week;
   if ($day_offset<0) $day_offset+=7;
   $scope=array();
   $scope[0] = __('Select Week','eme');
   for ( $i = 0; $i < $count; $i++) {
      $limit_start=eme_date_calc("-$day_offset days +$i weeks");
      $limit_end=eme_date_calc("+6 days",$limit_start);
      $this_scope=$limit_start."--".$limit_end;
      $scope_text = eme_localised_date($limit_start)." -- ".eme_localised_date($limit_end);
      $scope[$this_scope] = $scope_text;
   }
   return $scope;
}

function eme_create_month_scope($count) {
   $scope=array();
   $scope[0] = __('Select Month','eme');
   for ( $i = 0; $i < $count; $i++) {
      $limit_start= eme_date_calc("first day of $i month");
      $limit_end= eme_date_calc("last day of $i month");
      $this_scope = "$limit_start--$limit_end";
      $scope_text = eme_localised_date ($limit_start,get_option('eme_show_period_monthly_dateformat'));
      $scope[$this_scope] = $scope_text;
   }
   return $scope;
}

function eme_create_year_scope($count) {
   $day_offset=date('j')-1;
   $scope=array();
   $scope[0] = __('Select Year','eme');
   for ( $i = 0; $i < $count; $i++) {
      $year=date('Y', strtotime("$i year -$day_offset days"));
      $limit_start = "$year-01-01";
      $limit_end   = "$year-12-31";
      $this_scope = "$limit_start--$limit_end";
      $scope_text = eme_localised_date ($limit_start,get_option('eme_show_period_yearly_dateformat'));
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
   $localised_scope_post_name="eme_localised_scope_filter";

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

   $scope_fieldcount=0;
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
         if ($categories && (empty($fields) || strstr($fields,'categories'))) {
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

         if ($locations && (empty($fields) || strstr($fields,'locations'))) {
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
         if ($towns && (empty($fields) || strstr($fields,'towns'))) {
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
         if ($scope_fieldcount==0 && (empty($fields) || strstr($fields,'weeks'))) {
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_week_scope($scope_count));
            $scope_fieldcount++;
         }
      } elseif (preg_match('/#_FILTER_MONTHS/', $result)) {
         if ($scope_fieldcount==0 && (empty($fields) || strstr($fields,'months'))) {
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_month_scope($scope_count));
            $scope_fieldcount++;
         }
      } elseif (preg_match('/#_FILTER_MONTHRANGE/', $result)) {
         if ($scope_fieldcount==0 && (empty($fields) || strstr($fields,'monthrange'))) {
            $replacement = "<input type='text' id='$localised_scope_post_name' name='$localised_scope_post_name' readonly='readonly' >";
            $replacement .= "<input type='hidden' id='$scope_post_name' name='$scope_post_name' value='".eme_sanitize_html($selected_scope)."'>";
            wp_enqueue_script('eme-jquery-datepick');
            wp_enqueue_style('eme-jquery-datepick',EME_PLUGIN_URL."js/jquery-datepick/jquery.datepick.css");
            // jquery ui locales are with dashes, not underscores
            $locale_code = get_locale();
            $locale_code = preg_replace( "/_/","-", $locale_code );
            $locale_file = EME_PLUGIN_DIR. "js/jquery-datepick/jquery.datepick-$locale_code.js";
            $locale_file_url = EME_PLUGIN_URL. "js/jquery-datepick/jquery.datepick-$locale_code.js";
            // for english, no translation code is needed)
            if ($locale_code != "en-US") {
               if (!file_exists($locale_file)) {
                  $locale_code = substr ( $locale_code, 0, 2 );
                  $locale_file = EME_PLUGIN_DIR. "js/jquery-datepick/jquery.datepick-$locale_code.js";
                  $locale_file_url = EME_PLUGIN_URL. "js/jquery-datepick/jquery.datepick-$locale_code.js";
               }
               if (file_exists($locale_file))
                  wp_enqueue_script('eme-jquery-datepick-locale',$locale_file_url);
            }

            ob_start();
            ?>
            <script type="text/javascript">
            var locale_code = '<?php echo $locale_code;?>';
            var firstDayOfWeek = <?php echo get_option('start_of_week');?>;
            </script>
            <?php
            $replacement .= ob_get_clean();
            $replacement .= "<script type='text/javascript' src='".EME_PLUGIN_URL."js/eme_filters.js'></script>";
         }
      } elseif (preg_match('/#_FILTER_YEARS/', $result)) {
         if ($scope_fieldcount==0 && (empty($fields) || strstr($fields,'years'))) {
            $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_year_scope($scope_count));
            $scope_fieldcount++;
         }
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
