<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

defined( 'GPLUGIN_VERSION' ) or define( 'GPLUGIN_VERSION', '27' );
defined( 'GPLUGIN_TEXTDOMAIN' ) or define( 'GPLUGIN_TEXTDOMAIN', 'gplugin' );
defined( 'GPLUGIN_DEBUG' ) or define( 'GPLUGIN_DEBUG', constant( 'WP_DEBUG' ) );

// MAYBE : adding current directory
defined( 'GPLUGIN_DIR' ) or define( 'GPLUGIN_DIR', '' );
defined( 'GPLUGIN_URL' ) or define( 'GPLUGIN_URL', '' );


if ( ! function_exists( 'gPluginFactory' ) ) : function gPluginFactory( $class = 'gPluginClassCore', $constants = array(), $args = array() ){
	if ( class_exists( $class ) )
		return call_user_func_array( array( $class, 'instance' ), array( $class, $constants, $args ) );

	gPluginError( __FUNCTION__, sprintf( '%s class not exists!', $class ) );
	return FALSE;
} endif;

if ( ! function_exists( 'gPluginError' ) ) : function gPluginError( $function, $message, $version = GPLUGIN_VERSION ) {
	if ( WP_DEBUG )
		 _doing_it_wrong( $function, $message, $version );
		//error_log( print_r( $log, TRUE ) );
		//trigger_error( sprintf( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', $function, $message, $version ) );
} endif;