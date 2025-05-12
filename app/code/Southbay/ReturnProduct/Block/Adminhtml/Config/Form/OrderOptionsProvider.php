<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;

class OrderOptionsProvider implements OptionSourceInterface
{
    private $context;

    public function __construct(\Magento\Backend\Block\Template\Context $context)
    {
        $this->context = $context;
    }

    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Seleccione un orden')],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::INVOICE_ORDER_ASC,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::INVOICE_ORDER_ASC_NAME)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::INVOICE_ORDER_DESC,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::INVOICE_ORDER_DESC_NAME)
            ]
        ];
    }
}
