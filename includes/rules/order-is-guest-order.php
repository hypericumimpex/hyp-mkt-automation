<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) or exit;

/**
 * @class AW_Rule_Is_Guest_Order
 */
class AW_Rule_Is_Guest_Order extends AutomateWoo\Rules\Abstract_Bool {

	public $data_item = 'order';

	function init() {
		$this->title = __( "Order - Is Placed By Guest", 'automatewoo' );
	}

	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {

		$is_guest = $order->get_user_id() === 0;

		switch ( $value ) {
			case 'yes':
				return $is_guest;
				break;

			case 'no':
				return ! $is_guest;
				break;
		}
	}
}

return new AW_Rule_Is_Guest_Order();
