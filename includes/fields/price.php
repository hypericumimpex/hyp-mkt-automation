<?php
// phpcs:ignoreFile

namespace AutomateWoo\Fields;

use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Price
 */
class Price extends Text {

	protected $name = 'price';

	protected $type = 'text';


	function __construct() {
		parent::__construct();

		$this->set_title( __( 'Price', 'automatewoo' ) );
		$this->classes[] = 'automatewoo-field--type-price';
	}

	/**
	 * Sanitizes the field value.
	 *
	 * Removes currency symbols, thousand separators and sets correct decimal places.
	 * Empty string values are deliberately allowed.
	 *
	 * @since 4.4.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	function sanitize_value( $value ) {
		$value = trim( $value );

		// preserve empty string values, don't convert to '0.00'
		if ( $value === '' ) {
			return '';
		}

		return Clean::price( $value );
	}

}
