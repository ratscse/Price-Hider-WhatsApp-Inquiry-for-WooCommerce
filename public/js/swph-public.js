/**
 * Smart WhatsApp Price Hider — Public JS
 * Fires an AJAX request to log WhatsApp button clicks.
 */
(function ($) {
    'use strict';

    $(document).on('click', '.swph-whatsapp-btn', function () {
        var productId = $(this).data('product-id');

        if ( ! productId || ! swphData ) {
            return;
        }

        $.post(swphData.ajaxUrl, {
            action:     'swph_track_click',
            nonce:      swphData.nonce,
            product_id: productId
        });
        // Fire-and-forget; we do not await or alert on the result.
    });

}(jQuery));
