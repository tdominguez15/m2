<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;

class StatusOptionsProvider implements OptionSourceInterface
{
    private $context;

    public function __construct(\Magento\Backend\Block\Template\Context $context)
    {
        $this->context = $context;
    }

    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Seleccione un tipo de tramite')],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_RECEIVED,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_RECEIVED)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_FAIL_INIT,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_FAIL_INIT)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_GOOD_INIT)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_REJECTED,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_REJECTED)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CANCEL,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CANCEL)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_APPROVAL)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_APPROVAL_GOOD)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA_GOOD,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CONTROL_QA_GOOD)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CONTROL_QA)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONFIRMED,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CONFIRMED)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_REJECTED_IN_CONTROL_QA,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_REJECTED_IN_CONTROL_QA)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_ARCHIVED,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_ARCHIVED)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_DOCUMENTS_SENT,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_DOCUMENTS_SENT)
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CLOSED,
                'label' => __(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CLOSED)
            ]
        ];
    }
}
