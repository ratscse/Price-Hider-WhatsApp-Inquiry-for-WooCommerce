<?php
/**
 * Handles plugin activation, deactivation, and database setup.
 *
 * @package Rats_Price_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Install
 */
class SWPH_Install {

	/**
	 * Runs on plugin activation.
	 */
	public static function activate(): void {
		self::create_tables();
		self::set_default_options();
		flush_rewrite_rules();
	}

	/**
	 * Runs on plugin deactivation.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 */
	public static function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'swph_analytics';

		$sql = "CREATE TABLE {$table_name} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			product_id  BIGINT(20) UNSIGNED NOT NULL,
			clicked_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			user_agent  VARCHAR(255)        NOT NULL DEFAULT '',
			ip_hash     VARCHAR(64)         NOT NULL DEFAULT '',
			PRIMARY KEY (id),
			KEY product_id (product_id),
			KEY clicked_at (clicked_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'swph_db_version', SWPH_VERSION );
	}

	/**
	 * Set sensible default options if they do not exist yet.
	 */
	private static function set_default_options(): void {
		$defaults = array(
			'swph_global_whatsapp'         => '',
			'swph_guest_only'              => '0',
			'swph_default_button_label'    => __( 'WhatsApp Us', 'rats-price-inquiry-for-woocommerce' ),
			'swph_default_message_template'=> 'Hi, I\'m interested in {product_name}. Can you tell me the current price? Link: {product_url}',
			'swph_category_rules'          => array(),
			'swph_enable_analytics'        => '1',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				update_option( $key, $value );
			}
		}
	}
}
