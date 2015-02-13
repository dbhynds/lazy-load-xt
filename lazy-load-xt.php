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
	
	protected $dir;
	protected $lazyloadxt_ver = '1.0.6';
	protected $settingsClass;
	protected $settings;

	function __construct() {
		
		add_filter( 'the_content', array($this,'the_content_filter') );
		add_filter( 'wp_get_attachment_image_attributes', array($this,'wp_get_attachment_image_attributes_filter') );
		
		//add_filter( 'get_image_tag', array($this,'get_image_tag_filter'), 10, 2);
		add_action( 'wp_enqueue_scripts', array($this,'load_scripts') );
		
		if (is_admin()) {
			require 'settings.php';
			$settingsClass = new LazyLoadXTSettings;
			register_activation_hook(__FILE__,array($settingsClass,'first_time_activation'));
		}

		$this->settings = $this->get_settings();
		$this->dir = plugin_dir_url(__FILE__);

	}


	function get_settings() {

		$general = get_option('lazyloadxt_general');
		$effects = get_option('lazyloadxt_effects');
		$addons = get_option('lazyloadxt_addons');

		$settings_arr = array(
				'minimize_scripts',
				'load_extras',
				'fade_in',
				'spinner',
				'script_based_tagging',
				'responsive_images',
				'print',
				'background_image',
				'deferred_load',
			);

		$settings = array();
		foreach ($settings_arr as $setting) {
			if ($general && array_key_exists('lazyloadxt_'.$setting,$general)){
				$return = $general['lazyloadxt_'.$setting];
			} elseif ($effects && array_key_exists('lazyloadxt_'.$setting,$effects)){
				$return = $effects['lazyloadxt_'.$setting];
			} elseif ($addons && array_key_exists('lazyloadxt_'.$setting,$addons)){
				$return = $addons['lazyloadxt_'.$setting];
			} else {
				$return = false;
			}
			$settings[$setting] = $return;
		}
		return $settings;

	}


	
	function load_scripts() {
		$min = ($this->settings['minimize_scripts']) ? '.min' : '';
		$jqll = 'jquery.lazyloadxt';
		
		if ( $this->settings['fade_in'] ) {
			wp_enqueue_style( 'lazyloadxt-fadein-style', $this->dir.'css/'.$jqll.'.fadein'.$min.'.css', false, $this->lazyloadxt_ver );
		}
		if ( $this->settings['spinner'] ) {
			wp_enqueue_style( 'lazyloadxt-spinner-style', $this->dir.'css/'.$jqll.'.spinner'.$min.'.css', false, $this->lazyloadxt_ver );
		}
		
		if ( $this->settings['load_extras'] ) {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.'.extra'.$min.'.js', array( 'jquery' ), $this->lazyloadxt_ver );
		} else {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.$min.'.js', array( 'jquery' ), $this->lazyloadxt_ver );
		}

		/*if ( $this->settings['script_based_tagging'] ) {
		}
		if ( $this->settings['responsive_images'] ) {
		}*/
		if ( $this->settings['print'] ) {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.'.print'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
		}
		/*if ( $this->settings['background_image'] ) {
		}*/
		if ( $this->settings['deferred_load'] ) {
			wp_enqueue_script( 'lazy-load-xt-script', $this->dir.'js/'.$jqll.'.autoload'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
		}
		
	}

	function the_content_filter($content) {
		$newcontent = $content;
		$newcontent = $this->switch_src_for_data_src($newcontent,'img');
		if ($this->settings['load_extras']) {
			$newcontent = $this->switch_src_for_data_src($newcontent,'iframe');
		}
		return $newcontent;
	}

	function switch_src_for_data_src($content,$tag) {
		$doc = new DOMDocument();
		$doc->LoadHTML($content);
		$elements = $doc->getElementsByTagName($tag);

		foreach ($elements as $element) {
			$src = $element->getAttribute('src');
			// Remove the existing attributes.
			$element->removeAttribute('src');
			// Set the new attribute.
			$element->setAttribute('data-src', $src);
		}

		$return = new DOMDocument();
		$body = $doc->getElementsByTagName('body')->item(0);
		foreach ($body->childNodes as $child){
		    $return->appendChild($return->importNode($child, true));
		}
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
		$attr['data-src'] = $attr['src'];
		unset($attr['src']);
		return $attr;
	}

}

new LazyLoadXT;




