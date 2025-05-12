define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    var addedToCart = {
        init: function (baseUrl) {
            urlBuilder.setBaseUrl(baseUrl);
            var correctUrl = urlBuilder.build('southbay_product/product/cartitems');
            $.ajax({
                url: correctUrl,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var cartSkus = response.map(function(item) {
                        return item.sku;
                    });

                    $('.product-item-link').each(function() {
                        var sku = $(this).closest('.product-item').find('.product-sku').text().trim();
                        if ($.inArray(sku, cartSkus) !== -1) {
                            $(this).addClass('added-to-cart');
                            $(this).closest('.product-item').find('.action.tocart.primary').hide();
                        } else {
                            $(this).removeClass('added-to-cart');
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error(" Error al obtener los SKUs del carrito:", error);
                }
            });
        }
    };

    return addedToCart;
});
