<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginNetworkCore' ) ) { class gPluginNetworkCore extends gPluginClassCore
{
	public function setup_globals( $constants = array(), $args = array() ) 
	{ 
		$this->args = gPluginUtils::parse_args_r( $args, array(
			'domain' => 'gplugin',
			'title' => __( 'gPlugin Network', GPLUGIN_TEXTDOMAIN ),
			'network' => true,
			'term_meta' => false,
			'options' => array(),
		) );
		
		$this->constants = apply_filters( $this->args['domain'].'_network_constants', $constants );
		$this->blog_map = get_site_option( $this->args['domain'].'_blog_map', array() );
		$this->current_blog = get_current_blog_id();
		
		$this->root = false;
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
        } else {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            $this->setup_network();
        }
		
		$this->setup_modules();
	}
	
	// currently called manually
	function setup_settings()
	{
		if ( isset( $this->constants['class_network_settings'] ) )
			$this->settings = gPluginFactory( $this->constants['class_network_settings'], $this->constants, $this->getFilters( 'network_settings_args' ) );
		if ( isset( $this->constants['class_component_settings'] ) )			
			$this->components = gPluginFactory( $this->constants['class_component_settings'], $this->constants, $this->getFilters( 'component_settings_args' ) );
		if ( isset( $this->constants['class_module_settings'] ) )			
			$this->modules = gPluginFactory( $this->constants['class_module_settings'], $this->constants, $this->getFilters( 'module_settings_args' ) );
	}
    
    function init() 
	{
		// gPlugin Local:
		if ( ! is_textdomain_loaded( GPLUGIN_TEXTDOMAIN ) )
			load_plugin_textdomain( GPLUGIN_TEXTDOMAIN, false, 'gplugin/languages' );
		
		// Parent Plugin Local:
		//if ( ! is_textdomain_loaded( $this->args['domain'] ) )
			//$this->load_textdomain();
		
		// init here to help filtering the templates
		if ( isset( $this->constants['class_mustache'] ) )
			call_user_func( array( $this->constants['class_mustache'], 'init' ) );
	}
	
    function plugins_loaded()
	{
		$this->load_textdomain();
	}
	
	function load_textdomain() {}
    function admin_init() {}
	function setup_network() {}
	function setup_modules() {}
    //function network_settings_save() {} // do the saves
	
    function network_admin_menu()
    {
		if ( ! method_exists( $this, 'network_settings_save' ) )
			return; // bail if extended class not ready to have a network settings page
	
        $hook = add_submenu_page( 'settings.php',
            sprintf( _x( '%s Network', 'Network Settings Page Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ), // Page HTML Title
            sprintf( _x( '%s', 'Network Menu Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ), // Menu Title
            'manage_network_options', 
            $this->args['domain'],
            array( $this, 'network_settings' ) 
        );
		
		add_action( 'load-'.$hook, array( $this, 'network_settings_save' ) ); 
    }
    
	function network_settings()
	{
        $settings_uri = 'settings.php?page='.$this->args['domain'];
		$sub = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';
		$messages = $this->getFilters( 'network_settings_messages' );
		$subs = $this->getFilters( 'network_settings_subs', array(
			'overview' => __( 'Overview', GPLUGIN_TEXTDOMAIN ),
			'general' => __( 'General', GPLUGIN_TEXTDOMAIN ),
			'console' => __( 'Console', GPLUGIN_TEXTDOMAIN ),
		) );
		
		?><div class="wrap"><h2><?php 
			printf( _x( '%s Network Settings', 'Network Settings Page Title', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ); ?></h2><?php 
			gPluginFormHelper::header_nav( $settings_uri, $sub, $subs );
			
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
				do_action( $this->args['domain'].'_network_settings_html', $sub, $settings_uri );
				
		?><div class="clear"></div></div><?php
	}
	
	// called by extended class as default settings page html
	public function network_settings_html( $sub, $settings_uri )
	{
		echo '<form method="post" action="">';
			settings_fields( $this->args['domain'].'_'.$sub );
			do_settings_sections( $this->args['domain'].'_'.$sub );
			submit_button();
		echo '</form>';
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
    
    function get_option( $name, $default = false ) 
	{
        $options = get_site_option( $this->args['domain'], false );
        if ( $options === false ) $options = array();
        if ( !isset( $options[$name] ) ) $options[$name] = $default;
        return $options[$name];
    }

    function update_option( $name, $value ) 
	{
        $options = get_site_option( $this->args['domain'], false );
        if ( $options === false ) $options = array();
        //unset ( $option[$name] );
        $options[$name] = $value;
        return update_site_option( $this->args['domain'], $options );
    }

    function delete_option( $name ) 
	{
        $options = get_site_option( $this->args['domain'], false );
        if ( $options === false ) $options = array();
        unset( $options[$name] );
        return update_option( $this->args['domain'], $options );
    }
	
	// TODO : adapt
	// add_filter( 'plugin_row_meta', array( $this, "plugin_row_meta"), 10, 4 );
	function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status )
	{
		$pname = plugin_basename(__FILE__);
		if ($pname === $plugin_file ) {
			$plugin_meta[] = sprintf(
				'<a href="%s">Donate</a>',
				'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8RADH554RPKDU'
			);
		}
		return $links;
	}

} }