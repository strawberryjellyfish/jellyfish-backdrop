<?php
if ( !class_exists( 'Jellyfish_Backdrop_Admin' ) ) {
  class Jellyfish_Backdrop_Admin {
    public function __construct() {
      //Set up admin pages
      add_action( 'admin_init', array($this, 'admin_init') );
      add_action( 'admin_menu', array($this, 'settings_menu') );
    }

    public function admin_init() {
      // Register settings
      $this->define_post_metabox();
      register_setting(
        'jellyfish_backdrop_options',
        'jellyfish_backdrop_options',
        array($this, 'validate_options')
      );

      add_settings_section(
        'main_section',
        'Global Settings',
        array($this, 'section_text'),
        'jellyfish_backdrop'
      );

      add_settings_field(
        'jellyfish_backdrop_default_background',
        'Default background image',
        array($this, 'setting_default_background'),
        'jellyfish_backdrop',
        'main_section'
      );

      add_settings_field(
        'jellyfish_backdrop_show_default',
        'Enable default background',
        array($this, 'setting_show_default'),
        'jellyfish_backdrop',
        'main_section'
      );

      add_settings_field(
        'jellyfish_backdrop_use_postmeta',
        'Enable Page & Post Slideshows',
        array($this, 'setting_use_postmeta'),
        'jellyfish_backdrop',
        'main_section'
      );

      add_settings_field(
        'jellyfish_backdrop_text_slide_duration',
        'Side duration (seconds)',
        array($this, 'setting_slide_duration'),
        'jellyfish_backdrop',
        'main_section'
      );

      add_settings_field(
        'jellyfish_backdrop_text_fade_speed',
        'Fade speed (seconds)',
        array($this, 'setting_fade_speed'),
        'jellyfish_backdrop',
        'main_section'
      );

      add_settings_field(
        'jellyfish_backdrop_container',
        'HTML container ID/class',
        array($this, 'setting_container'),
        'jellyfish_backdrop',
        'main_section'
      );
    }

    public function settings_menu() {
      // Add sub page to the Appearance Menu
      global $jellyfish_backdrop_settings_page_hook;
      $jellyfish_backdrop_settings_page_hook= add_options_page(
        'Jellyfish Backdrop Slideshow Settings',
        'Backdrop Slideshow',
        'manage_options',
        'jellyfish-backdrop',
        array($this, 'plugin_settings_page')
      );
      add_action( "load-{$jellyfish_backdrop_settings_page_hook}", array($this, 'enqueue_settings_script') );
    }

    function enqueue_settings_script() {
      // Enqueue Meta Box Scripts
      wp_enqueue_script( 'jquery-ui-core' );
      wp_enqueue_script( 'jquery-ui-sortable' );
      wp_enqueue_script( 'jquery-ui-slider' );
      wp_enqueue_script( 'media-upload' );
      wp_enqueue_media();
      add_thickbox();
      wp_enqueue_script( 'at-meta-box', plugins_url( '/jellyfish-backdrop/meta-box-class/js/meta-box.js'), array( 'jquery' ), null, true );
      wp_enqueue_style( 'at-meta-box', plugins_url( '/jellyfish-backdrop/meta-box-class/css/meta-box.css') );
      wp_enqueue_style( 'at-jquery-ui-css', plugins_url( '/jellyfish-backdrop/meta-box-class/js/jquery-ui/jquery-ui.css' ) );
    }

    public function setting_default_background() {
      $options = get_option( 'jellyfish_backdrop_options' );
      $id = isset($options['id']) ? $options['id'] : '';
      $url = isset($options['url']) ? $options['url'] : '';
      if (isset($options['id'])) {
        $image = wp_get_attachment_image_src( $options['id'], 'thumbnail' );
      } else {
       $image = array( plugins_url( '/jellyfish-backdrop/meta-box-class/images/photo.png' ), 150, 150 );
      }
      $width = get_option( 'thumbnail_size_w' );
      $height = get_option( 'thumbnail_size_h' );

      echo "<div class='simplePanelImagePreview'>";
      echo "<img class='thumbnail' src='{$image[0]}' style='height: auto; max-height: {$height}px; width: auto; max-width: {$width}px;' />";
      echo "</div>";
      echo "<input type='hidden' name='jellyfish_backdrop_options[id]' value='{$id}'/>";
      echo "<input type='hidden' name='jellyfish_backdrop_options[url]' value='{$url}'/>";
      if ( empty($options['url']) ) {
        echo "<input class='button simplePanelimageUpload' id='default_image' value='Upload Image' type='button'/>";
      } else {
        echo "<input class='button  simplePanelimageUploadclear' id='default_image' value='Remove Image' type='button'/>";
      }

    }

    public function section_text() {
      // print section HTML
      echo '<p>You can configure the default settings for the backdrop slideshow here, as well as enable or disable the default backdrop or post and page specific slideshows..</p>';
    }

    public function setting_slide_duration() {
      $options = get_option( 'jellyfish_backdrop_options' );
      $value = isset($options['slide_duration']) ? $options['slide_duration'] : 10;
      echo "<div id='jellyfish_backdrop_slide_duration_slider' class='at-slider' data-value='".$value."' data-min='0' data-max='30' data-step='0.1'></div>";
      echo "<input type='text' class='at-text' name='jellyfish_backdrop_slide_duration' id='jellyfish_backdrop_slide_duration' value='{$value}' size='5' />";
      echo "<div class='desc-field'>Time in seconds</div>";
    }

    public function setting_container() {
      $options = get_option( 'jellyfish_backdrop_options' );
      $value = isset($options['container']) ? $options['container'] : 'body';
      echo "<input id='jellyfish_backdrop_container' name='jellyfish_backdrop_options[container]' size='40' type='text' value='{$value}' />";
      echo "<div class='desc-field'>The HTML element, id or class to apply teh background to (defaults to body)</div>";
    }

    public function setting_fade_speed() {
      $options = get_option( 'jellyfish_backdrop_options' );
      $value = isset($options['fade_speed']) ? $options['fade_speed'] : 0.5;
      echo "<div id='jellyfish_backdrop_fade_speed_slider' class='at-slider' data-value='".$value."' data-min='0' data-max='5' data-step='0.01'></div>";
      echo "<input type='text' class='at-text' name='jellyfish_backdrop_fade_speed' id='jellyfish_backdrop_fade_speed' value='{$value}' size='5' />";
      echo "<div class='desc-field'>Time in seconds</div>";
    }

    public function setting_show_default() {
      $options = get_option( 'jellyfish_backdrop_options' );
      $value = isset($options['show_default']) ? $options['show_default'] : false;
      echo "<input id='jellyfish_backdrop_show_default' name='jellyfish_backdrop_options[show_default]' type='checkbox' ". checked( true, $value, false ). " /> ";
    }

    public function setting_use_postmeta() {
      $options = get_option( 'jellyfish_backdrop_options' );
      $value = isset($options['use_postmeta']) ? $options['use_postmeta'] : false;
      echo "<input id='jellyfish_backdrop_use_postmeta' name='jellyfish_backdrop_options[use_postmeta]' type='checkbox' ". checked( true, $value, false ). " /> ";
    }

    public function plugin_settings_page() {
      // Print admin options page
      if(!current_user_can('manage_options')) {
          wp_die(__('You do not have sufficient permissions to access this page.'));
      }
    ?>
      <div class="wrap">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2>Jellyfish Backdrop Slideshow Settings</h2>
        <form action="options.php" method="post">
        <?php settings_fields( 'jellyfish_backdrop_options' ); ?>
        <?php do_settings_sections( 'jellyfish_backdrop' ); ?>
        <p class="submit">
          <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
        </p>
        </form>
      </div>
    <?php
    }

    function define_post_metabox() {
      // Add custom post meta box
      require_once("meta-box-class/my-meta-box-class.php");
      $jb_prefix = '_jellyfish_backdrop_';

      $jb_config = array(
        'id' => 'jellyfish_backdrop',
        'title' => 'Backdrop Slideshow',
        'pages' => array('post', 'page'),
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(),
        'local_images' => false,
        'use_with_theme' => false
      );

      $jb_meta =  new AT_Meta_Box($jb_config);

      $jb_meta->add_text('_jellyfish_backdrop_container',
        array(
          'name'=> 'Containing Element',
          'desc' => 'id or class of a page element to place the images in, defaults to body (full page)',
          'std' => 'body',
          'class' => 'regular-text'
        )
      );

      $jb_meta->add_slider('_jellyfish_backdrop_slide_duration',
        array(
          'name'=> 'Slide Duration',
          'desc' => 'How long to show each image (in seconds)',
          'std' => '5',
          'min' => '0',
          'max' => '30',
          'step' => '0.1',
          'class' => ''
        )
      );

      $jb_meta->add_slider('_jellyfish_backdrop_fade_speed',
        array(
          'name'=> 'Fade Speed',
          'desc' => 'Speed of fade between images (in seconds)',
          'std' => '0.5',
          'min' => '0',
          'max' => '5',
          'step' => '0.01',
          'class' => ''
        )
      );

      $jb_repeater_fields[] = $jb_meta->add_image('_jellyfish_backdrop_image',
        array(
          'name'=> '',
          'size' => 'thumbnail',
          'hide_remove' => true
        ),
        true
      );

      $jb_meta->add_repeater_block('_jellyfish_backdrop_images',
        array(
          'name' => 'Background Images',
          'fields' => $jb_repeater_fields,
          'inline'   => true,
          'sortable' => true
        )
      );

      $jb_meta->finish();
    }

    function validate_options( $input ) {
      // return array of valid options
      $valid['url'] =  esc_url( $input['url'] );
      $valid['id'] =  intval( $input['id'] );
      $valid['container'] =  wp_filter_nohtml_kses( $input['container'] );
      if (is_numeric($input['slide_duration']) && ($input['slide_duration'] > 0))
        $valid['slide_duration'] =  sanitize_text_field($input['slide_duration']);
      if (is_numeric($input['fade_speed']) && ($input['fade_speed'] >= 0))
        $valid['fade_speed'] =  sanitize_text_field($input['fade_speed']);
      if ( $input['use_postmeta'] == true ) {
        $valid['use_postmeta'] = true;
      } else {
        $valid['use_postmeta'] = false;
      }

      if ( $input['show_default'] == true ) {
        $valid['show_default'] = true;
      } else {
        $valid['show_default'] = false;
      }

      return $valid;
    }

  }
}
?>