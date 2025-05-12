<?php

namespace Southbay\Product\Plugin;

use Magento\Framework\Locale\ListsInterface;

class TranslatedListsPlugin
{
    public function aroundGetCountryTranslation(
        ListsInterface $subject,
        callable       $proceed,
                       $value,
                       $locale = null
    )
    {
        if ($value == 'Z1') {
            return 'Zona Franca Border';
        } else if ($value == 'Z2') {
            return 'Zona Franca Duty';
        }
        return $proceed($value, $locale);
    }
}
