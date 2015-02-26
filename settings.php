<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class LazyLoadXTSettings {

	protected $ver = '0.2.0'; // Plugin version
	protected $defaults = array(
			'general' => array(
					'lazyloadxt_minimize_scripts' => 1,
					'lazyloadxt_thumbnails' => 1,
					'lazyloadxt_textwidgets' => 1,
					'lazyloadxt_load_extras' => 0,
					'lazyloadxt_excludeclasses' => '',
					'lazyloadxt_spinner' => 0,
					'lazyloadxt_deferred_load' => 0,
					'lazyloadxt_background_image' => 0,
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
			if (get_option('lazyloadxt_'.$key,false) != false) {
				update_option('lazyloadxt_'.$key,$val);
			}
		}
		update_option('lazyloadxt_version',$this->ver);
	}
	
	function update() {
		$defaults = $this->defaults;
		$ver = $this->ver;
		$dbver = get_option('lazyloadxt_version','');
		if (version_compare($ver,$dbver,'>')) {
			if (version_compare($dbver,'0.2','<=')) {
				$this->first_time_activation();
			}
			update_option('lazyloadxt_version',$this->ver);
		}
	}



	function lazyloadxt_add_admin_menu() { 
		$admin_page = add_options_page( 'Lazy Load XT', 'Lazy Load XT', 'manage_options', 'lazyloadxt', array($this,'settings_page') );
	}
	function lazyloadxt_enqueue_admin() {
	}


	function lazyloadxt_settings_init() {

		register_setting( 'basicSettings', 'lazyloadxt_general' );
		register_setting( 'basicSettings', 'lazyloadxt_effects' );
		register_setting( 'basicSettings', 'lazyloadxt_addons' );

		add_settings_section(
			'lazyloadxt_basic_section',
			__( 'General Settings', 'lazy-load-xt' ),
			array($this,'lazyloadxt_basic_section_callback'),
			'basicSettings'
		);

		add_settings_field( 
			'lazyloadxt_general',
			__( 'Basics', 'lazy-load-xt' ),
			array($this,'lazyloadxt_general_render'),
			'basicSettings',
			'lazyloadxt_basic_section' 
		);

		add_settings_field( 
			'lazyloadxt_effects',
			__( 'Effects', 'lazy-load-xt' ),
			array($this,'lazyloadxt_effects_render'),
			'basicSettings',
			'lazyloadxt_basic_section' 
		);

		add_settings_field( 
			'lazyloadxt_addons',
			__( 'Addons', 'lazy-load-xt' ),
			array($this,'lazyloadxt_addons_render'),
			'basicSettings',
			'lazyloadxt_basic_section' 
		);

	}



	function lazyloadxt_general_render() {

		$options = get_option( 'lazyloadxt_general' );
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php _e('Basic settings','lazy-load-xt'); ?></span>
			</legend>
			<label for="lazyloadxt_minimize_scripts">
				<input type='checkbox' id='lazyloadxt_minimize_scripts' name='lazyloadxt_general[lazyloadxt_minimize_scripts]' <?php checked( $options['lazyloadxt_minimize_scripts'], 1 ); ?> value="1">
				<?php _e('Load minimized versions of javascript and css files.','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_load_extras">
				<input type='checkbox' id='lazyloadxt_load_extras' name='lazyloadxt_general[lazyloadxt_load_extras]' <?php checked( $options['lazyloadxt_load_extras'], 1 ); ?> value="1">
				<?php _e('Lazy load YouTube and Vimeo videos, iframes, audio, etc.','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_thumbnails">
				<input type='checkbox' id='lazyloadxt_thumbnails' name='lazyloadxt_general[lazyloadxt_thumbnails]' <?php checked( $options['lazyloadxt_thumbnails'], 1 ); ?> value="1">
				<?php _e('Lazy load post thumbnails.','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_textwidgets">
				<input type='checkbox' id='lazyloadxt_textwidgets' name='lazyloadxt_general[lazyloadxt_textwidgets]' <?php checked( $options['lazyloadxt_textwidgets'], 1 ); ?> value="1">
				<?php _e('Lazy load text widgets.','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_excludeclasses">
				<?php _e('Skip lazy loading on these classes:','lazy-load-xt'); ?><br />
				<textarea id='lazyloadxt_excludeclasses' name='lazyloadxt_general[lazyloadxt_excludeclasses]' rows="3" cols="60"><?php echo $options['lazyloadxt_excludeclasses']; ?></textarea>
				<p class="description"><?php _e('Prevent objects with the above classes from being lazy loaded. (List classes separated by a space and without the proceding period. e.g. "skip-lazy-load size-thumbnail".)','lazy-load-xt'); ?></p>
			</label>
		</fieldset>
		<?php

	}

	function lazyloadxt_effects_render() {

		$options = get_option( 'lazyloadxt_effects' );
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php _e('Effects settings','lazy-load-xt'); ?></span>
			</legend>
			<label for="lazyloadxt_fade_in">
				<input type='checkbox' id='lazyloadxt_fade_in' name='lazyloadxt_effects[lazyloadxt_fade_in]' <?php checked( $options['lazyloadxt_fade_in'], 1 ); ?> value="1">
				<?php _e('Fade in lazy loaded objects','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_spinner">
				<input type='checkbox' id='lazyloadxt_spinner' name='lazyloadxt_effects[lazyloadxt_spinner]' <?php checked( $options['lazyloadxt_spinner'], 1 ); ?> value="1">
				<?php _e('Show spinner while objects are loading','lazy-load-xt'); ?>
			</label>
		</fieldset>
		<?php

	}

	function lazyloadxt_addons_render() {

		$options = get_option( 'lazyloadxt_addons' ); ?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php _e('Addons settings','lazy-load-xt'); ?></span>
			</legend>
			<label for="lazyloadxt_print">
				<input type='checkbox' id='lazyloadxt_print' name='lazyloadxt_addons[lazyloadxt_print]' <?php checked( $options['lazyloadxt_print'], 1 ); ?> value="1">
				<?php _e('Make sure lazy loaded elements appear in the print view.','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_deferred_load">
				<input type='checkbox' id='lazyloadxt_deferred_load' name='lazyloadxt_addons[lazyloadxt_deferred_load]' <?php checked( $options['lazyloadxt_deferred_load'], 1 ); ?> value="1">
				<?php _e('Defer loading of objects by 50ms.','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_background_image">
				<input type='checkbox' id='lazyloadxt_background_image' name='lazyloadxt_addons[lazyloadxt_background_image]' <?php checked( $options['lazyloadxt_background_image'], 1 ); ?> value="1">
				<?php _e('Lazy load background images.','lazy-load-xt'); ?>
				<p class="description"><?php _e('Note: You must add the attribute "data-bg" with a value of path to the image to elements with a background image.','lazy-load-xt'); ?></p>
				<p class="description"><?php _e('E.g. "&lt;div data-bg="/path/to/image.png"&gt;...&lt;/div&gt;"','lazy-load-xt'); ?></p>
			</label>
		</fieldset>
		<?php

	}


	function lazyloadxt_basic_section_callback() { 

		_e( 'Customize the basic features of Lazy Load XT.', 'lazy-load-xt' );

	}


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

}


