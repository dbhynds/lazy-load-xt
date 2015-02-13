=== Lazy Load XT ===
Contributors: dbhynds
Tags: Lazy Load, Lazy Load XT, iframe, image, media, video, YouTube, Vimeo
Requires at least: 2.8
Tested up to: 4.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lazy Load images, videos, iframes and more using Lazy Load XT.

== Description ==

Lazy load images, YouTube and Vimeo videos, and iframes using [Lazy Load XT](https://github.com/ressio/lazy-load-xt).

This plugin works by loading the Lazy Load XT script and replacing the `src` attributes with `data-src` when the content of a post or page is loaded on the front end of a WordPress site.

== Installation ==

1. Download and unzip Lazy Load XT.
1. Upload the `lazy-load-xt` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

Ask some questions.

== Changelog ==

= 0.1 =
* Load Lazy Load XT js using `wp_enqueue_scripts()`
* Replace `src` with `data-src` in `the_content()`
