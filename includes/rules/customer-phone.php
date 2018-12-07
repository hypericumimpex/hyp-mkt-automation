<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) or exit;

/**
 * @class Customer_Phone
 */
class Customer_Phone extends Abstract_String {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer - Phone', 'automatewoo' );
		$this->group = __( 'Customer', 'automatewoo' );
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return $this->validate_string( $this->data_layer()->get_customer_phone(), $compare, $value );
	}

}

return new Customer_Phone();
