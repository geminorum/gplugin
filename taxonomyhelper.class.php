<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
if ( ! class_exists( 'gPluginTaxonomyHelper' ) ) { class gPluginTaxonomyHelper
{

	// *********** https://gist.github.com/helenhousandi/1573966
	
	// http://scribu.net/wordpress/sortable-taxonomy-columns.html
	// https://gist.github.com/scribu/856587
	
	// http://scribu.net/wordpress/custom-sortable-columns.html
	// https://gist.github.com/scribu/906872


	/** ---------------------------------------------------------------------------------
						USED FUNCTION: Modyfy with Caution!
	--------------------------------------------------------------------------------- **/

	// USED WHEN: admin edit table
	public static function get_admin_terms_edit( $post_id, $post_type, $taxonomy, $glue = ', ', $empty = '&#8212;' )
	{
		$taxonomy_object = get_taxonomy( $taxonomy );
		//gnetwork_dump( $taxonomy ); die();
		if ( $terms = get_the_terms( $post_id, $taxonomy ) ) {
			$out = array();
			foreach ( $terms as $t ) {
				$posts_in_term_qv = array();
				if ( 'post' != $post_type )
					$posts_in_term_qv['post_type'] = $post_type;
				if ( $taxonomy_object->query_var ) {
					$posts_in_term_qv[ $taxonomy_object->query_var ] = $t->slug;
				} else {
					$posts_in_term_qv['taxonomy'] = $taxonomy;
					$posts_in_term_qv['term'] = $t->slug;
				}

				$out[] = sprintf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( $posts_in_term_qv, 'edit.php' ) ),
					esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) )
				);
			}
			//echo join( __( ', ' ), $out );
			return join( $glue, $out );
		} else {
			return $empty;
		}
	}
	
    public static function update_count_callback( $terms, $taxonomy )
    {
        global $wpdb;
        foreach ( (array) $terms as $term ) {
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
            do_action( 'edit_term_taxonomy', $term, $taxonomy );
            $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
            do_action( 'edited_term_taxonomy', $term, $taxonomy );
        }
    }
	
    public static function insert_default_terms( $taxonomy, $defaults )
    {
        if ( ! taxonomy_exists( $taxonomy ) )
			return false;

		foreach ( $defaults as $term_slug => $term_name )
			if ( ! term_exists( $term_slug, $taxonomy ) )
				wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug ) );

		return true;
    }	
	
	public static function prepare_terms( $taxonomy, $args = array(), $terms = null, $key = 'term_id', $obj = true )
	{
		$new_terms = array(); 
		
		if ( is_null( $terms ) )
			$terms = get_terms( $taxonomy, array_merge( array( 
				'hide_empty' => false,
				'orderby' => 'name',
				'order' => 'ASC' 
			), $args ) );
		
		foreach( $terms as $term ) {
			$new = array(
				'name' => $term->name,
				'description' => $term->description,
				'excerpt' => $term->description,
				'link' => get_term_link( $term, $taxonomy ),
				'count' => $term->count,
				'parent' => $term->parent,
				'slug' => $term->slug,
				'id' => $term->term_id,
				);
			$new_terms[$term->{$key}] = $obj ? (object) $new : $new;
		}
		
		// TODO : use cache
		
		return $new_terms;
	}





	
	
	

	/** ---------------------------------------------------------------------------------
									NOT USED YET
	--------------------------------------------------------------------------------- **/	
	
	
	// Remove a given term from the specified post. This function is missing from core WordPress.
	// https://gist.github.com/mjangda/1506353
	// maybe use : http://codex.wordpress.org/Function_Reference/wp_remove_object_terms
	// above added since WP3.6
	function remove_post_term( $post_id, $term, $taxonomy ) 
	{
	 
		if ( ! is_numeric( $term ) ) {
			$term = get_term( $term, $taxonomy );
			if ( ! $term || is_wp_error( $term ) )
				return false;
			$term_id = $term->term_id;
		} else {
			$term_id = $term;
		}
		 
		// Get the existing terms and only keep the ones we don't want removed
		$new_terms = array();
		$current_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		 
		foreach ( $current_terms as $current_term ) {
			if ( $current_term != $term_id )
				$new_terms[] = intval( $current_term );
		}
		 
		return wp_set_object_terms( $post_id, $new_terms, $taxonomy );
	}

	// if not exist, create and and term to a post 
	// http://stackoverflow.com/a/2436534
	function add_post_term( $id, $term, $tax ) 
	{

		$term_id = is_term($term);
		$term_id = intval($term_id);
		if (!$term_id) {
			$term_id = wp_insert_term($term, $tax);
			$term_id = $term_id['term_id'];
			$term_id = intval($term_id);
		}

		// get the list of terms already on this object:
		$terms = wp_get_object_terms($id, $tax);
		$terms[] = $term_id;

		$result =  wp_set_object_terms($id, $terms, $tax, FALSE);

		return $result;
	}
	
	
	
	// https://gist.github.com/danielbachhuber/2922627	
	// Check if another blog has a given taxonomy
	/**
	 * Check if another blog on the network has a taxonomy registered
	 * We check by seeing if there's a term in that taxonomy, so this
	 * approach will only work if that's the case.
	 *
	 * I didn't actually test this... you obviously should :)
	 *
	 * @see https://twitter.com/trepmal/status/212766835504984065
	 */
	function dbx_blog_has_taxonomy( $_blog_id, $taxonomy = 'author' ) 
	{
		// This isn't cached but you'd want to cache it heavily as you
		// don't want to run switch_to_blog() on every pageload
		switch_to_blog( $_blog_id );
		global $wpdb;
		$query = $wpdb->prepare( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy=%s LIMIT 1", $taxonomy );
		$result = (bool)$wpdb->get_var( $query );
		restore_current_blog();
		return $result;
	}	
	

	// dep!
	// based on WP get_term_by()
	// returns array of matched terms
	function search_term_by( $field, $value, $taxonomy, $output = OBJECT, $filter = 'raw' )
	{
		global $wpdb;

		if ( ! taxonomy_exists( $taxonomy ) )
			return false;

		if ( 'slug' == $field ) {
			$field = 't.slug';
			$value = sanitize_title( $value );
			if ( empty($value) )
				return false;
		} else if ( 'name' == $field ) {
			// Assume already escaped
			$value = wp_unslash( $value );
			$field = 't.name';
		} else {
			$term = get_term( (int) $value, $taxonomy, $output, $filter );
			if ( is_wp_error( $term ) )
				$term = false;
			return $term;
		}

		$term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND $field LIKE %%s% LIMIT 1", $taxonomy, $value ) );
		if ( !$term )
			return false;

		wp_cache_add($term->term_id, $term, $taxonomy);

		$term = apply_filters( 'get_term', $term, $taxonomy);
		$term = apply_filters( "get_$taxonomy", $term, $taxonomy);
		$term = sanitize_term( $term, $taxonomy, $filter);

		if ( $output == OBJECT ) {
			return $term;
		} elseif ( $output == ARRAY_A ) {
			return get_object_vars($term);
		} elseif ( $output == ARRAY_N ) {
			return array_values(get_object_vars($term));
		} else {
			return $term;
		}	
	}
	
	


	// http://wordpress.mfields.org/2010/remove-taxonomy-box-from-wordpress-administration-panels/
    function mfields_hide_taxonomies_from_admin() {
        global $wp_taxonomies;
        $hide = array(
            'symbolism'
            );
        foreach( $wp_taxonomies as $name => $taxonomy ) {
            if( in_array( $name, $hide ) ) {
                remove_meta_box( 'tagsdiv-' . $name, 'post', 'side' );
                add_meta_box(
                    'mfields_taxonomy_ui_' . $name,
                    $taxonomy->label,
                    'my_custom_taxonomy_handler_function',
                    'post',
                    'side',
                    'core',
                    array( 'taxonomy' => $name )
                    );
            }
        }
    } // add_action( 'add_meta_boxes', 'mfields_hide_taxonomies_from_admin' );




	// http://wordpress.mfields.org/2010/set-default-terms-for-your-custom-taxonomies-in-wordpress-3-0/
	/**
	 * Define default terms for custom taxonomies in WordPress 3.0.1
	 *
	 * @author    Michael Fields     http://wordpress.mfields.org/
	 * @props     John P. Bloch      http://www.johnpbloch.com/
	 *
	 * @since     2010-09-13
	 * @alter     2010-09-14
	 *
	 * @license   GPLv2
	 */
	function mfields_set_default_object_terms( $post_id, $post ) {
		if ( 'publish' === $post->post_status ) {
			$defaults = array(
				'post_tag' => array( 'taco', 'banana' ),
				'monkey-faces' => array( 'see-no-evil' ),
				);
			$taxonomies = get_object_taxonomies( $post->post_type );
			foreach ( (array) $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $post_id, $taxonomy );
				if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
					wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
				}
			}
		}
	} // add_action( 'save_post', 'mfields_set_default_object_terms', 100, 2 );
	
	// http://wordpress.mfields.org/2010/set-default-terms-for-your-custom-taxonomies-in-wordpress-3-0/
    function save_post( $post_id, $post ) 
    {
        $gBookHomePlugin =& gBookHomePlugin::getInstance();
        $options = $gBookHomePlugin->getOptions();            

        if ( 'publish' === $post->post_status ) {
            $defaults = array( 'calendar_type' => array( $options['default_type'] ) ); // ? : don't need really, must add it anyways.
            if ( false !== $options['default_location'] ) 
				$defaults['location'] = array( $options['default_location'] );
            
            $taxonomies = get_object_taxonomies( $post->post_type );
            foreach ( (array) $taxonomies as $taxonomy ) {
                $terms = wp_get_post_terms( $post_id, $taxonomy );
                if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                    wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
                }
            }
        }
    } //add_action( 'save_post', array( &$this, 'save_post' ), 100, 2 );    
    
	// Can't add initial calendars on activation, because taxonomy isn't yet registered
    function register_default_calendar_types( $flag = 'gevent_registering_cal_types' )
    {
        if ( taxonomy_exists( 'calendar_type' ) ) {
            $gBookHomePlugin =& gBookHomePlugin::getInstance();
            $options = $gBookHomePlugin->getOptions();            
            foreach ( $options['types'] as $type => $details ) {
                if ( false !== $details && ! term_exists( $type, 'calendar_type' ) ) {
                    wp_insert_term( $details['title'], 'calendar_type', array(
                        'description'=> $details['desc'],
                        'slug' => $type,
                    ) );
                }
            }
            delete_option( $flag );
        }
    }	
	
} }