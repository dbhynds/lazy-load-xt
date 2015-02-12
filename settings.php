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

		register_setting( 'generalSettings', 'lazyloadxt_general' );
		register_setting( 'effects', 'lazyloadxt_effects' );
		register_setting( 'addOns', 'lazyloadxt_addons' );

		add_settings_section(
			'lazyloadxt_general_section',
			__( 'General Settings', 'lazy-load-xt' ),
			array($this,'lazyloadxt_general_section_callback'),
			'generalSettings'
		);

		add_settings_section(
			'lazyloadxt_effects_section',
			__( 'Effects', 'lazy-load-xt' ),
			array($this,'lazyloadxt_effects_section_callback'),
			'effects'
		);

		add_settings_section(
			'lazyloadxt_addons_section',
			__( 'Add Ons', 'lazy-load-xt' ),
			array($this,'lazyloadxt_addons_section_callback'),
			'addOns'
		);



		add_settings_field( 
			'lazyloadxt_minimize_scripts',
			__( 'Load minimized scripts', 'lazy-load-xt' ),
			array($this,'lazyloadxt_minimize_scripts_render'),
			'generalSettings',
			'lazyloadxt_general_section' 
		);

		add_settings_field( 
			'lazyloadxt_load_extras',
			__( 'Load "extras" version', 'lazy-load-xt' ),
			array($this,'lazyloadxt_load_extras_render'),
			'generalSettings',
			'lazyloadxt_general_section' 
		);

		add_settings_field( 
			'lazyloadxt_fade_in',
			__( 'Fade objects in on load', 'lazy-load-xt' ),
			array($this,'lazyloadxt_fade_in_render'),
			'effects',
			'lazyloadxt_effects_section' 
		);

		add_settings_field( 
			'lazyloadxt_spinner',
			__( 'Show spinner as objects are loading', 'lazy-load-xt' ),
			array($this,'lazyloadxt_spinner_render'),
			'effects',
			'lazyloadxt_effects_section' 
		);

		/*add_settings_field( 
			'lazyloadxt_script_based_tagging',
			__( 'Script-based tagging', 'lazy-load-xt' ),
			array($this,'lazyloadxt_script_based_tagging_render'),
			'addOns',
			'lazyloadxt_addons_section' 
		);

		add_settings_field( 
			'lazyloadxt_responsive_images',
			__( 'Responsive images', 'lazy-load-xt' ),
			array($this,'lazyloadxt_responsive_images_render'),
			'addOns',
			'lazyloadxt_addons_section' 
		);*/

		add_settings_field( 
			'lazyloadxt_print',
			__( 'Print', 'lazy-load-xt' ),
			array($this,'lazyloadxt_print_render'),
			'addOns',
			'lazyloadxt_addons_section' 
		);

		/*add_settings_field( 
			'lazyloadxt_background_image',
			__( 'Lazy load background images', 'lazy-load-xt' ),
			array($this,'lazyloadxt_background_image_render'),
			'addOns',
			'lazyloadxt_addons_section' 
		);*/

		add_settings_field( 
			'lazyloadxt_deferred_load',
			__( 'Defer loading script', 'lazy-load-xt' ),
			array($this,'lazyloadxt_deferred_load_render'),
			'addOns',
			'lazyloadxt_addons_section' 
		);


	}


	function lazyloadxt_minimize_scripts_render() { 

		$options = get_option( 'lazyloadxt_general' );
		?>
		<label for="lazyloadxt_minimize_scripts">
			<input type='checkbox' id='lazyloadxt_minimize_scripts' name='lazyloadxt_general[lazyloadxt_minimize_scripts]' <?php checked( $options['lazyloadxt_minimize_scripts'], 1 ); ?> value='1'>
			Load minimized versions of javascript and css files.
		</label>
		<?php

	}


	function lazyloadxt_load_extras_render() { 

		$options = get_option( 'lazyloadxt_general' );
		?>
		<label for="lazyloadxt_load_extras">
			<input type='checkbox' id='lazyloadxt_load_extras' name='lazyloadxt_general[lazyloadxt_load_extras]' <?php checked( $options['lazyloadxt_load_extras'], 1 ); ?> value='1'>
			Lazy load YouTube and Vimeo videos, iframes, audio, etc.
		</label>
		<?php

	}


	function lazyloadxt_fade_in_render() { 

		$options = get_option( 'lazyloadxt_effects' );
		?>
		<label for="lazyloadxt_fade_in">
			<input type='checkbox' id='lazyloadxt_fade_in' name='lazyloadxt_effects[lazyloadxt_fade_in]' <?php checked( $options['lazyloadxt_fade_in'], 1 ); ?> value='1'>
			Fade in lazy loaded objects
		</label>
		<?php

	}


	function lazyloadxt_spinner_render() { 

		$options = get_option( 'lazyloadxt_effects' );
		?>
		<label for="lazyloadxt_spinner">
			<input type='checkbox' id='lazyloadxt_spinner' name='lazyloadxt_effects[lazyloadxt_spinner]' <?php checked( $options['lazyloadxt_spinner'], 1 ); ?> value='1'>
			Show spinner while objects are loading
		</label>
		<?php

	}


	/*function lazyloadxt_script_based_tagging_render() { 

		$options = get_option( 'lazyloadxt_addons' );
		?>
		<label for="lazyloadxt_script_based_tagging">
			<input type='checkbox' id='lazyloadxt_script_based_tagging' name='lazyloadxt_addons[lazyloadxt_script_based_tagging]' <?php checked( $options['lazyloadxt_script_based_tagging'], 1 ); ?> value='1'>

		</label>
		<?php

	}


	function lazyloadxt_responsive_images_render() { 

		$options = get_option( 'lazyloadxt_addons' );
		?>
		<label for="lazyloadxt_responsive_images">
			<input type='checkbox' id='lazyloadxt_responsive_images' name='lazyloadxt_addons[lazyloadxt_responsive_images]' <?php checked( $options['lazyloadxt_responsive_images'], 1 ); ?> value='1'>
		</label>
		<?php

	}*/


	function lazyloadxt_print_render() { 

		$options = get_option( 'lazyloadxt_addons' );
		?>
		<label for="lazyloadxt_print">
			<input type='checkbox' id='lazyloadxt_print' name='lazyloadxt_addons[lazyloadxt_print]' <?php checked( $options['lazyloadxt_print'], 1 ); ?> value='1'>
			Make sure lazy loaded elements appear in the print view
		</label>
		<?php

	}


	/*function lazyloadxt_background_image_render() { 

		$options = get_option( 'lazyloadxt_addons' );
		?>
		<label for="lazyloadxt_background_image">
			<input type='checkbox' id='lazyloadxt_background_image' name='lazyloadxt_addons[lazyloadxt_background_image]' <?php checked( $options['lazyloadxt_background_image'], 1 ); ?> value='1'>
		</label>
		<?php

	}*/


	function lazyloadxt_deferred_load_render() { 

		$options = get_option( 'lazyloadxt_addons' );
		?>
		<label for="lazyloadxt_deferred_load">
			<input type='checkbox' id='lazyloadxt_deferred_load' name='lazyloadxt_addons[lazyloadxt_deferred_load]' <?php checked( $options['lazyloadxt_deferred_load'], 1 ); ?> value='1'>
			Defer loading of objects by 50ms
		</label>
		<?php

	}


	function lazyloadxt_general_section_callback() { 

		//_e( 'General section description', 'lazy-load-xt' );

	}

	function lazyloadxt_effects_section_callback() { 

		//_e( 'Effects section description', 'lazy-load-xt' );

	}

	function lazyloadxt_addons_section_callback() { 

		//_e( 'Plugin settings description', 'lazy-load-xt' );

	}


	function settings_page() { 

		?>
		<div class="wrap">
			<h2>Lazy Load XT</h2>
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'generalSettings' );
				do_settings_sections( 'generalSettings' );
				submit_button();
				?>
			</form>
			<hr />
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'effects' );
				do_settings_sections( 'effects' );
				submit_button();
				?>
			</form>
			<hr />
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'addOns' );
				do_settings_sections( 'addOns' );
				submit_button();
				?>
			</form>
		</div>
		<?php

	}

}


