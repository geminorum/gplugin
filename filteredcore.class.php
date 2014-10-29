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
    
    function get( $group, $fallback = array() )
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
} }