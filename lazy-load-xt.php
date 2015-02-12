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

	/*var $setting_groups = array(
			'lazyloadxt-general' => array(
					'title' => 'General Settings',
					'settings' => array(
						'lazyloadxt_min' => array('Minimize Scripts','radio',
							array(
								'default' => 1,
								'label' => 'Load minized version of scripts',
								'options' => array(
									'1' => 'Yes',
									'0' => 'No'
								)
							)),
						'lazyloadxt_extra' => array('Extras','checkbox',
							array(
								'label' => 'Lazy Load iframes'
							)),
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
		);*/

	function __construct() {
		/*if ( is_admin() ){ // admin actions
			add_action( 'admin_menu', array($this,'admin_menu') );
			add_action( 'admin_init', array($this,'register_settings') );
		}*/
		add_filter( 'the_content', array($this,'the_content_filter') );
		//add_filter( 'get_image_tag', array($this,'get_image_tag_filter'), 10, 2);
		add_action( 'wp_enqueue_scripts', array($this,'load_scripts') );
		
		if (is_admin()) {
			require 'settings.php';
			new LazyLoadXTSettings;
		}

	}
	
	function load_scripts() {
		//wp_enqueue_style( 'lazyloadxt-style', plugin_dir_url('css/jquery.lazyloadxt.fadein.css'), false, '1.0.6' );
		wp_enqueue_script( 'lazy-load-xt-script', plugin_dir_url(__FILE__).'js/jquery.lazyloadxt.min.js', array( 'jquery' ), '1.0.6' );
	}

	function the_content_filter($content) {
		$newcontent = $content;
		$newcontent = $this->switch_src_for_data_src($newcontent,'img');
		$newcontent = $this->switch_src_for_data_src($newcontent,'iframe');
		return $newcontent;
	}

	function switch_src_for_data_src($content,$tag) {
		$doc = new DOMDocument();
		$doc->LoadHTML($content);
		$elements = $doc->getElementsByTagName($tag);
		/*$attributes = array('src'=>'data-src');
		foreach ($elements as $element) {
			//var_dump($element);
			foreach ($attributes as $key=>$value) {
				// Get the value of the current attributes and set them to variables.
				$$key = $element->getAttribute($key);
				// Remove the existing attributes.
				$element->removeAttribute($key);
				// Set the new attribute.
				$element->setAttribute($value, $$key);
			}
		}*/

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

	/*function admin_menu() {
		add_options_page('Lazy Load XT Settings', 'Lazy Load XT', 'administrator','lazyloadxt',array($this,'settings_page'));
	}

	function register_settings() {
		
		$setting_groups = $this->setting_groups;

		foreach ($setting_groups as $group => $settings) {
			add_settings_section(
		        $group,
		        $settings['title'],
		        array($this,'settings_section_callback'),
		        'lazyloadxt'
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
						'type' => $setting_args[1],
						'args' => $setting_args[2]
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
		$id = $args['id'];
		$atts = $args['args'];
		switch ($args['type']) {
			case 'checkbox' :
				$val = (get_option($id)) ? 'checked="checked' : '';
				//var_dump($val);
				echo '<label for="'.$id.'">';
				echo '<input type="checkbox" value="'.$atts['default'].'" '.$val.' name="'.$id.'"/> '.$atts['label'];
				echo '</label>';
				break;
			case 'radio' :
				echo '<p>'.$atts['label'].'</p>';
				foreach ($atts['options'] as $key => $option) {
					$val = (get_option($id) == $key) ? 'checked="checked"' : '';
					echo '<label for="'.$id.'">';
					echo '<input type="radio" value="'.$key.'" '.$val.' name="'.$id.'"/> '.$option;
					echo '</label>';
					echo '<br />';
				}
				break;
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
	}*/

}

new LazyLoadXT;


