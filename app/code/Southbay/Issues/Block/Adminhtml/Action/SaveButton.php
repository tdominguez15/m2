<?php

namespace Southbay\Issues\Block\Adminhtml\Action;

use Magento\Customer\Api\AccountManagementInterface;

class SaveButton extends \Southbay\ReturnProduct\Block\Adminhtml\Action\SaveButton
{
    public function getButtonData()
    {
        $result = parent::getButtonData();
        $result['label'] = __('Ejecutar');
        return $result;
    }
}
