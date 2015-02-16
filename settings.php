<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class LazyLoadXTSettings {

	public function __construct() {
		add_action( 'admin_menu', array($this,'lazyloadxt_add_admin_menu') );
		add_action( 'admin_init', array($this,'lazyloadxt_settings_init') );
	}

	function first_time_activation() {
		if ( !get_option('lazyloadxt_general') ) {
			update_option('lazyloadxt_general',array('lazyloadxt_minimize_scripts'=>1));
		}
	}

	function lazyloadxt_add_admin_menu() { 
		add_options_page( 'Lazy Load XT', 'Lazy Load XT', 'manage_options', 'lazyloadxt', array($this,'settings_page') );
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




		register_setting( 'advancedSettings', 'lazyloadxt_advanced' );

		add_settings_section(
			'lazyloadxt_advanced_section',
			__( 'Advanced Settings', 'lazy-load-xt' ),
			array($this,'lazyloadxt_advanced_section_callback'),
			'advancedSettings'
		);

		add_settings_field( 
			'lazyloadxt_advanced',
			__( 'Advanced', 'lazy-load-xt' ),
			array($this,'lazyloadxt_advanced_render'),
			'advancedSettings',
			'lazyloadxt_advanced_section' 
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
				<input type='checkbox' id='lazyloadxt_minimize_scripts' name='lazyloadxt_general[lazyloadxt_minimize_scripts]' <?php checked( $options['lazyloadxt_minimize_scripts'], 1 ); ?> value='1'>
				<?php _e('Load minimized versions of javascript and css files.','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_load_extras">
				<input type='checkbox' id='lazyloadxt_load_extras' name='lazyloadxt_general[lazyloadxt_load_extras]' <?php checked( $options['lazyloadxt_load_extras'], 1 ); ?> value='1'>
				<?php _e('Lazy load YouTube and Vimeo videos, iframes, audio, etc.','lazy-load-xt'); ?>
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
				<input type='checkbox' id='lazyloadxt_fade_in' name='lazyloadxt_effects[lazyloadxt_fade_in]' <?php checked( $options['lazyloadxt_fade_in'], 1 ); ?> value='1'>
				<?php _e('Fade in lazy loaded objects','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_spinner">
				<input type='checkbox' id='lazyloadxt_spinner' name='lazyloadxt_effects[lazyloadxt_spinner]' <?php checked( $options['lazyloadxt_spinner'], 1 ); ?> value='1'>
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
			<?php /* ?>
			<label for="lazyloadxt_script_based_tagging">
				<input type='checkbox' id='lazyloadxt_script_based_tagging' name='lazyloadxt_addons[lazyloadxt_script_based_tagging]' <?php checked( $options['lazyloadxt_script_based_tagging'], 1 ); ?> value='1'>

			</label>
			<br />
			<label for="lazyloadxt_responsive_images">
				<input type='checkbox' id='lazyloadxt_responsive_images' name='lazyloadxt_addons[lazyloadxt_responsive_images]' <?php checked( $options['lazyloadxt_responsive_images'], 1 ); ?> value='1'>
			</label>
			<br />
			<?php */ ?>
			<label for="lazyloadxt_print">
				<input type='checkbox' id='lazyloadxt_print' name='lazyloadxt_addons[lazyloadxt_print]' <?php checked( $options['lazyloadxt_print'], 1 ); ?> value='1'>
				<?php _e('Make sure lazy loaded elements appear in the print view','lazy-load-xt'); ?>
			</label>
			<br />
			<?php /* ?>
			<label for="lazyloadxt_background_image">
				<input type='checkbox' id='lazyloadxt_background_image' name='lazyloadxt_addons[lazyloadxt_background_image]' <?php checked( $options['lazyloadxt_background_image'], 1 ); ?> value='1'>
			</label>
			<br />
			<?php */ ?>
			<label for="lazyloadxt_deferred_load">
				<input type='checkbox' id='lazyloadxt_deferred_load' name='lazyloadxt_addons[lazyloadxt_deferred_load]' <?php checked( $options['lazyloadxt_deferred_load'], 1 ); ?> value='1'>
				<?php _e('Defer loading of objects by 50ms','lazy-load-xt'); ?>
			</label>
		</fieldset>
		<?php

	}



	function lazyloadxt_advanced_render() {

		$options = get_option( 'lazyloadxt_advanced' );
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php _e('Advanced settings','lazy-load-xt'); ?></span>
			</legend>
			<label for="lazyloadxt_autoInit">
				<input type='checkbox' id='lazyloadxt_autoInit' name='lazyloadxt_advanced[lazyloadxt_autoInit]' <?php checked( $options['lazyloadxt_autoInit'], 1 ); ?> value='1'>
				<?php _e('autoInit','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_selector">
				<input type='text' id='lazyloadxt_selector' name='lazyloadxt_advanced[lazyloadxt_selector]' value='<?php echo $options['lazyloadxt_selector']; ?>'>
				<p class="description"><?php _e('selector','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_srcAttr">
				<input type='text' id='lazyloadxt_srcAttr' name='lazyloadxt_advanced[lazyloadxt_srcAttr]' value='<?php echo $options['lazyloadxt_srcAttr']; ?>'>
				<p class="description"><?php _e('srcAttr','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_blankImage">
				<input type='text' id='lazyloadxt_blankImage' name='lazyloadxt_advanced[lazyloadxt_blankImage]' value='<?php echo $options['lazyloadxt_blankImage']; ?>'>
				<p class="description"><?php _e('blankImage','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_edgeY">
				<input type='number' id='lazyloadxt_edgeY' name='lazyloadxt_advanced[lazyloadxt_edgeY]' value='<?php echo $options['lazyloadxt_edgeY']; ?>'>
				<p class="description"><?php _e('edgeY','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_edgeX">
				<input type='number' id='lazyloadxt_edgeX' name='lazyloadxt_advanced[lazyloadxt_edgeX]' value='<?php echo $options['lazyloadxt_edgeX']; ?>'>
				<p class="description"><?php _e('edgeX','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_throttle">
				<input type='number' id='lazyloadxt_throttle' name='lazyloadxt_advanced[lazyloadxt_throttle]' value='<?php echo $options['lazyloadxt_throttle']; ?>'>
				<p class="description"><?php _e('throttle','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_visibleOnly">
				<input type='checkbox' id='lazyloadxt_visibleOnly' name='lazyloadxt_advanced[lazyloadxt_visibleOnly]' <?php checked( $options['lazyloadxt_visibleOnly'], 1 ); ?> value='1'>
				<?php _e('visibleOnly','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_checkDuplicates">
				<input type='checkbox' id='lazyloadxt_checkDuplicates' name='lazyloadxt_advanced[lazyloadxt_checkDuplicates]' <?php checked( $options['lazyloadxt_checkDuplicates'], 1 ); ?> value='1'>
				<?php _e('checkDuplicates','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_scrollContainer">
				<input type='text' id='lazyloadxt_scrollContainer' name='lazyloadxt_advanced[lazyloadxt_scrollContainer]' value='<?php echo $options['lazyloadxt_scrollContainer']; ?>'>
				<p class="description"><?php _e('scrollContainer','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_forceLoad">
				<input type='checkbox' id='lazyloadxt_forceLoad' name='lazyloadxt_advanced[lazyloadxt_forceLoad]' <?php checked( $options['lazyloadxt_forceLoad'], 1 ); ?> value='1'>
				<?php _e('forceLoad','lazy-load-xt'); ?>
			</label>
			<br />
			<label for="lazyloadxt_loadEvent">
				<input type='text' id='lazyloadxt_loadEvent' name='lazyloadxt_advanced[lazyloadxt_loadEvent]' value='<?php echo $options['lazyloadxt_loadEvent']; ?>'>
				<p class="description"><?php _e('loadEvent','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_updateEvent">
				<input type='text' id='lazyloadxt_updateEvent' name='lazyloadxt_advanced[lazyloadxt_updateEvent]' value='<?php echo $options['lazyloadxt_updateEvent']; ?>'>
				<p class="description"><?php _e('updateEvent','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_updateEvent">
				<input type='text' id='lazyloadxt_updateEvent' name='lazyloadxt_advanced[lazyloadxt_updateEvent]' value='<?php echo $options['lazyloadxt_updateEvent']; ?>'>
				<p class="description"><?php _e('updateEvent','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_forceEvent">
				<input type='text' id='lazyloadxt_forceEvent' name='lazyloadxt_advanced[lazyloadxt_forceEvent]' value='<?php echo $options['lazyloadxt_forceEvent']; ?>'>
				<p class="description"><?php _e('forceEvent','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_oninit">
				<input type='text' id='lazyloadxt_oninit' name='lazyloadxt_advanced[lazyloadxt_oninit]' value='<?php echo $options['lazyloadxt_oninit']; ?>'>
				<p class="description"><?php _e('oninit','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_onshow">
				<input type='text' id='lazyloadxt_onshow' name='lazyloadxt_advanced[lazyloadxt_onshow]' value='<?php echo $options['lazyloadxt_onshow']; ?>'>
				<p class="description"><?php _e('onshow','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_onload">
				<input type='text' id='lazyloadxt_onload' name='lazyloadxt_advanced[lazyloadxt_onload]' value='<?php echo $options['lazyloadxt_onload']; ?>'>
				<p class="description"><?php _e('onload','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_onerror">
				<input type='text' id='lazyloadxt_onerror' name='lazyloadxt_advanced[lazyloadxt_onerror]' value='<?php echo $options['lazyloadxt_onerror']; ?>'>
				<p class="description"><?php _e('onerror','lazy-load-xt'); ?></p>
			</label>
			<br />
			<label for="lazyloadxt_oncomplete">
				<input type='text' id='lazyloadxt_oncomplete' name='lazyloadxt_advanced[lazyloadxt_oncomplete]' value='<?php echo $options['lazyloadxt_oncomplete']; ?>'>
				<p class="description"><?php _e('oncomplete','lazy-load-xt'); ?></p>
			</label>
		</fieldset>
		<?php

	}


	function lazyloadxt_basic_section_callback() { 

		_e( 'Customize the basic features of Lazy Load XT.', 'lazy-load-xt' );

	}

	function lazyloadxt_advanced_section_callback() { 

		_e( 'Customize the advanced features of Lazy Load XT.', 'lazy-load-xt' );

	}

	


	function settings_page() { 

		?>
		<div class="wrap">
			<h2><?php _e('Lazy Load XT'); ?></h2>
			<ul class="subsubsub" style='overflow: auto;'>
				<li class="basic"><a href="#basic" class="basic">Basic Settings</a> |</li>
				<li class="advanced"><a href="#advanced" class="advanced">Advanced Settings</a></li>
			</ul>
			<br />
			<br />
			<form id="basic" action='options.php' method='post' style='clear:both;'>
				<?php
				settings_fields( 'basicSettings' );
				do_settings_sections( 'basicSettings' );
				submit_button();
				?>
			</form>
			<form id="advanced" action='options.php' method='post' style='clear:both;'>
				<?php
				settings_fields( 'advancedSettings' );
				do_settings_sections( 'advancedSettings' );
				submit_button();
				?>
			</form>
		</div>
		<?php

	}

}


