define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/form/form',
    'mage/translate',
    'uiRegistry'
], function ($, ko, Component, ComponentParent, $t, registry) {
    'use strict';

    return ComponentParent.extend({
        initialize: function () {
            this._super();

            const self = this;
            window._registry = registry;
            window._$ = $;

            $(document).on('southbay_return_product_set_values', function (e, data) {
                console.log('southbay_return_product_set_values...', data);
                self.source.set('data.fields.form_fields.southbay_return_product_id', data.id);
                self.source.set('data.fields.form_fields.southbay_return_product_type', data.type);
                self.source.set('data.fields.form_fields.southbay_return_product_customer', data.customer);
                self.source.set('data.fields.form_fields.southbay_return_total_amount', data.detail.total_accepted);
                self.source.set('data.fields.form_fields.southbay_return_amount_accepted', data.detail.total_amount);
                self.source.set('data.fields.form_fields.southbay_return_financial_approval_total_accepted_amount', data.detail.total_amount_financial);
                self.source.set('data.fields.form_fields.southbay_return_financial_approval_exchange_rate', data.detail.exchange_rate);

                if (!$('.southbay_return_product_id_show_detail').length) {
                    $('.southbay_return_product_id_container .admin__field-control').append(self.showDetailTemplate());
                    $('.southbay_return_product_id_show_detail_action').on('click', function (e) {
                        e.preventDefault();

                        $('body').trigger('processStart');

                        const link = data.link;

                        $.ajax({
                            url: link, // Reemplaza con la ruta de tu controlador
                            type: 'GET',
                            success: function (response) {
                                registry.get('new_approval.areas.fields.fields.southbay_return_modal').openModal();
                                $('.southbay_return_product_detail_container .admin__fieldset-wrapper-content .admin__fieldset').html(response);
                            },
                            error: function (error) {
                                console.error('Error', error);
                            },
                            complete: function () {
                                $('body').trigger('processStop');
                            }
                        });
                    });
                }
            });

            return this;
        },
        showDetailTemplate: function () {
            const text = $t('Ver detalle');
            return `
                <p class="southbay_return_product_id_show_detail">
                    <a class="southbay_return_product_id_show_detail_action" href="#">${text}</a>
                </p>
            `;
        }
    });
});
