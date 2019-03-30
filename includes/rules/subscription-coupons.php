<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Subscription_Coupons rule class.
 *
 * @since   4.5.0
 * @package AutomateWoo\Rules
 */
class Subscription_Coupons extends \AW_Rule_Order_Coupons {

	/**
	 * Data item for the rule.
	 *
	 * @var string
	 */
	public $data_item = 'subscription';

	/**
	 * Init the rule.
	 */
	public function init() {
		$this->title = __( 'Subscription - Coupons', 'automatewoo' );
	}

}

return new Subscription_Coupons();
