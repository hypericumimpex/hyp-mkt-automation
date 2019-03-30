<?php

namespace AutomateWoo;

use WC_Points_Rewards_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Customer Points Class.
 *
 * @since 4.5.0
 *
 * @class Customer_Points
 */
class Customer_Points extends Variable {

	/**
	 * Load Admin Details
	 */
	function load_admin_details() {
		$this->description = __( "Displays the customer's total points.", 'automatewoo' );

		$this->add_parameter_select_field( 'format', __( 'Choose whether to display the total number of points or their monetary value.', 'automatewoo' ), [
			''        => __( 'Number of Points', 'automatewoo' ),
			'decimal' => __( 'Point value as decimal', 'automatewoo' ),
			'price'   => __( 'Point value as price', 'automatewoo' ),
		], false );
	}

	/**
	 * Get Value method.
	 *
	 * @param Customer $customer
	 * @param array    $parameters
	 *
	 * @return string
	 */
	function get_value( $customer, $parameters ) {

		if ( ! $customer->is_registered() ) {
			return false;
		}

		$format = isset( $parameters['format'] ) ? $parameters['format'] : 'total';

		$return = null;

		switch ( $format ) {
			case 'total':
				$return = WC_Points_Rewards_Manager::get_users_points( $customer->get_user_id() );
				break;
			case 'decimal':
				$return = WC_Points_Rewards_Manager::get_users_points_value( $customer->get_user_id() );
				break;
			case 'price':
				$raw    = WC_Points_Rewards_Manager::get_users_points_value( $customer->get_user_id() );
				$return = wc_price( $raw );
				break;
		}

		return $return;
	}
}

return new Customer_Points();
