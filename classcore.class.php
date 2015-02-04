<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! function_exists( 'gPluginFactory' ) ) : function gPluginFactory( $class = 'gPluginClassCore', $constants = array(), $args = array() ){
	if ( class_exists( $class ) )
		return call_user_func_array( array( $class, 'instance' ), array( $class, $constants, $args ) );
	else if ( WP_DEBUG )
		_doing_it_wrong( __FUNCTION__, sprintf( '%s class not exists!', $class ), GPLUGIN_VERSION );
	return false;
} endif;

if ( ! class_exists( 'gPluginClassCore' ) ) { 

// http://stackoverflow.com/a/6386309
interface gPluginClassCoreInterface 
{
    static function instance( $constants = array(), $args = array() );
}

// based on WP_Deregister_Users by John James Jacoby : http://wordpress.org/extend/plugins/deregister-users/
class gPluginClassCore implements gPluginClassCoreInterface 
{
	protected $data;
	
    public static final function instance( $class = 'gPluginClassCore', $constants = array(), $args = array() ) 
	{
        static $instance;
		if ( ! isset( $instance ) )	{
			$instance = new $class();
			$instance->setup_globals( $constants, $args );
			$instance->setup_actions();
		}
		return $instance;
    }
	
	// A dummy constructor to prevent loading more than once.
	protected function __construct() { /* Do nothing here */ }
	protected function __distruct() { $this->data = null; }
	// A dummy magic method to prevent cloning
	// public function __clone() { _doing_it_wrong( __FUNCTION__, 'Doing it wrong, chief.', GPLUGIN_VERSION ); }
	// A dummy magic method to prevent unserialization
	// public function __wakeup() { _doing_it_wrong( __FUNCTION__, 'Doing it wrong, chief.', GPLUGIN_VERSION ); }
	// Magic method for checking the existence of a certain custom field
	public function __isset( $key ) { return isset( $this->data[$key] ); }
	// Magic method for getting varibles
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }
	// Magic method for setting varibles
	public function __set( $key, $value ) { $this->data[$key] = $value; }
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }
	// Magic method to prevent notices and errors from invalid method calls
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }
	
	// workaround to avoid : "Indirect modification of overloaded property"
	public function inject( $key, $value ) { $this->{$key} = array_merge( $this->{$key}, (array) $value ); }
	public function append( $key, $arr_key, $arr_val ) { $temp = $this->{$key}; $temp[$arr_key] = $arr_val; $this->{$key} = $temp; }
	
	// must dep
	// shortcode_atts() mock-up
	public function set_args( $defaults, $args ) { 
		$args = (array) $args; 
		$out = array();
		foreach( $defaults as $name => $default ) { 
			if ( array_key_exists( $name, $args ) ) 
				$out[$name] = $args[$name]; 
			else 
				$out[$name] = $default; 
		}
		$this->args = $out;
	}
	
	// Set some smart defaults to class variables. Allow some of them to be filtered to allow for early overriding.
	public function setup_globals( $constants = array(), $args = array() ) { $this->constants = $constants; $this->args = $args; }
	// Setup the default hooks and actions
	public function setup_actions() { }	
	
	////////////////////////////////////////////////////
	// helpers
	
	// ANCESTOR : shortcode_atts()
	public static function atts( $pairs, $atts )
	{
		$atts = (array) $atts;
		$out = array();

		foreach( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $atts ) )
				$out[$name] = $atts[$name];
			else
				$out[$name] = $default;
		}

		return $out;
	}	
}

}