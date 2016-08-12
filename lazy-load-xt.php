<?php
/*
Plugin Name: Lazy Load XT
Plugin URI: http://wordpress.org/plugins/lazy-load-xt/
Description: Lazy Load XT is the fastest, lightest, fully customizable lazy load plugin in the WordPress Plugin Directory. Lazy load images, YouTube and Vimeo videos, and iframes using <a href="https://github.com/ressio/lazy-load-xt" target="_blank">Lazy Load XT</a>.
Author: Davo Hynds
Author URI: http://www.mightybytes.com/
Version: 0.5.3
Text Domain: lazy-load-xt
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Init
require dirname(__FILE__).'/LazyLoadXT.php';
new LazyLoadXT\LazyLoadXT;

// Admin Init
if (is_admin()) {
  require dirname(__FILE__).'/LazyLoadXTSettings.php';
  $settingsClass = new LazyLoadXT\LazyLoadXTSettings;
  // If this is the first time we've enabled the plugin, setup default settings
  register_activation_hook(__FILE__,array($settingsClass,'first_time_activation'));
  add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array($settingsClass,'lazyloadxt_action_links'));
}

/* API */

// Pass HTML to this function to filter it for lazy loading
function get_lazyloadxt_html($html = '') {
  global $lazyloadxt;
  return $lazyloadxt->filter_html($html);
}
