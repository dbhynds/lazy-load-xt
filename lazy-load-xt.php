<?php
/**
 * @package Lazy_Load_XT
 * @version 0.1
 */
/*
Plugin Name: Lazy Load XT for WordPress
Plugin URI: http://wordpress.org/plugins/lazy-load-xt/
Description: Lazy load post images using Lazy Load XT
Author: Davo Hynds
Author URI: http://mightybytes.com
Version: 0.1
Text Domain: lazy-load-xt
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class LazyLoadXT {
	
	protected $dir; // Plugin directory
	protected $lazyloadxt_ver = '1.0.6'; // Version of Lazy Load XT (the script, not this plugin)
	protected $settingsClass; // Settings class for admin area
	protected $settings; // Settings for this plugin

	function __construct() {
		
		/*if ( intval( get_query_var( 'print' ) ) == 1 ||
			intval( get_query_var( 'printpage' ) ) == 1 || 
			strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' ) !== false ) {
				return;
		}*/
		
		// If we're in the admin area, load the settings class
		if (is_admin()) {
			require 'settings.php';
			$settingsClass = new LazyLoadXTSettings;
			// If this is the first time we've enabled the plugin, setup default settings
			register_activation_hook(__FILE__,array($settingsClass,'first_time_activation'));
		}

		// Store our settings in memory to reduce mysql calls
		$this->settings = $this->get_settings();
		$this->dir = plugin_dir_url(__FILE__);

		//add_filter( 'get_image_tag', array($this,'get_image_tag_filter'), 10, 2);
		
		// Enqueue Lazy Load XT scripts and styles
		add_action( 'wp_enqueue_scripts', array($this,'load_scripts') );
		// If advanced settings are enabled, print inline js in the head
		if ( $this->settings['advanced'] ) {
			add_action( 'wp_head', array($this,'print_scripts') );
		}
		
		// Replace the 'src' attr with 'data-src' in the_content
		add_filter( 'the_content', array($this,'the_content_filter') );
		// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail
		if ($this->settings['thumbnails']) {
			add_filter( 'wp_get_attachment_image_attributes', array($this,'wp_get_attachment_image_attributes_filter') );
		}
		
		

	}


	function get_settings() {

		// Get setting options from the db
		$general = get_option('lazyloadxt_general');
		$effects = get_option('lazyloadxt_effects');
		$addons = get_option('lazyloadxt_addons');
		$advanced = get_option('lazyloadxt_advanced');

		// Set the array of options
		$settings_arr = array(
				'minimize_scripts',
				'load_extras',
				'thumbnails',
				'fade_in',
				'spinner',
				'script_based_tagging',
				'responsive_images',
				'print',
				'background_image',
				'deferred_load',
			);

		// Start fresh
		$settings = array();
		// Loop through the settings we're looking for, and set them if they exist
		foreach ($settings_arr as $setting) {
			if ($general && array_key_exists('lazyloadxt_'.$setting,$general)){
				$return = $general['lazyloadxt_'.$setting];
			} elseif ($effects && array_key_exists('lazyloadxt_'.$setting,$effects)){
				$return = $effects['lazyloadxt_'.$setting];
			} elseif ($addons && array_key_exists('lazyloadxt_'.$setting,$addons)){
				$return = $addons['lazyloadxt_'.$setting];
			} else {
				// Otherwise set the option to false
				$return = false;
			}
			$settings[$setting] = $return;
		}

		// If enabled, set the advanced settings to an array
		if ($advanced['lazyloadxt_enabled']) {
			foreach ($advanced as $key => $val) {
				if ( $key != 'lazyloadxt_enabled' ) {
					$settings['advanced'][str_replace('lazyloadxt_','',$key)] = $val;
				}
			}
		} else {
			// Otherwise set it to false
			$settings['advanced'] = false;
		}
		
		// Return the settings
		return $settings;

	}


	
	function load_scripts() {

		// Are these minified?
		$min = ($this->settings['minimize_scripts']) ? '.min' : '';
		// Just to save space
		$jqll = 'jquery.lazyloadxt';
		
		// Enqueue fade-in if enabled
		if ( $this->settings['fade_in'] ) {
			wp_enqueue_style( 'lazyloadxt-fadein-style', $this->dir.'css/'.$jqll.'.fadein'.$min.'.css', false, $this->lazyloadxt_ver );
		}
		// Enqueue spinner if enabled
		if ( $this->settings['spinner'] ) {
			wp_enqueue_style( 'lazyloadxt-spinner-style', $this->dir.'css/'.$jqll.'.spinner'.$min.'.css', false, $this->lazyloadxt_ver );
		}
		
		// Enqueue extras enabled. Otherwise, load the regular script
		if ( $this->settings['load_extras'] ) {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.'.extra'.$min.'.js', array( 'jquery' ), $this->lazyloadxt_ver );
		} else {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.$min.'.js', array( 'jquery' ), $this->lazyloadxt_ver );
		}

		/*if ( $this->settings['script_based_tagging'] ) {
		}
		if ( $this->settings['responsive_images'] ) {
		}*/
		// Enqueue print if enabled
		if ( $this->settings['print'] ) {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.'.print'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
		}
		/*if ( $this->settings['background_image'] ) {
		}*/
		// Enqueue deferred load if enabled
		if ( $this->settings['deferred_load'] ) {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.'.autoload'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
		}
		
	}

	function print_scripts() {
		?>
		<script type="text/javascript">
			jQuery.extend(jQuery.lazyLoadXT, { <?php
				// Print out the advanced settings
				foreach ($this->settings['advanced'] as $key => $val) {
					echo "$key : '$val', ";
				}
			?> } );
		</script>
		<?php
	}

	function the_content_filter($content) {
		// If there's anything there, replace the 'src' with 'data-src'
		if (strlen($content)) {
			$newcontent = $content;
			// Replace 'src' with 'data-src' on images
			$newcontent = $this->switch_src_for_data_src($newcontent,'img');
			// If enabled, replace 'src' with 'data-src' on iframes
			if ($this->settings['load_extras']) {
				$newcontent = $this->switch_src_for_data_src($newcontent,'iframe');
			}
			return $newcontent;
		} else {
			// Otherwise, carry on
			return $content;
		}
	}

	function switch_src_for_data_src($content,$tag) {
		// Make a new DOMDoc
		$doc = new DOMDocument();
		// Load it up
		$doc->LoadHTML($content);
		// Get the elements we need to switch the src for
		$elements = $doc->getElementsByTagName($tag);

		// Switch out the 'src' with 'data-src'
		foreach ($elements as $element) {
			$src = $element->getAttribute('src');
			// Remove the existing attributes.
			$element->removeAttribute('src');
			// Set the new attribute.
			$element->setAttribute('data-src', $src);
		}

		// Prep for return
		$return = new DOMDocument();
		// Get the contents of the body tag
		$body = $doc->getElementsByTagName('body')->item(0);
		// Append them to the $return
		foreach ($body->childNodes as $child){
		    $return->appendChild($return->importNode($child, true));
		}
		// And we're done
		return $return->saveHTML();
	}

	/*function get_image_tag_filter($html, $id, $alt, $align, $size) {
	    list( $img_src, $width, $height ) = image_downsize($id, $size);
	    $imagesize = getimagesize($img_src);

	    $class = 'align' . esc_attr($align) .' size-' . esc_attr($size) . ' wp-image-' . $id;
	    $html = '<img data-src="' . esc_attr($img_src) . '" alt="' . esc_attr($alt) . '" data-width="' . $imagesize[0] . '" data-height="' . $imagesize[1] . '" class="' . $class . '" />';
	    $before = '';
	    $after = '<noscript><img src="' . esc_attr($img_src) . '" alt="' . esc_attr($alt) . '" class="' . $class . '" /></noscript>';
	    $html = $before . $html . $after;

	    return $html;
	}*/


	function wp_get_attachment_image_attributes_filter($attr) {
		// Change the attribute 'src' to 'data-src'
		$attr['data-src'] = $attr['src'];
		unset($attr['src']);
		return $attr;
	}

}

// Init
new LazyLoadXT;




