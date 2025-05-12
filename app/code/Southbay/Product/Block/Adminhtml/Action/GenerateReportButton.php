<?php

namespace Southbay\Product\Block\Adminhtml\Action;

use Magento\Customer\Api\AccountManagementInterface;

class GenerateReportButton extends \Magento\Customer\Block\Adminhtml\Edit\SaveButton
{
    public function __construct(\Magento\Backend\Block\Widget\Context $context,
                                \Magento\Framework\Registry           $registry,
                                AccountManagementInterface            $customerAccountManagement)
    {
        parent::__construct($context, $registry, $customerAccountManagement);
    }

    public function getButtonData()
    {
        return [
            'label' => __('Generar reporte'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
