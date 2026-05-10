=== Rats Price Inquiry for WooCommerce ===
Contributors:      shuab_ratan
Tags:              woocommerce, whatsapp, hide price, price inquiry, catalog mode
Requires at least: 5.8
Tested up to:      6.9
Requires PHP:      7.4
Requires Plugins:  woocommerce
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Hide WooCommerce prices and replace them with WhatsApp inquiry buttons — routing, auto-messages, guest-only mode, custom labels, analytics and more.

== Description ==

**Rats Price Inquiry for WooCommerce** is a free WooCommerce plugin that hides product prices and replaces the add-to-cart button with a smart WhatsApp inquiry button. Perfect for B2B stores, quote-based shops, or any business that wants customers to ask before buying.

= Key Features =

**Smart WhatsApp Routing**
Set a different WhatsApp number for each product category. Enable round-robin rotation between two numbers to balance inquiry load between staff members automatically.

**Auto-Populated Chat Messages**
When a customer clicks your button, WhatsApp opens with a pre-filled message including the product name, URL, and SKU. Fully customisable with placeholders, overridable per product.

**Guest-Only Mode**
Hide prices only for logged-out visitors. Registered members see normal prices — great for building a mailing list with a "Member Price" experience.

**Custom Button Labels**
Change the button text globally, per category, or per individual product: "Check Stock," "Request Quote," "Bulk Order Inquiry," and so on.

**Mini Analytics Dashboard**
Click counts per product, ranked by popularity. Export as CSV or reset at any time.

**Flexible Price-Hiding Rules**
Hide prices for an entire category or override at product level. Product overrides always win.

**REST API**
GET /wp-json/swph/v1/product/{id} for headless / block-based themes.

**Tools Page**
CSV export, analytics reset, and environment health check.

== Installation ==

1. Upload the `rats-price-inquiry-for-woocommerce` folder to `/wp-content/plugins/`.
2. Activate the plugin in Plugins > Installed Plugins.
3. Ensure WooCommerce is installed and active.
4. Go to Price Inquiry > Settings to configure.

== Frequently Asked Questions ==

= Does this work with block-based themes? =
The plugin hooks into standard WooCommerce PHP template actions. Classic and Storefront-based themes work out of the box. For full-site-editing themes, use the REST API endpoint to build a custom block or add minor CSS targeting.

= How does round-robin rotation work? =
A WordPress transient counter per category alternates between the primary and rotate number. The counter resets daily.

= Is it GDPR compliant? =
Only a SHA-256 hash of the visitor IP is stored for analytics — no plain-text personal data. Update your privacy policy accordingly.

= What is removed when I delete the plugin? =
The uninstall.php routine removes all plugin options, per-product post meta, the analytics DB table, and rotation transients.

== Screenshots ==

1. Settings page with global options and live message preview.
2. Category rules table with hide-price, dual WhatsApp numbers, and custom labels.
3. Analytics dashboard with stat cards and per-product click table.
4. Tools page — CSV export, reset, environment info.
5. Per-product meta box in the WooCommerce General tab.
6. Front-end single product page with WhatsApp button.
7. Front-end shop loop with animated WhatsApp button on product cards.

== Changelog ==

= 1.0.0 =
* Initial release.
* Smart per-category WhatsApp routing with round-robin rotation.
* Auto-populated WhatsApp messages with product name, URL, and SKU placeholders.
* Guest-only price-hiding mode.
* Custom button labels (global / category / product level).
* Mini analytics dashboard with click counts per product.
* CSV export and analytics reset in Tools page.
* REST API endpoint for headless / block-theme support.
* Per-product meta box for hide price, number, label, and message overrides.
* Live message preview, copy-placeholder chips, and category search in admin.
* WooCommerce HPOS compatibility declaration.
* Full uninstall cleanup via uninstall.php.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade steps required.
