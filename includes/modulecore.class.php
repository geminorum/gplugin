<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginModuleCore' ) ) { class gPluginModuleCore extends gPluginClassCore
{

	protected $priority_init           = 10;
	protected $priority_plugins_loaded = 10;
	protected $priority_admin_init     = 10;

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->current_blog = get_current_blog_id();
		parent::setup_globals( $constants, $args );

		// FIXME: DROP THIS
		if ( isset( $this->_plugins_loaded ) ) {
			self::__dep( 'var $_plugins_loaded' );
			$this->priority_plugins_loaded = $this->_plugins_loaded;
		}

		// FIXME: DROP THIS
		if ( isset( $this->_init_priority ) ) {
			self::__dep( 'var $_init_priority' );
			$this->priority_init = $this->_init_priority;
		}

		// FIXME: DROP THIS
		if ( isset( $this->_admin_init_priority ) ) {
			self::__dep( 'var $_admin_init_priority' );
			$this->priority_admin_init = $this->_admin_init_priority;
		}
	}

	public function setup_actions()
	{
		if ( method_exists( $this, 'plugins_loaded' ) )
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), $this->priority_plugins_loaded );

		if ( method_exists( $this, 'init' ) )
			add_action( 'init', array( $this, 'init' ), $this->priority_init );

		if ( is_admin() ) {
			if ( method_exists( $this, 'admin_init' ) )
				add_action( 'admin_init', array( $this, 'admin_init' ), $this->priority_admin_init );

			if ( method_exists( $this, 'admin_menu' ) )
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}
	}

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