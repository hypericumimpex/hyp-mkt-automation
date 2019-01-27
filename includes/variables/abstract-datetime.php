<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class Variable_Abstract_Datetime
 */
class Variable_Abstract_Datetime extends Variable {

	public $_desc_format_tip;

	function load_admin_details() {
		$this->_desc_format_tip = sprintf(
			__( "To modify how the date and time is formatted please refer to the %sWordPress documentation%s.", 'automatewoo' ),
			'<a href="https://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">', '</a>'
		);

		$this->add_parameter_text_field( 'format',
			__( "Optional parameter to modify the display of the datetime. Default is MySQL format (Y-m-d H:i:s)", 'automatewoo' ),
			false, Format::MYSQL );

		$this->add_parameter_text_field( 'modify',
			__( "Optional parameter to modify the value of the datetime. Uses the PHP strtotime() function.", 'automatewoo' ), false,
			__( "e.g. +2 months, -1 day, +6 hours", 'automatewoo' )
		);
	}


	/**
	 * Formats a datetime variable.
	 *
	 * Dates should be passed in the site's timezone.
	 * WC_DateTime objects will maintain their specified timezone.
	 *
	 * @param \WC_DateTime|DateTime|string $input
	 * @param array                        $parameters [modify, format]
	 * @param bool                         $is_gmt
	 *
	 * @return string|false
	 */
	function format_datetime( $input, $parameters, $is_gmt = false ) {
		if ( ! $input ) {
			return false;
		}

		if ( is_a( $input, 'DateTime' ) ) {
			$date = $input;
		} else {
			try {
				if ( is_numeric( $input ) ) {
					$date = new DateTime();
					$date->setTimestamp( $input );
				} else {
					$date = new DateTime( $input );
				}
			} catch ( \Exception $e ) {
				return false;
			}
		}

		if ( $is_gmt ) {
			$date->convert_to_site_time();
		}

		$format = ! empty( $parameters['format'] ) ? $parameters['format'] : Format::MYSQL;

		if ( ! empty( $parameters['modify'] ) ) {
			$date->modify( $parameters['modify'] );
		}

		return $date->format( $format );
	}
}
