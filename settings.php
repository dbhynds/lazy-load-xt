<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class LazyLoadXTSettings {

  const ver = '0.5.3'; // Plugin version
  const ns = 'lazy-load-xt';
  protected $defaults = array(
    'general' => array(
      'lazyloadxt_minimize_scripts' => 1,
      'lazyloadxt_thumbnails' => 1,
      'lazyloadxt_textwidgets' => 1,
      'lazyloadxt_avatars' => 1,
      'lazyloadxt_load_extras' => 1,
      'lazyloadxt_excludeclasses' => '',
      'lazyloadxt_img' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
      'lazyloadxt_responsive' => 1,
    ),
  );

  public function __construct() {
    add_action( 'admin_menu', array($this,'lazyloadxt_add_admin_menu') );
    add_action( 'admin_init', array($this,'lazyloadxt_settings_init') );
    add_action( 'admin_enqueue_scripts', array($this,'lazyloadxt_enqueue_admin') );
    add_action( 'upgrader_process_complete', array($this,'update') );
  }

  function first_time_activation() {
    // Set default settings
    $defaults = $this->defaults;
    foreach ($defaults as $key => $val) {
      if (get_option('lazyloadxt_'.$key,false) == false) {
        update_option('lazyloadxt_'.$key,$val);
      }
    }
    update_option('lazyloadxt_version',self::ver);
  }

  /**
   * Set default option values for new options when the plugin is updated.
   *
   * @since Lazy Load XT 0.3.0
   */
  function upgrade($to) {
    error_log($to);
    if (version_compare($dbver,'0.2','<=')) {
      $this->first_time_activation();
    }
    if (version_compare($to,'0.3','<=')) {
      $general = get_option('lazyloadxt_general');
      $general['lazyloadxt_avatars'] = $this->defaults['general']['lazyloadxt_avatars'];
      update_option('lazyloadxt_general',$general);
    }
    if (version_compare($to,'0.5.0','<=')) {
      $general = get_option('lazyloadxt_general');
      $general['lazyloadxt_responsive'] = $this->defaults['general']['lazyloadxt_responsive'];
      error_log($this->defaults['general']['lazyloadxt_responsive']);
      update_option('lazyloadxt_general',$general);
    }
    if (version_compare($to,'1.0','<=')) {
      if (get_option('lazyloadxt_advanced',false) == false) {
        update_option($this->defaults['advanced']);
      }
    }
  }
  
  function update() {
    $defaults = $this->defaults;
    $ver = self::ver;
    $dbver = get_option('lazyloadxt_version','');
    if (version_compare($ver,$dbver,'>')) {
      if (version_compare($dbver,'0.2','<=')) {
        $this->first_time_activation();
      } elseif (version_compare($dbver,'0.3','<=')) {
        $this->upgrade('0.3');
      }
      update_option('lazyloadxt_version',$ver);
    }
  }


  function lazyloadxt_add_admin_menu() { 
    $admin_page = add_options_page( 'Lazy Load XT', 'Lazy Load XT', 'manage_options', 'lazyloadxt', array($this,'settings_page') );
  }
  function lazyloadxt_enqueue_admin() {
    $screen = get_current_screen();
    if ($screen->base == 'settings_page_lazyloadxt') {
      wp_enqueue_style('thickbox-css');
      add_action( 'admin_notices', array($this,'ask_for_feedback') );
    }
  }
  
  /**
   * Ask users for feedback about the plugin.
   *
   * @since Lazy Load XT 0.3.0
   */
  function ask_for_feedback() {
    ?>
    <div class="updated">
        <p><?php _e( 'Help improve Lazy Load XT: <a href="https://wordpress.org/support/plugin/lazy-load-xt" target="_blank">submit feedback, questions, and bug reports</a>.', self::ns ); ?></p>
    </div>
    <?php
    wp_enqueue_script('thickbox');
  }
  function lazyloadxt_action_links( $links ) {
    $links[] = '<a href="options-general.php?page=lazyloadxt">'.__('Settings',self::ns).'</a>';
    return $links;
  }

  function lazyloadxt_settings_init() {

    register_setting( 'basicSettings', 'lazyloadxt_general' );
    register_setting( 'basicSettings', 'lazyloadxt_effects' );
    register_setting( 'basicSettings', 'lazyloadxt_addons' );

    add_settings_section(
      'lazyloadxt_basic_section',
      __( 'General Settings', self::ns ),
      array($this,'lazyloadxt_basic_section_callback'),
      'basicSettings'
    );

    add_settings_field( 
      'lazyloadxt_general',
      __( 'Basics', self::ns ),
      array($this,'lazyloadxt_general_render'),
      'basicSettings',
      'lazyloadxt_basic_section' 
    );

    add_settings_field( 
      'lazyloadxt_effects',
      __( 'Effects', self::ns ),
      array($this,'lazyloadxt_effects_render'),
      'basicSettings',
      'lazyloadxt_basic_section' 
    );

    add_settings_field( 
      'lazyloadxt_addons',
      __( 'Addons', self::ns ),
      array($this,'lazyloadxt_addons_render'),
      'basicSettings',
      'lazyloadxt_basic_section' 
    );

  }


  /**
   * Output HTML for General Settings.
   *
   * @since Lazy Load XT 0.1.0
   */
  function lazyloadxt_general_render() {

    $options = get_option( 'lazyloadxt_general' );
    ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Basic settings', self::ns ); ?></span>
      </legend>
      <label for="lazyloadxt_minimize_scripts">
        <input type='checkbox' id='lazyloadxt_minimize_scripts' name='lazyloadxt_general[lazyloadxt_minimize_scripts]' <?php $this->checked_r( $options, 'lazyloadxt_minimize_scripts', 1 ); ?> value="1">
        <?php _e('Load minimized versions of javascript and css files.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_cdn">
        <input type='checkbox' id='lazyloadxt_cdn' name='lazyloadxt_general[lazyloadxt_cdn]' <?php $this->checked_r( $options, 'lazyloadxt_cdn', 1 ); ?> value="1">
        <?php _e('Load scripts from cdnjs (v1.0.5).','lazy-load-xt'); ?>
      </label>
      <br />
      <label for="lazyloadxt_footer">
        <input type='checkbox' id='lazyloadxt_footer' name='lazyloadxt_general[lazyloadxt_footer]' <?php $this->checked_r( $options, 'lazyloadxt_footer', 1 ); ?> value="1">
        <?php _e('Load scripts in the footer.','lazy-load-xt'); ?>
      </label>
    </fieldset>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Lazy Load settings','lazy-load-xt'); ?></span>
      </legend>
      <br />
      <label for="lazyloadxt_load_extras">
        <input type='checkbox' id='lazyloadxt_load_extras' name='lazyloadxt_general[lazyloadxt_load_extras]' <?php $this->checked_r( $options, 'lazyloadxt_load_extras', 1 ); ?> value="1">
        <?php _e('Lazy load YouTube and Vimeo videos, iframes, audio, etc.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_responsive">
        <input type='checkbox' id='lazyloadxt_responsive' name='lazyloadxt_general[lazyloadxt_responsive]' <?php $this->checked_r( $options, 'lazyloadxt_responsive', 1 ); ?> value="1">
        <?php _e('Lazy load responsive images.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_thumbnails">
        <input type='checkbox' id='lazyloadxt_thumbnails' name='lazyloadxt_general[lazyloadxt_thumbnails]' <?php $this->checked_r( $options, 'lazyloadxt_thumbnails', 1 ); ?> value="1">
        <?php _e('Lazy load post thumbnails.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_textwidgets">
        <input type='checkbox' id='lazyloadxt_textwidgets' name='lazyloadxt_general[lazyloadxt_textwidgets]' <?php $this->checked_r( $options, 'lazyloadxt_textwidgets', 1 ); ?> value="1">
        <?php _e('Lazy load text widgets.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_avatars">
        <input type='checkbox' id='lazyloadxt_avatars' name='lazyloadxt_general[lazyloadxt_avatars]' <?php $this->checked_r( $options, 'lazyloadxt_avatars', 1 ); ?> value="1">
        <?php _e('Lazy load gravatars.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_excludeclasses">
        <?php _e('Skip lazy loading on these classes:', self::ns ); ?><br />
        <textarea id='lazyloadxt_excludeclasses' name='lazyloadxt_general[lazyloadxt_excludeclasses]' rows="3" cols="60"><?php echo $options['lazyloadxt_excludeclasses']; ?></textarea>
        <p class="description"><?php _e('Prevent objects with the above classes from being lazy loaded. (List classes separated by a space and without the proceding period. e.g. "skip-lazy-load size-thumbnail".)', self::ns ); ?></p>
      </label>
      <br />
      <label for="lazyloadxt_ajax">
        <input type='checkbox' id='lazyloadxt_ajax' name='lazyloadxt_general[lazyloadxt_ajax]' <?php $this->checked_r( $options, 'lazyloadxt_ajax', 1 ); ?> value="1">
        <?php _e('Enable AJAX navigation (infinite scroll, lightbox, etc).', self::ns ); ?>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Output HTML for Effects Settings.
   *
   * @since Lazy Load XT 0.1.0
   */
  function lazyloadxt_effects_render() {

    $options = get_option( 'lazyloadxt_effects' );
    ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Effects settings', self::ns ); ?></span>
      </legend>
      <label for="lazyloadxt_fade_in">
        <input type='checkbox' id='lazyloadxt_fade_in' name='lazyloadxt_effects[lazyloadxt_fade_in]' <?php $this->checked_r( $options, 'lazyloadxt_fade_in', 1 ); ?> value="1">
        <?php _e('Fade in lazy loaded objects.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_spinner">
        <input type='checkbox' id='lazyloadxt_spinner' name='lazyloadxt_effects[lazyloadxt_spinner]' <?php $this->checked_r( $options, 'lazyloadxt_spinner', 1 ); ?> value="1">
        <?php _e('Show spinner while objects are loading.', self::ns ); ?>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Output HTML for AddOns Settings.
   *
   * @since Lazy Load XT 0.1.0
   */
  function lazyloadxt_addons_render() {

    $options = get_option( 'lazyloadxt_addons' ); ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Addons settings', self::ns ); ?></span>
      </legend>
      <label for="lazyloadxt_script_based_tagging">
        <input type='checkbox' id='lazyloadxt_script_based_tagging' name='lazyloadxt_addons[lazyloadxt_script_based_tagging]' <?php $this->checked_r( $options, 'lazyloadxt_script_based_tagging', 1 ); ?> value="1">
        <?php _e('Enable script-based tagging.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_print">
        <input type='checkbox' id='lazyloadxt_print' name='lazyloadxt_addons[lazyloadxt_print]' <?php $this->checked_r( $options, 'lazyloadxt_print', 1 ); ?> value="1">
        <?php _e('Make sure lazy loaded elements appear in the print view.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_deferred_load">
        <input type='checkbox' id='lazyloadxt_deferred_load' name='lazyloadxt_addons[lazyloadxt_deferred_load]' <?php $this->checked_r( $options, 'lazyloadxt_deferred_load', 1 ); ?> value="1">
        <?php _e('Defer loading of objects by 50ms.', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_background_image">
        <input type='checkbox' id='lazyloadxt_background_image' name='lazyloadxt_addons[lazyloadxt_background_image]' <?php $this->checked_r( $options, 'lazyloadxt_background_image', 1 ); ?> value="1">
        <?php _e('Lazy load background images.', self::ns ); ?>
        <p class="description"><?php _e('Note: You must add the attribute "data-bg" with a value of path to the image to elements with a background image.', self::ns ); ?></p>
        <p class="description"><?php _e('E.g. "&lt;div data-bg="/path/to/image.png"&gt;...&lt;/div&gt;"', self::ns ); ?></p>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Output HTML for Advanced Settings.
   *
   * @since Lazy Load XT 0.5.0
   */
  function lazyloadxt_advanced_render() {

    $options = get_option( 'lazyloadxt_advanced' ); ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e( 'Advanced settings', self::ns ); ?></span>
      </legend>
      <label for="lazyloadxt_enabled">
        <input type='checkbox' id='lazyloadxt_enabled' name='lazyloadxt_advanced[lazyloadxt_enabled]' <?php $this->checked_r( $options, 'lazyloadxt_enabled', 1 ); ?> value="1">
        <?php _e( 'Enable advanced options. ', self::ns ); ?>
        <p class="description"><?php _e( 'The following settings will only go into effect if advanced options are enabled.', self::ns ); ?></p>
        <p class="description"><?php _e( 'Refer to RESS.io\'s <a href="https://github.com/ressio/lazy-load-xt#options">documentation on github</a> for further explanation of each option.', self::ns ); ?></p>
      </label>
      <br />
      <?php
      /*
        autoInit
        selector
        srcAttr
        blankImage
      */
      ?>
      <label for="lazyloadxt_edgeY">
        <?php _e( 'Edge Y:', self::ns ); ?><br />
        <input type='number' id='lazyloadxt_edgeY' name='lazyloadxt_advanced[lazyloadxt_edgeY]' value="<?php echo $options['lazyloadxt_edgeY']; ?>">
        <?php _e( 'pixels', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_edgeX">
        <?php _e( 'Edge X:', self::ns ); ?><br />
        <input type='number' id='lazyloadxt_edgeX' name='lazyloadxt_advanced[lazyloadxt_edgeX]' value="<?php echo $options['lazyloadxt_edgeX']; ?>">
        <?php _e( 'pixels', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_throttle">
        <?php _e( 'Throttle:', self::ns ); ?><br />
        <input type='number' id='lazyloadxt_throttle' name='lazyloadxt_advanced[lazyloadxt_throttle]' value="<?php echo $options['lazyloadxt_throttle']; ?>">
        <?php _e( 'ms', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_visibleOnly">
        <input type='checkbox' id='lazyloadxt_visibleOnly' name='lazyloadxt_advanced[lazyloadxt_visibleOnly]' <?php $this->checked_r( $options, 'lazyloadxt_visibleOnly', 1 ); ?> value="1">
        <?php _e( 'Visible only', self::ns ); ?>
      </label>
      <br />
      <label for="lazyloadxt_checkDuplicates">
        <input type='checkbox' id='lazyloadxt_checkDuplicates' name='lazyloadxt_advanced[lazyloadxt_checkDuplicates]' <?php $this->checked_r( $options, 'lazyloadxt_checkDuplicates', 1 ); ?> value="1">
        <?php _e( 'Check duplicates', self::ns ); ?>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Callback for the settings section.
   *
   * @since Lazy Load XT 0.1.0
   */
  function lazyloadxt_basic_section_callback() { 
    _e( 'Customize the basic features of Lazy Load XT.', self::ns );
  }


  /**
   * Render the settings form.
   *
   * @since Lazy Load XT 0.1.0
   */
  function settings_page() { 

    ?>
    <div class="wrap">
      <h2><?php _e('Lazy Load XT'); ?></h2>
      <form id="basic" action='options.php' method='post' style='clear:both;'>
        <?php
        settings_fields( 'basicSettings' );
        do_settings_sections( 'basicSettings' );
        submit_button();
        ?>
      </form>
    </div>
    <?php

  }

  function checked_r($option, $key, $current = true, $echo = true) {
    if (is_array($option) && array_key_exists($key, $option)) {
      checked( $option[$key],$current,$echo );
    }
  }

}
