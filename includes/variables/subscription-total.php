<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Subscription_Total
 */
class Variable_Subscription_Total extends Variable {


	function load_admin_details() {
		$this->description = __( "Displays the subscription's recurring total.", 'automatewoo');
	}


	/**
	 * @param \WC_Subscription $subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters ) {
		return wc_price( $subscription->get_total(), [
			'currency' => $subscription->get_currency()
		] );
	}
}

return new Variable_Subscription_Total();
