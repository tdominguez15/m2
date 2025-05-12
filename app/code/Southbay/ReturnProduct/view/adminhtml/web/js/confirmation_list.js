require(['jquery', 'ko', 'uiRegistry'],
    function ($, ko, registry) {
        window._$ = $;
        window._registry = registry;

        $(document).on('click', '.data-row .action-menu-item', function (e) {
            e.preventDefault();

            const elem = $(e.originalEvent.target);
            const link = elem.attr('href');
            load(link);
        });

        function load(link) {
            $('body').trigger('processStart');

            if (link.includes('southbay_return_product/confirmation/confirm/')) {
                window.location.href = link;
            } else {
                $.ajax({
                    url: link, // Reemplaza con la ruta de tu controlador
                    type: 'GET',
                    success: function (response) {
                        registry.get('confirmation_form.areas.southbay_return_pending.southbay_return_pending.southbay_return_modal').openModal();
                        $('.southbay_return_product_detail_container .admin__fieldset-wrapper-content .admin__fieldset').html(response);
                        $('.retry').on('click', function (e) {
                            const _elem = $(e.target);
                            const _link = _elem.attr('href');

                            e.preventDefault();
                            $.ajax({
                                url: _link, // Reemplaza con la ruta de tu controlador
                                type: 'GET',
                                success: function (response) {
                                    load(link);
                                },
                                error: function (error) {
                                    console.error('Error', error);
                                },
                                complete: function () {
                                    $('body').trigger('processStop');
                                }
                            });
                        });
                    },
                    error: function (error) {
                        console.error('Error', error);
                    },
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
            }
        }
    }
);
