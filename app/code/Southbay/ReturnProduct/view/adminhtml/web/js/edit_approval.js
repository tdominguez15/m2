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
        hideLoader: function () {
            if (!$('.southbay_return_product_id_show_detail').length) {
                const self = this;
                const template = this.showDetailTemplate();
                $('.southbay_return_product_id_container .admin__field-control').append(template);
                $('.southbay_return_product_id_show_detail_action').on('click', function (e) {
                    e.preventDefault();

                    $('body').trigger('processStart');

                    const link = self.source.get('data.link');

                    $.ajax({
                        url: link, // Reemplaza con la ruta de tu controlador
                        type: 'GET',
                        success: function (response) {
                            registry.get('edit_approval.areas.fields.fields.southbay_return_modal').openModal();
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

            return this._super();
        },
        initialize: function () {
            this._super();

            window._registry = registry;
            window._$ = $;

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
