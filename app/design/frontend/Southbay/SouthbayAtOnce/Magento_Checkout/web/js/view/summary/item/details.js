define([
    'uiComponent',
    'escaper',
    'ko',
    'jquery'
], function (Component, escaper, ko, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details',
            allowedTags: ['b', 'strong', 'i', 'em', 'u']
        },

        initialize: function () {
            this._super();
            this.customQtys = ko.observable({});
        },

        getNameUnsanitizedHtml: function (quoteItem) {
            var txt = document.createElement('textarea');
            txt.innerHTML = quoteItem.name;
            return escaper.escapeHtml(txt.value, this.allowedTags);
        },

        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        getCustomQty: function (quoteItem) {
            var self = this;
            var customQtys = this.customQtys();

            if (quoteItem && quoteItem.item_id) {
                if (!customQtys[quoteItem.item_id]) {
                    // Si no hay cantidad personalizada guardada para este artículo
                    $.ajax({
                        url: '/southbay_custom_checkout/Quote/Customqty',
                        type: 'POST',
                        data: {
                            item_id: quoteItem.item_id
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                customQtys[quoteItem.item_id] = response.customqty;
                            } else {
                                console.error('Error fetching custom quantity:', response.message);
                                customQtys[quoteItem.item_id] = 1;
                            }
                            self.customQtys(customQtys);
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', error);
                            customQtys[quoteItem.item_id] = 1;
                            self.customQtys(customQtys);
                        }
                    });
                }
                return customQtys[quoteItem.item_id];
            }
            return 1;
        }
    });
});
