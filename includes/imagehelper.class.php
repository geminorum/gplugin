<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginImageHelper' ) ) { class gPluginImageHelper extends gPluginClassCore
{

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// ALSO SEE : http://css-tricks.com/data-uris/
	public static function toBase64( $path, $filetype )
	{
		return 'data:image/'.$filetype.';base64,'.base64_encode( file_get_contents( $path ) );
	}

	// ONLY JPEG!
	// http://snipplr.com/view/27513/
	public static function fromBase64( $data, $path )
	{
		$img = imagecreatefromstring( base64_decode( $data ) );
		if ( $img != FALSE )
		imagejpeg( $img, $path );
	}
} }
