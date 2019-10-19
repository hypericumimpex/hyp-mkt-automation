<?php

namespace AutomateWoo;

/**
 * Class to manage admin notices.
 *
 * @since   4.7.0
 * @package AutomateWoo
 */
class Admin_Notices {

	/**
	 * Store of admin notices.
	 *
	 * @var array
	 */
	private static $notices;

	/**
	 * Whether notices have been changed and need to be saved.
	 *
	 * @var bool
	 */
	private static $has_changes = false;

	/**
	 * Define whether Admin_Notices::init() has run.
	 *
	 * @var bool
	 */
	private static $did_init = false;

	/**
	 * Init admin notices.
	 */
	public static function init() {
		if ( self::$did_init ) {
			return;
		}

		self::$did_init = true;
		add_action( 'shutdown', [ __CLASS__, 'save_notices' ] );

		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'wp_ajax_automatewoo_remove_notice', [ __CLASS__, 'handle_ajax_remove_notice' ] );
			add_action( 'admin_notices', [ __CLASS__, 'output_notices' ] );
		}
	}

	/**
	 * Get current admin notices.
	 *
	 * @return array
	 */
	public static function get_notices() {
		if ( ! isset( self::$notices ) ) {
			self::$notices = get_option( 'automatewoo_admin_notices', [] );
		}
		return self::$notices;
	}

	/**
	 * Add a notice.
	 *
	 * @param string $name
	 */
	public static function add_notice( $name ) {
		self::init();

		self::$notices     = array_unique( array_merge( self::get_notices(), [ $name ] ) );
		self::$has_changes = true;
	}

	/**
	 * Remove a notice.
	 *
	 * @param string $name
	 */
	public static function remove_notice( $name ) {
		self::init();

		self::$notices     = array_diff( self::get_notices(), [ $name ] );
		self::$has_changes = true;
	}

	/**
	 * Save notices to database.
	 */
	public static function save_notices() {
		if ( self::$has_changes ) {
			update_option( 'automatewoo_admin_notices', self::get_notices() );
		}
	}

	/**
	 * Output admin notices.
	 */
	public static function output_notices() {
		// Only show notices on AW screens
		if ( ! Admin::is_automatewoo_screen() ) {
			return;
		}

		$notices = self::get_notices();

		foreach ( $notices as $notice ) {
			$method_name = "output_{$notice}_notice";
			if ( is_callable( [ __CLASS__, $method_name ] ) ) {
				call_user_func( [ __CLASS__, $method_name ] );
			} else {
				do_action( "automatewoo/admin_notice/{$notice}" );
			}
		}
	}

	/**
	 * Remove notice by ajax request.
	 */
	public static function handle_ajax_remove_notice() {
		if ( ! wp_verify_nonce( sanitize_key( aw_get_post_var( 'nonce' ) ), 'aw-remove-notice' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'automatewoo' ) );
		}

		$notice = Clean::string( aw_get_post_var( 'notice' ) );
		if ( $notice ) {
			self::remove_notice( $notice );
		}
		die;
	}

	/**
	 * Output pretty AutomateWoo welcome notice.
	 */
	private static function output_welcome_notice() {
		Admin::get_view( 'welcome-notice' );
	}

}
