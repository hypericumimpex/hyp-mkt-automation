<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Order_Meta
 */
class Variable_Order_Meta extends Variable_Abstract_Meta {


	function load_admin_details() {
		parent::load_admin_details();
		$this->description = __( "Displays the value of an order custom field.", 'automatewoo');
	}


	/**
	 * @param $order \WC_Order
	 * @param $parameters array
	 * @return string|bool
	 */
	function get_value( $order, $parameters ) {
		if ( $parameters['key'] ) {
			return $order->get_meta( $parameters['key'] );
		}
		return false;
	}
}

return new Variable_Order_Meta();
