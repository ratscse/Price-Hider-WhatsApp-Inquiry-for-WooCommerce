<?php
/**
 * Uninstall routine — runs when the plugin is deleted from the WP admin.
 *
 * Removes all plugin options and the custom analytics table.
 *
 * @package Rats_Price_Inquiry_for_WooCommerce
 */

// WordPress calls this file directly; bail if accessed any other way.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// ── 1. Remove plugin options ──────────────────────────────────────────────
$swph_options = array(
	'swph_global_whatsapp',
	'swph_guest_only',
	'swph_default_button_label',
	'swph_default_message_template',
	'swph_category_rules',
	'swph_enable_analytics',
	'swph_db_version',
);

foreach ( $swph_options as $swph_option ) {
	delete_option( $swph_option );
}

// ── 2. Remove per-product post meta ──────────────────────────────────────
$swph_meta_keys = array(
	'_swph_hide_price',
	'_swph_whatsapp_number',
	'_swph_button_label',
	'_swph_custom_message',
);

foreach ( $swph_meta_keys as $swph_key ) {
	delete_post_meta_by_key( $swph_key );
}

// ── 3. Drop the analytics table ───────────────────────────────────────────
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}swph_analytics" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// ── 4. Remove any lingering transients (rotation counters) ───────────────
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%swph_rotate_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
