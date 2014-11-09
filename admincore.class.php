<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPluginAdminCore extends gPluginClassCore
{
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
    
	function init() { }
	function admin_init() { }
	function admin_footer() { }
	
	function admin_menu() 
	{
        // check if the menu already registered
        global $submenu;
        if ( isset( $submenu['options-general.php'] ) )
            foreach ( $submenu['options-general.php'] as $the_submenu )
                if ( in_array( $this->args['domain'], $the_submenu ) )
                    return;

        add_submenu_page( 'options-general.php',
            sprintf( __( '%s Settings', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ), // Page HTML Title
            //_x( $this->args['title'], 'plugin_core_menu' GPLUGIN_TEXTDOMAIN ), // Menu Title
            $this->args['title'], // Menu Title
            'manage_options',
            $this->args['domain'],
            array( $this, 'plugin_settings' )
        );
	}

	// usually overrides by the child class
	function admin_print_styles() 
	{ 
		$this->print_admin_edit_styles();
	}

	// the caller must check cpt first
	function print_admin_edit_styles( $post_type = 'post' )
	{
		if ( strpos( $_SERVER['REQUEST_URI'], 'post.php' ) 
			|| strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) )
				gPluginFormHelper::linkStyleSheet( $this->constants['plugin_url'].'assets/css/'.$this->args['component'].'-'.$post_type.'-post.css' );
		
		if ( strpos( $_SERVER['REQUEST_URI'], 'edit.php' ) ) 
			gPluginFormHelper::linkStyleSheet( $this->constants['plugin_url'].'assets/css/'.$this->args['component'].'-'.$post_type.'-edit.css' );
	}

	// for settings page only
	function admin_print_styles_settings() 
	{
        if ( strpos( $_SERVER['REQUEST_URI'], 'page='.$this->args['domain'] ) )
			echo '<link rel="stylesheet" href="'.$this->constants['plugin_url'].'assets/css/settings.css" type="text/css" />'; 	
	}
	
	function settings_link( $links ) 
	{
		array_unshift( $links, '<a href="options-general.php?page='.$this->args['domain'].'">'.__( 'Settings', GPLUGIN_TEXTDOMAIN ).'</a>' );
		return $links;
	}
	
   
	function plugin_settings()
	{
		$this->store_settings();
		
        $messages = $this->getFilters( $this->args['component'].'_settings_messages' );
		$waiting = esc_url( $this->constants['plugin_url'].'/assets/images/ajax-loader.gif' );
		$wpspin = esc_url( admin_url( 'images/wpspin_light.gif' ) );
		if ( ! isset( $_GET['sub'] ) ) 
            $_GET['sub'] = 'settings'; 
        $settings_uri = 'options-general.php?page='.$this->args['domain'];
		$settings_page = $this->constants['plugin_dir'].'admin/'.$this->args['component'].'.header.php';
		if ( file_exists( $settings_page ) )
			require_once( $settings_page );
	}

	// OLD!!!!!
    // Rough Draft!
	// TODO : add do_actions()
	function store_settings()
	{
		if ( isset( $_GET['page'] ) && $this->args['domain'] == $_GET['page'] ) 	{
			if ( isset( $_REQUEST['action'] ) && 'save' == $_REQUEST['action'] ) {
				switch ($_REQUEST['sub']) {
					case 'settings' : {
						check_admin_referer( $this->args['domain'].'-settings');
						$message_code = gplugin_store_source_request();
						wp_safe_redirect( add_query_arg( 'message', $message_code, wp_get_referer()).'&sub=settings');
					} break;
					case 'fields' : {
						check_admin_referer($this->args['domain'].'-fields');
						$message_code = gplugin_store_settings_request();
						wp_safe_redirect(add_query_arg( 'message', $message_code, wp_get_referer()).'&sub=fields');
					} break;
					case 'convert' : {
						check_admin_referer( $this->args['domain'].'-convert');
						$message_code = gplugin_store_users_request();
						wp_safe_redirect( add_query_arg( 'message', $message_code, wp_get_referer()).'&sub=convert');
					} break;
				}	
				die;
			}
		}			
	}
	
	// wrapper
	public static function post_type()
	{
		return gPluginWPHelper::getCurrentPostType();
	}
}