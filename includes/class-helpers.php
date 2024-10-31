<?php

namespace Almiro\Wordpress\Nextcellent\Converter;

/**
 * Helper class containing various helper functions to convert the things.
 */
class Helpers {

	/**
	 * Unserialize strings based on the NextGEN format.
	 *
	 * @param string $value The serialized value.
	 *
	 * @return mixed The output.
	 */
	public static function unserialize( $value ) {
		$retval = null;
		if ( is_string( $value ) ) {
			$retval = stripcslashes( $value );

			if ( strlen( $value ) > 1 ) {
				// We can't always rely on base64_decode() or json_decode() to return FALSE as their documentation
				// claims so check if $retval begins with a: as that indicates we have a serialized PHP object.
				if ( strpos( $retval, 'a:' ) === 0 ) {
					$er     = error_reporting( 0 );
					$retval = unserialize( $value );
					error_reporting( $er );
				} else {
					// We use json_decode() here because PHP's unserialize() is not Unicode safe.
					$retval = json_decode( base64_decode( $retval ), true );
				}
			}
		}

		return $retval;
	}
}