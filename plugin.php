<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

/*
Plugin Name: gPlugin
Plugin URI: http://geminorum.ir/wordpress/gplugin
Description: Shade of a framework to help with WordPress development
Version: 36
License: GPLv3+
Author: geminorum
Author URI: http://geminorum.ir/
Network: true
TextDomain: gplugin
DomainPath : /languages
GitHub Plugin URI: https://github.com/geminorum/gplugin
GitHub Branch: master
Requires WP: 4.4
Requires PHP: 5.3
*/

define( 'GPLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GPLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GPLUGIN_FILE', basename( GPLUGIN_DIR ).'/'.basename( __FILE__ ) );

require( GPLUGIN_DIR.'/includes/load.php' );
gplugin_init();
