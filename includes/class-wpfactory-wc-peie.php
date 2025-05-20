<?php
/**
 * Product Excel Import & Export for WooCommerce - Main Class
 *
 * @version 7.0.0
 * @since   7.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_PEIE' ) ) :

final class WPFactory_WC_PEIE {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 7.0.0
	 */
	public $version = WPFACTORY_WC_PEIE_VERSION;

	/**
	 * @var   WPFactory_WC_PEIE The single instance of the class
	 * @since 7.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main WPFactory_WC_PEIE Instance.
	 *
	 * Ensures only one instance of WPFactory_WC_PEIE is loaded or can be loaded.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 *
	 * @static
	 * @return  WPFactory_WC_PEIE - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WPFactory_WC_PEIE Constructor.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Check for active WooCommerce plugin
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * admin.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 */
	function admin() {
		return true;
	}

	/**
	 * plugin_url.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( WPFACTORY_WC_PEIE_FILE ) );
	}

	/**
	 * plugin_path.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( WPFACTORY_WC_PEIE_FILE ) );
	}

}

endif;
