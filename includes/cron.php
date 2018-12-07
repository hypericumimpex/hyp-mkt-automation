<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Cron manager
 * @class Cron
 */
class Cron {

	/** @var array : worker => schedule */
	static $workers = [
		'events' => 'automatewoo_one_minute',
		'two_minute' => 'automatewoo_two_minutes',
		'five_minute' => 'automatewoo_five_minutes',
		'fifteen_minute' => 'automatewoo_fifteen_minutes',
		'thirty_minute' => 'automatewoo_thirty_minutes',
		'hourly' => 'hourly',
		'four_hourly' => 'automatewoo_four_hours',
		'daily' => 'daily',
		'two_days' => 'automatewoo_two_days',
		'weekly' => 'automatewoo_weekly'
	];


	/**
	 * Init cron
	 */
	static function init() {

		add_filter( 'cron_schedules', [ __CLASS__, 'add_schedules' ], 100 );

		foreach ( self::$workers as $worker => $schedule ) {
			add_action( 'automatewoo_' . $worker . '_worker', [ __CLASS__, 'before_worker' ], 1 );
		}

		add_action( 'admin_init', [ __CLASS__, 'add_events' ] );

		add_action( 'automatewoo_five_minute_worker', [ __CLASS__, 'check_for_gmt_offset_change' ] );
		add_action( 'automatewoo/gmt_offset_changed', [ __CLASS__, 'update_midnight_cron_after_offset_change' ], 10, 2 );

		// set up midnight cron job, but doesn't repair it (which is important)
		add_action( 'admin_init', [ __CLASS__, 'setup_midnight_cron' ] );
	}


	/**
	 * Prevents workers from working if they have done so in the past 30 seconds
	 */
	static function before_worker() {

		$action = current_action();

		if ( self::is_worker_locked( $action ) ) {
			remove_all_actions( $action ); // prevent actions from running
			return;
		}

		@set_time_limit(300);

		self::update_last_run( $action );
	}


	/**
	 * @param $action
	 * @return \DateTime|bool
	 */
	static function get_last_run( $action ) {
		$last_runs = get_option('aw_workers_last_run');
		if ( is_array( $last_runs ) && isset( $last_runs[$action] ) ) {
			$date = new DateTime();
			$date->setTimestamp( $last_runs[$action] );
			return $date;
		}
		else {
			return false;
		}
	}


	/**
	 * @param $action
	 */
	static function update_last_run( $action ) {
		$last_runs = get_option('aw_workers_last_run');

		if ( ! $last_runs ) $last_runs = [];

		$last_runs[$action] = time();

		update_option( 'aw_workers_last_run', $last_runs, false );
	}


	/**
	 * @param $action
	 * @return int|false
	 */
	static function get_worker_interval( $action ) {
		$schedules = wp_get_schedules();
		$schedule = wp_get_schedule( $action );

		if ( isset( $schedules[$schedule] ) ) {
			return $schedules[$schedule]['interval'];
		}

		return false;
	}


	/**
	 * Checks if worker started running less than 30 seconds
	 *
	 * @param $action
	 * @return bool
	 */
	static function is_worker_locked( $action ) {
		if ( ! $time_last_run = self::get_last_run( $action ) ) {
			return false;
		}

		$time_unlocked = clone $time_last_run;
		$time_unlocked->modify( '+30 seconds' );

		if ( $time_unlocked->getTimestamp() > time() ) {
			return true;
		}

		return false;
	}


	/**
	 * Add cron workers
	 */
	static function add_events() {
		foreach ( self::$workers as $worker => $schedule ) {
			$hook = 'automatewoo_' . $worker . '_worker';

			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time(), $schedule, $hook );
			}
		}
	}


	/**
	 * @param $schedules
	 * @return mixed
	 */
	static function add_schedules( $schedules ) {

		$schedules['automatewoo_one_minute'] = [
			'interval' => 60,
			'display' => __( 'One minute', 'automatewoo' )
		];

		$schedules['automatewoo_two_minutes'] = [
			'interval' => 120,
			'display' => __( 'Two minutes', 'automatewoo' )
		];

		$schedules['automatewoo_five_minutes'] = [
			'interval' => 300,
			'display' => __( 'Five minutes', 'automatewoo' )
		];

		$schedules['automatewoo_fifteen_minutes'] = [
			'interval' => 900,
			'display' => __( 'Fifteen minutes', 'automatewoo' )
		];

		$schedules['automatewoo_thirty_minutes'] = [
			'interval' => 1800,
			'display' => __( 'Thirty minutes', 'automatewoo' )
		];

		$schedules['automatewoo_two_days'] = [
			'interval' => 172800,
			'display' => __( 'Two days', 'automatewoo' )
		];

		$schedules['automatewoo_four_hours'] = [
			'interval' => 14400,
			'display' => __( 'Four hours', 'automatewoo' )
		];

		$schedules['automatewoo_weekly'] = [
			'interval' => 604800,
			'display' => __('Once weekly', 'automatewoo' )
		];

		return $schedules;
	}


	/**
	 * Track changes in the GMT offset such as DST
	 *
	 * @since 3.8
	 */
	static function check_for_gmt_offset_change() {
		$new_offset = Time_Helper::get_timezone_offset();
		$existing_offset = get_option( 'automatewoo_gmt_offset' );

		if ( $existing_offset === false ) {
			update_option( 'automatewoo_gmt_offset', $new_offset, false );
			return;
		}

		if ( $existing_offset != $new_offset ) {
			do_action( 'automatewoo/gmt_offset_changed', $new_offset, $existing_offset );
			update_option( 'automatewoo_gmt_offset', $new_offset, false );
		}
	}


	/**
	 * This updates the timing of the midnight cron job based on the change in offset.
	 *
	 * @since 3.8
	 *
	 * @param int $new_offset
	 * @param int $existing_offset
	 */
	static function update_midnight_cron_after_offset_change( $new_offset, $existing_offset ) {
		if ( ! $next = wp_next_scheduled( 'automatewoo_midnight' ) ) {
			self::setup_midnight_cron(); // no cron set
			return;
		}

		$difference = $existing_offset - $new_offset;

		$date = new DateTime();
		$date->setTimestamp( $next );
		$date->modify( "$difference hours" );

		self::update_midnight_cron( $date );

		self::fix_midnight_cron(); // checks calculation and fixes it if needed
	}


	/**
	 * Set midnight cron, if not already set
	 * @since 3.8
	 */
	static function setup_midnight_cron() {
		if ( $next = wp_next_scheduled( 'automatewoo_midnight' ) ) {
			return false; // already setup
		}

		// calculate next midnight in the site's timezone
		$date = new DateTime( 'now' );
		$date->convert_to_site_time();
		$date->setTime( 0, 0, 0 );
		// actually trigger now instead of tomorrow, to avoid issues with custom time of day triggers
		// these triggers could skip 1 day if we don't run the midnight cron immediately when adding
		// TODO remove in the future
		//$date->modify('+1 day');
		$date->convert_to_utc_time(); // convert back to UTC

		self::update_midnight_cron( $date );
	}


	/**
	 * If the midnight cron job is not correct this method will reset it.
	 */
	static function fix_midnight_cron() {
		if ( self::is_midnight_cron_correct() ) {
			return; // already correct
		}

		// clear and setup again
		wp_clear_scheduled_hook( 'automatewoo_midnight' );
		self::setup_midnight_cron();
	}


	/**
	 * @return bool
	 */
	static function is_midnight_cron_correct() {
		if ( ! $next = wp_next_scheduled( 'automatewoo_midnight' ) ) {
			return false;
		}
		$date = new DateTime();
		$date->setTimestamp( $next );
		$date->convert_to_site_time();
		return $date->format('Hi') == '0000';
	}


	/**
	 * Sets a new time for the midnight cron.
	 *
	 * @param DateTime $date GMT
	 */
	static function update_midnight_cron( $date ) {
		wp_clear_scheduled_hook( 'automatewoo_midnight' );
		wp_schedule_event( $date->getTimestamp(), 'daily', 'automatewoo_midnight' );
	}

}
