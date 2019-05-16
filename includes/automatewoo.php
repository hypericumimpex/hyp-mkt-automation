<?php

defined( 'ABSPATH' ) || exit;

require_once 'automatewoo-legacy.php';


/**
 * AutomateWoo plugin singleton.
 *
 * @class   AutomateWoo
 * @package AutomateWoo
 */
final class AutomateWoo extends AutomateWoo_Legacy {

	/**
	 * The plugin version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * The plugin slug 'automatewoo'.
	 *
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * The plugin basename 'automatewoo/automatewoo.php'.
	 *
	 * @var string
	 */
	public $plugin_basename;

	/**
	 * The plugin website URL.
	 *
	 * @var string
	 */
	public $website_url = 'https://automatewoo.com/';

	/**
	 * Order helper class.
	 *
	 * @var AutomateWoo\Order_Helper
	 */
	public $order_helper;

	/**
	 * Options class.
	 *
	 * @var AutomateWoo\Options
	 */
	private $options;

	/**
	 * Instance of singleton.
	 *
	 * @var AutomateWoo
	 */
	private static $_instance = null;

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->version         = AUTOMATEWOO_VERSION;
		$this->plugin_basename = plugin_basename( AUTOMATEWOO_FILE );
		$this->plugin_slug     = AUTOMATEWOO_SLUG;
		include_once $this->path() . '/includes/autoloader.php';
		add_action( 'woocommerce_init', [ $this, 'init' ], 20 );
	}

	/**
	 * Init
	 */
	function init() {

		$this->includes();

		AutomateWoo\Constants::init();
		AutomateWoo\Post_Types::init();
		AutomateWoo\Cron::init();
		AutomateWoo\Ajax::init();
		AutomateWoo\Session_Tracker::init();
		AutomateWoo\Customers::init();

		// legacy access to session tracker class
		$this->session_tracker = new AutomateWoo\Session_Tracker();
		$this->order_helper    = new AutomateWoo\Order_Helper();

		do_action( 'automatewoo_init_addons' );

		// Init all triggers
		// Actions don't load until required by admin interface or when a workflow runs
		AutomateWoo\Triggers::init();

		if ( is_admin() ) {
			$this->admin = new AutomateWoo\Admin();
			AutomateWoo\Admin::init();
			AutomateWoo\Updater::init();
			AutomateWoo\Installer::init();
		}

		do_action( 'automatewoo_init' );

		AutomateWoo\Event_Helpers\User_Registration::init();
		AutomateWoo\Event_Helpers\Order_Pending::init();
		AutomateWoo\Event_Helpers\Order_Created::init();
		AutomateWoo\Event_Helpers\Order_Paid::init();
		AutomateWoo\Event_Helpers\Order_Status_Changed::init();
		AutomateWoo\Event_Helpers\Products_On_Sale::init();
		AutomateWoo\Event_Helpers\Review_Posted::init();

		if ( AutomateWoo\Integrations::is_subscriptions_active() ) {
			AutomateWoo\Event_Helpers\Subscription_Created::init();
			AutomateWoo\Event_Helpers\Subscription_Status_Changed::init();
			AutomateWoo\Event_Helpers\Subscription_Renewal_Payment_Complete::init();
			AutomateWoo\Event_Helpers\Subscription_Renewal_Payment_Failed::init();
		}

		/**
		 * Check if Points and Rewards is active
		 *
		 * @since 4.5.0
		 */
		if ( AutomateWoo\Integrations::is_points_rewards_active() ) {
			new \AutomateWoo\Points_Rewards_Integration();
		}

		if ( $this->is_request( 'ajax' ) || $this->is_request( 'cron' ) ) {
			// Load all background processes
			AutomateWoo\Background_Processes::get_all();
			// Load async request
			AutomateWoo\Events::get_event_runner_async_request();
		}

		if ( AutomateWoo\Options::abandoned_cart_enabled() ) {
			AutomateWoo\Carts::init();
		}

		AutomateWoo\Communication_Account_Tab::init();

		AutomateWoo\Workflows::init();
		AutomateWoo\Hooks::init();

		if ( version_compare( WC()->version, '3.4', '>=' ) ) {
			new AutomateWoo\Privacy();
		}

		do_action( 'automatewoo_loaded' );
	}

	/**
	 * File includes.
	 */
	function includes() {
		include_once $this->path() . '/includes/customer-functions.php';
		include_once $this->path() . '/includes/product-functions.php';
		include_once $this->path() . '/includes/helpers.php';
		include_once $this->path() . '/includes/hooks.php';

		if ( ! class_exists('Easy_User_Tags') ) {
			new AutomateWoo\User_Tags();
		}
	}

	/**
	 * Plugin options.
	 *
	 * @return AutomateWoo\Options
	 */
	function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = new AutomateWoo\Options();
		}
		return $this->options;
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type Ajax, frontend or admin.
	 *
	 * @return bool
	 */
	function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! WC()->is_rest_api_request();
		}
		return false;
	}

	/**
	 * Get the URL to something in the plugin dir.
	 *
	 * @param string $end End of the URL.
	 *
	 * @return string
	 */
	function url( $end = '' ) {
		return untrailingslashit( plugin_dir_url( $this->plugin_basename ) ) . $end;
	}

	/**
	 * Get the URL to something in the plugin admin assets dir.
	 *
	 * @param string $end End of the URL.
	 *
	 * @return string
	 */
	function admin_assets_url( $end = '' ) {
		return AW()->url( '/admin/assets' . $end );
	}

	/**
	 * Get the path to something in the plugin dir.
	 *
	 * @param string $end End of the path.
	 *
	 * @return string
	 */
	function path( $end = '' ) {
		return untrailingslashit( dirname( AUTOMATEWOO_FILE ) ) . $end;
	}

	/**
	 * Get the path to something in the plugin admin dir.
	 *
	 * @param string $end End of the path.
	 *
	 * @return string
	 */
	function admin_path( $end = '' ) {
		return $this->path( '/admin' . $end );
	}

	/**
	 * Get the path to something in the plugin library dir.
	 *
	 * @param string $end End of the path.
	 *
	 * @return string
	 */
	function lib_path( $end = '' ) {
		return $this->path( '/includes/libraries' . $end );
	}

	/**
	 * Return the singleton instance.
	 *
	 * @return AutomateWoo
	 */
	static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

/**
 * For backwards compatible.
 *
 * @deprecated
 *
 * @return AutomateWoo
 */
function AutomateWoo() {
	return AW();
}

/**
 * Access the plugin singleton with this.
 *
 * @return AutomateWoo
 */
function AW() {
	return AutomateWoo::instance();
}

AW();
