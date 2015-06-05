<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginModuleCore' ) ) { class gPluginModuleCore extends gPluginClassCore
{

	var $_init_priority       = 10;
	var $_admin_init_priority = 10;

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->current_blog = get_current_blog_id();
		parent::setup_globals( $constants, $args );
	}

	public function setup_actions()
	{
		add_action( 'init', array( &$this, 'init' ), $this->_init_priority );
		// add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ), $this->_plugins_loaded );

		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'admin_init' ), $this->_admin_init_priority );
			// add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		}
	}

	public function init() {}
	public function plugins_loaded() {}
	public function admin_init() {}
	public function admin_menu() {}

	public function getFilters( $context, $fallback = array() )
	{
		if ( isset( $this->constants['class_filters'] )
			&& class_exists( $this->constants['class_filters'] ) ) {
				$filtred = gPluginFactory( $this->constants['class_filters'] );
				return $filtred->get( $context, $fallback );
		}
		return $fallback;
	}
} }
