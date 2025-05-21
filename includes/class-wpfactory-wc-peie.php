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

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * localize.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 */
	function localize() {
		load_plugin_textdomain(
			'woo-product-excel-importer',
			false,
			dirname( plugin_basename( WPFACTORY_WC_PEIE_FILE ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 *
	 * @see     https://developer.woocommerce.com/docs/hpos-extension-recipe-book/
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				WPFACTORY_WC_PEIE_FILE,
				true
			);
		}
	}

	/**
	 * admin.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 */
	function admin() {

		// Settings
		add_action( 'admin_menu', array( $this, 'add_settings' ) );

	}

	/**
	 * add_settings.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 */
	function add_settings() {

		add_submenu_page(
			'edit.php?post_type=product',
			'Product Import Export',
			'Import from Excel',
			'wpeieWoo',
			'woo-product-importer',
			'woopei_init'
		);

		add_submenu_page(
			'woocommerce',
			'Product Import Export',
			'Import from Excel',
			'wpeieWoo',
			'woo-product-importer',
			'woopei_init'
		);

		add_menu_page(
			'Woo Product Importer Settings',
			'Product Import Export',
			'wpeieWoo',
			'woo-product-importer',
			'woopei_init',
			'dashicons-upload',
			'50'
		);

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
