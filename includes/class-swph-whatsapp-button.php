<?php
/**
 * Renders the WhatsApp inquiry button on product pages and loops.
 *
 * @package Price_Hider_WhatsApp_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_WhatsApp_Button
 */
class SWPH_WhatsApp_Button {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_WhatsApp_Button|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_WhatsApp_Button
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {
		// Single product page — render after the price.
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_single_button' ), 31 );

		// Shop / category loops.
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'render_loop_button' ), 10 );

		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// AJAX handler for analytics ping.
		add_action( 'wp_ajax_swph_track_click', array( $this, 'ajax_track_click' ) );
		add_action( 'wp_ajax_nopriv_swph_track_click', array( $this, 'ajax_track_click' ) );
	}

	/**
	 * Enqueue front-end CSS and JS.
	 * Fires on all WooCommerce pages (shop, category, single product, etc.).
	 */
	public function enqueue_assets(): void {
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			return;
		}

		wp_enqueue_style(
			'swph-public',
			SWPH_PLUGIN_URL . 'public/css/swph-public.css',
			array(),
			SWPH_VERSION
		);

		wp_enqueue_script(
			'swph-public',
			SWPH_PLUGIN_URL . 'public/js/swph-public.js',
			array( 'jquery' ),
			SWPH_VERSION,
			true
		);

		wp_localize_script(
			'swph-public',
			'swphData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'swph_track_nonce' ),
			)
		);
	}

	/**
	 * Render button on single product page.
	 */
	public function render_single_button(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		if ( ! SWPH_Price_Hider::instance()->should_hide( $product ) ) {
			return;
		}
		echo $this->build_button_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render button in shop/category loop.
	 */
	public function render_loop_button(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		if ( ! SWPH_Price_Hider::instance()->should_hide( $product ) ) {
			return;
		}
		echo $this->build_button_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Build the WhatsApp button anchor HTML.
	 *
	 * @param WC_Product $product Product object.
	 * @return string Safe HTML string.
	 */
	public function build_button_html( WC_Product $product ): string {
		$settings   = SWPH_Settings::instance();
		$number     = $settings->resolve_whatsapp_number( $product );
		$label      = $settings->resolve_button_label( $product );
		$message    = $this->build_message( $product );
		$product_id = $product->get_id();

		if ( empty( $number ) ) {
			return '';
		}

		$wa_url = esc_url(
			'https://wa.me/' . $number . '?text=' . rawurlencode( $message )
		);

		$html  = '<div class="swph-whatsapp-wrap">';
		$html .= sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer" class="swph-whatsapp-btn" data-product-id="%d" aria-label="%s">',
			$wa_url,
			absint( $product_id ),
			esc_attr( $label )
		);
		$html .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.845L0 24l6.335-1.652C8.02 23.438 9.977 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.666-.491-5.218-1.352l-.374-.213-3.762.981.999-3.671-.235-.384A9.946 9.946 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>';
		$html .= '<span>' . esc_html( $label ) . '</span>';
		$html .= '</a>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Build the pre-filled WhatsApp message for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return string Decoded message string (rawurlencode applied later).
	 */
	private function build_message( WC_Product $product ): string {
		$settings = SWPH_Settings::instance();

		// Product-level custom message overrides the template.
		$custom_message = get_post_meta( $product->get_id(), '_swph_custom_message', true );
		$template       = ! empty( $custom_message )
			? $custom_message
			: $settings->default_message_template();

		$placeholders = array(
			'{product_name}' => $product->get_name(),
			'{product_url}'  => get_permalink( $product->get_id() ),
			'{product_sku}'  => $product->get_sku() ?: 'N/A',
		);

		return str_replace(
			array_keys( $placeholders ),
			array_values( $placeholders ),
			$template
		);
	}

	/**
	 * AJAX handler: record a WhatsApp button click.
	 */
	public function ajax_track_click(): void {
		check_ajax_referer( 'swph_track_nonce', 'nonce' );

		if ( ! SWPH_Settings::instance()->analytics_enabled() ) {
			wp_send_json_success();
			return;
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( 'Invalid product.' );
			return;
		}

		SWPH_Analytics::instance()->record_click( $product_id );
		wp_send_json_success();
	}
}
