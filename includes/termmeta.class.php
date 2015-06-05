<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

//if ( ! defined( 'GTERMMETA_DB_VERSION' ) ) define( 'GTERMMETA_DB_VERSION', '1' );
//if ( ! defined( 'GTERMMETA_FILE' ) ) define( 'GTERMMETA_FILE', constant( 'GPEOPLE_FILE' ) );

if ( ! class_exists( 'gPluginTermMeta' ) ) { class gPluginTermMeta extends gPluginClassCore
{
	/*
	 * Based on Simple Term Meta by Jacob M Goldman : http://www.cmurrayconsulting.com/software/wordpress-simple-term-meta/
	 */

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->set_args( array(
			'db_version' => '1',
		), $args );
	}

	public function setup_actions()
	{
		// http://justintadlock.com/archives/2011/06/09/install-and-upgrade-functions-for-plugins-and-themes

		// NO NEED : probably, because we call it manually upon enableing the remote
		//register_activation_hook( constant( 'GTERMMETA_FILE' ), array( &$this, 'activation_hook' ) );

		//register_uninstall_hook( constant( 'GTERMMETA_FILE' ), array( &$this, 'uninstall_hook' ) );
		//register_uninstall_hook( constant( 'GTERMMETA_FILE' ), 'gPluginTermMeta::uninstall_hook' );
		//register_deactivation_hook( constant( 'GTERMMETA_FILE' ), 'gPluginTermMeta::deactivation_hook' );


		//register_uninstall_hook( constant( 'GTERMMETA_FILE' ), array( 'gPluginTermMeta', 'uninstall_hook' ) );
		add_action( 'init', array( $this, 'define_table' ) );
	}

	public static function activation_hook( $table_db_version = '1' )
	{
		global $wpdb;
		$table_name = $wpdb->prefix.'termmeta';

		if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) :

			$sql = "CREATE TABLE ".$table_name." (
			meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			term_id bigint(20) unsigned NOT NULL DEFAULT '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY (meta_id),
			KEY term_id (term_id),
			KEY meta_key (meta_key)
			);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		endif;

		update_option( 'gtermmeta_db_version', $table_db_version );
	}

	public function uninstall_hook()
	{
		global $wpdb;
		$table_name = $wpdb->prefix.'termmeta';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		delete_option( 'gtermmeta_db_version' );
	}

	public function define_table()
	{
		global $wpdb;
		$wpdb->termmeta = $wpdb->prefix.'termmeta';
	}

	/**
	* Updates metadata cache for list of term IDs.
	*
	* Performs SQL query to retrieve the metadata for the term IDs and updates the
	* metadata cache for the terms. Therefore, the functions, which call this
	* function, do not need to perform SQL queries on their own.
	*
	* @param array $term_ids List of post IDs.
	* @return bool|array Returns false if there is nothing to update or an array of metadata.
	*/
	public static function update_termmeta_cache( $term_ids )
	{
		return update_meta_cache( 'term', $term_ids );
	}

	/**
	* Add meta data field to a term.
	*
	* @param int $term_id Term ID.
	* @param string $key Metadata name.
	* @param mixed $value Metadata value.
	* @param bool $unique Optional, default is false. Whether the same key should not be added.
	* @return bool False for failure. True for success.
	*/
	public static function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false )
	{
		return add_metadata( 'term', $term_id, $meta_key, $meta_value, $unique );
	}

	/**
	* Remove metadata matching criteria from a term.
	*
	* You can match based on the key, or key and value. Removing based on key and
	* value, will keep from removing duplicate metadata with the same key. It also
	* allows removing all metadata matching key, if needed.
	*
	* @param int $term_id Term ID
	* @param string $meta_key Metadata name.
	* @param mixed $meta_value Optional. Metadata value.
	* @return bool False for failure. True for success.
	*/
	public static function delete_term_meta( $term_id, $meta_key, $meta_value = '' )
	{
		return delete_metadata( 'term', $term_id, $meta_key, $meta_value );
	}

	/**
	* Retrieve term meta field for a term.
	*
	* @param int $term_id Term ID.
	* @param string $key The meta key to retrieve.
	* @param bool $single Whether to return a single value.
	* @return mixed Will be an array if $single is false. Will be value of meta data field if $single
	*  is true.
	*/
	public static function get_term_meta( $term_id, $key, $single = false )
	{
		return get_metadata( 'term', $term_id, $key, $single );
	}

	/**
	* Update term meta field based on term ID.
	*
	* Use the $prev_value parameter to differentiate between meta fields with the
	* same key and term ID.
	*
	* If the meta field for the term does not exist, it will be added.
	*
	* @param int $term_id Term ID.
	* @param string $key Metadata key.
	* @param mixed $value Metadata value.
	* @param mixed $prev_value Optional. Previous value to check before removing.
	* @return bool False on failure, true if success.
	*/
	public static function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' )
	{
		return update_metadata( 'term', $term_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	* Delete everything from term meta matching meta key.
	*
	* @param string $term_meta_key Key to search for when deleting.
	* @return bool Whether the term meta key was deleted from the database
	*/
	public static function delete_term_meta_by_key( $term_meta_key )
	{
		if ( !$term_meta_key )
			return false;

		global $wpdb;
		$term_ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT term_id FROM $wpdb->termmeta WHERE meta_key = %s", $term_meta_key));
		if ( $term_ids ) {
			$termmetaids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->termmeta WHERE meta_key = %s", $term_meta_key ) );
			$in = implode( ',', array_fill(1, count($termmetaids), '%d'));
			do_action( 'delete_termmeta', $termmetaids );
			$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->termmeta WHERE meta_id IN($in)", $termmetaids ));
			do_action( 'deleted_termmeta', $termmetaids );
			foreach ( $term_ids as $term_id )
				wp_cache_delete($term_id, 'term_meta');
			return true;
		}
		return false;
	}


	/**
	* Retrieve term meta fields, based on term ID.
	*
	* The term meta fields are retrieved from the cache, so the function is
	* optimized to be called more than once. It also applies to the functions, that
	* use this function.
	*
	* @param int $term_id term ID
	* @return array
	*/
	public static function get_term_custom( $term_id )
	{
		$term_id = (int) $term_id;

		if ( ! wp_cache_get( $term_id, 'term_meta' ) )
			self::update_termmeta_cache( $term_id );

		return wp_cache_get( $term_id, 'term_meta' );
	}


	/**
	* Retrieve meta field names for a term.
	*
	* If there are no meta fields, then nothing (null) will be returned.
	*
	* @param int $term_id term ID
	* @return array|null Either array of the keys, or null if keys could not be retrieved.
	*/
	public static function get_term_custom_keys( $term_id )
	{
		$custom = self::get_term_custom( $term_id );

		if ( ! is_array( $custom) )
			return;

		if ( $keys = array_keys( $custom ) )
			return $keys;
	}

	/**
	* Retrieve values for a custom term field.
	*
	* The parameters must not be considered optional. All of the term meta fields
	* will be retrieved and only the meta field key values returned.
	*
	* @param string $key Meta field key.
	* @param int $term_id Term ID
	* @return array Meta field values.
	*/
	public static function get_term_custom_values( $key = '', $term_id )
	{
		if ( !$key )
			return null;

		$custom = self::get_term_custom($term_id);
		return isset( $custom[$key] ) ? $custom[$key] : null;
	}
} }

if ( ! function_exists( 'update_termmeta_cache' ) ) : function update_termmeta_cache( $term_ids ) { return gPluginTermMeta::update_termmeta_cache( $term_ids ); } endif;
if ( ! function_exists( 'add_term_meta' ) ) : function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) { return gPluginTermMeta::add_term_meta( $term_id, $meta_key, $meta_value, $unique ); } endif;
if ( ! function_exists( 'delete_term_meta' ) ) : function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) { return gPluginTermMeta::delete_term_meta( $term_id, $meta_key, $meta_value ); } endif;
if ( ! function_exists( 'get_term_meta' ) ) : function get_term_meta( $term_id, $key, $single = false ) { return gPluginTermMeta::get_term_meta( $term_id, $key, $single ); } endif;
if ( ! function_exists( 'update_term_meta' ) ) : function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) { return gPluginTermMeta::update_term_meta( $term_id, $meta_key, $meta_value, $prev_value ); } endif;
if ( ! function_exists( 'delete_term_meta_by_key' ) ) : function delete_term_meta_by_key( $term_meta_key ) { return gPluginTermMeta::delete_term_meta_by_key( $term_meta_key ); } endif;
if ( ! function_exists( 'get_term_custom' ) ) : function get_term_custom( $term_id ) { return gPluginTermMeta::get_term_custom( $term_id ); } endif;
if ( ! function_exists( 'get_term_custom_keys' ) ) : function get_term_custom_keys( $term_id ) { return gPluginTermMeta::get_term_custom_keys( $term_id ); } endif;
if ( ! function_exists( 'get_term_custom_values' ) ) : function get_term_custom_values( $key, $term_id ) { return gPluginTermMeta::get_term_custom_values( $key, $term_id ); } endif;
