define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/form/form'
], function ($, ko, Component, ComponentParent) {
    'use strict';

    return ComponentParent.extend({
        initialize: function () {
            window._$ = $;
            this._super();

            const items = this.source.get('data.fields.items');
            const control_qa_items = 'control_qa_form.areas.fields.fields.items_fieldset.southbay_return_control_qa_items';

            $('div[data-index="items_fieldset"]').hide();

            if (items) {
                window.southbay_return_product_set_values = {
                    items: items,
                    control_qa_items: control_qa_items,
                    edit_mode: this.editMode()
                };
            } else {
                const self = this;
                $('#save').show();

                $(document).on('southbay_return_product_set_values', function (e, data) {
                    console.log('southbay_return_product_set_values...');

                    $('div[data-index="search_fieldset"]').hide();
                    $('div[data-index="items_fieldset"]').show();
                    $('div[data-index="detail"]').show();

                    self.source.set('data.fields.detail.southbay_return_id', data.id);
                    self.source.set('data.fields.detail.southbay_return_product_type', data.type);
                    self.source.set('data.fields.detail.southbay_return_product_customer', data.customer);
                });
            }

            return this;
        },
        editMode: function () {
            const edit_mode = this.source.get('data.fields.edit_mode');
            if (edit_mode) {
                if (edit_mode !== 'edit') {
                    return 'view';
                } else {
                    return 'edit';
                }
            } else {
                return 'new';
            }
        },
        hideLoader: function () {
            const edit_mode = this.editMode();

            if (edit_mode === 'new') {
                $('div[data-index="search_fieldset"]').show();
                $('div[data-index="items_fieldset"]').hide();
                $('div[data-index="detail"]').hide();
                $(document).trigger('searchPending');

                $('.search_again').on('click', function (e) {
                    e.preventDefault();

                    $('div[data-index="search_fieldset"]').show();
                    $('div[data-index="items_fieldset"]').hide();
                    $('div[data-index="detail"]').hide();

                    $(document).trigger('southbay_return_product_reception_reset');
                });
            } else {
                $('div[data-index="search_fieldset"]').hide();
                $('div[data-index="items_fieldset"]').show();
                $('div[data-index="detail"]').show();
                $('.search_again').hide();

                if (edit_mode === 'edit') {
                    $('#save').show();
                }
            }

            return this._super();
        }
    });
});
