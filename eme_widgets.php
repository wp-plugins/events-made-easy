<?php

class WP_Widget_eme_list extends WP_Widget {
   function WP_Widget_eme_list() {
      $widget_ops = array('classname' => 'widget_eme_list', 'description' => __( 'Events List','eme' ) );
      $this->WP_Widget('eme_list', __('Events List','eme'), $widget_ops);
      $this->alt_option_name = 'widget_eme_list';
   }
   function widget( $args, $instance ) {
      extract($args);
      //$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Events','eme' ) : $instance['title'], $instance, $this->id_base);
      $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
      $limit = isset( $instance['limit'] ) ? intval($instance['limit']) : 5;
      $scope = empty( $instance['scope'] ) ? 'future' : $instance['scope'];
      $showperiod = empty( $instance['showperiod'] ) ? '' : $instance['showperiod'];
      $show_ongoing = isset( $instance['show_ongoing'] ) ? $instance['show_ongoing'] : true;
      $order = empty( $instance['order'] ) ? 'ASC' : $instance['order'];
      $header = empty( $instance['header'] ) ? '<ul>' : $instance['header'];
      $footer = empty( $instance['footer'] ) ? '</ul>' : $instance['footer'];
      $category = empty( $instance['category'] ) ? '' : $instance['category'];
      $notcategory = empty( $instance['notcategory'] ) ? '' : $instance['notcategory'];
      $recurrence_only_once = empty( $instance['recurrence_only_once'] ) ? false : $instance['recurrence_only_once'];
      $format = empty( $instance['format'] ) ? DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT : $instance['format'];

      if ($instance['authorid']==-1 ) {
         $author='';
      } else {
         $authinfo=get_userdata($instance['authorid']);
         $author=$authinfo->user_login;
      }
      echo $before_widget;
      if ( $title)
         echo $before_title . $title . $after_title;

      $events_list = eme_get_events_list($limit,$scope,$order,$format,false,$category,$showperiod,0,$author,'',0,'',0,$show_ongoing,0,$notcategory,$recurrence_only_once);
      if ($events_list == get_option('eme_no_events_message' ))
         echo $events_list;
      else
         echo $header.$events_list.$footer;
      echo $after_widget;
   }
   function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['limit'] = intval($new_instance['limit']);
      $instance['scope'] = $new_instance['scope'];
      if ( in_array( $new_instance['showperiod'], array( 'daily', 'monthly', 'yearly' ) ) ) {
         $instance['showperiod'] = $new_instance['showperiod'];
      } else {
         $instance['showperiod'] = '';
      }
      if ( in_array( $new_instance['order'], array( 'ASC', 'DESC' ) ) ) {
         $instance['order'] = $new_instance['order'];
      } else {
         $instance['order'] = 'ASC';
      }
      $instance['show_ongoing'] = $new_instance['show_ongoing'];
      $instance['category'] = $new_instance['category'];
      $instance['notcategory'] = $new_instance['notcategory'];
      $instance['recurrence_only_once'] = $new_instance['recurrence_only_once'];
      $instance['format'] = $new_instance['format'];
      $instance['authorid'] = $new_instance['authorid'];
      $instance['header'] = $new_instance['header'];
      $instance['footer'] = $new_instance['footer'];
      return $instance;
   }
   function form( $instance ) {
      //Defaults
      $instance = wp_parse_args( (array) $instance, array( 'limit' => 5, 'scope' => 'future', 'order' => 'ASC', 'format' => DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT, 'authorid' => '', 'show_ongoing'=> 1 ) );
      $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
      $limit = isset( $instance['limit'] ) ? intval($instance['limit']) : 5;
      $scope = empty( $instance['scope'] ) ? 'future' : eme_sanitize_html($instance['scope']);
      $showperiod = empty( $instance['showperiod'] ) ? '' : eme_sanitize_html($instance['showperiod']);
      if ( isset( $instance['show_ongoing'] ) && ( $instance['show_ongoing'] != false ))
         $show_ongoing = true;
      else
         $show_ongoing = false;
      $order = empty( $instance['order'] ) ? 'ASC' : eme_sanitize_html($instance['order']);
      $header = empty( $instance['header'] ) ? '<ul>' : eme_sanitize_html($instance['header']);
      $footer = empty( $instance['footer'] ) ? '</ul>' : eme_sanitize_html($instance['footer']);
      $category = empty( $instance['category'] ) ? '' : eme_sanitize_html($instance['category']);
      $notcategory = empty( $instance['notcategory'] ) ? '' : eme_sanitize_html($instance['notcategory']);
      $recurrence_only_once = empty( $instance['recurrence_only_once'] ) ? '' : eme_sanitize_html($instance['recurrence_only_once']);
      $authorid = empty( $instance['authorid'] ) ? '' : eme_sanitize_html($instance['authorid']);
      $categories = eme_get_categories();
      $format = empty( $instance['format'] ) ? DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT : $instance['format'];
?>
  <p>
   <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
   <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of events','eme'); ?>: </label>
    <input type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $limit;?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('scope'); ?>"><?php _e('Scope of the events','eme'); ?><br /><?php _e('(See the doc for &#91;events_list] for all possible values)'); ?>:</label><br />
    <input type="text" id="<?php echo $this->get_field_id('scope'); ?>" name="<?php echo $this->get_field_name('scope'); ?>" value="<?php echo $scope;?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('showperiod'); ?>"><?php _e('Show events per period','eme'); ?>:</label><br />
   <select id="<?php echo $this->get_field_id('showperiod'); ?>" name="<?php echo $this->get_field_name('showperiod'); ?>">
         <option value="" <?php selected( $showperiod, '' ); ?>><?php _e('Select...','eme'); ?></option>
         <option value="daily" <?php selected( $showperiod, 'daily' ); ?>><?php _e('Daily','eme'); ?></option>
         <option value="monthly" <?php selected( $showperiod, 'monthly' ); ?>><?php _e('Monthly','eme'); ?></option>
         <option value="yearly" <?php selected( $showperiod, 'yearly' ); ?>><?php _e('Yearly','eme'); ?></option>
    </select>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order of the events','eme'); ?>:</label><br />
    <select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
         <option value="ASC" <?php selected( $order, 'ASC' ); ?>><?php _e('Ascendant','eme'); ?></option>
         <option value="DESC" <?php selected( $order, 'DESC' ); ?>><?php _e('Descendant','eme'); ?></option>
    </select>
  </p>
<?php
  if(get_option('eme_categories_enabled')) {
?>
  <p>
    <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category','eme'); ?>:</label><br />
    <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
      <option value=""><?php _e ( 'Select...', 'eme' ); ?></option>
      <?php
      foreach ( $categories as $my_category ){
      ?>
         <option value="<?php echo $my_category['category_id']; ?>" <?php selected( $category,$my_category['category_id']); ?>><?php echo $my_category['category_name']; ?></option>
      <?php
      }
      ?>
    </select>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('notcategory'); ?>"><?php _e('Exclude Category','eme'); ?>:</label><br />
    <select id="<?php echo $this->get_field_id('notcategory'); ?>" name="<?php echo $this->get_field_name('notcategory'); ?>">
      <option value=""><?php _e ( 'Select...', 'eme' ); ?></option>
      <?php
      foreach ( $categories as $my_category ){
      ?>
         <option value="<?php echo $my_category['category_id']; ?>" <?php selected( $notcategory,$my_category['category_id']); ?>><?php echo $my_category['category_name']; ?></option>
      <?php
      }
      ?>
    </select>
  </p>
<?php
  }
?>
  <p>
    <label for="<?php echo $this->get_field_id('show_ongoing'); ?>"><?php _e('Show Ongoing Events?', 'eme'); ?>:</label>
    <input type="checkbox" id="<?php echo $this->get_field_id('show_ongoing'); ?>" name="<?php echo $this->get_field_name('show_ongoing'); ?>" value="1" <?php echo ($show_ongoing) ? 'checked="checked"':'' ;?> />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('recurrence_only_once'); ?>"><?php _e('Show Recurrent Events Only Once?', 'eme'); ?>:</label>
    <input type="checkbox" id="<?php echo $this->get_field_id('recurrence_only_once'); ?>" name="<?php echo $this->get_field_name('recurrence_only_once'); ?>" value="1" <?php echo ($recurrence_only_once) ? 'checked="checked"':'' ;?> />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('authorid'); ?>"><?php _e('Author','eme'); ?>:</label><br />
<?php
wp_dropdown_users ( array ('id' => $this->get_field_id('authorid'), 'name' => $this->get_field_name('authorid'), 'show_option_none' => __ ( "Select...", 'eme' ), 'selected' => $authorid ) );
?>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('header'); ?>"><?php _e('List header format<br />(if empty &lt;ul&gt; is used)','eme'); ?>: </label>
    <input type="text" id="<?php echo $this->get_field_id('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" value="<?php echo $header;?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('format'); ?>"><?php _e('List item format','eme'); ?>:</label>
    <textarea id="<?php echo $this->get_field_id('format'); ?>" name="<?php echo $this->get_field_name('format'); ?>" rows="5" cols="24"><?php echo eme_sanitize_html($format);?></textarea>
  </p> 
  <p>
    <label for="<?php echo $this->get_field_id('footer'); ?>"><?php _e('List footer format<br />(if empty &lt;/ul&gt; is used)','eme'); ?>: </label>
    <input type="text" id="<?php echo $this->get_field_id('footer'); ?>" name="<?php echo $this->get_field_name('footer'); ?>" value="<?php echo $footer;?>" />
  </p>
<?php
    }
}     

class WP_Widget_eme_calendar extends WP_Widget {
   function WP_Widget_eme_calendar() {
      $widget_ops = array('classname' => 'widget_eme_calendar', 'description' => __( 'Events Calendar', 'eme' ) );
      $this->WP_Widget('eme_calendar', __('Events Calendar','eme'), $widget_ops);
      $this->alt_option_name = 'widget_eme_calendar';
   }
   function widget( $args, $instance ) {
      global $wp_query;
      extract($args);
      //$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Calendar','eme' ) : $instance['title'], $instance, $this->id_base);
      $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
      $long_events = isset( $instance['long_events'] ) ? $instance['long_events'] : false;
      $category = empty( $instance['category'] ) ? '' : $instance['category'];
      $notcategory = empty( $instance['notcategory'] ) ? '' : $instance['notcategory'];
      if ($instance['authorid']==-1 ) {
         $author='';
      } else {
         $authinfo=get_userdata($instance['authorid']);
         $author=$authinfo->user_login;
      }
      echo $before_widget;
      if ( $title)
         echo $before_title . $title . $after_title;
      
      $options=array();
      $options['title'] = $title;
      $options['long_events'] = $long_events;
      $options['category'] = $category;
      $options['notcategory'] = $notcategory;
      // the month shown depends on the calendar day clicked
      if (get_query_var('calendar_day')) {
          $options['month'] = date("m", strtotime(get_query_var('calendar_day')));
          $options['year'] = date("Y", strtotime(get_query_var('calendar_day')));
      } else {
          $options['month'] = date("m");
          $options['year'] = date("Y");
      }
      $options['author'] = $author;
      eme_get_calendar($options);
      echo $after_widget;
   }
   
   function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['category'] = $new_instance['category'];
      $instance['notcategory'] = $new_instance['notcategory'];
      $instance['long_events'] = $new_instance['long_events'];
      $instance['authorid'] = $new_instance['authorid'];
      return $instance;
   }

   function form( $instance ) {
      //Defaults
      $instance = wp_parse_args( (array) $instance, array( 'long_events' => 0 ) );
      $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
      $category = empty( $instance['category'] ) ? '' : eme_sanitize_html($instance['category']);
      $notcategory = empty( $instance['notcategory'] ) ? '' : eme_sanitize_html($instance['notcategory']);
      $long_events = isset( $instance['long_events'] ) ? eme_sanitize_html($instance['long_events']) : false;
      $authorid = isset( $instance['authorid'] ) ? eme_sanitize_html($instance['authorid']) : '';
      $categories = eme_get_categories();
?>
  <p>
   <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
   <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
  </p>      
  <p>
    <label for="<?php echo $this->get_field_id('long_events'); ?>"><?php _e('Show Long Events?', 'eme'); ?>:</label>
    <input type="checkbox" id="<?php echo $this->get_field_id('long_events'); ?>" name="<?php echo $this->get_field_name('long_events'); ?>" value="1" <?php echo ($long_events) ? 'checked="checked"':'' ;?> />
  </p>
  <?php
      if(get_option('eme_categories_enabled')) {
  ?>
  <p>
    <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category','eme'); ?>:</label><br />
   <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
      <option value=""><?php _e ( 'Select...', 'eme' ); ?>   </option>
      <?php
      foreach ( $categories as $my_category ){
      ?>
      <option value="<?php echo $my_category['category_id']; ?>" <?php selected( $category,$my_category['category_id']); ?>><?php echo $my_category['category_name']; ?></option>
      <?php
      }
      ?>
   </select>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('notcategory'); ?>"><?php _e('Exclude Category','eme'); ?>:</label><br />
   <select id="<?php echo $this->get_field_id('notcategory'); ?>" name="<?php echo $this->get_field_name('notcategory'); ?>">
      <option value=""><?php _e ( 'Select...', 'eme' ); ?>   </option>
      <?php
      foreach ( $categories as $my_category ){
      ?>
      <option value="<?php echo $my_category['category_id']; ?>" <?php selected( $notcategory,$my_category['category_id']); ?>><?php echo $my_category['category_name']; ?></option>
      <?php
      }
      ?>
   </select>
  </p>
<?php
      }
?>
  <p>
    <label for="<?php echo $this->get_field_id('authorid'); ?>"><?php _e('Author','eme'); ?>:</label><br />
<?php
wp_dropdown_users ( array ('id' => $this->get_field_id('authorid'), 'name' => $this->get_field_name('authorid'), 'show_option_none' => __ ( "Select...", 'eme' ), 'selected' => $authorid ) );
?>
  </p>
<?php
   } 
}

?>
