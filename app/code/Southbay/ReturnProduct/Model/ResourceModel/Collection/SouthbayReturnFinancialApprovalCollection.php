<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnFinancialApprovalCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReturnFinancialApproval',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApproval'
        );
    }
}
