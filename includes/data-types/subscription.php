<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Data_Type_Subscription
 */
class Data_Type_Subscription extends Data_Type {


	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		return is_a( $item, 'WC_Subscription' );
	}


	/**
	 * @param \WC_Subscription $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->get_id();
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return \WC_Subscription|false
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {
		if ( ! Integrations::is_subscriptions_active() || ! $compressed_item ) {
			return false;
		}

		return wcs_get_subscription( $compressed_item );
	}

}

return new Data_Type_Subscription();
