<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginUtils' ) ) { class gPluginUtils extends gPluginClassCore
{

	public static function IP()
	{
		if ( getenv( 'HTTP_CLIENT_IP' ) )
			return getenv( 'HTTP_CLIENT_IP' );

		if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
			return getenv( 'HTTP_X_FORWARDED_FOR' );

		if ( getenv( 'HTTP_X_FORWARDED' ) )
			return getenv( 'HTTP_X_FORWARDED' );

		if ( getenv( 'HTTP_FORWARDED_FOR' ) )
			return getenv( 'HTTP_FORWARDED_FOR' );

		if ( getenv( 'HTTP_FORWARDED' ) )
			return getenv( 'HTTP_FORWARDED' );

		return $_SERVER['REMOTE_ADDR'];
	}

	// FIXME: DEPRECATED
	public static function getIP()
	{
		self::__dep( 'gPluginUtils::IP()' );
		return self::IP();
	}

	public static function roundArray( $array, $precision = -3, $mode = PHP_ROUND_HALF_UP )
	{
		$new = array();

		foreach( (array) $array as $key => $value )
			$new[$key] = round( (float) $value, $precision, $mode );

		return $new;
	}

	public static function dump( & $var, $htmlSafe = TRUE )
	{
		$result = var_export( $var, TRUE );
		echo '<pre dir="ltr" style="text-align:left;direction:ltr;">'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	}

	public static function dump_get( & $var, $htmlSafe = TRUE )
	{
		$result = var_export( $var, TRUE );
		return '<pre dir="ltr" style="text-align:left;direction:ltr;">'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	}


	public static function dump_n( $var, $htmlSafe = TRUE )
	{
		$result = var_export( $var, TRUE );
		echo '<pre dir="ltr" style="text-align:left;direction:ltr;">'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	}

	// returns array of the keys if options values are TRUE
	public static function getKeys( $options = array() )
	{
		$keys = array();

		foreach ( (array) $options as $support => $enabled )
			if ( $enabled )
				$keys[] = $support;

		return $keys;
	}

	// http://wordpress.mfields.org/2011/rekey-an-indexed-array-of-post-objects-by-post-id/
	public static function reKey( $list, $key )
	{
		if ( ! empty( $list ) ) {
			$ids = wp_list_pluck( $list, $key );
			$list = array_combine( $ids, $list );
		}

		return $list;
	}

	public static function sameKey( $old )
	{
		$new = array();

		foreach ( $old as $key => $value )
			$new[$value] = $value;

		return $new;
	}

	// for useing with $('form').serializeArray();
	// http://api.jquery.com/serializeArray/
	public static function parseJSArray( $array )
	{
		$parsed = array();
		foreach ( $array as $part )
			$parsed[$part['name']] = $part['value'];
		return $parsed;
	}

	public static function strpos_arr( $haystack, $needle )
	{
		if ( ! is_array( $needle ) )
			$needle = array( $needle );

		foreach ( $needle as $what )
			if ( FALSE !== ( $pos = strpos( $haystack, $what ) ) )
				return $pos;

		return FALSE;
	}

	// http://teleogistic.net/2013/05/a-recursive-sorta-version-of-wp_parse_args/
	// https://gist.github.com/boonebgorges/5510970
	/**
	* Recursive argument parsing
	*
	* This acts like a multi-dimensional version of wp_parse_args() (minus
	* the querystring parsing - you must pass arrays).
	*
	* Values from $a override those from $b; keys in $b that don't exist
	* in $a are passed through.
	*
	* This is different from array_merge_recursive(), both because of the
	* order of preference ($a overrides $b) and because of the fact that
	* array_merge_recursive() combines arrays deep in the tree, rather
	* than overwriting the b array with the a array.
	*
	* The implementation of this function is specific to the needs of
	* BP_Group_Extension, where we know that arrays will always be
	* associative, and that an argument under a given key in one array
	* will be matched by a value of identical depth in the other one. The
	* function is NOT designed for general use, and will probably result
	* in unexpected results when used with data in the wild. See, eg,
	* http://core.trac.wordpress.org/ticket/19888
	*
	* @since BuddyPress (1.8)
	* @arg array $a
	* @arg array $b
	* @return array
	*/
	public static function parse_args_r( &$a, $b ) {
		$a = (array) $a;
		$b = (array) $b;
		$r = $b;

		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && isset( $r[ $k ] ) ) {
				$r[ $k ] = self::parse_args_r( $v, $r[ $k ] );
			} else {
				$r[ $k ] = $v;
			}
		}

		return $r;
	}

	public static function parse_args( $args, $defaults = '' )
	{
		if ( is_object( $args ) )
			$r = get_object_vars( $args );
		else if ( is_array( $args ) )
			$r =& $args;
		else
			self::parse_str( $args, $r );

		if ( is_array( $defaults ) )
			return array_merge( $defaults, $r );
		return $r;
	}

	public static function parse_str( $string, & $array )
	{
		parse_str( $string, $array );
		if ( get_magic_quotes_gpc() )
			$array = stripslashes_deep( $array );
	}

	public static function unslash( $value )
	{
		return self::stripslashes_deep( $value );
	}

	public static function stripslashes_deep( $value )
	{
		if ( is_array( $value ) ) {
			$value = array_map( array( __CLASS__, 'stripslashes_deep' ), $value );
		} else if ( is_object( $value ) ) {
			$vars = get_object_vars( $value );
			foreach ( $vars as $key => $data ) {
				$value->{$key} = self::stripslashes_deep( $data );
			}
		} else if ( is_string( $value ) ) {
			$value = stripslashes( $value );
		}

		return $value;
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// http://php.net/manual/en/function.array-key-exists.php#77848
	// example : if (array_key_exists_r('login|user|passwd',$_GET)) {
	public static function array_key_exists_r( $keys, $search_r )
	{
		$keys_r = split( '\|',$keys );
		foreach ( $keys_r as $key )
			if ( ! array_key_exists($key,$search_r) )
				return FALSE;
		return TRUE;
	}

	// http://stackoverflow.com/a/17620260
	public static function search_array( $value, $key, $array )
	{
		foreach ( $array as $k => $val )
			if ( $val[$key] == $value )
				return $k;
		return NULL;
	}

	// http://stackoverflow.com/a/15031805
	/**
	 * Search Revisions
	 * @param string $search_value The value to search for, ie a specific 'Taylor'
	 * @param string $key_to_search The associative key to find it in, ie first_name
	 * @param string $other_matching_key The associative key to find in the matches for employed
	 * @param string $other_matching_value The value to find in that matching associative key, ie TRUE
	 *
	 * @return array keys, ie all the people with the first name 'Taylor' that are employed.
	 */
	public static function search_revisions( $revisions, $search_value, $key_to_search, $other_matching_value = NULL, $other_matching_key = NULL )
	{
		// This function will search the revisions for a certain value
		// related to the associative key you are looking for.
		$keys = array();
		foreach ( $revisions as $key => $cur_value ) {
			if ($cur_value[$key_to_search] === $search_value) {
				if (isset($other_matching_key) && isset($other_matching_value)) {
					if ($cur_value[$other_matching_key] === $other_matching_value) {
						$keys[] = $key;
					}
				} else {
					// I must keep in mind that some searches may have multiple
					// matches and others would not, so leave it open with no continues.
					$keys[] = $key;
				}
			}
		}
		return $keys;
	}

	// http://nl1.php.net/manual/en/function.array-multisort.php#100534
	// I came up with an easy way to sort database-style results. This does what example 3 does, except it takes care of creating those intermediate arrays for you before passing control on to array_multisort().
	// Pass the array, followed by the column names and sort flags
	// $sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
	public static function array_orderby()
	{
		$args = func_get_args();
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row)
					$tmp[$key] = $row[$field];
				$args[$n] = $tmp;
				}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
	}


	// http://davidwalsh.name/data-uri-php
	// http://www.catswhocode.com/blog/snippets/image-data-uris-with-php
	public static function getDataURI( $image, $mime = '' )
	{
		return 'data: '.( function_exists( 'mime_content_type' ) ? mime_content_type( $image ) : $mime ).';base64,'.base64_encode( file_get_contents( $image ) );
	}

	// recursively sort multidimentional arrays by key
	// http://www.php.net/manual/en/function.ksort.php#105399
	public static function deep_ksort( & $arr )
	{
		ksort( $arr );
		foreach ( $arr as &$a )
			if ( is_array($a) && ! empty( $a ) )
				self::deep_ksort( $a );
	}

	// Sort multidimensional Array by Value
	// http://stackoverflow.com/questions/2699086/sort-multidimensional-array-by-value-2
	// http://en.wikipedia.org/wiki/Sorting_algorithm
	// aasort( $your_array, "order" );
	public static function aasort( & $array, $key )
	{
		$sorter = $ret = array();
		reset( $array );

		foreach ( $array as $ii => $va )
			$sorter[$ii] = $va[$key];

		asort( $sorter );
		foreach ( $sorter as $ii => $va )
			$ret[$ii] = $array[$ii];

		$array = $ret;
	}

	// If you need to prepend something to the array without the keys being reindexed and/or need to prepend a key value pair
	// http://www.php.net/manual/en/function.array-unshift.php#14358
	// CAUTION : not on big arrays
	public static function array_unshift_assoc( &$arr, $key, $val )
	{
		$arr = array_reverse( $arr, TRUE );
		$arr[$key] = $val;
		$arr = array_reverse( $arr, TRUE );
		return count( $arr );
	}
} }
