define([
        'jquery',
        'mage/translate',
        'jquery/ui'
    ],
    function ($, $t)
    {
        'use strict';

        return function (target)
        {
            $.widget('mage.catalogAddToCart', target, {
                options: {
                    addToCartButtonTextDefault: $t('Update Cart')
                }
            });
            return $.mage.catalogAddToCart;
        };
    });
