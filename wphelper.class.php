<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
if ( ! class_exists( 'gPluginWPHelper' ) ) { class gPluginWPHelper
{
	/** ---------------------------------------------------------------------------------
						USED FUNCTION: Modyfy with Caution!
	--------------------------------------------------------------------------------- **/

	// Checks for the current post type
	// FROM: EditFlow 0.7
	// @return string|null $post_type The post type we've found, or null if no post type
	public static function getCurrentPostType()
	{
		global $post, $typenow, $pagenow, $current_screen;

		if ( $post && $post->post_type )
			$post_type = $post->post_type;
		elseif ( $typenow )
			$post_type = $typenow;
		elseif ( $current_screen && isset( $current_screen->post_type ) )
			$post_type = $current_screen->post_type;
		elseif ( isset( $_REQUEST['post_type'] ) )
			$post_type = sanitize_key( $_REQUEST['post_type'] );
		else
			$post_type = null;

		return $post_type;
	}

	public static function notice( $notice, $class = 'updated fade', $echo = true )
	{
		$html = sprintf( '<div id="message" class="%s"><p>%s</p></div>', $class, $notice );
		if ( ! $echo )
			return $html;
		echo $html;
	}

	// from gMemberHelper
	public static function get_current_site_blog_id()
    {
        if ( ! is_multisite() )
            return get_current_blog_id();

        global $current_site;
        return absint( $current_site->blog_id );
    }


	// http://kovshenin.com/2011/attachments-filename-and-directory-in-wordpress/
	// an absolute path (filesystem path, not URL) to the location of your attachment file.
	public static function get_attachmnet_path( $post_id, $uploads = null )
	{
		if ( is_null( $uploads ) )
			$uploads = wp_upload_dir();

		return str_replace( $uploads['baseurl'], $uploads['basedir'], wp_get_attachment_url( $post_id ) );
	}



	// http://www.stephenharris.info/2012/get-post-content-by-id/
	// Display the post content. Optinally allows post ID to be passed
	/**
	 * @param int $id Optional. Post ID.
	 * @param string $more_link_text Optional. Content for when there is more text.
	 * @param bool $stripteaser Optional. Strip teaser content before the more text. Default is false.
	 */
	public static function the_content_by_id( $post_id = 0, $more_link_text = null, $stripteaser = false )
	{
		global $post;
		$post = get_post( $post_id );
		setup_postdata( $post, $more_link_text, $stripteaser );
		the_content();
		wp_reset_postdata( $post );
	}


	// PROBABLY : the infinite loop!!
	// Originally from Easy Digital Downloads
	// _edd_die_handler()
	public static function _die_handler()
	{
		if ( defined( 'GPLUGIN_UNIT_TESTS' ) )
			return array( __CLASS__, '_die_handler' );
		else
			die();
	}

	/**
	 * Wrapper function for wp_die(). This function adds filters for wp_die() which
	 * kills execution of the script using wp_die(). This allows us to then to work
	 * with functions using edd_die() in the unit tests.
	 * @author Sunny Ratilal
	 */
	// Originally from Easy Digital Downloads
	// edd_die()
	public static function _die()
	{
		add_filter( 'wp_die_ajax_handler', array( __CLASS__, '_die_handler' ), 10, 3 );
		add_filter( 'wp_die_handler', array( __CLASS__, '_die_handler' ), 10, 3 );
		wp_die('');
	}

	// Originally from Easy Digital Downloads
	// Get the current page URL
	// edd_get_current_page_url()
	public static function get_current_page_url()
	{
		if ( is_front_page() ) {
			$page_url = home_url();
		} else {
			$page_url = 'http';

		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
			$page_url .= "s";

		$page_url .= "://";

		if ( $_SERVER["SERVER_PORT"] != "80" )
			$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else
			$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		return esc_url( $page_url );
	}

	// FROM: gTheme 2
	// debug on production env
	public static function is_debug()
	{
		if ( WP_DEBUG && WP_DEBUG_DISPLAY && ! self::is_dev() )
			return true;

		return false;
	}

	// FROM: gTheme 2
	// debug on developmnet env
	public static function is_dev()
	{
		if ( defined( 'WP_STAGE' )
			&& 'development' == constant( 'WP_STAGE' ) )
				return true;

		return false;
	}

	// FROM: EDD
	function is_caching()
	{
		return ( function_exists( 'wpsupercache_site_admin' ) || defined( 'W3TC' ) );
		//return apply_filters( 'edd_is_caching_plugin_active', $caching );
	}

    public static function getUserRols( $object = false )
    {
        $roles = ( $object ? new stdClass : array() );
        $wp_roles = get_editable_roles();
        foreach( $wp_roles as $role_name => $role )
            if ( $object )
                $roles->{$role_name} = translate_user_role( $role['name'] );
            else
                $roles[$role_name] = translate_user_role( $role['name'] );
        return $roles;
    }

	// Originally From : http://wp.tutsplus.com/tutorials/creative-coding/add-a-custom-column-in-posts-and-custom-post-types-admin-screen/
	public static function get_featured_image_src( $post_id, $size = 'thumbnail', $default = false )
	{
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $post_thumbnail_id ) {
			$post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, $size );
			return $post_thumbnail_img[0];
		}
		return $default;
	}

    public static function get_user_edit_link( $user_ID )
    {
        return add_query_arg( 'wp_http_referer', urlencode( stripslashes( $_SERVER['REQUEST_URI'] ) ), 'user-edit.php?user_id='.$user_ID );
    }

	// https://gist.github.com/boonebgorges/4165099
	// Are we looking at the WordPress admin?
	// Because AJAX requests are sent to wp-admin/admin-ajax.php, WordPress's is_admin() function returns true when DOING_AJAX. This function contains logic to test whether AJAX requests are coming from the front end or from the Dashboard.
	public static function is_admin()
	{
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// wp_get_referer() should contain the URL of the requesting
			// page. Check to see whether it's an admin page
			$is_admin = 0 === strpos( wp_get_referer(), admin_url() );
		} else {
			$is_admin = is_admin();
		}

		return $is_admin;
	}


	/**
	* Generates a domain-mapping safe URL on WordPress.com
	* Core's ajaxurl uses admin_url() which returns *.wordpress.com which doesn't work for the front-end on domain-mapped sites.
	* This works around that and generates the correct URL based on context.
	*/
	// https://gist.github.com/tollmanz/1986992
	function admin_ajax_url( $path = '' )
	{
		if ( is_admin() )
			$url = admin_url( 'admin-ajax.php' );
		else
			$url = home_url( 'wp-admin/admin-ajax.php' );

		$url .= ltrim( $path, '/' );

		return $url;
	}

	function redirect_whitelist( $request_uri = null )
	{
		if ( is_null( $request_uri ) )
			$request_uri = $_SERVER['REQUEST_URI'];

		return gPluginUtils::strpos_arr( $request_uri, array(
			'wp-login.php',
			'wp-signup.php',
			'wp-activate.php',
			'xmlrpc.php',
			'wp-admin',
		) );
	}






	/** ---------------------------------------------------------------------------------
									NOT USED YET
	--------------------------------------------------------------------------------- **/

	// img : spinner / wpspin_light
	public static function imgSpin( $img = 'spinner', $large = false  )
	{
		return esc_url( admin_url( 'images/'.$img.( $large ? '-2x' : '' ).'.gif' ) );
	}

	// based on bp
	public static function username_from_email( $email, $strict = true )
	{
		return preg_replace( '/\s+/', '', sanitize_user( preg_replace( '/([^@]*).*/', '$1', $email ), $strict ) );
	}

	// UNFINISHED
	// update post meta by array
	// build for speed!
	function update_post_meta( $post_id, $meta_array )
	{
		// make sure meta is added to the post, not a revision
		if ( $the_post = wp_is_post_revision( $post_id ) )
			$post_id = $the_post;

		return update_metadata( 'post', $post_id, $meta_key, $meta_value, '' );
	}




	// http://tommcfarlin.com/save-custom-post-meta/
	// https://gist.github.com/tommcfarlin/4468321
	// if( user_can_save( $post_id, 'meta_data_nonce' ) )
	/**
	* An example function used to demonstrate how to use the `user_can_save` function
	* that provides boilerplate security checks when saving custom post meta data.
	*
	* The ultimate goal is provide a simple helper function to be used in themes and
	* plugins without the need to use a set of complex conditionals and constants.
	*
	* Instead, the aim is to have a simplified function that's easy to read and that uses
	* WordPress APIs.
	*
	* The DocBlocks should provide all information needed to understand how the function works.
	*/
	/**
	* Determines whether or not the current user has the ability to save meta data associated with this post.
	*
	* @param int $post_id The ID of the post being save
	* @param bool Whether or not the user has the ability to save this post.
	*/
	function user_can_save( $post_id, $nonce )
	{
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) );

		// Return true if the user is able to save; otherwise, false.
		return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

	}






    function is_plugin_active( $plugin )
    {
        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || self::is_plugin_active_for_network( $plugin );
    }

    function is_plugin_active_for_network( $plugin )
    {
        if ( ! is_multisite() )
            return false;

        $plugins = get_site_option( 'active_sitewide_plugins' );
        if ( isset( $plugins[$plugin] ) )
            return true;

        return false;
    }

	// NOT WORKING!!!! ON ADMIN
	// http://kovshenin.com/2012/current-url-in-wordpress/
	// http://www.stephenharris.info/2012/how-to-get-the-current-url-in-wordpress/
	function getCurrentURL( $trailingslashit = false )
	{
		global $wp;

		if ( is_admin() )
			$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
        else
            $current_url = home_url( add_query_arg( array(), ( empty( $wp->request ) ? false : $wp->request ) ) );

		if ( $trailingslashit )
			return trailingslashit( $current_url );

		return $current_url;
	}

	function getRequestURI()
	{
		return stripslashes_deep( $_SERVER['REQUEST_URI'] );
	}







	// http://gilbert.pellegrom.me/wordpress-get_blog_url
	// Have you ever needed to find the WordPress blog URL? Using home_url() is fine but what if your Settings > Reading options
	// in WordPress are set to your blog having a static page (usually called �Blog�). You may need to know what the URL of that page is.
	// So here is a quick function I came up with to find it.
	function get_blog_url()
	{
        if( $posts_page_id = get_option( 'page_for_posts' ) ){
            return home_url( get_page_uri( $posts_page_id ) );
        } else {
            return home_url();
        }
    }



	// http://webdevstudios.com/2013/04/03/how-to-quickly-grab-post-fields-outside-the-loop-with-get_post_field-in-wordpress/
	// http://codex.wordpress.org/Function_Reference/get_post#Return
	function get_post_field( $field, $post, $default = '', $context = 'display' )
	{
		$post = get_post( $post );

		if ( ! $post )
			return $default;

		if ( ! isset( $post->$field ) )
			return $default;

		return sanitize_post_field( $field, $post->$field, $post->ID, $context );
	}


	function get_updates( $basename )
	{
		$updates = get_plugin_updates();
		//$basename = plugin_basename(__FILE__);
		if ( isset( $updates[$basename] ) ) {
			$update = $updates[$basename];
			//echo '<div class="error"><p><strong>';
			//printf( __( 'A new version of this importer is available. Please update to version %s to ensure compatibility with newer export files.', 'wordpress-importer' ), $update->update->new_version );
			//echo '</strong></p></div>';
			return $update->update->new_version;
		}
		return false;
	}

	// Originally from : http://wordpress.org/extend/plugins/kimili-flash-embed/
	function isMinimumWPVersion( $minimum_version )
	{
		return ( version_compare( get_bloginfo( 'version' ), $minimum_version ) >= 0 );
	}


	// http://wordpress.org/plugins/sem-autolink-uri/

	// Originally from : http://wordpress.org/extend/plugins/categories-autolink/
	function linkify( $text, $terms )
	{
		foreach ( $terms as $name => $link )
			$text = preg_replace( "|(?!<[^<>]*?)(?<![?./&])\b($name)\b(?!:)(?![^<>]*?>)|imsU", "<a href=\"$link\">$1</a>", $text );
		return $text;
	}

	// UNFINISHED!!
	function hashify( $text, $callback = false )
	{
		// http://stackoverflow.com/a/7408417/642752
		// Assuming your strings are common CSS names (alphanumeric + dash)
		// $text = preg_replace( '/(#[\w-]+)/', '$1' . $stringtoappend, $text );

		// http://stackoverflow.com/questions/11138191/preg-match-all-after-hash-tag-before-next-hash-tag-in-a-string

	}

	// UNFINISHED!!
	function mentionify( $text, $callback = false )
	{
		// http://stackoverflow.com/a/10384173/642752
		// $text = preg_replace('/@([^@ ]+)/', '<a href="/$1">@$1</a> ', $text );

		// http://stackoverflow.com/a/10384251/642752
		// '@name1 kdfjd fkjd as@name2 @ lkjlkj @name3'
		// preg_match_all('/(^|\s)(@\w+)/', $text, $result );
		// var_dump($result[2]);
		// http://ideone.com/AcXO3


		// http://stackoverflow.com/questions/7150652/regex-valid-twitter-mention
		// http://stackoverflow.com/questions/6673944/regular-expression-to-find-and-replace-mentions-like-twitter

		// BETTER :
		// http://stackoverflow.com/questions/9465486/php-preg-replace-regex-mention-username

	}





	// adapt!
    function getUsers( $all_fields = false )
    {
        $users = get_users( array(
            'blog_id' => '', // TODO : add option to include entire network users / what if it changes and then the stored user id points to ???
            'orderby' => 'display_name',
            'fields' => ( $all_fields ? 'all_with_meta' : 'all' ),
        ) );
        return gPluginForm::reKey( $users, 'ID' );
    }


} }
