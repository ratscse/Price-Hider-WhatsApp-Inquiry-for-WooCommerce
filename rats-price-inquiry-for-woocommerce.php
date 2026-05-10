<?php
/**
 * Plugin Name:       Rats Price Inquiry for WooCommerce
 * Description:       Hide WooCommerce prices and replace them with smart WhatsApp inquiry buttons. Features category/product routing, auto-messages, guest-only mode, custom labels, and click analytics.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            M A Shuab Ratan
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rats-price-inquiry-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 6.0
 * WC tested up to:   8.9
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'SWPH_VERSION', '1.0.0' );
define( 'SWPH_PLUGIN_FILE', __FILE__ );
define( 'SWPH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWPH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SWPH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class loader.
 *
 * @since 1.0.0
 */
final class Rats_Price_Inquiry {

	/**
	 * Single instance of this class.
	 *
	 * @var Rats_Price_Inquiry|null
	 */
	private static $instance = null;

	/**
	 * Get or create the singleton instance.
	 *
	 * @return Rats_Price_Inquiry
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — private to enforce singleton pattern.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes(): void {
		require_once SWPH_PLUGIN_DIR . 'includes/class-swph-install.php';
		require_once SWPH_PLUGIN_DIR . 'includes/class-swph-settings.php';
		require_once SWPH_PLUGIN_DIR . 'includes/class-swph-price-hider.php';
		require_once SWPH_PLUGIN_DIR . 'includes/class-swph-inquiry-button.php';
		require_once SWPH_PLUGIN_DIR . 'includes/class-swph-analytics.php';
		require_once SWPH_PLUGIN_DIR . 'includes/class-swph-product-meta.php';
		require_once SWPH_PLUGIN_DIR . 'includes/class-swph-rest-api.php';

		if ( is_admin() ) {
			require_once SWPH_PLUGIN_DIR . 'admin/class-swph-admin.php';
			require_once SWPH_PLUGIN_DIR . 'admin/class-swph-admin-analytics.php';
			require_once SWPH_PLUGIN_DIR . 'admin/class-swph-admin-tools.php';
		}
	}

	/**
	 * Register WordPress hooks.
	 */
	private function init_hooks(): void {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		register_activation_hook( SWPH_PLUGIN_FILE, array( 'SWPH_Install', 'activate' ) );
		register_deactivation_hook( SWPH_PLUGIN_FILE, array( 'SWPH_Install', 'deactivate' ) );
	}

	/**
	 * Boot the plugin after all plugins are loaded.
	 */
	public function on_plugins_loaded(): void {
		// Bail early if WooCommerce is not active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		// Declare HPOS compatibility.
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		// Boot components.
		SWPH_Settings::instance();
		SWPH_Price_Hider::instance();
		SWPH_Inquiry_Button::instance();
		SWPH_Analytics::instance();
		SWPH_Product_Meta::instance();
		SWPH_REST_API::instance();

		if ( is_admin() ) {
			SWPH_Admin::instance();
			SWPH_Admin_Analytics::instance();
			SWPH_Admin_Tools::instance();
		}
	}

	/**
	 * Declare WooCommerce HPOS compatibility.
	 */
	public function declare_hpos_compatibility(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				SWPH_PLUGIN_FILE,
				true
			);
		}
	}

	/**
	 * Admin notice when WooCommerce is not active.
	 */
	public function woocommerce_missing_notice(): void {
		echo '<div class="notice notice-error"><p>' .
				esc_html__( 'Rats Price Inquiry for WooCommerce requires WooCommerce to be installed and active.', 'rats-price-inquiry-for-woocommerce' ) .
			'</p></div>';
	}
}

/**
 * Returns the main instance of the plugin.
 *
 * @return Rats_Price_Inquiry
 */
function SWPH(): Rats_Price_Inquiry {
	return Rats_Price_Inquiry::instance();
}

// Kick off.
SWPH();
