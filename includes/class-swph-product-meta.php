<?php
/**
 * Adds per-product meta box fields for WhatsApp settings.
 *
 * @package Price_Hider_WhatsApp_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_Product_Meta
 */
class SWPH_Product_Meta {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_Product_Meta|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_Product_Meta
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_fields' ) );
	}

	/**
	 * Output the custom fields inside the General tab of the product edit page.
	 */
	public function add_fields(): void {
		global $post;
		$product_id = $post->ID;

		echo '<div class="options_group swph-product-options">';
		echo '<h4 style="padding:10px 12px 0;margin:0;color:#096dd9;">' . esc_html__( '📲 WhatsApp Price Hider', 'price-hider-whatsapp-inquiry-for-woocommerce' ) . '</h4>';

		// Hide price for this product.
		woocommerce_wp_select(
			array(
				'id'          => '_swph_hide_price',
				'label'       => __( 'Hide Price', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'options'     => array(
					''    => __( '— Use category/global rule —', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
					'yes' => __( 'Yes — hide price & show WhatsApp button', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
					'no'  => __( 'No — always show price', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				),
				'value'       => get_post_meta( $product_id, '_swph_hide_price', true ),
				'desc_tip'    => true,
				'description' => __( 'Override the category/global hide-price setting for this product.', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
			)
		);

		// WhatsApp number.
		woocommerce_wp_text_input(
			array(
				'id'          => '_swph_whatsapp_number',
				'label'       => __( 'WhatsApp Number', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'placeholder' => '1234567890',
				'desc_tip'    => true,
				'description' => __( 'Digits only (e.g., 14155238886). Leave blank to use category or global number.', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'value'       => get_post_meta( $product_id, '_swph_whatsapp_number', true ),
			)
		);

		// Custom button label.
		woocommerce_wp_text_input(
			array(
				'id'          => '_swph_button_label',
				'label'       => __( 'Button Label', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'placeholder' => __( 'e.g., Request Quote', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'desc_tip'    => true,
				'description' => __( 'Custom text for the WhatsApp button on this product. Leave blank to use global label.', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'value'       => get_post_meta( $product_id, '_swph_button_label', true ),
			)
		);

		// Custom message template.
		woocommerce_wp_textarea_input(
			array(
				'id'          => '_swph_custom_message',
				'label'       => __( 'Custom WhatsApp Message', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'placeholder' => __( 'Use {product_name}, {product_url}, {product_sku}', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'desc_tip'    => true,
				'description' => __( 'Pre-filled message when the button is clicked. Use {product_name}, {product_url}, {product_sku}. Leave blank for global template.', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
				'value'       => get_post_meta( $product_id, '_swph_custom_message', true ),
				'rows'        => 3,
			)
		);

		echo '</div>';
	}

	/**
	 * Save the custom meta fields.
	 *
	 * @param int $product_id Product ID.
	 */
	public function save_fields( int $product_id ): void {
		// Nonce already verified by WooCommerce before this hook fires.

		$hide_price = isset( $_POST['_swph_hide_price'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? sanitize_text_field( wp_unslash( $_POST['_swph_hide_price'] ) )
			: '';
		update_post_meta( $product_id, '_swph_hide_price', $hide_price );

		$number = isset( $_POST['_swph_whatsapp_number'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['_swph_whatsapp_number'] ) ) )
			: '';
		update_post_meta( $product_id, '_swph_whatsapp_number', $number );

		$label = isset( $_POST['_swph_button_label'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? sanitize_text_field( wp_unslash( $_POST['_swph_button_label'] ) )
			: '';
		update_post_meta( $product_id, '_swph_button_label', $label );

		$message = isset( $_POST['_swph_custom_message'] ) // phpcs:ignore WordPress.Security.NonceVerification
			? sanitize_textarea_field( wp_unslash( $_POST['_swph_custom_message'] ) )
			: '';
		update_post_meta( $product_id, '_swph_custom_message', $message );
	}
}
