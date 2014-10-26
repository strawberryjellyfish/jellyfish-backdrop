<?php
/*
  Plugin Name: Jellyfish Backdrop
  Plugin URI: http://strawberryjellfish.com/wordpress-plugins/jellyfish-backdrop/
  Description: Add fullscreen background images and background slideshows to any WordPress page element.
  Author: Robert Miller <rob@strawberryjellyfish.com>
  Version: 0.5
  Author URI: http://strawberryjellyfish.com/
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Online: http://www.gnu.org/licenses/gpl.txt
*/


// set default options
register_activation_hook( __FILE__, 'add_jellyfish_backdrop_defaults' );

// add admin page
add_action( 'admin_menu', 'jellyfish_backdrop_settings_menu' );

// Register and define the settings
add_action( 'admin_init', 'jellyfish_backdrop_init' );

if ( is_admin() ) {
  // Set up custom meta box
  require_once("meta-box-class/my-meta-box-class.php");
    require_once("meta-box-class/my-meta-box-class.php");
    $prefix = 'jellyfish_backdrop_';
    /*
     * configure your meta box
     */
    $config = array(
        'id' => 'jellyfish_backdrop',
        'title' => 'Backdrop Slideshow',
        'pages' => array('post', 'page'),
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(),
        'local_images' => false,
        'use_with_theme' => false
    );
    $jb_meta =  new AT_Meta_Box($config);

    $jb_meta->addText('_jellyfish_backdrop_container',
      array(
        'name'=> 'Containing Element',
        'desc' => 'id or class of a page element to place the images in, defaults to body (full page)',
        'std' => 'body'
      )
    );
    $jb_meta->addText('_jellyfish_backdrop_slide_duration',
      array(
        'name'=> 'Slide Duration',
        'desc' => 'How long to show each image (in seconds)',
        'std' => '5000'
      )
    );
    $jb_meta->addText('_jellyfish_backdrop_fade_speed',
      array(
        'name'=> 'Fade Speed',
        'desc' => 'Speed of fade between images (in seconds)',
        'std' => '500'
      )
    );

    $repeater_fields[] = $jb_meta->addImage('_jellyfish_backdrop_image',
      array(
        'name'=> 'Image'
      ), true);

    $jb_meta->addRepeaterBlock('_jellyfish_backdrop_images',
      array(
        'name' => 'Background Images',
        'fields' => $repeater_fields,
        'sortable' => true
      )
    );

    $jb_meta->Finish();

} else {
  // Queue JavaScript for frontend only
  add_action( 'wp_enqueue_scripts', 'jellyfish_backdrop_queue_scripts' );
}

function jellyfish_backdrop_queue_scripts() {
  // to save unnecessary requests and bandwidth, only include js where
  // actually needed

  $jellyfish_backdrop_options = get_option( 'jellyfish_backdrop_options' );
  $js_needed = false;

  if ( $jellyfish_backdrop_options['show_default'] == true ) {
    // default on so we always need to print scripts
    $js_needed = true;

  } elseif ( $jellyfish_backdrop_options['use_postmeta'] == true ) {
    // use postmeta is on, queue JavaScript if a post has a background
    if ( is_single() or is_page() ) {
      if ( get_post_meta( get_the_ID(), 'jellyfish_backdrop_image', true ) ) {
        $js_needed = true;
      }
    }
  }

  if ( $js_needed ) {
    // enqueue jQuery library and js to page footer
    wp_register_script( 'backstretch', plugins_url( 'js/jquery.backstretch.min.js' , __FILE__ ), array( 'jquery' ), '', true );
    wp_enqueue_script( 'backstretch' );
    add_action( 'wp_footer', 'jellyfish_backdrop_print_script', 100 );
  }
}

// default option settings
function add_jellyfish_backdrop_defaults() {
  if ( get_option( 'jellyfish_backdrop_options' ) === false ) {
    $arr = array(
      'default_background' => plugins_url( 'images/demo_background.jpg', __FILE__ ),
      'container' => 'body',
      'slide_duration' => 5000,
      'fade_speed' => 500,
      'show_default' => true,
      'use_postmeta' => true
    );
    update_option( 'jellyfish_backdrop_options', $arr );
  }
}

// Register settings
function jellyfish_backdrop_init() {
  register_setting( 'jellyfish_backdrop_options', 'jellyfish_backdrop_options', 'jellyfish_backdrop_options_validate' );
  add_settings_section( 'main_section', 'Global Settings', 'jellyfish_backdrop_section_text', __FILE__ );
  add_settings_field( 'jellyfish_backdrop_default_background', 'Global background url', 'setting_default_background', __FILE__, 'main_section' );
  add_settings_field( 'jellyfish_backdrop_container', 'HTML container ID/class', 'setting_container', __FILE__, 'main_section' );
  add_settings_field( 'jellyfish_backdrop_show_default', 'Show global background', 'setting_show_default', __FILE__, 'main_section' );
  add_settings_field( 'jellyfish_backdrop_use_postmeta', 'Use custom fields', 'setting_use_postmeta', __FILE__, 'main_section' );
  add_settings_field( 'jellyfish_backdrop_text_slide_duration', 'Background slideshow speed', 'setting_slide_duration', __FILE__, 'main_section' );
  add_settings_field( 'jellyfish_backdrop_text_fade_speed', 'Background fade speed', 'setting_fade_speed', __FILE__, 'main_section' );
}

// Add sub page to the Appearance Menu
function jellyfish_backdrop_settings_menu() {
  // this plugin is purely for visual effect it is therefore more logical to
  // add the options page to the Appearance section
  add_theme_page( 'Jellyfish Backdrop Slideshow Settings', 'Backdrop Slideshow', 'manage_options', 'jellyfish-backdrop', 'jellyfish_backdrop_config_page' );
}

// print section HTML
function jellyfish_backdrop_section_text() {
  echo '<p>If the <em>Show Global Background</em> is checked and an image url provided that image will be used as the background for the entire website. This will override any theme background.</p>';
  echo '<p>If the <em>Use Custom Fields</em> option is checked the plugin will also look for any images attached to an individual post or page using the <b>background_image</b> custom field. If a post or page has more than one <b>background_image</b> custom field the images will be displayed as a slideshow.</p>';
}

// option form element functions

function setting_slide_duration() {
  $options = get_option( 'jellyfish_backdrop_options' );
  echo "<input id='jellyfish_backdrop_slide_duration' name='jellyfish_backdrop_options[slide_duration]' size='40' type='text' value='{$options['slide_duration']}' />";
}

function setting_default_background() {
  $options = get_option( 'jellyfish_backdrop_options' );
  echo "<input id='jellyfish_backdrop_default_background' name='jellyfish_backdrop_options[default_background]' size='40' type='text' value='{$options['default_background']}' />";
}

function setting_container() {
  $options = get_option( 'jellyfish_backdrop_options' );
  echo "<input id='jellyfish_backdrop_container' name='jellyfish_backdrop_options[container]' size='40' type='text' value='{$options['container']}' />";
}

function setting_fade_speed() {
  $options = get_option( 'jellyfish_backdrop_options' );
  echo "<input id='jellyfish_backdrop_fade_speed' name='jellyfish_backdrop_options[fade_speed]' size='40' type='text' value='{$options['fade_speed']}' />";
}

function setting_show_default() {
  $options = get_option( 'jellyfish_backdrop_options' );
  echo "<input id='jellyfish_backdrop_show_default' name='jellyfish_backdrop_options[show_default]' type='checkbox' ". checked( true, $options['show_default'], false ). " /> ";
}

function setting_use_postmeta() {
  $options = get_option( 'jellyfish_backdrop_options' );
  echo "<input id='jellyfish_backdrop_use_postmeta' name='jellyfish_backdrop_options[use_postmeta]' type='checkbox' ". checked( true, $options['use_postmeta'], false ). " /> ";
}

// Print admin options page
function jellyfish_backdrop_config_page() {
?>
  <div class="wrap">
    <div class="icon32" id="icon-options-general"><br></div>
    <h2>Jellyfish Backdrop Slideshow Settings</h2>
    <p>
      Jellyfish Backdrop allows you to display define background images and
      background slideshows for almost any part of your blog. You can either
      display images as fullscreen backgrounds or as the background to a specific
      area of the page.</p>
    <p>
      You may define individual backgrounds or slideshows for specific
      posts or pages making it easy to make a homepage slideshow for example.
    </p>
    <form action="options.php" method="post">
    <?php settings_fields( 'jellyfish_backdrop_options' ); ?>
    <?php do_settings_sections( __FILE__ ); ?>
    <p class="submit">
      <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    </p>
    </form>
  </div>
<?php
}

// Validate user data
function jellyfish_backdrop_options_validate( $input ) {
  $valid['default_background'] =  wp_filter_nohtml_kses( $input['default_background'] );
  $valid['container'] =  wp_filter_nohtml_kses( $input['container'] );
  $valid['slide_duration'] =  absint( $input['slide_duration'] );
  $valid['fade_speed'] =  absint( $input['fade_speed'] );
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

  return $valid; // return validated input
}

// Print out backdrop JavaScript
function jellyfish_backdrop_print_script() {
  global $wpdb, $post_id;
  $options = get_option( 'jellyfish_backdrop_options' );
  $fade_speed = is_numeric($options['fade_speed']) ? $options['fade_speed'] : 500;
  $slide_duration = is_numeric($options['slide_duration']) ? $options['slide_duration'] : 500;
  $container = $options['container'] ? $options['container'] :'body';
  $image_array = array();
  if ( ( $options['use_postmeta'] == true ) && ( is_single() or is_page() ) ) {
    $images = get_post_meta(get_the_ID(), '_jellyfish_backdrop_images', true);
    foreach ($images as $arr){
      array_push($image_array, $arr['_jellyfish_backdrop_image']['url']);
    }

    $post_container = get_post_meta(get_the_ID(), '_jellyfish_backdrop_container', true );
    if ($post_container) $container = $post_container;

    $post_fade_speed = get_post_meta(get_the_ID(), '_jellyfish_backdrop_fade_speed', true );
    if ($post_fade_speed) $fade_speed = $post_fade_speed;

    $post_slide_duration = get_post_meta(get_the_ID(), '_jellyfish_backdrop_slide_duration', true );
    if ($post_slide_duration) $slide_duration = $post_slide_duration;
  }

  if ( ( $options['show_default'] == true ) && empty($image_array)) {
    array_push($image_array, $options['default_background']);
  }

if ( !empty($image_array) ) {
  echo "
<script>
  jQuery(document).ready(function($) {
    var sjb_backgrounds = " . json_encode($image_array) . ";
    $('$container').backstretch(sjb_backgrounds,
      {speed: $fade_speed, duration: $slide_duration}
    );
  });
</script>";
  }
}
?>