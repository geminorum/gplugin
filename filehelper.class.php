<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
if ( ! class_exists( 'gPluginFileHelper' ) ) { class gPluginFileHelper
{

	/** ---------------------------------------------------------------------------------
						USED FUNCTION: Modyfy with Caution!
	--------------------------------------------------------------------------------- **/

	public static function require_array( $array, $base_path, $suffix = '.class.php', $folder = 'includes' )
	{
		foreach ( $array as $file )
			if ( file_exists( $base_path.$folder.DIRECTORY_SEPARATOR.$file.$suffix ) ) 
				require_once( $base_path.$folder.DIRECTORY_SEPARATOR.$file.$suffix );
	}
	
	// FROM: edd
	// Checks if the string (filename) provided is an image URL
	public static function is_image_url( $string ) 
	{
		$ext = self::extension( $string );
		switch ( strtolower( $ext ) ) {
			case 'jpg': return true;
			case 'png': return true;
			case 'gif': return true;
		}
		return false;
	}
	
	// FROM : edd
	// Returns the file extension of a filename.
	public static function extension( $string ) 
	{
		$parts = explode( '.', $string );
		return end( $parts );
	}
	
	
	
	
	
	/** ---------------------------------------------------------------------------------
									NOT USED YET
	--------------------------------------------------------------------------------- **/	
	
	
	// http://www.paulund.co.uk/html5-download-attribute
	function download( $file_path, $mime = 'application/octet-stream' )
	{
		if ( file_exists( $file_path ) ) {
			header( 'Content-Description: File Transfer' );
			header( 'Pragma: public' ); // required
			header( 'Expires: 0' );	// no cache
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: private', false );
			header( 'Content-Type: '.$mime );
			header( 'Content-Length: '.filesize( $file_path ) );
			header( 'Content-Disposition: attachment; filename="'.basename( $file_path ).'"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: close' );
			
			ob_clean();
            flush();
			
			readfile( $file_path );
			
			exit();
		}
	}	
	
	
	
	
	
	/**
	// https://gist.github.com/chrisguitarguy/6096271
	// Remove BOM from a string
	function remove_bom()
	{
		$file = new \SplFileObject('some_file_with_bom.csv'); 

		// http://en.wikipedia.org/wiki/Byte_order_mark#UTF-8
		$bom = pack('CCC', 0xEF, 0xBB, 0xBF);

		$first = true;
		foreach ($file as $line) {
			if ($first && substr($line, 0, 3) === $bom) {
				$line = substr($line, 3);
			}
			
			$first = false;
			
			// your lines don't have a BOM, do you shit
		}	
	}
	**/
	
} }


// http://christopherdavis.me/blog/extracting-gzip-files.html