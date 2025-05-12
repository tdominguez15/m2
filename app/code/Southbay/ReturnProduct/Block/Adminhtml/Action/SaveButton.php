<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Action;

use Magento\Customer\Api\AccountManagementInterface;

class SaveButton extends \Magento\Customer\Block\Adminhtml\Edit\SaveButton
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
            'label' => __('Guardar'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
