define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'mage/url'
], function (Component, quote, priceUtils, totals, mageUrl) {
    'use strict';

    return Component.extend({
        defaults: {
            isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
            template: 'Magento_Tax/checkout/summary/grand-total'
        },
        totals: quote.getTotals(),
        isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

        /**
         * @return {*}
         */
        isDisplayed: function () {
            return this.isFullMode();
        },

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
         * @return {*|String}
         */
        getBaseValue: function () {
            var price = 0;

            if (this.totals()) {
                price = this.totals()['base_grand_total'];
            }

            return priceUtils.formatPriceLocale(price, quote.getBasePriceFormat());
        },

        /**
         * @return {*}
         */
        getGrandTotalExclTax: function () {
            var total = this.totals(),
                amount;

            if (!total) {
                return 0;
            }

            amount = total['grand_total'] - total['tax_amount'];

            if (amount < 0) {
                amount = 0;
            }

            return this.getFormattedPrice(amount);
        },

        /**
         * @return {Boolean}
         */
        isBaseGrandTotalDisplayNeeded: function () {
            var total = this.totals();

            if (!total) {
                return false;
            }

            return total['base_currency_code'] != total['quote_currency_code']; //eslint-disable-line eqeqeq
        }
    });
});
