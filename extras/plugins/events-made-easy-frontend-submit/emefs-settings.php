<?php
if (!function_exists('is_admin')) {
   header('Status: 403 Forbidden');
   header('HTTP/1.1 403 Forbidden');
   exit();
}

if (!class_exists("EMEFS_Settings")) :

   class EMEFS_Settings {

      public static $default_settings = 
         array(   
               'auto_publish' => STATUS_PUBLIC,
               'guest_submit' => false,
               'success_page' => 0,
               'guest_not_allowed_page' => 0
              );
      var $pagehook, $page_id, $settings_field, $options;


      function __construct() {   
         $this->page_id = 'emefs';
         // This is the get_options slug used in the database to store our plugin option values.
         $this->settings_field = 'emefs_options';
         $this->options = get_option( $this->settings_field );

         add_action('admin_init', array($this,'admin_init'), 20 );
         add_action( 'admin_menu', array($this, 'admin_menu'), 20);
      }

      function admin_init() {
         register_setting( $this->settings_field, $this->settings_field, array($this, 'sanitize_theme_options') );
         add_option( $this->settings_field, emefs_settings::$default_settings );
      }

      function admin_menu() {
         if ( ! current_user_can('update_plugins') )
            return;

         // Add a new submenu to the standard Settings panel
         $this->pagehook = $page =  add_options_page( 
               __('EME Frontend Submit', 'emefs'), __('EME Frontend Submit', 'emefs'), 
               'administrator', $this->page_id, array($this,'render') );

         // Executed on-load. Add all metaboxes.
         add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

         // Include js, css, or header *only* for our settings page
         add_action("admin_print_scripts-$page", array($this, 'js_includes'));
         //    add_action("admin_print_styles-$page", array($this, 'css_includes'));
         add_action("admin_head-$page", array($this, 'admin_head') );
      }

      function admin_head() { ?>
         <style>
            .settings_page_emefs_submit label { display:inline-block; width: 150px; }
         </style>

            <?php }


      function js_includes() {
         // Needed to allow metabox layout and close functionality.
         wp_enqueue_script( 'postbox' );
      }


      /*
         Sanitize our plugin settings array as needed.
       */ 
      function sanitize_theme_options($options) {
         //$options['example_text'] = stripcslashes($options['example_text']);
         return $options;
      }


      /*
         Settings access functions.

       */
      protected function get_field_name( $name ) {
         return sprintf( '%s[%s]', $this->settings_field, $name );
      }

      protected function get_field_id( $id ) {
         return sprintf( '%s[%s]', $this->settings_field, $id );
      }

      protected function get_field_value( $key ) {
         return $this->options[$key];
      }


      /*
         Render settings page.

       */

      function render() {
         global $wp_meta_boxes;

         $title = __('Events Made Easy Frontend Submit Settings', 'emefs');
         ?>
            <div class="wrap">   
            <h2><?php echo esc_html( $title ); ?></h2>

            <form method="post" action="options.php">

            <div class="metabox-holder">
            <div class="postbox-container" style="width: 99%;">
            <?php 
            // Render metaboxes
            settings_fields($this->settings_field); 
            do_meta_boxes( $this->pagehook, 'main', null );
            if ( isset( $wp_meta_boxes[$this->pagehook]['column2'] ) )
               do_meta_boxes( $this->pagehook, 'column2', null );
         ?>
            </div>
            </div>

            <p>
            <input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
            </p>
            </form>
            </div>

            <!-- Needed to allow metabox layout and close functionality. -->
            <script type="text/javascript">
            //<![CDATA[
            jQuery(document).ready( function ($) {
                  // close postboxes that should be closed
                  $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                  // postboxes setup
                  postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
                  });
         //]]>
         </script>
            <?php }


      function metaboxes() {
         // Example metabox containing two example checkbox controls.
         // Also includes and example input text box, rendered in HTML in the condition_box function
         add_meta_box( 'emefs-conditions', __( 'Events Made Easy Frontend Submit Settings', 'emefs' ), array( $this, 'condition_box' ), $this->pagehook, 'main' );
      }

      function condition_box() {
         ?>
          <table class="form-table">
          <?php
          eme_options_select (__('State for new event','emefs'), $this->get_field_name('auto_publish'), eme_status_array(), __ ('The state for a newly submitted event.','emefs'), $this->get_field_value('auto_publish') );
          eme_options_radio_binary (__('Allow guest submit?','emefs'), $this->get_field_name('guest_submit'), __ ( 'Check this option if you want guests also to be able to add new events.', 'emefs' ), $this->get_field_value('guest_submit'));
          eme_options_select ( __ ( 'Success Page','emefs'), $this->get_field_name('success_page'), eme_get_all_pages (), __ ( 'The page a person will be redirected to after successfully submitting a new event if the person submitting the event has no right to see the newly submitted event.','emefs'), $this->get_field_value('success_page'));
          eme_options_select ( __ ( 'Guests not allowed page','emefs'), $this->get_field_name('guest_not_allowed_page'), eme_get_all_pages (), __ ( 'The page a guest will be redirected to when trying to submit a new event when they are not allowed to do so.','emefs'), $this->get_field_value('guest_not_allowed_page'));
          ?>
          </table>
          <?php
      }

   } // end class
endif;
?>
