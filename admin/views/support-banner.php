<?php
/**
 * Support Banner Template
 * 
 * Reusable support/donation banner shown across admin pages.
 *
 * @package Price_Hider_WhatsApp_Inquiry_for_WooCommerce
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Support Banner -->
<div class="swph-support-banner">
	<div class="swph-support-item">
		<span class="dashicons dashicons-heart"></span>
		<div>
			<strong><?php esc_html_e( 'Enjoying the plugin?', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></strong><br>
			<a href="https://www.buymeacoffee.com/digitaskills" target="_blank" rel="noopener noreferrer" class="swph-btn swph-btn-coffee"><?php esc_html_e( 'Buy Me a Coffee', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></a>
			<a href="https://ko-fi.com/digitaskills" target="_blank" rel="noopener noreferrer" class="swph-btn swph-btn-donate"><?php esc_html_e( 'Donate via Ko-fi', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></a>
		</div>
	</div>
	<div class="swph-support-item">
		<span class="dashicons dashicons-admin-tools"></span>
		<div>
			<strong><?php esc_html_e( 'Need custom plugin development?', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></strong><br>
            <a href="https://digitaskills.com/hire-me" target="_blank" rel="noopener noreferrer" class="swph-btn swph-btn-hire"><?php esc_html_e( 'Hire Me', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></a>
            <a href="https://wa.link/bdvvum" target="_blank" rel="noopener noreferrer" class="swph-btn swph-btn-hire"><?php esc_html_e( 'Report a bug', 'price-hider-whatsapp-inquiry-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
