<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;

class TypeRolOptionsProvider implements OptionSourceInterface
{
    private $context;

    public function __construct(\Magento\Backend\Block\Template\Context $context)
    {
        $this->context = $context;
    }

    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Seleccione un tipo de rol')],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_RECEPTION,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_NAME_RECEPTION)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_NAME_APPROVAL)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CONTROL_QA,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_NAME_CONTROL_QA)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_NAME_CHECK)
            ]
        ];
    }
}
