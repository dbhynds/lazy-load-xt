# Lazy Load XT

A WordPress plugin to lazy Load images, videos, iframes and more using Lazy Load XT.

## Table of Contents

*	Description
*	Installation
*	Frequently Asked Questions
*	Changelog

## Description

Lazy load images, YouTube and Vimeo videos, and iframes using [Lazy Load XT](https://github.com/ressio/lazy-load-xt).

Lazy Load XT is the fastest, lightest, fully customizable lazy load plugin in the WordPress Plugin Directory.

This plugin works by loading the Lazy Load XT script and replacing the `src` attributes with `data-src` when the content of a post or page is loaded on the front end of a WordPress site.

[View Lazy Load XT on WordPress.org.](https://wordpress.org/plugins/lazy-load-xt/)

## Installation

1.	Download and unzip [Lazy Load XT](https://wordpress.org/plugins/lazy-load-xt/).
2.	Upload the `lazy-load-xt` directory to the `/wp-content/plugins/` directory
3.	Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions

None right now. Ask some!

## Changelog

### 0.2
*	Lazy load HTML5 elements
*	Toggle and lazy load featured images, text widgets
*	Specify css classes to skip lazy loading
*	Improve settings, installation and upgrade
*	Fallback to 1x1 pixel transparent gif
*	HTML validation

### 0.1
*	Load Lazy Load XT js using `wp_enqueue_scripts()`
*	Replace `src` with `data-src` in `the_content()`