define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/url'
], function (Component, quote, urlBuilder, mageUrl) {
    'use strict';

    var displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;

    return Component.extend({
        defaults: {
            displaySubtotalMode: displaySubtotalMode,
            template: 'Magento_Tax/checkout/summary/subtotal'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|String}
         */
        getValue: function () {
            var total = 0;

            if (this.totals()) {
                if (this.isCartPage()) {
                    var items = document.querySelectorAll('td.col.subtotal .price');
                    if (items) {
                        items.forEach(function(item) {
                            var priceText = item.textContent.trim().replace(/[^\d.,-]/g, ''); // Eliminar caracteres no numéricos excepto ',' y '-'
                            var priceValue = parseFloat(priceText.replace(/\./g, '').replace(',', '.')); // Reemplazar ',' por '.' y convertir a float
                            total += (priceValue );
                        });
                    }
                }
                else {
                    total = this.totals().subtotal;
                }
            }
            return this.getFormattedPrice(total);
        },

        /**
         * check if we are in cart page
         *
         * @returns {boolean}
         */
        isCartPage: function () {
            var currentUrl = mageUrl.build('checkout/cart').replace(/\/$/, ""); // Eliminar la barra diagonal al final
            var currentLocation = window.location.href.replace(/\/$/, ""); // Eliminar la barra diagonal al final
            return currentLocation === currentUrl;
        },

        /**
         * @return {Boolean}
         */
        isBothPricesDisplayed: function () {
            return this.displaySubtotalMode == 'both'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isIncludingTaxDisplayed: function () {
            return this.displaySubtotalMode == 'including'; //eslint-disable-line eqeqeq
        }
    });
});
