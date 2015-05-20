<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// Base class for WordPress plugins that automagically hooks things for you.
// https://gist.github.com/chrisguitarguy/3845798

if ( ! class_exists( 'gPluginComponentCore' ) ) { class gPluginComponentCore extends gPluginClassCore
{
	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->args = gPluginUtils::parse_args_r( $args, array(
			'domain' => 'gplugin',
			'title' => __( 'gPlugin', GPLUGIN_TEXTDOMAIN ),
			'network' => false,
			'component' => 'default',
			'term_meta' => false,
			'options' => array(),
			'option_group' => false,
		) );

		if ( false === $this->args['option_group'] )
			$this->inject( 'args', array( 'option_group' => $this->args['domain'] ) );

		$this->current_blog = get_current_blog_id();

		$constants = apply_filters( $this->args['domain'].'_constants', $constants );

		$this->constants = gPluginUtils::parse_args_r( $constants, array(
			'plugin_dir' => GPLUGIN_DIR,
			'plugin_url' => GPLUGIN_URL,
			'class_filters' => 'gPluginFiltersCore',
			'meta_key' => '_gplugin',
			'theme_templates_dir' => 'gplugin_templates',
		) );

		$this->options = $this->init_options();

		// bail if the plugin is in network mode
		if ( $this->args['network'] )
			return;

		if ( isset( $this->constants['class_filters'] ) )
			gPluginFactory( $this->constants['class_filters'], $constants, $args );

		if ( isset( $this->args['term_meta'] ) && $this->args['term_meta'] )
			gPluginFactory( 'gPluginTermMeta', $constants, $args ); // no point passing the arguments!
	}

	public function setup_actions()
	{
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }
	}

	function init()
	{
		// bail if the plugin is in network mode
		if ( $this->args['network'] )
			return;

		// gPlugin Local:
		if ( ! is_textdomain_loaded( GPLUGIN_TEXTDOMAIN ) )
			load_plugin_textdomain( GPLUGIN_TEXTDOMAIN, false, 'gplugin/languages' );

		// Parent Plugin Local:
		if ( ! is_textdomain_loaded( $this->args['domain'] ) )
			$this->load_textdomain();

		// init here to help filtering the templates
		if ( isset( $this->constants['class_mustache'] ) )
			call_user_func( array( $this->constants['class_mustache'], 'init' ) );

	}

	function plugins_loaded() {}
    function admin_init() {}
	function load_textdomain() {}

	function is_admin() { return gPluginWPHelper::is_admin(); } // for ajax calls
    function getConstants() { return $this->constants; }
    function getArgs() { return $this->args; }
	function getOptions() { return $this->options; }

	// UNFINISHED!
	function textdomain() {

		// try to get locale
		$locale = apply_filters( 'bp_invitation_load_textdomain_get_locale', get_locale() );

		// if we found a locale, try to load .mo file
		if ( !empty( $locale ) ) {
			// default .mo file path
			$mofile_default = sprintf( '%s/languages/%s-%s.mo', $this->plugin_dir, 'bp-invitation', $locale );
			// final filtered file path
			$mofile = apply_filters( 'bp_invitation_textdomain_mofile', $mofile_default );
			// make sure file exists, and load it
			if ( file_exists( $mofile ) ) {
				load_textdomain( 'bp-invitation', $mofile );
			}
		}
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

    // CAUTION : must not use for the first time calling the class
	function getOption( $name, $default = false )
	{
        //$class= get_class();
        //$the_class = $class::getInstance();
        //$options = $the_class::getOptions();
        //return ( isset( $this->options[$name] ) ? $this->options[$name] : $default ) ;

		if ( isset( $this->options[$name] ) )
			return $this->options[$name];
		return $default;
	}

	function get_option( $name, $default = false )
	{
		return ( isset( $this->options[$name] ) ? $this->options[$name] : $default ) ;
	}

	function update_option( $name, $value )
	{
		$this->options[$name] = $value;
		$options = get_option( $this->args['option_group'], false );
		if ( $options === false ) $options = array();
		$options[$name] = $value;
		return update_option( $this->args['option_group'], $options );
	}

	function delete_option( $name )
	{
		$options = get_option( $this->args['option_group'] );
		if ( $options === false ) $options = array();
		unset( $this->options[$name] );
		unset( $options[$name] );
		return update_option( $this->args['option_group'], $options );
	}

	function init_options()
	{
        $defaults = $this->getFilters( $this->args['option_group'].'_defaults' );
		//gPeopleComponentCore::dump($defaults); die();

		$options = get_option( $this->args['option_group'], false );
		if ( $options === false ) {
			// must uncommnet after the Settings UI finished.
			//add_action( 'admin_notices', array( $this, 'admin_notices_configure' ) );
			$options = $defaults;
		} else {
            foreach ( $defaults as $key => $value )
                if ( ! isset( $options[$key] ) )
                    $options[$key] = $value;
        }
		return $options;
	}

    function admin_notices_configure()
    {
        if ( WP_DEBUG )
			return;
        echo '<div class="error"><p><a href="options-general.php?page='.$this->args['domain'].'">'.sprintf( __( '%s is not configured yet.', GPLUGIN_TEXTDOMAIN ), $this->args['title'] ).'</a></p></div>';
    }

	function get_postmeta( $post_id, $field = false, $default = '', $key = null )
	{
		return self::get_meta( 'post', $post_id, $field, $default, $key );
	}

	function get_termmeta( $term_id, $field = false, $default = '', $key = null )
	{
		return self::get_meta( 'term', $term_id, $field, $default, $key );
	}

	function sanitize_meta_key( $key, $from = 'post' )
	{
		if ( is_null( $key ) ) {
			if ( isset( $this->constants[$from.'_meta_key'] ) )
				$key = $this->constants[$from.'_meta_key'];
			else if ( isset( $this->constants[$this->args['component'].'_meta_key'] ) )
				$key = $this->constants[$this->args['component'].'_meta_key'];
			else
				$key = $this->constants['meta_key'];
		}
		return $key;
	}

	function get_meta( $from, $id, $field = false, $default = '', $key = null )
	{
		$key = self::sanitize_meta_key( $key, $from );
		//echo $key;
		switch( $from ) {
			case 'user' :
				$meta = get_user_meta( $id, $key, false );
			break;
			case 'term' :
				$meta = gPluginTermMeta::get_term_meta( $id, $key, true );
			break;
			case 'post' :
			default :
				$meta = get_metadata( 'post', $id, $key, true );
		}

        if ( empty( $meta ) )
            return $default;

		if ( false === $field )
			return $meta;

		if ( isset( $meta[$field] ) )
			return $meta[$field];

		return $default;
	}

	function update_postmeta( $post_id, $value, $field = false, $key = null )
	{
		return self::update_meta( 'post', $post_id, $value, $field, $key );
	}

	function update_meta( $to, $id, $value, $field = false, $key = null )
	{
		$key = self::sanitize_meta_key( $key, $to );

		if ( false === $field ) {
			$meta = $value;
		} else {
			$meta = self::get_meta( $to, $id, false, array(), $key );
			$meta[$field] = $value;
		}

		switch( $to ) {

			case 'user' :
				if ( false === $value )
					delete_user_meta( $id, $key );
				else
					update_user_meta( $id, $key, $meta );
			break;

			case 'term' :
				if ( false === $value )
					gPluginTermMeta::delete_term_meta( $id, $key );
				else
					gPluginTermMeta::update_term_meta( $id, $key, $meta );
			break;

			case 'post' :
			default :
				if ( false === $value )
					delete_post_meta( $id, $key );
				else
					update_post_meta( $id, $key, $meta );
		}

		wp_cache_flush();
		return $id;
	}

    // http://justintadlock.com/archives/2010/08/20/linking-terms-to-a-specific-post
    // NO NEED : we store the people post id
	// must move to : wphelper
    function get_post_id_by_slug( $slug, $post_type )
    {
        global $wpdb;
        $slug = rawurlencode( urldecode( $slug ) );
        $slug = sanitize_title( basename( $slug ) );
        $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s", $slug, $post_type ) );

        if ( is_array( $post_id ) )
            return $post_id[0];
        elseif ( !empty( $post_id ) );
            return $post_id;

        return false;
    }

	function get_template_part( $slug, $name = null, $load = true )
	{
		do_action( 'get_template_part_'.$slug, $slug, $name ); // standard WP action

		$templates = array();
		if ( isset( $name ) )
			$templates[] = $slug.'-'.$name.'.php';
		$templates[] = $slug.'.php';

		$templates = apply_filters( $this->args['domain'].'_get_template_part', $templates, $slug, $name );
		return $this->locate_template( $templates, $load, false );
	}

	function locate_template( $template_names, $load = false, $require_once = true )
	{
		$located = false;
		foreach ( (array) $template_names as $template_name ) {
			if ( empty( $template_name ) )
				continue;

			$template_name = untrailingslashit( $template_name );
			if ( file_exists( get_stylesheet_directory().DS.$this->constants['theme_templates_dir'].DS.$template_name ) ) {
				$located = get_stylesheet_directory().DS.$this->constants['theme_templates_dir'].DS.$template_name;
				break;
			} elseif ( file_exists( get_template_directory().DS.$this->constants['theme_templates_dir'].DS.$template_name ) ) {
				$located = get_template_directory().DS.$this->constants['theme_templates_dir'].DS.$template_name;
				break;
			} elseif ( file_exists( $this->constants['plugin_dir'].DS.'templates'.DS.$template_name ) ) {
				$located = $this->constants['plugin_dir'].DS.'templates'.DS.$template_name;
				break;
			}
		}

		if ( ( true == $load ) && ! empty( $located ) )
			load_template( $located, $require_once );

		return $located;
	}

    // TODO : add our own load_template()
} }
