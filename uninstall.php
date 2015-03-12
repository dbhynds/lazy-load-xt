<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

foreach (array('lazyloadxt_general','lazyloadxt_effects','lazyloadxt_addons','lazyloadxt_advanced') as $option) {
	delete_option($option);
}