<?php
/**
 * Manages retrieval of plugin settings with caching.
 *
 * @package Price_Hider_WhatsApp_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Settings
 */
class SWPH_Settings {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_Settings|null
	 */
	private static $instance = null;

	/**
	 * Cached settings array.
	 *
	 * @var array
	 */
	private array $cache = array();

	/**
	 * Get or create singleton.
	 *
	 * @return SWPH_Settings
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
	 * Generic getter with in-request cache.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Fallback value.
	 * @return mixed
	 */
	public function get( string $key, $default = '' ) {
		if ( ! array_key_exists( $key, $this->cache ) ) {
			$this->cache[ $key ] = get_option( $key, $default );
		}
		return $this->cache[ $key ];
	}

	// -----------------------------------------------------------------------
	// Convenience getters
	// -----------------------------------------------------------------------

	/** Global WhatsApp number (fallback). */
	public function global_whatsapp(): string {
		return sanitize_text_field( $this->get( 'swph_global_whatsapp', '' ) );
	}

	/** Whether price hiding applies only to guests. */
	public function guest_only(): bool {
		return (bool) $this->get( 'swph_guest_only', '0' );
	}

	/** Default button label. */
	public function default_button_label(): string {
		return sanitize_text_field( $this->get( 'swph_default_button_label', __( 'WhatsApp Us', 'price-hider-whatsapp-inquiry-for-woocommerce' ) ) );
	}

	/** Default message template. */
	public function default_message_template(): string {
		return sanitize_textarea_field( $this->get( 'swph_default_message_template', 'Hi, I\'m interested in {product_name}. Can you tell me the current price? Link: {product_url}' ) );
	}

	/**
	 * Category rules: array of [ category_id => [ whatsapp, rotate_whatsapp, label ] ].
	 *
	 * @return array
	 */
	public function category_rules(): array {
		$rules = $this->get( 'swph_category_rules', array() );
		return is_array( $rules ) ? $rules : array();
	}

	/** Whether analytics tracking is enabled. */
	public function analytics_enabled(): bool {
		return (bool) $this->get( 'swph_enable_analytics', '1' );
	}

	/**
	 * Resolve the best WhatsApp number for a given product.
	 * Priority: product-level → category-level → global.
	 * Supports round-robin rotation between two numbers.
	 *
	 * @param WC_Product $product Product object.
	 * @return string Phone number (digits only).
	 */
	public function resolve_whatsapp_number( WC_Product $product ): string {
		$product_id = $product->get_id();

		// 1. Product-level number.
		$product_number = get_post_meta( $product_id, '_swph_whatsapp_number', true );
		if ( ! empty( $product_number ) ) {
			return $this->clean_number( $product_number );
		}

		// 2. Category-level (first matching category that has a rule).
		$category_rules = $this->category_rules();
		$term_ids       = wc_get_product_term_ids( $product_id, 'product_cat' );

		foreach ( $term_ids as $term_id ) {
			if ( ! empty( $category_rules[ $term_id ] ) ) {
				$rule    = $category_rules[ $term_id ];
				$primary = ! empty( $rule['whatsapp'] ) ? $rule['whatsapp'] : '';
				$rotate  = ! empty( $rule['rotate_whatsapp'] ) ? $rule['rotate_whatsapp'] : '';

				if ( $primary && $rotate ) {
					// Simple round-robin via a transient counter.
					$counter_key = 'swph_rotate_' . $term_id;
					$counter     = (int) get_transient( $counter_key );
					$number      = ( 0 === $counter % 2 ) ? $primary : $rotate;
					set_transient( $counter_key, $counter + 1, DAY_IN_SECONDS );
					return $this->clean_number( $number );
				}

				if ( $primary ) {
					return $this->clean_number( $primary );
				}
			}
		}

		// 3. Global fallback.
		return $this->clean_number( $this->global_whatsapp() );
	}

	/**
	 * Resolve button label for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	public function resolve_button_label( WC_Product $product ): string {
		$product_id    = $product->get_id();
		$product_label = get_post_meta( $product_id, '_swph_button_label', true );

		if ( ! empty( $product_label ) ) {
			return sanitize_text_field( $product_label );
		}

		// Check category rule.
		$category_rules = $this->category_rules();
		$term_ids       = wc_get_product_term_ids( $product_id, 'product_cat' );

		foreach ( $term_ids as $term_id ) {
			if ( ! empty( $category_rules[ $term_id ]['label'] ) ) {
				return sanitize_text_field( $category_rules[ $term_id ]['label'] );
			}
		}

		return $this->default_button_label();
	}

	/**
	 * Strip non-digit characters from a phone number.
	 *
	 * @param string $number Raw number.
	 * @return string
	 */
	private function clean_number( string $number ): string {
		return preg_replace( '/\D/', '', $number );
	}
}
