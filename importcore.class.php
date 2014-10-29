<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
if ( ! class_exists( 'gPluginImportCore' ) ) { class gPluginImportCore extends gPluginClassCore
{
	//public function setup_globals( $constants = array(), $args = array() ) { parent::setup_globals(); }
	function setup_actions() {}
	
	// helper
	function implode( $string, $l10n = false, $glue = '،' )
	{
		$results = explode( ',', str_ireplace( array( '،', '-', ',' ), ',', $string ) );
		if ( $l10n )
			$results = array_map( array( $this, 'l10n' ), $results );
		return implode( $glue, $results );
	}
	
	// http://ask.libreoffice.org/en/question/7036/how-to-convert-every-hyphendash-between-numbers-to-en-dash/
	// https://help.libreoffice.org/Common/List_of_Regular_Expressions
	
	// 
	
	// helper
	function explode( $string, $l10n = false, $vav = false )
	{
		$dels = array( '،', '-', ',' );
		if ( $vav )
			$dels[] = ' و ';
			
		$results = explode( ',', str_ireplace( $dels, ',', $string ) );
		if ( ! $l10n )
			return $results;
		return array_map( array( $this, 'l10n' ), $results );
	}
	
	// helper
	function l10n( $string, $strip = false )
	{
		return gPluginPersianHelper::l10n( trim( $string ), $strip );
	}
	
	// helper
	function cleanup( $string )
	{
		return gPluginPersianHelper::cleanup( trim( $string ) );
	}
	
	//helper
	function str( $string )
	{
		$string = str_ireplace( array( "\n", "\t" ), ' ', $string );
		
		return $string;
	}
	
	
	
	// http://wp-wizzard.com/tutorials/get-a-list-of-attachments-by-mime-type-from-the-wordpress-media-library
	function select_attachment( $name = 'attach_id', $selected = false, $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' )
	{
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_mime_type' => $mime,
			'post_parent' => null,
		); 
		$attachments = get_posts( $args );
		
		if ( $attachments ) {
			echo '<select name="'.$name.'">';
			foreach ($attachments as $post) {
				setup_postdata( $post ); 
				echo '<option value="'.$post->ID.'"'.( $selected == $post->ID ? ' selected="selected"' : '' ).'>'.$post->post_title.'</option>';
			}
			echo '</select>';
		}
	}

	// exact copy of wp core with diffrent output
	function media_sideload_image( $file, $post_id, $desc = null ) 
	{
		// fix file filename for query strings
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
		$file_array['name'] = basename( $matches[0] );
		// Download file to temp location
		$file_array['tmp_name'] = download_url( $file );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) )
			return $file_array['tmp_name'];

		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		// If error storing permanently, unlink
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}
		
		return $id;
	}	
	
	
	function unpublish_post( $post_id )
	{
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), array( 'ID' => $post_id ) );
		clean_post_cache( $post_id );
		return true;
	}
	
	// DRAFT
	function delete_attachments( $post_id )
	{
		global $wpdb;

		$sql = "SELECT ID FROM {$wpdb->posts} ";
		$sql .= " WHERE post_parent = $post_id ";
		$sql .= " AND post_type = 'attachment'";

		$ids = $wpdb->get_col($sql);

		foreach ( $ids as $id )
			wp_delete_attachment($id);
	}
		
}
}