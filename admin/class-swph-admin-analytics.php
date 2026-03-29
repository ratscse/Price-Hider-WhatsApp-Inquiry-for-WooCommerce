<?php
/**
 * Renders the analytics dashboard page.
 *
 * @package Price_Hider_WhatsApp_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Admin_Analytics
 */
class SWPH_Admin_Analytics {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_Admin_Analytics|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_Admin_Analytics
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
	 * Render the analytics page (called as a static callback by add_submenu_page).
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'price-hider-whatsapp-inquiry-for-woocommerce' ) );
		}

		$analytics = SWPH_Analytics::instance();
		$total     = $analytics->total_clicks();
		$last_7    = $analytics->clicks_last_days( 7 );
		$last_30   = $analytics->clicks_last_days( 30 );
		$rows      = $analytics->get_click_counts( 100 );
		?>
		<div class="wrap swph-admin-wrap">
			<h1 class="swph-page-title">
				<span class="swph-logo">📊</span>
				<?php esc_html_e( 'WhatsApp Button Analytics', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?>
			</h1>

			<?php include SWPH_PLUGIN_DIR . 'admin/views/support-banner.php'; ?>

			<!-- Summary Cards -->
			<div class="swph-stats-grid">
				<div class="swph-stat-card">
					<span class="swph-stat-number"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
					<span class="swph-stat-label"><?php esc_html_e( 'Total Clicks', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></span>
				</div>
				<div class="swph-stat-card">
					<span class="swph-stat-number"><?php echo esc_html( number_format_i18n( $last_7 ) ); ?></span>
					<span class="swph-stat-label"><?php esc_html_e( 'Last 7 Days', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></span>
				</div>
				<div class="swph-stat-card">
					<span class="swph-stat-number"><?php echo esc_html( number_format_i18n( $last_30 ) ); ?></span>
					<span class="swph-stat-label"><?php esc_html_e( 'Last 30 Days', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></span>
				</div>
				<div class="swph-stat-card">
					<span class="swph-stat-number"><?php echo esc_html( number_format_i18n( count( $rows ) ) ); ?></span>
					<span class="swph-stat-label"><?php esc_html_e( 'Products Tracked', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></span>
				</div>
			</div>

			<!-- Data Table -->
			<div class="swph-card">
				<h2><?php esc_html_e( 'Clicks per Product', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></h2>

				<?php if ( empty( $rows ) ) : ?>
					<p><?php esc_html_e( 'No click data yet. Enable analytics in Settings and wait for customers to click your WhatsApp buttons.', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></p>
				<?php else : ?>
					<table class="widefat striped swph-analytics-table">
						<thead>
							<tr>
								<th><?php esc_html_e( '#', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Product', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Product ID', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'WhatsApp Clicks', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $i => $row ) : ?>
							<tr>
								<td><?php echo absint( $i + 1 ); ?></td>
								<td><strong><?php echo esc_html( $row->product_name ); ?></strong></td>
								<td><?php echo absint( $row->product_id ); ?></td>
								<td>
									<span class="swph-click-badge"><?php echo esc_html( number_format_i18n( $row->click_count ) ); ?></span>
								</td>
								<td>
									<?php if ( '#' !== $row->product_url ) : ?>
										<a href="<?php echo esc_url( $row->product_url ); ?>" class="button button-small">
											<?php esc_html_e( 'Edit Product', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?>
										</a>
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
