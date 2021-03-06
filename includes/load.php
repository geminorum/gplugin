<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

$GLOBALS['_gplugin'] = array( 41, __FILE__, array(
	'gPluginFactory',
	'gPluginClassCore',

	'gPluginPluginCore', // FIXME: DEPRECATED
	'gPluginModuleCore',

	'gPluginNetworkCore',
	'gPluginComponentCore',

	'gPluginSettingsCore',
	'gPluginFilteredCore',

	'gPluginAdminCore',
	'gPluginMetaCore',
	'gPluginAjaxCore',

	'gPluginTemplateCore', // FIXME: DEPRECATED
	'gPluginListTableCore', // FIXME: DEPRECATED
	'gPluginLoggerCore',
	'gPluginImportCore',

	'gPluginSettings',
	// 'gPluginSession', // FIXME: ADD CONSTANT TO ENABLE

	'gPluginWPHelper',
	'gPluginWPRemote', // FIXME: DEPRECATED
	'gPluginTaxonomyHelper',
	'gPluginCacheHelper',

	'gPluginPersianHelper',

	'gPluginFormHelper',
	'gPluginTextHelper',
	'gPluginFileHelper',
	'gPluginLocationHelper',
	'gPluginDateTimeHelper',

	'gPluginHTTP',
	'gPluginHTML',
	'gPluginUtils',
	'gPluginNumber',
	'gPluginHashed',
) );

// modified version of scb by scribu : http://wordpress.org/extend/plugins/scb-framework/
if ( ! class_exists( 'gPlugin' ) ) : class gPlugin {

	private static $candidates = array();
	private static $classes;
	private static $callbacks = array();

	private static $loaded;

	static function init( $callback = '' )
	{
		list( $rev, $file, $classes ) = $GLOBALS['_gplugin'];
		self::$candidates[$file] = $rev;
		self::$classes[$file] = $classes;

		if ( ! empty( $callback ) ) {
			self::$callbacks[$file] = $callback;
			add_action( 'activate_plugin',  array( __CLASS__, 'delayed_activation' ) );
		}

		if ( did_action( 'plugins_loaded' ) )
			self::load();
		else
			add_action( 'plugins_loaded', array( __CLASS__, 'load' ), 9, 0 );
	}

	static function delayed_activation( $plugin )
	{
		$plugin_dir = dirname( $plugin );

		if ( '.' == $plugin_dir )
			return;

		foreach ( self::$callbacks as $file => $callback ) {
			if ( dirname( dirname( plugin_basename( $file ) ) ) == $plugin_dir ) {
				$rev = self::load( FALSE );
				call_user_func_array( $callback, array( $rev ) );
				do_action( 'gplugin_activation_'.$plugin, $rev );
				break;
			}
		}
	}

	static function load( $do_callbacks = TRUE )
	{
		arsort( self::$candidates );
		$rev  = current( self::$candidates );
		$file = key( self::$candidates );
		$path = dirname( $file ).'/';

		foreach ( self::$classes[$file] as $class_name ) {
			if ( class_exists( $class_name ) )
				continue;

			$fpath = $path.substr( strtolower( $class_name ), 7 ).'.class.php';
			if ( file_exists( $fpath ) ) {
				include $fpath;
				self::$loaded[] = $fpath;
			}
		}

		if ( $do_callbacks )
			foreach ( self::$callbacks as $callback )
				call_user_func_array( $callback, array( $rev ) );

		return $rev;
	}

	static function get_info()
	{
		arsort( self::$candidates );
		return array( self::$loaded, self::$candidates );
	}

} endif;

if ( ! function_exists( 'gplugin_init' ) ) : function gplugin_init( $callback = '' ) {
	gPlugin::init( $callback );
} endif;
