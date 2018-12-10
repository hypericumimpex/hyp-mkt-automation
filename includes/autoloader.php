<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @since 4.0
 */
class Autoloader {

	/**
	 * Register autoloader
	 */
	static function init() {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}


	/**
	 * @param $class
	 */
	static function autoload( $class ) {
		$path = self::get_autoload_path( $class );

		if ( $path && file_exists( $path ) ) {
			include $path;
		}
	}


	/**
	 * @param string $class
	 * @return string
	 */
	static function get_autoload_path( $class ) {
		if ( substr( $class, 0, 3 ) != 'AW_' && substr( $class, 0, 12 ) != 'AutomateWoo\\' ) {
			return false;
		}

		if ( strpos( $class, 'AutomateWoo\Referrals\\' ) === 0 ) {
			return false;
		}

		$file = str_replace( ['AW_', 'AutomateWoo\\' ], '/', $class );
		$file = str_replace( '_', '-', $file );
		$file = strtolower( $file );
		$file = str_replace( '\\', '/', $file );

		$abstracts = [
			'/action',
			'/trigger',
			'/model',
			'/query-custom-table',
			'/integration',
			'/variable',
			'/options-api',
			'/tool',
			'/data-type',
			'/database-table',
		];


		if ( in_array( $file, $abstracts ) ) {
			return AW()->path() . '/includes/abstracts' . $file . '.php';
		}
		if ( $file === '/admin' ) {
			include_once AW()->path() . '/admin/admin.php';
		}
		elseif ( strstr( $file, '/admin-' ) || strstr( $file, '/admin/' ) ) {
			$file = str_replace( '/admin-', '/admin/', $file );
			$file = str_replace( '/controller-', '/controllers/', $file );

			return AW()->path() . $file . '.php';
		}
		else {
			$file = str_replace( '/trigger-', '/triggers/', $file );
			$file = str_replace( '/action-', '/actions/', $file );
			$file = str_replace( '/model-', '/models/model-', $file );
			$file = str_replace( '/variable-', '/variables/', $file );
			$file = str_replace( '/integration-', '/integrations/', $file );
			$file = str_replace( '/rule-', '/rules/', $file );

			return AW()->path() . '/includes' . $file . '.php';
		}
	}


}

Autoloader::init();
