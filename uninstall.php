<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// Delete lazyloadxt settings from the options table
foreach (array('lazyloadxt_general','lazyloadxt_effects','lazyloadxt_addons','lazyloadxt_advanced','lazyloadxt_version') as $option) {
	delete_option($option);
}