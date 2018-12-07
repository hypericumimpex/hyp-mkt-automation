<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

use AutomateWoo\Compat;
use AutomateWoo\Clean;

defined( 'ABSPATH' ) or exit;

/**
 * @class Order_Created_Via
 */
class Order_Created_Via extends Abstract_Select {

	public $data_item = 'order';


	function init() {
		$this->title = __( 'Order - Created Via', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function load_select_choices() {
		return [
			'checkout' => __( 'Checkout', 'automatewoo' ),
			'rest-api' => __( 'REST API', 'automatewoo' ),
		];
	}


	/**
	 * @param $order \WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_select( Clean::string( Compat\Order::get_created_via( $order ) ), $compare, $value );
	}

}

return new Order_Created_Via();
