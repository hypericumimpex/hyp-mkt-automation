<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Rule_Subscription_Payment_Count
 */
class AW_Rule_Subscription_Payment_Count extends AutomateWoo\Rules\Abstract_Number {

	public $data_item = 'subscription';

	public $support_floats = false;


	function init() {
		$this->title = __( 'Subscription - Payment Count', 'automatewoo' );
	}


	/**
	 * @param $subscription WC_Subscription
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $subscription, $compare, $value ) {
		// Method changed in WCS 2.6
		$payment_count = is_callable( [ $subscription, 'get_payment_count' ] ) ? $subscription->get_payment_count() : $subscription->get_completed_payment_count();

		return $this->validate_number( $payment_count, $compare, $value );
	}

}
