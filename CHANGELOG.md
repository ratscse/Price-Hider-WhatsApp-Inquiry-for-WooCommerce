# Changelog

All notable changes to **Price Hider & WhatsApp Inquiry for WooCommerce** are documented here.

This project follows [Semantic Versioning](https://semver.org/) and
[Keep a Changelog](https://keepachangelog.com/) conventions.

---

## [1.0.0] — 2024-01-01

### Added
- **Smart WhatsApp Routing**: Set per-category WhatsApp numbers with round-robin
  rotation between two staff members via a transient-based counter.
- **Auto-Populated Messages**: Pre-filled WhatsApp chat message using
  `{product_name}`, `{product_url}`, and `{product_sku}` placeholders.
  Fully customisable per product or globally.
- **Guest-Only Mode**: Optionally hide prices only for logged-out users so
  registered members continue seeing normal prices.
- **Custom Button Labels**: Override the default "WhatsApp Us" text globally,
  per category, or per individual product.
- **Mini Analytics Dashboard**: Custom DB table (`wp_swph_analytics`) records
  every button click (IP stored as SHA-256 hash). Admin table shows ranked
  click counts per product with summary stat cards.
- **Per-Product Meta Box**: Hide price, custom WhatsApp number, button label,
  and message template fields added to the WooCommerce General product tab.
- **CSV Export**: Download all click analytics as a CSV from the Tools page.
- **Analytics Reset**: Truncate click data from the Tools page with
  confirmation prompt.
- **REST API Endpoint**: `GET /wp-json/swph/v1/product/{id}` returns
  `should_hide`, `button_label`, and `whatsapp_url` for headless / block
  theme support.
- **Tools Page**: DB table status check, plugin environment info, export,
  and reset.
- **Admin UX**:
  - Live WhatsApp message preview below the template field.
  - Click-to-copy placeholder chips (`{product_name}`, etc.).
  - Phone fields auto-strip non-digit characters on blur.
  - Master "toggle all" checkbox for category hide-price column.
  - Highlighted rows for categories with hide-price enabled.
  - Category search/filter input for stores with many categories.
- **Donation / Hire banners** on every admin page.
- **WooCommerce HPOS compatibility** declaration.
- **Uninstall routine**: removes all options, post meta, DB table, and
  rotation transients on plugin deletion.
- Full internationalisation support with `.pot` file.

---

*Older versions will be listed here as they are released.*
