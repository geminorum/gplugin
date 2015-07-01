<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginNetworkCore' ) ) { class gPluginNetworkCore extends gPluginClassCore
{

	var $_asset_styles = false;
	var $_asset_config = false;
	var $_asset_object = 'gPlugin';
	var $_asset_args   = array();

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->args = gPluginUtils::parse_args_r( $args, array(
			'title'     => __( 'gPlugin Network', GPLUGIN_TEXTDOMAIN ),
			'domain'    => 'gplugin',
			'network'   => true,
			'term_meta' => false,
			'options'   => array(),
		) );

		$this->constants    = apply_filters( $this->args['domain'].'_network_constants', $constants );
		// $this->blog_map     = get_site_option( $this->args['domain'].'_blog_map', array() ); // NOT USED YET, MUST CAN BE DISABLED
		$this->current_blog = get_current_blog_id();

		$this->root   = false;
		$this->remote = false;

		if ( isset( $this->constants['class_filters'] ) )
			gPluginFactory( $this->constants['class_filters'], $constants, $args );

		if ( isset( $this->args['term_meta'] ) && $this->args['term_meta'] )
			gPluginFactory( 'gPluginTermMeta', $constants, $args ); // no point passing the arguments!

		$this->setup_settings();
	}

	public function setup_actions()
	{
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );

		if( isset( $this->constants['class_network_settings'] ) && method_exists( $this, 'settings_args_late' ) )
			add_filter( 'gplugin_settings_args_'.strtolower( $this->constants['class_network_settings'] ), array( $this, 'settings_args_late' ) );

		if ( is_network_admin() ) {

			add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
			add_action( 'admin_print_footer_scripts', array( $this, 'footer_asset_config' ), 99 );

		} else {

			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'admin_print_footer_scripts', array( $this, 'footer_asset_config' ), 99 );
			} else {
				add_action( 'wp_footer' , array( $this, 'footer_asset_config'  ), 99 );
			}

			$this->setup_network();
		}

		$this->setup_modules();
	}

	public function setup_settings()
	{
		if ( isset( $this->constants['class_network_settings'] ) )
			$this->settings = gPluginFactory(
				$this->constants['class_network_settings'],
				$this->constants,
				$this->getFilters( 'network_settings_args' )
			);

		if ( isset( $this->constants['class_component_settings'] ) )
			$this->components = gPluginFactory(
				$this->constants['class_component_settings'],
				$this->constants,
				$this->getFilters( 'component_settings_args' )
			);

		if ( isset( $this->constants['class_module_settings'] ) )
			$this->modules = gPluginFactory(
				$this->constants['class_module_settings'],
				$this->constants,
				$this->getFilters( 'module_settings_args' )
			);
	}

	public function init()
	{
		// gPlugin Locale:
		if ( ! is_textdomain_loaded( GPLUGIN_TEXTDOMAIN ) )
			load_plugin_textdomain( GPLUGIN_TEXTDOMAIN, false, 'gplugin/languages' );

		// Parent Plugin Locale:
		//if ( ! is_textdomain_loaded( $this->args['domain'] ) )
			//$this->load_textdomain();

		// init here to help filtering the templates
		if ( isset( $this->constants['class_mustache'] ) )
			call_user_func( array( $this->constants['class_mustache'], 'init' ) );
	}

	public function plugins_loaded()
	{
		$this->load_textdomain();
	}

	public function load_textdomain() {}
	public function admin_init() {}
	public function setup_network() {}
	public function setup_modules() {}

	public function network_admin_menu()
	{
		// bail if extended class not ready to have a network settings page
		if ( ! method_exists( $this, 'network_settings_save' ) )
			return;

		$hook = add_submenu_page( 'settings.php',
			sprintf( _x( '%s Network', 'Network Settings Page Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ), // Page HTML Title
			sprintf( _x( '%s', 'Network Menu Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ), // Menu Title
			'manage_network_options',
			$this->args['domain'],
			array( $this, 'network_settings' )
		);

		add_action( 'load-'.$hook, array( $this, 'network_settings_save' ) );
	}

	public function network_settings()
	{
		$settings_uri = 'settings.php?page='.$this->args['domain'];
		$sub          = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';
		$messages     = $this->getFilters( 'network_settings_messages' );
		$subs         = $this->getFilters( 'network_settings_subs', array(
			'overview' => __( 'Overview', GPLUGIN_TEXTDOMAIN ),
			'general'  => __( 'General', GPLUGIN_TEXTDOMAIN ),
			'console'  => __( 'Console', GPLUGIN_TEXTDOMAIN ),
		) );

		?><div class="wrap"><h2> <?php
			printf( _x( '%s Network Settings', 'Network Settings Page Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ); ?></h2> <?php
			gPluginFormHelper::headerNav( $settings_uri, $sub, $subs );

			if ( isset( $_GET['message'] ) ) {
				if ( isset( $messages[$_REQUEST['message']] ) ) {
					echo $messages[$_REQUEST['message']];
				} else {
					gPluginWPHelper::notice( $_REQUEST['message'], 'error fade' );
				}
				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
			}

			if ( file_exists( $this->constants['plugin_dir'].'admin/network.'.$sub.'.php' ) )
				require_once( $this->constants['plugin_dir'].'admin/network.'.$sub.'.php' );
			else
				do_action( $this->args['domain'].'_network_settings_sub_'.$sub, $settings_uri, $sub );

		?><div class="clear"></div></div> <?php
	}

	// called by extended class as default settings page html
	public function network_settings_html( $settings_uri, $sub )
	{
		echo '<form method="post" action="">';
			settings_fields( $this->args['domain'].'_'.$sub );
			do_settings_sections( $this->args['domain'].'_'.$sub );
			submit_button();
		echo '</form>';
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

	public function enqueue_asset_config( $args = array(), $scope = null )
	{
		$this->_asset_config = true;

		if ( count( $args ) ) {
			if ( is_null( $scope ) )
				$temp = array_merge( $this->_asset_args, $args );
			else
				$temp = array_merge( $this->_asset_args, array( $scope => $args ) );
			$this->_asset_args = $temp;
		}
	}

	// front & admin
	public function footer_asset_config()
	{
		if ( ! $this->_asset_config )
			return;

		$args = $this->_asset_args;
		$args['api'] = defined( 'GNETWORK_AJAX_ENDPOINT' ) && GNETWORK_AJAX_ENDPOINT ? GNETWORK_AJAX_ENDPOINT : admin_url( 'admin-ajax.php' );

	?> <script type="text/javascript">
/* <![CDATA[ */
	var <?php echo $this->_asset_object; ?> = <?php echo wp_json_encode( $args ); ?>;

	<?php if ( gPluginWPHelper::isDev() ) echo 'console.log('.$this->_asset_object.');'; ?>

/* ]]> */
</script> <?php
	}

	// TODO: extend by child to use network option
	public function get_site_user_id( $fallback = true )
	{
		if ( defined( 'GNETWORK_SITE_USER_ID' ) && constant( 'GNETWORK_SITE_USER_ID' ) )
			return GNETWORK_SITE_USER_ID;

		if ( function_exists( 'gtheme_get_option' ) ) {
			$gtheme_user = gtheme_get_option( 'default_user', 0 );
			if ( $gtheme_user )
				return $gtheme_user;
		}

		if ( $fallback )
			return get_current_user_id();

		return 0;
	}

	public function get_option( $name, $default = false )
	{
		$options = get_site_option( $this->args['domain'], false );
		if ( $options === false ) $options = array();
		if ( !isset( $options[$name] ) ) $options[$name] = $default;
		return $options[$name];
	}

	public function update_option( $name, $value )
	{
		$options = get_site_option( $this->args['domain'], false );
		if ( $options === false ) $options = array();
		$options[$name] = $value;
		return update_site_option( $this->args['domain'], $options );
	}

	public function delete_option( $name )
	{
		$options = get_site_option( $this->args['domain'], false );
		if ( $options === false ) $options = array();
		unset( $options[$name] );
		return update_option( $this->args['domain'], $options );
	}
} }
