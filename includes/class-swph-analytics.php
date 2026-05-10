<?php
/**
 * Handles recording and querying WhatsApp button click analytics.
 *
 * @package Rats_Price_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Analytics
 */
class SWPH_Analytics {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_Analytics|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_Analytics
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {}

	/**
	 * Record a single button click.
	 *
	 * @param int $product_id Product ID.
	 */
	public function record_click( int $product_id ): void {
		global $wpdb;

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . 'swph_analytics',
			array(
				'product_id' => $product_id,
				'clicked_at' => current_time( 'mysql' ),
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] )
					? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
					: '',
				'ip_hash'    => isset( $_SERVER['REMOTE_ADDR'] )
					? hash( 'sha256', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) )
					: '',
			),
			array( '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Retrieve click counts per product.
	 *
	 * @param int    $limit     Max rows to return.
	 * @param string $date_from MySQL date string (YYYY-MM-DD) or empty for all time.
	 * @return array  Array of objects with product_id, product_name, click_count.
	 */
	public function get_click_counts( int $limit = 50, string $date_from = '' ): array {
		global $wpdb;

		if ( ! empty( $date_from ) ) {

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.product_id, COUNT(*) AS click_count
				FROM {$wpdb->prefix}swph_analytics a
				WHERE clicked_at >= %s
				GROUP BY a.product_id
				ORDER BY click_count DESC
				LIMIT %d",
				$date_from,
				$limit
			)
		);

		} else {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.product_id, COUNT(*) AS click_count
				FROM {$wpdb->prefix}swph_analytics a
				GROUP BY a.product_id
				ORDER BY click_count DESC
				LIMIT %d",
				$limit
			)
		);
		}	


		if ( empty( $results ) ) {
			return array();
		}

		// Attach product names.
		foreach ( $results as &$row ) {
			$product = wc_get_product( $row->product_id );
			$row->product_name = $product ? $product->get_name() : __( '(deleted)', 'rats-price-inquiry-for-woocommerce' );
			$row->product_url  = $product ? get_edit_post_link( $row->product_id ) : '#';
		}
		unset( $row );

		return $results;
	}

	/**
	 * Total clicks across all products.
	 *
	 * @return int
	 */
	public function total_clicks(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}swph_analytics" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Clicks in the last N days.
	 *
	 * @param int $days Number of days.
	 * @return int
	 */
	public function clicks_last_days( int $days = 30 ): int {
		global $wpdb;
		$date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}swph_analytics WHERE clicked_at >= %s",
				$date
			)
		);
	}
}
