<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Form\Grid;

use Magento\Framework\Data\OptionSourceInterface;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnConfig;

class ReturnTypeOptionsProvider implements OptionSourceInterface
{
    private $context;

    public function __construct(\Magento\Backend\Block\Template\Context $context)
    {
        $this->context = $context;
    }

    public function toOptionArray()
    {
        return [
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD,
                'label' => __(SouthbayReturnConfig::getTypeName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD))
            ],
            [
                'value' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL,
                'label' => __(SouthbayReturnConfig::getTypeName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL))
            ]
        ];
    }
}
