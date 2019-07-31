<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

/*
Plugin Name: gPlugin
Plugin URI: https://geminorum.ir/wordpress/gplugin
Description: Shade of a framework to help with WordPress development
Version: 40
License: GPLv3+
Author: geminorum
Author URI: https://geminorum.ir/
Network: true
TextDomain: gplugin
DomainPath : /languages
GitHub Plugin URI: https://github.com/geminorum/gplugin
Requires WP: 4.4
Requires PHP: 5.6
*/

define( 'GPLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GPLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GPLUGIN_FILE', basename( GPLUGIN_DIR ).'/'.basename( __FILE__ ) );

require( GPLUGIN_DIR.'/includes/load.php' );
gplugin_init();
