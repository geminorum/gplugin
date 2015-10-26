<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginHashed' ) ) { class gPluginHashed extends gPluginClassCore
{

	// http://stackoverflow.com/a/6564310
	// http://stackoverflow.com/a/6564274
	public static function uniqid()
	{
		return md5( uniqid( microtime().rand(), TRUE ) );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------



	/*

	http://www.fileformat.info/tool/hash.htm

	http://wordpress.org/plugins/wp-hashed-ids/
	http://www.hashids.org/php/

	http://stackoverflow.com/a/2237247

	http://kvz.io/blog/2009/06/10/create-short-ids-with-php-like-youtube-or-tinyurl/
	http://www.codinghorror.com/blog/2007/08/url-shortening-hashes-in-practice.html

	http://blog.kevburnsjr.com/php-unique-hash

	*/

	//http://programanddesign.com/php/base62-encode/
	// If you have large integers and you want to shrink them down in size for whatever reason, you can use this code. Should be easy enough to extend if you want even higher bases (just add a few more chars and increase the base).

	/**
	 * Converts a base 10 number to any other base.
	 *
	 * @param int $val   Decimal number
	 * @param int $base  Base to convert to. If null, will use strlen($chars) as base.
	 * @param string $chars Characters used in base, arranged lowest to highest. Must be at least $base characters long.
	 *
	 * @return string    Number converted to specified base
	 */
	public static function base_encode( $val, $base = 62, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' )
	{
		if ( ! isset( $base ) )
			$base = strlen( $chars );
		$str = '';

		do {
			$m = bcmod( $val, $base );
			$str = $chars[$m].$str;
			$val = bcdiv( bcsub( $val, $m ), $base );
		} while ( bccomp( $val,0 ) > 0 );

		return $str;
	}

	/**
	* Convert a number from any base to base 10
	*
	* @param string $str   Number
	* @param int $base  Base of number. If null, will use strlen($chars) as base.
	* @param string $chars Characters use in base, arranged lowest to highest. Must be at least $base characters long.
	*
	* @return int    Number converted to base 10
	*/
	public static function base_decode( $str, $base = 62, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' )
	{
		if ( ! isset( $base ) )
			$base = strlen( $chars );
		$len = strlen( $str );
		$val = 0;
		$arr = array_flip( str_split( $chars ) );

		for( $i = 0; $i < $len; ++$i )
			$val = bcadd( $val, bcmul( $arr[$str[$i]], bcpow( $base, $len-$i - 1 ) ) );

		return $val;
	}



	// http://kvz.io/blog/2009/06/10/create-short-ids-with-php-like-youtube-or-tinyurl/
	// alphaID(9007199254740989); -> PpQXn7COf
	// alphaID('PpQXn7COf', true); -> 9007199254740989

	/**
	 * Translates a number to a short alhanumeric version
	 *
	 * Translated any number up to 9007199254740992
	 * to a shorter version in letters e.g.:
	 * 9007199254740989 --> PpQXn7COf
	 *
	 * specifiying the second argument true, it will
	 * translate back e.g.:
	 * PpQXn7COf --> 9007199254740989
	 *
	 * this function is based on any2dec && dec2any by
	 * fragmer[at]mail[dot]ru
	 * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
	 *
	 * If you want the alphaID to be at least 3 letter long, use the
	 * $pad_up = 3 argument
	 *
	 * In most cases this is better than totally random ID generators
	 * because this can easily avoid duplicate ID's.
	 * For example if you correlate the alpha ID to an auto incrementing ID
	 * in your database, you're done.
	 *
	 * The reverse is done because it makes it slightly more cryptic,
	 * but it also makes it easier to spread lots of IDs in different
	 * directories on your filesystem. Example:
	 * $part1 = substr($alpha_id,0,1);
	 * $part2 = substr($alpha_id,1,1);
	 * $part3 = substr($alpha_id,2,strlen($alpha_id));
	 * $destindir = "/".$part1."/".$part2."/".$part3;
	 * // by reversing, directories are more evenly spread out. The
	 * // first 26 directories already occupy 26 main levels
	 *
	 * more info on limitation:
	 * - http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/165372
	 *
	 * if you really need this for bigger numbers you probably have to look
	 * at things like: http://theserverpages.com/php/manual/en/ref.bc.php
	 * or: http://theserverpages.com/php/manual/en/ref.gmp.php
	 * but I haven't really dugg into this. If you have more info on those
	 * matters feel free to leave a comment.
	 *
	 * The following code block can be utilized by PEAR's Testing_DocTest
	 * <code>
	 * // Input //
	 * $number_in = 2188847690240;
	 * $alpha_in  = "SpQXn7Cb";
	 *
	 * // Execute //
	 * $alpha_out  = alphaID($number_in, false, 8);
	 * $number_out = alphaID($alpha_in, true, 8);
	 *
	 * if ($number_in != $number_out) {
	 *	 echo "Conversion failure, ".$alpha_in." returns ".$number_out." instead of the ";
	 *	 echo "desired: ".$number_in."\n";
	 * }
	 * if ($alpha_in != $alpha_out) {
	 *	 echo "Conversion failure, ".$number_in." returns ".$alpha_out." instead of the ";
	 *	 echo "desired: ".$alpha_in."\n";
	 * }
	 *
	 * // Show //
	 * echo $number_out." => ".$alpha_out."\n";
	 * echo $alpha_in." => ".$number_out."\n";
	 * echo alphaID(238328, false)." => ".alphaID(alphaID(238328, false), true)."\n";
	 *
	 * // expects:
	 * // 2188847690240 => SpQXn7Cb
	 * // SpQXn7Cb => 2188847690240
	 * // aaab => 238328
	 *
	 * </code>
	 *
	 * @author	Kevin van Zonneveld <kevin@vanzonneveld.net>
	 * @author	Simon Franz
	 * @author	Deadfish
	 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	 * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
	 * @link	  http://kevin.vanzonneveld.net/
	 *
	 * @param mixed   $in	  String or long input to translate
	 * @param boolean $to_num  Reverses translation when true
	 * @param mixed   $pad_up  Number or boolean padds the result up to a specified length
	 * @param string  $passKey Supplying a password makes it harder to calculate the original ID
	 *
	 * @return mixed string or long
	 */
	public static function alphaID( $in, $to_num = false, $pad_up = false, $passKey = null )
	{
		$index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($passKey !== null) {
			// Although this function's purpose is to just make the
			// ID short - and not so much secure,
			// with this patch by Simon Franz (http://blog.snaky.org/)
			// you can optionally supply a password to make it harder
			// to calculate the corresponding numeric ID

			for ($n = 0; $n<strlen($index); $n++) {
				$i[] = substr( $index,$n ,1);
			}

			$passhash = hash('sha256',$passKey);
			$passhash = (strlen($passhash) < strlen($index))
				? hash('sha512',$passKey)
				: $passhash;

			for ($n=0; $n < strlen($index); $n++) {
				$p[] =  substr($passhash, $n ,1);
			}

			array_multisort($p,  SORT_DESC, $i);
			$index = implode($i);
		}

		$base  = strlen($index);

		if ($to_num) {
			// Digital number  <<--  alphabet letter code
			$in  = strrev($in);
			$out = 0;
			$len = strlen($in) - 1;
			for ($t = 0; $t <= $len; $t++) {
				$bcpow = bcpow($base, $len - $t);
				$out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
			}

			if (is_numeric($pad_up)) {
				$pad_up--;
				if ($pad_up > 0) {
					$out -= pow($base, $pad_up);
				}
			}
			$out = sprintf('%F', $out);
			$out = substr($out, 0, strpos($out, '.'));
		} else {
			// Digital number  -->>  alphabet letter code
			if (is_numeric($pad_up)) {
				$pad_up--;
				if ($pad_up > 0) {
					$in += pow($base, $pad_up);
				}
			}

			$out = "";
			for ($t = floor(log($in, $base)); $t >= 0; $t--) {
				$bcp = bcpow($base, $t);
				$a   = floor($in / $bcp) % $base;
				$out = $out . substr($index, $a, 1);
				$in  = $in - ($a * $bcp);
			}
			$out = strrev($out); // reverse
		}

		return $out;
	}



} }


// http://www.sitepoint.com/php-random-number-generator/
/*
gPluginHashedRandom::seed(42); // set seed

// echo 10 numbers between 1 and 100
for ($i = 0; $i < 10; $i++) {
	echo gPluginHashedRandom::num(1, 100) . '<br />';
}
*/
if ( ! class_exists( 'gPluginHashedRandom' ) ) { class gPluginHashedRandom
{
	private static $RSeed = 0; // random seed
	 // set seed
	public static function seed( $s = 0 )
	{
		self::$RSeed = abs( intval( $s ) ) % 9999999 + 1;
		self::num();
	}

	// generate random number
	public static function num( $min = 0, $max = 9999999 )
	{
		if ( self::$RSeed == 0 )
			self::seed( mt_rand() );
		self::$RSeed = ( self::$RSeed * 125 ) % 2796203;
		return self::$RSeed % ($max - $min + 1) + $min;
	}
} }
