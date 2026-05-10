/**
 * Rats Price Inquiry for WooCommerce — Admin JS
 *
 * Features:
 *  - Live message preview
 *  - Phone number format helper (strip non-digits on blur)
 *  - "Mark All Categories" shortcut checkbox
 *  - Highlight rows when category hide-price is enabled
 *  - Category row search/filter
 */
( function ( $ ) {
	'use strict';

	// ── Constants ────────────────────────────────────────────────
	var SAMPLE_PRODUCT = 'Blue Wireless Headphones';
	var SAMPLE_URL     = window.location.origin + '/product/blue-wireless-headphones/';
	var SAMPLE_SKU     = 'BWH-001';

	// ── Boot on DOM ready ─────────────────────────────────────────
	$( document ).ready( function () {
		initMessagePreview();
		initPhoneFormatters();
		initMarkAllCategories();
		initCategoryRowHighlight();
		initCategorySearch();
	} );

	// ── Live message preview ──────────────────────────────────────
	function initMessagePreview() {
		var $textarea = $( '#swph_default_message_template' );
		if ( ! $textarea.length ) return;

		var $preview = $( '<div class="swph-msg-preview"><strong>&#128241; Preview:</strong><p class="swph-msg-preview-text"></p></div>' );
		$textarea.after( $preview );

		function updatePreview() {
			var tpl = $textarea.val()
				.replace( /\{product_name\}/g, SAMPLE_PRODUCT )
				.replace( /\{product_url\}/g,  SAMPLE_URL )
				.replace( /\{product_sku\}/g,  SAMPLE_SKU );
			$preview.find( '.swph-msg-preview-text' ).text( tpl );
		}

		$textarea.on( 'input', updatePreview );
		updatePreview();
	}

	// ── Phone number formatting ───────────────────────────────────
	function initPhoneFormatters() {
		$( document ).on( 'blur', 'input[name="swph_global_whatsapp"], input[name*="[whatsapp]"], input[name*="[rotate_whatsapp]"], input[name="_swph_whatsapp_number"]', function () {
			var raw     = $( this ).val();
			var cleaned = raw.replace( /\D/g, '' );
			if ( raw !== cleaned ) {
				$( this ).val( cleaned ).addClass( 'swph-field-cleaned' );
				setTimeout( function () { $( '.swph-field-cleaned' ).removeClass( 'swph-field-cleaned' ); }, 1200 );
			}
		} );
	}

	// ── Mark/unmark all category hide-price checkboxes ───────────
	function initMarkAllCategories() {
		var $table = $( '.swph-category-table' );
		if ( ! $table.length ) return;

		var $thHide = $table.find( 'thead tr th:nth-child(2)' );
		var $masterCb = $( '<input type="checkbox" title="Toggle all" style="margin-left:6px;cursor:pointer;">' );
		$thHide.append( $masterCb );

		$masterCb.on( 'change', function () {
			var checked = $( this ).is( ':checked' );
			$table.find( 'tbody input[type="checkbox"]' ).prop( 'checked', checked ).trigger( 'change' );
		} );
	}

	// ── Highlight category rows when hide is ticked ───────────────
	function initCategoryRowHighlight() {
		$( document ).on( 'change', '.swph-category-table tbody input[type="checkbox"]', function () {
			$( this ).closest( 'tr' ).toggleClass( 'swph-row-active', $( this ).is( ':checked' ) );
		} );

		$( '.swph-category-table tbody input[type="checkbox"]:checked' ).each( function () {
			$( this ).closest( 'tr' ).addClass( 'swph-row-active' );
		} );
	}

	// ── Category table search/filter ──────────────────────────────
	function initCategorySearch() {
		var $wrap = $( '.swph-category-table-wrap' );
		if ( ! $wrap.length ) return;

		var $input = $( '<input type="search" placeholder="Filter categories\u2026" class="swph-cat-search regular-text" style="margin-bottom:10px;display:block;">' );
		$wrap.before( $input );

		$input.on( 'input', function () {
			var q = $( this ).val().toLowerCase();
			$( '.swph-category-table tbody tr' ).each( function () {
				var name = $( this ).find( 'td:first' ).text().toLowerCase();
				$( this ).toggle( ! q || name.indexOf( q ) !== -1 );
			} );
		} );
	}

	// ── Copy placeholder chips ────────────────────────────────────
	$( document ).on( 'click', '.swph-copy-placeholder', function ( e ) {
		e.preventDefault();
		var text  = $( this ).data( 'value' );
		var $self = $( this );

		if ( navigator.clipboard ) {
			navigator.clipboard.writeText( text );
		} else {
			var el = document.createElement( 'textarea' );
			el.value = text;
			document.body.appendChild( el );
			el.select();
			document.execCommand( 'copy' );
			document.body.removeChild( el );
		}

		var orig = $self.text();
		$self.text( '\u2714 Copied!' ).css( 'color', '#25d366' );
		setTimeout( function () { $self.text( orig ).css( 'color', '' ); }, 1500 );
	} );

}( jQuery ) );
