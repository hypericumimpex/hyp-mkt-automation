<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

use AutomateWoo\Subscription_Workflow_Helper;

defined( 'ABSPATH' ) or exit;

/**
 * @class Subscription_Status
 */
class Subscription_Status extends Abstract_Select {

	public $data_item = 'subscription';


	function init() {
		$this->title = __( 'Subscription - Status', 'automatewoo' );
	}


	function load_select_choices() {
		return Subscription_Workflow_Helper::get_subscription_statuses();
	}


	/**
	 * @param $subscription \WC_Subscription
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $subscription, $compare, $value ) {
		return $this->validate_select( 'wc-' . $subscription->get_status(), $compare, $value );
	}

}

return new Subscription_Status();
