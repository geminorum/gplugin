<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPluginAdminCore extends gPluginClassCore
{
	var $_component = 'admin'; // root / remote

	// public function setup_globals( $constants = array(), $args = array() ) { $this->constants = $constants; $this->args = $args; }

	public function setup_actions()
	{
		add_action( 'init', array( $this, 'init' ) );
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_print_styles_settings' ) ); // for settings page

			add_action( 'admin_footer', array( $this, 'admin_footer' ) );

			// no need for network
			if ( ! $this->args['network'] ) {
				add_action( 'plugin_action_links_'.$this->args['domain'].'/'.$this->args['domain'].'.php', array( $this, 'settings_link' ), 10, 4 );
			}
		}
	}

	public function init() { }
	public function admin_init() { }
	public function admin_footer() { }
	//public function admin_settings_load() { }

    public function admin_menu()
    {
		// bail if extended class not ready to have an admin settings page
		if ( ! method_exists( $this, 'admin_settings_load' ) )
			return;

        $hook = add_submenu_page( 'options-general.php',
            sprintf( _x( '%s Settings', 'Admin Settings Page Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ),
            sprintf( _x( '%s', 'Admin Menu Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ),
            'manage_options',
            $this->args['domain'],
            array( $this, 'admin_settings' )
        );

		add_action( 'load-'.$hook, array( $this, 'admin_settings_load' ) );
    }

	function admin_settings()
	{
        $settings_uri = 'options-general.php?page='.$this->args['domain'];
		$sub = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';
		$subs = $this->getFilters( $this->_component.'_settings_subs', array(
			'overview' => __( 'Overview', GPLUGIN_TEXTDOMAIN ),
			'general' => __( 'General', GPLUGIN_TEXTDOMAIN ),
		) );

		$messages = $this->getFilters( $this->_component.'_settings_messages' );

		echo '<div class="wrap"><h2>';
			printf( _x( '%s Settings', 'Admin Settings Page Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] );
			echo '</h2>';
			gPluginFormHelper::headerNav( $settings_uri, $sub, $subs );

			if ( isset( $_GET['message'] ) ) {
				if ( isset( $messages[$_REQUEST['message']] ) ) {
					echo $messages[$_REQUEST['message']];
				} else {
					gPluginWPHelper::notice( $_REQUEST['message'], 'error fade' );
				}
				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
			}

			$file = $this->constants['plugin_dir'].'admin'.DS.$this->_component.'.admin.'.$sub.'.php';
			if ( file_exists( $file ) )
				require_once( $file );
			else
				do_action( $this->args['domain'].'_'.$this->_component.'_settings_sub_'.$sub, $settings_uri, $sub );

		echo '<div class="clear"></div></div>';
	}

	// usually overrides by the child class
	public function admin_print_styles()
	{
		$this->print_admin_edit_styles();
	}

	// the caller must check cpt first
	public function print_admin_edit_styles( $post_type = 'post' )
	{
		if ( strpos( $_SERVER['REQUEST_URI'], 'post.php' )
			|| strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) )
				gPluginFormHelper::linkStyleSheet( $this->constants['plugin_url'].'assets/css/'.$this->args['component'].'-'.$post_type.'-post.css' );

		if ( strpos( $_SERVER['REQUEST_URI'], 'edit.php' ) )
			gPluginFormHelper::linkStyleSheet( $this->constants['plugin_url'].'assets/css/'.$this->args['component'].'-'.$post_type.'-edit.css' );
	}

	// for settings page only
	public function admin_print_styles_settings()
	{
        if ( strpos( $_SERVER['REQUEST_URI'], 'page='.$this->args['domain'] ) )
			echo '<link rel="stylesheet" href="'.$this->constants['plugin_url'].'assets/css/settings.css" type="text/css" />';
	}

	public function settings_link( $links )
	{
		array_unshift( $links, '<a href="options-general.php?page='.$this->args['domain'].'">'.__( 'Settings', GPLUGIN_TEXTDOMAIN ).'</a>' );
		return $links;
	}

	// wrapper
	public static function post_type()
	{
		return gPluginWPHelper::getCurrentPostType();
	}

    function getFilters( $context, $fallback = array() )
    {
		if ( isset( $this->constants['class_filters'] )
			&& class_exists( $this->constants['class_filters'] ) ) {
				$filtred = gPluginFactory( $this->constants['class_filters'] );
				return $filtred->get( $context, $fallback );
		}
		return $fallback;
    }

}
