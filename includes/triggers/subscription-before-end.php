<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Subscription_Before_End
 */
class Trigger_Subscription_Before_End extends Trigger_Subscription_Before_Renewal {


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Subscription Before End', 'automatewoo' );
		$this->description = __( 'This trigger checks for subscriptions that are due to expire/end once every 24 hours.', 'automatewoo' );
	}


	function load_fields() {

		$days_before = ( new Fields\Number() )
			->set_name( 'days_before' )
			->set_title( __( 'Days before end', 'automatewoo' ) )
			->set_required();

		$this->add_field( $days_before );
		$this->add_field( $this->get_field_time_of_day() );
		$this->add_field_subscription_products();
	}


	/**
	 * @param Workflow $workflow
	 * @return array
	 */
	function get_background_tasks( $workflow ) {
		$tasks = [];
		$days_before = absint( $workflow->get_trigger_option( 'days_before' ) );

		// days before field must be set
		if ( ! $days_before ) {
			return [];
		}

		$date = new DateTime();
		$date->convert_to_site_time();
		$date->modify( "+$days_before days" );

		foreach ( $this->get_subscriptions_by_end_day( $date ) as $subscription_id ) {
			$tasks[] = [
				'workflow_id' => $workflow->get_id(),
				'workflow_data' => [
					'subscription_id' => $subscription_id
				]
			];
		}

		return $tasks;
	}


	/**
	 * @param $date DateTime Must be in site time!
	 * @return array
	 */
	function get_subscriptions_by_end_day( $date ) {
		$day_start = clone $date;
		$day_end = clone $date;
		$day_start->setTime(0,0,0);
		$day_end->setTime(23,59,59);

		$day_start->convert_to_utc_time();
		$day_end->convert_to_utc_time();

		$query = new \WP_Query([
			'post_type' => 'shop_subscription',
			'post_status' => 'wc-active',
			'fields' => 'ids',
			'posts_per_page' => -1,
			'no_found_rows' => true,
			'meta_query' => [
				[
					'key' => '_schedule_end',
					'compare' => '>',
					'value' => $day_start->to_mysql_string(),
				],
				[
					'key' => '_schedule_end',
					'compare' => '<',
					'value' => $day_end->to_mysql_string(),
				]
			]
		]);

		return $query->posts;
	}

}
