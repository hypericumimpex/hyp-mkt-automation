<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

use AutomateWoo\Compat;

defined( 'ABSPATH' ) or exit;

/**
 * @class Subscription_Requires_Manual_Renewal
 */
class Subscription_Requires_Manual_Renewal extends Abstract_Bool {

	public $data_item = 'subscription';


	function init() {
		$this->title = __( 'Subscription - Requires Manual Renewal', 'automatewoo' );
		$this->group = __( 'Subscription', 'automatewoo' );
	}


	/**
	 * @param \WC_Subscription $subscription
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $subscription, $compare, $value ) {
		$manual = $subscription->get_requires_manual_renewal();
		return $value === 'yes' ? $manual : ! $manual;
	}

}

return new Subscription_Requires_Manual_Renewal();
