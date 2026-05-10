<?php
/**
 * Handles hiding WooCommerce prices on the front end.
 *
 * @package Rats_Price_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Price_Hider
 */
class SWPH_Price_Hider {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_Price_Hider|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_Price_Hider
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {
		add_filter( 'woocommerce_get_price_html', array( $this, 'maybe_hide_price' ), 10, 2 );
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'maybe_hide_add_to_cart' ), 5 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'maybe_hide_loop_button' ), 10, 2 );
	}

	/**
	 * Replace price HTML when hiding is required.
	 *
	 * @param string     $price_html Original price HTML.
	 * @param WC_Product $product    Product object.
	 * @return string
	 */
	public function maybe_hide_price( string $price_html, WC_Product $product ): string {
		if ( ! $this->should_hide( $product ) ) {
			return $price_html;
		}

		$settings = SWPH_Settings::instance();

		return '<span class="swph-price-hidden">' .
				esc_html( $settings->contact_price_text() ) .
			'</span>';
	}

	/**
	 * Remove the add-to-cart form on single product pages.
	 */
	public function maybe_hide_add_to_cart(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		if ( ! $this->should_hide( $product ) ) {
			return;
		}

		// Remove standard add-to-cart elements.
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	}

	/**
	 * Remove the add-to-cart button in loops (shop/category pages).
	 *
	 * @param string     $link    Button HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function maybe_hide_loop_button( string $link, WC_Product $product ): string {
		if ( ! $this->should_hide( $product ) ) {
			return $link;
		}
		return ''; // Button replaced by WhatsApp button elsewhere.
	}

	/**
	 * Determine if price/cart should be hidden for a given product.
	 *
	 * @param WC_Product $product Product.
	 * @return bool
	 */
	public function should_hide( WC_Product $product ): bool {
		$settings = SWPH_Settings::instance();

		// Guest-only mode: logged-in users see normal prices.
		if ( $settings->guest_only() && is_user_logged_in() ) {
			return false;
		}

		$product_id = $product->get_id();

		// 1. Product-level override.
		$product_hide = get_post_meta( $product_id, '_swph_hide_price', true );
		if ( 'yes' === $product_hide ) {
			return true;
		}
		if ( 'no' === $product_hide ) {
			return false;
		}

		// 2. Category-level rule.
		$term_ids = wc_get_product_term_ids( $product_id, 'product_cat' );
		$rules    = $settings->category_rules();

		foreach ( $term_ids as $term_id ) {
			if ( isset( $rules[ $term_id ] ) && ! empty( $rules[ $term_id ]['hide_price'] ) ) {
				return (bool) $rules[ $term_id ]['hide_price'];
			}
		}

		return false;
	}
}
