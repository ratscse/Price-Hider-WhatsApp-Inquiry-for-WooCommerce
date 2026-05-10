<?php
/**
 * Handles WordPress admin pages and settings registration.
 *
 * @package Rats_Price_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Admin
 */
class SWPH_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_Admin|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_Admin
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . SWPH_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Register admin menu pages.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Rats Price Inquiry', 'rats-price-inquiry-for-woocommerce' ),
			__( 'Price Inquiry', 'rats-price-inquiry-for-woocommerce' ),
			'manage_woocommerce',
			'swph-settings',
			array( $this, 'render_settings_page' ),
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#a7aaad" d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.845L0 24l6.335-1.652C8.02 23.438 9.977 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>' ),
			56
		);

		add_submenu_page(
			'swph-settings',
			__( 'Settings', 'rats-price-inquiry-for-woocommerce' ),
			__( 'Settings', 'rats-price-inquiry-for-woocommerce' ),
			'manage_woocommerce',
			'swph-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'swph-settings',
			__( 'Analytics', 'rats-price-inquiry-for-woocommerce' ),
			__( 'Analytics', 'rats-price-inquiry-for-woocommerce' ),
			'manage_woocommerce',
			'swph-analytics',
			array( 'SWPH_Admin_Analytics', 'render_page' )
		);

		add_submenu_page(
			'swph-settings',
			__( 'Tools', 'rats-price-inquiry-for-woocommerce' ),
			__( 'Tools', 'rats-price-inquiry-for-woocommerce' ),
			'manage_woocommerce',
			'swph-tools',
			array( 'SWPH_Admin_Tools', 'render_page' )
		);
	}

	/**
	 * Register settings with the Settings API.
	 */
	public function register_settings(): void {
		register_setting( 'swph_settings_group', 'swph_global_whatsapp', array( 'sanitize_callback' => array( $this, 'sanitize_phone' ) ) );
		register_setting( 'swph_settings_group', 'swph_guest_only', array( 'sanitize_callback' => 'absint' ) );
		register_setting( 'swph_settings_group', 'swph_default_button_label', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'swph_settings_group', 'swph_contact_price_text', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'swph_settings_group', 'swph_default_message_template', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
		register_setting( 'swph_settings_group', 'swph_category_rules', array( 'sanitize_callback' => array( $this, 'sanitize_category_rules' ) ) );
		register_setting( 'swph_settings_group', 'swph_enable_analytics', array( 'sanitize_callback' => 'absint' ) );
	}

	/**
	 * Sanitize phone number — digits only.
	 *
	 * @param string $value Raw input.
	 * @return string
	 */
	public function sanitize_phone( string $value ): string {
		return preg_replace( '/\D/', '', $value );
	}

	/**
	 * Sanitize the nested category rules array.
	 *
	 * @param mixed $value Raw POST value.
	 * @return array
	 */
	public function sanitize_category_rules( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		$sanitized = array();
		foreach ( $value as $term_id => $rule ) {
			$term_id = absint( $term_id );
			if ( ! $term_id ) {
				continue;
			}
			$sanitized[ $term_id ] = array(
				'hide_price'      => ! empty( $rule['hide_price'] ) ? 1 : 0,
				'whatsapp'        => preg_replace( '/\D/', '', sanitize_text_field( $rule['whatsapp'] ?? '' ) ),
				'rotate_whatsapp' => preg_replace( '/\D/', '', sanitize_text_field( $rule['rotate_whatsapp'] ?? '' ) ),
				'label'           => sanitize_text_field( $rule['label'] ?? '' ),
			);
		}
		return $sanitized;
	}

	/**
	 * Enqueue admin CSS/JS only on plugin pages.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( string $hook ): void {
		// WordPress generates hook names as: {parent-slug}_page_{child-slug}
		// For a top-level page: toplevel_page_{slug}
		// Sub-pages of swph-settings: swph-settings_page_{slug}
		
		$plugin_pages = array(
			'toplevel_page_swph-settings',
			'price-inquiry_page_swph-analytics',
			'price-inquiry_page_swph-tools',
		);
		if ( ! in_array( $hook, $plugin_pages, true ) ) {
			return;
		}
		wp_enqueue_style( 'swph-admin', SWPH_PLUGIN_URL . 'admin/css/swph-admin.css', array(), SWPH_VERSION );
		wp_enqueue_script( 'swph-admin', SWPH_PLUGIN_URL . 'admin/js/swph-admin.js', array( 'jquery' ), SWPH_VERSION, true );
	}

	/**
	 * Add "Settings" link on the Plugins page.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function plugin_action_links( array $links ): array {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=swph-settings' ) ) . '">' . esc_html__( 'Settings', 'rats-price-inquiry-for-woocommerce' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Render the main settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'rats-price-inquiry-for-woocommerce' ) );
		}

		$settings       = SWPH_Settings::instance();
		$category_rules = $settings->category_rules();
		$categories     = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
		?>
		<div class="wrap swph-admin-wrap">
			<h1 class="swph-page-title">
				<span class="swph-logo">
					<img src="<?php echo esc_url( SWPH_PLUGIN_URL . 'admin/img/logo.png' ); ?>" alt="<?php esc_attr_e( 'Rats Price Inquiry for WooCommerce', 'rats-price-inquiry-for-woocommerce' ); ?>" style="height: 32px; border-radius: 10%; width: auto;">
				</span>
				<?php esc_html_e( 'Rats Price Inquiry for WooCommerce', 'rats-price-inquiry-for-woocommerce' ); ?>
			</h1>

		<?php include SWPH_PLUGIN_DIR . 'admin/views/support-banner.php'; ?>

			<?php settings_errors( 'swph_settings_group' ); ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'swph_settings_group' ); ?>

				<!-- Global Settings -->
				<div class="swph-card">
					<h2><?php esc_html_e( '⚙️ Global Settings', 'rats-price-inquiry-for-woocommerce' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
								<th scope="row"><label for="swph_global_whatsapp"><?php esc_html_e( 'Global WhatsApp Number', 'rats-price-inquiry-for-woocommerce' ); ?></label></th>
							<td>
								<input type="text" id="swph_global_whatsapp" name="swph_global_whatsapp" value="<?php echo esc_attr( $settings->global_whatsapp() ); ?>" placeholder="14155238886" class="regular-text">
									<p class="description"><?php esc_html_e( 'Digits only, including country code. Used as fallback when no category/product number is set.', 'rats-price-inquiry-for-woocommerce' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Guest-Only Mode', 'rats-price-inquiry-for-woocommerce' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="swph_guest_only" value="1" <?php checked( $settings->guest_only() ); ?>>
										<?php esc_html_e( 'Hide prices only for logged-out visitors (members see normal prices)', 'rats-price-inquiry-for-woocommerce' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="swph_default_button_label"><?php esc_html_e( 'Default Button Label', 'rats-price-inquiry-for-woocommerce' ); ?></label></th>
							<td>
								<input type="text" id="swph_default_button_label" name="swph_default_button_label" value="<?php echo esc_attr( $settings->default_button_label() ); ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="swph_contact_price_text">
									<?php esc_html_e( 'Contact Price Text', 'rats-price-inquiry-for-woocommerce' ); ?>
								</label>
							</th>
							<td>
								<input
									type="text"
									id="swph_contact_price_text"
									name="swph_contact_price_text"
									value="<?php echo esc_attr( $settings->contact_price_text() ); ?>"
									class="regular-text"
								>

								<p class="description">
									<?php esc_html_e( 'Text shown instead of the WooCommerce price.', 'rats-price-inquiry-for-woocommerce' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="swph_default_message_template"><?php esc_html_e( 'WhatsApp Message Template', 'rats-price-inquiry-for-woocommerce' ); ?></label></th>
							<td>
								<textarea id="swph_default_message_template" name="swph_default_message_template" rows="4" class="large-text"><?php echo esc_textarea( $settings->default_message_template() ); ?></textarea>
								<p class="description">
											<?php esc_html_e( 'Available placeholders (click to copy):', 'rats-price-inquiry-for-woocommerce' ); ?>
									<a href="#" class="swph-copy-placeholder button button-small" data-value="{product_name}" style="margin:2px;">{product_name}</a>
									<a href="#" class="swph-copy-placeholder button button-small" data-value="{product_url}" style="margin:2px;">{product_url}</a>
									<a href="#" class="swph-copy-placeholder button button-small" data-value="{product_sku}" style="margin:2px;">{product_sku}</a>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Enable Analytics', 'rats-price-inquiry-for-woocommerce' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="swph_enable_analytics" value="1" <?php checked( $settings->analytics_enabled() ); ?>>
											<?php esc_html_e( 'Track WhatsApp button clicks', 'rats-price-inquiry-for-woocommerce' ); ?>
								</label>
							</td>
						</tr>
					</table>
				</div>

				<!-- Category Rules -->
				<div class="swph-card">
						<h2><?php esc_html_e( '📂 Category Rules', 'rats-price-inquiry-for-woocommerce' ); ?></h2>
<p class="description"><?php esc_html_e( 'Set routing rules per product category. Leave fields blank to inherit global settings.', 'rats-price-inquiry-for-woocommerce' ); ?></p>

					<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
					<div class="swph-category-table-wrap">
						<table class="swph-category-table widefat striped">
							<thead>
								<tr>
											<th><?php esc_html_e( 'Category', 'rats-price-inquiry-for-woocommerce' ); ?></th>
											<th><?php esc_html_e( 'Hide Price', 'rats-price-inquiry-for-woocommerce' ); ?></th>
											<th><?php esc_html_e( 'WhatsApp Number (Primary)', 'rats-price-inquiry-for-woocommerce' ); ?></th>
											<th><?php esc_html_e( 'WhatsApp Number (Rotate)', 'rats-price-inquiry-for-woocommerce' ); ?></th>
											<th><?php esc_html_e( 'Button Label', 'rats-price-inquiry-for-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $categories as $cat ) :
									$rule = $category_rules[ $cat->term_id ] ?? array();
								?>
								<tr>
									<td><strong><?php echo esc_html( $cat->name ); ?></strong></td>
									<td>
										<input type="checkbox"
											name="swph_category_rules[<?php echo absint( $cat->term_id ); ?>][hide_price]"
											value="1"
											<?php checked( ! empty( $rule['hide_price'] ) ); ?>>
									</td>
									<td>
										<input type="text"
											name="swph_category_rules[<?php echo absint( $cat->term_id ); ?>][whatsapp]"
											value="<?php echo esc_attr( $rule['whatsapp'] ?? '' ); ?>"
											placeholder="14155238886"
											class="regular-text">
									</td>
									<td>
										<input type="text"
											name="swph_category_rules[<?php echo absint( $cat->term_id ); ?>][rotate_whatsapp]"
											value="<?php echo esc_attr( $rule['rotate_whatsapp'] ?? '' ); ?>"
															placeholder="<?php esc_attr_e( 'Optional second number', 'rats-price-inquiry-for-woocommerce' ); ?>"
											class="regular-text">
									</td>
									<td>
										<input type="text"
											name="swph_category_rules[<?php echo absint( $cat->term_id ); ?>][label]"
											value="<?php echo esc_attr( $rule['label'] ?? '' ); ?>"
															placeholder="<?php esc_attr_e( 'e.g., Request Quote', 'rats-price-inquiry-for-woocommerce' ); ?>"
											class="regular-text">
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php else : ?>
								<p><?php esc_html_e( 'No product categories found. Create some in WooCommerce first.', 'rats-price-inquiry-for-woocommerce' ); ?></p>
					<?php endif; ?>
				</div>

				<?php submit_button( __( 'Save Settings', 'rats-price-inquiry-for-woocommerce' ) ); ?>
			</form>
		<?php
	}
}
