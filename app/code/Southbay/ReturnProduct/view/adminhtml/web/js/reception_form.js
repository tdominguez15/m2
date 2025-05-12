define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/form/form',
    'mage/url'
], function ($, ko, Component, ComponentParent) {
    'use strict';

    var _handler = null;

    return ComponentParent.extend({
        initialize: function () {
            this._super();

            console.log('Reception init...');

            const self = this;

            $(document).on('southbay_return_product_reception_set_values', function (e, data) {
                $('div[data-index="new_reception_search_fieldset"]').hide();
                $('div[data-index="detail"]').show();

                self.source.set('data.fields.detail.southbay_return_id', data.id);
                self.source.set('data.fields.detail.southbay_return_product_type', data.type);
                self.source.set('data.fields.detail.southbay_return_product_customer', data.customer);
                self.source.set('data.fields.detail.southbay_return_total_packages', data.total_packages);
                self.source.set('data.fields.detail.southbay_return_reception_total_packages', '');
            });


            _handler = setInterval(function () {
                self.checkLoaded();
            }, 100);

            return this;
        },
        checkLoaded: function () {
            if ($('div[data-index="detail"]').length === 0 ||
                $('.search_again').length === 0
            ) {
                return;
            }

            clearInterval(_handler);

            const edit_mode = this.source.get('data.fields.edit_mode');

            console.log('edit_mode:', edit_mode);

            if (!edit_mode) {
                $('div[data-index="new_reception_search_fieldset"]').show();
                $('div[data-index="detail"]').hide();
                $(document).trigger('searchPending');

                $('.search_again').on('click', function (e) {
                    e.preventDefault();

                    $('div[data-index="new_reception_search_fieldset"]').show();
                    $('div[data-index="detail"]').hide();

                    $(document).trigger('southbay_return_product_reception_reset');
                });
            } else {
                $('div[data-index="new_reception_search_fieldset"]').hide();
                $('div[data-index="detail"]').show();
                $('.search_again').hide();

                if (edit_mode === 'edit') {
                    $('#save').show();
                } else {
                    $('.total_packages input').prop('readonly', true);
                }
            }
        }
    });
});
