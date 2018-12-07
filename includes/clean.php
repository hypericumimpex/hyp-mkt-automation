<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Sanitizer
 * @class Clean
 * @since 2.9
 */
class Clean {

	/**
	 * @param $string
	 * @return string
	 */
	static function string( $string ) {
		return sanitize_text_field( $string );
	}


	/**
	 * @param $email
	 * @return string
	 */
	static function email( $email ) {
		return strtolower( sanitize_email( $email ) );
	}


	/**
	 * Sanitize a multi-line string. Will strip HTML tags.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	static function textarea( $text ) {
		return implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $text ) ) );
	}


	/**
	 * Sanitizes and formats a price value.
	 * Removes currency symbols and thousand separators.
	 *
	 * @since 4.4
	 *
	 * @param string|float $price
	 *
	 * @return string
	 */
	static function price( $price ) {
		if ( ! is_float( $price ) ) {
			$price = str_replace( wc_get_price_thousand_separator(), '', $price );
		}
		return wc_format_decimal( $price, wc_get_price_decimals() );
	}


	/**
	 * @param array $var
	 * @return array
	 */
	static function ids( $var ) {
		if ( is_array( $var ) ) {
			return array_filter( array_map( 'absint', $var ) );
		}
		elseif ( is_numeric( $var ) ) {
			return [ absint( $var ) ];
		}
		return [];
	}


	/**
	 * @param string|int $id
	 * @return int
	 */
	static function id( $id ) {
		return absint( $id );
	}


	/**
	 * @param $var
	 * @return array|string
	 */
	static function recursive( $var ) {
		if ( is_array( $var ) ) {
			return array_map( [ 'AutomateWoo\Clean', 'recursive' ], $var );
		}
		else {
			return is_scalar( $var ) ? self::string( $var ) : $var;
		}
	}


	/**
	 * @param string|array $values
	 * @return array
	 */
	static function multi_select_values( $values ) {

		// pre WC 3.0 multi selects were saved as comma delimited strings
		if ( is_string( $values ) ) {
			$values = explode( ',', $values );
		}

		if ( $values ) {
			return Clean::recursive( $values );
		}
		else {
			return [];
		}
	}


	/**
	 * Convert comma delimited string to array.
	 *
	 * @param string $list
	 * @return array
	 */
	static function comma_delimited_string( $list ) {
		$list = explode(',', Clean::string( $list ) );
		return array_filter( array_map( 'trim', $list ) );
	}


	/**
	 * Performs a basic sanitize for AW email content permitting all HTML.
	 *
	 * Can contain unprocessed variables {{}}.
	 *
	 * @since 4.3
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	static function email_content( $content ) {
		$content = wp_check_invalid_utf8( stripslashes( $content ) );
		return $content;
	}


	/**
	 * HTML encodes emoji's in string or array.
	 *
	 * @since 4.3
	 *
	 * @param string|array $data
	 *
	 * @return string|array
	 */
	static function encode_emoji( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as &$field ) {
				if ( is_array( $field ) || is_string( $field ) ) {
					$field = self::encode_emoji( $field );
				}
			}
		}
		elseif ( is_string( $data ) ) {
			$data = wp_encode_emoji( $data );
		}
		return $data;
	}
	
}
