<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) or exit;

/**
 * @class AW_Rule_Order_Coupons
 */
class AW_Rule_Order_Coupons extends AutomateWoo\Rules\Abstract_Select {

	public $data_item = 'order';

	public $is_multi = true;


	function init() {
		$this->title = __( 'Order - Coupons', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function load_select_choices() {
		return AutomateWoo\Fields_Helper::get_coupons_list();
	}


	/**
	 * @param WC_Order $order
	 * @param string   $compare
	 * @param array    $expected_coupons
	 *
	 * @return bool
	 */
	function validate( $order, $compare, $expected_coupons ) {
		return $this->validate_select_case_insensitive( $order->get_used_coupons(), $compare, $expected_coupons );
	}


}

return new AW_Rule_Order_Coupons();
