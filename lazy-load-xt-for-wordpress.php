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

	function init() {
		if ( is_admin() ){ // admin actions
			add_action( 'admin_menu', array($this,'admin_menu') );
			add_action( 'admin_init', array($this,'register_settings') );
		}
		add_filter( 'the_content', array($this,'the_content_filter') );
		//add_filter( 'get_image_tag', array($this,'get_image_tag_filter'), 10, 2);
		add_action( 'wp_enqueue_scripts', array($this,'load_scripts') );
	}
	
	function load_scripts() {
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
	}

	function admin_menu() {
		add_options_page('Lazy Load XT Settings', 'Lazy Load XT', 'administrator',$this,array($this,'settings_page'));
	}

	function register_settings() {
		register_setting('lazyloadxt','min');
		register_setting('lazyloadxt','extra');
		register_setting('lazyloadxt','script-based-tagging');
		register_setting('lazyloadxt','responsive-images');
		register_setting('lazyloadxt','print');
		register_setting('lazyloadxt','background-image');
		register_setting('lazyloadxt','deferred-load');
	}

	function settings_page() {
		?>
        // Set class property
        $this->options = get_option( 'lazyloadxt' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>My Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'lazyloadxt' );   
                do_settings_sections( 'my-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
	}

}

LazyLoadXT->init();

