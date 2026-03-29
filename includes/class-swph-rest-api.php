<?php
/**
 * Registers a REST API endpoint so headless / block-based themes can
 * query per-product WhatsApp settings without server-side rendering.
 *
 * GET /wp-json/swph/v1/product/{id}
 *
 * @package Price_Hider_WhatsApp_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SWPH_REST_API
 */
class SWPH_REST_API {

	/**
	 * Singleton instance.
	 *
	 * @var SWPH_REST_API|null
	 */
	private static $instance = null;

	/**
	 * @return SWPH_REST_API
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor. */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'swph/v1',
			'/product/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_product_data' ),
				'permission_callback' => array( $this, 'check_product_data_permission' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);
	}

	/**
	 * Return WhatsApp button data for a product.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_product_data( WP_REST_Request $request ) {
		$product_id = (int) $request->get_param( 'id' );
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return new WP_Error( 'swph_not_found', __( 'Product not found.', 'price-hider-whatsapp-inquiry-for-woocommerce' ), array( 'status' => 404 ) );
		}

		$settings   = SWPH_Settings::instance();
		$price_hider = SWPH_Price_Hider::instance();
		$should_hide = $price_hider->should_hide( $product );

		$data = array(
			'product_id'   => $product_id,
			'should_hide'  => $should_hide,
			'button_label' => $settings->resolve_button_label( $product ),
			'whatsapp_url' => '',
		);

		if ( $should_hide ) {
			// Provide URL only (no full HTML) for JS rendering.
			$number  = $settings->resolve_whatsapp_number( $product );
			$message = rawurlencode(
				str_replace(
					array( '{product_name}', '{product_url}', '{product_sku}' ),
					array( $product->get_name(), get_permalink( $product_id ), $product->get_sku() ?: 'N/A' ),
					$settings->default_message_template()
				)
			);
			if ( $number ) {
				$data['whatsapp_url'] = 'https://wa.me/' . $number . '?text=' . $message;
			}
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Check if user has permission to access product data endpoint.
	 *
	 * @return bool|WP_Error
	 */
	public function check_product_data_permission() {
		// Allow authenticated users and shop managers/admins.
		// For public block themes, clients can authenticate via REST with tokens.
		if ( is_user_logged_in() ) {
			return true;
		}

		// Allow shop managers and above.
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		return new WP_Error(
			'rest_forbidden',
				esc_html__( 'You do not have permission to access this endpoint.', 'price-hider-whatsapp-inquiry-for-woocommerce' ),
			array( 'status' => 403 )
		);
	}
}
