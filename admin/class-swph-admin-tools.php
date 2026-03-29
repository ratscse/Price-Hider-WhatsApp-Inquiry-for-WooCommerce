<?php
/**
 * Admin Tools page — export analytics as CSV and reset data.
 *
 * @package Price_Hider_WhatsApp_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Admin_Tools
 */
class SWPH_Admin_Tools {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_Admin_Tools|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_Admin_Tools
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
	}

	/**
	 * Handle form submissions (export / reset).
	 */
	public function handle_actions(): void {
		if ( ! isset( $_GET['swph_action'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'Permission denied.', 'price-hider-whatsapp-inquiry-for-woocommerce' ) );
		}
		check_admin_referer( 'swph_tools_action' );

		$action = sanitize_key( $_GET['swph_action'] );

		if ( 'export_csv' === $action ) {
			$this->export_csv();
		}

		if ( 'reset_analytics' === $action ) {
			$this->reset_analytics();
			wp_safe_redirect( admin_url( 'admin.php?page=swph-tools&swph_reset=1' ) );
			exit;
		}
	}

	/**
	 * Stream a CSV download of click analytics.
	 */
	private function export_csv(): void {
		$rows = SWPH_Analytics::instance()->get_click_counts( 9999 );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="swph-analytics-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fputcsv( $output, array( 'Product ID', 'Product Name', 'Click Count' ) );

		foreach ( $rows as $row ) {
			fputcsv( $output, array( $row->product_id, $row->product_name, $row->click_count ) );
		}
		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}

	/**
	 * Truncate the analytics table.
	 */
	private function reset_analytics(): void {
		global $wpdb;
		// Table name and prefix are controlled by WordPress, so direct query is safe.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}swph_analytics`" );
	}

	/**
	 * Render the tools page.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'price-hider-whatsapp-inquiry-for-woocommerce' ) );
		}

		$reset_done  = isset( $_GET['swph_reset'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$export_url  = wp_nonce_url( admin_url( 'admin.php?page=swph-tools&swph_action=export_csv' ), 'swph_tools_action' );
		$reset_url   = wp_nonce_url( admin_url( 'admin.php?page=swph-tools&swph_action=reset_analytics' ), 'swph_tools_action' );
		?>
		<div class="wrap swph-admin-wrap">
			<h1 class="swph-page-title">
				<span class="swph-logo">🔧</span>
				<?php esc_html_e( 'Tools', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?>
			</h1>

			<?php if ( $reset_done ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Analytics data has been reset.', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></p></div>
			<?php endif; ?>

			<!-- Export -->
			<div class="swph-card">
				<h2><?php esc_html_e( '📥 Export Analytics', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Download all WhatsApp button click data as a CSV file.', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></p>
				<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Download CSV', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?>
				</a>
			</div>

			<!-- Reset -->
			<div class="swph-card swph-card-danger">
				<h2><?php esc_html_e( '🗑️ Reset Analytics', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Permanently delete all recorded click data. This cannot be undone.', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></p>
				<a href="<?php echo esc_url( $reset_url ); ?>"
				   class="button button-secondary swph-btn-reset"
				   onclick="return confirm('<?php echo esc_js( __( 'Are you sure? All analytics data will be permanently deleted.', 'price-hider-whatsapp-inquiry-for-woocommerce' ) ); ?>');">
					<?php esc_html_e( 'Reset All Analytics', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?>
				</a>
			</div>

			<!-- Plugin info -->
			<div class="swph-card">
				<h2><?php esc_html_e( 'ℹ️ Plugin Information', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></h2>
				<table class="widefat striped" style="max-width:500px">
					<tbody>
						<tr><th><?php esc_html_e( 'Version', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th><td><?php echo esc_html( SWPH_VERSION ); ?></td></tr>
						<tr><th><?php esc_html_e( 'WooCommerce', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th><td><?php echo esc_html( defined( 'WC_VERSION' ) ? WC_VERSION : 'N/A' ); ?></td></tr>
						<tr><th><?php esc_html_e( 'WordPress', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
						<tr><th><?php esc_html_e( 'PHP', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th><td><?php echo esc_html( phpversion() ); ?></td></tr>
						<tr><th><?php esc_html_e( 'DB Table', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th>
							<td>
								<?php
								global $wpdb;
								$exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}swph_analytics'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
								echo $exists
									? '<span style="color:#25d366">✔ ' . esc_html__( 'Created', 'price-hider-whatsapp-inquiry-for-woocommerce' ) . '</span>'
									: '<span style="color:#d63638">✘ ' . esc_html__( 'Missing — try deactivating and reactivating the plugin', 'price-hider-whatsapp-inquiry-for-woocommerce' ) . '</span>';
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<?php include SWPH_PLUGIN_DIR . 'admin/views/support-banner.php'; ?>
		</div>
		<?php
	}
}
