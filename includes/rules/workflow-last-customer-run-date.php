<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) or exit;

/**
 * Workflow last customer run date rule.
 *
 * @class Workflow_Last_Customer_Run_Date
 */
class Workflow_Last_Customer_Run_Date extends Abstract_Date {

	/**
	 * What data we're using to validate.
	 *
	 * @var string
	 */
	public $data_item = 'customer';

	/**
	 * Workflow_Last_Customer_Run_Date constructor.
	 */
	public function __construct() {
		$this->has_is_past_comparision = true;

		parent::__construct();
	}

	/**
	 * Init.
	 */
	function init() {
		$this->title = __( 'Workflow - Last Run Date For Customer', 'automatewoo' );
		$this->group = __( 'Workflow', 'automatewoo' );
	}

	/**
	 * Validates rule.
	 *
	 * @param \AutomateWoo\Customer $customer The customer to validate.
	 * @param string                $compare  The type of comparison.
	 * @param mixed                 $value    The values we have to compare. Null is allowed when $compare is is_not_set.
	 *
	 * @return bool
	 */
	function validate( $customer, $compare, $value = null ) {
		$workflow = $this->get_workflow();

		if ( ! $workflow ) {
			return false;
		}

		return $this->validate_date( $compare, $value, $customer->get_workflow_last_run_date( $workflow ) );
	}

}

return new Workflow_Last_Customer_Run_Date();
