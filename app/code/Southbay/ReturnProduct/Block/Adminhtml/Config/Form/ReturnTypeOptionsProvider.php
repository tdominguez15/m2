<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

class ReturnTypeOptionsProvider extends \Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnTypeOptionsProvider
{
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('Seleccione un tipo')]);
        return $options;
    }
}
