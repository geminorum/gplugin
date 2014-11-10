<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
if ( ! class_exists( 'gPluginFilteredCore' ) ) { class gPluginFilteredCore extends gPluginClassCore
{
	public function setup_globals( $constants = array(), $args = array() ) 
	{ 
		$this->args = gPluginUtils::parse_args_r( $args, array(
			'domain' => 'gplugin',
			'title' => __( 'gPlugin Network', GPLUGIN_TEXTDOMAIN ),
			'filter_prefix' => false,
		) );
		
		if ( false === $this->args['filter_prefix'] )
			$this->inject( 'args', array( 'filter_prefix' => $this->args['domain'] ) );
		
		
		$this->constants = gPluginUtils::parse_args_r( $constants, array(
			'plugin_dir' => GPLUGIN_DIR,
			'plugin_url' => GPLUGIN_URL,
		) );
		
		$this->filtered = array();
	}
	
	public function get( $group, $fallback = array() )
	{
		//echo( $group).'<br />';
	
		if ( isset( $this->filtered[$group] ) 
			&& count( $this->filtered[$group] ) ) 
            return $this->filtered[$group];
		
		if ( ! method_exists( $this, $group ) ) {
			if ( ! count( $fallback ) )
				gPluginError( __FUNCTION__, sprintf( '%s group filter not exists!', $group ) );
			return $fallback;
		}
		
		$group_defaults = call_user_func( array( $this, $group ) );
		$this->inject( 'filtered', array( $group => apply_filters( $this->args['filter_prefix'].'_'.$group, $group_defaults ) ) );
		
		//if ( $group == 'remote_support_post_types' )
			//gnetwork_dump($group_defaults);
		
		return $this->filtered[$group]; 
	}
	
	// gPlugin default group filters
	
	//public function admin_settings_subs() { return array(); }
	//public function admin_settings_messages() { return array(); }
	//public function root_settings_subs() { return array(); }
	//public function root_settings_messages() { return array(); }
	//public function remote_settings_subs() { return array(); }
	//public function remote_settings_messages() { return array(); }
	//public function network_settings_subs() { return array(); }
	//public function network_settings_messages() { return array(); }
	
	//public function network_settings_args() { return array(); }
	//public function component_settings_args() { return array(); }
	//public function module_settings_args() { return array(); }
	
} }