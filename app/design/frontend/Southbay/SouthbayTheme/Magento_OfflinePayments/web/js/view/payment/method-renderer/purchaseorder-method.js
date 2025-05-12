/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'Magento_Checkout/js/view/payment/default',
    'jquery',
    'mage/url',
    'mage/validation',
], function (Component, $, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_OfflinePayments/payment/purchaseorder-form',
            purchaseOrderNumber: '',
            additionalInformation: '',
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('purchaseOrderNumber')
                .observe('additionalInformation')

            this.nextPoNumber();

            return this;
        },

        nextPoNumber: function() {
            var self = this;
            var url = urlBuilder.build('southbay_custom_checkout/purchase/nextponumber');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    self.purchaseOrderNumber(data.new_po_number);
                },
                error: function () {
                    console.log('Error getting next po number');
                    self.purchaseOrderNumber('');
                }
            });
        },

        /**
         * @return {Object}
         */
        getData: function () {
            return {
                method: this.item.method,
                'po_number': this.purchaseOrderNumber(),
                'additional_data': {
                    'order_observation': this.additionalInformation()
                }
            };
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = 'form[data-role=purchaseorder-form]';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
