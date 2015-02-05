<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * @package Lazy_Load_XT_for_WordPress
 * @version 0.1
 */
/*
Plugin Name: Lazy Load XT for WordPress
Plugin URI: http://wordpress.org/plugins/lazy-load-xt-for-wordpress/
Description: Implement Lazy Load XT (https://github.com/ressio/lazy-load-xt) for WordPress
Author: Davo Hynds
Version: 0.1
Author URI: http://mightybytes.com
*/

class LazyLoadXT {

	var $setting_groups = array(
			'lazyloadxt-general' => array(
					'title' => 'General Settings',
					'settings' => array(
						'lazyloadxt_min' => array('Minimize Scripts','checkbox'),
						'lazyloadxt_extra' => array('Extras','checkbox'),
					)
				),
			'lazyloadxt-addons' => array(
					'title' => 'Addons Settings',
					'settings' => array(
						'lazyloadxt_fadein' => array('Fade In','checkbox'),
						'lazyloadxt_spinner' => array('Spinner','checkbox'),
					)
				),
			'lazyloadxt-effects' => array(
					'title' => 'Effects Settings',
					'settings' => array(
						'lazyloadxt_script_based_tagging' => array('Script-based Tagging','checkbox'),
						'lazyloadxt_responsive_images' => array('Responsive Images','checkbox'),
						'lazyloadxt_print' => array('Print','checkbox'),
						'lazyloadxt_background_image' => array('Background Image','checkbox'),
						'lazyloadxt_deferred_load' => array('Deferred Load','checkbox'),
					)
				),
		);

	function __construct() {
		if ( is_admin() ){ // admin actions
			add_action( 'admin_menu', array($this,'admin_menu') );
			add_action( 'admin_init', array($this,'register_settings') );
		}
		//add_filter( 'the_content', array($this,'the_content_filter') );
		//add_filter( 'get_image_tag', array($this,'get_image_tag_filter'), 10, 2);
		//add_action( 'wp_enqueue_scripts', array($this,'load_scripts') );
	}
	
	/*function load_scripts() {
		//wp_enqueue_style( 'lazyloadxt-style', plugin_dir_url('css/jquery.lazyloadxt.fadein.css'), false, '1.0.6' );
		wp_enqueue_script( 'lazyloadxt-script', plugin_dir_url('js/jquery.lazyloadxt.min.js'), array( 'jquery' ), '1.0.6' );
	}

	function the_content_filter($content) {
		$doc = new DOMDocument();
		$doc->LoadHTML($content);
		$images = $doc->getElementsByTagName('img');
		$attributes = array('src'=>'data-src', 'class'=>'class');
		foreach ($images as $image) {
			foreach ($attributes as $key=>$value) {
				// Get the value of the current attributes and set them to variables.
				$$key = $image->getAttribute($key);
				// Remove the existing attributes.
				$image->removeAttribute($key);
				// Set the new attribute.
				switch ($key) {
					case 'class':
						if (!empty($$key)) {
							$image->setAttribute($value, $$key . ' fs-img');
						} else {
							$image->setAttribute($value, $$key . 'fs-img');
						}
						break;
					default:
						$image->setAttribute($value, $$key);
				}
			}
			// You already have the $src once the $attributes loop has run, so you can use it here.
			// Find size attributes
			$imagesize = getimagesize($image_url);
			// Set image size attributes
			$image->setAttribute('data-width', $imagesize[0]);
			$image->setAttribute('data-height', $imagesize[1]);
			// Add the new noscript node.
			$noscript = $doc->createElement('noscript');
			$noscriptnode = $image->parentNode->insertBefore($noscript, $image);
			// Add the img node to the noscript node.
			$img = $doc->createElement('IMG');
			$newimg = $noscriptnode->appendChild($img);
			$newimg->setAttribute('src', $src);
		}
		return $doc->saveHTML();
	}

	function get_image_tag_filter($html, $id, $alt, $align, $size) {
	    list( $img_src, $width, $height ) = image_downsize($id, $size);
	    $imagesize = getimagesize($img_src);

	    $class = 'align' . esc_attr($align) .' size-' . esc_attr($size) . ' wp-image-' . $id;
	    $html = '<img data-src="' . esc_attr($img_src) . '" alt="' . esc_attr($alt) . '" data-width="' . $imagesize[0] . '" data-height="' . $imagesize[1] . '" class="' . $class . '" />';
	    $before = '';
	    $after = '<noscript><img src="' . esc_attr($img_src) . '" alt="' . esc_attr($alt) . '" class="' . $class . '" /></noscript>';
	    $html = $before . $html . $after;

	    return $html;
	}*/

	function admin_menu() {
		add_options_page('Lazy Load XT Settings', 'Lazy Load XT', 'administrator','lazyloadxt',array($this,'settings_page'));
	}

	function register_settings() {
		
		$setting_groups = $this->setting_groups;

		foreach ($setting_groups as $group => $settings) {
			add_settings_section(
		        $group,         // ID used to identify this section and with which to register options
		        $settings['title'],                  // Title to be displayed on the administration page
		        array($this,'settings_section_callback'), // Callback used to render the description of the section
		        'lazyloadxt'                           // Page on which to add this section of options
		    );
			foreach ($settings['settings'] as $setting => $setting_args) {
				register_setting('lazyloadxt',$setting);
				add_settings_field (
					$setting,
					$setting_args[0],
					array($this,'form_field'),
					'lazyloadxt',
					$group,
					array(
						'id' => $setting,
						'type' => $setting_args[1]
					)
				);
			}

		}

	}

	function settings_section_callback($args) {
		//var_dump($args);
	}

	function form_field($args) {
		//var_dump($args);
		if ($args['type'] == 'checkbox') {
			$val = (get_option($args['id'])) ? 'checked="checked' : '';
			var_dump($val);
			echo '<input type="checkbox" value="1" '.$val.' />';
		}
	}

	function settings_page() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Lazy Load XT Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'lazyloadxt' ); 
                do_settings_sections( 'lazyloadxt' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
	}

}

new LazyLoadXT;

