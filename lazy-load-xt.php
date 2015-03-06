<?php
/**
 * @package Lazy_Load_XT
 * @version 0.2
 */
/*
Plugin Name: Lazy Load XT
Plugin URI: http://wordpress.org/plugins/lazy-load-xt/
Description: Lazy load post images using Lazy Load XT
Author: Davo Hynds
Version: 0.2
Text Domain: lazy-load-xt
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class LazyLoadXT {
	
	protected $dir; // Plugin directory
	protected $lazyloadxt_ver = '1.0.6'; // Version of Lazy Load XT (the script, not this plugin)
	protected $settingsClass; // Settings class for admin area
	protected $settings; // Settings for this plugins

	function __construct() {

		// Store our settings in memory to reduce mysql calls
		$this->settings = $this->get_settings();
		$this->dir = plugin_dir_url(__FILE__);

		//$this->check_version();

		
		// If we're in the admin area, load the settings class
		if (is_admin()) {
			require 'settings.php';
			$settingsClass = new LazyLoadXTSettings;
			// If this is the first time we've enabled the plugin, setup default settings
			register_activation_hook(__FILE__,array($settingsClass,'first_time_activation'));
		}
		
		// Enqueue Lazy Load XT scripts and styles
		add_action( 'wp_enqueue_scripts', array($this,'load_scripts') );
		// If advanced settings are enabled, print inline js in the head
		if ( $this->settings['advanced'] ) {
			add_action( 'wp_head', array($this,'print_scripts') );
		}
		
		// Replace the 'src' attr with 'data-src' in the_content
		add_filter( 'the_content', array($this,'the_content_filter') );
		// If enabled replace the 'src' attr with 'data-src' in text widgets
		if ($this->settings['textwidgets']) {
			add_filter( 'widget_text', array($this,'the_content_filter') );
		}
		// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail
		if ($this->settings['thumbnails']) {
			add_filter( 'post_thumbnail_html', array($this,'the_content_filter') );
			//add_filter( 'wp_get_attachment_image_attributes', array($this,'wp_get_attachment_image_attributes_filter') );
		}
		// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail
		if ($this->settings['avatars']) {
			add_filter( 'get_avatar', array($this,'the_content_filter') );
		}

		//add_filter( 'get_image_tag', array($this,'get_image_tag_filter'), 10, 2);
		

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
				'avatars',
				'textwidgets',
				'excludeclasses',
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

		$settings['excludeclasses'] = ($settings['excludeclasses']) ? explode(' ',$settings['excludeclasses']) : array();
		
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

		if ( $this->settings['script_based_tagging'] ) {
			wp_enqueue_script( 'lazy-load-xt-bg', $this->dir.'js/'.$jqll.'.script'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
		}
		/*if ( $this->settings['responsive_images'] ) {
		}*/
		// Enqueue print if enabled
		if ( $this->settings['print'] ) {
			wp_enqueue_script( 'lazy-load-xt-print', $this->dir.'js/'.$jqll.'.print'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
		}
		if ( $this->settings['background_image'] ) {
			wp_enqueue_script( 'lazy-load-xt-bg', $this->dir.'js/'.$jqll.'.bg'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
		}
		// Enqueue deferred load if enabled
		if ( $this->settings['deferred_load'] ) {
			wp_enqueue_script( 'lazy-load-xt-deferred', $this->dir.'js/'.$jqll.'.autoload'.$min.'.js', array( 'jquery','lazy-load-xt-script' ), $this->lazyloadxt_ver );
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
			$newcontent = $this->preg_replace_html($newcontent,array('img'));
			// If enabled, replace 'src' with 'data-src' on extra elements
			if ($this->settings['load_extras']) {
				$newcontent = $this->preg_replace_html($newcontent,array('iframe','embed','video','audio'));
			}
			return $newcontent;
		} else {
			// Otherwise, carry on
			return $content;
		}
	}

	function preg_replace_html($content,$tags) {

		$search = array();
		$replace = array();

		// Attributes to search for
		$attrs = implode('|',array('src','poster'));
		// Elements requiring a 'src' attribute to be valide HTML
		$src_req = array('img','video');

		// Loop through tags
		foreach($tags as $tag) {
			// Look for tag in content
			preg_match_all('/<'.$tag.'[\s\r\n]+.*?(\/|\/'.$tag.')>/is',$content,$matches);

			// If tags exist, loop through them and replace stuff
			if (count($matches[0])) {
				foreach ($matches[0] as $match) {
					preg_match('/[\s\r\n]class=[\'"](.*?)[\'"]/', $match, $classes);
					$classes_r = explode(' ',$classes[1]);
					// But first, check that the tag doesn't have any excluded classes
					if (count(array_intersect($classes_r, $this->settings['excludeclasses'])) == 0) {
						// Set the original version for <noscript>
						$original = $match;
						// And add it to the $search array.
						array_push($search, $original);

						// Use script-based tagging
						if ($this->settings['script_based_tagging']) {
							// If it's self-closing, use L();
							if (in_array($tag, array('img','embed'))) {
								$replace_markup = '<script>L();</script>'.$original;
							} else {
								// Otherwise, use Lb(); and Le();
								$replace_markup = '<script>Lb(\''.$tag.'\');</script>'.$original.'<script>Le();</script>';
							}
							// Add it to the $replace array
							array_push($replace, $replace_markup);
						} else {
							// If the element requires a 'src', set the src to default image
							$src = (in_array($tag, $src_req)) ? ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"' : '';
							// If the element is an audio tag, set the src to a blank mp3
							$src = ($tag == 'audio') ? $this->dir.'assets/empty.mp3' : $src;

							// Set replace html
							$replace_markup = $match;
							// Now replace attr with data-attr
							$replace_markup = preg_replace('/[\s\r\n]('.$attrs.')?=/', $src.' data-$1=', $replace_markup);
							// And add the original in as <noscript>
							$replace_markup .= '<noscript>'.$original.'</noscript>';
							// And add it to the $replace array.
							array_push($replace, $replace_markup);
						}
					}
				}
			}
		}

		// Replace all the $search items with the $replace items
		$newcontent = str_replace($search, $replace, $content);
		return $newcontent;
	}

	function switch_src_for_data_src($content, $tags) {
		// Make a new DOMDoc
		$doc = new DOMDocument();
		// Load it up (Doesn't like HTML5)
		@$doc->LoadHTML($content);

		// Attributes to search for
		$attrs = array('src','poster');
		// Elements requiring a 'src' attribute to be valide HTML
		$src_req = array('img','source');

		foreach ($tags as $tag) {
			// Get the elements we need to switch the src for
			$elements = $doc->getElementsByTagName($tag);

			// Switch out the 'src' with 'data-src'
			foreach ($elements as $element) {
				// Get the classes of element
				if ($tag == 'source') {
					// Check the parent tag for <video> and <audio> tags
					$parent = $element->parentNode;
					$classes = explode(' ',$parent->getAttribute('class'));
				} else {
					$classes = explode(' ',$element->getAttribute('class'));
				}
				
				// If it doesn't have any of the designated "skip" classes, replace the 'src' with 'data-src'
				if (count(array_intersect($classes,$this->settings['excludeclasses'])) == 0) {
					foreach ($attrs as $attr) {
						// Try to get the src attr
						$elemattr = $element->getAttribute($attr);
						//if attr exists
						if ($elemattr) {
							// If a 'src' attribute is required for valid html
							if (in_array($tag,$src_req)) {
								// Set the 'src' to a 1x1 pixel transparent gif
								$element->setAttribute($attr,'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
							} else {
								// Remove the existing attributes.
								$element->removeAttribute($attr);
							}
    						// Set the new attribute.
							$element->setAttribute('data-'.$attr, $elemattr);
						}
					}
				}
			}
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
		// Set 'src' to a 1x1 pixel transparent gif
		$attr['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
		return $attr;
	}



	// Make an API
	public function filter_html($content) {
		return $this->the_content_filter($content);
	}



}

// Init
$lazyloadxt = new LazyLoadXT;



