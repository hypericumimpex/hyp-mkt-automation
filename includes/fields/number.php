<?php
// phpcs:ignoreFile

namespace AutomateWoo\Fields;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Number
 */
class Number extends Text {

	protected $name = 'number_input';

	protected $type = 'number';


	function __construct() {
		parent::__construct();
		$this->title = __( 'Number', 'automatewoo' );
	}


	/**
	 * @param $min string
	 * @return $this
	 */
	function set_min( $min ) {
		$this->add_extra_attr( 'min', $min );
		return $this;
	}


	/**
	 * @param $max string
	 * @return $this
	 */
	function set_max( $max ) {
		$this->add_extra_attr( 'max', $max );
		return $this;
	}

	/**
	 * Sanitizes the value of the field.
	 *
	 * Defaults to sanitize as a single line string. Override this method for fields that should be sanitized differently.
	 *
	 * @since 4.4.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	function sanitize_value( $value ) {
		return (float) $value;
	}

}
