<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
if ( ! class_exists( 'gPluginFileHelper' ) ) { class gPluginFileHelper
{

	/** ---------------------------------------------------------------------------------
						USED FUNCTIONS: Modyfy with Caution!
	--------------------------------------------------------------------------------- **/	
	
	public static function mime( $extension )
	{
		switch ( $extension ) {
			case 'xlsx' : return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			case 'csv' : return 'text/csv';
			case 'xml' : return 'text/xml';
		}
		return '';
	}	
	
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

	// TESTED : not working very well with UTF
	// http://www.scriptville.in/parse-csv-data-to-array/
	// Parse csv data to array
	// Here, we have function to parse csv file into array. This function return associative array of each line with all column name in array as key and csv data for each line as key values.
	public static function csvToArray( $file )
	{
		$rows = $headers = array();
		
		if ( file_exists( $file ) && is_readable( $file ) ) {
			
			$handle = fopen( $file, 'r' );
			
			while ( ! feof( $handle ) ) {
				
				$row = fgetcsv( $handle, 10240, ',', '"' );

				if ( empty( $headers ) )
					$headers = $row;
				else if ( is_array( $row ) ) {
					array_splice( $row, count( $headers ) );
					$rows[] = array_combine( $headers, $row );
				}
			}
			
			fclose( $handle );
		
		} else {
			
			throw new Exception( $file.' doesn`t exist or is not readable.' );
			
		}
		
		return $rows;
    }	
	
	// http://www.php.net/manual/en/function.copy.php#103732
	// 'cp -R' written in PHP.
	public static function copy( $path, $dest, $ds = DIRECTORY_SEPARATOR ) 
	{
		if( is_dir( $path ) ) {
			
			@ mkdir( $dest );
			$objects = scandir( $path );
			
			if( sizeof( $objects ) > 0 ) {
				
				foreach( $objects as $file ) {
					
					if( $file == "." || $file == ".." )
						continue;
					
					if( is_dir( $path.$ds.$file ) )
						self::copy( $path.$ds.$file, $dest.$ds.$file );
					else
						copy( $path.$ds.$file, $dest.$ds.$file );
				}
			}
			
			return true;
			
		} elseif( is_file( $path ) ) {
			
			return copy( $path, $dest );
			
		} 
		
		return false;
	}


	
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