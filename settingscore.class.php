<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
/**

	See: https://github.com/gilbitron/WordPress-Settings-Framework



**/



if ( ! class_exists( 'gPluginSettingsCore' ) ) { class gPluginSettingsCore extends gPluginClassCore
{  
/**
    var $_settings_args = array(
        'plugin_class' => false,
        'option_group' => 'gpluginsettings',
		'settings_sanitize' => false, // override sanitization
		'field_callback' => false, // oberride field print
		'page' => 'general',
        'sections' => array( 
			'default' => array( 
			//'date' => array( 
				'title' => false,
				//'title' => 'Section Title',
				'callback' => array( __CLASS__, 'section_callback' ), // '__return_false'
				'fields' => array(
					'enabled' => array(
						'title' => 'gPlugin',
						'desc' => '',
						'type' => 'enabled',
						'dir' => 'ltr',
						'default' => 0,
						'filter' => false, // 'esc_attr'
					),
				),
			),
		),
    );
	**/
	
	public function setup_globals( $constants = array(), $args = array() ) 
	{	
		$this->current_blog = get_current_blog_id();
		$this->option_group = isset( $args['option_group'] ) ? $args['option_group'] : 'gpluginsettings';
		$this->page = ( isset( $args['page'] ) && $args['page'] ? $args['page'] : 'general' );		
		
		$this->constants = array_merge( array(
			'plugin_dir' => GPLUGIN_DIR,
			'plugin_url' => GPLUGIN_URL,
			'class_filters' => 'gPluginFiltersCore',
		), apply_filters( $this->domain.'_settings_constants', $constants ) );
		
		$this->args = array_merge( array(
			'plugin_class' => false,
			'plugin_args' => array(),
			'settings_sanitize' => null, // null for default, false for disable
			'field_callback' => false,
			'site_options' => false, // site wide or blog option storing
			'register_hook' => false, // hook that runs on settings page load
		), $args );
		
		$this->options = self::get_options();
		$this->enabled = isset( $this->options['enabled'] ) ? $this->options['enabled'] : false;

		// for something like old gMember Restricted class
		// when we have to initiate a plugin module if enabled option
		$plugin_class = $this->args['plugin_class'];
		if ( $this->enabled && class_exists( $plugin_class ) ) 
			gPluginFactory( $plugin_class, $this->constants, $this->args['plugin_args'] );
	}
	
	public function setup_actions() 
	{ 
		add_action( 'init', array( & $this, 'init_late' ), 999 );	
		
		if ( $this->args['register_hook'] )
			add_action( $this->args['register_hook'], array( & $this, 'register_hook' ) );	
		else
			add_action( 'admin_init', array( & $this, 'register_hook' ) );	
	}	
    
	public function init_late()
	{
		// giving the plugin a chance to manipulate pre settings args by therir own filter hooks!
		$key = 'gplugin_settings_args_'.strtolower( get_class( $this ) );
		
		if ( has_filter( $key ) ) {
			$this->args = apply_filters( $key, $this->args );
			if ( isset( $this->args['page'] ) )
				$this->page = $this->args['page'];
				
			// recall for option defaults to avoid undefinded index notices
			$this->options = self::get_options();
		}
	}
	
	public function register_hook()
	{
		if ( is_array( $this->page ) ) {
			foreach ( $this->page as $page_name => $sections )
				$this->register_sections( $page_name, $sections );
		} else {
			if ( ! count( $this->args['sections'] ) )
				return;
		
			$this->register_sections( $this->page, $this->args['sections'] );
		}
		
		if ( $this->args['settings_sanitize'] && is_callable( $this->args['settings_sanitize'] ) )
			add_filter( 'sanitize_option_'.$this->option_group, $this->args['settings_sanitize'], 10, 2 );
		else if ( false !== $this->args['settings_sanitize'] )
			add_filter( 'sanitize_option_'.$this->option_group, array( $this, 'settings_sanitize' ), 10, 2 );
	}
	
	public function register_sections( $page_name, $sections )
	{
		register_setting( $page_name, $this->option_group );  // we added the sanitization manually

		$field_callback = $this->args['field_callback'] ? $this->args['field_callback'] : array( $this, 'do_settings_field' );
		
		foreach ( $sections as $section_name => $section_args ) {
			if ( 'gplugin' == $section_name )
				add_settings_section( 'gplugin', '&nbsp;', false, $page_name );
			else if ( false !== $section_args['title'] )
				add_settings_section( $section_name, $section_args['title'], $section_args['callback'],	$page_name );
			
			foreach ( $section_args['fields'] as $field_name => $field_args )
				add_settings_field( $this->option_group.'_'.$field_name, 
					$field_args['title'], 
					( isset( $field_args['cb'] ) && $field_args['cb'] ? $field_args['cb'] : $field_callback ), 
					$page_name, 
					$section_name, 
					array_merge( $field_args, array( 
						'field' => $field_name,
						'label_for' => $this->option_group.'['.$field_name.']',
						'option_group' => $this->option_group,
					) ) 
				);
		}
	}
	
	public function do_settings_field( $r )
	{
		$args = shortcode_atts( array( 
			'type' => 'enabled',
			'field' => false,
			'values' => array(),
			'filter' => false, // will use via sanitize
			'dir' => false,
			'default' => '',
			'desc' => '',
			'class' => '',
			'label_for' => '',
			'option_group' => $this->option_group,
		), $r );
		
		
		if ( ! $args['field'] )
			return;
		
		$name = $args['option_group'].'['.esc_attr( $args['field'] ).']';
		
		switch ( $args['type'] ) {
			case 'enabled' :
				?><select name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="<?php echo $args['class']; ?>" >
					<option value="0" <?php selected( $this->options[$args['field']], 0 ); ?>><?php esc_html_e( 'Disabled' ); ?></option>
					<option value="1" <?php selected( $this->options[$args['field']], 1 ); ?>><?php esc_html_e( 'Enabled' ); ?></option>
				</select><?php
				
				if ( $args['desc'] )
					echo '<br /><span class="description">'.esc_html( $args['desc'] ).'</span>';
				
			break;
			
			case 'text' :
				?><input type="text" class="regular-text code <?php echo $args['class']; ?>" 
					name="<?php echo $name; ?>" id="<?php echo $name; ?>" 
					value="<?php echo esc_attr( $this->options[$args['field']] ); ?>" 
					<?php if ( $args['dir'] ) echo 'dir="'.$args['dir'].'"'; ?> /><?php
				if ( $args['desc'] )
					echo '<br /><span class="description">'.esc_html( $args['desc'] ).'</span>';
					
			break;
			
			case 'checkbox' :
				if ( count( $args['values'] ) ) {
					foreach( $args['values'] as $value_name => $value_title ) {
						?><input type="checkbox" name="<?php echo $name.'['.$value_name.']'; ?>" id="<?php echo $name.'['.$value_name.']'; ?>" value="1"  class="<?php echo $args['class']; ?>" <?php 
						checked( true, in_array( $value_name, ( array ) $this->options[$args['field']] ) );?> /><?php
						?>&nbsp;<span><?php echo esc_html( $value_title ); ?></span><br /><?php
					}
				} else {
					?><input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="1"  class="<?php echo $args['class']; ?>" <?php checked( 1, $this->options[$args['field']] );?> /><?php
				}
				if ( $args['desc'] )
					echo ' <span class="description" style="vertical-align:base;">'.esc_html( $args['desc'] ).'</span>';
				
			break;
			
			case 'select' :
				// alow hiding
				if ( false !== $args['values'] ) {
					?><select name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="<?php echo $args['class']; ?>"><?php
						foreach ( $args['values'] as $value_name => $value_title ) {
							?><option value="<?php echo esc_attr( $value_name ); ?>" <?php selected( $value_name, $this->options[$args['field']] );?>><?php echo esc_html( $value_title ); ?></option><?php
						}
					?></select><?php
					if ( $args['desc'] )
						echo '<br /><span class="description">'.$args['desc'].'</span>';
				}
			break;
			
			
			default :
				echo 'Error: setting type\'s not defind';
				if ( $args['desc'] )
					echo '<br /><span class="description">'.esc_html( $args['desc'] ).'</span>';

		}
		
	}

    public function get( $field, $default = false )
	{
		if ( isset( $this->options[$field] ) ) 
			return $this->options[$field];
		return $default;
	}
	
	// must dep : use get()
    public function get_option( $field, $default = false )
	{
		return $this->get( $field, $default );
	}
	
    public function get_option_OLD( $field, $default = false )
	{
		$options = self::get_options();
		if ( isset( $options[$field] ) )
			return $options[$field];
		return $default;
	}
	
    public function get_options()
    {
		if ( $this->args['site_options'] )
			$options = get_site_option( $this->option_group, array() );
		else
			$options = get_option( $this->option_group, array() );
			
		return gPluginUtils::parse_args_r( $options, self::get_option_defaults() );
    }
	
    public function update_options( $options = null )
    {
		if ( is_null( $options ) )
			$options = $this->options;
	
		if ( $this->args['site_options'] )
			return update_site_option( $this->option_group, $options );
		else
			return update_option( $this->option_group, $options );
    }
    
    public function get_option_defaults()
    {
		$defaults = array();
		
		if ( is_array( $this->page ) ) {
			foreach ( $this->page as $page_name => $sections ) {
				foreach ( $sections as $section_name => $section_args ) {
					foreach ( $section_args['fields'] as $field_name => $field_args ) {
						//if ( 'checkbox' == $field_args['type'] && isset( $field_args['values'] ) )
						$defaults[$field_name] = $field_args['default'];
					}
				}
			}
		} else {
			foreach ( $this->args['sections'] as $section_name => $section_args ) {
				foreach ( $section_args['fields'] as $field_name => $field_args ) {
					$defaults[$field_name] = $field_args['default'];
				}
			}
		}		
		
		return (array) apply_filters( $this->option_group.'_option_defaults', $defaults );
    }
    
    public function settings_sanitize( $input )
    {
		$output = array();
		
		if ( is_array( $this->page ) ) {
			foreach ( $this->page as $page_name => $sections ) {
				$output = $this->settings_sanitize_section( $sections, $input, $output, $this->options );
			}
		} else {
			$output = $this->settings_sanitize_section( $this->args['sections'], $input, $output, $this->options );
		}
		
		return $output;
	}	
	
	public function settings_sanitize_section( $sections, $input, $output, $stored = array() ) 
	{
		foreach ( $sections as $section => $section_args ) {
			foreach ( $section_args['fields'] as $field => $field_args ) {
				// new value
				if ( isset( $input[$field] ) ) {
					
					// callback
					if ( isset( $field_args['filter'] ) && $field_args['filter'] && is_callable( $field_args['filter'] ) ) {
						$output[$field] = call_user_func_array( $field_args['filter'], array( $input[$field] ) );
					
					// disabled select
					} else if ( isset( $field_args['values'] ) && false === $field_args['values'] ){
						$output[$field] = $field_args['default'];
					
					// multiple checkboxes
					} else if ( is_array( $input[$field] ) ) {
						$output[$field] = gPluginUtils::getKeys( $input[$field] );
					
					// default
					} else {
						$output[$field] = $input[$field];
					}
				// previously stored value 
				} else if ( isset( $stored[$field] ) ) {
					$output[$field] = $stored[$field];
				// default value
				} else {
					$output[$field] = $field_args['default'];
				}
			}
		}
        return $output;
    }
    
    public function section_callback( $section )
    {
        echo '<p>Section Description</p>';
    }
} 
}