<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
/*
Plugin Name: gPlugin
Plugin URI: http://geminorum.ir/wordpress/gplugin
Description: Shade of a framework to help with WordPress development
Version: 33
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

/*
	Copyright 2016 geminorum

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'GPLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GPLUGIN_URL', plugin_dir_url( __FILE__ ) );

require( GPLUGIN_DIR.'/includes/load.php' );
gplugin_init();
