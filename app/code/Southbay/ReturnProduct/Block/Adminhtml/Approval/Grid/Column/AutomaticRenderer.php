<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Approval\Grid\Column;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class AutomaticRenderer extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $user_code = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_USER_CODE);
        if($user_code == '0') {
            return __('Si');
        } else {
            return __('No');
        }
    }
}
